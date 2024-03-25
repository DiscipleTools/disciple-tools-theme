import{x as t}from"./lit-element-2409d5fe.js";import{e,i as s,t as i}from"./directive-de55b00a.js";
/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const n=e(class extends s{constructor(t){var e;if(super(t),t.type!==i.ATTRIBUTE||"class"!==t.name||(null===(e=t.strings)||void 0===e?void 0:e.length)>2)throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.")}render(t){return" "+Object.keys(t).filter((e=>t[e])).join(" ")+" "}update(e,[s]){var i,n;if(void 0===this.nt){this.nt=new Set,void 0!==e.strings&&(this.st=new Set(e.strings.join(" ").split(/\s/).filter((t=>""!==t))));for(const t in s)s[t]&&!(null===(i=this.st)||void 0===i?void 0:i.has(t))&&this.nt.add(t);return this.render(s)}const r=e.element.classList;this.nt.forEach((t=>{t in s||(r.remove(t),this.nt.delete(t))}));for(const t in s){const e=!!s[t];e===this.nt.has(t)||(null===(n=this.st)||void 0===n?void 0:n.has(t))||(e?(r.add(t),this.nt.add(t)):(r.remove(t),this.nt.delete(t)))}return t}});export{n as o};
