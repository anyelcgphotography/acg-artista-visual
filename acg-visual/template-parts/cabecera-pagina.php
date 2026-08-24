<?php
/**
 * Cabecera interior compartida por páginas, entradas y archivos.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'epigrafe' => '',
		'titulo'   => '',
		'texto'    => '',
		'imagen'   => 0,
		'migas'    => true,
	)
);
?>
<header class="acg-cabecera-interior acg-esquema--oscuro<?php echo $acg_args['imagen'] ? ' acg-cabecera-interior--con-foto' : ''; ?>">
	<?php if ( $acg_args['imagen'] ) : ?>
		<div class="acg-cabecera-interior__fondo">
			<?php echo wp_get_attachment_image( (int) $acg_args['imagen'], 'full', false, array( 'class' => 'acg-thumb', 'decoding' => 'async', 'alt' => '' ) ); ?>
			<span class="acg-hero__velo" aria-hidden="true"></span>
		</div>
	<?php endif; ?>

	<div class="acg-contenedor">
		<?php if ( $acg_args['migas'] ) : ?>
			<?php acg_breadcrumbs(); ?>
		<?php endif; ?>

		<?php if ( $acg_args['epigrafe'] ) : ?>
			<p class="acg-epigrafe"><?php echo esc_html( $acg_args['epigrafe'] ); ?></p>
		<?php endif; ?>

		<h1 class="acg-titulo"><?php echo esc_html( $acg_args['titulo'] ); ?></h1>

		<?php if ( $acg_args['texto'] ) : ?>
			<p class="acg-cabecera-interior__texto"><?php echo esc_html( $acg_args['texto'] ); ?></p>
		<?php endif; ?>
	</div>
</header>
