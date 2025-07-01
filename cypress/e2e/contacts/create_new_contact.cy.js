describe('Create New Contact', () => {

  const seed = Math.floor(Math.random() * 100);
  let shared_data = {
    'contact_name': `Cypress Contact [${seed}]`
  };

  it('Login to D.T frontend and create new contact.', () => {
    cy.session(
      'dt_frontend_login_and_create_new_contact',
      () => {

        /**
         * Ensure uncaught exceptions do not fail test run; however, any thrown
         * exceptions must not be ignored and a ticket must be raised, in order
         * to resolve identified exception.
         *
         * TODO:
         *  - Resolve any identified exceptions.
         */

        cy.on('uncaught:exception', (err, runnable) => {
          // Returning false here prevents Cypress from failing the test
          return false;
        });

        // Capture admin credentials.
        const dt_config = cy.config('dt');
        const username = dt_config.credentials.admin.username;
        const password = dt_config.credentials.admin.password;

        // Login to D.T frontend.
        cy.dtLogin(username, password);

        // Navigate to contacts list view.
        cy.visit('/contacts');

        // Click through to new contact record creation view.
        cy.get('a.create-post-desktop').click({
          force: true
        });

        // Set new required contact record name.
        cy.get('#name').type(shared_data.contact_name);

        // Submit new contact record creation request.
        cy.get('button.js-create-post-button').click({
          force: true
        });

        // Confirm new contact record has been successfully created.
        cy.visit('/contacts');
        cy.contains(shared_data.contact_name);
        cy.log(shared_data.contact_name);

      }
    );
  });

  it('Delete recently created contact record.', () => {
    cy.session(
      'delete_recently_created_contact_record',
      () => {

        /**
         * Ensure uncaught exceptions do not fail test run; however, any thrown
         * exceptions must not be ignored and a ticket must be raised, in order
         * to resolve identified exception.
         *
         * TODO:
         *  - Resolve any identified exceptions.
         */

        cy.on('uncaught:exception', (err, runnable) => {
          // Returning false here prevents Cypress from failing the test
          return false;
        });

        // Capture admin credentials.
        const dt_config = cy.config('dt');
        const username = dt_config.credentials.admin.username;
        const password = dt_config.credentials.admin.password;

        // Login to D.T frontend.
        cy.dtLogin(username, password);

        // Navigate to contacts list view.
        cy.visit('/contacts');

        // Obtain handle onto recently created contact record and navigate to record details.
        cy.contains(shared_data.contact_name).click();

        // Open delete model and confirm deletion request.
        cy.get('a[data-open="delete-record-modal"]').click({
          force: true
        });

        cy.get('#delete-record').click({
          force: true
        });

        // Finally, confirm record has been removed.
        cy.visit('/contacts');
        cy.contains(shared_data.contact_name).should('not.exist');

      }
    );
  });
});
