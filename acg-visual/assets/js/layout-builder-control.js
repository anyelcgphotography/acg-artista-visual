/**
 * Constructor visual del control «Diseñador de cabecera/pie».
 *
 * Vive solo dentro del panel de controles del Personalizador (nunca en el
 * front del sitio, ni siquiera en su iframe de previsualización): pinta la
 * paleta de elementos y las columnas de cada fila, y sincroniza el
 * arrastrar-y-soltar con el ajuste real a través de wp.customize, sin
 * ninguna librería externa (mismo criterio que el efecto gooey del front).
 *
 * El ajuste se guarda como JSON en texto plano; este archivo es el único que
 * lo escribe desde el admin, y inc/layout-builder.php es el único que lo lee
 * y lo sanea al guardar.
 */
( function ( api, $ ) {
	'use strict';

	/**
	 * Genera un id corto y único para un elemento recién colocado.
	 *
	 * @return {string}
	 */
	function nuevoId() {
		return 'el-' + Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2, 7 );
	}

	/**
	 * Decodifica el JSON del ajuste con una salida segura si viene vacío o
	 * corrupto: mejor un constructor sin filas que un error de JS a medias.
	 *
	 * @param {string} raw Valor crudo del ajuste.
	 * @return {Object}
	 */
	function parsear( raw ) {
		try {
			var datos = JSON.parse( raw );
			return ( datos && 'object' === typeof datos ) ? datos : {};
		} catch ( error ) {
			return {};
		}
	}

	/**
	 * Constructor de una instancia del panel (una por control: cabecera o pie).
	 *
	 * @param {wp.customize.Control} control  Control de Customizer ya listo.
	 * @param {string}               contexto 'header' o 'footer'.
	 */
	function ACGLayoutBuilder( control, contexto ) {
		this.control = control;
		this.contexto = contexto;
		this.registry = window.acgLayoutBuilder.registry;
		this.rows = window.acgLayoutBuilder.rows[ contexto ];
		this.zones = window.acgLayoutBuilder.zones;
		this.i18n = window.acgLayoutBuilder.i18n;
		this.root = control.container.find( '.acg-layout-builder' );
		this.datos = parsear( control.setting() );
		this.arrastre = null;

		this.pintar();

		var self = this;
		control.setting.bind( function ( a ) {
			var recibido = parsear( a );
			// Si el cambio viene de fuera (por ejemplo al restablecer el
			// ajuste), se repinta; si lo acabamos de escribir nosotros mismos
			// desde confirmar(), los datos ya coinciden y no hace falta.
			if ( JSON.stringify( recibido ) !== JSON.stringify( self.datos ) ) {
				self.datos = recibido;
				self.pintar();
			}
		} );
	}

	ACGLayoutBuilder.prototype.confirmar = function () {
		this.control.setting.set( JSON.stringify( this.datos ) );
	};

	/**
	 * Se asegura de que exista el array fila→columna antes de leer o escribir
	 * en él, para no repetir comprobaciones en cada método.
	 *
	 * @param {string} fila
	 * @param {string} zona
	 * @return {Array}
	 */
	ACGLayoutBuilder.prototype.zonaDatos = function ( fila, zona ) {
		this.datos[ fila ] = this.datos[ fila ] || {};
		this.datos[ fila ][ zona ] = this.datos[ fila ][ zona ] || [];
		return this.datos[ fila ][ zona ];
	};

	/**
	 * Cuenta cuántas veces se usa cada tipo en todo el constructor, para
	 * saber qué queda disponible en la paleta.
	 *
	 * @return {Object<string,number>}
	 */
	ACGLayoutBuilder.prototype.usados = function () {
		var conteo = {};
		var datos = this.datos;

		Object.keys( datos ).forEach( function ( fila ) {
			Object.keys( datos[ fila ] || {} ).forEach( function ( zona ) {
				( datos[ fila ][ zona ] || [] ).forEach( function ( item ) {
					conteo[ item.type ] = ( conteo[ item.type ] || 0 ) + 1;
				} );
			} );
		} );

		return conteo;
	};

	/**
	 * Tipos que se pueden seguir arrastrando desde la paleta: los que admiten
	 * el contexto actual y, si no se pueden repetir, los que aún no se han
	 * colocado en ninguna columna.
	 *
	 * @return {string[]}
	 */
	ACGLayoutBuilder.prototype.disponibles = function () {
		var registry = this.registry;
		var contexto = this.contexto;
		var usados = this.usados();

		return Object.keys( registry ).filter( function ( tipo ) {
			var def = registry[ tipo ];
			if ( -1 === def.contexts.indexOf( contexto ) ) {
				return false;
			}
			return def.allow_multiple || ! usados[ tipo ];
		} );
	};

	ACGLayoutBuilder.prototype.pintar = function () {
		var self = this;
		this.root.empty();

		// --- Paleta de elementos disponibles ---
		var paleta = $( '<div class="acg-lb-palette"></div>' );
		paleta.append( $( '<p class="acg-lb-palette__title"></p>' ).text( this.i18n.available ) );
		var listaPaleta = $( '<ul class="acg-lb-palette__list"></ul>' );

		this.disponibles().forEach( function ( tipo ) {
			var chip = $( '<li class="acg-lb-chip acg-lb-chip--source" draggable="true"></li>' )
				.attr( 'data-type', tipo )
				.text( self.registry[ tipo ].label );

			chip.on( 'dragstart', function ( evento ) {
				self.arrastre = { origen: 'paleta', type: tipo };
				evento.originalEvent.dataTransfer.effectAllowed = 'copy';
			} );
			chip.on( 'dragend', function () {
				self.root.find( '.is-target' ).removeClass( 'is-target' );
			} );

			listaPaleta.append( chip );
		} );

		if ( ! listaPaleta.children().length ) {
			listaPaleta.append( $( '<li class="acg-lb-palette__vacia"></li>' ).text( this.i18n.allUsed ) );
		}

		paleta.append( listaPaleta );
		this.root.append( paleta );

		// --- Filas y columnas ---
		var filas = $( '<div class="acg-lb-rows"></div>' );

		Object.keys( this.rows ).forEach( function ( filaId ) {
			var fila = $( '<div class="acg-lb-row"></div>' );
			fila.append( $( '<p class="acg-lb-row__title"></p>' ).text( self.rows[ filaId ] ) );

			var zonasWrap = $( '<div class="acg-lb-row__zones"></div>' );

			Object.keys( self.zones ).forEach( function ( zonaId ) {
				var zona = $( '<div class="acg-lb-zone"></div>' );
				zona.append( $( '<p class="acg-lb-zone__title"></p>' ).text( self.zones[ zonaId ] ) );

				var itemsEl = $( '<ul class="acg-lb-zone__items"></ul>' );
				var items = self.zonaDatos( filaId, zonaId );

				if ( ! items.length ) {
					itemsEl.addClass( 'is-empty' ).attr( 'data-placeholder', self.i18n.empty );
				}

				items.forEach( function ( item, indice ) {
					itemsEl.append( self.pintarChip( item, filaId, zonaId, indice ) );
				} );

				self.enlazarZona( itemsEl, filaId, zonaId );

				zona.append( itemsEl );
				zonasWrap.append( zona );
			} );

			fila.append( zonasWrap );
			filas.append( fila );
		} );

		this.root.append( filas );
	};

	/**
	 * Pinta un elemento ya colocado en una columna.
	 *
	 * @param {Object} item   Elemento {id, type, params}.
	 * @param {string} fila
	 * @param {string} zona
	 * @param {number} indice Posición dentro de la columna.
	 * @return {jQuery}
	 */
	ACGLayoutBuilder.prototype.pintarChip = function ( item, fila, zona, indice ) {
		var self = this;
		var def = this.registry[ item.type ] || { label: item.type };

		var chip = $( '<li class="acg-lb-chip acg-lb-chip--placed" draggable="true"></li>' )
			.attr( { 'data-id': item.id, 'data-index': indice } );

		var cabecera = $( '<div class="acg-lb-chip__head"></div>' );
		cabecera.append( $( '<span class="acg-lb-chip__label"></span>' ).text( def.label ) );

		var quitar = $( '<button type="button" class="acg-lb-chip__remove"></button>' )
			.attr( 'aria-label', this.i18n.remove )
			.html( '&times;' );
		quitar.on( 'click', function ( evento ) {
			evento.stopPropagation();
			self.quitar( fila, zona, indice );
		} );
		cabecera.append( quitar );
		chip.append( cabecera );

		if ( def.has_text ) {
			var campo = $( '<textarea class="acg-lb-chip__text" rows="2"></textarea>' )
				.attr( 'placeholder', this.i18n.textPlaceholder )
				.val( item.params && item.params.text ? item.params.text : '' );

			campo.on( 'input', function () {
				item.params = item.params || {};
				item.params.text = campo.val();
				self.confirmar();
			} );
			// Sin esto, empezar a seleccionar texto en el campo arrastraría
			// el chip entero en vez de mover el cursor.
			campo.on( 'mousedown dragstart', function ( evento ) {
				evento.stopPropagation();
			} );

			chip.append( campo );
		}

		chip.on( 'dragstart', function ( evento ) {
			self.arrastre = { origen: 'zona', fila: fila, zona: zona, indice: indice };
			evento.originalEvent.dataTransfer.effectAllowed = 'move';
			evento.originalEvent.stopPropagation();
		} );
		chip.on( 'dragend', function () {
			self.root.find( '.is-target' ).removeClass( 'is-target' );
		} );

		return chip;
	};

	/**
	 * Activa una columna como zona donde soltar elementos.
	 *
	 * @param {jQuery} itemsEl Lista de la columna.
	 * @param {string} fila
	 * @param {string} zona
	 * @return {void}
	 */
	ACGLayoutBuilder.prototype.enlazarZona = function ( itemsEl, fila, zona ) {
		var self = this;

		itemsEl.on( 'dragover', function ( evento ) {
			evento.preventDefault();
			evento.originalEvent.dataTransfer.dropEffect =
				self.arrastre && 'paleta' === self.arrastre.origen ? 'copy' : 'move';
			itemsEl.addClass( 'is-target' );
		} );

		itemsEl.on( 'dragleave', function () {
			itemsEl.removeClass( 'is-target' );
		} );

		itemsEl.on( 'drop', function ( evento ) {
			evento.preventDefault();
			itemsEl.removeClass( 'is-target' );

			if ( ! self.arrastre ) {
				return;
			}

			var indiceDestino = self.indiceSuelta( itemsEl, evento.originalEvent.clientY );

			if ( 'paleta' === self.arrastre.origen ) {
				self.insertar( fila, zona, indiceDestino, { id: nuevoId(), type: self.arrastre.type, params: {} } );
			} else {
				self.mover( self.arrastre, fila, zona, indiceDestino );
			}

			self.arrastre = null;
		} );
	};

	/**
	 * Calcula en qué posición de la lista cae el punto donde se soltó,
	 * comparando con el punto medio de cada chip ya pintado.
	 *
	 * @param {jQuery} itemsEl
	 * @param {number} clientY
	 * @return {number}
	 */
	ACGLayoutBuilder.prototype.indiceSuelta = function ( itemsEl, clientY ) {
		var indice = 0;

		itemsEl.children( '.acg-lb-chip' ).each( function () {
			var rect = this.getBoundingClientRect();
			if ( clientY > rect.top + rect.height / 2 ) {
				indice++;
			}
		} );

		return indice;
	};

	ACGLayoutBuilder.prototype.insertar = function ( fila, zona, indice, item ) {
		this.zonaDatos( fila, zona ).splice( indice, 0, item );
		this.confirmar();
		this.pintar();
	};

	ACGLayoutBuilder.prototype.quitar = function ( fila, zona, indice ) {
		this.zonaDatos( fila, zona ).splice( indice, 1 );
		this.confirmar();
		this.pintar();
	};

	ACGLayoutBuilder.prototype.mover = function ( origen, fila, zona, indice ) {
		var listaOrigen = this.zonaDatos( origen.fila, origen.zona );
		var item = listaOrigen[ origen.indice ];

		if ( ! item ) {
			return;
		}

		listaOrigen.splice( origen.indice, 1 );

		// Si el elemento se mueve dentro de la misma columna, al quitarlo el
		// índice de destino puede haberse corrido un puesto.
		if ( origen.fila === fila && origen.zona === zona && origen.indice < indice ) {
			indice--;
		}

		this.zonaDatos( fila, zona ).splice( indice, 0, item );
		this.confirmar();
		this.pintar();
	};

	api.bind( 'ready', function () {
		[ 'header', 'footer' ].forEach( function ( contexto ) {
			api.control( 'acg_' + contexto + '_layout', function ( control ) {
				new ACGLayoutBuilder( control, contexto );
			} );
		} );
	} );
} )( wp.customize, jQuery );
