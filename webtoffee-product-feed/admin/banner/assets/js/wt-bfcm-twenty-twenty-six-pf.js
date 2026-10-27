(function ($) {
	'use strict';

	$(function () {
		const wt_bfcm_twenty_twenty_six_banner_pf = {
			init: function () {
				const data_obj = {
					_wpnonce: wt_bfcm_twenty_twenty_six_banner_js_params_pf.nonce,
					action: wt_bfcm_twenty_twenty_six_banner_js_params_pf.action,
					wt_bfcm_twenty_twenty_six_banner_action_type_pf: '',
				};

				$(document).on( 'click', '.wt-bfcm-banner-2026-pf .bfcm_cta_button', function (e) {
					e.preventDefault();
					const elm = $(this);
					window.open( wt_bfcm_twenty_twenty_six_banner_js_params_pf.cta_link, '_blank' );
					elm.parents( '.wt-bfcm-banner-2026-pf' ).hide();
					data_obj['wt_bfcm_twenty_twenty_six_banner_action_type_pf'] = 3; /** Clicked the button. */

					$.ajax({
						url: wt_bfcm_twenty_twenty_six_banner_js_params_pf.ajax_url,
						data: data_obj,
						type: 'POST'
					});
				}).on( 'click', '.wt-bfcm-banner-2026-pf .notice-dismiss', function (e) {
					e.preventDefault();
					data_obj['wt_bfcm_twenty_twenty_six_banner_action_type_pf'] = 2; /** Closed by user. */

					$.ajax({
						url: wt_bfcm_twenty_twenty_six_banner_js_params_pf.ajax_url,
						data: data_obj,
						type: 'POST',
					});
				});
			}
		};
		wt_bfcm_twenty_twenty_six_banner_pf.init();
	});

})(jQuery);
