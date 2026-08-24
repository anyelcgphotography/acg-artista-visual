<?php
/**
 * Meta boxes nativas para cuando ACF no está instalado.
 *
 * El sitio se entrega con ACF gratuito, pero el theme no puede dar por hecho
 * que siga activo: si alguien lo desactiva, sin esto la portada quedaría con
 * los textos guardados pero sin forma de editarlos. Con esto, los mismos
 * campos siguen siendo editables —con una interfaz más sobria— y sobre las
 * **mismas meta keys**, así que el contenido se conserva al activar o
 * desactivar el plugin.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

/**
 * ¿Está ACF disponible para dibujar los campos?
 *
 * @return bool
 */
function acg_has_acf() {
	return function_exists( 'acf_add_local_field_group' );
}

/**
 * Registra una meta box por grupo de campos en las pantallas que le tocan.
 *
 * @return void
 */
function acg_register_meta_boxes() {
	if ( acg_has_acf() ) {
		return;
	}

	foreach ( acg_field_groups() as $grupo_id => $grupo ) {
		$pantallas = isset( $grupo['screens'] ) ? $grupo['screens'] : array();

		foreach ( $pantallas as $pantalla ) {
			add_meta_box(
				'acg_meta_' . $grupo_id,
				$grupo['title'],
				'acg_render_meta_box',
				$pantalla,
				'normal',
				'high',
				array( 'grupo' => $grupo_id )
			);
		}
	}
}
add_action( 'add_meta_boxes', 'acg_register_meta_boxes' );

/**
 * Pinta los campos de un grupo.
 *
 * @param WP_Post $post     Post que se está editando.
 * @param array   $meta_box Datos de la meta box, con el id del grupo.
 * @return void
 */
function acg_render_meta_box( $post, $meta_box ) {
	$grupo_id = $meta_box['args']['grupo'];
	$grupos   = acg_field_groups();

	if ( ! isset( $grupos[ $grupo_id ] ) ) {
		return;
	}

	// El grupo de la portada solo tiene sentido en la página de inicio.
	if ( 'portada' === $grupo_id && (int) get_option( 'page_on_front' ) !== (int) $post->ID ) {
		printf(
			'<p>%s</p>',
			esc_html__( 'Estos campos se editan en la página que esté asignada como portada del sitio (Ajustes → Lectura).', 'acg-visual' )
		);
		return;
	}

	wp_nonce_field( 'acg_guardar_meta', 'acg_meta_nonce' );

	$campos = $grupos[ $grupo_id ]['fields'];

	$tiene_pestanas = false;
	foreach ( $campos as $campo ) {
		if ( 'tab' === $campo['type'] ) {
			$tiene_pestanas = true;
			break;
		}
	}

	// Sin ACF no hay pestañas de verdad, pero un grupo con <details> por
	// bloque —la primera abierta— se acerca bastante: se ve de un vistazo
	// qué secciones hay y se pliega lo que no se está editando.
	if ( ! $tiene_pestanas ) {
		echo '<div class="acg-meta-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;">';
		foreach ( $campos as $campo ) {
			acg_render_meta_field( $campo, $post->ID );
		}
		echo '</div>';
		return;
	}

	$primero = true;
	$abierto = false;

	foreach ( $campos as $campo ) {
		if ( 'tab' === $campo['type'] ) {
			if ( $abierto ) {
				echo '</div></details>';
			}

			printf(
				'<details class="acg-meta-tab" style="margin-bottom:10px;border-top:1px solid #dcdcde;"%1$s><summary style="cursor:pointer;font-weight:600;font-size:14px;padding:12px 0;">%2$s</summary><div class="acg-meta-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;padding-bottom:14px;">',
				$primero ? ' open' : '',
				esc_html( $campo['label'] )
			);

			$primero = false;
			$abierto = true;
			continue;
		}

		acg_render_meta_field( $campo, $post->ID );
	}

	if ( $abierto ) {
		echo '</div></details>';
	}
}

/**
 * Pinta un campo suelto dentro de la meta box.
 *
 * @param array $campo   Campo del catálogo.
 * @param int   $post_id ID del post.
 * @return void
 */
function acg_render_meta_field( $campo, $post_id ) {
	// Las pestañas no dibujan un campo: acg_render_meta_box() ya las usa
	// para abrir y cerrar los bloques <details>, así que aquí solo hay que
	// evitar que caigan por defecto al renderizado de campo de texto.
	if ( 'tab' === $campo['type'] ) {
		return;
	}

	$id     = 'acg_campo_' . $campo['name'];
	$valor  = get_post_meta( $post_id, $campo['name'], true );
	$valor  = ( '' === $valor && 'separator' !== $campo['type'] ) ? $campo['default'] : $valor;
	$span   = $campo['ancho'] >= 100 ? 'grid-column:1/-1;' : '';

	if ( 'separator' === $campo['type'] ) {
		printf(
			'<h3 style="grid-column:1/-1;margin:18px 0 0;padding-top:14px;border-top:1px solid #dcdcde;font-size:14px;">%s</h3>',
			esc_html( $campo['label'] )
		);
		return;
	}

	printf( '<p style="%smargin:0;">', esc_attr( $span ) );
	printf( '<label for="%s" style="display:block;font-weight:600;margin-bottom:5px;">%s</label>', esc_attr( $id ), esc_html( $campo['label'] ) );

	switch ( $campo['type'] ) {
		case 'textarea':
		case 'wysiwyg':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="%3$d" class="widefat">%4$s</textarea>',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				absint( 'wysiwyg' === $campo['type'] ? 8 : $campo['rows'] ),
				esc_textarea( (string) $valor )
			);
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s" class="widefat">', esc_attr( $id ), esc_attr( $campo['name'] ) );
			foreach ( $campo['choices'] as $clave => $etiqueta ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $clave ),
					selected( (string) $valor, (string) $clave, false ),
					esc_html( $etiqueta )
				);
			}
			echo '</select>';
			break;

		case 'true_false':
			printf(
				'<input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s>',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				checked( (string) $valor, '1', false )
			);
			break;

		case 'image':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" class="widefat" min="0" step="1">',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				esc_attr( (string) $valor )
			);
			printf(
				'<span class="description">%s</span>',
				esc_html__( 'ID de la imagen en la biblioteca de medios (se ve en la URL al abrirla). Con ACF activo este campo es un selector visual.', 'acg-visual' )
			);
			break;

		case 'number':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" class="widefat">',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				esc_attr( (string) $valor )
			);
			break;

		case 'url':
			printf(
				'<input type="url" id="%1$s" name="%2$s" value="%3$s" class="widefat" placeholder="https://">',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				esc_attr( (string) $valor )
			);
			break;

		default:
			printf(
				'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="widefat">',
				esc_attr( $id ),
				esc_attr( $campo['name'] ),
				esc_attr( (string) $valor )
			);
			break;
	}

	if ( $campo['instructions'] ) {
		printf( '<span class="description" style="display:block;margin-top:4px;">%s</span>', esc_html( $campo['instructions'] ) );
	}

	echo '</p>';
}

/**
 * Guarda los campos de las meta boxes.
 *
 * @param int $post_id ID del post.
 * @return void
 */
function acg_save_meta_boxes( $post_id ) {
	if ( acg_has_acf() ) {
		return;
	}

	if ( ! isset( $_POST['acg_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['acg_meta_nonce'] ) ), 'acg_guardar_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$tipo = get_post_type( $post_id );

	foreach ( acg_field_groups() as $grupo ) {
		$pantallas = isset( $grupo['screens'] ) ? $grupo['screens'] : array();

		if ( ! in_array( $tipo, $pantallas, true ) ) {
			continue;
		}

		foreach ( $grupo['fields'] as $campo ) {
			if ( in_array( $campo['type'], array( 'separator', 'tab' ), true ) ) {
				continue;
			}

			$clave = $campo['name'];

			// Las casillas no viajan en el POST cuando están desmarcadas, así
			// que se guardan explícitamente a 0 en vez de dejar el valor viejo.
			if ( 'true_false' === $campo['type'] ) {
				update_post_meta( $post_id, $clave, isset( $_POST[ $clave ] ) ? '1' : '0' );
				continue;
			}

			if ( ! isset( $_POST[ $clave ] ) ) {
				continue;
			}

			$bruto = wp_unslash( $_POST[ $clave ] ); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotSanitized

			switch ( $campo['type'] ) {
				case 'wysiwyg':
					$valor = wp_kses_post( $bruto );
					break;

				case 'textarea':
					$valor = sanitize_textarea_field( $bruto );
					break;

				case 'image':
				case 'number':
					$valor = '' === trim( (string) $bruto ) ? '' : (string) absint( $bruto );
					break;

				case 'url':
					$valor = esc_url_raw( $bruto );
					break;

				case 'select':
					$valor = isset( $campo['choices'][ $bruto ] ) ? (string) $bruto : (string) $campo['default'];
					break;

				default:
					$valor = sanitize_text_field( $bruto );
					break;
			}

			update_post_meta( $post_id, $clave, $valor );
		}
	}
}
add_action( 'save_post', 'acg_save_meta_boxes' );
