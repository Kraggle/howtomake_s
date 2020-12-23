import { jQuery as $ } from '../src/jquery3.4.1.js';

export default {
	build() {

		const onResize = () => {
			const using = $('.list-part:visible').length;
			if ($('.list').data('using') != using) {

				const parts = [
					null,
					$('.list-part[part=1]'),
					$('.list-part[part=2]'),
					$('.list-part[part=3]')
				];

				let items = $('.list .entry');

				if (items.data().hasOwnProperty('order')) {
					items = items.sort(function(a, b) {
						const sA = parseInt($(a).data('order')),
							sB = parseInt($(b).data('order'));
						return (sA < sB) ? -1 : (sA > sB) ? 1 : 0;
					});
				}

				let current = 1;

				items.each(function(i) {
					if (!$(this).data().hasOwnProperty('order'))
						$(this).data('order', i);

					$(this).clone(true).appendTo(parts[current]);
					$(this).remove();
					current = current == using ? 1 : current + 1;
				});

				$('.list').data('using', using);
			}
		};
		onResize();

		$(window).on('resize', onResize);
	}
};
