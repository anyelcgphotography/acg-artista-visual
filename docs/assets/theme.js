/**
 * ACG Artista Visual — comportamiento del front.
 *
 * Sin dependencias: son cinco piezas pequeñas (menú móvil, filtros del
 * portafolio, revelado al hacer scroll, lightbox y formulario) y ninguna
 * justifica cargar una librería.
 *
 * Principio general: todo lo que hay aquí es mejora progresiva. Si este
 * archivo no llega a ejecutarse, la web sigue siendo navegable — los filtros
 * simplemente no aparecen, las fotos abren su ficha y el formulario se envía
 * por WhatsApp.
 */
( function () {
	'use strict';

	var datos = window.acgData || {};
	var i18n = datos.i18n || {};

	/**
	 * Atajo para consultar el DOM.
	 *
	 * @param {string}      selector Selector CSS.
	 * @param {Element|Document} raiz Nodo donde buscar.
	 * @return {Element[]}
	 */
	function todos( selector, raiz ) {
		return Array.prototype.slice.call( ( raiz || document ).querySelectorAll( selector ) );
	}

	/* ------------------------------------------------------------ Menú móvil. */

	function menuMovil() {
		var boton = document.querySelector( '.acg-burger' );
		var panel = document.getElementById( 'acg-menu-movil' );

		if ( ! boton || ! panel ) {
			return;
		}

		function abrir( abierto ) {
			boton.setAttribute( 'aria-expanded', abierto ? 'true' : 'false' );
			panel.hidden = ! abierto;
			// Bloquear el scroll del fondo mientras el panel está abierto evita
			// el efecto de «dos scrolls» en iOS.
			document.documentElement.style.overflow = abierto ? 'hidden' : '';
		}

		boton.addEventListener( 'click', function () {
			abrir( 'true' !== boton.getAttribute( 'aria-expanded' ) );
		} );

		panel.addEventListener( 'click', function ( evento ) {
			if ( evento.target.closest( 'a' ) ) {
				abrir( false );
			}
		} );

		document.addEventListener( 'keydown', function ( evento ) {
			if ( 'Escape' === evento.key && ! panel.hidden ) {
				abrir( false );
				boton.focus();
			}
		} );

		// Al volver a escritorio el panel deja de tener sentido: si se queda
		// abierto, tapa la página con el menú de móvil.
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth > 900 && ! panel.hidden ) {
				abrir( false );
			}
		} );
	}

	/* ------------------------------------------------ Filtros del portafolio. */

	function filtrosPortafolio() {
		var barra = document.querySelector( '[data-acg-filtros]' );
		var mosaico = document.querySelector( '[data-acg-mosaico]' );

		if ( ! barra || ! mosaico ) {
			return;
		}

		var vacio = document.querySelector( '[data-acg-vacio]' );
		var fichas = todos( '.acg-tarjeta-foto', mosaico );

		// La barra nace oculta en el HTML y se muestra aquí: si el JS falla,
		// nadie ve unos filtros que no filtrarían nada.
		barra.hidden = false;

		barra.addEventListener( 'click', function ( evento ) {
			var boton = evento.target.closest( '[data-filtro]' );

			if ( ! boton ) {
				return;
			}

			var filtro = boton.getAttribute( 'data-filtro' );
			var visibles = 0;

			todos( '[data-filtro]', barra ).forEach( function ( otro ) {
				otro.classList.toggle( 'is-active', otro === boton );
			} );

			fichas.forEach( function ( ficha ) {
				var categorias = ( ficha.getAttribute( 'data-categorias' ) || '' ).split( ' ' );
				var mostrar = 'all' === filtro || categorias.indexOf( filtro ) !== -1;

				ficha.hidden = ! mostrar;

				if ( mostrar ) {
					visibles++;
				}
			} );

			if ( vacio ) {
				vacio.hidden = visibles > 0;
			}
		} );
	}

	/* ------------------------------------------------- Revelado al hacer scroll. */

	function revelado() {
		if ( false === datos.animaciones ) {
			return;
		}

		var candidatos = todos( '.acg-seccion__cabecera, .acg-tarjeta-foto, .acg-servicio, .acg-paso, .acg-cita, .acg-sobre__media, .acg-sobre__texto, .acg-encargos, .acg-form' );

		if ( ! candidatos.length || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		candidatos.forEach( function ( nodo ) {
			nodo.classList.add( 'acg-anima' );
		} );

		var observador = new IntersectionObserver( function ( entradas ) {
			entradas.forEach( function ( entrada ) {
				if ( ! entrada.isIntersecting ) {
					return;
				}

				entrada.target.classList.add( 'is-visible' );
				observador.unobserve( entrada.target );
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );

		candidatos.forEach( function ( nodo ) {
			observador.observe( nodo );
		} );
	}

	/* -------------------------------------------------------------- Lightbox. */

	function lightbox() {
		if ( false === datos.lightbox ) {
			return;
		}

		var capa = null;

		function cerrar() {
			if ( capa ) {
				capa.hidden = true;
				document.documentElement.style.overflow = '';
			}
		}

		function abrir( src, alt ) {
			if ( ! capa ) {
				capa = document.createElement( 'div' );
				capa.className = 'acg-lightbox';
				capa.innerHTML = '<button type="button" class="acg-lightbox__cerrar" aria-label="' +
					( i18n.cerrar || 'Cerrar' ) +
					'">&times;</button><img alt="">';
				document.body.appendChild( capa );

				capa.addEventListener( 'click', function ( evento ) {
					// Cerrar al pinchar fuera de la foto o en el aspa, pero no
					// sobre la propia imagen.
					if ( 'IMG' !== evento.target.tagName ) {
						cerrar();
					}
				} );
			}

			var imagen = capa.querySelector( 'img' );
			imagen.src = src;
			imagen.alt = alt || '';
			capa.hidden = false;
			document.documentElement.style.overflow = 'hidden';
			capa.querySelector( '.acg-lightbox__cerrar' ).focus();
		}

		document.addEventListener( 'keydown', function ( evento ) {
			if ( 'Escape' === evento.key ) {
				cerrar();
			}
		} );

		// Solo en el mosaico de la portada: en el archivo y en las fichas, el
		// clic tiene que seguir llevando al trabajo completo.
		todos( '[data-acg-mosaico] .acg-tarjeta-foto__enlace' ).forEach( function ( enlace ) {
			enlace.addEventListener( 'click', function ( evento ) {
				var imagen = enlace.querySelector( 'img' );

				if ( ! imagen ) {
					return;
				}

				evento.preventDefault();
				abrir( imagen.currentSrc || imagen.src, imagen.alt );
			} );
		} );
	}

	/* ------------------------------------------------------------ Formulario. */

	function formularios() {
		todos( '[data-acg-form]' ).forEach( function ( form ) {
			var aviso = form.querySelector( '[data-acg-aviso]' );
			var boton = form.querySelector( 'button[type="submit"]' );

			/**
			 * Compone el mensaje de WhatsApp con lo que haya escrito el
			 * visitante. Es el plan B cuando el CRM no está activo, y también
			 * la red de seguridad si la petición AJAX falla: el contacto no se
			 * pierde por un error de servidor.
			 *
			 * @return {string}
			 */
			function textoWhatsapp() {
				var partes = [];
				var campos = {
					nombre: 'Nombre',
					email: 'Email',
					telefono: 'Teléfono',
					servicio: 'Servicio',
					fecha: 'Fecha',
					mensaje: 'Mensaje'
				};

				Object.keys( campos ).forEach( function ( clave ) {
					var campo = form.elements[ clave ];

					if ( campo && campo.value ) {
						partes.push( campos[ clave ] + ': ' + campo.value );
					}
				} );

				return partes.join( '\n' );
			}

			function irAWhatsapp() {
				var base = form.getAttribute( 'data-whatsapp' );

				if ( ! base ) {
					return false;
				}

				var separador = base.indexOf( '?' ) === -1 ? '?' : '&';
				window.open( base + separador + 'text=' + encodeURIComponent( textoWhatsapp() ), '_blank', 'noopener' );

				return true;
			}

			function mensaje( texto, clase ) {
				if ( ! aviso ) {
					return;
				}

				aviso.textContent = texto;
				aviso.className = 'acg-form__aviso ' + ( clase || '' );
			}

			form.addEventListener( 'submit', function ( evento ) {
				evento.preventDefault();

				if ( ! form.checkValidity() ) {
					form.reportValidity();
					return;
				}

				if ( 'crm' !== form.getAttribute( 'data-modo' ) ) {
					// Este es el único camino que existe cuando no hay backend
					// detrás del formulario: una página estática (por ejemplo
					// publicada en GitHub Pages) o el theme sin el plugin del
					// CRM. Todo pasa en el navegador, sin llamada a servidor,
					// así que hace falta avisar aquí mismo de si funcionó.
					if ( irAWhatsapp() ) {
						mensaje( i18n.waAbierto || 'Se ha abierto WhatsApp con tu mensaje.', 'is-ok' );
					} else {
						mensaje( i18n.waSinNumero || 'No hay un número de WhatsApp configurado.', 'is-error' );
					}
					return;
				}

				// `set` y no `append`: el formulario ya trae estos dos campos
				// para poder enviarse sin JavaScript, y duplicarlos sería
				// mandar dos valores con la misma clave.
				var cuerpo = new FormData( form );
				cuerpo.set( 'action', 'acg_submit_lead' );
				cuerpo.set( 'nonce', datos.nonce || '' );

				boton.disabled = true;
				boton.textContent = i18n.enviando || 'Enviando…';
				mensaje( '' );

				window.fetch( datos.ajaxUrl, { method: 'POST', body: cuerpo, credentials: 'same-origin' } )
					.then( function ( respuesta ) {
						return respuesta.json();
					} )
					.then( function ( respuesta ) {
						if ( respuesta && respuesta.success ) {
							form.reset();
							mensaje( ( respuesta.data && respuesta.data.message ) || i18n.ok, 'is-ok' );
							return;
						}

						mensaje( ( respuesta && respuesta.data && respuesta.data.message ) || i18n.error, 'is-error' );
					} )
					.catch( function () {
						mensaje( i18n.error, 'is-error' );
						irAWhatsapp();
					} )
					.then( function () {
						boton.disabled = false;
						boton.textContent = i18n.enviar || 'Enviar';
					} );
			} );
		} );
	}

	/* ------------------------------------------------------------- Arranque. */

	function iniciar() {
		menuMovil();
		filtrosPortafolio();
		revelado();
		lightbox();
		formularios();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', iniciar );
	} else {
		iniciar();
	}
} )();
