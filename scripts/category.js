import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import { list } from './partials/list-min.js';
import gallery from './partials/gallery-min.js';

$(() => {

	list.build();
	gallery.build();
});

