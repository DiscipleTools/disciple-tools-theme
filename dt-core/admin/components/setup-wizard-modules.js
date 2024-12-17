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
      stage: { type: String, attribute: false },
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

    this.selectedModules = Object.entries(this.data.use_cases).reduce(
      (selectedModules, [key, useCase]) => {
        if (useCase.selected) {
          useCase.recommended_modules.forEach((moduleKey) => {
            selectedModules.push(moduleKey);
          });
        }
        return selectedModules;
      },
      [],
    );
  }

  firstUpdated() {
    /* Reduce the keys down to ones that exist in the details list of use cases */
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
        /* TODO: fire off to the API here */
        this.stage = 'follow-up';
        break;
      case 'follow-up':
        this.dispatchEvent(new CustomEvent('next'));
        break;
    }
  }
  nextLabel() {
    return this.translations.next;
  }
  toggleModule(key) {
    const checkbox = this.renderRoot.querySelector(`#${key}`);
    if (this.selectedModules.includes(key)) {
      checkbox.checked = false;
      const index = this.selectedModules.findIndex((module) => module === key);
      this.selectedModules = [
        ...this.selectedModules.slice(0, index),
        ...this.selectedModules.slice(index + 1),
      ];
    } else {
      checkbox.checked = true;
      this.selectedModules = [...this.selectedModules, key];
    }
  }

  render() {
    return html`
      <div class="cover">
        <div class="content flow">
          ${this.stage === 'work'
            ? html`
                <h2>Module selection</h2>
                <p>
                  The recommended modules for your chosen use case(s) are
                  selected below
                </p>
                <p>
                  Feel free to change this selection according to what you need
                  D.T to do.
                </p>
                <section>
                  <table>
                    <thead>
                      <tr>
                        <th></th>
                        <th>Module</th>
                        <th>Description</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${Object.keys(this.availableModules).length > 0
                        ? html`
                            ${repeat(
                              this.availableModules,
                              (module) => module.key,
                              (module) => {
                                return html`
                                  <tr
                                    key=${module.key}
                                    @click=${() =>
                                      this.toggleModule(module.key)}
                                  >
                                    <td>
                                      <input
                                        type="checkbox"
                                        id=${module.key}
                                        ?checked=${this.selectedModules.includes(
                                          module.key,
                                        )}
                                      />
                                    </td>
                                    <td>${module.name}</td>
                                    <td>${module.description}</td>
                                  </tr>
                                `;
                              },
                            )}
                          `
                        : ''}
                    </tbody>
                  </table>
                </section>
              `
            : ''}
          ${this.stage === 'follow-up'
            ? html`
                <h2>The modules you have chosen have been turned on.</h2>
                <p>
                  You can adjust these modules to your liking in the 'Settings
                  (DT)' section of the Wordpress admin.
                </p>
              `
            : ''}
        </div>
        <setup-wizard-controls
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
