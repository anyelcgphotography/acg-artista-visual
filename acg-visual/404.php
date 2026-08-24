<?php
/**
 * Página no encontrada.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="acg-seccion acg-esquema--oscuro acg-404">
	<div class="acg-contenedor acg-contenedor--estrecho">
		<p class="acg-epigrafe">404</p>
		<h1 class="acg-titulo"><?php echo esc_html( acg_s( 'error_404' ) ); ?></h1>
		<p class="acg-404__texto"><?php echo esc_html( acg_s( 'error_404_texto' ) ); ?></p>

		<div class="acg-hero__botones">
			<a class="acg-btn acg-btn--acento" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( acg_s( 'inicio' ) ); ?></a>
			<a class="acg-btn acg-btn--linea" href="<?php echo esc_url( get_post_type_archive_link( 'acg_trabajo' ) ); ?>"><?php echo esc_html( acg_s( 'ver_portafolio' ) ); ?></a>
		</div>

		<div class="acg-404__buscador"><?php get_search_form(); ?></div>
	</div>
</section>
<?php
get_footer();
