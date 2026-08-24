<?php
/**
 * Avisos del panel: los tres pasos que dejan el sitio funcionando.
 *
 * El theme funciona sin ACF y sin el CRM, pero degradado. En vez de fallar en
 * silencio, avisa de qué falta y enlaza directamente a donde se arregla.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Avisa de lo que falta por configurar.
 *
 * @return void
 */
function acg_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$pantalla = get_current_screen();

	// Solo en las pantallas donde el aviso es accionable: el escritorio, la
	// lista de plugins y las de apariencia. En el editor de una entrada estorba.
	if ( ! $pantalla || ! in_array( $pantalla->id, array( 'dashboard', 'plugins', 'themes', 'appearance_page_acg-demo' ), true ) ) {
		return;
	}

	acg_notice_demo();
	acg_notice_acf();
	acg_notice_crm();
	acg_notice_portada();
}
add_action( 'admin_notices', 'acg_admin_notices' );

/**
 * Invitación a importar la demo, justo después de activar el theme.
 *
 * @return void
 */
function acg_notice_demo() {
	if ( ! get_transient( 'acg_mostrar_demo' ) || ! current_user_can( 'import' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info is-dismissible"><p><strong>%s</strong> %s</p><p><a class="button button-primary" href="%s">%s</a></p></div>',
		esc_html__( 'Theme ACG Artista Visual activado.', 'acg-visual' ),
		esc_html__( 'Puedes cargar el contenido de ejemplo y tener la web montada en un minuto.', 'acg-visual' ),
		esc_url( admin_url( 'themes.php?page=' . ACG_Demo_Importer::PAGE ) ),
		esc_html__( 'Importar contenido demo', 'acg-visual' )
	);

	delete_transient( 'acg_mostrar_demo' );
}

/**
 * Recomienda instalar ACF.
 *
 * @return void
 */
function acg_notice_acf() {
	if ( acg_has_acf() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p><p><a class="button" href="%s">%s</a></p></div>',
		esc_html__( 'Advanced Custom Fields no está activo.', 'acg-visual' ),
		esc_html__( 'El sitio sigue siendo editable —los mismos campos aparecen como cajas normales al editar cada página—, pero con ACF la edición es bastante más cómoda: selector visual de imágenes, editor enriquecido y campos agrupados.', 'acg-visual' ),
		esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ),
		esc_html__( 'Instalar Advanced Custom Fields', 'acg-visual' )
	);
}

/**
 * Avisa de que sin el plugin del CRM el formulario no guarda leads.
 *
 * @return void
 */
function acg_notice_crm() {
	if ( defined( 'ACG_CRM_VERSION' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'El plugin ACG CRM no está activo.', 'acg-visual' ),
		esc_html__( 'Mientras no lo esté, el formulario de contacto no guarda las solicitudes: el botón de enviar abre WhatsApp con el mensaje ya escrito, como hacía la maqueta.', 'acg-visual' )
	);
}

/**
 * Avisa si el sitio no tiene una página asignada como portada: sin ella, los
 * campos de la home no tienen dónde guardarse.
 *
 * @return void
 */
function acg_notice_portada() {
	if ( acg_front_id() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p><p><a class="button" href="%s">%s</a></p></div>',
		esc_html__( 'La portada todavía muestra las últimas entradas.', 'acg-visual' ),
		esc_html__( 'Los textos de la página de inicio se editan en la página que esté asignada como portada. Asigna una en Ajustes → Lectura, o importa el contenido demo y se configura sola.', 'acg-visual' ),
		esc_url( admin_url( 'options-reading.php' ) ),
		esc_html__( 'Ir a Ajustes de lectura', 'acg-visual' )
	);
}

/**
 * Atajo a la edición de la portada desde la barra de administración: es la
 * página que más se toca y, al ser la home, no hay un «Editar página» obvio.
 *
 * @param WP_Admin_Bar $barra Barra de administración.
 * @return void
 */
function acg_admin_bar_portada( $barra ) {
	if ( ! is_front_page() || ! acg_front_id() || ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	$barra->add_node(
		array(
			'id'    => 'acg-editar-portada',
			'title' => __( 'Editar contenido de la portada', 'acg-visual' ),
			'href'  => get_edit_post_link( acg_front_id(), '' ),
		)
	);
}
add_action( 'admin_bar_menu', 'acg_admin_bar_portada', 90 );
