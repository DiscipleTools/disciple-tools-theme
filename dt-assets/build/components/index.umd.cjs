(function(_,j){typeof exports=="object"&&typeof module<"u"?j(exports):typeof define=="function"&&define.amd?define(["exports"],j):(_=typeof globalThis<"u"?globalThis:_||self,j(_.DtWebComponents={}))})(this,function(_){"use strict";var Mr=Object.defineProperty;var jr=(_,j,W)=>j in _?Mr(_,j,{enumerable:!0,configurable:!0,writable:!0,value:W}):_[j]=W;var Me=(_,j,W)=>jr(_,typeof j!="symbol"?j+"":j,W);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var Os;const j=globalThis,W=j.ShadowRoot&&(j.ShadyCSS===void 0||j.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,Bt=Symbol(),Ht=new WeakMap;let Us=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==Bt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(W&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=Ht.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&Ht.set(t,e))}return e}toString(){return this.cssText}};const Vs=o=>new Us(typeof o=="string"?o:o+"",void 0,Bt),Bs=(o,e)=>{if(W)o.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),s=j.litNonce;s!==void 0&&i.setAttribute("nonce",s),i.textContent=t.cssText,o.appendChild(i)}},Kt=W?o=>o:o=>o instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return Vs(t)})(o):o;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:Hs,defineProperty:Ks,getOwnPropertyDescriptor:Ws,getOwnPropertyNames:Gs,getOwnPropertySymbols:Zs,getPrototypeOf:Js}=Object,G=globalThis,Wt=G.trustedTypes,Qs=Wt?Wt.emptyScript:"",tt=G.reactiveElementPolyfillSupport,be=(o,e)=>o,it={toAttribute(o,e){switch(e){case Boolean:o=o?Qs:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,e){let t=o;switch(e){case Boolean:t=o!==null;break;case Number:t=o===null?null:Number(o);break;case Object:case Array:try{t=JSON.parse(o)}catch{t=null}}return t}},Gt=(o,e)=>!Hs(o,e),Zt={attribute:!0,type:String,converter:it,reflect:!1,hasChanged:Gt};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),G.litPropertyMetadata??(G.litPropertyMetadata=new WeakMap);let ge=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Zt){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),s=this.getPropertyDescriptor(e,i,t);s!==void 0&&Ks(this.prototype,e,s)}}static getPropertyDescriptor(e,t,i){const{get:s,set:a}=Ws(this.prototype,e)??{get(){return this[t]},set(n){this[t]=n}};return{get(){return s==null?void 0:s.call(this)},set(n){const r=s==null?void 0:s.call(this);a.call(this,n),this.requestUpdate(e,r,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Zt}static _$Ei(){if(this.hasOwnProperty(be("elementProperties")))return;const e=Js(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(be("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(be("properties"))){const t=this.properties,i=[...Gs(t),...Zs(t)];for(const s of i)this.createProperty(s,t[s])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,s]of t)this.elementProperties.set(i,s)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const s=this._$Eu(t,i);s!==void 0&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const s of i)t.unshift(Kt(s))}else e!==void 0&&t.push(Kt(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return Bs(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),s=this.constructor._$Eu(e,i);if(s!==void 0&&i.reflect===!0){const n=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:it).toAttribute(t,i.type);this._$Em=e,n==null?this.removeAttribute(s):this.setAttribute(s,n),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,s=i._$Eh.get(e);if(s!==void 0&&this._$Em!==s){const n=i.getPropertyOptions(s),r=typeof n.converter=="function"?{fromAttribute:n.converter}:((a=n.converter)==null?void 0:a.fromAttribute)!==void 0?n.converter:it;this._$Em=s,this[s]=r.fromAttribute(t,n.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??Gt)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,n]of this._$Ep)this[a]=n;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[a,n]of s)n.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],n)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(s=>{var a;return(a=s.hostUpdate)==null?void 0:a.call(s)}),this.update(t)):this._$EU()}catch(s){throw e=!1,this._$EU(),s}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var s;return(s=i.hostUpdated)==null?void 0:s.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}};ge.elementStyles=[],ge.shadowRootOptions={mode:"open"},ge[be("elementProperties")]=new Map,ge[be("finalized")]=new Map,tt==null||tt({ReactiveElement:ge}),(G.reactiveElementVersions??(G.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const me=globalThis,je=me.trustedTypes,Jt=je?je.createPolicy("lit-html",{createHTML:o=>o}):void 0,Qt="$lit$",Z=`lit$${Math.random().toFixed(9).slice(2)}$`,Xt="?"+Z,Xs=`<${Xt}>`,se=document,ve=()=>se.createComment(""),ye=o=>o===null||typeof o!="object"&&typeof o!="function",st=Array.isArray,Ys=o=>st(o)||typeof(o==null?void 0:o[Symbol.iterator])=="function",ot=`[ 	
\f\r]`,we=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Yt=/-->/g,ei=/>/g,oe=RegExp(`>|${ot}(?:([^\\s"'>=/]+)(${ot}*=${ot}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),ti=/'/g,ii=/"/g,si=/^(?:script|style|textarea|title)$/i,eo=o=>(e,...t)=>({_$litType$:o,strings:e,values:t}),u=eo(1),U=Symbol.for("lit-noChange"),L=Symbol.for("lit-nothing"),oi=new WeakMap,ae=se.createTreeWalker(se,129);function ai(o,e){if(!st(o)||!o.hasOwnProperty("raw"))throw Error("invalid template strings array");return Jt!==void 0?Jt.createHTML(e):e}const to=(o,e)=>{const t=o.length-1,i=[];let s,a=e===2?"<svg>":e===3?"<math>":"",n=we;for(let r=0;r<t;r++){const l=o[r];let d,h,g=-1,m=0;for(;m<l.length&&(n.lastIndex=m,h=n.exec(l),h!==null);)m=n.lastIndex,n===we?h[1]==="!--"?n=Yt:h[1]!==void 0?n=ei:h[2]!==void 0?(si.test(h[2])&&(s=RegExp("</"+h[2],"g")),n=oe):h[3]!==void 0&&(n=oe):n===oe?h[0]===">"?(n=s??we,g=-1):h[1]===void 0?g=-2:(g=n.lastIndex-h[2].length,d=h[1],n=h[3]===void 0?oe:h[3]==='"'?ii:ti):n===ii||n===ti?n=oe:n===Yt||n===ei?n=we:(n=oe,s=void 0);const y=n===oe&&o[r+1].startsWith("/>")?" ":"";a+=n===we?l+Xs:g>=0?(i.push(d),l.slice(0,g)+Qt+l.slice(g)+Z+y):l+Z+(g===-2?r:y)}return[ai(o,a+(o[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),i]};class _e{constructor({strings:e,_$litType$:t},i){let s;this.parts=[];let a=0,n=0;const r=e.length-1,l=this.parts,[d,h]=to(e,t);if(this.el=_e.createElement(d,i),ae.currentNode=this.el.content,t===2||t===3){const g=this.el.content.firstChild;g.replaceWith(...g.childNodes)}for(;(s=ae.nextNode())!==null&&l.length<r;){if(s.nodeType===1){if(s.hasAttributes())for(const g of s.getAttributeNames())if(g.endsWith(Qt)){const m=h[n++],y=s.getAttribute(g).split(Z),w=/([.?@])?(.*)/.exec(m);l.push({type:1,index:a,name:w[2],strings:y,ctor:w[1]==="."?so:w[1]==="?"?oo:w[1]==="@"?ao:ze}),s.removeAttribute(g)}else g.startsWith(Z)&&(l.push({type:6,index:a}),s.removeAttribute(g));if(si.test(s.tagName)){const g=s.textContent.split(Z),m=g.length-1;if(m>0){s.textContent=je?je.emptyScript:"";for(let y=0;y<m;y++)s.append(g[y],ve()),ae.nextNode(),l.push({type:2,index:++a});s.append(g[m],ve())}}}else if(s.nodeType===8)if(s.data===Xt)l.push({type:2,index:a});else{let g=-1;for(;(g=s.data.indexOf(Z,g+1))!==-1;)l.push({type:7,index:a}),g+=Z.length-1}a++}}static createElement(e,t){const i=se.createElement("template");return i.innerHTML=e,i}}function he(o,e,t=o,i){var n,r;if(e===U)return e;let s=i!==void 0?(n=t._$Co)==null?void 0:n[i]:t._$Cl;const a=ye(e)?void 0:e._$litDirective$;return(s==null?void 0:s.constructor)!==a&&((r=s==null?void 0:s._$AO)==null||r.call(s,!1),a===void 0?s=void 0:(s=new a(o),s._$AT(o,t,i)),i!==void 0?(t._$Co??(t._$Co=[]))[i]=s:t._$Cl=s),s!==void 0&&(e=he(o,s._$AS(o,e.values),s,i)),e}let io=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:i}=this._$AD,s=((e==null?void 0:e.creationScope)??se).importNode(t,!0);ae.currentNode=s;let a=ae.nextNode(),n=0,r=0,l=i[0];for(;l!==void 0;){if(n===l.index){let d;l.type===2?d=new pe(a,a.nextSibling,this,e):l.type===1?d=new l.ctor(a,l.name,l.strings,this,e):l.type===6&&(d=new no(a,this,e)),this._$AV.push(d),l=i[++r]}n!==(l==null?void 0:l.index)&&(a=ae.nextNode(),n++)}return ae.currentNode=se,s}p(e){let t=0;for(const i of this._$AV)i!==void 0&&(i.strings!==void 0?(i._$AI(e,i,t),t+=i.strings.length-2):i._$AI(e[t])),t++}};class pe{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,i,s){this.type=2,this._$AH=L,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=i,this.options=s,this._$Cv=(s==null?void 0:s.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=he(this,e,t),ye(e)?e===L||e==null||e===""?(this._$AH!==L&&this._$AR(),this._$AH=L):e!==this._$AH&&e!==U&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Ys(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==L&&ye(this._$AH)?this._$AA.nextSibling.data=e:this.T(se.createTextNode(e)),this._$AH=e}$(e){var a;const{values:t,_$litType$:i}=e,s=typeof i=="number"?this._$AC(e):(i.el===void 0&&(i.el=_e.createElement(ai(i.h,i.h[0]),this.options)),i);if(((a=this._$AH)==null?void 0:a._$AD)===s)this._$AH.p(t);else{const n=new io(s,this),r=n.u(this.options);n.p(t),this.T(r),this._$AH=n}}_$AC(e){let t=oi.get(e.strings);return t===void 0&&oi.set(e.strings,t=new _e(e)),t}k(e){st(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let i,s=0;for(const a of e)s===t.length?t.push(i=new pe(this.O(ve()),this.O(ve()),this,this.options)):i=t[s],i._$AI(a),s++;s<t.length&&(this._$AR(i&&i._$AB.nextSibling,s),t.length=s)}_$AR(e=this._$AA.nextSibling,t){var i;for((i=this._$AP)==null?void 0:i.call(this,!1,!0,t);e&&e!==this._$AB;){const s=e.nextSibling;e.remove(),e=s}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class ze{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,i,s,a){this.type=1,this._$AH=L,this._$AN=void 0,this.element=e,this.name=t,this._$AM=s,this.options=a,i.length>2||i[0]!==""||i[1]!==""?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=L}_$AI(e,t=this,i,s){const a=this.strings;let n=!1;if(a===void 0)e=he(this,e,t,0),n=!ye(e)||e!==this._$AH&&e!==U,n&&(this._$AH=e);else{const r=e;let l,d;for(e=a[0],l=0;l<a.length-1;l++)d=he(this,r[i+l],t,l),d===U&&(d=this._$AH[l]),n||(n=!ye(d)||d!==this._$AH[l]),d===L?e=L:e!==L&&(e+=(d??"")+a[l+1]),this._$AH[l]=d}n&&!s&&this.j(e)}j(e){e===L?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class so extends ze{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===L?void 0:e}}class oo extends ze{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==L)}}class ao extends ze{constructor(e,t,i,s,a){super(e,t,i,s,a),this.type=5}_$AI(e,t=this){if((e=he(this,e,t,0)??L)===U)return;const i=this._$AH,s=e===L&&i!==L||e.capture!==i.capture||e.once!==i.once||e.passive!==i.passive,a=e!==L&&(i===L||s);s&&this.element.removeEventListener(this.name,this,i),a&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class no{constructor(e,t,i){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(e){he(this,e)}}const ro={I:pe},at=me.litHtmlPolyfillSupport;at==null||at(_e,pe),(me.litHtmlVersions??(me.litHtmlVersions=[])).push("3.2.1");const lo=(o,e,t)=>{const i=(t==null?void 0:t.renderBefore)??e;let s=i._$litPart$;if(s===void 0){const a=(t==null?void 0:t.renderBefore)??null;i._$litPart$=s=new pe(e.insertBefore(ve(),a),a,void 0,t??{})}return s._$AI(o),s};/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const De=globalThis,nt=De.ShadowRoot&&(De.ShadyCSS===void 0||De.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,rt=Symbol(),ni=new WeakMap;let ri=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==rt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(nt&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=ni.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&ni.set(t,e))}return e}toString(){return this.cssText}};const co=o=>new ri(typeof o=="string"?o:o+"",void 0,rt),x=(o,...e)=>{const t=o.length===1?o[0]:e.reduce((i,s,a)=>i+(n=>{if(n._$cssResult$===!0)return n.cssText;if(typeof n=="number")return n;throw Error("Value passed to 'css' function must be a 'css' function result: "+n+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(s)+o[a+1],o[0]);return new ri(t,o,rt)},uo=(o,e)=>{if(nt)o.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),s=De.litNonce;s!==void 0&&i.setAttribute("nonce",s),i.textContent=t.cssText,o.appendChild(i)}},li=nt?o=>o:o=>o instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return co(t)})(o):o;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:ho,defineProperty:po,getOwnPropertyDescriptor:fo,getOwnPropertyNames:bo,getOwnPropertySymbols:go,getPrototypeOf:mo}=Object,J=globalThis,di=J.trustedTypes,vo=di?di.emptyScript:"",lt=J.reactiveElementPolyfillSupport,$e=(o,e)=>o,dt={toAttribute(o,e){switch(e){case Boolean:o=o?vo:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,e){let t=o;switch(e){case Boolean:t=o!==null;break;case Number:t=o===null?null:Number(o);break;case Object:case Array:try{t=JSON.parse(o)}catch{t=null}}return t}},ci=(o,e)=>!ho(o,e),ui={attribute:!0,type:String,converter:dt,reflect:!1,hasChanged:ci};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),J.litPropertyMetadata??(J.litPropertyMetadata=new WeakMap);class fe extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=ui){if(t.state&&(t.attribute=!1),this._$Ei(),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),s=this.getPropertyDescriptor(e,i,t);s!==void 0&&po(this.prototype,e,s)}}static getPropertyDescriptor(e,t,i){const{get:s,set:a}=fo(this.prototype,e)??{get(){return this[t]},set(n){this[t]=n}};return{get(){return s==null?void 0:s.call(this)},set(n){const r=s==null?void 0:s.call(this);a.call(this,n),this.requestUpdate(e,r,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??ui}static _$Ei(){if(this.hasOwnProperty($e("elementProperties")))return;const e=mo(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty($e("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty($e("properties"))){const t=this.properties,i=[...bo(t),...go(t)];for(const s of i)this.createProperty(s,t[s])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,s]of t)this.elementProperties.set(i,s)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const s=this._$Eu(t,i);s!==void 0&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const s of i)t.unshift(li(s))}else e!==void 0&&t.push(li(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return uo(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$EC(e,t){var a;const i=this.constructor.elementProperties.get(e),s=this.constructor._$Eu(e,i);if(s!==void 0&&i.reflect===!0){const n=(((a=i.converter)==null?void 0:a.toAttribute)!==void 0?i.converter:dt).toAttribute(t,i.type);this._$Em=e,n==null?this.removeAttribute(s):this.setAttribute(s,n),this._$Em=null}}_$AK(e,t){var a;const i=this.constructor,s=i._$Eh.get(e);if(s!==void 0&&this._$Em!==s){const n=i.getPropertyOptions(s),r=typeof n.converter=="function"?{fromAttribute:n.converter}:((a=n.converter)==null?void 0:a.fromAttribute)!==void 0?n.converter:dt;this._$Em=s,this[s]=r.fromAttribute(t,n.type),this._$Em=null}}requestUpdate(e,t,i){if(e!==void 0){if(i??(i=this.constructor.getPropertyOptions(e)),!(i.hasChanged??ci)(this[e],t))return;this.P(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(e,t,i){this._$AL.has(e)||this._$AL.set(e,t),i.reflect===!0&&this._$Em!==e&&(this._$Ej??(this._$Ej=new Set)).add(e)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[a,n]of this._$Ep)this[a]=n;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[a,n]of s)n.wrapped!==!0||this._$AL.has(a)||this[a]===void 0||this.P(a,this[a],n)}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(s=>{var a;return(a=s.hostUpdate)==null?void 0:a.call(s)}),this.update(t)):this._$EU()}catch(s){throw e=!1,this._$EU(),s}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var s;return(s=i.hostUpdated)==null?void 0:s.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Ej&&(this._$Ej=this._$Ej.forEach(t=>this._$EC(t,this[t]))),this._$EU()}updated(e){}firstUpdated(e){}}fe.elementStyles=[],fe.shadowRootOptions={mode:"open"},fe[$e("elementProperties")]=new Map,fe[$e("finalized")]=new Map,lt==null||lt({ReactiveElement:fe}),(J.reactiveElementVersions??(J.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */let ne=class extends fe{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=lo(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return U}};ne._$litElement$=!0,ne.finalized=!0,(Os=globalThis.litElementHydrateSupport)==null||Os.call(globalThis,{LitElement:ne});const ct=globalThis.litElementPolyfillSupport;ct==null||ct({LitElement:ne}),(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.1.1");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ut={ATTRIBUTE:1,CHILD:2},ht=o=>(...e)=>({_$litDirective$:o,values:e});let pt=class{constructor(e){}get _$AU(){return this._$AM._$AU}_$AT(e,t,i){this._$Ct=e,this._$AM=t,this._$Ci=i}_$AS(e,t){return this.update(e,t)}update(e,t){return this.render(...t)}};/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const T=ht(class extends pt{constructor(o){var e;if(super(o),o.type!==ut.ATTRIBUTE||o.name!=="class"||((e=o.strings)==null?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(o){return" "+Object.keys(o).filter(e=>o[e]).join(" ")+" "}update(o,[e]){var i,s;if(this.st===void 0){this.st=new Set,o.strings!==void 0&&(this.nt=new Set(o.strings.join(" ").split(/\s/).filter(a=>a!=="")));for(const a in e)e[a]&&!((i=this.nt)!=null&&i.has(a))&&this.st.add(a);return this.render(e)}const t=o.element.classList;for(const a of this.st)a in e||(t.remove(a),this.st.delete(a));for(const a in e){const n=!!e[a];n===this.st.has(a)||(s=this.nt)!=null&&s.has(a)||(n?(t.add(a),this.st.add(a)):(t.remove(a),this.st.delete(a)))}return U}});/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ft="lit-localize-status";/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const p=(o,...e)=>({strTag:!0,strings:o,values:e}),yo=o=>typeof o!="string"&&"strTag"in o,hi=(o,e,t)=>{let i=o[0];for(let s=1;s<o.length;s++)i+=e[t?t[s-1]:s-1],i+=o[s];return i};/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const pi=o=>yo(o)?hi(o.strings,o.values):o;let I=pi,fi=!1;function wo(o){if(fi)throw new Error("lit-localize can only be configured once");I=o,fi=!0}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class _o{constructor(e){this.__litLocalizeEventHandler=t=>{t.detail.status==="ready"&&this.host.requestUpdate()},this.host=e}hostConnected(){window.addEventListener(ft,this.__litLocalizeEventHandler)}hostDisconnected(){window.removeEventListener(ft,this.__litLocalizeEventHandler)}}const $o=o=>o.addController(new _o(o));/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class bi{constructor(){this.settled=!1,this.promise=new Promise((e,t)=>{this._resolve=e,this._reject=t})}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const V=[];for(let o=0;o<256;o++)V[o]=(o>>4&15).toString(16)+(o&15).toString(16);function xo(o){let e=0,t=8997,i=0,s=33826,a=0,n=40164,r=0,l=52210;for(let d=0;d<o.length;d++)t^=o.charCodeAt(d),e=t*435,i=s*435,a=n*435,r=l*435,a+=t<<8,r+=s<<8,i+=e>>>16,t=e&65535,a+=i>>>16,s=i&65535,l=r+(a>>>16)&65535,n=a&65535;return V[l>>8]+V[l&255]+V[n>>8]+V[n&255]+V[s>>8]+V[s&255]+V[t>>8]+V[t&255]}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ko="",So="h",Eo="s";function To(o,e){return(e?So:Eo)+xo(typeof o=="string"?o:o.join(ko))}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const gi=new WeakMap,mi=new Map;function Ao(o,e,t){if(o){const i=(t==null?void 0:t.id)??Oo(e),s=o[i];if(s){if(typeof s=="string")return s;if("strTag"in s)return hi(s.strings,e.values,s.values);{let a=gi.get(s);return a===void 0&&(a=s.values,gi.set(s,a)),{...s,values:a.map(n=>e.values[n])}}}}return pi(e)}function Oo(o){const e=typeof o=="string"?o:o.strings;let t=mi.get(e);return t===void 0&&(t=To(e,typeof o!="string"&&!("strTag"in o)),mi.set(e,t)),t}/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function bt(o){window.dispatchEvent(new CustomEvent(ft,{detail:o}))}let Fe="",gt,vi,Re,mt,yi,re=new bi;re.resolve();let Ne=0;const Co=o=>(wo((e,t)=>Ao(yi,e,t)),Fe=vi=o.sourceLocale,Re=new Set(o.targetLocales),Re.add(o.sourceLocale),mt=o.loadLocale,{getLocale:Lo,setLocale:Io}),Lo=()=>Fe,Io=o=>{if(o===(gt??Fe))return re.promise;if(!Re||!mt)throw new Error("Internal error");if(!Re.has(o))throw new Error("Invalid locale code");Ne++;const e=Ne;return gt=o,re.settled&&(re=new bi),bt({status:"loading",loadingLocale:o}),(o===vi?Promise.resolve({templates:void 0}):mt(o)).then(i=>{Ne===e&&(Fe=o,gt=void 0,yi=i.templates,bt({status:"ready",readyLocale:o}),re.resolve())},i=>{Ne===e&&(bt({status:"error",errorLocale:o,errorMessage:i.toString()}),re.reject(i))}),re.promise},Po=(o,e,t)=>{const i=o[e];return i?typeof i=="function"?i():Promise.resolve(i):new Promise((s,a)=>{(typeof queueMicrotask=="function"?queueMicrotask:setTimeout)(a.bind(null,new Error("Unknown variable dynamic import: "+e+(e.split("/").length!==t?". Note that variables only represent file names one level deep.":""))))})},Mo="en",jo=["am_ET","ar","ar_MA","bg_BG","bn_BD","bs_BA","cs","de_DE","el","en_US","es_419","es_ES","fa_IR","fr_FR","hi_IN","hr","hu_HU","id_ID","it_IT","ja","ko_KR","mk_MK","mr","my_MM","ne_NP","nl_NL","pa_IN","pl","pt_BR","ro_RO","ru_RU","sl_SI","sr_BA","sw","th","tl","tr_TR","uk","vi","zh_CN","zh_TW"],{setLocale:zo}=Co({sourceLocale:Mo,targetLocales:jo,loadLocale:o=>Po(Object.assign({"./generated/am_ET.js":()=>Promise.resolve().then(()=>Ha),"./generated/ar.js":()=>Promise.resolve().then(()=>Wa),"./generated/ar_MA.js":()=>Promise.resolve().then(()=>Za),"./generated/bg_BG.js":()=>Promise.resolve().then(()=>Qa),"./generated/bn_BD.js":()=>Promise.resolve().then(()=>Ya),"./generated/bs_BA.js":()=>Promise.resolve().then(()=>tn),"./generated/cs.js":()=>Promise.resolve().then(()=>on),"./generated/de_DE.js":()=>Promise.resolve().then(()=>nn),"./generated/el.js":()=>Promise.resolve().then(()=>ln),"./generated/en_US.js":()=>Promise.resolve().then(()=>cn),"./generated/es-419.js":()=>Promise.resolve().then(()=>hn),"./generated/es_419.js":()=>Promise.resolve().then(()=>fn),"./generated/es_ES.js":()=>Promise.resolve().then(()=>gn),"./generated/fa_IR.js":()=>Promise.resolve().then(()=>vn),"./generated/fr_FR.js":()=>Promise.resolve().then(()=>wn),"./generated/hi_IN.js":()=>Promise.resolve().then(()=>$n),"./generated/hr.js":()=>Promise.resolve().then(()=>kn),"./generated/hu_HU.js":()=>Promise.resolve().then(()=>En),"./generated/id_ID.js":()=>Promise.resolve().then(()=>An),"./generated/it_IT.js":()=>Promise.resolve().then(()=>Cn),"./generated/ja.js":()=>Promise.resolve().then(()=>In),"./generated/ko_KR.js":()=>Promise.resolve().then(()=>Mn),"./generated/mk_MK.js":()=>Promise.resolve().then(()=>zn),"./generated/mr.js":()=>Promise.resolve().then(()=>Fn),"./generated/my_MM.js":()=>Promise.resolve().then(()=>Nn),"./generated/ne_NP.js":()=>Promise.resolve().then(()=>Un),"./generated/nl_NL.js":()=>Promise.resolve().then(()=>Bn),"./generated/pa_IN.js":()=>Promise.resolve().then(()=>Kn),"./generated/pl.js":()=>Promise.resolve().then(()=>Gn),"./generated/pt_BR.js":()=>Promise.resolve().then(()=>Jn),"./generated/ro_RO.js":()=>Promise.resolve().then(()=>Xn),"./generated/ru_RU.js":()=>Promise.resolve().then(()=>er),"./generated/sl_SI.js":()=>Promise.resolve().then(()=>ir),"./generated/sr_BA.js":()=>Promise.resolve().then(()=>or),"./generated/sw.js":()=>Promise.resolve().then(()=>nr),"./generated/th.js":()=>Promise.resolve().then(()=>lr),"./generated/tl.js":()=>Promise.resolve().then(()=>cr),"./generated/tr_TR.js":()=>Promise.resolve().then(()=>hr),"./generated/uk.js":()=>Promise.resolve().then(()=>fr),"./generated/vi.js":()=>Promise.resolve().then(()=>gr),"./generated/zh_CN.js":()=>Promise.resolve().then(()=>vr),"./generated/zh_TW.js":()=>Promise.resolve().then(()=>wr)}),`./generated/${o}.js`,3)});class vt{constructor(e,t="/wp-json"){this.nonce=e;let i=t;i.match("^http")&&(i=i.replace(/^http[s]?:\/\/.*?\//,"")),i=`/${i}/`.replace(/\/\//g,"/"),this.apiRoot=i}async makeRequest(e,t,i,s="dt/v1/"){let a=s;!a.endsWith("/")&&!t.startsWith("/")&&(a+="/");const n=t.startsWith("http")?t:`${this.apiRoot}${a}${t}`,r={method:e,credentials:"same-origin",headers:{"Content-Type":"application/json","X-WP-Nonce":this.nonce}};e!=="GET"&&(r.body=JSON.stringify(i));const l=await fetch(n,r),d=await l.json();if(!l.ok){const h=new Error((d==null?void 0:d.message)||d.toString());throw h.args={status:l.status,statusText:l.statusText,body:d},h}return d}async makeRequestOnPosts(e,t,i={}){return this.makeRequest(e,t,i,"dt-posts/v2/")}async getPost(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}`)}async createPost(e,t){return this.makeRequestOnPosts("POST",e,t)}async fetchPostsList(e,t){return this.makeRequestOnPosts("POST",`${e}/list`,t)}async updatePost(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}`,i)}async deletePost(e,t){return this.makeRequestOnPosts("DELETE",`${e}/${t}`)}async listPostsCompact(e,t=""){const i=new URLSearchParams({s:t});return this.makeRequestOnPosts("GET",`${e}/compact?${i}`)}async getPostDuplicates(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/all_duplicates`,i)}async checkFieldValueExists(e,t){return this.makeRequestOnPosts("POST",`${e}/check_field_value_exists`,t)}async getMultiSelectValues(e,t,i=""){const s=new URLSearchParams({s:i,field:t});return this.makeRequestOnPosts("GET",`${e}/multi-select-values?${s}`)}async getLocations(e,t,i,s=""){const a=new URLSearchParams({s,field:t,filter:i});return this.makeRequest("GET",`mapping_module/search_location_grid_by_name?${a}`)}async transferContact(e,t){return this.makeRequestOnPosts("POST","contacts/transfer",{contact_id:e,site_post_id:t})}async transferContactSummaryUpdate(e,t){return this.makeRequestOnPosts("POST","contacts/transfer/summary/send-update",{contact_id:e,update:t})}async requestRecordAccess(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/request_record_access`,{user_id:i})}async createComment(e,t,i,s="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments`,{comment:i,comment_type:s})}async updateComment(e,t,i,s,a="comment"){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}`,{comment:s,comment_type:a})}async deleteComment(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/comments/${i}`)}async getComments(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/comments`)}async toggle_comment_reaction(e,t,i,s,a){return this.makeRequestOnPosts("POST",`${e}/${t}/comments/${i}/react`,{user_id:s,reaction:a})}async getPostActivity(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/activity`)}async getSingleActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/activity/${i}`)}async revertActivity(e,t,i){return this.makeRequestOnPosts("GET",`${e}/${t}/revert/${i}`)}async getPostShares(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/shares`)}async addPostShare(e,t,i){return this.makeRequestOnPosts("POST",`${e}/${t}/shares`,{user_id:i})}async removePostShare(e,t,i){return this.makeRequestOnPosts("DELETE",`${e}/${t}/shares`,{user_id:i})}async getFilters(){return this.makeRequest("GET","users/get_filters")}async saveFilters(e,t){return this.makeRequest("POST","users/save_filters",{filter:t,postType:e})}async deleteFilter(e,t){return this.makeRequest("DELETE","users/save_filters",{id:t,postType:e})}async searchUsers(e,t=""){const i=new URLSearchParams({s:t});return this.makeRequest("GET",`users/get_users?${i}&post_type=${e}`)}async checkDuplicateUsers(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/duplicates`)}async getContactInfo(e,t){return this.makeRequestOnPosts("GET",`${e}/${t}/`)}async createUser(e){return this.makeRequest("POST","users/create",e)}async advanced_search(e,t,i,s){return this.makeRequest("GET","advanced_search",{query:e,postType:t,offset:i,post:s.post,comment:s.comment,meta:s.meta,status:s.status},"dt-posts/v2/posts/search/")}}(function(){(function(o){const e=new WeakMap,t=new WeakMap,i=new WeakMap,s=new WeakMap,a=new WeakMap,n=new WeakMap,r=new WeakMap,l=new WeakMap,d=new WeakMap,h=new WeakMap,g=new WeakMap,m=new WeakMap,y=new WeakMap,w=new WeakMap,O=new WeakMap,C={ariaAtomic:"aria-atomic",ariaAutoComplete:"aria-autocomplete",ariaBusy:"aria-busy",ariaChecked:"aria-checked",ariaColCount:"aria-colcount",ariaColIndex:"aria-colindex",ariaColIndexText:"aria-colindextext",ariaColSpan:"aria-colspan",ariaCurrent:"aria-current",ariaDescription:"aria-description",ariaDisabled:"aria-disabled",ariaExpanded:"aria-expanded",ariaHasPopup:"aria-haspopup",ariaHidden:"aria-hidden",ariaInvalid:"aria-invalid",ariaKeyShortcuts:"aria-keyshortcuts",ariaLabel:"aria-label",ariaLevel:"aria-level",ariaLive:"aria-live",ariaModal:"aria-modal",ariaMultiLine:"aria-multiline",ariaMultiSelectable:"aria-multiselectable",ariaOrientation:"aria-orientation",ariaPlaceholder:"aria-placeholder",ariaPosInSet:"aria-posinset",ariaPressed:"aria-pressed",ariaReadOnly:"aria-readonly",ariaRelevant:"aria-relevant",ariaRequired:"aria-required",ariaRoleDescription:"aria-roledescription",ariaRowCount:"aria-rowcount",ariaRowIndex:"aria-rowindex",ariaRowIndexText:"aria-rowindextext",ariaRowSpan:"aria-rowspan",ariaSelected:"aria-selected",ariaSetSize:"aria-setsize",ariaSort:"aria-sort",ariaValueMax:"aria-valuemax",ariaValueMin:"aria-valuemin",ariaValueNow:"aria-valuenow",ariaValueText:"aria-valuetext",role:"role"},M=(f,c)=>{for(let b in C){c[b]=null;let v=null;const $=C[b];Object.defineProperty(c,b,{get(){return v},set(k){v=k,f.isConnected?z(f,$,k):h.set(f,c)}})}};function A(f){const c=s.get(f),{form:b}=c;Ps(f,b,c),Is(f,c.labels)}const ee=(f,c=!1)=>{const b=document.createTreeWalker(f,NodeFilter.SHOW_ELEMENT,{acceptNode(k){return s.has(k)?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let v=b.nextNode();const $=!c||f.disabled;for(;v;)v.formDisabledCallback&&$&&Rt(v,f.disabled),v=b.nextNode()},Qe={attributes:!0,attributeFilter:["disabled","name"]},te=et()?new MutationObserver(f=>{for(const c of f){const b=c.target;if(c.attributeName==="disabled"&&(b.constructor.formAssociated?Rt(b,b.hasAttribute("disabled")):b.localName==="fieldset"&&ee(b)),c.attributeName==="name"&&b.constructor.formAssociated){const v=s.get(b),$=d.get(b);v.setFormValue($)}}}):{};function E(f){f.forEach(c=>{const{addedNodes:b,removedNodes:v}=c,$=Array.from(b),k=Array.from(v);$.forEach(S=>{var F;if(s.has(S)&&S.constructor.formAssociated&&A(S),h.has(S)){const P=h.get(S);Object.keys(C).filter(q=>P[q]!==null).forEach(q=>{z(S,C[q],P[q])}),h.delete(S)}if(O.has(S)){const P=O.get(S);z(S,"internals-valid",P.validity.valid.toString()),z(S,"internals-invalid",(!P.validity.valid).toString()),z(S,"aria-invalid",(!P.validity.valid).toString()),O.delete(S)}if(S.localName==="form"){const P=l.get(S),K=document.createTreeWalker(S,NodeFilter.SHOW_ELEMENT,{acceptNode(Vt){return s.has(Vt)&&Vt.constructor.formAssociated&&!(P&&P.has(Vt))?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_SKIP}});let q=K.nextNode();for(;q;)A(q),q=K.nextNode()}S.localName==="fieldset"&&((F=te.observe)===null||F===void 0||F.call(te,S,Qe),ee(S,!0))}),k.forEach(S=>{const F=s.get(S);F&&i.get(F)&&Cs(F),r.has(S)&&r.get(S).disconnect()})})}function R(f){f.forEach(c=>{const{removedNodes:b}=c;b.forEach(v=>{const $=y.get(c.target);s.has(v)&&js(v),$.disconnect()})})}const ue=f=>{var c,b;const v=new MutationObserver(R);!((c=window==null?void 0:window.ShadyDOM)===null||c===void 0)&&c.inUse&&f.mode&&f.host&&(f=f.host),(b=v.observe)===null||b===void 0||b.call(v,f,{childList:!0}),y.set(f,v)};et()&&new MutationObserver(E);const ie={childList:!0,subtree:!0},z=(f,c,b)=>{f.getAttribute(c)!==b&&f.setAttribute(c,b)},Rt=(f,c)=>{f.toggleAttribute("internals-disabled",c),c?z(f,"aria-disabled","true"):f.removeAttribute("aria-disabled"),f.formDisabledCallback&&f.formDisabledCallback.apply(f,[c])},Cs=f=>{i.get(f).forEach(b=>{b.remove()}),i.set(f,[])},Ls=(f,c)=>{const b=document.createElement("input");return b.type="hidden",b.name=f.getAttribute("name"),f.after(b),i.get(c).push(b),b},_r=(f,c)=>{var b;i.set(c,[]),(b=te.observe)===null||b===void 0||b.call(te,f,Qe)},Is=(f,c)=>{if(c.length){Array.from(c).forEach(v=>v.addEventListener("click",f.click.bind(f)));let b=c[0].id;c[0].id||(b=`${c[0].htmlFor}_Label`,c[0].id=b),z(f,"aria-labelledby",b)}},Xe=f=>{const c=Array.from(f.elements).filter(k=>!k.tagName.includes("-")&&k.validity).map(k=>k.validity.valid),b=l.get(f)||[],v=Array.from(b).filter(k=>k.isConnected).map(k=>s.get(k).validity.valid),$=[...c,...v].includes(!1);f.toggleAttribute("internals-invalid",$),f.toggleAttribute("internals-valid",!$)},$r=f=>{Xe(Ye(f.target))},xr=f=>{Xe(Ye(f.target))},kr=f=>{const c=["button[type=submit]","input[type=submit]","button:not([type])"].map(b=>`${b}:not([disabled])`).map(b=>`${b}:not([form])${f.id?`,${b}[form='${f.id}']`:""}`).join(",");f.addEventListener("click",b=>{if(b.target.closest(c)){const $=l.get(f);if(f.noValidate)return;$.size&&Array.from($).reverse().map(F=>s.get(F).reportValidity()).includes(!1)&&b.preventDefault()}})},Sr=f=>{const c=l.get(f.target);c&&c.size&&c.forEach(b=>{b.constructor.formAssociated&&b.formResetCallback&&b.formResetCallback.apply(b)})},Ps=(f,c,b)=>{if(c){const v=l.get(c);if(v)v.add(f);else{const $=new Set;$.add(f),l.set(c,$),kr(c),c.addEventListener("reset",Sr),c.addEventListener("input",$r),c.addEventListener("change",xr)}n.set(c,{ref:f,internals:b}),f.constructor.formAssociated&&f.formAssociatedCallback&&setTimeout(()=>{f.formAssociatedCallback.apply(f,[c])},0),Xe(c)}},Ye=f=>{let c=f.parentNode;return c&&c.tagName!=="FORM"&&(c=Ye(c)),c},H=(f,c,b=DOMException)=>{if(!f.constructor.formAssociated)throw new b(c)},Ms=(f,c,b)=>{const v=l.get(f);return v&&v.size&&v.forEach($=>{s.get($)[b]()||(c=!1)}),c},js=f=>{if(f.constructor.formAssociated){const c=s.get(f),{labels:b,form:v}=c;Is(f,b),Ps(f,v,c)}};function et(){return typeof MutationObserver<"u"}class Er{constructor(){this.badInput=!1,this.customError=!1,this.patternMismatch=!1,this.rangeOverflow=!1,this.rangeUnderflow=!1,this.stepMismatch=!1,this.tooLong=!1,this.tooShort=!1,this.typeMismatch=!1,this.valid=!0,this.valueMissing=!1,Object.seal(this)}}const Tr=f=>(f.badInput=!1,f.customError=!1,f.patternMismatch=!1,f.rangeOverflow=!1,f.rangeUnderflow=!1,f.stepMismatch=!1,f.tooLong=!1,f.tooShort=!1,f.typeMismatch=!1,f.valid=!0,f.valueMissing=!1,f),Ar=(f,c,b)=>(f.valid=Or(c),Object.keys(c).forEach(v=>f[v]=c[v]),b&&Xe(b),f),Or=f=>{let c=!0;for(let b in f)b!=="valid"&&f[b]!==!1&&(c=!1);return c},Nt=new WeakMap;function zs(f,c){f.toggleAttribute(c,!0),f.part&&f.part.add(c)}class qt extends Set{static get isPolyfilled(){return!0}constructor(c){if(super(),!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");Nt.set(this,c)}add(c){if(!/^--/.test(c)||typeof c!="string")throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${c} must start with '--'.`);const b=super.add(c),v=Nt.get(this),$=`state${c}`;return v.isConnected?zs(v,$):setTimeout(()=>{zs(v,$)}),b}clear(){for(let[c]of this.entries())this.delete(c);super.clear()}delete(c){const b=super.delete(c),v=Nt.get(this);return v.isConnected?(v.toggleAttribute(`state${c}`,!1),v.part&&v.part.remove(`state${c}`)):setTimeout(()=>{v.toggleAttribute(`state${c}`,!1),v.part&&v.part.remove(`state${c}`)}),b}}function Ds(f,c,b,v){if(typeof c=="function"?f!==c||!0:!c.has(f))throw new TypeError("Cannot read private member from an object whose class did not declare it");return b==="m"?v:b==="a"?v.call(f):v?v.value:c.get(f)}function Cr(f,c,b,v,$){if(typeof c=="function"?f!==c||!0:!c.has(f))throw new TypeError("Cannot write private member to an object whose class did not declare it");return c.set(f,b),b}var Pe;class Lr{constructor(c){Pe.set(this,void 0),Cr(this,Pe,c);for(let b=0;b<c.length;b++){let v=c[b];this[b]=v,v.hasAttribute("name")&&(this[v.getAttribute("name")]=v)}Object.freeze(this)}get length(){return Ds(this,Pe,"f").length}[(Pe=new WeakMap,Symbol.iterator)](){return Ds(this,Pe,"f")[Symbol.iterator]()}item(c){return this[c]==null?null:this[c]}namedItem(c){return this[c]==null?null:this[c]}}function Ir(){const f=HTMLFormElement.prototype.checkValidity;HTMLFormElement.prototype.checkValidity=b;const c=HTMLFormElement.prototype.reportValidity;HTMLFormElement.prototype.reportValidity=v;function b(...k){let S=f.apply(this,k);return Ms(this,S,"checkValidity")}function v(...k){let S=c.apply(this,k);return Ms(this,S,"reportValidity")}const{get:$}=Object.getOwnPropertyDescriptor(HTMLFormElement.prototype,"elements");Object.defineProperty(HTMLFormElement.prototype,"elements",{get(...k){const S=$.call(this,...k),F=Array.from(l.get(this)||[]);if(F.length===0)return S;const P=Array.from(S).concat(F).sort((K,q)=>K.compareDocumentPosition?K.compareDocumentPosition(q)&2?1:-1:0);return new Lr(P)}})}class Fs{static get isPolyfilled(){return!0}constructor(c){if(!c||!c.tagName||c.tagName.indexOf("-")===-1)throw new TypeError("Illegal constructor");const b=c.getRootNode(),v=new Er;this.states=new qt(c),e.set(this,c),t.set(this,v),s.set(c,this),M(c,this),_r(c,this),Object.seal(this),b instanceof DocumentFragment&&ue(b)}checkValidity(){const c=e.get(this);if(H(c,"Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const b=t.get(this);if(!b.valid){const v=new Event("invalid",{bubbles:!1,cancelable:!0,composed:!1});c.dispatchEvent(v)}return b.valid}get form(){const c=e.get(this);H(c,"Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");let b;return c.constructor.formAssociated===!0&&(b=Ye(c)),b}get labels(){const c=e.get(this);H(c,"Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");const b=c.getAttribute("id"),v=c.getRootNode();return v&&b?v.querySelectorAll(`[for="${b}"]`):[]}reportValidity(){const c=e.get(this);if(H(c,"Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!this.willValidate)return!0;const b=this.checkValidity(),v=m.get(this);if(v&&!c.constructor.formAssociated)throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");return!b&&v&&(c.focus(),v.focus()),b}setFormValue(c){const b=e.get(this);if(H(b,"Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."),Cs(this),c!=null&&!(c instanceof FormData)){if(b.getAttribute("name")){const v=Ls(b,this);v.value=c}}else c!=null&&c instanceof FormData&&Array.from(c).reverse().forEach(([v,$])=>{if(typeof $=="string"){const k=Ls(b,this);k.name=v,k.value=$}});d.set(b,c)}setValidity(c,b,v){const $=e.get(this);if(H($,"Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."),!c)throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");m.set(this,v);const k=t.get(this),S={};for(const K in c)S[K]=c[K];Object.keys(S).length===0&&Tr(k);const F=Object.assign(Object.assign({},k),S);delete F.valid;const{valid:P}=Ar(k,F,this.form);if(!P&&!b)throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");a.set(this,P?"":b),$.isConnected?($.toggleAttribute("internals-invalid",!P),$.toggleAttribute("internals-valid",P),z($,"aria-invalid",`${!P}`)):O.set($,this)}get shadowRoot(){const c=e.get(this),b=g.get(c);return b||null}get validationMessage(){const c=e.get(this);return H(c,"Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."),a.get(this)}get validity(){const c=e.get(this);return H(c,"Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."),t.get(this)}get willValidate(){const c=e.get(this);return H(c,"Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."),!(c.disabled||c.hasAttribute("disabled")||c.hasAttribute("readonly"))}}function Pr(){if(typeof window>"u"||!window.ElementInternals||!HTMLElement.prototype.attachInternals)return!1;class f extends HTMLElement{constructor(){super(),this.internals=this.attachInternals()}}const c=`element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g,"")}`;customElements.define(c,f);const b=new f;return["shadowRoot","form","willValidate","validity","validationMessage","labels","setFormValue","setValidity","checkValidity","reportValidity"].every(v=>v in b.internals)}let Rs=!1,Ns=!1;function Ut(f){Ns||(Ns=!0,window.CustomStateSet=qt,f&&(HTMLElement.prototype.attachInternals=function(...c){const b=f.call(this,c);return b.states=new qt(this),b}))}function qs(f=!0){if(!Rs){if(Rs=!0,typeof window<"u"&&(window.ElementInternals=Fs),typeof CustomElementRegistry<"u"){const c=CustomElementRegistry.prototype.define;CustomElementRegistry.prototype.define=function(b,v,$){if(v.formAssociated){const k=v.prototype.connectedCallback;v.prototype.connectedCallback=function(){w.has(this)||(w.set(this,!0),this.hasAttribute("disabled")&&Rt(this,!0)),k!=null&&k.apply(this),js(this)}}c.call(this,b,v,$)}}if(typeof HTMLElement<"u"&&(HTMLElement.prototype.attachInternals=function(){if(this.tagName){if(this.tagName.indexOf("-")===-1)throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.")}else return{};if(s.has(this))throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");return new Fs(this)}),typeof Element<"u"){let c=function(...v){const $=b.apply(this,v);if(g.set(this,$),et()){const k=new MutationObserver(E);window.ShadyDOM?k.observe(this,ie):k.observe($,ie),r.set(this,k)}return $};const b=Element.prototype.attachShadow;Element.prototype.attachShadow=c}et()&&typeof document<"u"&&new MutationObserver(E).observe(document.documentElement,ie),typeof HTMLFormElement<"u"&&Ir(),(f||typeof window<"u"&&!window.CustomStateSet)&&Ut()}}return!!customElements.polyfillWrapFlushCallback||(Pr()?typeof window<"u"&&!window.CustomStateSet&&Ut(HTMLElement.prototype.attachInternals):qs(!1)),o.forceCustomStateSetPolyfill=Ut,o.forceElementInternalsPolyfill=qs,Object.defineProperty(o,"__esModule",{value:!0}),o})({})})();class N extends ne{static get properties(){return{RTL:{type:Boolean},locale:{type:String},apiRoot:{type:String,reflect:!1},postType:{type:String,reflect:!1},postID:{type:String,reflect:!1}}}get _focusTarget(){return this.shadowRoot.children[0]instanceof Element?this.shadowRoot.children[0]:null}constructor(){super(),$o(this),this.addEventListener("click",this._proxyClick.bind(this)),this.addEventListener("focus",this._proxyFocus.bind(this))}connectedCallback(){super.connectedCallback(),this.apiRoot=this.apiRoot?`${this.apiRoot}/`.replace("//","/"):"/",this.api=new vt(this.nonce,this.apiRoot)}willUpdate(e){if(this.RTL===void 0){const t=this.closest("[dir]");if(t){const i=t.getAttribute("dir");i&&(this.RTL=i.toLowerCase()==="rtl")}}if(!this.locale){const t=this.closest("[lang]");if(t){const i=t.getAttribute("lang");i&&(this.locale=i)}}if(!this.locale){const t=this.getRootNode();if(t instanceof ShadowRoot&&t.host){const i=t.host;i.locale&&(this.locale=i.locale)}}if(e&&e.has("locale")&&this.locale)try{zo(this.locale)}catch(t){console.error(t)}}_proxyClick(){this.clicked=!0}_proxyFocus(){if(this._focusTarget){if(this.clicked){this.clicked=!1;return}this._focusTarget.focus()}}focus(){this._proxyFocus()}}class wi extends N{static get formAssociated(){return!0}static get styles(){return x`
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
    `}static get properties(){return{label:{type:String},context:{type:String},type:{type:String},title:{type:String},outline:{type:Boolean},round:{type:Boolean},disabled:{type:Boolean}}}get classes(){const e={"dt-button":!0,"dt-button--outline":this.outline,"dt-button--round":this.round},t=`dt-button--${this.context}`;return e[t]=!0,e}get _field(){return this.shadowRoot.querySelector("button")}get _focusTarget(){return this._field}constructor(){super(),this.context="default",this.internals=this.attachInternals()}handleClick(e){e.preventDefault(),this.type==="submit"&&this.internals.form&&this.internals.form.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0}))}render(){const e={...this.classes};return u`
      <button
        part="button"
        class=${T(e)}
        title=${this.title}
        type=${this.type}
        @click=${this.handleClick}
        ?disabled=${this.disabled}
      >
        <slot></slot>
      </button>
    `}}window.customElements.define("dt-button",wi);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const _i="important",Do=" !"+_i,Q=ht(class extends pt{constructor(o){var e;if(super(o),o.type!==ut.ATTRIBUTE||o.name!=="style"||((e=o.strings)==null?void 0:e.length)>2)throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.")}render(o){return Object.keys(o).reduce((e,t)=>{const i=o[t];return i==null?e:e+`${t=t.includes("-")?t:t.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g,"-$&").toLowerCase()}:${i};`},"")}update(o,[e]){const{style:t}=o.element;if(this.ft===void 0)return this.ft=new Set(Object.keys(e)),this.render(e);for(const i of this.ft)e[i]==null&&(this.ft.delete(i),i.includes("-")?t.removeProperty(i):t[i]=null);for(const i in e){const s=e[i];if(s!=null){this.ft.add(i);const a=typeof s=="string"&&s.endsWith(Do);i.includes("-")||a?t.setProperty(i,a?s.slice(0,-11):s,a?_i:""):t[i]=s}}return U}});/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/const $i=Object.freeze({left:0,top:0,width:16,height:16}),qe=Object.freeze({rotate:0,vFlip:!1,hFlip:!1}),xe=Object.freeze({...$i,...qe}),yt=Object.freeze({...xe,body:"",hidden:!1}),Fo=Object.freeze({width:null,height:null}),xi=Object.freeze({...Fo,...qe});function Ro(o,e=0){const t=o.replace(/^-?[0-9.]*/,"");function i(s){for(;s<0;)s+=4;return s%4}if(t===""){const s=parseInt(o);return isNaN(s)?0:i(s)}else if(t!==o){let s=0;switch(t){case"%":s=25;break;case"deg":s=90}if(s){let a=parseFloat(o.slice(0,o.length-t.length));return isNaN(a)?0:(a=a/s,a%1===0?i(a):0)}}return e}const No=/[\s,]+/;function qo(o,e){e.split(No).forEach(t=>{switch(t.trim()){case"horizontal":o.hFlip=!0;break;case"vertical":o.vFlip=!0;break}})}const ki={...xi,preserveAspectRatio:""};function Si(o){const e={...ki},t=(i,s)=>o.getAttribute(i)||s;return e.width=t("width",null),e.height=t("height",null),e.rotate=Ro(t("rotate","")),qo(e,t("flip","")),e.preserveAspectRatio=t("preserveAspectRatio",t("preserveaspectratio","")),e}function Uo(o,e){for(const t in ki)if(o[t]!==e[t])return!0;return!1}const ke=/^[a-z0-9]+(-[a-z0-9]+)*$/,Se=(o,e,t,i="")=>{const s=o.split(":");if(o.slice(0,1)==="@"){if(s.length<2||s.length>3)return null;i=s.shift().slice(1)}if(s.length>3||!s.length)return null;if(s.length>1){const r=s.pop(),l=s.pop(),d={provider:s.length>0?s[0]:i,prefix:l,name:r};return e&&!Ue(d)?null:d}const a=s[0],n=a.split("-");if(n.length>1){const r={provider:i,prefix:n.shift(),name:n.join("-")};return e&&!Ue(r)?null:r}if(t&&i===""){const r={provider:i,prefix:"",name:a};return e&&!Ue(r,t)?null:r}return null},Ue=(o,e)=>o?!!((o.provider===""||o.provider.match(ke))&&(e&&o.prefix===""||o.prefix.match(ke))&&o.name.match(ke)):!1;function Vo(o,e){const t={};!o.hFlip!=!e.hFlip&&(t.hFlip=!0),!o.vFlip!=!e.vFlip&&(t.vFlip=!0);const i=((o.rotate||0)+(e.rotate||0))%4;return i&&(t.rotate=i),t}function Ei(o,e){const t=Vo(o,e);for(const i in yt)i in qe?i in o&&!(i in t)&&(t[i]=qe[i]):i in e?t[i]=e[i]:i in o&&(t[i]=o[i]);return t}function Bo(o,e){const t=o.icons,i=o.aliases||Object.create(null),s=Object.create(null);function a(n){if(t[n])return s[n]=[];if(!(n in s)){s[n]=null;const r=i[n]&&i[n].parent,l=r&&a(r);l&&(s[n]=[r].concat(l))}return s[n]}return Object.keys(t).concat(Object.keys(i)).forEach(a),s}function Ho(o,e,t){const i=o.icons,s=o.aliases||Object.create(null);let a={};function n(r){a=Ei(i[r]||s[r],a)}return n(e),t.forEach(n),Ei(o,a)}function Ti(o,e){const t=[];if(typeof o!="object"||typeof o.icons!="object")return t;o.not_found instanceof Array&&o.not_found.forEach(s=>{e(s,null),t.push(s)});const i=Bo(o);for(const s in i){const a=i[s];a&&(e(s,Ho(o,s,a)),t.push(s))}return t}const Ko={provider:"",aliases:{},not_found:{},...$i};function wt(o,e){for(const t in e)if(t in o&&typeof o[t]!=typeof e[t])return!1;return!0}function Ai(o){if(typeof o!="object"||o===null)return null;const e=o;if(typeof e.prefix!="string"||!o.icons||typeof o.icons!="object"||!wt(o,Ko))return null;const t=e.icons;for(const s in t){const a=t[s];if(!s.match(ke)||typeof a.body!="string"||!wt(a,yt))return null}const i=e.aliases||Object.create(null);for(const s in i){const a=i[s],n=a.parent;if(!s.match(ke)||typeof n!="string"||!t[n]&&!i[n]||!wt(a,yt))return null}return e}const Ve=Object.create(null);function Wo(o,e){return{provider:o,prefix:e,icons:Object.create(null),missing:new Set}}function X(o,e){const t=Ve[o]||(Ve[o]=Object.create(null));return t[e]||(t[e]=Wo(o,e))}function _t(o,e){return Ai(e)?Ti(e,(t,i)=>{i?o.icons[t]=i:o.missing.add(t)}):[]}function Go(o,e,t){try{if(typeof t.body=="string")return o.icons[e]={...t},!0}catch{}return!1}function Zo(o,e){let t=[];return(typeof o=="string"?[o]:Object.keys(Ve)).forEach(s=>{(typeof s=="string"&&typeof e=="string"?[e]:Object.keys(Ve[s]||{})).forEach(n=>{const r=X(s,n);t=t.concat(Object.keys(r.icons).map(l=>(s!==""?"@"+s+":":"")+n+":"+l))})}),t}let Ee=!1;function Oi(o){return typeof o=="boolean"&&(Ee=o),Ee}function Te(o){const e=typeof o=="string"?Se(o,!0,Ee):o;if(e){const t=X(e.provider,e.prefix),i=e.name;return t.icons[i]||(t.missing.has(i)?null:void 0)}}function Ci(o,e){const t=Se(o,!0,Ee);if(!t)return!1;const i=X(t.provider,t.prefix);return Go(i,t.name,e)}function Li(o,e){if(typeof o!="object")return!1;if(typeof e!="string"&&(e=o.provider||""),Ee&&!e&&!o.prefix){let s=!1;return Ai(o)&&(o.prefix="",Ti(o,(a,n)=>{n&&Ci(a,n)&&(s=!0)})),s}const t=o.prefix;if(!Ue({provider:e,prefix:t,name:"a"}))return!1;const i=X(e,t);return!!_t(i,o)}function Jo(o){return!!Te(o)}function Qo(o){const e=Te(o);return e?{...xe,...e}:null}function Xo(o){const e={loaded:[],missing:[],pending:[]},t=Object.create(null);o.sort((s,a)=>s.provider!==a.provider?s.provider.localeCompare(a.provider):s.prefix!==a.prefix?s.prefix.localeCompare(a.prefix):s.name.localeCompare(a.name));let i={provider:"",prefix:"",name:""};return o.forEach(s=>{if(i.name===s.name&&i.prefix===s.prefix&&i.provider===s.provider)return;i=s;const a=s.provider,n=s.prefix,r=s.name,l=t[a]||(t[a]=Object.create(null)),d=l[n]||(l[n]=X(a,n));let h;r in d.icons?h=e.loaded:n===""||d.missing.has(r)?h=e.missing:h=e.pending;const g={provider:a,prefix:n,name:r};h.push(g)}),e}function Ii(o,e){o.forEach(t=>{const i=t.loaderCallbacks;i&&(t.loaderCallbacks=i.filter(s=>s.id!==e))})}function Yo(o){o.pendingCallbacksFlag||(o.pendingCallbacksFlag=!0,setTimeout(()=>{o.pendingCallbacksFlag=!1;const e=o.loaderCallbacks?o.loaderCallbacks.slice(0):[];if(!e.length)return;let t=!1;const i=o.provider,s=o.prefix;e.forEach(a=>{const n=a.icons,r=n.pending.length;n.pending=n.pending.filter(l=>{if(l.prefix!==s)return!0;const d=l.name;if(o.icons[d])n.loaded.push({provider:i,prefix:s,name:d});else if(o.missing.has(d))n.missing.push({provider:i,prefix:s,name:d});else return t=!0,!0;return!1}),n.pending.length!==r&&(t||Ii([o],a.id),a.callback(n.loaded.slice(0),n.missing.slice(0),n.pending.slice(0),a.abort))})}))}let ea=0;function ta(o,e,t){const i=ea++,s=Ii.bind(null,t,i);if(!e.pending.length)return s;const a={id:i,icons:e,callback:o,abort:s};return t.forEach(n=>{(n.loaderCallbacks||(n.loaderCallbacks=[])).push(a)}),s}const $t=Object.create(null);function Pi(o,e){$t[o]=e}function xt(o){return $t[o]||$t[""]}function ia(o,e=!0,t=!1){const i=[];return o.forEach(s=>{const a=typeof s=="string"?Se(s,e,t):s;a&&i.push(a)}),i}var sa={resources:[],index:0,timeout:2e3,rotate:750,random:!1,dataAfterTimeout:!1};function oa(o,e,t,i){const s=o.resources.length,a=o.random?Math.floor(Math.random()*s):o.index;let n;if(o.random){let E=o.resources.slice(0);for(n=[];E.length>1;){const R=Math.floor(Math.random()*E.length);n.push(E[R]),E=E.slice(0,R).concat(E.slice(R+1))}n=n.concat(E)}else n=o.resources.slice(a).concat(o.resources.slice(0,a));const r=Date.now();let l="pending",d=0,h,g=null,m=[],y=[];typeof i=="function"&&y.push(i);function w(){g&&(clearTimeout(g),g=null)}function O(){l==="pending"&&(l="aborted"),w(),m.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),m=[]}function C(E,R){R&&(y=[]),typeof E=="function"&&y.push(E)}function M(){return{startTime:r,payload:e,status:l,queriesSent:d,queriesPending:m.length,subscribe:C,abort:O}}function A(){l="failed",y.forEach(E=>{E(void 0,h)})}function ee(){m.forEach(E=>{E.status==="pending"&&(E.status="aborted")}),m=[]}function Qe(E,R,ue){const ie=R!=="success";switch(m=m.filter(z=>z!==E),l){case"pending":break;case"failed":if(ie||!o.dataAfterTimeout)return;break;default:return}if(R==="abort"){h=ue,A();return}if(ie){h=ue,m.length||(n.length?te():A());return}if(w(),ee(),!o.random){const z=o.resources.indexOf(E.resource);z!==-1&&z!==o.index&&(o.index=z)}l="completed",y.forEach(z=>{z(ue)})}function te(){if(l!=="pending")return;w();const E=n.shift();if(E===void 0){if(m.length){g=setTimeout(()=>{w(),l==="pending"&&(ee(),A())},o.timeout);return}A();return}const R={status:"pending",resource:E,callback:(ue,ie)=>{Qe(R,ue,ie)}};m.push(R),d++,g=setTimeout(te,o.rotate),t(E,e,R.callback)}return setTimeout(te),M}function Mi(o){const e={...sa,...o};let t=[];function i(){t=t.filter(r=>r().status==="pending")}function s(r,l,d){const h=oa(e,r,l,(g,m)=>{i(),d&&d(g,m)});return t.push(h),h}function a(r){return t.find(l=>r(l))||null}return{query:s,find:a,setIndex:r=>{e.index=r},getIndex:()=>e.index,cleanup:i}}function kt(o){let e;if(typeof o.resources=="string")e=[o.resources];else if(e=o.resources,!(e instanceof Array)||!e.length)return null;return{resources:e,path:o.path||"/",maxURL:o.maxURL||500,rotate:o.rotate||750,timeout:o.timeout||5e3,random:o.random===!0,index:o.index||0,dataAfterTimeout:o.dataAfterTimeout!==!1}}const Be=Object.create(null),Ae=["https://api.simplesvg.com","https://api.unisvg.com"],He=[];for(;Ae.length>0;)Ae.length===1||Math.random()>.5?He.push(Ae.shift()):He.push(Ae.pop());Be[""]=kt({resources:["https://api.iconify.design"].concat(He)});function ji(o,e){const t=kt(e);return t===null?!1:(Be[o]=t,!0)}function Ke(o){return Be[o]}function aa(){return Object.keys(Be)}function zi(){}const St=Object.create(null);function na(o){if(!St[o]){const e=Ke(o);if(!e)return;const t=Mi(e),i={config:e,redundancy:t};St[o]=i}return St[o]}function Di(o,e,t){let i,s;if(typeof o=="string"){const a=xt(o);if(!a)return t(void 0,424),zi;s=a.send;const n=na(o);n&&(i=n.redundancy)}else{const a=kt(o);if(a){i=Mi(a);const n=o.resources?o.resources[0]:"",r=xt(n);r&&(s=r.send)}}return!i||!s?(t(void 0,424),zi):i.query(e,s,t)().abort}const Fi="iconify2",Oe="iconify",Ri=Oe+"-count",Ni=Oe+"-version",qi=36e5,ra=168;function Et(o,e){try{return o.getItem(e)}catch{}}function Tt(o,e,t){try{return o.setItem(e,t),!0}catch{}}function Ui(o,e){try{o.removeItem(e)}catch{}}function At(o,e){return Tt(o,Ri,e.toString())}function Ot(o){return parseInt(Et(o,Ri))||0}const le={local:!0,session:!0},Vi={local:new Set,session:new Set};let Ct=!1;function la(o){Ct=o}let We=typeof window>"u"?{}:window;function Bi(o){const e=o+"Storage";try{if(We&&We[e]&&typeof We[e].length=="number")return We[e]}catch{}le[o]=!1}function Hi(o,e){const t=Bi(o);if(!t)return;const i=Et(t,Ni);if(i!==Fi){if(i){const r=Ot(t);for(let l=0;l<r;l++)Ui(t,Oe+l.toString())}Tt(t,Ni,Fi),At(t,0);return}const s=Math.floor(Date.now()/qi)-ra,a=r=>{const l=Oe+r.toString(),d=Et(t,l);if(typeof d=="string"){try{const h=JSON.parse(d);if(typeof h=="object"&&typeof h.cached=="number"&&h.cached>s&&typeof h.provider=="string"&&typeof h.data=="object"&&typeof h.data.prefix=="string"&&e(h,r))return!0}catch{}Ui(t,l)}};let n=Ot(t);for(let r=n-1;r>=0;r--)a(r)||(r===n-1?(n--,At(t,n)):Vi[o].add(r))}function Ki(){if(!Ct){la(!0);for(const o in le)Hi(o,e=>{const t=e.data,i=e.provider,s=t.prefix,a=X(i,s);if(!_t(a,t).length)return!1;const n=t.lastModified||-1;return a.lastModifiedCached=a.lastModifiedCached?Math.min(a.lastModifiedCached,n):n,!0})}}function da(o,e){const t=o.lastModifiedCached;if(t&&t>=e)return t===e;if(o.lastModifiedCached=e,t)for(const i in le)Hi(i,s=>{const a=s.data;return s.provider!==o.provider||a.prefix!==o.prefix||a.lastModified===e});return!0}function ca(o,e){Ct||Ki();function t(i){let s;if(!le[i]||!(s=Bi(i)))return;const a=Vi[i];let n;if(a.size)a.delete(n=Array.from(a).shift());else if(n=Ot(s),!At(s,n+1))return;const r={cached:Math.floor(Date.now()/qi),provider:o.provider,data:e};return Tt(s,Oe+n.toString(),JSON.stringify(r))}e.lastModified&&!da(o,e.lastModified)||Object.keys(e.icons).length&&(e.not_found&&(e=Object.assign({},e),delete e.not_found),t("local")||t("session"))}function Wi(){}function ua(o){o.iconsLoaderFlag||(o.iconsLoaderFlag=!0,setTimeout(()=>{o.iconsLoaderFlag=!1,Yo(o)}))}function ha(o,e){o.iconsToLoad?o.iconsToLoad=o.iconsToLoad.concat(e).sort():o.iconsToLoad=e,o.iconsQueueFlag||(o.iconsQueueFlag=!0,setTimeout(()=>{o.iconsQueueFlag=!1;const{provider:t,prefix:i}=o,s=o.iconsToLoad;delete o.iconsToLoad;let a;if(!s||!(a=xt(t)))return;a.prepare(t,i,s).forEach(r=>{Di(t,r,l=>{if(typeof l!="object")r.icons.forEach(d=>{o.missing.add(d)});else try{const d=_t(o,l);if(!d.length)return;const h=o.pendingIcons;h&&d.forEach(g=>{h.delete(g)}),ca(o,l)}catch(d){console.error(d)}ua(o)})})}))}const Lt=(o,e)=>{const t=ia(o,!0,Oi()),i=Xo(t);if(!i.pending.length){let l=!0;return e&&setTimeout(()=>{l&&e(i.loaded,i.missing,i.pending,Wi)}),()=>{l=!1}}const s=Object.create(null),a=[];let n,r;return i.pending.forEach(l=>{const{provider:d,prefix:h}=l;if(h===r&&d===n)return;n=d,r=h,a.push(X(d,h));const g=s[d]||(s[d]=Object.create(null));g[h]||(g[h]=[])}),i.pending.forEach(l=>{const{provider:d,prefix:h,name:g}=l,m=X(d,h),y=m.pendingIcons||(m.pendingIcons=new Set);y.has(g)||(y.add(g),s[d][h].push(g))}),a.forEach(l=>{const{provider:d,prefix:h}=l;s[d][h].length&&ha(l,s[d][h])}),e?ta(e,i,a):Wi},pa=o=>new Promise((e,t)=>{const i=typeof o=="string"?Se(o,!0):o;if(!i){t(o);return}Lt([i||o],s=>{if(s.length&&i){const a=Te(i);if(a){e({...xe,...a});return}}t(o)})});function fa(o){try{const e=typeof o=="string"?JSON.parse(o):o;if(typeof e.body=="string")return{...e}}catch{}}function ba(o,e){const t=typeof o=="string"?Se(o,!0,!0):null;if(!t){const a=fa(o);return{value:o,data:a}}const i=Te(t);if(i!==void 0||!t.prefix)return{value:o,name:t,data:i};const s=Lt([t],()=>e(o,t,Te(t)));return{value:o,name:t,loading:s}}function It(o){return o.hasAttribute("inline")}let Gi=!1;try{Gi=navigator.vendor.indexOf("Apple")===0}catch{}function ga(o,e){switch(e){case"svg":case"bg":case"mask":return e}return e!=="style"&&(Gi||o.indexOf("<a")===-1)?"svg":o.indexOf("currentColor")===-1?"bg":"mask"}const ma=/(-?[0-9.]*[0-9]+[0-9.]*)/g,va=/^-?[0-9.]*[0-9]+[0-9.]*$/g;function Pt(o,e,t){if(e===1)return o;if(t=t||100,typeof o=="number")return Math.ceil(o*e*t)/t;if(typeof o!="string")return o;const i=o.split(ma);if(i===null||!i.length)return o;const s=[];let a=i.shift(),n=va.test(a);for(;;){if(n){const r=parseFloat(a);isNaN(r)?s.push(a):s.push(Math.ceil(r*e*t)/t)}else s.push(a);if(a=i.shift(),a===void 0)return s.join("");n=!n}}function Zi(o,e){const t={...xe,...o},i={...xi,...e},s={left:t.left,top:t.top,width:t.width,height:t.height};let a=t.body;[t,i].forEach(y=>{const w=[],O=y.hFlip,C=y.vFlip;let M=y.rotate;O?C?M+=2:(w.push("translate("+(s.width+s.left).toString()+" "+(0-s.top).toString()+")"),w.push("scale(-1 1)"),s.top=s.left=0):C&&(w.push("translate("+(0-s.left).toString()+" "+(s.height+s.top).toString()+")"),w.push("scale(1 -1)"),s.top=s.left=0);let A;switch(M<0&&(M-=Math.floor(M/4)*4),M=M%4,M){case 1:A=s.height/2+s.top,w.unshift("rotate(90 "+A.toString()+" "+A.toString()+")");break;case 2:w.unshift("rotate(180 "+(s.width/2+s.left).toString()+" "+(s.height/2+s.top).toString()+")");break;case 3:A=s.width/2+s.left,w.unshift("rotate(-90 "+A.toString()+" "+A.toString()+")");break}M%2===1&&(s.left!==s.top&&(A=s.left,s.left=s.top,s.top=A),s.width!==s.height&&(A=s.width,s.width=s.height,s.height=A)),w.length&&(a='<g transform="'+w.join(" ")+'">'+a+"</g>")});const n=i.width,r=i.height,l=s.width,d=s.height;let h,g;return n===null?(g=r===null?"1em":r==="auto"?d:r,h=Pt(g,l/d)):(h=n==="auto"?l:n,g=r===null?Pt(h,d/l):r==="auto"?d:r),{attributes:{width:h.toString(),height:g.toString(),viewBox:s.left.toString()+" "+s.top.toString()+" "+l.toString()+" "+d.toString()},body:a}}let Ge=(()=>{let o;try{if(o=fetch,typeof o=="function")return o}catch{}})();function ya(o){Ge=o}function wa(){return Ge}function _a(o,e){const t=Ke(o);if(!t)return 0;let i;if(!t.maxURL)i=0;else{let s=0;t.resources.forEach(n=>{s=Math.max(s,n.length)});const a=e+".json?icons=";i=t.maxURL-s-t.path.length-a.length}return i}function $a(o){return o===404}const xa=(o,e,t)=>{const i=[],s=_a(o,e),a="icons";let n={type:a,provider:o,prefix:e,icons:[]},r=0;return t.forEach((l,d)=>{r+=l.length+1,r>=s&&d>0&&(i.push(n),n={type:a,provider:o,prefix:e,icons:[]},r=l.length),n.icons.push(l)}),i.push(n),i};function ka(o){if(typeof o=="string"){const e=Ke(o);if(e)return e.path}return"/"}const Sa={prepare:xa,send:(o,e,t)=>{if(!Ge){t("abort",424);return}let i=ka(e.provider);switch(e.type){case"icons":{const a=e.prefix,r=e.icons.join(","),l=new URLSearchParams({icons:r});i+=a+".json?"+l.toString();break}case"custom":{const a=e.uri;i+=a.slice(0,1)==="/"?a.slice(1):a;break}default:t("abort",400);return}let s=503;Ge(o+i).then(a=>{const n=a.status;if(n!==200){setTimeout(()=>{t($a(n)?"abort":"next",n)});return}return s=501,a.json()}).then(a=>{if(typeof a!="object"||a===null){setTimeout(()=>{a===404?t("abort",a):t("next",s)});return}setTimeout(()=>{t("success",a)})}).catch(()=>{t("next",s)})}};function Ji(o,e){switch(o){case"local":case"session":le[o]=e;break;case"all":for(const t in le)le[t]=e;break}}function Qi(){Pi("",Sa),Oi(!0);let o;try{o=window}catch{}if(o){if(Ki(),o.IconifyPreload!==void 0){const t=o.IconifyPreload,i="Invalid IconifyPreload syntax.";typeof t=="object"&&t!==null&&(t instanceof Array?t:[t]).forEach(s=>{try{(typeof s!="object"||s===null||s instanceof Array||typeof s.icons!="object"||typeof s.prefix!="string"||!Li(s))&&console.error(i)}catch{console.error(i)}})}if(o.IconifyProviders!==void 0){const t=o.IconifyProviders;if(typeof t=="object"&&t!==null)for(const i in t){const s="IconifyProviders["+i+"] is invalid.";try{const a=t[i];if(typeof a!="object"||!a||a.resources===void 0)continue;ji(i,a)||console.error(s)}catch{console.error(s)}}}}return{enableCache:t=>Ji(t,!0),disableCache:t=>Ji(t,!1),iconExists:Jo,getIcon:Qo,listIcons:Zo,addIcon:Ci,addCollection:Li,calculateSize:Pt,buildIcon:Zi,loadIcons:Lt,loadIcon:pa,addAPIProvider:ji,_api:{getAPIConfig:Ke,setAPIModule:Pi,sendAPIQuery:Di,setFetch:ya,getFetch:wa,listAPIProviders:aa}}}function Xi(o,e){let t=o.indexOf("xlink:")===-1?"":' xmlns:xlink="http://www.w3.org/1999/xlink"';for(const i in e)t+=" "+i+'="'+e[i]+'"';return'<svg xmlns="http://www.w3.org/2000/svg"'+t+">"+o+"</svg>"}function Ea(o){return o.replace(/"/g,"'").replace(/%/g,"%25").replace(/#/g,"%23").replace(/</g,"%3C").replace(/>/g,"%3E").replace(/\s+/g," ")}function Ta(o){return'url("data:image/svg+xml,'+Ea(o)+'")'}const Mt={"background-color":"currentColor"},Yi={"background-color":"transparent"},es={image:"var(--svg)",repeat:"no-repeat",size:"100% 100%"},ts={"-webkit-mask":Mt,mask:Mt,background:Yi};for(const o in ts){const e=ts[o];for(const t in es)e[o+"-"+t]=es[t]}function is(o){return o+(o.match(/^[-0-9.]+$/)?"px":"")}function Aa(o,e,t){const i=document.createElement("span");let s=o.body;s.indexOf("<a")!==-1&&(s+="<!-- "+Date.now()+" -->");const a=o.attributes,n=Xi(s,{...a,width:e.width+"",height:e.height+""}),r=Ta(n),l=i.style,d={"--svg":r,width:is(a.width),height:is(a.height),...t?Mt:Yi};for(const h in d)l.setProperty(h,d[h]);return i}function Oa(o){const e=document.createElement("span");return e.innerHTML=Xi(o.body,o.attributes),e.firstChild}function ss(o,e){const t=e.icon.data,i=e.customisations,s=Zi(t,i);i.preserveAspectRatio&&(s.attributes.preserveAspectRatio=i.preserveAspectRatio);const a=e.renderedMode;let n;switch(a){case"svg":n=Oa(s);break;default:n=Aa(s,{...xe,...t},a==="mask")}const r=Array.from(o.childNodes).find(l=>{const d=l.tagName&&l.tagName.toUpperCase();return d==="SPAN"||d==="SVG"});r?n.tagName==="SPAN"&&r.tagName===n.tagName?r.setAttribute("style",n.getAttribute("style")):o.replaceChild(n,r):o.appendChild(n)}const jt="data-style";function os(o,e){let t=Array.from(o.childNodes).find(i=>i.hasAttribute&&i.hasAttribute(jt));t||(t=document.createElement("style"),t.setAttribute(jt,jt),o.appendChild(t)),t.textContent=":host{display:inline-block;vertical-align:"+(e?"-0.125em":"0")+"}span,svg{display:block}"}function as(o,e,t){const i=t&&(t.rendered?t:t.lastRender);return{rendered:!1,inline:e,icon:o,lastRender:i}}function Ca(o="iconify-icon"){let e,t;try{e=window.customElements,t=window.HTMLElement}catch{return}if(!e||!t)return;const i=e.get(o);if(i)return i;const s=["icon","mode","inline","width","height","rotate","flip"],a=class extends t{constructor(){super();Me(this,"_shadowRoot");Me(this,"_state");Me(this,"_checkQueued",!1);const l=this._shadowRoot=this.attachShadow({mode:"open"}),d=It(this);os(l,d),this._state=as({value:""},d),this._queueCheck()}static get observedAttributes(){return s.slice(0)}attributeChangedCallback(l){if(l==="inline"){const d=It(this),h=this._state;d!==h.inline&&(h.inline=d,os(this._shadowRoot,d))}else this._queueCheck()}get icon(){const l=this.getAttribute("icon");if(l&&l.slice(0,1)==="{")try{return JSON.parse(l)}catch{}return l}set icon(l){typeof l=="object"&&(l=JSON.stringify(l)),this.setAttribute("icon",l)}get inline(){return It(this)}set inline(l){this.setAttribute("inline",l?"true":null)}restartAnimation(){const l=this._state;if(l.rendered){const d=this._shadowRoot;if(l.renderedMode==="svg")try{d.lastChild.setCurrentTime(0);return}catch{}ss(d,l)}}get status(){const l=this._state;return l.rendered?"rendered":l.icon.data===null?"failed":"loading"}_queueCheck(){this._checkQueued||(this._checkQueued=!0,setTimeout(()=>{this._check()}))}_check(){if(!this._checkQueued)return;this._checkQueued=!1;const l=this._state,d=this.getAttribute("icon");if(d!==l.icon.value){this._iconChanged(d);return}if(!l.rendered)return;const h=this.getAttribute("mode"),g=Si(this);(l.attrMode!==h||Uo(l.customisations,g))&&this._renderIcon(l.icon,g,h)}_iconChanged(l){const d=ba(l,(h,g,m)=>{const y=this._state;if(y.rendered||this.getAttribute("icon")!==h)return;const w={value:h,name:g,data:m};w.data?this._gotIconData(w):y.icon=w});d.data?this._gotIconData(d):this._state=as(d,this._state.inline,this._state)}_gotIconData(l){this._checkQueued=!1,this._renderIcon(l,Si(this),this.getAttribute("mode"))}_renderIcon(l,d,h){const g=ga(l.data.body,h),m=this._state.inline;ss(this._shadowRoot,this._state={rendered:!0,icon:l,inline:m,customisations:d,attrMode:h,renderedMode:g})}};s.forEach(r=>{r in a.prototype||Object.defineProperty(a.prototype,r,{get:function(){return this.getAttribute(r)},set:function(l){this.setAttribute(r,l)}})});const n=Qi();for(const r in n)a[r]=a.prototype[r]=n[r];return e.define(o,a),a}const La=Ca()||Qi(),{enableCache:Nr,disableCache:qr,iconExists:Ur,getIcon:Vr,listIcons:Br,addIcon:Hr,addCollection:Kr,calculateSize:Wr,buildIcon:Gr,loadIcons:Zr,loadIcon:Jr,addAPIProvider:Qr,_api:Xr}=La;class ns extends N{static get styles(){return x`
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
    `}static get properties(){return{...super.properties,icon:{type:String},tooltip:{type:String},tooltip_open:{type:Boolean},size:{type:String},slotted:{type:Boolean,attribute:!1}}}firstUpdated(){const e=this.shadowRoot.querySelector("slot[name=tooltip]");e&&e.addEventListener("slotchange",t=>{const s=t.target.assignedNodes();let a=!1;s.length>0&&(s[0].tagName==="SLOT"?a=s[0].assignedNodes().length>0:a=!0),this.slotted=a})}_toggleTooltip(){this.tooltip_open?this.tooltip_open=!1:this.tooltip_open=!0}tooltipClasses(){return{tooltip:!0,slotted:this.slotted}}render(){const e=this.tooltip?u`<div
          class="${T(this.tooltipClasses())}"
          ?hidden=${this.tooltip_open}
          @click="${this._toggleTooltip}"
        >
          <slot name="tooltip"></slot>
          <span class="attr-msg">${this.tooltip}</span>
        </div>`:null;return u`
      <div class="icon-container">
        <iconify-icon
          icon=${this.icon}
          width="${this.size}"
          @click=${this._toggleTooltip}
        ></iconify-icon>
        ${e}
      </div>
    `}}window.customElements.define("dt-icon",ns);class rs extends N{static get styles(){return x`
      :host {
        font-family: var(--font-family);
        font-size: var(--dt-label-font-size, 14px);
        font-weight: var(--dt-label-font-weight, 700);
        color: var(--dt-label-color, #000);
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
    `}static get properties(){return{icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String}}}firstUpdated(){const e=this.shadowRoot.querySelectorAll("slot");if(e&&e.length)for(const n of e)n.addEventListener("slotchange",r=>{const l=r.target.assignedNodes();let d=!1;l.length&&(l[0].tagName==="SLOT"?d=l[0].assignedNodes().length||l[0].children.length:d=!0),d&&r.target.classList.add("slotted")});const i=this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({flatten:!0});for(const n of i)n.style.height="100%",n.style.width="auto";const s=this.shadowRoot.querySelector("slot:not([name])"),a=this.shadowRoot.querySelector(".label");if(s&&a){const n=s.assignedNodes().map(r=>{var l;return(l=r.textContent)==null?void 0:l.trim()}).filter(r=>r).join(" ");n&&a.setAttribute("title",n)}}get _slottedChildren(){return this.shadowRoot.querySelector("slot").assignedElements({flatten:!0})}_mdiToIconify(e){return/^mdi\s+mdi-/.test(e)?e.replace(/^mdi\s+mdi-/,"mdi:"):/^mdi-/.test(e)?e.replace(/^mdi-/,"mdi:"):e}_renderIconContent(){if(!this.icon||!this.icon.trim())return null;const e=this.icon.trim();return!(e.startsWith("http://")||e.startsWith("https://")||e.startsWith("/")||e.startsWith("data:"))&&e.toLowerCase().includes("mdi")?u`<dt-icon icon="${this._mdiToIconify(e)}" size="1em"></dt-icon>`:u`<img src="${e}" alt="${this.iconAltText||""}" />`}render(){const e=u`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;return u`
      <div class="label" part="label">
        <span class="icon icon-start">
          <slot name="icon-start"
            >${this._renderIconContent()}</slot
          >
        </span>
        <span class="label-text"><slot></slot></span>

        ${this.private?u`<span class="icon private">
              ${e}
              <span class="tooltip"
                >${this.privateLabel||I("Private Field: Only I can see its content")}</span
              >
            </span> `:null}

        <span class="icon icon-end">
          <slot name="icon-end"></slot>
        </span>
      </div>
    `}}window.customElements.define("dt-label",rs);class Ia extends ne{static get styles(){return x`
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
          var(--dt-spinner-color-1, #919191);
        border-radius: 50%;
        border-top-color: var(--dt-spinner-color-2, #000);
        display: inline-block;
        height: var(--dt-spinner-size, 1rem);
        width: var(--dt-spinner-size, 1rem);
      }
    `}}window.customElements.define("dt-spinner",Ia);class Pa extends ne{static get styles(){return x`
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
        border-bottom: var(--dt-checkmark-width) solid currentcolor;
        border-right: var(--dt-checkmark-width) solid currentcolor;
      }
    `}}window.customElements.define("dt-checkmark",Pa);/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const B=o=>o??L;class D extends N{static get formAssociated(){return!0}static get styles(){return[x`
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
          margin-top: 1rem;
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
      `]}static get properties(){return{...super.properties,name:{type:String},label:{type:String},icon:{type:String},iconAltText:{type:String},private:{type:Boolean},privateLabel:{type:String},disabled:{type:Boolean},required:{type:Boolean},requiredMessage:{type:String},touched:{type:Boolean,state:!0},invalid:{type:Boolean,state:!0},error:{type:String},loading:{type:Boolean},saved:{type:Boolean},errorSlotted:{type:Boolean,attribute:!1}}}get _field(){return this.shadowRoot.querySelector("input, textarea, select")}get _focusTarget(){return this._field}constructor(){super(),this.savedTimeout=null,this.touched=!1,this.invalid=!1,this.internals=this.attachInternals(),this.addEventListener("invalid",e=>{e&&e.preventDefault(),this.touched=!0,this._validateRequired()})}firstUpdated(...e){super.firstUpdated(...e);const t=this.shadowRoot.querySelector("slot[name=error]");t&&t.addEventListener("slotchange",s=>{const n=s.target.assignedNodes();let r=!1;n.length>0&&(n[0].tagName==="SLOT"?r=n[0].assignedNodes().length>0:r=!0),this.errorSlotted=r});const i=D._jsonToFormData(this.value,this.name);this.internals.setFormValue(i),this._validateRequired()}static _buildFormData(e,t,i){if(t&&typeof t=="object"&&!(t instanceof Date)&&!(t instanceof File))Object.keys(t).forEach(s=>{this._buildFormData(e,t[s],i?`${i}[${s}]`:s)});else{const s=t??"";e.append(i,s)}}static _jsonToFormData(e,t){const i=new FormData;return D._buildFormData(i,e,t),i}_setFormValue(e){const t=D._jsonToFormData(e,this.name);this.internals.setFormValue(t,e),this._validateRequired(),this.touched=!0}_validateRequired(){}labelTemplate(){return this.label?u`
      <dt-label
        ?private=${this.private}
        privateLabel="${B(this.privateLabel)}"
        iconAltText="${B(this.iconAltText)}"
        icon="${B(this.icon)}"
        exportparts="label: label-container"
      >
        ${this.icon?null:u`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    `:""}_errorClasses(){return{"error-container":!0,slotted:this.errorSlotted}}renderIcons(){return u`
      ${this.renderIconInvalid()} ${this.renderError()}
      ${this.renderIconLoading()} ${this.renderIconSaved()}
    `}renderIconInvalid(){return this.touched&&this.invalid?u`<div class="${T(this._errorClasses())}">
          <dt-icon
            icon="mdi:alert-circle"
            class="alert"
            size="1.4rem"
          ></dt-icon>
          <span class="error-text"> ${this.internals.validationMessage} </span>
        </div> `:null}renderIconLoading(){return this.loading?u`<dt-spinner class="icon-overlay"></dt-spinner>`:null}renderIconSaved(){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?u`<dt-checkmark
          class="icon-overlay success fade-out"
        ></dt-checkmark>`:null}renderError(){return this.error?u`<div class="${T(this._errorClasses())}">
          <dt-icon icon="mdi:alert-circle" class="alert" size="1rem"></dt-icon>
          <span class="error-text">
            <slot name="error"></slot>
            <span class="attr-msg">${this.error}</span>
          </span>
        </div>`:null}render(){return u`
      ${this.labelTemplate()}
      <slot></slot>
    `}reset(){var e;(e=this._field)!=null&&e.reset&&this._field.reset(),this.value="",this._setFormValue("")}}/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{I:Ma}=ro,ls=()=>document.createComment(""),Ce=(o,e,t)=>{var a;const i=o._$AA.parentNode,s=e===void 0?o._$AB:e._$AA;if(t===void 0){const n=i.insertBefore(ls(),s),r=i.insertBefore(ls(),s);t=new Ma(n,r,o,o.options)}else{const n=t._$AB.nextSibling,r=t._$AM,l=r!==o;if(l){let d;(a=t._$AQ)==null||a.call(t,o),t._$AM=o,t._$AP!==void 0&&(d=o._$AU)!==r._$AU&&t._$AP(d)}if(n!==s||l){let d=t._$AA;for(;d!==n;){const h=d.nextSibling;i.insertBefore(d,s),d=h}}}return t},de=(o,e,t=o)=>(o._$AI(e,t),o),ja={},za=(o,e=ja)=>o._$AH=e,Da=o=>o._$AH,zt=o=>{var i;(i=o._$AP)==null||i.call(o,!1,!0);let e=o._$AA;const t=o._$AB.nextSibling;for(;e!==t;){const s=e.nextSibling;e.remove(),e=s}};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const ds=(o,e,t)=>{const i=new Map;for(let s=e;s<=t;s++)i.set(o[s],s);return i},ce=ht(class extends pt{constructor(o){if(super(o),o.type!==ut.CHILD)throw Error("repeat() can only be used in text expressions")}dt(o,e,t){let i;t===void 0?t=e:e!==void 0&&(i=e);const s=[],a=[];let n=0;for(const r of o)s[n]=i?i(r,n):n,a[n]=t(r,n),n++;return{values:a,keys:s}}render(o,e,t){return this.dt(o,e,t).values}update(o,[e,t,i]){const s=Da(o),{values:a,keys:n}=this.dt(e,t,i);if(!Array.isArray(s))return this.ut=n,a;const r=this.ut??(this.ut=[]),l=[];let d,h,g=0,m=s.length-1,y=0,w=a.length-1;for(;g<=m&&y<=w;)if(s[g]===null)g++;else if(s[m]===null)m--;else if(r[g]===n[y])l[y]=de(s[g],a[y]),g++,y++;else if(r[m]===n[w])l[w]=de(s[m],a[w]),m--,w--;else if(r[g]===n[w])l[w]=de(s[g],a[w]),Ce(o,l[w+1],s[g]),g++,w--;else if(r[m]===n[y])l[y]=de(s[m],a[y]),Ce(o,s[g],s[m]),m--,y++;else if(d===void 0&&(d=ds(n,y,w),h=ds(r,g,m)),d.has(r[g]))if(d.has(r[m])){const O=h.get(n[y]),C=O!==void 0?s[O]:null;if(C===null){const M=Ce(o,s[g]);de(M,a[y]),l[y]=M}else l[y]=de(C,a[y]),Ce(o,s[g],C),s[O]=null;y++}else zt(s[m]),m--;else zt(s[g]),g++;for(;y<=w;){const O=Ce(o,l[w+1]);de(O,a[y]),l[y++]=O}for(;g<=m;){const O=s[g++];O!==null&&zt(O)}return this.ut=n,za(o,l),U}}),Fa=o=>class extends o{constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},query:{type:String,state:!0},options:{type:Array},filteredOptions:{type:Array,state:!0},open:{type:Boolean,state:!0},canUpdate:{type:Boolean,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean}}}willUpdate(e){if(super.willUpdate(e),e&&!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");!e.style.getPropertyValue("--container-width")&&e.clientWidth>0&&e.style.setProperty("--container-width",`${e.clientWidth}px`)}_select(){console.error("Must implement `_select(value)` function"),this._clearSearch()}static _focusInput(e){e.target===e.currentTarget&&e.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!0,this.activeIndex=-1)}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1,this.canUpdate=!0)}_inputKeyDown(e){}_inputKeyUp(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0,this.query=e.target.value;break}}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,s=t.offsetTop+t.clientHeight,a=e.scrollTop,n=e.scrollTop+e.clientHeight;s>n?e.scrollTo({top:s-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select(this.query):this._select(this.filteredOptions[this.activeIndex].id))}_clickOption(e){e.target&&e.target.value&&this._select(e.target.value)}_clickAddNew(e){var t;e.target&&this._select((t=e.target.dataset)==null?void 0:t.label)}_clearSearch(){const e=this.shadowRoot.querySelector("input");e&&(e.value="")}_listHighlightNext(){this.allowAdd?this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1):this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_renderOption(e,t){return u`
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
    `}_baseRenderOptions(){return this.filteredOptions.length?ce(this.filteredOptions,e=>e.id,(e,t)=>this._renderOption(e,t)):this.loading?u`<li><div>${I("Loading options...")}</div></li>`:u`<li><div>${I("No Data Available")}</div></li>`}_renderOptions(){let e=this._baseRenderOptions();return this.allowAdd&&this.query&&(Array.isArray(e)||(e=[e]),e.push(u`<li tabindex="-1">
        <button
          data-label="${this.query}"
          @click="${this._clickAddNew}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          class="${this.activeIndex>-1&&this.activeIndex>=this.filteredOptions.length?"active":""}"
        >
          ${I("Add")} "${this.query}"
        </button>
      </li>`)),e}};class Ze extends Fa(D){static get styles(){return[...super.styles,x`
        :host {
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
        }

        .input-group {
          cursor: text; /* Indicates the area is clickable */
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
        }

        .field-container input,
        .field-container .selected-option {
          //height: 1.5rem;
        }

        .selected-option {
          cursor: default;
          border: 1px solid var(--dt-multi-select-tag-border-color, #c2e0ff);
          background-color: var(
            --dt-multi-select-tag-background-color,
            #c2e0ff
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
      `]}static get properties(){return{...super.properties,placeholder:{type:String},containerHeight:{type:Number,state:!0}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length)if(typeof this.value[0]=="string")this.value=[...this.value.filter(i=>i!==`-${e}`),e];else{let i=!1;const s=this.value.map(a=>{const n={...a};return a.id===e.id&&a.delete&&(delete n.delete,i=!0),n});i||s.push(e),this.value=s}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._setFormValue(this.value),this.query&&(this.query=""),this._clearSearch()}_remove(e){if(e.stopPropagation(),e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(i=>i===e.target.dataset.value?`-${i}`:i),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value),this.open&&this.shadowRoot.querySelector("input").focus()}document.activeElement.blur()}updated(){super.updated(),this._updateContainerHeight()}_updateContainerHeight(){const e=this.shadowRoot.querySelector(".field-container");if(e){const t=e.offsetHeight;this.containerHeight!==t&&(this.containerHeight=t,this.requestUpdate())}}_filterOptions(){return this.filteredOptions=(this.options||[]).filter(e=>!(this.value||[]).includes(e.id)&&(!this.query||e.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),i=e.has("query"),s=e.has("options");(t||i||s)&&this._filterOptions()}}_handleDivClick(){const e=this.renderRoot.querySelector("input");e&&e.focus()}_handleItemClick(e){e.stopPropagation(),document.activeElement.blur()}_renderSelectedOptions(){return this.options&&this.value&&this.value.filter(e=>e.charAt(0)!=="-").map(e=>u`
            <div class="selected-option"
              @click="${this._handleItemClick}"
              @keydown="${this._handleItemClick}">
              <span>${this.options.find(t=>t.id===e).label}</span>
              <button
                @click="${this._remove}"
                ?disabled="${this.disabled}"
                data-value="${e}"
              >
                x
              </button>
            </div>
          `)}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"field-container":!0,invalid:this.touched&&this.invalid}}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return u`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}"
          @click="${this._handleDivClick}"
          @keydown="${this._handleDivClick}">
        <div
          class="${T(this.classes)}"
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
        <ul class="option-list" style=${Q(e)}>
          ${this._renderOptions()}
        </ul>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-select",Ze);class Ra extends N{static get styles(){return x`
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
    `}static get properties(){return{key:{type:String},metric:{type:Object},active:{type:Boolean,reflect:!0},disabled:{type:Boolean},missingIcon:{type:String}}}renderIcon(){var s;const e=(s=window==null?void 0:window.wpApiShare)==null?void 0:s.template_dir,{metric:t,missingIcon:i=`${e}/dt-assets/images/groups/missing.svg`}=this;if(t["font-icon"]){const a=t["font-icon"].replace("mdi mdi-","mdi:");return u`<dt-icon icon="${a}" size="unset"></dt-icon>`}return u`<img
      src="${t.icon?t.icon:i}"
      alt="${t}"
    />`}render(){const{metric:e,active:t,disabled:i}=this;return u`<div
      class=${T({"health-item":!0,"health-item--active":t,"health-item--disabled":i})}
      title="${e.description}"
      @click="${this._handleClick}"
    >
      ${this.renderIcon()}
    </div>`}async _handleClick(e){if(this.disabled)return;const t=!this.active;this.active=t;const i=new CustomEvent("change",{detail:{key:this.key,active:t}});this.dispatchEvent(i)}}window.customElements.define("dt-church-health-icon",Ra);class cs extends D{static get styles(){return[...super.styles,x`
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
          min-height: var(--dt-form-input-height, 2.5rem);
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
          border: 0.1em solid rgb(0 0 0 / 0.2);
          position: relative;
          border-radius: 100vw;
          background-color: var(
            --dt-toggle-background-color-off,
            var(--gray-2)
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
          background-color: white;
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
          background-color: var(--primary-color);
        }

        .toggle[aria-pressed='true'] .toggle-display::before,
        .toggle-input:checked + .toggle-display::before {
          transform: translate(100%, -50%);
        }

        .toggle[disabled] .toggle-display,
        .toggle-input:disabled + .toggle-display {
          opacity: 0.6;
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
          color: var(--alert-color);
          font-size: 0.55em;
        }

        .toggle-icon--checkmark {
          font-size: 0.65em;
          color: var(--success-color);
        }
      `]}static get properties(){return{...super.properties,id:{type:String},checked:{type:Boolean,reflect:!0},icons:{type:Boolean,default:!1}}}constructor(){super(),this.icons=!1}firstUpdated(){this.checked===void 0&&(this.checked=!1);const e=this.checked?"1":"0";this._setFormValue(e),this.value=this.checked}onChange(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.checked,newValue:e.target.checked}});this.checked=e.target.checked,this.value=e.target.checked,this._setFormValue(this.checked?"1":"0"),this.dispatchEvent(t)}onClickToggle(e){e.preventDefault(),e.target.closest("label").querySelector("input").click()}render(){const e=u`<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="toggle-icon toggle-icon--checkmark"><path d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z" fill="currentcolor" stroke="currentcolor" /></svg>`,t=u`<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="toggle-icon toggle-icon--cross"><path d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z" fill="currentcolor" /></svg>`;return u`
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
              ${this.icons?u` ${e} ${t} `:u``}
            </span>
          </label>
          ${this.renderIcons()}
        </div>
      </div>
    `}}window.customElements.define("dt-toggle",cs);class us extends Ze{static get styles(){return[...super.styles,x`
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
      `]}static get properties(){const e={...super.properties,settings:{type:Object,reflect:!1},missingIcon:{type:String}};return delete e.placeholder,delete e.containerHeight,e}_filterOptions(){const e=this.options||[];if(!Object.values(e).length)return[];const t=Object.entries(e);return this.filteredOptions=t.filter(([i,s])=>i!=="church_commitment"),this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e){const t=e.has("value"),i=e.has("options");(t||i)&&this._filterOptions()}}get isCommited(){return this.value?this.value.includes("church_commitment"):!1}render(){return u`
      <div class="health-circle__wrapper input-group">
        <div
          class="health-circle__container"
          style="--icon-count: ${this.filteredOptions.length}"
        >
          <div
            class=${T({"health-circle":!0,"health-circle--committed":this.isCommited,"health-circle--disabled":this.disabled})}
          >
            <div class="health-circle__grid">
              ${this.filteredOptions.map(([e,t],i)=>u`<dt-church-health-icon
                    key="${e}"
                    .metric=${t}
                    .active=${(this.value||[]).indexOf(e)!==-1}
                    .style="--i: ${i+1}"
                    .missingIcon="${this.missingIcon}"
                    ?disabled=${this.disabled}
                    data-value="${e}"
                    @change="${this.handleIconClick}"
                  >
                  </dt-church-health-icon>`)}
            </div>
            ${this.renderIconLoading()} ${this.renderIconSaved()}
          </div>
        </div>

        <dt-toggle
          name="church_commitment"
          label="${this.options.church_commitment.label}"
          @change="${this.handleToggleChange}"
          ?disabled=${this.disabled}
          ?checked=${this.isCommited}
          data-value="church_commitment"
        >
        </dt-toggle>
        ${this.renderError()}
      </div>
    `}handleIconClick(e){const{key:t,active:i}=e.detail;i?this._select(t):this._remove(e)}async handleToggleChange(e){const{field:t,newValue:i}=e.detail;i?this._select(t):this._remove(e)}}window.customElements.define("dt-church-health-circle",us);class Le extends Ze{static get properties(){return{...super.properties,allowAdd:{type:Boolean}}}static get styles(){return[...super.styles,x`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(--primary-color, #3f729b);
        }
        .selected-option a[href="#"],
        .selected-option a[href=""] {
          color: var(--dt-multi-select-text-color, #0a0a0a);
          pointer-events: none;
        }
        .invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
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
      `]}_addRecord(){const e=new CustomEvent("dt:add-new",{detail:{field:this.name,value:this.query}});this.dispatchEvent(e)}willUpdate(e){super.willUpdate(e),e&&e.has("open")&&this.open&&(!this.filteredOptions||!this.filteredOptions.length)&&this._filterOptions()}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.startsWith("-"));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1;let n=a;n.length&&typeof n[0]=="string"&&(n=n.map(r=>({id:r}))),i.allOptions=n,i.filteredOptions=n.filter(r=>!e.includes(r.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_renderOption(e,t){return u`
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
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||typeof t=="string"&&t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){const e=this.options||this.allOptions;return(this.value||[]).filter(t=>!t.startsWith("-")).map(t=>{var a;let i=t;if(e){const n=e.filter(r=>r===t||r.id===t);n.length&&(i=n[0].label||n[0].id||t)}let s;if(!s&&((a=window==null?void 0:window.SHAREDFUNCTIONS)!=null&&a.createCustomFilter)){const n=window.SHAREDFUNCTIONS.createCustomFilter(this.name,[t]),r=this.label||this.name,l=[{id:`${this.name}_${t}`,name:`${r}: ${t}`}];s=window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType,n,l)}return u`
          <div
            class="selected-option"
            @click="${this._handleItemClick}"
            @keydown="${this._handleItemClick}"
          >
            <a href="${s}" ?disabled="${this.disabled}" alt="${t}"
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
        `})}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return u`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}"
        @click="${this._handleDivClick}"
        @keydown="${this._handleDivClick}"
      >
        <div
          class="${T(this.classes)}"
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
        ${this.allowAdd?u`<button class="input-addon btn-add" @click=${this._addRecord}>
              <dt-icon icon="mdi:tag-plus-outline"></dt-icon>
            </button>`:null}
        <ul class="option-list" style=${Q(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-tags",Le);class hs extends Le{static get styles(){return[...super.styles,x`
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
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((s,a)=>!s&&a.id==t?a:s,null);i&&this._select(i)}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.stopPropagation(),e.target&&e.target.dataset&&e.target.dataset.value){let t=e.target.dataset.value;const i=Number.parseInt(t);Number.isNaN(i)||(t=i);const s=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(a=>{const n={...a};return a.id===t&&(n.delete=!0),n}),s.detail.newValue=this.value,this.dispatchEvent(s),this.open&&this.shadowRoot.querySelector("input").focus(),this._validateRequired()}document.activeElement.blur()}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id);if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.delete))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>u`
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
        `)}_renderOption(e,t){const i=u`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`,s=e.status||{label:"",color:""};return u`
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
          ${s.label?u`<span class="status">${s.label}</span>`:null}
          ${e.user?i:null}
        </button>
      </li>
    `}render(){const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return u`
      ${this.labelTemplate()}

      <div
        class="input-group ${this.disabled?"disabled":""} ${this.allowAdd?"allowAdd":""}"
        @click="${this._handleDivClick}"
        @keydown="${this._handleDivClick}"
      >
        <div
          class="${T(this.classes)}"
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
        ${this.allowAdd?u`<button class="input-addon btn-add" @click=${this._addRecord}>
              <dt-icon icon="mdi:account-plus-outline"></dt-icon>
            </button>`:null}
        <ul class="option-list" style=${Q(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-connection",hs);class ps extends Le{static get styles(){return[...super.styles,x`
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
      `]}static get properties(){return{...super.properties,single:{type:Boolean}}}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length&&!this.single){let i=!1;const s=this.value.map(a=>{const n={...a};return a.id===e.id&&a.delete?(delete n.delete,i=!0):this.single&&!a.delete&&(n.delete=!0),n});i||s.push(e),this.value=s}else this.value=[e];t.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.canUpdate=!0,this.dispatchEvent(t),this._clearSearch()}_clickOption(e){if(e.target&&e.target.value){const t=parseInt(e.target.value,10),i=this.filteredOptions.reduce((s,a)=>!s&&a.id==t?a:s,null);i&&this._select(i),this.query=""}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="",this.query="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]),this.query="")}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,remove:!0}});this.value=(this.value||[]).map(i=>{const s={...i};return i.id.toString()===e.target.dataset.value&&(s.delete=!0),s}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>Number(i==null?void 0:i.id));if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:this.query,onSuccess:a=>{i.loading=!1,i.filteredOptions=a.filter(n=>!e.includes(n.id))},onError:a=>{console.warn(a),i.loading=!1,this.canUpdate=!1}}});this.dispatchEvent(s)}return this.filteredOptions}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>u`
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
        `)}_renderOption(e,t){return u`
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
    `}}window.customElements.define("dt-users-connection",ps);class fs extends N{static get styles(){return x`
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
    `}static get properties(){return{value:{type:String},success:{type:Boolean},error:{type:Boolean}}}get inputStyles(){return this.success?{"--dt-text-border-color":"var(--copy-text-success-color, var(--success-color))","--dt-form-text-color":"var( --copy-text-success-color, var(--success-color))",color:"var( --copy-text-success-color, var(--success-color))"}:this.error?{"---dt-text-border-color":"var(--copy-text-alert-color, var(--alert-color))","--dt-form-text-color":"var(--copy-text-alert-color, var(--alert-color))"}:{}}get icon(){return this.success?"ic:round-check":"ic:round-content-copy"}async copy(){try{this.success=!1,this.error=!1,await navigator.clipboard.writeText(this.value),this.success=!0,this.error=!1}catch(e){console.log(e),this.success=!1,this.error=!0}}render(){return u`
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
    `}}window.customElements.define("dt-copy-text",fs);class Dt extends D{static get styles(){return[...super.styles,x`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-form-background-color, #cecece);
          border: 1px solid var(--dt-form-border-color, #cacaca);
          border-radius: var(--dt-date-border-radius, 0);
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
          border: solid 1px gray;
          border-collapse: collapse;
          background-color: var(
            --dt-date-background-color,
            var(--dt-form-background-color, buttonface)
          );
          border: 1px solid
            var(--dt-date-border-color, var(--dt-form-border-color, #fefefe));
          border-radius: var(--dt-date-border-radius, 0);
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
      `]}static get properties(){return{...super.properties,value:{type:String,reflect:!0},timestamp:{converter:e=>{let t=Number(e);if(t<1e12&&(t*=1e3),t)return t},reflect:!0}}}updateTimestamp(e){const t=new Date(e).getTime(),i=t/1e3,s=new CustomEvent("change",{detail:{field:this.name,oldValue:this.timestamp,newValue:i}});this.timestamp=t,this.value=e,this._setFormValue(e),this.dispatchEvent(s)}_change(e){this.updateTimestamp(e.target.value)}clearInput(){this.updateTimestamp("")}showDatePicker(){this.shadowRoot.querySelector("input").showPicker()}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}get fieldContainerClasses(){return{"field-container":!0,invalid:this.touched&&this.invalid}}render(){return this.timestamp?this.value=new Date(this.timestamp).toISOString().substring(0,10):this.value&&(this.timestamp=new Date(this.value).getTime()),u`
      ${this.labelTemplate()}

      <div class="input-group">
        <div class="${T(this.fieldContainerClasses)}">
          <input
            id="${this.id}"
            class="${T(this.classes)}"
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
    `}reset(){this.updateTimestamp(""),super.reset()}}window.customElements.define("dt-date",Dt);class bs extends Dt{static get styles(){return[...super.styles,x`
        input[type='datetime-local'] {
          max-width: calc(100% - 22px - 1rem);
        }
      `]}static get properties(){return{...super.properties,tzoffset:{type:Number}}}constructor(){super(),this.tzoffset=new Date().getTimezoneOffset()*6e4}render(){return this.timestamp?this.value=new Date(this.timestamp-this.tzoffset).toISOString().substring(0,16):this.value&&(this.timestamp=new Date(this.value).getTime()),u`
      ${this.labelTemplate()}

      <div class="input-group">
        <div class="${T(this.fieldContainerClasses)}">
          <input
            id="${this.id}"
            class="${T(this.classes)}"
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
          >
            <dt-icon icon="mdi:close"></dt-icon>
          </button>
        </div>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-datetime",bs);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*Je(o,e){if(o!==void 0){let t=0;for(const i of o)yield e(i,t++)}}class gs extends Le{static get properties(){return{...super.properties,filters:{type:Array}}}static get styles(){return[...super.styles,x`
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
      `]}_clickOption(e){if(e.target&&e.target.value){const t=e.target.value,i=this.filteredOptions.reduce((s,a)=>!s&&a.id===t?a:s,null);this._select(i)}}_clickAddNew(e){var t,i;if(e.target){this._select({id:(t=e.target.dataset)==null?void 0:t.label,label:(i=e.target.dataset)==null?void 0:i.label,isNew:!0});const s=this.shadowRoot.querySelector("input");s&&(s.value="")}}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex+1>this.filteredOptions.length?this._select({id:this.query,label:this.query,isNew:!0}):this._select(this.filteredOptions[this.activeIndex]))}_remove(e){if(e.target&&e.target.dataset&&e.target.dataset.value){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map(i=>{const s={...i};return i.id.toString()===e.target.dataset.value&&(s.delete=!0),s}),t.detail.newValue=this.value,this.dispatchEvent(t),this.open&&this.shadowRoot.querySelector("input").focus()}}updated(){super.updated();const e=this.shadowRoot.querySelector(".input-group"),t=e.style.getPropertyValue("--select-width"),i=this.shadowRoot.querySelector("select");!t&&(i==null?void 0:i.clientWidth)>0&&e.style.setProperty("--select-width",`${i.clientWidth}px`)}_filterOptions(){var t;const e=(this.value||[]).filter(i=>!i.delete).map(i=>i==null?void 0:i.id.toString());if((t=this.options)!=null&&t.length)this.filteredOptions=(this.options||[]).filter(i=>!e.includes(i.id)&&(!this.query||i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())));else if(this.open||this.canUpdate){this.loading=!0,this.filteredOptions=[];const i=this,s=this.shadowRoot.querySelector("select"),a=new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,query:this.query,filter:s==null?void 0:s.value,onSuccess:n=>{i.loading=!1,i.filteredOptions=n.filter(r=>!e.includes(r.id))},onError:n=>{console.warn(n),i.loading=!1}}});this.dispatchEvent(a)}return this.filteredOptions}_renderOption(e,t){return u`
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
    `}_renderSelectedOptions(){return(this.value||[]).filter(e=>!e.delete).map(e=>u`
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
        `)}render(){const e={display:this.open?"block":"none",top:`${this.containerHeight}px`};return u`
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
          ${Je(this.filters,t=>u`<option value="${t.id}">${t.label}</option>`)}
        </select>
        <ul class="option-list" style=${Q(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.renderIconInvalid()} ${this.renderError()}
      </div>
    `}}window.customElements.define("dt-location",gs);class Na{constructor(e){this.token=e}async searchPlaces(e,t="en"){const i=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:t}),s={method:"GET",headers:{"Content-Type":"application/json"}},a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)}.json?${i}`,r=await(await fetch(a,s)).json();return r==null?void 0:r.features}async reverseGeocode(e,t,i="en"){const s=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:i}),a={method:"GET",headers:{"Content-Type":"application/json"}},n=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(e)},${encodeURI(t)}.json?${s}`,l=await(await fetch(n,a)).json();return l==null?void 0:l.features}}class qa{constructor(e,t,i){var s,a,n;if(this.token=e,this.window=t,!((n=(a=(s=t.google)==null?void 0:s.maps)==null?void 0:a.places)!=null&&n.AutocompleteService)){const r=i.createElement("script");r.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${e}`,i.body.appendChild(r)}}async getPlacePredictions(e,t="en"){try{return await this._getPlacePredictionsLegacy(e,t)}catch(i){const s=await this._getPlaceSuggestionsRest(e,t);if(s)return s;throw{message:i}}}async _getPlacePredictionsLegacy(e,t="en"){return this.window.google?new Promise((i,s)=>{const a=new this.window.google.maps.places.AutocompleteService;window.gm_authFailure=function(){s("Google Maps API Key authentication failed")},a.getPlacePredictions({input:e,language:t},(n,r)=>{r!=="OK"?s(r):i(n)})}):null}async _getPlaceSuggestionsRest(e,t="en"){const i="https://places.googleapis.com/v1/places:autocomplete?key="+encodeURIComponent(this.token),a=await fetch(i,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({input:e})}),n=await a.json();if(!a.ok&&n.error)throw n.error;return(Array.isArray(n&&n.suggestions)?n.suggestions:[]).map(h=>h&&h.placePrediction?h.placePrediction:null).filter(Boolean).map(h=>{const g=h.placeId||(h.place?String(h.place).replace("places/",""):null),m=h.text&&h.text.text||[h.structuredFormat&&h.structuredFormat.mainText&&h.structuredFormat.mainText.text,h.structuredFormat&&h.structuredFormat.secondaryText&&h.structuredFormat.secondaryText.text].filter(Boolean).join(", ");return g&&m?{description:m,place_id:g}:null}).filter(Boolean)}async getPlaceDetails(e,t="en"){let i=null;if(this.window.google){const s=new window.google.maps.Geocoder;try{const{results:a}=await s.geocode({placeId:e.place_id,language:t}),n=a[0];i={lng:n.geometry.location.lng(),lat:n.geometry.location.lat(),level:this.convert_level(n.types[0]),label:e.description||n.formatted_address}}catch(a){i={error:a}}}return i}async reverseGeocode(e,t,i="en"){const a=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${t},${e}`,language:i,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,r=await(await fetch(a,{method:"GET"})).json();return r==null?void 0:r.results}convert_level(e){switch(e){case"administrative_area_level_0":e="admin0";break;case"administrative_area_level_1":e="admin1";break;case"administrative_area_level_2":e="admin2";break;case"administrative_area_level_3":e="admin3";break;case"administrative_area_level_4":e="admin4";break;case"administrative_area_level_5":e="admin5";break}return e}}class ms extends N{static get styles(){return x`
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
    `}static get properties(){return{title:{type:String},context:{type:String},isHelp:{type:Boolean},isOpen:{type:Boolean},hideHeader:{type:Boolean},hideButton:{type:Boolean},buttonClass:{type:Object},buttonStyle:{type:Object},headerClass:{type:Object},imageSrc:{type:String},imageStyle:{type:Object},tileLabel:{type:String},buttonLabel:{type:String},dropdownListImg:{type:String},submitButton:{type:Boolean},closeButton:{type:Boolean},bottom:{type:Boolean}}}constructor(){super(),this.context="default",this.addEventListener("open",()=>this._openModal()),this.addEventListener("close",()=>this._closeModal())}_openModal(){this.isOpen=!0,this.shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}get formattedTitle(){if(!this.title)return"";const e=this.title.replace(/_/g," ");return e.charAt(0).toUpperCase()+e.slice(1)}_dialogHeader(e){return this.hideHeader?u``:u`
      <header>
            <h1 id="modal-field-title" class="modal-header">${this.formattedTitle}</h1>
            <button @click="${this._cancelModal}" class="toggle">${e}</button>
          </header>
      `}_closeModal(){this.isOpen=!1,this.shadowRoot.querySelector("dialog").close(),document.querySelector("body").style.overflow="initial"}_cancelModal(){this._triggerClose("cancel")}_triggerClose(e){this.dispatchEvent(new CustomEvent("close",{detail:{action:e}}))}_dialogClick(e){if(e.target.tagName!=="DIALOG")return;const t=e.target.getBoundingClientRect();(t.top<=e.clientY&&e.clientY<=t.top+t.height&&t.left<=e.clientX&&e.clientX<=t.left+t.width)===!1&&this._cancelModal()}_dialogKeypress(e){e.key==="Escape"&&this._cancelModal()}_helpMore(){return this.isHelp?u`
          <div class="help-more">
            <h5>${I("Need more help?")}</h5>
            <a
              class="button small"
              id="docslink"
              href="https://disciple.tools/user-docs"
              target="_blank"
              >${I("Read the documentation")}</a
            >
          </div>
        `:null}firstUpdated(){this.isOpen&&this._openModal()}_onButtonClick(){this._triggerClose("button")}get classes(){return{...this.headerClass,"no-header":this.hideHeader,bottom:this.bottom}}render(){const e=u`
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
    `;return u`
      <dialog
        id=""
        class="dt-modal dt-modal--width ${T(this.classes)}"
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
          ${this.closeButton?u`
            <button
              class="button small"
              data-close=""
              aria-label="Close reveal"
              type="button"
              @click=${this._onButtonClick}
            >
              <slot name="close-button">${I("Close")}</slot>
              </button>

            `:""}
              ${this.submitButton?u`
                <slot name="submit-button"></span>

                `:""}
              </div>
            ${this._helpMore()}
          </footer>
        </form>
      </dialog>

      ${this.hideButton?null:u`
      <button
        class="button small opener ${T(this.buttonClass||{})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${Q(this.buttonStyle||{})}
      >
      ${this.dropdownListImg?u`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">`:""}
      ${this.imageSrc?u`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${Q(this.imageStyle||{})}
                 />`:""}
      ${this.buttonLabel?u`${this.buttonLabel}`:""}
      </button>
      `}
    `}}window.customElements.define("dt-modal",ms);class vs extends N{static get properties(){return{...super.properties,title:{type:String},isOpen:{type:Boolean},canEdit:{type:Boolean,state:!0},metadata:{type:Object},center:{type:Array},mapboxToken:{type:String,attribute:"mapbox-token"}}}static get styles(){return[x`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `]}constructor(){super(),this.addEventListener("open",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")),this.isOpen=!0}),this.addEventListener("close",e=>{this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")),this.isOpen=!1})}connectedCallback(){if(super.connectedCallback(),this.canEdit=!this.metadata,window.mapboxgl)this.initMap();else{const e=document.createElement("script");e.src="https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js",e.onload=this.initMap.bind(this),document.body.appendChild(e)}}initMap(){if(!this.isOpen||!window.mapboxgl||!this.mapboxToken)return;const e=this.shadowRoot.querySelector("#map");if(e&&!this.map){this.map=new window.mapboxgl.Map({accessToken:this.mapboxToken,container:e,style:"mapbox://styles/mapbox/streets-v12",minZoom:1}),this.map.on("load",()=>this.map.resize()),this.center&&this.center.length&&(this.map.setCenter(this.center),this.map.setZoom(15));const t=new mapboxgl.NavigationControl;this.map.addControl(t,"bottom-right"),this.addPinFromMetadata(),this.map.on("click",i=>{this.canEdit&&(this.marker?this.marker.setLngLat(i.lngLat):this.marker=new mapboxgl.Marker().setLngLat(i.lngLat).addTo(this.map))})}}addPinFromMetadata(){if(this.metadata){const{lng:e,lat:t,level:i}=this.metadata;let s=15;i==="admin0"?s=3:i==="admin1"?s=6:i==="admin2"&&(s=10),this.map&&(this.map.setCenter([e,t]),this.map.setZoom(s),this.marker=new mapboxgl.Marker().setLngLat([e,t]).addTo(this.map))}}updated(e){window.mapboxgl&&(e.has("metadata")&&this.metadata&&this.metadata.lat&&this.addPinFromMetadata(),e.has("isOpen")&&this.isOpen&&this.initMap())}onClose(e){var t;((t=e==null?void 0:e.detail)==null?void 0:t.action)==="button"&&this.marker&&this.dispatchEvent(new CustomEvent("submit",{detail:{location:this.marker.getLngLat()}}))}render(){var e;return u`
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

        ${this.canEdit?u`<div slot="close-button">${I("Save")}</div>`:null}
      </dt-modal>

      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `}}window.customElements.define("dt-map-modal",vs);class Ua extends N{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},invalid:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[x`
        :host {
          --dt-location-map-border-color: var(--dt-form-border-color, #fefefe);
          position: relative;
          font-family: Helvetica, Arial, sans-serif;
          display: block;
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
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
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
            var(--dt-text-border-color-alert, var(--alert-color));
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
      `]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",async()=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()}),this.mapboxToken&&(this.mapboxService=new Na(this.mapboxToken))}firstUpdated(){var e;this.googleToken&&!((e=this.metadata)!=null&&e.lat)&&(this.googleGeocodeService=new qa(this.googleToken,window,document))}disconnectedCallback(){super.disconnectedCallback(),this.removeEventListener("autofocus",this.handleAutofocus)}updated(){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const e=this.shadowRoot.querySelector(".option-list"),t=this.shadowRoot.querySelector("button.active");if(e&&t){const i=t.offsetTop,s=t.offsetTop+t.clientHeight,a=e.scrollTop,n=e.scrollTop+e.clientHeight;s>n?e.scrollTo({top:s-e.clientHeight,behavior:"smooth"}):i<a&&e.scrollTo({top:i,behavior:"smooth"})}}_clickOption(e){var i;const t=e.currentTarget??e.target;if(t&&t.value){const s=JSON.parse(t.value);this._select({...s,key:(i=this.metadata)==null?void 0:i.key})}}_touchStart(e){e.target&&(this.detectTap=!1)}_touchMove(e){e.target&&(this.detectTap=!0)}_touchEnd(e){this.detectTap||(e.target&&e.target.value&&this._clickOption(e),this.detectTap=!1)}_keyboardSelectOption(){var e;this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query,key:(e=this.metadata)==null?void 0:e.key}))}async _select(e){if(e.place_id&&this.googleGeocodeService){this.saved=!1,this.loading=!0;const s=await this.googleGeocodeService.getPlaceDetails(e,this.locale);if(this.loading=!1,s){if(s.error){console.error(s.error),this.error=s.error.message;return}e.lat=s.lat,e.lng=s.lng,e.level=s.level}}const t={detail:{metadata:e},bubbles:!1};this.dispatchEvent(new CustomEvent("select",t)),this.metadata=e;const i=this.shadowRoot.querySelector("input");i&&(i.value=e==null?void 0:e.label),this.open=!1,this.activeIndex=-1}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(e){(!e.relatedTarget||!["BUTTON","LI"].includes(e.relatedTarget.nodeName))&&(this.open=!1)}_inputKeyDown(e){switch(e.keyCode||e.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:e.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0;break}}_inputKeyUp(e){const t=e.keyCode||e.which,i=[9,13];e.target.value&&!i.includes(t)&&(this.open=!0),this.query=e.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query){if(this.googleToken&&this.googleGeocodeService){this.saved=!1,this.loading=!0;try{const e=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(e||[]).map(t=>({label:t.description,place_id:t.place_id,source:"user",raw:t})),this.loading=!1}catch(e){console.error(e),this.error=e.message||"An error occurred while searching for locations.",this.loading=!1;return}}else if(this.mapboxToken&&this.mapboxService){this.saved=!1,this.loading=!0;const e=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=e.map(t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"})),this.loading=!1}}return this.filteredOptions}willUpdate(e){if(super.willUpdate(e),e&&(e.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout(()=>this._filterOptions(),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length)){const i=this.shadowRoot.querySelector(".input-group");i&&(this.containerHeight=i.offsetHeight)}}_change(){}_delete(){const e={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",e))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(e){var t,i;if((i=(t=e==null?void 0:e.detail)==null?void 0:t.location)!=null&&i.lat){const{location:s}=e==null?void 0:e.detail,{lat:a,lng:n}=s;if(this.googleGeocodeService){const r=await this.googleGeocodeService.reverseGeocode(n,a,this.locale);if(r&&r.length){const l=r[0];this._select({lng:l.geometry.location.lng,lat:l.geometry.location.lat,level:l.types&&l.types.length?l.types[0]:null,label:l.formatted_address,source:"user"})}}else if(this.mapboxService){const r=await this.mapboxService.reverseGeocode(n,a,this.locale);if(r&&r.length){const l=r[0];this._select({lng:l.center[0],lat:l.center[1],level:l.place_type[0],label:l.place_name,source:"user"})}}}}_renderOption(e,t,i){return u`
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
    `}_renderOptions(){const e=[];return this.filteredOptions.length?e.push(...this.filteredOptions.map((t,i)=>this._renderOption(t,i))):this.loading?e.push(u`<li><div>${I("Loading...")}</div></li>`):e.push(u`<li><div>${I("No Data Available")}</div></li>`),e.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,u`<strong>${I("Use")}: "${this.query}"</strong>`)),e}get classes(){return{"field-container":!0,invalid:this.invalid}}render(){var s,a,n,r;const e={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},t=!!((s=this.metadata)!=null&&s.label),i=((a=this.metadata)==null?void 0:a.lat)&&((n=this.metadata)==null?void 0:n.lng);return u`
      <div class="input-group">
        <div class="${T(this.classes)}">
          <input
            type="text"
            class="${this.disabled?"disabled":null}"
            placeholder="${this.placeholder}"
            .value="${((r=this.metadata)==null?void 0:r.label)??""}"
            .disabled=${t&&i||this.disabled}
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
          />

          ${t&&i?u`
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
          ${t?u`
                <button
                  class="input-addon btn-delete"
                  @click=${this._delete}
                  ?disabled=${this.disabled}
                >
                  <slot name="delete-icon"
                    ><dt-icon icon="mdi:trash-can-outline"></dt-icon
                  ></slot>
                </button>
              `:u`
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
        <ul class="option-list" style=${Q(e)}>
          ${this._renderOptions()}
        </ul>
        ${this.loading?u`<dt-spinner
              class="icon-overlay ${i?"selected":""}"
            ></dt-spinner>`:null}
        ${this.renderIconSaved(i)}
      </div>

      <dt-map-modal
        .metadata=${this.metadata}
        mapbox-token="${this.mapboxToken}"
        @submit=${this._onMapModalSubmit}
      ></dt-map-modal>
    `}renderIconSaved(e){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?u`<dt-checkmark
              class="icon-overlay success fade-out ${e?"selected":""}"
            ></dt-checkmark>`:null}}window.customElements.define("dt-location-map-item",Ua);class ys extends D{static get properties(){return{...super.properties,placeholder:{type:String},value:{type:Array,reflect:!0},locations:{type:Array,state:!0},open:{type:Boolean,state:!0},limit:{type:Number,attribute:"limit"},onchange:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},activeItem:{type:String,state:!0}}}static get styles(){return[...super.styles,x`
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
      `]}constructor(){super(),this.limit=0,this.value=[],this.locations=[{id:Date.now()}]}_setFormValue(e){super._setFormValue(e),this.internals.setFormValue(JSON.stringify(e))}willUpdate(...e){super.willUpdate(...e),this.value&&this.value.filter(t=>!t.id)&&(this.value=[...this.value.map(t=>({...t,id:t.id||t.grid_meta_id}))]),this.updateLocationList()}firstUpdated(...e){super.firstUpdated(...e),this.internals.setFormValue(JSON.stringify(this.value))}updated(e){var t,i;if(e.has("value")){const s=e.get("value");s&&(s==null?void 0:s.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewLocation()}if(e.has("locations")){const s=e.get("locations");s&&(s==null?void 0:s.length)!==((i=this.locations)==null?void 0:i.length)&&this.focusNewLocation()}}focusNewLocation(){const e=this.shadowRoot.querySelectorAll("dt-location-map-item");e&&e.length&&e[e.length-1].dispatchEvent(new Event("autofocus"))}updateLocationList(){if(!this.disabled&&(this.open||!this.value||!this.value.length)){this.open=!0;const e=(this.value||[]).filter(i=>i.label),t=this.limit===0||e.length<this.limit;this.locations=[...e,...t?[{id:Date.now()}]:[]]}else this.locations=[...(this.value||[]).filter(e=>e.label)]}selectLocation(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i={...e.detail.metadata,id:Date.now()};if(i.lat){const s=Math.round(i.lat*1e7)/1e7,a=Math.round(i.lng*10**7)/10**7;this.activeItem=`${s}/${a}`}else this.activeItem=i.label;this.value=[...(this.value||[]).filter(s=>s.label&&(!s.key||s.key!==i.key)&&(!s.id||s.id!==i.id)),i],this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}deleteItem(e){var a;this.activeItem=void 0;const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}}),i=(a=e.detail)==null?void 0:a.metadata,s=i==null?void 0:i.grid_meta_id;s?this.value=(this.value||[]).filter(n=>n.grid_meta_id!==s):i.lat&&i.lng?this.value=(this.value||[]).filter(n=>n.lat!==i.lat&&n.lng!==i.lng):this.value=(this.value||[]).filter(n=>(!n.key||n.key!==i.key)&&(!n.id||n.id!==i.id)),this.updateLocationList(),t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}addNew(){const e=(this.value||[]).filter(t=>t.label);(this.limit===0||e.length<this.limit)&&(this.open=!0,this.updateLocationList())}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t.value))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required")):(this.invalid=!1,this.internals.setValidity({}))}labelTemplate(){return this.label?u`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon?null:u`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
        ${!this.open&&(this.limit==0||this.locations.length<this.limit)?u`
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
    `:""}renderItem(e,t){const i=Math.round(e.lat*1e7)/1e7,s=Math.round(e.lng*10**7)/10**7,a=`${i}/${s}`,n=this.activeItem&&(this.activeItem===e.label||this.activeItem===a)||t===0&&!this.activeItem;return u`
      <dt-location-map-item
        placeholder="${this.placeholder}"
        .metadata=${e}
        mapbox-token="${this.mapboxToken}"
        google-token="${this.googleToken}"
        @delete=${this.deleteItem}
        @select=${this.selectLocation}
        ?disabled=${this.disabled}
        ?invalid=${this.invalid&&this.touched}
        ?loading=${n?this.loading:!1}
        ?saved=${n?this.saved:!1}
      ></dt-location-map-item>
    `}render(){return[...this.value||[]],u`
      ${this.labelTemplate()}
      <div class="input-group">
        ${ce(this.locations||[],e=>e.id,(e,t)=>this.renderItem(e,t))}
        ${this.renderError()} ${this.renderIconInvalid()}
      </div>
    `}}window.customElements.define("dt-location-map",ys);class ws extends D{static get styles(){return[...super.styles,x`
        input {
          color: var(--dt-form-text-color, #000);
          appearance: none;
          background-color: var(--dt-form-background-color, #fff);
          border: 1px solid var(--dt-form-border-color, #ccc);
          border-radius: 0;
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
          border-color: var(--dt-form-invalid-border-color, #dc3545);
        }

        .icon-overlay {
          inset-inline-end: 2rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:String,reflect:!0},min:{type:Number},max:{type:Number}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_validateValue(e){return!(e<this.min||e>this.max)}async _change(e){if(this._validateValue(e.target.value)){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value},bubbles:!0,composed:!0});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}else e.currentTarget.value="",this.value=void 0}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const i=this.internals.form.querySelector("button[type=submit]");i&&i.click()}}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return u`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          type="number"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${T(this.classes)}"
          .value="${this.value}"
          min="${B(this.min)}"
          max="${B(this.max)}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
          part="input"
        />

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-number",ws);class _s extends D{static get styles(){return[...super.styles,x`
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
      `]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},value:{type:String,reflect:!0},color:{type:String,state:!0},onchange:{type:String}}}updateColor(){if(this.value&&this.options){const e=this.options.filter(t=>t.id===this.value);e&&e.length&&(this.color=e[0].color)}}isColorSelect(){return(this.options||[]).reduce((e,t)=>e||t.color,!1)}willUpdate(e){super.willUpdate(e),e.has("value")&&this.updateColor()}_change(e){const t=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{invalid:this.touched&&this.invalid,"color-select":this.isColorSelect()}}render(){return u`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled?"disabled":""}" dir="${this.RTL?"rtl":"ltr"}">
        <select
          name="${this.name}"
          aria-label="${this.name}"
          @change="${this._change}"
          class="${T(this.classes)}"
          style="background-color: ${this.color};"
          ?disabled="${this.disabled}"
          ?required=${this.required}
          part="select"
        >
          <option disabled selected hidden value="">${this.placeholder}</option>

          ${this.options&&this.options.map(e=>u`
              <option value="${e.id}" ?selected="${e.id===this.value}">
                ${e.label}
              </option>
            `)}
        </select>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-single-select",_s);class Ft extends D{static get styles(){return[...super.styles,x`
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
      `]}static get properties(){return{...super.properties,type:{type:String},placeholder:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}implicitFormSubmit(e){if((e.keyCode||e.which)===13&&this.internals.form){const i=this.internals.form.querySelector("button[type=submit]");i&&i.click()}}_validateRequired(){const{value:e}=this;this.required&&!e?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid,disabled:this.disabled}}render(){return u`
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
          class="${T(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
          part="input"
        />

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-text",Ft);class $s extends D{static get styles(){return[...super.styles,x`
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

        textarea.invalid {
          border-color: var(--dt-text-border-color-alert, var(--alert-color));
        }
      `]}static get properties(){return{...super.properties,id:{type:String},value:{type:String,reflect:!0}}}_input(e){this.value=e.target.value,this._setFormValue(this.value)}_change(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:e.target.value}});this.value=e.target.value,this._setFormValue(this.value),this.dispatchEvent(t)}_validateRequired(){const{value:e}=this;!e&&this.required?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return u`
      ${this.labelTemplate()}

      <div class="input-group">
        <textarea
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${T(this.classes)}"
          .value="${this.value||""}"
          @change=${this._change}
          @input=${this._input}
          part="textarea"
        ></textarea>

        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-textarea",$s);/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function Y(o,e,t){return o?e(o):t==null?void 0:t(o)}class xs extends Ft{static get styles(){return[...super.styles,x`
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
          height: 100%;
        }
        .field-container:has(.btn-remove) ~ .icon-overlay {
          inset-inline-end: 5.5rem;
        }
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0}}}updated(e){var t;if(e.has("value")){const i=e.get("value");i&&(i==null?void 0:i.length)!==((t=this.value)==null?void 0:t.length)&&this.focusNewItem()}}focusNewItem(){const e=this.shadowRoot.querySelectorAll("input");e&&e.length&&e[e.length-1].focus()}_addItem(){const e={verified:!1,value:"",tempKey:Date.now().toString()};this.value=[...this.value,e]}_removeItem(e){const t=e.currentTarget.dataset.key;if(t){const i=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}}),s=this.value.filter(a=>a.tempKey!==t).map(a=>{const n={...a};return a.key===t&&(n.delete=!0),n});s.filter(a=>!a.delete).length||s.push({value:"",tempKey:Date.now().toString()}),this.value=s,i.detail.newValue=this.value,this.dispatchEvent(i),this._setFormValue(this.value)}}_change(e){var i,s;const t=(s=(i=e==null?void 0:e.currentTarget)==null?void 0:i.dataset)==null?void 0:s.key;if(t){const a=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=this.value.map(n=>{var r;return{...n,value:n.key===t||n.tempKey===t?(r=e.target)==null?void 0:r.value:n.value}}),a.detail.newValue=this.value,this._setFormValue(this.value),this.dispatchEvent(a)}}_inputFieldTemplate(e,t){return u`
      <div class="field-container">
        <input
          data-key="${e.key??e.tempKey}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type||"text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${T(this.classes)}"
          .value="${e.value||""}"
          @change=${this._change}
          novalidate
        />

        ${Y(t>1||e.key||e.value,()=>u`
            <button
              class="input-addon btn-remove"
              @click=${this._removeItem}
              data-key="${e.key??e.tempKey}"
              ?disabled=${this.disabled}
            >
              <dt-icon icon="mdi:close"></dt-icon>
            </button>
          `,()=>u``)}
        <button
          class="input-addon btn-add"
          @click=${this._addItem}
          ?disabled=${this.disabled}
        >
          <dt-icon icon="mdi:plus-thick"></dt-icon>
        </button>
      </div>
    `}renderIcons(){let e=0,t=!1;for(const[a,n]of(this.value||[]).entries())!n.value&&a!==0?e+=1:n.delete&&!t&&(t=!0);let i=.5;t===!1&&(i+=3*e);const s=`padding-block-end: ${i.toString()}rem`;return u`
      ${this.renderIconInvalid()} ${this.renderError()}
      ${this.renderIconLoading(s)} ${this.renderIconSaved(s)}
    `}renderIconLoading(e){return this.loading?u`<dt-spinner class="icon-overlay" style="${e}"></dt-spinner>`:null}renderIconSaved(e){return this.saved&&(this.savedTimeout&&clearTimeout(this.savedTimeout),this.savedTimeout=setTimeout(()=>{this.savedTimeout=null,this.saved=!1},5e3)),this.saved?u`<dt-checkmark
          class="icon-overlay success fade-out"
          style="${e}"
        ></dt-checkmark>`:null}_renderInputFields(){return(!this.value||!this.value.length)&&(this.value=[{verified:!1,value:"",tempKey:Date.now().toString()}]),u`
      ${ce((this.value??[]).filter(e=>!e.delete),e=>e.id,e=>this._inputFieldTemplate(e,this.value.length))}
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t.value))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"text-input":!0,invalid:this.touched&&this.invalid}}render(){return u`
      ${this.labelTemplate()}
      <div class="input-group">
        ${this._renderInputFields()} ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-text",xs);class ks extends D{static get styles(){return[...super.styles,x`
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
          padding-block: 0;
        }

        .input-group.disabled {
          background-color: inherit;
        }

        .error-container {
          margin-block-start: 5px;
        }
        .invalid ~ .error-container {
          border-top-width: 1px;
        }
      `]}constructor(){super(),this.options=[]}static get properties(){return{value:{type:Array,reflect:!0},context:{type:String},options:{type:Array},outline:{type:Boolean}}}get _field(){return this.shadowRoot.querySelector(".input-group")}_select(e){const t=new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length){const i=this.value.includes(e);this.value=[...this.value.filter(s=>s!==e&&s!==`-${e}`),i?`-${e}`:e]}else this.value=[e];t.detail.newValue=this.value,this.dispatchEvent(t),this._setFormValue(this.value)}_clickOption(e){var t;(t=e==null?void 0:e.currentTarget)!=null&&t.value&&this._select(e.currentTarget.value)}_inputKeyUp(e){switch(e.keyCode||e.which){case 13:this._clickOption(e);break}}_renderButton(e){const i=(this.value??[]).includes(e.id)?"success":this.touched&&this.invalid?"alert":"inactive",s=this.outline??(this.touched&&this.invalid);return u`
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
        ${e.icon?u`<span class="icon"
              ><img src="${e.icon}" alt="${this.iconAltText}"
            /></span>`:null}
        ${e.label}
      </dt-button>
    `}_validateRequired(){const{value:e}=this;this.required&&(!e||e.every(t=>!t||t.charAt(0)==="-"))?(this.invalid=!0,this.internals.setValidity({valueMissing:!0},this.requiredMessage||"This field is required",this._field)):(this.invalid=!1,this.internals.setValidity({}))}get classes(){return{"button-group":!0,invalid:this.touched&&this.invalid}}render(){return u`
      ${this.labelTemplate()}
      <div class="input-group ${this.disabled?"disabled":""}">
        <div class="${T(this.classes)}">
          ${ce(this.options??[],e=>e.id,e=>this._renderButton(e))}
        </div>
        ${this.renderIcons()}
      </div>
    `}}window.customElements.define("dt-multi-select-button-group",ks);class Ss extends D{constructor(){super();Me(this,"_handleUploadStagedEvent",()=>{this.uploadStagedFiles()});this.value=[],this.acceptedFileTypes=["image/*","application/pdf"],this.maxFileSize=null,this.maxFiles=null,this.deleteEnabled=!0,this.downloadEnabled=!0,this.renameEnabled=!0,this.displayLayout="grid",this.fileTypeIcon="",this.autoUpload=!0,this.postType="",this.postId="",this.metaKey="",this.keyPrefix="",this.uploading=!1,this.stagedFiles=[],this._uploadZoneExpanded=!1,this._dragOver=!1,this._editingFileKey="",this._editingFileName="",this._dragLeaveTimeout=null,this._resizeObserver=null,this._keydownAttached=!1}static get styles(){return[...super.styles,x`
        :host {
          display: block;
        }

        .upload-zone {
          border: 2px dashed var(--dt-upload-border-color, #ccc);
          border-radius: 4px;
          text-align: center;
          background-color: var(--dt-upload-background-color, #fafafa);
          transition: padding 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
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
          border: 1px solid var(--dt-upload-file-border-color, #ddd);
          border-radius: 4px;
          overflow: hidden;
          background-color: var(--dt-upload-file-background-color, #fff);
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
          background-color: var(--dt-upload-file-icon-background, #f5f5f5);
          color: var(--dt-upload-file-icon-color, #999);
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
          color: var(--dt-upload-file-name-color, #333);
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
          color: var(--dt-upload-file-name-color, #333);
          padding: 0.25rem 0.5rem;
          width: 100%;
          box-sizing: border-box;
          border: 1px solid var(--primary-color, #0073aa);
          border-radius: 2px;
          background: var(--dt-upload-file-background-color, #fff);
        }

        .file-name-edit:focus {
          outline: none;
          border-color: var(--primary-color, #0073aa);
        }

        .file-size {
          font-size: 0.7rem;
          color: var(--dt-upload-file-size-color, #999);
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
      `]}static get properties(){return{...super.properties,value:{type:Array,reflect:!0},acceptedFileTypes:{type:Array,attribute:"accepted-file-types"},maxFileSize:{type:Number,attribute:"max-file-size"},maxFiles:{type:Number,attribute:"max-files"},deleteEnabled:{type:Boolean,attribute:"delete-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},downloadEnabled:{type:Boolean,attribute:"download-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},renameEnabled:{type:Boolean,attribute:"rename-enabled",converter:{fromAttribute:t=>t==null||t===""?!0:t!=="false"&&t!==!1}},displayLayout:{type:String,attribute:"display-layout"},fileTypeIcon:{type:String,attribute:"file-type-icon"},autoUpload:{type:Boolean,attribute:"auto-upload",converter:{fromAttribute:t=>{if(t==null)return!0;const i=String(t).toLowerCase().trim();return i!=="false"&&i!=="0"&&t!==!1}}},postType:{type:String,attribute:"post-type"},postId:{type:String,attribute:"post-id"},metaKey:{type:String,attribute:"meta-key"},keyPrefix:{type:String,attribute:"key-prefix"},uploading:{type:Boolean,state:!0},stagedFiles:{type:Array,state:!0},_uploadZoneExpanded:{type:Boolean,state:!0},_dragOver:{type:Boolean,state:!0},_editingFileKey:{type:String,state:!0},_editingFileName:{type:String,state:!0}}}connectedCallback(){super.connectedCallback(),this.addEventListener("dt:upload-files",this._handleUploadStagedEvent),this._boundKeydown=this._handleHostKeydown.bind(this)}disconnectedCallback(){var t;super.disconnectedCallback(),this.removeEventListener("dt:upload-files",this._handleUploadStagedEvent),this._removeKeydownListener(),this._cancelScheduledCollapse(),(t=this._resizeObserver)==null||t.disconnect()}_addKeydownListener(){this._keydownAttached||(this._keydownAttached=!0,this.addEventListener("keydown",this._boundKeydown,{capture:!0}))}_removeKeydownListener(){this._keydownAttached&&(this._keydownAttached=!1,this.removeEventListener("keydown",this._boundKeydown,{capture:!0}))}_handleHostKeydown(t){var s;if(!this._editingFileKey)return;const i=(s=this.shadowRoot)==null?void 0:s.querySelector(".file-name-edit");i&&(t.key==="Enter"||t.keyCode===13?(t.preventDefault(),t.stopPropagation(),t.stopImmediatePropagation(),this._commitRename(this._editingFileKey,i.value)):(t.key==="Escape"||t.keyCode===27)&&(t.preventDefault(),t.stopPropagation(),t.stopImmediatePropagation(),this._cancelRename()))}firstUpdated(t){super.firstUpdated(t),this._setupResizeObserver()}updated(t){super.updated(t),(t.has("value")||t.has("stagedFiles")||t.has("error"))&&this.updateComplete.then(()=>this._refreshMasonry()),t.has("_editingFileKey")&&(this._editingFileKey?(this._addKeydownListener(),this.updateComplete.then(()=>{var s;const i=(s=this.shadowRoot)==null?void 0:s.querySelector(".file-name-edit");i&&(i.focus(),i.select())})):this._removeKeydownListener())}_setupResizeObserver(){typeof ResizeObserver>"u"||(this._resizeObserver=new ResizeObserver(()=>{this._refreshMasonry()}),this._resizeObserver.observe(this))}_refreshMasonry(){if(typeof window<"u"&&window.jQuery){const t=this;requestAnimationFrame(()=>{let i=null;window.masonGrid&&window.masonGrid.length&&window.masonGrid.masonry?i=window.masonGrid:i=window.jQuery(t).closest(".grid, .masonry-container, .masonry, [data-masonry]"),i&&i.length&&i.masonry&&i.masonry("layout")})}}_expandUploadZone(){this._uploadZoneExpanded=!0}_scheduleCollapse(){this._cancelScheduledCollapse(),this._dragLeaveTimeout=setTimeout(()=>{this._uploadZoneExpanded=!1,this._dragLeaveTimeout=null},300)}_cancelScheduledCollapse(){this._dragLeaveTimeout&&(clearTimeout(this._dragLeaveTimeout),this._dragLeaveTimeout=null)}uploadStagedFiles(){this.stagedFiles.length>0&&this._uploadFiles(this.stagedFiles)}_removeStagedFile(t){t>=0&&t<this.stagedFiles.length&&(this.stagedFiles=this.stagedFiles.filter((i,s)=>s!==t),this.requestUpdate())}_parseValue(t){if(Array.isArray(t))return t;if(typeof t=="string")try{const i=JSON.parse(t);return Array.isArray(i)?i:[]}catch{return[]}return[]}_formatFileSize(t){return t<1024?`${t} B`:t<1024*1024?`${(t/1024).toFixed(1)} KB`:`${(t/(1024*1024)).toFixed(1)} MB`}_isImage(t){return(t.type||"").toLowerCase().startsWith("image/")}_mdiToIconify(t){if(!t||typeof t!="string")return"";const i=t.trim();return i.startsWith("mdi:")?i:i.includes("mdi-")?`mdi:${i.replace(/.*mdi-/,"").replace(/\s/g,"-")}`:i.startsWith("mdi ")?`mdi:${i.replace(/^mdi\s+/,"").replace(/\s/g,"-")}`:i}_renderFileTypeIcon(t){const i=this.fileTypeIcon||"";if(!i)return null;if(/^(https?:|\/|data:)/.test(i))return u`<img src="${i}" alt="" />`;const a=this._mdiToIconify(i);return a?u`<dt-icon icon="${a}"></dt-icon>`:null}_getFilePreviewUrl(t){const i=t.thumbnail_key||t.large_thumbnail_key;if(this._isImage(t)){if(t.large_thumbnail_url)return t.large_thumbnail_url;if(t.thumbnail_url)return t.thumbnail_url;if(t.url)return t.url;if(i)return null}return null}_handleFileSelect(t){const i=Array.from(t.target.files||[]);i.length!==0&&(t.target.value="",this._processFiles(i))}_handleDrop(t){if(t.preventDefault(),t.stopPropagation(),this._dragOver=!1,t.currentTarget.classList.remove("drag-over"),this.disabled||this.uploading)return;const i=Array.from(t.dataTransfer.files||[]);i.length!==0&&this._processFiles(i)}_handleDragOver(t){t.preventDefault(),t.stopPropagation(),!this.disabled&&!this.uploading&&(this._dragOver=!0,this._expandUploadZone(),this._cancelScheduledCollapse(),t.currentTarget.classList.add("drag-over"))}_handleDragLeave(t){t.preventDefault(),t.stopPropagation(),this._dragOver=!1,t.currentTarget.classList.remove("drag-over"),this._scheduleCollapse()}_handleZoneClick(t){var i;if(!t.target.closest('input[type="file"]')&&(this._expandUploadZone(),this._cancelScheduledCollapse(),!this.disabled&&!this.uploading)){const s=(i=this.shadowRoot)==null?void 0:i.querySelector('input[type="file"]');s&&s.click()}}_handleZoneMouseEnter(){!this.disabled&&!this.uploading&&(this._expandUploadZone(),this._cancelScheduledCollapse())}_handleZoneMouseLeave(){this._scheduleCollapse()}_processFiles(t){const i=this._validateFiles(t);if(i.length===0)return;this.error="";const s=(this.value||[]).length+this.stagedFiles.length;if(this.maxFiles&&s+i.length>this.maxFiles){this.error=`${this.maxFiles} files allowed`;return}this.autoUpload?this._uploadFiles(i):(this.stagedFiles=[...this.stagedFiles,...i],this._uploadZoneExpanded=!1,this.requestUpdate(),this.updateComplete.then(()=>this._refreshMasonry()))}_validateFiles(t){const i=[],s=this.maxFileSize?this.maxFileSize*1024*1024:null,a=Array.isArray(this.acceptedFileTypes)?this.acceptedFileTypes:["image/*","application/pdf"],n=a.join(",");for(const r of t){if(s&&r.size>s){this.error=`File "${r.name}" exceeds ${this.maxFileSize} MB`;continue}if(n&&n!=="*"&&!a.some(d=>{if(d.startsWith("."))return r.name.toLowerCase().endsWith(d.toLowerCase());if(d.endsWith("/*")){const h=d.slice(0,-2);return(r.type||"").startsWith(h)}return r.type===d||r.name&&r.name.toLowerCase().endsWith(`.${d.split("/")[1]}`)})){this.error=`File type not allowed: ${r.name}`;continue}i.push(r)}return i}async _uploadFiles(t){var i,s;if(!this.postType||!this.postId||!this.metaKey){this.error="Missing required parameters for upload";return}if(!((i=window.wpApiShare)!=null&&i.nonce)){this.error="Authentication nonce not available";return}this.uploading=!0,this.loading=!0,this.error="";try{const a=new FormData;t.forEach(m=>a.append("storage_upload_files[]",m)),a.append("meta_key",this.metaKey),a.append("key_prefix",this.keyPrefix||""),a.append("upload_type","post"),a.append("is_multi_file","true"),a.append("storage_s3_url_duration","+7 days");const n=((s=window.wpApiShare)==null?void 0:s.root)||"/wp-json",r=`${n}dt-posts/v2/${this.postType}/${this.postId}/storage_upload`,l=await fetch(r,{method:"POST",headers:{"X-WP-Nonce":window.wpApiShare.nonce},body:a}),d=await l.json();if(!l.ok||!d.uploaded)throw new Error(d.uploaded_msg||"Upload failed");const h=`${n}dt-posts/v2/${this.postType}/${this.postId}`,g=await fetch(h,{headers:{"X-WP-Nonce":window.wpApiShare.nonce}});if(g.ok){const y=(await g.json())[this.metaKey],w=Array.isArray(this.value)?[...this.value]:[],O=(d.uploaded_files||[]).filter(C=>C.uploaded&&C.file).map(C=>C.file);if(O.length>0){const C=new Set(w.map(A=>String(A.key||A))),M=[...w];for(const A of O){const ee=String(A.key||A);C.has(ee)||(M.push(A),C.add(ee))}this.value=M}else Array.isArray(y)&&y.length>0&&(this.value=y)}else{const m=Array.isArray(this.value)?[...this.value]:[];d.uploaded_files&&d.uploaded_files.forEach(y=>{y.uploaded&&y.file&&m.push(y.file)}),this.value=m}this.stagedFiles=[],this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:this.value}})),this._refreshMasonry(),this._uploadZoneExpanded=!1,this.saved=!0}catch(a){console.error("Upload error:",a),this.error=a.message||"Upload failed"}finally{this.uploading=!1,this.loading=!1}}async _deleteFile(t){var i,s;if(!(!this.deleteEnabled||!this.postType||!this.postId||!this.metaKey)){if(!((i=window.wpApiShare)!=null&&i.nonce)){this.error="Authentication nonce not available";return}if(confirm("Are you sure you want to delete this file?")){this.loading=!0,this.error="";try{const n=`${((s=window.wpApiShare)==null?void 0:s.root)||"/wp-json"}dt-posts/v2/${this.postType}/${this.postId}/storage_delete_single`,r=await fetch(n,{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":window.wpApiShare.nonce},body:JSON.stringify({meta_key:this.metaKey,file_key:t})}),l=await r.json();if(!r.ok||!l.deleted)throw new Error(l.message||"Delete failed");this.value=(this.value||[]).filter(d=>(d.key||d)!==t),this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:this.value}})),this.updateComplete.then(()=>this._refreshMasonry())}catch(a){console.error("Delete error:",a),this.error=a.message||"Delete failed"}finally{this.loading=!1}}}}async _renameFile(t,i){var s,a;if(!(!this.renameEnabled||!this.postType||!this.postId||!this.metaKey)){if(!((s=window.wpApiShare)!=null&&s.nonce)){this.error="Authentication nonce not available";return}this.loading=!0,this.error="";try{const r=`${((a=window.wpApiShare)==null?void 0:a.root)||"/wp-json"}dt-posts/v2/${this.postType}/${this.postId}/storage_rename_single`,l=await fetch(r,{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":window.wpApiShare.nonce},body:JSON.stringify({meta_key:this.metaKey,file_key:t,new_name:i})}),d=await l.json();if(!l.ok||!d.renamed)throw new Error(d.error||d.message||"Rename failed");const h=this._parseValue(this.value);this.value=h.map(g=>(g.key||g)===t?{...g,name:i}:g),this._editingFileKey="",this.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{field:this.name,oldValue:this.value,newValue:this.value}})),this.updateComplete.then(()=>this._refreshMasonry())}catch(n){console.error("Rename error:",n),this.error=(n==null?void 0:n.message)||"Rename failed"}finally{this.loading=!1}}}_startRename(t,i){!this.renameEnabled||this.disabled||(this._editingFileKey=typeof t=="string"?t:String(t),this._editingFileName=i||"")}_commitRename(t,i){const s=(i??this._editingFileName??"").trim();if(this._editingFileKey="",this._editingFileName="",!s)return;const n=this._parseValue(this.value).find(l=>(l.key||l)===t),r=(n==null?void 0:n.name)||(typeof t=="string"?t.split("/").pop():"");s!==r&&this._renameFile(t,s)}_cancelRename(){this._editingFileKey="",this._editingFileName=""}_downloadFile(t){if(!this.downloadEnabled)return;const i=t.url;if(!i)return;const s=document.createElement("a");s.href=i,s.download=t.name||"download",s.target="_blank",s.rel="noopener",document.body.appendChild(s),s.click(),document.body.removeChild(s)}_validateRequired(){var i,s,a,n;const t=Array.isArray(this.value)?this.value:[];this.required&&t.length===0?(this.invalid=!0,(s=(i=this.internals)==null?void 0:i.setValidity)==null||s.call(i,{valueMissing:!0},this.requiredMessage||"This field is required")):(this.invalid=!1,(n=(a=this.internals)==null?void 0:a.setValidity)==null||n.call(a,{}))}render(){const t=this._parseValue(this.value),s=(this.displayLayout||"grid")==="grid";return u`
      <div class="input-group">
        ${this.labelTemplate()}
        <div
          class="upload-zone ${T({compact:!this._uploadZoneExpanded,expanded:this._uploadZoneExpanded,disabled:this.disabled,"drag-over":this._dragOver,uploading:this.uploading})}"
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
            <span class="upload-icon"><dt-icon icon="mdi:cloud-upload"></dt-icon></span>
            <span class="expandable upload-text">Drag files here or click to upload</span>
            <span class="expandable upload-hint">${(this.acceptedFileTypes||[]).join(", ")}${this.maxFileSize?` • Max ${this.maxFileSize} MB`:""}</span>
          </div>
        </div>

        ${Y(this.stagedFiles.length>0&&!this.autoUpload,()=>u`
          <div class="staged-files">
            <div class="staged-files-title">Staged files (${this.stagedFiles.length})</div>
            ${ce(this.stagedFiles,(a,n)=>`${a.name}-${a.size}-${n}`,(a,n)=>u`
              <div class="staged-file-item">
                <span>${a.name} (${this._formatFileSize(a.size)})</span>
                <button class="remove" type="button" title="Remove" @click=${r=>{r.stopPropagation(),this._removeStagedFile(n)}}>
                  <dt-icon icon="mdi:trash-can"></dt-icon>
                </button>
              </div>
            `)}
            <button class="upload-staged-btn" type="button" ?disabled=${this.uploading} @click=${()=>this.uploadStagedFiles()}>
              Upload
            </button>
          </div>
        `)}

        ${Y(t.length>0,()=>u`
          <div class="files-container">
            <div class=${s?"files-grid":"files-list"}>
              ${ce(t,a=>a.key||a,a=>{const n=typeof a.key=="string"?a.key:typeof a=="string"?a:String(a.key??a.name??""),r=a.name||(typeof n=="string"?n.split("/").pop():""),l=a.size,d=this._getFilePreviewUrl(a),h=this._isImage(a),g=this._editingFileKey===n;return u`
                    <div class="file-item ${s?"file-item-grid":"file-item-list"}">
                      ${Y(d,()=>u`
                          <a
                            class="file-preview-link"
                            href=${a.url||"#"}
                            target="_blank"
                            rel="noopener"
                            @click=${m=>{a.url||m.preventDefault()}}
                          >
                            <img src="${d}" alt="${r}" loading="lazy" />
                          </a>
                        `,()=>u`
                          ${a.url?u`
                                <a
                                  class="file-preview-link file-icon-area"
                                  href=${a.url}
                                  target="_blank"
                                  rel="noopener"
                                >
                                  ${this._renderFileTypeIcon(a)||(h?u`<dt-icon icon="mdi:image"></dt-icon>`:u`<dt-icon icon="mdi:file"></dt-icon>`)}
                                </a>
                              `:u`
                                <div class="file-icon-area">
                                  ${this._renderFileTypeIcon(a)||(h?u`<dt-icon icon="mdi:image"></dt-icon>`:u`<dt-icon icon="mdi:file"></dt-icon>`)}
                                </div>
                              `}
                        `)}
                      ${Y(g,()=>u`
                          <input
                            class="file-name-edit"
                            type="text"
                            .value=${this._editingFileName}
                            @input=${m=>{this._editingFileName=m.target.value}}
                            @keydown=${m=>{m.key==="Enter"||m.keyCode===13?(m.preventDefault(),m.stopPropagation(),this._commitRename(n,m.target.value)):(m.key==="Escape"||m.keyCode===27)&&(m.preventDefault(),this._cancelRename())}}
                            @blur=${m=>this._commitRename(n,m.target.value)}
                            @click=${m=>m.stopPropagation()}
                          />
                        `,()=>u`
                          <div
                            class="file-name ${this.renameEnabled&&!this.disabled?"file-name-editable":""}"
                            role=${this.renameEnabled&&!this.disabled?"button":void 0}
                            tabindex=${this.renameEnabled&&!this.disabled?0:void 0}
                            @click=${m=>{m.stopPropagation(),this.renameEnabled&&!this.disabled&&this._startRename(n,r)}}
                            @keydown=${m=>{this.renameEnabled&&!this.disabled&&(m.key==="Enter"||m.key===" ")&&(m.preventDefault(),this._startRename(n,r))}}
                          >
                            ${r}
                          </div>
                        `)}
                      ${Y(l!=null,()=>u`<div class="file-size">${this._formatFileSize(l)}</div>`)}
                      <div class="file-actions">
                        ${Y(this.downloadEnabled&&a.url,()=>u`
                            <button class="download" type="button" @click=${m=>{m.stopPropagation(),this._downloadFile(a)}} title="Download"><dt-icon icon="mdi:cloud-download"></dt-icon></button>
                          `)}
                        ${Y(this.deleteEnabled,()=>u`
                            <button class="delete" type="button" @click=${m=>{m.stopPropagation(),this._deleteFile(n)}} title="Delete"><dt-icon icon="mdi:trash-can"></dt-icon></button>
                          `)}
                      </div>
                    </div>
                  `})}
            </div>
          </div>
        `)}

        ${this.renderIcons()}
      </div>
    `}}customElements.define("dt-upload-file",Ss);class Es extends N{static get styles(){return x`
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
    `}static get properties(){return{context:{type:String},dismissable:{type:Boolean},timeout:{type:Number},hide:{type:Boolean},outline:{type:Boolean}}}get classes(){const e={"dt-alert":!0,"dt-alert--outline":this.outline},t=`dt-alert--${this.context}`;return e[t]=!0,e}constructor(){super(),this.context="default"}connectedCallback(){super.connectedCallback(),this.timeout&&setTimeout(()=>{this._dismiss()},this.timeout)}_dismiss(){this.hide=!0}render(){if(this.hide)return u``;const e=u`
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
    `;return u`
      <div role="alert" class=${T(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable?u`
              <button @click="${this._dismiss}" class="toggle">${e}</button>
            `:null}
      </div>
    `}}window.customElements.define("dt-alert",Es);class Ts extends N{static get styles(){return x`
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
    `}static get properties(){return{postType:{type:String},postTypeLabel:{type:String},posttypesettings:{type:Object,attribute:!0},posts:{type:Array},total:{type:Number},columns:{type:Array},sortedBy:{type:String},loading:{type:Boolean,default:!0},offset:{type:Number},showArchived:{type:Boolean,default:!1},showFieldsSelector:{type:Boolean,default:!1},showBulkEditSelector:{type:Boolean,default:!1},nonce:{type:String},payload:{type:Object},favorite:{type:Boolean},initialLoadPost:{type:Boolean,default:!1},loadMore:{type:Boolean,default:!1},headerClick:{type:Boolean,default:!1}}}constructor(){super(),this.sortedBy="name",this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.sortedColumns},this.initalLoadPost=!1,this.initalLoadPost||(this.posts=[],this.limit=100)}firstUpdated(){this.postTypeSettings=window.post_type_fields,this.sortedColumns=this.columns.includes("favorite")?["favorite",...this.columns.filter(e=>e!=="favorite")]:this.columns,this.style.setProperty("--number-of-columns",this.columns.length-1)}async _getPosts(e){const t=await new CustomEvent("dt:get-data",{bubbles:!0,detail:{field:this.name,postType:this.postType,query:e,onSuccess:i=>{this.initalLoadPost&&this.loadMore&&(this.posts=[...this.posts,...i],this.postsLength=this.posts.length,this.total=i.length,this.loadMore=!1),this.initalLoadPost||(this.posts=[...i],this.offset=this.posts.length,this.initalLoadPost=!0,this.total=i.length),this.headerClick&&(this.posts=i,this.offset=this.posts.length,this.headerClick=!1),this.total=i.length},onError:i=>{console.warn(i)}}});this.dispatchEvent(t)}_headerClick(e){const t=e.target.dataset.id;this.sortedBy===t?t.startsWith("-")?this.sortedBy=t.replace("-",""):this.sortedBy=`-${t}`:this.sortedBy=t,this.payload={sort:this.sortedBy,overall_status:["-closed"],limit:this.limit,fields_to_return:this.columns},this.headerClick=!0,this._getPosts(this.payload)}static _rowClick(e){window.open(e,"_self")}_bulkEdit(){this.showBulkEditSelector=!this.showBulkEditSelector}_fieldsEdit(){this.showFieldsSelector=!this.showFieldsSelector}_toggleShowArchived(){if(this.showArchived=!this.showArchived,this.headerClick=!0,this.showArchived){const{overall_status:e,offset:t,...i}=this.payload;this.payload=i}else this.payload.overall_status=["-closed"];this._getPosts(this.payload)}_sortArrowsClass(e){return this.sortedBy===e?"sortedBy":""}_sortArrowsToggle(e){return this.sortedBy!==`-${e}`?`-${e}`:e}_headerTemplate(){return this.postTypeSettings?u`
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
            ${Je(this.sortedColumns,e=>{const t=e==="favorite";return u`<th
                class="all"
                data-id="${this._sortArrowsToggle(e)}"
                @click=${this._headerClick}
              >
                  <span class="column-name"
                     >${t?null:this.postTypeSettings[e].name}</span
                  >
                  ${t?"":u`<span id="sort-arrows">
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
      `:null}_rowTemplate(){if(this.posts&&Array.isArray(this.posts)){const e=this.posts.map((t,i)=>this.showArchived||!this.showArchived&&t.overall_status!=="closed"?u`
              <tr class="dnd-moved" data-link="${t.permalink}" @click=${()=>this._rowClick(t.permalink)}>
                <td class="bulk_edit_checkbox no-title">
                  <input type="checkbox" name="bulk_edit_id" .value="${t.ID}" />
                </td>
                <td class="no-title line-count">${i+1}.</td>
                ${this._cellTemplate(t)}
              </tr>
            `:null).filter(t=>t!==null);return e.length>0?e:u`<p>No contacts available</p>`}return null}formatDate(e){const t=new Date(e);return new Intl.DateTimeFormat("en-US",{month:"long",day:"numeric",year:"numeric"}).format(t)}_cellTemplate(e){return Je(this.sortedColumns,t=>{if(["text","textarea","number"].includes(this.postTypeSettings[t].type))return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t]}
        </td>`;if(this.postTypeSettings[t].type==="date")return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${this.formatDate(e[t].formatted)}
        </td>`;if(this.postTypeSettings[t].type==="user_select"&&e[t]&&e[t].display)return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${B(e[t].display)}
        </td>`;if(this.postTypeSettings[t].type==="key_select"&&e[t]&&(e[t].label||e[t].name))return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${e[t].label||e[t].name}
        </td>`;if(this.postTypeSettings[t].type==="multi_select"||this.postTypeSettings[t].type==="tags"&&e[t]&&e[t].length>0)return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <ul>
            ${Je(e[t],i=>u`<li>
                  ${this.postTypeSettings[t].default[i].label}
                </li>`)}
          </ul>
        </td>`;if(this.postTypeSettings[t].type==="location"||this.postTypeSettings[t].type==="location_meta")return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${B(e[t].label)}
        </td>`;if(this.postTypeSettings[t].type==="communication_channel")return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          ${B(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="connection")return u` <td
          dir="auto"
          title="${this.postTypeSettings[t].name}"
        >
          <!-- TODO: look at this, it doesn't match the current theme. -->
          ${B(e[t].value)}
        </td>`;if(this.postTypeSettings[t].type==="boolean"){if(t==="favorite")return u`<td
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
                class="${T({"icon-star":!0,selected:e.favorite})}"
                height="15"
                viewBox="0 0 32 32"
              >
                <path
                  d="M 31.916 12.092 C 31.706 11.417 31.131 10.937 30.451 10.873 L 21.215 9.996 L 17.564 1.077 C 17.295 0.423 16.681 0 16 0 C 15.318 0 14.706 0.423 14.435 1.079 L 10.784 9.996 L 1.546 10.873 C 0.868 10.937 0.295 11.417 0.084 12.092 C -0.126 12.769 0.068 13.51 0.581 13.978 L 7.563 20.367 L 5.503 29.83 C 5.354 30.524 5.613 31.245 6.165 31.662 C 6.462 31.886 6.811 32 7.161 32 C 7.463 32 7.764 31.915 8.032 31.747 L 16 26.778 L 23.963 31.747 C 24.546 32.113 25.281 32.08 25.834 31.662 C 26.386 31.243 26.645 30.524 26.494 29.83 L 24.436 20.367 L 31.417 13.978 C 31.931 13.51 32.127 12.769 31.916 12.092 Z M 31.916 12.092"
                />
              </svg>
            </dt-button>
          </td>`;if(this.postTypeSettings[t]===!0)return u`<td
            dir="auto"
            title="${this.postTypeSettings[t].name}"
          >
            ['&check;']
          </td>`}return u`<td
        dir="auto"
        title="${this.postTypeSettings[t].name}"
      ></td>`})}_fieldListIconTemplate(e){return this.postTypeSettings[e].icon?u`<img
        class="dt-icon"
        src="${this.postTypeSettings[e].icon}"
        alt="${this.postTypeSettings[e].name}"
      />`:null}_fieldsListTemplate(){return ce(Object.keys(this.postTypeSettings).sort((e,t)=>{const i=this.postTypeSettings[e].name.toUpperCase(),s=this.postTypeSettings[t].name.toUpperCase();return i<s?-1:i>s?1:0}),e=>e,e=>this.postTypeSettings[e].hidden?null:u`<li class="list-field-picker-item">
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
          </li> `)}_fieldsSelectorTemplate(){return this.showFieldsSelector?u`<div
        id="list_column_picker"
        class="list_field_picker list_action_section"
      >
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${I("Choose which fields to display as columns in the list")}
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
      </div>`:null}_updateFields(e){const t=e.target.value,i=this.columns;i.includes(t)?(i.filter(s=>s!==t),i.splice(i.indexOf(t),1)):i.push(t),this.columns=i,this.style.setProperty("--number-of-columns",this.columns.length-1),this.requestUpdate()}_bulkSelectorTemplate(){return this.showBulkEditSelector?u`<div id="bulk_edit_picker" class="list_action_section">
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${I(p`Select all the ${this.postType} you want to update from the list, and update them below`)}
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
      </div>`:null}connectedCallback(){super.connectedCallback(),this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns},this.posts.length===0&&this._getPosts(this.payload).then(e=>{this.posts=e})}_handleLoadMore(){this.limit=500,this.payload={sort:this.sortedBy,overall_status:["-closed"],fields_to_return:this.columns,offset:this.offset,limit:this.limit},this.loadMore=!0,this._getPosts(this.payload).then(e=>{console.log(e)})}render(){const e={bulk_editing:this.showBulkEditSelector,hidden:!1};this.posts&&(this.total=this.posts.length);const t=u`
      <svg viewBox="0 0 100 100" fill="#000000" style="enable-background:new 0 0 100 100;" xmlns="http://www.w3.org/2000/svg">
        <line style="stroke-linecap: round; paint-order: fill; fill: none; stroke-width: 15px;" x1="7.97" y1="50.199" x2="76.069" y2="50.128" transform="matrix(0.999999, 0.001017, -0.001017, 0.999999, 0.051038, -0.042708)"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="17.751" x2="92.058" y2="17.751"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="82.853" x2="42.343" y2="82.853"/>
        <polygon style="stroke-linecap: round; stroke-miterlimit: 1; stroke-linejoin: round; fill: rgb(255, 255, 255); paint-order: stroke; stroke-width: 9px;" points="22.982 64.982 33.592 53.186 50.916 70.608 82.902 21.308 95 30.85 52.256 95"/>
      </svg>
    `,i=u`<svg height='100px' width='100px'  fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M94.4,63c0-5.7-3.6-10.5-8.6-12.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5     s3.6,10.5,8.6,12.5v17.2c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5C90.9,73.6,94.4,68.7,94.4,63z M81,66.7     c-2,0-3.7-1.7-3.7-3.7c0-2,1.7-3.7,3.7-3.7s3.7,1.7,3.7,3.7C84.7,65.1,83.1,66.7,81,66.7z"></path><path d="M54.8,24.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v17.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v43.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V49.5c5-1.9,8.6-6.8,8.6-12.5S59.8,26.5,54.8,24.5z M50,40.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C53.7,39.1,52,40.7,50,40.7z"></path><path d="M23.8,50.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v17.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5c5-1.9,8.6-6.8,8.6-12.5S28.8,52.5,23.8,50.5z M19,66.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C22.7,65.1,21,66.7,19,66.7z"></path></g></g></g></svg>`;return u`
      <div class="section">
        <div class="header">
          <div class="section-header">
            <span
              class="section-header posts-header"
              style="display: inline-block"
              >${I(p`${this.postTypeLabel?this.postTypeLabel:this.postType} List`)}</span
            >
          </div>
          <span class="filter-result-text"
            >${I(p`Showing ${this.total} of ${this.total}`)}</span
          >

          <button
            class="bulkToggle toggleButton"
            id="bulk_edit_button"
            @click=${this._bulkEdit}
          >
            ${t} ${I("Bulk Edit")}
          </button>
          <button
            class="fieldsToggle toggleButton"
            id="fields_edit_button"
            @click=${this._fieldsEdit}
          >
            ${i} ${I("Fields")}
          </button>

          <dt-toggle
            name="showArchived"
            label=${I("Show Archived")}
            ?checked=${this.showArchived}
            hideIcons
            onchange=${this._toggleShowArchived}
            @click=${this._toggleShowArchived}
          ></dt-toggle>
        </div>

        ${this._fieldsSelectorTemplate()} ${this._bulkSelectorTemplate()}
        <table class="table-contacts ${T(e)}">
          ${this._headerTemplate()}
          ${this.posts?this._rowTemplate():I("Loading")}
        </table>
          ${this.total>=100?u`<div class="text-center"><dt-button buttonStyle=${JSON.stringify({margin:"0"})} class="loadMoreButton btn btn-primary" @click=${this._handleLoadMore}>Load More</dt-button></div>`:""}
      </div>
    `}}window.customElements.define("dt-list",Ts);class As extends N{static get styles(){return x`
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
    `}static get properties(){return{title:{type:String},expands:{type:Boolean},collapsed:{type:Boolean},gap:{type:String}}}get hasHeading(){return this.title||this.expands}_toggle(){this.collapsed=!this.collapsed}renderHeading(){return this.hasHeading?u`
      <h3 class="section-header">
        ${this.title}
        ${this.expands?u`
              <button
                @click="${this._toggle}"
                class="toggle chevron ${this.collapsed?"down":"up"}"
              >
                &nbsp;
              </button>
            `:null}
      </h3>
    `:L}render(){return u`
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
    `}}window.customElements.define("dt-tile",As);class Ie{get api(){return this._api}constructor(e,t,i,s="wp-json"){this.postType=e,this.postId=t,this.nonce=i,this.debounceTimers={},this._api=new vt(this.nonce,s),this.apiRoot=this._api.apiRoot,this.autoSaveComponents=["dt-connection","dt-users-connection","dt-date","dt-datetime","dt-location","dt-location-map","dt-multi-select","dt-number","dt-single-select","dt-tags","dt-text","dt-textarea","dt-toggle","dt-multi-text","dt-multi-select-button-group","dt-list","dt-button","dt-church-health-circle"],this.dynamicLoadComponents=["dt-connection","dt-tags","dt-modal","dt-list","dt-button","dt-location","dt-users-connection"]}initialize(){this.postId&&this.enableAutoSave(),this.attachLoadEvents()}async attachLoadEvents(e){const t=document.querySelectorAll(e||this.dynamicLoadComponents.join(","));t&&t.forEach(i=>{i.dataset.eventDtGetData||(i.addEventListener("dt:get-data",this.handleGetDataEvent.bind(this)),i.dataset.eventDtGetData=!0)})}async checkDuplicates(e,t){const i=document.querySelector("dt-modal.duplicate-detected");if(i){const s=i.shadowRoot.querySelector(".duplicates-detected-button");s&&(s.style.display="none");const a=await this._api.checkDuplicateUsers(this.postType,this.postId);t&&a.ids.length>0&&s&&(s.style.display="block")}}enableAutoSave(e){const t=document.querySelectorAll(e||this.autoSaveComponents.join(","));t&&t.forEach(i=>{i.addEventListener("change",this.handleChangeEvent.bind(this))})}async handleGetDataEvent(e){const t=e.detail;if(t){const{field:i,query:s,onSuccess:a,onError:n}=t;try{const r=e.target.tagName.toLowerCase();let l=[];switch(r){case"dt-button":l=await this._api.getContactInfo(this.postType,this.postId);break;case"dt-list":l=(await this._api.fetchPostsList(this.postType,s)).posts;break;case"dt-connection":{const d=t.postType||this.postType,h=await this._api.listPostsCompact(d,s),g={...h,posts:h.posts.filter(m=>m.ID!==parseInt(this.postId,10))};g!=null&&g.posts&&(l=Ie.convertApiValue("dt-connection",g==null?void 0:g.posts));break}case"dt-users-connection":{const d=t.postType||this.postType,h=await this._api.searchUsers(d,s),g={...h,posts:h.filter(m=>m.ID!==parseInt(this.postId,10))};g!=null&&g.posts&&(l=Ie.convertApiValue("dt-users-connection",g==null?void 0:g.posts));break}case"dt-location":{l=await this._api.getLocations(this.postType,i,t.filter,s),l=l.location_grid.map(d=>({id:d.ID,label:d.name}));break}case"dt-tags":default:l=await this._api.getMultiSelectValues(this.postType,i,s),l=l.map(d=>({id:d,label:d}));break}a(l)}catch(r){n(r)}}}async handleChangeEvent(e){const t=e.detail;if(t){const{field:i,newValue:s,oldValue:a,remove:n}=t,r=e.target.tagName.toLowerCase(),l=Ie.convertValue(r,s,a);if(e.target.removeAttribute("saved"),e.target.setAttribute("loading",!0),r==="dt-number"){const d=`${this.postType}-${this.postId}-${i}`;this.debounce(d,async()=>{try{const h=await this._api.updatePost(this.postType,this.postId,{[i]:l});document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:h,field:i,value:l,component:r}})),e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(h){console.error(h),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",h.message||h.toString())}},1e3)}else try{const d={[i]:l};if(r==="dt-location-map"){const g=l.values.filter(m=>!m.lng||!m.lat);d[i].values=l.values.filter(m=>m.lng&&m.lat),d.contact_address=g,d.contact_address.length===0&&delete d.contact_address,d[i].values.length===0&&delete d[i]}const h=await this._api.updatePost(this.postType,this.postId,d);if(document.dispatchEvent(new CustomEvent("dt:post:update",{detail:{response:h,field:i,value:l,component:r}})),r==="dt-location-map"){const g=e.target;g.value=h[i]}e.target.removeAttribute("loading"),e.target.setAttribute("error",""),e.target.setAttribute("saved",!0)}catch(d){console.error(d),e.target.removeAttribute("loading"),e.target.setAttribute("invalid",!0),e.target.setAttribute("error",d.message||d.toString())}}}debounce(e,t,i){this.debounceTimers[e]&&clearTimeout(this.debounceTimers[e]),this.debounceTimers[e]=setTimeout(()=>{t()},i)}static convertApiValue(e,t){let i=t;switch(e){case"dt-connection":i=t.map(s=>({id:s.ID,label:s.name??s.post_title,link:s.permalink,status:s.status}));break;case"dt-users-connection":t&&!Array.isArray(t)&&(t.id||t.ID)?i=[{id:t.id||t.ID,label:t.display,avatar:t.avatar||""}]:Array.isArray(t)&&(i=t.map(s=>({id:s.id||s.ID,label:s.display||s.name,avatar:s.avatar||""})));break}return i}static convertValue(e,t,i=null){let s=t;if(t)switch(e.toLowerCase()){case"dt-toggle":typeof t=="string"&&(s=t.toLowerCase()==="true");break;case"dt-church-health-circle":case"dt-multi-select":case"dt-multi-select-button-group":case"dt-tags":typeof t=="string"&&(s=[t]),s={values:s.map(n=>{if(typeof n=="string"){const l={value:n};return n.startsWith("-")&&(l.delete=!0,l.value=n.substring(1)),l}const r={value:n.id};return n.delete&&(r.delete=n.delete),r}),force_values:!1};break;case"dt-users-connection":{const n=[],r=s.filter(d=>!d.delete);if(r.length<=1){s=r.length===1?parseInt(r[0].id,10):"";break}const l=new Map((i||[]).map(d=>[d.id,d]));for(const d of s){const h=l.get(d.id),g={id:d.id,changes:{}};if(h){let m=!1;const y=new Set([...Object.keys(h),...Object.keys(d)]);for(const w of y)d[w]!==h[w]&&(g.changes[w]=Object.prototype.hasOwnProperty.call(d,w)?d[w]:void 0,m=!0);if(m){n.push(g);break}}else{g.changes={...d},n.push(g);break}}s=n[0].id;break}case"dt-connection":typeof t=="string"&&(s=[{id:t}]),s={values:s.map(n=>{const r={value:n.id};return n.delete&&(r.delete=n.delete),r}),force_values:!1};break;case"dt-location":const a=new Set((i||[]).map(n=>n.id));typeof t=="string"?s=[{id:t}]:s=t.filter(n=>!(a.has(n.id)&&!n.delete)),s={values:s.map(n=>{const r={value:n.id};return n.delete&&(r.delete=n.delete),r}),force_values:!1};break;case"dt-location-map":if(s=t.filter(n=>!((i||[]).includes(n)&&!n.delete)),i)for(const n of i)t.some(l=>n.id&&l.id&&n.id===l.id||n.key&&l.key&&n.key===l.key&&(!l.lat||!l.lng))||(n.delete=!0,s.push(n));s={values:s.map(n=>{const r=n;return n.delete&&(r.delete=n.delete),r}),force_values:!1};break;case"dt-multi-text":Array.isArray(t)?s=t.map(n=>{const r={...n};return delete r.tempKey,r}):typeof t=="string"&&(s=[{value:t}]);break}return s}static valueArrayDiff(e,t){const i={value1:[],value2:[]};if(Array.isArray(e)||(e=[]),Array.isArray(t)||(t=[]),e.length>0&&typeof e[0]!="object")return i.value1=e.filter(r=>!t.includes(r)),i.value2=t.filter(r=>!e.includes(r)),i;const s=r=>JSON.stringify(r),a=new Map(e.map(r=>[s(r),r])),n=new Map(t.map(r=>[s(r),r]));for(const[r,l]of a)n.has(r)||i.value1.push(l);for(const[r,l]of n)a.has(r)||i.value2.push(l);return i}}const Va="0.8.9",Ba={s226be12a5b1a27e8:"ሰነዶቹን ያንብቡ",s33f85f24c0f5f008:"አስቀምጥ",s36cb242ac90353bc:"መስኮች",s41cb4006238ebd3b:"የጅምላ አርትዕ",s5e8250fb85d64c23:"ገጠመ",s625ad019db843f94:"ተጠቀም",sac83d7f9358b43db:p`${0} ዝርዝር`,sbf1ca928ec1deb62:"ተጨማሪ እገዛ ይፈልጋሉ?",sd1a8dc951b2b6a98:"በዝርዝሩ ውስጥ እንደ ዓምዶች የትኞቹን መስኮች እንደሚያሳዩ ይምረጡ",sf9aee319a006c9b4:"አክል",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ha=Object.freeze(Object.defineProperty({__proto__:null,templates:Ba},Symbol.toStringTag,{value:"Module"})),Ka={s04ceadb276bbe149:"خيارات التحميل...",s226be12a5b1a27e8:"اقرأ الوثائق",s29e25f5e4622f847:"افتح",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"غلق",s625ad019db843f94:"استخدام",s9d51bfd93b5dbeca:"عرض المحفوظات",sac83d7f9358b43db:p`${0}قائمة الأعضاء`,sb1bd536b63e9e995:"المجال الخاص: أنا فقط أستطيع رؤية محتواه",sb59d68ed12d46377:"جار التحميل",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",scb9a1ff437efbd2a:p`حَدِّد جميع ${0} التي تريد تحديثها من القائمة ، وقم بتحديثها أدناه`,sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",seafe6ef133ede7da:p`عرض 1 of ${0}`,sf9aee319a006c9b4:"لأضف",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Wa=Object.freeze(Object.defineProperty({__proto__:null,templates:Ka},Symbol.toStringTag,{value:"Module"})),Ga={s226be12a5b1a27e8:"اقرأ الوثائق",s33f85f24c0f5f008:"حفظ",s36cb242ac90353bc:"مجالات",s41cb4006238ebd3b:"التحرير بالجملة",s5e8250fb85d64c23:"أغلق",s625ad019db843f94:"استخدام",sbf1ca928ec1deb62:"هل تريد المزيد من المساعدة؟",sd1a8dc951b2b6a98:"اختر المجالات المراد عرضها كأعمدة في القائمة",sf9aee319a006c9b4:"إضافة",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Za=Object.freeze(Object.defineProperty({__proto__:null,templates:Ga},Symbol.toStringTag,{value:"Module"})),Ja={s226be12a5b1a27e8:"Прочетете документацията",s33f85f24c0f5f008:"Запазете",s36cb242ac90353bc:"Полета",s41cb4006238ebd3b:"Групово редактиране",s5e8250fb85d64c23:"Близо",s625ad019db843f94:"Използвайте",sbf1ca928ec1deb62:"Имате нужда от повече помощ?",sd1a8dc951b2b6a98:"Изберете кои полета да се показват като колони в списъка",sf9aee319a006c9b4:"Добавяне",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Qa=Object.freeze(Object.defineProperty({__proto__:null,templates:Ja},Symbol.toStringTag,{value:"Module"})),Xa={s226be12a5b1a27e8:"নথিপত্রাদি পাঠ করুন",s33f85f24c0f5f008:"সংরক্ষণ করুন",s36cb242ac90353bc:"ক্ষেত্র",s41cb4006238ebd3b:"বাল্ক এডিট",s5e8250fb85d64c23:"বন্ধ",s625ad019db843f94:"ব্যবহার",sbf1ca928ec1deb62:"আরও সাহায্য প্রয়োজন?",sd1a8dc951b2b6a98:"তালিকার কলাম হিসাবে কোন ক্ষেত্রগুলি প্রদর্শিত হবে তা চয়ন করুন",sf9aee319a006c9b4:"অ্যাড",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Ya=Object.freeze(Object.defineProperty({__proto__:null,templates:Xa},Symbol.toStringTag,{value:"Module"})),en={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitajte dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:p`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate više pomoći?",scb9a1ff437efbd2a:p`Odaberite sve ${0} koje želite ažurirati sa liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Odaberite koja polja će se prikazati kao kolone na listi",seafe6ef133ede7da:p`Prikazuje se 1 od ${0}`,sf9aee319a006c9b4:"Dodati",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},tn=Object.freeze(Object.defineProperty({__proto__:null,templates:en},Symbol.toStringTag,{value:"Module"})),sn={s226be12a5b1a27e8:"Přečtěte si dokumentaci",s33f85f24c0f5f008:"Uložit",s36cb242ac90353bc:"Pole",s41cb4006238ebd3b:"Hromadná úprava",s5e8250fb85d64c23:"Zavřít",s625ad019db843f94:"Použití",sbf1ca928ec1deb62:"Potřebujete další pomoc?",sd1a8dc951b2b6a98:"Vyberte pole, která chcete v seznamu zobrazit jako sloupce",sf9aee319a006c9b4:"Přidat",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},on=Object.freeze(Object.defineProperty({__proto__:null,templates:sn},Symbol.toStringTag,{value:"Module"})),an={s226be12a5b1a27e8:"Lesen Sie die Dokumentation",s33f85f24c0f5f008:"Speichern",s36cb242ac90353bc:"Felder",s41cb4006238ebd3b:"Im Stapel bearbeiten",s5e8250fb85d64c23:"Schließen",s625ad019db843f94:"Verwenden",sbf1ca928ec1deb62:"Benötigen Sie weitere Hilfe?",sd1a8dc951b2b6a98:"Wählen Sie aus, welche Felder in der Liste als Spalte angezeigt werden sollen",sf9aee319a006c9b4:"Hinzufügen",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},nn=Object.freeze(Object.defineProperty({__proto__:null,templates:an},Symbol.toStringTag,{value:"Module"})),rn={s226be12a5b1a27e8:"Διαβάστε την τεκμηρίωση",s33f85f24c0f5f008:"Αποθήκευση",s36cb242ac90353bc:"Πεδία",s41cb4006238ebd3b:"Μαζική Επεξεργασία",s5e8250fb85d64c23:"Κλείσιμο",s625ad019db843f94:"Χρήση",sbf1ca928ec1deb62:"Χρειάζεστε περισσότερη βοήθεια;",sd1a8dc951b2b6a98:"Επιλέξτε ποια πεδία θα εμφανίζονται ως στήλες στη λίστα",sf9aee319a006c9b4:"Προσθήκη",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ln=Object.freeze(Object.defineProperty({__proto__:null,templates:rn},Symbol.toStringTag,{value:"Module"})),dn={sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",sf9aee319a006c9b4:"Add",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog",s33f85f24c0f5f008:"Save",s49730f3d5751a433:"Loading...",s625ad019db843f94:"Use",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},cn=Object.freeze(Object.defineProperty({__proto__:null,templates:dn},Symbol.toStringTag,{value:"Module"})),un={s8900c9de2dbae68b:"No hay opciones disponibles",sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sf9aee319a006c9b4:"Añadir",sd1a8dc951b2b6a98:"Choose which fields to display as columns in the list",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sb9b8c412407d5691:"This is where the bulk edit form will go.",sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s36cb242ac90353bc:"Fields",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading",sbf1ca928ec1deb62:"Need more help?",s226be12a5b1a27e8:"Read the documentation",s5e8250fb85d64c23:"Close",s29e25f5e4622f847:"Open Dialog"},hn=Object.freeze(Object.defineProperty({__proto__:null,templates:un},Symbol.toStringTag,{value:"Module"})),pn={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Leer la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:p`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:p`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:p`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},fn=Object.freeze(Object.defineProperty({__proto__:null,templates:pn},Symbol.toStringTag,{value:"Module"})),bn={s04ceadb276bbe149:"Cargando opciones...",s226be12a5b1a27e8:"Lee la documentación",s29e25f5e4622f847:"Abrir Diálogo",s33f85f24c0f5f008:"Guardar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edición masiva",s5e8250fb85d64c23:"Cerrar",s625ad019db843f94:"Usar",s9d51bfd93b5dbeca:"Mostrar archivado",sac83d7f9358b43db:p`${0} Lista`,sb1bd536b63e9e995:"Campo Privado: Solo yo puedo ver su contenido",sb59d68ed12d46377:"Cargando",sbf1ca928ec1deb62:"¿Necesitas más ayuda?",scb9a1ff437efbd2a:p`Selecciona todos los ${0} que quieras actualizar del listado y actualízalos debajo`,sd1a8dc951b2b6a98:"Elige qué campos mostrar como columnas en el listado",seafe6ef133ede7da:p`Mostrando 1 de ${0}`,sf9aee319a006c9b4:"Agregar",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},gn=Object.freeze(Object.defineProperty({__proto__:null,templates:bn},Symbol.toStringTag,{value:"Module"})),mn={s04ceadb276bbe149:"در حال بارگیری گزینه‌ها...",s226be12a5b1a27e8:"راهنمای سایت",s29e25f5e4622f847:"جعبه محاوره ای را باز کنید",s33f85f24c0f5f008:"صرفه جویی",s36cb242ac90353bc:"حوزه‌ها",s41cb4006238ebd3b:"ویرایش انبوه",s5e8250fb85d64c23:"بستن",s625ad019db843f94:"استفاده کنید",s9d51bfd93b5dbeca:"نمایش بایگانی شده",sac83d7f9358b43db:p`لیست ${0}`,sb1bd536b63e9e995:"زمینه خصوصی: فقط من می توانم محتوای آن را داشته باشم",sb59d68ed12d46377:"بارگیری",sbf1ca928ec1deb62:"آیا به راهنمایی بیشتری نیاز دارید؟",scb9a1ff437efbd2a:p`همۀ ${0} مورد نظر برای به روزرسانی را از لیست انتخاب کنید و آن‌ها را در زیر به روز کنید`,sd1a8dc951b2b6a98:"انتخاب کنید که کدام یک از حوزه‌ها به‌عنوان ستون در لیست نمایش داده شوند",seafe6ef133ede7da:p`نمایش 1 از ${0}`,sf9aee319a006c9b4:"افزودن",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},vn=Object.freeze(Object.defineProperty({__proto__:null,templates:mn},Symbol.toStringTag,{value:"Module"})),yn={s04ceadb276bbe149:"Chargement les options...",s226be12a5b1a27e8:"Lire la documentation",s29e25f5e4622f847:"Ouvrir la boîte de dialogue",s33f85f24c0f5f008:"sauver",s36cb242ac90353bc:"Champs",s41cb4006238ebd3b:"Modification groupée",s5e8250fb85d64c23:"Fermer",s625ad019db843f94:"Utiliser",s9d51bfd93b5dbeca:"Afficher Archivé",sac83d7f9358b43db:p`${0} Liste`,sb1bd536b63e9e995:"Champ privé : je suis le seul à voir son contenu",sb59d68ed12d46377:"Chargement",sbf1ca928ec1deb62:"Besoin d'aide ?",scb9a1ff437efbd2a:p`Sélectionnez tous les ${0} que vous souhaitez mettre à jour dans la liste et mettez-les à jour ci-dessous`,sd1a8dc951b2b6a98:"Choisissez les champs à afficher sous forme de colonnes dans la liste",seafe6ef133ede7da:p`Affichage de 1 sur ${0}`,sf9aee319a006c9b4:"Ajouter",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},wn=Object.freeze(Object.defineProperty({__proto__:null,templates:yn},Symbol.toStringTag,{value:"Module"})),_n={s226be12a5b1a27e8:"डॉक्यूमेंटेशन पढ़ें",s33f85f24c0f5f008:"बचाना",s36cb242ac90353bc:"खेत",s41cb4006238ebd3b:"थोक संपादित",s5e8250fb85d64c23:"बंद",s625ad019db843f94:"उपयोग",sbf1ca928ec1deb62:"क्या और मदद चाहिये?",sd1a8dc951b2b6a98:"सूची में कॉलम के रूप में प्रदर्शित करने के लिए कौन से फ़ील्ड चुनें",sf9aee319a006c9b4:"जोडें",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},$n=Object.freeze(Object.defineProperty({__proto__:null,templates:_n},Symbol.toStringTag,{value:"Module"})),xn={s04ceadb276bbe149:"Učitavanje opcija...",s226be12a5b1a27e8:"Pročitaj dokumentaciju",s29e25f5e4622f847:"Otvorite dijalog",s33f85f24c0f5f008:"Spremi",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Skupno uređivanje",s5e8250fb85d64c23:"Zatvoriti",s625ad019db843f94:"Koristi",s9d51bfd93b5dbeca:"Prikaži arhivirano",sac83d7f9358b43db:p`${0} Lista`,sb1bd536b63e9e995:"Privatno polje: Samo ja mogu vidjeti njegov sadržaj",sb59d68ed12d46377:"Učitavanje",sbf1ca928ec1deb62:"Trebate li pomoć?",scb9a1ff437efbd2a:p`Odaberite sve${0}koje želite ažurirati s liste i ažurirajte ih ispod`,sd1a8dc951b2b6a98:"Izaberite polja koja će se prikazivati kao stupci na popisu",seafe6ef133ede7da:p`Prikazuje se 1 od${0}`,sf9aee319a006c9b4:"Dodaj",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},kn=Object.freeze(Object.defineProperty({__proto__:null,templates:xn},Symbol.toStringTag,{value:"Module"})),Sn={s226be12a5b1a27e8:"Olvasd el a dokumentációt",s33f85f24c0f5f008:"Megment",s36cb242ac90353bc:"Mezők",s41cb4006238ebd3b:"Tömeges Szerkesztés",s5e8250fb85d64c23:"Bezár",s625ad019db843f94:"Használ",sbf1ca928ec1deb62:"Több segítség szükséges?",sd1a8dc951b2b6a98:"Válassza ki, melyik mezők jelenjenek meg oszlopként a listában",sf9aee319a006c9b4:"Hozzáadás",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},En=Object.freeze(Object.defineProperty({__proto__:null,templates:Sn},Symbol.toStringTag,{value:"Module"})),Tn={s226be12a5b1a27e8:"Bacalah dokumentasi",s33f85f24c0f5f008:"Simpan",s36cb242ac90353bc:"Larik",s41cb4006238ebd3b:"Edit Massal",s5e8250fb85d64c23:"Menutup",s625ad019db843f94:"Gunakan",sbf1ca928ec1deb62:"Perlukan bantuan lagi?",sd1a8dc951b2b6a98:"Pilih larik mana yang akan ditampilkan sebagai kolom dalam daftar",sf9aee319a006c9b4:"Tambah",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},An=Object.freeze(Object.defineProperty({__proto__:null,templates:Tn},Symbol.toStringTag,{value:"Module"})),On={s04ceadb276bbe149:"Caricando opzioni...",s226be12a5b1a27e8:"Leggi la documentazione",s29e25f5e4622f847:"Apri Dialogo",s33f85f24c0f5f008:"Salvare",s36cb242ac90353bc:"Campi",s41cb4006238ebd3b:"Modifica in blocco",s5e8250fb85d64c23:"Chiudi",s625ad019db843f94:"Uso",s9d51bfd93b5dbeca:"Visualizza Archiviati",sac83d7f9358b43db:p`${0} Lista`,sb1bd536b63e9e995:"Campo Privato: Solo io posso vedere i suoi contenuti",sb59d68ed12d46377:"Caricando",sbf1ca928ec1deb62:"Hai bisogno di ulteriore assistenza?",scb9a1ff437efbd2a:p`Seleziona tutti i ${0}vuoi aggiornare dalla lista e aggiornali sotto`,sd1a8dc951b2b6a98:"Scegli quali campi visualizzare come colonne nell'elenco",seafe6ef133ede7da:p`Visualizzando 1 di ${0}`,sf9aee319a006c9b4:"Inserisci",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},Cn=Object.freeze(Object.defineProperty({__proto__:null,templates:On},Symbol.toStringTag,{value:"Module"})),Ln={s226be12a5b1a27e8:"ドキュメントを読む",s33f85f24c0f5f008:"セーブ",s36cb242ac90353bc:"田畑",s41cb4006238ebd3b:"一括編集",s5e8250fb85d64c23:"閉じる",s625ad019db843f94:"使用する",sbf1ca928ec1deb62:"もっと助けが必要ですか？",sd1a8dc951b2b6a98:"リストの列として表示するフィールドを選択します",sf9aee319a006c9b4:"追加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},In=Object.freeze(Object.defineProperty({__proto__:null,templates:Ln},Symbol.toStringTag,{value:"Module"})),Pn={s226be12a5b1a27e8:"문서 읽기",s33f85f24c0f5f008:"구하다",s36cb242ac90353bc:"필드",s41cb4006238ebd3b:"대량 수정",s5e8250fb85d64c23:"닫기",s625ad019db843f94:"사용",sbf1ca928ec1deb62:"더 많은 도움이 필요하신가요?",sd1a8dc951b2b6a98:"목록에서 어떤 필드를 표시할지 고르세요",sf9aee319a006c9b4:"추가",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Mn=Object.freeze(Object.defineProperty({__proto__:null,templates:Pn},Symbol.toStringTag,{value:"Module"})),jn={s226be12a5b1a27e8:"Прочитај ја документацијата",s33f85f24c0f5f008:"Зачувај",s36cb242ac90353bc:"Полиња",s41cb4006238ebd3b:"Уреди повеќе",s5e8250fb85d64c23:"Затвори",s625ad019db843f94:"Користи",sbf1ca928ec1deb62:"Дали ти треба повеќе помош?",sd1a8dc951b2b6a98:"Избери кои полиња да се прикажат како колони во листата",sf9aee319a006c9b4:"Додади",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},zn=Object.freeze(Object.defineProperty({__proto__:null,templates:jn},Symbol.toStringTag,{value:"Module"})),Dn={s226be12a5b1a27e8:"कागदपत्रे वाचा.",s33f85f24c0f5f008:"जतन करा",s36cb242ac90353bc:"क्षेत्रे",s41cb4006238ebd3b:"बल्क एडिट करा",s5e8250fb85d64c23:"बंद करा",s625ad019db843f94:"वापर",sbf1ca928ec1deb62:"अधिक मदत आवश्यक आहे का?",sd1a8dc951b2b6a98:"यादीत कोणती क्षेत्रे स्तंभ म्हणून दर्शवली जावीत हे निवडा",sf9aee319a006c9b4:"समाविष्ट करा",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Fn=Object.freeze(Object.defineProperty({__proto__:null,templates:Dn},Symbol.toStringTag,{value:"Module"})),Rn={s226be12a5b1a27e8:"စာရွက်စာတမ်းများကိုဖတ်ပါ",s33f85f24c0f5f008:"သိမ်းဆည်းပါ",s36cb242ac90353bc:"နယ်ပယ်ဒေသများ",s5e8250fb85d64c23:"ပိတ်သည်",s625ad019db843f94:"အသုံးပြုပါ",sbf1ca928ec1deb62:"နောက်ထပ်အကူအညီလိုပါသလား။",sd1a8dc951b2b6a98:"စာရင်းရှိကော်လံများအနေဖြင့်ဖော်ပြမည့်မည်သည့်နယ်ပယ်ဒေသများကိုရွေးချယ်ပါ",sf9aee319a006c9b4:"ထည့်ပါ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s41cb4006238ebd3b:"Bulk Edit",s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Nn=Object.freeze(Object.defineProperty({__proto__:null,templates:Rn},Symbol.toStringTag,{value:"Module"})),qn={s226be12a5b1a27e8:"कागजात पढ्नुहोस्",s33f85f24c0f5f008:"सुरक्षित गर्नुहोस",s36cb242ac90353bc:"क्षेत्रहरू",s41cb4006238ebd3b:"थोक सम्पादन",s5e8250fb85d64c23:"बन्द गर्नुहोस",s625ad019db843f94:"प्रयोग गर्नुहोस्",sbf1ca928ec1deb62:"थप मद्दत चाहिन्छ?",sd1a8dc951b2b6a98:"सूचीमा स्तम्भहरूको रूपमा कुन क्षेत्रहरू प्रदर्शन गर्ने छनौट गर्नुहोस्",sf9aee319a006c9b4:"थप",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Un=Object.freeze(Object.defineProperty({__proto__:null,templates:qn},Symbol.toStringTag,{value:"Module"})),Vn={s04ceadb276bbe149:"aan het laden.....",s226be12a5b1a27e8:"Lees de documentatie",s29e25f5e4622f847:"Dialoogvenster openen",s33f85f24c0f5f008:"Opslaan",s36cb242ac90353bc:"Velden",s41cb4006238ebd3b:"Bulkbewerking",s5e8250fb85d64c23:"sluit",s625ad019db843f94:"Gebruiken",sac83d7f9358b43db:p`${0} Lijst`,sb1bd536b63e9e995:"Privéveld: alleen ik kan de inhoud zien",sb59d68ed12d46377:"aan het laden",sbf1ca928ec1deb62:"Meer hulp nodig?",sd1a8dc951b2b6a98:"Kies welke velden u als kolommen in de lijst wilt weergeven",seafe6ef133ede7da:p`1 van ${0} laten zien`,sf9aee319a006c9b4:"Toevoegen",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,s9d51bfd93b5dbeca:"Show Archived"},Bn=Object.freeze(Object.defineProperty({__proto__:null,templates:Vn},Symbol.toStringTag,{value:"Module"})),Hn={s226be12a5b1a27e8:"ਦਸਤਾਵੇਜ਼ ਪੜ੍ਹੋ",s33f85f24c0f5f008:"ਸੇਵ",s36cb242ac90353bc:"ਖੇਤਰ",s41cb4006238ebd3b:"ਥੋਕ ਸੰਪਾਦਨ",s5e8250fb85d64c23:"ਬੰਦ ਕਰੋ",s625ad019db843f94:"ਵਰਤੋਂ",sbf1ca928ec1deb62:"ਹੋਰ ਮਦਦ ਦੀ ਲੋੜ ਹੈ?",sd1a8dc951b2b6a98:"ਸੂਚੀ ਵਿੱਚ ਕਾਲਮ ਦੇ ਰੂਪ ਵਿੱਚ ਪ੍ਰਦਰਸ਼ਿਤ ਕਰਨ ਲਈ ਕਿਹੜੇ ਖੇਤਰ ਚੁਣੋ",sf9aee319a006c9b4:"ਸ਼ਾਮਲ ਕਰੋ",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Kn=Object.freeze(Object.defineProperty({__proto__:null,templates:Hn},Symbol.toStringTag,{value:"Module"})),Wn={s226be12a5b1a27e8:"Przeczytaj dokumentację",s33f85f24c0f5f008:"Zapisać",s36cb242ac90353bc:"Pola",s41cb4006238ebd3b:"Edycja zbiorcza",s5e8250fb85d64c23:"Zamknij",s625ad019db843f94:"Posługiwać się",sbf1ca928ec1deb62:"Potrzebujesz pomocy?",sd1a8dc951b2b6a98:"Wybierz, które pola mają być wyświetlane jako kolumny na liście",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Gn=Object.freeze(Object.defineProperty({__proto__:null,templates:Wn},Symbol.toStringTag,{value:"Module"})),Zn={s226be12a5b1a27e8:"Leia a documentação",s33f85f24c0f5f008:"Salvar",s36cb242ac90353bc:"Campos",s41cb4006238ebd3b:"Edição em massa",s5e8250fb85d64c23:"Fechar",s625ad019db843f94:"Usar",sbf1ca928ec1deb62:"Precisa de mais ajuda?",sd1a8dc951b2b6a98:"Escolha quais campos exibir como colunas na lista",sf9aee319a006c9b4:"Adicionar",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Jn=Object.freeze(Object.defineProperty({__proto__:null,templates:Zn},Symbol.toStringTag,{value:"Module"})),Qn={s226be12a5b1a27e8:"Citiți documentația",s33f85f24c0f5f008:"Salvați",s36cb242ac90353bc:"Câmpuri",s41cb4006238ebd3b:"Editare masivă",s5e8250fb85d64c23:"Închide",s625ad019db843f94:"Utilizare",sbf1ca928ec1deb62:"Ai nevoie de mai mult ajutor?",sd1a8dc951b2b6a98:"Alegeți câmpurile care să fie afișate în coloane în listă",sf9aee319a006c9b4:"Adăuga",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},Xn=Object.freeze(Object.defineProperty({__proto__:null,templates:Qn},Symbol.toStringTag,{value:"Module"})),Yn={s226be12a5b1a27e8:"Читать документацию",s33f85f24c0f5f008:"Сохранить",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Массовое редактирование",s5e8250fb85d64c23:"Закрыть",s625ad019db843f94:"Использовать",sbf1ca928ec1deb62:"Нужна дополнительная помощь?",sd1a8dc951b2b6a98:"Выберите, какие поля отображать как столбцы в списке",sf9aee319a006c9b4:"Добавить",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},er=Object.freeze(Object.defineProperty({__proto__:null,templates:Yn},Symbol.toStringTag,{value:"Module"})),tr={s226be12a5b1a27e8:"Preberite dokumentacijo",s33f85f24c0f5f008:"Shrani",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"Urejanje v velikem obsegu",s5e8250fb85d64c23:"Zapri",s625ad019db843f94:"Uporaba",sbf1ca928ec1deb62:"Potrebujete več pomoči?",sd1a8dc951b2b6a98:"Izberite, katera polja naj bodo prikazana kot stolpci na seznamu",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},ir=Object.freeze(Object.defineProperty({__proto__:null,templates:tr},Symbol.toStringTag,{value:"Module"})),sr={s226be12a5b1a27e8:"Pročitajte dokumentaciju",s33f85f24c0f5f008:"Sačuvaj",s36cb242ac90353bc:"Polja",s41cb4006238ebd3b:"masovno uređivanje",s5e8250fb85d64c23:"Zatvori",s625ad019db843f94:"Koristiti",sbf1ca928ec1deb62:"Treba vam više pomoći?",sd1a8dc951b2b6a98:"Izaberite koja polja da se prikazuju kao kolone na listi",sf9aee319a006c9b4:"Dodaj",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},or=Object.freeze(Object.defineProperty({__proto__:null,templates:sr},Symbol.toStringTag,{value:"Module"})),ar={s04ceadb276bbe149:"Inapakia chaguo...",s226be12a5b1a27e8:"Soma nyaraka",s29e25f5e4622f847:"Fungua Kidirisha",s33f85f24c0f5f008:"Hifadhi",s36cb242ac90353bc:"Mashamba",s41cb4006238ebd3b:"Hariri kwa Wingi",s5e8250fb85d64c23:"Funga",s625ad019db843f94:"Tumia",s9d51bfd93b5dbeca:"Onyesha Kumbukumbu",sac83d7f9358b43db:p`Orodha ya${0}`,sb1bd536b63e9e995:"Sehemu ya Faragha: Ni mimi pekee ninayeweza kuona maudhui yake",sb59d68ed12d46377:"Inapakia",sbf1ca928ec1deb62:"Unahitaji msaada zaidi?",scb9a1ff437efbd2a:p`Chagua ${0} zote ungependa kusasisha kutoka kwenye orodha, na uzisasishe hapa chini.`,sd1a8dc951b2b6a98:"Chagua ni sehemu zipi zitaonyeshwa kama safu wima kwenye orodha",seafe6ef133ede7da:p`Inaonyesha 1 kati ya ${0}`,sf9aee319a006c9b4:"Ongeza",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},nr=Object.freeze(Object.defineProperty({__proto__:null,templates:ar},Symbol.toStringTag,{value:"Module"})),rr={s226be12a5b1a27e8:"อ่านเอกสาร",s33f85f24c0f5f008:"บันทึก",s36cb242ac90353bc:"ฟิลด์",s41cb4006238ebd3b:"แก้ไขเป็นกลุ่ม",s5e8250fb85d64c23:"ปิด",s625ad019db843f94:"ใช้",sbf1ca928ec1deb62:"ต้องการความช่วยเหลือเพิ่มเติมหรือไม่?",sd1a8dc951b2b6a98:"เลือกฟิลด์ที่จะแสดงเป็นคอลัมน์ในรายการ",sf9aee319a006c9b4:"เพิ่ม",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},lr=Object.freeze(Object.defineProperty({__proto__:null,templates:rr},Symbol.toStringTag,{value:"Module"})),dr={s226be12a5b1a27e8:"Basahin ang dokumentasyon",s33f85f24c0f5f008:"I-save",s36cb242ac90353bc:"Mga Field",s41cb4006238ebd3b:"Maramihang Pag-edit",s5e8250fb85d64c23:"Isara",s625ad019db843f94:"Gamitin",sbf1ca928ec1deb62:"Kailangan mo pa ba ng tulong?",sd1a8dc951b2b6a98:"Piliin kung aling mga field ang ipapakita bilang mga column sa listahan",sf9aee319a006c9b4:"Idagdag",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},cr=Object.freeze(Object.defineProperty({__proto__:null,templates:dr},Symbol.toStringTag,{value:"Module"})),ur={s04ceadb276bbe149:"Seçenekler Yükleniyor...",s226be12a5b1a27e8:"Belgeleri oku",s29e25f5e4622f847:"İletişim Kutusunu Aç",s33f85f24c0f5f008:"Kaydet",s36cb242ac90353bc:"Alanlar",s41cb4006238ebd3b:"Toplu Düzenleme",s5e8250fb85d64c23:"Kapat",s625ad019db843f94:"Kullan",s9d51bfd93b5dbeca:"Arşivlenmiş Göster",sac83d7f9358b43db:p`${0} Listesi`,sb1bd536b63e9e995:"Özel Alan: İçeriğini sadece ben görebilirim",sb59d68ed12d46377:"Yükleniyor",sbf1ca928ec1deb62:"Daha fazla yardıma ihtiyacınız var mı?",scb9a1ff437efbd2a:p`Listeden güncellemek istediğiniz tüm ${0} 'i seçin ve aşağıda güncelleyin`,sd1a8dc951b2b6a98:"Listede Hangi Alanların Sütun Olarak Görüntüleneceğini Seçin",seafe6ef133ede7da:p`Gösteriliyor 1 of ${0}`,sf9aee319a006c9b4:"Ekle",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},hr=Object.freeze(Object.defineProperty({__proto__:null,templates:ur},Symbol.toStringTag,{value:"Module"})),pr={s226be12a5b1a27e8:"Прочитайте документацію",s33f85f24c0f5f008:"Зберегти",s36cb242ac90353bc:"Поля",s41cb4006238ebd3b:"Масове редагування",s5e8250fb85d64c23:"Закрити",s625ad019db843f94:"Використати",sbf1ca928ec1deb62:"Потрібна додаткова допомога?",sd1a8dc951b2b6a98:"Виберіть, яке поле відображати у вигляді стовпців у списку",sf9aee319a006c9b4:"Додати",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},fr=Object.freeze(Object.defineProperty({__proto__:null,templates:pr},Symbol.toStringTag,{value:"Module"})),br={s226be12a5b1a27e8:"Đọc tài liệu",s33f85f24c0f5f008:"Lưu",s36cb242ac90353bc:"Trường",s41cb4006238ebd3b:"Chỉnh sửa Hàng loạt",s5e8250fb85d64c23:"Đóng",s625ad019db843f94:"Sử dụng",sbf1ca928ec1deb62:"Bạn cần trợ giúp thêm?",sd1a8dc951b2b6a98:"Chọn các trường để hiển thị dưới dạng cột trong danh sách",sf9aee319a006c9b4:"Bổ sung",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},gr=Object.freeze(Object.defineProperty({__proto__:null,templates:br},Symbol.toStringTag,{value:"Module"})),mr={s226be12a5b1a27e8:"阅读文档",s33f85f24c0f5f008:"保存",s36cb242ac90353bc:"字段",s41cb4006238ebd3b:"批量编辑",s5e8250fb85d64c23:"关",s625ad019db843f94:"使用",sbf1ca928ec1deb62:"需要更多帮助吗？",sd1a8dc951b2b6a98:"选择哪些字段要在列表中显示为列",sf9aee319a006c9b4:"添加",sb1bd536b63e9e995:"Private Field: Only I can see its content",s04ceadb276bbe149:"Loading options...",sd2e180dab4fbcfb9:"No Data Available",s29e25f5e4622f847:"Open Dialog",s49730f3d5751a433:"Loading...",scb9a1ff437efbd2a:p`Select all the ${0} you want to update from the list, and update them below`,sac83d7f9358b43db:p`${0} List`,seafe6ef133ede7da:p`Showing 1 of ${0}`,s9d51bfd93b5dbeca:"Show Archived",sb59d68ed12d46377:"Loading"},vr=Object.freeze(Object.defineProperty({__proto__:null,templates:mr},Symbol.toStringTag,{value:"Module"})),yr={s04ceadb276bbe149:"正在載入選項...",s226be12a5b1a27e8:"閱讀文檔",s29e25f5e4622f847:"開啟對話視窗",s33f85f24c0f5f008:"儲存",s36cb242ac90353bc:"欄位",s41cb4006238ebd3b:"大量編輯",s5e8250fb85d64c23:"關",s625ad019db843f94:"使用",s9d51bfd93b5dbeca:"顯示已儲存",sac83d7f9358b43db:p`${0} 清單`,sb1bd536b63e9e995:"私人欄位：只有我可以看見內容",sb59d68ed12d46377:"載入中",sbf1ca928ec1deb62:"需要更多幫助嗎？",scb9a1ff437efbd2a:p`從清單中選取要更新的項目${0}，並在下面進行更新`,sd1a8dc951b2b6a98:"選擇哪些欄位要顯示為列表中的直行",seafe6ef133ede7da:p`第1頁 （共${0}頁）`,sf9aee319a006c9b4:"新增",sd2e180dab4fbcfb9:"No Data Available",s49730f3d5751a433:"Loading..."},wr=Object.freeze(Object.defineProperty({__proto__:null,templates:yr},Symbol.toStringTag,{value:"Module"}));_.ApiService=vt,_.ComponentService=Ie,_.DtAlert=Es,_.DtBase=N,_.DtButton=wi,_.DtChurchHealthCircle=us,_.DtConnection=hs,_.DtCopyText=fs,_.DtDate=Dt,_.DtDatetime=bs,_.DtFormBase=D,_.DtIcon=ns,_.DtLabel=rs,_.DtList=Ts,_.DtLocation=gs,_.DtLocationMap=ys,_.DtMapModal=vs,_.DtModal=ms,_.DtMultiSelect=Ze,_.DtMultiSelectButtonGroup=ks,_.DtMultiText=xs,_.DtNumberField=ws,_.DtSingleSelect=_s,_.DtTags=Le,_.DtText=Ft,_.DtTextArea=$s,_.DtTile=As,_.DtToggle=cs,_.DtUploadFile=Ss,_.DtUsersConnection=ps,_.version=Va,Object.defineProperty(_,Symbol.toStringTag,{value:"Module"})});
