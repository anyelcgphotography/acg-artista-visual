<?php
/**
 * Funciones sueltas que usan varias clases del plugin.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Estados por los que pasa una solicitud.
 *
 * El orden importa: es el que se pinta en el embudo del Escritorio.
 *
 * @return array<string,string>
 */
function acg_crm_estados() {
	return array(
		'nuevo'       => __( 'Nuevo', 'acg-crm' ),
		'contactado'  => __( 'Contactado', 'acg-crm' ),
		'presupuesto' => __( 'Presupuesto enviado', 'acg-crm' ),
		'reservado'   => __( 'Fecha reservada', 'acg-crm' ),
		'ganado'      => __( 'Cliente', 'acg-crm' ),
		'perdido'     => __( 'Perdido', 'acg-crm' ),
	);
}

/**
 * Color con el que se pinta cada estado en el panel.
 *
 * @return array<string,string>
 */
function acg_crm_estado_colores() {
	return array(
		'nuevo'       => '#fa6613',
		'contactado'  => '#2271b1',
		'presupuesto' => '#8c6d1f',
		'reservado'   => '#3858e9',
		'ganado'      => '#008a20',
		'perdido'     => '#8a8f98',
	);
}

/**
 * Etiqueta legible de un estado.
 *
 * @param string $estado Clave del estado.
 * @return string
 */
function acg_crm_estado_label( $estado ) {
	$estados = acg_crm_estados();

	return isset( $estados[ $estado ] ) ? $estados[ $estado ] : $estados['nuevo'];
}

/**
 * Un ajuste del plugin.
 *
 * @param string $clave   Nombre del ajuste.
 * @param mixed  $defecto Valor por defecto.
 * @return mixed
 */
function acg_crm_opcion( $clave, $defecto = '' ) {
	$opciones = (array) get_option( 'acg_crm_opciones', array() );

	return isset( $opciones[ $clave ] ) && '' !== $opciones[ $clave ] ? $opciones[ $clave ] : $defecto;
}

/**
 * Dirección que recibe los avisos de nuevas solicitudes.
 *
 * Se busca primero en los ajustes del plugin, después en el Personalizador
 * del theme y, si no hay nada, en el correo del administrador del sitio.
 *
 * @return string
 */
function acg_crm_email_destino() {
	$email = acg_crm_opcion( 'email_destino' );

	if ( ! $email ) {
		$email = get_theme_mod( 'acg_email_destino', '' );
	}

	if ( ! $email ) {
		$email = get_theme_mod( 'acg_email', '' );
	}

	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * Hash de la IP de quien envía.
 *
 * Se guarda el hash y no la IP en claro: sirve igual para frenar envíos
 * repetidos desde el mismo sitio, pero deja de ser un dato personal
 * identificable si alguien accede a la base de datos.
 *
 * @return string
 */
function acg_crm_hash_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	return $ip ? wp_hash( $ip ) : '';
}

/**
 * Cadena en el idioma de la solicitud.
 *
 * El plugin no depende del theme, así que trae su propio par de textos en vez
 * de llamar a `acg_t()`: si el theme cambia, los correos siguen saliendo.
 *
 * @param string $es     Texto en español.
 * @param string $pt     Texto en portugués.
 * @param string $idioma Código de idioma.
 * @return string
 */
function acg_crm_t( $es, $pt, $idioma = 'es' ) {
	return ( 'pt' === $idioma && '' !== $pt ) ? $pt : $es;
}
