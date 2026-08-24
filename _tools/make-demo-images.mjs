/**
 * Genera las imágenes del contenido demo.
 *
 * Son SVG y no fotos: pesan unos pocos KB, se ven nítidos en cualquier
 * pantalla y no obligan a meter en el repositorio material con derechos de
 * terceros. Angie los sustituye por sus fotos desde la biblioteca de medios
 * sin tocar una línea de código.
 *
 * Cada escena es la misma receta —cielo en degradado, sol, horizonte,
 * siluetas y un grano muy sutil— con distinta paleta y distintos elementos,
 * de modo que la demo parece un portafolio y no una rejilla de rectángulos.
 *
 * Uso: node _tools/make-demo-images.mjs
 */
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const RAIZ = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const DESTINO = join( RAIZ, 'acg-visual', 'demo', 'images' );

const NARANJA = '#fa6613';

/**
 * Escenas de la demo. `w` y `h` marcan la proporción del recorte.
 */
const ESCENAS = [
	{
		archivo: 'acg-hero.svg',
		w: 1600, h: 1000,
		cielo: [ '#1a1410', '#5a2c12', '#c25a1c' ],
		suelo: '#0a0a0a',
		sol: { x: 0.66, y: 0.44, r: 0.1, color: '#ffb066' },
		figuras: [ { tipo: 'pareja', x: 0.42, escala: 1 } ],
		aves: true,
	},
	{
		archivo: 'boda-ceremonia.svg',
		w: 1500, h: 1000,
		cielo: [ '#241a16', '#6b3a1c', '#e08a3c' ],
		suelo: '#120d0a',
		sol: { x: 0.28, y: 0.5, r: 0.08, color: '#ffd0a0' },
		figuras: [ { tipo: 'pareja', x: 0.6, escala: .9 }, { tipo: 'persona', x: 0.8, escala: .55 } ],
	},
	{
		archivo: 'boda-detalle.svg',
		w: 1000, h: 1250,
		cielo: [ '#0e0e0e', '#2a2018', '#7a4a24' ],
		suelo: '#0b0b0b',
		sol: { x: 0.5, y: 0.36, r: 0.16, color: '#ffcf9a' },
		figuras: [ { tipo: 'ramo', x: 0.5, escala: 1 } ],
	},
	{
		archivo: 'evento-corporativo.svg',
		w: 1000, h: 1250,
		cielo: [ '#0b1016', '#16283a', '#2f5b7e' ],
		suelo: '#080b0e',
		sol: { x: 0.7, y: 0.3, r: 0.07, color: '#cfe6ff' },
		figuras: [ { tipo: 'multitud', x: 0.5, escala: 1 } ],
	},
	{
		archivo: 'evento-escenario.svg',
		w: 1500, h: 1000,
		cielo: [ '#0a0a0f', '#1e1230', '#5b2a55' ],
		suelo: '#09090c',
		sol: { x: 0.5, y: 0.28, r: 0.13, color: '#ff9ad2' },
		figuras: [ { tipo: 'multitud', x: 0.5, escala: 1.1 } ],
		focos: true,
	},
	{
		archivo: 'marca-personal.svg',
		w: 1000, h: 1250,
		cielo: [ '#151210', '#3a2b20', '#9a6a3c' ],
		suelo: '#0d0b09',
		sol: { x: 0.36, y: 0.34, r: 0.2, color: '#ffddb4' },
		figuras: [ { tipo: 'retrato', x: 0.5, escala: 1 } ],
	},
	{
		archivo: 'retrato-natural.svg',
		w: 1000, h: 1250,
		cielo: [ '#101512', '#22392c', '#5f8a5c' ],
		suelo: '#0a0d0b',
		sol: { x: 0.66, y: 0.3, r: 0.12, color: '#e8ffcf' },
		figuras: [ { tipo: 'retrato', x: 0.46, escala: .95 } ],
	},
	{
		archivo: 'servicio-bodas.svg',
		w: 1200, h: 900,
		cielo: [ '#1b1310', '#5c3018', '#d17a34' ],
		suelo: '#0d0a08',
		sol: { x: 0.72, y: 0.42, r: 0.09, color: '#ffc48a' },
		figuras: [ { tipo: 'pareja', x: 0.38, escala: .95 } ],
	},
	{
		archivo: 'servicio-eventos.svg',
		w: 1200, h: 900,
		cielo: [ '#0b0e14', '#182740', '#39618f' ],
		suelo: '#080a0d',
		sol: { x: 0.24, y: 0.32, r: 0.08, color: '#d7e9ff' },
		figuras: [ { tipo: 'multitud', x: 0.55, escala: 1 } ],
	},
	{
		archivo: 'servicio-marca.svg',
		w: 1200, h: 900,
		cielo: [ '#141210', '#332a22', '#8d6c48' ],
		suelo: '#0b0a09',
		sol: { x: 0.68, y: 0.34, r: 0.14, color: '#ffe6c2' },
		figuras: [ { tipo: 'retrato', x: 0.42, escala: .9 } ],
	},
	{
		archivo: 'retrato-angie.svg',
		w: 1000, h: 1250,
		cielo: [ '#121212', '#2c211a', '#8a5228' ],
		suelo: '#0a0a0a',
		sol: { x: 0.62, y: 0.28, r: 0.15, color: '#ffd6a8' },
		figuras: [ { tipo: 'fotografa', x: 0.48, escala: 1 } ],
	},
];

/**
 * Silueta de una persona de pie.
 *
 * @param {number} cx    Centro horizontal en píxeles.
 * @param {number} suelo Línea de horizonte en píxeles.
 * @param {number} alto  Altura de la figura.
 * @param {string} color Color del relleno.
 * @return {string}
 */
function persona( cx, suelo, alto, color ) {
	const cabeza = alto * 0.16;
	const cuerpo = alto - cabeza;
	const ancho = alto * 0.3;

	return `<g fill="${ color }">
		<circle cx="${ cx.toFixed( 1 ) }" cy="${ ( suelo - cuerpo - cabeza * 0.5 ).toFixed( 1 ) }" r="${ ( cabeza * 0.5 ).toFixed( 1 ) }"/>
		<path d="M${ ( cx - ancho / 2 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) }
			C${ ( cx - ancho / 2 ).toFixed( 1 ) } ${ ( suelo - cuerpo * 0.72 ).toFixed( 1 ) }
			 ${ ( cx - ancho * 0.42 ).toFixed( 1 ) } ${ ( suelo - cuerpo ).toFixed( 1 ) }
			 ${ cx.toFixed( 1 ) } ${ ( suelo - cuerpo ).toFixed( 1 ) }
			C${ ( cx + ancho * 0.42 ).toFixed( 1 ) } ${ ( suelo - cuerpo ).toFixed( 1 ) }
			 ${ ( cx + ancho / 2 ).toFixed( 1 ) } ${ ( suelo - cuerpo * 0.72 ).toFixed( 1 ) }
			 ${ ( cx + ancho / 2 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) } Z"/>
	</g>`;
}

/**
 * Compone el grupo de figuras de una escena.
 *
 * @param {Object} figura Definición de la figura.
 * @param {number} ancho  Ancho del lienzo.
 * @param {number} suelo  Línea de horizonte.
 * @param {number} alto   Alto del lienzo.
 * @return {string}
 */
function dibujarFigura( figura, ancho, suelo, alto ) {
	const cx = ancho * figura.x;
	const base = alto * 0.46 * figura.escala;
	const tinta = '#050505';

	switch ( figura.tipo ) {
		case 'pareja':
			return persona( cx - base * 0.17, suelo, base, tinta ) +
				persona( cx + base * 0.17, suelo, base * 0.94, tinta );

		case 'multitud': {
			let salida = '';
			const alturas = [ 0.74, 0.9, 1, 0.86, 0.7, 0.8 ];

			alturas.forEach( ( factor, indice ) => {
				const desplazamiento = ( indice - ( alturas.length - 1 ) / 2 ) * base * 0.26;
				salida += persona( cx + desplazamiento, suelo, base * 0.8 * factor, tinta );
			} );

			return salida;
		}

		case 'retrato': {
			// Un busto: cabeza y hombros, con el cuerpo cortado por el borde
			// inferior. La cabeza va a un tercio del alto del busto, que es la
			// proporción que hace que se lea como un retrato y no como una bola.
			const r = base * 0.26;
			const cy = suelo - base * 0.78;

			return `<g fill="${ tinta }">
				<circle cx="${ cx.toFixed( 1 ) }" cy="${ cy.toFixed( 1 ) }" r="${ r.toFixed( 1 ) }"/>
				<path d="M${ ( cx - r * 2 ).toFixed( 1 ) } ${ alto }
					C${ ( cx - r * 1.9 ).toFixed( 1 ) } ${ ( cy + r * 1.1 ).toFixed( 1 ) }
					 ${ ( cx - r * 0.9 ).toFixed( 1 ) } ${ ( cy + r * 0.72 ).toFixed( 1 ) }
					 ${ cx.toFixed( 1 ) } ${ ( cy + r * 0.72 ).toFixed( 1 ) }
					C${ ( cx + r * 0.9 ).toFixed( 1 ) } ${ ( cy + r * 0.72 ).toFixed( 1 ) }
					 ${ ( cx + r * 1.9 ).toFixed( 1 ) } ${ ( cy + r * 1.1 ).toFixed( 1 ) }
					 ${ ( cx + r * 2 ).toFixed( 1 ) } ${ alto } Z"/>
			</g>`;
		}

		case 'fotografa': {
			const r = base * 0.24;
			const cy = suelo - base * 0.8;

			return `<g fill="${ tinta }">
				<circle cx="${ cx.toFixed( 1 ) }" cy="${ cy.toFixed( 1 ) }" r="${ r.toFixed( 1 ) }"/>
				<path d="M${ ( cx - r * 1.9 ).toFixed( 1 ) } ${ alto }
					C${ ( cx - r * 1.8 ).toFixed( 1 ) } ${ ( cy + r * 1.1 ).toFixed( 1 ) }
					 ${ ( cx - r * 0.9 ).toFixed( 1 ) } ${ ( cy + r * 0.75 ).toFixed( 1 ) }
					 ${ cx.toFixed( 1 ) } ${ ( cy + r * 0.75 ).toFixed( 1 ) }
					C${ ( cx + r * 0.9 ).toFixed( 1 ) } ${ ( cy + r * 0.75 ).toFixed( 1 ) }
					 ${ ( cx + r * 1.8 ).toFixed( 1 ) } ${ ( cy + r * 1.1 ).toFixed( 1 ) }
					 ${ ( cx + r * 1.9 ).toFixed( 1 ) } ${ alto } Z"/>
				<rect x="${ ( cx - r * 0.55 ).toFixed( 1 ) }" y="${ ( cy - r * 0.16 ).toFixed( 1 ) }" width="${ ( r * 1.1 ).toFixed( 1 ) }" height="${ ( r * 0.72 ).toFixed( 1 ) }" rx="${ ( r * 0.12 ).toFixed( 1 ) }" fill="${ NARANJA }"/>
				<circle cx="${ cx.toFixed( 1 ) }" cy="${ ( cy + r * 0.2 ).toFixed( 1 ) }" r="${ ( r * 0.24 ).toFixed( 1 ) }" fill="${ tinta }"/>
			</g>`;
		}

		case 'ramo': {
			// Un ramo visto de cerca: tallos y manchas de flor.
			const cy = suelo - base * 0.3;
			let flores = '';

			for ( let i = 0; i < 9; i++ ) {
				const angulo = ( i / 9 ) * Math.PI * 2;
				const rx = cx + Math.cos( angulo ) * base * 0.3;
				const ry = cy + Math.sin( angulo ) * base * 0.22;
				const color = i % 3 === 0 ? NARANJA : '#f0e2d2';

				flores += `<circle cx="${ rx.toFixed( 1 ) }" cy="${ ry.toFixed( 1 ) }" r="${ ( base * 0.11 ).toFixed( 1 ) }" fill="${ color }" opacity="${ i % 2 ? '.9' : '.7' }"/>`;
			}

			return `<g>
				<path d="M${ ( cx - base * 0.05 ).toFixed( 1 ) } ${ cy.toFixed( 1 ) } L${ ( cx - base * 0.12 ).toFixed( 1 ) } ${ alto } M${ ( cx + base * 0.05 ).toFixed( 1 ) } ${ cy.toFixed( 1 ) } L${ ( cx + base * 0.14 ).toFixed( 1 ) } ${ alto }" stroke="#1d1a15" stroke-width="${ ( base * 0.05 ).toFixed( 1 ) }" fill="none"/>
				${ flores }
			</g>`;
		}

		default:
			return persona( cx, suelo, base, tinta );
	}
}

/**
 * Construye el SVG completo de una escena.
 *
 * @param {Object} escena Definición.
 * @return {string}
 */
function construir( escena ) {
	const { w, h } = escena;
	const suelo = h * 0.82;
	const id = escena.archivo.replace( '.svg', '' );

	const figuras = ( escena.figuras || [] )
		.map( ( figura ) => dibujarFigura( figura, w, suelo, h ) )
		.join( '' );

	const aves = escena.aves
		? [ [ 0.16, 0.18 ], [ 0.22, 0.13 ], [ 0.27, 0.2 ] ]
			.map( ( [ x, y ] ) => {
				const px = w * x;
				const py = h * y;
				const s = w * 0.012;

				return `<path d="M${ ( px - s ).toFixed( 1 ) } ${ py.toFixed( 1 )}q${ s.toFixed( 1 ) } ${ ( -s * 0.7 ).toFixed( 1 ) } ${ s.toFixed( 1 ) } 0q0 ${ ( -s * 0.7 ).toFixed( 1 ) } ${ s.toFixed( 1 ) } 0" fill="none" stroke="#0a0a0a" stroke-width="${ ( s * 0.22 ).toFixed( 2 ) }" opacity=".5"/>`;
			} )
			.join( '' )
		: '';

	const focos = escena.focos
		? `<g opacity=".22">
			<path d="M${ ( w * 0.3 ).toFixed( 1 ) } 0 L${ ( w * 0.12 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) } L${ ( w * 0.4 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) } Z" fill="#ffd9f2"/>
			<path d="M${ ( w * 0.7 ).toFixed( 1 ) } 0 L${ ( w * 0.6 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) } L${ ( w * 0.92 ).toFixed( 1 ) } ${ suelo.toFixed( 1 ) } Z" fill="#cfe0ff"/>
		</g>`
		: '';

	return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${ w } ${ h }" width="${ w }" height="${ h }" role="img">
	<defs>
		<linearGradient id="cielo-${ id }" x1="0" y1="0" x2="0" y2="1">
			<stop offset="0" stop-color="${ escena.cielo[ 0 ] }"/>
			<stop offset=".55" stop-color="${ escena.cielo[ 1 ] }"/>
			<stop offset="1" stop-color="${ escena.cielo[ 2 ] }"/>
		</linearGradient>
		<radialGradient id="sol-${ id }" cx="50%" cy="50%" r="50%">
			<stop offset="0" stop-color="${ escena.sol.color }" stop-opacity=".95"/>
			<stop offset="1" stop-color="${ escena.sol.color }" stop-opacity="0"/>
		</radialGradient>
		<linearGradient id="velo-${ id }" x1="0" y1="0" x2="0" y2="1">
			<stop offset="0" stop-color="#000" stop-opacity=".45"/>
			<stop offset=".45" stop-color="#000" stop-opacity="0"/>
			<stop offset="1" stop-color="#000" stop-opacity=".5"/>
		</linearGradient>
		<filter id="grano-${ id }">
			<feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2"/>
			<feColorMatrix type="saturate" values="0"/>
		</filter>
	</defs>

	<rect width="${ w }" height="${ h }" fill="url(#cielo-${ id })"/>
	<circle cx="${ ( w * escena.sol.x ).toFixed( 1 ) }" cy="${ ( h * escena.sol.y ).toFixed( 1 ) }" r="${ ( w * escena.sol.r * 2.6 ).toFixed( 1 ) }" fill="url(#sol-${ id })"/>
	<circle cx="${ ( w * escena.sol.x ).toFixed( 1 ) }" cy="${ ( h * escena.sol.y ).toFixed( 1 ) }" r="${ ( w * escena.sol.r * 0.42 ).toFixed( 1 ) }" fill="${ escena.sol.color }" opacity=".85"/>
	${ focos }
	${ aves }
	<rect y="${ suelo.toFixed( 1 ) }" width="${ w }" height="${ ( h - suelo ).toFixed( 1 ) }" fill="${ escena.suelo }"/>
	${ figuras }
	<rect width="${ w }" height="${ h }" fill="url(#velo-${ id })"/>
	<rect width="${ w }" height="${ h }" filter="url(#grano-${ id })" opacity=".05"/>
</svg>
`;
}

mkdirSync( DESTINO, { recursive: true } );

let total = 0;

for ( const escena of ESCENAS ) {
	writeFileSync( join( DESTINO, escena.archivo ), construir( escena ), 'utf8' );
	total++;
}

console.log( `${ total } imágenes generadas en acg-visual/demo/images/` );
