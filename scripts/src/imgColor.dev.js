"use strict";

var _jquery = require("./jquery3.4.1.js");

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var imgColor = {
  pluginName: 'imgColor',
  canvas: document.createElement('canvas'),
  SIDE_TOP: 't',
  SIDE_RIGHT: 'r',
  SIDE_BOTTOM: 'b',
  SIDE_LEFT: 'l',
  dfdCache: {},
  // get or cache Deferred objects
  getDfd: function getDfd(key) {
    var dfd = this.dfdCache[key];

    if (!dfd) {
      dfd = _jquery.jQuery.Deferred();
      this.dfdCache[key] = dfd;
    }

    return dfd;
  },
  decToHex: function decToHex(num) {
    var hex = num.toString(16);
    return hex.length === 1 ? '0' + hex : hex;
  },
  rgbToHex: function rgbToHex(r, g, b) {
    return ['#', decToHex(r), decToHex(g), decToHex(b)].join('');
  },
  count: function count(idx, imageData, colorsInfo) {
    // if true, means alpha value is less than 127, ignore this point
    if (imageData[idx + 3] < 127) {
      return;
    }

    var name = rgbToHex(imageData[idx], imageData[idx + 1], imageData[idx + 2]);

    if (colorsInfo[name]) {
      colorsInfo[name]++;
    } else {
      colorsInfo[name] = 1;
    }
  },
  traverse: function traverse(side, colorsInfo, width, height, imageData) {
    var x, y;

    if (side === this.SIDE_TOP || side === this.SIDE_BOTTOM) {
      y = side === this.SIDE_TOP ? 0 : height - 1;

      for (x = 0; x < width; x++) {
        this.count((y * width + x) * 4, imageData, colorsInfo);
      }
    } else {
      // side is right or left
      height = height - 1;
      x = side === this.SIDE_RIGHT ? width - 1 : 0;

      for (y = 1; y < height; y++) {
        this.count((y * width + x) * 4, imageData, colorsInfo);
      }
    }
  },
  computeByImage: function computeByImage(img, ignore) {
    var data,
        k,
        v,
        color = '#ffffff',
        colorAmount = 0;
    var colorsInfo = {},
        width = img.width,
        height = img.height,
        ctx = this.canvas.getContext('2d');
    this.canvas.width = width;
    this.canvas.height = height;
    ctx.drawImage(img, 0, 0);
    data = ctx.getImageData(0, 0, width, height).data;
    if (ignore.indexOf(this.SIDE_TOP) < 0) // don't ignore top border
      this.traverse(this.SIDE_TOP, colorsInfo, width, height, data);
    if (ignore.indexOf(this.SIDE_RIGHT) < 0) // don't ignore right border
      this.traverse(this.SIDE_RIGHT, colorsInfo, width, height, data);
    if (ignore.indexOf(this.SIDE_BOTTOM) < 0) // don't ignore bottom border
      this.traverse(this.SIDE_BOTTOM, colorsInfo, width, height, data);
    if (ignore.indexOf(this.SIDE_LEFT) < 0) // don't ignore left border
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
  compute: function compute(url, ignore) {
    var _this = this;

    var img = new Image();
    var data = {
      url: url,
      ignore: ignore
    };

    img.onload = function () {
      try {
        data.color = computeByImage(_this, ignore);

        _this.getDfd(url).resolve(data);
      } catch (e) {
        // Error - the canvas has been tainted by cross-origin data.
        _this.getDfd(url).reject(data);
      }

      img = null;
    };

    img.onerror = function () {
      // Error - Cross-origin image load denied
      img.onerror = null;
      img = null;

      _this.getDfd(url).reject(data);
    };

    img.crossOrigin = ''; // '' is same as 'anonymous'

    img.src = url;
  },
  imageColor: {
    color: function color(options) {
      var root = imgColor,
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
};

function Plugin(element, selector, options) {
  var el = (0, _jquery.jQuery)(element),
      ignore = el.data('imgColorIgnore');

  if (_typeof(selector) === 'object') {
    options = selector;
    selector = undefined;
  }

  options = _jquery.jQuery.extend({
    url: element.src
  }, options); // if data-imgcolr-ignore is specified on the img node, then rewrite the options

  if (typeof ignore === 'string') {
    options.ignore = ignore;
  }

  options.success = function (data) {
    var matches = typeof selector === 'function' ? selector.call(element, element, data.color) : typeof selector === 'string' ? (0, _jquery.jQuery)(selector) : el.parent(); // for `selector.call(element, element, data.color)` may not return a jQuery object

    if (matches && matches.jquery) {
      matches.css('backgroundColor', data.color);
    }
  };

  imgColor.imageColor.color(options);
} // @param selector {Selector | Function}[optional]
// @param {string}   options.ignore - Which border should be ignored,
//    there are 4 kinds of values: 't', 'r', 'b', 'l', you can ignore multiple borders like this: 'tb', it's optional


_jquery.jQuery.fn[imgColor.pluginName] = function (selector, options) {
  return this.each(function () {
    new Plugin(this, selector, options);
  });
};