import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import { K, timed } from './custom/K-min.js';
import './custom/jquery-visible-min.js';
import V from './custom/Paths.js';
import CodeMirror from './src/codemirror-min.js';
import './src/CodeMirror/mode/htmlmixed/htmlmixed.js';
import './src/CodeMirror/addon/display/placeholder.js';
import * as S from './keywords-shared.js';
import SVG from './custom/SVGIcons-min.js';

$(() => {
	/* eslint-disable no-undef */
	const isIn = !!params.is_user_logged_in;
	/* eslint-enable no-undef */

	const _tool = $('.tool-wrap'),
		_setting = $('.setting-wrap'),
		_result = $('.results-wrap');
	$('.tab-wrap', _tool).css('grid-template-columns', `repeat(${$('.tab', _tool).length}, max-content)`);

	// MARK: Tab switch button action
	$('.content-wrap').on('click', '.tab:not(.active)', function() {
		const tab = $(this).attr('tab'),
			_p = $(this).parents('.tab-wrap');
		$('.active', _p.parent()).removeClass('active');
		$(this).add($(`.box[box=${tab}]`)).addClass('active');

		tab == 'html' && editor.refresh();
		$(this).closest('[which]').attr('which') == 'Result' && (
			keys.active = tab,
			$('.results-wrap .box.active .filter.input').trigger('input'),
			redoTabs()
		);
		K.local(`kdt${$(this).closest('.tab-box').attr('which')}Tab`, tab);
	});

	let resizer = false,
		sSize, sPos, area;

	// MARK: Textarea resize action
	$('.textarea .handle').on('mousedown', function(e) {
		area = $(this).siblings('textarea');
		!area.visible() && (area = $(this).siblings('.CodeMirror'));
		sSize = area.outerHeight();
		sPos = e.clientY + 25;
		resizer = true;
	});

	$(window).on('mousemove', function(e) {
		if (!resizer) return;
		area.height(sSize + e.clientY - sPos);
	});

	$(window).on('mouseup', function(e) {
		if (!resizer) return;
		resizer = false;
		area.focus();
	});
	// END textarea resize action

	// MARK: CodeMirror
	const editor = CodeMirror.fromTextArea($('textarea.code').get(0), {
		lineNumbers: true,
		mode: 'text/html',
		lineWrapping: true
	});

	// MARK: Select previous tab
	const tab = K.local('kdtToolTab');
	tab && $(`.tool-wrap [tab=${tab}]`).click();

	// MARK: Check url on enter pressed
	$('input[type=url]').on('keyup', e => {
		const _btn = $('.button[btn=url]');
		e.which == 13 && !_btn.hasClass('loading') && _btn.trigger('click');
	});

	// MARK: Get default settings
	const keys = {
		numbers: {
			1: 'one',
			2: 'two',
			3: 'three',
			4: 'four',
			5: 'five',
			6: 'six',
			7: 'seven',
			8: 'eight',
			9: 'nine',
		},
		active: 1
	};

	const settings = {
		user() {
			return $.getJSON(V.ajax, {
				action: 'keywords_get_user',
				nonce: $('#keyword_nonce').val()
			}, function(data) {
				S.convertSettings(keys, data);
			});
		},
		get() {
			const data = {};
			$('[key]', _setting).each(function() {
				const _i = $('input', this),
					key = $(this).attr('key');
				let value = [];
				if (_i.length > 1) {
					_i.each(function() {
						$(this).val() && value.push($(this).val());
					});
				} else value = _i.attr('type') == 'checkbox' ? _i.is(':checked') : _i.val();
				data[key] = value;
			});
			S.convertSettings(keys, data);

			return data;
		}
	};

	settings.user();

	// MARK: Populate tables with results
	const updateResults = () => {
		const n = keys.active;
		keys.active = n < keys.min ? keys.min : (n > keys.max ? keys.max : n);

		const _this = $('.results-wrap'),
			_bWrap = $('.box-wrap', _this),
			_tWrap = $('.tab-wrap', _this);

		$('.tab-wrap', _this).css(
			'grid-template-columns',
			`repeat(${keys.max - (keys.min - 1)}, max-content)`
		);

		const grabTab = num => {
			const _el = $(`.tab[tab=${num}]`, _tWrap),
				plural = num == 1 ? '' : 's',
				word = keys.numbers[num] || '',
				active = keys.active == num ? ' active' : '';
			return _el.length ? _el[`${active ? 'add' : 'remove'}Class`]('active') : $('<div />', {
				class: `tab${keys.active == num ? ' active' : ''}`,
				tab: num,
				text: `${word.firstToUpper()} Word${plural}`
			});
		};

		const radio = num => {
			const filter = K.local('filterRadio') || 'exact';
			return ['and', 'or', 'exact'].map(word =>
				`<input type="radio"  name="filter-radio-${num}" id="filter_${word}_${num}" value="${word}" ${filter == word ? 'checked' : ''} >
				<label class="item" for="filter_${word}_${num}">${word}</label>`
			).join('');
		};

		const grabBox = num => {
			const _el = $(`.box[box=${num}]`, _bWrap),
				active = keys.active == num ? ' active' : '';
			return _el.length ? _el[`${active ? 'add' : 'remove'}Class`]('active') : $('<div />', {
				class: `box${active}`,
				box: num,
				html: `<div class="filter-wrap">
						<label class="input-wrap" for="filter_${num}">
							<span class="head">Filter</span>
							<input id="filter_${num}" type="text" class="filter input" filter="${num}"/>
						</label>
						<div class="radio-wrap">
							${radio(num)}
							<div class="selector"></div>
						</div>
					</div>
					<div class="keys-table"></div>`
			});
		};

		for (let i = 1; i <= 10; i++) {
			const _tab = grabTab(i),
				_box = grabBox(i);
			if (i < keys.min || i > keys.max) {
				_tab.remove();
				_box.remove();
			} else {
				_tab.appendTo(_tWrap);
				_box.appendTo(_bWrap);
			}
		}

		$.each(keys.groups, (count, group) => {
			const _box = $(`[box=${count}] .keys-table`, _bWrap);
			_box.html(
				`<div class="row head">
					<div class="head-wrap left" order="asc" sort="filter">
						<span>Keywords</span>
						<div class="icon">${SVG.sort}</div>
					</div>
					<div class="head-wrap" order="asc" sort="frequency">
						<span>Frequency</span>
						<div class="icon">${SVG.sort}</div>
					</div>
					<div class="head-wrap sort" order="desc" sort="density">
						<span>Density</span>
						<div class="icon">${SVG.sort}</div>
					</div>
				</div>
				<div class="sort-table"></div>
				<div class="none">There are no results!</div>`
			);

			$.each(group, (i, key) => {

				$('.sort-table', _box).append(
					`<div class="row item show" filter="${key.keywords}" 
						frequency="${key.count}" density="${(key.density * 10000).toFixed(0)}">
						<span class="left">${key.keywords}</span>
						<span>${key.count}</span>
						<span>${key.density.toFixed(2)}%</span>
					</div>`
				);
			});

			$('.none', _box).css('display', group.length ? 'none' : 'block');
		});

		$('.hidden').removeClass('hidden');
		$('.loading').removeClass('loading');
		redoTabs();
	};

	// MARK: Check button click action
	$('.box-wrap').on('click', '.button:not(.loading)', function() {
		const _this = $(this);
		$('.error.url').removeClass('show');
		$(this).addClass('loading');

		const type = $(this).attr('btn'),
			_box = $(this).closest(`[box=${type}]`),
			resume = content => {
				keys.content = content;
				S.getKeywords(keys);
				updateResults();
			};

		switch (type) {
			case 'url':
				$.getJSON(V.ajax, {
					action: 'get_cross_origin',
					url: $('input', _box).val(),
					nonce: $('#keyword_nonce').val()
				}, r => {
					if (r.error) {
						$('.error.url').addClass('show').text(r.error);
						_this.removeClass('loading');
					} else resume(r.content);
				});
				break;

			case 'html':
				resume(editor.getValue());
				break;

			case 'text':
				resume($('textarea', _box).val());
				break;
		}
	});

	// MARK: Collapse/expand button action
	$('.button.collapse').on('click', function() {
		const _wrap = $(this).closest('.collapse-wrap');
		_wrap[`${_wrap.hasClass('active') ? 'remove' : 'add'}Class`]('active');
	});

	// MARK: Ensuring min and max on inputs is adhered to
	$('input[type=number][min][max]').on('blur', function() {
		const val = parseInt($(this).val()),
			min = parseInt($(this).attr('min')),
			max = parseInt($(this).attr('max')),
			sum = $(this).attr('sum'),
			other = $(this).attr('other') ? parseInt($(`#${$(this).attr('other')}`).val()) : false;

		if (!val) {
			$(this).val($(this).attr($(this).attr('resolve')));
		} else if (val > max) {
			if (sum && !K.evaluate[sum](val, other)) {
				$(this).val(other);
				return;
			}
			$(this).val(max);
		} else if (val < min) {
			if (sum && !K.evaluate[sum](val, other)) {
				$(this).val(other);
				return;
			}
			$(this).val(min);
		} else if (sum && !K.evaluate[sum](val, other)) {
			$(this).val(other);
		}
	});

	// MARK: Add entry button action
	_setting.on('click', '.button.add[add]', function() {
		const type = $(this).attr('add'),
			_item = $(this).siblings(`[is=${type}]`).eq(0).clone();
		_item.insertAfter($(this)).find('input').val('');
	});

	_setting.on('input change propertyChange', 'input', function() {
		activeUpdate(true);
	});

	_setting.on('click', '.update.button:not(.active)', function() {
		$(this).addClass('loading active');
		const data = settings.get();
		S.getKeywords(keys);
		updateResults();
		activeUpdate(false);

		$.ajax({
			url: V.ajax,
			type: 'POST',
			data: {
				action: 'keywords_save_user',
				nonce: $('#keyword_nonce').val(),
				data
			}
		});
	});

	const updateTimer = timed(),
		activeUpdate = active => updateTimer.run(() => {
			_setting[`${active ? 'add' : 'remove'}Class`]('do-update');
			$('.update.button', _setting).removeClass('active loading');
		}, 500);

	// MARK: Delete button action
	_setting.on('click', '.button.delete', function() {
		const type = $(this).attr('delete'),
			_item = $(this).closest(`[is=${type}]`);
		if ($(`[is=${type}]`, _setting).length > 1) _item.remove();
		else $('input', _item).val('');
		activeUpdate(true);
	});

	$('.button.clear', _setting).on('click', () => {
		const selector = '.collapse-wrap .input-wrap',
			item = $(selector).eq(0).clone();
		$(selector).remove();
		item.insertAfter($('.collapse-wrap .button.add')).find('input').val('').trigger('input');
	});

	$('.button.default', _setting).on('click', () => {
		const selector = '.collapse-wrap .input-wrap',
			item = $(selector).eq(0).clone();
		$(selector).remove();

		$.getJSON(V.ajax, {
			action: 'keywords_get_default',
			nonce: $('#keyword_nonce').val()
		}, function(data) {
			$.each(data.ignore_list, (i, word) => {
				const _i = item.clone();
				$('input', _i).val(word);
				$('.collapse-wrap', _setting).append(_i);
			});

			$('.collapse-wrap .input-wrap input', _setting).trigger('input');
		});

	});

	// MARK: Filter input action 
	_result.on('input propertyChange', '.filter.input', function() {
		const data = $(this).data();
		!data.timer && (data.timer = timed());
		data.timer.run(() => {
			const val = $(this).val(),
				_el = $(this).closest('.box');
			$('.filter.input', _result).val(val);
			_el.find('[filter]').removeClass('show');
			val && _el.find(S.filter[K.local('filterRadio') || 'exact'](val)).addClass('show');
			!val && _el.find('[filter]').addClass('show');
		}, 500);
	});

	_result.on('change propertyChange', '.radio-wrap input', function() {
		const val = $(this).val();
		K.local('filterRadio', val);
		$(`input[value=${val}]`, _result).prop('checked', true);
		$('.results-wrap .box.active .filter.input').trigger('input');
	});

	_result.on('click', '.head-wrap', function() {
		let order = $(this).attr('order');
		const sort = $(this).attr('sort');
		if ($(this).hasClass('sort')) {
			order = order == 'asc' ? 'desc' : 'asc';
			$(this).attr('order', order);
		} else {
			$(this).siblings('.sort').removeClass('sort');
			$(this).addClass('sort');
		}

		const _box = $(this).closest('.keys-table'),
			sorted = _box.find('.row.item').sort((a, b) => {
				let sA = $(a).attr(sort),
					sB = $(b).attr(sort);

				const down = order == 'asc' ? -1 : 1,
					up = order == 'asc' ? 1 : -1;

				if (!isNaN(sA)) {
					sA = parseInt(sA);
					sB = parseInt(sB);
				}

				return sA < sB ? down : (sA > sB ? up : 0);
			});

		$('.sort-table', _box).html(sorted);
	});

	const redoTabs = () => {
		const width = $('.content .wrapper').outerWidth(),
			tabs = $('.tab', _result);

		let tabWidth = 10;

		tabs.each(function(i) {
			const w = $(this).outerWidth() + 11;
			tabWidth += w;
		});

		if (width < tabWidth) {
			const margin = (tabWidth - width) / (tabs.length - 1);
			tabs.css('margin-left', `-${margin}px`).addClass('blend');
		} else {
			tabs.css('margin-left', 0).removeClass('blend');
		}
	};
});

$.expr.pseudos.regex = $.expr.createPseudo(function(expression) {
	return function(el) {
		const matchParams = expression.split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels)
					? matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels, '')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
		return regex.test($(el)[attr.method](attr.property));
	};
});
