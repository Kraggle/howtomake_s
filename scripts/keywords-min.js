import{jQuery as t}from"./src/jquery-3.5.1-min.js";import e from"./custom/Paths.js";import{K as s,timed as n}from"./custom/K-min.js";import a from"./custom/SVGIcons-min.js";import{getKeywords as o,getSettings as i}from"./keywords-shared-min.js";t((()=>{const s=t("#htm-keyword-info");if(!s.length)return;const r={numbers:{1:"one",2:"two",3:"three",4:"four",5:"five",6:"six",7:"seven",8:"eight",9:"nine"},ignore:[],timed:n(),content:null,new:!1,on:!0,groups:{},update(e){r.timed.run((()=>{r.new=!1,e||(e=t("textarea#content")),r.on=!!e.level;const s=r.on?e.level.content:t(e.target?e.target:e).val();s!==r.content&&(r.content=s,r.task())}),r.new?0:1e3*r.refresh)},createElements(){t(".htm-tab-wrap .tab-group").css("grid-template-columns",`repeat(${r.max-(r.min-1)}, max-content)`);const e=e=>{const n=t(`.tab[tab=${e}]`,s);return n.length?n:t("<div />",{class:"tab",tab:e,text:e})},n=e=>{const n=t(`.box[box=${e}]`,s),a=1==e?"":"s";return n.length?n:t("<div />",{class:"box",box:e,html:`<h3 class="title">Keywords <span>(${r.numbers[e]} word${a})</span></h3>\n\t\t\t\t\t<input type="text" class="filter-input" filter="${e}" placeholder="Keyword Filter"/>\n\t\t\t\t\t<div class="keys-table"></div>`})};for(let t=1;t<=10;t++){const s=e(t),a=n(t);t<r.min||t>r.max?(s.remove(),a.remove()):(s.appendTo(".htm-tab-wrap .tab-group"),a.appendTo(".htm-tab-wrap .htm-boxes"))}},task(){r.createElements(),o(r),r.display()},display(){t.each(r.groups,((e,s)=>{const n=t(`.htm-boxes [box=${e}] .keys-table`);n.html(`\n\t\t\t\t\t<div class="row head">\n\t\t\t\t\t\t<div class="head-wrap" order="asc" sort="filter">\n\t\t\t\t\t\t\t<span class="left">Keywords</span>\n\t\t\t\t\t\t\t<div class="icon">${a.sort}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class="head-wrap" order="asc" sort="frequency">\n\t\t\t\t\t\t\t<span>Freq.</span>\n\t\t\t\t\t\t\t<div class="icon">${a.sort}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class="head-wrap sort" order="desc" sort="density">\n\t\t\t\t\t\t\t<span>Density</span>\n\t\t\t\t\t\t\t<div class="icon">${a.sort}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class="sort-table"></div>\n\t\t\t\t`),t.each(s,((e,s)=>{t(".sort-table",n).append(`\n\t\t\t\t\t\t<div class="row item" filter="${s.keywords}" \n\t\t\t\t\t\t\tfrequency="${s.count}" density="${(1e4*s.density).toFixed(0)}">\n\t\t\t\t\t\t\t<span class="left">${s.keywords}</span>\n\t\t\t\t\t\t\t<span>${s.count}</span>\n\t\t\t\t\t\t\t<span>${s.density.toFixed(2)}%</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t`)}))}))},settings(){t.when(i(r)).then((()=>{r.content="",r.new=!0,r.update()}))}};t("#post-body-content").on("input propertyChange","textarea#content",r.update);const c=setInterval((()=>{tinyMCE.editors.length&&"content"===tinyMCE.editors[0].id&&(tinyMCE.editors[0].on("change",r.update),clearInterval(c))}),2e3);r.settings(),s.on("click",".tab:not(.active)",(function(){t(".active",s).removeClass("active"),t(this).addClass("active");const e=t(`.box[box=${t(this).attr("tab")}]`,s);e.addClass("active"),t(".filter-input",e).trigger("input")})),s.on("click",".button.delete",(function(){const e=t(this).attr("delete"),n=t(this).closest(`[is=${e}]`);t(`[is=${e}]`,s).length>1?n.remove():t("input",n).val("")})),s.on("click",".button-primary[add]",(function(){const e=t(this).attr("add"),s=t(this).parent();s.siblings(`[is=${e}]`).eq(0).clone().insertBefore(s).find("input").val("")})),s.on("input propertyChange",".filter-input",(function(){const e=t(this).data();!e.timer&&(e.timer=n()),e.timer.run((()=>{const e=t(this).val(),n=t(this).parent();t(".filter-input",s).val(e),n.find("[filter]").removeClass("hide"),e&&n.find(`:not([filter*=${e}])`).addClass("hide")}),500)})),s.on("click",".head-wrap",(function(){let e=t(this).attr("order");const s=t(this).attr("sort");t(this).hasClass("sort")?(e="asc"==e?"desc":"asc",t(this).attr("order",e)):(t(this).siblings(".sort").removeClass("sort"),t(this).addClass("sort"));const n=t(this).closest(".keys-table"),a=n.find(".row.item").sort(((n,a)=>{let o=t(n).attr(s),i=t(a).attr(s);const r="asc"==e?-1:1,c="asc"==e?1:-1;return isNaN(o)||(o=parseInt(o),i=parseInt(i)),o<i?r:o>i?c:0}));t(".sort-table",n).html(a)})),t(".save",s).on("click",(function(){const n={};t("[key]",s).each((function(){const e=t("input",this),s=t(this).attr("key");let a=[];e.length>1?e.each((function(){t(this).val()&&a.push(t(this).val())})):a="checkbox"==e.attr("type")?e.is(":checked"):e.val(),n[s]=a})),t.ajax({url:e.ajax,type:"POST",data:{action:"set_keyword_settings",nonce:t("#keyword_nonce").val(),data:n}}).done((()=>{r.settings()}))}))}));