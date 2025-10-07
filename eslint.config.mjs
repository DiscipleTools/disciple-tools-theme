import js from '@eslint/js';
import babelParser from '@babel/eslint-parser';
import prettierConfig from 'eslint-config-prettier';

export default [
  js.configs.recommended,
  prettierConfig,
  {
    languageOptions: {
      ecmaVersion: 2017,
      sourceType: 'module',
      parser: babelParser,
      parserOptions: {
        requireConfigFile: false
      },
      globals: {
        // Browser globals
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        navigator: 'readonly',
        alert: 'readonly',
        confirm: 'readonly',
        prompt: 'readonly',
        setTimeout: 'readonly',
        setInterval: 'readonly',
        clearTimeout: 'readonly',
        clearInterval: 'readonly',
        fetch: 'readonly',
        localStorage: 'readonly',
        sessionStorage: 'readonly',
        location: 'readonly',
        // Node globals
        process: 'readonly',
        __dirname: 'readonly',
        __filename: 'readonly',
        require: 'readonly',
        module: 'readonly',
        exports: 'readonly',
        // Custom globals
        jQuery: 'readonly',
        Toastify: 'readonly',
      }
    },
    rules: {
      'no-console': 'off',
      'no-undef': 'error',
      'no-unused-vars': 'off',
      'no-empty': 'off',
      'no-useless-escape': 'off',
      'no-restricted-syntax': [
        'error',
        {
          message: "Please don't use _. for lodash as it conflict with underscore!",
          selector: 'MemberExpression > Identifier[name="_"]'
        }
      ]
    }
  },
  {
    ignores: [
      'dt-assets/js/modernizr-custom.js',
      'dt-assets/build/**/*',
      'dt-core/admin/multi-role/js/min/**/*',
      'dt-core/admin/multi-role/*',
      'dt-core/dependencies/**/*',
      'dt-core/libraries/**/*',
      'gulpfile.js',
      'node_modules/**/*',
      'vendor/**/*',
      '**/*.min.js',
      'cypress.config.js',
      'cypress/**/*'
    ]
  }
];
