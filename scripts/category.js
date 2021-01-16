import { jQuery as $ } from './src/jquery3.4.1.js';
import { list } from './partials/list.js';
import gallery from './partials/gallery.js';

$(() => {

	list.build();
	gallery.build();
});

