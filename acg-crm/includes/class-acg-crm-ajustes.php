<?php
/**
 * Ajustes del CRM, bajo el menú de solicitudes.
 *
 * Son cuatro opciones y viven en una sola entrada de `wp_options` en vez de
 * una fila por ajuste: se leen siempre juntas.
 *
 * @package ACG_CRM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pantalla de ajustes.
 */
class ACG_CRM_Ajustes {

	/**
	 * Slug de la página.
	 */
	const PAGE = 'acg-crm-ajustes';

	/**
	 * Engancha la pantalla.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'registrar' ) );
	}

	/**
	 * Añade la página bajo «CRM».
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . ACG_CRM_CPT::TIPO,
			__( 'Ajustes del CRM', 'acg-crm' ),
			__( 'Ajustes', 'acg-crm' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Registra la opción y su saneado.
	 *
	 * @return void
	 */
	public static function registrar() {
		register_setting(
			'acg_crm_ajustes',
			'acg_crm_opciones',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanear' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanea los ajustes recibidos.
	 *
	 * @param mixed $valores Valores del formulario.
	 * @return array
	 */
	public static function sanear( $valores ) {
		$valores = is_array( $valores ) ? $valores : array();

		return array(
			'email_destino' => isset( $valores['email_destino'] ) ? sanitize_email( $valores['email_destino'] ) : '',
			'acuse_recibo'  => empty( $valores['acuse_recibo'] ) ? '' : '1',
			'aviso_admin'   => empty( $valores['aviso_admin'] ) ? '' : '1',
			'firma'         => isset( $valores['firma'] ) ? sanitize_text_field( $valores['firma'] ) : '',
		);
	}

	/**
	 * Pinta la pantalla.
	 *
	 * @return void
	 */
	public static function render() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Ajustes del CRM', 'acg-crm' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'acg_crm_ajustes' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="acg_crm_email"><?php esc_html_e( 'Email que recibe las solicitudes', 'acg-crm' ); ?></label>
						</th>
						<td>
							<input
								type="email"
								id="acg_crm_email"
								name="acg_crm_opciones[email_destino]"
								value="<?php echo esc_attr( acg_crm_opcion( 'email_destino' ) ); ?>"
								class="regular-text"
							>
							<p class="description">
								<?php
								printf(
									/* translators: %s: dirección de correo. */
									esc_html__( 'Si lo dejas vacío se usa %s.', 'acg-crm' ),
									esc_html( get_option( 'admin_email' ) )
								);
								?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Avisos por correo', 'acg-crm' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="acg_crm_opciones[acuse_recibo]"
									value="1"
									<?php checked( acg_crm_opcion( 'acuse_recibo', '1' ), '1' ); ?>
								>
								<?php esc_html_e( 'Enviar un acuse de recibo a quien escribe, en su idioma', 'acg-crm' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Usar el formulario en otro theme', 'acg-crm' ); ?></h2>
			<p>
				<?php esc_html_e( 'Con el theme ACG Artista Visual el formulario ya está en la portada. En cualquier otro theme, pega este shortcode en la página que quieras:', 'acg-crm' ); ?>
			</p>
			<p><code>[acg_formulario]</code></p>
		</div>
		<?php
	}
}
