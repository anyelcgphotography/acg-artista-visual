<?php
/**
 * Piezas reutilizables de plantilla: logotipo, iconos, redes, cabeceras de
 * sección y utilidades del portafolio.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Logotipo del sitio.
 *
 * Si hay logotipo subido se usa ese; si no, se pinta la marca vectorial (el
 * hexágono + «ACG») en SVG en línea, que se recolorea solo con la paleta y no
 * necesita dos archivos para fondo claro y oscuro.
 *
 * @param array $args Opcional: 'contexto' ('header' o 'footer').
 * @return void
 */
function acg_logo( $args = array() ) {
	$args     = wp_parse_args( $args, array( 'contexto' => 'header' ) );
	$subtitulo = acg_t(
		get_theme_mod( 'acg_marca_subtitulo', 'ARTISTA VISUAL' ),
		get_theme_mod( 'acg_marca_subtitulo_pt', '' )
	);
	?>
	<a class="acg-marca acg-marca--<?php echo esc_attr( $args['contexto'] ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php if ( has_custom_logo() ) : ?>
			<?php
			$logo_id = get_theme_mod( 'custom_logo' );
			echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'acg-marca__img', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ) );
			?>
		<?php else : ?>
			<?php acg_monograma(); ?>
			<span class="acg-marca__texto">
				<span class="acg-marca__nombre"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				<?php if ( $subtitulo ) : ?>
					<span class="acg-marca__sub"><?php echo esc_html( $subtitulo ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</a>
	<?php
}

/**
 * Hexágono de la marca en SVG.
 *
 * Es el mismo de la marca de agua de Angie: un hexágono abierto por los
 * costados, en dos trazos. Va en línea y con `currentColor` para que herede
 * el color de donde se coloque, sin necesitar una versión por fondo.
 *
 * @param int $tamano Lado en píxeles.
 * @return void
 */
function acg_monograma( $tamano = 30 ) {
	printf(
		'<svg class="acg-monograma" viewBox="0 0 100 100" width="%1$d" height="%1$d" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true" focusable="false">'
			. '<path d="M8 42V30L50 6l42 24v12"/>'
			. '<path d="M8 58v12l42 24 42-24V58"/>'
			. '</svg>',
		absint( $tamano )
	);
}

/**
 * Iconos de interfaz en SVG en línea.
 *
 * En línea y no como fuente de iconos: son cinco trazos, pesan menos que una
 * petición y heredan el color del texto sin trucos.
 *
 * @param string $nombre Identificador del icono.
 * @param int    $tamano Lado en píxeles.
 * @return string HTML del SVG.
 */
function acg_icon( $nombre, $tamano = 20 ) {
	$trazos = array(
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/>',
		'linkedin'  => '<path d="M4.5 9.5v10M4.5 5.2v.1M10 19.5v-10M10 13c0-2 1.4-3.5 3.4-3.5S17 11 17 13.4v6.1"/>',
		'facebook'  => '<path d="M14.5 8.5h2.2M14.5 21V8.6c0-1.7 1-2.6 2.7-2.6h1.3M11 12.5h6"/>',
		'behance'   => '<path d="M2.5 6.5h5.2c1.7 0 2.6.9 2.6 2.2s-.9 2.2-2.6 2.2H2.5zM2.5 10.9h5.6c1.9 0 2.9 1 2.9 2.5s-1 2.5-2.9 2.5H2.5zM14 6.9h6M21.5 13.4c0-2.2-1.4-3.7-3.5-3.7s-3.6 1.6-3.6 3.8 1.4 3.8 3.6 3.8c1.7 0 2.9-.8 3.3-2.2M14.6 13.4h6.9"/>',
		'pinterest' => '<path d="M12 3a8.5 8.5 0 0 0-3.3 16.3M12 3a8.5 8.5 0 0 1 1.3 16.9M10 21l2.4-9M9.5 12.5c-.6-1.9.4-4 2.6-4s3 1.6 2.7 3.4c-.4 2-1.8 3.2-3.2 2.8"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10 9.6l5 2.4-5 2.4z"/>',
		'whatsapp'  => '<path d="M3.5 20.5l1.3-4.3A8 8 0 1 1 8 19.4z"/><path d="M8.8 9.2c.3 2.6 3.4 5.4 5.9 5.7.7.1 1.4-.6 1.5-1.3l-2-.9-1 1c-1-.5-2-1.4-2.5-2.5l1-1-.9-2c-.7.1-1.4.7-1.3 1.5z"/>',
		'mail'      => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M3 7l9 6 9-6"/>',
		'phone'     => '<path d="M6 3.5h3l1.5 4-2 1.5a12 12 0 0 0 6.5 6.5l1.5-2 4 1.5v3c0 1-.8 1.8-1.8 1.7A17.5 17.5 0 0 1 4.3 5.3 1.7 1.7 0 0 1 6 3.5z"/>',
		'pin'       => '<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
		'flecha'    => '<path d="M4 12h15M13 6l6 6-6 6"/>',
		'buscar'    => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
		'cerrar'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'mas'       => '<path d="M12 5v14M5 12h14"/>',
		'menos'     => '<path d="M5 12h14"/>',
	);

	if ( ! isset( $trazos[ $nombre ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="acg-icono acg-icono--%1$s" viewBox="0 0 24 24" width="%2$d" height="%2$d" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $nombre ),
		absint( $tamano ),
		$trazos[ $nombre ]
	);
}

/**
 * Enlace de WhatsApp con el número del Personalizador.
 *
 * @param string $texto Mensaje que se precarga en el chat.
 * @return string URL, o cadena vacía si no hay número configurado.
 */
function acg_whatsapp_url( $texto = '' ) {
	$numero = preg_replace( '/[^0-9]/', '', (string) get_theme_mod( 'acg_whatsapp', '5516992193838' ) );

	if ( ! $numero ) {
		return '';
	}

	$url = 'https://wa.me/' . $numero;

	if ( $texto ) {
		$url = add_query_arg( 'text', rawurlencode( $texto ), $url );
	}

	return $url;
}

/**
 * Lista de redes sociales configuradas.
 *
 * @return void
 */
function acg_the_social() {
	$redes = array();

	foreach ( acg_social_networks() as $id => $datos ) {
		$url = get_theme_mod( 'acg_social_' . $id, $datos['default'] );

		if ( $url ) {
			$redes[ $id ] = array( 'url' => $url, 'label' => $datos['label'] );
		}
	}

	$whatsapp = acg_whatsapp_url();

	if ( $whatsapp ) {
		$redes['whatsapp'] = array( 'url' => $whatsapp, 'label' => 'WhatsApp' );
	}

	if ( ! $redes ) {
		return;
	}
	?>
	<ul class="acg-redes">
		<?php foreach ( $redes as $id => $red ) : ?>
			<li>
				<a href="<?php echo esc_url( $red['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $red['label'] ); ?>">
					<?php echo acg_icon( $id, 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="acg-redes__texto"><?php echo esc_html( $red['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Datos de contacto en bloque (teléfono, email, ciudad).
 *
 * @return void
 */
function acg_the_contact_info() {
	$telefono = get_theme_mod( 'acg_telefono', '' );
	$email    = get_theme_mod( 'acg_email', '' );
	$ciudad   = get_theme_mod( 'acg_ciudad', '' );

	if ( ! $telefono && ! $email && ! $ciudad ) {
		return;
	}
	?>
	<ul class="acg-contacto-info">
		<?php if ( $telefono ) : ?>
			<li><?php echo acg_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>"><?php echo esc_html( $telefono ); ?></a></li>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<li><?php echo acg_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
		<?php endif; ?>
		<?php if ( $ciudad ) : ?>
			<li><?php echo acg_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $ciudad ); ?></span></li>
		<?php endif; ?>
	</ul>
	<?php
}

/**
 * Abre una sección de la portada con su esquema de color y su ancla.
 *
 * @param string $id      Identificador de la sección.
 * @param array  $args    'ancla', 'clase', 'etiqueta'.
 * @return void
 */
function acg_section_open( $id, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'ancla'    => $id,
			'clase'    => '',
			'etiqueta' => 'section',
		)
	);

	printf(
		'<%1$s id="%2$s" class="acg-seccion acg-seccion--%3$s acg-esquema--%4$s%5$s">',
		tag_escape( $args['etiqueta'] ),
		esc_attr( $args['ancla'] ),
		esc_attr( $id ),
		esc_attr( acg_section_scheme( $id ) ),
		$args['clase'] ? ' ' . esc_attr( $args['clase'] ) : ''
	);
}

/**
 * Cierra una sección abierta con acg_section_open().
 *
 * @param string $etiqueta Etiqueta HTML usada al abrir.
 * @return void
 */
function acg_section_close( $etiqueta = 'section' ) {
	printf( '</%s>', tag_escape( $etiqueta ) );
}

/**
 * Cabecera de sección: número, epígrafe y titular.
 *
 * @param array $args 'numero', 'epigrafe', 'titulo', 'texto', 'clase'.
 * @return void
 */
function acg_section_header( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'numero'   => '',
			'epigrafe' => '',
			'titulo'   => '',
			'texto'    => '',
			'clase'    => '',
		)
	);

	if ( ! $args['epigrafe'] && ! $args['titulo'] ) {
		return;
	}
	?>
	<div class="acg-seccion__cabecera <?php echo esc_attr( $args['clase'] ); ?>">
		<div>
			<?php if ( $args['epigrafe'] ) : ?>
				<p class="acg-epigrafe">
					<?php if ( $args['numero'] ) : ?>
						<span class="acg-epigrafe__num"><?php echo esc_html( $args['numero'] ); ?></span> —
					<?php endif; ?>
					<?php echo esc_html( $args['epigrafe'] ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $args['titulo'] ) : ?>
				<h2 class="acg-titulo"><?php echo esc_html( $args['titulo'] ); ?></h2>
			<?php endif; ?>
		</div>
		<?php if ( $args['texto'] ) : ?>
			<p class="acg-seccion__intro"><?php echo esc_html( $args['texto'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Imagen de un trabajo o servicio, con recorte según la proporción elegida.
 *
 * @param int    $post_id ID del post.
 * @param string $tamano  Tamaño registrado.
 * @param array  $attr    Atributos extra.
 * @return void
 */
function acg_thumb( $post_id = null, $tamano = 'acg-tarjeta', $attr = array() ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( ! has_post_thumbnail( $post_id ) ) {
		echo '<span class="acg-thumb acg-thumb--vacia" aria-hidden="true"></span>';
		return;
	}

	$attr = wp_parse_args( $attr, array( 'class' => 'acg-thumb', 'loading' => 'lazy', 'decoding' => 'async' ) );

	echo wp_get_attachment_image( get_post_thumbnail_id( $post_id ), $tamano, false, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Consulta preparada de un CPT del theme, ya ordenada.
 *
 * @param string $tipo   Nombre del CPT.
 * @param int    $limite Número máximo de elementos.
 * @param array  $extra  Argumentos adicionales de WP_Query.
 * @return WP_Query
 */
function acg_query( $tipo, $limite = -1, $extra = array() ) {
	return new WP_Query(
		array_merge(
			array(
				'post_type'              => $tipo,
				'posts_per_page'         => $limite,
				'orderby'                => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
			),
			$extra
		)
	);
}

/**
 * Proporción CSS elegida para un trabajo del portafolio.
 *
 * @param int $post_id ID del trabajo.
 * @return string Valor para aspect-ratio.
 */
function acg_aspect_ratio( $post_id = null ) {
	$valor = acg_raw_field( 'formato', $post_id ? $post_id : get_the_ID() );

	return 'apaisada' === $valor ? '3 / 2' : '4 / 5';
}

/**
 * ¿Este trabajo ocupa dos columnas del mosaico?
 *
 * @param int $post_id ID del trabajo.
 * @return bool
 */
function acg_is_wide( $post_id = null ) {
	return 'apaisada' === acg_raw_field( 'formato', $post_id ? $post_id : get_the_ID() );
}

/**
 * Migas de pan sencillas para las plantillas internas.
 *
 * @return void
 */
function acg_breadcrumbs() {
	?>
	<nav class="acg-migas" aria-label="<?php esc_attr_e( 'Migas de pan', 'acg-visual' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( acg_s( 'inicio' ) ); ?></a>
		<?php if ( is_singular( 'acg_trabajo' ) || is_post_type_archive( 'acg_trabajo' ) || is_tax( 'acg_categoria' ) ) : ?>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'acg_trabajo' ) ); ?>"><?php echo esc_html( acg_s( 'nav_portafolio' ) ); ?></a>
		<?php elseif ( is_singular( 'acg_servicio' ) || is_post_type_archive( 'acg_servicio' ) ) : ?>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'acg_servicio' ) ); ?>"><?php echo esc_html( acg_s( 'nav_servicios' ) ); ?></a>
		<?php endif; ?>
		<?php if ( is_singular() ) : ?>
			<span aria-hidden="true">/</span>
			<span aria-current="page"><?php echo esc_html( acg_title() ); ?></span>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Botón que abre el menú en móvil. Vive fuera del diseñador de cabecera
 * porque tiene que existir siempre, se ponga lo que se ponga en las zonas.
 *
 * @return void
 */
function acg_the_burger_button() {
	?>
	<button class="acg-burger" type="button" aria-expanded="false" aria-controls="acg-menu-movil" aria-label="<?php echo esc_attr( acg_s( 'abrir_menu' ) ); ?>">
		<span></span><span></span><span></span>
	</button>
	<?php
}

/**
 * ID de la página asignada como portada, o 0 si el sitio muestra las últimas
 * entradas. Los campos de la portada se guardan en esa página.
 *
 * @return int
 */
function acg_front_id() {
	return (int) get_option( 'page_on_front' );
}

/**
 * Campo de la portada en el idioma activo.
 *
 * @param string $campo   Nombre base del campo.
 * @param string $defecto Valor si no hay nada guardado.
 * @return string
 */
function acg_home( $campo, $defecto = '' ) {
	$id = acg_front_id();

	return $id ? acg_field( $campo, $id, $defecto ) : $defecto;
}

/**
 * Campo crudo de la portada, sin resolver idioma (imágenes, números, switches).
 *
 * @param string $campo   Nombre del campo.
 * @param mixed  $defecto Valor si no hay nada guardado.
 * @return mixed
 */
function acg_home_raw( $campo, $defecto = '' ) {
	$id = acg_front_id();

	if ( ! $id ) {
		return $defecto;
	}

	$valor = acg_raw_field( $campo, $id );

	return ( '' === $valor || null === $valor ) ? $defecto : $valor;
}

/**
 * Convierte un textarea de una línea por elemento en un array limpio.
 *
 * @param string $texto Contenido del campo.
 * @return string[]
 */
function acg_lines( $texto ) {
	$lineas = preg_split( '/\r\n|\r|\n/', (string) $texto );
	$lineas = array_map( 'trim', $lineas );

	return array_values( array_filter( $lineas, static function ( $linea ) {
		return '' !== $linea;
	} ) );
}

/**
 * Estrellas de valoración de un testimonio (1 a 5), en SVG.
 *
 * Se dibuja aquí y no con acg_icon() porque las estrellas necesitan rellenarse
 * o no según la puntuación; el resto de iconos del theme son siempre un único
 * trazo sin relleno.
 *
 * @param int $valoracion Puntuación de 0 a $maximo. 0 no pinta nada.
 * @param int $maximo     Número total de estrellas.
 * @param int $tamano     Lado de cada estrella en píxeles.
 * @return string HTML, o cadena vacía si no hay valoración.
 */
function acg_star_rating( $valoracion, $maximo = 5, $tamano = 15 ) {
	$valoracion = max( 0, min( $maximo, (int) $valoracion ) );

	if ( ! $valoracion ) {
		return '';
	}

	$punta  = '10,1.5 12.4,7 18.5,7.6 13.9,11.6 15.2,17.5 10,14.3 4.8,17.5 6.1,11.6 1.5,7.6 7.6,7';
	$salida = sprintf(
		'<span class="acg-estrellas" role="img" aria-label="%s">',
		esc_attr(
			sprintf(
				/* translators: 1: puntuación, 2: máximo de estrellas. */
				__( 'Valoración: %1$d de %2$d', 'acg-visual' ),
				$valoracion,
				$maximo
			)
		)
	);

	for ( $i = 1; $i <= $maximo; $i++ ) {
		$salida .= sprintf(
			'<svg class="acg-estrella%1$s" viewBox="0 0 20 19" width="%2$d" height="%2$d" aria-hidden="true" focusable="false"><polygon points="%3$s"/></svg>',
			$i <= $valoracion ? ' is-activa' : '',
			absint( $tamano ),
			$punta
		);
	}

	return $salida . '</span>';
}
