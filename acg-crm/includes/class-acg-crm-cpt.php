<?php
/**
 * Tipo de contenido «Solicitud»: cada lead capturado por el formulario.
 *
 * Se guarda como CPT y no como tabla propia a propósito: así hereda gratis
 * buscador, filtros, papelera, permisos, revisiones y la interfaz de listado
 * de WordPress, que es justo lo que se necesita para gestionar una cartera
 * pequeña. La única tabla propia del plugin es la del historial.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro del CPT de solicitudes.
 */
class ACG_CRM_CPT {

	/**
	 * Nombre del tipo de contenido.
	 */
	const TIPO = 'acg_lead';

	/**
	 * Engancha el registro.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registra el tipo de contenido.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			self::TIPO,
			array(
				'labels'              => array(
					'name'               => __( 'Solicitudes', 'acg-crm' ),
					'singular_name'      => __( 'Solicitud', 'acg-crm' ),
					'menu_name'          => __( 'CRM', 'acg-crm' ),
					'all_items'          => __( 'Solicitudes', 'acg-crm' ),
					'add_new'            => __( 'Añadir solicitud', 'acg-crm' ),
					'add_new_item'       => __( 'Añadir solicitud', 'acg-crm' ),
					'edit_item'          => __( 'Ficha de la solicitud', 'acg-crm' ),
					'view_item'          => __( 'Ver solicitud', 'acg-crm' ),
					'search_items'       => __( 'Buscar solicitudes', 'acg-crm' ),
					'not_found'          => __( 'Todavía no hay solicitudes.', 'acg-crm' ),
					'not_found_in_trash' => __( 'No hay solicitudes en la papelera.', 'acg-crm' ),
				),
				// Nunca públicas: son datos de clientes, no contenido del sitio.
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-groups',
				'menu_position'       => 26,
				'supports'            => array( 'title', 'editor' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}

	/**
	 * Crea una solicitud a partir de datos ya validados.
	 *
	 * @param array $datos Campos de la solicitud.
	 * @return int|WP_Error ID del lead o error.
	 */
	public static function crear( $datos ) {
		$titulo = $datos['nombre'] ? $datos['nombre'] : __( 'Solicitud sin nombre', 'acg-crm' );

		$lead_id = wp_insert_post(
			array(
				'post_type'    => self::TIPO,
				'post_title'   => $titulo,
				'post_content' => isset( $datos['mensaje'] ) ? $datos['mensaje'] : '',
				'post_status'  => 'publish',
			),
			true
		);

		if ( is_wp_error( $lead_id ) ) {
			return $lead_id;
		}

		$meta = array(
			'lead_estado'    => 'nuevo',
			'lead_email'     => isset( $datos['email'] ) ? $datos['email'] : '',
			'lead_telefono'  => isset( $datos['telefono'] ) ? $datos['telefono'] : '',
			'lead_servicio'  => isset( $datos['servicio'] ) ? $datos['servicio'] : '',
			'lead_fecha'     => isset( $datos['fecha'] ) ? $datos['fecha'] : '',
			'lead_idioma'    => isset( $datos['idioma'] ) ? $datos['idioma'] : 'es',
			'lead_origen'    => isset( $datos['origen'] ) ? $datos['origen'] : '/',
			'_acg_estado_previo' => 'nuevo',
			'_acg_consent'   => current_time( 'mysql' ),
			'_acg_ip_hash'   => acg_crm_hash_ip(),
		);

		foreach ( $meta as $clave => $valor ) {
			update_post_meta( $lead_id, $clave, $valor );
		}

		return (int) $lead_id;
	}
}
