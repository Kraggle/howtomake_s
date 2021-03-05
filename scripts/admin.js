import { jQuery as $ } from './src/jquery-3.5.1.js';
import V from './custom/Paths.js';
import { K, timed } from './custom/K.js';
import './src/jQuery-UI/jq-ui-core.js';
import './src/jQuery-UI/modules/jq-ui-autocomplete.js';

$(() => {
	time.build($('.ks-progress-time'));
	feed.build($('.ks-feed-text'));

	$('.ks-button[count]').addClass('doMe');

	$('.ks-button').on('click', function() {
		const count = $(this).attr('count'),
			action = $(this).attr('action'),
			other = $(this).attr('other'),
			type = $(this).attr('type');

		if (type) {
			if (!$(this).hasClass('doMe'))
				return;

			const inputs = {};
			$(this).parents('.ks-setting-box').find('input').each(function() {
				inputs[$(this).attr('name')] = $(this).is(':checked');
			});

			ajax(action, {
				inputs
			}, data => {
				if (data.success) {
					$(this).removeClass('doMe');

					if (data.action && data.ids && data.ids.length) {
						const total = data.ids.length;

						// console.log(data);

						const loopCall = () => {
							time.start();

							if (!data.ids.length) {
								$(this).removeClass('doMe');
								$(`#${other}`).addClass('doMe');
								doProgress(10, 10);
								time.reset();
								return;
							}

							ajax(data.action, {
								ids: data.ids.splice(0, data.loop)
							}, result => {
								if (result.success) {
									doProgress(total, data.ids.length);
									time.left(data.ids.length, data.loop).end();
									loopCall();
								}
							});
						};

						loopCall();
					}
				}
			});

		} else if (count) {
			ajax(action, data => {
				$(`#${count}`).val(data.count);

				if (data.count > 0) {
					$(this).removeClass('doMe');
					$(`#${other}`).addClass('doMe');
				}

				if (data.ids)
					$(`#${other}`).data('ids', data.ids);

				if (data.data)
					$(`#${other}`).data('data', data.data);
			});

		} else {
			if (!$(this).hasClass('doMe'))
				return;

			const loop = parseInt($(this).attr('repeat') || 0),
				ids = $(this).data('ids'),
				dataRelay = $(this).data('data'),
				doDouble = $(this).attr('doDouble');

			let length = ids.length,
				get = $(this).attr('get');
			if (get) {
				$(this).parents('.ks-setting-box').find(get).each(function() {
					if ($.type(get) != 'array') get = [];
					if ($(this).is(':checked'))
						get.push($(this).attr('name').replace(/^_/, ''));
				});
			}

			if (loop && ids.length) {

				const total = ids.length;

				const loopCall = () => {
					time.start();
					if (!ids.length) {
						$(this).removeClass('doMe');
						$(`#${other}`).addClass('doMe');
						doProgress(10, 10);
						time.reset();
						return;
					}

					ajax(action, {
						ids: ids.splice(0, loop),
						data: dataRelay,
						get
					}, data => {
						if (data.success) {
							if (!doDouble) {
								$(`#${$(`#${other}`).attr('count')}`).val(ids.length);

								doProgress(total, ids.length);
								time.left(ids.length, loop).end();
							}

							loopCall();

						} else if (data.double) doubleCall(data);
					});
				};

				const doubleCall = (use) => {
					time.start();
					if (use.action && use.ids && use.ids.length) {

						ajax(use.action, {
							ids: use.ids.splice(0, use.loop),
							data: dataRelay,
							get
						}, data => {
							if (data.success) {
								length -= use.loop;
								$(`#${$(`#${other}`).attr('count')}`).val(length);

								doProgress(total, length);
								time.left(length, use.loop).end();

								doubleCall(use);
							}
						});

					} else
						loopCall();

				};

				loopCall();
			} else {

				ajax(action, data => {

					if (data.success) {
						if (!$(this).hasClass('red')) {
							$(this).removeClass('doMe');
							$(`#${other}`).addClass('doMe');
						}

						$(`#${$(`#${other}`).attr('count')}`).val(data.count);
					}
				});
			}
		}
	});

	$('input[type="checkbox"').on('change', function() {
		$(this).parents('.ks-setting-box').find('.ks-button').addClass('doMe');
	});

	setInterval(() => {
		$('.edit').closest('.acf-input').each(function() {
			const _me = $(this);
			$('.edit', _me).length > 1 && $('.edit', _me).last().remove();

			const _els = $('> .edit, > .select2', _me);
			if (_els.length === 2) {
				_els.wrapAll('<div class="with-edit-wrap"></div>');

				$('.edit', _me).on('click', function(e) {
					e.preventDefault();
					const id = $('select option[selected=selected]', _me).val();
					if (id) {
						// console.log(id);

						$.ajax({
							url: V.ajax,
							type: 'POST',
							data: {
								action: 'get_edit_link',
								nonce: $('.admin-page-nonce').attr('nonce'),
								id
							}
						}).done(result => {
							result = JSON.parse(result);

							if (result.link) {
								console.log(result.link);
								const win = window.open(result.link, '_blank');
								win.focus();
							}
						});
					}
				});
			}
		});
	}, 2000);

	const ids = {
		el: $('div[data-name="toc"]'),
		list: [],
		changed: false,
		timed: timed(),
		text: {
			content: null
		},
		visual: {
			content: null
		},
		on: true, // true = visual, false = text
		update(e) {
			ids.timed.run(() => {
				if (!e) e = $('textarea#content');
				ids.on = !!e.level;

				const on = ids.on ? 'visual' : 'text',
					content = ids.on ? e.level.content : $(e.target ? e.target : e).val();

				if (content !== ids[on].content) {
					ids[on].content = content;

					const list = [];

					$('<div />', {
						html: content
					}).find('[id][id!=""]').each(function() {
						list.push($(this).attr('id'));
					});

					if (!K.equals(ids.list, list)) {
						ids.list = list;
						ids.changed = true;
						ids.autoComplete();
					}
				}
			}, 2000);
		},
		autoComplete() {
			const _els = $('td[data-name="id"] input', ids.el);
			_els.autocomplete({
				source: ids.list
			});

			const events = 'input change keyup click propertyChange';
			_els.off(events).on(events, ids.ifError);
			_els.each(ids.ifError);
		},
		ifError() {
			const val = $(this).val();
			if (ids.changed || $(this).data('value') !== val) {
				ids.changed = false;

				const _select = $(this).closest('.acf-table').find('[data-name=link] select'),
					link = $(':selected', _select).text() !== '- Select -';

				_select.data('timer') && clearInterval(_select.data('timer'));
				_select.data('timer', setInterval(() => {
					if (_select.data('value') !== $(':selected', _select).text()) {
						_select.data('value', $(':selected', _select).text());
						$(this).trigger('change');
						ids.changed = true;
					}
				}, 2000));

				$(this).data('value', val);
				$(this)[`${K.isInArray(val, ids.list) || !val || link ? 'remove' : 'add'}Class`]('error');
			}
		}
	};

	if (ids.el.length) {
		$('#post-body-content').on('input propertyChange', 'textarea#content', ids.update);

		const editor = setInterval(() => {
			/* eslint-disable no-undef */
			if (tinyMCE.editors.length && tinyMCE.editors[0].id === 'content') {
				tinyMCE.editors[0].on('change', ids.update);
				clearInterval(editor);
			}
			/* eslint-enable no-undef */
		}, 2000);

		ids.update();

		ids.el.on('click', 'a[data-event=add-row]', ids.autoComplete);
	}
});

function doProgress(total, left) {
	const current = total - left,
		goto = current * 100 / total;
	$('.ks-progress-back').css('width', `${goto}%`);
	$('.ks-progress-number').text(!goto ? '' : goto.toFixed(2) + '%');
}

function ajax(action) {
	const a = arguments;

	let data = {},
		callback = function() { };

	if (typeof a[1] === 'object') {
		data = a[1];

		if (typeof a[2] === 'function')
			callback = a[2];
	} else if (typeof a[1] === 'function')
		callback = a[1];

	// console.log(data);

	$.ajax({
		url: V.ajax,
		type: 'POST',
		data: {
			action,
			nonce: $('.ks-box').data('nonce'),
			data
		}
	}).done(result => {
		result = JSON.parse(result.replace(/0$/, ''));
		// console.log(result);

		if (result.message) {
			$.each(result.message, (i, v) => {
				setTimeout(() => {
					feed.add(v);
				}, i * 250);
			});
		}

		callback(result);
	});
}

const feed = {
	element: null,
	count: 0,

	build(element) {
		this.element = element;
	},

	clear() {
		if (!$(this.element).length) return;
		$(this.element).html('');
	},

	add(msg) {
		if (!$(this.element).length) return;
		this.count++;
		$(this.element).append(`<p>${msg}</p>`);
		const h = $(this.element).outerHeight(true);
		if (h > 84)
			$(this.element).css('top', `-${h - 84}px`);
	}
};

const time = {
	startTime: null,
	endTime: null,
	average: [],
	element: null,
	remain: 0,
	loop: 0,

	build(element) {
		this.element = element;
		return this;
	},

	reset() {
		this.average = [];
		$(this.element).html('');
		return this;
	},

	start() {
		this.startTime = new Date();
		return this;
	},

	end() {
		this.endTime = new Date();
		this.average.push(this.endTime - this.startTime);
		this.update();
		return this;
	},

	update() {
		if (!this.element || this.average.length < 3) return;
		const avg = (this.average.reduce((a, b) => a + b, 0) / this.average.length) || 0;
		const remain = avg * (this.remain / this.loop);
		$(this.element).html(this.time(remain));
	},

	time(ms) {
		let seconds = (ms / 1000).toFixed(0),
			minutes = Math.floor(seconds / 60),
			hours = '';
		if (minutes > 59) {
			hours = Math.floor(minutes / 60);
			minutes = minutes - (hours * 60);
		}

		seconds = Math.floor(seconds % 60);

		let result = hours ? `${hours} hours` : '';
		if (minutes && seconds) result += ` ${minutes} minutes and ${seconds} seconds`;
		else if (hours && seconds) result += ` and ${seconds} seconds`;
		else if (hours && minutes) result += ` and ${minutes} minutes`;
		else if (minutes) result += `${minutes} minutes`;
		else if (seconds) result += `${seconds} seconds`;
		result += ' estimated';
		return result;
	},

	left(left, loop) {
		this.remain = left;
		this.loop = loop;
		return this;
	}
};
