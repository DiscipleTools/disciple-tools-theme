import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardControls extends OpenLitElement {
    static get properties() {
        return {
            translations: { type: Object },
            hideBack: { type: Boolean },
        };
    }
    back() {
        this.dispatchEvent(new CustomEvent('back'))
    }
    next() {
        this.dispatchEvent(new CustomEvent('next'))
    }
    render() {
        return html`
            <div class="cluster" position="end">
                ${
                    this.hideBack ? '' : html`
                        <button @click=${this.back}>${this.translations?.back ?? 'back'}</button>
                    `
                }
                <button @click=${this.next} class="btn-primary">${this.translations?.next ?? 'next'}</button>
            </div>
        `
    }
}
customElements.define('setup-wizard-controls', SetupWizardControls);
