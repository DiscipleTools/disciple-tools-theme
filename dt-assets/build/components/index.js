var DtWebComponents=function($){"use strict";var In=Object.defineProperty;var Mn=($,q,K)=>q in $?In($,q,{enumerable:!0,configurable:!0,writable:!0,value:K}):$[q]=K;var Ye=($,q,K)=>Mn($,typeof q!="symbol"?q+"":q,K);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var Es;const q=globalThis,K=q.ShadowRoot&&(q.ShadyCSS===void 0||q.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,Ut=Symbol(),Vt=new WeakMap;let qs=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==Ut)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(K&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=Vt.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&Vt.set(t,e))}return e}toString(){return this.cssText}};const Fs=o=>new qs(typeof o=="string"?o:o+"",void 0,Ut),Us=(o,e)=>{if(K)o.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),s=q.litNonce;s!==void 0&&i.setAttribute("nonce",s),i.textContent=t.cssText,o.appendChild(i)}},Bt=K?o=>o:o=>o instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return Fs(t)})(o):o;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:Vs,defineProperty:Bs,getOwnPropertyDescriptor:Hs,getOwnPropertyNames:Gs,getOwnPropertySymbols:Ws,getPrototypeOf:Ks}=Object,Z=globalThis,Ht=Z.trustedTypes,Zs=Ht?Ht.emptyScript:"",et=Z.reactiveElementPolyfillSupport,he=(o,e)=>o,tt={toAttribute(o,e){switch(e){case Boolean:o=o?Zs:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,e){let t=o;switch(e){case Boolean:t=o!==null;break;case Number:t=o===null?null:Number(o);break;case Object:case Array:try{t=JSON.parse(o)}catch{t=null}}return t}},Gt=(o,e)=>!Vs(o,e),Wt={attribute:!0,type:String,converter:tt,reflect:!1,hasChanged:Gt};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),Z.litPropertyMetadata??(Z.litPropertyMetadata=new WeakMap);let pe=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Wt){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),s=this.getPropertyDescriptor(e,i,t);s!==void 0&&Bs(this.prototype,e,s)}}static getPropertyDescriptor(e,t,i){const{get:s,set:a}=Hs(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return s==null?void 0:s.call(this)},set(r){const n=s==null?void 0:s.call(this);a.call(this,r),this.requestUpdate(e,n,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Wt}static _$Ei(){if(this.hasOwnProperty(he("elementProperties")))return;const e=Ks(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(he("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(he("properties"))){const t=this.properties,i=[...Gs(t),...Ws(t)];for(const s of i)this.createProperty(s,t[s])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,s]of t)this.elementProperties.set(i,s)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const s=this._$Eu(t,i);s!==void 0&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const s of i)t.unshift(Bt(s))}else e!==void 0&&t.push(Bt(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return Us(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),s=this.constructor._$Eu(e,i);if(s!==void 0&&i.reflect===!0){const r=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:tt).toAttribute(t,i.type);this._$Em=e,r==null?this.removeAttribute(s):this.setAttribute(s,r),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,s=i._$Eh.get(e);if(s!==void 0&&this._$Em!==s){const r=i.getPropertyOptions(s),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:tt;this._$Em=s,this[s]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??Gt)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[a,r]of s)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(s=>{var a;return(a=s.hostUpdate)==null?void 0:a.call(s)}),this.update(t)):this._$EU()}catch(s){throw e=!1,this._$EU(),s}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var s;return(s=i.hostUpdated)==null?void 0:s.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}};pe.elementStyles=[],pe.shadowRootOptions={mode:"open"},pe[he("elementProperties")]=new Map,pe[he("finalized")]=new Map,et==null||et({ReactiveElement:pe}),(Z.reactiveElementVersions??(Z.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const fe=globalThis,Pe=fe.trustedTypes,Kt=Pe?Pe.createPolicy("lit-html",{createHTML:o=>o}):void 0,Zt="$lit$",J=`lit$${Math.random().toFixed(9).slice(2)}$`,Jt="?"+J,Js=`<${Jt}>`,te=document,be=()=>te.createComment(""),ge=o=>o===null||typeof o!="object"&&typeof o!="function",it=Array.isArray,Qs=o=>it(o)||typeof(o==null?void 0:o[Symbol.iterator])=="function",st=`[ 	
\f\r]`,me=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Qt=/-->/g,Xt=/>/g,ie=RegExp(`>|${st}(?:([^\\s"'>=/]+)(${st}*=${st}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),Yt=/'/g,ei=/"/g,ti=/^(?:script|style|textarea|title)$/i,Xs=o=>(e,...t)=>({_$litType$:o,strings:e,values:t}),c=Xs(1),V=Symbol.for("lit-noChange"),E=Symbol.for("lit-nothing"),ii=new WeakMap,se=te.createTreeWalker(te,129);function si(o,e){if(!it(o)||!o.hasOwnProperty("raw"))throw Error("invalid template strings array");return Kt!==void 0?Kt.createHTML(e):e}const Ys=(o,e)=>{const t=o.length-1,i=[];let s,a=e===2?"<svg>":e===3?"<math>":"",r=me;for(let n=0;n<t;n++){const l=o[n];let u,b,g=-1,v=0;for(;v<l.length&&(r.lastIndex=v,b=r.exec(l),b!==null);)v=r.lastIndex,r===me?b[1]==="!--"?r=Qt:b[1]!==void 0?r=Xt:b[2]!==void 0?(ti.test(b[2])&&(s=RegExp("</"+b[2],"g")),r=ie):b[3]!==void 0&&(r=ie):r===ie?b[0]===">"?(r=s??me,g=-1):b[1]===void 0?g=-2:(g=r.lastIndex-b[2].length,u=b[1],r=b[3]===void 0?ie:b[3]==='"'?ei:Yt):r===ei||r===Yt?r=ie:r===Qt||r===Xt?r=me:(r=ie,s=void 0);const y=r===ie&&o[n+1].startsWith("/>")?" ":"";a+=r===me?l+Js:g>=0?(i.push(u),l.slice(0,g)+Zt+l.slice(g)+J+y):l+J+(g===-2?n:y)}return[si(o,a+(o[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),i]};class ve{constructor({strings:e,_$litType$:t},i){let s;this.parts=[];let a=0,r=0;const n=e.length-1,l=this.parts,[u,b]=Ys(e,t);if(this.el=ve.createElement(u,i),se.currentNode=this.el.content,t===2||t===3){const g=this.el.content.firstChild;g.replaceWith(...g.childNodes)}for(;(s=se.nextNode())!==null&&l.length<n;){if(s.nodeType===1){if(s.hasAttributes())for(const g of s.getAttributeNames())if(g.endsWith(Zt)){const v=b[r++],y=s.getAttribute(g).split(J),_=/([.?@])?(.*)/.exec(v);l.push({type:1,index:a,name:_[2],strings:y,ctor:_[1]==="."?to:_[1]==="?"?io:_[1]==="@"?so:Ie}),s.removeAttribute(g)}else g.startsWith(J)&&(l.push({type:6,index:a}),s.removeAttribute(g));if(ti.test(s.tagName)){const g=s.textContent.split(J),v=g.length-1;if(v>0){s.textContent=Pe?Pe.emptyScript:"";for(let y=0;y<v;y++)s.append(g[y],be()),se.nextNode(),l.push({type:2,index:++a});s.append(g[v],be())}}}else if(s.nodeType===8)if(s.data===Jt)l.push({type:2,index:a});else{let g=-1;for(;(g=s.data.indexOf(J,g+1))!==-1;)l.push({type:7,index:a}),g+=J.length-1}a++}}static createElement(e,t){const i=te.createElement("template");return i.innerHTML=e,i}}function le(o,e,t=o,i){var r,n;if(e===V)return e;let s=i!==void 0?(r=t._$Co)==null?void 0:r[i]:t._$Cl;const a=ge(e)?void 0:e._$litDirective$;return(s==null?void 0:s.constructor)!==a&&((n=s==null?void 0:s._$AO)==null||n.call(s,!1),a===void 0?s=void 0:(s=new a(o),s._$AT(o,t,i)),i!==void 0?(t._$Co??(t._$Co=[]))[i]=s:t._$Cl=s),s!==void 0&&(e=le(o,s._$AS(o,e.values),s,i)),e}let eo=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:i}=this._$AD,s=((e==null?void 0:e.creationScope)??te).importNode(t,!0);se.currentNode=s;let a=se.nextNode(),r=0,n=0,l=i[0];for(;l!==void 0;){if(r===l.index){let u;l.type===2?u=new de(a,a.nextSibling,this,e):l.type===1?u=new l.ctor(a,l.name,l.strings,this,e):l.type===6&&(u=new oo(a,this,e)),this._$AV.push(u),l=i[++n]}r!==(l==null?void 0:l.index)&&(a=se.nextNode(),r++)}return se.currentNode=te,s}p(e){let t=0;for(const i of this._$AV)i!==void 0&&(i.strings!==void 0?(i._$AI(e,i,t),t+=i.strings.length-2):i._$AI(e[t])),t++}};class de{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,i,s){this.type=2,this._$AH=E,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=i,this.options=s,this._$Cv=(s==null?void 0:s.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=le(this,e,t),ge(e)?e===E||e==null||e===""?(this._$AH!==E&&this._$AR(),this._$AH=E):e!==this._$AH&&e!==V&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Qs(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==E&&ge(this._$AH)?this._$AA.nextSibling.data=e:this.T(te.createTextNode(e)),this._$AH=e}$(e){var a;const{values:t,_$litType$:i}=e,s=typeof i=="number"?this._$AC(e):(i.el===void 0&&(i.el=ve.createElement(si(i.h,i.h[0]),this.options)),i);if(((a=this._$AH)==null?void 0:a._$AD)===s)this._$AH.p(t);else{const r=new eo(s,this),n=r.u(this.options);r.p(t),this.T(n),this._$AH=r}}_$AC(e){let t=ii.get(e.strings);return t===void 0&&ii.set(e.strings,t=new ve(e)),t}k(e){it(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let i,s=0;for(const a of e)s===t.length?t.push(i=new de(this.O(be()),this.O(be()),this,this.options)):i=t[s],i._$AI(a),s++;s<t.length&&(this._$AR(i&&i._$AB.nextSibling,s),t.length=s)}_$AR(e=this._$AA.nextSibling,t){var i;for((i=this._$AP)==null?void 0:i.call(this,!1,!0,t);e&&e!==this._$AB;){const s=e.nextSibling;e.remove(),e=s}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class Ie{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,i,s,a){this.type=1,this._$AH=E,this._$AN=void 0,this.element=e,this.name=t,this._$AM=s,this.options=a,i.length>2||i[0]!==""||i[1]!==""?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=E}_$AI(e,t=this,i,s){const a=this.strings;let r=!1;if(a===void 0)e=le(this,e,t,0),r=!ge(e)||e!==this._$AH&&e!==V,r&&(this._$AH=e);else{const n=e;let l,u;for(e=a[0],l=0;l<a.length-1;l++)u=le(this,n[i+l],t,l),u===V&&(u=this._$AH[l]),r||(r=!ge(u)||u!==this._$AH[l]),u===E?e=E:e!==E&&(e+=(u??"")+a[l+1]),this._$AH[l]=u}r&&!s&&this.j(e)}j(e){e===E?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class to extends Ie{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===E?void 0:e}}class io extends Ie{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==E)}}class so extends Ie{constructor(e,t,i,s,a){super(e,t,i,s,a),this.type=5}_$AI(e,t=this){if((e=le(this,e,t,0)??E)===V)return;const i=this._$AH,s=e===E&&i!==E||e.capture!==i.capture||e.once!==i.once||e.passive!==i.passive,a=e!==E&&(i===E||s);s&&this.element.removeEventListener(this.name,this,i),a&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class oo{constructor(e,t,i){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(e){le(this,e)}}const ao={I:de},ot=fe.litHtmlPolyfillSupport;ot==null||ot(ve,de),(fe.litHtmlVersions??(fe.litHtmlVersions=[])).push("3.2.1");const ro=(o,e,t)=>{const i=(t==null?void 0:t.renderBefore)??e;let s=i._$litPart$;if(s===void 0){const a=(t==null?void 0:t.renderBefore)??null;i._$litPart$=s=new de(e.insertBefore(be(),a),a,void 0,t??{})}return s._$AI(o),s};/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const Me=globalThis,at=Me.ShadowRoot&&(Me.ShadyCSS===void 0||Me.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,rt=Symbol(),oi=new WeakMap;let ai=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==rt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(at&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=oi.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&oi.set(t,e))}return e}toString(){return this.cssText}};const no=o=>new ai(typeof o=="string"?o:o+"",void 0,rt),S=(o,...e)=>{const t=o.length===1?o[0]:e.reduce((i,s,a)=>i+(r=>{if(r._$cssResult$===!0)return r.cssText;if(typeof r=="number")return r;throw Error("Value passed to 'css' function must be a 'css' function result: "+r+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(s)+o[a+1],o[0]);return new ai(t,o,rt)},lo=(o,e)=>{if(at)o.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),s=Me.litNonce;s!==void 0&&i.setAttribute("nonce",s),i.textContent=t.cssText,o.appendChild(i)}},ri=at?o=>o:o=>o instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return no(t)})(o):o;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:co,defineProperty:uo,getOwnPropertyDescriptor:ho,getOwnPropertyNames:po,getOwnPropertySymbols:fo,getPrototypeOf:bo}=Object,Q=globalThis,ni=Q.trustedTypes,go=ni?ni.emptyScript:"",nt=Q.reactiveElementPolyfillSupport,ye=(o,e)=>o,lt={toAttribute(o,e){switch(e){case Boolean:o=o?go:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,e){let t=o;switch(e){case Boolean:t=o!==null;break;case Number:t=o===null?null:Number(o);break;case Object:case Array:try{t=JSON.parse(o)}catch{t=null}}return t}},li=(o,e)=>!co(o,e),di={attribute:!0,type:String,converter:lt,reflect:!1,hasChanged:li};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),Q.litPropertyMetadata??(Q.litPropertyMetadata=new WeakMap);class ce extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=di){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),s=this.getPropertyDescriptor(e,i,t);s!==void 0&&uo(this.prototype,e,s)}}static getPropertyDescriptor(e,t,i){const{get:s,set:a}=ho(this.prototype,e)??{get(){return this[t]},set(r){this[t]=r}};return{get(){return s==null?void 0:s.call(this)},set(r){const n=s==null?void 0:s.call(this);a.call(this,r),this.requestUpdate(e,n,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??di}static _$Ei(){if(this.hasOwnProperty(ye("elementProperties")))return;const e=bo(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(ye("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(ye("properties"))){const t=this.properties,i=[...po(t),...fo(t)];for(const s of i)this.createProperty(s,t[s])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,s]of t)this.elementProperties.set(i,s)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const s=this._$Eu(t,i);s!==void 0&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const s of i)t.unshift(ri(s))}else e!==void 0&&t.push(ri(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return lo(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),s=this.constructor._$Eu(e,i);if(s!==void 0&&i.reflect===!0){const r=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:lt).toAttribute(t,i.type);this._$Em=e,r==null?this.removeAttribute(s):this.setAttribute(s,r),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,s=i._$Eh.get(e);if(s!==void 0&&this._$Em!==s){const r=i.getPropertyOptions(s),n=typeof r.converter=="function"?{fromAttribute:r.converter}:((a=r.converter)==null?void 0:a.fromAttribute)!==void 0?r.converter:lt;this._$Em=s,this[s]=n.fromAttribute(t,r.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??li)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,r]of this._$Ep)this[a]=r;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[a,r]of s)r.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],r)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(s=>{var a;return(a=s.hostUpdate)==null?void 0:a.call(s)}),this.update(t)):this._$EU()}catch(s){throw e=!1,this._$EU(),s}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var s;return(s=i.hostUpdated)==null?void 0:s.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}}ce.elementStyles=[],ce.shadowRootOptions={mode:"open"},ce[ye("elementProperties")]=new Map,ce[ye("finalized")]=new Map,nt==null||nt({ReactiveElement:ce}),(Q.reactiveElementVersions??(Q.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */let F=class extends ce{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=ro(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return V}};F._$litElement$=!0,F.finalized=!0,(Es=globalThis.litElementHydrateSupport)==null||Es.call(globalThis,{LitElement:F});const dt=globalThis.litElementPolyfillSupport;dt==null||dt({LitElement:F}),(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.1.1");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ct={ATTRIBUTE:1,CHILD:2},ut=o=>(...e)=>({_$litDirective$:o,values:e});let ht=class{constructor(e){}get _$AU(){return this._$AM._$AU}_$AT(e,t,i){this._$Ct=e,this._$AM=t,this._$Ci=i}_$AS(e,t){return this.update(e,t)}update(e,t){return this.render(...t)}};/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const C=ut(class extends ht{constructor(o){var e;if(super(o),o.type!==ct.ATTRIBUTE||o.name!=="class"||((e=o.strings)==null?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(o){return" "+Object.keys(o).filter(e=>o[e]).join(" ")+" "}update(o,[e]){var i,s;if(this.st===void 0){this.st=new Set,o.strings!==void 0&&(this.nt=new Set(o.strings.join(" ").split(/\s/).filter(a=>a!=="")));for(const a in e)e[a]&&!((i=this.nt)!=null&&i.has(a))&&this.st.add(a);return this.render(e)}const t=o.element.classList;for(const a of this.st)a in e||(t.remove(a),this.st.delete(a));for(const a in e){const r=!!e[a];r===this.st.has(a)||(s=this.nt)!=null&&s.has(a)||(r?(t.add(a),this.st.add(a)):(t.remove(a),this.st.delete(a)))}return V}});/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const pt="lit-localize-status";/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const h=(o,...e)=>({strTag:!0,strings:o,values:e}),mo=o=>typeof o!="string"&&"strTag"in o,ci=(o,e,t)=>{let i=o[0];for(let s=1;s<o.length;s++)i+=e[t?t[s-1]:s-1],i+=o[s];return i};/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ui=o=>mo(o)?ci(o.strings,o.values):o;let A=ui,hi=!1;function vo(o){if(hi)throw new Error("lit-localize can only be configured once");A=o,hi=!0}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class yo{constructor(e){this.__litLocalizeEventHandler=t=>{t.detail.status==="ready"&&this.host.requestUpdate()},this.host=e}hostConnected(){window.addEventListener(pt,this.__litLocalizeEventHandler)}hostDisconnected(){window.removeEventListener(pt,this.__litLocalizeEventHandler)}}const wo=o=>o.addController(new yo(o));/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class pi{constructor(){this.settled=!1,this.promise=new Promise((e,t)=>{this._resolve=e,this._reject=t})}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const B=[];for(let o=0;o<256;o++)B[o]=(o>>4&15).toString(16)+(o&15).toString(16);function _o(o){let e=0,t=8997,i=0,s=33826,a=0,r=40164,n=0,l=52210;for(let u=0;u<o.length;u++)t^=o.charCodeAt(u),e=t*435,i=s*435,a=r*435,n=l*435,a+=t<<8,n+=s<<8,i+=e>>>16,t=e&65535,a+=i>>>16,s=i&65535,l=n+(a>>>16)&65535,r=a&65535;return B[l>>8]+B[l&255]+B[r>>8]+B[r&255]+B[s>>8]+B[s&255]+B[t>>8]+B[t&255]}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const $o="",xo="h",ko="s";function So(o,e){return(e?xo:ko)+_o(typeof o=="string"?o:o.join($o))}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const fi=new WeakMap,bi=new Map;function To(o,e,t){if(o){const i=(t==null?void 0:t.id)??Eo(e),s=o[i];if(s){if(typeof s=="string")return s;if("strTag"in s)return ci(s.strings,e.values,s.values);{let a=fi.get(s);return a===void 0&&(a=s.values,fi.set(s,a)),{...s,values:a.map(r=>e.values[r])}}}}return ui(e)}function Eo(o){const e=typeof o=="string"?o:o.strings;let t=bi.get(e);return t===void 0&&(t=So(e,typeof o!="string"&&!("strTag"in o)),bi.set(e,t)),t}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function ft(o){window.dispatchEvent(new CustomEvent(pt,{detail:o}))}let je="",bt,gi,ze,gt,mi,oe=new pi;oe.resolve();let De=0;const Ao=o=>(vo((e,t)=>To(mi,e,t)),je=gi=o.sourceLocale,ze=new Set(o.targetLocales),ze.add(o.sourceLocale),gt=o.loadLocale,{getLocale:Oo,setLocale:Co}),Oo=()=>je,Co=o=>{if(o===(bt??je))return oe.promise;if(!ze||!gt)throw new Error("Internal error");if(!ze.has(o))throw new Error("Invalid locale code");De++;const e=De;return bt=o,oe.settled&&(oe=new pi),ft({status:"loading",loadingLocale:o}),(o===gi?Promise.resolve({templates:void 0}):gt(o)).then(i=>{De===e&&(je=o,bt=void 0,mi=i.templates,ft({status:"ready",readyLocale:o}),oe.resolve())},i=>{De===e&&(ft({status:"error",errorLocale:o,errorMessage:i.toString()}),oe.reject(i))}),oe.promise},Lo=(o,e,t)=>{const i=o[e];return i?typeof i=="function"?i():Promise.resolve(i):new Promise((s,a)=>{(typeof queueMicrotask=="function"?queueMicrotask:setTimeout)(a.bind(null,new Error("Unknown variable dynamic import: "+e+(e.split("/").length!==t?". Note that variables only represent file names one level deep.":""))))})},Po="en",Io=["am_ET","ar","ar_MA","bg_BG","bn_BD","bs_BA","cs","de_DE","el","en_US","es_419","es_ES","fa_IR","fr_FR","hi_IN","hr","hu_HU","id_ID","it_IT","ja","ko_KR","mk_MK","mr","my_MM","ne_NP","nl_NL","pa_IN","pl","pt_BR","ro_RO","ru_RU","sl_SI","sr_BA","sw","th","tl","tr_TR","uk","vi","zh_CN","zh_TW"],{setLocale:Mo}=Ao({sourceLocale:Po,targetLocales:Io,loadLocale:o=>Lo(Object.assign({"./generated/am_ET.js":()=>Promise.resolve().then(()=>Ba),"./generated/ar.js":()=>Promise.resolve().then(()=>Ga),"./generated/ar_MA.js":()=>Promise.resolve().then(()=>Ka),"./generated/bg_BG.js":()=>Promise.resolve().then(()=>Ja),"./generated/bn_BD.js":()=>Promise.resolve().then(()=>Xa),"./generated/bs_BA.js":()=>Promise.resolve().then(()=>er),"./generated/cs.js":()=>Promise.resolve().then(()=>ir),"./generated/de_DE.js":()=>Promise.resolve().then(()=>or),"./generated/el.js":()=>Promise.resolve().then(()=>rr),"./generated/en_US.js":()=>Promise.resolve().then(()=>lr),"./generated/es-419.js":()=>Promise.resolve().then(()=>cr),"./generated/es_419.js":()=>Promise.resolve().then(()=>hr),"./generated/es_ES.js":()=>Promise.resolve().then(()=>fr),"./generated/fa_IR.js":()=>Promise.resolve().then(()=>gr),"./generated/fr_FR.js":()=>Promise.resolve().then(()=>vr),"./generated/hi_IN.js":()=>Promise.resolve().then(()=>wr),"./generated/hr.js":()=>Promise.resolve().then(()=>$r),"./generated/hu_HU.js":()=>Promise.resolve().then(()=>kr),"./generated/id_ID.js":()=>Promise.resolve().then(()=>Tr),"./generated/it_IT.js":()=>Promise.resolve().then(()=>Ar),"./generated/ja.js":()=>Promise.resolve().then(()=>Cr),"./generated/ko_KR.js":()=>Promise.resolve().then(()=>Pr),"./generated/mk_MK.js":()=>Promise.resolve().then(()=>Mr),"./generated/mr.js":()=>Promise.resolve().then(()=>zr),"./generated/my_MM.js":()=>Promise.resolve().then(()=>Rr),"./generated/ne_NP.js":()=>Promise.resolve().then(()=>qr),"./generated/nl_NL.js":()=>Promise.resolve().then(()=>Ur),"./generated/pa_IN.js":()=>Promise.resolve().then(()=>Br),"./generated/pl.js":()=>Promise.resolve().then(()=>Gr),"./generated/pt_BR.js":()=>Promise.resolve().then(()=>Kr),"./generated/ro_RO.js":()=>Promise.resolve().then(()=>Jr),"./generated/ru_RU.js":()=>Promise.resolve().then(()=>Xr),"./generated/sl_SI.js":()=>Promise.resolve().then(()=>en),"./generated/sr_BA.js":()=>Promise.resolve().then(()=>sn),"./generated/sw.js":()=>Promise.resolve().then(()=>an),"./generated/th.js":()=>Promise.resolve().then(()=>nn),"./generated/tl.js":()=>Promise.resolve().then(()=>dn),"./generated/tr_TR.js":()=>Promise.resolve().then(()=>un),"./generated/uk.js":()=>Promise.resolve().then(()=>pn),"./generated/vi.js":()=>Promise.resolve().then(()=>bn),"./generated/zh_CN.js":()=>Promise.resolve().then(()=>mn),"./generated/zh_TW.js":()=>Promise.resolve().then(()=>yn)}),`./generated/${o}.js`,3)});class Re{constructor(e,t="/wp-json"){this.nonce=e;let i=t;i.match("^http")&&(i=i.replace(/^http[s]?:\/\/.*?\//,"")),i=`/${i}/`.replace(/\/\//g,"/"),this.apiRoot=i}async makeRequest(e,t,i,s="dt/v1/"){let a=s;!a.endsWith("/")&&!t.startsWith("/")&&(a+="/");const r=t.startsWith("http")?t:`${this.apiRoot}${a}${t}`,n={method:e,credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce}};e!=="GET"&&(n.body=JSON.stringify(i));const l=await fetch(r,n),u=await l.json();if(!l.ok){const b=new Error((u==null?void 0:u.message)||u.toString());throw b.args={status:l.status,statusText:l.statusText,body:u},b}return u}async makeRequestOnPosts(e,t,i={}){return this.makeRequest(e,t,i,"dt-posts/v2/")}async getPost(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}`)}async createPost(e,t){return this.makeRequestOnPosts("POST",e,t)}async fetchPostsList(e,t){return this.makeRequestOnPosts("POST",`${e}/list`,t)}async updatePost(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}`,i)}async deletePost(e,t){return this.makeRequestOnPosts("DELETE",`${e}/${t}`)}async listPostsCompact(e,t=""){const i=new URLSearchParams({s:t});return this.makeRequestOnPosts("GET",`${e}/compact?${i}`)}async getPostDuplicates(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/all_duplicates`,i)}async checkFieldValueExists(e,t){return this.makeRequestOnPosts("POST",`${e}/check_field_value_exists`,t)}async getMultiSelectValues(e,t,i=""){const s=new URLSearchParams({s:i,field:t});return this.makeRequestOnPosts("GET",`${e}/multi-select-values?${s}`)}async transferContact(e,t){return this.makeRequestOnPosts("POST","contacts/transfer",{contact_id:e,site_post_id:t})}async transferContactSummaryUpdate(e,t){return this.makeRequestOnPosts("POST","contacts/transfer/summary/send-update",{contact_id:e,update:t})}async requestRecordAccess(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/request_record_access`,{user_id:i})}async createComment(e,t,i,s="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments`,{comment:i,comment_type:s})}async updateComment(e,t,i,s,a="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}`,{comment:s,comment_type:a})}async deleteComment(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/comments/${i}`)}async getComments(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/comments`)}async toggle_comment_reaction(e,t,i,s,a){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}/react`,{user_id:s,reaction:a})}async getPostActivity(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/activity`)}async getSingleActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/activity/${i}`)}async revertActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/revert/${i}`)}async getPostShares(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/shares`)}async addPostShare(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/shares`,{user_id:i})}async removePostShare(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/shares`,{user_id:i})}async getFilters(){return this.makeRequest("GET","users/get_filters")}async saveFilters(e,t){return this.makeRequest("POST","users/save_filters",{filter:t,postType:e})}async deleteFilter(e,t){return this.makeRequest("DELETE","users/save_filters",{id:t,postType:e})}async searchUsers(e="",t){const i=new URLSearchParams({s:e});return this.makeRequest("GET",`users/get_users?${i}&post_type=${t}`)}async checkDuplicateUsers(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/duplicates`)}async getContactInfo(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/`)}async createUser(e){return this.makeRequest("POST","users/create",e)}async advanced_search(e,t,i,s){return this.makeRequest("GET","advanced_search",{query:e,postType:t,offset:i,post:s.post,comment:s.comment,meta:s.meta,status:s.status},"dt-posts/v2/posts/search/")}}(function(){(function(o){const e=new WeakMap,t=new WeakMap,i=new WeakMap,s=new WeakMap,a=new WeakMap,r=new WeakMap,n=new WeakMap,l=new WeakMap,u=new WeakMap,b=new WeakMap,g=new WeakMap,v=new WeakMap,y=new WeakMap,_=new WeakMap,L=new WeakMap,N={ariaAtomic:"aria-atomic",ariaAutoComplete:"aria-autocomplete",ariaBusy:"aria-busy",ariaChecked:"aria-checked",ariaColCount:"aria-colcount",ariaColIndex:"aria-colindex",ariaColIndexText:"aria-colindextext",ariaColSpan:"aria-colspan",ariaCurrent:"aria-current",ariaDescription:"aria-description",ariaDisabled:"aria-disabled",ariaExpanded:"aria-expanded",ariaHasPopup:"aria-haspopup",ariaHidden:"aria-hidden",ariaInvalid:"aria-invalid",ariaKeyShortcuts:"aria-keyshortcuts",ariaLabel:"aria-label",ariaLevel:"aria-level",ariaLive:"aria-live",ariaModal:"aria-modal",ariaMultiLine:"aria-multiline",ariaMultiSelectable:"aria-multiselectable",ariaOrientation:"aria-orientation",ariaPlaceholder:"aria-placeholder",ariaPosInSet:"aria-posinset",ariaPressed:"aria-pressed",ariaReadOnly:"aria-readonly",ariaRelevant:"aria-relevant",ariaRequired:"aria-required",ariaRoleDescription:"aria-roledescription",ariaRowCount:"aria-rowcount",ariaRowIndex:"aria-rowindex",ariaRowIndexText:"aria-rowindextext",ariaRowSpan:"aria-rowspan",ariaSelected:"aria-selected",ariaSetSize:"aria-setsize",ariaSort:"aria-sort",ariaValueMax:"aria-valuemax",ariaValueMin:"aria-valuemin",ariaValueNow:"aria-valuenow",ariaValueText:"aria-valuetext",role:"role"},M=(p,d)=>{for(let f in N){d[f]=null;let m=null;const w=N[f];Object.defineProperty(d,f,{get(){return m},set(x){m=x,p.isConnected?I(p,w,x):b.set(p,d)}})}};function P(p){const d=s.get(p),{form:f}=d;Ls(p,f,d),Cs(p,d.labels)}const Ce=(p,d=!1)=>{const f=document.createTreeWalker(p,NodeFilter.SHOW_ELEMENT,{acceptNode(x){return s.has(x)?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let m=f.nextNode();const w=!d||p.disabled;for(;m;)m.formDisabledCallback&&w&&Dt(m,p.disabled),m=f.nextNode()},Ze={attributes:!0,attributeFilter:["disabled","name"]},Y=Xe()?new MutationObserver(p=>{for(const d of p){const f=d.target;if(d.attributeName==="disabled"&&(f.constructor.formAssociated?Dt(f,f.hasAttribute("disabled")):f.localName==="fieldset"&&Ce(f)),d.attributeName==="name"&&f.constructor.formAssociated){const m=s.get(f),w=u.get(f);m.setFormValue(w)}}}):{};function T(p){p.forEach(d=>{const{addedNodes:f,removedNodes:m}=d,w=Array.from(f),x=Array.from(m);w.forEach(k=>{var j;if(s.has(k)&&k.constructor.formAssociated&&P(k),b.has(k)){const O=b.get(k);Object.keys(N).filter(U=>O[U]!==null).forEach(U=>{I(k,N[U],O[U])}),b.delete(k)}if(L.has(k)){const O=L.get(k);I(k,"internals-valid",O.validity.valid.toString()),I(k,"internals-invalid",(!O.validity.valid).toString()),I(k,"aria-invalid",(!O.validity.valid).toString()),L.delete(k)}if(k.localName==="form"){const O=l.get(k),W=document.createTreeWalker(k,NodeFilter.SHOW_ELEMENT,{acceptNode(Ft){return s.has(Ft)&&Ft.constructor.formAssociated&&!(O&&O.has(Ft))?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let U=W.nextNode();for(;U;)P(U),U=W.nextNode()}k.localName==="fieldset"&&((j=Y.observe)===null||j===void 0||j.call(Y,k,Ze),Ce(k,!0))}),x.forEach(k=>{const j=s.get(k);j&&i.get(j)&&As(j),n.has(k)&&n.get(k).disconnect()})})}function D(p){p.forEach(d=>{const{removedNodes:f}=d;f.forEach(m=>{const w=y.get(d.target);s.has(m)&&Is(m),w.disconnect()})})}const ne=p=>{var d,f;const m=new MutationObserver(D);!((d=window==null?void 0:window.ShadyDOM)===null||d===void 0)&&d.inUse&&p.mode&&p.host&&(p=p.host),(f=m.observe)===null||f===void 0||f.call(m,p,{childList:!0}),y.set(p,m)};Xe()&&new MutationObserver(T);const ee={childList:!0,subtree:!0},I=(p,d,f)=>{p.getAttribute(d)!==f&&p.setAttribute(d,f)},Dt=(p,d)=>{p.toggleAttribute("internals-disabled",d),d?I(p,"aria-disabled","true"):p.removeAttribute("aria-disabled"),p.formDisabledCallback&&p.formDisabledCallback.apply(p,[d])},As=p=>{i.get(p).forEach(f=>{f.remove()}),i.set(p,[])},Os=(p,d)=>{const f=document.createElement("input");return f.type="hidden",f.name=p.getAttribute("name"),p.after(f),i.get(d).push(f),f},wn=(p,d)=>{var f;i.set(d,[]),(f=Y.observe)===null||f===void 0||f.call(Y,p,Ze)},Cs=(p,d)=>{if(d.length){Array.from(d).forEach(m=>m.addEventListener("click",p.click.bind(p)));let f=d[0].id;d[0].id||(f=`${d[0].htmlFor}_Label`,d[0].id=f),I(p,"aria-labelledby",f)}},Je=p=>{const d=Array.from(p.elements).filter(x=>!x.tagName.includes("-")&&x.validity).map(x=>x.validity.valid),f=l.get(p)||[],m=Array.from(f).filter(x=>x.isConnected).map(x=>s.get(x).validity.valid),w=[...d,...m].includes(!1);p.toggleAttribute("internals-invalid",w),p.toggleAttribute("internals-valid",!w)},_n=p=>{Je(Qe(p.target))},$n=p=>{Je(Qe(p.target))},xn=p=>{const d=["button[type=submit]","input[type=submit]","button:not([type])"].map(f=>`${f}:not([disabled])`).map(f=>`${f}:not([form])${p.id?`,${f}[form='${p.id}']`:""}`).join(",");p.addEventListener("click",f=>{if(f.target.closest(d)){const w=l.get(p);if(p.noValidate)return;w.size&&Array.from(w).reverse().map(j=>s.get(j).reportValidity()).includes(!1)&&f.preventDefault()}})},kn=p=>{const d=l.get(p.target);d&&d.size&&d.forEach(f=>{f.constructor.formAssociated&&f.formResetCallback&&f.formResetCallback.apply(f)})},Ls=(p,d,f)=>{if(d){const m=l.get(d);if(m)m.add(p);else{const w=new Set;w.add(p),l.set(d,w),xn(d),d.addEventListener("reset",kn),d.addEventListener("input",_n),d.addEventListener("change",$n)}r.set(d,{ref:p,internals:f}),p.constructor.formAssociated&&p.formAssociatedCallback&&setTimeout(()=>{p.formAssociatedCallback.apply(p,[d])},0),Je(d)}},Qe=p=>{let d=p.parentNode;return d&&d.tagName!=="FORM"&&(d=Qe(d)),d},G=(p,d,f=DOMException)=>{if(!p.constructor.formAssociated)throw new f(d)},Ps=(p,d,f)=>{const m=l.get(p);return m&&m.size&&m.forEach(w=>{s.get(w)[f]()||(d=!1)}),d},Is=p=>{if(p.constructor.formAssociated){const d=s.get(p),{labels:f,form:m}=d;Cs(p,f),Ls(p,m,d)}};function Xe(){return typeof MutationObserver<"u"}class Sn{constructor(){this.badInput=!1,this.customError=!1,this.patternMismatch=!1,this.rangeOverflow=!1,this.rangeUnderflow=!1,this.stepMismatch=!1,this.tooLong=!1,this.tooShort=!1,this.typeMismatch=!1,this.valid=!0,this.valueMissing=!1,Object.seal(this)}}const Tn=p=>(p.badInput=!1,p.customError=!1,p.patternMismatch=!1,p.rangeOverflow=!1,p.rangeUnderflow=!1,p.stepMismatch=!1,p.tooLong=!1,p.tooShort=!1,p.typeMismatch=!1,p.valid=!0,p.valueMissing=!1,p),En=(p,d,f)=>(p.valid=An(d),Object.keys(d).forEach(m=>p[m]=d[m]),f&&Je(f),p),An=p=>{let d=!0;for(let f in p)f!=="valid"&&p[f]!==!1&&(d=!1);return d},Rt=new WeakMap;function Ms(p,d){p.toggleAttribute(d,!0),p.part&&p.part.add(d)}class Nt extends Set{static get isPolyfilled(){return!0}constructor(d){if(super(),!d||!d.tagName||d.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");Rt.set(this,d)}add(d){if(!/^--/.test(d)||typeof d!="string")throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${d} must start with '--'.`);const f=super.add(d),m=Rt.get(this),w=`state${d}`;return m.isConnected?Ms(m,w):setTimeout(()=>{Ms(m,w)}),f}clear(){for(let[d]of this.entries())this.delete(d);super.clear()}delete(d){const f=super.delete(d),m=Rt.get(this);return m.isConnected?(m.toggleAttribute(`state${d}`,!1),m.part&&m.part.remove(`state${d}`)):setTimeout(()=>{m.toggleAttribute(`state${d}`,!1),m.part&&m.part.remove(`state${d}`)}),f}}function js(p,d,f,m){if(typeof d=="function"?p!==d||!0:!d.has(p))throw new TypeError("Cannot read private member from an object whose class did not declare it");return f==="m"?m:f==="a"?m.call(p):m?m.value:d.get(p)}function On(p,d,f,m,w){if(typeof d=="function"?p!==d||!0:!d.has(p))throw new TypeError("Cannot write private member to an object whose class did not declare it");return d.set(p,f),f}var Le;class Cn{constructor(d){Le.set(this,void 0),On(this,Le,d);for(let f=0;f<d.length;f++){let m=d[f];this[f]=m,m.hasAttribute("name")&&(this[m.getAttribute("name")]=m)}Object.freeze(this)}get length(){return js(this,Le,"f").length}[(Le=new WeakMap,Symbol.iterator)](){return js(this,Le,"f")[Symbol.iterator]()}item(d){return this[d]==null?null:this[d]}namedItem(d){return this[d]==null?null:this[d]}}function Ln(){const p=HTMLFormElement.prototype.checkValidity;HTMLFormElement.prototype.checkValidity=f;const d=HTMLFormElement.prototype.reportValidity;HTMLFormElement.prototype.reportValidity=m;function f(...x){let k=p.apply(this,x);return Ps(this,k,"checkValidity")}function m(...x){let k=d.apply(this,x);return Ps(this,k,"reportValidity")}const{get:w}=Object.getOwnPropertyDescriptor(HTMLFormElement.prototype,"elements");Object.defineProperty(HTMLFormElement.prototype,"elements",{get(...x){const k=w.call(this,...x),j=Array.from(l.get(this)||[]);if(j.length===0)return k;const O=Array.from(k).concat(j).sort((W,U)=>W.compareDocumentPosition?W.compareDocumentPosition(U)&2?1:-1:0);return new Cn(O)}})}class zs{static get isPolyfilled(){return!0}constructor(d){if(!d||!d.tagName||d.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");const f=d.getRootNode(),m=new Sn;this.states=new Nt(d),e.set(this,d),t.set(this,m),s.set(d,this),M(d,this),wn(d,this),Object.seal(this),f instanceof DocumentFragment&&ne(f)}checkValidity(){const d=e.get(this);if(G(d,"Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=t.get(this);if(!f.valid){const m=new Event("invalid",{bubbles:!1,cancelable:!0,composed:!1});d.dispatchEvent(m)}return f.valid}get form(){const d=e.get(this);G(d,"Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");let f;return d.constructor.formAssociated===!0&&(f=Qe(d)),f}get labels(){const d=e.get(this);G(d,"Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");const f=d.getAttribute("id"),m=d.getRootNode();return m&&f?m.querySelectorAll(`[for="${f}"]`):[]}reportValidity(){const d=e.get(this);if(G(d,"Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=this.checkValidity(),m=v.get(this);if(m&&!d.constructor.formAssociated)throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");return!f&&m&&(d.focus(),m.focus()),f}setFormValue(d){const f=e.get(this);if(G(f,"Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."),As(this),d!=null&&!(d instanceof FormData)){if(f.getAttribute("name")){const m=Os(f,this);m.value=d}}else d!=null&&d instanceof FormData&&Array.from(d).reverse().forEach(([m,w])=>{if(typeof w=="string"){const x=Os(f,this);x.name=m,x.value=w}});u.set(f,d)}setValidity(d,f,m){const w=e.get(this);if(G(w,"Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!d)throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");v.set(this,m);const x=t.get(this),k={};for(const W in d)k[W]=d[W];Object.keys(k).length===0&&Tn(x);const j=Object.assign(Object.assign({},x),k);delete j.valid;const{valid:O}=En(x,j,this.form);if(!O&&!f)throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");a.set(this,O?"":f),w.isConnected?(w.toggleAttribute("internals-invalid",!O),w.toggleAttribute("internals-valid",O),I(w,"aria-invalid",`${!O}`)):L.set(w,this)}get shadowRoot(){const d=e.get(this),f=g.get(d);return f||null}get validationMessage(){const d=e.get(this);return G(d,"Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."),a.get(this)}get validity(){const d=e.get(this);return G(d,"Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."),t.get(this)}get willValidate(){const d=e.get(this);return G(d,"Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."),!(d.disabled||d.hasAttribute("disabled")||d.hasAttribute("readonly"))}}function Pn(){if(typeof window>"u"||!window.ElementInternals||!HTMLElement.prototype.attachInternals)return!1;class p extends HTMLElement{constructor(){super(),this.internals=this.attachInternals()}}const d=`element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g,"")}`;customElements.define(d,p);const f=new p;return["shadowRoot","form","willValidate","validity","validationMessage","labels","setFormValue","setValidity","checkValidity","reportValidity"].every(m=>m in f.internals)}let Ds=!1,Rs=!1;function qt(p){Rs||(Rs=!0,window.CustomStateSet=Nt,p&&(HTMLElement.prototype.attachInternals=function(...d){const f=p.call(this,d);return f.states=new Nt(this),f}))}function Ns(p=!0){if(!Ds){if(Ds=!0,typeof window<"u"&&(window.ElementInternals=zs),typeof CustomElementRegistry<"u"){const d=CustomElementRegistry.prototype.define;CustomElementRegistry.prototype.define=function(f,m,w){if(m.formAssociated){const x=m.prototype.connectedCallback;m.prototype.connectedCallback=function(){_.has(this)||(_.set(this,!0),this.hasAttribute("disabled")&&Dt(this,!0)),x!=null&&x.apply(this),Is(this)}}d.call(this,f,m,w)}}if(typeof HTMLElement<"u"&&(HTMLElement.prototype.attachInternals=function(){if(this.tagName){if(this.tagName.indexOf("-")===-1)throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.")}else return{};if(s.has(this))throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");return new zs(this)}),typeof Element<"u"){let d=function(...m){const w=f.apply(this,m);if(g.set(this,w),Xe()){const x=new MutationObserver(T);window.ShadyDOM?x.observe(this,ee):x.observe(w,ee),n.set(this,x)}return w};const f=Element.prototype.attachShadow;Element.prototype.attachShadow=d}Xe()&&typeof document<"u"&&new MutationObserver(T).observe(document.documentElement,ee),typeof HTMLFormElement<"u"&&Ln(),(p||typeof window<"u"&&!window.CustomStateSet)&&qt()}}return!!customElements.polyfillWrapFlushCallback||(Pn()?typeof window<"u"&&!window.CustomStateSet&&qt(HTMLElement.prototype.attachInternals):Ns(!1)),o.forceCustomStateSetPolyfill=qt,o.forceElementInternalsPolyfill=Ns,Object.defineProperty(o,"__esModule",{value:!0}),o})({})})();class R extends F{static get properties(){return{RTL:{type:Boolean},locale:{type:String},apiRoot:{type:String,reflect:!1},postType:{type:String,reflect:!1},postID:{type:String,reflect:!1}}}get _focusTarget(){return this.shadowRoot.children[0]instanceof Element?this.shadowRoot.children[0]:null}constructor(){super(),wo(this),this.addEventListener("click",this._proxyClick.bind(this)),this.addEventListener("focus",this._proxyFocus.bind(this))}connectedCallback(){super.connectedCallback(),this.apiRoot=this.apiRoot?`${this.apiRoot}/`.replace("//","/"):"/",this.api=new Re(this.nonce,this.apiRoot)}willUpdate(e){if(this.RTL===void 0){const t=this.closest("[dir]");if(t){const i=t.getAttribute("dir");i&&(this.RTL=i.toLowerCase()==="rtl")}}if(!this.locale){const t=this.closest("[lang]");if(t){const i=t.getAttribute("lang");i&&(this.locale=i)}}if(e&&e.has("locale")&&this.locale)try{Mo(this.locale)}catch(t){console.error(t)}}_proxyClick(){this.clicked=!0}_proxyFocus(){if(this._focusTarget){if(this.clicked){this.clicked=!1;return}this._focusTarget.focus()}}focus(){this._proxyFocus()}}class vi extends R{static get formAssociated(){return!0}static get styles(){return S`
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
    `}static get properties(){return{label:{type:String},context:{type:String},type:{type:String},title:{type:String},outline:{type:Boolean},round:{type:Boolean},disabled:{type:Boolean}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--round":this.round},t=`dt-button--${this.context}`;return e[t]=!0,e}get _field(){return this.shadowRoot.querySelector("button")}get _focusTarget(){return this._field}constructor(){super(),this.context="default",this.internals=this.attachInternals()}handleClick(e){e.preventDefault(),this.type==="submit"&&this.internals.form&&this.internals.form.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0}))}render(){const e={...this.classes};return c`
      <button
        part="button"
        class=${C(e)}
        title=${this.title}
        type=${this.type}
        @click=${this.handleClick}
        ?disabled=${this.disabled}
      >
        <slot></slot>
      </button>
    `}}window.customElements.define("dt-button",vi);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const yi="important",jo=" !"+yi,H=ut(class extends ht{constructor(o){var e;if(super(o),o.type!==ct.ATTRIBUTE||o.name!=="style"||((e=o.strings)==null?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(o){return Object.keys(o).reduce((e,t)=>{const i=o[t];return i==null?e:e+`${t=t.includes("-")?t:t.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${i};`},"")}update(o,[e]){const{style:t}=o.element;if(this.ft===void 0)return this.ft=new Set(Object.keys(e)),this.render(e);for(const i of this.ft)e[i]==null&&(this.ft.delete(i),i.includes("-")?t.removeProperty(i):t[i]=null);for(const i in e){const s=e[i];if(s!=null){this.ft.add(i);const a=typeof s=="string"&&s.endsWith(jo);i.includes("-")||a?t.setProperty(i,a?s.slice(0,-11):s,a?yi:""):t[i]=s}}return V}});class wi extends R{static get styles(){return S`
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
    `}static get properties(){return{title:{type:String},context:{type:String},isHelp:{type:Boolean},isOpen:{type:Boolean},hideHeader:{type:Boolean},hideButton:{type:Boolean},buttonClass:{type:Object},buttonStyle:{type:Object},headerClass:{type:Object},imageSrc:{type:String},imageStyle:{type:Object},tileLabel:{type:String},buttonLabel:{type:String},dropdownListImg:{type:String},submitButton:{type:Boolean},closeButton:{type:Boolean}}}constructor(){super(),this.context="default",this.addEventListener("open",()=>this._openModal()),this.addEventListener("close",()=>this._closeModal())}_openModal(){this.isOpen=!0,this.shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}get formattedTitle(){if(!this.title)return"";const e=this.title.replace(/_/g," ");return e.charAt(0).toUpperCase()+e.slice(1)}_dialogHeader(e){return this.hideHeader?c``:c`
      <header>
            <h1 id="modal-field-title" class="modal-header">${this.formattedTitle}</h1>
            <button @click="${this._cancelModal}" class="toggle">${e}</button>
          </header>
      `}_closeModal(){this.isOpen=!1,this.shadowRoot.querySelector("dialog").close(),document.querySelector("body").style.overflow="initial"}_cancelModal(){this._triggerClose("cancel")}_triggerClose(e){this.dispatchEvent(new CustomEvent("close",{detail:{action:e}}))}_dialogClick(e){if(e.target.tagName!=="DIALOG")return;const t=e.target.getBoundingClientRect();(t.top<=e.clientY&&e.clientY<=t.top+t.height&&t.left<=e.clientX&&e.clientX<=t.left+t.width)===!1&&this._cancelModal()}_dialogKeypress(e){e.key==="Escape"&&this._cancelModal()}_helpMore(){return this.isHelp?c`
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
        `:null}firstUpdated(){this.isOpen&&this._openModal()}_onButtonClick(){this._triggerClose("button")}render(){const e=c`
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
    `;return c`
      <dialog
        id=""
        class="dt-modal dt-modal--width ${C(this.headerClass||{})}"
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
          ${this.closeButton?c`
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
              ${this.submitButton?c`
                <slot name="submit-button"></span>

                `:""}
              </div>
            ${this._helpMore()}
          </footer>
        </form>
      </dialog>

      ${this.hideButton?null:c`
      <button
        class="button small opener ${C(this.buttonClass||{})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${H(this.buttonStyle||{})}
      >
      ${this.dropdownListImg?c`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">`:""}
      ${this.imageSrc?c`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${H(this.imageStyle||{})}
                 />`:""}
      ${this.buttonLabel?c`${this.buttonLabel}`:""}
      </button>
      `}
    `}}window.customElements.define("dt-modal",wi);class _i extends F{static get styles(){return S`
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
    `}static get properties(){return{options:{type:Array},label:{type:String},isModal:{type:Boolean},buttonStyle:{type:Object},default:{type:Boolean},context:{type:String}}}get classes(){const e={"dt-dropdown":!0},t=`dt-dropdown--${this.context}`;return e[t]=!0,e}render(){return c`
    <div class="dropdown">
    <button
    class=${C(this.classes)}
    style=${H(this.buttonStyle||{})}
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

    ${this.options?this.options.map(e=>c`
        ${e.isModal?c`
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
                ${e.icon?c`<img
                   src="${e.icon}"
                   alt="${e.label} icon"
                   class="icon"
                 />`:""}
                ${e.label}
                </button>
              </li>
            `:c`
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
    `}_redirectToHref(e){let t=e;/^https?:\/\//i.test(t)||(t=`http://${t}`),window.open(t,"_blank")}_openDialog(e){const t=e.replace(/\s/g,"-").toLowerCase();document.querySelector(`#${t}`).shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}_handleHover(){const e=this.shadowRoot.querySelector("ul");e.style.display="block"}_handleMouseLeave(){const e=this.shadowRoot.querySelector("ul");e.style.display="none"}}window.customElements.define("dt-dropdown",_i);class zo extends R{static get styles(){return S`
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
    `}static get properties(){return{key:{type:String},metric:{type:Object},group:{type:Object},active:{type:Boolean,reflect:!0},missingIcon:{type:String},handleSave:{type:Function}}}render(){const{metric:e,active:t,missingIcon:i=`${window.wpApiShare.template_dir}/dt-assets/images/groups/missing.svg`}=this;return c`<div
      class=${C({"health-item":!0,"health-item--active":t})}
      title="${e.description}"
      @click="${this._handleClick}"
    >
      <img src="${e.icon?e.icon:i}" />
    </div>`}async _handleClick(){if(!this.handleSave)return;const e=!this.active;this.active=e;const t={health_metrics:{values:[{value:this.key,delete:!e}]}};try{await this.handleSave(this.group.ID,t)}catch(i){console.error(i);return}e?this.group.health_metrics.push(this.key):this.group.health_metrics.pop(this.key)}}window.customElements.define("dt-church-health-icon",zo);class $i extends R{static get styles(){return S`
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
    `}static get properties(){return{groupId:{type:Number},group:{type:Object,reflect:!1},settings:{type:Object,reflect:!1},errorMessage:{type:String,attribute:!1},missingIcon:{type:String},handleSave:{type:Function}}}get metrics(){const e=this.settings||[];return Object.values(e).length?Object.entries(e).filter(([i,s])=>i!=="church_commitment"):[]}get isCommited(){return!this.group||!this.group.health_metrics?!1:this.group.health_metrics.includes("church_commitment")}connectedCallback(){super.connectedCallback(),this.fetch()}adoptedCallback(){this.distributeItems()}updated(){this.distributeItems()}async fetch(){try{const e=[this.fetchSettings(),this.fetchGroup()];let[t,i]=await Promise.all(e);this.settings=t,this.post=i,t||(this.errorMessage="Error loading settings"),i||(this.errorMessage="Error loading group")}catch(e){console.error(e)}}fetchGroup(){if(this.group)return Promise.resolve(this.group);fetch(`/wp-json/dt-posts/v2/groups/${this.groupId}`).then(e=>e.json())}fetchSettings(){return this.settings?Promise.resolve(this.settings):fetch("/wp-json/dt-posts/v2/groups/settings").then(e=>e.json())}findMetric(e){const t=this.metrics.find(i=>i.key===e);return t?t.value:null}render(){if(!this.group||!this.metrics.length)return c`<dt-spinner></dt-spinner>`;const e=this.group.health_metrics||[];return this.errorMessage&&c`<dt-alert type="error">${this.errorMessage}</dt-alert>`,c`
      <div class="health-circle__wrapper">
        <div class="health-circle__container">
          <div
            class=${C({"health-circle":!0,"health-circle--committed":this.isCommited})}
          >
            <div class="health-circle__grid">
              ${this.metrics.map(([t,i],s)=>c`<dt-church-health-icon
                    key="${t}"
                    .group="${this.group}"
                    .metric=${i}
                    .active=${e.indexOf(t)!==-1}
                    .style="--i: ${s+1}"
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
    `}distributeItems(){const e=this.renderRoot.querySelector(".health-circle__container");let s=e.querySelectorAll("dt-church-health-icon").length,a=Math.tan(Math.PI/s);e.style.setProperty("--m",s),e.style.setProperty("--tan",+a.toFixed(2))}async toggleClick(e){const{handleSave:t}=this;if(!t)return;let i=this.renderRoot.querySelector("dt-toggle"),s=i.toggleAttribute("checked");this.group.health_metrics||(this.group.health_metrics=[]);const a={health_metrics:{values:[{value:"church_commitment",delete:!s}]}};try{await t(this.group.ID,a)}catch(r){i.toggleAttribute("checked",!s),console.error(r);return}s?this.group.health_metrics.push("church_commitment"):this.group.health_metrics.pop("church_commitment"),this.requestUpdate()}_isChecked(){return Object.hasOwn(this.group,"health_metrics")?this.group.health_metrics.includes("church_commitment")?this.isChurch=!0:this.isChurch=!1:this.isChurch=!1}}window.customElements.define("dt-church-health-circle",$i);/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/const xi=Object.freeze({left:0,top:0,width:16,height:16}),Ne=Object.freeze({rotate:0,vFlip:!1,hFlip:!1}),we=Object.freeze({...xi,...Ne}),mt=Object.freeze({...we,body:"",hidden:!1}),Do=Object.freeze({width:null,height:null}),ki=Object.freeze({...Do,...Ne});function Ro(o,e=0){const t=o.replace(/^-?[0-9.]*/,"");function i(s){for(;s<0;)s+=4;return s%4}if(t===""){const s=parseInt(o);return isNaN(s)?0:i(s)}else if(t!==o){let s=0;switch(t){case"%":s=25;break;case"deg":s=90}if(s){let a=parseFloat(o.slice(0,o.length-t.length));return isNaN(a)?0:(a=a/s,a%1===0?i(a):0)}}return e}const No=/[\s,]+/;function qo(o,e){e.split(No).forEach(t=>{switch(t.trim()){case"horizontal":o.hFlip=!0;break;case"vertical":o.vFlip=!0;break}})}const Si={...ki,preserveAspectRatio:""};function Ti(o){const e={...Si},t=(i,s)=>o.getAttribute(i)||s;return e.width=t("width",null),e.height=t("height",null),e.rotate=Ro(t("rotate","")),qo(e,t("flip","")),e.preserveAspectRatio=t("preserveAspectRatio",t("preserveaspectratio","")),e}function Fo(o,e){for(const t in Si)if(o[t]!==e[t])return!0;return!1}const _e=/^[a-z0-9]+(-[a-z0-9]+)*$/,$e=(o,e,t,i="")=>{const s=o.split(":");if(o.slice(0,1)==="@"){if(s.length<2||s.length>3)return null;i=s.shift().slice(1)}if(s.length>3||!s.length)return null;if(s.length>1){const n=s.pop(),l=s.pop(),u={provider:s.length>0?s[0]:i,prefix:l,name:n};return e&&!qe(u)?null:u}const a=s[0],r=a.split("-");if(r.length>1){const n={provider:i,prefix:r.shift(),name:r.join("-")};return e&&!qe(n)?null:n}if(t&&i===""){const n={provider:i,prefix:"",name:a};return e&&!qe(n,t)?null:n}return null},qe=(o,e)=>o?!!((o.provider===""||o.provider.match(_e))&&(e&&o.prefix===""||o.prefix.match(_e))&&o.name.match(_e)):!1;function Uo(o,e){const t={};!o.hFlip!=!e.hFlip&&(t.hFlip=!0),!o.vFlip!=!e.vFlip&&(t.vFlip=!0);const i=((o.rotate||0)+(e.rotate||0))%4;return i&&(t.rotate=i),t}function Ei(o,e){const t=Uo(o,e);for(const i in mt)i in Ne?i in o&&!(i in t)&&(t[i]=Ne[i]):i in e?t[i]=e[i]:i in o&&(t[i]=o[i]);return t}function Vo(o,e){const t=o.icons,i=o.aliases||Object.create(null),s=Object.create(null);function a(r){if(t[r])return s[r]=[];if(!(r in s)){s[r]=null;const n=i[r]&&i[r].parent,l=n&&a(n);l&&(s[r]=[n].concat(l))}return s[r]}return Object.keys(t).concat(Object.keys(i)).forEach(a),s}function Bo(o,e,t){const i=o.icons,s=o.aliases||Object.create(null);let a={};function r(n){a=Ei(i[n]||s[n],a)}return r(e),t.forEach(r),Ei(o,a)}function Ai(o,e){const t=[];if(typeof o!="object"||typeof o.icons!="object")return t;o.not_found instanceof Array&&o.not_found.forEach(s=>{e(s,null),t.push(s)});const i=Vo(o);for(const s in i){const a=i[s];a&&(e(s,Bo(o,s,a)),t.push(s))}return t}const Ho={provider:"",aliases:{},not_found:{},...xi};function vt(o,e){for(const t in e)if(t in o&&typeof o[t]!=typeof e[t])return!1;return!0}function Oi(o){if(typeof o!="object"||o===null)return null;const e=o;if(typeof e.prefix!="string"||!o.icons||typeof o.icons!="object"||!vt(o,Ho))return null;const t=e.icons;for(const s in t){const a=t[s];if(!s.match(_e)||typeof a.body!="string"||!vt(a,mt))return null}const i=e.aliases||Object.create(null);for(const s in i){const a=i[s],r=a.parent;if(!s.match(_e)||typeof r!="string"||!t[r]&&!i[r]||!vt(a,mt))return null}return e}const Fe=Object.create(null);function Go(o,e){return{provider:o,prefix:e,icons:Object.create(null),missing:new Set}}function X(o,e){const t=Fe[o]||(Fe[o]=Object.create(null));return t[e]||(t[e]=Go(o,e))}function yt(o,e){return Oi(e)?Ai(e,(t,i)=>{i?o.icons[t]=i:o.missing.add(t)}):[]}function Wo(o,e,t){try{if(typeof t.body=="string")return o.icons[e]={...t},!0}catch{}return!1}function Ko(o,e){let t=[];return(typeof o=="string"?[o]:Object.keys(Fe)).forEach(s=>{(typeof s=="string"&&typeof e=="string"?[e]:Object.keys(Fe[s]||{})).forEach(r=>{const n=X(s,r);t=t.concat(Object.keys(n.icons).map(l=>(s!==""?"@"+s+":":"")+r+":"+l))})}),t}let xe=!1;function Ci(o){return typeof o=="boolean"&&(xe=o),xe}function ke(o){const e=typeof o=="string"?$e(o,!0,xe):o;if(e){const t=X(e.provider,e.prefix),i=e.name;return t.icons[i]||(t.missing.has(i)?null:void 0)}}function Li(o,e){const t=$e(o,!0,xe);if(!t)return!1;const i=X(t.provider,t.prefix);return Wo(i,t.name,e)}function Pi(o,e){if(typeof o!="object")return!1;if(typeof e!="string"&&(e=o.provider||""),xe&&!e&&!o.prefix){let s=!1;return Oi(o)&&(o.prefix="",Ai(o,(a,r)=>{r&&Li(a,r)&&(s=!0)})),s}const t=o.prefix;if(!qe({provider:e,prefix:t,name:"a"}))return!1;const i=X(e,t);return!!yt(i,o)}function Zo(o){return!!ke(o)}function Jo(o){const e=ke(o);return e?{...we,...e}:null}function Qo(o){const e={loaded:[],missing:[],pending:[]},t=Object.create(null);o.sort((s,a)=>s.provider!==a.provider?s.provider.localeCompare(a.provider):s.prefix!==a.prefix?s.prefix.localeCompare(a.prefix):s.name.localeCompare(a.name));let i={provider:"",prefix:"",name:""};return o.forEach(s=>{if(i.name===s.name&&i.prefix===s.prefix&&i.provider===s.provider)return;i=s;const a=s.provider,r=s.prefix,n=s.name,l=t[a]||(t[a]=Object.create(null)),u=l[r]||(l[r]=X(a,r));let b;n in u.icons?b=e.loaded:r===""||u.missing.has(n)?b=e.missing:b=e.pending;const g={provider:a,prefix:r,name:n};b.push(g)}),e}function Ii(o,e){o.forEach(t=>{const i=t.loaderCallbacks;i&&(t.loaderCallbacks=i.filter(s=>s.id!==e))})}function Xo(o){o.pendingCallbacksFlag||(o.pendingCallbacksFlag=!0,setTimeout(()=>{o.pendingCallbacksFlag=!1;const e=o.loaderCallbacks?o.loaderCallbacks.slice(0):[];if(!e.length)return;let t=!1;const i=o.provider,s=o.prefix;e.forEach(a=>{const r=a.icons,n=r.pending.length;r.pending=r.pending.filter(l=>{if(l.prefix!==s)return!0;const u=l.name;if(o.icons[u])r.loaded.push({provider:i,prefix:s,name:u});else if(o.missing.has(u))r.missing.push({provider:i,prefix:s,name:u});else return t=!0,!0;return!1}),r.pending.length!==n&&(t||Ii([o],a.id),a.callback(r.loaded.slice(0),r.missing.slice(0),r.pending.slice(0),a.abort))})}))}let Yo=0;function ea(o,e,t){const i=Yo++,s=Ii.bind(null,t,i);if(!e.pending.length)return s;const a={id:i,icons:e,callback:o,abort:s};return t.forEach(r=>{(r.loaderCallbacks||(r.loaderCallbacks=[])).push(a)}),s}const wt=Object.create(null);function Mi(o,e){wt[o]=e}function _t(o){return wt[o]||wt[""]}function ta(o,e=!0,t=!1){const i=[];return o.forEach(s=>{const a=typeof s=="string"?$e(s,e,t):s;a&&i.push(a)}),i}var ia={resources:[],index:0,timeout:2e3,rotate:750,random:!1,dataAfterTimeout:!1};function sa(o,e,t,i){const s=o.resources.length,a=o.random?Math.floor(Math.random()*s):o.index;let r;if(o.random){let T=o.resources.slice(0);for(r=[];T.length>1;){const D=Math.floor(Math.random()*T.length);r.push(T[D]),T=T.slice(0,D).concat(T.slice(D+1))}r=r.concat(T)}else r=o.resources.slice(a).concat(o.resources.slice(0,a));const n=Date.now();let l="pending",u=0,b,g=null,v=[],y=[];typeof i=="function"&&y.push(i);function _(){g&&(clearTimeout(g),g=null)}function L(){l==="pending"&&(l="aborted"),_(),v.forEach(T=>{T.status==="pending"&&(T.status="aborted")}),v=[]}function N(T,D){D&&(y=[]),typeof T=="function"&&y.push(T)}function M(){return{startTime:n,payload:e,status:l,queriesSent:u,queriesPending:v.length,subscribe:N,abort:L}}function P(){l="failed",y.forEach(T=>{T(void 0,b)})}function Ce(){v.forEach(T=>{T.status==="pending"&&(T.status="aborted")}),v=[]}function Ze(T,D,ne){const ee=D!=="success";switch(v=v.filter(I=>I!==T),l){case"pending":break;case"failed":if(ee||!o.dataAfterTimeout)return;break;default:return}if(D==="abort"){b=ne,P();return}if(ee){b=ne,v.length||(r.length?Y():P());return}if(_(),Ce(),!o.random){const I=o.resources.indexOf(T.resource);I!==-1&&I!==o.index&&(o.index=I)}l="completed",y.forEach(I=>{I(ne)})}function Y(){if(l!=="pending")return;_();const T=r.shift();if(T===void 0){if(v.length){g=setTimeout(()=>{_(),l==="pending"&&(Ce(),P())},o.timeout);return}P();return}const D={status:"pending",resource:T,callback:(ne,ee)=>{Ze(D,ne,ee)}};v.push(D),u++,g=setTimeout(Y,o.rotate),t(T,e,D.callback)}return setTimeout(Y),M}function ji(o){const e={...ia,...o};let t=[];function i(){t=t.filter(n=>n().status==="pending")}function s(n,l,u){const b=sa(e,n,l,(g,v)=>{i(),u&&u(g,v)});return t.push(b),b}function a(n){return t.find(l=>n(l))||null}return{query:s,find:a,setIndex:n=>{e.index=n},getIndex:()=>e.index,cleanup:i}}function $t(o){let e;if(typeof o.resources=="string")e=[o.resources];else if(e=o.resources,!(e instanceof Array)||!e.length)return null;return{resources:e,path:o.path||"/",maxURL:o.maxURL||500,rotate:o.rotate||750,timeout:o.timeout||5e3,random:o.random===!0,index:o.index||0,dataAfterTimeout:o.dataAfterTimeout!==!1}}const Ue=Object.create(null),Se=["https://api.simplesvg.com","https://api.unisvg.com"],Ve=[];for(;Se.length>0;)Se.length===1||Math.random()>.5?Ve.push(Se.shift()):Ve.push(Se.pop());Ue[""]=$t({resources:["https://api.iconify.design"].concat(Ve)});function zi(o,e){const t=$t(e);return t===null?!1:(Ue[o]=t,!0)}function Be(o){return Ue[o]}function oa(){return Object.keys(Ue)}function Di(){}const xt=Object.create(null);function aa(o){if(!xt[o]){const e=Be(o);if(!e)return;const t=ji(e),i={config:e,redundancy:t};xt[o]=i}return xt[o]}function Ri(o,e,t){let i,s;if(typeof o=="string"){const a=_t(o);if(!a)return t(void 0,424),Di;s=a.send;const r=aa(o);r&&(i=r.redundancy)}else{const a=$t(o);if(a){i=ji(a);const r=o.resources?o.resources[0]:"",n=_t(r);n&&(s=n.send)}}return!i||!s?(t(void 0,424),Di):i.query(e,s,t)().abort}const Ni="iconify2",Te="iconify",qi=Te+"-count",Fi=Te+"-version",Ui=36e5,ra=168;function kt(o,e){try{return o.getItem(e)}catch{}}function St(o,e,t){try{return o.setItem(e,t),!0}catch{}}function Vi(o,e){try{o.removeItem(e)}catch{}}function Tt(o,e){return St(o,qi,e.toString())}function Et(o){return parseInt(kt(o,qi))||0}const ae={local:!0,session:!0},Bi={local:new Set,session:new Set};let At=!1;function na(o){At=o}let He=typeof window>"u"?{}:window;function Hi(o){const e=o+"Storage";try{if(He&&He[e]&&typeof He[e].length=="number")return He[e]}catch{}ae[o]=!1}function Gi(o,e){const t=Hi(o);if(!t)return;const i=kt(t,Fi);if(i!==Ni){if(i){const n=Et(t);for(let l=0;l<n;l++)Vi(t,Te+l.toString())}St(t,Fi,Ni),Tt(t,0);return}const s=Math.floor(Date.now()/Ui)-ra,a=n=>{const l=Te+n.toString(),u=kt(t,l);if(typeof u=="string"){try{const b=JSON.parse(u);if(typeof b=="object"&&typeof b.cached=="number"&&b.cached>s&&typeof b.provider=="string"&&typeof b.data=="object"&&typeof b.data.prefix=="string"&&e(b,n))return!0}catch{}Vi(t,l)}};let r=Et(t);for(let n=r-1;n>=0;n--)a(n)||(n===r-1?(r--,Tt(t,r)):Bi[o].add(n))}function Wi(){if(!At){na(!0);for(const o in ae)Gi(o,e=>{const t=e.data,i=e.provider,s=t.prefix,a=X(i,s);if(!yt(a,t).length)return!1;const r=t.lastModified||-1;return a.lastModifiedCached=a.lastModifiedCached?Math.min(a.lastModifiedCached,r):r,!0})}}function la(o,e){const t=o.lastModifiedCached;if(t&&t>=e)return t===e;if(o.lastModifiedCached=e,t)for(const i in ae)Gi(i,s=>{const a=s.data;return s.provider!==o.provider||a.prefix!==o.prefix||a.lastModified===e});return!0}function da(o,e){At||Wi();function t(i){let s;if(!ae[i]||!(s=Hi(i)))return;const a=Bi[i];let r;if(a.size)a.delete(r=Array.from(a).shift());else if(r=Et(s),!Tt(s,r+1))return;const n={cached:Math.floor(Date.now()/Ui),provider:o.provider,data:e};return St(s,Te+r.toString(),JSON.stringify(n))}e.lastModified&&!la(o,e.lastModified)||Object.keys(e.icons).length&&(e.not_found&&(e=Object.assign({},e),delete e.not_found),t("local")||t("session"))}function Ki(){}function ca(o){o.iconsLoaderFlag||(o.iconsLoaderFlag=!0,setTimeout(()=>{o.iconsLoaderFlag=!1,Xo(o)}))}function ua(o,e){o.iconsToLoad?o.iconsToLoad=o.iconsToLoad.concat(e).sort():o.iconsToLoad=e,o.iconsQueueFlag||(o.iconsQueueFlag=!0,setTimeout(()=>{o.iconsQueueFlag=!1;const{provider:t,prefix:i}=o,s=o.iconsToLoad;delete o.iconsToLoad;let a;if(!s||!(a=_t(t)))return;a.prepare(t,i,s).forEach(n=>{Ri(t,n,l=>{if(typeof l!="object")n.icons.forEach(u=>{o.missing.add(u)});else try{const u=yt(o,l);if(!u.length)return;const b=o.pendingIcons;b&&u.forEach(g=>{b.delete(g)}),da(o,l)}catch(u){console.error(u)}ca(o)})})}))}const Ot=(o,e)=>{const t=ta(o,!0,Ci()),i=Qo(t);if(!i.pending.length){let l=!0;return e&&setTimeout(()=>{l&&e(i.loaded,i.missing,i.pending,Ki)}),()=>{l=!1}}const s=Object.create(null),a=[];let r,n;return i.pending.forEach(l=>{const{provider:u,prefix:b}=l;if(b===n&&u===r)return;r=u,n=b,a.push(X(u,b));const g=s[u]||(s[u]=Object.create(null));g[b]||(g[b]=[])}),i.pending.forEach(l=>{const{provider:u,prefix:b,name:g}=l,v=X(u,b),y=v.pendingIcons||(v.pendingIcons=new Set);y.has(g)||(y.add(g),s[u][b].push(g))}),a.forEach(l=>{const{provider:u,prefix:b}=l;s[u][b].length&&ua(l,s[u][b])}),e?ea(e,i,a):Ki},ha=o=>new Promise((e,t)=>{const i=typeof o=="string"?$e(o,!0):o;if(!i){t(o);return}Ot([i||o],s=>{if(s.length&&i){const a=ke(i);if(a){e({...we,...a});return}}t(o)})});function pa(o){try{const e=typeof o=="string"?JSON.parse(o):o;if(typeof e.body=="string")return{...e}}catch{}}function fa(o,e){const t=typeof o=="string"?$e(o,!0,!0):null;if(!t){const a=pa(o);return{value:o,data:a}}const i=ke(t);if(i!==void 0||!t.prefix)return{value:o,name:t,data:i};const s=Ot([t],()=>e(o,t,ke(t)));return{value:o,name:t,loading:s}}function Ct(o){return o.hasAttribute("inline")}let Zi=!1;try{Zi=navigator.vendor.indexOf("Apple")===0}catch{}function ba(o,e){switch(e){case"svg":case"bg":case"mask":return e}return e!=="style"&&(Zi||o.indexOf("<a")===-1)?"svg":o.indexOf("currentColor")===-1?"bg":"mask"}const ga=/(-?[0-9.]*[0-9]+[0-9.]*)/g,ma=/^-?[0-9.]*[0-9]+[0-9.]*$/g;function Lt(o,e,t){if(e===1)return o;if(t=t||100,typeof o=="number")return Math.ceil(o*e*t)/t;if(typeof o!="string")return o;const i=o.split(ga);if(i===null||!i.length)return o;const s=[];let a=i.shift(),r=ma.test(a);for(;;){if(r){const n=parseFloat(a);isNaN(n)?s.push(a):s.push(Math.ceil(n*e*t)/t)}else s.push(a);if(a=i.shift(),a===void 0)return s.join("");r=!r}}function Ji(o,e){const t={...we,...o},i={...ki,...e},s={left:t.left,top:t.top,width:t.width,height:t.height};let a=t.body;[t,i].forEach(y=>{const _=[],L=y.hFlip,N=y.vFlip;let M=y.rotate;L?N?M+=2:(_.push("translate("+(s.width+s.left).toString()+" "+(0-s.top).toString()+")"),_.push("scale(-1 1)"),s.top=s.left=0):N&&(_.push("translate("+(0-s.left).toString()+" "+(s.height+s.top).toString()+")"),_.push("scale(1 -1)"),s.top=s.left=0);let P;switch(M<0&&(M-=Math.floor(M/4)*4),M=M%4,M){case 1:P=s.height/2+s.top,_.unshift("rotate(90 "+P.toString()+" "+P.toString()+")");break;case 2:_.unshift("rotate(180 "+(s.width/2+s.left).toString()+" "+(s.height/2+s.top).toString()+")");break;case 3:P=s.width/2+s.left,_.unshift("rotate(-90 "+P.toString()+" "+P.toString()+")");break}M%2===1&&(s.left!==s.top&&(P=s.left,s.left=s.top,s.top=P),s.width!==s.height&&(P=s.width,s.width=s.height,s.height=P)),_.length&&(a='<g transform="'+_.join(" ")+'">'+a+"</g>")});const r=i.width,n=i.height,l=s.width,u=s.height;let b,g;return r===null?(g=n===null?"1em":n==="auto"?u:n,b=Lt(g,l/u)):(b=r==="auto"?l:r,g=n===null?Lt(b,u/l):n==="auto"?u:n),{attributes:{width:b.toString(),height:g.toString(),viewBox:s.left.toString()+" "+s.top.toString()+" "+l.toString()+" "+u.toString()},body:a}}let Ge=(()=>{let o;try{if(o=fetch,typeof o=="function")return o}catch{}})();function va(o){Ge=o}function ya(){return Ge}function wa(o,e){const t=Be(o);if(!t)return 0;let i;if(!t.maxURL)i=0;else{let s=0;t.resources.forEach(r=>{s=Math.max(s,r.length)});const a=e+".json?icons=";i=t.maxURL-s-t.path.length-a.length}return i}function _a(o){return o===404}const $a=(o,e,t)=>{const i=[],s=wa(o,e),a="icons";let r={type:a,provider:o,prefix:e,icons:[]},n=0;return t.forEach((l,u)=>{n+=l.length+1,n>=s&&u>0&&(i.push(r),r={type:a,provider:o,prefix:e,icons:[]},n=l.length),r.icons.push(l)}),i.push(r),i};function xa(o){if(typeof o=="string"){const e=Be(o);if(e)return e.path}return"/"}const ka={prepare:$a,send:(o,e,t)=>{if(!Ge){t("abort",424);return}let i=xa(e.provider);switch(e.type){case"icons":{const a=e.prefix,n=e.icons.join(","),l=new URLSearchParams({icons:n});i+=a+".json?"+l.toString();break}case"custom":{const a=e.uri;i+=a.slice(0,1)==="/"?a.slice(1):a;break}default:t("abort",400);return}let s=503;Ge(o+i).then(a=>{const r=a.status;if(r!==200){setTimeout(()=>{t(_a(r)?"abort":"next",r)});return}return s=501,a.json()}).then(a=>{if(typeof a!="object"||a===null){setTimeout(()=>{a===404?t("abort",a):t("next",s)});return}setTimeout(()=>{t("success",a)})}).catch(()=>{t("next",s)})}};function Qi(o,e){switch(o){case"local":case"session":ae[o]=e;break;case"all":for(const t in ae)ae[t]=e;break}}function Xi(){Mi("",ka),Ci(!0);let o;try{o=window}catch{}if(o){if(Wi(),o.IconifyPreload!==void 0){const t=o.IconifyPreload,i="Invalid IconifyPreload syntax.";typeof t=="object"&&t!==null&&(t instanceof Array?t:[t]).forEach(s=>{try{(typeof s!="object"||s===null||s instanceof Array||typeof s.icons!="object"||typeof s.prefix!="string"||!Pi(s))&&console.error(i)}catch{console.error(i)}})}if(o.IconifyProviders!==void 0){const t=o.IconifyProviders;if(typeof t=="object"&&t!==null)for(const i in t){const s="IconifyProviders["+i+"] is invalid.";try{const a=t[i];if(typeof a!="object"||!a||a.resources===void 0)continue;zi(i,a)||console.error(s)}catch{console.error(s)}}}}return{enableCache:t=>Qi(t,!0),disableCache:t=>Qi(t,!1),iconExists:Zo,getIcon:Jo,listIcons:Ko,addIcon:Li,addCollection:Pi,calculateSize:Lt,buildIcon:Ji,loadIcons:Ot,loadIcon:ha,addAPIProvider:zi,_api:{getAPIConfig:Be,setAPIModule:Mi,sendAPIQuery:Ri,setFetch:va,getFetch:ya,listAPIProviders:oa}}}function Yi(o,e){let t=o.indexOf("xlink:")===-1?"":' xmlns:xlink="http://www.w3.org/1999/xlink"';for(const i in e)t+=" "+i+'="'+e[i]+'"';return'<svg xmlns="http://www.w3.org/2000/svg"'+t+">"+o+"</svg>"}function Sa(o){return o.replace(/"/g,"'").replace(/%/g,"%25").replace(/#/g,"%23").replace(/</g,"%3C").replace(/>/g,"%3E").replace(/\s+/g," ")}function Ta(o){return'url("data:image/svg+xml,'+Sa(o)+'")'}const Pt={"background-color":"currentColor"},es={"background-color":"transparent"},ts={image:"var(--svg)",repeat:"no-repeat",size:"100% 100%"},is={"-webkit-mask":Pt,mask:Pt,background:es};for(const o in is){const e=is[o];for(const t in ts)e[o+"-"+t]=ts[t]}function ss(o){return o+(o.match(/^[-0-9.]+$/)?"px":"")}function Ea(o,e,t){const i=document.createElement("span");let s=o.body;s.indexOf("<a")!==-1&&(s+="<!-- "+Date.now()+" -->");const a=o.attributes,r=Yi(s,{...a,width:e.width+"",height:e.height+""}),n=Ta(r),l=i.style,u={"--svg":n,width:ss(a.width),height:ss(a.height),...t?Pt:es};for(const b in u)l.setProperty(b,u[b]);return i}function Aa(o){const e=document.createElement("span");return e.innerHTML=Yi(o.body,o.attributes),e.firstChild}function os(o,e){const t=e.icon.data,i=e.customisations,s=Ji(t,i);i.preserveAspectRatio&&(s.attributes.preserveAspectRatio=i.preserveAspectRatio);const a=e.renderedMode;let r;switch(a){case"svg":r=Aa(s);break;default:r=Ea(s,{...we,...t},a==="mask")}const n=Array.from(o.childNodes).find(l=>{const u=l.tagName&&l.tagName.toUpperCase();return u==="SPAN"||u==="SVG"});n?r.tagName==="SPAN"&&n.tagName===r.tagName?n.setAttribute("style",r.getAttribute("style")):o.replaceChild(r,n):o.appendChild(r)}const It="data-style";function as(o,e){let t=Array.from(o.childNodes).find(i=>i.hasAttribute&&i.hasAttribute(It));t||(t=document.createElement("style"),t.setAttribute(It,It),o.appendChild(t)),t.textContent=":host{display:inline-block;vertical-align:"+(e?"-0.125em":"0")+"}span,svg{display:block}"}function rs(o,e,t){const i=t&&(t.rendered?t:t.lastRender);return{rendered:!1,inline:e,icon:o,lastRender:i}}function Oa(o="iconify-icon"){let e,t;try{e=window.customElements,t=window.HTMLElement}catch{return}if(!e||!t)return;const i=e.get(o);if(i)return i;const s=["icon","mode","inline","width","height","rotate","flip"],a=class extends t{constructor(){super();Ye(this,"_shadowRoot");Ye(this,"_state");Ye(this,"_checkQueued",!1);const l=this._shadowRoot=this.attachShadow({mode:"open"}),u=Ct(this);as(l,u),this._state=rs({value:""},u),this._queueCheck()}static get observedAttributes(){return s.slice(0)}attributeChangedCallback(l){if(l==="inline"){const u=Ct(this),b=this._state;u!==b.inline&&(b.inline=u,as(this._shadowRoot,u))}else this._queueCheck()}get icon(){const l=this.getAttribute("icon");if(l&&l.slice(0,1)==="{")try{return JSON.parse(l)}catch{}return l}set icon(l){typeof l=="object"&&(l=JSON.stringify(l)),this.setAttribute("icon",l)}get inline(){return Ct(this)}set inline(l){this.setAttribute("inline",l?"true":null)}restartAnimation(){const l=this._state;if(l.rendered){const u=this._shadowRoot;if(l.renderedMode==="svg")try{u.lastChild.setCurrentTime(0);return}catch{}os(u,l)}}get status(){const l=this._state;return l.rendered?"rendered":l.icon.data===null?"failed":"loading"}_queueCheck(){this._checkQueued||(this._checkQueued=!0,setTimeout(()=>{this._check()}))}_check(){if(!this._checkQueued)return;this._checkQueued=!1;const l=this._state,u=this.getAttribute("icon");if(u!==l.icon.value){this._iconChanged(u);return}if(!l.rendered)return;const b=this.getAttribute("mode"),g=Ti(this);(l.attrMode!==b||Fo(l.customisations,g))&&this._renderIcon(l.icon,g,b)}_iconChanged(l){const u=fa(l,(b,g,v)=>{const y=this._state;if(y.rendered||this.getAttribute("icon")!==b)return;const _={value:b,name:g,data:v};_.data?this._gotIconData(_):y.icon=_});u.data?this._gotIconData(u):this._state=rs(u,this._state.inline,this._state)}_gotIconData(l){this._checkQueued=!1,this._renderIcon(l,Ti(this),this.getAttribute("mode"))}_renderIcon(l,u,b){const g=ba(l.data.body,b),v=this._state.inline;os(this._shadowRoot,this._state={rendered:!0,icon:l,inline:v,customisations:u,attrMode:b,renderedMode:g})}};s.forEach(n=>{n in a.prototype||Object.defineProperty(a.prototype,n,{get:function(){return this.getAttribute(n)},set:function(l){this.setAttribute(n,l)}})});const r=Xi();for(const n in r)a[n]=a.prototype[n]=r[n];return e.define(o,a),a}const Ca=Oa()||Xi(),{enableCache:Nn,disableCache:qn,iconExists:Fn,getIcon:Un,listIcons:Vn,addIcon:Bn,addCollection:Hn,calculateSize:Gn,buildIcon:Wn,loadIcons:Kn,loadIcon:Zn,addAPIProvider:Jn,_api:Qn}=Ca;class ns extends R{static get styles(){return S`
      :root, .icon-container {
        font-size: inherit;
        color: inherit;
        display: inline-flex;
        width: fit-content;
        height: fit-content;
        position: relative;
        font-family: var(--font-family);
      }
      .tooltip {
        --tt-padding: 0.25rem;
        position: absolute;
        right: 0px;
        top: calc(-1lh - var(--tt-padding) - var(--tt-padding) - 4px);
        min-width: max-content;
        border: solid 1px currentcolor;
        background-color: var(--dt-form-background-color, var(--surface-1));
        padding: var(--tt-padding);
        border-radius: .25rem;
        text-align: end;
        z-index: 1;
        display:block;
      }
      .tooltip:before {
        position: absolute;
        right: .7rem;
        top: calc(1lh + var(--tt-padding) + var(--tt-padding) + 1px);
        content: " ";
        border-width: .25rem;
        border-style: solid;
        border-color: currentcolor transparent transparent transparent;
      }
      .tooltip[hidden] {
        display: none;
      }
    `}static get properties(){return{...super.properties,icon:{type:String},tooltip:{type:String},tooltip_open:{type:Boolean},size:{type:String}}}_showTooltip(){this.tooltip_open?this.tooltip_open=!1:this.tooltip_open=!0}render(){const e=this.tooltip?c`<div class="tooltip" ?hidden=${this.tooltip_open}>${this.tooltip}</div>`:null;return c`
      <div class="icon-container">
      <iconify-icon icon=${this.icon} width="${this.size}" @click=${this._showTooltip}></iconify-icon>
      ${e}
      </div>
    `}}window.customElements.define("dt-icon",ns);class ls extends R{static get styles(){return S`
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
    `}static get properties(){return{icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String}}}firstUpdated(){const t=this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({flatten:!0});for(const i of t)i.style.height="100%",i.style.width="auto"}get _slottedChildren(){return this.shadowRoot.querySelector("slot").assignedElements({flatten:!0})}render(){const e=c`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;return c`
      <div class="label">
        <span class="icon">
          <slot name="icon-start">
            ${this.icon?c`<img src="${this.icon}" alt="${this.iconAltText}" />`:null}
          </slot>
        </span>
        <slot></slot>

        ${this.private?c`<span class="icon private">
              ${e}
              <span class="tooltip"
                >${this.privateLabel||A("Private Field: Only I can see its content")}</span
              >
            </span> `:null}
      </div>
    `}}window.customElements.define("dt-label",ls);class z extends R{static get formAssociated(){return!0}static get styles(){return[S`
        .input-group {
          position: relative;
        }
        .input-group.disabled {
          background-color: var(--disabled-color);
        }

        /* === Inline Icons === */
        .icon-overlay {
          position: absolute;
          inset-inline-end: 1rem;
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
      `]}static get properties(){return{...super.properties,name:{type:String},label:{type:String},icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String},disabled:{type:Boolean},required:{type:Boolean},requiredMessage:{type:String},touched:{type:Boolean,state:!0},invalid:{type:Boolean,state:!0},error:{type:String},loading:{type:Boolean},saved:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector("input, textarea, select")}get _focusTarget(){return this._field}constructor(){super(),this.touched=!1,this.invalid=!1,this.internals=this.attachInternals(),this.addEventListener("invalid",e=>{e&&e.preventDefault(),this.touched=!0,this._validateRequired()})}firstUpdated(...e){super.firstUpdated(...e);const t=z._jsonToFormData(this.value,this.name);this.internals.setFormValue(t),this._validateRequired()}static _buildFormData(e,t,i){if(t&&typeof t=="object"&&!(t instanceof Date)&&!(t instanceof File))Object.keys(t).forEach(s=>{this._buildFormData(e,t[s],i?`${i}[${s}]`:s)});else{const s=t??"";e.append(i,s)}}static _jsonToFormData(e,t){const i=new FormData;return z._buildFormData(i,e,t),i}_setFormValue(e){const t=z._jsonToFormData(e,this.name);this.internals.setFormValue(t,e),this._validateRequired(),this.touched=!0}_validateRequired(){}labelTemplate(){return this.label?c`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon?null:c`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    `:""}render(){return c`
      ${this.labelTemplate()}
      <slot></slot>
    `}}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{I:La}=ao,ds=()=>document.createComment(""),Ee=(o,e,t)=>{var a;const i=o._$AA.parentNode,s=e===void 0?o._$AB:e._$AA;if(t===void 0){const r=i.insertBefore(ds(),s),n=i.insertBefore(ds(),s);t=new La(r,n,o,o.options)}else{const r=t._$AB.nextSibling,n=t._$AM,l=n!==o;if(l){let u;(a=t._$AQ)==null||a.call(t,o),t._$AM=o,t._$AP!==void 0&&(u=o._$AU)!==n._$AU&&t._$AP(u)}if(r!==s||l){let u=t._$AA;for(;u!==r;){const b=u.nextSibling;i.insertBefore(u,s),u=b}}}return t},re=(o,e,t=o)=>(o._$AI(e,t),o),Pa={},Ia=(o,e=Pa)=>o._$AH=e,Ma=o=>o._$AH,Mt=o=>{var i;(i=o._$AP)==null||i.call(o,!1,!0);let e=o._$AA;const t=o._$AB.nextSibling;for(;e!==t;){const s=e.nextSibling;e.remove(),e=s}};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const cs=(o,e,t)=>{const i=new Map;for(let s=e;s<=t;s++)i.set(o[s],s);return i},Ae=ut(class extends ht{constructor(o){if(super(o),o.type!==ct.CHILD)throw Error("repeat() can only be used in text expressions")}dt(o,e,t){let i;t===void 0?t=e:e!==void 0&&(i=e);const s=[],a=[];let r=0;for(const n of o)s[r]=i?i(n,r):r,a[r]=t(n,r),r++;return{values:a,keys:s}}render(o,e,t){return this.dt(o,e,t).values}update(o,[e,t,i]){const s=Ma(o),{values:a,keys:r}=this.dt(e,t,i);if(!Array.isArray(s))return this.ut=r,a;const n=this.ut??(this.ut=[]),l=[];let u,b,g=0,v=s.length-1,y=0,_=a.length-1;for(;g<=v&&y<=_;)if(s[g]===null)g++;else if(s[v]===null)v--;else if(n[g]===r[y])l[y]=re(s[g],a[y]),g++,y++;else if(n[v]===r[_])l[_]=re(s[v],a[_]),v--,_--;else if(n[g]===r[_])l[_]=re(s[g],a[_]),Ee(o,l[_+1],s[g]),g++,_--;else if(n[v]===r[y])l[y]=re(s[v],a[y]),Ee(o,s[g],s[v]),v--,y++;else if(u===void 0&&(u=cs(r,y,_),b=cs(n,g,v)),u.has(n[g]))if(u.has(n[v])){const L=b.get(r[y]),N=L!==void 0?s[L]:null;if(N===null){const M=Ee(o,s[g]);re(M,a[y]),l[y]=M}else l[y]=re(N,a[y]),Ee(o,s[g],N),s[L]=null;y++}else Mt(s[v]),v--;else Mt(s[g]),g++;for(;y<=_;){const L=Ee(o,l[_+1]);re(L,a[y]),l[y++]=L}for(;g<=v;){const L=s[g++];L!==null&&Mt(L)}return this.ut=r,Ia(o,l),V}}),ja=o=>class extends o{constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},query:{type:String,state:!0},options:{type:Array},filteredOptions:{type:Array,state:!0},open:{type:Boolean,state:!0},canUpdate:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean}}}willUpdate(e){if(super.willUpdate(e),e&&!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");!e.style.getPropertyValue("--container-width")&&e.clientWidth>0&&e.style.setProperty("--container-width",`${e.clientWidth}px`)}_select(){console.error("Must implement `_select(value)` function")}static _focusInput(e){e.target===e.currentTarget&&e.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!0,this.activeIndex=-1)}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1,this.canUpdate=!0)}_inputKeyDown(e){}_inputKeyUp(e){switch(e.keyCode||e.which){case 8:e.target.value===""?this.value=this.value.slice(0,-1):this.open=!0;break;case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0,this.query=e.target.value;break}}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,s=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;s>r?e.scrollTo({top:s-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select(this.query):this._select(this.filteredOptions[this.activeIndex].id),this._clearSearch())}_clickOption(e){e.target&&e.target.value&&(this._select(e.target.value),this._clearSearch())}_clickAddNew(e){var t;e.target&&(this._select((t=e.target.dataset)==null?void 0:t.label),this._clearSearch())}_clearSearch(){const e=this.shadowRoot.querySelector("input");e&&(e.value="")}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1):this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_renderOption(e,t){return c`
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
    `}_baseRenderOptions(){return this.filteredOptions.length?Ae(this.filteredOptions,e=>e.id,(e,t)=>this._renderOption(e,t)):this.loading?c`<li><div>${A("Loading options...")}</div></li>`:c`<li><div>${A("No Data Available")}</div></li>`}_renderOptions(){let e=this._baseRenderOptions();return this.allowAdd&&this.query&&(Array.isArray(e)||(e=[e]),e.push(c`<li tabindex="-1">
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
      </li>`)),e}};class za extends F{static get styles(){return S`
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
    `}}window.customElements.define("dt-spinner",za);class Da extends F{static get styles(){return S`
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
    `}}window.customElements.define("dt-checkmark",Da);class jt extends ja(z){static get styles(){return[...super.styles,S`
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

        .field-container.invalid {
          border: 1px solid var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},containerHeight:{type:Number,state:!0}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length)if(typeof this.value[0]=="string")this.value=[...this.value.filter(i=>i!==`-${e}`),e];else{let i=!1;const s=this.value.map(a=>{const r={...a};return a.id===e.id&&a.delete&&(delete r.delete,i=!0),r});i||s.push(e),this.value=s}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._setFormValue(this.value)}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(i=>i===e.target.dataset.value?`-${i}`:i),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){return this.filteredOptions=(this.options||[]).filter(e=>!(this.value||[]).includes(e.id)&&(!this.query||e.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),i=e.has("query"),s=e.has("options");(t||i||s)&&this._filterOptions()}}_renderSelectedOptions(){return this.options&&this.options.filter(e=>this.value&&this.value.indexOf(e.id)>-1).map(e=>c`
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
          `)}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"field-container":!0,invalid:this.touched&&this.invalid}}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return c`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}">
        <div
          class="${C(this.classes)}"
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
        <ul class="option-list" style=${H(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>`:null}
        </div>
`}}window.customElements.define("dt-multi-select",jt);class Oe extends jt{static get properties(){return{...super.properties,allowAdd:{type:Boolean}}}static get styles(){return[...super.styles,S`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(--primary-color, #3f729b);
        }
        .invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
        .input-group {
          display: flex;
        }

        .field-container {
          flex: 1;
        }

        .input-addon.btn-add {
          background-color: var(--dt-multi-text-background-color, #fefefe);
          border: 1px solid var(--dt-multi-text-border-color, #fefefe);
          width: 37.5px;
          &:disabled {
            color: var(--dt-text-placeholder-color, #999);
          }
          &:hover:not([disabled]) {
            background-color: var(--success-color, #cc4b37);
            color: var(--dt-multi-text-button-hover-color, #ffffff);
          }
        }
        .input-group.allowAdd .icon-overlay {
          inset-inline-end: 3rem;
        }
      `]}_addRecord(){const e=new CustomEvent("dt:add-new",{detail:{field:this.name,value:this.query}});this.dispatchEvent(e)}willUpdate(e){super.willUpdate(e),e&&e.has("open")&&this.open&&(!this.filteredOptions||!this.filteredOptions.length)&&this._filterOptions()}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.startsWith("-"));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1;let r=a;r.length&&typeof r[0]=="string"&&(r=r.map(n=>({id:n}))),i.allOptions=r,i.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_renderOption(e,t){return c`
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
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||typeof t=="string"&&t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){const e=this.options||this.allOptions;return(this.value||[]).filter(t=>!t.startsWith("-")).map(t=>{var a;let i=t;if(e){const r=e.filter(n=>n===t||n.id===t);r.length&&(i=r[0].label||r[0].id||t)}let s;if(!s&&((a=window==null?void 0:window.SHAREDFUNCTIONS)!=null&&a.createCustomFilter)){const r=window.SHAREDFUNCTIONS.createCustomFilter(this.name,[t]),n=this.label||this.name,l=[{id:`${this.name}_${t}`,name:`${n}: ${t}`}];s=window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType,r,l)}return c`
          <div class="selected-option">
            <a
              href="${s||"#"}"
              ?disabled="${this.disabled}"
              alt="${t}"
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
        `})}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return c`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}">
        <div
          class="${C(this.classes)}"
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
        ${this.allowAdd?c`<button
          class="input-addon btn-add"
          @click=${this._addRecord}
          >
            <dt-icon icon="mdi:tag-plus-outline"></dt-icon>
          </button>`:null}
        <ul class="option-list" style=${H(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>`:null}
        </div>
`}}window.customElements.define("dt-tags",Oe);class us extends Oe{static get styles(){return[...super.styles,S`
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
        .invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((s,a)=>!s&&a.id==t?a:s,null);i&&this._select(i),this._clearSearch()}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){let t=e.target.dataset.value;const i=Number.parseInt(t);Number.isNaN(i)||(t=i);const s=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(a=>{const r={...a};return a.id===t&&(r.delete=!0),r}),s.detail.newValue=this.value,this.dispatchEvent(s),this.open&&this.shadowRoot.querySelector("input").focus(),this._validateRequired()}}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.delete))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>c`
          <div class="selected-option">
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
        `)}_renderOption(e,t){const i=c`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`,s=e.status||{label:"",color:""};return c`
      <li tabindex="-1" style="border-inline-start-color:${s.color}">
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
          ${s.label?c`<span class="status">${s.label}</span>`:null}
          ${e.user?i:null}
        </button>
      </li>
    `}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return c`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}">
        <div
          class="${C(this.classes)}"
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
        ${this.allowAdd?c`<button
          class="input-addon btn-add"
          @click=${this._addRecord}
          >
            <dt-icon icon="mdi:account-plus-outline"></dt-icon>
          </button>`:null}
        <ul class="option-list" style=${H(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>`:null}
        </div>
`}}window.customElements.define("dt-connection",us);class hs extends Oe{static get styles(){return[...super.styles,S`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((s,a)=>!s&&a.id==t?a:s,null);i&&this._select(i),this._clearSearch()}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,remove:!0}});this.value=(this.value||[]).map(i=>{const s={...i};return i.id===parseInt(e.target.dataset.value,10)&&(s.delete=!0),s}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>c`
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
        `)}_renderOption(e,t){return c`
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
    `}}window.customElements.define("dt-users-connection",hs);class ps extends R{static get styles(){return S`
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
    `}static get properties(){return{value:{type:String},success:{type:Boolean},error:{type:Boolean}}}get inputStyles(){return this.success?{"--dt-text-border-color":"var(--copy-text-success-color, var(--success-color))","--dt-form-text-color":"var( --copy-text-success-color, var(--success-color))",color:"var( --copy-text-success-color, var(--success-color))"}:this.error?{"---dt-text-border-color":"var(--copy-text-alert-color, var(--alert-color))","--dt-form-text-color":"var(--copy-text-alert-color, var(--alert-color))"}:{}}get icon(){return this.success?"ic:round-check":"ic:round-content-copy"}async copy(){try{this.success=!1,this.error=!1,await navigator.clipboard.writeText(this.value),this.success=!0,this.error=!1}catch(e){console.log(e),this.success=!1,this.error=!0}}render(){return c`
      <div class="copy-text" style=${H(this.inputStyles)}>
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
    `}}window.customElements.define("dt-copy-text",ps);class fs extends z{static get styles(){return[...super.styles,S`
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
          inset-inline-end: 4rem;
        }
        input.invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},timestamp:{converter:e=>{let t=Number(e);if(t<1e12&&(t*=1e3),t)return t},reflect:!0}}}updateTimestamp(e){const t=new Date(e).getTime(),i=t/1e3,s=new CustomEvent("change",{detail:{field:this.name,oldValue:this.timestamp,newValue:i}});this.timestamp=t,this.value=e,this._setFormValue(e),this.dispatchEvent(s)}_change(e){this.updateTimestamp(e.target.value)}clearInput(){this.updateTimestamp("")}showDatePicker(){this.shadowRoot.querySelector("input").showPicker()}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"input-group-field":!0,dt_date_picker:!0,invalid:this.touched&&this.invalid}}render(){return this.timestamp?this.value=new Date(this.timestamp).toISOString().substring(0,10):this.value&&(this.timestamp=new Date(this.value).getTime()),c`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          class="${C(this.classes)}"
          type="date"
          autocomplete="off"
          .placeholder="${new Date().toISOString().substring(0,10)}"
          .value="${this.value}"
          .timestamp="${this.date}"
          ?disabled=${this.disabled}
          ?required=${this.required}
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

        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-date",fs);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*We(o,e){if(o!==void 0){let t=0;for(const i of o)yield e(i,t++)}}class bs extends Oe{static get properties(){return{...super.properties,filters:{type:Array},mapboxKey:{type:String},dtMapbox:{type:Object}}}static get styles(){return[...super.styles,S`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=e.target.value,i=this.filteredOptions.reduce((s,a)=>!s&&a.id===t?a:s,null);this._select(i)}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(i=>{const s={...i};return i.id===e.target.dataset.value&&(s.delete=!0),s}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}updated(){super.updated();const e=this.shadowRoot.querySelector(".input-group"),t=e.style.getPropertyValue("--select-width"),i=this.shadowRoot.querySelector("select");!t&&(i==null?void 0:i.clientWidth)>0&&e.style.setProperty("--select-width",`${i.clientWidth}px`)}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else{this.loading=!0,this.filteredOptions=[];const i=this,s=this.shadowRoot.querySelector("select"),a=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,query:this.query,filter:s==null?void 0:s.value,onSuccess:r=>{i.loading=!1,i.filteredOptions=r.filter(n=>!e.includes(n.id))},onError:r=>{console.warn(r),i.loading=!1}}});this.dispatchEvent(a)}return this.filteredOptions}_renderOption(e,t){return c`
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
    `}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>c`
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
        `)}render(){const e={display:this.open?"block":"none",top:`${this.containerHeight}px`};return this.mapboxKey?c` ${this.labelTemplate()}
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
          </div>`:c`
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

              ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
              ${this.saved?c`<dt-checkmark
                    class="icon-overlay success"
                  ></dt-checkmark>`:null}
            </div>
            <select class="filter-list" ?disabled="${this.disabled}">
              ${We(this.filters,t=>c`<option value="${t.id}">${t.label}</option>`)}
            </select>
            <ul class="option-list" style=${H(e)}>
              ${this._renderOptions()}
            </ul>
          </div>
        `}}window.customElements.define("dt-location",bs);class Ra{constructor(e){this.token=e}async searchPlaces(e,t="en"){const i=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:t}),s={method:"GET",headers:{"Content-Type":"application/json"}},a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)}.json?${i}`,n=await(await fetch(a,s)).json();return n==null?void 0:n.features}async reverseGeocode(e,t,i="en"){const s=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:i}),a={method:"GET",headers:{"Content-Type":"application/json"}},r=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)},${encodeURI(t)}.json?${s}`,l=await(await fetch(r,a)).json();return l==null?void 0:l.features}}class Na{constructor(e,t,i){var s,a,r;if(this.token=e,this.window=t,!((r=(a=(s=t.google)==null?void 0:s.maps)==null?void 0:a.places)!=null&&r.AutocompleteService)){let n=i.createElement("script");n.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${e}`,i.body.appendChild(n)}}async getPlacePredictions(e,t="en"){if(this.window.google){const i=new this.window.google.maps.places.AutocompleteService,{predictions:s}=await i.getPlacePredictions({input:e,language:t});return s}return null}async getPlaceDetails(e,t="en"){const s=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,address:e,language:t})}`,r=await(await fetch(s,{method:"GET"})).json();let n=[];switch(r.status){case"OK":n=r.results;break}return n&&n.length?n[0]:null}async reverseGeocode(e,t,i="en"){const a=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${t},${e}`,language:i,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,n=await(await fetch(a,{method:"GET"})).json();return n==null?void 0:n.results}}class gs extends R{static get properties(){return{...super.properties,title:{type:String},isOpen:{type:Boolean},canEdit:{type:Boolean,state:!0},metadata:{type:Object},center:{type:Array},mapboxToken:{type:String,attribute:"mapbox-token"}}}static get styles(){return[S`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `]}constructor(){super(),this.addEventListener("open",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")),this.isOpen=!0}),this.addEventListener("close",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")),this.isOpen=!1})}connectedCallback(){if(super.connectedCallback(),this.canEdit=!this.metadata,window.mapboxgl)this.initMap();else{let e=document.createElement("script");e.src="https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js",e.onload=this.initMap.bind(this),document.body.appendChild(e),console.log("injected script")}}initMap(){if(!this.isOpen||!window.mapboxgl||!this.mapboxToken)return;const e=this.shadowRoot.querySelector("#map");if(e&&!this.map){this.map=new window.mapboxgl.Map({accessToken:this.mapboxToken,container:e,style:"mapbox://styles/mapbox/streets-v12",minZoom:1}),this.map.on("load",()=>this.map.resize()),this.center&&this.center.length&&(this.map.setCenter(this.center),this.map.setZoom(15));const t=new mapboxgl.NavigationControl;this.map.addControl(t,"bottom-right"),this.addPinFromMetadata(),this.map.on("click",i=>{this.canEdit&&(this.marker?this.marker.setLngLat(i.lngLat):this.marker=new mapboxgl.Marker().setLngLat(i.lngLat).addTo(this.map))})}}addPinFromMetadata(){if(this.metadata){const{lng:e,lat:t,level:i}=this.metadata;let s=15;i==="admin0"?s=3:i==="admin1"?s=6:i==="admin2"&&(s=10),this.map&&(this.map.setCenter([e,t]),this.map.setZoom(s),this.marker=new mapboxgl.Marker().setLngLat([e,t]).addTo(this.map))}}updated(e){window.mapboxgl&&(e.has("metadata")&&this.metadata&&this.metadata.lat&&this.addPinFromMetadata(),e.has("isOpen")&&this.isOpen&&this.initMap())}onClose(e){var t;((t=e==null?void 0:e.detail)==null?void 0:t.action)==="button"&&this.marker&&this.dispatchEvent(new CustomEvent("submit",{detail:{location:this.marker.getLngLat()}}))}render(){var e;return c`      
      <dt-modal
        .title=${(e=this.metadata)==null?void 0:e.label}
        ?isopen=${this.isOpen}
        hideButton
        @close=${this.onClose}
      >
        <div slot="content">
          <div class="map" id="map"></div>
        </div>
       
        ${this.canEdit?c`<div slot="close-button">${A("Save")}</div>`:null}
      </dt-modal>
      
      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `}}window.customElements.define("dt-map-modal",gs);class qa extends F{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[S`
        :host {
          --dt-location-map-border-color: var(--dt-form-border-color, #fefefe);
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
          --dt-location-map-border-radius: var(--dt-form-border-radius, 0);
          display: flex;
          margin-bottom: 0.5rem;
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
          flex-shrink: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          aspect-ratio: 1/1;
          padding: 0.6em;
          border-collapse: collapse;
          color: var(--dt-location-map-button-color, #cc4b37);
          background-color: var(--dt-location-map-background-color, buttonface);
          border: var(--dt-form-border-width, 1px) solid var(--dt-location-map-border-color);
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
      `]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",async()=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()}),this.mapboxToken&&(this.mapboxService=new Ra(this.mapboxToken)),this.googleToken&&(this.googleGeocodeService=new Na(this.googleToken,window,document))}disconnectedCallback(){super.disconnectedCallback(),this.removeEventListener("autofocus",this.handleAutofocus)}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,s=t.offsetTop+t.clientHeight,a=e.scrollTop,r=e.scrollTop+e.clientHeight;s>r?e.scrollTo({top:s-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_clickOption(e){const t=e.currentTarget??e.target;t&&t.value&&this._select(JSON.parse(t.value))}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query}))}async _select(e){if(e.place_id&&this.googleGeocodeService){this.loading=!0;const s=await this.googleGeocodeService.getPlaceDetails(e.label,this.locale);this.loading=!1,s&&(e.lat=s.geometry.location.lat,e.lng=s.geometry.location.lng,e.level=s.types&&s.types.length?s.types[0]:null)}const t={detail:{metadata:e},bubbles:!1};this.dispatchEvent(new CustomEvent("select",t)),this.metadata=e;const i=this.shadowRoot.querySelector("input");i&&(i.value=e==null?void 0:e.label),this.open=!1,this.activeIndex=-1}get _focusTarget(){let e=this._field;return this.metadata&&(e=this.shadowRoot.querySelector("button")||e),e}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1)}_inputKeyDown(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){const t=e.keyCode||e.which,i=[9,13];e.target.value&&!i.includes(t)&&(this.open=!0),this.query=e.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query){if(this.googleToken&&this.googleGeocodeService){this.loading=!0;try{const e=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(e||[]).map(t=>({label:t.description,place_id:t.place_id,source:"user",raw:t})),this.loading=!1}catch(e){console.error(e),this.error=!0,this.loading=!1;return}}else if(this.mapboxToken&&this.mapboxService){this.loading=!0;const e=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=e.map(t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"})),this.loading=!1}}return this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e&&(e.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout(()=>this._filterOptions(),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length)){const i=this.shadowRoot.querySelector(".input-group");i&&(this.containerHeight=i.offsetHeight)}}_change(){}_delete(){const e={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",e))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(e){var t,i;if((i=(t=e==null?void 0:e.detail)==null?void 0:t.location)!=null&&i.lat){const{location:s}=e==null?void 0:e.detail,{lat:a,lng:r}=s;if(this.googleGeocodeService){const n=await this.googleGeocodeService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.geometry.location.lng,lat:l.geometry.location.lat,level:l.types&&l.types.length?l.types[0]:null,label:l.formatted_address,source:"user"})}}else if(this.mapboxService){const n=await this.mapboxService.reverseGeocode(r,a,this.locale);if(n&&n.length){const l=n[0];this._select({lng:l.center[0],lat:l.center[1],level:l.place_type[0],label:l.place_name,source:"user"})}}}}_renderOption(e,t,i){return c`
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
    `}_renderOptions(){let e=[];return this.filteredOptions.length?e.push(...this.filteredOptions.map((t,i)=>this._renderOption(t,i))):this.loading?e.push(c`<li><div>${A("Loading...")}</div></li>`):e.push(c`<li><div>${A("No Data Available")}</div></li>`),e.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,c`<strong>${A("Use")}: "${this.query}"</strong>`)),e}render(){var s,a,r,n;const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},t=!!((s=this.metadata)!=null&&s.label),i=((a=this.metadata)==null?void 0:a.lat)&&((r=this.metadata)==null?void 0:r.lng);return c`
      <div class="input-group">
        <div class="field-container">
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

          ${t&&i?c`
          <button
            class="input-addon btn-map"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <slot name="map-icon"><dt-icon icon="mdi:map"></dt-icon></slot>
          </button>
          `:null}
          ${t?c`
          <button
            class="input-addon btn-delete"
            @click=${this._delete}
            ?disabled=${this.disabled}
          >
            <slot name="delete-icon"><dt-icon icon="mdi:trash-can-outline"></dt-icon></slot>
          </button>
          `:c`
          <button
            class="input-addon btn-pin"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <slot name="pin-icon"><dt-icon icon="mdi:map-marker-radius"></dt-icon></slot>
          </button>
          `}
        </div>
        <ul class="option-list" style=${H(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched&&this.invalid||this.error?c`<dt-exclamation-circle class="icon-overlay alert"></dt-exclamation-circle>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>

      <dt-map-modal
        .metadata=${this.metadata}
        mapbox-token="${this.mapboxToken}"
        @submit=${this._onMapModalSubmit}
      ></dt-map-modal>

`}}window.customElements.define("dt-location-map-item",qa);class ms extends z{static get properties(){return{...super.properties,placeholder:{type:String},value:{type:Array,reflect:!0},locations:{type:Array,state:!0},open:{type:Boolean,state:!0},limit:{type:Number,attribute:"limit"},onchange:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"}}}static get styles(){return[...super.styles,S`
        :host {
          font-family: Helvetica, Arial, sans-serif;
        }
        .input-group {
          display: flex;
        }

        .field-container {
          position: relative;
        }
      `]}constructor(){super(),this.limit=0,this.value=[],this.locations=[{id:Date.now()}]}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e))}willUpdate(...e){super.willUpdate(...e),this.value&&this.value.filter(t=>!t.id)&&(this.value=[...this.value.map(t=>({...t,id:t.grid_meta_id}))]),this.updateLocationList()}firstUpdated(...e){super.firstUpdated(...e),this.internals.setFormValue(JSON.stringify(this.value))}updated(e){var t,i;if(e.has("value")){const s=e.get("value");s&&(s==null?void 0:s.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewLocation()}if(e.has("locations")){const s=e.get("locations");s&&(s==null?void 0:s.length)!==((i=this.locations)==null?void 0:i.length)&&this.focusNewLocation()}}focusNewLocation(){const e=this.shadowRoot.querySelectorAll("dt-location-map-item");e&&e.length&&e[e.length-1].dispatchEvent(new Event("autofocus"))}updateLocationList(){if(!this.disabled&&(this.open||!this.value||!this.value.length)){this.open=!0;const e=(this.value||[]).filter(i=>i.label),t=this.limit===0||e.length<this.limit;this.locations=[...e,...t?[{id:Date.now()}]:[]]}else this.locations=[...(this.value||[]).filter(e=>e.label)]}selectLocation(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i={...e.detail.metadata,id:Date.now()};this.value=[...(this.value||[]).filter(s=>s.label),i],this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}deleteItem(e){var a;const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i=(a=e.detail)==null?void 0:a.metadata,s=i==null?void 0:i.grid_meta_id;s?this.value=(this.value||[]).filter(r=>r.grid_meta_id!==s):this.value=(this.value||[]).filter(r=>r.lat!==i.lat&&r.lng!==i.lng),this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}addNew(){const e=(this.value||[]).filter(t=>t.label);(this.limit===0||e.length<this.limit)&&(this.open=!0,this.updateLocationList())}renderItem(e){return c`
      <dt-location-map-item
        placeholder="${this.placeholder}"
        .metadata=${e}
        mapbox-token="${this.mapboxToken}"
        google-token="${this.googleToken}"
        @delete=${this.deleteItem}
        @select=${this.selectLocation}
        ?disabled=${this.disabled}
      ></dt-location-map-item>
    `}render(){return[...this.value||[]],c`
      ${this.labelTemplate()}

      ${Ae(this.locations||[],e=>e.id,(e,t)=>this.renderItem(e,t))}
      ${!this.open&&(this.limit==0||this.locations.length<this.limit)?c`<button @click="${this.addNew}">Add New</button>`:null}
    `}}window.customElements.define("dt-location-map",ms);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ue=o=>o??E;class vs extends z{static get styles(){return[...super.styles,S`
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
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},oldValue:{type:String},min:{type:Number},max:{type:Number},loading:{type:Boolean},saved:{type:Boolean},onchange:{type:String}}}connectedCallback(){super.connectedCallback(),this.oldValue=this.value}_checkValue(e){return!(e<this.min||e>this.max)}async onChange(e){if(this._checkValue(e.target.value)){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value},bubbles:!0,composed:!0});this.value=e.target.value,this._field.setCustomValidity(""),this.dispatchEvent(t),this.api=new Re(this.nonce,`${this.apiRoot}`)}else e.currentTarget.value=""}handleError(e="An error occurred."){let t=e;t instanceof Error?(console.error(t),t=t.message):console.error(t),this.error=t,this._field.setCustomValidity(t),this.invalid=!0,this.value=this.oldValue}render(){return c`
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
    `}}window.customElements.define("dt-number",vs);class ys extends z{static get styles(){return[...super.styles,S`
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
        select.invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},value:{type:String,reflect:!0},color:{type:String,state:!0},onchange:{type:String}}}updateColor(){if(this.value&&this.options){const e=this.options.filter(t=>t.id===this.value);e&&e.length&&(this.color=e[0].color)}}isColorSelect(){return(this.options||[]).reduce((e,t)=>e||t.color,!1)}willUpdate(e){super.willUpdate(e),e.has("value")&&this.updateColor()}_change(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{invalid:this.touched&&this.invalid,"color-select":this.isColorSelect()}}render(){return c`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}" dir="${this.RTL?"rtl":"ltr"}">
        <select
          name="${this.name}"
          aria-label="${this.name}"
          @change="${this._change}"
          class="${C(this.classes)}"
          style="background-color: ${this.color};"
          ?disabled="${this.disabled}"
          ?required=${this.required}
        >
          <option disabled selected hidden value="">${this.placeholder}</option>

          ${this.options&&this.options.map(e=>c`
              <option value="${e.id}" ?selected="${e.id===this.value}">
                ${e.label}
              </option>
            `)}
        </select>
        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-single-select",ys);class Fa extends F{static get styles(){return S`
      svg use {
        fill: currentcolor;
      }
    `}render(){return c`
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
    `}}window.customElements.define("dt-exclamation-circle",Fa);class zt extends z{static get styles(){return[...super.styles,S`
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
      `]}static get properties(){return{...super.properties,id:{type:String},type:{type:String},placeholder:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const i=this.internals.form.querySelector("button[type=submit]");i&&i.click()}}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return c`
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
          class="${C(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
        />

        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-text",zt);class ws extends z{static get styles(){return[...super.styles,S`
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
        textarea.invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}_validateRequired(){const{value:e}=this;!e&&this.required?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return c`
      ${this.labelTemplate()}

      <div class="input-group">
        <textarea
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${C(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
        ></textarea>

        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-textarea",ws);class _s extends z{static get styles(){return[...super.styles,S`
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
      `]}static get properties(){return{...super.properties,id:{type:String},checked:{type:Boolean,reflect:!0},onchange:{type:String},hideIcons:{type:Boolean,default:!0}}}constructor(){super(),this.hideIcons=!1}onChange(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.checked,newValue:e.target.checked}});this.checked=e.target.checked,this._setFormValue(this.checked),this.dispatchEvent(t)}render(){const e=c`<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--checkmark"><path d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z" fill="currentcolor" stroke="currentcolor" /></svg>`,t=c`<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--cross"><path d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z" fill="currentcolor" /></svg>`;return c`
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
          ${this.hideIcons?c``:c` ${e} ${t} `}
        </span>
      </label>
    `}}window.customElements.define("dt-toggle",_s);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function Ua(o,e,t){return o?e(o):t==null?void 0:t(o)}class $s extends zt{static get styles(){return[...super.styles,S`
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
          inset-inline-end: 4rem;
          height: 2.5rem;
        }
        .field-container:has(.btn-remove) ~ .icon-overlay {
          inset-inline-end: 5.5rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0}}}updated(e){var t;if(e.has("value")){const i=e.get("value");i&&(i==null?void 0:i.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewItem()}}focusNewItem(){const e=this.shadowRoot.querySelectorAll("input");e&&e.length&&e[e.length-1].focus()}_addItem(){const e={verified:!1,value:"",tempKey:Date.now().toString()};this.value=[...this.value,e]}_removeItem(e){const t=e.currentTarget.dataset.key;if(t){const i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}}),s=this.value.filter(a=>a.tempKey!==t).map(a=>{const r={...a};return a.key===t&&(r.delete=!0),r});s.filter(a=>!a.delete).length||s.push({value:"",tempKey:Date.now().toString()}),this.value=s,i.detail.newValue=this.value,this.dispatchEvent(i),this._setFormValue(this.value)}}_change(e){var i,s;const t=(s=(i=e==null?void 0:e.currentTarget)==null?void 0:i.dataset)==null?void 0:s.key;if(t){const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=this.value.map(r=>{var n;return{...r,value:r.key===t||r.tempKey===t?(n=e.target)==null?void 0:n.value:r.value}}),a.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(a)}}_inputFieldTemplate(e,t){return c`
      <div class="field-container">
        <input
          data-key="${e.key??e.tempKey}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${C(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
        />

        ${Ua(t>1||e.key||e.value,()=>c`
              <button
                  class="input-addon btn-remove"
                  @click=${this._removeItem}
                  data-key="${e.key??e.tempKey}"
                  ?disabled=${this.disabled}
              >
                <dt-icon icon="mdi:close"></dt-icon>
              </button>
            `,()=>c``)}
        <button
          class="input-addon btn-add"
          @click=${this._addItem}
          ?disabled=${this.disabled}
        >
          <dt-icon icon="mdi:plus-thick"></dt-icon>
        </button>
      </div>
    `}_renderInputFields(){return(!this.value||!this.value.length)&&(this.value=[{verified:!1,value:"",tempKey:Date.now().toString()}]),c`
      ${Ae((this.value??[]).filter(e=>!e.delete),e=>e.id,e=>this._inputFieldTemplate(e,this.value.length))}
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t.value))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return c`
      ${this.labelTemplate()}
      <div class="input-group">
        ${this._renderInputFields()}

        ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
        ${this.error?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>`:null}
        ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
      </div>
    `}}window.customElements.define("dt-multi-text",$s);class xs extends z{static get styles(){return[...super.styles,S`
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
      `]}constructor(){super(),this.options=[]}static get properties(){return{value:{type:Array,reflect:!0},context:{type:String},options:{type:Array},outline:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector(".input-group")}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length){const i=this.value.includes(e);this.value=[...this.value.filter(s=>s!==e&&s!==`-${e}`),i?`-${e}`:e]}else this.value=[e];t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}_clickOption(e){var t;(t=e==null?void 0:e.currentTarget)!=null&&t.value&&this._select(e.currentTarget.value)}_inputKeyUp(e){switch(e.keyCode||e.which){case 13:this._clickOption(e);break}}_renderButton(e){const i=(this.value??[]).includes(e.id)?"success":this.touched&&this.invalid?"alert":"inactive",s=this.outline??(this.touched&&this.invalid);return c`
    <dt-button
      custom
      type="success"
      context=${i}
      .value=${e.id}
      @click="${this._clickOption}"
      ?disabled="${this.disabled}"
      ?outline="${s}"
      role="button"
      value="${e.id}"
    >
      ${e.icon?c`<span class="icon"><img src="${e.icon}" alt="${this.iconAltText}" /></span>`:null}
      ${e.label}
    </dt-button>
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"button-group":!0,invalid:this.touched&&this.invalid}}render(){return c`
       ${this.labelTemplate()}
       <div class="input-group ${this.disabled?"disabled":""}">
         <div class="${C(this.classes)}">
           ${Ae(this.options??[],e=>e.id,e=>this._renderButton(e))}
         </div>
         ${this.touched&&this.invalid?c`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.internals.validationMessage}"
              size="2rem"
            ></dt-icon>`:null}
         ${this.loading?c`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
         ${this.saved?c`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
         ${this.error?c`<dt-icon
                  icon="mdi:alert-circle"
                  class="icon-overlay alert"
                  tooltip="${this.error}"
                  size="2rem"
                  ></dt-icon>`:null}
       </div>
    `}}window.customElements.define("dt-multi-select-button-group",xs);class ks extends R{static get styles(){return S`
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
    `}static get properties(){return{context:{type:String},dismissable:{type:Boolean},timeout:{type:Number},hide:{type:Boolean},outline:{type:Boolean}}}get classes(){const e={"dt-alert":!0,"dt-alert--outline":this.outline},t=`dt-alert--${this.context}`;return e[t]=!0,e}constructor(){super(),this.context="default"}connectedCallback(){super.connectedCallback(),this.timeout&&setTimeout(()=>{this._dismiss()},this.timeout)}_dismiss(){this.hide=!0}render(){if(this.hide)return c``;const e=c`
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
    `;return c`
      <div role="alert" class=${C(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable?c`
              <button @click="${this._dismiss}" class="toggle">${e}</button>
            `:null}
      </div>
    `}}window.customElements.define("dt-alert",ks);class Ss extends R{static get styles(){return S`
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
    `}static get properties(){return{postType:{type:String},postTypeLabel:{type:String},posttypesettings:{type:Object,attribute:!0},posts:{type:Array},total:{type:Number},columns:{type:Array},sortedBy:{type:String},loading:{type:Boolean,default:!0},offset:{type:Number},showArchived:{type:Boolean,default:!1},showFieldsSelector:{type:Boolean,default:!1},showBulkEditSelector:{type:Boolean,default:!1},nonce:{type:String},payload:{type:Object},favorite:{type:Boolean},initialLoadPost:{type:Boolean,default:!1},loadMore:{type:Boolean,default:!1},headerClick:{type:Boolean,default:!1}}}constructor(){super(),this.sortedBy="name",this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.sortedColumns},this.initalLoadPost=!1,this.initalLoadPost||(this.posts=[],this.limit=100)}firstUpdated(){this.postTypeSettings=window.post_type_fields,this.sortedColumns=this.columns.includes("favorite")?["favorite",...this.columns.filter(e=>e!=="favorite")]:this.columns,this.style.setProperty("--number-of-columns",this.columns.length-1)}async _getPosts(e){const t=await new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:e,onSuccess:i=>{this.initalLoadPost&&this.loadMore&&(this.posts=[...this.posts,...i],this.postsLength=this.posts.length,this.total=i.length,this.loadMore=!1),this.initalLoadPost||(this.posts=[...i],this.offset=this.posts.length,this.initalLoadPost=!0,this.total=i.length),this.headerClick&&(this.posts=i,this.offset=this.posts.length,this.headerClick=!1),this.total=i.length},onError:i=>{console.warn(i)}}});this.dispatchEvent(t)}_headerClick(e){const t=e.target.dataset.id;this.sortedBy===t?t.startsWith("-")?this.sortedBy=t.replace("-",""):this.sortedBy=`-${t}`:this.sortedBy=t,this.payload={sort:this.sortedBy,overall_status:["-closed"],limit:this.limit,fields_to_return:this.columns},this.headerClick=!0,this._getPosts(this.payload)}static _rowClick(e){window.open(e,"_self")}_bulkEdit(){this.showBulkEditSelector=!this.showBulkEditSelector}_fieldsEdit(){this.showFieldsSelector=!this.showFieldsSelector}_toggleShowArchived(){if(this.showArchived=!this.showArchived,this.headerClick=!0,this.showArchived){const{overall_status:e,offset:t,...i}=this.payload;this.payload=i}else this.payload.overall_status=["-closed"];this._getPosts(this.payload)}_sortArrowsClass(e){return this.sortedBy===e?"sortedBy":""}_sortArrowsToggle(e){return this.sortedBy!==`-${e}`?`-${e}`:e}_headerTemplate(){return this.postTypeSettings?c`
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
            ${We(this.sortedColumns,e=>{const t=e==="favorite";return c`<th
                class="all"
                data-id="${this._sortArrowsToggle(e)}"
                @click=${this._headerClick}
              >
                  <span class="column-name"
                     >${t?null:this.postTypeSettings[e].name}</span
                  >
                  ${t?"":c`<span id="sort-arrows">
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
      `:null}_rowTemplate(){if(this.posts&&Array.isArray(this.posts)){const e=this.posts.map((t,i)=>this.showArchived||!this.showArchived&&t.overall_status!=="closed"?c`
              <tr class="dnd-moved" data-link="${t.permalink}" @click=${()=>this._rowClick(t.permalink)}>
                <td class="bulk_edit_checkbox no-title">
                  <input type="checkbox" name="bulk_edit_id" .value="${t.ID}" />
                </td>
                <td class="no-title line-count">${i+1}.</td>
                ${this._cellTemplate(t)}
              </tr>
            `:null).filter(t=>t!==null);return e.length>0?e:c`<p>No contacts available</p>`}return null}formatDate(e){const t=new Date(e);return new Intl.DateTimeFormat("en-US",{month:"long",day:"numeric",year:"numeric"}).format(t)}_cellTemplate(e){return We(this.sortedColumns,t=>{if(["text","textarea","number"].includes(this.postTypeSettings[t].type))return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t]}
        </td>`;if(this.postTypeSettings[t].type==="date")return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${this.formatDate(e[t].formatted)}
        </td>`;if(this.postTypeSettings[t].type==="user_select"&&e[t]&&e[t].display)return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].display)}
        </td>`;if(this.postTypeSettings[t].type==="key_select"&&e[t]&&(e[t].label||e[t].name))return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t].label||e[t].name}
        </td>`;if(this.postTypeSettings[t].type==="multi_select"||this.postTypeSettings[t].type==="tags"&&e[t]&&e[t].length>0)return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <ul>
            ${We(e[t],i=>c`<li>
                  ${this.postTypeSettings[t].default[i].label}
                </li>`)}
          </ul>
        </td>`;if(this.postTypeSettings[t].type==="location"||this.postTypeSettings[t].type==="location_meta")return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].label)}
        </td>`;if(this.postTypeSettings[t].type==="communication_channel")return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${ue(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="connection")return c` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <!-- TODO: look at this, it doesn't match the current theme. -->
          ${ue(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="boolean"){if(t==="favorite")return c`<td
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
                class="${C({"icon-star":!0,selected:e.favorite})}"
                height="15"
                viewBox="0 0 32 32"
              >
                <path
                  d="M 31.916 12.092 C 31.706 11.417 31.131 10.937 30.451 10.873 L 21.215 9.996 L 17.564 1.077 C 17.295 0.423 16.681 0 16 0 C 15.318 0 14.706 0.423 14.435 1.079 L 10.784 9.996 L 1.546 10.873 C 0.868 10.937 0.295 11.417 0.084 12.092 C -0.126 12.769 0.068 13.51 0.581 13.978 L 7.563 20.367 L 5.503 29.83 C 5.354 30.524 5.613 31.245 6.165 31.662 C 6.462 31.886 6.811 32 7.161 32 C 7.463 32 7.764 31.915 8.032 31.747 L 16 26.778 L 23.963 31.747 C 24.546 32.113 25.281 32.08 25.834 31.662 C 26.386 31.243 26.645 30.524 26.494 29.83 L 24.436 20.367 L 31.417 13.978 C 31.931 13.51 32.127 12.769 31.916 12.092 Z M 31.916 12.092"
                />
              </svg>
            </dt-button>
          </td>`;if(this.postTypeSettings[t]===!0)return c`<td
            dir="auto"
            title="${this.postTypeSettings[t].name}"
          >
            ['&check;']
          </td>`}return c`<td
        dir="auto"
        title="${this.postTypeSettings[t].name}"
      ></td>`})}_fieldListIconTemplate(e){return this.postTypeSettings[e].icon?c`<img
        class="dt-icon"
        src="${this.postTypeSettings[e].icon}"
        alt="${this.postTypeSettings[e].name}"
      />`:null}_fieldsListTemplate(){return Ae(Object.keys(this.postTypeSettings).sort((e,t)=>{const i=this.postTypeSettings[e].name.toUpperCase(),s=this.postTypeSettings[t].name.toUpperCase();return i<s?-1:i>s?1:0}),e=>e,e=>this.postTypeSettings[e].hidden?null:c`<li class="list-field-picker-item">
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
          </li> `)}_fieldsSelectorTemplate(){return this.showFieldsSelector?c`<div
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
      </div>`:null}_updateFields(e){const t=e.target.value,i=this.columns;i.includes(t)?(i.filter(s=>s!==t),i.splice(i.indexOf(t),1)):i.push(t),this.columns=i,this.style.setProperty("--number-of-columns",this.columns.length-1),this.requestUpdate()}_bulkSelectorTemplate(){return this.showBulkEditSelector?c`<div id="bulk_edit_picker" class="list_action_section">
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
      </div>`:null}connectedCallback(){super.connectedCallback(),this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns},this.posts.length===0&&this._getPosts(this.payload).then(e=>{this.posts=e})}_handleLoadMore(){this.limit=500,this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns,offset:this.offset,limit:this.limit},this.loadMore=!0,this._getPosts(this.payload).then(e=>{console.log(e)})}render(){const e={bulk_editing:this.showBulkEditSelector,hidden:!1};this.posts&&(this.total=this.posts.length);const t=c`
      <svg viewBox="0 0 100 100" fill="#000000" style="enable-background:new 0 0 100 100;" xmlns="http://www.w3.org/2000/svg">
        <line style="stroke-linecap: round; paint-order: fill; fill: none; stroke-width: 15px;" x1="7.97" y1="50.199" x2="76.069" y2="50.128" transform="matrix(0.999999, 0.001017, -0.001017, 0.999999, 0.051038, -0.042708)"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="17.751" x2="92.058" y2="17.751"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="82.853" x2="42.343" y2="82.853"/>
        <polygon style="stroke-linecap: round; stroke-miterlimit: 1; stroke-linejoin: round; fill: rgb(255, 255, 255); paint-order: stroke; stroke-width: 9px;" points="22.982 64.982 33.592 53.186 50.916 70.608 82.902 21.308 95 30.85 52.256 95"/>
      </svg>
    `,i=c`<svg height='100px' width='100px'  fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M94.4,63c0-5.7-3.6-10.5-8.6-12.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5     s3.6,10.5,8.6,12.5v17.2c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5C90.9,73.6,94.4,68.7,94.4,63z M81,66.7     c-2,0-3.7-1.7-3.7-3.7c0-2,1.7-3.7,3.7-3.7s3.7,1.7,3.7,3.7C84.7,65.1,83.1,66.7,81,66.7z"></path><path d="M54.8,24.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v17.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v43.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V49.5c5-1.9,8.6-6.8,8.6-12.5S59.8,26.5,54.8,24.5z M50,40.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C53.7,39.1,52,40.7,50,40.7z"></path><path d="M23.8,50.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v17.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5c5-1.9,8.6-6.8,8.6-12.5S28.8,52.5,23.8,50.5z M19,66.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C22.7,65.1,21,66.7,19,66.7z"></path></g></g></g></svg>`;return c`
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
            ${i} ${A("Fields")}
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
        <table class="table-contacts ${C(e)}">
          ${this._headerTemplate()}
          ${this.posts?this._rowTemplate():A("Loading")}
        </table>
          ${this.total>=100?c`<div class="text-center"><dt-button buttonStyle=${JSON.stringify({margin:"0"})} class="loadMoreButton btn btn-primary" @click=${this._handleLoadMore}>Load More</dt-button></div>`:""}
      </div>
    `}}window.customElements.define("dt-list",Ss);class Ts extends R{static get styles(){return S`
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
    `}static get properties(){return{title:{type:String},expands:{type:Boolean},collapsed:{type:Boolean}}}get hasHeading(){return this.title||this.expands}_toggle(){this.collapsed=!this.collapsed}renderHeading(){return this.hasHeading?c`
        <h3 class="section-header">
          ${this.title}
          ${this.expands?c`
                <button
                  @click="${this._toggle}"
                  class="toggle chevron ${this.collapsed?"down":"up"}"
                >
                  &nbsp;
                </button>
              `:null}
        </h3>
    `:E}render(){return c`
      <section>
        ${this.renderHeading()}
        <div part="body" class="section-body ${this.collapsed?"collapsed":null}">
          <slot></slot>
        </div>
      </section>
    `}}window.customElements.define("dt-tile",Ts);class Ke{get api(){return this._api}constructor(e,t,i,s="wp-json"){this.postType=e,this.postId=t,this.nonce=i,this._api=new Re(this.nonce,s),this.apiRoot=this._api.apiRoot,this.autoSaveComponents=["dt-connection","dt-date","dt-location","dt-multi-select","dt-number","dt-single-select","dt-tags","dt-text","dt-textarea","dt-toggle","dt-multi-text","dt-multi-select-button-group","dt-list","dt-button"],this.dynamicLoadComponents=["dt-connection","dt-tags","dt-modal","dt-list","dt-button"]}initialize(){this.postId&&this.enableAutoSave(),this.attachLoadEvents()}async attachLoadEvents(e){const t=document.querySelectorAll(e||this.dynamicLoadComponents.join(","));t&&t.forEach(i=>{i.dataset.eventDtGetData||(i.addEventListener("dt:get-data",this.handleGetDataEvent.bind(this)),i.dataset.eventDtGetData=!0)})}async checkDuplicates(e,t){const i=document.querySelector("dt-modal.duplicate-detected");if(i){const s=i.shadowRoot.querySelector(".duplicates-detected-button");s&&(s.style.display="none");const a=await this._api.checkDuplicateUsers(this.postType,this.postId);t&&a.ids.length>0&&s&&(s.style.display="block")}}enableAutoSave(e){const t=document.querySelectorAll(e||this.autoSaveComponents.join(","));t&&t.forEach(i=>{i.addEventListener("change",this.handleChangeEvent.bind(this))})}async handleGetDataEvent(e){const t=e.detail;if(t){const{field:i,query:s,onSuccess:a,onError:r}=t;try{const n=e.target.tagName.toLowerCase();let l=[];switch(n){case"dt-button":l=await this._api.getContactInfo(this.postType,this.postId);break;case"dt-list":l=(await this._api.fetchPostsList(this.postType,s)).posts;break;case"dt-connection":{const u=t.postType||this.postType,b=await this._api.listPostsCompact(u,s),g={...b,posts:b.posts.filter(v=>v.ID!==parseInt(this.postId,10))};g!=null&&g.posts&&(l=Ke.convertApiValue("dt-connection",g==null?void 0:g.posts));break}case"dt-tags":default:l=await this._api.getMultiSelectValues(this.postType,i,s),l=l.map(u=>({id:u,label:u}));break}a(l)}catch(n){r(n)}}}async handleChangeEvent(e){const t=e.detail;if(t){const{field:i,newValue:s,oldValue:a,remove:r}=t,n=e.target.tagName.toLowerCase(),l=Ke.convertValue(n,s,a);e.target.setAttribute("loading",!0);try{let u;switch(n){case"dt-users-connection":{if(r===!0){u=await this._api.removePostShare(this.postType,this.postId,l);break}u=await this._api.addPostShare(this.postType,this.postId,l);break}default:{u=await this._api.updatePost(this.postType,this.postId,{[i]:l}),document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:u,field:i,value:l,component:n}}));break}}e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(u){console.error(u),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",u.message||u.toString())}}}static convertApiValue(e,t){let i=t;switch(e){case"dt-connection":i=t.map(s=>({id:s.ID,label:s.name??s.post_title,link:s.permalink,status:s.status}));break}return i}static convertValue(e,t,i){let s=t;if(t)switch(e.toLowerCase()){case"dt-toggle":typeof t=="string"&&(s=t.toLowerCase()==="true");break;case"dt-multi-select":case"dt-multi-select-button-group":case"dt-tags":typeof t=="string"&&(s=[t]),s={values:s.map(a=>{if(typeof a=="string"){const n={value:a};return a.startsWith("-")&&(n.delete=!0,n.value=a.substring(1)),n}const r={value:a.id};return a.delete&&(r.delete=a.delete),r}),force_values:!1};break;case"dt-users-connection":{const a=[],r=new Map(i.map(n=>[n.id,n]));for(const n of s){const l=r.get(n.id),u={id:n.id,changes:{}};if(l){let b=!1;const g=new Set([...Object.keys(l),...Object.keys(n)]);for(const v of g)n[v]!==l[v]&&(u.changes[v]=Object.prototype.hasOwnProperty.call(n,v)?n[v]:void 0,b=!0);if(b){a.push(u);break}}else{u.changes={...n},a.push(u);break}}s=a[0].id;break}case"dt-connection":case"dt-location":typeof t=="string"&&(s=[{id:t}]),s={values:s.map(a=>{const r={value:a.id};return a.delete&&(r.delete=a.delete),r}),force_values:!1};break;case"dt-multi-text":Array.isArray(t)?s=t.map(a=>{const r={...a};return delete r.tempKey,r}):typeof t=="string"&&(s=[{value:t}]);break}return s}}const Va={s226be12a5b1a27e8:"ሰነዶቹን ያንብቡ",s33f85f24c0f5f008:"አስቀምጥ",s36cb242ac90353bc:"መስኮች",s41cb4006238ebd3b:"የጅምላ አርትዕ",s5e8250fb85d64c23:"ገጠመ",s625ad019db843f94:"ተጠቀም",sac83d7f9358b43db:h`${0} ዝርዝር`,sbf1ca928ec1deb62:"ተጨማሪ እገዛ ይፈልጋሉ?",sd1a8dc951b2b6a98:"በዝርዝሩ ውስጥ እንደ ዓምዶች የትኞቹን መስኮች እንደሚያሳዩ ይምረጡ",sf9aee319a006c9b4:"አክል",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ba=Object.freeze(Object.defineProperty({__proto__:null,templates:Va},Symbol.toStringTag,{value:"Module"})),Ha={s04ceadb276bbe149:"خيارات التحميل...",s226be12a5b1a27e8:"اقرأ الوثائق",s29e25f5e4622f847:"افتح",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"غلق",s625ad019db843f94:"استخدام",s9d51bfd93b5dbeca:"عرض المحفوظات",sac83d7f9358b43db:h`${0}قائمة الأعضاء`,sb1bd536b63e9e995:"المجال الخاص: أنا فقط أستطيع رؤية محتواه",sb59d68ed12d46377:"جار التحميل",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",scb9a1ff437efbd2a:h`حَدِّد جميع ${0} التي تريد تحديثها من القائمة ، وقم بتحديثها أدناه`,sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",seafe6ef133ede7da:h`عرض 1 of ${0}`,sf9aee319a006c9b4:"لأضف",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ga=Object.freeze(Object.defineProperty({__proto__:null,templates:Ha},Symbol.toStringTag,{value:"Module"})),Wa={s226be12a5b1a27e8:"اقرأ الوثائق",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"أغلق",s625ad019db843f94:"استخدام",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",sf9aee319a006c9b4:"إضافة",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ka=Object.freeze(Object.defineProperty({__proto__:null,templates:Wa},Symbol.toStringTag,{value:"Module"})),Za={s226be12a5b1a27e8:"Прочетете документацията",s33f85f24c0f5f008:"Запазете",s36cb242ac90353bc:"Полета",s41cb4006238ebd3b:"Групово редактиране",s5e8250fb85d64c23:"Близо",s625ad019db843f94:"Използвайте",sbf1ca928ec1deb62:"Имате нужда от повече помощ?",sd1a8dc951b2b6a98:"Изберете кои полета да се показват като колони в списъка",sf9aee319a006c9b4:"Добавяне",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ja=Object.freeze(Object.defineProperty({__proto__:null,templates:Za},Symbol.toStringTag,{value:"Module"})),Qa={s226be12a5b1a27e8:"নথিপত্রাদি পাঠ করুন",s33f85f24c0f5f008:"সংরক্ষণ করুন",s36cb242ac90353bc:"ক্ষেত্র",s41cb4006238ebd3b:"বাল্ক এডিট",s5e8250fb85d64c23:"বন্ধ",s625ad019db843f94:"ব্যবহার",sbf1ca928ec1deb62:"আরও সাহায্য প্রয়োজন?",sd1a8dc951b2b6a98:"তালিকার কলাম হিসাবে কোন ক্ষেত্রগুলি প্রদর্শিত হবে তা চয়ন করুন",sf9aee319a006c9b4:"অ্যাড",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xa=Object.freeze(Object.defineProperty({__proto__:null,templates:Qa},Symbol.toStringTag,{value:"Module"})),Ya={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitajte dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate više pomoći?",scb9a1ff437efbd2a:h`Odaberite sve ${0} koje želite ažurirati sa liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Odaberite koja polja će se prikazati kao kolone na listi",seafe6ef133ede7da:h`Prikazuje se 1 od ${0}`,sf9aee319a006c9b4:"Dodati",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},er=Object.freeze(Object.defineProperty({__proto__:null,templates:Ya},Symbol.toStringTag,{value:"Module"})),tr={s226be12a5b1a27e8:"Přečtěte si dokumentaci",s33f85f24c0f5f008:"Uložit",s36cb242ac90353bc:"Pole",s41cb4006238ebd3b:"Hromadná úprava",s5e8250fb85d64c23:"Zavřít",s625ad019db843f94:"Použití",sbf1ca928ec1deb62:"Potřebujete další pomoc?",sd1a8dc951b2b6a98:"Vyberte pole, která chcete v seznamu zobrazit jako sloupce",sf9aee319a006c9b4:"Přidat",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ir=Object.freeze(Object.defineProperty({__proto__:null,templates:tr},Symbol.toStringTag,{value:"Module"})),sr={s226be12a5b1a27e8:"Lesen Sie die Dokumentation",s33f85f24c0f5f008:"Speichern",s36cb242ac90353bc:"Felder",s41cb4006238ebd3b:"Im Stapel bearbeiten",s5e8250fb85d64c23:"Schließen",s625ad019db843f94:"Verwenden",sbf1ca928ec1deb62:"Benötigen Sie weitere Hilfe?",sd1a8dc951b2b6a98:"Wählen Sie aus, welche Felder in der Liste als Spalte angezeigt werden sollen",sf9aee319a006c9b4:"Hinzufügen",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},or=Object.freeze(Object.defineProperty({__proto__:null,templates:sr},Symbol.toStringTag,{value:"Module"})),ar={s226be12a5b1a27e8:"Διαβάστε την τεκμηρίωση",s33f85f24c0f5f008:"Αποθήκευση",s36cb242ac90353bc:"Πεδία",s41cb4006238ebd3b:"Μαζική Επεξεργασία",s5e8250fb85d64c23:"Κλείσιμο",s625ad019db843f94:"Χρήση",sbf1ca928ec1deb62:"Χρειάζεστε περισσότερη βοήθεια;",sd1a8dc951b2b6a98:"Επιλέξτε ποια πεδία θα εμφανίζονται ως στήλες στη λίστα",sf9aee319a006c9b4:"Προσθήκη",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},rr=Object.freeze(Object.defineProperty({__proto__:null,templates:ar},Symbol.toStringTag,{value:"Module"})),nr={sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",sf9aee319a006c9b4:"Add",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog",s33f85f24c0f5f008:"Save",s49730f3d5751a433:"Loading...",s625ad019db843f94:"Use",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},lr=Object.freeze(Object.defineProperty({__proto__:null,templates:nr},Symbol.toStringTag,{value:"Module"})),dr={s8900c9de2dbae68b:"No hay opciones disponibles",sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sf9aee319a006c9b4:"Añadir",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sb9b8c412407d5691:"This is where the bulk edit form will go.",sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog"},cr=Object.freeze(Object.defineProperty({__proto__:null,templates:dr},Symbol.toStringTag,{value:"Module"})),ur={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Leer la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},hr=Object.freeze(Object.defineProperty({__proto__:null,templates:ur},Symbol.toStringTag,{value:"Module"})),pr={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Lee la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},fr=Object.freeze(Object.defineProperty({__proto__:null,templates:pr},Symbol.toStringTag,{value:"Module"})),br={s04ceadb276bbe149:"در حال بارگیری گزینه‌ها...",s226be12a5b1a27e8:"راهنمای سایت",s29e25f5e4622f847:"جعبه محاوره ای را باز کنید",s33f85f24c0f5f008:"صرفه جویی",s36cb242ac90353bc:"حوزه‌ها",s41cb4006238ebd3b:"ویرایش انبوه",s5e8250fb85d64c23:"بستن",s625ad019db843f94:"استفاده کنید",s9d51bfd93b5dbeca:"نمایش بایگانی شده",sac83d7f9358b43db:h`لیست ${0}`,sb1bd536b63e9e995:"زمینه خصوصی: فقط من می توانم محتوای آن را داشته باشم",sb59d68ed12d46377:"بارگیری",sbf1ca928ec1deb62:"آیا به راهنمایی بیشتری نیاز دارید؟",scb9a1ff437efbd2a:h`همۀ ${0} مورد نظر برای به روزرسانی را از لیست انتخاب کنید و آن‌ها را در زیر به روز کنید`,sd1a8dc951b2b6a98:"انتخاب کنید که کدام یک از حوزه‌ها به‌عنوان ستون در لیست نمایش داده شوند",seafe6ef133ede7da:h`نمایش 1 از ${0}`,sf9aee319a006c9b4:"افزودن",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},gr=Object.freeze(Object.defineProperty({__proto__:null,templates:br},Symbol.toStringTag,{value:"Module"})),mr={s04ceadb276bbe149:"Chargement les options...",s226be12a5b1a27e8:"Lire la documentation",s29e25f5e4622f847:"Ouvrir la boîte de dialogue",s33f85f24c0f5f008:"sauver",s36cb242ac90353bc:"Champs",s41cb4006238ebd3b:"Modification groupée",s5e8250fb85d64c23:"Fermer",s625ad019db843f94:"Utiliser",s9d51bfd93b5dbeca:"Afficher Archivé",sac83d7f9358b43db:h`${0} Liste`,sb1bd536b63e9e995:"Champ privé : je suis le seul à voir son contenu",sb59d68ed12d46377:"Chargement",sbf1ca928ec1deb62:"Besoin d'aide ?",scb9a1ff437efbd2a:h`Sélectionnez tous les ${0} que vous souhaitez mettre à jour dans la liste et mettez-les à jour ci-dessous`,sd1a8dc951b2b6a98:"Choisissez les champs à afficher sous forme de colonnes dans la liste",seafe6ef133ede7da:h`Affichage de 1 sur ${0}`,sf9aee319a006c9b4:"Ajouter",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},vr=Object.freeze(Object.defineProperty({__proto__:null,templates:mr},Symbol.toStringTag,{value:"Module"})),yr={s226be12a5b1a27e8:"डॉक्यूमेंटेशन पढ़ें",s33f85f24c0f5f008:"बचाना",s36cb242ac90353bc:"खेत",s41cb4006238ebd3b:"थोक संपादित",s5e8250fb85d64c23:"बंद",s625ad019db843f94:"उपयोग",sbf1ca928ec1deb62:"क्या और मदद चाहिये?",sd1a8dc951b2b6a98:"सूची में कॉलम के रूप में प्रदर्शित करने के लिए कौन से फ़ील्ड चुनें",sf9aee319a006c9b4:"जोडें",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},wr=Object.freeze(Object.defineProperty({__proto__:null,templates:yr},Symbol.toStringTag,{value:"Module"})),_r={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitaj dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Spremi",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvoriti",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate li pomoć?",scb9a1ff437efbd2a:h`Odaberite sve${0}koje želite ažurirati s liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Izaberite polja koja će se prikazivati kao stupci na popisu",seafe6ef133ede7da:h`Prikazuje se 1 od${0}`,sf9aee319a006c9b4:"Dodaj",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},$r=Object.freeze(Object.defineProperty({__proto__:null,templates:_r},Symbol.toStringTag,{value:"Module"})),xr={s226be12a5b1a27e8:"Olvasd el a dokumentációt",s33f85f24c0f5f008:"Megment",s36cb242ac90353bc:"Mezők",s41cb4006238ebd3b:"Tömeges Szerkesztés",s5e8250fb85d64c23:"Bezár",s625ad019db843f94:"Használ",sbf1ca928ec1deb62:"Több segítség szükséges?",sd1a8dc951b2b6a98:"Válassza ki, melyik mezők jelenjenek meg oszlopként a listában",sf9aee319a006c9b4:"Hozzáadás",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},kr=Object.freeze(Object.defineProperty({__proto__:null,templates:xr},Symbol.toStringTag,{value:"Module"})),Sr={s226be12a5b1a27e8:"Bacalah dokumentasi",s33f85f24c0f5f008:"Simpan",s36cb242ac90353bc:"Larik",s41cb4006238ebd3b:"Edit Massal",s5e8250fb85d64c23:"Menutup",s625ad019db843f94:"Gunakan",sbf1ca928ec1deb62:"Perlukan bantuan lagi?",sd1a8dc951b2b6a98:"Pilih larik mana yang akan ditampilkan sebagai kolom dalam daftar",sf9aee319a006c9b4:"Tambah",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Tr=Object.freeze(Object.defineProperty({__proto__:null,templates:Sr},Symbol.toStringTag,{value:"Module"})),Er={s04ceadb276bbe149:"Caricando opzioni...",s226be12a5b1a27e8:"Leggi la documentazione",s29e25f5e4622f847:"Apri Dialogo",s33f85f24c0f5f008:"Salvare",s36cb242ac90353bc:"Campi",s41cb4006238ebd3b:"Modifica in blocco",s5e8250fb85d64c23:"Chiudi",s625ad019db843f94:"Uso",s9d51bfd93b5dbeca:"Visualizza Archiviati",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privato: Solo io posso vedere i suoi contenuti",sb59d68ed12d46377:"Caricando",sbf1ca928ec1deb62:"Hai bisogno di ulteriore assistenza?",scb9a1ff437efbd2a:h`Seleziona tutti i ${0}vuoi aggiornare dalla lista e aggiornali sotto`,sd1a8dc951b2b6a98:"Scegli quali campi visualizzare come colonne nell'elenco",seafe6ef133ede7da:h`Visualizzando 1 di ${0}`,sf9aee319a006c9b4:"Inserisci",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ar=Object.freeze(Object.defineProperty({__proto__:null,templates:Er},Symbol.toStringTag,{value:"Module"})),Or={s226be12a5b1a27e8:"ドキュメントを読む",s33f85f24c0f5f008:"セーブ",s36cb242ac90353bc:"田畑",s41cb4006238ebd3b:"一括編集",s5e8250fb85d64c23:"閉じる",s625ad019db843f94:"使用する",sbf1ca928ec1deb62:"もっと助けが必要ですか？",sd1a8dc951b2b6a98:"リストの列として表示するフィールドを選択します",sf9aee319a006c9b4:"追加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Cr=Object.freeze(Object.defineProperty({__proto__:null,templates:Or},Symbol.toStringTag,{value:"Module"})),Lr={s226be12a5b1a27e8:"문서 읽기",s33f85f24c0f5f008:"구하다",s36cb242ac90353bc:"필드",s41cb4006238ebd3b:"대량 수정",s5e8250fb85d64c23:"닫기",s625ad019db843f94:"사용",sbf1ca928ec1deb62:"더 많은 도움이 필요하신가요?",sd1a8dc951b2b6a98:"목록에서 어떤 필드를 표시할지 고르세요",sf9aee319a006c9b4:"추가",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Pr=Object.freeze(Object.defineProperty({__proto__:null,templates:Lr},Symbol.toStringTag,{value:"Module"})),Ir={s226be12a5b1a27e8:"Прочитај ја документацијата",s33f85f24c0f5f008:"Зачувај",s36cb242ac90353bc:"Полиња",s41cb4006238ebd3b:"Уреди повеќе",s5e8250fb85d64c23:"Затвори",s625ad019db843f94:"Користи",sbf1ca928ec1deb62:"Дали ти треба повеќе помош?",sd1a8dc951b2b6a98:"Избери кои полиња да се прикажат како колони во листата",sf9aee319a006c9b4:"Додади",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Mr=Object.freeze(Object.defineProperty({__proto__:null,templates:Ir},Symbol.toStringTag,{value:"Module"})),jr={s226be12a5b1a27e8:"कागदपत्रे वाचा.",s33f85f24c0f5f008:"जतन करा",s36cb242ac90353bc:"क्षेत्रे",s41cb4006238ebd3b:"बल्क एडिट करा",s5e8250fb85d64c23:"बंद करा",s625ad019db843f94:"वापर",sbf1ca928ec1deb62:"अधिक मदत आवश्यक आहे का?",sd1a8dc951b2b6a98:"यादीत कोणती क्षेत्रे स्तंभ म्हणून दर्शवली जावीत हे निवडा",sf9aee319a006c9b4:"समाविष्ट करा",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},zr=Object.freeze(Object.defineProperty({__proto__:null,templates:jr},Symbol.toStringTag,{value:"Module"})),Dr={s226be12a5b1a27e8:"စာရွက်စာတမ်းများကိုဖတ်ပါ",s33f85f24c0f5f008:"သိမ်းဆည်းပါ",s36cb242ac90353bc:"နယ်ပယ်ဒေသများ",s5e8250fb85d64c23:"ပိတ်သည်",s625ad019db843f94:"အသုံးပြုပါ",sbf1ca928ec1deb62:"နောက်ထပ်အကူအညီလိုပါသလား။",sd1a8dc951b2b6a98:"စာရင်းရှိကော်လံများအနေဖြင့်ဖော်ပြမည့်မည်သည့်နယ်ပယ်ဒေသများကိုရွေးချယ်ပါ",sf9aee319a006c9b4:"ထည့်ပါ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Rr=Object.freeze(Object.defineProperty({__proto__:null,templates:Dr},Symbol.toStringTag,{value:"Module"})),Nr={s226be12a5b1a27e8:"कागजात पढ्नुहोस्",s33f85f24c0f5f008:"सुरक्षित गर्नुहोस",s36cb242ac90353bc:"क्षेत्रहरू",s41cb4006238ebd3b:"थोक सम्पादन",s5e8250fb85d64c23:"बन्द गर्नुहोस",s625ad019db843f94:"प्रयोग गर्नुहोस्",sbf1ca928ec1deb62:"थप मद्दत चाहिन्छ?",sd1a8dc951b2b6a98:"सूचीमा स्तम्भहरूको रूपमा कुन क्षेत्रहरू प्रदर्शन गर्ने छनौट गर्नुहोस्",sf9aee319a006c9b4:"थप",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},qr=Object.freeze(Object.defineProperty({__proto__:null,templates:Nr},Symbol.toStringTag,{value:"Module"})),Fr={s04ceadb276bbe149:"aan het laden.....",s226be12a5b1a27e8:"Lees de documentatie",s29e25f5e4622f847:"Dialoogvenster openen",s33f85f24c0f5f008:"Opslaan",s36cb242ac90353bc:"Velden",s41cb4006238ebd3b:"Bulkbewerking",s5e8250fb85d64c23:"sluit",s625ad019db843f94:"Gebruiken",sac83d7f9358b43db:h`${0} Lijst`,sb1bd536b63e9e995:"Privéveld: alleen ik kan de inhoud zien",sb59d68ed12d46377:"aan het laden",sbf1ca928ec1deb62:"Meer hulp nodig?",sd1a8dc951b2b6a98:"Kies welke velden u als kolommen in de lijst wilt weergeven",seafe6ef133ede7da:h`1 van ${0} laten zien`,sf9aee319a006c9b4:"Toevoegen",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,s9d51bfd93b5dbeca:"Show Archived"},Ur=Object.freeze(Object.defineProperty({__proto__:null,templates:Fr},Symbol.toStringTag,{value:"Module"})),Vr={s226be12a5b1a27e8:"ਦਸਤਾਵੇਜ਼ ਪੜ੍ਹੋ",s33f85f24c0f5f008:"ਸੇਵ",s36cb242ac90353bc:"ਖੇਤਰ",s41cb4006238ebd3b:"ਥੋਕ ਸੰਪਾਦਨ",s5e8250fb85d64c23:"ਬੰਦ ਕਰੋ",s625ad019db843f94:"ਵਰਤੋਂ",sbf1ca928ec1deb62:"ਹੋਰ ਮਦਦ ਦੀ ਲੋੜ ਹੈ?",sd1a8dc951b2b6a98:"ਸੂਚੀ ਵਿੱਚ ਕਾਲਮ ਦੇ ਰੂਪ ਵਿੱਚ ਪ੍ਰਦਰਸ਼ਿਤ ਕਰਨ ਲਈ ਕਿਹੜੇ ਖੇਤਰ ਚੁਣੋ",sf9aee319a006c9b4:"ਸ਼ਾਮਲ ਕਰੋ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Br=Object.freeze(Object.defineProperty({__proto__:null,templates:Vr},Symbol.toStringTag,{value:"Module"})),Hr={s226be12a5b1a27e8:"Przeczytaj dokumentację",s33f85f24c0f5f008:"Zapisać",s36cb242ac90353bc:"Pola",s41cb4006238ebd3b:"Edycja zbiorcza",s5e8250fb85d64c23:"Zamknij",s625ad019db843f94:"Posługiwać się",sbf1ca928ec1deb62:"Potrzebujesz pomocy?",sd1a8dc951b2b6a98:"Wybierz, które pola mają być wyświetlane jako kolumny na liście",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Gr=Object.freeze(Object.defineProperty({__proto__:null,templates:Hr},Symbol.toStringTag,{value:"Module"})),Wr={s226be12a5b1a27e8:"Leia a documentação",s33f85f24c0f5f008:"Salvar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edição em massa",s5e8250fb85d64c23:"Fechar",s625ad019db843f94:"Usar",sbf1ca928ec1deb62:"Precisa de mais ajuda?",sd1a8dc951b2b6a98:"Escolha quais campos exibir como colunas na lista",sf9aee319a006c9b4:"Adicionar",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Kr=Object.freeze(Object.defineProperty({__proto__:null,templates:Wr},Symbol.toStringTag,{value:"Module"})),Zr={s226be12a5b1a27e8:"Citiți documentația",s33f85f24c0f5f008:"Salvați",s36cb242ac90353bc:"Câmpuri",s41cb4006238ebd3b:"Editare masivă",s5e8250fb85d64c23:"Închide",s625ad019db843f94:"Utilizare",sbf1ca928ec1deb62:"Ai nevoie de mai mult ajutor?",sd1a8dc951b2b6a98:"Alegeți câmpurile care să fie afișate în coloane în listă",sf9aee319a006c9b4:"Adăuga",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Jr=Object.freeze(Object.defineProperty({__proto__:null,templates:Zr},Symbol.toStringTag,{value:"Module"})),Qr={s226be12a5b1a27e8:"Читать документацию",s33f85f24c0f5f008:"Сохранить",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Массовое редактирование",s5e8250fb85d64c23:"Закрыть",s625ad019db843f94:"Использовать",sbf1ca928ec1deb62:"Нужна дополнительная помощь?",sd1a8dc951b2b6a98:"Выберите, какие поля отображать как столбцы в списке",sf9aee319a006c9b4:"Добавить",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xr=Object.freeze(Object.defineProperty({__proto__:null,templates:Qr},Symbol.toStringTag,{value:"Module"})),Yr={s226be12a5b1a27e8:"Preberite dokumentacijo",s33f85f24c0f5f008:"Shrani",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Urejanje v velikem obsegu",s5e8250fb85d64c23:"Zapri",s625ad019db843f94:"Uporaba",sbf1ca928ec1deb62:"Potrebujete več pomoči?",sd1a8dc951b2b6a98:"Izberite, katera polja naj bodo prikazana kot stolpci na seznamu",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},en=Object.freeze(Object.defineProperty({__proto__:null,templates:Yr},Symbol.toStringTag,{value:"Module"})),tn={s226be12a5b1a27e8:"Pročitajte dokumentaciju",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"masovno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristiti",sbf1ca928ec1deb62:"Treba vam više pomoći?",sd1a8dc951b2b6a98:"Izaberite koja polja da se prikazuju kao kolone na listi",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},sn=Object.freeze(Object.defineProperty({__proto__:null,templates:tn},Symbol.toStringTag,{value:"Module"})),on={s04ceadb276bbe149:"Inapakia chaguo...",s226be12a5b1a27e8:"Soma nyaraka",s29e25f5e4622f847:"Fungua Kidirisha",s33f85f24c0f5f008:"Hifadhi",s36cb242ac90353bc:"Mashamba",s41cb4006238ebd3b:"Hariri kwa Wingi",s5e8250fb85d64c23:"Funga",s625ad019db843f94:"Tumia",s9d51bfd93b5dbeca:"Onyesha Kumbukumbu",sac83d7f9358b43db:h`Orodha ya${0}`,sb1bd536b63e9e995:"Sehemu ya Faragha: Ni mimi pekee ninayeweza kuona maudhui yake",sb59d68ed12d46377:"Inapakia",sbf1ca928ec1deb62:"Unahitaji msaada zaidi?",scb9a1ff437efbd2a:h`Chagua ${0} zote ungependa kusasisha kutoka kwenye orodha, na uzisasishe hapa chini.`,sd1a8dc951b2b6a98:"Chagua ni sehemu zipi zitaonyeshwa kama safu wima kwenye orodha",seafe6ef133ede7da:h`Inaonyesha 1 kati ya ${0}`,sf9aee319a006c9b4:"Ongeza",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},an=Object.freeze(Object.defineProperty({__proto__:null,templates:on},Symbol.toStringTag,{value:"Module"})),rn={s226be12a5b1a27e8:"อ่านเอกสาร",s33f85f24c0f5f008:"บันทึก",s36cb242ac90353bc:"ฟิลด์",s41cb4006238ebd3b:"แก้ไขเป็นกลุ่ม",s5e8250fb85d64c23:"ปิด",s625ad019db843f94:"ใช้",sbf1ca928ec1deb62:"ต้องการความช่วยเหลือเพิ่มเติมหรือไม่?",sd1a8dc951b2b6a98:"เลือกฟิลด์ที่จะแสดงเป็นคอลัมน์ในรายการ",sf9aee319a006c9b4:"เพิ่ม",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nn=Object.freeze(Object.defineProperty({__proto__:null,templates:rn},Symbol.toStringTag,{value:"Module"})),ln={s226be12a5b1a27e8:"Basahin ang dokumentasyon",s33f85f24c0f5f008:"I-save",s36cb242ac90353bc:"Mga Field",s41cb4006238ebd3b:"Maramihang Pag-edit",s5e8250fb85d64c23:"Isara",s625ad019db843f94:"Gamitin",sbf1ca928ec1deb62:"Kailangan mo pa ba ng tulong?",sd1a8dc951b2b6a98:"Piliin kung aling mga field ang ipapakita bilang mga column sa listahan",sf9aee319a006c9b4:"Idagdag",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},dn=Object.freeze(Object.defineProperty({__proto__:null,templates:ln},Symbol.toStringTag,{value:"Module"})),cn={s04ceadb276bbe149:"Seçenekler Yükleniyor...",s226be12a5b1a27e8:"Belgeleri oku",s29e25f5e4622f847:"İletişim Kutusunu Aç",s33f85f24c0f5f008:"Kaydet",s36cb242ac90353bc:"Alanlar",s41cb4006238ebd3b:"Toplu Düzenleme",s5e8250fb85d64c23:"Kapat",s625ad019db843f94:"Kullan",s9d51bfd93b5dbeca:"Arşivlenmiş Göster",sac83d7f9358b43db:h`${0} Listesi`,sb1bd536b63e9e995:"Özel Alan: İçeriğini sadece ben görebilirim",sb59d68ed12d46377:"Yükleniyor",sbf1ca928ec1deb62:"Daha fazla yardıma ihtiyacınız var mı?",scb9a1ff437efbd2a:h`Listeden güncellemek istediğiniz tüm ${0} 'i seçin ve aşağıda güncelleyin`,sd1a8dc951b2b6a98:"Listede Hangi Alanların Sütun Olarak Görüntüleneceğini Seçin",seafe6ef133ede7da:h`Gösteriliyor 1 of ${0}`,sf9aee319a006c9b4:"Ekle",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},un=Object.freeze(Object.defineProperty({__proto__:null,templates:cn},Symbol.toStringTag,{value:"Module"})),hn={s226be12a5b1a27e8:"Прочитайте документацію",s33f85f24c0f5f008:"Зберегти",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Масове редагування",s5e8250fb85d64c23:"Закрити",s625ad019db843f94:"Використати",sbf1ca928ec1deb62:"Потрібна додаткова допомога?",sd1a8dc951b2b6a98:"Виберіть, яке поле відображати у вигляді стовпців у списку",sf9aee319a006c9b4:"Додати",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},pn=Object.freeze(Object.defineProperty({__proto__:null,templates:hn},Symbol.toStringTag,{value:"Module"})),fn={s226be12a5b1a27e8:"Đọc tài liệu",s33f85f24c0f5f008:"Lưu",s36cb242ac90353bc:"Trường",s41cb4006238ebd3b:"Chỉnh sửa Hàng loạt",s5e8250fb85d64c23:"Đóng",s625ad019db843f94:"Sử dụng",sbf1ca928ec1deb62:"Bạn cần trợ giúp thêm?",sd1a8dc951b2b6a98:"Chọn các trường để hiển thị dưới dạng cột trong danh sách",sf9aee319a006c9b4:"Bổ sung",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},bn=Object.freeze(Object.defineProperty({__proto__:null,templates:fn},Symbol.toStringTag,{value:"Module"})),gn={s226be12a5b1a27e8:"阅读文档",s33f85f24c0f5f008:"保存",s36cb242ac90353bc:"字段",s41cb4006238ebd3b:"批量编辑",s5e8250fb85d64c23:"关",s625ad019db843f94:"使用",sbf1ca928ec1deb62:"需要更多帮助吗？",sd1a8dc951b2b6a98:"选择哪些字段要在列表中显示为列",sf9aee319a006c9b4:"添加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},mn=Object.freeze(Object.defineProperty({__proto__:null,templates:gn},Symbol.toStringTag,{value:"Module"})),vn={s04ceadb276bbe149:"正在載入選項...",s226be12a5b1a27e8:"閱讀文檔",s29e25f5e4622f847:"開啟對話視窗",s33f85f24c0f5f008:"儲存",s36cb242ac90353bc:"欄位",s41cb4006238ebd3b:"大量編輯",s5e8250fb85d64c23:"關",s625ad019db843f94:"使用",s9d51bfd93b5dbeca:"顯示已儲存",sac83d7f9358b43db:h`${0} 清單`,sb1bd536b63e9e995:"私人欄位：只有我可以看見內容",sb59d68ed12d46377:"載入中",sbf1ca928ec1deb62:"需要更多幫助嗎？",scb9a1ff437efbd2a:h`從清單中選取要更新的項目${0}，並在下面進行更新`,sd1a8dc951b2b6a98:"選擇哪些欄位要顯示為列表中的直行",seafe6ef133ede7da:h`第1頁 （共${0}頁）`,sf9aee319a006c9b4:"新增",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},yn=Object.freeze(Object.defineProperty({__proto__:null,templates:vn},Symbol.toStringTag,{value:"Module"}));return $.ApiService=Re,$.ComponentService=Ke,$.DtAlert=ks,$.DtBase=R,$.DtButton=vi,$.DtChurchHealthCircle=$i,$.DtConnection=us,$.DtCopyText=ps,$.DtDate=fs,$.DtDropdown=_i,$.DtFormBase=z,$.DtIcon=ns,$.DtLabel=ls,$.DtList=Ss,$.DtLocation=bs,$.DtLocationMap=ms,$.DtMapModal=gs,$.DtModal=wi,$.DtMultiSelect=jt,$.DtMultiSelectButtonGroup=xs,$.DtMultiText=$s,$.DtNumberField=vs,$.DtSingleSelect=ys,$.DtTags=Oe,$.DtText=zt,$.DtTextArea=ws,$.DtTile=Ts,$.DtToggle=_s,$.DtUsersConnection=hs,Object.defineProperty($,Symbol.toStringTag,{value:"Module"}),$}({});
