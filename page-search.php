<?php
/* Template Name: Ad Search */
/**
 * The template for displaying Pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Adforest
 */
?>
<?php get_header(); ?>
<?php
global $adforest_theme;


wp_enqueue_script('adforest-search');


$meta = array(
    'key' => 'post_id',
    'value' => '0',
    'compare' => '!=',
);

// only active ads
$is_active = array(
    'key' => '_adforest_ad_status_',
    'value' => 'active',
    'compare' => '=',
);


$condition = '';
if (isset($_GET['condition']) && $_GET['condition'] != "") {
    $condition = array(
        'key' => '_adforest_ad_condition',
        'value' => $_GET['condition'],
        'compare' => '=',
    );
}
$ad_type = '';
if (isset($_GET['ad_type']) && $_GET['ad_type'] != "") {
    $ad_type = array(
        'key' => '_adforest_ad_type',
        'value' => $_GET['ad_type'],
        'compare' => '=',
    );
}
$warranty = '';
if (isset($_GET['warranty']) && $_GET['warranty'] != "") {
    $warranty = array(
        'key' => '_adforest_ad_warranty',
        'value' => $_GET['warranty'],
        'compare' => '=',
    );
}
$feature_or_simple = '';
if (isset($_GET['ad']) && $_GET['ad'] != "") {
    $feature_or_simple = array(
        'key' => '_adforest_is_feature',
        'value' => $_GET['ad'],
        'compare' => '=',
    );
}
$currency = '';
if (isset($_GET['c']) && $_GET['c'] != "") {
    $currency = array(
        'key' => '_adforest_ad_currency',
        'value' => $_GET['c'],
        'compare' => '=',
    );
}

$price = '';
if (isset($_GET['min_price']) && $_GET['min_price'] != "") {
    $price = array(
        'key' => '_adforest_ad_price',
        'value' => array($_GET['min_price'], $_GET['max_price']),
        'type' => 'numeric',
        'compare' => 'BETWEEN',
    );
}
$location = '';
if (isset($_GET['location']) && $_GET['location'] != "") {
    $location = array(
        'key' => '_adforest_ad_location',
        'value' => trim($_GET['location']),
        'compare' => 'LIKE',
    );
}

//Location
$countries_location = '';
if (isset($_GET['country_id']) && $_GET['country_id'] != "") {
    $countries_location = array(
        array(
            'taxonomy' => 'ad_country',
            'field' => 'term_id',
            'terms' => $_GET['country_id'],
        ),
    );
}

$order = 'desc';
$orderBy = 'date';
if (isset($_GET['sort']) && $_GET['sort'] != "") {
    $orde_arr = explode('-', $_GET['sort']);
    $order = isset($orde_arr[1]) ? $orde_arr[1] : 'desc';

    if (isset($orde_arr[0]) && $orde_arr[0] == 'price') {

        $orderBy = 'meta_value_num';
    } else {
        $orderBy = isset($orde_arr[0]) ? $orde_arr[0] : 'date';
    }
}


$category = '';
if (isset($_GET['cat_id']) && $_GET['cat_id'] != "") {
    $category = array(
        array(
            'taxonomy' => 'ad_cats',
            'field' => 'term_id',
            'terms' => $_GET['cat_id'],
        ),
    );
}

$title = '';
if (isset($_GET['ad_title']) && $_GET['ad_title'] != "") {
    $title = $_GET['ad_title'];
}


// redirect if only cat or location searched to category archive
if (($title == '') && ($countries_location == '') && ($category != '')) {
	wp_redirect( get_term_link(intval($category[0]['terms'])) );
	exit;
}
if (($title == '') && ($countries_location != '') && ($category == '')) {
	wp_redirect( get_term_link(intval($countries_location[0]['terms'])) );
	exit;
}


$custom_search = array();

if (isset($_GET['min_custom'])) {
    foreach ($_GET['min_custom'] as $key => $val) {
        $get_minVal = $val;
        $get_maxVal = ( isset($_GET['max_custom']["$key"]) && $_GET['max_custom']["$key"] != "" ) ? $_GET['max_custom']["$key"] : '';
        if ($get_minVal != "" && $get_maxVal != "") {
            $metaKey = '_adforest_tpl_field_' . $key;

            if (adforest_validateDateFormat($get_minVal) && adforest_validateDateFormat($get_maxVal)) {
                $custom_search[] = array(
                    'key' => $metaKey,
                    'value' => array($get_minVal, $get_maxVal),
                    'compare' => 'BETWEEN',
                );
            } else {

                $custom_search[] = array(
                    'key' => $metaKey,
                    'value' => array($get_minVal, $get_maxVal),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN',
                );
            }
        }
    }
}

if (isset($_GET['custom'])) {
    foreach ($_GET['custom'] as $key => $val) {
        if (is_array($val)) {
            $arr = array();
            $metaKey = '_adforest_tpl_field_' . $key;

            foreach ($val as $v) {

                $custom_search[] = array(
                    'key' => $metaKey,
                    'value' => $v,
                    'compare' => 'LIKE',
                );
            }
        } else {
            if (trim($val) == "0") {
                continue;
            }

            $val = stripslashes_deep($val);

            $metaKey = '_adforest_tpl_field_' . $key;
            $custom_search[] = array(
                'key' => $metaKey,
                'value' => $val,
                'compare' => 'LIKE',
            );
        }
    }
}

if (get_query_var('paged')) {
    $paged = get_query_var('paged');
} else if (get_query_var('page')) {
    // This will occur if on front page.
    $paged = get_query_var('page');
} else {
    $paged = 1;
}
$args = array(
    's' => $title,
    'post_type' => 'ad_post',
    'post_status' => 'publish',
    'posts_per_page' => get_option('posts_per_page'),
    'tax_query' => array(
        $category,
        $countries_location,
    ),
    'meta_key' => '_adforest_ad_price',
    'meta_query' => array(
        $is_active,
        $condition,
        $ad_type,
        $warranty,
        $feature_or_simple,
        $price,
        $currency,
        $location,
        $custom_search,
        $lat_lng_meta_query,
    ),
    'order' => $order,
    'orderby' => $orderBy,
    'paged' => $paged,
);
$results = new WP_Query($args);



$GLOBALS['widget_counter'] = 0;
require trailingslashit(get_stylesheet_directory()) . 'template-parts/layouts/search/search-sidebar.php';
?>

<!--footer section-->
<?php get_footer(); ?>