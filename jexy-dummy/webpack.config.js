const path = require('path');
const { merge } = require('webpack-merge');
/**
 * WordPress Dependencies
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');

module.exports = [
  merge(
    {
      ...defaultConfig,
      name: 'blocks',
      output: {
        path: path.resolve(__dirname, 'blocks/dist'),
      },
    },
    {}
  ),
  merge(
    {
      ...defaultConfig,
      name: 'admin',
      output: {
        path: path.resolve(__dirname, 'plugin/admin/dist'),
      },
    },
    {}
  )];
