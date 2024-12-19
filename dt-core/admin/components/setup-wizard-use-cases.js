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
    this.stage = 'prompt';
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
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  nextLabel() {
    switch (this.stage) {
      case 'work':
        return this.translations.confirm;
      default:
        return this.translations.next;
    }
  }
  toggleOption(option) {
    if (this.options[option]) {
      this.options[option] = false;
    } else {
      this.options[option] = true;
    }
    this.requestUpdate();
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
                <h2>Part 1: Use Cases</h2>
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
                <h2>Part 1: Use Cases</h2>
                <p>
                  Choose one or more of these use cases to tailor what parts of
                  Disciple.Tools to turn on.
                </p>
                <p>
                  You can fine tune those choices further to your own needs in
                  the following steps.
                </p>
                <div>
                  <table>
                    <thead>
                      <tr>
                        <th></th>
                        <th>Use Case</th>
                        <th style="width: 600px;">Description</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${repeat(
                        this.useCases,
                        (option) => option.key,
                        (option) => html`
                          <tr @click="${() => this.toggleOption(option.key)}">
                            <td>
                              <input
                                .checked=${this.options[option.key]}
                                type="checkbox"
                                name="${option.key}"
                                id="${option.key}"
                              />
                            </td>
                            <td>${option.name}</td>
                            <td>${option.description ?? ''}</td>
                          </tr>
                        `,
                      )}
                    </tbody>
                  </table>
                </div>
              `
            : ''}
          ${this.stage === 'follow-up'
            ? html`
                <h2>Part 1: Use Cases</h2>
                <p><strong>Use cases selected.</strong></p>
                <p>
                  Based on the use cases you have now chosen, we can recommend
                  some modules and plugins that will think will be helpful.
                </p>
              `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep && this.stage === 'prompt'}
          nextLabel=${this.nextLabel()}
          backLabel=${this.translations.back}
          skipLabel=${this.translations.skip}
          @next=${this.next}
          @back=${this.back}
          @skip=${this.skip}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-use-cases', SetupWizardUseCases);
