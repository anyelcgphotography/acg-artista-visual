<?php
/**
 * Archivo del portafolio: el mosaico completo, con filtros por categoría.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

$acg_terminos = get_terms(
	array(
		'taxonomy'   => 'acg_categoria',
		'hide_empty' => true,
	)
);

get_template_part(
	'template-parts/cabecera-pagina',
	null,
	array(
		'epigrafe' => acg_s( 'nav_portafolio' ),
		'titulo'   => is_tax() ? single_term_title( '', false ) : acg_home( 'portafolio_titulo', acg_s( 'nav_portafolio' ) ),
		'texto'    => is_tax() ? wp_strip_all_tags( term_description() ) : acg_home( 'portafolio_texto' ),
		'migas'    => true,
	)
);
?>

<section class="acg-seccion acg-esquema--claro">
	<div class="acg-contenedor">
		<?php if ( ! is_wp_error( $acg_terminos ) && count( $acg_terminos ) > 1 ) : ?>
			<div class="acg-filtros">
				<a class="acg-chip<?php echo is_post_type_archive() ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'acg_trabajo' ) ); ?>"><?php echo esc_html( acg_s( 'todo' ) ); ?></a>
				<?php foreach ( $acg_terminos as $acg_termino ) : ?>
					<a class="acg-chip<?php echo is_tax( 'acg_categoria', $acg_termino->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $acg_termino ) ); ?>">
						<?php echo esc_html( acg_t( $acg_termino->name, get_term_meta( $acg_termino->term_id, 'nombre_pt', true ) ) ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="acg-mosaico" style="--acg-cols: 3;">
				<?php
				while ( have_posts() ) :
					the_post();

					$acg_etiqueta = acg_field( 'etiqueta' );
					$acg_ancha    = acg_is_wide();
					?>
					<figure
						class="acg-tarjeta-foto<?php echo $acg_ancha ? ' acg-tarjeta-foto--ancha' : ''; ?>"
						style="aspect-ratio: <?php echo esc_attr( acg_aspect_ratio() ); ?>;"
					>
						<a href="<?php the_permalink(); ?>" class="acg-tarjeta-foto__enlace">
							<?php acg_thumb( get_the_ID(), $acg_ancha ? 'acg-apaisada' : 'acg-vertical' ); ?>
							<span class="screen-reader-text"><?php echo esc_html( acg_title() ); ?></span>
						</a>
						<?php if ( $acg_etiqueta ) : ?>
							<figcaption class="acg-tarjeta-foto__pie"><?php echo esc_html( $acg_etiqueta ); ?></figcaption>
						<?php endif; ?>
					</figure>
					<?php
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
