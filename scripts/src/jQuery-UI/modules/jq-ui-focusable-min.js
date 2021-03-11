import{jQuery as e}from"../../jquery-3.5.1-min.js";import"../jq-ui-core-min.js";import"./jq-ui-mouse-min.js";
/*!
 * jQuery UI Focusable 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
e.ui.focusable=function(i,t){var r,s,n,o,a,u=i.nodeName.toLowerCase();return"area"===u?(s=(r=i.parentNode).name,!(!i.href||!s||"map"!==r.nodeName.toLowerCase())&&((n=e("img[usemap='#"+s+"']")).length>0&&n.is(":visible"))):(/^(input|select|textarea|button|object)$/.test(u)?(o=!i.disabled)&&(a=e(i).closest("fieldset")[0])&&(o=!a.disabled):o="a"===u&&i.href||t,o&&e(i).is(":visible")&&function(e){var i=e.css("visibility");for(;"inherit"===i;)i=(e=e.parent()).css("visibility");return"hidden"!==i}(e(i)))},e.extend(e.expr[":"],{focusable:function(i){return e.ui.focusable(i,null!=e.attr(i,"tabindex"))}}),e.fn.form=function(){return"string"==typeof this[0].form?this.closest("form"):e(this[0].form)};