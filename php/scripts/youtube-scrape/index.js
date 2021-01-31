import { jQuery as $ } from '../../../scripts/src/jquery-3.5.1-min.js';

$(() => {
	setInterval(() => {
		$.getJSON('./logs/latest-log.php', data => {
			if (data.content)
				$('.logs').html(`<pre>${data.content}</pre>`);
		});
	}, 1000);
});
