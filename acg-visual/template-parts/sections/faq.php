<?php
/**
 * Portada: preguntas frecuentes.
 *
 * El acordeón usa <details>/<summary> nativos: se abren sin JavaScript, son
 * accesibles de serie y el buscador ve el texto de las respuestas aunque
 * estén plegadas.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_query = acg_query( 'acg_faq' );

if ( ! $acg_query->have_posts() ) {
	return;
}

acg_section_open( 'faq' );
?>
	<div class="acg-contenedor acg-contenedor--estrecho">
		<?php
		acg_section_header(
			array(
				'numero'   => '06',
				'epigrafe' => acg_home( 'faq_epigrafe' ),
				'titulo'   => acg_home( 'faq_titulo' ),
				'clase'    => 'acg-seccion__cabecera--apilada',
			)
		);
		?>

		<div class="acg-faq">
			<?php
			while ( $acg_query->have_posts() ) :
				$acg_query->the_post();
				?>
				<details class="acg-faq__item">
					<summary class="acg-faq__pregunta">
						<span><?php echo esc_html( acg_title() ); ?></span>
						<span class="acg-faq__signo" aria-hidden="true"></span>
					</summary>
					<div class="acg-faq__respuesta acg-prosa"><?php echo wp_kses_post( acg_content() ); ?></div>
				</details>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php
acg_section_close();
