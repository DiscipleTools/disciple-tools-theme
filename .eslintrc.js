module.exports = {
  "env": {
    "browser": true,
    "es6": true
  },
  "parserOptions": {
    "ecmaVersion": 2017, // enables parsing async functions correctly
  },
  "extends": "eslint:recommended",
  "globals": {
    "jQuery": false,
  },
  "rules": {
    "no-console": "off",
    "no-undef": "off",
    "no-unused-vars": "off",
    "no-empty": "off",
    "no-useless-escape": "off",
  },
  "parser": "babel-eslint"
};
