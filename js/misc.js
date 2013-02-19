;(function($) {
	var tab = $('.subsubsub a');
	if (!tab.length) return;
	tab.on('click', function(){
		tab.removeClass('current');
		$(this).addClass('current');
	});
})(jQuery);