import{i as t,y as e}from"../../lit-element-2409d5fe.js";import{m as o}from"../../lit-localize-763e4978.js";import{o as a}from"../../class-map-8d921948.js";import{i}from"../../style-map-ac85d91b.js";import{D as r}from"../../dt-base.js";import"../../directive-de55b00a.js";window.customElements.define("dt-modal",class extends r{static get styles(){return t`:host{display:block;font-family:var(--font-family)}:host:has(dialog[open]){overflow:hidden}.dt-modal{display:block;background:var(--dt-modal-background-color,#fff);color:var(--dt-modal-color,#000);max-inline-size:min(90vw,100%);max-block-size:min(80vh,100%);max-block-size:min(80dvb,100%);margin:auto;height:fit-content;padding:var(--dt-modal-padding,1em);position:fixed;inset:0;border-radius:1em;border:none;box-shadow:var(--shadow-6);z-index:1000;transition:opacity .1s ease-in-out}dialog:not([open]){pointer-events:none;opacity:0}dialog::backdrop{background:var(--dt-modal-backdrop-color,rgba(0,0,0,.25));animation:var(--dt-modal-animation,fade-in .75s)}@keyframes fade-in{from{opacity:0}to{opacity:1}}h1,h2,h3,h4,h5,h6{line-height:1.4;text-rendering:optimizeLegibility;color:inherit;font-style:normal;font-weight:300;margin:0}form{display:grid;height:fit-content;grid-template-columns:1fr;grid-template-rows:100px auto 100px;grid-template-areas:'header' 'main' 'footer';position:relative}form.no-header{grid-template-rows:auto auto;grid-template-areas:'main' 'footer'}header{grid-area:header;display:flex;justify-content:space-between}.button{color:var(--dt-modal-button-color,#fff);background:var(--dt-modal-button-background,#000);font-size:1rem;border:.1em solid var(--dt-modal-button-background,#000);border-radius:.25em;padding:.25rem .5rem;cursor:pointer;text-decoration:none}.button.opener{color:var(--dt-modal-button-opener-color,var(--dt-modal-button-color,#fff));background:var(--dt-modal-button-opener-background,var(--dt-modal-button-background,#000));border:.1em solid var(--dt-modal-button-opener-background,#000)}button.toggle{margin-inline-end:0;margin-inline-start:auto;background:0 0;border:none;color:inherit;cursor:pointer;display:flex;align-items:flex-start}article{grid-area:main;overflow:auto}footer{grid-area:footer;display:flex;justify-content:space-between}.help-more h5{font-size:.75rem;display:block}.help-more .button{font-size:.75rem;display:block}`}static get properties(){return{title:{type:String},context:{type:String},isHelp:{type:Boolean},isOpen:{type:Boolean},hideHeader:{type:Boolean},hideButton:{type:Boolean},buttonClass:{type:Object},buttonStyle:{type:Object}}}constructor(){super(),this.context="default",this.addEventListener("open",(t=>this._openModal())),this.addEventListener("close",(t=>this._closeModal()))}_openModal(){this.isOpen=!0,this.shadowRoot.querySelector("dialog").showModal(),document.querySelector("body").style.overflow="hidden"}_dialogHeader(t){return this.hideHeader?e``:e`<header><h1 id="modal-field-title">${this.title}</h1><button @click="${this._cancelModal}" class="toggle">${t}</button></header>`}_closeModal(){this.isOpen=!1,this.shadowRoot.querySelector("dialog").close(),document.querySelector("body").style.overflow="initial"}_cancelModal(){this._triggerClose("cancel")}_triggerClose(t){this.dispatchEvent(new CustomEvent("close",{detail:{action:t}}))}_dialogClick(t){if("DIALOG"!==t.target.tagName)return;const e=t.target.getBoundingClientRect();!1===(e.top<=t.clientY&&t.clientY<=e.top+e.height&&e.left<=t.clientX&&t.clientX<=e.left+e.width)&&this._cancelModal()}_dialogKeypress(t){"Escape"===t.key&&this._cancelModal()}_helpMore(){return this.isHelp?e`<div class="help-more"><h5>${o("Need more help?")}</h5><a class="button small" id="docslink" href="https://disciple.tools/user-docs" target="_blank">${o("Read the documentation")}</a></div>`:null}firstUpdated(){this.isOpen&&this._openModal()}_onButtonClick(){this._triggerClose("button")}render(){const t=e`<svg viewPort="0 0 12 12" version="1.1" width="12" height="12">xmlns="http://www.w3.org/2000/svg"><line x1="1" y1="11" x2="11" y2="1" stroke="currentColor" stroke-width="2"/><line x1="1" y1="1" x2="11" y2="11" stroke="currentColor" stroke-width="2"/></svg>`;return e`<dialog class="dt-modal" @click="${this._dialogClick}" @keypress="${this._dialogKeypress}"><form method="dialog" class="${this.hideHeader?"no-header":""}">${this._dialogHeader(t)}<article><slot name="content"></slot></article><footer><button class="button small" data-close="" aria-label="Close reveal" type="button" @click="${this._onButtonClick}"><slot name="close-button">${o("Close")}</slot></button> ${this._helpMore()}</footer></form></dialog>${this.hideButton?null:e`<button class="button small opener ${a(this.buttonClass||{})}" data-open="" aria-label="Open reveal" type="button" @click="${this._openModal}" style="${i(this.buttonStyle||{})}"><slot name="openButton">${o("Open Dialog")}</slot></button>`}`}});
//# sourceMappingURL=dt-modal.js.map