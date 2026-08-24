<?php
/**
 * Bilingüe español / portugués sin plugins de traducción.
 *
 * Cómo funciona, y por qué así:
 *
 * - El idioma activo se decide **en el servidor** (`?lang=pt`, cookie, o el
 *   idioma por defecto del Personalizador) y la página se pinta ya en ese
 *   idioma. La alternativa —duplicar el HTML y ocultar uno de los dos con
 *   JavaScript, como hace la maqueta— manda al visitante el doble de texto y
 *   deja a Google leyendo las dos versiones mezcladas en la misma URL.
 * - Cada campo editable tiene su gemelo en portugués (`..._pt`). Si está
 *   vacío se cae al español, así el sitio nunca aparece con huecos mientras
 *   Angie va traduciendo.
 * - Las micro-cadenas de la interfaz (botones, etiquetas de formulario) no se
 *   editan: viven en el diccionario de `acg_strings()`.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Idiomas que soporta el sitio.
 *
 * @return array<string,string>
 */
function acg_languages() {
	return array(
		'es' => __( 'Español', 'acg-visual' ),
		'pt' => __( 'Português', 'acg-visual' ),
	);
}

/**
 * Idioma activo en esta petición.
 *
 * Se resuelve una sola vez y se cachea en estático: se consulta decenas de
 * veces por página.
 *
 * @return string 'es' o 'pt'.
 */
function acg_lang() {
	static $lang = null;

	if ( null !== $lang ) {
		return $lang;
	}

	$defecto = get_theme_mod( 'acg_idioma_defecto', 'es' );
	$lang    = isset( acg_languages()[ $defecto ] ) ? $defecto : 'es';

	$idiomas = acg_languages();

	// La cookie manda sobre el idioma por defecto, y la URL sobre la cookie:
	// así un enlace compartido con ?lang=pt llega en portugués aunque quien
	// lo abra tuviera guardado el español.
	if ( isset( $_COOKIE['acg_lang'] ) ) {
		$guardado = sanitize_key( wp_unslash( $_COOKIE['acg_lang'] ) );

		if ( isset( $idiomas[ $guardado ] ) ) {
			$lang = $guardado;
		}
	}

	if ( isset( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$pedido = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( isset( $idiomas[ $pedido ] ) ) {
			$lang = $pedido;
		}
	}

	return $lang;
}

/**
 * ¿Estamos pintando la versión portuguesa?
 *
 * @return bool
 */
function acg_is_pt() {
	return 'pt' === acg_lang();
}

/**
 * Recuerda el idioma elegido durante un año.
 *
 * Se hace en `template_redirect` (antes de imprimir nada) y solo cuando el
 * visitante lo ha pedido explícitamente con `?lang=`, para no plantar una
 * cookie a quien solo pasa por el sitio.
 *
 * @return void
 */
function acg_remember_lang() {
	if ( ! isset( $_GET['lang'] ) || headers_sent() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$pedido = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! isset( acg_languages()[ $pedido ] ) ) {
		return;
	}

	setcookie( 'acg_lang', $pedido, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
}
add_action( 'template_redirect', 'acg_remember_lang' );

/**
 * URL actual con otro idioma.
 *
 * @param string $lang Código de idioma.
 * @return string
 */
function acg_lang_url( $lang ) {
	$actual = home_url( add_query_arg( array() ) );

	return esc_url( add_query_arg( 'lang', $lang, $actual ) );
}

/**
 * Devuelve la cadena del idioma activo.
 *
 * @param string $es Texto en español.
 * @param string $pt Texto en portugués (si viene vacío, se usa el español).
 * @return string
 */
function acg_t( $es, $pt = '' ) {
	if ( acg_is_pt() && '' !== trim( (string) $pt ) ) {
		return $pt;
	}

	return $es;
}

/**
 * Lee un campo de un post en el idioma activo.
 *
 * Busca `{$campo}_pt` cuando el idioma es portugués y cae a `{$campo}` si
 * está vacío. Funciona con ACF si está activo y con post meta nativa si no,
 * de modo que el theme no depende del plugin para renderizar.
 *
 * @param string   $campo   Nombre base del campo (sin sufijo de idioma).
 * @param int|null $post_id ID del post; por defecto el actual.
 * @param string   $defecto Valor si no hay nada guardado.
 * @return string
 */
function acg_field( $campo, $post_id = null, $defecto = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( ! $post_id ) {
		return $defecto;
	}

	$valor = '';

	if ( acg_is_pt() ) {
		$valor = acg_raw_field( $campo . '_pt', $post_id );
	}

	if ( '' === trim( (string) $valor ) ) {
		$valor = acg_raw_field( $campo, $post_id );
	}

	return '' !== trim( (string) $valor ) ? $valor : $defecto;
}

/**
 * Lectura cruda de un campo, con ACF si está disponible.
 *
 * @param string $campo   Nombre del campo.
 * @param int    $post_id ID del post.
 * @return mixed
 */
function acg_raw_field( $campo, $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		$valor = get_field( $campo, $post_id );

		if ( null !== $valor && '' !== $valor && false !== $valor ) {
			return $valor;
		}
	}

	return get_post_meta( $post_id, $campo, true );
}

/**
 * Título de un post en el idioma activo.
 *
 * @param int|null $post_id ID del post.
 * @return string
 */
function acg_title( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$pt      = acg_is_pt() ? acg_raw_field( 'titulo_pt', $post_id ) : '';

	return '' !== trim( (string) $pt ) ? $pt : get_the_title( $post_id );
}

/**
 * Contenido de un post en el idioma activo, ya formateado.
 *
 * @param int|null $post_id ID del post.
 * @return string HTML.
 */
function acg_content( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$pt      = acg_is_pt() ? acg_raw_field( 'texto_pt', $post_id ) : '';

	if ( '' !== trim( (string) $pt ) ) {
		return wpautop( wp_kses_post( $pt ) );
	}

	$post = get_post( $post_id );

	return $post ? apply_filters( 'the_content', $post->post_content ) : '';
}

/**
 * Contenido en texto plano y recortado, para tarjetas y listados.
 *
 * @param int|null $post_id  ID del post.
 * @param int      $palabras Número máximo de palabras.
 * @return string
 */
function acg_excerpt( $post_id = null, $palabras = 28 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$pt      = acg_is_pt() ? acg_raw_field( 'texto_pt', $post_id ) : '';
	$texto   = '' !== trim( (string) $pt ) ? $pt : get_post_field( 'post_content', $post_id );

	return wp_trim_words( wp_strip_all_tags( $texto ), $palabras, '…' );
}

/**
 * Diccionario de micro-cadenas de la interfaz.
 *
 * No son editables desde el panel a propósito: son etiquetas de sistema
 * («Enviar», «Leer más»), no contenido. Lo que sí es contenido —titulares,
 * descripciones de sección— se edita con ACF en la página correspondiente.
 *
 * @return array<string,array{es:string,pt:string}>
 */
function acg_strings() {
	$cadenas = array(
		'nav_portafolio'   => array( 'es' => 'Portafolio', 'pt' => 'Portfólio' ),
		'nav_servicios'    => array( 'es' => 'Servicios', 'pt' => 'Serviços' ),
		'nav_sobre'        => array( 'es' => 'Sobre mí', 'pt' => 'Sobre mim' ),
		'nav_proceso'      => array( 'es' => 'Proceso', 'pt' => 'Processo' ),
		'nav_contacto'     => array( 'es' => 'Contacto', 'pt' => 'Contato' ),
		'ver_portafolio'   => array( 'es' => 'Ver portafolio', 'pt' => 'Ver portfólio' ),
		'pedir_presupuesto' => array( 'es' => 'Pedir presupuesto', 'pt' => 'Pedir orçamento' ),
		'todo'             => array( 'es' => 'Todo', 'pt' => 'Tudo' ),
		'mas_instagram'    => array( 'es' => 'Más en Instagram', 'pt' => 'Mais no Instagram' ),
		'leer_mas'         => array( 'es' => 'Ver el trabajo', 'pt' => 'Ver o trabalho' ),
		'volver'           => array( 'es' => 'Volver', 'pt' => 'Voltar' ),
		'form_nombre'      => array( 'es' => 'Nombre', 'pt' => 'Nome' ),
		'form_contacto'    => array( 'es' => 'Email o teléfono', 'pt' => 'E-mail ou telefone' ),
		'form_email'       => array( 'es' => 'Email', 'pt' => 'E-mail' ),
		'form_telefono'    => array( 'es' => 'Teléfono', 'pt' => 'Telefone' ),
		'form_servicio'    => array( 'es' => 'Servicio', 'pt' => 'Serviço' ),
		'form_fecha'       => array( 'es' => 'Fecha', 'pt' => 'Data' ),
		'form_mensaje'     => array( 'es' => 'Mensaje', 'pt' => 'Mensagem' ),
		'form_enviar'      => array( 'es' => 'Enviar solicitud', 'pt' => 'Enviar pedido' ),
		'form_enviando'    => array( 'es' => 'Enviando…', 'pt' => 'Enviando…' ),
		'form_privacidad'  => array( 'es' => 'He leído y acepto la política de privacidad.', 'pt' => 'Li e aceito a política de privacidade.' ),
		'form_otro'        => array( 'es' => 'Otro', 'pt' => 'Outro' ),
		'form_elige'       => array( 'es' => 'Elige una opción', 'pt' => 'Escolha uma opção' ),
		'form_ok'          => array( 'es' => '¡Gracias! Te respondo en menos de 24 h.', 'pt' => 'Obrigada! Respondo em menos de 24 h.' ),
		'form_error'       => array( 'es' => 'No se ha podido enviar. Escríbeme por WhatsApp, por favor.', 'pt' => 'Não foi possível enviar. Me escreva pelo WhatsApp, por favor.' ),
		'form_wa_abierto'  => array( 'es' => 'Se ha abierto WhatsApp con tu mensaje. Si no se abrió, escríbeme directamente.', 'pt' => 'O WhatsApp abriu com sua mensagem. Se não abriu, me escreva diretamente.' ),
		'form_wa_sin_numero' => array( 'es' => 'No hay un número de WhatsApp configurado todavía.', 'pt' => 'Ainda não há um número de WhatsApp configurado.' ),
		'o_whatsapp'       => array( 'es' => 'o escríbeme por WhatsApp', 'pt' => 'ou me escreva pelo WhatsApp' ),
		'menu'             => array( 'es' => 'Menú', 'pt' => 'Menu' ),
		'abrir_menu'       => array( 'es' => 'Abrir el menú', 'pt' => 'Abrir o menu' ),
		'cerrar_menu'      => array( 'es' => 'Cerrar el menú', 'pt' => 'Fechar o menu' ),
		'buscar'           => array( 'es' => 'Buscar', 'pt' => 'Buscar' ),
		'idioma'           => array( 'es' => 'Idioma', 'pt' => 'Idioma' ),
		'sin_resultados'   => array( 'es' => 'No hay nada por aquí todavía.', 'pt' => 'Ainda não há nada por aqui.' ),
		'error_404'        => array( 'es' => 'Esta página no existe', 'pt' => 'Esta página não existe' ),
		'error_404_texto'  => array( 'es' => 'El enlace puede estar mal escrito o la página se movió. Vuelve al inicio o mira el portafolio.', 'pt' => 'O link pode estar errado ou a página foi movida. Volte ao início ou veja o portfólio.' ),
		'inicio'           => array( 'es' => 'Inicio', 'pt' => 'Início' ),
		'relacionados'     => array( 'es' => 'Más trabajos', 'pt' => 'Mais trabalhos' ),
		'categoria'        => array( 'es' => 'Categoría', 'pt' => 'Categoria' ),
		'resultados_para'  => array( 'es' => 'Resultados para', 'pt' => 'Resultados para' ),
	);

	/**
	 * Permite añadir o cambiar micro-cadenas desde un child theme.
	 *
	 * @param array $cadenas Diccionario completo.
	 */
	return apply_filters( 'acg_strings', $cadenas );
}

/**
 * Micro-cadena de interfaz en el idioma activo.
 *
 * @param string $clave Clave del diccionario.
 * @return string
 */
function acg_s( $clave ) {
	$cadenas = acg_strings();

	if ( ! isset( $cadenas[ $clave ] ) ) {
		return '';
	}

	return acg_t( $cadenas[ $clave ]['es'], $cadenas[ $clave ]['pt'] );
}

/**
 * Atributo lang del documento, coherente con el idioma activo.
 *
 * @param string $salida Atributos calculados por WordPress.
 * @return string
 */
function acg_language_attributes( $salida ) {
	$lang = acg_is_pt() ? 'pt-BR' : 'es';

	return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $salida );
}
add_filter( 'language_attributes', 'acg_language_attributes' );

/**
 * Enlaces hreflang: le dicen a Google que la misma URL tiene dos versiones
 * y cuál es cuál. Sin esto, servir dos idiomas en la misma URL se lee como
 * contenido inconsistente.
 *
 * @return void
 */
function acg_hreflang_tags() {
	if ( is_404() ) {
		return;
	}

	$base = home_url( add_query_arg( array() ) );

	printf( '<link rel="alternate" hreflang="es" href="%s" />' . "\n", esc_url( add_query_arg( 'lang', 'es', $base ) ) );
	printf( '<link rel="alternate" hreflang="pt" href="%s" />' . "\n", esc_url( add_query_arg( 'lang', 'pt', $base ) ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( remove_query_arg( 'lang', $base ) ) );
}
add_action( 'wp_head', 'acg_hreflang_tags' );

/**
 * Conmutador de idioma. Se usa como elemento del diseñador de cabecera y pie.
 *
 * @return void
 */
function acg_language_switcher() {
	$actual = acg_lang();
	?>
	<div class="acg-lang" role="group" aria-label="<?php echo esc_attr( acg_s( 'idioma' ) ); ?>">
		<?php foreach ( acg_languages() as $codigo => $nombre ) : ?>
			<a
				class="acg-lang__btn<?php echo $codigo === $actual ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( acg_lang_url( $codigo ) ); ?>"
				lang="<?php echo esc_attr( $codigo ); ?>"
				hreflang="<?php echo esc_attr( $codigo ); ?>"
				rel="alternate"
				<?php echo $codigo === $actual ? ' aria-current="true"' : ''; ?>
			><?php echo esc_html( strtoupper( $codigo ) ); ?><span class="screen-reader-text"> — <?php echo esc_html( $nombre ); ?></span></a>
		<?php endforeach; ?>
	</div>
	<?php
}
