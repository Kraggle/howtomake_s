import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import V from './custom/Paths.js';

$(() => {

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
				if (data.success)
					$(this).removeClass('doMe');
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
			});

		} else {
			if (!$(this).hasClass('doMe'))
				return;

			const loop = parseInt($(this).attr('repeat') || 0),
				ids = $(this).data('ids');

			if (loop && ids.length) {

				const total = ids.length;

				const loopCall = () => {
					if (!ids.length) {
						$(this).removeClass('doMe');
						$(`#${other}`).addClass('doMe');
						doProgress(10, 10);
						return;
					}

					ajax(action, {
						ids: ids.splice(0, loop)
					}, data => {
						if (data.success) {
							$(`#${$(`#${other}`).attr('count')}`).val(ids.length);

							loopCall();
							doProgress(total, ids.length);
						}
					});
				};

				loopCall();
			} else {

				ajax(action, data => {

					if (data.success) {
						$(this).removeClass('doMe');
						$(`#${other}`).addClass('doMe');

						$(`#${$(`#${other}`).attr('count')}`).val(data.count);
					}
				});
			}
		}
	});

	$('input[type="checkbox"').on('change', function() {
		$(this).parents('.ks-setting-box').find('.ks-button').addClass('doMe');
	});
});

function doProgress(total, left) {
	const current = total - left - 1,
		goto = current * 100 / total;
	$('.ks-progress-back').css('width', `${goto}%`);
	$('.ks-progress-number').text(`${goto.toFixed(3)}%`);
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
		data: {
			action,
			nonce: $('.ks-box').data('nonce'),
			data
		}
	}).done(result => {
		result = JSON.parse(result.replace(/0$/, ''));
		// console.log(result);

		callback(result);
	});
}
