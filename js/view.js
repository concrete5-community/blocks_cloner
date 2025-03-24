(()=>{"use strict";var e,t,n,o={417:(e,t,n)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.hook=function(){document.addEventListener("DOMContentLoaded",(function(){var e;null===(e=window.ConcreteEvent)||void 0===e||e.subscribe("ConcreteMenuShow",(function(e,t){var n=null==t?void 0:t.menu,r=null==t?void 0:t.menuElement;n&&r&&function(e,t){var n,r=1===(null===(n=e.$element)||void 0===n?void 0:n.length)?e.$element[0]:null;if(r){var a=(0,l.parseArea)(r);if(null!==a)!function(e,t,n){if(!t.find("a[data-ccm-blocks-cloner]").length){var l=t.find("a:last");0!==l.length&&l.after($("<a data-ccm-blocks-cloner />").attr("dialog-title",((0,o.localize)("importBlockFromXmlIntoAreaName")||"Import Block from XML into %s").replace("%s",n.displayName)).attr("class","dialog-launch dropdown-item").attr("dialog-width","90%").attr("dialog-height","80%").attr("href","".concat(window.CCM_DISPATCHER_FILENAME,"/ccm/blocks_cloner/dialogs/import?cID=").concat(window.CCM_CID,"&aID=").concat(n.id,"&aHandle=").concat(encodeURIComponent(n.handle))).text((0,o.localize)("importBlockFromXml")||"Import Block from XML").dialog())}}(0,t,a);else{var i=(0,l.parseBlock)(r);null!==i&&function(e,t,n){if(!t.find("a[data-ccm-blocks-cloner]").length){var l=t.find("a:last");0!==l.length&&l.after($("<a data-ccm-blocks-cloner />").attr("dialog-title",((0,o.localize)("exportBlockTypeNameAsXml")||"Export %s block as XML").replace("%s",n.displayName)).attr("class","dialog-launch dropdown-item").attr("dialog-width","90%").attr("dialog-height","80%").attr("href","".concat(window.CCM_DISPATCHER_FILENAME,"/ccm/blocks_cloner/dialogs/export?cID=").concat(window.CCM_CID,"&bID=").concat(n.id)).text((0,o.localize)("exportAsXml")||"Export as XML").dialog())}}(0,t,i)}}}(n,r)}))}))};var o=n(438),l=n(753)},438:(e,t)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.localize=function(e){var t,n=null===(t=window.ccmBlocksClonerI18N)||void 0===t?void 0:t[e];return"string"==typeof n?n:null},t.getBlockTypeName=function(e){var t;return(null===(t=window.ccmBlocksClonerI18N)||void 0===t?void 0:t.blockTypeNames[e])||null}},447:(e,t)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.setElementHighlighted=function e(t,r,a){if(void 0===a&&(a=!1),r){if(n===t)return void(a&&l(n));null!==n&&e(n,!1)}r!==("1"===t.dataset.blocksClonerHighlighted)&&(r?(o.forEach((function(e,n){t.dataset["blocksClonerRestore".concat(n)]=t.style[n],t.style[n]=e})),t.dataset.blocksClonerHighlighted="1",n=t):(Array.from(o.keys()).forEach((function(e){var n=t.dataset["blocksClonerRestore".concat(e)];void 0!==n&&(delete t.dataset["blocksClonerRestore".concat(e)],t.style[e]=n)})),delete t.dataset.blocksClonerHighlighted,n=null)),n&&a&&l(n)};var n=null,o=new Map([["outline","1px solid red"],["boxShadow","0 0 3px 3px #ff0000"],["transition","box-shadow 0.5s, outline 0.5s"]]);function l(e){e.scrollIntoView&&function(e,t){new IntersectionObserver((function(e,n){t(e.some((function(e){return e.isIntersecting}))),n.disconnect()}),{root:null,rootMargin:"0px",threshold:.1}).observe(e)}(e,(function(t){if(!t)try{e.scrollIntoView({behavior:"smooth",block:"nearest",inline:"start"})}catch(e){}}))}},753:(e,t,n)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.parseArea=r,t.parseBlock=a,t.getPageStructure=function(e){e=Object.assign({skipAreasWithoutBlocks:!1,skipBlocksWithoutChildAreas:!1},e||{});var t={children:[]};return i(document.body,t,e),t.children.filter((function(t){return t.type===o.Area&&(!e.skipAreasWithoutBlocks||t.children.length>0)}))};var o,l=n(438);function r(e){if("DIV"!==e.tagName)return null;var t=Number(e.dataset.areaId)||0;if(t<=0)return null;var n=e.dataset.areaHandle;if(!n)return null;var l=e.dataset.areaDisplayName;return l?{type:o.Area,element:e,id:t,handle:n,displayName:l,isGlobal:e.classList.contains("ccm-global-area"),enableGridContainer:["1","true"].includes(e.dataset.areaEnableGridContainer||""),children:[]}:null}function a(e){if("DIV"!==e.tagName)return null;var t=Number(e.dataset.blockId)||0;if(t<=0)return null;var n=e.dataset.blockTypeHandle;return n?{type:o.Block,element:e,id:t,handle:n,displayName:(0,l.getBlockTypeName)(n)||n,children:[]}:null}function i(e,t,n){var l=r(e),d=l?null:a(e),s=l||d,u=t;s&&(t.children.push(s),u=s),Array.from(e.children).forEach((function(e){return i(e,u,n)})),null!==d&&n.skipBlocksWithoutChildAreas&&(c(d,o.Area)||t.children.splice(t.children.indexOf(d),1)),null!==l&&n.skipAreasWithoutBlocks&&(c(l,o.Block)||t.children.splice(t.children.indexOf(l),1))}function c(e,t){return!!e.children.some((function(e){return e.type===t}))||e.children.some((function(e){return c(e,t)}))}!function(e){e.Area="area",e.Block="block"}(o||(o={}))}},l={};function r(e){var t=l[e];if(void 0!==t)return t.exports;var n=l[e]={exports:{}};return o[e](n,n.exports,r),n.exports}e=r(447),t=r(417),n=r(753),void 0===window.ccmBlocksCloner&&(window.ccmBlocksCloner={getPageStructure:n.getPageStructure,setElementHighlighted:e.setElementHighlighted},(0,t.hook)())})();