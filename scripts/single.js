import { jQuery as $ } from './src/jquery-3.5.1-min.js';

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

	$('iframe').each(function() {
		const p = $(this).parent();
		if (p.hasClass('video-wrap')) return;
		if (p.hasClass('content-wrap'))
			$(this).wrap('<p class="iframe-wrap"></p>')
		else p.addClass('iframe-wrap');
	});
});
