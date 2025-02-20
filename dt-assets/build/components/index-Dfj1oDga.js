var Io = Object.defineProperty;
var Lo = (s, t, e) => t in s ? Io(s, t, { enumerable: !0, configurable: !0, writable: !0, value: e }) : s[t] = e;
var Ot = (s, t, e) => Lo(s, typeof t != "symbol" ? t + "" : t, e);
/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const Mt = globalThis, ke = Mt.ShadowRoot && (Mt.ShadyCSS === void 0 || Mt.ShadyCSS.nativeShadow) && "adoptedStyleSheets" in Document.prototype && "replace" in CSSStyleSheet.prototype, Mi = Symbol(), Je = /* @__PURE__ */ new WeakMap();
let Mo = class {
  constructor(t, e, i) {
    if (this._$cssResult$ = !0, i !== Mi) throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");
    this.cssText = t, this.t = e;
  }
  get styleSheet() {
    let t = this.o;
    const e = this.t;
    if (ke && t === void 0) {
      const i = e !== void 0 && e.length === 1;
      i && (t = Je.get(e)), t === void 0 && ((this.o = t = new CSSStyleSheet()).replaceSync(this.cssText), i && Je.set(e, t));
    }
    return t;
  }
  toString() {
    return this.cssText;
  }
};
const Po = (s) => new Mo(typeof s == "string" ? s : s + "", void 0, Mi), Ro = (s, t) => {
  if (ke) s.adoptedStyleSheets = t.map((e) => e instanceof CSSStyleSheet ? e : e.styleSheet);
  else for (const e of t) {
    const i = document.createElement("style"), o = Mt.litNonce;
    o !== void 0 && i.setAttribute("nonce", o), i.textContent = e.cssText, s.appendChild(i);
  }
}, Qe = ke ? (s) => s : (s) => s instanceof CSSStyleSheet ? ((t) => {
  let e = "";
  for (const i of t.cssRules) e += i.cssText;
  return Po(e);
})(s) : s;
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const { is: No, defineProperty: jo, getOwnPropertyDescriptor: qo, getOwnPropertyNames: Uo, getOwnPropertySymbols: Bo, getPrototypeOf: Fo } = Object, G = globalThis, Xe = G.trustedTypes, Do = Xe ? Xe.emptyScript : "", Yt = G.reactiveElementPolyfillSupport, ft = (s, t) => s, he = { toAttribute(s, t) {
  switch (t) {
    case Boolean:
      s = s ? Do : null;
      break;
    case Object:
    case Array:
      s = s == null ? s : JSON.stringify(s);
  }
  return s;
}, fromAttribute(s, t) {
  let e = s;
  switch (t) {
    case Boolean:
      e = s !== null;
      break;
    case Number:
      e = s === null ? null : Number(s);
      break;
    case Object:
    case Array:
      try {
        e = JSON.parse(s);
      } catch {
        e = null;
      }
  }
  return e;
} }, Pi = (s, t) => !No(s, t), Ye = { attribute: !0, type: String, converter: he, reflect: !1, hasChanged: Pi };
Symbol.metadata ?? (Symbol.metadata = Symbol("metadata")), G.litPropertyMetadata ?? (G.litPropertyMetadata = /* @__PURE__ */ new WeakMap());
let dt = class extends HTMLElement {
  static addInitializer(t) {
    this._$Ei(), (this.l ?? (this.l = [])).push(t);
  }
  static get observedAttributes() {
    return this.finalize(), this._$Eh && [...this._$Eh.keys()];
  }
  static createProperty(t, e = Ye) {
    if (e.state && (e.attribute = !1), this._$Ei(), this.elementProperties.set(t, e), !e.noAccessor) {
      const i = Symbol(), o = this.getPropertyDescriptor(t, i, e);
      o !== void 0 && jo(this.prototype, t, o);
    }
  }
  static getPropertyDescriptor(t, e, i) {
    const { get: o, set: r } = qo(this.prototype, t) ?? { get() {
      return this[e];
    }, set(n) {
      this[e] = n;
    } };
    return { get() {
      return o == null ? void 0 : o.call(this);
    }, set(n) {
      const a = o == null ? void 0 : o.call(this);
      r.call(this, n), this.requestUpdate(t, a, i);
    }, configurable: !0, enumerable: !0 };
  }
  static getPropertyOptions(t) {
    return this.elementProperties.get(t) ?? Ye;
  }
  static _$Ei() {
    if (this.hasOwnProperty(ft("elementProperties"))) return;
    const t = Fo(this);
    t.finalize(), t.l !== void 0 && (this.l = [...t.l]), this.elementProperties = new Map(t.elementProperties);
  }
  static finalize() {
    if (this.hasOwnProperty(ft("finalized"))) return;
    if (this.finalized = !0, this._$Ei(), this.hasOwnProperty(ft("properties"))) {
      const e = this.properties, i = [...Uo(e), ...Bo(e)];
      for (const o of i) this.createProperty(o, e[o]);
    }
    const t = this[Symbol.metadata];
    if (t !== null) {
      const e = litPropertyMetadata.get(t);
      if (e !== void 0) for (const [i, o] of e) this.elementProperties.set(i, o);
    }
    this._$Eh = /* @__PURE__ */ new Map();
    for (const [e, i] of this.elementProperties) {
      const o = this._$Eu(e, i);
      o !== void 0 && this._$Eh.set(o, e);
    }
    this.elementStyles = this.finalizeStyles(this.styles);
  }
  static finalizeStyles(t) {
    const e = [];
    if (Array.isArray(t)) {
      const i = new Set(t.flat(1 / 0).reverse());
      for (const o of i) e.unshift(Qe(o));
    } else t !== void 0 && e.push(Qe(t));
    return e;
  }
  static _$Eu(t, e) {
    const i = e.attribute;
    return i === !1 ? void 0 : typeof i == "string" ? i : typeof t == "string" ? t.toLowerCase() : void 0;
  }
  constructor() {
    super(), this._$Ep = void 0, this.isUpdatePending = !1, this.hasUpdated = !1, this._$Em = null, this._$Ev();
  }
  _$Ev() {
    var t;
    this._$ES = new Promise((e) => this.enableUpdating = e), this._$AL = /* @__PURE__ */ new Map(), this._$E_(), this.requestUpdate(), (t = this.constructor.l) == null || t.forEach((e) => e(this));
  }
  addController(t) {
    var e;
    (this._$EO ?? (this._$EO = /* @__PURE__ */ new Set())).add(t), this.renderRoot !== void 0 && this.isConnected && ((e = t.hostConnected) == null || e.call(t));
  }
  removeController(t) {
    var e;
    (e = this._$EO) == null || e.delete(t);
  }
  _$E_() {
    const t = /* @__PURE__ */ new Map(), e = this.constructor.elementProperties;
    for (const i of e.keys()) this.hasOwnProperty(i) && (t.set(i, this[i]), delete this[i]);
    t.size > 0 && (this._$Ep = t);
  }
  createRenderRoot() {
    const t = this.shadowRoot ?? this.attachShadow(this.constructor.shadowRootOptions);
    return Ro(t, this.constructor.elementStyles), t;
  }
  connectedCallback() {
    var t;
    this.renderRoot ?? (this.renderRoot = this.createRenderRoot()), this.enableUpdating(!0), (t = this._$EO) == null || t.forEach((e) => {
      var i;
      return (i = e.hostConnected) == null ? void 0 : i.call(e);
    });
  }
  enableUpdating(t) {
  }
  disconnectedCallback() {
    var t;
    (t = this._$EO) == null || t.forEach((e) => {
      var i;
      return (i = e.hostDisconnected) == null ? void 0 : i.call(e);
    });
  }
  attributeChangedCallback(t, e, i) {
    this._$AK(t, i);
  }
  _$EC(t, e) {
    var r;
    const i = this.constructor.elementProperties.get(t), o = this.constructor._$Eu(t, i);
    if (o !== void 0 && i.reflect === !0) {
      const n = (((r = i.converter) == null ? void 0 : r.toAttribute) !== void 0 ? i.converter : he).toAttribute(e, i.type);
      this._$Em = t, n == null ? this.removeAttribute(o) : this.setAttribute(o, n), this._$Em = null;
    }
  }
  _$AK(t, e) {
    var r;
    const i = this.constructor, o = i._$Eh.get(t);
    if (o !== void 0 && this._$Em !== o) {
      const n = i.getPropertyOptions(o), a = typeof n.converter == "function" ? { fromAttribute: n.converter } : ((r = n.converter) == null ? void 0 : r.fromAttribute) !== void 0 ? n.converter : he;
      this._$Em = o, this[o] = a.fromAttribute(e, n.type), this._$Em = null;
    }
  }
  requestUpdate(t, e, i) {
    if (t !== void 0) {
      if (i ?? (i = this.constructor.getPropertyOptions(t)), !(i.hasChanged ?? Pi)(this[t], e)) return;
      this.P(t, e, i);
    }
    this.isUpdatePending === !1 && (this._$ES = this._$ET());
  }
  P(t, e, i) {
    this._$AL.has(t) || this._$AL.set(t, e), i.reflect === !0 && this._$Em !== t && (this._$Ej ?? (this._$Ej = /* @__PURE__ */ new Set())).add(t);
  }
  async _$ET() {
    this.isUpdatePending = !0;
    try {
      await this._$ES;
    } catch (e) {
      Promise.reject(e);
    }
    const t = this.scheduleUpdate();
    return t != null && await t, !this.isUpdatePending;
  }
  scheduleUpdate() {
    return this.performUpdate();
  }
  performUpdate() {
    var i;
    if (!this.isUpdatePending) return;
    if (!this.hasUpdated) {
      if (this.renderRoot ?? (this.renderRoot = this.createRenderRoot()), this._$Ep) {
        for (const [r, n] of this._$Ep) this[r] = n;
        this._$Ep = void 0;
      }
      const o = this.constructor.elementProperties;
      if (o.size > 0) for (const [r, n] of o) n.wrapped !== !0 || this._$AL.has(r) || this[r] === void 0 || this.P(r, this[r], n);
    }
    let t = !1;
    const e = this._$AL;
    try {
      t = this.shouldUpdate(e), t ? (this.willUpdate(e), (i = this._$EO) == null || i.forEach((o) => {
        var r;
        return (r = o.hostUpdate) == null ? void 0 : r.call(o);
      }), this.update(e)) : this._$EU();
    } catch (o) {
      throw t = !1, this._$EU(), o;
    }
    t && this._$AE(e);
  }
  willUpdate(t) {
  }
  _$AE(t) {
    var e;
    (e = this._$EO) == null || e.forEach((i) => {
      var o;
      return (o = i.hostUpdated) == null ? void 0 : o.call(i);
    }), this.hasUpdated || (this.hasUpdated = !0, this.firstUpdated(t)), this.updated(t);
  }
  _$EU() {
    this._$AL = /* @__PURE__ */ new Map(), this.isUpdatePending = !1;
  }
  get updateComplete() {
    return this.getUpdateComplete();
  }
  getUpdateComplete() {
    return this._$ES;
  }
  shouldUpdate(t) {
    return !0;
  }
  update(t) {
    this._$Ej && (this._$Ej = this._$Ej.forEach((e) => this._$EC(e, this[e]))), this._$EU();
  }
  updated(t) {
  }
  firstUpdated(t) {
  }
};
dt.elementStyles = [], dt.shadowRootOptions = { mode: "open" }, dt[ft("elementProperties")] = /* @__PURE__ */ new Map(), dt[ft("finalized")] = /* @__PURE__ */ new Map(), Yt == null || Yt({ ReactiveElement: dt }), (G.reactiveElementVersions ?? (G.reactiveElementVersions = [])).push("2.0.4");
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const gt = globalThis, qt = gt.trustedTypes, ti = qt ? qt.createPolicy("lit-html", { createHTML: (s) => s }) : void 0, Ri = "$lit$", W = `lit$${Math.random().toFixed(9).slice(2)}$`, Ni = "?" + W, Vo = `<${Ni}>`, ot = document, vt = () => ot.createComment(""), yt = (s) => s === null || typeof s != "object" && typeof s != "function", Se = Array.isArray, zo = (s) => Se(s) || typeof (s == null ? void 0 : s[Symbol.iterator]) == "function", te = `[ 	
\f\r]`, ut = /<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g, ei = /-->/g, ii = />/g, X = RegExp(`>|${te}(?:([^\\s"'>=/]+)(${te}*=${te}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`, "g"), oi = /'/g, si = /"/g, ji = /^(?:script|style|textarea|title)$/i, Ho = (s) => (t, ...e) => ({ _$litType$: s, strings: t, values: e }), d = Ho(1), V = Symbol.for("lit-noChange"), E = Symbol.for("lit-nothing"), ri = /* @__PURE__ */ new WeakMap(), et = ot.createTreeWalker(ot, 129);
function qi(s, t) {
  if (!Se(s) || !s.hasOwnProperty("raw")) throw Error("invalid template strings array");
  return ti !== void 0 ? ti.createHTML(t) : t;
}
const Wo = (s, t) => {
  const e = s.length - 1, i = [];
  let o, r = t === 2 ? "<svg>" : t === 3 ? "<math>" : "", n = ut;
  for (let a = 0; a < e; a++) {
    const l = s[a];
    let u, f, g = -1, b = 0;
    for (; b < l.length && (n.lastIndex = b, f = n.exec(l), f !== null); ) b = n.lastIndex, n === ut ? f[1] === "!--" ? n = ei : f[1] !== void 0 ? n = ii : f[2] !== void 0 ? (ji.test(f[2]) && (o = RegExp("</" + f[2], "g")), n = X) : f[3] !== void 0 && (n = X) : n === X ? f[0] === ">" ? (n = o ?? ut, g = -1) : f[1] === void 0 ? g = -2 : (g = n.lastIndex - f[2].length, u = f[1], n = f[3] === void 0 ? X : f[3] === '"' ? si : oi) : n === si || n === oi ? n = X : n === ei || n === ii ? n = ut : (n = X, o = void 0);
    const v = n === X && s[a + 1].startsWith("/>") ? " " : "";
    r += n === ut ? l + Vo : g >= 0 ? (i.push(u), l.slice(0, g) + Ri + l.slice(g) + W + v) : l + W + (g === -2 ? a : v);
  }
  return [qi(s, r + (s[e] || "<?>") + (t === 2 ? "</svg>" : t === 3 ? "</math>" : "")), i];
};
class wt {
  constructor({ strings: t, _$litType$: e }, i) {
    let o;
    this.parts = [];
    let r = 0, n = 0;
    const a = t.length - 1, l = this.parts, [u, f] = Wo(t, e);
    if (this.el = wt.createElement(u, i), et.currentNode = this.el.content, e === 2 || e === 3) {
      const g = this.el.content.firstChild;
      g.replaceWith(...g.childNodes);
    }
    for (; (o = et.nextNode()) !== null && l.length < a; ) {
      if (o.nodeType === 1) {
        if (o.hasAttributes()) for (const g of o.getAttributeNames()) if (g.endsWith(Ri)) {
          const b = f[n++], v = o.getAttribute(g).split(W), w = /([.?@])?(.*)/.exec(b);
          l.push({ type: 1, index: r, name: w[2], strings: v, ctor: w[1] === "." ? Ko : w[1] === "?" ? Zo : w[1] === "@" ? Jo : zt }), o.removeAttribute(g);
        } else g.startsWith(W) && (l.push({ type: 6, index: r }), o.removeAttribute(g));
        if (ji.test(o.tagName)) {
          const g = o.textContent.split(W), b = g.length - 1;
          if (b > 0) {
            o.textContent = qt ? qt.emptyScript : "";
            for (let v = 0; v < b; v++) o.append(g[v], vt()), et.nextNode(), l.push({ type: 2, index: ++r });
            o.append(g[b], vt());
          }
        }
      } else if (o.nodeType === 8) if (o.data === Ni) l.push({ type: 2, index: r });
      else {
        let g = -1;
        for (; (g = o.data.indexOf(W, g + 1)) !== -1; ) l.push({ type: 7, index: r }), g += W.length - 1;
      }
      r++;
    }
  }
  static createElement(t, e) {
    const i = ot.createElement("template");
    return i.innerHTML = t, i;
  }
}
function nt(s, t, e = s, i) {
  var n, a;
  if (t === V) return t;
  let o = i !== void 0 ? (n = e._$Co) == null ? void 0 : n[i] : e._$Cl;
  const r = yt(t) ? void 0 : t._$litDirective$;
  return (o == null ? void 0 : o.constructor) !== r && ((a = o == null ? void 0 : o._$AO) == null || a.call(o, !1), r === void 0 ? o = void 0 : (o = new r(s), o._$AT(s, e, i)), i !== void 0 ? (e._$Co ?? (e._$Co = []))[i] = o : e._$Cl = o), o !== void 0 && (t = nt(s, o._$AS(s, t.values), o, i)), t;
}
let Go = class {
  constructor(t, e) {
    this._$AV = [], this._$AN = void 0, this._$AD = t, this._$AM = e;
  }
  get parentNode() {
    return this._$AM.parentNode;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  u(t) {
    const { el: { content: e }, parts: i } = this._$AD, o = ((t == null ? void 0 : t.creationScope) ?? ot).importNode(e, !0);
    et.currentNode = o;
    let r = et.nextNode(), n = 0, a = 0, l = i[0];
    for (; l !== void 0; ) {
      if (n === l.index) {
        let u;
        l.type === 2 ? u = new at(r, r.nextSibling, this, t) : l.type === 1 ? u = new l.ctor(r, l.name, l.strings, this, t) : l.type === 6 && (u = new Qo(r, this, t)), this._$AV.push(u), l = i[++a];
      }
      n !== (l == null ? void 0 : l.index) && (r = et.nextNode(), n++);
    }
    return et.currentNode = ot, o;
  }
  p(t) {
    let e = 0;
    for (const i of this._$AV) i !== void 0 && (i.strings !== void 0 ? (i._$AI(t, i, e), e += i.strings.length - 2) : i._$AI(t[e])), e++;
  }
};
class at {
  get _$AU() {
    var t;
    return ((t = this._$AM) == null ? void 0 : t._$AU) ?? this._$Cv;
  }
  constructor(t, e, i, o) {
    this.type = 2, this._$AH = E, this._$AN = void 0, this._$AA = t, this._$AB = e, this._$AM = i, this.options = o, this._$Cv = (o == null ? void 0 : o.isConnected) ?? !0;
  }
  get parentNode() {
    let t = this._$AA.parentNode;
    const e = this._$AM;
    return e !== void 0 && (t == null ? void 0 : t.nodeType) === 11 && (t = e.parentNode), t;
  }
  get startNode() {
    return this._$AA;
  }
  get endNode() {
    return this._$AB;
  }
  _$AI(t, e = this) {
    t = nt(this, t, e), yt(t) ? t === E || t == null || t === "" ? (this._$AH !== E && this._$AR(), this._$AH = E) : t !== this._$AH && t !== V && this._(t) : t._$litType$ !== void 0 ? this.$(t) : t.nodeType !== void 0 ? this.T(t) : zo(t) ? this.k(t) : this._(t);
  }
  O(t) {
    return this._$AA.parentNode.insertBefore(t, this._$AB);
  }
  T(t) {
    this._$AH !== t && (this._$AR(), this._$AH = this.O(t));
  }
  _(t) {
    this._$AH !== E && yt(this._$AH) ? this._$AA.nextSibling.data = t : this.T(ot.createTextNode(t)), this._$AH = t;
  }
  $(t) {
    var r;
    const { values: e, _$litType$: i } = t, o = typeof i == "number" ? this._$AC(t) : (i.el === void 0 && (i.el = wt.createElement(qi(i.h, i.h[0]), this.options)), i);
    if (((r = this._$AH) == null ? void 0 : r._$AD) === o) this._$AH.p(e);
    else {
      const n = new Go(o, this), a = n.u(this.options);
      n.p(e), this.T(a), this._$AH = n;
    }
  }
  _$AC(t) {
    let e = ri.get(t.strings);
    return e === void 0 && ri.set(t.strings, e = new wt(t)), e;
  }
  k(t) {
    Se(this._$AH) || (this._$AH = [], this._$AR());
    const e = this._$AH;
    let i, o = 0;
    for (const r of t) o === e.length ? e.push(i = new at(this.O(vt()), this.O(vt()), this, this.options)) : i = e[o], i._$AI(r), o++;
    o < e.length && (this._$AR(i && i._$AB.nextSibling, o), e.length = o);
  }
  _$AR(t = this._$AA.nextSibling, e) {
    var i;
    for ((i = this._$AP) == null ? void 0 : i.call(this, !1, !0, e); t && t !== this._$AB; ) {
      const o = t.nextSibling;
      t.remove(), t = o;
    }
  }
  setConnected(t) {
    var e;
    this._$AM === void 0 && (this._$Cv = t, (e = this._$AP) == null || e.call(this, t));
  }
}
class zt {
  get tagName() {
    return this.element.tagName;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  constructor(t, e, i, o, r) {
    this.type = 1, this._$AH = E, this._$AN = void 0, this.element = t, this.name = e, this._$AM = o, this.options = r, i.length > 2 || i[0] !== "" || i[1] !== "" ? (this._$AH = Array(i.length - 1).fill(new String()), this.strings = i) : this._$AH = E;
  }
  _$AI(t, e = this, i, o) {
    const r = this.strings;
    let n = !1;
    if (r === void 0) t = nt(this, t, e, 0), n = !yt(t) || t !== this._$AH && t !== V, n && (this._$AH = t);
    else {
      const a = t;
      let l, u;
      for (t = r[0], l = 0; l < r.length - 1; l++) u = nt(this, a[i + l], e, l), u === V && (u = this._$AH[l]), n || (n = !yt(u) || u !== this._$AH[l]), u === E ? t = E : t !== E && (t += (u ?? "") + r[l + 1]), this._$AH[l] = u;
    }
    n && !o && this.j(t);
  }
  j(t) {
    t === E ? this.element.removeAttribute(this.name) : this.element.setAttribute(this.name, t ?? "");
  }
}
class Ko extends zt {
  constructor() {
    super(...arguments), this.type = 3;
  }
  j(t) {
    this.element[this.name] = t === E ? void 0 : t;
  }
}
class Zo extends zt {
  constructor() {
    super(...arguments), this.type = 4;
  }
  j(t) {
    this.element.toggleAttribute(this.name, !!t && t !== E);
  }
}
class Jo extends zt {
  constructor(t, e, i, o, r) {
    super(t, e, i, o, r), this.type = 5;
  }
  _$AI(t, e = this) {
    if ((t = nt(this, t, e, 0) ?? E) === V) return;
    const i = this._$AH, o = t === E && i !== E || t.capture !== i.capture || t.once !== i.once || t.passive !== i.passive, r = t !== E && (i === E || o);
    o && this.element.removeEventListener(this.name, this, i), r && this.element.addEventListener(this.name, this, t), this._$AH = t;
  }
  handleEvent(t) {
    var e;
    typeof this._$AH == "function" ? this._$AH.call(((e = this.options) == null ? void 0 : e.host) ?? this.element, t) : this._$AH.handleEvent(t);
  }
}
class Qo {
  constructor(t, e, i) {
    this.element = t, this.type = 6, this._$AN = void 0, this._$AM = e, this.options = i;
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  _$AI(t) {
    nt(this, t);
  }
}
const Xo = { I: at }, ee = gt.litHtmlPolyfillSupport;
ee == null || ee(wt, at), (gt.litHtmlVersions ?? (gt.litHtmlVersions = [])).push("3.2.1");
const Yo = (s, t, e) => {
  const i = (e == null ? void 0 : e.renderBefore) ?? t;
  let o = i._$litPart$;
  if (o === void 0) {
    const r = (e == null ? void 0 : e.renderBefore) ?? null;
    i._$litPart$ = o = new at(t.insertBefore(vt(), r), r, void 0, e ?? {});
  }
  return o._$AI(s), o;
};
/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const Pt = globalThis, Ee = Pt.ShadowRoot && (Pt.ShadyCSS === void 0 || Pt.ShadyCSS.nativeShadow) && "adoptedStyleSheets" in Document.prototype && "replace" in CSSStyleSheet.prototype, Ce = Symbol(), ni = /* @__PURE__ */ new WeakMap();
let Ui = class {
  constructor(t, e, i) {
    if (this._$cssResult$ = !0, i !== Ce) throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");
    this.cssText = t, this.t = e;
  }
  get styleSheet() {
    let t = this.o;
    const e = this.t;
    if (Ee && t === void 0) {
      const i = e !== void 0 && e.length === 1;
      i && (t = ni.get(e)), t === void 0 && ((this.o = t = new CSSStyleSheet()).replaceSync(this.cssText), i && ni.set(e, t));
    }
    return t;
  }
  toString() {
    return this.cssText;
  }
};
const ts = (s) => new Ui(typeof s == "string" ? s : s + "", void 0, Ce), $ = (s, ...t) => {
  const e = s.length === 1 ? s[0] : t.reduce((i, o, r) => i + ((n) => {
    if (n._$cssResult$ === !0) return n.cssText;
    if (typeof n == "number") return n;
    throw Error("Value passed to 'css' function must be a 'css' function result: " + n + ". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.");
  })(o) + s[r + 1], s[0]);
  return new Ui(e, s, Ce);
}, es = (s, t) => {
  if (Ee) s.adoptedStyleSheets = t.map((e) => e instanceof CSSStyleSheet ? e : e.styleSheet);
  else for (const e of t) {
    const i = document.createElement("style"), o = Pt.litNonce;
    o !== void 0 && i.setAttribute("nonce", o), i.textContent = e.cssText, s.appendChild(i);
  }
}, ai = Ee ? (s) => s : (s) => s instanceof CSSStyleSheet ? ((t) => {
  let e = "";
  for (const i of t.cssRules) e += i.cssText;
  return ts(e);
})(s) : s;
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const { is, defineProperty: os, getOwnPropertyDescriptor: ss, getOwnPropertyNames: rs, getOwnPropertySymbols: ns, getPrototypeOf: as } = Object, K = globalThis, li = K.trustedTypes, ls = li ? li.emptyScript : "", ie = K.reactiveElementPolyfillSupport, mt = (s, t) => s, pe = { toAttribute(s, t) {
  switch (t) {
    case Boolean:
      s = s ? ls : null;
      break;
    case Object:
    case Array:
      s = s == null ? s : JSON.stringify(s);
  }
  return s;
}, fromAttribute(s, t) {
  let e = s;
  switch (t) {
    case Boolean:
      e = s !== null;
      break;
    case Number:
      e = s === null ? null : Number(s);
      break;
    case Object:
    case Array:
      try {
        e = JSON.parse(s);
      } catch {
        e = null;
      }
  }
  return e;
} }, Bi = (s, t) => !is(s, t), ci = { attribute: !0, type: String, converter: pe, reflect: !1, hasChanged: Bi };
Symbol.metadata ?? (Symbol.metadata = Symbol("metadata")), K.litPropertyMetadata ?? (K.litPropertyMetadata = /* @__PURE__ */ new WeakMap());
class st extends HTMLElement {
  static addInitializer(t) {
    this._$Ei(), (this.l ?? (this.l = [])).push(t);
  }
  static get observedAttributes() {
    return this.finalize(), this._$Eh && [...this._$Eh.keys()];
  }
  static createProperty(t, e = ci) {
    if (e.state && (e.attribute = !1), this._$Ei(), this.elementProperties.set(t, e), !e.noAccessor) {
      const i = Symbol(), o = this.getPropertyDescriptor(t, i, e);
      o !== void 0 && os(this.prototype, t, o);
    }
  }
  static getPropertyDescriptor(t, e, i) {
    const { get: o, set: r } = ss(this.prototype, t) ?? { get() {
      return this[e];
    }, set(n) {
      this[e] = n;
    } };
    return { get() {
      return o == null ? void 0 : o.call(this);
    }, set(n) {
      const a = o == null ? void 0 : o.call(this);
      r.call(this, n), this.requestUpdate(t, a, i);
    }, configurable: !0, enumerable: !0 };
  }
  static getPropertyOptions(t) {
    return this.elementProperties.get(t) ?? ci;
  }
  static _$Ei() {
    if (this.hasOwnProperty(mt("elementProperties"))) return;
    const t = as(this);
    t.finalize(), t.l !== void 0 && (this.l = [...t.l]), this.elementProperties = new Map(t.elementProperties);
  }
  static finalize() {
    if (this.hasOwnProperty(mt("finalized"))) return;
    if (this.finalized = !0, this._$Ei(), this.hasOwnProperty(mt("properties"))) {
      const e = this.properties, i = [...rs(e), ...ns(e)];
      for (const o of i) this.createProperty(o, e[o]);
    }
    const t = this[Symbol.metadata];
    if (t !== null) {
      const e = litPropertyMetadata.get(t);
      if (e !== void 0) for (const [i, o] of e) this.elementProperties.set(i, o);
    }
    this._$Eh = /* @__PURE__ */ new Map();
    for (const [e, i] of this.elementProperties) {
      const o = this._$Eu(e, i);
      o !== void 0 && this._$Eh.set(o, e);
    }
    this.elementStyles = this.finalizeStyles(this.styles);
  }
  static finalizeStyles(t) {
    const e = [];
    if (Array.isArray(t)) {
      const i = new Set(t.flat(1 / 0).reverse());
      for (const o of i) e.unshift(ai(o));
    } else t !== void 0 && e.push(ai(t));
    return e;
  }
  static _$Eu(t, e) {
    const i = e.attribute;
    return i === !1 ? void 0 : typeof i == "string" ? i : typeof t == "string" ? t.toLowerCase() : void 0;
  }
  constructor() {
    super(), this._$Ep = void 0, this.isUpdatePending = !1, this.hasUpdated = !1, this._$Em = null, this._$Ev();
  }
  _$Ev() {
    var t;
    this._$ES = new Promise((e) => this.enableUpdating = e), this._$AL = /* @__PURE__ */ new Map(), this._$E_(), this.requestUpdate(), (t = this.constructor.l) == null || t.forEach((e) => e(this));
  }
  addController(t) {
    var e;
    (this._$EO ?? (this._$EO = /* @__PURE__ */ new Set())).add(t), this.renderRoot !== void 0 && this.isConnected && ((e = t.hostConnected) == null || e.call(t));
  }
  removeController(t) {
    var e;
    (e = this._$EO) == null || e.delete(t);
  }
  _$E_() {
    const t = /* @__PURE__ */ new Map(), e = this.constructor.elementProperties;
    for (const i of e.keys()) this.hasOwnProperty(i) && (t.set(i, this[i]), delete this[i]);
    t.size > 0 && (this._$Ep = t);
  }
  createRenderRoot() {
    const t = this.shadowRoot ?? this.attachShadow(this.constructor.shadowRootOptions);
    return es(t, this.constructor.elementStyles), t;
  }
  connectedCallback() {
    var t;
    this.renderRoot ?? (this.renderRoot = this.createRenderRoot()), this.enableUpdating(!0), (t = this._$EO) == null || t.forEach((e) => {
      var i;
      return (i = e.hostConnected) == null ? void 0 : i.call(e);
    });
  }
  enableUpdating(t) {
  }
  disconnectedCallback() {
    var t;
    (t = this._$EO) == null || t.forEach((e) => {
      var i;
      return (i = e.hostDisconnected) == null ? void 0 : i.call(e);
    });
  }
  attributeChangedCallback(t, e, i) {
    this._$AK(t, i);
  }
  _$EC(t, e) {
    var r;
    const i = this.constructor.elementProperties.get(t), o = this.constructor._$Eu(t, i);
    if (o !== void 0 && i.reflect === !0) {
      const n = (((r = i.converter) == null ? void 0 : r.toAttribute) !== void 0 ? i.converter : pe).toAttribute(e, i.type);
      this._$Em = t, n == null ? this.removeAttribute(o) : this.setAttribute(o, n), this._$Em = null;
    }
  }
  _$AK(t, e) {
    var r;
    const i = this.constructor, o = i._$Eh.get(t);
    if (o !== void 0 && this._$Em !== o) {
      const n = i.getPropertyOptions(o), a = typeof n.converter == "function" ? { fromAttribute: n.converter } : ((r = n.converter) == null ? void 0 : r.fromAttribute) !== void 0 ? n.converter : pe;
      this._$Em = o, this[o] = a.fromAttribute(e, n.type), this._$Em = null;
    }
  }
  requestUpdate(t, e, i) {
    if (t !== void 0) {
      if (i ?? (i = this.constructor.getPropertyOptions(t)), !(i.hasChanged ?? Bi)(this[t], e)) return;
      this.P(t, e, i);
    }
    this.isUpdatePending === !1 && (this._$ES = this._$ET());
  }
  P(t, e, i) {
    this._$AL.has(t) || this._$AL.set(t, e), i.reflect === !0 && this._$Em !== t && (this._$Ej ?? (this._$Ej = /* @__PURE__ */ new Set())).add(t);
  }
  async _$ET() {
    this.isUpdatePending = !0;
    try {
      await this._$ES;
    } catch (e) {
      Promise.reject(e);
    }
    const t = this.scheduleUpdate();
    return t != null && await t, !this.isUpdatePending;
  }
  scheduleUpdate() {
    return this.performUpdate();
  }
  performUpdate() {
    var i;
    if (!this.isUpdatePending) return;
    if (!this.hasUpdated) {
      if (this.renderRoot ?? (this.renderRoot = this.createRenderRoot()), this._$Ep) {
        for (const [r, n] of this._$Ep) this[r] = n;
        this._$Ep = void 0;
      }
      const o = this.constructor.elementProperties;
      if (o.size > 0) for (const [r, n] of o) n.wrapped !== !0 || this._$AL.has(r) || this[r] === void 0 || this.P(r, this[r], n);
    }
    let t = !1;
    const e = this._$AL;
    try {
      t = this.shouldUpdate(e), t ? (this.willUpdate(e), (i = this._$EO) == null || i.forEach((o) => {
        var r;
        return (r = o.hostUpdate) == null ? void 0 : r.call(o);
      }), this.update(e)) : this._$EU();
    } catch (o) {
      throw t = !1, this._$EU(), o;
    }
    t && this._$AE(e);
  }
  willUpdate(t) {
  }
  _$AE(t) {
    var e;
    (e = this._$EO) == null || e.forEach((i) => {
      var o;
      return (o = i.hostUpdated) == null ? void 0 : o.call(i);
    }), this.hasUpdated || (this.hasUpdated = !0, this.firstUpdated(t)), this.updated(t);
  }
  _$EU() {
    this._$AL = /* @__PURE__ */ new Map(), this.isUpdatePending = !1;
  }
  get updateComplete() {
    return this.getUpdateComplete();
  }
  getUpdateComplete() {
    return this._$ES;
  }
  shouldUpdate(t) {
    return !0;
  }
  update(t) {
    this._$Ej && (this._$Ej = this._$Ej.forEach((e) => this._$EC(e, this[e]))), this._$EU();
  }
  updated(t) {
  }
  firstUpdated(t) {
  }
}
st.elementStyles = [], st.shadowRootOptions = { mode: "open" }, st[mt("elementProperties")] = /* @__PURE__ */ new Map(), st[mt("finalized")] = /* @__PURE__ */ new Map(), ie == null || ie({ ReactiveElement: st }), (K.reactiveElementVersions ?? (K.reactiveElementVersions = [])).push("2.0.4");
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
let U = class extends st {
  constructor() {
    super(...arguments), this.renderOptions = { host: this }, this._$Do = void 0;
  }
  createRenderRoot() {
    var e;
    const t = super.createRenderRoot();
    return (e = this.renderOptions).renderBefore ?? (e.renderBefore = t.firstChild), t;
  }
  update(t) {
    const e = this.render();
    this.hasUpdated || (this.renderOptions.isConnected = this.isConnected), super.update(t), this._$Do = Yo(e, this.renderRoot, this.renderOptions);
  }
  connectedCallback() {
    var t;
    super.connectedCallback(), (t = this._$Do) == null || t.setConnected(!0);
  }
  disconnectedCallback() {
    var t;
    super.disconnectedCallback(), (t = this._$Do) == null || t.setConnected(!1);
  }
  render() {
    return V;
  }
};
var Li;
U._$litElement$ = !0, U.finalized = !0, (Li = globalThis.litElementHydrateSupport) == null || Li.call(globalThis, { LitElement: U });
const oe = globalThis.litElementPolyfillSupport;
oe == null || oe({ LitElement: U });
(globalThis.litElementVersions ?? (globalThis.litElementVersions = [])).push("4.1.1");
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const Te = { ATTRIBUTE: 1, CHILD: 2 }, Ae = (s) => (...t) => ({ _$litDirective$: s, values: t });
let Oe = class {
  constructor(t) {
  }
  get _$AU() {
    return this._$AM._$AU;
  }
  _$AT(t, e, i) {
    this._$Ct = t, this._$AM = e, this._$Ci = i;
  }
  _$AS(t, e) {
    return this.update(t, e);
  }
  update(t, e) {
    return this.render(...e);
  }
};
/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const N = Ae(class extends Oe {
  constructor(s) {
    var t;
    if (super(s), s.type !== Te.ATTRIBUTE || s.name !== "class" || ((t = s.strings) == null ? void 0 : t.length) > 2) throw Error("`classMap()` can only be used in the `class` attribute and must be the only part in the attribute.");
  }
  render(s) {
    return " " + Object.keys(s).filter((t) => s[t]).join(" ") + " ";
  }
  update(s, [t]) {
    var i, o;
    if (this.st === void 0) {
      this.st = /* @__PURE__ */ new Set(), s.strings !== void 0 && (this.nt = new Set(s.strings.join(" ").split(/\s/).filter((r) => r !== "")));
      for (const r in t) t[r] && !((i = this.nt) != null && i.has(r)) && this.st.add(r);
      return this.render(t);
    }
    const e = s.element.classList;
    for (const r of this.st) r in t || (e.remove(r), this.st.delete(r));
    for (const r in t) {
      const n = !!t[r];
      n === this.st.has(r) || (o = this.nt) != null && o.has(r) || (n ? (e.add(r), this.st.add(r)) : (e.remove(r), this.st.delete(r)));
    }
    return V;
  }
});
/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const Fi = "important", cs = " !" + Fi, Z = Ae(class extends Oe {
  constructor(s) {
    var t;
    if (super(s), s.type !== Te.ATTRIBUTE || s.name !== "style" || ((t = s.strings) == null ? void 0 : t.length) > 2) throw Error("The `styleMap` directive must be used in the `style` attribute and must be the only part in the attribute.");
  }
  render(s) {
    return Object.keys(s).reduce((t, e) => {
      const i = s[e];
      return i == null ? t : t + `${e = e.includes("-") ? e : e.replace(/(?:^(webkit|moz|ms|o)|)(?=[A-Z])/g, "-$&").toLowerCase()}:${i};`;
    }, "");
  }
  update(s, [t]) {
    const { style: e } = s.element;
    if (this.ft === void 0) return this.ft = new Set(Object.keys(t)), this.render(t);
    for (const i of this.ft) t[i] == null && (this.ft.delete(i), i.includes("-") ? e.removeProperty(i) : e[i] = null);
    for (const i in t) {
      const o = t[i];
      if (o != null) {
        this.ft.add(i);
        const r = typeof o == "string" && o.endsWith(cs);
        i.includes("-") || r ? e.setProperty(i, r ? o.slice(0, -11) : o, r ? Fi : "") : e[i] = o;
      }
    }
    return V;
  }
});
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const fe = "lit-localize-status";
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const ds = (s, ...t) => ({
  strTag: !0,
  strings: s,
  values: t
}), se = ds, us = (s) => typeof s != "string" && "strTag" in s, Di = (s, t, e) => {
  let i = s[0];
  for (let o = 1; o < s.length; o++)
    i += t[e ? e[o - 1] : o - 1], i += s[o];
  return i;
};
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const Vi = (s) => us(s) ? Di(s.strings, s.values) : s;
let S = Vi, di = !1;
function hs(s) {
  if (di)
    throw new Error("lit-localize can only be configured once");
  S = s, di = !0;
}
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
class ps {
  constructor(t) {
    this.__litLocalizeEventHandler = (e) => {
      e.detail.status === "ready" && this.host.requestUpdate();
    }, this.host = t;
  }
  hostConnected() {
    window.addEventListener(fe, this.__litLocalizeEventHandler);
  }
  hostDisconnected() {
    window.removeEventListener(fe, this.__litLocalizeEventHandler);
  }
}
const fs = (s) => s.addController(new ps(s)), gs = fs;
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
class zi {
  constructor() {
    this.settled = !1, this.promise = new Promise((t, e) => {
      this._resolve = t, this._reject = e;
    });
  }
  resolve(t) {
    this.settled = !0, this._resolve(t);
  }
  reject(t) {
    this.settled = !0, this._reject(t);
  }
}
/**
 * @license
 * Copyright 2014 Travis Webb
 * SPDX-License-Identifier: MIT
 */
const D = [];
for (let s = 0; s < 256; s++)
  D[s] = (s >> 4 & 15).toString(16) + (s & 15).toString(16);
function ms(s) {
  let t = 0, e = 8997, i = 0, o = 33826, r = 0, n = 40164, a = 0, l = 52210;
  for (let u = 0; u < s.length; u++)
    e ^= s.charCodeAt(u), t = e * 435, i = o * 435, r = n * 435, a = l * 435, r += e << 8, a += o << 8, i += t >>> 16, e = t & 65535, r += i >>> 16, o = i & 65535, l = a + (r >>> 16) & 65535, n = r & 65535;
  return D[l >> 8] + D[l & 255] + D[n >> 8] + D[n & 255] + D[o >> 8] + D[o & 255] + D[e >> 8] + D[e & 255];
}
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const bs = "", vs = "h", ys = "s";
function ws(s, t) {
  return (t ? vs : ys) + ms(typeof s == "string" ? s : s.join(bs));
}
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const ui = /* @__PURE__ */ new WeakMap(), hi = /* @__PURE__ */ new Map();
function xs(s, t, e) {
  if (s) {
    const i = (e == null ? void 0 : e.id) ?? _s(t), o = s[i];
    if (o) {
      if (typeof o == "string")
        return o;
      if ("strTag" in o)
        return Di(
          o.strings,
          // Cast `template` because its type wasn't automatically narrowed (but
          // we know it must be the same type as `localized`).
          t.values,
          o.values
        );
      {
        let r = ui.get(o);
        return r === void 0 && (r = o.values, ui.set(o, r)), {
          ...o,
          values: r.map((n) => t.values[n])
        };
      }
    }
  }
  return Vi(t);
}
function _s(s) {
  const t = typeof s == "string" ? s : s.strings;
  let e = hi.get(t);
  return e === void 0 && (e = ws(t, typeof s != "string" && !("strTag" in s)), hi.set(t, e)), e;
}
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
function re(s) {
  window.dispatchEvent(new CustomEvent(fe, { detail: s }));
}
let Ut = "", ne, Hi, Bt, ge, Wi, tt = new zi();
tt.resolve();
let It = 0;
const $s = (s) => (hs((t, e) => xs(Wi, t, e)), Ut = Hi = s.sourceLocale, Bt = new Set(s.targetLocales), Bt.add(s.sourceLocale), ge = s.loadLocale, { getLocale: ks, setLocale: Ss }), ks = () => Ut, Ss = (s) => {
  if (s === (ne ?? Ut))
    return tt.promise;
  if (!Bt || !ge)
    throw new Error("Internal error");
  if (!Bt.has(s))
    throw new Error("Invalid locale code");
  It++;
  const t = It;
  return ne = s, tt.settled && (tt = new zi()), re({ status: "loading", loadingLocale: s }), (s === Hi ? (
    // We could switch to the source locale synchronously, but we prefer to
    // queue it on a microtask so that switching locales is consistently
    // asynchronous.
    Promise.resolve({ templates: void 0 })
  ) : ge(s)).then((i) => {
    It === t && (Ut = s, ne = void 0, Wi = i.templates, re({ status: "ready", readyLocale: s }), tt.resolve());
  }, (i) => {
    It === t && (re({
      status: "error",
      errorLocale: s,
      errorMessage: i.toString()
    }), tt.reject(i));
  }), tt.promise;
}, Es = (s, t, e) => {
  const i = s[t];
  return i ? typeof i == "function" ? i() : Promise.resolve(i) : new Promise((o, r) => {
    (typeof queueMicrotask == "function" ? queueMicrotask : setTimeout)(
      r.bind(
        null,
        new Error(
          "Unknown variable dynamic import: " + t + (t.split("/").length !== e ? ". Note that variables only represent file names one level deep." : "")
        )
      )
    );
  });
}, Cs = "en", Ts = [
  "am_ET",
  "ar",
  "ar_MA",
  "bg_BG",
  "bn_BD",
  "bs_BA",
  "cs",
  "de_DE",
  "el",
  "en_US",
  "es_419",
  "es_ES",
  "fa_IR",
  "fr_FR",
  "hi_IN",
  "hr",
  "hu_HU",
  "id_ID",
  "it_IT",
  "ja",
  "ko_KR",
  "mk_MK",
  "mr",
  "my_MM",
  "ne_NP",
  "nl_NL",
  "pa_IN",
  "pl",
  "pt_BR",
  "ro_RO",
  "ru_RU",
  "sl_SI",
  "sr_BA",
  "sw",
  "th",
  "tl",
  "tr_TR",
  "uk",
  "vi",
  "zh_CN",
  "zh_TW"
], { setLocale: As } = $s({
  sourceLocale: Cs,
  targetLocales: Ts,
  loadLocale: (s) => Es(/* @__PURE__ */ Object.assign({ "./generated/am_ET.js": () => import("./am_ET-yrasUBKh.js"), "./generated/ar.js": () => import("./ar-VPnJfgrL.js"), "./generated/ar_MA.js": () => import("./ar_MA-D7YFIGdt.js"), "./generated/bg_BG.js": () => import("./bg_BG-Dy8b3wcF.js"), "./generated/bn_BD.js": () => import("./bn_BD-C0dym5Ji.js"), "./generated/bs_BA.js": () => import("./bs_BA-CTvZFcp_.js"), "./generated/cs.js": () => import("./cs-CVtJbURQ.js"), "./generated/de_DE.js": () => import("./de_DE-BvGROHLp.js"), "./generated/el.js": () => import("./el-B_sVP62M.js"), "./generated/en_US.js": () => import("./en_US-DdtmvNPA.js"), "./generated/es-419.js": () => import("./es-419-D3Oc6SSm.js"), "./generated/es_419.js": () => import("./es_419-BNX0AMiD.js"), "./generated/es_ES.js": () => import("./es_ES-B4O1hKzS.js"), "./generated/fa_IR.js": () => import("./fa_IR-CnikeBiu.js"), "./generated/fr_FR.js": () => import("./fr_FR-D-UF6Vxx.js"), "./generated/hi_IN.js": () => import("./hi_IN-CLwz0Fa3.js"), "./generated/hr.js": () => import("./hr-yQjTvMuJ.js"), "./generated/hu_HU.js": () => import("./hu_HU-By-r76rd.js"), "./generated/id_ID.js": () => import("./id_ID-b20aRpPc.js"), "./generated/it_IT.js": () => import("./it_IT-SuP7RfWK.js"), "./generated/ja.js": () => import("./ja-DZTMLAbU.js"), "./generated/ko_KR.js": () => import("./ko_KR-d2xrOG4u.js"), "./generated/mk_MK.js": () => import("./mk_MK-DhFIZsaN.js"), "./generated/mr.js": () => import("./mr-D2HECp0s.js"), "./generated/my_MM.js": () => import("./my_MM-zqTjjLNf.js"), "./generated/ne_NP.js": () => import("./ne_NP-Ulw8iIUJ.js"), "./generated/nl_NL.js": () => import("./nl_NL-B-ZFUDsH.js"), "./generated/pa_IN.js": () => import("./pa_IN-mwA_SBKM.js"), "./generated/pl.js": () => import("./pl-C2PdoI5z.js"), "./generated/pt_BR.js": () => import("./pt_BR-Bh8b0Kpk.js"), "./generated/ro_RO.js": () => import("./ro_RO-CnH0VIzK.js"), "./generated/ru_RU.js": () => import("./ru_RU-D3dUVqmL.js"), "./generated/sl_SI.js": () => import("./sl_SI-DUIUvM0O.js"), "./generated/sr_BA.js": () => import("./sr_BA-Dk9VOrlj.js"), "./generated/sw.js": () => import("./sw-CLFIw3mQ.js"), "./generated/th.js": () => import("./th-rlC1eem4.js"), "./generated/tl.js": () => import("./tl-CLmrDFnh.js"), "./generated/tr_TR.js": () => import("./tr_TR-DPRc6RlR.js"), "./generated/uk.js": () => import("./uk-He7vRM3g.js"), "./generated/vi.js": () => import("./vi-Rs_cbZNe.js"), "./generated/zh_CN.js": () => import("./zh_CN-sYV9EEvW.js"), "./generated/zh_TW.js": () => import("./zh_TW-D8kcVOM5.js") }), `./generated/${s}.js`, 3)
});
class Ie {
  /**
   * @param nonce - WordPress nonce for authentication
   * @param apiRoot - Root of API (default: wp-json) (i.e. the part before dt/v1/ or dt-posts/v2/)
   */
  constructor(t, e = "/wp-json") {
    this.nonce = t, this.apiRoot = e.endsWith("/") ? `${e}` : `${e} + "/"`, this.apiRoot = `/${e}/`.replace(/\/\//g, "/");
  }
  /**
   * Send request to server
   * @param {string} type HTTP Method
   * @param {string} url Either full URL to API endpoint or just the URL segment after base
   * @param {Object} data Post data to send in body of request
   * @param {string} base Base of URL endpoint. Defaults to "dt/v1/"
   * @returns {Promise<any>}
   */
  async makeRequest(t, e, i, o = "dt/v1/") {
    let r = o;
    !r.endsWith("/") && !e.startsWith("/") && (r += "/");
    const n = e.startsWith("http") ? e : `${this.apiRoot}${r}${e}`, a = {
      method: t,
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": this.nonce
      }
    };
    t !== "GET" && (a.body = JSON.stringify(i));
    const l = await fetch(n, a), u = await l.json();
    if (!l.ok) {
      const f = new Error((u == null ? void 0 : u.message) || u.toString());
      throw f.args = {
        status: l.status,
        statusText: l.statusText,
        body: u
      }, f;
    }
    return u;
  }
  /**
   * Send request to server for /dt-posts/v2/
   * @param {string} type HTTP Method
   * @param {string} url Either full URL to API endpoint or just the URL segment after base
   * @param {Object} data Post data to send in body of request
   * @returns {Promise<any>}
   */
  async makeRequestOnPosts(t, e, i = {}) {
    return this.makeRequest(t, e, i, "dt-posts/v2/");
  }
  // region Posts
  /**
   * Get Post from API
   * @param {string} postType
   * @param {number} postId
   * @returns {Promise<any>}
   */
  async getPost(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}`);
  }
  /**
   * Create Post via API
   * @param {string} postType
   * @param {Object} fields
   * @returns {Promise<any>}
   */
  async createPost(t, e) {
    return this.makeRequestOnPosts("POST", t, e);
  }
  /**
  * Fetch contacts list via API
  * @param {string} postType
  * @param {Object} data This would be payload to be send while hitting API
  * @returns {Promise<any>}
  */
  async fetchPostsList(t, e) {
    return this.makeRequestOnPosts("POST", `${t}/list`, e);
  }
  /**
   * Update Post via API
   * @param {string} postType
   * @param {number} postId
   * @param {Object} data Post data to be updated
   * @returns {Promise<any>}
   */
  async updatePost(t, e, i) {
    return this.makeRequestOnPosts("POST", `${t}/${e}`, i);
  }
  /**
   * Delete Post via API
   * @param {string} postType
   * @param {number} postId
   * @returns {Promise<any>}
   */
  async deletePost(t, e) {
    return this.makeRequestOnPosts("DELETE", `${t}/${e}`);
  }
  /**
   * Get compact list of posts for autocomplete fields
   * @param {string} postType
   * @param {string} query - the string to filter the list to. Or the id of the target record
   * @returns {Promise<any>}
   * @see https://developers.disciple.tools/theme-core/api-posts/list-posts-compact
   */
  async listPostsCompact(t, e = "") {
    const i = new URLSearchParams({
      s: e
    });
    return this.makeRequestOnPosts(
      "GET",
      `${t}/compact?${i}`
    );
  }
  /**
   * Get duplicates for a post
   * @param {string} postType
   * @param {number} postId
   * @param {Object} args
   * @returns {Promise<any>}
   */
  async getPostDuplicates(t, e, i) {
    return this.makeRequestOnPosts(
      "GET",
      `${t}/${e}/all_duplicates`,
      i
    );
  }
  async checkFieldValueExists(t, e) {
    return this.makeRequestOnPosts(
      "POST",
      `${t}/check_field_value_exists`,
      e
    );
  }
  /**
   * Get values for a multi_select field
   * @param {string} postType
   * @param {string} field
   * @param {string} query - Search Query
   * @returns {Promise<any>}
   */
  async getMultiSelectValues(t, e, i = "") {
    const o = new URLSearchParams({
      s: i,
      field: e
    });
    return this.makeRequestOnPosts(
      "GET",
      `${t}/multi-select-values?${o}`
    );
  }
  /**
   * Transfer contact to another site
   * @param {number} contactId
   * @param {string} siteId
   * @returns {Promise<any>}
   */
  async transferContact(t, e) {
    return this.makeRequestOnPosts("POST", "contacts/transfer", {
      contact_id: t,
      site_post_id: e
    });
  }
  /**
   * Transfer contact summary update
   * @param {number} contactId
   * @param {Object} update
   * @returns {Promise<any>}
   */
  async transferContactSummaryUpdate(t, e) {
    return this.makeRequestOnPosts(
      "POST",
      "contacts/transfer/summary/send-update",
      {
        contact_id: t,
        update: e
      }
    );
  }
  /**
   * Request access to post
   * @param {string} postType
   * @param {number} postId
   * @param {number} userId
   * @returns {Promise<any>}
   */
  async requestRecordAccess(t, e, i) {
    return this.makeRequestOnPosts(
      "POST",
      `${t}/${e}/request_record_access`,
      {
        user_id: i
      }
    );
  }
  // endregion
  // region Comments
  /**
   * Create comment on post via API
   * @param {string} postType
   * @param {number} postId
   * @param {string} comment Text of comment
   * @param {string} commentType Type of comment
   * @returns {Promise<any>}
   */
  async createComment(t, e, i, o = "comment") {
    return this.makeRequestOnPosts("POST", `${t}/${e}/comments`, {
      comment: i,
      comment_type: o
    });
  }
  /**
   * Update post comment via API
   * @param {string} postType
   * @param {number} postId
   * @param {number} commentId
   * @param {string} commentContent
   * @param {string} commentType
   * @returns {Promise<any>}
   */
  async updateComment(t, e, i, o, r = "comment") {
    return this.makeRequestOnPosts(
      "POST",
      `${t}/${e}/comments/${i}`,
      {
        comment: o,
        comment_type: r
      }
    );
  }
  /**
   * Delete post comment via API
   * @param {string} postType
   * @param {number} postId
   * @param {number} commentId
   * @returns {Promise<any>}
   */
  async deleteComment(t, e, i) {
    return this.makeRequestOnPosts(
      "DELETE",
      `${t}/${e}/comments/${i}`
    );
  }
  /**
   * Get post comments via API
   * @param {string} postType
   * @param {number} postId
   * @returns {Promise<any>}
   */
  async getComments(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}/comments`);
  }
  /**
   * Toggle post comment reaction
   * @param {string} postType
   * @param {number} postId
   * @param {number} commentId
   * @param {number} userId
   * @param {string} reaction
   * @returns {Promise<any>}
   */
  async toggle_comment_reaction(t, e, i, o, r) {
    return this.makeRequestOnPosts(
      "POST",
      `${t}/${e}/comments/${i}/react`,
      {
        user_id: o,
        reaction: r
      }
    );
  }
  // endregion
  // region Activity
  /**
   * Get all activity for a post
   * @param {string} postType
   * @param {number} postId
   * @returns {Promise<any>}
   */
  async getPostActivity(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}/activity`);
  }
  /**
   * Get single activity for a post
   * @param {string} postType
   * @param {number} postId
   * @param {number} activityId
   * @returns {Promise<any>}
   */
  async getSingleActivity(t, e, i) {
    return this.makeRequestOnPosts(
      "GET",
      `${t}/${e}/activity/${i}`
    );
  }
  /**
   * Revert post activity
   * @param {string} postType
   * @param {number} postId
   * @param {number} activityId
   * @returns {Promise<any>}
   */
  async revertActivity(t, e, i) {
    return this.makeRequestOnPosts(
      "GET",
      `${t}/${e}/revert/${i}`
    );
  }
  // endregion
  // region Shares
  /**
   * Get all share for a post
   * @param {string} postType
   * @param {number} postId
   * @returns {Promise<any>}
   */
  async getPostShares(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}/shares`);
  }
  /**
   * Share a post with a user
   * @param {string} postType
   * @param {number} postId
   * @param {number} userId
   * @returns {Promise<any>}
   */
  async addPostShare(t, e, i) {
    return this.makeRequestOnPosts("POST", `${t}/${e}/shares`, {
      user_id: i
    });
  }
  /**
   * Un-share a post with a user
   * @param {string} postType
   * @param {number} postId
   * @param {number} userId
   * @returns {Promise<any>}
   */
  async removePostShare(t, e, i) {
    return this.makeRequestOnPosts("DELETE", `${t}/${e}/shares`, {
      user_id: i
    });
  }
  // endregion
  // region Filters
  /**
   * Get Filters
   * @returns {Promise<any>}
   */
  async getFilters() {
    return this.makeRequest("GET", "users/get_filters");
  }
  /**
   * Save filters
   * @param {string} postType
   * @param {Object} filter
   * @returns {Promise<any>}
   */
  async saveFilters(t, e) {
    return this.makeRequest("POST", "users/save_filters", { filter: e, postType: t });
  }
  /**
   * Delete filter
   * @param {string} postType
   * @param {number} id
   * @returns {Promise<void>}
   */
  async deleteFilter(t, e) {
    return this.makeRequest("DELETE", "users/save_filters", { id: e, postType: t });
  }
  // endregion
  // region Users
  /**
   * Search users
   * @param {string} query
   * @returns {Promise<any>}
   */
  async searchUsers(t = "", e) {
    const i = new URLSearchParams({
      s: t
    });
    return this.makeRequest("GET", `users/get_users?${i}&post_type=${e}`);
  }
  // Duplicate Users
  async checkDuplicateUsers(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}/duplicates`);
  }
  async getContactInfo(t, e) {
    return this.makeRequestOnPosts("GET", `${t}/${e}/`);
  }
  /**
   * Create user
   * @param {Object} user
   * @returns {Promise<any>}
   */
  async createUser(t) {
    return this.makeRequest("POST", "users/create", t);
  }
  // endregion
  /**
   * Advanced search
   * @param {string} query
   * @param {string} postType
   * @param {number} offset
   * @param {Object} filters
   * @param {Object} filters.post
   * @param {Object} filters.comment
   * @param {Object} filters.meta
   * @param {Object} filters.status
   * @returns {Promise<any>}
   */
  async advanced_search(t, e, i, o) {
    return this.makeRequest(
      "GET",
      "advanced_search",
      {
        query: t,
        postType: e,
        offset: i,
        post: o.post,
        comment: o.comment,
        meta: o.meta,
        status: o.status
      },
      "dt-posts/v2/posts/search/"
    );
  }
}
(function() {
  (function(s) {
    const t = /* @__PURE__ */ new WeakMap(), e = /* @__PURE__ */ new WeakMap(), i = /* @__PURE__ */ new WeakMap(), o = /* @__PURE__ */ new WeakMap(), r = /* @__PURE__ */ new WeakMap(), n = /* @__PURE__ */ new WeakMap(), a = /* @__PURE__ */ new WeakMap(), l = /* @__PURE__ */ new WeakMap(), u = /* @__PURE__ */ new WeakMap(), f = /* @__PURE__ */ new WeakMap(), g = /* @__PURE__ */ new WeakMap(), b = /* @__PURE__ */ new WeakMap(), v = /* @__PURE__ */ new WeakMap(), w = /* @__PURE__ */ new WeakMap(), T = /* @__PURE__ */ new WeakMap(), P = {
      ariaAtomic: "aria-atomic",
      ariaAutoComplete: "aria-autocomplete",
      ariaBusy: "aria-busy",
      ariaChecked: "aria-checked",
      ariaColCount: "aria-colcount",
      ariaColIndex: "aria-colindex",
      ariaColIndexText: "aria-colindextext",
      ariaColSpan: "aria-colspan",
      ariaCurrent: "aria-current",
      ariaDescription: "aria-description",
      ariaDisabled: "aria-disabled",
      ariaExpanded: "aria-expanded",
      ariaHasPopup: "aria-haspopup",
      ariaHidden: "aria-hidden",
      ariaInvalid: "aria-invalid",
      ariaKeyShortcuts: "aria-keyshortcuts",
      ariaLabel: "aria-label",
      ariaLevel: "aria-level",
      ariaLive: "aria-live",
      ariaModal: "aria-modal",
      ariaMultiLine: "aria-multiline",
      ariaMultiSelectable: "aria-multiselectable",
      ariaOrientation: "aria-orientation",
      ariaPlaceholder: "aria-placeholder",
      ariaPosInSet: "aria-posinset",
      ariaPressed: "aria-pressed",
      ariaReadOnly: "aria-readonly",
      ariaRelevant: "aria-relevant",
      ariaRequired: "aria-required",
      ariaRoleDescription: "aria-roledescription",
      ariaRowCount: "aria-rowcount",
      ariaRowIndex: "aria-rowindex",
      ariaRowIndexText: "aria-rowindextext",
      ariaRowSpan: "aria-rowspan",
      ariaSelected: "aria-selected",
      ariaSetSize: "aria-setsize",
      ariaSort: "aria-sort",
      ariaValueMax: "aria-valuemax",
      ariaValueMin: "aria-valuemin",
      ariaValueNow: "aria-valuenow",
      ariaValueText: "aria-valuetext",
      role: "role"
    }, I = (h, c) => {
      for (let p in P) {
        c[p] = null;
        let m = null;
        const y = P[p];
        Object.defineProperty(c, p, {
          get() {
            return m;
          },
          set(x) {
            m = x, h.isConnected ? O(h, y, x) : f.set(h, c);
          }
        });
      }
    };
    function A(h) {
      const c = o.get(h), { form: p } = c;
      Fe(h, p, c), Be(h, c.labels);
    }
    const lt = (h, c = !1) => {
      const p = document.createTreeWalker(h, NodeFilter.SHOW_ELEMENT, {
        acceptNode(x) {
          return o.has(x) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
        }
      });
      let m = p.nextNode();
      const y = !c || h.disabled;
      for (; m; )
        m.formDisabledCallback && y && Kt(m, h.disabled), m = p.nextNode();
    }, Et = { attributes: !0, attributeFilter: ["disabled", "name"] }, z = At() ? new MutationObserver((h) => {
      for (const c of h) {
        const p = c.target;
        if (c.attributeName === "disabled" && (p.constructor.formAssociated ? Kt(p, p.hasAttribute("disabled")) : p.localName === "fieldset" && lt(p)), c.attributeName === "name" && p.constructor.formAssociated) {
          const m = o.get(p), y = u.get(p);
          m.setFormValue(y);
        }
      }
    }) : {};
    function k(h) {
      h.forEach((c) => {
        const { addedNodes: p, removedNodes: m } = c, y = Array.from(p), x = Array.from(m);
        y.forEach((_) => {
          var L;
          if (o.has(_) && _.constructor.formAssociated && A(_), f.has(_)) {
            const C = f.get(_);
            Object.keys(P).filter((q) => C[q] !== null).forEach((q) => {
              O(_, P[q], C[q]);
            }), f.delete(_);
          }
          if (T.has(_)) {
            const C = T.get(_);
            O(_, "internals-valid", C.validity.valid.toString()), O(_, "internals-invalid", (!C.validity.valid).toString()), O(_, "aria-invalid", (!C.validity.valid).toString()), T.delete(_);
          }
          if (_.localName === "form") {
            const C = l.get(_), F = document.createTreeWalker(_, NodeFilter.SHOW_ELEMENT, {
              acceptNode(Xt) {
                return o.has(Xt) && Xt.constructor.formAssociated && !(C && C.has(Xt)) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
              }
            });
            let q = F.nextNode();
            for (; q; )
              A(q), q = F.nextNode();
          }
          _.localName === "fieldset" && ((L = z.observe) === null || L === void 0 || L.call(z, _, Et), lt(_, !0));
        }), x.forEach((_) => {
          const L = o.get(_);
          L && i.get(L) && qe(L), a.has(_) && a.get(_).disconnect();
        });
      });
    }
    function M(h) {
      h.forEach((c) => {
        const { removedNodes: p } = c;
        p.forEach((m) => {
          const y = v.get(c.target);
          o.has(m) && Ve(m), y.disconnect();
        });
      });
    }
    const Q = (h) => {
      var c, p;
      const m = new MutationObserver(M);
      !((c = window == null ? void 0 : window.ShadyDOM) === null || c === void 0) && c.inUse && h.mode && h.host && (h = h.host), (p = m.observe) === null || p === void 0 || p.call(m, h, { childList: !0 }), v.set(h, m);
    };
    At() && new MutationObserver(k);
    const H = {
      childList: !0,
      subtree: !0
    }, O = (h, c, p) => {
      h.getAttribute(c) !== p && h.setAttribute(c, p);
    }, Kt = (h, c) => {
      h.toggleAttribute("internals-disabled", c), c ? O(h, "aria-disabled", "true") : h.removeAttribute("aria-disabled"), h.formDisabledCallback && h.formDisabledCallback.apply(h, [c]);
    }, qe = (h) => {
      i.get(h).forEach((p) => {
        p.remove();
      }), i.set(h, []);
    }, Ue = (h, c) => {
      const p = document.createElement("input");
      return p.type = "hidden", p.name = h.getAttribute("name"), h.after(p), i.get(c).push(p), p;
    }, vo = (h, c) => {
      var p;
      i.set(c, []), (p = z.observe) === null || p === void 0 || p.call(z, h, Et);
    }, Be = (h, c) => {
      if (c.length) {
        Array.from(c).forEach((m) => m.addEventListener("click", h.click.bind(h)));
        let p = c[0].id;
        c[0].id || (p = `${c[0].htmlFor}_Label`, c[0].id = p), O(h, "aria-labelledby", p);
      }
    }, Ct = (h) => {
      const c = Array.from(h.elements).filter((x) => !x.tagName.includes("-") && x.validity).map((x) => x.validity.valid), p = l.get(h) || [], m = Array.from(p).filter((x) => x.isConnected).map((x) => o.get(x).validity.valid), y = [...c, ...m].includes(!1);
      h.toggleAttribute("internals-invalid", y), h.toggleAttribute("internals-valid", !y);
    }, yo = (h) => {
      Ct(Tt(h.target));
    }, wo = (h) => {
      Ct(Tt(h.target));
    }, xo = (h) => {
      const c = ["button[type=submit]", "input[type=submit]", "button:not([type])"].map((p) => `${p}:not([disabled])`).map((p) => `${p}:not([form])${h.id ? `,${p}[form='${h.id}']` : ""}`).join(",");
      h.addEventListener("click", (p) => {
        if (p.target.closest(c)) {
          const y = l.get(h);
          if (h.noValidate)
            return;
          y.size && Array.from(y).reverse().map((L) => o.get(L).reportValidity()).includes(!1) && p.preventDefault();
        }
      });
    }, _o = (h) => {
      const c = l.get(h.target);
      c && c.size && c.forEach((p) => {
        p.constructor.formAssociated && p.formResetCallback && p.formResetCallback.apply(p);
      });
    }, Fe = (h, c, p) => {
      if (c) {
        const m = l.get(c);
        if (m)
          m.add(h);
        else {
          const y = /* @__PURE__ */ new Set();
          y.add(h), l.set(c, y), xo(c), c.addEventListener("reset", _o), c.addEventListener("input", yo), c.addEventListener("change", wo);
        }
        n.set(c, { ref: h, internals: p }), h.constructor.formAssociated && h.formAssociatedCallback && setTimeout(() => {
          h.formAssociatedCallback.apply(h, [c]);
        }, 0), Ct(c);
      }
    }, Tt = (h) => {
      let c = h.parentNode;
      return c && c.tagName !== "FORM" && (c = Tt(c)), c;
    }, B = (h, c, p = DOMException) => {
      if (!h.constructor.formAssociated)
        throw new p(c);
    }, De = (h, c, p) => {
      const m = l.get(h);
      return m && m.size && m.forEach((y) => {
        o.get(y)[p]() || (c = !1);
      }), c;
    }, Ve = (h) => {
      if (h.constructor.formAssociated) {
        const c = o.get(h), { labels: p, form: m } = c;
        Be(h, p), Fe(h, m, c);
      }
    };
    function At() {
      return typeof MutationObserver < "u";
    }
    class $o {
      constructor() {
        this.badInput = !1, this.customError = !1, this.patternMismatch = !1, this.rangeOverflow = !1, this.rangeUnderflow = !1, this.stepMismatch = !1, this.tooLong = !1, this.tooShort = !1, this.typeMismatch = !1, this.valid = !0, this.valueMissing = !1, Object.seal(this);
      }
    }
    const ko = (h) => (h.badInput = !1, h.customError = !1, h.patternMismatch = !1, h.rangeOverflow = !1, h.rangeUnderflow = !1, h.stepMismatch = !1, h.tooLong = !1, h.tooShort = !1, h.typeMismatch = !1, h.valid = !0, h.valueMissing = !1, h), So = (h, c, p) => (h.valid = Eo(c), Object.keys(c).forEach((m) => h[m] = c[m]), p && Ct(p), h), Eo = (h) => {
      let c = !0;
      for (let p in h)
        p !== "valid" && h[p] !== !1 && (c = !1);
      return c;
    }, Zt = /* @__PURE__ */ new WeakMap();
    function ze(h, c) {
      h.toggleAttribute(c, !0), h.part && h.part.add(c);
    }
    class Jt extends Set {
      static get isPolyfilled() {
        return !0;
      }
      constructor(c) {
        if (super(), !c || !c.tagName || c.tagName.indexOf("-") === -1)
          throw new TypeError("Illegal constructor");
        Zt.set(this, c);
      }
      add(c) {
        if (!/^--/.test(c) || typeof c != "string")
          throw new DOMException(`Failed to execute 'add' on 'CustomStateSet': The specified value ${c} must start with '--'.`);
        const p = super.add(c), m = Zt.get(this), y = `state${c}`;
        return m.isConnected ? ze(m, y) : setTimeout(() => {
          ze(m, y);
        }), p;
      }
      clear() {
        for (let [c] of this.entries())
          this.delete(c);
        super.clear();
      }
      delete(c) {
        const p = super.delete(c), m = Zt.get(this);
        return m.isConnected ? (m.toggleAttribute(`state${c}`, !1), m.part && m.part.remove(`state${c}`)) : setTimeout(() => {
          m.toggleAttribute(`state${c}`, !1), m.part && m.part.remove(`state${c}`);
        }), p;
      }
    }
    function He(h, c, p, m) {
      if (typeof c == "function" ? h !== c || !0 : !c.has(h)) throw new TypeError("Cannot read private member from an object whose class did not declare it");
      return p === "m" ? m : p === "a" ? m.call(h) : m ? m.value : c.get(h);
    }
    function Co(h, c, p, m, y) {
      if (typeof c == "function" ? h !== c || !0 : !c.has(h)) throw new TypeError("Cannot write private member to an object whose class did not declare it");
      return c.set(h, p), p;
    }
    var ct;
    class To {
      constructor(c) {
        ct.set(this, void 0), Co(this, ct, c);
        for (let p = 0; p < c.length; p++) {
          let m = c[p];
          this[p] = m, m.hasAttribute("name") && (this[m.getAttribute("name")] = m);
        }
        Object.freeze(this);
      }
      get length() {
        return He(this, ct, "f").length;
      }
      [(ct = /* @__PURE__ */ new WeakMap(), Symbol.iterator)]() {
        return He(this, ct, "f")[Symbol.iterator]();
      }
      item(c) {
        return this[c] == null ? null : this[c];
      }
      namedItem(c) {
        return this[c] == null ? null : this[c];
      }
    }
    function Ao() {
      const h = HTMLFormElement.prototype.checkValidity;
      HTMLFormElement.prototype.checkValidity = p;
      const c = HTMLFormElement.prototype.reportValidity;
      HTMLFormElement.prototype.reportValidity = m;
      function p(...x) {
        let _ = h.apply(this, x);
        return De(this, _, "checkValidity");
      }
      function m(...x) {
        let _ = c.apply(this, x);
        return De(this, _, "reportValidity");
      }
      const { get: y } = Object.getOwnPropertyDescriptor(HTMLFormElement.prototype, "elements");
      Object.defineProperty(HTMLFormElement.prototype, "elements", {
        get(...x) {
          const _ = y.call(this, ...x), L = Array.from(l.get(this) || []);
          if (L.length === 0)
            return _;
          const C = Array.from(_).concat(L).sort((F, q) => F.compareDocumentPosition ? F.compareDocumentPosition(q) & 2 ? 1 : -1 : 0);
          return new To(C);
        }
      });
    }
    class We {
      static get isPolyfilled() {
        return !0;
      }
      constructor(c) {
        if (!c || !c.tagName || c.tagName.indexOf("-") === -1)
          throw new TypeError("Illegal constructor");
        const p = c.getRootNode(), m = new $o();
        this.states = new Jt(c), t.set(this, c), e.set(this, m), o.set(c, this), I(c, this), vo(c, this), Object.seal(this), p instanceof DocumentFragment && Q(p);
      }
      checkValidity() {
        const c = t.get(this);
        if (B(c, "Failed to execute 'checkValidity' on 'ElementInternals': The target element is not a form-associated custom element."), !this.willValidate)
          return !0;
        const p = e.get(this);
        if (!p.valid) {
          const m = new Event("invalid", {
            bubbles: !1,
            cancelable: !0,
            composed: !1
          });
          c.dispatchEvent(m);
        }
        return p.valid;
      }
      get form() {
        const c = t.get(this);
        B(c, "Failed to read the 'form' property from 'ElementInternals': The target element is not a form-associated custom element.");
        let p;
        return c.constructor.formAssociated === !0 && (p = Tt(c)), p;
      }
      get labels() {
        const c = t.get(this);
        B(c, "Failed to read the 'labels' property from 'ElementInternals': The target element is not a form-associated custom element.");
        const p = c.getAttribute("id"), m = c.getRootNode();
        return m && p ? m.querySelectorAll(`[for="${p}"]`) : [];
      }
      reportValidity() {
        const c = t.get(this);
        if (B(c, "Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element."), !this.willValidate)
          return !0;
        const p = this.checkValidity(), m = b.get(this);
        if (m && !c.constructor.formAssociated)
          throw new DOMException("Failed to execute 'reportValidity' on 'ElementInternals': The target element is not a form-associated custom element.");
        return !p && m && (c.focus(), m.focus()), p;
      }
      setFormValue(c) {
        const p = t.get(this);
        if (B(p, "Failed to execute 'setFormValue' on 'ElementInternals': The target element is not a form-associated custom element."), qe(this), c != null && !(c instanceof FormData)) {
          if (p.getAttribute("name")) {
            const m = Ue(p, this);
            m.value = c;
          }
        } else c != null && c instanceof FormData && Array.from(c).reverse().forEach(([m, y]) => {
          if (typeof y == "string") {
            const x = Ue(p, this);
            x.name = m, x.value = y;
          }
        });
        u.set(p, c);
      }
      setValidity(c, p, m) {
        const y = t.get(this);
        if (B(y, "Failed to execute 'setValidity' on 'ElementInternals': The target element is not a form-associated custom element."), !c)
          throw new TypeError("Failed to execute 'setValidity' on 'ElementInternals': 1 argument required, but only 0 present.");
        b.set(this, m);
        const x = e.get(this), _ = {};
        for (const F in c)
          _[F] = c[F];
        Object.keys(_).length === 0 && ko(x);
        const L = Object.assign(Object.assign({}, x), _);
        delete L.valid;
        const { valid: C } = So(x, L, this.form);
        if (!C && !p)
          throw new DOMException("Failed to execute 'setValidity' on 'ElementInternals': The second argument should not be empty if one or more flags in the first argument are true.");
        r.set(this, C ? "" : p), y.isConnected ? (y.toggleAttribute("internals-invalid", !C), y.toggleAttribute("internals-valid", C), O(y, "aria-invalid", `${!C}`)) : T.set(y, this);
      }
      get shadowRoot() {
        const c = t.get(this), p = g.get(c);
        return p || null;
      }
      get validationMessage() {
        const c = t.get(this);
        return B(c, "Failed to read the 'validationMessage' property from 'ElementInternals': The target element is not a form-associated custom element."), r.get(this);
      }
      get validity() {
        const c = t.get(this);
        return B(c, "Failed to read the 'validity' property from 'ElementInternals': The target element is not a form-associated custom element."), e.get(this);
      }
      get willValidate() {
        const c = t.get(this);
        return B(c, "Failed to read the 'willValidate' property from 'ElementInternals': The target element is not a form-associated custom element."), !(c.disabled || c.hasAttribute("disabled") || c.hasAttribute("readonly"));
      }
    }
    function Oo() {
      if (typeof window > "u" || !window.ElementInternals || !HTMLElement.prototype.attachInternals)
        return !1;
      class h extends HTMLElement {
        constructor() {
          super(), this.internals = this.attachInternals();
        }
      }
      const c = `element-internals-feature-detection-${Math.random().toString(36).replace(/[^a-z]+/g, "")}`;
      customElements.define(c, h);
      const p = new h();
      return [
        "shadowRoot",
        "form",
        "willValidate",
        "validity",
        "validationMessage",
        "labels",
        "setFormValue",
        "setValidity",
        "checkValidity",
        "reportValidity"
      ].every((m) => m in p.internals);
    }
    let Ge = !1, Ke = !1;
    function Qt(h) {
      Ke || (Ke = !0, window.CustomStateSet = Jt, h && (HTMLElement.prototype.attachInternals = function(...c) {
        const p = h.call(this, c);
        return p.states = new Jt(this), p;
      }));
    }
    function Ze(h = !0) {
      if (!Ge) {
        if (Ge = !0, typeof window < "u" && (window.ElementInternals = We), typeof CustomElementRegistry < "u") {
          const c = CustomElementRegistry.prototype.define;
          CustomElementRegistry.prototype.define = function(p, m, y) {
            if (m.formAssociated) {
              const x = m.prototype.connectedCallback;
              m.prototype.connectedCallback = function() {
                w.has(this) || (w.set(this, !0), this.hasAttribute("disabled") && Kt(this, !0)), x != null && x.apply(this), Ve(this);
              };
            }
            c.call(this, p, m, y);
          };
        }
        if (typeof HTMLElement < "u" && (HTMLElement.prototype.attachInternals = function() {
          if (this.tagName) {
            if (this.tagName.indexOf("-") === -1)
              throw new Error("Failed to execute 'attachInternals' on 'HTMLElement': Unable to attach ElementInternals to non-custom elements.");
          } else return {};
          if (o.has(this))
            throw new DOMException("DOMException: Failed to execute 'attachInternals' on 'HTMLElement': ElementInternals for the specified element was already attached.");
          return new We(this);
        }), typeof Element < "u") {
          let c = function(...m) {
            const y = p.apply(this, m);
            if (g.set(this, y), At()) {
              const x = new MutationObserver(k);
              window.ShadyDOM ? x.observe(this, H) : x.observe(y, H), a.set(this, x);
            }
            return y;
          };
          const p = Element.prototype.attachShadow;
          Element.prototype.attachShadow = c;
        }
        At() && typeof document < "u" && new MutationObserver(k).observe(document.documentElement, H), typeof HTMLFormElement < "u" && Ao(), (h || typeof window < "u" && !window.CustomStateSet) && Qt();
      }
    }
    return !!customElements.polyfillWrapFlushCallback || (Oo() ? typeof window < "u" && !window.CustomStateSet && Qt(HTMLElement.prototype.attachInternals) : Ze(!1)), s.forceCustomStateSetPolyfill = Qt, s.forceElementInternalsPolyfill = Ze, Object.defineProperty(s, "__esModule", { value: !0 }), s;
  })({});
})();
class j extends U {
  static get properties() {
    return {
      /**
       * Sets the text direction used for localization. If it is not set,
       * it will read the `dir` attribute of the nearest parent,
       * defaulting to the root `<html>` element if no others are found.
       */
      RTL: { type: Boolean },
      /**
       * Defines the locale to be used for localization of the component.
       * If it is not set, it will read the `lang` attribute of the nearest parent,
       * defaulting to the root `<html>` element if no others are found.
       */
      locale: { type: String },
      /**
       * _Feature migrated to ApiService_
       * @deprecated
       */
      apiRoot: { type: String, reflect: !1 },
      /**
       * _Feature migrated to ApiService_
       * @deprecated
       */
      postType: { type: String, reflect: !1 },
      /**
       * _Feature migrated to ApiService_
       * @deprecated
       */
      postID: { type: String, reflect: !1 }
    };
  }
  /**
   * Used to set which element of the shadow DOM should receive focus when the component itself
   * receives focus. This will use the `_focusTarget`, so that getter should be changed instead
   * of this function.
   *
   * By default, it will find the first child in the shadow DOM and focus that element
   */
  get _focusTarget() {
    return this.shadowRoot.children[0] instanceof Element ? this.shadowRoot.children[0] : null;
  }
  constructor() {
    super(), gs(this), this.addEventListener("focus", this._proxyFocus.bind(this));
  }
  connectedCallback() {
    super.connectedCallback(), this.apiRoot = this.apiRoot ? `${this.apiRoot}/`.replace("//", "/") : "/", this.api = new Ie(this.nonce, this.apiRoot);
  }
  willUpdate(t) {
    if (this.RTL === void 0) {
      const e = this.closest("[dir]");
      if (e) {
        const i = e.getAttribute("dir");
        i && (this.RTL = i.toLowerCase() === "rtl");
      }
    }
    if (!this.locale) {
      const e = this.closest("[lang]");
      if (e) {
        const i = e.getAttribute("lang");
        i && (this.locale = i);
      }
    }
    if (t && t.has("locale") && this.locale)
      try {
        As(this.locale);
      } catch (e) {
        console.error(e);
      }
  }
  /**
   * Used to transfer focus to the shadow DOM when the component itself receives focus.
   * This will use the `_focusTarget` to determine which shadow DOM element to focus,
   * so that getter should be changed instead of this function when the shadow DOM is non-standard.
   * @returns
   */
  _proxyFocus() {
    this._focusTarget && this._focusTarget.focus();
  }
}
class Os extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      label: { type: String },
      context: { type: String },
      type: { type: String },
      outline: { type: Boolean },
      href: { type: String },
      title: { type: String },
      // onClick: { attribute: false },
      rounded: { type: Boolean },
      confirm: { type: String },
      buttonClass: { type: String },
      custom: { type: Boolean },
      favorite: { type: Boolean, reflect: !0 },
      favorited: { type: String },
      listButton: { type: Boolean },
      buttonStyle: { type: Object }
    };
  }
  get classes() {
    const t = {
      "dt-button": !0,
      "dt-button--outline": this.outline,
      "dt-button--rounded": this.rounded,
      "dt-button--custom": this.custom
    }, e = `dt-button--${this.context}`;
    return t[e] = !0, t;
  }
  constructor() {
    super(), this.context = "default", this.favorite = this.favorited || !1, this.listButton = !1;
  }
  connectedCallback() {
    super.connectedCallback(), (this.id.startsWith("favorite") || this.id === "follow-button" || this.id === "following-button") && window.addEventListener("load", async () => {
      const t = await new CustomEvent("dt:get-data", {
        bubbles: !0,
        detail: {
          field: this.id,
          postType: this.postType,
          onSuccess: (e) => {
            switch (Object.keys(e).find(
              (o) => ["favorite", "unfollow", "follow"].includes(o)
            )) {
              case "favorite":
                {
                  this.favorite = e.favorite ? e.favorite : !1;
                  const n = this.shadowRoot.querySelector("slot").assignedNodes({
                    flatten: !0
                  }).find(
                    (a) => a.nodeType === Node.ELEMENT_NODE && a.classList.contains("icon-star")
                  );
                  this.favorite ? n.classList.add("selected") : n.classList.remove("selected"), this.requestUpdate();
                }
                break;
              case "follow":
                this.following = !0, this.requestUpdate();
                break;
              case "unfollow":
                this.following = !1, this.requestUpdate();
                break;
              default:
                console.log("No matching Key found!");
                break;
            }
          },
          onError: (e) => {
            console.warn(e);
          }
        }
      });
      this.dispatchEvent(t);
    });
  }
  handleClick(t) {
    if (t.preventDefault(), this.confirm && !confirm(this.confirm)) {
      t.preventDefault();
      return;
    }
    if ((this.id.startsWith("favorite") || this.id === "follow-button" || this.id === "following-button") && (t.preventDefault(), this.onClick(t)), this.id === "create-post-button") {
      const e = this.closest("form");
      e ? console.log("Form found", e) : console.error("Form not found!");
      const i = new FormData(e), o = {
        form: {},
        el: {
          type: "access"
        }
      };
      i.forEach((n, a) => {
        o.form[a] = n;
      }), Array.from(e.elements).forEach((n) => {
        if (n.localName.startsWith("dt-") && n.value && String(n.value).trim() !== "")
          if (n.localName.startsWith("dt-comm")) {
            const a = n.value.map((l) => ({
              value: l.value
            }));
            o.el[n.name] = a;
          } else if (n.localName.startsWith("dt-multi") || n.localName.startsWith("dt-tags")) {
            const a = n.value.map((l) => ({ value: l }));
            o.el[n.name] = { values: a };
          } else if (n.localName.startsWith("dt-connection")) {
            const a = n.value.map((l) => ({
              value: l.label
            }));
            o.el[n.name] = { values: a };
          } else
            o.el[n.name] = n.value;
      });
      const r = new CustomEvent("send-data", {
        detail: {
          field: this.id,
          newValue: o
        }
      });
      this.dispatchEvent(r);
    }
    if (this.type === "submit") {
      const e = this.closest("form");
      e && e.dispatchEvent(new Event("submit", { cancelable: !0, bubbles: !0 }));
    }
  }
  onClick(t) {
    if (t.preventDefault(), t.stopPropagation(), this.listButton && (this.favorite = this.favorited), this.id.startsWith("favorite")) {
      const e = new CustomEvent("customClick", {
        detail: {
          field: this.id,
          toggleState: !this.favorite
        },
        bubbles: !0,
        composed: !0
      });
      this.favorite = !this.favorite;
      const r = this.shadowRoot.querySelector("slot").assignedNodes({ flatten: !0 }).find(
        (n) => n.nodeType === Node.ELEMENT_NODE && n.classList.contains("icon-star")
      );
      r && (r.classList.contains("selected") ? r.classList.remove("selected") : r.classList.add("selected")), this.dispatchEvent(e), this.requestUpdate();
    } else if (this.id === "follow-button" || this.id === "following-button") {
      const e = this.following, i = new CustomEvent("customClick", {
        detail: {
          field: this.id,
          toggleState: e
        }
      });
      this.id = this.id === "follow-button" ? "following-button" : "follow-button", this.label = this.label === "Follow" ? "Following" : "Follow", this.outline = !this.outline, this.following = !this.following, this.requestUpdate(), this.dispatchEvent(i);
    }
  }
  _getSVGIcon() {
    return this.id === "follow-button" || this.id === "following-button" ? d`<svg
          xmlns="http://www.w3.org/2000/svg"
          width="1em"
          height="1em"
          viewBox="0 0 24 24"
        >
          <path
            fill="currentColor"
            d="M12 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5a5 5 0 0 1 5-5a5 5 0 0 1 5 5a5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5"
          />
        </svg>` : "";
  }
  _dismiss() {
    this.hide = !0;
  }
  render() {
    if (this.hide)
      return d``;
    const t = {
      ...this.classes
    }, e = (this.id === "follow-button" || this.id === "following-button") && this.label ? this.label : d`<slot></slot>`;
    return this.href ? d`
        <a
          id=${this.name}
          class=${N(t)}
          href=${this.href}
          title=${this.title}
          type=${this.type}
          @click=${this.handleClick}
        >
          <div>${e}${this._getSVGIcon()}</div>
        </a>
      ` : d`
      <button
        class=${N(t)}
        title=${this.title}
        style=${Z(this.buttonStyle || {})}
        type=${this.type}
        .value=${this.value}
        @click=${this.handleClick}
      >
        <div>${e}${this._getSVGIcon()}</div>
      </button>
    `;
  }
}
window.customElements.define("dt-button", Os);
class Is extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      title: { type: String },
      context: { type: String },
      isHelp: { type: Boolean },
      isOpen: { type: Boolean },
      hideHeader: { type: Boolean },
      hideButton: { type: Boolean },
      buttonClass: { type: Object },
      buttonStyle: { type: Object },
      headerClass: { type: Object },
      imageSrc: { type: String },
      imageStyle: { type: Object },
      tileLabel: { type: String },
      buttonLabel: { type: String },
      dropdownListImg: { type: String },
      submitButton: { type: Boolean },
      closeButton: { type: Boolean }
    };
  }
  constructor() {
    super(), this.context = "default", this.addEventListener("open", () => this._openModal()), this.addEventListener("close", () => this._closeModal());
  }
  _openModal() {
    this.isOpen = !0, this.shadowRoot.querySelector("dialog").showModal(), document.querySelector("body").style.overflow = "hidden";
  }
  // to format title coming from backend
  get formattedTitle() {
    if (!this.title) return "";
    const t = this.title.replace(/_/g, " ");
    return t.charAt(0).toUpperCase() + t.slice(1);
  }
  _dialogHeader(t) {
    return this.hideHeader ? d`` : d`
      <header>
            <h1 id="modal-field-title" class="modal-header">${this.formattedTitle}</h1>
            <button @click="${this._cancelModal}" class="toggle">${t}</button>
          </header>
      `;
  }
  _closeModal() {
    this.isOpen = !1, this.shadowRoot.querySelector("dialog").close(), document.querySelector("body").style.overflow = "initial";
  }
  _cancelModal() {
    this._triggerClose("cancel");
  }
  _triggerClose(t) {
    this.dispatchEvent(new CustomEvent("close", {
      detail: {
        action: t
      }
    }));
  }
  _dialogClick(t) {
    if (t.target.tagName !== "DIALOG")
      return;
    const e = t.target.getBoundingClientRect();
    (e.top <= t.clientY && t.clientY <= e.top + e.height && e.left <= t.clientX && t.clientX <= e.left + e.width) === !1 && this._cancelModal();
  }
  _dialogKeypress(t) {
    t.key === "Escape" && this._cancelModal();
  }
  _helpMore() {
    return this.isHelp ? d`
          <div class="help-more">
            <h5>${S("Need more help?")}</h5>
            <a
              class="button small"
              id="docslink"
              href="https://disciple.tools/user-docs"
              target="_blank"
              >${S("Read the documentation")}</a
            >
          </div>
        ` : null;
  }
  firstUpdated() {
    this.isOpen && this._openModal();
  }
  _onButtonClick() {
    this._triggerClose("button");
  }
  render() {
    const t = d`
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
    `;
    return d`
      <dialog
        id=""
        class="dt-modal dt-modal--width ${N(this.headerClass || {})}"
        @click=${this._dialogClick}
        @keypress=${this._dialogKeypress}
      >
        <form method="dialog" class=${this.hideHeader ? "no-header" : ""}>
      ${this._dialogHeader(t)}
          <article>
            <slot name="content"></slot>
          </article>
          <footer>
          <div class=footer-button>
          ${this.closeButton ? d`
            <button
              class="button small"
              data-close=""
              aria-label="Close reveal"
              type="button"
              @click=${this._onButtonClick}
            >
              <slot name="close-button">${S("Close")}</slot>
              </button>

            ` : ""}
              ${this.submitButton ? d`
                <slot name="submit-button"></span>

                ` : ""}
              </div>
            ${this._helpMore()}
          </footer>
        </form>
      </dialog>

      ${this.hideButton ? null : d`
      <button
        class="button small opener ${N(this.buttonClass || {})}"
        data-open=""
        aria-label="Open reveal"
        type="button"
        @click="${this._openModal}"
        style=${Z(this.buttonStyle || {})}
      >
      ${this.dropdownListImg ? d`<img src=${this.dropdownListImg} alt="" style="width = 15px; height : 15px">` : ""}
      ${this.imageSrc ? d`<img
                   src="${this.imageSrc}"
                   alt="${this.buttonLabel} icon"
                   class="help-icon"
                   style=${Z(this.imageStyle || {})}
                 />` : ""}
      ${this.buttonLabel ? d`${this.buttonLabel}` : ""}
      </button>
      `}
    `;
  }
}
window.customElements.define("dt-modal", Is);
class Ls extends U {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      options: { type: Array },
      label: { type: String },
      isModal: { type: Boolean },
      buttonStyle: { type: Object },
      default: { type: Boolean },
      context: { type: String }
    };
  }
  get classes() {
    const t = {
      "dt-dropdown": !0
    }, e = `dt-dropdown--${this.context}`;
    return t[e] = !0, t;
  }
  render() {
    return d`
    <div class="dropdown">
    <button
    class=${N(this.classes)}
    style=${Z(this.buttonStyle || {})}
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

    ${this.options ? this.options.map(
      (t) => d`
        ${t.isModal ? d`
              <li
                class="pre-list-item"
                @click="${() => this._openDialog(t.label)}"
                @keydown="${() => this._openDialog(t.label)}"
              >

                <button
                style=""
                @click="${() => this._openDialog(t.label)}"
                class="list-style dt-modal"
                >
                ${t.icon ? d`<img
                   src="${t.icon}"
                   alt="${t.label} icon"
                   class="icon"
                 />` : ""}
                ${t.label}
                </button>
              </li>
            ` : d`
              <li
                class="list-style pre-list-item"
                @click="${() => this._redirectToHref(t.href)}"
                @keydown="${() => this._redirectToHref(t.href)}"
              >

                <button
                  style=""
                  @click="${() => this._redirectToHref(t.href)}"
                >
                  <img
                    src=${t.icon}
                    alt=${t.label}
                    class="icon"
                  />
                  ${t.label.replace(/-/g, " ")}
                </button>
              </li>
            `}
      `
    ) : ""}
    </ul>


    </div>
    `;
  }
  // eslint-disable-next-line class-methods-use-this
  _redirectToHref(t) {
    let e = t;
    /^https?:\/\//i.test(e) || (e = `http://${e}`), window.open(e, "_blank");
  }
  _openDialog(t) {
    const e = t.replace(/\s/g, "-").toLowerCase();
    document.querySelector(`#${e}`).shadowRoot.querySelector("dialog").showModal(), document.querySelector("body").style.overflow = "hidden";
  }
  _handleHover() {
    const t = this.shadowRoot.querySelector("ul");
    t.style.display = "block";
  }
  _handleMouseLeave() {
    const t = this.shadowRoot.querySelector("ul");
    t.style.display = "none";
  }
}
window.customElements.define("dt-dropdown", Ls);
class Ms extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      key: { type: String },
      metric: { type: Object },
      group: { type: Object },
      active: { type: Boolean, reflect: !0 },
      missingIcon: { type: String },
      handleSave: { type: Function }
    };
  }
  render() {
    const {
      metric: t,
      active: e,
      missingIcon: i = `${window.wpApiShare.template_dir}/dt-assets/images/groups/missing.svg`
    } = this;
    return d`<div
      class=${N({
      "health-item": !0,
      "health-item--active": e
    })}
      title="${t.description}"
      @click="${this._handleClick}"
    >
      <img src="${t.icon ? t.icon : i}" />
    </div>`;
  }
  async _handleClick() {
    if (!this.handleSave)
      return;
    const t = !this.active;
    this.active = t;
    const e = {
      health_metrics: {
        values: [
          {
            value: this.key,
            delete: !t
          }
        ]
      }
    };
    try {
      await this.handleSave(this.group.ID, e);
    } catch (i) {
      console.error(i);
      return;
    }
    t ? this.group.health_metrics.push(this.key) : this.group.health_metrics.pop(this.key);
  }
}
window.customElements.define("dt-church-health-icon", Ms);
class Ps extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      groupId: { type: Number },
      group: { type: Object, reflect: !1 },
      settings: { type: Object, reflect: !1 },
      errorMessage: { type: String, attribute: !1 },
      missingIcon: { type: String },
      handleSave: { type: Function }
    };
  }
  /**
   * Map fields settings as an array and filter out church commitment
   */
  get metrics() {
    const t = this.settings || [];
    return Object.values(t).length ? Object.entries(t).filter(([i, o]) => i !== "church_commitment") : [];
  }
  get isCommited() {
    return !this.group || !this.group.health_metrics ? !1 : this.group.health_metrics.includes("church_commitment");
  }
  /**
   * Fetch group data on component load if it's not provided as a property
   */
  connectedCallback() {
    super.connectedCallback(), this.fetch();
  }
  adoptedCallback() {
    this.distributeItems();
  }
  /**
   * Position the items after the component is rendered
   */
  updated() {
    this.distributeItems();
  }
  /**
   * Fetch the group and settings data if not provided by the server
   */
  async fetch() {
    try {
      const t = [this.fetchSettings(), this.fetchGroup()];
      let [e, i] = await Promise.all(t);
      this.settings = e, this.post = i, e || (this.errorMessage = "Error loading settings"), i || (this.errorMessage = "Error loading group");
    } catch (t) {
      console.error(t);
    }
  }
  /**
   * Fetch the group data if it's not already set
   * @returns
   */
  fetchGroup() {
    if (this.group)
      return Promise.resolve(this.group);
    fetch(`/wp-json/dt-posts/v2/groups/${this.groupId}`).then(
      (t) => t.json()
    );
  }
  /**
   * Fetch the settings data if not already set
   * @returns
   */
  fetchSettings() {
    return this.settings ? Promise.resolve(this.settings) : fetch("/wp-json/dt-posts/v2/groups/settings").then(
      (t) => t.json()
    );
  }
  /**
   * Find a metric by key
   * @param {*} key
   * @returns
   */
  findMetric(t) {
    const e = this.metrics.find((i) => i.key === t);
    return e ? e.value : null;
  }
  /**
   * Render the component
   * @returns
   */
  render() {
    if (!this.group || !this.metrics.length)
      return d`<dt-spinner></dt-spinner>`;
    const t = this.group.health_metrics || [];
    return this.errorMessage && d`<dt-alert type="error">${this.errorMessage}</dt-alert>`, d`
      <div class="health-circle__wrapper">
        <div class="health-circle__container">
          <div
            class=${N({
      "health-circle": !0,
      "health-circle--committed": this.isCommited
    })}
          >
            <div class="health-circle__grid">
              ${this.metrics.map(
      ([e, i], o) => d`<dt-church-health-icon
                    key="${e}"
                    .group="${this.group}"
                    .metric=${i}
                    .active=${t.indexOf(e) !== -1}
                    .style="--i: ${o + 1}"
                    .missingIcon="${this.missingIcon}"
                    .handleSave="${this.handleSave}"
                  >
                  </dt-church-health-icon>`
    )}
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
    `;
  }
  /**
   * Dynamically distribute items in Church Health Circle
   * according to amount of health metric elements
   */
  distributeItems() {
    const t = this.renderRoot.querySelector(
      ".health-circle__container"
    );
    let o = t.querySelectorAll("dt-church-health-icon").length, r = Math.tan(Math.PI / o);
    t.style.setProperty("--m", o), t.style.setProperty("--tan", +r.toFixed(2));
  }
  async toggleClick(t) {
    const { handleSave: e } = this;
    if (!e)
      return;
    let i = this.renderRoot.querySelector("dt-toggle"), o = i.toggleAttribute("checked");
    this.group.health_metrics || (this.group.health_metrics = []);
    const r = {
      health_metrics: {
        values: [
          {
            value: "church_commitment",
            delete: !o
          }
        ]
      }
    };
    try {
      await e(this.group.ID, r);
    } catch (n) {
      i.toggleAttribute("checked", !o), console.error(n);
      return;
    }
    o ? this.group.health_metrics.push("church_commitment") : this.group.health_metrics.pop("church_commitment"), this.requestUpdate();
  }
  _isChecked() {
    return Object.hasOwn(this.group, "health_metrics") ? this.group.health_metrics.includes("church_commitment") ? this.isChurch = !0 : this.isChurch = !1 : this.isChurch = !1;
  }
}
window.customElements.define("dt-church-health-circle", Ps);
class Rs extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      icon: { type: String },
      iconAltText: { type: String },
      private: { type: Boolean },
      privateLabel: { type: String }
    };
  }
  firstUpdated() {
    const e = this.shadowRoot.querySelector("slot[name=icon-start]").assignedElements({ flatten: !0 });
    for (const i of e)
      i.style.height = "100%", i.style.width = "auto";
  }
  get _slottedChildren() {
    return this.shadowRoot.querySelector("slot").assignedElements({ flatten: !0 });
  }
  render() {
    const t = d`<svg class="icon" height='100px' width='100px' fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M5273.1,2400.1v-2c0-2.8-5-4-9.7-4s-9.7,1.3-9.7,4v2c0,1.8,0.7,3.6,2,4.9l5,4.9c0.3,0.3,0.4,0.6,0.4,1v6.4     c0,0.4,0.2,0.7,0.6,0.8l2.9,0.9c0.5,0.1,1-0.2,1-0.8v-7.2c0-0.4,0.2-0.7,0.4-1l5.1-5C5272.4,2403.7,5273.1,2401.9,5273.1,2400.1z      M5263.4,2400c-4.8,0-7.4-1.3-7.5-1.8v0c0.1-0.5,2.7-1.8,7.5-1.8c4.8,0,7.3,1.3,7.5,1.8C5270.7,2398.7,5268.2,2400,5263.4,2400z"></path><path d="M5268.4,2410.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1c0-0.6-0.4-1-1-1H5268.4z"></path><path d="M5272.7,2413.7h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2414.1,5273.3,2413.7,5272.7,2413.7z"></path><path d="M5272.7,2417h-4.3c-0.6,0-1,0.4-1,1c0,0.6,0.4,1,1,1h4.3c0.6,0,1-0.4,1-1C5273.7,2417.5,5273.3,2417,5272.7,2417z"></path></g><path d="M75.8,37.6v-9.3C75.8,14.1,64.2,2.5,50,2.5S24.2,14.1,24.2,28.3v9.3c-7,0.6-12.4,6.4-12.4,13.6v32.6    c0,7.6,6.1,13.7,13.7,13.7h49.1c7.6,0,13.7-6.1,13.7-13.7V51.2C88.3,44,82.8,38.2,75.8,37.6z M56,79.4c0.2,1-0.5,1.9-1.5,1.9h-9.1    c-1,0-1.7-0.9-1.5-1.9l3-11.8c-2.5-1.1-4.3-3.6-4.3-6.6c0-4,3.3-7.3,7.3-7.3c4,0,7.3,3.3,7.3,7.3c0,2.9-1.8,5.4-4.3,6.6L56,79.4z     M62.7,37.5H37.3v-9.1c0-7,5.7-12.7,12.7-12.7s12.7,5.7,12.7,12.7V37.5z"></path></g></g></svg>`;
    return d`
      <div class="label">
        <span class="icon">
          <slot name="icon-start">
            ${this.icon ? d`<img src="${this.icon}" alt="${this.iconAltText}" />` : null}
          </slot>
        </span>
        <slot></slot>

        ${this.private ? d`<span class="icon private">
              ${t}
              <span class="tooltip"
                >${this.privateLabel || S("Private Field: Only I can see its content")}</span
              >
            </span> ` : null}
      </div>
    `;
  }
}
window.customElements.define("dt-label", Rs);
class R extends j {
  static get formAssociated() {
    return !0;
  }
  static get styles() {
    return [
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      /**
       * The name attribute used to identify an input within a form.
       * This will be submitted with the form as the field's key.
       */
      name: { type: String },
      /**
       * Text to be displayed in the label above the form field.
       * <br/><br/>
       * Leave this empty to not display a label.
       */
      label: { type: String },
      /**
       * Icon to be used beside the label. This should be a URL to an image file.
       * <br/><br/>
       * To use an embedded SVG as the icon, see the `icon` slot.
       */
      icon: { type: String },
      /** Alt text to be added to icon image */
      iconAltText: { type: String },
      /** Indicates if field is marked with a lock icon to indicate private fields. */
      private: { type: Boolean },
      /** Tooltip text to be added to private icon. */
      privateLabel: { type: String },
      /** Disables field. */
      disabled: { type: Boolean },
      /** Validates that field is not empty when form is submitted and displays error if not. */
      required: { type: Boolean },
      /** Error message to be displayed for required field validation. */
      requiredMessage: { type: String },
      /**
       * _Internal state value not available via HTML attribute._
       * <br/><br/>
       * Indicates that the form field has been changed to use when validating form.
       */
      touched: {
        type: Boolean,
        state: !0
      },
      /**
       * _Internal state value not available via HTML attribute._
       * <br/><br/>
       * Indicates that the form field is not valid to use when validating form.
       */
      invalid: {
        type: Boolean,
        state: !0
      },
      /** Enables error state with error icon. This error message will be displayed. */
      error: { type: String },
      /** Enables display of loading indicator. */
      loading: { type: Boolean },
      /** Enables display of saved indicator. */
      saved: { type: Boolean }
    };
  }
  /**
   * Identifies the form element to receive focus when the component receives focus.
   */
  get _field() {
    return this.shadowRoot.querySelector("input, textarea, select");
  }
  /**
   * Sets the focus target to `_field`.
   */
  get _focusTarget() {
    return this._field;
  }
  constructor() {
    super(), this.touched = !1, this.invalid = !1, this.internals = this.attachInternals(), this.addEventListener("invalid", () => {
      this.touched = !0, this._validateRequired();
    });
  }
  firstUpdated(...t) {
    super.firstUpdated(...t);
    const e = R._jsonToFormData(this.value, this.name);
    this.internals.setFormValue(e), this._validateRequired();
  }
  /**
   * Recursively create FormData from JSON data
   * @param formData
   * @param data
   * @param parentKey
   * @private
   */
  static _buildFormData(t, e, i) {
    if (e && typeof e == "object" && !(e instanceof Date) && !(e instanceof File))
      Object.keys(e).forEach((o) => {
        this._buildFormData(t, e[o], i ? `${i}[${o}]` : o);
      });
    else {
      const o = e ?? "";
      t.append(i, o);
    }
  }
  /**
   * Convert JSON to FormData object
   * @param data
   * @param parentKey - prefix for all values. Should be the field name
   * @returns {FormData}
   * @private
   */
  static _jsonToFormData(t, e) {
    const i = new FormData();
    return R._buildFormData(i, t, e), i;
  }
  /**
   * Interacts with the form internals to set the form value that will be submitted with a standard
   * HTML form.
   * @param value
   * @private
   */
  _setFormValue(t) {
    const e = R._jsonToFormData(t, this.name);
    this.internals.setFormValue(e, t), this._validateRequired(), this.touched = !0;
  }
  /* eslint-disable class-methods-use-this */
  /**
   * Not implemented by default.
   *
   * Can/should be overriden by each component to implement logic for checking if a value is entered/selected
   * @private
   */
  _validateRequired() {
  }
  /* eslint-enable class-methods-use-this */
  /**
   * Renders the `<dt-label>` element. Should be used in each component to place the label in
   * the appropriate location.
   * @returns {TemplateResult<1>|string}
   */
  labelTemplate() {
    return this.label ? d`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon ? null : d`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
    ` : "";
  }
  /**
   * Renders the component. This should be overridden by each component.
   * @returns {TemplateResult<1>}
   */
  render() {
    return d`
      ${this.labelTemplate()}
      <slot></slot>
    `;
  }
}
/**
 * @license
 * Copyright 2020 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const { I: Ns } = Xo, pi = () => document.createComment(""), ht = (s, t, e) => {
  var r;
  const i = s._$AA.parentNode, o = t === void 0 ? s._$AB : t._$AA;
  if (e === void 0) {
    const n = i.insertBefore(pi(), o), a = i.insertBefore(pi(), o);
    e = new Ns(n, a, s, s.options);
  } else {
    const n = e._$AB.nextSibling, a = e._$AM, l = a !== s;
    if (l) {
      let u;
      (r = e._$AQ) == null || r.call(e, s), e._$AM = s, e._$AP !== void 0 && (u = s._$AU) !== a._$AU && e._$AP(u);
    }
    if (n !== o || l) {
      let u = e._$AA;
      for (; u !== n; ) {
        const f = u.nextSibling;
        i.insertBefore(u, o), u = f;
      }
    }
  }
  return e;
}, Y = (s, t, e = s) => (s._$AI(t, e), s), js = {}, qs = (s, t = js) => s._$AH = t, Us = (s) => s._$AH, ae = (s) => {
  var i;
  (i = s._$AP) == null || i.call(s, !1, !0);
  let t = s._$AA;
  const e = s._$AB.nextSibling;
  for (; t !== e; ) {
    const o = t.nextSibling;
    t.remove(), t = o;
  }
};
/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const fi = (s, t, e) => {
  const i = /* @__PURE__ */ new Map();
  for (let o = t; o <= e; o++) i.set(s[o], o);
  return i;
}, Le = Ae(class extends Oe {
  constructor(s) {
    if (super(s), s.type !== Te.CHILD) throw Error("repeat() can only be used in text expressions");
  }
  dt(s, t, e) {
    let i;
    e === void 0 ? e = t : t !== void 0 && (i = t);
    const o = [], r = [];
    let n = 0;
    for (const a of s) o[n] = i ? i(a, n) : n, r[n] = e(a, n), n++;
    return { values: r, keys: o };
  }
  render(s, t, e) {
    return this.dt(s, t, e).values;
  }
  update(s, [t, e, i]) {
    const o = Us(s), { values: r, keys: n } = this.dt(t, e, i);
    if (!Array.isArray(o)) return this.ut = n, r;
    const a = this.ut ?? (this.ut = []), l = [];
    let u, f, g = 0, b = o.length - 1, v = 0, w = r.length - 1;
    for (; g <= b && v <= w; ) if (o[g] === null) g++;
    else if (o[b] === null) b--;
    else if (a[g] === n[v]) l[v] = Y(o[g], r[v]), g++, v++;
    else if (a[b] === n[w]) l[w] = Y(o[b], r[w]), b--, w--;
    else if (a[g] === n[w]) l[w] = Y(o[g], r[w]), ht(s, l[w + 1], o[g]), g++, w--;
    else if (a[b] === n[v]) l[v] = Y(o[b], r[v]), ht(s, o[g], o[b]), b--, v++;
    else if (u === void 0 && (u = fi(n, v, w), f = fi(a, g, b)), u.has(a[g])) if (u.has(a[b])) {
      const T = f.get(n[v]), P = T !== void 0 ? o[T] : null;
      if (P === null) {
        const I = ht(s, o[g]);
        Y(I, r[v]), l[v] = I;
      } else l[v] = Y(P, r[v]), ht(s, o[g], P), o[T] = null;
      v++;
    } else ae(o[b]), b--;
    else ae(o[g]), g++;
    for (; v <= w; ) {
      const T = ht(s, l[w + 1]);
      Y(T, r[v]), l[v++] = T;
    }
    for (; g <= b; ) {
      const T = o[g++];
      T !== null && ae(T);
    }
    return this.ut = n, qs(s, l), V;
  }
}), Bs = (s) => class extends s {
  constructor() {
    super(), this.activeIndex = -1, this.filteredOptions = [], this.detectTap = !1;
  }
  static get properties() {
    return {
      ...super.properties,
      value: {
        type: Array,
        reflect: !0
      },
      query: {
        type: String,
        state: !0
      },
      options: { type: Array },
      filteredOptions: { type: Array, state: !0 },
      open: {
        type: Boolean,
        state: !0
      },
      canUpdate: {
        type: Boolean,
        state: !0
      },
      activeIndex: {
        type: Number,
        state: !0
      },
      containerHeight: {
        type: Number,
        state: !0
      },
      loading: { type: Boolean }
    };
  }
  willUpdate(t) {
    if (super.willUpdate(t), t && !this.containerHeight && this.shadowRoot.children && this.shadowRoot.children.length) {
      const e = this.shadowRoot.querySelector(".input-group");
      e && (this.containerHeight = e.offsetHeight);
    }
  }
  updated() {
    this._scrollOptionListToActive();
    const t = this.shadowRoot.querySelector(".input-group");
    !t.style.getPropertyValue("--container-width") && t.clientWidth > 0 && t.style.setProperty(
      "--container-width",
      `${t.clientWidth}px`
    );
  }
  _select() {
    console.error("Must implement `_select(value)` function");
  }
  /* Search Input Field Events */
  static _focusInput(t) {
    t.target === t.currentTarget && t.target.getElementsByTagName("input")[0].focus();
  }
  _inputFocusIn(t) {
    (!t.relatedTarget || !["BUTTON", "LI"].includes(t.relatedTarget.nodeName)) && (this.open = !0, this.activeIndex = -1);
  }
  _inputFocusOut(t) {
    (!t.relatedTarget || !["BUTTON", "LI"].includes(t.relatedTarget.nodeName)) && (this.open = !1, this.canUpdate = !0);
  }
  _inputKeyDown(t) {
    switch (t.keyCode || t.which) {
      case 8:
        t.target.value === "" ? this.value = this.value.slice(0, -1) : this.open = !0;
        break;
      case 38:
        this.open = !0, this._listHighlightPrevious();
        break;
      case 40:
        this.open = !0, this._listHighlightNext();
        break;
      case 9:
        this.activeIndex < 0 ? this.open = !1 : t.preventDefault(), this._keyboardSelectOption();
        break;
      case 13:
        this._keyboardSelectOption();
        break;
      case 27:
        this.open = !1, this.activeIndex = -1;
        break;
      default:
        this.open = !0;
        break;
    }
  }
  _inputKeyUp(t) {
    this.query = t.target.value;
  }
  /**
   * When navigating via keyboard, keep active element within visible area of option list
   * @private
   */
  _scrollOptionListToActive() {
    const t = this.shadowRoot.querySelector(".option-list"), e = this.shadowRoot.querySelector("button.active");
    if (t && e) {
      const i = e.offsetTop, o = e.offsetTop + e.clientHeight, r = t.scrollTop, n = t.scrollTop + t.clientHeight;
      o > n ? t.scrollTo({
        top: o - t.clientHeight,
        behavior: "smooth"
      }) : i < r && t.scrollTo({ top: i, behavior: "smooth" });
    }
  }
  /* Option List Events */
  _touchStart(t) {
    t.target && (this.detectTap = !1);
  }
  _touchMove(t) {
    t.target && (this.detectTap = !0);
  }
  _touchEnd(t) {
    this.detectTap || (t.target && t.target.value && this._clickOption(t), this.detectTap = !1);
  }
  _keyboardSelectOption() {
    this.activeIndex > -1 && (this.activeIndex + 1 > this.filteredOptions.length ? this._select(this.query) : this._select(this.filteredOptions[this.activeIndex].id), this._clearSearch());
  }
  _clickOption(t) {
    t.target && t.target.value && (this._select(t.target.value), this._clearSearch());
  }
  _clickAddNew(t) {
    var e;
    t.target && (this._select((e = t.target.dataset) == null ? void 0 : e.label), this._clearSearch());
  }
  _clearSearch() {
    const t = this.shadowRoot.querySelector("input");
    t && (t.value = "");
  }
  /* Option List Navigation */
  _listHighlightNext() {
    this.allowAdd ? this.activeIndex = Math.min(
      this.filteredOptions.length,
      // allow 1 more than the list length
      this.activeIndex + 1
    ) : this.activeIndex = Math.min(
      this.filteredOptions.length - 1,
      this.activeIndex + 1
    );
  }
  _listHighlightPrevious() {
    this.activeIndex = Math.max(0, this.activeIndex - 1);
  }
  /* Rendering */
  _renderOption(t, e) {
    return d`
      <li tabindex="-1">
        <button
          value="${t.id}"
          type="button"
          data-label="${t.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          ${t.label}
        </button>
      </li>
    `;
  }
  _baseRenderOptions() {
    return this.filteredOptions.length ? Le(this.filteredOptions, (t) => t.id, (t, e) => this._renderOption(t, e)) : this.loading ? d`<li><div>${S("Loading options...")}</div></li>` : d`<li><div>${S("No Data Available")}</div></li>`;
  }
  _renderOptions() {
    let t = this._baseRenderOptions();
    return this.allowAdd && this.query && (Array.isArray(t) || (t = [t]), t.push(d`<li tabindex="-1">
        <button
          data-label="${this.query}"
          @click="${this._clickAddNew}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          class="${this.activeIndex > -1 && this.activeIndex >= this.filteredOptions.length ? "active" : ""}"
        >
          ${S("Add")} "${this.query}"
        </button>
      </li>`)), t;
  }
};
class Fs extends U {
  static get styles() {
    return $`
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
    `;
  }
}
window.customElements.define("dt-spinner", Fs);
class Ds extends U {
  static get styles() {
    return $`
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
    `;
  }
}
window.customElements.define("dt-checkmark", Ds);
class Gi extends Bs(R) {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      placeholder: { type: String },
      containerHeight: {
        type: Number,
        state: !0
      },
      onchange: { type: String }
    };
  }
  _select(t) {
    const e = new CustomEvent("change", {
      bubbles: !0,
      detail: {
        field: this.name,
        oldValue: this.value
      }
    });
    if (this.value && this.value.length)
      if (typeof this.value[0] == "string")
        this.value = [...this.value.filter((i) => i !== `-${t}`), t];
      else {
        let i = !1;
        const o = this.value.map((r) => {
          const n = {
            ...r
          };
          return r.id === t.id && r.delete && (delete n.delete, i = !0), n;
        });
        i || o.push(t), this.value = o;
      }
    else
      this.value = [t];
    e.detail.newValue = this.value, this.open = !1, this.activeIndex = -1, this.canUpdate = !0, this.dispatchEvent(e), this._setFormValue(this.value);
  }
  _remove(t) {
    if (t.target && t.target.dataset && t.target.dataset.value) {
      const e = new CustomEvent("change", {
        bubbles: !0,
        detail: {
          field: this.name,
          oldValue: this.value
        }
      });
      this.value = (this.value || []).map(
        (i) => i === t.target.dataset.value ? `-${i}` : i
      ), e.detail.newValue = this.value, this.dispatchEvent(e), this._setFormValue(this.value), this.open && this.shadowRoot.querySelector("input").focus();
    }
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  _filterOptions() {
    return this.filteredOptions = (this.options || []).filter(
      (t) => !(this.value || []).includes(t.id) && (!this.query || t.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))
    ), this.filteredOptions;
  }
  willUpdate(t) {
    if (super.willUpdate(t), t) {
      const e = t.has("value"), i = t.has("query"), o = t.has("options");
      (e || i || o) && this._filterOptions();
    }
  }
  _renderSelectedOptions() {
    return this.options && this.options.filter((t) => this.value && this.value.indexOf(t.id) > -1).map(
      (t) => d`
            <div class="selected-option">
              <span>${t.label}</span>
              <button
                @click="${this._remove}"
                ?disabled="${this.disabled}"
                data-value="${t.id}"
              >
                x
              </button>
            </div>
          `
    );
  }
  render() {
    const t = {
      display: this.open ? "block" : "none",
      top: this.containerHeight ? `${this.containerHeight}px` : "2.5rem"
    };
    return d`
      ${this.labelTemplate()}

      <div class="input-group ${this.disabled ? "disabled" : ""}">
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
        <ul class="option-list" style=${Z(t)}>
          ${this._renderOptions()}
        </ul>
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
        ${this.error ? d`<dt-icon
                icon="mdi:alert-circle"
                class="icon-overlay alert"
                tooltip="${this.error}"
                size="2rem"
                ></dt-icon>` : null}
        </div>
`;
  }
}
window.customElements.define("dt-multi-select", Gi);
class Ht extends Gi {
  static get properties() {
    return {
      ...super.properties,
      allowAdd: { type: Boolean },
      onload: { type: String }
    };
  }
  static get styles() {
    return [
      ...super.styles,
      $`
        .selected-option a,
        .selected-option a:active,
        .selected-option a:visited {
          text-decoration: none;
          color: var(--primary-color, #3f729b);
        }
      `
    ];
  }
  willUpdate(t) {
    super.willUpdate(t), t && t.has("open") && this.open && (!this.filteredOptions || !this.filteredOptions.length) && this._filterOptions();
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  _filterOptions() {
    var e;
    const t = (this.value || []).filter((i) => !i.startsWith("-"));
    if ((e = this.options) != null && e.length)
      this.filteredOptions = (this.options || []).filter(
        (i) => !t.includes(i.id) && (!this.query || i.id.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))
      );
    else if (this.open || this.canUpdate) {
      this.loading = !0, this.filteredOptions = [];
      const i = this, o = new CustomEvent("dt:get-data", {
        bubbles: !0,
        detail: {
          field: this.name,
          postType: this.postType,
          query: this.query,
          onSuccess: (r) => {
            i.loading = !1;
            let n = r;
            n.length && typeof n[0] == "string" && (n = n.map((a) => ({
              id: a
            }))), i.allOptions = n, i.filteredOptions = n.filter(
              (a) => !t.includes(a.id)
            );
          },
          onError: (r) => {
            console.warn(r), i.loading = !1, this.canUpdate = !1;
          }
        }
      });
      this.dispatchEvent(o);
    }
    return this.filteredOptions;
  }
  _renderOption(t, e) {
    return d`
      <li tabindex="-1">
        <button
          value="${t.id}"
          type="button"
          data-label="${t.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          ${t.label || t.id}
        </button>
      </li>
    `;
  }
  _renderSelectedOptions() {
    const t = this.options || this.allOptions;
    return (this.value || []).filter((e) => !e.startsWith("-")).map(
      (e) => {
        var r;
        let i = e;
        if (t) {
          const n = t.filter((a) => a === e || a.id === e);
          n.length && (i = n[0].label || n[0].id || e);
        }
        let o;
        if (!o && ((r = window == null ? void 0 : window.SHAREDFUNCTIONS) != null && r.createCustomFilter)) {
          const n = window.SHAREDFUNCTIONS.createCustomFilter(this.name, [e]), a = this.label || this.name, l = [{ id: `${this.name}_${e}`, name: `${a}: ${e}` }];
          o = window.SHAREDFUNCTIONS.create_url_for_list_query(this.postType, n, l);
        }
        return d`
          <div class="selected-option">
            <a
              href="${o || "#"}"
              ?disabled="${this.disabled}"
              alt="${e}"
              >${i}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${e}"
            >
              x
            </button>
          </div>
        `;
      }
    );
  }
}
window.customElements.define("dt-tags", Ht);
class Vs extends Ht {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  _clickOption(t) {
    if (t.target && t.target.value) {
      const e = parseInt(t.target.value, 10), i = this.filteredOptions.reduce((o, r) => !o && r.id == e ? r : o, null);
      i && this._select(i), this._clearSearch();
    }
  }
  _clickAddNew(t) {
    var e, i;
    if (t.target) {
      this._select({
        id: (e = t.target.dataset) == null ? void 0 : e.label,
        label: (i = t.target.dataset) == null ? void 0 : i.label,
        isNew: !0
      });
      const o = this.shadowRoot.querySelector("input");
      o && (o.value = "");
    }
  }
  _keyboardSelectOption() {
    this.activeIndex > -1 && (this.activeIndex + 1 > this.filteredOptions.length ? this._select({
      id: this.query,
      label: this.query,
      isNew: !0
    }) : this._select(this.filteredOptions[this.activeIndex]), this._clearSearch());
  }
  _remove(t) {
    if (t.target && t.target.dataset && t.target.dataset.value) {
      const e = new CustomEvent("change", {
        detail: {
          field: this.name,
          oldValue: this.value
        }
      });
      this.value = (this.value || []).map((i) => {
        const o = {
          ...i
        };
        return i.id === t.target.dataset.value && (o.delete = !0), o;
      }), e.detail.newValue = this.value, this.dispatchEvent(e), this.open && this.shadowRoot.querySelector("input").focus();
    }
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  _filterOptions() {
    var e;
    const t = (this.value || []).filter((i) => !i.delete).map((i) => i == null ? void 0 : i.id);
    if ((e = this.options) != null && e.length)
      this.filteredOptions = (this.options || []).filter(
        (i) => !t.includes(i.id) && (!this.query || i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))
      );
    else if (this.open || this.canUpdate) {
      this.loading = !0, this.filteredOptions = [];
      const i = this, o = new CustomEvent("dt:get-data", {
        bubbles: !0,
        detail: {
          field: this.name,
          postType: this.postType,
          query: this.query,
          onSuccess: (r) => {
            i.loading = !1, i.filteredOptions = r.filter(
              (n) => !t.includes(n.id)
            );
          },
          onError: (r) => {
            console.warn(r), i.loading = !1, this.canUpdate = !1;
          }
        }
      });
      this.dispatchEvent(o);
    }
    return this.filteredOptions;
  }
  _renderSelectedOptions() {
    return (this.value || []).filter((t) => !t.delete).map(
      (t) => d`
          <div class="selected-option">
            <a
              href="${t.link}"
              style="border-inline-start-color: ${t.status ? t.status.color : ""}"
              ?disabled="${this.disabled}"
              title="${t.status ? t.status.label : t.label}"
              >${t.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t.id}"
            >
              x
            </button>
          </div>
        `
    );
  }
  _renderOption(t, e) {
    const i = d`<svg width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>circle-08 2</title><desc>Created using Figma</desc><g id="Canvas" transform="translate(1457 4940)"><g id="circle-08 2"><g id="Group"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(-1457 -4940)" fill="#000000"/></g></g></g></g><defs><path id="path0_fill" d="M 12 0C 5.383 0 0 5.383 0 12C 0 18.617 5.383 24 12 24C 18.617 24 24 18.617 24 12C 24 5.383 18.617 0 12 0ZM 8 10C 8 7.791 9.844 6 12 6C 14.156 6 16 7.791 16 10L 16 11C 16 13.209 14.156 15 12 15C 9.844 15 8 13.209 8 11L 8 10ZM 12 22C 9.567 22 7.335 21.124 5.599 19.674C 6.438 18.091 8.083 17 10 17L 14 17C 15.917 17 17.562 18.091 18.401 19.674C 16.665 21.124 14.433 22 12 22Z"/></defs></svg>`, o = t.status || {
      label: "",
      color: ""
    };
    return d`
      <li tabindex="-1" style="border-inline-start-color:${o.color}">
        <button
          value="${t.id}"
          type="button"
          data-label="${t.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          <span class="label">${t.label}</span>
          <span class="connection-id">(#${t.id})</span>
          ${o.label ? d`<span class="status">${o.label}</span>` : null}
          ${t.user ? i : null}
        </button>
      </li>
    `;
  }
}
window.customElements.define("dt-connection", Vs);
class zs extends Ht {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  _clickOption(t) {
    if (t.target && t.target.value) {
      const e = parseInt(t.target.value, 10), i = this.filteredOptions.reduce((o, r) => !o && r.id == e ? r : o, null);
      i && this._select(i), this._clearSearch();
    }
  }
  _clickAddNew(t) {
    var e, i;
    if (t.target) {
      this._select({
        id: (e = t.target.dataset) == null ? void 0 : e.label,
        label: (i = t.target.dataset) == null ? void 0 : i.label,
        isNew: !0
      });
      const o = this.shadowRoot.querySelector("input");
      o && (o.value = "");
    }
  }
  _keyboardSelectOption() {
    this.activeIndex > -1 && (this.activeIndex + 1 > this.filteredOptions.length ? this._select({
      id: this.query,
      label: this.query,
      isNew: !0
    }) : this._select(this.filteredOptions[this.activeIndex]), this._clearSearch());
  }
  _remove(t) {
    if (t.target && t.target.dataset && t.target.dataset.value) {
      const e = new CustomEvent("change", {
        detail: {
          field: this.name,
          oldValue: this.value,
          remove: !0
        }
      });
      this.value = (this.value || []).map((i) => {
        const o = {
          ...i
        };
        return i.id === parseInt(t.target.dataset.value, 10) && (o.delete = !0), o;
      }), e.detail.newValue = this.value, this.dispatchEvent(e), this.open && this.shadowRoot.querySelector("input").focus();
    }
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  _filterOptions() {
    var e;
    const t = (this.value || []).filter((i) => !i.delete).map((i) => i == null ? void 0 : i.id);
    if ((e = this.options) != null && e.length)
      this.filteredOptions = (this.options || []).filter(
        (i) => !t.includes(i.id) && (!this.query || i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))
      );
    else if (this.open || this.canUpdate) {
      this.loading = !0, this.filteredOptions = [];
      const i = this, o = new CustomEvent("dt:get-data", {
        bubbles: !0,
        detail: {
          field: this.name,
          postType: this.postType,
          query: this.query,
          onSuccess: (r) => {
            i.loading = !1, i.filteredOptions = r.filter(
              (n) => !t.includes(n.id)
            );
          },
          onError: (r) => {
            console.warn(r), i.loading = !1, this.canUpdate = !1;
          }
        }
      });
      this.dispatchEvent(o);
    }
    return this.filteredOptions;
  }
  _renderSelectedOptions() {
    return (this.value || []).filter((t) => !t.delete).map(
      (t) => d`
          <div class="selected-option">
            <a
              href="${t.link}"
              style="border-inline-start-color: ${t.status ? t.status : ""}"
              ?disabled="${this.disabled}"
              title="${t.label}"
              >${t.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t.id}"
            >
              x
            </button>
          </div>
        `
    );
  }
  _renderOption(t, e) {
    return d`
      <li tabindex="-1" style="border-inline-start-color:${t.status}">
        <button
          value="${t.id}"
          type="button"
          data-label="${t.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @blur="${this._inputFocusOut}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          <span class="avatar"><img src="${t.avatar}" alt="${t.label}"/></span> &nbsp;
          <span class="connection-id">${t.label}</span>
        </button>
      </li>
    `;
  }
}
window.customElements.define("dt-users-connection", zs);
class Hs extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      value: { type: String },
      success: { type: Boolean },
      error: { type: Boolean }
    };
  }
  get inputStyles() {
    return this.success ? {
      "--dt-text-border-color": "var(--copy-text-success-color, var(--success-color))",
      "--dt-form-text-color": "var( --copy-text-success-color, var(--success-color))",
      color: "var( --copy-text-success-color, var(--success-color))"
    } : this.error ? {
      "---dt-text-border-color": "var(--copy-text-alert-color, var(--alert-color))",
      "--dt-form-text-color": "var(--copy-text-alert-color, var(--alert-color))"
    } : {};
  }
  get icon() {
    return this.success ? "ic:round-check" : "ic:round-content-copy";
  }
  async copy() {
    try {
      this.success = !1, this.error = !1, await navigator.clipboard.writeText(this.value), this.success = !0, this.error = !1;
    } catch (t) {
      console.log(t), this.success = !1, this.error = !0;
    }
  }
  render() {
    return d`
      <div class="copy-text" style=${Z(this.inputStyles)}>
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
    `;
  }
}
window.customElements.define("dt-copy-text", Hs);
class Ws extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      id: { type: String },
      value: {
        type: String,
        reflect: !0
      },
      timestamp: {
        converter: (t) => {
          let e = Number(t);
          if (e < 1e12 && (e *= 1e3), e) return e;
        },
        reflect: !0
      },
      onchange: { type: String }
    };
  }
  // _convertArabicToEnglishNumbers() {
  //   this.value
  //   .replace(/[\u0660-\u0669]/g, (c) => { return c.charCodeAt(0) - 0x0660; })
  //     .replace(/[\u06f0-\u06f9]/g, (c) => {
  //       return c.charCodeAt(0) - 0x06f0;
  //     });
  // }
  updateTimestamp(t) {
    const e = new Date(t).getTime(), i = e / 1e3, o = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.timestamp,
        newValue: i
      }
    });
    this.timestamp = e, this.value = t, this._setFormValue(t), this.dispatchEvent(o);
  }
  _change(t) {
    this.updateTimestamp(t.target.value);
  }
  clearInput() {
    this.updateTimestamp("");
  }
  showDatePicker() {
    this.shadowRoot.querySelector("input").showPicker();
  }
  render() {
    return this.timestamp ? this.value = new Date(this.timestamp).toISOString().substring(0, 10) : this.value && (this.timestamp = new Date(this.value).getTime()), d`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          class="input-group-field dt_date_picker"
          type="date"
          autocomplete="off"
          .placeholder="${(/* @__PURE__ */ new Date()).toISOString().substring(0, 10)}"
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

        ${this.touched && this.invalid || this.error ? d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>` : null}
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
      </div>
    `;
  }
}
window.customElements.define("dt-date", Ws);
/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
function* Rt(s, t) {
  if (s !== void 0) {
    let e = 0;
    for (const i of s) yield t(i, e++);
  }
}
class Gs extends Ht {
  static get properties() {
    return {
      ...super.properties,
      filters: { type: Array },
      mapboxKey: { type: String },
      dtMapbox: { type: Object }
    };
  }
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  _clickOption(t) {
    if (t.target && t.target.value) {
      const e = t.target.value, i = this.filteredOptions.reduce((o, r) => !o && r.id === e ? r : o, null);
      this._select(i);
    }
  }
  _clickAddNew(t) {
    var e, i;
    if (t.target) {
      this._select({
        id: (e = t.target.dataset) == null ? void 0 : e.label,
        label: (i = t.target.dataset) == null ? void 0 : i.label,
        isNew: !0
      });
      const o = this.shadowRoot.querySelector("input");
      o && (o.value = "");
    }
  }
  _keyboardSelectOption() {
    this.activeIndex > -1 && (this.activeIndex + 1 > this.filteredOptions.length ? this._select({
      id: this.query,
      label: this.query,
      isNew: !0
    }) : this._select(this.filteredOptions[this.activeIndex]));
  }
  _remove(t) {
    if (t.target && t.target.dataset && t.target.dataset.value) {
      const e = new CustomEvent("change", {
        detail: {
          field: this.name,
          oldValue: this.value
        }
      });
      this.value = (this.value || []).map((i) => {
        const o = {
          ...i
        };
        return i.id === t.target.dataset.value && (o.delete = !0), o;
      }), e.detail.newValue = this.value, this.dispatchEvent(e), this.open && this.shadowRoot.querySelector("input").focus();
    }
  }
  updated() {
    super.updated();
    const t = this.shadowRoot.querySelector(".input-group"), e = t.style.getPropertyValue("--select-width"), i = this.shadowRoot.querySelector("select");
    !e && (i == null ? void 0 : i.clientWidth) > 0 && t.style.setProperty("--select-width", `${i.clientWidth}px`);
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  _filterOptions() {
    var e;
    const t = (this.value || []).filter((i) => !i.delete).map((i) => i == null ? void 0 : i.id);
    if ((e = this.options) != null && e.length)
      this.filteredOptions = (this.options || []).filter(
        (i) => !t.includes(i.id) && (!this.query || i.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase()))
      );
    else {
      this.loading = !0, this.filteredOptions = [];
      const i = this, o = this.shadowRoot.querySelector("select"), r = new CustomEvent("dt:get-data", {
        bubbles: !0,
        detail: {
          field: this.name,
          query: this.query,
          filter: o == null ? void 0 : o.value,
          onSuccess: (n) => {
            i.loading = !1, i.filteredOptions = n.filter(
              (a) => !t.includes(a.id)
            );
          },
          onError: (n) => {
            console.warn(n), i.loading = !1;
          }
        }
      });
      this.dispatchEvent(r);
    }
    return this.filteredOptions;
  }
  _renderOption(t, e) {
    return d`
      <li tabindex="-1">
        <button
          value="${t.id}"
          type="button"
          data-label="${t.label}"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          ${t.label}
        </button>
      </li>
    `;
  }
  _renderSelectedOptions() {
    return (this.value || []).filter((t) => !t.delete).map(
      (t) => d`
          <div class="selected-option">
            <a
              href="${t.link}"
              ?disabled="${this.disabled}"
              alt="${t.status ? t.status.label : t.label}"
              >${t.label}</a
            >
            <button
              @click="${this._remove}"
              ?disabled="${this.disabled}"
              data-value="${t.id}"
            >
              x
            </button>
          </div>
        `
    );
  }
  render() {
    const t = {
      display: this.open ? "block" : "none",
      top: `${this.containerHeight}px`
    };
    return this.mapboxKey ? d` ${this.labelTemplate()}
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
          </div>` : d`
          ${this.labelTemplate()}

          <div class="input-group ${this.disabled ? "disabled" : ""}">
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

              ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
              ${this.saved ? d`<dt-checkmark
                    class="icon-overlay success"
                  ></dt-checkmark>` : null}
            </div>
            <select class="filter-list" ?disabled="${this.disabled}">
              ${Rt(
      this.filters,
      (e) => d`<option value="${e.id}">${e.label}</option>`
    )}
            </select>
            <ul class="option-list" style=${Z(t)}>
              ${this._renderOptions()}
            </ul>
          </div>
        `;
  }
}
window.customElements.define("dt-location", Gs);
class Ks {
  constructor(t) {
    this.token = t;
  }
  /**
   * Search places via Mapbox API
   * @param query
   * @param language
   * @returns {Promise<Array>}
   */
  async searchPlaces(t, e = "en") {
    const i = new URLSearchParams({
      types: ["country", "region", "postcode", "district", "place", "locality", "neighborhood", "address"],
      limit: 6,
      access_token: this.token,
      language: e
    }), o = {
      method: "GET",
      headers: {
        "Content-Type": "application/json"
      }
    }, r = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(t)}.json?${i}`, a = await (await fetch(r, o)).json();
    return a == null ? void 0 : a.features;
  }
  /**
   * Reverse geocode a long/lat pair to get place details
   * @param longitude
   * @param latitude
   * @param language
   * @returns {Promise<Array>}
   */
  async reverseGeocode(t, e, i = "en") {
    const o = new URLSearchParams({
      types: ["country", "region", "postcode", "district", "place", "locality", "neighborhood", "address"],
      access_token: this.token,
      language: i
    }), r = {
      method: "GET",
      headers: {
        "Content-Type": "application/json"
      }
    }, n = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(t)},${encodeURI(e)}.json?${o}`, l = await (await fetch(n, r)).json();
    return l == null ? void 0 : l.features;
  }
}
class Zs {
  constructor(t, e, i) {
    var o, r, n;
    if (this.token = t, this.window = e, !((n = (r = (o = e.google) == null ? void 0 : o.maps) == null ? void 0 : r.places) != null && n.AutocompleteService)) {
      let a = i.createElement("script");
      a.src = `https://maps.googleapis.com/maps/api/js?libraries=places&key=${t}`, i.body.appendChild(a);
    }
  }
  /**
   * Search places via Mapbox API
   * @param query
   * @returns {Promise<any>}
   */
  async getPlacePredictions(t, e = "en") {
    if (this.window.google) {
      const i = new this.window.google.maps.places.AutocompleteService(), { predictions: o } = await i.getPlacePredictions({
        input: t,
        language: e
      });
      return o;
    }
    return null;
  }
  /**
   * Get details for a given address
   * @param address
   * @param language
   * @returns {Promise<null>}
   */
  async getPlaceDetails(t, e = "en") {
    const o = `https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({
      key: this.token,
      address: t,
      language: e
    })}`, n = await (await fetch(o, { method: "GET" })).json();
    let a = [];
    switch (n.status) {
      case "OK":
        a = n.results;
        break;
    }
    return a && a.length ? a[0] : null;
  }
  /**
   * Reverse geocode a lng/lat pair to get place details
   * @param longitude
   * @param latitude
   * @param language
   * @returns {Promise<Array>}
   */
  async reverseGeocode(t, e, i = "en") {
    const r = `https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({
      key: this.token,
      latlng: `${e},${t}`,
      language: i,
      result_type: [
        "point_of_interest",
        "establishment",
        "premise",
        "street_address",
        "neighborhood",
        "sublocality",
        "locality",
        "colloquial_area",
        "political",
        "country"
      ].join("|")
    })}`, a = await (await fetch(r, { method: "GET" })).json();
    return a == null ? void 0 : a.results;
  }
}
/**
* (c) Iconify
*
* For the full copyright and license information, please view the license.txt
* files at https://github.com/iconify/iconify
*
* Licensed under MIT.
*
* @license MIT
* @version 1.0.2
*/
const Ki = Object.freeze(
  {
    left: 0,
    top: 0,
    width: 16,
    height: 16
  }
), Ft = Object.freeze({
  rotate: 0,
  vFlip: !1,
  hFlip: !1
}), kt = Object.freeze({
  ...Ki,
  ...Ft
}), me = Object.freeze({
  ...kt,
  body: "",
  hidden: !1
}), Js = Object.freeze({
  width: null,
  height: null
}), Zi = Object.freeze({
  ...Js,
  ...Ft
});
function Qs(s, t = 0) {
  const e = s.replace(/^-?[0-9.]*/, "");
  function i(o) {
    for (; o < 0; )
      o += 4;
    return o % 4;
  }
  if (e === "") {
    const o = parseInt(s);
    return isNaN(o) ? 0 : i(o);
  } else if (e !== s) {
    let o = 0;
    switch (e) {
      case "%":
        o = 25;
        break;
      case "deg":
        o = 90;
    }
    if (o) {
      let r = parseFloat(s.slice(0, s.length - e.length));
      return isNaN(r) ? 0 : (r = r / o, r % 1 === 0 ? i(r) : 0);
    }
  }
  return t;
}
const Xs = /[\s,]+/;
function Ys(s, t) {
  t.split(Xs).forEach((e) => {
    switch (e.trim()) {
      case "horizontal":
        s.hFlip = !0;
        break;
      case "vertical":
        s.vFlip = !0;
        break;
    }
  });
}
const Ji = {
  ...Zi,
  preserveAspectRatio: ""
};
function gi(s) {
  const t = {
    ...Ji
  }, e = (i, o) => s.getAttribute(i) || o;
  return t.width = e("width", null), t.height = e("height", null), t.rotate = Qs(e("rotate", "")), Ys(t, e("flip", "")), t.preserveAspectRatio = e("preserveAspectRatio", e("preserveaspectratio", "")), t;
}
function tr(s, t) {
  for (const e in Ji)
    if (s[e] !== t[e])
      return !0;
  return !1;
}
const bt = /^[a-z0-9]+(-[a-z0-9]+)*$/, St = (s, t, e, i = "") => {
  const o = s.split(":");
  if (s.slice(0, 1) === "@") {
    if (o.length < 2 || o.length > 3)
      return null;
    i = o.shift().slice(1);
  }
  if (o.length > 3 || !o.length)
    return null;
  if (o.length > 1) {
    const a = o.pop(), l = o.pop(), u = {
      provider: o.length > 0 ? o[0] : i,
      prefix: l,
      name: a
    };
    return t && !Nt(u) ? null : u;
  }
  const r = o[0], n = r.split("-");
  if (n.length > 1) {
    const a = {
      provider: i,
      prefix: n.shift(),
      name: n.join("-")
    };
    return t && !Nt(a) ? null : a;
  }
  if (e && i === "") {
    const a = {
      provider: i,
      prefix: "",
      name: r
    };
    return t && !Nt(a, e) ? null : a;
  }
  return null;
}, Nt = (s, t) => s ? !!((s.provider === "" || s.provider.match(bt)) && (t && s.prefix === "" || s.prefix.match(bt)) && s.name.match(bt)) : !1;
function er(s, t) {
  const e = {};
  !s.hFlip != !t.hFlip && (e.hFlip = !0), !s.vFlip != !t.vFlip && (e.vFlip = !0);
  const i = ((s.rotate || 0) + (t.rotate || 0)) % 4;
  return i && (e.rotate = i), e;
}
function mi(s, t) {
  const e = er(s, t);
  for (const i in me)
    i in Ft ? i in s && !(i in e) && (e[i] = Ft[i]) : i in t ? e[i] = t[i] : i in s && (e[i] = s[i]);
  return e;
}
function ir(s, t) {
  const e = s.icons, i = s.aliases || /* @__PURE__ */ Object.create(null), o = /* @__PURE__ */ Object.create(null);
  function r(n) {
    if (e[n])
      return o[n] = [];
    if (!(n in o)) {
      o[n] = null;
      const a = i[n] && i[n].parent, l = a && r(a);
      l && (o[n] = [a].concat(l));
    }
    return o[n];
  }
  return Object.keys(e).concat(Object.keys(i)).forEach(r), o;
}
function or(s, t, e) {
  const i = s.icons, o = s.aliases || /* @__PURE__ */ Object.create(null);
  let r = {};
  function n(a) {
    r = mi(
      i[a] || o[a],
      r
    );
  }
  return n(t), e.forEach(n), mi(s, r);
}
function Qi(s, t) {
  const e = [];
  if (typeof s != "object" || typeof s.icons != "object")
    return e;
  s.not_found instanceof Array && s.not_found.forEach((o) => {
    t(o, null), e.push(o);
  });
  const i = ir(s);
  for (const o in i) {
    const r = i[o];
    r && (t(o, or(s, o, r)), e.push(o));
  }
  return e;
}
const sr = {
  provider: "",
  aliases: {},
  not_found: {},
  ...Ki
};
function le(s, t) {
  for (const e in t)
    if (e in s && typeof s[e] != typeof t[e])
      return !1;
  return !0;
}
function Xi(s) {
  if (typeof s != "object" || s === null)
    return null;
  const t = s;
  if (typeof t.prefix != "string" || !s.icons || typeof s.icons != "object" || !le(s, sr))
    return null;
  const e = t.icons;
  for (const o in e) {
    const r = e[o];
    if (!o.match(bt) || typeof r.body != "string" || !le(
      r,
      me
    ))
      return null;
  }
  const i = t.aliases || /* @__PURE__ */ Object.create(null);
  for (const o in i) {
    const r = i[o], n = r.parent;
    if (!o.match(bt) || typeof n != "string" || !e[n] && !i[n] || !le(
      r,
      me
    ))
      return null;
  }
  return t;
}
const Dt = /* @__PURE__ */ Object.create(null);
function rr(s, t) {
  return {
    provider: s,
    prefix: t,
    icons: /* @__PURE__ */ Object.create(null),
    missing: /* @__PURE__ */ new Set()
  };
}
function J(s, t) {
  const e = Dt[s] || (Dt[s] = /* @__PURE__ */ Object.create(null));
  return e[t] || (e[t] = rr(s, t));
}
function Me(s, t) {
  return Xi(t) ? Qi(t, (e, i) => {
    i ? s.icons[e] = i : s.missing.add(e);
  }) : [];
}
function nr(s, t, e) {
  try {
    if (typeof e.body == "string")
      return s.icons[t] = { ...e }, !0;
  } catch {
  }
  return !1;
}
function ar(s, t) {
  let e = [];
  return (typeof s == "string" ? [s] : Object.keys(Dt)).forEach((o) => {
    (typeof o == "string" && typeof t == "string" ? [t] : Object.keys(Dt[o] || {})).forEach((n) => {
      const a = J(o, n);
      e = e.concat(
        Object.keys(a.icons).map(
          (l) => (o !== "" ? "@" + o + ":" : "") + n + ":" + l
        )
      );
    });
  }), e;
}
let xt = !1;
function Yi(s) {
  return typeof s == "boolean" && (xt = s), xt;
}
function _t(s) {
  const t = typeof s == "string" ? St(s, !0, xt) : s;
  if (t) {
    const e = J(t.provider, t.prefix), i = t.name;
    return e.icons[i] || (e.missing.has(i) ? null : void 0);
  }
}
function to(s, t) {
  const e = St(s, !0, xt);
  if (!e)
    return !1;
  const i = J(e.provider, e.prefix);
  return nr(i, e.name, t);
}
function bi(s, t) {
  if (typeof s != "object")
    return !1;
  if (typeof t != "string" && (t = s.provider || ""), xt && !t && !s.prefix) {
    let o = !1;
    return Xi(s) && (s.prefix = "", Qi(s, (r, n) => {
      n && to(r, n) && (o = !0);
    })), o;
  }
  const e = s.prefix;
  if (!Nt({
    provider: t,
    prefix: e,
    name: "a"
  }))
    return !1;
  const i = J(t, e);
  return !!Me(i, s);
}
function lr(s) {
  return !!_t(s);
}
function cr(s) {
  const t = _t(s);
  return t ? {
    ...kt,
    ...t
  } : null;
}
function dr(s) {
  const t = {
    loaded: [],
    missing: [],
    pending: []
  }, e = /* @__PURE__ */ Object.create(null);
  s.sort((o, r) => o.provider !== r.provider ? o.provider.localeCompare(r.provider) : o.prefix !== r.prefix ? o.prefix.localeCompare(r.prefix) : o.name.localeCompare(r.name));
  let i = {
    provider: "",
    prefix: "",
    name: ""
  };
  return s.forEach((o) => {
    if (i.name === o.name && i.prefix === o.prefix && i.provider === o.provider)
      return;
    i = o;
    const r = o.provider, n = o.prefix, a = o.name, l = e[r] || (e[r] = /* @__PURE__ */ Object.create(null)), u = l[n] || (l[n] = J(r, n));
    let f;
    a in u.icons ? f = t.loaded : n === "" || u.missing.has(a) ? f = t.missing : f = t.pending;
    const g = {
      provider: r,
      prefix: n,
      name: a
    };
    f.push(g);
  }), t;
}
function eo(s, t) {
  s.forEach((e) => {
    const i = e.loaderCallbacks;
    i && (e.loaderCallbacks = i.filter((o) => o.id !== t));
  });
}
function ur(s) {
  s.pendingCallbacksFlag || (s.pendingCallbacksFlag = !0, setTimeout(() => {
    s.pendingCallbacksFlag = !1;
    const t = s.loaderCallbacks ? s.loaderCallbacks.slice(0) : [];
    if (!t.length)
      return;
    let e = !1;
    const i = s.provider, o = s.prefix;
    t.forEach((r) => {
      const n = r.icons, a = n.pending.length;
      n.pending = n.pending.filter((l) => {
        if (l.prefix !== o)
          return !0;
        const u = l.name;
        if (s.icons[u])
          n.loaded.push({
            provider: i,
            prefix: o,
            name: u
          });
        else if (s.missing.has(u))
          n.missing.push({
            provider: i,
            prefix: o,
            name: u
          });
        else
          return e = !0, !0;
        return !1;
      }), n.pending.length !== a && (e || eo([s], r.id), r.callback(
        n.loaded.slice(0),
        n.missing.slice(0),
        n.pending.slice(0),
        r.abort
      ));
    });
  }));
}
let hr = 0;
function pr(s, t, e) {
  const i = hr++, o = eo.bind(null, e, i);
  if (!t.pending.length)
    return o;
  const r = {
    id: i,
    icons: t,
    callback: s,
    abort: o
  };
  return e.forEach((n) => {
    (n.loaderCallbacks || (n.loaderCallbacks = [])).push(r);
  }), o;
}
const be = /* @__PURE__ */ Object.create(null);
function vi(s, t) {
  be[s] = t;
}
function ve(s) {
  return be[s] || be[""];
}
function fr(s, t = !0, e = !1) {
  const i = [];
  return s.forEach((o) => {
    const r = typeof o == "string" ? St(o, t, e) : o;
    r && i.push(r);
  }), i;
}
var gr = {
  resources: [],
  index: 0,
  timeout: 2e3,
  rotate: 750,
  random: !1,
  dataAfterTimeout: !1
};
function mr(s, t, e, i) {
  const o = s.resources.length, r = s.random ? Math.floor(Math.random() * o) : s.index;
  let n;
  if (s.random) {
    let k = s.resources.slice(0);
    for (n = []; k.length > 1; ) {
      const M = Math.floor(Math.random() * k.length);
      n.push(k[M]), k = k.slice(0, M).concat(k.slice(M + 1));
    }
    n = n.concat(k);
  } else
    n = s.resources.slice(r).concat(s.resources.slice(0, r));
  const a = Date.now();
  let l = "pending", u = 0, f, g = null, b = [], v = [];
  typeof i == "function" && v.push(i);
  function w() {
    g && (clearTimeout(g), g = null);
  }
  function T() {
    l === "pending" && (l = "aborted"), w(), b.forEach((k) => {
      k.status === "pending" && (k.status = "aborted");
    }), b = [];
  }
  function P(k, M) {
    M && (v = []), typeof k == "function" && v.push(k);
  }
  function I() {
    return {
      startTime: a,
      payload: t,
      status: l,
      queriesSent: u,
      queriesPending: b.length,
      subscribe: P,
      abort: T
    };
  }
  function A() {
    l = "failed", v.forEach((k) => {
      k(void 0, f);
    });
  }
  function lt() {
    b.forEach((k) => {
      k.status === "pending" && (k.status = "aborted");
    }), b = [];
  }
  function Et(k, M, Q) {
    const H = M !== "success";
    switch (b = b.filter((O) => O !== k), l) {
      case "pending":
        break;
      case "failed":
        if (H || !s.dataAfterTimeout)
          return;
        break;
      default:
        return;
    }
    if (M === "abort") {
      f = Q, A();
      return;
    }
    if (H) {
      f = Q, b.length || (n.length ? z() : A());
      return;
    }
    if (w(), lt(), !s.random) {
      const O = s.resources.indexOf(k.resource);
      O !== -1 && O !== s.index && (s.index = O);
    }
    l = "completed", v.forEach((O) => {
      O(Q);
    });
  }
  function z() {
    if (l !== "pending")
      return;
    w();
    const k = n.shift();
    if (k === void 0) {
      if (b.length) {
        g = setTimeout(() => {
          w(), l === "pending" && (lt(), A());
        }, s.timeout);
        return;
      }
      A();
      return;
    }
    const M = {
      status: "pending",
      resource: k,
      callback: (Q, H) => {
        Et(M, Q, H);
      }
    };
    b.push(M), u++, g = setTimeout(z, s.rotate), e(k, t, M.callback);
  }
  return setTimeout(z), I;
}
function io(s) {
  const t = {
    ...gr,
    ...s
  };
  let e = [];
  function i() {
    e = e.filter((a) => a().status === "pending");
  }
  function o(a, l, u) {
    const f = mr(
      t,
      a,
      l,
      (g, b) => {
        i(), u && u(g, b);
      }
    );
    return e.push(f), f;
  }
  function r(a) {
    return e.find((l) => a(l)) || null;
  }
  return {
    query: o,
    find: r,
    setIndex: (a) => {
      t.index = a;
    },
    getIndex: () => t.index,
    cleanup: i
  };
}
function Pe(s) {
  let t;
  if (typeof s.resources == "string")
    t = [s.resources];
  else if (t = s.resources, !(t instanceof Array) || !t.length)
    return null;
  return {
    resources: t,
    path: s.path || "/",
    maxURL: s.maxURL || 500,
    rotate: s.rotate || 750,
    timeout: s.timeout || 5e3,
    random: s.random === !0,
    index: s.index || 0,
    dataAfterTimeout: s.dataAfterTimeout !== !1
  };
}
const Wt = /* @__PURE__ */ Object.create(null), pt = [
  "https://api.simplesvg.com",
  "https://api.unisvg.com"
], jt = [];
for (; pt.length > 0; )
  pt.length === 1 || Math.random() > 0.5 ? jt.push(pt.shift()) : jt.push(pt.pop());
Wt[""] = Pe({
  resources: ["https://api.iconify.design"].concat(jt)
});
function yi(s, t) {
  const e = Pe(t);
  return e === null ? !1 : (Wt[s] = e, !0);
}
function Gt(s) {
  return Wt[s];
}
function br() {
  return Object.keys(Wt);
}
function wi() {
}
const ce = /* @__PURE__ */ Object.create(null);
function vr(s) {
  if (!ce[s]) {
    const t = Gt(s);
    if (!t)
      return;
    const e = io(t), i = {
      config: t,
      redundancy: e
    };
    ce[s] = i;
  }
  return ce[s];
}
function oo(s, t, e) {
  let i, o;
  if (typeof s == "string") {
    const r = ve(s);
    if (!r)
      return e(void 0, 424), wi;
    o = r.send;
    const n = vr(s);
    n && (i = n.redundancy);
  } else {
    const r = Pe(s);
    if (r) {
      i = io(r);
      const n = s.resources ? s.resources[0] : "", a = ve(n);
      a && (o = a.send);
    }
  }
  return !i || !o ? (e(void 0, 424), wi) : i.query(t, o, e)().abort;
}
const xi = "iconify2", $t = "iconify", so = $t + "-count", _i = $t + "-version", ro = 36e5, yr = 168;
function ye(s, t) {
  try {
    return s.getItem(t);
  } catch {
  }
}
function Re(s, t, e) {
  try {
    return s.setItem(t, e), !0;
  } catch {
  }
}
function $i(s, t) {
  try {
    s.removeItem(t);
  } catch {
  }
}
function we(s, t) {
  return Re(s, so, t.toString());
}
function xe(s) {
  return parseInt(ye(s, so)) || 0;
}
const it = {
  local: !0,
  session: !0
}, no = {
  local: /* @__PURE__ */ new Set(),
  session: /* @__PURE__ */ new Set()
};
let Ne = !1;
function wr(s) {
  Ne = s;
}
let Lt = typeof window > "u" ? {} : window;
function ao(s) {
  const t = s + "Storage";
  try {
    if (Lt && Lt[t] && typeof Lt[t].length == "number")
      return Lt[t];
  } catch {
  }
  it[s] = !1;
}
function lo(s, t) {
  const e = ao(s);
  if (!e)
    return;
  const i = ye(e, _i);
  if (i !== xi) {
    if (i) {
      const a = xe(e);
      for (let l = 0; l < a; l++)
        $i(e, $t + l.toString());
    }
    Re(e, _i, xi), we(e, 0);
    return;
  }
  const o = Math.floor(Date.now() / ro) - yr, r = (a) => {
    const l = $t + a.toString(), u = ye(e, l);
    if (typeof u == "string") {
      try {
        const f = JSON.parse(u);
        if (typeof f == "object" && typeof f.cached == "number" && f.cached > o && typeof f.provider == "string" && typeof f.data == "object" && typeof f.data.prefix == "string" && t(f, a))
          return !0;
      } catch {
      }
      $i(e, l);
    }
  };
  let n = xe(e);
  for (let a = n - 1; a >= 0; a--)
    r(a) || (a === n - 1 ? (n--, we(e, n)) : no[s].add(a));
}
function co() {
  if (!Ne) {
    wr(!0);
    for (const s in it)
      lo(s, (t) => {
        const e = t.data, i = t.provider, o = e.prefix, r = J(
          i,
          o
        );
        if (!Me(r, e).length)
          return !1;
        const n = e.lastModified || -1;
        return r.lastModifiedCached = r.lastModifiedCached ? Math.min(r.lastModifiedCached, n) : n, !0;
      });
  }
}
function xr(s, t) {
  const e = s.lastModifiedCached;
  if (e && e >= t)
    return e === t;
  if (s.lastModifiedCached = t, e)
    for (const i in it)
      lo(i, (o) => {
        const r = o.data;
        return o.provider !== s.provider || r.prefix !== s.prefix || r.lastModified === t;
      });
  return !0;
}
function _r(s, t) {
  Ne || co();
  function e(i) {
    let o;
    if (!it[i] || !(o = ao(i)))
      return;
    const r = no[i];
    let n;
    if (r.size)
      r.delete(n = Array.from(r).shift());
    else if (n = xe(o), !we(o, n + 1))
      return;
    const a = {
      cached: Math.floor(Date.now() / ro),
      provider: s.provider,
      data: t
    };
    return Re(
      o,
      $t + n.toString(),
      JSON.stringify(a)
    );
  }
  t.lastModified && !xr(s, t.lastModified) || Object.keys(t.icons).length && (t.not_found && (t = Object.assign({}, t), delete t.not_found), e("local") || e("session"));
}
function ki() {
}
function $r(s) {
  s.iconsLoaderFlag || (s.iconsLoaderFlag = !0, setTimeout(() => {
    s.iconsLoaderFlag = !1, ur(s);
  }));
}
function kr(s, t) {
  s.iconsToLoad ? s.iconsToLoad = s.iconsToLoad.concat(t).sort() : s.iconsToLoad = t, s.iconsQueueFlag || (s.iconsQueueFlag = !0, setTimeout(() => {
    s.iconsQueueFlag = !1;
    const { provider: e, prefix: i } = s, o = s.iconsToLoad;
    delete s.iconsToLoad;
    let r;
    if (!o || !(r = ve(e)))
      return;
    r.prepare(e, i, o).forEach((a) => {
      oo(e, a, (l) => {
        if (typeof l != "object")
          a.icons.forEach((u) => {
            s.missing.add(u);
          });
        else
          try {
            const u = Me(
              s,
              l
            );
            if (!u.length)
              return;
            const f = s.pendingIcons;
            f && u.forEach((g) => {
              f.delete(g);
            }), _r(s, l);
          } catch (u) {
            console.error(u);
          }
        $r(s);
      });
    });
  }));
}
const je = (s, t) => {
  const e = fr(s, !0, Yi()), i = dr(e);
  if (!i.pending.length) {
    let l = !0;
    return t && setTimeout(() => {
      l && t(
        i.loaded,
        i.missing,
        i.pending,
        ki
      );
    }), () => {
      l = !1;
    };
  }
  const o = /* @__PURE__ */ Object.create(null), r = [];
  let n, a;
  return i.pending.forEach((l) => {
    const { provider: u, prefix: f } = l;
    if (f === a && u === n)
      return;
    n = u, a = f, r.push(J(u, f));
    const g = o[u] || (o[u] = /* @__PURE__ */ Object.create(null));
    g[f] || (g[f] = []);
  }), i.pending.forEach((l) => {
    const { provider: u, prefix: f, name: g } = l, b = J(u, f), v = b.pendingIcons || (b.pendingIcons = /* @__PURE__ */ new Set());
    v.has(g) || (v.add(g), o[u][f].push(g));
  }), r.forEach((l) => {
    const { provider: u, prefix: f } = l;
    o[u][f].length && kr(l, o[u][f]);
  }), t ? pr(t, i, r) : ki;
}, Sr = (s) => new Promise((t, e) => {
  const i = typeof s == "string" ? St(s, !0) : s;
  if (!i) {
    e(s);
    return;
  }
  je([i || s], (o) => {
    if (o.length && i) {
      const r = _t(i);
      if (r) {
        t({
          ...kt,
          ...r
        });
        return;
      }
    }
    e(s);
  });
});
function Er(s) {
  try {
    const t = typeof s == "string" ? JSON.parse(s) : s;
    if (typeof t.body == "string")
      return {
        ...t
      };
  } catch {
  }
}
function Cr(s, t) {
  const e = typeof s == "string" ? St(s, !0, !0) : null;
  if (!e) {
    const r = Er(s);
    return {
      value: s,
      data: r
    };
  }
  const i = _t(e);
  if (i !== void 0 || !e.prefix)
    return {
      value: s,
      name: e,
      data: i
      // could be 'null' -> icon is missing
    };
  const o = je([e], () => t(s, e, _t(e)));
  return {
    value: s,
    name: e,
    loading: o
  };
}
function de(s) {
  return s.hasAttribute("inline");
}
let uo = !1;
try {
  uo = navigator.vendor.indexOf("Apple") === 0;
} catch {
}
function Tr(s, t) {
  switch (t) {
    case "svg":
    case "bg":
    case "mask":
      return t;
  }
  return t !== "style" && (uo || s.indexOf("<a") === -1) ? "svg" : s.indexOf("currentColor") === -1 ? "bg" : "mask";
}
const Ar = /(-?[0-9.]*[0-9]+[0-9.]*)/g, Or = /^-?[0-9.]*[0-9]+[0-9.]*$/g;
function _e(s, t, e) {
  if (t === 1)
    return s;
  if (e = e || 100, typeof s == "number")
    return Math.ceil(s * t * e) / e;
  if (typeof s != "string")
    return s;
  const i = s.split(Ar);
  if (i === null || !i.length)
    return s;
  const o = [];
  let r = i.shift(), n = Or.test(r);
  for (; ; ) {
    if (n) {
      const a = parseFloat(r);
      isNaN(a) ? o.push(r) : o.push(Math.ceil(a * t * e) / e);
    } else
      o.push(r);
    if (r = i.shift(), r === void 0)
      return o.join("");
    n = !n;
  }
}
function ho(s, t) {
  const e = {
    ...kt,
    ...s
  }, i = {
    ...Zi,
    ...t
  }, o = {
    left: e.left,
    top: e.top,
    width: e.width,
    height: e.height
  };
  let r = e.body;
  [e, i].forEach((v) => {
    const w = [], T = v.hFlip, P = v.vFlip;
    let I = v.rotate;
    T ? P ? I += 2 : (w.push(
      "translate(" + (o.width + o.left).toString() + " " + (0 - o.top).toString() + ")"
    ), w.push("scale(-1 1)"), o.top = o.left = 0) : P && (w.push(
      "translate(" + (0 - o.left).toString() + " " + (o.height + o.top).toString() + ")"
    ), w.push("scale(1 -1)"), o.top = o.left = 0);
    let A;
    switch (I < 0 && (I -= Math.floor(I / 4) * 4), I = I % 4, I) {
      case 1:
        A = o.height / 2 + o.top, w.unshift(
          "rotate(90 " + A.toString() + " " + A.toString() + ")"
        );
        break;
      case 2:
        w.unshift(
          "rotate(180 " + (o.width / 2 + o.left).toString() + " " + (o.height / 2 + o.top).toString() + ")"
        );
        break;
      case 3:
        A = o.width / 2 + o.left, w.unshift(
          "rotate(-90 " + A.toString() + " " + A.toString() + ")"
        );
        break;
    }
    I % 2 === 1 && (o.left !== o.top && (A = o.left, o.left = o.top, o.top = A), o.width !== o.height && (A = o.width, o.width = o.height, o.height = A)), w.length && (r = '<g transform="' + w.join(" ") + '">' + r + "</g>");
  });
  const n = i.width, a = i.height, l = o.width, u = o.height;
  let f, g;
  return n === null ? (g = a === null ? "1em" : a === "auto" ? u : a, f = _e(g, l / u)) : (f = n === "auto" ? l : n, g = a === null ? _e(f, u / l) : a === "auto" ? u : a), {
    attributes: {
      width: f.toString(),
      height: g.toString(),
      viewBox: o.left.toString() + " " + o.top.toString() + " " + l.toString() + " " + u.toString()
    },
    body: r
  };
}
const Ir = () => {
  let s;
  try {
    if (s = fetch, typeof s == "function")
      return s;
  } catch {
  }
};
let Vt = Ir();
function Lr(s) {
  Vt = s;
}
function Mr() {
  return Vt;
}
function Pr(s, t) {
  const e = Gt(s);
  if (!e)
    return 0;
  let i;
  if (!e.maxURL)
    i = 0;
  else {
    let o = 0;
    e.resources.forEach((n) => {
      o = Math.max(o, n.length);
    });
    const r = t + ".json?icons=";
    i = e.maxURL - o - e.path.length - r.length;
  }
  return i;
}
function Rr(s) {
  return s === 404;
}
const Nr = (s, t, e) => {
  const i = [], o = Pr(s, t), r = "icons";
  let n = {
    type: r,
    provider: s,
    prefix: t,
    icons: []
  }, a = 0;
  return e.forEach((l, u) => {
    a += l.length + 1, a >= o && u > 0 && (i.push(n), n = {
      type: r,
      provider: s,
      prefix: t,
      icons: []
    }, a = l.length), n.icons.push(l);
  }), i.push(n), i;
};
function jr(s) {
  if (typeof s == "string") {
    const t = Gt(s);
    if (t)
      return t.path;
  }
  return "/";
}
const qr = (s, t, e) => {
  if (!Vt) {
    e("abort", 424);
    return;
  }
  let i = jr(t.provider);
  switch (t.type) {
    case "icons": {
      const r = t.prefix, a = t.icons.join(","), l = new URLSearchParams({
        icons: a
      });
      i += r + ".json?" + l.toString();
      break;
    }
    case "custom": {
      const r = t.uri;
      i += r.slice(0, 1) === "/" ? r.slice(1) : r;
      break;
    }
    default:
      e("abort", 400);
      return;
  }
  let o = 503;
  Vt(s + i).then((r) => {
    const n = r.status;
    if (n !== 200) {
      setTimeout(() => {
        e(Rr(n) ? "abort" : "next", n);
      });
      return;
    }
    return o = 501, r.json();
  }).then((r) => {
    if (typeof r != "object" || r === null) {
      setTimeout(() => {
        r === 404 ? e("abort", r) : e("next", o);
      });
      return;
    }
    setTimeout(() => {
      e("success", r);
    });
  }).catch(() => {
    e("next", o);
  });
}, Ur = {
  prepare: Nr,
  send: qr
};
function Si(s, t) {
  switch (s) {
    case "local":
    case "session":
      it[s] = t;
      break;
    case "all":
      for (const e in it)
        it[e] = t;
      break;
  }
}
function po() {
  vi("", Ur), Yi(!0);
  let s;
  try {
    s = window;
  } catch {
  }
  if (s) {
    if (co(), s.IconifyPreload !== void 0) {
      const e = s.IconifyPreload, i = "Invalid IconifyPreload syntax.";
      typeof e == "object" && e !== null && (e instanceof Array ? e : [e]).forEach((o) => {
        try {
          // Check if item is an object and not null/array
          (typeof o != "object" || o === null || o instanceof Array || // Check for 'icons' and 'prefix'
          typeof o.icons != "object" || typeof o.prefix != "string" || // Add icon set
          !bi(o)) && console.error(i);
        } catch {
          console.error(i);
        }
      });
    }
    if (s.IconifyProviders !== void 0) {
      const e = s.IconifyProviders;
      if (typeof e == "object" && e !== null)
        for (const i in e) {
          const o = "IconifyProviders[" + i + "] is invalid.";
          try {
            const r = e[i];
            if (typeof r != "object" || !r || r.resources === void 0)
              continue;
            yi(i, r) || console.error(o);
          } catch {
            console.error(o);
          }
        }
    }
  }
  return {
    enableCache: (e) => Si(e, !0),
    disableCache: (e) => Si(e, !1),
    iconExists: lr,
    getIcon: cr,
    listIcons: ar,
    addIcon: to,
    addCollection: bi,
    calculateSize: _e,
    buildIcon: ho,
    loadIcons: je,
    loadIcon: Sr,
    addAPIProvider: yi,
    _api: {
      getAPIConfig: Gt,
      setAPIModule: vi,
      sendAPIQuery: oo,
      setFetch: Lr,
      getFetch: Mr,
      listAPIProviders: br
    }
  };
}
function fo(s, t) {
  let e = s.indexOf("xlink:") === -1 ? "" : ' xmlns:xlink="http://www.w3.org/1999/xlink"';
  for (const i in t)
    e += " " + i + '="' + t[i] + '"';
  return '<svg xmlns="http://www.w3.org/2000/svg"' + e + ">" + s + "</svg>";
}
function Br(s) {
  return s.replace(/"/g, "'").replace(/%/g, "%25").replace(/#/g, "%23").replace(/</g, "%3C").replace(/>/g, "%3E").replace(/\s+/g, " ");
}
function Fr(s) {
  return 'url("data:image/svg+xml,' + Br(s) + '")';
}
const $e = {
  "background-color": "currentColor"
}, go = {
  "background-color": "transparent"
}, Ei = {
  image: "var(--svg)",
  repeat: "no-repeat",
  size: "100% 100%"
}, Ci = {
  "-webkit-mask": $e,
  mask: $e,
  background: go
};
for (const s in Ci) {
  const t = Ci[s];
  for (const e in Ei)
    t[s + "-" + e] = Ei[e];
}
function Ti(s) {
  return s + (s.match(/^[-0-9.]+$/) ? "px" : "");
}
function Dr(s, t, e) {
  const i = document.createElement("span");
  let o = s.body;
  o.indexOf("<a") !== -1 && (o += "<!-- " + Date.now() + " -->");
  const r = s.attributes, n = fo(o, {
    ...r,
    width: t.width + "",
    height: t.height + ""
  }), a = Fr(n), l = i.style, u = {
    "--svg": a,
    width: Ti(r.width),
    height: Ti(r.height),
    ...e ? $e : go
  };
  for (const f in u)
    l.setProperty(f, u[f]);
  return i;
}
function Vr(s) {
  const t = document.createElement("span");
  return t.innerHTML = fo(s.body, s.attributes), t.firstChild;
}
function Ai(s, t) {
  const e = t.icon.data, i = t.customisations, o = ho(e, i);
  i.preserveAspectRatio && (o.attributes.preserveAspectRatio = i.preserveAspectRatio);
  const r = t.renderedMode;
  let n;
  switch (r) {
    case "svg":
      n = Vr(o);
      break;
    default:
      n = Dr(o, {
        ...kt,
        ...e
      }, r === "mask");
  }
  const a = Array.from(s.childNodes).find((l) => {
    const u = l.tagName && l.tagName.toUpperCase();
    return u === "SPAN" || u === "SVG";
  });
  a ? n.tagName === "SPAN" && a.tagName === n.tagName ? a.setAttribute("style", n.getAttribute("style")) : s.replaceChild(n, a) : s.appendChild(n);
}
const ue = "data-style";
function Oi(s, t) {
  let e = Array.from(s.childNodes).find((i) => i.hasAttribute && i.hasAttribute(ue));
  e || (e = document.createElement("style"), e.setAttribute(ue, ue), s.appendChild(e)), e.textContent = ":host{display:inline-block;vertical-align:" + (t ? "-0.125em" : "0") + "}span,svg{display:block}";
}
function Ii(s, t, e) {
  const i = e && (e.rendered ? e : e.lastRender);
  return {
    rendered: !1,
    inline: t,
    icon: s,
    lastRender: i
  };
}
function zr(s = "iconify-icon") {
  let t, e;
  try {
    t = window.customElements, e = window.HTMLElement;
  } catch {
    return;
  }
  if (!t || !e)
    return;
  const i = t.get(s);
  if (i)
    return i;
  const o = [
    // Icon
    "icon",
    // Mode
    "mode",
    "inline",
    // Customisations
    "width",
    "height",
    "rotate",
    "flip"
  ], r = class extends e {
    /**
     * Constructor
     */
    constructor() {
      super();
      // Root
      Ot(this, "_shadowRoot");
      // State
      Ot(this, "_state");
      // Attributes check queued
      Ot(this, "_checkQueued", !1);
      const l = this._shadowRoot = this.attachShadow({
        mode: "open"
      }), u = de(this);
      Oi(l, u), this._state = Ii({
        value: ""
      }, u), this._queueCheck();
    }
    /**
     * Observed attributes
     */
    static get observedAttributes() {
      return o.slice(0);
    }
    /**
     * Observed properties that are different from attributes
     *
     * Experimental! Need to test with various frameworks that support it
     */
    /*
    static get properties() {
        return {
            inline: {
                type: Boolean,
                reflect: true,
            },
            // Not listing other attributes because they are strings or combination
            // of string and another type. Cannot have multiple types
        };
    }
    */
    /**
     * Attribute has changed
     */
    attributeChangedCallback(l) {
      if (l === "inline") {
        const u = de(this), f = this._state;
        u !== f.inline && (f.inline = u, Oi(this._shadowRoot, u));
      } else
        this._queueCheck();
    }
    /**
     * Get/set icon
     */
    get icon() {
      const l = this.getAttribute("icon");
      if (l && l.slice(0, 1) === "{")
        try {
          return JSON.parse(l);
        } catch {
        }
      return l;
    }
    set icon(l) {
      typeof l == "object" && (l = JSON.stringify(l)), this.setAttribute("icon", l);
    }
    /**
     * Get/set inline
     */
    get inline() {
      return de(this);
    }
    set inline(l) {
      this.setAttribute("inline", l ? "true" : null);
    }
    /**
     * Restart animation
     */
    restartAnimation() {
      const l = this._state;
      if (l.rendered) {
        const u = this._shadowRoot;
        if (l.renderedMode === "svg")
          try {
            u.lastChild.setCurrentTime(0);
            return;
          } catch {
          }
        Ai(u, l);
      }
    }
    /**
     * Get status
     */
    get status() {
      const l = this._state;
      return l.rendered ? "rendered" : l.icon.data === null ? "failed" : "loading";
    }
    /**
     * Queue attributes re-check
     */
    _queueCheck() {
      this._checkQueued || (this._checkQueued = !0, setTimeout(() => {
        this._check();
      }));
    }
    /**
     * Check for changes
     */
    _check() {
      if (!this._checkQueued)
        return;
      this._checkQueued = !1;
      const l = this._state, u = this.getAttribute("icon");
      if (u !== l.icon.value) {
        this._iconChanged(u);
        return;
      }
      if (!l.rendered)
        return;
      const f = this.getAttribute("mode"), g = gi(this);
      (l.attrMode !== f || tr(l.customisations, g)) && this._renderIcon(l.icon, g, f);
    }
    /**
     * Icon value has changed
     */
    _iconChanged(l) {
      const u = Cr(l, (f, g, b) => {
        const v = this._state;
        if (v.rendered || this.getAttribute("icon") !== f)
          return;
        const w = {
          value: f,
          name: g,
          data: b
        };
        w.data ? this._gotIconData(w) : v.icon = w;
      });
      u.data ? this._gotIconData(u) : this._state = Ii(u, this._state.inline, this._state);
    }
    /**
     * Got new icon data, icon is ready to (re)render
     */
    _gotIconData(l) {
      this._checkQueued = !1, this._renderIcon(l, gi(this), this.getAttribute("mode"));
    }
    /**
     * Re-render based on icon data
     */
    _renderIcon(l, u, f) {
      const g = Tr(l.data.body, f), b = this._state.inline;
      Ai(this._shadowRoot, this._state = {
        rendered: !0,
        icon: l,
        inline: b,
        customisations: u,
        attrMode: f,
        renderedMode: g
      });
    }
  };
  o.forEach((a) => {
    a in r.prototype || Object.defineProperty(r.prototype, a, {
      get: function() {
        return this.getAttribute(a);
      },
      set: function(l) {
        this.setAttribute(a, l);
      }
    });
  });
  const n = po();
  for (const a in n)
    r[a] = r.prototype[a] = n[a];
  return t.define(s, r), r;
}
const Hr = zr() || po(), { enableCache: gn, disableCache: mn, iconExists: bn, getIcon: vn, listIcons: yn, addIcon: wn, addCollection: xn, calculateSize: _n, buildIcon: $n, loadIcons: kn, loadIcon: Sn, addAPIProvider: En, _api: Cn } = Hr;
class Wr extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      ...super.properties,
      icon: { type: String },
      tooltip: { type: String },
      tooltip_open: { type: Boolean },
      size: { type: String }
    };
  }
  _showTooltip() {
    this.tooltip_open ? this.tooltip_open = !1 : this.tooltip_open = !0;
  }
  render() {
    const t = this.tooltip ? d`<div class="tooltip" ?hidden=${this.tooltip_open}>${this.tooltip}</div>` : null;
    return d`
      <iconify-icon icon=${this.icon} width="${this.size}" @click=${this._showTooltip}></iconify-icon>
      ${t}
    `;
  }
}
window.customElements.define("dt-icon", Wr);
class Gr extends j {
  static get properties() {
    return {
      ...super.properties,
      title: { type: String },
      isOpen: { type: Boolean },
      canEdit: { type: Boolean, state: !0 },
      metadata: { type: Object },
      center: { type: Array },
      mapboxToken: {
        type: String,
        attribute: "mapbox-token"
      }
    };
  }
  static get styles() {
    return [
      $`
        .map {
          width: 100%;
          min-width: 50vw;
          min-height: 50dvb;
        }
      `
    ];
  }
  constructor() {
    super(), this.addEventListener("open", (t) => {
      this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("open")), this.isOpen = !0;
    }), this.addEventListener("close", (t) => {
      this.shadowRoot.querySelector("dt-modal").dispatchEvent(new Event("close")), this.isOpen = !1;
    });
  }
  connectedCallback() {
    if (super.connectedCallback(), this.canEdit = !this.metadata, window.mapboxgl)
      this.initMap();
    else {
      let t = document.createElement("script");
      t.src = "https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js", t.onload = this.initMap.bind(this), document.body.appendChild(t), console.log("injected script");
    }
  }
  initMap() {
    if (!this.isOpen || !window.mapboxgl || !this.mapboxToken)
      return;
    const t = this.shadowRoot.querySelector("#map");
    if (t && !this.map) {
      this.map = new window.mapboxgl.Map({
        accessToken: this.mapboxToken,
        container: t,
        style: "mapbox://styles/mapbox/streets-v12",
        // style URL
        minZoom: 1
      }), this.map.on("load", () => this.map.resize()), this.center && this.center.length && (this.map.setCenter(this.center), this.map.setZoom(15));
      const e = new mapboxgl.NavigationControl();
      this.map.addControl(e, "bottom-right"), this.addPinFromMetadata(), this.map.on("click", (i) => {
        this.canEdit && (this.marker ? this.marker.setLngLat(i.lngLat) : this.marker = new mapboxgl.Marker().setLngLat(i.lngLat).addTo(this.map));
      });
    }
  }
  addPinFromMetadata() {
    if (this.metadata) {
      const { lng: t, lat: e, level: i } = this.metadata;
      let o = 15;
      i === "admin0" ? o = 3 : i === "admin1" ? o = 6 : i === "admin2" && (o = 10), this.map && (this.map.setCenter([t, e]), this.map.setZoom(o), this.marker = new mapboxgl.Marker().setLngLat([t, e]).addTo(this.map));
    }
  }
  updated(t) {
    window.mapboxgl && (t.has("metadata") && this.metadata && this.metadata.lat && this.addPinFromMetadata(), t.has("isOpen") && this.isOpen && this.initMap());
  }
  onClose(t) {
    var e;
    ((e = t == null ? void 0 : t.detail) == null ? void 0 : e.action) === "button" && this.marker && this.dispatchEvent(new CustomEvent("submit", {
      detail: {
        location: this.marker.getLngLat()
      }
    }));
  }
  render() {
    var t;
    return d`      
      <dt-modal
        .title=${(t = this.metadata) == null ? void 0 : t.label}
        ?isopen=${this.isOpen}
        hideButton
        @close=${this.onClose}
      >
        <div slot="content">
          <div class="map" id="map"></div>
        </div>
       
        ${this.canEdit ? d`<div slot="close-button">${S("Save")}</div>` : null}
      </dt-modal>
      
      <link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
    `;
  }
}
window.customElements.define("dt-map-modal", Gr);
class Kr extends U {
  static get properties() {
    return {
      id: { type: String, reflect: !0 },
      placeholder: { type: String },
      mapboxToken: { type: String, attribute: "mapbox-token" },
      googleToken: { type: String, attribute: "google-token" },
      metadata: { type: Object },
      disabled: { type: Boolean },
      open: {
        type: Boolean,
        state: !0
      },
      query: {
        type: String,
        state: !0
      },
      activeIndex: {
        type: Number,
        state: !0
      },
      containerHeight: {
        type: Number,
        state: !0
      },
      loading: { type: Boolean },
      saved: { type: Boolean },
      filteredOptions: { type: Array, state: !0 }
    };
  }
  static get styles() {
    return [
      $`
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
      `,
      $`
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
      `,
      $`
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
      `
    ];
  }
  constructor() {
    super(), this.activeIndex = -1, this.filteredOptions = [], this.detectTap = !1, this.debounceTimer = null;
  }
  connectedCallback() {
    super.connectedCallback(), this.addEventListener("autofocus", async () => {
      await this.updateComplete;
      const t = this.shadowRoot.querySelector("input");
      t && t.focus();
    }), this.mapboxToken && (this.mapboxService = new Ks(this.mapboxToken)), this.googleToken && (this.googleGeocodeService = new Zs(this.googleToken, window, document));
  }
  disconnectedCallback() {
    super.disconnectedCallback(), this.removeEventListener("autofocus", this.handleAutofocus);
  }
  updated() {
    this._scrollOptionListToActive();
    const t = this.shadowRoot.querySelector(".input-group");
    t.style.getPropertyValue("--container-width") || t.style.setProperty(
      "--container-width",
      `${t.clientWidth}px`
    );
  }
  /**
   * When navigating via keyboard, keep active element within visible area of option list
   * @private
   */
  _scrollOptionListToActive() {
    const t = this.shadowRoot.querySelector(".option-list"), e = this.shadowRoot.querySelector("button.active");
    if (t && e) {
      const i = e.offsetTop, o = e.offsetTop + e.clientHeight, r = t.scrollTop, n = t.scrollTop + t.clientHeight;
      o > n ? t.scrollTo({
        top: o - t.clientHeight,
        behavior: "smooth"
      }) : i < r && t.scrollTo({ top: i, behavior: "smooth" });
    }
  }
  _clickOption(t) {
    const e = t.currentTarget ?? t.target;
    e && e.value && this._select(JSON.parse(e.value));
  }
  _touchStart(t) {
    t.target && (this.detectTap = !1);
  }
  _touchMove(t) {
    t.target && (this.detectTap = !0);
  }
  _touchEnd(t) {
    this.detectTap || (t.target && t.target.value && this._clickOption(t), this.detectTap = !1);
  }
  _keyboardSelectOption() {
    this.activeIndex > -1 && (this.activeIndex < this.filteredOptions.length ? this._select(this.filteredOptions[this.activeIndex]) : this._select({
      value: this.query,
      label: this.query
    }));
  }
  async _select(t) {
    if (t.place_id && this.googleGeocodeService) {
      this.loading = !0;
      const o = await this.googleGeocodeService.getPlaceDetails(t.label, this.locale);
      this.loading = !1, o && (t.lat = o.geometry.location.lat, t.lng = o.geometry.location.lng, t.level = o.types && o.types.length ? o.types[0] : null);
    }
    const e = {
      detail: {
        metadata: t
      },
      bubbles: !1
    };
    this.dispatchEvent(new CustomEvent("select", e)), this.metadata = t;
    const i = this.shadowRoot.querySelector("input");
    i && (i.value = t == null ? void 0 : t.label), this.open = !1, this.activeIndex = -1;
  }
  get _focusTarget() {
    let t = this._field;
    return this.metadata && (t = this.shadowRoot.querySelector("button") || t), t;
  }
  _inputFocusIn() {
    this.activeIndex = -1;
  }
  _inputFocusOut(t) {
    (!t.relatedTarget || !["BUTTON", "LI"].includes(t.relatedTarget.nodeName)) && (this.open = !1);
  }
  _inputKeyDown(t) {
    switch (t.keyCode || t.which) {
      case 38:
        this.open = !0, this._listHighlightPrevious();
        break;
      case 40:
        this.open = !0, this._listHighlightNext();
        break;
      case 9:
        this.activeIndex < 0 ? this.open = !1 : t.preventDefault(), this._keyboardSelectOption();
        break;
      case 13:
        this._keyboardSelectOption();
        break;
      case 27:
        this.open = !1, this.activeIndex = -1;
        break;
      default:
        this.open = !0;
        break;
    }
  }
  _inputKeyUp(t) {
    const e = t.keyCode || t.which, i = [9, 13];
    t.target.value && !i.includes(e) && (this.open = !0), this.query = t.target.value;
  }
  _listHighlightNext() {
    this.activeIndex = Math.min(
      this.filteredOptions.length,
      this.activeIndex + 1
    );
  }
  _listHighlightPrevious() {
    this.activeIndex = Math.max(0, this.activeIndex - 1);
  }
  /**
   * Filter to options that:
   *   1: are not selected
   *   2: match the search query
   * @private
   */
  async _filterOptions() {
    if (this.query) {
      if (this.googleToken && this.googleGeocodeService) {
        this.loading = !0;
        try {
          const t = await this.googleGeocodeService.getPlacePredictions(this.query, this.locale);
          this.filteredOptions = (t || []).map((e) => ({
            label: e.description,
            place_id: e.place_id,
            source: "user",
            raw: e
          })), this.loading = !1;
        } catch (t) {
          console.error(t), this.error = !0, this.loading = !1;
          return;
        }
      } else if (this.mapboxToken && this.mapboxService) {
        this.loading = !0;
        const t = await this.mapboxService.searchPlaces(this.query, this.locale);
        this.filteredOptions = t.map((e) => ({
          lng: e.center[0],
          lat: e.center[1],
          level: e.place_type[0],
          label: e.place_name,
          source: "user"
        })), this.loading = !1;
      }
    }
    return this.filteredOptions;
  }
  willUpdate(t) {
    if (super.willUpdate(t), t && (t.has("query") && (this.error = !1, clearTimeout(this.debounceTimer), this.debounceTimer = setTimeout(() => this._filterOptions(), 300)), !this.containerHeight && this.shadowRoot.children && this.shadowRoot.children.length)) {
      const i = this.shadowRoot.querySelector(".input-group");
      i && (this.containerHeight = i.offsetHeight);
    }
  }
  _change() {
  }
  _delete() {
    const t = {
      detail: {
        metadata: this.metadata
      },
      bubbles: !1
    };
    this.dispatchEvent(new CustomEvent("delete", t));
  }
  _openMapModal() {
    this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"));
  }
  async _onMapModalSubmit(t) {
    var e, i;
    if ((i = (e = t == null ? void 0 : t.detail) == null ? void 0 : e.location) != null && i.lat) {
      const { location: o } = t == null ? void 0 : t.detail, { lat: r, lng: n } = o;
      if (this.googleGeocodeService) {
        const a = await this.googleGeocodeService.reverseGeocode(n, r, this.locale);
        if (a && a.length) {
          const l = a[0];
          this._select({
            lng: l.geometry.location.lng,
            lat: l.geometry.location.lat,
            level: l.types && l.types.length ? l.types[0] : null,
            label: l.formatted_address,
            source: "user"
          });
        }
      } else if (this.mapboxService) {
        const a = await this.mapboxService.reverseGeocode(n, r, this.locale);
        if (a && a.length) {
          const l = a[0];
          this._select({
            lng: l.center[0],
            lat: l.center[1],
            level: l.place_type[0],
            label: l.place_name,
            source: "user"
          });
        }
      }
    }
  }
  _renderOption(t, e, i) {
    return d`
      <li tabindex="-1">
        <button
          value="${JSON.stringify(t)}"
          type="button"
          @click="${this._clickOption}"
          @touchstart="${this._touchStart}"
          @touchmove="${this._touchMove}"
          @touchend="${this._touchEnd}"
          tabindex="-1"
          class="${this.activeIndex > -1 && this.activeIndex === e ? "active" : ""}"
        >
          ${i ?? t.label}
        </button>
      </li>
    `;
  }
  _renderOptions() {
    let t = [];
    return this.filteredOptions.length ? t.push(...this.filteredOptions.map((e, i) => this._renderOption(e, i))) : this.loading ? t.push(d`<li><div>${S("Loading...")}</div></li>`) : t.push(d`<li><div>${S("No Data Available")}</div></li>`), t.push(this._renderOption({
      value: this.query,
      label: this.query
    }, (this.filteredOptions || []).length, d`<strong>${S("Use")}: "${this.query}"</strong>`)), t;
  }
  render() {
    var o, r, n, a;
    const t = {
      display: this.open ? "block" : "none",
      top: this.containerHeight ? `${this.containerHeight}px` : "2.5rem"
    }, e = !!((o = this.metadata) != null && o.label), i = ((r = this.metadata) == null ? void 0 : r.lat) && ((n = this.metadata) == null ? void 0 : n.lng);
    return d`
      <div class="input-group">
        <div class="field-container">
          <input
            type="text"
            class="${this.disabled ? "disabled" : null}"
            placeholder="${this.placeholder}"
            .value="${(a = this.metadata) == null ? void 0 : a.label}"
            .disabled=${e && i || this.disabled}
            @focusin="${this._inputFocusIn}"
            @blur="${this._inputFocusOut}"
            @keydown="${this._inputKeyDown}"
            @keyup="${this._inputKeyUp}"
          />

          ${e && i ? d`
          <button
            class="input-addon btn-map"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:map"></dt-icon>
          </button>
          ` : null}
          ${e ? d`
          <button
            class="input-addon btn-delete"
            @click=${this._delete}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:trash-can-outline"></dt-icon>
          </button>
          ` : d`
          <button
            class="input-addon btn-pin"
            @click=${this._openMapModal}
            ?disabled=${this.disabled}
          >
            <dt-icon icon="mdi:map-marker-radius"></dt-icon>
          </button>
          `}
        </div>
        <ul class="option-list" style=${Z(t)}>
          ${this._renderOptions()}
        </ul>
        ${this.touched && this.invalid || this.error ? d`<dt-exclamation-circle class="icon-overlay alert"></dt-exclamation-circle>` : null}
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
      </div>

      <dt-map-modal
        .metadata=${this.metadata}
        mapbox-token="${this.mapboxToken}"
        @submit=${this._onMapModalSubmit}
      ></dt-map-modal>

`;
  }
}
window.customElements.define("dt-location-map-item", Kr);
class Zr extends R {
  static get properties() {
    return {
      ...super.properties,
      placeholder: { type: String },
      value: {
        type: Array,
        reflect: !0
      },
      locations: {
        type: Array,
        state: !0
      },
      open: {
        type: Boolean,
        state: !0
      },
      onchange: { type: String },
      mapboxToken: {
        type: String,
        attribute: "mapbox-token"
      },
      googleToken: {
        type: String,
        attribute: "google-token"
      }
    };
  }
  static get styles() {
    return [
      ...super.styles,
      $`
        :host {
          font-family: Helvetica, Arial, sans-serif;
        }
        .input-group {
          display: flex;
        }

        .field-container {
          position: relative;
        }
      `
    ];
  }
  constructor() {
    super(), this.value = [], this.locations = [{
      id: Date.now()
    }];
  }
  _setFormValue(t) {
    super._setFormValue(t), this.internals.setFormValue(JSON.stringify(t));
  }
  willUpdate(...t) {
    super.willUpdate(...t), this.value && this.value.filter((e) => !e.id) && (this.value = [
      ...this.value.map((e) => ({
        ...e,
        id: e.grid_meta_id
      }))
    ]), this.updateLocationList();
  }
  firstUpdated(...t) {
    super.firstUpdated(...t), this.internals.setFormValue(JSON.stringify(this.value));
  }
  updated(t) {
    var e, i;
    if (t.has("value")) {
      const o = t.get("value");
      o && (o == null ? void 0 : o.length) !== ((e = this.value) == null ? void 0 : e.length) && this.focusNewLocation();
    }
    if (t.has("locations")) {
      const o = t.get("locations");
      o && (o == null ? void 0 : o.length) !== ((i = this.locations) == null ? void 0 : i.length) && this.focusNewLocation();
    }
  }
  focusNewLocation() {
    const t = this.shadowRoot.querySelectorAll("dt-location-map-item");
    t && t.length && t[t.length - 1].dispatchEvent(new Event("autofocus"));
  }
  updateLocationList() {
    !this.disabled && (this.open || !this.value || !this.value.length) ? (this.open = !0, this.locations = [
      ...(this.value || []).filter((t) => t.label),
      {
        id: Date.now()
      }
    ]) : this.locations = [
      ...(this.value || []).filter((t) => t.label)
    ];
  }
  selectLocation(t) {
    const e = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value
      }
    }), i = {
      ...t.detail.metadata,
      id: Date.now()
    };
    this.value = [
      ...(this.value || []).filter((o) => o.label),
      i
    ], this.updateLocationList(), e.detail.newValue = this.value, this.dispatchEvent(e), this._setFormValue(this.value);
  }
  deleteItem(t) {
    var r;
    const e = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value
      }
    }), i = (r = t.detail) == null ? void 0 : r.metadata, o = i == null ? void 0 : i.grid_meta_id;
    o ? this.value = (this.value || []).filter((n) => n.grid_meta_id !== o) : this.value = (this.value || []).filter((n) => n.lat !== i.lat && n.lng !== i.lng), this.updateLocationList(), e.detail.newValue = this.value, this.dispatchEvent(e), this._setFormValue(this.value);
  }
  addNew() {
    this.open = !0, this.updateLocationList();
  }
  renderItem(t) {
    return d`
      <dt-location-map-item
        placeholder="${this.placeholder}"
        .metadata=${t}
        mapbox-token="${this.mapboxToken}"
        google-token="${this.googleToken}"
        @delete=${this.deleteItem}
        @select=${this.selectLocation}
        ?disabled=${this.disabled}
      ></dt-location-map-item>
    `;
  }
  render() {
    return [...this.value || []], d`
      ${this.labelTemplate()}

      ${Le(this.locations || [], (t) => t.id, (t, e) => this.renderItem(t, e))}
      ${this.open ? null : d`<button @click="${this.addNew}">Add New</button>`}
    `;
  }
}
window.customElements.define("dt-location-map", Zr);
/**
 * @license
 * Copyright 2018 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */
const rt = (s) => s ?? E;
class Jr extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      id: { type: String },
      value: {
        type: String,
        reflect: !0
      },
      oldValue: {
        type: String
      },
      min: { type: Number },
      max: { type: Number },
      loading: { type: Boolean },
      saved: { type: Boolean },
      onchange: { type: String }
    };
  }
  connectedCallback() {
    super.connectedCallback(), this.oldValue = this.value;
  }
  _checkValue(t) {
    return !(t < this.min || t > this.max);
  }
  async onChange(t) {
    if (this._checkValue(t.target.value)) {
      const e = new CustomEvent("change", {
        detail: {
          field: this.name,
          oldValue: this.value,
          newValue: t.target.value
        },
        bubbles: !0,
        composed: !0
      });
      this.value = t.target.value, this._field.setCustomValidity(""), this.dispatchEvent(e), this.api = new Ie(this.nonce, `${this.apiRoot}`);
    } else
      t.currentTarget.value = "";
  }
  handleError(t = "An error occurred.") {
    let e = t;
    e instanceof Error ? (console.error(e), e = e.message) : console.error(e), this.error = e, this._field.setCustomValidity(e), this.invalid = !0, this.value = this.oldValue;
  }
  render() {
    return d`
      ${this.labelTemplate()}

      <input
        id="${this.id}"
        name="${this.name}"
        aria-label="${this.label}"
        type="number"
        ?disabled=${this.disabled}
        class="text-input"
        .value="${this.value}"
        min="${rt(this.min)}"
        max="${rt(this.max)}"
        @change=${this.onChange}
      />
    `;
  }
}
window.customElements.define("dt-number", Jr);
class Qr extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      placeholder: { type: String },
      options: { type: Array },
      value: {
        type: String,
        reflect: !0
      },
      color: {
        type: String,
        state: !0
      },
      onchange: { type: String }
    };
  }
  /**
   * Find the color for the currently selected value
   */
  updateColor() {
    if (this.value && this.options) {
      const t = this.options.filter((e) => e.id === this.value);
      t && t.length && (this.color = t[0].color);
    }
  }
  isColorSelect() {
    return (this.options || []).reduce(
      (t, e) => t || e.color,
      !1
    );
  }
  willUpdate(t) {
    super.willUpdate(t), t.has("value") && this.updateColor();
  }
  _change(t) {
    const e = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value,
        newValue: t.target.value
      }
    });
    this.value = t.target.value, this._setFormValue(this.value), this.dispatchEvent(e);
  }
  render() {
    return d`
      ${this.labelTemplate()}

      <div class="container" dir="${this.RTL ? "rtl" : "ltr"}">
        <select
          name="${this.name}"
          aria-label="${this.name}"
          @change="${this._change}"
          class="${this.isColorSelect() ? "color-select" : ""}"
          style="background-color: ${this.color};"
          ?disabled="${this.disabled}"
        >
          <option disabled selected hidden value="">${this.placeholder}</option>

          ${this.options && this.options.map(
      (t) => d`
              <option value="${t.id}" ?selected="${t.id === this.value}">
                ${t.label}
              </option>
            `
    )}
        </select>
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
      </div>
    `;
  }
}
window.customElements.define("dt-single-select", Qr);
class Xr extends U {
  static get styles() {
    return $`
      svg use {
        fill: currentcolor;
      }
    `;
  }
  render() {
    return d`
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
    `;
  }
}
window.customElements.define("dt-exclamation-circle", Xr);
class mo extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      /** Element ID */
      id: { type: String },
      /** `type` attribute of `<input />` */
      type: { type: String },
      /** Placeholder displayed when no value is entered */
      placeholder: { type: String },
      /** Value of field. Reflected back to attribute in order to select from DOM if needed. */
      value: {
        type: String,
        reflect: !0
      }
    };
  }
  _input(t) {
    this.value = t.target.value, this._setFormValue(this.value);
  }
  _change(t) {
    const e = new CustomEvent("change", {
      bubbles: !0,
      detail: {
        field: this.name,
        oldValue: this.value,
        newValue: t.target.value
      }
    });
    this.value = t.target.value, this._setFormValue(this.value), this.dispatchEvent(e);
  }
  implicitFormSubmit(t) {
    if ((t.keyCode || t.which) === 13 && this.internals.form) {
      const i = this.internals.form.querySelector("button");
      i && i.click();
    }
  }
  _validateRequired() {
    const { value: t } = this, e = this.shadowRoot.querySelector("input");
    t === "" && this.required ? (this.invalid = !0, this.internals.setValidity(
      {
        valueMissing: !0
      },
      this.requiredMessage || "This field is required",
      e
    )) : (this.invalid = !1, this.internals.setValidity({}));
  }
  get classes() {
    return {
      "text-input": !0,
      invalid: this.touched && this.invalid
    };
  }
  render() {
    return d`
      ${this.labelTemplate()}

      <div class="input-group">
        <input
          id="${this.id}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type || "text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${N(this.classes)}"
          .value="${this.value || ""}"
          @change=${this._change}
          @input=${this._input}
          novalidate
          @keyup="${this.implicitFormSubmit}"
        />

        ${this.touched && this.invalid ? d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>` : null}
        ${this.error ? d`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
            ></dt-icon>` : null}
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
      </div>
    `;
  }
}
window.customElements.define("dt-text", mo);
class Yr extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      id: { type: String },
      value: {
        type: String,
        reflect: !0
      },
      loading: { type: Boolean },
      saved: { type: Boolean },
      onchange: { type: String }
    };
  }
  onChange(t) {
    const e = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value,
        newValue: t.target.value
      }
    });
    this.value = t.target.value, this.dispatchEvent(e);
  }
  render() {
    return d`
      ${this.labelTemplate()}

      <textarea
        id="${this.id}"
        name="${this.name}"
        aria-label="${this.label}"
        type="text"
        ?disabled=${this.disabled}
        class="text-input"
        @change=${this.onChange}
        .value="${this.value || ""}"
      ></textarea>
    `;
  }
}
window.customElements.define("dt-textarea", Yr);
class tn extends R {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      id: { type: String },
      checked: {
        type: Boolean,
        reflect: !0
      },
      onchange: { type: String },
      hideIcons: { type: Boolean, default: !0 }
    };
  }
  constructor() {
    super(), this.hideIcons = !1;
  }
  onChange(t) {
    const e = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.checked,
        newValue: t.target.checked
      }
    });
    this.checked = t.target.checked, this._setFormValue(this.checked), this.dispatchEvent(e);
  }
  render() {
    const t = d`<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--checkmark"><path d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z" fill="currentcolor" stroke="currentcolor" /></svg>`, e = d`<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="Toggle__icon Toggle__icon--cross"><path d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z" fill="currentcolor" /></svg>`;
    return d`
      <label class="Toggle" for="${this.id}" dir="${this.RTL ? "rtl" : "ltr"}">
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
          ${this.hideIcons ? d`` : d` ${t} ${e} `}
        </span>
      </label>
    `;
  }
}
window.customElements.define("dt-toggle", tn);
class en extends mo {
  static get styles() {
    return [
      ...super.styles,
      $`
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
      `
    ];
  }
  static get properties() {
    return {
      ...super.properties,
      value: { type: Array, reflect: !0 }
    };
  }
  _addClick() {
    const t = {
      verified: !1,
      value: "",
      key: `new-${this.name}-${Math.floor(Math.random() * 100)}`
    };
    this.value = [...this.value, t], this.requestUpdate();
  }
  _deleteField(t) {
    const e = this.value.findIndex((l) => l.key === t.key);
    e !== -1 && this.value.splice(e, 1), this.value = [...this.value];
    const { verified: i, value: o, ...r } = t, n = { ...r, delete: !0 }, a = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: n,
        newValue: this.value
      }
    });
    this.dispatchEvent(a), this.requestUpdate();
  }
  labelTemplate() {
    return this.label ? d`
      <dt-label
        ?private=${this.private}
        privateLabel="${this.privateLabel}"
        iconAltText="${this.iconAltText}"
        icon="${this.icon}"
      >
        ${this.icon ? null : d`<slot name="icon-start" slot="icon-start"></slot>`}
        ${this.label}
      </dt-label>
      <button class="add-btn" @click=${this._addClick}>
        <svg class="add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 10h-4V6a2 2 0 0 0-4 0l.071 4H6a2 2 0 0 0 0 4l4.071-.071L10 18a2 2 0 0 0 4 0v-4.071L18 14a2 2 0 0 0 0-4z"></svg>
      </button>
    ` : "";
  }
  _inputFieldTemplate(t) {
    const i = t.key === `new-${this.name}-0` ? "" : d`
      <button class="delete-button"  @click=${() => this._deleteField(t)}>
        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
          <path
            id="path0_fill"
            fill-rule="evenodd"
            d="M 14 7C 14 10.866 10.866 14 7 14C 3.13403 14 0 10.866 0 7C 0 3.13401 3.13403 0 7 0C 10.866 0 14 3.13401 14 7ZM 9.51294 3.51299L 7 6.01299L 4.48706 3.51299L 3.5 4.49999L 6.01294 6.99999L 3.5 9.49999L 4.48706 10.487L 7 7.98699L 9.5 10.5L 10.4871 9.51299L 7.98706 6.99999L 10.5 4.49999L 9.51294 3.51299Z"
          />
        </svg>
      </button>
  `;
    return d`
      <div class="input-group">
        <input
          id="${t.key}"
          name="${this.name}"
          aria-label="${this.label}"
          type="${this.type || "text"}"
          placeholder="${this.placeholder}"
          ?disabled=${this.disabled}
          ?required=${this.required}
          class="${N(this.classes)}"
          .value="${t.value || ""}"
          @change=${this._change}
          novalidate
          @keyup="${this.implicitFormSubmit}"
        />
        ${i}

        ${this.touched && this.invalid ? d`<dt-exclamation-circle
              class="icon-overlay alert"
            ></dt-exclamation-circle>` : null}
        ${this.error ? d`<dt-icon
              icon="mdi:alert-circle"
              class="icon-overlay alert"
              tooltip="${this.error}"
              size="2rem"
              ></dt-icon>` : null}
        ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
      </div>
    `;
  }
  // update the value comming from API
  _setFormValue(t) {
    super._setFormValue(t), this.internals.setFormValue(JSON.stringify(t)), this.value = t, this.requestUpdate();
  }
  _change(t) {
    const e = t.target.id, { value: i } = t.target, o = this.value;
    this.value.find((n, a) => n.key === e ? (o[a] = { verified: !1, value: i, key: e }, !0) : !1);
    const r = new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value,
        newValue: o,
        onSuccess: (n) => {
          n && this._setFormValue(n[this.name]);
        }
      }
    });
    this.value = o, this._setFormValue(this.value), this.dispatchEvent(r);
  }
  // rendering the input at 0 index
  _renderInputFields() {
    return this.value == null || !this.value.length ? (this.value = [{
      verified: !1,
      value: "",
      key: `new-${this.name}-0`
    }], this._inputFieldTemplate(this.value[0])) : d`
      ${this.value.map(
      (t) => this._inputFieldTemplate(t)
    )}
    `;
  }
  render() {
    return d`
     <div class="label-wrapper">
        ${this.labelTemplate()}
      </div>
      ${this._renderInputFields()}
    `;
  }
}
window.customElements.define("dt-comm-channel", en);
class on extends R {
  static get styles() {
    return $`
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
  `;
  }
  static get properties() {
    return {
      buttons: { type: Array },
      selectedButtons: { type: Array },
      value: { type: Array, reflect: !0 },
      icon: { type: String },
      isModal: { type: Array }
    };
  }
  get classes() {
    const t = {
      "dt-button": !0,
      "dt-button--outline": this.outline,
      "dt-button--rounded": this.rounded
    }, e = `dt-button--${this.context}`;
    return t[e] = !0, t;
  }
  constructor() {
    super(), this.buttons = [], this.selectedButtons = [], this.value = [], this.custom = !0;
  }
  connectedCallback() {
    super.connectedCallback(), this.selectedButtons = this.value ? this.value.map((t) => ({ value: t })) : [];
  }
  _handleButtonClick(t, e) {
    var r;
    const i = t.target.value;
    i === "milestone_baptized" && this.isModal && this.isModal.includes(e) && !((r = this.value) != null && r.includes("milestone_baptized")) && (document.querySelector("#baptized-modal").shadowRoot.querySelector("dialog").showModal(), document.querySelector("body").style.overflow = "hidden");
    const o = this.selectedButtons.findIndex(
      (n) => n.value === i
    );
    o > -1 ? (this.selectedButtons.splice(o, 1), this.selectedButtons.push({ value: `-${i}` })) : this.selectedButtons.push({ value: i }), this.value = this.selectedButtons.filter((n) => !n.value.startsWith("-")).map((n) => n.value), this.dispatchEvent(new CustomEvent("change", {
      detail: {
        field: this.name,
        oldValue: this.value,
        newValue: this.selectedButtons
      }
    })), this._setFormValue(this.value), this.requestUpdate();
  }
  _inputKeyDown(t) {
    switch (t.keyCode || t.which) {
      case 13:
        this._handleButtonClick(t);
        break;
    }
  }
  render() {
    return d`
       ${this.labelTemplate()}
       ${this.loading ? d`<dt-spinner class="icon-overlay"></dt-spinner>` : null}
        ${this.saved ? d`<dt-checkmark class="icon-overlay success"></dt-checkmark>` : null}
       <div class="button-group">
        ${this.buttons.map((t) => Object.keys(t).map((i) => {
      const r = this.selectedButtons.some(
        (n) => n.value === i && !n.delete
      ) ? "success" : "disabled";
      return d`
            <dt-button
            custom
              id=${i}
              type="success"
              context=${r}
              .value=${i || this.value}
              @click="${(n) => this._handleButtonClick(n, t[i].label)}"
              @keydown="${this._inputKeyDown}"
              role="button"
              >
               <span class="icon">
                ${t[i].icon ? d`<img src="${t[i].icon}" alt="${this.iconAltText}" />` : null}
            </span>
             ${t[i].label}</dt-button>
          `;
    }))}
        </div>
    `;
  }
}
window.customElements.define(
  "dt-multiselect-buttons-group",
  on
);
class sn extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      context: { type: String },
      dismissable: { type: Boolean },
      timeout: { type: Number },
      hide: { type: Boolean },
      outline: { type: Boolean }
    };
  }
  get classes() {
    const t = {
      "dt-alert": !0,
      "dt-alert--outline": this.outline
    }, e = `dt-alert--${this.context}`;
    return t[e] = !0, t;
  }
  constructor() {
    super(), this.context = "default";
  }
  connectedCallback() {
    super.connectedCallback(), this.timeout && setTimeout(() => {
      this._dismiss();
    }, this.timeout);
  }
  _dismiss() {
    this.hide = !0;
  }
  render() {
    if (this.hide)
      return d``;
    const t = d`
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
    `;
    return d`
      <div role="alert" class=${N(this.classes)}>
        <div>
          <slot></slot>
        </div>
        ${this.dismissable ? d`
              <button @click="${this._dismiss}" class="toggle">${t}</button>
            ` : null}
      </div>
    `;
  }
}
window.customElements.define("dt-alert", sn);
class rn extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      postType: { type: String },
      postTypeLabel: { type: String },
      posttypesettings: { type: Object, attribute: !0 },
      posts: { type: Array },
      total: { type: Number },
      columns: { type: Array },
      sortedBy: { type: String },
      loading: { type: Boolean, default: !0 },
      offset: { type: Number },
      showArchived: { type: Boolean, default: !1 },
      showFieldsSelector: { type: Boolean, default: !1 },
      showBulkEditSelector: { type: Boolean, default: !1 },
      nonce: { type: String },
      payload: { type: Object },
      favorite: { type: Boolean },
      initialLoadPost: { type: Boolean, default: !1 },
      loadMore: { type: Boolean, default: !1 },
      headerClick: { type: Boolean, default: !1 }
    };
  }
  constructor() {
    super(), this.sortedBy = "name", this.payload = {
      sort: this.sortedBy,
      overall_status: [
        "-closed"
      ],
      fields_to_return: this.sortedColumns
    }, this.initalLoadPost = !1, this.initalLoadPost || (this.posts = [], this.limit = 100);
  }
  firstUpdated() {
    this.postTypeSettings = window.post_type_fields, this.sortedColumns = this.columns.includes("favorite") ? ["favorite", ...this.columns.filter((t) => t !== "favorite")] : this.columns, this.style.setProperty("--number-of-columns", this.columns.length - 1);
  }
  async _getPosts(t) {
    const e = await new CustomEvent("dt:get-data", {
      bubbles: !0,
      detail: {
        field: this.name,
        postType: this.postType,
        query: t,
        onSuccess: (i) => {
          this.initalLoadPost && this.loadMore && (this.posts = [...this.posts, ...i], this.postsLength = this.posts.length, this.total = i.length, this.loadMore = !1), this.initalLoadPost || (this.posts = [...i], this.offset = this.posts.length, this.initalLoadPost = !0, this.total = i.length), this.headerClick && (this.posts = i, this.offset = this.posts.length, this.headerClick = !1), this.total = i.length;
        },
        onError: (i) => {
          console.warn(i);
        }
      }
    });
    this.dispatchEvent(e);
  }
  _headerClick(t) {
    const e = t.target.dataset.id;
    this.sortedBy === e ? e.startsWith("-") ? this.sortedBy = e.replace("-", "") : this.sortedBy = `-${e}` : this.sortedBy = e, this.payload = {
      sort: this.sortedBy,
      overall_status: [
        "-closed"
      ],
      limit: this.limit,
      fields_to_return: this.columns
    }, this.headerClick = !0, this._getPosts(this.payload);
  }
  static _rowClick(t) {
    window.open(t, "_self");
  }
  _bulkEdit() {
    this.showBulkEditSelector = !this.showBulkEditSelector;
  }
  _fieldsEdit() {
    this.showFieldsSelector = !this.showFieldsSelector;
  }
  _toggleShowArchived() {
    if (this.showArchived = !this.showArchived, this.headerClick = !0, this.showArchived) {
      const { overall_status: t, offset: e, ...i } = this.payload;
      this.payload = i;
    } else
      this.payload.overall_status = ["-closed"];
    this._getPosts(this.payload);
  }
  _sortArrowsClass(t) {
    return this.sortedBy === t ? "sortedBy" : "";
  }
  /* The above code appears to be a comment block in JavaScript. It includes a function name
  "_sortArrowsToggle" and a question asking what the code is doing. However, the function
  implementation or any other code logic is not provided within the comment block. */
  _sortArrowsToggle(t) {
    return this.sortedBy !== `-${t}` ? `-${t}` : t;
  }
  _headerTemplate() {
    return this.postTypeSettings ? d`
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
            ${Rt(this.sortedColumns, (t) => {
      const e = t === "favorite";
      return d`<th
                class="all"
                data-id="${this._sortArrowsToggle(t)}"
                @click=${this._headerClick}
              >
                  <span class="column-name"
                     >${e ? null : this.postTypeSettings[t].name}</span
                  >
                  ${e ? "" : d`<span id="sort-arrows">
                        <span
                          class="sort-arrow-up ${this._sortArrowsClass(t)}"
                          data-id="${t}"
                        ></span>
                        <span
                          class="sort-arrow-down ${this._sortArrowsClass(
        `-${t}`
      )}"
                          data-id="-${t}"
                        ></span>
                      </span>`}
              </th>`;
    })}
          </tr>
        </thead>
      ` : null;
  }
  _rowTemplate() {
    if (this.posts && Array.isArray(this.posts)) {
      const t = this.posts.map((e, i) => this.showArchived || !this.showArchived && e.overall_status !== "closed" ? d`
              <tr class="dnd-moved" data-link="${e.permalink}" @click=${() => this._rowClick(e.permalink)}>
                <td class="bulk_edit_checkbox no-title">
                  <input type="checkbox" name="bulk_edit_id" .value="${e.ID}" />
                </td>
                <td class="no-title line-count">${i + 1}.</td>
                ${this._cellTemplate(e)}
              </tr>
            ` : null).filter((e) => e !== null);
      return t.length > 0 ? t : d`<p>No contacts available</p>`;
    }
    return null;
  }
  // eslint-disable-next-line class-methods-use-this
  formatDate(t) {
    const e = new Date(t);
    return new Intl.DateTimeFormat("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric"
    }).format(e);
  }
  _cellTemplate(t) {
    return Rt(this.sortedColumns, (e) => {
      if (["text", "textarea", "number"].includes(
        this.postTypeSettings[e].type
      ))
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${t[e]}
        </td>`;
      if (this.postTypeSettings[e].type === "date")
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${this.formatDate(t[e].formatted)}
        </td>`;
      if (this.postTypeSettings[e].type === "user_select" && t[e] && t[e].display)
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${rt(t[e].display)}
        </td>`;
      if (this.postTypeSettings[e].type === "key_select" && t[e] && (t[e].label || t[e].name))
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${t[e].label || t[e].name}
        </td>`;
      if (this.postTypeSettings[e].type === "multi_select" || this.postTypeSettings[e].type === "tags" && t[e] && t[e].length > 0)
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          <ul>
            ${Rt(
          t[e],
          (i) => d`<li>
                  ${this.postTypeSettings[e].default[i].label}
                </li>`
        )}
          </ul>
        </td>`;
      if (this.postTypeSettings[e].type === "location" || this.postTypeSettings[e].type === "location_meta")
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${rt(t[e].label)}
        </td>`;
      if (this.postTypeSettings[e].type === "communication_channel")
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          ${rt(t[e].value)}
        </td>`;
      if (this.postTypeSettings[e].type === "connection")
        return d` <td
          dir="auto"
          title="${this.postTypeSettings[e].name}"
        >
          <!-- TODO: look at this, it doesn't match the current theme. -->
          ${rt(t[e].value)}
        </td>`;
      if (this.postTypeSettings[e].type === "boolean") {
        if (e === "favorite")
          return d`<td
            dir="auto"
            title="${this.postTypeSettings[e].name}"
            class=""
          >
            <dt-button
              id="favorite-button-${t.ID}"
              label="favorite"
              title="favorite"
              type="button"
              posttype="contacts"
              context="star"
              .favorited=${t.favorite ? t.favorite : !1}
              .listButton=${!0}
            >
              <svg
                class="${N({
            "icon-star": !0,
            selected: t.favorite
          })}"
                height="15"
                viewBox="0 0 32 32"
              >
                <path
                  d="M 31.916 12.092 C 31.706 11.417 31.131 10.937 30.451 10.873 L 21.215 9.996 L 17.564 1.077 C 17.295 0.423 16.681 0 16 0 C 15.318 0 14.706 0.423 14.435 1.079 L 10.784 9.996 L 1.546 10.873 C 0.868 10.937 0.295 11.417 0.084 12.092 C -0.126 12.769 0.068 13.51 0.581 13.978 L 7.563 20.367 L 5.503 29.83 C 5.354 30.524 5.613 31.245 6.165 31.662 C 6.462 31.886 6.811 32 7.161 32 C 7.463 32 7.764 31.915 8.032 31.747 L 16 26.778 L 23.963 31.747 C 24.546 32.113 25.281 32.08 25.834 31.662 C 26.386 31.243 26.645 30.524 26.494 29.83 L 24.436 20.367 L 31.417 13.978 C 31.931 13.51 32.127 12.769 31.916 12.092 Z M 31.916 12.092"
                />
              </svg>
            </dt-button>
          </td>`;
        if (this.postTypeSettings[e] === !0)
          return d`<td
            dir="auto"
            title="${this.postTypeSettings[e].name}"
          >
            ['&check;']
          </td>`;
      }
      return d`<td
        dir="auto"
        title="${this.postTypeSettings[e].name}"
      ></td>`;
    });
  }
  _fieldListIconTemplate(t) {
    return this.postTypeSettings[t].icon ? d`<img
        class="dt-icon"
        src="${this.postTypeSettings[t].icon}"
        alt="${this.postTypeSettings[t].name}"
      />` : null;
  }
  _fieldsListTemplate() {
    return Le(
      Object.keys(this.postTypeSettings).sort((t, e) => {
        const i = this.postTypeSettings[t].name.toUpperCase(), o = this.postTypeSettings[e].name.toUpperCase();
        return i < o ? -1 : i > o ? 1 : 0;
      }),
      (t) => t,
      (t) => this.postTypeSettings[t].hidden ? null : d`<li class="list-field-picker-item">
            <label>
              <input
                type="checkbox"
                id="${t}"
                name="${t}"
                .value="${t}"
                @change=${this._updateFields}
                ?checked=${this.columns.includes(t)}
              />
              ${this._fieldListIconTemplate(t)}
              ${this.postTypeSettings[t].name}</label
            >
          </li> `
    );
  }
  _fieldsSelectorTemplate() {
    return this.showFieldsSelector ? d`<div
        id="list_column_picker"
        class="list_field_picker list_action_section"
      >
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${S("Choose which fields to display as columns in the list")}
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
      </div>` : null;
  }
  _updateFields(t) {
    const e = t.target.value, i = this.columns;
    i.includes(e) ? (i.filter((o) => o !== e), i.splice(i.indexOf(e), 1)) : i.push(e), this.columns = i, this.style.setProperty("--number-of-columns", this.columns.length - 1), this.requestUpdate();
  }
  _bulkSelectorTemplate() {
    return this.showBulkEditSelector ? d`<div id="bulk_edit_picker" class="list_action_section">
        <div class="list_action_section_header">
          <p style="font-weight:bold">
            ${S(
      se`Select all the ${this.postType} you want to update from the list, and update them below`
    )}
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
      </div>` : null;
  }
  connectedCallback() {
    super.connectedCallback(), this.payload = {
      sort: this.sortedBy,
      overall_status: ["-closed"],
      fields_to_return: this.columns
    }, this.posts.length === 0 && this._getPosts(this.payload).then((t) => {
      this.posts = t;
    });
  }
  _handleLoadMore() {
    this.limit = 500, this.payload = {
      sort: this.sortedBy,
      overall_status: [
        "-closed"
      ],
      fields_to_return: this.columns,
      offset: this.offset,
      limit: this.limit
    }, this.loadMore = !0, this._getPosts(this.payload).then((t) => {
      console.log(t);
    });
  }
  render() {
    const t = {
      bulk_editing: this.showBulkEditSelector,
      hidden: !1
    };
    this.posts && (this.total = this.posts.length);
    const e = d`
      <svg viewBox="0 0 100 100" fill="#000000" style="enable-background:new 0 0 100 100;" xmlns="http://www.w3.org/2000/svg">
        <line style="stroke-linecap: round; paint-order: fill; fill: none; stroke-width: 15px;" x1="7.97" y1="50.199" x2="76.069" y2="50.128" transform="matrix(0.999999, 0.001017, -0.001017, 0.999999, 0.051038, -0.042708)"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="17.751" x2="92.058" y2="17.751"/>
        <line style="stroke-linecap: round; stroke-width: 15px;" x1="7.97" y1="82.853" x2="42.343" y2="82.853"/>
        <polygon style="stroke-linecap: round; stroke-miterlimit: 1; stroke-linejoin: round; fill: rgb(255, 255, 255); paint-order: stroke; stroke-width: 9px;" points="22.982 64.982 33.592 53.186 50.916 70.608 82.902 21.308 95 30.85 52.256 95"/>
      </svg>
    `, i = d`<svg height='100px' width='100px'  fill="#000000" xmlns:x="http://ns.adobe.com/Extensibility/1.0/" xmlns:i="http://ns.adobe.com/AdobeIllustrator/10.0/" xmlns:graph="http://ns.adobe.com/Graphs/1.0/" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g i:extraneous="self"><g><path d="M94.4,63c0-5.7-3.6-10.5-8.6-12.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5     s3.6,10.5,8.6,12.5v17.2c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5C90.9,73.6,94.4,68.7,94.4,63z M81,66.7     c-2,0-3.7-1.7-3.7-3.7c0-2,1.7-3.7,3.7-3.7s3.7,1.7,3.7,3.7C84.7,65.1,83.1,66.7,81,66.7z"></path><path d="M54.8,24.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v17.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v43.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V49.5c5-1.9,8.6-6.8,8.6-12.5S59.8,26.5,54.8,24.5z M50,40.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C53.7,39.1,52,40.7,50,40.7z"></path><path d="M23.8,50.5V7.3c0-2.7-2.2-4.8-4.8-4.8c-2.7,0-4.8,2.2-4.8,4.8v43.2c-5,1.9-8.6,6.8-8.6,12.5s3.6,10.5,8.6,12.5v17.2     c0,2.7,2.2,4.8,4.8,4.8c2.7,0,4.8-2.2,4.8-4.8V75.5c5-1.9,8.6-6.8,8.6-12.5S28.8,52.5,23.8,50.5z M19,66.7c-2,0-3.7-1.7-3.7-3.7     c0-2,1.7-3.7,3.7-3.7c2,0,3.7,1.7,3.7,3.7C22.7,65.1,21,66.7,19,66.7z"></path></g></g></g></svg>`;
    return d`
      <div class="section">
        <div class="header">
          <div class="section-header">
            <span
              class="section-header posts-header"
              style="display: inline-block"
              >${S(
      se`${this.postTypeLabel ? this.postTypeLabel : this.postType} List`
    )}</span
            >
          </div>
          <span class="filter-result-text"
            >${S(se`Showing ${this.total} of ${this.total}`)}</span
          >

          <button
            class="bulkToggle toggleButton"
            id="bulk_edit_button"
            @click=${this._bulkEdit}
          >
            ${e} ${S("Bulk Edit")}
          </button>
          <button
            class="fieldsToggle toggleButton"
            id="fields_edit_button"
            @click=${this._fieldsEdit}
          >
            ${i} ${S("Fields")}
          </button>

          <dt-toggle
            name="showArchived"
            label=${S("Show Archived")}
            ?checked=${this.showArchived}
            hideIcons
            onchange=${this._toggleShowArchived}
            @click=${this._toggleShowArchived}
          ></dt-toggle>
        </div>

        ${this._fieldsSelectorTemplate()} ${this._bulkSelectorTemplate()}
        <table class="table-contacts ${N(t)}">
          ${this._headerTemplate()}
          ${this.posts ? this._rowTemplate() : S("Loading")}
        </table>
          ${this.total >= 100 ? d`<div class="text-center"><dt-button buttonStyle=${JSON.stringify({ margin: "0" })} class="loadMoreButton btn btn-primary" @click=${this._handleLoadMore}>Load More</dt-button></div>` : ""}
      </div>
    `;
  }
}
window.customElements.define("dt-list", rn);
class nn extends j {
  static get styles() {
    return $`
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
    `;
  }
  static get properties() {
    return {
      title: { type: String },
      expands: { type: Boolean },
      collapsed: { type: Boolean }
    };
  }
  get hasHeading() {
    return this.title || this.expands;
  }
  _toggle() {
    this.collapsed = !this.collapsed;
  }
  renderHeading() {
    return this.hasHeading ? d`
        <h3 class="section-header">
          ${this.title}
          ${this.expands ? d`
                <button
                  @click="${this._toggle}"
                  class="toggle chevron ${this.collapsed ? "down" : "up"}"
                >
                  &nbsp;
                </button>
              ` : null}
        </h3>
    ` : E;
  }
  render() {
    return d`
      <section>
        ${this.renderHeading()}
        <div part="body" class="section-body ${this.collapsed ? "collapsed" : null}">
          <slot></slot>
        </div>
      </section>
    `;
  }
}
window.customElements.define("dt-tile", nn);
class bo {
  /**
   * Initialize ComponentService
   * @param postType - D.T Post Type (e.g. contacts, groups, etc.)
   * @param postId - ID of current post
   * @param nonce - WordPress nonce for authentication
   * @param apiRoot - Root of API (default: wp-json) (i.e. the part before dt/v1/ or dt-posts/v2/)
   */
  constructor(t, e, i, o = "wp-json") {
    this.postType = t, this.postId = e, this.nonce = i, this.apiRoot = `${o}/`.replace("//", "/"), this.api = new Ie(this.nonce, this.apiRoot), this.autoSaveComponents = [
      "dt-connection",
      "dt-date",
      "dt-location",
      "dt-multi-select",
      "dt-number",
      "dt-single-select",
      "dt-tags",
      "dt-text",
      "dt-textarea",
      "dt-toggle",
      "dt-comm-channel",
      "dt-multiselect-buttons-group",
      "dt-list",
      "dt-button"
    ], this.dynamicLoadComponents = [
      "dt-connection",
      "dt-tags",
      "dt-modal",
      "dt-list",
      "dt-button"
    ];
  }
  /**
   * Initialize components on the page with necessary event listeners
   */
  initialize() {
    this.postId && this.enableAutoSave();
    const t = document.querySelector(
      "dt-button#create-post-button"
    );
    t && t.addEventListener(
      "send-data",
      this.processFormSubmission.bind(this)
    );
    const e = document.querySelector("dt-list");
    e && e.tagName.toLowerCase() === "dt-list" && e.addEventListener(
      "customClick",
      this.handleCustomClickEvent.bind(this)
    ), this.attachLoadEvents();
  }
  /**
   * Attach onload events to components that load their options
   * dynamically via API
   * @param {string} [selector] (Optional) Override default selector
   */
  async attachLoadEvents(t) {
    const e = document.querySelectorAll(
      t || this.dynamicLoadComponents.join(",")
    ), i = Array.from(e).filter(
      (o) => o.tagName.toLowerCase() === "dt-modal" && o.classList.contains("duplicate-detected")
    );
    i.length > 0 && this.checkDuplicates(e, i), e && e.forEach(
      (o) => o.addEventListener("dt:get-data", this.handleGetDataEvent.bind(this))
    );
  }
  async checkDuplicates(t, e) {
    const i = document.querySelector("dt-modal.duplicate-detected");
    if (i) {
      const o = i.shadowRoot.querySelector(
        ".duplicates-detected-button"
      );
      o && (o.style.display = "none");
      const r = await this.api.checkDuplicateUsers(
        this.postType,
        this.postId
      );
      e && r.ids.length > 0 && o && (o.style.display = "block");
    }
  }
  /**
   * Enable auto-save feature for all components on the current page
   * @param {string} [selector] (Optional) Override default selector
   */
  enableAutoSave(t) {
    const e = document.querySelectorAll(
      t || this.autoSaveComponents.join(",")
    );
    e && e.forEach((i) => {
      i.tagName.toLowerCase() === "dt-button" && i.addEventListener("customClick", this.handleCustomClickEvent.bind(this)), i.addEventListener("change", this.handleChangeEvent.bind(this));
    });
  }
  async handleCustomClickEvent(t) {
    const e = t.detail;
    if (e) {
      const { field: i, toggleState: o } = e;
      t.target.setAttribute("loading", !0);
      let r;
      i.startsWith("favorite-button") ? (r = { favorite: o }, /\d$/.test(i) && (this.postId = i.split("-").pop())) : i.startsWith("following-button") || i.startsWith("follow-button") ? r = {
        follow: { values: [{ value: "1", delete: o }] },
        unfollow: { values: [{ value: "1", delete: !o }] }
      } : console.log("No match found for the field");
      try {
        const n = await this.api.updatePost(
          this.postType,
          this.postId,
          r
        );
      } catch (n) {
        console.error(n), t.target.removeAttribute("loading"), t.target.setAttribute("invalid", !0), t.target.setAttribute("error", n.message || n.toString());
      }
    }
  }
  /**
    * Handle Post creation on new contact form
    *
    */
  async processFormSubmission(t) {
    const e = t.detail, { newValue: i } = e;
    try {
      const o = await this.api.createPost(
        this.postType,
        i.el
      );
      o && (window.location = o.permalink), t.target.removeAttribute("loading"), t.target.setAttribute("error", ""), t.target.setAttribute("saved", !0);
    } catch (o) {
      console.error(o), t.target.removeAttribute("loading"), t.target.setAttribute("invalid", !0), t.target.setAttribute("error", o.message || o.toString());
    }
  }
  /**
   * Event listener for load events.
   * Will attempt to load data from API and call success/error callback
   * @param {Event} event
   * @returns {Promise<void>}
   */
  async handleGetDataEvent(t) {
    const e = t.detail;
    if (e) {
      const { field: i, query: o, onSuccess: r, onError: n } = e;
      try {
        const a = t.target.tagName.toLowerCase();
        let l = [];
        switch (a) {
          case "dt-button":
            l = await this.api.getContactInfo(
              this.postType,
              this.postId
            );
            break;
          case "dt-list":
            l = (await this.api.fetchPostsList(this.postType, o)).posts;
            break;
          case "dt-connection": {
            const u = e.postType || this.postType, f = await this.api.listPostsCompact(
              u,
              o
            ), g = {
              ...f,
              posts: f.posts.filter(
                (b) => b.ID !== parseInt(this.postId, 10)
              )
            };
            g != null && g.posts && (l = g == null ? void 0 : g.posts.map((b) => ({
              id: b.ID,
              label: b.name,
              link: b.permalink,
              status: b.status
            })));
            break;
          }
          case "dt-tags":
          default:
            l = await this.api.getMultiSelectValues(
              this.postType,
              i,
              o
            ), l = l.map((u) => ({
              id: u,
              label: u
            }));
            break;
        }
        r(l);
      } catch (a) {
        n(a);
      }
    }
  }
  /**
   * Event listener for change events.
   * Will set loading property, attempt to save value via API,
   * and then set saved/invalid attribute based on success
   * @param {Event} event
   * @returns {Promise<void>}
   */
  async handleChangeEvent(t) {
    const e = t.detail;
    if (e) {
      const { field: i, newValue: o, oldValue: r, remove: n } = e, a = t.target.tagName.toLowerCase(), l = bo.convertValue(
        a,
        o,
        r
      );
      t.target.setAttribute("loading", !0);
      try {
        let u;
        switch (a) {
          case "dt-users-connection": {
            if (n === !0) {
              u = await this.api.removePostShare(this.postType, this.postId, l);
              break;
            }
            u = await this.api.addPostShare(this.postType, this.postId, l);
            break;
          }
          default: {
            u = await this.api.updatePost(this.postType, this.postId, {
              [i]: l
            }), a === "dt-comm-channel" && e.onSuccess && e.onSuccess(u);
            break;
          }
        }
        t.target.removeAttribute("loading"), t.target.setAttribute("error", ""), t.target.setAttribute("saved", !0);
      } catch (u) {
        console.error(u), t.target.removeAttribute("loading"), t.target.setAttribute("invalid", !0), t.target.setAttribute("error", u.message || u.toString());
      }
    }
  }
  /**
   * Convert value returned from a component into what is expected by DT API
   * @param {string} component Tag name of component. E.g. dt-text
   * @param {mixed} value
   * @returns {mixed}
   */
  static convertValue(t, e, i) {
    let o = e;
    if (e)
      switch (t) {
        case "dt-toggle":
          typeof e == "string" && (o = e.toLowerCase() === "true");
          break;
        case "dt-multi-select":
        case "dt-tags":
          typeof e == "string" && (o = [e]), o = {
            values: o.map((r) => {
              if (typeof r == "string") {
                const a = {
                  value: r.replace("-", "")
                };
                return r.startsWith("-") && (a.delete = !0), a;
              }
              const n = {
                value: r.id
              };
              return r.delete && (n.delete = r.delete), n;
            }),
            force_values: !1
          };
          break;
        case "dt-users-connection": {
          const r = [], n = new Map(i.map((a) => [a.id, a]));
          for (const a of o) {
            const l = n.get(a.id), u = { id: a.id, changes: {} };
            if (l) {
              let f = !1;
              const g = /* @__PURE__ */ new Set([...Object.keys(l), ...Object.keys(a)]);
              for (const b of g)
                a[b] !== l[b] && (u.changes[b] = Object.prototype.hasOwnProperty.call(a, b) ? a[b] : void 0, f = !0);
              if (f) {
                r.push(u);
                break;
              }
            } else {
              u.changes = { ...a }, r.push(u);
              break;
            }
          }
          o = r[0].id;
          break;
        }
        case "dt-connection":
        case "dt-location":
          typeof e == "string" && (o = [
            {
              id: e
            }
          ]), o = {
            values: o.map((r) => {
              const n = {
                value: r.id
              };
              return r.delete && (n.delete = r.delete), n;
            }),
            force_values: !1
          };
          break;
        case "dt-multiselect-buttons-group":
          typeof e == "string" && (o = [
            {
              id: e
            }
          ]), o = {
            values: o.map((r) => r.value.startsWith("-") ? {
              value: r.value.replace("-", ""),
              delete: !0
            } : r),
            force_values: !1
          };
          break;
        case "dt-comm-channel": {
          const r = e.length;
          i && i.delete === !0 ? o = [i] : e[r - 1].key === "" || e[r - 1].key.startsWith("new-contact") ? (o = [], e.forEach((n) => {
            o.push({ value: n.value });
          })) : o = e;
          break;
        }
      }
    return o;
  }
}
export {
  j as A,
  Ie as B,
  bo as C,
  Os as D,
  Ls as a,
  Ps as b,
  Vs as c,
  zs as d,
  Hs as e,
  Ws as f,
  Rs as g,
  Gs as h,
  Zr as i,
  Gr as j,
  Gi as k,
  Jr as l,
  Qr as m,
  Ht as n,
  mo as o,
  Yr as p,
  tn as q,
  en as r,
  se as s,
  on as t,
  R as u,
  sn as v,
  rn as w,
  Is as x,
  nn as y,
  Wr as z
};
