<?php
/**
 * Historial de cada solicitud: notas, llamadas y cambios de estado.
 *
 * Va en su propia tabla y no como post meta porque son muchas filas por lead
 * y se leen ordenadas por fecha; con meta habría que serializar un array
 * entero y reescribirlo en cada nota.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gestión del historial.
 */
class ACG_CRM_Actividad {

	/**
	 * Versión del esquema de la tabla.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Engancha los hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'quizas_actualizar' ) );
		add_action( 'save_post_' . ACG_CRM_CPT::TIPO, array( __CLASS__, 'registrar_cambio_estado' ), 20, 2 );
		add_action( 'admin_post_acg_crm_nota', array( __CLASS__, 'guardar_nota' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'borrar_historial' ) );
	}

	/**
	 * Nombre completo de la tabla.
	 *
	 * @return string
	 */
	public static function tabla() {
		global $wpdb;

		return $wpdb->prefix . 'acg_actividad';
	}

	/**
	 * Crea o actualiza la tabla.
	 *
	 * @return void
	 */
	public static function instalar_tabla() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tabla   = self::tabla();
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tabla} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			lead_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			tipo VARCHAR(32) NOT NULL DEFAULT 'nota',
			contenido LONGTEXT NULL,
			creado DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY lead_idx (lead_id),
			KEY creado_idx (creado)
		) {$collate};";

		dbDelta( $sql );

		update_option( 'acg_crm_db_version', self::DB_VERSION );
	}

	/**
	 * Recrea la tabla si cambió el esquema —o si el plugin se copió a mano
	 * sin pasar por el hook de activación.
	 *
	 * @return void
	 */
	public static function quizas_actualizar() {
		if ( get_option( 'acg_crm_db_version' ) === self::DB_VERSION ) {
			return;
		}

		self::instalar_tabla();
	}

	/**
	 * Añade una entrada al historial.
	 *
	 * @param int    $lead_id   ID de la solicitud.
	 * @param string $tipo      'nota', 'estado', 'sistema'.
	 * @param string $contenido Texto (admite HTML sencillo).
	 * @param int    $user_id   Autor; 0 si lo genera el sistema.
	 * @return void
	 */
	public static function add( $lead_id, $tipo, $contenido, $user_id = 0 ) {
		global $wpdb;

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::tabla(),
			array(
				'lead_id'   => (int) $lead_id,
				'user_id'   => $user_id ? (int) $user_id : get_current_user_id(),
				'tipo'      => sanitize_key( $tipo ),
				'contenido' => wp_kses_post( $contenido ),
				'creado'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Historial de una solicitud, de lo más reciente a lo más antiguo.
	 *
	 * @param int $lead_id ID de la solicitud.
	 * @param int $limite  Número máximo de entradas.
	 * @return array<int,object>
	 */
	public static function get( $lead_id, $limite = 30 ) {
		global $wpdb;

		$tabla = self::tabla();

		return $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT * FROM {$tabla} WHERE lead_id = %d ORDER BY creado DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL
				$lead_id,
				$limite
			)
		);
	}

	/**
	 * Últimas entradas de todo el CRM, para el widget del Escritorio.
	 *
	 * @param int $limite Número de entradas.
	 * @return array<int,object>
	 */
	public static function recientes( $limite = 6 ) {
		global $wpdb;

		$tabla = self::tabla();

		return $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT * FROM {$tabla} ORDER BY creado DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL
				$limite
			)
		);
	}

	/**
	 * Deja constancia de cada cambio de estado sin que haya que escribirlo.
	 *
	 * @param int     $post_id ID de la solicitud.
	 * @param WP_Post $post    Objeto del post.
	 * @return void
	 */
	public static function registrar_cambio_estado( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}

		$actual  = get_post_meta( $post_id, 'lead_estado', true );
		$previo  = get_post_meta( $post_id, '_acg_estado_previo', true );

		if ( ! $actual || $actual === $previo ) {
			return;
		}

		self::add(
			$post_id,
			'estado',
			sprintf(
				/* translators: %s: nombre del estado. */
				__( 'Estado cambiado a <strong>%s</strong>.', 'acg-crm' ),
				esc_html( acg_crm_estado_label( $actual ) )
			)
		);

		update_post_meta( $post_id, '_acg_estado_previo', $actual );
	}

	/**
	 * Guarda una nota escrita desde la ficha de la solicitud.
	 *
	 * @return void
	 */
	public static function guardar_nota() {
		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;

		if ( ! $lead_id || ! current_user_can( 'edit_post', $lead_id ) ) {
			wp_die( esc_html__( 'No tienes permiso para añadir notas a esta solicitud.', 'acg-crm' ) );
		}

		check_admin_referer( 'acg_crm_nota_' . $lead_id );

		$texto = isset( $_POST['nota'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nota'] ) ) : '';
		$tipo  = isset( $_POST['tipo'] ) ? sanitize_key( wp_unslash( $_POST['tipo'] ) ) : 'nota';

		if ( '' !== trim( $texto ) ) {
			self::add( $lead_id, $tipo, $texto );
		}

		wp_safe_redirect( get_edit_post_link( $lead_id, '' ) );
		exit;
	}

	/**
	 * Al borrar definitivamente una solicitud, se lleva su historial: si no,
	 * la tabla acumularía filas huérfanas para siempre.
	 *
	 * @param int $post_id ID del post que se borra.
	 * @return void
	 */
	public static function borrar_historial( $post_id ) {
		if ( ACG_CRM_CPT::TIPO !== get_post_type( $post_id ) ) {
			return;
		}

		global $wpdb;

		$wpdb->delete( self::tabla(), array( 'lead_id' => (int) $post_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
}
