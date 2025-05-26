// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

// -- Frontend D.T Login -- //
Cypress.Commands.add('dtLogin', (username, password) => {

  // Navigate to DT frontend login page.
  cy.visit('/wp-login.php');

  // Specify credentials and submit.
  cy.get('#user_login').invoke('attr', 'value', username);
  cy.get('#user_pass').invoke('attr', 'value', password);
  cy.get('#wp-submit').click();

});
