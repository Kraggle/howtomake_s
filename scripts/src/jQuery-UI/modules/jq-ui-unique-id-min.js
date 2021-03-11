import{jQuery as i}from"../../jquery-3.5.1-min.js";var t;
/*!
 * jQuery UI Unique ID 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
i.fn.extend({uniqueId:(t=0,function(){return this.each((function(){this.id||(this.id="ui-id-"+ ++t)}))}),removeUniqueId:function(){return this.each((function(){/^ui-id-\d+$/.test(this.id)&&i(this).removeAttr("id")}))}});