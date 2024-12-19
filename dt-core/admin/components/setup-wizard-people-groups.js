import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardPeopleGroups extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      _saving: { type: Boolean, attribute: false },
      _finished: { type: Boolean, attribute: false },
    };
  }

  constructor() {
    super();
    this._saving = false;
    this._finished = false;
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    if (this._finished) {
      this.dispatchEvent(new CustomEvent('next'));
      return;
    }

    this._saving = true;
    /* TODO: save? or has that happened when they clicked buttons? */
    this._saving = false;
    this._finished = true;
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  nextLabel() {
    if (this._finished) {
      return 'Next';
    }
    return 'Confirm';
  }

  render() {
    return html`
      <div class="cover">
        <h2>Import People Groups</h2>
        <div class="content flow">
          <section>
            <p>
              If you're not sure which people groups to add, you can add them
              all. <br />(There are a lot of them though)
            </p>
            <button class="btn-primary">Import all</button>
          </section>
          <section>
            <p>or</p>
            <ol>
              <li>Choose a country in the dropdown</li>
              <li>
                Add only the people groups that you need for linking to contacts
                in D.T.
              </li>
            </ol>
            <select name="country" id="country">
              ${this.step
                ? Object.values(this.step.config.countries).map((country) => {
                    return html` <option value=${country}>${country}</option> `;
                  })
                : ''}
            </select>
          </section>
          ${this._finished
            ? html` <section class="ms-auto card success">Keys saved</section> `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          ?saving=${this._saving}
          nextLabel=${this.nextLabel()}
          @next=${this.next}
          @back=${this.back}
          @skip=${this.skip}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-people-groups', SetupWizardPeopleGroups);
