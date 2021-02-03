import { jQuery as $ } from '../../../scripts/src/jquery-3.5.1-min.js';

$(() => {
	setInterval(() => {
		$.getJSON('./info/latest-log.php', data => {
			if (data.content)
				$('.logs').html(`<pre>${data.content}</pre>`);
		});
	}, 5000);

	$('#kill-switch').on('click', function() {
		$.ajax({
			url: 'kill-switch.php',
			data: {
				kill: true
			}
		});
	});

	$('#cancel-kill').on('click', function() {
		$.ajax({
			url: 'kill-switch.php',
			data: {
				kill: false
			}
		});
	});
});
