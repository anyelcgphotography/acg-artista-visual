<?php
/**
 * Configuración base del theme: soportes, menús, imágenes, CPTs y taxonomías.
 *
 * Los tipos de contenido se registran aquí y no en el plugin porque son la
 * materia prima de las secciones de la portada: sin ellos el theme no tiene
 * nada que pintar. El plugin ACG CRM se ocupa solo de los leads, así que el
 * sitio sigue siendo editable aunque el CRM se desactive.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Soportes del theme, menús y tamaños de imagen.
 *
 * @return void
 */
function acg_setup() {
	load_theme_textdomain( 'acg-visual', ACG_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 420,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menú principal', 'acg-visual' ),
			'footer'  => __( 'Menú del pie', 'acg-visual' ),
			'legal'   => __( 'Menú legal', 'acg-visual' ),
		)
	);

	// Recortes pensados para el mosaico del portafolio: uno apaisado (3/2) y
	// uno vertical (4/5), que son las dos proporciones que usa la rejilla.
	add_image_size( 'acg-apaisada', 1600, 1067, true );
	add_image_size( 'acg-vertical', 1000, 1250, true );
	add_image_size( 'acg-tarjeta', 900, 675, true );
}
add_action( 'after_setup_theme', 'acg_setup' );

/**
 * Zonas de widgets del pie, usadas por el diseñador de pie.
 *
 * @return void
 */
function acg_widgets_init() {
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array(
				'id'            => 'footer-' . $i,
				'name'          => sprintf(
					/* translators: %d: número de columna. */
					__( 'Pie — columna %d', 'acg-visual' ),
					$i
				),
				'description'   => __( 'Widgets que puedes colocar en el pie con el diseñador de pie de página.', 'acg-visual' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'acg_widgets_init' );

/**
 * Etiquetas de un CPT en español, con el género correcto en las cadenas que
 * lo necesitan. Evita repetir veinte líneas por cada tipo de contenido.
 *
 * @param string $singular Nombre en singular.
 * @param string $plural   Nombre en plural.
 * @param string $genero   'm' o 'f'.
 * @return array<string,string>
 */
function acg_cpt_labels( $singular, $plural, $genero = 'm' ) {
	$femenino = ( 'f' === $genero );
	$nuevo    = $femenino ? 'nueva' : 'nuevo';
	$sufijo   = $femenino ? 'a' : 'o';
	$articulo = $femenino ? 'La' : 'El';
	$minus    = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
	$sing_min = call_user_func( $minus, $singular );
	$plur_min = call_user_func( $minus, $plural );

	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new'            => sprintf( 'Añadir %s', $nuevo ),
		'add_new_item'       => sprintf( 'Añadir %s %s', $nuevo, $sing_min ),
		'edit_item'          => sprintf( 'Editar %s', $sing_min ),
		'new_item'           => sprintf( '%s %s', ucfirst( $nuevo ), $sing_min ),
		'view_item'          => sprintf( 'Ver %s', $sing_min ),
		'search_items'       => sprintf( 'Buscar %s', $plur_min ),
		'not_found'          => sprintf( 'No hay %s todavía', $plur_min ),
		'not_found_in_trash' => sprintf( 'No hay %s en la papelera', $plur_min ),
		'all_items'          => $plural,
		'archives'           => sprintf( 'Archivo de %s', $plur_min ),
		'item_published'     => sprintf( '%s %s publicad%s.', $articulo, $sing_min, $sufijo ),
		'item_updated'       => sprintf( '%s %s actualizad%s.', $articulo, $sing_min, $sufijo ),
	);
}

/**
 * Registra los tipos de contenido repetibles del sitio.
 *
 * Todo lo que se repite en la portada es un CPT y no un campo repetidor,
 * porque la versión gratuita de ACF no tiene Repeater: así Angie añade un
 * trabajo o un servicio como añadiría una entrada, y se ordenan con el campo
 * «Orden» de Atributos de página.
 *
 * @return void
 */
function acg_register_post_types() {
	register_post_type(
		'acg_trabajo',
		array(
			'labels'        => acg_cpt_labels( 'Trabajo', 'Portafolio' ),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-format-gallery',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'rewrite'       => array( 'slug' => 'portafolio' ),
			'show_in_rest'  => true,
			'taxonomies'    => array( 'acg_categoria' ),
		)
	);

	register_post_type(
		'acg_servicio',
		array(
			'labels'        => acg_cpt_labels( 'Servicio', 'Servicios' ),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-camera',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'rewrite'       => array( 'slug' => 'servicios' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'acg_proceso',
		array(
			'labels'        => acg_cpt_labels( 'Paso del proceso', 'Proceso' ),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-editor-ol',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'acg_testimonio',
		array(
			'labels'        => acg_cpt_labels( 'Testimonio', 'Testimonios' ),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'acg_equipo',
		array(
			'labels'        => acg_cpt_labels( 'Equipo de trabajo', 'Equipo de trabajo' ),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-camera-alt',
			'menu_position' => 24,
			'supports'      => array( 'title', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'acg_faq',
		array(
			'labels'        => acg_cpt_labels( 'Pregunta frecuente', 'Preguntas frecuentes', 'f' ),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-editor-help',
			'menu_position' => 25,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'acg_register_post_types' );

/**
 * Categorías del portafolio: alimentan los filtros de la rejilla.
 *
 * @return void
 */
function acg_register_taxonomies() {
	register_taxonomy(
		'acg_categoria',
		array( 'acg_trabajo' ),
		array(
			'labels'            => array(
				'name'          => __( 'Categorías del portafolio', 'acg-visual' ),
				'singular_name' => __( 'Categoría', 'acg-visual' ),
				'menu_name'     => __( 'Categorías', 'acg-visual' ),
				'add_new_item'  => __( 'Añadir categoría', 'acg-visual' ),
				'edit_item'     => __( 'Editar categoría', 'acg-visual' ),
				'search_items'  => __( 'Buscar categorías', 'acg-visual' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'categoria-portafolio' ),
		)
	);
}
add_action( 'init', 'acg_register_taxonomies' );

/**
 * Ordena los CPTs por el campo «Orden» en el admin y en el front, que es
 * como se controla la secuencia de las secciones sin campos repetidores.
 *
 * @param WP_Query $query Consulta principal.
 * @return void
 */
function acg_order_cpt_queries( $query ) {
	$tipos = array( 'acg_trabajo', 'acg_servicio', 'acg_proceso', 'acg_testimonio', 'acg_equipo', 'acg_faq' );

	if ( is_admin() && ! wp_doing_ajax() ) {
		$tipo_pantalla = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( in_array( $tipo_pantalla, $tipos, true ) && ! isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}

		return;
	}

	if ( ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( $tipos ) || $query->is_tax( 'acg_categoria' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', 24 );
	}
}
add_action( 'pre_get_posts', 'acg_order_cpt_queries' );

/**
 * Vacía las reglas de reescritura la primera vez que se activa el theme,
 * para que los archivos de portafolio y servicios respondan sin tener que
 * entrar a Ajustes → Enlaces permanentes.
 *
 * @return void
 */
function acg_flush_rewrites_once() {
	if ( get_option( 'acg_rewrites_flushed' ) === ACG_VERSION ) {
		return;
	}

	acg_register_post_types();
	acg_register_taxonomies();
	flush_rewrite_rules();
	update_option( 'acg_rewrites_flushed', ACG_VERSION );
}
add_action( 'after_switch_theme', 'acg_flush_rewrites_once' );
add_action( 'admin_init', 'acg_flush_rewrites_once' );

/**
 * Ancho del contenido, usado por WordPress para incrustados.
 *
 * @return void
 */
function acg_content_width() {
	$GLOBALS['content_width'] = 1240;
}
add_action( 'after_setup_theme', 'acg_content_width', 0 );

/**
 * Columnas propias de la lista de testimonios: origen y valoración.
 *
 * Sin esto, una reseña copiada de Google Business se ve igual que una escrita
 * a mano en la lista del admin, y no hay forma de distinguirlas sin abrir
 * cada una.
 *
 * @param array $columnas Columnas actuales.
 * @return array
 */
function acg_columnas_testimonio( $columnas ) {
	$nueva = array();

	foreach ( $columnas as $clave => $etiqueta ) {
		$nueva[ $clave ] = $etiqueta;

		if ( 'title' === $clave ) {
			$nueva['acg_fuente']     = __( 'Origen', 'acg-visual' );
			$nueva['acg_valoracion'] = __( 'Valoración', 'acg-visual' );
		}
	}

	return $nueva;
}
add_filter( 'manage_acg_testimonio_posts_columns', 'acg_columnas_testimonio' );

/**
 * Contenido de las columnas propias de testimonios.
 *
 * @param string $columna Columna que se pinta.
 * @param int    $post_id ID del testimonio.
 * @return void
 */
function acg_columna_testimonio( $columna, $post_id ) {
	if ( 'acg_fuente' === $columna ) {
		$fuente = get_post_meta( $post_id, 'fuente', true );
		echo 'google' === $fuente
			? esc_html__( 'Google Business', 'acg-visual' )
			: esc_html__( 'Manual', 'acg-visual' );
		return;
	}

	if ( 'acg_valoracion' === $columna ) {
		$valoracion = (int) get_post_meta( $post_id, 'valoracion', true );
		echo $valoracion ? esc_html( str_repeat( '★', $valoracion ) ) : '—';
	}
}
add_action( 'manage_acg_testimonio_posts_custom_column', 'acg_columna_testimonio', 10, 2 );
