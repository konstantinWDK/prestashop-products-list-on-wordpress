<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Añadir menú directamente a la página de Listados
function prestashop_products_settings_menu() {
    // Crear solo una opción en el menú que apunte directamente a "Listados"
    add_menu_page(
        'Gestión de Listados de PrestaShop',  // Título de la página
        'Listados',                          // Nombre que aparecerá en el menú
        'manage_options',                    // Capacidad requerida
        'prestashop-products-listings',      // Slug de la página
        'prestashop_products_listings_page', // Función que muestra el contenido
        'dashicons-products',                // Icono del menú
        20                                   // Posición en el menú
    );
}
add_action('admin_menu', 'prestashop_products_settings_menu');

// Página de gestión de listados
function prestashop_products_listings_page() {
    // Mensajes de confirmación
    if (isset($_GET['status']) && $_GET['status'] == 'saved') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('¡Listado guardado correctamente!', 'products-lists-from-prestashop') . '</p></div>';
    } elseif (isset($_GET['status']) && $_GET['status'] == 'deleted') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('¡Listado eliminado correctamente!', 'products-lists-from-prestashop') . '</p></div>';
    }

    // Si se está editando un listado
    $editing_listing_id = isset($_GET['edit']) ? sanitize_text_field(wp_unslash($_GET['edit'])) : null;
    $listings = get_option('prestashop_product_listings', array());

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prestashop_listing_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['prestashop_listing_nonce'])), 'save_prestashop_listing')) {
        if (isset($_POST['list_name'])) {
            // Procesar la creación o edición de un listado
            $new_listing = array(
                'id' => isset($_POST['listing_id']) && !empty($_POST['listing_id']) ? sanitize_text_field(wp_unslash($_POST['listing_id'])) : wp_generate_uuid4(),
                'name' => sanitize_text_field(wp_unslash($_POST['list_name'])),
                'categories' => isset($_POST['categories']) ? sanitize_text_field(wp_unslash($_POST['categories'])) : '',
                'currency' => isset($_POST['currency']) ? sanitize_text_field(wp_unslash($_POST['currency'])) : '',
                'api_key' => isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '',
                'max_products' => isset($_POST['max_products']) ? intval($_POST['max_products']) : 0,
                'shop_url' => isset($_POST['shop_url']) ? esc_url_raw(wp_unslash($_POST['shop_url'])) : '',
                'order' => isset($_POST['order']) ? sanitize_text_field(wp_unslash($_POST['order'])) : '',
            );

            // Si es una edición, actualizar el listado correspondiente
            if ($editing_listing_id) {
                foreach ($listings as $index => $listing) {
                    if ($listing['id'] === $editing_listing_id) {
                        $listings[$index] = $new_listing;
                        break;
                    }
                }
            } else {
                $listings[] = $new_listing;
            }

            update_option('prestashop_product_listings', $listings);

            // Redirigir con el mensaje de éxito
            if (!headers_sent()) {
                wp_safe_redirect(admin_url('admin.php?page=prestashop-products-listings&status=saved'));
                exit;
            }
        }
    }
    
    // Eliminar un listado
    if (isset($_GET['delete'])) {
        $delete_id = sanitize_text_field(wp_unslash($_GET['delete']));
        foreach ($listings as $index => $listing) {
            if ($listing['id'] === $delete_id) {
                unset($listings[$index]);
                break;
            }
        }
        update_option('prestashop_product_listings', $listings);

        // Redirigir con el mensaje de eliminación
        if (!headers_sent()) {
            wp_safe_redirect(admin_url('admin.php?page=prestashop-products-listings&status=deleted'));
            exit;
        }
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Gestión de Listados de PrestaShop', 'products-lists-from-prestashop'); ?></h1>

        <?php if ($editing_listing_id) :
            // Obtener el listado en edición
            $editing_listing = null;
            foreach ($listings as $listing) {
                if ($listing['id'] === $editing_listing_id) {
                    $editing_listing = $listing;
                    break;
                }
            }
        ?>
        <h2><?php echo esc_html__('Editando Listado', 'products-lists-from-prestashop'); ?></h2>
        <form method="post" action="">
            <input type="hidden" name="listing_id" value="<?php echo esc_attr($editing_listing['id']); ?>" />
            <?php wp_nonce_field('save_prestashop_listing', 'prestashop_listing_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="list_name"><?php echo esc_html__('Nombre del Listado', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="text" id="list_name" name="list_name" value="<?php echo esc_attr($editing_listing['name']); ?>" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="categories"><?php echo esc_html__('Categoría (una ID por listado)', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
  
                        <input type="text" id="categories" name="categories" value="<?php echo esc_attr($editing_listing['categories']); ?>" required />
                        <p class="description">
                            <?php echo esc_html__('Es suficiente con una sola ID. Los productso se mostrarían si tienen esas categoria como la principal', 'products-lists-from-prestashop'); ?>
 
                        </p>  
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="currency"><?php echo esc_html__('Moneda', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <select name="currency" id="currency">
                            <option value="EUR" <?php selected($editing_listing['currency'], 'EUR'); ?>><?php echo esc_html__('Euros (€)', 'products-lists-from-prestashop'); ?></option>
                            <option value="USD" <?php selected($editing_listing['currency'], 'USD'); ?>><?php echo esc_html__('Dólares ($)', 'products-lists-from-prestashop'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="api_key"><?php echo esc_html__('Clave API de PrestaShop', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($editing_listing['api_key']); ?>" required />
                        <p class="description">
                            <?php echo esc_html__('Introduce la clave API que se utiliza para conectar con PrestaShop. Asegúrate de que sea correcta para evitar problemas de conexión. Puedes consultar la guía sobre cómo crear la API en el webservice de PrestaShop ', 'products-lists-from-prestashop'); ?>
                            <a href="https://webdesignerk.com/wordpress/plugins/mostrar-productos-de-prestashop-en-wordpress/" target="_blank"><?php echo esc_html__('aquí.', 'products-lists-from-prestashop'); ?></a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_products"><?php echo esc_html__('Número máximo de productos', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="number" id="max_products" name="max_products" value="<?php echo esc_attr($editing_listing['max_products']); ?>" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="shop_url"><?php echo esc_html__('URL de la tienda', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="text" id="shop_url" name="shop_url" value="<?php echo esc_attr($editing_listing['shop_url']); ?>" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="order"><?php echo esc_html__('Orden de los productos', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <select name="order" id="order">
                            <option value="ASC" <?php selected($editing_listing['order'], 'ASC'); ?>><?php echo esc_html__('Ascendente', 'products-lists-from-prestashop'); ?></option>
                            <option value="DESC" <?php selected($editing_listing['order'], 'DESC'); ?>><?php echo esc_html__('Descendente', 'products-lists-from-prestashop'); ?></option>
                            <option value="RANDOM" <?php selected($editing_listing['order'], 'RANDOM'); ?>><?php echo esc_html__('Aleatorio', 'products-lists-from-prestashop'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(esc_html__('Guardar Cambios', 'products-lists-from-prestashop')); ?>
        </form>

        <?php else : ?>
        <h2><?php echo esc_html__('Crear un nuevo listado', 'products-lists-from-prestashop'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('save_prestashop_listing', 'prestashop_listing_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="list_name"><?php echo esc_html__('Nombre del Listado', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="text" id="list_name" name="list_name" value="" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="categories"><?php echo esc_html__('Categoría (una ID por listado)', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <p class="description">
                            <?php echo esc_html__('Es suficiente con una sola ID. Los productso se mostrarían si tienen esas categoria como la principal', 'products-lists-from-prestashop'); ?>
                        </p>  
                        <input type="text" id="categories" name="categories" value="" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="currency"><?php echo esc_html__('Moneda', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <select name="currency" id="currency">
                            <option value="EUR"><?php echo esc_html__('Euros (€)', 'products-lists-from-prestashop'); ?></option>
                            <option value="USD"><?php echo esc_html__('Dólares ($)', 'products-lists-from-prestashop'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="api_key"><?php echo esc_html__('Clave API de PrestaShop', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <p class="description">
                            <?php echo esc_html__('Introduce la clave API que se utiliza para conectar con PrestaShop. Asegúrate de que sea correcta para evitar problemas de conexión. Puedes consultar la guía sobre cómo crear la API en el webservice de PrestaShop ', 'products-lists-from-prestashop'); ?>
                            <a href="https://webdesignerk.com/wordpress/plugins/mostrar-productos-de-prestashop-en-wordpress/" target="_blank"><?php echo esc_html__('aquí.', 'products-lists-from-prestashop'); ?></a>
                        </p>
                        <input type="text" id="api_key" name="api_key" value="" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_products"><?php echo esc_html__('Número máximo de productos', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="number" id="max_products" name="max_products" value="10" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="shop_url"><?php echo esc_html__('URL de la tienda', 'products-lists-from-prestashop'); ?></label></th>
                    <td><input type="text" id="shop_url" name="shop_url" value="https://tuweb.com" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="order"><?php echo esc_html__('Orden de los productos', 'products-lists-from-prestashop'); ?></label></th>
                    <td>
                        <select name="order" id="order">
                            <option value="ASC"><?php echo esc_html__('Ascendente', 'products-lists-from-prestashop'); ?></option>
                            <option value="DESC"><?php echo esc_html__('Descendente', 'products-lists-from-prestashop'); ?></option>
                            <option value="RANDOM"><?php echo esc_html__('Aleatorio', 'products-lists-from-prestashop'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(esc_html__('Crear Listado', 'products-lists-from-prestashop')); ?>
        </form>
        <?php endif; ?>

        <h2><?php echo esc_html__('Listados creados', 'products-lists-from-prestashop'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Nombre', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Shortcode', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Categorías', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Moneda', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Máximo de productos', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Orden', 'products-lists-from-prestashop'); ?></th>
                    <th><?php echo esc_html__('Acciones', 'products-lists-from-prestashop'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($listings)) : ?>
                    <?php foreach ($listings as $listing) : ?>
                        <tr>
                            <td><?php echo esc_html($listing['name']); ?></td>
                            <td>[prestashop_listado id="<?php echo esc_html($listing['id']); ?>"]</td>
                            <td><?php echo esc_html($listing['categories']); ?></td>
                            <td><?php echo esc_html($listing['currency']); ?></td>
                            <td><?php echo esc_html($listing['max_products']); ?></td>
                            <td><?php echo esc_html($listing['order']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg('edit', $listing['id'], admin_url('admin.php?page=prestashop-products-listings'))); ?>"><?php echo esc_html__('Editar', 'products-lists-from-prestashop'); ?></a> |
                                <a href="<?php echo esc_url(add_query_arg('delete', $listing['id'], admin_url('admin.php?page=prestashop-products-listings'))); ?>" onclick="return confirm('<?php echo esc_html__('¿Estás seguro de que quieres eliminar este listado?', 'products-lists-from-prestashop'); ?>');"><?php echo esc_html__('Eliminar', 'products-lists-from-prestashop'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7"><?php echo esc_html__('No hay listados creados aún.', 'products-lists-from-prestashop'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
