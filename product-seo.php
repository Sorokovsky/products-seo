<?php
/**
 * Plugin Name: Product SEO
 * Description: Використовує ACF поля для кастомних метатегів на сторінках товарів.
 * Version: 1.0
 * Requires Plugins: advanced-custom-fields, woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

function add_meta_fields_to_products(): void {
    acf_add_local_field_group(array(
        'key' => 'products_seo_fields',
        'title' => 'Мета теги товару',
        'fields' => array(
            array(
            'key' => 'products_seo_fields_title',
            'type' => 'text',
            'name' => 'products_seo_title',
            'label' => 'Title',
            'instructions' => 'Введіть заголовок для товару',
            'required' => 0,
            'maxlength' => 60,
            ),
            array(
                'key' => 'products_seo_fields_description',
                'type' => 'text',
                'name' => 'products_seo_description',
                'label' => 'Description',
                'instructions' => 'Введіть опис для товару',
                'required' => 0,
                'maxlength' => 160,
            ),
            array(
                'key' => 'products_seo_fields_keywords',
                'type' => 'text',
                'name' => 'products_seo_keywords',
                'label' => 'Keywords',
                'instructions' => 'Введіть ключові слова для товару',
                'required' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product',
                )
            )
        ),
        'menu_order' => 5,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
    ));
}

function custom_product_seo_title(string $title): string {
    if (is_product()) {
        $id = get_the_ID();
        $product = wc_get_product($id);
        $seo_title = get_field('products_seo_title', $id);
        if (empty($seo_title)) {
            $seo_title = $product->get_title();
        }
        if (!empty($seo_title)) {
            return $seo_title;
        }
    }
    return $title;
}

function custom_product_meta_tags(): void {
    if (!is_product()) {?>
        <meta name="twitter:card" content="summary">
        <?php
        return;
    }
    $id = get_the_ID();
    $product = wc_get_product($id);
    $seo_title = get_field('products_seo_title', $id);
    if (empty($seo_title)) {
        $seo_title = $product->get_title();
    }
    $seo_description = get_field('products_seo_description', $id);
    if (empty($seo_description)) {
        $seo_description = $product->get_description();
    }
    $seo_keywords = get_field('products_seo_keywords', $id);
    $image_url = get_product_image_url();
    ?>
    <meta name="description" content="<?php echo esc_attr($seo_description);?>">
    <meta name="keywords" content="<?php echo esc_attr($seo_keywords);?>">
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?php echo esc_url(get_permalink($id)); ?>">
    <meta property="og:title" content="<?php echo esc_attr($seo_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($seo_description); ?>">
    <meta property="og:image" content="<?php echo esc_url($image_url); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($seo_title);?>">
    <meta name="twitter:description" content="<?php echo esc_attr($seo_description);?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    <meta name="twitter:card" content="summary_large_image">
<?php
}

function get_product_image_url(): string {
    global $product;
    if ($product instanceof WC_Product) {
        $image_id = $product->get_image_id();
        $url = wp_get_attachment_image_url($image_id, 'full');
        if (empty($url)) {
            $url = wc_placeholder_img_src();
        }
        return $url;
    }
    return '';
}

add_action('acf/init', 'add_meta_fields_to_products');
add_filter('pre_get_document_title', 'custom_product_seo_title');
add_action('wp_head', 'custom_product_meta_tags', 1);