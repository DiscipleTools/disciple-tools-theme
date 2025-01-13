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
      <div class="step-layout">
        <h2>Personalize Your Disciple.Tools Experience!</h2>
        <div class="content stack">
          <div class="centered-view">
            <p>
              We’re excited to help you get started with Disciple.Tools — a powerful CRM designed to support disciple-making and movement-building.
            </p>
            <p>
              Whether you’re managing connections, fostering relationships, or tracking the growth of a disciple-making movement, this tool can adapt to your needs.
            </p>
            <p>
              To give you the best start, we’ll guide you through a few simple steps to customize your setup:
            </p>
            <p>
              <ol class="bubble-list">
                <li>
                  <strong>Choose system features</strong>: Decide which parts of Disciple.Tools you want to enable.
                </li>
                <li>
                  <strong>Install plugins</strong>: Pick the tools and integrations you need.
                </li>
                <li>
                  <strong>Complete additional setup</strong>: Configure options to tailor the system to your goals.
                </li>
              </ol>
            </p>
            <p style="text-align: center; margin-top:2rem">
              <strong class="text-blue">Ready? Let's get started.</strong>
            </p>
          </div>
        </div>
        <setup-wizard-controls
          hideBack
          hideSkip
          @next=${this.next}
          @back=${this.back}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-intro', SetupWizardIntro);
