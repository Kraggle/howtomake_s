import { jQuery as $ } from '../../jquery-3.5.1-min.js';

/*!
 * jQuery UI Unique ID 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: uniqueId
//>>group: Core
//>>description: Functions to generate and remove uniqueId's
//>>docs: http://api.jqueryui.com/uniqueId/



$.fn.extend({
    uniqueId: (function() {
        var uuid = 0;

        return function() {
            return this.each(function() {
                if (!this.id) {
                    this.id = "ui-id-" + (++uuid);
                }
            });
        };
    })(),

    removeUniqueId: function() {
        return this.each(function() {
            if (/^ui-id-\d+$/.test(this.id)) {
                $(this).removeAttr("id");
            }
        });
    }
});