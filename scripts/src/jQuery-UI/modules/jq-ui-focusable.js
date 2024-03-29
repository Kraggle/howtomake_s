import { jQuery as $ } from '../../jquery-3.5.1.js';
import '../jq-ui-core.js';
import './jq-ui-mouse.js'

/*!
 * jQuery UI Focusable 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: :focusable Selector
//>>group: Core
//>>description: Selects elements which can be focused.
//>>docs: http://api.jqueryui.com/focusable-selector/



// Selectors
$.ui.focusable = function(element, hasTabindex) {
    var map, mapName, img, focusableIfVisible, fieldset,
        nodeName = element.nodeName.toLowerCase();

    if ("area" === nodeName) {
        map = element.parentNode;
        mapName = map.name;
        if (!element.href || !mapName || map.nodeName.toLowerCase() !== "map") {
            return false;
        }
        img = $("img[usemap='#" + mapName + "']");
        return img.length > 0 && img.is(":visible");
    }

    if (/^(input|select|textarea|button|object)$/.test(nodeName)) {
        focusableIfVisible = !element.disabled;

        if (focusableIfVisible) {

            // Form controls within a disabled fieldset are disabled.
            // However, controls within the fieldset's legend do not get disabled.
            // Since controls generally aren't placed inside legends, we skip
            // this portion of the check.
            fieldset = $(element).closest("fieldset")[0];
            if (fieldset) {
                focusableIfVisible = !fieldset.disabled;
            }
        }
    } else if ("a" === nodeName) {
        focusableIfVisible = element.href || hasTabindex;
    } else {
        focusableIfVisible = hasTabindex;
    }

    return focusableIfVisible && $(element).is(":visible") && visible($(element));
};

// Support: IE 8 only
// IE 8 doesn't resolve inherit to visible/hidden for computed values
function visible(element) {
    var visibility = element.css("visibility");
    while (visibility === "inherit") {
        element = element.parent();
        visibility = element.css("visibility");
    }
    return visibility !== "hidden";
}

$.extend($.expr[":"], {
    focusable: function(element) {
        return $.ui.focusable(element, $.attr(element, "tabindex") != null);
    }
});


// Support: IE8 Only
// IE8 does not support the form attribute and when it is supplied. It overwrites the form prop
// with a string, so we need to find the proper form.
$.fn.form = function() {
    return typeof this[0].form === "string" ? this.closest("form") : $(this[0].form);
};