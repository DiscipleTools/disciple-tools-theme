import { html } from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardKeys extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      _options: { type: Object, attribute: false },
      _saving: { type: Boolean, attribute: false },
      _finished: { type: Boolean, attribute: false },
    };
  }

  constructor() {
    super();
    this._saving = false;
    this._finished = false;
    this._options = {
      dt_mapbox_api_key: '',
      dt_google_map_key: '',
    };
    this.show_mapbox_instructions = false;
    this.show_google_instructions = false;
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
    await window.dt_admin_shared.update_dt_options(this._options);
    this._saving = false;
    this._finished = true;
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }

  render() {
    this._options.dt_mapbox_api_key = this.step.config.dt_mapbox_api_key;
    this._options.dt_google_map_key = this.step.config.dt_google_map_key;
    return html`
      <div class="cover">
        <div class="content flow">
          <h2>Part 3: Mapping and Geocoding</h2>
          <p>
            Disciple.Tools comes with high level mapping which is only as
            precise as a country county or department. Extra tools are needed to
            be able to geolocate to more precise locations like a street address
            or a city. See
            <a
              href="https://disciple.tools/user-docs/getting-started-info/admin/geolocation/"
              >Geolocation Docs</a
            >
            for more details.
          </p>
          <p>
            We highly recommend setting up MapBox to enable better mapping and
            location pins for your records. This however is not required and
            Disciple.Tools can be used without it.
          </p>
          <p>
            Mapbox is needed for better maps. You will want to also add a Google
            key for more precise worldwide geocoding.
          </p>
          <table style="width: 100%">
            <thead>
              <tr>
                <th>Key</th>
                <th>Description</th>
                <th style="width: 30%">Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Mapbox key</td>
                <td>
                  Upgrade maps and get precise locations with a MapBox key.
                  <br />
                  <button
                    type="button"
                    @click=${() => {
                      this.show_mapbox_instructions =
                        !this.show_mapbox_instructions;
                      this.requestUpdate();
                    }}
                  >
                    Show Instructions
                  </button>
                  <div
                    ?hidden=${!this.show_mapbox_instructions}
                    style="max-width: 600px;font-size: 14px"
                  >
                    <ol>
                      <li>
                        Go to
                        <a href="https://www.mapbox.com/" target="_blank"
                          >MapBox.com</a
                        >.
                      </li>
                      <li>
                        Register for a new account (<a
                          href="https://account.mapbox.com/auth/signup/"
                          target="_blank"
                          >MapBox.com</a
                        >)<br />
                        <em
                          >(email required. A credit card might be required,
                          though you will likely not go over the free monthly
                          quota.)</em
                        >
                      </li>
                      <li>
                        Once registered, go to your account home page. (<a
                          target="_blank"
                          href="https://account.mapbox.com/"
                          >Account Page</a
                        >)<br />
                      </li>
                      <li>
                        Inside the section labeled "Access Tokens", either
                        create a new token or use the default token provided.
                        Copy this token.
                      </li>
                      <li>
                        Paste the token into the "Mapbox API Token" field in the
                        box above.
                      </li>
                    </ol>
                  </div>
                </td>
                <td>
                  <input
                    style="width: 100%"
                    type="text"
                    name="mapbox"
                    .value=${this.step.config.dt_mapbox_api_key || ''}
                    @input=${(e) => {
                      this._options.dt_mapbox_api_key = e.target.value;
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>Google key</td>
                <td>
                  Upgrade maps and get even more precise locations with a Google
                  key.
                  <br />
                  <button
                    type="button"
                    @click=${() => {
                      this.show_google_instructions =
                        !this.show_google_instructions;
                      this.requestUpdate();
                    }}
                  >
                    Show Instructions
                  </button>
                  <div
                    ?hidden=${!this.show_google_instructions}
                    style="max-width: 600px;font-size: 14px"
                  >
                    <ol>
                      <li>
                        Go to
                        <a href="https://console.cloud.google.com"
                          >https://console.cloud.google.com</a
                        >.
                      </li>
                      <li>
                        Register with your Google Account or Gmail Account
                      </li>
                      <li>Once registered, create a new project.<br /></li>
                      <li>
                        Then go to APIs & Services > Credentials and "Create
                        Credentials" API Key. Copy this key.
                      </li>
                      <li>
                        Paste the key into the "Google API Key" field in the box
                        above here in the Disciple.Tools Mapping Admin.
                      </li>
                      <li>
                        Again, in Google Cloud Console, in APIs & Services go to
                        Library. Find and enable: (1) Maps Javascript API, (2)
                        Places API, and (3) GeoCoding API.
                      </li>
                      <li>
                        Lastly, in in Credentials for the API key it is
                        recommended in the settings of the API key to be set
                        "None" for Application Restrictions and "Don't restrict
                        key" in API Restrictions.
                      </li>
                    </ol>
                  </div>
                </td>
                <td>
                  <input
                    style="width: 100%"
                    type="text"
                    name="mapbox"
                    .value=${this.step.config.dt_google_map_key || ''}
                    @input=${(e) => {
                      this._options.dt_google_map_key = e.target.value;
                    }}
                  />
                </td>
              </tr>
            </tbody>
          </table>
          ${this._finished
            ? html` <section class="card success">Keys saved</section> `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          @next=${this.next}
          @back=${this.back}
          @skip=${this.skip}
          ?saving=${this._saving}
        ></setup-wizard-controls>
      </div>
    `;
  }
}
customElements.define('setup-wizard-keys', SetupWizardKeys);
