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
	});
})(jQuery);
