<?php
/**
 * Formulario de búsqueda.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="acg-buscador" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="acg-buscar-<?php echo esc_attr( wp_unique_id() ); ?>"><?php echo esc_html( acg_s( 'buscar' ) ); ?></label>
	<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( acg_s( 'buscar' ) ); ?>">
	<button type="submit" aria-label="<?php echo esc_attr( acg_s( 'buscar' ) ); ?>">
		<?php echo acg_icon( 'buscar', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</form>
