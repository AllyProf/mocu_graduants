(function ($) {
	'use strict';

	function hideMocuPreloader() {
		var $pre = $('.preloader');
		if (!$pre.length || $pre.data('hiding')) {
			return;
		}
		$pre.data('hiding', true);
		$('.status').fadeOut();
		$pre.delay(350).fadeOut('slow');
	}

	$(window).on('load', hideMocuPreloader);
	$(function () {
		if (document.readyState === 'complete') {
			hideMocuPreloader();
		}
		setTimeout(hideMocuPreloader, 2500);

		$(document).on('click', '[data-sg-back]', function (e) {
			var referrer = document.referrer || '';
			var sameOrigin = false;
			try {
				sameOrigin = referrer && new URL(referrer).origin === window.location.origin;
			} catch (err) {
				sameOrigin = false;
			}
			if (sameOrigin && window.history.length > 1) {
				e.preventDefault();
				window.history.back();
			}
		});
	});
})(jQuery);
