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
      toastMessage: { type: String, attribute: false },
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
    this.toastMessage = '';
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
        this.dispatchEvent(new CustomEvent('back'));
        break;
    }
  }
  next() {
    switch (this.stage) {
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
    this.dismissToast();
    this.stage = 'work';
    this.requestUpdate();
  }
  selectedOptions() {
    return Object.keys(this.options).filter((option) => this.options[option]);
  }
  saveOptions() {
    for (const option in this.options) {
      window.setupWizardShare.data.use_cases[option].selected =
        this.options[option];
    }
    if (this.selectedOptions().length) {
      this.setToastMessage('Use cases selected');
    } else {
      this.setToastMessage('No use cases selected');
    }
  }
  setToastMessage(message) {
    this.toastMessage = message;
  }
  dismissToast() {
    this.toastMessage = '';
  }

  render() {
    return html`
      <div class="step-layout">
        <h2>Use Cases</h2>
        <div class="content stack">
          ${this.useCases
            ? html`
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
          <section
            class="ms-auto card success toast"
            data-state=${this.toastMessage.length ? '' : 'empty'}
          >
            <button class="close-btn btn-outline" @click=${this.dismissToast}>
              x
            </button>

            <p><strong>${this.toastMessage}</strong></p>
            <p>
              ${this.selectedOptions().length
                ? 'Based on the use case(s) you have now chosen, we can recommend some modules and plugins that we think will be helpful.'
                : 'In the next steps, simply choose what options seems best'}
            </p>
          </section>
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
