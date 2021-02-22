
import { jQuery as $ } from './src/jquery-3.5.1.js';
import V from './custom/Paths.js';
import { timed, K } from './custom/K.js';
import { clamp as $clamp } from './src/clamp.js';

const timer = timed(),
	tileWidth = 216;

$(() => {

	getSearchResults();

	$(window).scroll(function() {
		const _el = $('.entry').last(),
			tEl = _el.offset().top,
			bEl = _el.offset().top + _el.outerHeight(),
			bSc = $(window).scrollTop() + $(window).innerHeight(),
			tSc = $(window).scrollTop();

		if ((bSc > tEl) && (tSc < bEl)) {
			timer.run(() => {
				getSearchResults();
			}, 500);
		}

		const hasFixed = $('.query-box').hasClass('fixed'),
			top = $(window).scrollTop();
		if (top > 0 && !hasFixed) {
			$('.query-box, .terms-box, .filter-box, .list').addClass('fixed');
		} else if (!top && hasFixed) {
			$('.query-box, .terms-box, .filter-box, .list').removeClass('fixed');
		}
	});

	const onResize = () => {
		const list = $('main .list');
		list.css('grid-template-columns', `repeat(${Math.floor(list.outerWidth(true) / tileWidth)}, 1fr)`);
		$('.detail-wrap').css('max-height', $('.channel-title').outerHeight(true) + $('.featured-video').outerHeight(true) + 30 + 'px');
	};
	onResize();
	$(window).on('resize', onResize);

	const _catBox = $('.category-box'),
		_catScroll = $('.scroller', _catBox),
		_catNext = $('.nav.next'),
		_catPrev = $('.nav.prev');

	let scrollWidth = 0;
	$('.link', _catScroll).each(function() {
		scrollWidth += parseInt($(this).outerWidth() + 10);
	});
	_catScroll.width(scrollWidth);

	const section = {
		total: 0,
		current: 0,
		width: null,
		running: false
	};

	const catButtons = () => {
		const bW = _catBox.width(),
			sW = _catScroll.width(),
			l = Math.abs(_catScroll.position().left),
			next = l + bW < sW,
			prev = l != 0;

		section.total = Math.ceil((sW / (bW / 2)));
		section.width = sW / section.total;

		_catPrev[prev ? 'addClass' : 'removeClass']('active');
		_catBox[prev ? 'addClass' : 'removeClass']('prev');
		_catNext[next ? 'addClass' : 'removeClass']('active');
		_catBox[next ? 'addClass' : 'removeClass']('next');

		section.running = false;
	};
	catButtons();

	_catNext.on('click', () => {

		if (section.running) return;
		section.running = true;

		if (section.current != section.total - 1) {
			section.current++;
			_catScroll.css({
				left: -(section.current * section.width)
			});
		}

		setTimeout(() => {
			catButtons();
		}, 600);
	});

	_catPrev.on('click', () => {

		if (section.running) return;
		section.running = true;

		if (section.current != 0) {
			section.current--;
			_catScroll.css({
				left: -(section.current * section.width)
			});
		}

		setTimeout(() => {
			catButtons();
		}, 600);
	});

	$('.more-info').on('click', function() {
		const open = $(this).hasClass('open'),
			_el = $('+ .collapse', this);
		if (open) {
			_el.removeClass('shadows');
			setTimeout(() => {
				_el.slideUp(600);
			}, 300);
		} else {
			_el.slideDown(600, () => {
				_el.addClass('shadows');
			});
		}

		$(this)[`${open ? 'remove' : 'add'}Class`]('open');
	});

	$('.detail-head').on('click', function() {
		if ($(document).width() > 720) return;

		const _me = $(this).parents('.detail-box'),
			open = _me.hasClass('open'),
			_el = $('.collapse', _me);
		_el[`slide${open ? 'Up' : 'Down'}`](600);
		_me[`${open ? 'remove' : 'add'}Class`]('open');
	});
});

let offset = 0,
	loadingMore = false,
	found = null;

const postsPerPage = () =>
	Math.max(6, Math.round($('.list').outerWidth(true) / tileWidth) * Math.round($(window).outerHeight(true) / 300));

function getSearchResults() {
	if (loadingMore) {
		timer.run(() => {
			getSearchResults();
		}, 500);
		return;
	}

	loadingMore = true;

	offset = $('.list .entry').length;

	if (!K.empty(found) && offset === found) {
		loadingMore = false;
		return;
	}

	createLoaders();

	const query = {
		post_type: ['video'],
		tax_query: {
			0: {
				taxonomy: 'video-channel',
				field: 'term_id',
				terms: [$('.list').attr('term')]
			}
		},
		posts_per_page: postsPerPage(),
		offset
	};

	// console.log(query);

	$.ajax({
		url: V.ajax,
		data: {
			query,
			action: 'custom_search',
			nonce: $('.content').data('nonce')
		}
	}).done(function(data) {
		data = JSON.parse(data.replace(/0$/, ''));

		// console.log(data);

		const start = offset + 1;
		$.each(data.posts, (i, v) => {
			$(`article[index=${start + i}]`).replaceWith($(v).attr('index', start + i));
		});

		$('.entry.only-loader').remove();
		$('.entry .entry-title').clamp({ clamp: 2 });
		$('.entry').has('.entry-meta').each(function() {
			$('.detail-type', this).replaceWith($('.entry-date', this));
			$('.entry-meta', this).remove();
		});

		doCounter(data.found);

		loadingMore = false;
	});
}

function doCounter(total) {
	const _w = $('.results');
	if (!total) {
		found = null;
		_w.addClass('none');
		return;
	}

	found = total;

	_w.removeClass('none');
	$('.got', _w).text($('.entry').length);
	$('.total', _w).text(total);
}

function createLoaders() {
	const posts = postsPerPage(),
		start = offset + 1;
	for (let i = start; i < start + posts; i++) {
		$('.list').append($(
			`<article index="${i}" class="entry only-loader">
				<div class="load-ripple">
					<div></div>
					<div></div>
				</div>
			</article>`
		));
	}

	// onResize(true);
}

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
