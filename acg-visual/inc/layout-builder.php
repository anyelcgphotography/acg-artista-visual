<?php
/**
 * Diseñador de encabezado y pie de página.
 *
 * Un constructor por zonas: la cabecera y el pie se dividen en filas y cada
 * fila en tres columnas (izquierda / centro / derecha). Desde el
 * Personalizador se arrastra cada elemento —logo, menú, botón, redes, el
 * conmutador de idioma…— a la columna donde debe aparecer.
 *
 * Dos decisiones que explican el diseño:
 *
 * - **Solo decide dónde y en qué orden, nunca el contenido.** El texto del
 *   botón, el número de WhatsApp o las URLs de redes se siguen editando en su
 *   propia sección del Personalizador. Así no hay dos sitios donde editar lo
 *   mismo.
 * - **Se guarda como JSON en un theme_mod**, porque ACF gratuito no tiene ni
 *   Repeater ni Options Page. `acg_sanitize_layout_json()` valida ese JSON
 *   contra una lista blanca antes de guardarlo, de modo que un valor
 *   manipulado a mano no puede colar un tipo inexistente ni HTML sin filtrar.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catálogo de elementos colocables.
 *
 * 'contexts' filtra en qué constructor aparece cada uno; 'allow_multiple'
 * solo lo necesita el texto libre (el resto tiene una única fuente de datos,
 * así que repetirlo no tendría sentido); 'has_text' marca los que llevan su
 * propio campo de texto en la ficha del constructor.
 *
 * @return array<string,array{label:string,contexts:string[],allow_multiple?:bool,has_text?:bool}>
 */
function acg_layout_element_registry() {
	return array(
		'logo'             => array(
			'label'    => __( 'Logotipo', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'site_tagline'     => array(
			'label'    => __( 'Descripción del sitio', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'menu_primary'     => array(
			'label'    => __( 'Menú principal', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'menu_footer'      => array(
			'label'    => __( 'Menú del pie', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'menu_legal'       => array(
			'label'    => __( 'Menú legal', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'lang_switcher'    => array(
			'label'    => __( 'Conmutador de idioma ES / PT', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'cta_button'       => array(
			'label'    => __( 'Botón de llamada a la acción', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'social'           => array(
			'label'    => __( 'Redes sociales', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'contact_info'     => array(
			'label'    => __( 'Datos de contacto', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'search'           => array(
			'label'    => __( 'Buscador', 'acg-visual' ),
			'contexts' => array( 'header', 'footer' ),
		),
		'copyright'        => array(
			'label'    => __( 'Aviso de copyright', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'custom_text'      => array(
			'label'          => __( 'Texto o HTML personalizado', 'acg-visual' ),
			'contexts'       => array( 'header', 'footer' ),
			'allow_multiple' => true,
			'has_text'       => true,
		),
		'footer_widgets_1' => array(
			'label'    => __( 'Widgets del pie — columna 1', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'footer_widgets_2' => array(
			'label'    => __( 'Widgets del pie — columna 2', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
		'footer_widgets_3' => array(
			'label'    => __( 'Widgets del pie — columna 3', 'acg-visual' ),
			'contexts' => array( 'footer' ),
		),
	);
}

/**
 * Filas de cada constructor.
 *
 * La fila principal se pinta siempre, aunque esté vacía, porque es la que
 * sostiene la estructura. La inferior solo se pinta si tiene algo, para no
 * dejar una barra vacía en el HTML.
 *
 * @param string $contexto 'header' o 'footer'.
 * @return array<string,string>
 */
function acg_layout_rows( $contexto ) {
	if ( 'header' === $contexto ) {
		return array(
			'principal' => __( 'Fila principal', 'acg-visual' ),
			'inferior'  => __( 'Fila inferior (barra bajo la cabecera)', 'acg-visual' ),
		);
	}

	return array(
		'principal' => __( 'Fila principal', 'acg-visual' ),
		'inferior'  => __( 'Fila inferior (línea de copyright)', 'acg-visual' ),
	);
}

/**
 * Columnas dentro de cada fila.
 *
 * @return array<string,string>
 */
function acg_layout_zones() {
	return array(
		'izquierda' => __( 'Izquierda', 'acg-visual' ),
		'centro'    => __( 'Centro', 'acg-visual' ),
		'derecha'   => __( 'Derecha', 'acg-visual' ),
	);
}

/**
 * Construye un elemento con su forma completa.
 *
 * @param string $tipo   Clave del catálogo.
 * @param array  $params Parámetros propios.
 * @return array{id:string,type:string,params:array}
 */
function acg_layout_item( $tipo, $params = array() ) {
	return array(
		'id'     => 'default-' . $tipo,
		'type'   => $tipo,
		'params' => $params,
	);
}

/**
 * Disposición de fábrica: la misma que tiene la maqueta original.
 *
 * @param string $contexto 'header' o 'footer'.
 * @return array
 */
function acg_default_layout( $contexto ) {
	if ( 'header' === $contexto ) {
		return array(
			'principal' => array(
				'izquierda' => array( acg_layout_item( 'logo' ) ),
				'centro'    => array( acg_layout_item( 'menu_primary' ) ),
				'derecha'   => array( acg_layout_item( 'lang_switcher' ), acg_layout_item( 'cta_button' ) ),
			),
			'inferior'  => array(
				'izquierda' => array(),
				'centro'    => array(),
				'derecha'   => array(),
			),
		);
	}

	return array(
		'principal' => array(
			'izquierda' => array( acg_layout_item( 'logo' ), acg_layout_item( 'site_tagline' ) ),
			'centro'    => array( acg_layout_item( 'contact_info' ) ),
			'derecha'   => array( acg_layout_item( 'social' ) ),
		),
		'inferior'  => array(
			'izquierda' => array( acg_layout_item( 'copyright' ) ),
			'centro'    => array( acg_layout_item( 'menu_legal' ) ),
			'derecha'   => array( acg_layout_item( 'lang_switcher' ) ),
		),
	);
}

/**
 * Sanea el JSON del constructor de cabecera.
 *
 * @param string $valor Valor recibido del control.
 * @return string
 */
function acg_sanitize_header_layout( $valor ) {
	return acg_sanitize_layout_json( $valor, 'header' );
}

/**
 * Sanea el JSON del constructor de pie.
 *
 * @param string $valor Valor recibido del control.
 * @return string
 */
function acg_sanitize_footer_layout( $valor ) {
	return acg_sanitize_layout_json( $valor, 'footer' );
}

/**
 * Valida y limpia el JSON de un constructor, descartando cualquier fila,
 * columna, tipo o parámetro fuera de la lista blanca. Si el JSON no se puede
 * decodificar, vuelve a la disposición de fábrica: nunca deja el ajuste en un
 * estado que rompa el render.
 *
 * @param mixed  $valor    Valor recibido.
 * @param string $contexto 'header' o 'footer'.
 * @return string JSON saneado.
 */
function acg_sanitize_layout_json( $valor, $contexto ) {
	$decodificado = is_string( $valor ) ? json_decode( $valor, true ) : null;

	if ( ! is_array( $decodificado ) ) {
		return wp_json_encode( acg_default_layout( $contexto ) );
	}

	$catalogo = acg_layout_element_registry();
	$filas    = array_keys( acg_layout_rows( $contexto ) );
	$zonas    = array_keys( acg_layout_zones() );
	$limpio   = array();
	$total    = 0;
	// Nadie necesita más de 40 elementos entre las dos filas, y el tope evita
	// que un JSON manipulado a mano deje la cabecera lentísima.
	$maximo   = 40;

	foreach ( $filas as $fila ) {
		$limpio[ $fila ] = array();

		foreach ( $zonas as $zona ) {
			$limpio[ $fila ][ $zona ] = array();

			$origen = ( isset( $decodificado[ $fila ][ $zona ] ) && is_array( $decodificado[ $fila ][ $zona ] ) )
				? $decodificado[ $fila ][ $zona ]
				: array();

			foreach ( $origen as $item ) {
				if ( $total >= $maximo ) {
					break 3;
				}

				if ( ! is_array( $item ) || empty( $item['type'] ) ) {
					continue;
				}

				$tipo = sanitize_key( $item['type'] );

				if ( ! isset( $catalogo[ $tipo ] ) || ! in_array( $contexto, $catalogo[ $tipo ]['contexts'], true ) ) {
					continue;
				}

				$id = ! empty( $item['id'] ) ? sanitize_key( $item['id'] ) : wp_generate_uuid4();

				$params = array();
				if ( ! empty( $catalogo[ $tipo ]['has_text'] ) && isset( $item['params']['text'] ) ) {
					$params['text'] = wp_kses_post( (string) $item['params']['text'] );
				}

				$limpio[ $fila ][ $zona ][] = array(
					'id'     => $id,
					'type'   => $tipo,
					'params' => $params,
				);
				++$total;
			}
		}
	}

	return wp_json_encode( $limpio );
}

/**
 * Disposición guardada, ya decodificada.
 *
 * @param string $contexto 'header' o 'footer'.
 * @return array
 */
function acg_get_layout( $contexto ) {
	$mod          = 'header' === $contexto ? 'acg_header_layout' : 'acg_footer_layout';
	$defecto      = acg_default_layout( $contexto );
	$bruto        = get_theme_mod( $mod, wp_json_encode( $defecto ) );
	$decodificado = is_string( $bruto ) ? json_decode( $bruto, true ) : null;

	return is_array( $decodificado ) ? $decodificado : $defecto;
}

/**
 * Pinta las filas y columnas de un constructor con su contenido real.
 *
 * @param string $contexto 'header' o 'footer'.
 * @return void
 */
function acg_render_layout( $contexto ) {
	$layout = acg_get_layout( $contexto );

	foreach ( acg_layout_rows( $contexto ) as $fila_id => $fila_label ) {
		$zonas = isset( $layout[ $fila_id ] ) ? $layout[ $fila_id ] : array();

		$tiene_contenido = false;
		foreach ( $zonas as $items ) {
			if ( ! empty( $items ) ) {
				$tiene_contenido = true;
				break;
			}
		}

		if ( 'principal' !== $fila_id && ! $tiene_contenido ) {
			continue;
		}

		printf(
			'<div class="acg-builder-row acg-builder-row--%1$s acg-builder-row--%2$s">',
			esc_attr( $contexto ),
			esc_attr( $fila_id )
		);

		foreach ( acg_layout_zones() as $zona_id => $zona_label ) {
			$items = isset( $zonas[ $zona_id ] ) ? $zonas[ $zona_id ] : array();

			// La columna derecha de la fila principal de la cabecera se pinta
			// siempre: ahí vive el botón del menú móvil, que no es un
			// elemento del constructor.
			$es_derecha_cabecera = ( 'header' === $contexto && 'principal' === $fila_id && 'derecha' === $zona_id );

			if ( ! $items && ! $es_derecha_cabecera ) {
				continue;
			}

			list( $etiqueta, $atributos ) = acg_layout_zone_wrapper( $contexto, $fila_id, $zona_id );

			printf( '<%1$s%2$s>', tag_escape( $etiqueta ), $atributos ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			foreach ( $items as $elemento ) {
				acg_render_layout_element( $elemento, $contexto );
			}

			if ( $es_derecha_cabecera ) {
				acg_the_burger_button();
			}

			printf( '</%s>', tag_escape( $etiqueta ) );
		}

		echo '</div>';
	}
}

/**
 * Etiqueta HTML y atributos de una columna. La columna central de la fila
 * principal de la cabecera es la navegación, así que sale como <nav> con su
 * id y su etiqueta accesible.
 *
 * @param string $contexto 'header' o 'footer'.
 * @param string $fila     ID de la fila.
 * @param string $zona     ID de la columna.
 * @return array{0:string,1:string}
 */
function acg_layout_zone_wrapper( $contexto, $fila, $zona ) {
	if ( 'header' === $contexto && 'principal' === $fila && 'centro' === $zona ) {
		return array(
			'nav',
			sprintf(
				' class="acg-nav acg-builder-zone acg-builder-zone--centro" id="acg-navegacion" aria-label="%s"',
				esc_attr__( 'Menú principal', 'acg-visual' )
			),
		);
	}

	return array( 'div', sprintf( ' class="acg-builder-zone acg-builder-zone--%s"', esc_attr( $zona ) ) );
}

/**
 * Pinta un elemento del constructor según su tipo.
 *
 * @param array  $elemento Elemento con 'type' y 'params'.
 * @param string $contexto 'header' o 'footer'.
 * @return void
 */
function acg_render_layout_element( $elemento, $contexto ) {
	$tipo   = isset( $elemento['type'] ) ? $elemento['type'] : '';
	$params = isset( $elemento['params'] ) && is_array( $elemento['params'] ) ? $elemento['params'] : array();

	switch ( $tipo ) {
		case 'logo':
			acg_logo( array( 'contexto' => $contexto ) );
			break;

		case 'site_tagline':
			$descripcion = get_bloginfo( 'description' );
			if ( $descripcion ) {
				printf( '<p class="acg-pie__tagline">%s</p>', esc_html( $descripcion ) );
			}
			break;

		case 'menu_primary':
			acg_menu( 'primary', 'acg-nav__lista' );
			break;

		case 'menu_footer':
			acg_menu( 'footer', 'acg-pie__menu' );
			break;

		case 'menu_legal':
			if ( has_nav_menu( 'legal' ) ) {
				printf( '<nav class="acg-pie__legal" aria-label="%s">', esc_attr__( 'Enlaces legales', 'acg-visual' ) );
				acg_menu( 'legal', 'acg-pie__legal-lista' );
				echo '</nav>';
			}
			break;

		case 'lang_switcher':
			if ( get_theme_mod( 'acg_idioma_switcher', true ) ) {
				acg_language_switcher();
			}
			break;

		case 'cta_button':
			$texto = acg_t(
				get_theme_mod( 'acg_cta_texto', 'WhatsApp' ),
				get_theme_mod( 'acg_cta_texto_pt', '' )
			);
			$url = get_theme_mod( 'acg_cta_url', '' );
			$url = $url ? $url : acg_whatsapp_url();

			if ( $texto && $url ) {
				printf(
					'<a class="acg-btn acg-btn--acento acg-btn--sm" href="%s"%s>%s%s</a>',
					esc_url( $url ),
					0 === strpos( $url, 'http' ) && false === strpos( $url, home_url() ) ? ' target="_blank" rel="noopener noreferrer"' : '',
					acg_icon( 'whatsapp', 17 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html( $texto )
				);
			}
			break;

		case 'social':
			acg_the_social();
			break;

		case 'contact_info':
			acg_the_contact_info();
			break;

		case 'search':
			get_search_form();
			break;

		case 'copyright':
			printf(
				'<p class="acg-pie__copy">%s</p>',
				esc_html(
					sprintf(
						/* translators: 1: año, 2: nombre del sitio. */
						__( '© %1$s %2$s. Todos los derechos reservados.', 'acg-visual' ),
						date_i18n( 'Y' ),
						get_bloginfo( 'name' )
					)
				)
			);
			break;

		case 'custom_text':
			$texto = isset( $params['text'] ) ? $params['text'] : '';
			if ( $texto ) {
				printf( '<div class="acg-builder-texto">%s</div>', wp_kses_post( wpautop( $texto ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			break;

		case 'footer_widgets_1':
		case 'footer_widgets_2':
		case 'footer_widgets_3':
			$columna = substr( $tipo, -1 );
			if ( is_active_sidebar( 'footer-' . $columna ) ) {
				echo '<div class="acg-pie__col">';
				dynamic_sidebar( 'footer-' . $columna );
				echo '</div>';
			}
			break;
	}
}

/**
 * Pinta un menú si existe en esa ubicación.
 *
 * @param string $ubicacion Ubicación registrada.
 * @param string $clase     Clase de la lista.
 * @return void
 */
function acg_menu( $ubicacion, $clase ) {
	if ( ! has_nav_menu( $ubicacion ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $ubicacion,
			'container'      => false,
			'menu_class'     => $clase,
			'depth'          => 'primary' === $ubicacion ? 2 : 1,
			'fallback_cb'    => false,
		)
	);
}

/**
 * Control del Personalizador: el constructor visual de arrastrar y soltar.
 *
 * Va dentro de un `class_exists` porque este archivo se carga también en el
 * front, donde `WP_Customize_Control` no existe.
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
	/**
	 * Control de disposición por zonas.
	 */
	class ACG_Layout_Control extends WP_Customize_Control {

		/**
		 * Tipo de control.
		 *
		 * @var string
		 */
		public $type = 'acg_layout';

		/**
		 * 'header' o 'footer'.
		 *
		 * @var string
		 */
		public $contexto = 'header';

		/**
		 * Carga el JS/CSS del constructor y le pasa el catálogo.
		 *
		 * @return void
		 */
		public function enqueue() {
			wp_enqueue_style(
				'acg-layout-builder',
				ACG_URI . '/assets/css/layout-builder-control.css',
				array(),
				acg_asset_version( 'assets/css/layout-builder-control.css' )
			);

			wp_enqueue_script(
				'acg-layout-builder',
				ACG_URI . '/assets/js/layout-builder-control.js',
				array( 'jquery', 'customize-controls' ),
				acg_asset_version( 'assets/js/layout-builder-control.js' ),
				true
			);

			wp_localize_script(
				'acg-layout-builder',
				'acgLayoutBuilder',
				array(
					'registry' => acg_layout_element_registry(),
					'rows'     => array(
						'header' => acg_layout_rows( 'header' ),
						'footer' => acg_layout_rows( 'footer' ),
					),
					'zones'    => acg_layout_zones(),
					'i18n'     => array(
						'available'       => __( 'Elementos disponibles — arrástralos a una columna', 'acg-visual' ),
						'empty'           => __( 'Suelta aquí un elemento', 'acg-visual' ),
						'remove'          => __( 'Quitar', 'acg-visual' ),
						'allUsed'         => __( 'Ya has colocado todos los elementos disponibles.', 'acg-visual' ),
						'textPlaceholder' => __( 'Escribe el texto (admite HTML sencillo)…', 'acg-visual' ),
					),
				)
			);
		}

		/**
		 * Pinta el envoltorio; el contenido lo rellena el JS con el valor
		 * actual del ajuste.
		 *
		 * @return void
		 */
		public function render_content() {
			?>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			<div class="acg-layout-builder" data-contexto="<?php echo esc_attr( $this->contexto ); ?>" data-setting="<?php echo esc_attr( $this->id ); ?>"></div>
			<?php
		}
	}
}

/**
 * Registra las dos secciones del constructor en el Personalizador.
 *
 * @param WP_Customize_Manager $wp_customize Gestor del Personalizador.
 * @return void
 */
function acg_register_layout_customizer( $wp_customize ) {
	if ( ! class_exists( 'ACG_Layout_Control' ) ) {
		return;
	}

	$secciones = array(
		'header' => array(
			'id'          => 'acg_header_builder',
			'title'       => __( 'Diseñador de encabezado', 'acg-visual' ),
			'priority'    => 8,
			'setting'     => 'acg_header_layout',
			'sanitize'    => 'acg_sanitize_header_layout',
			'label'       => __( 'Zonas del encabezado', 'acg-visual' ),
			'description' => __( 'Arrastra cada elemento a la columna donde quieras que aparezca. El contenido de cada uno (textos, enlaces, números) se sigue editando en su propia sección — aquí solo decides qué se ve y en qué orden.', 'acg-visual' ),
		),
		'footer' => array(
			'id'          => 'acg_footer_builder',
			'title'       => __( 'Diseñador de pie de página', 'acg-visual' ),
			'priority'    => 68,
			'setting'     => 'acg_footer_layout',
			'sanitize'    => 'acg_sanitize_footer_layout',
			'label'       => __( 'Zonas del pie', 'acg-visual' ),
			'description' => __( 'Igual que el encabezado. Los textos y enlaces se editan en «Contacto» y «Redes sociales».', 'acg-visual' ),
		),
	);

	foreach ( $secciones as $contexto => $datos ) {
		$wp_customize->add_section(
			$datos['id'],
			array(
				'title'       => $datos['title'],
				'panel'       => 'acg_panel',
				'priority'    => $datos['priority'],
				'description' => $datos['description'],
			)
		);

		$wp_customize->add_setting(
			$datos['setting'],
			array(
				'default'           => wp_json_encode( acg_default_layout( $contexto ) ),
				'sanitize_callback' => $datos['sanitize'],
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new ACG_Layout_Control(
				$wp_customize,
				$datos['setting'],
				array(
					'section'  => $datos['id'],
					'contexto' => $contexto,
					'label'    => $datos['label'],
				)
			)
		);
	}
}
// Prioridad 20: el panel «acg_panel» lo crea inc/customizer.php en la 10, y
// una sección tiene que declararse cuando su panel ya existe.
add_action( 'customize_register', 'acg_register_layout_customizer', 20 );
