import {
  html,
  repeat,
} from 'https://cdn.jsdelivr.net/gh/lit/dist@2.4.0/all/lit-all.min.js';
import { OpenLitElement } from './setup-wizard-open-element.js';

export class SetupWizardPeopleGroups extends OpenLitElement {
  static get properties() {
    return {
      step: { type: Object },
      firstStep: { type: Boolean },
      saving: { type: Boolean, attribute: false },
      finished: { type: Boolean, attribute: false },
      peopleGroups: { type: Array, attribute: false },
    };
  }

  constructor() {
    super();
    this.saving = false;
    this.finished = false;
    this.peopleGroups = [];
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    if (this.finished) {
      this.dispatchEvent(new CustomEvent('next'));
      return;
    }

    this.saving = true;
    await this.installPeopleGroups();
    this.saving = false;
    this.finished = true;
  }
  skip() {
    this.dispatchEvent(new CustomEvent('next'));
  }
  nextLabel() {
    if (this.finished) {
      return 'Next';
    }
    return 'Confirm';
  }

  async selectCountry(event) {
    const country = event.target.value;

    const peopleGroups =
      await window.dt_admin_shared.people_groups_get(country);

    this.peopleGroups = peopleGroups;
  }

  selectAll() {
    this.peopleGroups.forEach((group) => {
      group.selected = true;
    });
    this.requestUpdate();
  }
  async installPeopleGroups() {
    const peopleGroupsToInstall = this.peopleGroups.filter(
      (peopleGroup) => peopleGroup.selected,
    );

    for (let peopleGroup of peopleGroupsToInstall) {
      this.installPeopleGroup(peopleGroup);
      await this.wait(500);
    }
  }
  async installPeopleGroup(peopleGroup) {
    peopleGroup.installing = true;
    this.requestUpdate();

    let data = {
      rop3: peopleGroup.ROP3,
      country: peopleGroup.Ctry,
      location_grid: peopleGroup.location_grid,
    };

    await window.dt_admin_shared.people_groups_install(data);

    peopleGroup.installing = false;
    this.requestUpdate();
  }
  wait(time) {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve();
      }, time);
    });
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
            <p>or</p>
          </section>
          <section class="flow">
            <div>
              <ol>
                <li>Choose a country in the dropdown</li>
                <li>
                  Add only the people groups that you need for linking to
                  contacts in D.T.
                </li>
              </ol>
              <select name="country" id="country" @change=${this.selectCountry}>
                <option value="">Select a country</option>
                ${this.step
                  ? Object.values(this.step.config.countries).map((country) => {
                      return html`
                        <option value=${country}>${country}</option>
                      `;
                    })
                  : ''}
              </select>
            </div>
            <div class="people-groups">
              ${this.peopleGroups.length > 0
                ? html`
                    <table>
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>ROP3</th>
                          <th>
                            Add <br />
                            <span
                              style="color: blue;cursor: pointer"
                              @click=${() => this.selectAll()}
                            >
                              select all
                            </span>
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        ${repeat(
                          this.peopleGroups,
                          (people) => people[28],
                          (people) => {
                            let action = 'Added';
                            if (people.installing) {
                              action = html`<span class="spinner"></span>`;
                            } else if (!people.active) {
                              action = html`<input
                                type="checkbox"
                                .checked=${people.selected}
                              />`;
                            }

                            return html`
                              <tr
                                @click=${() => {
                                  people.selected = !people.selected;
                                  this.requestUpdate();
                                }}
                              >
                                <td>${people.PeopNameAcrossCountries}</td>
                                <td>${people.ROP3}</td>
                                <td>${action}</td>
                              </tr>
                            `;
                          },
                        )}
                      </tbody>
                    </table>
                  `
                : ''}
            </div>
          </section>
          ${this.finished
            ? html` <section class="ms-auto card success">Keys saved</section> `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          ?saving=${this.saving}
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
