/**
 * Users table in LitElement
 */

jQuery(document).ready(function($) {
  $('#chart').empty().html(`
    <style>
        users-table {
            --sort-both: url(${window.wpApiShare.template_dir + '/dt-assets/images/sort_both.png'});
            --sort-desc: url(${window.wpApiShare.template_dir + '/dt-assets/images/sort_desc.png'});
            --sort-asc: url(${window.wpApiShare.template_dir + '/dt-assets/images/sort_asc.png'});
        }
    </style>
    <users-table></users-table>`)
})

import {html, css, LitElement} from 'https://cdn.jsdelivr.net/gh/lit/dist@2/core/lit-core.min.js';

export class UsersTable extends LitElement {
  static properties = {
    users: {type: Array, state:true},
    search: {type: String, state:true},
    sort: {type: String, state:true},
    loading : {type: Boolean, state:true},
    total_users : {type: Number, state:true}
  }

  constructor() {
    super();

    this.users = [{name:'loading...'}]
    this.getUsers();

    this.translations = window.SHAREDFUNCTIONS.escapeObject( window.dt_users_table.translations );
  }

  async getUsers( search = '', sort = '', filter ) {
    this.loading = true;
    let users =  await fetch(window.dt_users_table.rest_endpoint + `get-users`, {
      method: 'POST',
      body: JSON.stringify({
        limit: 500,
        search,
        sort,
        filter
      }),
      headers: {
        "Content-Type": "application/json",
        'X-WP-Nonce': window.wpApiShare.nonce
      }
    }).then(res => res.json());
    this.loading = false;
    this.users = users.users;
    this.total_users = users.total_users;

  }

  open_edit_modal(user_id){
    window.open_user_modal( user_id )
  }

  search_text(){
    this.search = this.shadowRoot.querySelector('#search-users').value;
    this.getUsers( this.search )
  }

  clear_search(){
    this.search = '';
    this.getUsers( this.search )
  }

  sort_column(e){
    let id = e.target.id;
    let sort = e.target.id;
    if ( this.sort === sort ) {
      sort = sort.includes('-') ? sort.replace('-', '') : '-' + sort
    }
    this.getUsers( this.search, sort )

    //add sort_asc or sort_desc class to column
    this.shadowRoot.querySelectorAll('th').forEach(th=> th.classList.remove('sorting_asc', 'sorting_desc'));
    this.shadowRoot.querySelector('#' + id).classList.add(sort.includes('-') ? 'sorting_desc' : 'sorting_asc');

    this.sort = sort;
  }

  filter_column(column, value, e){
    let filter = {};
    filter[column] = value;
    this.getUsers( this.search, this.sort, filter )

    //set all filter selects to empty
    this.shadowRoot.querySelectorAll('.filter-select').forEach(select=> select.value = '');
    e.target.value = value;
  }

  render() {
    return html`
        <div id="title-row">
            <div>
                <h2>${this.translations.users} ${this.loading ? html`<img style="height:1em;" src="${window.wpApiShare.template_dir}/spinner.svg" alt="spinner" />` : html`<span style="font-size: 14px;font-weight: normal">${this.translations.showing_x_of_y.replace('%1$s', this.users.length).replace('%2$s', this.total_users)}`}</span></h2>
            </div>
            <div class="search-section">
                <input id="search-users" type="search" placeholder="${this.translations.search}" @search="${this.clear_search}">
                <button class="button" @click="${this.search_text}">${this.translations.go}</button>
            </div>

        </div>
        <br>
        <table class="sortable">
            <tr class="filter-row">
                ${Object.keys(window.dt_users_table.fields).map(k=>{
                  if ( window.dt_users_table.fields[k].hidden === true  ) return;
                  if ( window.dt_users_table.fields[k].options  ) {
                    let options = window.dt_users_table.fields[k].options;
                    return html`<td data-field="${k}" data-type="${window.dt_users_table.fields[k].type}">
                        <select class="filter-select" @change="${e=>this.filter_column(k,e.target.value, e)}">
                            <option value=""></option>
                            ${Object.keys(options).map(o=> html`<option value="${o}">${options[o].label}</option>`)}
                        </select>
                    </td>`
                  } else {
                    return html`<td data-field="${k}" data-type="${window.dt_users_table.fields[k].type}"></td>`
                  }
                })}
            </tr>
            <tr>
                ${Object.keys(window.dt_users_table.fields).map(k=>{
                  if ( window.dt_users_table.fields[k].hidden === true  ) return;
                  return html`<th id="${k}" data-field="${k}" @click="${this.sort_column}" data-type="${window.dt_users_table.fields[k].type}">
                      ${window.dt_users_table.fields[k].label}
                  </th>`
                })}
            </tr>
            
            ${this.users.length ? this.users.map(user => html`
                <tr class="user_row" data-user="${user.ID}" @click="${e=>this.open_edit_modal(user.ID)}">
                    ${Object.keys(window.dt_users_table.fields).map(k=>{
                      if ( window.dt_users_table.fields[k].hidden === true  ) return;
                      let field = window.dt_users_table.fields[k];
                      
                      if ( field.type === 'text' || field.type === 'number' ){
                        return html`<td>${user[k]}</td>`
                      } else
                      //date fields
                      if ( field.type === 'date' ){
                        return html`<td>${user[k] ? window.SHAREDFUNCTIONS.formatDate(user[k]):''}</td>`
                      } else
                      //array fields   
                      if ( ['array', 'array_keys'].includes(field.type) ){
                        let labels = (user[k] || []).map(v=>{
                          return field.options[v]?.label || v
                        }).join(', ')
                        return html`<td>${labels}</td>`
                      } else
                      //key select fields  
                      if ( ['key_select'].includes(field.type)){
                        return html`<td>${field.options[user[k]]?.label || user[k]}</td>`
                      } else
                      //location grid fields  
                      if ( ['location_grid'].includes(field.type) ){
                        let labels = (user[k] || []).map(v=>{
                          return v.label || v
                        }).join(', ')
                        return html`<td>${labels}</td>`
                      }
                    })}
                </tr>
            `
            ) : html`` }
        </table>
    `;
  }
  static get styles() {
    return [
      css`
        table {
          font-family: arial, sans-serif;
          border-collapse: collapse;
          width: 100%;
          table-layout: fixed;
        }
        
        td, th {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
          //width: 150px;
        }
        th[data-type="number"],td[data-type="number"] {
          width: 75px;
        }
        th[data-field="ID"],td[data-field="ID"] {
          width: 50px;
        }
        
        tr:not(.filter-row):nth-child(even) {
          background-color: #eee;
        }
        .sortable th {
          background-repeat: no-repeat;
          background-position: center right;
          padding-right:1.5rem;
          background-image: var(--sort-both);
        }
        .sortable .sorting_desc {
          background-image: var(--sort-desc);
        }
        .sortable .sorting_asc {
          background-image: var(--sort-asc);
        }
        #title-row {
          display: flex;
          justify-content: space-between;
        }
        .search-section {
          margin: auto 0;
        }
        .filter-row td {
          border: none;
        }
        .filter-row td select {
          width: 100%;
        }
        .filter-select {
          width: 100%;
        }
        .loading-table {
          display: none;
        }
        tr.user_row:hover {
          background-color: #ddd;
          cursor: pointer;
        }
        td {
          overflow: hidden;
        }
        
        select {
          width: 100%;
          font-size: 14px;
          line-height: 2;
          color: #2c3338;
          border-color: #8c8f94;
          box-shadow: none;
          border-radius: 3px;
          padding: 0 24px 0 8px;
          min-height: 30px;
          max-width: 25rem;
          -webkit-appearance: none;
          background: #fff url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;
          background-size: 16px 16px;
          cursor: pointer;
          vertical-align: middle;
        }
        button {
          margin-bottom: 0.2em;
          margin-top: 0.2em;
          padding: 0.4em 0.75em;
          border-radius: 5px;
          background-color: #3f729b;
          color: #fefefe;
          border: 1px solid transparent;
          cursor: pointer;
        }
        input {
          padding: 0 8px;
          line-height: 2;
          min-height: 30px;
          box-shadow: 0 0 0 transparent;
          border-radius: 4px;
          border: 1px solid #8c8f94;
          background-color: #fff;
          color: #2c3338;
        }
      `
    ]
  }
}

customElements.define('users-table', UsersTable);