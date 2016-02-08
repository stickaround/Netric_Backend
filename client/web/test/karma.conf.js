module.exports = function(config){
  config.set({

    basePath : '../',

    files : [
      'vendor/aereus/alib_full.js',
      //'test/unit/**/*.js',
      'test/unit/entity/EntitySpec.js',

      // fixtures
      {pattern: 'svr/**/*', watched: true, served: true, included: false}
    ],

    // add preprocessor to the files that should be
    // processed via browserify
    preprocessors: {
      'test/unit/**/*.js': [ 'browserify' ]
    },

    // see what is going on
    //logLevel: 'LOG_DEBUG',

    autoWatch : true,

    frameworks: ['browserify', 'jasmine'],

    browsers : ['Chrome'],

    plugins : [
      'karma-chrome-launcher',
      'karma-firefox-launcher',
      'karma-browserify',
      'karma-jasmine'
    ],

    // add additional browserify configuration properties here
    // such as transform and/or debug=true to generate source maps
    browserify: {
      debug: true,
      es6: true,
      transform: [
        ['babelify', {loose: "all", nonStandard: true}],
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
