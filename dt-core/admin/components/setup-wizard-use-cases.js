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
          <div class="centered-view">
            <p>
              Choose from the following use cases that best fit your needs to
              activate specific features in Disciple.Tools.
            </p>
            <p>
              You'll have the change to fine-tune these choices in the next
              steps.
            </p>
            <p>
              <strong>(Select one or more)</strong>
            </p>
          </div>
          <div class="stack" style="margin-right:1rem;">
            ${repeat(
              this.useCases || [],
              (option) => option.key,
              (option) => html`
                <div
                  class="option-button"
                  @click="${() => this.toggleOption(option.key)}"
                  ?selected="${this.options[option.key]}"
                  style="
                          display: grid;
                          grid-template-columns: 70px 5fr 1fr;
                        "
                >
                  <div class="option-button-checkmark">
                    ${this.options[option.key]
                      ? html`
                          <img
                            src="${window.setupWizardShare.image_url +
                            'verified.svg'}"
                          />
                        `
                      : html`<div class="circle-div"></div>`}
                  </div>
                  <div>
                    <strong class="text-blue">${option.name}</strong><br />
                    ${option.description ?? ''}
                  </div>
                  <div class="center-all">
                    <img
                      class="option-button-image"
                      src="${window.setupWizardShare.image_url + 'group.svg'}"
                    />
                  </div>
                </div>
              `,
            )}
          </div>
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
