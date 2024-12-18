import {
  html,
  css,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardPlugins extends OpenLitElement {
  static styles = [
    css`
      :host {
        display: block;
      }
    `,
  ];

  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
    };
  }
  constructor() {
    super();
    this.plugins = window.setupWizardShare.data.plugins;
    //@todo get list of recommended plugins
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    const plugins_to_install = this.plugins.filter((plugin) => plugin.selected);

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
    this.requestUpdate();

    this.dispatchEvent(new CustomEvent('next'));
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

  render() {
    return html`
      <div class="cover">
        <div class="content">
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
                <th>Info</th>
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
                    <td>
                      <a href=${plugin.permalink} target="_blank">
                        Plugin Link
                      </a>
                    </td>
                  </tr>
                `;
              })}
            </tbody>
          </table>
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          nextLabel="install and activate selected plugins"
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-plugins', SetupWizardPlugins);
