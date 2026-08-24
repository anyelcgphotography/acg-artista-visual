/**
 * Genera docs/, una copia autocontenida de la maqueta lista para GitHub Pages.
 *
 * GitHub Pages, sin workflow de Actions, solo sabe servir dos sitios: la raíz
 * del repositorio o una carpeta llamada exactamente `docs/` en la rama
 * principal. `docs/` es la opción que no obliga a mover el resto del
 * proyecto (theme, plugin, ZIP) a la raíz del repo.
 *
 * `_preview/index.html` no se puede publicar tal cual: referencia el CSS, el
 * JS y las imágenes de muestra con rutas relativas hacia `../acg-visual/...`,
 * que solo funcionan porque hoy vive junto a esa carpeta en el proyecto. Este
 * script copia dentro de `docs/` exactamente los archivos que la maqueta usa
 * — leídos de la fuente real del theme, nunca duplicados a mano — y reescribe
 * esas rutas para que apunten dentro de la propia carpeta publicada.
 *
 * `docs/` es contenido generado: se borra y se reconstruye entero en cada
 * ejecución, así que nunca hace falta (ni conviene) editarlo a mano.
 *
 * Uso: node _tools/build-docs.mjs
 */
import { existsSync, mkdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const RAIZ = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const ORIGEN_HTML = join( RAIZ, '_preview', 'index.html' );
const DESTINO = join( RAIZ, 'docs' );

if ( ! existsSync( ORIGEN_HTML ) ) {
	console.error( 'No encuentro _preview/index.html.' );
	process.exit( 1 );
}

// docs/ es contenido generado por este script: se reconstruye desde cero para
// que nunca queden archivos de una versión anterior de la maqueta.
if ( existsSync( DESTINO ) ) {
	rmSync( DESTINO, { recursive: true, force: true } );
}
mkdirSync( join( DESTINO, 'assets' ), { recursive: true } );
mkdirSync( join( DESTINO, 'images' ), { recursive: true } );

let html = readFileSync( ORIGEN_HTML, 'utf8' );

/**
 * Marca de versión para el cache-busting, igual de estable dentro de una
 * misma ejecución del script para el CSS y el JS.
 */
const version = Date.now();

/**
 * Copia un archivo de origen a destino y devuelve su tamaño en bytes.
 *
 * @param {string} origen  Ruta absoluta de origen.
 * @param {string} destino Ruta absoluta de destino.
 * @return {number}
 */
function copiar( origen, destino ) {
	if ( ! existsSync( origen ) ) {
		throw new Error( `La maqueta referencia un archivo que no existe: ${ origen }` );
	}

	writeFileSync( destino, readFileSync( origen ) );

	return statSync( destino ).size;
}

let total = 0;

// CSS y JS del theme: un archivo cada uno, con su versión para que el
// navegador no sirva una copia vieja tras el siguiente despliegue.
total += copiar( join( RAIZ, 'acg-visual', 'assets', 'css', 'theme.css' ), join( DESTINO, 'assets', 'theme.css' ) );
total += copiar( join( RAIZ, 'acg-visual', 'assets', 'js', 'theme.js' ), join( DESTINO, 'assets', 'theme.js' ) );

html = html.replace( /\.\.\/acg-visual\/assets\/css\/theme\.css(\?v=\d+)?/, `assets/theme.css?v=${ version }` );
html = html.replace( /\.\.\/acg-visual\/assets\/js\/theme\.js(\?v=\d+)?/, `assets/theme.js?v=${ version }` );

// Imágenes de muestra: solo las que la maqueta usa de verdad, detectadas por
// sus propias referencias — así el script avisa si alguna falta en vez de
// publicar un <img> roto, y docs/ no arrastra imágenes que ya no se usan.
const referencias = [ ...html.matchAll( /\.\.\/acg-visual\/demo\/images\/([\w-]+\.svg)/g ) ];
const imagenes = [ ...new Set( referencias.map( ( m ) => m[ 1 ] ) ) ];

if ( ! imagenes.length ) {
	console.warn( 'Aviso: la maqueta no referencia ninguna imagen de demo/images.' );
}

for ( const archivo of imagenes ) {
	total += copiar( join( RAIZ, 'acg-visual', 'demo', 'images', archivo ), join( DESTINO, 'images', archivo ) );
}

html = html.replace( /\.\.\/acg-visual\/demo\/images\//g, 'images/' );

writeFileSync( join( DESTINO, 'index.html' ), html );

// Sin esto, GitHub Pages pasa el sitio por Jekyll antes de publicarlo: un
// paso de construcción de más que este sitio no necesita, y que además
// ignora por convención cualquier carpeta que empiece por guion bajo.
writeFileSync( join( DESTINO, '.nojekyll' ), '' );

console.log( `docs/ generada: index.html, assets/theme.css, assets/theme.js, ${ imagenes.length } imágenes (${ ( total / 1024 ).toFixed( 1 ) } KB en total).` );
console.log( 'Sin publicar todavía: nada de esto se ha subido a git ni a GitHub.' );
