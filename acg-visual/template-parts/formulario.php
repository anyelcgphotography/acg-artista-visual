<?php
/**
 * Formulario de contacto.
 *
 * Es el mismo bloque en la portada y en cualquier página que active la
 * opción «Añadir el formulario de contacto al final», así que vive en su
 * propio template-part.
 *
 * El envío lo recoge el plugin ACG CRM por AJAX y crea un lead. Si el plugin
 * no está activo, el formulario no se pierde: el botón cambia a «Enviar por
 * WhatsApp» y compone el mensaje con lo que se haya escrito, que es
 * exactamente lo que hacía la maqueta original.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_crm       = defined( 'ACG_CRM_VERSION' );
$acg_servicios = acg_query( 'acg_servicio', 20 );
$acg_privacidad = get_option( 'wp_page_for_privacy_policy' );
?>
<?php
// Sin JavaScript el formulario se envía a admin-post.php y vuelve a esta
// página con ?acg_envio=ok. Con JavaScript, theme.js intercepta el envío y lo
// manda por AJAX, que es lo que se ve en el 99 % de los casos.
$acg_aviso_envio = isset( $_GET['acg_envio'] ) ? sanitize_key( wp_unslash( $_GET['acg_envio'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<?php if ( 'ok' === $acg_aviso_envio ) : ?>
	<p class="acg-form__aviso is-ok"><?php echo esc_html( acg_s( 'form_ok' ) ); ?></p>
<?php elseif ( 'error' === $acg_aviso_envio ) : ?>
	<p class="acg-form__aviso is-error"><?php echo esc_html( acg_s( 'form_error' ) ); ?></p>
<?php endif; ?>

<form
	class="acg-form"
	data-acg-form
	data-modo="<?php echo $acg_crm ? 'crm' : 'whatsapp'; ?>"
	data-whatsapp="<?php echo esc_attr( acg_whatsapp_url() ); ?>"
	method="post"
	action="<?php echo esc_url( $acg_crm ? admin_url( 'admin-post.php' ) : '' ); ?>"
	novalidate
>
	<input type="hidden" name="action" value="acg_submit_lead">
	<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'acg_lead_form' ) ); ?>">
	<div class="acg-form__fila">
		<label class="acg-campo">
			<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_nombre' ) ); ?> *</span>
			<input type="text" name="nombre" required autocomplete="name">
		</label>

		<label class="acg-campo">
			<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_email' ) ); ?> *</span>
			<input type="email" name="email" required autocomplete="email">
		</label>
	</div>

	<div class="acg-form__fila">
		<label class="acg-campo">
			<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_telefono' ) ); ?></span>
			<input type="tel" name="telefono" autocomplete="tel">
		</label>

		<label class="acg-campo">
			<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_fecha' ) ); ?></span>
			<input type="date" name="fecha">
		</label>
	</div>

	<label class="acg-campo">
		<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_servicio' ) ); ?></span>
		<select name="servicio">
			<option value=""><?php echo esc_html( acg_s( 'form_elige' ) ); ?></option>
			<?php
			while ( $acg_servicios->have_posts() ) :
				$acg_servicios->the_post();
				?>
				<option value="<?php echo esc_attr( acg_title() ); ?>" data-id="<?php the_ID(); ?>"><?php echo esc_html( acg_title() ); ?></option>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
			<option value="<?php echo esc_attr( acg_s( 'form_otro' ) ); ?>"><?php echo esc_html( acg_s( 'form_otro' ) ); ?></option>
		</select>
	</label>

	<label class="acg-campo">
		<span class="acg-campo__label"><?php echo esc_html( acg_s( 'form_mensaje' ) ); ?></span>
		<textarea name="mensaje" rows="5"></textarea>
	</label>

	<label class="acg-campo acg-campo--check">
		<input type="checkbox" name="acepto" value="1" required>
		<span>
			<?php echo esc_html( acg_s( 'form_privacidad' ) ); ?>
			<?php if ( $acg_privacidad ) : ?>
				<a href="<?php echo esc_url( get_permalink( $acg_privacidad ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( get_the_title( $acg_privacidad ) ); ?></a>
			<?php endif; ?>
		</span>
	</label>

	<?php // Trampa para bots: un humano nunca ve este campo, así que si llega relleno se descarta el envío. ?>
	<div class="acg-honeypot" aria-hidden="true">
		<label>
			<?php esc_html_e( 'No rellenes este campo', 'acg-visual' ); ?>
			<input type="text" name="acg_web" tabindex="-1" autocomplete="off">
		</label>
	</div>

	<input type="hidden" name="inicio" value="<?php echo esc_attr( time() ); ?>">
	<input type="hidden" name="idioma" value="<?php echo esc_attr( acg_lang() ); ?>">
	<input type="hidden" name="origen" value="<?php echo esc_attr( wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ) ); ?>">

	<div class="acg-form__acciones">
		<button type="submit" class="acg-btn acg-btn--acento"><?php echo esc_html( acg_s( 'form_enviar' ) ); ?></button>

		<?php if ( acg_whatsapp_url() ) : ?>
			<a class="acg-form__wa" href="<?php echo esc_url( acg_whatsapp_url() ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo acg_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( acg_s( 'o_whatsapp' ) ); ?>
			</a>
		<?php endif; ?>
	</div>

	<p class="acg-form__aviso" data-acg-aviso role="status" aria-live="polite"></p>
</form>
