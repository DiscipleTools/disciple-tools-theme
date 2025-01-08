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
    window.location.href = window.setupWizardShare.admin_url;
  }

  render() {
    return html`
      <div class="step-layout">
        <h2>All finished</h2>
        <div class="content stack">
          <p>
            After closing this setup wizard, you'll find yourself in the
            WordPress admin dashboard. From there, you can explore these
            settings and continue customizing your Disciple.Tools site.
          </p>
          <p>
            Disciple.Tools has a huge ability to be customized to serve your
            needs. If you have any questions or need any further assistance in
            getting Disciple.Tools. to work for you, please reach out on our
            community forum or our discord channel.
          </p>
          <a href="https://community.disciple.tools/" target="_blank">
            Community Forum
          </a>
          <a href="https://discord.gg/kp5pYmrhSd" target="_blank">
            Discord Invitation
          </a>
        </div>
        <setup-wizard-controls
          hideSkip
          nextLabel=${this.translations.finish}
          @next=${this.next}
          @back=${this.back}
          nextLabel="Close Wizard"
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-celebration', SetupWizardCelebration);
