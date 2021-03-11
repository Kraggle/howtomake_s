import{jQuery as t}from"../../jquery-3.5.1-min.js";
/*!
 * jQuery UI Effects 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
var e=t;t.effects={effect:{}};
/*!
 * jQuery Color Animations v2.1.2
 * https://github.com/jquery/jquery-color
 *
 * Copyright 2014 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * Date: Wed Jan 16 08:47:09 2013 -0600
 */
var i,n=/^([-+])=\s*(\d+\.?\d*)/,o=[{re:/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[t[1],t[2],t[3],t[4]]}},{re:/rgba?\(\s*(\d+(?:\.\d+)?)%\s*,\s*(\d+(?:\.\d+)?)%\s*,\s*(\d+(?:\.\d+)?)%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[2.55*t[1],2.55*t[2],2.55*t[3],t[4]]}},{re:/#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,parse:function(t){return[parseInt(t[1],16),parseInt(t[2],16),parseInt(t[3],16)]}},{re:/#([a-f0-9])([a-f0-9])([a-f0-9])/,parse:function(t){return[parseInt(t[1]+t[1],16),parseInt(t[2]+t[2],16),parseInt(t[3]+t[3],16)]}},{re:/hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)%\s*,\s*(\d+(?:\.\d+)?)%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,space:"hsla",parse:function(t){return[t[1],t[2]/100,t[3]/100,t[4]]}}],r=e.Color=function(t,i,n,o){return new e.Color.fn.parse(t,i,n,o)},s={rgba:{props:{red:{idx:0,type:"byte"},green:{idx:1,type:"byte"},blue:{idx:2,type:"byte"}}},hsla:{props:{hue:{idx:0,type:"degrees"},saturation:{idx:1,type:"percent"},lightness:{idx:2,type:"percent"}}}},a={byte:{floor:!0,max:255},percent:{max:1},degrees:{mod:360,floor:!0}},f=r.support={},c=e("<p>")[0],l=e.each;function u(t,e,i){var n=a[e.type]||{};return null==t?i||!e.def?null:e.def:(t=n.floor?~~t:parseFloat(t),isNaN(t)?e.def:n.mod?(t+n.mod)%n.mod:0>t?0:n.max<t?n.max:t)}function d(t){var n=r(),a=n._rgba=[];return t=t.toLowerCase(),l(o,(function(e,i){var o,r=i.re.exec(t),f=r&&i.parse(r),c=i.space||"rgba";if(f)return o=n[c](f),n[s[c].cache]=o[s[c].cache],a=n._rgba=o._rgba,!1})),a.length?("0,0,0,0"===a.join()&&e.extend(a,i.transparent),n):i[t]}function h(t,e,i){return 6*(i=(i+1)%1)<1?t+(e-t)*i*6:2*i<1?e:3*i<2?t+(e-t)*(2/3-i)*6:t}c.style.cssText="background-color:rgba(1,1,1,.5)",f.rgba=c.style.backgroundColor.indexOf("rgba")>-1,l(s,(function(t,e){e.cache="_"+t,e.props.alpha={idx:3,type:"percent",def:1}})),r.fn=e.extend(r.prototype,{parse:function(t,n,o,a){if(void 0===t)return this._rgba=[null,null,null,null],this;(t.jquery||t.nodeType)&&(t=e(t).css(n),n=void 0);var f=this,c=e.type(t),h=this._rgba=[];return void 0!==n&&(t=[t,n,o,a],c="array"),"string"===c?this.parse(d(t)||i._default):"array"===c?(l(s.rgba.props,(function(e,i){h[i.idx]=u(t[i.idx],i)})),this):"object"===c?(l(s,t instanceof r?function(e,i){t[i.cache]&&(f[i.cache]=t[i.cache].slice())}:function(i,n){var o=n.cache;l(n.props,(function(e,i){if(!f[o]&&n.to){if("alpha"===e||null==t[e])return;f[o]=n.to(f._rgba)}f[o][i.idx]=u(t[e],i,!0)})),f[o]&&e.inArray(null,f[o].slice(0,3))<0&&(f[o][3]=1,n.from&&(f._rgba=n.from(f[o])))}),this):void 0},is:function(t){var e=r(t),i=!0,n=this;return l(s,(function(t,o){var r,s=e[o.cache];return s&&(r=n[o.cache]||o.to&&o.to(n._rgba)||[],l(o.props,(function(t,e){if(null!=s[e.idx])return i=s[e.idx]===r[e.idx]}))),i})),i},_space:function(){var t=[],e=this;return l(s,(function(i,n){e[n.cache]&&t.push(i)})),t.pop()},transition:function(t,e){var i=r(t),n=i._space(),o=s[n],f=0===this.alpha()?r("transparent"):this,c=f[o.cache]||o.to(f._rgba),d=c.slice();return i=i[o.cache],l(o.props,(function(t,n){var o=n.idx,r=c[o],s=i[o],f=a[n.type]||{};null!==s&&(null===r?d[o]=s:(f.mod&&(s-r>f.mod/2?r+=f.mod:r-s>f.mod/2&&(r-=f.mod)),d[o]=u((s-r)*e+r,n)))})),this[n](d)},blend:function(t){if(1===this._rgba[3])return this;var i=this._rgba.slice(),n=i.pop(),o=r(t)._rgba;return r(e.map(i,(function(t,e){return(1-n)*o[e]+n*t})))},toRgbaString:function(){var t="rgba(",i=e.map(this._rgba,(function(t,e){return null==t?e>2?1:0:t}));return 1===i[3]&&(i.pop(),t="rgb("),t+i.join()+")"},toHslaString:function(){var t="hsla(",i=e.map(this.hsla(),(function(t,e){return null==t&&(t=e>2?1:0),e&&e<3&&(t=Math.round(100*t)+"%"),t}));return 1===i[3]&&(i.pop(),t="hsl("),t+i.join()+")"},toHexString:function(t){var i=this._rgba.slice(),n=i.pop();return t&&i.push(~~(255*n)),"#"+e.map(i,(function(t){return 1===(t=(t||0).toString(16)).length?"0"+t:t})).join("")},toString:function(){return 0===this._rgba[3]?"transparent":this.toRgbaString()}}),r.fn.parse.prototype=r.fn,s.hsla.to=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e,i,n=t[0]/255,o=t[1]/255,r=t[2]/255,s=t[3],a=Math.max(n,o,r),f=Math.min(n,o,r),c=a-f,l=a+f,u=.5*l;return e=f===a?0:n===a?60*(o-r)/c+360:o===a?60*(r-n)/c+120:60*(n-o)/c+240,i=0===c?0:u<=.5?c/l:c/(2-l),[Math.round(e)%360,i,u,null==s?1:s]},s.hsla.from=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e=t[0]/360,i=t[1],n=t[2],o=t[3],r=n<=.5?n*(1+i):n+i-n*i,s=2*n-r;return[Math.round(255*h(s,r,e+1/3)),Math.round(255*h(s,r,e)),Math.round(255*h(s,r,e-1/3)),o]},l(s,(function(t,i){var o=i.props,s=i.cache,a=i.to,f=i.from;r.fn[t]=function(t){if(a&&!this[s]&&(this[s]=a(this._rgba)),void 0===t)return this[s].slice();var i,n=e.type(t),c="array"===n||"object"===n?t:arguments,d=this[s].slice();return l(o,(function(t,e){var i=c["object"===n?t:e.idx];null==i&&(i=d[e.idx]),d[e.idx]=u(i,e)})),f?((i=r(f(d)))[s]=d,i):r(d)},l(o,(function(i,o){r.fn[i]||(r.fn[i]=function(r){var s,a=e.type(r),f="alpha"===i?this._hsla?"hsla":"rgba":t,c=this[f](),l=c[o.idx];return"undefined"===a?l:("function"===a&&(r=r.call(this,l),a=e.type(r)),null==r&&o.empty?this:("string"===a&&(s=n.exec(r))&&(r=l+parseFloat(s[2])*("+"===s[1]?1:-1)),c[o.idx]=r,this[f](c)))})}))})),r.hook=function(t){var i=t.split(" ");l(i,(function(t,i){e.cssHooks[i]={set:function(t,n){var o,s,a="";if("transparent"!==n&&("string"!==e.type(n)||(o=d(n)))){if(n=r(o||n),!f.rgba&&1!==n._rgba[3]){for(s="backgroundColor"===i?t.parentNode:t;(""===a||"transparent"===a)&&s&&s.style;)try{a=e.css(s,"backgroundColor"),s=s.parentNode}catch(t){}n=n.blend(a&&"transparent"!==a?a:"_default")}n=n.toRgbaString()}try{t.style[i]=n}catch(t){}}},e.fx.step[i]=function(t){t.colorInit||(t.start=r(t.elem,i),t.end=r(t.end),t.colorInit=!0),e.cssHooks[i].set(t.elem,t.start.transition(t.end,t.pos))}}))},r.hook("backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor"),e.cssHooks.borderColor={expand:function(t){var e={};return l(["Top","Right","Bottom","Left"],(function(i,n){e["border"+n+"Color"]=t})),e}},i=e.Color.names={aqua:"#00ffff",black:"#000000",blue:"#0000ff",fuchsia:"#ff00ff",gray:"#808080",green:"#008000",lime:"#00ff00",maroon:"#800000",navy:"#000080",olive:"#808000",purple:"#800080",red:"#ff0000",silver:"#c0c0c0",teal:"#008080",white:"#ffffff",yellow:"#ffff00",transparent:[null,null,null,0],_default:"#ffffff"};var p,g=["add","remove","toggle"],m={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};function b(e){var i,n,o=e.ownerDocument.defaultView?e.ownerDocument.defaultView.getComputedStyle(e,null):e.currentStyle,r={};if(o&&o.length&&o[0]&&o[o[0]])for(n=o.length;n--;)"string"==typeof o[i=o[n]]&&(r[t.camelCase(i)]=o[i]);else for(i in o)"string"==typeof o[i]&&(r[i]=o[i]);return r}function y(e,i,n,o){return t.isPlainObject(e)&&(i=e,e=e.effect),e={effect:e},null==i&&(i={}),t.isFunction(i)&&(o=i,n=null,i={}),("number"==typeof i||t.fx.speeds[i])&&(o=n,n=i,i={}),t.isFunction(n)&&(o=n,n=null),i&&t.extend(e,i),n=n||i.duration,e.duration=t.fx.off?0:"number"==typeof n?n:n in t.fx.speeds?t.fx.speeds[n]:t.fx.speeds._default,e.complete=o||i.complete,e}function v(e){return!(e&&"number"!=typeof e&&!t.fx.speeds[e])||("string"==typeof e&&!t.effects.effect[e]||(!!t.isFunction(e)||"object"==typeof e&&!e.effect))}function x(t,e){var i=e.outerWidth(),n=e.outerHeight(),o=/^rect\((-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto)\)$/.exec(t)||["",0,i,n,0];return{top:parseFloat(o[1])||0,right:"auto"===o[2]?i:parseFloat(o[2]),bottom:"auto"===o[3]?n:parseFloat(o[3]),left:parseFloat(o[4])||0}}t.each(["borderLeftStyle","borderRightStyle","borderBottomStyle","borderTopStyle"],(function(i,n){t.fx.step[n]=function(t){("none"!==t.end&&!t.setAttr||1===t.pos&&!t.setAttr)&&(e.style(t.elem,n,t.end),t.setAttr=!0)}})),t.fn.addBack||(t.fn.addBack=function(t){return this.add(null==t?this.prevObject:this.prevObject.filter(t))}),t.effects.animateClass=function(e,i,n,o){var r=t.speed(i,n,o);return this.queue((function(){var i,n=t(this),o=n.attr("class")||"",s=r.children?n.find("*").addBack():n;s=s.map((function(){return{el:t(this),start:b(this)}})),(i=function(){t.each(g,(function(t,i){e[i]&&n[i+"Class"](e[i])}))})(),s=s.map((function(){return this.end=b(this.el[0]),this.diff=function(e,i){var n,o,r={};for(n in i)o=i[n],e[n]!==o&&(m[n]||!t.fx.step[n]&&isNaN(parseFloat(o))||(r[n]=o));return r}(this.start,this.end),this})),n.attr("class",o),s=s.map((function(){var e=this,i=t.Deferred(),n=t.extend({},r,{queue:!1,complete:function(){i.resolve(e)}});return this.el.animate(this.diff,n),i.promise()})),t.when.apply(t,s.get()).done((function(){i(),t.each(arguments,(function(){var e=this.el;t.each(this.diff,(function(t){e.css(t,"")}))})),r.complete.call(n[0])}))}))},t.fn.extend({addClass:(p=t.fn.addClass,function(e,i,n,o){return i?t.effects.animateClass.call(this,{add:e},i,n,o):p.apply(this,arguments)}),removeClass:function(e){return function(i,n,o,r){return arguments.length>1?t.effects.animateClass.call(this,{remove:i},n,o,r):e.apply(this,arguments)}}(t.fn.removeClass),toggleClass:function(e){return function(i,n,o,r,s){return"boolean"==typeof n||void 0===n?o?t.effects.animateClass.call(this,n?{add:i}:{remove:i},o,r,s):e.apply(this,arguments):t.effects.animateClass.call(this,{toggle:i},n,o,r)}}(t.fn.toggleClass),switchClass:function(e,i,n,o,r){return t.effects.animateClass.call(this,{add:i,remove:e},n,o,r)}}),t.expr&&t.expr.filters&&t.expr.filters.animated&&(t.expr.filters.animated=function(e){return function(i){return!!t(i).data("ui-effects-animated")||e(i)}}(t.expr.filters.animated)),!1!==t.uiBackCompat&&t.extend(t.effects,{save:function(t,e){for(var i=0,n=e.length;i<n;i++)null!==e[i]&&t.data("ui-effects-"+e[i],t[0].style[e[i]])},restore:function(t,e){for(var i,n=0,o=e.length;n<o;n++)null!==e[n]&&(i=t.data("ui-effects-"+e[n]),t.css(e[n],i))},setMode:function(t,e){return"toggle"===e&&(e=t.is(":hidden")?"show":"hide"),e},createWrapper:function(e){if(e.parent().is(".ui-effects-wrapper"))return e.parent();var i={width:e.outerWidth(!0),height:e.outerHeight(!0),float:e.css("float")},n=t("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0}),o={width:e.width(),height:e.height()},r=document.activeElement;try{r.id}catch(t){r=document.body}return e.wrap(n),(e[0]===r||t.contains(e[0],r))&&t(r).trigger("focus"),n=e.parent(),"static"===e.css("position")?(n.css({position:"relative"}),e.css({position:"relative"})):(t.extend(i,{position:e.css("position"),zIndex:e.css("z-index")}),t.each(["top","left","bottom","right"],(function(t,n){i[n]=e.css(n),isNaN(parseInt(i[n],10))&&(i[n]="auto")})),e.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})),e.css(o),n.css(i).show()},removeWrapper:function(e){var i=document.activeElement;return e.parent().is(".ui-effects-wrapper")&&(e.parent().replaceWith(e),(e[0]===i||t.contains(e[0],i))&&t(i).trigger("focus")),e}}),t.extend(t.effects,{version:"1.12.1",define:function(e,i,n){return n||(n=i,i="effect"),t.effects.effect[e]=n,t.effects.effect[e].mode=i,n},scaledDimensions:function(t,e,i){if(0===e)return{height:0,width:0,outerHeight:0,outerWidth:0};var n="horizontal"!==i?(e||100)/100:1,o="vertical"!==i?(e||100)/100:1;return{height:t.height()*o,width:t.width()*n,outerHeight:t.outerHeight()*o,outerWidth:t.outerWidth()*n}},clipToBox:function(t){return{width:t.clip.right-t.clip.left,height:t.clip.bottom-t.clip.top,left:t.clip.left,top:t.clip.top}},unshift:function(t,e,i){var n=t.queue();e>1&&n.splice.apply(n,[1,0].concat(n.splice(e,i))),t.dequeue()},saveStyle:function(t){t.data("ui-effects-style",t[0].style.cssText)},restoreStyle:function(t){t[0].style.cssText=t.data("ui-effects-style")||"",t.removeData("ui-effects-style")},mode:function(t,e){var i=t.is(":hidden");return"toggle"===e&&(e=i?"show":"hide"),(i?"hide"===e:"show"===e)&&(e="none"),e},getBaseline:function(t,e){var i,n;switch(t[0]){case"top":i=0;break;case"middle":i=.5;break;case"bottom":i=1;break;default:i=t[0]/e.height}switch(t[1]){case"left":n=0;break;case"center":n=.5;break;case"right":n=1;break;default:n=t[1]/e.width}return{x:n,y:i}},createPlaceholder:function(e){var i,n=e.css("position"),o=e.position();return e.css({marginTop:e.css("marginTop"),marginBottom:e.css("marginBottom"),marginLeft:e.css("marginLeft"),marginRight:e.css("marginRight")}).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()),/^(static|relative)/.test(n)&&(n="absolute",i=t("<"+e[0].nodeName+">").insertAfter(e).css({display:/^(inline|ruby)/.test(e.css("display"))?"inline-block":"block",visibility:"hidden",marginTop:e.css("marginTop"),marginBottom:e.css("marginBottom"),marginLeft:e.css("marginLeft"),marginRight:e.css("marginRight"),float:e.css("float")}).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()).addClass("ui-effects-placeholder"),e.data("ui-effects-placeholder",i)),e.css({position:n,left:o.left,top:o.top}),i},removePlaceholder:function(t){var e="ui-effects-placeholder",i=t.data(e);i&&(i.remove(),t.removeData(e))},cleanUp:function(e){t.effects.restoreStyle(e),t.effects.removePlaceholder(e)},setTransition:function(e,i,n,o){return o=o||{},t.each(i,(function(t,i){var r=e.cssUnit(i);r[0]>0&&(o[i]=r[0]*n+r[1])})),o}}),t.fn.extend({effect:function(){var e=y.apply(this,arguments),i=t.effects.effect[e.effect],n=i.mode,o=e.queue,r=o||"fx",s=e.complete,a=e.mode,f=[],c=function(e){var i=t(this),o=t.effects.mode(i,a)||n;i.data("ui-effects-animated",!0),f.push(o),n&&("show"===o||o===n&&"hide"===o)&&i.show(),n&&"none"===o||t.effects.saveStyle(i),t.isFunction(e)&&e()};if(t.fx.off||!i)return a?this[a](e.duration,s):this.each((function(){s&&s.call(this)}));function l(o){var r=t(this);function c(){t.isFunction(s)&&s.call(r[0]),t.isFunction(o)&&o()}e.mode=f.shift(),!1===t.uiBackCompat||n?"none"===e.mode?(r[a](),c()):i.call(r[0],e,(function(){r.removeData("ui-effects-animated"),t.effects.cleanUp(r),"hide"===e.mode&&r.hide(),c()})):(r.is(":hidden")?"hide"===a:"show"===a)?(r[a](),c()):i.call(r[0],e,c)}return!1===o?this.each(c).each(l):this.queue(r,c).queue(r,l)},show:function(t){return function(e){if(v(e))return t.apply(this,arguments);var i=y.apply(this,arguments);return i.mode="show",this.effect.call(this,i)}}(t.fn.show),hide:function(t){return function(e){if(v(e))return t.apply(this,arguments);var i=y.apply(this,arguments);return i.mode="hide",this.effect.call(this,i)}}(t.fn.hide),toggle:function(t){return function(e){if(v(e)||"boolean"==typeof e)return t.apply(this,arguments);var i=y.apply(this,arguments);return i.mode="toggle",this.effect.call(this,i)}}(t.fn.toggle),cssUnit:function(e){var i=this.css(e),n=[];return t.each(["em","px","%","pt"],(function(t,e){i.indexOf(e)>0&&(n=[parseFloat(i),e])})),n},cssClip:function(t){return t?this.css("clip","rect("+t.top+"px "+t.right+"px "+t.bottom+"px "+t.left+"px)"):x(this.css("clip"),this)},transfer:function(e,i){var n=t(this),o=t(e.to),r="fixed"===o.css("position"),s=t("body"),a=r?s.scrollTop():0,f=r?s.scrollLeft():0,c=o.offset(),l={top:c.top-a,left:c.left-f,height:o.innerHeight(),width:o.innerWidth()},u=n.offset(),d=t("<div class='ui-effects-transfer'></div>").appendTo("body").addClass(e.className).css({top:u.top-a,left:u.left-f,height:n.innerHeight(),width:n.innerWidth(),position:r?"fixed":"absolute"}).animate(l,e.duration,e.easing,(function(){d.remove(),t.isFunction(i)&&i()}))}}),t.fx.step.clip=function(e){e.clipInit||(e.start=t(e.elem).cssClip(),"string"==typeof e.end&&(e.end=x(e.end,e.elem)),e.clipInit=!0),t(e.elem).cssClip({top:e.pos*(e.end.top-e.start.top)+e.start.top,right:e.pos*(e.end.right-e.start.right)+e.start.right,bottom:e.pos*(e.end.bottom-e.start.bottom)+e.start.bottom,left:e.pos*(e.end.left-e.start.left)+e.start.left})};var w={};t.each(["Quad","Cubic","Quart","Quint","Expo"],(function(t,e){w[e]=function(e){return Math.pow(e,t+2)}})),t.extend(w,{Sine:function(t){return 1-Math.cos(t*Math.PI/2)},Circ:function(t){return 1-Math.sqrt(1-t*t)},Elastic:function(t){return 0===t||1===t?t:-Math.pow(2,8*(t-1))*Math.sin((80*(t-1)-7.5)*Math.PI/15)},Back:function(t){return t*t*(3*t-2)},Bounce:function(t){for(var e,i=4;t<((e=Math.pow(2,--i))-1)/11;);return 1/Math.pow(4,3-i)-7.5625*Math.pow((3*e-2)/22-t,2)}}),t.each(w,(function(e,i){t.easing["easeIn"+e]=i,t.easing["easeOut"+e]=function(t){return 1-i(1-t)},t.easing["easeInOut"+e]=function(t){return t<.5?i(2*t)/2:1-i(-2*t+2)/2}})),
/*!
 * jQuery UI Effects Blind 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("blind","hide",(function(e,i){var n={up:["bottom","top"],vertical:["bottom","top"],down:["top","bottom"],left:["right","left"],horizontal:["right","left"],right:["left","right"]},o=t(this),r=e.direction||"up",s=o.cssClip(),a={clip:t.extend({},s)},f=t.effects.createPlaceholder(o);a.clip[n[r][0]]=a.clip[n[r][1]],"show"===e.mode&&(o.cssClip(a.clip),f&&f.css(t.effects.clipToBox(a)),a.clip=s),f&&f.animate(t.effects.clipToBox(a),e.duration,e.easing),o.animate(a,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Bounce 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("bounce",(function(e,i){var n,o,r,s=t(this),a=e.mode,f="hide"===a,c="show"===a,l=e.direction||"up",u=e.distance,d=e.times||5,h=2*d+(c||f?1:0),p=e.duration/h,g=e.easing,m="up"===l||"down"===l?"top":"left",b="up"===l||"left"===l,y=0,v=s.queue().length;for(t.effects.createPlaceholder(s),r=s.css(m),u||(u=s["top"===m?"outerHeight":"outerWidth"]()/3),c&&((o={opacity:1})[m]=r,s.css("opacity",0).css(m,b?2*-u:2*u).animate(o,p,g)),f&&(u/=Math.pow(2,d-1)),(o={})[m]=r;y<d;y++)(n={})[m]=(b?"-=":"+=")+u,s.animate(n,p,g).animate(o,p,g),u=f?2*u:u/2;f&&((n={opacity:0})[m]=(b?"-=":"+=")+u,s.animate(n,p,g)),s.queue(i),t.effects.unshift(s,v,h+1)})),
/*!
 * jQuery UI Effects Clip 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("clip","hide",(function(e,i){var n,o={},r=t(this),s=e.direction||"vertical",a="both"===s,f=a||"horizontal"===s,c=a||"vertical"===s;n=r.cssClip(),o.clip={top:c?(n.bottom-n.top)/2:n.top,right:f?(n.right-n.left)/2:n.right,bottom:c?(n.bottom-n.top)/2:n.bottom,left:f?(n.right-n.left)/2:n.left},t.effects.createPlaceholder(r),"show"===e.mode&&(r.cssClip(o.clip),o.clip=n),r.animate(o,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Drop 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("drop","hide",(function(e,i){var n,o=t(this),r="show"===e.mode,s=e.direction||"left",a="up"===s||"down"===s?"top":"left",f="up"===s||"left"===s?"-=":"+=",c="+="===f?"-=":"+=",l={opacity:0};t.effects.createPlaceholder(o),n=e.distance||o["top"===a?"outerHeight":"outerWidth"](!0)/2,l[a]=f+n,r&&(o.css(l),l[a]=c+n,l.opacity=1),o.animate(l,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Explode 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("explode","hide",(function(e,i){var n,o,r,s,a,f,c=e.pieces?Math.round(Math.sqrt(e.pieces)):3,l=c,u=t(this),d="show"===e.mode,h=u.show().css("visibility","hidden").offset(),p=Math.ceil(u.outerWidth()/l),g=Math.ceil(u.outerHeight()/c),m=[];function b(){m.push(this),m.length===c*l&&(u.css({visibility:"visible"}),t(m).remove(),i())}for(n=0;n<c;n++)for(s=h.top+n*g,f=n-(c-1)/2,o=0;o<l;o++)r=h.left+o*p,a=o-(l-1)/2,u.clone().appendTo("body").wrap("<div></div>").css({position:"absolute",visibility:"visible",left:-o*p,top:-n*g}).parent().addClass("ui-effects-explode").css({position:"absolute",overflow:"hidden",width:p,height:g,left:r+(d?a*p:0),top:s+(d?f*g:0),opacity:d?0:1}).animate({left:r+(d?0:a*p),top:s+(d?0:f*g),opacity:d?1:0},e.duration||500,e.easing,b)})),
/*!
 * jQuery UI Effects Fade 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("fade","toggle",(function(e,i){var n="show"===e.mode;t(this).css("opacity",n?0:1).animate({opacity:n?1:0},{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Fold 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("fold","hide",(function(e,i){var n=t(this),o=e.mode,r="show"===o,s="hide"===o,a=e.size||15,f=/([0-9]+)%/.exec(a),c=!!e.horizFirst?["right","bottom"]:["bottom","right"],l=e.duration/2,u=t.effects.createPlaceholder(n),d=n.cssClip(),h={clip:t.extend({},d)},p={clip:t.extend({},d)},g=[d[c[0]],d[c[1]]],m=n.queue().length;f&&(a=parseInt(f[1],10)/100*g[s?0:1]),h.clip[c[0]]=a,p.clip[c[0]]=a,p.clip[c[1]]=0,r&&(n.cssClip(p.clip),u&&u.css(t.effects.clipToBox(p)),p.clip=d),n.queue((function(i){u&&u.animate(t.effects.clipToBox(h),l,e.easing).animate(t.effects.clipToBox(p),l,e.easing),i()})).animate(h,l,e.easing).animate(p,l,e.easing).queue(i),t.effects.unshift(n,m,4)})),
/*!
 * jQuery UI Effects Highlight 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("highlight","show",(function(e,i){var n=t(this),o={backgroundColor:n.css("backgroundColor")};"hide"===e.mode&&(o.opacity=0),t.effects.saveStyle(n),n.css({backgroundImage:"none",backgroundColor:e.color||"#ffff99"}).animate(o,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Size 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("size",(function(e,i){var n,o,r,s=t(this),a=["fontSize"],f=["borderTopWidth","borderBottomWidth","paddingTop","paddingBottom"],c=["borderLeftWidth","borderRightWidth","paddingLeft","paddingRight"],l=e.mode,u="effect"!==l,d=e.scale||"both",h=e.origin||["middle","center"],p=s.css("position"),g=s.position(),m=t.effects.scaledDimensions(s),b=e.from||m,y=e.to||t.effects.scaledDimensions(s,0);t.effects.createPlaceholder(s),"show"===l&&(r=b,b=y,y=r),o={from:{y:b.height/m.height,x:b.width/m.width},to:{y:y.height/m.height,x:y.width/m.width}},"box"!==d&&"both"!==d||(o.from.y!==o.to.y&&(b=t.effects.setTransition(s,f,o.from.y,b),y=t.effects.setTransition(s,f,o.to.y,y)),o.from.x!==o.to.x&&(b=t.effects.setTransition(s,c,o.from.x,b),y=t.effects.setTransition(s,c,o.to.x,y))),"content"!==d&&"both"!==d||o.from.y!==o.to.y&&(b=t.effects.setTransition(s,a,o.from.y,b),y=t.effects.setTransition(s,a,o.to.y,y)),h&&(n=t.effects.getBaseline(h,m),b.top=(m.outerHeight-b.outerHeight)*n.y+g.top,b.left=(m.outerWidth-b.outerWidth)*n.x+g.left,y.top=(m.outerHeight-y.outerHeight)*n.y+g.top,y.left=(m.outerWidth-y.outerWidth)*n.x+g.left),s.css(b),"content"!==d&&"both"!==d||(f=f.concat(["marginTop","marginBottom"]).concat(a),c=c.concat(["marginLeft","marginRight"]),s.find("*[width]").each((function(){var i=t(this),n=t.effects.scaledDimensions(i),r={height:n.height*o.from.y,width:n.width*o.from.x,outerHeight:n.outerHeight*o.from.y,outerWidth:n.outerWidth*o.from.x},s={height:n.height*o.to.y,width:n.width*o.to.x,outerHeight:n.height*o.to.y,outerWidth:n.width*o.to.x};o.from.y!==o.to.y&&(r=t.effects.setTransition(i,f,o.from.y,r),s=t.effects.setTransition(i,f,o.to.y,s)),o.from.x!==o.to.x&&(r=t.effects.setTransition(i,c,o.from.x,r),s=t.effects.setTransition(i,c,o.to.x,s)),u&&t.effects.saveStyle(i),i.css(r),i.animate(s,e.duration,e.easing,(function(){u&&t.effects.restoreStyle(i)}))}))),s.animate(y,{queue:!1,duration:e.duration,easing:e.easing,complete:function(){var e=s.offset();0===y.opacity&&s.css("opacity",b.opacity),u||(s.css("position","static"===p?"relative":p).offset(e),t.effects.saveStyle(s)),i()}})})),
/*!
 * jQuery UI Effects Scale 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("scale",(function(e,i){var n=t(this),o=e.mode,r=parseInt(e.percent,10)||(0===parseInt(e.percent,10)||"effect"!==o?0:100),s=t.extend(!0,{from:t.effects.scaledDimensions(n),to:t.effects.scaledDimensions(n,r,e.direction||"both"),origin:e.origin||["middle","center"]},e);e.fade&&(s.from.opacity=1,s.to.opacity=0),t.effects.effect.size.call(this,s,i)})),
/*!
 * jQuery UI Effects Puff 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("puff","hide",(function(e,i){var n=t.extend(!0,{},e,{fade:!0,percent:parseInt(e.percent,10)||150});t.effects.effect.scale.call(this,n,i)})),
/*!
 * jQuery UI Effects Pulsate 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("pulsate","show",(function(e,i){var n=t(this),o=e.mode,r="show"===o,s=r||"hide"===o,a=2*(e.times||5)+(s?1:0),f=e.duration/a,c=0,l=1,u=n.queue().length;for(!r&&n.is(":visible")||(n.css("opacity",0).show(),c=1);l<a;l++)n.animate({opacity:c},f,e.easing),c=1-c;n.animate({opacity:c},f,e.easing),n.queue(i),t.effects.unshift(n,u,a+1)})),
/*!
 * jQuery UI Effects Shake 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("shake",(function(e,i){var n=1,o=t(this),r=e.direction||"left",s=e.distance||20,a=e.times||3,f=2*a+1,c=Math.round(e.duration/f),l="up"===r||"down"===r?"top":"left",u="up"===r||"left"===r,d={},h={},p={},g=o.queue().length;for(t.effects.createPlaceholder(o),d[l]=(u?"-=":"+=")+s,h[l]=(u?"+=":"-=")+2*s,p[l]=(u?"-=":"+=")+2*s,o.animate(d,c,e.easing);n<a;n++)o.animate(h,c,e.easing).animate(p,c,e.easing);o.animate(h,c,e.easing).animate(d,c/2,e.easing).queue(i),t.effects.unshift(o,g,f+1)})),
/*!
 * jQuery UI Effects Slide 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
t.effects.define("slide","show",(function(e,i){var n,o,r=t(this),s={up:["bottom","top"],down:["top","bottom"],left:["right","left"],right:["left","right"]},a=e.mode,f=e.direction||"left",c="up"===f||"down"===f?"top":"left",l="up"===f||"left"===f,u=e.distance||r["top"===c?"outerHeight":"outerWidth"](!0),d={};t.effects.createPlaceholder(r),n=r.cssClip(),o=r.position()[c],d[c]=(l?-1:1)*u+o,d.clip=r.cssClip(),d.clip[s[f][1]]=d.clip[s[f][0]],"show"===a&&(r.cssClip(d.clip),r.css(c,d[c]),d.clip=n,d[c]=o),r.animate(d,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})),
/*!
 * jQuery UI Effects Transfer 1.12.1
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
!1!==t.uiBackCompat&&t.effects.define("transfer",(function(e,i){t(this).transfer(e,i)}));