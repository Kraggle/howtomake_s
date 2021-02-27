import { jQuery as $ } from './src/jquery-3.5.1.js';
import header from './partials/header.js';
import dialog from './util/dialog.js';
import './custom/jquery-visible.js';
import { timed } from './custom/K.js';

$(() => {
	header.build();

	/* Open Modal Window */
	dialog.show();

	$('a[href^="#"]').on('click', function(e) {
		const id = $(this).attr('href');
		if (!id.match(/^#/) || id.length == 1 || !$(id).length) return;

		e.preventDefault();

		$([document.documentElement, document.body]).animate({
			scrollTop: $(id).offset().top - 170
		}, 800);
	});

	const onResize = () => {

		if ($('.wrap.with-description').length) {
			let h = 0;
			$('.main > .title, .main > .meta, .main > .description').each(function() {
				h += $(this).outerHeight();
			});
			$('.body-decor').height(h);
			$('.wrap > .content').css('margin-top', `-${h + 100}px`);
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

	const _side = $('.sidebar');
	if (_side.length) {
		let old, found;
		const timer = timed();
		$(window).on('scroll', () => {
			found = false;
			$('.content-wrap > [id!=""][id]').each(function() {
				const id = '#' + $(this).attr('id'),
					_btn = $(`.sidebar [href="${id}"]`);

				if (!found && $(this).visible(true) && _btn.length) {
					found = true;

					if (this != old) {
						old = this;
						_btn.siblings().removeClass('active');
						_btn.addClass('active');

						timer.run(() => {

							let top = 0;
							_btn.prevAll().each(function() {
								top += $(this).outerHeight(true);
							});

							$('.sidebar .content').animate({
								scrollTop: top
							}, 300);
						}, 100);
					}
				}
			});
		});
	}
});

