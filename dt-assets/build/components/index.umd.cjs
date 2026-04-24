(function(_,L){typeof exports=="object"&&typeof module<"u"?L(exports):typeof define=="function"&&define.amd?define(["exports"],L):(_=typeof globalThis<"u"?globalThis:_||self,L(_.DtWebComponents={}))})(this,function(_){"use strict";var Mn=Object.defineProperty;var jn=(_,L,W)=>L in _?Mn(_,L,{enumerable:!0,configurable:!0,writable:!0,value:W}):_[L]=W;var Me=(_,L,W)=>jn(_,typeof L!="symbol"?L+"":L,W);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var Oo;const L=globalThis,W=L.ShadowRoot&&(L.ShadyCSS===void 0||L.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,Vt=Symbol(),Bt=new WeakMap;let Uo=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==Vt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(W&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=Bt.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&Bt.set(t,e))}return e}toString(){return this.cssText}};const Vo=s=>new Uo(typeof s=="string"?s:s+"",void 0,Vt),Bo=(s,e)=>{if(W)s.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),o=L.litNonce;o!==void 0&&i.setAttribute("nonce",o),i.textContent=t.cssText,s.appendChild(i)}},Ht=W?s=>s:s=>s instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return Vo(t)})(s):s;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:Ho,defineProperty:Ko,getOwnPropertyDescriptor:Go,getOwnPropertyNames:Wo,getOwnPropertySymbols:Zo,getPrototypeOf:Jo}=Object,Z=globalThis,Kt=Z.trustedTypes,Qo=Kt?Kt.emptyScript:"",et=Z.reactiveElementPolyfillSupport,fe=(s,e)=>s,tt={toAttribute(s,e){switch(e){case Boolean:s=s?Qo:null;break;case Object:case Array:s=s==null?s:JSON.stringify(s)}return s},fromAttribute(s,e){let t=s;switch(e){case Boolean:t=s!==null;break;case Number:t=s===null?null:Number(s);break;case Object:case Array:try{t=JSON.parse(s)}catch{t=null}}return t}},Gt=(s,e)=>!Ho(s,e),Wt={attribute:!0,type:String,converter:tt,reflect:!1,hasChanged:Gt};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),Z.litPropertyMetadata??(Z.litPropertyMetadata=new WeakMap);let be=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Wt){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),o=this.getPropertyDescriptor(e,i,t);o!==void 0&&Ko(this.prototype,e,o)}}static getPropertyDescriptor(e,t,i){const{get:o,set:a}=Go(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return o==null?void 0:o.call(this)},set(r){const n=o==null?void 0:o.call(this);a.call(this,r),this.requestUpdate(e,n,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Wt}static _$Ei(){if(this.hasOwnProperty(fe("elementProperties")))return;const e=Jo(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(fe("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(fe("properties"))){const t=this.properties,i=[...Wo(t),...Zo(t)];for(const o of i)this.createProperty(o,t[o])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,o]of t)this.elementProperties.set(i,o)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const o=this._$Eu(t,i);o!==void 0&&this._$Eh.set(o,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const o of i)t.unshift(Ht(o))}else e!==void 0&&t.push(Ht(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return Bo(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),o=this.constructor._$Eu(e,i);if(o!==void 0&&i.reflect===!0){const r=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:tt).toAttribute(t,i.type);this._$Em=e,r==null?this.removeAttribute(o):this.setAttribute(o,r),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,o=i._$Eh.get(e);if(o!==void 0&&this._$Em!==o){const r=i.getPropertyOptions(o),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:tt;this._$Em=o,this[o]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??Gt)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const o=this.constructor.elementProperties;if(o.size>0)for(const[a,r]of o)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(o=>{var a;return(a=o.hostUpdate)==null?void 0:a.call(o)}),this.update(t)):this._$EU()}catch(o){throw e=!1,this._$EU(),o}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var o;return(o=i.hostUpdated)==null?void 0:o.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}};be.elementStyles=[],be.shadowRootOptions={mode:"open"},be[fe("elementProperties")]=new Map,be[fe("finalized")]=new Map,et==null||et({ReactiveElement:be}),(Z.reactiveElementVersions??(Z.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const me=globalThis,je=me.trustedTypes,Zt=je?je.createPolicy("lit-html",{createHTML:s=>s}):void 0,Jt="$lit$",J=`lit$${Math.random().toFixed(9).slice(2)}$`,Qt="?"+J,Xo=`<${Qt}>`,ie=document,ge=()=>ie.createComment(""),ve=s=>s===null||typeof s!="object"&&typeof s!="function",it=Array.isArray,Yo=s=>it(s)||typeof(s==null?void 0:s[Symbol.iterator])=="function",ot=`[ 	
\f\r]`,ye=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Xt=/-->/g,Yt=/>/g,oe=RegExp(`>|${ot}(?:([^\\s"'>=/]+)(${ot}*=${ot}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),ei=/'/g,ti=/"/g,ii=/^(?:script|style|textarea|title)$/i,es=s=>(e,...t)=>({_$litType$:s,strings:e,values:t}),h=es(1),V=Symbol.for("lit-noChange"),O=Symbol.for("lit-nothing"),oi=new WeakMap,se=ie.createTreeWalker(ie,129);function si(s,e){if(!it(s)||!s.hasOwnProperty("raw"))throw Error("invalid template strings array");return Zt!==void 0?Zt.createHTML(e):e}const ts=(s,e)=>{const t=s.length-1,i=[];let o,a=e===2?"<svg>":e===3?"<math>":"",r=ye;for(let n=0;n<t;n++){const l=s[n];let d,u,p=-1,g=0;for(;g<l.length&&(r.lastIndex=g,u=r.exec(l),u!==null);)g=r.lastIndex,r===ye?u[1]==="!--"?r=Xt:u[1]!==void 0?r=Yt:u[2]!==void 0?(ii.test(u[2])&&(o=RegExp("</"+u[2],"g")),r=oe):u[3]!==void 0&&(r=oe):r===oe?u[0]===">"?(r=o??ye,p=-1):u[1]===void 0?p=-2:(p=r.lastIndex-u[2].length,d=u[1],r=u[3]===void 0?oe:u[3]==='"'?ti:ei):r===ti||r===ei?r=oe:r===Xt||r===Yt?r=ye:(r=oe,o=void 0);const y=r===oe&&s[n+1].startsWith("/>")?" ":"";a+=r===ye?l+Xo:p>=0?(i.push(d),l.slice(0,p)+Jt+l.slice(p)+J+y):l+J+(p===-2?n:y)}return[si(s,a+(s[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),i]};class _e{constructor({strings:e,_$litType$:t},i){let o;this.parts=[];let a=0,r=0;const n=e.length-1,l=this.parts,[d,u]=ts(e,t);if(this.el=_e.createElement(d,i),se.currentNode=this.el.content,t===2||t===3){const p=this.el.content.firstChild;p.replaceWith(...p.childNodes)}for(;(o=se.nextNode())!==null&&l.length<n;){if(o.nodeType===1){if(o.hasAttributes())for(const p of o.getAttributeNames())if(p.endsWith(Jt)){const g=u[r++],y=o.getAttribute(p).split(J),w=/([.?@])?(.*)/.exec(g);l.push({type:1,index:a,name:w[2],strings:y,ctor:w[1]==="."?os:w[1]==="?"?ss:w[1]==="@"?as:Fe}),o.removeAttribute(p)}else p.startsWith(J)&&(l.push({type:6,index:a}),o.removeAttribute(p));if(ii.test(o.tagName)){const p=o.textContent.split(J),g=p.length-1;if(g>0){o.textContent=je?je.emptyScript:"";for(let y=0;y<g;y++)o.append(p[y],ge()),se.nextNode(),l.push({type:2,index:++a});o.append(p[g],ge())}}}else if(o.nodeType===8)if(o.data===Qt)l.push({type:2,index:a});else{let p=-1;for(;(p=o.data.indexOf(J,p+1))!==-1;)l.push({type:7,index:a}),p+=J.length-1}a++}}static createElement(e,t){const i=ie.createElement("template");return i.innerHTML=e,i}}function ue(s,e,t=s,i){var r,n;if(e===V)return e;let o=i!==void 0?(r=t._$Co)==null?void 0:r[i]:t._$Cl;const a=ve(e)?void 0:e._$litDirective$;return(o==null?void 0:o.constructor)!==a&&((n=o==null?void 0:o._$AO)==null||n.call(o,!1),a===void 0?o=void 0:(o=new a(s),o._$AT(s,t,i)),i!==void 0?(t._$Co??(t._$Co=[]))[i]=o:t._$Cl=o),o!==void 0&&(e=ue(s,o._$AS(s,e.values),o,i)),e}let is=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:i}=this._$AD,o=((e==null?void 0:e.creationScope)??ie).importNode(t,!0);se.currentNode=o;let a=se.nextNode(),r=0,n=0,l=i[0];for(;l!==void 0;){if(r===l.index){let d;l.type===2?d=new he(a,a.nextSibling,this,e):l.type===1?d=new l.ctor(a,l.name,l.strings,this,e):l.type===6&&(d=new rs(a,this,e)),this._$AV.push(d),l=i[++n]}r!==(l==null?void 0:l.index)&&(a=se.nextNode(),r++)}return se.currentNode=ie,o}p(e){let t=0;for(const i of this._$AV)i!==void 0&&(i.strings!==void 0?(i._$AI(e,i,t),t+=i.strings.length-2):i._$AI(e[t])),t++}};class he{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,i,o){this.type=2,this._$AH=O,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=i,this.options=o,this._$Cv=(o==null?void 0:o.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=ue(this,e,t),ve(e)?e===O||e==null||e===""?(this._$AH!==O&&this._$AR(),this._$AH=O):e!==this._$AH&&e!==V&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Yo(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==O&&ve(this._$AH)?this._$AA.nextSibling.data=e:this.T(ie.createTextNode(e)),this._$AH=e}$(e){var a;const{values:t,_$litType$:i}=e,o=typeof i=="number"?this._$AC(e):(i.el===void 0&&(i.el=_e.createElement(si(i.h,i.h[0]),this.options)),i);if(((a=this._$AH)==null?void 0:a._$AD)===o)this._$AH.p(t);else{const r=new is(o,this),n=r.u(this.options);r.p(t),this.T(n),this._$AH=r}}_$AC(e){let t=oi.get(e.strings);return t===void 0&&oi.set(e.strings,t=new _e(e)),t}k(e){it(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let i,o=0;for(const a of e)o===t.length?t.push(i=new he(this.O(ge()),this.O(ge()),this,this.options)):i=t[o],i._$AI(a),o++;o<t.length&&(this._$AR(i&&i._$AB.nextSibling,o),t.length=o)}_$AR(e=this._$AA.nextSibling,t){var i;for((i=this._$AP)==null?void 0:i.call(this,!1,!0,t);e&&e!==this._$AB;){const o=e.nextSibling;e.remove(),e=o}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class Fe{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,i,o,a){this.type=1,this._$AH=O,this._$AN=void 0,this.element=e,this.name=t,this._$AM=o,this.options=a,i.length>2||i[0]!==""||i[1]!==""?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=O}_$AI(e,t=this,i,o){const a=this.strings;let r=!1;if(a===void 0)e=ue(this,e,t,0),r=!ve(e)||e!==this._$AH&&e!==V,r&&(this._$AH=e);else{const n=e;let l,d;for(e=a[0],l=0;l<a.length-1;l++)d=ue(this,n[i+l],t,l),d===V&&(d=this._$AH[l]),r||(r=!ve(d)||d!==this._$AH[l]),d===O?e=O:e!==O&&(e+=(d??"")+a[l+1]),this._$AH[l]=d}r&&!o&&this.j(e)}j(e){e===O?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class os extends Fe{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===O?void 0:e}}class ss extends Fe{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==O)}}class as extends Fe{constructor(e,t,i,o,a){super(e,t,i,o,a),this.type=5}_$AI(e,t=this){if((e=ue(this,e,t,0)??O)===V)return;const i=this._$AH,o=e===O&&i!==O||e.capture!==i.capture||e.once!==i.once||e.passive!==i.passive,a=e!==O&&(i===O||o);o&&this.element.removeEventListener(this.name,this,i),a&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class rs{constructor(e,t,i){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(e){ue(this,e)}}const ns={I:he},st=me.litHtmlPolyfillSupport;st==null||st(_e,he),(me.litHtmlVersions??(me.litHtmlVersions=[])).push("3.2.1");const ls=(s,e,t)=>{const i=(t==null?void 0:t.renderBefore)??e;let o=i._$litPart$;if(o===void 0){const a=(t==null?void 0:t.renderBefore)??null;i._$litPart$=o=new he(e.insertBefore(ge(),a),a,void 0,t??{})}return o._$AI(s),o};/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ze=globalThis,at=ze.ShadowRoot&&(ze.ShadyCSS===void 0||ze.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,rt=Symbol(),ai=new WeakMap;let ri=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==rt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(at&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=ai.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&ai.set(t,e))}return e}toString(){return this.cssText}};const ds=s=>new ri(typeof s=="string"?s:s+"",void 0,rt),x=(s,...e)=>{const t=s.length===1?s[0]:e.reduce((i,o,a)=>i+(r=>{if(r._$cssResult$===!0)return r.cssText;if(typeof r=="number")return r;throw Error("Value passed to 'css' function must be a 'css' function result: "+r+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(o)+s[a+1],s[0]);return new ri(t,s,rt)},cs=(s,e)=>{if(at)s.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),o=ze.litNonce;o!==void 0&&i.setAttribute("nonce",o),i.textContent=t.cssText,s.appendChild(i)}},ni=at?s=>s:s=>s instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return ds(t)})(s):s;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:us,defineProperty:hs,getOwnPropertyDescriptor:ps,getOwnPropertyNames:fs,getOwnPropertySymbols:bs,getPrototypeOf:ms}=Object,Q=globalThis,li=Q.trustedTypes,gs=li?li.emptyScript:"",nt=Q.reactiveElementPolyfillSupport,we=(s,e)=>s,lt={toAttribute(s,e){switch(e){case Boolean:s=s?gs:null;break;case Object:case Array:s=s==null?s:JSON.stringify(s)}return s},fromAttribute(s,e){let t=s;switch(e){case Boolean:t=s!==null;break;case Number:t=s===null?null:Number(s);break;case Object:case Array:try{t=JSON.parse(s)}catch{t=null}}return t}},di=(s,e)=>!us(s,e),ci={attribute:!0,type:String,converter:lt,reflect:!1,hasChanged:di};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),Q.litPropertyMetadata??(Q.litPropertyMetadata=new WeakMap);class pe extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=ci){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),o=this.getPropertyDescriptor(e,i,t);o!==void 0&&hs(this.prototype,e,o)}}static getPropertyDescriptor(e,t,i){const{get:o,set:a}=ps(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return o==null?void 0:o.call(this)},set(r){const n=o==null?void 0:o.call(this);a.call(this,r),this.requestUpdate(e,n,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??ci}static _$Ei(){if(this.hasOwnProperty(we("elementProperties")))return;const e=ms(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(we("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(we("properties"))){const t=this.properties,i=[...fs(t),...bs(t)];for(const o of i)this.createProperty(o,t[o])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,o]of t)this.elementProperties.set(i,o)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const o=this._$Eu(t,i);o!==void 0&&this._$Eh.set(o,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const o of i)t.unshift(ni(o))}else e!==void 0&&t.push(ni(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return cs(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),o=this.constructor._$Eu(e,i);if(o!==void 0&&i.reflect===!0){const r=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:lt).toAttribute(t,i.type);this._$Em=e,r==null?this.removeAttribute(o):this.setAttribute(o,r),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,o=i._$Eh.get(e);if(o!==void 0&&this._$Em!==o){const r=i.getPropertyOptions(o),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:lt;this._$Em=o,this[o]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??di)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const o=this.constructor.elementProperties;if(o.size>0)for(const[a,r]of o)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(o=>{var a;return(a=o.hostUpdate)==null?void 0:a.call(o)}),this.update(t)):this._$EU()}catch(o){throw e=!1,this._$EU(),o}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var o;return(o=i.hostUpdated)==null?void 0:o.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}}pe.elementStyles=[],pe.shadowRootOptions={mode:"open"},pe[we("elementProperties")]=new Map,pe[we("finalized")]=new Map,nt==null||nt({ReactiveElement:pe}),(Q.reactiveElementVersions??(Q.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */let ae=class extends pe{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=ls(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return V}};ae._$litElement$=!0,ae.finalized=!0,(Oo=globalThis.litElementHydrateSupport)==null||Oo.call(globalThis,{LitElement:ae});const dt=globalThis.litElementPolyfillSupport;dt==null||dt({LitElement:ae}),(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.1.1");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ct={ATTRIBUTE:1,CHILD:2},ut=s=>(...e)=>({_$litDirective$:s,values:e});let ht=class{constructor(e){}get _$AU(){return this._$AM._$AU}_$AT(e,t,i){this._$Ct=e,this._$AM=t,this._$Ci=i}_$AS(e,t){return this.update(e,t)}update(e,t){return this.render(...t)}};/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const A=ut(class extends ht{constructor(s){var e;if(super(s),s.type!==ct.ATTRIBUTE||s.name!=="class"||((e=s.strings)==null?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(s){return" "+Object.keys(s).filter(e=>s[e]).join(" ")+" "}update(s,[e]){var i,o;if(this.st===void 0){this.st=new Set,s.strings!==void 0&&(this.nt=new Set(s.strings.join(" ").split(/\s/).filter(a=>a!=="")));for(const a in e)e[a]&&!((i=this.nt)!=null&&i.has(a))&&this.st.add(a);return this.render(e)}const t=s.element.classList;for(const a of this.st)a in e||(t.remove(a),this.st.delete(a));for(const a in e){const r=!!e[a];r===this.st.has(a)||(o=this.nt)!=null&&o.has(a)||(r?(t.add(a),this.st.add(a)):(t.remove(a),this.st.delete(a)))}return V}});/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const pt="lit-localize-status";/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const m=(s,...e)=>({strTag:!0,strings:s,values:e}),vs=s=>typeof s!="string"&&"strTag"in s,ui=(s,e,t)=>{let i=s[0];for(let o=1;o<s.length;o++)i+=e[t?t[o-1]:o-1],i+=s[o];return i};/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const hi=s=>vs(s)?ui(s.strings,s.values):s;let R=hi,pi=!1;function ys(s){if(pi)throw new Error("lit-localize can only be configured once");R=s,pi=!0}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class _s{constructor(e){this.__litLocalizeEventHandler=t=>{t.detail.status==="ready"&&this.host.requestUpdate()},this.host=e}hostConnected(){window.addEventListener(pt,this.__litLocalizeEventHandler)}hostDisconnected(){window.removeEventListener(pt,this.__litLocalizeEventHandler)}}const ws=s=>s.addController(new _s(s));/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class fi{constructor(){this.settled=!1,this.promise=new Promise((e,t)=>{this._resolve=e,this._reject=t})}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const B=[];for(let s=0;s<256;s++)B[s]=(s>>4&15).toString(16)+(s&15).toString(16);function $s(s){let e=0,t=8997,i=0,o=33826,a=0,r=40164,n=0,l=52210;for(let d=0;d<s.length;d++)t^=s.charCodeAt(d),e=t*435,i=o*435,a=r*435,n=l*435,a+=t<<8,n+=o<<8,i+=e>>>16,t=e&65535,a+=i>>>16,o=i&65535,l=n+(a>>>16)&65535,r=a&65535;return B[l>>8]+B[l&255]+B[r>>8]+B[r&255]+B[o>>8]+B[o&255]+B[t>>8]+B[t&255]}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const xs="",ks="h",Ss="s";function Es(s,e){return(e?ks:Ss)+$s(typeof s=="string"?s:s.join(xs))}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const bi=new WeakMap,mi=new Map;function As(s,e,t){if(s){const i=(t==null?void 0:t.id)??Ts(e),o=s[i];if(o){if(typeof o=="string")return o;if("strTag"in o)return ui(o.strings,e.values,o.values);{let a=bi.get(o);return a===void 0&&(a=o.values,bi.set(o,a)),{...o,values:a.map(r=>e.values[r])}}}}return hi(e)}function Ts(s){const e=typeof s=="string"?s:s.strings;let t=mi.get(e);return t===void 0&&(t=Es(e,typeof s!="string"&&!("strTag"in s)),mi.set(e,t)),t}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function ft(s){window.dispatchEvent(new CustomEvent(pt,{detail:s}))}let De="",bt,gi,Re,mt,vi,re=new fi;re.resolve();let Ne=0;const Os=s=>(ys((e,t)=>As(vi,e,t)),De=gi=s.sourceLocale,Re=new Set(s.targetLocales),Re.add(s.sourceLocale),mt=s.loadLocale,{getLocale:Cs,setLocale:Is}),Cs=()=>De,Is=s=>{if(s===(bt??De))return re.promise;if(!Re||!mt)throw new Error("Internal error");if(!Re.has(s))throw new Error("Invalid locale code");Ne++;const e=Ne;return bt=s,re.settled&&(re=new fi),ft({status:"loading",loadingLocale:s}),(s===gi?Promise.resolve({templates:void 0}):mt(s)).then(i=>{Ne===e&&(De=s,bt=void 0,vi=i.templates,ft({status:"ready",readyLocale:s}),re.resolve())},i=>{Ne===e&&(ft({status:"error",errorLocale:s,errorMessage:i.toString()}),re.reject(i))}),re.promise},Ls=(s,e,t)=>{const i=s[e];return i?typeof i=="function"?i():Promise.resolve(i):new Promise((o,a)=>{(typeof queueMicrotask=="function"?queueMicrotask:setTimeout)(a.bind(null,new Error("Unknown variable dynamic import: "+e+(e.split("/").length!==t?". Note that variables only represent file names one level deep.":""))))})},Ps="en",Ms=["am_ET","ar","ar_MA","bg_BG","bn_BD","bs_BA","cs","de_DE","el","en_US","es_419","es_ES","fa_IR","fr_FR","hi_IN","hr","hu_HU","id_ID","it_IT","ja","ko_KR","mk_MK","mr","my_MM","ne_NP","nl_NL","pa_IN","pl","pt_BR","ro_RO","ru_RU","sl_SI","sr_BA","sw","th","tl","tr_TR","uk","vi","zh_CN","zh_TW"],{setLocale:js}=Os({sourceLocale:Ps,targetLocales:Ms,loadLocale:s=>Ls(Object.assign({"./generated/am_ET.js":()=>Promise.resolve().then(()=>Ha),"./generated/ar.js":()=>Promise.resolve().then(()=>Ga),"./generated/ar_MA.js":()=>Promise.resolve().then(()=>Za),"./generated/bg_BG.js":()=>Promise.resolve().then(()=>Qa),"./generated/bn_BD.js":()=>Promise.resolve().then(()=>Ya),"./generated/bs_BA.js":()=>Promise.resolve().then(()=>tr),"./generated/cs.js":()=>Promise.resolve().then(()=>or),"./generated/de_DE.js":()=>Promise.resolve().then(()=>ar),"./generated/el.js":()=>Promise.resolve().then(()=>nr),"./generated/en_US.js":()=>Promise.resolve().then(()=>dr),"./generated/es-419.js":()=>Promise.resolve().then(()=>ur),"./generated/es_419.js":()=>Promise.resolve().then(()=>pr),"./generated/es_ES.js":()=>Promise.resolve().then(()=>br),"./generated/fa_IR.js":()=>Promise.resolve().then(()=>gr),"./generated/fr_FR.js":()=>Promise.resolve().then(()=>yr),"./generated/hi_IN.js":()=>Promise.resolve().then(()=>wr),"./generated/hr.js":()=>Promise.resolve().then(()=>xr),"./generated/hu_HU.js":()=>Promise.resolve().then(()=>Sr),"./generated/id_ID.js":()=>Promise.resolve().then(()=>Ar),"./generated/it_IT.js":()=>Promise.resolve().then(()=>Or),"./generated/ja.js":()=>Promise.resolve().then(()=>Ir),"./generated/ko_KR.js":()=>Promise.resolve().then(()=>Pr),"./generated/mk_MK.js":()=>Promise.resolve().then(()=>jr),"./generated/mr.js":()=>Promise.resolve().then(()=>zr),"./generated/my_MM.js":()=>Promise.resolve().then(()=>Rr),"./generated/ne_NP.js":()=>Promise.resolve().then(()=>qr),"./generated/nl_NL.js":()=>Promise.resolve().then(()=>Vr),"./generated/pa_IN.js":()=>Promise.resolve().then(()=>Hr),"./generated/pl.js":()=>Promise.resolve().then(()=>Gr),"./generated/pt_BR.js":()=>Promise.resolve().then(()=>Zr),"./generated/ro_RO.js":()=>Promise.resolve().then(()=>Qr),"./generated/ru_RU.js":()=>Promise.resolve().then(()=>Yr),"./generated/sl_SI.js":()=>Promise.resolve().then(()=>tn),"./generated/sr_BA.js":()=>Promise.resolve().then(()=>sn),"./generated/sw.js":()=>Promise.resolve().then(()=>rn),"./generated/th.js":()=>Promise.resolve().then(()=>ln),"./generated/tl.js":()=>Promise.resolve().then(()=>cn),"./generated/tr_TR.js":()=>Promise.resolve().then(()=>hn),"./generated/uk.js":()=>Promise.resolve().then(()=>fn),"./generated/vi.js":()=>Promise.resolve().then(()=>mn),"./generated/zh_CN.js":()=>Promise.resolve().then(()=>vn),"./generated/zh_TW.js":()=>Promise.resolve().then(()=>_n)}),`./generated/${s}.js`,3)});class yi{constructor(e,t="/wp-json"){this.nonce=e;let i=t;i.match("^http")&&(i=i.replace(/^http[s]?:\/\/.*?\//,"")),i=`/${i}/`.replace(/\/\//g,"/"),this.apiRoot=i}async makeRequest(e,t,i,o="dt/v1/"){let a=o;!a.endsWith("/")&&!t.startsWith("/")&&(a+="/");const r=t.startsWith("http")?t:`${this.apiRoot}${a}${t}`,n={method:e,credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce}};e!=="GET"&&(n.body=JSON.stringify(i));const l=await fetch(r,n),d=await l.json();if(!l.ok){const u=new Error((d==null?void 0:d.message)||d.toString());throw u.args={status:l.status,statusText:l.statusText,body:d},u}return d}async makeRequestOnPosts(e,t,i={}){return this.makeRequest(e,t,i,"dt-posts/v2/")}async getPost(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}`)}async createPost(e,t){return this.makeRequestOnPosts("POST",e,t)}async fetchPostsList(e,t){return this.makeRequestOnPosts("POST",`${e}/list`,t)}async updatePost(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}`,i)}async deletePost(e,t){return this.makeRequestOnPosts("DELETE",`${e}/${t}`)}async listPostsCompact(e,t=""){const i=new URLSearchParams({s:t});return this.makeRequestOnPosts("GET",`${e}/compact?${i}`)}async getPostDuplicates(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/all_duplicates`,i)}async checkFieldValueExists(e,t){return this.makeRequestOnPosts("POST",`${e}/check_field_value_exists`,t)}async getMultiSelectValues(e,t,i=""){const o=new URLSearchParams({s:i,field:t});return this.makeRequestOnPosts("GET",`${e}/multi-select-values?${o}`)}async getLocations(e,t,i,o=""){const a=new URLSearchParams({s:o,field:t,filter:i});return this.makeRequest("GET",`mapping_module/search_location_grid_by_name?${a}`)}async transferContact(e,t){return this.makeRequestOnPosts("POST","contacts/transfer",{contact_id:e,site_post_id:t})}async transferContactSummaryUpdate(e,t){return this.makeRequestOnPosts("POST","contacts/transfer/summary/send-update",{contact_id:e,update:t})}async requestRecordAccess(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/request_record_access`,{user_id:i})}async createComment(e,t,i,o="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments`,{comment:i,comment_type:o})}async updateComment(e,t,i,o,a="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}`,{comment:o,comment_type:a})}async deleteComment(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/comments/${i}`)}async getComments(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/comments`)}async toggle_comment_reaction(e,t,i,o,a){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}/react`,{user_id:o,reaction:a})}async getPostActivity(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/activity`)}async getSingleActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/activity/${i}`)}async revertActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/revert/${i}`)}async getPostShares(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/shares`)}async addPostShare(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/shares`,{user_id:i})}async removePostShare(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/shares`,{user_id:i})}async getFilters(){return this.makeRequest("GET","users/get_filters")}async saveFilters(e,t){return this.makeRequest("POST","users/save_filters",{filter:t,postType:e})}async deleteFilter(e,t){return this.makeRequest("DELETE","users/save_filters",{id:t,postType:e})}async searchUsers(e,t=""){const i=new URLSearchParams({s:t});return this.makeRequest("GET",`users/get_users?${i}&post_type=${e}`)}async checkDuplicateUsers(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/duplicates`)}async getContactInfo(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/`)}async createUser(e){return this.makeRequest("POST","users/create",e)}async advanced_search(e,t,i,o){return this.makeRequest("GET","advanced_search",{query:e,postType:t,offset:i,post:o.post,comment:o.comment,meta:o.meta,status:o.status},"dt-posts/v2/posts/search/")}async uploadFiles(e,t,i,o,a=""){const r=new FormData;i.forEach(l=>r.append("storage_upload_files[]",l)),r.append("meta_key",o),r.append("key_prefix",a),r.append("upload_type","post"),r.append("is_multi_file","true"),r.append("storage_s3_url_duration","+7 days");const n=`${this.apiRoot}dt-posts/v2/${e}/${t}/storage_upload`;return await new Promise((l,d)=>{const u=new XMLHttpRequest;u.open("POST",n,!0),u.withCredentials=!0,u.setRequestHeader("X-WP-Nonce",this.nonce),u.onload=()=>{let p={};try{p=JSON.parse(u.responseText||"{}")}catch{p={message:u.responseText||"Upload failed"}}if(u.status>=200&&u.status<300)l(p);else{const g=new Error((p==null?void 0:p.uploaded_msg)||(p==null?void 0:p.message)||"Upload failed");g.args={status:u.status,statusText:u.statusText,body:p},d(g)}},u.onerror=()=>d(new Error("Upload failed")),u.send(r)})}async deleteFile(e,t,i,o){return this.makeRequestOnPosts("POST",`${e}/${t}/storage_delete_single`,{meta_key:i,file_key:o})}async renameFile(e,t,i,o,a){return this.makeRequestOnPosts("POST",`${e}/${t}/storage_rename_single`,{meta_key:i,file_key:o,new_name:a})}async downloadFile(e,t,i,o){const a=`${this.apiRoot}dt-posts/v2/${e}/${t}/storage_download`,r=await fetch(a,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce},body:JSON.stringify({meta_key:i,file_key:o})});if(!r.ok){const n=await r.json().catch(()=>({message:"Download failed"}));throw new Error(n.message||"Download failed")}return await r.blob()}}(function(){(function(s){const e=new WeakMap,t=new WeakMap,i=new WeakMap,o=new WeakMap,a=new WeakMap,r=new WeakMap,n=new WeakMap,l=new WeakMap,d=new WeakMap,u=new WeakMap,p=new WeakMap,g=new WeakMap,y=new WeakMap,w=new WeakMap,T=new WeakMap,D={ariaAtomic:"aria-atomic",ariaAutoComplete:"aria-autocomplete",ariaBusy:"aria-busy",ariaChecked:"aria-checked",ariaColCount:"aria-colcount",ariaColIndex:"aria-colindex",ariaColIndexText:"aria-colindextext",ariaColSpan:"aria-colspan",ariaCurrent:"aria-current",ariaDescription:"aria-description",ariaDisabled:"aria-disabled",ariaExpanded:"aria-expanded",ariaHasPopup:"aria-haspopup",ariaHidden:"aria-hidden",ariaInvalid:"aria-invalid",ariaKeyShortcuts:"aria-keyshortcuts",ariaLabel:"aria-label",ariaLevel:"aria-level",ariaLive:"aria-live",ariaModal:"aria-modal",ariaMultiLine:"aria-multiline",ariaMultiSelectable:"aria-multiselectable",ariaOrientation:"aria-orientation",ariaPlaceholder:"aria-placeholder",ariaPosInSet:"aria-posinset",ariaPressed:"aria-pressed",ariaReadOnly:"aria-readonly",ariaRelevant:"aria-relevant",ariaRequired:"aria-required",ariaRoleDescription:"aria-roledescription",ariaRowCount:"aria-rowcount",ariaRowIndex:"aria-rowindex",ariaRowIndexText:"aria-rowindextext",ariaRowSpan:"aria-rowspan",ariaSelected:"aria-selected",ariaSetSize:"aria-setsize",ariaSort:"aria-sort",ariaValueMax:"aria-valuemax",ariaValueMin:"aria-valuemin",ariaValueNow:"aria-valuenow",ariaValueText:"aria-valuetext",role:"role"},j=(f,c)=>{for(let b in D){c[b]=null;let v=null;const $=D[b];Object.defineProperty(c,b,{get(){return v},set(k){v=k,f.isConnected?P(f,$,k):u.set(f,c)}})}};function I(f){const c=o.get(f),{form:b}=c;Po(f,b,c),Lo(f,c.labels)}const Le=(f,c=!1)=>{const b=document.createTreeWalker(f,NodeFilter.SHOW_ELEMENT,{acceptNode(k){return o.has(k)?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let v=b.nextNode();const $=!c||f.disabled;for(;v;)v.formDisabledCallback&&$&&Dt(v,f.disabled),v=b.nextNode()},Je={attributes:!0,attributeFilter:["disabled","name"]},ee=Ye()?new MutationObserver(f=>{for(const c of f){const b=c.target;if(c.attributeName==="disabled"&&(b.constructor.formAssociated?Dt(b,b.hasAttribute("disabled")):b.localName==="fieldset"&&Le(b)),c.attributeName==="name"&&b.constructor.formAssociated){const v=o.get(b),$=d.get(b);v.setFormValue($)}}}):{};function E(f){f.forEach(c=>{const{addedNodes:b,removedNodes:v}=c,$=Array.from(b),k=Array.from(v);$.forEach(S=>{var F;if(o.has(S)&&S.constructor.formAssociated&&I(S),u.has(S)){const C=u.get(S);Object.keys(D).filter(U=>C[U]!==null).forEach(U=>{P(S,D[U],C[U])}),u.delete(S)}if(T.has(S)){const C=T.get(S);P(S,"internals-valid",C.validity.valid.toString()),P(S,"internals-invalid",(!C.validity.valid).toString()),P(S,"aria-invalid",(!C.validity.valid).toString()),T.delete(S)}if(S.localName==="form"){const C=l.get(S),G=document.createTreeWalker(S,NodeFilter.SHOW_ELEMENT,{acceptNode(Ut){return o.has(Ut)&&Ut.constructor.formAssociated&&!(C&&C.has(Ut))?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let U=G.nextNode();for(;U;)I(U),U=G.nextNode()}S.localName==="fieldset"&&((F=ee.observe)===null||F===void 0||F.call(ee,S,Je),Le(S,!0))}),k.forEach(S=>{const F=o.get(S);F&&i.get(F)&&Co(F),n.has(S)&&n.get(S).disconnect()})})}function z(f){f.forEach(c=>{const{removedNodes:b}=c;b.forEach(v=>{const $=y.get(c.target);o.has(v)&&jo(v),$.disconnect()})})}const ce=f=>{var c,b;const v=new MutationObserver(z);!((c=window==null?void 0:window.ShadyDOM)===null||c===void 0)&&c.inUse&&f.mode&&f.host&&(f=f.host),(b=v.observe)===null||b===void 0||b.call(v,f,{childList:!0}),y.set(f,v)};Ye()&&new MutationObserver(E);const te={childList:!0,subtree:!0},P=(f,c,b)=>{f.getAttribute(c)!==b&&f.setAttribute(c,b)},Dt=(f,c)=>{f.toggleAttribute("internals-disabled",c),c?P(f,"aria-disabled","true"):f.removeAttribute("aria-disabled"),f.formDisabledCallback&&f.formDisabledCallback.apply(f,[c])},Co=f=>{i.get(f).forEach(b=>{b.remove()}),i.set(f,[])},Io=(f,c)=>{const b=document.createElement("input");return b.type="hidden",b.name=f.getAttribute("name"),f.after(b),i.get(c).push(b),b},wn=(f,c)=>{var b;i.set(c,[]),(b=ee.observe)===null||b===void 0||b.call(ee,f,Je)},Lo=(f,c)=>{if(c.length){Array.from(c).forEach(v=>v.addEventListener("click",f.click.bind(f)));let b=c[0].id;c[0].id||(b=`${c[0].htmlFor}_Label`,c[0].id=b),P(f,"aria-labelledby",b)}},Qe=f=>{const c=Array.from(f.elements).filter(k=>!k.tagName.includes("-")&&k.validity).map(k=>k.validity.valid),b=l.get(f)||[],v=Array.from(b).filter(k=>k.isConnected).map(k=>o.get(k).validity.valid),$=[...c,...v].includes(!1);f.toggleAttribute("internals-invalid",$),f.toggleAttribute("internals-valid",!$)},$n=f=>{Qe(Xe(f.target))},xn=f=>{Qe(Xe(f.target))},kn=f=>{const c=["button[type=submit]","input[type=submit]","button:not([type])"].map(b=>`${b}:not([disabled])`).map(b=>`${b}:not([form])${f.id?`,${b}[form='${f.id}']`:""}`).join(",");f.addEventListener("click",b=>{if(b.target.closest(c)){const $=l.get(f);if(f.noValidate)return;$.size&&Array.from($).reverse().map(F=>o.get(F).reportValidity()).includes(!1)&&b.preventDefault()}})},Sn=f=>{const c=l.get(f.target);c&&c.size&&c.forEach(b=>{b.constructor.formAssociated&&b.formResetCallback&&b.formResetCallback.apply(b)})},Po=(f,c,b)=>{if(c){const v=l.get(c);if(v)v.add(f);else{const $=new Set;$.add(f),l.set(c,$),kn(c),c.addEventListener("reset",Sn),c.addEventListener("input",$n),c.addEventListener("change",xn)}r.set(c,{ref:f,internals:b}),f.constructor.formAssociated&&f.formAssociatedCallback&&setTimeout(()=>{f.formAssociatedCallback.apply(f,[c])},0),Qe(c)}},Xe=f=>{let c=f.parentNode;return c&&c.tagName!=="FORM"&&(c=Xe(c)),c},K=(f,c,b=DOMException)=>{if(!f.constructor.formAssociated)throw new b(c)},Mo=(f,c,b)=>{const v=l.get(f);return v&&v.size&&v.forEach($=>{o.get($)[b]()||(c=!1)}),c},jo=f=>{if(f.constructor.formAssociated){const c=o.get(f),{labels:b,form:v}=c;Lo(f,b),Po(f,v,c)}};function Ye(){return typeof MutationObserver<"u"}class En{constructor(){this.badInput=!1,this.customError=!1,this.patternMismatch=!1,this.rangeOverflow=!1,this.rangeUnderflow=!1,this.stepMismatch=!1,this.tooLong=!1,this.tooShort=!1,this.typeMismatch=!1,this.valid=!0,this.valueMissing=!1,Object.seal(this)}}const An=f=>(f.badInput=!1,f.customError=!1,f.patternMismatch=!1,f.rangeOverflow=!1,f.rangeUnderflow=!1,f.stepMismatch=!1,f.tooLong=!1,f.tooShort=!1,f.typeMismatch=!1,f.valid=!0,f.valueMissing=!1,f),Tn=(f,c,b)=>(f.valid=On(c),Object.keys(c).forEach(v=>f[v]=c[v]),b&&Qe(b),f),On=f=>{let c=!0;for(let b in f)b!=="valid"&&f[b]!==!1&&(c=!1);return c},Rt=new WeakMap;function Fo(f,c){f.toggleAttribute(c,!0),f.part&&f.part.add(c)}class Nt extends Set{static get isPolyfilled(){return!0}constructor(c){if(super(),!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");Rt.set(this,c)}add(c){if(!/^--/.test(c)||typeof c!="string")throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${c} must start with '--'.`);const b=super.add(c),v=Rt.get(this),$=`state${c}`;return v.isConnected?Fo(v,$):setTimeout(()=>{Fo(v,$)}),b}clear(){for(let[c]of this.entries())this.delete(c);super.clear()}delete(c){const b=super.delete(c),v=Rt.get(this);return v.isConnected?(v.toggleAttribute(`state${c}`,!1),v.part&&v.part.remove(`state${c}`)):setTimeout(()=>{v.toggleAttribute(`state${c}`,!1),v.part&&v.part.remove(`state${c}`)}),b}}function zo(f,c,b,v){if(typeof c=="function"?f!==c||!0:!c.has(f))throw new TypeError("Cannot read private member from an object whose class did not declare it");return b==="m"?v:b==="a"?v.call(f):v?v.value:c.get(f)}function Cn(f,c,b,v,$){if(typeof c=="function"?f!==c||!0:!c.has(f))throw new TypeError("Cannot write private member to an object whose class did not declare it");return c.set(f,b),b}var Pe;class In{constructor(c){Pe.set(this,void 0),Cn(this,Pe,c);for(let b=0;b<c.length;b++){let v=c[b];this[b]=v,v.hasAttribute("name")&&(this[v.getAttribute("name")]=v)}Object.freeze(this)}get length(){return zo(this,Pe,"f").length}[(Pe=new WeakMap,Symbol.iterator)](){return zo(this,Pe,"f")[Symbol.iterator]()}item(c){return this[c]==null?null:this[c]}namedItem(c){return this[c]==null?null:this[c]}}function Ln(){const f=HTMLFormElement.prototype.checkValidity;HTMLFormElement.prototype.checkValidity=b;const c=HTMLFormElement.prototype.reportValidity;HTMLFormElement.prototype.reportValidity=v;function b(...k){let S=f.apply(this,k);return Mo(this,S,"checkValidity")}function v(...k){let S=c.apply(this,k);return Mo(this,S,"reportValidity")}const{get:$}=Object.getOwnPropertyDescriptor(HTMLFormElement.prototype,"elements");Object.defineProperty(HTMLFormElement.prototype,"elements",{get(...k){const S=$.call(this,...k),F=Array.from(l.get(this)||[]);if(F.length===0)return S;const C=Array.from(S).concat(F).sort((G,U)=>G.compareDocumentPosition?G.compareDocumentPosition(U)&2?1:-1:0);return new In(C)}})}class Do{static get isPolyfilled(){return!0}constructor(c){if(!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");const b=c.getRootNode(),v=new En;this.states=new Nt(c),e.set(this,c),t.set(this,v),o.set(c,this),j(c,this),wn(c,this),Object.seal(this),b instanceof DocumentFragment&&ce(b)}checkValidity(){const c=e.get(this);if(K(c,"Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const b=t.get(this);if(!b.valid){const v=new Event("invalid",{bubbles:!1,cancelable:!0,composed:!1});c.dispatchEvent(v)}return b.valid}get form(){const c=e.get(this);K(c,"Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");let b;return c.constructor.formAssociated===!0&&(b=Xe(c)),b}get labels(){const c=e.get(this);K(c,"Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");const b=c.getAttribute("id"),v=c.getRootNode();return v&&b?v.querySelectorAll(`[for="${b}"]`):[]}reportValidity(){const c=e.get(this);if(K(c,"Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const b=this.checkValidity(),v=g.get(this);if(v&&!c.constructor.formAssociated)throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");return!b&&v&&(c.focus(),v.focus()),b}setFormValue(c){const b=e.get(this);if(K(b,"Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."),Co(this),c!=null&&!(c instanceof FormData)){if(b.getAttribute("name")){const v=Io(b,this);v.value=c}}else c!=null&&c instanceof FormData&&Array.from(c).reverse().forEach(([v,$])=>{if(typeof $=="string"){const k=Io(b,this);k.name=v,k.value=$}});d.set(b,c)}setValidity(c,b,v){const $=e.get(this);if(K($,"Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!c)throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");g.set(this,v);const k=t.get(this),S={};for(const G in c)S[G]=c[G];Object.keys(S).length===0&&An(k);const F=Object.assign(Object.assign({},k),S);delete F.valid;const{valid:C}=Tn(k,F,this.form);if(!C&&!b)throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");a.set(this,C?"":b),$.isConnected?($.toggleAttribute("internals-invalid",!C),$.toggleAttribute("internals-valid",C),P($,"aria-invalid",`${!C}`)):T.set($,this)}get shadowRoot(){const c=e.get(this),b=p.get(c);return b||null}get validationMessage(){const c=e.get(this);return K(c,"Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."),a.get(this)}get validity(){const c=e.get(this);return K(c,"Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."),t.get(this)}get willValidate(){const c=e.get(this);return K(c,"Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."),!(c.disabled||c.hasAttribute("disabled")||c.hasAttribute("readonly"))}}function Pn(){if(typeof window>"u"||!window.ElementInternals||!HTMLElement.prototype.attachInternals)return!1;class f extends HTMLElement{constructor(){super(),this.internals=this.attachInternals()}}const c=`element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g,"")}`;customElements.define(c,f);const b=new f;return["shadowRoot","form","willValidate","validity","validationMessage","labels","setFormValue","setValidity","checkValidity","reportValidity"].every(v=>v in b.internals)}let Ro=!1,No=!1;function qt(f){No||(No=!0,window.CustomStateSet=Nt,f&&(HTMLElement.prototype.attachInternals=function(...c){const b=f.call(this,c);return b.states=new Nt(this),b}))}function qo(f=!0){if(!Ro){if(Ro=!0,typeof window<"u"&&(window.ElementInternals=Do),typeof CustomElementRegistry<"u"){const c=CustomElementRegistry.prototype.define;CustomElementRegistry.prototype.define=function(b,v,$){if(v.formAssociated){const k=v.prototype.connectedCallback;v.prototype.connectedCallback=function(){w.has(this)||(w.set(this,!0),this.hasAttribute("disabled")&&Dt(this,!0)),k!=null&&k.apply(this),jo(this)}}c.call(this,b,v,$)}}if(typeof HTMLElement<"u"&&(HTMLElement.prototype.attachInternals=function(){if(this.tagName){if(this.tagName.indexOf("-")===-1)throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.")}else return{};if(o.has(this))throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");return new Do(this)}),typeof Element<"u"){let c=function(...v){const $=b.apply(this,v);if(p.set(this,$),Ye()){const k=new MutationObserver(E);window.ShadyDOM?k.observe(this,te):k.observe($,te),n.set(this,k)}return $};const b=Element.prototype.attachShadow;Element.prototype.attachShadow=c}Ye()&&typeof document<"u"&&new MutationObserver(E).observe(document.documentElement,te),typeof HTMLFormElement<"u"&&Ln(),(f||typeof window<"u"&&!window.CustomStateSet)&&qt()}}return!!customElements.polyfillWrapFlushCallback||(Pn()?typeof window<"u"&&!window.CustomStateSet&&qt(HTMLElement.prototype.attachInternals):qo(!1)),s.forceCustomStateSetPolyfill=qt,s.forceElementInternalsPolyfill=qo,Object.defineProperty(s,"__esModule",{value:!0}),s})({})})();class N extends ae{static get styles(){return[x`
        :host {
        }
      `]}static get properties(){return{RTL:{type:Boolean},locale:{type:String}}}get _focusTarget(){return this.shadowRoot.children[0]instanceof Element?this.shadowRoot.children[0]:null}_standardizeLocale(e){return e&&e.replace(/-/g,"_")}constructor(){super(),ws(this),this.addEventListener("click",this._proxyClick.bind(this)),this.addEventListener("focus",this._proxyFocus.bind(this))}willUpdate(e){if(this.RTL===void 0){const t=this.closest("[dir]");if(t){const i=t.getAttribute("dir");i&&(this.RTL=i.toLowerCase()==="rtl")}}if(!this.locale){const t=this.closest("[lang]");if(t){const i=t.getAttribute("lang");i&&(this.locale=this._standardizeLocale(i))}}if(!this.locale){const t=this.getRootNode();if(t instanceof ShadowRoot&&t.host){const i=t.host;i.locale&&(this.locale=this._standardizeLocale(i.locale))}}if(e&&e.has("locale")&&this.locale)try{js(this.locale)}catch(t){console.error(t)}}_proxyClick(){this.clicked=!0}_proxyFocus(){if(this._focusTarget){if(this.clicked){this.clicked=!1;return}this._focusTarget.focus()}}focus(){this._proxyFocus()}}class _i extends N{static get formAssociated(){return!0}static get styles(){return x`
      :host {
        display: inline-flex;
        width: fit-content;
        height: fit-content;
      }

      .dt-button {
        cursor: pointer;
        display: flex;
        margin: var(--dt-button-margin, 0px);
        padding: var(--dt-button-padding-y, 10px)
          var(--dt-button-padding-x, 10px);
        font-family: var(--dt-button-font-family);
        font-size: var(--dt-button-font-size, 14px);
        line-height: var(--dt-button-line-height, inherit);
        font-weight: var(--dt-button-font-weight, 700);
        background-color: var(
          --dt-button-context-background-color,
          var(--dt-button-background-color)
        );
        border: var(--dt-button-border-width, 1px) solid
          var(--dt-button-context-border-color, var(--dt-button-border-color));
        border-radius: var(--dt-button-border-radius, 10px);
        box-shadow: var(
          --dt-button-box-shadow,
          --dt-button-context-box-shadow(0 2px 4px rgb(0 0 0 / 25%))
        );
        color: var(--dt-button-context-text-color, var(--dt-button-text-color));
        text-rendering: optimizeLegibility;
        gap: var(--dt-button-gap, 10px);
        justify-content: var(--dt-button-justify-content, center);
        align-content: var(--dt-button-align-content, center);
        align-items: var(--dt-button-align-items, center);
        text-decoration: var(
          --dt-button-text-decoration,
          var(--dt-button-context-text-decoration, none)
        );
        text-transform: var(--dt-button-text-transform, none);
        letter-spacing: var(--dt-button-letter-spacing, normal);
        width: var(--dt-button-width, 100%);
        height: var(--dt-button-height, auto);
        aspect-ratio: var(--dt-button-aspect-ratio, auto);
        position: relative;
      }

      .dt-button.dt-button--outline {
        background-color: transparent;
        color: var(--dt-button-context-text-color, var(--text-color-inverse));
      }

      .dt-button--primary:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--primary-color);
        --dt-button-context-background-color: var(--primary-color);
        --dt-button-context-text-color: var(--dt-button-text-color-light);
      }

      .dt-button--link:not(.dt-button--outline) {
        --dt-button-context-text-decoration: underline;
        --dt-button-context-box-shadow: none;
        --dt-button-context-border-color: transparent;
        --dt-button-context-background-color: transparent;
        --dt-button-context-text-color: var(--dt-button-text-color-dark);
      }

      .dt-button--alert:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--alert-color);
        --dt-button-context-background-color: var(--alert-color);
        --dt-button-context-text-color: var(--dt-button-text-color-light);
      }

      .dt-button--caution:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--caution-color);
        --dt-button-context-background-color: var(--caution-color);
        --dt-button-context-text-color: var(--dt-button-text-color-dark);
      }

      .dt-button--success:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--success-color);
        --dt-button-context-background-color: var(--success-color);
        --dt-button-context-text-color: var(--dt-button-text-color-light);
      }

      .dt-button--inactive:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--dt-button-inactive-color);
        --dt-button-context-background-color: var(--dt-button-inactive-color);
        --dt-button-context-text-color: var(--dt-button-text-color-dark);
      }

      .dt-button--disabled:not(.dt-button--outline) {
        --dt-button-context-border-color: var(--disabled-color);
        --dt-button-context-background-color: var(--disabled-color);
        --dt-button-context-text-color: var(--dt-button-text-color-dark);
      }

      .dt-button--primary.dt-button--outline {
        --dt-button-context-border-color: var(--primary-color);
        --dt-button-context-text-color: var(--primary-color);
      }

      .dt-button--alert.dt-button--outline {
        --dt-button-context-border-color: var(--alert-color);
        --dt-button-context-text-color: var(--alert-color);
      }

      .dt-button--caution.dt-button--outline {
        --dt-button-context-border-color: var(--caution-color);
        --dt-button-context-text-color: var(--caution-color);
      }

      .dt-button--success.dt-button--outline {
        --dt-button-context-border-color: var(--success-color);
        --dt-button-context-text-color: var(--success-color);
      }

      .dt-button--inactive.dt-button--outline {
        --dt-button-context-border-color: var(--dt-button-inactive-color);
        --dt-button-context-text-color: var(--dt-button-text-color-dark);
      }

      .dt-button--disabled.dt-button--outline {
        --dt-button-context-border-color: var(--disabled-color);
      }

      .dt-button.dt-button--round {
        --dt-button-border-radius: 50%;
        --dt-button-padding-x: 0px;
        --dt-button-padding-y: 0px;
        --dt-button-aspect-ratio: var(--dt-button-round-aspect-ratio, 1/1);
      }

      .dt-button[disabled] {
        opacity: 0.5;
        &:hover {
          cursor: not-allowed;
        }
      }
    `}static get properties(){return{label:{type:String},context:{type:String},type:{type:String},title:{type:String},outline:{type:Boolean},round:{type:Boolean},disabled:{type:Boolean}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--round":this.round},t=`dt-button--${this.context}`;return e[t]=!0,e}get _field(){return this.shadowRoot.querySelector("button")}get _focusTarget(){return this._field}constructor(){super(),this.context="default",this.internals=this.attachInternals()}handleClick(e){e.preventDefault(),this.type==="submit"&&this.internals.form&&this.internals.form.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0}))}render(){const e={...this.classes};return h`
      <button
        part="button"
        class=${A(e)}
        title=${this.title}
        type=${this.type}
        @click=${this.handleClick}
        ?disabled=${this.disabled}
      >
        <slot></slot>
      </button>
    `}}window.customElements.define("dt-button",_i);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const wi="important",Fs=" !"+wi,X=ut(class extends ht{constructor(s){var e;if(super(s),s.type!==ct.ATTRIBUTE||s.name!=="style"||((e=s.strings)==null?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(s){return Object.keys(s).reduce((e,t)=>{const i=s[t];return i==null?e:e+`${t=t.includes("-")?t:t.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${i};`},"")}update(s,[e]){const{style:t}=s.element;if(this.ft===void 0)return this.ft=new Set(Object.keys(e)),this.render(e);for(const i of this.ft)e[i]==null&&(this.ft.delete(i),i.includes("-")?t.removeProperty(i):t[i]=null);for(const i in e){const o=e[i];if(o!=null){this.ft.add(i);const a=typeof o=="string"&&o.endsWith(Fs);i.includes("-")||a?t.setProperty(i,a?o.slice(0,-11):o,a?wi:""):t[i]=o}}return V}});/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/const $i=Object.freeze({left:0,top:0,width:16,height:16}),qe=Object.freeze({rotate:0,vFlip:!1,hFlip:!1}),$e=Object.freeze({...$i,...qe}),gt=Object.freeze({...$e,body:"",hidden:!1}),zs=Object.freeze({width:null,height:null}),xi=Object.freeze({...zs,...qe});function Ds(s,e=0){const t=s.replace(/^-?[0-9.]*/,"");function i(o){for(;o<0;)o+=4;return o%4}if(t===""){const o=parseInt(s);return isNaN(o)?0:i(o)}else if(t!==s){let o=0;switch(t){case"%":o=25;break;case"deg":o=90}if(o){let a=parseFloat(s.slice(0,s.length-t.length));return isNaN(a)?0:(a=a/o,a%1===0?i(a):0)}}return e}const Rs=/[\s,]+/;function Ns(s,e){e.split(Rs).forEach(t=>{switch(t.trim()){case"horizontal":s.hFlip=!0;break;case"vertical":s.vFlip=!0;break}})}const ki={...xi,preserveAspectRatio:""};function Si(s){const e={...ki},t=(i,o)=>s.getAttribute(i)||o;return e.width=t("width",null),e.height=t("height",null),e.rotate=Ds(t("rotate","")),Ns(e,t("flip","")),e.preserveAspectRatio=t("preserveAspectRatio",t("preserveaspectratio","")),e}function qs(s,e){for(const t in ki)if(s[t]!==e[t])return!0;return!1}const xe=/^[a-z0-9]+(-[a-z0-9]+)*$/,ke=(s,e,t,i="")=>{const o=s.split(":");if(s.slice(0,1)==="@"){if(o.length<2||o.length>3)return null;i=o.shift().slice(1)}if(o.length>3||!o.length)return null;if(o.length>1){const n=o.pop(),l=o.pop(),d={provider:o.length>0?o[0]:i,prefix:l,name:n};return e&&!Ue(d)?null:d}const a=o[0],r=a.split("-");if(r.length>1){const n={provider:i,prefix:r.shift(),name:r.join("-")};return e&&!Ue(n)?null:n}if(t&&i===""){const n={provider:i,prefix:"",name:a};return e&&!Ue(n,t)?null:n}return null},Ue=(s,e)=>s?!!((s.provider===""||s.provider.match(xe))&&(e&&s.prefix===""||s.prefix.match(xe))&&s.name.match(xe)):!1;function Us(s,e){const t={};!s.hFlip!=!e.hFlip&&(t.hFlip=!0),!s.vFlip!=!e.vFlip&&(t.vFlip=!0);const i=((s.rotate||0)+(e.rotate||0))%4;return i&&(t.rotate=i),t}function Ei(s,e){const t=Us(s,e);for(const i in gt)i in qe?i in s&&!(i in t)&&(t[i]=qe[i]):i in e?t[i]=e[i]:i in s&&(t[i]=s[i]);return t}function Vs(s,e){const t=s.icons,i=s.aliases||Object.create(null),o=Object.create(null);function a(r){if(t[r])return o[r]=[];if(!(r in o)){o[r]=null;const n=i[r]&&i[r].parent,l=n&&a(n);l&&(o[r]=[n].concat(l))}return o[r]}return Object.keys(t).concat(Object.keys(i)).forEach(a),o}function Bs(s,e,t){const i=s.icons,o=s.aliases||Object.create(null);let a={};function r(n){a=Ei(i[n]||o[n],a)}return r(e),t.forEach(r),Ei(s,a)}function Ai(s,e){const t=[];if(typeof s!="object"||typeof s.icons!="object")return t;s.not_found instanceof Array&&s.not_found.forEach(o=>{e(o,null),t.push(o)});const i=Vs(s);for(const o in i){const a=i[o];a&&(e(o,Bs(s,o,a)),t.push(o))}return t}const Hs={provider:"",aliases:{},not_found:{},...$i};function vt(s,e){for(const t in e)if(t in s&&typeof s[t]!=typeof e[t])return!1;return!0}function Ti(s){if(typeof s!="object"||s===null)return null;const e=s;if(typeof e.prefix!="string"||!s.icons||typeof s.icons!="object"||!vt(s,Hs))return null;const t=e.icons;for(const o in t){const a=t[o];if(!o.match(xe)||typeof a.body!="string"||!vt(a,gt))return null}const i=e.aliases||Object.create(null);for(const o in i){const a=i[o],r=a.parent;if(!o.match(xe)||typeof r!="string"||!t[r]&&!i[r]||!vt(a,gt))return null}return e}const Ve=Object.create(null);function Ks(s,e){return{provider:s,prefix:e,icons:Object.create(null),missing:new Set}}function Y(s,e){const t=Ve[s]||(Ve[s]=Object.create(null));return t[e]||(t[e]=Ks(s,e))}function yt(s,e){return Ti(e)?Ai(e,(t,i)=>{i?s.icons[t]=i:s.missing.add(t)}):[]}function Gs(s,e,t){try{if(typeof t.body=="string")return s.icons[e]={...t},!0}catch{}return!1}function Ws(s,e){let t=[];return(typeof s=="string"?[s]:Object.keys(Ve)).forEach(o=>{(typeof o=="string"&&typeof e=="string"?[e]:Object.keys(Ve[o]||{})).forEach(r=>{const n=Y(o,r);t=t.concat(Object.keys(n.icons).map(l=>(o!==""?"@"+o+":":"")+r+":"+l))})}),t}let Se=!1;function Oi(s){return typeof s=="boolean"&&(Se=s),Se}function Ee(s){const e=typeof s=="string"?ke(s,!0,Se):s;if(e){const t=Y(e.provider,e.prefix),i=e.name;return t.icons[i]||(t.missing.has(i)?null:void 0)}}function Ci(s,e){const t=ke(s,!0,Se);if(!t)return!1;const i=Y(t.provider,t.prefix);return Gs(i,t.name,e)}function Ii(s,e){if(typeof s!="object")return!1;if(typeof e!="string"&&(e=s.provider||""),Se&&!e&&!s.prefix){let o=!1;return Ti(s)&&(s.prefix="",Ai(s,(a,r)=>{r&&Ci(a,r)&&(o=!0)})),o}const t=s.prefix;if(!Ue({provider:e,prefix:t,name:"a"}))return!1;const i=Y(e,t);return!!yt(i,s)}function Zs(s){return!!Ee(s)}function Js(s){const e=Ee(s);return e?{...$e,...e}:null}function Qs(s){const e={loaded:[],missing:[],pending:[]},t=Object.create(null);s.sort((o,a)=>o.provider!==a.provider?o.provider.localeCompare(a.provider):o.prefix!==a.prefix?o.prefix.localeCompare(a.prefix):o.name.localeCompare(a.name));let i={provider:"",prefix:"",name:""};return s.forEach(o=>{if(i.name===o.name&&i.prefix===o.prefix&&i.provider===o.provider)return;i=o;const a=o.provider,r=o.prefix,n=o.name,l=t[a]||(t[a]=Object.create(null)),d=l[r]||(l[r]=Y(a,r));let u;n in d.icons?u=e.loaded:r===""||d.missing.has(n)?u=e.missing:u=e.pending;const p={provider:a,prefix:r,name:n};u.push(p)}),e}function Li(s,e){s.forEach(t=>{const i=t.loaderCallbacks;i&&(t.loaderCallbacks=i.filter(o=>o.id!==e))})}function Xs(s){s.pendingCallbacksFlag||(s.pendingCallbacksFlag=!0,setTimeout(()=>{s.pendingCallbacksFlag=!1;const e=s.loaderCallbacks?s.loaderCallbacks.slice(0):[];if(!e.length)return;let t=!1;const i=s.provider,o=s.prefix;e.forEach(a=>{const r=a.icons,n=r.pending.length;r.pending=r.pending.filter(l=>{if(l.prefix!==o)return!0;const d=l.name;if(s.icons[d])r.loaded.push({provider:i,prefix:o,name:d});else if(s.missing.has(d))r.missing.push({provider:i,prefix:o,name:d});else return t=!0,!0;return!1}),r.pending.length!==n&&(t||Li([s],a.id),a.callback(r.loaded.slice(0),r.missing.slice(0),r.pending.slice(0),a.abort))})}))}let Ys=0;function ea(s,e,t){const i=Ys++,o=Li.bind(null,t,i);if(!e.pending.length)return o;const a={id:i,icons:e,callback:s,abort:o};return t.forEach(r=>{(r.loaderCallbacks||(r.loaderCallbacks=[])).push(a)}),o}const _t=Object.create(null);function Pi(s,e){_t[s]=e}function wt(s){return _t[s]||_t[""]}function ta(s,e=!0,t=!1){const i=[];return s.forEach(o=>{const a=typeof o=="string"?ke(o,e,t):o;a&&i.push(a)}),i}var ia={resources:[],index:0,timeout:2e3,rotate:750,random:!1,dataAfterTimeout:!1};function oa(s,e,t,i){const o=s.resources.length,a=s.random?Math.floor(Math.random()*o):s.index;let r;if(s.random){let E=s.resources.slice(0);for(r=[];E.length>1;){const z=Math.floor(Math.random()*E.length);r.push(E[z]),E=E.slice(0,z).concat(E.slice(z+1))}r=r.concat(E)}else r=s.resources.slice(a).concat(s.resources.slice(0,a));const n=Date.now();let l="pending",d=0,u,p=null,g=[],y=[];typeof i=="function"&&y.push(i);function w(){p&&(clearTimeout(p),p=null)}function T(){l==="pending"&&(l="aborted"),w(),g.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),g=[]}function D(E,z){z&&(y=[]),typeof E=="function"&&y.push(E)}function j(){return{startTime:n,payload:e,status:l,queriesSent:d,queriesPending:g.length,subscribe:D,abort:T}}function I(){l="failed",y.forEach(E=>{E(void 0,u)})}function Le(){g.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),g=[]}function Je(E,z,ce){const te=z!=="success";switch(g=g.filter(P=>P!==E),l){case"pending":break;case"failed":if(te||!s.dataAfterTimeout)return;break;default:return}if(z==="abort"){u=ce,I();return}if(te){u=ce,g.length||(r.length?ee():I());return}if(w(),Le(),!s.random){const P=s.resources.indexOf(E.resource);P!==-1&&P!==s.index&&(s.index=P)}l="completed",y.forEach(P=>{P(ce)})}function ee(){if(l!=="pending")return;w();const E=r.shift();if(E===void 0){if(g.length){p=setTimeout(()=>{w(),l==="pending"&&(Le(),I())},s.timeout);return}I();return}const z={status:"pending",resource:E,callback:(ce,te)=>{Je(z,ce,te)}};g.push(z),d++,p=setTimeout(ee,s.rotate),t(E,e,z.callback)}return setTimeout(ee),j}function Mi(s){const e={...ia,...s};let t=[];function i(){t=t.filter(n=>n().status==="pending")}function o(n,l,d){const u=oa(e,n,l,(p,g)=>{i(),d&&d(p,g)});return t.push(u),u}function a(n){return t.find(l=>n(l))||null}return{query:o,find:a,setIndex:n=>{e.index=n},getIndex:()=>e.index,cleanup:i}}function $t(s){let e;if(typeof s.resources=="string")e=[s.resources];else if(e=s.resources,!(e instanceof Array)||!e.length)return null;return{resources:e,path:s.path||"/",maxURL:s.maxURL||500,rotate:s.rotate||750,timeout:s.timeout||5e3,random:s.random===!0,index:s.index||0,dataAfterTimeout:s.dataAfterTimeout!==!1}}const Be=Object.create(null),Ae=["https://api.simplesvg.com","https://api.unisvg.com"],He=[];for(;Ae.length>0;)Ae.length===1||Math.random()>.5?He.push(Ae.shift()):He.push(Ae.pop());Be[""]=$t({resources:["https://api.iconify.design"].concat(He)});function ji(s,e){const t=$t(e);return t===null?!1:(Be[s]=t,!0)}function Ke(s){return Be[s]}function sa(){return Object.keys(Be)}function Fi(){}const xt=Object.create(null);function aa(s){if(!xt[s]){const e=Ke(s);if(!e)return;const t=Mi(e),i={config:e,redundancy:t};xt[s]=i}return xt[s]}function zi(s,e,t){let i,o;if(typeof s=="string"){const a=wt(s);if(!a)return t(void 0,424),Fi;o=a.send;const r=aa(s);r&&(i=r.redundancy)}else{const a=$t(s);if(a){i=Mi(a);const r=s.resources?s.resources[0]:"",n=wt(r);n&&(o=n.send)}}return!i||!o?(t(void 0,424),Fi):i.query(e,o,t)().abort}const Di="iconify2",Te="iconify",Ri=Te+"-count",Ni=Te+"-version",qi=36e5,ra=168;function kt(s,e){try{return s.getItem(e)}catch{}}function St(s,e,t){try{return s.setItem(e,t),!0}catch{}}function Ui(s,e){try{s.removeItem(e)}catch{}}function Et(s,e){return St(s,Ri,e.toString())}function At(s){return parseInt(kt(s,Ri))||0}const ne={local:!0,session:!0},Vi={local:new Set,session:new Set};let Tt=!1;function na(s){Tt=s}let Ge=typeof window>"u"?{}:window;function Bi(s){const e=s+"Storage";try{if(Ge&&Ge[e]&&typeof Ge[e].length=="number")return Ge[e]}catch{}ne[s]=!1}function Hi(s,e){const t=Bi(s);if(!t)return;const i=kt(t,Ni);if(i!==Di){if(i){const n=At(t);for(let l=0;l<n;l++)Ui(t,Te+l.toString())}St(t,Ni,Di),Et(t,0);return}const o=Math.floor(Date.now()/qi)-ra,a=n=>{const l=Te+n.toString(),d=kt(t,l);if(typeof d=="string"){try{const u=JSON.parse(d);if(typeof u=="object"&&typeof u.cached=="number"&&u.cached>o&&typeof u.provider=="string"&&typeof u.data=="object"&&typeof u.data.prefix=="string"&&e(u,n))return!0}catch{}Ui(t,l)}};let r=At(t);for(let n=r-1;n>=0;n--)a(n)||(n===r-1?(r--,Et(t,r)):Vi[s].add(n))}function Ki(){if(!Tt){na(!0);for(const s in ne)Hi(s,e=>{const t=e.data,i=e.provider,o=t.prefix,a=Y(i,o);if(!yt(a,t).length)return!1;const r=t.lastModified||-1;return a.lastModifiedCached=a.lastModifiedCached?Math.min(a.lastModifiedCached,r):r,!0})}}function la(s,e){const t=s.lastModifiedCached;if(t&&t>=e)return t===e;if(s.lastModifiedCached=e,t)for(const i in ne)Hi(i,o=>{const a=o.data;return o.provider!==s.provider||a.prefix!==s.prefix||a.lastModified===e});return!0}function da(s,e){Tt||Ki();function t(i){let o;if(!ne[i]||!(o=Bi(i)))return;const a=Vi[i];let r;if(a.size)a.delete(r=Array.from(a).shift());else if(r=At(o),!Et(o,r+1))return;const n={cached:Math.floor(Date.now()/qi),provider:s.provider,data:e};return St(o,Te+r.toString(),JSON.stringify(n))}e.lastModified&&!la(s,e.lastModified)||Object.keys(e.icons).length&&(e.not_found&&(e=Object.assign({},e),delete e.not_found),t("local")||t("session"))}function Gi(){}function ca(s){s.iconsLoaderFlag||(s.iconsLoaderFlag=!0,setTimeout(()=>{s.iconsLoaderFlag=!1,Xs(s)}))}function ua(s,e){s.iconsToLoad?s.iconsToLoad=s.iconsToLoad.concat(e).sort():s.iconsToLoad=e,s.iconsQueueFlag||(s.iconsQueueFlag=!0,setTimeout(()=>{s.iconsQueueFlag=!1;const{provider:t,prefix:i}=s,o=s.iconsToLoad;delete s.iconsToLoad;let a;if(!o||!(a=wt(t)))return;a.prepare(t,i,o).forEach(n=>{zi(t,n,l=>{if(typeof l!="object")n.icons.forEach(d=>{s.missing.add(d)});else try{const d=yt(s,l);if(!d.length)return;const u=s.pendingIcons;u&&d.forEach(p=>{u.delete(p)}),da(s,l)}catch(d){console.error(d)}ca(s)})})}))}const Ot=(s,e)=>{const t=ta(s,!0,Oi()),i=Qs(t);if(!i.pending.length){let l=!0;return e&&setTimeout(()=>{l&&e(i.loaded,i.missing,i.pending,Gi)}),()=>{l=!1}}const o=Object.create(null),a=[];let r,n;return i.pending.forEach(l=>{const{provider:d,prefix:u}=l;if(u===n&&d===r)return;r=d,n=u,a.push(Y(d,u));const p=o[d]||(o[d]=Object.create(null));p[u]||(p[u]=[])}),i.pending.forEach(l=>{const{provider:d,prefix:u,name:p}=l,g=Y(d,u),y=g.pendingIcons||(g.pendingIcons=new Set);y.has(p)||(y.add(p),o[d][u].push(p))}),a.forEach(l=>{const{provider:d,prefix:u}=l;o[d][u].length&&ua(l,o[d][u])}),e?ea(e,i,a):Gi},ha=s=>new Promise((e,t)=>{const i=typeof s=="string"?ke(s,!0):s;if(!i){t(s);return}Ot([i||s],o=>{if(o.length&&i){const a=Ee(i);if(a){e({...$e,...a});return}}t(s)})});function pa(s){try{const e=typeof s=="string"?JSON.parse(s):s;if(typeof e.body=="string")return{...e}}catch{}}function fa(s,e){const t=typeof s=="string"?ke(s,!0,!0):null;if(!t){const a=pa(s);return{value:s,data:a}}const i=Ee(t);if(i!==void 0||!t.prefix)return{value:s,name:t,data:i};const o=Ot([t],()=>e(s,t,Ee(t)));return{value:s,name:t,loading:o}}function Ct(s){return s.hasAttribute("inline")}let Wi=!1;try{Wi=navigator.vendor.indexOf("Apple")===0}catch{}function ba(s,e){switch(e){case"svg":case"bg":case"mask":return e}return e!=="style"&&(Wi||s.indexOf("<a")===-1)?"svg":s.indexOf("currentColor")===-1?"bg":"mask"}const ma=/(-?[0-9.]*[0-9]+[0-9.]*)/g,ga=/^-?[0-9.]*[0-9]+[0-9.]*$/g;function It(s,e,t){if(e===1)return s;if(t=t||100,typeof s=="number")return Math.ceil(s*e*t)/t;if(typeof s!="string")return s;const i=s.split(ma);if(i===null||!i.length)return s;const o=[];let a=i.shift(),r=ga.test(a);for(;;){if(r){const n=parseFloat(a);isNaN(n)?o.push(a):o.push(Math.ceil(n*e*t)/t)}else o.push(a);if(a=i.shift(),a===void 0)return o.join("");r=!r}}function Zi(s,e){const t={...$e,...s},i={...xi,...e},o={left:t.left,top:t.top,width:t.width,height:t.height};let a=t.body;[t,i].forEach(y=>{const w=[],T=y.hFlip,D=y.vFlip;let j=y.rotate;T?D?j+=2:(w.push("translate("+(o.width+o.left).toString()+" "+(0-o.top).toString()+")"),w.push("scale(-1 1)"),o.top=o.left=0):D&&(w.push("translate("+(0-o.left).toString()+" "+(o.height+o.top).toString()+")"),w.push("scale(1 -1)"),o.top=o.left=0);let I;switch(j<0&&(j-=Math.floor(j/4)*4),j=j%4,j){case 1:I=o.height/2+o.top,w.unshift("rotate(90 "+I.toString()+" "+I.toString()+")");break;case 2:w.unshift("rotate(180 "+(o.width/2+o.left).toString()+" "+(o.height/2+o.top).toString()+")");break;case 3:I=o.width/2+o.left,w.unshift("rotate(-90 "+I.toString()+" "+I.toString()+")");break}j%2===1&&(o.left!==o.top&&(I=o.left,o.left=o.top,o.top=I),o.width!==o.height&&(I=o.width,o.width=o.height,o.height=I)),w.length&&(a='<g transform="'+w.join(" ")+'">'+a+"</g>")});const r=i.width,n=i.height,l=o.width,d=o.height;let u,p;return r===null?(p=n===null?"1em":n==="auto"?d:n,u=It(p,l/d)):(u=r==="auto"?l:r,p=n===null?It(u,d/l):n==="auto"?d:n),{attributes:{width:u.toString(),height:p.toString(),viewBox:o.left.toString()+" "+o.top.toString()+" "+l.toString()+" "+d.toString()},body:a}}let We=(()=>{let s;try{if(s=fetch,typeof s=="function")return s}catch{}})();function va(s){We=s}function ya(){return We}function _a(s,e){const t=Ke(s);if(!t)return 0;let i;if(!t.maxURL)i=0;else{let o=0;t.resources.forEach(r=>{o=Math.max(o,r.length)});const a=e+".json?icons=";i=t.maxURL-o-t.path.length-a.length}return i}function wa(s){return s===404}const $a=(s,e,t)=>{const i=[],o=_a(s,e),a="icons";let r={type:a,provider:s,prefix:e,icons:[]},n=0;return t.forEach((l,d)=>{n+=l.length+1,n>=o&&d>0&&(i.push(r),r={type:a,provider:s,prefix:e,icons:[]},n=l.length),r.icons.push(l)}),i.push(r),i};function xa(s){if(typeof s=="string"){const e=Ke(s);if(e)return e.path}return"/"}const ka={prepare:$a,send:(s,e,t)=>{if(!We){t("abort",424);return}let i=xa(e.provider);switch(e.type){case"icons":{const a=e.prefix,n=e.icons.join(","),l=new URLSearchParams({icons:n});i+=a+".json?"+l.toString();break}case"custom":{const a=e.uri;i+=a.slice(0,1)==="/"?a.slice(1):a;break}default:t("abort",400);return}let o=503;We(s+i).then(a=>{const r=a.status;if(r!==200){setTimeout(()=>{t(wa(r)?"abort":"next",r)});return}return o=501,a.json()}).then(a=>{if(typeof a!="object"||a===null){setTimeout(()=>{a===404?t("abort",a):t("next",o)});return}setTimeout(()=>{t("success",a)})}).catch(()=>{t("next",o)})}};function Ji(s,e){switch(s){case"local":case"session":ne[s]=e;break;case"all":for(const t in ne)ne[t]=e;break}}function Qi(){Pi("",ka),Oi(!0);let s;try{s=window}catch{}if(s){if(Ki(),s.IconifyPreload!==void 0){const t=s.IconifyPreload,i="Invalid IconifyPreload syntax.";typeof t=="object"&&t!==null&&(t instanceof Array?t:[t]).forEach(o=>{try{(typeof o!="object"||o===null||o instanceof Array||typeof o.icons!="object"||typeof o.prefix!="string"||!Ii(o))&&console.error(i)}catch{console.error(i)}})}if(s.IconifyProviders!==void 0){const t=s.IconifyProviders;if(typeof t=="object"&&t!==null)for(const i in t){const o="IconifyProviders["+i+"] is invalid.";try{const a=t[i];if(typeof a!="object"||!a||a.resources===void 0)continue;ji(i,a)||console.error(o)}catch{console.error(o)}}}}return{enableCache:t=>Ji(t,!0),disableCache:t=>Ji(t,!1),iconExists:Zs,getIcon:Js,listIcons:Ws,addIcon:Ci,addCollection:Ii,calculateSize:It,buildIcon:Zi,loadIcons:Ot,loadIcon:ha,addAPIProvider:ji,_api:{getAPIConfig:Ke,setAPIModule:Pi,sendAPIQuery:zi,setFetch:va,getFetch:ya,listAPIProviders:sa}}}function Xi(s,e){let t=s.indexOf("xlink:")===-1?"":' xmlns:xlink="http://www.w3.org/1999/xlink"';for(const i in e)t+=" "+i+'="'+e[i]+'"';return'<svg xmlns="http://www.w3.org/2000/svg"'+t+">"+s+"</svg>"}function Sa(s){return s.replace(/"/g,"'").replace(/%/g,"%25").replace(/#/g,"%23").replace(/</g,"%3C").replace(/>/g,"%3E").replace(/\s+/g," ")}function Ea(s){return'url("data:image/svg+xml,'+Sa(s)+'")'}const Lt={"background-color":"currentColor"},Yi={"background-color":"transparent"},eo={image:"var(--svg)",repeat:"no-repeat",size:"100% 100%"},to={"-webkit-mask":Lt,mask:Lt,background:Yi};for(const s in to){const e=to[s];for(const t in eo)e[s+"-"+t]=eo[t]}function io(s){return s+(s.match(/^[-0-9.]+$/)?"px":"")}function Aa(s,e,t){const i=document.createElement("span");let o=s.body;o.indexOf("<a")!==-1&&(o+="<!-- "+Date.now()+" -->");const a=s.attributes,r=Xi(o,{...a,width:e.width+"",height:e.height+""}),n=Ea(r),l=i.style,d={"--svg":n,width:io(a.width),height:io(a.height),...t?Lt:Yi};for(const u in d)l.setProperty(u,d[u]);return i}function Ta(s){const e=document.createElement("span");return e.innerHTML=Xi(s.body,s.attributes),e.firstChild}function oo(s,e){const t=e.icon.data,i=e.customisations,o=Zi(t,i);i.preserveAspectRatio&&(o.attributes.preserveAspectRatio=i.preserveAspectRatio);const a=e.renderedMode;let r;switch(a){case"svg":r=Ta(o);break;default:r=Aa(o,{...$e,...t},a==="mask")}const n=Array.from(s.childNodes).find(l=>{const d=l.tagName&&l.tagName.toUpperCase();return d==="SPAN"||d==="SVG"});n?r.tagName==="SPAN"&&n.tagName===r.tagName?n.setAttribute("style",r.getAttribute("style")):s.replaceChild(r,n):s.appendChild(r)}const Pt="data-style";function so(s,e){let t=Array.from(s.childNodes).find(i=>i.hasAttribute&&i.hasAttribute(Pt));t||(t=document.createElement("style"),t.setAttribute(Pt,Pt),s.appendChild(t)),t.textContent=":host{display:inline-block;vertical-align:"+(e?"-0.125em":"0")+"}span,svg{display:block}"}function ao(s,e,t){const i=t&&(t.rendered?t:t.lastRender);return{rendered:!1,inline:e,icon:s,lastRender:i}}function Oa(s="iconify-icon"){let e,t;try{e=window.customElements,t=window.HTMLElement}catch{return}if(!e||!t)return;const i=e.get(s);if(i)return i;const o=["icon","mode","inline","width","height","rotate","flip"],a=class extends t{constructor(){super();Me(this,"_shadowRoot");Me(this,"_state");Me(this,"_checkQueued",!1);const l=this._shadowRoot=this.attachShadow({mode:"open"}),d=Ct(this);so(l,d),this._state=ao({value:""},d),this._queueCheck()}static get observedAttributes(){return o.slice(0)}attributeChangedCallback(l){if(l==="inline"){const d=Ct(this),u=this._state;d!==u.inline&&(u.inline=d,so(this._shadowRoot,d))}else this._queueCheck()}get icon(){const l=this.getAttribute("icon");if(l&&l.slice(0,1)==="{")try{return JSON.parse(l)}catch{}return l}set icon(l){typeof l=="object"&&(l=JSON.stringify(l)),this.setAttribute("icon",l)}get inline(){return Ct(this)}set inline(l){this.setAttribute("inline",l?"true":null)}restartAnimation(){const l=this._state;if(l.rendered){const d=this._shadowRoot;if(l.renderedMode==="svg")try{d.lastChild.setCurrentTime(0);return}catch{}oo(d,l)}}get status(){const l=this._state;return l.rendered?"rendered":l.icon.data===null?"failed":"loading"}_queueCheck(){this._checkQueued||(this._checkQueued=!0,setTimeout(()=>{this._check()}))}_check(){if(!this._checkQueued)return;this._checkQueued=!1;const l=this._state,d=this.getAttribute("icon");if(d!==l.icon.value){this._iconChanged(d);return}if(!l.rendered)return;const u=this.getAttribute("mode"),p=Si(this);(l.attrMode!==u||qs(l.customisations,p))&&this._renderIcon(l.icon,p,u)}_iconChanged(l){const d=fa(l,(u,p,g)=>{const y=this._state;if(y.rendered||this.getAttribute("icon")!==u)return;const w={value:u,name:p,data:g};w.data?this._gotIconData(w):y.icon=w});d.data?this._gotIconData(d):this._state=ao(d,this._state.inline,this._state)}_gotIconData(l){this._checkQueued=!1,this._renderIcon(l,Si(this),this.getAttribute("mode"))}_renderIcon(l,d,u){const p=ba(l.data.body,u),g=this._state.inline;oo(this._shadowRoot,this._state={rendered:!0,icon:l,inline:g,customisations:d,attrMode:u,renderedMode:p})}};o.forEach(n=>{n in a.prototype||Object.defineProperty(a.prototype,n,{get:function(){return this.getAttribute(n)},set:function(l){this.setAttribute(n,l)}})});const r=Qi();for(const n in r)a[n]=a.prototype[n]=r[n];return e.define(s,a),a}const Ca=Oa()||Qi(),{enableCache:Nn,disableCache:qn,iconExists:Un,getIcon:Vn,listIcons:Bn,addIcon:Hn,addCollection:Kn,calculateSize:Gn,buildIcon:Wn,loadIcons:Zn,loadIcon:Jn,addAPIProvider:Qn,_api:Xn}=Ca;class ro extends N{static get styles(){return x`
      :root {
        pointer-events: none;
      }
      :root,
      .icon-container {
        font-size: inherit;
        color: inherit;
        display: inline-flex;
        width: fit-content;
        height: fit-content;
        position: relative;
        font-family: var(--font-family);
        pointer-events: auto;
      }
      .tooltip {
        --tt-padding: 0.25rem;
        position: absolute;
        right: 0px;
        top: calc(-1lh - var(--tt-padding) - var(--tt-padding) - 4px);
        min-width: max-content;
        border: solid 1px currentcolor;
        background-color: color(
          from var(--dt-form-background-color, var(--surface-1)) srgb 1 1 1 /
            0.7
        );
        padding: var(--tt-padding);
        border-radius: 0.25rem;
        text-align: end;
        z-index: 1;
        display: block;
      }
      .tooltip:before {
        position: absolute;
        right: 0.7rem;
        top: calc(1lh + var(--tt-padding) + var(--tt-padding) + 1px);
        content: ' ';
        border-width: 0.25rem;
        border-style: solid;
        border-color: currentcolor transparent transparent transparent;
      }
      .tooltip[hidden] {
        display: none;
      }

      .tooltip.slotted .attr-msg {
        display: none;
      }

      .tooltip:hover {
        opacity: 0.25;
      }
    `}static get properties(){return{...super.properties,icon:{type:String},tooltip:{type:String},tooltip_open:{type:Boolean},size:{type:String},slotted:{type:Boolean,attribute:!1}}}firstUpdated(){const e=this.shadowRoot.querySelector("slot[name=tooltip]");e&&e.addEventListener("slotchange",t=>{const o=t.target.assignedNodes();let a=!1;o.length>0&&(o[0].tagName==="SLOT"?a=o[0].assignedNodes().length>0:a=!0),this.slotted=a})}_toggleTooltip(){this.tooltip_open?this.tooltip_open=!1:this.tooltip_open=!0}tooltipClasses(){return{tooltip:!0,slotted:this.slotted}}render(){const e=this.tooltip?h`<div
          class="${A(this.tooltipClasses())}"
          ?hidden=${this.tooltip_open}
          @click="${this._toggleTooltip}"
        >
          <slot name="tooltip"></slot>
          <span class="attr-msg">${this.tooltip}</span>
        </div>`:null;return h`
      <div class="icon-container">
        <iconify-icon
          icon=${this.icon}
          width="${this.size}"
          @click=${this._toggleTooltip}
        ></iconify-icon>
        ${e}
      </div>
    `}}window.customElements.define("dt-icon",ro);class no extends N{static get styles(){return x`
      :host {
        --dt-label-font-size: 14px;

        font-family: var(--font-family);
        font-size: var(--dt-label-font-size, 14px);
        font-weight: var(--dt-label-font-weight, 700);
        color: var(--dt-label-color, var(--dt-form-text-color, #000));
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .label {
        text-overflow: ellipsis;
        overflow-x: clip;
        display: flex;
        gap: 0.5ch;
        padding-inline-end: 1ch;

        > .label-text {
          flex-grow: 1;

          &:has(~ .private) {
            flex-grow: 0;
          }
        }
        > .icon {
          flex-grow: 0;
        }
      }

      .icon {
        height: var(--dt-label-font-size, 14px);
        width: auto;
        display: inline-flex;
        align-items: center;

        &:empty,
        &:has(slot:not(.slotted):empty) {
          display: none;
        }
      }

      .icon dt-icon,
      .icon img {
        height: 100%;
        width: auto;
      }

      .icon.private {
        position: relative;
        flex-grow: 1;
      }
      .icon.private:hover .tooltip {
        display: block;
      }
      .tooltip {
        display: none;
        position: absolute;
        color: var(--dt-label-tooltip-color, var(--gray-0));
        background: var(--dt-label-tooltip-background, var(--surface-2));
        top: calc(100% + 0.5rem);
        inset-inline-start: -1rem;
        font-weight: normal;
        padding: 0.4rem;
        z-index: 1;
      }
      .tooltip:before {
        content: '';
        position: absolute;
        inset-inline-start: 1rem;
        top: -0.5rem;
        height: 0;
        width: 0;
        border-bottom: 0.5rem solid
          var(--dt-label-tooltip-background, var(--surface-2));
        border-inline-start: 0.5rem solid transparent;
        border-inline-end: 0.5rem solid transparent;
      }
    `}static get properties(){return{icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String}}}_mdiToIconify(e){return/^mdi\s+mdi-/.test(e)?e.replace(/^mdi\s+mdi-/,"mdi:"):/^mdi-/.test(e)?e.replace(/^mdi-/,"mdi:"):e}_renderIconContent(){if(!this.icon||!this.icon.trim())return null;const e=this.icon.trim();return!(e.startsWith("http://")||e.startsWith("https://")||e.startsWith("/")||e.startsWith("data:"))&&e.toLowerCase().includes("mdi")?h`<dt-icon
        icon="${this._mdiToIconify(e)}"
        size="1em"
      ></dt-icon>`:h`<img src="${e}" alt="${this.iconAltText||""}" />`}firstUpdated(){const e=this.shadowRoot.querySelectorAll("slot");if(e&&e.length)for(const r of e)r.addEventListener("slotchange",n=>{const l=n.target.assignedNodes();let d=!1;l.length&&(l[0].tagName==="SLOT"?d=l[0].assignedNodes().length||l[0].children.length:d=!0),d&&n.target.classList.add("slotted")});const i=this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({flatten:!0});for(const r of i)r.style.height="100%",r.style.width="auto";const o=this.shadowRoot.querySelector("slot:not([name])"),a=this.shadowRoot.querySelector(".label");if(o&&a){const r=o.assignedNodes().map(n=>{var l;return(l=n.textContent)==null?void 0:l.trim()}).filter(n=>n).join(" ");r&&a.setAttribute("title",r)}}get _slottedChildren(){return this.shadowRoot.querySelector("slot").assignedElements({flatten:!0})}render(){const e=h`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;return h`
      <div class="label" part="label">
        <span class="icon icon-start">
          <slot name="icon-start">${this._renderIconContent()}</slot>
        </span>
        <span class="label-text"><slot></slot></span>

        ${this.private?h`<span class="icon private">
              ${e}
              <span class="tooltip"
                >${this.privateLabel||R("Private Field: Only I can see its content")}</span
              >
            </span> `:null}

        <span class="icon icon-end">
          <slot name="icon-end"></slot>
        </span>
      </div>
    `}}window.customElements.define("dt-label",no);class Ia extends ae{static get styles(){return x`
      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }
      :host::before {
        content: '';
        animation: spin 1s linear infinite;
        border: var(--dt-spinner-thickness, 0.25rem) solid
          var(--dt-spinner-color-1, var(--gray-1));
        border-radius: 50%;
        border-top-color: var(--dt-spinner-color-2, var(--black));
        display: inline-block;
        height: var(--dt-spinner-size, 1rem);
        width: var(--dt-spinner-size, 1rem);
      }
    `}}window.customElements.define("dt-spinner",Ia);class La extends ae{static get styles(){return x`
      :host {
        margin-top: -0.25rem;
        width: 2rem;
      }
      :host::before {
        content: '';
        transform: rotate(45deg);
        height: 1rem;
        width: 0.5rem;
        color: inherit;
        border-bottom: var(--dt-checkmark-width, 3px) solid currentcolor;
        border-right: var(--dt-checkmark-width, 3px) solid currentcolor;
      }
    `}}window.customElements.define("dt-checkmark",La);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const le=s=>s??O;class M extends N{static get formAssociated(){return!0}static get styles(){return[...super.styles,x`
        .input-group {
          position: relative;
        }
        .input-group.disabled {
          background-color: var(--disabled-color);
        }

        /* === Inline Icons === */
        .icon-overlay {
          position: absolute;
          inset-inline-end: 0.5rem;
          top: 0;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: flex-end;
          padding-block: 0.5rem;
          box-sizing: border-box;
          pointer-events: none;
        }

        .icon-overlay.alert {
          color: var(--alert-color);
          cursor: pointer;
        }
        .icon-overlay.success {
          color: var(--success-color);
          width: 1.4rem;
        }
      `,x`
        @keyframes fadeOut {
          0% {
            opacity: 1;
          }
          75% {
            opacity: 1;
          }
          100% {
            opacity: 0;
          }
        }
        .icon-overlay.fade-out {
          opacity: 0;
          animation: fadeOut 4s;
        }
      `,x`
        .error-container {
          display: flex;
          align-items: center;
          font-family: var(--font-family);
          font-size: 0.875rem;
          font-weight: 300;
          padding: 3px 0.5rem;
          gap: 0.5rem;

          border: solid 1px var(--color-alert);
          background-color: var(--color-alert-container);
          color: var(--color-on-alert-container);

          &.slotted .attr-msg {
            display: none;
          }
        }
        .invalid ~ .error-container {
          border-top-width: 0;
        }
      `]}static get properties(){return{...super.properties,name:{type:String},label:{type:String},icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String},disabled:{type:Boolean},required:{type:Boolean},requiredMessage:{type:String},touched:{type:Boolean,state:!0},invalid:{type:Boolean,state:!0},error:{type:String},loading:{type:Boolean},saved:{type:Boolean},errorSlotted:{type:Boolean,attribute:!1}}}get _field(){return this.shadowRoot.querySelector("input, textarea, select")}get _focusTarget(){return this._field}constructor(){super(),this.savedTimeout=null,this.touched=!1,this.invalid=!1,this.internals=this.attachInternals(),this.addEventListener("invalid",e=>{e&&e.preventDefault(),this.touched=!0,this._validateRequired()})}firstUpdated(...e){super.firstUpdated(...e);const t=this.shadowRoot.querySelector("slot[name=error]");t&&t.addEventListener("slotchange",o=>{const r=o.target.assignedNodes();let n=!1;r.length>0&&(r[0].tagName==="SLOT"?n=r[0].assignedNodes().length>0:n=!0),this.errorSlotted=n});const i=M._jsonToFormData(this.value,this.name);this.internals.setFormValue(i),this._validateRequired()}static _buildFormData(e,t,i){if(t&&typeof t=="object"&&!(t instanceof Date)&&!(t instanceof File))Object.keys(t).forEach(o=>{this._buildFormData(e,t[o],i?`${i}[${o}]`:o)});else{const o=t??"";e.append(i,o)}}static _jsonToFormData(e,t){const i=new FormData;return M._buildFormData(i,e,t),i}_setFormValue(e){const t=M._jsonToFormData(e,this.name);this.internals.setFormValue(t,e),this._validateRequired(),this.touched=!0}_validateRequired(){}labelTemplate(){return this.label?h`
      <dt-label
        ?private=${this.private}
        privateLabel="${le(this.privateLabel)}"
        iconAltText="${le(this.iconAltText)}"
        icon="${le(this.icon)}"
        exportparts="label: label-container"
      >
        ${this.icon?null:h`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    `:""}_errorClasses(){return{"error-container":!0,slotted:this.errorSlotted}}renderIcons(){return h`
      ${this.renderIconInvalid()} ${this.renderError()}
      ${this.renderIconLoading()} ${this.renderIconSaved()}
    `}renderIconInvalid(){return this.touched&&this.invalid?h`<div class="${A(this._errorClasses())}">
          <dt-icon
            icon="mdi:alert-circle"
            class="alert"
            size="1.4rem"
          ></dt-icon>
          <span class="error-text"> ${this.internals.validationMessage} </span>
        </div> `:null}renderIconLoading(){return this.loading?h`<dt-spinner class="icon-overlay"></dt-spinner>`:null}renderIconSaved(){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?h`<dt-checkmark
          class="icon-overlay success fade-out"
        ></dt-checkmark>`:null}renderError(){return this.error?h`<div class="${A(this._errorClasses())}">
          <dt-icon icon="mdi:alert-circle" class="alert" size="1rem"></dt-icon>
          <span class="error-text">
            <slot name="error"></slot>
            <span class="attr-msg">${this.error}</span>
          </span>
        </div>`:null}render(){return h`
      ${this.labelTemplate()}
      <slot></slot>
    `}reset(){var e;(e=this._field)!=null&&e.reset&&this._field.reset(),this.value="",this._setFormValue("")}}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{I:Pa}=ns,lo=()=>document.createComment(""),Oe=(s,e,t)=>{var a;const i=s._$AA.parentNode,o=e===void 0?s._$AB:e._$AA;if(t===void 0){const r=i.insertBefore(lo(),o),n=i.insertBefore(lo(),o);t=new Pa(r,n,s,s.options)}else{const r=t._$AB.nextSibling,n=t._$AM,l=n!==s;if(l){let d;(a=t._$AQ)==null||a.call(t,s),t._$AM=s,t._$AP!==void 0&&(d=s._$AU)!==n._$AU&&t._$AP(d)}if(r!==o||l){let d=t._$AA;for(;d!==r;){const u=d.nextSibling;i.insertBefore(d,o),d=u}}}return t},de=(s,e,t=s)=>(s._$AI(e,t),s),Ma={},ja=(s,e=Ma)=>s._$AH=e,Fa=s=>s._$AH,Mt=s=>{var i;(i=s._$AP)==null||i.call(s,!1,!0);let e=s._$AA;const t=s._$AB.nextSibling;for(;e!==t;){const o=e.nextSibling;e.remove(),e=o}};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const co=(s,e,t)=>{const i=new Map;for(let o=e;o<=t;o++)i.set(s[o],o);return i},H=ut(class extends ht{constructor(s){if(super(s),s.type!==ct.CHILD)throw Error("repeat() can only be used in text expressions")}dt(s,e,t){let i;t===void 0?t=e:e!==void 0&&(i=e);const o=[],a=[];let r=0;for(const n of s)o[r]=i?i(n,r):r,a[r]=t(n,r),r++;return{values:a,keys:o}}render(s,e,t){return this.dt(s,e,t).values}update(s,[e,t,i]){const o=Fa(s),{values:a,keys:r}=this.dt(e,t,i);if(!Array.isArray(o))return this.ut=r,a;const n=this.ut??(this.ut=[]),l=[];let d,u,p=0,g=o.length-1,y=0,w=a.length-1;for(;p<=g&&y<=w;)if(o[p]===null)p++;else if(o[g]===null)g--;else if(n[p]===r[y])l[y]=de(o[p],a[y]),p++,y++;else if(n[g]===r[w])l[w]=de(o[g],a[w]),g--,w--;else if(n[p]===r[w])l[w]=de(o[p],a[w]),Oe(s,l[w+1],o[p]),p++,w--;else if(n[g]===r[y])l[y]=de(o[g],a[y]),Oe(s,o[p],o[g]),g--,y++;else if(d===void 0&&(d=co(r,y,w),u=co(n,p,g)),d.has(n[p]))if(d.has(n[g])){const T=u.get(r[y]),D=T!==void 0?o[T]:null;if(D===null){const j=Oe(s,o[p]);de(j,a[y]),l[y]=j}else l[y]=de(D,a[y]),Oe(s,o[p],D),o[T]=null;y++}else Mt(o[g]),g--;else Mt(o[p]),p++;for(;y<=w;){const T=Oe(s,l[w+1]);de(T,a[y]),l[y++]=T}for(;p<=g;){const T=o[p++];T!==null&&Mt(T)}return this.ut=r,ja(s,l),V}}),za=s=>class extends s{constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},query:{type:String,state:!0},options:{type:Array},filteredOptions:{type:Array,state:!0},open:{type:Boolean,state:!0},canUpdate:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},showAbove:{type:Boolean,state:!0}}}willUpdate(e){if(super.willUpdate(e),e&&!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}updated(e){super.updated&&super.updated(e),this._scrollOptionListToActive();const t=this.shadowRoot.querySelector(".input-group");t&&!t.style.getPropertyValue("--container-width")&&t.clientWidth>0&&t.style.setProperty("--container-width",`${t.clientWidth}px`),e&&e.has("open")&&this.open&&this._checkPosition()}_checkPosition(){const e=this.shadowRoot.querySelector(".input-group");if(e){const t=e.getBoundingClientRect(),o=window.innerHeight-t.bottom,a=t.top;o<150&&a>o?this.showAbove=!0:this.showAbove=!1}}get optionListStyles(){const e={display:this.open?"block":"none"};return this.showAbove?(e.bottom=this.containerHeight?`${this.containerHeight}px`:"2.5rem",e.top="auto"):(e.top=this.containerHeight?`${this.containerHeight}px`:"2.5rem",e.bottom="auto"),e}_select(){console.error("Must implement `_select(value)` function"),this._clearSearch()}static _focusInput(e){e.target===e.currentTarget&&e.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!0,this.activeIndex=-1)}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1,this.canUpdate=!0)}_inputKeyDown(e){}_inputKeyUp(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0,this.query=e.target.value;break}}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,o=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;o>r?e.scrollTo({top:o-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select(this.query):this._select(this.filteredOptions[this.activeIndex].id))}_clickOption(e){e.target&&e.target.value&&this._select(e.target.value)}_clickAddNew(e){var t;e.target&&this._select((t=e.target.dataset)==null?void 0:t.label)}_clearSearch(){const e=this.shadowRoot.querySelector("input");e&&(e.value="")}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1):this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_renderOption(e,t){return h`
        <li tabindex="-1">
          <button
            value="${e.id}"
            type="button"
            data-label="${e.label}"
            @click="${this._clickOption}"
            @touchstart="${this._touchStart}"
            @touchmove="${this._touchMove}"
            @touchend="${this._touchEnd}"
            tabindex="-1"
            class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
          >
            ${e.label}
          </button>
        </li>
      `}_baseRenderOptions(){return this.filteredOptions.length?H(this.filteredOptions,e=>e.id,(e,t)=>this._renderOption(e,t)):this.loading?h`<li><div>${R("Loading options...")}</div></li>`:h`<li><div>${R("No Data Available")}</div></li>`}_renderOptions(){let e=this._baseRenderOptions();return this.allowAdd&&this.query&&(Array.isArray(e)||(e=[e]),e.push(h`<li tabindex="-1">
            <button
              data-label="${this.query}"
              @click="${this._clickAddNew}"
              @touchstart="${this._touchStart}"
              @touchmove="${this._touchMove}"
              @touchend="${this._touchEnd}"
              class="${this.activeIndex>-1&&this.activeIndex>=this.filteredOptions.length?"active":""}"
            >
              ${R("Add")} "${this.query}"
            </button>
          </li>`)),e}};class Ze extends za(M){static get styles(){return[...super.styles,x`
        :host {
          position: relative;
          font-family: var(--font-family, Helvetica, Arial, sans-serif);
        }

        .input-group {
          cursor: text; /* Indicates the area is clickable */
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
        }
        .input-group.disabled input,
        .input-group.disabled .field-container {
          background-color: var(
            --dt-multi-select-disabled-background-color,
            var(--dt-form-disabled-background-color, var(--disabled-color))
          );
        }
        .input-group.disabled a,
        .input-group.disabled button {
          cursor: not-allowed;
          pointer-events: none;
        }
        .input-group.disabled *:hover {
          cursor: not-allowed;
        }

        .field-container {
          background-color: var(
            --dt-multi-select-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(
              --dt-multi-select-border-color,
              var(--dt-form-border-color, #cacaca)
            );
          border-radius: var(
            --dt-multi-select-border-radius,
            var(--dt-form-border-radius, 0)
          );
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          font-size: 1rem;
          font-weight: 300;
          min-height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding-top: 0.25rem;
          padding-bottom: 0.25rem;
          padding-inline: 0.5rem;
          box-sizing: border-box;
          width: 100%;
          text-transform: none;
          display: flex;
          column-gap: 4px;
          row-gap: 0.2rem;
          flex-wrap: wrap;
          min-width: 0;
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
        }

        .field-container input,
        .field-container .selected-option {
          //height: 1.5rem;
        }

        .selected-option {
          cursor: default;
          border: 1px solid
            var(
              --dt-multi-select-tag-border-color,
              var(--primary-color-light-1, #c2e0ff)
            );
          background-color: var(
            --dt-multi-select-tag-background-color,
            var(--primary-color-light-0, #ecf5fc)
          );

          display: flex;
          font-size: 0.875rem;
          position: relative;
          border-radius: 2px;
          box-sizing: border-box;
          min-width: 0;
        }
        .selected-option > *:first-child {
          padding-inline-start: 4px;
          padding-block: 0.25rem;
          line-height: normal;
          text-overflow: ellipsis;
          overflow: hidden;
          white-space: nowrap;
          --container-padding: calc(0.5rem + 0.5rem + 2px);
          --option-padding: 8px;
          --option-button: 20px;
          max-width: calc(
            var(--container-width) - var(--container-padding) - var(
                --option-padding
              ) - var(--option-button)
          );
        }
        .selected-option * {
          align-self: center;
        }
        .selected-option button {
          background: transparent;
          outline: 0;
          border: 0;
          border-inline-start: 1px solid
            var(
              --dt-multi-select-tag-border-color,
              var(--primary-color-light-1, #c2e0ff)
            );
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          margin-inline-start: 4px;
        }
        .selected-option button:hover {
          cursor: pointer;
        }

        .field-container input {
          background-color: transparent;
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          flex-grow: 1;
          min-width: 50px;
          flex-basis: 50px;
          border: 0;
          margin-block-start: 0.375rem;
          font-family: inherit;
          font-size: inherit;
        }
        .field-container input:focus,
        .field-container input:focus-visible,
        .field-container input:active {
          border: 0;
          outline: 0;
        }
        .field-container input::placeholder {
          color: var(
            --dt-multi-select-placeholder-color,
            var(--dt-form-placeholder-color, #999)
          );
          opacity: 1;
        }

        /* === Options List === */
        .option-list {
          list-style: none;
          margin: 0;
          padding: 0;
          border: 1px solid
            var(
              --dt-multi-select-border-color,
              var(--dt-form-border-color, #cacaca)
            );
          background: var(
            --dt-multi-select-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          z-index: 10;
          position: absolute;
          width: 100%;
          top: 0;
          left: 0;
          box-shadow: var(--shadow-1);
          max-height: 150px;
          overflow-y: scroll;
        }
        .option-list li {
          border-block-start: 1px solid
            var(
              --dt-multi-select-border-color,
              var(--dt-form-border-color, #cacaca)
            );
          outline: 0;
        }
        .option-list li div,
        .option-list li button {
          padding: 0.5rem 0.75rem;
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          font-weight: 100;
          font-size: 1rem;
          text-decoration: none;
          text-align: inherit;
        }
        .option-list li button {
          display: block;
          width: 100%;
          border: 0;
          background: transparent;
        }
        .option-list li button:hover,
        .option-list li button.active {
          cursor: pointer;
          background: var(
            --dt-multi-select-option-hover-background,
            var(--dt-form-option-hover-background, var(--surface-2))
          );
        }

        .field-container.invalid {
          border-color: var(
            --dt-multi-select-border-color-alert,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},containerHeight:{type:Number,state:!0}}}_select(e){const t=this.value,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:t}});if(this.value&&this.value.length)if(typeof this.value[0]=="string")this.value=[...this.value.filter(o=>o!==`-${e}`),e];else{let o=!1;const a=this.value.map(r=>{const n={...r};return r.id===e.id&&r.delete&&(delete n.delete,o=!0),n});o||a.push(e),this.value=a}else this.value=[e];i.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(i),this._setFormValue(this.value),this.query&&(this.query=""),this._clearSearch()}_remove(e){if(e.stopPropagation(),e.target&&e.target.dataset&&e.target.dataset.value){const t=this.value,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:t}});this.value=(this.value||[]).map(o=>o===e.target.dataset.value?`-${o}`:o),i.detail.newValue=this.value,this.dispatchEvent(i),this._setFormValue(this.value),this.open&&this.shadowRoot.querySelector("input").focus()}document.activeElement.blur()}updated(e){super.updated(e),this._updateContainerHeight()}_updateContainerHeight(){const e=this.shadowRoot.querySelector(".field-container");if(e){const t=e.offsetHeight;this.containerHeight!==t&&(this.containerHeight=t,this.requestUpdate())}}_filterOptions(){return this.filteredOptions=(this.options||[]).filter(e=>!(this.value||[]).includes(e.id)&&(!this.query||e.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),i=e.has("query"),o=e.has("options");(t||i||o)&&this._filterOptions()}}_handleDivClick(){const e=this.renderRoot.querySelector("input");e&&e.focus()}_handleItemClick(e){e.stopPropagation(),document.activeElement.blur()}_renderSelectedOptions(){return this.options&&this.value&&this.value.filter(e=>e.charAt(0)!=="-").map(e=>h`
            <div
              class="selected-option"
              @click="${this._handleItemClick}"
              @keydown="${this._handleItemClick}"
              part="tag"
            >
              <span
                >${this.options.find(t=>t.id===e).label}</span
              >
              <button
                @click="${this._remove}"
                ?disabled="${this.disabled}"
                data-value="${e}"
                part="remove-button"
              >
                x
              </button>
            </div>
          `)}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"field-container":!0,invalid:this.touched&&this.invalid}}render(){const e=this.optionListStyles;return h`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""}"
        @click="${this._handleDivClick}"
        @keydown="${this._handleDivClick}"
        part="input-group"
      >
        <div
          class="${A(this.classes)}"
          @click="${this._focusInput}"
          @keydown="${this._focusInput}"
          part="field-container"
        >
          ${this._renderSelectedOptions()}
          <input
            type="text"
            placeholder="${this.placeholder||""}"
            aria-label="${this.label||""}"
            autocomplete="off"
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
            ?required=${this.required}
            part="input"
          />
        </div>
        <ul
          class="option-list"
          style=${X(e)}
          part="option-list"
        >
          ${this._renderOptions()}
        </ul>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-select",Ze);class Da extends N{static get styles(){return x`
      root {
        display: block;
      }
      .health-item {
        padding: 0.5rem;

        img,
        dt-icon {
          width: 100%;
          height: 100%;
        }

        img {
          /* remove color from images and then semi-transparent */
          filter: grayscale(1) opacity(0.75);
        }
        dt-icon {
          /* lesser opacity than images because they start as solid black */
          filter: opacity(0.2);
        }

        &.health-item--active {
          img,
          dt-icon {
            filter: none !important;
          }
        }
      }
    `}static get properties(){return{key:{type:String},metric:{type:Object},active:{type:Boolean,reflect:!0},disabled:{type:Boolean},missingIcon:{type:String}}}renderIcon(){var o;const e=(o=window==null?void 0:window.wpApiShare)==null?void 0:o.template_dir,{metric:t,missingIcon:i=`${e}/dt-assets/images/groups/missing.svg`}=this;if(t["font-icon"]){const a=t["font-icon"].replace("mdi mdi-","mdi:");return h`<dt-icon icon="${a}" size="unset"></dt-icon>`}return h`<img
      src="${t.icon?t.icon:i}"
      alt="${t}"
    />`}render(){const{metric:e,active:t,disabled:i}=this;return h`<div
      class=${A({"health-item":!0,"health-item--active":t,"health-item--disabled":i})}
      title="${e.description}"
      @click="${this._handleClick}"
    >
      ${this.renderIcon()}
    </div>`}async _handleClick(e){if(this.disabled)return;const t=!this.active;this.active=t;const i=new CustomEvent("change",{detail:{key:this.key,active:t}});this.dispatchEvent(i)}}window.customElements.define("dt-church-health-icon",Da);class uo extends M{static get styles(){return[...super.styles,x`
        .root {
          display: block;
        }

        .toggle-wrapper {
          display: flex; /* Aligns children (label and icons) horizontally */
          align-items: center; /* Vertically aligns children */
          gap: 1ch; /* Adds a small gap between the label and the icons */
        }

        .toggle {
          position: relative;
          flex-wrap: wrap;
          display: flex;
          align-items: center;
          width: fit-content;
          cursor: pointer;
          min-height: var(
            --dt-toggle-input-height,
            var(--dt-form-input-height, 2.5rem)
          );
        }

        .icon-overlay {
          inset-inline-end: 0.5rem;
          align-items: center;
        }

        button.toggle {
          border: 0;
          background-color: transparent;
          font: inherit;
        }

        .toggle-input {
          position: absolute;
          opacity: 0;
          width: fit-content;
          height: 100%;
        }

        .toggle-display {
          --offset: 2px;
          --diameter: 1em;

          display: inline-flex;
          align-items: center;
          justify-content: space-around;
          box-sizing: content-box;
          width: calc(var(--diameter) * 2 + var(--offset) * 2);
          height: calc(var(--diameter) + var(--offset) * 2);
          border: 0.1em solid var(--dt-toggle-border-color, rgb(0 0 0 / 0.2));
          position: relative;
          border-radius: 100vw;
          background-color: var(
            --dt-toggle-background-color-off,
            var(--dt-form-background-color-off, #e6e6e6)
          );
          transition: 250ms;
        }

        .toggle-display::before {
          content: '';
          z-index: 2;
          position: absolute;
          top: 50%;
          left: var(--offset);
          box-sizing: border-box;
          width: var(--diameter);
          height: var(--diameter);
          border-radius: 50%;
          background-color: var(--dt-toggle-handle-color, white);
          transform: translate(0, -50%);
          will-change: transform;
          transition: inherit;
        }

        .toggle:focus .toggle-display,
        .toggle-input:focus + .toggle-display {
          outline: 1px dotted #212121;
          outline: 1px auto -webkit-focus-ring-color;
          outline-offset: 2px;
        }

        .toggle:focus,
        .toggle:focus:not(:focus-visible) .toggle-display,
        .toggle-input:focus:not(:focus-visible) + .toggle-display {
          outline: 0;
        }

        .toggle[aria-pressed='true'] .toggle-display,
        .toggle-input:checked + .toggle-display {
          background-color: var(
            --dt-toggle-background-color-on,
            var(--dt-form-primary-color, var(--primary-color))
          );
        }

        .toggle[aria-pressed='true'] .toggle-display::before,
        .toggle-input:checked + .toggle-display::before {
          transform: translate(100%, -50%);
        }

        .toggle[disabled] .toggle-display,
        .toggle-input:disabled + .toggle-display {
          opacity: var(--dt-toggle-disabled-opacity, 0.6);
          filter: grayscale(40%);
          cursor: not-allowed;
        }
        [dir='rtl'] .toggle-display::before {
          left: auto;
          right: var(--offset);
        }

        [dir='rtl'] .toggle[aria-pressed='true'] + .toggle-display::before,
        [dir='rtl'] .toggle-input:checked + .toggle-display::before {
          transform: translate(-100%, -50%);
        }

        .toggle-icon {
          display: inline-block;
          width: 1em;
          height: 1em;
          color: inherit;
          fill: currentcolor;
          vertical-align: middle;
          overflow: hidden;
        }

        .toggle-icon--cross {
          color: var(--dt-toggle-icon-color-off, var(--alert-color));
          font-size: 0.55em;
        }

        .toggle-icon--checkmark {
          font-size: 0.65em;
          color: var(--dt-toggle-icon-color-on, var(--success-color));
        }
      `]}static get properties(){return{...super.properties,id:{type:String},checked:{type:Boolean,reflect:!0},icons:{type:Boolean,default:!1}}}constructor(){super(),this.icons=!1}firstUpdated(){super.firstUpdated(),this.checked===void 0&&(this.checked=!1);const e=this.checked?"1":"0";this._setFormValue(e),this.value=this.checked}onChange(e){const t=e.target.checked,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.checked,newValue:t}});this.checked=t,this.value=t,this._setFormValue(this.checked?"1":"0"),this.dispatchEvent(i)}onClickToggle(e){e.preventDefault(),e.target.closest("label").querySelector("input").click()}render(){const e=h`<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="toggle-icon toggle-icon--checkmark"><path d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z" fill="currentcolor" stroke="currentcolor" /></svg>`,t=h`<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="toggle-icon toggle-icon--cross"><path d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z" fill="currentcolor" /></svg>`;return h`
      <div class="root" part="root">
        ${this.labelTemplate()}

        <div class="input-group">
          <label
            class="toggle"
            for="${this.id}"
            aria-label="${this.label}"
            part="toggle"
          >
            <input
              type="checkbox"
              name="${this.name}"
              id="${this.id}"
              class="toggle-input"
              .checked=${this.checked||0}
              @click=${this.onChange}
              ?disabled=${this.disabled}
            />
            <span class="toggle-display" @click=${this.onClickToggle}>
              ${this.icons?h` ${e} ${t} `:h``}
            </span>
          </label>
          ${this.renderIcons()}
        </div>
      </div>
    `}}window.customElements.define("dt-toggle",uo);class ho extends Ze{static get styles(){return[...super.styles,x`
        .health-circle__container {
          --icon-count: 9;
          /* Updated circle size based on dynamic width */
          --circle-size: var(--container-width, 250px);
          /* Dynamically calculate icon size based on circle size. Max: 125px */
          --icon-size: min(calc(var(--circle-size) / 5), 125px);
          --circle-padding: max(
            0.5rem,
            calc(var(--circle-size) / 250px * 0.5rem)
          );
          --radius: calc(
            0.5 * var(--circle-size) - 0.5 *
              var(--icon-size) - var(--circle-padding)
          ); /* radius from center to icon center, accounting for inner padding */

          margin: 1rem auto;
          display: flex;
          justify-content: center;
          align-items: center;
          width: var(--circle-size);
          height: var(--circle-size);
          position: relative;
          overflow: visible;
        }

        .health-circle {
          display: block;
          border-radius: 100%;
          border: 3px darkgray dashed;
          position: absolute;
          box-sizing: border-box;
          width: 100%;
          height: 100%;
          left: 0;
          top: 0;
        }

        .health-circle__grid {
          display: inline-block;
          position: relative;
          height: 100%;
          width: 100%;
          margin-left: auto;
          margin-right: auto;
          position: relative;
        }

        .health-circle--committed {
          border: 3px #4caf50 solid !important;
        }
        .health-circle--disabled dt-church-health-icon {
          cursor: not-allowed;
        }

        dt-church-health-icon {
          position: absolute;
          border-radius: 100%;
          font-size: 16px;
          color: black;
          text-align: center;
          font-style: italic;
          cursor: pointer;
          top: 50%;
          left: 50%;
          margin: calc(-0.5 * var(--icon-size));
          width: var(--icon-size);
          height: var(--icon-size);
          --az: calc(var(--i) * 1turn / var(--icon-count));
          transform: rotate(var(--az)) translate(var(--radius))
            rotate(calc(-1 * var(--az)));
        }
      `,x`
        dt-toggle::part(root) {
          display: flex;
          align-items: center;
        }
        dt-toggle::part(toggle) {
          padding-top: 0;
        }
        dt-toggle::part(label-container) {
          font-weight: 300;
        }
      `,x`
        .icon-overlay {
          inset-inline-end: 0;
        }
        .error-container {
          margin-block-start: 0.5rem;
        }
      `]}static get properties(){const e={...super.properties,missingIcon:{type:String}};return delete e.placeholder,delete e.containerHeight,e}_filterOptions(){const e=this.options||[];if(!Object.values(e).length)return[];const t=Object.entries(e);return this.filteredOptions=t.filter(([i,o])=>i!=="church_commitment"),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),i=e.has("options");(t||i)&&this._filterOptions()}}get isCommited(){return this.value?this.value.includes("church_commitment"):!1}render(){var e,t;return h`
      <div class="health-circle__wrapper input-group">
        <div
          class="health-circle__container"
          style="--icon-count: ${this.filteredOptions.length}"
        >
          <div
            class=${A({"health-circle":!0,"health-circle--committed":this.isCommited,"health-circle--disabled":this.disabled})}
          >
            <div class="health-circle__grid">
              ${this.filteredOptions.map(([i,o],a)=>h`<dt-church-health-icon
                    key="${i}"
                    .metric=${o}
                    .active=${(this.value||[]).indexOf(i)!==-1}
                    .style="--i: ${a+1}"
                    .missingIcon="${this.missingIcon}"
                    ?disabled=${this.disabled}
                    data-value="${i}"
                    @change="${this.handleIconClick}"
                  >
                  </dt-church-health-icon>`)}
            </div>
            ${this.renderIconLoading()} ${this.renderIconSaved()}
          </div>
        </div>

        <dt-toggle
          name="church_commitment"
          label="${(t=(e=this.options)==null?void 0:e.church_commitment)==null?void 0:t.label}"
          @change="${this.handleToggleChange}"
          ?disabled=${this.disabled}
          ?checked=${this.isCommited}
          data-value="church_commitment"
        >
        </dt-toggle>
        ${this.renderError()}
      </div>
    `}handleIconClick(e){const{key:t,active:i}=e.detail;i?this._select(t):this._remove(e)}async handleToggleChange(e){const{field:t,newValue:i}=e.detail;i?this._select(t):this._remove(e)}}window.customElements.define("dt-church-health-circle",ho);class Ce extends Ze{static get properties(){return{...super.properties,postType:{type:String,reflect:!1},allowAdd:{type:Boolean}}}static get styles(){return[...super.styles,x`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(
            --dt-tags-selected-link-color,
            var(--primary-color, #3f729b)
          );
        }
        .selected-option a[href='#'],
        .selected-option a[href=''] {
          color: var(
            --dt-tags-selected-text-color,
            var(
              --dt-multi-select-text-color,
              var(--dt-form-text-color, #0a0a0a)
            )
          );
          pointer-events: none;
        }
        .invalid {
          border-color: var(
            --dt-tags-invalid-border-color,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }

        .input-group {
          display: flex;
          flex-wrap: wrap;

          .error-container {
            flex-basis: 100%;
          }
        }

        .field-container {
          flex: 1;
        }

        .input-addon.btn-add {
          background-color: var(
            --dt-tags-add-button-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(
              --dt-tags-add-button-border-color,
              var(--dt-form-border-color, #fefefe)
            );
          width: 37.5px;
          &:disabled {
            color: var(
              --dt-tags-add-button-disabled-color,
              var(--dt-form-placeholder-color, #999)
            );
          }
          &:hover:not([disabled]) {
            background-color: var(
              --dt-tags-add-button-hover-background-color,
              var(--success-color, #4caf50)
            );
            color: var(
              --dt-tags-add-button-hover-color,
              var(--dt-form-text-color-light, #ffffff)
            );
          }
        }
        .input-group.allowAdd .icon-overlay {
          inset-inline-end: 3rem;
        }
      `]}_addRecord(){const e=new CustomEvent("dt:add-new",{detail:{field:this.name,value:this.query}});this.dispatchEvent(e)}willUpdate(e){super.willUpdate(e),e&&e.has("open")&&this.open&&(!this.filteredOptions||!this.filteredOptions.length)&&this._filterOptions()}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.startsWith("-"));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1;let r=a;r.length&&typeof r[0]=="string"&&(r=r.map(n=>({id:n}))),i.allOptions=r,i.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_renderOption(e,t){return h`
      <li tabindex="-1">
        <button
          value="${e.id}"
          type="button"
          data-label="${e.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          ${e.label||e.id}
        </button>
      </li>
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||typeof t=="string"&&t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){const e=this.options||this.allOptions;return(this.value||[]).filter(t=>!t.startsWith("-")).map(t=>{var a;let i=t;if(e){const r=e.filter(n=>n===t||n.id===t);r.length&&(i=r[0].label||r[0].id||t)}let o;if(!o&&((a=window==null?void 0:window.SHAREDFUNCTIONS)!=null&&a.createCustomFilter)){const r=window.SHAREDFUNCTIONS.createCustomFilter(this.name,[t]),n=this.label||this.name,l=[{id:`${this.name}_${t}`,name:`${n}: ${t}`}];o=window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType,r,l)}return h`
          <div
            class="selected-option"
            @click="${this._handleItemClick}"
            @keydown="${this._handleItemClick}"
          >
            <a href="${o}" ?disabled="${this.disabled}" alt="${t}"
              >${i}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t}"
            >
              x
            </button>
          </div>
        `})}render(){const e=this.optionListStyles;return h`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}"
        @click="${this._handleDivClick}"
        @keydown="${this._handleDivClick}"
      >
        <div
          class="${A(this.classes)}"
          @click="${this._focusInput}"
          @keydown="${this._focusInput}"
        >
          ${this._renderSelectedOptions()}
          <input
            type="text"
            placeholder="${this.placeholder}"
            autocomplete="off"
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
            ?required=${this.required}
          />
        </div>
        ${this.allowAdd?h`<button class="input-addon btn-add" @click=${this._addRecord}>
              <dt-icon icon="mdi:tag-plus-outline"></dt-icon>
            </button>`:null}
        <ul class="option-list" style=${X(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-tags",Ce);class po extends Ce{static get styles(){return[...super.styles,x`
        .selected-option a {
          border-inline-start: solid 3px transparent;
        }

        li button * {
          pointer-events: none;
        }

        li {
          border-inline-start: solid 5px transparent;
        }

        li button .status {
          font-style: italic;
          opacity: 0.6;
        }
        li button .status:before {
          content: '[';
          font-style: normal;
        }
        li button .status:after {
          content: ']';
          font-style: normal;
        }

        li button svg {
          width: 1.5em;
          height: auto;
          margin-bottom: -0.25em;
        }
        li button svg use {
          fill: var(--dt-connection-icon-fill, var(--primary-color));
        }
        .invalid {
          border-color: var(--dt-form-border-color-alert, var(--alert-color));
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((o,a)=>!o&&a.id==t?a:o,null);i&&this._select(i)}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.stopPropagation(),e.target&&e.target.dataset&&e.target.dataset.value){let t=e.target.dataset.value;const i=Number.parseInt(t);Number.isNaN(i)||(t=i);const o=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(a=>{const r={...a};return a.id===t&&(r.delete=!0),r}),o.detail.newValue=this.value,this.dispatchEvent(o),this.open&&this.shadowRoot.querySelector("input").focus(),this._validateRequired()}document.activeElement.blur()}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.delete))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>h`
          <div
            class="selected-option"
            @click="${this._handleItemClick}"
            @keydown="${this._handleItemClick}"
          >
            <a
              href="${e.link}"
              style="border-inline-start-color: ${e.status?e.status.color:""}"
              ?disabled="${this.disabled}"
              ?required=${this.required}
              title="${e.status?e.status.label:e.label}"
              >${e.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${e.id}"
            >
              x
            </button>
          </div>
        `)}_renderOption(e,t){const i=h`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`,o=e.status||{label:"",color:""};return h`
      <li tabindex="-1" style="border-inline-start-color:${o.color}">
        <button
          value="${e.id}"
          type="button"
          data-label="${e.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          <span class="label">${e.label}</span>
          <span class="connection-id">(#${e.id})</span>
          ${o.label?h`<span class="status">${o.label}</span>`:null}
          ${e.user?i:null}
        </button>
      </li>
    `}render(){const e=this.optionListStyles;return h`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}"
        @click="${this._handleDivClick}"
        @keydown="${this._handleDivClick}"
      >
        <div
          class="${A(this.classes)}"
          @click="${this._focusInput}"
          @keydown="${this._focusInput}"
        >
          ${this._renderSelectedOptions()}
          <input
            type="text"
            placeholder="${this.placeholder}"
            autocomplete="off"
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
            ?required=${this.required}
          />
        </div>
        ${this.allowAdd?h`<button class="input-addon btn-add" @click=${this._addRecord}>
              <dt-icon icon="mdi:account-plus-outline"></dt-icon>
            </button>`:null}
        <ul class="option-list" style=${X(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-connection",po);class fo extends Ce{static get styles(){return[...super.styles,x`
        .selected-option a {
          border-inline-start: solid 3px transparent;
        }

        li button * {
          pointer-events: none;
        }

        li {
          border-inline-start: solid 5px transparent;
        }

        li button .status {
          font-style: italic;
          opacity: 0.6;
        }
        li button .status:before {
          content: '[';
          font-style: normal;
        }
        li button .status:after {
          content: ']';
          font-style: normal;
        }

        li button .avatar {
          margin-inline-end: 1ch;
        }

        li button svg {
          width: 20px;
          height: auto;
          margin-bottom: -4px;
        }
        li button svg use {
          fill: var(--dt-users-connection-icon-fill, var(--primary-color));
        }
      `]}static get properties(){return{...super.properties,single:{type:Boolean}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length){let i=!1,o=this.value.map(a=>{const r={...a};return a.id===e.id&&a.delete?(delete r.delete,i=!0):this.single&&!a.delete&&(r.delete=!0),r});i||o.push(e),this.single&&(o=o.filter(a=>!a.delete)),this.value=o}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._clearSearch()}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((o,a)=>!o&&a.id==t?a:o,null);i&&this._select(i),this.query=""}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="",this.query="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this.query="")}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,remove:!0}});this.value=(this.value||[]).map(i=>{const o={...i};return i.id.toString()===e.target.dataset.value&&(o.delete=!0),o}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>Number(i==null?void 0:i.id));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>h`
          <div class="selected-option">
            <a
              href="${e.link}"
              style="border-inline-start-color: ${e.status?e.status:""}"
              ?disabled="${this.disabled}"
              title="${e.label}"
              >${e.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${e.id}"
            >
              x
            </button>
          </div>
        `)}_renderOption(e,t){const i=e.avatar?h`<span class="avatar"
          ><img src="${e.avatar}" alt="${e.label}"
        /></span>`:h`<span class="avatar"></span>`;return h`
      <li tabindex="-1" style="border-inline-start-color:${e.status}">
        <button
          value="${e.id}"
          type="button"
          data-label="${e.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          ${i}
          <span class="connection-id">${e.label}</span>
        </button>
      </li>
    `}}window.customElements.define("dt-users-connection",fo);class bo extends N{static get styles(){return x`
      :root {
        font-size: inherit;
        --dt-copy-text-color: #575757;
      }

      .copy-text {
        --dt-form-text-color: var(--dt-copy-text-color, #575757);
        display: flex;
        align-items: center;
        position: relative;
        width: calc(100% + 1.5em);
      }

      .copy-text__input {
        flex: 1;
      }

      .copy_icon {
        cursor: copy;
        font-size: 1em;
        display: block;
        transform: translate(-1.5em, -0.3125em);
        width: 1.25em;
      }

      :host([dir='rtl']) .copy_icon {
        transform: translate(1.5em, -0.3125em);
      }
    `}static get properties(){return{value:{type:String},success:{type:Boolean},error:{type:Boolean}}}get inputStyles(){return this.success?{"--dt-text-border-color":"var(--copy-text-success-color, var(--success-color))","--dt-form-text-color":"var( --copy-text-success-color, var(--success-color))",color:"var( --copy-text-success-color, var(--success-color))"}:this.error?{"---dt-text-border-color":"var(--copy-text-alert-color, var(--alert-color))","--dt-form-text-color":"var(--copy-text-alert-color, var(--alert-color))"}:{}}get icon(){return this.success?"ic:round-check":"ic:round-content-copy"}async copy(){try{this.success=!1,this.error=!1,await navigator.clipboard.writeText(this.value),this.success=!0,this.error=!1}catch(e){console.log(e),this.success=!1,this.error=!0}}render(){return h`
      <div class="copy-text" style=${X(this.inputStyles)}>
        <dt-text
          class="copy-text__input"
          value="${this.value}"
          disabled
        ></dt-text>
        <dt-icon
          class="copy_icon"
          icon="${this.icon}"
          @click="${this.copy}"
        ></dt-icon>
      </div>
    `}}window.customElements.define("dt-copy-text",bo);class jt extends M{static get styles(){return[...super.styles,x`
        input {
          color: var(--dt-date-text-color, var(--dt-form-text-color, #000));
          appearance: none;
          background-color: var(
            --dt-date-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(--dt-date-border-color, var(--dt-form-border-color, #cacaca));
          border-radius: var(
            --dt-date-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-date-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly],
        .input-group button:disabled {
          background-color: var(
            --dt-date-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }
        input.disabled {
          color: var(--dt-text-placeholder-color, #999);
        }
        input:focus-within,
        input:focus-visible {
          outline: none;
        }
        input::placeholder {
          color: var(--dt-date-placeholder-color, #999);
          text-transform: var(--dt-date-placeholder-transform, none);
          font-size: var(--dt-date-placeholder-font-size, 1rem);
          font-weight: var(--dt-date-placeholder-font-weight, 400);
          letter-spacing: var(--dt-date-placeholder-letter-spacing, normal);
        }
        .invalid,
        .field-container.invalid .input-addon {
          border-color: var(--dt-date-border-color-alert, var(--alert-color));
        }
      `,x`
        .field-container {
          display: flex;
        }
        .field-container input {
          flex-grow: 1;
        }
        .field-container .input-addon {
          flex-shrink: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          aspect-ratio: 1/1;
          padding: 10px;
          border-collapse: collapse;
          background-color: var(
            --dt-date-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(--dt-date-border-color, var(--dt-form-border-color, #cacaca));
          border-radius: var(
            --dt-date-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-date-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
        }

        .input-addon:disabled {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-date-placeholder-color, #999);
        }
        .input-addon:disabled:hover {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-date-placeholder-color, #999);
          cursor: not-allowed;
        }

        .input-addon.btn-clear {
          height: 2.5rem;
          color: var(--alert-color, #cc4b37);
          &:disabled {
            color: var(--dt-date-placeholder-color, #999);
          }
          &:hover:not([disabled]) {
            background-color: var(--alert-color, #cc4b37);
            color: var(--dt-date-button-hover-color, #ffffff);
          }
        }

        .icon-overlay {
          inset-inline-end: 5rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:String,reflect:!0},timestamp:{converter:e=>{let t=Number(e);if(t<1e12&&(t*=1e3),t)return t},reflect:!0}}}updateTimestamp(e){const t=e?new Date(e).getTime():0,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e}});this.timestamp=t,this.value=e,this._setFormValue(e),this.dispatchEvent(i)}_change(e){this.updateTimestamp(e.target.value)}clearInput(){this.updateTimestamp("")}showDatePicker(){this.shadowRoot.querySelector("input").showPicker()}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}get fieldContainerClasses(){return{"field-container":!0,invalid:this.touched&&this.invalid}}render(){return this.timestamp?this.value=new Date(this.timestamp).toISOString().substring(0,10):this.value&&(this.timestamp=new Date(this.value).getTime()),h`
      ${this.labelTemplate()}

      <div class="input-group">
        <div class="${A(this.fieldContainerClasses)}">
          <input
            id="${this.id}"
            class="${A(this.classes)}"
            type="date"
            autocomplete="off"
            .placeholder="${new Date().toISOString().substring(0,10)}"
            .value="${this.value}"
            .timestamp="${this.date}"
            ?disabled=${this.disabled}
            ?required=${this.required}
            @change="${this._change}"
            novalidate
            @click="${this.showDatePicker}"
            part="input"
          />
          <button
            class="input-addon btn-clear"
            @click="${this.clearInput}"
            data-inputid="${this.id}"
            ?disabled=${this.disabled}
            part="clear-button"
            aria-label="Clear date"
          >
            <dt-icon icon="mdi:close"></dt-icon>
          </button>
        </div>

        ${this.renderIcons()}
      </div>
    `}reset(){this.updateTimestamp(""),super.reset()}}window.customElements.define("dt-date",jt);class mo extends jt{static get styles(){return[...super.styles,x`
        input[type='datetime-local'] {
          max-width: calc(100% - 22px - 1rem);
        }
      `]}static get properties(){return{...super.properties,tzoffset:{type:Number}}}constructor(){super(),this.tzoffset=new Date().getTimezoneOffset()*6e4}render(){return this.timestamp?this.value=new Date(this.timestamp-this.tzoffset).toISOString().substring(0,16):this.value&&(this.timestamp=new Date(this.value).getTime()),h`
      ${this.labelTemplate()}

      <div class="input-group">
        <div class="${A(this.fieldContainerClasses)}">
          <input
            id="${this.id}"
            class="${A(this.classes)}"
            type="datetime-local"
            autocomplete="off"
            .placeholder="${new Date().toISOString()}"
            .value="${this.value}"
            .timestamp="${this.date}"
            ?disabled=${this.disabled}
            ?required=${this.required}
            @change="${this._change}"
            novalidate
            @click="${this.showDatePicker}"
            part="input"
          />
          <button
            class="input-addon btn-clear"
            @click="${this.clearInput}"
            data-inputid="${this.id}"
            ?disabled=${this.disabled}
            part="clear-button"
            aria-label="Clear date and time"
          >
            <dt-icon icon="mdi:close"></dt-icon>
          </button>
        </div>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-datetime",mo);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*Ra(s,e){if(s!==void 0){let t=0;for(const i of s)yield e(i,t++)}}class go extends Ce{static get properties(){return{...super.properties,filters:{type:Array}}}static get styles(){return[...super.styles,x`
        .input-group {
          display: flex;
        }

        .field-container {
          position: relative;
        }

        select {
          border: 1px solid var(--dt-form-border-color, #cacaca);
          outline: 0;
        }
        .selected-option > *:first-child {
          max-width: calc(
            var(--container-width) - var(--select-width) - var(
                --container-padding
              ) - var(--option-padding) - var(--option-button) -
              8px
          );
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=e.target.value,i=this.filteredOptions.reduce((o,a)=>!o&&a.id===t?a:o,null);this._select(i)}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(i=>{const o={...i};return i.id.toString()===e.target.dataset.value&&(o.delete=!0),o}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}updated(e){super.updated(e);const t=this.shadowRoot.querySelector(".input-group"),i=t.style.getPropertyValue("--select-width"),o=this.shadowRoot.querySelector("select");!i&&(o==null?void 0:o.clientWidth)>0&&t.style.setProperty("--select-width",`${o.clientWidth}px`)}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id.toString());if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,o=this.shadowRoot.querySelector("select"),a=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,query:this.query,filter:o==null?void 0:o.value,onSuccess:r=>{i.loading=!1,i.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:r=>{console.warn(r),i.loading=!1}}});this.dispatchEvent(a)}return this.filteredOptions}_renderOption(e,t){return h`
      <li tabindex="-1">
        <button
          value="${e.id}"
          type="button"
          data-label="${e.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          ${e.label}
        </button>
      </li>
    `}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>h`
          <div class="selected-option">
            <a
              href="${e.link}"
              ?disabled="${this.disabled}"
              alt="${e.status?e.status.label:e.label}"
              >${e.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${e.id}"
            >
              x
            </button>
          </div>
        `)}render(){const e=this.optionListStyles;return h`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}">
        <div
          class="field-container"
          @click="${this._focusInput}"
          @keydown="${this._focusInput}"
        >
          ${this._renderSelectedOptions()}
          <input
            type="text"
            placeholder="${this.placeholder}"
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
          />

          ${this.renderIconLoading()} ${this.renderIconSaved()}
        </div>
        <select
          class="filter-list"
          ?disabled="${this.disabled}"
          @change="${this._filterOptions}"
        >
          ${Ra(this.filters,t=>h`<option value="${t.id}">${t.label}</option>`)}
        </select>
        <ul class="option-list" style=${X(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIconInvalid()} ${this.renderError()}
      </div>
    `}}window.customElements.define("dt-location",go);class Na{constructor(e){this.token=e}async searchPlaces(e,t="en"){const i=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:t}),o={method:"GET",headers:{"Content-Type":"application/json"}},a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)}.json?${i}`,n=await(await fetch(a,o)).json();return n==null?void 0:n.features}async reverseGeocode(e,t,i="en"){const o=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:i}),a={method:"GET",headers:{"Content-Type":"application/json"}},r=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)},${encodeURI(t)}.json?${o}`,l=await(await fetch(r,a)).json();return l==null?void 0:l.features}}class qa{constructor(e,t,i){var o,a,r;if(this.token=e,this.window=t,!((r=(a=(o=t.google)==null?void 0:o.maps)==null?void 0:a.places)!=null&&r.AutocompleteService)){const n=i.createElement("script");n.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${e}`,i.body.appendChild(n)}}async getPlacePredictions(e,t="en"){try{return await this._getPlacePredictionsLegacy(e,t)}catch(i){const o=await this._getPlaceSuggestionsRest(e,t);if(o)return o;throw{message:i}}}async _getPlacePredictionsLegacy(e,t="en"){return this.window.google?new Promise((i,o)=>{const a=new this.window.google.maps.places.AutocompleteService;window.gm_authFailure=function(){o("Google Maps API Key authentication failed")},a.getPlacePredictions({input:e,language:t},(r,n)=>{n!=="OK"?o(n):i(r)})}):null}async _getPlaceSuggestionsRest(e,t="en"){const i="https://places.googleapis.com/v1/places:autocomplete?key="+encodeURIComponent(this.token),a=await fetch(i,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({input:e})}),r=await a.json();if(!a.ok&&r.error)throw r.error;return(Array.isArray(r&&r.suggestions)?r.suggestions:[]).map(u=>u&&u.placePrediction?u.placePrediction:null).filter(Boolean).map(u=>{const p=u.placeId||(u.place?String(u.place).replace("places/",""):null),g=u.text&&u.text.text||[u.structuredFormat&&u.structuredFormat.mainText&&u.structuredFormat.mainText.text,u.structuredFormat&&u.structuredFormat.secondaryText&&u.structuredFormat.secondaryText.text].filter(Boolean).join(", ");return p&&g?{description:g,place_id:p}:null}).filter(Boolean)}async getPlaceDetails(e,t="en"){let i=null;if(this.window.google){const o=new window.google.maps.Geocoder;try{const{results:a}=await o.geocode({placeId:e.place_id,language:t}),r=a[0];i={lng:r.geometry.location.lng(),lat:r.geometry.location.lat(),level:this.convert_level(r.types[0]),label:e.description||r.formatted_address}}catch(a){i={error:a}}}return i}async reverseGeocode(e,t,i="en"){const a=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${t},${e}`,language:i,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,n=await(await fetch(a,{method:"GET"})).json();return n==null?void 0:n.results}convert_level(e){switch(e){case"administrative_area_level_0":e="admin0";break;case"administrative_area_level_1":e="admin1";break;case"administrative_area_level_2":e="admin2";break;case"administrative_area_level_3":e="admin3";break;case"administrative_area_level_4":e="admin4";break;case"administrative_area_level_5":e="admin5";break}return e}}class vo extends N{static get styles(){return x`
      :host {
        display: block;
        font-family: var(--font-family);
      }
      :host:has(dialog[open]) {
        overflow: hidden;
      }

      .dt-modal {
        display: block;
        background: var(--dt-modal-background-color, #fff);
        color: var(--dt-modal-color, #000);
        max-inline-size: min(90dvw, 100%);
        max-block-size: min(80dvh, 100%);
        max-block-size: min(80dvb, 100%);
        margin: auto;
        height: fit-content;
        scrollbar-color: var(--border-color) transparent;
        padding: var(--dt-modal-padding, 0em);
        position: fixed;
        inset: 0;
        border-radius: 1em;
        border: none;
        box-shadow: var(--shadow-6);
        z-index: 1000;
        transition: opacity 0.1s ease-in-out;
      }
      .dt-modal.dt-modal--width {
        width: 80dvw;
        background-color: #fefefe;
        border-radius: 10px;
      }
      #modal-field-title {
      font-size: 2rem;
      }

      .dt-modal.bottom {
        position: fixed;
        margin-bottom: 0;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        max-inline-size: fit-content;
     }

     dialog:not([open]).bottom {
        opacity: 0;
        transform: translateY(100%);
        transition: 0.5s ease;
      }

      dialog[open].bottom {
        opacity: 1;
        transform: translateY(0);
        transition: 0.5s ease;
      }

     @media (max-width: 600px) {
        /* CSS rules specific to small mobile devices */
        .dt-modal.bottom {
          width: 100%;
          margin-left: 0;
          margin-right: 0;
        }
      }

      dialog:not([open]) {
        pointer-events: none;
        opacity: 0;
      }

      dialog::backdrop {
        background: var(--dt-modal-backdrop-color, rgba(0, 0, 0, 0.25));
        animation: var(--dt-modal-animation, fade-in 0.75s);
      }

      @keyframes fade-in {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      h1,
      h2,
      h3,
      h4,
      h5,
      h6 {
        line-height: 1.4;
        text-rendering: optimizeLegibility;
        color: inherit;
        font-style: normal;
        font-weight: 300;
        margin: 0;
      }

      form {
        height: fit-content;
        position: relative;
      }

      header {
        display: flex;
        justify-content: space-between;
        padding: 1em;
      }

      .button {
        color: var(--dt-modal-button-color, #fff);
        background: var(--dt-modal-button-background, #3f729b);
        font-size: 1rem;
        border: 0.1em solid var(--dt-modal-button-background, #000);
        border-radius: 0.25em;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        text-decoration: none;
      }

      .button.opener {
        color: var(--dt-modal-button-opener-color,var(--dt-modal-button-color, #fff) );
        background: var(--dt-modal-button-opener-background, var(--dt-modal-button-background, #3f729b) );
        border: 0.1em solid var(--dt-modal-button-opener-background, #000);
      }
      button.toggle {
        margin-inline-end: 0;
        margin-inline-start: auto;
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
      }

      article {
        overflow: auto;
        padding: 0em 1em;
      }

      footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1rem;
        border-top: 1px solid #ccc;
      }

      footer.footer-button{
      justify-content: flex-start;

      }

      .help-more h5 {
        font-size: 0.75rem;
        display: block;
      }
      .help-more .button {
        font-size: 0.75rem;
        display: block;
      }
      .help-icon {
        -webkit-filter: invert(69%) sepia(1%) saturate(0) hue-rotate(239deg) brightness(94%) contrast(86%);
        filter: invert(69%) sepia(1%) saturate(0) hue-rotate(239deg) brightness(94%) contrast(86%);
        height: 1rem;
      }
      .dt-modal.header-blue-bg {
        header {
          background-color: #3f729b;
          color: #fff;
          text-align: center;
          #modal-field-title {
            font-size: 1.5rem;
            width: 100%;
          }
        }
        article {
          padding: 0em 1em;
        }
        footer {
          padding-inline: .7rem;
          justify-content: flex-end;
          .button {
            padding: 12px 14px;
          }
        }
      }
      .button img {
        height: 1em;
        width: 1em;
      }
      .footer-button {
        display: flex;
        gap: .5rem;
      }
      .footer-button .button {
        min-height: 2.25rem;
      }
      .footer-button .button.small {
        border-color: #3f729b;
      }
      .footer-button .button.small:hover {
        color: #ffffff !important;
        background-color: #38668c !important;
      }
      @media screen and (min-width: 40em) {
          .dt-modal.dt-modal--full-width{
            max-width: 80rem;
            width: 90%;
        }
    }

     ::slotted([slot="content"]) {
      /* Styles for the content inside the named slot */
      font-size: 15px;;
    }
    `}static get properties(){return{title:{type:String},context:{type:String},isHelp:{type:Boolean},isOpen:{type:Boolean},hideHeader:{type:Boolean},hideButton:{type:Boolean},buttonClass:{type:Object},buttonStyle:{type:Object},headerClass:{type:Object},imageSrc:{type:String},imageStyle:{type:Object},tileLabel:{type:String},buttonLabel:{type:String},dropdownListImg:{type:String},submitButton:{type:Boolean},closeButton:{type:Boolean},bottom:{type:Boolean}}}constructor(){super(),this.context="default",this.addEventListener("open",()=>this._openModal()),this.addEventListener("close",()=>this._closeModal())}_openModal(){this.isOpen=!0,this.shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}get formattedTitle(){if(!this.title)return"";const e=this.title.replace(/_/g," ");return e.charAt(0).toUpperCase()+e.slice(1)}_dialogHeader(e){return this.hideHeader?h``:h`
      <header>
            <h1 id="modal-field-title" class="modal-header">${this.formattedTitle}</h1>
            <button @click="${this._cancelModal}" class="toggle">${e}</button>
          </header>
      `}_closeModal(){this.isOpen=!1,this.shadowRoot.querySelector("dialog").close(),document.querySelector("body").style.overflow="initial"}_cancelModal(){this._triggerClose("cancel")}_triggerClose(e){this.dispatchEvent(new CustomEvent("close",{detail:{action:e}}))}_dialogClick(e){if(e.target.tagName!=="DIALOG")return;const t=e.target.getBoundingClientRect();(t.top<=e.clientY&&e.clientY<=t.top+t.height&&t.left<=e.clientX&&e.clientX<=t.left+t.width)===!1&&this._cancelModal()}_dialogKeypress(e){e.key==="Escape"&&this._cancelModal()}_helpMore(){return this.isHelp?h`
          <div class="help-more">
            <h5>${R("Need more help?")}</h5>
            <a
              class="button small"
              id="docslink"
              href="https://disciple.tools/user-docs"
              target="_blank"
              >${R("Read the documentation")}</a
            >
          </div>
        `:null}firstUpdated(){this.isOpen&&this._openModal()}_onButtonClick(){this._triggerClose("button")}get classes(){return{...this.headerClass,"no-header":this.hideHeader,bottom:this.bottom}}render(){const e=h`
      <svg viewPort="0 0 12 12" version="1.1" width='12' height='12'>
          xmlns="http://www.w3.org/2000/svg">
        <line x1="1" y1="11"
              x2="11" y2="1"
              stroke="currentColor"
              stroke-width="2"/>
        <line x1="1" y1="1"
              x2="11" y2="11"
              stroke="currentColor"
              stroke-width="2"/>
      </svg>
    `;return h`
      <dialog
        id=""
        class="dt-modal dt-modal--width ${A(this.classes)}"
        @click=${this._dialogClick}
        @keypress=${this._dialogKeypress}
      >
        <form method="dialog" class=${this.hideHeader?"no-header":""}>
      ${this._dialogHeader(e)}
          <article>
            <slot name="content"></slot>
          </article>
          <footer>
          <div class=footer-button>
          ${this.closeButton?h`
            <button
              class="button small"
              data-close=""
              aria-label="Close reveal"
              type="button"
              @click=${this._onButtonClick}
            >
              <slot name="close-button">${R("Close")}</slot>
              </button>

            `:""}
              ${this.submitButton?h`
                <slot name="submit-button"></span>

                `:""}
              </div>
            ${this._helpMore()}
          </footer>
        </form>
      </dialog>

      ${this.hideButton?null:h`
      <button
        class="button small opener ${A(this.buttonClass||{})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${X(this.buttonStyle||{})}
      >
      ${this.dropdownListImg?h`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">`:""}
      ${this.imageSrc?h`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${X(this.imageStyle||{})}
                 />`:""}
      ${this.buttonLabel?h`${this.buttonLabel}`:""}
      </button>
      `}
    `}}window.customElements.define("dt-modal",vo);class yo extends N{static get properties(){return{...super.properties,title:{type:String},isOpen:{type:Boolean},canEdit:{type:Boolean,state:!0},metadata:{type:Object},center:{type:Array},mapboxToken:{type:String,attribute:"mapbox-token"}}}static get styles(){return[x`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `]}constructor(){super(),this.addEventListener("open",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")),this.isOpen=!0}),this.addEventListener("close",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")),this.isOpen=!1})}connectedCallback(){if(super.connectedCallback(),this.canEdit=!this.metadata,window.mapboxgl)this.initMap();else{const e=document.createElement("script");e.src="https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js",e.onload=this.initMap.bind(this),document.body.appendChild(e)}}initMap(){if(!this.isOpen||!window.mapboxgl||!this.mapboxToken)return;const e=this.shadowRoot.querySelector("#map");if(e&&!this.map){this.map=new window.mapboxgl.Map({accessToken:this.mapboxToken,container:e,style:"mapbox://styles/mapbox/streets-v12",minZoom:1}),this.map.on("load",()=>this.map.resize()),this.center&&this.center.length&&(this.map.setCenter(this.center),this.map.setZoom(15));const t=new mapboxgl.NavigationControl;this.map.addControl(t,"bottom-right"),this.addPinFromMetadata(),this.map.on("click",i=>{this.canEdit&&(this.marker?this.marker.setLngLat(i.lngLat):this.marker=new mapboxgl.Marker().setLngLat(i.lngLat).addTo(this.map))})}}addPinFromMetadata(){if(this.metadata){const{lng:e,lat:t,level:i}=this.metadata;let o=15;i==="admin0"?o=3:i==="admin1"?o=6:i==="admin2"&&(o=10),this.map&&(this.map.setCenter([e,t]),this.map.setZoom(o),this.marker=new mapboxgl.Marker().setLngLat([e,t]).addTo(this.map))}}updated(e){window.mapboxgl&&(e.has("metadata")&&this.metadata&&this.metadata.lat&&this.addPinFromMetadata(),e.has("isOpen")&&this.isOpen&&this.initMap())}onClose(e){var t;((t=e==null?void 0:e.detail)==null?void 0:t.action)==="button"&&this.marker&&this.dispatchEvent(new CustomEvent("submit",{detail:{location:this.marker.getLngLat()}}))}render(){var e;return h`
      <dt-modal
        .title=${(e=this.metadata)==null?void 0:e.label}
        ?isopen=${this.isOpen}
        hideButton
        @close=${this.onClose}
        tabindex="-1"
      >
        <div slot="content">
          <div class="map" id="map"></div>
        </div>

        ${this.canEdit?h`<div slot="close-button">${R("Save")}</div>`:null}
      </dt-modal>

      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `}}window.customElements.define("dt-map-modal",yo);class Ua extends N{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},invalid:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[...super.styles,x`
        :host {
          --dt-location-map-border-color: var(--dt-form-border-color, #fefefe);
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
          display: block;
        }

        .input-group {
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
        }
        .input-group.disabled input,
        .input-group.disabled .field-container {
          background-color: var(--disabled-color);
        }
        .input-group.disabled a,
        .input-group.disabled button {
          cursor: not-allowed;
          pointer-events: none;
        }
        .input-group.disabled *:hover {
          cursor: not-allowed;
        }

        /* === Options List === */
        .option-list {
          list-style: none;
          margin: 0;
          padding: 0;
          border: 1px solid var(--dt-form-border-color, #cacaca);
          background: var(--dt-form-background-color, #fefefe);
          z-index: 10;
          position: absolute;
          width: var(--container-width, 100%);
          width: 100%;
          top: 0;
          left: 0;
          box-shadow: var(--shadow-1);
          max-height: 150px;
          overflow-y: scroll;
        }
        .option-list li {
          border-block-start: 1px solid var(--dt-form-border-color, #cacaca);
          outline: 0;
        }
        .option-list li div,
        .option-list li button {
          padding: 0.5rem 0.75rem;
          color: var(
            --dt-multi-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          font-weight: 100;
          font-size: 1rem;
          text-decoration: none;
          text-align: inherit;
        }
        .option-list li button {
          display: block;
          width: 100%;
          border: 0;
          background: transparent;
        }
        .option-list li button:hover,
        .option-list li button.active {
          cursor: pointer;
          background: var(
            --dt-multi-select-option-hover-background,
            var(--surface-2)
          );
        }
      `,x`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-location-map-background-color, #fefefe);
          border: 1px solid var(--dt-location-map-border-color);
          box-shadow: var(
            --dt-location-map-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          line-height: 1.5;
          margin: 0;
          height: 2.5rem;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
          background-color: var(
            --dt-text-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }
        input.disabled {
          color: var(--dt-text-placeholder-color, #999);
        }
        input:focus-within,
        input:focus-visible {
          outline: none;
        }
        input::placeholder {
          color: var(--dt-text-placeholder-color, #999);
          text-transform: var(--dt-text-placeholder-transform, none);
          font-size: var(--dt-text-placeholder-font-size, 1rem);
          font-weight: var(--dt-text-placeholder-font-weight, 400);
          letter-spacing: var(--dt-text-placeholder-letter-spacing, normal);
        }
        input.invalid {
          border-color: var(--dt-form-border-color-alert, var(--alert-color));
        }

        .field-container {
          --dt-location-map-border-radius: var(--dt-form-border-radius, 0);
          display: flex;
        }
        .field-container > *:first-child {
          border-start-start-radius: var(--dt-location-map-border-radius);
          border-end-start-radius: var(--dt-location-map-border-radius);
        }
        .field-container > *:last-child {
          border-start-end-radius: var(--dt-location-map-border-radius);
          border-end-end-radius: var(--dt-location-map-border-radius);
        }
        .field-container input {
          flex-grow: 1;
          border-width: var(--dt-form-border-width, 1px);
        }
        .field-container .input-addon {
          height: 2.5rem;
          flex-shrink: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          aspect-ratio: 1/1;
          padding: 0.6em;
          border-collapse: collapse;
          color: var(--dt-location-map-button-color, #cc4b37);
          background-color: var(--dt-location-map-background-color, buttonface);
          border: var(--dt-form-border-width, 1px) solid
            var(--dt-location-map-border-color);
          box-shadow: var(
            --dt-location-map-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          min-width: 3em;
        }
        .field-container .input-addon dt-icon {
          display: flex;
          font-size: var(--dt-location-map-icon-size, 1rem);
        }
        .field-container .input-addon:hover {
          background-color: var(
            --dt-location-map-button-hover-background-color,
            #cc4b37
          );
          color: var(--dt-location-map-button-hover-color, #ffffff);
        }
        .field-container.invalid {
          border: 1px solid
            var(--dt-form-border-color-alert, var(--alert-color));
        }

        .input-addon:disabled {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-text-placeholder-color, #999);
        }
        .input-addon:disabled:hover {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-text-placeholder-color, #999);
          cursor: not-allowed;
        }
      `,x`
        /* === Inline Icons === */
        .icon-overlay {
          position: absolute;
          top: 0;
          inset-inline-end: 3.5rem;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .icon-overlay.alert {
          color: var(--alert-color);
        }
        .icon-overlay.success {
          color: var(--success-color);
        }
        .icon-overlay.selected {
          inset-inline-end: 6.25rem;
        }
        @keyframes fadeOut {
          0% {
            opacity: 1;
          }
          75% {
            opacity: 1;
          }
          100% {
            opacity: 0;
          }
        }
        .icon-overlay.fade-out {
          opacity: 0;
          animation: fadeOut 4s;
        }
      `]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",async()=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()}),this.mapboxToken&&(this.mapboxService=new Na(this.mapboxToken))}firstUpdated(){var e;this.googleToken&&!((e=this.metadata)!=null&&e.lat)&&(this.googleGeocodeService=new qa(this.googleToken,window,document))}disconnectedCallback(){super.disconnectedCallback(),this.removeEventListener("autofocus",this.handleAutofocus)}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,o=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;o>r?e.scrollTo({top:o-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_clickOption(e){var i;const t=e.currentTarget??e.target;if(t&&t.value){const o=JSON.parse(t.value);this._select({...o,key:(i=this.metadata)==null?void 0:i.key})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){var e;this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query,key:(e=this.metadata)==null?void 0:e.key}))}async _select(e){if(e.place_id&&this.googleGeocodeService){this.saved=!1,this.loading=!0;const o=await this.googleGeocodeService.getPlaceDetails(e,this.locale);if(this.loading=!1,o){if(o.error){console.error(o.error),this.error=o.error.message;return}e.lat=o.lat,e.lng=o.lng,e.level=o.level}}const t={detail:{metadata:e},bubbles:!1};this.dispatchEvent(new CustomEvent("select",t)),this.metadata=e;const i=this.shadowRoot.querySelector("input");i&&(i.value=e==null?void 0:e.label),this.open=!1,this.activeIndex=-1}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1)}_inputKeyDown(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){const t=e.keyCode||e.which,i=[9,13];e.target.value&&!i.includes(t)&&(this.open=!0),this.query=e.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query){if(this.googleToken&&this.googleGeocodeService){this.saved=!1,this.loading=!0;try{const e=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(e||[]).map(t=>({label:t.description,place_id:t.place_id,source:"user",raw:t})),this.loading=!1}catch(e){console.error(e),this.error=e.message||"An error occurred while searching for locations.",this.loading=!1;return}}else if(this.mapboxToken&&this.mapboxService){this.saved=!1,this.loading=!0;const e=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=e.map(t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"})),this.loading=!1}}return this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e&&(e.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout(()=>this._filterOptions(),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length)){const i=this.shadowRoot.querySelector(".input-group");i&&(this.containerHeight=i.offsetHeight)}}_change(){}_delete(){const e={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",e))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(e){var t,i;if((i=(t=e==null?void 0:e.detail)==null?void 0:t.location)!=null&&i.lat){const{location:o}=e==null?void 0:e.detail,{lat:a,lng:r}=o;if(this.googleGeocodeService){const n=await this.googleGeocodeService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.geometry.location.lng,lat:l.geometry.location.lat,level:l.types&&l.types.length?l.types[0]:null,label:l.formatted_address,source:"user"})}}else if(this.mapboxService){const n=await this.mapboxService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.center[0],lat:l.center[1],level:l.place_type[0],label:l.place_name,source:"user"})}}}}_renderOption(e,t,i){return h`
      <li tabindex="-1">
        <button
          value="${JSON.stringify(e)}"
          type="button"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          ${i??e.label}
        </button>
      </li>
    `}_renderOptions(){const e=[];return this.filteredOptions.length?e.push(...this.filteredOptions.map((t,i)=>this._renderOption(t,i))):this.loading?e.push(h`<li><div>${R("Loading...")}</div></li>`):e.push(h`<li><div>${R("No Data Available")}</div></li>`),e.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,h`<strong>${R("Use")}: "${this.query}"</strong>`)),e}get classes(){return{"field-container":!0,invalid:this.invalid}}render(){var o,a,r,n;const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},t=!!((o=this.metadata)!=null&&o.label),i=((a=this.metadata)==null?void 0:a.lat)&&((r=this.metadata)==null?void 0:r.lng);return h`
      <div class="input-group">
        <div class="${A(this.classes)}">
          <input
            type="text"
            class="${this.disabled?"disabled":null}"
            placeholder="${this.placeholder}"
            .value="${((n=this.metadata)==null?void 0:n.label)??""}"
            .disabled=${t&&i||this.disabled}
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
          />

          ${t&&i?h`
                <button
                  class="input-addon btn-map"
                  @click=${this._openMapModal}
                  ?disabled=${this.disabled}
                >
                  <slot name="map-icon"
                    ><dt-icon icon="mdi:map"></dt-icon
                  ></slot>
                </button>
              `:null}
          ${t?h`
                <button
                  class="input-addon btn-delete"
                  @click=${this._delete}
                  ?disabled=${this.disabled}
                >
                  <slot name="delete-icon"
                    ><dt-icon icon="mdi:trash-can-outline"></dt-icon
                  ></slot>
                </button>
              `:h`
                <button
                  class="input-addon btn-pin"
                  @click=${this._openMapModal}
                  ?disabled=${this.disabled}
                >
                  <slot name="pin-icon"
                    ><dt-icon icon="mdi:map-marker-radius"></dt-icon
                  ></slot>
                </button>
              `}
        </div>
        <ul class="option-list" style=${X(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.loading?h`<dt-spinner
              class="icon-overlay ${i?"selected":""}"
            ></dt-spinner>`:null}
        ${this.renderIconSaved(i)}
      </div>

      <dt-map-modal
        .metadata=${this.metadata}
        mapbox-token="${this.mapboxToken}"
        @submit=${this._onMapModalSubmit}
      ></dt-map-modal>
    `}renderIconSaved(e){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?h`<dt-checkmark
          class="icon-overlay success fade-out ${e?"selected":""}"
        ></dt-checkmark>`:null}}window.customElements.define("dt-location-map-item",Ua);class _o extends M{static get properties(){return{...super.properties,placeholder:{type:String},value:{type:Array},locations:{type:Array,state:!0},open:{type:Boolean,state:!0},limit:{type:Number,attribute:"limit"},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},activeItem:{type:String,state:!0}}}static get styles(){return[...super.styles,x`
        :host {
          font-family: Helvetica, Arial, sans-serif;
        }
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 5px;
        }

        .field-container {
          position: relative;
        }

        .icon-btn {
          background-color: transparent;
          border: none;
          cursor: pointer;
          height: 0.9em;
          padding: 0;
          color: var(--success-color, #cc4b37);
          transform: scale(1.5);
        }
      `]}constructor(){super(),this.limit=0,this.value=[],this.locations=[{id:Date.now()}]}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e))}willUpdate(...e){super.willUpdate(...e),this.value&&this.value.filter(t=>!t.id)&&(this.value=[...this.value.map(t=>({...t,id:t.id||t.grid_meta_id}))]),this.updateLocationList()}firstUpdated(...e){super.firstUpdated(...e),this.internals.setFormValue(JSON.stringify(this.value))}updated(e){var t,i;if(e.has("value")){const o=e.get("value");o&&(o==null?void 0:o.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewLocation()}if(e.has("locations")){const o=e.get("locations");o&&(o==null?void 0:o.length)!==((i=this.locations)==null?void 0:i.length)&&this.focusNewLocation()}}focusNewLocation(){const e=this.shadowRoot.querySelectorAll("dt-location-map-item");e&&e.length&&e[e.length-1].dispatchEvent(new Event("autofocus"))}updateLocationList(){if(!this.disabled&&(this.open||!this.value||!this.value.length)){this.open=!0;const e=(this.value||[]).filter(i=>i.label),t=this.limit===0||e.length<this.limit;this.locations=[...e,...t?[{id:Date.now()}]:[]]}else this.locations=[...(this.value||[]).filter(e=>e.label)]}selectLocation(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i={...e.detail.metadata,id:Date.now()};if(i.lat){const o=Math.round(i.lat*1e7)/1e7,a=Math.round(i.lng*10**7)/10**7;this.activeItem=`${o}/${a}`}else this.activeItem=i.label;this.value=[...(this.value||[]).filter(o=>o.label&&(!o.key||o.key!==i.key)&&(!o.id||o.id!==i.id)),i],this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}deleteItem(e){var a;this.activeItem=void 0;const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i=(a=e.detail)==null?void 0:a.metadata,o=i==null?void 0:i.grid_meta_id;o?this.value=(this.value||[]).filter(r=>r.grid_meta_id!==o):i.lat&&i.lng?this.value=(this.value||[]).filter(r=>r.lat!==i.lat&&r.lng!==i.lng):this.value=(this.value||[]).filter(r=>(!r.key||r.key!==i.key)&&(!r.id||r.id!==i.id)),this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}addNew(){const e=(this.value||[]).filter(t=>t.label);(this.limit===0||e.length<this.limit)&&(this.open=!0,this.updateLocationList())}reset(){this.value=[],this._setFormValue([])}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t.label))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required")):(this.invalid=!1,this.internals.setValidity({}))}labelTemplate(){return this.label?h`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon?null:h`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
        ${!this.open&&(this.limit==0||this.locations.length<this.limit)?h`
              <slot name="icon-end" slot="icon-end">
                <button
                  @click="${this.addNew}"
                  class="icon-btn"
                  id="add-item"
                  type="button"
                >
                  <dt-icon icon="mdi:plus-thick"></dt-icon>
                </button>
              </slot>
            `:null}
      </dt-label>
    `:""}renderItem(e,t){const i=Math.round(e.lat*1e7)/1e7,o=Math.round(e.lng*10**7)/10**7,a=`${i}/${o}`,r=this.activeItem&&(this.activeItem===e.label||this.activeItem===a)||t===0&&!this.activeItem;return h`
      <dt-location-map-item
        placeholder="${this.placeholder}"
        .metadata=${e}
        mapbox-token="${this.mapboxToken}"
        google-token="${this.googleToken}"
        @delete=${this.deleteItem}
        @select=${this.selectLocation}
        ?disabled=${this.disabled}
        ?invalid=${this.invalid&&this.touched}
        ?loading=${r?this.loading:!1}
        ?saved=${r?this.saved:!1}
      ></dt-location-map-item>
    `}render(){return[...this.value||[]],h`
      ${this.labelTemplate()}
      <div class="input-group">
        ${H(this.locations||[],e=>e.id,(e,t)=>this.renderItem(e,t))}
        ${this.renderError()} ${this.renderIconInvalid()}
      </div>
    `}}window.customElements.define("dt-location-map",_o);class wo extends M{static get styles(){return[...super.styles,x`
        input {
          color: var(
            --dt-number-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          appearance: none;
          background-color: var(
            --dt-number-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(--dt-number-border-color, var(--dt-form-border-color, #cecece));
          border-radius: var(
            --dt-number-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-number-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
          width: 100%;
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
          background-color: var(
            --dt-number-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }
        input:focus-within,
        input:focus-visible {
          outline: none;
        }
        input.invalid {
          border-color: var(
            --dt-number-border-color-alert,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }

        .icon-overlay {
          inset-inline-end: 2rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:String,reflect:!0},min:{type:Number},max:{type:Number}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_validateValue(e){return!(e<this.min||e>this.max)}async _change(e){if(this._validateValue(e.target.value)){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value},bubbles:!0});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}else e.currentTarget.value="",this.value=void 0}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const i=this.internals.form.querySelector("button[type=submit]");i&&i.click()}}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return h`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          type="number"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${A(this.classes)}"
          .value="${this.value}"
          min="${le(this.min)}"
          max="${le(this.max)}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
          part="input"
        />

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-number",wo);class $o extends M{static get styles(){return[...super.styles,x`
        :host {
          --dt-single-select-text-color: var(--dt-form-text-color, #0a0a0a);
          position: relative;
        }

        select {
          appearance: none;
          background-color: var(
            --dt-single-select-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          background-image:
            linear-gradient(
              45deg,
              transparent 50%,
              var(
                  --dt-single-select-icon-color,
                  var(
                    --dt-single-select-text-color,
                    var(--dt-form-text-color, #0a0a0a)
                  )
                )
                50%
            ),
            linear-gradient(
              135deg,
              var(
                  --dt-single-select-icon-color,
                  var(
                    --dt-single-select-text-color,
                    var(--dt-form-text-color, #0a0a0a)
                  )
                )
                50%,
              transparent 50%
            );
          background-position:
            calc(100% - 20px) calc(1em + 2px),
            calc(100% - 15px) calc(1em + 2px),
            calc(100% - 2.5em) 0.5em;
          background-size:
            5px 5px,
            5px 5px,
            1px 1.5em;
          background-repeat: no-repeat;
          border: 1px solid
            var(
              --dt-single-select-border-color,
              var(--dt-form-border-color, #cacaca)
            );
          border-radius: var(
            --dt-single-select-border-radius,
            var(--dt-form-border-radius, 0)
          );
          color: var(
            --dt-single-select-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          font-family: var(--font-family, sans-serif);
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          padding-inline-end: 1.6rem;
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
          box-sizing: border-box;
          width: 100%;
          text-transform: none;
        }
        select:disabled,
        select[readonly] {
          background-color: var(
            --dt-single-select-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }
        [dir='rtl'] select {
          background-position:
            15px calc(1em + 2px),
            20px calc(1em + 2px),
            2.5em 0.5em;
        }
        select.color-select {
          background-image:
            linear-gradient(45deg, transparent 50%, currentColor 50%),
            linear-gradient(135deg, currentColor 50%, transparent 50%);
          background-color: var(
            --dt-single-select-border-color,
            var(--dt-form-border-color, #cacaca)
          );
          border: none;
          border-radius: 10px;
          color: var(
            --dt-single-select-text-color-inverse,
            var(--dt-form-text-color-inverse, #fff)
          );
          font-weight: 700;
          text-shadow: rgb(0 0 0 / 45%) 0 0 6px;
        }

        .icon-overlay {
          height: 2.5rem;
          inset-inline-end: 2.5rem;
        }
        select.invalid {
          border-color: var(
            --dt-single-select-border-color-alert,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},value:{type:String,reflect:!0},color:{type:String,state:!0},onchange:{type:String}}}updateColor(){if(this.value&&this.options){const e=this.options.filter(t=>t.id===this.value);e&&e.length&&(this.color=e[0].color)}}isColorSelect(){return(this.options||[]).reduce((e,t)=>e||t.color,!1)}willUpdate(e){super.willUpdate(e),e.has("value")&&this.updateColor()}_change(e){const t=e.target.value,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:t}});this.value=t,this._setFormValue(this.value),this.dispatchEvent(i)}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{invalid:this.touched&&this.invalid,"color-select":this.isColorSelect()}}render(){return h`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""}"
        dir="${this.RTL?"rtl":"ltr"}"
      >
        <select
          name="${this.name}"
          aria-label="${this.name}"
          @change="${this._change}"
          class="${A(this.classes)}"
          style="${this.color?"background-color: "+this.color+";":""}"
          ?disabled="${this.disabled}"
          ?required=${this.required}
          part="select"
        >
          <option disabled selected hidden value="">${this.placeholder}</option>

          ${this.options&&this.options.map(e=>h`
              <option value="${e.id}" ?selected="${e.id===this.value}">
                ${e.label}
              </option>
            `)}
        </select>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-single-select",$o);class Ft extends M{static get styles(){return[...super.styles,x`
        input {
          color: var(--dt-text-text-color, var(--dt-form-text-color, #0a0a0a));
          appearance: none;
          background-color: var(
            --dt-text-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(--dt-text-border-color, var(--dt-form-border-color, #cecece));
          border-radius: var(
            --dt-text-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-text-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
          width: 100%;
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
          background-color: var(
            --dt-text-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }
        input:focus-within,
        input:focus-visible {
          outline: none;
        }
        input::placeholder {
          color: var(
            --dt-text-placeholder-color,
            var(--dt-form-placeholder-color, #999)
          );
          text-transform: var(--dt-text-placeholder-transform, none);
          font-size: var(--dt-text-placeholder-font-size, 1rem);
          font-weight: var(--dt-text-placeholder-font-weight, 400);
          letter-spacing: var(--dt-text-placeholder-letter-spacing, normal);
        }
        input.invalid {
          border-color: var(
            --dt-text-border-color-alert,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }
      `]}static get properties(){return{...super.properties,type:{type:String},placeholder:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const i=this.internals.form.querySelector("button[type=submit]");i&&i.click()}}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return h`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${A(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
          part="input"
        />

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-text",Ft);class xo extends M{static get styles(){return[...super.styles,x`
        textarea {
          color: var(
            --dt-textarea-text-color,
            var(--dt-form-text-color, #0a0a0a)
          );
          appearance: none;
          background-color: var(
            --dt-textarea-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(
              --dt-textarea-border-color,
              var(--dt-form-border-color, #cecece)
            );
          border-radius: var(
            --dt-textarea-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-textarea-input-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 10rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
          position: relative;
          outline: 0;
          resize: none;
          width: 100%;
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
          background-color: var(
            --dt-textarea-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          cursor: not-allowed;
        }

        textarea.invalid {
          border-color: var(
            --dt-textarea-border-color-alert,
            var(--dt-form-border-color-alert, var(--alert-color))
          );
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const{value:t}=this,i=e.target.value,o=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:t,newValue:i}});this.value=i,this._setFormValue(this.value),this.dispatchEvent(o)}_validateRequired(){const{value:e}=this;!e&&this.required?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return h`
      ${this.labelTemplate()}

      <div class="input-group">
        <textarea
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${A(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          part="textarea"
        ></textarea>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-textarea",xo);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function q(s,e,t){return s?e(s):t==null?void 0:t(s)}class zt extends Ft{static get styles(){return[...super.styles,x`
        :host {
          display: block;
        }
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(
            --dt-multi-text-background-color,
            var(--dt-form-background-color, #fefefe)
          );
          border: 1px solid
            var(
              --dt-multi-text-border-color,
              var(--dt-form-border-color, #fefefe)
            );
          border-radius: var(
            --dt-multi-text-border-radius,
            var(--dt-form-border-radius, 0)
          );
          box-shadow: var(
            --dt-multi-text-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: var(--dt-form-padding, 0.5333333333rem);
          transition: var(
            --dt-form-transition,
            box-shadow 0.5s,
            border-color 0.25s ease-in-out
          );
        }
        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
          background-color: var(
            --dt-multi-text-disabled-background-color,
            var(--dt-form-disabled-background-color, #e6e6e6)
          );
          color: var(
            --dt-multi-text-disabled-color,
            var(--dt-form-placeholder-color, #999)
          );
          cursor: not-allowed;
        }
        input.disabled {
          color: var(
            --dt-multi-text-disabled-color,
            var(--dt-form-placeholder-color, #999)
          );
        }
        input:focus-within,
        input:focus-visible {
          outline: none;
        }
        input::placeholder {
          color: var(--dt-text-placeholder-color, #999);
          text-transform: var(--dt-text-placeholder-transform, none);
          font-size: var(--dt-text-placeholder-font-size, 1rem);
          font-weight: var(--dt-text-placeholder-font-weight, 400);
          letter-spacing: var(--dt-text-placeholder-letter-spacing, normal);
        }
        input.invalid {
          border-color: var(--dt-form-border-color-alert, var(--alert-color));
        }
      `,x`
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }
        .field-container {
          display: flex;
        }
        .field-container input {
          flex-grow: 1;
        }
        .field-container .input-addon {
          height: 2.5rem;
          flex-shrink: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          aspect-ratio: 1/1;
          padding: 10px;
          border: solid 1px gray;
          border-collapse: collapse;
          background-color: var(
            --dt-multi-text-background-color,
            var(--dt-form-background-color, buttonface)
          );
          border: 1px solid
            var(
              --dt-multi-text-border-color,
              var(--dt-form-border-color, #fefefe)
            );
          border-radius: var(--dt-multi-text-border-radius, 0);
          box-shadow: var(
            --dt-multi-text-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
        }

        .input-addon:disabled {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-text-placeholder-color, #999);
        }
        .input-addon:disabled:hover {
          background-color: var(--dt-form-disabled-background-color);
          color: var(--dt-text-placeholder-color, #999);
          cursor: not-allowed;
        }

        .input-addon.btn-remove {
          color: var(
            --dt-multi-text-remove-button-color,
            var(--alert-color, #cc4b37)
          );
          &:disabled {
            color: var(
              --dt-multi-text-disabled-color,
              var(--dt-form-placeholder-color, #999)
            );
          }
          &:hover:not([disabled]) {
            background-color: var(
              --dt-multi-text-remove-button-hover-background-color,
              var(--alert-color, #cc4b37)
            );
            color: var(--dt-multi-text-remove-button-hover-color, #ffffff);
          }
        }
        .input-addon.btn-add {
          color: var(
            --dt-multi-text-add-button-color,
            var(--success-color, #cc4b37)
          );
          &:disabled {
            color: var(
              --dt-multi-text-disabled-color,
              var(--dt-form-placeholder-color, #999)
            );
          }
          &:hover:not([disabled]) {
            background-color: var(
              --dt-multi-text-add-button-hover-background-color,
              var(--success-color, #cc4b37)
            );
            color: var(--dt-multi-text-add-button-hover-color, #ffffff);
          }
        }

        .icon-overlay {
          inset-inline-end: 3.5rem;
          height: 100%;
        }
        .field-container:has(.btn-remove) ~ .icon-overlay {
          inset-inline-end: 5.5rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0}}}updated(e){var t;if(e.has("value")){const i=e.get("value");i&&(i==null?void 0:i.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewItem()}}focusNewItem(){const e=this.shadowRoot.querySelectorAll("input");e&&e.length&&e[e.length-1].focus()}_addItem(){const e={verified:!1,value:"",tempKey:Date.now().toString()};this.value=[...this.value,e]}_removeItem(e){const t=e.currentTarget.dataset.key;if(t){const i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}}),o=this.value.filter(a=>a.tempKey!==t).map(a=>{const r={...a};return a.key===t&&(r.delete=!0),r});o.filter(a=>!a.delete).length||o.push({value:"",tempKey:Date.now().toString()}),this.value=o,i.detail.newValue=this.value,this.dispatchEvent(i),this._setFormValue(this.value)}}_change(e){var i,o;const t=(o=(i=e==null?void 0:e.currentTarget)==null?void 0:i.dataset)==null?void 0:o.key;if(t){const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=this.value.map(r=>{var n;return{...r,value:r.key===t||r.tempKey===t?(n=e.target)==null?void 0:n.value:r.value}}),a.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(a)}}_inputFieldTemplate(e,t){return h`
      <div class="field-container">
        <input
          data-key="${e.key??e.tempKey}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${A(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
        />

        ${q(t>1||e.key||e.value,()=>h`
            <button
              class="input-addon btn-remove"
              @click=${this._removeItem}
              data-key="${e.key??e.tempKey}"
              ?disabled=${this.disabled}
            >
              <dt-icon icon="mdi:close"></dt-icon>
            </button>
          `,()=>h``)}
        <button
          class="input-addon btn-add"
          @click=${this._addItem}
          ?disabled=${this.disabled}
        >
          <dt-icon icon="mdi:plus-thick"></dt-icon>
        </button>
      </div>
    `}renderIcons(){let e=0,t=!1;for(const[a,r]of(this.value||[]).entries())!r.value&&a!==0?e+=1:r.delete&&!t&&(t=!0);let i=.5;t===!1&&(i+=3*e);const o=`padding-block-end: ${i.toString()}rem`;return h`
      ${this.renderIconInvalid()} ${this.renderError()}
      ${this.renderIconLoading(o)} ${this.renderIconSaved(o)}
    `}renderIconLoading(e){return this.loading?h`<dt-spinner class="icon-overlay" style="${e}"></dt-spinner>`:null}renderIconSaved(e){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?h`<dt-checkmark
          class="icon-overlay success fade-out"
          style="${e}"
        ></dt-checkmark>`:null}_renderInputFields(){return(!this.value||!this.value.length)&&(this.value=[{verified:!1,value:"",tempKey:Date.now().toString()}]),h`
      ${H((this.value??[]).filter(e=>!e.delete),e=>e.id,e=>this._inputFieldTemplate(e,this.value.length))}
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t.value))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return h`
      ${this.labelTemplate()}
      <div class="input-group">
        ${this._renderInputFields()} ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-text",zt);class ko extends zt{static get styles(){return[...super.styles,x`
        .icon-btn {
          background-color: transparent;
          border: none;
          cursor: pointer;
          height: 0.9em;
          padding: 0;
          color: var(--success-color, #cc4b37);
          transform: scale(1.5);
        }

        .groups-list {
        position: relative;
        }
          
        .option-list {
          display: block;
          position: absolute;
          inset-inline-start: auto;
          inset-inline-end: 0;
          list-style: none;
          margin-top: 0;
          padding: 0;
          border: 1px solid var(--dt-form-border-color, #CACACA);
          background: var(--dt-form-background-color, #FEFEFE);
          z-index: 10;
          box-shadow: var(--shadow-1);
          max-height: 150px;
        }

        .option-list li {
          border-block-start: 1px solid var(--dt-form-border-color, #CACACA);
          outline: 0;
        }
        .option-list li div,
        .option-list li button {
          padding: 0.5rem 0.75rem;
          color: var(--dt-multi-select-text-color, #0A0A0A);
          font-weight: 100;
          font-size: 1rem;
          text-decoration: none;
          text-align: inherit;
        }

        .option-list li button {
          display: block;
          width: 100%;
          border: 0;
          background: transparent;
        }
        .option-list li button:hover,
        .option-list li button.active {
          cursor: pointer;
          background: var(--dt-multi-select-option-hover-background, #F5F5F5);
        }
        .link-button {
        background: none;
        border: none;
        padding-left: 0.5rem;
        margin: 0;
        color: #0000ee; /* Default blue link color (adjust as needed) */
        text-decoration: underline;
        cursor: pointer;
        }
        .groups-no-value {
          display: flex;
          align-items: center;
          min-height: 2.5rem;
        }
        .icon-overlay {
          inset-inline-end: 0.5rem;
          height: 100%;
        }
        .field-container:has(.btn-remove) ~ .icon-overlay {
          inset-inline-end: 3rem;
        }
        .heading {
          margin-top: .5rem;
          margin-bottom: 0;
          font-family: var(--font-family);
          font-size: var(--dt-label-font-size, 14px);
          font-weight: var(--dt-label-font-weight, 700);
          color: var(--dt-label-color, #000);
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }
      `]}static get properties(){return{...super.properties,groups:{type:Array},open:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},activeGroup:{type:String,state:!0},isDeleting:{type:Boolean,state:!0}}}constructor(){super(),this.open=!1,this.activeIndex=-1}_addItem(e){var i;const t={verified:!1,value:"",tempKey:Date.now().toString(),type:e.id};(i=this.value[0])!=null&&i.type?this.value=[...this.value,t]:this.value=[t],this.open=!1,this.activeIndex=-1,this.updateComplete.then(()=>{const o=this.renderRoot.querySelectorAll("input"),a=Array.from(o).find(r=>r.getAttribute("data-key")===t.tempKey);a==null||a.focus()})}_removeItem(e){const t=e.currentTarget.dataset.key;if(t){const i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}}),o=this.value.filter(a=>a.tempKey!==t).map(a=>{const r={...a};return(a.meta_id===t||a.tempKey===t)&&(r.delete=!0,this.activeGroup=r.type),r});this.value=o,i.detail.newValue=this.value,this.dispatchEvent(i),this._setFormValue(this.value)}}_change(e){var i,o;const t=(o=(i=e==null?void 0:e.currentTarget)==null?void 0:i.dataset)==null?void 0:o.key;if(t){const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=this.value.map(r=>{var n;return r.meta_id===t||r.tempKey===t?(this.activeGroup=r.type,{...r,value:(n=e.target)==null?void 0:n.value}):r}),a.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(a)}}handleClick(){if(this.renderRoot.querySelector(".icon-btn").focus(),this.groups){this.open=!this.open,this.activeIndex=-1;const e=this.renderRoot.querySelector(".option-list");e==null||e.focus()}else{const e={verified:!1,value:"",tempKey:Date.now().toString()};this.value=[...this.value,e]}}_handleButtonBlur(e){var t;(t=e.relatedTarget)!=null&&t.id.includes("group-")||(this.open=!1)}_inputKeyDown(e){const t=e.keyCode||e.which;if(this.groups)switch(t){case 38:e.preventDefault(),this.open=!0,this._listHighlightPrevious();break;case 40:e.preventDefault(),this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:e.preventDefault(),this.open?this._keyboardSelectOption():this.open=!0;break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0,this.query=e.target.value;break}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.groups.length?this._addItem(this.query):this._addItem(this.groups[this.activeIndex]))}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.groups.length,this.activeIndex+1):this.activeIndex=Math.min(this.groups.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_inputFieldTemplate(e,t){var i;return h`
      <div class="field-container">
        <input
          data-key="${e.meta_id??e.tempKey}"
          tabindex="1"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${A(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
        />

        ${q(((i=this.value[0])==null?void 0:i.type)||!this.groups&&t>1,()=>h`
            <button
              class="input-addon btn-remove"
              tabindex="1"
              @click=${this._removeItem}
              data-key="${e.meta_id??e.tempKey}"
              ?disabled=${this.disabled}
            >
              <dt-icon icon="mdi:close"></dt-icon>
            </button>
          `,()=>h``)}
      </div>
    `}_renderGroup(e,t){return h`
      <li tabindex="-1">
        <button
          value="${e.id}"
          id="group-${e.id}"
          type="button"
          data-label="${e.label}"
          @click="${()=>this._addItem(e)}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex>-1&&this.activeIndex===t?"active":""}"
        >
          ${e.label}
        </button>
      </li>
    `}renderIcons(){const e=this.groups.findIndex(l=>l.id===this.activeGroup);this.loading&&(this.isDeleting=!1);for(const[l,d]of(this.value||[]).entries())d.delete&&!this.isDeleting&&(this.isDeleting=!0,this.activeGroup=d.type);const t=this.groups.map(l=>(this.value||[]).filter(d=>d.type===l.id&&!d.delete).length);let i=0,o=0;for(let l=t.length-1;l>e;l-=1)if(t[l]>0){o+=t[l];break}for(let l=0;l<e;l+=1)if(t[l]>0){i+=t[l];break}let a=0,r=0;for(let l=this.groups.length-1;l>=0;l-=1){const d=this.groups[l];let u=0;l>e?(u=(this.value||[]).filter(p=>p.type===d.id&&!p.delete).length,u>0&&(r+=1)):l===e&&(u=(this.value||[]).filter(p=>p.type===d.id&&!p.delete).length-1,this.isDeleting&&u===0?i>0?u=i-1:o>0&&(r-=1):u=(this.value||[]).filter(p=>p.type===d.id&&!p.delete).length-1),a+=u}a!==0&&(a*=3,a+=r*2.5),a+=.5,console.log(a);const n=`padding-block-end: ${a.toString()}rem`;return h`
      ${this.renderIconInvalid()} ${this.renderError()}
      ${this.renderIconLoading(n)} ${this.renderIconSaved(n)}
      `}_renderInputFields(){(!this.value||!this.value.length)&&(this.value=[{verified:!1,value:"",tempKey:Date.now().toString()}]);const e=this.value[0];return this.groups&&e&&e.type?this.groups.map(t=>{const i=(this.value??[]).filter(o=>!o.delete&&o.type===t.id);if(i.length>0)return h`
          <h3 class="heading">${t.label}</h3>
          ${H(i,o=>o.id,o=>this._inputFieldTemplate(o,this.value.length))}
        `}):this.groups?h`
      <div class="groups-no-value">
        No items to show.<button class="link-button" @click=${this.handleClick}>Add items</button>
      </div>
      `:h`
      ${H((this.value??[]).filter(t=>!t.delete),t=>t.id,t=>this._inputFieldTemplate(t,this.value.length))}
    `}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}labelTemplate(){return this.label?h`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
        exportparts="label: label-container"
      >
        ${this.icon?null:h`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
        <slot name="icon-end" slot="icon-end">
          <button
            @click="${this.handleClick}"
            @keydown="${this._inputKeyDown}"
            @blur="${this._handleButtonBlur}"
            class="icon-btn"
            id="add-item"
            type="button"
            tabindex="1"
          >
            <dt-icon icon="mdi:plus-thick"></dt-icon>
          </button>
          ${this.open?h`
          <div class="groups-list">
            <ul id="myDropdown" class="option-list">
              ${H(this.groups,e=>e.id,(e,t)=>this._renderGroup(e,t))}
            </ul>
          </div>
          `:""}
        </slot>
      </dt-label>
    `:""}}window.customElements.define("dt-multi-text-groups",ko);class So extends M{static get styles(){return[...super.styles,x`
        :host {
          margin-bottom: var(--dt-multi-select-button-group-margin-bottom, 5px);
          --dt-button-font-size: var(
            --dt-multi-select-button-group-button-font-size,
            0.75rem
          );
          --dt-button-font-weight: var(
            --dt-multi-select-button-group-button-font-weight,
            0
          );
          --dt-button-line-height: var(
            --dt-multi-select-button-group-button-line-height,
            1em
          );
          --dt-button-padding-y: var(
            --dt-multi-select-button-group-button-padding-y,
            0.85em
          );
          --dt-button-padding-x: var(
            --dt-multi-select-button-group-button-padding-x,
            1em
          );
        }
        span .icon {
          vertical-align: middle;
          padding: 0 2px;
        }
        .icon img {
          width: 15px !important;
          height: 15px !important;
          margin-right: 1px !important;
          vertical-align: sub;
        }
        .button-group {
          display: inline-flex;
          flex-direction: row;
          flex-wrap: wrap;
          gap: var(--dt-multi-select-button-group-gap-y, 5px)
            var(--dt-multi-select-button-group-gap-x, 10px);
        }
        dt-button {
          margin: 0px;
        }

        .icon-overlay {
          padding-block: 0;
        }

        .input-group.disabled {
          background-color: inherit;
        }

        .error-container {
          margin-block-start: var(
            --dt-multi-select-button-group-error-margin-top,
            5px
          );
        }
        .invalid ~ .error-container {
          border-top-width: 1px;
        }
      `]}constructor(){super(),this.options=[]}static get properties(){return{value:{type:Array,reflect:!0},context:{type:String},options:{type:Array},outline:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector(".input-group")}_select(e){const t=this.value,i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:t}});if(this.value&&this.value.length){const o=this.value.includes(e);this.value=[...this.value.filter(a=>a!==e&&a!==`-${e}`),o?`-${e}`:e]}else this.value=[e];i.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(i)}_clickOption(e){var t;(t=e==null?void 0:e.currentTarget)!=null&&t.value&&this._select(e.currentTarget.value)}_inputKeyUp(e){switch(e.keyCode||e.which){case 13:this._clickOption(e);break}}_renderButton(e){const i=(this.value??[]).includes(e.id)?"success":this.touched&&this.invalid?"alert":"inactive",o=this.outline??(this.touched&&this.invalid);return h`
      <dt-button
        custom
        type="success"
        context=${i}
        .value=${e.id}
        @click="${this._clickOption}"
        ?disabled="${this.disabled}"
        ?outline="${o}"
        role="button"
        value="${e.id}"
        part="button"
      >
        ${e.icon?h`<span class="icon"
              ><img src="${e.icon}" alt="${this.iconAltText}"
            /></span>`:null}
        ${e.label}
      </dt-button>
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"button-group":!0,invalid:this.touched&&this.invalid}}render(){return h`
      ${this.labelTemplate()}
      <div
        class="input-group ${this.disabled?"disabled":""}"
        part="input-group"
      >
        <div class="${A(this.classes)}" part="button-group">
          ${H(this.options??[],e=>e.id,e=>this._renderButton(e))}
        </div>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-select-button-group",So);class Eo extends M{constructor(){super();Me(this,"_handleUploadStagedEvent",()=>{this.uploadStagedFiles()});this.value=[],this.acceptedFileTypes=["image/*","application/pdf"],this.maxFileSize=null,this.maxFiles=null,this.deleteEnabled=!0,this.downloadEnabled=!0,this.renameEnabled=!0,this.displayLayout="grid",this.fileTypeIcon="",this.autoUpload=!0,this.postType="",this.postId="",this.metaKey="",this.keyPrefix="",this.uploading=!1,this.stagedFiles=[],this._uploadZoneExpanded=!1,this._dragOver=!1,this._editingFileKey="",this._editingFileName="",this._dragLeaveTimeout=null,this._resizeObserver=null,this._keydownAttached=!1,this._suppressRenameBlurCommit=!1,this._standaloneFilesByKey=new Map}static get styles(){return[...super.styles,x`
        :host {
          display: block;
        }

        .upload-zone {
          border: 2px dashed var(--dt-upload-border-color, #ccc);
          border-radius: 4px;
          text-align: center;
          background-color: var(--dt-upload-background-color, #fafafa);
          transition:
            padding 0.2s ease,
            background-color 0.2s ease,
            border-color 0.2s ease;
          cursor: pointer;
          position: relative;
          width: 100%;
          box-sizing: border-box;
        }

        .upload-zone.compact {
          padding: 0.75rem;
        }

        .upload-zone.expanded {
          padding: 2rem;
        }

        .upload-zone:hover:not(.disabled):not(.uploading) {
          border-color: var(--dt-upload-border-color-hover, #999);
          background-color: var(--dt-upload-background-color-hover, #f0f0f0);
        }

        .upload-zone.drag-over {
          border-color: var(--primary-color, #0073aa);
          background-color: var(--dt-upload-background-color-drag, #e8f4f8);
        }

        .upload-zone.disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }

        .upload-zone.uploading {
          pointer-events: none;
        }

        .upload-zone-content {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 0.5rem;
        }

        .upload-zone-content .expandable {
          transition: opacity 0.2s ease;
        }

        .upload-zone.compact .upload-zone-content .expandable {
          display: none;
        }

        .upload-zone.expanded .upload-zone-content .expandable {
          display: block;
        }

        .upload-icon {
          color: var(--dt-upload-icon-color, #999);
          flex-shrink: 0;
          transition: font-size 0.2s ease;
        }

        .upload-zone.compact .upload-icon {
          font-size: 1.75rem;
        }

        .upload-zone.expanded .upload-icon {
          font-size: 3rem;
        }

        .upload-text {
          font-size: 1rem;
          color: var(--dt-upload-text-color, #666);
        }

        .upload-hint {
          font-size: 0.875rem;
          color: var(--dt-upload-hint-color, #999);
        }

        input[type='file'] {
          position: absolute;
          width: 0;
          height: 0;
          opacity: 0;
          overflow: hidden;
        }

        .files-container {
          margin-top: 1rem;
        }

        .files-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
          gap: 1rem;
        }

        .files-list {
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }

        .file-item {
          position: relative;
          border: 1px solid var(--dt-file-upload-border-color, #ddd);
          border-radius: 4px;
          overflow: hidden;
          background-color: var(--dt-file-upload-background-color, #fff);
        }

        .file-item-grid {
          aspect-ratio: 1;
          display: flex;
          flex-direction: column;
        }

        .file-item-list {
          display: grid;
          grid-template-columns: 40px 1fr auto;
          grid-template-rows: auto auto;
          align-items: center;
          padding: 0.5rem;
          gap: 0 0.75rem;
          row-gap: 0.125rem;
        }

        .file-item-list .file-preview-link,
        .file-item-list .file-icon-area {
          grid-column: 1;
          grid-row: 1 / -1;
          width: 40px;
          height: 40px;
          min-width: 40px;
          min-height: 40px;
          border-radius: 6px;
          overflow: hidden;
        }

        .file-item-list .file-name,
        .file-item-list .file-name-edit,
        .file-item-list input.file-name-edit {
          grid-column: 2;
          grid-row: 1;
          min-width: 0;
        }

        .file-item-list .file-size {
          grid-column: 2;
          grid-row: 2;
        }

        .file-item-list .file-actions {
          grid-column: 3;
          grid-row: 1 / -1;
          position: relative;
        }

        .status-indicators {
          display: flex;
          justify-content: flex-end;
          margin-top: 0.5rem;
        }

        .status-indicators .icon-overlay {
          position: static;
          inset-inline-end: auto;
          top: auto;
          height: auto;
          padding-block: 0;
        }

        .file-item-list .file-icon-area dt-icon {
          font-size: 1.25rem;
        }

        .file-preview-link {
          display: block;
          cursor: pointer;
          flex: 1;
          min-height: 0;
          height: calc(100% - 1.5rem);
        }

        .file-preview-link img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          display: block;
        }

        .file-icon-area {
          display: flex;
          align-items: center;
          justify-content: center;
          background-color: var(--dt-file-upload-icon-background, #f5f5f5);
          color: var(--dt-file-upload-icon-color, #999);
          flex: 1;
          min-height: 0;
          height: calc(100% - 1.5rem);
        }

        .file-icon-area dt-icon {
          font-size: 2rem;
        }

        .file-icon-area img {
          max-width: 100%;
          max-height: 100%;
          object-fit: contain;
        }

        .file-name {
          font-size: 0.75rem;
          color: var(--dt-file-upload-name-color, #333);
          padding: 0.25rem 0.5rem;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }

        .file-name-editable {
          cursor: pointer;
          position: relative;
          z-index: 2;
        }

        .file-name-editable:hover {
          text-decoration: underline;
        }

        .file-name-edit {
          font-size: 0.75rem;
          color: var(--dt-file-upload-name-color, #333);
          padding: 0.25rem 0.5rem;
          width: 100%;
          box-sizing: border-box;
          border: 1px solid var(--primary-color, #0073aa);
          border-radius: 2px;
          background: var(--dt-file-upload-background-color, #fff);
        }

        .file-name-edit:focus {
          outline: none;
          border-color: var(--primary-color, #0073aa);
        }

        .file-size {
          font-size: 0.7rem;
          color: var(--dt-file-upload-size-color, #999);
          padding: 0 0.5rem 0.25rem;
        }

        .file-actions {
          position: absolute;
          top: 0.25rem;
          inset-inline-end: 0.25rem;
          display: flex;
          gap: 0.25rem;
          z-index: 1;
          pointer-events: none;
        }

        .file-actions button {
          pointer-events: auto;
          background: rgba(255, 255, 255, 0.9);
          border: none;
          border-radius: 4px;
          padding: 0.25rem;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .file-actions button:hover {
          background: #fff;
        }

        .file-actions button dt-icon {
          font-size: 1rem;
        }

        .file-actions button.download {
          color: var(--primary-color, #0073aa);
        }

        .file-actions button.delete {
          color: var(--alert-color, #dc3545);
        }

        .staged-files {
          margin-top: 1rem;
          padding: 1rem;
          border: 1px dashed var(--dt-upload-border-color, #ccc);
          border-radius: 4px;
          background: var(--dt-upload-background-color, #fafafa);
        }

        .staged-files-title {
          font-size: 0.875rem;
          font-weight: 600;
          margin-bottom: 0.5rem;
        }

        .staged-file-item {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.25rem 0;
          font-size: 0.875rem;
        }

        .staged-file-item span {
          flex: 1;
          min-width: 0;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .staged-file-item button.remove {
          flex-shrink: 0;
          margin-inline-start: auto;
          padding: 0.25rem;
          background: transparent;
          border: none;
          color: var(--alert-color, #dc3545);
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .staged-file-item button.remove:hover {
          opacity: 0.8;
        }

        .upload-staged-btn {
          margin-top: 0.5rem;
          padding: 0.5rem 1rem;
          background: var(--primary-color, #0073aa);
          color: #fff;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 0.875rem;
        }

        .upload-staged-btn:hover:not(:disabled) {
          opacity: 0.9;
        }

        .upload-staged-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }

        .error-container {
          margin-top: 1rem;
          max-width: 100%;
          overflow: hidden;
        }

        .error-container .error-text {
          flex: 1;
          min-width: 0;
          overflow-wrap: break-word;
          word-wrap: break-word;
          word-break: break-word;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0,converter:{fromAttribute:t=>{if(t==null||t==="")return[];try{const i=JSON.parse(t);return Array.isArray(i)?i:[]}catch{return[]}},toAttribute:t=>Array.isArray(t)&&t.length>0?JSON.stringify(t):""}},acceptedFileTypes:{type:Array,attribute:"accepted-file-types"},acceptedFileTypesLabel:{type:String,attribute:"accepted-file-types-label"},maxFileSize:{type:Number,attribute:"max-file-size"},maxFiles:{type:Number,attribute:"max-files"},deleteEnabled:{type:Boolean,attribute:"delete-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},downloadEnabled:{type:Boolean,attribute:"download-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},renameEnabled:{type:Boolean,attribute:"rename-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},displayLayout:{type:String,attribute:"display-layout"},fileTypeIcon:{type:String,attribute:"file-type-icon"},autoUpload:{type:Boolean,attribute:"auto-upload",converter:{fromAttribute:t=>{if(t==null)return!0;const i=String(t).toLowerCase().trim();return i!=="false"&&i!=="0"&&t!==!1}}},postType:{type:String,attribute:"post-type"},postId:{type:String,attribute:"post-id"},metaKey:{type:String,attribute:"meta-key"},keyPrefix:{type:String,attribute:"key-prefix"},uploading:{type:Boolean,state:!0},stagedFiles:{type:Array,state:!0},_uploadZoneExpanded:{type:Boolean,state:!0},_dragOver:{type:Boolean,state:!0},_editingFileKey:{type:String,state:!0},_editingFileName:{type:String,state:!0}}}connectedCallback(){super.connectedCallback(),this.addEventListener("dt:upload-files",this._handleUploadStagedEvent),this._boundKeydown=this._handleHostKeydown.bind(this)}disconnectedCallback(){var t;super.disconnectedCallback(),this.removeEventListener("dt:upload-files",this._handleUploadStagedEvent),this._removeKeydownListener(),this._cancelScheduledCollapse(),(t=this._resizeObserver)==null||t.disconnect()}_addKeydownListener(){this._keydownAttached||(this._keydownAttached=!0,this.addEventListener("keydown",this._boundKeydown,{capture:!0}))}_removeKeydownListener(){this._keydownAttached&&(this._keydownAttached=!1,this.removeEventListener("keydown",this._boundKeydown,{capture:!0}))}_handleHostKeydown(t){var o;if(!this._editingFileKey)return;const i=(o=this.shadowRoot)==null?void 0:o.querySelector(".file-name-edit");i&&(t.key==="Enter"||t.keyCode===13?(t.preventDefault(),t.stopPropagation(),t.stopImmediatePropagation(),this._commitRename(this._editingFileKey,i.value)):(t.key==="Escape"||t.keyCode===27)&&(t.preventDefault(),t.stopPropagation(),t.stopImmediatePropagation(),this._cancelRename()))}firstUpdated(t){Array.isArray(this.value)||(this.value=this._parseValue(this.value)),super.firstUpdated(t)}updated(t){super.updated(t),t.has("value")&&this._setFormValue(this.value),t.has("_editingFileKey")&&(this._editingFileKey?(this._addKeydownListener(),this.updateComplete.then(()=>{var o;const i=(o=this.shadowRoot)==null?void 0:o.querySelector(".file-name-edit");i&&(i.focus(),i.select())})):this._removeKeydownListener())}_expandUploadZone(){this._uploadZoneExpanded=!0}_scheduleCollapse(){this._cancelScheduledCollapse(),this._dragLeaveTimeout=setTimeout(()=>{this._uploadZoneExpanded=!1,this._dragLeaveTimeout=null},300)}_cancelScheduledCollapse(){this._dragLeaveTimeout&&(clearTimeout(this._dragLeaveTimeout),this._dragLeaveTimeout=null)}uploadStagedFiles(){this.stagedFiles.length>0&&this._uploadFiles(this.stagedFiles)}_removeStagedFile(t){t>=0&&t<this.stagedFiles.length&&(this.stagedFiles=this.stagedFiles.filter((i,o)=>o!==t),this.requestUpdate())}_parseValue(t){if(Array.isArray(t))return t;if(typeof t=="string")try{const i=JSON.parse(t);return Array.isArray(i)?i:[]}catch{return[]}return[]}_formatFileSize(t){return t<1024?`${t} B`:t<1024*1024?`${(t/1024).toFixed(1)} KB`:`${(t/(1024*1024)).toFixed(1)} MB`}_formatAcceptedTypes(){return this.acceptedFileTypesLabel?this.acceptedFileTypesLabel:(this.acceptedFileTypes||[]).join(", ")}_isImage(t){return(t.type||"").toLowerCase().startsWith("image/")}_mdiToIconify(t){if(!t||typeof t!="string")return"";const i=t.trim();return i.startsWith("mdi:")?i:i.includes("mdi-")?`mdi:${i.replace(/.*mdi-/,"").replace(/\s/g,"-")}`:i.startsWith("mdi ")?`mdi:${i.replace(/^mdi\s+/,"").replace(/\s/g,"-")}`:i}_getFileTypeIconMapping(){return{"application/pdf":"mdi:file-pdf-box","text/plain":"mdi:text-box-edit-outline","application/rtf":"mdi:text-box-edit-outline","text/rtf":"mdi:text-box-edit-outline","text/csv":"mdi:text-box-edit-outline","text/html":"mdi:language-html5","application/msword":"mdi:microsoft-word","application/json":"mdi:code-json","application/xml":"mdi:file-xml-box",".pdf":"mdi:file-pdf-box",".txt":"mdi:text-box-edit-outline",".rtf":"mdi:text-box-edit-outline",".csv":"mdi:text-box-edit-outline",".html":"mdi:language-html5",".htm":"mdi:language-html5",".docx":"mdi:microsoft-word",".doc":"mdi:microsoft-word",".json":"mdi:code-json",".xml":"mdi:file-xml-box"}}_getFileTypeIcon(t){if(this.fileTypeIcon&&this.fileTypeIcon.trim())return this.fileTypeIcon.trim();const i=(t.type||"").toLowerCase(),o=this._getFileTypeIconMapping();if(i&&o[i])return o[i];if(t.name){const a=t.name.split(".");if(a.length>1){const r="."+a.pop().toLowerCase();if(o[r])return o[r]}}return null}_renderFileTypeIcon(t){const i=this._getFileTypeIcon(t);if(!i)return null;if(/^(https?:|\/|data:)/.test(i))return h`<img src="${i}" alt="" />`;const a=this._mdiToIconify(i);return a?h`<dt-icon icon="${a}"></dt-icon>`:null}_getFilePreviewUrl(t){const i=t.thumbnail_key||t.large_thumbnail_key;if(this._isImage(t)){if(t.large_thumbnail_url)return t.large_thumbnail_url;if(t.thumbnail_url)return t.thumbnail_url;if(t.url)return t.url;if(i)return null}return null}_handleFileSelect(t){const i=Array.from(t.target.files||[]);i.length!==0&&(t.target.value="",this._processFiles(i))}_handleDrop(t){if(t.preventDefault(),t.stopPropagation(),this._dragOver=!1,t.currentTarget.classList.remove("drag-over"),this.disabled||this.uploading)return;const i=Array.from(t.dataTransfer.files||[]);i.length!==0&&this._processFiles(i)}_handleDragOver(t){t.preventDefault(),t.stopPropagation(),!this.disabled&&!this.uploading&&(this._dragOver=!0,this._expandUploadZone(),this._cancelScheduledCollapse(),t.currentTarget.classList.add("drag-over"))}_handleDragLeave(t){t.preventDefault(),t.stopPropagation(),this._dragOver=!1,t.currentTarget.classList.remove("drag-over"),this._scheduleCollapse()}_handleZoneClick(t){var i;if(!t.target.closest('input[type="file"]')&&(this._expandUploadZone(),this._cancelScheduledCollapse(),!this.disabled&&!this.uploading)){const o=(i=this.shadowRoot)==null?void 0:i.querySelector('input[type="file"]');o&&o.click()}}_handleZoneMouseEnter(){!this.disabled&&!this.uploading&&(this._expandUploadZone(),this._cancelScheduledCollapse())}_handleZoneMouseLeave(){this._scheduleCollapse()}_processFiles(t){const i=this._validateFiles(t);if(i.length===0)return;this.error="";const o=(this.value||[]).length+this.stagedFiles.length;if(this.maxFiles&&o+i.length>this.maxFiles){this.error=`${this.maxFiles} files allowed`;return}this.autoUpload?this._uploadFiles(i):(this.stagedFiles=[...this.stagedFiles,...i],this._uploadZoneExpanded=!1,this.requestUpdate())}_validateFiles(t){const i=[],o=this.maxFileSize?this.maxFileSize*1024*1024:null,a=Array.isArray(this.acceptedFileTypes)?this.acceptedFileTypes:["image/*","application/pdf"],r=a.join(",");for(const n of t){if(o&&n.size>o){this.error=`File "${n.name}" exceeds ${this.maxFileSize} MB`;continue}if(r&&r!=="*"&&!a.some(d=>{if(d.startsWith("."))return n.name.toLowerCase().endsWith(d.toLowerCase());if(d.endsWith("/*")){const u=d.slice(0,-2);return(n.type||"").startsWith(u)}return n.type===d||n.name&&n.name.toLowerCase().endsWith(`.${d.split("/")[1]}`)})){this.error=`File type not allowed: ${n.name}`;continue}i.push(n)}return i}_isStandaloneMode(){return!this.postType||!this.postId||!this.metaKey}async _filesToMockFileObjects(t){const i=[];for(const o of t){const r={key:`standalone_${Date.now()}_${Math.random().toString(36).slice(2)}_${o.name}`,name:o.name,type:o.type||"application/octet-stream",size:o.size};if(this._isImage({type:o.type}))try{const n=URL.createObjectURL(o);i.push({...r,url:n,thumbnail_url:n})}catch{i.push({...r,url:"#"})}else i.push({...r,url:"#"})}return i}getPendingFilesForUpload(){const t=[...this.stagedFiles||[]],i=new Set(t.map(a=>`${(a==null?void 0:a.name)||""}::${(a==null?void 0:a.size)||0}::${(a==null?void 0:a.lastModified)||0}`)),o=this._parseValue(this.value);for(const a of o){const r=String((a==null?void 0:a.key)||a||"");if(!r)continue;const n=this._standaloneFilesByKey.get(r);if(!n)continue;const l=`${(n==null?void 0:n.name)||""}::${(n==null?void 0:n.size)||0}::${(n==null?void 0:n.lastModified)||0}`;i.has(l)||(t.push(n),i.add(l))}return t}async _uploadFiles(t){if(this._isStandaloneMode()){const o=this._parseValue(this.value);this.uploading=!0,this.loading=!0,this.error="";try{const a=await this._filesToMockFileObjects(t);a.forEach((n,l)=>{n!=null&&n.key&&t[l]&&this._standaloneFilesByKey.set(String(n.key),t[l])});const r=[...o,...a];this.value=r,this.stagedFiles=[],this._uploadZoneExpanded=!1,this.saved=!0,this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:o,newValue:r}}))}catch(a){this.error=(a==null?void 0:a.message)||"Upload failed"}finally{this.uploading=!1,this.loading=!1}return}this.uploading=!0,this.loading=!0,this.error="";const i=new CustomEvent("dt:upload",{bubbles:!0,detail:{files:t,metaKey:this.metaKey,keyPrefix:this.keyPrefix||"",onSuccess:({result:o,fieldValue:a})=>{const r=this._parseValue(this.value);let n=r;const l=(o.uploaded_files||[]).filter(d=>d.uploaded&&d.file).map(d=>d.file);if(l.length>0){const d=new Set(r.map(p=>String(p.key||p))),u=[...r];for(const p of l){const g=String(p.key||p);d.has(g)||(u.push(p),d.add(g))}n=u,this.value=n}else Array.isArray(a)&&a.length>0&&(n=a,this.value=n);this.stagedFiles=[],this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:r,newValue:n}})),this._uploadZoneExpanded=!1,this.saved=!0,this.uploading=!1,this.loading=!1},onError:o=>{console.error("Upload error:",o),this.error=o.message||"Upload failed",this.uploading=!1,this.loading=!1}}});this.dispatchEvent(i)}async _deleteFile(t){if(!this.deleteEnabled||!confirm("Are you sure you want to delete this file?"))return;if(this._isStandaloneMode()){const o=this._parseValue(this.value),a=o.find(n=>(n.key||n)===t);a&&a.url&&a.url.startsWith("blob:")&&URL.revokeObjectURL(a.url),a&&a.thumbnail_url&&a.thumbnail_url.startsWith("blob:")&&a.thumbnail_url!==a.url&&URL.revokeObjectURL(a.thumbnail_url),this._standaloneFilesByKey.delete(String(t));const r=o.filter(n=>(n.key||n)!==t);this.value=r,this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:o,newValue:r}})),this.dispatchEvent(new CustomEvent("dt:delete-file",{bubbles:!0,detail:{fileKey:t,metaKey:this.metaKey||""}}));return}if(!this.postType||!this.postId||!this.metaKey)return;this.loading=!0,this.error="";const i=new CustomEvent("dt:delete-file",{bubbles:!0,detail:{fileKey:t,metaKey:this.metaKey,onSuccess:()=>{const o=this._parseValue(this.value),a=o.filter(r=>(r.key||r)!==t);this.value=a,this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:o,newValue:a}})),this.loading=!1},onError:o=>{console.error("Delete error:",o),this.error=o.message||"Delete failed",this.loading=!1}}});this.dispatchEvent(i)}async _renameFile(t,i){if(!this.renameEnabled)return;if(this._isStandaloneMode()){const a=this._parseValue(this.value),r=a.map(n=>(n.key||n)===t?{...n,name:i}:n);this.value=r,this._editingFileKey="",this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:a,newValue:r}})),this.dispatchEvent(new CustomEvent("dt:rename-file",{bubbles:!0,detail:{fileKey:t,newName:i,metaKey:this.metaKey||""}}));return}if(!this.postType||!this.postId||!this.metaKey)return;this.loading=!0,this.error="";const o=new CustomEvent("dt:rename-file",{bubbles:!0,detail:{fileKey:t,newName:i,metaKey:this.metaKey,onSuccess:()=>{const a=this._parseValue(this.value),r=a.map(n=>(n.key||n)===t?{...n,name:i}:n);this.value=r,this._editingFileKey="",this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:a,newValue:r}})),this.loading=!1},onError:a=>{console.error("Rename error:",a),this.error=(a==null?void 0:a.message)||"Rename failed",this.loading=!1}}});this.dispatchEvent(o)}_startRename(t,i){!this.renameEnabled||this.disabled||(this._editingFileKey=typeof t=="string"?t:String(t),this._editingFileName=i||"")}_commitRename(t,i){if(this._suppressRenameBlurCommit){this._suppressRenameBlurCommit=!1;return}if(!this._editingFileKey||this._editingFileKey!==t)return;const o=(i??this._editingFileName??"").trim();if(this._editingFileKey="",this._editingFileName="",!o)return;const r=this._parseValue(this.value).find(l=>(l.key||l)===t),n=(r==null?void 0:r.name)||(typeof t=="string"?t.split("/").pop():"");o!==n&&this._renameFile(t,o)}_cancelRename(){this._suppressRenameBlurCommit=!0,this._editingFileKey="",this._editingFileName="",setTimeout(()=>{this._suppressRenameBlurCommit=!1},0)}_handleRenameBlur(t,i){if(this._suppressRenameBlurCommit){this._suppressRenameBlurCommit=!1;return}this._commitRename(t,i)}_downloadFile(t){if(!this.downloadEnabled)return;if(this._isStandaloneMode()){const r=t.url;if(!r)return;const n=t.key||t,l=t.name||(typeof n=="string"?n.split("/").pop():"download")||"download",d=document.createElement("a");d.href=r,d.download=l,d.target="_blank",d.rel="noopener",document.body.appendChild(d),d.click(),document.body.removeChild(d),this.dispatchEvent(new CustomEvent("dt:download-file",{bubbles:!0,detail:{fileKey:n,fileName:l,metaKey:this.metaKey||""}}));return}const i=t.key||t,o=t.name||(typeof i=="string"?i.split("/").pop():"download")||"download",a=new CustomEvent("dt:download-file",{bubbles:!0,detail:{fileKey:i,fileName:o,metaKey:this.metaKey,onSuccess:()=>{},onError:r=>{console.error("Download error:",r),this.error=r.message||"Download failed"}}});this.dispatchEvent(a)}_validateRequired(){var i,o,a,r;const t=Array.isArray(this.value)?this.value:[];this.required&&t.length===0?(this.invalid=!0,(o=(i=this.internals)==null?void 0:i.setValidity)==null||o.call(i,{valueMissing:!0},this.requiredMessage||"This field is required")):(this.invalid=!1,(r=(a=this.internals)==null?void 0:a.setValidity)==null||r.call(a,{}))}labelTemplate(){if(!this.label)return"";let t=null;if(this.icon&&this.icon.trim()){const i=this.icon.trim();if(i.startsWith("http://")||i.startsWith("https://")||i.startsWith("/")||i.startsWith("data:"))t=h`<img
          src="${i}"
          alt="${this.iconAltText||""}"
        />`;else if(i.toLowerCase().includes("mdi")){const a=this._mdiToIconify(i);a&&(t=h`<dt-icon icon="${a}" size="1em"></dt-icon>`)}}return h`
      <dt-label
        ?private=${this.private}
        privateLabel="${le(this.privateLabel)}"
        iconAltText="${le(this.iconAltText)}"
        icon=""
        exportparts="label: label-container"
      >
        ${t?h`<span slot="icon-start">${t}</span>`:h`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    `}render(){const t=this._parseValue(this.value),o=(this.displayLayout||"grid")==="grid";return h`
      <div class="input-group">
        ${this.labelTemplate()}
        <div
          class="upload-zone ${A({compact:!this._uploadZoneExpanded,expanded:this._uploadZoneExpanded,disabled:this.disabled,"drag-over":this._dragOver,uploading:this.uploading})}"
          @click=${this._handleZoneClick}
          @mouseenter=${this._handleZoneMouseEnter}
          @mouseleave=${this._handleZoneMouseLeave}
          @dragover=${this._handleDragOver}
          @dragleave=${this._handleDragLeave}
          @drop=${this._handleDrop}
        >
          <input
            type="file"
            ?multiple=${!0}
            accept=${(this.acceptedFileTypes||[]).join(",")}
            @change=${this._handleFileSelect}
          />
          <div class="upload-zone-content">
            <span class="upload-icon"
              ><dt-icon icon="mdi:cloud-upload"></dt-icon
            ></span>
            <span class="expandable upload-text"
              >Drag files here or click to upload</span
            >
            <span class="expandable upload-hint"
              >${this._formatAcceptedTypes()}${this.maxFileSize?` • Max ${this.maxFileSize} MB`:""}</span
            >
          </div>
        </div>

        ${q(this.stagedFiles.length>0&&!this.autoUpload,()=>h`
            <div class="staged-files">
              <div class="staged-files-title">
                Staged files (${this.stagedFiles.length})
              </div>
              ${H(this.stagedFiles,(a,r)=>`${a.name}-${a.size}-${r}`,(a,r)=>h`
                  <div class="staged-file-item">
                    <span>${a.name} (${this._formatFileSize(a.size)})</span>
                    <button
                      class="remove"
                      type="button"
                      title="Remove"
                      @click=${n=>{n.stopPropagation(),this._removeStagedFile(r)}}
                    >
                      <dt-icon icon="mdi:trash-can"></dt-icon>
                    </button>
                  </div>
                `)}
              <button
                class="upload-staged-btn"
                type="button"
                ?disabled=${this.uploading}
                @click=${()=>this.uploadStagedFiles()}
              >
                Upload
              </button>
            </div>
          `)}
        ${q(this.loading||this.saved,()=>h`
            <div class="status-indicators">
              ${this.renderIconLoading()} ${this.renderIconSaved()}
            </div>
          `)}
        ${q(t.length>0,()=>h`
            <div class="files-container">
              <div class=${o?"files-grid":"files-list"}>
                ${H(t,a=>a.key||a,a=>{const r=typeof a.key=="string"?a.key:typeof a=="string"?a:String(a.key??a.name??""),n=a.name||(typeof r=="string"?r.split("/").pop():""),l=a.size,d=this._getFilePreviewUrl(a),u=this._isImage(a),p=this._editingFileKey===r;return h`
                      <div
                        class="file-item ${o?"file-item-grid":"file-item-list"}"
                      >
                        ${q(d,()=>h`
                            <a
                              class="file-preview-link"
                              href=${d||a.url||"#"}
                              target="_blank"
                              rel="noopener"
                              @click=${g=>{!d&&!a.url&&g.preventDefault()}}
                            >
                              <img
                                src="${d}"
                                alt="${n}"
                                loading="lazy"
                              />
                            </a>
                          `,()=>h`
                            ${a.url?h`
                                  <a
                                    class="file-preview-link file-icon-area"
                                    href=${a.url}
                                    target="_blank"
                                    rel="noopener"
                                  >
                                    ${this._renderFileTypeIcon(a)||(u?h`<dt-icon
                                          icon="mdi:image"
                                        ></dt-icon>`:h`<dt-icon
                                          icon="mdi:file-outline"
                                        ></dt-icon>`)}
                                  </a>
                                `:h`
                                  <div class="file-icon-area">
                                    ${this._renderFileTypeIcon(a)||(u?h`<dt-icon
                                          icon="mdi:image"
                                        ></dt-icon>`:h`<dt-icon
                                          icon="mdi:file-outline"
                                        ></dt-icon>`)}
                                  </div>
                                `}
                          `)}
                        ${q(p,()=>h`
                            <input
                              class="file-name-edit"
                              type="text"
                              .value=${this._editingFileName}
                              @input=${g=>{this._editingFileName=g.target.value}}
                              @keydown=${g=>{g.key==="Enter"||g.keyCode===13?(g.preventDefault(),g.stopPropagation(),this._commitRename(r,g.target.value)):(g.key==="Escape"||g.keyCode===27)&&(g.preventDefault(),this._cancelRename())}}
                              @blur=${g=>this._handleRenameBlur(r,g.target.value)}
                              @click=${g=>g.stopPropagation()}
                            />
                          `,()=>h`
                            <div
                              class="file-name ${this.renameEnabled&&!this.disabled?"file-name-editable":""}"
                              role=${this.renameEnabled&&!this.disabled?"button":void 0}
                              tabindex=${this.renameEnabled&&!this.disabled?0:void 0}
                              @click=${g=>{g.stopPropagation(),this.renameEnabled&&!this.disabled&&this._startRename(r,n)}}
                              @keydown=${g=>{this.renameEnabled&&!this.disabled&&(g.key==="Enter"||g.key===" ")&&(g.preventDefault(),this._startRename(r,n))}}
                            >
                              ${n}
                            </div>
                          `)}
                        ${q(l!=null,()=>h`<div class="file-size">
                              ${this._formatFileSize(l)}
                            </div>`)}
                        <div class="file-actions">
                          ${q(this.downloadEnabled&&a.url,()=>h`
                              <button
                                class="download"
                                type="button"
                                @click=${g=>{g.stopPropagation(),this._downloadFile(a)}}
                                title="Download"
                              >
                                <dt-icon icon="mdi:cloud-download"></dt-icon>
                              </button>
                            `)}
                          ${q(this.deleteEnabled&&!this.disabled,()=>h`
                              <button
                                class="delete"
                                type="button"
                                @click=${g=>{g.stopPropagation(),this._deleteFile(r)}}
                                title="Delete"
                              >
                                <dt-icon icon="mdi:trash-can"></dt-icon>
                              </button>
                            `)}
                        </div>
                      </div>
                    `})}
              </div>
            </div>
          `)}
        ${this.renderIconInvalid()} ${this.renderError()}
      </div>
    `}}customElements.define("dt-file-upload",Eo);class Ao extends N{static get styles(){return x`
      :host {
        display: block;
      }

      .dt-alert {
        padding: var(--dt-alert-padding, 10px);
        font-family: var(--dt-alert-font-family);
        font-size: var(--dt-alert-font-size, 14px);
        font-weight: var(--dt-alert-font-weight, 700);
        background-color: var(
          --dt-alert-context-background-color,
          var(--dt-alert-background-color)
        );
        border: var(--dt-alert-border-width, 1px) solid
          var(--dt-alert-context-border-color, var(--dt-alert-border-color));
        border-radius: var(--dt-alert-border-radius, 10px);
        box-shadow: var(--dt-alert-box-shadow, 0 2px 4px rgb(0 0 0 / 25%));
        color: var(--dt-alert-context-text-color, var(--dt-alert-text-color));
        text-rendering: optimizeLegibility;
        display: flex;
        gap: var(--dt-alert-gap, 10px);
        justify-content: space-between;
        align-content: center;
        align-items: center;
        white-space: initial;
      }

      .dt-alert.dt-alert--outline {
        background-color: transparent;
        color: var(--dt-alert-context-text-color, var(--text-color-inverse));
      }

      .dt-alert--primary:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--primary-color);
        --dt-alert-context-background-color: var(--primary-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-light);
      }

      .dt-alert--alert:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--alert-color);
        --dt-alert-context-background-color: var(--alert-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-light);
      }

      .dt-alert--caution:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--caution-color);
        --dt-alert-context-background-color: var(--caution-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-dark);
      }

      .dt-alert--success:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--success-color);
        --dt-alert-context-background-color: var(--success-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-light);
      }

      .dt-alert--inactive:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--inactive-color);
        --dt-alert-context-background-color: var(--inactive-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-light);
      }

      .dt-alert--disabled:not(.dt-alert--outline) {
        --dt-alert-context-border-color: var(--disabled-color);
        --dt-alert-context-background-color: var(--disabled-color);
        --dt-alert-context-text-color: var(--dt-alert-text-color-dark);
      }

      .dt-alert--primary.dt-alert--outline {
        --dt-alert-context-border-color: var(--primary-color);
        --dt-alert-context-text-color: var(--primary-color);
      }

      .dt-alert--alert.dt-alert--outline {
        --dt-alert-context-border-color: var(--alert-color);
        --dt-alert-context-text-color: var(--alert-color);
      }

      .dt-alert--caution.dt-alert--outline {
        --dt-alert-context-border-color: var(--caution-color);
        --dt-alert-context-text-color: var(--caution-color);
      }

      .dt-alert--success.dt-alert--outline {
        --dt-alert-context-border-color: var(--success-color);
        --dt-alert-context-text-color: var(--success-color);
      }

      .dt-alert--inactive.dt-alert--outline {
        --dt-alert-context-border-color: var(--inactive-color);
      }

      .dt-alert--disabled.dt-alert--outline {
        --dt-alert-context-border-color: var(--disabled-color);
      }

      button.toggle {
        margin-inline-end: 0;
        margin-inline-start: auto;
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
      }
    `}static get properties(){return{context:{type:String},dismissable:{type:Boolean},timeout:{type:Number},hide:{type:Boolean},outline:{type:Boolean}}}get classes(){const e={"dt-alert":!0,"dt-alert--outline":this.outline},t=`dt-alert--${this.context}`;return e[t]=!0,e}constructor(){super(),this.context="default"}connectedCallback(){super.connectedCallback(),this.timeout&&setTimeout(()=>{this._dismiss()},this.timeout)}_dismiss(){this.hide=!0}render(){if(this.hide)return h``;const e=h`
      <svg viewPort="0 0 12 12" version="1.1" width='12' height='12'>
           xmlns="http://www.w3.org/2000/svg">
        <line x1="1" y1="11"
              x2="11" y2="1"
              stroke="currentColor"
              stroke-width="2"/>
        <line x1="1" y1="1"
              x2="11" y2="11"
              stroke="currentColor"
              stroke-width="2"/>
      </svg>
    `;return h`
      <div role="alert" class=${A(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable?h`
              <button @click="${this._dismiss}" class="toggle">${e}</button>
            `:null}
      </div>
    `}}window.customElements.define("dt-alert",Ao);class To extends N{static get styles(){return x`
      :host {
        font-family: var(--dt-tile-font-family, var(--font-family));
        font-size: var(--dt-tile-font-size, 14px);
        font-weight: var(--dt-tile-font-weight, 700);
        overflow: hidden;
        text-overflow: ellipsis;
      }

      section {
        background-color: var(
          --dt-tile-background-color,
          var(--surface-1, #fefefe)
        );
        border-top: var(
          --dt-tile-border-top,
          1px solid var(--dt-tile-border-color, var(--border-color))
        );
        border-bottom: var(
          --dt-tile-border-bottom,
          1px solid var(--dt-tile-border-color, var(--border-color))
        );
        border-right: var(
          --dt-tile-border-right,
          1px solid var(--dt-tile-border-color, var(--border-color))
        );
        border-left: var(
          --dt-tile-border-left,
          1px solid var(--dt-tile-border-color, var(--border-color))
        );
        border-radius: var(--dt-tile-border-radius, 10px);
        box-shadow: var(--dt-tile-box-shadow, var(--shadow-0));
        padding: 1rem;
        margin: var(--dt-tile-margin, 0);
      }

      h3 {
        line-height: 1.4;
        margin: var(--dt-tile-header-margin, 0 0 0.5rem 0);
        text-rendering: optimizeLegibility;
        font-family: var(--dt-tile-font-family, var(--font-family));
        font-style: normal;
        font-weight: var(--dt-tile-header-font-weight, 300);
      }

      .section-header {
        color: var(--dt-tile-header-color, var(--primary-color));
        font-size: 1.5rem;
        display: flex;
        text-transform: var(--dt-tile-header-text-transform, capitalize);
        justify-content: var(--dt-tile-header-justify-content);
      }

      .section-body {
        display: grid;
        grid-template-columns: var(
          --dt-tile-body-grid-template-columns,
          repeat(auto-fill, minmax(200px, 1fr))
        );
        transition: height 1s ease 0s;
        gap: var(--dt-tile-body-grid-gap, 1rem 1.4rem);
        height: auto;
      }
      .section-body.collapsed {
        height: 0 !important;
        overflow: hidden;
      }

      button.toggle {
        margin-inline-end: 0;
        margin-inline-start: auto;
        background: none;
        border: none;
      }

      .chevron::before {
        border-color: var(--dt-tile-header-color, var(--primary-color));
        border-style: solid;
        border-width: 2px 2px 0 0;
        content: '';
        display: inline-block;
        height: 1em;
        width: 1em;
        left: 0.15em;
        position: relative;
        top: 0.15em;
        transform: rotate(-45deg);
        vertical-align: top;
      }

      .chevron.down:before {
        top: 0;
        transform: rotate(135deg);
      }
    `}static get properties(){return{title:{type:String},expands:{type:Boolean},collapsed:{type:Boolean},gap:{type:String}}}get hasHeading(){return this.title||this.expands}_toggle(){this.collapsed=!this.collapsed}renderHeading(){return this.hasHeading?h`
      <h3 class="section-header">
        ${this.title}
        ${this.expands?h`
              <button
                @click="${this._toggle}"
                class="toggle chevron ${this.collapsed?"down":"up"}"
              >
                &nbsp;
              </button>
            `:null}
      </h3>
    `:O}render(){return h`
      <section>
        ${this.renderHeading()}
        <div
          part="body"
          class="section-body ${this.collapsed?"collapsed":null}"
          style="${this.gap?`gap: ${this.gap};`:null}"
        >
          <slot></slot>
        </div>
      </section>
    `}}window.customElements.define("dt-tile",To);class Ie{get api(){return this._api}constructor(e,t,i,o="wp-json"){this.postType=e,this.postId=t,this.nonce=i,this.debounceTimers={},this._api=new yi(this.nonce,o),this.apiRoot=this._api.apiRoot,this.autoSaveComponents=["dt-connection","dt-users-connection","dt-date","dt-datetime","dt-location","dt-location-map","dt-multi-select","dt-number","dt-single-select","dt-tags","dt-text","dt-textarea","dt-toggle","dt-multi-text","dt-multi-text-groups","dt-multi-select-button-group","dt-button","dt-church-health-circle"],this.dynamicLoadComponents=["dt-connection","dt-tags","dt-modal","dt-button","dt-location","dt-users-connection"]}initialize(){this.postId&&this.enableAutoSave(),this.attachLoadEvents(),this.attachFileUploadEvents()}async attachLoadEvents(e){const t=document.querySelectorAll(e||this.dynamicLoadComponents.join(","));t&&t.forEach(i=>{i.dataset.eventDtGetData||(i.addEventListener("dt:get-data",this.handleGetDataEvent.bind(this)),i.dataset.eventDtGetData=!0)})}async checkDuplicates(e,t){const i=document.querySelector("dt-modal.duplicate-detected");if(i){const o=i.shadowRoot.querySelector(".duplicates-detected-button");o&&(o.style.display="none");const a=await this._api.checkDuplicateUsers(this.postType,this.postId);t&&a.ids.length>0&&o&&(o.style.display="block")}}enableAutoSave(e){const t=document.querySelectorAll(e||this.autoSaveComponents.join(","));t&&t.forEach(i=>{i.addEventListener("change",this.handleChangeEvent.bind(this))})}attachFileUploadEvents(e){const t=document.querySelectorAll(e||"dt-file-upload");t&&t.forEach(i=>{i.dataset.eventDtUpload||(i.addEventListener("dt:upload",this.handleUploadEvent.bind(this)),i.addEventListener("dt:delete-file",this.handleDeleteFileEvent.bind(this)),i.addEventListener("dt:rename-file",this.handleRenameFileEvent.bind(this)),i.addEventListener("dt:download-file",this.handleDownloadFileEvent.bind(this)),i.dataset.eventDtUpload=!0)})}async handleGetDataEvent(e){const t=e.detail;if(t){const{field:i,query:o,onSuccess:a,onError:r}=t;try{const n=e.target.tagName.toLowerCase();let l=[];switch(n){case"dt-button":l=await this._api.getContactInfo(this.postType,this.postId);break;case"dt-connection":{const d=t.postType||this.postType,u=await this._api.listPostsCompact(d,o),p={...u,posts:u.posts.filter(g=>g.ID!==parseInt(this.postId,10))};p!=null&&p.posts&&(l=Ie.convertApiValue("dt-connection",p==null?void 0:p.posts));break}case"dt-users-connection":{const d=t.postType||this.postType,u=await this._api.searchUsers(d,o),p={...u,posts:u.filter(g=>g.ID!==parseInt(this.postId,10))};p!=null&&p.posts&&(l=Ie.convertApiValue("dt-users-connection",p==null?void 0:p.posts));break}case"dt-location":{l=await this._api.getLocations(this.postType,i,t.filter,o),l=l.location_grid.map(d=>({id:d.ID,label:d.name}));break}case"dt-tags":default:l=await this._api.getMultiSelectValues(this.postType,i,o),l=l.map(d=>({id:d,label:d}));break}a(l)}catch(n){r(n)}}}async handleChangeEvent(e){const t=e.detail;if(t){const{field:i,newValue:o,oldValue:a,remove:r}=t,n=e.target.tagName.toLowerCase(),l=Ie.convertValue(n,o,a);if(e.target.removeAttribute("saved"),e.target.setAttribute("loading",!0),n==="dt-number"){const d=`${this.postType}-${this.postId}-${i}`;this.debounce(d,async()=>{try{const u=await this._api.updatePost(this.postType,this.postId,{[i]:l});document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:u,field:i,value:l,component:n}})),e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(u){console.error(u),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",u.message||u.toString())}},1e3)}else try{const d={[i]:l};if(n==="dt-location-map"){const p=l.values.filter(g=>!g.lng||!g.lat);d[i].values=l.values.filter(g=>g.lng&&g.lat),d.contact_address=p,d.contact_address.length===0&&delete d.contact_address,d[i].values.length===0&&delete d[i]}const u=await this._api.updatePost(this.postType,this.postId,d);if(document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:u,field:i,value:l,component:n}})),n==="dt-location-map"||n==="dt-multi-text-groups"){const p=e.target;p.value=u[i]}e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(d){console.error(d),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",d.message||d.toString())}}}async handleUploadEvent(e){const t=e.detail;if(!t)return;const{files:i,metaKey:o,keyPrefix:a,onSuccess:r,onError:n}=t,l=e.target;l.setAttribute("loading",!0),l.removeAttribute("saved"),l.removeAttribute("error");try{const d=await this._api.uploadFiles(this.postType,this.postId,i,o,a||""),p=(await this._api.getPost(this.postType,this.postId))[o]||[];r&&r({result:d,fieldValue:p})}catch(d){l.setAttribute("error",d.message||"Upload failed"),n&&n(d)}finally{l.removeAttribute("loading")}}async handleDeleteFileEvent(e){const t=e.detail;if(!t)return;const{fileKey:i,metaKey:o,onSuccess:a,onError:r}=t,n=e.target;n.setAttribute("loading",!0),n.removeAttribute("saved"),n.removeAttribute("error");try{await this._api.deleteFile(this.postType,this.postId,o,i),a&&a()}catch(l){n.setAttribute("error",l.message||"Delete failed"),r&&r(l)}finally{n.removeAttribute("loading")}}async handleRenameFileEvent(e){const t=e.detail;if(!t)return;const{fileKey:i,newName:o,metaKey:a,onSuccess:r,onError:n}=t,l=e.target;l.setAttribute("loading",!0),l.removeAttribute("saved"),l.removeAttribute("error");try{const d=await this._api.renameFile(this.postType,this.postId,a,i,o);r&&r(d)}catch(d){l.setAttribute("error",d.message||"Rename failed"),n&&n(d)}finally{l.removeAttribute("loading")}}async handleDownloadFileEvent(e){const t=e.detail;if(!t)return;const{fileKey:i,fileName:o,metaKey:a,onSuccess:r,onError:n}=t,l=e.target;try{const d=await this._api.downloadFile(this.postType,this.postId,a,i),u=window.URL.createObjectURL(d),p=document.createElement("a");p.href=u,p.download=o||"download",document.body.appendChild(p),p.click(),document.body.removeChild(p),window.URL.revokeObjectURL(u),r&&r()}catch(d){l.setAttribute("error",d.message||"Download failed"),n&&n(d)}}debounce(e,t,i){this.debounceTimers[e]&&clearTimeout(this.debounceTimers[e]),this.debounceTimers[e]=setTimeout(()=>{t()},i)}static convertApiValue(e,t){let i=t;switch(e){case"dt-connection":i=t.map(o=>({id:o.ID,label:o.name??o.post_title,link:o.permalink,status:o.status}));break;case"dt-users-connection":t&&!Array.isArray(t)&&(t.id||t.ID)?i=[{id:t.id||t.ID,label:t.display,avatar:t.avatar||""}]:Array.isArray(t)&&(i=t.map(o=>({id:o.id||o.ID,label:o.display||o.name,avatar:o.avatar||""})));break}return i}static convertValue(e,t,i=null){let o=t;if(t)switch(e.toLowerCase()){case"dt-toggle":typeof t=="string"&&(o=t.toLowerCase()==="true");break;case"dt-church-health-circle":case"dt-multi-select":case"dt-multi-select-button-group":case"dt-tags":typeof t=="string"&&(o=[t]),o={values:o.map(n=>{if(typeof n=="string"){const d={value:n};return n.startsWith("-")&&(d.delete=!0,d.value=n.substring(1)),d}const l={value:n.id};return n.delete&&(l.delete=n.delete),l}),force_values:!1};break;case"dt-users-connection":{const n=[],l=o.filter(u=>!u.delete);if(l.length<=1){o=l.length===1?parseInt(l[0].id,10):"";break}const d=new Map((i||[]).map(u=>[u.id,u]));for(const u of o){const p=d.get(u.id),g={id:u.id,changes:{}};if(p){let y=!1;const w=new Set([...Object.keys(p),...Object.keys(u)]);for(const T of w)u[T]!==p[T]&&(g.changes[T]=Object.prototype.hasOwnProperty.call(u,T)?u[T]:void 0,y=!0);if(y){n.push(g);break}}else{g.changes={...u},n.push(g);break}}o=n[0].id;break}case"dt-connection":typeof t=="string"&&(o=[{id:t}]),o={values:o.map(n=>{const l={value:n.id};return n.delete&&(l.delete=n.delete),l}),force_values:!1};break;case"dt-location":const a=new Set((i||[]).map(n=>n.id));typeof t=="string"?o=[{id:t}]:o=t.filter(n=>!(a.has(n.id)&&!n.delete)),o={values:o.map(n=>{const l={value:n.id};return n.delete&&(l.delete=n.delete),l}),force_values:!1};break;case"dt-location-map":if(o=t.filter(n=>!((i||[]).includes(n)&&!n.delete)),i)for(const n of i)t.some(d=>n.id&&d.id&&n.id===d.id||n.key&&d.key&&n.key===d.key&&(!d.lat||!d.lng))||(n.delete=!0,o.push(n));o={values:o.map(n=>{const l=n;return n.delete&&(l.delete=n.delete),l}),force_values:!1};break;case"dt-multi-text":Array.isArray(t)?o=t.map(n=>{const l={...n};return delete l.tempKey,l}):typeof t=="string"&&(o=[{value:t}]);break;case"dt-multi-text-groups":let r=[];Array.isArray(t)?r=t.filter(n=>n.value!=="").map(n=>{const l={...n};return delete l.tempKey,l}):typeof t=="string"&&(r=[{value:t}]),o={values:r,force_values:!1};break}return o}static valueArrayDiff(e,t){const i={value1:[],value2:[]};if(Array.isArray(e)||(e=[]),Array.isArray(t)||(t=[]),e.length>0&&typeof e[0]!="object")return i.value1=e.filter(n=>!t.includes(n)),i.value2=t.filter(n=>!e.includes(n)),i;const o=n=>JSON.stringify(n),a=new Map(e.map(n=>[o(n),n])),r=new Map(t.map(n=>[o(n),n]));for(const[n,l]of a)r.has(n)||i.value1.push(l);for(const[n,l]of r)a.has(n)||i.value2.push(l);return i}}const Va="0.8.12",Ba={s226be12a5b1a27e8:"ሰነዶቹን ያንብቡ",s33f85f24c0f5f008:"አስቀምጥ",s36cb242ac90353bc:"መስኮች",s41cb4006238ebd3b:"የጅምላ አርትዕ",s5e8250fb85d64c23:"ገጠመ",s625ad019db843f94:"ተጠቀም",sac83d7f9358b43db:m`${0} ዝርዝር`,sbf1ca928ec1deb62:"ተጨማሪ እገዛ ይፈልጋሉ?",sd1a8dc951b2b6a98:"በዝርዝሩ ውስጥ እንደ ዓምዶች የትኞቹን መስኮች እንደሚያሳዩ ይምረጡ",sf9aee319a006c9b4:"አክል",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ha=Object.freeze(Object.defineProperty({__proto__:null,templates:Ba},Symbol.toStringTag,{value:"Module"})),Ka={s04ceadb276bbe149:"خيارات التحميل...",s226be12a5b1a27e8:"اقرأ الوثائق",s29e25f5e4622f847:"افتح",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"غلق",s625ad019db843f94:"استخدام",s9d51bfd93b5dbeca:"عرض المحفوظات",sac83d7f9358b43db:m`${0}قائمة الأعضاء`,sb1bd536b63e9e995:"المجال الخاص: أنا فقط أستطيع رؤية محتواه",sb59d68ed12d46377:"جار التحميل",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",scb9a1ff437efbd2a:m`حَدِّد جميع ${0} التي تريد تحديثها من القائمة ، وقم بتحديثها أدناه`,sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",seafe6ef133ede7da:m`عرض 1 of ${0}`,sf9aee319a006c9b4:"لأضف",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ga=Object.freeze(Object.defineProperty({__proto__:null,templates:Ka},Symbol.toStringTag,{value:"Module"})),Wa={s226be12a5b1a27e8:"اقرأ الوثائق",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"أغلق",s625ad019db843f94:"استخدام",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",sf9aee319a006c9b4:"إضافة",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Za=Object.freeze(Object.defineProperty({__proto__:null,templates:Wa},Symbol.toStringTag,{value:"Module"})),Ja={s226be12a5b1a27e8:"Прочетете документацията",s33f85f24c0f5f008:"Запазете",s36cb242ac90353bc:"Полета",s41cb4006238ebd3b:"Групово редактиране",s5e8250fb85d64c23:"Близо",s625ad019db843f94:"Използвайте",sbf1ca928ec1deb62:"Имате нужда от повече помощ?",sd1a8dc951b2b6a98:"Изберете кои полета да се показват като колони в списъка",sf9aee319a006c9b4:"Добавяне",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Qa=Object.freeze(Object.defineProperty({__proto__:null,templates:Ja},Symbol.toStringTag,{value:"Module"})),Xa={s226be12a5b1a27e8:"নথিপত্রাদি পাঠ করুন",s33f85f24c0f5f008:"সংরক্ষণ করুন",s36cb242ac90353bc:"ক্ষেত্র",s41cb4006238ebd3b:"বাল্ক এডিট",s5e8250fb85d64c23:"বন্ধ",s625ad019db843f94:"ব্যবহার",sbf1ca928ec1deb62:"আরও সাহায্য প্রয়োজন?",sd1a8dc951b2b6a98:"তালিকার কলাম হিসাবে কোন ক্ষেত্রগুলি প্রদর্শিত হবে তা চয়ন করুন",sf9aee319a006c9b4:"অ্যাড",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ya=Object.freeze(Object.defineProperty({__proto__:null,templates:Xa},Symbol.toStringTag,{value:"Module"})),er={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitajte dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:m`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate više pomoći?",scb9a1ff437efbd2a:m`Odaberite sve ${0} koje želite ažurirati sa liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Odaberite koja polja će se prikazati kao kolone na listi",seafe6ef133ede7da:m`Prikazuje se 1 od ${0}`,sf9aee319a006c9b4:"Dodati",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},tr=Object.freeze(Object.defineProperty({__proto__:null,templates:er},Symbol.toStringTag,{value:"Module"})),ir={s226be12a5b1a27e8:"Přečtěte si dokumentaci",s33f85f24c0f5f008:"Uložit",s36cb242ac90353bc:"Pole",s41cb4006238ebd3b:"Hromadná úprava",s5e8250fb85d64c23:"Zavřít",s625ad019db843f94:"Použití",sbf1ca928ec1deb62:"Potřebujete další pomoc?",sd1a8dc951b2b6a98:"Vyberte pole, která chcete v seznamu zobrazit jako sloupce",sf9aee319a006c9b4:"Přidat",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},or=Object.freeze(Object.defineProperty({__proto__:null,templates:ir},Symbol.toStringTag,{value:"Module"})),sr={s226be12a5b1a27e8:"Lesen Sie die Dokumentation",s33f85f24c0f5f008:"Speichern",s36cb242ac90353bc:"Felder",s41cb4006238ebd3b:"Im Stapel bearbeiten",s5e8250fb85d64c23:"Schließen",s625ad019db843f94:"Verwenden",sbf1ca928ec1deb62:"Benötigen Sie weitere Hilfe?",sd1a8dc951b2b6a98:"Wählen Sie aus, welche Felder in der Liste als Spalte angezeigt werden sollen",sf9aee319a006c9b4:"Hinzufügen",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ar=Object.freeze(Object.defineProperty({__proto__:null,templates:sr},Symbol.toStringTag,{value:"Module"})),rr={s226be12a5b1a27e8:"Διαβάστε την τεκμηρίωση",s33f85f24c0f5f008:"Αποθήκευση",s36cb242ac90353bc:"Πεδία",s41cb4006238ebd3b:"Μαζική Επεξεργασία",s5e8250fb85d64c23:"Κλείσιμο",s625ad019db843f94:"Χρήση",sbf1ca928ec1deb62:"Χρειάζεστε περισσότερη βοήθεια;",sd1a8dc951b2b6a98:"Επιλέξτε ποια πεδία θα εμφανίζονται ως στήλες στη λίστα",sf9aee319a006c9b4:"Προσθήκη",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nr=Object.freeze(Object.defineProperty({__proto__:null,templates:rr},Symbol.toStringTag,{value:"Module"})),lr={sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",sf9aee319a006c9b4:"Add",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog",s33f85f24c0f5f008:"Save",s49730f3d5751a433:"Loading...",s625ad019db843f94:"Use",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},dr=Object.freeze(Object.defineProperty({__proto__:null,templates:lr},Symbol.toStringTag,{value:"Module"})),cr={s8900c9de2dbae68b:"No hay opciones disponibles",sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sf9aee319a006c9b4:"Añadir",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sb9b8c412407d5691:"This is where the bulk edit form will go.",sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog"},ur=Object.freeze(Object.defineProperty({__proto__:null,templates:cr},Symbol.toStringTag,{value:"Module"})),hr={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Leer la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:m`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:m`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:m`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},pr=Object.freeze(Object.defineProperty({__proto__:null,templates:hr},Symbol.toStringTag,{value:"Module"})),fr={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Lee la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:m`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:m`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:m`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},br=Object.freeze(Object.defineProperty({__proto__:null,templates:fr},Symbol.toStringTag,{value:"Module"})),mr={s04ceadb276bbe149:"در حال بارگیری گزینه‌ها...",s226be12a5b1a27e8:"راهنمای سایت",s29e25f5e4622f847:"جعبه محاوره ای را باز کنید",s33f85f24c0f5f008:"صرفه جویی",s36cb242ac90353bc:"حوزه‌ها",s41cb4006238ebd3b:"ویرایش انبوه",s5e8250fb85d64c23:"بستن",s625ad019db843f94:"استفاده کنید",s9d51bfd93b5dbeca:"نمایش بایگانی شده",sac83d7f9358b43db:m`لیست ${0}`,sb1bd536b63e9e995:"زمینه خصوصی: فقط من می توانم محتوای آن را داشته باشم",sb59d68ed12d46377:"بارگیری",sbf1ca928ec1deb62:"آیا به راهنمایی بیشتری نیاز دارید؟",scb9a1ff437efbd2a:m`همۀ ${0} مورد نظر برای به روزرسانی را از لیست انتخاب کنید و آن‌ها را در زیر به روز کنید`,sd1a8dc951b2b6a98:"انتخاب کنید که کدام یک از حوزه‌ها به‌عنوان ستون در لیست نمایش داده شوند",seafe6ef133ede7da:m`نمایش 1 از ${0}`,sf9aee319a006c9b4:"افزودن",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},gr=Object.freeze(Object.defineProperty({__proto__:null,templates:mr},Symbol.toStringTag,{value:"Module"})),vr={s04ceadb276bbe149:"Chargement les options...",s226be12a5b1a27e8:"Lire la documentation",s29e25f5e4622f847:"Ouvrir la boîte de dialogue",s33f85f24c0f5f008:"sauver",s36cb242ac90353bc:"Champs",s41cb4006238ebd3b:"Modification groupée",s5e8250fb85d64c23:"Fermer",s625ad019db843f94:"Utiliser",s9d51bfd93b5dbeca:"Afficher Archivé",sac83d7f9358b43db:m`${0} Liste`,sb1bd536b63e9e995:"Champ privé : je suis le seul à voir son contenu",sb59d68ed12d46377:"Chargement",sbf1ca928ec1deb62:"Besoin d'aide ?",scb9a1ff437efbd2a:m`Sélectionnez tous les ${0} que vous souhaitez mettre à jour dans la liste et mettez-les à jour ci-dessous`,sd1a8dc951b2b6a98:"Choisissez les champs à afficher sous forme de colonnes dans la liste",seafe6ef133ede7da:m`Affichage de 1 sur ${0}`,sf9aee319a006c9b4:"Ajouter",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},yr=Object.freeze(Object.defineProperty({__proto__:null,templates:vr},Symbol.toStringTag,{value:"Module"})),_r={s226be12a5b1a27e8:"डॉक्यूमेंटेशन पढ़ें",s33f85f24c0f5f008:"बचाना",s36cb242ac90353bc:"खेत",s41cb4006238ebd3b:"थोक संपादित",s5e8250fb85d64c23:"बंद",s625ad019db843f94:"उपयोग",sbf1ca928ec1deb62:"क्या और मदद चाहिये?",sd1a8dc951b2b6a98:"सूची में कॉलम के रूप में प्रदर्शित करने के लिए कौन से फ़ील्ड चुनें",sf9aee319a006c9b4:"जोडें",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},wr=Object.freeze(Object.defineProperty({__proto__:null,templates:_r},Symbol.toStringTag,{value:"Module"})),$r={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitaj dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Spremi",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvoriti",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:m`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate li pomoć?",scb9a1ff437efbd2a:m`Odaberite sve${0}koje želite ažurirati s liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Izaberite polja koja će se prikazivati kao stupci na popisu",seafe6ef133ede7da:m`Prikazuje se 1 od${0}`,sf9aee319a006c9b4:"Dodaj",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},xr=Object.freeze(Object.defineProperty({__proto__:null,templates:$r},Symbol.toStringTag,{value:"Module"})),kr={s226be12a5b1a27e8:"Olvasd el a dokumentációt",s33f85f24c0f5f008:"Megment",s36cb242ac90353bc:"Mezők",s41cb4006238ebd3b:"Tömeges Szerkesztés",s5e8250fb85d64c23:"Bezár",s625ad019db843f94:"Használ",sbf1ca928ec1deb62:"Több segítség szükséges?",sd1a8dc951b2b6a98:"Válassza ki, melyik mezők jelenjenek meg oszlopként a listában",sf9aee319a006c9b4:"Hozzáadás",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Sr=Object.freeze(Object.defineProperty({__proto__:null,templates:kr},Symbol.toStringTag,{value:"Module"})),Er={s226be12a5b1a27e8:"Bacalah dokumentasi",s33f85f24c0f5f008:"Simpan",s36cb242ac90353bc:"Larik",s41cb4006238ebd3b:"Edit Massal",s5e8250fb85d64c23:"Menutup",s625ad019db843f94:"Gunakan",sbf1ca928ec1deb62:"Perlukan bantuan lagi?",sd1a8dc951b2b6a98:"Pilih larik mana yang akan ditampilkan sebagai kolom dalam daftar",sf9aee319a006c9b4:"Tambah",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ar=Object.freeze(Object.defineProperty({__proto__:null,templates:Er},Symbol.toStringTag,{value:"Module"})),Tr={s04ceadb276bbe149:"Caricando opzioni...",s226be12a5b1a27e8:"Leggi la documentazione",s29e25f5e4622f847:"Apri Dialogo",s33f85f24c0f5f008:"Salvare",s36cb242ac90353bc:"Campi",s41cb4006238ebd3b:"Modifica in blocco",s5e8250fb85d64c23:"Chiudi",s625ad019db843f94:"Uso",s9d51bfd93b5dbeca:"Visualizza Archiviati",sac83d7f9358b43db:m`${0} Lista`,sb1bd536b63e9e995:"Campo Privato: Solo io posso vedere i suoi contenuti",sb59d68ed12d46377:"Caricando",sbf1ca928ec1deb62:"Hai bisogno di ulteriore assistenza?",scb9a1ff437efbd2a:m`Seleziona tutti i ${0}vuoi aggiornare dalla lista e aggiornali sotto`,sd1a8dc951b2b6a98:"Scegli quali campi visualizzare come colonne nell'elenco",seafe6ef133ede7da:m`Visualizzando 1 di ${0}`,sf9aee319a006c9b4:"Inserisci",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Or=Object.freeze(Object.defineProperty({__proto__:null,templates:Tr},Symbol.toStringTag,{value:"Module"})),Cr={s226be12a5b1a27e8:"ドキュメントを読む",s33f85f24c0f5f008:"セーブ",s36cb242ac90353bc:"田畑",s41cb4006238ebd3b:"一括編集",s5e8250fb85d64c23:"閉じる",s625ad019db843f94:"使用する",sbf1ca928ec1deb62:"もっと助けが必要ですか？",sd1a8dc951b2b6a98:"リストの列として表示するフィールドを選択します",sf9aee319a006c9b4:"追加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ir=Object.freeze(Object.defineProperty({__proto__:null,templates:Cr},Symbol.toStringTag,{value:"Module"})),Lr={s226be12a5b1a27e8:"문서 읽기",s33f85f24c0f5f008:"구하다",s36cb242ac90353bc:"필드",s41cb4006238ebd3b:"대량 수정",s5e8250fb85d64c23:"닫기",s625ad019db843f94:"사용",sbf1ca928ec1deb62:"더 많은 도움이 필요하신가요?",sd1a8dc951b2b6a98:"목록에서 어떤 필드를 표시할지 고르세요",sf9aee319a006c9b4:"추가",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Pr=Object.freeze(Object.defineProperty({__proto__:null,templates:Lr},Symbol.toStringTag,{value:"Module"})),Mr={s226be12a5b1a27e8:"Прочитај ја документацијата",s33f85f24c0f5f008:"Зачувај",s36cb242ac90353bc:"Полиња",s41cb4006238ebd3b:"Уреди повеќе",s5e8250fb85d64c23:"Затвори",s625ad019db843f94:"Користи",sbf1ca928ec1deb62:"Дали ти треба повеќе помош?",sd1a8dc951b2b6a98:"Избери кои полиња да се прикажат како колони во листата",sf9aee319a006c9b4:"Додади",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},jr=Object.freeze(Object.defineProperty({__proto__:null,templates:Mr},Symbol.toStringTag,{value:"Module"})),Fr={s226be12a5b1a27e8:"कागदपत्रे वाचा.",s33f85f24c0f5f008:"जतन करा",s36cb242ac90353bc:"क्षेत्रे",s41cb4006238ebd3b:"बल्क एडिट करा",s5e8250fb85d64c23:"बंद करा",s625ad019db843f94:"वापर",sbf1ca928ec1deb62:"अधिक मदत आवश्यक आहे का?",sd1a8dc951b2b6a98:"यादीत कोणती क्षेत्रे स्तंभ म्हणून दर्शवली जावीत हे निवडा",sf9aee319a006c9b4:"समाविष्ट करा",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},zr=Object.freeze(Object.defineProperty({__proto__:null,templates:Fr},Symbol.toStringTag,{value:"Module"})),Dr={s226be12a5b1a27e8:"စာရွက်စာတမ်းများကိုဖတ်ပါ",s33f85f24c0f5f008:"သိမ်းဆည်းပါ",s36cb242ac90353bc:"နယ်ပယ်ဒေသများ",s5e8250fb85d64c23:"ပိတ်သည်",s625ad019db843f94:"အသုံးပြုပါ",sbf1ca928ec1deb62:"နောက်ထပ်အကူအညီလိုပါသလား။",sd1a8dc951b2b6a98:"စာရင်းရှိကော်လံများအနေဖြင့်ဖော်ပြမည့်မည်သည့်နယ်ပယ်ဒေသများကိုရွေးချယ်ပါ",sf9aee319a006c9b4:"ထည့်ပါ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Rr=Object.freeze(Object.defineProperty({__proto__:null,templates:Dr},Symbol.toStringTag,{value:"Module"})),Nr={s226be12a5b1a27e8:"कागजात पढ्नुहोस्",s33f85f24c0f5f008:"सुरक्षित गर्नुहोस",s36cb242ac90353bc:"क्षेत्रहरू",s41cb4006238ebd3b:"थोक सम्पादन",s5e8250fb85d64c23:"बन्द गर्नुहोस",s625ad019db843f94:"प्रयोग गर्नुहोस्",sbf1ca928ec1deb62:"थप मद्दत चाहिन्छ?",sd1a8dc951b2b6a98:"सूचीमा स्तम्भहरूको रूपमा कुन क्षेत्रहरू प्रदर्शन गर्ने छनौट गर्नुहोस्",sf9aee319a006c9b4:"थप",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},qr=Object.freeze(Object.defineProperty({__proto__:null,templates:Nr},Symbol.toStringTag,{value:"Module"})),Ur={s04ceadb276bbe149:"aan het laden.....",s226be12a5b1a27e8:"Lees de documentatie",s29e25f5e4622f847:"Dialoogvenster openen",s33f85f24c0f5f008:"Opslaan",s36cb242ac90353bc:"Velden",s41cb4006238ebd3b:"Bulkbewerking",s5e8250fb85d64c23:"sluit",s625ad019db843f94:"Gebruiken",sac83d7f9358b43db:m`${0} Lijst`,sb1bd536b63e9e995:"Privéveld: alleen ik kan de inhoud zien",sb59d68ed12d46377:"aan het laden",sbf1ca928ec1deb62:"Meer hulp nodig?",sd1a8dc951b2b6a98:"Kies welke velden u als kolommen in de lijst wilt weergeven",seafe6ef133ede7da:m`1 van ${0} laten zien`,sf9aee319a006c9b4:"Toevoegen",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,s9d51bfd93b5dbeca:"Show Archived"},Vr=Object.freeze(Object.defineProperty({__proto__:null,templates:Ur},Symbol.toStringTag,{value:"Module"})),Br={s226be12a5b1a27e8:"ਦਸਤਾਵੇਜ਼ ਪੜ੍ਹੋ",s33f85f24c0f5f008:"ਸੇਵ",s36cb242ac90353bc:"ਖੇਤਰ",s41cb4006238ebd3b:"ਥੋਕ ਸੰਪਾਦਨ",s5e8250fb85d64c23:"ਬੰਦ ਕਰੋ",s625ad019db843f94:"ਵਰਤੋਂ",sbf1ca928ec1deb62:"ਹੋਰ ਮਦਦ ਦੀ ਲੋੜ ਹੈ?",sd1a8dc951b2b6a98:"ਸੂਚੀ ਵਿੱਚ ਕਾਲਮ ਦੇ ਰੂਪ ਵਿੱਚ ਪ੍ਰਦਰਸ਼ਿਤ ਕਰਨ ਲਈ ਕਿਹੜੇ ਖੇਤਰ ਚੁਣੋ",sf9aee319a006c9b4:"ਸ਼ਾਮਲ ਕਰੋ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Hr=Object.freeze(Object.defineProperty({__proto__:null,templates:Br},Symbol.toStringTag,{value:"Module"})),Kr={s226be12a5b1a27e8:"Przeczytaj dokumentację",s33f85f24c0f5f008:"Zapisać",s36cb242ac90353bc:"Pola",s41cb4006238ebd3b:"Edycja zbiorcza",s5e8250fb85d64c23:"Zamknij",s625ad019db843f94:"Posługiwać się",sbf1ca928ec1deb62:"Potrzebujesz pomocy?",sd1a8dc951b2b6a98:"Wybierz, które pola mają być wyświetlane jako kolumny na liście",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Gr=Object.freeze(Object.defineProperty({__proto__:null,templates:Kr},Symbol.toStringTag,{value:"Module"})),Wr={s226be12a5b1a27e8:"Leia a documentação",s33f85f24c0f5f008:"Salvar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edição em massa",s5e8250fb85d64c23:"Fechar",s625ad019db843f94:"Usar",sbf1ca928ec1deb62:"Precisa de mais ajuda?",sd1a8dc951b2b6a98:"Escolha quais campos exibir como colunas na lista",sf9aee319a006c9b4:"Adicionar",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Zr=Object.freeze(Object.defineProperty({__proto__:null,templates:Wr},Symbol.toStringTag,{value:"Module"})),Jr={s226be12a5b1a27e8:"Citiți documentația",s33f85f24c0f5f008:"Salvați",s36cb242ac90353bc:"Câmpuri",s41cb4006238ebd3b:"Editare masivă",s5e8250fb85d64c23:"Închide",s625ad019db843f94:"Utilizare",sbf1ca928ec1deb62:"Ai nevoie de mai mult ajutor?",sd1a8dc951b2b6a98:"Alegeți câmpurile care să fie afișate în coloane în listă",sf9aee319a006c9b4:"Adăuga",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Qr=Object.freeze(Object.defineProperty({__proto__:null,templates:Jr},Symbol.toStringTag,{value:"Module"})),Xr={s226be12a5b1a27e8:"Читать документацию",s33f85f24c0f5f008:"Сохранить",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Массовое редактирование",s5e8250fb85d64c23:"Закрыть",s625ad019db843f94:"Использовать",sbf1ca928ec1deb62:"Нужна дополнительная помощь?",sd1a8dc951b2b6a98:"Выберите, какие поля отображать как столбцы в списке",sf9aee319a006c9b4:"Добавить",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Yr=Object.freeze(Object.defineProperty({__proto__:null,templates:Xr},Symbol.toStringTag,{value:"Module"})),en={s226be12a5b1a27e8:"Preberite dokumentacijo",s33f85f24c0f5f008:"Shrani",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Urejanje v velikem obsegu",s5e8250fb85d64c23:"Zapri",s625ad019db843f94:"Uporaba",sbf1ca928ec1deb62:"Potrebujete več pomoči?",sd1a8dc951b2b6a98:"Izberite, katera polja naj bodo prikazana kot stolpci na seznamu",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},tn=Object.freeze(Object.defineProperty({__proto__:null,templates:en},Symbol.toStringTag,{value:"Module"})),on={s226be12a5b1a27e8:"Pročitajte dokumentaciju",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"masovno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristiti",sbf1ca928ec1deb62:"Treba vam više pomoći?",sd1a8dc951b2b6a98:"Izaberite koja polja da se prikazuju kao kolone na listi",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},sn=Object.freeze(Object.defineProperty({__proto__:null,templates:on},Symbol.toStringTag,{value:"Module"})),an={s04ceadb276bbe149:"Inapakia chaguo...",s226be12a5b1a27e8:"Soma nyaraka",s29e25f5e4622f847:"Fungua Kidirisha",s33f85f24c0f5f008:"Hifadhi",s36cb242ac90353bc:"Mashamba",s41cb4006238ebd3b:"Hariri kwa Wingi",s5e8250fb85d64c23:"Funga",s625ad019db843f94:"Tumia",s9d51bfd93b5dbeca:"Onyesha Kumbukumbu",sac83d7f9358b43db:m`Orodha ya${0}`,sb1bd536b63e9e995:"Sehemu ya Faragha: Ni mimi pekee ninayeweza kuona maudhui yake",sb59d68ed12d46377:"Inapakia",sbf1ca928ec1deb62:"Unahitaji msaada zaidi?",scb9a1ff437efbd2a:m`Chagua ${0} zote ungependa kusasisha kutoka kwenye orodha, na uzisasishe hapa chini.`,sd1a8dc951b2b6a98:"Chagua ni sehemu zipi zitaonyeshwa kama safu wima kwenye orodha",seafe6ef133ede7da:m`Inaonyesha 1 kati ya ${0}`,sf9aee319a006c9b4:"Ongeza",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},rn=Object.freeze(Object.defineProperty({__proto__:null,templates:an},Symbol.toStringTag,{value:"Module"})),nn={s226be12a5b1a27e8:"อ่านเอกสาร",s33f85f24c0f5f008:"บันทึก",s36cb242ac90353bc:"ฟิลด์",s41cb4006238ebd3b:"แก้ไขเป็นกลุ่ม",s5e8250fb85d64c23:"ปิด",s625ad019db843f94:"ใช้",sbf1ca928ec1deb62:"ต้องการความช่วยเหลือเพิ่มเติมหรือไม่?",sd1a8dc951b2b6a98:"เลือกฟิลด์ที่จะแสดงเป็นคอลัมน์ในรายการ",sf9aee319a006c9b4:"เพิ่ม",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ln=Object.freeze(Object.defineProperty({__proto__:null,templates:nn},Symbol.toStringTag,{value:"Module"})),dn={s226be12a5b1a27e8:"Basahin ang dokumentasyon",s33f85f24c0f5f008:"I-save",s36cb242ac90353bc:"Mga Field",s41cb4006238ebd3b:"Maramihang Pag-edit",s5e8250fb85d64c23:"Isara",s625ad019db843f94:"Gamitin",sbf1ca928ec1deb62:"Kailangan mo pa ba ng tulong?",sd1a8dc951b2b6a98:"Piliin kung aling mga field ang ipapakita bilang mga column sa listahan",sf9aee319a006c9b4:"Idagdag",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},cn=Object.freeze(Object.defineProperty({__proto__:null,templates:dn},Symbol.toStringTag,{value:"Module"})),un={s04ceadb276bbe149:"Seçenekler Yükleniyor...",s226be12a5b1a27e8:"Belgeleri oku",s29e25f5e4622f847:"İletişim Kutusunu Aç",s33f85f24c0f5f008:"Kaydet",s36cb242ac90353bc:"Alanlar",s41cb4006238ebd3b:"Toplu Düzenleme",s5e8250fb85d64c23:"Kapat",s625ad019db843f94:"Kullan",s9d51bfd93b5dbeca:"Arşivlenmiş Göster",sac83d7f9358b43db:m`${0} Listesi`,sb1bd536b63e9e995:"Özel Alan: İçeriğini sadece ben görebilirim",sb59d68ed12d46377:"Yükleniyor",sbf1ca928ec1deb62:"Daha fazla yardıma ihtiyacınız var mı?",scb9a1ff437efbd2a:m`Listeden güncellemek istediğiniz tüm ${0} 'i seçin ve aşağıda güncelleyin`,sd1a8dc951b2b6a98:"Listede Hangi Alanların Sütun Olarak Görüntüleneceğini Seçin",seafe6ef133ede7da:m`Gösteriliyor 1 of ${0}`,sf9aee319a006c9b4:"Ekle",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},hn=Object.freeze(Object.defineProperty({__proto__:null,templates:un},Symbol.toStringTag,{value:"Module"})),pn={s226be12a5b1a27e8:"Прочитайте документацію",s33f85f24c0f5f008:"Зберегти",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Масове редагування",s5e8250fb85d64c23:"Закрити",s625ad019db843f94:"Використати",sbf1ca928ec1deb62:"Потрібна додаткова допомога?",sd1a8dc951b2b6a98:"Виберіть, яке поле відображати у вигляді стовпців у списку",sf9aee319a006c9b4:"Додати",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},fn=Object.freeze(Object.defineProperty({__proto__:null,templates:pn},Symbol.toStringTag,{value:"Module"})),bn={s226be12a5b1a27e8:"Đọc tài liệu",s33f85f24c0f5f008:"Lưu",s36cb242ac90353bc:"Trường",s41cb4006238ebd3b:"Chỉnh sửa Hàng loạt",s5e8250fb85d64c23:"Đóng",s625ad019db843f94:"Sử dụng",sbf1ca928ec1deb62:"Bạn cần trợ giúp thêm?",sd1a8dc951b2b6a98:"Chọn các trường để hiển thị dưới dạng cột trong danh sách",sf9aee319a006c9b4:"Bổ sung",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},mn=Object.freeze(Object.defineProperty({__proto__:null,templates:bn},Symbol.toStringTag,{value:"Module"})),gn={s226be12a5b1a27e8:"阅读文档",s33f85f24c0f5f008:"保存",s36cb242ac90353bc:"字段",s41cb4006238ebd3b:"批量编辑",s5e8250fb85d64c23:"关",s625ad019db843f94:"使用",sbf1ca928ec1deb62:"需要更多帮助吗？",sd1a8dc951b2b6a98:"选择哪些字段要在列表中显示为列",sf9aee319a006c9b4:"添加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:m`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:m`${0} List`,seafe6ef133ede7da:m`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},vn=Object.freeze(Object.defineProperty({__proto__:null,templates:gn},Symbol.toStringTag,{value:"Module"})),yn={s04ceadb276bbe149:"正在載入選項...",s226be12a5b1a27e8:"閱讀文檔",s29e25f5e4622f847:"開啟對話視窗",s33f85f24c0f5f008:"儲存",s36cb242ac90353bc:"欄位",s41cb4006238ebd3b:"大量編輯",s5e8250fb85d64c23:"關",s625ad019db843f94:"使用",s9d51bfd93b5dbeca:"顯示已儲存",sac83d7f9358b43db:m`${0} 清單`,sb1bd536b63e9e995:"私人欄位：只有我可以看見內容",sb59d68ed12d46377:"載入中",sbf1ca928ec1deb62:"需要更多幫助嗎？",scb9a1ff437efbd2a:m`從清單中選取要更新的項目${0}，並在下面進行更新`,sd1a8dc951b2b6a98:"選擇哪些欄位要顯示為列表中的直行",seafe6ef133ede7da:m`第1頁 （共${0}頁）`,sf9aee319a006c9b4:"新增",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},_n=Object.freeze(Object.defineProperty({__proto__:null,templates:yn},Symbol.toStringTag,{value:"Module"}));_.ApiService=yi,_.ComponentService=Ie,_.DtAlert=Ao,_.DtBase=N,_.DtButton=_i,_.DtChurchHealthCircle=ho,_.DtConnection=po,_.DtCopyText=bo,_.DtDate=jt,_.DtDatetime=mo,_.DtFileUpload=Eo,_.DtFormBase=M,_.DtIcon=ro,_.DtLabel=no,_.DtLocation=go,_.DtLocationMap=_o,_.DtMapModal=yo,_.DtModal=vo,_.DtMultiSelect=Ze,_.DtMultiSelectButtonGroup=So,_.DtMultiText=zt,_.DtMultiTextGroups=ko,_.DtNumberField=wo,_.DtSingleSelect=$o,_.DtTags=Ce,_.DtText=Ft,_.DtTextArea=xo,_.DtTile=To,_.DtToggle=uo,_.DtUsersConnection=fo,_.version=Va,Object.defineProperty(_,Symbol.toStringTag,{value:"Module"})});
