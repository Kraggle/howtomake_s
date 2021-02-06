import { jQuery as $ } from '../src/jquery-3.5.1-min.js';

export default {
	build() {

		$('.gallery-nav').on('click', '.gallery-btn:not(.active)', function() {
			$('.gallery .active').removeClass('active');
			$(`#${$(this).attr('btn')}`).addClass('active');
			$(this).addClass('active');
		});

		$('.gallery-arrow').on('click', function() {
			let index = parseInt($('.gallery-item.active').attr('index'));
			const last = parseInt($('.gallery-item').last().attr('index'));
			index = $(this).hasClass('left') ? index - 1 : index + 1;
			index = index < 0 ? last : index > last ? 0 : index;

			$(`.gallery .active`).removeClass('active');
			$(`.gallery [index=${index}]`).addClass('active');
		});
	}
};
