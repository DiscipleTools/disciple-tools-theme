import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardIntro extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
    };
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
        <h2>Setting up D.T. for you</h2>
        <div class="content">
          <p>
            We're glad you are here, and want to help set you up so you can take
            advantage of the powertool that is D.T.
          </p>
          <p>
            D.T. can be used in many ways from managing connections and
            relationships, all the way through to tracking and managing a
            movement of Disciple Making.
          </p>
          <p>
            In order to help you, we want to take you through a series of
            choices to give you the best start at getting Disiple.Tools setup
            ready to suit your needs.
          </p>
          <p>
            Feel free to skip this setup stage if you already know what you
            need. But if this will be helpful for you let's dive in.
          </p>
        </div>
        <setup-wizard-controls
          hideBack
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-intro', SetupWizardIntro);
