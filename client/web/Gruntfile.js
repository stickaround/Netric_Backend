module.exports = function(grunt) {
    grunt.initConfig({
        // Used to build
        distFolder: 'dist',
        pkg: grunt.file.readJSON('package.json'),
        
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
            
            // Make sure any new scripts are included in the html documents
            blocks: {
                files: ['src/js/**/*.js'],
                tasks: ['fileblocks:dev']
            },
            
            // Reload the browser if any of these files change
            livereload: {
                files: [
                    '*.html', 
                    'css/*.css',
                    'js/**/*.js',
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
                        'css/theme-default.css': 'sass/theme-default.scss'
                    }
            }
        },
        
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
                  'dist/js/netric.js': ['js/**/*.js']
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
                    {expand: true, cwd: '.', src: ['images/**'], dest: 'dist/'},

                    // Copy css
                    {expand: true, cwd: '.', src: ['css/**'], dest: 'dist/'},

                    // JS should be already copied by the concat_in_order task
                ]
            }
        },
        
        /*
         * Automatically insert script tags into index.html
         */
        fileblocks: {
            dev: {
                src: 'index.html',
                blocks: {
                    'app': { 
                        src: 'js/**/*.js'
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
        },

        /*
         * Load aereus lib
         */
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
    });

    /*
     * Load up tasks
     */
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');

    /*
     * Now register callable tasks
     */

    // Register our own custom task alias.
    grunt.registerTask('concat', ['concat_in_order:all']);
    
    // Insert script tags into index test file
    grunt.registerTask('includes', ['wiredep', 'fileblocks:dev']);
    
    // Compine and put built application in dist
    grunt.registerTask('compile', ['svn_fetch', 'concat', 'sass:dist', 'copy']);
    
    // Default will build sass, update js includes and then sit and watch for changes
    grunt.registerTask('default', ['svn_fetch:alib', 'sass:dist', 'includes', 'watch']);
};
