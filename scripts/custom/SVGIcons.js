import { jQuery as $ } from '../src/jquery-3.5.1-min.js';
import V from './Paths.js';

const SVG = {};

$.each([{
	name: 'arrow',
	type: 'solid',
	icon: 'angle-right'
}, {
	name: 'fast',
	type: 'solid',
	icon: 'angle-double-right'
}, {
	name: 'all',
	type: 'solid',
	icon: 'ellipsis-h'
}, {
	name: 'sort',
	type: 'solid',
	icon: 'sort-up'
}], (i, v) => {

	$.get(`${V.fonts}/font-awesome/${v.type}/${v.icon}.svg`, data => SVG[v.name] = $('<div>').append($(data).find('svg').clone()).html());
});

export default SVG;
