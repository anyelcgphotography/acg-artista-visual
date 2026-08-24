<?php
/**
 * Portada: equipo de trabajo (cámara, flash y ópticas).
 *
 * Toda la sección se puede ocultar con un interruptor en
 * Personalizar → Secciones de la portada; la comprobación la hace
 * front-page.php antes de incluir este archivo.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_titulo = acg_home( 'equipo_titulo', __( 'Equipo de trabajo', 'acg-visual' ) );
$acg_texto  = acg_home( 'equipo_texto' );
$acg_query  = acg_query( 'acg_equipo' );

if ( ! $acg_query->have_posts() ) {
	return;
}

// Dos columnas: cuerpo y flash a un lado, ópticas al otro. Si alguien no usa
// el campo «grupo», todo cae en la primera y la sección sigue leyéndose bien.
$acg_columnas = array( 'cuerpo' => array(), 'optica' => array() );

while ( $acg_query->have_posts() ) {
	$acg_query->the_post();
	$acg_grupo = acg_raw_field( 'grupo', get_the_ID() );
	$acg_grupo = isset( $acg_columnas[ $acg_grupo ] ) ? $acg_grupo : 'cuerpo';

	$acg_columnas[ $acg_grupo ][] = acg_title();
}
wp_reset_postdata();

acg_section_open( 'equipo', array( 'ancla' => 'equipo' ) );
?>
	<div class="acg-contenedor acg-equipo">
		<div class="acg-equipo__intro">
			<h2 class="acg-titulo acg-titulo--sm"><?php echo esc_html( $acg_titulo ); ?></h2>
			<?php if ( $acg_texto ) : ?>
				<p class="acg-equipo__texto"><?php echo esc_html( $acg_texto ); ?></p>
			<?php endif; ?>
		</div>

		<?php foreach ( $acg_columnas as $acg_clave => $acg_piezas ) : ?>
			<?php if ( ! $acg_piezas ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<ul class="acg-equipo__lista">
				<?php foreach ( $acg_piezas as $acg_pieza ) : ?>
					<li><?php echo esc_html( $acg_pieza ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
<?php
acg_section_close();
