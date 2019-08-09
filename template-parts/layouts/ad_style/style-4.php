<?php
global $adforest_theme;
$pid = get_the_ID();
$poster_id = get_post_field('post_author', $pid);
$user_pic = adforest_get_user_dp($poster_id);
$address = get_post_meta($pid, '_adforest_ad_location', true);
$type = $adforest_theme['cat_and_location'];
?>
<div class="main-content-area clearfix">
    <section class="section-padding modern-version">
        <div class="container">
            <?php get_template_part('template-parts/layouts/ad_style/rearrange', 'notification'); ?>
			<?php get_template_part('template-parts/layouts/ad_style/feature', 'notification'); ?>
            <!-- Row -->
            <div class="row">
                <!-- Middle Content Area -->
                <div class="col-md-8 col-xs-12 col-sm-12">

                    <!-- Single Ad -->
                    <div class="singlepost-content">

                        <?php get_template_part('template-parts/layouts/ad_style/ad', 'status'); ?>

                        <?php
                        $f_class = '';
                        if (get_post_meta($pid, '_adforest_is_feature', true) == '1' && get_post_meta($pid, '_adforest_ad_status_', true) == 'active') {
                            $ribbion = 'featured-ribbon';
                            if (is_rtl()) {
                                $ribbion = 'featured-ribbon-rtl';
                            }

                            echo '
							<div class="' . esc_attr($ribbion) . '">
								<span>' . __('Featured', 'adforest') . '</span>
							</div>';
                            $f_class = 'featured-border';
                        }

						$ad_id	=	get_the_ID();
						$media	=	adforest_get_ad_images(get_the_ID());
						if (!$media) {
							$media[] = attachment_url_to_postid(adforest_get_ad_default_image_url('full'));
						}
						$title	=	get_the_title();
						if( count( $media ) > 0 ) {
							$i = 0;
							foreach( $media as $m ) {
								$mid	=	'';
								if ( isset( $m->ID ) ) {
									$mid	= 	$m->ID;
								} else {
									$mid	=	$m;
								}	
								$img  = wp_get_attachment_image_src($mid, 'adforest-single-post');
								
								$full_img  = wp_get_attachment_image_src($mid, 'full');

								
								if( $img[0] == "" ) { continue; } ?>
								
								<div class="item<?php if ($i == 0) { echo ' item_gray'; }?>">
									<a href="<?php echo esc_url($full_img[0]); ?>" data-caption="<?php echo esc_attr( $title ); ?>" data-fancybox="group">    
										<img alt="<?php echo esc_attr( $title ); ?>" src="<?php echo esc_attr( $img[0] ); ?>">	
									</a>
								</div>
							
							
								<?php
								if ($i == 0) { ?>
									<div class="descs-box">
										<?php
											$cats_html = '';
											$post_categories = wp_get_object_terms($pid, array('ad_cats'), array('orderby' => 'term_group'));
											foreach ($post_categories as $c) {
												$cat = get_term($c);
												$cats_html .= '<span><a href="' . get_term_link($cat->term_id) . '">' . esc_html($cat->name) . '</a></span>';
												$it_one++;
											}
										
											echo '
											<div class="category-title">
												' . $cats_html . '
											</div>';
										?>
										<h1><?php the_title(); ?></h1>
								   
										<div class="modern-version-block-info">
											<div class="post-author">
												<?php echo __('Posted', 'adforest') . ':'; ?>
												<a href="javascript:void(0);"><?php echo get_the_date(); ?></a>
												<span class="actions_divider">|</span>
												<?php echo __('Views', 'adforest') . ':'; ?>
												<a href="javascript:void(0);"><?php echo adforest_getPostViews($pid); ?></a>
												<?php
												$my_url = adforest_get_current_url();
												if (strpos($my_url, 'adforest.scriptsbundle.com') !== false) {
													
												} else {
													if (get_post_field('post_author', $pid) == get_current_user_id() || is_super_admin(get_current_user_id())) {
														?>
														<span class="actions_divider">|</span>
														<a href="<?php echo get_the_permalink($adforest_theme['sb_post_ad_page']); ?>?id=<?php echo esc_attr($pid); ?>"><?php echo __('Edit', 'adforest'); ?></a>
														<?php
													}
												}
												?>
											</div>
											<?php
											$custom_location = get_post_meta(get_the_ID(), '_adforest_ad_location', true);
											
											if ($custom_location) {
												?>
												<div class="custom_location">
													<?php echo'<i class="fa fa-map-marker"></i> ' . get_post_meta(get_the_ID(), '_adforest_ad_location', true); ?>
												</div>	
											<?php } ?>
										</div>
										

										
										<div class="desc-points">
											<?php the_content(); ?>
										</div>
									</div>

									<?php

								} 
								$i++;
							}
						}
						?>

                        


						
						

                    </div>
                    <!-- Single Ad End --> 
                </div>
                <!-- Right Sidebar -->
                <div class="col-md-4 col-xs-12 col-sm-12">
                    <!-- Sidebar Widgets -->
                    <div class="sidebar">


                        <?php if (is_active_sidebar('adforest_ad_sidebar_top')) { ?>
                            <?php dynamic_sidebar('adforest_ad_sidebar_top'); ?>
                        <?php } ?>
                        <?php
                        $poster_name = get_post_meta($pid, '_adforest_poster_name', true);
                        if ($poster_name == "") {
                            $user_info = get_userdata($poster_id);
                            $poster_name = $user_info->display_name;
                        }
                        ?>
                        <div class="widget">
                            <div class="widget-heading">
                                <h4 class="panel-title">
									
									<?php
									if (get_post_meta($pid, '_adforest_ad_status_', true) != "" && get_post_meta($pid, '_adforest_ad_status_', true) == 'active') {
										?>
										<div class="new-price-tag">
											<?php
											if (get_post_meta($pid, '_adforest_ad_price_type', true) == "no_price" || ( get_post_meta($pid, '_adforest_ad_price', true) == "" && get_post_meta($pid, '_adforest_ad_price_type', true) != "free" && get_post_meta($pid, '_adforest_ad_price_type', true) != "on_call" )) {
												
											} else {
												?>
												<h3><?php echo adforest_adPrice($pid, 'negotiable-single'); ?></h3>
												<?php
											}
											?>

										</div>
										<?php
									}
									?>
							
							
							

                                </h4>
                            </div>
                            <div class="widget-content">


                                <div class="sidebar-user-info">
                                    <div class="row">


                                        <div class="media">
                                            <a href="<?php echo get_author_posts_url($poster_id); ?>?type=ads" class="pull-left <?php echo esc_attr($flip); ?>"> 
                                                <img src="<?php echo esc_url($user_pic); ?>" width="80" height="80" alt="<?php echo __('Profile Pic', 'adforest'); ?>">
                                            </a>
                                            <div class="media-body">
                                                <h4 class="media-heading"></h4>
                                                <?php
                                                if (get_user_meta($poster_id, '_sb_badge_type', true) != "" && get_user_meta($poster_id, '_sb_badge_text', true) != "" && isset($adforest_theme['sb_enable_user_badge']) && $adforest_theme['sb_enable_user_badge'] && $adforest_theme['sb_enable_user_badge'] && isset($adforest_theme['user_public_profile']) && $adforest_theme['user_public_profile'] != "" && $adforest_theme['user_public_profile'] == "modern") {
                                                    ?>
                                                    <span class="label <?php echo get_user_meta($poster_id, '_sb_badge_type', true); ?>">
                                                        <?php echo get_user_meta($poster_id, '_sb_badge_text', true); ?>
                                                    </span>
                                                    &nbsp;
                                                    <?php
                                                }
                                                ?>
												
												<div class="ad_author"><?php echo esc_html($poster_name); ?></div>
												
												<div class="author_registered"><?php echo __('[:ru]На сайте с[:ua]На сайті з[:]') . ' <b>' . date_i18n('M. Y',strtotime(get_userdata($poster_id)->user_registered)) . '</b>'; ?></div>

												<div class="ad_location"><?php echo adforest_display_adLocation($pid); ?></div>
									
                                                <?php
                                                if (isset($adforest_theme['user_public_profile']) && $adforest_theme['user_public_profile'] != "" && $adforest_theme['user_public_profile'] == "modern" && isset($adforest_theme['sb_enable_user_ratting']) && $adforest_theme['sb_enable_user_ratting']) {
                                                    ?>
                                                    <a href="<?php echo get_author_posts_url($poster_id); ?>?type=1">
                                                        <div class="rating">
                                                            <?php
                                                            $got = get_user_meta($poster_id, "_adforest_rating_avg", true);
                                                            if ($got == "")
                                                                $got = 0;
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                if ($i <= round($got))
                                                                    echo '<i class="fa fa-star"></i>';
                                                                else
                                                                    echo '<i class="fa fa-star-o"></i>';
                                                            }
                                                            ?>
                                                            <span class="rating-count">
                                                                (<?php
                                                                echo count(adforest_get_all_ratings($poster_id));
                                                                ?>)
                                                            </span>
                                                        </div>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <?php if (get_post_meta($pid, '_adforest_ad_type', true) != "") { ?>
                                    <div class="ad-type">

                                        <?php
                                        if (is_rtl()) {
                                            $link1 = trailingslashit(get_template_directory_uri()) . 'images/megaphone2.png';
                                            $link2 = trailingslashit(get_template_directory_uri()) . 'images/megaphone.png';
                                        } else {
                                            $link1 = trailingslashit(get_template_directory_uri()) . 'images/megaphone.png';
                                            $link2 = trailingslashit(get_template_directory_uri()) . 'images/megaphone2.png';
                                        }
                                        ?>

                                        <div class="type-icon">  <img src="<?php echo esc_url($link1); ?>" /> </div>
                                        <div class="type-text"> <span><?php echo get_post_meta($pid, '_adforest_ad_type', true); ?></span> <img src="<?php echo esc_url($link2); ?>" /></div>
                                    </div>
                                    <br />
                                <?php } ?>                  
                                <div class="sidebar-user-link">
                                    <?php
                                    if (get_post_meta($pid, '_adforest_ad_status_', true) != "" && get_post_meta($pid, '_adforest_ad_status_', true) == 'active') {
                                        if ($adforest_theme['communication_mode'] == 'both' || $adforest_theme['communication_mode'] == 'phone') {
                                            $call_now = 'javascript:void(0);';
                                            if (wp_is_mobile())
                                                $call_now = 'tel:' . get_post_meta($pid, '_adforest_poster_contact', true);

                                            $contact_num = get_post_meta($pid, '_adforest_poster_contact', true);
                                            $tool_tip = '';
                                            $is_verification_on = false;
                                            $batch_src = '';
                                            $cls = 'btn-phone';
                                            if (isset($adforest_theme['sb_phone_verification']) && $adforest_theme['sb_phone_verification']) {
                                                $is_verification_on = true;
                                                $contact_num = get_user_meta($poster_id, '_sb_contact', true);
                                                if ($contact_num != "") {
                                                    if (get_user_meta($poster_id, '_sb_is_ph_verified', true) == '1') {
                                                        $tool_tip = 'data-toggle="tooltip" data-placement="top" data-original-title="' . __('Verified', 'adforest') . '"';
                                                        $batch_src = trailingslashit(get_template_directory_uri()) . 'images/verified.png';
                                                        $cls = 'btn-phone';
                                                    } else {
                                                        $tool_tip = 'data-toggle="tooltip" data-placement="top" data-original-title="' . __('Not verified', 'adforest') . '"';
                                                        $batch_src = trailingslashit(get_template_directory_uri()) . 'images/not-verified.png';
                                                        $cls = 'btn-warning';
                                                    }
                                                } else {
                                                    $contact_num = get_post_meta($pid, '_adforest_poster_contact', true);
                                                    $tool_tip = 'data-toggle="tooltip" data-placement="top" data-original-title="' . __('Not verified', 'adforest') . '"';
                                                    $batch_src = trailingslashit(get_template_directory_uri()) . 'images/not-verified.png';
                                                    $cls = 'btn-warning';
                                                }
                                            }
                                            if ($contact_num != "") {

                                                if (adforest_showPhone_to_users()) {
                                                    $contact_num = __("Login To View", "adforest");
                                                    $call_now = "javascript:void(0)";
                                                    $adforest_login_page = isset($adforest_theme['sb_sign_in_page']) ? $adforest_theme['sb_sign_in_page'] : '';
                                                    if ($adforest_login_page != '') {
                                                        $call_now = get_the_permalink($adforest_login_page) . '?u=' . esc_url(adforest_get_current_url());
                                                    }
                                                }
                                                ?>
                                                <!--<div class="or"></div>-->
                                                <a href="<?php echo adforest_returnEcho($call_now); ?>" class="btn btn-block <?php echo esc_attr($cls); ?>" role="button" id="show_ph_num" data-ph-num="<?php echo esc_attr($contact_num); ?>" <?php echo adforest_returnEcho($tool_tip); ?>>
                                                    <?php
                                                    if (!$is_verification_on) {
                                                        ?>
                                                        <i class="fa fa-phone"></i> 
                                                        <?php
                                                    }
                                                    ?>
                                                    <span>
                                                        <?php
                                                        if ($is_verification_on) {
                                                            ?>
                                                            <img src="<?php echo adforest_returnEcho($batch_src); ?>">
                                                            <?php
                                                        }
                                                        ?>
                                                        <?php echo __('Click to show phone number', 'adforest'); ?>
                                                    </span>
                                                </a>
                                                <?php
                                            }
                                        }
                                        if ($adforest_theme['communication_mode'] == 'both' || $adforest_theme['communication_mode'] == 'message') {
                                            if (get_current_user_id() == "") {
                                                ?>

                                                <a href="<?php echo get_the_permalink($adforest_theme['sb_sign_in_page']); ?>?u=<?php echo esc_url(adforest_get_current_url()); ?>" class="btn btn-message btn-block" role="button" >
                                                    <i class="fa fa-envelope-o"></i> <?php echo __('Message Seller', 'adforest'); ?>
                                                </a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href="javascript:void(0);" class="btn btn-message btn-block" role="button" data-toggle="modal" data-target=".price-quote" >
                                                    <span class="fa fa-send"></span> <?php echo " " . __('Send Message', 'adforest'); ?>
                                                </a>
                                                <?php
                                            }
                                        }
                                    } else if (get_post_meta($pid, '_adforest_ad_status_', true) != "") {
                                        ?>
                                        <a class="btn btn-block btn-danger "><?php echo adforest_ad_statues(get_post_meta($pid, '_adforest_ad_status_', true)); ?></a>
                                        <?php
                                    } else {
                                        update_post_meta($pid, '_adforest_ad_status_', 'active');
                                    }
                                    ?>                    

                                    <ul class="ad-action-list">
                                        <li>
                                            <a href="javascript:void(0);" id="ad_to_fav" data-adid="<?php echo get_the_ID(); ?>">
                                                <i class="fa fa-star"></i> <?php echo __('Save ad as favorite', 'adforest'); ?>
                                            </a>
                                        </li>
                                        <?php
                                        if (isset($adforest_theme['share_ads_on']) && $adforest_theme['share_ads_on']) {
                                            ?>
                                            <li>
                                                <a data-toggle="modal" data-target=".share-ad">
                                                    <i class="fa fa-share-alt"></i> <?php echo __('Share this ad', 'adforest'); ?>
                                                </a>
                                            </li>
                                            <?php
                                            get_template_part('template-parts/layouts/ad_style/share', 'ad');
                                        }
                                        ?>

                                        <li>
                                            <a data-target=".report-quote" data-toggle="modal">
                                                <i class="fa fa-warning"></i> <?php echo __('Report this ad', 'adforest'); ?>
                                            </a>
                                        </li>
                                    </ul>

                                </div>

									<a class="btn btn-primary user_more" role="button" href="<?php echo get_author_posts_url($poster_id); ?>?type=ads">
									<?php echo __('[:ru]Другие объявления автора[:ua]Інші оголошення автора[:]'); ?>
                                    </a>
                            </div>
                        </div>


                        <?php
                        if (isset($adforest_theme['sb_custom_location']) && $adforest_theme['sb_custom_location'] != "" && count(wp_get_post_terms($pid, 'ad_country')) > 0) {
                            ?>
                            <div class="country-locations">
                                <img src="<?php echo trailingslashit(get_template_directory_uri()) . 'images/earth-globe.png'; ?>" />
                                <div class="class-name"><div id="word-count"><?php echo adforest_display_adLocation($pid); ?></div></div>
                            </div>
                            <div class="clearfix"></div>
                            <?php
                        }
                        ?>

                        <?php
                        get_template_part('template-parts/layouts/ad_style/report', 'ad');
                        ?>

                        <?php
                        if (isset($adforest_theme['sb_enable_comments_offer']) && $adforest_theme['sb_enable_comments_offer'] && get_post_meta($pid, '_adforest_ad_status_', true) != 'sold' && get_post_meta($pid, '_adforest_ad_status_', true) != 'expired' && get_post_meta($pid, '_adforest_ad_price', true) != "0") {
                            if (isset($adforest_theme['sb_enable_comments_offer_user']) && $adforest_theme['sb_enable_comments_offer_user'] && get_post_meta($pid, '_adforest_ad_bidding', true) == 1) {
                                echo adforest_bidding_stats($pid);
                            } else if (isset($adforest_theme['sb_enable_comments_offer_user']) && $adforest_theme['sb_enable_comments_offer_user'] && get_post_meta($pid, '_adforest_ad_bidding', true) == 0) {
                                
                            } else {
                                echo adforest_bidding_stats($pid);
                            }
                        }
                        ?>
                        <!-- Saftey Tips  --> 
                        <?php
                        if ($adforest_theme['tips_title'] != '' && $adforest_theme['tips_for_ad'] != "") {
                            ?>
                            <div class="widget">
                                <div class="widget-heading">
                                    <h4 class="panel-title"><a><?php echo adforest_returnEcho($adforest_theme['tips_title']); ?></a></h4>
                                </div>
                                <div class="widget-content saftey">
                                    <?php echo adforest_returnEcho($adforest_theme['tips_for_ad']); ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if (is_active_sidebar('adforest_ad_sidebar_bottom')) { ?>
                            <?php dynamic_sidebar('adforest_ad_sidebar_bottom'); ?>
                        <?php } ?>

                    </div>
                    <!-- Sidebar Widgets End -->
                </div>
                <!-- Middle Content Area  End -->
            </div>
            <!-- Row End -->
            <!-- Row End -->
            <div class="row" style="margin-top: -30px">
                <?php get_template_part('template-parts/layouts/ad_style/related', 'ads'); ?>
            </div>

        </div>
        <!-- Main Container End -->
    </section>
</div>
<?php
get_template_part('template-parts/layouts/ad_style/message', 'seller');
if (get_post_field('post_author', $pid) == $poster_id && get_post_meta($pid, '_adforest_ad_status_', true) == 'active') {
    get_template_part('template-parts/layouts/ad_style/sort', 'images');
}
?>