# ACG Artista Visual — theme + plugin para WordPress

Web de Anyel C. González, fotógrafa y artista visual: portafolio filtrable,
servicios, proceso, testimonios y captación de clientes desde el formulario de
contacto. Bilingüe español / portugués sin plugins de traducción.

```
Angie/
├── acg-visual/        Theme
├── acg-crm/           Plugin (solicitudes y clientes)
├── _preview/          Maqueta HTML para revisar el diseño sin WordPress
├── _tools/            Utilidades de construcción
├── _dist/             ZIP listos para instalar
└── v1.0/              Maqueta original de la que sale todo
```

---

## Instalación

El orden importa: el theme espera encontrar el CRM para el formulario.

1. **Advanced Custom Fields (gratuito)** — Plugins → Añadir nuevo → buscar
   «Advanced Custom Fields» → Instalar y activar.
2. **ACG CRM** — Plugins → Añadir nuevo → Subir plugin → `_dist/acg-crm.zip` →
   Activar.
3. **Theme ACG Artista Visual** — Apariencia → Temas → Añadir nuevo → Subir
   tema → `_dist/acg-visual.zip` → Activar.
4. **Contenido demo** — Apariencia → Contenido demo → *Importar contenido demo*.

Con el paso 4 quedan creados el portafolio, los servicios, el proceso, los
testimonios, el equipo, las preguntas frecuentes, las páginas legales, los
menús y la portada con todos sus textos en los dos idiomas.

> El theme funciona sin ACF: los mismos campos aparecen como cajas normales al
> editar cada página. Con ACF la edición es bastante más cómoda (selector
> visual de imágenes, editor enriquecido, campos agrupados).

---

## Qué encontrarás en el panel

| Menú | Qué es |
|---|---|
| **CRM → Solicitudes** | Cada contacto del formulario, con estado, historial y exportación a CSV |
| **CRM → Ajustes** | A qué correo llegan las solicitudes y si se manda acuse de recibo |
| **Portafolio** | Los trabajos, con categorías y formato en el mosaico |
| **Servicios** | Las coberturas; las tres marcadas como destacadas salen en la portada |
| **Proceso**, **Testimonios**, **Equipo de trabajo**, **Preguntas frecuentes** | Contenido repetible de la portada |
| **Páginas → Inicio** | Todos los textos de la portada, sección por sección |
| **Escritorio → Solicitudes de clientes** | Embudo, solicitudes de la semana, tasa de conversión y lo último anotado |
| **Apariencia → Personalizar → ACG Artista Visual** | Colores, secciones, idioma, contacto, redes y los diseñadores de encabezado y pie |
| **Apariencia → Contenido demo** | Importar o eliminar la demo |

---

## Los cuatro requisitos del encargo, y dónde están

### Diseñador de encabezado y pie desde el panel

Personalizar → **Diseñador de encabezado** / **Diseñador de pie de página**:
un constructor por zonas al estilo del que traen themes como Astra. Cada fila
(la principal y una inferior opcional) tiene tres columnas donde se arrastra
cada elemento: logo, menú, botón de WhatsApp, conmutador de idioma, redes,
datos de contacto, buscador, copyright o texto libre.

Dos decisiones que explican cómo está montado:

- **El constructor decide dónde y en qué orden, nunca el contenido.** El texto
  del botón, el número de WhatsApp o las URLs de redes se siguen editando en su
  sección de siempre. Así no hay dos sitios donde editar lo mismo.
- **Se guarda como JSON en un theme_mod**, porque ACF gratuito no tiene
  Repeater ni Options Page. `acg_sanitize_layout_json()` valida ese JSON contra
  una lista blanca de filas, columnas y tipos antes de guardarlo: un valor
  manipulado a mano no puede colar un elemento inexistente ni HTML sin filtrar.

El control es JavaScript vanilla con `draggable`/`drop` nativos, sin librerías,
y solo se carga dentro del Personalizador, nunca en el front.

### Diseño adaptado al móvil

Mismo marcado en todos los tamaños; lo que cambia es la disposición:

- La cabecera se queda **siempre en una fila** (logo, WhatsApp, menú
  hamburguesa). El menú y el conmutador de idioma se van al panel móvil, que ya
  los lleva: una cabecera que crece hacia abajo empujaría el titular del hero
  fuera de la pantalla.
- El mosaico del portafolio pasa de tres columnas a dos (≤1024 px) y a una
  (≤720 px), y las fotos apaisadas dejan de ocupar doble columna.
- Los botones que son la acción principal de un bloque pasan a ancho completo;
  los de la cabecera no.

### Todo editable con ACF gratuito

La versión libre de ACF **no** tiene Repeater, Flexible Content, Gallery ni
Options Page. Para conseguir igualmente un sitio 100 % editable:

- Todo lo repetible es un **CPT** (portafolio, servicios, proceso, testimonios,
  equipo, preguntas frecuentes). Se ordenan con el campo *Orden* de Atributos
  de página.
- Los ajustes globales viven en el **Personalizador**.
- Las listas cortas («también por encargo») son un **textarea con una línea por
  elemento**.
- Los campos se declaran **una sola vez**, en `acg_field_groups()`
  (`inc/acf-fields.php`), y desde ahí se registran en ACF o como meta boxes
  nativas si ACF no está. Las meta keys son las mismas en los dos casos, así
  que el contenido sobrevive a activar o desactivar el plugin.
- La página de **Inicio** se edita por **pestañas** (una por sección de la
  portada: hero, portafolio, servicios…), con campo tipo `tab` de ACF —
  disponible en la versión gratuita, a diferencia de Repeater u Options Page.
  Sin ACF, el fallback agrupa lo mismo en bloques `<details>` plegables, con
  el primero abierto: no son pestañas de verdad, pero se navega igual de bien.

### Testimonios desde Google Business Profile

El CPT **Testimonios** tiene tres campos pensados para reseñas copiadas de
Google: **Origen** (manual o Google), **Valoración** (1 a 5 estrellas) y
**Enlace de la reseña**. Con origen «Google», la tarjeta muestra las estrellas
y un enlace «Reseña de Google» que lleva al perfil público.

No hay sincronización automática con la API de Google Business Profile: eso
exigiría un proyecto en Google Cloud, credenciales y una integración con
mantenimiento propio, desproporcionado para un puñado de reseñas. En su lugar,
el flujo es copiar y pegar: abrir Google Business Profile → Reseñas, copiar el
texto de la que se quiera destacar, y pegarla en un testimonio nuevo con
origen «Google» y el enlace del perfil (botón «Compartir perfil»).

### Contenido demo real

`acg-visual/demo/content.json` trae el contenido completo de la maqueta, en
español y en portugués, y `demo/images/` once ilustraciones SVG generadas para
la ocasión (`node _tools/make-demo-images.mjs`). Son SVG y no fotos: pesan unos
KB, se ven nítidas en cualquier pantalla y no meten material de terceros en el
proyecto. Angie las sustituye por las suyas desde la biblioteca de medios sin
tocar código.

Todo lo que crea la importación queda marcado con `_acg_demo`, así que
*Eliminar contenido demo* se lo lleva sin rozar lo que haya creado el cliente.

---

## Colores de fondo configurables

Personalizar → **Colores** define cinco colores (acento, fondo oscuro, fondo
claro, tinta y tarjetas). Personalizar → **Fondos de la portada** elige, para
cada bloque, sobre cuál de los tres esquemas se pinta: oscuro, claro o acento.

Se eligen esquemas y no colores sueltos por sección para que el contraste del
texto acompañe siempre al fondo: no hay forma de dejar texto negro sobre fondo
negro desde el panel. Los tres esquemas se calculan en PHP
(`acg_css_variables()` en `inc/enqueue.php`) y viajan al navegador como
variables CSS ya resueltas; el CSS de dentro de cada sección nunca necesita
saber sobre qué fondo está.

El color de texto sobre el acento se decide comparando el **contraste real**
(fórmula WCAG) contra la tinta y contra el blanco. Un simple «¿es un color
claro?» no vale: el naranja de la marca tiene luminancia baja y aun así el
negro contrasta con él más del doble que el blanco (7:1 frente a 3:1).

## Mostrar u ocultar secciones de la portada (equipo de trabajo incluido)

Páginas → **Inicio** (o Escritorio → «Editar contenido de la portada», atajo
que añade el theme en la barra de administración): cada sección tiene su
propia **pestaña**, y arriba del todo de cada pestaña hay un interruptor
**«Mostrar esta sección en la portada»**. Están las diez secciones, equipo de
trabajo entre ellas.

El interruptor vive junto a los textos de la sección —no en el
Personalizador— para que Angie lo vea en el mismo sitio donde edita el
contenido, sin cambiar de pantalla. El fondo de cada sección (oscuro, claro o
acento) sigue siendo un ajuste del Personalizador, porque es una decisión de
estilo global y no de contenido de esta página en concreto.

`front-page.php` recorre las secciones y solo incluye las que tienen el
interruptor encendido: las apagadas no llegan ni a consultar la base de datos.

---

## Bilingüe español / portugués

El idioma se decide **en el servidor** (`?lang=pt`, cookie, o el idioma por
defecto del Personalizador) y la página se pinta ya en ese idioma. La
alternativa —duplicar el HTML y ocultar uno de los dos con JavaScript, como
hacía la maqueta— manda al visitante el doble de texto y deja a Google leyendo
las dos versiones mezcladas en la misma URL. Se emiten etiquetas `hreflang`
para que los buscadores sepan que la misma URL tiene dos versiones.

Cada campo editable tiene su gemelo `(PT)`. **Si el campo portugués está vacío
se muestra el español**, así que el sitio nunca sale con huecos mientras Angie
va traduciendo. Las micro-cadenas de interfaz (botones, etiquetas del
formulario) no se editan: viven en el diccionario de `acg_strings()`.

---

## El CRM

`acg-crm` es un plugin independiente, extraído del CRM que ya teníamos en
AdryDigital y reducido a lo que hace falta aquí: capturar y seguir clientes.

- Cada envío del formulario crea una **Solicitud** con nombre, contacto,
  servicio, fecha del encargo, mensaje, idioma y página de origen.
- Seis estados —nuevo, contactado, presupuesto enviado, fecha reservada,
  cliente, perdido— y un **historial** por solicitud, con notas, llamadas y
  correos. Los cambios de estado se anotan solos.
- Avisa por correo a Angie (con `Reply-To` de quien escribe, para responder
  directamente) y manda un **acuse de recibo al cliente en su idioma**.
- Widget en el Escritorio con el embudo, las solicitudes de la semana y la tasa
  de conversión, y exportación a CSV.
- Antispam sin captcha, en tres capas: nonce, honeypot y trampa de tiempo. Al
  honeypot se le responde «enviado» en vez de un error, para no darle al bot la
  señal de que ha sido detectado.
- La IP no se guarda en claro, solo su hash.

**Fuera del theme ACG** el formulario se coloca con el shortcode
`[acg_formulario]`.

Si el plugin se desactiva, el formulario del theme no se pierde: el botón pasa
a componer el mensaje y abrirlo en WhatsApp, que es lo que hacía la maqueta.
Sin JavaScript, el formulario se envía por POST a `admin-post.php` y vuelve a
la página con el aviso.

Desactivar el plugin **no borra nada**: los datos de clientes se quedan donde
están.

---

## Desarrollo

```bash
node _tools/check-php.mjs acg-visual acg-crm   # sintaxis PHP sin PHP instalado
node _tools/make-demo-images.mjs               # ilustraciones de la demo
python _tools/make-screenshot.py               # miniatura del theme
node _tools/package.mjs                        # ZIP (solo lo que cambió)
```

`package.mjs` guarda una huella de cada paquete en `_dist/.manifest.json` y solo
vuelve a comprimir lo que ha cambiado: si tocas el theme, no se regenera el ZIP
del plugin.

Para revisar el diseño sin WordPress, sirve la carpeta del proyecto y abre
`_preview/index.html`. Usa el CSS y el JS reales del theme y repite su marcado,
así que refleja lo que se renderiza; lo único escrito a mano son las variables
de color, que en WordPress calcula PHP.

---

## Pendiente antes de publicar

- Sustituir las ilustraciones de la demo por las fotos reales de Angie.
- Completar los datos fiscales del titular en el aviso legal y la política de
  privacidad: los textos incluidos son una **base de trabajo**, no
  asesoramiento jurídico.
- Rellenar el campo del mapa (Personalizar → Contacto) si se quiere mostrar la
  zona de cobertura.
- Revisar el correo de destino de las solicitudes en CRM → Ajustes.
