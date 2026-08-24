<?php
/**
 * Ficha de un trabajo del portafolio.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$acg_cliente = acg_raw_field( 'cliente', get_the_ID() );
	$acg_lugar   = acg_raw_field( 'lugar', get_the_ID() );
	$acg_anio    = acg_raw_field( 'anio', get_the_ID() );

	get_template_part(
		'template-parts/cabecera-pagina',
		null,
		array(
			'epigrafe' => acg_field( 'etiqueta' ),
			'titulo'   => acg_title(),
			'imagen'   => has_post_thumbnail() ? get_post_thumbnail_id() : 0,
		)
	);
	?>

	<section class="acg-seccion acg-esquema--claro">
		<div class="acg-contenedor acg-trabajo">
			<div class="acg-trabajo__texto acg-prosa">
				<?php echo wp_kses_post( acg_content() ); ?>
			</div>

			<aside class="acg-ficha">
				<?php if ( $acg_cliente ) : ?>
					<div class="acg-ficha__dato"><span><?php esc_html_e( 'Cliente', 'acg-visual' ); ?></span><strong><?php echo esc_html( $acg_cliente ); ?></strong></div>
				<?php endif; ?>
				<?php if ( $acg_lugar ) : ?>
					<div class="acg-ficha__dato"><span><?php esc_html_e( 'Lugar', 'acg-visual' ); ?></span><strong><?php echo esc_html( $acg_lugar ); ?></strong></div>
				<?php endif; ?>
				<?php if ( $acg_anio ) : ?>
					<div class="acg-ficha__dato"><span><?php esc_html_e( 'Año', 'acg-visual' ); ?></span><strong><?php echo esc_html( $acg_anio ); ?></strong></div>
				<?php endif; ?>
				<?php
				$acg_cats = get_the_term_list( get_the_ID(), 'acg_categoria', '', ', ' );
				if ( $acg_cats && ! is_wp_error( $acg_cats ) ) :
					?>
					<div class="acg-ficha__dato"><span><?php echo esc_html( acg_s( 'categoria' ) ); ?></span><strong><?php echo wp_kses_post( $acg_cats ); ?></strong></div>
				<?php endif; ?>

				<a class="acg-btn acg-btn--acento acg-btn--sm" href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>"><?php echo esc_html( acg_s( 'pedir_presupuesto' ) ); ?></a>
			</aside>
		</div>
	</section>

	<?php
	// Más trabajos de la misma categoría; si no hay, los más recientes.
	$acg_cats_ids = wp_get_post_terms( get_the_ID(), 'acg_categoria', array( 'fields' => 'ids' ) );
	$acg_args     = array( 'post__not_in' => array( get_the_ID() ) );

	if ( ! is_wp_error( $acg_cats_ids ) && $acg_cats_ids ) {
		$acg_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'acg_categoria',
				'field'    => 'term_id',
				'terms'    => $acg_cats_ids,
			),
		);
	}

	$acg_relacionados = acg_query( 'acg_trabajo', 3, $acg_args );

	if ( $acg_relacionados->have_posts() ) :
		?>
		<section class="acg-seccion acg-esquema--oscuro">
			<div class="acg-contenedor">
				<h2 class="acg-titulo acg-titulo--sm"><?php echo esc_html( acg_s( 'relacionados' ) ); ?></h2>
				<div class="acg-mosaico" style="--acg-cols: 3;">
					<?php
					while ( $acg_relacionados->have_posts() ) :
						$acg_relacionados->the_post();
						?>
						<figure class="acg-tarjeta-foto" style="aspect-ratio: <?php echo esc_attr( acg_aspect_ratio() ); ?>;">
							<a href="<?php the_permalink(); ?>" class="acg-tarjeta-foto__enlace">
								<?php acg_thumb( get_the_ID(), 'acg-vertical' ); ?>
								<span class="screen-reader-text"><?php echo esc_html( acg_title() ); ?></span>
							</a>
							<figcaption class="acg-tarjeta-foto__pie"><?php echo esc_html( acg_title() ); ?></figcaption>
						</figure>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
