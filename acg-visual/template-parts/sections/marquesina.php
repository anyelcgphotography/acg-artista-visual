<?php
/**
 * Portada: cinta de especialidades en movimiento.
 *
 * El texto se duplica en el HTML porque la animación desplaza la mitad de su
 * ancho: así el bucle se cierra sin salto visible.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_texto = trim( (string) acg_home( 'marquesina_texto' ) );

if ( ! $acg_texto ) {
	return;
}
?>
<div class="acg-marquesina acg-esquema--acento" aria-hidden="true">
	<div class="acg-marquesina__pista">
		<span class="acg-marquesina__grupo"><?php echo esc_html( $acg_texto ); ?> · </span>
		<span class="acg-marquesina__grupo"><?php echo esc_html( $acg_texto ); ?> · </span>
	</div>
</div>
