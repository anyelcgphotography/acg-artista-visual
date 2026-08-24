<?php
/**
 * Carga de estilos y scripts, y cálculo de la paleta.
 *
 * Los colores elegidos en el Personalizador se resuelven **en PHP** y viajan
 * al navegador como variables CSS ya calculadas. Así el CSS no necesita saber
 * mezclar colores y la misma paleta vale para el front, el editor y la vista
 * previa del Personalizador.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versión de un asset a partir de su fecha de modificación, para que el
 * navegador no sirva CSS viejo tras cada retoque.
 *
 * @param string $ruta_rel Ruta relativa dentro del theme.
 * @return string
 */
function acg_asset_version( $ruta_rel ) {
	$archivo = ACG_DIR . '/' . ltrim( $ruta_rel, '/' );

	return file_exists( $archivo ) ? (string) filemtime( $archivo ) : ACG_VERSION;
}

/**
 * Convierte un color hexadecimal en sus tres componentes.
 *
 * @param string $hex Color en formato #rrggbb o #rgb.
 * @return array{0:int,1:int,2:int}
 */
function acg_hex_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return array( 0, 0, 0 );
	}

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * El mismo color con transparencia, listo para CSS.
 *
 * @param string $hex   Color base.
 * @param float  $alpha Opacidad entre 0 y 1.
 * @return string
 */
function acg_rgba( $hex, $alpha ) {
	list( $r, $g, $b ) = acg_hex_rgb( $hex );

	return sprintf( 'rgba(%d,%d,%d,%s)', $r, $g, $b, rtrim( rtrim( number_format( (float) $alpha, 3, '.', '' ), '0' ), '.' ) );
}

/**
 * Mezcla dos colores.
 *
 * @param string $hex_a Color base.
 * @param string $hex_b Color con el que se mezcla.
 * @param float  $peso  Proporción del segundo color (0 a 1).
 * @return string Hexadecimal.
 */
function acg_mix( $hex_a, $hex_b, $peso ) {
	list( $r1, $g1, $b1 ) = acg_hex_rgb( $hex_a );
	list( $r2, $g2, $b2 ) = acg_hex_rgb( $hex_b );
	$peso                 = max( 0, min( 1, (float) $peso ) );

	return sprintf(
		'#%02x%02x%02x',
		(int) round( $r1 + ( $r2 - $r1 ) * $peso ),
		(int) round( $g1 + ( $g2 - $g1 ) * $peso ),
		(int) round( $b1 + ( $b2 - $b1 ) * $peso )
	);
}

/**
 * Luminancia relativa de un color (WCAG), para decidir si encima va texto
 * claro u oscuro sin tener que preguntarlo en el Personalizador.
 *
 * @param string $hex Color.
 * @return float Entre 0 (negro) y 1 (blanco).
 */
function acg_luminance( $hex ) {
	$canales = array_map(
		static function ( $canal ) {
			$canal = $canal / 255;

			return $canal <= 0.03928 ? $canal / 12.92 : pow( ( $canal + 0.055 ) / 1.055, 2.4 );
		},
		acg_hex_rgb( $hex )
	);

	return 0.2126 * $canales[0] + 0.7152 * $canales[1] + 0.0722 * $canales[2];
}

/**
 * Color de texto legible sobre un fondo dado.
 *
 * Compara el contraste real (fórmula WCAG) contra la tinta y contra el blanco
 * y se queda con el que más contrasta. Un simple «¿es un color claro?» no
 * sirve: el naranja de la marca tiene una luminancia baja y aun así el negro
 * contrasta con él más del doble que el blanco (7:1 frente a 3:1).
 *
 * @param string $fondo Color de fondo.
 * @return string
 */
function acg_readable_on( $fondo ) {
	$tinta = get_theme_mod( 'acg_color_tinta', '#000000' );
	$luz   = acg_luminance( $fondo );

	$contraste = static function ( $a, $b ) {
		$claro  = max( $a, $b );
		$oscuro = min( $a, $b );

		return ( $claro + 0.05 ) / ( $oscuro + 0.05 );
	};

	$con_tinta  = $contraste( $luz, acg_luminance( $tinta ) );
	$con_blanco = $contraste( $luz, 1.0 );

	return $con_tinta >= $con_blanco ? $tinta : '#ffffff';
}

/**
 * Un color de la paleta, con su valor de fábrica como red de seguridad.
 *
 * @param string $id Identificador del ajuste.
 * @return string
 */
function acg_color( $id ) {
	$defectos = acg_default_colors();
	$defecto  = isset( $defectos[ $id ] ) ? $defectos[ $id ] : '#000000';
	$valor    = get_theme_mod( $id, $defecto );

	return sanitize_hex_color( $valor ) ? $valor : $defecto;
}

/**
 * Variables CSS del sitio: la paleta base y los tres esquemas de sección.
 *
 * @return string CSS listo para inyectar.
 */
function acg_css_variables() {
	$acento     = acg_color( 'acg_color_acento' );
	$oscuro     = acg_color( 'acg_color_oscuro' );
	$claro      = acg_color( 'acg_color_claro' );
	$tinta      = acg_color( 'acg_color_tinta' );
	$superficie = acg_color( 'acg_color_superficie' );

	// Sobre el naranja de marca, el texto negro contrasta mucho mejor que el
	// blanco; sobre un acento que el cliente pusiera oscuro, al revés.
	$sobre_acento = acg_readable_on( $acento );

	$base = array(
		'--acg-acento'        => $acento,
		'--acg-acento-hover'  => acg_mix( $acento, '#ffffff', 0.22 ),
		'--acg-acento-hondo'  => acg_mix( $acento, '#000000', 0.28 ),
		'--acg-oscuro'        => $oscuro,
		'--acg-claro'         => $claro,
		'--acg-tinta'         => $tinta,
		'--acg-superficie'    => $superficie,
		'--acg-sobre-acento'  => $sobre_acento,
		'--acg-max'           => '1240px',
		'--acg-gutter'        => 'clamp(18px, 4vw, 56px)',
		'--acg-seccion-y'     => 'clamp(70px, 11vh, 140px)',
		'--acg-fuente-titulo' => "Livvic, 'TT Norms', 'Helvetica Neue', Arial, sans-serif",
		'--acg-fuente-texto'  => "Manrope, 'TT Norms', Livvic, system-ui, -apple-system, 'Segoe UI', sans-serif",
	);

	// Cada esquema define el mismo juego de variables; las secciones solo
	// cambian de clase y el CSS interior no se entera de nada.
	$esquemas = array(
		'oscuro' => array(
			'--sec-bg'      => $oscuro,
			'--sec-fg'      => '#ffffff',
			'--sec-texto'   => acg_rgba( $claro, 0.78 ),
			'--sec-suave'   => acg_rgba( $claro, 0.55 ),
			'--sec-borde'   => acg_rgba( $claro, 0.14 ),
			'--sec-card'    => $superficie,
			'--sec-acento'  => $acento,
			'--sec-input'   => acg_mix( $oscuro, '#ffffff', 0.06 ),
		),
		'claro'  => array(
			'--sec-bg'      => $claro,
			'--sec-fg'      => $tinta,
			'--sec-texto'   => acg_rgba( $tinta, 0.7 ),
			'--sec-suave'   => acg_rgba( $tinta, 0.55 ),
			'--sec-borde'   => acg_rgba( $tinta, 0.18 ),
			'--sec-card'    => acg_mix( $claro, '#ffffff', 0.4 ),
			'--sec-acento'  => acg_mix( $acento, '#000000', 0.1 ),
			'--sec-input'   => '#ffffff',
		),
		'acento' => array(
			'--sec-bg'      => $acento,
			'--sec-fg'      => $sobre_acento,
			'--sec-texto'   => acg_rgba( $sobre_acento, 0.78 ),
			'--sec-suave'   => acg_rgba( $sobre_acento, 0.6 ),
			'--sec-borde'   => acg_rgba( $sobre_acento, 0.28 ),
			'--sec-card'    => acg_mix( $acento, '#ffffff', 0.14 ),
			'--sec-acento'  => $sobre_acento,
			'--sec-input'   => '#ffffff',
		),
	);

	$css = ':root{';
	foreach ( $base as $clave => $valor ) {
		$css .= $clave . ':' . $valor . ';';
	}
	$css .= '}';

	foreach ( $esquemas as $nombre => $vars ) {
		$css .= '.acg-esquema--' . $nombre . '{';
		foreach ( $vars as $clave => $valor ) {
			$css .= $clave . ':' . $valor . ';';
		}
		$css .= '}';
	}

	// El cuerpo hereda el esquema oscuro para que las zonas que no son una
	// sección (menú móvil, buscador, 404) tengan siempre tokens definidos.
	$css .= 'body{';
	foreach ( $esquemas['oscuro'] as $clave => $valor ) {
		$css .= $clave . ':' . $valor . ';';
	}
	$css .= '}';

	return $css;
}

/**
 * Encola los assets del front.
 *
 * @return void
 */
function acg_enqueue_assets() {
	if ( get_theme_mod( 'acg_google_fonts', true ) ) {
		wp_enqueue_style(
			'acg-fonts',
			'https://fonts.googleapis.com/css2?family=Livvic:wght@300;400;500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		);
	}

	wp_enqueue_style( 'acg-theme', ACG_URI . '/assets/css/theme.css', array(), acg_asset_version( 'assets/css/theme.css' ) );
	wp_add_inline_style( 'acg-theme', acg_css_variables() );

	wp_enqueue_script( 'acg-theme', ACG_URI . '/assets/js/theme.js', array(), acg_asset_version( 'assets/js/theme.js' ), true );

	wp_localize_script(
		'acg-theme',
		'acgData',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'acg_lead_form' ),
			'lang'        => acg_lang(),
			'animaciones' => (bool) get_theme_mod( 'acg_animaciones', true ),
			'lightbox'    => (bool) get_theme_mod( 'acg_lightbox', true ),
			'i18n'        => array(
				'enviando'   => acg_s( 'form_enviando' ),
				'enviar'     => acg_s( 'form_enviar' ),
				'error'      => acg_s( 'form_error' ),
				'ok'         => acg_s( 'form_ok' ),
				'waAbierto'  => acg_s( 'form_wa_abierto' ),
				'waSinNumero' => acg_s( 'form_wa_sin_numero' ),
				'cerrar'     => acg_s( 'cerrar_menu' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'acg_enqueue_assets' );

/**
 * Lleva la misma paleta al editor de bloques del admin.
 *
 * @return void
 */
function acg_enqueue_editor_assets() {
	wp_register_style( 'acg-editor-vars', false, array(), ACG_VERSION ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'acg-editor-vars' );
	wp_add_inline_style( 'acg-editor-vars', acg_css_variables() );
}
add_action( 'enqueue_block_editor_assets', 'acg_enqueue_editor_assets' );

/**
 * Clases del body que necesita el CSS: idioma activo y cabecera fija.
 *
 * @param array $clases Clases actuales.
 * @return array
 */
function acg_body_classes( $clases ) {
	$clases[] = 'acg-lang-' . acg_lang();

	if ( get_theme_mod( 'acg_cabecera_fija', true ) ) {
		$clases[] = 'acg-header-fija';
	}

	if ( ! get_theme_mod( 'acg_animaciones', true ) ) {
		$clases[] = 'acg-sin-animacion';
	}

	return $clases;
}
add_filter( 'body_class', 'acg_body_classes' );
