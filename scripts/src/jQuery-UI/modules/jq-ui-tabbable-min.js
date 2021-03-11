import{jQuery as i}from"../../jquery-3.5.1-min.js";import"../jq-ui-core-min.js";import"./jq-ui-mouse-min.js";import"./jq-ui-sortable-min.js";import"./jq-ui-focusable-min.js";
/*!
 * jQuery UI Tabbable 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
i.extend(i.expr[":"],{tabbable:function(r){var e=i.attr(r,"tabindex"),t=null!=e;return(!t||e>=0)&&i.ui.focusable(r,t)}});