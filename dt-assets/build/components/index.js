var DtWebComponents=function($){"use strict";var Pr=Object.defineProperty;var Ir=($,F,G)=>F in $?Pr($,F,{enumerable:!0,configurable:!0,writable:!0,value:G}):$[F]=G;var Qe=($,F,G)=>Ir($,typeof F!="symbol"?F+"":F,G);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var To;const F=globalThis,G=F.ShadowRoot&&(F.ShadyCSS===void 0||F.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,Bt=Symbol(),qt=new WeakMap;let Uo=class{constructor(e,t,s){if(this._$cssResult$=!0,s!==Bt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(G&&e===void 0){const s=t!==void 0&&t.length===1;s&&(e=qt.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),s&&qt.set(t,e))}return e}toString(){return this.cssText}};const Bo=i=>new Uo(typeof i=="string"?i:i+"",void 0,Bt),qo=(i,e)=>{if(G)i.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const s=document.createElement("style"),o=F.litNonce;o!==void 0&&s.setAttribute("nonce",o),s.textContent=t.cssText,i.appendChild(s)}},Vt=G?i=>i:i=>i instanceof CSSStyleSheet?(e=>{let t="";for(const s of e.cssRules)t+=s.cssText;return Bo(t)})(i):i;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:Vo,defineProperty:Ho,getOwnPropertyDescriptor:Wo,getOwnPropertyNames:Go,getOwnPropertySymbols:Ko,getPrototypeOf:Zo}=Object,K=globalThis,Ht=K.trustedTypes,Jo=Ht?Ht.emptyScript:"",Xe=K.reactiveElementPolyfillSupport,he=(i,e)=>i,Ye={toAttribute(i,e){switch(e){case Boolean:i=i?Jo:null;break;case Object:case Array:i=i==null?i:JSON.stringify(i)}return i},fromAttribute(i,e){let t=i;switch(e){case Boolean:t=i!==null;break;case Number:t=i===null?null:Number(i);break;case Object:case Array:try{t=JSON.parse(i)}catch{t=null}}return t}},Wt=(i,e)=>!Vo(i,e),Gt={attribute:!0,type:String,converter:Ye,reflect:!1,hasChanged:Wt};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),K.litPropertyMetadata??(K.litPropertyMetadata=new WeakMap);let pe=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Gt){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const s=Symbol(),o=this.getPropertyDescriptor(e,s,t);o!==void 0&&Ho(this.prototype,e,o)}}static getPropertyDescriptor(e,t,s){const{get:o,set:a}=Wo(this.prototype,e)??{get(){return this[t]},set(n){this[t]=n}};return{get(){return o==null?void 0:o.call(this)},set(n){const r=o==null?void 0:o.call(this);a.call(this,n),this.requestUpdate(e,r,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Gt}static _$Ei(){if(this.hasOwnProperty(he("elementProperties")))return;const e=Zo(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(he("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(he("properties"))){const t=this.properties,s=[...Go(t),...Ko(t)];for(const o of s)this.createProperty(o,t[o])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[s,o]of t)this.elementProperties.set(s,o)}this._$Eh=new Map;for(const[t,s]of this.elementProperties){const o=this._$Eu(t,s);o!==void 0&&this._$Eh.set(o,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const s=new Set(e.flat(1/0).reverse());for(const o of s)t.unshift(Vt(o))}else e!==void 0&&t.push(Vt(e));return t}static _$Eu(e,t){const s=t.attribute;return s===!1?void 0:typeof s=="string"?s:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const s of t.keys())this.hasOwnProperty(s)&&(e.set(s,this[s]),delete this[s]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return qo(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var s;return(s=t.hostConnected)==null?void 0:s.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var s;return(s=t.hostDisconnected)==null?void 0:s.call(t)})}attributeChangedCallback(e,t,s){this._$AK(e,s)}_$EC(e,t){var a;const s=this.constructor.elementProperties.get(e),o=this.constructor._$Eu(e,s);if(o!==void 0&&s.reflect===!0){const n=(((a=s.converter)==null?void 0:a.toAttribute)!==void 0?s.converter:Ye).toAttribute(t,s.type);this._$Em=e,n==null?this.removeAttribute(o):this.setAttribute(o,n),this._$Em=null}}_$AK(e,t){var a;const s=this.constructor,o=s._$Eh.get(e);if(o!==void 0&&this._$Em!==o){const n=s.getPropertyOptions(o),r=typeof n.converter=="function"?{fromAttribute:n.converter}:((a=n.converter)==null?void 0:a.fromAttribute)!==void 0?n.converter:Ye;this._$Em=o,this[o]=r.fromAttribute(t,n.type),this._$Em=null}}requestUpdate(e,t,s){if(e!==void 0){if(s??(s=this.constructor.getPropertyOptions(e)),!(s.hasChanged??Wt)(this[e],t))return;this.P(e,t,s)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,s){this._$AL.has(e)||this._$AL.set(e,t),s.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var s;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,n]of this._$Ep)this[a]=n;this._$Ep=void 0}const o=this.constructor.elementProperties;if(o.size>0)for(const[a,n]of o)n.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],n)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(s=this._$EO)==null||s.forEach(o=>{var a;return(a=o.hostUpdate)==null?void 0:a.call(o)}),this.update(t)):this._$EU()}catch(o){throw e=!1,this._$EU(),o}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(s=>{var o;return(o=s.hostUpdated)==null?void 0:o.call(s)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}};pe.elementStyles=[],pe.shadowRootOptions={mode:"open"},pe[he("elementProperties")]=new Map,pe[he("finalized")]=new Map,Xe==null||Xe({ReactiveElement:pe}),(K.reactiveElementVersions??(K.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const fe=globalThis,Le=fe.trustedTypes,Kt=Le?Le.createPolicy("lit-html",{createHTML:i=>i}):void 0,Zt="$lit$",Z=`lit$${Math.random().toFixed(9).slice(2)}$`,Jt="?"+Z,Qo=`<${Jt}>`,te=document,be=()=>te.createComment(""),ge=i=>i===null||typeof i!="object"&&typeof i!="function",et=Array.isArray,Xo=i=>et(i)||typeof(i==null?void 0:i[Symbol.iterator])=="function",tt=`[ 	
\f\r]`,me=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Qt=/-->/g,Xt=/>/g,se=RegExp(`>|${tt}(?:([^\\s"'>=/]+)(${tt}*=${tt}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),Yt=/'/g,es=/"/g,ts=/^(?:script|style|textarea|title)$/i,Yo=i=>(e,...t)=>({_$litType$:i,strings:e,values:t}),d=Yo(1),q=Symbol.for("lit-noChange"),C=Symbol.for("lit-nothing"),ss=new WeakMap,oe=te.createTreeWalker(te,129);function os(i,e){if(!et(i)||!i.hasOwnProperty("raw"))throw Error("invalid template strings array");return Kt!==void 0?Kt.createHTML(e):e}const ei=(i,e)=>{const t=i.length-1,s=[];let o,a=e===2?"<svg>":e===3?"<math>":"",n=me;for(let r=0;r<t;r++){const l=i[r];let u,b,g=-1,v=0;for(;v<l.length&&(n.lastIndex=v,b=n.exec(l),b!==null);)v=n.lastIndex,n===me?b[1]==="!--"?n=Qt:b[1]!==void 0?n=Xt:b[2]!==void 0?(ts.test(b[2])&&(o=RegExp("</"+b[2],"g")),n=se):b[3]!==void 0&&(n=se):n===se?b[0]===">"?(n=o??me,g=-1):b[1]===void 0?g=-2:(g=n.lastIndex-b[2].length,u=b[1],n=b[3]===void 0?se:b[3]==='"'?es:Yt):n===es||n===Yt?n=se:n===Qt||n===Xt?n=me:(n=se,o=void 0);const y=n===se&&i[r+1].startsWith("/>")?" ":"";a+=n===me?l+Qo:g>=0?(s.push(u),l.slice(0,g)+Zt+l.slice(g)+Z+y):l+Z+(g===-2?r:y)}return[os(i,a+(i[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),s]};class ve{constructor({strings:e,_$litType$:t},s){let o;this.parts=[];let a=0,n=0;const r=e.length-1,l=this.parts,[u,b]=ei(e,t);if(this.el=ve.createElement(u,s),oe.currentNode=this.el.content,t===2||t===3){const g=this.el.content.firstChild;g.replaceWith(...g.childNodes)}for(;(o=oe.nextNode())!==null&&l.length<r;){if(o.nodeType===1){if(o.hasAttributes())for(const g of o.getAttributeNames())if(g.endsWith(Zt)){const v=b[n++],y=o.getAttribute(g).split(Z),_=/([.?@])?(.*)/.exec(v);l.push({type:1,index:a,name:_[2],strings:y,ctor:_[1]==="."?si:_[1]==="?"?oi:_[1]==="@"?ii:Pe}),o.removeAttribute(g)}else g.startsWith(Z)&&(l.push({type:6,index:a}),o.removeAttribute(g));if(ts.test(o.tagName)){const g=o.textContent.split(Z),v=g.length-1;if(v>0){o.textContent=Le?Le.emptyScript:"";for(let y=0;y<v;y++)o.append(g[y],be()),oe.nextNode(),l.push({type:2,index:++a});o.append(g[v],be())}}}else if(o.nodeType===8)if(o.data===Jt)l.push({type:2,index:a});else{let g=-1;for(;(g=o.data.indexOf(Z,g+1))!==-1;)l.push({type:7,index:a}),g+=Z.length-1}a++}}static createElement(e,t){const s=te.createElement("template");return s.innerHTML=e,s}}function le(i,e,t=i,s){var n,r;if(e===q)return e;let o=s!==void 0?(n=t._$Co)==null?void 0:n[s]:t._$Cl;const a=ge(e)?void 0:e._$litDirective$;return(o==null?void 0:o.constructor)!==a&&((r=o==null?void 0:o._$AO)==null||r.call(o,!1),a===void 0?o=void 0:(o=new a(i),o._$AT(i,t,s)),s!==void 0?(t._$Co??(t._$Co=[]))[s]=o:t._$Cl=o),o!==void 0&&(e=le(i,o._$AS(i,e.values),o,s)),e}let ti=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:s}=this._$AD,o=((e==null?void 0:e.creationScope)??te).importNode(t,!0);oe.currentNode=o;let a=oe.nextNode(),n=0,r=0,l=s[0];for(;l!==void 0;){if(n===l.index){let u;l.type===2?u=new ce(a,a.nextSibling,this,e):l.type===1?u=new l.ctor(a,l.name,l.strings,this,e):l.type===6&&(u=new ai(a,this,e)),this._$AV.push(u),l=s[++r]}n!==(l==null?void 0:l.index)&&(a=oe.nextNode(),n++)}return oe.currentNode=te,o}p(e){let t=0;for(const s of this._$AV)s!==void 0&&(s.strings!==void 0?(s._$AI(e,s,t),t+=s.strings.length-2):s._$AI(e[t])),t++}};class ce{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,s,o){this.type=2,this._$AH=C,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=s,this.options=o,this._$Cv=(o==null?void 0:o.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=le(this,e,t),ge(e)?e===C||e==null||e===""?(this._$AH!==C&&this._$AR(),this._$AH=C):e!==this._$AH&&e!==q&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Xo(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==C&&ge(this._$AH)?this._$AA.nextSibling.data=e:this.T(te.createTextNode(e)),this._$AH=e}$(e){var a;const{values:t,_$litType$:s}=e,o=typeof s=="number"?this._$AC(e):(s.el===void 0&&(s.el=ve.createElement(os(s.h,s.h[0]),this.options)),s);if(((a=this._$AH)==null?void 0:a._$AD)===o)this._$AH.p(t);else{const n=new ti(o,this),r=n.u(this.options);n.p(t),this.T(r),this._$AH=n}}_$AC(e){let t=ss.get(e.strings);return t===void 0&&ss.set(e.strings,t=new ve(e)),t}k(e){et(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let s,o=0;for(const a of e)o===t.length?t.push(s=new ce(this.O(be()),this.O(be()),this,this.options)):s=t[o],s._$AI(a),o++;o<t.length&&(this._$AR(s&&s._$AB.nextSibling,o),t.length=o)}_$AR(e=this._$AA.nextSibling,t){var s;for((s=this._$AP)==null?void 0:s.call(this,!1,!0,t);e&&e!==this._$AB;){const o=e.nextSibling;e.remove(),e=o}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class Pe{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,s,o,a){this.type=1,this._$AH=C,this._$AN=void 0,this.element=e,this.name=t,this._$AM=o,this.options=a,s.length>2||s[0]!==""||s[1]!==""?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=C}_$AI(e,t=this,s,o){const a=this.strings;let n=!1;if(a===void 0)e=le(this,e,t,0),n=!ge(e)||e!==this._$AH&&e!==q,n&&(this._$AH=e);else{const r=e;let l,u;for(e=a[0],l=0;l<a.length-1;l++)u=le(this,r[s+l],t,l),u===q&&(u=this._$AH[l]),n||(n=!ge(u)||u!==this._$AH[l]),u===C?e=C:e!==C&&(e+=(u??"")+a[l+1]),this._$AH[l]=u}n&&!o&&this.j(e)}j(e){e===C?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class si extends Pe{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===C?void 0:e}}class oi extends Pe{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==C)}}class ii extends Pe{constructor(e,t,s,o,a){super(e,t,s,o,a),this.type=5}_$AI(e,t=this){if((e=le(this,e,t,0)??C)===q)return;const s=this._$AH,o=e===C&&s!==C||e.capture!==s.capture||e.once!==s.once||e.passive!==s.passive,a=e!==C&&(s===C||o);o&&this.element.removeEventListener(this.name,this,s),a&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class ai{constructor(e,t,s){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=s}get _$AU(){return this._$AM._$AU}_$AI(e){le(this,e)}}const ni={I:ce},st=fe.litHtmlPolyfillSupport;st==null||st(ve,ce),(fe.litHtmlVersions??(fe.litHtmlVersions=[])).push("3.2.1");const ri=(i,e,t)=>{const s=(t==null?void 0:t.renderBefore)??e;let o=s._$litPart$;if(o===void 0){const a=(t==null?void 0:t.renderBefore)??null;s._$litPart$=o=new ce(e.insertBefore(be(),a),a,void 0,t??{})}return o._$AI(i),o};/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const Ie=globalThis,ot=Ie.ShadowRoot&&(Ie.ShadyCSS===void 0||Ie.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,it=Symbol(),is=new WeakMap;let as=class{constructor(e,t,s){if(this._$cssResult$=!0,s!==it)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(ot&&e===void 0){const s=t!==void 0&&t.length===1;s&&(e=is.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),s&&is.set(t,e))}return e}toString(){return this.cssText}};const li=i=>new as(typeof i=="string"?i:i+"",void 0,it),S=(i,...e)=>{const t=i.length===1?i[0]:e.reduce((s,o,a)=>s+(n=>{if(n._$cssResult$===!0)return n.cssText;if(typeof n=="number")return n;throw Error("Value passed to 'css' function must be a 'css' function result: "+n+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(o)+i[a+1],i[0]);return new as(t,i,it)},ci=(i,e)=>{if(ot)i.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const s=document.createElement("style"),o=Ie.litNonce;o!==void 0&&s.setAttribute("nonce",o),s.textContent=t.cssText,i.appendChild(s)}},ns=ot?i=>i:i=>i instanceof CSSStyleSheet?(e=>{let t="";for(const s of e.cssRules)t+=s.cssText;return li(t)})(i):i;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:di,defineProperty:ui,getOwnPropertyDescriptor:hi,getOwnPropertyNames:pi,getOwnPropertySymbols:fi,getPrototypeOf:bi}=Object,J=globalThis,rs=J.trustedTypes,gi=rs?rs.emptyScript:"",at=J.reactiveElementPolyfillSupport,ye=(i,e)=>i,nt={toAttribute(i,e){switch(e){case Boolean:i=i?gi:null;break;case Object:case Array:i=i==null?i:JSON.stringify(i)}return i},fromAttribute(i,e){let t=i;switch(e){case Boolean:t=i!==null;break;case Number:t=i===null?null:Number(i);break;case Object:case Array:try{t=JSON.parse(i)}catch{t=null}}return t}},ls=(i,e)=>!di(i,e),cs={attribute:!0,type:String,converter:nt,reflect:!1,hasChanged:ls};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),J.litPropertyMetadata??(J.litPropertyMetadata=new WeakMap);class de extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=cs){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const s=Symbol(),o=this.getPropertyDescriptor(e,s,t);o!==void 0&&ui(this.prototype,e,o)}}static getPropertyDescriptor(e,t,s){const{get:o,set:a}=hi(this.prototype,e)??{get(){return this[t]},set(n){this[t]=n}};return{get(){return o==null?void 0:o.call(this)},set(n){const r=o==null?void 0:o.call(this);a.call(this,n),this.requestUpdate(e,r,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??cs}static _$Ei(){if(this.hasOwnProperty(ye("elementProperties")))return;const e=bi(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(ye("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(ye("properties"))){const t=this.properties,s=[...pi(t),...fi(t)];for(const o of s)this.createProperty(o,t[o])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[s,o]of t)this.elementProperties.set(s,o)}this._$Eh=new Map;for(const[t,s]of this.elementProperties){const o=this._$Eu(t,s);o!==void 0&&this._$Eh.set(o,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const s=new Set(e.flat(1/0).reverse());for(const o of s)t.unshift(ns(o))}else e!==void 0&&t.push(ns(e));return t}static _$Eu(e,t){const s=t.attribute;return s===!1?void 0:typeof s=="string"?s:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const s of t.keys())this.hasOwnProperty(s)&&(e.set(s,this[s]),delete this[s]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return ci(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var s;return(s=t.hostConnected)==null?void 0:s.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var s;return(s=t.hostDisconnected)==null?void 0:s.call(t)})}attributeChangedCallback(e,t,s){this._$AK(e,s)}_$EC(e,t){var a;const s=this.constructor.elementProperties.get(e),o=this.constructor._$Eu(e,s);if(o!==void 0&&s.reflect===!0){const n=(((a=s.converter)==null?void 0:a.toAttribute)!==void 0?s.converter:nt).toAttribute(t,s.type);this._$Em=e,n==null?this.removeAttribute(o):this.setAttribute(o,n),this._$Em=null}}_$AK(e,t){var a;const s=this.constructor,o=s._$Eh.get(e);if(o!==void 0&&this._$Em!==o){const n=s.getPropertyOptions(o),r=typeof n.converter=="function"?{fromAttribute:n.converter}:((a=n.converter)==null?void 0:a.fromAttribute)!==void 0?n.converter:nt;this._$Em=o,this[o]=r.fromAttribute(t,n.type),this._$Em=null}}requestUpdate(e,t,s){if(e!==void 0){if(s??(s=this.constructor.getPropertyOptions(e)),!(s.hasChanged??ls)(this[e],t))return;this.P(e,t,s)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,s){this._$AL.has(e)||this._$AL.set(e,t),s.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var s;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,n]of this._$Ep)this[a]=n;this._$Ep=void 0}const o=this.constructor.elementProperties;if(o.size>0)for(const[a,n]of o)n.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],n)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(s=this._$EO)==null||s.forEach(o=>{var a;return(a=o.hostUpdate)==null?void 0:a.call(o)}),this.update(t)):this._$EU()}catch(o){throw e=!1,this._$EU(),o}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(s=>{var o;return(o=s.hostUpdated)==null?void 0:o.call(s)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}}de.elementStyles=[],de.shadowRootOptions={mode:"open"},de[ye("elementProperties")]=new Map,de[ye("finalized")]=new Map,at==null||at({ReactiveElement:de}),(J.reactiveElementVersions??(J.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */let U=class extends de{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=ri(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return q}};U._$litElement$=!0,U.finalized=!0,(To=globalThis.litElementHydrateSupport)==null||To.call(globalThis,{LitElement:U});const rt=globalThis.litElementPolyfillSupport;rt==null||rt({LitElement:U}),(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.1.1");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const lt={ATTRIBUTE:1,CHILD:2},ct=i=>(...e)=>({_$litDirective$:i,values:e});let dt=class{constructor(e){}get _$AU(){return this._$AM._$AU}_$AT(e,t,s){this._$Ct=e,this._$AM=t,this._$Ci=s}_$AS(e,t){return this.update(e,t)}update(e,t){return this.render(...t)}};/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const z=ct(class extends dt{constructor(i){var e;if(super(i),i.type!==lt.ATTRIBUTE||i.name!=="class"||((e=i.strings)==null?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(i){return" "+Object.keys(i).filter(e=>i[e]).join(" ")+" "}update(i,[e]){var s,o;if(this.st===void 0){this.st=new Set,i.strings!==void 0&&(this.nt=new Set(i.strings.join(" ").split(/\s/).filter(a=>a!=="")));for(const a in e)e[a]&&!((s=this.nt)!=null&&s.has(a))&&this.st.add(a);return this.render(e)}const t=i.element.classList;for(const a of this.st)a in e||(t.remove(a),this.st.delete(a));for(const a in e){const n=!!e[a];n===this.st.has(a)||(o=this.nt)!=null&&o.has(a)||(n?(t.add(a),this.st.add(a)):(t.remove(a),this.st.delete(a)))}return q}});/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ds="important",mi=" !"+ds,Q=ct(class extends dt{constructor(i){var e;if(super(i),i.type!==lt.ATTRIBUTE||i.name!=="style"||((e=i.strings)==null?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(i){return Object.keys(i).reduce((e,t)=>{const s=i[t];return s==null?e:e+`${t=t.includes("-")?t:t.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${s};`},"")}update(i,[e]){const{style:t}=i.element;if(this.ft===void 0)return this.ft=new Set(Object.keys(e)),this.render(e);for(const s of this.ft)e[s]==null&&(this.ft.delete(s),s.includes("-")?t.removeProperty(s):t[s]=null);for(const s in e){const o=e[s];if(o!=null){this.ft.add(s);const a=typeof o=="string"&&o.endsWith(mi);s.includes("-")||a?t.setProperty(s,a?o.slice(0,-11):o,a?ds:""):t[s]=o}}return q}});/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ut="lit-localize-status";/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const h=(i,...e)=>({strTag:!0,strings:i,values:e}),vi=i=>typeof i!="string"&&"strTag"in i,us=(i,e,t)=>{let s=i[0];for(let o=1;o<i.length;o++)s+=e[t?t[o-1]:o-1],s+=i[o];return s};/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const hs=i=>vi(i)?us(i.strings,i.values):i;let T=hs,ps=!1;function yi(i){if(ps)throw new Error("lit-localize can only be configured once");T=i,ps=!0}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class wi{constructor(e){this.__litLocalizeEventHandler=t=>{t.detail.status==="ready"&&this.host.requestUpdate()},this.host=e}hostConnected(){window.addEventListener(ut,this.__litLocalizeEventHandler)}hostDisconnected(){window.removeEventListener(ut,this.__litLocalizeEventHandler)}}const _i=i=>i.addController(new wi(i));/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class fs{constructor(){this.settled=!1,this.promise=new Promise((e,t)=>{this._resolve=e,this._reject=t})}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const V=[];for(let i=0;i<256;i++)V[i]=(i>>4&15).toString(16)+(i&15).toString(16);function $i(i){let e=0,t=8997,s=0,o=33826,a=0,n=40164,r=0,l=52210;for(let u=0;u<i.length;u++)t^=i.charCodeAt(u),e=t*435,s=o*435,a=n*435,r=l*435,a+=t<<8,r+=o<<8,s+=e>>>16,t=e&65535,a+=s>>>16,o=s&65535,l=r+(a>>>16)&65535,n=a&65535;return V[l>>8]+V[l&255]+V[n>>8]+V[n&255]+V[o>>8]+V[o&255]+V[t>>8]+V[t&255]}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const xi="",ki="h",Si="s";function Ei(i,e){return(e?ki:Si)+$i(typeof i=="string"?i:i.join(xi))}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const bs=new WeakMap,gs=new Map;function Ci(i,e,t){if(i){const s=(t==null?void 0:t.id)??Ti(e),o=i[s];if(o){if(typeof o=="string")return o;if("strTag"in o)return us(o.strings,e.values,o.values);{let a=bs.get(o);return a===void 0&&(a=o.values,bs.set(o,a)),{...o,values:a.map(n=>e.values[n])}}}}return hs(e)}function Ti(i){const e=typeof i=="string"?i:i.strings;let t=gs.get(e);return t===void 0&&(t=Ei(e,typeof i!="string"&&!("strTag"in i)),gs.set(e,t)),t}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function ht(i){window.dispatchEvent(new CustomEvent(ut,{detail:i}))}let Me="",pt,ms,je,ft,vs,ie=new fs;ie.resolve();let De=0;const Ai=i=>(yi((e,t)=>Ci(vs,e,t)),Me=ms=i.sourceLocale,je=new Set(i.targetLocales),je.add(i.sourceLocale),ft=i.loadLocale,{getLocale:Oi,setLocale:Li}),Oi=()=>Me,Li=i=>{if(i===(pt??Me))return ie.promise;if(!je||!ft)throw new Error("Internal error");if(!je.has(i))throw new Error("Invalid locale code");De++;const e=De;return pt=i,ie.settled&&(ie=new fs),ht({status:"loading",loadingLocale:i}),(i===ms?Promise.resolve({templates:void 0}):ft(i)).then(s=>{De===e&&(Me=i,pt=void 0,vs=s.templates,ht({status:"ready",readyLocale:i}),ie.resolve())},s=>{De===e&&(ht({status:"error",errorLocale:i,errorMessage:s.toString()}),ie.reject(s))}),ie.promise},Pi=(i,e,t)=>{const s=i[e];return s?typeof s=="function"?s():Promise.resolve(s):new Promise((o,a)=>{(typeof queueMicrotask=="function"?queueMicrotask:setTimeout)(a.bind(null,new Error("Unknown variable dynamic import: "+e+(e.split("/").length!==t?". Note that variables only represent file names one level deep.":""))))})},Ii="en",Mi=["am_ET","ar","ar_MA","bg_BG","bn_BD","bs_BA","cs","de_DE","el","en_US","es_419","es_ES","fa_IR","fr_FR","hi_IN","hr","hu_HU","id_ID","it_IT","ja","ko_KR","mk_MK","mr","my_MM","ne_NP","nl_NL","pa_IN","pl","pt_BR","ro_RO","ru_RU","sl_SI","sr_BA","sw","th","tl","tr_TR","uk","vi","zh_CN","zh_TW"],{setLocale:ji}=Ai({sourceLocale:Ii,targetLocales:Mi,loadLocale:i=>Pi(Object.assign({"./generated/am_ET.js":()=>Promise.resolve().then(()=>qa),"./generated/ar.js":()=>Promise.resolve().then(()=>Ha),"./generated/ar_MA.js":()=>Promise.resolve().then(()=>Ga),"./generated/bg_BG.js":()=>Promise.resolve().then(()=>Za),"./generated/bn_BD.js":()=>Promise.resolve().then(()=>Qa),"./generated/bs_BA.js":()=>Promise.resolve().then(()=>Ya),"./generated/cs.js":()=>Promise.resolve().then(()=>tn),"./generated/de_DE.js":()=>Promise.resolve().then(()=>on),"./generated/el.js":()=>Promise.resolve().then(()=>nn),"./generated/en_US.js":()=>Promise.resolve().then(()=>ln),"./generated/es-419.js":()=>Promise.resolve().then(()=>dn),"./generated/es_419.js":()=>Promise.resolve().then(()=>hn),"./generated/es_ES.js":()=>Promise.resolve().then(()=>fn),"./generated/fa_IR.js":()=>Promise.resolve().then(()=>gn),"./generated/fr_FR.js":()=>Promise.resolve().then(()=>vn),"./generated/hi_IN.js":()=>Promise.resolve().then(()=>wn),"./generated/hr.js":()=>Promise.resolve().then(()=>$n),"./generated/hu_HU.js":()=>Promise.resolve().then(()=>kn),"./generated/id_ID.js":()=>Promise.resolve().then(()=>En),"./generated/it_IT.js":()=>Promise.resolve().then(()=>Tn),"./generated/ja.js":()=>Promise.resolve().then(()=>On),"./generated/ko_KR.js":()=>Promise.resolve().then(()=>Pn),"./generated/mk_MK.js":()=>Promise.resolve().then(()=>Mn),"./generated/mr.js":()=>Promise.resolve().then(()=>Dn),"./generated/my_MM.js":()=>Promise.resolve().then(()=>Nn),"./generated/ne_NP.js":()=>Promise.resolve().then(()=>Fn),"./generated/nl_NL.js":()=>Promise.resolve().then(()=>Bn),"./generated/pa_IN.js":()=>Promise.resolve().then(()=>Vn),"./generated/pl.js":()=>Promise.resolve().then(()=>Wn),"./generated/pt_BR.js":()=>Promise.resolve().then(()=>Kn),"./generated/ro_RO.js":()=>Promise.resolve().then(()=>Jn),"./generated/ru_RU.js":()=>Promise.resolve().then(()=>Xn),"./generated/sl_SI.js":()=>Promise.resolve().then(()=>er),"./generated/sr_BA.js":()=>Promise.resolve().then(()=>sr),"./generated/sw.js":()=>Promise.resolve().then(()=>ir),"./generated/th.js":()=>Promise.resolve().then(()=>nr),"./generated/tl.js":()=>Promise.resolve().then(()=>lr),"./generated/tr_TR.js":()=>Promise.resolve().then(()=>dr),"./generated/uk.js":()=>Promise.resolve().then(()=>hr),"./generated/vi.js":()=>Promise.resolve().then(()=>fr),"./generated/zh_CN.js":()=>Promise.resolve().then(()=>gr),"./generated/zh_TW.js":()=>Promise.resolve().then(()=>vr)}),`./generated/${i}.js`,3)});class ze{constructor(e,t="/wp-json"){this.nonce=e,this.apiRoot=t.endsWith("/")?`${t}`:`${t} + "/"`,this.apiRoot=`/${t}/`.replace(/\/\//g,"/")}async makeRequest(e,t,s,o="dt/v1/"){let a=o;!a.endsWith("/")&&!t.startsWith("/")&&(a+="/");const n=t.startsWith("http")?t:`${this.apiRoot}${a}${t}`,r={method:e,credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce}};e!=="GET"&&(r.body=JSON.stringify(s));const l=await fetch(n,r),u=await l.json();if(!l.ok){const b=new Error((u==null?void 0:u.message)||u.toString());throw b.args={status:l.status,statusText:l.statusText,body:u},b}return u}async makeRequestOnPosts(e,t,s={}){return this.makeRequest(e,t,s,"dt-posts/v2/")}async getPost(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}`)}async createPost(e,t){return this.makeRequestOnPosts("POST",e,t)}async fetchPostsList(e,t){return this.makeRequestOnPosts("POST",`${e}/list`,t)}async updatePost(e,t,s){return this.makeRequestOnPosts("POST",`${e}/${t}`,s)}async deletePost(e,t){return this.makeRequestOnPosts("DELETE",`${e}/${t}`)}async listPostsCompact(e,t=""){const s=new URLSearchParams({s:t});return this.makeRequestOnPosts("GET",`${e}/compact?${s}`)}async getPostDuplicates(e,t,s){return this.makeRequestOnPosts("GET",`${e}/${t}/all_duplicates`,s)}async checkFieldValueExists(e,t){return this.makeRequestOnPosts("POST",`${e}/check_field_value_exists`,t)}async getMultiSelectValues(e,t,s=""){const o=new URLSearchParams({s,field:t});return this.makeRequestOnPosts("GET",`${e}/multi-select-values?${o}`)}async transferContact(e,t){return this.makeRequestOnPosts("POST","contacts/transfer",{contact_id:e,site_post_id:t})}async transferContactSummaryUpdate(e,t){return this.makeRequestOnPosts("POST","contacts/transfer/summary/send-update",{contact_id:e,update:t})}async requestRecordAccess(e,t,s){return this.makeRequestOnPosts("POST",`${e}/${t}/request_record_access`,{user_id:s})}async createComment(e,t,s,o="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments`,{comment:s,comment_type:o})}async updateComment(e,t,s,o,a="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${s}`,{comment:o,comment_type:a})}async deleteComment(e,t,s){return this.makeRequestOnPosts("DELETE",`${e}/${t}/comments/${s}`)}async getComments(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/comments`)}async toggle_comment_reaction(e,t,s,o,a){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${s}/react`,{user_id:o,reaction:a})}async getPostActivity(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/activity`)}async getSingleActivity(e,t,s){return this.makeRequestOnPosts("GET",`${e}/${t}/activity/${s}`)}async revertActivity(e,t,s){return this.makeRequestOnPosts("GET",`${e}/${t}/revert/${s}`)}async getPostShares(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/shares`)}async addPostShare(e,t,s){return this.makeRequestOnPosts("POST",`${e}/${t}/shares`,{user_id:s})}async removePostShare(e,t,s){return this.makeRequestOnPosts("DELETE",`${e}/${t}/shares`,{user_id:s})}async getFilters(){return this.makeRequest("GET","users/get_filters")}async saveFilters(e,t){return this.makeRequest("POST","users/save_filters",{filter:t,postType:e})}async deleteFilter(e,t){return this.makeRequest("DELETE","users/save_filters",{id:t,postType:e})}async searchUsers(e="",t){const s=new URLSearchParams({s:e});return this.makeRequest("GET",`users/get_users?${s}&post_type=${t}`)}async checkDuplicateUsers(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/duplicates`)}async getContactInfo(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/`)}async createUser(e){return this.makeRequest("POST","users/create",e)}async advanced_search(e,t,s,o){return this.makeRequest("GET","advanced_search",{query:e,postType:t,offset:s,post:o.post,comment:o.comment,meta:o.meta,status:o.status},"dt-posts/v2/posts/search/")}}(function(){(function(i){const e=new WeakMap,t=new WeakMap,s=new WeakMap,o=new WeakMap,a=new WeakMap,n=new WeakMap,r=new WeakMap,l=new WeakMap,u=new WeakMap,b=new WeakMap,g=new WeakMap,v=new WeakMap,y=new WeakMap,_=new WeakMap,O=new WeakMap,R={ariaAtomic:"aria-atomic",ariaAutoComplete:"aria-autocomplete",ariaBusy:"aria-busy",ariaChecked:"aria-checked",ariaColCount:"aria-colcount",ariaColIndex:"aria-colindex",ariaColIndexText:"aria-colindextext",ariaColSpan:"aria-colspan",ariaCurrent:"aria-current",ariaDescription:"aria-description",ariaDisabled:"aria-disabled",ariaExpanded:"aria-expanded",ariaHasPopup:"aria-haspopup",ariaHidden:"aria-hidden",ariaInvalid:"aria-invalid",ariaKeyShortcuts:"aria-keyshortcuts",ariaLabel:"aria-label",ariaLevel:"aria-level",ariaLive:"aria-live",ariaModal:"aria-modal",ariaMultiLine:"aria-multiline",ariaMultiSelectable:"aria-multiselectable",ariaOrientation:"aria-orientation",ariaPlaceholder:"aria-placeholder",ariaPosInSet:"aria-posinset",ariaPressed:"aria-pressed",ariaReadOnly:"aria-readonly",ariaRelevant:"aria-relevant",ariaRequired:"aria-required",ariaRoleDescription:"aria-roledescription",ariaRowCount:"aria-rowcount",ariaRowIndex:"aria-rowindex",ariaRowIndexText:"aria-rowindextext",ariaRowSpan:"aria-rowspan",ariaSelected:"aria-selected",ariaSetSize:"aria-setsize",ariaSort:"aria-sort",ariaValueMax:"aria-valuemax",ariaValueMin:"aria-valuemin",ariaValueNow:"aria-valuenow",ariaValueText:"aria-valuetext",role:"role"},I=(p,c)=>{for(let f in R){c[f]=null;let m=null;const w=R[f];Object.defineProperty(c,f,{get(){return m},set(x){m=x,p.isConnected?P(p,w,x):b.set(p,c)}})}};function L(p){const c=o.get(p),{form:f}=c;Po(p,f,c),Lo(p,c.labels)}const Ae=(p,c=!1)=>{const f=document.createTreeWalker(p,NodeFilter.SHOW_ELEMENT,{acceptNode(x){return o.has(x)?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let m=f.nextNode();const w=!c||p.disabled;for(;m;)m.formDisabledCallback&&w&&zt(m,p.disabled),m=f.nextNode()},Ge={attributes:!0,attributeFilter:["disabled","name"]},Y=Je()?new MutationObserver(p=>{for(const c of p){const f=c.target;if(c.attributeName==="disabled"&&(f.constructor.formAssociated?zt(f,f.hasAttribute("disabled")):f.localName==="fieldset"&&Ae(f)),c.attributeName==="name"&&f.constructor.formAssociated){const m=o.get(f),w=u.get(f);m.setFormValue(w)}}}):{};function E(p){p.forEach(c=>{const{addedNodes:f,removedNodes:m}=c,w=Array.from(f),x=Array.from(m);w.forEach(k=>{var M;if(o.has(k)&&k.constructor.formAssociated&&L(k),b.has(k)){const A=b.get(k);Object.keys(R).filter(B=>A[B]!==null).forEach(B=>{P(k,R[B],A[B])}),b.delete(k)}if(O.has(k)){const A=O.get(k);P(k,"internals-valid",A.validity.valid.toString()),P(k,"internals-invalid",(!A.validity.valid).toString()),P(k,"aria-invalid",(!A.validity.valid).toString()),O.delete(k)}if(k.localName==="form"){const A=l.get(k),W=document.createTreeWalker(k,NodeFilter.SHOW_ELEMENT,{acceptNode(Ut){return o.has(Ut)&&Ut.constructor.formAssociated&&!(A&&A.has(Ut))?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let B=W.nextNode();for(;B;)L(B),B=W.nextNode()}k.localName==="fieldset"&&((M=Y.observe)===null||M===void 0||M.call(Y,k,Ge),Ae(k,!0))}),x.forEach(k=>{const M=o.get(k);M&&s.get(M)&&Ao(M),r.has(k)&&r.get(k).disconnect()})})}function D(p){p.forEach(c=>{const{removedNodes:f}=c;f.forEach(m=>{const w=y.get(c.target);o.has(m)&&Mo(m),w.disconnect()})})}const re=p=>{var c,f;const m=new MutationObserver(D);!((c=window==null?void 0:window.ShadyDOM)===null||c===void 0)&&c.inUse&&p.mode&&p.host&&(p=p.host),(f=m.observe)===null||f===void 0||f.call(m,p,{childList:!0}),y.set(p,m)};Je()&&new MutationObserver(E);const ee={childList:!0,subtree:!0},P=(p,c,f)=>{p.getAttribute(c)!==f&&p.setAttribute(c,f)},zt=(p,c)=>{p.toggleAttribute("internals-disabled",c),c?P(p,"aria-disabled","true"):p.removeAttribute("aria-disabled"),p.formDisabledCallback&&p.formDisabledCallback.apply(p,[c])},Ao=p=>{s.get(p).forEach(f=>{f.remove()}),s.set(p,[])},Oo=(p,c)=>{const f=document.createElement("input");return f.type="hidden",f.name=p.getAttribute("name"),p.after(f),s.get(c).push(f),f},yr=(p,c)=>{var f;s.set(c,[]),(f=Y.observe)===null||f===void 0||f.call(Y,p,Ge)},Lo=(p,c)=>{if(c.length){Array.from(c).forEach(m=>m.addEventListener("click",p.click.bind(p)));let f=c[0].id;c[0].id||(f=`${c[0].htmlFor}_Label`,c[0].id=f),P(p,"aria-labelledby",f)}},Ke=p=>{const c=Array.from(p.elements).filter(x=>!x.tagName.includes("-")&&x.validity).map(x=>x.validity.valid),f=l.get(p)||[],m=Array.from(f).filter(x=>x.isConnected).map(x=>o.get(x).validity.valid),w=[...c,...m].includes(!1);p.toggleAttribute("internals-invalid",w),p.toggleAttribute("internals-valid",!w)},wr=p=>{Ke(Ze(p.target))},_r=p=>{Ke(Ze(p.target))},$r=p=>{const c=["button[type=submit]","input[type=submit]","button:not([type])"].map(f=>`${f}:not([disabled])`).map(f=>`${f}:not([form])${p.id?`,${f}[form='${p.id}']`:""}`).join(",");p.addEventListener("click",f=>{if(f.target.closest(c)){const w=l.get(p);if(p.noValidate)return;w.size&&Array.from(w).reverse().map(M=>o.get(M).reportValidity()).includes(!1)&&f.preventDefault()}})},xr=p=>{const c=l.get(p.target);c&&c.size&&c.forEach(f=>{f.constructor.formAssociated&&f.formResetCallback&&f.formResetCallback.apply(f)})},Po=(p,c,f)=>{if(c){const m=l.get(c);if(m)m.add(p);else{const w=new Set;w.add(p),l.set(c,w),$r(c),c.addEventListener("reset",xr),c.addEventListener("input",wr),c.addEventListener("change",_r)}n.set(c,{ref:p,internals:f}),p.constructor.formAssociated&&p.formAssociatedCallback&&setTimeout(()=>{p.formAssociatedCallback.apply(p,[c])},0),Ke(c)}},Ze=p=>{let c=p.parentNode;return c&&c.tagName!=="FORM"&&(c=Ze(c)),c},H=(p,c,f=DOMException)=>{if(!p.constructor.formAssociated)throw new f(c)},Io=(p,c,f)=>{const m=l.get(p);return m&&m.size&&m.forEach(w=>{o.get(w)[f]()||(c=!1)}),c},Mo=p=>{if(p.constructor.formAssociated){const c=o.get(p),{labels:f,form:m}=c;Lo(p,f),Po(p,m,c)}};function Je(){return typeof MutationObserver<"u"}class kr{constructor(){this.badInput=!1,this.customError=!1,this.patternMismatch=!1,this.rangeOverflow=!1,this.rangeUnderflow=!1,this.stepMismatch=!1,this.tooLong=!1,this.tooShort=!1,this.typeMismatch=!1,this.valid=!0,this.valueMissing=!1,Object.seal(this)}}const Sr=p=>(p.badInput=!1,p.customError=!1,p.patternMismatch=!1,p.rangeOverflow=!1,p.rangeUnderflow=!1,p.stepMismatch=!1,p.tooLong=!1,p.tooShort=!1,p.typeMismatch=!1,p.valid=!0,p.valueMissing=!1,p),Er=(p,c,f)=>(p.valid=Cr(c),Object.keys(c).forEach(m=>p[m]=c[m]),f&&Ke(f),p),Cr=p=>{let c=!0;for(let f in p)f!=="valid"&&p[f]!==!1&&(c=!1);return c},Nt=new WeakMap;function jo(p,c){p.toggleAttribute(c,!0),p.part&&p.part.add(c)}class Rt extends Set{static get isPolyfilled(){return!0}constructor(c){if(super(),!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");Nt.set(this,c)}add(c){if(!/^--/.test(c)||typeof c!="string")throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${c} must start with '--'.`);const f=super.add(c),m=Nt.get(this),w=`state${c}`;return m.isConnected?jo(m,w):setTimeout(()=>{jo(m,w)}),f}clear(){for(let[c]of this.entries())this.delete(c);super.clear()}delete(c){const f=super.delete(c),m=Nt.get(this);return m.isConnected?(m.toggleAttribute(`state${c}`,!1),m.part&&m.part.remove(`state${c}`)):setTimeout(()=>{m.toggleAttribute(`state${c}`,!1),m.part&&m.part.remove(`state${c}`)}),f}}function Do(p,c,f,m){if(typeof c=="function"?p!==c||!0:!c.has(p))throw new TypeError("Cannot read private member from an object whose class did not declare it");return f==="m"?m:f==="a"?m.call(p):m?m.value:c.get(p)}function Tr(p,c,f,m,w){if(typeof c=="function"?p!==c||!0:!c.has(p))throw new TypeError("Cannot write private member to an object whose class did not declare it");return c.set(p,f),f}var Oe;class Ar{constructor(c){Oe.set(this,void 0),Tr(this,Oe,c);for(let f=0;f<c.length;f++){let m=c[f];this[f]=m,m.hasAttribute("name")&&(this[m.getAttribute("name")]=m)}Object.freeze(this)}get length(){return Do(this,Oe,"f").length}[(Oe=new WeakMap,Symbol.iterator)](){return Do(this,Oe,"f")[Symbol.iterator]()}item(c){return this[c]==null?null:this[c]}namedItem(c){return this[c]==null?null:this[c]}}function Or(){const p=HTMLFormElement.prototype.checkValidity;HTMLFormElement.prototype.checkValidity=f;const c=HTMLFormElement.prototype.reportValidity;HTMLFormElement.prototype.reportValidity=m;function f(...x){let k=p.apply(this,x);return Io(this,k,"checkValidity")}function m(...x){let k=c.apply(this,x);return Io(this,k,"reportValidity")}const{get:w}=Object.getOwnPropertyDescriptor(HTMLFormElement.prototype,"elements");Object.defineProperty(HTMLFormElement.prototype,"elements",{get(...x){const k=w.call(this,...x),M=Array.from(l.get(this)||[]);if(M.length===0)return k;const A=Array.from(k).concat(M).sort((W,B)=>W.compareDocumentPosition?W.compareDocumentPosition(B)&2?1:-1:0);return new Ar(A)}})}class zo{static get isPolyfilled(){return!0}constructor(c){if(!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");const f=c.getRootNode(),m=new kr;this.states=new Rt(c),e.set(this,c),t.set(this,m),o.set(c,this),I(c,this),yr(c,this),Object.seal(this),f instanceof DocumentFragment&&re(f)}checkValidity(){const c=e.get(this);if(H(c,"Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=t.get(this);if(!f.valid){const m=new Event("invalid",{bubbles:!1,cancelable:!0,composed:!1});c.dispatchEvent(m)}return f.valid}get form(){const c=e.get(this);H(c,"Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");let f;return c.constructor.formAssociated===!0&&(f=Ze(c)),f}get labels(){const c=e.get(this);H(c,"Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");const f=c.getAttribute("id"),m=c.getRootNode();return m&&f?m.querySelectorAll(`[for="${f}"]`):[]}reportValidity(){const c=e.get(this);if(H(c,"Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const f=this.checkValidity(),m=v.get(this);if(m&&!c.constructor.formAssociated)throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");return!f&&m&&(c.focus(),m.focus()),f}setFormValue(c){const f=e.get(this);if(H(f,"Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."),Ao(this),c!=null&&!(c instanceof FormData)){if(f.getAttribute("name")){const m=Oo(f,this);m.value=c}}else c!=null&&c instanceof FormData&&Array.from(c).reverse().forEach(([m,w])=>{if(typeof w=="string"){const x=Oo(f,this);x.name=m,x.value=w}});u.set(f,c)}setValidity(c,f,m){const w=e.get(this);if(H(w,"Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!c)throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");v.set(this,m);const x=t.get(this),k={};for(const W in c)k[W]=c[W];Object.keys(k).length===0&&Sr(x);const M=Object.assign(Object.assign({},x),k);delete M.valid;const{valid:A}=Er(x,M,this.form);if(!A&&!f)throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");a.set(this,A?"":f),w.isConnected?(w.toggleAttribute("internals-invalid",!A),w.toggleAttribute("internals-valid",A),P(w,"aria-invalid",`${!A}`)):O.set(w,this)}get shadowRoot(){const c=e.get(this),f=g.get(c);return f||null}get validationMessage(){const c=e.get(this);return H(c,"Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."),a.get(this)}get validity(){const c=e.get(this);return H(c,"Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."),t.get(this)}get willValidate(){const c=e.get(this);return H(c,"Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."),!(c.disabled||c.hasAttribute("disabled")||c.hasAttribute("readonly"))}}function Lr(){if(typeof window>"u"||!window.ElementInternals||!HTMLElement.prototype.attachInternals)return!1;class p extends HTMLElement{constructor(){super(),this.internals=this.attachInternals()}}const c=`element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g,"")}`;customElements.define(c,p);const f=new p;return["shadowRoot","form","willValidate","validity","validationMessage","labels","setFormValue","setValidity","checkValidity","reportValidity"].every(m=>m in f.internals)}let No=!1,Ro=!1;function Ft(p){Ro||(Ro=!0,window.CustomStateSet=Rt,p&&(HTMLElement.prototype.attachInternals=function(...c){const f=p.call(this,c);return f.states=new Rt(this),f}))}function Fo(p=!0){if(!No){if(No=!0,typeof window<"u"&&(window.ElementInternals=zo),typeof CustomElementRegistry<"u"){const c=CustomElementRegistry.prototype.define;CustomElementRegistry.prototype.define=function(f,m,w){if(m.formAssociated){const x=m.prototype.connectedCallback;m.prototype.connectedCallback=function(){_.has(this)||(_.set(this,!0),this.hasAttribute("disabled")&&zt(this,!0)),x!=null&&x.apply(this),Mo(this)}}c.call(this,f,m,w)}}if(typeof HTMLElement<"u"&&(HTMLElement.prototype.attachInternals=function(){if(this.tagName){if(this.tagName.indexOf("-")===-1)throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.")}else return{};if(o.has(this))throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");return new zo(this)}),typeof Element<"u"){let c=function(...m){const w=f.apply(this,m);if(g.set(this,w),Je()){const x=new MutationObserver(E);window.ShadyDOM?x.observe(this,ee):x.observe(w,ee),r.set(this,x)}return w};const f=Element.prototype.attachShadow;Element.prototype.attachShadow=c}Je()&&typeof document<"u"&&new MutationObserver(E).observe(document.documentElement,ee),typeof HTMLFormElement<"u"&&Or(),(p||typeof window<"u"&&!window.CustomStateSet)&&Ft()}}return!!customElements.polyfillWrapFlushCallback||(Lr()?typeof window<"u"&&!window.CustomStateSet&&Ft(HTMLElement.prototype.attachInternals):Fo(!1)),i.forceCustomStateSetPolyfill=Ft,i.forceElementInternalsPolyfill=Fo,Object.defineProperty(i,"__esModule",{value:!0}),i})({})})();class N extends U{static get properties(){return{RTL:{type:Boolean},locale:{type:String},apiRoot:{type:String,reflect:!1},postType:{type:String,reflect:!1},postID:{type:String,reflect:!1}}}get _focusTarget(){return this.shadowRoot.children[0]instanceof Element?this.shadowRoot.children[0]:null}constructor(){super(),_i(this),this.addEventListener("focus",this._proxyFocus.bind(this))}connectedCallback(){super.connectedCallback(),this.apiRoot=this.apiRoot?`${this.apiRoot}/`.replace("//","/"):"/",this.api=new ze(this.nonce,this.apiRoot)}willUpdate(e){if(this.RTL===void 0){const t=this.closest("[dir]");if(t){const s=t.getAttribute("dir");s&&(this.RTL=s.toLowerCase()==="rtl")}}if(!this.locale){const t=this.closest("[lang]");if(t){const s=t.getAttribute("lang");s&&(this.locale=s)}}if(e&&e.has("locale")&&this.locale)try{ji(this.locale)}catch(t){console.error(t)}}_proxyFocus(){this._focusTarget&&this._focusTarget.focus()}}class ys extends N{static get styles(){return S`
      :host {
        display: inline-flex;
        width: fit-content;
        height: fit-content;
      }

      .dt-button {
        cursor: pointer;
        display: flex;
        margin: var(--dt-button-margin, 5px);
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
        --dt-button-context-border-color: var(--inactive-color);
        --dt-button-context-background-color: var(--inactive-color);
        --dt-button-context-text-color: var(--dt-button-text-color-light);
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
        --dt-button-context-border-color: var(--inactive-color);
      }

      .dt-button--disabled.dt-button--outline {
        --dt-button-context-border-color: var(--disabled-color);
      }

      .dt-button.dt-button--rounded {
        --dt-button-border-radius: 50%;
        --dt-button-padding-x: 0px;
        --dt-button-padding-y: 0px;
        --dt-button-aspect-ratio: var(--dt-button-rounded-aspect-ratio, 1/1);
      }

      .dt-button--custom {
        padding: var(--dt-button-padding-y, 7px)
          var(--dt-button-padding-x, 10px);
        font-size: var(--dt-button-font-size, 12px);
        font-weight: var(--dt-button-font-weight, 300);
        border-radius: var(--dt-button-border-radius, 5px);
      }

      .dt-button--star {
        --dt-button-background-color: transparent;
        --dt-button-border-color: transparent;
        padding: 0;
      }
      ::slotted(svg) {
        margin: 1.5em;
        vertical-align: middle !important;
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
    `}static get properties(){return{label:{type:String},context:{type:String},type:{type:String},outline:{type:Boolean},href:{type:String},title:{type:String},rounded:{type:Boolean},confirm:{type:String},buttonClass:{type:String},custom:{type:Boolean},favorite:{type:Boolean,reflect:!0},favorited:{type:String},listButton:{type:Boolean},buttonStyle:{type:Object}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--rounded":this.rounded,"dt-button--custom":this.custom},t=`dt-button--${this.context}`;return e[t]=!0,e}constructor(){super(),this.context="default",this.favorite=this.favorited||!1,this.listButton=!1}connectedCallback(){super.connectedCallback(),(this.id.startsWith("favorite")||this.id==="follow-button"||this.id==="following-button")&&window.addEventListener("load",async()=>{const e=await new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.id,postType:this.postType,onSuccess:t=>{switch(Object.keys(t).find(o=>["favorite","unfollow","follow"].includes(o))){case"favorite":{this.favorite=t.favorite?t.favorite:!1;const n=this.shadowRoot.querySelector("slot").assignedNodes({flatten:!0}).find(r=>r.nodeType===Node.ELEMENT_NODE&&r.classList.contains("icon-star"));this.favorite?n.classList.add("selected"):n.classList.remove("selected"),this.requestUpdate()}break;case"follow":this.following=!0,this.requestUpdate();break;case"unfollow":this.following=!1,this.requestUpdate();break;default:console.log("No matching Key found!");break}},onError:t=>{console.warn(t)}}});this.dispatchEvent(e)})}handleClick(e){if(e.preventDefault(),this.confirm&&!confirm(this.confirm)){e.preventDefault();return}if((this.id.startsWith("favorite")||this.id==="follow-button"||this.id==="following-button")&&(e.preventDefault(),this.onClick(e)),this.id==="create-post-button"){const t=this.closest("form");t?console.log("Form found",t):console.error("Form not found!");const s=new FormData(t),o={form:{},el:{type:"access"}};s.forEach((n,r)=>{o.form[r]=n}),Array.from(t.elements).forEach(n=>{if(n.localName.startsWith("dt-")&&n.value&&String(n.value).trim()!=="")if(n.localName.startsWith("dt-comm")){const r=n.value.map(l=>({value:l.value}));o.el[n.name]=r}else if(n.localName.startsWith("dt-multi")||n.localName.startsWith("dt-tags")){const r=n.value.map(l=>({value:l}));o.el[n.name]={values:r}}else if(n.localName.startsWith("dt-connection")){const r=n.value.map(l=>({value:l.label}));o.el[n.name]={values:r}}else o.el[n.name]=n.value});const a=new CustomEvent("send-data",{detail:{field:this.id,newValue:o}});this.dispatchEvent(a)}if(this.type==="submit"){const t=this.closest("form");t&&t.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0}))}}onClick(e){if(e.preventDefault(),e.stopPropagation(),this.listButton&&(this.favorite=this.favorited),this.id.startsWith("favorite")){const t=new CustomEvent("customClick",{detail:{field:this.id,toggleState:!this.favorite},bubbles:!0,composed:!0});this.favorite=!this.favorite;const a=this.shadowRoot.querySelector("slot").assignedNodes({flatten:!0}).find(n=>n.nodeType===Node.ELEMENT_NODE&&n.classList.contains("icon-star"));a&&(a.classList.contains("selected")?a.classList.remove("selected"):a.classList.add("selected")),this.dispatchEvent(t),this.requestUpdate()}else if(this.id==="follow-button"||this.id==="following-button"){const t=this.following,s=new CustomEvent("customClick",{detail:{field:this.id,toggleState:t}});this.id=this.id==="follow-button"?"following-button":"follow-button",this.label=this.label==="Follow"?"Following":"Follow",this.outline=!this.outline,this.following=!this.following,this.requestUpdate(),this.dispatchEvent(s)}}_getSVGIcon(){return this.id==="follow-button"||this.id==="following-button"?d`<svg
          xmlns="http://www.w3.org/2000/svg"
          width="1em"
          height="1em"
          viewBox="0 0 24 24"
        >
          <path
            fill="currentColor"
            d="M12 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5a5 5 0 0 1 5-5a5 5 0 0 1 5 5a5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5"
          />
        </svg>`:""}_dismiss(){this.hide=!0}render(){if(this.hide)return d``;const e={...this.classes},t=(this.id==="follow-button"||this.id==="following-button")&&this.label?this.label:d`<slot></slot>`;return this.href?d`
        <a
          id=${this.name}
          class=${z(e)}
          href=${this.href}
          title=${this.title}
          type=${this.type}
          @click=${this.handleClick}
        >
          <div>${t}${this._getSVGIcon()}</div>
        </a>
      `:d`
      <button
        class=${z(e)}
        title=${this.title}
        style=${Q(this.buttonStyle||{})}
        type=${this.type}
        .value=${this.value}
        @click=${this.handleClick}
      >
        <div>${t}${this._getSVGIcon()}</div>
      </button>
    `}}window.customElements.define("dt-button",ys);class ws extends N{static get styles(){return S`
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
            <h5>${T("Need more help?")}</h5>
            <a
              class="button small"
              id="docslink"
              href="https://disciple.tools/user-docs"
              target="_blank"
              >${T("Read the documentation")}</a
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
        class="dt-modal dt-modal--width ${z(this.headerClass||{})}"
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
              <slot name="close-button">${T("Close")}</slot>
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
        class="button small opener ${z(this.buttonClass||{})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${Q(this.buttonStyle||{})}
      >
      ${this.dropdownListImg?d`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">`:""}
      ${this.imageSrc?d`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${Q(this.imageStyle||{})}
                 />`:""}
      ${this.buttonLabel?d`${this.buttonLabel}`:""}
      </button>
      `}
    `}}window.customElements.define("dt-modal",ws);class _s extends U{static get styles(){return S`
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
    class=${z(this.classes)}
    style=${Q(this.buttonStyle||{})}
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
    `}_redirectToHref(e){let t=e;/^https?:\/\//i.test(t)||(t=`http://${t}`),window.open(t,"_blank")}_openDialog(e){const t=e.replace(/\s/g,"-").toLowerCase();document.querySelector(`#${t}`).shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}_handleHover(){const e=this.shadowRoot.querySelector("ul");e.style.display="block"}_handleMouseLeave(){const e=this.shadowRoot.querySelector("ul");e.style.display="none"}}window.customElements.define("dt-dropdown",_s);class Di extends N{static get styles(){return S`
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
    `}static get properties(){return{key:{type:String},metric:{type:Object},group:{type:Object},active:{type:Boolean,reflect:!0},missingIcon:{type:String},handleSave:{type:Function}}}render(){const{metric:e,active:t,missingIcon:s=`${window.wpApiShare.template_dir}/dt-assets/images/groups/missing.svg`}=this;return d`<div
      class=${z({"health-item":!0,"health-item--active":t})}
      title="${e.description}"
      @click="${this._handleClick}"
    >
      <img src="${e.icon?e.icon:s}" />
    </div>`}async _handleClick(){if(!this.handleSave)return;const e=!this.active;this.active=e;const t={health_metrics:{values:[{value:this.key,delete:!e}]}};try{await this.handleSave(this.group.ID,t)}catch(s){console.error(s);return}e?this.group.health_metrics.push(this.key):this.group.health_metrics.pop(this.key)}}window.customElements.define("dt-church-health-icon",Di);class $s extends N{static get styles(){return S`
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
    `}static get properties(){return{groupId:{type:Number},group:{type:Object,reflect:!1},settings:{type:Object,reflect:!1},errorMessage:{type:String,attribute:!1},missingIcon:{type:String},handleSave:{type:Function}}}get metrics(){const e=this.settings||[];return Object.values(e).length?Object.entries(e).filter(([s,o])=>s!=="church_commitment"):[]}get isCommited(){return!this.group||!this.group.health_metrics?!1:this.group.health_metrics.includes("church_commitment")}connectedCallback(){super.connectedCallback(),this.fetch()}adoptedCallback(){this.distributeItems()}updated(){this.distributeItems()}async fetch(){try{const e=[this.fetchSettings(),this.fetchGroup()];let[t,s]=await Promise.all(e);this.settings=t,this.post=s,t||(this.errorMessage="Error loading settings"),s||(this.errorMessage="Error loading group")}catch(e){console.error(e)}}fetchGroup(){if(this.group)return Promise.resolve(this.group);fetch(`/wp-json/dt-posts/v2/groups/${this.groupId}`).then(e=>e.json())}fetchSettings(){return this.settings?Promise.resolve(this.settings):fetch("/wp-json/dt-posts/v2/groups/settings").then(e=>e.json())}findMetric(e){const t=this.metrics.find(s=>s.key===e);return t?t.value:null}render(){if(!this.group||!this.metrics.length)return d`<dt-spinner></dt-spinner>`;const e=this.group.health_metrics||[];return this.errorMessage&&d`<dt-alert type="error">${this.errorMessage}</dt-alert>`,d`
      <div class="health-circle__wrapper">
        <div class="health-circle__container">
          <div
            class=${z({"health-circle":!0,"health-circle--committed":this.isCommited})}
          >
            <div class="health-circle__grid">
              ${this.metrics.map(([t,s],o)=>d`<dt-church-health-icon
                    key="${t}"
                    .group="${this.group}"
                    .metric=${s}
                    .active=${e.indexOf(t)!==-1}
                    .style="--i: ${o+1}"
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
    `}distributeItems(){const e=this.renderRoot.querySelector(".health-circle__container");let o=e.querySelectorAll("dt-church-health-icon").length,a=Math.tan(Math.PI/o);e.style.setProperty("--m",o),e.style.setProperty("--tan",+a.toFixed(2))}async toggleClick(e){const{handleSave:t}=this;if(!t)return;let s=this.renderRoot.querySelector("dt-toggle"),o=s.toggleAttribute("checked");this.group.health_metrics||(this.group.health_metrics=[]);const a={health_metrics:{values:[{value:"church_commitment",delete:!o}]}};try{await t(this.group.ID,a)}catch(n){s.toggleAttribute("checked",!o),console.error(n);return}o?this.group.health_metrics.push("church_commitment"):this.group.health_metrics.pop("church_commitment"),this.requestUpdate()}_isChecked(){return Object.hasOwn(this.group,"health_metrics")?this.group.health_metrics.includes("church_commitment")?this.isChurch=!0:this.isChurch=!1:this.isChurch=!1}}window.customElements.define("dt-church-health-circle",$s);class xs extends N{static get styles(){return S`
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
    `}static get properties(){return{icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String}}}firstUpdated(){const t=this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({flatten:!0});for(const s of t)s.style.height="100%",s.style.width="auto"}get _slottedChildren(){return this.shadowRoot.querySelector("slot").assignedElements({flatten:!0})}render(){const e=d`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;return d`
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
                >${this.privateLabel||T("Private Field: Only I can see its content")}</span
              >
            </span> `:null}
      </div>
    `}}window.customElements.define("dt-label",xs);class j extends N{static get formAssociated(){return!0}static get styles(){return[S`
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
      `]}static get properties(){return{...super.properties,name:{type:String},label:{type:String},icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String},disabled:{type:Boolean},required:{type:Boolean},requiredMessage:{type:String},touched:{type:Boolean,state:!0},invalid:{type:Boolean,state:!0},error:{type:String},loading:{type:Boolean},saved:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector("input, textarea, select")}get _focusTarget(){return this._field}constructor(){super(),this.touched=!1,this.invalid=!1,this.internals=this.attachInternals(),this.addEventListener("invalid",()=>{this.touched=!0,this._validateRequired()})}firstUpdated(...e){super.firstUpdated(...e);const t=j._jsonToFormData(this.value,this.name);this.internals.setFormValue(t),this._validateRequired()}static _buildFormData(e,t,s){if(t&&typeof t=="object"&&!(t instanceof Date)&&!(t instanceof File))Object.keys(t).forEach(o=>{this._buildFormData(e,t[o],s?`${s}[${o}]`:o)});else{const o=t??"";e.append(s,o)}}static _jsonToFormData(e,t){const s=new FormData;return j._buildFormData(s,e,t),s}_setFormValue(e){const t=j._jsonToFormData(e,this.name);this.internals.setFormValue(t,e),this._validateRequired(),this.touched=!0}_validateRequired(){}labelTemplate(){return this.label?d`
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
 */const{I:zi}=ni,ks=()=>document.createComment(""),we=(i,e,t)=>{var a;const s=i._$AA.parentNode,o=e===void 0?i._$AB:e._$AA;if(t===void 0){const n=s.insertBefore(ks(),o),r=s.insertBefore(ks(),o);t=new zi(n,r,i,i.options)}else{const n=t._$AB.nextSibling,r=t._$AM,l=r!==i;if(l){let u;(a=t._$AQ)==null||a.call(t,i),t._$AM=i,t._$AP!==void 0&&(u=i._$AU)!==r._$AU&&t._$AP(u)}if(n!==o||l){let u=t._$AA;for(;u!==n;){const b=u.nextSibling;s.insertBefore(u,o),u=b}}}return t},ae=(i,e,t=i)=>(i._$AI(e,t),i),Ni={},Ri=(i,e=Ni)=>i._$AH=e,Fi=i=>i._$AH,bt=i=>{var s;(s=i._$AP)==null||s.call(i,!1,!0);let e=i._$AA;const t=i._$AB.nextSibling;for(;e!==t;){const o=e.nextSibling;e.remove(),e=o}};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const Ss=(i,e,t)=>{const s=new Map;for(let o=e;o<=t;o++)s.set(i[o],o);return s},gt=ct(class extends dt{constructor(i){if(super(i),i.type!==lt.CHILD)throw Error("repeat() can only be used in text expressions")}dt(i,e,t){let s;t===void 0?t=e:e!==void 0&&(s=e);const o=[],a=[];let n=0;for(const r of i)o[n]=s?s(r,n):n,a[n]=t(r,n),n++;return{values:a,keys:o}}render(i,e,t){return this.dt(i,e,t).values}update(i,[e,t,s]){const o=Fi(i),{values:a,keys:n}=this.dt(e,t,s);if(!Array.isArray(o))return this.ut=n,a;const r=this.ut??(this.ut=[]),l=[];let u,b,g=0,v=o.length-1,y=0,_=a.length-1;for(;g<=v&&y<=_;)if(o[g]===null)g++;else if(o[v]===null)v--;else if(r[g]===n[y])l[y]=ae(o[g],a[y]),g++,y++;else if(r[v]===n[_])l[_]=ae(o[v],a[_]),v--,_--;else if(r[g]===n[_])l[_]=ae(o[g],a[_]),we(i,l[_+1],o[g]),g++,_--;else if(r[v]===n[y])l[y]=ae(o[v],a[y]),we(i,o[g],o[v]),v--,y++;else if(u===void 0&&(u=Ss(n,y,_),b=Ss(r,g,v)),u.has(r[g]))if(u.has(r[v])){const O=b.get(n[y]),R=O!==void 0?o[O]:null;if(R===null){const I=we(i,o[g]);ae(I,a[y]),l[y]=I}else l[y]=ae(R,a[y]),we(i,o[g],R),o[O]=null;y++}else bt(o[v]),v--;else bt(o[g]),g++;for(;y<=_;){const O=we(i,l[_+1]);ae(O,a[y]),l[y++]=O}for(;g<=v;){const O=o[g++];O!==null&&bt(O)}return this.ut=n,Ri(i,l),q}}),Ui=i=>class extends i{constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},query:{type:String,state:!0},options:{type:Array},filteredOptions:{type:Array,state:!0},open:{type:Boolean,state:!0},canUpdate:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean}}}willUpdate(e){if(super.willUpdate(e),e&&!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");!e.style.getPropertyValue("--container-width")&&e.clientWidth>0&&e.style.setProperty("--container-width",`${e.clientWidth}px`)}_select(){console.error("Must implement `_select(value)` function")}static _focusInput(e){e.target===e.currentTarget&&e.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!0,this.activeIndex=-1)}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1,this.canUpdate=!0)}_inputKeyDown(e){switch(e.keyCode||e.which){case 8:e.target.value===""?this.value=this.value.slice(0,-1):this.open=!0;break;case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){this.query=e.target.value}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const s=t.offsetTop,o=t.offsetTop+t.clientHeight,a=e.scrollTop,n=e.scrollTop+e.clientHeight;o>n?e.scrollTo({top:o-e.clientHeight,behavior:"smooth"}):s<a&&e.scrollTo({top:s,behavior:"smooth"})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select(this.query):this._select(this.filteredOptions[this.activeIndex].id),this._clearSearch())}_clickOption(e){e.target&&e.target.value&&(this._select(e.target.value),this._clearSearch())}_clickAddNew(e){var t;e.target&&(this._select((t=e.target.dataset)==null?void 0:t.label),this._clearSearch())}_clearSearch(){const e=this.shadowRoot.querySelector("input");e&&(e.value="")}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1):this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_renderOption(e,t){return d`
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
    `}_baseRenderOptions(){return this.filteredOptions.length?gt(this.filteredOptions,e=>e.id,(e,t)=>this._renderOption(e,t)):this.loading?d`<li><div>${T("Loading options...")}</div></li>`:d`<li><div>${T("No Data Available")}</div></li>`}_renderOptions(){let e=this._baseRenderOptions();return this.allowAdd&&this.query&&(Array.isArray(e)||(e=[e]),e.push(d`<li tabindex="-1">
        <button
          data-label="${this.query}"
          @click="${this._clickAddNew}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          class="${this.activeIndex>-1&&this.activeIndex>=this.filteredOptions.length?"active":""}"
        >
          ${T("Add")} "${this.query}"
        </button>
      </li>`)),e}};class Bi extends U{static get styles(){return S`
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
    `}}window.customElements.define("dt-spinner",Bi);class qi extends U{static get styles(){return S`
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
    `}}window.customElements.define("dt-checkmark",qi);class mt extends Ui(j){static get styles(){return[...super.styles,S`
        :host {
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
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
          height: 1.25rem;
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
      `]}static get properties(){return{...super.properties,placeholder:{type:String},containerHeight:{type:Number,state:!0},onchange:{type:String}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length)if(typeof this.value[0]=="string")this.value=[...this.value.filter(s=>s!==`-${e}`),e];else{let s=!1;const o=this.value.map(a=>{const n={...a};return a.id===e.id&&a.delete&&(delete n.delete,s=!0),n});s||o.push(e),this.value=o}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._setFormValue(this.value)}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(s=>s===e.target.dataset.value?`-${s}`:s),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){return this.filteredOptions=(this.options||[]).filter(e=>!(this.value||[]).includes(e.id)&&(!this.query||e.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),s=e.has("query"),o=e.has("options");(t||s||o)&&this._filterOptions()}}_renderSelectedOptions(){return this.options&&this.options.filter(e=>this.value&&this.value.indexOf(e.id)>-1).map(e=>d`
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
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
            ?disabled="${this.disabled}"
          />
        </div>
        <ul class="option-list" style=${Q(e)}>
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
`}}window.customElements.define("dt-multi-select",mt);class _e extends mt{static get properties(){return{...super.properties,allowAdd:{type:Boolean},onload:{type:String}}}static get styles(){return[...super.styles,S`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(--primary-color, #3f729b);
        }
      `]}willUpdate(e){super.willUpdate(e),e&&e.has("open")&&this.open&&(!this.filteredOptions||!this.filteredOptions.length)&&this._filterOptions()}_filterOptions(){var t;const e=(this.value||[]).filter(s=>!s.startsWith("-"));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(s=>!e.includes(s.id)&&(!this.query||s.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const s=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{s.loading=!1;let n=a;n.length&&typeof n[0]=="string"&&(n=n.map(r=>({id:r}))),s.allOptions=n,s.filteredOptions=n.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),s.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_renderOption(e,t){return d`
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
    `}_renderSelectedOptions(){const e=this.options||this.allOptions;return(this.value||[]).filter(t=>!t.startsWith("-")).map(t=>{var a;let s=t;if(e){const n=e.filter(r=>r===t||r.id===t);n.length&&(s=n[0].label||n[0].id||t)}let o;if(!o&&((a=window==null?void 0:window.SHAREDFUNCTIONS)!=null&&a.createCustomFilter)){const n=window.SHAREDFUNCTIONS.createCustomFilter(this.name,[t]),r=this.label||this.name,l=[{id:`${this.name}_${t}`,name:`${r}: ${t}`}];o=window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType,n,l)}return d`
          <div class="selected-option">
            <a
              href="${o||"#"}"
              ?disabled="${this.disabled}"
              alt="${t}"
              >${s}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t}"
            >
              x
            </button>
          </div>
        `})}}window.customElements.define("dt-tags",_e);class Es extends _e{static get styles(){return[...super.styles,S`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),s=this.filteredOptions.reduce((o,a)=>!o&&a.id==t?a:o,null);s&&this._select(s),this._clearSearch()}}_clickAddNew(e){var t,s;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(s=e.target.dataset)==null?void 0:s.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(s=>{const o={...s};return s.id===e.target.dataset.value&&(o.delete=!0),o}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(s=>!s.delete).map(s=>s==null?void 0:s.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(s=>!e.includes(s.id)&&(!this.query||s.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const s=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{s.loading=!1,s.filteredOptions=a.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),s.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>d`
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
        `)}_renderOption(e,t){const s=d`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`,o=e.status||{label:"",color:""};return d`
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
          ${o.label?d`<span class="status">${o.label}</span>`:null}
          ${e.user?s:null}
        </button>
      </li>
    `}}window.customElements.define("dt-connection",Es);class Cs extends _e{static get styles(){return[...super.styles,S`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),s=this.filteredOptions.reduce((o,a)=>!o&&a.id==t?a:o,null);s&&this._select(s),this._clearSearch()}}_clickAddNew(e){var t,s;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(s=e.target.dataset)==null?void 0:s.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this._clearSearch())}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,remove:!0}});this.value=(this.value||[]).map(s=>{const o={...s};return s.id===parseInt(e.target.dataset.value,10)&&(o.delete=!0),o}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(s=>!s.delete).map(s=>s==null?void 0:s.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(s=>!e.includes(s.id)&&(!this.query||s.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const s=this,o=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{s.loading=!1,s.filteredOptions=a.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),s.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(o)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>d`
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
    `}}window.customElements.define("dt-users-connection",Cs);class Ts extends N{static get styles(){return S`
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
      <div class="copy-text" style=${Q(this.inputStyles)}>
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
    `}}window.customElements.define("dt-copy-text",Ts);class As extends j{static get styles(){return[...super.styles,S`
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
          display: inline-flex;
          margin: 0 0 1.0666666667rem;
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
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},timestamp:{converter:e=>{let t=Number(e);if(t<1e12&&(t*=1e3),t)return t},reflect:!0},onchange:{type:String}}}updateTimestamp(e){const t=new Date(e).getTime(),s=t/1e3,o=new CustomEvent("change",{detail:{field:this.name,oldValue:this.timestamp,newValue:s}});this.timestamp=t,this.value=e,this._setFormValue(e),this.dispatchEvent(o)}_change(e){this.updateTimestamp(e.target.value)}clearInput(){this.updateTimestamp("")}showDatePicker(){this.shadowRoot.querySelector("input").showPicker()}render(){return this.timestamp?this.value=new Date(this.timestamp).toISOString().substring(0,10):this.value&&(this.timestamp=new Date(this.value).getTime()),d`
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
    `}}window.customElements.define("dt-date",As);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*Ne(i,e){if(i!==void 0){let t=0;for(const s of i)yield e(s,t++)}}class Os extends _e{static get properties(){return{...super.properties,filters:{type:Array},mapboxKey:{type:String},dtMapbox:{type:Object}}}static get styles(){return[...super.styles,S`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=e.target.value,s=this.filteredOptions.reduce((o,a)=>!o&&a.id===t?a:o,null);this._select(s)}}_clickAddNew(e){var t,s;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(s=e.target.dataset)==null?void 0:s.label,isNew:!0});const o=this.shadowRoot.querySelector("input");o&&(o.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(s=>{const o={...s};return s.id===e.target.dataset.value&&(o.delete=!0),o}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}updated(){super.updated();const e=this.shadowRoot.querySelector(".input-group"),t=e.style.getPropertyValue("--select-width"),s=this.shadowRoot.querySelector("select");!t&&(s==null?void 0:s.clientWidth)>0&&e.style.setProperty("--select-width",`${s.clientWidth}px`)}_filterOptions(){var t;const e=(this.value||[]).filter(s=>!s.delete).map(s=>s==null?void 0:s.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(s=>!e.includes(s.id)&&(!this.query||s.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else{this.loading=!0,this.filteredOptions=[];const s=this,o=this.shadowRoot.querySelector("select"),a=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,query:this.query,filter:o==null?void 0:o.value,onSuccess:n=>{s.loading=!1,s.filteredOptions=n.filter(r=>!e.includes(r.id))},onError:n=>{console.warn(n),s.loading=!1}}});this.dispatchEvent(a)}return this.filteredOptions}_renderOption(e,t){return d`
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
            <ul class="option-list" style=${Q(e)}>
              ${this._renderOptions()}
            </ul>
          </div>
        `}}window.customElements.define("dt-location",Os);class Vi{constructor(e){this.token=e}async searchPlaces(e,t="en"){const s=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:t}),o={method:"GET",headers:{"Content-Type":"application/json"}},a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)}.json?${s}`,r=await(await fetch(a,o)).json();return r==null?void 0:r.features}async reverseGeocode(e,t,s="en"){const o=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:s}),a={method:"GET",headers:{"Content-Type":"application/json"}},n=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)},${encodeURI(t)}.json?${o}`,l=await(await fetch(n,a)).json();return l==null?void 0:l.features}}class Hi{constructor(e,t,s){var o,a,n;if(this.token=e,this.window=t,!((n=(a=(o=t.google)==null?void 0:o.maps)==null?void 0:a.places)!=null&&n.AutocompleteService)){let r=s.createElement("script");r.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${e}`,s.body.appendChild(r)}}async getPlacePredictions(e,t="en"){if(this.window.google){const s=new this.window.google.maps.places.AutocompleteService,{predictions:o}=await s.getPlacePredictions({input:e,language:t});return o}return null}async getPlaceDetails(e,t="en"){const o=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,address:e,language:t})}`,n=await(await fetch(o,{method:"GET"})).json();let r=[];switch(n.status){case"OK":r=n.results;break}return r&&r.length?r[0]:null}async reverseGeocode(e,t,s="en"){const a=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${t},${e}`,language:s,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,r=await(await fetch(a,{method:"GET"})).json();return r==null?void 0:r.results}}/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/const Ls=Object.freeze({left:0,top:0,width:16,height:16}),Re=Object.freeze({rotate:0,vFlip:!1,hFlip:!1}),$e=Object.freeze({...Ls,...Re}),vt=Object.freeze({...$e,body:"",hidden:!1}),Wi=Object.freeze({width:null,height:null}),Ps=Object.freeze({...Wi,...Re});function Gi(i,e=0){const t=i.replace(/^-?[0-9.]*/,"");function s(o){for(;o<0;)o+=4;return o%4}if(t===""){const o=parseInt(i);return isNaN(o)?0:s(o)}else if(t!==i){let o=0;switch(t){case"%":o=25;break;case"deg":o=90}if(o){let a=parseFloat(i.slice(0,i.length-t.length));return isNaN(a)?0:(a=a/o,a%1===0?s(a):0)}}return e}const Ki=/[\s,]+/;function Zi(i,e){e.split(Ki).forEach(t=>{switch(t.trim()){case"horizontal":i.hFlip=!0;break;case"vertical":i.vFlip=!0;break}})}const Is={...Ps,preserveAspectRatio:""};function Ms(i){const e={...Is},t=(s,o)=>i.getAttribute(s)||o;return e.width=t("width",null),e.height=t("height",null),e.rotate=Gi(t("rotate","")),Zi(e,t("flip","")),e.preserveAspectRatio=t("preserveAspectRatio",t("preserveaspectratio","")),e}function Ji(i,e){for(const t in Is)if(i[t]!==e[t])return!0;return!1}const xe=/^[a-z0-9]+(-[a-z0-9]+)*$/,ke=(i,e,t,s="")=>{const o=i.split(":");if(i.slice(0,1)==="@"){if(o.length<2||o.length>3)return null;s=o.shift().slice(1)}if(o.length>3||!o.length)return null;if(o.length>1){const r=o.pop(),l=o.pop(),u={provider:o.length>0?o[0]:s,prefix:l,name:r};return e&&!Fe(u)?null:u}const a=o[0],n=a.split("-");if(n.length>1){const r={provider:s,prefix:n.shift(),name:n.join("-")};return e&&!Fe(r)?null:r}if(t&&s===""){const r={provider:s,prefix:"",name:a};return e&&!Fe(r,t)?null:r}return null},Fe=(i,e)=>i?!!((i.provider===""||i.provider.match(xe))&&(e&&i.prefix===""||i.prefix.match(xe))&&i.name.match(xe)):!1;function Qi(i,e){const t={};!i.hFlip!=!e.hFlip&&(t.hFlip=!0),!i.vFlip!=!e.vFlip&&(t.vFlip=!0);const s=((i.rotate||0)+(e.rotate||0))%4;return s&&(t.rotate=s),t}function js(i,e){const t=Qi(i,e);for(const s in vt)s in Re?s in i&&!(s in t)&&(t[s]=Re[s]):s in e?t[s]=e[s]:s in i&&(t[s]=i[s]);return t}function Xi(i,e){const t=i.icons,s=i.aliases||Object.create(null),o=Object.create(null);function a(n){if(t[n])return o[n]=[];if(!(n in o)){o[n]=null;const r=s[n]&&s[n].parent,l=r&&a(r);l&&(o[n]=[r].concat(l))}return o[n]}return Object.keys(t).concat(Object.keys(s)).forEach(a),o}function Yi(i,e,t){const s=i.icons,o=i.aliases||Object.create(null);let a={};function n(r){a=js(s[r]||o[r],a)}return n(e),t.forEach(n),js(i,a)}function Ds(i,e){const t=[];if(typeof i!="object"||typeof i.icons!="object")return t;i.not_found instanceof Array&&i.not_found.forEach(o=>{e(o,null),t.push(o)});const s=Xi(i);for(const o in s){const a=s[o];a&&(e(o,Yi(i,o,a)),t.push(o))}return t}const ea={provider:"",aliases:{},not_found:{},...Ls};function yt(i,e){for(const t in e)if(t in i&&typeof i[t]!=typeof e[t])return!1;return!0}function zs(i){if(typeof i!="object"||i===null)return null;const e=i;if(typeof e.prefix!="string"||!i.icons||typeof i.icons!="object"||!yt(i,ea))return null;const t=e.icons;for(const o in t){const a=t[o];if(!o.match(xe)||typeof a.body!="string"||!yt(a,vt))return null}const s=e.aliases||Object.create(null);for(const o in s){const a=s[o],n=a.parent;if(!o.match(xe)||typeof n!="string"||!t[n]&&!s[n]||!yt(a,vt))return null}return e}const Ue=Object.create(null);function ta(i,e){return{provider:i,prefix:e,icons:Object.create(null),missing:new Set}}function X(i,e){const t=Ue[i]||(Ue[i]=Object.create(null));return t[e]||(t[e]=ta(i,e))}function wt(i,e){return zs(e)?Ds(e,(t,s)=>{s?i.icons[t]=s:i.missing.add(t)}):[]}function sa(i,e,t){try{if(typeof t.body=="string")return i.icons[e]={...t},!0}catch{}return!1}function oa(i,e){let t=[];return(typeof i=="string"?[i]:Object.keys(Ue)).forEach(o=>{(typeof o=="string"&&typeof e=="string"?[e]:Object.keys(Ue[o]||{})).forEach(n=>{const r=X(o,n);t=t.concat(Object.keys(r.icons).map(l=>(o!==""?"@"+o+":":"")+n+":"+l))})}),t}let Se=!1;function Ns(i){return typeof i=="boolean"&&(Se=i),Se}function Ee(i){const e=typeof i=="string"?ke(i,!0,Se):i;if(e){const t=X(e.provider,e.prefix),s=e.name;return t.icons[s]||(t.missing.has(s)?null:void 0)}}function Rs(i,e){const t=ke(i,!0,Se);if(!t)return!1;const s=X(t.provider,t.prefix);return sa(s,t.name,e)}function Fs(i,e){if(typeof i!="object")return!1;if(typeof e!="string"&&(e=i.provider||""),Se&&!e&&!i.prefix){let o=!1;return zs(i)&&(i.prefix="",Ds(i,(a,n)=>{n&&Rs(a,n)&&(o=!0)})),o}const t=i.prefix;if(!Fe({provider:e,prefix:t,name:"a"}))return!1;const s=X(e,t);return!!wt(s,i)}function ia(i){return!!Ee(i)}function aa(i){const e=Ee(i);return e?{...$e,...e}:null}function na(i){const e={loaded:[],missing:[],pending:[]},t=Object.create(null);i.sort((o,a)=>o.provider!==a.provider?o.provider.localeCompare(a.provider):o.prefix!==a.prefix?o.prefix.localeCompare(a.prefix):o.name.localeCompare(a.name));let s={provider:"",prefix:"",name:""};return i.forEach(o=>{if(s.name===o.name&&s.prefix===o.prefix&&s.provider===o.provider)return;s=o;const a=o.provider,n=o.prefix,r=o.name,l=t[a]||(t[a]=Object.create(null)),u=l[n]||(l[n]=X(a,n));let b;r in u.icons?b=e.loaded:n===""||u.missing.has(r)?b=e.missing:b=e.pending;const g={provider:a,prefix:n,name:r};b.push(g)}),e}function Us(i,e){i.forEach(t=>{const s=t.loaderCallbacks;s&&(t.loaderCallbacks=s.filter(o=>o.id!==e))})}function ra(i){i.pendingCallbacksFlag||(i.pendingCallbacksFlag=!0,setTimeout(()=>{i.pendingCallbacksFlag=!1;const e=i.loaderCallbacks?i.loaderCallbacks.slice(0):[];if(!e.length)return;let t=!1;const s=i.provider,o=i.prefix;e.forEach(a=>{const n=a.icons,r=n.pending.length;n.pending=n.pending.filter(l=>{if(l.prefix!==o)return!0;const u=l.name;if(i.icons[u])n.loaded.push({provider:s,prefix:o,name:u});else if(i.missing.has(u))n.missing.push({provider:s,prefix:o,name:u});else return t=!0,!0;return!1}),n.pending.length!==r&&(t||Us([i],a.id),a.callback(n.loaded.slice(0),n.missing.slice(0),n.pending.slice(0),a.abort))})}))}let la=0;function ca(i,e,t){const s=la++,o=Us.bind(null,t,s);if(!e.pending.length)return o;const a={id:s,icons:e,callback:i,abort:o};return t.forEach(n=>{(n.loaderCallbacks||(n.loaderCallbacks=[])).push(a)}),o}const _t=Object.create(null);function Bs(i,e){_t[i]=e}function $t(i){return _t[i]||_t[""]}function da(i,e=!0,t=!1){const s=[];return i.forEach(o=>{const a=typeof o=="string"?ke(o,e,t):o;a&&s.push(a)}),s}var ua={resources:[],index:0,timeout:2e3,rotate:750,random:!1,dataAfterTimeout:!1};function ha(i,e,t,s){const o=i.resources.length,a=i.random?Math.floor(Math.random()*o):i.index;let n;if(i.random){let E=i.resources.slice(0);for(n=[];E.length>1;){const D=Math.floor(Math.random()*E.length);n.push(E[D]),E=E.slice(0,D).concat(E.slice(D+1))}n=n.concat(E)}else n=i.resources.slice(a).concat(i.resources.slice(0,a));const r=Date.now();let l="pending",u=0,b,g=null,v=[],y=[];typeof s=="function"&&y.push(s);function _(){g&&(clearTimeout(g),g=null)}function O(){l==="pending"&&(l="aborted"),_(),v.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),v=[]}function R(E,D){D&&(y=[]),typeof E=="function"&&y.push(E)}function I(){return{startTime:r,payload:e,status:l,queriesSent:u,queriesPending:v.length,subscribe:R,abort:O}}function L(){l="failed",y.forEach(E=>{E(void 0,b)})}function Ae(){v.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),v=[]}function Ge(E,D,re){const ee=D!=="success";switch(v=v.filter(P=>P!==E),l){case"pending":break;case"failed":if(ee||!i.dataAfterTimeout)return;break;default:return}if(D==="abort"){b=re,L();return}if(ee){b=re,v.length||(n.length?Y():L());return}if(_(),Ae(),!i.random){const P=i.resources.indexOf(E.resource);P!==-1&&P!==i.index&&(i.index=P)}l="completed",y.forEach(P=>{P(re)})}function Y(){if(l!=="pending")return;_();const E=n.shift();if(E===void 0){if(v.length){g=setTimeout(()=>{_(),l==="pending"&&(Ae(),L())},i.timeout);return}L();return}const D={status:"pending",resource:E,callback:(re,ee)=>{Ge(D,re,ee)}};v.push(D),u++,g=setTimeout(Y,i.rotate),t(E,e,D.callback)}return setTimeout(Y),I}function qs(i){const e={...ua,...i};let t=[];function s(){t=t.filter(r=>r().status==="pending")}function o(r,l,u){const b=ha(e,r,l,(g,v)=>{s(),u&&u(g,v)});return t.push(b),b}function a(r){return t.find(l=>r(l))||null}return{query:o,find:a,setIndex:r=>{e.index=r},getIndex:()=>e.index,cleanup:s}}function xt(i){let e;if(typeof i.resources=="string")e=[i.resources];else if(e=i.resources,!(e instanceof Array)||!e.length)return null;return{resources:e,path:i.path||"/",maxURL:i.maxURL||500,rotate:i.rotate||750,timeout:i.timeout||5e3,random:i.random===!0,index:i.index||0,dataAfterTimeout:i.dataAfterTimeout!==!1}}const Be=Object.create(null),Ce=["https://api.simplesvg.com","https://api.unisvg.com"],qe=[];for(;Ce.length>0;)Ce.length===1||Math.random()>.5?qe.push(Ce.shift()):qe.push(Ce.pop());Be[""]=xt({resources:["https://api.iconify.design"].concat(qe)});function Vs(i,e){const t=xt(e);return t===null?!1:(Be[i]=t,!0)}function Ve(i){return Be[i]}function pa(){return Object.keys(Be)}function Hs(){}const kt=Object.create(null);function fa(i){if(!kt[i]){const e=Ve(i);if(!e)return;const t=qs(e),s={config:e,redundancy:t};kt[i]=s}return kt[i]}function Ws(i,e,t){let s,o;if(typeof i=="string"){const a=$t(i);if(!a)return t(void 0,424),Hs;o=a.send;const n=fa(i);n&&(s=n.redundancy)}else{const a=xt(i);if(a){s=qs(a);const n=i.resources?i.resources[0]:"",r=$t(n);r&&(o=r.send)}}return!s||!o?(t(void 0,424),Hs):s.query(e,o,t)().abort}const Gs="iconify2",Te="iconify",Ks=Te+"-count",Zs=Te+"-version",Js=36e5,ba=168;function St(i,e){try{return i.getItem(e)}catch{}}function Et(i,e,t){try{return i.setItem(e,t),!0}catch{}}function Qs(i,e){try{i.removeItem(e)}catch{}}function Ct(i,e){return Et(i,Ks,e.toString())}function Tt(i){return parseInt(St(i,Ks))||0}const ne={local:!0,session:!0},Xs={local:new Set,session:new Set};let At=!1;function ga(i){At=i}let He=typeof window>"u"?{}:window;function Ys(i){const e=i+"Storage";try{if(He&&He[e]&&typeof He[e].length=="number")return He[e]}catch{}ne[i]=!1}function eo(i,e){const t=Ys(i);if(!t)return;const s=St(t,Zs);if(s!==Gs){if(s){const r=Tt(t);for(let l=0;l<r;l++)Qs(t,Te+l.toString())}Et(t,Zs,Gs),Ct(t,0);return}const o=Math.floor(Date.now()/Js)-ba,a=r=>{const l=Te+r.toString(),u=St(t,l);if(typeof u=="string"){try{const b=JSON.parse(u);if(typeof b=="object"&&typeof b.cached=="number"&&b.cached>o&&typeof b.provider=="string"&&typeof b.data=="object"&&typeof b.data.prefix=="string"&&e(b,r))return!0}catch{}Qs(t,l)}};let n=Tt(t);for(let r=n-1;r>=0;r--)a(r)||(r===n-1?(n--,Ct(t,n)):Xs[i].add(r))}function to(){if(!At){ga(!0);for(const i in ne)eo(i,e=>{const t=e.data,s=e.provider,o=t.prefix,a=X(s,o);if(!wt(a,t).length)return!1;const n=t.lastModified||-1;return a.lastModifiedCached=a.lastModifiedCached?Math.min(a.lastModifiedCached,n):n,!0})}}function ma(i,e){const t=i.lastModifiedCached;if(t&&t>=e)return t===e;if(i.lastModifiedCached=e,t)for(const s in ne)eo(s,o=>{const a=o.data;return o.provider!==i.provider||a.prefix!==i.prefix||a.lastModified===e});return!0}function va(i,e){At||to();function t(s){let o;if(!ne[s]||!(o=Ys(s)))return;const a=Xs[s];let n;if(a.size)a.delete(n=Array.from(a).shift());else if(n=Tt(o),!Ct(o,n+1))return;const r={cached:Math.floor(Date.now()/Js),provider:i.provider,data:e};return Et(o,Te+n.toString(),JSON.stringify(r))}e.lastModified&&!ma(i,e.lastModified)||Object.keys(e.icons).length&&(e.not_found&&(e=Object.assign({},e),delete e.not_found),t("local")||t("session"))}function so(){}function ya(i){i.iconsLoaderFlag||(i.iconsLoaderFlag=!0,setTimeout(()=>{i.iconsLoaderFlag=!1,ra(i)}))}function wa(i,e){i.iconsToLoad?i.iconsToLoad=i.iconsToLoad.concat(e).sort():i.iconsToLoad=e,i.iconsQueueFlag||(i.iconsQueueFlag=!0,setTimeout(()=>{i.iconsQueueFlag=!1;const{provider:t,prefix:s}=i,o=i.iconsToLoad;delete i.iconsToLoad;let a;if(!o||!(a=$t(t)))return;a.prepare(t,s,o).forEach(r=>{Ws(t,r,l=>{if(typeof l!="object")r.icons.forEach(u=>{i.missing.add(u)});else try{const u=wt(i,l);if(!u.length)return;const b=i.pendingIcons;b&&u.forEach(g=>{b.delete(g)}),va(i,l)}catch(u){console.error(u)}ya(i)})})}))}const Ot=(i,e)=>{const t=da(i,!0,Ns()),s=na(t);if(!s.pending.length){let l=!0;return e&&setTimeout(()=>{l&&e(s.loaded,s.missing,s.pending,so)}),()=>{l=!1}}const o=Object.create(null),a=[];let n,r;return s.pending.forEach(l=>{const{provider:u,prefix:b}=l;if(b===r&&u===n)return;n=u,r=b,a.push(X(u,b));const g=o[u]||(o[u]=Object.create(null));g[b]||(g[b]=[])}),s.pending.forEach(l=>{const{provider:u,prefix:b,name:g}=l,v=X(u,b),y=v.pendingIcons||(v.pendingIcons=new Set);y.has(g)||(y.add(g),o[u][b].push(g))}),a.forEach(l=>{const{provider:u,prefix:b}=l;o[u][b].length&&wa(l,o[u][b])}),e?ca(e,s,a):so},_a=i=>new Promise((e,t)=>{const s=typeof i=="string"?ke(i,!0):i;if(!s){t(i);return}Ot([s||i],o=>{if(o.length&&s){const a=Ee(s);if(a){e({...$e,...a});return}}t(i)})});function $a(i){try{const e=typeof i=="string"?JSON.parse(i):i;if(typeof e.body=="string")return{...e}}catch{}}function xa(i,e){const t=typeof i=="string"?ke(i,!0,!0):null;if(!t){const a=$a(i);return{value:i,data:a}}const s=Ee(t);if(s!==void 0||!t.prefix)return{value:i,name:t,data:s};const o=Ot([t],()=>e(i,t,Ee(t)));return{value:i,name:t,loading:o}}function Lt(i){return i.hasAttribute("inline")}let oo=!1;try{oo=navigator.vendor.indexOf("Apple")===0}catch{}function ka(i,e){switch(e){case"svg":case"bg":case"mask":return e}return e!=="style"&&(oo||i.indexOf("<a")===-1)?"svg":i.indexOf("currentColor")===-1?"bg":"mask"}const Sa=/(-?[0-9.]*[0-9]+[0-9.]*)/g,Ea=/^-?[0-9.]*[0-9]+[0-9.]*$/g;function Pt(i,e,t){if(e===1)return i;if(t=t||100,typeof i=="number")return Math.ceil(i*e*t)/t;if(typeof i!="string")return i;const s=i.split(Sa);if(s===null||!s.length)return i;const o=[];let a=s.shift(),n=Ea.test(a);for(;;){if(n){const r=parseFloat(a);isNaN(r)?o.push(a):o.push(Math.ceil(r*e*t)/t)}else o.push(a);if(a=s.shift(),a===void 0)return o.join("");n=!n}}function io(i,e){const t={...$e,...i},s={...Ps,...e},o={left:t.left,top:t.top,width:t.width,height:t.height};let a=t.body;[t,s].forEach(y=>{const _=[],O=y.hFlip,R=y.vFlip;let I=y.rotate;O?R?I+=2:(_.push("translate("+(o.width+o.left).toString()+" "+(0-o.top).toString()+")"),_.push("scale(-1 1)"),o.top=o.left=0):R&&(_.push("translate("+(0-o.left).toString()+" "+(o.height+o.top).toString()+")"),_.push("scale(1 -1)"),o.top=o.left=0);let L;switch(I<0&&(I-=Math.floor(I/4)*4),I=I%4,I){case 1:L=o.height/2+o.top,_.unshift("rotate(90 "+L.toString()+" "+L.toString()+")");break;case 2:_.unshift("rotate(180 "+(o.width/2+o.left).toString()+" "+(o.height/2+o.top).toString()+")");break;case 3:L=o.width/2+o.left,_.unshift("rotate(-90 "+L.toString()+" "+L.toString()+")");break}I%2===1&&(o.left!==o.top&&(L=o.left,o.left=o.top,o.top=L),o.width!==o.height&&(L=o.width,o.width=o.height,o.height=L)),_.length&&(a='<g transform="'+_.join(" ")+'">'+a+"</g>")});const n=s.width,r=s.height,l=o.width,u=o.height;let b,g;return n===null?(g=r===null?"1em":r==="auto"?u:r,b=Pt(g,l/u)):(b=n==="auto"?l:n,g=r===null?Pt(b,u/l):r==="auto"?u:r),{attributes:{width:b.toString(),height:g.toString(),viewBox:o.left.toString()+" "+o.top.toString()+" "+l.toString()+" "+u.toString()},body:a}}let We=(()=>{let i;try{if(i=fetch,typeof i=="function")return i}catch{}})();function Ca(i){We=i}function Ta(){return We}function Aa(i,e){const t=Ve(i);if(!t)return 0;let s;if(!t.maxURL)s=0;else{let o=0;t.resources.forEach(n=>{o=Math.max(o,n.length)});const a=e+".json?icons=";s=t.maxURL-o-t.path.length-a.length}return s}function Oa(i){return i===404}const La=(i,e,t)=>{const s=[],o=Aa(i,e),a="icons";let n={type:a,provider:i,prefix:e,icons:[]},r=0;return t.forEach((l,u)=>{r+=l.length+1,r>=o&&u>0&&(s.push(n),n={type:a,provider:i,prefix:e,icons:[]},r=l.length),n.icons.push(l)}),s.push(n),s};function Pa(i){if(typeof i=="string"){const e=Ve(i);if(e)return e.path}return"/"}const Ia={prepare:La,send:(i,e,t)=>{if(!We){t("abort",424);return}let s=Pa(e.provider);switch(e.type){case"icons":{const a=e.prefix,r=e.icons.join(","),l=new URLSearchParams({icons:r});s+=a+".json?"+l.toString();break}case"custom":{const a=e.uri;s+=a.slice(0,1)==="/"?a.slice(1):a;break}default:t("abort",400);return}let o=503;We(i+s).then(a=>{const n=a.status;if(n!==200){setTimeout(()=>{t(Oa(n)?"abort":"next",n)});return}return o=501,a.json()}).then(a=>{if(typeof a!="object"||a===null){setTimeout(()=>{a===404?t("abort",a):t("next",o)});return}setTimeout(()=>{t("success",a)})}).catch(()=>{t("next",o)})}};function ao(i,e){switch(i){case"local":case"session":ne[i]=e;break;case"all":for(const t in ne)ne[t]=e;break}}function no(){Bs("",Ia),Ns(!0);let i;try{i=window}catch{}if(i){if(to(),i.IconifyPreload!==void 0){const t=i.IconifyPreload,s="Invalid IconifyPreload syntax.";typeof t=="object"&&t!==null&&(t instanceof Array?t:[t]).forEach(o=>{try{(typeof o!="object"||o===null||o instanceof Array||typeof o.icons!="object"||typeof o.prefix!="string"||!Fs(o))&&console.error(s)}catch{console.error(s)}})}if(i.IconifyProviders!==void 0){const t=i.IconifyProviders;if(typeof t=="object"&&t!==null)for(const s in t){const o="IconifyProviders["+s+"] is invalid.";try{const a=t[s];if(typeof a!="object"||!a||a.resources===void 0)continue;Vs(s,a)||console.error(o)}catch{console.error(o)}}}}return{enableCache:t=>ao(t,!0),disableCache:t=>ao(t,!1),iconExists:ia,getIcon:aa,listIcons:oa,addIcon:Rs,addCollection:Fs,calculateSize:Pt,buildIcon:io,loadIcons:Ot,loadIcon:_a,addAPIProvider:Vs,_api:{getAPIConfig:Ve,setAPIModule:Bs,sendAPIQuery:Ws,setFetch:Ca,getFetch:Ta,listAPIProviders:pa}}}function ro(i,e){let t=i.indexOf("xlink:")===-1?"":' xmlns:xlink="http://www.w3.org/1999/xlink"';for(const s in e)t+=" "+s+'="'+e[s]+'"';return'<svg xmlns="http://www.w3.org/2000/svg"'+t+">"+i+"</svg>"}function Ma(i){return i.replace(/"/g,"'").replace(/%/g,"%25").replace(/#/g,"%23").replace(/</g,"%3C").replace(/>/g,"%3E").replace(/\s+/g," ")}function ja(i){return'url("data:image/svg+xml,'+Ma(i)+'")'}const It={"background-color":"currentColor"},lo={"background-color":"transparent"},co={image:"var(--svg)",repeat:"no-repeat",size:"100% 100%"},uo={"-webkit-mask":It,mask:It,background:lo};for(const i in uo){const e=uo[i];for(const t in co)e[i+"-"+t]=co[t]}function ho(i){return i+(i.match(/^[-0-9.]+$/)?"px":"")}function Da(i,e,t){const s=document.createElement("span");let o=i.body;o.indexOf("<a")!==-1&&(o+="<!-- "+Date.now()+" -->");const a=i.attributes,n=ro(o,{...a,width:e.width+"",height:e.height+""}),r=ja(n),l=s.style,u={"--svg":r,width:ho(a.width),height:ho(a.height),...t?It:lo};for(const b in u)l.setProperty(b,u[b]);return s}function za(i){const e=document.createElement("span");return e.innerHTML=ro(i.body,i.attributes),e.firstChild}function po(i,e){const t=e.icon.data,s=e.customisations,o=io(t,s);s.preserveAspectRatio&&(o.attributes.preserveAspectRatio=s.preserveAspectRatio);const a=e.renderedMode;let n;switch(a){case"svg":n=za(o);break;default:n=Da(o,{...$e,...t},a==="mask")}const r=Array.from(i.childNodes).find(l=>{const u=l.tagName&&l.tagName.toUpperCase();return u==="SPAN"||u==="SVG"});r?n.tagName==="SPAN"&&r.tagName===n.tagName?r.setAttribute("style",n.getAttribute("style")):i.replaceChild(n,r):i.appendChild(n)}const Mt="data-style";function fo(i,e){let t=Array.from(i.childNodes).find(s=>s.hasAttribute&&s.hasAttribute(Mt));t||(t=document.createElement("style"),t.setAttribute(Mt,Mt),i.appendChild(t)),t.textContent=":host{display:inline-block;vertical-align:"+(e?"-0.125em":"0")+"}span,svg{display:block}"}function bo(i,e,t){const s=t&&(t.rendered?t:t.lastRender);return{rendered:!1,inline:e,icon:i,lastRender:s}}function Na(i="iconify-icon"){let e,t;try{e=window.customElements,t=window.HTMLElement}catch{return}if(!e||!t)return;const s=e.get(i);if(s)return s;const o=["icon","mode","inline","width","height","rotate","flip"],a=class extends t{constructor(){super();Qe(this,"_shadowRoot");Qe(this,"_state");Qe(this,"_checkQueued",!1);const l=this._shadowRoot=this.attachShadow({mode:"open"}),u=Lt(this);fo(l,u),this._state=bo({value:""},u),this._queueCheck()}static get observedAttributes(){return o.slice(0)}attributeChangedCallback(l){if(l==="inline"){const u=Lt(this),b=this._state;u!==b.inline&&(b.inline=u,fo(this._shadowRoot,u))}else this._queueCheck()}get icon(){const l=this.getAttribute("icon");if(l&&l.slice(0,1)==="{")try{return JSON.parse(l)}catch{}return l}set icon(l){typeof l=="object"&&(l=JSON.stringify(l)),this.setAttribute("icon",l)}get inline(){return Lt(this)}set inline(l){this.setAttribute("inline",l?"true":null)}restartAnimation(){const l=this._state;if(l.rendered){const u=this._shadowRoot;if(l.renderedMode==="svg")try{u.lastChild.setCurrentTime(0);return}catch{}po(u,l)}}get status(){const l=this._state;return l.rendered?"rendered":l.icon.data===null?"failed":"loading"}_queueCheck(){this._checkQueued||(this._checkQueued=!0,setTimeout(()=>{this._check()}))}_check(){if(!this._checkQueued)return;this._checkQueued=!1;const l=this._state,u=this.getAttribute("icon");if(u!==l.icon.value){this._iconChanged(u);return}if(!l.rendered)return;const b=this.getAttribute("mode"),g=Ms(this);(l.attrMode!==b||Ji(l.customisations,g))&&this._renderIcon(l.icon,g,b)}_iconChanged(l){const u=xa(l,(b,g,v)=>{const y=this._state;if(y.rendered||this.getAttribute("icon")!==b)return;const _={value:b,name:g,data:v};_.data?this._gotIconData(_):y.icon=_});u.data?this._gotIconData(u):this._state=bo(u,this._state.inline,this._state)}_gotIconData(l){this._checkQueued=!1,this._renderIcon(l,Ms(this),this.getAttribute("mode"))}_renderIcon(l,u,b){const g=ka(l.data.body,b),v=this._state.inline;po(this._shadowRoot,this._state={rendered:!0,icon:l,inline:v,customisations:u,attrMode:b,renderedMode:g})}};o.forEach(r=>{r in a.prototype||Object.defineProperty(a.prototype,r,{get:function(){return this.getAttribute(r)},set:function(l){this.setAttribute(r,l)}})});const n=no();for(const r in n)a[r]=a.prototype[r]=n[r];return e.define(i,a),a}const Ra=Na()||no(),{enableCache:Nr,disableCache:Rr,iconExists:Fr,getIcon:Ur,listIcons:Br,addIcon:qr,addCollection:Vr,calculateSize:Hr,buildIcon:Wr,loadIcons:Gr,loadIcon:Kr,addAPIProvider:Zr,_api:Jr}=Ra;class go extends N{static get styles(){return S`
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
    `}}window.customElements.define("dt-icon",go);class mo extends N{static get properties(){return{...super.properties,title:{type:String},isOpen:{type:Boolean},canEdit:{type:Boolean,state:!0},metadata:{type:Object},center:{type:Array},mapboxToken:{type:String,attribute:"mapbox-token"}}}static get styles(){return[S`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `]}constructor(){super(),this.addEventListener("open",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")),this.isOpen=!0}),this.addEventListener("close",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")),this.isOpen=!1})}connectedCallback(){if(super.connectedCallback(),this.canEdit=!this.metadata,window.mapboxgl)this.initMap();else{let e=document.createElement("script");e.src="https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js",e.onload=this.initMap.bind(this),document.body.appendChild(e),console.log("injected script")}}initMap(){if(!this.isOpen||!window.mapboxgl||!this.mapboxToken)return;const e=this.shadowRoot.querySelector("#map");if(e&&!this.map){this.map=new window.mapboxgl.Map({accessToken:this.mapboxToken,container:e,style:"mapbox://styles/mapbox/streets-v12",minZoom:1}),this.map.on("load",()=>this.map.resize()),this.center&&this.center.length&&(this.map.setCenter(this.center),this.map.setZoom(15));const t=new mapboxgl.NavigationControl;this.map.addControl(t,"bottom-right"),this.addPinFromMetadata(),this.map.on("click",s=>{this.canEdit&&(this.marker?this.marker.setLngLat(s.lngLat):this.marker=new mapboxgl.Marker().setLngLat(s.lngLat).addTo(this.map))})}}addPinFromMetadata(){if(this.metadata){const{lng:e,lat:t,level:s}=this.metadata;let o=15;s==="admin0"?o=3:s==="admin1"?o=6:s==="admin2"&&(o=10),this.map&&(this.map.setCenter([e,t]),this.map.setZoom(o),this.marker=new mapboxgl.Marker().setLngLat([e,t]).addTo(this.map))}}updated(e){window.mapboxgl&&(e.has("metadata")&&this.metadata&&this.metadata.lat&&this.addPinFromMetadata(),e.has("isOpen")&&this.isOpen&&this.initMap())}onClose(e){var t;((t=e==null?void 0:e.detail)==null?void 0:t.action)==="button"&&this.marker&&this.dispatchEvent(new CustomEvent("submit",{detail:{location:this.marker.getLngLat()}}))}render(){var e;return d`      
      <dt-modal
        .title=${(e=this.metadata)==null?void 0:e.label}
        ?isopen=${this.isOpen}
        hideButton
        @close=${this.onClose}
      >
        <div slot="content">
          <div class="map" id="map"></div>
        </div>
       
        ${this.canEdit?d`<div slot="close-button">${T("Save")}</div>`:null}
      </dt-modal>
      
      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `}}window.customElements.define("dt-map-modal",mo);class Fa extends U{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[S`
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
      `]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",async()=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()}),this.mapboxToken&&(this.mapboxService=new Vi(this.mapboxToken)),this.googleToken&&(this.googleGeocodeService=new Hi(this.googleToken,window,document))}disconnectedCallback(){super.disconnectedCallback(),this.removeEventListener("autofocus",this.handleAutofocus)}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const s=t.offsetTop,o=t.offsetTop+t.clientHeight,a=e.scrollTop,n=e.scrollTop+e.clientHeight;o>n?e.scrollTo({top:o-e.clientHeight,behavior:"smooth"}):s<a&&e.scrollTo({top:s,behavior:"smooth"})}}_clickOption(e){const t=e.currentTarget??e.target;t&&t.value&&this._select(JSON.parse(t.value))}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query}))}async _select(e){if(e.place_id&&this.googleGeocodeService){this.loading=!0;const o=await this.googleGeocodeService.getPlaceDetails(e.label,this.locale);this.loading=!1,o&&(e.lat=o.geometry.location.lat,e.lng=o.geometry.location.lng,e.level=o.types&&o.types.length?o.types[0]:null)}const t={detail:{metadata:e},bubbles:!1};this.dispatchEvent(new CustomEvent("select",t)),this.metadata=e;const s=this.shadowRoot.querySelector("input");s&&(s.value=e==null?void 0:e.label),this.open=!1,this.activeIndex=-1}get _focusTarget(){let e=this._field;return this.metadata&&(e=this.shadowRoot.querySelector("button")||e),e}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1)}_inputKeyDown(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){const t=e.keyCode||e.which,s=[9,13];e.target.value&&!s.includes(t)&&(this.open=!0),this.query=e.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query){if(this.googleToken&&this.googleGeocodeService){this.loading=!0;try{const e=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(e||[]).map(t=>({label:t.description,place_id:t.place_id,source:"user",raw:t})),this.loading=!1}catch(e){console.error(e),this.error=!0,this.loading=!1;return}}else if(this.mapboxToken&&this.mapboxService){this.loading=!0;const e=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=e.map(t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"})),this.loading=!1}}return this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e&&(e.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout(()=>this._filterOptions(),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length)){const s=this.shadowRoot.querySelector(".input-group");s&&(this.containerHeight=s.offsetHeight)}}_change(){}_delete(){const e={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",e))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(e){var t,s;if((s=(t=e==null?void 0:e.detail)==null?void 0:t.location)!=null&&s.lat){const{location:o}=e==null?void 0:e.detail,{lat:a,lng:n}=o;if(this.googleGeocodeService){const r=await this.googleGeocodeService.reverseGeocode(n,a,this.locale);if(r&&r.length){const l=r[0];this._select({lng:l.geometry.location.lng,lat:l.geometry.location.lat,level:l.types&&l.types.length?l.types[0]:null,label:l.formatted_address,source:"user"})}}else if(this.mapboxService){const r=await this.mapboxService.reverseGeocode(n,a,this.locale);if(r&&r.length){const l=r[0];this._select({lng:l.center[0],lat:l.center[1],level:l.place_type[0],label:l.place_name,source:"user"})}}}}_renderOption(e,t,s){return d`
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
          ${s??e.label}
        </button>
      </li>
    `}_renderOptions(){let e=[];return this.filteredOptions.length?e.push(...this.filteredOptions.map((t,s)=>this._renderOption(t,s))):this.loading?e.push(d`<li><div>${T("Loading...")}</div></li>`):e.push(d`<li><div>${T("No Data Available")}</div></li>`),e.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,d`<strong>${T("Use")}: "${this.query}"</strong>`)),e}render(){var o,a,n,r;const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},t=!!((o=this.metadata)!=null&&o.label),s=((a=this.metadata)==null?void 0:a.lat)&&((n=this.metadata)==null?void 0:n.lng);return d`
      <div class="input-group">
        <div class="field-container">
          <input
            type="text"
            class="${this.disabled?"disabled":null}"
            placeholder="${this.placeholder}"
            .value="${(r=this.metadata)==null?void 0:r.label}"
            .disabled=${t&&s||this.disabled}
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
          />

          ${t&&s?d`
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
        <ul class="option-list" style=${Q(e)}>
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

`}}window.customElements.define("dt-location-map-item",Fa);class vo extends j{static get properties(){return{...super.properties,placeholder:{type:String},value:{type:Array,reflect:!0},locations:{type:Array,state:!0},open:{type:Boolean,state:!0},onchange:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"}}}static get styles(){return[...super.styles,S`
        :host {
          font-family: Helvetica, Arial, sans-serif;
        }
        .input-group {
          display: flex;
        }

        .field-container {
          position: relative;
        }
      `]}constructor(){super(),this.value=[],this.locations=[{id:Date.now()}]}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e))}willUpdate(...e){super.willUpdate(...e),this.value&&this.value.filter(t=>!t.id)&&(this.value=[...this.value.map(t=>({...t,id:t.grid_meta_id}))]),this.updateLocationList()}firstUpdated(...e){super.firstUpdated(...e),this.internals.setFormValue(JSON.stringify(this.value))}updated(e){var t,s;if(e.has("value")){const o=e.get("value");o&&(o==null?void 0:o.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewLocation()}if(e.has("locations")){const o=e.get("locations");o&&(o==null?void 0:o.length)!==((s=this.locations)==null?void 0:s.length)&&this.focusNewLocation()}}focusNewLocation(){const e=this.shadowRoot.querySelectorAll("dt-location-map-item");e&&e.length&&e[e.length-1].dispatchEvent(new Event("autofocus"))}updateLocationList(){!this.disabled&&(this.open||!this.value||!this.value.length)?(this.open=!0,this.locations=[...(this.value||[]).filter(e=>e.label),{id:Date.now()}]):this.locations=[...(this.value||[]).filter(e=>e.label)]}selectLocation(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),s={...e.detail.metadata,id:Date.now()};this.value=[...(this.value||[]).filter(o=>o.label),s],this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}deleteItem(e){var a;const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),s=(a=e.detail)==null?void 0:a.metadata,o=s==null?void 0:s.grid_meta_id;o?this.value=(this.value||[]).filter(n=>n.grid_meta_id!==o):this.value=(this.value||[]).filter(n=>n.lat!==s.lat&&n.lng!==s.lng),this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}addNew(){this.open=!0,this.updateLocationList()}renderItem(e){return d`
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

      ${gt(this.locations||[],e=>e.id,(e,t)=>this.renderItem(e,t))}
      ${this.open?null:d`<button @click="${this.addNew}">Add New</button>`}
    `}}window.customElements.define("dt-location-map",vo);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ue=i=>i??C;class yo extends j{static get styles(){return[...super.styles,S`
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
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0},oldValue:{type:String},min:{type:Number},max:{type:Number},loading:{type:Boolean},saved:{type:Boolean},onchange:{type:String}}}connectedCallback(){super.connectedCallback(),this.oldValue=this.value}_checkValue(e){return!(e<this.min||e>this.max)}async onChange(e){if(this._checkValue(e.target.value)){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value},bubbles:!0,composed:!0});this.value=e.target.value,this._field.setCustomValidity(""),this.dispatchEvent(t),this.api=new ze(this.nonce,`${this.apiRoot}`)}else e.currentTarget.value=""}handleError(e="An error occurred."){let t=e;t instanceof Error?(console.error(t),t=t.message):console.error(t),this.error=t,this._field.setCustomValidity(t),this.invalid=!0,this.value=this.oldValue}render(){return d`
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
    `}}window.customElements.define("dt-number",yo);class wo extends j{static get styles(){return[...super.styles,S`
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
          margin: 0 0 1rem;
          padding: 0.53rem;
          padding-inline-end: 1.6rem;
          transition: border-color 0.25s ease-in-out;
          transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
          box-sizing: border-box;
          width: 100%;
          text-transform: none;
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
          inset-inline-end: 2.5rem;
        }
      `]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},value:{type:String,reflect:!0},color:{type:String,state:!0},onchange:{type:String}}}updateColor(){if(this.value&&this.options){const e=this.options.filter(t=>t.id===this.value);e&&e.length&&(this.color=e[0].color)}}isColorSelect(){return(this.options||[]).reduce((e,t)=>e||t.color,!1)}willUpdate(e){super.willUpdate(e),e.has("value")&&this.updateColor()}_change(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}render(){return d`
      ${this.labelTemplate()}

      <div class="container" dir="${this.RTL?"rtl":"ltr"}">
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
    `}}window.customElements.define("dt-single-select",wo);class Ua extends U{static get styles(){return S`
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
    `}}window.customElements.define("dt-exclamation-circle",Ua);class jt extends j{static get styles(){return[...super.styles,S`
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
          margin: 0 0 1.0666666667rem;
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
          cursor: copy;
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
      `]}static get properties(){return{...super.properties,id:{type:String},type:{type:String},placeholder:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const s=this.internals.form.querySelector("button");s&&s.click()}}_validateRequired(){const{value:e}=this,t=this.shadowRoot.querySelector("input");e===""&&this.required?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",t)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return d`
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
          class="${z(this.classes)}"
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
    `}}window.customElements.define("dt-text",jt);class _o extends j{static get styles(){return[...super.styles,S`
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
          margin: 0 0 1.0666666667rem;
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
          class="${z(this.classes)}"
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
    `}}window.customElements.define("dt-textarea",_o);class $o extends j{static get styles(){return[...super.styles,S`
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
    `}}window.customElements.define("dt-toggle",$o);class xo extends jt{static get styles(){return[...super.styles,S`
        :host {
          display: block;
        }
       .label-wrapper {
          display: flex;
          flex-direction: row;
          flex-wrap: wrap;
          width: 100%;
          align-items: center;
       }
       .add-btn {
          background-color: transparent;
          border: none;
       }
        .add-icon {
          color: var(--dt-comm-channel-add-btn-color, var(--success-color));
          height: 1.75rem;
          margin: 0 1rem
        }
        .input-group {
          display: flex;
          list-style-type: none;
          margin: 0;
          padding: 0;
        }
        .input-group li {
          display: flex;
          width: 100%;
          flex-direction: row;
          align-content: center;
          justify-content: center;
          align-items: center;
        }
        #path0_fill {
          fill: red;
        }

        .delete-button {
          background-color: transparent;
          border: none;
        }

        .delete-button svg {
          width: 1.5em;
          height: 1.5em;
          cursor: pointer;
        }

        .icon-overlay {
          inset-inline-end: 3rem;
          top: -15%;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0}}}_addClick(){const e={verified:!1,value:"",key:`new-${this.name}-${Math.floor(Math.random()*100)}`};this.value=[...this.value,e],this.requestUpdate()}_deleteField(e){const t=this.value.findIndex(l=>l.key===e.key);t!==-1&&this.value.splice(t,1),this.value=[...this.value];const{verified:s,value:o,...a}=e,n={...a,delete:!0},r=new CustomEvent("change",{detail:{field:this.name,oldValue:n,newValue:this.value}});this.dispatchEvent(r),this.requestUpdate()}labelTemplate(){return this.label?d`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon?null:d`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
      <button class="add-btn" @click=${this._addClick}>
        <svg class="add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 10h-4V6a2 2 0 0 0-4 0l.071 4H6a2 2 0 0 0 0 4l4.071-.071L10 18a2 2 0 0 0 4 0v-4.071L18 14a2 2 0 0 0 0-4z"></svg>
      </button>
    `:""}_inputFieldTemplate(e){const s=e.key===`new-${this.name}-0`?"":d`
      <button class="delete-button"  @click=${()=>this._deleteField(e)}>
        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
          <path
            id="path0_fill"
            fill-rule="evenodd"
            d="M 14 7C 14 10.866 10.866 14 7 14C 3.13403 14 0 10.866 0 7C 0 3.13401 3.13403 0 7 0C 10.866 0 14 3.13401 14 7ZM 9.51294 3.51299L 7 6.01299L 4.48706 3.51299L 3.5 4.49999L 6.01294 6.99999L 3.5 9.49999L 4.48706 10.487L 7 7.98699L 9.5 10.5L 10.4871 9.51299L 7.98706 6.99999L 10.5 4.49999L 9.51294 3.51299Z"
          />
        </svg>
      </button>
  `;return d`
      <div class="input-group">
        <input
          id="${e.key}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${z(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
          @keyup="${this.implicitFormSubmit}"
        />
        ${s}

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
    `}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e)),this.value=e,this.requestUpdate()}_change(e){const t=e.target.id,{value:s}=e.target,o=this.value;this.value.find((n,r)=>n.key===t?(o[r]={verified:!1,value:s,key:t},!0):!1);const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:o,onSuccess:n=>{n&&this._setFormValue(n[this.name])}}});this.value=o,this._setFormValue(this.value),this.dispatchEvent(a)}_renderInputFields(){return this.value==null||!this.value.length?(this.value=[{verified:!1,value:"",key:`new-${this.name}-0`}],this._inputFieldTemplate(this.value[0])):d`
      ${this.value.map(e=>this._inputFieldTemplate(e))}
    `}render(){return d`
     <div class="label-wrapper">
        ${this.labelTemplate()}
      </div>
      ${this._renderInputFields()}
    `}}window.customElements.define("dt-comm-channel",xo);class ko extends j{static get styles(){return S`
   :host {
        margin-bottom: 5px;
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
      }
  `}static get properties(){return{buttons:{type:Array},selectedButtons:{type:Array},value:{type:Array,reflect:!0},icon:{type:String},isModal:{type:Array}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--rounded":this.rounded},t=`dt-button--${this.context}`;return e[t]=!0,e}constructor(){super(),this.buttons=[],this.selectedButtons=[],this.value=[],this.custom=!0}connectedCallback(){super.connectedCallback(),this.selectedButtons=this.value?this.value.map(e=>({value:e})):[]}_handleButtonClick(e,t){var a;const s=e.target.value;s==="milestone_baptized"&&this.isModal&&this.isModal.includes(t)&&!((a=this.value)!=null&&a.includes("milestone_baptized"))&&(document.querySelector("#baptized-modal").shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden");const o=this.selectedButtons.findIndex(n=>n.value===s);o>-1?(this.selectedButtons.splice(o,1),this.selectedButtons.push({value:`-${s}`})):this.selectedButtons.push({value:s}),this.value=this.selectedButtons.filter(n=>!n.value.startsWith("-")).map(n=>n.value),this.dispatchEvent(new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:this.selectedButtons}})),this._setFormValue(this.value),this.requestUpdate()}_inputKeyDown(e){switch(e.keyCode||e.which){case 13:this._handleButtonClick(e);break}}render(){return d`
       ${this.labelTemplate()}
       ${this.loading?d`<dt-spinner class="icon-overlay"></dt-spinner>`:null}
        ${this.saved?d`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}
       <div class="button-group">
        ${this.buttons.map(e=>Object.keys(e).map(s=>{const a=this.selectedButtons.some(n=>n.value===s&&!n.delete)?"success":"disabled";return d`
            <dt-button
            custom
              id=${s}
              type="success"
              context=${a}
              .value=${s||this.value}
              @click="${n=>this._handleButtonClick(n,e[s].label)}"
              @keydown="${this._inputKeyDown}"
              role="button"
              >
               <span class="icon">
                ${e[s].icon?d`<img src="${e[s].icon}" alt="${this.iconAltText}" />`:null}
            </span>
             ${e[s].label}</dt-button>
          `}))}
        </div>
    `}}window.customElements.define("dt-multiselect-buttons-group",ko);class So extends N{static get styles(){return S`
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
      <div role="alert" class=${z(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable?d`
              <button @click="${this._dismiss}" class="toggle">${e}</button>
            `:null}
      </div>
    `}}window.customElements.define("dt-alert",So);class Eo extends N{static get styles(){return S`
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
    `}static get properties(){return{postType:{type:String},postTypeLabel:{type:String},posttypesettings:{type:Object,attribute:!0},posts:{type:Array},total:{type:Number},columns:{type:Array},sortedBy:{type:String},loading:{type:Boolean,default:!0},offset:{type:Number},showArchived:{type:Boolean,default:!1},showFieldsSelector:{type:Boolean,default:!1},showBulkEditSelector:{type:Boolean,default:!1},nonce:{type:String},payload:{type:Object},favorite:{type:Boolean},initialLoadPost:{type:Boolean,default:!1},loadMore:{type:Boolean,default:!1},headerClick:{type:Boolean,default:!1}}}constructor(){super(),this.sortedBy="name",this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.sortedColumns},this.initalLoadPost=!1,this.initalLoadPost||(this.posts=[],this.limit=100)}firstUpdated(){this.postTypeSettings=window.post_type_fields,this.sortedColumns=this.columns.includes("favorite")?["favorite",...this.columns.filter(e=>e!=="favorite")]:this.columns,this.style.setProperty("--number-of-columns",this.columns.length-1)}async _getPosts(e){const t=await new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:e,onSuccess:s=>{this.initalLoadPost&&this.loadMore&&(this.posts=[...this.posts,...s],this.postsLength=this.posts.length,this.total=s.length,this.loadMore=!1),this.initalLoadPost||(this.posts=[...s],this.offset=this.posts.length,this.initalLoadPost=!0,this.total=s.length),this.headerClick&&(this.posts=s,this.offset=this.posts.length,this.headerClick=!1),this.total=s.length},onError:s=>{console.warn(s)}}});this.dispatchEvent(t)}_headerClick(e){const t=e.target.dataset.id;this.sortedBy===t?t.startsWith("-")?this.sortedBy=t.replace("-",""):this.sortedBy=`-${t}`:this.sortedBy=t,this.payload={sort:this.sortedBy,overall_status:["-closed"],limit:this.limit,fields_to_return:this.columns},this.headerClick=!0,this._getPosts(this.payload)}static _rowClick(e){window.open(e,"_self")}_bulkEdit(){this.showBulkEditSelector=!this.showBulkEditSelector}_fieldsEdit(){this.showFieldsSelector=!this.showFieldsSelector}_toggleShowArchived(){if(this.showArchived=!this.showArchived,this.headerClick=!0,this.showArchived){const{overall_status:e,offset:t,...s}=this.payload;this.payload=s}else this.payload.overall_status=["-closed"];this._getPosts(this.payload)}_sortArrowsClass(e){return this.sortedBy===e?"sortedBy":""}_sortArrowsToggle(e){return this.sortedBy!==`-${e}`?`-${e}`:e}_headerTemplate(){return this.postTypeSettings?d`
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
      `:null}_rowTemplate(){if(this.posts&&Array.isArray(this.posts)){const e=this.posts.map((t,s)=>this.showArchived||!this.showArchived&&t.overall_status!=="closed"?d`
              <tr class="dnd-moved" data-link="${t.permalink}" @click=${()=>this._rowClick(t.permalink)}>
                <td class="bulk_edit_checkbox no-title">
                  <input type="checkbox" name="bulk_edit_id" .value="${t.ID}" />
                </td>
                <td class="no-title line-count">${s+1}.</td>
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
            ${Ne(e[t],s=>d`<li>
                  ${this.postTypeSettings[t].default[s].label}
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
                class="${z({"icon-star":!0,selected:e.favorite})}"
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
      />`:null}_fieldsListTemplate(){return gt(Object.keys(this.postTypeSettings).sort((e,t)=>{const s=this.postTypeSettings[e].name.toUpperCase(),o=this.postTypeSettings[t].name.toUpperCase();return s<o?-1:s>o?1:0}),e=>e,e=>this.postTypeSettings[e].hidden?null:d`<li class="list-field-picker-item">
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
            ${T("Choose which fields to display as columns in the list")}
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
      </div>`:null}_updateFields(e){const t=e.target.value,s=this.columns;s.includes(t)?(s.filter(o=>o!==t),s.splice(s.indexOf(t),1)):s.push(t),this.columns=s,this.style.setProperty("--number-of-columns",this.columns.length-1),this.requestUpdate()}_bulkSelectorTemplate(){return this.showBulkEditSelector?d`<div id="bulk_edit_picker" class="list_action_section">
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${T(h`Select all the ${this.postType} you want to update from the list, and update them below`)}
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
    `,s=d`<svg height='100px' width='100px'  fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M94.4,63c0-5.7-3.6-10.5-8.6-12.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5     s3.6,10.5,8.6,12.5v17.2c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5C90.9,73.6,94.4,68.7,94.4,63z M81,66.7     c-2,0-3.7-1.7-3.7-3.7c0-2,1.7-3.7,3.7-3.7s3.7,1.7,3.7,3.7C84.7,65.1,83.1,66.7,81,66.7z"></path><path d="M54.8,24.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v17.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v43.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V49.5c5-1.9,8.6-6.8,8.6-12.5S59.8,26.5,54.8,24.5z M50,40.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C53.7,39.1,52,40.7,50,40.7z"></path><path d="M23.8,50.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v17.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5c5-1.9,8.6-6.8,8.6-12.5S28.8,52.5,23.8,50.5z M19,66.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C22.7,65.1,21,66.7,19,66.7z"></path></g></g></g></svg>`;return d`
      <div class="section">
        <div class="header">
          <div class="section-header">
            <span
              class="section-header posts-header"
              style="display: inline-block"
              >${T(h`${this.postTypeLabel?this.postTypeLabel:this.postType} List`)}</span
            >
          </div>
          <span class="filter-result-text"
            >${T(h`Showing ${this.total} of ${this.total}`)}</span
          >

          <button
            class="bulkToggle toggleButton"
            id="bulk_edit_button"
            @click=${this._bulkEdit}
          >
            ${t} ${T("Bulk Edit")}
          </button>
          <button
            class="fieldsToggle toggleButton"
            id="fields_edit_button"
            @click=${this._fieldsEdit}
          >
            ${s} ${T("Fields")}
          </button>

          <dt-toggle
            name="showArchived"
            label=${T("Show Archived")}
            ?checked=${this.showArchived}
            hideIcons
            onchange=${this._toggleShowArchived}
            @click=${this._toggleShowArchived}
          ></dt-toggle>
        </div>

        ${this._fieldsSelectorTemplate()} ${this._bulkSelectorTemplate()}
        <table class="table-contacts ${z(e)}">
          ${this._headerTemplate()}
          ${this.posts?this._rowTemplate():T("Loading")}
        </table>
          ${this.total>=100?d`<div class="text-center"><dt-button buttonStyle=${JSON.stringify({margin:"0"})} class="loadMoreButton btn btn-primary" @click=${this._handleLoadMore}>Load More</dt-button></div>`:""}
      </div>
    `}}window.customElements.define("dt-list",Eo);class Co extends N{static get styles(){return S`
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
    `:C}render(){return d`
      <section>
        ${this.renderHeading()}
        <div part="body" class="section-body ${this.collapsed?"collapsed":null}">
          <slot></slot>
        </div>
      </section>
    `}}window.customElements.define("dt-tile",Co);class Dt{constructor(e,t,s,o="wp-json"){this.postType=e,this.postId=t,this.nonce=s,this.apiRoot=`${o}/`.replace("//","/"),this.api=new ze(this.nonce,this.apiRoot),this.autoSaveComponents=["dt-connection","dt-date","dt-location","dt-multi-select","dt-number","dt-single-select","dt-tags","dt-text","dt-textarea","dt-toggle","dt-comm-channel","dt-multiselect-buttons-group","dt-list","dt-button"],this.dynamicLoadComponents=["dt-connection","dt-tags","dt-modal","dt-list","dt-button"]}initialize(){this.postId&&this.enableAutoSave();const e=document.querySelector("dt-button#create-post-button");e&&e.addEventListener("send-data",this.processFormSubmission.bind(this));const t=document.querySelector("dt-list");t&&t.tagName.toLowerCase()==="dt-list"&&t.addEventListener("customClick",this.handleCustomClickEvent.bind(this)),this.attachLoadEvents()}async attachLoadEvents(e){const t=document.querySelectorAll(e||this.dynamicLoadComponents.join(",")),s=Array.from(t).filter(o=>o.tagName.toLowerCase()==="dt-modal"&&o.classList.contains("duplicate-detected"));s.length>0&&this.checkDuplicates(t,s),t&&t.forEach(o=>o.addEventListener("dt:get-data",this.handleGetDataEvent.bind(this)))}async checkDuplicates(e,t){const s=document.querySelector("dt-modal.duplicate-detected");if(s){const o=s.shadowRoot.querySelector(".duplicates-detected-button");o&&(o.style.display="none");const a=await this.api.checkDuplicateUsers(this.postType,this.postId);t&&a.ids.length>0&&o&&(o.style.display="block")}}enableAutoSave(e){const t=document.querySelectorAll(e||this.autoSaveComponents.join(","));t&&t.forEach(s=>{s.tagName.toLowerCase()==="dt-button"&&s.addEventListener("customClick",this.handleCustomClickEvent.bind(this)),s.addEventListener("change",this.handleChangeEvent.bind(this))})}async handleCustomClickEvent(e){const t=e.detail;if(t){const{field:s,toggleState:o}=t;e.target.setAttribute("loading",!0);let a;s.startsWith("favorite-button")?(a={favorite:o},/\d$/.test(s)&&(this.postId=s.split("-").pop())):s.startsWith("following-button")||s.startsWith("follow-button")?a={follow:{values:[{value:"1",delete:o}]},unfollow:{values:[{value:"1",delete:!o}]}}:console.log("No match found for the field");try{const n=await this.api.updatePost(this.postType,this.postId,a)}catch(n){console.error(n),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",n.message||n.toString())}}}async processFormSubmission(e){const t=e.detail,{newValue:s}=t;try{const o=await this.api.createPost(this.postType,s.el);o&&(window.location=o.permalink),e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(o){console.error(o),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",o.message||o.toString())}}async handleGetDataEvent(e){const t=e.detail;if(t){const{field:s,query:o,onSuccess:a,onError:n}=t;try{const r=e.target.tagName.toLowerCase();let l=[];switch(r){case"dt-button":l=await this.api.getContactInfo(this.postType,this.postId);break;case"dt-list":l=(await this.api.fetchPostsList(this.postType,o)).posts;break;case"dt-connection":{const u=t.postType||this.postType,b=await this.api.listPostsCompact(u,o),g={...b,posts:b.posts.filter(v=>v.ID!==parseInt(this.postId,10))};g!=null&&g.posts&&(l=g==null?void 0:g.posts.map(v=>({id:v.ID,label:v.name,link:v.permalink,status:v.status})));break}case"dt-tags":default:l=await this.api.getMultiSelectValues(this.postType,s,o),l=l.map(u=>({id:u,label:u}));break}a(l)}catch(r){n(r)}}}async handleChangeEvent(e){const t=e.detail;if(t){const{field:s,newValue:o,oldValue:a,remove:n}=t,r=e.target.tagName.toLowerCase(),l=Dt.convertValue(r,o,a);e.target.setAttribute("loading",!0);try{let u;switch(r){case"dt-users-connection":{if(n===!0){u=await this.api.removePostShare(this.postType,this.postId,l);break}u=await this.api.addPostShare(this.postType,this.postId,l);break}default:{u=await this.api.updatePost(this.postType,this.postId,{[s]:l}),r==="dt-comm-channel"&&t.onSuccess&&t.onSuccess(u);break}}e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(u){console.error(u),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",u.message||u.toString())}}}static convertValue(e,t,s){let o=t;if(t)switch(e){case"dt-toggle":typeof t=="string"&&(o=t.toLowerCase()==="true");break;case"dt-multi-select":case"dt-tags":typeof t=="string"&&(o=[t]),o={values:o.map(a=>{if(typeof a=="string"){const r={value:a};return a.startsWith("-")&&(r.delete=!0,r.value=a.substring(1)),r}const n={value:a.id};return a.delete&&(n.delete=a.delete),n}),force_values:!1};break;case"dt-users-connection":{const a=[],n=new Map(s.map(r=>[r.id,r]));for(const r of o){const l=n.get(r.id),u={id:r.id,changes:{}};if(l){let b=!1;const g=new Set([...Object.keys(l),...Object.keys(r)]);for(const v of g)r[v]!==l[v]&&(u.changes[v]=Object.prototype.hasOwnProperty.call(r,v)?r[v]:void 0,b=!0);if(b){a.push(u);break}}else{u.changes={...r},a.push(u);break}}o=a[0].id;break}case"dt-connection":case"dt-location":typeof t=="string"&&(o=[{id:t}]),o={values:o.map(a=>{const n={value:a.id};return a.delete&&(n.delete=a.delete),n}),force_values:!1};break;case"dt-multiselect-buttons-group":typeof t=="string"&&(o=[{id:t}]),o={values:o.map(a=>a.value.startsWith("-")?{value:a.value.replace("-",""),delete:!0}:a),force_values:!1};break;case"dt-comm-channel":{const a=t.length;s&&s.delete===!0?o=[s]:t[a-1].key===""||t[a-1].key.startsWith("new-contact")?(o=[],t.forEach(n=>{o.push({value:n.value})})):o=t;break}}return o}}const Ba={s226be12a5b1a27e8:"ሰነዶቹን ያንብቡ",s33f85f24c0f5f008:"አስቀምጥ",s36cb242ac90353bc:"መስኮች",s41cb4006238ebd3b:"የጅምላ አርትዕ",s5e8250fb85d64c23:"ገጠመ",s625ad019db843f94:"ተጠቀም",sac83d7f9358b43db:h`${0} ዝርዝር`,sbf1ca928ec1deb62:"ተጨማሪ እገዛ ይፈልጋሉ?",sd1a8dc951b2b6a98:"በዝርዝሩ ውስጥ እንደ ዓምዶች የትኞቹን መስኮች እንደሚያሳዩ ይምረጡ",sf9aee319a006c9b4:"አክል",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},qa=Object.freeze(Object.defineProperty({__proto__:null,templates:Ba},Symbol.toStringTag,{value:"Module"})),Va={s04ceadb276bbe149:"خيارات التحميل...",s226be12a5b1a27e8:"اقرأ الوثائق",s29e25f5e4622f847:"افتح",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"غلق",s625ad019db843f94:"استخدام",s9d51bfd93b5dbeca:"عرض المحفوظات",sac83d7f9358b43db:h`${0}قائمة الأعضاء`,sb1bd536b63e9e995:"المجال الخاص: أنا فقط أستطيع رؤية محتواه",sb59d68ed12d46377:"جار التحميل",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",scb9a1ff437efbd2a:h`حَدِّد جميع ${0} التي تريد تحديثها من القائمة ، وقم بتحديثها أدناه`,sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",seafe6ef133ede7da:h`عرض 1 of ${0}`,sf9aee319a006c9b4:"لأضف",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ha=Object.freeze(Object.defineProperty({__proto__:null,templates:Va},Symbol.toStringTag,{value:"Module"})),Wa={s226be12a5b1a27e8:"اقرأ الوثائق",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"أغلق",s625ad019db843f94:"استخدام",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",sf9aee319a006c9b4:"إضافة",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ga=Object.freeze(Object.defineProperty({__proto__:null,templates:Wa},Symbol.toStringTag,{value:"Module"})),Ka={s226be12a5b1a27e8:"Прочетете документацията",s33f85f24c0f5f008:"Запазете",s36cb242ac90353bc:"Полета",s41cb4006238ebd3b:"Групово редактиране",s5e8250fb85d64c23:"Близо",s625ad019db843f94:"Използвайте",sbf1ca928ec1deb62:"Имате нужда от повече помощ?",sd1a8dc951b2b6a98:"Изберете кои полета да се показват като колони в списъка",sf9aee319a006c9b4:"Добавяне",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Za=Object.freeze(Object.defineProperty({__proto__:null,templates:Ka},Symbol.toStringTag,{value:"Module"})),Ja={s226be12a5b1a27e8:"নথিপত্রাদি পাঠ করুন",s33f85f24c0f5f008:"সংরক্ষণ করুন",s36cb242ac90353bc:"ক্ষেত্র",s41cb4006238ebd3b:"বাল্ক এডিট",s5e8250fb85d64c23:"বন্ধ",s625ad019db843f94:"ব্যবহার",sbf1ca928ec1deb62:"আরও সাহায্য প্রয়োজন?",sd1a8dc951b2b6a98:"তালিকার কলাম হিসাবে কোন ক্ষেত্রগুলি প্রদর্শিত হবে তা চয়ন করুন",sf9aee319a006c9b4:"অ্যাড",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Qa=Object.freeze(Object.defineProperty({__proto__:null,templates:Ja},Symbol.toStringTag,{value:"Module"})),Xa={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitajte dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate više pomoći?",scb9a1ff437efbd2a:h`Odaberite sve ${0} koje želite ažurirati sa liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Odaberite koja polja će se prikazati kao kolone na listi",seafe6ef133ede7da:h`Prikazuje se 1 od ${0}`,sf9aee319a006c9b4:"Dodati",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Ya=Object.freeze(Object.defineProperty({__proto__:null,templates:Xa},Symbol.toStringTag,{value:"Module"})),en={s226be12a5b1a27e8:"Přečtěte si dokumentaci",s33f85f24c0f5f008:"Uložit",s36cb242ac90353bc:"Pole",s41cb4006238ebd3b:"Hromadná úprava",s5e8250fb85d64c23:"Zavřít",s625ad019db843f94:"Použití",sbf1ca928ec1deb62:"Potřebujete další pomoc?",sd1a8dc951b2b6a98:"Vyberte pole, která chcete v seznamu zobrazit jako sloupce",sf9aee319a006c9b4:"Přidat",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},tn=Object.freeze(Object.defineProperty({__proto__:null,templates:en},Symbol.toStringTag,{value:"Module"})),sn={s226be12a5b1a27e8:"Lesen Sie die Dokumentation",s33f85f24c0f5f008:"Speichern",s36cb242ac90353bc:"Felder",s41cb4006238ebd3b:"Im Stapel bearbeiten",s5e8250fb85d64c23:"Schließen",s625ad019db843f94:"Verwenden",sbf1ca928ec1deb62:"Benötigen Sie weitere Hilfe?",sd1a8dc951b2b6a98:"Wählen Sie aus, welche Felder in der Liste als Spalte angezeigt werden sollen",sf9aee319a006c9b4:"Hinzufügen",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},on=Object.freeze(Object.defineProperty({__proto__:null,templates:sn},Symbol.toStringTag,{value:"Module"})),an={s226be12a5b1a27e8:"Διαβάστε την τεκμηρίωση",s33f85f24c0f5f008:"Αποθήκευση",s36cb242ac90353bc:"Πεδία",s41cb4006238ebd3b:"Μαζική Επεξεργασία",s5e8250fb85d64c23:"Κλείσιμο",s625ad019db843f94:"Χρήση",sbf1ca928ec1deb62:"Χρειάζεστε περισσότερη βοήθεια;",sd1a8dc951b2b6a98:"Επιλέξτε ποια πεδία θα εμφανίζονται ως στήλες στη λίστα",sf9aee319a006c9b4:"Προσθήκη",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nn=Object.freeze(Object.defineProperty({__proto__:null,templates:an},Symbol.toStringTag,{value:"Module"})),rn={sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",sf9aee319a006c9b4:"Add",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog",s33f85f24c0f5f008:"Save",s49730f3d5751a433:"Loading...",s625ad019db843f94:"Use",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ln=Object.freeze(Object.defineProperty({__proto__:null,templates:rn},Symbol.toStringTag,{value:"Module"})),cn={s8900c9de2dbae68b:"No hay opciones disponibles",sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sf9aee319a006c9b4:"Añadir",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sb9b8c412407d5691:"This is where the bulk edit form will go.",sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog"},dn=Object.freeze(Object.defineProperty({__proto__:null,templates:cn},Symbol.toStringTag,{value:"Module"})),un={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Leer la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},hn=Object.freeze(Object.defineProperty({__proto__:null,templates:un},Symbol.toStringTag,{value:"Module"})),pn={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Lee la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:h`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:h`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},fn=Object.freeze(Object.defineProperty({__proto__:null,templates:pn},Symbol.toStringTag,{value:"Module"})),bn={s04ceadb276bbe149:"در حال بارگیری گزینه‌ها...",s226be12a5b1a27e8:"راهنمای سایت",s29e25f5e4622f847:"جعبه محاوره ای را باز کنید",s33f85f24c0f5f008:"صرفه جویی",s36cb242ac90353bc:"حوزه‌ها",s41cb4006238ebd3b:"ویرایش انبوه",s5e8250fb85d64c23:"بستن",s625ad019db843f94:"استفاده کنید",s9d51bfd93b5dbeca:"نمایش بایگانی شده",sac83d7f9358b43db:h`لیست ${0}`,sb1bd536b63e9e995:"زمینه خصوصی: فقط من می توانم محتوای آن را داشته باشم",sb59d68ed12d46377:"بارگیری",sbf1ca928ec1deb62:"آیا به راهنمایی بیشتری نیاز دارید؟",scb9a1ff437efbd2a:h`همۀ ${0} مورد نظر برای به روزرسانی را از لیست انتخاب کنید و آن‌ها را در زیر به روز کنید`,sd1a8dc951b2b6a98:"انتخاب کنید که کدام یک از حوزه‌ها به‌عنوان ستون در لیست نمایش داده شوند",seafe6ef133ede7da:h`نمایش 1 از ${0}`,sf9aee319a006c9b4:"افزودن",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},gn=Object.freeze(Object.defineProperty({__proto__:null,templates:bn},Symbol.toStringTag,{value:"Module"})),mn={s04ceadb276bbe149:"Chargement les options...",s226be12a5b1a27e8:"Lire la documentation",s29e25f5e4622f847:"Ouvrir la boîte de dialogue",s33f85f24c0f5f008:"sauver",s36cb242ac90353bc:"Champs",s41cb4006238ebd3b:"Modification groupée",s5e8250fb85d64c23:"Fermer",s625ad019db843f94:"Utiliser",s9d51bfd93b5dbeca:"Afficher Archivé",sac83d7f9358b43db:h`${0} Liste`,sb1bd536b63e9e995:"Champ privé : je suis le seul à voir son contenu",sb59d68ed12d46377:"Chargement",sbf1ca928ec1deb62:"Besoin d'aide ?",scb9a1ff437efbd2a:h`Sélectionnez tous les ${0} que vous souhaitez mettre à jour dans la liste et mettez-les à jour ci-dessous`,sd1a8dc951b2b6a98:"Choisissez les champs à afficher sous forme de colonnes dans la liste",seafe6ef133ede7da:h`Affichage de 1 sur ${0}`,sf9aee319a006c9b4:"Ajouter",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},vn=Object.freeze(Object.defineProperty({__proto__:null,templates:mn},Symbol.toStringTag,{value:"Module"})),yn={s226be12a5b1a27e8:"डॉक्यूमेंटेशन पढ़ें",s33f85f24c0f5f008:"बचाना",s36cb242ac90353bc:"खेत",s41cb4006238ebd3b:"थोक संपादित",s5e8250fb85d64c23:"बंद",s625ad019db843f94:"उपयोग",sbf1ca928ec1deb62:"क्या और मदद चाहिये?",sd1a8dc951b2b6a98:"सूची में कॉलम के रूप में प्रदर्शित करने के लिए कौन से फ़ील्ड चुनें",sf9aee319a006c9b4:"जोडें",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},wn=Object.freeze(Object.defineProperty({__proto__:null,templates:yn},Symbol.toStringTag,{value:"Module"})),_n={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitaj dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Spremi",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvoriti",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate li pomoć?",scb9a1ff437efbd2a:h`Odaberite sve${0}koje želite ažurirati s liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Izaberite polja koja će se prikazivati kao stupci na popisu",seafe6ef133ede7da:h`Prikazuje se 1 od${0}`,sf9aee319a006c9b4:"Dodaj",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},$n=Object.freeze(Object.defineProperty({__proto__:null,templates:_n},Symbol.toStringTag,{value:"Module"})),xn={s226be12a5b1a27e8:"Olvasd el a dokumentációt",s33f85f24c0f5f008:"Megment",s36cb242ac90353bc:"Mezők",s41cb4006238ebd3b:"Tömeges Szerkesztés",s5e8250fb85d64c23:"Bezár",s625ad019db843f94:"Használ",sbf1ca928ec1deb62:"Több segítség szükséges?",sd1a8dc951b2b6a98:"Válassza ki, melyik mezők jelenjenek meg oszlopként a listában",sf9aee319a006c9b4:"Hozzáadás",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},kn=Object.freeze(Object.defineProperty({__proto__:null,templates:xn},Symbol.toStringTag,{value:"Module"})),Sn={s226be12a5b1a27e8:"Bacalah dokumentasi",s33f85f24c0f5f008:"Simpan",s36cb242ac90353bc:"Larik",s41cb4006238ebd3b:"Edit Massal",s5e8250fb85d64c23:"Menutup",s625ad019db843f94:"Gunakan",sbf1ca928ec1deb62:"Perlukan bantuan lagi?",sd1a8dc951b2b6a98:"Pilih larik mana yang akan ditampilkan sebagai kolom dalam daftar",sf9aee319a006c9b4:"Tambah",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},En=Object.freeze(Object.defineProperty({__proto__:null,templates:Sn},Symbol.toStringTag,{value:"Module"})),Cn={s04ceadb276bbe149:"Caricando opzioni...",s226be12a5b1a27e8:"Leggi la documentazione",s29e25f5e4622f847:"Apri Dialogo",s33f85f24c0f5f008:"Salvare",s36cb242ac90353bc:"Campi",s41cb4006238ebd3b:"Modifica in blocco",s5e8250fb85d64c23:"Chiudi",s625ad019db843f94:"Uso",s9d51bfd93b5dbeca:"Visualizza Archiviati",sac83d7f9358b43db:h`${0} Lista`,sb1bd536b63e9e995:"Campo Privato: Solo io posso vedere i suoi contenuti",sb59d68ed12d46377:"Caricando",sbf1ca928ec1deb62:"Hai bisogno di ulteriore assistenza?",scb9a1ff437efbd2a:h`Seleziona tutti i ${0}vuoi aggiornare dalla lista e aggiornali sotto`,sd1a8dc951b2b6a98:"Scegli quali campi visualizzare come colonne nell'elenco",seafe6ef133ede7da:h`Visualizzando 1 di ${0}`,sf9aee319a006c9b4:"Inserisci",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Tn=Object.freeze(Object.defineProperty({__proto__:null,templates:Cn},Symbol.toStringTag,{value:"Module"})),An={s226be12a5b1a27e8:"ドキュメントを読む",s33f85f24c0f5f008:"セーブ",s36cb242ac90353bc:"田畑",s41cb4006238ebd3b:"一括編集",s5e8250fb85d64c23:"閉じる",s625ad019db843f94:"使用する",sbf1ca928ec1deb62:"もっと助けが必要ですか？",sd1a8dc951b2b6a98:"リストの列として表示するフィールドを選択します",sf9aee319a006c9b4:"追加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},On=Object.freeze(Object.defineProperty({__proto__:null,templates:An},Symbol.toStringTag,{value:"Module"})),Ln={s226be12a5b1a27e8:"문서 읽기",s33f85f24c0f5f008:"구하다",s36cb242ac90353bc:"필드",s41cb4006238ebd3b:"대량 수정",s5e8250fb85d64c23:"닫기",s625ad019db843f94:"사용",sbf1ca928ec1deb62:"더 많은 도움이 필요하신가요?",sd1a8dc951b2b6a98:"목록에서 어떤 필드를 표시할지 고르세요",sf9aee319a006c9b4:"추가",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Pn=Object.freeze(Object.defineProperty({__proto__:null,templates:Ln},Symbol.toStringTag,{value:"Module"})),In={s226be12a5b1a27e8:"Прочитај ја документацијата",s33f85f24c0f5f008:"Зачувај",s36cb242ac90353bc:"Полиња",s41cb4006238ebd3b:"Уреди повеќе",s5e8250fb85d64c23:"Затвори",s625ad019db843f94:"Користи",sbf1ca928ec1deb62:"Дали ти треба повеќе помош?",sd1a8dc951b2b6a98:"Избери кои полиња да се прикажат како колони во листата",sf9aee319a006c9b4:"Додади",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Mn=Object.freeze(Object.defineProperty({__proto__:null,templates:In},Symbol.toStringTag,{value:"Module"})),jn={s226be12a5b1a27e8:"कागदपत्रे वाचा.",s33f85f24c0f5f008:"जतन करा",s36cb242ac90353bc:"क्षेत्रे",s41cb4006238ebd3b:"बल्क एडिट करा",s5e8250fb85d64c23:"बंद करा",s625ad019db843f94:"वापर",sbf1ca928ec1deb62:"अधिक मदत आवश्यक आहे का?",sd1a8dc951b2b6a98:"यादीत कोणती क्षेत्रे स्तंभ म्हणून दर्शवली जावीत हे निवडा",sf9aee319a006c9b4:"समाविष्ट करा",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Dn=Object.freeze(Object.defineProperty({__proto__:null,templates:jn},Symbol.toStringTag,{value:"Module"})),zn={s226be12a5b1a27e8:"စာရွက်စာတမ်းများကိုဖတ်ပါ",s33f85f24c0f5f008:"သိမ်းဆည်းပါ",s36cb242ac90353bc:"နယ်ပယ်ဒေသများ",s5e8250fb85d64c23:"ပိတ်သည်",s625ad019db843f94:"အသုံးပြုပါ",sbf1ca928ec1deb62:"နောက်ထပ်အကူအညီလိုပါသလား။",sd1a8dc951b2b6a98:"စာရင်းရှိကော်လံများအနေဖြင့်ဖော်ပြမည့်မည်သည့်နယ်ပယ်ဒေသများကိုရွေးချယ်ပါ",sf9aee319a006c9b4:"ထည့်ပါ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Nn=Object.freeze(Object.defineProperty({__proto__:null,templates:zn},Symbol.toStringTag,{value:"Module"})),Rn={s226be12a5b1a27e8:"कागजात पढ्नुहोस्",s33f85f24c0f5f008:"सुरक्षित गर्नुहोस",s36cb242ac90353bc:"क्षेत्रहरू",s41cb4006238ebd3b:"थोक सम्पादन",s5e8250fb85d64c23:"बन्द गर्नुहोस",s625ad019db843f94:"प्रयोग गर्नुहोस्",sbf1ca928ec1deb62:"थप मद्दत चाहिन्छ?",sd1a8dc951b2b6a98:"सूचीमा स्तम्भहरूको रूपमा कुन क्षेत्रहरू प्रदर्शन गर्ने छनौट गर्नुहोस्",sf9aee319a006c9b4:"थप",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Fn=Object.freeze(Object.defineProperty({__proto__:null,templates:Rn},Symbol.toStringTag,{value:"Module"})),Un={s04ceadb276bbe149:"aan het laden.....",s226be12a5b1a27e8:"Lees de documentatie",s29e25f5e4622f847:"Dialoogvenster openen",s33f85f24c0f5f008:"Opslaan",s36cb242ac90353bc:"Velden",s41cb4006238ebd3b:"Bulkbewerking",s5e8250fb85d64c23:"sluit",s625ad019db843f94:"Gebruiken",sac83d7f9358b43db:h`${0} Lijst`,sb1bd536b63e9e995:"Privéveld: alleen ik kan de inhoud zien",sb59d68ed12d46377:"aan het laden",sbf1ca928ec1deb62:"Meer hulp nodig?",sd1a8dc951b2b6a98:"Kies welke velden u als kolommen in de lijst wilt weergeven",seafe6ef133ede7da:h`1 van ${0} laten zien`,sf9aee319a006c9b4:"Toevoegen",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,s9d51bfd93b5dbeca:"Show Archived"},Bn=Object.freeze(Object.defineProperty({__proto__:null,templates:Un},Symbol.toStringTag,{value:"Module"})),qn={s226be12a5b1a27e8:"ਦਸਤਾਵੇਜ਼ ਪੜ੍ਹੋ",s33f85f24c0f5f008:"ਸੇਵ",s36cb242ac90353bc:"ਖੇਤਰ",s41cb4006238ebd3b:"ਥੋਕ ਸੰਪਾਦਨ",s5e8250fb85d64c23:"ਬੰਦ ਕਰੋ",s625ad019db843f94:"ਵਰਤੋਂ",sbf1ca928ec1deb62:"ਹੋਰ ਮਦਦ ਦੀ ਲੋੜ ਹੈ?",sd1a8dc951b2b6a98:"ਸੂਚੀ ਵਿੱਚ ਕਾਲਮ ਦੇ ਰੂਪ ਵਿੱਚ ਪ੍ਰਦਰਸ਼ਿਤ ਕਰਨ ਲਈ ਕਿਹੜੇ ਖੇਤਰ ਚੁਣੋ",sf9aee319a006c9b4:"ਸ਼ਾਮਲ ਕਰੋ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Vn=Object.freeze(Object.defineProperty({__proto__:null,templates:qn},Symbol.toStringTag,{value:"Module"})),Hn={s226be12a5b1a27e8:"Przeczytaj dokumentację",s33f85f24c0f5f008:"Zapisać",s36cb242ac90353bc:"Pola",s41cb4006238ebd3b:"Edycja zbiorcza",s5e8250fb85d64c23:"Zamknij",s625ad019db843f94:"Posługiwać się",sbf1ca928ec1deb62:"Potrzebujesz pomocy?",sd1a8dc951b2b6a98:"Wybierz, które pola mają być wyświetlane jako kolumny na liście",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Wn=Object.freeze(Object.defineProperty({__proto__:null,templates:Hn},Symbol.toStringTag,{value:"Module"})),Gn={s226be12a5b1a27e8:"Leia a documentação",s33f85f24c0f5f008:"Salvar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edição em massa",s5e8250fb85d64c23:"Fechar",s625ad019db843f94:"Usar",sbf1ca928ec1deb62:"Precisa de mais ajuda?",sd1a8dc951b2b6a98:"Escolha quais campos exibir como colunas na lista",sf9aee319a006c9b4:"Adicionar",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Kn=Object.freeze(Object.defineProperty({__proto__:null,templates:Gn},Symbol.toStringTag,{value:"Module"})),Zn={s226be12a5b1a27e8:"Citiți documentația",s33f85f24c0f5f008:"Salvați",s36cb242ac90353bc:"Câmpuri",s41cb4006238ebd3b:"Editare masivă",s5e8250fb85d64c23:"Închide",s625ad019db843f94:"Utilizare",sbf1ca928ec1deb62:"Ai nevoie de mai mult ajutor?",sd1a8dc951b2b6a98:"Alegeți câmpurile care să fie afișate în coloane în listă",sf9aee319a006c9b4:"Adăuga",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Jn=Object.freeze(Object.defineProperty({__proto__:null,templates:Zn},Symbol.toStringTag,{value:"Module"})),Qn={s226be12a5b1a27e8:"Читать документацию",s33f85f24c0f5f008:"Сохранить",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Массовое редактирование",s5e8250fb85d64c23:"Закрыть",s625ad019db843f94:"Использовать",sbf1ca928ec1deb62:"Нужна дополнительная помощь?",sd1a8dc951b2b6a98:"Выберите, какие поля отображать как столбцы в списке",sf9aee319a006c9b4:"Добавить",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xn=Object.freeze(Object.defineProperty({__proto__:null,templates:Qn},Symbol.toStringTag,{value:"Module"})),Yn={s226be12a5b1a27e8:"Preberite dokumentacijo",s33f85f24c0f5f008:"Shrani",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Urejanje v velikem obsegu",s5e8250fb85d64c23:"Zapri",s625ad019db843f94:"Uporaba",sbf1ca928ec1deb62:"Potrebujete več pomoči?",sd1a8dc951b2b6a98:"Izberite, katera polja naj bodo prikazana kot stolpci na seznamu",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},er=Object.freeze(Object.defineProperty({__proto__:null,templates:Yn},Symbol.toStringTag,{value:"Module"})),tr={s226be12a5b1a27e8:"Pročitajte dokumentaciju",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"masovno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristiti",sbf1ca928ec1deb62:"Treba vam više pomoći?",sd1a8dc951b2b6a98:"Izaberite koja polja da se prikazuju kao kolone na listi",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},sr=Object.freeze(Object.defineProperty({__proto__:null,templates:tr},Symbol.toStringTag,{value:"Module"})),or={s04ceadb276bbe149:"Inapakia chaguo...",s226be12a5b1a27e8:"Soma nyaraka",s29e25f5e4622f847:"Fungua Kidirisha",s33f85f24c0f5f008:"Hifadhi",s36cb242ac90353bc:"Mashamba",s41cb4006238ebd3b:"Hariri kwa Wingi",s5e8250fb85d64c23:"Funga",s625ad019db843f94:"Tumia",s9d51bfd93b5dbeca:"Onyesha Kumbukumbu",sac83d7f9358b43db:h`Orodha ya${0}`,sb1bd536b63e9e995:"Sehemu ya Faragha: Ni mimi pekee ninayeweza kuona maudhui yake",sb59d68ed12d46377:"Inapakia",sbf1ca928ec1deb62:"Unahitaji msaada zaidi?",scb9a1ff437efbd2a:h`Chagua ${0} zote ungependa kusasisha kutoka kwenye orodha, na uzisasishe hapa chini.`,sd1a8dc951b2b6a98:"Chagua ni sehemu zipi zitaonyeshwa kama safu wima kwenye orodha",seafe6ef133ede7da:h`Inaonyesha 1 kati ya ${0}`,sf9aee319a006c9b4:"Ongeza",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},ir=Object.freeze(Object.defineProperty({__proto__:null,templates:or},Symbol.toStringTag,{value:"Module"})),ar={s226be12a5b1a27e8:"อ่านเอกสาร",s33f85f24c0f5f008:"บันทึก",s36cb242ac90353bc:"ฟิลด์",s41cb4006238ebd3b:"แก้ไขเป็นกลุ่ม",s5e8250fb85d64c23:"ปิด",s625ad019db843f94:"ใช้",sbf1ca928ec1deb62:"ต้องการความช่วยเหลือเพิ่มเติมหรือไม่?",sd1a8dc951b2b6a98:"เลือกฟิลด์ที่จะแสดงเป็นคอลัมน์ในรายการ",sf9aee319a006c9b4:"เพิ่ม",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nr=Object.freeze(Object.defineProperty({__proto__:null,templates:ar},Symbol.toStringTag,{value:"Module"})),rr={s226be12a5b1a27e8:"Basahin ang dokumentasyon",s33f85f24c0f5f008:"I-save",s36cb242ac90353bc:"Mga Field",s41cb4006238ebd3b:"Maramihang Pag-edit",s5e8250fb85d64c23:"Isara",s625ad019db843f94:"Gamitin",sbf1ca928ec1deb62:"Kailangan mo pa ba ng tulong?",sd1a8dc951b2b6a98:"Piliin kung aling mga field ang ipapakita bilang mga column sa listahan",sf9aee319a006c9b4:"Idagdag",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},lr=Object.freeze(Object.defineProperty({__proto__:null,templates:rr},Symbol.toStringTag,{value:"Module"})),cr={s04ceadb276bbe149:"Seçenekler Yükleniyor...",s226be12a5b1a27e8:"Belgeleri oku",s29e25f5e4622f847:"İletişim Kutusunu Aç",s33f85f24c0f5f008:"Kaydet",s36cb242ac90353bc:"Alanlar",s41cb4006238ebd3b:"Toplu Düzenleme",s5e8250fb85d64c23:"Kapat",s625ad019db843f94:"Kullan",s9d51bfd93b5dbeca:"Arşivlenmiş Göster",sac83d7f9358b43db:h`${0} Listesi`,sb1bd536b63e9e995:"Özel Alan: İçeriğini sadece ben görebilirim",sb59d68ed12d46377:"Yükleniyor",sbf1ca928ec1deb62:"Daha fazla yardıma ihtiyacınız var mı?",scb9a1ff437efbd2a:h`Listeden güncellemek istediğiniz tüm ${0} 'i seçin ve aşağıda güncelleyin`,sd1a8dc951b2b6a98:"Listede Hangi Alanların Sütun Olarak Görüntüleneceğini Seçin",seafe6ef133ede7da:h`Gösteriliyor 1 of ${0}`,sf9aee319a006c9b4:"Ekle",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},dr=Object.freeze(Object.defineProperty({__proto__:null,templates:cr},Symbol.toStringTag,{value:"Module"})),ur={s226be12a5b1a27e8:"Прочитайте документацію",s33f85f24c0f5f008:"Зберегти",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Масове редагування",s5e8250fb85d64c23:"Закрити",s625ad019db843f94:"Використати",sbf1ca928ec1deb62:"Потрібна додаткова допомога?",sd1a8dc951b2b6a98:"Виберіть, яке поле відображати у вигляді стовпців у списку",sf9aee319a006c9b4:"Додати",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},hr=Object.freeze(Object.defineProperty({__proto__:null,templates:ur},Symbol.toStringTag,{value:"Module"})),pr={s226be12a5b1a27e8:"Đọc tài liệu",s33f85f24c0f5f008:"Lưu",s36cb242ac90353bc:"Trường",s41cb4006238ebd3b:"Chỉnh sửa Hàng loạt",s5e8250fb85d64c23:"Đóng",s625ad019db843f94:"Sử dụng",sbf1ca928ec1deb62:"Bạn cần trợ giúp thêm?",sd1a8dc951b2b6a98:"Chọn các trường để hiển thị dưới dạng cột trong danh sách",sf9aee319a006c9b4:"Bổ sung",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},fr=Object.freeze(Object.defineProperty({__proto__:null,templates:pr},Symbol.toStringTag,{value:"Module"})),br={s226be12a5b1a27e8:"阅读文档",s33f85f24c0f5f008:"保存",s36cb242ac90353bc:"字段",s41cb4006238ebd3b:"批量编辑",s5e8250fb85d64c23:"关",s625ad019db843f94:"使用",sbf1ca928ec1deb62:"需要更多帮助吗？",sd1a8dc951b2b6a98:"选择哪些字段要在列表中显示为列",sf9aee319a006c9b4:"添加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:h`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:h`${0} List`,seafe6ef133ede7da:h`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},gr=Object.freeze(Object.defineProperty({__proto__:null,templates:br},Symbol.toStringTag,{value:"Module"})),mr={s04ceadb276bbe149:"正在載入選項...",s226be12a5b1a27e8:"閱讀文檔",s29e25f5e4622f847:"開啟對話視窗",s33f85f24c0f5f008:"儲存",s36cb242ac90353bc:"欄位",s41cb4006238ebd3b:"大量編輯",s5e8250fb85d64c23:"關",s625ad019db843f94:"使用",s9d51bfd93b5dbeca:"顯示已儲存",sac83d7f9358b43db:h`${0} 清單`,sb1bd536b63e9e995:"私人欄位：只有我可以看見內容",sb59d68ed12d46377:"載入中",sbf1ca928ec1deb62:"需要更多幫助嗎？",scb9a1ff437efbd2a:h`從清單中選取要更新的項目${0}，並在下面進行更新`,sd1a8dc951b2b6a98:"選擇哪些欄位要顯示為列表中的直行",seafe6ef133ede7da:h`第1頁 （共${0}頁）`,sf9aee319a006c9b4:"新增",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},vr=Object.freeze(Object.defineProperty({__proto__:null,templates:mr},Symbol.toStringTag,{value:"Module"}));return $.ApiService=ze,$.ComponentService=Dt,$.DtAlert=So,$.DtBase=N,$.DtButton=ys,$.DtChurchHealthCircle=$s,$.DtCommChannel=xo,$.DtConnection=Es,$.DtCopyText=Ts,$.DtDate=As,$.DtDropdown=_s,$.DtFormBase=j,$.DtIcon=go,$.DtLabel=xs,$.DtList=Eo,$.DtLocation=Os,$.DtLocationMap=vo,$.DtMapModal=mo,$.DtModal=ws,$.DtMultiSelect=mt,$.DtMultiSelectButtonGroup=ko,$.DtNumberField=yo,$.DtSingleSelect=wo,$.DtTags=_e,$.DtText=jt,$.DtTextArea=_o,$.DtTile=Co,$.DtToggle=$o,$.DtUsersConnection=Cs,Object.defineProperty($,Symbol.toStringTag,{value:"Module"}),$}({});
