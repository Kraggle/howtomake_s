import { jQuery as $ } from '../../../scripts/src/jquery-3.5.1-min.js';

$(() => {
	setInterval(() => {
		$.ajax({
			url: './info/latest-log.php?v',
			type: 'GET',
			cache: false
		}).done(function(data) {
			data = JSON.parse(data);
			if (data.content)
				$('.logs').html(`<pre>${data.content}</pre>`);
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
