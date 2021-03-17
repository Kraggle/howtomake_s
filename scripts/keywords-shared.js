import { jQuery as $ } from './src/jquery-3.5.1-min.js';
import { K } from './custom/K-min.js';
import V from './custom/Paths.js';

export function getKeywords(keys) {
	K.extend({
		content: '',
		ignore: [],
		count: 2,
		min: 1,
		max: 6,
		enabled: true,
		alts: false,
		meta: false
	}, keys);

	const _content = $('<div />', { html: keys.content });
	_content.find('script, link, style, iframe, noscript').remove();

	const blocks = _content.text().split(/[ ]{0,}[.;:?!\n][ ]{0,}/);

	keys.doAltTags && _content.find('img').each((i, img) => {
		const alt = $(img).attr('alt');
		alt && blocks.push(alt);
	});

	keys.meta && _content.find('meta[content]').each((i, meta) => {
		const data = $(meta).attr('content');
		data && blocks.push(data);
	});

	const groups = {};
	for (let i = keys.min; i <= keys.max; i++)
		groups[i] = {};

	let wordCount = 0;

	K.each(blocks, (j, v) => {
		if (v.length < 4 || !v.match(/\w+/)) return;

		const words = v.split(/ \W{0,}|\W{0,} /);
		wordCount += words.length;
		K.each(groups, (count, group) => {
			count = parseInt(count);

			if (words.length >= count) {

				K.each(words, (i, word) => {
					let string = '';
					for (let l = i; l < count + i; l++) {
						if (!words[l]) return;
						string += ' ' + words[l];
					}
					string = string.trim().toLowerCase();
					const first = string.replaceAll('’', '\'').match(/^[^ ]+/)[0],
						last = string.replaceAll('’', '\'').match(/[^ ]+$/)[0];

					if (string.length < 3 || string.match(/^(\d+|\d.+\d)$/)) return;
					if (keys.enabled) {
						if (count < 3 && (K.isInArray(first, keys.ignore) || K.isInArray(last, keys.ignore))) return;
						if (count > 2 && K.isInArray(first, keys.ignore) && K.isInArray(last, keys.ignore)) return;
					}

					if (!group[string]) group[string] = 0;
					group[string]++;
				});
			}
		});
	});

	keys.groups = {};
	K.each(groups, (count, group) => {
		keys.groups[count] = [];
		K.each(group, (words, found) => {
			found >= keys.count && keys.groups[count].push({
				keywords: words,
				count: found,
				density: (found / (wordCount / count)) * 100
			});
		});

		keys.groups[count].sort((a, b) => b.count - a.count);
	});

	return keys.groups;
}

export function getSettings(keys) {
	return $.getJSON(V.ajax, {
		action: 'get_keyword_settings',
		nonce: $('#keyword_nonce').val()
	}, function(data) {
		convertSettings(keys, data);
	});
}

export const convertSettings = (keys, data) => {
	keys.ignore = data.ignore_list;
	keys.min = parseInt(data.min_words);
	keys.max = parseInt(data.max_words);
	keys.refresh = parseInt(data.refresh);
	keys.count = parseInt(data.min_count);
	keys.enabled = parseBool(data.ignore_enabled);
	keys.meta = parseBool(data.meta_tags);
	keys.alts = parseBool(data.image_alts);
};

const parseBool = x => x === true || x === 'true';

export const filter = {
	or: words => '[filter]:regex(filter, ^.*(' + words.split(' ').join('|') + ').*$)',
	and: words => '[filter]:regex(filter, ^' + words.split(' ').map(f => `(?=.*${f})`).join('') + '.*$)',
	exact: words => `[filter]:regex(filter, ^.*${words}.*$)`
};
