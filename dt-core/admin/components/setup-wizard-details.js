import {
  html,
  css,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardDetails extends OpenLitElement {
  static styles = [
    css`
      :host {
        display: block;
      }
    `,
  ];

  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
    };
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  next() {
    this.dispatchEvent(new CustomEvent('next'));
  }

  render() {
    return html`
      <div class="step-layout">
        <div class="content">Sort out details here</div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-details', SetupWizardDetails);
