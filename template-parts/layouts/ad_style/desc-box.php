<?php
global $adforest_theme;
$pid = get_the_ID();


?>
<div class="descs-box" id="description">
    <?php get_template_part('template-parts/layouts/ad_style/status', 'watermark'); ?>
    <?php get_template_part('template-parts/layouts/ad_style/short', 'features'); ?>
    <!-- Short Features  --> 
    <div class="desc-points">
        <?php the_content(); ?>
    </div>
</div>	