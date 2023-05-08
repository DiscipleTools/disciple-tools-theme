import{i as t,y as e}from"../../lit-element-2409d5fe.js";import{i}from"../../style-map-ac85d91b.js";import{m as o}from"../../lit-localize-763e4978.js";import{D as r}from"../dt-form-base.js";import"../../icons/dt-spinner.js";import"../../icons/dt-checkmark.js";import"../../directive-de55b00a.js";import"../../dt-base.js";import"../dt-label/dt-label.js";class s extends r{static get styles(){return[...super.styles,t`:host{position:relative;font-family:Helvetica,Arial,sans-serif}.input-group{color:var(--dt-multi-select-text-color,#0a0a0a);margin-bottom:1rem}.input-group.disabled .field-container,.input-group.disabled input{background-color:var(--disabled-color)}.input-group.disabled a,.input-group.disabled button{cursor:not-allowed;pointer-events:none}.input-group.disabled :hover{cursor:not-allowed}.field-container{background-color:var(--dt-multi-select-background-color,#fefefe);border:1px solid var(--dt-form-border-color,#cacaca);border-radius:0;color:var(--dt-multi-select-text-color,#0a0a0a);font-size:1rem;font-weight:300;min-height:2.5rem;line-height:1.5;margin:0;padding-top:calc(.5rem - .375rem);padding-bottom:.5rem;padding-inline:.5rem 1.6rem;box-sizing:border-box;width:100%;text-transform:none;display:flex;flex-wrap:wrap}.field-container .selected-option,.field-container input{height:1.25rem}.selected-option{border:1px solid var(--dt-multi-select-tag-border-color,#c2e0ff);background-color:var(--dt-multi-select-tag-background-color,#c2e0ff);display:flex;font-size:.875rem;position:relative;border-radius:2px;margin-inline-end:4px;margin-block-start:.375rem;box-sizing:border-box}.selected-option>:first-child{padding-inline-start:4px;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;--container-padding:calc(0.5rem + 1.6rem + 2px);--option-padding:8px;--option-button:20px;max-width:calc(var(--container-width) - var(--container-padding) - var(--option-padding) - var(--option-button))}.selected-option *{align-self:center}.selected-option button{background:0 0;outline:0;border:0;border-inline-start:1px solid var(--dt-multi-select-tag-border-color,#c2e0ff);color:var(--dt-multi-select-text-color,#0a0a0a);margin-inline-start:4px}.selected-option button:hover{cursor:pointer}.field-container input{background-color:var(--dt-form-background-color,#fff);color:var(--dt-form-text-color,#000);flex-grow:1;min-width:50px;border:0;margin-block-start:.375rem}.field-container input:active,.field-container input:focus,.field-container input:focus-visible{border:0;outline:0}.field-container input::placeholder{color:var(--dt-text-placeholder-color,#999);opacity:1}.option-list{list-style:none;margin:0;padding:0;border:1px solid var(--dt-form-border-color,#cacaca);background:var(--dt-form-background-color,#fefefe);z-index:10;position:absolute;width:100%;top:0;left:0;box-shadow:var(--shadow-1);max-height:150px;overflow-y:scroll}.option-list li{border-block-start:1px solid var(--dt-form-border-color,#cacaca);outline:0}.option-list li button,.option-list li div{padding:.5rem .75rem;color:var(--dt-multi-select-text-color,#0a0a0a);font-weight:100;font-size:1rem;text-decoration:none;text-align:inherit}.option-list li button{display:block;width:100%;border:0;background:0 0}.option-list li button.active,.option-list li button:hover{cursor:pointer;background:var(--dt-multi-select-option-hover-background,#f5f5f5)}`]}static get properties(){return{...super.properties,placeholder:{type:String},options:{type:Array},filteredOptions:{type:Array,state:!0},value:{type:Array,reflect:!0},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},onchange:{type:String}}}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1}updated(){this._scrollOptionListToActive();const t=this.shadowRoot.querySelector(".input-group");!t.style.getPropertyValue("--container-width")&&t.clientWidth>0&&t.style.setProperty("--container-width",`${t.clientWidth}px`)}_scrollOptionListToActive(){const t=this.shadowRoot.querySelector(".option-list"),e=this.shadowRoot.querySelector("button.active");if(t&&e){const i=e.offsetTop,o=e.offsetTop+e.clientHeight,r=t.scrollTop;o>t.scrollTop+t.clientHeight?t.scrollTo({top:o-t.clientHeight,behavior:"smooth"}):i<r&&t.scrollTo({top:i,behavior:"smooth"})}}_clickOption(t){t.target&&t.target.value&&this._select(t.target.value)}_touchStart(t){t.target&&(this.detectTap=!1)}_touchMove(t){t.target&&(this.detectTap=!0)}_touchEnd(t){this.detectTap||(t.target&&t.target.value&&this._clickOption(t),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&this._select(this.filteredOptions[this.activeIndex].id)}_select(t){const e=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});if(this.value&&this.value.length)if("string"==typeof this.value[0])this.value=[...this.value.filter((e=>e!==`-${t}`)),t];else{let e=!1;const i=this.value.map((i=>{const o={...i};return i.id===t.id&&i.delete&&(delete o.delete,e=!0),o}));e||i.push(t),this.value=i}else this.value=[t];e.detail.newValue=this.value,this.open=!1,this.activeIndex=-1,this.dispatchEvent(e),this._setFormValue(this.value)}_remove(t){if(t.target&&t.target.dataset&&t.target.dataset.value){const e=new CustomEvent("change",{detail:{field:this.name,oldValue:this.value}});this.value=(this.value||[]).map((e=>e===t.target.dataset.value?`-${e}`:e)),e.detail.newValue=this.value,this.dispatchEvent(e),this.open&&this.shadowRoot.querySelector("input").focus()}}static _focusInput(t){t.target===t.currentTarget&&t.target.getElementsByTagName("input")[0].focus()}_inputFocusIn(){this.open=!0,this.activeIndex=-1}_inputFocusOut(t){t.relatedTarget&&["BUTTON","LI"].includes(t.relatedTarget.nodeName)||(this.open=!1)}_inputKeyDown(t){switch(t.keyCode||t.which){case 8:""===t.target.value&&(this.value=this.value.slice(0,-1));break;case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:t.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0}}_inputKeyUp(t){this.query=t.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length-1,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}_filterOptions(){return this.filteredOptions=(this.options||[]).filter((t=>!(this.value||[]).includes(t.id)&&(!this.query||t.label.toLocaleLowerCase().includes(this.query.toLocaleLowerCase())))),this.filteredOptions}willUpdate(t){if(super.willUpdate(t),t){const e=t.has("value"),i=t.has("query"),o=t.has("options");if((e||i||o)&&this._filterOptions(),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}}_renderSelectedOptions(){return this.options&&this.options.filter((t=>this.value&&this.value.indexOf(t.id)>-1)).map((t=>e`<div class="selected-option"><span>${t.label}</span> <button @click="${this._remove}" ?disabled="${this.disabled}" data-value="${t.id}">x</button></div>`))}_renderOption(t,i){return e`<li tabindex="-1"><button value="${t.id}" type="button" data-label="${t.label}" @click="${this._clickOption}" @touchstart="${this._touchStart}" @touchmove="${this._touchMove}" @touchend="${this._touchEnd}" tabindex="-1" class="${this.activeIndex>-1&&this.activeIndex===i?"active":""}">${t.label}</button></li>`}_renderOptions(){return this.filteredOptions.length?this.filteredOptions.map(((t,e)=>this._renderOption(t,e))):e`<li><div>${o("No Data Available")}</div></li>`}render(){const t={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"};return e`${this.labelTemplate()}<div class="input-group ${this.disabled?"disabled":""}"><div class="field-container" @click="${this._focusInput}" @keydown="${this._focusInput}">${this._renderSelectedOptions()} <input type="text" placeholder="${this.placeholder}" @focusin="${this._inputFocusIn}" @blur="${this._inputFocusOut}" @keydown="${this._inputKeyDown}" @keyup="${this._inputKeyUp}" ?disabled="${this.disabled}"></div><ul class="option-list" style="${i(t)}">${this._renderOptions()}</ul>${this.loading?e`<dt-spinner class="icon-overlay"></dt-spinner>`:null} ${this.saved?e`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null} ${this.error?e`<dt-icon icon="mdi:alert-circle" class="icon-overlay alert" tooltip="${this.error}" size="2rem"></dt-icon>`:null}</div>`}}window.customElements.define("dt-multi-select",s);export{s as D};
