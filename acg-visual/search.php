<?php
/**
 * Resultados de búsqueda.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/cabecera-pagina',
	null,
	array(
		'epigrafe' => acg_s( 'resultados_para' ),
		'titulo'   => get_search_query(),
		'migas'    => false,
	)
);
?>

<section class="acg-seccion acg-esquema--claro">
	<div class="acg-contenedor">
		<?php if ( have_posts() ) : ?>
			<div class="acg-rejilla-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/tarjeta-post' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p class="acg-vacio"><?php echo esc_html( acg_s( 'sin_resultados' ) ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
