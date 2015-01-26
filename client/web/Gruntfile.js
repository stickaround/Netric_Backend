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
                files: ['js/**/*.js', 'build/js/ui/**/*.js'],
                tasks: ['fileblocks:dev']
            },

            // Render jsx filse into js files
            react: {
                files: ['js/ui/**/*.jsx'],
                tasks: ['react']
            },
            
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
        react: {
            files: {
                expand: true,
                cwd: 'js/ui',
                src: ['**/*.jsx'],
                dest: 'build/js/ui',
                ext: '.js'
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
                    {expand: true, cwd: '.', src: ['images/**'], dest: 'dist/'},

                    // Copy css
                    {expand: true, cwd: '.', src: ['css/**'], dest: 'dist/'},

                    // Copy fonts
                    {expand: true, cwd: '.', src: ['fonts/**'], dest: 'dist/'},

                    // JS should be already copied by the concat_in_order task
                ]
            },
            build: {
                files: [
                    // Copy all js to build dir so we can merge with jsx
                    {expand: true, cwd: '.', src: ['js/**'], dest: 'build/'},
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
                        src: 'js/**/*.js'
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
    grunt.loadNpmTasks('grunt-concat-in-order');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-file-blocks');
    grunt.loadNpmTasks('grunt-wiredep');
    grunt.loadNpmTasks('grunt-svn-fetch');
    grunt.loadNpmTasks('grunt-react');

    /*
     * Now register callable tasks
     */

    // Register our own custom task alias.
    grunt.registerTask('concat', ['concat_in_order:all']);
    
    // Insert script tags into index test file
    grunt.registerTask('includes', ['wiredep', 'fileblocks:dev']);
    
    // Compine and put built application in dist
    grunt.registerTask('compile', ['svn_fetch', 'copy:build', 'react', 'concat', 'sass:dist', 'copy:main']);
    
    // Default will build sass, update js includes and then sit and watch for changes
    grunt.registerTask('default', ['svn_fetch:alib', 'sass:dist', 'includes', 'watch']);
};
