import {
  html,
  repeat,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardModules extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      stage: { type: String, attribute: false },
      toastMessage: { type: String, attribute: false },
      availableModules: { type: Array, attribute: false },
      selectedModules: { type: Object, attribute: false },
      loading: { Boolean, attribute: false },
      finished: { Boolean, attribute: false },
    };
  }

  constructor() {
    super();
    this.toastMessage = '';
    this.stage = 'work';
    this.data = window.setupWizardShare.data;
    this.translations = window.setupWizardShare.translations;
    this.loading = false;
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
    this.selectedModules = this.availableModules.reduce((modules, module) => {
      modules[module.key] = false;
      return modules;
    }, {});

    Object.entries(this.data.use_cases).forEach(([key, useCase]) => {
      if (useCase.selected) {
        useCase.recommended_modules.forEach((moduleKey) => {
          this.selectedModules[moduleKey] = true;
        });
      }
    });
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    if (this.finished) {
      this.dispatchEvent(new CustomEvent('next'));
    } else {
      await this.submitModuleChanges();
      this.finished = true;
      this.setToastMessage('The modules you have chosen have been turned on.');
    }
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  nextLabel() {
    if (this.finished) {
      return this.translations.next;
    }
    return this.translations.confirm;
  }
  setToastMessage(message) {
    this.toastMessage = message;
  }
  dismissToast() {
    this.toastMessage = '';
  }
  toggleModule(key) {
    this.selectedModules[key] = !this.selectedModules[key];
    this.finished = false;
    this.dismissToast();
    this.requestUpdate();
  }
  async submitModuleChanges() {
    this.loading = true;
    this.requestUpdate();
    await window.dt_admin_shared.modules_update(this.selectedModules);

    this.dispatchEvent(
      new CustomEvent('enableSteps', {
        detail: { people_groups: this.selectedModules.people_groups_module },
      }),
    );

    window.setupWizardShare.enabledModules = this.selectedModules;
    this.loading = false;
  }

  render() {
    return html`
      <div class="step-layout">
        <h2>Module selection</h2>
        <div class="content stack">
          <div class="centered-view">
            <p>
              The recommended modules for your chosen use case(s) are selected
              below.
            </p>
            <p>
              Feel free to change this selection according to what you need
              Disciple.Tools to do.
            </p>
          </div>
          <section
            style="margin-right: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;"
          >
            ${repeat(
              this.availableModules || [],
              (module) => module.key,
              (module) => {
                return html`
                  <div
                    class="option-button"
                    key=${module.key}
                    @click=${() => this.toggleModule(module.key)}
                    ?selected=${this.selectedModules[module.key]}
                    style="
                    display: grid;
                    grid-template-columns: 70px 5fr"
                  >
                    <div class="option-button-checkmark">
                      ${this.selectedModules[module.key]
                        ? html`
                            <img
                              src="${window.setupWizardShare.image_url +
                              'verified.svg'}"
                            />
                          `
                        : html` <div class="circle-div"></div> `}
                    </div>
                    <div>
                      <strong class="text-blue">${module.name}</strong><br />
                      ${module.description ?? ''}
                    </div>
                  </div>
                `;
              },
            )}
          </section>
          <section
            class="ms-auto card success toast"
            data-state=${this.toastMessage.length ? '' : 'empty'}
          >
            <button class="close-btn btn-outline" @click=${this.dismissToast}>
              x
            </button>
            <div class="toast-layout">
              <div class="center-all">
                <img
                  src="${window.setupWizardShare.admin_image_url +
                  'check-circle.svg'}"
                />
              </div>
              <div class="toast-message">
                <p><strong>${this.toastMessage}</strong></p>
                <p>
                  You can enable and disable these modules to your liking in the
                  "Settings (D.T)" section of the Wordpress admin.
                </p>
              </div>
            </div>
          </section>
        </div>
        <setup-wizard-controls
          nextLabel=${this.nextLabel()}
          backLabel=${this.translations.back}
          skipLabel=${this.translations.skip}
          ?saving=${this.loading}
          @next=${this.next}
          @back=${this.back}
          @skip=${this.skip}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-modules', SetupWizardModules);
