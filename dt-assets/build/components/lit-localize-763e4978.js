/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const e=(e,...t)=>({strTag:!0,strings:e,values:t}),t=(e,t,r)=>{let s=e[0];for(let o=1;o<e.length;o++)s+=t[r?r[o-1]:o-1],s+=e[o];return s},r=e=>{return"string"!=typeof(r=e)&&"strTag"in r?t(e.strings,e.values):e;var r},s="lit-localize-status";
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
class o{constructor(){this.settled=!1,this.promise=new Promise(((e,t)=>{this._resolve=e,this._reject=t}))}resolve(e){this.settled=!0,this._resolve(e)}reject(e){this.settled=!0,this._reject(e)}}
/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */const n=[];for(let e=0;e<256;e++)n[e]=(e>>4&15).toString(16)+(15&e).toString(16);function l(e,t){return(t?"h":"s")+function(e){let t=0,r=8997,s=0,o=33826,l=0,i=40164,a=0,c=52210;for(let n=0;n<e.length;n++)r^=e.charCodeAt(n),t=435*r,s=435*o,l=435*i,a=435*c,l+=r<<8,a+=o<<8,s+=t>>>16,r=65535&t,l+=s>>>16,o=65535&s,c=a+(l>>>16)&65535,i=65535&l;return n[c>>8]+n[255&c]+n[i>>8]+n[255&i]+n[o>>8]+n[255&o]+n[r>>8]+n[255&r]}
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */("string"==typeof e?e:e.join(""))}
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const i=new WeakMap,a=new Map;function c(e,s,o){var n;if(e){const r=null!==(n=null==o?void 0:o.id)&&void 0!==n?n:function(e){const t="string"==typeof e?e:e.strings;let r=a.get(t);void 0===r&&(r=l(t,"string"!=typeof e&&!("strTag"in e)),a.set(t,r));return r}
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */(s),c=e[r];if(c){if("string"==typeof c)return c;if("strTag"in c)return t(c.strings,s.values,c.values);{let e=i.get(c);return void 0===e&&(e=c.values,i.set(c,e)),{...c,values:e.map((e=>s.values[e]))}}}}return r(s)}function u(e){window.dispatchEvent(new CustomEvent("lit-localize-status",{detail:e}))}let g,d,v,f,h,p="",w=new o;w.resolve();let m=0;const L=e=>(function(e){if(S)throw new Error("lit-localize can only be configured once");E=e,S=!0}(((e,t)=>c(h,e,t))),p=d=e.sourceLocale,v=new Set(e.targetLocales),v.add(e.sourceLocale),f=e.loadLocale,{getLocale:y,setLocale:j}),y=()=>p,j=e=>{if(e===(null!=g?g:p))return w.promise;if(!v||!f)throw new Error("Internal error");if(!v.has(e))throw new Error("Invalid locale code");m++;const t=m;g=e,w.settled&&(w=new o),u({status:"loading",loadingLocale:e});return(e===d?Promise.resolve({templates:void 0}):f(e)).then((r=>{m===t&&(p=e,g=void 0,h=r.templates,u({status:"ready",readyLocale:e}),w.resolve())}),(r=>{m===t&&(u({status:"error",errorLocale:e,errorMessage:r.toString()}),w.reject(r))})),w.promise};
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
let E=r,S=!1;export{s as L,L as c,E as m,e as s};
