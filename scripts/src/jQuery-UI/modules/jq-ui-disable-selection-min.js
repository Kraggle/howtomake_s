import{jQuery as e}from"../../jquery-3.5.1-min.js";import"../jq-ui-core-min.js";import"./jq-ui-mouse-min.js";import"./jq-ui-sortable-min.js";var t;
/*!
 * jQuery UI Disable Selection 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
e.fn.extend({disableSelection:(t="onselectstart"in document.createElement("div")?"selectstart":"mousedown",function(){return this.on(t+".ui-disableSelection",(function(e){e.preventDefault()}))}),enableSelection:function(){return this.off(".ui-disableSelection")}});