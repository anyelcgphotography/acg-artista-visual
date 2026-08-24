<?php
/**
 * Ficha de un servicio, con el formulario de contacto al final.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part(
		'template-parts/cabecera-pagina',
		null,
		array(
			'epigrafe' => acg_s( 'nav_servicios' ),
			'titulo'   => acg_title(),
			'texto'    => acg_field( 'nota' ),
			'imagen'   => has_post_thumbnail() ? get_post_thumbnail_id() : 0,
		)
	);
	?>

	<section class="acg-seccion acg-esquema--claro">
		<div class="acg-contenedor acg-contenedor--estrecho acg-prosa">
			<?php echo wp_kses_post( acg_content() ); ?>
		</div>
	</section>

	<section class="acg-seccion acg-esquema--oscuro">
		<div class="acg-contenedor acg-contenedor--estrecho">
			<h2 class="acg-titulo acg-titulo--sm"><?php echo esc_html( acg_home( 'contacto_titulo', acg_s( 'pedir_presupuesto' ) ) ); ?></h2>
			<?php get_template_part( 'template-parts/formulario' ); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
