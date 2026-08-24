<?php
/**
 * Interfaz del CRM en el panel: lista de solicitudes, ficha e historial.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pantallas del CRM.
 */
class ACG_CRM_Admin {

	/**
	 * Engancha todo lo del panel.
	 *
	 * @return void
	 */
	public static function init() {
		$tipo = ACG_CRM_CPT::TIPO;

		add_filter( 'manage_' . $tipo . '_posts_columns', array( __CLASS__, 'columnas' ) );
		add_action( 'manage_' . $tipo . '_posts_custom_column', array( __CLASS__, 'columna' ), 10, 2 );
		add_filter( 'manage_edit-' . $tipo . '_sortable_columns', array( __CLASS__, 'columnas_ordenables' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'filtro_estado' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'aplicar_filtro' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . $tipo, array( __CLASS__, 'guardar_ficha' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_acg_crm_exportar', array( __CLASS__, 'exportar_csv' ) );
		add_action( 'views_edit-' . $tipo, array( __CLASS__, 'boton_exportar' ) );
	}

	/**
	 * Columnas de la lista de solicitudes.
	 *
	 * @param array $columnas Columnas actuales.
	 * @return array
	 */
	public static function columnas( $columnas ) {
		return array(
			'cb'           => isset( $columnas['cb'] ) ? $columnas['cb'] : '',
			'title'        => __( 'Nombre', 'acg-crm' ),
			'acg_estado'   => __( 'Estado', 'acg-crm' ),
			'acg_contacto' => __( 'Contacto', 'acg-crm' ),
			'acg_servicio' => __( 'Servicio', 'acg-crm' ),
			'acg_fecha'    => __( 'Fecha del encargo', 'acg-crm' ),
			'date'         => __( 'Recibida', 'acg-crm' ),
		);
	}

	/**
	 * Contenido de cada columna propia.
	 *
	 * @param string $columna Columna que se pinta.
	 * @param int    $post_id ID de la solicitud.
	 * @return void
	 */
	public static function columna( $columna, $post_id ) {
		switch ( $columna ) {
			case 'acg_estado':
				$estado  = get_post_meta( $post_id, 'lead_estado', true );
				$estado  = $estado ? $estado : 'nuevo';
				$colores = acg_crm_estado_colores();
				$color   = isset( $colores[ $estado ] ) ? $colores[ $estado ] : '#8a8f98';

				printf(
					'<span class="acg-crm-estado" style="--acg-estado:%1$s">%2$s</span>',
					esc_attr( $color ),
					esc_html( acg_crm_estado_label( $estado ) )
				);
				break;

			case 'acg_contacto':
				$email    = get_post_meta( $post_id, 'lead_email', true );
				$telefono = get_post_meta( $post_id, 'lead_telefono', true );

				if ( $email ) {
					printf( '<a href="mailto:%1$s">%1$s</a><br>', esc_attr( $email ) );
				}

				if ( $telefono ) {
					printf(
						'<a href="https://wa.me/%1$s" target="_blank" rel="noopener">%2$s</a>',
						esc_attr( preg_replace( '/[^0-9]/', '', $telefono ) ),
						esc_html( $telefono )
					);
				}
				break;

			case 'acg_servicio':
				echo esc_html( get_post_meta( $post_id, 'lead_servicio', true ) );
				break;

			case 'acg_fecha':
				$fecha = get_post_meta( $post_id, 'lead_fecha', true );
				echo $fecha ? esc_html( mysql2date( get_option( 'date_format' ), $fecha ) ) : '—';
				break;
		}
	}

	/**
	 * Permite ordenar por la fecha del encargo, que no es la de recepción.
	 *
	 * @param array $columnas Columnas ordenables.
	 * @return array
	 */
	public static function columnas_ordenables( $columnas ) {
		$columnas['acg_fecha'] = 'acg_fecha';

		return $columnas;
	}

	/**
	 * Desplegable para filtrar por estado.
	 *
	 * @return void
	 */
	public static function filtro_estado() {
		global $typenow;

		if ( ACG_CRM_CPT::TIPO !== $typenow ) {
			return;
		}

		$actual = isset( $_GET['acg_estado'] ) ? sanitize_key( wp_unslash( $_GET['acg_estado'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<select name="acg_estado">
			<option value=""><?php esc_html_e( 'Todos los estados', 'acg-crm' ); ?></option>
			<?php foreach ( acg_crm_estados() as $clave => $etiqueta ) : ?>
				<option value="<?php echo esc_attr( $clave ); ?>" <?php selected( $actual, $clave ); ?>>
					<?php echo esc_html( $etiqueta ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Aplica el filtro de estado y la ordenación por fecha del encargo.
	 *
	 * @param WP_Query $query Consulta.
	 * @return void
	 */
	public static function aplicar_filtro( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ACG_CRM_CPT::TIPO !== $query->get( 'post_type' ) ) {
			return;
		}

		$estado = isset( $_GET['acg_estado'] ) ? sanitize_key( wp_unslash( $_GET['acg_estado'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( $estado && isset( acg_crm_estados()[ $estado ] ) ) {
			$query->set(
				'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					array(
						'key'   => 'lead_estado',
						'value' => $estado,
					),
				)
			);
		}

		if ( 'acg_fecha' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', 'lead_fecha' ); // phpcs:ignore WordPress.DB.SlowDBQuery
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Cajas de la ficha de una solicitud.
	 *
	 * @return void
	 */
	public static function meta_boxes() {
		add_meta_box(
			'acg_crm_datos',
			__( 'Datos de la solicitud', 'acg-crm' ),
			array( __CLASS__, 'caja_datos' ),
			ACG_CRM_CPT::TIPO,
			'normal',
			'high'
		);

		add_meta_box(
			'acg_crm_historial',
			__( 'Historial', 'acg-crm' ),
			array( __CLASS__, 'caja_historial' ),
			ACG_CRM_CPT::TIPO,
			'normal',
			'default'
		);

		add_meta_box(
			'acg_crm_estado',
			__( 'Estado', 'acg-crm' ),
			array( __CLASS__, 'caja_estado' ),
			ACG_CRM_CPT::TIPO,
			'side',
			'high'
		);
	}

	/**
	 * Caja con los datos de contacto.
	 *
	 * @param WP_Post $post Solicitud.
	 * @return void
	 */
	public static function caja_datos( $post ) {
		wp_nonce_field( 'acg_crm_ficha', 'acg_crm_ficha_nonce' );

		$campos = array(
			'lead_email'    => array( 'label' => __( 'Email', 'acg-crm' ), 'tipo' => 'email' ),
			'lead_telefono' => array( 'label' => __( 'Teléfono', 'acg-crm' ), 'tipo' => 'text' ),
			'lead_servicio' => array( 'label' => __( 'Servicio', 'acg-crm' ), 'tipo' => 'text' ),
			'lead_fecha'    => array( 'label' => __( 'Fecha del encargo', 'acg-crm' ), 'tipo' => 'date' ),
			'lead_valor'    => array( 'label' => __( 'Presupuesto (€)', 'acg-crm' ), 'tipo' => 'number' ),
		);
		?>
		<div class="acg-crm-rejilla">
			<?php foreach ( $campos as $clave => $campo ) : ?>
				<p>
					<label for="<?php echo esc_attr( $clave ); ?>"><strong><?php echo esc_html( $campo['label'] ); ?></strong></label><br>
					<input
						type="<?php echo esc_attr( $campo['tipo'] ); ?>"
						id="<?php echo esc_attr( $clave ); ?>"
						name="<?php echo esc_attr( $clave ); ?>"
						value="<?php echo esc_attr( get_post_meta( $post->ID, $clave, true ) ); ?>"
						class="widefat"
					>
				</p>
			<?php endforeach; ?>
		</div>

		<p class="description">
			<?php
			$idioma = get_post_meta( $post->ID, 'lead_idioma', true );
			$origen = get_post_meta( $post->ID, 'lead_origen', true );

			printf(
				/* translators: 1: idioma, 2: página de origen. */
				esc_html__( 'Llegó en %1$s desde %2$s.', 'acg-crm' ),
				esc_html( 'pt' === $idioma ? 'português' : 'español' ),
				esc_html( $origen ? $origen : '/' )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Caja del historial, con el formulario para añadir notas.
	 *
	 * @param WP_Post $post Solicitud.
	 * @return void
	 */
	public static function caja_historial( $post ) {
		$entradas = ACG_CRM_Actividad::get( $post->ID );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="acg-crm-nota">
			<input type="hidden" name="action" value="acg_crm_nota">
			<input type="hidden" name="lead_id" value="<?php echo esc_attr( $post->ID ); ?>">
			<?php wp_nonce_field( 'acg_crm_nota_' . $post->ID ); ?>

			<textarea name="nota" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Anota qué habéis hablado, qué has enviado, qué queda pendiente…', 'acg-crm' ); ?>"></textarea>

			<p>
				<select name="tipo">
					<option value="nota"><?php esc_html_e( 'Nota', 'acg-crm' ); ?></option>
					<option value="llamada"><?php esc_html_e( 'Llamada', 'acg-crm' ); ?></option>
					<option value="email"><?php esc_html_e( 'Email enviado', 'acg-crm' ); ?></option>
					<option value="whatsapp"><?php esc_html_e( 'WhatsApp', 'acg-crm' ); ?></option>
				</select>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Añadir al historial', 'acg-crm' ); ?></button>
			</p>
		</form>

		<?php if ( ! $entradas ) : ?>
			<p class="description"><?php esc_html_e( 'Todavía no hay nada anotado.', 'acg-crm' ); ?></p>
			<?php return; ?>
		<?php endif; ?>

		<ul class="acg-crm-historial">
			<?php foreach ( $entradas as $entrada ) : ?>
				<li>
					<span class="acg-crm-historial__meta">
						<?php
						$autor = $entrada->user_id ? get_userdata( $entrada->user_id ) : null;

						echo esc_html(
							mysql2date( get_option( 'date_format' ) . ' H:i', $entrada->creado )
						);

						if ( $autor ) {
							echo ' · ' . esc_html( $autor->display_name );
						}

						echo ' · ' . esc_html( $entrada->tipo );
						?>
					</span>
					<span class="acg-crm-historial__texto"><?php echo wp_kses_post( $entrada->contenido ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Caja lateral con el estado y los atajos de contacto.
	 *
	 * @param WP_Post $post Solicitud.
	 * @return void
	 */
	public static function caja_estado( $post ) {
		$actual   = get_post_meta( $post->ID, 'lead_estado', true );
		$actual   = $actual ? $actual : 'nuevo';
		$email    = get_post_meta( $post->ID, 'lead_email', true );
		$telefono = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $post->ID, 'lead_telefono', true ) );
		?>
		<p>
			<select name="lead_estado" class="widefat">
				<?php foreach ( acg_crm_estados() as $clave => $etiqueta ) : ?>
					<option value="<?php echo esc_attr( $clave ); ?>" <?php selected( $actual, $clave ); ?>>
						<?php echo esc_html( $etiqueta ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="description"><?php esc_html_e( 'Cada cambio de estado queda anotado solo en el historial.', 'acg-crm' ); ?></p>

		<?php if ( $email || $telefono ) : ?>
			<p class="acg-crm-atajos">
				<?php if ( $email ) : ?>
					<a class="button" href="mailto:<?php echo esc_attr( $email ); ?>"><?php esc_html_e( 'Responder por email', 'acg-crm' ); ?></a>
				<?php endif; ?>
				<?php if ( $telefono ) : ?>
					<a class="button" href="https://wa.me/<?php echo esc_attr( $telefono ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir WhatsApp', 'acg-crm' ); ?></a>
				<?php endif; ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Guarda los campos de la ficha.
	 *
	 * @param int $post_id ID de la solicitud.
	 * @return void
	 */
	public static function guardar_ficha( $post_id ) {
		if ( ! isset( $_POST['acg_crm_ficha_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['acg_crm_ficha_nonce'] ) ), 'acg_crm_ficha' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$texto = array( 'lead_telefono', 'lead_servicio', 'lead_fecha' );

		foreach ( $texto as $clave ) {
			if ( isset( $_POST[ $clave ] ) ) {
				update_post_meta( $post_id, $clave, sanitize_text_field( wp_unslash( $_POST[ $clave ] ) ) );
			}
		}

		if ( isset( $_POST['lead_email'] ) ) {
			update_post_meta( $post_id, 'lead_email', sanitize_email( wp_unslash( $_POST['lead_email'] ) ) );
		}

		if ( isset( $_POST['lead_valor'] ) ) {
			update_post_meta( $post_id, 'lead_valor', (float) preg_replace( '/[^0-9.]/', '', wp_unslash( $_POST['lead_valor'] ) ) ); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotSanitized
		}

		if ( isset( $_POST['lead_estado'] ) ) {
			$estado = sanitize_key( wp_unslash( $_POST['lead_estado'] ) );

			if ( isset( acg_crm_estados()[ $estado ] ) ) {
				update_post_meta( $post_id, 'lead_estado', $estado );
			}
		}
	}

	/**
	 * Estilos del panel, solo en las pantallas del CRM.
	 *
	 * @param string $hook Pantalla actual.
	 * @return void
	 */
	public static function assets( $hook ) {
		$pantalla = get_current_screen();
		$es_crm   = $pantalla && ACG_CRM_CPT::TIPO === $pantalla->post_type;

		if ( ! $es_crm && 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'acg-crm-admin',
			ACG_CRM_URL . 'assets/css/admin.css',
			array(),
			ACG_CRM_VERSION
		);
	}

	/**
	 * Botón de exportar sobre la lista de solicitudes.
	 *
	 * @param array $vistas Vistas de la lista.
	 * @return array
	 */
	public static function boton_exportar( $vistas ) {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=acg_crm_exportar' ),
			'acg_crm_exportar'
		);

		printf(
			'<p><a class="button" href="%s">%s</a></p>',
			esc_url( $url ),
			esc_html__( 'Exportar a CSV', 'acg-crm' )
		);

		return $vistas;
	}

	/**
	 * Descarga todas las solicitudes en CSV.
	 *
	 * @return void
	 */
	public static function exportar_csv() {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'No tienes permiso para exportar las solicitudes.', 'acg-crm' ) );
		}

		check_admin_referer( 'acg_crm_exportar' );

		$leads = get_posts(
			array(
				'post_type'      => ACG_CRM_CPT::TIPO,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=solicitudes-' . gmdate( 'Y-m-d' ) . '.csv' );

		$salida = fopen( 'php://output', 'w' );

		// BOM para que Excel abra los acentos correctamente.
		fwrite( $salida, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		fputcsv(
			$salida,
			array(
				__( 'Recibida', 'acg-crm' ),
				__( 'Nombre', 'acg-crm' ),
				__( 'Email', 'acg-crm' ),
				__( 'Teléfono', 'acg-crm' ),
				__( 'Servicio', 'acg-crm' ),
				__( 'Fecha del encargo', 'acg-crm' ),
				__( 'Estado', 'acg-crm' ),
				__( 'Presupuesto', 'acg-crm' ),
				__( 'Idioma', 'acg-crm' ),
				__( 'Mensaje', 'acg-crm' ),
			)
		);

		foreach ( $leads as $lead ) {
			fputcsv(
				$salida,
				array(
					get_the_date( 'Y-m-d H:i', $lead ),
					$lead->post_title,
					get_post_meta( $lead->ID, 'lead_email', true ),
					get_post_meta( $lead->ID, 'lead_telefono', true ),
					get_post_meta( $lead->ID, 'lead_servicio', true ),
					get_post_meta( $lead->ID, 'lead_fecha', true ),
					acg_crm_estado_label( get_post_meta( $lead->ID, 'lead_estado', true ) ),
					get_post_meta( $lead->ID, 'lead_valor', true ),
					get_post_meta( $lead->ID, 'lead_idioma', true ),
					$lead->post_content,
				)
			);
		}

		fclose( $salida ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}
