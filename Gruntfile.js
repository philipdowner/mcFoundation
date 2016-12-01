module.exports = function(grunt) {
  
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
		
		sass: {
      options: {
	      sourceMap: true
      },
      
      dist: {
        options: {
          style: 'compressed'
        },
        files: {
					'css/foundation.min.css': 'scss/foundation.scss',
			    'css/editor-style.min.css': 'scss/editor-style.scss',
			    'css/email.min.css': 'scss/email.scss',
			    'css/gforms-datepicker.min.css': 'scss/gforms/gforms-datepicker.scss'
				}
      }
    },

    uglify: {
      options: {
	      sourceMap: true
      },
      mcFoundation: {
        files: [{'js/dist/mcFoundation-init.jquery.min.js': 'js/src/mcFoundation-init.jquery.js'}]//Should likely be all files in this dir
      }
    },
    
    jshint: {
	    all: ['Gruntfile.js', 'js/src/**/*.js']
    },

    watch: {
      grunt: {
	      files: ['Gruntfile.js']
	    },
      mcFoundation: {
	      files: ['js/src/**/*.js'],
	      tasks: ['jshint', 'uglify:mcFoundation']
      },
      sass: {
        files: 'scss/**/*.scss',
        tasks: ['sass']
      },
      php: {
	      files: ['**/**/*.php']//Consider adding grunt-phplint
      },
      options: {
	      livereload: true
      }
    }
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');

  grunt.registerTask('build', ['sass', 'uglify', 'jshint']);
  grunt.registerTask('default', ['watch']);
};
