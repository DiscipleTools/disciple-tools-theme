import {
  html,
  css,
  LitElement,
  staticHtml,
  unsafeStatic,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';

export class SetupWizard extends LitElement {
  static styles = [
    css`
      :host {
        display: block;
        font-size: 18px;
        line-height: 1.4;
        font-family: Arial, Helvetica, sans-serif;
        --primary-color: #3f729b;
        --primary-hover-color: #366184;
        --secondary-color: #4caf50;
        --default-color: #efefef;
        --default-hover-color: #cdcdcd;
        --default-dark: #ababab;
      }
      /* Resets */
      /* Inherit fonts for inputs and buttons */
      input,
      button,
      textarea,
      select {
        font-family: inherit;
        font-size: inherit;
      }
      /* Set shorter line heights on headings and interactive elements */
      h1,
      h2,
      h3,
      h4,
      button,
      input,
      label {
        line-height: 1.1;
      }
      /* Box sizing rules */
      *,
      *::before,
      *::after {
        box-sizing: border-box;
      }
      /* Global */
      h1,
      h2,
      h3 {
        font-weight: 500;
        color: var(--primary-color);
      }
      button {
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        background-color: var(--default-color);
        transition: all 120ms linear;
      }
      button:hover,
      button:active,
      button:focus {
        background-color: var(--default-hover-color);
      }
      select,
      input {
        padding: 0.2em 0.5em;
        border-radius: 8px;
        border: 2px solid var(--default-hover-color);
        background-color: white;
      }
      /* Composition */
      .wrap {
        padding: 1rem;
        min-height: 100vh;
      }
      .cluster {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space, 1rem);
        justify-content: flex-start;
        align-items: center;
      }
      .cluster[position='end'] {
        justify-content: flex-end;
      }
      .repel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        flex-direction: row-reverse;
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

        &[size='small'] {
          --column-size: 100px;
        }
      }
      @supports (width: min(250px, 100%)) {
        .grid {
          grid-template-columns: repeat(
            auto-fit,
            minmax(min(var(--column-size, 250px), 100%), 1fr)
          );
        }
      }
      .cover {
        display: flex;
        flex-direction: column;
        min-block-size: 80vh;
      }
      .cover > * {
        margin-block: 0.5rem;
      }
      .cover > .content {
        margin-block-end: auto;
      }
      .cover > :first-child:not(.content) {
        margin-block-start: 0;
      }
      .cover > :last-child:not(.content) {
        margin-block-end: 0;
      }
      /* Utilities */
      .ms-auto {
        margin-left: auto;
      }
      .me-auto {
        margin-right: auto;
      }
      .align-start {
        align-items: flex-start;
      }
      .white {
        color: white;
      }
      /* Blocks */
      .wizard {
        border-radius: 12px;
        border: 1px solid transparent;
        overflow: hidden;
        background-color: white;
        padding: 1rem;
      }
      .sidebar {
        background-color: grey;
        color: white;
        padding: 1rem;
      }
      .btn-primary {
        background-color: var(--primary-color);
        color: var(--default-color);
      }
      .btn-primary:hover,
      .btn-primary:focus,
      .btn-primary:active {
        background-color: var(--primary-hover-color);
      }
      .btn-outline {
        border: 1px solid transparent;
        background-color: transparent;
        color: var(--primary-color);
      }
      .btn-outline:hover,
      .btn-outline:focus {
        border-color: var(--primary-color);
        background-color: transparent;
      }
      .btn-card {
        background-color: var(--primary-color);
        color: var(--default-color);
        padding: 1rem 2rem;
        box-shadow: 1px 1px 3px 0px var(--default-dark);
        cursor: pointer;
        position: relative;
      }
      .btn-card.selected {
        background-color: var(--secondary-color);
      }
      .btn-card.selected:focus,
      .btn-card.selected:hover,
      .btn-card.selected:active {
        background-color: var(--secondary-color);
      }
      .btn-card:focus,
      .btn-card:hover,
      .btn-card:active {
        background-color: var(--primary-hover-color);
      }
      .btn-card-gray {
        background-color: var(--default-color);
        color: black;
      }
      .btn-card-gray:hover {
        background-color: var(--secondary-color);
        opacity: 0.8;
      }
      .btn-card.disabled {
        background-color: var(--secondary-color);
        opacity: 0.5;
        color: var(--default-color);
      }
      .btn-card.disabled:hover {
        background-color: var(--default-hover-color);
      }
      .input-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
      }
      .toggle {
        position: relative;
        display: inline-flex;
        cursor: pointer;
        input {
          display: none;
        }
        div {
          display: inline-block;
          padding: 1rem 0.5rem;
          background-color: var(--default-color);
          border-radius: 8px;
          width: 100%;
          text-align: center;
          transition: all 120ms linear;
        }
        input:checked + div {
          background-color: var(--secondary-color);
          color: white;
          h1,
          h2,
          h3 {
            color: white;
          }
        }
      }
      .breadcrumbs {
        --gap: 6rem;
        --divider-width: calc(var(--gap) / 2);
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
        background-color: var(--primary);
        left: calc((var(--gap) + var(--divider-width)) / -2 - 2px);
        top: calc(50% - 1px);
      }
      .crumb {
        position: relative;
        width: 16px;
        height: 16px;
        border-radius: 100%;
        border: 2px solid var(--default-hover-color);
      }
      .crumb.complete {
        background-color: var(--primary);
        border-color: var(--primary);
      }
      .crumb.active {
        outline: 5px solid var(--primary);
        outline-offset: -10px;
      }
      .tag {
        border: 1px solid black;
        display: inline;
        padding: 0.2em 0.5em;
        background-color: var(--primary-color);
      }
      .spinner {
        background: url('images/spinner.gif') no-repeat;
        background-size: 20px 20px;
        opacity: 0.7;
        width: 20px;
        height: 20px;
        display: block;
      }
      table {
        margin-bottom: 1rem;
      }
      table td {
        padding: 0.5rem;
      }
      table thead tr {
        background-color: var(--default-color);
      }
      table tr:nth-child(even) {
        background-color: var(--default-color);
      }
    `,
  ];

  static get properties() {
    return {
      steps: { type: Array },
      currentStepNumber: { type: Number, attribute: false },
      decision: { type: String, attribute: false },
    };
  }

  constructor() {
    super();

    this.translations = window.setupWizardShare.translations;
    this.adminUrl = window.setupWizardShare.admin_url;
    this.steps = [];
    this.currentStepNumber = 0;

    const url = new URL(location.href);

    this.isKitchenSink = url.searchParams.has('kitchen-sink');
  }

  firstUpdated() {
    if (
      this.steps.length === 0 &&
      window.setupWizardShare &&
      window.setupWizardShare.steps &&
      window.setupWizardShare.steps.length !== 0
    ) {
      this.steps = window.setupWizardShare.steps;
    }
  }

  render() {
    return html`
      <div class="wrap">
        <div class="repel">
          <button class="btn-outline ms-auto" @click=${this.exit}>
            ${this.translations.exit}
          </button>
          <h2 class="me-auto">${this.translations.title}</h2>
        </div>
        <div class="wizard">
          ${this.isKitchenSink
            ? this.kitchenSink()
            : html` ${this.renderStep()} `}
        </div>
      </div>
    `;
  }

  back() {
    this.gotoStep(this.currentStepNumber - 1);
  }
  next() {
    this.gotoStep(this.currentStepNumber + 1);
  }
  gotoStep(i) {
    if (i < 0) {
      this.currentStepNumber = 0;
      return;
    }
    if (i > this.steps.length - 1) {
      this.currentStepNumber = this.steps.length - 1;
      return;
    }
    this.currentStepNumber = i;
  }
  exit() {
    /* TODO: Set option dt_setup_wizard_seen */
    location.href = this.adminUrl;
  }

  renderStep() {
    if (this.steps.length === 0) {
      return;
    }
    const step = this.steps[this.currentStepNumber];
    const { component } = step;

    return staticHtml`
            <${unsafeStatic(component)}
                .step=${step}
                ?firstStep=${this.currentStepNumber === 0}
                @back=${this.back}
                @next=${this.next}
            ></${unsafeStatic(component)}>
        `;
  }

  renderMultiSelect(component) {
    return html`
      <div class="multiSelect">
        ${component.description ? html` <p>${component.description}</p> ` : ''}
        <div class="grid" size="small">
          ${component.options && component.options.length > 0
            ? component.options.map(
                (option) => html`
                  <label class="toggle" for="${option.key}">
                    <input
                      ?checked=${option.checked}
                      type="checkbox"
                      name="${option.key}"
                      id="${option.key}"
                    />
                    <span>${option.name}</span>
                  </label>
                `,
              )
            : ''}
        </div>
      </div>
    `;
  }
  renderModuleDecision(component) {
    return html`
      <div class="decisions">
        ${component.description ? html` <p>${component.description}</p> ` : ''}
        <div class="grid">
          ${component.options && component.options.length > 0
            ? component.options.map(
                (option) => html`
                  <button class="btn-card" data-key=${option.key}>
                    <h3 class="white">${option.name}</h3>
                    <p>${option.description ?? ''}</p>
                  </button>
                `,
              )
            : ''}
        </div>
      </div>
    `;
  }
  renderFields(component) {
    return html`
      <div class="options">
        ${component.description ? html` <p>${component.description}</p> ` : ''}
        <div class="flow">
          ${component.options && component.options.length > 0
            ? component.options.map(
                (option) => html`
                  <div class="input-group">
                    <label for="${option.key}">${option.name}</label>
                    <input
                      placeholder="${option.value}"
                      type="text"
                      name="${option.key}"
                      id="${option.key}"
                    />
                  </div>
                `,
              )
            : ''}
        </div>
      </div>
    `;
  }

  kitchenSink() {
    return html`
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
          <input placeholder="foo" type="text" name="foo" id="foo" />
        </div>
        <div class="input-group">
          <label for="bar">bar</label>
          <input placeholder="bar" type="text" name="bar" id="bar" />
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
            <input type="checkbox" />
            <div>Name</div>
          </label>
          <label class="toggle">
            <input type="checkbox" />
            <div>Gender</div>
          </label>
          <label class="toggle">
            <input type="checkbox" />
            <div>Email</div>
          </label>
          <label class="toggle">
            <input type="checkbox" />
            <div>Location</div>
          </label>
          <label class="toggle">
            <input type="checkbox" />
            <div>Phone</div>
          </label>
        </div>
        <h3>Stepper</h3>
        <div class="flow | stepper">
          ${this.renderStep()}
          <div class="cluster">
            <button @click=${this.back}>Back</button>
            <button @click=${this.next} class="btn-primary">Next</button>
          </div>
        </div>
      </div>
    `;
  }
}
customElements.define('setup-wizard', SetupWizard);
