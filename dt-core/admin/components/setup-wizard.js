import {html, css, LitElement} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/core/lit-core.min.js';

console.log('setup-wizard.js')

export class SetupWizard extends LitElement {
    static styles = [
        css`
            /* Global */
            :host {
                display: block;
                font-size: 18px;
                line-height: 1.4;
            }
            h1, h2 {
                font-weight: 500;
                color: #3f729b;
            }
            button {
                font-size: inherit;
                border: none;
                padding: 0.5rem 1.5rem;
                border-radius: 8px;
                cursor: pointer;
                background-color: #efefef;
                transition: background-color 120ms linear;
            }
            button:hover,
            button:active,
            button:focus {
                background-color: #cdcdcd;
            }
            /* Composition */
            .wrap {
                padding: 1rem;
                min-height: 80vh;
            }
            .with-sidebar {
                display: flex;
                flex-wrap: wrap;
                gap: var(--s1);
            }
            .with-sidebar > :first-child {
                flex-basis: 0;
                flex-grow: 999;
                min-inline-size: 70%;
            }
            .with-sidebar > :last-child {
                flex-grow: 1;
            }
            .cluster {
                display: flex;
                flex-wrap: wrap;
                gap: var(--space, 1rem);
                justify-content: flex-start;
                align-items: center;
            }
            /* Utilities */
            .flex-end {
                justify-content: end;
            }
            /* Blocks */
            .wizard {
                border-radius: 12px;
                border: 1px solid transparent;
                overflow: hidden;
            }
            .sidebar {
                background-color: grey;
                color: white;
                padding: 1rem;
            }
            .content {
                background-color: white;
                padding: 1rem;
            }
            .btn-primary {
                background-color: #3f729b;
                color: #fefefe;
            }
            .btn-primary:hover,
            .btn-primary:focus,
            .btn-primary:active {
                background-color: #366184;
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

                <div class="with-sidebar | wizard">
                    <div class="content">
                        <h2>${this.translations.title}</h2>
                        Content here
                        <div class="cluster flex-end">
                            <button>${this.translations.back}</button>
                            <button class="btn-primary">${this.translations.next}</button>
                        </div>
                    </div>
                    <div class="sidebar">
                        Sidebar here
                    </div>
                </div>
            </div>
        `;
    }
}
customElements.define('setup-wizard', SetupWizard);
