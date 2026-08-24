<?php
/**
 * Entrada del blog.
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
			'epigrafe' => get_the_date(),
			'titulo'   => acg_title(),
			'imagen'   => has_post_thumbnail() ? get_post_thumbnail_id() : 0,
		)
	);
	?>

	<article class="acg-seccion acg-esquema--claro">
		<div class="acg-contenedor acg-contenedor--estrecho acg-prosa">
			<?php echo wp_kses_post( acg_content() ); ?>
		</div>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<section class="acg-seccion acg-esquema--claro"><div class="acg-contenedor acg-contenedor--estrecho">';
		comments_template();
		echo '</div></section>';
	}
endwhile;

get_footer();
