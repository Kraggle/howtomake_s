import { jQuery as $ } from './src/jquery3.4.1.js';

$(() => {
	$('.main').append($('.yarpp-related'));

	if ($('.et_pb_text_inner').length)
		$('.content-wrap').html($('.et_pb_text_inner').html());

	$('.content-wrap img').each(function() {
		if ($(this).hasClass('emoji')) return;
		if ($(this).parent().hasClass('content-wrap')) {
			$(this).wrap('<div class="image-wrap"></div>');
			return;
		}

		const parent = $(this).parentsUntil('.content-wrap'),
			sibs = $(this).siblings(),
			img = $(this).add().wrap('<div class="image-wrap"></div>').parent();

		parent.after(img);
		img.after(sibs);

		parent.remove();
	});

	$('.content-wrap a[href*="#"]').each(function() {

		$(this).attr('href', $(this).attr('href').replace(/^[^#]+/, ''));
	});

	$('.content-wrap a[href^="#"]').on('click', function(e) {
		e.preventDefault();
		const id = $(this).attr('href');

		if (id.length == 1 || !$(id).length) return;

		$([document.documentElement, document.body]).animate({
			scrollTop: $(id).offset().top - 170
		}, 800);
	});
});
