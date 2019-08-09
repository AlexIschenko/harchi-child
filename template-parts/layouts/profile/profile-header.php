<div class="seller-public-profile-items clearfix">
    <div class="seller-public-profile-icons">
        <?php
        $social_icons = '<ul>';
        $profiles = adforest_social_profiles();
        foreach ($profiles as $key => $value) {
            if (get_user_meta($author->ID, '_sb_profile_' . $key, true) != "") {

                $social_icons .= '<li><a href="' . esc_url(get_user_meta($author->ID, '_sb_profile_' . $key, true)) . '" target="_blank"><i class="fa fa-' . $key . '"></i></a></li>';
            }
        }
        $social_icons .= '</ul>';
        echo adforest_returnEcho($social_icons);
        ?>
    </div>
    <div class="seller-public-profile-image">
        <a href="<?php echo adforest_set_url_param(get_author_posts_url($author->ID),'type','ads'); ?>">
			<img src="<?php echo esc_attr($user_pic); ?>" id="user_dp" alt="<?php echo __('Profile Picture', 'adforest'); ?>" class="img-responsive">
		</a>
	</div>
	
    <div class="seller-public-profile-details">
        <h2>
			<a href="<?php echo adforest_set_url_param(get_author_posts_url($author->ID),'type','ads'); ?>"><?php echo esc_html($author->display_name); ?></a>
		</h2>
		
        <span class="seller-public-product-link">					
			<?php echo __('[:ru]Адрес:[:ua]Адреса:[:]&nbsp;') . get_user_meta($author->ID, '_sb_address', true); ?>
		</span>
		
		<br>		
        <span class="seller-public-product-link">					
			<?php echo __('[:ru]Дата регистрации на сайте:[:ua]Дата реєстрації на сайті:[:]&nbsp;') . date_i18n("F Y", strtotime(get_userdata($author->ID)->user_registered)); ?>
		</span>
		
		<br>
		
        <span class="seller-public-product-link">
			<?php  printf( _x( 'Last active : %s Ago', 'Last login time', 'adforest' ), adforest_get_last_login($author->ID) ); ?>
		</span>		

        <div class="seller-public-profile-buttons">
            <?php
            if (get_user_meta($author->ID, '_sb_badge_type', true) != "" && get_user_meta($author->ID, '_sb_badge_text', true) != "" && isset($adforest_theme['sb_enable_user_badge']) && $adforest_theme['sb_enable_user_badge'] && $adforest_theme['sb_enable_user_badge'] && isset($adforest_theme['user_public_profile']) && $adforest_theme['user_public_profile'] != "" && $adforest_theme['user_public_profile'] == "modern") {
                ?>
                <button class="btn my-btn-updated <?php echo get_user_meta($author->ID, '_sb_badge_type', true); ?>">
                    <?php echo get_user_meta($author->ID, '_sb_badge_text', true); ?>
                </button>
                <?php
            }

            $user_type = '';
            if (get_user_meta($author->ID, '_sb_user_type', true) == 'Indiviual') {
                $user_type = __('Individual', 'adforest');
            } else if (get_user_meta($author->ID, '_sb_user_type', true) == 'Dealer') {
                $user_type = __('Dealer', 'adforest');
            }
            if ($user_type != "") {
                ?>
                <button class="btn my-btn label-success"><?php echo adforest_returnEcho($user_type); ?></button>
                <?php
            }
            ?>
        </div>
		<div class="seller-product-area-texts"><?php echo get_user_meta($author_id, '_sb_user_intro', true); ?></div>

    </div>
</div>