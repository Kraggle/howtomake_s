import { jQuery as $ } from '../../jquery-3.5.1-min.js';
import '../jq-ui-core-min.js';
import './jq-ui-mouse-min.js'
import './jq-ui-sortable-min.js'
import './jq-ui-focusable-min.js'

/*!
 * jQuery UI Tabbable 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: :tabbable Selector
//>>group: Core
//>>description: Selects elements which can be tabbed to.
//>>docs: http://api.jqueryui.com/tabbable-selector/



$.extend($.expr[":"], {
    tabbable: function(element) {
        var tabIndex = $.attr(element, "tabindex"),
            hasTabindex = tabIndex != null;
        return (!hasTabindex || tabIndex >= 0) && $.ui.focusable(element, hasTabindex);
    }
});