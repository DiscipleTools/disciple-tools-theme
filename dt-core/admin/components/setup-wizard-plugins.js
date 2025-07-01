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
        //only install plugins if the user has permissions to.
        plugin.selected =
          plugin.installed || window.setupWizardShare.can_install_plugins;
      }
    });
  }

  back() {
    if (!this.loading) {
      this.dispatchEvent(new CustomEvent('back'));
    }
  }
  skip() {
    if (!this.loading) {
      this.dispatchEvent(new CustomEvent('next'));
    }
  }
  async next() {
    const plugins_to_install = this.getPluginsToInstall();
    if (this.canNavigate()) {
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
    if (this.canNavigate()) {
      return 'Next';
    }
    return 'Confirm';
  }
  canNavigate() {
    return this.finished || this.getPluginsToInstall().length === 0;
  }
  setToastMessage(message) {
    this.toastMessage = message;
  }
  dismissToast() {
    this.toastMessage = '';
  }
  getPluginsToInstall() {
    return this.plugins.filter((plugin) => plugin.selected && !plugin.active);
  }
  togglePlugin(plugin, disabled) {
    plugin.selected = !plugin.selected && !disabled;
    this.finished = false;
    this.dismissToast();
    this.requestUpdate();
  }

  render() {
    return html`
      <div class="step-layout">
        <img
          class="blue-svg step-icon"
          src="${window.setupWizardShare.admin_image_url + 'plugin.svg'}"
        />
        <h2>Recommended Plugins</h2>
        <div class="content stack">
          <div class="centered-view">
            <p>
              Plugins are optional and add additional functionality to
              Disciple.Tools based on your needs.
            </p>
            <p>
              Plugins can be activated or deactivated at any time. You can find
              the full list of Disciple.Tools plugin in the "Extensions (D.T)"
              tab later.
            </p>
          </div>
          <table style="margin-right:1rem">
            <thead>
              <tr>
                <th>Plugin Name</th>
                <th>
                  Install/Activate <br />
                  <span class="table-control" @click=${() => this.select_all()}>
                    (select all)
                  </span>
                </th>
                <th style="width: 60%">Description</th>
              </tr>
            </thead>
            <tbody>
              ${this.plugins.map((plugin) => {
                const disabled =
                  !window.setupWizardShare.can_install_plugins &&
                  !plugin.installed;
                let action = html`<img
                    style="height: 20px; filter: grayscale(1);"
                    src="${window.setupWizardShare.image_url + 'verified.svg'}"
                  /><br />
                  <span style="color:grey">Active</span>`;
                if (plugin.installing) {
                  action = html`<span class="spinner light"></span>`;
                } else if (!plugin.active) {
                  action = html`<div class="circle-div"></div>`;
                  action = html`<input
                      type="checkbox"
                      .checked=${plugin.selected}
                      .disabled=${disabled}
                    />${disabled ? '*' : ''}`;
                }

                return html`
                  <tr @click=${() => this.togglePlugin(plugin, disabled)}>
                    <td><strong>${plugin.name}</strong></td>
                    <td style="text-align: center;">${action}</td>
                    <td
                      style="max-width: 50%"
                      @click=${(e) => e.stopImmediatePropagation()}
                    >
                      ${plugin.description}
                      <a href=${plugin.permalink} target="_blank">
                        More Info
                      </a>
                    </td>
                  </tr>
                `;
              })}
            </tbody>
          </table>
          ${!window.setupWizardShare.can_install_plugins
            ? html`<p>
                <strong>*</strong>Only your server administrator can install
                plugins.
              </p>`
            : ''}
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
              <div class="toast-message">${this.toastMessage}</div>
            </div>
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
