<?php global $adforest_theme; 


//do_action( 'qm/debug', $adforest_theme ); // DEBUG

?>

<header class="main_header">

	<div class="container">
	
		<div class="top_bar">
			
			<div class="site_logo">

				<a href="<?php echo home_url('/'); ?>">
					<?php
						if (qtranxf_getLanguage() == 'ua') {
							$logo_url = get_stylesheet_directory_uri() . '/images/logo_ua.svg';
						} else  {
							$logo_url = get_stylesheet_directory_uri() . '/images/logo_ru.svg';
						}
					?>
					<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr__('Site Logo', 'adforest'); ?>" id="sb_site_logo">
				</a>

			</div>
			
			<?php echo qtranxf_generateLanguageSelectCode(); ?>
					
			<ul class="right_menu">
				<?php
				$user_id = get_current_user_id();
				if (is_user_logged_in()) {
									
					global $wpdb;
					$user_info = get_userdata($user_id);
									
					$unread_msgs = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id = '$user_id' AND meta_value = '0' ");
					if ($unread_msgs == 0) $unread_msgs = '';
									
					$fav_ads_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->usermeta WHERE user_id = '$user_id' AND meta_key LIKE  '_sb_fav_id%' ");
					if ($fav_ads_count == 0) $fav_ads_count = '';
								
					$favorites_page_url = get_the_permalink($adforest_theme['sb_profile_page']) . '/?type=fav_ads';
					$cart_url = wc_get_cart_url();
									
					$cart_total_items = WC()->cart->get_cart_contents_count();
					if ($cart_total_items == 0) $cart_total_items = '';
									
					$user_display_name = $user_info->data->display_name;
					if (function_exists('adforest_get_user_dp')) { $user_image_url = esc_url(adforest_get_user_dp($user_id)); }
									


												
				} else {
									
					$login_page = get_the_permalink($adforest_theme['sb_sign_in_page']);
					$favorites_page_url = $login_page;
					$cart_url = $login_page;
					$user_display_name = esc_html__('Sign In', 'adforest');
					$user_image_url = $adforest_theme['sb_user_dp']['url'];

				}
									
															
				// FAVORITES
				?>
				<li class="favorites_top_menu_item" title="<?php esc_html_e('Favourite Ads', 'adforest'); ?>">
					<a href="<?php echo $favorites_page_url; ?>" >
						<i class="fa fa-star"></i>
						<?php if ($fav_ads_count != "" && is_user_logged_in()) { ?>
							<span class="favorites_total"><?php echo $fav_ads_count; ?></span>
						<?php } ?>
					</a>
				</li>
				<?php
									
									

				// CART	
				?>
				<li class="cart_top_menu_item" title="<?php echo __('Cart', 'adforest'); ?>">
					<a href="<?php echo $cart_url ; ?>">
						<i class="fa fa-shopping-cart"></i>
						<?php 
									
						if ( $cart_total_items != ""  && is_user_logged_in()) { ?>
							<span class="cart_total_items"><?php echo $cart_total_items; ?></span>
						<?php } ?>
					</a>
				</li>
				<?php
									
									
				// USER MENU + MESSEDGES BADGE
				?>
				<li class="dropdown hidden-sm-down user_menu" title="<?php echo __("Profile", "adforest"); ?>">
					<a href="" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<div class="user_display_name"><?php echo $user_display_name ?></div>
						<img class="img-circle" src="<?php echo $user_image_url; ?>" alt="<?php __('user prfile picture', 'adforest'); ?>" width="32" height="32">
						<div class="msgs_count"><?php echo $unread_msgs; ?></div>

					</a>
										
					<?php if (is_user_logged_in()) { ?>
						<ul class="dropdown-menu"><!-- LOGGED IN DROPDOWN -->
						
							<li class="add_ad_top_button add_ad_top_button_mobile">
								<?php if (isset($adforest_theme['ad_in_menu']) && $adforest_theme['ad_in_menu']) { ?>
									<a class="btn btn-theme" href="<?php echo get_the_permalink($adforest_theme['sb_post_ad_page']); ?>"><i class="fa fa-plus"></i><?php echo __('Post an Ad', 'adforest'); ?></a>
								<?php } ?>
							</li>
						
							
							<li>
								<a href="<?php echo get_the_permalink($adforest_theme['sb_profile_page']); ?>">
								<i class="fa fa-user"></i> <?php echo __("Profile", "adforest"); ?></a>
							</li>
							
							
							<li>
								<a href="<?php echo home_url() . '/packages/'; ?>">
									<i class="fa fa-shopping-bag"></i>
									<?php echo __('Packages', 'adforest'); ?>
								</a> 
							</li>
							
							
							<li>
							
								<a href="<?php echo $favorites_page_url; ?>">
								<i class="fa fa-star"></i> <?php echo __('Favourite Ads', 'adforest'); ?> <span class="menu_badge"><?php echo $fav_ads_count; ?></span></a> 							
							
							</li>

					
							
							<?php if (isset($adforest_theme['communication_mode']) && ( $adforest_theme['communication_mode'] == 'both' || $adforest_theme['communication_mode'] == 'message' )) { ?>
								<li class="menu_item_messeges">
									<a href="<?php echo get_the_permalink($adforest_theme['sb_profile_page']) . '/?type=messages'; ?>">
									<i class="fa fa-envelope"></i> <?php echo __('Messages', 'adforest'); ?> <span class="menu_badge"><?php echo esc_html($unread_msgs); ?></span></a> 
								</li>
							<?php }

							
							
							if (isset($adforest_theme['sb_cart_in_menu']) && $adforest_theme['sb_cart_in_menu'] && in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
								global $woocommerce; ?>
								<li>
									<a href="<?php echo wc_get_cart_url(); ?>">
										<i class="fa fa-shopping-cart"></i>
										<?php echo __('Cart', 'adforest');
										if ( $cart_total_items != ""  && is_user_logged_in()) { ?>
											<span class="cart_total_items"><?php echo $cart_total_items; ?></span>
											<span class="cart_total_price"><?php echo '(' . $cart_total_price . ')'; ?></span>
										<?php } ?>
									</a> 
								</li>
							<?php } ?>
										

										
							<li>
								<a href="<?php echo wp_logout_url(get_the_permalink($adforest_theme['sb_sign_in_page'])); ?>">
								<i class="fa fa-power-off"></i> <?php echo __("Logout", "adforest"); ?></a>
							</li>
										
						</ul><!-- END DROPDOWN -->
						
					<?php } else { ?>
					
						<ul class="dropdown-menu"><!-- LOGGED OUT DROPDOWN -->
									
							<li>
								<a href="<?php echo get_the_permalink($adforest_theme['sb_sign_in_page']); ?>">
								<i class="fa fa-sign-in"></i> <?php echo esc_html__('Sign In', 'adforest'); ?></a>
							</li>
							
							
							
							<li>
								<a href="<?php echo get_the_permalink($adforest_theme['sb_sign_up_page']); ?>">
								<i class="fa fa-user-plus"></i> <?php echo esc_html__('Sign Up', 'adforest'); ?> <span class="menu_badge"><?php echo $fav_ads_count; ?></span></a> 							
							</li>	
							
						</ul><!-- END DROPDOWN -->					
			
					<?php } ?>
					
				</li> 
							
				<li class="add_ad_top_button">
					<?php if (isset($adforest_theme['ad_in_menu']) && $adforest_theme['ad_in_menu']) { ?>
						<a class="btn btn-theme" href="<?php echo get_the_permalink($adforest_theme['sb_post_ad_page']); ?>"><i class="fa fa-plus"></i><?php echo __('Post an Ad', 'adforest'); ?></a>
					<?php } ?>
				</li>
						
			</ul>
						

		</div><!-- row -->
	
	</div><!-- container -->

</header>

<?php 
// SEARCH IN HEADER
$pages_to_hide_search = array(
	'post-ad',
	'profile',
	'login',
	'register'
);
global $post;
$current_page = $post->post_name;
if (!in_array($current_page, $pages_to_hide_search)) {
	parse_str($_SERVER['QUERY_STRING'], $vars);

	// cats
	if ($vars) {
		if ($vars['cat_id']) {
			$loaded_category_title = (get_term_by('id', $vars['cat_id'], 'ad_cats'))->name;
		} else {
			$loaded_category_title = '[:ru]Все рубрики[:ua]Всі рубрики[:]';
		}
	} 
	
	$args = array(
		'taxonomy'     => 'ad_cats',
		'parent'        => 0,
		'hide_empty'    => false          
	);

	$terms = get_terms( $args );
	$terms = __($terms);
	$sorted_terms = array();
	foreach($terms as $term) {
		$sorted_terms[$term->term_id] = $term->name;
	}

	if ($lang == 'ru') { $coll = collator_create('ru_RU'); } 
	elseif  ($lang == 'ua') { $coll = collator_create('uk_UA'); } 
	else { $coll = collator_create( 'en_US' ); }
	collator_asort( $coll, $sorted_terms, Collator::SORT_STRING );
	
	$bull_preloader = '<span class="bull_preloader"></span>';
	$top_cats = '<div class="ad_cat_item" data-cat_id="">' . $bull_preloader . '<span class="cat_title">[:ru]Все рубрики[:ua]Всі рубрики[:]</span></div>';
	foreach($sorted_terms as $term_id=>$term_name) {
		if ( count(get_term_children( $term_id, 'ad_cats' )) > 0 ) {
			$has_children = 'true';
			$sub_cat_html = '<div class="cat_' . $term_id . '_sub_cat sub_cat_wrap"></div>';
			$has_children_arrow = '<span class="cat_arrow"><i class="fa fa-angle-right"></i></span>';
		} else {
			$has_children = 'false';
			$sub_cat_html = '';
			$has_children_arrow = "";
		}
		$top_cats .= '<div class="ad_cat_item_' . $term_id . ' ad_cat_item" data-sub_loaded="false" data-has_child="' . $has_children . '" data-cat_id="' . $term_id . '">' . $bull_preloader . '<span class="cat_title">' . $term_name . '</span>' . $has_children_arrow . $sub_cat_html . '</div>';
	}

	// locations
	if ($vars) {
		if ($vars['country_id']) {
			$loaded_location_title = (get_term_by('id', $vars['country_id'], 'ad_country'))->name;
		} else {
			$loaded_location_title = '[:ru]Все регионы[:ua]Всі регіони[:]';
		}
	} 
	
	$args = array(
		'taxonomy'      => 'ad_country',
		'parent'        => 0,
		'hide_empty'    => false,
	);

	$terms = get_terms( $args );
	$terms = __($terms);
	$sorted_terms = array();
	foreach($terms as $term) {
		$sorted_terms[$term->term_id] = $term->name;
	}

	if ($lang == 'ru') { $coll = collator_create('ru_RU'); } 
	elseif  ($lang == 'ua') { $coll = collator_create('uk_UA'); } 
	else { $coll = collator_create( 'en_US' ); }
	collator_asort( $coll, $sorted_terms, Collator::SORT_STRING );

	$bull_preloader = '<span class="bull_preloader"></span>';
	$top_locs = '<div class="ad_cat_item" data-cat_id="">' . $bull_preloader . '<span class="cat_title">[:ru]Все регионы[:ua]Всі регіони[:]</span></div>';
	foreach($sorted_terms as $term_id=>$term_name) {
		if ( count(get_term_children( $term_id, 'ad_country' )) > 0 ) {
			$has_children = 'true';
			$sub_loc_html = '<div class="cat_' . $term_id . '_sub_cat sub_cat_wrap"></div>';
			$has_children_arrow = '<span class="cat_arrow"><i class="fa fa-angle-right"></i></span>';
		} else {
			$has_children = 'false';
			$sub_loc_html = '';
			$has_children_arrow = "";
		}
		$top_locs .= '<div class="ad_cat_item_' . $term_id . ' ad_cat_item" data-sub_loaded="false" data-has_child="' . $has_children . '" data-cat_id="' . $term_id . '">' . $bull_preloader . '<span class="cat_title">' . $term_name . '</span>' . $has_children_arrow . $sub_loc_html . '</div>';
	}
	echo '
	<div class="container">
		<div id="search-section">
			<div class="row">
				<div class="col-lg-12 col-xs-12 col-sm-12 col-md-12">
					<form method="get" action="'. urldecode(get_the_permalink($adforest_theme['sb_search_page'])) .'" class="search-form">
					
						<!-- Search Field -->
						<div class="col-md-9 col-xs-12 no-padding main_search_field">
							<input type="text" autocomplete="off"  value="' . $vars['ad_title'] . '" name="ad_title" class="form-control" placeholder="'.__('What Are You Looking For...','adforest').'" />
						</div>
						
						<!-- Search Button -->
						<div class="col-md-3 col-xs-12 no-padding main_search_button">
							<button type="submit" class="btn btn-block btn-light">'.__('Search','adforest').'</button>
						</div>
						
						<!-- Category Field -->
						<div class="col-sm-12 col-md-6 no-padding main_search_category">
							<input readonly type="text" autocomplete="off"  value="' . __($loaded_category_title) . '" id="search_category_field" class="form-control" placeholder="'.__('Select Category','adforest').'" />
							<div class="search_category_field_icon"><i class="fa fa-bars" aria-hidden="true"></i></div>
							<div class="search_category_field_clear">×</div>
						</div>
						<div id="search_category_modal">
							<div class="top_level">
								<div class="current_top_level"><span class="cat_arrow"><span class="close_modal_icon">×</span></span>' . __('[:ru]Закрыть[:ua]Закрити[:]') . '</div>
								<div class="top_level_cats_wrap">' . __($top_cats) . '</div>
							</div>
						</div>			
						
						<!-- Location Field -->
						<div class="col-sm-12 col-md-6 no-padding main_search_location">
							<input readonly type="text" autocomplete="off"  value="' . __($loaded_location_title) . '" id="search_location_field" class="form-control" placeholder="'.__('Select Location','adforest').'" />
							<div class="search_location_field_icon"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
							<div class="search_location_field_clear">×</div>
						</div>
						<div id="search_location_modal">
							<div class="top_level">
								<div class="current_top_level"><span class="cat_arrow"><span class="close_modal_icon">×</span></span>' . __('[:ru]Закрыть[:ua]Закрити[:]') . '</div>
								<div class="top_level_cats_wrap">' . __($top_locs) . '</div>
							</div>
						</div>	

						<input type="hidden" value="' . $vars['cat_id'] . '" name="cat_id" />
						<input type="hidden" value="' . $vars['country_id'] . '" name="country_id" />
						
					</form>
				</div>
			</div>
		</div>
	</div>';
}













