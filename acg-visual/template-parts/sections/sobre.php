<?php
/**
 * Portada: sobre la fotógrafa.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_imagen = (int) acg_home_raw( 'sobre_imagen', 0 );
$acg_texto  = acg_home( 'sobre_texto' );
$acg_titulo = acg_home( 'sobre_titulo' );

if ( ! $acg_texto && ! $acg_titulo ) {
	return;
}

acg_section_open( 'sobre' );
?>
	<div class="acg-contenedor acg-sobre">
		<?php if ( $acg_imagen ) : ?>
			<div class="acg-sobre__media">
				<div class="acg-sobre__marco">
					<?php echo wp_get_attachment_image( $acg_imagen, 'acg-vertical', false, array( 'class' => 'acg-thumb', 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( $acg_titulo ) ) ); ?>
				</div>
				<span class="acg-sobre__hexagono" aria-hidden="true"><?php acg_monograma( 120 ); ?></span>
			</div>
		<?php endif; ?>

		<div class="acg-sobre__texto">
			<?php
			acg_section_header(
				array(
					'numero'   => '03',
					'epigrafe' => acg_home( 'sobre_epigrafe', acg_s( 'nav_sobre' ) ),
					'titulo'   => $acg_titulo,
					'clase'    => 'acg-seccion__cabecera--apilada',
				)
			);
			?>

			<div class="acg-prosa"><?php echo wp_kses_post( wpautop( $acg_texto ) ); ?></div>

			<div class="acg-sobre__enlaces">
				<?php foreach ( acg_social_networks() as $acg_id => $acg_datos ) : ?>
					<?php $acg_url = get_theme_mod( 'acg_social_' . $acg_id, $acg_datos['default'] ); ?>
					<?php if ( $acg_url ) : ?>
						<a class="acg-btn acg-btn--linea acg-btn--sm" href="<?php echo esc_url( $acg_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $acg_datos['label'] ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php
acg_section_close();
