const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const TransferWebpackPlugin = require('transfer-webpack-plugin');

module.exports = function(config){
  config.set({

    basePath : '../',

    files : [
      'vendor/aereus/alib_full.js',
      'test/unit/**/*.js',
      'test/unit/entity/definitionLoaderSpec.js',

      // fixtures
      {pattern: 'svr/**/*', watched: true, served: true, included: false}
    ],

    // add preprocessor to the files that should be
    // processed via browserify
    preprocessors: {
      'test/unit/*Spec.js': [ 'webpack', 'sourcemap' ],
      'test/unit/**/*Spec.js': [ 'webpack', 'sourcemap' ]
    },

    // see what is going on
    //logLevel: 'LOG_DEBUG',

    autoWatch : true,

    frameworks: ['jasmine'],

    browsers : ['Chrome'],

    plugins : [
      'karma-chrome-launcher',
      'karma-firefox-launcher',
      'karma-webpack',
      'karma-sourcemap-loader',
      'karma-jasmine'
    ],

      // karma watches the test entry points
      // (you don't need to specify the entry option)
      // webpack watches dependencies
    webpack: {
        context: __dirname,
        entry: "../src/main.js",
        resolve: {
            extensions: ['', '.scss', '.js', '.jsx'],
            packageMains: ['browser', 'web', 'browserify', 'main', 'style', 'netric'],
            alias: [
                {
                    'netric': path.resolve(__dirname + '../src')
                }
            ],
            modulesDirectories: [
                'node_modules',
                path.resolve(__dirname, './../node_modules')
            ]
        },
        module: {
            loaders: [
                {
                    test: /\.js$/,
                    loader: 'babel',
                    exclude: /(node_modules)/
                },
                {
                    test: /\.(scss|css)$/,
                    loader: ExtractTextPlugin.extract('style', 'css?sourceMap&modules&importLoaders=1&localIdentName=[name]__[local]___[hash:base64:5]!postcss!sass?sourceMap')
                },
                {
                    test: /\.jsx$/,
                    loader: 'babel',
                    exclude: /(node_modules)/
                },
                {
                    test: /\.ttf$|\.eot$/,
                    loader: 'file',
                    query: {
                        name: 'fonts/[name].[ext]'
                    }
                }
            ]
        },
        devtool: 'inline-source-map'
    },

    webpackMiddleware: {
      // webpack-dev-middleware configuration
      // i. e.
        watchOptions: {
            poll: true
        }
    },

    // add additional browserify configuration properties here
    // such as transform and/or debug=true to generate source maps
    browserify: {
      debug: true,
      es6: true,
      transform: [
        ['babelify', {presets: ["es2015", "stage-1", "react"]}],
        ['envify', {NODE_ENV: 'test'}]
      ],
      configure: function(bundle) {
        bundle.on('prebundle', function() {
          bundle.external('netric');
        });
      }
    },

    junitReporter : {
      outputFile: 'test_out/unit.xml',
      suite: 'unit'
    }

  });
};
