import {
  html,
  repeat,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardUseCases extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      stage: { type: String, attribute: false },
      useCases: { type: Array, attribute: false },
      options: { type: Object, attribute: false },
      availableModules: { type: Array, attribute: false },
      selectedModules: { type: Array, attribute: false },
    };
  }

  constructor() {
    super();
    this.stage = 'work';
    this.data = window.setupWizardShare.data;
    this.translations = window.setupWizardShare.translations;
    this.availableModules = [];
    this.selectedModules = [];
    this.options = Object.entries(this.data.use_cases).reduce(
      (options, [key, useCase]) => {
        const selected =
          (useCase.selected && useCase.selected === true) || false;
        return {
          ...options,
          [key]: selected,
        };
      },
      {},
    );
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
  }

  back() {
    switch (this.stage) {
      case 'follow-up':
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
        this.saveOptions();
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
      default:
        return this.translations.next;
    }
  }
  toggleOption(event) {
    const option = event.target.id;
    if (this.options[option]) {
      this.options[option] = false;
    } else {
      this.options[option] = true;
    }
  }
  saveOptions() {
    for (const option in this.options) {
      window.setupWizardShare.data.use_cases[option].selected =
        this.options[option];
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
                            <label class="toggle" for="${option.key}">
                              <input
                                ?checked=${option.selected}
                                type="checkbox"
                                name="${option.key}"
                                id="${option.key}"
                                @change=${this.toggleOption}
                              />
                              <div>
                                <h3>${option.name}</h3>
                                <p>${option.description ?? ''}</p>
                              </div>
                            </label>
                          `,
                        )
                      : ''}
                  </div>
                </div>
              `
            : ''}
          ${this.stage === 'follow-up'
            ? html`
                <h2>Use cases selected</h2>
                <p>
                  Now that you have chosen your use cases, we can recommend some
                  modules and plugins that will be helpful for these use cases.
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
customElements.define('setup-wizard-use-cases', SetupWizardUseCases);
