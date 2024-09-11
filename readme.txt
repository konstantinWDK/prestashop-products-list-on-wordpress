=== Prestashop products lists ===
Contributors: Konstantin WDK
Post soporte para el plugin: https://webdesignerk.com/wordpress/plugins/mostrar-productos-de-prestashop-en-wordpress/
Tags: prestashop, ecommerce, productos, listados, flexbox, api
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.2
Test PHP until: 8.1
Stable tag: 2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Un plugin que permite mostrar productos de una tienda PrestaShop obteniendo productos de su API en WordPress con un layout responsive usando Flexbox. Incluye la opción de crear múltiples listados desde el backoffice.

== Descripción ==

**Prestashop products lists** es una herramienta diseñada para integrarse con tu tienda PrestaShop a través de su API, permitiéndote mostrar productos directamente en tu sitio de WordPress mediante un shortcode. La interfaz del plugin es completamente configurable desde el backoffice de WordPress, donde puedes crear múltiples listados de productos con parámetros personalizados como la moneda, las categorías y el orden de los productos.

### Características principales:
- Muestra productos de PrestaShop mediante un shortcode en cualquier página o post de WordPress.
- Opciones para configurar la moneda (euros o dólares) y seleccionar categorías específicas.
- Permite establecer un número máximo de productos a mostrar.
- Utiliza Flexbox para una visualización responsive de los productos.
- Ordena los productos de manera ascendente, descendente o aleatoria.
- Crea y gestiona múltiples listados personalizados desde el panel de administración.
- Genera automáticamente shortcodes para cada listado de productos.

== Instalación ==

1. Sube los archivos del plugin al directorio `/wp-content/plugins/prestashop-products-lists` o instálalo directamente desde el directorio de plugins de WordPress.
2. Activa el plugin desde el menú 'Plugins' de WordPress.
3. Dirígete al menú "Listados" en el panel de administración de WordPress para configurar y gestionar los listados de productos.
4. Usa el shortcode `[prestashop_listado id="ID_DEL_LISTADO"]` para mostrar productos en cualquier página o post.

== Frequently Asked Questions (FAQ) ==

= ¿Cómo obtengo la clave API de PrestaShop? =
La clave API de PrestaShop se puede generar desde el panel de administración de PrestaShop, en la sección "Parámetros Avanzados > Webservices".
Añade permisos ver GET a tu API de "categories, images, price_ranges, products

= ¿Cómo agrego los productos en una página o post? =
Usa el shortcode `[prestashop_listado id="ID_DEL_LISTADO"]` para mostrar un listado de productos en cualquier página o post de WordPress.

= ¿Qué hacer si no veo los productos correctamente? =
Asegúrate de que la URL de tu tienda PrestaShop y la clave API estén configuradas correctamente en los ajustes del plugin.

== Compatibilidad ==

Este plugin ha sido testeado con las siguientes versiones de PrestaShop:
- 1.7.6
- 1.8.8
- 8.1.7

Y con las siguientes versiones de WordPress:
- 5.8
- 6.6

== Screenshots ==

1. /assets/screenshot-1.jpg - Configuración del plugin en el backoffice de WordPress.
2. /assets/screenshot-2.jpg - Ejemplo de visualización de productos en WordPress.
3. /assets/screenshot-3.jpg - Vista de la interfaz de gestión de listados.

== Changelog ==

= 2.2 =
* Se implementó la opción de organizar los productos por orden aleatorio.
* Se mejoró la seguridad ocultando la clave API en las URLs de las imágenes de los productos.
* Se añadieron más opciones para gestionar las monedas (euros o dólares).

= 2.1 =
* Se añadió soporte para múltiples listados personalizados desde el backoffice.
* Se mejoró el layout de productos usando Flexbox.

= 2.0 =
* Primera versión con integración básica de productos de PrestaShop.

== Upgrade Notice ==

= 2.2 =
Actualiza para obtener nuevas funciones de organización y mejoras de seguridad.
