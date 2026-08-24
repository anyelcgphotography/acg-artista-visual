<?php
/**
 * Definición de todos los campos editables del sitio.
 *
 * Los campos se declaran **una sola vez** en `acg_field_groups()` y desde ahí
 * se registran en ACF (este archivo) o como meta boxes nativas si ACF no está
 * instalado (inc/meta-fallback.php). Una única fuente de verdad: si mañana se
 * añade un campo, aparece en las dos interfaces sin tocar nada más.
 *
 * Restricciones que impone la versión gratuita de ACF, y cómo se resuelven:
 *
 * - Sin Repeater → todo lo repetible es un CPT (portafolio, servicios, pasos
 *   del proceso, testimonios, equipo, FAQ).
 * - Sin Options Page → los ajustes globales viven en el Personalizador.
 * - Sin Gallery → las galerías se arman con el CPT «Portafolio».
 * - Las listas cortas (especialidades, «también por encargo») son un textarea
 *   con una línea por elemento.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Un campo, en la forma que entienden los dos registradores.
 *
 * @param string $nombre Clave del campo (también la meta key).
 * @param string $label  Etiqueta visible.
 * @param string $tipo   text|textarea|wysiwyg|image|select|true_false|url|number.
 * @param array  $extra  'instructions', 'default', 'choices', 'rows', 'ancho'.
 * @return array
 */
function acg_def_field( $nombre, $label, $tipo = 'text', $extra = array() ) {
	return array_merge(
		array(
			'name'         => $nombre,
			'label'        => $label,
			'type'         => $tipo,
			'instructions' => '',
			'default'      => '',
			'choices'      => array(),
			'rows'         => 4,
			'ancho'        => 100,
		),
		$extra
	);
}

/**
 * El mismo campo en sus dos idiomas.
 *
 * El gemelo portugués va siempre justo detrás del español y a media anchura,
 * para que en el panel se lean como una pareja y no como dos campos sueltos.
 *
 * @param string $nombre Clave base.
 * @param string $label  Etiqueta visible.
 * @param string $tipo   Tipo de campo.
 * @param array  $extra  Extras.
 * @return array<int,array> Dos campos.
 */
function acg_def_pair( $nombre, $label, $tipo = 'text', $extra = array() ) {
	$es = acg_def_field( $nombre, $label, $tipo, array_merge( $extra, array( 'ancho' => 50 ) ) );
	$pt = acg_def_field(
		$nombre . '_pt',
		$label . ' (PT)',
		$tipo,
		array_merge(
			$extra,
			array(
				'ancho'        => 50,
				'instructions' => __( 'Versión en portugués. Si lo dejas vacío se muestra el texto en español.', 'acg-visual' ),
			)
		)
	);

	return array( $es, $pt );
}

/**
 * Añade varios campos a una lista de golpe.
 *
 * @param array $lista   Lista destino (por referencia).
 * @param array $campos  Campos a añadir.
 * @return void
 */
function acg_push_fields( &$lista, $campos ) {
	foreach ( $campos as $campo ) {
		$lista[] = $campo;
	}
}

/**
 * Catálogo completo de grupos de campos del theme.
 *
 * @return array<string,array{title:string,location:array,fields:array}>
 */
function acg_field_groups() {
	$grupos = array();

	// ---------------------------------------------------------------- Portada.
	$portada = array();

	$portada[] = acg_def_field( 'sep_hero', __( 'Portada (hero)', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_hero',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'hero_epigrafe', __( 'Línea sobre el titular', 'acg-visual' ), 'text', array( 'default' => 'ANYEL C. GONZÁLEZ · ARTISTA VISUAL' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_titulo', __( 'Titular', 'acg-visual' ), 'textarea', array( 'rows' => 2, 'default' => 'Bodas y eventos, fotografiados sin interrumpirlos.' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_texto', __( 'Texto de entrada', 'acg-visual' ), 'textarea', array( 'rows' => 3 ) ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_boton_1', __( 'Botón principal — texto', 'acg-visual' ), 'text', array( 'default' => 'Pedir presupuesto' ) ) );
	$portada[] = acg_def_field( 'hero_boton_1_url', __( 'Botón principal — enlace', 'acg-visual' ), 'text', array( 'default' => '#contacto', 'ancho' => 50 ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_boton_2', __( 'Botón secundario — texto', 'acg-visual' ), 'text', array( 'default' => 'Ver portafolio' ) ) );
	$portada[] = acg_def_field( 'hero_boton_2_url', __( 'Botón secundario — enlace', 'acg-visual' ), 'text', array( 'default' => '#portafolio', 'ancho' => 50 ) );
	$portada[] = acg_def_field( 'hero_imagen', __( 'Foto de fondo', 'acg-visual' ), 'image', array( 'instructions' => __( 'Horizontal y con espacio libre a la izquierda: encima va el titular. Mínimo 1920 px de ancho.', 'acg-visual' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_pie_equipo', __( 'Pie del hero — equipo', 'acg-visual' ), 'text', array( 'default' => 'NIKON D3300 · GODOX V860II-N · 18-55 / 70-300 MM' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'hero_pie_estado', __( 'Pie del hero — disponibilidad', 'acg-visual' ), 'text', array( 'default' => 'AGENDA ABIERTA' ) ) );

	$portada[] = acg_def_field( 'sep_marquesina', __( 'Cinta de especialidades', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_marquesina',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields(
		$portada,
		acg_def_pair(
			'marquesina_texto',
			__( 'Palabras de la cinta', 'acg-visual' ),
			'textarea',
			array(
				'rows'         => 2,
				'instructions' => __( 'Separadas por el símbolo · o por comas. Se repiten en bucle.', 'acg-visual' ),
				'default'      => 'BODAS · EVENTOS · MARCA PERSONAL · RETRATO · PRODUCTO · NATURALEZA · MASCOTAS · DEPORTE · STOCK',
			)
		)
	);

	$portada[] = acg_def_field( 'sep_portafolio', __( 'Portafolio', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_portafolio',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'portafolio_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'PORTAFOLIO' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'portafolio_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Trabajo reciente' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'portafolio_texto', __( 'Texto de apoyo', 'acg-visual' ), 'textarea', array( 'rows' => 3 ) ) );
	$portada[] = acg_def_field(
		'portafolio_columnas',
		__( 'Columnas del mosaico', 'acg-visual' ),
		'select',
		array(
			'choices' => array( '2' => '2', '3' => '3', '4' => '4' ),
			'default' => '3',
			'ancho'   => 50,
		)
	);
	$portada[] = acg_def_field(
		'portafolio_limite',
		__( 'Cuántos trabajos se muestran', 'acg-visual' ),
		'number',
		array( 'default' => '6', 'ancho' => 50, 'instructions' => __( '0 = todos.', 'acg-visual' ) )
	);
	$portada[] = acg_def_field( 'portafolio_filtros', __( 'Mostrar los filtros por categoría', 'acg-visual' ), 'true_false', array( 'default' => '1' ) );

	$portada[] = acg_def_field( 'sep_servicios', __( 'Servicios', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_servicios',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'servicios_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'SERVICIOS' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'servicios_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Tres coberturas principales' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'servicios_lista_titulo', __( 'Título de la lista secundaria', 'acg-visual' ), 'text', array( 'default' => 'TAMBIÉN POR ENCARGO' ) ) );
	acg_push_fields(
		$portada,
		acg_def_pair(
			'servicios_lista',
			__( 'Lista secundaria', 'acg-visual' ),
			'textarea',
			array(
				'rows'         => 8,
				'instructions' => __( 'Un servicio por línea.', 'acg-visual' ),
				'default'      => "Retratos fotográficos\nFotografía de productos\nFotografía de deportes\nFotografía de naturaleza\nFotografía de stock\nFotografía de restaurantes\nPrimeros planos / macro\nFotografía de mascotas",
			)
		)
	);

	$portada[] = acg_def_field( 'sep_sobre', __( 'Sobre mí', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_sobre',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'sobre_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'SOBRE MÍ' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'sobre_titulo', __( 'Nombre o titular', 'acg-visual' ), 'text', array( 'default' => 'Anyel C. González' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'sobre_texto', __( 'Biografía', 'acg-visual' ), 'wysiwyg' ) );
	$portada[] = acg_def_field( 'sobre_imagen', __( 'Retrato', 'acg-visual' ), 'image', array( 'instructions' => __( 'Vertical (proporción 4:5).', 'acg-visual' ) ) );

	$portada[] = acg_def_field( 'sep_proceso', __( 'Proceso', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_proceso',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'proceso_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'PROCESO' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'proceso_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Cómo trabajamos' ) ) );

	$portada[] = acg_def_field( 'sep_testimonios', __( 'Testimonios', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_testimonios',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'testimonios_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'CLIENTES' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'testimonios_titulo', __( 'Titular (opcional)', 'acg-visual' ), 'text' ) );

	$portada[] = acg_def_field( 'sep_equipo', __( 'Equipo de trabajo', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_equipo',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'equipo_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Equipo de trabajo' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'equipo_texto', __( 'Texto de apoyo (opcional)', 'acg-visual' ), 'textarea', array( 'rows' => 2 ) ) );

	$portada[] = acg_def_field( 'sep_faq', __( 'Preguntas frecuentes', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_faq',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'faq_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'PREGUNTAS FRECUENTES' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'faq_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Antes de escribir' ) ) );

	$portada[] = acg_def_field( 'sep_contacto', __( 'Contacto', 'acg-visual' ), 'tab' );
	$portada[] = acg_def_field(
		'activo_contacto',
		__( 'Mostrar esta sección en la portada', 'acg-visual' ),
		'true_false',
		array( 'default' => '1' )
	);
	acg_push_fields( $portada, acg_def_pair( 'contacto_epigrafe', __( 'Epígrafe', 'acg-visual' ), 'text', array( 'default' => 'CONTACTO' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'contacto_titulo', __( 'Titular', 'acg-visual' ), 'text', array( 'default' => 'Cuéntame tu fecha' ) ) );
	acg_push_fields( $portada, acg_def_pair( 'contacto_texto', __( 'Texto de apoyo', 'acg-visual' ), 'textarea', array( 'rows' => 3 ) ) );

	$grupos['portada'] = array(
		'title'    => __( 'Contenido de la portada', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ) ),
		'fields'   => $portada,
		'screens'  => array( 'page' ),
	);

	// -------------------------------------------------------------- Portafolio.
	// El título y el contenido en español son los nativos de WordPress; aquí
	// solo hace falta declarar sus gemelos en portugués.
	$trabajo   = array(
		acg_def_field( 'titulo_pt', __( 'Título (PT)', 'acg-visual' ), 'text', array( 'ancho' => 50 ) ),
		acg_def_field( 'texto_pt', __( 'Descripción (PT)', 'acg-visual' ), 'wysiwyg' ),
	);
	$trabajo[] = acg_def_field(
		'formato',
		__( 'Formato en el mosaico', 'acg-visual' ),
		'select',
		array(
			'choices'      => array(
				'vertical' => __( 'Vertical — ocupa una columna', 'acg-visual' ),
				'apaisada' => __( 'Apaisada — ocupa dos columnas', 'acg-visual' ),
			),
			'default'      => 'vertical',
			'ancho'        => 50,
			'instructions' => __( 'Alterna verticales y apaisadas para que el mosaico respire.', 'acg-visual' ),
		)
	);
	acg_push_fields( $trabajo, acg_def_pair( 'etiqueta', __( 'Etiqueta sobre la foto', 'acg-visual' ), 'text', array( 'instructions' => __( 'Ejemplo: BODA · 01', 'acg-visual' ) ) ) );
	$trabajo[] = acg_def_field( 'cliente', __( 'Cliente', 'acg-visual' ), 'text', array( 'ancho' => 33 ) );
	$trabajo[] = acg_def_field( 'lugar', __( 'Lugar', 'acg-visual' ), 'text', array( 'ancho' => 33 ) );
	$trabajo[] = acg_def_field( 'anio', __( 'Año', 'acg-visual' ), 'text', array( 'ancho' => 34 ) );

	$grupos['trabajo'] = array(
		'title'    => __( 'Datos del trabajo', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_trabajo' ) ) ),
		'fields'   => $trabajo,
		'screens'  => array( 'acg_trabajo' ),
	);

	// --------------------------------------------------------------- Servicios.
	$servicio = array(
		acg_def_field( 'titulo_pt', __( 'Título (PT)', 'acg-visual' ), 'text' ),
		acg_def_field( 'texto_pt', __( 'Descripción (PT)', 'acg-visual' ), 'wysiwyg' ),
	);
	acg_push_fields( $servicio, acg_def_pair( 'nota', __( 'Nota al pie de la tarjeta', 'acg-visual' ), 'text', array( 'instructions' => __( 'Ejemplo: PRESUPUESTO A MEDIDA, POR HORAS O JORNADA…', 'acg-visual' ) ) ) );
	$servicio[] = acg_def_field(
		'destacado',
		__( 'Mostrar como tarjeta grande en la portada', 'acg-visual' ),
		'true_false',
		array(
			'default'      => '1',
			'instructions' => __( 'Los servicios sin marcar siguen teniendo su propia página, pero no salen en la rejilla de la portada.', 'acg-visual' ),
		)
	);

	$grupos['servicio'] = array(
		'title'    => __( 'Datos del servicio', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_servicio' ) ) ),
		'fields'   => $servicio,
		'screens'  => array( 'acg_servicio' ),
	);

	// ----------------------------------------------------------------- Proceso.
	$grupos['proceso'] = array(
		'title'    => __( 'Paso del proceso', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_proceso' ) ) ),
		'fields'   => array(
			acg_def_field( 'titulo_pt', __( 'Título (PT)', 'acg-visual' ), 'text' ),
			acg_def_field( 'texto_pt', __( 'Texto (PT)', 'acg-visual' ), 'textarea', array( 'rows' => 3 ) ),
			acg_def_field( 'numero', __( 'Número que se muestra', 'acg-visual' ), 'text', array( 'instructions' => __( 'Si lo dejas vacío se numera solo según el orden.', 'acg-visual' ), 'ancho' => 50 ) ),
		),
		'screens'  => array( 'acg_proceso' ),
	);

	// ------------------------------------------------------------- Testimonios.
	$testimonio = array(
		acg_def_field(
			'fuente',
			__( 'Origen', 'acg-visual' ),
			'select',
			array(
				'choices'      => array(
					'manual' => __( 'Escrito a mano', 'acg-visual' ),
					'google' => __( 'Reseña de Google Business Profile', 'acg-visual' ),
				),
				'default'      => 'manual',
				'ancho'        => 50,
				'instructions' => __( 'Para copiar una reseña de tu perfil de Google Business: entra en Google Business Profile → Reseñas, copia el texto y pégalo abajo, y en «Enlace de la reseña» pon el enlace público de tu ficha (el botón «Compartir perfil»). No hay sincronización automática: se actualiza a mano cada vez que quieras traer una reseña nueva.', 'acg-visual' ),
			)
		),
		acg_def_field(
			'valoracion',
			__( 'Valoración', 'acg-visual' ),
			'select',
			array(
				'choices' => array(
					'5' => '★★★★★ (5)',
					'4' => '★★★★☆ (4)',
					'3' => '★★★☆☆ (3)',
					'2' => '★★☆☆☆ (2)',
					'1' => '★☆☆☆☆ (1)',
				),
				'default' => '5',
				'ancho'   => 50,
			)
		),
		acg_def_field( 'texto_pt', __( 'Testimonio (PT)', 'acg-visual' ), 'textarea', array( 'rows' => 4 ) ),
		acg_def_field( 'autor', __( 'Quién lo dice', 'acg-visual' ), 'text', array( 'ancho' => 50, 'instructions' => __( 'Ejemplo: MARIANA & TIAGO', 'acg-visual' ) ) ),
	);
	acg_push_fields( $testimonio, acg_def_pair( 'tipo', __( 'Tipo de trabajo', 'acg-visual' ), 'text', array( 'instructions' => __( 'Ejemplo: BODA', 'acg-visual' ) ) ) );
	$testimonio[] = acg_def_field(
		'url',
		__( 'Enlace de la reseña', 'acg-visual' ),
		'url',
		array( 'instructions' => __( 'Opcional. Si lo rellenas, el testimonio enlaza a la reseña original.', 'acg-visual' ) )
	);

	$grupos['testimonio'] = array(
		'title'    => __( 'Datos del testimonio', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_testimonio' ) ) ),
		'fields'   => $testimonio,
		'screens'  => array( 'acg_testimonio' ),
	);

	// ------------------------------------------------------------------ Equipo.
	$grupos['equipo'] = array(
		'title'    => __( 'Pieza de equipo', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_equipo' ) ) ),
		'fields'   => array(
			acg_def_field(
				'grupo',
				__( 'Columna', 'acg-visual' ),
				'select',
				array(
					'choices' => array(
						'cuerpo' => __( 'Cámara y flash', 'acg-visual' ),
						'optica' => __( 'Óptica', 'acg-visual' ),
					),
					'default' => 'cuerpo',
					'ancho'   => 50,
				)
			),
			acg_def_field( 'titulo_pt', __( 'Nombre (PT)', 'acg-visual' ), 'text', array( 'ancho' => 50 ) ),
		),
		'screens'  => array( 'acg_equipo' ),
	);

	// --------------------------------------------------------------------- FAQ.
	$grupos['faq'] = array(
		'title'    => __( 'Pregunta frecuente', 'acg-visual' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'acg_faq' ) ) ),
		'fields'   => array(
			acg_def_field( 'titulo_pt', __( 'Pregunta (PT)', 'acg-visual' ), 'text' ),
			acg_def_field( 'texto_pt', __( 'Respuesta (PT)', 'acg-visual' ), 'textarea', array( 'rows' => 4 ) ),
		),
		'screens'  => array( 'acg_faq' ),
	);

	// ------------------------------------------------------- Páginas y entradas.
	$pagina = array(
		acg_def_field( 'titulo_pt', __( 'Título (PT)', 'acg-visual' ), 'text' ),
		acg_def_field( 'texto_pt', __( 'Contenido (PT)', 'acg-visual' ), 'wysiwyg' ),
	);
	acg_push_fields( $pagina, acg_def_pair( 'pagina_epigrafe', __( 'Epígrafe sobre el título', 'acg-visual' ), 'text' ) );
	$pagina[] = acg_def_field( 'pagina_imagen', __( 'Imagen de cabecera', 'acg-visual' ), 'image', array( 'instructions' => __( 'Opcional. Si la dejas vacía, la cabecera va sobre el fondo liso.', 'acg-visual' ) ) );
	$pagina[] = acg_def_field( 'pagina_formulario', __( 'Añadir el formulario de contacto al final', 'acg-visual' ), 'true_false', array( 'default' => '0' ) );

	$grupos['pagina'] = array(
		'title'    => __( 'Opciones de la página', 'acg-visual' ),
		'location' => array(
			array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
			array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ),
		),
		'fields'   => $pagina,
		'screens'  => array( 'page', 'post' ),
	);

	/**
	 * Permite añadir grupos de campos desde un child theme o un plugin.
	 *
	 * @param array $grupos Catálogo completo.
	 */
	return apply_filters( 'acg_field_groups', $grupos );
}

/**
 * Traduce un campo del catálogo al formato de ACF.
 *
 * @param array  $campo    Campo del catálogo.
 * @param string $grupo_id Identificador del grupo, para componer la clave.
 * @return array
 */
function acg_field_to_acf( $campo, $grupo_id ) {
	$acf = array(
		'key'           => 'field_acg_' . $grupo_id . '_' . $campo['name'],
		'name'          => $campo['name'],
		'label'         => $campo['label'],
		'instructions'  => $campo['instructions'],
		'wrapper'       => array( 'width' => $campo['ancho'] ),
		'default_value' => $campo['default'],
	);

	switch ( $campo['type'] ) {
		case 'textarea':
			$acf['type'] = 'textarea';
			$acf['rows'] = $campo['rows'];
			break;

		case 'wysiwyg':
			$acf['type']         = 'wysiwyg';
			$acf['media_upload'] = 1;
			$acf['toolbar']      = 'basic';
			break;

		case 'image':
			$acf['type']          = 'image';
			$acf['return_format'] = 'id';
			$acf['preview_size']  = 'medium';
			$acf['library']       = 'all';
			unset( $acf['default_value'] );
			break;

		case 'select':
			$acf['type']         = 'select';
			$acf['choices']      = $campo['choices'];
			$acf['allow_null']   = 0;
			$acf['return_format'] = 'value';
			break;

		case 'true_false':
			$acf['type']          = 'true_false';
			$acf['ui']            = 1;
			$acf['default_value'] = (int) $campo['default'];
			break;

		case 'number':
			$acf['type'] = 'number';
			break;

		case 'url':
			$acf['type'] = 'url';
			break;

		case 'separator':
			$acf['type'] = 'message';
			$acf['message'] = '<strong style="font-size:14px">' . esc_html( $campo['label'] ) . '</strong>';
			$acf['label']   = '';
			unset( $acf['default_value'] );
			break;

		case 'tab':
			// Un campo 'tab' de ACF no dibuja nada por sí mismo: convierte en
			// pestaña todo lo que va después, hasta el siguiente 'tab'. Con
			// varios seguidos, ACF los agrupa solo en una fila de pestañas.
			$acf['type'] = 'tab';
			unset( $acf['default_value'] );
			break;

		default:
			$acf['type'] = 'text';
			break;
	}

	return $acf;
}

/**
 * Registra los grupos en ACF.
 *
 * Se engancha a `acf/init` y no a `init` porque las funciones locales de ACF
 * no existen hasta que el plugin ha arrancado.
 *
 * @return void
 */
function acg_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$orden = 0;

	foreach ( acg_field_groups() as $grupo_id => $grupo ) {
		$campos = array();

		foreach ( $grupo['fields'] as $campo ) {
			$campos[] = acg_field_to_acf( $campo, $grupo_id );
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_acg_' . $grupo_id,
				'title'                 => $grupo['title'],
				'fields'                => $campos,
				'location'              => $grupo['location'],
				'menu_order'            => $orden++,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'hide_on_screen'        => array(),
				'active'                => true,
				'show_in_rest'          => false,
				'description'           => '',
			)
		);
	}
}
add_action( 'acf/init', 'acg_register_acf_fields' );
