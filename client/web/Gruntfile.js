module.exports = function(grunt) {
    grunt.initConfig({
        // Used to build
        distFolder: 'dist',
        pkg: grunt.file.readJSON('package.json'),

        browserify: {
            options: {
                debug: true,
                transform: [
                    ['babelify', {loose: "all", nonStandard: true}]
                ],
                extensions: ['.jsx'],
                browserifyOptions : {
                    standalone: 'netric'
                }
            },
            dev: {
                options: {
                  //alias: ['react:']  // Make React available externally for dev tools
                },
                //cwd: 'js',
                src: ['js/main.js'],
                dest: 'build/js/netric.js'
            },
            production: {
                options: {
                  debug: false
                },
                //cwd: 'js',
                //src: ['**/*.jsx'],
                src: ['js/main.js'],
                dest: 'dist/js/netric.js'
            }
        },
        
        /**
         * Settings for watch which basically monitors files for
         * changes and then runs tasks if changes were detected.
         */
        watch: {
            // sass files obviously have to be compiled before rendering the page
            sass: {
                files: [
                    'sass/**/*.{scss,sass}',
                    'sass/_partials/**/*.{scss,sass}'
                ],
                tasks: ['sass:dist']
            },
            
            // Build browserfly bundle
            browserify: {
                files: ['js/**/*.js', 'js/**/*.jsx'],
                tasks: ['browserify:dev']
            },

            // Make sure any new scripts are included in the html documents
            blocks: {
                //files: ['js/**/*.js', 'build/js/ui/**/*.js'],
                files: ['build/js/**/*.js'],
                tasks: ['fileblocks:dev']
            },

            // Render jsx filse into js files
            // react: {
            //     files: ['js/ui/**/*.jsx'],
            //     tasks: ['react', 'browserify']
            // },
            
            // Reload the browser if any of these files change
            livereload: {
                files: [
                    '*.html', 
                    'css/*.css',
                    'js/**/*.js',
                    'js/**/*.jsx',
                    'img/**/*.{png,jpg,jpeg,gif,webp,svg}'
                ],
                options: {
                    livereload: true
                }
            }
        },
        
        /*
         * Compile sass into CSS
         */
        sass: {
            options: {
                sourceComments: 'map',
                outputStyle: 'compressed'
            },
            dist: {
                    files: {
                        'css/base.css': 'sass/base.scss',
                        'css/theme-default.css': 'sass/theme-default.scss',
                        'css/font-awesome.css': 'sass/font-awesome/font-awesome.scss'
                    }
            }
        },

        /*
         * Compile react jsx files into normal js files
         */
        //react: {
        //    files: {
        //        expand: true,
        //        cwd: 'js/ui',
        //        src: ['**/*.jsx'],
        //        dest: 'build/js/ui',
        //        ext: '.js'
        //    }
        //},
        
        /**
         * Gather all javascript files and concat into a single file
         */
        concat_in_order: {
            all: {
                options: {
                    /*
                    this is a default function that extracts required dependencies/module names from file content
                    (getMatches - function that pick groups from given regexp)
                    extractRequired: function (filepath, filecontent) {
                      return this.getMatches(/require\(['"]([^'"]+)['"]/g, filecontent);
                    },
                    this is a default function that extracts declared modules names from file content
                    extractDeclared: function (filepath, filecontent) {
                      return this.getMatches(/declare\(['"]([^'"]+)['"]/g, filecontent);
                    }
                    */
                   //onlyConcatRequiredFiles: true
                },
                files: {
                  'dist/js/netric.js': ['build/**/*.js']
                }
            }
        },
        
        /*
         * Task settings to copy published files to the dist directory
         */
        copy: {
            main: {
                files: [
                    // Copy images
                    {expand: true, cwd: '.', src: ['img/**'], dest: 'dist/'},

                    // Copy css
                    {expand: true, cwd: '.', src: ['css/**'], dest: 'dist/'},

                    // Copy fonts
                    {expand: true, cwd: '.', src: ['fonts/**'], dest: 'dist/'},

                    // Copy chamel ui css
                    {expand: true, cwd: './node_modules/chamel/dist', src: ['css/**'], dest: 'dist/'},

                    // Copy react - no longer needed because we now use requirejs for this
                    //{expand: true, cwd: '.', src: ['vendor/react/react-with-addons.min.js'], dest: 'dist/js/'},

                    // Copy aereus lib
                    {expand: true, cwd: '.', src: ['vendor/aereus/alib_full.cmp.js'], dest: 'dist/js/'},

                    // JS should be already copied by the browserify:production task
                ]
            },
            build: {
                files: [
                    // Copy all js to build dir so we can merge with jsx
                    {expand: true, cwd: '.', src: ['js/**'], dest: 'build/'},

                    // Copy chamel ui css
                    {expand: true, cwd: './node_modules/chamel/dist', src: ['css/**'], dest: 'build/'},
                ]
            }
        },
        
        /*
         * Automatically insert script tags into index.html
         */
        fileblocks: {
            /* Task options */
            options: {
                templates: {
                    'jsx': '<script type="text/jsx" src="${file}"></script>',
                    md: '+ ${file}' // Add a custom template
                }
            },
            dev: {
                src: 'index.html',
                blocks: {
                    'app': { 
                        src: 'build/js/**/*.js'
                    },
                    'components': {
                        src: 'build/js/ui/**/*.js'
                    }
                }
            }
        },

        /*
         * Wire in bower dependencies
         */
        wiredep: {

            target: {

                // Point to the files that should be updated when
                // you run `grunt wiredep`
                src: [
                    '**/*.html'
                ]
            }
        }

        /*
         * Load aereus lib
         
        svn_fetch: {
            options: {
                'repository': 'svn://src.aereus.com/var/src/',
                'path': 'vendor/'
            },
            alib: {
                map: {
                    'aereus': 'lib/js/trunk'
                }
            }
        }
        */
    });

    /*
     * Load up tasks
     */
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-concat-in-order');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-file-blocks');
    grunt.loadNpmTasks('grunt-wiredep');
    //grunt.loadNpmTasks('grunt-svn-fetch');
    grunt.loadNpmTasks('grunt-react');

    /*
     * Now register callable tasks
     */

    // Register our own custom task alias.
    grunt.registerTask('concat', ['concat_in_order:all']);
    
    // Insert script tags into index test file
    grunt.registerTask('includes', ['wiredep', 'fileblocks:dev']);
    
    // Compine and put built application in dist
    grunt.registerTask('compile', ['sass:dist', 'browserify:production', 'copy:main']);
    
    // Default will build sass, update js includes and then sit and watch for changes
    grunt.registerTask('default', ['sass:dist', 'browserify:dev', 'includes', 'watch']);

    // We are utilizing browserify for react components
    grunt.loadNpmTasks('grunt-browserify');
};
