import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import header from './partials/header.js';

$(() => {
	header.build();

	$('a[href^="#"]').on('click', function(e) {
		const id = $(this).attr('href');
		if (!id.match(/^#/) || id.length == 1 || !$(id).length) return;

		e.preventDefault();

		$([document.documentElement, document.body]).animate({
			scrollTop: $(id).offset().top - 170
		}, 800);
	});

	const onResize = () => {
		if ($('.related-wrap').length) {
			const using = $('.related-part:visible').length;
			if ($('.related-wrap').data('using') != using) {

				const parts = [
					null,
					$('.related-part[part=1]'),
					$('.related-part[part=2]'),
					$('.related-part[part=3]'),
					$('.related-part[part=4]'),
					$('.related-part[part=5]')
				];

				let items = $('.related-wrap .related');

				if (items.data().hasOwnProperty('order')) {
					items = items.sort(function(a, b) {
						const sA = parseInt($(a).data('order')),
							sB = parseInt($(b).data('order'));
						return (sA < sB) ? -1 : (sA > sB) ? 1 : 0;
					});
				}

				let current = 1;

				items.each(function(i) {
					if (!$(this).data().hasOwnProperty('order'))
						$(this).data('order', i);

					$(this).clone(true).appendTo(parts[current]);
					$(this).remove();
					current = current == using ? 1 : current + 1;
				});

				$('.related-wrap').data('using', using);
			}
		}

		if ($('.more-side').length) {
			const using = $('.more-part:visible').length;
			if ($('.more-side').data('using') != using) {

				let items = $('.more-side .more-panel');

				if (items.data().hasOwnProperty('order')) {
					items = items.sort(function(a, b) {
						const sA = parseInt($(a).data('order')),
							sB = parseInt($(b).data('order'));
						return (sA < sB) ? -1 : (sA > sB) ? 1 : 0;
					});
				}

				if (using === 0) {
					items.each(function(i) {
						if (!$(this).data().hasOwnProperty('order'))
							$(this).data('order', i);

						$(this).clone(true).appendTo($('.more-side'));
						$(this).remove();
					});

					$('.more-side').data('using', using);
					return;
				}

				const parts = [
					null,
					$('.more-part[part=1]'),
					$('.more-part[part=2]'),
					$('.more-part[part=3]')
				];

				let current = 1;

				items.each(function(i) {
					if (!$(this).data().hasOwnProperty('order'))
						$(this).data('order', i);

					$(this).clone(true).appendTo(parts[current]);
					$(this).remove();
					current = current == using ? 1 : current + 1;
				});

				$('.more-side').data('using', using);
			}
		}
	};
	onResize();

	$(window).on('resize', onResize);

	const bar = $('#wpadminbar');

	if (bar.length) {
		bar.slideUp('slow');
		$('<div class="admin-bar-show"></div>').on('click', () => bar.slideToggle('slow')).appendTo('.body-wrap');
	}
});

