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
      gettingBatches: { type: Boolean, attribute: false },
      importingAll: { type: Boolean, attribute: false },
      batchNumber: { type: Boolean, attribute: false },
      totalBatches: { type: Boolean, attribute: false },
      batchSize: { type: Number, attribute: false },
      countryInstalling: { type: String, attribute: false },
      importingFinished: { type: Boolean, attribute: false },
    };
  }

  constructor() {
    super();
    this.saving = false;
    this.finished = false;
    this.peopleGroups = [];
    this.peopleGroupsInstalled = [];
  }

  back() {
    this.dispatchEvent(new CustomEvent('back'));
  }
  async next() {
    if (this.isFinished()) {
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
    if (this.isFinished()) {
      return 'Next';
    }
    return 'Confirm';
  }
  isFinished() {
    return this.finished || this.importingFinished;
  }

  async selectCountry(event) {
    const country = event.target.value;

    const peopleGroups =
      await window.dt_admin_shared.people_groups_get(country);

    this.peopleGroups = peopleGroups;
  }
  selectPeopleGroup(people) {
    people.selected = !people.selected;
    this.finished = false;
    this.requestUpdate();
  }
  selectAll() {
    const selectAllOrNone =
      this.peopleGroups.filter(({ selected }) => selected).length ===
      this.peopleGroups.length
        ? false
        : true;
    const peopleGroupsInstalled = this.peopleGroups.filter(
      ({ installed, ROP3 }) =>
        !installed && !this.peopleGroupsInstalled.includes(ROP3),
    );

    peopleGroupsInstalled.forEach((group) => {
      group.selected = selectAllOrNone;
    });
    console.log(peopleGroupsInstalled);
    if (peopleGroupsInstalled.length > 0) {
      this.finished = false;
      this.requestUpdate();
    }
  }
  async installPeopleGroups() {
    const peopleGroupsToInstall = this.peopleGroups.filter(
      (peopleGroup) => peopleGroup.selected,
    );

    const installationPromises = peopleGroupsToInstall.map((peopleGroup, i) => {
      return this.wait(500 * i).then(() => {
        return this.installPeopleGroup(peopleGroup);
      });
    });

    await Promise.all(installationPromises);
  }
  async installPeopleGroup(peopleGroup) {
    peopleGroup.selected = false;
    peopleGroup.installing = true;
    this.requestUpdate();

    let data = {
      rop3: peopleGroup.ROP3,
      country: peopleGroup.Ctry,
      location_grid: peopleGroup.location_grid,
    };

    await window.dt_admin_shared.people_groups_install(data);

    peopleGroup.installing = false;
    peopleGroup.installed = true;
    this.peopleGroupsInstalled.push(peopleGroup.ROP3);
    this.requestUpdate();
  }
  wait(time) {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve();
      }, time);
    });
  }

  async getAllBatches() {
    this.gettingBatches = true;
    const result = await window.dt_admin_shared.people_groups_get_batches();
    this.gettingBatches = false;

    if (
      confirm(
        `Are you sure you want to import a total of ${result.total_records} people groups?`,
      )
    ) {
      return this.importAll(result.batches);
    }
  }

  async importAll(batches) {
    this.importingAll = true;
    this.batchNumber = 0;
    this.totalBatches = batches.length;
    for (const country in batches) {
      this.batchNumber = this.batchNumber + 1;
      const batch = batches[country];

      this.countryInstalling = country;
      this.batchSize = batch.length;
      await window.dt_admin_shared.people_groups_install_batch(batch);

      /* if (this.batchNumber === 2) {
        break;
      } */
    }
    this.importingAll = false;
    this.importingFinished = true;
  }

  render() {
    return html`
      <div class="cover">
        <h2>Import People Groups</h2>
        <div class="content flow">
          <section class="flow">
            <p>
              If you're not sure which people groups to add, you can add them
              all. <br />(There are around 17,000.)
            </p>
            ${!this.importingAll && !this.importingFinished
              ? html`
                  <button
                    class="btn-primary fit-content"
                    @click=${this.getAllBatches}
                  >
                    Import all
                    ${this.gettingBatches
                      ? html`<span class="spinner light"></span>`
                      : ''}
                  </button>
                `
              : ''}
            ${this.importingAll && !this.importingFinished
              ? html`
                  <div class="cluster">
                    <span class="spinner light"></span>
                    <p>
                      ${this.countryInstalling}: Processing ${this.batchSize}
                      people groups
                    </p>
                  </div>
                `
              : ''}
            ${this.importingAll
              ? html`
                  <button
                    class="btn-primary fit-content"
                    @click=${this.stopImport}
                  >
                    Stop Import
                  </button>
                `
              : ''}
          </section>
          <section class="flow">
            ${!this.importingAll && !this.importingFinished
              ? html`<div>
                  <p>or</p>
                  <ol>
                    <li>Choose a country in the dropdown</li>
                    <li>
                      Add only the people groups that you need for linking to
                      contacts in D.T.
                    </li>
                  </ol>
                  <select
                    name="country"
                    id="country"
                    @change=${this.selectCountry}
                  >
                    <option value="">Select a country</option>
                    ${this.step
                      ? Object.values(this.step.config.countries).map(
                          (country) => {
                            return html`
                              <option value=${country}>${country}</option>
                            `;
                          },
                        )
                      : ''}
                  </select>
                </div>`
              : ''}
            <div class="flow | people-groups">
              ${this.peopleGroups.length > 0
                ? html`
                    <table>
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>ROP3</th>
                          <th>
                            Add <br />
                            <button
                              class="btn-outline"
                              @click=${() => this.selectAll()}
                            >
                              select all
                            </button>
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
                            } else if (
                              !people.installed &&
                              !this.peopleGroupsInstalled.includes(people.ROP3)
                            ) {
                              action = html`<input
                                type="checkbox"
                                .checked=${people.selected}
                              />`;
                            }

                            return html`
                              <tr
                                @click=${() => this.selectPeopleGroup(people)}
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
            ? html`
                <section class="ms-auto card success">
                  People Groups installed
                </section>
              `
            : ''}
          ${this.importingFinished
            ? html`
                <section class="ms-auto card success">
                  Finished importing all people groups
                </section>
              `
            : ''}
        </div>
        <setup-wizard-controls
          ?hideBack=${this.firstStep}
          ?saving=${this.saving || this.importingAll}
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