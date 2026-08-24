<?php
/**
 * Punto de entrada del theme ACG Artista Visual.
 *
 * Aquí solo se definen las constantes y se cargan los módulos: cada
 * responsabilidad vive en su propio archivo dentro de inc/.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

define( 'ACG_VERSION', '1.0.0' );
define( 'ACG_DIR', get_template_directory() );
define( 'ACG_URI', get_template_directory_uri() );

require_once ACG_DIR . '/inc/setup.php';
require_once ACG_DIR . '/inc/i18n.php';
require_once ACG_DIR . '/inc/template-tags.php';
require_once ACG_DIR . '/inc/enqueue.php';
require_once ACG_DIR . '/inc/customizer.php';
require_once ACG_DIR . '/inc/layout-builder.php';
require_once ACG_DIR . '/inc/acf-fields.php';
require_once ACG_DIR . '/inc/meta-fallback.php';
require_once ACG_DIR . '/inc/demo-importer.php';
require_once ACG_DIR . '/inc/admin-notices.php';
