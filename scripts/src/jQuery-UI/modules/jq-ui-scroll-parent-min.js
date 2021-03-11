import{jQuery as s}from"../../jquery-3.5.1-min.js";
/*!
 * jQuery UI Scroll Parent 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
s.fn.scrollParent=function(t){var o=this.css("position"),r="absolute"===o,e=t?/(auto|scroll|hidden)/:/(auto|scroll)/,n=this.parents().filter((function(){var t=s(this);return(!r||"static"!==t.css("position"))&&e.test(t.css("overflow")+t.css("overflow-y")+t.css("overflow-x"))})).eq(0);return"fixed"!==o&&n.length?n:s(this[0].ownerDocument||document)};