<?php get_header(); ?>
<?php global $adforest_theme; ?>
<?php


if (have_posts()) {
    while (have_posts()) {
        the_post();
        $aid = get_the_ID();
		
		//update_post_meta($aid, '_adforest_is_feature_date', date('2019-06-12 00:10'));
		

		

		
        // Make expired to featured ad
        if (isset($adforest_theme['simple_ad_removal']) && $adforest_theme['simple_ad_removal'] != '-1') {
            $now = time(); // or your date as well
            $simple_date = strtotime(get_the_date('Y-m-d'));
            $simple_days = adforest_days_diff($now, $simple_date);
            $expiry_days = $adforest_theme['simple_ad_removal'];
            if ($simple_days > $expiry_days) {
                wp_trash_post($aid);
            }
        }
        if (get_post_meta($aid, '_adforest_is_feature', true) == '1' && $adforest_theme['featured_expiry'] != '-1') {
            if (isset($adforest_theme['featured_expiry']) && $adforest_theme['featured_expiry'] != '-1') {
				
				
				
				
				
				
				
/*				
				
		$gmt_offset = get_option('gmt_offset') * 60 * 60;
		
		$ad_featured_date = strtotime(get_post_meta($aid, '_adforest_is_feature_date', true));
		echo "Когда стало VIP";
		vardump(date_i18n( "Y-m-d H:i", $ad_featured_date + $gmt_offset));
		
		
		$expiry_seconds = $adforest_theme['featured_expiry'] * 60 * 60 * 24;
		$expiry_date = $ad_featured_date + $expiry_seconds;
		echo "Когда заканчивается VIP";
		vardump(date_i18n( "Y-m-d H:i", $expiry_date + $gmt_offset));
		
		
		$pass_seconds = time() - $ad_featured_date;
		$left_seconds = $expiry_seconds - $pass_seconds;
		echo "Осталось до конца VIP";
		vardump( gmdate("H:i", $left_seconds) );				
*/				
				
				
				
				
				
				
				
				
				
                $now = time(); // or your date as well
				//vardump(date_i18n( "Y-m-d H:i", $now));
				
                $featured_date = strtotime(get_post_meta($aid, '_adforest_is_feature_date', true));
				//vardump(date_i18n( "Y-m-d H:i", $featured_date));

                $featured_days = adforest_days_diff($now, $featured_date);
				//echo "featured_days";
				//vardump($featured_days);
				
                $expiry_days = $adforest_theme['featured_expiry'];
				//vardump($expiry_days);
				
                if ($featured_days > $expiry_days) {
                    update_post_meta($aid, '_adforest_is_feature', 0);
                }
            }
        }

        adforest_setPostViews($aid);

        if (isset($adforest_theme['design_type']) && $adforest_theme['design_type'] == 'modern') {
            get_template_part('template-parts/layouts/ad_style/style', $adforest_theme['ad_layout_style_modern']);
        } else {
            get_template_part('template-parts/layouts/ad_style/style', $adforest_theme['ad_layout_style']);
        }
    }
} else {
    get_template_part('template-parts/content', 'none');
}
get_footer();
?>