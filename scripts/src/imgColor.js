import { jQuery as $ } from './jquery-3.5.1-min.js';

const imgColor = {
	pluginName: 'imgColor',

	canvas: document.createElement('canvas'),

	SIDE_TOP: 't',
	SIDE_RIGHT: 'r',
	SIDE_BOTTOM: 'b',
	SIDE_LEFT: 'l',

	dfdCache: {},
	// get or cache Deferred objects
	getDfd(key) {
		let dfd = this.dfdCache[key];
		if (!dfd) {
			dfd = $.Deferred();
			this.dfdCache[key] = dfd;
		}
		return dfd;
	},

	decToHex(num) {
		const hex = num.toString(16);
		return hex.length === 1 ? '0' + hex : hex;
	},

	rgbToHex: (r, g, b) => ['#', decToHex(r), decToHex(g), decToHex(b)].join(''),

	count(idx, imageData, colorsInfo) {

		// if true, means alpha value is less than 127, ignore this point
		if (imageData[idx + 3] < 127) {
			return;
		}

		const name = rgbToHex(imageData[idx], imageData[idx + 1], imageData[idx + 2]);
		if (colorsInfo[name]) {
			colorsInfo[name]++;
		} else {
			colorsInfo[name] = 1;
		}
	},

	traverse(side, colorsInfo, width, height, imageData) {
		let x, y;
		if (side === this.SIDE_TOP || side === this.SIDE_BOTTOM) {
			y = (side === this.SIDE_TOP) ? 0 : (height - 1);
			for (x = 0; x < width; x++) {
				this.count((y * width + x) * 4, imageData, colorsInfo);
			}
		} else { // side is right or left
			height = height - 1;
			x = (side === this.SIDE_RIGHT) ? (width - 1) : 0;
			for (y = 1; y < height; y++) {
				this.count((y * width + x) * 4, imageData, colorsInfo);
			}
		}
	},

	computeByImage(img, ignore) {
		let data, k, v,
			color = '#ffffff',
			colorAmount = 0;
		const colorsInfo = {},
			width = img.width,
			height = img.height,
			ctx = this.canvas.getContext('2d');

		this.canvas.width = width;
		this.canvas.height = height;
		ctx.drawImage(img, 0, 0);
		data = ctx.getImageData(0, 0, width, height).data;

		if (ignore.indexOf(this.SIDE_TOP) < 0)  // don't ignore top border
			this.traverse(this.SIDE_TOP, colorsInfo, width, height, data);

		if (ignore.indexOf(this.SIDE_RIGHT) < 0)  // don't ignore right border
			this.traverse(this.SIDE_RIGHT, colorsInfo, width, height, data);

		if (ignore.indexOf(this.SIDE_BOTTOM) < 0)  // don't ignore bottom border
			this.traverse(this.SIDE_BOTTOM, colorsInfo, width, height, data);

		if (ignore.indexOf(this.SIDE_LEFT) < 0)  // don't ignore left border
			this.traverse(this.SIDE_LEFT, colorsInfo, width, height, data);

		for (k in colorsInfo) {
			v = colorsInfo[k];
			if (v > colorAmount) {
				color = k;
				colorAmount = v;
			}
		}

		return color;
	},

	compute(url, ignore) {
		let img = new Image();
		const data = { url: url, ignore: ignore };

		img.onload = () => {
			try {
				data.color = computeByImage(this, ignore);
				this.getDfd(url).resolve(data);
			} catch (e) { // Error - the canvas has been tainted by cross-origin data.
				this.getDfd(url).reject(data);
			}
			img = null;
		};
		img.onerror = () => { // Error - Cross-origin image load denied
			img.onerror = null;
			img = null;
			this.getDfd(url).reject(data);
		};

		img.crossOrigin = ''; // '' is same as 'anonymous'
		img.src = url;
	},

	imageColor: {
		color(options) {
			const root = imgColor,
				dfd = root.getDfd(options.url);

			if (typeof options.success === 'function') {
				dfd.done(options.success);
			}

			if (typeof options.error === 'function') {
				dfd.fail(options.error);
			}

			if ('pending' === dfd.state()) {
				root.compute(options.url, typeof options.ignore === 'string' ? options.ignore : '');
			}
		}
	}
}

function Plugin(element, selector, options) {
	const el = $(element),
		ignore = el.data('imgColorIgnore');

	if (typeof selector === 'object') {
		options = selector;
		selector = undefined;
	}

	options = $.extend({
		url: element.src
	}, options);

	// if data-imgcolr-ignore is specified on the img node, then rewrite the options
	if (typeof ignore === 'string') {
		options.ignore = ignore;
	}

	options.success = function(data) {
		var matches = typeof selector === 'function' ? selector.call(element, element, data.color) :
			typeof selector === 'string' ? $(selector) : el.parent();
		// for `selector.call(element, element, data.color)` may not return a jQuery object
		if (matches && matches.jquery) {
			matches.css('backgroundColor', data.color);
		}
	};

	imgColor.imageColor.color(options);
}

// @param selector {Selector | Function}[optional]
// @param {string}   options.ignore - Which border should be ignored,
//    there are 4 kinds of values: 't', 'r', 'b', 'l', you can ignore multiple borders like this: 'tb', it's optional
$.fn[imgColor.pluginName] = function(selector, options) {
	return this.each(function() {
		new Plugin(this, selector, options);
	});
};
