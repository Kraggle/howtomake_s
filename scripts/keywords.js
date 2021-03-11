import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import V from './custom/Paths.js';
import { K, timed } from './custom/K-min.js';
import SVG from './custom/SVGIcons-min.js';

$(() => {
	const _this = $('#htm-keyword-info');
	if (!_this.length) return;

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
		ignore: [],
		timed: timed(),
		content: null,
		new: false,
		on: true, // true = visual, false = text
		groups: {},

		update(e) {
			keys.timed.run(() => {
				keys.new = false;
				if (!e) e = $('textarea#content');
				keys.on = !!e.level;

				const content = keys.on ? e.level.content : $(e.target ? e.target : e).val();

				if (content !== keys.content) {
					keys.content = content;
					keys.task();
				}
			}, keys.new ? 0 : keys.refresh * 1000);
		},

		createElements() {
			$('.htm-tab-wrap .tab-group').css(
				'grid-template-columns',
				`repeat(${keys.max - (keys.min - 1)}, max-content)`
			);

			const grabTab = num => {
				const _el = $(`.tab[tab=${num}]`, _this);
				return _el.length ? _el : $('<div />', {
					class: 'tab',
					tab: num,
					text: num
				});
			};

			const grabBox = num => {
				const _el = $(`.box[box=${num}]`, _this),
					plural = num == 1 ? '' : 's';
				return _el.length ? _el : $('<div />', {
					class: 'box',
					box: num,
					html: `<h3 class="title">Keywords <span>(${keys.numbers[num]} word${plural})</span></h3>
					<input type="text" class="filter-input" filter="${num}" placeholder="Keyword Filter"/>
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
					_tab.appendTo('.htm-tab-wrap .tab-group');
					_box.appendTo('.htm-tab-wrap .htm-boxes');
				}
			}
		},

		task() {
			keys.createElements();
			// console.log('%c content has changed so we\'re working on keyword density', 'color:#f69552');

			const text = $('<div/>', {
				html: keys.content
			}).text();
			// console.log(text);

			const blocks = text.split(/[ ]{0,}[.;:?!\n][ ]{0,}/);
			// console.log(blocks);

			const groups = {};
			for (let i = keys.min; i <= keys.max; i++)
				groups[i] = {};

			let wordCount = 0;

			$.each(blocks, (j, v) => {
				if (v.length < 4 || !v.match(/\w+/)) return;

				const words = v.split(/ \W{0,}|\W{0,} /);
				wordCount += words.length;
				$.each(groups, (count, group) => {
					count = parseInt(count);

					if (words.length >= count) {

						$.each(words, (i, word) => {
							let string = '';
							for (let l = i; l < count + i; l++) {
								if (!words[l]) return;
								string += ' ' + words[l];
							}
							string = string.trim().toLowerCase();
							const first = string.replaceAll('’', '\'').match(/^[^ ]+/)[0],
								last = string.replaceAll('’', '\'').match(/[^ ]+$/)[0];

							if (string.length < 3 || string.match(/^(\d+|\d.+\d)$/)) return;
							if (keys.enabled) {
								if (count < 3 && (K.isInArray(first, keys.ignore) || K.isInArray(last, keys.ignore))) return;
								if (count > 2 && K.isInArray(first, keys.ignore) && K.isInArray(last, keys.ignore)) return;
							}

							if (!group[string]) group[string] = 0;
							group[string]++;
						});
					}
				});
			});

			keys.groups = {};
			$.each(groups, (count, group) => {
				keys.groups[count] = [];
				$.each(group, (words, found) => {
					found >= keys.count && keys.groups[count].push({
						keywords: words,
						count: found,
						density: (found / (wordCount / count)) * 100
					});
				});

				keys.groups[count].sort((a, b) => b.count - a.count);
			});

			keys.display();
		},

		display() {
			$.each(keys.groups, (count, group) => {
				const _box = $(`.htm-boxes [box=${count}] .keys-table`);
				_box.html(`
					<div class="row head">
						<div class="head-wrap" order="asc" sort="filter">
							<span class="left">Keywords</span>
							<div class="icon">${SVG.sort}</div>
						</div>
						<div class="head-wrap" order="asc" sort="frequency">
							<span>Freq.</span>
							<div class="icon">${SVG.sort}</div>
						</div>
						<div class="head-wrap sort" order="desc" sort="density">
							<span>Density</span>
							<div class="icon">${SVG.sort}</div>
						</div>
					</div>
					<div class="sort-table"></div>
				`);

				$.each(group, (i, key) => {

					$('.sort-table', _box).append(`
						<div class="row item" filter="${key.keywords}" 
							frequency="${key.count}" density="${(key.density * 10000).toFixed(0)}">
							<span class="left">${key.keywords}</span>
							<span>${key.count}</span>
							<span>${key.density.toFixed(2)}%</span>
						</div>
					`);
				});
			});
		},

		settings() {
			$.getJSON(V.ajax, {
				action: 'get_keyword_settings',
				nonce: $('#keyword_nonce').val()
			}, function(data) {
				keys.ignore = data.ignore_list;
				keys.min = parseInt(data.min_words);
				keys.max = parseInt(data.max_words);
				keys.refresh = parseInt(data.refresh);
				keys.count = parseInt(data.min_count);
				keys.enabled = data.ignore_enabled == 'true';
				keys.content = '';
				keys.new = true;
				keys.update();
			});
		}
	};

	$('#post-body-content').on('input propertyChange', 'textarea#content', keys.update);

	const editor = setInterval(() => {
		/* eslint-disable no-undef */
		if (tinyMCE.editors.length && tinyMCE.editors[0].id === 'content') {
			tinyMCE.editors[0].on('change', keys.update);
			clearInterval(editor);
		}
		/* eslint-enable no-undef */
	}, 2000);

	keys.settings();

	_this.on('click', '.tab:not(.active)', function() {
		$('.active', _this).removeClass('active');
		$(this).addClass('active');
		const _box = $(`.box[box=${$(this).attr('tab')}]`, _this);
		_box.addClass('active');
		$('.filter-input', _box).trigger('input');

	});

	_this.on('click', '.button.delete', function() {
		const type = $(this).attr('delete'),
			_item = $(this).closest(`[is=${type}]`);
		if ($(`[is=${type}]`, _this).length > 1)
			_item.remove();
		else
			$('input', _item).val('');
	});

	_this.on('click', '.button-primary[add]', function() {
		const type = $(this).attr('add'),
			_before = $(this).parent(),
			_item = _before.siblings(`[is=${type}]`).eq(0).clone();
		_item.insertBefore(_before).find('input').val('');
	});

	_this.on('input propertyChange', '.filter-input', function() {
		const data = $(this).data();
		!data.timer && (data.timer = timed());
		data.timer.run(() => {
			const val = $(this).val(),
				_el = $(this).parent();
			$('.filter-input', _this).val(val);
			_el.find('[filter]').removeClass('hide');
			val && _el.find(`:not([filter*=${val}])`).addClass('hide');
		}, 500);
	});

	_this.on('click', '.head-wrap', function() {
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

	$('.save', _this).on('click', function() {
		const data = {};
		$('[key]', _this).each(function() {
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

		$.ajax({
			url: V.ajax,
			type: 'POST',
			data: {
				action: 'set_keyword_settings',
				nonce: $('#keyword_nonce').val(),
				data
			}
		}).done(() => {
			keys.settings();
		});
	});
});
