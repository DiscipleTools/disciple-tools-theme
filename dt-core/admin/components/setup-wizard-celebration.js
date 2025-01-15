import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardCelebration extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
    };
  }

  constructor() {
    super();
    this.translations = window.setupWizardShare.translations;
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    await window.dt_admin_shared.update_dt_options({
      dt_setup_wizard_completed: true,
    });
    window.location.href = window.dt_admin_scripts.site_url;
  }

  render() {
    return html`
      <div class="step-layout">
        <h2>All finished!</h2>
        <div class="content stack">
          <div class="centered-view">
            <div style="text-align: center">
              <img
                src="${window.setupWizardShare.image_url + 'verified.svg'}"
                style="width: 80px; height: 80px;"
                class="green-svg"
              />
            </div>
            <p>
              You can now start using Disciple.Tools! Read more about using and
              customizing Disciple.Tools in the documentation.
            </p>
            <div style="text-align: center">
              <img
                class="blue-svg"
                style="width: 50px; height: 50px;"
                src="${window.setupWizardShare.admin_image_url + 'documentation.svg'}"
              />
              <br />
              <a
                href="https://disciple.tools/docs"
                target="_blank"
                class="button btn-primary btn-with-icon"
              >
                Documentation
                <img
                  class="white-svg"
                  src="${window.setupWizardShare.image_url + 'open-link.svg'}"
                />
              </a>
              </div>

              <p>
                For any questions or further assistance in getting
                Disciple.Tools to work for you, please reach out on our
                community forum or our discord channel.
              </p>
              <div
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem"
              >
                <div style="text-align: center">
                  <img
                    class="blue-svg"
                    style="width: 50px; height: 50px;"
                    src="${
                      window.setupWizardShare.admin_image_url + 'community.svg'
                    }"
                  /><br />
                  <a
                    href="https://community.disciple.tools/"
                    target="_blank"
                    class="button btn-primary btn-with-icon"
                  >
                    Community Forum
                    <img
                      class="white-svg"
                      src="${
                        window.setupWizardShare.image_url + 'open-link.svg'
                      }"
                    />
                  </a>
                </div>
                <div style="text-align: center">
                  <img
                    class="blue-svg"
                    style="width: 50px; height: 50px;"
                    src="${
                      window.setupWizardShare.admin_image_url + 'discord.svg'
                    }"
                  /><br />
                  <a
                    href="https://discord.gg/kp5pYmrhSd"
                    target="_blank"
                    class="button btn-primary btn-with-icon"
                  >
                    Discord Invitation
                    <img
                      class="white-svg"
                      src="${
                        window.setupWizardShare.image_url + 'open-link.svg'
                      }"
                    />
                  </a>
                </div>
              </div>
            </div>
          </div>
          <setup-wizard-controls
            hideSkip
            nextLabel=${this.translations.finish}
            @next=${this.next}
            @back=${this.back}
            nextLabel="Close Wizard"
          ></setup-wizard-controls>
        </div>
      </div>
    `;
  }
}
customElements.define('setup-wizard-celebration', SetupWizardCelebration);
