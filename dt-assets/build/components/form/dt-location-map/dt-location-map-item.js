import{s as t,i as e,y as o}from"../../lit-element-2409d5fe.js";import{i}from"../../style-map-ac85d91b.js";import{m as a}from"../../lit-localize-763e4978.js";import"../../icons/dt-icon.js";import"./dt-map-modal.js";import"../../directive-de55b00a.js";import"../../dt-base.js";import"../../layout/dt-modal/dt-modal.js";import"../../class-map-8d921948.js";class s{constructor(t){this.token=t}async searchPlaces(t,e="en"){const o=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],limit:6,access_token:this.token,language:e}),i=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(t)}.json?${o}`,a=await fetch(i,{method:"GET",headers:{"Content-Type":"application/json"}});return(await a.json())?.features}async reverseGeocode(t,e,o="en"){const i=new URLSearchParams({types:["country","region","postcode","district","place","locality","neighborhood","address"],access_token:this.token,language:o}),a=`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURI(t)},${encodeURI(e)}.json?${i}`,s=await fetch(a,{method:"GET",headers:{"Content-Type":"application/json"}});return(await s.json())?.features}}class n{constructor(t,e,o){if(this.token=t,this.window=e,!e.google?.maps?.places?.AutocompleteService){let e=o.createElement("script");e.src=`https://maps.googleapis.com/maps/api/js?libraries=places&key=${t}`,o.body.appendChild(e)}}async getPlacePredictions(t,e="en"){if(this.window.google){const o=new this.window.google.maps.places.AutocompleteService,{predictions:i}=await o.getPlacePredictions({input:t,language:e});return i}return null}async getPlaceDetails(t,e="en"){const o=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,address:t,language:e})}`,i=await fetch(o,{method:"GET"}),a=await i.json();let s=[];if("OK"===a.status)s=a.results;return s&&s.length?s[0]:null}async reverseGeocode(t,e,o="en"){const i=`https://maps.googleapis.com/maps/api/geocode/json?${new URLSearchParams({key:this.token,latlng:`${e},${t}`,language:o,result_type:["point_of_interest","establishment","premise","street_address","neighborhood","sublocality","locality","colloquial_area","political","country"].join("|")})}`,a=await fetch(i,{method:"GET"});return(await a.json())?.results}}window.customElements.define("dt-location-map-item",class extends t{static get properties(){return{id:{type:String,reflect:!0},placeholder:{type:String},mapboxToken:{type:String,attribute:"mapbox-token"},googleToken:{type:String,attribute:"google-token"},metadata:{type:Object},disabled:{type:Boolean},open:{type:Boolean,state:!0},query:{type:String,state:!0},activeIndex:{type:Number,state:!0},containerHeight:{type:Number,state:!0},loading:{type:Boolean},saved:{type:Boolean},filteredOptions:{type:Array,state:!0}}}static get styles(){return[e`:host{position:relative;font-family:Helvetica,Arial,sans-serif;display:block}.input-group{color:var(--dt-multi-select-text-color,#0a0a0a);margin-bottom:1rem}.input-group.disabled .field-container,.input-group.disabled input{background-color:var(--disabled-color)}.input-group.disabled a,.input-group.disabled button{cursor:not-allowed;pointer-events:none}.input-group.disabled :hover{cursor:not-allowed}.option-list{list-style:none;margin:0;padding:0;border:1px solid var(--dt-form-border-color,#cacaca);background:var(--dt-form-background-color,#fefefe);z-index:10;position:absolute;width:var(--container-width,100%);width:100%;top:0;left:0;box-shadow:var(--shadow-1);max-height:150px;overflow-y:scroll}.option-list li{border-block-start:1px solid var(--dt-form-border-color,#cacaca);outline:0}.option-list li button,.option-list li div{padding:.5rem .75rem;color:var(--dt-multi-select-text-color,#0a0a0a);font-weight:100;font-size:1rem;text-decoration:none;text-align:inherit}.option-list li button{display:block;width:100%;border:0;background:0 0}.option-list li button.active,.option-list li button:hover{cursor:pointer;background:var(--dt-multi-select-option-hover-background,#f5f5f5)}`,e`input{color:var(--dt-form-text-color,#000);appearance:none;background-color:var(--dt-location-map-background-color,#fefefe);border:1px solid var(--dt-location-map-border-color,#fefefe);border-radius:var(--dt-location-map-border-radius,0);box-shadow:var(--dt-location-map-box-shadow,var(--dt-form-input-box-shadow,inset 0 1px 2px hsl(0deg 0 4% / 10%)));box-sizing:border-box;display:block;font-family:inherit;font-size:1rem;font-weight:300;line-height:1.5;margin:0;padding:var(--dt-form-padding,.5333333333rem);transition:var(--dt-form-transition,box-shadow .5s,border-color .25s ease-in-out)}input:disabled,input[readonly],textarea:disabled,textarea[readonly]{background-color:var(--dt-text-disabled-background-color,var(--dt-form-disabled-background-color,#e6e6e6));cursor:not-allowed}input.disabled{color:var(--dt-text-placeholder-color,#999)}input:focus-visible,input:focus-within{outline:0}input::placeholder{color:var(--dt-text-placeholder-color,#999);text-transform:var(--dt-text-placeholder-transform,none);font-size:var(--dt-text-placeholder-font-size,1rem);font-weight:var(--dt-text-placeholder-font-weight,400);letter-spacing:var(--dt-text-placeholder-letter-spacing,normal)}input.invalid{border-color:var(--dt-text-border-color-alert,var(--alert-color))}.field-container{display:flex;margin-bottom:.5rem}.field-container input{flex-grow:1}.field-container .input-addon{flex-shrink:1;display:flex;justify-content:center;align-items:center;aspect-ratio:1/1;padding:10px;border:solid 1px gray;border-collapse:collapse;color:var(--dt-location-map-button-color,#cc4b37);background-color:var(--dt-location-map-background-color,buttonface);border:1px solid var(--dt-location-map-border-color,#fefefe);border-radius:var(--dt-location-map-border-radius,0);box-shadow:var(--dt-location-map-box-shadow,var(--dt-form-input-box-shadow,inset 0 1px 2px hsl(0deg 0 4% / 10%)))}.field-container .input-addon:hover{background-color:var(--dt-location-map-button-hover-background-color,#cc4b37);color:var(--dt-location-map-button-hover-color,#fff)}.input-addon:disabled{background-color:var(--dt-form-disabled-background-color);color:var(--dt-text-placeholder-color,#999)}.input-addon:disabled:hover{background-color:var(--dt-form-disabled-background-color);color:var(--dt-text-placeholder-color,#999);cursor:not-allowed}`,e`.icon-overlay{position:absolute;inset-inline-end:1rem;top:0;inset-inline-end:3rem;height:100%;display:flex;justify-content:center;align-items:center}.icon-overlay.alert{color:var(--alert-color)}.icon-overlay.success{color:var(--success-color)}`]}constructor(){super(),this.activeIndex=-1,this.filteredOptions=[],this.detectTap=!1,this.debounceTimer=null}connectedCallback(){super.connectedCallback(),this.addEventListener("autofocus",(async t=>{await this.updateComplete;const e=this.shadowRoot.querySelector("input");e&&e.focus()})),this.mapboxToken&&(this.mapboxService=new s(this.mapboxToken)),this.googleToken&&(this.googleGeocodeService=new n(this.googleToken,window,document))}updated(t){this._scrollOptionListToActive();const e=this.shadowRoot.querySelector(".input-group");e.style.getPropertyValue("--container-width")||e.style.setProperty("--container-width",`${e.clientWidth}px`)}_scrollOptionListToActive(){const t=this.shadowRoot.querySelector(".option-list"),e=this.shadowRoot.querySelector("button.active");if(t&&e){const o=e.offsetTop,i=e.offsetTop+e.clientHeight,a=t.scrollTop;i>t.scrollTop+t.clientHeight?t.scrollTo({top:i-t.clientHeight,behavior:"smooth"}):o<a&&t.scrollTo({top:o,behavior:"smooth"})}}_clickOption(t){const e=t.currentTarget??t.target;e&&e.value&&this._select(JSON.parse(e.value))}_touchStart(t){t.target&&(this.detectTap=!1)}_touchMove(t){t.target&&(this.detectTap=!0)}_touchEnd(t){this.detectTap||(t.target&&t.target.value&&this._clickOption(t),this.detectTap=!1)}_keyboardSelectOption(){this.activeIndex>-1&&(this.activeIndex<this.filteredOptions.length?this._select(this.filteredOptions[this.activeIndex]):this._select({value:this.query,label:this.query}))}async _select(t){if(t.place_id&&this.googleGeocodeService){this.loading=!0;const e=await this.googleGeocodeService.getPlaceDetails(t.label,this.locale);this.loading=!1,e&&(t.lat=e.geometry.location.lat,t.lng=e.geometry.location.lng,t.level=e.types&&e.types.length?e.types[0]:null)}const e={detail:{metadata:t},bubbles:!1};this.dispatchEvent(new CustomEvent("select",e)),this.metadata=t;const o=this.shadowRoot.querySelector("input");o&&(o.value=t?.label),this.open=!1,this.activeIndex=-1}get _focusTarget(){let t=this._field;return this.metadata&&(t=this.shadowRoot.querySelector("button")||t),t}_inputFocusIn(){this.activeIndex=-1}_inputFocusOut(t){t.relatedTarget&&["BUTTON","LI"].includes(t.relatedTarget.nodeName)||(this.open=!1)}_inputKeyDown(t){switch(t.keyCode||t.which){case 38:this.open=!0,this._listHighlightPrevious();break;case 40:this.open=!0,this._listHighlightNext();break;case 9:this.activeIndex<0?this.open=!1:t.preventDefault(),this._keyboardSelectOption();break;case 13:this._keyboardSelectOption();break;case 27:this.open=!1,this.activeIndex=-1;break;default:this.open=!0}}_inputKeyUp(t){const e=t.keyCode||t.which;t.target.value&&![9,13].includes(e)&&(this.open=!0),this.query=t.target.value}_listHighlightNext(){this.activeIndex=Math.min(this.filteredOptions.length,this.activeIndex+1)}_listHighlightPrevious(){this.activeIndex=Math.max(0,this.activeIndex-1)}async _filterOptions(){if(this.query)if(this.googleToken&&this.googleGeocodeService){this.loading=!0;try{const t=await this.googleGeocodeService.getPlacePredictions(this.query,this.locale);this.filteredOptions=(t||[]).map((t=>({label:t.description,place_id:t.place_id,source:"user",raw:t}))),this.loading=!1}catch(t){return console.error(t),this.error=!0,void(this.loading=!1)}}else if(this.mapboxToken&&this.mapboxService){this.loading=!0;const t=await this.mapboxService.searchPlaces(this.query,this.locale);this.filteredOptions=t.map((t=>({lng:t.center[0],lat:t.center[1],level:t.place_type[0],label:t.place_name,source:"user"}))),this.loading=!1}return this.filteredOptions}willUpdate(t){if(super.willUpdate(t),t){if(t.has("query")&&(this.error=!1,clearTimeout(this.debounceTimer),this.debounceTimer=setTimeout((()=>this._filterOptions()),300)),!this.containerHeight&&this.shadowRoot.children&&this.shadowRoot.children.length){const t=this.shadowRoot.querySelector(".input-group");t&&(this.containerHeight=t.offsetHeight)}}}_change(){}_delete(){const t={detail:{metadata:this.metadata},bubbles:!1};this.dispatchEvent(new CustomEvent("delete",t))}_openMapModal(){this.shadowRoot.querySelector("dt-map-modal").dispatchEvent(new Event("open"))}async _onMapModalSubmit(t){if(t?.detail?.location?.lat){const{location:e}=t?.detail,{lat:o,lng:i}=e;if(this.googleGeocodeService){const t=await this.googleGeocodeService.reverseGeocode(i,o,this.locale);if(t&&t.length){const e=t[0];this._select({lng:e.geometry.location.lng,lat:e.geometry.location.lat,level:e.types&&e.types.length?e.types[0]:null,label:e.formatted_address,source:"user"})}}else if(this.mapboxService){const t=await this.mapboxService.reverseGeocode(i,o,this.locale);if(t&&t.length){const e=t[0];this._select({lng:e.center[0],lat:e.center[1],level:e.place_type[0],label:e.place_name,source:"user"})}}}}_renderOption(t,e,i){return o`<li tabindex="-1"><button value="${JSON.stringify(t)}" type="button" @click="${this._clickOption}" @touchstart="${this._touchStart}" @touchmove="${this._touchMove}" @touchend="${this._touchEnd}" tabindex="-1" class="${this.activeIndex>-1&&this.activeIndex===e?"active":""}">${i??t.label}</button></li>`}_renderOptions(){let t=[];return this.filteredOptions.length?t.push(...this.filteredOptions.map(((t,e)=>this._renderOption(t,e)))):this.loading?t.push(o`<li><div>${a("Loading...")}</div></li>`):t.push(o`<li><div>${a("No Data Available")}</div></li>`),t.push(this._renderOption({value:this.query,label:this.query},(this.filteredOptions||[]).length,o`<strong>${a("Use")}: "${this.query}"</strong>`)),t}render(){const t={display:this.open?"block":"none",top:this.containerHeight?`${this.containerHeight}px`:"2.5rem"},e=!!this.metadata?.label,a=this.metadata?.lat&&this.metadata?.lng;return o`<div class="input-group"><div class="field-container"><input type="text" class="${this.disabled?"disabled":null}" placeholder="${this.placeholder}" value="${this.metadata?.label}" .disabled="${e&&a||this.disabled}" @focusin="${this._inputFocusIn}" @blur="${this._inputFocusOut}" @keydown="${this._inputKeyDown}" @keyup="${this._inputKeyUp}"> ${e&&a?o`<button class="input-addon btn-map" @click="${this._openMapModal}" ?disabled="${this.disabled}"><dt-icon icon="mdi:map"></button>`:null} ${e?o`<button class="input-addon btn-delete" @click="${this._delete}" ?disabled="${this.disabled}"><dt-icon icon="mdi:trash-can-outline"></button>`:o`<button class="input-addon btn-pin" @click="${this._openMapModal}" ?disabled="${this.disabled}"><dt-icon icon="mdi:map-marker-radius"></button>`}</div><ul class="option-list" style="${i(t)}">${this._renderOptions()}</ul>${this.touched&&this.invalid||this.error?o`<dt-exclamation-circle class="icon-overlay alert"></dt-exclamation-circle>`:null} ${this.loading?o`<dt-spinner class="icon-overlay"></dt-spinner>`:null} ${this.saved?o`<dt-checkmark class="icon-overlay success"></dt-checkmark>`:null}</div><dt-map-modal .metadata="${this.metadata}" mapbox-token="${this.mapboxToken}" @submit="${this._onMapModalSubmit}">`}});