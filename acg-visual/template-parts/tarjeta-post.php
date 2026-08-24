<?php
/**
 * Tarjeta de entrada para los listados.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'acg-tarjeta' ); ?>>
	<a class="acg-tarjeta__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php acg_thumb( get_the_ID(), 'acg-tarjeta' ); ?>
	</a>
	<div class="acg-tarjeta__cuerpo">
		<p class="acg-tarjeta__meta"><?php echo esc_html( get_the_date() ); ?></p>
		<h2 class="acg-tarjeta__titulo"><a href="<?php the_permalink(); ?>"><?php echo esc_html( acg_title() ); ?></a></h2>
		<p class="acg-tarjeta__texto"><?php echo esc_html( acg_excerpt( get_the_ID(), 24 ) ); ?></p>
	</div>
</article>
