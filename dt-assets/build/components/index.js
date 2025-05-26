var DtWebComponents=function($){"use strict";var In=Object.defineProperty;var Mn=($,N,W)=>N in $?In($,N,{enumerable:!0,configurable:!0,writable:!0,value:W}):$[N]=W;var Ye=($,N,W)=>Mn($,typeof N!="symbol"?N+"":N,W);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var Ai;const N=globalThis,W=N.ShadowRoot&&(N.ShadyCSS===void 0||N.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,qt=Symbol(),Bt=new WeakMap;let Ui=class{constructor(e,t,o){if(this._$cssResult$=!0,o!==qt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(W&&e===void 0){const o=t!==void 0&&t.length===1;o&&(e=Bt.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),o&&Bt.set(t,e))}return e}toString(){return this.cssText}};const qi=s=>new Ui(typeof s=="string"?s:s+"",void 0,qt),Bi=(s,e)=>{if(W)s.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const o=document.createElement("style"),i=N.litNonce;i!==void 0&&o.setAttribute("nonce",i),o.textContent=t.cssText,s.appendChild(o)}},Vt=W?s=>s:s=>s instanceof CSSStyleSheet?(e=>{let t="";for(const o of e.cssRules)t+=o.cssText;return qi(t)})(s):s;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:Vi,defineProperty:Hi,getOwnPropertyDescriptor:Gi,getOwnPropertyNames:Wi,getOwnPropertySymbols:Ki,getPrototypeOf:Zi}=Object,K=globalThis,Ht=K.trustedTypes,Ji=Ht?Ht.emptyScript:"",et=K.reactiveElementPolyfillSupport,he=(s,e)=>s,tt={toAttribute(s,e){switch(e){case Boolean:s=s?Ji:null;break;case Object:case Array:s=s==null?s:JSON.stringify(s)}return s},fromAttribute(s,e){let t=s;switch(e){case Boolean:t=s!==null;break;case Number:t=s===null?null:Number(s);break;case Object:case Array:try{t=JSON.parse(s)}catch{t=null}}return t}},Gt=(s,e)=>!Vi(s,e),Wt={attribute:!0,type:String,converter:tt,reflect:!1,hasChanged:Gt};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),K.litPropertyMetadata??(K.litPropertyMetadata=new WeakMap);let pe=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Wt){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const o=Symbol(),i=this.getPropertyDescriptor(e,o,t);i!==void 0&&Hi(this.prototype,e,i)}}static getPropertyDescriptor(e,t,o){const{get:i,set:a}=Gi(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return i==null?void 0:i.call(this)},set(r){const n=i==null?void 0:i.call(this);a.call(this,r),this.requestUpdate(e,n,o)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Wt}static _$Ei(){if(this.hasOwnProperty(he("elementProperties")))return;const e=Zi(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(he("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(he("properties"))){const t=this.properties,o=[...Wi(t),...Ki(t)];for(const i of o)this.createProperty(i,t[i])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[o,i]of t)this.elementProperties.set(o,i)}this._$Eh=new Map;for(const[t,o]of this.elementProperties){const i=this._$Eu(t,o);i!==void 0&&this._$Eh.set(i,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const o=new Set(e.flat(1/0).reverse());for(const i of o)t.unshift(Vt(i))}else e!==void 0&&t.push(Vt(e));return t}static _$Eu(e,t){const o=t.attribute;return o===!1?void 0:typeof o=="string"?o:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const o of t.keys())this.hasOwnProperty(o)&&(e.set(o,this[o]),delete this[o]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return Bi(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var o;return(o=t.hostConnected)==null?void 0:o.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var o;return(o=t.hostDisconnected)==null?void 0:o.call(t)})}attributeChangedCallback(e,t,o){this._$AK(e,o)}_$EC(e,t){var a;const o=this.constructor.elementProperties.get(e),i=this.constructor._$Eu(e,o);if(i!==void 0&&o.reflect===!0){const r=(((a=o.converter)==null?void 0:a.toAttribute)!==void 0?o.converter:tt).toAttribute(t,o.type);this._$Em=e,r==null?this.removeAttribute(i):this.setAttribute(i,r),this._$Em=null}}_$AK(e,t){var a;const o=this.constructor,i=o._$Eh.get(e);if(i!==void 0&&this._$Em!==i){const r=o.getPropertyOptions(i),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:tt;this._$Em=i,this[i]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,o){if(e!==void 0){if(o??(o=this.constructor.getPropertyOptions(e)),!(o.hasChanged??Gt)(this[e],t))return;this.P(e,t,o)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,o){this._$AL.has(e)||this._$AL.set(e,t),o.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var o;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const i=this.constructor.elementProperties;if(i.size>0)for(const[a,r]of i)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(o=this._$EO)==null||o.forEach(i=>{var a;return(a=i.hostUpdate)==null?void 0:a.call(i)}),this.update(t)):this._$EU()}catch(i){throw e=!1,this._$EU(),i}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(o=>{var i;return(i=o.hostUpdated)==null?void 0:i.call(o)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}};pe.elementStyles=[],pe.shadowRootOptions={mode:"open"},pe[he("elementProperties")]=new Map,pe[he("finalized")]=new Map,et==null||et({ReactiveElement:pe}),(K.reactiveElementVersions??(K.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const fe=globalThis,Pe=fe.trustedTypes,Kt=Pe?Pe.createPolicy("lit-html",{createHTML:s=>s}):void 0,Zt="$lit$",Z=`lit$${Math.random().toFixed(9).slice(2)}$`,Jt="?"+Z,Qi=`<${Jt}>`,ee=document,be=()=>ee.createComment(""),ge=s=>s===null||typeof s!="object"&&typeof s!="function",ot=Array.isArray,Xi=s=>ot(s)||typeof(s==null?void 0:s[Symbol.iterator])=="function",it=`[ 	
\f\r]`,me=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Qt=/-->/g,Xt=/>/g,te=RegExp(`>|${it}(?:([^\\s"'>=/]+)(${it}*=${it}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),Yt=/'/g,eo=/"/g,to=/^(?:script|style|textarea|title)$/i,Yi=s=>(e,...t)=>({_$litType$:s,strings:e,values:t}),d=Yi(1),B=Symbol.for("lit-noChange"),T=Symbol.for("lit-nothing"),oo=new WeakMap,oe=ee.createTreeWalker(ee,129);function io(s,e){if(!ot(s)||!s.hasOwnProperty("raw"))throw Error("invalid template strings array");return Kt!==void 0?Kt.createHTML(e):e}const es=(s,e)=>{const t=s.length-1,o=[];let i,a=e===2?"<svg>":e===3?"<math>":"",r=me;for(let n=0;n<t;n++){const l=s[n];let u,b,g=-1,v=0;for(;v<l.length&&(r.lastIndex=v,b=r.exec(l),b!==null);)v=r.lastIndex,r===me?b[1]==="!--"?r=Qt:b[1]!==void 0?r=Xt:b[2]!==void 0?(to.test(b[2])&&(i=RegExp("</"+b[2],"g")),r=te):b[3]!==void 0&&(r=te):r===te?b[0]===">"?(r=i??me,g=-1):b[1]===void 0?g=-2:(g=r.lastIndex-b[2].length,u=b[1],r=b[3]===void 0?te:b[3]==='"'?eo:Yt):r===eo||r===Yt?r=te:r===Qt||r===Xt?r=me:(r=te,i=void 0);const y=r===te&&s[n+1].startsWith("/>")?" ":"";a+=r===me?l+Qi:g>=0?(o.push(u),l.slice(0,g)+Zt+l.slice(g)+Z+y):l+Z+(g===-2?n:y)}return[io(s,a+(s[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),o]};class ve{constructor({strings:e,_$litType$:t},o){let i;this.parts=[];let a=0,r=0;const n=e.length-1,l=this.parts,[u,b]=es(e,t);if(this.el=ve.createElement(u,o),oe.currentNode=this.el.content,t===2||t===3){const g=this.el.content.firstChild;g.replaceWith(...g.childNodes)}for(;(i=oe.nextNode())!==null&&l.length<n;){if(i.nodeType===1){if(i.hasAttributes())for(const g of i.getAttributeNames())if(g.endsWith(Zt)){const v=b[r++],y=i.getAttribute(g).split(Z),_=/([.?@])?(.*)/.exec(v);l.push({type:1,index:a,name:_[2],strings:y,ctor:_[1]==="."?os:_[1]==="?"?is:_[1]==="@"?ss:Ie}),i.removeAttribute(g)}else g.startsWith(Z)&&(l.push({type:6,index:a}),i.removeAttribute(g));if(to.test(i.tagName)){const g=i.textContent.split(Z),v=g.length-1;if(v>0){i.textContent=Pe?Pe.emptyScript:"";for(let y=0;y<v;y++)i.append(g[y],be()),oe.nextNode(),l.push({type:2,index:++a});i.append(g[v],be())}}}else if(i.nodeType===8)if(i.data===Jt)l.push({type:2,index:a});else{let g=-1;for(;(g=i.data.indexOf(Z,g+1))!==-1;)l.push({type:7,index:a}),g+=Z.length-1}a++}}static createElement(e,t){const o=ee.createElement("template");return o.innerHTML=e,o}}function le(s,e,t=s,o){var r,n;if(e===B)return e;let i=o!==void 0?(r=t._$Co)==null?void 0:r[o]:t._$Cl;const a=ge(e)?void 0:e._$litDirective$;return(i==null?void 0:i.constructor)!==a&&((n=i==null?void 0:i._$AO)==null||n.call(i,!1),a===void 0?i=void 0:(i=new a(s),i._$AT(s,t,o)),o!==void 0?(t._$Co??(t._$Co=[]))[o]=i:t._$Cl=i),i!==void 0&&(e=le(s,i._$AS(s,e.values),i,o)),e}let ts=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:o}=this._$AD,i=((e==null?void 0:e.creationScope)??ee).importNode(t,!0);oe.currentNode=i;let a=oe.nextNode(),r=0,n=0,l=o[0];for(;l!==void 0;){if(r===l.index){let u;l.type===2?u=new ce(a,a.nextSibling,this,e):l.type===1?u=new l.ctor(a,l.name,l.strings,this,e):l.type===6&&(u=new as(a,this,e)),this._$AV.push(u),l=o[++n]}r!==(l==null?void 0:l.index)&&(a=oe.nextNode(),r++)}return oe.currentNode=ee,i}p(e){let t=0;for(const o of this._$AV)o!==void 0&&(o.strings!==void 0?(o._$AI(e,o,t),t+=o.strings.length-2):o._$AI(e[t])),t++}};class ce{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,o,i){this.type=2,this._$AH=T,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=o,this.options=i,this._$Cv=(i==null?void 0:i.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=le(this,e,t),ge(e)?e===T||e==null||e===""?(this._$AH!==T&&this._$AR(),this._$AH=T):e!==this._$AH&&e!==B&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Xi(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==T&&ge(this._$AH)?this._$AA.nextSibling.data=e:this.T(ee.createTextNode(e)),this._$AH=e}$(e){var a;const{values:t,_$litType$:o}=e,i=typeof o=="number"?this._$AC(e):(o.el===void 0&&(o.el=ve.createElement(io(o.h,o.h[0]),this.options)),o);if(((a=this._$AH)==null?void 0:a._$AD)===i)this._$AH.p(t);else{const r=new ts(i,this),n=r.u(this.options);r.p(t),this.T(n),this._$AH=r}}_$AC(e){let t=oo.get(e.strings);return t===void 0&&oo.set(e.strings,t=new ve(e)),t}k(e){ot(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let o,i=0;for(const a of e)i===t.length?t.push(o=new ce(this.O(be()),this.O(be()),this,this.options)):o=t[i],o._$AI(a),i++;i<t.length&&(this._$AR(o&&o._$AB.nextSibling,i),t.length=i)}_$AR(e=this._$AA.nextSibling,t){var o;for((o=this._$AP)==null?void 0:o.call(this,!1,!0,t);e&&e!==this._$AB;){const i=e.nextSibling;e.remove(),e=i}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class Ie{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,o,i,a){this.type=1,this._$AH=T,this._$AN=void 0,this.element=e,this.name=t,this._$AM=i,this.options=a,o.length>2||o[0]!==""||o[1]!==""?(this._$AH=Array(o.length-1).fill(new String),this.strings=o):this._$AH=T}_$AI(e,t=this,o,i){const a=this.strings;let r=!1;if(a===void 0)e=le(this,e,t,0),r=!ge(e)||e!==this._$AH&&e!==B,r&&(this._$AH=e);else{const n=e;let l,u;for(e=a[0],l=0;l<a.length-1;l++)u=le(this,n[o+l],t,l),u===B&&(u=this._$AH[l]),r||(r=!ge(u)||u!==this._$AH[l]),u===T?e=T:e!==T&&(e+=(u??"")+a[l+1]),this._$AH[l]=u}r&&!i&&this.j(e)}j(e){e===T?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class os extends Ie{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===T?void 0:e}}class is extends Ie{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==T)}}class ss extends Ie{constructor(e,t,o,i,a){super(e,t,o,i,a),this.type=5}_$AI(e,t=this){if((e=le(this,e,t,0)??T)===B)return;const o=this._$AH,i=e===T&&o!==T||e.capture!==o.capture||e.once!==o.once||e.passive!==o.passive,a=e!==T&&(o===T||i);i&&this.element.removeEventListener(this.name,this,o),a&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class as{constructor(e,t,o){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=o}get _$AU(){return this._$AM._$AU}_$AI(e){le(this,e)}}const rs={I:ce},st=fe.litHtmlPolyfillSupport;st==null||st(ve,ce),(fe.litHtmlVersions??(fe.litHtmlVersions=[])).push("3.2.1");const ns=(s,e,t)=>{const o=(t==null?void 0:t.renderBefore)??e;let i=o._$litPart$;if(i===void 0){const a=(t==null?void 0:t.renderBefore)??null;o._$litPart$=i=new ce(e.insertBefore(be(),a),a,void 0,t??{})}return i._$AI(s),i};/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const Me=globalThis,at=Me.ShadowRoot&&(Me.ShadyCSS===void 0||Me.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,rt=Symbol(),so=new WeakMap;let ao=class{constructor(e,t,o){if(this._$cssResult$=!0,o!==rt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(at&&e===void 0){const o=t!==void 0&&t.length===1;o&&(e=so.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),o&&so.set(t,e))}return e}toString(){return this.cssText}};const ls=s=>new ao(typeof s=="string"?s:s+"",void 0,rt),S=(s,...e)=>{const t=s.length===1?s[0]:e.reduce((o,i,a)=>o+(r=>{if(r._$cssResult$===!0)return r.cssText;if(typeof r=="number")return r;throw Error("Value passed to 'css' function must be a 'css' function result: "+r+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(i)+s[a+1],s[0]);return new ao(t,s,rt)},cs=(s,e)=>{if(at)s.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const o=document.createElement("style"),i=Me.litNonce;i!==void 0&&o.setAttribute("nonce",i),o.textContent=t.cssText,s.appendChild(o)}},ro=at?s=>s:s=>s instanceof CSSStyleSheet?(e=>{let t="";for(const o of e.cssRules)t+=o.cssText;return ls(t)})(s):s;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:ds,defineProperty:us,getOwnPropertyDescriptor:hs,getOwnPropertyNames:ps,getOwnPropertySymbols:fs,getPrototypeOf:bs}=Object,J=globalThis,no=J.trustedTypes,gs=no?no.emptyScript:"",nt=J.reactiveElementPolyfillSupport,ye=(s,e)=>s,lt={toAttribute(s,e){switch(e){case Boolean:s=s?gs:null;break;case Object:case Array:s=s==null?s:JSON.stringify(s)}return s},fromAttribute(s,e){let t=s;switch(e){case Boolean:t=s!==null;break;case Number:t=s===null?null:Number(s);break;case Object:case Array:try{t=JSON.parse(s)}catch{t=null}}return t}},lo=(s,e)=>!ds(s,e),co={attribute:!0,type:String,converter:lt,reflect:!1,hasChanged:lo};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),J.litPropertyMetadata??(J.litPropertyMetadata=new WeakMap);class de extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=co){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const o=Symbol(),i=this.getPropertyDescriptor(e,o,t);i!==void 0&&us(this.prototype,e,i)}}static getPropertyDescriptor(e,t,o){const{get:i,set:a}=hs(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return i==null?void 0:i.call(this)},set(r){const n=i==null?void 0:i.call(this);a.call(this,r),this.requestUpdate(e,n,o)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??co}static _$Ei(){if(this.hasOwnProperty(ye("elementProperties")))return;const e=bs(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(ye("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(ye("properties"))){const t=this.properties,o=[...ps(t),...fs(t)];for(const i of o)this.createProperty(i,t[i])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[o,i]of t)this.elementProperties.set(o,i)}this._$Eh=new Map;for(const[t,o]of this.elementProperties){const i=this._$Eu(t,o);i!==void 0&&this._$Eh.set(i,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const o=new Set(e.flat(1/0).reverse());for(const i of o)t.unshift(ro(i))}else e!==void 0&&t.push(ro(e));return t}static _$Eu(e,t){const o=t.attribute;return o===!1?void 0:typeof o=="string"?o:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const o of t.keys())this.hasOwnProperty(o)&&(e.set(o,this[o]),delete this[o]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return cs(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var o;return(o=t.hostConnected)==null?void 0:o.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var o;return(o=t.hostDisconnected)==null?void 0:o.call(t)})}attributeChangedCallback(e,t,o){this._$AK(e,o)}_$EC(e,t){var a;const o=this.constructor.elementProperties.get(e),i=this.constructor._$Eu(e,o);if(i!==void 0&&o.reflect===!0){const r=(((a=o.converter)==null?void 0:a.toAttribute)!==void 0?o.converter:lt).toAttribute(t,o.type);this._$Em=e,r==null?this.removeAttribute(i):this.setAttribute(i,r),this._$Em=null}}_$AK(e,t){var a;const o=this.constructor,i=o._$Eh.get(e);if(i!==void 0&&this._$Em!==i){const r=o.getPropertyOptions(i),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:lt;this._$Em=i,this[i]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,o){if(e!==void 0){if(o??(o=this.constructor.getPropertyOptions(e)),!(o.hasChanged??lo)(this[e],t))return;this.P(e,t,o)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,o){this._$AL.has(e)||this._$AL.set(e,t),o.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var o;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const i=this.constructor.elementProperties;if(i.size>0)for(const[a,r]of i)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(o=this._$EO)==null||o.forEach(i=>{var a;return(a=i.hostUpdate)==null?void 0:a.call(i)}),this.update(t)):this._$EU()}catch(i){throw e=!1,this._$EU(),i}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(o=>{var i;return(i=o.hostUpdated)==null?void 0:i.call(o)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}}de.elementStyles=[],de.shadowRootOptions={mode:"open"},de[ye("elementProperties")]=new Map,de[ye("finalized")]=new Map,nt==null||nt({ReactiveElement:de}),(J.reactiveElementVersions??(J.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */let U=class extends de{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=ns(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return B}};U._$litElement$=!0,U.finalized=!0,(Ai=globalThis.litElementHydrateSupport)==null||Ai.call(globalThis,{LitElement:U});const ct=globalThis.litElementPolyfillSupport;ct==null||ct({LitElement:U}),(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.1.1");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const dt={ATTRIBUTE:1,CHILD:2},ut=s=>(...e)=>({_$litDirective$:s,values:e});let ht=class{constructor(e){}get _$AU(){return this._$AM._$AU}_$AT(e,t,o){this._$Ct=e,this._$AM=t,this._$Ci=o}_$AS(e,t){return this.update(e,t)}update(e,t){return this.render(...t)}};/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const F=ut(class extends ht{constructor(s){var e;if(super(s),s.type!==dt.ATTRIBUTE||s.name!=="class"||((e=s.strings)==null?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(s){return" "+Object.keys(s).filter(e=>s[e]).join(" ")+" "}update(s,[e]){var o,i;if(this.st===void 0){this.st=new Set,s.strings!==void 0&&(this.nt=new Set(s.strings.join(" ").split(/\s/).filter(a=>a!=="")));for(const a in e)e[a]&&!((o=this.nt)!=null&&o.has(a))&&this.st.add(a);return this.render(e)}const t=s.element.classList;for(const a of this.st)a in e||(t.remove(a),this.st.delete(a));for(const a in e){const r=!!e[a];r===this.st.has(a)||(i=this.nt)!=null&&i.has(a)||(r?(t.add(a),this.st.add(a)):(t.remove(a),this.st.delete(a)))}return B}});/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const pt="lit-localize-status";/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const h=(s,...e)=>({strTag:!0,strings:s,values:e}),ms=s=>typeof s!="string"&&"strTag"in s,uo=(s,e,t)=>{let o=s[0];for(let i=1;i<s.length;i++)o+=e[t?t[i-1]:i-1],o+=s[i];return o};/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ho=s=>ms(s)?uo(s.strings,s.values):s;let A=ho,po=!1;function vs(s){if(po)throw new Error("lit-localize can only be configured once");A=s,po=!0}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class ys{constructor(e){this.__litLocalizeEventHandler=t=>{t.detail.status==="ready"&&this.host.requestUpdate()},this.host=e}hostConnected(){window.addEventListener(pt,this.__litLocalizeEventHandler)}hostDisconnected(){window.removeEventListener(pt,this.__litLocalizeEventHandler)}}const ws=s=>s.addController(new ys(s));/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class fo{constructor(){this.settled=!1,this.promise=new Promise((e,t)=>{this._resolve=e,this._reject=t})}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const V=[];for(let s=0;s<256;s++)V[s]=(s>>4&15).toString(16)+(s&15).toString(16);function _s(s){let e=0,t=8997,o=0,i=33826,a=0,r=40164,n=0,l=52210;for(let u=0;u<s.length;u++)t^=s.charCodeAt(u),e=t*435,o=i*435,a=r*435,n=l*435,a+=t<<8,n+=i<<8,o+=e>>>16,t=e&65535,a+=o>>>16,i=o&65535,l=n+(a>>>16)&65535,r=a&65535;return V[l>>8]+V[l&255]+V[r>>8]+V[r&255]+V[i>>8]+V[i&255]+V[t>>8]+V[t&255]}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const $s="",xs="h",ks="s";function Ss(s,e){return(e?xs:ks)+_s(typeof s=="string"?s:s.join($s))}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const bo=new WeakMap,go=new Map;function Es(s,e,t){if(s){const o=(t==null?void 0:t.id)??Ts(e),i=s[o];if(i){if(typeof i=="string")return i;if("strTag"in i)return uo(i.strings,e.values,i.values);{let a=bo.get(i);return a===void 0&&(a=i.values,bo.set(i,a)),{...i,values:a.map(r=>e.values[r])}}}}return ho(e)}function Ts(s){const e=typeof s=="string"?s:s.strings;let t=go.get(e);return t===void 0&&(t=Ss(e,typeof s!="string"&&!("strTag"in s)),go.set(e,t)),t}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function ft(s){window.dispatchEvent(new CustomEvent(pt,{detail:s}))}let je="",bt,mo,De,gt,vo,ie=new fo;ie.resolve();let ze=0;const As=s=>(vs((e,t)=>Es(vo,e,t)),je=mo=s.sourceLocale,De=new Set(s.targetLocales),De.add(s.sourceLocale),gt=s.loadLocale,{getLocale:Os,setLocale:Cs}),Os=()=>je,Cs=s=>{if(s===(bt??je))return ie.promise;if(!De||!gt)throw new Error("Internal error");if(!De.has(s))throw new Error("Invalid locale code");ze++;const e=ze;return bt=s,ie.settled&&(ie=new fo),ft({status:"loading",loadingLocale:s}),(s===mo?Promise.resolve({templates:void 0}):gt(s)).then(o=>{ze===e&&(je=s,bt=void 0,vo=o.templates,ft({status:"ready",readyLocale:s}),ie.resolve())},o=>{ze===e&&(ft({status:"error",errorLocale:s,errorMessage:o.toString()}),ie.reject(o))}),ie.promise},Ls=(s,e,t)=>{const o=s[e];return o?typeof o=="function"?o():Promise.resolve(o):new Promise((i,a)=>{(typeof queueMicrotask=="function"?queueMicrotask:setTimeout)(a.bind(null,new Error("Unknown variable dynamic import: "+e+(e.split("/").length!==t?". Note that variables only represent file names one level deep.":""))))})},Ps="en",Is=["am_ET","ar","ar_MA","bg_BG","bn_BD","bs_BA","cs","de_DE","el","en_US","es_419","es_ES","fa_IR","fr_FR","hi_IN","hr","hu_HU","id_ID","it_IT","ja","ko_KR","mk_MK","mr","my_MM","ne_NP","nl_NL","pa_IN","pl","pt_BR","ro_RO","ru_RU","sl_SI","sr_BA","sw","th","tl","tr_TR","uk","vi","zh_CN","zh_TW"],{setLocale:Ms}=As({sourceLocale:Ps,targetLocales:Is,loadLocale:s=>Ls(Object.assign({"./generated/am_ET.js":()=>Promise.resolve().then(()=>Va),"./generated/ar.js":()=>Promise.resolve().then(()=>Ga),"./generated/ar_MA.js":()=>Promise.resolve().then(()=>Ka),"./generated/bg_BG.js":()=>Promise.resolve().then(()=>Ja),"./generated/bn_BD.js":()=>Promise.resolve().then(()=>Xa),"./generated/bs_BA.js":()=>Promise.resolve().then(()=>er),"./generated/cs.js":()=>Promise.resolve().then(()=>or),"./generated/de_DE.js":()=>Promise.resolve().then(()=>sr),"./generated/el.js":()=>Promise.resolve().then(()=>rr),"./generated/en_US.js":()=>Promise.resolve().then(()=>lr),"./generated/es-419.js":()=>Promise.resolve().then(()=>dr),"./generated/es_419.js":()=>Promise.resolve().then(()=>hr),"./generated/es_ES.js":()=>Promise.resolve().then(()=>fr),"./generated/fa_IR.js":()=>Promise.resolve().then(()=>gr),"./generated/fr_FR.js":()=>Promise.resolve().then(()=>vr),"./generated/hi_IN.js":()=>Promise.resolve().then(()=>wr),"./generated/hr.js":()=>Promise.resolve().then(()=>$r),"./generated/hu_HU.js":()=>Promise.resolve().then(()=>kr),"./generated/id_ID.js":()=>Promise.resolve().then(()=>Er),"./generated/it_IT.js":()=>Promise.resolve().then(()=>Ar),"./generated/ja.js":()=>Promise.resolve().then(()=>Cr),"./generated/ko_KR.js":()=>Promise.resolve().then(()=>Pr),"./generated/mk_MK.js":()=>Promise.resolve().then(()=>Mr),"./generated/mr.js":()=>Promise.resolve().then(()=>Dr),"./generated/my_MM.js":()=>Promise.resolve().then(()=>Rr),"./generated/ne_NP.js":()=>Promise.resolve().then(()=>Fr),"./generated/nl_NL.js":()=>Promise.resolve().then(()=>qr),"./generated/pa_IN.js":()=>Promise.resolve().then(()=>Vr),"./generated/pl.js":()=>Promise.resolve().then(()=>Gr),"./generated/pt_BR.js":()=>Promise.resolve().then(()=>Kr),"./generated/ro_RO.js":()=>Promise.resolve().then(()=>Jr),"./generated/ru_RU.js":()=>Promise.resolve().then(()=>Xr),"./generated/sl_SI.js":()=>Promise.resolve().then(()=>en),"./generated/sr_BA.js":()=>Promise.resolve().then(()=>on),"./generated/sw.js":()=>Promise.resolve().then(()=>an),"./generated/th.js":()=>Promise.resolve().then(()=>nn),"./generated/tl.js":()=>Promise.resolve().then(()=>cn),"./generated/tr_TR.js":()=>Promise.resolve().then(()=>un),"./generated/uk.js":()=>Promise.resolve().then(()=>pn),"./generated/vi.js":()=>Promise.resolve().then(()=>bn),"./generated/zh_CN.js":()=>Promise.resolve().then(()=>mn),"./generated/zh_TW.js":()=>Promise.resolve().then(()=>yn)}),`./generated/${s}.js`,3)});class Re{constructor(e,t="/wp-json"){this.nonce=e;let o=t;o.match("^http")&&(o=o.replace(/^http[s]?:\/\/.*?\//,"")),o=`/${o}/`.replace(/\/\//g,"/"),this.apiRoot=o}async makeRequest(e,t,o,i="dt/v1/"){let a=i;!a.endsWith("/")&&!t.startsWith("/")&&(a+="/");const r=t.startsWith("http")?t:`${this.apiRoot}${a}${t}`,n={method:e,credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce}};e!=="GET"&&(n.body=JSON.stringify(o));const l=await fetch(r,n),u=await l.json();if(!l.ok){const b=new Error((u==null?void 0:u.message)||u.toString());throw b.args={status:l.status,statusText:l.statusText,body:u},b}return u}async makeRequestOnPosts(e,t,o={}){return this.makeRequest(e,t,o,"dt-posts/v2/")}async getPost(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}`)}async createPost(e,t){return this.makeRequestOnPosts("POST",e,t)}async fetchPostsList(e,t){return this.makeRequestOnPosts("POST",`${e}/list`,t)}async updatePost(e,t,o){return this.makeRequestOnPosts("POST",`${e}/${t}`,o)}async deletePost(e,t){return this.makeRequestOnPosts("DELETE",`${e}/${t}`)}async listPostsCompact(e,t=""){const o=new URLSearchParams({s:t});return this.makeRequestOnPosts("GET",`${e}/compact?${o}`)}async getPostDuplicates(e,t,o){return this.makeRequestOnPosts("GET",`${e}/${t}/all_duplicates`,o)}async checkFieldValueExists(e,t){return this.makeRequestOnPosts("POST",`${e}/check_field_value_exists`,t)}async getMultiSelectValues(e,t,o=""){const i=new URLSearchParams({s:o,field:t});return this.makeRequestOnPosts("GET",`${e}/multi-select-values?${i}`)}async transferContact(e,t){return this.makeRequestOnPosts("POST","contacts/transfer",{contact_id:e,site_post_id:t})}async transferContactSummaryUpdate(e,t){return this.makeRequestOnPosts("POST","contacts/transfer/summary/send-update",{contact_id:e,update:t})}async requestRecordAccess(e,t,o){return this.makeRequestOnPosts("POST",`${e}/${t}/request_record_access`,{user_id:o})}async createComment(e,t,o,i="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments`,{comment:o,comment_type:i})}async updateComment(e,t,o,i,a="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${o}`,{comment:i,comment_type:a})}async deleteComment(e,t,o){return this.makeRequestOnPosts("DELETE",`${e}/${t}/comments/${o}`)}async getComments(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/comments`)}async toggle_comment_reaction(e,t,o,i,a){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${o}/react`,{user_id:i,reaction:a})}async getPostActivity(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/activity`)}async getSingleActivity(e,t,o){return this.makeRequestOnPosts("GET",`${e}/${t}/activity/${o}`)}async revertActivity(e,t,o){return this.makeRequestOnPosts("GET",`${e}/${t}/revert/${o}`)}async getPostShares(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/shares`)}async addPostShare(e,t,o){return this.makeRequestOnPosts("POST",`${e}/${t}/shares`,{user_id:o})}async removePostShare(e,t,o){return this.makeRequestOnPosts("DELETE",`${e}/${t}/shares`,{user_id:o})}async getFilters(){return this.makeRequest("GET","users/get_filters")}async saveFilters(e,t){return this.makeRequest("POST","users/save_filters",{filter:t,postType:e})}async deleteFilter(e,t){return this.makeRequest("DELETE","users/save_filters",{id:t,postType:e})}async searchUsers(e="",t){const o=new URLSearchParams({s:e});return this.makeRequest("GET",`users/get_users?${o}&post_type=${t}`)}async checkDuplicateUsers(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/duplicates`)}async getContactInfo(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/`)}async createUser(e){return this.makeRequest("POST","users/create",e)}async advanced_search(e,t,o,i){return this.makeRequest("GET","advanced_search",{query:e,postType:t,offset:o,post:i.post,comment:i.comment,meta:i.meta,status:i.status},"dt-posts/v2/posts/search/")}}(function(){(function(s){const e=new WeakMap,t=new WeakMap,o=new WeakMap,i=new WeakMap,a=new WeakMap,r=new WeakMap,n=new WeakMap,l=new WeakMap,u=new WeakMap,b=new WeakMap,g=new WeakMap,v=new WeakMap,y=new WeakMap,_=new WeakMap,C=new WeakMap,R={ariaAtomic:"aria-atomic",ariaAutoComplete:"aria-autocomplete",ariaBusy:"aria-busy",ariaChecked:"aria-checked",ariaColCount:"aria-colcount",ariaColIndex:"aria-colindex",ariaColIndexText:"aria-colindextext",ariaColSpan:"aria-colspan",ariaCurrent:"aria-current",ariaDescription:"aria-description",ariaDisabled:"aria-disabled",ariaExpanded:"aria-expanded",ariaHasPopup:"aria-haspopup",ariaHidden:"aria-hidden",ariaInvalid:"aria-invalid",ariaKeyShortcuts:"aria-keyshortcuts",ariaLabel:"aria-label",ariaLevel:"aria-level",ariaLive:"aria-live",ariaModal:"aria-modal",ariaMultiLine:"aria-multiline",ariaMultiSelectable:"aria-multiselectable",ariaOrientation:"aria-orientation",ariaPlaceholder:"aria-placeholder",ariaPosInSet:"aria-posinset",ariaPressed:"aria-pressed",ariaReadOnly:"aria-readonly",ariaRelevant:"aria-relevant",ariaRequired:"aria-required",ariaRoleDescription:"aria-roledescription",ariaRowCount:"aria-rowcount",ariaRowIndex:"aria-rowindex",ariaRowIndexText:"aria-rowindextext",ariaRowSpan:"aria-rowspan",ariaSelected:"aria-selected",ariaSetSize:"aria-setsize",ariaSort:"aria-sort",ariaValueMax:"aria-valuemax",ariaValueMin:"aria-valuemin",ariaValueNow:"aria-valuenow",ariaValueText:"aria-valuetext",role:"role"},I=(p,c)=>{for(let f in R){c[f]=null;let m=null;const w=R[f];Object.defineProperty(c,f,{get(){return m},set(x){m=x,p.isConnected?P(p,w,x):b.set(p,c)}})}};function L(p){const c=i.get(p),{form:f}=c;Pi(p,f,c),Li(p,c.labels)}const Ce=(p,c=!1)=>{const f=document.createTreeWalker(p,NodeFilter.SHOW_ELEMENT,{acceptNode(x){return i.has(x)?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let m=f.nextNode();const w=!c||p.disabled;for(;m;)m.formDisabledCallback&&w&&zt(m,p.disabled),m=f.nextNode()},Ze={attributes:!0,attributeFilter:["disabled","name"]},X=Xe()?new MutationObserver(p=>{for(const c of p){const f=c.target;if(c.attributeName==="disabled"&&(f.constructor.formAssociated?zt(f,f.hasAttribute("disabled")):f.localName==="fieldset"&&Ce(f)),c.attributeName==="name"&&f.constructor.formAssociated){const m=i.get(f),w=u.get(f);m.setFormValue(w)}}}):{};function E(p){p.forEach(c=>{const{addedNodes:f,removedNodes:m}=c,w=Array.from(f),x=Array.from(m);w.forEach(k=>{var M;if(i.has(k)&&k.constructor.formAssociated&&L(k),b.has(k)){const O=b.get(k);Object.keys(R).filter(q=>O[q]!==null).forEach(q=>{P(k,R[q],O[q])}),b.delete(k)}if(C.has(k)){const O=C.get(k);P(k,"internals-valid",O.validity.valid.toString()),P(k,"internals-invalid",(!O.validity.valid).toString()),P(k,"aria-invalid",(!O.validity.valid).toString()),C.delete(k)}if(k.localName==="form"){const O=l.get(k),G=document.createTreeWalker(k,NodeFilter.SHOW_ELEMENT,{acceptNode(Ut){return i.has(Ut)&&Ut.constructor.formAssociated&&!(O&&O.has(Ut))?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let q=G.nextNode();for(;q;)L(q),q=G.nextNode()}k.localName==="fieldset"&&((M=X.observe)===null||M===void 0||M.call(X,k,Ze),Ce(k,!0))}),x.forEach(k=>{const M=i.get(k);M&&o.get(M)&&Oi(M),n.has(k)&&n.get(k).disconnect()})})}function D(p){p.forEach(c=>{const{removedNodes:f}=c;f.forEach(m=>{const w=y.get(c.target);i.has(m)&&Mi(m),w.disconnect()})})}const ne=p=>{var c,f;const m=new MutationObserver(D);!((c=window==null?void 0:window.ShadyDOM)===null||c===void 0)&&c.inUse&&p.mode&&p.host&&(p=p.host),(f=m.observe)===null||f===void 0||f.call(m,p,{childList:!0}),y.set(p,m)};Xe()&&new MutationObserver(E);const Y={childList:!0,subtree:!0},P=(p,c,f)=>{p.getAttribute(c)!==f&&p.setAttribute(c,f)},zt=(p,c)=>{p.toggleAttribute("internals-disabled",c),c?P(p,"aria-disabled","true"):p.removeAttribute("aria-disabled"),p.formDisabledCallback&&p.formDisabledCallback.apply(p,[c])},Oi=p=>{o.get(p).forEach(f=>{f.remove()}),o.set(p,[])},Ci=(p,c)=>{const f=document.createElement("input");return f.type="hidden",f.name=p.getAttribute("name"),p.after(f),o.get(c).push(f),f},wn=(p,c)=>{var f;o.set(c,[]),(f=X.observe)===null||f===void 0||f.call(X,p,Ze)},Li=(p,c)=>{if(c.length){Array.from(c).forEach(m=>m.addEventListener("click",p.click.bind(p)));let f=c[0].id;c[0].id||(f=`${c[0].htmlFor}_Label`,c[0].id=f),P(p,"aria-labelledby",f)}},Je=p=>{const c=Array.from(p.elements).filter(x=>!x.tagName.includes("-")&&x.validity).map(x=>x.validity.valid),f=l.get(p)||[],m=Array.from(f).filter(x=>x.isConnected).map(x=>i.get(x).validity.valid),w=[...c,...m].includes(!1);p.toggleAttribute("internals-invalid",w),p.toggleAttribute("internals-valid",!w)},_n=p=>{Je(Qe(p.target))},$n=p=>{Je(Qe(p.target))},xn=p=>{const c=["button[type=submit]","input[type=submit]","button:not([type])"].map(f=>`${f}:not([disabled])`).map(f=>`${f}:not([form])${p.id?`,${f}[form='${p.id}']`:""}`).join(",");p.addEventListener("click",f=>{if(f.target.closest(c)){const w=l.get(p);if(p.noValidate)return;w.size&&Array.from(w).reverse().map(M=>i.get(M).reportValidity()).includes(!1)&&f.preventDefault()}})},kn=p=>{const c=l.get(p.target);c&&c.size&&c.forEach(f=>{f.constructor.formAssociated&&f.formResetCallback&&f.formResetCallback.apply(f)})},Pi=(p,c,f)=>{if(c){const m=l.get(c);if(m)m.add(p);else{const w=new Set;w.add(p),l.set(c,w),xn(c),c.addEventListener("reset",kn),c.addEventListener("input",_n),c.addEventListener("change",$n)}r.set(c,{ref:p,internals:f}),p.constructor.formAssociated&&p.formAssociatedCallback&&setTimeout(()=>{p.formAssociatedCallback.apply(p,[c])},0),Je(c)}},Qe=p=>{let c=p.parentNode;return c&&c.tagName!=="FORM"&&(c=Qe(c)),c},H=(p,c,f=DOMException)=>{if(!p.constructor.formAssociated)throw new f(c)},Ii=(p,c,f)=>{const m=l.get(p);return m&&m.size&&m.forEach(w=>{i.get(w)[f]()||(c=!1)}),c},Mi=p=>{if(p.constructor.formAssociated){const c=i.get(p),{labels:f,form:m}=c;Li(p,f),Pi(p,m,c)}};function Xe(){return typeof MutationObserver<"u"}class Sn{constructor(){this.badInput=!1,this.customError=!1,this.patternMismatch=!1,this.rangeOverflow=!1,this.rangeUnderflow=!1,this.stepMismatch=!1,this.tooLong=!1,this.tooShort=!1,this.typeMismatch=!1,this.valid=!0,this.valueMissing=!1,Object.seal(this)}}const En=p=>(p.badInput=!1,p.customError=!1,p.patternMismatch=!1,p.rangeOverflow=!1,p.rangeUnderflow=!1,p.stepMismatch=!1,p.tooLong=!1,p.tooShort=!1,p.typeMismatch=!1,p.valid=!0,p.valueMissing=!1,p),Tn=(p,c,f)=>(p.valid=An(c),Object.keys(c).forEach(m=>p[m]=c[m]),f&&Je(f),p),An=p=>{let c=!0;for(let f in p)f!=="valid"&&p[f]!==!1&&(c=!1);return c},Rt=new WeakMap;function ji(p,c){p.toggleAttribute(c,!0),p.part&&p.part.add(c)}class Nt extends Set{static get isPolyfilled(){return!0}constructor(c){if(super(),!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");Rt.set(this,c)}add(c){if(!/^--/.test(c)||typeof c!="string")throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${c} must start with '--'.`);const f=super.add(c),m=Rt.get(this),w=`state${c}`;return m.isConnected?ji(m,w):setTimeout(()=>{ji(m,w)}),f}clear(){for(let[c]of this.entries())this.delete(c);super.clear()}delete(c){const f=super.delete(c),m=Rt.get(this);return m.isConnected?(m.toggleAttribute(`state${c}`,!1),m.part&&m.part.remove(`state${c}`)):setTimeout(()=>{m.toggleAttribute(`state${c}`,!1),m.part&&m.part.remove(`state${c}`)}),f}}function Di(p,c,f,m){if(typeof c=="function"?p!==c||!0:!c.has(p))throw new TypeError("Cannot read private member from an object whose class did not declare it");return f==="m"?m:f==="a"?m.call(p):m?m.value:c.get(p)}function On(p,c,f,m,w){if(typeof c=="function"?p!==c||!0:!c.has(p))throw new TypeError("Cannot write private member to an object whose class did not declare it");return c.set(p,f),f}var Le;class Cn{constructor(c){Le.set(this,void 0),On(this,Le,c);for(let f=0;f<c.length;f++){let m=c[f];this[f]=m,m.hasAttribute("name")&&(this[m.getAttribute("name")]=m)}Object.freeze(this)}get length(){return Di(this,Le,"f").length}[(Le=new WeakMap,Symbol.iterator)](){return Di(this,Le,"f")[Symbol.iterator]()}item(c){return this[c]==null?null:this[c]}namedItem(c){return this[c]==null?null:this[c]}}function Ln(){const p=HTMLFormElement.prototype.checkValidity;HTMLFormElement.prototype.checkValidity=f;const c=HTMLFormElement.prototype.reportValidity;HTMLFormElement.prototype.reportValidity=m;function f(...x){let k=p.apply(this,x);return Ii(this,k,"checkValidity")}function m(...x){let k=c.apply(this,x);return Ii(this,k,"reportValidity")}const{get:w}=Object.getOwnPropertyDescriptor(HTMLFormElement.prototype,"elements");Object.defineProperty(HTMLFormElement.prototype,"elements",{get(...x){const k=w.call(this,...x),M=Array.from(l.get(this)||[]);if(M.length===0)return k;const O=Array.from(k).concat(M).sort((G,q)=>G.compareDocumentPosition?G.compareDocumentPosition(q)&2?1:-1:0);return new Cn(O)}})}class zi{static get isPolyfilled(){return!0}constructor(c){if(!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");const f=c.getRootNode(),m=new Sn;this.states=new Nt(c),e.set(this,c),t.set(this,m),i.set(c,this),I(c,this),wn(c,this),Object.seal(this),f instanceof DocumentFragment&&ne(f)}checkValidity(){const c=e.get(this);if(H(c,"Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=t.get(this);if(!f.valid){const m=new Event("invalid",{bubbles:!1,cancelable:!0,composed:!1});c.dispatchEvent(m)}return f.valid}get form(){const c=e.get(this);H(c,"Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");let f;return c.constructor.formAssociated===!0&&(f=Qe(c)),f}get labels(){const c=e.get(this);H(c,"Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");const f=c.getAttribute("id"),m=c.getRootNode();return m&&f?m.querySelectorAll(`[for="${f}"]`):[]}reportValidity(){const c=e.get(this);if(H(c,"Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=this.checkValidity(),m=v.get(this);if(m&&!c.constructor.formAssociated)throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");return!f&&m&&(c.focus(),m.focus()),f}setFormValue(c){const f=e.get(this);if(H(f,"Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."),Oi(this),c!=null&&!(c instanceof FormData)){if(f.getAttribute("name")){const m=Ci(f,this);m.value=c}}else c!=null&&c instanceof FormData&&Array.from(c).reverse().forEach(([m,w])=>{if(typeof w=="string"){const x=Ci(f,this);x.name=m,x.value=w}});u.set(f,c)}setValidity(c,f,m){const w=e.get(this);if(H(w,"Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!c)throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");v.set(this,m);const x=t.get(this),k={};for(const G in c)k[G]=c[G];Object.keys(k).length===0&&En(x);const M=Object.assign(Object.assign({},x),k);delete M.valid;const{valid:O}=Tn(x,M,this.form);if(!O&&!f)throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");a.set(this,O?"":f),w.isConnected?(w.toggleAttribute("internals-invalid",!O),w.toggleAttribute("internals-valid",O),P(w,"aria-invalid",`${!O}`)):C.set(w,this)}get shadowRoot(){const c=e.get(this),f=g.get(c);return f||null}get validationMessage(){const c=e.get(this);return H(c,"Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."),a.get(this)}get validity(){const c=e.get(this);return H(c,"Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."),t.get(this)}get willValidate(){const c=e.get(this);return H(c,"Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."),!(c.disabled||c.hasAttribute("disabled")||c.hasAttribute("readonly"))}}function Pn(){if(typeof window>"u"||!window.ElementInternals||!HTMLElement.prototype.attachInternals)return!1;class p extends HTMLElement{constructor(){super(),this.internals=this.attachInternals()}}const c=`element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g,"")}`;customElements.define(c,p);const f=new p;return["shadowRoot","form","willValidate","validity","validationMessage","labels","setFormValue","setValidity","checkValidity","reportValidity"].every(m=>m in f.internals)}let Ri=!1,Ni=!1;function Ft(p){Ni||(Ni=!0,window.CustomStateSet=Nt,p&&(HTMLElement.prototype.attachInternals=function(...c){const f=p.call(this,c);return f.states=new Nt(this),f}))}function Fi(p=!0){if(!Ri){if(Ri=!0,typeof window<"u"&&(window.ElementInternals=zi),typeof CustomElementRegistry<"u"){const c=CustomElementRegistry.prototype.define;CustomElementRegistry.prototype.define=function(f,m,w){if(m.formAssociated){const x=m.prototype.connectedCallback;m.prototype.connectedCallback=function(){_.has(this)||(_.set(this,!0),this.hasAttribute("disabled")&&zt(this,!0)),x!=null&&x.apply(this),Mi(this)}}c.call(this,f,m,w)}}if(typeof HTMLElement<"u"&&(HTMLElement.prototype.attachInternals=function(){if(this.tagName){if(this.tagName.indexOf("-")===-1)throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.")}else return{};if(i.has(this))throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");return new zi(this)}),typeof Element<"u"){let c=function(...m){const w=f.apply(this,m);if(g.set(this,w),Xe()){const x=new MutationObserver(E);window.ShadyDOM?x.observe(this,Y):x.observe(w,Y),n.set(this,x)}return w};const f=Element.prototype.attachShadow;Element.prototype.attachShadow=c}Xe()&&typeof document<"u"&&new MutationObserver(E).observe(document.documentElement,Y),typeof HTMLFormElement<"u"&&Ln(),(p||typeof window<"u"&&!window.CustomStateSet)&&Ft()}}return!!customElements.polyfillWrapFlushCallback||(Pn()?typeof window<"u"&&!window.CustomStateSet&&Ft(HTMLElement.prototype.attachInternals):Fi(!1)),s.forceCustomStateSetPolyfill=Ft,s.forceElementInternalsPolyfill=Fi,Object.defineProperty(s,"__esModule",{value:!0}),s})({})})();class z extends U{static get properties(){return{RTL:{type:Boolean},locale:{type:String},apiRoot:{type:String,reflect:!1},postType:{type:String,reflect:!1},postID:{type:String,reflect:!1}}}get _focusTarget(){return this.shadowRoot.children[0]instanceof Element?this.shadowRoot.children[0]:null}constructor(){super(),ws(this),this.addEventListener("click",this._proxyClick.bind(this)),this.addEventListener("focus",this._proxyFocus.bind(this))}connectedCallback(){super.connectedCallback(),this.apiRoot=this.apiRoot?`${this.apiRoot}/`.replace("//","/"):"/",this.api=new Re(this.nonce,this.apiRoot)}willUpdate(e){if(this.RTL===void 0){const t=this.closest("[dir]");if(t){const o=t.getAttribute("dir");o&&(this.RTL=o.toLowerCase()==="rtl")}}if(!this.locale){const t=this.closest("[lang]");if(t){const o=t.getAttribute("lang");o&&(this.locale=o)}}if(e&&e.has("locale")&&this.locale)try{Ms(this.locale)}catch(t){console.error(t)}}_proxyClick(){this.clicked=!0}_proxyFocus(){if(this._focusTarget){if(this.clicked){this.clicked=!1;return}this._focusTarget.focus()}}focus(){this._proxyFocus()}}class yo extends z{static get formAssociated(){return!0}static get styles(){return S`
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
    `}static get properties(){return{label:{type:String},context:{type:String},type:{type:String},title:{type:String},outline:{type:Boolean},round:{type:Boolean},disabled:{type:Boolean}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--round":this.round},t=`dt-button--${this.context}`;return e[t]=!0,e}get _field(){return this.shadowRoot.querySelector("button")}get _focusTarget(){return this._field}constructor(){super(),this.context="default",this.internals=this.attachInternals()}handleClick(e){e.preventDefault(),this.type==="submit"&&this.internals.form&&this.internals.form.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0}))}render(){const e={...this.classes};return d`
      <button
        part="button"
        class=${F(e)}
        title=${this.title}
        type=${this.type}
        @click=${this.handleClick}
        ?disabled=${this.disabled}
      >
        <slot></slot>
      </button>
    `}}window.customElements.define("dt-button",yo);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const wo="important",js=" !"+wo,se=ut(class extends ht{constructor(s){var e;if(super(s),s.type!==dt.ATTRIBUTE||s.name!=="style"||((e=s.strings)==null?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(s){return Object.keys(s).reduce((e,t)=>{const o=s[t];return o==null?e:e+`${t=t.includes("-")?t:t.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${o};`},"")}update(s,[e]){const{style:t}=s.element;if(this.ft===void 0)return this.ft=new Set(Object.keys(e)),this.render(e);for(const o of this.ft)e[o]==null&&(this.ft.delete(o),o.includes("-")?t.removeProperty(o):t[o]=null);for(const o in e){const i=e[o];if(i!=null){this.ft.add(o);const a=typeof i=="string"&&i.endsWith(js);o.includes("-")||a?t.setProperty(o,a?i.slice(0,-11):i,a?wo:""):t[o]=i}}return B}});class _o extends z{static get styles(){return S`
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
        padding: var(--dt-modal-padding, 1em);
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
        border: 1px solid #cacaca;
        border-radius: 10px;
      }
      #modal-field-title {
      font-size: 2rem;
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
        display: grid;
        height: fit-content;
        grid-template-columns: 1fr;
        grid-template-rows: 2.5em auto 3em;
        grid-template-areas:
          'header'
          'main'
          'footer';
        position: relative;
      }

      form.no-header {
        grid-template-rows: auto auto;
        grid-template-areas:
          'main'
          'footer';
      }

      header {
        grid-area: header;
        display: flex;
        justify-content: space-between;
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
        grid-area: main;
        overflow: auto;
      }

      footer {
        grid-area: footer;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-block-start: 1rem;
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
      .dt-modal.dt-modal--contact-type form {
        grid-template-rows: 2.5em auto 4.5em;
      .dt-modal.header-blue-bg {
        padding: 0;
      }
      .dt-modal.header-blue-bg header {
        background-color: #3f729b;
        color: #fff;
        text-align: center;
        padding-top: .75rem;
      }
      .dt-modal.header-blue-bg header #modal-field-title {
        font-size: 1.5rem;
        width: 100%;
      }
      .dt-modal.header-blue-bg article {
        padding: .75rem 0;
      }
      .dt-modal.header-blue-bg footer {
        padding-inline: .7rem;
        justify-content: flex-end;
      }
      .dt-modal.header-blue-bg footer .button {
        padding: 12px 14px;
      }
      .dt-modal.header-blue-bg form {
        grid-template-rows: 2.5em auto 3em;
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
    `}static get properties(){return{title:{type:String},context:{type:String},isHelp:{type:Boolean},isOpen:{type:Boolean},hideHeader:{type:Boolean},hideButton:{type:Boolean},buttonClass:{type:Object},buttonStyle:{type:Object},headerClass:{type:Object},imageSrc:{type:String},imageStyle:{type:Object},tileLabel:{type:String},buttonLabel:{type:String},dropdownListImg:{type:String},submitButton:{type:Boolean},closeButton:{type:Boolean}}}constructor(){super(),this.context="default",this.addEventListener("open",()=>this._openModal()),this.addEventListener("close",()=>this._closeModal())}_openModal(){this.isOpen=!0,this.shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}get formattedTitle(){if(!this.title)return"";const e=this.title.replace(/_/g," ");return e.charAt(0).toUpperCase()+e.slice(1)}_dialogHeader(e){return this.hideHeader?d``:d`
      <header>
            <h1 id="modal-field-title" class="modal-header">${this.formattedTitle}</h1>
            <button @click="${this._cancelModal}" class="toggle">${e}</button>
          </header>
      `}_closeModal(){this.isOpen=!1,this.shadowRoot.querySelector("dialog").close(),document.querySelector("body").style.overflow="initial"}_cancelModal(){this._triggerClose("cancel")}_triggerClose(e){this.dispatchEvent(new CustomEvent("close",{detail:{action:e}}))}_dialogClick(e){if(e.target.tagName!=="DIALOG")return;const t=e.target.getBoundingClientRect();(t.top<=e.clientY&&e.clientY<=t.top+t.height&&t.left<=e.clientX&&e.clientX<=t.left+t.width)===!1&&this._cancelModal()}_dialogKeypress(e){e.key==="Escape"&&this._cancelModal()}_helpMore(){return this.isHelp?d`
          <div class="help-more">
            <h5>${A("Need more help?")}</h5>
            <a
              class="button small"
              id="docslink"
              href="https://disciple.tools/user-docs"
              target="_blank"
              >${A("Read the documentation")}</a
            >
          </div>
        `:null}firstUpdated(){this.isOpen&&this._openModal()}_onButtonClick(){this._triggerClose("button")}render(){const e=d`
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
    `;return d`
      <dialog
        id=""
        class="dt-modal dt-modal--width ${F(this.headerClass||{})}"
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
          ${this.closeButton?d`
            <button
              class="button small"
              data-close=""
              aria-label="Close reveal"
              type="button"
              @click=${this._onButtonClick}
            >
              <slot name="close-button">${A("Close")}</slot>
              </button>

            `:""}
              ${this.submitButton?d`
                <slot name="submit-button"></span>

                `:""}
              </div>
            ${this._helpMore()}
          </footer>
        </form>
      </dialog>

      ${this.hideButton?null:d`
      <button
        class="button small opener ${F(this.buttonClass||{})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${se(this.buttonStyle||{})}
      >
      ${this.dropdownListImg?d`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">`:""}
      ${this.imageSrc?d`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${se(this.imageStyle||{})}
                 />`:""}
      ${this.buttonLabel?d`${this.buttonLabel}`:""}
      </button>
      `}
    `}}window.customElements.define("dt-modal",_o);class $o extends U{static get styles(){return S`
      :host {
        display: block;
      }
      dt-button {
        background-color: var(--button-color, #3498db);
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
      }

      .dropdown {
        position: relative;
        display: inline-block;
      }
      button.dt-dropdown{
        padding:.5em;
        border:none;
        background-color: var(--dropdown-button-color, #00897B);
        color: var(--dropdown-button-text-color, #ffffff);
        border-radius:4px;
        }


      .dropdown ul {
        position: absolute;
        z-index: 999;
        min-width: 15rem;
        display: none;
        border: 0.5px solid var(--primary-color,#3f729b);
        background: #fff;
        padding: .4rem 0;
        margin: 0;
        list-style: none;
        width: 100%;
        border-radius: 3px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
      }

      .dropdown ul button {
        display: block;
        padding: .4rem 1em;
        text-decoration: none;
        color: var(--primary-color,#3f729b);
        font-size: 1rem;
        border-radius: 0.25em;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        text-decoration: none;
        background: none;
        border: none;
      }

      .dropdown ul a:focus {
        background-color: var(--button-hover-color, #2980b9);
      }

      .list-style {
        color: var(--primary-color,#3f729b);
        font-size: 1rem;
      }

      .list-style:hover {
        background-color: var(--button-hover-color, #3f729b);
      }

      .icon {
        height: 1em;
      }
      .pre-list-item {
        padding: .7rem 1rem;
      }
      .dropdown ul .pre-list-item button {
        padding: 0;
        font-size: .8em
      }
        .pre-list-item:hover {
          background-color: var(--primary-color,#3f729b);
        }
        .pre-list-item:hover button {
          color: var(--surface-1, #ffffff);
        }
        .pre-list-item:hover button img {
          width: 1em;
          height: 1em;
          -webkit-filter: invert(100%) sepia(100%) saturate(6%) hue-rotate(105deg) brightness(102%) contrast(102%);
          filter: invert(100%) sepia(100%) saturate(6%) hue-rotate(105deg) brightness(102%) contrast(102%);
        }
    `}static get properties(){return{options:{type:Array},label:{type:String},isModal:{type:Boolean},buttonStyle:{type:Object},default:{type:Boolean},context:{type:String}}}get classes(){const e={"dt-dropdown":!0},t=`dt-dropdown--${this.context}`;return e[t]=!0,e}render(){return d`
    <div class="dropdown">
    <button
    class=${F(this.classes)}
    style=${se(this.buttonStyle||{})}
    @mouseover=${this._handleHover}
    @mouseleave=${this._handleMouseLeave}
    @focus=${this._handleHover}
    >

    ${this.label} \u25BC

    </button>
    <ul
    class="dt-dropdown-list"
    @mouseover=${this._handleHover}
    @mouseleave=${this._handleMouseLeave}
    @focus=${this._handleHover}
    >

    ${this.options?this.options.map(e=>d`
        ${e.isModal?d`
              <li
                class="pre-list-item"
                @click="${()=>this._openDialog(e.label)}"
                @keydown="${()=>this._openDialog(e.label)}"
              >

                <button
                style=""
                @click="${()=>this._openDialog(e.label)}"
                class="list-style dt-modal"
                >
                ${e.icon?d`<img
                   src="${e.icon}"
                   alt="${e.label} icon"
                   class="icon"
                 />`:""}
                ${e.label}
                </button>
              </li>
            `:d`
              <li
                class="list-style pre-list-item"
                @click="${()=>this._redirectToHref(e.href)}"
                @keydown="${()=>this._redirectToHref(e.href)}"
              >

                <button
                  style=""
                  @click="${()=>this._redirectToHref(e.href)}"
                >
                  <img
                    src=${e.icon}
                    alt=${e.label}
                    class="icon"
                  />
                  ${e.label.replace(/-/g," ")}
                </button>
              </li>
            `}
      `):""}
    </ul>


    </div>
    `}_redirectToHref(e){let t=e;/^https?:\/\//i.test(t)||(t=`http://${t}`),window.open(t,"_blank")}_openDialog(e){const t=e.replace(/\s/g,"-").toLowerCase();document.querySelector(`#${t}`).shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}_handleHover(){const e=this.shadowRoot.querySelector("ul");e.style.display="block"}_handleMouseLeave(){const e=this.shadowRoot.querySelector("ul");e.style.display="none"}}window.customElements.define("dt-dropdown",$o);class Ds extends z{static get styles(){return S`
      root {
        display: block;
      }
      .health-item img {
        width: var(--d);
        height: var(--d);
        filter: grayscale(1) opacity(0.75);
      }
      .health-item--active img {
        filter: none !important;
      }
    `}static get properties(){return{key:{type:String},metric:{type:Object},group:{type:Object},active:{type:Boolean,reflect:!0},missingIcon:{type:String},handleSave:{type:Function}}}render(){const{metric:e,active:t,missingIcon:o=`${window.wpApiShare.template_dir}/dt-assets/images/groups/missing.svg`}=this;return d`<div
      class=${F({"health-item":!0,"health-item--active":t})}
      title="${e.description}"
      @click="${this._handleClick}"
    >
      <img src="${e.icon?e.icon:o}" />
    </div>`}async _handleClick(){if(!this.handleSave)return;const e=!this.active;this.active=e;const t={health_metrics:{values:[{value:this.key,delete:!e}]}};try{await this.handleSave(this.group.ID,t)}catch(o){console.error(o);return}e?this.group.health_metrics.push(this.key):this.group.health_metrics.pop(this.key)}}window.customElements.define("dt-church-health-icon",Ds);class xo extends z{static get styles(){return S`
      .health-circle__container {
        --d: 55px; /* image size */
        --rel: 1; /* how much extra space we want between images, 1 = one image size */
        --r: calc(1 * var(--d) / var(--tan)); /* circle radius */
        --s: calc(3 * var(--r));
        margin: 1rem auto;
        display: flex;
        justify-content: center;
        align-items: baseline;
        padding-top: 100%;
        height: 0;
        position: relative;
        overflow: visible;
      }

      .health-circle {
        display: block;
        border-radius: 100%;
        border: 3px darkgray dashed;
        max-width: 100%;
        position: absolute;
        transform: translate(-50%, -50%);
        left: 50%;
        top: 50%;
        width: 100%;
        height: 100%;
      }

      @media (max-width: 519px) {
        .health-circle__container {
          --d: 40px; /* image size */
        }

        .health-circle {
          max-width: 300px;
          max-height: 300px;
        }
      }

      @media (max-width: 400px) {
        .health-circle__container {
          --d: 30px; /* image size */
        }

        .health-circle {
          max-width: 250px;
          max-height: 250px;
        }
      }

      @media (max-width: 321px) {
        .health-circle__container {
          --d: 25px; /* image size */
        }

        .health-circle {
          max-width: 225px;
          max-height: 225px;
        }
      }

      .health-circle__grid {
        display: inline-block;
        position: relative;
        height: 100%;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        width: var(--s);
        max-width: 100%;
      }

      .health-circle--committed {
        border: 3px #4caf50 solid !important;
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
        margin: calc(-0.5 * var(--d));
        width: var(--d);
        height: var(--d);
        --az: calc(var(--i) * 1turn / var(--m));
        transform: rotate(var(--az)) translate(var(--r)) rotate(calc(-1 * var(--az)));
      }
    `}static get properties(){return{groupId:{type:Number},group:{type:Object,reflect:!1},settings:{type:Object,reflect:!1},errorMessage:{type:String,attribute:!1},missingIcon:{type:String},handleSave:{type:Function}}}get metrics(){const e=this.settings||[];return Object.values(e).length?Object.entries(e).filter(([o,i])=>o!=="church_commitment"):[]}get isCommited(){return!this.group||!this.group.health_metrics?!1:this.group.health_metrics.includes("church_commitment")}connectedCallback(){super.connectedCallback(),this.fetch()}adoptedCallback(){this.distributeItems()}updated(){this.distributeItems()}async fetch(){try{const e=[this.fetchSettings(),this.fetchGroup()];let[t,o]=await Promise.all(e);this.settings=t,this.post=o,t||(this.errorMessage="Error loading settings"),o||(this.errorMessage="Error loading group")}catch(e){console.error(e)}}fetchGroup(){if(this.group)return Promise.resolve(this.group);fetch(`/wp-json/dt-posts/v2/groups/${this.groupId}`).then(e=>e.json())}fetchSettings(){return this.settings?Promise.resolve(this.settings):fetch("/wp-json/dt-posts/v2/groups/settings").then(e=>e.json())}findMetric(e){const t=this.metrics.find(o=>o.key===e);return t?t.value:null}render(){if(!this.group||!this.metrics.length)return d`<dt-spinner></dt-spinner>`;const e=this.group.health_metrics||[];return this.errorMessage&&d`<dt-alert type="error">${this.errorMessage}</dt-alert>`,d`
      <div class="health-circle__wrapper">
        <div class="health-circle__container">
          <div
            class=${F({"health-circle":!0,"health-circle--committed":this.isCommited})}
          >
            <div class="health-circle__grid">
              ${this.metrics.map(([t,o],i)=>d`<dt-church-health-icon
                    key="${t}"
                    .group="${this.group}"
                    .metric=${o}
                    .active=${e.indexOf(t)!==-1}
                    .style="--i: ${i+1}"
                    .missingIcon="${this.missingIcon}"
                    .handleSave="${this.handleSave}"
                  >
                  </dt-church-health-icon>`)}
            </div>
          </div>
        </div>

        <dt-toggle
          name="church-commitment"
          label="${this.settings.church_commitment.label}"
          requiredmessage=""
          icon="https://cdn-icons-png.flaticon.com/512/1077/1077114.png"
          iconalttext="Icon Alt Text"
          privatelabel=""
          @click="${this.toggleClick}"
          ?checked=${this.isCommited}
        >
        </dt-toggle>
      </div>
    `}distributeItems(){const e=this.renderRoot.querySelector(".health-circle__container");let i=e.querySelectorAll("dt-church-health-icon").length,a=Math.tan(Math.PI/i);e.style.setProperty("--m",i),e.style.setProperty("--tan",+a.toFixed(2))}async toggleClick(e){const{handleSave:t}=this;if(!t)return;let o=this.renderRoot.querySelector("dt-toggle"),i=o.toggleAttribute("checked");this.group.health_metrics||(this.group.health_metrics=[]);const a={health_metrics:{values:[{value:"church_commitment",delete:!i}]}};try{await t(this.group.ID,a)}catch(r){o.toggleAttribute("checked",!i),console.error(r);return}i?this.group.health_metrics.push("church_commitment"):this.group.health_metrics.pop("church_commitment"),this.requestUpdate()}_isChecked(){return Object.hasOwn(this.group,"health_metrics")?this.group.health_metrics.includes("church_commitment")?this.isChurch=!0:this.isChurch=!1:this.isChurch=!1}}window.customElements.define("dt-church-health-circle",xo);class ko extends z{static get styles(){return S`
      :host {
        font-family: var(--font-family);
        font-size: var(--dt-label-font-size, 14px);
        font-weight: var(--dt-label-font-weight, 700);
        color: var(--dt-label-color, #000);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .icon {
        height: var(--dt-label-font-size, 14px);
        width: auto;
        display: inline-block;
      }

      .icon.private {
        position: relative;
      }
      .icon.private:hover .tooltip {
        display: block;
      }
      .tooltip {
        display: none;
        position: absolute;
        color: var(--dt-label-tooltip-color, #666);
        background: var(--dt-label-tooltip-background, #eee);
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
        border-bottom: 0.5rem solid var(--dt-label-tooltip-background, #eee);
        border-inline-start: 0.5rem solid transparent;
        border-inline-end: 0.5rem solid transparent;
      }
    `}static get properties(){return{icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String}}}firstUpdated(){const t=this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({flatten:!0});for(const o of t)o.style.height="100%",o.style.width="auto"}get _slottedChildren(){return this.shadowRoot.querySelector("slot").assignedElements({flatten:!0})}render(){const e=d`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;return d`
      <div class="label">
        <span class="icon">
          <slot name="icon-start">
            ${this.icon?d`<img src="${this.icon}" alt="${this.iconAltText}" />`:null}
          </slot>
        </span>
        <slot></slot>

        ${this.private?d`<span class="icon private">
              ${e}
              <span class="tooltip"
                >${this.privateLabel||A("Private Field: Only I can see its content")}</span
              >
            </span> `:null}
      </div>
    `}}window.customElements.define("dt-label",ko);class j extends z{static get formAssociated(){return!0}static get styles(){return[S`
        .input-group {
          position: relative;
        }
        .input-group.disabled {
          background-color: var(--disabled-color);
        }

        /* === Inline Icons === */
        .icon-overlay {
          position: absolute;
          inset-inline-end: 2rem;
          top: 0;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .icon-overlay.alert {
          color: var(--alert-color);
          cursor: pointer;
        }
        .icon-overlay.success {
          color: var(--success-color);
        }
      `]}static get properties(){return{...super.properties,name:{type:String},label:{type:String},icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String},disabled:{type:Boolean},required:{type:Boolean},requiredMessage:{type:String},touched:{type:Boolean,state:!0},invalid:{type:Boolean,state:!0},error:{type:String},loading:{type:Boolean},saved:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector("input, textarea, select")}get _focusTarget(){return this._field}constructor(){super(),this.touched=!1,this.invalid=!1,this.internals=this.attachInternals(),this.addEventListener("invalid",()=>{this.touched=!0,this._validateRequired()})}firstUpdated(...e){super.firstUpdated(...e);const t=j._jsonToFormData(this.value,this.name);this.internals.setFormValue(t),this._validateRequired()}static _buildFormData(e,t,o){if(t&&typeof t=="object"&&!(t instanceof Date)&&!(t instanceof File))Object.keys(t).forEach(i=>{this._buildFormData(e,t[i],o?`${o}[${i}]`:i)});else{const i=t??"";e.append(o,i)}}static _jsonToFormData(e,t){const o=new FormData;return j._buildFormData(o,e,t),o}_setFormValue(e){const t=j._jsonToFormData(e,this.name);this.internals.setFormValue(t,e),this._validateRequired(),this.touched=!0}_validateRequired(){}labelTemplate(){return this.label?d`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon?null:d`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    `:""}render(){return d`
      ${this.labelTemplate()}
      <slot></slot>
    `}}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{I:zs}=rs,So=()=>document.createComment(""),we=(s,e,t)=>{var a;const o=s._$AA.parentNode,i=e===void 0?s._$AB:e._$AA;if(t===void 0){const r=o.insertBefore(So(),i),n=o.insertBefore(So(),i);t=new zs(r,n,s,s.options)}else{const r=t._$AB.nextSibling,n=t._$AM,l=n!==s;if(l){let u;(a=t._$AQ)==null||a.call(t,s),t._$AM=s,t._$AP!==void 0&&(u=s._$AU)!==n._$AU&&t._$AP(u)}if(r!==i||l){let u=t._$AA;for(;u!==r;){const b=u.nextSibling;o.insertBefore(u,i),u=b}}}return t},ae=(s,e,t=s)=>(s._$AI(e,t),s),Rs={},Ns=(s,e=Rs)=>s._$AH=e,Fs=s=>s._$AH,mt=s=>{var o;(o=s._$AP)==null||o.call(s,!1,!0);let e=s._$AA;const t=s._$AB.nextSibling;for(;e!==t;){const i=e.nextSibling;e.remove(),e=i}};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const Eo=(s,e,t)=>{const o=new Map;for(let i=e;i<=t;i++)o.set(s[i],i);return o},_e=ut(class extends ht{constructor(s){if(super(s),s.type!==dt.CHILD)throw Error("repeat() can only be used in text expressions")}dt(s,e,t){let o;t===void 0?t=e:e!==void 0&&(o=e);const i=[],a=[];let r=0;for(const n of s)i[r]=o?o(n,r):r,a[r]=t(n,r),r++;return{values:a,keys:i}}render(s,e,t){return this.dt(s,e,t).values}update(s,[e,t,o]){const i=Fs(s),{values:a,keys:r}=this.dt(e,t,o);if(!Array.isArray(i))return this.ut=r,a;const n=this.ut??(this.ut=[]),l=[];let u,b,g=0,v=i.length-1,y=0,_=a.length-1;for(;g<=v&&y<=_;)if(i[g]===null)g++;else if(i[v]===null)v--;else if(n[g]===r[y])l[y]=ae(i[g],a[y]),g++,y++;else if(n[v]===r[_])l[_]=ae(i[v],a[_]),v--,_--;else if(n[g]===r[_])l[_]=ae(i[g],a[_]),we(s,l[_+1],i[g]),g++,_--;else if(n[v]===r[y])l[y]=ae(i[v],a[y]),we(s,i[g],i[v]),v--,y++;else if(u===void 0&&(u=Eo(r,y,_),b=Eo(n,g,v)),u.has(n[g]))if(u.has(n[v])){const C=b.get(r[y]),R=C!==void 0?i[C]:null;if(R===null){const I=we(s,i[g]);ae(I,a[y]),l[y]=I}else l[y]=ae(R,a[y]),we(s,i[g],R),i[C]=null;y++}else mt(i[v]),v--;else mt(i[g]),g++;for(;y<=_;){const C=we(s,l[_+1]);ae(C,a[y]),l[y++]=C}for(;g<=v;){const C=i[g++];C!==null&&mt(C)}return this.ut=r,Ns(s,l),B}}),Us=s=>class extends s{constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},query:{type:String,state:!0},options:{type:Array},filteredOptions:{type:Array,state:!0},open:{type:Boolean,state:!0},canUpdate:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean}}}willUpdate(e){if(super.willUpdate(e),e&&!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");!e.style.getPropertyValue("--container-width")&&e.clientWidth>0&&e.style.setProperty("--container-width",`${e.clientWidth}px`)}_select(){console.error("Must implement `_select(value)` function")}static _focusInput(e){e.target===e.currentTarget&&e.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!0,this.activeIndex=-1)}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1,this.canUpdate=!0)}_inputKeyDown(e){}_inputKeyUp(e){switch(e.keyCode||e.which){case 8:e.target.value===""?this.value=this.value.slice(0,-1):this.open=!0;break;case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0,this.query=e.target.value;break}}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const o=t.offsetTop,i=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;i>r?e.scrollTo({top:i-e.clientHeight,behavior:"smooth"}):o<a&&e.scrollTo({top:o,behavior:"smooth"})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select(this.query):this._select(this.filteredOptions[this.activeIndex].id),this._clearSearch())}_clickOption(e){e.target&&e.target.value&&(this._select(e.target.value),this._clearSearch())}_clickAddNew(e){var t;e.target&&(this._select((t=e.target.dataset)==null?void 0:t.label),this._clearSearch())}_clearSearch(){const e=this.shadowRoot.querySelector("input");e&&(e.value="")}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1):this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_renderOption(e,t){return d`
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
    `}_baseRenderOptions(){return this.filteredOptions.length?_e(this.filteredOptions,e=>e.id,(e,t)=>this._renderOption(e,t)):this.loading?d`<li><div>${A("Loading options...")}</div></li>`:d`<li><div>${A("No Data Available")}</div></li>`}_renderOptions(){let e=this._baseRenderOptions();return this.allowAdd&&this.query&&(Array.isArray(e)||(e=[e]),e.push(d`<li tabindex="-1">
        <button
          data-label="${this.query}"
          @click="${this._clickAddNew}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          class="${this.activeIndex>-1&&this.activeIndex>=this.filteredOptions.length?"active":""}"
        >
          ${A("Add")} "${this.query}"
        </button>
      </li>`)),e}};class qs extends U{static get styles(){return S`
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
        border: 0.25rem solid var(--dt-spinner-color-1, #919191);
        border-radius: 50%;
        border-top-color: var(--dt-spinner-color-2, #000);
        display: inline-block;
        height: 1rem;
        width: 1rem;
      }
    `}}window.customElements.define("dt-spinner",qs);class Bs extends U{static get styles(){return S`
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
      :host {
        margin-top: -0.25rem;
      }
      :host::before {
        content: '';
        transform: rotate(45deg);
        height: 1rem;
        width: 0.5rem;
        opacity: 0;
        color: inherit;
        border-bottom: var(--dt-checkmark-width) solid currentcolor;
        border-right: var(--dt-checkmark-width) solid currentcolor;
        animation: fadeOut 4s;
      }
    `}}window.customElements.define("dt-checkmark",Bs);class vt extends Us(j){static get styles(){return[...super.styles,S`
        :host {
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
        }

        .input-group {
          color: var(--dt-multi-select-text-color, #0a0a0a);
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

        .field-container {
          background-color: var(--dt-multi-select-background-color, #fefefe);
          border: 1px solid var(--dt-form-border-color, #cacaca);
          border-radius: 0;
          color: var(--dt-multi-select-text-color, #0a0a0a);
          font-size: 1rem;
          font-weight: 300;
          min-height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding-top: calc(0.5rem - 0.375rem);
          padding-bottom: 0.5rem;
          padding-inline: 0.5rem 1.6rem;
          box-sizing: border-box;
          width: 100%;
          text-transform: none;
          display: flex;
          flex-wrap: wrap;
        }

        .field-container input,
        .field-container .selected-option {
          //height: 1.5rem;
        }

        .selected-option {
          border: 1px solid var(--dt-multi-select-tag-border-color, #c2e0ff);
          background-color: var(
            --dt-multi-select-tag-background-color,
            #c2e0ff
          );

          display: flex;
          font-size: 0.875rem;
          position: relative;
          border-radius: 2px;
          margin-inline-end: 4px;
          margin-block-start: 0.375rem;
          box-sizing: border-box;
        }
        .selected-option > *:first-child {
          padding-inline-start: 4px;
          padding-block: 0.25rem;
          line-height: normal;
          text-overflow: ellipsis;
          overflow: hidden;
          white-space: nowrap;
          --container-padding: calc(0.5rem + 1.6rem + 2px);
          --option-padding: 8px;
          --option-button: 20px;
          max-width: calc(
            var(--container-width) - var(--container-padding) -
              var(--option-padding) - var(--option-button)
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
            var(--dt-multi-select-tag-border-color, #c2e0ff);
          color: var(--dt-multi-select-text-color, #0a0a0a);
          margin-inline-start: 4px;
        }
        .selected-option button:hover {
          cursor: pointer;
        }

        .field-container input {
          background-color: var(--dt-form-background-color, #fff);
          color: var(--dt-form-text-color, #000);
          flex-grow: 1;
          min-width: 50px;
          flex-basis: 50px;
          border: 0;
          margin-block-start: 0.375rem;
        }
        .field-container input:focus,
        .field-container input:focus-visible,
        .field-container input:active {
          border: 0;
          outline: 0;
        }
        .field-container input::placeholder {
          color: var(--dt-text-placeholder-color, #999);
          opacity: 1;
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
          color: var(--dt-multi-select-text-color, #0a0a0a);
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
          background: var(--dt-multi-select-option-hover-background, #f5f5f5);
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},containerHeight:{type:Number,state:!0}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length)if(typeof this.value[0]=="string")this.value=[...this.value.filter(o=>o!==`-${e}`),e];else{let o=!1;const i=this.value.map(a=>{const r={...a};return a.id===e.id&&a.delete&&(delete r.delete,o=!0),r});o||i.push(e),this.value=i}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._setFormValue(this.value)}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(o=>o===e.target.dataset.value?`-${o}`:o),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){return this.filteredOptions=(this.options||[]).filter(e=>!(this.value||[]).includes(e.id)&&(!this.query||e.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),o=e.has("query"),i=e.has("options");(t||o||i)&&this._filterOptions()}}_renderSelectedOptions(){return this.options&&this.options.filter(e=>this.value&&this.value.indexOf(e.id)>-1).map(e=>d`
            <div class="selected-option">
              <span>${e.label}</span>
              <button
                @click="${this._remove}"
                ?disabled="${this.disabled}"
                data-value="${e.id}"
              >
                x
              </button>
            </div>
          `)}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return d`
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
            autocomplete="off"
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
          />
        </div>
        <ul class="option-list" style=${se(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
        ${this.error?d`<dt-icon
                icon="mdi:alert-circle"
                class="icon-overlay alert"
                tooltip="${this.error}"
                size="2rem"
                ></dt-icon>`:null}
        </div>
`}}window.customElements.define("dt-multi-select",vt);class $e extends vt{static get properties(){return{...super.properties,allowAdd:{type:Boolean}}}static get styles(){return[...super.styles,S`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(--primary-color, #3f729b);
        }
      `]}willUpdate(e){super.willUpdate(e),e&&e.has("open")&&this.open&&(!this.filteredOptions||!this.filteredOptions.length)&&this._filterOptions()}_filterOptions(){var t;const e=(this.value||[]).filter(o=>!o.startsWith("-"));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(o=>!e.includes(o.id)&&(!this.query||o.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const o=this,i=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{o.loading=!1;let r=a;r.length&&typeof r[0]=="string"&&(r=r.map(n=>({id:n}))),o.allOptions=r,o.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),o.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(i)}return this.filteredOptions}_renderOption(e,t){return d`
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
    `}_renderSelectedOptions(){const e=this.options||this.allOptions;return(this.value||[]).filter(t=>!t.startsWith("-")).map(t=>{var a;let o=t;if(e){const r=e.filter(n=>n===t||n.id===t);r.length&&(o=r[0].label||r[0].id||t)}let i;if(!i&&((a=window==null?void 0:window.SHAREDFUNCTIONS)!=null&&a.createCustomFilter)){const r=window.SHAREDFUNCTIONS.createCustomFilter(this.name,[t]),n=this.label||this.name,l=[{id:`${this.name}_${t}`,name:`${n}: ${t}`}];i=window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType,r,l)}return d`
          <div class="selected-option">
            <a
              href="${i||"#"}"
              ?disabled="${this.disabled}"
              alt="${t}"
              >${o}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t}"
            >
              x
            </button>
          </div>
        `})}}window.customElements.define("dt-tags",$e);class To extends $e{static get styles(){return[...super.styles,S`
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
          margin-bottom: -.25em;
        }
        li button svg use {
          fill: var(--dt-connection-icon-fill, var(--primary-color));
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),o=this.filteredOptions.reduce((i,a)=>!i&&a.id==t?a:i,null);o&&this._select(o),this._clearSearch()}}_clickAddNew(e){var t,o;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(o=e.target.dataset)==null?void 0:o.label,isNew:!0});const i=this.shadowRoot.querySelector("input");i&&(i.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){let t=e.target.dataset.value;const o=Number.parseInt(t);Number.isNaN(o)||(t=o);const i=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(a=>{const r={...a};return a.id===t&&(r.delete=!0),r}),i.detail.newValue=this.value,this.dispatchEvent(i),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(o=>!o.delete).map(o=>o==null?void 0:o.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(o=>!e.includes(o.id)&&(!this.query||o.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const o=this,i=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{o.loading=!1,o.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),o.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(i)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>d`
          <div class="selected-option">
            <a
              href="${e.link}"
              style="border-inline-start-color: ${e.status?e.status.color:""}"
              ?disabled="${this.disabled}"
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
        `)}_renderOption(e,t){const o=d`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`,i=e.status||{label:"",color:""};return d`
      <li tabindex="-1" style="border-inline-start-color:${i.color}">
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
          ${i.label?d`<span class="status">${i.label}</span>`:null}
          ${e.user?o:null}
        </button>
      </li>
    `}}window.customElements.define("dt-connection",To);class Ao extends $e{static get styles(){return[...super.styles,S`
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
          width: 20px;
          height: auto;
          margin-bottom: -4px;
        }
        li button svg use {
          fill: var(--dt-connection-icon-fill, var(--primary-color));
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),o=this.filteredOptions.reduce((i,a)=>!i&&a.id==t?a:i,null);o&&this._select(o),this._clearSearch()}}_clickAddNew(e){var t,o;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(o=e.target.dataset)==null?void 0:o.label,isNew:!0});const i=this.shadowRoot.querySelector("input");i&&(i.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,remove:!0}});this.value=(this.value||[]).map(o=>{const i={...o};return o.id===parseInt(e.target.dataset.value,10)&&(i.delete=!0),i}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(o=>!o.delete).map(o=>o==null?void 0:o.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(o=>!e.includes(o.id)&&(!this.query||o.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const o=this,i=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{o.loading=!1,o.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),o.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(i)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>d`
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
        `)}_renderOption(e,t){return d`
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
          <span class="avatar"><img src="${e.avatar}" alt="${e.label}"/></span> &nbsp;
          <span class="connection-id">${e.label}</span>
        </button>
      </li>
    `}}window.customElements.define("dt-users-connection",Ao);class Oo extends z{static get styles(){return S`
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
    `}static get properties(){return{value:{type:String},success:{type:Boolean},error:{type:Boolean}}}get inputStyles(){return this.success?{"--dt-text-border-color":"var(--copy-text-success-color, var(--success-color))","--dt-form-text-color":"var( --copy-text-success-color, var(--success-color))",color:"var( --copy-text-success-color, var(--success-color))"}:this.error?{"---dt-text-border-color":"var(--copy-text-alert-color, var(--alert-color))","--dt-form-text-color":"var(--copy-text-alert-color, var(--alert-color))"}:{}}get icon(){return this.success?"ic:round-check":"ic:round-content-copy"}async copy(){try{this.success=!1,this.error=!1,await navigator.clipboard.writeText(this.value),this.success=!0,this.error=!1}catch(e){console.log(e),this.success=!1,this.error=!0}}render(){return d`
      <div class="copy-text" style=${se(this.inputStyles)}>
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
    `}}window.customElements.define("dt-copy-text",Oo);class Co extends j{static get styles(){return[...super.styles,S`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-form-background-color, #cecece);
          border: 1px solid var(--dt-form-border-color, #cacaca);
          border-radius: 0;
          box-shadow: var(
            --dt-form-input-box-shadow,
            inset 0 1px 2px hsl(0deg 0% 4% / 10%)
          );
          box-sizing: border-box;
          display: inline-flex;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
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
        textarea[readonly],
        .input-group button:disabled {
          background-color: var(--dt-form-disabled-background-color, #e6e6e6);
          cursor: not-allowed;
        }

        /* input::-webkit-datetime-edit-text { color: red; padding: 0 0.3em; } */
        input::-webkit-calendar-picker-indicator {
          color: red;
        }

        .input-group {
          position: relative;
          display: flex;
          width: 100%;
        }

        .input-group .input-group-button {
          font-size: 0.75rem;
          line-height: 1em;
          display: inline-flex;
        }
        .input-group .button {
          display: inline-block;
          background: var(--dt-form-background-color, #cecece);
          border: 1px solid var(--dt-form-border-color, #cecece);
          color: var(--alert-color, #cc4b37);
          align-self: stretch;
          font-size: 1rem;
          height: auto;
          padding: 0 1em;
          margin: 0;
        }
        .input-group .button:hover:not([disabled]) {
          background-color: var(--alert-color, #cc4b37);
          color: var(--text-color-inverse, #fefefe);
        }

        .icon-overlay {
          inset-inline-end: 5rem;
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},timestamp:{converter:e=>{let t=Number(e);if(t<1e12&&(t*=1e3),t)return t},reflect:!0}}}updateTimestamp(e){const t=new Date(e).getTime(),o=t/1e3,i=new CustomEvent("change",{detail:{field:this.name,oldValue:this.timestamp,newValue:o}});this.timestamp=t,this.value=e,this._setFormValue(e),this.dispatchEvent(i)}_change(e){this.updateTimestamp(e.target.value)}clearInput(){this.updateTimestamp("")}showDatePicker(){this.shadowRoot.querySelector("input").showPicker()}render(){return this.timestamp?this.value=new Date(this.timestamp).toISOString().substring(0,10):this.value&&(this.timestamp=new Date(this.value).getTime()),d`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          class="input-group-field dt_date_picker"
          type="date"
          autocomplete="off"
          .placeholder="${new Date().toISOString().substring(0,10)}"
          .value="${this.value}"
          .timestamp="${this.date}"
          ?disabled=${this.disabled}
          @change="${this._change}"
          @click="${this.showDatePicker}"
        />
        <button
          id="${this.id}-clear-button"
          class="button alert clear-date-button"
          data-inputid="${this.id}"
          title="Delete Date"
          type="button"
          ?disabled=${this.disabled}
          @click="${this.clearInput}"
        >
          x
        </button>

        ${this.touched&&this.invalid||this.error?d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>`:null}
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-date",Co);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*Ne(s,e){if(s!==void 0){let t=0;for(const o of s)yield e(o,t++)}}class Lo extends $e{static get properties(){return{...super.properties,filters:{type:Array},mapboxKey:{type:String},dtMapbox:{type:Object}}}static get styles(){return[...super.styles,S`
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
            var(--container-width) - var(--select-width) -
              var(--container-padding) - var(--option-padding) -
              var(--option-button) - 8px
          );
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=e.target.value,o=this.filteredOptions.reduce((i,a)=>!i&&a.id===t?a:i,null);this._select(o)}}_clickAddNew(e){var t,o;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(o=e.target.dataset)==null?void 0:o.label,isNew:!0});const i=this.shadowRoot.querySelector("input");i&&(i.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(o=>{const i={...o};return o.id===e.target.dataset.value&&(i.delete=!0),i}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}updated(){super.updated();const e=this.shadowRoot.querySelector(".input-group"),t=e.style.getPropertyValue("--select-width"),o=this.shadowRoot.querySelector("select");!t&&(o==null?void 0:o.clientWidth)>0&&e.style.setProperty("--select-width",`${o.clientWidth}px`)}_filterOptions(){var t;const e=(this.value||[]).filter(o=>!o.delete).map(o=>o==null?void 0:o.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(o=>!e.includes(o.id)&&(!this.query||o.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else{this.loading=!0,this.filteredOptions=[];const o=this,i=this.shadowRoot.querySelector("select"),a=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,query:this.query,filter:i==null?void 0:i.value,onSuccess:r=>{o.loading=!1,o.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:r=>{console.warn(r),o.loading=!1}}});this.dispatchEvent(a)}return this.filteredOptions}_renderOption(e,t){return d`
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
    `}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>d`
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
        `)}render(){const e={display:this.open?"block":"none",top:`${this.containerHeight}px`};return this.mapboxKey?d` ${this.labelTemplate()}
          <div id="mapbox-wrapper">
            <div
              id="mapbox-autocomplete"
              class="mapbox-autocomplete input-group"
              data-autosubmit="true"
              data-add-address="true"
            >
              <input
                id="mapbox-search"
                type="text"
                name="mapbox_search"
                class="input-group-field"
                autocomplete="off"
                dir="auto"
                placeholder="Search Location"
              />
              <div class="input-group-button">
                <button
                  id="mapbox-spinner-button"
                  class="button hollow"
                  style="display:none;border-color:lightgrey;"
                >
                  <span
                    class=""
                    style="border-radius: 50%;width: 24px;height: 24px;border: 0.25rem solid lightgrey;border-top-color: black;animation: spin 1s infinite linear;display: inline-block;"
                  ></span>
                </button>
                <button
                  id="mapbox-clear-autocomplete"
                  class="button alert input-height delete-button-style mapbox-delete-button"
                  type="button"
                  title="Clear"
                  style="display:none;"
                >
                  ×
                </button>
              </div>
              <div
                id="mapbox-autocomplete-list"
                class="mapbox-autocomplete-items"
              ></div>
            </div>
            <div id="location-grid-meta-results"></div>
          </div>`:d`
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

              ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
              ${this.saved?d`<dt-checkmark
                    class="icon-overlay success"
                  ></dt-checkmark>`:null}
            </div>
            <select class="filter-list" ?disabled="${this.disabled}">
              ${Ne(this.filters,t=>d`<option value="${t.id}">${t.label}</option>`)}
            </select>
            <ul class="option-list" style=${se(e)}>
              ${this._renderOptions()}
            </ul>
          </div>
        `}}window.customElements.define("dt-location",Lo);class Vs{constructor(e){this.token=e}async searchPlaces(e,t="en"){const o=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:t}),i={method:"GET",headers:{"Content-Type":"application/json"}},a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)}.json?${o}`,n=await(await fetch(a,i)).json();return n==null?void 0:n.features}async reverseGeocode(e,t,o="en"){const i=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:o}),a={method:"GET",headers:{"Content-Type":"application/json"}},r=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)},${encodeURI(t)}.json?${i}`,l=await(await fetch(r,a)).json();return l==null?void 0:l.features}}class Hs{constructor(e,t,o){var i,a,r;if(this.token=e,this.window=t,!((r=(a=(i=t.google)==null?void 0:i.maps)==null?void 0:a.places)!=null&&r.AutocompleteService)){let n=o.createElement("script");n.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${e}`,o.body.appendChild(n)}}async getPlacePredictions(e,t="en"){if(this.window.google){const o=new this.window.google.maps.places.AutocompleteService,{predictions:i}=await o.getPlacePredictions({input:e,language:t});return i}return null}async getPlaceDetails(e,t="en"){const i=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,address:e,language:t})}`,r=await(await fetch(i,{method:"GET"})).json();let n=[];switch(r.status){case"OK":n=r.results;break}return n&&n.length?n[0]:null}async reverseGeocode(e,t,o="en"){const a=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${t},${e}`,language:o,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,n=await(await fetch(a,{method:"GET"})).json();return n==null?void 0:n.results}}/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/const Po=Object.freeze({left:0,top:0,width:16,height:16}),Fe=Object.freeze({rotate:0,vFlip:!1,hFlip:!1}),xe=Object.freeze({...Po,...Fe}),yt=Object.freeze({...xe,body:"",hidden:!1}),Gs=Object.freeze({width:null,height:null}),Io=Object.freeze({...Gs,...Fe});function Ws(s,e=0){const t=s.replace(/^-?[0-9.]*/,"");function o(i){for(;i<0;)i+=4;return i%4}if(t===""){const i=parseInt(s);return isNaN(i)?0:o(i)}else if(t!==s){let i=0;switch(t){case"%":i=25;break;case"deg":i=90}if(i){let a=parseFloat(s.slice(0,s.length-t.length));return isNaN(a)?0:(a=a/i,a%1===0?o(a):0)}}return e}const Ks=/[\s,]+/;function Zs(s,e){e.split(Ks).forEach(t=>{switch(t.trim()){case"horizontal":s.hFlip=!0;break;case"vertical":s.vFlip=!0;break}})}const Mo={...Io,preserveAspectRatio:""};function jo(s){const e={...Mo},t=(o,i)=>s.getAttribute(o)||i;return e.width=t("width",null),e.height=t("height",null),e.rotate=Ws(t("rotate","")),Zs(e,t("flip","")),e.preserveAspectRatio=t("preserveAspectRatio",t("preserveaspectratio","")),e}function Js(s,e){for(const t in Mo)if(s[t]!==e[t])return!0;return!1}const ke=/^[a-z0-9]+(-[a-z0-9]+)*$/,Se=(s,e,t,o="")=>{const i=s.split(":");if(s.slice(0,1)==="@"){if(i.length<2||i.length>3)return null;o=i.shift().slice(1)}if(i.length>3||!i.length)return null;if(i.length>1){const n=i.pop(),l=i.pop(),u={provider:i.length>0?i[0]:o,prefix:l,name:n};return e&&!Ue(u)?null:u}const a=i[0],r=a.split("-");if(r.length>1){const n={provider:o,prefix:r.shift(),name:r.join("-")};return e&&!Ue(n)?null:n}if(t&&o===""){const n={provider:o,prefix:"",name:a};return e&&!Ue(n,t)?null:n}return null},Ue=(s,e)=>s?!!((s.provider===""||s.provider.match(ke))&&(e&&s.prefix===""||s.prefix.match(ke))&&s.name.match(ke)):!1;function Qs(s,e){const t={};!s.hFlip!=!e.hFlip&&(t.hFlip=!0),!s.vFlip!=!e.vFlip&&(t.vFlip=!0);const o=((s.rotate||0)+(e.rotate||0))%4;return o&&(t.rotate=o),t}function Do(s,e){const t=Qs(s,e);for(const o in yt)o in Fe?o in s&&!(o in t)&&(t[o]=Fe[o]):o in e?t[o]=e[o]:o in s&&(t[o]=s[o]);return t}function Xs(s,e){const t=s.icons,o=s.aliases||Object.create(null),i=Object.create(null);function a(r){if(t[r])return i[r]=[];if(!(r in i)){i[r]=null;const n=o[r]&&o[r].parent,l=n&&a(n);l&&(i[r]=[n].concat(l))}return i[r]}return Object.keys(t).concat(Object.keys(o)).forEach(a),i}function Ys(s,e,t){const o=s.icons,i=s.aliases||Object.create(null);let a={};function r(n){a=Do(o[n]||i[n],a)}return r(e),t.forEach(r),Do(s,a)}function zo(s,e){const t=[];if(typeof s!="object"||typeof s.icons!="object")return t;s.not_found instanceof Array&&s.not_found.forEach(i=>{e(i,null),t.push(i)});const o=Xs(s);for(const i in o){const a=o[i];a&&(e(i,Ys(s,i,a)),t.push(i))}return t}const ea={provider:"",aliases:{},not_found:{},...Po};function wt(s,e){for(const t in e)if(t in s&&typeof s[t]!=typeof e[t])return!1;return!0}function Ro(s){if(typeof s!="object"||s===null)return null;const e=s;if(typeof e.prefix!="string"||!s.icons||typeof s.icons!="object"||!wt(s,ea))return null;const t=e.icons;for(const i in t){const a=t[i];if(!i.match(ke)||typeof a.body!="string"||!wt(a,yt))return null}const o=e.aliases||Object.create(null);for(const i in o){const a=o[i],r=a.parent;if(!i.match(ke)||typeof r!="string"||!t[r]&&!o[r]||!wt(a,yt))return null}return e}const qe=Object.create(null);function ta(s,e){return{provider:s,prefix:e,icons:Object.create(null),missing:new Set}}function Q(s,e){const t=qe[s]||(qe[s]=Object.create(null));return t[e]||(t[e]=ta(s,e))}function _t(s,e){return Ro(e)?zo(e,(t,o)=>{o?s.icons[t]=o:s.missing.add(t)}):[]}function oa(s,e,t){try{if(typeof t.body=="string")return s.icons[e]={...t},!0}catch{}return!1}function ia(s,e){let t=[];return(typeof s=="string"?[s]:Object.keys(qe)).forEach(i=>{(typeof i=="string"&&typeof e=="string"?[e]:Object.keys(qe[i]||{})).forEach(r=>{const n=Q(i,r);t=t.concat(Object.keys(n.icons).map(l=>(i!==""?"@"+i+":":"")+r+":"+l))})}),t}let Ee=!1;function No(s){return typeof s=="boolean"&&(Ee=s),Ee}function Te(s){const e=typeof s=="string"?Se(s,!0,Ee):s;if(e){const t=Q(e.provider,e.prefix),o=e.name;return t.icons[o]||(t.missing.has(o)?null:void 0)}}function Fo(s,e){const t=Se(s,!0,Ee);if(!t)return!1;const o=Q(t.provider,t.prefix);return oa(o,t.name,e)}function Uo(s,e){if(typeof s!="object")return!1;if(typeof e!="string"&&(e=s.provider||""),Ee&&!e&&!s.prefix){let i=!1;return Ro(s)&&(s.prefix="",zo(s,(a,r)=>{r&&Fo(a,r)&&(i=!0)})),i}const t=s.prefix;if(!Ue({provider:e,prefix:t,name:"a"}))return!1;const o=Q(e,t);return!!_t(o,s)}function sa(s){return!!Te(s)}function aa(s){const e=Te(s);return e?{...xe,...e}:null}function ra(s){const e={loaded:[],missing:[],pending:[]},t=Object.create(null);s.sort((i,a)=>i.provider!==a.provider?i.provider.localeCompare(a.provider):i.prefix!==a.prefix?i.prefix.localeCompare(a.prefix):i.name.localeCompare(a.name));let o={provider:"",prefix:"",name:""};return s.forEach(i=>{if(o.name===i.name&&o.prefix===i.prefix&&o.provider===i.provider)return;o=i;const a=i.provider,r=i.prefix,n=i.name,l=t[a]||(t[a]=Object.create(null)),u=l[r]||(l[r]=Q(a,r));let b;n in u.icons?b=e.loaded:r===""||u.missing.has(n)?b=e.missing:b=e.pending;const g={provider:a,prefix:r,name:n};b.push(g)}),e}function qo(s,e){s.forEach(t=>{const o=t.loaderCallbacks;o&&(t.loaderCallbacks=o.filter(i=>i.id!==e))})}function na(s){s.pendingCallbacksFlag||(s.pendingCallbacksFlag=!0,setTimeout(()=>{s.pendingCallbacksFlag=!1;const e=s.loaderCallbacks?s.loaderCallbacks.slice(0):[];if(!e.length)return;let t=!1;const o=s.provider,i=s.prefix;e.forEach(a=>{const r=a.icons,n=r.pending.length;r.pending=r.pending.filter(l=>{if(l.prefix!==i)return!0;const u=l.name;if(s.icons[u])r.loaded.push({provider:o,prefix:i,name:u});else if(s.missing.has(u))r.missing.push({provider:o,prefix:i,name:u});else return t=!0,!0;return!1}),r.pending.length!==n&&(t||qo([s],a.id),a.callback(r.loaded.slice(0),r.missing.slice(0),r.pending.slice(0),a.abort))})}))}let la=0;function ca(s,e,t){const o=la++,i=qo.bind(null,t,o);if(!e.pending.length)return i;const a={id:o,icons:e,callback:s,abort:i};return t.forEach(r=>{(r.loaderCallbacks||(r.loaderCallbacks=[])).push(a)}),i}const $t=Object.create(null);function Bo(s,e){$t[s]=e}function xt(s){return $t[s]||$t[""]}function da(s,e=!0,t=!1){const o=[];return s.forEach(i=>{const a=typeof i=="string"?Se(i,e,t):i;a&&o.push(a)}),o}var ua={resources:[],index:0,timeout:2e3,rotate:750,random:!1,dataAfterTimeout:!1};function ha(s,e,t,o){const i=s.resources.length,a=s.random?Math.floor(Math.random()*i):s.index;let r;if(s.random){let E=s.resources.slice(0);for(r=[];E.length>1;){const D=Math.floor(Math.random()*E.length);r.push(E[D]),E=E.slice(0,D).concat(E.slice(D+1))}r=r.concat(E)}else r=s.resources.slice(a).concat(s.resources.slice(0,a));const n=Date.now();let l="pending",u=0,b,g=null,v=[],y=[];typeof o=="function"&&y.push(o);function _(){g&&(clearTimeout(g),g=null)}function C(){l==="pending"&&(l="aborted"),_(),v.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),v=[]}function R(E,D){D&&(y=[]),typeof E=="function"&&y.push(E)}function I(){return{startTime:n,payload:e,status:l,queriesSent:u,queriesPending:v.length,subscribe:R,abort:C}}function L(){l="failed",y.forEach(E=>{E(void 0,b)})}function Ce(){v.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),v=[]}function Ze(E,D,ne){const Y=D!=="success";switch(v=v.filter(P=>P!==E),l){case"pending":break;case"failed":if(Y||!s.dataAfterTimeout)return;break;default:return}if(D==="abort"){b=ne,L();return}if(Y){b=ne,v.length||(r.length?X():L());return}if(_(),Ce(),!s.random){const P=s.resources.indexOf(E.resource);P!==-1&&P!==s.index&&(s.index=P)}l="completed",y.forEach(P=>{P(ne)})}function X(){if(l!=="pending")return;_();const E=r.shift();if(E===void 0){if(v.length){g=setTimeout(()=>{_(),l==="pending"&&(Ce(),L())},s.timeout);return}L();return}const D={status:"pending",resource:E,callback:(ne,Y)=>{Ze(D,ne,Y)}};v.push(D),u++,g=setTimeout(X,s.rotate),t(E,e,D.callback)}return setTimeout(X),I}function Vo(s){const e={...ua,...s};let t=[];function o(){t=t.filter(n=>n().status==="pending")}function i(n,l,u){const b=ha(e,n,l,(g,v)=>{o(),u&&u(g,v)});return t.push(b),b}function a(n){return t.find(l=>n(l))||null}return{query:i,find:a,setIndex:n=>{e.index=n},getIndex:()=>e.index,cleanup:o}}function kt(s){let e;if(typeof s.resources=="string")e=[s.resources];else if(e=s.resources,!(e instanceof Array)||!e.length)return null;return{resources:e,path:s.path||"/",maxURL:s.maxURL||500,rotate:s.rotate||750,timeout:s.timeout||5e3,random:s.random===!0,index:s.index||0,dataAfterTimeout:s.dataAfterTimeout!==!1}}const Be=Object.create(null),Ae=["https://api.simplesvg.com","https://api.unisvg.com"],Ve=[];for(;Ae.length>0;)Ae.length===1||Math.random()>.5?Ve.push(Ae.shift()):Ve.push(Ae.pop());Be[""]=kt({resources:["https://api.iconify.design"].concat(Ve)});function Ho(s,e){const t=kt(e);return t===null?!1:(Be[s]=t,!0)}function He(s){return Be[s]}function pa(){return Object.keys(Be)}function Go(){}const St=Object.create(null);function fa(s){if(!St[s]){const e=He(s);if(!e)return;const t=Vo(e),o={config:e,redundancy:t};St[s]=o}return St[s]}function Wo(s,e,t){let o,i;if(typeof s=="string"){const a=xt(s);if(!a)return t(void 0,424),Go;i=a.send;const r=fa(s);r&&(o=r.redundancy)}else{const a=kt(s);if(a){o=Vo(a);const r=s.resources?s.resources[0]:"",n=xt(r);n&&(i=n.send)}}return!o||!i?(t(void 0,424),Go):o.query(e,i,t)().abort}const Ko="iconify2",Oe="iconify",Zo=Oe+"-count",Jo=Oe+"-version",Qo=36e5,ba=168;function Et(s,e){try{return s.getItem(e)}catch{}}function Tt(s,e,t){try{return s.setItem(e,t),!0}catch{}}function Xo(s,e){try{s.removeItem(e)}catch{}}function At(s,e){return Tt(s,Zo,e.toString())}function Ot(s){return parseInt(Et(s,Zo))||0}const re={local:!0,session:!0},Yo={local:new Set,session:new Set};let Ct=!1;function ga(s){Ct=s}let Ge=typeof window>"u"?{}:window;function ei(s){const e=s+"Storage";try{if(Ge&&Ge[e]&&typeof Ge[e].length=="number")return Ge[e]}catch{}re[s]=!1}function ti(s,e){const t=ei(s);if(!t)return;const o=Et(t,Jo);if(o!==Ko){if(o){const n=Ot(t);for(let l=0;l<n;l++)Xo(t,Oe+l.toString())}Tt(t,Jo,Ko),At(t,0);return}const i=Math.floor(Date.now()/Qo)-ba,a=n=>{const l=Oe+n.toString(),u=Et(t,l);if(typeof u=="string"){try{const b=JSON.parse(u);if(typeof b=="object"&&typeof b.cached=="number"&&b.cached>i&&typeof b.provider=="string"&&typeof b.data=="object"&&typeof b.data.prefix=="string"&&e(b,n))return!0}catch{}Xo(t,l)}};let r=Ot(t);for(let n=r-1;n>=0;n--)a(n)||(n===r-1?(r--,At(t,r)):Yo[s].add(n))}function oi(){if(!Ct){ga(!0);for(const s in re)ti(s,e=>{const t=e.data,o=e.provider,i=t.prefix,a=Q(o,i);if(!_t(a,t).length)return!1;const r=t.lastModified||-1;return a.lastModifiedCached=a.lastModifiedCached?Math.min(a.lastModifiedCached,r):r,!0})}}function ma(s,e){const t=s.lastModifiedCached;if(t&&t>=e)return t===e;if(s.lastModifiedCached=e,t)for(const o in re)ti(o,i=>{const a=i.data;return i.provider!==s.provider||a.prefix!==s.prefix||a.lastModified===e});return!0}function va(s,e){Ct||oi();function t(o){let i;if(!re[o]||!(i=ei(o)))return;const a=Yo[o];let r;if(a.size)a.delete(r=Array.from(a).shift());else if(r=Ot(i),!At(i,r+1))return;const n={cached:Math.floor(Date.now()/Qo),provider:s.provider,data:e};return Tt(i,Oe+r.toString(),JSON.stringify(n))}e.lastModified&&!ma(s,e.lastModified)||Object.keys(e.icons).length&&(e.not_found&&(e=Object.assign({},e),delete e.not_found),t("local")||t("session"))}function ii(){}function ya(s){s.iconsLoaderFlag||(s.iconsLoaderFlag=!0,setTimeout(()=>{s.iconsLoaderFlag=!1,na(s)}))}function wa(s,e){s.iconsToLoad?s.iconsToLoad=s.iconsToLoad.concat(e).sort():s.iconsToLoad=e,s.iconsQueueFlag||(s.iconsQueueFlag=!0,setTimeout(()=>{s.iconsQueueFlag=!1;const{provider:t,prefix:o}=s,i=s.iconsToLoad;delete s.iconsToLoad;let a;if(!i||!(a=xt(t)))return;a.prepare(t,o,i).forEach(n=>{Wo(t,n,l=>{if(typeof l!="object")n.icons.forEach(u=>{s.missing.add(u)});else try{const u=_t(s,l);if(!u.length)return;const b=s.pendingIcons;b&&u.forEach(g=>{b.delete(g)}),va(s,l)}catch(u){console.error(u)}ya(s)})})}))}const Lt=(s,e)=>{const t=da(s,!0,No()),o=ra(t);if(!o.pending.length){let l=!0;return e&&setTimeout(()=>{l&&e(o.loaded,o.missing,o.pending,ii)}),()=>{l=!1}}const i=Object.create(null),a=[];let r,n;return o.pending.forEach(l=>{const{provider:u,prefix:b}=l;if(b===n&&u===r)return;r=u,n=b,a.push(Q(u,b));const g=i[u]||(i[u]=Object.create(null));g[b]||(g[b]=[])}),o.pending.forEach(l=>{const{provider:u,prefix:b,name:g}=l,v=Q(u,b),y=v.pendingIcons||(v.pendingIcons=new Set);y.has(g)||(y.add(g),i[u][b].push(g))}),a.forEach(l=>{const{provider:u,prefix:b}=l;i[u][b].length&&wa(l,i[u][b])}),e?ca(e,o,a):ii},_a=s=>new Promise((e,t)=>{const o=typeof s=="string"?Se(s,!0):s;if(!o){t(s);return}Lt([o||s],i=>{if(i.length&&o){const a=Te(o);if(a){e({...xe,...a});return}}t(s)})});function $a(s){try{const e=typeof s=="string"?JSON.parse(s):s;if(typeof e.body=="string")return{...e}}catch{}}function xa(s,e){const t=typeof s=="string"?Se(s,!0,!0):null;if(!t){const a=$a(s);return{value:s,data:a}}const o=Te(t);if(o!==void 0||!t.prefix)return{value:s,name:t,data:o};const i=Lt([t],()=>e(s,t,Te(t)));return{value:s,name:t,loading:i}}function Pt(s){return s.hasAttribute("inline")}let si=!1;try{si=navigator.vendor.indexOf("Apple")===0}catch{}function ka(s,e){switch(e){case"svg":case"bg":case"mask":return e}return e!=="style"&&(si||s.indexOf("<a")===-1)?"svg":s.indexOf("currentColor")===-1?"bg":"mask"}const Sa=/(-?[0-9.]*[0-9]+[0-9.]*)/g,Ea=/^-?[0-9.]*[0-9]+[0-9.]*$/g;function It(s,e,t){if(e===1)return s;if(t=t||100,typeof s=="number")return Math.ceil(s*e*t)/t;if(typeof s!="string")return s;const o=s.split(Sa);if(o===null||!o.length)return s;const i=[];let a=o.shift(),r=Ea.test(a);for(;;){if(r){const n=parseFloat(a);isNaN(n)?i.push(a):i.push(Math.ceil(n*e*t)/t)}else i.push(a);if(a=o.shift(),a===void 0)return i.join("");r=!r}}function ai(s,e){const t={...xe,...s},o={...Io,...e},i={left:t.left,top:t.top,width:t.width,height:t.height};let a=t.body;[t,o].forEach(y=>{const _=[],C=y.hFlip,R=y.vFlip;let I=y.rotate;C?R?I+=2:(_.push("translate("+(i.width+i.left).toString()+" "+(0-i.top).toString()+")"),_.push("scale(-1 1)"),i.top=i.left=0):R&&(_.push("translate("+(0-i.left).toString()+" "+(i.height+i.top).toString()+")"),_.push("scale(1 -1)"),i.top=i.left=0);let L;switch(I<0&&(I-=Math.floor(I/4)*4),I=I%4,I){case 1:L=i.height/2+i.top,_.unshift("rotate(90 "+L.toString()+" "+L.toString()+")");break;case 2:_.unshift("rotate(180 "+(i.width/2+i.left).toString()+" "+(i.height/2+i.top).toString()+")");break;case 3:L=i.width/2+i.left,_.unshift("rotate(-90 "+L.toString()+" "+L.toString()+")");break}I%2===1&&(i.left!==i.top&&(L=i.left,i.left=i.top,i.top=L),i.width!==i.height&&(L=i.width,i.width=i.height,i.height=L)),_.length&&(a='<g transform="'+_.join(" ")+'">'+a+"</g>")});const r=o.width,n=o.height,l=i.width,u=i.height;let b,g;return r===null?(g=n===null?"1em":n==="auto"?u:n,b=It(g,l/u)):(b=r==="auto"?l:r,g=n===null?It(b,u/l):n==="auto"?u:n),{attributes:{width:b.toString(),height:g.toString(),viewBox:i.left.toString()+" "+i.top.toString()+" "+l.toString()+" "+u.toString()},body:a}}let We=(()=>{let s;try{if(s=fetch,typeof s=="function")return s}catch{}})();function Ta(s){We=s}function Aa(){return We}function Oa(s,e){const t=He(s);if(!t)return 0;let o;if(!t.maxURL)o=0;else{let i=0;t.resources.forEach(r=>{i=Math.max(i,r.length)});const a=e+".json?icons=";o=t.maxURL-i-t.path.length-a.length}return o}function Ca(s){return s===404}const La=(s,e,t)=>{const o=[],i=Oa(s,e),a="icons";let r={type:a,provider:s,prefix:e,icons:[]},n=0;return t.forEach((l,u)=>{n+=l.length+1,n>=i&&u>0&&(o.push(r),r={type:a,provider:s,prefix:e,icons:[]},n=l.length),r.icons.push(l)}),o.push(r),o};function Pa(s){if(typeof s=="string"){const e=He(s);if(e)return e.path}return"/"}const Ia={prepare:La,send:(s,e,t)=>{if(!We){t("abort",424);return}let o=Pa(e.provider);switch(e.type){case"icons":{const a=e.prefix,n=e.icons.join(","),l=new URLSearchParams({icons:n});o+=a+".json?"+l.toString();break}case"custom":{const a=e.uri;o+=a.slice(0,1)==="/"?a.slice(1):a;break}default:t("abort",400);return}let i=503;We(s+o).then(a=>{const r=a.status;if(r!==200){setTimeout(()=>{t(Ca(r)?"abort":"next",r)});return}return i=501,a.json()}).then(a=>{if(typeof a!="object"||a===null){setTimeout(()=>{a===404?t("abort",a):t("next",i)});return}setTimeout(()=>{t("success",a)})}).catch(()=>{t("next",i)})}};function ri(s,e){switch(s){case"local":case"session":re[s]=e;break;case"all":for(const t in re)re[t]=e;break}}function ni(){Bo("",Ia),No(!0);let s;try{s=window}catch{}if(s){if(oi(),s.IconifyPreload!==void 0){const t=s.IconifyPreload,o="Invalid IconifyPreload syntax.";typeof t=="object"&&t!==null&&(t instanceof Array?t:[t]).forEach(i=>{try{(typeof i!="object"||i===null||i instanceof Array||typeof i.icons!="object"||typeof i.prefix!="string"||!Uo(i))&&console.error(o)}catch{console.error(o)}})}if(s.IconifyProviders!==void 0){const t=s.IconifyProviders;if(typeof t=="object"&&t!==null)for(const o in t){const i="IconifyProviders["+o+"] is invalid.";try{const a=t[o];if(typeof a!="object"||!a||a.resources===void 0)continue;Ho(o,a)||console.error(i)}catch{console.error(i)}}}}return{enableCache:t=>ri(t,!0),disableCache:t=>ri(t,!1),iconExists:sa,getIcon:aa,listIcons:ia,addIcon:Fo,addCollection:Uo,calculateSize:It,buildIcon:ai,loadIcons:Lt,loadIcon:_a,addAPIProvider:Ho,_api:{getAPIConfig:He,setAPIModule:Bo,sendAPIQuery:Wo,setFetch:Ta,getFetch:Aa,listAPIProviders:pa}}}function li(s,e){let t=s.indexOf("xlink:")===-1?"":' xmlns:xlink="http://www.w3.org/1999/xlink"';for(const o in e)t+=" "+o+'="'+e[o]+'"';return'<svg xmlns="http://www.w3.org/2000/svg"'+t+">"+s+"</svg>"}function Ma(s){return s.replace(/"/g,"'").replace(/%/g,"%25").replace(/#/g,"%23").replace(/</g,"%3C").replace(/>/g,"%3E").replace(/\s+/g," ")}function ja(s){return'url("data:image/svg+xml,'+Ma(s)+'")'}const Mt={"background-color":"currentColor"},ci={"background-color":"transparent"},di={image:"var(--svg)",repeat:"no-repeat",size:"100% 100%"},ui={"-webkit-mask":Mt,mask:Mt,background:ci};for(const s in ui){const e=ui[s];for(const t in di)e[s+"-"+t]=di[t]}function hi(s){return s+(s.match(/^[-0-9.]+$/)?"px":"")}function Da(s,e,t){const o=document.createElement("span");let i=s.body;i.indexOf("<a")!==-1&&(i+="<!-- "+Date.now()+" -->");const a=s.attributes,r=li(i,{...a,width:e.width+"",height:e.height+""}),n=ja(r),l=o.style,u={"--svg":n,width:hi(a.width),height:hi(a.height),...t?Mt:ci};for(const b in u)l.setProperty(b,u[b]);return o}function za(s){const e=document.createElement("span");return e.innerHTML=li(s.body,s.attributes),e.firstChild}function pi(s,e){const t=e.icon.data,o=e.customisations,i=ai(t,o);o.preserveAspectRatio&&(i.attributes.preserveAspectRatio=o.preserveAspectRatio);const a=e.renderedMode;let r;switch(a){case"svg":r=za(i);break;default:r=Da(i,{...xe,...t},a==="mask")}const n=Array.from(s.childNodes).find(l=>{const u=l.tagName&&l.tagName.toUpperCase();return u==="SPAN"||u==="SVG"});n?r.tagName==="SPAN"&&n.tagName===r.tagName?n.setAttribute("style",r.getAttribute("style")):s.replaceChild(r,n):s.appendChild(r)}const jt="data-style";function fi(s,e){let t=Array.from(s.childNodes).find(o=>o.hasAttribute&&o.hasAttribute(jt));t||(t=document.createElement("style"),t.setAttribute(jt,jt),s.appendChild(t)),t.textContent=":host{display:inline-block;vertical-align:"+(e?"-0.125em":"0")+"}span,svg{display:block}"}function bi(s,e,t){const o=t&&(t.rendered?t:t.lastRender);return{rendered:!1,inline:e,icon:s,lastRender:o}}function Ra(s="iconify-icon"){let e,t;try{e=window.customElements,t=window.HTMLElement}catch{return}if(!e||!t)return;const o=e.get(s);if(o)return o;const i=["icon","mode","inline","width","height","rotate","flip"],a=class extends t{constructor(){super();Ye(this,"_shadowRoot");Ye(this,"_state");Ye(this,"_checkQueued",!1);const l=this._shadowRoot=this.attachShadow({mode:"open"}),u=Pt(this);fi(l,u),this._state=bi({value:""},u),this._queueCheck()}static get observedAttributes(){return i.slice(0)}attributeChangedCallback(l){if(l==="inline"){const u=Pt(this),b=this._state;u!==b.inline&&(b.inline=u,fi(this._shadowRoot,u))}else this._queueCheck()}get icon(){const l=this.getAttribute("icon");if(l&&l.slice(0,1)==="{")try{return JSON.parse(l)}catch{}return l}set icon(l){typeof l=="object"&&(l=JSON.stringify(l)),this.setAttribute("icon",l)}get inline(){return Pt(this)}set inline(l){this.setAttribute("inline",l?"true":null)}restartAnimation(){const l=this._state;if(l.rendered){const u=this._shadowRoot;if(l.renderedMode==="svg")try{u.lastChild.setCurrentTime(0);return}catch{}pi(u,l)}}get status(){const l=this._state;return l.rendered?"rendered":l.icon.data===null?"failed":"loading"}_queueCheck(){this._checkQueued||(this._checkQueued=!0,setTimeout(()=>{this._check()}))}_check(){if(!this._checkQueued)return;this._checkQueued=!1;const l=this._state,u=this.getAttribute("icon");if(u!==l.icon.value){this._iconChanged(u);return}if(!l.rendered)return;const b=this.getAttribute("mode"),g=jo(this);(l.attrMode!==b||Js(l.customisations,g))&&this._renderIcon(l.icon,g,b)}_iconChanged(l){const u=xa(l,(b,g,v)=>{const y=this._state;if(y.rendered||this.getAttribute("icon")!==b)return;const _={value:b,name:g,data:v};_.data?this._gotIconData(_):y.icon=_});u.data?this._gotIconData(u):this._state=bi(u,this._state.inline,this._state)}_gotIconData(l){this._checkQueued=!1,this._renderIcon(l,jo(this),this.getAttribute("mode"))}_renderIcon(l,u,b){const g=ka(l.data.body,b),v=this._state.inline;pi(this._shadowRoot,this._state={rendered:!0,icon:l,inline:v,customisations:u,attrMode:b,renderedMode:g})}};i.forEach(n=>{n in a.prototype||Object.defineProperty(a.prototype,n,{get:function(){return this.getAttribute(n)},set:function(l){this.setAttribute(n,l)}})});const r=ni();for(const n in r)a[n]=a.prototype[n]=r[n];return e.define(s,a),a}const Na=Ra()||ni(),{enableCache:Nn,disableCache:Fn,iconExists:Un,getIcon:qn,listIcons:Bn,addIcon:Vn,addCollection:Hn,calculateSize:Gn,buildIcon:Wn,loadIcons:Kn,loadIcon:Zn,addAPIProvider:Jn,_api:Qn}=Na;class gi extends z{static get styles(){return S`
      :root {
        font-size: inherit;
        color: inherit;
        display: inline-flex;
        width: fit-content;
        height: fit-content;
        position: relative;
      }
      .tooltip {
        position: absolute;
        right: 20px;
        top: -50%;
        min-width: max-content;
        border: solid 1px currentcolor;
        background-color: var(--dt-form-background-color, var(--surface-1));
        padding: .25rem;
        border-radius: .25rem;
        text-align: end;
        z-index: 1;
        display:block;
      }
      .tooltip:before {
        position: absolute;
        right: .7rem;
        top: 1.45rem;
        content: " ";
        border-width: .25rem;
        border-style: solid;
        border-color: transparent transparent currentcolor transparent;
      }
      .tooltip[hidden] {
        display: none;
      }
    `}static get properties(){return{...super.properties,icon:{type:String},tooltip:{type:String},tooltip_open:{type:Boolean},size:{type:String}}}_showTooltip(){this.tooltip_open?this.tooltip_open=!1:this.tooltip_open=!0}render(){const e=this.tooltip?d`<div class="tooltip" ?hidden=${this.tooltip_open}>${this.tooltip}</div>`:null;return d`
      <iconify-icon icon=${this.icon} width="${this.size}" @click=${this._showTooltip}></iconify-icon>
      ${e}
    `}}window.customElements.define("dt-icon",gi);class mi extends z{static get properties(){return{...super.properties,title:{type:String},isOpen:{type:Boolean},canEdit:{type:Boolean,state:!0},metadata:{type:Object},center:{type:Array},mapboxToken:{type:String,attribute:"mapbox-token"}}}static get styles(){return[S`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `]}constructor(){super(),this.addEventListener("open",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")),this.isOpen=!0}),this.addEventListener("close",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")),this.isOpen=!1})}connectedCallback(){if(super.connectedCallback(),this.canEdit=!this.metadata,window.mapboxgl)this.initMap();else{let e=document.createElement("script");e.src="https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js",e.onload=this.initMap.bind(this),document.body.appendChild(e),console.log("injected script")}}initMap(){if(!this.isOpen||!window.mapboxgl||!this.mapboxToken)return;const e=this.shadowRoot.querySelector("#map");if(e&&!this.map){this.map=new window.mapboxgl.Map({accessToken:this.mapboxToken,container:e,style:"mapbox://styles/mapbox/streets-v12",minZoom:1}),this.map.on("load",()=>this.map.resize()),this.center&&this.center.length&&(this.map.setCenter(this.center),this.map.setZoom(15));const t=new mapboxgl.NavigationControl;this.map.addControl(t,"bottom-right"),this.addPinFromMetadata(),this.map.on("click",o=>{this.canEdit&&(this.marker?this.marker.setLngLat(o.lngLat):this.marker=new mapboxgl.Marker().setLngLat(o.lngLat).addTo(this.map))})}}addPinFromMetadata(){if(this.metadata){const{lng:e,lat:t,level:o}=this.metadata;let i=15;o==="admin0"?i=3:o==="admin1"?i=6:o==="admin2"&&(i=10),this.map&&(this.map.setCenter([e,t]),this.map.setZoom(i),this.marker=new mapboxgl.Marker().setLngLat([e,t]).addTo(this.map))}}updated(e){window.mapboxgl&&(e.has("metadata")&&this.metadata&&this.metadata.lat&&this.addPinFromMetadata(),e.has("isOpen")&&this.isOpen&&this.initMap())}onClose(e){var t;((t=e==null?void 0:e.detail)==null?void 0:t.action)==="button"&&this.marker&&this.dispatchEvent(new CustomEvent("submit",{detail:{location:this.marker.getLngLat()}}))}render(){var e;return d`      
      <dt-modal
        .title=${(e=this.metadata)==null?void 0:e.label}
        ?isopen=${this.isOpen}
        hideButton
        @close=${this.onClose}
      >
        <div slot="content">
          <div class="map" id="map"></div>
        </div>
       
        ${this.canEdit?d`<div slot="close-button">${A("Save")}</div>`:null}
      </dt-modal>
      
      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `}}window.customElements.define("dt-map-modal",mi);class Fa extends U{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[S`
        :host {
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
          display: block;
        }

        .input-group {
          color: var(--dt-multi-select-text-color, #0a0a0a);
          margin-bottom: 1rem;
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
          color: var(--dt-multi-select-text-color, #0a0a0a);
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
          background: var(--dt-multi-select-option-hover-background, #f5f5f5);
        }
      `,S`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-location-map-background-color, #fefefe);
          border: 1px solid var(--dt-location-map-border-color, #fefefe);
          border-radius: var(--dt-location-map-border-radius, 0);
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
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }

        .field-container {
          display: flex;
          margin-bottom: 0.5rem;
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
          border: solid 1px gray;
          border-collapse: collapse;
          color: var(--dt-location-map-button-color, #cc4b37);
          background-color: var(--dt-location-map-background-color, buttonface);
          border: 1px solid var(--dt-location-map-border-color, #fefefe);
          border-radius: var(--dt-location-map-border-radius, 0);
          box-shadow: var(
            --dt-location-map-box-shadow,
            var(
              --dt-form-input-box-shadow,
              inset 0 1px 2px hsl(0deg 0% 4% / 10%)
            )
          );
        }
        .field-container .input-addon:hover {
          background-color: var(--dt-location-map-button-hover-background-color, #cc4b37);
          color: var(--dt-location-map-button-hover-color, #ffffff);
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
      `,S`
        /* === Inline Icons === */
        .icon-overlay {
          position: absolute;
          inset-inline-end: 1rem;
          top: 0;
          inset-inline-end: 3rem;
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
      `]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",async()=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()}),this.mapboxToken&&(this.mapboxService=new Vs(this.mapboxToken)),this.googleToken&&(this.googleGeocodeService=new Hs(this.googleToken,window,document))}disconnectedCallback(){super.disconnectedCallback(),this.removeEventListener("autofocus",this.handleAutofocus)}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const o=t.offsetTop,i=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;i>r?e.scrollTo({top:i-e.clientHeight,behavior:"smooth"}):o<a&&e.scrollTo({top:o,behavior:"smooth"})}}_clickOption(e){const t=e.currentTarget??e.target;t&&t.value&&this._select(JSON.parse(t.value))}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query}))}async _select(e){if(e.place_id&&this.googleGeocodeService){this.loading=!0;const i=await this.googleGeocodeService.getPlaceDetails(e.label,this.locale);this.loading=!1,i&&(e.lat=i.geometry.location.lat,e.lng=i.geometry.location.lng,e.level=i.types&&i.types.length?i.types[0]:null)}const t={detail:{metadata:e},bubbles:!1};this.dispatchEvent(new CustomEvent("select",t)),this.metadata=e;const o=this.shadowRoot.querySelector("input");o&&(o.value=e==null?void 0:e.label),this.open=!1,this.activeIndex=-1}get _focusTarget(){let e=this._field;return this.metadata&&(e=this.shadowRoot.querySelector("button")||e),e}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1)}_inputKeyDown(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){const t=e.keyCode||e.which,o=[9,13];e.target.value&&!o.includes(t)&&(this.open=!0),this.query=e.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query){if(this.googleToken&&this.googleGeocodeService){this.loading=!0;try{const e=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(e||[]).map(t=>({label:t.description,place_id:t.place_id,source:"user",raw:t})),this.loading=!1}catch(e){console.error(e),this.error=!0,this.loading=!1;return}}else if(this.mapboxToken&&this.mapboxService){this.loading=!0;const e=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=e.map(t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"})),this.loading=!1}}return this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e&&(e.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout(()=>this._filterOptions(),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length)){const o=this.shadowRoot.querySelector(".input-group");o&&(this.containerHeight=o.offsetHeight)}}_change(){}_delete(){const e={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",e))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(e){var t,o;if((o=(t=e==null?void 0:e.detail)==null?void 0:t.location)!=null&&o.lat){const{location:i}=e==null?void 0:e.detail,{lat:a,lng:r}=i;if(this.googleGeocodeService){const n=await this.googleGeocodeService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.geometry.location.lng,lat:l.geometry.location.lat,level:l.types&&l.types.length?l.types[0]:null,label:l.formatted_address,source:"user"})}}else if(this.mapboxService){const n=await this.mapboxService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.center[0],lat:l.center[1],level:l.place_type[0],label:l.place_name,source:"user"})}}}}_renderOption(e,t,o){return d`
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
          ${o??e.label}
        </button>
      </li>
    `}_renderOptions(){let e=[];return this.filteredOptions.length?e.push(...this.filteredOptions.map((t,o)=>this._renderOption(t,o))):this.loading?e.push(d`<li><div>${A("Loading...")}</div></li>`):e.push(d`<li><div>${A("No Data Available")}</div></li>`),e.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,d`<strong>${A("Use")}: "${this.query}"</strong>`)),e}render(){var i,a,r,n;const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},t=!!((i=this.metadata)!=null&&i.label),o=((a=this.metadata)==null?void 0:a.lat)&&((r=this.metadata)==null?void 0:r.lng);return d`
      <div class="input-group">
        <div class="field-container">
          <input
            type="text"
            class="${this.disabled?"disabled":null}"
            placeholder="${this.placeholder}"
            .value="${((n=this.metadata)==null?void 0:n.label)??""}"
            .disabled=${t&&o||this.disabled}
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
          />

          ${t&&o?d`
          <button
            class="input-addon btn-map"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:map"></dt-icon>
          </button>
          `:null}
          ${t?d`
          <button
            class="input-addon btn-delete"
            @click=${this._delete}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:trash-can-outline"></dt-icon>
          </button>
          `:d`
          <button
            class="input-addon btn-pin"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:map-marker-radius"></dt-icon>
          </button>
          `}
        </div>
        <ul class="option-list" style=${se(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched&&this.invalid||this.error?d`<dt-exclamation-circle class="icon-overlay alert"></dt-exclamation-circle>`:null}
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>

      <dt-map-modal
        .metadata=${this.metadata}
        mapbox-token="${this.mapboxToken}"
        @submit=${this._onMapModalSubmit}
      ></dt-map-modal>

`}}window.customElements.define("dt-location-map-item",Fa);class vi extends j{static get properties(){return{...super.properties,placeholder:{type:String},value:{type:Array,reflect:!0},locations:{type:Array,state:!0},open:{type:Boolean,state:!0},limit:{type:Number,attribute:"limit"},onchange:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"}}}static get styles(){return[...super.styles,S`
        :host {
          font-family: Helvetica, Arial, sans-serif;
        }
        .input-group {
          display: flex;
        }

        .field-container {
          position: relative;
        }
      `]}constructor(){super(),this.limit=0,this.value=[],this.locations=[{id:Date.now()}]}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e))}willUpdate(...e){super.willUpdate(...e),this.value&&this.value.filter(t=>!t.id)&&(this.value=[...this.value.map(t=>({...t,id:t.grid_meta_id}))]),this.updateLocationList()}firstUpdated(...e){super.firstUpdated(...e),this.internals.setFormValue(JSON.stringify(this.value))}updated(e){var t,o;if(e.has("value")){const i=e.get("value");i&&(i==null?void 0:i.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewLocation()}if(e.has("locations")){const i=e.get("locations");i&&(i==null?void 0:i.length)!==((o=this.locations)==null?void 0:o.length)&&this.focusNewLocation()}}focusNewLocation(){const e=this.shadowRoot.querySelectorAll("dt-location-map-item");e&&e.length&&e[e.length-1].dispatchEvent(new Event("autofocus"))}updateLocationList(){if(!this.disabled&&(this.open||!this.value||!this.value.length)){this.open=!0;const e=(this.value||[]).filter(o=>o.label),t=this.limit===0||e.length<this.limit;this.locations=[...e,...t?[{id:Date.now()}]:[]]}else this.locations=[...(this.value||[]).filter(e=>e.label)]}selectLocation(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),o={...e.detail.metadata,id:Date.now()};this.value=[...(this.value||[]).filter(i=>i.label),o],this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}deleteItem(e){var a;const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),o=(a=e.detail)==null?void 0:a.metadata,i=o==null?void 0:o.grid_meta_id;i?this.value=(this.value||[]).filter(r=>r.grid_meta_id!==i):this.value=(this.value||[]).filter(r=>r.lat!==o.lat&&r.lng!==o.lng),this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}addNew(){const e=(this.value||[]).filter(t=>t.label);(this.limit===0||e.length<this.limit)&&(this.open=!0,this.updateLocationList())}renderItem(e){return d`
      <dt-location-map-item
        placeholder="${this.placeholder}"
        .metadata=${e}
        mapbox-token="${this.mapboxToken}"
        google-token="${this.googleToken}"
        @delete=${this.deleteItem}
        @select=${this.selectLocation}
        ?disabled=${this.disabled}
      ></dt-location-map-item>
    `}render(){return[...this.value||[]],d`
      ${this.labelTemplate()}

      ${_e(this.locations||[],e=>e.id,(e,t)=>this.renderItem(e,t))}
      ${!this.open&&(this.limit==0||this.locations.length<this.limit)?d`<button @click="${this.addNew}">Add New</button>`:null}
    `}}window.customElements.define("dt-location-map",vi);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ue=s=>s??T;class yi extends j{static get styles(){return[...super.styles,S`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-form-background-color, #fff);
          border: 1px solid var(--dt-form-border-color, #ccc);
          border-radius: 0;
          box-shadow: var(
            --dt-form-input-box-shadow,
            inset 0 1px 2px hsl(0deg 0% 4% / 10%)
          );
          box-sizing: border-box;
          display: block;
          font-family: inherit;
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0 0 1.0666666667rem;
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
          background-color: var(--dt-form-disabled-background-color, #e6e6e6);
          cursor: not-allowed;
        }
        input:invalid {
          border-color: var(--dt-form-invalid-border-color, #dc3545);
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},oldValue:{type:String},min:{type:Number},max:{type:Number},loading:{type:Boolean},saved:{type:Boolean},onchange:{type:String}}}connectedCallback(){super.connectedCallback(),this.oldValue=this.value}_checkValue(e){return!(e<this.min||e>this.max)}async onChange(e){if(this._checkValue(e.target.value)){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value},bubbles:!0,composed:!0});this.value=e.target.value,this._field.setCustomValidity(""),this.dispatchEvent(t),this.api=new Re(this.nonce,`${this.apiRoot}`)}else e.currentTarget.value=""}handleError(e="An error occurred."){let t=e;t instanceof Error?(console.error(t),t=t.message):console.error(t),this.error=t,this._field.setCustomValidity(t),this.invalid=!0,this.value=this.oldValue}render(){return d`
      ${this.labelTemplate()}

      <input
        id="${this.id}"
        name="${this.name}"
        aria-label="${this.label}"
        type="number"
        ?disabled=${this.disabled}
        class="text-input"
        .value="${this.value}"
        min="${ue(this.min)}"
        max="${ue(this.max)}"
        @change=${this.onChange}
      />
    `}}window.customElements.define("dt-number",yi);class wi extends j{static get styles(){return[...super.styles,S`
        :host {
          position: relative;
        }

        select {
          appearance: none;
          background-color: var(--dt-form-background-color, #fefefe);
          background-image: linear-gradient(
              45deg,
              transparent 50%,
              var(--dt-single-select-text-color) 50%
            ),
            linear-gradient(
              135deg,
              var(--dt-single-select-text-color) 50%,
              transparent 50%
            );
          background-position: calc(100% - 20px) calc(1em + 2px),
            calc(100% - 15px) calc(1em + 2px), calc(100% - 2.5em) 0.5em;
          background-size: 5px 5px, 5px 5px, 1px 1.5em;
          background-repeat: no-repeat;
          border: 1px solid var(--dt-form-border-color, #cacaca);
          border-radius: 0;
          color: var(--dt-single-select-text-color, #0a0a0a);
          font-family: var(--font-family, sans-serif);
          font-size: 1rem;
          font-weight: 300;
          height: 2.5rem;
          line-height: 1.5;
          margin: 0;
          padding: 0.53rem;
          padding-inline-end: 1.6rem;
          transition: border-color 0.25s ease-in-out;
          transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
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
          background-position: 15px calc(1em + 2px), 20px calc(1em + 2px),
            2.5em 0.5em;
        }
        select.color-select {
          background-image: linear-gradient(
              45deg,
              transparent 50%,
              currentColor 50%
            ),
            linear-gradient(
              135deg,
              currentColor 50%,
              transparent 50%
            );
          background-color: var(--dt-form-border-color, #cacaca);
          border: none;
          border-radius: 10px;
          color: var(--dt-single-select-text-color-inverse, #fff);
          font-weight: 700;
          text-shadow: rgb(0 0 0 / 45%) 0 0 6px;
        }

        .icon-overlay {
          height: 2.5rem;
          inset-inline-end: 2.5rem;
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},value:{type:String,reflect:!0},color:{type:String,state:!0},onchange:{type:String}}}updateColor(){if(this.value&&this.options){const e=this.options.filter(t=>t.id===this.value);e&&e.length&&(this.color=e[0].color)}}isColorSelect(){return(this.options||[]).reduce((e,t)=>e||t.color,!1)}willUpdate(e){super.willUpdate(e),e.has("value")&&this.updateColor()}_change(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}render(){return d`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}" dir="${this.RTL?"rtl":"ltr"}">
        <select
          name="${this.name}"
          aria-label="${this.name}"
          @change="${this._change}"
          class="${this.isColorSelect()?"color-select":""}"
          style="background-color: ${this.color};"
          ?disabled="${this.disabled}"
        >
          <option disabled selected hidden value="">${this.placeholder}</option>

          ${this.options&&this.options.map(e=>d`
              <option value="${e.id}" ?selected="${e.id===this.value}">
                ${e.label}
              </option>
            `)}
        </select>
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-single-select",wi);class Ua extends U{static get styles(){return S`
      svg use {
        fill: currentcolor;
      }
    `}render(){return d`
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        version="1.1"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:xlink="http://www.w3.org/1999/xlink"
      >
        <g id="Canvas" transform="translate(1845 -2441)">
          <g id="alert-circle-exc">
            <g id="Group">
              <g id="Vector">
                <use
                  xlink:href="#path0_fill"
                  transform="translate(-1845 2441)"
                  fill="#000000"
                />
              </g>
            </g>
          </g>
        </g>
        <defs>
          <path
            id="path0_fill"
            d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 13.645 5L 13 14L 11 14L 10.392 5L 13.645 5ZM 12 20C 10.895 20 10 19.105 10 18C 10 16.895 10.895 16 12 16C 13.105 16 14 16.895 14 18C 14 19.105 13.105 20 12 20Z"
          />
        </defs>
      </svg>
    `}}window.customElements.define("dt-exclamation-circle",Ua);class Dt extends j{static get styles(){return[...super.styles,S`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-text-background-color, #fefefe);
          border: 1px solid var(--dt-text-border-color, #fefefe);
          border-radius: var(--dt-text-border-radius, 0);
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
          color: var(--dt-text-placeholder-color, #999);
          text-transform: var(--dt-text-placeholder-transform, none);
          font-size: var(--dt-text-placeholder-font-size, 1rem);
          font-weight: var(--dt-text-placeholder-font-weight, 400);
          letter-spacing: var(--dt-text-placeholder-letter-spacing, normal);
        }
        input.invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,id:{type:String},type:{type:String},placeholder:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const o=this.internals.form.querySelector("button[type=submit]");o&&o.click()}}_validateRequired(){const{value:e}=this,t=this.shadowRoot.querySelector("input");e===""&&this.required?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",t)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return d`
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
          class="${F(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
        />

        ${this.touched&&this.invalid?d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>`:null}
        ${this.error?d`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-text",Dt);class _i extends j{static get styles(){return[...super.styles,S`
        textarea {
          color: var(--dt-textarea-text-color, #0a0a0a);
          appearance: none;
          background-color: var(--dt-textarea-background-color, #fefefe);
          border: 1px solid var(--dt-textarea-border-color, #cecece);
          border-radius: 3px;
          box-shadow: var(
            --dt-textarea-input-box-shadow,
            inset 0 1px 2px hsl(0deg 0% 4% / 10%)
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
          overflow: hidden;
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
            #e6e6e6
          );
          cursor: not-allowed;
        }

        .icon-overlay {
          align-items: flex-start;
          padding-block: 1rem;
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return d`
      ${this.labelTemplate()}

      <div class="input-group">
        <textarea
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          ?disabled=${this.disabled}
          class="${F(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
        ></textarea>

        ${this.touched&&this.invalid?d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>`:null}
        ${this.error?d`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-textarea",_i);class $i extends j{static get styles(){return[...super.styles,S`
        :host {
          display: inline-block;
        }

        .Toggle {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          position: relative;
          margin-bottom: 1em;
          cursor: pointer;
          gap: 1ch;
        }

        button.Toggle {
          border: 0;
          padding: 0;
          background-color: transparent;
          font: inherit;
        }

        .Toggle__input {
          position: absolute;
          opacity: 0;
          width: 100%;
          height: 100%;
        }

        .Toggle__display {
          --offset: 0.25em;
          --diameter: 1.2em;

          display: inline-flex;
          align-items: center;
          justify-content: space-around;
          box-sizing: content-box;
          width: calc(var(--diameter) * 2 + var(--offset) * 2);
          height: calc(var(--diameter) + var(--offset) * 2);
          border: 0.1em solid rgb(0 0 0 / 0.2);
          position: relative;
          border-radius: 100vw;
          background-color: var(--dt-toggle-background-color-off, #ecf5fc);
          transition: 250ms;
        }

        .Toggle__display::before {
          content: '';
          z-index: 2;
          position: absolute;
          top: 50%;
          left: var(--offset);
          box-sizing: border-box;
          width: var(--diameter);
          height: var(--diameter);
          border: 0.1em solid rgb(0 0 0 / 0.2);
          border-radius: 50%;
          background-color: white;
          transform: translate(0, -50%);
          will-change: transform;
          transition: inherit;
        }

        .Toggle:focus .Toggle__display,
        .Toggle__input:focus + .Toggle__display {
          outline: 1px dotted #212121;
          outline: 1px auto -webkit-focus-ring-color;
          outline-offset: 2px;
        }

        .Toggle:focus,
        .Toggle:focus:not(:focus-visible) .Toggle__display,
        .Toggle__input:focus:not(:focus-visible) + .Toggle__display {
          outline: 0;
        }

        .Toggle[aria-pressed='true'] .Toggle__display,
        .Toggle__input:checked + .Toggle__display {
          background-color: var(--primary-color);
        }

        .Toggle[aria-pressed='true'] .Toggle__display::before,
        .Toggle__input:checked + .Toggle__display::before {
          transform: translate(100%, -50%);
        }

        .Toggle[disabled] .Toggle__display,
        .Toggle__input:disabled + .Toggle__display {
          opacity: 0.6;
          filter: grayscale(40%);
          cursor: not-allowed;
        }
        [dir='rtl'] .Toggle__display::before {
          left: auto;
          right: var(--offset);
        }

        [dir='rtl'] .Toggle[aria-pressed='true'] + .Toggle__display::before,
        [dir='rtl'] .Toggle__input:checked + .Toggle__display::before {
          transform: translate(-100%, -50%);
        }

        .Toggle__icon {
          display: inline-block;
          width: 1em;
          height: 1em;
          color: inherit;
          fill: currentcolor;
          vertical-align: middle;
          overflow: hidden;
        }

        .Toggle__icon--cross {
          color: var(--alert-color);
          font-size: 65%;
        }

        .Toggle__icon--checkmark {
          color: var(--success-color);
        }
      `]}static get properties(){return{...super.properties,id:{type:String},checked:{type:Boolean,reflect:!0},onchange:{type:String},hideIcons:{type:Boolean,default:!0}}}constructor(){super(),this.hideIcons=!1}onChange(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.checked,newValue:e.target.checked}});this.checked=e.target.checked,this._setFormValue(this.checked),this.dispatchEvent(t)}render(){const e=d`<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--checkmark"><path d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z" fill="currentcolor" stroke="currentcolor" /></svg>`,t=d`<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--cross"><path d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z" fill="currentcolor" /></svg>`;return d`
      <label class="Toggle" for="${this.id}" dir="${this.RTL?"rtl":"ltr"}">
        ${this.label}
        <input
          type="checkbox"
          name="${this.id}"
          id="${this.id}"
          class="Toggle__input"
          ?checked=${this.checked}
          @click=${this.onChange}
          ?disabled=${this.disabled}
        />
        <span class="Toggle__display" hidden>
          ${this.hideIcons?d``:d` ${e} ${t} `}
        </span>
      </label>
    `}}window.customElements.define("dt-toggle",$i);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function qa(s,e,t){return s?e(s):t==null?void 0:t(s)}class xi extends Dt{static get styles(){return[...super.styles,S`
        :host {
          display: block;
        }
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-multi-text-background-color, #fefefe);
          border: 1px solid var(--dt-multi-text-border-color, #fefefe);
          border-radius: var(--dt-multi-text-border-radius, 0);
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
          height: auto;
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
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }

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
          flex-shrink: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          aspect-ratio: 1/1;
          padding: 10px;
          border: solid 1px gray;
          border-collapse: collapse;
          background-color: var(--dt-multi-text-background-color, buttonface);
          border: 1px solid var(--dt-multi-text-border-color, #fefefe);
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
          color: var(--alert-color, #cc4b37);
          &:disabled {
            color: var(--dt-text-placeholder-color, #999);
          }
          &:hover:not([disabled]) {
            background-color: var(--alert-color, #cc4b37);
            color: var(--dt-multi-text-button-hover-color, #ffffff);
          }
        }
        .input-addon.btn-add {
          color: var(--success-color, #cc4b37);
          &:disabled {
            color: var(--dt-text-placeholder-color, #999);
          }
          &:hover:not([disabled]) {
            background-color: var(--success-color, #cc4b37);
            color: var(--dt-multi-text-button-hover-color, #ffffff);
          }
        }

        .icon-overlay {
          inset-inline-end: 5.5rem;
          height: 2.5rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0}}}updated(e){var t;if(e.has("value")){const o=e.get("value");o&&(o==null?void 0:o.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewItem()}}focusNewItem(){const e=this.shadowRoot.querySelectorAll("input");e&&e.length&&e[e.length-1].focus()}_addItem(){const e={verified:!1,value:"",tempKey:Date.now().toString()};this.value=[...this.value,e]}_removeItem(e){const t=e.currentTarget.dataset.key;if(t){const o=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}}),i=this.value.filter(a=>a.tempKey!==t).map(a=>{const r={...a};return a.key===t&&(r.delete=!0),r});i.filter(a=>!a.delete).length||i.push({value:"",tempKey:Date.now().toString()}),this.value=i,o.detail.newValue=this.value,this.dispatchEvent(o),this._setFormValue(this.value)}}_change(e){var o,i;const t=(i=(o=e==null?void 0:e.currentTarget)==null?void 0:o.dataset)==null?void 0:i.key;if(t){const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=this.value.map(r=>{var n;return{...r,value:r.key===t||r.tempKey===t?(n=e.target)==null?void 0:n.value:r.value}}),a.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(a)}}_inputFieldTemplate(e,t){return d`
      <div class="field-container">
        <input
          data-key="${e.key??e.tempKey}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${F(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
        />

        ${qa(t>1||e.key||e.value,()=>d`
              <button
                  class="input-addon btn-remove"
                  @click=${this._removeItem}
                  data-key="${e.key??e.tempKey}"
                  ?disabled=${this.disabled}
              >
                <dt-icon icon="mdi:close"></dt-icon>
              </button>
            `,()=>d``)}
        <button
          class="input-addon btn-add"
          @click=${this._addItem}
          ?disabled=${this.disabled}
        >
          <dt-icon icon="mdi:plus-thick"></dt-icon>
        </button>
      </div>
    `}_renderInputFields(){return(!this.value||!this.value.length)&&(this.value=[{verified:!1,value:"",tempKey:Date.now().toString()}]),d`
      ${_e((this.value??[]).filter(e=>!e.delete),e=>e.id,e=>this._inputFieldTemplate(e,this.value.length))}
    `}render(){return d`
      ${this.labelTemplate()}
      <div class="input-group">
        ${this._renderInputFields()}

        ${this.touched&&this.invalid?d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>`:null}
        ${this.error?d`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>`:null}
        ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-multi-text",xi);class ki extends j{static get styles(){return[...super.styles,S`
        :host {
          margin-bottom: 5px;
          --dt-button-font-size: 0.75rem;
          --dt-button-font-weight: 0;
          --dt-button-line-height: 1em;
          --dt-button-padding-y: 0.85em;
          --dt-button-padding-x: 1em;
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
          gap: 5px 10px;
        }
        dt-button {
          margin: 0px;
        }

        .icon-overlay {
          align-items: flex-start;
          padding-block: 4px;
        }

        .input-group.disabled {
          background-color: inherit;
        }
      `]}constructor(){super(),this.options=[]}static get properties(){return{value:{type:Array,reflect:!0},context:{type:String},options:{type:Array},outline:{type:Boolean}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length){const o=this.value.includes(e);this.value=[...this.value.filter(i=>i!==e&&i!==`-${e}`),o?`-${e}`:e]}else this.value=[e];t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}_clickOption(e){var t;(t=e==null?void 0:e.currentTarget)!=null&&t.value&&this._select(e.currentTarget.value)}_inputKeyUp(e){switch(e.keyCode||e.which){case 13:this._clickOption(e);break}}_renderButton(e){const o=(this.value??[]).includes(e.id)?"success":"inactive";return d`
    <dt-button
      custom
      type="success"
      context=${o}
      .value=${e.id}
      @click="${this._clickOption}"
      ?disabled="${this.disabled}"
      ?outline="${this.outline}"
      role="button"
      value="${e.id}"
    >
      ${e.icon?d`<span class="icon"><img src="${e.icon}" alt="${this.iconAltText}" /></span>`:null}
      ${e.label}
    </dt-button>
    `}render(){return d`
       ${this.labelTemplate()}
       <div class="input-group ${this.disabled?"disabled":""}">
         <div class="button-group">
           ${_e(this.options??[],e=>e.id,e=>this._renderButton(e))}
         </div>
         ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
         ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}

         ${this.error?d`<dt-icon
                  icon="mdi:alert-circle"
                  class="icon-overlay alert"
                  tooltip="${this.error}"
                  size="2rem"
                  ></dt-icon>`:null}
       </div>
    `}}window.customElements.define("dt-multi-select-button-group",ki);class Si extends z{static get styles(){return S`
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
    `}static get properties(){return{context:{type:String},dismissable:{type:Boolean},timeout:{type:Number},hide:{type:Boolean},outline:{type:Boolean}}}get classes(){const e={"dt-alert":!0,"dt-alert--outline":this.outline},t=`dt-alert--${this.context}`;return e[t]=!0,e}constructor(){super(),this.context="default"}connectedCallback(){super.connectedCallback(),this.timeout&&setTimeout(()=>{this._dismiss()},this.timeout)}_dismiss(){this.hide=!0}render(){if(this.hide)return d``;const e=d`
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
    `;return d`
      <div role="alert" class=${F(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable?d`
              <button @click="${this._dismiss}" class="toggle">${e}</button>
            `:null}
      </div>
    `}}window.customElements.define("dt-alert",Si);class Ei extends z{static get styles(){return S`
      :host {
        --number-of-columns: 7;
        font-family: var(--dt-list-font-family, var(--font-family));
        font-size: var(--dt-list-font-size, 15px);
        font-weight: var(--dt-list-font-weight, 300);
        line-height: var(--dt-list-line-height, 1.5);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .section {
        container-type: inline-size;
        background-color: var(--dt-list-background-color, #fefefe);
        border: 1px solid var(--dt-list-border-color, #f1f1f1);
        border-radius: var(--dt-list-border-radius, 10px);
        box-shadow: var(--dt-list-box-shadow, 0 2px 4px rgb(0 0 0 / 25%));
        padding: var(--dt-list-section-padding, 1rem);
      }

      .header {
        display: flex;
        justify-content: flex-start;
        align-items: baseline;
        gap: var(--dt-list-header-gap, 1.5em);
        flex-wrap: wrap;
      }

      .section-header {
        color: var(--dt-list-header-color, var(--primary-color));
        font-size: 1.5rem;
        display: inline-block;
        text-transform: capitalize;
      }

      .toggleButton {
        color: var(--dt-list-header-color, var(--primary-color));
        font-size: 1rem;
        background: transparent;
        border: var(--dt-list-toggleButton, 0.1em solid rgb(0 0 0 / 0.2));
        border-radius: 0.25em;
        padding: 0.25em 0.5em;
        cursor: pointer;
      }

      .toggleButton svg {
        height: 0.9rem;
        transform: translateY(-2px);
        vertical-align: bottom;
        width: 1rem;
        fill: var(--dt-list-header-color, var(--primary-color));
        stroke: var(--dt-list-header-color, var(--primary-color));
      }

      .list_action_section {
        background-color: var(
          --dt-list-action-section-background-color,
          #ecf5fc
        );
        border-radius: var(--dt-list-border-radius, 10px);
        margin: var(--dt-list-action-section-margin, 30px 0);
        padding: var(--dt-list-action-section-padding, 20px);
      }
      .list_action_section_header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
      }
      .close-button {
        outline: none;
        font-size: 2.5em;
        line-height: 1;
        color: var(--dt-list-action-close-button, var(--inactive-color));
        background: transparent;
        border: none;
        cursor: pointer;
      }
      .fieldsList {
        list-style-type: none;
        column-count: 1;
      }

      .list-field-picker-item {
        list-style-type: none;
      }

      .list-field-picker-item input {
        margin: 1rem;
      }

      .list-field-picker-item .dt-icon {
        height: var(--dt-list-field-picker-icon-size, 1rem);
        width: var(--dt-list-field-picker-icon-size, 1rem);
      }

      table {
        display: grid;
        border: 1px solid var(--dt-list-border-color, #f1f1f1);
        border-top: 0;
        border-collapse: collapse;
        min-width: 100%;
        grid-template-columns: 1fr;
      }

      /* table.table-contacts {
        display: table !important;
        width: 100%;
        border-collapse: collapse;
        border-radius: 0;
        margin-bottom: 1rem;
      } */

      table td:last-child {
        border-bottom: 1px solid var(--dt-list-border-color, #f1f1f1);
        padding-bottom: 2rem;
      }

      tbody,
      tr {
        display: contents;
      }

      thead {
        display: none;
      }
      /* table.table-contacts thead {
        display: table-header-group;
      }
      table.table-contacts tr {
        display: table-row;
      }
      table.table-contacts tbody {
        display: table-row-group;
      } */
      tr {
        cursor: pointer;
      }

      /* table.table-contacts tr:nth-child(2n + 1) {
        background: #fefefe;
      } */

      tr:nth-child(2n + 1) {
        background: #f1f1f1;
      }

      tr:hover {
        background-color: var(--dt-list-hover-background-color, #ecf5fc);
      }

      tr a {
        color: var(--dt-list-link-color, var(--primary-color));
      }

      .column-name {
        pointer-events: none;
        font-size: 15px;
        font-weight: 700;
      }
      #sort-arrows {
        grid-template-columns: 4fr 1fr;
        display: flex;
        flex-direction: column;
        height: 1em;
        justify-content: space-evenly;
      }
      th.all span.sort-arrow-up {
        border-color: transparent transparent
          var(--dt-list-sort-arrow-color, #dcdcdc) transparent;
        border-style: solid;
        border-width: 0 0.3em 0.3em 0.3em;
      }

      th.all span.sort-arrow-down {
        content: '';
        border-color: var(--dt-list-sort-arrow-color, #dcdcdc) transparent
          transparent;
        border-style: solid;
        border-width: 0.3em 0.3em 0;
      }

      th.all span.sort-arrow-up.sortedBy {
        border-color: transparent transparent
          var(--dt-list-sort-arrow-color-highlight, #41739c) transparent;
      }

      th.all span.sort-arrow-down.sortedBy {
        border-color: var(--dt-list-sort-arrow-color-highlight, #41739c)
          transparent transparent;
      }

      td {
        border: 0;
        grid-column: 1 / span 3;
        padding-inline-start: 1em;
      }

      td::before {
        content: attr(title) ': ';
        padding-inline-end: 1em;
      }

      td.no-title {
        grid-column: 1 / span 3;
      }

      td.line-count {
        padding-block-start: 0.8em;
        padding-inline-start: 1em;
      }

      td.no-title::before {
        content: '';
        padding-inline-end: 0.25em;
      }

      th.bulk_edit_checkbox,
      td.bulk_edit_checkbox {
        grid-column: 1 / auto;
        padding: 0;
        width: 0; /* Initially no width */
      }

      .bulk_edit_checkbox input {
        display: none;
        margin: 0;
      }

      .bulk_editing .bulk_edit_checkbox {
        grid-column: 1 / auto;
        padding: 0;
        width: auto; /* Width when parent has .bulk_editing */
      }
      .bulk_editing .bulk_edit_checkbox input {
        display: initial;
      }

      ul {
        margin: 0;
        padding: 0;
      }

      ul li {
        list-style-type: none;
      }

      input[type='checkbox'] {
        margin: 1rem;
      }
      table thead th,
      table tr td {
        padding: 0.5333333333rem 0.6666666667rem 0.6666666667rem;
      }

      ::slotted(svg) {
        fill: var(--fav-star-not-selected-color, #c7c6c1);
      }

      .icon-star {
        fill: var(--fav-star-not-selected-color, #c7c6c1); /* Default to gray (non-favorite) */
        margin: 0;
      }
      .icon-star.selected {
        fill: var(--fav-star-selected-color, #ffc105); /* Favorite state in yellow */
      }

      @media (min-width: 650px) {
        .fieldsList {
          column-count: 2;
        }
        table {
          grid-template-columns:
            minmax(0px, 0fr)
            minmax(32px, 0.25fr)
            minmax(32px, 0.25fr)
            repeat(var(--number-of-columns, 6), minmax(50px, 1fr));
        }

        table.bulk_editing {
          grid-template-columns:
            minmax(32px, 0.25fr)
            minmax(32px, 0.25fr)
            minmax(32px, 0.25fr)
            repeat(var(--number-of-columns, 6), minmax(50px, 1fr));
        }

        thead {
          display: contents;
        }

        th {
          position: sticky;
          top: 0;
          background: var(
            --dt-list-header-background-color,
            var(--dt-tile-background-color, #fefefe)
          );
          text-align: start;
          justify-self: start;
          font-weight: normal;
          font-size: 1.1rem;
          color: var(--dt-list-header-color, #0a0a0a);
          white-space: pre-wrap;
          display: grid;
          place-items: center;
          grid-template-columns: 2fr 1fr;
        }

        th:last-child {
          border: 0;
        }
        td {
          display: flex;
          align-items: center;
          grid-column: auto;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          padding-top: 0.5rem;
          padding-bottom: 0.5rem;
          padding-inline-start: 0;
          color: var(--text-color-mid);
          border-bottom: 1px solid var(--dt-list-border-color, #f1f1f1);
        }
        td::before {
          content: '';
          display: none;
        }

        td.no-title {
          grid-column: auto;
        }
      }

        .btn {
        -webkit-appearance: none;
        border: 1px solid transparent;
        border-radius: 5px;
        cursor: pointer;
        display: inline-block;
        font-family: inherit;
        font-size: .9rem;
        line-height: 1;
        margin: 0 !important;
        text-align: center;
        -webkit-transition: background-color .25s ease-out, color .25s ease-out;
        transition: background-color .25s ease-out, color .25s ease-out;
        vertical-align: middle;
      }

      .btn.btn-primary {
        background-color: #3f729b;
        color: #fefefe;
        border-radius: 5px;
      }

      .btn.btn-primary:hover, .btn.btn-primary:focus {
        background-color: #366184;
        color: #fefefe;
      }

      .text-center {
        text-align: center;
      }

      .btn.btn-primary .dt-button {
        margin: 0;
        border-radius: 5px;
      }


      @media (min-width: 950px) {
        .fieldsList {
          column-count: 3;
        }
      }

      @media (min-width: 1500px) {
        .fieldsList {
          column-count: 4;
        }
      }
    `}static get properties(){return{postType:{type:String},postTypeLabel:{type:String},posttypesettings:{type:Object,attribute:!0},posts:{type:Array},total:{type:Number},columns:{type:Array},sortedBy:{type:String},loading:{type:Boolean,default:!0},offset:{type:Number},showArchived:{type:Boolean,default:!1},showFieldsSelector:{type:Boolean,default:!1},showBulkEditSelector:{type:Boolean,default:!1},nonce:{type:String},payload:{type:Object},favorite:{type:Boolean},initialLoadPost:{type:Boolean,default:!1},loadMore:{type:Boolean,default:!1},headerClick:{type:Boolean,default:!1}}}constructor(){super(),this.sortedBy="name",this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.sortedColumns},this.initalLoadPost=!1,this.initalLoadPost||(this.posts=[],this.limit=100)}firstUpdated(){this.postTypeSettings=window.post_type_fields,this.sortedColumns=this.columns.includes("favorite")?["favorite",...this.columns.filter(e=>e!=="favorite")]:this.columns,this.style.setProperty("--number-of-columns",this.columns.length-1)}async _getPosts(e){const t=await new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:e,onSuccess:o=>{this.initalLoadPost&&this.loadMore&&(this.posts=[...this.posts,...o],this.postsLength=this.posts.length,this.total=o.length,this.loadMore=!1),this.initalLoadPost||(this.posts=[...o],this.offset=this.posts.length,this.initalLoadPost=!0,this.total=o.length),this.headerClick&&(this.posts=o,this.offset=this.posts.length,this.headerClick=!1),this.total=o.length},onError:o=>{console.warn(o)}}});this.dispatchEvent(t)}_headerClick(e){const t=e.target.dataset.id;this.sortedBy===t?t.startsWith("-")?this.sortedBy=t.replace("-",""):this.sortedBy=`-${t}`:this.sortedBy=t,this.payload={sort:this.sortedBy,overall_status:["-closed"],limit:this.limit,fields_to_return:this.columns},this.headerClick=!0,this._getPosts(this.payload)}static _rowClick(e){window.open(e,"_self")}_bulkEdit(){this.showBulkEditSelector=!this.showBulkEditSelector}_fieldsEdit(){this.showFieldsSelector=!this.showFieldsSelector}_toggleShowArchived(){if(this.showArchived=!this.showArchived,this.headerClick=!0,this.showArchived){const{overall_status:e,offset:t,...o}=this.payload;this.payload=o}else this.payload.overall_status=["-closed"];this._getPosts(this.payload)}_sortArrowsClass(e){return this.sortedBy===e?"sortedBy":""}_sortArrowsToggle(e){return this.sortedBy!==`-${e}`?`-${e}`:e}_headerTemplate(){return this.postTypeSettings?d`
        <thead>
          <tr>
            <th id="bulk_edit_master" class="bulk_edit_checkbox">
              <input
                type="checkbox"
                name="bulk_send_app_id"
                value=""
                id="bulk_edit_master_checkbox"
              />
            </th>
            <th class="no-title line-count"></th>
            ${Ne(this.sortedColumns,e=>{const t=e==="favorite";return d`<th
                class="all"
                data-id="${this._sortArrowsToggle(e)}"
                @click=${this._headerClick}
              >
                  <span class="column-name"
                     >${t?null:this.postTypeSettings[e].name}</span
                  >
                  ${t?"":d`<span id="sort-arrows">
                        <span
                          class="sort-arrow-up ${this._sortArrowsClass(e)}"
                          data-id="${e}"
                        ></span>
                        <span
                          class="sort-arrow-down ${this._sortArrowsClass(`-${e}`)}"
                          data-id="-${e}"
                        ></span>
                      </span>`}
              </th>`})}
          </tr>
        </thead>
      `:null}_rowTemplate(){if(this.posts&&Array.isArray(this.posts)){const e=this.posts.map((t,o)=>this.showArchived||!this.showArchived&&t.overall_status!=="closed"?d`
              <tr class="dnd-moved" data-link="${t.permalink}" @click=${()=>this._rowClick(t.permalink)}>
                <td class="bulk_edit_checkbox no-title">
                  <input type="checkbox" name="bulk_edit_id" .value="${t.ID}" />
                </td>
                <td class="no-title line-count">${o+1}.</td>
                ${this._cellTemplate(t)}
              </tr>
            `:null).filter(t=>t!==null);return e.length>0?e:d`<p>No contacts available</p>`}return null}formatDate(e){const t=new Date(e);return new Intl.DateTimeFormat("en-US",{month:"long",day:"numeric",year:"numeric"}).format(t)}_cellTemplate(e){return Ne(this.sortedColumns,t=>{if(["text","textarea","number"].includes(this.postTypeSettings[t].type))return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t]}
        </td>`;if(this.postTypeSettings[t].type==="date")return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${this.formatDate(e[t].formatted)}
        </td>`;if(this.postTypeSettings[t].type==="user_select"&&e[t]&&e[t].display)return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].display)}
        </td>`;if(this.postTypeSettings[t].type==="key_select"&&e[t]&&(e[t].label||e[t].name))return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t].label||e[t].name}
        </td>`;if(this.postTypeSettings[t].type==="multi_select"||this.postTypeSettings[t].type==="tags"&&e[t]&&e[t].length>0)return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <ul>
            ${Ne(e[t],o=>d`<li>
                  ${this.postTypeSettings[t].default[o].label}
                </li>`)}
          </ul>
        </td>`;if(this.postTypeSettings[t].type==="location"||this.postTypeSettings[t].type==="location_meta")return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].label)}
        </td>`;if(this.postTypeSettings[t].type==="communication_channel")return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="connection")return d` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <!-- TODO: look at this, it doesn't match the current theme. -->
          ${ue(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="boolean"){if(t==="favorite")return d`<td
            dir="auto"
            title="${this.postTypeSettings[t].name}"
            class=""
          >
            <dt-button
              id="favorite-button-${e.ID}"
              label="favorite"
              title="favorite"
              type="button"
              posttype="contacts"
              context="star"
              .favorited=${e.favorite?e.favorite:!1}
              .listButton=${!0}
            >
              <svg
                class="${F({"icon-star":!0,selected:e.favorite})}"
                height="15"
                viewBox="0 0 32 32"
              >
                <path
                  d="M 31.916 12.092 C 31.706 11.417 31.131 10.937 30.451 10.873 L 21.215 9.996 L 17.564 1.077 C 17.295 0.423 16.681 0 16 0 C 15.318 0 14.706 0.423 14.435 1.079 L 10.784 9.996 L 1.546 10.873 C 0.868 10.937 0.295 11.417 0.084 12.092 C -0.126 12.769 0.068 13.51 0.581 13.978 L 7.563 20.367 L 5.503 29.83 C 5.354 30.524 5.613 31.245 6.165 31.662 C 6.462 31.886 6.811 32 7.161 32 C 7.463 32 7.764 31.915 8.032 31.747 L 16 26.778 L 23.963 31.747 C 24.546 32.113 25.281 32.08 25.834 31.662 C 26.386 31.243 26.645 30.524 26.494 29.83 L 24.436 20.367 L 31.417 13.978 C 31.931 13.51 32.127 12.769 31.916 12.092 Z M 31.916 12.092"
                />
              </svg>
            </dt-button>
          </td>`;if(this.postTypeSettings[t]===!0)return d`<td
            dir="auto"
            title="${this.postTypeSettings[t].name}"
          >
            ['&check;']
          </td>`}return d`<td
        dir="auto"
        title="${this.postTypeSettings[t].name}"
      ></td>`})}_fieldListIconTemplate(e){return this.postTypeSettings[e].icon?d`<img
        class="dt-icon"
        src="${this.postTypeSettings[e].icon}"
        alt="${this.postTypeSettings[e].name}"
      />`:null}_fieldsListTemplate(){return _e(Object.keys(this.postTypeSettings).sort((e,t)=>{const o=this.postTypeSettings[e].name.toUpperCase(),i=this.postTypeSettings[t].name.toUpperCase();return o<i?-1:o>i?1:0}),e=>e,e=>this.postTypeSettings[e].hidden?null:d`<li class="list-field-picker-item">
            <label>
              <input
                type="checkbox"
                id="${e}"
                name="${e}"
                .value="${e}"
                @change=${this._updateFields}
                ?checked=${this.columns.includes(e)}
              />
              ${this._fieldListIconTemplate(e)}
              ${this.postTypeSettings[e].name}</label
            >
          </li> `)}_fieldsSelectorTemplate(){return this.showFieldsSelector?d`<div
        id="list_column_picker"
        class="list_field_picker list_action_section"
      >
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${A("Choose which fields to display as columns in the list")}
          </p>
          <button
            class="close-button list-action-close-button"
            data-close="list_column_picker"
            aria-label="Close modal"
            type="button"
            @click=${this._fieldsEdit}
          >
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <ul class="fieldsList">
          ${this._fieldsListTemplate()}
        </ul>
      </div>`:null}_updateFields(e){const t=e.target.value,o=this.columns;o.includes(t)?(o.filter(i=>i!==t),o.splice(o.indexOf(t),1)):o.push(t),this.columns=o,this.style.setProperty("--number-of-columns",this.columns.length-1),this.requestUpdate()}_bulkSelectorTemplate(){return this.showBulkEditSelector?d`<div id="bulk_edit_picker" class="list_action_section">
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${A(h`Select all the ${this.postType} you want to update from the list, and update them below`)}
          </p>
          <button
            class="close-button list-action-close-button"
            aria-label="Close modal"
            type="button"
            @click=${this._bulkEdit}
          >
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <ul class="fieldsList">
          This is where the bulk edit form will go.
        </ul>
      </div>`:null}connectedCallback(){super.connectedCallback(),this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns},this.posts.length===0&&this._getPosts(this.payload).then(e=>{this.posts=e})}_handleLoadMore(){this.limit=500,this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns,offset:this.offset,limit:this.limit},this.loadMore=!0,this._getPosts(this.payload).then(e=>{console.log(e)})}render(){const e={bulk_editing:this.showBulkEditSelector,hidden:!1};this.posts&&(this.total=this.posts.length);const t=d`
      <svg viewBox="0 0 100 100" fill="#000000" style="enable-background:new 0 0 100 100;" xmlns="http://www.w3.org/2000/svg">
        <line style="stroke-linecap: round; paint-order: fill; fill: none; stroke-width: 15px;" x1="7.97" y1="50.199" x2="76.069" y2="50.128" transform="matrix(0.999999, 0.001017, -0.001017, 0.999999, 0.051038, -0.042708)"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="17.751" x2="92.058" y2="17.751"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="82.853" x2="42.343" y2="82.853"/>
        <polygon style="stroke-linecap: round; stroke-miterlimit: 1; stroke-linejoin: round; fill: rgb(255, 255, 255); paint-order: stroke; stroke-width: 9px;" points="22.982 64.982 33.592 53.186 50.916 70.608 82.902 21.308 95 30.85 52.256 95"/>
      </svg>
    `,o=d`<svg height='100px' width='100px'  fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M94.4,63c0-5.7-3.6-10.5-8.6-12.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5     s3.6,10.5,8.6,12.5v17.2c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5C90.9,73.6,94.4,68.7,94.4,63z M81,66.7     c-2,0-3.7-1.7-3.7-3.7c0-2,1.7-3.7,3.7-3.7s3.7,1.7,3.7,3.7C84.7,65.1,83.1,66.7,81,66.7z"></path><path d="M54.8,24.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v17.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v43.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V49.5c5-1.9,8.6-6.8,8.6-12.5S59.8,26.5,54.8,24.5z M50,40.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C53.7,39.1,52,40.7,50,40.7z"></path><path d="M23.8,50.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v17.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5c5-1.9,8.6-6.8,8.6-12.5S28.8,52.5,23.8,50.5z M19,66.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C22.7,65.1,21,66.7,19,66.7z"></path></g></g></g></svg>`;return d`
      <div class="section">
        <div class="header">
          <div class="section-header">
            <span
              class="section-header posts-header"
              style="display: inline-block"
              >${A(h`${this.postTypeLabel?this.postTypeLabel:this.postType} List`)}</span
            >
          </div>
          <span class="filter-result-text"
            >${A(h`Showing ${this.total} of ${this.total}`)}</span
          >

          <button
            class="bulkToggle toggleButton"
            id="bulk_edit_button"
            @click=${this._bulkEdit}
          >
            ${t} ${A("Bulk Edit")}
          </button>
          <button
            class="fieldsToggle toggleButton"
            id="fields_edit_button"
            @click=${this._fieldsEdit}
          >
            ${o} ${A("Fields")}
          </button>

          <dt-toggle
            name="showArchived"
            label=${A("Show Archived")}
            ?checked=${this.showArchived}
            hideIcons
            onchange=${this._toggleShowArchived}
            @click=${this._toggleShowArchived}
          ></dt-toggle>
        </div>

        ${this._fieldsSelectorTemplate()} ${this._bulkSelectorTemplate()}
        <table class="table-contacts ${F(e)}">
          ${this._headerTemplate()}
          ${this.posts?this._rowTemplate():A("Loading")}
        </table>
          ${this.total>=100?d`<div class="text-center"><dt-button buttonStyle=${JSON.stringify({margin:"0"})} class="loadMoreButton btn btn-primary" @click=${this._handleLoadMore}>Load More</dt-button></div>`:""}
      </div>
    `}}window.customElements.define("dt-list",Ei);class Ti extends z{static get styles(){return S`
      :host {
        font-family: var(--dt-tile-font-family, var(--font-family));
        font-size: var(--dt-tile-font-size, 14px);
        font-weight: var(--dt-tile-font-weight, 700);
        overflow: hidden;
        text-overflow: ellipsis;
      }

      section {
        background-color: var(--dt-tile-background-color, #fefefe);
        border-top: var(--dt-tile-border-top, 1px solid #cecece);
        border-bottom: var(--dt-tile-border-bottom, 1px solid #cecece);
        border-right: var(--dt-tile-border-right, 1px solid #cecece);
        border-left: var(--dt-tile-border-left, 1px solid #cecece);
        border-radius: var(--dt-tile-border-radius, 10px);
        box-shadow: var(--dt-tile-box-shadow, 0 2px 4px rgb(0 0 0 / 25%));
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
        color: var(--dt-tile-header-color, #3f729b);
        font-size: 1.5rem;
        display: flex;
        text-transform: var(--dt-tile-header-text-transform, capitalize);
        justify-content: var(--dt-tile-header-justify-content);
      }

      .section-body {
        display: grid;
        grid-template-columns: var(--dt-tile-body-grid-template-columns, repeat(auto-fill, minmax(200px, 1fr)));
        column-gap: 1.4rem;
        transition: height 1s ease 0s;
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
    `}static get properties(){return{title:{type:String},expands:{type:Boolean},collapsed:{type:Boolean}}}get hasHeading(){return this.title||this.expands}_toggle(){this.collapsed=!this.collapsed}renderHeading(){return this.hasHeading?d`
        <h3 class="section-header">
          ${this.title}
          ${this.expands?d`
                <button
                  @click="${this._toggle}"
                  class="toggle chevron ${this.collapsed?"down":"up"}"
                >
                  &nbsp;
                </button>
              `:null}
        </h3>
    `:T}render(){return d`
      <section>
        ${this.renderHeading()}
        <div part="body" class="section-body ${this.collapsed?"collapsed":null}">
          <slot></slot>
        </div>
      </section>
    `}}window.customElements.define("dt-tile",Ti);class Ke{get api(){return this._api}constructor(e,t,o,i="wp-json"){this.postType=e,this.postId=t,this.nonce=o,this._api=new Re(this.nonce,i),this.apiRoot=this._api.apiRoot,this.autoSaveComponents=["dt-connection","dt-date","dt-location","dt-multi-select","dt-number","dt-single-select","dt-tags","dt-text","dt-textarea","dt-toggle","dt-multi-text","dt-multi-select-button-group","dt-list","dt-button"],this.dynamicLoadComponents=["dt-connection","dt-tags","dt-modal","dt-list","dt-button"]}initialize(){this.postId&&this.enableAutoSave();const e=document.querySelector("dt-button#create-post-button");e&&e.addEventListener("send-data",this.processFormSubmission.bind(this));const t=document.querySelector("dt-list");t&&t.tagName.toLowerCase()==="dt-list"&&t.addEventListener("customClick",this.handleCustomClickEvent.bind(this)),this.attachLoadEvents()}async attachLoadEvents(e){const t=document.querySelectorAll(e||this.dynamicLoadComponents.join(","));t&&t.forEach(o=>{o.dataset.eventDtGetData||(o.addEventListener("dt:get-data",this.handleGetDataEvent.bind(this)),o.dataset.eventDtGetData=!0)})}async checkDuplicates(e,t){const o=document.querySelector("dt-modal.duplicate-detected");if(o){const i=o.shadowRoot.querySelector(".duplicates-detected-button");i&&(i.style.display="none");const a=await this._api.checkDuplicateUsers(this.postType,this.postId);t&&a.ids.length>0&&i&&(i.style.display="block")}}enableAutoSave(e){const t=document.querySelectorAll(e||this.autoSaveComponents.join(","));t&&t.forEach(o=>{o.tagName.toLowerCase()==="dt-button"&&o.addEventListener("customClick",this.handleCustomClickEvent.bind(this)),o.addEventListener("change",this.handleChangeEvent.bind(this))})}async handleCustomClickEvent(e){const t=e.detail;if(t){const{field:o,toggleState:i}=t;e.target.setAttribute("loading",!0);let a;o.startsWith("favorite-button")?(a={favorite:i},/\d$/.test(o)&&(this.postId=o.split("-").pop())):o.startsWith("following-button")||o.startsWith("follow-button")?a={follow:{values:[{value:"1",delete:i}]},unfollow:{values:[{value:"1",delete:!i}]}}:console.log("No match found for the field");try{const r=await this._api.updatePost(this.postType,this.postId,a)}catch(r){console.error(r),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",r.message||r.toString())}}}async processFormSubmission(e){const t=e.detail,{newValue:o}=t;try{const i=await this._api.createPost(this.postType,o.el);i&&(window.location=i.permalink),e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(i){console.error(i),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",i.message||i.toString())}}async handleGetDataEvent(e){const t=e.detail;if(t){const{field:o,query:i,onSuccess:a,onError:r}=t;try{const n=e.target.tagName.toLowerCase();let l=[];switch(n){case"dt-button":l=await this._api.getContactInfo(this.postType,this.postId);break;case"dt-list":l=(await this._api.fetchPostsList(this.postType,i)).posts;break;case"dt-connection":{const u=t.postType||this.postType,b=await this._api.listPostsCompact(u,i),g={...b,posts:b.posts.filter(v=>v.ID!==parseInt(this.postId,10))};g!=null&&g.posts&&(l=Ke.convertApiValue("dt-connection",g==null?void 0:g.posts));break}case"dt-tags":default:l=await this._api.getMultiSelectValues(this.postType,o,i),l=l.map(u=>({id:u,label:u}));break}a(l)}catch(n){r(n)}}}async handleChangeEvent(e){const t=e.detail;if(t){const{field:o,newValue:i,oldValue:a,remove:r}=t,n=e.target.tagName.toLowerCase(),l=Ke.convertValue(n,i,a);e.target.setAttribute("loading",!0);try{let u;switch(n){case"dt-users-connection":{if(r===!0){u=await this._api.removePostShare(this.postType,this.postId,l);break}u=await this._api.addPostShare(this.postType,this.postId,l);break}default:{u=await this._api.updatePost(this.postType,this.postId,{[o]:l}),document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:u,field:o,value:l,component:n}}));break}}e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(u){console.error(u),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",u.message||u.toString())}}}static convertApiValue(e,t){let o=t;switch(e){case"dt-connection":o=t.map(i=>({id:i.ID,label:i.name??i.post_title,link:i.permalink,status:i.status}));break}return o}static convertValue(e,t,o){let i=t;if(t)switch(e.toLowerCase()){case"dt-toggle":typeof t=="string"&&(i=t.toLowerCase()==="true");break;case"dt-multi-select":case"dt-multi-select-button-group":case"dt-tags":typeof t=="string"&&(i=[t]),i={values:i.map(a=>{if(typeof a=="string"){const n={value:a};return a.startsWith("-")&&(n.delete=!0,n.value=a.substring(1)),n}const r={value:a.id};return a.delete&&(r.delete=a.delete),r}),force_values:!1};break;case"dt-users-connection":{const a=[],r=new Map(o.map(n=>[n.id,n]));for(const n of i){const l=r.get(n.id),u={id:n.id,changes:{}};if(l){let b=!1;const g=new Set([...Object.keys(l),...Object.keys(n)]);for(const v of g)n[v]!==l[v]&&(u.changes[v]=Object.prototype.hasOwnProperty.call(n,v)?n[v]:void 0,b=!0);if(b){a.push(u);break}}else{u.changes={...n},a.push(u);break}}i=a[0].id;break}case"dt-connection":case"dt-location":typeof t=="string"&&(i=[{id:t}]),i={values:i.map(a=>{const r={value:a.id};return a.delete&&(r.delete=a.delete),r}),force_values:!1};break;case"dt-multi-text":Array.isArray(t)?i=t.map(a=>{const r={...a};return delete r.tempKey,r}):typeof t=="string"&&(i=[{value:t}]);break}return i}}const Ba={s226be12a5b1a27e8:"ሰነዶቹን ያንብቡ",s33f85f24c0f5f008:"አስቀምጥ",s36cb242ac90353bc:"መስኮች",s41cb4006238ebd3b:"የጅምላ አርትዕ",s5e8250fb85d64c23:"ገጠመ",s625ad019db843f94:"ተጠቀም",sac83d7f9358b43db:h`${0} ዝርዝር`,sbf1ca928ec1deb62:"ተጨማሪ እገዛ ይፈልጋሉ?",sd1a8dc951b2b6a98:"በዝርዝሩ ውስጥ እንደ ዓምዶች የትኞቹን መስኮች እንደሚያሳዩ ይምረጡ",sf9aee319a006c9b4:"አክል",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Va=Object.freeze(Object.defineProperty({__proto__:null,templates:Ba},Symbol.toStringTag,{value:"Module"})),Ha={s04ceadb276bbe149:"خيارات التحميل...",s226be12a5b1a27e8:"اقرأ الوثائق",s29e25f5e4622f847:"افتح",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"غلق",s625ad019db843f94:"استخدام",s9d51bfd93b5dbeca:"عرض المحفوظات",sac83d7f9358b43db:h`${0}قائمة الأعضاء`,sb1bd536b63e9e995:"المجال الخاص: أنا فقط أستطيع رؤية محتواه",sb59d68ed12d46377:"جار التحميل",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",scb9a1ff437efbd2a:h`حَدِّد جميع ${0} التي تريد تحديثها من القائمة ، وقم بتحديثها أدناه`,sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",seafe6ef133ede7da:h`عرض 1 of ${0}`,sf9aee319a006c9b4:"لأضف",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ga=Object.freeze(Object.defineProperty({__proto__:null,templates:Ha},Symbol.toStringTag,{value:"Module"})),Wa={s226be12a5b1a27e8:"اقرأ الوثائق",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"أغلق",s625ad019db843f94:"استخدام",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",sf9aee319a006c9b4:"إضافة",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ka=Object.freeze(Object.defineProperty({__proto__:null,templates:Wa},Symbol.toStringTag,{value:"Module"})),Za={s226be12a5b1a27e8:"Прочетете документацията",s33f85f24c0f5f008:"Запазете",s36cb242ac90353bc:"Полета",s41cb4006238ebd3b:"Групово редактиране",s5e8250fb85d64c23:"Близо",s625ad019db843f94:"Използвайте",sbf1ca928ec1deb62:"Имате нужда от повече помощ?",sd1a8dc951b2b6a98:"Изберете кои полета да се показват като колони в списъка",sf9aee319a006c9b4:"Добавяне",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ja=Object.freeze(Object.defineProperty({__proto__:null,templates:Za},Symbol.toStringTag,{value:"Module"})),Qa={s226be12a5b1a27e8:"নথিপত্রাদি পাঠ করুন",s33f85f24c0f5f008:"সংরক্ষণ করুন",s36cb242ac90353bc:"ক্ষেত্র",s41cb4006238ebd3b:"বাল্ক এডিট",s5e8250fb85d64c23:"বন্ধ",s625ad019db843f94:"ব্যবহার",sbf1ca928ec1deb62:"আরও সাহায্য প্রয়োজন?",sd1a8dc951b2b6a98:"তালিকার কলাম হিসাবে কোন ক্ষেত্রগুলি প্রদর্শিত হবে তা চয়ন করুন",sf9aee319a006c9b4:"অ্যাড",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xa=Object.freeze(Object.defineProperty({__proto__:null,templates:Qa},Symbol.toStringTag,{value:"Module"})),Ya={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitajte dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate više pomoći?",scb9a1ff437efbd2a:h`Odaberite sve ${0} koje želite ažurirati sa liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Odaberite koja polja će se prikazati kao kolone na listi",seafe6ef133ede7da:h`Prikazuje se 1 od ${0}`,sf9aee319a006c9b4:"Dodati",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},er=Object.freeze(Object.defineProperty({__proto__:null,templates:Ya},Symbol.toStringTag,{value:"Module"})),tr={s226be12a5b1a27e8:"Přečtěte si dokumentaci",s33f85f24c0f5f008:"Uložit",s36cb242ac90353bc:"Pole",s41cb4006238ebd3b:"Hromadná úprava",s5e8250fb85d64c23:"Zavřít",s625ad019db843f94:"Použití",sbf1ca928ec1deb62:"Potřebujete další pomoc?",sd1a8dc951b2b6a98:"Vyberte pole, která chcete v seznamu zobrazit jako sloupce",sf9aee319a006c9b4:"Přidat",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},or=Object.freeze(Object.defineProperty({__proto__:null,templates:tr},Symbol.toStringTag,{value:"Module"})),ir={s226be12a5b1a27e8:"Lesen Sie die Dokumentation",s33f85f24c0f5f008:"Speichern",s36cb242ac90353bc:"Felder",s41cb4006238ebd3b:"Im Stapel bearbeiten",s5e8250fb85d64c23:"Schließen",s625ad019db843f94:"Verwenden",sbf1ca928ec1deb62:"Benötigen Sie weitere Hilfe?",sd1a8dc951b2b6a98:"Wählen Sie aus, welche Felder in der Liste als Spalte angezeigt werden sollen",sf9aee319a006c9b4:"Hinzufügen",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},sr=Object.freeze(Object.defineProperty({__proto__:null,templates:ir},Symbol.toStringTag,{value:"Module"})),ar={s226be12a5b1a27e8:"Διαβάστε την τεκμηρίωση",s33f85f24c0f5f008:"Αποθήκευση",s36cb242ac90353bc:"Πεδία",s41cb4006238ebd3b:"Μαζική Επεξεργασία",s5e8250fb85d64c23:"Κλείσιμο",s625ad019db843f94:"Χρήση",sbf1ca928ec1deb62:"Χρειάζεστε περισσότερη βοήθεια;",sd1a8dc951b2b6a98:"Επιλέξτε ποια πεδία θα εμφανίζονται ως στήλες στη λίστα",sf9aee319a006c9b4:"Προσθήκη",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},rr=Object.freeze(Object.defineProperty({__proto__:null,templates:ar},Symbol.toStringTag,{value:"Module"})),nr={sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",sf9aee319a006c9b4:"Add",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog",s33f85f24c0f5f008:"Save",s49730f3d5751a433:"Loading...",s625ad019db843f94:"Use",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},lr=Object.freeze(Object.defineProperty({__proto__:null,templates:nr},Symbol.toStringTag,{value:"Module"})),cr={s8900c9de2dbae68b:"No hay opciones disponibles",sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sf9aee319a006c9b4:"Añadir",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sb9b8c412407d5691:"This is where the bulk edit form will go.",sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog"},dr=Object.freeze(Object.defineProperty({__proto__:null,templates:cr},Symbol.toStringTag,{value:"Module"})),ur={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Leer la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},hr=Object.freeze(Object.defineProperty({__proto__:null,templates:ur},Symbol.toStringTag,{value:"Module"})),pr={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Lee la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},fr=Object.freeze(Object.defineProperty({__proto__:null,templates:pr},Symbol.toStringTag,{value:"Module"})),br={s04ceadb276bbe149:"در حال بارگیری گزینه‌ها...",s226be12a5b1a27e8:"راهنمای سایت",s29e25f5e4622f847:"جعبه محاوره ای را باز کنید",s33f85f24c0f5f008:"صرفه جویی",s36cb242ac90353bc:"حوزه‌ها",s41cb4006238ebd3b:"ویرایش انبوه",s5e8250fb85d64c23:"بستن",s625ad019db843f94:"استفاده کنید",s9d51bfd93b5dbeca:"نمایش بایگانی شده",sac83d7f9358b43db:h`لیست ${0}`,sb1bd536b63e9e995:"زمینه خصوصی: فقط من می توانم محتوای آن را داشته باشم",sb59d68ed12d46377:"بارگیری",sbf1ca928ec1deb62:"آیا به راهنمایی بیشتری نیاز دارید؟",scb9a1ff437efbd2a:h`همۀ ${0} مورد نظر برای به روزرسانی را از لیست انتخاب کنید و آن‌ها را در زیر به روز کنید`,sd1a8dc951b2b6a98:"انتخاب کنید که کدام یک از حوزه‌ها به‌عنوان ستون در لیست نمایش داده شوند",seafe6ef133ede7da:h`نمایش 1 از ${0}`,sf9aee319a006c9b4:"افزودن",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},gr=Object.freeze(Object.defineProperty({__proto__:null,templates:br},Symbol.toStringTag,{value:"Module"})),mr={s04ceadb276bbe149:"Chargement les options...",s226be12a5b1a27e8:"Lire la documentation",s29e25f5e4622f847:"Ouvrir la boîte de dialogue",s33f85f24c0f5f008:"sauver",s36cb242ac90353bc:"Champs",s41cb4006238ebd3b:"Modification groupée",s5e8250fb85d64c23:"Fermer",s625ad019db843f94:"Utiliser",s9d51bfd93b5dbeca:"Afficher Archivé",sac83d7f9358b43db:h`${0} Liste`,sb1bd536b63e9e995:"Champ privé : je suis le seul à voir son contenu",sb59d68ed12d46377:"Chargement",sbf1ca928ec1deb62:"Besoin d'aide ?",scb9a1ff437efbd2a:h`Sélectionnez tous les ${0} que vous souhaitez mettre à jour dans la liste et mettez-les à jour ci-dessous`,sd1a8dc951b2b6a98:"Choisissez les champs à afficher sous forme de colonnes dans la liste",seafe6ef133ede7da:h`Affichage de 1 sur ${0}`,sf9aee319a006c9b4:"Ajouter",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},vr=Object.freeze(Object.defineProperty({__proto__:null,templates:mr},Symbol.toStringTag,{value:"Module"})),yr={s226be12a5b1a27e8:"डॉक्यूमेंटेशन पढ़ें",s33f85f24c0f5f008:"बचाना",s36cb242ac90353bc:"खेत",s41cb4006238ebd3b:"थोक संपादित",s5e8250fb85d64c23:"बंद",s625ad019db843f94:"उपयोग",sbf1ca928ec1deb62:"क्या और मदद चाहिये?",sd1a8dc951b2b6a98:"सूची में कॉलम के रूप में प्रदर्शित करने के लिए कौन से फ़ील्ड चुनें",sf9aee319a006c9b4:"जोडें",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},wr=Object.freeze(Object.defineProperty({__proto__:null,templates:yr},Symbol.toStringTag,{value:"Module"})),_r={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitaj dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Spremi",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvoriti",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate li pomoć?",scb9a1ff437efbd2a:h`Odaberite sve${0}koje želite ažurirati s liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Izaberite polja koja će se prikazivati kao stupci na popisu",seafe6ef133ede7da:h`Prikazuje se 1 od${0}`,sf9aee319a006c9b4:"Dodaj",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},$r=Object.freeze(Object.defineProperty({__proto__:null,templates:_r},Symbol.toStringTag,{value:"Module"})),xr={s226be12a5b1a27e8:"Olvasd el a dokumentációt",s33f85f24c0f5f008:"Megment",s36cb242ac90353bc:"Mezők",s41cb4006238ebd3b:"Tömeges Szerkesztés",s5e8250fb85d64c23:"Bezár",s625ad019db843f94:"Használ",sbf1ca928ec1deb62:"Több segítség szükséges?",sd1a8dc951b2b6a98:"Válassza ki, melyik mezők jelenjenek meg oszlopként a listában",sf9aee319a006c9b4:"Hozzáadás",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},kr=Object.freeze(Object.defineProperty({__proto__:null,templates:xr},Symbol.toStringTag,{value:"Module"})),Sr={s226be12a5b1a27e8:"Bacalah dokumentasi",s33f85f24c0f5f008:"Simpan",s36cb242ac90353bc:"Larik",s41cb4006238ebd3b:"Edit Massal",s5e8250fb85d64c23:"Menutup",s625ad019db843f94:"Gunakan",sbf1ca928ec1deb62:"Perlukan bantuan lagi?",sd1a8dc951b2b6a98:"Pilih larik mana yang akan ditampilkan sebagai kolom dalam daftar",sf9aee319a006c9b4:"Tambah",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Er=Object.freeze(Object.defineProperty({__proto__:null,templates:Sr},Symbol.toStringTag,{value:"Module"})),Tr={s04ceadb276bbe149:"Caricando opzioni...",s226be12a5b1a27e8:"Leggi la documentazione",s29e25f5e4622f847:"Apri Dialogo",s33f85f24c0f5f008:"Salvare",s36cb242ac90353bc:"Campi",s41cb4006238ebd3b:"Modifica in blocco",s5e8250fb85d64c23:"Chiudi",s625ad019db843f94:"Uso",s9d51bfd93b5dbeca:"Visualizza Archiviati",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privato: Solo io posso vedere i suoi contenuti",sb59d68ed12d46377:"Caricando",sbf1ca928ec1deb62:"Hai bisogno di ulteriore assistenza?",scb9a1ff437efbd2a:h`Seleziona tutti i ${0}vuoi aggiornare dalla lista e aggiornali sotto`,sd1a8dc951b2b6a98:"Scegli quali campi visualizzare come colonne nell'elenco",seafe6ef133ede7da:h`Visualizzando 1 di ${0}`,sf9aee319a006c9b4:"Inserisci",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ar=Object.freeze(Object.defineProperty({__proto__:null,templates:Tr},Symbol.toStringTag,{value:"Module"})),Or={s226be12a5b1a27e8:"ドキュメントを読む",s33f85f24c0f5f008:"セーブ",s36cb242ac90353bc:"田畑",s41cb4006238ebd3b:"一括編集",s5e8250fb85d64c23:"閉じる",s625ad019db843f94:"使用する",sbf1ca928ec1deb62:"もっと助けが必要ですか？",sd1a8dc951b2b6a98:"リストの列として表示するフィールドを選択します",sf9aee319a006c9b4:"追加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Cr=Object.freeze(Object.defineProperty({__proto__:null,templates:Or},Symbol.toStringTag,{value:"Module"})),Lr={s226be12a5b1a27e8:"문서 읽기",s33f85f24c0f5f008:"구하다",s36cb242ac90353bc:"필드",s41cb4006238ebd3b:"대량 수정",s5e8250fb85d64c23:"닫기",s625ad019db843f94:"사용",sbf1ca928ec1deb62:"더 많은 도움이 필요하신가요?",sd1a8dc951b2b6a98:"목록에서 어떤 필드를 표시할지 고르세요",sf9aee319a006c9b4:"추가",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Pr=Object.freeze(Object.defineProperty({__proto__:null,templates:Lr},Symbol.toStringTag,{value:"Module"})),Ir={s226be12a5b1a27e8:"Прочитај ја документацијата",s33f85f24c0f5f008:"Зачувај",s36cb242ac90353bc:"Полиња",s41cb4006238ebd3b:"Уреди повеќе",s5e8250fb85d64c23:"Затвори",s625ad019db843f94:"Користи",sbf1ca928ec1deb62:"Дали ти треба повеќе помош?",sd1a8dc951b2b6a98:"Избери кои полиња да се прикажат како колони во листата",sf9aee319a006c9b4:"Додади",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Mr=Object.freeze(Object.defineProperty({__proto__:null,templates:Ir},Symbol.toStringTag,{value:"Module"})),jr={s226be12a5b1a27e8:"कागदपत्रे वाचा.",s33f85f24c0f5f008:"जतन करा",s36cb242ac90353bc:"क्षेत्रे",s41cb4006238ebd3b:"बल्क एडिट करा",s5e8250fb85d64c23:"बंद करा",s625ad019db843f94:"वापर",sbf1ca928ec1deb62:"अधिक मदत आवश्यक आहे का?",sd1a8dc951b2b6a98:"यादीत कोणती क्षेत्रे स्तंभ म्हणून दर्शवली जावीत हे निवडा",sf9aee319a006c9b4:"समाविष्ट करा",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Dr=Object.freeze(Object.defineProperty({__proto__:null,templates:jr},Symbol.toStringTag,{value:"Module"})),zr={s226be12a5b1a27e8:"စာရွက်စာတမ်းများကိုဖတ်ပါ",s33f85f24c0f5f008:"သိမ်းဆည်းပါ",s36cb242ac90353bc:"နယ်ပယ်ဒေသများ",s5e8250fb85d64c23:"ပိတ်သည်",s625ad019db843f94:"အသုံးပြုပါ",sbf1ca928ec1deb62:"နောက်ထပ်အကူအညီလိုပါသလား။",sd1a8dc951b2b6a98:"စာရင်းရှိကော်လံများအနေဖြင့်ဖော်ပြမည့်မည်သည့်နယ်ပယ်ဒေသများကိုရွေးချယ်ပါ",sf9aee319a006c9b4:"ထည့်ပါ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Rr=Object.freeze(Object.defineProperty({__proto__:null,templates:zr},Symbol.toStringTag,{value:"Module"})),Nr={s226be12a5b1a27e8:"कागजात पढ्नुहोस्",s33f85f24c0f5f008:"सुरक्षित गर्नुहोस",s36cb242ac90353bc:"क्षेत्रहरू",s41cb4006238ebd3b:"थोक सम्पादन",s5e8250fb85d64c23:"बन्द गर्नुहोस",s625ad019db843f94:"प्रयोग गर्नुहोस्",sbf1ca928ec1deb62:"थप मद्दत चाहिन्छ?",sd1a8dc951b2b6a98:"सूचीमा स्तम्भहरूको रूपमा कुन क्षेत्रहरू प्रदर्शन गर्ने छनौट गर्नुहोस्",sf9aee319a006c9b4:"थप",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Fr=Object.freeze(Object.defineProperty({__proto__:null,templates:Nr},Symbol.toStringTag,{value:"Module"})),Ur={s04ceadb276bbe149:"aan het laden.....",s226be12a5b1a27e8:"Lees de documentatie",s29e25f5e4622f847:"Dialoogvenster openen",s33f85f24c0f5f008:"Opslaan",s36cb242ac90353bc:"Velden",s41cb4006238ebd3b:"Bulkbewerking",s5e8250fb85d64c23:"sluit",s625ad019db843f94:"Gebruiken",sac83d7f9358b43db:h`${0} Lijst`,sb1bd536b63e9e995:"Privéveld: alleen ik kan de inhoud zien",sb59d68ed12d46377:"aan het laden",sbf1ca928ec1deb62:"Meer hulp nodig?",sd1a8dc951b2b6a98:"Kies welke velden u als kolommen in de lijst wilt weergeven",seafe6ef133ede7da:h`1 van ${0} laten zien`,sf9aee319a006c9b4:"Toevoegen",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,s9d51bfd93b5dbeca:"Show Archived"},qr=Object.freeze(Object.defineProperty({__proto__:null,templates:Ur},Symbol.toStringTag,{value:"Module"})),Br={s226be12a5b1a27e8:"ਦਸਤਾਵੇਜ਼ ਪੜ੍ਹੋ",s33f85f24c0f5f008:"ਸੇਵ",s36cb242ac90353bc:"ਖੇਤਰ",s41cb4006238ebd3b:"ਥੋਕ ਸੰਪਾਦਨ",s5e8250fb85d64c23:"ਬੰਦ ਕਰੋ",s625ad019db843f94:"ਵਰਤੋਂ",sbf1ca928ec1deb62:"ਹੋਰ ਮਦਦ ਦੀ ਲੋੜ ਹੈ?",sd1a8dc951b2b6a98:"ਸੂਚੀ ਵਿੱਚ ਕਾਲਮ ਦੇ ਰੂਪ ਵਿੱਚ ਪ੍ਰਦਰਸ਼ਿਤ ਕਰਨ ਲਈ ਕਿਹੜੇ ਖੇਤਰ ਚੁਣੋ",sf9aee319a006c9b4:"ਸ਼ਾਮਲ ਕਰੋ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Vr=Object.freeze(Object.defineProperty({__proto__:null,templates:Br},Symbol.toStringTag,{value:"Module"})),Hr={s226be12a5b1a27e8:"Przeczytaj dokumentację",s33f85f24c0f5f008:"Zapisać",s36cb242ac90353bc:"Pola",s41cb4006238ebd3b:"Edycja zbiorcza",s5e8250fb85d64c23:"Zamknij",s625ad019db843f94:"Posługiwać się",sbf1ca928ec1deb62:"Potrzebujesz pomocy?",sd1a8dc951b2b6a98:"Wybierz, które pola mają być wyświetlane jako kolumny na liście",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Gr=Object.freeze(Object.defineProperty({__proto__:null,templates:Hr},Symbol.toStringTag,{value:"Module"})),Wr={s226be12a5b1a27e8:"Leia a documentação",s33f85f24c0f5f008:"Salvar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edição em massa",s5e8250fb85d64c23:"Fechar",s625ad019db843f94:"Usar",sbf1ca928ec1deb62:"Precisa de mais ajuda?",sd1a8dc951b2b6a98:"Escolha quais campos exibir como colunas na lista",sf9aee319a006c9b4:"Adicionar",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Kr=Object.freeze(Object.defineProperty({__proto__:null,templates:Wr},Symbol.toStringTag,{value:"Module"})),Zr={s226be12a5b1a27e8:"Citiți documentația",s33f85f24c0f5f008:"Salvați",s36cb242ac90353bc:"Câmpuri",s41cb4006238ebd3b:"Editare masivă",s5e8250fb85d64c23:"Închide",s625ad019db843f94:"Utilizare",sbf1ca928ec1deb62:"Ai nevoie de mai mult ajutor?",sd1a8dc951b2b6a98:"Alegeți câmpurile care să fie afișate în coloane în listă",sf9aee319a006c9b4:"Adăuga",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Jr=Object.freeze(Object.defineProperty({__proto__:null,templates:Zr},Symbol.toStringTag,{value:"Module"})),Qr={s226be12a5b1a27e8:"Читать документацию",s33f85f24c0f5f008:"Сохранить",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Массовое редактирование",s5e8250fb85d64c23:"Закрыть",s625ad019db843f94:"Использовать",sbf1ca928ec1deb62:"Нужна дополнительная помощь?",sd1a8dc951b2b6a98:"Выберите, какие поля отображать как столбцы в списке",sf9aee319a006c9b4:"Добавить",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xr=Object.freeze(Object.defineProperty({__proto__:null,templates:Qr},Symbol.toStringTag,{value:"Module"})),Yr={s226be12a5b1a27e8:"Preberite dokumentacijo",s33f85f24c0f5f008:"Shrani",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Urejanje v velikem obsegu",s5e8250fb85d64c23:"Zapri",s625ad019db843f94:"Uporaba",sbf1ca928ec1deb62:"Potrebujete več pomoči?",sd1a8dc951b2b6a98:"Izberite, katera polja naj bodo prikazana kot stolpci na seznamu",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},en=Object.freeze(Object.defineProperty({__proto__:null,templates:Yr},Symbol.toStringTag,{value:"Module"})),tn={s226be12a5b1a27e8:"Pročitajte dokumentaciju",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"masovno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristiti",sbf1ca928ec1deb62:"Treba vam više pomoći?",sd1a8dc951b2b6a98:"Izaberite koja polja da se prikazuju kao kolone na listi",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},on=Object.freeze(Object.defineProperty({__proto__:null,templates:tn},Symbol.toStringTag,{value:"Module"})),sn={s04ceadb276bbe149:"Inapakia chaguo...",s226be12a5b1a27e8:"Soma nyaraka",s29e25f5e4622f847:"Fungua Kidirisha",s33f85f24c0f5f008:"Hifadhi",s36cb242ac90353bc:"Mashamba",s41cb4006238ebd3b:"Hariri kwa Wingi",s5e8250fb85d64c23:"Funga",s625ad019db843f94:"Tumia",s9d51bfd93b5dbeca:"Onyesha Kumbukumbu",sac83d7f9358b43db:h`Orodha ya${0}`,sb1bd536b63e9e995:"Sehemu ya Faragha: Ni mimi pekee ninayeweza kuona maudhui yake",sb59d68ed12d46377:"Inapakia",sbf1ca928ec1deb62:"Unahitaji msaada zaidi?",scb9a1ff437efbd2a:h`Chagua ${0} zote ungependa kusasisha kutoka kwenye orodha, na uzisasishe hapa chini.`,sd1a8dc951b2b6a98:"Chagua ni sehemu zipi zitaonyeshwa kama safu wima kwenye orodha",seafe6ef133ede7da:h`Inaonyesha 1 kati ya ${0}`,sf9aee319a006c9b4:"Ongeza",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},an=Object.freeze(Object.defineProperty({__proto__:null,templates:sn},Symbol.toStringTag,{value:"Module"})),rn={s226be12a5b1a27e8:"อ่านเอกสาร",s33f85f24c0f5f008:"บันทึก",s36cb242ac90353bc:"ฟิลด์",s41cb4006238ebd3b:"แก้ไขเป็นกลุ่ม",s5e8250fb85d64c23:"ปิด",s625ad019db843f94:"ใช้",sbf1ca928ec1deb62:"ต้องการความช่วยเหลือเพิ่มเติมหรือไม่?",sd1a8dc951b2b6a98:"เลือกฟิลด์ที่จะแสดงเป็นคอลัมน์ในรายการ",sf9aee319a006c9b4:"เพิ่ม",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nn=Object.freeze(Object.defineProperty({__proto__:null,templates:rn},Symbol.toStringTag,{value:"Module"})),ln={s226be12a5b1a27e8:"Basahin ang dokumentasyon",s33f85f24c0f5f008:"I-save",s36cb242ac90353bc:"Mga Field",s41cb4006238ebd3b:"Maramihang Pag-edit",s5e8250fb85d64c23:"Isara",s625ad019db843f94:"Gamitin",sbf1ca928ec1deb62:"Kailangan mo pa ba ng tulong?",sd1a8dc951b2b6a98:"Piliin kung aling mga field ang ipapakita bilang mga column sa listahan",sf9aee319a006c9b4:"Idagdag",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},cn=Object.freeze(Object.defineProperty({__proto__:null,templates:ln},Symbol.toStringTag,{value:"Module"})),dn={s04ceadb276bbe149:"Seçenekler Yükleniyor...",s226be12a5b1a27e8:"Belgeleri oku",s29e25f5e4622f847:"İletişim Kutusunu Aç",s33f85f24c0f5f008:"Kaydet",s36cb242ac90353bc:"Alanlar",s41cb4006238ebd3b:"Toplu Düzenleme",s5e8250fb85d64c23:"Kapat",s625ad019db843f94:"Kullan",s9d51bfd93b5dbeca:"Arşivlenmiş Göster",sac83d7f9358b43db:h`${0} Listesi`,sb1bd536b63e9e995:"Özel Alan: İçeriğini sadece ben görebilirim",sb59d68ed12d46377:"Yükleniyor",sbf1ca928ec1deb62:"Daha fazla yardıma ihtiyacınız var mı?",scb9a1ff437efbd2a:h`Listeden güncellemek istediğiniz tüm ${0} 'i seçin ve aşağıda güncelleyin`,sd1a8dc951b2b6a98:"Listede Hangi Alanların Sütun Olarak Görüntüleneceğini Seçin",seafe6ef133ede7da:h`Gösteriliyor 1 of ${0}`,sf9aee319a006c9b4:"Ekle",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},un=Object.freeze(Object.defineProperty({__proto__:null,templates:dn},Symbol.toStringTag,{value:"Module"})),hn={s226be12a5b1a27e8:"Прочитайте документацію",s33f85f24c0f5f008:"Зберегти",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Масове редагування",s5e8250fb85d64c23:"Закрити",s625ad019db843f94:"Використати",sbf1ca928ec1deb62:"Потрібна додаткова допомога?",sd1a8dc951b2b6a98:"Виберіть, яке поле відображати у вигляді стовпців у списку",sf9aee319a006c9b4:"Додати",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},pn=Object.freeze(Object.defineProperty({__proto__:null,templates:hn},Symbol.toStringTag,{value:"Module"})),fn={s226be12a5b1a27e8:"Đọc tài liệu",s33f85f24c0f5f008:"Lưu",s36cb242ac90353bc:"Trường",s41cb4006238ebd3b:"Chỉnh sửa Hàng loạt",s5e8250fb85d64c23:"Đóng",s625ad019db843f94:"Sử dụng",sbf1ca928ec1deb62:"Bạn cần trợ giúp thêm?",sd1a8dc951b2b6a98:"Chọn các trường để hiển thị dưới dạng cột trong danh sách",sf9aee319a006c9b4:"Bổ sung",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},bn=Object.freeze(Object.defineProperty({__proto__:null,templates:fn},Symbol.toStringTag,{value:"Module"})),gn={s226be12a5b1a27e8:"阅读文档",s33f85f24c0f5f008:"保存",s36cb242ac90353bc:"字段",s41cb4006238ebd3b:"批量编辑",s5e8250fb85d64c23:"关",s625ad019db843f94:"使用",sbf1ca928ec1deb62:"需要更多帮助吗？",sd1a8dc951b2b6a98:"选择哪些字段要在列表中显示为列",sf9aee319a006c9b4:"添加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},mn=Object.freeze(Object.defineProperty({__proto__:null,templates:gn},Symbol.toStringTag,{value:"Module"})),vn={s04ceadb276bbe149:"正在載入選項...",s226be12a5b1a27e8:"閱讀文檔",s29e25f5e4622f847:"開啟對話視窗",s33f85f24c0f5f008:"儲存",s36cb242ac90353bc:"欄位",s41cb4006238ebd3b:"大量編輯",s5e8250fb85d64c23:"關",s625ad019db843f94:"使用",s9d51bfd93b5dbeca:"顯示已儲存",sac83d7f9358b43db:h`${0} 清單`,sb1bd536b63e9e995:"私人欄位：只有我可以看見內容",sb59d68ed12d46377:"載入中",sbf1ca928ec1deb62:"需要更多幫助嗎？",scb9a1ff437efbd2a:h`從清單中選取要更新的項目${0}，並在下面進行更新`,sd1a8dc951b2b6a98:"選擇哪些欄位要顯示為列表中的直行",seafe6ef133ede7da:h`第1頁 （共${0}頁）`,sf9aee319a006c9b4:"新增",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},yn=Object.freeze(Object.defineProperty({__proto__:null,templates:vn},Symbol.toStringTag,{value:"Module"}));return $.ApiService=Re,$.ComponentService=Ke,$.DtAlert=Si,$.DtBase=z,$.DtButton=yo,$.DtChurchHealthCircle=xo,$.DtConnection=To,$.DtCopyText=Oo,$.DtDate=Co,$.DtDropdown=$o,$.DtFormBase=j,$.DtIcon=gi,$.DtLabel=ko,$.DtList=Ei,$.DtLocation=Lo,$.DtLocationMap=vi,$.DtMapModal=mi,$.DtModal=_o,$.DtMultiSelect=vt,$.DtMultiSelectButtonGroup=ki,$.DtMultiText=xi,$.DtNumberField=yi,$.DtSingleSelect=wi,$.DtTags=$e,$.DtText=Dt,$.DtTextArea=_i,$.DtTile=Ti,$.DtToggle=$i,$.DtUsersConnection=Ao,Object.defineProperty($,Symbol.toStringTag,{value:"Module"}),$}({});
