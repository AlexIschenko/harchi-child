<?php global $adforest_theme; ?>
<div class="main-content-area clearfix">
    <!-- =-=-=-=-=-=-= Latest Ads =-=-=-=-=-=-= -->
    <section class="section-padding <?php echo esc_attr($adforest_theme['search_bg']); ?>">
        <!-- Main Container -->
        <div class="container">
            <!-- Row -->
            <div class="row">


                <!-- Middle Content Area -->
                <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                    <!-- Row -->
                    <div class="row">
                        <!-- Sorting Filters -->
                        <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                            <div class="clearfix"></div>
                            <div class="listingTopFilterBar">
                                <div class="col-md-7 col-xs-12 col-sm-7 no-padding">
                                    <ul class="filterAdType">
                                        <li class="active">
                                            <a href="javascript:void(0);"><?php echo __('Found Ads', 'adforest'); ?>
                                                <small>(<?php echo esc_html($results->found_posts); ?>)</small>
                                            </a>
                                        </li>
                                        <?php
                                        $param = $_SERVER['QUERY_STRING'];

                                        if ($param != "") {
                                            ?>

                                            <li class="">
                                                <a href="<?php echo get_the_permalink($adforest_theme['sb_search_page']); ?>"><?php echo __('Reset Search', 'adforest'); ?></a>
                                            </li>
                                            <?php
                                        }

                                        $grid_view = adforest_custom_remove_url_query('view-type', 'grid');
                                        $list_view = adforest_custom_remove_url_query('view-type', 'list');

                                        if (isset($adforest_theme['search_layout_types']) && $adforest_theme['search_layout_types'] == true) {
                                            ?>
                                            <li class="<?php echo (is_rtl() ) ? 'pull-left' : 'pull-right'; ?>"><a href="<?php echo esc_url($list_view); ?>" class="<?php echo (is_rtl() ) ? 'pull-left' : 'pull-right'; ?>"><i class="fa fa-bars"></i></a><a href="<?php echo esc_url($grid_view); ?>" class="pull-right"><i class="fa fa-th-large"></i></a></li>                      
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="col-md-5 col-xs-12 col-sm-5 no-padding">
                                    <div class="header-listing">
                                        <?php
                                        if (isset($adforest_theme['search_layout_types']) && $adforest_theme['search_layout_types'] == true) {
                                            
                                        } else {
                                            ?>
                                            <h6><?php echo __('Sort by', 'adforest'); ?>:</h6>
                                        <?php } ?>

                                        <div class="custom-select-box <?php echo (is_rtl() ) ? 'pull-left' : 'pull-right'; ?>">

                                            <?php
                                            $selectedOldest = $selectedLatest = $selectedTitleAsc = $selectedTitleDesc = $selectedPriceHigh = $selectedPriceLow = '';
                                            if (isset($_GET['sort'])) {
                                                $selectedOldest = ( $_GET['sort'] == 'id-asc' ) ? 'selected' : '';
                                                $selectedLatest = ( $_GET['sort'] == 'id-desc' ) ? 'selected' : '';
                                                $selectedTitleAsc = ( $_GET['sort'] == 'title-asc' ) ? 'selected' : '';
                                                $selectedTitleDesc = ( $_GET['sort'] == 'title-desc' ) ? 'selected' : '';
                                                $selectedPriceHigh = ( $_GET['sort'] == 'price-desc' ) ? 'selected' : '';
                                                $selectedPriceLow = ( $_GET['sort'] == 'price-asc' ) ? 'selected' : '';
                                            }
                                            ?>
                                            <form method="get">
                                                <select name="sort" id="order_by" class="custom-select order_by">
                                                    <option value="id-desc" <?php echo esc_attr($selectedLatest); ?>>
                                                        <?php echo esc_html__('Newest To Oldest', 'adforest'); ?>
                                                    </option>
                                                    <option value="id-asc" <?php echo esc_attr($selectedOldest); ?>>
                                                        <?php echo esc_html__('Oldest To Newest', 'adforest'); ?>
                                                    </option>
                                                    <option value="price-desc" <?php echo esc_attr($selectedPriceHigh); ?>>
                                                        <?php echo esc_html__('Price: High to Low', 'adforest'); ?>
                                                    </option>
                                                    <option value="price-asc" <?php echo esc_attr($selectedPriceLow); ?>>
                                                        <?php echo esc_html__('Price: Low to High', 'adforest'); ?>
                                                    </option>
                                                </select>
                                                <?php echo adforest_search_params('sort'); ?>
                                            </form>   

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($adforest_theme['design_type']) && $adforest_theme['design_type'] == 'modern') { ?>
                            <div class="col-md-12 col-xs-12 col-sm-12 col-lg-12">
                                <?php get_template_part('template-parts/layouts/search/search', 'tags'); ?>
                            </div>
                        <?php } ?>
                        <!-- Sorting Filters End-->
                        <div class="clearfix"></div>
                        <?php
                        if (isset($adforest_theme['sb_allow_cats_above_filters']) && $adforest_theme['sb_allow_cats_above_filters']) {
                            if (isset($_GET['cat_id']) && $_GET['cat_id'] != "") {
                                ?>

                                <?php
                                $cat_id = $_GET['cat_id'];
                                $ad_cats = adforest_get_cats('ad_cats', $cat_id);
                                $res = '';
                                $rows_count = 1;
                                $max_rows = $adforest_theme['sb_max_sub_cats'];
                                $show = true;
                                if (count($ad_cats) > 0) {
                                    parse_str($_SERVER['QUERY_STRING'], $search_params);
                                    unset($search_params['cat_id']);
                                    $new_params = http_build_query($search_params);


                                    $cat_params = '';
                                    $cls = '';
                                    $res .= '<ul class="city-select-city" >';
                                    foreach ($ad_cats as $ad_cat) {
                                        if ($new_params != "") {
                                            $cat_params = '?' . $new_params . '&cat_id=' . $ad_cat->term_id;
                                            $cat_link = get_the_permalink($adforest_theme['sb_search_page']) . $cat_params;
                                        } else {
                                            $cat_params = '?cat_id=' . $ad_cat->term_id;
                                            $cat_link = get_the_permalink($adforest_theme['sb_search_page']) . $cat_params;
                                        }

                                        $li_col = '3';
                                        if (isset($adforest_theme['sb_li_cols']) && $adforest_theme['sb_li_cols'] != "") {
                                            $li_col = $adforest_theme['sb_li_cols'];
                                        }


                                        if ($rows_count > $max_rows && $show) {
                                            $show = false;
                                            $res .= '<li class="col-md-12 col-sm-12 col-xs-12 hide_cats text-center margin-top-20">
                        <a href="javascript:void(0);" >
                        ' . __('Show more', 'adforest') . '
                        </a>
                        </li>';
                                            $cls = 'no-display show_it';
                                        }

                                        $res .= '<li class="col-md-' . esc_attr($li_col) . ' col-sm-6 col-xs-12 ' . esc_attr($cls) . '">
                        <a href="' . $cat_link . '" >
                        ' . $ad_cat->name . ' <span>(' . $ad_cat->count . ')</span>
                        </a>
                        </li>';
                                        $rows_count++;
                                    }
                                    $res .= '</ul>';
                                    ?>
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="adforest-new-filters">
                                            <div class="panel panel-default">
                                                <!-- Heading -->
                                                <div class="panel-heading" role="tab" id="headingOnez">
                                                    <!-- Title -->
                                                    <h4 class="panel-title">
                                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOnez" aria-expanded="true" aria-controls="collapseOnez">
                                                            <i class="more-less glyphicon glyphicon-plus"></i>
                                                            <?php
                                                            //echo __('Categories', 'adforest');
                                                            $title = adforest_get_taxonomy_parents($cat_id, 'ad_cats', false);
                                                            $find = '&raquo;';
                                                            $replace = '';
                                                            $result = preg_replace("/$find/", $replace, $title, 1);
                                                            echo adforest_returnEcho($result);
                                                            ?>
                                                        </a>
                                                    </h4>
                                                    <!-- Title End -->
                                                </div>
                                                <!-- Content -->
                                                <form>
                                                    <div id="collapseOnez" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOnez">
                                                        <div class="panel-body">
                                                            <div class="search-modal">
                                                                <div class="search-block">

                                                                    <?php echo adforest_returnEcho($res); ?>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>                        


                                    </div>
                                    <div class="clearfix"></div>
                                    <?php
                                }
                            }
                        }
                        ?>

  
                      <?php
                        if (isset($adforest_theme['feature_on_search']) && $adforest_theme['feature_on_search']) {
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
							
					
                            echo adforest_get_search_vip_ads_list($args);
                        }
						
						
						
						
						
						
						
						
						
						
						
						
						
                        if (isset($adforest_theme['search_ad_720_1']) && $adforest_theme['search_ad_720_1'] != "" && $results->have_posts()) {
                            ?>
                            <div class="col-md-12">
                                <div class="margin-bottom-30 margin-top-10 text-center">
                                    <?php echo "" . $adforest_theme['search_ad_720_1']; ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="clearfix"></div>
                        <?php
                        $layouts = array('list_1', 'list_2', 'list_3');
                        if ($results->have_posts()) {
                            $type = $adforest_theme['search_ad_layout_for_sidebar'];
                            $col = 6;
                            if (isset($adforest_theme['design_type']) && $adforest_theme['design_type'] == 'modern') {
                                $col = 4;
                            }

                            $get_grid_layout = adforest_get_grid_layout();
                            $search_ad_layout_for_sidebar = ($get_grid_layout != "" ) ? $get_grid_layout : $adforest_theme['search_ad_layout_for_sidebar'];
                            $type = ($get_grid_layout != "" ) ? $get_grid_layout : $adforest_theme['search_ad_layout_for_sidebar'];

                            if (in_array($search_ad_layout_for_sidebar, $layouts)) {

                                require trailingslashit(get_stylesheet_directory()) . "inc/search-layout-list.php";
                                echo adforest_returnEcho($out);
                            } else {

                                require trailingslashit(get_template_directory()) . "template-parts/layouts/ad_style/search-layout-grid.php";
                                echo adforest_returnEcho($out);
                            }

                            /* Restore original Post Data */
                            wp_reset_postdata();
                        } else {
                            echo '<h2 class="padding-left-20">' . esc_html__('No Result Found.', 'adforest') . '</h2>';
                        }
                        ?>
                        <div class="clearfix"></div>
                        <?php
                        if (isset($adforest_theme['search_ad_720_2']) && $adforest_theme['search_ad_720_2'] != "" && $results->have_posts()) {
                            ?>
                            <div class="col-md-12">
                                <div class="margin-top-10 margin-bottom-30 text-center">
                                    <?php echo "" . $adforest_theme['search_ad_720_2']; ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <!-- Pagination -->  
                        <div class="text-center margin-top-30 margin-bottom-20">
                            <?php adforest_pagination_search($results); ?>
                        </div>
                        <!-- Pagination End -->   
                    </div>
                    <!-- Row End -->
                </div>
                <!-- Middle Content Area  End -->
            </div>
            <!-- Row End -->
        </div>
        <!-- Main Container End -->
    </section>
</div>