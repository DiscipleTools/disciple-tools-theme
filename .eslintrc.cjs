module.exports = {
  "env": {
    "browser": true,
    "es6": true,
    "node": true,
  },
  "parserOptions": {
    "ecmaVersion": 2017, // enables parsing async functions correctly
    "requireConfigFile": false
  },
  "extends": "eslint:recommended",
  "globals": {
    "jQuery": false,
  },
  "rules": {
    "no-console": "off",
    "no-undef": "error",
    "no-unused-vars": "off",
    "no-empty": "off",
    "no-useless-escape": "off",
    'no-restricted-syntax': [
      'error',
      {
        message: "Please don't use _. for lodash as it conflict with underscore!",
        selector:
          'MemberExpression > Identifier[name="_"]'
      }
    ]
  },
  "parser": "@babel/eslint-parser",
  ignorePatterns: [ 'dt-core/admin/multi-role/*' ]
};
