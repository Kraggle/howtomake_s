import { jQuery as $ } from './src/jquery-3.5.1-min.js';
// import { onResize, list } from './partials/list.js';
import V from './custom/Paths.js';
import { timed, K } from './custom/K.js';
import { clamp as $clamp } from './src/clamp.js';

const timer = timed();

$(() => {
	// list.build();

	// INFO:: Build the dropdown menus
	$('.dropdown').each(function() {
		const _me = $(this);
		_me.addClass('closed');

		const _s = $('<div />', {
			class: 'selected',
			html: $('select option:selected', _me).html()
		}).appendTo(_me);

		// INFO: Action when opening the dropdown
		_me.on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			const has = $(this).hasClass('closed');
			closeAllSelect(this);
			has && (_me.removeClass('closed'), $('.items', _me).slideDown(300));
		});

		const _dd = $('<div />', {
			class: 'items'
		}).appendTo(_me);

		const _dw = $('<div />', {
			class: 'items-wrap'
		}).appendTo(_dd);

		$('select option', _me).each(function() {
			$('<div />', {
				value: $(this).attr('value'),
				html: $(this).html()
			}).appendTo(_dw).on('click', function() {
				_s.html($(this).html());
				$('select', _me).val($(this).attr('value')).trigger('change');
			});
		});

		$(this).wrap('<div class="drop-wrap" />');
	});

	$(document).on('click', closeAllSelect);

	// INFO:: Build the toggle buttons 
	$('.toggle').each(function() {
		const _me = $(this),
			_rw = $('<div />', {
				class: 'toggle-wrap'
			}).appendTo(_me);

		$('label:not(.name)', this).each(function() {
			const f = $(this).attr('for');

			$('<div />', {
				class: 'item' + ($(`#${f}`, _me).is(':checked') ? ' show' : ''),
				for: f,
				html: $(this).html()
			}).appendTo(_rw);
		});

		_me.on('click', function() {
			const f = $('.item:not(.show)', _me).attr('for');
			$(`#${f}`, _me).prop('checked', true).trigger('change');
			$('.show', _me).removeClass('show');
			$(`.item[for="${f}"]`, _me).addClass('show');
		});
	});

	// INFO:: Build the check boxes
	$('.check').each(function() {
		const _me = $(this),
			data = $(this).data();

		$('<div />', {
			class: 'marker'
		}).prependTo($(this)).on('click', function() {
			const _in = $(this).siblings('input');
			!_in.attr('disabled') && _in.attr('enabled') == 'true' && (
				_in.prop('checked', !_in.is(':checked')),
				_in.trigger('change')
			);
		});

		$('input', this).prependTo($(this));

		const disableOnCheck = function() {
			let _ins = $(`input[disable="${data.disable}"]`);
			const on = $(this).is(':checked');
			_ins.attr('enabled', on).prop('checked', on);

			if (on) {
				_ins = $(`input[disable*="${data.disable}"]`);
				_ins.prop('checked', on);
			}
		};

		if (data.disable) {
			// disableOnCheck.call($('input', this));
			$('input', this).on('change', disableOnCheck);
		}

		$('label', this).on('click', e => {
			if ($('input', _me).attr('enabled') == 'false')
				e.preventDefault();
		});
	});

	// INFO:: Support for the multi checks
	$('.checks').each(function() {
		const _me = $(this),
			_ins = $('input', _me),
			data = _me.data();

		const disableAtMin = () => {
			const i = parseInt(data.atLeast),
				cls = `.checks[data-name="${data.include}"][enabled="true"]`;
			let _els = _me;

			if (data.include && $(cls).length)
				_els = _els.add($(cls));

			const _cd = $('input:checked', _els);
			_cd.attr('disabled', _cd.length == i);

		};

		if (data.atLeast) {
			disableAtMin();
			_ins.on('change', disableAtMin);
		}

		$('.any', this).on('click', function() {
			if ($(_me).attr('enabled') === 'true')
				$('input:not(:checked)', _me).prop('checked', true).trigger('change');
		});
	});

	getSearchResults();

	$('main input:not(#search), main select').on('change', reloadResults);
	$('#search').on('input', reloadResults);

	$('.search-box .clear').on('click', function() {
		$('#search').val('').trigger('input');
	});

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

		const hasFixed = $('.query-vars').hasClass('fixed'),
			top = $(window).scrollTop();
		if (top > 0 && !hasFixed) {
			$('.query-vars, .which-box').addClass('fixed');
		} else if (!top && hasFixed) {
			$('.query-vars, .which-box').removeClass('fixed');
		}
	});

	const onResize = () => {
		const list = $('main .list');
		list.css('grid-template-columns', `repeat(${Math.round(list.outerWidth(true) / 300)}, 1fr)`);
	};
	onResize();
	$(window).on('resize', onResize);

	$('.mobile-button').on('click', function() {
		const _m = $('.mobile-wrap');
		if (_m.hasClass('open')) {
			_m.removeClass('open');
		} else {
			_m.addClass('open');
		}
	});
});

function reloadResults() {
	timer.run(() => {
		getSearchResults(true);
	}, 500);
}

let offset = 0,
	loadingMore = false,
	found = null;

const postsPerPage = () =>
	Math.max(6, Math.round($('.list').outerWidth(true) / 300) * Math.round($(window).outerHeight(true) / 300));

function getSearchResults(reset = false) {
	if (loadingMore) {
		timer.run(() => {
			getSearchResults(reset);
		}, 500);
		return;
	}

	loadingMore = true;

	reset && $('.list article.entry').remove();

	offset = $('.list .entry').length;

	if (!K.empty(found) && offset === found) {
		loadingMore = false;
		return;
	}

	createLoaders();

	const query = {
		s: $('#search').val(),
		orderby: $('#orderby').val(),
		order: $('input[name=order]:checked').val(),
		post_type: [],
		tax_query: {},
		posts_per_page: postsPerPage(),
		offset
	};

	const active = {},
		aTerms = {};
	$('.type-box input:checked').each(function() {
		active[$(this).attr('name')] = true;
		aTerms[$(this).attr('taxonomy')] = [];
		query.post_type.push($(this).attr('name'));
	});

	const _terms = $('.cat-wrap input[enabled="true"]:checked');
	_terms.each(function() {
		const data = $(this).data('object');
		$.each(data, (type, tax) => {
			if (!active[type]) return;
			aTerms[tax.taxonomy].push(tax.slug);
		});
	});

	if (Object.keys(aTerms).length > 1) query.tax_query = { relation: 'OR' };
	let j = 0;
	$.each(aTerms, (taxonomy, terms) => {
		query.tax_query[j] = {
			taxonomy,
			field: 'slug',
			terms
		};
		j++;
	});

	// console.log(query);

	$.ajax({
		url: V.ajax,
		data: {
			query,
			action: 'custom_search',
			nonce: $('.query-vars').data('nonce')
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

function closeAllSelect() {
	$('.dropdown .items').slideUp(300, () => {
		$('.dropdown').addClass('closed');
	});
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
