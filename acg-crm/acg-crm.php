<?php
/**
 * Plugin Name:       ACG CRM — Solicitudes y clientes
 * Plugin URI:        https://adrydigital.com
 * Description:       CRM ligero para captar clientes desde el formulario de contacto: guarda cada solicitud como un lead con su estado y su historial, avisa por correo y resume la actividad en el Escritorio. Diseñado para el theme ACG Artista Visual, funciona con cualquier theme mediante el shortcode [acg_formulario].
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Adry Digital
 * Author URI:        https://adrydigital.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acg-crm
 * Domain Path:       /languages
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

define( 'ACG_CRM_VERSION', '1.0.0' );
define( 'ACG_CRM_FILE', __FILE__ );
define( 'ACG_CRM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACG_CRM_URL', plugin_dir_url( __FILE__ ) );
define( 'ACG_CRM_BASENAME', plugin_basename( __FILE__ ) );

require_once ACG_CRM_PATH . 'includes/helpers.php';
require_once ACG_CRM_PATH . 'includes/class-acg-crm-cpt.php';
require_once ACG_CRM_PATH . 'includes/class-acg-crm-actividad.php';
require_once ACG_CRM_PATH . 'includes/class-acg-crm-formulario.php';
require_once ACG_CRM_PATH . 'includes/class-acg-crm-emails.php';
require_once ACG_CRM_PATH . 'includes/class-acg-crm-ajustes.php';
require_once ACG_CRM_PATH . 'admin/class-acg-crm-admin.php';
require_once ACG_CRM_PATH . 'admin/class-acg-crm-escritorio.php';

ACG_CRM_CPT::init();
ACG_CRM_Actividad::init();
ACG_CRM_Formulario::init();
ACG_CRM_Emails::init();
ACG_CRM_Ajustes::init();
ACG_CRM_Admin::init();
ACG_CRM_Escritorio::init();

/**
 * Al activar: registra los tipos, crea la tabla de historial y refresca los
 * enlaces permanentes.
 *
 * @return void
 */
function acg_crm_activar() {
	ACG_CRM_CPT::register();
	ACG_CRM_Actividad::instalar_tabla();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'acg_crm_activar' );

/**
 * Al desactivar solo se limpian las reglas de reescritura.
 *
 * Los leads y su historial se quedan donde están: son datos del cliente, y
 * desactivar un plugin no puede ser una forma de borrar su cartera. Para
 * eliminarlos hay que borrarlos a mano desde la lista de solicitudes.
 *
 * @return void
 */
function acg_crm_desactivar() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'acg_crm_desactivar' );

/**
 * Carga las traducciones.
 *
 * @return void
 */
function acg_crm_textdomain() {
	load_plugin_textdomain( 'acg-crm', false, dirname( ACG_CRM_BASENAME ) . '/languages' );
}
add_action( 'init', 'acg_crm_textdomain' );

/**
 * Enlace a los ajustes desde la lista de plugins.
 *
 * @param array $enlaces Enlaces actuales.
 * @return array
 */
function acg_crm_enlace_ajustes( $enlaces ) {
	$ajustes = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'edit.php?post_type=acg_lead&page=acg-crm-ajustes' ) ),
		esc_html__( 'Ajustes', 'acg-crm' )
	);

	array_unshift( $enlaces, $ajustes );

	return $enlaces;
}
add_filter( 'plugin_action_links_' . ACG_CRM_BASENAME, 'acg_crm_enlace_ajustes' );
