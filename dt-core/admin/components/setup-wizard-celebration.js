import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardCelebration extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
    };
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  next() {
    window.location.href = window.setupWizardShare.admin_url;
  }

  render() {
    return html`
      <div class="cover">
        <h2>All finished</h2>
        <div class="content flow">
          <p>
            You can change all of these choices in the Settings (D.T.) tab in
            the WP admin.
          </p>
          <p>
            D.T. has a huge ability to be customized to serve your needs. If you
            have any questions or need any further assistance in getting D.T. to
            work for you, please reach out on our community forum or our discord
            channel.
          </p>
          <a href="https://community.disciple.tools/" target="_blank">
            Community Forum
          </a>
          <a href="https://discord.gg/vrrcXYwwTU" target="_blank">
            Discord Invitation
          </a>
        </div>
        <setup-wizard-controls
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-celebration', SetupWizardCelebration);
