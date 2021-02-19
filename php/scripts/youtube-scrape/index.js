import { jQuery as $ } from '../../../scripts/src/jquery-3.5.1.js';

$(() => {
	let old = '';

	setInterval(() => {
		$.ajax({
			url: './info/latest-log.php?v',
			type: 'GET',
			cache: false
		}).done(function(data) {
			data = JSON.parse(data);
			if (old != data.content) {
				$('.logs').html(`<pre>${data.content}</pre>`);
				old = data.content;

				$([document.documentElement, document.body]).animate({
					scrollTop: $('#scroller').offset().top
				}, 300);
			}
		});
	}, 1000);

	$('#kill-switch').on('click', function() {
		$.ajax({
			url: 'kill-switch.php?v',
			type: 'GET',
			data: {
				kill: true
			},
			cache: false
		});
	});

	$('#cancel-kill').on('click', function() {
		$.ajax({
			url: 'kill-switch.php?v',
			type: 'GET',
			data: {
				kill: false
			},
			cache: false
		});
	});
});
