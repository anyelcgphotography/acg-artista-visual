<?php
/**
 * Ajustes globales del sitio en el Personalizador.
 *
 * Todo lo que es global —paleta, redes, contacto, qué secciones se ven— vive
 * aquí y no en una página de opciones de ACF, porque la versión gratuita de
 * ACF no incluye Options Page. Lo que es contenido de una página concreta se
 * edita con ACF en esa página (ver inc/acf-fields.php).
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paleta de fábrica, tomada del manual de marca de ACG.
 *
 * @return array<string,string>
 */
function acg_default_colors() {
	return array(
		'acg_color_acento'      => '#fa6613',
		'acg_color_oscuro'      => '#0a0a0a',
		'acg_color_claro'       => '#e1e1e1',
		'acg_color_tinta'       => '#000000',
		'acg_color_superficie'  => '#111111',
	);
}

/**
 * Esquemas de fondo disponibles para las secciones.
 *
 * Cada sección de la portada elige uno de estos tres, en vez de un color
 * suelto: así el contraste del texto siempre acompaña al fondo y no hay forma
 * de dejar texto negro sobre fondo negro desde el Personalizador.
 *
 * @return array<string,string>
 */
function acg_scheme_choices() {
	return array(
		'oscuro' => __( 'Oscuro', 'acg-visual' ),
		'claro'  => __( 'Claro', 'acg-visual' ),
		'acento' => __( 'Acento (naranja)', 'acg-visual' ),
	);
}

/**
 * Secciones de la portada que se pueden mostrar, ocultar y recolorear.
 *
 * El orden de este array es el orden en que se pintan en la portada.
 *
 * @return array<string,array{label:string,esquema:string,activa:bool}>
 */
function acg_sections() {
	return array(
		'hero'         => array( 'label' => __( 'Portada (hero)', 'acg-visual' ), 'esquema' => 'oscuro', 'activa' => true ),
		'marquesina'   => array( 'label' => __( 'Cinta de especialidades', 'acg-visual' ), 'esquema' => 'acento', 'activa' => true ),
		'portafolio'   => array( 'label' => __( 'Portafolio', 'acg-visual' ), 'esquema' => 'claro', 'activa' => true ),
		'servicios'    => array( 'label' => __( 'Servicios', 'acg-visual' ), 'esquema' => 'oscuro', 'activa' => true ),
		'sobre'        => array( 'label' => __( 'Sobre mí', 'acg-visual' ), 'esquema' => 'claro', 'activa' => true ),
		'proceso'      => array( 'label' => __( 'Proceso', 'acg-visual' ), 'esquema' => 'oscuro', 'activa' => true ),
		'testimonios'  => array( 'label' => __( 'Testimonios', 'acg-visual' ), 'esquema' => 'claro', 'activa' => true ),
		'equipo'       => array( 'label' => __( 'Equipo de trabajo', 'acg-visual' ), 'esquema' => 'acento', 'activa' => true ),
		'faq'          => array( 'label' => __( 'Preguntas frecuentes', 'acg-visual' ), 'esquema' => 'claro', 'activa' => true ),
		'contacto'     => array( 'label' => __( 'Contacto', 'acg-visual' ), 'esquema' => 'oscuro', 'activa' => true ),
	);
}

/**
 * ¿Está activa una sección de la portada?
 *
 * @param string $id Identificador de la sección.
 * @return bool
 */
function acg_section_active( $id ) {
	$secciones = acg_sections();

	if ( ! isset( $secciones[ $id ] ) ) {
		return false;
	}

	// El switch vive junto al resto de campos de la sección, en la pestaña
	// correspondiente de la página de inicio, y no en el Personalizador: así
	// Angie lo ve en el mismo sitio donde edita los textos, sin cambiar de
	// pantalla. `acg_home_raw()` ya cae al valor por defecto si la portada
	// todavía no tiene nada guardado.
	return (bool) acg_home_raw( 'activo_' . $id, $secciones[ $id ]['activa'] );
}

/**
 * Esquema de fondo elegido para una sección.
 *
 * @param string $id Identificador de la sección.
 * @return string 'oscuro', 'claro' o 'acento'.
 */
function acg_section_scheme( $id ) {
	$secciones = acg_sections();
	$defecto   = isset( $secciones[ $id ] ) ? $secciones[ $id ]['esquema'] : 'oscuro';
	$valor     = get_theme_mod( 'acg_esquema_' . $id, $defecto );

	return isset( acg_scheme_choices()[ $valor ] ) ? $valor : $defecto;
}

/**
 * Sanea un valor de esquema.
 *
 * @param string $valor Valor recibido.
 * @return string
 */
function acg_sanitize_scheme( $valor ) {
	return isset( acg_scheme_choices()[ $valor ] ) ? $valor : 'oscuro';
}

/**
 * Sanea un booleano de checkbox.
 *
 * @param mixed $valor Valor recibido.
 * @return bool
 */
function acg_sanitize_checkbox( $valor ) {
	return (bool) $valor;
}

/**
 * Sanea el código de idioma por defecto.
 *
 * @param string $valor Valor recibido.
 * @return string
 */
function acg_sanitize_lang( $valor ) {
	return isset( acg_languages()[ $valor ] ) ? $valor : 'es';
}

/**
 * Registra el panel completo del theme.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_panel(
		'acg_panel',
		array(
			'title'       => __( 'ACG Artista Visual', 'acg-visual' ),
			'description' => __( 'Todos los ajustes del theme: colores, secciones, contacto, redes y los diseñadores de cabecera y pie.', 'acg-visual' ),
			'priority'    => 20,
		)
	);

	acg_customize_colores( $wp_customize );
	acg_customize_secciones( $wp_customize );
	acg_customize_idioma( $wp_customize );
	acg_customize_contacto( $wp_customize );
	acg_customize_redes( $wp_customize );
	acg_customize_cabecera( $wp_customize );
	acg_customize_efectos( $wp_customize );
}
add_action( 'customize_register', 'acg_customize_register' );

/**
 * Sección de colores.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_colores( $wp_customize ) {
	$wp_customize->add_section(
		'acg_colores',
		array(
			'title'       => __( 'Colores', 'acg-visual' ),
			'panel'       => 'acg_panel',
			'priority'    => 10,
			'description' => __( 'Los cinco colores de los que se deriva todo lo demás. El fondo de cada sección se elige después en «Secciones de la portada».', 'acg-visual' ),
		)
	);

	$etiquetas = array(
		'acg_color_acento'     => __( 'Acento — botones, números, detalles', 'acg-visual' ),
		'acg_color_oscuro'     => __( 'Fondo oscuro', 'acg-visual' ),
		'acg_color_claro'      => __( 'Fondo claro', 'acg-visual' ),
		'acg_color_tinta'      => __( 'Texto sobre fondo claro', 'acg-visual' ),
		'acg_color_superficie' => __( 'Tarjetas sobre fondo oscuro', 'acg-visual' ),
	);

	foreach ( acg_default_colors() as $id => $defecto ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $defecto,
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$id,
				array(
					'section' => 'acg_colores',
					'label'   => $etiquetas[ $id ],
				)
			)
		);
	}
}

/**
 * Sección con el interruptor y el fondo de cada bloque de la portada.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_secciones( $wp_customize ) {
	$wp_customize->add_section(
		'acg_secciones',
		array(
			'title'       => __( 'Fondos de la portada', 'acg-visual' ),
			'panel'       => 'acg_panel',
			'priority'    => 12,
			'description' => __( 'Elige sobre qué fondo se pinta cada bloque de la página de inicio. Para mostrar u ocultar un bloque, edita la página de inicio: cada sección tiene su propia pestaña, con el interruptor arriba del todo.', 'acg-visual' ),
		)
	);

	foreach ( acg_sections() as $id => $datos ) {
		// El hero y la cinta no cambian de esquema: el hero siempre va sobre
		// la foto a pantalla completa y la cinta es el único acento fijo.
		if ( in_array( $id, array( 'hero', 'marquesina' ), true ) ) {
			continue;
		}

		$wp_customize->add_setting(
			'acg_esquema_' . $id,
			array(
				'default'           => $datos['esquema'],
				'sanitize_callback' => 'acg_sanitize_scheme',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'acg_esquema_' . $id,
			array(
				'section' => 'acg_secciones',
				'type'    => 'select',
				'choices' => acg_scheme_choices(),
				'label'   => sprintf(
					/* translators: %s: nombre de la sección. */
					__( 'Fondo de «%s»', 'acg-visual' ),
					$datos['label']
				),
			)
		);
	}

	// El pie no es una sección de la portada —sale en todas las páginas— pero
	// su fondo se elige igual, y este es el sitio donde el cliente lo buscará.
	$wp_customize->add_setting(
		'acg_pie_esquema',
		array(
			'default'           => 'claro',
			'sanitize_callback' => 'acg_sanitize_scheme',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'acg_pie_esquema',
		array(
			'section' => 'acg_secciones',
			'type'    => 'select',
			'choices' => acg_scheme_choices(),
			'label'   => __( 'Fondo del pie de página', 'acg-visual' ),
		)
	);
}

/**
 * Sección de idioma.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_idioma( $wp_customize ) {
	$wp_customize->add_section(
		'acg_idioma',
		array(
			'title'       => __( 'Idioma', 'acg-visual' ),
			'panel'       => 'acg_panel',
			'priority'    => 14,
			'description' => __( 'El sitio se sirve en español o portugués. Cada campo de contenido tiene su gemelo «(PT)»; si lo dejas vacío, se muestra el español.', 'acg-visual' ),
		)
	);

	$wp_customize->add_setting(
		'acg_idioma_defecto',
		array(
			'default'           => 'es',
			'sanitize_callback' => 'acg_sanitize_lang',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'acg_idioma_defecto',
		array(
			'section' => 'acg_idioma',
			'type'    => 'select',
			'choices' => acg_languages(),
			'label'   => __( 'Idioma con el que se abre el sitio', 'acg-visual' ),
		)
	);

	$wp_customize->add_setting(
		'acg_idioma_switcher',
		array(
			'default'           => true,
			'sanitize_callback' => 'acg_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'acg_idioma_switcher',
		array(
			'section'     => 'acg_idioma',
			'type'        => 'checkbox',
			'label'       => __( 'Mostrar el conmutador ES / PT', 'acg-visual' ),
			'description' => __( 'Si lo apagas, el sitio queda solo en el idioma elegido arriba. Colócalo donde quieras con el diseñador de cabecera.', 'acg-visual' ),
		)
	);
}

/**
 * Sección de datos de contacto.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_contacto( $wp_customize ) {
	$wp_customize->add_section(
		'acg_contacto',
		array(
			'title'    => __( 'Contacto', 'acg-visual' ),
			'panel'    => 'acg_panel',
			'priority' => 20,
		)
	);

	$campos = array(
		'acg_whatsapp'      => array(
			'label'       => __( 'WhatsApp (solo números, con prefijo)', 'acg-visual' ),
			'default'     => '5516992193838',
			'sanitize'    => 'sanitize_text_field',
			'description' => __( 'Ejemplo: 5516992193838. Se usa en el botón de la cabecera y en el enlace de contacto.', 'acg-visual' ),
		),
		'acg_telefono'      => array(
			'label'    => __( 'Teléfono visible', 'acg-visual' ),
			'default'  => '+55 16 99219 3838',
			'sanitize' => 'sanitize_text_field',
		),
		'acg_email'         => array(
			'label'    => __( 'Email', 'acg-visual' ),
			'default'  => '',
			'sanitize' => 'sanitize_email',
		),
		'acg_ciudad'        => array(
			'label'    => __( 'Ciudad / zona de cobertura', 'acg-visual' ),
			'default'  => 'São Paulo · BR',
			'sanitize' => 'sanitize_text_field',
		),
		'acg_mapa_embed'    => array(
			'label'       => __( 'Mapa: URL de inserción de Google Maps', 'acg-visual' ),
			'default'     => '',
			'sanitize'    => 'esc_url_raw',
			'description' => __( 'En Google Maps: Compartir → Insertar un mapa → copia solo la URL que hay dentro de src="…". Si lo dejas vacío no se muestra el mapa.', 'acg-visual' ),
		),
		'acg_email_destino' => array(
			'label'       => __( 'Email que recibe las solicitudes', 'acg-visual' ),
			'default'     => '',
			'sanitize'    => 'sanitize_email',
			'description' => __( 'Si lo dejas vacío se usa el email del administrador del sitio.', 'acg-visual' ),
		),
	);

	foreach ( $campos as $id => $datos ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $datos['default'],
				'sanitize_callback' => $datos['sanitize'],
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'section'     => 'acg_contacto',
				'type'        => 'text',
				'label'       => $datos['label'],
				'description' => isset( $datos['description'] ) ? $datos['description'] : '',
			)
		);
	}
}

/**
 * Sección de redes sociales.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_redes( $wp_customize ) {
	$wp_customize->add_section(
		'acg_redes',
		array(
			'title'       => __( 'Redes sociales', 'acg-visual' ),
			'panel'       => 'acg_panel',
			'priority'    => 22,
			'description' => __( 'Deja vacía la que no uses y desaparece del sitio.', 'acg-visual' ),
		)
	);

	foreach ( acg_social_networks() as $id => $datos ) {
		$wp_customize->add_setting(
			'acg_social_' . $id,
			array(
				'default'           => $datos['default'],
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'acg_social_' . $id,
			array(
				'section' => 'acg_redes',
				'type'    => 'url',
				'label'   => $datos['label'],
			)
		);
	}
}

/**
 * Sección de cabecera: llamada a la acción y comportamiento.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_cabecera( $wp_customize ) {
	$wp_customize->add_section(
		'acg_cabecera',
		array(
			'title'       => __( 'Cabecera y llamada a la acción', 'acg-visual' ),
			'panel'       => 'acg_panel',
			'priority'    => 24,
			'description' => __( 'El texto del botón y el subtítulo de la marca. Dónde se colocan se decide en «Diseñador de cabecera».', 'acg-visual' ),
		)
	);

	$textos = array(
		'acg_marca_subtitulo'    => array(
			'label'   => __( 'Subtítulo bajo el logotipo', 'acg-visual' ),
			'default' => 'ARTISTA VISUAL',
		),
		'acg_marca_subtitulo_pt' => array(
			'label'   => __( 'Subtítulo bajo el logotipo (PT)', 'acg-visual' ),
			'default' => 'ARTISTA VISUAL',
		),
		'acg_cta_texto'          => array(
			'label'   => __( 'Texto del botón', 'acg-visual' ),
			'default' => 'WhatsApp',
		),
		'acg_cta_texto_pt'       => array(
			'label'   => __( 'Texto del botón (PT)', 'acg-visual' ),
			'default' => 'WhatsApp',
		),
		'acg_cta_url'            => array(
			'label'       => __( 'Enlace del botón', 'acg-visual' ),
			'default'     => '',
			'description' => __( 'Vacío = abre WhatsApp con el número de la sección «Contacto».', 'acg-visual' ),
		),
	);

	foreach ( $textos as $id => $datos ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $datos['default'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'section'     => 'acg_cabecera',
				'type'        => 'text',
				'label'       => $datos['label'],
				'description' => isset( $datos['description'] ) ? $datos['description'] : '',
			)
		);
	}

	$wp_customize->add_setting(
		'acg_cabecera_fija',
		array(
			'default'           => true,
			'sanitize_callback' => 'acg_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'acg_cabecera_fija',
		array(
			'section' => 'acg_cabecera',
			'type'    => 'checkbox',
			'label'   => __( 'Cabecera fija al hacer scroll', 'acg-visual' ),
		)
	);
}

/**
 * Sección de efectos y rendimiento.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_customize_efectos( $wp_customize ) {
	$wp_customize->add_section(
		'acg_efectos',
		array(
			'title'    => __( 'Efectos y tipografías', 'acg-visual' ),
			'panel'    => 'acg_panel',
			'priority' => 60,
		)
	);

	$interruptores = array(
		'acg_animaciones'   => array(
			'label'       => __( 'Animaciones al hacer scroll', 'acg-visual' ),
			'default'     => true,
			'description' => __( 'Se desactivan solas para quien tenga activado «reducir movimiento» en su sistema.', 'acg-visual' ),
		),
		'acg_google_fonts'  => array(
			'label'       => __( 'Cargar tipografías desde Google Fonts', 'acg-visual' ),
			'default'     => true,
			'description' => __( 'Si lo apagas, el sitio usa las tipografías del sistema y no se conecta a servidores de Google.', 'acg-visual' ),
		),
		'acg_lightbox'      => array(
			'label'   => __( 'Ampliar las fotos del portafolio al hacer clic', 'acg-visual' ),
			'default' => true,
		),
	);

	foreach ( $interruptores as $id => $datos ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $datos['default'],
				'sanitize_callback' => 'acg_sanitize_checkbox',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'section'     => 'acg_efectos',
				'type'        => 'checkbox',
				'label'       => $datos['label'],
				'description' => isset( $datos['description'] ) ? $datos['description'] : '',
			)
		);
	}
}

/**
 * Catálogo de redes sociales con su etiqueta y valor de fábrica.
 *
 * @return array<string,array{label:string,default:string}>
 */
function acg_social_networks() {
	return array(
		'instagram' => array( 'label' => 'Instagram', 'default' => 'https://www.instagram.com/anyelcg_photography' ),
		'linkedin'  => array( 'label' => 'LinkedIn', 'default' => 'https://www.linkedin.com/in/anyel-cgonzalez' ),
		'facebook'  => array( 'label' => 'Facebook', 'default' => '' ),
		'behance'   => array( 'label' => 'Behance', 'default' => '' ),
		'pinterest' => array( 'label' => 'Pinterest', 'default' => '' ),
		'youtube'   => array( 'label' => 'YouTube', 'default' => '' ),
	);
}

/**
 * Refresca la vista previa del Personalizador en los cambios en vivo.
 *
 * @return void
 */
function acg_customize_preview_js() {
	wp_enqueue_script(
		'acg-customizer',
		ACG_URI . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		acg_asset_version( 'assets/js/customizer.js' ),
		true
	);
}
add_action( 'customize_preview_init', 'acg_customize_preview_js' );
