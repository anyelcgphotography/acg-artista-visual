/**
 * Vista previa en vivo del Personalizador.
 *
 * Solo el nombre y la descripción del sitio se actualizan sin recargar; el
 * resto de ajustes (colores, esquemas de sección, disposición de cabecera y
 * pie) usan transporte `refresh`, porque replicar en JS la lógica de la
 * paleta y del constructor sería duplicar código que ya vive en PHP — y con
 * el riesgo de que las dos versiones se separen.
 */
( function ( api ) {
	'use strict';

	api( 'blogname', function ( valor ) {
		valor.bind( function ( nuevo ) {
			document.querySelectorAll( '.acg-marca__nombre' ).forEach( function ( nodo ) {
				nodo.textContent = nuevo;
			} );
		} );
	} );

	api( 'blogdescription', function ( valor ) {
		valor.bind( function ( nuevo ) {
			document.querySelectorAll( '.acg-pie__tagline' ).forEach( function ( nodo ) {
				nodo.textContent = nuevo;
			} );
		} );
	} );
} )( wp.customize );
