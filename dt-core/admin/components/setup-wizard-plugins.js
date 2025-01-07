import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardPlugins extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      toastMessage: { type: String, attribute: false },
      loading: { type: Boolean, attribute: false },
      finished: { type: Boolean, attribute: false },
    };
  }
  constructor() {
    super();
    this.toastMessage = '';
    this.loading = false;
    this.finished = false;
    this.plugins = window.setupWizardShare.data.plugins;
    let recommended_plugins = [];
    Object.keys(window.setupWizardShare.data.use_cases || {}).forEach(
      (use_case) => {
        if (window.setupWizardShare.data.use_cases[use_case].selected) {
          recommended_plugins = recommended_plugins.concat(
            window.setupWizardShare.data.use_cases[use_case]
              .recommended_plugins,
          );
        }
      },
    );
    //pre select recommended plugins
    this.plugins.forEach((plugin) => {
      if (recommended_plugins.includes(plugin.slug)) {
        plugin.selected = true;
      }
    });
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  async next() {
    const plugins_to_install = this.getPluginsToInstall();
    if (this.finished || plugins_to_install.length === 0) {
      this.dispatchEvent(new CustomEvent('next'));
      return;
    }
    this.loading = true;

    for (let plugin of plugins_to_install) {
      if (!plugin.installed) {
        plugin.installing = true;
        this.requestUpdate();
        await window.dt_admin_shared.plugin_install(plugin.download_url);
        await window.dt_admin_shared.plugin_activate(plugin.slug);
        plugin.installing = false;
        plugin.installed = true;
        // plugin.active = true;
      }
      if (plugin.selected && !plugin.active) {
        plugin.installing = true;
        this.requestUpdate();
        await window.dt_admin_shared.plugin_activate(plugin.slug);
        plugin.installing = false;
        plugin.active = true;
      }
    }
    this.loading = false;
    this.finished = true;
    plugins_to_install.length &&
      this.setToastMessage('Finished installing and activating plugins');
    this.requestUpdate();
  }

  select_all() {
    let already_all_selected =
      this.plugins.filter((plugin) => plugin.selected).length ===
      this.plugins.length;
    this.plugins.forEach((plugin) => {
      plugin.selected = !already_all_selected;
    });
    this.requestUpdate();
  }
  nextLabel() {
    const plugins_to_install = this.getPluginsToInstall();
    if (this.finished || plugins_to_install.length === 0) {
      return 'Next';
    }
    return 'Confirm';
  }
  setToastMessage(message) {
    this.toastMessage = message;
    setTimeout(() => {
      this.toastMessage = '';
    }, 3000);
  }
  getPluginsToInstall() {
    const plugins_to_install = this.plugins.filter((plugin) => plugin.selected);
    return plugins_to_install;
  }

  render() {
    return html`
      <div class="cover">
        <h2>Part 2: Recommended Plugins</h2>
        <div class="content flow">
          <p>
            Plugins are optional and add additional functionality
            to Disciple.Tools based on your needs.
          </p>
          <p>
            Plugins can be activated or deactivated at any time. You can find the full list of
            Disciple.Tools plugin in the "Extensions (D.T)" tab later.
          </p>
          </p>
          <table>
            <thead>
              <tr>
                <th>Plugin Name</th>
                <th>
                  Install/Activate <br />
                  <span
                    style="color: blue;cursor: pointer"
                    @click=${() => this.select_all()}
                  >
                    select all
                  </span>
                </th>
                <th style="width: 60%">Description</th>
                <th>More Info</th>
              </tr>
            </thead>
            <tbody>
              ${this.plugins.map((plugin) => {
                let action = 'Active';
                if (plugin.installing) {
                  action = html`<span class="spinner"></span>`;
                } else if (!plugin.active) {
                  action = html`<input
                    type="checkbox"
                    .checked=${plugin.selected}
                  />`;
                }
                if (
                  !window.setupWizardShare.can_install_plugins &&
                  !plugin.installed
                ) {
                  action = 'Not Available*';
                }

                return html`
                  <tr
                    @click=${() => {
                      plugin.selected = !plugin.selected;
                      this.requestUpdate();
                    }}
                  >
                    <td>${plugin.name}</td>
                    <td>${action}</td>
                    <td style="max-width: 50%">${plugin.description}</td>
                    <td @click=${(e) => e.stopImmediatePropagation()}>
                      <a href=${plugin.permalink} target="_blank">
                        Plugin Link
                      </a>
                    </td>
                  </tr>
                `;
              })}
            </tbody>
          </table>
          ${
            !window.setupWizardShare.can_install_plugins
              ? html`<p>
                  <strong>${this.toastMessage}</strong>
                  <strong>*Note:</strong> Only your server administrator can
                  install plugins.
                </p>`
              : ''
          }
        <section
          class="ms-auto card success toast"
          data-state=${this.toastMessage.length ? '' : 'empty'}
        >
          ${this.toastMessage}
        </section>
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          nextLabel=${this.nextLabel()}
          ?saving=${this.loading}
          @next=${this.next}
          @back=${this.back}
          @skip=${this.skip}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-plugins', SetupWizardPlugins);
