import {
  html,
  css,
  repeat,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardModules extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      stage: { type: String, attribute: false },
      useCases: { type: Array, attribute: false },
      option: { type: Object, attribute: false },
      availableModules: { type: Array, attribute: false },
      selectedModules: { type: Array, attribute: false },
    };
  }

  constructor() {
    super();
    this.stage = 'work';
    this.data = window.setupWizardShare.data;
    this.translations = window.setupWizardShare.translations;
    this.option = {};
    this.availableModules = [];
    this.selectedModules = [];
  }

  firstUpdated() {
    /* Reduce the keys down to ones that exist in the details list of use cases */
    const useCaseKeys = this.step.config.reduce((keys, key) => {
      if (this.data.use_cases[key]) {
        return [...keys, key];
      }
      return keys;
    }, []);
    this.useCases = useCaseKeys.map(
      (useCaseKey) => this.data.use_cases[useCaseKey],
    );
    this.availableModules = Object.entries(this.data.modules)
      .map(([key, module]) => {
        return {
          key,
          ...module,
        };
      })
      .reduce((modules, module) => {
        if (module.locked) {
          return modules;
        }
        modules.push(module);
        return modules;
      }, []);
  }

  back() {
    switch (this.stage) {
      case 'follow-up':
      /* Fallthrough */
      case 'confirm':
        this.stage = 'work';
        break;
      case 'work':
        this.stage = 'prompt';
        break;
      case 'prompt':
        this.dispatchEvent(new CustomEvent('back'));
        break;
    }
  }
  next() {
    switch (this.stage) {
      case 'prompt':
        this.stage = 'work';
        break;
      case 'work':
        this.stage = 'confirm';
        break;
      case 'confirm':
        /* TODO: fire off to the API here */
        this.stage = 'follow-up';
        break;
      case 'follow-up':
        this.dispatchEvent(new CustomEvent('next'));
        break;
    }
  }
  nextLabel() {
    switch (this.stage) {
      case 'work':
        return this.translations.confirm;
      case 'confirm':
        return this.translations.submit;
      default:
        return this.translations.next;
    }
  }
  selectOption(option) {
    this.option = option;
    this.selectedModules = option.recommended_modules;
    // Checkboxes use their own internal state when clicked and not the html attribute
    // So we need to manually set them all when programmatically selecting them
    const checkBoxes = this.renderRoot.querySelectorAll(
      'input[type="checkbox"]',
    );
    checkBoxes.forEach((checkbox) => {
      checkbox.checked = this.selectedModules.includes(
        checkbox.getAttribute('key'),
      );
    });
  }
  toggleModule(key) {
    if (this.selectedModules.includes(key)) {
      const index = this.selectedModules.findIndex((module) => module === key);
      this.selectedModules = [
        ...this.selectedModules.slice(0, index),
        ...this.selectedModules.slice(index + 1),
      ];
    } else {
      this.selectedModules = [...this.selectedModules, key];
    }
  }

  render() {
    return html`
      <div class="cover">
        <div class="content flow">
          ${this.stage === 'prompt'
            ? html`
                <h2>Time to customize what fields are available.</h2>
                <p>
                  In the next step you will be able to choose between some
                  common use cases of Disciple.Tools
                </p>
                <p>
                  You will still be able to customize to your particular use
                  case.
                </p>
              `
            : ''}
          ${this.stage === 'work'
            ? html`
                <h2>Choose a use case</h2>
                <p>
                  Choose one of these use cases to tailor what parts of
                  Disciple.Tools to turn on.
                </p>
                <p>
                  You can fine tune those choices further to your own needs.
                </p>
                <div class="decisions">
                  <div class="grid">
                    ${this.useCases && this.useCases.length > 0
                      ? this.useCases.map(
                          (option) => html`
                            <button
                              class="btn-card ${this.option.key === option.key
                                ? 'selected'
                                : ''}"
                              data-key=${option.key}
                              @click=${() => this.selectOption(option)}
                            >
                              <h3 class="white">${option.name}</h3>
                              <p>${option.description ?? ''}</p>
                            </button>
                          `,
                        )
                      : ''}
                  </div>
                </div>
                <section>
                  <h2>Available Modules</h2>
                  <div class="modules">
                    ${Object.keys(this.availableModules).length > 0
                      ? html`
                          <div class="flow" size="small">
                            ${repeat(
                              this.availableModules,
                              (module) => module.key,
                              (module) => {
                                return html`
                                  <label class="toggle" key=${module.key}>
                                    <input
                                      type="checkbox"
                                      key=${module.key}
                                      ?checked=${this.selectedModules.includes(
                                        module.key,
                                      ) || module.enabled}
                                      @input=${() =>
                                        this.toggleModule(module.key)}
                                    />
                                    <div class="flow">
                                      <h3>${module.name}</h3>
                                      <p>${module.description}</p>
                                    </div>
                                  </label>
                                `;
                              },
                            )}
                          </div>
                        `
                      : ''}
                  </div>
                </section>
              `
            : ''}
          ${this.stage === 'confirm'
            ? html`
                <h2>Confirm your selection</h2>
                <h3>These modules will be turned on</h3>
                <ul></ul>
                <h3>These modules will be turned off</h3>
                <ul></ul>
              `
            : ''}
          ${this.stage === 'follow-up'
            ? html`
                <h2>Your choices have been implemented</h2>
                <p>
                  You can make further changes to the way D.T. works in the
                  'Settings (DT)' section of the Wordpress admin.
                </p>
              `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep && this.stage === 'prompt'}
          nextLabel=${this.nextLabel()}
          backLabel=${this.translations.back}
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-modules', SetupWizardModules);
