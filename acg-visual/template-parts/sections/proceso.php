<?php
/**
 * Portada: pasos del proceso de trabajo.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_query = acg_query( 'acg_proceso' );

if ( ! $acg_query->have_posts() ) {
	return;
}

acg_section_open( 'proceso' );
?>
	<div class="acg-contenedor">
		<?php
		acg_section_header(
			array(
				'numero'   => '04',
				'epigrafe' => acg_home( 'proceso_epigrafe', acg_s( 'nav_proceso' ) ),
				'titulo'   => acg_home( 'proceso_titulo' ),
				'clase'    => 'acg-seccion__cabecera--apilada',
			)
		);
		?>

		<ol class="acg-proceso">
			<?php
			$acg_indice = 0;

			while ( $acg_query->have_posts() ) :
				$acg_query->the_post();
				++$acg_indice;

				$acg_numero = acg_raw_field( 'numero', get_the_ID() );
				$acg_numero = $acg_numero ? $acg_numero : str_pad( (string) $acg_indice, 2, '0', STR_PAD_LEFT );
				?>
				<li class="acg-paso">
					<span class="acg-paso__num"><?php echo esc_html( $acg_numero ); ?></span>
					<h3 class="acg-paso__titulo"><?php echo esc_html( acg_title() ); ?></h3>
					<p class="acg-paso__texto"><?php echo esc_html( acg_excerpt( get_the_ID(), 26 ) ); ?></p>
				</li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ol>
	</div>
<?php
acg_section_close();
