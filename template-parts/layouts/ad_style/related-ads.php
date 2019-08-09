<?php
global $adforest_theme; 
$pid = get_the_ID();
if( $adforest_theme['Related_ads_on'] ) {
	$cats = wp_get_post_terms( $pid, 'ad_cats' );
	$categories	=	array();
	foreach( $cats as $cat ) {
		$categories[]	=	$cat->term_id;
	}
	$args = array( 
		'post_type' => 'ad_post',
		'post_status' => 'publish',
		'posts_per_page' => $adforest_theme['max_ads'],
		'order'=> 'DESC',
		'post__not_in'	=> array( $pid ),
		'tax_query' => array(
			array(
				'taxonomy' => 'ad_cats',
				'field' => 'id',
				'terms' => $categories,
				'operator'=> 'IN'
			)
		)
	);

	$html = '';

	$ads = new WP_Query($args);
	if ($ads->have_posts()) {
		while ($ads->have_posts()) {
			
			$ads->the_post();
			$pid = get_the_ID();
			$html .=  ai_adforest_search_layout_list($pid);

		}
		wp_reset_postdata();
	}

	echo '
	<div class="grid-panel margin-top-30">
		<div class="heading-panel">
			<div class="col-xs-12 col-md-12 col-sm-12">
				<h3 class="main-title text-left">
					' . $adforest_theme['sb_related_ads_title'] . '
				</h3>
			</div>
		</div>
		<div class="posts-masonry">
			<div class="col-md-12 col-xs-12 col-sm-12">
				' . $html . '
			</div>
		</div>
	</div>';


}
?>