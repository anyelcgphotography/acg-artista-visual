<?php
/**
 * Portada: contacto, datos directos, mapa y formulario.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_mapa      = get_theme_mod( 'acg_mapa_embed', '' );
$acg_whatsapp  = acg_whatsapp_url();
$acg_telefono  = get_theme_mod( 'acg_telefono', '' );
$acg_email     = get_theme_mod( 'acg_email', '' );

acg_section_open( 'contacto' );
?>
	<div class="acg-contenedor acg-contacto">
		<div class="acg-contacto__intro">
			<?php
			acg_section_header(
				array(
					'numero'   => '07',
					'epigrafe' => acg_home( 'contacto_epigrafe', acg_s( 'nav_contacto' ) ),
					'titulo'   => acg_home( 'contacto_titulo' ),
					'clase'    => 'acg-seccion__cabecera--apilada',
				)
			);
			?>

			<?php if ( acg_home( 'contacto_texto' ) ) : ?>
				<p class="acg-contacto__texto"><?php echo esc_html( acg_home( 'contacto_texto' ) ); ?></p>
			<?php endif; ?>

			<ul class="acg-contacto__lista">
				<?php if ( $acg_whatsapp ) : ?>
					<li>
						<a href="<?php echo esc_url( $acg_whatsapp ); ?>" target="_blank" rel="noopener noreferrer">
							<span>WhatsApp</span><span class="acg-contacto__valor"><?php echo esc_html( $acg_telefono ); ?></span>
						</a>
					</li>
				<?php endif; ?>

				<?php if ( $acg_email ) : ?>
					<li>
						<a href="mailto:<?php echo esc_attr( $acg_email ); ?>">
							<span><?php echo esc_html( acg_s( 'form_email' ) ); ?></span><span class="acg-contacto__valor"><?php echo esc_html( $acg_email ); ?></span>
						</a>
					</li>
				<?php endif; ?>

				<?php foreach ( acg_social_networks() as $acg_id => $acg_datos ) : ?>
					<?php $acg_url = get_theme_mod( 'acg_social_' . $acg_id, $acg_datos['default'] ); ?>
					<?php if ( ! $acg_url ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<li>
						<a href="<?php echo esc_url( $acg_url ); ?>" target="_blank" rel="noopener noreferrer">
							<span><?php echo esc_html( $acg_datos['label'] ); ?></span>
							<span class="acg-contacto__valor"><?php echo esc_html( trim( (string) wp_parse_url( $acg_url, PHP_URL_PATH ), '/' ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $acg_mapa ) : ?>
				<div class="acg-mapa">
					<iframe
						src="<?php echo esc_url( $acg_mapa ); ?>"
						title="<?php echo esc_attr( get_theme_mod( 'acg_ciudad', '' ) ); ?>"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						allowfullscreen
					></iframe>
				</div>
			<?php endif; ?>
		</div>

		<div class="acg-contacto__form">
			<?php get_template_part( 'template-parts/formulario' ); ?>
		</div>
	</div>
<?php
acg_section_close();
