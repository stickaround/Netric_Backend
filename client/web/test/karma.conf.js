module.exports = function(config){
  config.set({

    basePath : '../',

    files : [
      'vendor/aereus/alib_full.js',
      'js/base.js',
      'js/mvc/mvc.js',
      'js/mvc/Controller.js',
      'js/**/*.js',
      'test/unit/**/*.js',

      // fixtures
      {pattern: 'svr/**/*', watched: true, served: true, included: false}
    ],

    autoWatch : true,

    frameworks: ['jasmine'],

    browsers : ['Chrome'],

    plugins : [
            'karma-chrome-launcher',
            'karma-firefox-launcher',
            'karma-jasmine'
    ],

    junitReporter : {
      outputFile: 'test_out/unit.xml',
      suite: 'unit'
    }

  });
};