<?php
/**
 * Portada: bloque de apertura a pantalla completa.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_imagen   = (int) acg_home_raw( 'hero_imagen', 0 );
$acg_epigrafe = acg_home( 'hero_epigrafe' );
$acg_titulo   = acg_home( 'hero_titulo' );
$acg_texto    = acg_home( 'hero_texto' );
$acg_btn1     = acg_home( 'hero_boton_1' );
$acg_btn1_url = acg_home_raw( 'hero_boton_1_url', '#contacto' );
$acg_btn2     = acg_home( 'hero_boton_2' );
$acg_btn2_url = acg_home_raw( 'hero_boton_2_url', '#portafolio' );
$acg_equipo   = acg_home( 'hero_pie_equipo' );
$acg_estado   = acg_home( 'hero_pie_estado' );
$acg_telefono = get_theme_mod( 'acg_telefono', '' );
?>
<header id="inicio" class="acg-hero acg-esquema--oscuro" data-seccion="hero">
	<div class="acg-hero__fondo">
		<?php if ( $acg_imagen ) : ?>
			<?php
			// La foto del hero es lo primero que se ve: se carga con prioridad
			// alta y sin lazy, que si no el navegador la deja para el final.
			echo wp_get_attachment_image(
				$acg_imagen,
				'full',
				false,
				array(
					'class'         => 'acg-hero__img',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'alt'           => '',
				)
			);
			?>
		<?php endif; ?>
		<span class="acg-hero__velo" aria-hidden="true"></span>
	</div>

	<div class="acg-hero__interior">
		<?php if ( $acg_epigrafe ) : ?>
			<p class="acg-hero__epigrafe">
				<span class="acg-hero__linea" aria-hidden="true"></span>
				<?php echo esc_html( $acg_epigrafe ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $acg_titulo ) : ?>
			<h1 class="acg-hero__titulo"><?php echo esc_html( $acg_titulo ); ?></h1>
		<?php endif; ?>

		<?php if ( $acg_texto ) : ?>
			<p class="acg-hero__texto"><?php echo esc_html( $acg_texto ); ?></p>
		<?php endif; ?>

		<?php if ( $acg_btn1 || $acg_btn2 ) : ?>
			<div class="acg-hero__botones">
				<?php if ( $acg_btn1 ) : ?>
					<a class="acg-btn acg-btn--acento" href="<?php echo esc_url( $acg_btn1_url ); ?>">
						<?php echo esc_html( $acg_btn1 ); ?>
						<?php echo acg_icon( 'flecha', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( $acg_btn2 ) : ?>
					<a class="acg-btn acg-btn--linea" href="<?php echo esc_url( $acg_btn2_url ); ?>"><?php echo esc_html( $acg_btn2 ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $acg_equipo || $acg_estado || $acg_telefono ) : ?>
		<div class="acg-hero__pie">
			<?php if ( $acg_equipo ) : ?>
				<span><?php echo esc_html( $acg_equipo ); ?></span>
			<?php endif; ?>
			<?php if ( $acg_estado ) : ?>
				<span class="acg-hero__estado"><span class="acg-punto" aria-hidden="true"></span><?php echo esc_html( $acg_estado ); ?></span>
			<?php endif; ?>
			<?php if ( $acg_telefono ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $acg_telefono ) ); ?>"><?php echo esc_html( $acg_telefono ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</header>
