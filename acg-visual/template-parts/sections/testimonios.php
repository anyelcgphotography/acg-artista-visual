<?php
/**
 * Portada: lo que dicen los clientes.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_query = acg_query( 'acg_testimonio', 6 );

if ( ! $acg_query->have_posts() ) {
	return;
}

acg_section_open( 'testimonios' );
?>
	<div class="acg-contenedor">
		<?php
		acg_section_header(
			array(
				'numero'   => '05',
				'epigrafe' => acg_home( 'testimonios_epigrafe' ),
				'titulo'   => acg_home( 'testimonios_titulo' ),
				'clase'    => 'acg-seccion__cabecera--apilada',
			)
		);
		?>

		<div class="acg-testimonios">
			<?php
			while ( $acg_query->have_posts() ) :
				$acg_query->the_post();

				$acg_autor      = acg_raw_field( 'autor', get_the_ID() );
				$acg_autor      = $acg_autor ? $acg_autor : acg_title();
				$acg_tipo       = acg_field( 'tipo' );
				$acg_estrellas  = acg_star_rating( acg_raw_field( 'valoracion', get_the_ID() ) );
				$acg_es_google  = 'google' === acg_raw_field( 'fuente', get_the_ID() );
				$acg_url_resena = acg_raw_field( 'url', get_the_ID() );
				?>
				<blockquote class="acg-cita">
					<?php if ( $acg_estrellas ) : ?>
						<?php echo $acg_estrellas; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
					<p class="acg-cita__texto">&ldquo;<?php echo esc_html( acg_excerpt( get_the_ID(), 44 ) ); ?>&rdquo;</p>
					<footer class="acg-cita__autor">
						<?php echo esc_html( $acg_autor ); ?><?php echo $acg_tipo ? ' · ' . esc_html( $acg_tipo ) : ''; ?>
					</footer>
					<?php if ( $acg_es_google ) : ?>
						<?php if ( $acg_url_resena ) : ?>
							<a class="acg-cita__fuente" href="<?php echo esc_url( $acg_url_resena ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( acg_t( 'Reseña de Google', 'Avaliação do Google' ) ); ?>
								<?php echo acg_icon( 'flecha', 12 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php else : ?>
							<span class="acg-cita__fuente"><?php echo esc_html( acg_t( 'Reseña de Google', 'Avaliação do Google' ) ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</blockquote>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php
acg_section_close();
