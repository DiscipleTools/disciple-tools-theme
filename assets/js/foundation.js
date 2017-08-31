(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilBox=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=100);/******/})(/************************************************************************//******/{/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/100:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(34);/***/},/***/3:/***/function _(module,exports){module.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};/***/},/***/34:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_box__=__webpack_require__(64);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Box=__WEBPACK_IMPORTED_MODULE_1__foundation_util_box__["a"/* Box */];/***/},/***/64:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Box;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_util_core__=__webpack_require__(3);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_util_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_util_core__);var Box={ImNotTouchingYou:ImNotTouchingYou,OverlapArea:OverlapArea,GetDimensions:GetDimensions,GetOffsets:GetOffsets,GetExplicitOffsets:GetExplicitOffsets/**
   * Compares the dimensions of an element to a container and determines collision events with container.
   * @function
   * @param {jQuery} element - jQuery object to test for collisions.
   * @param {jQuery} parent - jQuery object to use as bounding container.
   * @param {Boolean} lrOnly - set to true to check left and right values only.
   * @param {Boolean} tbOnly - set to true to check top and bottom values only.
   * @default if no parent object passed, detects collisions with `window`.
   * @returns {Boolean} - true if collision free, false if a collision in any direction.
   */};function ImNotTouchingYou(element,parent,lrOnly,tbOnly,ignoreBottom){return OverlapArea(element,parent,lrOnly,tbOnly,ignoreBottom)===0;};function OverlapArea(element,parent,lrOnly,tbOnly,ignoreBottom){var eleDims=GetDimensions(element),topOver,bottomOver,leftOver,rightOver;if(parent){var parDims=GetDimensions(parent);bottomOver=parDims.height+parDims.offset.top-(eleDims.offset.top+eleDims.height);topOver=eleDims.offset.top-parDims.offset.top;leftOver=eleDims.offset.left-parDims.offset.left;rightOver=parDims.width+parDims.offset.left-(eleDims.offset.left+eleDims.width);}else{bottomOver=eleDims.windowDims.height+eleDims.windowDims.offset.top-(eleDims.offset.top+eleDims.height);topOver=eleDims.offset.top-eleDims.windowDims.offset.top;leftOver=eleDims.offset.left-eleDims.windowDims.offset.left;rightOver=eleDims.windowDims.width-(eleDims.offset.left+eleDims.width);}bottomOver=ignoreBottom?0:Math.min(bottomOver,0);topOver=Math.min(topOver,0);leftOver=Math.min(leftOver,0);rightOver=Math.min(rightOver,0);if(lrOnly){return leftOver+rightOver;}if(tbOnly){return topOver+bottomOver;}// use sum of squares b/c we care about overlap area.
return Math.sqrt(topOver*topOver+bottomOver*bottomOver+leftOver*leftOver+rightOver*rightOver);}/**
 * Uses native methods to return an object of dimension values.
 * @function
 * @param {jQuery || HTML} element - jQuery object or DOM element for which to get the dimensions. Can be any element other that document or window.
 * @returns {Object} - nested object of integer pixel values
 * TODO - if element is window, return only those values.
 */function GetDimensions(elem){elem=elem.length?elem[0]:elem;if(elem===window||elem===document){throw new Error("I'm sorry, Dave. I'm afraid I can't do that.");}var rect=elem.getBoundingClientRect(),parRect=elem.parentNode.getBoundingClientRect(),winRect=document.body.getBoundingClientRect(),winY=window.pageYOffset,winX=window.pageXOffset;return{width:rect.width,height:rect.height,offset:{top:rect.top+winY,left:rect.left+winX},parentDims:{width:parRect.width,height:parRect.height,offset:{top:parRect.top+winY,left:parRect.left+winX}},windowDims:{width:winRect.width,height:winRect.height,offset:{top:winY,left:winX}}};}/**
 * Returns an object of top and left integer pixel values for dynamically rendered elements,
 * such as: Tooltip, Reveal, and Dropdown. Maintained for backwards compatibility, and where
 * you don't know alignment, but generally from
 * 6.4 forward you should use GetExplicitOffsets, as GetOffsets conflates position and alignment.
 * @function
 * @param {jQuery} element - jQuery object for the element being positioned.
 * @param {jQuery} anchor - jQuery object for the element's anchor point.
 * @param {String} position - a string relating to the desired position of the element, relative to it's anchor
 * @param {Number} vOffset - integer pixel value of desired vertical separation between anchor and element.
 * @param {Number} hOffset - integer pixel value of desired horizontal separation between anchor and element.
 * @param {Boolean} isOverflow - if a collision event is detected, sets to true to default the element to full width - any desired offset.
 * TODO alter/rewrite to work with `em` values as well/instead of pixels
 */function GetOffsets(element,anchor,position,vOffset,hOffset,isOverflow){console.log("NOTE: GetOffsets is deprecated in favor of GetExplicitOffsets and will be removed in 6.5");switch(position){case'top':return __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0__foundation_util_core__["rtl"])()?GetExplicitOffsets(element,anchor,'top','left',vOffset,hOffset,isOverflow):GetExplicitOffsets(element,anchor,'top','right',vOffset,hOffset,isOverflow);case'bottom':return __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0__foundation_util_core__["rtl"])()?GetExplicitOffsets(element,anchor,'bottom','left',vOffset,hOffset,isOverflow):GetExplicitOffsets(element,anchor,'bottom','right',vOffset,hOffset,isOverflow);case'center top':return GetExplicitOffsets(element,anchor,'top','center',vOffset,hOffset,isOverflow);case'center bottom':return GetExplicitOffsets(element,anchor,'bottom','center',vOffset,hOffset,isOverflow);case'center left':return GetExplicitOffsets(element,anchor,'left','center',vOffset,hOffset,isOverflow);case'center right':return GetExplicitOffsets(element,anchor,'right','center',vOffset,hOffset,isOverflow);case'left bottom':return GetExplicitOffsets(element,anchor,'bottom','left',vOffset,hOffset,isOverflow);case'right bottom':return GetExplicitOffsets(element,anchor,'bottom','right',vOffset,hOffset,isOverflow);// Backwards compatibility... this along with the reveal and reveal full
// classes are the only ones that didn't reference anchor
case'center':return{left:$eleDims.windowDims.offset.left+$eleDims.windowDims.width/2-$eleDims.width/2+hOffset,top:$eleDims.windowDims.offset.top+$eleDims.windowDims.height/2-($eleDims.height/2+vOffset)};case'reveal':return{left:($eleDims.windowDims.width-$eleDims.width)/2+hOffset,top:$eleDims.windowDims.offset.top+vOffset};case'reveal full':return{left:$eleDims.windowDims.offset.left,top:$eleDims.windowDims.offset.top};break;default:return{left:__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0__foundation_util_core__["rtl"])()?$anchorDims.offset.left-$eleDims.width+$anchorDims.width-hOffset:$anchorDims.offset.left+hOffset,top:$anchorDims.offset.top+$anchorDims.height+vOffset};}}function GetExplicitOffsets(element,anchor,position,alignment,vOffset,hOffset,isOverflow){var $eleDims=GetDimensions(element),$anchorDims=anchor?GetDimensions(anchor):null;var topVal,leftVal;// set position related attribute
switch(position){case'top':topVal=$anchorDims.offset.top-($eleDims.height+vOffset);break;case'bottom':topVal=$anchorDims.offset.top+$anchorDims.height+vOffset;break;case'left':leftVal=$anchorDims.offset.left-($eleDims.width+hOffset);break;case'right':leftVal=$anchorDims.offset.left+$anchorDims.width+hOffset;break;}// set alignment related attribute
switch(position){case'top':case'bottom':switch(alignment){case'left':leftVal=$anchorDims.offset.left+hOffset;break;case'right':leftVal=$anchorDims.offset.left-$eleDims.width+$anchorDims.width-hOffset;break;case'center':leftVal=isOverflow?hOffset:$anchorDims.offset.left+$anchorDims.width/2-$eleDims.width/2+hOffset;break;}break;case'right':case'left':switch(alignment){case'bottom':topVal=$anchorDims.offset.top-vOffset+$anchorDims.height-$eleDims.height;break;case'top':topVal=$anchorDims.offset.top+vOffset;break;case'center':topVal=$anchorDims.offset.top+vOffset+$anchorDims.height/2-$eleDims.height/2;break;}break;}return{top:topVal,left:leftVal};}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilBoxMin=mod.exports;}})(this,function(){"use strict";!function(t){function e(i){if(o[i])return o[i].exports;var n=o[i]={i:i,l:!1,exports:{}};return t[i].call(n.exports,n,n.exports,e),n.l=!0,n.exports;}var o={};e.m=t,e.c=o,e.i=function(t){return t;},e.d=function(t,o,i){e.o(t,o)||Object.defineProperty(t,o,{configurable:!1,enumerable:!0,get:i});},e.n=function(t){var o=t&&t.__esModule?function(){return t.default;}:function(){return t;};return e.d(o,"a",o),o;},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e);},e.p="",e(e.s=100);}({1:function _(t,e){t.exports={Foundation:window.Foundation};},100:function _(t,e,o){t.exports=o(34);},3:function _(t,e){t.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};},34:function _(t,e,o){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=o(1),n=(o.n(i),o(64));i.Foundation.Box=n.a;},64:function _(t,e,o){"use strict";function i(t,e,o,i,f){return 0===n(t,e,o,i,f);}function n(t,e,o,i,n){var s,r,h,a,c=f(t);if(e){var l=f(e);r=l.height+l.offset.top-(c.offset.top+c.height),s=c.offset.top-l.offset.top,h=c.offset.left-l.offset.left,a=l.width+l.offset.left-(c.offset.left+c.width);}else r=c.windowDims.height+c.windowDims.offset.top-(c.offset.top+c.height),s=c.offset.top-c.windowDims.offset.top,h=c.offset.left-c.windowDims.offset.left,a=c.windowDims.width-(c.offset.left+c.width);return r=n?0:Math.min(r,0),s=Math.min(s,0),h=Math.min(h,0),a=Math.min(a,0),o?h+a:i?s+r:Math.sqrt(s*s+r*r+h*h+a*a);}function f(t){if((t=t.length?t[0]:t)===window||t===document)throw new Error("I'm sorry, Dave. I'm afraid I can't do that.");var e=t.getBoundingClientRect(),o=t.parentNode.getBoundingClientRect(),i=document.body.getBoundingClientRect(),n=window.pageYOffset,f=window.pageXOffset;return{width:e.width,height:e.height,offset:{top:e.top+n,left:e.left+f},parentDims:{width:o.width,height:o.height,offset:{top:o.top+n,left:o.left+f}},windowDims:{width:i.width,height:i.height,offset:{top:n,left:f}}};}function s(t,e,i,n,f,s){switch(console.log("NOTE: GetOffsets is deprecated in favor of GetExplicitOffsets and will be removed in 6.5"),i){case"top":return o.i(h.rtl)()?r(t,e,"top","left",n,f,s):r(t,e,"top","right",n,f,s);case"bottom":return o.i(h.rtl)()?r(t,e,"bottom","left",n,f,s):r(t,e,"bottom","right",n,f,s);case"center top":return r(t,e,"top","center",n,f,s);case"center bottom":return r(t,e,"bottom","center",n,f,s);case"center left":return r(t,e,"left","center",n,f,s);case"center right":return r(t,e,"right","center",n,f,s);case"left bottom":return r(t,e,"bottom","left",n,f,s);case"right bottom":return r(t,e,"bottom","right",n,f,s);case"center":return{left:$eleDims.windowDims.offset.left+$eleDims.windowDims.width/2-$eleDims.width/2+f,top:$eleDims.windowDims.offset.top+$eleDims.windowDims.height/2-($eleDims.height/2+n)};case"reveal":return{left:($eleDims.windowDims.width-$eleDims.width)/2+f,top:$eleDims.windowDims.offset.top+n};case"reveal full":return{left:$eleDims.windowDims.offset.left,top:$eleDims.windowDims.offset.top};default:return{left:o.i(h.rtl)()?$anchorDims.offset.left-$eleDims.width+$anchorDims.width-f:$anchorDims.offset.left+f,top:$anchorDims.offset.top+$anchorDims.height+n};}}function r(t,e,o,i,n,s,r){var h,a,c=f(t),l=e?f(e):null;switch(o){case"top":h=l.offset.top-(c.height+n);break;case"bottom":h=l.offset.top+l.height+n;break;case"left":a=l.offset.left-(c.width+s);break;case"right":a=l.offset.left+l.width+s;}switch(o){case"top":case"bottom":switch(i){case"left":a=l.offset.left+s;break;case"right":a=l.offset.left-c.width+l.width-s;break;case"center":a=r?s:l.offset.left+l.width/2-c.width/2+s;}break;case"right":case"left":switch(i){case"bottom":h=l.offset.top-n+l.height-c.height;break;case"top":h=l.offset.top+n;break;case"center":h=l.offset.top+n+l.height/2-c.height/2;}}return{top:h,left:a};}o.d(e,"a",function(){return a;});var h=o(3),a=(o.n(h),{ImNotTouchingYou:i,OverlapArea:n,GetDimensions:f,GetOffsets:s,GetExplicitOffsets:r});}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilImageLoader=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=101);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/101:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(35);/***/},/***/35:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_imageLoader__=__webpack_require__(65);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].onImagesLoaded=__WEBPACK_IMPORTED_MODULE_1__foundation_util_imageLoader__["a"/* onImagesLoaded */];/***/},/***/65:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return onImagesLoaded;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);/**
 * Runs a callback function when images are fully loaded.
 * @param {Object} images - Image(s) to check if loaded.
 * @param {Func} callback - Function to execute when image is fully loaded.
 */function onImagesLoaded(images,callback){var self=this,unloaded=images.length;if(unloaded===0){callback();}images.each(function(){// Check if image is loaded
if(this.complete&&this.naturalWidth!==undefined){singleImageLoaded();}else{// If the above check failed, simulate loading on detached element.
var image=new Image();// Still count image as loaded if it finalizes with an error.
var events="load.zf.images error.zf.images";__WEBPACK_IMPORTED_MODULE_0_jquery___default()(image).one(events,function me(event){// Unbind the event listeners. We're using 'one' but only one of the two events will have fired.
__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).off(events,me);singleImageLoaded();});image.src=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).attr('src');}});function singleImageLoaded(){unloaded--;if(unloaded===0){callback();}}}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilImageLoaderMin=mod.exports;}})(this,function(){"use strict";!function(n){function t(o){if(e[o])return e[o].exports;var r=e[o]={i:o,l:!1,exports:{}};return n[o].call(r.exports,r,r.exports,t),r.l=!0,r.exports;}var e={};t.m=n,t.c=e,t.i=function(n){return n;},t.d=function(n,e,o){t.o(n,e)||Object.defineProperty(n,e,{configurable:!1,enumerable:!0,get:o});},t.n=function(n){var e=n&&n.__esModule?function(){return n.default;}:function(){return n;};return t.d(e,"a",e),e;},t.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t);},t.p="",t(t.s=101);}({0:function _(n,t){n.exports=jQuery;},1:function _(n,t){n.exports={Foundation:window.Foundation};},101:function _(n,t,e){n.exports=e(35);},35:function _(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=e(1),r=(e.n(o),e(65));o.Foundation.onImagesLoaded=r.a;},65:function _(n,t,e){"use strict";function o(n,t){function e(){0===--o&&t();}var o=n.length;0===o&&t(),n.each(function(){if(this.complete&&void 0!==this.naturalWidth)e();else{var n=new Image(),t="load.zf.images error.zf.images";i()(n).one(t,function n(o){i()(this).off(t,n),e();}),n.src=i()(this).attr("src");}});}e.d(t,"a",function(){return o;});var r=e(0),i=e.n(r);}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilKeyboard=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=102);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/102:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(36);/***/},/***/3:/***/function _(module,exports){module.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};/***/},/***/36:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_keyboard__=__webpack_require__(66);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Keyboard=__WEBPACK_IMPORTED_MODULE_1__foundation_util_keyboard__["a"/* Keyboard */];/***/},/***/66:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Keyboard;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_core__=__webpack_require__(3);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__foundation_util_core__);/*******************************************
 *                                         *
 * This util was created by Marius Olbertz *
 * Please thank Marius on GitHub /owlbertz *
 * or the web http://www.mariusolbertz.de/ *
 *                                         *
 ******************************************/var keyCodes={9:'TAB',13:'ENTER',27:'ESCAPE',32:'SPACE',35:'END',36:'HOME',37:'ARROW_LEFT',38:'ARROW_UP',39:'ARROW_RIGHT',40:'ARROW_DOWN'};var commands={};// Functions pulled out to be referenceable from internals
function findFocusable($element){if(!$element){return false;}return $element.find('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]').filter(function(){if(!__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).is(':visible')||__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).attr('tabindex')<0){return false;}//only have visible elements and those that have a tabindex greater or equal 0
return true;});}function parseKey(event){var key=keyCodes[event.which||event.keyCode]||String.fromCharCode(event.which).toUpperCase();// Remove un-printable characters, e.g. for `fromCharCode` calls for CTRL only events
key=key.replace(/\W+/,'');if(event.shiftKey)key='SHIFT_'+key;if(event.ctrlKey)key='CTRL_'+key;if(event.altKey)key='ALT_'+key;// Remove trailing underscore, in case only modifiers were used (e.g. only `CTRL_ALT`)
key=key.replace(/_$/,'');return key;}var Keyboard={keys:getKeyCodes(keyCodes),/**
   * Parses the (keyboard) event and returns a String that represents its key
   * Can be used like Foundation.parseKey(event) === Foundation.keys.SPACE
   * @param {Event} event - the event generated by the event handler
   * @return String key - String that represents the key pressed
   */parseKey:parseKey,/**
   * Handles the given (keyboard) event
   * @param {Event} event - the event generated by the event handler
   * @param {String} component - Foundation component's name, e.g. Slider or Reveal
   * @param {Objects} functions - collection of functions that are to be executed
   */handleKey:function handleKey(event,component,functions){var commandList=commands[component],keyCode=this.parseKey(event),cmds,command,fn;if(!commandList)return console.warn('Component not defined!');if(typeof commandList.ltr==='undefined'){// this component does not differentiate between ltr and rtl
cmds=commandList;// use plain list
}else{// merge ltr and rtl: if document is rtl, rtl overwrites ltr and vice versa
if(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1__foundation_util_core__["rtl"])())cmds=__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.extend({},commandList.ltr,commandList.rtl);else cmds=__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.extend({},commandList.rtl,commandList.ltr);}command=cmds[keyCode];fn=functions[command];if(fn&&typeof fn==='function'){// execute function  if exists
var returnValue=fn.apply();if(functions.handled||typeof functions.handled==='function'){// execute function when event was handled
functions.handled(returnValue);}}else{if(functions.unhandled||typeof functions.unhandled==='function'){// execute function when event was not handled
functions.unhandled();}}},/**
   * Finds all focusable elements within the given `$element`
   * @param {jQuery} $element - jQuery object to search within
   * @return {jQuery} $focusable - all focusable elements within `$element`
   */findFocusable:findFocusable,/**
   * Returns the component name name
   * @param {Object} component - Foundation component, e.g. Slider or Reveal
   * @return String componentName
   */register:function register(componentName,cmds){commands[componentName]=cmds;},// TODO9438: These references to Keyboard need to not require global. Will 'this' work in this context?
//
/**
   * Traps the focus in the given element.
   * @param  {jQuery} $element  jQuery object to trap the foucs into.
   */trapFocus:function trapFocus($element){var $focusable=findFocusable($element),$firstFocusable=$focusable.eq(0),$lastFocusable=$focusable.eq(-1);$element.on('keydown.zf.trapfocus',function(event){if(event.target===$lastFocusable[0]&&parseKey(event)==='TAB'){event.preventDefault();$firstFocusable.focus();}else if(event.target===$firstFocusable[0]&&parseKey(event)==='SHIFT_TAB'){event.preventDefault();$lastFocusable.focus();}});},/**
   * Releases the trapped focus from the given element.
   * @param  {jQuery} $element  jQuery object to release the focus for.
   */releaseFocus:function releaseFocus($element){$element.off('keydown.zf.trapfocus');}};/*
 * Constants for easier comparing.
 * Can be used like Foundation.parseKey(event) === Foundation.keys.SPACE
 */function getKeyCodes(kcs){var k={};for(var kc in kcs){k[kcs[kc]]=kcs[kc];}return k;}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilKeyboardMin=mod.exports;}})(this,function(){"use strict";!function(n){function t(o){if(e[o])return e[o].exports;var r=e[o]={i:o,l:!1,exports:{}};return n[o].call(r.exports,r,r.exports,t),r.l=!0,r.exports;}var e={};t.m=n,t.c=e,t.i=function(n){return n;},t.d=function(n,e,o){t.o(n,e)||Object.defineProperty(n,e,{configurable:!1,enumerable:!0,get:o});},t.n=function(n){var e=n&&n.__esModule?function(){return n.default;}:function(){return n;};return t.d(e,"a",e),e;},t.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t);},t.p="",t(t.s=102);}({0:function _(n,t){n.exports=jQuery;},1:function _(n,t){n.exports={Foundation:window.Foundation};},102:function _(n,t,e){n.exports=e(36);},3:function _(n,t){n.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};},36:function _(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=e(1),r=(e.n(o),e(66));o.Foundation.Keyboard=r.a;},66:function _(n,t,e){"use strict";function o(n){return!!n&&n.find("a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]").filter(function(){return!(!a()(this).is(":visible")||a()(this).attr("tabindex")<0);});}function r(n){var t=d[n.which||n.keyCode]||String.fromCharCode(n.which).toUpperCase();return t=t.replace(/\W+/,""),n.shiftKey&&(t="SHIFT_"+t),n.ctrlKey&&(t="CTRL_"+t),n.altKey&&(t="ALT_"+t),t=t.replace(/_$/,"");}e.d(t,"a",function(){return c;});var i=e(0),a=e.n(i),u=e(3),d=(e.n(u),{9:"TAB",13:"ENTER",27:"ESCAPE",32:"SPACE",35:"END",36:"HOME",37:"ARROW_LEFT",38:"ARROW_UP",39:"ARROW_RIGHT",40:"ARROW_DOWN"}),f={},c={keys:function(n){var t={};for(var e in n){t[n[e]]=n[e];}return t;}(d),parseKey:r,handleKey:function handleKey(n,t,o){var r,i,d,c=f[t],s=this.parseKey(n);if(!c)return console.warn("Component not defined!");if(r=void 0===c.ltr?c:e.i(u.rtl)()?a.a.extend({},c.ltr,c.rtl):a.a.extend({},c.rtl,c.ltr),i=r[s],(d=o[i])&&"function"==typeof d){var l=d.apply();(o.handled||"function"==typeof o.handled)&&o.handled(l);}else(o.unhandled||"function"==typeof o.unhandled)&&o.unhandled();},findFocusable:o,register:function register(n,t){f[n]=t;},trapFocus:function trapFocus(n){var t=o(n),e=t.eq(0),i=t.eq(-1);n.on("keydown.zf.trapfocus",function(n){n.target===i[0]&&"TAB"===r(n)?(n.preventDefault(),e.focus()):n.target===e[0]&&"SHIFT_TAB"===r(n)&&(n.preventDefault(),i.focus());});},releaseFocus:function releaseFocus(n){n.off("keydown.zf.trapfocus");}};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilMediaQuery=mod.exports;}})(this,function(){'use strict';var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj;}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj;};/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=103);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/103:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(37);/***/},/***/37:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_mediaQuery__=__webpack_require__(67);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].MediaQuery=__WEBPACK_IMPORTED_MODULE_1__foundation_util_mediaQuery__["a"/* MediaQuery */];__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].MediaQuery._init();/***/},/***/67:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return MediaQuery;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);// Default set of media queries
var defaultQueries={'default':'only screen',landscape:'only screen and (orientation: landscape)',portrait:'only screen and (orientation: portrait)',retina:'only screen and (-webkit-min-device-pixel-ratio: 2),'+'only screen and (min--moz-device-pixel-ratio: 2),'+'only screen and (-o-min-device-pixel-ratio: 2/1),'+'only screen and (min-device-pixel-ratio: 2),'+'only screen and (min-resolution: 192dpi),'+'only screen and (min-resolution: 2dppx)'};// matchMedia() polyfill - Test a CSS media type/query in JS.
// Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight. Dual MIT/BSD license
var matchMedia=window.matchMedia||function(){'use strict';// For browsers that support matchMedium api such as IE 9 and webkit
var styleMedia=window.styleMedia||window.media;// For those that don't support matchMedium
if(!styleMedia){var style=document.createElement('style'),script=document.getElementsByTagName('script')[0],info=null;style.type='text/css';style.id='matchmediajs-test';script&&script.parentNode&&script.parentNode.insertBefore(style,script);// 'style.currentStyle' is used by IE <= 8 and 'window.getComputedStyle' for all other browsers
info='getComputedStyle'in window&&window.getComputedStyle(style,null)||style.currentStyle;styleMedia={matchMedium:function matchMedium(media){var text='@media '+media+'{ #matchmediajs-test { width: 1px; } }';// 'style.styleSheet' is used by IE <= 8 and 'style.textContent' for all other browsers
if(style.styleSheet){style.styleSheet.cssText=text;}else{style.textContent=text;}// Test if media query is true or false
return info.width==='1px';}};}return function(media){return{matches:styleMedia.matchMedium(media||'all'),media:media||'all'};};}();var MediaQuery={queries:[],current:'',/**
   * Initializes the media query helper, by extracting the breakpoint list from the CSS and activating the breakpoint watcher.
   * @function
   * @private
   */_init:function _init(){var self=this;var $meta=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('meta.foundation-mq');if(!$meta.length){__WEBPACK_IMPORTED_MODULE_0_jquery___default()('<meta class="foundation-mq">').appendTo(document.head);}var extractedStyles=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('.foundation-mq').css('font-family');var namedQueries;namedQueries=parseStyleToObject(extractedStyles);for(var key in namedQueries){if(namedQueries.hasOwnProperty(key)){self.queries.push({name:key,value:'only screen and (min-width: '+namedQueries[key]+')'});}}this.current=this._getCurrentSize();this._watcher();},/**
   * Checks if the screen is at least as wide as a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it's smaller.
   */atLeast:function atLeast(size){var query=this.get(size);if(query){return matchMedia(query).matches;}return false;},/**
   * Checks if the screen matches to a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to check, either 'small only' or 'small'. Omitting 'only' falls back to using atLeast() method.
   * @returns {Boolean} `true` if the breakpoint matches, `false` if it does not.
   */is:function is(size){size=size.trim().split(' ');if(size.length>1&&size[1]==='only'){if(size[0]===this._getCurrentSize())return true;}else{return this.atLeast(size[0]);}return false;},/**
   * Gets the media query of a breakpoint.
   * @function
   * @param {String} size - Name of the breakpoint to get.
   * @returns {String|null} - The media query of the breakpoint, or `null` if the breakpoint doesn't exist.
   */get:function get(size){for(var i in this.queries){if(this.queries.hasOwnProperty(i)){var query=this.queries[i];if(size===query.name)return query.value;}}return null;},/**
   * Gets the current breakpoint name by testing every breakpoint and returning the last one to match (the biggest one).
   * @function
   * @private
   * @returns {String} Name of the current breakpoint.
   */_getCurrentSize:function _getCurrentSize(){var matched;for(var i=0;i<this.queries.length;i++){var query=this.queries[i];if(matchMedia(query.value).matches){matched=query;}}if((typeof matched==='undefined'?'undefined':_typeof(matched))==='object'){return matched.name;}else{return matched;}},/**
   * Activates the breakpoint watcher, which fires an event on the window whenever the breakpoint changes.
   * @function
   * @private
   */_watcher:function _watcher(){var _this=this;__WEBPACK_IMPORTED_MODULE_0_jquery___default()(window).off('resize.zf.mediaquery').on('resize.zf.mediaquery',function(){var newSize=_this._getCurrentSize(),currentSize=_this.current;if(newSize!==currentSize){// Change the current media query
_this.current=newSize;// Broadcast the media query change on the window
__WEBPACK_IMPORTED_MODULE_0_jquery___default()(window).trigger('changed.zf.mediaquery',[newSize,currentSize]);}});}};// Thank you: https://github.com/sindresorhus/query-string
function parseStyleToObject(str){var styleObject={};if(typeof str!=='string'){return styleObject;}str=str.trim().slice(1,-1);// browsers re-quote string style values
if(!str){return styleObject;}styleObject=str.split('&').reduce(function(ret,param){var parts=param.replace(/\+/g,' ').split('=');var key=parts[0];var val=parts[1];key=decodeURIComponent(key);// missing `=` should be `null`:
// http://w3.org/TR/2012/WD-url-20120524/#collect-url-parameters
val=val===undefined?null:decodeURIComponent(val);if(!ret.hasOwnProperty(key)){ret[key]=val;}else if(Array.isArray(ret[key])){ret[key].push(val);}else{ret[key]=[ret[key],val];}return ret;},{});return styleObject;}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilMediaQueryMin=mod.exports;}})(this,function(){"use strict";var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj;}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj;};!function(e){function t(r){if(n[r])return n[r].exports;var i=n[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,t),i.l=!0,i.exports;}var n={};t.m=e,t.c=n,t.i=function(e){return e;},t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r});},t.n=function(e){var n=e&&e.__esModule?function(){return e.default;}:function(){return e;};return t.d(n,"a",n),n;},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t);},t.p="",t(t.s=103);}({0:function _(e,t){e.exports=jQuery;},1:function _(e,t){e.exports={Foundation:window.Foundation};},103:function _(e,t,n){e.exports=n(37);},37:function _(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=n(1),i=(n.n(r),n(67));r.Foundation.MediaQuery=i.a,r.Foundation.MediaQuery._init();},67:function _(e,t,n){"use strict";function r(e){var t={};return"string"!=typeof e?t:(e=e.trim().slice(1,-1))?t=e.split("&").reduce(function(e,t){var n=t.replace(/\+/g," ").split("="),r=n[0],i=n[1];return r=decodeURIComponent(r),i=void 0===i?null:decodeURIComponent(i),e.hasOwnProperty(r)?Array.isArray(e[r])?e[r].push(i):e[r]=[e[r],i]:e[r]=i,e;},{}):t;}n.d(t,"a",function(){return a;});var i=n(0),u=n.n(i),o=window.matchMedia||function(){var e=window.styleMedia||window.media;if(!e){var t=document.createElement("style"),n=document.getElementsByTagName("script")[0],r=null;t.type="text/css",t.id="matchmediajs-test",n&&n.parentNode&&n.parentNode.insertBefore(t,n),r="getComputedStyle"in window&&window.getComputedStyle(t,null)||t.currentStyle,e={matchMedium:function matchMedium(e){var n="@media "+e+"{ #matchmediajs-test { width: 1px; } }";return t.styleSheet?t.styleSheet.cssText=n:t.textContent=n,"1px"===r.width;}};}return function(t){return{matches:e.matchMedium(t||"all"),media:t||"all"};};}(),a={queries:[],current:"",_init:function _init(){var e=this;u()("meta.foundation-mq").length||u()('<meta class="foundation-mq">').appendTo(document.head);var t,n=u()(".foundation-mq").css("font-family");t=r(n);for(var i in t){t.hasOwnProperty(i)&&e.queries.push({name:i,value:"only screen and (min-width: "+t[i]+")"});}this.current=this._getCurrentSize(),this._watcher();},atLeast:function atLeast(e){var t=this.get(e);return!!t&&o(t).matches;},is:function is(e){return e=e.trim().split(" "),e.length>1&&"only"===e[1]?e[0]===this._getCurrentSize():this.atLeast(e[0]);},get:function get(e){for(var t in this.queries){if(this.queries.hasOwnProperty(t)){var n=this.queries[t];if(e===n.name)return n.value;}}return null;},_getCurrentSize:function _getCurrentSize(){for(var e,t=0;t<this.queries.length;t++){var n=this.queries[t];o(n.value).matches&&(e=n);}return"object"==(typeof e==="undefined"?"undefined":_typeof(e))?e.name:e;},_watcher:function _watcher(){var e=this;u()(window).off("resize.zf.mediaquery").on("resize.zf.mediaquery",function(){var t=e._getCurrentSize(),n=e.current;t!==n&&(e.current=t,u()(window).trigger("changed.zf.mediaquery",[t,n]));});}};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilMotion=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=104);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/104:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(38);/***/},/***/3:/***/function _(module,exports){module.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};/***/},/***/38:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__=__webpack_require__(68);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Motion=__WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__["a"/* Motion */];__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Move=__WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__["b"/* Move */];/***/},/***/68:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"b",function(){return Move;});/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Motion;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_core__=__webpack_require__(3);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__foundation_util_core__);/**
 * Motion module.
 * @module foundation.motion
 */var initClasses=['mui-enter','mui-leave'];var activeClasses=['mui-enter-active','mui-leave-active'];var Motion={animateIn:function animateIn(element,animation,cb){animate(true,element,animation,cb);},animateOut:function animateOut(element,animation,cb){animate(false,element,animation,cb);}};function Move(duration,elem,fn){var anim,prog,start=null;// console.log('called');
if(duration===0){fn.apply(elem);elem.trigger('finished.zf.animate',[elem]).triggerHandler('finished.zf.animate',[elem]);return;}function move(ts){if(!start)start=ts;// console.log(start, ts);
prog=ts-start;fn.apply(elem);if(prog<duration){anim=window.requestAnimationFrame(move,elem);}else{window.cancelAnimationFrame(anim);elem.trigger('finished.zf.animate',[elem]).triggerHandler('finished.zf.animate',[elem]);}}anim=window.requestAnimationFrame(move);}/**
 * Animates an element in or out using a CSS transition class.
 * @function
 * @private
 * @param {Boolean} isIn - Defines if the animation is in or out.
 * @param {Object} element - jQuery or HTML object to animate.
 * @param {String} animation - CSS class to use.
 * @param {Function} cb - Callback to run when animation is finished.
 */function animate(isIn,element,animation,cb){element=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(element).eq(0);if(!element.length)return;var initClass=isIn?initClasses[0]:initClasses[1];var activeClass=isIn?activeClasses[0]:activeClasses[1];// Set up the animation
reset();element.addClass(animation).css('transition','none');requestAnimationFrame(function(){element.addClass(initClass);if(isIn)element.show();});// Start the animation
requestAnimationFrame(function(){element[0].offsetWidth;element.css('transition','').addClass(activeClass);});// Clean up the animation when it finishes
element.one(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1__foundation_util_core__["transitionend"])(element),finish);// Hides the element (for out animations), resets the element, and runs a callback
function finish(){if(!isIn)element.hide();reset();if(cb)cb.apply(element);}// Resets transitions and removes motion-specific classes
function reset(){element[0].style.transitionDuration=0;element.removeClass(initClass+' '+activeClass+' '+animation);}}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilMotionMin=mod.exports;}})(this,function(){"use strict";!function(n){function t(e){if(i[e])return i[e].exports;var o=i[e]={i:e,l:!1,exports:{}};return n[e].call(o.exports,o,o.exports,t),o.l=!0,o.exports;}var i={};t.m=n,t.c=i,t.i=function(n){return n;},t.d=function(n,i,e){t.o(n,i)||Object.defineProperty(n,i,{configurable:!1,enumerable:!0,get:e});},t.n=function(n){var i=n&&n.__esModule?function(){return n.default;}:function(){return n;};return t.d(i,"a",i),i;},t.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t);},t.p="",t(t.s=104);}({0:function _(n,t){n.exports=jQuery;},1:function _(n,t){n.exports={Foundation:window.Foundation};},104:function _(n,t,i){n.exports=i(38);},3:function _(n,t){n.exports={rtl:window.Foundation.rtl,GetYoDigits:window.Foundation.GetYoDigits,transitionend:window.Foundation.transitionend};},38:function _(n,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var e=i(1),o=(i.n(e),i(68));e.Foundation.Motion=o.a,e.Foundation.Move=o.b;},68:function _(n,t,i){"use strict";function e(n,t,i){function e(u){a||(a=u),r=u-a,i.apply(t),r<n?o=window.requestAnimationFrame(e,t):(window.cancelAnimationFrame(o),t.trigger("finished.zf.animate",[t]).triggerHandler("finished.zf.animate",[t]));}var o,r,a=null;if(0===n)return i.apply(t),void t.trigger("finished.zf.animate",[t]).triggerHandler("finished.zf.animate",[t]);o=window.requestAnimationFrame(e);}function o(n,t,e,o){function r(){n||t.hide(),d(),o&&o.apply(t);}function d(){t[0].style.transitionDuration=0,t.removeClass(c+" "+l+" "+e);}if(t=a()(t).eq(0),t.length){var c=n?s[0]:s[1],l=n?f[0]:f[1];d(),t.addClass(e).css("transition","none"),requestAnimationFrame(function(){t.addClass(c),n&&t.show();}),requestAnimationFrame(function(){t[0].offsetWidth,t.css("transition","").addClass(l);}),t.one(i.i(u.transitionend)(t),r);}}i.d(t,"b",function(){return e;}),i.d(t,"a",function(){return d;});var r=i(0),a=i.n(r),u=i(3),s=(i.n(u),["mui-enter","mui-leave"]),f=["mui-enter-active","mui-leave-active"],d={animateIn:function animateIn(n,t,i){o(!0,n,t,i);},animateOut:function animateOut(n,t,i){o(!1,n,t,i);}};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilNest=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=105);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/105:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(39);/***/},/***/39:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_nest__=__webpack_require__(69);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Nest=__WEBPACK_IMPORTED_MODULE_1__foundation_util_nest__["a"/* Nest */];/***/},/***/69:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Nest;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);var Nest={Feather:function Feather(menu){var type=arguments.length>1&&arguments[1]!==undefined?arguments[1]:'zf';menu.attr('role','menubar');var items=menu.find('li').attr({'role':'menuitem'}),subMenuClass='is-'+type+'-submenu',subItemClass=subMenuClass+'-item',hasSubClass='is-'+type+'-submenu-parent',applyAria=type!=='accordion';// Accordions handle their own ARIA attriutes.
items.each(function(){var $item=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),$sub=$item.children('ul');if($sub.length){$item.addClass(hasSubClass);$sub.addClass('submenu '+subMenuClass).attr({'data-submenu':''});if(applyAria){$item.attr({'aria-haspopup':true,'aria-label':$item.children('a:first').text()});// Note:  Drilldowns behave differently in how they hide, and so need
// additional attributes.  We should look if this possibly over-generalized
// utility (Nest) is appropriate when we rework menus in 6.4
if(type==='drilldown'){$item.attr({'aria-expanded':false});}}$sub.addClass('submenu '+subMenuClass).attr({'data-submenu':'','role':'menu'});if(type==='drilldown'){$sub.attr({'aria-hidden':true});}}if($item.parent('[data-submenu]').length){$item.addClass('is-submenu-item '+subItemClass);}});return;},Burn:function Burn(menu,type){var//items = menu.find('li'),
subMenuClass='is-'+type+'-submenu',subItemClass=subMenuClass+'-item',hasSubClass='is-'+type+'-submenu-parent';menu.find('>li, .menu, .menu > li').removeClass(subMenuClass+' '+subItemClass+' '+hasSubClass+' is-submenu-item submenu is-active').removeAttr('data-submenu').css('display','');}};/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilNestMin=mod.exports;}})(this,function(){"use strict";!function(n){function e(r){if(t[r])return t[r].exports;var u=t[r]={i:r,l:!1,exports:{}};return n[r].call(u.exports,u,u.exports,e),u.l=!0,u.exports;}var t={};e.m=n,e.c=t,e.i=function(n){return n;},e.d=function(n,t,r){e.o(n,t)||Object.defineProperty(n,t,{configurable:!1,enumerable:!0,get:r});},e.n=function(n){var t=n&&n.__esModule?function(){return n.default;}:function(){return n;};return e.d(t,"a",t),t;},e.o=function(n,e){return Object.prototype.hasOwnProperty.call(n,e);},e.p="",e(e.s=105);}({0:function _(n,e){n.exports=jQuery;},1:function _(n,e){n.exports={Foundation:window.Foundation};},105:function _(n,e,t){n.exports=t(39);},39:function _(n,e,t){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=t(1),u=(t.n(r),t(69));r.Foundation.Nest=u.a;},69:function _(n,e,t){"use strict";t.d(e,"a",function(){return a;});var r=t(0),u=t.n(r),a={Feather:function Feather(n){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"zf";n.attr("role","menubar");var t=n.find("li").attr({role:"menuitem"}),r="is-"+e+"-submenu",a=r+"-item",i="is-"+e+"-submenu-parent",o="accordion"!==e;t.each(function(){var n=u()(this),t=n.children("ul");t.length&&(n.addClass(i),t.addClass("submenu "+r).attr({"data-submenu":""}),o&&(n.attr({"aria-haspopup":!0,"aria-label":n.children("a:first").text()}),"drilldown"===e&&n.attr({"aria-expanded":!1})),t.addClass("submenu "+r).attr({"data-submenu":"",role:"menu"}),"drilldown"===e&&t.attr({"aria-hidden":!0})),n.parent("[data-submenu]").length&&n.addClass("is-submenu-item "+a);});},Burn:function Burn(n,e){var t="is-"+e+"-submenu",r=t+"-item",u="is-"+e+"-submenu-parent";n.find(">li, .menu, .menu > li").removeClass(t+" "+r+" "+u+" is-submenu-item submenu is-active").removeAttr("data-submenu").css("display","");}};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTimer=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=106);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/106:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(40);/***/},/***/40:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_timer__=__webpack_require__(70);__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"].Timer=__WEBPACK_IMPORTED_MODULE_1__foundation_util_timer__["a"/* Timer */];/***/},/***/70:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Timer;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);function Timer(elem,options,cb){var _this=this,duration=options.duration,//options is an object for easily adding features later.
nameSpace=Object.keys(elem.data())[0]||'timer',remain=-1,start,timer;this.isPaused=false;this.restart=function(){remain=-1;clearTimeout(timer);this.start();};this.start=function(){this.isPaused=false;// if(!elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
clearTimeout(timer);remain=remain<=0?duration:remain;elem.data('paused',false);start=Date.now();timer=setTimeout(function(){if(options.infinite){_this.restart();//rerun the timer.
}if(cb&&typeof cb==='function'){cb();}},remain);elem.trigger('timerstart.zf.'+nameSpace);};this.pause=function(){this.isPaused=true;//if(elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
clearTimeout(timer);elem.data('paused',true);var end=Date.now();remain=remain-(end-start);elem.trigger('timerpaused.zf.'+nameSpace);};}/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTimerMin=mod.exports;}})(this,function(){"use strict";!function(t){function e(r){if(n[r])return n[r].exports;var i=n[r]={i:r,l:!1,exports:{}};return t[r].call(i.exports,i,i.exports,e),i.l=!0,i.exports;}var n={};e.m=t,e.c=n,e.i=function(t){return t;},e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r});},e.n=function(t){var n=t&&t.__esModule?function(){return t.default;}:function(){return t;};return e.d(n,"a",n),n;},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e);},e.p="",e(e.s=106);}({0:function _(t,e){t.exports=jQuery;},1:function _(t,e){t.exports={Foundation:window.Foundation};},106:function _(t,e,n){t.exports=n(40);},40:function _(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n(1),i=(n.n(r),n(70));r.Foundation.Timer=i.a;},70:function _(t,e,n){"use strict";function r(t,e,n){var r,i,o=this,u=e.duration,a=Object.keys(t.data())[0]||"timer",s=-1;this.isPaused=!1,this.restart=function(){s=-1,clearTimeout(i),this.start();},this.start=function(){this.isPaused=!1,clearTimeout(i),s=s<=0?u:s,t.data("paused",!1),r=Date.now(),i=setTimeout(function(){e.infinite&&o.restart(),n&&"function"==typeof n&&n();},s),t.trigger("timerstart.zf."+a);},this.pause=function(){this.isPaused=!0,clearTimeout(i),t.data("paused",!0);var e=Date.now();s-=e-r,t.trigger("timerpaused.zf."+a);};}n.d(e,"a",function(){return r;});var i=n(0);n.n(i);}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTimerAndImageLoader=mod.exports;}})(this,function(){'use strict';!function($){function Timer(elem,options,cb){var _this=this,duration=options.duration,//options is an object for easily adding features later.
nameSpace=Object.keys(elem.data())[0]||'timer',remain=-1,start,timer;this.isPaused=false;this.restart=function(){remain=-1;clearTimeout(timer);this.start();};this.start=function(){this.isPaused=false;// if(!elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
clearTimeout(timer);remain=remain<=0?duration:remain;elem.data('paused',false);start=Date.now();timer=setTimeout(function(){if(options.infinite){_this.restart();//rerun the timer.
}if(cb&&typeof cb==='function'){cb();}},remain);elem.trigger('timerstart.zf.'+nameSpace);};this.pause=function(){this.isPaused=true;//if(elem.data('paused')){ return false; }//maybe implement this sanity check if used for other things.
clearTimeout(timer);elem.data('paused',true);var end=Date.now();remain=remain-(end-start);elem.trigger('timerpaused.zf.'+nameSpace);};}/**
   * Runs a callback function when images are fully loaded.
   * @param {Object} images - Image(s) to check if loaded.
   * @param {Func} callback - Function to execute when image is fully loaded.
   */function onImagesLoaded(images,callback){var self=this,unloaded=images.length;if(unloaded===0){callback();}images.each(function(){// Check if image is loaded
if(this.complete||this.readyState===4||this.readyState==='complete'){singleImageLoaded();}// Force load the image
else{// fix for IE. See https://css-tricks.com/snippets/jquery/fixing-load-in-ie-for-cached-images/
var src=$(this).attr('src');$(this).attr('src',src+(src.indexOf('?')>=0?'&':'?')+new Date().getTime());$(this).one('load',function(){singleImageLoaded();});}});function singleImageLoaded(){unloaded--;if(unloaded===0){callback();}}}Foundation.Timer=Timer;Foundation.onImagesLoaded=onImagesLoaded;}(jQuery);});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTimerAndImageLoaderMin=mod.exports;}})(this,function(){"use strict";!function(t){function e(t,e,i){var a,s,n=this,r=e.duration,o=Object.keys(t.data())[0]||"timer",u=-1;this.isPaused=!1,this.restart=function(){u=-1,clearTimeout(s),this.start();},this.start=function(){this.isPaused=!1,clearTimeout(s),u=u<=0?r:u,t.data("paused",!1),a=Date.now(),s=setTimeout(function(){e.infinite&&n.restart(),i&&"function"==typeof i&&i();},u),t.trigger("timerstart.zf."+o);},this.pause=function(){this.isPaused=!0,clearTimeout(s),t.data("paused",!0);var e=Date.now();u-=e-a,t.trigger("timerpaused.zf."+o);};}function i(e,i){function a(){s--,0===s&&i();}var s=e.length;0===s&&i(),e.each(function(){if(this.complete||4===this.readyState||"complete"===this.readyState)a();else{var e=t(this).attr("src");t(this).attr("src",e+(e.indexOf("?")>=0?"&":"?")+new Date().getTime()),t(this).one("load",function(){a();});}});}Foundation.Timer=e,Foundation.onImagesLoaded=i;}(jQuery);});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTouch=mod.exports;}})(this,function(){'use strict';/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=107);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/107:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(41);/***/},/***/41:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_touch__=__webpack_require__(71);__WEBPACK_IMPORTED_MODULE_1__foundation_util_touch__["a"/* Touch */].init(__WEBPACK_IMPORTED_MODULE_0_jquery___default.a);window.Foundation.Touch=__WEBPACK_IMPORTED_MODULE_1__foundation_util_touch__["a"/* Touch */];/***/},/***/71:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Touch;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}//**************************************************
//**Work inspired by multiple jquery swipe plugins**
//**Done by Yohai Ararat ***************************
//**************************************************
var Touch={};var startPosX,startPosY,startTime,elapsedTime,isMoving=false;function onTouchEnd(){//  alert(this);
this.removeEventListener('touchmove',onTouchMove);this.removeEventListener('touchend',onTouchEnd);isMoving=false;}function onTouchMove(e){if(__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.spotSwipe.preventDefault){e.preventDefault();}if(isMoving){var x=e.touches[0].pageX;var y=e.touches[0].pageY;var dx=startPosX-x;var dy=startPosY-y;var dir;elapsedTime=new Date().getTime()-startTime;if(Math.abs(dx)>=__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.spotSwipe.moveThreshold&&elapsedTime<=__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.spotSwipe.timeThreshold){dir=dx>0?'left':'right';}// else if(Math.abs(dy) >= $.spotSwipe.moveThreshold && elapsedTime <= $.spotSwipe.timeThreshold) {
//   dir = dy > 0 ? 'down' : 'up';
// }
if(dir){e.preventDefault();onTouchEnd.call(this);__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).trigger('swipe',dir).trigger('swipe'+dir);}}}function onTouchStart(e){if(e.touches.length==1){startPosX=e.touches[0].pageX;startPosY=e.touches[0].pageY;isMoving=true;startTime=new Date().getTime();this.addEventListener('touchmove',onTouchMove,false);this.addEventListener('touchend',onTouchEnd,false);}}function init(){this.addEventListener&&this.addEventListener('touchstart',onTouchStart,false);}function teardown(){this.removeEventListener('touchstart',onTouchStart);}var SpotSwipe=function(){function SpotSwipe($){_classCallCheck(this,SpotSwipe);this.version='1.0.0';this.enabled='ontouchstart'in document.documentElement;this.preventDefault=false;this.moveThreshold=75;this.timeThreshold=200;this.$=$;this._init();}_createClass(SpotSwipe,[{key:'_init',value:function _init(){var $=this.$;$.event.special.swipe={setup:init};$.each(['left','up','down','right'],function(){$.event.special['swipe'+this]={setup:function setup(){$(this).on('swipe',$.noop);}};});}}]);return SpotSwipe;}();/****************************************************
 * As far as I can tell, both setupSpotSwipe and    *
 * setupTouchHandler should be idempotent,          *
 * because they directly replace functions &        *
 * values, and do not add event handlers directly.  *
 ****************************************************/Touch.setupSpotSwipe=function($){$.spotSwipe=new SpotSwipe($);};/****************************************************
 * Method for adding pseudo drag events to elements *
 ***************************************************/Touch.setupTouchHandler=function($){$.fn.addTouch=function(){this.each(function(i,el){$(el).bind('touchstart touchmove touchend touchcancel',function(){//we pass the original event object because the jQuery event
//object is normalized to w3c specs and does not provide the TouchList
handleTouch(event);});});var handleTouch=function handleTouch(event){var touches=event.changedTouches,first=touches[0],eventTypes={touchstart:'mousedown',touchmove:'mousemove',touchend:'mouseup'},type=eventTypes[event.type],simulatedEvent;if('MouseEvent'in window&&typeof window.MouseEvent==='function'){simulatedEvent=new window.MouseEvent(type,{'bubbles':true,'cancelable':true,'screenX':first.screenX,'screenY':first.screenY,'clientX':first.clientX,'clientY':first.clientY});}else{simulatedEvent=document.createEvent('MouseEvent');simulatedEvent.initMouseEvent(type,true,true,window,1,first.screenX,first.screenY,first.clientX,first.clientY,false,false,false,false,0/*left*/,null);}first.target.dispatchEvent(simulatedEvent);};};};Touch.init=function($){if(typeof $.spotSwipe==='undefined'){Touch.setupSpotSwipe($);Touch.setupTouchHandler($);}};/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTouchMin=mod.exports;}})(this,function(){"use strict";!function(e){function t(o){if(n[o])return n[o].exports;var i=n[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,t),i.l=!0,i.exports;}var n={};t.m=e,t.c=n,t.i=function(e){return e;},t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:o});},t.n=function(e){var n=e&&e.__esModule?function(){return e.default;}:function(){return e;};return t.d(n,"a",n),n;},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t);},t.p="",t(t.s=107);}({0:function _(e,t){e.exports=jQuery;},107:function _(e,t,n){e.exports=n(41);},41:function _(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=n(0),i=n.n(o),u=n(71);u.a.init(i.a),window.Foundation.Touch=u.a;},71:function _(e,t,n){"use strict";function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function");}function i(){this.removeEventListener("touchmove",u),this.removeEventListener("touchend",i),w=!1;}function u(e){if(l.a.spotSwipe.preventDefault&&e.preventDefault(),w){var t,n=e.touches[0].pageX,o=(e.touches[0].pageY,s-n);p=new Date().getTime()-h,Math.abs(o)>=l.a.spotSwipe.moveThreshold&&p<=l.a.spotSwipe.timeThreshold&&(t=o>0?"left":"right"),t&&(e.preventDefault(),i.call(this),l()(this).trigger("swipe",t).trigger("swipe"+t));}}function r(e){1==e.touches.length&&(s=e.touches[0].pageX,a=e.touches[0].pageY,w=!0,h=new Date().getTime(),this.addEventListener("touchmove",u,!1),this.addEventListener("touchend",i,!1));}function c(){this.addEventListener&&this.addEventListener("touchstart",r,!1);}n.d(t,"a",function(){return v;});var s,a,h,p,f=n(0),l=n.n(f),d=function(){function e(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o);}}return function(t,n,o){return n&&e(t.prototype,n),o&&e(t,o),t;};}(),v={},w=!1,m=function(){function e(t){o(this,e),this.version="1.0.0",this.enabled="ontouchstart"in document.documentElement,this.preventDefault=!1,this.moveThreshold=75,this.timeThreshold=200,this.$=t,this._init();}return d(e,[{key:"_init",value:function value(){var e=this.$;e.event.special.swipe={setup:c},e.each(["left","up","down","right"],function(){e.event.special["swipe"+this]={setup:function setup(){e(this).on("swipe",e.noop);}};});}}]),e;}();v.setupSpotSwipe=function(e){e.spotSwipe=new m(e);},v.setupTouchHandler=function(e){e.fn.addTouch=function(){this.each(function(n,o){e(o).bind("touchstart touchmove touchend touchcancel",function(){t(event);});});var t=function t(e){var t,n=e.changedTouches,o=n[0],i={touchstart:"mousedown",touchmove:"mousemove",touchend:"mouseup"},u=i[e.type];"MouseEvent"in window&&"function"==typeof window.MouseEvent?t=new window.MouseEvent(u,{bubbles:!0,cancelable:!0,screenX:o.screenX,screenY:o.screenY,clientX:o.clientX,clientY:o.clientY}):(t=document.createEvent("MouseEvent"),t.initMouseEvent(u,!0,!0,window,1,o.screenX,o.screenY,o.clientX,o.clientY,!1,!1,!1,!1,0,null)),o.target.dispatchEvent(t);};};},v.init=function(e){void 0===e.spotSwipe&&(v.setupSpotSwipe(e),v.setupTouchHandler(e));};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTriggers=mod.exports;}})(this,function(){'use strict';var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj;}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj;};/******/(function(modules){// webpackBootstrap
/******/// The module cache
/******/var installedModules={};/******//******/// The require function
/******/function __webpack_require__(moduleId){/******//******/// Check if module is in cache
/******/if(installedModules[moduleId]){/******/return installedModules[moduleId].exports;/******/}/******/// Create a new module (and put it into the cache)
/******/var module=installedModules[moduleId]={/******/i:moduleId,/******/l:false,/******/exports:{}/******/};/******//******/// Execute the module function
/******/modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);/******//******/// Flag the module as loaded
/******/module.l=true;/******//******/// Return the exports of the module
/******/return module.exports;/******/}/******//******//******/// expose the modules object (__webpack_modules__)
/******/__webpack_require__.m=modules;/******//******/// expose the module cache
/******/__webpack_require__.c=installedModules;/******//******/// identity function for calling harmony imports with the correct context
/******/__webpack_require__.i=function(value){return value;};/******//******/// define getter function for harmony exports
/******/__webpack_require__.d=function(exports,name,getter){/******/if(!__webpack_require__.o(exports,name)){/******/Object.defineProperty(exports,name,{/******/configurable:false,/******/enumerable:true,/******/get:getter/******/});/******/}/******/};/******//******/// getDefaultExport function for compatibility with non-harmony modules
/******/__webpack_require__.n=function(module){/******/var getter=module&&module.__esModule?/******/function getDefault(){return module['default'];}:/******/function getModuleExports(){return module;};/******/__webpack_require__.d(getter,'a',getter);/******/return getter;/******/};/******//******/// Object.prototype.hasOwnProperty.call
/******/__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property);};/******//******/// __webpack_public_path__
/******/__webpack_require__.p="";/******//******/// Load entry module and return exports
/******/return __webpack_require__(__webpack_require__.s=108);/******/})(/************************************************************************//******/{/***/0:/***/function _(module,exports){module.exports=jQuery;/***/},/***/1:/***/function _(module,exports){module.exports={Foundation:window.Foundation};/***/},/***/108:/***/function _(module,exports,__webpack_require__){module.exports=__webpack_require__(42);/***/},/***/4:/***/function _(module,exports){module.exports={Motion:window.Foundation.Motion,Move:window.Foundation.Move};/***/},/***/42:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";Object.defineProperty(__webpack_exports__,"__esModule",{value:true});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core__=__webpack_require__(1);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0__foundation_core___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__foundation_core__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_jquery__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_2__foundation_util_triggers__=__webpack_require__(7);__WEBPACK_IMPORTED_MODULE_2__foundation_util_triggers__["a"/* Triggers */].init(__WEBPACK_IMPORTED_MODULE_1_jquery___default.a,__WEBPACK_IMPORTED_MODULE_0__foundation_core__["Foundation"]);/***/},/***/7:/***/function _(module,__webpack_exports__,__webpack_require__){"use strict";/* harmony export (binding) */__webpack_require__.d(__webpack_exports__,"a",function(){return Triggers;});/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery__=__webpack_require__(0);/* harmony import */var __WEBPACK_IMPORTED_MODULE_0_jquery___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__=__webpack_require__(4);/* harmony import */var __WEBPACK_IMPORTED_MODULE_1__foundation_util_motion___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__);var MutationObserver=function(){var prefixes=['WebKit','Moz','O','Ms',''];for(var i=0;i<prefixes.length;i++){if(prefixes[i]+'MutationObserver'in window){return window[prefixes[i]+'MutationObserver'];}}return false;}();var triggers=function triggers(el,type){el.data(type).split(' ').forEach(function(id){__WEBPACK_IMPORTED_MODULE_0_jquery___default()('#'+id)[type==='close'?'trigger':'triggerHandler'](type+'.zf.trigger',[el]);});};var Triggers={Listeners:{Basic:{},Global:{}},Initializers:{}};Triggers.Listeners.Basic={openListener:function openListener(){triggers(__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),'open');},closeListener:function closeListener(){var id=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).data('close');if(id){triggers(__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),'close');}else{__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).trigger('close.zf.trigger');}},toggleListener:function toggleListener(){var id=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).data('toggle');if(id){triggers(__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),'toggle');}else{__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).trigger('toggle.zf.trigger');}},closeableListener:function closeableListener(e){e.stopPropagation();var animation=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).data('closable');if(animation!==''){__WEBPACK_IMPORTED_MODULE_1__foundation_util_motion__["Motion"].animateOut(__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),animation,function(){__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).trigger('closed.zf');});}else{__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).fadeOut().trigger('closed.zf');}},toggleFocusListener:function toggleFocusListener(){var id=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).data('toggle-focus');__WEBPACK_IMPORTED_MODULE_0_jquery___default()('#'+id).triggerHandler('toggle.zf.trigger',[__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this)]);}};// Elements with [data-open] will reveal a plugin that supports it when clicked.
Triggers.Initializers.addOpenListener=function($elem){$elem.off('click.zf.trigger',Triggers.Listeners.Basic.openListener);$elem.on('click.zf.trigger','[data-open]',Triggers.Listeners.Basic.openListener);};// Elements with [data-close] will close a plugin that supports it when clicked.
// If used without a value on [data-close], the event will bubble, allowing it to close a parent component.
Triggers.Initializers.addCloseListener=function($elem){$elem.off('click.zf.trigger',Triggers.Listeners.Basic.closeListener);$elem.on('click.zf.trigger','[data-close]',Triggers.Listeners.Basic.closeListener);};// Elements with [data-toggle] will toggle a plugin that supports it when clicked.
Triggers.Initializers.addToggleListener=function($elem){$elem.off('click.zf.trigger',Triggers.Listeners.Basic.toggleListener);$elem.on('click.zf.trigger','[data-toggle]',Triggers.Listeners.Basic.toggleListener);};// Elements with [data-closable] will respond to close.zf.trigger events.
Triggers.Initializers.addCloseableListener=function($elem){$elem.off('close.zf.trigger',Triggers.Listeners.Basic.closeableListener);$elem.on('close.zf.trigger','[data-closeable], [data-closable]',Triggers.Listeners.Basic.closeableListener);};// Elements with [data-toggle-focus] will respond to coming in and out of focus
Triggers.Initializers.addToggleFocusListener=function($elem){$elem.off('focus.zf.trigger blur.zf.trigger',Triggers.Listeners.Basic.toggleFocusListener);$elem.on('focus.zf.trigger blur.zf.trigger','[data-toggle-focus]',Triggers.Listeners.Basic.toggleFocusListener);};// More Global/complex listeners and triggers
Triggers.Listeners.Global={resizeListener:function resizeListener($nodes){if(!MutationObserver){//fallback for IE 9
$nodes.each(function(){__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).triggerHandler('resizeme.zf.trigger');});}//trigger all listening elements and signal a resize event
$nodes.attr('data-events',"resize");},scrollListener:function scrollListener($nodes){if(!MutationObserver){//fallback for IE 9
$nodes.each(function(){__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this).triggerHandler('scrollme.zf.trigger');});}//trigger all listening elements and signal a scroll event
$nodes.attr('data-events',"scroll");},closeMeListener:function closeMeListener(e,pluginId){var plugin=e.namespace.split('.')[0];var plugins=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('[data-'+plugin+']').not('[data-yeti-box="'+pluginId+'"]');plugins.each(function(){var _this=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(this);_this.triggerHandler('close.zf.trigger',[_this]);});}// Global, parses whole document.
};Triggers.Initializers.addClosemeListener=function(pluginName){var yetiBoxes=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('[data-yeti-box]'),plugNames=['dropdown','tooltip','reveal'];if(pluginName){if(typeof pluginName==='string'){plugNames.push(pluginName);}else if((typeof pluginName==='undefined'?'undefined':_typeof(pluginName))==='object'&&typeof pluginName[0]==='string'){plugNames.concat(pluginName);}else{console.error('Plugin names must be strings');}}if(yetiBoxes.length){var listeners=plugNames.map(function(name){return'closeme.zf.'+name;}).join(' ');__WEBPACK_IMPORTED_MODULE_0_jquery___default()(window).off(listeners).on(listeners,Triggers.Listeners.Global.closeMeListener);}};function debounceGlobalListener(debounce,trigger,listener){var timer=void 0,args=Array.prototype.slice.call(arguments,3);__WEBPACK_IMPORTED_MODULE_0_jquery___default()(window).off(trigger).on(trigger,function(e){if(timer){clearTimeout(timer);}timer=setTimeout(function(){listener.apply(null,args);},debounce||10);//default time to emit scroll event
});}Triggers.Initializers.addResizeListener=function(debounce){var $nodes=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('[data-resize]');if($nodes.length){debounceGlobalListener(debounce,'resize.zf.trigger',Triggers.Listeners.Global.resizeListener,$nodes);}};Triggers.Initializers.addScrollListener=function(debounce){var $nodes=__WEBPACK_IMPORTED_MODULE_0_jquery___default()('[data-scroll]');if($nodes.length){debounceGlobalListener(debounce,'scroll.zf.trigger',Triggers.Listeners.Global.scrollListener,$nodes);}};Triggers.Initializers.addMutationEventsListener=function($elem){if(!MutationObserver){return false;}var $nodes=$elem.find('[data-resize], [data-scroll], [data-mutate]');//element callback
var listeningElementsMutation=function listeningElementsMutation(mutationRecordsList){var $target=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(mutationRecordsList[0].target);//trigger the event handler for the element depending on type
switch(mutationRecordsList[0].type){case"attributes":if($target.attr("data-events")==="scroll"&&mutationRecordsList[0].attributeName==="data-events"){$target.triggerHandler('scrollme.zf.trigger',[$target,window.pageYOffset]);}if($target.attr("data-events")==="resize"&&mutationRecordsList[0].attributeName==="data-events"){$target.triggerHandler('resizeme.zf.trigger',[$target]);}if(mutationRecordsList[0].attributeName==="style"){$target.closest("[data-mutate]").attr("data-events","mutate");$target.closest("[data-mutate]").triggerHandler('mutateme.zf.trigger',[$target.closest("[data-mutate]")]);}break;case"childList":$target.closest("[data-mutate]").attr("data-events","mutate");$target.closest("[data-mutate]").triggerHandler('mutateme.zf.trigger',[$target.closest("[data-mutate]")]);break;default:return false;//nothing
}};if($nodes.length){//for each element that needs to listen for resizing, scrolling, or mutation add a single observer
for(var i=0;i<=$nodes.length-1;i++){var elementObserver=new MutationObserver(listeningElementsMutation);elementObserver.observe($nodes[i],{attributes:true,childList:true,characterData:false,subtree:true,attributeFilter:["data-events","style"]});}}};Triggers.Initializers.addSimpleListeners=function(){var $document=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(document);Triggers.Initializers.addOpenListener($document);Triggers.Initializers.addCloseListener($document);Triggers.Initializers.addToggleListener($document);Triggers.Initializers.addCloseableListener($document);Triggers.Initializers.addToggleFocusListener($document);};Triggers.Initializers.addGlobalListeners=function(){var $document=__WEBPACK_IMPORTED_MODULE_0_jquery___default()(document);Triggers.Initializers.addMutationEventsListener($document);Triggers.Initializers.addResizeListener();Triggers.Initializers.addScrollListener();Triggers.Initializers.addClosemeListener();};Triggers.init=function($,Foundation){if(typeof $.triggersInitialized==='undefined'){var $document=$(document);if(document.readyState==="complete"){Triggers.Initializers.addSimpleListeners();Triggers.Initializers.addGlobalListeners();}else{$(window).on('load',function(){Triggers.Initializers.addSimpleListeners();Triggers.Initializers.addGlobalListeners();});}$.triggersInitialized=true;}if(Foundation){Foundation.Triggers=Triggers;// Legacy included to be backwards compatible for now.
Foundation.IHearYou=Triggers.Initializers.addGlobalListeners;}};/***/}/******/});});
(function(global,factory){if(typeof define==="function"&&define.amd){define([],factory);}else if(typeof exports!=="undefined"){factory();}else{var mod={exports:{}};factory();global.foundationUtilTriggersMin=mod.exports;}})(this,function(){"use strict";var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj;}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj;};!function(e){function t(r){if(i[r])return i[r].exports;var n=i[r]={i:r,l:!1,exports:{}};return e[r].call(n.exports,n,n.exports,t),n.l=!0,n.exports;}var i={};t.m=e,t.c=i,t.i=function(e){return e;},t.d=function(e,i,r){t.o(e,i)||Object.defineProperty(e,i,{configurable:!1,enumerable:!0,get:r});},t.n=function(e){var i=e&&e.__esModule?function(){return e.default;}:function(){return e;};return t.d(i,"a",i),i;},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t);},t.p="",t(t.s=108);}({0:function _(e,t){e.exports=jQuery;},1:function _(e,t){e.exports={Foundation:window.Foundation};},108:function _(e,t,i){e.exports=i(42);},4:function _(e,t){e.exports={Motion:window.Foundation.Motion,Move:window.Foundation.Move};},42:function _(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=i(1),n=(i.n(r),i(0)),s=i.n(n);i(7).a.init(s.a,r.Foundation);},7:function _(e,t,i){"use strict";function r(e,t,i){var r=void 0,n=Array.prototype.slice.call(arguments,3);s()(window).off(t).on(t,function(t){r&&clearTimeout(r),r=setTimeout(function(){i.apply(null,n);},e||10);});}i.d(t,"a",function(){return c;});var n=i(0),s=i.n(n),a=i(4),o=(i.n(a),function(){for(var e=["WebKit","Moz","O","Ms",""],t=0;t<e.length;t++){if(e[t]+"MutationObserver"in window)return window[e[t]+"MutationObserver"];}return!1;}()),l=function l(e,t){e.data(t).split(" ").forEach(function(i){s()("#"+i)["close"===t?"trigger":"triggerHandler"](t+".zf.trigger",[e]);});},c={Listeners:{Basic:{},Global:{}},Initializers:{}};c.Listeners.Basic={openListener:function openListener(){l(s()(this),"open");},closeListener:function closeListener(){s()(this).data("close")?l(s()(this),"close"):s()(this).trigger("close.zf.trigger");},toggleListener:function toggleListener(){s()(this).data("toggle")?l(s()(this),"toggle"):s()(this).trigger("toggle.zf.trigger");},closeableListener:function closeableListener(e){e.stopPropagation();var t=s()(this).data("closable");""!==t?a.Motion.animateOut(s()(this),t,function(){s()(this).trigger("closed.zf");}):s()(this).fadeOut().trigger("closed.zf");},toggleFocusListener:function toggleFocusListener(){var e=s()(this).data("toggle-focus");s()("#"+e).triggerHandler("toggle.zf.trigger",[s()(this)]);}},c.Initializers.addOpenListener=function(e){e.off("click.zf.trigger",c.Listeners.Basic.openListener),e.on("click.zf.trigger","[data-open]",c.Listeners.Basic.openListener);},c.Initializers.addCloseListener=function(e){e.off("click.zf.trigger",c.Listeners.Basic.closeListener),e.on("click.zf.trigger","[data-close]",c.Listeners.Basic.closeListener);},c.Initializers.addToggleListener=function(e){e.off("click.zf.trigger",c.Listeners.Basic.toggleListener),e.on("click.zf.trigger","[data-toggle]",c.Listeners.Basic.toggleListener);},c.Initializers.addCloseableListener=function(e){e.off("close.zf.trigger",c.Listeners.Basic.closeableListener),e.on("close.zf.trigger","[data-closeable], [data-closable]",c.Listeners.Basic.closeableListener);},c.Initializers.addToggleFocusListener=function(e){e.off("focus.zf.trigger blur.zf.trigger",c.Listeners.Basic.toggleFocusListener),e.on("focus.zf.trigger blur.zf.trigger","[data-toggle-focus]",c.Listeners.Basic.toggleFocusListener);},c.Listeners.Global={resizeListener:function resizeListener(e){o||e.each(function(){s()(this).triggerHandler("resizeme.zf.trigger");}),e.attr("data-events","resize");},scrollListener:function scrollListener(e){o||e.each(function(){s()(this).triggerHandler("scrollme.zf.trigger");}),e.attr("data-events","scroll");},closeMeListener:function closeMeListener(e,t){var i=e.namespace.split(".")[0];s()("[data-"+i+"]").not('[data-yeti-box="'+t+'"]').each(function(){var e=s()(this);e.triggerHandler("close.zf.trigger",[e]);});}},c.Initializers.addClosemeListener=function(e){var t=s()("[data-yeti-box]"),i=["dropdown","tooltip","reveal"];if(e&&("string"==typeof e?i.push(e):"object"==(typeof e==="undefined"?"undefined":_typeof(e))&&"string"==typeof e[0]?i.concat(e):console.error("Plugin names must be strings")),t.length){var r=i.map(function(e){return"closeme.zf."+e;}).join(" ");s()(window).off(r).on(r,c.Listeners.Global.closeMeListener);}},c.Initializers.addResizeListener=function(e){var t=s()("[data-resize]");t.length&&r(e,"resize.zf.trigger",c.Listeners.Global.resizeListener,t);},c.Initializers.addScrollListener=function(e){var t=s()("[data-scroll]");t.length&&r(e,"scroll.zf.trigger",c.Listeners.Global.scrollListener,t);},c.Initializers.addMutationEventsListener=function(e){if(!o)return!1;var t=e.find("[data-resize], [data-scroll], [data-mutate]"),i=function i(e){var t=s()(e[0].target);switch(e[0].type){case"attributes":"scroll"===t.attr("data-events")&&"data-events"===e[0].attributeName&&t.triggerHandler("scrollme.zf.trigger",[t,window.pageYOffset]),"resize"===t.attr("data-events")&&"data-events"===e[0].attributeName&&t.triggerHandler("resizeme.zf.trigger",[t]),"style"===e[0].attributeName&&(t.closest("[data-mutate]").attr("data-events","mutate"),t.closest("[data-mutate]").triggerHandler("mutateme.zf.trigger",[t.closest("[data-mutate]")]));break;case"childList":t.closest("[data-mutate]").attr("data-events","mutate"),t.closest("[data-mutate]").triggerHandler("mutateme.zf.trigger",[t.closest("[data-mutate]")]);break;default:return!1;}};if(t.length)for(var r=0;r<=t.length-1;r++){var n=new o(i);n.observe(t[r],{attributes:!0,childList:!0,characterData:!1,subtree:!0,attributeFilter:["data-events","style"]});}},c.Initializers.addSimpleListeners=function(){var e=s()(document);c.Initializers.addOpenListener(e),c.Initializers.addCloseListener(e),c.Initializers.addToggleListener(e),c.Initializers.addCloseableListener(e),c.Initializers.addToggleFocusListener(e);},c.Initializers.addGlobalListeners=function(){var e=s()(document);c.Initializers.addMutationEventsListener(e),c.Initializers.addResizeListener(),c.Initializers.addScrollListener(),c.Initializers.addClosemeListener();},c.init=function(e,t){if(void 0===e.triggersInitialized){e(document);"complete"===document.readyState?(c.Initializers.addSimpleListeners(),c.Initializers.addGlobalListeners()):e(window).on("load",function(){c.Initializers.addSimpleListeners(),c.Initializers.addGlobalListeners();}),e.triggersInitialized=!0;}t&&(t.Triggers=c,t.IHearYou=c.Initializers.addGlobalListeners);};}});});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundation);global.foundationAbide=mod.exports;}})(this,function(exports,_jquery,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Abide=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Abide=function(_Plugin){_inherits(Abide,_Plugin);function Abide(){_classCallCheck(this,Abide);return _possibleConstructorReturn(this,(Abide.__proto__||Object.getPrototypeOf(Abide)).apply(this,arguments));}_createClass(Abide,[{key:'_setup',value:function _setup(element){var options=arguments.length>1&&arguments[1]!==undefined?arguments[1]:{};this.$element=element;this.options=_jquery2.default.extend(true,{},Abide.defaults,this.$element.data(),options);this.className='Abide';// ie9 back compat
this._init();}},{key:'_init',value:function _init(){this.$inputs=this.$element.find('input, textarea, select');this._events();}},{key:'_events',value:function _events(){var _this3=this;this.$element.off('.abide').on('reset.zf.abide',function(){_this3.resetForm();}).on('submit.zf.abide',function(){return _this3.validateForm();});if(this.options.validateOn==='fieldChange'){this.$inputs.off('change.zf.abide').on('change.zf.abide',function(e){_this3.validateInput((0,_jquery2.default)(e.target));});}if(this.options.liveValidate){this.$inputs.off('input.zf.abide').on('input.zf.abide',function(e){_this3.validateInput((0,_jquery2.default)(e.target));});}if(this.options.validateOnBlur){this.$inputs.off('blur.zf.abide').on('blur.zf.abide',function(e){_this3.validateInput((0,_jquery2.default)(e.target));});}}},{key:'_reflow',value:function _reflow(){this._init();}},{key:'requiredCheck',value:function requiredCheck($el){if(!$el.attr('required'))return true;var isGood=true;switch($el[0].type){case'checkbox':isGood=$el[0].checked;break;case'select':case'select-one':case'select-multiple':var opt=$el.find('option:selected');if(!opt.length||!opt.val())isGood=false;break;default:if(!$el.val()||!$el.val().length)isGood=false;}return isGood;}},{key:'findFormError',value:function findFormError($el){var id=$el[0].id;var $error=$el.siblings(this.options.formErrorSelector);if(!$error.length){$error=$el.parent().find(this.options.formErrorSelector);}$error=$error.add(this.$element.find('[data-form-error-for="'+id+'"]'));return $error;}},{key:'findLabel',value:function findLabel($el){var id=$el[0].id;var $label=this.$element.find('label[for="'+id+'"]');if(!$label.length){return $el.closest('label');}return $label;}},{key:'findRadioLabels',value:function findRadioLabels($els){var _this4=this;var labels=$els.map(function(i,el){var id=el.id;var $label=_this4.$element.find('label[for="'+id+'"]');if(!$label.length){$label=(0,_jquery2.default)(el).closest('label');}return $label[0];});return(0,_jquery2.default)(labels);}},{key:'addErrorClasses',value:function addErrorClasses($el){var $label=this.findLabel($el);var $formError=this.findFormError($el);if($label.length){$label.addClass(this.options.labelErrorClass);}if($formError.length){$formError.addClass(this.options.formErrorClass);}$el.addClass(this.options.inputErrorClass).attr('data-invalid','');}},{key:'removeRadioErrorClasses',value:function removeRadioErrorClasses(groupName){var $els=this.$element.find(':radio[name="'+groupName+'"]');var $labels=this.findRadioLabels($els);var $formErrors=this.findFormError($els);if($labels.length){$labels.removeClass(this.options.labelErrorClass);}if($formErrors.length){$formErrors.removeClass(this.options.formErrorClass);}$els.removeClass(this.options.inputErrorClass).removeAttr('data-invalid');}},{key:'removeErrorClasses',value:function removeErrorClasses($el){// radios need to clear all of the els
if($el[0].type=='radio'){return this.removeRadioErrorClasses($el.attr('name'));}var $label=this.findLabel($el);var $formError=this.findFormError($el);if($label.length){$label.removeClass(this.options.labelErrorClass);}if($formError.length){$formError.removeClass(this.options.formErrorClass);}$el.removeClass(this.options.inputErrorClass).removeAttr('data-invalid');}},{key:'validateInput',value:function validateInput($el){var clearRequire=this.requiredCheck($el),validated=false,customValidator=true,validator=$el.attr('data-validator'),equalTo=true;// don't validate ignored inputs or hidden inputs or disabled inputs
if($el.is('[data-abide-ignore]')||$el.is('[type="hidden"]')||$el.is('[disabled]')){return true;}switch($el[0].type){case'radio':validated=this.validateRadio($el.attr('name'));break;case'checkbox':validated=clearRequire;break;case'select':case'select-one':case'select-multiple':validated=clearRequire;break;default:validated=this.validateText($el);}if(validator){customValidator=this.matchValidation($el,validator,$el.attr('required'));}if($el.attr('data-equalto')){equalTo=this.options.validators.equalTo($el);}var goodToGo=[clearRequire,validated,customValidator,equalTo].indexOf(false)===-1;var message=(goodToGo?'valid':'invalid')+'.zf.abide';if(goodToGo){// Re-validate inputs that depend on this one with equalto
var dependentElements=this.$element.find('[data-equalto="'+$el.attr('id')+'"]');if(dependentElements.length){var _this=this;dependentElements.each(function(){if((0,_jquery2.default)(this).val()){_this.validateInput((0,_jquery2.default)(this));}});}}this[goodToGo?'removeErrorClasses':'addErrorClasses']($el);/**
     * Fires when the input is done checking for validation. Event trigger is either `valid.zf.abide` or `invalid.zf.abide`
     * Trigger includes the DOM element of the input.
     * @event Abide#valid
     * @event Abide#invalid
     */$el.trigger(message,[$el]);return goodToGo;}},{key:'validateForm',value:function validateForm(){var acc=[];var _this=this;this.$inputs.each(function(){acc.push(_this.validateInput((0,_jquery2.default)(this)));});var noError=acc.indexOf(false)===-1;this.$element.find('[data-abide-error]').css('display',noError?'none':'block');/**
     * Fires when the form is finished validating. Event trigger is either `formvalid.zf.abide` or `forminvalid.zf.abide`.
     * Trigger includes the element of the form.
     * @event Abide#formvalid
     * @event Abide#forminvalid
     */this.$element.trigger((noError?'formvalid':'forminvalid')+'.zf.abide',[this.$element]);return noError;}},{key:'validateText',value:function validateText($el,pattern){// A pattern can be passed to this function, or it will be infered from the input's "pattern" attribute, or it's "type" attribute
pattern=pattern||$el.attr('pattern')||$el.attr('type');var inputText=$el.val();var valid=false;if(inputText.length){// If the pattern attribute on the element is in Abide's list of patterns, then test that regexp
if(this.options.patterns.hasOwnProperty(pattern)){valid=this.options.patterns[pattern].test(inputText);}// If the pattern name isn't also the type attribute of the field, then test it as a regexp
else if(pattern!==$el.attr('type')){valid=new RegExp(pattern).test(inputText);}else{valid=true;}}// An empty field is valid if it's not required
else if(!$el.prop('required')){valid=true;}return valid;}},{key:'validateRadio',value:function validateRadio(groupName){// If at least one radio in the group has the `required` attribute, the group is considered required
// Per W3C spec, all radio buttons in a group should have `required`, but we're being nice
var $group=this.$element.find(':radio[name="'+groupName+'"]');var valid=false,required=false;// For the group to be required, at least one radio needs to be required
$group.each(function(i,e){if((0,_jquery2.default)(e).attr('required')){required=true;}});if(!required)valid=true;if(!valid){// For the group to be valid, at least one radio needs to be checked
$group.each(function(i,e){if((0,_jquery2.default)(e).prop('checked')){valid=true;}});};return valid;}},{key:'matchValidation',value:function matchValidation($el,validators,required){var _this5=this;required=required?true:false;var clear=validators.split(' ').map(function(v){return _this5.options.validators[v]($el,required,$el.parent());});return clear.indexOf(false)===-1;}},{key:'resetForm',value:function resetForm(){var $form=this.$element,opts=this.options;(0,_jquery2.default)('.'+opts.labelErrorClass,$form).not('small').removeClass(opts.labelErrorClass);(0,_jquery2.default)('.'+opts.inputErrorClass,$form).not('small').removeClass(opts.inputErrorClass);(0,_jquery2.default)(opts.formErrorSelector+'.'+opts.formErrorClass).removeClass(opts.formErrorClass);$form.find('[data-abide-error]').css('display','none');(0,_jquery2.default)(':input',$form).not(':button, :submit, :reset, :hidden, :radio, :checkbox, [data-abide-ignore]').val('').removeAttr('data-invalid');(0,_jquery2.default)(':input:radio',$form).not('[data-abide-ignore]').prop('checked',false).removeAttr('data-invalid');(0,_jquery2.default)(':input:checkbox',$form).not('[data-abide-ignore]').prop('checked',false).removeAttr('data-invalid');/**
     * Fires when the form has been reset.
     * @event Abide#formreset
     */$form.trigger('formreset.zf.abide',[$form]);}},{key:'_destroy',value:function _destroy(){var _this=this;this.$element.off('.abide').find('[data-abide-error]').css('display','none');this.$inputs.off('.abide').each(function(){_this.removeErrorClasses((0,_jquery2.default)(this));});}}]);return Abide;}(_foundation.Plugin);/**
 * Default settings for plugin
 */Abide.defaults={/**
   * The default event to validate inputs. Checkboxes and radios validate immediately.
   * Remove or change this value for manual validation.
   * @option
   * @type {?string}
   * @default 'fieldChange'
   */validateOn:'fieldChange',/**
   * Class to be applied to input labels on failed validation.
   * @option
   * @type {string}
   * @default 'is-invalid-label'
   */labelErrorClass:'is-invalid-label',/**
   * Class to be applied to inputs on failed validation.
   * @option
   * @type {string}
   * @default 'is-invalid-input'
   */inputErrorClass:'is-invalid-input',/**
   * Class selector to use to target Form Errors for show/hide.
   * @option
   * @type {string}
   * @default '.form-error'
   */formErrorSelector:'.form-error',/**
   * Class added to Form Errors on failed validation.
   * @option
   * @type {string}
   * @default 'is-visible'
   */formErrorClass:'is-visible',/**
   * Set to true to validate text inputs on any value change.
   * @option
   * @type {boolean}
   * @default false
   */liveValidate:false,/**
   * Set to true to validate inputs on blur.
   * @option
   * @type {boolean}
   * @default false
   */validateOnBlur:false,patterns:{alpha:/^[a-zA-Z]+$/,alpha_numeric:/^[a-zA-Z0-9]+$/,integer:/^[-+]?\d+$/,number:/^[-+]?\d*(?:[\.\,]\d+)?$/,// amex, visa, diners
card:/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|(?:222[1-9]|2[3-6][0-9]{2}|27[0-1][0-9]|2720)[0-9]{12}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/,cvv:/^([0-9]){3,4}$/,// http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#valid-e-mail-address
email:/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/,url:/^(https?|ftp|file|ssh):\/\/(((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/,// abc.de
domain:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,8}$/,datetime:/^([0-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])T([0-5][0-9])\:([0-5][0-9])\:([0-5][0-9])(Z|([\-\+]([0-1][0-9])\:00))$/,// YYYY-MM-DD
date:/(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))$/,// HH:MM:SS
time:/^(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}$/,dateISO:/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/,// MM/DD/YYYY
month_day_year:/^(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.]\d{4}$/,// DD/MM/YYYY
day_month_year:/^(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.]\d{4}$/,// #FFF or #FFFFFF
color:/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/,// Domain || URL
website:{test:function test(text){return Abide.defaults.patterns['domain'].test(text)||Abide.defaults.patterns['url'].test(text);}}},/**
   * Optional validation functions to be used. `equalTo` being the only default included function.
   * Functions should return only a boolean if the input is valid or not. Functions are given the following arguments:
   * el : The jQuery element to validate.
   * required : Boolean value of the required attribute be present or not.
   * parent : The direct parent of the input.
   * @option
   */validators:{equalTo:function equalTo(el,required,parent){return(0,_jquery2.default)('#'+el.attr('data-equalto')).val()===el.val();}}};exports.Abide=Abide;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.core','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.core'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationAccordion=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Accordion=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Accordion=function(_Plugin){_inherits(Accordion,_Plugin);function Accordion(){_classCallCheck(this,Accordion);return _possibleConstructorReturn(this,(Accordion.__proto__||Object.getPrototypeOf(Accordion)).apply(this,arguments));}_createClass(Accordion,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Accordion.defaults,this.$element.data(),options);this.className='Accordion';// ie9 back compat
this._init();_foundationUtil.Keyboard.register('Accordion',{'ENTER':'toggle','SPACE':'toggle','ARROW_DOWN':'next','ARROW_UP':'previous'});}},{key:'_init',value:function _init(){var _this3=this;this.$element.attr('role','tablist');this.$tabs=this.$element.children('[data-accordion-item]');this.$tabs.each(function(idx,el){var $el=(0,_jquery2.default)(el),$content=$el.children('[data-tab-content]'),id=$content[0].id||(0,_foundationUtil2.GetYoDigits)(6,'accordion'),linkId=el.id||id+'-label';$el.find('a:first').attr({'aria-controls':id,'role':'tab','id':linkId,'aria-expanded':false,'aria-selected':false});$content.attr({'role':'tabpanel','aria-labelledby':linkId,'aria-hidden':true,'id':id});});var $initActive=this.$element.find('.is-active').children('[data-tab-content]');this.firstTimeInit=true;if($initActive.length){this.down($initActive,this.firstTimeInit);this.firstTimeInit=false;}this._checkDeepLink=function(){var anchor=window.location.hash;//need a hash and a relevant anchor in this tabset
if(anchor.length){var $link=_this3.$element.find('[href$="'+anchor+'"]'),$anchor=(0,_jquery2.default)(anchor);if($link.length&&$anchor){if(!$link.parent('[data-accordion-item]').hasClass('is-active')){_this3.down($anchor,_this3.firstTimeInit);_this3.firstTimeInit=false;};//roll up a little to show the titles
if(_this3.options.deepLinkSmudge){var _this=_this3;(0,_jquery2.default)(window).load(function(){var offset=_this.$element.offset();(0,_jquery2.default)('html, body').animate({scrollTop:offset.top},_this.options.deepLinkSmudgeDelay);});}/**
            * Fires when the zplugin has deeplinked at pageload
            * @event Accordion#deeplink
            */_this3.$element.trigger('deeplink.zf.accordion',[$link,$anchor]);}}};//use browser to open a tab, if it exists in this tabset
if(this.options.deepLink){this._checkDeepLink();}this._events();}},{key:'_events',value:function _events(){var _this=this;this.$tabs.each(function(){var $elem=(0,_jquery2.default)(this);var $tabContent=$elem.children('[data-tab-content]');if($tabContent.length){$elem.children('a').off('click.zf.accordion keydown.zf.accordion').on('click.zf.accordion',function(e){e.preventDefault();_this.toggle($tabContent);}).on('keydown.zf.accordion',function(e){_foundationUtil.Keyboard.handleKey(e,'Accordion',{toggle:function toggle(){_this.toggle($tabContent);},next:function next(){var $a=$elem.next().find('a').focus();if(!_this.options.multiExpand){$a.trigger('click.zf.accordion');}},previous:function previous(){var $a=$elem.prev().find('a').focus();if(!_this.options.multiExpand){$a.trigger('click.zf.accordion');}},handled:function handled(){e.preventDefault();e.stopPropagation();}});});}});if(this.options.deepLink){(0,_jquery2.default)(window).on('popstate',this._checkDeepLink);}}},{key:'toggle',value:function toggle($target){if($target.closest('[data-accordion]').is('[disabled]')){console.info('Cannot toggle an accordion that is disabled.');return;}if($target.parent().hasClass('is-active')){this.up($target);}else{this.down($target);}//either replace or update browser history
if(this.options.deepLink){var anchor=$target.prev('a').attr('href');if(this.options.updateHistory){history.pushState({},'',anchor);}else{history.replaceState({},'',anchor);}}}},{key:'down',value:function down($target,firstTime){var _this4=this;/**
     * checking firstTime allows for initial render of the accordion
     * to render preset is-active panes.
     */if($target.closest('[data-accordion]').is('[disabled]')&&!firstTime){console.info('Cannot call down on an accordion that is disabled.');return;}$target.attr('aria-hidden',false).parent('[data-tab-content]').addBack().parent().addClass('is-active');if(!this.options.multiExpand&&!firstTime){var $currentActive=this.$element.children('.is-active').children('[data-tab-content]');if($currentActive.length){this.up($currentActive.not($target));}}$target.slideDown(this.options.slideSpeed,function(){/**
       * Fires when the tab is done opening.
       * @event Accordion#down
       */_this4.$element.trigger('down.zf.accordion',[$target]);});(0,_jquery2.default)('#'+$target.attr('aria-labelledby')).attr({'aria-expanded':true,'aria-selected':true});}},{key:'up',value:function up($target){if($target.closest('[data-accordion]').is('[disabled]')){console.info('Cannot call up on an accordion that is disabled.');return;}var $aunts=$target.parent().siblings(),_this=this;if(!this.options.allowAllClosed&&!$aunts.hasClass('is-active')||!$target.parent().hasClass('is-active')){return;}$target.slideUp(_this.options.slideSpeed,function(){/**
       * Fires when the tab is done collapsing up.
       * @event Accordion#up
       */_this.$element.trigger('up.zf.accordion',[$target]);});$target.attr('aria-hidden',true).parent().removeClass('is-active');(0,_jquery2.default)('#'+$target.attr('aria-labelledby')).attr({'aria-expanded':false,'aria-selected':false});}},{key:'_destroy',value:function _destroy(){this.$element.find('[data-tab-content]').stop(true).slideUp(0).css('display','');this.$element.find('a').off('.zf.accordion');if(this.options.deepLink){(0,_jquery2.default)(window).off('popstate',this._checkDeepLink);}}}]);return Accordion;}(_foundation.Plugin);Accordion.defaults={/**
   * Amount of time to animate the opening of an accordion pane.
   * @option
   * @type {number}
   * @default 250
   */slideSpeed:250,/**
   * Allow the accordion to have multiple open panes.
   * @option
   * @type {boolean}
   * @default false
   */multiExpand:false,/**
   * Allow the accordion to close all panes.
   * @option
   * @type {boolean}
   * @default false
   */allowAllClosed:false,/**
   * Allows the window to scroll to content of pane specified by hash anchor
   * @option
   * @type {boolean}
   * @default false
   */deepLink:false,/**
   * Adjust the deep link scroll to make sure the top of the accordion panel is visible
   * @option
   * @type {boolean}
   * @default false
   */deepLinkSmudge:false,/**
   * Animation time (ms) for the deep link adjustment
   * @option
   * @type {number}
   * @default 300
   */deepLinkSmudgeDelay:300,/**
   * Update the browser history with the open accordion
   * @option
   * @type {boolean}
   * @default false
   */updateHistory:false};exports.Accordion=Accordion;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.nest','./foundation.util.core','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.nest'),require('./foundation.util.core'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationAccordionMenu=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.AccordionMenu=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var AccordionMenu=function(_Plugin){_inherits(AccordionMenu,_Plugin);function AccordionMenu(){_classCallCheck(this,AccordionMenu);return _possibleConstructorReturn(this,(AccordionMenu.__proto__||Object.getPrototypeOf(AccordionMenu)).apply(this,arguments));}_createClass(AccordionMenu,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},AccordionMenu.defaults,this.$element.data(),options);this.className='AccordionMenu';// ie9 back compat
this._init();_foundationUtil.Keyboard.register('AccordionMenu',{'ENTER':'toggle','SPACE':'toggle','ARROW_RIGHT':'open','ARROW_UP':'up','ARROW_DOWN':'down','ARROW_LEFT':'close','ESCAPE':'closeAll'});}},{key:'_init',value:function _init(){_foundationUtil2.Nest.Feather(this.$element,'accordion');var _this=this;this.$element.find('[data-submenu]').not('.is-active').slideUp(0);//.find('a').css('padding-left', '1rem');
this.$element.attr({'role':'tree','aria-multiselectable':this.options.multiOpen});this.$menuLinks=this.$element.find('.is-accordion-submenu-parent');this.$menuLinks.each(function(){var linkId=this.id||(0,_foundationUtil3.GetYoDigits)(6,'acc-menu-link'),$elem=(0,_jquery2.default)(this),$sub=$elem.children('[data-submenu]'),subId=$sub[0].id||(0,_foundationUtil3.GetYoDigits)(6,'acc-menu'),isActive=$sub.hasClass('is-active');if(_this.options.submenuToggle){$elem.addClass('has-submenu-toggle');$elem.children('a').after('<button id="'+linkId+'" class="submenu-toggle" aria-controls="'+subId+'" aria-expanded="'+isActive+'" title="'+_this.options.submenuToggleText+'"><span class="submenu-toggle-text">'+_this.options.submenuToggleText+'</span></button>');}else{$elem.attr({'aria-controls':subId,'aria-expanded':isActive,'id':linkId});}$sub.attr({'aria-labelledby':linkId,'aria-hidden':!isActive,'role':'group','id':subId});});this.$element.find('li').attr({'role':'treeitem'});var initPanes=this.$element.find('.is-active');if(initPanes.length){var _this=this;initPanes.each(function(){_this.down((0,_jquery2.default)(this));});}this._events();}},{key:'_events',value:function _events(){var _this=this;this.$element.find('li').each(function(){var $submenu=(0,_jquery2.default)(this).children('[data-submenu]');if($submenu.length){if(_this.options.submenuToggle){(0,_jquery2.default)(this).children('.submenu-toggle').off('click.zf.accordionMenu').on('click.zf.accordionMenu',function(e){_this.toggle($submenu);});}else{(0,_jquery2.default)(this).children('a').off('click.zf.accordionMenu').on('click.zf.accordionMenu',function(e){e.preventDefault();_this.toggle($submenu);});}}}).on('keydown.zf.accordionmenu',function(e){var $element=(0,_jquery2.default)(this),$elements=$element.parent('ul').children('li'),$prevElement,$nextElement,$target=$element.children('[data-submenu]');$elements.each(function(i){if((0,_jquery2.default)(this).is($element)){$prevElement=$elements.eq(Math.max(0,i-1)).find('a').first();$nextElement=$elements.eq(Math.min(i+1,$elements.length-1)).find('a').first();if((0,_jquery2.default)(this).children('[data-submenu]:visible').length){// has open sub menu
$nextElement=$element.find('li:first-child').find('a').first();}if((0,_jquery2.default)(this).is(':first-child')){// is first element of sub menu
$prevElement=$element.parents('li').first().find('a').first();}else if($prevElement.parents('li').first().children('[data-submenu]:visible').length){// if previous element has open sub menu
$prevElement=$prevElement.parents('li').find('li:last-child').find('a').first();}if((0,_jquery2.default)(this).is(':last-child')){// is last element of sub menu
$nextElement=$element.parents('li').first().next('li').find('a').first();}return;}});_foundationUtil.Keyboard.handleKey(e,'AccordionMenu',{open:function open(){if($target.is(':hidden')){_this.down($target);$target.find('li').first().find('a').first().focus();}},close:function close(){if($target.length&&!$target.is(':hidden')){// close active sub of this item
_this.up($target);}else if($element.parent('[data-submenu]').length){// close currently open sub
_this.up($element.parent('[data-submenu]'));$element.parents('li').first().find('a').first().focus();}},up:function up(){$prevElement.focus();return true;},down:function down(){$nextElement.focus();return true;},toggle:function toggle(){if(_this.options.submenuToggle){return false;}if($element.children('[data-submenu]').length){_this.toggle($element.children('[data-submenu]'));return true;}},closeAll:function closeAll(){_this.hideAll();},handled:function handled(preventDefault){if(preventDefault){e.preventDefault();}e.stopImmediatePropagation();}});});//.attr('tabindex', 0);
}},{key:'hideAll',value:function hideAll(){this.up(this.$element.find('[data-submenu]'));}},{key:'showAll',value:function showAll(){this.down(this.$element.find('[data-submenu]'));}},{key:'toggle',value:function toggle($target){if(!$target.is(':animated')){if(!$target.is(':hidden')){this.up($target);}else{this.down($target);}}}},{key:'down',value:function down($target){var _this=this;if(!this.options.multiOpen){this.up(this.$element.find('.is-active').not($target.parentsUntil(this.$element).add($target)));}$target.addClass('is-active').attr({'aria-hidden':false});if(this.options.submenuToggle){$target.prev('.submenu-toggle').attr({'aria-expanded':true});}else{$target.parent('.is-accordion-submenu-parent').attr({'aria-expanded':true});}$target.slideDown(_this.options.slideSpeed,function(){/**
       * Fires when the menu is done opening.
       * @event AccordionMenu#down
       */_this.$element.trigger('down.zf.accordionMenu',[$target]);});}},{key:'up',value:function up($target){var _this=this;$target.slideUp(_this.options.slideSpeed,function(){/**
       * Fires when the menu is done collapsing up.
       * @event AccordionMenu#up
       */_this.$element.trigger('up.zf.accordionMenu',[$target]);});var $menus=$target.find('[data-submenu]').slideUp(0).addBack().attr('aria-hidden',true);if(this.options.submenuToggle){$menus.prev('.submenu-toggle').attr('aria-expanded',false);}else{$menus.parent('.is-accordion-submenu-parent').attr('aria-expanded',false);}}},{key:'_destroy',value:function _destroy(){this.$element.find('[data-submenu]').slideDown(0).css('display','');this.$element.find('a').off('click.zf.accordionMenu');if(this.options.submenuToggle){this.$element.find('.has-submenu-toggle').removeClass('has-submenu-toggle');this.$element.find('.submenu-toggle').remove();}_foundationUtil2.Nest.Burn(this.$element,'accordion');}}]);return AccordionMenu;}(_foundation.Plugin);AccordionMenu.defaults={/**
   * Amount of time to animate the opening of a submenu in ms.
   * @option
   * @type {number}
   * @default 250
   */slideSpeed:250,/**
   * Adds a separate submenu toggle button. This allows the parent item to have a link.
   * @option
   * @example true
   */submenuToggle:false,/**
   * The text used for the submenu toggle if enabled. This is used for screen readers only.
   * @option
   * @example true
   */submenuToggleText:'Toggle menu',/**
   * Allow the menu to have multiple open panes.
   * @option
   * @type {boolean}
   * @default true
   */multiOpen:true};exports.AccordionMenu=AccordionMenu;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.nest','./foundation.util.core','./foundation.util.box','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.nest'),require('./foundation.util.core'),require('./foundation.util.box'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationDrilldown=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundationUtil4,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Drilldown=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Drilldown=function(_Plugin){_inherits(Drilldown,_Plugin);function Drilldown(){_classCallCheck(this,Drilldown);return _possibleConstructorReturn(this,(Drilldown.__proto__||Object.getPrototypeOf(Drilldown)).apply(this,arguments));}_createClass(Drilldown,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Drilldown.defaults,this.$element.data(),options);this.className='Drilldown';// ie9 back compat
this._init();_foundationUtil.Keyboard.register('Drilldown',{'ENTER':'open','SPACE':'open','ARROW_RIGHT':'next','ARROW_UP':'up','ARROW_DOWN':'down','ARROW_LEFT':'previous','ESCAPE':'close','TAB':'down','SHIFT_TAB':'up'});}},{key:'_init',value:function _init(){_foundationUtil2.Nest.Feather(this.$element,'drilldown');if(this.options.autoApplyClass){this.$element.addClass('drilldown');}this.$element.attr({'role':'tree','aria-multiselectable':false});this.$submenuAnchors=this.$element.find('li.is-drilldown-submenu-parent').children('a');this.$submenus=this.$submenuAnchors.parent('li').children('[data-submenu]').attr('role','group');this.$menuItems=this.$element.find('li').not('.js-drilldown-back').attr('role','treeitem').find('a');this.$element.attr('data-mutate',this.$element.attr('data-drilldown')||(0,_foundationUtil3.GetYoDigits)(6,'drilldown'));this._prepareMenu();this._registerEvents();this._keyboardEvents();}},{key:'_prepareMenu',value:function _prepareMenu(){var _this=this;// if(!this.options.holdOpen){
//   this._menuLinkEvents();
// }
this.$submenuAnchors.each(function(){var $link=(0,_jquery2.default)(this);var $sub=$link.parent();if(_this.options.parentLink){$link.clone().prependTo($sub.children('[data-submenu]')).wrap('<li class="is-submenu-parent-item is-submenu-item is-drilldown-submenu-item" role="menuitem"></li>');}$link.data('savedHref',$link.attr('href')).removeAttr('href').attr('tabindex',0);$link.children('[data-submenu]').attr({'aria-hidden':true,'tabindex':0,'role':'group'});_this._events($link);});this.$submenus.each(function(){var $menu=(0,_jquery2.default)(this),$back=$menu.find('.js-drilldown-back');if(!$back.length){switch(_this.options.backButtonPosition){case"bottom":$menu.append(_this.options.backButton);break;case"top":$menu.prepend(_this.options.backButton);break;default:console.error("Unsupported backButtonPosition value '"+_this.options.backButtonPosition+"'");}}_this._back($menu);});this.$submenus.addClass('invisible');if(!this.options.autoHeight){this.$submenus.addClass('drilldown-submenu-cover-previous');}// create a wrapper on element if it doesn't exist.
if(!this.$element.parent().hasClass('is-drilldown')){this.$wrapper=(0,_jquery2.default)(this.options.wrapper).addClass('is-drilldown');if(this.options.animateHeight)this.$wrapper.addClass('animate-height');this.$element.wrap(this.$wrapper);}// set wrapper
this.$wrapper=this.$element.parent();this.$wrapper.css(this._getMaxDims());}},{key:'_resize',value:function _resize(){this.$wrapper.css({'max-width':'none','min-height':'none'});// _getMaxDims has side effects (boo) but calling it should update all other necessary heights & widths
this.$wrapper.css(this._getMaxDims());}},{key:'_events',value:function _events($elem){var _this=this;$elem.off('click.zf.drilldown').on('click.zf.drilldown',function(e){if((0,_jquery2.default)(e.target).parentsUntil('ul','li').hasClass('is-drilldown-submenu-parent')){e.stopImmediatePropagation();e.preventDefault();}// if(e.target !== e.currentTarget.firstElementChild){
//   return false;
// }
_this._show($elem.parent('li'));if(_this.options.closeOnClick){var $body=(0,_jquery2.default)('body');$body.off('.zf.drilldown').on('click.zf.drilldown',function(e){if(e.target===_this.$element[0]||_jquery2.default.contains(_this.$element[0],e.target)){return;}e.preventDefault();_this._hideAll();$body.off('.zf.drilldown');});}});}},{key:'_registerEvents',value:function _registerEvents(){if(this.options.scrollTop){this._bindHandler=this._scrollTop.bind(this);this.$element.on('open.zf.drilldown hide.zf.drilldown closed.zf.drilldown',this._bindHandler);}this.$element.on('mutateme.zf.trigger',this._resize.bind(this));}},{key:'_scrollTop',value:function _scrollTop(){var _this=this;var $scrollTopElement=_this.options.scrollTopElement!=''?(0,_jquery2.default)(_this.options.scrollTopElement):_this.$element,scrollPos=parseInt($scrollTopElement.offset().top+_this.options.scrollTopOffset,10);(0,_jquery2.default)('html, body').stop(true).animate({scrollTop:scrollPos},_this.options.animationDuration,_this.options.animationEasing,function(){/**
        * Fires after the menu has scrolled
        * @event Drilldown#scrollme
        */if(this===(0,_jquery2.default)('html')[0])_this.$element.trigger('scrollme.zf.drilldown');});}},{key:'_keyboardEvents',value:function _keyboardEvents(){var _this=this;this.$menuItems.add(this.$element.find('.js-drilldown-back > a, .is-submenu-parent-item > a')).on('keydown.zf.drilldown',function(e){var $element=(0,_jquery2.default)(this),$elements=$element.parent('li').parent('ul').children('li').children('a'),$prevElement,$nextElement;$elements.each(function(i){if((0,_jquery2.default)(this).is($element)){$prevElement=$elements.eq(Math.max(0,i-1));$nextElement=$elements.eq(Math.min(i+1,$elements.length-1));return;}});_foundationUtil.Keyboard.handleKey(e,'Drilldown',{next:function next(){if($element.is(_this.$submenuAnchors)){_this._show($element.parent('li'));$element.parent('li').one((0,_foundationUtil3.transitionend)($element),function(){$element.parent('li').find('ul li a').filter(_this.$menuItems).first().focus();});return true;}},previous:function previous(){_this._hide($element.parent('li').parent('ul'));$element.parent('li').parent('ul').one((0,_foundationUtil3.transitionend)($element),function(){setTimeout(function(){$element.parent('li').parent('ul').parent('li').children('a').first().focus();},1);});return true;},up:function up(){$prevElement.focus();// Don't tap focus on first element in root ul
return!$element.is(_this.$element.find('> li:first-child > a'));},down:function down(){$nextElement.focus();// Don't tap focus on last element in root ul
return!$element.is(_this.$element.find('> li:last-child > a'));},close:function close(){// Don't close on element in root ul
if(!$element.is(_this.$element.find('> li > a'))){_this._hide($element.parent().parent());$element.parent().parent().siblings('a').focus();}},open:function open(){if(!$element.is(_this.$menuItems)){// not menu item means back button
_this._hide($element.parent('li').parent('ul'));$element.parent('li').parent('ul').one((0,_foundationUtil3.transitionend)($element),function(){setTimeout(function(){$element.parent('li').parent('ul').parent('li').children('a').first().focus();},1);});return true;}else if($element.is(_this.$submenuAnchors)){_this._show($element.parent('li'));$element.parent('li').one((0,_foundationUtil3.transitionend)($element),function(){$element.parent('li').find('ul li a').filter(_this.$menuItems).first().focus();});return true;}},handled:function handled(preventDefault){if(preventDefault){e.preventDefault();}e.stopImmediatePropagation();}});});// end keyboardAccess
}},{key:'_hideAll',value:function _hideAll(){var $elem=this.$element.find('.is-drilldown-submenu.is-active').addClass('is-closing');if(this.options.autoHeight)this.$wrapper.css({height:$elem.parent().closest('ul').data('calcHeight')});$elem.one((0,_foundationUtil3.transitionend)($elem),function(e){$elem.removeClass('is-active is-closing');});/**
         * Fires when the menu is fully closed.
         * @event Drilldown#closed
         */this.$element.trigger('closed.zf.drilldown');}},{key:'_back',value:function _back($elem){var _this=this;$elem.off('click.zf.drilldown');$elem.children('.js-drilldown-back').on('click.zf.drilldown',function(e){e.stopImmediatePropagation();// console.log('mouseup on back');
_this._hide($elem);// If there is a parent submenu, call show
var parentSubMenu=$elem.parent('li').parent('ul').parent('li');if(parentSubMenu.length){_this._show(parentSubMenu);}});}},{key:'_menuLinkEvents',value:function _menuLinkEvents(){var _this=this;this.$menuItems.not('.is-drilldown-submenu-parent').off('click.zf.drilldown').on('click.zf.drilldown',function(e){// e.stopImmediatePropagation();
setTimeout(function(){_this._hideAll();},0);});}},{key:'_show',value:function _show($elem){if(this.options.autoHeight)this.$wrapper.css({height:$elem.children('[data-submenu]').data('calcHeight')});$elem.attr('aria-expanded',true);$elem.children('[data-submenu]').addClass('is-active').removeClass('invisible').attr('aria-hidden',false);/**
     * Fires when the submenu has opened.
     * @event Drilldown#open
     */this.$element.trigger('open.zf.drilldown',[$elem]);}},{key:'_hide',value:function _hide($elem){if(this.options.autoHeight)this.$wrapper.css({height:$elem.parent().closest('ul').data('calcHeight')});var _this=this;$elem.parent('li').attr('aria-expanded',false);$elem.attr('aria-hidden',true).addClass('is-closing');$elem.addClass('is-closing').one((0,_foundationUtil3.transitionend)($elem),function(){$elem.removeClass('is-active is-closing');$elem.blur().addClass('invisible');});/**
     * Fires when the submenu has closed.
     * @event Drilldown#hide
     */$elem.trigger('hide.zf.drilldown',[$elem]);}},{key:'_getMaxDims',value:function _getMaxDims(){var maxHeight=0,result={},_this=this;this.$submenus.add(this.$element).each(function(){var numOfElems=(0,_jquery2.default)(this).children('li').length;var height=_foundationUtil4.Box.GetDimensions(this).height;maxHeight=height>maxHeight?height:maxHeight;if(_this.options.autoHeight){(0,_jquery2.default)(this).data('calcHeight',height);if(!(0,_jquery2.default)(this).hasClass('is-drilldown-submenu'))result['height']=height;}});if(!this.options.autoHeight)result['min-height']=maxHeight+'px';result['max-width']=this.$element[0].getBoundingClientRect().width+'px';return result;}},{key:'_destroy',value:function _destroy(){if(this.options.scrollTop)this.$element.off('.zf.drilldown',this._bindHandler);this._hideAll();this.$element.off('mutateme.zf.trigger');_foundationUtil2.Nest.Burn(this.$element,'drilldown');this.$element.unwrap().find('.js-drilldown-back, .is-submenu-parent-item').remove().end().find('.is-active, .is-closing, .is-drilldown-submenu').removeClass('is-active is-closing is-drilldown-submenu').end().find('[data-submenu]').removeAttr('aria-hidden tabindex role');this.$submenuAnchors.each(function(){(0,_jquery2.default)(this).off('.zf.drilldown');});this.$submenus.removeClass('drilldown-submenu-cover-previous invisible');this.$element.find('a').each(function(){var $link=(0,_jquery2.default)(this);$link.removeAttr('tabindex');if($link.data('savedHref')){$link.attr('href',$link.data('savedHref')).removeData('savedHref');}else{return;}});}}]);return Drilldown;}(_foundation.Plugin);Drilldown.defaults={/**
   * Drilldowns depend on styles in order to function properly; in the default build of Foundation these are
   * on the `drilldown` class. This option auto-applies this class to the drilldown upon initialization.
   * @option
   * @type {boolian}
   * @default true
   */autoApplyClass:true,/**
   * Markup used for JS generated back button. Prepended  or appended (see backButtonPosition) to submenu lists and deleted on `destroy` method, 'js-drilldown-back' class required. Remove the backslash (`\`) if copy and pasting.
   * @option
   * @type {string}
   * @default '<li class="js-drilldown-back"><a tabindex="0">Back</a></li>'
   */backButton:'<li class="js-drilldown-back"><a tabindex="0">Back</a></li>',/**
   * Position the back button either at the top or bottom of drilldown submenus. Can be `'left'` or `'bottom'`.
   * @option
   * @type {string}
   * @default top
   */backButtonPosition:'top',/**
   * Markup used to wrap drilldown menu. Use a class name for independent styling; the JS applied class: `is-drilldown` is required. Remove the backslash (`\`) if copy and pasting.
   * @option
   * @type {string}
   * @default '<div></div>'
   */wrapper:'<div></div>',/**
   * Adds the parent link to the submenu.
   * @option
   * @type {boolean}
   * @default false
   */parentLink:false,/**
   * Allow the menu to return to root list on body click.
   * @option
   * @type {boolean}
   * @default false
   */closeOnClick:false,/**
   * Allow the menu to auto adjust height.
   * @option
   * @type {boolean}
   * @default false
   */autoHeight:false,/**
   * Animate the auto adjust height.
   * @option
   * @type {boolean}
   * @default false
   */animateHeight:false,/**
   * Scroll to the top of the menu after opening a submenu or navigating back using the menu back button
   * @option
   * @type {boolean}
   * @default false
   */scrollTop:false,/**
   * String jquery selector (for example 'body') of element to take offset().top from, if empty string the drilldown menu offset().top is taken
   * @option
   * @type {string}
   * @default ''
   */scrollTopElement:'',/**
   * ScrollTop offset
   * @option
   * @type {number}
   * @default 0
   */scrollTopOffset:0,/**
   * Scroll animation duration
   * @option
   * @type {number}
   * @default 500
   */animationDuration:500,/**
   * Scroll animation easing. Can be `'swing'` or `'linear'`.
   * @option
   * @type {string}
   * @see {@link https://api.jquery.com/animate|JQuery animate}
   * @default 'swing'
   */animationEasing:'swing'// holdOpen: false
};exports.Drilldown=Drilldown;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.core','./foundation.positionable','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.core'),require('./foundation.positionable'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationDropdown=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation,_foundationUtil3){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Dropdown=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}var _get=function get(object,property,receiver){if(object===null)object=Function.prototype;var desc=Object.getOwnPropertyDescriptor(object,property);if(desc===undefined){var parent=Object.getPrototypeOf(object);if(parent===null){return undefined;}else{return get(parent,property,receiver);}}else if("value"in desc){return desc.value;}else{var getter=desc.get;if(getter===undefined){return undefined;}return getter.call(receiver);}};function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Dropdown=function(_Positionable){_inherits(Dropdown,_Positionable);function Dropdown(){_classCallCheck(this,Dropdown);return _possibleConstructorReturn(this,(Dropdown.__proto__||Object.getPrototypeOf(Dropdown)).apply(this,arguments));}_createClass(Dropdown,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Dropdown.defaults,this.$element.data(),options);this.className='Dropdown';// ie9 back compat
// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil3.Triggers.init(_jquery2.default);this._init();_foundationUtil.Keyboard.register('Dropdown',{'ENTER':'open','SPACE':'open','ESCAPE':'close'});}},{key:'_init',value:function _init(){var $id=this.$element.attr('id');this.$anchors=(0,_jquery2.default)('[data-toggle="'+$id+'"]').length?(0,_jquery2.default)('[data-toggle="'+$id+'"]'):(0,_jquery2.default)('[data-open="'+$id+'"]');this.$anchors.attr({'aria-controls':$id,'data-is-focus':false,'data-yeti-box':$id,'aria-haspopup':true,'aria-expanded':false});this._setCurrentAnchor(this.$anchors.first());if(this.options.parentClass){this.$parent=this.$element.parents('.'+this.options.parentClass);}else{this.$parent=null;}this.$element.attr({'aria-hidden':'true','data-yeti-box':$id,'data-resize':$id,'aria-labelledby':this.$currentAnchor.id||(0,_foundationUtil2.GetYoDigits)(6,'dd-anchor')});_get(Dropdown.prototype.__proto__||Object.getPrototypeOf(Dropdown.prototype),'_init',this).call(this);this._events();}},{key:'_getDefaultPosition',value:function _getDefaultPosition(){// handle legacy classnames
var position=this.$element[0].className.match(/(top|left|right|bottom)/g);if(position){return position[0];}else{return'bottom';}}},{key:'_getDefaultAlignment',value:function _getDefaultAlignment(){// handle legacy float approach
var horizontalPosition=/float-(\S+)/.exec(this.$currentAnchor.className);if(horizontalPosition){return horizontalPosition[1];}return _get(Dropdown.prototype.__proto__||Object.getPrototypeOf(Dropdown.prototype),'_getDefaultAlignment',this).call(this);}},{key:'_setPosition',value:function _setPosition(){_get(Dropdown.prototype.__proto__||Object.getPrototypeOf(Dropdown.prototype),'_setPosition',this).call(this,this.$currentAnchor,this.$element,this.$parent);}},{key:'_setCurrentAnchor',value:function _setCurrentAnchor(el){this.$currentAnchor=(0,_jquery2.default)(el);}},{key:'_events',value:function _events(){var _this=this;this.$element.on({'open.zf.trigger':this.open.bind(this),'close.zf.trigger':this.close.bind(this),'toggle.zf.trigger':this.toggle.bind(this),'resizeme.zf.trigger':this._setPosition.bind(this)});this.$anchors.off('click.zf.trigger').on('click.zf.trigger',function(){_this._setCurrentAnchor(this);});if(this.options.hover){this.$anchors.off('mouseenter.zf.dropdown mouseleave.zf.dropdown').on('mouseenter.zf.dropdown',function(){_this._setCurrentAnchor(this);var bodyData=(0,_jquery2.default)('body').data();if(typeof bodyData.whatinput==='undefined'||bodyData.whatinput==='mouse'){clearTimeout(_this.timeout);_this.timeout=setTimeout(function(){_this.open();_this.$anchors.data('hover',true);},_this.options.hoverDelay);}}).on('mouseleave.zf.dropdown',function(){clearTimeout(_this.timeout);_this.timeout=setTimeout(function(){_this.close();_this.$anchors.data('hover',false);},_this.options.hoverDelay);});if(this.options.hoverPane){this.$element.off('mouseenter.zf.dropdown mouseleave.zf.dropdown').on('mouseenter.zf.dropdown',function(){clearTimeout(_this.timeout);}).on('mouseleave.zf.dropdown',function(){clearTimeout(_this.timeout);_this.timeout=setTimeout(function(){_this.close();_this.$anchors.data('hover',false);},_this.options.hoverDelay);});}}this.$anchors.add(this.$element).on('keydown.zf.dropdown',function(e){var $target=(0,_jquery2.default)(this),visibleFocusableElements=_foundationUtil.Keyboard.findFocusable(_this.$element);_foundationUtil.Keyboard.handleKey(e,'Dropdown',{open:function open(){if($target.is(_this.$anchors)){_this.open();_this.$element.attr('tabindex',-1).focus();e.preventDefault();}},close:function close(){_this.close();_this.$anchors.focus();}});});}},{key:'_addBodyHandler',value:function _addBodyHandler(){var $body=(0,_jquery2.default)(document.body).not(this.$element),_this=this;$body.off('click.zf.dropdown').on('click.zf.dropdown',function(e){if(_this.$anchors.is(e.target)||_this.$anchors.find(e.target).length){return;}if(_this.$element.find(e.target).length){return;}_this.close();$body.off('click.zf.dropdown');});}},{key:'open',value:function open(){// var _this = this;
/**
     * Fires to close other open dropdowns, typically when dropdown is opening
     * @event Dropdown#closeme
     */this.$element.trigger('closeme.zf.dropdown',this.$element.attr('id'));this.$anchors.addClass('hover').attr({'aria-expanded':true});// this.$element/*.show()*/;
this.$element.addClass('is-opening');this._setPosition();this.$element.removeClass('is-opening').addClass('is-open').attr({'aria-hidden':false});if(this.options.autoFocus){var $focusable=_foundationUtil.Keyboard.findFocusable(this.$element);if($focusable.length){$focusable.eq(0).focus();}}if(this.options.closeOnClick){this._addBodyHandler();}if(this.options.trapFocus){_foundationUtil.Keyboard.trapFocus(this.$element);}/**
     * Fires once the dropdown is visible.
     * @event Dropdown#show
     */this.$element.trigger('show.zf.dropdown',[this.$element]);}},{key:'close',value:function close(){if(!this.$element.hasClass('is-open')){return false;}this.$element.removeClass('is-open').attr({'aria-hidden':true});this.$anchors.removeClass('hover').attr('aria-expanded',false);/**
     * Fires once the dropdown is no longer visible.
     * @event Dropdown#hide
     */this.$element.trigger('hide.zf.dropdown',[this.$element]);if(this.options.trapFocus){_foundationUtil.Keyboard.releaseFocus(this.$element);}}},{key:'toggle',value:function toggle(){if(this.$element.hasClass('is-open')){if(this.$anchors.data('hover'))return;this.close();}else{this.open();}}},{key:'_destroy',value:function _destroy(){this.$element.off('.zf.trigger').hide();this.$anchors.off('.zf.dropdown');(0,_jquery2.default)(document.body).off('click.zf.dropdown');}}]);return Dropdown;}(_foundation.Positionable);Dropdown.defaults={/**
   * Class that designates bounding container of Dropdown (default: window)
   * @option
   * @type {?string}
   * @default null
   */parentClass:null,/**
   * Amount of time to delay opening a submenu on hover event.
   * @option
   * @type {number}
   * @default 250
   */hoverDelay:250,/**
   * Allow submenus to open on hover events
   * @option
   * @type {boolean}
   * @default false
   */hover:false,/**
   * Don't close dropdown when hovering over dropdown pane
   * @option
   * @type {boolean}
   * @default false
   */hoverPane:false,/**
   * Number of pixels between the dropdown pane and the triggering element on open.
   * @option
   * @type {number}
   * @default 0
   */vOffset:0,/**
   * Number of pixels between the dropdown pane and the triggering element on open.
   * @option
   * @type {number}
   * @default 0
   */hOffset:0,/**
   * DEPRECATED: Class applied to adjust open position.
   * @option
   * @type {string}
   * @default ''
   */positionClass:'',/**
   * Position of dropdown. Can be left, right, bottom, top, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */position:'auto',/**
   * Alignment of dropdown relative to anchor. Can be left, right, bottom, top, center, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */alignment:'auto',/**
   * Allow overlap of container/window. If false, dropdown will first try to position as defined by data-position and data-alignment, but reposition if it would cause an overflow.
   * @option
   * @type {boolean}
   * @default false
   */allowOverlap:false,/**
   * Allow overlap of only the bottom of the container. This is the most common
   * behavior for dropdowns, allowing the dropdown to extend the bottom of the
   * screen but not otherwise influence or break out of the container.
   * @option
   * @type {boolean}
   * @default true
   */allowBottomOverlap:true,/**
   * Allow the plugin to trap focus to the dropdown pane if opened with keyboard commands.
   * @option
   * @type {boolean}
   * @default false
   */trapFocus:false,/**
   * Allow the plugin to set focus to the first focusable element within the pane, regardless of method of opening.
   * @option
   * @type {boolean}
   * @default false
   */autoFocus:false,/**
   * Allows a click on the body to close the dropdown.
   * @option
   * @type {boolean}
   * @default false
   */closeOnClick:false};exports.Dropdown=Dropdown;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.nest','./foundation.util.box','./foundation.util.core','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.nest'),require('./foundation.util.box'),require('./foundation.util.core'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationDropdownMenu=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundationUtil4,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.DropdownMenu=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var DropdownMenu=function(_Plugin){_inherits(DropdownMenu,_Plugin);function DropdownMenu(){_classCallCheck(this,DropdownMenu);return _possibleConstructorReturn(this,(DropdownMenu.__proto__||Object.getPrototypeOf(DropdownMenu)).apply(this,arguments));}_createClass(DropdownMenu,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},DropdownMenu.defaults,this.$element.data(),options);this.className='DropdownMenu';// ie9 back compat
this._init();_foundationUtil.Keyboard.register('DropdownMenu',{'ENTER':'open','SPACE':'open','ARROW_RIGHT':'next','ARROW_UP':'up','ARROW_DOWN':'down','ARROW_LEFT':'previous','ESCAPE':'close'});}},{key:'_init',value:function _init(){_foundationUtil2.Nest.Feather(this.$element,'dropdown');var subs=this.$element.find('li.is-dropdown-submenu-parent');this.$element.children('.is-dropdown-submenu-parent').children('.is-dropdown-submenu').addClass('first-sub');this.$menuItems=this.$element.find('[role="menuitem"]');this.$tabs=this.$element.children('[role="menuitem"]');this.$tabs.find('ul.is-dropdown-submenu').addClass(this.options.verticalClass);if(this.options.alignment==='auto'){if(this.$element.hasClass(this.options.rightClass)||(0,_foundationUtil4.rtl)()||this.$element.parents('.top-bar-right').is('*')){this.options.alignment='right';subs.addClass('opens-left');}else{this.options.alignment='left';subs.addClass('opens-right');}}else{if(this.options.alignment==='right'){subs.addClass('opens-left');}else{subs.addClass('opens-right');}}this.changed=false;this._events();}},{key:'_isVertical',value:function _isVertical(){return this.$tabs.css('display')==='block'||this.$element.css('flex-direction')==='column';}},{key:'_isRtl',value:function _isRtl(){return this.$element.hasClass('align-right')||(0,_foundationUtil4.rtl)()&&!this.$element.hasClass('align-left');}},{key:'_events',value:function _events(){var _this=this,hasTouch='ontouchstart'in window||typeof window.ontouchstart!=='undefined',parClass='is-dropdown-submenu-parent';// used for onClick and in the keyboard handlers
var handleClickFn=function handleClickFn(e){var $elem=(0,_jquery2.default)(e.target).parentsUntil('ul','.'+parClass),hasSub=$elem.hasClass(parClass),hasClicked=$elem.attr('data-is-click')==='true',$sub=$elem.children('.is-dropdown-submenu');if(hasSub){if(hasClicked){if(!_this.options.closeOnClick||!_this.options.clickOpen&&!hasTouch||_this.options.forceFollow&&hasTouch){return;}else{e.stopImmediatePropagation();e.preventDefault();_this._hide($elem);}}else{e.preventDefault();e.stopImmediatePropagation();_this._show($sub);$elem.add($elem.parentsUntil(_this.$element,'.'+parClass)).attr('data-is-click',true);}}};if(this.options.clickOpen||hasTouch){this.$menuItems.on('click.zf.dropdownmenu touchstart.zf.dropdownmenu',handleClickFn);}// Handle Leaf element Clicks
if(_this.options.closeOnClickInside){this.$menuItems.on('click.zf.dropdownmenu',function(e){var $elem=(0,_jquery2.default)(this),hasSub=$elem.hasClass(parClass);if(!hasSub){_this._hide();}});}if(!this.options.disableHover){this.$menuItems.on('mouseenter.zf.dropdownmenu',function(e){var $elem=(0,_jquery2.default)(this),hasSub=$elem.hasClass(parClass);if(hasSub){clearTimeout($elem.data('_delay'));$elem.data('_delay',setTimeout(function(){_this._show($elem.children('.is-dropdown-submenu'));},_this.options.hoverDelay));}}).on('mouseleave.zf.dropdownmenu',function(e){var $elem=(0,_jquery2.default)(this),hasSub=$elem.hasClass(parClass);if(hasSub&&_this.options.autoclose){if($elem.attr('data-is-click')==='true'&&_this.options.clickOpen){return false;}clearTimeout($elem.data('_delay'));$elem.data('_delay',setTimeout(function(){_this._hide($elem);},_this.options.closingTime));}});}this.$menuItems.on('keydown.zf.dropdownmenu',function(e){var $element=(0,_jquery2.default)(e.target).parentsUntil('ul','[role="menuitem"]'),isTab=_this.$tabs.index($element)>-1,$elements=isTab?_this.$tabs:$element.siblings('li').add($element),$prevElement,$nextElement;$elements.each(function(i){if((0,_jquery2.default)(this).is($element)){$prevElement=$elements.eq(i-1);$nextElement=$elements.eq(i+1);return;}});var nextSibling=function nextSibling(){$nextElement.children('a:first').focus();e.preventDefault();},prevSibling=function prevSibling(){$prevElement.children('a:first').focus();e.preventDefault();},openSub=function openSub(){var $sub=$element.children('ul.is-dropdown-submenu');if($sub.length){_this._show($sub);$element.find('li > a:first').focus();e.preventDefault();}else{return;}},closeSub=function closeSub(){//if ($element.is(':first-child')) {
var close=$element.parent('ul').parent('li');close.children('a:first').focus();_this._hide(close);e.preventDefault();//}
};var functions={open:openSub,close:function close(){_this._hide(_this.$element);_this.$menuItems.eq(0).children('a').focus();// focus to first element
e.preventDefault();},handled:function handled(){e.stopImmediatePropagation();}};if(isTab){if(_this._isVertical()){// vertical menu
if(_this._isRtl()){// right aligned
_jquery2.default.extend(functions,{down:nextSibling,up:prevSibling,next:closeSub,previous:openSub});}else{// left aligned
_jquery2.default.extend(functions,{down:nextSibling,up:prevSibling,next:openSub,previous:closeSub});}}else{// horizontal menu
if(_this._isRtl()){// right aligned
_jquery2.default.extend(functions,{next:prevSibling,previous:nextSibling,down:openSub,up:closeSub});}else{// left aligned
_jquery2.default.extend(functions,{next:nextSibling,previous:prevSibling,down:openSub,up:closeSub});}}}else{// not tabs -> one sub
if(_this._isRtl()){// right aligned
_jquery2.default.extend(functions,{next:closeSub,previous:openSub,down:nextSibling,up:prevSibling});}else{// left aligned
_jquery2.default.extend(functions,{next:openSub,previous:closeSub,down:nextSibling,up:prevSibling});}}_foundationUtil.Keyboard.handleKey(e,'DropdownMenu',functions);});}},{key:'_addBodyHandler',value:function _addBodyHandler(){var $body=(0,_jquery2.default)(document.body),_this=this;$body.off('mouseup.zf.dropdownmenu touchend.zf.dropdownmenu').on('mouseup.zf.dropdownmenu touchend.zf.dropdownmenu',function(e){var $link=_this.$element.find(e.target);if($link.length){return;}_this._hide();$body.off('mouseup.zf.dropdownmenu touchend.zf.dropdownmenu');});}},{key:'_show',value:function _show($sub){var idx=this.$tabs.index(this.$tabs.filter(function(i,el){return(0,_jquery2.default)(el).find($sub).length>0;}));var $sibs=$sub.parent('li.is-dropdown-submenu-parent').siblings('li.is-dropdown-submenu-parent');this._hide($sibs,idx);$sub.css('visibility','hidden').addClass('js-dropdown-active').parent('li.is-dropdown-submenu-parent').addClass('is-active');var clear=_foundationUtil3.Box.ImNotTouchingYou($sub,null,true);if(!clear){var oldClass=this.options.alignment==='left'?'-right':'-left',$parentLi=$sub.parent('.is-dropdown-submenu-parent');$parentLi.removeClass('opens'+oldClass).addClass('opens-'+this.options.alignment);clear=_foundationUtil3.Box.ImNotTouchingYou($sub,null,true);if(!clear){$parentLi.removeClass('opens-'+this.options.alignment).addClass('opens-inner');}this.changed=true;}$sub.css('visibility','');if(this.options.closeOnClick){this._addBodyHandler();}/**
     * Fires when the new dropdown pane is visible.
     * @event DropdownMenu#show
     */this.$element.trigger('show.zf.dropdownmenu',[$sub]);}},{key:'_hide',value:function _hide($elem,idx){var $toClose;if($elem&&$elem.length){$toClose=$elem;}else if(idx!==undefined){$toClose=this.$tabs.not(function(i,el){return i===idx;});}else{$toClose=this.$element;}var somethingToClose=$toClose.hasClass('is-active')||$toClose.find('.is-active').length>0;if(somethingToClose){$toClose.find('li.is-active').add($toClose).attr({'data-is-click':false}).removeClass('is-active');$toClose.find('ul.js-dropdown-active').removeClass('js-dropdown-active');if(this.changed||$toClose.find('opens-inner').length){var oldClass=this.options.alignment==='left'?'right':'left';$toClose.find('li.is-dropdown-submenu-parent').add($toClose).removeClass('opens-inner opens-'+this.options.alignment).addClass('opens-'+oldClass);this.changed=false;}/**
       * Fires when the open menus are closed.
       * @event DropdownMenu#hide
       */this.$element.trigger('hide.zf.dropdownmenu',[$toClose]);}}},{key:'_destroy',value:function _destroy(){this.$menuItems.off('.zf.dropdownmenu').removeAttr('data-is-click').removeClass('is-right-arrow is-left-arrow is-down-arrow opens-right opens-left opens-inner');(0,_jquery2.default)(document.body).off('.zf.dropdownmenu');_foundationUtil2.Nest.Burn(this.$element,'dropdown');}}]);return DropdownMenu;}(_foundation.Plugin);/**
 * Default settings for plugin
 */DropdownMenu.defaults={/**
   * Disallows hover events from opening submenus
   * @option
   * @type {boolean}
   * @default false
   */disableHover:false,/**
   * Allow a submenu to automatically close on a mouseleave event, if not clicked open.
   * @option
   * @type {boolean}
   * @default true
   */autoclose:true,/**
   * Amount of time to delay opening a submenu on hover event.
   * @option
   * @type {number}
   * @default 50
   */hoverDelay:50,/**
   * Allow a submenu to open/remain open on parent click event. Allows cursor to move away from menu.
   * @option
   * @type {boolean}
   * @default false
   */clickOpen:false,/**
   * Amount of time to delay closing a submenu on a mouseleave event.
   * @option
   * @type {number}
   * @default 500
   */closingTime:500,/**
   * Position of the menu relative to what direction the submenus should open. Handled by JS. Can be `'auto'`, `'left'` or `'right'`.
   * @option
   * @type {string}
   * @default 'auto'
   */alignment:'auto',/**
   * Allow clicks on the body to close any open submenus.
   * @option
   * @type {boolean}
   * @default true
   */closeOnClick:true,/**
   * Allow clicks on leaf anchor links to close any open submenus.
   * @option
   * @type {boolean}
   * @default true
   */closeOnClickInside:true,/**
   * Class applied to vertical oriented menus, Foundation default is `vertical`. Update this if using your own class.
   * @option
   * @type {string}
   * @default 'vertical'
   */verticalClass:'vertical',/**
   * Class applied to right-side oriented menus, Foundation default is `align-right`. Update this if using your own class.
   * @option
   * @type {string}
   * @default 'align-right'
   */rightClass:'align-right',/**
   * Boolean to force overide the clicking of links to perform default action, on second touch event for mobile.
   * @option
   * @type {boolean}
   * @default true
   */forceFollow:true};exports.DropdownMenu=DropdownMenu;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.mediaQuery','./foundation.util.imageLoader','./foundation.util.core','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.mediaQuery'),require('./foundation.util.imageLoader'),require('./foundation.util.core'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationEqualizer=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Equalizer=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Equalizer=function(_Plugin){_inherits(Equalizer,_Plugin);function Equalizer(){_classCallCheck(this,Equalizer);return _possibleConstructorReturn(this,(Equalizer.__proto__||Object.getPrototypeOf(Equalizer)).apply(this,arguments));}_createClass(Equalizer,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Equalizer.defaults,this.$element.data(),options);this.className='Equalizer';// ie9 back compat
this._init();}},{key:'_init',value:function _init(){var eqId=this.$element.attr('data-equalizer')||'';var $watched=this.$element.find('[data-equalizer-watch="'+eqId+'"]');_foundationUtil.MediaQuery._init();this.$watched=$watched.length?$watched:this.$element.find('[data-equalizer-watch]');this.$element.attr('data-resize',eqId||(0,_foundationUtil3.GetYoDigits)(6,'eq'));this.$element.attr('data-mutate',eqId||(0,_foundationUtil3.GetYoDigits)(6,'eq'));this.hasNested=this.$element.find('[data-equalizer]').length>0;this.isNested=this.$element.parentsUntil(document.body,'[data-equalizer]').length>0;this.isOn=false;this._bindHandler={onResizeMeBound:this._onResizeMe.bind(this),onPostEqualizedBound:this._onPostEqualized.bind(this)};var imgs=this.$element.find('img');var tooSmall;if(this.options.equalizeOn){tooSmall=this._checkMQ();(0,_jquery2.default)(window).on('changed.zf.mediaquery',this._checkMQ.bind(this));}else{this._events();}if(tooSmall!==undefined&&tooSmall===false||tooSmall===undefined){if(imgs.length){(0,_foundationUtil2.onImagesLoaded)(imgs,this._reflow.bind(this));}else{this._reflow();}}}},{key:'_pauseEvents',value:function _pauseEvents(){this.isOn=false;this.$element.off({'.zf.equalizer':this._bindHandler.onPostEqualizedBound,'resizeme.zf.trigger':this._bindHandler.onResizeMeBound,'mutateme.zf.trigger':this._bindHandler.onResizeMeBound});}},{key:'_onResizeMe',value:function _onResizeMe(e){this._reflow();}},{key:'_onPostEqualized',value:function _onPostEqualized(e){if(e.target!==this.$element[0]){this._reflow();}}},{key:'_events',value:function _events(){var _this=this;this._pauseEvents();if(this.hasNested){this.$element.on('postequalized.zf.equalizer',this._bindHandler.onPostEqualizedBound);}else{this.$element.on('resizeme.zf.trigger',this._bindHandler.onResizeMeBound);this.$element.on('mutateme.zf.trigger',this._bindHandler.onResizeMeBound);}this.isOn=true;}},{key:'_checkMQ',value:function _checkMQ(){var tooSmall=!_foundationUtil.MediaQuery.is(this.options.equalizeOn);if(tooSmall){if(this.isOn){this._pauseEvents();this.$watched.css('height','auto');}}else{if(!this.isOn){this._events();}}return tooSmall;}},{key:'_killswitch',value:function _killswitch(){return;}},{key:'_reflow',value:function _reflow(){if(!this.options.equalizeOnStack){if(this._isStacked()){this.$watched.css('height','auto');return false;}}if(this.options.equalizeByRow){this.getHeightsByRow(this.applyHeightByRow.bind(this));}else{this.getHeights(this.applyHeight.bind(this));}}},{key:'_isStacked',value:function _isStacked(){if(!this.$watched[0]||!this.$watched[1]){return true;}return this.$watched[0].getBoundingClientRect().top!==this.$watched[1].getBoundingClientRect().top;}},{key:'getHeights',value:function getHeights(cb){var heights=[];for(var i=0,len=this.$watched.length;i<len;i++){this.$watched[i].style.height='auto';heights.push(this.$watched[i].offsetHeight);}cb(heights);}},{key:'getHeightsByRow',value:function getHeightsByRow(cb){var lastElTopOffset=this.$watched.length?this.$watched.first().offset().top:0,groups=[],group=0;//group by Row
groups[group]=[];for(var i=0,len=this.$watched.length;i<len;i++){this.$watched[i].style.height='auto';//maybe could use this.$watched[i].offsetTop
var elOffsetTop=(0,_jquery2.default)(this.$watched[i]).offset().top;if(elOffsetTop!=lastElTopOffset){group++;groups[group]=[];lastElTopOffset=elOffsetTop;}groups[group].push([this.$watched[i],this.$watched[i].offsetHeight]);}for(var j=0,ln=groups.length;j<ln;j++){var heights=(0,_jquery2.default)(groups[j]).map(function(){return this[1];}).get();var max=Math.max.apply(null,heights);groups[j].push(max);}cb(groups);}},{key:'applyHeight',value:function applyHeight(heights){var max=Math.max.apply(null,heights);/**
     * Fires before the heights are applied
     * @event Equalizer#preequalized
     */this.$element.trigger('preequalized.zf.equalizer');this.$watched.css('height',max);/**
     * Fires when the heights have been applied
     * @event Equalizer#postequalized
     */this.$element.trigger('postequalized.zf.equalizer');}},{key:'applyHeightByRow',value:function applyHeightByRow(groups){/**
     * Fires before the heights are applied
     */this.$element.trigger('preequalized.zf.equalizer');for(var i=0,len=groups.length;i<len;i++){var groupsILength=groups[i].length,max=groups[i][groupsILength-1];if(groupsILength<=2){(0,_jquery2.default)(groups[i][0][0]).css({'height':'auto'});continue;}/**
        * Fires before the heights per row are applied
        * @event Equalizer#preequalizedrow
        */this.$element.trigger('preequalizedrow.zf.equalizer');for(var j=0,lenJ=groupsILength-1;j<lenJ;j++){(0,_jquery2.default)(groups[i][j][0]).css({'height':max});}/**
        * Fires when the heights per row have been applied
        * @event Equalizer#postequalizedrow
        */this.$element.trigger('postequalizedrow.zf.equalizer');}/**
     * Fires when the heights have been applied
     */this.$element.trigger('postequalized.zf.equalizer');}},{key:'_destroy',value:function _destroy(){this._pauseEvents();this.$watched.css('height','auto');}}]);return Equalizer;}(_foundation.Plugin);/**
 * Default settings for plugin
 */Equalizer.defaults={/**
   * Enable height equalization when stacked on smaller screens.
   * @option
   * @type {boolean}
   * @default false
   */equalizeOnStack:false,/**
   * Enable height equalization row by row.
   * @option
   * @type {boolean}
   * @default false
   */equalizeByRow:false,/**
   * String representing the minimum breakpoint size the plugin should equalize heights on.
   * @option
   * @type {string}
   * @default ''
   */equalizeOn:''};exports.Equalizer=Equalizer;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.mediaQuery','./foundation.plugin','./foundation.util.core'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.mediaQuery'),require('./foundation.plugin'),require('./foundation.util.core'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationInterchange=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundation,_foundationUtil2){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Interchange=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Interchange=function(_Plugin){_inherits(Interchange,_Plugin);function Interchange(){_classCallCheck(this,Interchange);return _possibleConstructorReturn(this,(Interchange.__proto__||Object.getPrototypeOf(Interchange)).apply(this,arguments));}_createClass(Interchange,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Interchange.defaults,options);this.rules=[];this.currentPath='';this.className='Interchange';// ie9 back compat
this._init();this._events();}},{key:'_init',value:function _init(){_foundationUtil.MediaQuery._init();var id=this.$element[0].id||(0,_foundationUtil2.GetYoDigits)(6,'interchange');this.$element.attr({'data-resize':id,'id':id});this._addBreakpoints();this._generateRules();this._reflow();}},{key:'_events',value:function _events(){var _this3=this;this.$element.off('resizeme.zf.trigger').on('resizeme.zf.trigger',function(){return _this3._reflow();});}},{key:'_reflow',value:function _reflow(){var match;// Iterate through each rule, but only save the last match
for(var i in this.rules){if(this.rules.hasOwnProperty(i)){var rule=this.rules[i];if(window.matchMedia(rule.query).matches){match=rule;}}}if(match){this.replace(match.path);}}},{key:'_addBreakpoints',value:function _addBreakpoints(){for(var i in _foundationUtil.MediaQuery.queries){if(_foundationUtil.MediaQuery.queries.hasOwnProperty(i)){var query=_foundationUtil.MediaQuery.queries[i];Interchange.SPECIAL_QUERIES[query.name]=query.value;}}}},{key:'_generateRules',value:function _generateRules(element){var rulesList=[];var rules;if(this.options.rules){rules=this.options.rules;}else{rules=this.$element.data('interchange');}rules=typeof rules==='string'?rules.match(/\[.*?\]/g):rules;for(var i in rules){if(rules.hasOwnProperty(i)){var rule=rules[i].slice(1,-1).split(', ');var path=rule.slice(0,-1).join('');var query=rule[rule.length-1];if(Interchange.SPECIAL_QUERIES[query]){query=Interchange.SPECIAL_QUERIES[query];}rulesList.push({path:path,query:query});}}this.rules=rulesList;}},{key:'replace',value:function replace(path){if(this.currentPath===path)return;var _this=this,trigger='replaced.zf.interchange';// Replacing images
if(this.$element[0].nodeName==='IMG'){this.$element.attr('src',path).on('load',function(){_this.currentPath=path;}).trigger(trigger);}// Replacing background images
else if(path.match(/\.(gif|jpg|jpeg|png|svg|tiff)([?#].*)?/i)){path=path.replace(/\(/g,'%28').replace(/\)/g,'%29');this.$element.css({'background-image':'url('+path+')'}).trigger(trigger);}// Replacing HTML
else{_jquery2.default.get(path,function(response){_this.$element.html(response).trigger(trigger);(0,_jquery2.default)(response).foundation();_this.currentPath=path;});}/**
     * Fires when content in an Interchange element is done being loaded.
     * @event Interchange#replaced
     */// this.$element.trigger('replaced.zf.interchange');
}},{key:'_destroy',value:function _destroy(){this.$element.off('resizeme.zf.trigger');}}]);return Interchange;}(_foundation.Plugin);/**
 * Default settings for plugin
 */Interchange.defaults={/**
   * Rules to be applied to Interchange elements. Set with the `data-interchange` array notation.
   * @option
   * @type {?array}
   * @default null
   */rules:null};Interchange.SPECIAL_QUERIES={'landscape':'screen and (orientation: landscape)','portrait':'screen and (orientation: portrait)','retina':'only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx)'};exports.Interchange=Interchange;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.core','./foundation.plugin','./foundation.smoothScroll'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.core'),require('./foundation.plugin'),require('./foundation.smoothScroll'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundation,global.foundation);global.foundationMagellan=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundation,_foundation2){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Magellan=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Magellan=function(_Plugin){_inherits(Magellan,_Plugin);function Magellan(){_classCallCheck(this,Magellan);return _possibleConstructorReturn(this,(Magellan.__proto__||Object.getPrototypeOf(Magellan)).apply(this,arguments));}_createClass(Magellan,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Magellan.defaults,this.$element.data(),options);this.className='Magellan';// ie9 back compat
this._init();this.calcPoints();}},{key:'_init',value:function _init(){var id=this.$element[0].id||(0,_foundationUtil.GetYoDigits)(6,'magellan');var _this=this;this.$targets=(0,_jquery2.default)('[data-magellan-target]');this.$links=this.$element.find('a');this.$element.attr({'data-resize':id,'data-scroll':id,'id':id});this.$active=(0,_jquery2.default)();this.scrollPos=parseInt(window.pageYOffset,10);this._events();}},{key:'calcPoints',value:function calcPoints(){var _this=this,body=document.body,html=document.documentElement;this.points=[];this.winHeight=Math.round(Math.max(window.innerHeight,html.clientHeight));this.docHeight=Math.round(Math.max(body.scrollHeight,body.offsetHeight,html.clientHeight,html.scrollHeight,html.offsetHeight));this.$targets.each(function(){var $tar=(0,_jquery2.default)(this),pt=Math.round($tar.offset().top-_this.options.threshold);$tar.targetPoint=pt;_this.points.push(pt);});}},{key:'_events',value:function _events(){var _this=this,$body=(0,_jquery2.default)('html, body'),opts={duration:_this.options.animationDuration,easing:_this.options.animationEasing};(0,_jquery2.default)(window).one('load',function(){if(_this.options.deepLinking){if(location.hash){_this.scrollToLoc(location.hash);}}_this.calcPoints();_this._updateActive();});this.$element.on({'resizeme.zf.trigger':this.reflow.bind(this),'scrollme.zf.trigger':this._updateActive.bind(this)}).on('click.zf.magellan','a[href^="#"]',function(e){e.preventDefault();var arrival=this.getAttribute('href');_this.scrollToLoc(arrival);});this._deepLinkScroll=function(e){if(_this.options.deepLinking){_this.scrollToLoc(window.location.hash);}};(0,_jquery2.default)(window).on('popstate',this._deepLinkScroll);}},{key:'scrollToLoc',value:function scrollToLoc(loc){this._inTransition=true;var _this=this;var options={animationEasing:this.options.animationEasing,animationDuration:this.options.animationDuration,threshold:this.options.threshold,offset:this.options.offset};_foundation2.SmoothScroll.scrollToLoc(loc,options,function(){_this._inTransition=false;_this._updateActive();});}},{key:'reflow',value:function reflow(){this.calcPoints();this._updateActive();}},{key:'_updateActive',value:function _updateActive()/*evt, elem, scrollPos*/{if(this._inTransition){return;}var winPos=/*scrollPos ||*/parseInt(window.pageYOffset,10),curIdx;if(winPos+this.winHeight===this.docHeight){curIdx=this.points.length-1;}else if(winPos<this.points[0]){curIdx=undefined;}else{var isDown=this.scrollPos<winPos,_this=this,curVisible=this.points.filter(function(p,i){return isDown?p-_this.options.offset<=winPos:p-_this.options.offset-_this.options.threshold<=winPos;});curIdx=curVisible.length?curVisible.length-1:0;}this.$active.removeClass(this.options.activeClass);this.$active=this.$links.filter('[href="#'+this.$targets.eq(curIdx).data('magellan-target')+'"]').addClass(this.options.activeClass);if(this.options.deepLinking){var hash="";if(curIdx!=undefined){hash=this.$active[0].getAttribute('href');}if(hash!==window.location.hash){if(window.history.pushState){window.history.pushState(null,null,hash);}else{window.location.hash=hash;}}}this.scrollPos=winPos;/**
     * Fires when magellan is finished updating to the new active element.
     * @event Magellan#update
     */this.$element.trigger('update.zf.magellan',[this.$active]);}},{key:'_destroy',value:function _destroy(){this.$element.off('.zf.trigger .zf.magellan').find('.'+this.options.activeClass).removeClass(this.options.activeClass);if(this.options.deepLinking){var hash=this.$active[0].getAttribute('href');window.location.hash.replace(hash,'');}(0,_jquery2.default)(window).off('popstate',this._deepLinkScroll);}}]);return Magellan;}(_foundation.Plugin);/**
 * Default settings for plugin
 */Magellan.defaults={/**
   * Amount of time, in ms, the animated scrolling should take between locations.
   * @option
   * @type {number}
   * @default 500
   */animationDuration:500,/**
   * Animation style to use when scrolling between locations. Can be `'swing'` or `'linear'`.
   * @option
   * @type {string}
   * @default 'linear'
   * @see {@link https://api.jquery.com/animate|Jquery animate}
   */animationEasing:'linear',/**
   * Number of pixels to use as a marker for location changes.
   * @option
   * @type {number}
   * @default 50
   */threshold:50,/**
   * Class applied to the active locations link on the magellan container.
   * @option
   * @type {string}
   * @default 'is-active'
   */activeClass:'is-active',/**
   * Allows the script to manipulate the url of the current page, and if supported, alter the history.
   * @option
   * @type {boolean}
   * @default false
   */deepLinking:false,/**
   * Number of pixels to offset the scroll of the page on item click if using a sticky nav bar.
   * @option
   * @type {number}
   * @default 0
   */offset:0};exports.Magellan=Magellan;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.mediaQuery','./foundation.util.core','./foundation.plugin','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.mediaQuery'),require('./foundation.util.core'),require('./foundation.plugin'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationOffcanvas=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation,_foundationUtil4){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.OffCanvas=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var OffCanvas=function(_Plugin){_inherits(OffCanvas,_Plugin);function OffCanvas(){_classCallCheck(this,OffCanvas);return _possibleConstructorReturn(this,(OffCanvas.__proto__||Object.getPrototypeOf(OffCanvas)).apply(this,arguments));}_createClass(OffCanvas,[{key:'_setup',value:function _setup(element,options){var _this3=this;this.className='OffCanvas';// ie9 back compat
this.$element=element;this.options=_jquery2.default.extend({},OffCanvas.defaults,this.$element.data(),options);this.contentClasses={base:[],reveal:[]};this.$lastTrigger=(0,_jquery2.default)();this.$triggers=(0,_jquery2.default)();this.position='left';this.$content=(0,_jquery2.default)();this.nested=!!this.options.nested;// Defines the CSS transition/position classes of the off-canvas content container.
(0,_jquery2.default)(['push','overlap']).each(function(index,val){_this3.contentClasses.base.push('has-transition-'+val);});(0,_jquery2.default)(['left','right','top','bottom']).each(function(index,val){_this3.contentClasses.base.push('has-position-'+val);_this3.contentClasses.reveal.push('has-reveal-'+val);});// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil4.Triggers.init(_jquery2.default);_foundationUtil2.MediaQuery._init();this._init();this._events();_foundationUtil.Keyboard.register('OffCanvas',{'ESCAPE':'close'});}},{key:'_init',value:function _init(){var id=this.$element.attr('id');this.$element.attr('aria-hidden','true');// Find off-canvas content, either by ID (if specified), by siblings or by closest selector (fallback)
if(this.options.contentId){this.$content=(0,_jquery2.default)('#'+this.options.contentId);}else if(this.$element.siblings('[data-off-canvas-content]').length){this.$content=this.$element.siblings('[data-off-canvas-content]').first();}else{this.$content=this.$element.closest('[data-off-canvas-content]').first();}if(!this.options.contentId){// Assume that the off-canvas element is nested if it isn't a sibling of the content
this.nested=this.$element.siblings('[data-off-canvas-content]').length===0;}else if(this.options.contentId&&this.options.nested===null){// Warning if using content ID without setting the nested option
// Once the element is nested it is required to work properly in this case
console.warn('Remember to use the nested option if using the content ID option!');}if(this.nested===true){// Force transition overlap if nested
this.options.transition='overlap';// Remove appropriate classes if already assigned in markup
this.$element.removeClass('is-transition-push');}this.$element.addClass('is-transition-'+this.options.transition+' is-closed');// Find triggers that affect this element and add aria-expanded to them
this.$triggers=(0,_jquery2.default)(document).find('[data-open="'+id+'"], [data-close="'+id+'"], [data-toggle="'+id+'"]').attr('aria-expanded','false').attr('aria-controls',id);// Get position by checking for related CSS class
this.position=this.$element.is('.position-left, .position-top, .position-right, .position-bottom')?this.$element.attr('class').match(/position\-(left|top|right|bottom)/)[1]:this.position;// Add an overlay over the content if necessary
if(this.options.contentOverlay===true){var overlay=document.createElement('div');var overlayPosition=(0,_jquery2.default)(this.$element).css("position")==='fixed'?'is-overlay-fixed':'is-overlay-absolute';overlay.setAttribute('class','js-off-canvas-overlay '+overlayPosition);this.$overlay=(0,_jquery2.default)(overlay);if(overlayPosition==='is-overlay-fixed'){(0,_jquery2.default)(this.$overlay).insertAfter(this.$element);}else{this.$content.append(this.$overlay);}}this.options.isRevealed=this.options.isRevealed||new RegExp(this.options.revealClass,'g').test(this.$element[0].className);if(this.options.isRevealed===true){this.options.revealOn=this.options.revealOn||this.$element[0].className.match(/(reveal-for-medium|reveal-for-large)/g)[0].split('-')[2];this._setMQChecker();}if(this.options.transitionTime){this.$element.css('transition-duration',this.options.transitionTime);}// Initally remove all transition/position CSS classes from off-canvas content container.
this._removeContentClasses();}},{key:'_events',value:function _events(){this.$element.off('.zf.trigger .zf.offcanvas').on({'open.zf.trigger':this.open.bind(this),'close.zf.trigger':this.close.bind(this),'toggle.zf.trigger':this.toggle.bind(this),'keydown.zf.offcanvas':this._handleKeyboard.bind(this)});if(this.options.closeOnClick===true){var $target=this.options.contentOverlay?this.$overlay:this.$content;$target.on({'click.zf.offcanvas':this.close.bind(this)});}}},{key:'_setMQChecker',value:function _setMQChecker(){var _this=this;(0,_jquery2.default)(window).on('changed.zf.mediaquery',function(){if(_foundationUtil2.MediaQuery.atLeast(_this.options.revealOn)){_this.reveal(true);}else{_this.reveal(false);}}).one('load.zf.offcanvas',function(){if(_foundationUtil2.MediaQuery.atLeast(_this.options.revealOn)){_this.reveal(true);}});}},{key:'_removeContentClasses',value:function _removeContentClasses(hasReveal){if(typeof hasReveal!=='boolean'){this.$content.removeClass(this.contentClasses.base.join(' '));}else if(hasReveal===false){this.$content.removeClass('has-reveal-'+this.position);}}},{key:'_addContentClasses',value:function _addContentClasses(hasReveal){this._removeContentClasses(hasReveal);if(typeof hasReveal!=='boolean'){this.$content.addClass('has-transition-'+this.options.transition+' has-position-'+this.position);}else if(hasReveal===true){this.$content.addClass('has-reveal-'+this.position);}}},{key:'reveal',value:function reveal(isRevealed){if(isRevealed){this.close();this.isRevealed=true;this.$element.attr('aria-hidden','false');this.$element.off('open.zf.trigger toggle.zf.trigger');this.$element.removeClass('is-closed');}else{this.isRevealed=false;this.$element.attr('aria-hidden','true');this.$element.off('open.zf.trigger toggle.zf.trigger').on({'open.zf.trigger':this.open.bind(this),'toggle.zf.trigger':this.toggle.bind(this)});this.$element.addClass('is-closed');}this._addContentClasses(isRevealed);}},{key:'_stopScrolling',value:function _stopScrolling(event){return false;}},{key:'_recordScrollable',value:function _recordScrollable(event){var elem=this;// called from event handler context with this as elem
// If the element is scrollable (content overflows), then...
if(elem.scrollHeight!==elem.clientHeight){// If we're at the top, scroll down one pixel to allow scrolling up
if(elem.scrollTop===0){elem.scrollTop=1;}// If we're at the bottom, scroll up one pixel to allow scrolling down
if(elem.scrollTop===elem.scrollHeight-elem.clientHeight){elem.scrollTop=elem.scrollHeight-elem.clientHeight-1;}}elem.allowUp=elem.scrollTop>0;elem.allowDown=elem.scrollTop<elem.scrollHeight-elem.clientHeight;elem.lastY=event.originalEvent.pageY;}},{key:'_stopScrollPropagation',value:function _stopScrollPropagation(event){var elem=this;// called from event handler context with this as elem
var up=event.pageY<elem.lastY;var down=!up;elem.lastY=event.pageY;if(up&&elem.allowUp||down&&elem.allowDown){event.stopPropagation();}else{event.preventDefault();}}},{key:'open',value:function open(event,trigger){if(this.$element.hasClass('is-open')||this.isRevealed){return;}var _this=this;if(trigger){this.$lastTrigger=trigger;}if(this.options.forceTo==='top'){window.scrollTo(0,0);}else if(this.options.forceTo==='bottom'){window.scrollTo(0,document.body.scrollHeight);}if(this.options.transitionTime&&this.options.transition!=='overlap'){this.$element.siblings('[data-off-canvas-content]').css('transition-duration',this.options.transitionTime);}else{this.$element.siblings('[data-off-canvas-content]').css('transition-duration','');}/**
     * Fires when the off-canvas menu opens.
     * @event OffCanvas#opened
     */this.$element.addClass('is-open').removeClass('is-closed');this.$triggers.attr('aria-expanded','true');this.$element.attr('aria-hidden','false').trigger('opened.zf.offcanvas');this.$content.addClass('is-open-'+this.position);// If `contentScroll` is set to false, add class and disable scrolling on touch devices.
if(this.options.contentScroll===false){(0,_jquery2.default)('body').addClass('is-off-canvas-open').on('touchmove',this._stopScrolling);this.$element.on('touchstart',this._recordScrollable);this.$element.on('touchmove',this._stopScrollPropagation);}if(this.options.contentOverlay===true){this.$overlay.addClass('is-visible');}if(this.options.closeOnClick===true&&this.options.contentOverlay===true){this.$overlay.addClass('is-closable');}if(this.options.autoFocus===true){this.$element.one((0,_foundationUtil3.transitionend)(this.$element),function(){if(!_this.$element.hasClass('is-open')){return;// exit if prematurely closed
}var canvasFocus=_this.$element.find('[data-autofocus]');if(canvasFocus.length){canvasFocus.eq(0).focus();}else{_this.$element.find('a, button').eq(0).focus();}});}if(this.options.trapFocus===true){this.$content.attr('tabindex','-1');_foundationUtil.Keyboard.trapFocus(this.$element);}this._addContentClasses();}},{key:'close',value:function close(cb){if(!this.$element.hasClass('is-open')||this.isRevealed){return;}var _this=this;this.$element.removeClass('is-open');this.$element.attr('aria-hidden','true')/**
       * Fires when the off-canvas menu opens.
       * @event OffCanvas#closed
       */.trigger('closed.zf.offcanvas');this.$content.removeClass('is-open-left is-open-top is-open-right is-open-bottom');// If `contentScroll` is set to false, remove class and re-enable scrolling on touch devices.
if(this.options.contentScroll===false){(0,_jquery2.default)('body').removeClass('is-off-canvas-open').off('touchmove',this._stopScrolling);this.$element.off('touchstart',this._recordScrollable);this.$element.off('touchmove',this._stopScrollPropagation);}if(this.options.contentOverlay===true){this.$overlay.removeClass('is-visible');}if(this.options.closeOnClick===true&&this.options.contentOverlay===true){this.$overlay.removeClass('is-closable');}this.$triggers.attr('aria-expanded','false');if(this.options.trapFocus===true){this.$content.removeAttr('tabindex');_foundationUtil.Keyboard.releaseFocus(this.$element);}// Listen to transitionEnd and add class when done.
this.$element.one((0,_foundationUtil3.transitionend)(this.$element),function(e){_this.$element.addClass('is-closed');_this._removeContentClasses();});}},{key:'toggle',value:function toggle(event,trigger){if(this.$element.hasClass('is-open')){this.close(event,trigger);}else{this.open(event,trigger);}}},{key:'_handleKeyboard',value:function _handleKeyboard(e){var _this4=this;_foundationUtil.Keyboard.handleKey(e,'OffCanvas',{close:function close(){_this4.close();_this4.$lastTrigger.focus();return true;},handled:function handled(){e.stopPropagation();e.preventDefault();}});}},{key:'_destroy',value:function _destroy(){this.close();this.$element.off('.zf.trigger .zf.offcanvas');this.$overlay.off('.zf.offcanvas');}}]);return OffCanvas;}(_foundation.Plugin);OffCanvas.defaults={/**
   * Allow the user to click outside of the menu to close it.
   * @option
   * @type {boolean}
   * @default true
   */closeOnClick:true,/**
   * Adds an overlay on top of `[data-off-canvas-content]`.
   * @option
   * @type {boolean}
   * @default true
   */contentOverlay:true,/**
   * Target an off-canvas content container by ID that may be placed anywhere. If null the closest content container will be taken.
   * @option
   * @type {?string}
   * @default null
   */contentId:null,/**
   * Define the off-canvas element is nested in an off-canvas content. This is required when using the contentId option for a nested element.
   * @option
   * @type {boolean}
   * @default null
   */nested:null,/**
   * Enable/disable scrolling of the main content when an off canvas panel is open.
   * @option
   * @type {boolean}
   * @default true
   */contentScroll:true,/**
   * Amount of time in ms the open and close transition requires. If none selected, pulls from body style.
   * @option
   * @type {number}
   * @default null
   */transitionTime:null,/**
   * Type of transition for the offcanvas menu. Options are 'push', 'detached' or 'slide'.
   * @option
   * @type {string}
   * @default push
   */transition:'push',/**
   * Force the page to scroll to top or bottom on open.
   * @option
   * @type {?string}
   * @default null
   */forceTo:null,/**
   * Allow the offcanvas to remain open for certain breakpoints.
   * @option
   * @type {boolean}
   * @default false
   */isRevealed:false,/**
   * Breakpoint at which to reveal. JS will use a RegExp to target standard classes, if changing classnames, pass your class with the `revealClass` option.
   * @option
   * @type {?string}
   * @default null
   */revealOn:null,/**
   * Force focus to the offcanvas on open. If true, will focus the opening trigger on close.
   * @option
   * @type {boolean}
   * @default true
   */autoFocus:true,/**
   * Class used to force an offcanvas to remain open. Foundation defaults for this are `reveal-for-large` & `reveal-for-medium`.
   * @option
   * @type {string}
   * @default reveal-for-
   * @todo improve the regex testing for this.
   */revealClass:'reveal-for-',/**
   * Triggers optional focus trapping when opening an offcanvas. Sets tabindex of [data-off-canvas-content] to -1 for accessibility purposes.
   * @option
   * @type {boolean}
   * @default false
   */trapFocus:false};exports.OffCanvas=OffCanvas;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.motion','./foundation.util.timer','./foundation.util.imageLoader','./foundation.util.core','./foundation.plugin','./foundation.util.touch'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.motion'),require('./foundation.util.timer'),require('./foundation.util.imageLoader'),require('./foundation.util.core'),require('./foundation.plugin'),require('./foundation.util.touch'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationOrbit=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundationUtil4,_foundationUtil5,_foundation,_foundationUtil6){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Orbit=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Orbit=function(_Plugin){_inherits(Orbit,_Plugin);function Orbit(){_classCallCheck(this,Orbit);return _possibleConstructorReturn(this,(Orbit.__proto__||Object.getPrototypeOf(Orbit)).apply(this,arguments));}_createClass(Orbit,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Orbit.defaults,this.$element.data(),options);this.className='Orbit';// ie9 back compat
_foundationUtil6.Touch.init(_jquery2.default);// Touch init is idempotent, we just need to make sure it's initialied.
this._init();_foundationUtil.Keyboard.register('Orbit',{'ltr':{'ARROW_RIGHT':'next','ARROW_LEFT':'previous'},'rtl':{'ARROW_LEFT':'next','ARROW_RIGHT':'previous'}});}},{key:'_init',value:function _init(){// @TODO: consider discussion on PR #9278 about DOM pollution by changeSlide
this._reset();this.$wrapper=this.$element.find('.'+this.options.containerClass);this.$slides=this.$element.find('.'+this.options.slideClass);var $images=this.$element.find('img'),initActive=this.$slides.filter('.is-active'),id=this.$element[0].id||(0,_foundationUtil5.GetYoDigits)(6,'orbit');this.$element.attr({'data-resize':id,'id':id});if(!initActive.length){this.$slides.eq(0).addClass('is-active');}if(!this.options.useMUI){this.$slides.addClass('no-motionui');}if($images.length){(0,_foundationUtil4.onImagesLoaded)($images,this._prepareForOrbit.bind(this));}else{this._prepareForOrbit();//hehe
}if(this.options.bullets){this._loadBullets();}this._events();if(this.options.autoPlay&&this.$slides.length>1){this.geoSync();}if(this.options.accessible){// allow wrapper to be focusable to enable arrow navigation
this.$wrapper.attr('tabindex',0);}}},{key:'_loadBullets',value:function _loadBullets(){this.$bullets=this.$element.find('.'+this.options.boxOfBullets).find('button');}},{key:'geoSync',value:function geoSync(){var _this=this;this.timer=new _foundationUtil3.Timer(this.$element,{duration:this.options.timerDelay,infinite:false},function(){_this.changeSlide(true);});this.timer.start();}},{key:'_prepareForOrbit',value:function _prepareForOrbit(){var _this=this;this._setWrapperHeight();}},{key:'_setWrapperHeight',value:function _setWrapperHeight(cb){//rewrite this to `for` loop
var max=0,temp,counter=0,_this=this;this.$slides.each(function(){temp=this.getBoundingClientRect().height;(0,_jquery2.default)(this).attr('data-slide',counter);if(!/mui/g.test((0,_jquery2.default)(this)[0].className)&&_this.$slides.filter('.is-active')[0]!==_this.$slides.eq(counter)[0]){//if not the active slide, set css position and display property
(0,_jquery2.default)(this).css({'position':'relative','display':'none'});}max=temp>max?temp:max;counter++;});if(counter===this.$slides.length){this.$wrapper.css({'height':max});//only change the wrapper height property once.
if(cb){cb(max);}//fire callback with max height dimension.
}}},{key:'_setSlideHeight',value:function _setSlideHeight(height){this.$slides.each(function(){(0,_jquery2.default)(this).css('max-height',height);});}},{key:'_events',value:function _events(){var _this=this;//***************************************
//**Now using custom event - thanks to:**
//**      Yohai Ararat of Toronto      **
//***************************************
//
this.$element.off('.resizeme.zf.trigger').on({'resizeme.zf.trigger':this._prepareForOrbit.bind(this)});if(this.$slides.length>1){if(this.options.swipe){this.$slides.off('swipeleft.zf.orbit swiperight.zf.orbit').on('swipeleft.zf.orbit',function(e){e.preventDefault();_this.changeSlide(true);}).on('swiperight.zf.orbit',function(e){e.preventDefault();_this.changeSlide(false);});}//***************************************
if(this.options.autoPlay){this.$slides.on('click.zf.orbit',function(){_this.$element.data('clickedOn',_this.$element.data('clickedOn')?false:true);_this.timer[_this.$element.data('clickedOn')?'pause':'start']();});if(this.options.pauseOnHover){this.$element.on('mouseenter.zf.orbit',function(){_this.timer.pause();}).on('mouseleave.zf.orbit',function(){if(!_this.$element.data('clickedOn')){_this.timer.start();}});}}if(this.options.navButtons){var $controls=this.$element.find('.'+this.options.nextClass+', .'+this.options.prevClass);$controls.attr('tabindex',0)//also need to handle enter/return and spacebar key presses
.on('click.zf.orbit touchend.zf.orbit',function(e){e.preventDefault();_this.changeSlide((0,_jquery2.default)(this).hasClass(_this.options.nextClass));});}if(this.options.bullets){this.$bullets.on('click.zf.orbit touchend.zf.orbit',function(){if(/is-active/g.test(this.className)){return false;}//if this is active, kick out of function.
var idx=(0,_jquery2.default)(this).data('slide'),ltr=idx>_this.$slides.filter('.is-active').data('slide'),$slide=_this.$slides.eq(idx);_this.changeSlide(ltr,$slide,idx);});}if(this.options.accessible){this.$wrapper.add(this.$bullets).on('keydown.zf.orbit',function(e){// handle keyboard event with keyboard util
_foundationUtil.Keyboard.handleKey(e,'Orbit',{next:function next(){_this.changeSlide(true);},previous:function previous(){_this.changeSlide(false);},handled:function handled(){// if bullet is focused, make sure focus moves
if((0,_jquery2.default)(e.target).is(_this.$bullets)){_this.$bullets.filter('.is-active').focus();}}});});}}}},{key:'_reset',value:function _reset(){// Don't do anything if there are no slides (first run)
if(typeof this.$slides=='undefined'){return;}if(this.$slides.length>1){// Remove old events
this.$element.off('.zf.orbit').find('*').off('.zf.orbit');// Restart timer if autoPlay is enabled
if(this.options.autoPlay){this.timer.restart();}// Reset all sliddes
this.$slides.each(function(el){(0,_jquery2.default)(el).removeClass('is-active is-active is-in').removeAttr('aria-live').hide();});// Show the first slide
this.$slides.first().addClass('is-active').show();// Triggers when the slide has finished animating
this.$element.trigger('slidechange.zf.orbit',[this.$slides.first()]);// Select first bullet if bullets are present
if(this.options.bullets){this._updateBullets(0);}}}},{key:'changeSlide',value:function changeSlide(isLTR,chosenSlide,idx){if(!this.$slides){return;}// Don't freak out if we're in the middle of cleanup
var $curSlide=this.$slides.filter('.is-active').eq(0);if(/mui/g.test($curSlide[0].className)){return false;}//if the slide is currently animating, kick out of the function
var $firstSlide=this.$slides.first(),$lastSlide=this.$slides.last(),dirIn=isLTR?'Right':'Left',dirOut=isLTR?'Left':'Right',_this=this,$newSlide;if(!chosenSlide){//most of the time, this will be auto played or clicked from the navButtons.
$newSlide=isLTR?//if wrapping enabled, check to see if there is a `next` or `prev` sibling, if not, select the first or last slide to fill in. if wrapping not enabled, attempt to select `next` or `prev`, if there's nothing there, the function will kick out on next step. CRAZY NESTED TERNARIES!!!!!
this.options.infiniteWrap?$curSlide.next('.'+this.options.slideClass).length?$curSlide.next('.'+this.options.slideClass):$firstSlide:$curSlide.next('.'+this.options.slideClass)://pick next slide if moving left to right
this.options.infiniteWrap?$curSlide.prev('.'+this.options.slideClass).length?$curSlide.prev('.'+this.options.slideClass):$lastSlide:$curSlide.prev('.'+this.options.slideClass);//pick prev slide if moving right to left
}else{$newSlide=chosenSlide;}if($newSlide.length){/**
      * Triggers before the next slide starts animating in and only if a next slide has been found.
      * @event Orbit#beforeslidechange
      */this.$element.trigger('beforeslidechange.zf.orbit',[$curSlide,$newSlide]);if(this.options.bullets){idx=idx||this.$slides.index($newSlide);//grab index to update bullets
this._updateBullets(idx);}if(this.options.useMUI&&!this.$element.is(':hidden')){_foundationUtil2.Motion.animateIn($newSlide.addClass('is-active').css({'position':'absolute','top':0}),this.options['animInFrom'+dirIn],function(){$newSlide.css({'position':'relative','display':'block'}).attr('aria-live','polite');});_foundationUtil2.Motion.animateOut($curSlide.removeClass('is-active'),this.options['animOutTo'+dirOut],function(){$curSlide.removeAttr('aria-live');if(_this.options.autoPlay&&!_this.timer.isPaused){_this.timer.restart();}//do stuff?
});}else{$curSlide.removeClass('is-active is-in').removeAttr('aria-live').hide();$newSlide.addClass('is-active is-in').attr('aria-live','polite').show();if(this.options.autoPlay&&!this.timer.isPaused){this.timer.restart();}}/**
    * Triggers when the slide has finished animating in.
    * @event Orbit#slidechange
    */this.$element.trigger('slidechange.zf.orbit',[$newSlide]);}}},{key:'_updateBullets',value:function _updateBullets(idx){var $oldBullet=this.$element.find('.'+this.options.boxOfBullets).find('.is-active').removeClass('is-active').blur(),span=$oldBullet.find('span:last').detach(),$newBullet=this.$bullets.eq(idx).addClass('is-active').append(span);}},{key:'_destroy',value:function _destroy(){this.$element.off('.zf.orbit').find('*').off('.zf.orbit').end().hide();}}]);return Orbit;}(_foundation.Plugin);Orbit.defaults={/**
  * Tells the JS to look for and loadBullets.
  * @option
   * @type {boolean}
  * @default true
  */bullets:true,/**
  * Tells the JS to apply event listeners to nav buttons
  * @option
   * @type {boolean}
  * @default true
  */navButtons:true,/**
  * motion-ui animation class to apply
  * @option
   * @type {string}
  * @default 'slide-in-right'
  */animInFromRight:'slide-in-right',/**
  * motion-ui animation class to apply
  * @option
   * @type {string}
  * @default 'slide-out-right'
  */animOutToRight:'slide-out-right',/**
  * motion-ui animation class to apply
  * @option
   * @type {string}
  * @default 'slide-in-left'
  *
  */animInFromLeft:'slide-in-left',/**
  * motion-ui animation class to apply
  * @option
   * @type {string}
  * @default 'slide-out-left'
  */animOutToLeft:'slide-out-left',/**
  * Allows Orbit to automatically animate on page load.
  * @option
   * @type {boolean}
  * @default true
  */autoPlay:true,/**
  * Amount of time, in ms, between slide transitions
  * @option
   * @type {number}
  * @default 5000
  */timerDelay:5000,/**
  * Allows Orbit to infinitely loop through the slides
  * @option
   * @type {boolean}
  * @default true
  */infiniteWrap:true,/**
  * Allows the Orbit slides to bind to swipe events for mobile, requires an additional util library
  * @option
   * @type {boolean}
  * @default true
  */swipe:true,/**
  * Allows the timing function to pause animation on hover.
  * @option
   * @type {boolean}
  * @default true
  */pauseOnHover:true,/**
  * Allows Orbit to bind keyboard events to the slider, to animate frames with arrow keys
  * @option
   * @type {boolean}
  * @default true
  */accessible:true,/**
  * Class applied to the container of Orbit
  * @option
   * @type {string}
  * @default 'orbit-container'
  */containerClass:'orbit-container',/**
  * Class applied to individual slides.
  * @option
   * @type {string}
  * @default 'orbit-slide'
  */slideClass:'orbit-slide',/**
  * Class applied to the bullet container. You're welcome.
  * @option
   * @type {string}
  * @default 'orbit-bullets'
  */boxOfBullets:'orbit-bullets',/**
  * Class applied to the `next` navigation button.
  * @option
   * @type {string}
  * @default 'orbit-next'
  */nextClass:'orbit-next',/**
  * Class applied to the `previous` navigation button.
  * @option
   * @type {string}
  * @default 'orbit-previous'
  */prevClass:'orbit-previous',/**
  * Boolean to flag the js to use motion ui classes or not. Default to true for backwards compatability.
  * @option
   * @type {boolean}
  * @default true
  */useMUI:true};exports.Orbit=Orbit;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.mediaQuery','./foundation.util.core','./foundation.plugin','./foundation.dropdownMenu','./foundation.drilldown','./foundation.accordionMenu'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.mediaQuery'),require('./foundation.util.core'),require('./foundation.plugin'),require('./foundation.dropdownMenu'),require('./foundation.drilldown'),require('./foundation.accordionMenu'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation,global.foundation,global.foundation,global.foundation);global.foundationResponsiveMenu=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation,_foundation2,_foundation3,_foundation4){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.ResponsiveMenu=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var MenuPlugins={dropdown:{cssClass:'dropdown',plugin:_foundation2.DropdownMenu},drilldown:{cssClass:'drilldown',plugin:_foundation3.Drilldown},accordion:{cssClass:'accordion-menu',plugin:_foundation4.AccordionMenu}};// import "foundation.util.triggers.js";
/**
 * ResponsiveMenu module.
 * @module foundation.responsiveMenu
 * @requires foundation.util.triggers
 * @requires foundation.util.mediaQuery
 */var ResponsiveMenu=function(_Plugin){_inherits(ResponsiveMenu,_Plugin);function ResponsiveMenu(){_classCallCheck(this,ResponsiveMenu);return _possibleConstructorReturn(this,(ResponsiveMenu.__proto__||Object.getPrototypeOf(ResponsiveMenu)).apply(this,arguments));}_createClass(ResponsiveMenu,[{key:'_setup',value:function _setup(element,options){this.$element=(0,_jquery2.default)(element);this.rules=this.$element.data('responsive-menu');this.currentMq=null;this.currentPlugin=null;this.className='ResponsiveMenu';// ie9 back compat
this._init();this._events();}},{key:'_init',value:function _init(){_foundationUtil.MediaQuery._init();// The first time an Interchange plugin is initialized, this.rules is converted from a string of "classes" to an object of rules
if(typeof this.rules==='string'){var rulesTree={};// Parse rules from "classes" pulled from data attribute
var rules=this.rules.split(' ');// Iterate through every rule found
for(var i=0;i<rules.length;i++){var rule=rules[i].split('-');var ruleSize=rule.length>1?rule[0]:'small';var rulePlugin=rule.length>1?rule[1]:rule[0];if(MenuPlugins[rulePlugin]!==null){rulesTree[ruleSize]=MenuPlugins[rulePlugin];}}this.rules=rulesTree;}if(!_jquery2.default.isEmptyObject(this.rules)){this._checkMediaQueries();}// Add data-mutate since children may need it.
this.$element.attr('data-mutate',this.$element.attr('data-mutate')||(0,_foundationUtil2.GetYoDigits)(6,'responsive-menu'));}},{key:'_events',value:function _events(){var _this=this;(0,_jquery2.default)(window).on('changed.zf.mediaquery',function(){_this._checkMediaQueries();});// $(window).on('resize.zf.ResponsiveMenu', function() {
//   _this._checkMediaQueries();
// });
}},{key:'_checkMediaQueries',value:function _checkMediaQueries(){var matchedMq,_this=this;// Iterate through each rule and find the last matching rule
_jquery2.default.each(this.rules,function(key){if(_foundationUtil.MediaQuery.atLeast(key)){matchedMq=key;}});// No match? No dice
if(!matchedMq)return;// Plugin already initialized? We good
if(this.currentPlugin instanceof this.rules[matchedMq].plugin)return;// Remove existing plugin-specific CSS classes
_jquery2.default.each(MenuPlugins,function(key,value){_this.$element.removeClass(value.cssClass);});// Add the CSS class for the new plugin
this.$element.addClass(this.rules[matchedMq].cssClass);// Create an instance of the new plugin
if(this.currentPlugin)this.currentPlugin.destroy();this.currentPlugin=new this.rules[matchedMq].plugin(this.$element,{});}},{key:'_destroy',value:function _destroy(){this.currentPlugin.destroy();(0,_jquery2.default)(window).off('.zf.ResponsiveMenu');}}]);return ResponsiveMenu;}(_foundation.Plugin);ResponsiveMenu.defaults={};exports.ResponsiveMenu=ResponsiveMenu;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.mediaQuery','./foundation.util.motion','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.mediaQuery'),require('./foundation.util.motion'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationResponsiveToggle=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.ResponsiveToggle=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var ResponsiveToggle=function(_Plugin){_inherits(ResponsiveToggle,_Plugin);function ResponsiveToggle(){_classCallCheck(this,ResponsiveToggle);return _possibleConstructorReturn(this,(ResponsiveToggle.__proto__||Object.getPrototypeOf(ResponsiveToggle)).apply(this,arguments));}_createClass(ResponsiveToggle,[{key:'_setup',value:function _setup(element,options){this.$element=(0,_jquery2.default)(element);this.options=_jquery2.default.extend({},ResponsiveToggle.defaults,this.$element.data(),options);this.className='ResponsiveToggle';// ie9 back compat
this._init();this._events();}},{key:'_init',value:function _init(){_foundationUtil.MediaQuery._init();var targetID=this.$element.data('responsive-toggle');if(!targetID){console.error('Your tab bar needs an ID of a Menu as the value of data-tab-bar.');}this.$targetMenu=(0,_jquery2.default)('#'+targetID);this.$toggler=this.$element.find('[data-toggle]').filter(function(){var target=(0,_jquery2.default)(this).data('toggle');return target===targetID||target==="";});this.options=_jquery2.default.extend({},this.options,this.$targetMenu.data());// If they were set, parse the animation classes
if(this.options.animate){var input=this.options.animate.split(' ');this.animationIn=input[0];this.animationOut=input[1]||null;}this._update();}},{key:'_events',value:function _events(){var _this=this;this._updateMqHandler=this._update.bind(this);(0,_jquery2.default)(window).on('changed.zf.mediaquery',this._updateMqHandler);this.$toggler.on('click.zf.responsiveToggle',this.toggleMenu.bind(this));}},{key:'_update',value:function _update(){// Mobile
if(!_foundationUtil.MediaQuery.atLeast(this.options.hideFor)){this.$element.show();this.$targetMenu.hide();}// Desktop
else{this.$element.hide();this.$targetMenu.show();}}},{key:'toggleMenu',value:function toggleMenu(){var _this3=this;if(!_foundationUtil.MediaQuery.atLeast(this.options.hideFor)){/**
       * Fires when the element attached to the tab bar toggles.
       * @event ResponsiveToggle#toggled
       */if(this.options.animate){if(this.$targetMenu.is(':hidden')){_foundationUtil2.Motion.animateIn(this.$targetMenu,this.animationIn,function(){_this3.$element.trigger('toggled.zf.responsiveToggle');_this3.$targetMenu.find('[data-mutate]').triggerHandler('mutateme.zf.trigger');});}else{_foundationUtil2.Motion.animateOut(this.$targetMenu,this.animationOut,function(){_this3.$element.trigger('toggled.zf.responsiveToggle');});}}else{this.$targetMenu.toggle(0);this.$targetMenu.find('[data-mutate]').trigger('mutateme.zf.trigger');this.$element.trigger('toggled.zf.responsiveToggle');}}}},{key:'_destroy',value:function _destroy(){this.$element.off('.zf.responsiveToggle');this.$toggler.off('.zf.responsiveToggle');(0,_jquery2.default)(window).off('changed.zf.mediaquery',this._updateMqHandler);}}]);return ResponsiveToggle;}(_foundation.Plugin);ResponsiveToggle.defaults={/**
   * The breakpoint after which the menu is always shown, and the tab bar is hidden.
   * @option
   * @type {string}
   * @default 'medium'
   */hideFor:'medium',/**
   * To decide if the toggle should be animated or not.
   * @option
   * @type {boolean}
   * @default false
   */animate:false};exports.ResponsiveToggle=ResponsiveToggle;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.mediaQuery','./foundation.util.motion','./foundation.plugin','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.mediaQuery'),require('./foundation.util.motion'),require('./foundation.plugin'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationReveal=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation,_foundationUtil4){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Reveal=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Reveal=function(_Plugin){_inherits(Reveal,_Plugin);function Reveal(){_classCallCheck(this,Reveal);return _possibleConstructorReturn(this,(Reveal.__proto__||Object.getPrototypeOf(Reveal)).apply(this,arguments));}_createClass(Reveal,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Reveal.defaults,this.$element.data(),options);this.className='Reveal';// ie9 back compat
this._init();// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil4.Triggers.init(_jquery2.default);_foundationUtil.Keyboard.register('Reveal',{'ESCAPE':'close'});}},{key:'_init',value:function _init(){_foundationUtil2.MediaQuery._init();this.id=this.$element.attr('id');this.isActive=false;this.cached={mq:_foundationUtil2.MediaQuery.current};this.isMobile=mobileSniff();this.$anchor=(0,_jquery2.default)('[data-open="'+this.id+'"]').length?(0,_jquery2.default)('[data-open="'+this.id+'"]'):(0,_jquery2.default)('[data-toggle="'+this.id+'"]');this.$anchor.attr({'aria-controls':this.id,'aria-haspopup':true,'tabindex':0});if(this.options.fullScreen||this.$element.hasClass('full')){this.options.fullScreen=true;this.options.overlay=false;}if(this.options.overlay&&!this.$overlay){this.$overlay=this._makeOverlay(this.id);}this.$element.attr({'role':'dialog','aria-hidden':true,'data-yeti-box':this.id,'data-resize':this.id});if(this.$overlay){this.$element.detach().appendTo(this.$overlay);}else{this.$element.detach().appendTo((0,_jquery2.default)(this.options.appendTo));this.$element.addClass('without-overlay');}this._events();if(this.options.deepLink&&window.location.hash==='#'+this.id){(0,_jquery2.default)(window).one('load.zf.reveal',this.open.bind(this));}}},{key:'_makeOverlay',value:function _makeOverlay(){var additionalOverlayClasses='';if(this.options.additionalOverlayClasses){additionalOverlayClasses=' '+this.options.additionalOverlayClasses;}return(0,_jquery2.default)('<div></div>').addClass('reveal-overlay'+additionalOverlayClasses).appendTo(this.options.appendTo);}},{key:'_updatePosition',value:function _updatePosition(){var width=this.$element.outerWidth();var outerWidth=(0,_jquery2.default)(window).width();var height=this.$element.outerHeight();var outerHeight=(0,_jquery2.default)(window).height();var left,top;if(this.options.hOffset==='auto'){left=parseInt((outerWidth-width)/2,10);}else{left=parseInt(this.options.hOffset,10);}if(this.options.vOffset==='auto'){if(height>outerHeight){top=parseInt(Math.min(100,outerHeight/10),10);}else{top=parseInt((outerHeight-height)/4,10);}}else{top=parseInt(this.options.vOffset,10);}this.$element.css({top:top+'px'});// only worry about left if we don't have an overlay or we havea  horizontal offset,
// otherwise we're perfectly in the middle
if(!this.$overlay||this.options.hOffset!=='auto'){this.$element.css({left:left+'px'});this.$element.css({margin:'0px'});}}},{key:'_events',value:function _events(){var _this3=this;var _this=this;this.$element.on({'open.zf.trigger':this.open.bind(this),'close.zf.trigger':function closeZfTrigger(event,$element){if(event.target===_this.$element[0]||(0,_jquery2.default)(event.target).parents('[data-closable]')[0]===$element){// only close reveal when it's explicitly called
return _this3.close.apply(_this3);}},'toggle.zf.trigger':this.toggle.bind(this),'resizeme.zf.trigger':function resizemeZfTrigger(){_this._updatePosition();}});if(this.options.closeOnClick&&this.options.overlay){this.$overlay.off('.zf.reveal').on('click.zf.reveal',function(e){if(e.target===_this.$element[0]||_jquery2.default.contains(_this.$element[0],e.target)||!_jquery2.default.contains(document,e.target)){return;}_this.close();});}if(this.options.deepLink){(0,_jquery2.default)(window).on('popstate.zf.reveal:'+this.id,this._handleState.bind(this));}}},{key:'_handleState',value:function _handleState(e){if(window.location.hash==='#'+this.id&&!this.isActive){this.open();}else{this.close();}}},{key:'open',value:function open(){var _this4=this;// either update or replace browser history
if(this.options.deepLink){var hash='#'+this.id;if(window.history.pushState){if(this.options.updateHistory){window.history.pushState({},'',hash);}else{window.history.replaceState({},'',hash);}}else{window.location.hash=hash;}}this.isActive=true;// Make elements invisible, but remove display: none so we can get size and positioning
this.$element.css({'visibility':'hidden'}).show().scrollTop(0);if(this.options.overlay){this.$overlay.css({'visibility':'hidden'}).show();}this._updatePosition();this.$element.hide().css({'visibility':''});if(this.$overlay){this.$overlay.css({'visibility':''}).hide();if(this.$element.hasClass('fast')){this.$overlay.addClass('fast');}else if(this.$element.hasClass('slow')){this.$overlay.addClass('slow');}}if(!this.options.multipleOpened){/**
       * Fires immediately before the modal opens.
       * Closes any other modals that are currently open
       * @event Reveal#closeme
       */this.$element.trigger('closeme.zf.reveal',this.id);}var _this=this;function addRevealOpenClasses(){if(_this.isMobile){if(!_this.originalScrollPos){_this.originalScrollPos=window.pageYOffset;}(0,_jquery2.default)('html, body').addClass('is-reveal-open');}else{(0,_jquery2.default)('body').addClass('is-reveal-open');}}// Motion UI method of reveal
if(this.options.animationIn){var afterAnimation=function afterAnimation(){_this.$element.attr({'aria-hidden':false,'tabindex':-1}).focus();addRevealOpenClasses();_foundationUtil.Keyboard.trapFocus(_this.$element);};if(this.options.overlay){_foundationUtil3.Motion.animateIn(this.$overlay,'fade-in');}_foundationUtil3.Motion.animateIn(this.$element,this.options.animationIn,function(){if(_this4.$element){// protect against object having been removed
_this4.focusableElements=_foundationUtil.Keyboard.findFocusable(_this4.$element);afterAnimation();}});}// jQuery method of reveal
else{if(this.options.overlay){this.$overlay.show(0);}this.$element.show(this.options.showDelay);}// handle accessibility
this.$element.attr({'aria-hidden':false,'tabindex':-1}).focus();_foundationUtil.Keyboard.trapFocus(this.$element);addRevealOpenClasses();this._extraHandlers();/**
     * Fires when the modal has successfully opened.
     * @event Reveal#open
     */this.$element.trigger('open.zf.reveal');}},{key:'_extraHandlers',value:function _extraHandlers(){var _this=this;if(!this.$element){return;}// If we're in the middle of cleanup, don't freak out
this.focusableElements=_foundationUtil.Keyboard.findFocusable(this.$element);if(!this.options.overlay&&this.options.closeOnClick&&!this.options.fullScreen){(0,_jquery2.default)('body').on('click.zf.reveal',function(e){if(e.target===_this.$element[0]||_jquery2.default.contains(_this.$element[0],e.target)||!_jquery2.default.contains(document,e.target)){return;}_this.close();});}if(this.options.closeOnEsc){(0,_jquery2.default)(window).on('keydown.zf.reveal',function(e){_foundationUtil.Keyboard.handleKey(e,'Reveal',{close:function close(){if(_this.options.closeOnEsc){_this.close();}}});});}}},{key:'close',value:function close(){if(!this.isActive||!this.$element.is(':visible')){return false;}var _this=this;// Motion UI method of hiding
if(this.options.animationOut){if(this.options.overlay){_foundationUtil3.Motion.animateOut(this.$overlay,'fade-out');}_foundationUtil3.Motion.animateOut(this.$element,this.options.animationOut,finishUp);}// jQuery method of hiding
else{this.$element.hide(this.options.hideDelay);if(this.options.overlay){this.$overlay.hide(0,finishUp);}else{finishUp();}}// Conditionals to remove extra event listeners added on open
if(this.options.closeOnEsc){(0,_jquery2.default)(window).off('keydown.zf.reveal');}if(!this.options.overlay&&this.options.closeOnClick){(0,_jquery2.default)('body').off('click.zf.reveal');}this.$element.off('keydown.zf.reveal');function finishUp(){if(_this.isMobile){if((0,_jquery2.default)('.reveal:visible').length===0){(0,_jquery2.default)('html, body').removeClass('is-reveal-open');}if(_this.originalScrollPos){(0,_jquery2.default)('body').scrollTop(_this.originalScrollPos);_this.originalScrollPos=null;}}else{if((0,_jquery2.default)('.reveal:visible').length===0){(0,_jquery2.default)('body').removeClass('is-reveal-open');}}_foundationUtil.Keyboard.releaseFocus(_this.$element);_this.$element.attr('aria-hidden',true);/**
      * Fires when the modal is done closing.
      * @event Reveal#closed
      */_this.$element.trigger('closed.zf.reveal');}/**
    * Resets the modal content
    * This prevents a running video to keep going in the background
    */if(this.options.resetOnClose){this.$element.html(this.$element.html());}this.isActive=false;if(_this.options.deepLink){if(window.history.replaceState){window.history.replaceState('',document.title,window.location.href.replace('#'+this.id,''));}else{window.location.hash='';}}this.$anchor.focus();}},{key:'toggle',value:function toggle(){if(this.isActive){this.close();}else{this.open();}}},{key:'_destroy',value:function _destroy(){if(this.options.overlay){this.$element.appendTo((0,_jquery2.default)(this.options.appendTo));// move $element outside of $overlay to prevent error unregisterPlugin()
this.$overlay.hide().off().remove();}this.$element.hide().off();this.$anchor.off('.zf');(0,_jquery2.default)(window).off('.zf.reveal:'+this.id);}}]);return Reveal;}(_foundation.Plugin);Reveal.defaults={/**
   * Motion-UI class to use for animated elements. If none used, defaults to simple show/hide.
   * @option
   * @type {string}
   * @default ''
   */animationIn:'',/**
   * Motion-UI class to use for animated elements. If none used, defaults to simple show/hide.
   * @option
   * @type {string}
   * @default ''
   */animationOut:'',/**
   * Time, in ms, to delay the opening of a modal after a click if no animation used.
   * @option
   * @type {number}
   * @default 0
   */showDelay:0,/**
   * Time, in ms, to delay the closing of a modal after a click if no animation used.
   * @option
   * @type {number}
   * @default 0
   */hideDelay:0,/**
   * Allows a click on the body/overlay to close the modal.
   * @option
   * @type {boolean}
   * @default true
   */closeOnClick:true,/**
   * Allows the modal to close if the user presses the `ESCAPE` key.
   * @option
   * @type {boolean}
   * @default true
   */closeOnEsc:true,/**
   * If true, allows multiple modals to be displayed at once.
   * @option
   * @type {boolean}
   * @default false
   */multipleOpened:false,/**
   * Distance, in pixels, the modal should push down from the top of the screen.
   * @option
   * @type {number|string}
   * @default auto
   */vOffset:'auto',/**
   * Distance, in pixels, the modal should push in from the side of the screen.
   * @option
   * @type {number|string}
   * @default auto
   */hOffset:'auto',/**
   * Allows the modal to be fullscreen, completely blocking out the rest of the view. JS checks for this as well.
   * @option
   * @type {boolean}
   * @default false
   */fullScreen:false,/**
   * Percentage of screen height the modal should push up from the bottom of the view.
   * @option
   * @type {number}
   * @default 10
   */btmOffsetPct:10,/**
   * Allows the modal to generate an overlay div, which will cover the view when modal opens.
   * @option
   * @type {boolean}
   * @default true
   */overlay:true,/**
   * Allows the modal to remove and reinject markup on close. Should be true if using video elements w/o using provider's api, otherwise, videos will continue to play in the background.
   * @option
   * @type {boolean}
   * @default false
   */resetOnClose:false,/**
   * Allows the modal to alter the url on open/close, and allows the use of the `back` button to close modals. ALSO, allows a modal to auto-maniacally open on page load IF the hash === the modal's user-set id.
   * @option
   * @type {boolean}
   * @default false
   */deepLink:false,/**
   * Update the browser history with the open modal
   * @option
   * @default false
   */updateHistory:false,/**
   * Allows the modal to append to custom div.
   * @option
   * @type {string}
   * @default "body"
   */appendTo:"body",/**
   * Allows adding additional class names to the reveal overlay.
   * @option
   * @type {string}
   * @default ''
   */additionalOverlayClasses:''};function iPhoneSniff(){return /iP(ad|hone|od).*OS/.test(window.navigator.userAgent);}function androidSniff(){return /Android/.test(window.navigator.userAgent);}function mobileSniff(){return iPhoneSniff()||androidSniff();}exports.Reveal=Reveal;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.motion','./foundation.util.core','./foundation.plugin','./foundation.util.touch','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.motion'),require('./foundation.util.core'),require('./foundation.plugin'),require('./foundation.util.touch'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil,global.foundationUtil);global.foundationSlider=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation,_foundationUtil4,_foundationUtil5){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Slider=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Slider=function(_Plugin){_inherits(Slider,_Plugin);function Slider(){_classCallCheck(this,Slider);return _possibleConstructorReturn(this,(Slider.__proto__||Object.getPrototypeOf(Slider)).apply(this,arguments));}_createClass(Slider,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Slider.defaults,this.$element.data(),options);this.className='Slider';// ie9 back compat
// Touch and Triggers inits are idempotent, we just need to make sure it's initialied.
_foundationUtil4.Touch.init(_jquery2.default);_foundationUtil5.Triggers.init(_jquery2.default);this._init();_foundationUtil.Keyboard.register('Slider',{'ltr':{'ARROW_RIGHT':'increase','ARROW_UP':'increase','ARROW_DOWN':'decrease','ARROW_LEFT':'decrease','SHIFT_ARROW_RIGHT':'increase_fast','SHIFT_ARROW_UP':'increase_fast','SHIFT_ARROW_DOWN':'decrease_fast','SHIFT_ARROW_LEFT':'decrease_fast','HOME':'min','END':'max'},'rtl':{'ARROW_LEFT':'increase','ARROW_RIGHT':'decrease','SHIFT_ARROW_LEFT':'increase_fast','SHIFT_ARROW_RIGHT':'decrease_fast'}});}},{key:'_init',value:function _init(){this.inputs=this.$element.find('input');this.handles=this.$element.find('[data-slider-handle]');this.$handle=this.handles.eq(0);this.$input=this.inputs.length?this.inputs.eq(0):(0,_jquery2.default)('#'+this.$handle.attr('aria-controls'));this.$fill=this.$element.find('[data-slider-fill]').css(this.options.vertical?'height':'width',0);var isDbl=false,_this=this;if(this.options.disabled||this.$element.hasClass(this.options.disabledClass)){this.options.disabled=true;this.$element.addClass(this.options.disabledClass);}if(!this.inputs.length){this.inputs=(0,_jquery2.default)().add(this.$input);this.options.binding=true;}this._setInitAttr(0);if(this.handles[1]){this.options.doubleSided=true;this.$handle2=this.handles.eq(1);this.$input2=this.inputs.length>1?this.inputs.eq(1):(0,_jquery2.default)('#'+this.$handle2.attr('aria-controls'));if(!this.inputs[1]){this.inputs=this.inputs.add(this.$input2);}isDbl=true;// this.$handle.triggerHandler('click.zf.slider');
this._setInitAttr(1);}// Set handle positions
this.setHandles();this._events();}},{key:'setHandles',value:function setHandles(){var _this3=this;if(this.handles[1]){this._setHandlePos(this.$handle,this.inputs.eq(0).val(),true,function(){_this3._setHandlePos(_this3.$handle2,_this3.inputs.eq(1).val(),true);});}else{this._setHandlePos(this.$handle,this.inputs.eq(0).val(),true);}}},{key:'_reflow',value:function _reflow(){this.setHandles();}},{key:'_pctOfBar',value:function _pctOfBar(value){var pctOfBar=percent(value-this.options.start,this.options.end-this.options.start);switch(this.options.positionValueFunction){case"pow":pctOfBar=this._logTransform(pctOfBar);break;case"log":pctOfBar=this._powTransform(pctOfBar);break;}return pctOfBar.toFixed(2);}},{key:'_value',value:function _value(pctOfBar){switch(this.options.positionValueFunction){case"pow":pctOfBar=this._powTransform(pctOfBar);break;case"log":pctOfBar=this._logTransform(pctOfBar);break;}var value=(this.options.end-this.options.start)*pctOfBar+this.options.start;return value;}},{key:'_logTransform',value:function _logTransform(value){return baseLog(this.options.nonLinearBase,value*(this.options.nonLinearBase-1)+1);}},{key:'_powTransform',value:function _powTransform(value){return(Math.pow(this.options.nonLinearBase,value)-1)/(this.options.nonLinearBase-1);}},{key:'_setHandlePos',value:function _setHandlePos($hndl,location,noInvert,cb){// don't move if the slider has been disabled since its initialization
if(this.$element.hasClass(this.options.disabledClass)){return;}//might need to alter that slightly for bars that will have odd number selections.
location=parseFloat(location);//on input change events, convert string to number...grumble.
// prevent slider from running out of bounds, if value exceeds the limits set through options, override the value to min/max
if(location<this.options.start){location=this.options.start;}else if(location>this.options.end){location=this.options.end;}var isDbl=this.options.doubleSided;//this is for single-handled vertical sliders, it adjusts the value to account for the slider being "upside-down"
//for click and drag events, it's weird due to the scale(-1, 1) css property
if(this.options.vertical&&!noInvert){location=this.options.end-location;}if(isDbl){//this block is to prevent 2 handles from crossing eachother. Could/should be improved.
if(this.handles.index($hndl)===0){var h2Val=parseFloat(this.$handle2.attr('aria-valuenow'));location=location>=h2Val?h2Val-this.options.step:location;}else{var h1Val=parseFloat(this.$handle.attr('aria-valuenow'));location=location<=h1Val?h1Val+this.options.step:location;}}var _this=this,vert=this.options.vertical,hOrW=vert?'height':'width',lOrT=vert?'top':'left',handleDim=$hndl[0].getBoundingClientRect()[hOrW],elemDim=this.$element[0].getBoundingClientRect()[hOrW],//percentage of bar min/max value based on click or drag point
pctOfBar=this._pctOfBar(location),//number of actual pixels to shift the handle, based on the percentage obtained above
pxToMove=(elemDim-handleDim)*pctOfBar,//percentage of bar to shift the handle
movement=(percent(pxToMove,elemDim)*100).toFixed(this.options.decimal);//fixing the decimal value for the location number, is passed to other methods as a fixed floating-point value
location=parseFloat(location.toFixed(this.options.decimal));// declare empty object for css adjustments, only used with 2 handled-sliders
var css={};this._setValues($hndl,location);// TODO update to calculate based on values set to respective inputs??
if(isDbl){var isLeftHndl=this.handles.index($hndl)===0,//empty variable, will be used for min-height/width for fill bar
dim,//percentage w/h of the handle compared to the slider bar
handlePct=~~(percent(handleDim,elemDim)*100);//if left handle, the math is slightly different than if it's the right handle, and the left/top property needs to be changed for the fill bar
if(isLeftHndl){//left or top percentage value to apply to the fill bar.
css[lOrT]=movement+'%';//calculate the new min-height/width for the fill bar.
dim=parseFloat(this.$handle2[0].style[lOrT])-movement+handlePct;//this callback is necessary to prevent errors and allow the proper placement and initialization of a 2-handled slider
//plus, it means we don't care if 'dim' isNaN on init, it won't be in the future.
if(cb&&typeof cb==='function'){cb();}//this is only needed for the initialization of 2 handled sliders
}else{//just caching the value of the left/bottom handle's left/top property
var handlePos=parseFloat(this.$handle[0].style[lOrT]);//calculate the new min-height/width for the fill bar. Use isNaN to prevent false positives for numbers <= 0
//based on the percentage of movement of the handle being manipulated, less the opposing handle's left/top position, plus the percentage w/h of the handle itself
dim=movement-(isNaN(handlePos)?(this.options.initialStart-this.options.start)/((this.options.end-this.options.start)/100):handlePos)+handlePct;}// assign the min-height/width to our css object
css['min-'+hOrW]=dim+'%';}this.$element.one('finished.zf.animate',function(){/**
                     * Fires when the handle is done moving.
                     * @event Slider#moved
                     */_this.$element.trigger('moved.zf.slider',[$hndl]);});//because we don't know exactly how the handle will be moved, check the amount of time it should take to move.
var moveTime=this.$element.data('dragging')?1000/60:this.options.moveTime;(0,_foundationUtil2.Move)(moveTime,$hndl,function(){// adjusting the left/top property of the handle, based on the percentage calculated above
// if movement isNaN, that is because the slider is hidden and we cannot determine handle width,
// fall back to next best guess.
if(isNaN(movement)){$hndl.css(lOrT,pctOfBar*100+'%');}else{$hndl.css(lOrT,movement+'%');}if(!_this.options.doubleSided){//if single-handled, a simple method to expand the fill bar
_this.$fill.css(hOrW,pctOfBar*100+'%');}else{//otherwise, use the css object we created above
_this.$fill.css(css);}});/**
     * Fires when the value has not been change for a given time.
     * @event Slider#changed
     */clearTimeout(_this.timeout);_this.timeout=setTimeout(function(){_this.$element.trigger('changed.zf.slider',[$hndl]);},_this.options.changedDelay);}},{key:'_setInitAttr',value:function _setInitAttr(idx){var initVal=idx===0?this.options.initialStart:this.options.initialEnd;var id=this.inputs.eq(idx).attr('id')||(0,_foundationUtil3.GetYoDigits)(6,'slider');this.inputs.eq(idx).attr({'id':id,'max':this.options.end,'min':this.options.start,'step':this.options.step});this.inputs.eq(idx).val(initVal);this.handles.eq(idx).attr({'role':'slider','aria-controls':id,'aria-valuemax':this.options.end,'aria-valuemin':this.options.start,'aria-valuenow':initVal,'aria-orientation':this.options.vertical?'vertical':'horizontal','tabindex':0});}},{key:'_setValues',value:function _setValues($handle,val){var idx=this.options.doubleSided?this.handles.index($handle):0;this.inputs.eq(idx).val(val);$handle.attr('aria-valuenow',val);}},{key:'_handleEvent',value:function _handleEvent(e,$handle,val){var value,hasVal;if(!val){//click or drag events
e.preventDefault();var _this=this,vertical=this.options.vertical,param=vertical?'height':'width',direction=vertical?'top':'left',eventOffset=vertical?e.pageY:e.pageX,halfOfHandle=this.$handle[0].getBoundingClientRect()[param]/2,barDim=this.$element[0].getBoundingClientRect()[param],windowScroll=vertical?(0,_jquery2.default)(window).scrollTop():(0,_jquery2.default)(window).scrollLeft();var elemOffset=this.$element.offset()[direction];// touch events emulated by the touch util give position relative to screen, add window.scroll to event coordinates...
// best way to guess this is simulated is if clientY == pageY
if(e.clientY===e.pageY){eventOffset=eventOffset+windowScroll;}var eventFromBar=eventOffset-elemOffset;var barXY;if(eventFromBar<0){barXY=0;}else if(eventFromBar>barDim){barXY=barDim;}else{barXY=eventFromBar;}var offsetPct=percent(barXY,barDim);value=this._value(offsetPct);// turn everything around for RTL, yay math!
if((0,_foundationUtil3.rtl)()&&!this.options.vertical){value=this.options.end-value;}value=_this._adjustValue(null,value);//boolean flag for the setHandlePos fn, specifically for vertical sliders
hasVal=false;if(!$handle){//figure out which handle it is, pass it to the next function.
var firstHndlPos=absPosition(this.$handle,direction,barXY,param),secndHndlPos=absPosition(this.$handle2,direction,barXY,param);$handle=firstHndlPos<=secndHndlPos?this.$handle:this.$handle2;}}else{//change event on input
value=this._adjustValue(null,val);hasVal=true;}this._setHandlePos($handle,value,hasVal);}},{key:'_adjustValue',value:function _adjustValue($handle,value){var val,step=this.options.step,div=parseFloat(step/2),left,prev_val,next_val;if(!!$handle){val=parseFloat($handle.attr('aria-valuenow'));}else{val=value;}left=val%step;prev_val=val-left;next_val=prev_val+step;if(left===0){return val;}val=val>=prev_val+div?next_val:prev_val;return val;}},{key:'_events',value:function _events(){this._eventsForHandle(this.$handle);if(this.handles[1]){this._eventsForHandle(this.$handle2);}}},{key:'_eventsForHandle',value:function _eventsForHandle($handle){var _this=this,curHandle,timer;this.inputs.off('change.zf.slider').on('change.zf.slider',function(e){var idx=_this.inputs.index((0,_jquery2.default)(this));_this._handleEvent(e,_this.handles.eq(idx),(0,_jquery2.default)(this).val());});if(this.options.clickSelect){this.$element.off('click.zf.slider').on('click.zf.slider',function(e){if(_this.$element.data('dragging')){return false;}if(!(0,_jquery2.default)(e.target).is('[data-slider-handle]')){if(_this.options.doubleSided){_this._handleEvent(e);}else{_this._handleEvent(e,_this.$handle);}}});}if(this.options.draggable){this.handles.addTouch();var $body=(0,_jquery2.default)('body');$handle.off('mousedown.zf.slider').on('mousedown.zf.slider',function(e){$handle.addClass('is-dragging');_this.$fill.addClass('is-dragging');//
_this.$element.data('dragging',true);curHandle=(0,_jquery2.default)(e.currentTarget);$body.on('mousemove.zf.slider',function(e){e.preventDefault();_this._handleEvent(e,curHandle);}).on('mouseup.zf.slider',function(e){_this._handleEvent(e,curHandle);$handle.removeClass('is-dragging');_this.$fill.removeClass('is-dragging');_this.$element.data('dragging',false);$body.off('mousemove.zf.slider mouseup.zf.slider');});})// prevent events triggered by touch
.on('selectstart.zf.slider touchmove.zf.slider',function(e){e.preventDefault();});}$handle.off('keydown.zf.slider').on('keydown.zf.slider',function(e){var _$handle=(0,_jquery2.default)(this),idx=_this.options.doubleSided?_this.handles.index(_$handle):0,oldValue=parseFloat(_this.inputs.eq(idx).val()),newValue;// handle keyboard event with keyboard util
_foundationUtil.Keyboard.handleKey(e,'Slider',{decrease:function decrease(){newValue=oldValue-_this.options.step;},increase:function increase(){newValue=oldValue+_this.options.step;},decrease_fast:function decrease_fast(){newValue=oldValue-_this.options.step*10;},increase_fast:function increase_fast(){newValue=oldValue+_this.options.step*10;},min:function min(){newValue=_this.options.start;},max:function max(){newValue=_this.options.end;},handled:function handled(){// only set handle pos when event was handled specially
e.preventDefault();_this._setHandlePos(_$handle,newValue,true);}});/*if (newValue) { // if pressed key has special function, update value
        e.preventDefault();
        _this._setHandlePos(_$handle, newValue);
      }*/});}},{key:'_destroy',value:function _destroy(){this.handles.off('.zf.slider');this.inputs.off('.zf.slider');this.$element.off('.zf.slider');clearTimeout(this.timeout);}}]);return Slider;}(_foundation.Plugin);Slider.defaults={/**
   * Minimum value for the slider scale.
   * @option
   * @type {number}
   * @default 0
   */start:0,/**
   * Maximum value for the slider scale.
   * @option
   * @type {number}
   * @default 100
   */end:100,/**
   * Minimum value change per change event.
   * @option
   * @type {number}
   * @default 1
   */step:1,/**
   * Value at which the handle/input *(left handle/first input)* should be set to on initialization.
   * @option
   * @type {number}
   * @default 0
   */initialStart:0,/**
   * Value at which the right handle/second input should be set to on initialization.
   * @option
   * @type {number}
   * @default 100
   */initialEnd:100,/**
   * Allows the input to be located outside the container and visible. Set to by the JS
   * @option
   * @type {boolean}
   * @default false
   */binding:false,/**
   * Allows the user to click/tap on the slider bar to select a value.
   * @option
   * @type {boolean}
   * @default true
   */clickSelect:true,/**
   * Set to true and use the `vertical` class to change alignment to vertical.
   * @option
   * @type {boolean}
   * @default false
   */vertical:false,/**
   * Allows the user to drag the slider handle(s) to select a value.
   * @option
   * @type {boolean}
   * @default true
   */draggable:true,/**
   * Disables the slider and prevents event listeners from being applied. Double checked by JS with `disabledClass`.
   * @option
   * @type {boolean}
   * @default false
   */disabled:false,/**
   * Allows the use of two handles. Double checked by the JS. Changes some logic handling.
   * @option
   * @type {boolean}
   * @default false
   */doubleSided:false,/**
   * Potential future feature.
   */// steps: 100,
/**
   * Number of decimal places the plugin should go to for floating point precision.
   * @option
   * @type {number}
   * @default 2
   */decimal:2,/**
   * Time delay for dragged elements.
   */// dragDelay: 0,
/**
   * Time, in ms, to animate the movement of a slider handle if user clicks/taps on the bar. Needs to be manually set if updating the transition time in the Sass settings.
   * @option
   * @type {number}
   * @default 200
   */moveTime:200,//update this if changing the transition time in the sass
/**
   * Class applied to disabled sliders.
   * @option
   * @type {string}
   * @default 'disabled'
   */disabledClass:'disabled',/**
   * Will invert the default layout for a vertical<span data-tooltip title="who would do this???"> </span>slider.
   * @option
   * @type {boolean}
   * @default false
   */invertVertical:false,/**
   * Milliseconds before the `changed.zf-slider` event is triggered after value change.
   * @option
   * @type {number}
   * @default 500
   */changedDelay:500,/**
  * Basevalue for non-linear sliders
  * @option
  * @type {number}
  * @default 5
  */nonLinearBase:5,/**
  * Basevalue for non-linear sliders, possible values are: `'linear'`, `'pow'` & `'log'`. Pow and Log use the nonLinearBase setting.
  * @option
  * @type {string}
  * @default 'linear'
  */positionValueFunction:'linear'};function percent(frac,num){return frac/num;}function absPosition($handle,dir,clickPos,param){return Math.abs($handle.position()[dir]+$handle[param]()/2-clickPos);}function baseLog(base,value){return Math.log(value)/Math.log(base);}exports.Slider=Slider;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.core','./foundation.util.mediaQuery','./foundation.plugin','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.core'),require('./foundation.util.mediaQuery'),require('./foundation.plugin'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationSticky=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation,_foundationUtil3){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Sticky=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Sticky=function(_Plugin){_inherits(Sticky,_Plugin);function Sticky(){_classCallCheck(this,Sticky);return _possibleConstructorReturn(this,(Sticky.__proto__||Object.getPrototypeOf(Sticky)).apply(this,arguments));}_createClass(Sticky,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Sticky.defaults,this.$element.data(),options);this.className='Sticky';// ie9 back compat
// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil3.Triggers.init(_jquery2.default);this._init();}},{key:'_init',value:function _init(){_foundationUtil2.MediaQuery._init();var $parent=this.$element.parent('[data-sticky-container]'),id=this.$element[0].id||(0,_foundationUtil.GetYoDigits)(6,'sticky'),_this=this;if($parent.length){this.$container=$parent;}else{this.wasWrapped=true;this.$element.wrap(this.options.container);this.$container=this.$element.parent();}this.$container.addClass(this.options.containerClass);this.$element.addClass(this.options.stickyClass).attr({'data-resize':id,'data-mutate':id});if(this.options.anchor!==''){(0,_jquery2.default)('#'+_this.options.anchor).attr({'data-mutate':id});}this.scrollCount=this.options.checkEvery;this.isStuck=false;(0,_jquery2.default)(window).one('load.zf.sticky',function(){//We calculate the container height to have correct values for anchor points offset calculation.
_this.containerHeight=_this.$element.css("display")=="none"?0:_this.$element[0].getBoundingClientRect().height;_this.$container.css('height',_this.containerHeight);_this.elemHeight=_this.containerHeight;if(_this.options.anchor!==''){_this.$anchor=(0,_jquery2.default)('#'+_this.options.anchor);}else{_this._parsePoints();}_this._setSizes(function(){var scroll=window.pageYOffset;_this._calc(false,scroll);//Unstick the element will ensure that proper classes are set.
if(!_this.isStuck){_this._removeSticky(scroll>=_this.topPoint?false:true);}});_this._events(id.split('-').reverse().join('-'));});}},{key:'_parsePoints',value:function _parsePoints(){var top=this.options.topAnchor==""?1:this.options.topAnchor,btm=this.options.btmAnchor==""?document.documentElement.scrollHeight:this.options.btmAnchor,pts=[top,btm],breaks={};for(var i=0,len=pts.length;i<len&&pts[i];i++){var pt;if(typeof pts[i]==='number'){pt=pts[i];}else{var place=pts[i].split(':'),anchor=(0,_jquery2.default)('#'+place[0]);pt=anchor.offset().top;if(place[1]&&place[1].toLowerCase()==='bottom'){pt+=anchor[0].getBoundingClientRect().height;}}breaks[i]=pt;}this.points=breaks;return;}},{key:'_events',value:function _events(id){var _this=this,scrollListener=this.scrollListener='scroll.zf.'+id;if(this.isOn){return;}if(this.canStick){this.isOn=true;(0,_jquery2.default)(window).off(scrollListener).on(scrollListener,function(e){if(_this.scrollCount===0){_this.scrollCount=_this.options.checkEvery;_this._setSizes(function(){_this._calc(false,window.pageYOffset);});}else{_this.scrollCount--;_this._calc(false,window.pageYOffset);}});}this.$element.off('resizeme.zf.trigger').on('resizeme.zf.trigger',function(e,el){_this._eventsHandler(id);});this.$element.on('mutateme.zf.trigger',function(e,el){_this._eventsHandler(id);});if(this.$anchor){this.$anchor.on('mutateme.zf.trigger',function(e,el){_this._eventsHandler(id);});}}},{key:'_eventsHandler',value:function _eventsHandler(id){var _this=this,scrollListener=this.scrollListener='scroll.zf.'+id;_this._setSizes(function(){_this._calc(false);if(_this.canStick){if(!_this.isOn){_this._events(id);}}else if(_this.isOn){_this._pauseListeners(scrollListener);}});}},{key:'_pauseListeners',value:function _pauseListeners(scrollListener){this.isOn=false;(0,_jquery2.default)(window).off(scrollListener);/**
     * Fires when the plugin is paused due to resize event shrinking the view.
     * @event Sticky#pause
     * @private
     */this.$element.trigger('pause.zf.sticky');}},{key:'_calc',value:function _calc(checkSizes,scroll){if(checkSizes){this._setSizes();}if(!this.canStick){if(this.isStuck){this._removeSticky(true);}return false;}if(!scroll){scroll=window.pageYOffset;}if(scroll>=this.topPoint){if(scroll<=this.bottomPoint){if(!this.isStuck){this._setSticky();}}else{if(this.isStuck){this._removeSticky(false);}}}else{if(this.isStuck){this._removeSticky(true);}}}},{key:'_setSticky',value:function _setSticky(){var _this=this,stickTo=this.options.stickTo,mrgn=stickTo==='top'?'marginTop':'marginBottom',notStuckTo=stickTo==='top'?'bottom':'top',css={};css[mrgn]=this.options[mrgn]+'em';css[stickTo]=0;css[notStuckTo]='auto';this.isStuck=true;this.$element.removeClass('is-anchored is-at-'+notStuckTo).addClass('is-stuck is-at-'+stickTo).css(css)/**
                  * Fires when the $element has become `position: fixed;`
                  * Namespaced to `top` or `bottom`, e.g. `sticky.zf.stuckto:top`
                  * @event Sticky#stuckto
                  */.trigger('sticky.zf.stuckto:'+stickTo);this.$element.on("transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd",function(){_this._setSizes();});}},{key:'_removeSticky',value:function _removeSticky(isTop){var stickTo=this.options.stickTo,stickToTop=stickTo==='top',css={},anchorPt=(this.points?this.points[1]-this.points[0]:this.anchorHeight)-this.elemHeight,mrgn=stickToTop?'marginTop':'marginBottom',notStuckTo=stickToTop?'bottom':'top',topOrBottom=isTop?'top':'bottom';css[mrgn]=0;css['bottom']='auto';if(isTop){css['top']=0;}else{css['top']=anchorPt;}this.isStuck=false;this.$element.removeClass('is-stuck is-at-'+stickTo).addClass('is-anchored is-at-'+topOrBottom).css(css)/**
                  * Fires when the $element has become anchored.
                  * Namespaced to `top` or `bottom`, e.g. `sticky.zf.unstuckfrom:bottom`
                  * @event Sticky#unstuckfrom
                  */.trigger('sticky.zf.unstuckfrom:'+topOrBottom);}},{key:'_setSizes',value:function _setSizes(cb){this.canStick=_foundationUtil2.MediaQuery.is(this.options.stickyOn);if(!this.canStick){if(cb&&typeof cb==='function'){cb();}}var _this=this,newElemWidth=this.$container[0].getBoundingClientRect().width,comp=window.getComputedStyle(this.$container[0]),pdngl=parseInt(comp['padding-left'],10),pdngr=parseInt(comp['padding-right'],10);if(this.$anchor&&this.$anchor.length){this.anchorHeight=this.$anchor[0].getBoundingClientRect().height;}else{this._parsePoints();}this.$element.css({'max-width':newElemWidth-pdngl-pdngr+'px'});var newContainerHeight=this.$element[0].getBoundingClientRect().height||this.containerHeight;if(this.$element.css("display")=="none"){newContainerHeight=0;}this.containerHeight=newContainerHeight;this.$container.css({height:newContainerHeight});this.elemHeight=newContainerHeight;if(!this.isStuck){if(this.$element.hasClass('is-at-bottom')){var anchorPt=(this.points?this.points[1]-this.$container.offset().top:this.anchorHeight)-this.elemHeight;this.$element.css('top',anchorPt);}}this._setBreakPoints(newContainerHeight,function(){if(cb&&typeof cb==='function'){cb();}});}},{key:'_setBreakPoints',value:function _setBreakPoints(elemHeight,cb){if(!this.canStick){if(cb&&typeof cb==='function'){cb();}else{return false;}}var mTop=emCalc(this.options.marginTop),mBtm=emCalc(this.options.marginBottom),topPoint=this.points?this.points[0]:this.$anchor.offset().top,bottomPoint=this.points?this.points[1]:topPoint+this.anchorHeight,// topPoint = this.$anchor.offset().top || this.points[0],
// bottomPoint = topPoint + this.anchorHeight || this.points[1],
winHeight=window.innerHeight;if(this.options.stickTo==='top'){topPoint-=mTop;bottomPoint-=elemHeight+mTop;}else if(this.options.stickTo==='bottom'){topPoint-=winHeight-(elemHeight+mBtm);bottomPoint-=winHeight-mBtm;}else{//this would be the stickTo: both option... tricky
}this.topPoint=topPoint;this.bottomPoint=bottomPoint;if(cb&&typeof cb==='function'){cb();}}},{key:'_destroy',value:function _destroy(){this._removeSticky(true);this.$element.removeClass(this.options.stickyClass+' is-anchored is-at-top').css({height:'',top:'',bottom:'','max-width':''}).off('resizeme.zf.trigger').off('mutateme.zf.trigger');if(this.$anchor&&this.$anchor.length){this.$anchor.off('change.zf.sticky');}(0,_jquery2.default)(window).off(this.scrollListener);if(this.wasWrapped){this.$element.unwrap();}else{this.$container.removeClass(this.options.containerClass).css({height:''});}}}]);return Sticky;}(_foundation.Plugin);Sticky.defaults={/**
   * Customizable container template. Add your own classes for styling and sizing.
   * @option
   * @type {string}
   * @default '&lt;div data-sticky-container&gt;&lt;/div&gt;'
   */container:'<div data-sticky-container></div>',/**
   * Location in the view the element sticks to. Can be `'top'` or `'bottom'`.
   * @option
   * @type {string}
   * @default 'top'
   */stickTo:'top',/**
   * If anchored to a single element, the id of that element.
   * @option
   * @type {string}
   * @default ''
   */anchor:'',/**
   * If using more than one element as anchor points, the id of the top anchor.
   * @option
   * @type {string}
   * @default ''
   */topAnchor:'',/**
   * If using more than one element as anchor points, the id of the bottom anchor.
   * @option
   * @type {string}
   * @default ''
   */btmAnchor:'',/**
   * Margin, in `em`'s to apply to the top of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */marginTop:1,/**
   * Margin, in `em`'s to apply to the bottom of the element when it becomes sticky.
   * @option
   * @type {number}
   * @default 1
   */marginBottom:1,/**
   * Breakpoint string that is the minimum screen size an element should become sticky.
   * @option
   * @type {string}
   * @default 'medium'
   */stickyOn:'medium',/**
   * Class applied to sticky element, and removed on destruction. Foundation defaults to `sticky`.
   * @option
   * @type {string}
   * @default 'sticky'
   */stickyClass:'sticky',/**
   * Class applied to sticky container. Foundation defaults to `sticky-container`.
   * @option
   * @type {string}
   * @default 'sticky-container'
   */containerClass:'sticky-container',/**
   * Number of scroll events between the plugin's recalculating sticky points. Setting it to `0` will cause it to recalc every scroll event, setting it to `-1` will prevent recalc on scroll.
   * @option
   * @type {number}
   * @default -1
   */checkEvery:-1};/**
 * Helper function to calculate em values
 * @param Number {em} - number of em's to calculate into pixels
 */function emCalc(em){return parseInt(window.getComputedStyle(document.body,null).fontSize,10)*em;}exports.Sticky=Sticky;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.keyboard','./foundation.util.imageLoader','./foundation.plugin'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.keyboard'),require('./foundation.util.imageLoader'),require('./foundation.plugin'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationTabs=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Tabs=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj;}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj;};function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Tabs=function(_Plugin){_inherits(Tabs,_Plugin);function Tabs(){_classCallCheck(this,Tabs);return _possibleConstructorReturn(this,(Tabs.__proto__||Object.getPrototypeOf(Tabs)).apply(this,arguments));}_createClass(Tabs,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Tabs.defaults,this.$element.data(),options);this.className='Tabs';// ie9 back compat
this._init();_foundationUtil.Keyboard.register('Tabs',{'ENTER':'open','SPACE':'open','ARROW_RIGHT':'next','ARROW_UP':'previous','ARROW_DOWN':'next','ARROW_LEFT':'previous'// 'TAB': 'next',
// 'SHIFT_TAB': 'previous'
});}},{key:'_init',value:function _init(){var _this3=this;var _this=this;this.$element.attr({'role':'tablist'});this.$tabTitles=this.$element.find('.'+this.options.linkClass);this.$tabContent=(0,_jquery2.default)('[data-tabs-content="'+this.$element[0].id+'"]');this.$tabTitles.each(function(){var $elem=(0,_jquery2.default)(this),$link=$elem.find('a'),isActive=$elem.hasClass(''+_this.options.linkActiveClass),hash=$link.attr('data-tabs-target')||$link[0].hash.slice(1),linkId=$link[0].id?$link[0].id:hash+'-label',$tabContent=(0,_jquery2.default)('#'+hash);$elem.attr({'role':'presentation'});$link.attr({'role':'tab','aria-controls':hash,'aria-selected':isActive,'id':linkId,'tabindex':isActive?'0':'-1'});$tabContent.attr({'role':'tabpanel','aria-labelledby':linkId});if(!isActive){$tabContent.attr('aria-hidden','true');}if(isActive&&_this.options.autoFocus){(0,_jquery2.default)(window).load(function(){(0,_jquery2.default)('html, body').animate({scrollTop:$elem.offset().top},_this.options.deepLinkSmudgeDelay,function(){$link.focus();});});}});if(this.options.matchHeight){var $images=this.$tabContent.find('img');if($images.length){(0,_foundationUtil2.onImagesLoaded)($images,this._setHeight.bind(this));}else{this._setHeight();}}//current context-bound function to open tabs on page load or history popstate
this._checkDeepLink=function(){var anchor=window.location.hash;//need a hash and a relevant anchor in this tabset
if(anchor.length){var $link=_this3.$element.find('[href$="'+anchor+'"]');if($link.length){_this3.selectTab((0,_jquery2.default)(anchor),true);//roll up a little to show the titles
if(_this3.options.deepLinkSmudge){var offset=_this3.$element.offset();(0,_jquery2.default)('html, body').animate({scrollTop:offset.top},_this3.options.deepLinkSmudgeDelay);}/**
            * Fires when the zplugin has deeplinked at pageload
            * @event Tabs#deeplink
            */_this3.$element.trigger('deeplink.zf.tabs',[$link,(0,_jquery2.default)(anchor)]);}}};//use browser to open a tab, if it exists in this tabset
if(this.options.deepLink){this._checkDeepLink();}this._events();}},{key:'_events',value:function _events(){this._addKeyHandler();this._addClickHandler();this._setHeightMqHandler=null;if(this.options.matchHeight){this._setHeightMqHandler=this._setHeight.bind(this);(0,_jquery2.default)(window).on('changed.zf.mediaquery',this._setHeightMqHandler);}if(this.options.deepLink){(0,_jquery2.default)(window).on('popstate',this._checkDeepLink);}}},{key:'_addClickHandler',value:function _addClickHandler(){var _this=this;this.$element.off('click.zf.tabs').on('click.zf.tabs','.'+this.options.linkClass,function(e){e.preventDefault();e.stopPropagation();_this._handleTabChange((0,_jquery2.default)(this));});}},{key:'_addKeyHandler',value:function _addKeyHandler(){var _this=this;this.$tabTitles.off('keydown.zf.tabs').on('keydown.zf.tabs',function(e){if(e.which===9)return;var $element=(0,_jquery2.default)(this),$elements=$element.parent('ul').children('li'),$prevElement,$nextElement;$elements.each(function(i){if((0,_jquery2.default)(this).is($element)){if(_this.options.wrapOnKeys){$prevElement=i===0?$elements.last():$elements.eq(i-1);$nextElement=i===$elements.length-1?$elements.first():$elements.eq(i+1);}else{$prevElement=$elements.eq(Math.max(0,i-1));$nextElement=$elements.eq(Math.min(i+1,$elements.length-1));}return;}});// handle keyboard event with keyboard util
_foundationUtil.Keyboard.handleKey(e,'Tabs',{open:function open(){$element.find('[role="tab"]').focus();_this._handleTabChange($element);},previous:function previous(){$prevElement.find('[role="tab"]').focus();_this._handleTabChange($prevElement);},next:function next(){$nextElement.find('[role="tab"]').focus();_this._handleTabChange($nextElement);},handled:function handled(){e.stopPropagation();e.preventDefault();}});});}},{key:'_handleTabChange',value:function _handleTabChange($target,historyHandled){/**
     * Check for active class on target. Collapse if exists.
     */if($target.hasClass(''+this.options.linkActiveClass)){if(this.options.activeCollapse){this._collapseTab($target);/**
            * Fires when the zplugin has successfully collapsed tabs.
            * @event Tabs#collapse
            */this.$element.trigger('collapse.zf.tabs',[$target]);}return;}var $oldTab=this.$element.find('.'+this.options.linkClass+'.'+this.options.linkActiveClass),$tabLink=$target.find('[role="tab"]'),hash=$tabLink.attr('data-tabs-target')||$tabLink[0].hash.slice(1),$targetContent=this.$tabContent.find('#'+hash);//close old tab
this._collapseTab($oldTab);//open new tab
this._openTab($target);//either replace or update browser history
if(this.options.deepLink&&!historyHandled){var anchor=$target.find('a').attr('href');if(this.options.updateHistory){history.pushState({},'',anchor);}else{history.replaceState({},'',anchor);}}/**
     * Fires when the plugin has successfully changed tabs.
     * @event Tabs#change
     */this.$element.trigger('change.zf.tabs',[$target,$targetContent]);//fire to children a mutation event
$targetContent.find("[data-mutate]").trigger("mutateme.zf.trigger");}},{key:'_openTab',value:function _openTab($target){var $tabLink=$target.find('[role="tab"]'),hash=$tabLink.attr('data-tabs-target')||$tabLink[0].hash.slice(1),$targetContent=this.$tabContent.find('#'+hash);$target.addClass(''+this.options.linkActiveClass);$tabLink.attr({'aria-selected':'true','tabindex':'0'});$targetContent.addClass(''+this.options.panelActiveClass).removeAttr('aria-hidden');}},{key:'_collapseTab',value:function _collapseTab($target){var $target_anchor=$target.removeClass(''+this.options.linkActiveClass).find('[role="tab"]').attr({'aria-selected':'false','tabindex':-1});(0,_jquery2.default)('#'+$target_anchor.attr('aria-controls')).removeClass(''+this.options.panelActiveClass).attr({'aria-hidden':'true'});}},{key:'selectTab',value:function selectTab(elem,historyHandled){var idStr;if((typeof elem==='undefined'?'undefined':_typeof(elem))==='object'){idStr=elem[0].id;}else{idStr=elem;}if(idStr.indexOf('#')<0){idStr='#'+idStr;}var $target=this.$tabTitles.find('[href$="'+idStr+'"]').parent('.'+this.options.linkClass);this._handleTabChange($target,historyHandled);}},{key:'_setHeight',value:function _setHeight(){var max=0,_this=this;// Lock down the `this` value for the root tabs object
this.$tabContent.find('.'+this.options.panelClass).css('height','').each(function(){var panel=(0,_jquery2.default)(this),isActive=panel.hasClass(''+_this.options.panelActiveClass);// get the options from the parent instead of trying to get them from the child
if(!isActive){panel.css({'visibility':'hidden','display':'block'});}var temp=this.getBoundingClientRect().height;if(!isActive){panel.css({'visibility':'','display':''});}max=temp>max?temp:max;}).css('height',max+'px');}},{key:'_destroy',value:function _destroy(){this.$element.find('.'+this.options.linkClass).off('.zf.tabs').hide().end().find('.'+this.options.panelClass).hide();if(this.options.matchHeight){if(this._setHeightMqHandler!=null){(0,_jquery2.default)(window).off('changed.zf.mediaquery',this._setHeightMqHandler);}}if(this.options.deepLink){(0,_jquery2.default)(window).off('popstate',this._checkDeepLink);}}}]);return Tabs;}(_foundation.Plugin);Tabs.defaults={/**
   * Allows the window to scroll to content of pane specified by hash anchor
   * @option
   * @type {boolean}
   * @default false
   */deepLink:false,/**
   * Adjust the deep link scroll to make sure the top of the tab panel is visible
   * @option
   * @type {boolean}
   * @default false
   */deepLinkSmudge:false,/**
   * Animation time (ms) for the deep link adjustment
   * @option
   * @type {number}
   * @default 300
   */deepLinkSmudgeDelay:300,/**
   * Update the browser history with the open tab
   * @option
   * @type {boolean}
   * @default false
   */updateHistory:false,/**
   * Allows the window to scroll to content of active pane on load if set to true.
   * Not recommended if more than one tab panel per page.
   * @option
   * @type {boolean}
   * @default false
   */autoFocus:false,/**
   * Allows keyboard input to 'wrap' around the tab links.
   * @option
   * @type {boolean}
   * @default true
   */wrapOnKeys:true,/**
   * Allows the tab content panes to match heights if set to true.
   * @option
   * @type {boolean}
   * @default false
   */matchHeight:false,/**
   * Allows active tabs to collapse when clicked.
   * @option
   * @type {boolean}
   * @default false
   */activeCollapse:false,/**
   * Class applied to `li`'s in tab link list.
   * @option
   * @type {string}
   * @default 'tabs-title'
   */linkClass:'tabs-title',/**
   * Class applied to the active `li` in tab link list.
   * @option
   * @type {string}
   * @default 'is-active'
   */linkActiveClass:'is-active',/**
   * Class applied to the content containers.
   * @option
   * @type {string}
   * @default 'tabs-panel'
   */panelClass:'tabs-panel',/**
   * Class applied to the active content container.
   * @option
   * @type {string}
   * @default 'is-active'
   */panelActiveClass:'is-active'};exports.Tabs=Tabs;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.motion','./foundation.plugin','./foundation.util.triggers'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.motion'),require('./foundation.plugin'),require('./foundation.util.triggers'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundation,global.foundationUtil);global.foundationToggler=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundation,_foundationUtil2){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Toggler=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Toggler=function(_Plugin){_inherits(Toggler,_Plugin);function Toggler(){_classCallCheck(this,Toggler);return _possibleConstructorReturn(this,(Toggler.__proto__||Object.getPrototypeOf(Toggler)).apply(this,arguments));}_createClass(Toggler,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Toggler.defaults,element.data(),options);this.className='';this.className='Toggler';// ie9 back compat
// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil2.Triggers.init(_jquery2.default);this._init();this._events();}},{key:'_init',value:function _init(){var input;// Parse animation classes if they were set
if(this.options.animate){input=this.options.animate.split(' ');this.animationIn=input[0];this.animationOut=input[1]||null;}// Otherwise, parse toggle class
else{input=this.$element.data('toggler');// Allow for a . at the beginning of the string
this.className=input[0]==='.'?input.slice(1):input;}// Add ARIA attributes to triggers
var id=this.$element[0].id;(0,_jquery2.default)('[data-open="'+id+'"], [data-close="'+id+'"], [data-toggle="'+id+'"]').attr('aria-controls',id);// If the target is hidden, add aria-hidden
this.$element.attr('aria-expanded',this.$element.is(':hidden')?false:true);}},{key:'_events',value:function _events(){this.$element.off('toggle.zf.trigger').on('toggle.zf.trigger',this.toggle.bind(this));}},{key:'toggle',value:function toggle(){this[this.options.animate?'_toggleAnimate':'_toggleClass']();}},{key:'_toggleClass',value:function _toggleClass(){this.$element.toggleClass(this.className);var isOn=this.$element.hasClass(this.className);if(isOn){/**
       * Fires if the target element has the class after a toggle.
       * @event Toggler#on
       */this.$element.trigger('on.zf.toggler');}else{/**
       * Fires if the target element does not have the class after a toggle.
       * @event Toggler#off
       */this.$element.trigger('off.zf.toggler');}this._updateARIA(isOn);this.$element.find('[data-mutate]').trigger('mutateme.zf.trigger');}},{key:'_toggleAnimate',value:function _toggleAnimate(){var _this=this;if(this.$element.is(':hidden')){_foundationUtil.Motion.animateIn(this.$element,this.animationIn,function(){_this._updateARIA(true);this.trigger('on.zf.toggler');this.find('[data-mutate]').trigger('mutateme.zf.trigger');});}else{_foundationUtil.Motion.animateOut(this.$element,this.animationOut,function(){_this._updateARIA(false);this.trigger('off.zf.toggler');this.find('[data-mutate]').trigger('mutateme.zf.trigger');});}}},{key:'_updateARIA',value:function _updateARIA(isOn){this.$element.attr('aria-expanded',isOn?true:false);}},{key:'_destroy',value:function _destroy(){this.$element.off('.zf.toggler');}}]);return Toggler;}(_foundation.Plugin);Toggler.defaults={/**
   * Tells the plugin if the element should animated when toggled.
   * @option
   * @type {boolean}
   * @default false
   */animate:false};exports.Toggler=Toggler;});
(function(global,factory){if(typeof define==="function"&&define.amd){define(['exports','jquery','./foundation.util.core','./foundation.util.mediaQuery','./foundation.util.triggers','./foundation.positionable'],factory);}else if(typeof exports!=="undefined"){factory(exports,require('jquery'),require('./foundation.util.core'),require('./foundation.util.mediaQuery'),require('./foundation.util.triggers'),require('./foundation.positionable'));}else{var mod={exports:{}};factory(mod.exports,global.jquery,global.foundationUtil,global.foundationUtil,global.foundationUtil,global.foundation);global.foundationTooltip=mod.exports;}})(this,function(exports,_jquery,_foundationUtil,_foundationUtil2,_foundationUtil3,_foundation){'use strict';Object.defineProperty(exports,"__esModule",{value:true});exports.Tooltip=undefined;var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj};}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function");}}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor);}}return function(Constructor,protoProps,staticProps){if(protoProps)defineProperties(Constructor.prototype,protoProps);if(staticProps)defineProperties(Constructor,staticProps);return Constructor;};}();function _possibleConstructorReturn(self,call){if(!self){throw new ReferenceError("this hasn't been initialised - super() hasn't been called");}return call&&(typeof call==="object"||typeof call==="function")?call:self;}var _get=function get(object,property,receiver){if(object===null)object=Function.prototype;var desc=Object.getOwnPropertyDescriptor(object,property);if(desc===undefined){var parent=Object.getPrototypeOf(object);if(parent===null){return undefined;}else{return get(parent,property,receiver);}}else if("value"in desc){return desc.value;}else{var getter=desc.get;if(getter===undefined){return undefined;}return getter.call(receiver);}};function _inherits(subClass,superClass){if(typeof superClass!=="function"&&superClass!==null){throw new TypeError("Super expression must either be null or a function, not "+typeof superClass);}subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,enumerable:false,writable:true,configurable:true}});if(superClass)Object.setPrototypeOf?Object.setPrototypeOf(subClass,superClass):subClass.__proto__=superClass;}var Tooltip=function(_Positionable){_inherits(Tooltip,_Positionable);function Tooltip(){_classCallCheck(this,Tooltip);return _possibleConstructorReturn(this,(Tooltip.__proto__||Object.getPrototypeOf(Tooltip)).apply(this,arguments));}_createClass(Tooltip,[{key:'_setup',value:function _setup(element,options){this.$element=element;this.options=_jquery2.default.extend({},Tooltip.defaults,this.$element.data(),options);this.className='Tooltip';// ie9 back compat
this.isActive=false;this.isClick=false;// Triggers init is idempotent, just need to make sure it is initialized
_foundationUtil3.Triggers.init(_jquery2.default);this._init();}},{key:'_init',value:function _init(){_foundationUtil2.MediaQuery._init();var elemId=this.$element.attr('aria-describedby')||(0,_foundationUtil.GetYoDigits)(6,'tooltip');this.options.tipText=this.options.tipText||this.$element.attr('title');this.template=this.options.template?(0,_jquery2.default)(this.options.template):this._buildTemplate(elemId);if(this.options.allowHtml){this.template.appendTo(document.body).html(this.options.tipText).hide();}else{this.template.appendTo(document.body).text(this.options.tipText).hide();}this.$element.attr({'title':'','aria-describedby':elemId,'data-yeti-box':elemId,'data-toggle':elemId,'data-resize':elemId}).addClass(this.options.triggerClass);_get(Tooltip.prototype.__proto__||Object.getPrototypeOf(Tooltip.prototype),'_init',this).call(this);this._events();}},{key:'_getDefaultPosition',value:function _getDefaultPosition(){// handle legacy classnames
var position=this.$element[0].className.match(/\b(top|left|right|bottom)\b/g);return position?position[0]:'top';}},{key:'_getDefaultAlignment',value:function _getDefaultAlignment(){return'center';}},{key:'_getHOffset',value:function _getHOffset(){if(this.position==='left'||this.position==='right'){return this.options.hOffset+this.options.tooltipWidth;}else{return this.options.hOffset;}}},{key:'_getVOffset',value:function _getVOffset(){if(this.position==='top'||this.position==='bottom'){return this.options.vOffset+this.options.tooltipHeight;}else{return this.options.vOffset;}}},{key:'_buildTemplate',value:function _buildTemplate(id){var templateClasses=(this.options.tooltipClass+' '+this.options.positionClass+' '+this.options.templateClasses).trim();var $template=(0,_jquery2.default)('<div></div>').addClass(templateClasses).attr({'role':'tooltip','aria-hidden':true,'data-is-active':false,'data-is-focus':false,'id':id});return $template;}},{key:'_setPosition',value:function _setPosition(){_get(Tooltip.prototype.__proto__||Object.getPrototypeOf(Tooltip.prototype),'_setPosition',this).call(this,this.$element,this.template);}},{key:'show',value:function show(){if(this.options.showOn!=='all'&&!_foundationUtil2.MediaQuery.is(this.options.showOn)){// console.error('The screen is too small to display this tooltip');
return false;}var _this=this;this.template.css('visibility','hidden').show();this._setPosition();this.template.removeClass('top bottom left right').addClass(this.position);this.template.removeClass('align-top align-bottom align-left align-right align-center').addClass('align-'+this.alignment);/**
     * Fires to close all other open tooltips on the page
     * @event Closeme#tooltip
     */this.$element.trigger('closeme.zf.tooltip',this.template.attr('id'));this.template.attr({'data-is-active':true,'aria-hidden':false});_this.isActive=true;// console.log(this.template);
this.template.stop().hide().css('visibility','').fadeIn(this.options.fadeInDuration,function(){//maybe do stuff?
});/**
     * Fires when the tooltip is shown
     * @event Tooltip#show
     */this.$element.trigger('show.zf.tooltip');}},{key:'hide',value:function hide(){// console.log('hiding', this.$element.data('yeti-box'));
var _this=this;this.template.stop().attr({'aria-hidden':true,'data-is-active':false}).fadeOut(this.options.fadeOutDuration,function(){_this.isActive=false;_this.isClick=false;});/**
     * fires when the tooltip is hidden
     * @event Tooltip#hide
     */this.$element.trigger('hide.zf.tooltip');}},{key:'_events',value:function _events(){var _this=this;var $template=this.template;var isFocus=false;if(!this.options.disableHover){this.$element.on('mouseenter.zf.tooltip',function(e){if(!_this.isActive){_this.timeout=setTimeout(function(){_this.show();},_this.options.hoverDelay);}}).on('mouseleave.zf.tooltip',function(e){clearTimeout(_this.timeout);if(!isFocus||_this.isClick&&!_this.options.clickOpen){_this.hide();}});}if(this.options.clickOpen){this.$element.on('mousedown.zf.tooltip',function(e){e.stopImmediatePropagation();if(_this.isClick){//_this.hide();
// _this.isClick = false;
}else{_this.isClick=true;if((_this.options.disableHover||!_this.$element.attr('tabindex'))&&!_this.isActive){_this.show();}}});}else{this.$element.on('mousedown.zf.tooltip',function(e){e.stopImmediatePropagation();_this.isClick=true;});}if(!this.options.disableForTouch){this.$element.on('tap.zf.tooltip touchend.zf.tooltip',function(e){_this.isActive?_this.hide():_this.show();});}this.$element.on({// 'toggle.zf.trigger': this.toggle.bind(this),
// 'close.zf.trigger': this.hide.bind(this)
'close.zf.trigger':this.hide.bind(this)});this.$element.on('focus.zf.tooltip',function(e){isFocus=true;if(_this.isClick){// If we're not showing open on clicks, we need to pretend a click-launched focus isn't
// a real focus, otherwise on hover and come back we get bad behavior
if(!_this.options.clickOpen){isFocus=false;}return false;}else{_this.show();}}).on('focusout.zf.tooltip',function(e){isFocus=false;_this.isClick=false;_this.hide();}).on('resizeme.zf.trigger',function(){if(_this.isActive){_this._setPosition();}});}},{key:'toggle',value:function toggle(){if(this.isActive){this.hide();}else{this.show();}}},{key:'_destroy',value:function _destroy(){this.$element.attr('title',this.template.text()).off('.zf.trigger .zf.tooltip').removeClass('has-tip top right left').removeAttr('aria-describedby aria-haspopup data-disable-hover data-resize data-toggle data-tooltip data-yeti-box');this.template.remove();}}]);return Tooltip;}(_foundation.Positionable);Tooltip.defaults={disableForTouch:false,/**
   * Time, in ms, before a tooltip should open on hover.
   * @option
   * @type {number}
   * @default 200
   */hoverDelay:200,/**
   * Time, in ms, a tooltip should take to fade into view.
   * @option
   * @type {number}
   * @default 150
   */fadeInDuration:150,/**
   * Time, in ms, a tooltip should take to fade out of view.
   * @option
   * @type {number}
   * @default 150
   */fadeOutDuration:150,/**
   * Disables hover events from opening the tooltip if set to true
   * @option
   * @type {boolean}
   * @default false
   */disableHover:false,/**
   * Optional addtional classes to apply to the tooltip template on init.
   * @option
   * @type {string}
   * @default ''
   */templateClasses:'',/**
   * Non-optional class added to tooltip templates. Foundation default is 'tooltip'.
   * @option
   * @type {string}
   * @default 'tooltip'
   */tooltipClass:'tooltip',/**
   * Class applied to the tooltip anchor element.
   * @option
   * @type {string}
   * @default 'has-tip'
   */triggerClass:'has-tip',/**
   * Minimum breakpoint size at which to open the tooltip.
   * @option
   * @type {string}
   * @default 'small'
   */showOn:'small',/**
   * Custom template to be used to generate markup for tooltip.
   * @option
   * @type {string}
   * @default ''
   */template:'',/**
   * Text displayed in the tooltip template on open.
   * @option
   * @type {string}
   * @default ''
   */tipText:'',touchCloseText:'Tap to close.',/**
   * Allows the tooltip to remain open if triggered with a click or touch event.
   * @option
   * @type {boolean}
   * @default true
   */clickOpen:true,/**
   * DEPRECATED Additional positioning classes, set by the JS
   * @option
   * @type {string}
   * @default ''
   */positionClass:'',/**
   * Position of tooltip. Can be left, right, bottom, top, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */position:'auto',/**
   * Alignment of tooltip relative to anchor. Can be left, right, bottom, top, center, or auto.
   * @option
   * @type {string}
   * @default 'auto'
   */alignment:'auto',/**
   * Allow overlap of container/window. If false, tooltip will first try to
   * position as defined by data-position and data-alignment, but reposition if
   * it would cause an overflow.  @option
   * @type {boolean}
   * @default false
   */allowOverlap:false,/**
   * Allow overlap of only the bottom of the container. This is the most common
   * behavior for dropdowns, allowing the dropdown to extend the bottom of the
   * screen but not otherwise influence or break out of the container.
   * Less common for tooltips.
   * @option
   * @type {boolean}
   * @default false
   */allowBottomOverlap:false,/**
   * Distance, in pixels, the template should push away from the anchor on the Y axis.
   * @option
   * @type {number}
   * @default 0
   */vOffset:0,/**
   * Distance, in pixels, the template should push away from the anchor on the X axis
   * @option
   * @type {number}
   * @default 0
   */hOffset:0,/**
   * Distance, in pixels, the template spacing auto-adjust for a vertical tooltip
   * @option
   * @type {number}
   * @default 14
   */tooltipHeight:14,/**
   * Distance, in pixels, the template spacing auto-adjust for a horizontal tooltip
   * @option
   * @type {number}
   * @default 12
   */tooltipWidth:12,/**
   * Allow HTML in tooltip. Warning: If you are loading user-generated content into tooltips,
   * allowing HTML may open yourself up to XSS attacks.
   * @option
   * @type {boolean}
   * @default false
   */allowHtml:false};/**
 * TODO utilize resize event trigger
 */exports.Tooltip=Tooltip;});