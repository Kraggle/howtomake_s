import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import { clamp as $clamp } from './src/clamp-min.js';

const tileWidth = 216;

$(() => {

	const youtube = $('.youtube').length > 0;

	const onResize = () => {
		const list = $('main > .yarpp-related .list');
		list.css('grid-template-columns', `repeat(${Math.floor(list.outerWidth(true) / tileWidth)}, 1fr)`);

		if (youtube) {
			const id = 'single-list-style',
				cH = `${$('.content-wrap').outerHeight(true) - $('.channel-title').outerHeight(true)}px`;
			$(`#${id}`).remove();

			$('#htm_s-single-css').after(`
				<style id="${id}">
					body .youtube .side-content .yarpp-related {
						max-height: min(${cH}, calc(100vh - 275px));
					}
					
					body:not(.scrolled):not(.at-top) .youtube .side-content .yarpp-related {
						max-height: min(${cH}, calc(100vh - 185px));
					}
					
					body.scrolled .youtube .side-content .yarpp-related {
						max-height: min(${cH}, calc(100vh - 95px));
					}
				</style>
			`);
		}
	};
	onResize();
	$(window).on('resize', onResize);

	$('.entry .entry-title').clamp({ clamp: 2 });
	$('.entry').has('.entry-meta').each(function() {
		$('.detail-type', this).replaceWith($('.entry-date', this));
		$('.entry-meta', this).remove();
	});

	if (youtube) {
		$('.youtube .content-wrap .iframe-wrap').remove();

		$('.show-more').on('click', function() {
			const has = $(this).hasClass('open');
			let max = 0;

			$('.desc').children().each(function() {
				max += $(this).outerHeight(true);
			});

			$(this)[`${has ? 'remove' : 'add'}Class`]('open');
			$('.desc').css('max-height', (has ? 250 : max) + 'px');

			let i = 0;
			const int = setInterval(() => {
				onResize();
				i++;
				i == 3 && clearInterval(int);
			}, 200);
		});
	}

	$('.youtube .button-wrap .video').click(function() {

		var btn = $(this);



		btn.addClass('loading');

		$.ajax({
			url: params.ajax,
			data: {
				action: 'user_post_interaction',
				nonce: $('.youtube .button-wrap ').data('nonce'),
				post_id: btn.data('post-id'),
				user_action: btn.data('action'),
				state: btn.hasClass('selected') ? 'off' : 'on'// Previous state
			}
		}).done(function(json) {
			btn.removeClass('loading')
			btn.toggleClass('selected');

			// When turning on Like/Dislike, switch the other one off
			if (btn.hasClass("like") && btn.hasClass("selected")) $(".dislike").removeClass("selected");
			if (btn.hasClass("dislike") && btn.hasClass("selected")) $(".like").removeClass("selected");


			var data = JSON.parse(json)
			console.log(data)

		})
	})
});

$.fn.extend({
	clamp(options) {
		this.each(function() {
			if ($(this).data('clamp') !== options) {
				$clamp(this, options);
				$(this).data('clamp', options);
			}
		});
	}
});
