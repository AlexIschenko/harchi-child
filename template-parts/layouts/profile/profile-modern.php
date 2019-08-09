<?php global $adforest_theme; ?>
<?php
$author_id = get_query_var('author');
$author = get_user_by('ID', $author_id);
$user_pic = adforest_get_user_dp($author_id, 'adforest-user-profile');
?>
<section class="seller-public-profile padding-top-50">
    <div class="container">
        <div class="row">

            <div class="col-lg-12"><!-- header -->
                <?php require trailingslashit(get_stylesheet_directory()) . 'template-parts/layouts/profile/profile-header.php'; ?>

                <div class="seller-product-trigger margin-top-20">
                    <div class="heading-panel">
                        <h3 class="main-title text-left"><?php echo __('Ad(s) posted by', 'adforest'); ?>
                            <span class="showed"><?php echo " " . $author->display_name; ?></span>
                        </h3>
                    </div>
                </div>
				

            </div>

			<!-- posts -->
			<?php 
			$html = '';
			if (have_posts() > 0 && in_array('sb_framework/index.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			while (have_posts()) {

					the_post();
					$pid = get_the_ID();
					$html .= ai_adforest_search_layout_list($pid);
				}
			} else {
				echo __('No record found.', 'adforest');
			}	
			
			
			echo '
			<div class="posts">
				<div class="col-md-12 col-xs-12 col-sm-12">
					' . $html . '
				</div>
			</div>';

			?>
			<div class="text-center">
				<?php adforest_pagination(); ?>
			</div>

        </div>
    </div>
</section>