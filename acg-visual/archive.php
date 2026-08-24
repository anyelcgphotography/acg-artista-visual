<?php
/**
 * Listado genérico: blog, archivos y cualquier consulta sin plantilla propia.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/cabecera-pagina',
	null,
	array(
		'titulo' => is_home() ? get_bloginfo( 'name' ) : wp_strip_all_tags( get_the_archive_title() ),
		'texto'  => wp_strip_all_tags( get_the_archive_description() ),
		'migas'  => false,
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

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
				)
			);
			?>
		<?php else : ?>
			<p class="acg-vacio"><?php echo esc_html( acg_s( 'sin_resultados' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
