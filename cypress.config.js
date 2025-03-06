const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
    baseUrl: 'https://wp.ddev.site:8443'
  },
  dt: {
    credentials: {
      admin: {
        username: 'admin',
        password: 'admin'
      }
    }
  }
});
