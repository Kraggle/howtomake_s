import { jQuery as $ } from '../../jquery-3.5.1.js';
import '../jq-ui-core.js';

/*!
 * jQuery UI Mouse 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

//>>label: Mouse
//>>group: Widgets
//>>description: Abstracts mouse-based interactions to assist in creating certain widgets.
//>>docs: http://api.jqueryui.com/mouse/



var mouseHandled = false;
$(document).on("mouseup", function() {
    mouseHandled = false;
});

$.widget("ui.mouse", {
    version: "1.12.1",
    options: {
        cancel: "input, textarea, button, select, option",
        distance: 1,
        delay: 0
    },
    _mouseInit: function() {
        var that = this;

        this.element
            .on("mousedown." + this.widgetName, function(event) {
                return that._mouseDown(event);
            })
            .on("click." + this.widgetName, function(event) {
                if (true === $.data(event.target, that.widgetName + ".preventClickEvent")) {
                    $.removeData(event.target, that.widgetName + ".preventClickEvent");
                    event.stopImmediatePropagation();
                    return false;
                }
            });

        this.started = false;
    },

    // TODO: make sure destroying one instance of mouse doesn't mess with
    // other instances of mouse
    _mouseDestroy: function() {
        this.element.off("." + this.widgetName);
        if (this._mouseMoveDelegate) {
            this.document
                .off("mousemove." + this.widgetName, this._mouseMoveDelegate)
                .off("mouseup." + this.widgetName, this._mouseUpDelegate);
        }
    },

    _mouseDown: function(event) {

        // don't let more than one widget handle mouseStart
        if (mouseHandled) {
            return;
        }

        this._mouseMoved = false;

        // We may have missed mouseup (out of window)
        (this._mouseStarted && this._mouseUp(event));

        this._mouseDownEvent = event;

        var that = this,
            btnIsLeft = (event.which === 1),

            // event.target.nodeName works around a bug in IE 8 with
            // disabled inputs (#7620)
            elIsCancel = (typeof this.options.cancel === "string" && event.target.nodeName ?
                $(event.target).closest(this.options.cancel).length : false);
        if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
            return true;
        }

        this.mouseDelayMet = !this.options.delay;
        if (!this.mouseDelayMet) {
            this._mouseDelayTimer = setTimeout(function() {
                that.mouseDelayMet = true;
            }, this.options.delay);
        }

        if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
            this._mouseStarted = (this._mouseStart(event) !== false);
            if (!this._mouseStarted) {
                event.preventDefault();
                return true;
            }
        }

        // Click event may never have fired (Gecko & Opera)
        if (true === $.data(event.target, this.widgetName + ".preventClickEvent")) {
            $.removeData(event.target, this.widgetName + ".preventClickEvent");
        }

        // These delegates are required to keep context
        this._mouseMoveDelegate = function(event) {
            return that._mouseMove(event);
        };
        this._mouseUpDelegate = function(event) {
            return that._mouseUp(event);
        };

        this.document
            .on("mousemove." + this.widgetName, this._mouseMoveDelegate)
            .on("mouseup." + this.widgetName, this._mouseUpDelegate);

        event.preventDefault();

        mouseHandled = true;
        return true;
    },

    _mouseMove: function(event) {

        // Only check for mouseups outside the document if you've moved inside the document
        // at least once. This prevents the firing of mouseup in the case of IE<9, which will
        // fire a mousemove event if content is placed under the cursor. See #7778
        // Support: IE <9
        if (this._mouseMoved) {

            // IE mouseup check - mouseup happened when mouse was out of window
            if ($.ui.ie && (!document.documentMode || document.documentMode < 9) &&
                !event.button) {
                return this._mouseUp(event);

                // Iframe mouseup check - mouseup occurred in another document
            } else if (!event.which) {

                // Support: Safari <=8 - 9
                // Safari sets which to 0 if you press any of the following keys
                // during a drag (#14461)
                if (event.originalEvent.altKey || event.originalEvent.ctrlKey ||
                    event.originalEvent.metaKey || event.originalEvent.shiftKey) {
                    this.ignoreMissingWhich = true;
                } else if (!this.ignoreMissingWhich) {
                    return this._mouseUp(event);
                }
            }
        }

        if (event.which || event.button) {
            this._mouseMoved = true;
        }

        if (this._mouseStarted) {
            this._mouseDrag(event);
            return event.preventDefault();
        }

        if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
            this._mouseStarted =
                (this._mouseStart(this._mouseDownEvent, event) !== false);
            (this._mouseStarted ? this._mouseDrag(event) : this._mouseUp(event));
        }

        return !this._mouseStarted;
    },

    _mouseUp: function(event) {
        this.document
            .off("mousemove." + this.widgetName, this._mouseMoveDelegate)
            .off("mouseup." + this.widgetName, this._mouseUpDelegate);

        if (this._mouseStarted) {
            this._mouseStarted = false;

            if (event.target === this._mouseDownEvent.target) {
                $.data(event.target, this.widgetName + ".preventClickEvent", true);
            }

            this._mouseStop(event);
        }

        if (this._mouseDelayTimer) {
            clearTimeout(this._mouseDelayTimer);
            delete this._mouseDelayTimer;
        }

        this.ignoreMissingWhich = false;
        mouseHandled = false;
        event.preventDefault();
    },

    _mouseDistanceMet: function(event) {
        return (Math.max(
            Math.abs(this._mouseDownEvent.pageX - event.pageX),
            Math.abs(this._mouseDownEvent.pageY - event.pageY)
        ) >= this.options.distance);
    },

    _mouseDelayMet: function( /* event */) {
        return this.mouseDelayMet;
    },

    // These are placeholder methods, to be overriden by extending plugin
    _mouseStart: function( /* event */) { },
    _mouseDrag: function( /* event */) { },
    _mouseStop: function( /* event */) { },
    _mouseCapture: function( /* event */) { return true; }
});

// $.ui.plugin is deprecated. Use $.widget() extensions instead.
$.ui.plugin = {
    add: function(module, option, set) {
        var i,
            proto = $.ui[module].prototype;
        for (i in set) {
            proto.plugins[i] = proto.plugins[i] || [];
            proto.plugins[i].push([option, set[i]]);
        }
    },
    call: function(instance, name, args, allowDisconnected) {
        var i,
            set = instance.plugins[name];

        if (!set) {
            return;
        }

        if (!allowDisconnected && (!instance.element[0].parentNode ||
            instance.element[0].parentNode.nodeType === 11)) {
            return;
        }

        for (i = 0; i < set.length; i++) {
            if (instance.options[set[i][0]]) {
                set[i][1].apply(instance.element, args);
            }
        }
    }
};

$.ui.safeActiveElement = function(document) {
    var activeElement;

    // Support: IE 9 only
    // IE9 throws an "Unspecified error" accessing document.activeElement from an <iframe>
    try {
        activeElement = document.activeElement;
    } catch (error) {
        activeElement = document.body;
    }

    // Support: IE 9 - 11 only
    // IE may return null instead of an element
    // Interestingly, this only seems to occur when NOT in an iframe
    if (!activeElement) {
        activeElement = document.body;
    }

    // Support: IE 11 only
    // IE11 returns a seemingly empty object in some cases when accessing
    // document.activeElement from an <iframe>
    if (!activeElement.nodeName) {
        activeElement = document.body;
    }

    return activeElement;
};

$.ui.safeBlur = function(element) {

    // Support: IE9 - 10 only
    // If the <body> is blurred, IE will switch windows, see #9420
    if (element && element.nodeName.toLowerCase() !== "body") {
        $(element).trigger("blur");
    }
};