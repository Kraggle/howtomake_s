import{jQuery as o}from"../jquery-3.5.1-min.js";
/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
o.support.touch="ontouchend"in document;var t,e=o.ui.mouse.prototype,u=e._mouseInit,n=e._mouseDestroy;function c(o,t){if(!(o.originalEvent.touches.length>1)){o.preventDefault();var e=o.originalEvent.changedTouches[0],u=document.createEvent("MouseEvents");u.initMouseEvent(t,!0,!0,window,1,e.screenX,e.screenY,e.clientX,e.clientY,!1,!1,!1,!1,0,null),o.target.dispatchEvent(u)}}e._touchStart=function(o){!t&&this._mouseCapture(o.originalEvent.changedTouches[0])&&(t=!0,this._touchMoved=!1,c(o,"mouseover"),c(o,"mousemove"),c(o,"mousedown"))},e._touchMove=function(o){t&&(this._touchMoved=!0,c(o,"mousemove"))},e._touchEnd=function(o){t&&(c(o,"mouseup"),c(o,"mouseout"),this._touchMoved||c(o,"click"),t=!1)},e._mouseInit=function(){var t=this;t.element.bind({touchstart:o.proxy(t,"_touchStart"),touchmove:o.proxy(t,"_touchMove"),touchend:o.proxy(t,"_touchEnd")}),u.call(t)},e._mouseDestroy=function(){var t=this;t.element.unbind({touchstart:o.proxy(t,"_touchStart"),touchmove:o.proxy(t,"_touchMove"),touchend:o.proxy(t,"_touchEnd")}),n.call(t)};