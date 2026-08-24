<?php
/**
 * Importador de contenido demo.
 *
 * Deja el sitio igual que la maqueta: medios, portafolio, servicios, proceso,
 * testimonios, equipo, preguntas frecuentes, la portada con todos sus textos
 * (en español y en portugués), los menús y los ajustes del Personalizador.
 *
 * Todo lo que crea queda marcado con `_acg_demo`, así que se puede revertir
 * sin tocar el contenido real del cliente. Reimportar actualiza lo ya
 * importado en lugar de duplicarlo.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * Importador de la demo.
 */
class ACG_Demo_Importer {

	/**
	 * Slug de la página del importador.
	 */
	const PAGE = 'acg-demo';

	/**
	 * Tipos de contenido que crea la demo, por su clave en el JSON.
	 *
	 * @return array<string,string>
	 */
	public static function tipos() {
		return array(
			'portafolio'  => 'acg_trabajo',
			'servicios'   => 'acg_servicio',
			'proceso'     => 'acg_proceso',
			'testimonios' => 'acg_testimonio',
			'equipo'      => 'acg_equipo',
			'faq'         => 'acg_faq',
		);
	}

	/**
	 * Engancha la interfaz.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		add_action( 'admin_post_acg_import_demo', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_acg_reset_demo', array( __CLASS__, 'handle_reset' ) );
		add_action( 'after_switch_theme', array( __CLASS__, 'on_activate' ) );
	}

	/**
	 * Al activar el theme, invita a importar la demo.
	 *
	 * @return void
	 */
	public static function on_activate() {
		set_transient( 'acg_mostrar_demo', 1, MINUTE_IN_SECONDS );
	}

	/**
	 * Registra la página bajo Apariencia.
	 *
	 * @return void
	 */
	public static function register_page() {
		add_theme_page(
			__( 'Contenido demo', 'acg-visual' ),
			__( 'Contenido demo', 'acg-visual' ),
			'import',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Lee y decodifica el JSON de la demo.
	 *
	 * @return array|null
	 */
	private static function load_content() {
		$archivo = ACG_DIR . '/demo/content.json';

		if ( ! file_exists( $archivo ) ) {
			return null;
		}

		$bruto = file_get_contents( $archivo ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$datos = json_decode( $bruto, true );

		return is_array( $datos ) ? $datos : null;
	}

	/**
	 * Pinta la pantalla del importador.
	 *
	 * @return void
	 */
	public static function render_page() {
		$datos     = self::load_content();
		$importado = (bool) get_option( 'acg_demo_importada' );
		$mensaje   = isset( $_GET['acg_msg'] ) ? sanitize_key( wp_unslash( $_GET['acg_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Contenido demo de ACG Artista Visual', 'acg-visual' ); ?></h1>

			<?php if ( 'ok' === $mensaje ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Contenido demo importado. Ya puedes ver la portada.', 'acg-visual' ); ?></p></div>
			<?php elseif ( 'reset' === $mensaje ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Contenido demo eliminado.', 'acg-visual' ); ?></p></div>
			<?php elseif ( 'nofile' === $mensaje ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'No encuentro demo/content.json dentro del theme.', 'acg-visual' ); ?></p></div>
			<?php endif; ?>

			<?php if ( ! $datos ) : ?>
				<p><?php esc_html_e( 'No hay archivo de contenido demo disponible.', 'acg-visual' ); ?></p>
				<?php return; ?>
			<?php endif; ?>

			<div class="card" style="max-width:760px">
				<h2><?php esc_html_e( 'Qué se va a importar', 'acg-visual' ); ?></h2>
				<ul style="list-style:disc;padding-left:20px">
					<?php foreach ( self::tipos() as $clave => $tipo ) : ?>
						<?php
						$objeto = get_post_type_object( $tipo );
						$total  = isset( $datos[ $clave ] ) ? count( $datos[ $clave ] ) : 0;

						if ( ! $total || ! $objeto ) {
							continue;
						}
						?>
						<li>
							<?php
							printf(
								/* translators: 1: número de elementos, 2: nombre del tipo de contenido. */
								esc_html__( '%1$d en «%2$s»', 'acg-visual' ),
								absint( $total ),
								esc_html( $objeto->labels->name )
							);
							?>
						</li>
					<?php endforeach; ?>
					<li><?php esc_html_e( 'La portada completa, con todos los textos en español y portugués', 'acg-visual' ); ?></li>
					<li><?php esc_html_e( 'Los menús principal y de pie, y las categorías del portafolio', 'acg-visual' ); ?></li>
					<li><?php esc_html_e( 'Colores, datos de contacto, redes y demás ajustes del Personalizador', 'acg-visual' ); ?></li>
				</ul>

				<p class="description">
					<?php esc_html_e( 'La importación no borra nada de lo que ya tengas: crea contenido nuevo y lo marca como demo. Si vuelves a lanzarla, actualiza lo ya importado en lugar de duplicarlo.', 'acg-visual' ); ?>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="acg_import_demo">
					<?php wp_nonce_field( 'acg_import_demo' ); ?>

					<p>
						<label><input type="checkbox" name="importar_medios" value="1" checked> <?php esc_html_e( 'Importar las imágenes de ejemplo a la biblioteca de medios', 'acg-visual' ); ?></label><br>
						<label><input type="checkbox" name="importar_ajustes" value="1" checked> <?php esc_html_e( 'Aplicar los ajustes del Personalizador', 'acg-visual' ); ?></label><br>
						<label><input type="checkbox" name="importar_menus" value="1" checked> <?php esc_html_e( 'Crear los menús', 'acg-visual' ); ?></label>
					</p>

					<p>
						<button type="submit" class="button button-primary button-hero">
							<?php echo $importado ? esc_html__( 'Volver a importar', 'acg-visual' ) : esc_html__( 'Importar contenido demo', 'acg-visual' ); ?>
						</button>
					</p>
				</form>
			</div>

			<?php if ( $importado ) : ?>
				<div class="card" style="max-width:760px;margin-top:20px">
					<h2><?php esc_html_e( 'Empezar de cero', 'acg-visual' ); ?></h2>
					<p><?php esc_html_e( 'Envía a la papelera todo el contenido marcado como demo y borra las imágenes importadas. No toca nada que hayas creado tú.', 'acg-visual' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php esc_attr_e( '¿Seguro que quieres eliminar todo el contenido demo?', 'acg-visual' ); ?>');">
						<input type="hidden" name="action" value="acg_reset_demo">
						<?php wp_nonce_field( 'acg_reset_demo' ); ?>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Eliminar contenido demo', 'acg-visual' ); ?></button>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Ejecuta la importación.
	 *
	 * @return void
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'import' ) ) {
			wp_die( esc_html__( 'No tienes permiso para importar contenido.', 'acg-visual' ) );
		}

		check_admin_referer( 'acg_import_demo' );

		$datos = self::load_content();

		if ( ! $datos ) {
			wp_safe_redirect( admin_url( 'themes.php?page=' . self::PAGE . '&acg_msg=nofile' ) );
			exit;
		}

		// Subir una docena de imágenes y generar sus recortes puede pasarse
		// del límite por defecto en hostings lentos.
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}

		$con_medios  = ! empty( $_POST['importar_medios'] );
		$con_ajustes = ! empty( $_POST['importar_ajustes'] );
		$con_menus   = ! empty( $_POST['importar_menus'] );

		$medios = $con_medios ? self::import_media( $datos ) : (array) get_option( 'acg_demo_medios', array() );

		self::import_terms( $datos );

		foreach ( self::tipos() as $clave => $tipo ) {
			if ( ! empty( $datos[ $clave ] ) ) {
				self::import_posts( $datos[ $clave ], $tipo, $medios );
			}
		}

		self::import_pages( $datos, $medios );

		if ( $con_ajustes && ! empty( $datos['opciones'] ) ) {
			self::import_options( $datos['opciones'], $medios );
		}

		if ( $con_menus ) {
			self::build_menus();
		}

		update_option( 'acg_demo_importada', time() );
		flush_rewrite_rules();

		wp_safe_redirect( admin_url( 'themes.php?page=' . self::PAGE . '&acg_msg=ok' ) );
		exit;
	}

	/**
	 * Sube a la biblioteca de medios los archivos de demo/images.
	 *
	 * @param array $datos Contenido demo.
	 * @return array<string,int> Mapa nombre de archivo => ID de adjunto.
	 */
	private static function import_media( $datos ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		/*
		 * Las fotos de ejemplo son SVG que viajan dentro del theme, así que se
		 * permiten explícitamente mientras dura la importación. Es un permiso
		 * acotado —se retira al terminar el bucle— y no cambia lo que el sitio
		 * acepta subir desde la biblioteca de medios.
		 */
		$permitir_svg = static function ( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		};

		$tipo_svg = static function ( $comprobado, $archivo, $nombre ) {
			if ( preg_match( '/\.svg$/i', $nombre ) ) {
				$comprobado['ext']             = 'svg';
				$comprobado['type']            = 'image/svg+xml';
				$comprobado['proper_filename'] = $nombre;
			}
			return $comprobado;
		};

		add_filter( 'upload_mimes', $permitir_svg, 99 );
		add_filter( 'wp_check_filetype_and_ext', $tipo_svg, 99, 3 );

		$mapa   = (array) get_option( 'acg_demo_medios', array() );
		$origen = ACG_DIR . '/demo/images/';

		foreach ( (array) ( isset( $datos['medios'] ) ? $datos['medios'] : array() ) as $medio ) {
			$nombre = is_array( $medio ) ? ( isset( $medio['archivo'] ) ? $medio['archivo'] : '' ) : $medio;
			$titulo = is_array( $medio ) && isset( $medio['titulo'] ) ? $medio['titulo'] : $nombre;
			$alt    = is_array( $medio ) && isset( $medio['alt'] ) ? $medio['alt'] : $titulo;

			if ( ! $nombre ) {
				continue;
			}

			if ( isset( $mapa[ $nombre ] ) && get_post( $mapa[ $nombre ] ) ) {
				continue;
			}

			$ruta = $origen . basename( $nombre );

			if ( ! file_exists( $ruta ) ) {
				continue;
			}

			$tmp = wp_tempnam( basename( $nombre ) );

			if ( ! $tmp || ! copy( $ruta, $tmp ) ) {
				continue;
			}

			$adjunto = media_handle_sideload(
				array(
					'name'     => basename( $nombre ),
					'tmp_name' => $tmp,
				),
				0,
				$titulo
			);

			if ( is_wp_error( $adjunto ) ) {
				if ( file_exists( $tmp ) ) {
					wp_delete_file( $tmp );
				}
				continue;
			}

			update_post_meta( $adjunto, '_wp_attachment_image_alt', $alt );
			update_post_meta( $adjunto, '_acg_demo', 1 );

			$mapa[ $nombre ] = (int) $adjunto;
		}

		remove_filter( 'upload_mimes', $permitir_svg, 99 );
		remove_filter( 'wp_check_filetype_and_ext', $tipo_svg, 99 );

		update_option( 'acg_demo_medios', $mapa );

		return $mapa;
	}

	/**
	 * Crea las categorías del portafolio.
	 *
	 * @param array $datos Contenido demo.
	 * @return void
	 */
	private static function import_terms( $datos ) {
		if ( empty( $datos['taxonomias'] ) ) {
			return;
		}

		foreach ( $datos['taxonomias'] as $taxonomia => $terminos ) {
			if ( ! taxonomy_exists( $taxonomia ) ) {
				continue;
			}

			foreach ( $terminos as $termino ) {
				$slug = isset( $termino['slug'] ) ? $termino['slug'] : sanitize_title( $termino['nombre'] );
				$id   = term_exists( $slug, $taxonomia );

				if ( ! $id ) {
					$id = wp_insert_term( $termino['nombre'], $taxonomia, array( 'slug' => $slug ) );
				}

				// El nombre en portugués del término se guarda como meta: la
				// taxonomía nativa no tiene dónde ponerlo.
				if ( ! is_wp_error( $id ) && ! empty( $termino['nombre_pt'] ) ) {
					update_term_meta( (int) $id['term_id'], 'nombre_pt', $termino['nombre_pt'] );
				}
			}
		}
	}

	/**
	 * Crea o actualiza un conjunto de posts de la demo.
	 *
	 * @param array  $items  Definiciones.
	 * @param string $tipo   Tipo de post.
	 * @param array  $medios Mapa de medios.
	 * @return array<string,int> Mapa slug => ID.
	 */
	private static function import_posts( $items, $tipo, $medios ) {
		$mapa = array();

		foreach ( $items as $indice => $item ) {
			$slug      = isset( $item['slug'] ) ? $item['slug'] : sanitize_title( $item['titulo'] );
			$existente = self::find_demo_post( $tipo, $slug );

			$args = array(
				'post_type'    => $tipo,
				'post_title'   => $item['titulo'],
				'post_name'    => $slug,
				'post_content' => isset( $item['contenido'] ) ? $item['contenido'] : '',
				'post_excerpt' => isset( $item['extracto'] ) ? $item['extracto'] : '',
				'post_status'  => 'publish',
				'menu_order'   => isset( $item['orden'] ) ? (int) $item['orden'] : $indice,
			);

			if ( $existente ) {
				$args['ID'] = $existente;
				$post_id    = wp_update_post( $args );
			} else {
				$post_id = wp_insert_post( $args );
			}

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			update_post_meta( $post_id, '_acg_demo', 1 );

			if ( ! empty( $item['imagen'] ) && isset( $medios[ $item['imagen'] ] ) ) {
				set_post_thumbnail( $post_id, $medios[ $item['imagen'] ] );
			}

			if ( ! empty( $item['campos'] ) ) {
				self::save_fields( $post_id, $item['campos'], $medios );
			}

			if ( ! empty( $item['terminos'] ) ) {
				foreach ( $item['terminos'] as $taxonomia => $slugs ) {
					if ( taxonomy_exists( $taxonomia ) ) {
						wp_set_object_terms( $post_id, (array) $slugs, $taxonomia, false );
					}
				}
			}

			$mapa[ $slug ] = (int) $post_id;
		}

		return $mapa;
	}

	/**
	 * Busca un post de la demo por slug.
	 *
	 * @param string $tipo Tipo de post.
	 * @param string $slug Slug.
	 * @return int
	 */
	private static function find_demo_post( $tipo, $slug ) {
		$encontrados = get_posts(
			array(
				'post_type'      => $tipo,
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $encontrados ? (int) $encontrados[0] : 0;
	}

	/**
	 * Guarda campos personalizados resolviendo referencias a medios.
	 *
	 * Los valores que empiezan por «@» apuntan a un archivo del mapa de medios.
	 *
	 * @param int   $post_id ID del post.
	 * @param array $campos  Pares clave/valor.
	 * @param array $medios  Mapa de medios.
	 * @return void
	 */
	private static function save_fields( $post_id, $campos, $medios ) {
		foreach ( $campos as $clave => $valor ) {
			if ( is_string( $valor ) && 0 === strpos( $valor, '@' ) ) {
				$archivo = substr( $valor, 1 );

				if ( ! isset( $medios[ $archivo ] ) ) {
					continue;
				}

				$valor = $medios[ $archivo ];
			}

			if ( function_exists( 'update_field' ) ) {
				update_field( $clave, $valor, $post_id );
			}

			// Se guarda también en meta plana: así el contenido sobrevive a
			// que ACF se active o se desactive.
			update_post_meta( $post_id, $clave, $valor );
		}
	}

	/**
	 * Crea las páginas de la demo, entre ellas la portada.
	 *
	 * @param array $datos  Contenido demo.
	 * @param array $medios Mapa de medios.
	 * @return void
	 */
	private static function import_pages( $datos, $medios ) {
		if ( empty( $datos['paginas'] ) ) {
			return;
		}

		foreach ( $datos['paginas'] as $clave => $pagina ) {
			$slug      = isset( $pagina['slug'] ) ? $pagina['slug'] : sanitize_title( $pagina['titulo'] );
			$existente = self::find_demo_post( 'page', $slug );

			$args = array(
				'post_type'    => 'page',
				'post_title'   => $pagina['titulo'],
				'post_name'    => $slug,
				'post_content' => isset( $pagina['contenido'] ) ? $pagina['contenido'] : '',
				'post_status'  => 'publish',
			);

			if ( $existente ) {
				$args['ID'] = $existente;
				$page_id    = wp_update_post( $args );
			} else {
				$page_id = wp_insert_post( $args );
			}

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				continue;
			}

			update_post_meta( $page_id, '_acg_demo', 1 );

			if ( ! empty( $pagina['campos'] ) ) {
				self::save_fields( $page_id, $pagina['campos'], $medios );
			}

			if ( 'portada' === $clave ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $page_id );
			}

			if ( 'blog' === $clave ) {
				update_option( 'page_for_posts', $page_id );
			}

			if ( 'privacidad' === $clave ) {
				update_option( 'wp_page_for_privacy_policy', $page_id );
			}
		}
	}

	/**
	 * Aplica los ajustes del Personalizador de la demo.
	 *
	 * @param array $opciones Ajustes.
	 * @param array $medios   Mapa de medios.
	 * @return void
	 */
	private static function import_options( $opciones, $medios ) {
		foreach ( $opciones as $clave => $valor ) {
			if ( is_string( $valor ) && 0 === strpos( $valor, '@' ) ) {
				$archivo = substr( $valor, 1 );

				if ( ! isset( $medios[ $archivo ] ) ) {
					continue;
				}

				$valor = $medios[ $archivo ];
			}

			if ( 'blogname' === $clave || 'blogdescription' === $clave ) {
				update_option( $clave, $valor );
				continue;
			}

			set_theme_mod( $clave, $valor );
		}
	}

	/**
	 * Crea el menú principal y el del pie.
	 *
	 * @return void
	 */
	private static function build_menus() {
		$enlaces = array(
			array( 'titulo' => acg_s( 'nav_portafolio' ), 'url' => (string) get_post_type_archive_link( 'acg_trabajo' ) ),
			array( 'titulo' => acg_s( 'nav_servicios' ), 'url' => (string) get_post_type_archive_link( 'acg_servicio' ) ),
			array( 'titulo' => acg_s( 'nav_sobre' ), 'url' => home_url( '/#sobre' ) ),
			array( 'titulo' => acg_s( 'nav_proceso' ), 'url' => home_url( '/#proceso' ) ),
			array( 'titulo' => acg_s( 'nav_contacto' ), 'url' => home_url( '/#contacto' ) ),
		);

		self::build_menu( __( 'Principal', 'acg-visual' ), 'primary', $enlaces );

		self::build_menu(
			__( 'Pie', 'acg-visual' ),
			'footer',
			array(
				array( 'titulo' => acg_s( 'nav_portafolio' ), 'url' => (string) get_post_type_archive_link( 'acg_trabajo' ) ),
				array( 'titulo' => acg_s( 'nav_servicios' ), 'url' => (string) get_post_type_archive_link( 'acg_servicio' ) ),
				array( 'titulo' => acg_s( 'nav_contacto' ), 'url' => home_url( '/#contacto' ) ),
			)
		);

		// El menú legal se arma con las páginas que existan: si alguien borra
		// una de las dos, el menú sale con la otra en vez de con un enlace roto.
		$legales = array();

		foreach ( array( 'aviso-legal', 'politica-de-privacidad' ) as $slug ) {
			$pagina = get_page_by_path( $slug );

			if ( $pagina ) {
				$legales[] = array(
					'titulo' => get_the_title( $pagina ),
					'url'    => (string) get_permalink( $pagina ),
				);
			}
		}

		if ( $legales ) {
			self::build_menu( __( 'Legal', 'acg-visual' ), 'legal', $legales );
		}
	}

	/**
	 * Crea o vacía y rellena un menú, y lo asigna a su ubicación.
	 *
	 * @param string $nombre    Nombre del menú.
	 * @param string $ubicacion Ubicación registrada.
	 * @param array  $enlaces   Lista de enlaces.
	 * @return void
	 */
	private static function build_menu( $nombre, $ubicacion, $enlaces ) {
		$menu = wp_get_nav_menu_object( $nombre );

		if ( $menu ) {
			// Se vacía antes de rellenar para que reimportar no duplique.
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}

			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $nombre );

			if ( is_wp_error( $menu_id ) ) {
				return;
			}
		}

		$orden = 1;

		foreach ( $enlaces as $enlace ) {
			if ( empty( $enlace['url'] ) ) {
				continue;
			}

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'    => $enlace['titulo'],
					'menu-item-url'      => $enlace['url'],
					'menu-item-status'   => 'publish',
					'menu-item-position' => $orden++,
				)
			);
		}

		$ubicaciones               = get_theme_mod( 'nav_menu_locations', array() );
		$ubicaciones[ $ubicacion ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $ubicaciones );
	}

	/**
	 * Elimina todo el contenido marcado como demo.
	 *
	 * @return void
	 */
	public static function handle_reset() {
		if ( ! current_user_can( 'import' ) ) {
			wp_die( esc_html__( 'No tienes permiso para hacer esto.', 'acg-visual' ) );
		}

		check_admin_referer( 'acg_reset_demo' );

		$tipos   = array_values( self::tipos() );
		$tipos[] = 'page';

		$posts = get_posts(
			array(
				'post_type'      => $tipos,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_acg_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 1, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);

		foreach ( $posts as $post_id ) {
			wp_trash_post( $post_id );
		}

		foreach ( (array) get_option( 'acg_demo_medios', array() ) as $adjunto ) {
			if ( get_post_meta( $adjunto, '_acg_demo', true ) ) {
				wp_delete_attachment( $adjunto, true );
			}
		}

		delete_option( 'acg_demo_medios' );
		delete_option( 'acg_demo_importada' );

		wp_safe_redirect( admin_url( 'themes.php?page=' . self::PAGE . '&acg_msg=reset' ) );
		exit;
	}
}

ACG_Demo_Importer::init();
