<?php get_header(); ?>
<?php global $adforest_theme; ?>
<div class="main-content-area clearfix">
    <!-- =-=-=-=-=-=-= Latest Ads =-=-=-=-=-=-= -->
    <section class="pattern_dots">
		
        <!-- Main Container -->
        <div class="container">
            <!-- Row -->
            <div class="row">
				<div class="col-md-12 col-lg-12 col-sx-12">
				
					<div class="panel cat_header">
				
						<h1><?php echo __('[:ru]Рубрика: [:ua]Рубрика: [:]' . single_cat_title("", false)); ?></h1>
					
					
						<div class="ai_breadcrumbs"><?php get_breadcrumbs('ad_cats'); ?></div>
					</div>	
					
					
				</div>

                <?php
                if (isset($adforest_theme['feature_on_search']) && $adforest_theme['feature_on_search']) {
                    $category = array(
                        array(
                            'taxonomy' => 'ad_cats',
                            'field' => 'term_id',
                            'terms' => get_queried_object_id(),
                        ),
                    );
                    $args = array(
                        'post_type' => 'ad_post',
                        'post_status' => 'publish',
                        'posts_per_page' => $adforest_theme['max_ads_feature'],
                        'tax_query' => array(
                            $category,
                        ),
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
                        'orderby' => 'rand',
                    );
					
					    $results = new WP_Query($args);
						
						$vip_pids = array();
						// vip
						if ($results->have_posts()) {
							while ($results->have_posts()) {
								$results->the_post();
								$vip_pids[] = get_the_ID();
							}
							wp_reset_postdata();
						}
				}
	
					
				
                
                ?>
				
                <!-- Middle Content Area -->
                <div class="col-md-12 col-lg-12 col-sx-12">
                    <!-- Row -->
                    <div class="row">
                        <?php
                        if (have_posts()) {
                            ?>
                            <?php
                            if (isset($adforest_theme['design_type']) && $adforest_theme['design_type'] == 'modern') {
                                
                            } else {
                                ?>
                                <!-- Sorting Filters -->
                                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
								 
                                    <!-- Sorting Filters Breadcrumb -->
                                    <div class="filter-brudcrums">
                                        <span>
                                            <?php echo __('Category', 'adforest') . ': ' . ucfirst(single_cat_title("", false)); ?>
                                        </span>
                                    </div>
                                    <!-- Sorting Filters Breadcrumb End -->
                                </div>
                                <!-- Sorting Filters End-->
                                <?php
                            }
                            ?>
                            <div class="clearfix"></div>
                            <!-- Ads Archive 1 -->
                            <div class="posts-masonry">
                                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
									<?php
									if ($vip_pids) { ?>
										<ul class="list-unstyled" style="padding-bottom: 50px;">
											<?php
											foreach($vip_pids as $vip_pid) {
												echo ai_adforest_search_layout_list($vip_pid);
											}
											?>
										</ul>
									<?php } ?>
									
                                    <ul class="list-unstyled">
                                        <?php
                                        while (have_posts()) {
                                            the_post();
                                            $pid = get_the_ID();
                                            echo ai_adforest_search_layout_list($pid);
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <!-- Ads Archive End -->  
                            <div class="clearfix"></div>
                            <!-- Pagination -->  
                            <div class="col-md-12 col-xs-12 col-sm-12">
                                <?php adforest_pagination(); ?>
                            </div>
                            <!-- Pagination End -->
                            <?php
                        } else {
                            get_template_part('template-parts/content', 'none');
                        }
                        ?>
                    </div>
                    <!-- Row End -->
                </div>
                <!-- Middle Content Area  End -->
            </div>
            <!-- Row End -->
        </div>
        <!-- Main Container End -->
    </section>
    <!-- =-=-=-=-=-=-= Ads Archives End =-=-=-=-=-=-= -->

</div>
<?php get_footer(); ?>