import{jQuery as e}from"../../jquery-3.5.1-min.js";import"../jq-ui-core-min.js";import"./jq-ui-mouse-min.js";import"./jq-ui-sortable-min.js";
/*!
 * jQuery UI Selectable 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
e.widget("ui.selectable",e.ui.mouse,{version:"1.12.1",options:{appendTo:"body",autoRefresh:!0,distance:0,filter:"*",tolerance:"touch",selected:null,selecting:null,start:null,stop:null,unselected:null,unselecting:null},_create:function(){var t=this;this._addClass("ui-selectable"),this.dragged=!1,this.refresh=function(){t.elementPos=e(t.element[0]).offset(),t.selectees=e(t.options.filter,t.element[0]),t._addClass(t.selectees,"ui-selectee"),t.selectees.each((function(){var s=e(this),l=s.offset(),i={left:l.left-t.elementPos.left,top:l.top-t.elementPos.top};e.data(this,"selectable-item",{element:this,$element:s,left:i.left,top:i.top,right:i.left+s.outerWidth(),bottom:i.top+s.outerHeight(),startselected:!1,selected:s.hasClass("ui-selected"),selecting:s.hasClass("ui-selecting"),unselecting:s.hasClass("ui-unselecting")})}))},this.refresh(),this._mouseInit(),this.helper=e("<div>"),this._addClass(this.helper,"ui-selectable-helper")},_destroy:function(){this.selectees.removeData("selectable-item"),this._mouseDestroy()},_mouseStart:function(t){var s=this,l=this.options;this.opos=[t.pageX,t.pageY],this.elementPos=e(this.element[0]).offset(),this.options.disabled||(this.selectees=e(l.filter,this.element[0]),this._trigger("start",t),e(l.appendTo).append(this.helper),this.helper.css({left:t.pageX,top:t.pageY,width:0,height:0}),l.autoRefresh&&this.refresh(),this.selectees.filter(".ui-selected").each((function(){var l=e.data(this,"selectable-item");l.startselected=!0,t.metaKey||t.ctrlKey||(s._removeClass(l.$element,"ui-selected"),l.selected=!1,s._addClass(l.$element,"ui-unselecting"),l.unselecting=!0,s._trigger("unselecting",t,{unselecting:l.element}))})),e(t.target).parents().addBack().each((function(){var l,i=e.data(this,"selectable-item");if(i)return l=!t.metaKey&&!t.ctrlKey||!i.$element.hasClass("ui-selected"),s._removeClass(i.$element,l?"ui-unselecting":"ui-selected")._addClass(i.$element,l?"ui-selecting":"ui-unselecting"),i.unselecting=!l,i.selecting=l,i.selected=l,l?s._trigger("selecting",t,{selecting:i.element}):s._trigger("unselecting",t,{unselecting:i.element}),!1})))},_mouseDrag:function(t){if(this.dragged=!0,!this.options.disabled){var s,l=this,i=this.options,n=this.opos[0],c=this.opos[1],a=t.pageX,r=t.pageY;return n>a&&(s=a,a=n,n=s),c>r&&(s=r,r=c,c=s),this.helper.css({left:n,top:c,width:a-n,height:r-c}),this.selectees.each((function(){var s=e.data(this,"selectable-item"),o=!1,u={};s&&s.element!==l.element[0]&&(u.left=s.left+l.elementPos.left,u.right=s.right+l.elementPos.left,u.top=s.top+l.elementPos.top,u.bottom=s.bottom+l.elementPos.top,"touch"===i.tolerance?o=!(u.left>a||u.right<n||u.top>r||u.bottom<c):"fit"===i.tolerance&&(o=u.left>n&&u.right<a&&u.top>c&&u.bottom<r),o?(s.selected&&(l._removeClass(s.$element,"ui-selected"),s.selected=!1),s.unselecting&&(l._removeClass(s.$element,"ui-unselecting"),s.unselecting=!1),s.selecting||(l._addClass(s.$element,"ui-selecting"),s.selecting=!0,l._trigger("selecting",t,{selecting:s.element}))):(s.selecting&&((t.metaKey||t.ctrlKey)&&s.startselected?(l._removeClass(s.$element,"ui-selecting"),s.selecting=!1,l._addClass(s.$element,"ui-selected"),s.selected=!0):(l._removeClass(s.$element,"ui-selecting"),s.selecting=!1,s.startselected&&(l._addClass(s.$element,"ui-unselecting"),s.unselecting=!0),l._trigger("unselecting",t,{unselecting:s.element}))),s.selected&&(t.metaKey||t.ctrlKey||s.startselected||(l._removeClass(s.$element,"ui-selected"),s.selected=!1,l._addClass(s.$element,"ui-unselecting"),s.unselecting=!0,l._trigger("unselecting",t,{unselecting:s.element})))))})),!1}},_mouseStop:function(t){var s=this;return this.dragged=!1,e(".ui-unselecting",this.element[0]).each((function(){var l=e.data(this,"selectable-item");s._removeClass(l.$element,"ui-unselecting"),l.unselecting=!1,l.startselected=!1,s._trigger("unselected",t,{unselected:l.element})})),e(".ui-selecting",this.element[0]).each((function(){var l=e.data(this,"selectable-item");s._removeClass(l.$element,"ui-selecting")._addClass(l.$element,"ui-selected"),l.selecting=!1,l.selected=!0,l.startselected=!0,s._trigger("selected",t,{selected:l.element})})),this._trigger("stop",t),this.helper.remove(),!1}});