//webpack.config.js
const path = require('path');
const {argv} = require('process');

const config = {
  entry: {
    main: './src/index.ts',
  },
  output: {
    path: path.resolve(__dirname, '../assets'),
    filename: 'view.js',
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js'],
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        loader: 'ts-loader',
      },
    ],
  },
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'inline-source-map';
  }
  return config;
};
