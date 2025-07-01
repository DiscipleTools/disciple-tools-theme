import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardControls extends OpenLitElement {
  static get properties() {
    return {
      hideBack: { type: Boolean },
      hideSkip: { type: Boolean },
      backLabel: { type: String },
      nextLabel: { type: String },
      skipLabel: { type: String },
      saving: { type: Boolean },
    };
  }
  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  next() {
    if (this.saving) {
      return;
    }
    this.dispatchEvent(new CustomEvent('next'));
  }
  skip() {
    this.dispatchEvent(new CustomEvent('skip'));
  }
  render() {
    return html`
      <div class="cluster" position="end">
        ${this.hideSkip
          ? ''
          : html`
              <button @click=${this.skip} class="btn-outline">
                ${this.skipLabel ?? 'Skip'}
              </button>
            `}
        ${this.hideBack
          ? ''
          : html`
              <button @click=${this.back}>${this.backLabel ?? 'Back'}</button>
            `}
        <button
          @click=${this.next}
          class="btn-primary ${this.saving ? 'saving' : ''}"
          ?disabled=${this.saving}
        >
          ${this.nextLabel ?? 'Next'}
          ${this.saving ? html`<span class="spinner light"></span>` : ''}
        </button>
      </div>
    `;
  }
}
customElements.define('setup-wizard-controls', SetupWizardControls);
