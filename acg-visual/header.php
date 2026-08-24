<?php
/**
 * Cabecera del documento y encabezado del sitio.
 *
 * Las zonas del encabezado las pinta el diseñador (inc/layout-builder.php);
 * aquí solo está el andamiaje que tiene que existir siempre: el enlace de
 * salto, el <header> y el menú móvil.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="acg-skip" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'acg-visual' ); ?></a>

<header class="acg-header" id="acg-header">
	<?php acg_render_layout( 'header' ); ?>
</header>

<div class="acg-menu-movil" id="acg-menu-movil" hidden>
	<div class="acg-menu-movil__interior">
		<?php acg_menu( 'primary', 'acg-menu-movil__lista' ); ?>

		<?php if ( get_theme_mod( 'acg_idioma_switcher', true ) ) : ?>
			<?php acg_language_switcher(); ?>
		<?php endif; ?>

		<?php acg_the_contact_info(); ?>
		<?php acg_the_social(); ?>
	</div>
</div>

<main id="contenido" class="acg-main">
