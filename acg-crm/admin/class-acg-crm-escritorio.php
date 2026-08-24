<?php
/**
 * Widget del Escritorio: cómo va la captación de un vistazo.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resumen en el Escritorio.
 */
class ACG_CRM_Escritorio {

	/**
	 * Engancha el widget.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'registrar' ) );
	}

	/**
	 * Registra el widget si el usuario puede ver las solicitudes.
	 *
	 * @return void
	 */
	public static function registrar() {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'acg_crm_resumen',
			__( 'Solicitudes de clientes', 'acg-crm' ),
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Cuenta solicitudes por estado en una sola consulta.
	 *
	 * Se hace con SQL directo y no con un WP_Query por estado porque son seis
	 * estados: seis consultas para pintar un widget del Escritorio es un coste
	 * que se paga en cada carga del panel.
	 *
	 * @return array<string,int>
	 */
	private static function conteo_por_estado() {
		global $wpdb;

		$filas = $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT pm.meta_value AS estado, COUNT(*) AS total
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = 'lead_estado'
				AND p.post_type = %s
				AND p.post_status NOT IN ('trash', 'auto-draft')
				GROUP BY pm.meta_value",
				ACG_CRM_CPT::TIPO
			)
		);

		$conteo = array_fill_keys( array_keys( acg_crm_estados() ), 0 );

		foreach ( (array) $filas as $fila ) {
			if ( isset( $conteo[ $fila->estado ] ) ) {
				$conteo[ $fila->estado ] = (int) $fila->total;
			}
		}

		return $conteo;
	}

	/**
	 * Solicitudes recibidas en los últimos siete días.
	 *
	 * @return int
	 */
	private static function de_esta_semana() {
		$recientes = get_posts(
			array(
				'post_type'      => ACG_CRM_CPT::TIPO,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'date_query'     => array( array( 'after' => '7 days ago' ) ),
			)
		);

		return count( $recientes );
	}

	/**
	 * Pinta el widget.
	 *
	 * @return void
	 */
	public static function render() {
		$conteo   = self::conteo_por_estado();
		$total    = array_sum( $conteo );
		$colores  = acg_crm_estado_colores();
		$semana   = self::de_esta_semana();
		$ganados  = isset( $conteo['ganado'] ) ? $conteo['ganado'] : 0;
		$tasa     = $total > 0 ? round( ( $ganados / $total ) * 100, 1 ) : 0;
		$historial = ACG_CRM_Actividad::recientes( 5 );
		?>
		<div class="acg-crm-widget">
			<div class="acg-crm-cifras">
				<div>
					<strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong>
					<span><?php esc_html_e( 'solicitudes', 'acg-crm' ); ?></span>
				</div>
				<div>
					<strong><?php echo esc_html( number_format_i18n( $semana ) ); ?></strong>
					<span><?php esc_html_e( 'esta semana', 'acg-crm' ); ?></span>
				</div>
				<div>
					<strong><?php echo esc_html( $tasa ); ?>%</strong>
					<span><?php esc_html_e( 'se convierten', 'acg-crm' ); ?></span>
				</div>
			</div>

			<?php if ( $total ) : ?>
				<ul class="acg-crm-embudo">
					<?php foreach ( acg_crm_estados() as $clave => $etiqueta ) : ?>
						<?php $ancho = $total ? max( 2, round( ( $conteo[ $clave ] / $total ) * 100 ) ) : 0; ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . ACG_CRM_CPT::TIPO . '&acg_estado=' . $clave ) ); ?>">
								<span class="acg-crm-embudo__label"><?php echo esc_html( $etiqueta ); ?></span>
								<span class="acg-crm-embudo__barra">
									<span style="width:<?php echo esc_attr( $ancho ); ?>%;background:<?php echo esc_attr( $colores[ $clave ] ); ?>"></span>
								</span>
								<span class="acg-crm-embudo__num"><?php echo esc_html( number_format_i18n( $conteo[ $clave ] ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'Todavía no ha llegado ninguna solicitud por el formulario.', 'acg-crm' ); ?></p>
			<?php endif; ?>

			<?php if ( $historial ) : ?>
				<h3><?php esc_html_e( 'Lo último', 'acg-crm' ); ?></h3>
				<ul class="acg-crm-ultimo">
					<?php foreach ( $historial as $entrada ) : ?>
						<li>
							<a href="<?php echo esc_url( (string) get_edit_post_link( $entrada->lead_id, '' ) ); ?>">
								<strong><?php echo esc_html( get_the_title( $entrada->lead_id ) ); ?></strong>
							</a>
							<span><?php echo wp_kses_post( wp_trim_words( $entrada->contenido, 14 ) ); ?></span>
							<em><?php echo esc_html( human_time_diff( strtotime( $entrada->creado ), current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp ?></em>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<p class="acg-crm-widget__pie">
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . ACG_CRM_CPT::TIPO ) ); ?>">
					<?php esc_html_e( 'Ver todas las solicitudes', 'acg-crm' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
