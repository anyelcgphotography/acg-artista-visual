<?php
/**
 * Archivo de servicios.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/cabecera-pagina',
	null,
	array(
		'epigrafe' => acg_home( 'servicios_epigrafe', acg_s( 'nav_servicios' ) ),
		'titulo'   => acg_home( 'servicios_titulo', acg_s( 'nav_servicios' ) ),
		'migas'    => true,
	)
);
?>

<section class="acg-seccion acg-esquema--oscuro">
	<div class="acg-contenedor">
		<?php if ( have_posts() ) : ?>
			<div class="acg-servicios">
				<?php
				while ( have_posts() ) :
					the_post();
					$acg_nota = acg_field( 'nota' );
					?>
					<article class="acg-servicio">
						<a class="acg-servicio__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php acg_thumb( get_the_ID(), 'acg-tarjeta' ); ?>
						</a>
						<div class="acg-servicio__cuerpo">
							<h2 class="acg-servicio__titulo"><a href="<?php the_permalink(); ?>"><?php echo esc_html( acg_title() ); ?></a></h2>
							<p class="acg-servicio__texto"><?php echo esc_html( acg_excerpt( get_the_ID(), 34 ) ); ?></p>
							<?php if ( $acg_nota ) : ?>
								<p class="acg-servicio__nota"><?php echo esc_html( $acg_nota ); ?></p>
							<?php endif; ?>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="acg-vacio"><?php echo esc_html( acg_s( 'sin_resultados' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
