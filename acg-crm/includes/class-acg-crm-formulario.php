<?php
/**
 * Captura de solicitudes desde el front.
 *
 * Recibe el envío del formulario del theme (por AJAX) o de cualquier otro
 * theme mediante el shortcode [acg_formulario], lo valida y crea el lead.
 *
 * Antispam sin captcha, en tres capas que no molestan a nadie:
 *
 * 1. Nonce, que descarta los envíos que no vienen de una página del sitio.
 * 2. Honeypot: un campo oculto que solo rellenan los bots.
 * 3. Trampa de tiempo: rellenar el formulario en menos de tres segundos no lo
 *    hace una persona.
 *
 * Se responde «éxito» al honeypot en vez de un error, para no darle al bot la
 * señal de que ha sido detectado.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Endpoint del formulario.
 */
class ACG_CRM_Formulario {

	/**
	 * Segundos mínimos entre cargar el formulario y enviarlo.
	 */
	const SEGUNDOS_MINIMOS = 3;

	/**
	 * Engancha los endpoints.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_acg_submit_lead', array( __CLASS__, 'procesar' ) );
		add_action( 'wp_ajax_nopriv_acg_submit_lead', array( __CLASS__, 'procesar' ) );
		add_action( 'admin_post_nopriv_acg_submit_lead', array( __CLASS__, 'procesar_sin_js' ) );
		add_action( 'admin_post_acg_submit_lead', array( __CLASS__, 'procesar_sin_js' ) );
		add_shortcode( 'acg_formulario', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Idioma en el que se envió el formulario.
	 *
	 * @return string
	 */
	private static function idioma() {
		$idioma = isset( $_POST['idioma'] ) ? sanitize_key( wp_unslash( $_POST['idioma'] ) ) : 'es'; // phpcs:ignore WordPress.Security.NonceVerification

		return 'pt' === $idioma ? 'pt' : 'es';
	}

	/**
	 * Valida el envío y devuelve los datos limpios.
	 *
	 * @return array{ok:bool,datos?:array,mensaje?:string,codigo?:int,silencioso?:bool}
	 */
	private static function validar() {
		$idioma = self::idioma();

		if ( ! empty( $_POST['acg_web'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array( 'ok' => false, 'silencioso' => true );
		}

		$inicio = isset( $_POST['inicio'] ) ? absint( $_POST['inicio'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( $inicio && ( time() - $inicio ) < self::SEGUNDOS_MINIMOS ) {
			return array(
				'ok'      => false,
				'codigo'  => 429,
				'mensaje' => acg_crm_t(
					'El envío ha ido demasiado rápido. Inténtalo otra vez.',
					'O envio foi rápido demais. Tente novamente.',
					$idioma
				),
			);
		}

		$nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( '' === $nombre || ! is_email( $email ) ) {
			return array(
				'ok'      => false,
				'codigo'  => 400,
				'mensaje' => acg_crm_t(
					'Revisa el nombre y el email.',
					'Confira o nome e o e-mail.',
					$idioma
				),
			);
		}

		if ( empty( $_POST['acepto'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array(
				'ok'      => false,
				'codigo'  => 400,
				'mensaje' => acg_crm_t(
					'Necesito que aceptes la política de privacidad.',
					'Preciso que você aceite a política de privacidade.',
					$idioma
				),
			);
		}

		// La fecha llega de un <input type="date">, así que o es AAAA-MM-DD o
		// no es una fecha que valga la pena guardar.
		$fecha = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$fecha = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) ? $fecha : '';

		return array(
			'ok'    => true,
			'datos' => array(
				'nombre'   => $nombre,
				'email'    => $email,
				'telefono' => isset( $_POST['telefono'] ) ? sanitize_text_field( wp_unslash( $_POST['telefono'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'servicio' => isset( $_POST['servicio'] ) ? sanitize_text_field( wp_unslash( $_POST['servicio'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'fecha'    => $fecha,
				'mensaje'  => isset( $_POST['mensaje'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mensaje'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'idioma'   => $idioma,
				'origen'   => isset( $_POST['origen'] ) ? sanitize_text_field( wp_unslash( $_POST['origen'] ) ) : '/', // phpcs:ignore WordPress.Security.NonceVerification
			),
		);
	}

	/**
	 * Crea el lead y dispara los avisos.
	 *
	 * @param array $datos Datos ya validados.
	 * @return int|WP_Error
	 */
	private static function registrar( $datos ) {
		$lead_id = ACG_CRM_CPT::crear( $datos );

		if ( is_wp_error( $lead_id ) ) {
			return $lead_id;
		}

		ACG_CRM_Actividad::add(
			$lead_id,
			'sistema',
			sprintf(
				/* translators: %s: ruta de la página desde la que se envió. */
				__( 'Solicitud recibida desde <strong>%s</strong>.', 'acg-crm' ),
				esc_html( $datos['origen'] ? $datos['origen'] : '/' )
			),
			0
		);

		ACG_CRM_Emails::avisar_nueva( $lead_id, $datos );

		/**
		 * Se dispara justo después de guardar una solicitud.
		 *
		 * @param int   $lead_id ID de la solicitud.
		 * @param array $datos   Datos recibidos.
		 */
		do_action( 'acg_crm_lead_creado', $lead_id, $datos );

		return $lead_id;
	}

	/**
	 * Procesa el envío por AJAX.
	 *
	 * @return void
	 */
	public static function procesar() {
		check_ajax_referer( 'acg_lead_form', 'nonce' );

		$idioma = self::idioma();
		$ok     = acg_crm_t(
			'¡Gracias! Te respondo en menos de 24 h.',
			'Obrigada! Respondo em menos de 24 h.',
			$idioma
		);

		$validacion = self::validar();

		if ( empty( $validacion['ok'] ) ) {
			// Al bot se le responde que todo ha ido bien: así no aprende que
			// el honeypot existe.
			if ( ! empty( $validacion['silencioso'] ) ) {
				wp_send_json_success( array( 'message' => $ok ) );
			}

			wp_send_json_error(
				array( 'message' => $validacion['mensaje'] ),
				isset( $validacion['codigo'] ) ? $validacion['codigo'] : 400
			);
		}

		$lead_id = self::registrar( $validacion['datos'] );

		if ( is_wp_error( $lead_id ) ) {
			wp_send_json_error(
				array(
					'message' => acg_crm_t(
						'No he podido guardar tu mensaje. Escríbeme por WhatsApp, por favor.',
						'Não consegui salvar sua mensagem. Me escreva pelo WhatsApp, por favor.',
						$idioma
					),
				),
				500
			);
		}

		wp_send_json_success( array( 'message' => $ok, 'lead' => $lead_id ) );
	}

	/**
	 * Procesa el envío cuando no hay JavaScript (el formulario se manda a
	 * admin-post.php y se vuelve a la página con un parámetro en la URL).
	 *
	 * @return void
	 */
	public static function procesar_sin_js() {
		check_admin_referer( 'acg_lead_form', 'nonce' );

		$validacion = self::validar();
		$destino    = wp_get_referer() ? wp_get_referer() : home_url( '/' );

		if ( empty( $validacion['ok'] ) ) {
			$estado = ! empty( $validacion['silencioso'] ) ? 'ok' : 'error';
			wp_safe_redirect( add_query_arg( 'acg_envio', $estado, $destino ) . '#contacto' );
			exit;
		}

		$lead_id = self::registrar( $validacion['datos'] );
		$estado  = is_wp_error( $lead_id ) ? 'error' : 'ok';

		wp_safe_redirect( add_query_arg( 'acg_envio', $estado, $destino ) . '#contacto' );
		exit;
	}

	/**
	 * Shortcode [acg_formulario], para usar el CRM con otros themes.
	 *
	 * Si el theme ACG está activo se reutiliza su plantilla, que ya está
	 * maquetada; si no, se pinta un formulario sobrio con los mismos campos.
	 *
	 * @return string
	 */
	public static function shortcode() {
		ob_start();

		if ( function_exists( 'get_template_part' ) && locate_template( 'template-parts/formulario.php' ) ) {
			get_template_part( 'template-parts/formulario' );

			return (string) ob_get_clean();
		}

		$nonce = wp_create_nonce( 'acg_lead_form' );
		?>
		<form class="acg-crm-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="acg_submit_lead">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
			<input type="hidden" name="inicio" value="<?php echo esc_attr( time() ); ?>">

			<p>
				<label><?php esc_html_e( 'Nombre', 'acg-crm' ); ?> *<br>
					<input type="text" name="nombre" required>
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Email', 'acg-crm' ); ?> *<br>
					<input type="email" name="email" required>
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Teléfono', 'acg-crm' ); ?><br>
					<input type="tel" name="telefono">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Fecha', 'acg-crm' ); ?><br>
					<input type="date" name="fecha">
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Mensaje', 'acg-crm' ); ?><br>
					<textarea name="mensaje" rows="5"></textarea>
				</label>
			</p>
			<p>
				<label>
					<input type="checkbox" name="acepto" value="1" required>
					<?php esc_html_e( 'He leído y acepto la política de privacidad.', 'acg-crm' ); ?>
				</label>
			</p>

			<div style="position:absolute;left:-9999px" aria-hidden="true">
				<label><?php esc_html_e( 'No rellenes este campo', 'acg-crm' ); ?>
					<input type="text" name="acg_web" tabindex="-1" autocomplete="off">
				</label>
			</div>

			<p><button type="submit"><?php esc_html_e( 'Enviar solicitud', 'acg-crm' ); ?></button></p>
		</form>
		<?php

		return (string) ob_get_clean();
	}
}
