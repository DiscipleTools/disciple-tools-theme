require('dotenv').config();

// GULP PACKAGES
// Most packages are lazy loaded
var gulp  = require('gulp'),
  gutil = require('gulp-util'),
  browserSync = require('browser-sync').create(),
  filter = require('gulp-filter'),
  plugin = require('gulp-load-plugins')(),
  rename = require('gulp-rename'),
  merge = require('merge-stream');


// GULP VARIABLES
// Modify these variables to match your project needs

// Set local URL if using Browser-Sync
const LOCAL_URL = process.env.BROWSERSYNC_PROXIED_SITE || 'http://jointswp-github.dev/';

// Set path to Foundation files
const FOUNDATION = 'node_modules/foundation-sites';

// Select Foundation components, remove components project will not use
const SOURCE = {
  scripts: [
    // Lets grab what-input first
    'node_modules/what-input/dist/what-input.js',

    // Foundation core - needed if you want to use any of the components below
    FOUNDATION + '/dist/js/plugins/foundation.core.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.*.js',

    // Pick the components you need in your project
    FOUNDATION + '/dist/js/plugins/foundation.abide.js',
    FOUNDATION + '/dist/js/plugins/foundation.accordion.js',
    FOUNDATION + '/dist/js/plugins/foundation.accordionMenu.js',
    FOUNDATION + '/dist/js/plugins/foundation.drilldown.js',
    FOUNDATION + '/dist/js/plugins/foundation.dropdown.js',
    FOUNDATION + '/dist/js/plugins/foundation.dropdownMenu.js',
    FOUNDATION + '/dist/js/plugins/foundation.equalizer.js',
    FOUNDATION + '/dist/js/plugins/foundation.interchange.js',
    FOUNDATION + '/dist/js/plugins/foundation.magellan.js',
    FOUNDATION + '/dist/js/plugins/foundation.offcanvas.js',
    FOUNDATION + '/dist/js/plugins/foundation.orbit.js',
    FOUNDATION + '/dist/js/plugins/foundation.responsiveAccordionTabs.js',
    FOUNDATION + '/dist/js/plugins/foundation.responsiveMenu.js',
    FOUNDATION + '/dist/js/plugins/foundation.responsiveToggle.js',
    FOUNDATION + '/dist/js/plugins/foundation.reveal.js',
    FOUNDATION + '/dist/js/plugins/foundation.slider.js',
    FOUNDATION + '/dist/js/plugins/foundation.smoothScroll.js',
    FOUNDATION + '/dist/js/plugins/foundation.sticky.js',
    FOUNDATION + '/dist/js/plugins/foundation.tabs.js',
    FOUNDATION + '/dist/js/plugins/foundation.toggler.js',
    FOUNDATION + '/dist/js/plugins/foundation.tooltip.js',

    // Place custom JS here, files will be concantonated, minified if ran with --production
    'assets/js/footer-scripts.js',
  ],

  // Scss files will be concantonated, minified if ran with --production
  styles: 'assets/scss/**/*.scss',

  php: '**/*.php'
};

const BUILD_DIRS = {
  styles: 'build/css/',
  scripts: 'build/js/',
};

// GULP FUNCTIONS
// concat, and minify JavaScript
gulp.task('scripts', function() {

  return gulp.src(SOURCE.scripts)
    .pipe(plugin.plumber(function(error) {
      gutil.log(gutil.colors.red(error.message));
      this.emit('end');
    }))
    .pipe(plugin.sourcemaps.init())
    .pipe(plugin.babel({
      presets: ['es2015'],
      compact: true,
      ignore: ['what-input.js']
    }))
    .pipe(plugin.concat('scripts.js'))
    .pipe(plugin.uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(plugin.sourcemaps.write('.')) // Creates sourcemap for minified JS
    .pipe(gulp.dest(BUILD_DIRS.scripts))
});

// Compile Sass, Autoprefix and minify
gulp.task('styles', function() {
  return gulp.src(SOURCE.styles)
    .pipe(plugin.plumber(function(error) {
      gutil.log(gutil.colors.red(error.message));
      this.emit('end');
    }))
    .pipe(plugin.sourcemaps.init())
    .pipe(plugin.sass())
    .pipe(plugin.autoprefixer({
      browsers: ['last 2 versions'],
      cascade: false
    }))
    .pipe(rename({suffix: '.min'}))
    .pipe(plugin.cssnano())
    .pipe(plugin.sourcemaps.write('.'))
    .pipe(gulp.dest(BUILD_DIRS.styles))
    .pipe(browserSync.reload({
      stream: true
    }));
});

// Browser-Sync watch files and inject changes
gulp.task('browsersync', ['default'], function() {

  // Watch these files
  var files = [
    SOURCE.styles,
    SOURCE.scripts,
    SOURCE.php,
    'assets/**/*.js', // Some .js aren't in SOURCE.scripts
  ];

  browserSync.init(files, {
    proxy: LOCAL_URL,
  });

  gulp.watch(SOURCE.styles, ['styles']);
  gulp.watch(SOURCE.scripts, ['scripts']).on('change', browserSync.reload);

});

// Watch files for changes (without Browser-Sync)
gulp.task('watch', function() {

  // Watch .scss files
  gulp.watch(SOURCE.styles, ['styles']);

  // Watch scripts files
  gulp.watch(SOURCE.scripts, ['scripts']);

});

// Run styles, scripts and foundation-js
gulp.task('default', ['styles', 'scripts']);
