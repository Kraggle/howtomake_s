import { jQuery as $ } from '../../../scripts/src/jquery-3.5.1-min.js';

$(() => {
	setInterval(() => {
		$.getJSON(`./info/latest-log.php?v=${makeID(10)}`, data => {
			if (data.content)
				$('.logs').html(`<pre>${data.content}</pre>`);
		});
	}, 1000);

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

function makeID(length) {
	let result = '';
	const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
		charactersLength = characters.length;
	for (let i = 0; i < length; i++)
		result += characters.charAt(Math.floor(Math.random() * charactersLength));

	return result;
}
