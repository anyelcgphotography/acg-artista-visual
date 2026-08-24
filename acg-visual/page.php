<?php
/**
 * Página estándar.
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
			'epigrafe' => acg_field( 'pagina_epigrafe' ),
			'titulo'   => acg_title(),
			'imagen'   => (int) acg_raw_field( 'pagina_imagen', get_the_ID() ),
		)
	);
	?>

	<section class="acg-seccion acg-esquema--claro">
		<div class="acg-contenedor acg-contenedor--estrecho acg-prosa">
			<?php echo wp_kses_post( acg_content() ); ?>
			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="acg-paginas-post">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	</section>

	<?php if ( acg_raw_field( 'pagina_formulario', get_the_ID() ) ) : ?>
		<section class="acg-seccion acg-esquema--oscuro">
			<div class="acg-contenedor acg-contenedor--estrecho">
				<h2 class="acg-titulo acg-titulo--sm"><?php echo esc_html( acg_s( 'nav_contacto' ) ); ?></h2>
				<?php get_template_part( 'template-parts/formulario' ); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
