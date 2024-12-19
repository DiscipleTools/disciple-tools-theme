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
      availableModules: { type: Array, attribute: false },
      selectedModules: { type: Object, attribute: false },
      loading: { Boolean, attribute: false },
      finished: { Boolean, attribute: false },
    };
  }

  constructor() {
    super();
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
    }
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  nextLabel() {
    return this.translations.next;
  }
  toggleModule(key) {
    const checkbox = this.renderRoot.querySelector(`#${key}`);
    if (this.selectedModules[key]) {
      checkbox.checked = false;
      this.selectedModules[key] = false;
    } else {
      checkbox.checked = true;
      this.selectedModules[key] = true;
    }
  }
  async submitModuleChanges() {
    this.loading = true;
    this.requestUpdate();
    await window.dt_admin_shared.modules_update(this.selectedModules);
    this.loading = false;
  }

  render() {
    return html`
      <div class="cover">
        <div class="content flow">
          <h2>Part 1: Module selection</h2>
          ${this.stage === 'work'
            ? html`
                <p>
                  The recommended modules for your chosen use case(s) are
                  selected below.
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
                                        ?checked=${this.selectedModules[
                                          module.key
                                        ]}
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
                ${this.finished
                  ? html`
                      <section class="card success">
                        <p>
                          <strong
                            >The modules you have chosen have been turned
                            on.</strong
                          >
                        </p>
                        <p>
                          You can enable and disable these modules to your
                          liking in the 'Settings (D.T)' section of the
                          Wordpress admin.
                        </p>
                      </section>
                    `
                  : ''}
              `
            : ''}
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
