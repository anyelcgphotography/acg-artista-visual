<?php
/**
 * Correos del CRM: aviso a la fotógrafa y acuse de recibo al cliente.
 *
 * Los dos van en HTML sencillo y sin imágenes: son correos transaccionales
 * que tienen que llegar a la bandeja de entrada, y las plantillas cargadas de
 * recursos externos son justo lo que hace que acaben en spam.
 *
 * El acuse de recibo se manda en el idioma en el que estaba navegando quien
 * escribió, no en el del sitio.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Envío de correos.
 */
class ACG_CRM_Emails {

	/**
	 * No hay nada que enganchar: los correos salen bajo demanda.
	 *
	 * El método existe para que el arranque del plugin trate a todas las
	 * clases igual.
	 *
	 * @return void
	 */
	public static function init() {}

	/**
	 * Envuelve el contenido en una plantilla mínima.
	 *
	 * @param string $titulo Titular del correo.
	 * @param string $cuerpo HTML del cuerpo.
	 * @return string
	 */
	private static function plantilla( $titulo, $cuerpo ) {
		$acento = '#fa6613';

		return '<div style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#1a1a1a;max-width:600px">'
			. '<p style="margin:0 0 4px;font-size:11px;letter-spacing:.24em;text-transform:uppercase;color:' . $acento . '">'
			. esc_html( get_bloginfo( 'name' ) ) . '</p>'
			. '<h1 style="margin:0 0 20px;font-size:22px;line-height:1.25">' . esc_html( $titulo ) . '</h1>'
			. $cuerpo
			. '<p style="margin-top:28px;padding-top:16px;border-top:1px solid #e2e2e2;font-size:12px;color:#777">'
			. esc_html__( 'Mensaje automático del formulario de contacto.', 'acg-crm' ) . '</p>'
			. '</div>';
	}

	/**
	 * Envía un correo en HTML.
	 *
	 * La cabecera `Content-Type` viaja en la propia llamada en vez de por el
	 * filtro global `wp_mail_content_type`: así solo afecta a estos correos y
	 * no a los que manden WordPress u otros plugins.
	 *
	 * @param string $para   Destinatario.
	 * @param string $asunto Asunto.
	 * @param string $cuerpo Cuerpo en HTML.
	 * @param array  $extra  Cabeceras adicionales.
	 * @return bool
	 */
	private static function enviar( $para, $asunto, $cuerpo, $extra = array() ) {
		$cabeceras = array_merge( array( 'Content-Type: text/html; charset=UTF-8' ), $extra );

		return (bool) wp_mail( $para, $asunto, $cuerpo, $cabeceras );
	}

	/**
	 * Avisa de una solicitud nueva y manda el acuse de recibo.
	 *
	 * @param int   $lead_id ID de la solicitud.
	 * @param array $datos   Datos recibidos.
	 * @return void
	 */
	public static function avisar_nueva( $lead_id, $datos ) {
		self::avisar_admin( $lead_id, $datos );

		if ( acg_crm_opcion( 'acuse_recibo', '1' ) ) {
			self::acusar_recibo( $datos );
		}
	}

	/**
	 * Correo interno con los datos y el enlace a la ficha.
	 *
	 * @param int   $lead_id ID de la solicitud.
	 * @param array $datos   Datos recibidos.
	 * @return void
	 */
	private static function avisar_admin( $lead_id, $datos ) {
		$filas = array(
			__( 'Nombre', 'acg-crm' )   => $datos['nombre'],
			__( 'Email', 'acg-crm' )    => $datos['email'],
			__( 'Teléfono', 'acg-crm' ) => $datos['telefono'],
			__( 'Servicio', 'acg-crm' ) => $datos['servicio'],
			__( 'Fecha', 'acg-crm' )    => $datos['fecha'],
			__( 'Idioma', 'acg-crm' )   => 'pt' === $datos['idioma'] ? 'Português' : 'Español',
			__( 'Origen', 'acg-crm' )   => $datos['origen'],
		);

		$cuerpo = '<table style="border-collapse:collapse;width:100%">';

		foreach ( $filas as $etiqueta => $valor ) {
			if ( '' === trim( (string) $valor ) ) {
				continue;
			}

			$cuerpo .= '<tr>'
				. '<td style="padding:7px 12px 7px 0;color:#777;white-space:nowrap;vertical-align:top">' . esc_html( $etiqueta ) . '</td>'
				. '<td style="padding:7px 0;font-weight:600">' . esc_html( $valor ) . '</td>'
				. '</tr>';
		}

		$cuerpo .= '</table>';

		if ( ! empty( $datos['mensaje'] ) ) {
			$cuerpo .= '<p style="margin-top:18px;padding:14px 16px;background:#f6f6f6;white-space:pre-line">'
				. esc_html( $datos['mensaje'] ) . '</p>';
		}

		$cuerpo .= '<p style="margin-top:22px">'
			. '<a href="' . esc_url( get_edit_post_link( $lead_id, '' ) ) . '" style="display:inline-block;background:#fa6613;color:#000;padding:12px 22px;font-weight:700;text-decoration:none">'
			. esc_html__( 'Abrir la ficha', 'acg-crm' ) . '</a></p>';

		$asunto = sprintf(
			/* translators: 1: nombre del sitio, 2: nombre de quien escribe. */
			__( '[%1$s] Nueva solicitud de %2$s', 'acg-crm' ),
			get_bloginfo( 'name' ),
			$datos['nombre']
		);

		// Responder al correo lleva directamente a quien escribió, sin tener
		// que copiar la dirección desde el panel.
		self::enviar(
			acg_crm_email_destino(),
			$asunto,
			self::plantilla( __( 'Nueva solicitud', 'acg-crm' ), $cuerpo ),
			array( 'Reply-To: ' . $datos['nombre'] . ' <' . $datos['email'] . '>' )
		);
	}

	/**
	 * Acuse de recibo para quien ha escrito, en su idioma.
	 *
	 * @param array $datos Datos recibidos.
	 * @return void
	 */
	private static function acusar_recibo( $datos ) {
		$idioma = $datos['idioma'];
		$sitio  = get_bloginfo( 'name' );

		$titulo = acg_crm_t( '¡Gracias por escribir!', 'Obrigada por escrever!', $idioma );

		$texto = acg_crm_t(
			'He recibido tu mensaje y te respondo en menos de 24 horas con disponibilidad y presupuesto.',
			'Recebi sua mensagem e respondo em menos de 24 horas com disponibilidade e orçamento.',
			$idioma
		);

		$cuerpo = '<p>' . esc_html( acg_crm_t( 'Hola', 'Olá', $idioma ) ) . ' ' . esc_html( $datos['nombre'] ) . ',</p>'
			. '<p>' . esc_html( $texto ) . '</p>';

		if ( ! empty( $datos['fecha'] ) ) {
			$cuerpo .= '<p>' . esc_html( acg_crm_t( 'Fecha que me indicas:', 'Data que você indicou:', $idioma ) )
				. ' <strong>' . esc_html( $datos['fecha'] ) . '</strong></p>';
		}

		if ( ! empty( $datos['mensaje'] ) ) {
			$cuerpo .= '<p style="padding:14px 16px;background:#f6f6f6;white-space:pre-line">'
				. esc_html( $datos['mensaje'] ) . '</p>';
		}

		$cuerpo .= '<p>' . esc_html( acg_crm_t( 'Un abrazo,', 'Um abraço,', $idioma ) ) . '<br>' . esc_html( $sitio ) . '</p>';

		$asunto = sprintf(
			/* translators: %s: nombre del sitio. */
			acg_crm_t( 'Tu solicitud en %s', 'Seu pedido em %s', $idioma ),
			$sitio
		);

		self::enviar( $datos['email'], $asunto, self::plantilla( $titulo, $cuerpo ) );
	}
}
