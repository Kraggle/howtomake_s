import{jQuery as e}from"../../jquery-3.5.1-min.js";
/*!
 * jQuery UI :data 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
e.extend(e.expr[":"],{data:e.expr.createPseudo?e.expr.createPseudo((function(r){return function(t){return!!e.data(t,r)}})):function(r,t,n){return!!e.data(r,n[3])}});