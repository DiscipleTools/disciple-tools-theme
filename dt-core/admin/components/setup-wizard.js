import {html, css, LitElement} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/core/lit-core.min.js';

console.log('setup-wizard.js')

export class SetupWizard extends LitElement {
    static styles = [
        css`
            :host {
                display: block;
            }
            .wrap {
                padding: 1rem;
            }
        `
    ];

    constructor() {
        super()

        this.translations = setupWizardShare.translations
    }

    render() {
        return html`
            <div class="wrap">
                <h2>${this.translations.title}</h2>
            </div>
        `;
    }
}
customElements.define('setup-wizard', SetupWizard);
