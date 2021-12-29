/**
 * Grunt Configuration File.
 *
 * @since 1.0.0
 *
 * @param grunt
 */
module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		filesPHP: [
			'gofer-seo/*.php',
			'gofer-seo/admin/**/*.php',
			'gofer-seo/admin/*.php',
			'gofer-seo/includes/**/*.php',
			'gofer-seo/includes/*.php',
			'gofer-seo/public/**/*.php',
			'gofer-seo/public/*.php',
			// 'gofer-seo/templates/**/*.php',
			// 'gofer-seo/templates/*.php',
			'!**/index.php',
			'!**/compatibility/wp/**'
		],
		filesJS: [
			'gofer-seo/**/*.js',
			'!**/*.min.js',

			'!**/build/**',
			'!**/assets/bootstrap-v*/*.js',
			'!**/assets/select2-v*/*.js',
			'!**/assets/google-analytics-autotrack-v*/*.js'
		],
		filesCSS: [
			'gofer-seo/admin/css/**/*.css',
			'!gofer-seo/admin/css/**/*.min.css',
			'gofer-seo/admin/css/*.css',
			'!gofer-seo/admin/css/*.min.css',
			'gofer-seo/public/css/**/*.css',
			'!gofer-seo/public/css/**/*.min.css',
			'gofer-seo/public/css/*.css',
			'!gofer-seo/public/css/*.min.css'
		],
		filesMinJS: [
			'**/admin/js/*.js',
			'**/admin/js/**/*.js',
			'**/public/js/*.js',
			'**/public/js/**/*.js',
			'!**/*.min.js',

			'!**/assets/bootstrap-v*/*.js',
			'!**/assets/select2-v*/*.js',
			'!**/assets/google-analytics-autotrack-v*/*.js'
		],
		filesMinCSS: [
			'**/admin/css/*.css',
			'**/admin/css/**/*.css',
			'**/public/css/*.css',
			'**/public/css/**/*.css',
			'!**/css/*.min.css',
			'!**/css/**/*.min.css',

			'!**/assets/select2-v*/*.css'
		],

		// https://www.npmjs.com/package/grunt-mkdir#the-mkdir-task
		mkdir: {
			logs: {
				options: {
					mode: 777,
					create: ['logs']
				}
			}
		},

		// https://www.npmjs.com/package/grunt-phpcbf#the-phpcbf-task
		phpcbf: {
			options: {
				bin: 'vendor/bin/phpcbf',
				standard: 'phpcs.xml',
				noPatch: false,
				extensions: 'php'
			},
			src: [
				'<%= filesPHP %>'
			]
		},

		// https://www.npmjs.com/package/phplint#grunt
		// https://www.npmjs.com/package/grunt-phplint
		phplint: {
			options: {
				standard: 'phpcs.xml'
			},
			src: [
				'<%= filesPHP %>'
			]
		},

		// https://www.npmjs.com/package/grunt-phpcs#php-code-sniffer-task
		phpcs: {
			options: {
				bin: 'vendor/bin/phpcs',
				standard: 'phpcs.xml',
				reportFile: 'logs/phpcs.log'
			},
			src: [
				'<%= filesPHP %>'
			]
		},

		// https://www.npmjs.com/package/jshint
		// https://www.npmjs.com/package/grunt-contrib-jshint
		jshint: {
			options: {
				jshintrc: true,
				reporterOutput: 'logs/jshint.log'
			},
			all: [
				'<%= filesJS %>'
			]
		},

		// https://www.npmjs.com/package/eslint
		// https://www.npmjs.com/package/grunt-eslint
		eslint: {
			options: {
				outputFile: 'logs/eslint.log'
			},
			target: [
				'<%= filesJS %>'
			]
		},

		// https://www.npmjs.com/package/grunt-contrib-uglify
		uglify: {
			dev: {
				files: [{
					expand: true,
					cwd: 'gofer-seo/',
					src: [
						'<%= filesMinJS %>'
					],
					dest: ['gofer-seo/'],
					rename: function(dst, src) {
						// To keep the source js files and make new files as `*.min.js`:
						return dst + '/' + src.replace('.js', '.min.js');
					}
				}]
			}
		},

		// https://www.npmjs.com/package/grunt-contrib-cssmin
		cssmin: {
			dev: {
				files: [{
					expand: true,
					cwd: 'gofer-seo/',
					src: [
						'<%= filesMinCSS %>'
					],
					dest: 'gofer-seo/',
					ext: '.min.css'
				}]
			}
		},

		// https://www.npmjs.com/package/grunt-stylelint
		stylelint: {
			options: {
				configFile: '.stylelintrc',
				formatter: 'string',
				ignoreDisables: false,
				failOnError: true,
				outputFile: 'logs/stylelint.log',
				reportNeedlessDisables: false,
				fix: false,
				syntax: ''
			},

			auto_fix: {
				fix: true
			},
			check: {
				src: [
					'<%= filesCSS %>'
				]
			},
			check_strict: {
				options: {
					ignoreDisables: true,
					reportNeedlessDisables: true
				},
				src: [
					'<%= filesCSS %>'
				]
			}
		},

		// https://www.npmjs.com/package/grunt-contrib-csslint
		// Deprecated.
		csslint: {
			options: {
				'adjoining-classes': false,
				'box-model': false,
				'bulletproof-font-face': false,
				'display-property-grouping': false,
				'duplicate-background-images': false,
				'duplicate-properties': false,
				'empty-rules': false,
				'errors': false,
				'fallback-colors': false,
				'floats': false,
				'font-sizes': false,
				'ids': false,
				'important': false,
				'order-alphabetical': false,
				'outline-none': false,
				'overqualified-elements': false,
				'qualified-headings': false,
				'regex-selectors': false,
				'unique-headings': false,
				'universal-selector': false,
				'unqualified-attributes': false,
				'zero-units': false,

				csslintrc: '.stylelintrc',
				formatters: [
					{id: 'text', dest: 'logs/csslint.log'}
					// {id: 'compact', dest: 'logs/csslint_compact.txt'}
				]
			},
			src: [
				'gofer-seo/admin/css/**/*.css',
				'!gofer-seo/admin/css/**/*.min.css',
				'gofer-seo/admin/css/*.css',
				'!gofer-seo/admin/css/*.min.css',
				'gofer-seo/public/css/**/*.css',
				'!gofer-seo/public/css/**/*.min.css',
				'gofer-seo/public/css/*.css',
				'!gofer-seo/public/css/*.min.css'
			]
		}
	});

	// Load the plugins.
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-phpcbf');
	grunt.loadNpmTasks('grunt-phpcs');
	grunt.loadNpmTasks('grunt-phplint');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-eslint');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-csslint');
	grunt.loadNpmTasks('grunt-stylelint');

	// Default task(s).
	grunt.registerTask(
		'default',
		[
			'mkdir',
			'phpcbf',
			'phpcs',
			'phplint',
			'jshint',
			'eslint',
			'uglify',
			'stylelint:auto_fix',
			'stylelint:check',
			'cssmin'
		]
	);
	grunt.registerTask(
		'check',
		[
			'mkdir',
			'phpcs',
			'phplint',
			'jshint',
			'eslint',
			'stylelint:check'
		]
	);
	grunt.registerTask(
		'check php',
		[
			'mkdir',
			'phpcs',
			'phplint'
		]
	);
	grunt.registerTask(
		'check js',
		[
			'mkdir',
			'jshint',
			'eslint'
		]
	);
	grunt.registerTask(
		'check css',
		[
			'mkdir',
			'stylelint:check'
		]
	);
	grunt.registerTask(
		'check auto',
		[
			'mkdir',
			'phpcbf',
			'stylelint:auto_fix'
		]
	);
	grunt.registerTask(
		'build',
		[
			'uglify',
			'cssmin'
		]
	);

};
