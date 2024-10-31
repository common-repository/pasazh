<?php

require_once('PASAZHAPIPasazhProduct.php');
/*
Plugin Name: Pasazh
Plugin URI: https://epasazh.com
Description: با استفاده از این افزونه میتوانید فروشگاه خود را به پاساژ متصل نمایید. کافیست آن را فعال نمایید پس از آن به پنل مدیریت خودت در وب سایت پاساژ مراجعه نموده و ادامه مراحل جهت اتصال را تکمیل نمایید.
Version: 1.15
Author: aliparsa
Author URI: https://profiles.wordpress.org/aliparsa/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset
*/

const PASAZHAPI_papi_version = "1.15";

add_action('rest_api_init', function () {
    register_rest_route('papi', '/products', array(
        'methods' => 'GET',
        'callback' => 'PASAZHAPI_products',
        'permission_callback' => '__return_true'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('papi', '/products/count', array(
        'methods' => 'GET',
        'callback' => 'PASAZHAPI_products_count',
        'permission_callback' => '__return_true'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('papi', '/info', array(
        'methods' => 'GET',
        'callback' => 'PASAZHAPI_info',
        'permission_callback' => '__return_true'
    ));
});

//add_action('woocommerce_new_product', 'PASAZHAPI_sync_product', 10, 1);
//add_action('woocommerce_update_product', 'PASAZHAPI_sync_product', 10, 1);
add_action('deleted_post', 'PASAZHAPI_delete_product');
add_action('save_post', 'PASAZHAPI_sync_product');


//region api functions

function PASAZHAPI_info($product_id)
{

    $logo_url = null;
    $attachment_image_src = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
    if(is_array($attachment_image_src))
        $logo_url = $attachment_image_src[0];

    $response = [
        'name' => get_bloginfo('name'),
        'url' => get_site_url(),
        'logo' => $logo_url,
        'papi_version' => PASAZHAPI_papi_version
    ];
    return new WP_REST_Response($response, 200);

}

function PASAZHAPI_products_count(){
    return new WP_REST_Response(wp_count_posts('product'), 200);
}

function PASAZHAPI_products($data)
{

    $base64 = $data->get_params()["query"];
    $json = base64_decode($base64);
    $query = json_decode($json, true);
    if(!isset($query['status'])){
        $query['status'] = [ 'trash', 'publish'];
    }

    $pasazh_products = [];
    $items = wc_get_products($query);
    foreach ($items as $product) {
        $product->description = apply_filters( 'the_content', $product->description );
        $pasazh_product = PASAZHAPI_get_pasazh_product_from_wc_product($product);
        $pasazh_products[] = $pasazh_product;
    }

    $response = [];
    $response["meta"] = [
        "count" => sizeof($pasazh_products)
    ];
    $response["products"] = $pasazh_products;


    return new WP_REST_Response($response, 200);
}

function PASAZHAPI_delete_product($product_id)
{


    try {
        if (get_transient("papi_product_id") == $product_id) return;
        set_transient('papi_product_id', $product_id, 1);

        $post = get_post($product_id);


        if (!$post)
            return;

        $post_type = $post->post_type;
        if ($post_type != "product")
            return;


        wp_remote_get("https://epasazh.com/api/webhooks/woocommerce/addon-product-deleted/$product_id", [
            "headers" => [
                "papi-source-url" => get_site_url()
            ]
        ]);


    } catch (Exception $e) {

    }

}

function PASAZHAPI_sync_product($product_id)
{


    try {
        if (get_transient("papi_product_id") == $product_id) return;
        set_transient('papi_product_id', $product_id, 1);

        $post = get_post($product_id);


        if (!$post)
            return;

        $post_type = $post->post_type;
        if ($post_type != "product")
            return;


        wp_remote_get("https://epasazh.com/api/webhooks/woocommerce/addon-product-updated/$product_id", [
            "headers" => [
                "papi-source-url" => get_site_url()
            ]
        ]);


    } catch (Exception $e) {

    }

}

//endregion

//region helpers

function PASAZHAPI_get_pasazh_product_from_wc_product($product)
{

    $product_status = $product->get_status();

    $pasazh_product = new PASAZHAPIPasazhProduct();
    $pasazh_product->setId($product->get_id());
    $pasazh_product->setName($product->get_name());
    $pasazh_product->setUrl(get_permalink($product->get_id()));

    $description = $product->get_description();
    if (strlen($description) == 0) {
        foreach ($product->get_attributes() as $attribute) {
            $attribute_data = json_decode(json_encode($attribute['data'], JSON_PRETTY_PRINT));
            $is_visible = $attribute_data->is_visible == 1;
            if ($is_visible) {
                $description .= $attribute_data->name . " : \r\n";
                $description .= implode(' , ', $attribute_data->options);
                $description .= "\r\n\r\n";
            }
        }
    }

    $pasazh_product->setDescription($description);


    // region technical desc
    $technical_description = "";
    $attrs = $product->get_attributes();

    $test = "";
    foreach ($attrs as $attr) {

        if ($attr["visible"] != true) continue;
        if ($attr["variation"] == true) continue;
        if (strlen($technical_description) > 0)
            $technical_description .= "\n\n";
        $data = $attr->get_data();
        $technical_description .= $data["name"] . " : \n";
        $options_text = "";
        foreach ($data["options"] as $option) {

            if (strlen($options_text) > 0)
                $options_text .= " | ";
            $options_text .= $option;

            $test .= $options_text;

        }

        $technical_description .= $options_text;
    }

    if (strlen($technical_description) > 0)
        $pasazh_product->setTechnicalDescription($technical_description);

    if (strlen($product->get_weight()) > 0)
        $pasazh_product->setWeight($product->get_weight());

    if ($product->is_type('variable')) {

        $specification_arr = [];


        foreach ($product->get_available_variations() as $variation) {

            $is_in_stock = isset($variation['is_in_stock']) && $variation['is_in_stock'] == 1;

            $specification_object = new \stdClass();
            $specification_object->name = "";
            foreach ($variation["attributes"] as $attribute) {
                $specification_object->name .= $attribute . " ";
            }
            $specification_object->name = trim($specification_object->name);
            $specification_object->price = $variation["display_price"];
            $specification_object->is_in_stock = $is_in_stock;
            $specification_arr [] = $specification_object;

        }


        if (sizeof($specification_arr) > 0) {
            $pasazh_product->setSpecifications($specification_arr);
        }

    }


    $quantity = 0;
    if ($product->get_manage_stock()) {
        $quantity = $product->get_stock_quantity();
    } else {
        if ($product->is_in_stock()) {
            $quantity = 1;
        }
    }

    $pasazh_product->setQuantity($quantity);

    if (
        strlen($product->get_regular_price()) > 0 &&
        strlen($product->get_sale_price()) > 0 &&
        $product->get_regular_price() != $product->get_sale_price()
    ) {
        $pasazh_product->setPrice($product->get_regular_price());
        $percent_discount = ($product->get_sale_price() * 100) / $product->get_regular_price();
        $pasazh_product->setPercentDiscount(intval(100 - $percent_discount));
    } else {
        $pasazh_product->setPrice($product->get_price());
        $pasazh_product->setPercentDiscount(0);
    }

    if (strlen($product->get_sku()) > 0) {
        $pasazh_product->setCommodityId($product->get_sku());
    }

    //region extract images
    $image_id = (int)$product->get_image_id();
    $image_ids = $product->get_gallery_image_ids();
    if (!is_array($image_ids))
        $image_ids = [];
    array_unshift($image_ids, $image_id);
    $image_ids = array_unique($image_ids);
    $arr_images = [];
    foreach ($image_ids as $id) {
        $image_url = wp_get_attachment_image_url($id, 'full');
        $arr_images[] = $image_url;
    }
    $pasazh_product->setImages($arr_images);
    //endregion

    //region visibility status
    if ($product->get_status() == "publish") {
        $pasazh_product->setVisibilityStatus(1);
    } else {
        $pasazh_product->setVisibilityStatus(0);

    }

    return $pasazh_product;
}

//endregion

?>
