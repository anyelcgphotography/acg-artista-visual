/**
 * Comprobador de sintaxis PHP sin PHP.
 *
 * No sustituye a `php -l`, pero recorre cada archivo como lo haría el lexer:
 * distingue HTML de código, cadenas simples y dobles, comentarios de línea y
 * de bloque, y heredoc/nowdoc. Sobre ese recorrido comprueba el equilibrio de
 * llaves, paréntesis y corchetes, y detecta delimitadores sin cerrar, que son
 * los errores fatales que de verdad rompen un theme al activarlo.
 *
 * Uso: node _tools/check-php.mjs [directorio...]
 */
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const CIERRES = { '}': '{', ')': '(', ']': '[' };

/**
 * Recorre un directorio devolviendo todos los .php que encuentre.
 *
 * @param {string} dir Directorio raíz.
 * @return {string[]} Rutas de archivo.
 */
function listarPhp( dir ) {
	const salida = [];

	for ( const entrada of readdirSync( dir ) ) {
		if ( entrada.startsWith( '.' ) || entrada === 'node_modules' ) {
			continue;
		}

		const ruta = join( dir, entrada );

		if ( statSync( ruta ).isDirectory() ) {
			salida.push( ...listarPhp( ruta ) );
		} else if ( ruta.endsWith( '.php' ) ) {
			salida.push( ruta );
		}
	}

	return salida;
}

/**
 * Analiza un archivo y devuelve la lista de problemas encontrados.
 *
 * @param {string} ruta Ruta del archivo.
 * @return {string[]} Problemas.
 */
function analizar( ruta ) {
	const src = readFileSync( ruta, 'utf8' );
	const problemas = [];
	const pila = [];

	let i = 0;
	let linea = 1;
	let enPhp = false;

	/**
	 * Avanza n caracteres contando los saltos de línea.
	 *
	 * @param {number} n Cuántos caracteres avanzar.
	 */
	function avanzar( n ) {
		for ( let k = 0; k < n && i < src.length; k++, i++ ) {
			if ( src[ i ] === '\n' ) {
				linea++;
			}
		}
	}

	while ( i < src.length ) {
		if ( ! enPhp ) {
			const abre = src.indexOf( '<?php', i );
			const abreCorto = src.indexOf( '<?=', i );
			let pos = -1;
			let largo = 0;

			if ( abre !== -1 && ( abreCorto === -1 || abre < abreCorto ) ) {
				pos = abre;
				largo = 5;
			} else if ( abreCorto !== -1 ) {
				pos = abreCorto;
				largo = 3;
			}

			if ( pos === -1 ) {
				break;
			}

			avanzar( pos - i + largo );
			enPhp = true;
			continue;
		}

		const c = src[ i ];
		const dos = src.substr( i, 2 );

		// Cierre de bloque PHP.
		if ( dos === '?>' ) {
			avanzar( 2 );
			enPhp = false;
			continue;
		}

		// Comentario de línea.
		if ( dos === '//' || c === '#' ) {
			const fin = src.indexOf( '\n', i );
			avanzar( ( fin === -1 ? src.length : fin ) - i );
			continue;
		}

		// Comentario de bloque.
		if ( dos === '/*' ) {
			const fin = src.indexOf( '*/', i + 2 );
			if ( fin === -1 ) {
				problemas.push( `línea ${ linea }: comentario /* sin cerrar` );
				break;
			}
			avanzar( fin + 2 - i );
			continue;
		}

		// Heredoc y nowdoc.
		if ( dos === '<<' && src[ i + 2 ] === '<' ) {
			const resto = src.slice( i + 3 );
			const m = resto.match( /^[ \t]*(['"]?)([A-Za-z_][A-Za-z0-9_]*)\1\r?\n/ );

			if ( ! m ) {
				problemas.push( `línea ${ linea }: apertura de heredoc mal formada` );
				avanzar( 3 );
				continue;
			}

			const etiqueta = m[ 2 ];
			avanzar( 3 + m[ 0 ].length );

			// El cierre es la etiqueta al principio de una línea (admite sangría).
			const cierre = new RegExp( `^[ \\t]*${ etiqueta }\\b`, 'm' );
			const trozo = src.slice( i );
			const enc = trozo.match( cierre );

			if ( ! enc ) {
				problemas.push( `línea ${ linea }: heredoc «${ etiqueta }» sin cerrar` );
				break;
			}

			avanzar( enc.index + enc[ 0 ].length );
			continue;
		}

		// Cadenas.
		if ( c === "'" || c === '"' ) {
			const comilla = c;
			const inicio = linea;
			avanzar( 1 );
			let cerrada = false;

			while ( i < src.length ) {
				if ( src[ i ] === '\\' ) {
					avanzar( 2 );
					continue;
				}
				if ( src[ i ] === comilla ) {
					avanzar( 1 );
					cerrada = true;
					break;
				}
				avanzar( 1 );
			}

			if ( ! cerrada ) {
				problemas.push( `línea ${ inicio }: cadena ${ comilla } sin cerrar` );
				break;
			}
			continue;
		}

		// Equilibrio de delimitadores.
		if ( c === '{' || c === '(' || c === '[' ) {
			pila.push( { c, linea } );
			avanzar( 1 );
			continue;
		}

		if ( c === '}' || c === ')' || c === ']' ) {
			const esperado = CIERRES[ c ];
			const ultimo = pila.pop();

			if ( ! ultimo ) {
				problemas.push( `línea ${ linea }: «${ c }» sobrante` );
			} else if ( ultimo.c !== esperado ) {
				problemas.push( `línea ${ linea }: «${ c }» no casa con «${ ultimo.c }» abierto en la línea ${ ultimo.linea }` );
			}

			avanzar( 1 );
			continue;
		}

		avanzar( 1 );
	}

	for ( const abierto of pila ) {
		problemas.push( `línea ${ abierto.linea }: «${ abierto.c }» sin cerrar` );
	}

	return problemas;
}

const dirs = process.argv.slice( 2 );

if ( ! dirs.length ) {
	console.error( 'Indica al menos un directorio.' );
	process.exit( 1 );
}

let archivos = 0;
let conFallos = 0;

for ( const dir of dirs ) {
	for ( const ruta of listarPhp( dir ) ) {
		archivos++;
		const problemas = analizar( ruta );

		if ( problemas.length ) {
			conFallos++;
			console.log( `\n✗ ${ relative( process.cwd(), ruta ) }` );
			problemas.forEach( ( p ) => console.log( `    ${ p }` ) );
		}
	}
}

console.log( `\n${ archivos } archivos analizados, ${ conFallos } con problemas.` );
process.exit( conFallos ? 1 : 0 );
