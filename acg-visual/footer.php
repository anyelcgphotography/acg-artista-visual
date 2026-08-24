<?php
/**
 * Pie del sitio y cierre del documento.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="acg-pie acg-esquema--<?php echo esc_attr( get_theme_mod( 'acg_pie_esquema', 'claro' ) ); ?>">
	<?php acg_render_layout( 'footer' ); ?>
</footer>

<?php if ( acg_whatsapp_url() ) : ?>
	<a class="acg-wa-flotante" href="<?php echo esc_url( acg_whatsapp_url() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
		<?php echo acg_icon( 'whatsapp', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
