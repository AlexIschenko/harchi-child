jQuery(document).ready(function ($) {

	//$('body').css('padding-top', $('header').outerHeight());

	
	
	if ($.fn.fancybox) {
		jQuery('[data-fancybox="group"]').fancybox({
			loop: 1,
			buttons: ["close"],
			animationEffect: "fade",
			animationDuration: 200,
		});

	}

	
	
	var site_lang = $('html').attr('lang');



	$("select").on("select2:open", function (event) {
		$('input.select2-search__field').attr('placeholder', 'начните писать для поиска...');
	});



	$("select").select2({
		placeholder: $('#select_place_holder').val(),
		allowClear: true,
		width: '100%',
		language: {
			noResults: function (params) {
				return "Ничего не найдено...";
			}
		}
	});

	if (site_lang == 'uk') {
		var cat_search_placeholder = 'Оберіть розділ...';
		$("select").on("select2:open", function (event) {
			$('input.select2-search__field').attr('placeholder', 'почніть писати для пошуку...');
		});
	} else if (site_lang == 'ru-RU') {
		var cat_search_placeholder = 'Выберите раздел...';
		$("select").on("select2:open", function (event) {
			$('input.select2-search__field').attr('placeholder', 'начните писать для поиска...');
		});
	} else {
		var cat_search_placeholder = 'Select category...';
	}

	$("select.category").select2({
		placeholder: cat_search_placeholder,
		allowClear: true,
		width: '100%',
		language: {
			noResults: function (params) {
				return "Ничего не найдено...";
			}
		}
	});



	var adforest_ajax_url = $('#adforest_ajax_url').val();

	ai_check_notification();

	// CHECK NOTIFICATION INTERVAL
	if ($('#msg_notification_on').val() != "" && $('#msg_notification_on').val() != 0) {
		if ($('#is_logged_in').val() == '1') {
			setInterval(function () {

				ai_check_notification();

			}, 60000);
		}
	}


	// CHECK NOTIFICATION ON PAGE INTERACTIONS
	$(document).ajaxComplete(function (event, xhr, settings) {

		var response = settings.data;

		if (response) {
			if (JSON.stringify(response) != '{}') {

				// check notifications
				var check_m = response.indexOf("sb_check_messages");
				var get_n = response.indexOf("sb_get_notifications");
				if (check_m == -1 && get_n == -1) {
					ai_check_notification();
				}

				// post ad form req fields
				var get_sub_cat = response.indexOf("sb_get_sub_cat");
				if (get_sub_cat != -1) {
					setTimeout(function () {
						if ($('#ad_cat_sub').is(":visible")) {
							$('#ad_cat_sub').attr('data-parsley-required', 'true');
						} else {
							$('#ad_cat_sub').attr('data-parsley-required', 'false');
						}
						if ($('#ad_cat_sub_sub').is(":visible")) {
							$('#ad_cat_sub_sub').attr('data-parsley-required', 'true');
						} else {
							$('#ad_cat_sub_sub').attr('data-parsley-required', 'false');
						}
						if ($('#ad_cat_sub_sub_sub').is(":visible")) {
							$('#ad_cat_sub_sub_sub').attr('data-parsley-required', 'true');
						} else {
							$('#ad_cat_sub_sub_sub').attr('data-parsley-required', 'false');
						}
					}, 200);
				}
			}
			console.log(response);
		}
		//console.log(get_sub_cat);


	});



	function ai_check_notification() {
		$.post(adforest_ajax_url, { action: 'sb_check_messages', new_msgs: $('#is_unread_msgs').val(), }).done(function (response) {

			var get_r = response.split('|');
			if ($.trim(get_r[0]) == '1') {
				toastr.success(get_r[1], '', { timeOut: 5000, "closeButton": true, "positionClass": "toast-bottom-left" });

				$('#is_unread_msgs').val(get_r[2]);
				$('.msgs_count').html(get_r[2]);
				$('.menu_item_messeges .menu_badge').html(get_r[2]);
				$.post(adforest_ajax_url, { action: 'sb_get_notifications' }).done(function (notifications) {
					$('.message-center').html(notifications);
					show_red_dots(notifications);
				});
			}
			if ($.trim(get_r[0]) == '0') {

				$('#is_unread_msgs').val(get_r[2]);
				$('.msgs_count').html('');
				$('.menu_item_messeges .menu_badge').html('');
				$.post(adforest_ajax_url, { action: 'sb_get_notifications' }).done(function (notifications) {
					$('.message-center').html(notifications);
					show_red_dots(notifications);
				});
			}
			if ($.trim(get_r[0]) == '2') {

				$('#is_unread_msgs').val(get_r[2]);
				$('.msgs_count').html(get_r[2]);
				$('.menu_item_messeges .menu_badge').html(get_r[2]);
				$.post(adforest_ajax_url, { action: 'sb_get_notifications' }).done(function (notifications) {
					$('.message-center').html(notifications);
					show_red_dots(notifications);
				});

			}
		});
	}

	function show_red_dots(notifications) {
		notifications_html = $.parseHTML('<div class="all_notification">' + notifications + '</div>');
		var sended_dot = false;
		var recived_dot = false
		$(notifications_html).find('a').each(function () {
			var url = $(this).attr('href');
			var has_get = url.indexOf("sb_get_messages");
			var has_load = url.indexOf("sb_load_messages");
			if (has_get == -1) {
				recived_dot = true;
			}
			if (has_load == -1) {
				sended_dot = true;
			}
		});
		// recived
		if (recived_dot) {
			$('.messages_actions[sb_action="received_msgs_ads_list"]').addClass('has_red_dot');
		} else {
			$('.messages_actions[sb_action="received_msgs_ads_list"]').removeClass('has_red_dot');
		}
		// sended
		if (sended_dot) {
			$('.messages_actions[sb_action="my_msgs_outbox"]').addClass('has_red_dot');
		} else {
			$('.messages_actions[sb_action="my_msgs_outbox"]').removeClass('has_red_dot');
		}
		// all messages tab when not active
		if (recived_dot || sended_dot) {
			$('li.active .menu-name[sb_action="my_msgs"]').removeClass('has_red_dot');
			$('li:not(.active) .menu-name[sb_action="my_msgs"]').addClass('has_red_dot');
		}
		// clear
		if (!recived_dot && !sended_dot) {
			$('li .menu-name[sb_action="my_msgs"]').removeClass('has_red_dot');
		}
	}




	$('.owl-carousel').each(function () {

		var h = $(this).outerHeight();

		$(this).find('.owl-item').each(function () {

			$(this).height(h);

		});

	});



	// PROFILE AVATAR
	$('body').on('click', '.btn.remove_avatar', function (e) {
		$('#imgInp').val('');
		$('#imgInp').trigger('change');

	});
	$("body").on('DOMSubtreeModified', "#img-upload", function () {
		$('.img-circle').attr('src', $(this).attr('src'));
	});






	// SEARCH HEADER
	var search_category_field = $('#search_category_field');
	var search_location_field = $('#search_location_field');
	var search_category_modal = $('#search_category_modal');
	var search_location_modal = $('#search_location_modal');
	// CATS
	$(document).click(function (event) {
		if (!$(event.target).closest("#search_category_field, #search_category_modal").length) {
			search_category_modal.hide();
		}
	});
	// OPEN CATEGORY SELECT PANEL
	$('#search_category_field').click(function () {
		search_category_modal.css('top', search_category_field.parent().position().top + search_category_field.outerHeight(true));
		search_category_modal.toggle();
	});
	// AJAX CATEGORY NAVIGATION AND SELECT
	$(document).on('click', '#search_category_modal .ad_cat_item', function (event) {
		event.stopPropagation();
		var current_cat_id = $(this).attr('data-cat_id');
		var current_has_child = $(this).attr('data-has_child');
		if (current_has_child == 'true') {
			// load child cats
			if ($(this).attr('data-sub_loaded') == 'false') {
				// load sub cats
				$.ajax({
					type: "POST",
					url: mainajax.ajaxurl,
					data: {
						action: 'get_sub_cats',
						cat_id: current_cat_id
					},
					beforeSend: function ( xhr ) {
						$('.ad_cat_item_' + current_cat_id).find('.bull_preloader').addClass('bull_loading');
					},
					success: function (response) {
						$('.cat_' + current_cat_id + '_sub_cat').html(response);
						$('.ad_cat_item_' + current_cat_id).attr('data-sub_loaded', true);
						$('.cat_' + current_cat_id + '_sub_cat').show();
						$('.ad_cat_item_' + current_cat_id).find('.bull_preloader').removeClass('bull_loading');
					}
				});
			} else {
				// show sub cats without loading
				$('.cat_' + current_cat_id + '_sub_cat').show();
			}
		} else {
			// select cat as no child
			$('input[name="cat_id"]').val(current_cat_id);
			$('#search_category_field').val($(this).find('.cat_title').text());
			$('.search_category_field_clear').show();
			search_category_modal.hide();

		}
	});
	// CATEGORY BACK
	$(document).on('click', '.back_to_top_level', function (event) {
		event.stopPropagation();
		var current_cat_id = $(this).attr('data-cat_id');
		$('.cat_' + current_cat_id + '_sub_cat').hide();
	});
	// CATEGORY CLEAR
	$(document).on('click', '.search_category_field_clear', function (event) {
		event.stopPropagation();
		$('input[name="cat_id"]').val('');
		$('#search_category_field').val('');
		$('.search_category_field_clear').hide();
	});
	// CATEGORY CLOSE
	$(document).on('click', '#search_category_modal .current_top_level', function (event) {
		event.stopPropagation();
		search_category_modal.hide();

	});



	// LOCATIONS
	$(document).click(function (event) {
		if (!$(event.target).closest("#search_location_field, #search_location_modal").length) {
			search_location_modal.hide();
		}
	});
	// OPEN CATEGORY SELECT PANEL
	$('#search_location_field').click(function () {
		search_location_modal.css('top', search_location_field.parent().position().top + search_location_field.outerHeight(true));
		search_location_modal.toggle();
	});
	// AJAX LOCATION NAVIGATION AND SELECT
	$(document).on('click', '#search_location_modal .ad_cat_item', function (event) {
		event.stopPropagation();
		var current_cat_id = $(this).attr('data-cat_id');
		var current_has_child = $(this).attr('data-has_child');
		if (current_has_child == 'true') {
			// load child cats
			if ($(this).attr('data-sub_loaded') == 'false') {
				// load sub cats
				$.ajax({
					type: "POST",
					url: mainajax.ajaxurl,
					data: {
						action: 'get_sub_locs',
						cat_id: current_cat_id
					},
					beforeSend: function ( xhr ) {
						$('.ad_cat_item_' + current_cat_id).find('.bull_preloader').addClass('bull_loading');
					},
					success: function (response) {
						$('.cat_' + current_cat_id + '_sub_cat').html(response);
						$('.ad_cat_item_' + current_cat_id).attr('data-sub_loaded', true);
						$('.cat_' + current_cat_id + '_sub_cat').show();
						$('.ad_cat_item_' + current_cat_id).find('.bull_preloader').removeClass('bull_loading');
					}
				});
			} else {
				// show sub cats without loading
				$('.cat_' + current_cat_id + '_sub_cat').show();
			}
		} else {
			// select cat as no child
			$('input[name="country_id"]').val(current_cat_id);
			$('#search_location_field').val($(this).find('.cat_title').text());
			$('.search_location_field_clear').show();
			search_location_modal.hide();

		}
	});
	// LOCATION BACK
	$(document).on('click', '.back_to_top_level', function (event) {
		event.stopPropagation();
		var current_cat_id = $(this).attr('data-cat_id');
		$('.cat_' + current_cat_id + '_sub_cat').hide();
	});
	// LOCATION CLEAR
	$(document).on('click', '.search_location_field_clear', function (event) {
		event.stopPropagation();
		$('input[name="country_id"]').val('');
		$('#search_location_field').val('');
		$('.search_location_field_clear').hide();
	});
	// LOCATION CLOSE
	$(document).on('click', '#search_location_modal .current_top_level', function (event) {
		event.stopPropagation();
		search_location_modal.hide();
	});

	if (search_category_field.val() != '') {
		$('.search_category_field_clear').show();
	}
	if (search_location_field.val() != '') {
		$('.search_location_field_clear').show();
	}




	
	$('.nav_locations').click(function() {
		$('.cats').hide();
		$('.locations').show();
	});
	
	$('.nav_cats').click(function() {
		$('.locations').hide();
		$('.cats').show();
	});

});





















