<?php
/**
 * Portada.
 *
 * Cada bloque es un template-part y se pinta solo si su interruptor está
 * encendido en Personalizar → Secciones de la portada.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

foreach ( array_keys( acg_sections() ) as $seccion ) {
	if ( acg_section_active( $seccion ) ) {
		get_template_part( 'template-parts/sections/' . $seccion );
	}
}

get_footer();
