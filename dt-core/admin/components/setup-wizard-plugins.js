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
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  next() {
    this.dispatchEvent(new CustomEvent('next'));
  }

  render() {
    return html`
      <div class="cover">
        <div class="content">
          <div
            class="plugin grid"
            style="grid-template-columns: 1fr 1fr 1fr 1fr"
          >
            ${this.plugins.map((plugin) => {
              return html`
                <div
                  class="btn-card flow ${plugin.active || plugin.selected
                    ? 'selected'
                    : ''}"
                  @click=${() => {
                    plugin.selected = !plugin.selected;
                    this.requestUpdate();
                  }}
                >
                  <div style="display: inline">${plugin.name}</div>
                  <div>
                    ${plugin.installed && !plugin.active
                      ? html`<div
                          class="tag"
                          style="background-color:var(--default-dark)"
                        >
                          Installed
                        </div>`
                      : ''}
                    ${plugin.active
                      ? html`<div
                          class="tag"
                          style="background-color: var(--secondary-color);"
                        >
                          Active
                        </div>`
                      : ''}
                  </div>
                  <div title="${plugin.description}">
                    ${plugin.description.length > 100
                      ? plugin.description.substring(0, 100) + '...'
                      : plugin.description}
                  </div>
                </div>
              `;
            })}
          </div>
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
