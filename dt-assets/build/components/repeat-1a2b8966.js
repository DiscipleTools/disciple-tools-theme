import{L as e,x as t}from"./lit-element-2409d5fe.js";import{e as n,i as s,t as o}from"./directive-de55b00a.js";
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{I:l}=e,r=()=>document.createComment(""),i=(e,t,n)=>{var s;const o=e._$AA.parentNode,i=void 0===t?e._$AB:t._$AA;if(void 0===n){const t=o.insertBefore(r(),i),s=o.insertBefore(r(),i);n=new l(t,s,e,e.options)}else{const t=n._$AB.nextSibling,l=n._$AM,r=l!==e;if(r){let t;null===(s=n._$AQ)||void 0===s||s.call(n,e),n._$AM=e,void 0!==n._$AP&&(t=e._$AU)!==l._$AU&&n._$AP(t)}if(t!==i||r){let e=n._$AA;for(;e!==t;){const t=e.nextSibling;o.insertBefore(e,i),e=t}}}return n},f=(e,t,n=e)=>(e._$AI(t,n),e),u={},c=e=>{var t;null===(t=e._$AP)||void 0===t||t.call(e,!1,!0);let n=e._$AA;const s=e._$AB.nextSibling;for(;n!==s;){const e=n.nextSibling;n.remove(),n=e}},A=(e,t,n)=>{const s=new Map;for(let o=t;o<=n;o++)s.set(e[o],o);return s},a=n(class extends s{constructor(e){if(super(e),e.type!==o.CHILD)throw Error("repeat() can only be used in text expressions")}ht(e,t,n){let s;void 0===n?n=t:void 0!==t&&(s=t);const o=[],l=[];let r=0;for(const t of e)o[r]=s?s(t,r):r,l[r]=n(t,r),r++;return{values:l,keys:o}}render(e,t,n){return this.ht(e,t,n).values}update(e,[n,s,o]){var l;const r=e._$AH,{values:a,keys:d}=this.ht(n,s,o);if(!Array.isArray(r))return this.ut=d,a;const v=null!==(l=this.ut)&&void 0!==l?l:this.ut=[],$=[];let _,h,p=0,m=r.length-1,x=0,g=a.length-1;for(;p<=m&&x<=g;)if(null===r[p])p++;else if(null===r[m])m--;else if(v[p]===d[x])$[x]=f(r[p],a[x]),p++,x++;else if(v[m]===d[g])$[g]=f(r[m],a[g]),m--,g--;else if(v[p]===d[g])$[g]=f(r[p],a[g]),i(e,$[g+1],r[p]),p++,g--;else if(v[m]===d[x])$[x]=f(r[m],a[x]),i(e,r[p],r[m]),m--,x++;else if(void 0===_&&(_=A(d,x,g),h=A(v,p,m)),_.has(v[p]))if(_.has(v[m])){const t=h.get(d[x]),n=void 0!==t?r[t]:null;if(null===n){const t=i(e,r[p]);f(t,a[x]),$[x]=t}else $[x]=f(n,a[x]),i(e,r[p],n),r[t]=null;x++}else c(r[m]),m--;else c(r[p]),p++;for(;x<=g;){const t=i(e,$[g+1]);f(t,a[x]),$[x++]=t}for(;p<=m;){const e=r[p++];null!==e&&c(e)}return this.ut=d,((e,t=u)=>{e._$AH=t})(e,$),t}});
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */export{a as c};
//# sourceMappingURL=repeat-1a2b8966.js.map
