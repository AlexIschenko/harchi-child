<?php
	$args = array(
		'post_type' => 'ad_post',
		'post_status' => 'publish',
		'posts_per_page' => -1,

		'meta_query' => array(
			array(
				'key' => '_adforest_is_feature',
				'value' => 1,
				'compare' => '=',
			),
			array(
				'key' => '_adforest_ad_status_',
				'value' => 'active',
				'compare' => '=',
			),
		),
	);
	
	$results = new WP_Query($args);
	

	$vips = array();
	if ($results->have_posts()) {
		while ($results->have_posts()) {
			$results->the_post();
			$vips[] = get_the_ID();
		}
		wp_reset_postdata();
	}
	vardump($vips);
	
	
	/*
	$args = array(
		'post_type' => 'ad_post',
		'post_status' => 'publish',
		'posts_per_page' => 10,
		'post__not_in' => $vips,
		'meta_query' => array(
			array(
				'key' => '_adforest_ad_status_',
				'value' => 'active',
				'compare' => '=',
			),
		),
		'orderby' => 'rand',
	);
	
	$results = new WP_Query($args);
	

	$vips = array();
	if ($results->have_posts()) {
		while ($results->have_posts()) {
			$results->the_post();
			vardump(get_the_ID());
			update_post_meta(get_the_ID(), '_adforest_is_feature', '1');
			update_post_meta(get_the_ID(), '_adforest_is_feature_date', date('Y-m-d'));
		}
		wp_reset_postdata();
	}
	*/