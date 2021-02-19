import { jQuery as $ } from '../../jquery-3.5.1.js';
import '../jq-ui-core.js';
import './jq-ui-mouse.js'
import './jq-ui-sortable.js'
import './jq-ui-focusable.js'

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