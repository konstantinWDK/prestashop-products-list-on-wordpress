<?php
/**
 * Plugin Name: PrestaShop Products Lists - Listados Personalizados
 * Description: Muestra productos de PrestaShop en tu sitio de WordPress usando su API con un layout responsive usando Flexbox. Ahora con la opción de crear múltiples listados desde el backoffice.
 * Version: 2.2
 * Author: Konstantin WDK
 * Author URI: https://webdesignerk.com
 * License: GPL2
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir el archivo de administración del backoffice
require_once plugin_dir_path(__FILE__) . 'admin-backoffice.php';

// Enqueue del archivo CSS
function prestashop_products_enqueue_styles() {
    wp_enqueue_style('prestashop-products-style', plugins_url('/css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'prestashop_products_enqueue_styles');

// Shortcode para mostrar un listado específico
function mostrar_listado_prestashop($atts) {
    // Saneado de atributos del shortcode
    $atts = shortcode_atts(array('id' => ''), $atts, 'prestashop_listado');
    $atts['id'] = sanitize_text_field($atts['id']);
    
    $listings = get_option('prestashop_product_listings', array());
    
    // Buscar el listado por ID
    foreach ($listings as $listing) {
        if ($listing['id'] === $atts['id']) {
            // Llamar a la función de mostrar productos con los parámetros del listado
            return mostrar_productos_prestashop(array(
                'currency' => sanitize_text_field($listing['currency']),
                'api_key' => sanitize_text_field($listing['api_key']),
                'categories' => sanitize_text_field($listing['categories']),
                'max_products' => intval($listing['max_products']),
                'shop_url' => esc_url_raw($listing['shop_url']),
                'order' => sanitize_text_field($listing['order'])
            ));
        }
    }

    return esc_html__('Listado no encontrado.', 'text-domain');
}
add_shortcode('prestashop_listado', 'mostrar_listado_prestashop');

// Función para mostrar productos con parámetros personalizados
function mostrar_productos_prestashop($atts) {
    // Parámetros predeterminados y saneado de entrada
    $atts = shortcode_atts(array(
        'currency' => 'EUR',
        'api_key' => '',
        'categories' => '13',
        'max_products' => '10',
        'shop_url' => 'https://tuweb.com',
        'order' => 'ASC'
    ), $atts);

    $currency = sanitize_text_field($atts['currency']);
    $api_key = sanitize_text_field($atts['api_key']);
    $categories = array_map('sanitize_text_field', explode(',', $atts['categories']));
    $max_products = intval($atts['max_products']);
    $shop_url = esc_url_raw($atts['shop_url']);
    $order = sanitize_text_field($atts['order']);

    if (empty($api_key)) {
        return esc_html__('Por favor, ingrese la clave API.', 'text-domain');
    }

    // Crear la URL de la API
    $category_filter = implode(',', $categories);
    $url = esc_url_raw($shop_url . '/api/products?filter[id_category_default]=[' . $category_filter . ']&filter[active]=1&display=[id,name,price,id_default_image,link_rewrite]&limit=' . $max_products . '&ws_key=' . $api_key . '&output_format=JSON');

    // Hacer la petición a la API
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return esc_html__('Error al conectar con la API de PrestaShop', 'text-domain');
    }

    // Obtener el cuerpo de la respuesta
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Asegurarse de que la respuesta sea válida y contenga productos
    if (is_array($data) && isset($data['products']) && is_array($data['products'])) {
        // Ordenar los productos
        if ($order === 'ASC') {
            usort($data['products'], function($a, $b) {
                return $a['price'] <=> $b['price'];
            });
        } elseif ($order === 'DESC') {
            usort($data['products'], function($a, $b) {
                return $b['price'] <=> $a['price'];
            });
        } elseif ($order === 'RANDOM') {
            shuffle($data['products']);
        }

        // Generar el HTML
        $output = '<div class="prestashop-products-grid">';

        foreach ($data['products'] as $product) {
            $product_name = isset($product['name']) ? esc_html($product['name']) : esc_html__('Nombre no disponible', 'text-domain');
            $product_price = number_format($product['price'], 2);
            if ($currency == 'USD') {
                $product_price = number_format($product['price'] * 1.2, 2);
            }

            $product_slug = isset($product['link_rewrite'][2]['value']) ? sanitize_text_field($product['link_rewrite'][2]['value']) : 'slug-no-disponible';
            $image_id = isset($product['id_default_image']) ? esc_attr($product['id_default_image']) : 'default';
            $image_url = esc_url($shop_url . '/' . $image_id . '-home_default/' . $product_slug . '.jpg');
            $product_url = esc_url($shop_url . '/' . $product['id'] . '-' . $product_slug . '.html');

            $output .= '<div class="prestashop-product">';
            $output .= '<a href="' . esc_url($product_url) . '">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product_name) . '" class="product-image">';
            $output .= '</a>';
            $output .= '<div class="product-info">';
            $output .= '<h5 class="product-title"><a href="' . esc_url($product_url) . '">' . esc_html($product_name) . '</a></h5>';
            $output .= '<p class="product-price">' . ($currency == 'EUR' ? '€' : '$') . esc_html($product_price) . '</p>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    } else {
        return esc_html__('No se encontraron productos activos en estas categorías o hubo un error en la respuesta de la API.', 'text-domain');
    }
}
?>
