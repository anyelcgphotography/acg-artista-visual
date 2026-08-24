<?php
/**
 * Portada: mosaico del portafolio con filtros por categoría.
 *
 * Los filtros son de cliente (muestran y ocultan tarjetas ya cargadas) y no
 * una recarga por AJAX: en una portada con seis u ocho fotos, pedir al
 * servidor lo que ya está en la página solo añade espera. Sin JavaScript, los
 * filtros no aparecen y se ven todos los trabajos.
 *
 * @package ACG_Visual
 */

defined( 'ABSPATH' ) || exit;

$acg_limite = (int) acg_home_raw( 'portafolio_limite', 6 );
$acg_limite = $acg_limite > 0 ? $acg_limite : -1;
$acg_query  = acg_query( 'acg_trabajo', $acg_limite, array( 'update_post_term_cache' => true ) );

if ( ! $acg_query->have_posts() ) {
	return;
}

$acg_columnas = (int) acg_home_raw( 'portafolio_columnas', 3 );
$acg_columnas = min( 4, max( 2, $acg_columnas ) );
$acg_filtros  = (bool) acg_home_raw( 'portafolio_filtros', 1 );
$acg_terminos = $acg_filtros ? get_terms(
	array(
		'taxonomy'   => 'acg_categoria',
		'hide_empty' => true,
	)
) : array();
$acg_instagram = get_theme_mod( 'acg_social_instagram', 'https://www.instagram.com/anyelcg_photography' );

acg_section_open( 'portafolio' );
?>
	<div class="acg-contenedor">
		<?php
		acg_section_header(
			array(
				'numero'   => '01',
				'epigrafe' => acg_home( 'portafolio_epigrafe', acg_s( 'nav_portafolio' ) ),
				'titulo'   => acg_home( 'portafolio_titulo' ),
				'texto'    => acg_home( 'portafolio_texto' ),
			)
		);
		?>

		<?php if ( ! is_wp_error( $acg_terminos ) && count( $acg_terminos ) > 1 ) : ?>
			<div class="acg-filtros" data-acg-filtros hidden>
				<button type="button" class="acg-chip is-active" data-filtro="all"><?php echo esc_html( acg_s( 'todo' ) ); ?></button>
				<?php foreach ( $acg_terminos as $acg_termino ) : ?>
					<button type="button" class="acg-chip" data-filtro="<?php echo esc_attr( $acg_termino->slug ); ?>">
						<?php echo esc_html( acg_t( $acg_termino->name, get_term_meta( $acg_termino->term_id, 'nombre_pt', true ) ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="acg-mosaico" style="--acg-cols: <?php echo absint( $acg_columnas ); ?>;" data-acg-mosaico>
			<?php
			while ( $acg_query->have_posts() ) :
				$acg_query->the_post();

				$acg_slugs    = wp_get_post_terms( get_the_ID(), 'acg_categoria', array( 'fields' => 'slugs' ) );
				$acg_slugs    = is_wp_error( $acg_slugs ) ? array() : $acg_slugs;
				$acg_etiqueta = acg_field( 'etiqueta' );
				$acg_ancha    = acg_is_wide();
				?>
				<figure
					class="acg-tarjeta-foto<?php echo $acg_ancha ? ' acg-tarjeta-foto--ancha' : ''; ?>"
					style="aspect-ratio: <?php echo esc_attr( acg_aspect_ratio() ); ?>;"
					data-categorias="<?php echo esc_attr( implode( ' ', $acg_slugs ) ); ?>"
				>
					<a href="<?php the_permalink(); ?>" class="acg-tarjeta-foto__enlace">
						<?php acg_thumb( get_the_ID(), $acg_ancha ? 'acg-apaisada' : 'acg-vertical' ); ?>
						<span class="screen-reader-text"><?php echo esc_html( acg_title() ); ?></span>
					</a>
					<?php if ( $acg_etiqueta ) : ?>
						<figcaption class="acg-tarjeta-foto__pie"><?php echo esc_html( $acg_etiqueta ); ?></figcaption>
					<?php endif; ?>
				</figure>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<p class="acg-mosaico__vacio" data-acg-vacio hidden><?php echo esc_html( acg_s( 'sin_resultados' ) ); ?></p>

		<div class="acg-mosaico__pie">
			<span class="acg-raya" aria-hidden="true"></span>
			<a class="acg-enlace-sub" href="<?php echo esc_url( get_post_type_archive_link( 'acg_trabajo' ) ); ?>"><?php echo esc_html( acg_s( 'nav_portafolio' ) ); ?></a>
			<?php if ( $acg_instagram ) : ?>
				<a class="acg-enlace-sub" href="<?php echo esc_url( $acg_instagram ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( acg_s( 'mas_instagram' ) ); ?></a>
			<?php endif; ?>
		</div>
	</div>
<?php
acg_section_close();
