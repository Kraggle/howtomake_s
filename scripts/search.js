import { jQuery as $ } from './src/jquery3.4.1.js';
import { onResize, list } from './partials/list.js';
import V from './custom/Paths.js';
import { timed } from './custom/K.js';

const timer = timed();

$(() => {
	list.build();

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
		const data = $(this).data();

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
			const _me = $(`.checks[data-name="${data.disable}"]`),
				on = $(this).is(':checked');
			_me.attr('enabled', on);
			$('input', _me).attr('enabled', on).prop('checked', on);
		};

		if (data.disable) {
			// disableOnCheck.call($('input', this));
			$('input', this).on('change', disableOnCheck);
		}
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
	});
});

function reloadResults() {
	timer.run(() => {
		getSearchResults(true);
	}, 500);
}

let page = 0,
	loadingMore = false;

function getSearchResults(reset = false) {
	if (loadingMore) {
		timer.run(() => {
			getSearchResults(reset);
		}, 500);
		return;
	}

	loadingMore = true;

	if (reset) {
		$('.list article.entry').remove();
		page = 0;
	}

	page++;

	createLoaders();

	const query = {
		s: $('#search').val(),
		paged: page,
		orderby: $('#orderby').val(),
		order: $('input[name=order]:checked').val(),
		post_type: [],
		tax_query: {}
	};

	$('.type-box input:checked').each(function() {
		query.post_type.push($(this).attr('name'));
	});

	const _cats = $('.cat-wrap[enabled="true"]');
	if (_cats.length > 1) query.tax_query = { relation: 'OR' };
	_cats.each(function(i) {
		query.tax_query[i] = {
			taxonomy: $(this).attr('tax'),
			field: 'slug',
			terms: []
		};
		$('input:checked', this).each(function() {
			query.tax_query[i].terms.push($(this).attr('name'));
		});
	});

	console.log(query);

	$.ajax({
		url: V.ajax,
		data: {
			query,
			action: 'custom_search',
			nonce: $('.query-vars').data('nonce')
		}
	}).done(function(data) {
		data = JSON.parse(data.replace(/0$/, ''));
		const start = (page - 1) * 9;
		$.each(data.posts, (i, v) => {
			$(`article[index=${start + i}]`).replaceWith($(v).attr('index', start + i));
		});

		$('.entry.only-loader').remove();

		loadingMore = false;
	});
}

function closeAllSelect() {
	$('.dropdown .items').slideUp(300, () => {
		$('.dropdown').addClass('closed');
	});
}

function createLoaders() {
	const start = (page - 1) * 9;
	for (let i = start; i < start + 9; i++) {
		$('.list').append($(
			`<article index="${i}" class="entry only-loader">
				<div class="load-ripple">
					<div></div>
					<div></div>
				</div>
			</article>`
		));
	}

	onResize(true);
}
