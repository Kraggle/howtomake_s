import{jQuery as e}from"../src/jquery-3.5.1-min.js";import o from"./Paths.js";const t={};e.each([{name:"arrow",type:"solid",icon:"angle-right"},{name:"fast",type:"solid",icon:"angle-double-right"},{name:"all",type:"solid",icon:"ellipsis-h"},{name:"sort",type:"solid",icon:"sort-up"}],((n,s)=>{e.get(`${o.fonts}/font-awesome/${s.type}/${s.icon}.svg`,(o=>t[s.name]=e("<div>").append(e(o).find("svg").clone()).html()))}));export default t;