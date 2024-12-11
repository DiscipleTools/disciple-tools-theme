import {html, css, LitElement} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/core/lit-core.min.js';

console.log('setup-wizard.js')

export class SetupWizard extends LitElement {
    static styles = [
        css`
            :host {
                display: block;
                font-size: 18px;
                line-height: 1.4;
            }
            /* Resets */
            /* Inherit fonts for inputs and buttons */
            input, button,
            textarea, select {
                font-family: inherit;
                font-size: inherit;
            }
            /* Set shorter line heights on headings and interactive elements */
            h1, h2, h3, h4,
            button, input, label {
                line-height: 1.1;
            }
            /* Box sizing rules */
            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }
            /* Global */
            h1, h2, h3 {
                font-weight: 500;
                color: #3f729b;
            }
            button {
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
            select, input {
                padding: 0.2em 0.5em;
                border-radius: 8px;
                border: 2px solid #cdcdcd;
                background-color: white;
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
            .flow {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }
            .flow > * {
                margin-block: 0;
            }
            .flow > * + * {
                margin-block-start: var(--spacing, 1rem);
            }
            .grid {
                display: grid;
                grid-gap: 1rem;

                &[size="small"] {
                    --column-size: 100px;
                }
            }
            @supports (width: min(250px, 100%)) {
                .grid {
                    grid-template-columns: repeat(auto-fit, minmax(min(var(--column-size, 250px), 100%), 1fr));
                }
            }
            /* Utilities */
            .ms-auto {
                margin-left: auto;
            }
            .align-start {
                align-items: flex-start;
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
            .btn-card {
                padding: 1rem 2rem;
                box-shadow: 1px 1px 3px 0px #ababab;
            }
            .input-group {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }
            .toggle {
                position: relative;
                display: inline-block;

                input {
                    display: none;
                }
                span {
                    display: inline-block;
                    padding-block: 1rem;
                    background-color: #cdcdcd;
                    border-radius: 8px;
                    width: 100%;
                    text-align: center;
                }
                input:checked + span {
                    background-color: #4caf50;
                    color: white;
                }
            }
            .breadcrumbs {
                --gap: 6rem;
                --divider-width: calc( var(--gap) / 2 );
                display: flex;
            }
            .breadcrumbs > * + * {
                margin-left: var(--gap);
            }
            .breadcrumbs > * + *:before {
                content: '';
                width: var(--divider-width);
                position: absolute;
                height: 3px;
                border-radius: 10px;
                background-color: #3F729B;
                left: calc( ( var(--gap) + var(--divider-width) ) / -2 - 2px );
                top: calc(50% - 1px);
            }
            .crumb {
                position: relative;
                width: 16px;
                height: 16px;
                border-radius: 100%;
                border: 2px solid #cdcdcd;
            }
            .crumb.complete {
                background-color: #3F729B;
                border-color: #3F729B;
            }
            .crumb.active {
                outline: 5px solid #3F729B;
                outline-offset: -10px;
            }
        `
    ];

    static get properties() {
        return {
            steps: { type: Array },
        };
    }

    constructor() {
        super()

        this.translations = setupWizardShare.translations
        this.steps = []

        const url = new URL(location.href)

        this.isKitchenSink = url.searchParams.has('kitchen-sink')
    }

    firstUpdated() {
        if (this.steps.length === 0 && setupWizardShare && setupWizardShare.steps && setupWizardShare.steps.length !== 0) {
            this.steps = setupWizardShare.steps
        }
    }

    render() {
        return html`
            <div class="wrap">

                <div class="wizard">
                    <div class="content">
                        <h2>${this.translations.title}</h2>
                        ${
                            this.isKitchenSink ? html`
                                <div class="flow">
                                    <h3>A cluster of buttons</h3>
                                    <div class="cluster">
                                        <button>Bog standard button</button>
                                        <button class="btn-primary">Primary button</button>
                                    </div>
                                    <h3>A grid of button cards</h3>
                                    <div class="grid">
                                        <button class="btn-card">A button card</button>
                                        <button class="btn-card">A button card</button>
                                        <button class="btn-card">A button card</button>
                                    </div>
                                    <h3>Fields</h3>
                                    <div class="input-group">
                                        <label for="foo">Foo</label>
                                        <input placeholder="foo" type="text" name="foo" id="foo">
                                    </div>
                                    <div class="input-group">
                                        <label for="bar">bar</label>
                                        <input placeholder="bar" type="text" name="bar" id="bar">
                                    </div>
                                    <div class="input-group">
                                        <label for="day">day</label>
                                        <select name="day" id="day">
                                            <option value="1">1</option>
                                            <option value="any">any</option>
                                            <option value="thing">thing</option>
                                        </select>
                                    </div>
                                    <h3>Breadcrumbs</h3>
                                    <div class="breadcrumbs">
                                        <div class="crumb complete" title="foo"></div>
                                        <div class="crumb active" title="bar"></div>
                                        <div class="crumb" title="day"></div>
                                    </div>

                                    <h3>Selectable items</h3>
                                    <div class="grid" size="small">
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span>Name</span>
                                        </label>
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span>Gender</span>
                                        </label>
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span>Email</span>
                                        </label>
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span>Location</span>
                                        </label>
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span>Phone</span>
                                        </label>
                                    </div>
                                </div>
                            ` : html`
                                Content here
                                <div class="cluster ms-auto">
                                    <button>${this.translations.back}</button>
                                    <button class="btn-primary">${this.translations.next}</button>
                                </div>
                            `
                        }
                    </div>
                </div>
            </div>
        `;
    }
}
customElements.define('setup-wizard', SetupWizard);
