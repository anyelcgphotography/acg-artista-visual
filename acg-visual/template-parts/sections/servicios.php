<?php
/**
 * Portada: servicios destacados y lista de encargos.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_query = acg_query(
	'acg_servicio',
	6,
	array(
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array(
				'key'     => 'destacado',
				'value'   => '1',
				'compare' => '=',
			),
			array(
				'key'     => 'destacado',
				'compare' => 'NOT EXISTS',
			),
		),
	)
);

$acg_lista = acg_lines( acg_home( 'servicios_lista' ) );

if ( ! $acg_query->have_posts() && ! $acg_lista ) {
	return;
}

acg_section_open( 'servicios' );
?>
	<div class="acg-contenedor">
		<?php
		acg_section_header(
			array(
				'numero'   => '02',
				'epigrafe' => acg_home( 'servicios_epigrafe', acg_s( 'nav_servicios' ) ),
				'titulo'   => acg_home( 'servicios_titulo' ),
				'clase'    => 'acg-seccion__cabecera--apilada',
			)
		);
		?>

		<?php if ( $acg_query->have_posts() ) : ?>
			<div class="acg-servicios">
				<?php
				while ( $acg_query->have_posts() ) :
					$acg_query->the_post();
					$acg_nota = acg_field( 'nota' );
					?>
					<article class="acg-servicio">
						<a class="acg-servicio__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php acg_thumb( get_the_ID(), 'acg-tarjeta' ); ?>
						</a>
						<div class="acg-servicio__cuerpo">
							<h3 class="acg-servicio__titulo">
								<a href="<?php the_permalink(); ?>"><?php echo esc_html( acg_title() ); ?></a>
							</h3>
							<p class="acg-servicio__texto"><?php echo esc_html( acg_excerpt( get_the_ID(), 32 ) ); ?></p>
							<?php if ( $acg_nota ) : ?>
								<p class="acg-servicio__nota"><?php echo esc_html( $acg_nota ); ?></p>
							<?php endif; ?>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<?php if ( $acg_lista ) : ?>
			<div class="acg-encargos">
				<p class="acg-encargos__titulo"><?php echo esc_html( acg_home( 'servicios_lista_titulo' ) ); ?></p>
				<ul class="acg-encargos__lista">
					<?php foreach ( $acg_lista as $acg_item ) : ?>
						<li><?php echo esc_html( $acg_item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
<?php
acg_section_close();
