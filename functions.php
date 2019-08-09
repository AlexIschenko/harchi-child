<?php
/*This file is part of adforest-child, adforest child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

function adforest_child_enqueue_child_styles() {
	
	wp_register_script( 'main', get_stylesheet_directory_uri() . '/js/main.js' );
    wp_enqueue_script( 'main' );
	
	$parent_style = 'parent-style'; 
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 
		'child-style', 
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get('Version')
	);
	wp_localize_script( 'main', 'mainajax', 
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' )
	));
}

	
add_action( 'wp_enqueue_scripts', 'adforest_child_enqueue_child_styles' );

/*Write here your own functions */


// update
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/AlexIschenko/harchi-child',
	__FILE__,
	'harchi-child'
);
$myUpdateChecker->setAuthentication('160840e846570b870e9ceba55f55b00b9bf90a11 ');






remove_filter('get_the_time', 'qtranxf_timeFromPostForCurrentLanguage',0,3);
remove_filter('get_the_date', 'qtranxf_dateFromPostForCurrentLanguage',0,3);
remove_filter('get_the_modified_date', 'qtranxf_dateModifiedFromPostForCurrentLanguage',0,2);



// Load translation files from your child theme instead of the parent theme
function ai_fter_setup_theme() {
    //load_child_theme_textdomain( 'adforest', get_stylesheet_directory() . '/languages' );
	
	//add_image_size( 'adforest-ad-related', 313, 0, true );
	add_image_size( 'adforest-single-post', 0, 440, true ); 

}
add_action( 'after_setup_theme', 'ai_fter_setup_theme',11 );


//add_action( 'plugins_loaded', 'myplugin_init' );
//function myplugin_init(){
//	load_plugin_textdomain( 'redux-framework', false, get_stylesheet_directory() . '/redux-languages' );
//}










// DELETE ATTACHMENTS ON POST DELETE FROM TRASH
add_action( 'before_delete_post', function( $post_id ) {
	$attachments = get_attached_media( '', $post_id );
	foreach ($attachments as $attachment) {
		wp_delete_attachment( $attachment->ID, 'true' );
	}
	$term = get_term_by('name', 'ad-'.$post_id, WPMF_TAXO);
	$term_id = $term->term_id;
	wp_delete_term( $term_id, WPMF_TAXO );
});





// DELETE USER MEDIA FOLDER ON USER DELETE
add_action( 'delete_user', function( $user_id ) {
	$term = get_term_by('name', 'user-'.$user_id, WPMF_TAXO);
	$term_id = $term->term_id;
	wp_delete_term( $term_id, WPMF_TAXO );
});





// MODIFY ADD AD FORM
include dirname(__FILE__) . '/inc/post_ad_form.php';





// UPLOAD TO AD FOLDER
if ( ! function_exists( 'adforest_upload_ad_images' ) ) {
function adforest_upload_ad_images(){
	
	global $adforest_theme;
	
	adforest_authenticate_check();
	
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	
	if( isset( $adforest_theme['sb_standard_images_size'] ) && $adforest_theme['sb_standard_images_size'] )
	{
		list($width, $height)	=	getimagesize($_FILES["my_file_upload"]["tmp_name"]);
		if( $width < 760 )
		{
			echo '0|' . __( "Minimum image dimension should be", 'adforest' ) . ' 760x410';
			die();
		}
		
		if( $height < 410 )
		{
			echo '0|' . __( "Minimum image dimension should be", 'adforest' ) . ' 760x410';
			die();
		}
	}
	
	
	$size_arr	=	explode( '-', $adforest_theme['sb_upload_size'] );
	$display_size	=	$size_arr[1];
	$actual_size	=	$size_arr[0];
	
	// Allow certain file formats
	$imageFileType	=	strtolower(end( explode('.', $_FILES['my_file_upload']['name'] ) ));
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
	&& $imageFileType != "gif" )
	{
		echo '0|' . __( "Sorry, only JPG, JPEG, PNG & GIF files are allowed.", 'adforest' );
		die();
	}
	 
	 // Check file size
	if ($_FILES['my_file_upload']['size'] > $actual_size) 
	{
		echo '0|' . __( "Max allowed image size is", 'adforest' ) . " " . $display_size;
		die();
	}
	
	
	// Let WordPress handle the upload.
	// Remember, 'my_image_upload' is the name of our file input in our form above.
	if( $_GET['is_update'] != "" )
	{
		$ad_id	=	$_GET['is_update'];
	}
	else
	{
		$ad_id	=	get_user_meta ( get_current_user_id(), 'ad_in_progress', true );
	}
	
	if($ad_id == "" )
	{
		echo '0|' . __( "Please enter title first in order to create ad.", 'adforest' );
		die();
	}
	
	// Check max image limit
	$media = get_attached_media( 'image',$ad_id );
	if( count( $media ) >= $adforest_theme['sb_upload_limit'] )
	{
		echo '0|' . __( "You can not upload more than ", 'adforest' ) . " " . $adforest_theme['sb_upload_limit'];
		die();
	}
	
	$attachment_id = media_handle_upload( 'my_file_upload', $ad_id );
	
	
	
	
	// create and set folder for ad
	
	$parent_name = 'user-'.get_current_user_id();
	$parent_term = get_term_by('name', $parent_name, WPMF_TAXO);
	$parent_term_id = $parent_term->term_id;
	wp_insert_term( 'ad-'.$ad_id, WPMF_TAXO, array(
		'parent'      => $parent_term_id,
	) );
	wp_set_object_terms( $attachment_id, 'ad-'.$ad_id, WPMF_TAXO, false);
	
	
	
	
	$imgaes	=	get_post_meta( $ad_id, '_sb_photo_arrangement_', true );
	if( $imgaes != "" )
	{
		$imgaes = $imgaes .',' . $attachment_id;
		update_post_meta( $ad_id, '_sb_photo_arrangement_', $imgaes );	
	}
	echo adforest_returnEcho($attachment_id);
	die();
    

}
}













// REGISTER USER AND CREATE WPMF TAXONOMY
if ( ! function_exists( 'adforest_register_user' ) ) {
	function adforest_register_user() {
        global $adforest_theme;
        // Getting values
        $params = array();
        parse_str($_POST['sb_data'], $params);

        if (email_exists($params['sb_reg_email']) == false) {

            $google_captcha_auth = false;
            $google_captcha_auth = adforest_recaptcha_verify($adforest_theme['google_api_secret'], $params['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'], $params['is_captcha']);
            $captcha_type = isset($adforest_theme['google-recaptcha-type']) && !empty($adforest_theme['google-recaptcha-type']) ? $adforest_theme['google-recaptcha-type'] : 'v2';


            if ($google_captcha_auth) {

                $user_name = explode('@', $params['sb_reg_email']);
                $u_name = adforest_check_user_name($user_name[0]);
                $uid = wp_create_user($u_name, $params['sb_reg_password'], sanitize_email($params['sb_reg_email']));
                wp_update_user(array('ID' => $uid, 'display_name' => sanitize_text_field($params['sb_reg_name'])));
                update_user_meta($uid, '_sb_contact', sanitize_text_field($params['sb_reg_contact']));
				
				// create folder for user
				wp_insert_term( 'user-'.$uid, WPMF_TAXO, array(
					'parent'      => 572,
				) );

                if ($adforest_theme['sb_allow_ads']) {
                    update_user_meta($uid, '_sb_simple_ads', $adforest_theme['sb_free_ads_limit']);
                    if ($adforest_theme['sb_allow_featured_ads']) {
                        update_user_meta($uid, '_sb_featured_ads', $adforest_theme['sb_featured_ads_limit']);
                    }
                    if ($adforest_theme['sb_allow_bump_ads']) {
                        update_user_meta($uid, '_sb_bump_ads', $adforest_theme['sb_bump_ads_limit']);
                    }
                    if ($adforest_theme['sb_package_validity'] == '-1') {
                        update_user_meta($uid, '_sb_expire_ads', $adforest_theme['sb_package_validity']);
                    } else {
                        $days = $adforest_theme['sb_package_validity'];
                        $expiry_date = date('Y-m-d', strtotime("+$days days"));
                        update_user_meta($uid, '_sb_expire_ads', $expiry_date);
                    }
                } else {
                    update_user_meta($uid, '_sb_simple_ads', 0);
                    update_user_meta($uid, '_sb_featured_ads', 0);
                    update_user_meta($uid, '_sb_bump_ads', 0);
                    update_user_meta($uid, '_sb_expire_ads', date('Y-m-d'));
                }

                update_user_meta($uid, '_sb_pkg_type', 'free');
                // Email for new user
                if (function_exists('adforest_email_on_new_user')) {
                    adforest_email_on_new_user($uid, '');
                }

                // check phone verification is on or not
                if (isset($adforest_theme['sb_phone_verification']) && $adforest_theme['sb_phone_verification'] && in_array('wp-twilio-core/core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                    update_user_meta($uid, '_sb_is_ph_verified', '0');
                }

                if (isset($adforest_theme['sb_new_user_email_verification']) && $adforest_theme['sb_new_user_email_verification']) {
                    $user = new WP_User($uid);
                    // Remove all user roles after registration
                    foreach ($user->roles as $role) {
                        $user->remove_role($role);
                    }
                    echo 2;
                    die();
                } else {
                    adforest_auto_login($params['sb_reg_email'], $params['sb_reg_password'], true);
                    echo 1;
                    die();
                }
            } else {

                if ($captcha_type == 'v3') {
                    echo __('You are spammer ! Get out.', 'adforest');
                } else {
                    echo __('please verify captcha code', 'adforest');
                }
                die();
            }
        } else {
            echo __('Email already exist, please try other one.', 'adforest');
            die();
        }


        die();
	 
}
}






// AJAX CHECK MESSAGES
if (!function_exists('adforest_check_messages')) {

    function adforest_check_messages() {
        adforest_authenticate_check();

        $user_id = get_current_user_id();
        $current_msgs = $_POST['new_msgs'];
        global $wpdb;
        $unread_msgs = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id = '$user_id' AND meta_value = '0' ");

        if ($unread_msgs > $current_msgs) {
			global $adforest_theme;
			
			if ($unread_msgs == 1) {
				$adforest_theme['msg_notification_text'] = __('[:ru]У вас %count% новое уведомление[:ua]У вас %count% нове повідомлення');
			} elseif ($unread_msgs >= 2 && $unread_msgs <= 4) {
				$adforest_theme['msg_notification_text'] = __('[:ru]У вас %count% новых уведомления[:ua]У вас %count% нових повідомлення');		
			}  elseif ($unread_msgs >= 5) {
				$adforest_theme['msg_notification_text'] = __('[:ru]У вас %count% новых уведомлений[:ua]У вас %count% нових повідомленнь');							
			}


			echo '1|' . str_replace('%count%', $unread_msgs, $adforest_theme['msg_notification_text']) . '|' . $unread_msgs;
			die();
        }
        
        if ($unread_msgs == 0) {
				echo '0|unread: ' . $unread_msgs . ', current: ' . $current_msgs . '|' . $unread_msgs;
				die();
        }
		
	    if ( $unread_msgs  <= $current_msgs && $unread_msgs != 0) {
				echo '2||' . $unread_msgs;
				die();
        }		
        
        die();
    }

}



// LOAD MESSEGES LIST
function adforest_get_messages() {
	
        adforest_authenticate_check();

        $ad_id = $_POST['ad_id'];
        $user_id = $_POST['user_id'];
        $authors = array($user_id, get_current_user_id());

        // Mark as read conversation
        update_comment_meta(get_current_user_id(), $ad_id . "_" . $user_id, 1);


        $parent = $user_id;
        if ($_POST['inbox'] == 'yes') {
            $parent = get_current_user_id();
        }
        $args = array(
            'author__in' => $authors,
            'post_id' => $ad_id,
            'parent' => $parent,
            'orderby' => 'comment_date',
            'order' => 'ASC',
        );
        $comments = get_comments($args);
        $i = 1;
        $total = count($comments);
        if (count($comments) > 0) {
            foreach ($comments as $comment) {
				
                $user_pic = '';
                $class = 'friend-message';
                if ($comment->user_id == get_current_user_id()) {
                    $class = 'my-message';
                }
                $user_pic = adforest_get_user_dp($comment->user_id);
                $id = '';
                if ($i == $total) {
                    $id = 'id="last_li"';
                }
                $i++;
                $messages .= '
					<li class="' . $class . ' clearfix" ' . $id . '>
						<div class="message_author">
							' . $comment->comment_author . '
						</div>
						<figure class="profile-picture">
							<img src="' . $user_pic . '" class="img-circle" alt="' . __('Profile Pic', 'adforest') . '">
						</figure>
						<div class="message">
							' . $comment->comment_content . '
							<div class="time"><i class="fa fa-clock-o"></i> ' . date('d.m.Y H:i',strtotime($comment->comment_date)) . '</div>
						</div>
					</li>
				';
            }
        }
		$messages .= '
			<div id="message_list_end"></div>
			<script>
				(function($) {
					
					var st = $(document).scrollTop();
					var vh = $(window).height();
					
					
					$("html, body").animate({
						scrollTop: $("#message_list_end").offset().top - vh + 200
					}, 500, "easeOutExpo");
					
					
					
				})(jQuery);
			</script>
		';
        echo adforest_returnEcho($messages);
        die();
    }




// LOAD MESSAGES ON CLICK
if (!function_exists('adforest_load_messages')) {
    function adforest_load_messages() {
        $ad_id = $_POST['ad_id'];
        $profile = new adforest_profile();
        $args = array(
            'post_type' => 'ad_post',
            'author' => $profile->user_info->ID,
            'post_status' => 'publish',
            'posts_per_page' => get_option('posts_per_page'),
            'paged' => $paged,
            'order' => 'DESC',
            'orderby' => 'ID'
        );

		global $wpdb;

		$rows = $wpdb->get_results("SELECT comment_author, user_id FROM $wpdb->comments WHERE comment_post_ID = '$ad_id' AND comment_type = 'ad_post'  GROUP BY user_id ORDER BY MAX(comment_date) DESC");

		$users = '';
		$messages = '';
		$author_html = '';
		$form = '<div class="text-center">' . __('No message received on this ad yet.', 'adforest') . '</div>';
		$turn = 1;
		$level_2 = '';
		
		foreach ($rows as $row) {
			if (get_current_user_id() == $row->user_id)
				continue;
			$user_dp = adforest_get_user_dp($row->user_id);

			$last_date = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments WHERE comment_post_ID = '$ad_id' AND user_id = '" . $row->user_id . "' AND comment_type = 'ad_post' ORDER BY comment_date DESC LIMIT 1");
			$date = explode(' ', $last_date);
			$cls = '';

			$msg_status = get_comment_meta(get_current_user_id(), $ad_id . "_" . $row->user_id, true);
			
			
			$status = '';
			$has_message = '';
			if ($msg_status == '0') {
				$status = '<i class="fa fa-envelope" aria-hidden="true"></i>';
				$has_message = " has_message";
			}
			
			
			$users .= '
				<li class="user_list ' . $cls . $has_message . '" cid="' . $ad_id . '" second_user="' . $row->user_id . '" id="sb_' . $row->user_id . '_' . $ad_id . '">
					 <a href="javascript:void(0);">
						<div class="image">
							<img src="' . $user_dp . '" alt="' . $row->comment_author . '">
						</div>
						<div class="user-name">
							<div class="author">
								<span>' . $row->comment_author . '</span>
							</div>
							<p>' . get_the_title($ad_id) . '</p>
							<div class="time" id="' . $row->user_id . '_' . $ad_id . '">
								' . $status . '
							</div>
						</div>
					</a>
				</li>
			';
			
			$authors = array($row->user_id, get_current_user_id());
			if ($turn == 1) {
				$args = array(
					'author__in' => $authors,
					'post_id' => $ad_id,
					'parent' => $row->user_id,
					'orderby' => 'comment_date',
					'order' => 'ASC',
				);
				$comments = get_comments($args);
				if (count($comments) > 0) {


                $level_2 = '<input type="hidden" id="usr_id" name="usr_id" value="' . $row->user_id . '" />
				<input type="hidden" id="rece_id" name="rece_id" value="' . $row->user_id . '" />
				<input type="hidden" name="msg_receiver_id" id="msg_receiver_id" value="' . esc_attr($row->user_id) . '" />
				';
                        foreach ($comments as $comment) {
                            $user_pic = '';
                            $class = 'friend-message';
                            if ($comment->user_id == get_current_user_id()) {
                                $class = 'my-message';
                            }
                            $user_pic = adforest_get_user_dp($comment->user_id);
                        }
						$messages = "<style>.chat-form {display: none;}</style>";
                    }

                    // Message form
                    $profile = new adforest_profile();
                    $form = '
					<form role="form" class="form-inline" id="send_message">
						<div class="form-group">
							<input type="hidden" name="ad_post_id" id="ad_post_id" value="' . $ad_id . '" />
							<input type="hidden" name="name" value="' . $profile->user_info->display_name . '" />
							<input type="hidden" name="email" value="' . $profile->user_info->user_email . '" />
							' . $level_2 . '
							<textarea rows="4" name="message" id="sb_forest_message" placeholder="' . __('Type a message here...', 'adforest') . '" class="form-control message-text" autocomplete="off" data-parsley-required="true" data-parsley-error-message="' . __('This field is required.', 'adforest') . '"></textarea>
						</div>
						<button class="btn btn-theme" id="send_msg" type="submit" inbox="no">' . __('Send', 'adforest') . '</button>
					</form>
					';
					
                }
                $turn++;
            }
			// end foreach
			
			
			
            if ($users == '') {
                $users = '<li class="padding-top-30 padding-bottom-20"><div class="user-name">' . __('No message received on this ad yet.', 'adforest') . '</div></li>';
            }
			
            $title = '';
            if (isset($ad_id) && $ad_id != "") {
                $title = '<a href="' . get_the_permalink($ad_id) . '" target="_blank">' . get_the_title($ad_id) . '</a>';
            }	
			
			// выберите сообщение ----------------------------------------------------------
			$msg = '<div class="text-center">' . __('Please click to your ad in order to see messages.', 'adforest') . '</div>';
            
			
			
            echo '
			<div class="messages_top_bar">
				<div class="message-header_nav">
					<span><a class="messages_actions" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
					<span class="actions_divider">|</span>
					<span><a class="messages_actions" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
					<span class="actions_divider">|</span>
					<span><a class="messages_actions active" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
				</div>
			</div>
			
			<div class="messages_header">
				<h4>' . $title . '</h4>			
			</div>
			
			<div class="message-body">
				<div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
					<div class="message-inbox">
						<ul class="message-history">
							' . $users . '
						</ul>
					</div>
				</div>
				<div class="col-md-8 clearfix col-xs-12 message-content">
					' . $title_html2 . '
					<div class="message-details">
						<ul class="messages" id="messages">
							' . $msg . '
							<style>.chat-form {display: none;}</style>
					   </ul>
					   <div class="chat-form">
							' . $form . '
						</div>
					</div>
				</div>
			</div>

			<script>
				jQuery(document).ready(function($) {
					var has_message = $("li.has_message");
					var first = $("li.user_list")[0];
					has_message.each(function() {
						$(first).before(this);
					});
				});
			</script>
			';

        die();
    }
}
	

	
	
	
	
	
	

//	INBOX
if (!function_exists('adforest_received_msgs_ads_list')) {

    function adforest_received_msgs_ads_list() {
		
            global $adforest_theme;
            global $wpdb;
            $profile = new adforest_profile();
            $args = array(
                'post_type' => 'ad_post',
                'author' => $profile->user_info->ID,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'paged' => $paged,
                'order' => 'DESC',
                'orderby' => 'ID',
            );


            $ads = new WP_Query($args);

            if ($ads->have_posts()) {
                $number = 0;
                $ads_list = '';
                while ($ads->have_posts()) {
                    $ads->the_post();
                    $pid = get_the_ID();

                    $ad_img = $adforest_theme['default_related_image']['url'];
                    $media = adforest_get_ad_images($pid);
                    if (count($media) > 0) {
                        foreach ($media as $m) {
                            $mid = '';
                            if (isset($m->ID))
                                $mid = $m->ID;
                            else
                                $mid = $m;

                            $img = wp_get_attachment_image_src($mid, 'adforest-ad-related');
                            $ad_img = $img[0];
                            break;
                        }
                    }

                    $is_unread_msgs = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id = '" . get_current_user_id() . "' AND meta_value = '0' AND meta_key like '" . $pid . "_%'");

                    $status = '';
					$has_message = '';
                    if ($is_unread_msgs > 0) {
                        $status = '<i class="fa fa-envelope" aria-hidden="true"></i>';
						$has_message = " has_message";
                    }

                    $ads_list .= '<li class="get_msgs' . $has_message  . '" ad_msg="' . esc_attr($pid) . '"><a href="javascript:void(0);">
							<div class="image">
							   <img src="' . $ad_img . '" alt="' . get_the_title($pid) . '">
							</div>
							<div class="user-name">
							   <div class="author">
								  <span>' . get_the_title($pid) . '</span>
							   </div>
							   <div class="time">
								  ' . $status . '
							   </div>
							</div>
						 </a>
						 </li>';
                }
            }

			if ($ads_list == '') {
				$msg = '<div class="text-center">' . __('[:en]You have no inbox[:ru]У вас нет входящих сообщений[:ua]У вас немає вхідних повідомлень') . '</div>';
			} else {
				$msg = '<div class="text-center">' . __('Please click to your ad in order to see messages.', 'adforest') . '</div>';
			}
            
			
            echo '
			<div class="messages_top_bar">
				<div class="message-header_nav">
					<span><a class="messages_actions" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
					<span class="actions_divider">|</span>
					<span><a class="messages_actions" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
					<span class="actions_divider">|</span>
					<span><a class="messages_actions active" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
				</div>
			</div>
			
			<div class="messages_header">
				<h4>' . __("Received Offers", "adforest") . '</h4>			
			</div>
			
			   <div class="message-body">
				 <div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
					<div class="message-inbox">
						<ul class="message-history">
							' . $ads_list . '
						</ul>
					</div>
				 </div>
				 <div class="col-md-8 clearfix col-xs-12 message-content">
					<div class="message-details">
				   <ul class="messages" id="messages">
						' . $msg . '
				   </ul>
					</div>
				 </div>
			  </div>
		   <script>
				jQuery(document).ready(function($) {
					var has_message = $("li.has_message");
					var first = $("li.get_msgs")[0];
					has_message.each(function() {
						$(first).before(this);
					});
				});
		   </script>
			';

        die();
    }

}








// MESSAGES LINK IN NAV MENU
function adforest_header_messages_callback() {
	$profile_type = isset($_GET['type']) && !empty($_GET['type']) ? $_GET['type'] : '';
	if ($profile_type == 'messages') {
		?>
		<script>
			jQuery(document).ready(function () {
				jQuery('.menu-name[sb_action="my_msgs"]').click();
				var uri = window.location.toString();
				if (uri.indexOf("?") > 0) {
					var clean_uri = uri.substring(0, uri.indexOf("?"));
					window.history.replaceState({}, document.title, clean_uri);
				}
			});
		</script>
		<?php
	}
	if (class_exists('Redux')) {
		$hide_captcha_badge = Redux::getOption('adforest_theme', 'hide_captcha_badge');
	}
	$hide_captcha_badge = isset($hide_captcha_badge) ? $hide_captcha_badge : false;


	if (isset($hide_captcha_badge) && $hide_captcha_badge) {
		?>
		<style>
			.grecaptcha-badge {
				display: none;
			}
		</style>
		<?php
	}
}
add_action('wp_footer', 'adforest_header_messages_callback');
	







	
	
	
	
	
	
// OUTBOX
add_action('wp_ajax_my_msgs_outbox', 'adforest_my_msgs_outbox');
if (!function_exists('adforest_my_msgs_outbox')) {

    function adforest_my_msgs_outbox() {

        $profile = new adforest_profile();
        $ads = new ads();
		$user_id = $profile->user_info->ID;

		global $adforest_theme;
		global $wpdb;

		$rows = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_type = 'ad_post' AND user_id = '$user_id' AND comment_parent = '$user_id' GROUP BY comment_post_ID ORDER BY comment_ID DESC");

		$users = '';
		$messages = '';
		$form = '<div class="text-center">' . __('No message received on this ad yet.', 'adforest') . '</div>';
		$author_html = '';
		$turn = 1;
		$level_2 = '';
		$title_html = '<span class="sb_ad_title">' . __("Sent Offers", "adforest") . '</span>';
		foreach ($rows as $row) {
			$last_date = $row->comment_date;
			$date = explode(' ', $last_date);
			$author = get_post_field('post_author', $row->comment_post_ID);
			$cls = '';

			$ad_img = $adforest_theme['default_related_image']['url'];
			$media = adforest_get_ad_images($row->comment_post_ID);
			if (count($media) > 0) {
				foreach ($media as $m) {
					$mid = '';
					if (isset($m->ID))
						$mid = $m->ID;
					else
						$mid = $m;

					$img = wp_get_attachment_image_src($mid, 'adforest-ad-related');
					$ad_img = $img[0];
					break;
				}
			}


			if (isset($row->comment_post_ID) && $row->comment_post_ID != "") {
				$title_html .= '<span class="sb_ad_title no-display" id="title_for_' . esc_attr($row->comment_post_ID) . '" ><a href="' . get_the_permalink($row->comment_post_ID) . '" target="_blank" >' . get_the_title($row->comment_post_ID) . '</a></span>';
			}


			$ad_id = $row->comment_post_ID;
			$comment_author = get_userdata($author);

			$msg_status = get_comment_meta(get_current_user_id(), $ad_id . "_" . $author, true);
			$status = '';
			$has_message = '';
			if ($msg_status == '0') {
				$status = '<i class="fa fa-envelope" aria-hidden="true"></i>';
				$has_message = " has_message";
			}

			$users .= '
				<li class="user_list ad_title_show ' . $cls . $has_message . '" cid="' . $row->comment_post_ID . '" second_user="' . $author . '" inbox="yes" id="sb_' . $author . '_' . $ad_id . '">
					<a href="javascript:void(0);">
						<div class="image">
							<img src="' . $ad_img . '" alt="' . $comment_author->display_name . '">
						</div>
						<div class="user-name">
							<div class="author">
								<span>' . get_the_title($ad_id) . '</span>
							</div>
							<p>' . $comment_author->display_name . '</p>
							<div class="time" id="' . $author . '_' . $ad_id . '">
								' . $status . '
							</div>
						</div>
					</a>
				</li>
			';
			
			$authors = array($author, get_current_user_id());
			if ($turn == 1) {
				$args = array(
					'author__in' => $authors,
					'post_id' => $ad_id,
					'parent' => get_current_user_id(),
					'post_type' => 'ad_post',
					'orderby' => 'comment_date',
					'order' => 'ASC',
				);
				$comments = get_comments($args);
				if (count($comments) > 0) {

					foreach ($comments as $comment) {
						$user_pic = '';
						$class = 'friend-message';
						if ($comment->user_id == get_current_user_id()) {
							$class = 'my-message';
						}
						$user_pic = adforest_get_user_dp($comment->user_id);
						$messages .= '
							<li class="' . $class . ' clearfix">
								<figure class="profile-picture">
									<a href="' . get_author_posts_url($comment->user_id) . '?type=ads" class="link" target="_blank">
										<img src="' . $user_pic . '" class="img-circle" alt="' . __('Profile Pic', 'adforest') . '">
									</a>
								</figure>
								<div class="message">
									' . $comment->comment_content . '
									<div class="time"><i class="fa fa-clock-o"></i> ' . date('d.m.Y H:i',strtotime($comment->comment_date)) . '</div>
								</div>
							</li>
						';
					}
				}

				// Message form
				$profile = new adforest_profile();
				$level_2 = '
					<input type="hidden" name="usr_id" value="' . $user_id . '" />
					<input type="hidden" id="usr_id" value="' . $author . '" />
					<input type="hidden" id="rece_id" name="rece_id" value="' . $author . '" />
					<input type="hidden" name="msg_receiver_id" id="msg_receiver_id" value="' . esc_attr($author) . '" />
				';
				$form = '
					<form role="form" class="form-inline" id="send_message">
						<div class="form-group">
							<input type="hidden" name="ad_post_id" id="ad_post_id" value="' . $ad_id . '" />
							<input type="hidden" name="name" value="' . $profile->user_info->display_name . '" />
							<input type="hidden" name="email" value="' . $profile->user_info->user_email . '" />
							' . $level_2 . '
							<textarea rows="4" name="message" id="sb_forest_message" placeholder="' . __('Type a message here...', 'adforest') . '" class="form-control message-text" autocomplete="off" data-parsley-required="true" data-parsley-error-message="' . __('This field is required.', 'adforest') . '"></textarea>
						</div>
						<button class="btn btn-theme" id="send_msg" type="submit" inbox="yes">' . __('Send', 'adforest') . '</button>
					</form>
					';
			}
			$turn++;
		}
		

		if ($users == '') {
			$msg = '<div class="text-center">' . __('[:en]You have no sent messages[:ru]У вас нет отправленных сообщений[:ua]У вас немає відправлених повідомлень') . '</div>';
		} else {
			$msg = '<div class="text-center">' . __('Please click to your ad in order to see messages.', 'adforest') . '</div>';
		}

		echo '
		<div class="messages_top_bar">
			<div class="message-header_nav">
				<span><a class="messages_actions" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
				<span class="actions_divider">|</span>
				<span><a class="messages_actions active" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
				<span class="actions_divider">|</span>
				<span><a class="messages_actions" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
			</div>
		</div>
		
		<div class="messages_header">
			<h4>' . $title_html . '</h4>
		</div>
		
		<div class="message-body">
			<div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
				<div class="message-inbox">
					<ul class="message-history">
						' . $users . '
					</ul>
				</div>
			 </div>
			 <div class="col-md-8 clearfix col-xs-12 message-content">
				<div class="message-details">
				   <ul class="messages" id="messages">
						' . $msg . '
						<style>.chat-form {display: none;}</style>
				   </ul>
				   <div class="chat-form ">
					  ' . $form . '
				   </div>
				</div>
			 </div>
		  </div>
		</div>
		<script>
			jQuery(document).ready(function($) {
				var has_message = $("li.has_message");
				var first = $("li.user_list")[0];
				has_message.each(function() {
					$(first).before(this);
				});
			});
		</script>
		';
        die();
    }

}
	
	
	
	
	
	
// DEFAULT MESSAGES (NOTIFICATIONS PAGE)
if (!function_exists('adforest_my_msgs')) {

    function adforest_my_msgs() {
		
		echo '
		<div class="messages_top_bar">
			<div class="message-header_nav">
				<span><a class="messages_actions active" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
				<span class="actions_divider">|</span>
				<span><a class="messages_actions" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
				<span class="actions_divider">|</span>
				<span><a class="messages_actions" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
			</div>
		</div>
		
		<div class="messages_header">
			<h4>' . __("See all notifications", "adforest") . '</h4>
		</div>		
		
		
		<div class="message-body">
			 <div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
				<div class="message-inbox">
					<ul class="message-history">
					</ul>
				</div>
			</div>
			<div class="col-md-8 clearfix col-xs-12 message-content">
				<div class="message-details">
				   <ul class="messages" id="messages">';

						$user_id = get_current_user_id();
						$user_info = get_userdata( $user_id );
						global $wpdb;
						$notes = $wpdb->get_results( "SELECT * FROM $wpdb->commentmeta WHERE comment_id = '$user_id' AND  meta_value = 0 ORDER BY meta_id DESC LIMIT 30", OBJECT );							
						$unread_msgs = count( $notes );
						$msg_count = $unread_msgs;
						
						if ($unread_msgs == 1) {
							$notification_header_text = __('[:en]You have<[:ru]У вас[:ua]У вас') . ' <span class="msgs_count">' . $unread_msgs . '</span> '  . __('[:en]new notification<[:ru]новое уведомление[:ua]нове повідомлення');
						} elseif ($unread_msgs == '' ) {
							$notification_header_text = __('[:en]You have no new notifications<[:ru]У вас нет новых уведомлений[:ua]У вас немає нових повідомлень');
						} elseif ($unread_msgs >= 2 && $unread_msgs <= 4) {
							$notification_header_text = __('[:en]You have<[:ru]У вас[:ua]У вас') . ' <span class="msgs_count">' . $unread_msgs . '</span> '  . __('[:en]new notifications<[:ru]новых уведомления[:ua]нових повідомлення');							
						}  elseif ($unread_msgs >= 5) {
							$notification_header_text = __('[:en]You have<[:ru]У вас[:ua]У вас') . ' <span class="msgs_count">' . $unread_msgs . '</span> '  . __('[:en]new notifications<[:ru]новых уведомлений[:ua]нових повідомлень');							
						} 

						echo '
						<li>
							<div class="drop-title">
								' . $notification_header_text . '
							</div>
						</li>
						<li>
							<div class="message-center">';
								if( $unread_msgs > 0 ) { 

									if( count( $notes ) > 0 ) {

										foreach( $notes as $note ) {
											$ad_img	=	$adforest_theme['default_related_image']['url'];
											$get_arr	=	explode( '_', $note->meta_key );
											$ad_id = $get_arr[0];
											$media	=	 adforest_get_ad_images($ad_id);
											if( count( $media ) > 0 ) {
												$counting	=	1;
												foreach( $media as $m ) {
													if( $counting > 1 ) {
														$mid	=	'';
														if ( isset( $m->ID ) ) {
															$mid = $m->ID;
														} else {
															$mid = $m;
														}
														$image  = wp_get_attachment_image_src( $mid, 'adforest-single-small');
														if( $image[0] != "" ) {
															$ad_img = $image[0];	
														}
														break;
													}
													$counting++;	
												}
											}
							
											$action = get_the_permalink( $adforest_theme['sb_profile_page'] ) . '?sb_action=sb_get_messages'.  '&ad_id=' . $ad_id  .  '&user_id=' . $user_id .'&uid=' . $get_arr[1];
											$poster_id	=	get_post_field( 'post_author', $ad_id );
											if( $poster_id == $user_id ) {
												$action = get_the_permalink( $adforest_theme['sb_profile_page'] ) . '?sb_action=sb_load_messages' .  '&ad_id=' . $ad_id .  '&uid=' . $get_arr[1];
											}
											$user_data	=	get_userdata( $get_arr[1] );
											$user_pic	=	adforest_get_user_dp($get_arr[1]);

											echo '<a href="' . esc_url( $action ) . '">
												<div class="user-img"> <img src="' . esc_url( $user_pic ) . '" alt="' . adforest_returnEcho($user_data->display_name) . '" width="30" height="50" > </div>
												<div class="mail-contnet">
													<h5>' . adforest_returnEcho($user_data->display_name) . '</h5> <span class="mail-desc">' . get_the_title( $ad_id ) . '</span></div>
											</a>';

										}
									}
								}

					  
								echo '
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		';
		
        die();
    }

}












		
		
// PROFILE PAGE MAIN SHORTCODE
if ( !function_exists ( 'profile_short_base_func' ) ) {
	function profile_short_base_func($atts, $content = '') {
		extract(shortcode_atts(array(
			'profile_layout' => '',
		) , $atts));
		
		$profile	=	new adforest_profile();
		adforest_user_not_logged_in();
		
		// REMOVE DELETED ADS FROM FAVORITES
		$user_id = get_current_user_id();
		if (is_user_logged_in()) {		
			global $wpdb;
			$user_info = get_userdata($user_id);			
			$rows = $wpdb->get_results("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$user_id' AND meta_key LIKE '_sb_fav_id_%'");
			foreach ($rows as $row) {
				$ad_id = $row->meta_value;	
				if (get_post_status ($ad_id) == "" || get_post_status ($ad_id) == "trash") {
					delete_user_meta($user_id, '_sb_fav_id_'.$ad_id, $ad_id);
				}
			}
		}
		
		return ' 
			 <section class="section-padding bg-gray" >
				<!-- Main Container -->
				<div class="container">
				   '.ai_adforest_profile_full_top().'
				   <br>
				   '.ai_adforest_profile_full_body().'
				</div>
				<!-- Main Container End -->
			 </section>
		';

					

	}
}
function ai_adforest_profile_full_top() {
	$user = new adforest_profile;
	$user->user_info = get_userdata(get_current_user_id());
	$user_pic = adforest_get_user_dp($user->user_info->ID, 'adforest-user-profile');

	global $adforest_theme;
	$msgs = '';
	if ($adforest_theme['communication_mode'] == 'both' || $adforest_theme['communication_mode'] == 'message') {
		$msgs = '
		<li>
		  <a href="javascript:void(0);">
			 <div class="menu-name" sb_action="my_msgs"><span>' . __('Messages', 'adforest') . '</span></div>
		  </a>
	   </li>
	   ';
	}

	$packages = '';
	$order_history = '';
	if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$packages = '
		<li>
		  <a href="' . get_the_permalink($adforest_theme['sb_packages_page']) . '" target="_blank">
			 <div class="menu-name" sb_action="">' . __('Packages', 'adforest') . '</div>
		  </a>
	   </li>
	   ';

		$order_history = '
		<li>
		  <a href="javascript:void(0);">
			 <div class="menu-name" sb_action="my_orders">' . __('Package history', 'adforest') . '</div>
		  </a>
	    </li>
	    ';
	}
	$package_type_html = '';
	if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$package_type = get_user_meta($user->user_info->ID, '_sb_pkg_type', true);
		if (get_user_meta($user->user_info->ID, '_sb_pkg_type', true) != 'free') {
			$package_type = __('Paid', 'adforest');
		} else {
			$package_type = __('Free', 'adforest');
		}
		$package_type_html = '<span class="label label-warning">' . $package_type . '</span>';
	}
	$rating = '';
	if (isset($adforest_theme['user_public_profile']) && $adforest_theme['user_public_profile'] != "" && $adforest_theme['user_public_profile'] == "modern" && isset($adforest_theme['sb_enable_user_ratting']) && $adforest_theme['sb_enable_user_ratting']) {

		$rating = '
		<a href="' . get_author_posts_url($user->user_info->ID) . '?type=1">
			<div class="rating">';
				$got = get_user_meta($user->user_info->ID, "_adforest_rating_avg", true);
				if ($got == "")
					$got = 0;
				for ($i = 1; $i <= 5; $i++) {
					if ($i <= round($got))
						$rating .= '<i class="fa fa-star"></i>';
					else
						$rating .= '<i class="fa fa-star-o"></i>';
				}
				$rating .= '
				<span class="rating-count">
					(' . count(adforest_get_all_ratings($user->user_info->ID)) . ')
				</span>
			</div>
		</a>';
	}

	$badge = '';
	if (get_user_meta($user->user_info->ID, '_sb_badge_type', true) != "" && get_user_meta($user->user_info->ID, '_sb_badge_text', true) != "" && isset($adforest_theme['sb_enable_user_badge']) && $adforest_theme['sb_enable_user_badge'] && $adforest_theme['sb_enable_user_badge'] && isset($adforest_theme['user_public_profile']) && $adforest_theme['user_public_profile'] != "" && $adforest_theme['user_public_profile'] == "modern") {
		$badge = '
		<span class="label ' . get_user_meta($user->user_info->ID, '_sb_badge_type', true) . '">
		' . get_user_meta($user->user_info->ID, '_sb_badge_text', true) . '
		</span>
		';
	}

	$user_type = '';
	if (get_user_meta($user->user_info->ID, '_sb_user_type', true) == 'Indiviual') {
		$user_type = __('Individual', 'adforest');
	} else if (get_user_meta($user->user_info->ID, '_sb_user_type', true) == 'Dealer') {
		$user_type = __('Dealer', 'adforest');
	}

	$profile_html = '';
	$profiles = adforest_social_profiles();
	foreach ($profiles as $key => $value) {
		if (get_user_meta($user->user_info->ID, '_sb_profile_' . $key, true) != "")
			$profile_html .= '<li><a href="' . esc_url(get_user_meta($user->user_info->ID, '_sb_profile_' . $key, true)) . '" class="fa fa-' . $key . '" target="_blank"></a></li>';
	}

	return '
	<div class="row">
		<div class="col-md-12 col-xs-12 col-sm-12">
			<div class="user_profile_header">
				<section class="search-result-item">
					<div class="image-link" href="javascript:void(0);">
						<img class="image" alt="' . __('Profile Picture', 'adforest') . '" src="' . $user_pic . '" id="user_dp">
						<ul class="social-f">
							' . $profile_html . '
						</ul>
					</div>
					<div class="search-result-item-body">
						<div class="row">
							<div class="col-md-5 col-sm-12 col-xs-12">
			
								<h4 class="search-result-item-heading sb_put_user_name">' . $user->user_info->display_name . '</h4>
								<p class="info">
									<span class="profile_tabs" sb_action="get_profile"><i class="fa fa-user"></i>&nbsp; ' . __('Profile', 'adforest') . '</span>
									<span class="actions_divider">|</span>
									<span class="profile_tabs" sb_action="update_profile"><i class="fa fa-edit"></i>&nbsp; ' . __('Edit Profile', 'adforest') . '</span>
								</p>
								<p class="info sb_put_user_address">' . get_user_meta($user->user_info->ID, '_sb_address', true) . '</p>
								<p class="description">' . __('Last active', 'adforest') . ': ' . adforest_get_last_login($user->user_info->ID) . ' ' . __('Ago', 'adforest') . '</p>
								' . $package_type_html . '
								<span class="label label-success sb_user_type">' . $user_type . '</span>
								' . $badge . '
								' . $rating . '
	
							</div>
							
							
							<div class="col-md-7 col-sm-12 col-xs-12">
								<div class="row ad-history">
									<div class="col-md-4 col-sm-4 col-xs-12">
										<div class="user-stats">
											<h2>' . adforest_get_sold_ads($user->user_info->ID) . '</h2>
											<small>' . __('Ad Sold', 'adforest') . '</small>
										</div>
									</div>
									<div class="col-md-4 col-sm-4 col-xs-12">
										<div class="user-stats">
											<h2>' . adforest_get_all_ads($user->user_info->ID) . '</h2>
											<small>' . __('Total Listings', 'adforest') . '</small>
										</div>
									</div>
									<div class="col-md-4 col-sm-4 col-xs-12">
										<div class="user-stats">
											<h2>' . adforest_get_disbale_ads($user->user_info->ID) . '</h2>
											<small>' . __('Inactve ads', 'adforest') . '</small>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				<div class="dashboard-menu-container">
					<ul>
						<li>
							<a href="javascript:void(0);">
								<div class="menu-name" sb_action="my_ads">' . __('My Ads', 'adforest') . '</div>
							</a>
						</li>
						<li>
							<a href="javascript:void(0);">
								<div class="menu-name" sb_action="my_inactive_ads">' . __('Inactive Ads', 'adforest') . '</div>
							</a>
						</li>
						<li>
							<a href="javascript:void(0);">
								<div class="menu-name" sb_action="my_feature_ads">' . __('Featured Ads', 'adforest') . '</div>
							</a>
						</li>
						<li>
							<a href="javascript:void(0);">
								<div class="menu-name" sb_action="my_fav_ads" id="adforest-fav-ads">' . __('Fav Ads', 'adforest') . '</div>
							</a>
						</li>
						' . $msgs . '
						' . $packages . '
						' . $order_history . '
					</ul>
				</div>
			</div>
		</div>
	</div>
	';
}
function ai_adforest_profile_full_body() {
	if (isset($_GET['sb_action']) && isset($_GET['ad_id']) && isset($_GET['uid']) && $_GET['sb_action'] == 'sb_load_messages') {
		$script = "
			<script>
				jQuery(document).ready(function($){
					adforest_select_msg('$_GET[ad_id]', '$_GET[uid]', 'no');
				});
			</script>
		";
		$ads = new ads();
		return '<div id="adforest_res">
			' . adforest_load_messages_by_ad_id($_GET['ad_id']) . '
			</div>
			' . $script . '
		';
	} else if (isset($_GET['sb_action']) && isset($_GET['ad_id']) && isset($_GET['uid']) && isset($_GET['user_id']) && $_GET['sb_action'] == 'sb_get_messages') {
		$script = "
			<script>
				jQuery(document).ready(function($){
					adforest_select_msg('$_GET[ad_id]', '$_GET[uid]', 'yes');
					$('.message-history').find('li.message-history-active').prependTo('.message-history');					
				});
			</script>
		";
		$ads = new ads();
		return '<div id="adforest_res">
			' . adforest_load_messages_by_user_id($_GET['user_id']) . '
			</div>
			' . $script . '
		';
	} else {
		$profile = new adforest_profile();
		return '<div id="adforest_res">
			' . $profile->adforest_profile_get() . '
			</div>
		';
	}
}
function adforest_load_messages_by_ad_id($ad_id) {
	$script = '';


	global $wpdb;

	$rows = $wpdb->get_results("SELECT comment_author, user_id FROM $wpdb->comments WHERE comment_post_ID = '$ad_id' AND comment_type = 'ad_post'  GROUP BY user_id ORDER BY MAX(comment_date) DESC");

	$users = '';
	$messages = '';
	$author_html = '';
	$form = '<div class="text-center">' . __('No message received on this ad yet.', 'adforest') . '</div>';
	$turn = 1;
	$level_2 = '';
	foreach ($rows as $row) {
		if (get_current_user_id() == $row->user_id)
			continue;
		$user_dp = adforest_get_user_dp($row->user_id);

		$last_date = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments WHERE comment_post_ID = '$ad_id' AND user_id = '" . $row->user_id . "' AND comment_type = 'ad_post' ORDER BY comment_date DESC LIMIT 1");
		$date = explode(' ', $last_date);
		$cls = '';
		if ($turn == 1)
			$cls = 'message-history-active';

		$msg_status = get_comment_meta(get_current_user_id(), $ad_id . "_" . $row->user_id, true);
		$status = '';
		$has_message = '';
		if ($msg_status == '0') {
			$status = '<i class="fa fa-envelope" aria-hidden="true"></i>';
		}
		$users .= '
			<li class="user_list ' . $cls . '" cid="' . $ad_id . '" second_user="' . $row->user_id . '" id="sb_' . $row->user_id . '_' . $ad_id . '">
				<a href="javascript:void(0);">
					<div class="image">
					   <img src="' . $user_dp . '" alt="' . $row->comment_author . '">
					</div>
					<div class="user-name">
					   <div class="author">
						  <span>' . $row->comment_author . '</span>
					   </div>
					   <p>' . get_the_title($ad_id) . '</p>
					   <div class="time" id="' . $row->user_id . '_' . $ad_id . '">
							' . $status . '
					   </div>
					</div>
				</a>
			</li>
		';
		$authors = array($row->user_id, get_current_user_id());
		if ($turn == 1) {
			$args = array(
				'author__in' => $authors,
				'post_id' => $ad_id,
				'parent' => $row->user_id,
				'orderby' => 'comment_date',
				'order' => 'ASC',
			);
			$comments = get_comments($args);
			if (count($comments) > 0) {


				$level_2 = '
					<input type="hidden" id="usr_id" name="usr_id" value="' . $row->user_id . '" />
					<input type="hidden" id="rece_id" name="rece_id" value="' . $row->user_id . '" />
					<input type="hidden" name="msg_receiver_id" id="msg_receiver_id" value="' . esc_attr($row->user_id) . '" />
				';
				foreach ($comments as $comment) {
					$user_pic = '';
					$class = 'friend-message';
					if ($comment->user_id == get_current_user_id()) {
						$class = 'my-message';
					}
					$user_pic = adforest_get_user_dp($comment->user_id);
					$messages .= '
						<li class="' . $class . ' clearfix">
								 <figure class="profile-picture">
									<a href="' . get_author_posts_url($comment->user_id) . '?type=ads" class="link" target="_blank">
										<img src="' . $user_pic . '" class="img-circle" alt="' . __('Profile Pic', 'adforest') . '">
									</a>
								</figure>
								<div class="message">
									' . $comment->comment_content . '
									<div class="time"><i class="fa fa-clock-o"></i> ' . date('d.m.Y H:i',strtotime($comment->comment_date)) . '</div>
								 </div>
						</li>
					';
				}
			}

			// Message form
			$profile = new adforest_profile();
			$form = '
				<form role="form" class="form-inline" id="send_message">
					<div class="form-group">
						<input type="hidden" name="ad_post_id" id="ad_post_id" value="' . $ad_id . '" />
						<input type="hidden" name="name" value="' . $profile->user_info->display_name . '" />
						<input type="hidden" name="email" value="' . $profile->user_info->user_email . '" />
						 ' . $level_2 . '
						<textarea rows="4" name="message" id="sb_forest_message" placeholder="' . __('Type a message here...', 'adforest') . '" class="form-control message-text" autocomplete="off" data-parsley-required="true" data-parsley-error-message="' . __('This field is required.', 'adforest') . '"></textarea>
					</div>
					<button class="btn btn-theme" id="send_msg" type="submit" inbox="no">' . __('Send', 'adforest') . '</button>
				</form>
			';
		}
		$turn++;
	}// end foreach
	
	if ($users == '') {
		$users = '<li class="padding-top-30 padding-bottom-20"><div class="user-name">' . __('No message received on this ad yet.', 'adforest') . '</div></li>';
	}
	$title = '';
	if (isset($ad_id) && $ad_id != "") {
		$title = '<a href="' . get_the_permalink($ad_id) . '" target="_blank">' . get_the_title($ad_id) . '</a>';
	}

	return '
	<div class="messages_top_bar">
		<div class="message-header_nav">
			<span><a class="messages_actions" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
			<span class="actions_divider">|</span>
			<span><a class="messages_actions" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
			<span class="actions_divider">|</span>
			<span><a class="messages_actions active" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
		</div>
	</div>

	<div class="messages_header">
		<h4>' . $title . '</h4>			
	</div>
	
	<div class="message-body">

		<div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
			<div class="message-inbox">
				<ul class="message-history">
					' . $users . '
				</ul>
			</div>
		</div>
		<div class="col-md-8 clearfix col-xs-12 message-content">
			<div class="message-details">
				<ul class="messages" id="messages">
					' . $messages . '
				</ul>
				<div class="chat-form ">
					' . $form . '
				</div>
			</div>
		</div>
	</div>
	';
	}
function adforest_load_messages_by_user_id($user_id) {
	global $adforest_theme;
	$script = '';

	global $wpdb;

	$rows = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_type = 'ad_post' AND user_id = '$user_id' AND comment_parent = '$user_id' GROUP BY comment_post_ID ORDER BY comment_ID DESC");

	$users = '';
	$messages = '';
	$form = '<div class="text-center">' . __('No message received on this ad yet.', 'adforest') . '</div>';
	$author_html = '';
	$turn = 1;
	$level_2 = '';
	$title_html = '';
	foreach ($rows as $row) {
		$last_date = $row->comment_date;
		$date = explode(' ', $last_date);
		$author = get_post_field('post_author', $row->comment_post_ID);
		$cls = '';
		if ($turn == 1)
			$cls = 'message-history-active';

		$ad_img = $adforest_theme['default_related_image']['url'];
		$media = adforest_get_ad_images($row->comment_post_ID);
		if (count($media) > 0) {
			foreach ($media as $m) {
				$mid = '';
				if (isset($m->ID))
					$mid = $m->ID;
				else
					$mid = $m;

				$img = wp_get_attachment_image_src($mid, 'adforest-ad-related');
				$ad_img = $img[0];
				break;
			}
		}


		if (isset($row->comment_post_ID) && $row->comment_post_ID != "") {
			if ($turn == 1) {
				$title_html .= '<span class="sb_ad_title" id="title_for_' . esc_attr($row->comment_post_ID) . '"><a href="' . get_the_permalink($row->comment_post_ID) . '" target="_blank" >' . get_the_title($row->comment_post_ID) . '</a></span>';
			} else {
				$title_html .= '<span class="sb_ad_title no-display" id="title_for_' . esc_attr($row->comment_post_ID) . '" ><a href="' . get_the_permalink($row->comment_post_ID) . '" target="_blank" >' . get_the_title($row->comment_post_ID) . '</a></span>';
			}
		}


		$ad_id = $row->comment_post_ID;
		$comment_author = get_userdata($author);

		$msg_status = get_comment_meta(get_current_user_id(), $ad_id . "_" . $author, true);
		$status = '';
		$has_message = '';
		if ($msg_status == '0') {
			
			$status = '<i class="fa fa-envelope" aria-hidden="true"></i>';
			$has_message = " has_message";
		}

		$users .= '
			<li class="user_list ad_title_show ' . $cls . $has_message . '" cid="' . $row->comment_post_ID . '" second_user="' . $author . '" inbox="yes" id="sb_' . $author . '_' . $ad_id . '">
				<a href="javascript:void(0);">
					<div class="image">
						<img src="' . $ad_img . '" alt="' . $comment_author->display_name . '">
					</div>
					<div class="user-name">
						<div class="author">
							<span>' . get_the_title($ad_id) . '</span>
						</div>
						<p>' . $comment_author->display_name . '</p>
						<div class="time" id="' . $author . '_' . $ad_id . '">
							' . $status . '
					   </div>
					</div>
				</a>
			</li>
		';
		
		$authors = array($author, get_current_user_id());
		if ($turn == 1) {
			$args = array(
				'author__in' => $authors,
				'post_id' => $ad_id,
				'parent' => get_current_user_id(),
				'post_type' => 'ad_post',
				'orderby' => 'comment_date',
				'order' => 'ASC',
			);
			$comments = get_comments($args);
			if (count($comments) > 0) {

				foreach ($comments as $comment) {
					$user_pic = '';
					$class = 'friend-message';
					if ($comment->user_id == get_current_user_id()) {
						$class = 'my-message';
					}
					$user_pic = adforest_get_user_dp($comment->user_id);
					$messages .= '
						<li class="' . $class . ' clearfix">
							<figure class="profile-picture">
								<a href="' . get_author_posts_url($comment->user_id) . '?type=ads" class="link" target="_blank">
									<img src="' . $user_pic . '" class="img-circle" alt="' . __('Profile Pic', 'adforest') . '">
								</a>
							</figure>
							<div class="message">
								' . $comment->comment_content . '
								<div class="time"><i class="fa fa-clock-o"></i> ' . adforest_timeago($comment->comment_date) . '</div>
							</div>
						</li>
					';
				}
			}

			// Message form
			$profile = new adforest_profile();
			$level_2 = '
				<input type="hidden" name="usr_id" value="' . $user_id . '" />
				<input type="hidden" id="usr_id" value="' . $author . '" />
				<input type="hidden" id="rece_id" name="rece_id" value="' . $author . '" />
				<input type="hidden" name="msg_receiver_id" id="msg_receiver_id" value="' . esc_attr($author) . '" />
			';
			
			$form = '
				<form role="form" class="form-inline" id="send_message">
					<div class="form-group">
						<input type="hidden" name="ad_post_id" id="ad_post_id" value="' . $ad_id . '" />
						<input type="hidden" name="name" value="' . $profile->user_info->display_name . '" />
						<input type="hidden" name="email" value="' . $profile->user_info->user_email . '" />
						' . $level_2 . '
						<textarea rows="4" name="message" id="sb_forest_message" placeholder="' . __('Type a message here...', 'adforest') . '" class="form-control message-text" autocomplete="off" data-parsley-required="true" data-parsley-error-message="' . __('This field is required.', 'adforest') . '"></textarea>
					</div>
					<button class="btn btn-theme" id="send_msg" type="submit" inbox="yes">' . __('Send', 'adforest') . '</button>
				</form>
			';
		}
		$turn++;
	} // end foreach
	
	if ($users == '') {
		$users = '<li class="padding-top-30 padding-bottom-20"><div class="user-name">' . __('Nothing Found.', 'adforest') . '</div></li>';
	}


	return '
	<div class="messages_top_bar">
		<div class="message-header_nav">
			<span><a class="messages_actions" sb_action="my_msgs"><small>' . __('See all notifications', 'adforest') . '</small></a></span>
			<span class="actions_divider">|</span>
			<span><a class="messages_actions active" sb_action="my_msgs_outbox"><small>' . __('Sent Offers', 'adforest') . '</small></a></span>
			<span class="actions_divider">|</span>
			<span><a class="messages_actions" sb_action="received_msgs_ads_list"><small>' . __('Received  Offers', 'adforest') . '</small></a></span>
		</div>
	</div>
	
	<div class="messages_header">
		<h4>' . $title_html . '</h4>			
	</div>
	
	<div class="message-body">
		<div class="col-md-4 col-sm-5 col-xs-12 left_pannel">
			<div class="message-inbox">
				<ul class="message-history">
					' . $users . '
				</ul>
			</div>
		</div>
		<div class="col-md-8 clearfix col-xs-12 message-content">
			<div class="message-details">
				<ul class="messages" id="messages">
					' . $messages . '
				</ul>
				<div class="chat-form ">
					' . $form . '
				</div>
			</div>
		</div>
	</div>

	<script>
		jQuery(document).ready(function($) {
			var has_message = $("li.has_message");
			var first = $("li.user_list")[0];
			has_message.each(function() {
				$(first).before(this);
			});
		});
	</script>
	';
}	






// PRICE
if (!function_exists('adforest_adPrice')) {

	function adforest_adPrice($id = '', $class = 'negotiable') {
		if (get_post_meta($id, '_adforest_ad_price', true) == "" && get_post_meta($id, '_adforest_ad_price_type', true) == "on_call") {
			return __("Price On Call", 'adforest');
		}
		if (get_post_meta($id, '_adforest_ad_price', true) == "" && get_post_meta($id, '_adforest_ad_price_type', true) == "free") {
			return __("Free", 'adforest');
		}

		if (get_post_meta($id, '_adforest_ad_price', true) == "" || get_post_meta($id, '_adforest_ad_price_type', true) == "no_price") {
			return '';
		}

		$price = 0;
		global $adforest_theme;
		$thousands_sep = ",";
		if (isset($adforest_theme['sb_price_separator']) && $adforest_theme['sb_price_separator'] != "") {
			$thousands_sep = $adforest_theme['sb_price_separator'];
		}
		$decimals = 0;
		if (isset($adforest_theme['sb_price_decimals']) && $adforest_theme['sb_price_decimals'] != "") {
			$decimals = $adforest_theme['sb_price_decimals'];
		}
		$decimals_separator = ".";
		if (isset($adforest_theme['sb_price_decimals_separator']) && $adforest_theme['sb_price_decimals_separator'] != "") {
			$decimals_separator = $adforest_theme['sb_price_decimals_separator'];
		}
		$curreny = $adforest_theme['sb_currency'];
		if (get_post_meta($id, '_adforest_ad_currency', true) != "") {
			$curreny = get_post_meta($id, '_adforest_ad_currency', true);
		}

		if ($id != "") {
			if (is_numeric(get_post_meta($id, '_adforest_ad_price', true))) {
				$price = number_format(get_post_meta($id, '_adforest_ad_price', true), $decimals, $decimals_separator, $thousands_sep);
			}

			$price = ( isset($price) && $price != "") ? $price : 0;

			if (isset($adforest_theme['sb_price_direction']) && $adforest_theme['sb_price_direction'] == 'right') {
				$price = $price . $curreny;
			} else if (isset($adforest_theme['sb_price_direction']) && $adforest_theme['sb_price_direction'] == 'right_with_space') {
				$price = $price . " " . $curreny;
			} else if (isset($adforest_theme['sb_price_direction']) && $adforest_theme['sb_price_direction'] == 'left') {
				$price = $curreny . $price;
			} else if (isset($adforest_theme['sb_price_direction']) && $adforest_theme['sb_price_direction'] == 'left_with_space') {
				$price = $curreny . " " . $price;
			} else {
				$price = $curreny . $price;
			}
		}
		// Price type fixed or ...
		$price_type_html = '';
		if (get_post_meta($id, '_adforest_ad_price_type', true) != "" && isset($adforest_theme['allow_price_type']) && $adforest_theme['allow_price_type']) {
			$price_type_name = '';
			$price_type = get_post_meta($id, '_adforest_ad_price_type', true);
			if ($price_type == 'Торг') {
				$price_type_name = __('Negotiable', 'adforest');
			}
			if ($price_type != 'Fixed') {
				$price_type_html = '<span class="' . esc_attr($class) . '"> ' . $price_type_name . '</span>';
			}
		}

		return $price . $price_type_html;
	}

}






// UPLOAD AVATAR TO USER FOLDER
if (!function_exists('adforest_user_profile_pic')) {

    function adforest_user_profile_pic() {


        /* img upload */

        $condition_img = 7;
        $img_count = count(explode(',', $_POST["image_gallery"]));

        if (!empty($_FILES["my_file_upload"])) {

            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';


            $files = $_FILES["my_file_upload"];



            $attachment_ids = array();
            $attachment_idss = '';

            if ($img_count >= 1) {
                $imgcount = $img_count;
            } else {
                $imgcount = 1;
            }


            $ul_con = '';

            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array(
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );

                    $_FILES = array("my_file_upload" => $file);

// Allow certain file formats
                    $imageFileType = strtolower(end(explode('.', $file['name'])));
                    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                        echo '0|' . __("Sorry, only JPG, JPEG, PNG & GIF files are allowed.", 'adforest');
                        die();
                    }

                    // Check file size
                    if ($file['size'] > 2097152) {
                        echo '0|' . __("Max allowd image size is 2MB", 'adforest');
                        die();
                    }


                    foreach ($_FILES as $file => $array) {

                        if ($imgcount >= $condition_img) {
                            break;
                        }
                        $attach_id = media_handle_upload($file, $post_id);
                        $attachment_ids[] = $attach_id;
						
						
						

                        $image_link = wp_get_attachment_image_src($attach_id, 'adforest-user-profile');
                    }
                    if ($imgcount > $condition_img) {
                        break;
                    }
                    $imgcount++;
                }
            }
        }
        /* img upload */
        $attachment_idss = array_filter($attachment_ids);
        $attachment_idss = implode(',', $attachment_idss);


        $arr = array();
        $arr['attachment_idss'] = $attachment_idss;
        $arr['ul_con'] = $ul_con;

        $profile = new adforest_profile();
        $uid = $profile->user_info->ID;
        update_user_meta($uid, '_sb_user_pic', $attach_id);
		
		
		$user_folder = 'user-'.$uid;
		wp_set_object_terms( $attach_id, $user_folder, WPMF_TAXO, false);
		
		if (!$image_link[0]) {
			global $adforest_theme;
			$image_link[0] = $adforest_theme['sb_user_dp']['url'];
		}
		
        echo '1|' . $image_link[0];
        die();
    }

}







// EDIT PROFILE PAGE (ADD DELETE AVATAR BUTTON)
if (!function_exists('adforest_profile_update_ajax')) {
    function adforest_profile_update_ajax() {
        $profile = new adforest_profile();
        echo ai_adforest_profile_update_form();
        die();
    }
}
function ai_adforest_profile_update_form() {
	
	$profile = new adforest_profile();
	$user_pic = $user_pic = adforest_get_user_dp($profile->user_info->ID);

	$is_indiviual = '';
	$is_dealer = '';
	if (get_user_meta($profile->user_info->ID, '_sb_user_type', true) == 'Dealer') {
		$is_dealer = 'selected="selected"';
	}
	if (get_user_meta($profile->user_info->ID, '_sb_user_type', true) == 'Indiviual') {
		$is_indiviual = 'selected="selected"';
	}
	$user_type = '<option value="Indiviual"  ' . $is_indiviual . '>' . __('Individual', 'adforest') . '</option>
			 <option value="Dealer" ' . $is_dealer . '>' . __('Dealer', 'adforest') . '</option>';


	$change_password_html = '';
	$my_url = adforest_get_current_url();
	if (strpos($my_url, 'adforest.scriptsbundle.com') !== false) {
		$change_password_html = '<a data-toggle="tooltip" data-placement="top" title="' . __('Change Password', 'adforest') . '" data-original-title="' . __('Disable for Demo', 'adforest') . '">' . __('Change Password', 'adforest') . '</a>';
	} else {
		$change_password_html = '<a data-target="#myModal" data-toggle="modal">' . __('Change Password', 'adforest') . '</a>';
	}
	$intro_html = '';
	if (true) {
		$intro_html = '
		<div class="col-md-12 col-sm-12 col-xs-12 margin-bottom-30">
			<label>' . __('Introduction', 'adforest') . ' <span class="color-red"></span></label>
			<textarea name="sb_user_intro" class="form-control" rows="6">' . esc_attr(get_user_meta($profile->user_info->ID, '_sb_user_intro', true)) . '</textarea>
		</div>
		';
	}
	global $adforest_theme;
	if (isset($adforest_theme['sb_enable_social_links']) && $adforest_theme['sb_enable_social_links']) {
		$social_html = '';
		$profiles = adforest_social_profiles();
		foreach ($profiles as $key => $value) {

			$social_html .= '
			<div class="col-md-6 col-sm-6 col-xs-12">
				<label>' . $value . ' <span class="color-red"></span></label>
				<input type="text" class="form-control margin-bottom-20" value="' . esc_attr(get_user_meta($profile->user_info->ID, '_sb_profile_' . $key, true)) . '" name="_sb_profile_' . $key . '">
			</div>
			';
		}
	}

	$ph_placeholder = '';
	if (isset($adforest_theme['sb_phone_verification']) && $adforest_theme['sb_phone_verification'] && in_array('wp-twilio-core/core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$ph_placeholder = __('+CountrycodePhonenumber', 'adforest');
	}

	/* Delete Account HTML Starts */

	$delete_account_html = '';
	if (isset($adforest_theme['sb_new_user_delete_option']) && $adforest_theme['sb_new_user_delete_option']) {
		$data_title = __("Are you sure you want to delete this account?", "adforest");
		$delete_account_html = '<a class="remove_user_profile delete_site_user" href="javascript:void(0);" data-btn-ok-label="' . __("Yes", "adforest") . '" data-btn-cancel-label="' . __("No", "adforest") . '" data-toggle="confirmation" data-singleton="true" data-title="' . $data_title . '" data-content="" data-user-id="' . $profile->user_info->ID . '" title="' . __("Delete Account?", "adforest") . '" aria-describedby="confirmation151400">' . __("Delete Account?", "adforest") . '</a>';
	}
	/* Delete Account HTML Ends */

	return adforest_load_search_countries() . adforest_get_location('adforest_location') . '
		<div class="profile-section margin-bottom-20">
			<div class="profile-tabs">
				<div class="tab-content">
					<div class="profile-edit tab-pane fade in active" id="edit">
						<h2 class="heading-md">' . __('Manage your Security Settings', 'adforest') . '</h2>
						<p>' . __('Manage Your Account', 'adforest') . '</p>
						<div class="clearfix"></div>
						
						<form id="sb_update_profile" enctype="multipart/form-data">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<p class="help-block pull-right">
									' . $change_password_html . '
									</p>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<label>' . __('Your Name', 'adforest') . '</label>
									<input type="text" class="form-control margin-bottom-20" value="' . esc_attr($profile->user_info->display_name) . '" name="sb_user_name">
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<label>' . __('Email Address', 'adforest') . '</label>
									<input type="text" class="form-control margin-bottom-20" value="' . esc_attr($profile->user_info->user_email) . '" readonly>
								</div>
								<div class="col-md-6 col-sm-12 col-xs-12">  
									<label>' . __('Contact Number', 'adforest') . '</label>
									<input type="text" class="form-control margin-bottom-20" name="sb_user_contact" id="sb_user_contact" value="' . esc_attr(get_user_meta($profile->user_info->ID, '_sb_contact', true)) . '" placeholder="' . $ph_placeholder . '">
								</div>
								<div class="col-md-6 col-sm-12 col-xs-12 margin-bottom-20 form-group">
									<label>' . __('I am', 'adforest') . '</label>
									<select class="category form-control" name="sb_user_type">
										' . $user_type . '
									</select>
								</div>
								' . $social_html . '
								<div class="col-md-12 col-sm-12 col-xs-12 margin-bottom-20">
									<label>' . __('Location', 'adforest') . '</label>
									<input type="text" class="form-control margin-bottom-20" name="sb_user_address" id="sb_user_address" autocomplete="on" value="' . esc_attr(get_user_meta($profile->user_info->ID, '_sb_address', true)) . '">
								</div>
								' . $intro_html . '
							</div>   
							<div class="row margin-bottom-20">
								<div class="form-group">
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-btn">
												<span class="btn btn-default btn-file">
													' . __('Profile Picture', 'adforest') . '
													<input type="file" id="imgInp" name="my_file_upload[]" accept = "image/*" class="sb_files-data form-control">
													
												</span>
												<span class="btn btn-default btn-file remove_avatar">
													'.__("<!--:ru-->Удалить изображение профиля<!--:ua-->Видалити зображення профілю<!--:-->").'
												</span>
											</span>
											<input type="hidden" class="form-control" readonly>
										</div>
									</div>
									<div class="col-md-3">
										<img id="img-upload" class="img-responsive" src="' . $user_pic . '" alt="' . __('Profile Picture', 'adforest') . '" width="100" height="100" />
									</div>
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="row">
								<div class="col-md-8 col-sm-8 col-xs-12">
									' . $delete_account_html . '
								</div>
								<div class="col-md-4 col-sm-4 col-xs-12 text-right">
									<button type="button" class="btn btn-theme btn-sm" id="sb_user_profile_update">
										' . __('Update My Info', 'adforest') . '
									</button>
								</div>
							</div>
						</form>
					</div>
					<div class="custom-modal">
						<div id="myModal" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<!-- Modal content-->
								<div class="modal-content">
									<div class="modal-header rte">
										<h2 class="modal-title">' . __('Password Change', 'adforest') . '</h2>
									</div>
									<form id="sb-change-password">
										<div class="modal-body">
											<div class="form-group">
												<label>' . __('Current Password', 'adforest') . '</label>
												<input placeholder="' . __('Current Password', 'adforest') . '" class="form-control" type="password"  name="current_pass" id="current_pass">
											</div>
											<div class="form-group">
												<label>' . __('New Password', 'adforest') . '</label>
												<input placeholder="' . __('New Password', 'adforest') . '" class="form-control" type="password" name="new_pass" id="new_pass">
											</div>
											<div class="form-group">
												<label>' . __('Confirm New Password', 'adforest') . '</label>
												<input placeholder="' . __('Confirm Password', 'adforest') . '" class="form-control" type="password" name="con_new_pass" id="con_new_pass">
											</div>
										</div>
										<div class="modal-footer">
											<button class="btn btn-theme btn-sm" type="button" id="change_pwd">' . __('Reset My Account', 'adforest') . '</button>
				
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
}





// Get user profile PIC
if (!function_exists('adforest_get_user_dp')) {

    function adforest_get_user_dp($user_id, $size = 'adforest-single-small') {
        global $adforest_theme;
        $user_pic = trailingslashit(get_template_directory_uri()) . 'images/users/9.jpg';
        if (isset($adforest_theme['sb_user_dp']['url']) && $adforest_theme['sb_user_dp']['url'] != "") {
            $user_pic = $adforest_theme['sb_user_dp']['url'];
        }

        $image_link = array();
        if (get_user_meta($user_id, '_sb_user_pic', true) != "") {
            $attach_id = get_user_meta($user_id, '_sb_user_pic', true);
            $image_link = wp_get_attachment_image_src($attach_id, $size);
        }
		if ($image_link) {
			if (count($image_link) > 0) {
				if ($image_link[0] != "") {
					$headers = @get_headers($image_link[0]);
					if (strpos($headers[0], '404') === false) {

						return $image_link[0];
					} else {
						return $user_pic;
					}
				} else {
					return $user_pic;
				}
			} else {
				return $user_pic;
			}
		} else {
			return $user_pic;
		}
    }

}





// echo translation
function adforest_returnEcho($html = '') {
	return __($html);
}
	


/*
// HOME CATEGORY TO SEARCH PAGE ?????????????????????????????
function adforest_browsecategorieswi_shortcode($atts, $content = '') {
		
	require trailingslashit( get_template_directory () ) . "inc/theme_shortcodes/shortcodes/layouts/header_layout.php";
	extract( shortcode_atts(
		array(
			'section_title' => __('Popular Categories', 'adforest'),
			'cat_link_page' => '',
			'cats' => '',
		), $atts ));
		
	$section_title;
	$button_link;
	$ad_categories = $atts['cats'];
		
	// For custom locations
	$ad_categories_html	=	'';
	if( isset( $atts['cats'] ) ) {
		$rows = vc_param_group_parse_atts( $atts['cats'] );
		if( count( (array)$rows ) > 0 ) {
			foreach($rows as $row ) {
				if( isset( $row['cat'] )  ) {
					$term = get_term( $row['cat'], 'ad_cats' );
					$term_link = adforest_cat_link_page($row['cat'], $cat_link_page );
						
					if($term) {
						$cat_img_url = ( isset($row['cat_img']) ) ? adforest_returnImgSrc( $row['cat_img'] ) : '';
						$ad_categories_html .= '<a href="' . esc_url($term_link) . '"> <span class="category_new"><img src="' . esc_url($cat_img_url) . '" class="img-responsive"></span> <span class="title">' . esc_html($term->name) . '</span> </a>';
					}
				}
			}
		}
	}
		
	$view_all = '';
	return '
		<div class="wpb-browse-categories">
			<section class="section-padding "> 
				<div class="container"> 
					<div class="row"> 
						<!-- Heading Area -->
						' . $header . '
						<div class="row">
							<div class="category_gridz text-center">' . $ad_categories_html . '</div>
						</div>
						' . $view_all . '
					</div>
				</div>
			</section>
		</div>
	';
	
}
*/




// CATS SEARCH AJAX
add_action('wp_ajax_get_sub_cats', 'ai_get_sub_cats');
add_action('wp_ajax_nopriv_get_sub_cats', 'ai_get_sub_cats');
function ai_get_sub_cats() {
	
	$cat_id = $_POST['cat_id'];
	$current_cat = get_term_by('id', $cat_id, 'ad_cats');
	$current_cat_name = $current_cat->name;
	$current_cat_parent = $current_cat->parent;
	
	
	$args = array(
		'taxonomy'     => 'ad_cats',
		'parent'        => $cat_id,
		'hide_empty'    => false           
	);
	$terms = get_terms( $args );
	$terms = __($terms);
	$sorted_terms = array();
	foreach($terms as $term) {
		$sorted_terms[$term->term_id] = $term->name;
	}

	$lang = qtranxf_getLanguage();
	if ($lang == 'ru') { $coll = collator_create('ru_RU'); } 
	elseif  ($lang == 'ua') { $coll = collator_create('uk_UA'); } 
	else { $coll = collator_create( 'en_US' ); }
	collator_asort( $coll, $sorted_terms, Collator::SORT_STRING );
	
	$bull_preloader = '<span class="bull_preloader"></span>';
	$html = '<div class="back_to_top_level" data-cat_id="' . $cat_id . '" data-parent_cat_id="' . $current_cat_parent . '"><span class="cat_arrow"><i class="fa fa-angle-left"></i></span> Назад</div>';
	$html .= '<div class="sub_cats_wrap">';
		$html .= '<div class="ad_cat_item all_cats" data-has_child="false" data-cat_id="' . $cat_id . '">' . $bull_preloader . '[:ru]Все в[:ua]Все у[:] <span class="cat_title">' . $current_cat_name . '</span></div>';
		foreach($sorted_terms as $term_id=>$term_name) {
			if ( count(get_term_children( $term->term_id, 'ad_cats' )) > 0 ) {
				$has_children = 'true';
				$sub_cat_html = '<div class="cat_' . $term_id . '_sub_cat sub_cat_wrap"></div>';
				$has_children_arrow = '<span class="cat_arrow"><i class="fa fa-angle-right"></i></span>';
			} else {
				$has_children = 'false';
				$sub_cat_html = '';
				$has_children_arrow = "";
			}
			$html .= '<div class="ad_cat_item_' . $term_id . ' ad_cat_item" data-sub_loaded="false" data-has_child="' . $has_children . '" data-cat_id="' . $term_id . '">' . $bull_preloader . '<span class="cat_title">' . $term_name . '</span>' . $has_children_arrow . $sub_cat_html . '</div>';
		}
	$html .= '</div>';
	
	echo __($html);
	wp_die();
}


// LOCATIONS SEARCH AJAX
add_action('wp_ajax_get_sub_locs', 'ai_get_sub_locs');
add_action('wp_ajax_nopriv_get_sub_locs', 'ai_get_sub_locs');
function ai_get_sub_locs() {
	
	$cat_id = $_POST['cat_id'];
	$current_cat = get_term_by('id', $cat_id, 'ad_country');
	$current_cat_name = $current_cat->name;
	$current_cat_parent = $current_cat->parent;
	
	
	$args = array(
		'taxonomy'     => 'ad_country',
		'parent'        => $cat_id,
		'hide_empty'    => false,	
	);
	$terms = get_terms( $args );
	$terms = __($terms);
	$sorted_terms = array();
	foreach($terms as $term) {
		$sorted_terms[$term->term_id] = $term->name;
	}

	$lang = qtranxf_getLanguage();
	if ($lang == 'ru') { $coll = collator_create('ru_RU'); } 
	elseif  ($lang == 'ua') { $coll = collator_create('uk_UA'); } 
	else { $coll = collator_create( 'en_US' ); }
	collator_asort( $coll, $sorted_terms, Collator::SORT_STRING );

	$bull_preloader = '<span class="bull_preloader"></span>';
	$html = '<div class="back_to_top_level" data-cat_id="' . $cat_id . '" data-parent_cat_id="' . $current_cat_parent . '"><span class="cat_arrow"><i class="fa fa-angle-left"></i></span> Назад</div>';
	$html .= '<div class="sub_cats_wrap">';
		$html .= '<div class="ad_cat_item all_cats" data-has_child="false" data-cat_id="' . $cat_id . '">' . $bull_preloader . '[:ru]Вся[:ua]Вся[:] <span class="cat_title">' . $current_cat_name . '</span></div>';
		foreach($sorted_terms as $term_id=>$term_name) {
			if ( count(get_term_children( $term_id, 'ad_country' )) > 0 ) {
				$has_children = 'true';
				$sub_cat_html = '<div class="cat_' . $term_id . '_sub_cat sub_cat_wrap"></div>';
				$has_children_arrow = '<span class="cat_arrow"><i class="fa fa-angle-right"></i></span>';
			} else {
				$has_children = 'false';
				$sub_cat_html = '';
				$has_children_arrow = "";
			}
			$html .= '<div class="ad_cat_item_' . $term_id . ' ad_cat_item" data-sub_loaded="false" data-has_child="' . $has_children . '" data-cat_id="' . $term_id . '">' . $bull_preloader . '<span class="cat_title">' . $term_name . '</span>' . $has_children_arrow . $sub_cat_html . '</div>';
		}
	$html .= '</div>';
	
	echo __($html);
	wp_die();
}



// Remove Ad
add_action('wp_ajax_sb_update_ad_status', 'adforest_sb_update_ad_status');
if (!function_exists('adforest_sb_update_ad_status')) {

    function adforest_sb_update_ad_status() {
        adforest_authenticate_check();
        $ad_id = $_POST['ad_id'];
        $status = $_POST['status'];
        update_post_meta($ad_id, '_adforest_ad_status_', $status);
		if ($status == 'expired') {
			$post = array( 'ID' => $ad_id, 'post_status' => 'draft' );
			wp_update_post($post);
		}
		if ($status == 'active') {
			$post = array( 'ID' => $ad_id, 'post_status' => 'publish' );
			wp_update_post($post);
		}
        echo '1|' . __("Updated successfully.", 'adforest');
        die();
    }

}
  
  
  
  
// INACTIVE ADS IN PROFILE
function adforest_my_inactive_ads() {	
	$profile = new adforest_profile();
	$paged = $_POST['paged'];
	if (!isset($paged))
		$paged = 1;
	$args = array(
		'post_type' => 'ad_post',
		'author' => $profile->user_info->ID,
		'post_status' => array('pending', 'draft'),
		'posts_per_page' => get_option('posts_per_page'),
		'paged' => $paged,
		'order' => 'DESC',
		'orderby' => 'ID'
	);
	$fav_ads = 'no';
	$show_pagination = 1;
	echo adforest_returnEcho($profile->adforest_my_ads($args, $paged, $show_pagination, $fav_ads));
	

	die();
}




// REDUX OPTIONS TRANSLATIONS
function custom_theme_options_translations() {
	global $adforest_theme;
	$lang = qtranxf_getLanguage();

	if ($lang == 'ru') {
		$adforest_theme['sb_location_titles'] = 'Область|Город (районный центр)';
	} else  {
		$adforest_theme['sb_location_titles'] = 'Область|Місто (районный центр)';
	}
	if ($lang == 'ru') {
		$adforest_theme['report_options'] = 'Спам|Оскорбительное|Мошенничество';
	} else  {
		$adforest_theme['report_options'] = 'Спам|Образливе|Шахрайство';
	}
	if ($lang == 'ru') {
		$adforest_theme['sb_ad_update_notice'] = 'Внимание! Вы редактируете объявление';
	} else  {
		$adforest_theme['sb_ad_update_notice'] = 'Увага! Ви редагуєте оголошення';
	}
	if ($lang == 'ru') {
		$adforest_theme['sb_feature_desc'] = 'VIP объявления показываются в лучших местах сайта и привлекают больше внимания';
	} else  {
		$adforest_theme['sb_feature_desc'] = 'VIP оголошення показуються у найкращих місцях сайту та привертають більше уваги';
	}
	if ($lang == 'ru') {
		$adforest_theme['sb_related_ads_title'] = 'Похожие объявления';
	} else  {
		$adforest_theme['sb_related_ads_title'] = 'Схожі оголошення';
	}
	if ($lang == 'ru') {
		$adforest_theme['feature_ads_title'] = 'VIP объявления';
	} else  {
		$adforest_theme['feature_ads_title'] = 'VIP оголошення';
	}
	
	
	// LETTERS

	if ($lang == 'ru') {
		$logo_img = 'https://harchi.in.ua/wp-content/uploads/2017/03/harchi_logo_ru.jpg';
	} else  {
		$logo_img = 'https://harchi.in.ua/wp-content/uploads/2017/03/harchi_logo_ua.jpg';
	}	
	
	$message_before_content = '
		<table class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f6f6f6; width: 100%;" border="0" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"> </td>
					<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; max-width: 580px; padding: 10px; width: 580px; margin: 0 auto !important;">
						<div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px;">
							<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #fff; border-radius: 3px; width: 100%;">
								<tbody>
									<tr>
										<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
											<table style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" border="0" cellspacing="0" cellpadding="0">
												<tbody>
													<tr style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
														<td class="alert" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; color: #000; font-weight: 500; text-align: center; border-radius: 3px 3px 0 0; background-color: #fff; margin: 0; padding: 20px;" align="center" valign="top" bgcolor="#fff">
															<img src="' . $logo_img . '" />
														</td>
													</tr>
													<tr>
														<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">	
	';
	$message_after_content = '
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="footer" style="clear: both; padding-top: 10px; text-align: center; width: 100%;">
								<table style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" border="0" cellspacing="0" cellpadding="0">
									<tbody>
										<tr>
											<td class="content-block powered-by" style="font-family: sans-serif; font-size: 12px; vertical-align: top; color: #999999; text-align: center;"><a style="color: #999999;" href="https://harchi.in.ua" target="_blank" rel="noopener">harchi.in.ua</a></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>		
	';	
	
		
	
	
	// new message
	if ($lang == 'ru') {
		$adforest_theme['sb_message_subject_on_new_ad'] = 'Харчи.in.ua - У вас новое сообщение';
		$adforest_theme['sb_message_from_on_new_ad'] = 'Харчи.in.ua <info@harchi.in.ua>';
		$new_message_content = '
			<p>Здравствуйте, вы получили новое сообщение от <strong>%sender_name%</strong> по объявлению <a href="%ad_link%">%ad_title%</a></p>
			<p>Текст сообщения: %message%</p>
			<p><strong>Благодарим что вы с нами!</strong></p>
		';
	} else  {
		$adforest_theme['sb_message_subject_on_new_ad'] = 'Харчі.in.ua - У вас нове повідомлення';
		$adforest_theme['sb_message_from_on_new_ad'] = 'Харчі.in.ua <info@harchi.in.ua>';
		$new_message_content = '
			<p>Вітаємо, ви отримали нове повідомлення від <strong>%sender_name%</strong> по оголошенню <a href="%ad_link%">%ad_title%</a></p>
			<p>Текст повідомлення: %message%</p>
			<p><strong>Дякуємо що обрали нас!</strong></p>
		';
	}	
	$adforest_theme['sb_message_on_new_ad'] = $message_before_content . $new_message_content . $message_after_content;	

	
	
	// reset password
	if ($lang == 'ru') {
		$adforest_theme['sb_forgot_password_subject'] = 'Харчи.in.ua - Восстановление пароля';
		$adforest_theme['sb_forgot_password_from'] = 'Харчи.in.ua <info@harchi.in.ua>';
		$reset_password_content = '
			<p>Здравствуйте <strong>%user%</strong>!</p>
			<p>Ваша ссылка для восстановления пароля: <a href="%reset_link%">%reset_link%</a></p>
			<p><strong>Благодарим что вы с нами!</strong></p>
		';
	} else  {
		$adforest_theme['sb_forgot_password_subject'] = 'Харчі.in.ua - Відновлення паролю';
		$adforest_theme['sb_forgot_password_from'] = 'Харчі.in.ua <info@harchi.in.ua>';
		$reset_password_content = '
			<p>Вітаємо <strong>%user%</strong>!</p>
			<p>Ваше посилання для відновлення паролю: <a href="%reset_link%">%reset_link%</a></p>
			<p><strong>Дякуємо що обрали нас!</strong></p>
		';
	}	
	$adforest_theme['sb_forgot_password_message'] = $message_before_content . $reset_password_content . $message_after_content;		
	

	// user wellcome
	if ($lang == 'ru') {
		$adforest_theme['sb_new_user_message_subject'] = 'Харчи.in.ua - Подтверждение регистрации';
		$adforest_theme['sb_new_user_message_from'] = 'Харчи.in.ua <info@harchi.in.ua>';
		$user_wellcome_content = '
			<p>Здравствуйте <strong>%display_name%</strong>!</p>
			<p>Для активации вашей учетной записи на сайте <strong>Харчи.in.ua</strong> перейдите по ссылке: <a href="%verification_link%">%verification_link%</a> и введите ваш логин и пароль</p>
			<p><strong>Благодарим что вы с нами!</strong></p>
		';
	} else {
		$adforest_theme['sb_new_user_message_subject'] = 'Харчі.in.ua - Підтвердження реєстрації';
		$adforest_theme['sb_new_user_message_from'] = 'Харчі.in.ua <info@harchi.in.ua>';
		$user_wellcome_content = '
			<p>Вітаємо <strong>%display_name%</strong>!</p>
			<p>Для активації вашого облікового запису на сайті <strong>Харчі.in.ua</strong> перейдіть за посиланням: <a href="%verification_link%">%verification_link%</a> та введіть ваш логін і пароль</p>
			<p><strong>Дякуємо за реєстрацію!</strong></p>
		';
	}	
	$adforest_theme['sb_new_user_message'] = $message_before_content . $user_wellcome_content . $message_after_content;	
	

	// ad activation
	if ($lang == 'ru') {
		$adforest_theme['sb_active_ad_email_subject'] = 'Харчи.in.ua - Объявление опубликовано';
		$adforest_theme['sb_active_ad_email_from'] = 'Харчи.in.ua <info@harchi.in.ua>';
		$ad_activation_content = '
			<p>Здравствуйте <strong>%user_name%</strong>!</p>
			<p>Ваше объявление <strong>"%ad_title%"</strong> активировано, для его просмотра перейдите по ссылке: <a href="%ad_link%">%ad_link%</a></p>
			<p>Для эффективной продажи ваших товаров и услуг советуем воспользоваться специальными <a href="https://harchi.in.ua/packages/"><b>пакетами</b></a> для продвижения объявлений на сайте Харчи.in.ua</p>
			<p><strong>Благодарим что вы с нами!</strong></p>
		';
	} else {
		$adforest_theme['sb_active_ad_email_subject'] = 'Харчі.in.ua - Оголошення опубліковано';
		$adforest_theme['sb_active_ad_email_from'] = 'Харчі.in.ua <info@harchi.in.ua>';
		$ad_activation_content = '
			<p>Вітаємо <strong>%user_name%</strong>!</p>
			<p>Ваше оголошення <strong>"%ad_title%"</strong> активовано, для його перегляду перейдіть за посиланням: <a href="%ad_link%">%ad_link%</a></p>
			<p>Для ефективного продажу ваших товарів та послуг радимо скористатися спеціальними <a href="https://harchi.in.ua/ua/packages/"><b>пакетами</b></a> для просування оголошень на сайті Харчі.in.ua</p>
			<p><strong>Дякуємо що обрали нас!</strong></p>
		';
	}	
	$adforest_theme['sb_active_ad_email_message'] = $message_before_content . $ad_activation_content . $message_after_content;
}

	
add_action( 'wp_loaded', 'custom_theme_options_translations' );




// OVERIDE HOME LISTING 
function ads_short_base_func($atts, $content = '') {
	$no_title = 'yes';
	require trailingslashit(get_template_directory()) . "inc/theme_shortcodes/shortcodes/layouts/header_layout.php";
	require trailingslashit(get_stylesheet_directory()) . "inc/ads_layout.php";
	$parallex = '';
	if ($section_bg == 'img') {
		$parallex = 'parallex';
	}
	$btnHTML = '';
	if ($main_link != "") {
		$aHTML = adforest_ThemeBtn($main_link, 'btn btn-theme text-center', false);
		if ($aHTML != "") {
			$btnHTML = '<div class="text-center">' . $aHTML . '</div>';
		}
	}
	return '
	<section class="custom-padding ' . $bg_color . ' ' . $bg_color . '" ' . $style . '>
		<!-- Main Container -->
		<div class="container">
			<!-- Row -->
			<div class="row">
				' . $header . '
				' . $html . '
			</div>
			' . $btnHTML . '
		</div>
	</section>';
}






// my layout list
function ai_adforest_search_layout_list($pid) {
	global $adforest_theme;
	$author_id = get_post_field('post_author', $pid);
	
	
	$is_feature = '';
	if (get_post_meta($pid, '_adforest_is_feature', true) == '1') {
		$is_feature = '
			<div class="featured-ribbon">
				<span>' . __('Featured', 'adforest') . '</span>
			</div>';
		$is_feature_class = 'ad_featured';
	}
	

	$price = '
	<div class="price">
		<span>
			' . adforest_adPrice($pid) . '
		</span> 
	</div>';

	$output = '
	<div class="well ad-listing clearfix ' . $is_feature_class . '">
		<div class="col-md-3 col-sm-5 col-xs-12 grid-style no-padding">';
			$img = adforest_get_ad_default_image_url('adforest-ad-related');
			$media = adforest_get_ad_images($pid);
			$total_imgs = count($media);
			if (count($media) > 0) {
				foreach ($media as $m) {
					$mid = '';
					if (isset($m->ID))
						$mid = $m->ID;
					else
						$mid = $m;
					$image = wp_get_attachment_image_src($mid, 'adforest-ad-related');
					$img = $image[0];
					break;
				}		
			}
			$output .= '
			<div class="img-box">
				' . $is_feature . '
				<a href="' . get_the_permalink($pid) . '">
					<img src="' . esc_url($img) . '" class="img-responsive" alt="' . get_the_title($pid) . '">
				</a>
			</div>

		</div>';


		$output .= '
		<div class="col-md-9 col-sm-7 col-xs-12">
			<!-- Ad Content-->
			<div class="row">
				<div class="content-area">
					<div class="col-md-9 col-sm-12 col-xs-12">';
						$cats_html = '';
						$post_categories = wp_get_object_terms($pid, array('ad_cats'), array('orderby' => 'term_group'));
						foreach ($post_categories as $c) {
							$cat = get_term($c);
							$cats_html .= '<span><a href="' . get_term_link($cat->term_id) . '">' . esc_html($cat->name) . '</a></span>';
							$it_one++;
						}
					
						$locations_arr =  wp_get_object_terms($pid, array('ad_country'), array('orderby' => 'term_group'));
						$locations_arr = array_reverse($locations_arr);
						foreach ($locations_arr as $locations) {;
							$loc = get_term($locations);
							$location_html .= '<span><a class="ad_location" href="' . get_term_link($loc->term_id) . '">' . esc_html($loc->name) . '</a></span>';
						}					
					
						$output .= '
						<div class="category-title">
							' . $cats_html . '
						</div>
						
						<!-- Ad Title -->
						<h3>
							<a href="' . get_the_permalink($pid) . '">
								' . get_the_title($pid) . '
							</a>
						</h3>
						
						<ul class="ad-meta-info">
							<li> <i class="fa fa-map-marker"></i>
								<a href="javascript:void(0);">
									' . $location_html . '
								</a>
							</li>
							<li> <i class="fa fa-clock-o"></i>
								' . get_the_date(get_option('date_format'), $pid) . '
							</li>
						</ul>
						
						<!--<div class="ad-details">
							<p>' . adforest_words_count(get_the_excerpt(), 150) . '</p>
						</div>-->
						
					</div>
					
					<div class="col-md-3 col-xs-12 col-sm-12">
						<!-- Price -->
						' . $price . '
					</div>

				</div>
				<div class="additional-info pull-right">
					<a title="' . __('Save', 'adforest') . '" href="javascript:void(0);" class="fa fa-star-o save-ad" data-adid="' . esc_attr($pid) . '"></a>
				</div>
			</div>
			<!-- Ad Content End -->
		</div>
	</div>';
	return $output;
}













function get_breadcrumbs($tax_name) {
	
	// breadcrumbs
	$home = '<a href="'.home_url().'" rel="nofollow">[:ru]Главная[:ua]Головна[:]</a>';
	$queried_object = get_queried_object();
	$crumbs = array_reverse ( get_ancestors( $queried_object->term_id, $tax_name ));
	$divider = '<span class="ai_breadcrumbs_divider"><i class="fa fa-angle-right"></i></span>';
	$crumbs_html = '';
	if (count($crumbs) == 0) {
		$crumbs_html .= $divider . $queried_object->name;
	} else {
		foreach ($crumbs as $crumb) {
			$crumbs_html .= $divider . '<a class="ai_breabcrumb" href="' . get_term_link($crumb) . '">' . get_term($crumb)->name . '</a>';
		}
		$crumbs_html .= $divider . $queried_object->name;
	}
	$breacrumbs = $home . $crumbs_html;
	echo __($breacrumbs);
	
	
	// child cats
	$child_cats = get_terms( $tax_name, array( 'parent' => $queried_object->term_id, 'hide_empty' => false ) );
	$child_cats = __($child_cats);
	$sorted_terms = array();
	foreach($child_cats as $term) {
		$sorted_terms[$term->term_id] = $term->name;
	}	
	
	$lang = qtranxf_getLanguage();
	if ($lang == 'ru') { $coll = collator_create('ru_RU'); } 
	elseif  ($lang == 'ua') { $coll = collator_create('uk_UA'); } 
	else { $coll = collator_create( 'en_US' ); }
	collator_asort( $coll, $sorted_terms, Collator::SORT_STRING );
	
	
	
	if (count($sorted_terms) > 0) {
		$bull = '<span class="ai_bull"><i class="fa fa-angle-right"></i></span>';
		$child_cats_html = '<div class="ai_child_cats">';
		foreach($sorted_terms as $term_id=>$term_name) {
			$child_cats_html .= '<div class="ai_child_cat">' . $bull . '<a href="' . get_term_link($term_id) . '">' . $term_name . ' (' . get_term($term_id)->count . ')</a></div>';
		}
		$child_cats_html .= '</div>';
		echo __($child_cats_html);
	}
}








// DEBUG
function vardump($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}




// DAYS DIFF FOR EXPIRED
function adforest_days_diff($now, $from) {
	$datediff = $now - $from;
	return $datediff / (60 * 60 * 24);
}




// FEATURED EXPIRED
add_action( 'updated_post_meta', 'wpse16835_after_post_meta', 10, 4 );
function wpse16835_after_post_meta( $meta_id, $post_id, $meta_key, $meta_value )
{
    if ( '_adforest_is_feature_date' == $meta_key ) {
		$meta_value = date('Y-m-d H:i');
        update_post_meta( $post_id, $meta_key, $meta_value );
    }
}







// PROFILE VIP LIST
function adforest_my_feature_ads() {
	$profile = new adforest_profile();
	$paged = $_POST['paged'];
	if (!isset($paged))
		$paged = 1;
	$args = array(
		'post_type' => 'ad_post',
		'author' => $profile->user_info->ID,
		'post_status' => 'publish',
		'posts_per_page' => get_option('posts_per_page'),
		'meta_key' => '_adforest_is_feature',
		'meta_value' => '1',
		'paged' => $paged,
		'order' => 'DESC',
		'orderby' => 'ID'
	);

	$fav_ads = 'no';
	$show_pagination = 1;	
	echo  __(ai_adforest_fav_layout_list($args, $paged, $show_pagination, $fav_ads));
	die();
}


// PROFILE VIP ITEM
function ai_adforest_fav_layout_list($args, $paged, $show_pagination, $fav_ads) {
	$my_ads = '';
	global $adforest_theme;


	$flip_it = '';
	$ribbion = 'featured-ribbon';

	$ads_p = new ads();
	
	$args = apply_filters('adforest_wpml_show_all_posts', $args);
	$ads = new WP_Query($args);
	if ($ads->have_posts()) {
		$number = 0;
		while ($ads->have_posts()) {
			$ads->the_post();

			$pid = get_the_ID();

			
			// messages
			$messages = '';
			if ($fav_ads == 'no') {
				if ($adforest_theme['communication_mode'] == 'both' || $adforest_theme['communication_mode'] == 'message') {

					$messages = '<div class="notification msgs get_msgs" ad_msg=' . $pid . '>
							<a class="round-btn" href="javascript:void(0);"><i class="fa fa-envelope-o"></i></a>
							<span>' . $ads_p->adforest_count_ad_messages($pid) . '</span>
			 </div>';
				}
			}

			
			// image
			$outer_html = '';
			$media = adforest_get_ad_images($pid);
			if (count($media) > 0) {
				$counting = 1;
				foreach ($media as $m) {
					if ($counting > 1)
						break;

					$mid = '';
					if (isset($m->ID))
						$mid = $m->ID;
					else
						$mid = $m;
					$image = wp_get_attachment_image_src($mid, 'adforest-ad-related');

					$outer_html = '
					<div class="image">
						<img src="' . $image[0] . '" alt="' . get_the_title() . '" class="img-responsive">
					</div>';
					$counting++;
				}
			} else {
				$outer_html = '
				<div class="image">
					<img src="' . adforest_get_ad_default_image_url('adforest-ad-related') . '" alt="' . get_the_title() . '" class="img-responsive">
				</div>';
			}
			
			
				
			// edit/delete
			$edit = '
			<li>
				<a data-toggle="tooltip" data-placement="top" title="' . __('Edit this Ad', 'adforest') . '" data-original-title="' . __('Edit this Ad', 'adforest') . '" href="' . get_the_permalink($sb_post_ad_page) . '?id=' . get_the_ID() . '"><i class="fa fa-pencil edit"></i></a> 
			</li>';
			$delete = '
			<li>
				<a  href="javascript:void(0);" data-adid="' . get_the_ID() . '" class="remove_ad" data-btn-ok-label="' . __('Yes', 'adforest') . '" data-btn-cancel-label="' . __('No', 'adforest') . '" data-toggle="confirmation" data-singleton="true" data-title="' . __('Are you sure?', 'adforest') . '" data-content="" ><i class="fa fa-times delete"></i></a>
			</li>';



			// expired
			if (get_post_meta($pid, '_adforest_is_feature', true) == '1') {
				
				// show expire date
				$gmt_offset = get_option('gmt_offset') * 60 * 60;
				$ad_featured_date = strtotime(get_post_meta($pid, '_adforest_is_feature_date', true));
				$expiry_seconds = $adforest_theme['featured_expiry'] * 60 * 60 * 24;
				$expiry_date = $ad_featured_date + $expiry_seconds;
				$vip_expire_time = "[:ru]Дата окончания VIP: [:ua]Термін закінчення VIP: [:]" . '<span class="ad_expire_time">' . date_i18n( "j F Y, H:i", $expiry_date + $gmt_offset) . '</span>';
		
				// remove VIP if expire
				$now = time(); // or your date as well
                $featured_date = strtotime(get_post_meta($pid, '_adforest_is_feature_date', true));
                $featured_days = adforest_days_diff($now, $featured_date);
                $expiry_days = $adforest_theme['featured_expiry'];
                if ($featured_days > $expiry_days) {
                    update_post_meta($pid, '_adforest_is_feature', 0);
					continue;
                }
				$is_feature = '
				<div class="' . esc_attr($ribbion) . '">
					<span>' . __('Featured', 'adforest') . '</span>
				</div>';
				
			}

			
			// categories
			$cats_html = '';
			$post_categories = wp_get_object_terms($pid, array('ad_cats'), array('orderby' => 'term_group'));
			foreach ($post_categories as $c) {
				$cat = get_term($c);
				$cats_html .= '<span><a href="' . get_term_link($cat->term_id) . '">' . esc_html($cat->name) . '</a></span>';
				$it_one++;
			}
		
			$locations_arr =  wp_get_object_terms($pid, array('ad_country'), array('orderby' => 'term_group'));
			$locations_arr = array_reverse($locations_arr);
			foreach ($locations_arr as $locations) {;
				$loc = get_term($locations);
				$location_html .= '<span><a class="ad_location" href="' . get_term_link($loc->term_id) . '">' . esc_html($loc->name) . '</a></span>';
			}					
					

			$my_ads .= '
			<div class="well ad-listing clearfix" id="holder-' . get_the_ID() . '">
				<div class="white category-grid-box-1">
					<!-- Image Box -->
					' . $outer_html . '
					 <!-- Short Description -->
					 <div class="short-description-1 content-area">
						' . $messages . '
						<!-- Category Title -->
						<div class="category-title"> ' . $cats_html . ' </div>
						<!-- Ad Title -->
						<h3>
						   <a title="javascript:void(0);" href="' . get_the_permalink() . '">' . get_the_title() . '</a>
						</h3>
						
						<!-- Price -->
						<div class="ad-price">' . adforest_adPrice(get_the_ID()) . '</div> 
						<div class="ad_expire_termin">' . $vip_expire_time . '</div>
					</div>
					
					<!-- Ad Meta Stats -->
					<div class="ad-info-1">
						<ul class="pull-left ' . esc_attr($flip_it) . '">
							<li> <i class="fa fa-eye"></i><a href="javascript:void(0);">' . adforest_getPostViews(get_the_ID()) . ' ' . __('Views', 'adforest') . '</a> </li>
							<li> <i class="fa fa-clock-o"></i>' . get_the_date(get_option('date_format'), get_the_ID()) . '</li>
						</ul>
						<ul class="pull-right ' . esc_attr($flip_it) . '">
							' . $delete . '
							' . $edit . '
						</ul>
					</div>
				</div>
			</div>';
		}
		wp_reset_postdata();
	} else {
		$my_ads = get_template_part('template-parts/content', 'none');
	}
	

	// pagination
	$load_more = '';
	if ($show_pagination == 1) {
		
		$load_more = $ads_p->adforest_get_pages($paged, $ads->max_num_pages, $fav_ads);
	}

	// return
	return '
	<div class="row">

		<div class="col-md-12 col-sm-12 col-xs-12">

			' . $my_ads . '                   


			<div class="clearfix"></div>

			<div class="col-md-12 col-xs-12 col-sm-12">
				' . $load_more . '
			</div>

		</div>

	</div>

	<input type="hidden" id="max_pages" value="' . $ads->max_num_pages . '" />';
}


// VIP SLIDER
function ads_short_slider2_base_func($atts, $content = '') {
	//vardump($atts);
	$no_title = 'yes';
	$modern_slider = 1;
	require trailingslashit(get_template_directory()) . "inc/theme_shortcodes/shortcodes/layouts/header_layout.php";
	require trailingslashit(get_stylesheet_directory()) . "inc/ads_layout.php";
	$parallex = '';
	if ($section_bg == 'img') {
		$parallex = 'parallex';
	}

	return '<section class="gray">
		<div class="container">
		   <div class="row">
			  <div class="col-md-12 col-xs-12 col-sm-12 grid-section">
				  <div class="grid-card">
					   ' . $header . '
					  <div class="featured-slider-1 owl-carousel owl-theme"> ' . $html . ' </div>
				  </div>
			  </div>
		   </div>
		</div>
	 </section>
	 ';
}


// VIP SLIDER ITEM 
function ai_slider_grid_item($pid, $col = 6, $sm = 6, $holder = '') {
	$my_ads = '';

	// image
	$img = '';
	$media = adforest_get_ad_images($pid);
	if (count($media) > 0) {
		foreach ($media as $m) {
			$mid = '';
			if (isset($m->ID))
				$mid = $m->ID;
			else
				$mid = $m;

			$image = wp_get_attachment_image_src($mid, 'adforest-ad-related');
			$img = '<img src="' . $image[0] . '" alt="' . get_the_title() . '" class="img-responsive">';
			break;
		}
	}
	else {
		$img = '<img src="' . adforest_get_ad_default_image_url('adforest-ad-related') . '" alt="' . get_the_title() . '" class="img-responsive">';
	}

	$is_feature = '';
	if (get_post_meta($pid, '_adforest_is_feature', true) == '1') {
		$is_feature = '<span class="ad-status">' . __('Featured', 'adforest') . '</span>';
	}

	$ad_title = get_the_title();
	if (function_exists('adforest_title_limit')) {
		$ad_title = adforest_title_limit($ad_title);
	}

	if ($col == 0) {
		$my_ads .= '<div class="item">';
	} else {
		$my_ads .= '<div class="col-md-' . esc_attr($col) . ' col-xs-12 col-sm-' . esc_attr($sm) . '">';
	}
	
	$locations_arr =  wp_get_object_terms($pid, array('ad_country'), array('orderby' => 'term_group'));
	$locations_arr = array_reverse($locations_arr);
	$locations = $locations_arr[0];

	$loc = get_term($locations);
	$location_html .= '<span><a class="ad_location" href="' . get_term_link($loc->term_id) . '">' . esc_html($loc->name) . '</a></span>';
	
	
	$my_ads .= '
	<div class="category-grid-box">

		<div class="category-grid-img">
			<a title="' . get_the_title() . '" href="' . get_the_permalink() . '">
				' . $img . '
				' . $is_feature . '
			</a>
		</div>

		<div class="short-description">

			<h3>
				<a title="' . get_the_title() . '" href="' . get_the_permalink() . '">' . $ad_title . '</a>
			</h3>

			<div class="price">
				' . adforest_adPrice($pid) . '
			</div>
			
		</div>

		<div class="ad-meta-info ad-info">
			<ul>
				<li>
					<i class="fa fa-map-marker"></i>
					' . $location_html . '
				</li>
			</ul>
		</div>
	</div>';

	$my_ads .= '</div>';

	return $my_ads;
}






add_shortcode('home_cats', 'home_cats_func');
function home_cats_func(){

	$html = '';
	$cats = '';
	$locations = '';
	$nav = '';

	// cats
	$args = array(
		'taxonomy'     => 'ad_cats',
		'parent'        => 0,
		'hide_empty'    => false          
	);
	$terms = get_terms( $args );
	foreach($terms as $term) {
		$cats .= '
		<a class="home_cat" href="' . get_term_link( $term->term_id, 'ad_cats' ) . '">
			<div class="cat_image">
				<img src="' . get_term_meta($term->term_id,'cat_image',true) . '">
			</div>
			<div class="home_cat_title">' . $term->name . '</div>
		</a>
		';
	}
	
	
	// locations
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
	foreach($sorted_terms as $term_id=>$term_name) {
		$locations .= '
		<a class="home_location" href="' . get_term_link( $term_id, 'ad_country' ) . '">
			<div class="cat_title"><i class="fa fa-angle-right"></i>' . $term_name . '</div>
		</a>
		';
	}	




	$nav .= '
	<div class="home_cats_nav_wrap">
		<span class="nav_cats">[:ru]Категории[:ua]Категорії[:]</span> | <span class="nav_locations">[:ru]Регионы[:ua]Регіони[:]</span>
	</div>
	';

	$html .= '
	<div class="home_main_cats_wrap">
		<section class="section-padding "> 
			<div class="container"> 
				<div class="row"> 
					<div class="row">
						<div class="cats">
							<div class="cats_wrap">
								' . $cats . '
							</div>
						</div>
						<div class="locations"  style="display: none;">
							<div class="locations_wrap">
								' . $locations . '
							</div>
						</div>
						' . $nav . '
					</div>
				</div>
			</div>
		</section>
	</div>
	';


	return __($html);
}






/* Add Image Upload to Taxonomy */
// Add Upload fields to "Add New Taxonomy" form
function add_series_image_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="series_image"><?php _e( 'Series Image:', 'journey' ); ?></label>
		<input type="text" name="series_image[image]" id="series_image[image]" class="series-image" value="<?php echo $seriesimage; ?>">
		<input class="upload_image_button button" name="_add_series_image" id="_add_series_image" type="button" value="Select/Upload Image" />
		<script>
			jQuery(document).ready(function() {
				jQuery('#_add_series_image').click(function() {
					wp.media.editor.send.attachment = function(props, attachment) {
						jQuery('.series-image').val(attachment.url);
					}
					wp.media.editor.open(this);
					return false;
				});
			});
		</script>
	</div>
<?php
}
// Add Upload fields to "Edit Taxonomy" form
function journey_series_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "weekend-series_$t_id" ); ?>
	
	<tr class="form-field">
	<th scope="row" valign="top"><label for="_series_image"><?php _e( 'Series Image', 'journey' ); ?></label></th>
		<td>
			<?php
				$seriesimage = get_term_meta($t_id,'cat_image',true); 
				?>
			<input type="text" name="series_image[image]" id="series_image[image]" class="series-image" value="<?php echo $seriesimage; ?>">
			<input class="upload_image_button button" name="_series_image" id="_series_image" type="button" value="Select/Upload Image" />
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"></th>
		<td style="height: 150px;">
			<style>
				div.img-wrap {
					background-size:contain; 
					max-width: 450px; 
					max-height: 150px; 
					width: 100%; 
					height: 100%; 
					overflow:hidden; 
				}
				div.img-wrap img {
					max-width: 450px;
				}
			</style>
			<div class="img-wrap">
				<img src="<?php echo $seriesimage; ?>" id="series-img">
			</div>
			<script>
			jQuery(document).ready(function() {
				jQuery('#_series_image').click(function() {
					wp.media.editor.send.attachment = function(props, attachment) {
						jQuery('#series-img').attr("src",attachment.url)
						jQuery('.series-image').val(attachment.url)
					}
					wp.media.editor.open(this);
					return false;
				});
			});
			</script>
		</td>
	</tr>
<?php
}
// Save Taxonomy Image fields callback function.
function save_series_custom_meta( $term_id ) {
	if ( isset( $_POST['series_image'] ) ) {
		update_term_meta($term_id,'cat_image',$_POST['series_image']['image']);
	}
}
add_action( 'ad_cats_edit_form_fields', 'journey_series_edit_meta_field', 10, 2 );
add_action( 'ad_cats_add_form_fields', 'add_series_image_field', 10, 2 );
add_action( 'edited_ad_cats', 'save_series_custom_meta', 10, 2 );  
add_action( 'create_ad_cats', 'save_series_custom_meta', 10, 2 );












// WC 
// remove checkout fields
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
	unset($fields['billing']['billing_first_name']);
	unset($fields['billing']['billing_last_name']);
	unset($fields['billing']['billing_company']);
	unset($fields['billing']['billing_address_1']);
	unset($fields['billing']['billing_address_2']);
	unset($fields['billing']['billing_city']);
	unset($fields['billing']['billing_postcode']);
	unset($fields['billing']['billing_country']);
	unset($fields['billing']['billing_state']);
	unset($fields['billing']['billing_phone']);
	unset($fields['order']['order_comments']);
	unset($fields['billing']['billing_email']);
	unset($fields['account']['account_username']);
	unset($fields['account']['account_password']);
	unset($fields['account']['account_password-2']);
    return $fields;
}


// cart on checkout page
add_action( 'woocommerce_before_checkout_form', 'bbloomer_cart_on_checkout_page_only', 5 );
function bbloomer_cart_on_checkout_page_only() {
	if ( is_wc_endpoint_url( 'order-received' ) ) return;
	echo do_shortcode('[woocommerce_cart]');
}

add_action( 'template_redirect', 'bbloomer_redirect_empty_cart_checkout_to_home' );
function bbloomer_redirect_empty_cart_checkout_to_home() {
   if ( is_cart() && is_checkout() && 0 == WC()->cart->get_cart_contents_count() && ! is_wc_endpoint_url( 'order-pay' ) && ! is_wc_endpoint_url( 'order-received' ) ) {
      wp_safe_redirect( home_url() . '/packages/' );
      exit;
   }
}




// WC REDIRECT AFTER PAYMENT
add_action( 'woocommerce_thankyou', 'bbloomer_redirectcustom');
function bbloomer_redirectcustom( $order_id ){
	$order = wc_get_order( $order_id );


    if ( $order->status == 'completed' ) {
        wp_safe_redirect( 'https://harchi.in.ua/thankyou/' );
        exit;
    }  

    if ( $order->status == 'pending' ) {
		
		wp_delete_post($order_id,true);
/*
		$pay_now_url = esc_url( $order->get_checkout_payment_url() );
		echo $pay_now_url;
		die();
*/		
        wp_safe_redirect( 'https://harchi.in.ua/error/?order_id=' . $order_id );
        exit;
    }
}



/* VIP ADS ON SEARCH PAGE */
function adforest_get_search_vip_ads_list($args) {
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
	if ($vip_pids) { ?>
		<div class="posts-masonry" style="padding-bottom: 50px;">
			<div class="col-md-12 col-xs-12 col-sm-12">
				<?php
				foreach($vip_pids as $vip_pid) {
					echo ai_adforest_search_layout_list($vip_pid);
				}
				?>
			</div>
		</div>
	<?php } 	
}




/* FIX AD THUMBNAIL */
add_action( 'wp_loaded', function(){
	remove_action( 'single_template', 'adforest_set_ad_featured_img' );
});

add_filter('single_template', 'adforest_set_ad_featured_img2');
function adforest_set_ad_featured_img2($single_template) {
    global $post;
    if ($post->post_type == 'ad_post') {
        $media = adforest_get_ad_images($post->ID);
        $img_ids = '';
        if (is_array($media) && count($media) > 0) {
			
			$m = $media[0];
			$mid = '';
			if (isset($m->ID)) {
				$mid = $m->ID;
			} else {
				$mid = $m;
			}
			if ($mid != get_post_thumbnail_id($post->ID)) {
				set_post_thumbnail($post->ID, $mid);
			}

        }
    }
    return $single_template;
}