import globals from 'globals';
import pluginJs from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import eslintConfigPrettier from 'eslint-config-prettier';

export default [
  { languageOptions: { globals: { ...globals.browser, ...globals.node, route: 'readonly' } } },

  pluginJs.configs.recommended,

  ...pluginVue.configs['flat/recommended'],

  eslintConfigPrettier,

  {
    files: ['**/*.vue', '**/*.js', '**/*.mjs'],
    rules: {
      'vue/multi-word-component-names': 'off',
    },
  },
  {
    ignores: [
      'node_modules/',
      'vendor/',
      'public/build/',
      'storage/',
      'bootstrap/cache/',
      '*.blade.php',
      'eslint.config.js',
    ],
  },
];
