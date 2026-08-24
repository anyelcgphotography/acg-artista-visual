/**
 * Empaqueta el theme y el plugin en ZIP listos para instalar en WordPress.
 *
 * Guarda una huella SHA-256 del contenido de cada paquete en
 * _dist/.manifest.json y solo vuelve a comprimir los que han cambiado: si
 * tocas el theme, el ZIP del plugin se queda como estaba.
 *
 * Escribe el ZIP a mano (cabecera local + directorio central + EOCD) para no
 * depender de utilidades externas ni de cómo Compress-Archive normaliza rutas.
 *
 * Uso:
 *   node _tools/package.mjs           empaqueta lo que haya cambiado
 *   node _tools/package.mjs --force   empaqueta todo
 */
import { createHash } from 'node:crypto';
import { deflateRawSync } from 'node:zlib';
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const dist = join( raiz, '_dist' );
const manifiesto = join( dist, '.manifest.json' );
const forzar = process.argv.includes( '--force' );

const PAQUETES = [
	{ carpeta: 'acg-visual', zip: 'acg-visual.zip', nombre: 'Theme ACG Artista Visual' },
	{ carpeta: 'acg-crm', zip: 'acg-crm.zip', nombre: 'Plugin ACG CRM' },
];

// Nunca deben viajar dentro del paquete.
const EXCLUIR = [ /(^|[\\/])\./, /node_modules/, /\.map$/, /Thumbs\.db$/, /\.DS_Store$/ ];

/* ------------------------------------------------------------------ */
/* Recorrido de archivos                                               */
/* ------------------------------------------------------------------ */

/**
 * Lista recursivamente los archivos de un directorio, en orden estable.
 *
 * @param {string} dir  Directorio a recorrer.
 * @param {string} base Directorio raíz, para calcular rutas relativas.
 * @return {string[]} Rutas relativas con separador «/».
 */
function listar( dir, base ) {
	const salida = [];

	for ( const entrada of readdirSync( dir ).sort() ) {
		const ruta = join( dir, entrada );
		const rel = relative( base, ruta ).split( sep ).join( '/' );

		if ( EXCLUIR.some( ( re ) => re.test( rel ) ) ) {
			continue;
		}

		if ( statSync( ruta ).isDirectory() ) {
			salida.push( ...listar( ruta, base ) );
		} else {
			salida.push( rel );
		}
	}

	return salida;
}

/**
 * Huella del contenido de un paquete: nombres y bytes de todos sus archivos.
 *
 * @param {string} dir      Directorio del paquete.
 * @param {string[]} rutas  Rutas relativas.
 * @return {string} SHA-256 en hexadecimal.
 */
function huella( dir, rutas ) {
	const hash = createHash( 'sha256' );

	for ( const rel of rutas ) {
		hash.update( rel );
		hash.update( readFileSync( join( dir, rel ) ) );
	}

	return hash.digest( 'hex' );
}

/* ------------------------------------------------------------------ */
/* Escritura del ZIP                                                   */
/* ------------------------------------------------------------------ */

const tablaCrc = ( () => {
	const t = new Int32Array( 256 );
	for ( let n = 0; n < 256; n++ ) {
		let c = n;
		for ( let k = 0; k < 8; k++ ) {
			c = c & 1 ? 0xedb88320 ^ ( c >>> 1 ) : c >>> 1;
		}
		t[ n ] = c;
	}
	return t;
} )();

/**
 * CRC-32 de un búfer, tal como lo exige el formato ZIP.
 *
 * @param {Buffer} buf Datos.
 * @return {number} CRC sin signo.
 */
function crc32( buf ) {
	let c = 0xffffffff;
	for ( let i = 0; i < buf.length; i++ ) {
		c = tablaCrc[ ( c ^ buf[ i ] ) & 0xff ] ^ ( c >>> 8 );
	}
	return ( c ^ 0xffffffff ) >>> 0;
}

/**
 * Convierte una fecha a la representación MS-DOS que usa el ZIP.
 *
 * @param {Date} d Fecha.
 * @return {{hora:number,fecha:number}} Campos empaquetados.
 */
function fechaDos( d ) {
	return {
		hora: ( d.getHours() << 11 ) | ( d.getMinutes() << 5 ) | ( d.getSeconds() >> 1 ),
		fecha: ( ( d.getFullYear() - 1980 ) << 9 ) | ( ( d.getMonth() + 1 ) << 5 ) | d.getDate(),
	};
}

/**
 * Crea un ZIP con los archivos indicados bajo una carpeta raíz.
 *
 * WordPress exige que el theme o el plugin cuelguen de una única carpeta
 * dentro del ZIP, por eso todas las rutas se prefijan con `carpetaRaiz`.
 *
 * @param {string}   destino     Ruta del ZIP a escribir.
 * @param {string}   dir         Directorio de origen.
 * @param {string[]} rutas       Rutas relativas a incluir.
 * @param {string}   carpetaRaiz Nombre de la carpeta dentro del ZIP.
 * @return {number} Tamaño del ZIP en bytes.
 */
function escribirZip( destino, dir, rutas, carpetaRaiz ) {
	const locales = [];
	const central = [];
	const ahora = fechaDos( new Date() );

	let offset = 0;

	for ( const rel of rutas ) {
		const nombre = `${ carpetaRaiz }/${ rel }`;
		const nombreBuf = Buffer.from( nombre, 'utf8' );
		const datos = readFileSync( join( dir, rel ) );
		const comprimido = deflateRawSync( datos, { level: 9 } );

		// Si comprimir no compensa, se guarda tal cual (método 0).
		const usarDeflate = comprimido.length < datos.length;
		const carga = usarDeflate ? comprimido : datos;
		const metodo = usarDeflate ? 8 : 0;
		const crc = crc32( datos );

		const cabecera = Buffer.alloc( 30 );
		cabecera.writeUInt32LE( 0x04034b50, 0 );
		cabecera.writeUInt16LE( 20, 4 ); // Versión necesaria.
		cabecera.writeUInt16LE( 0x0800, 6 ); // Nombres en UTF-8.
		cabecera.writeUInt16LE( metodo, 8 );
		cabecera.writeUInt16LE( ahora.hora, 10 );
		cabecera.writeUInt16LE( ahora.fecha, 12 );
		cabecera.writeUInt32LE( crc, 14 );
		cabecera.writeUInt32LE( carga.length, 18 );
		cabecera.writeUInt32LE( datos.length, 22 );
		cabecera.writeUInt16LE( nombreBuf.length, 26 );
		cabecera.writeUInt16LE( 0, 28 );

		locales.push( cabecera, nombreBuf, carga );

		const entrada = Buffer.alloc( 46 );
		entrada.writeUInt32LE( 0x02014b50, 0 );
		entrada.writeUInt16LE( 20, 4 ); // Versión con la que se creó.
		entrada.writeUInt16LE( 20, 6 ); // Versión necesaria.
		entrada.writeUInt16LE( 0x0800, 8 );
		entrada.writeUInt16LE( metodo, 10 );
		entrada.writeUInt16LE( ahora.hora, 12 );
		entrada.writeUInt16LE( ahora.fecha, 14 );
		entrada.writeUInt32LE( crc, 16 );
		entrada.writeUInt32LE( carga.length, 20 );
		entrada.writeUInt32LE( datos.length, 24 );
		entrada.writeUInt16LE( nombreBuf.length, 28 );
		entrada.writeUInt16LE( 0, 30 ); // Longitud del campo extra.
		entrada.writeUInt16LE( 0, 32 ); // Longitud del comentario.
		entrada.writeUInt16LE( 0, 34 ); // Número de disco.
		entrada.writeUInt16LE( 0, 36 ); // Atributos internos.
		entrada.writeUInt32LE( 0o644 << 16, 38 ); // Permisos POSIX.
		entrada.writeUInt32LE( offset, 42 );

		central.push( entrada, nombreBuf );

		offset += cabecera.length + nombreBuf.length + carga.length;
	}

	const bufLocales = Buffer.concat( locales );
	const bufCentral = Buffer.concat( central );

	const eocd = Buffer.alloc( 22 );
	eocd.writeUInt32LE( 0x06054b50, 0 );
	eocd.writeUInt16LE( 0, 4 );
	eocd.writeUInt16LE( 0, 6 );
	eocd.writeUInt16LE( rutas.length, 8 );
	eocd.writeUInt16LE( rutas.length, 10 );
	eocd.writeUInt32LE( bufCentral.length, 12 );
	eocd.writeUInt32LE( bufLocales.length, 16 );
	eocd.writeUInt16LE( 0, 20 );

	const zip = Buffer.concat( [ bufLocales, bufCentral, eocd ] );
	writeFileSync( destino, zip );

	return zip.length;
}

/* ------------------------------------------------------------------ */
/* Ejecución                                                           */
/* ------------------------------------------------------------------ */

mkdirSync( dist, { recursive: true } );

const previo = existsSync( manifiesto )
	? JSON.parse( readFileSync( manifiesto, 'utf8' ) )
	: {};

const nuevo = {};
let cambios = 0;

for ( const paquete of PAQUETES ) {
	const dir = join( raiz, paquete.carpeta );

	if ( ! existsSync( dir ) ) {
		console.log( `· ${ paquete.nombre }: carpeta no encontrada, se omite` );
		continue;
	}

	const rutas = listar( dir, dir );
	const firma = huella( dir, rutas );
	const destino = join( dist, paquete.zip );

	nuevo[ paquete.carpeta ] = { hash: firma, archivos: rutas.length };

	const sinCambios = previo[ paquete.carpeta ]?.hash === firma;

	if ( sinCambios && existsSync( destino ) && ! forzar ) {
		console.log( `· ${ paquete.nombre }: sin cambios, no se vuelve a exportar` );
		continue;
	}

	const bytes = escribirZip( destino, dir, rutas, paquete.carpeta );
	cambios++;

	console.log(
		`✓ ${ paquete.nombre } → _dist/${ paquete.zip } ` +
		`(${ rutas.length } archivos, ${ ( bytes / 1024 ).toFixed( 1 ) } KB)`
	);
}

writeFileSync( manifiesto, JSON.stringify( nuevo, null, '\t' ) + '\n', 'utf8' );

console.log( cambios ? `\n${ cambios } paquete(s) exportado(s).` : '\nNada que exportar: todo estaba al día.' );
