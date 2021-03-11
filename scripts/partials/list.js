import { jQuery as $ } from '../src/jquery-3.5.1-min.js';

export const onResize = force => {
	const using = $('.list-part:visible').length;
	if (force || $('.list').data('using') != using) {

		const parts = [
			null,
			$('.list-part[part=1]'),
			$('.list-part[part=2]'),
			$('.list-part[part=3]')
		];

		let items = $('.list .entry');

		if (items.length && items.attr('index')) {
			items = items.sort(function(a, b) {
				const sA = parseInt($(a).attr('index')),
					sB = parseInt($(b).attr('index'));
				return (sA < sB) ? -1 : (sA > sB) ? 1 : 0;
			});

		} else if (items.length && 'order' in items.data()) {
			items = items.sort(function(a, b) {
				const sA = parseInt($(a).data('order')),
					sB = parseInt($(b).data('order'));
				return (sA < sB) ? -1 : (sA > sB) ? 1 : 0;
			});
		}

		let current = 1;

		items.each(function(i) {
			if (!('order' in $(this).data()))
				$(this).data('order', i);

			$(this).clone(true).appendTo(parts[current]);
			$(this).remove();
			current = current == using ? 1 : current + 1;
		});

		$('.list').data('using', using);
	}
};

export const list = {
	build() {

		onResize();
		$(window).on('resize', onResize);
	}
};
