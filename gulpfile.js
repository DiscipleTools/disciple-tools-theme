require('dotenv').config();

/**
 * GULP PACKAGES
 */

// Most packages are lazy loaded
var gulp = require('gulp'),
  log = require('fancy-log');
  browserSync = require('browser-sync'),
  plugin = require('gulp-load-plugins')(),
  touch = require('gulp-touch-cmd'),
  rename = require('gulp-rename'),
  frep = require('gulp-frep'),
  merge = require('merge-stream'),
  postcss = require('gulp-postcss'),
  cssnano = require('cssnano');

/**
 * DEFINE GULP VARIABLE VALUES TO MATCH YOUR PROJECT NEEDS
 */

//Run "npm  install" to get the node_modules directory, and Set path below to Foundation files
const FOUNDATION = 'node_modules/foundation-sites';

// Select Foundation components, remove components project will not use
const SOURCE = {
  scripts: [
    // Lets grab what-input first
    'node_modules/what-input/dist/what-input.js',

    // Foundation core - needed if you want to use any of the components below
    FOUNDATION + '/dist/js/plugins/foundation.core.js',
    // FOUNDATION + '/dist/js/plugins/foundation.util.*.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.keyboard.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.nest.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.mediaQuery.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.triggers.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.box.js',
    FOUNDATION + '/dist/js/plugins/foundation.util.touch.js',

    // Pick the components you need in your project
    // FOUNDATION + '/dist/js/plugins/foundation.abide.js',
    FOUNDATION + '/dist/js/plugins/foundation.accordion.js',
    FOUNDATION + '/dist/js/plugins/foundation.accordionMenu.js',
    FOUNDATION + '/dist/js/plugins/foundation.drilldown.js',
    FOUNDATION + '/dist/js/plugins/foundation.dropdown.js',
    FOUNDATION + '/dist/js/plugins/foundation.dropdownMenu.js',
    FOUNDATION + '/dist/js/plugins/foundation.equalizer.js',
    // FOUNDATION + '/dist/js/plugins/foundation.interchange.js',
    FOUNDATION + '/dist/js/plugins/foundation.magellan.js',
    FOUNDATION + '/dist/js/plugins/foundation.offcanvas.js',
    // FOUNDATION + '/dist/js/plugins/foundation.orbit.js',
    FOUNDATION + '/dist/js/plugins/foundation.tabs.js',
    FOUNDATION + '/dist/js/plugins/foundation.responsiveAccordionTabs.js',
    FOUNDATION + '/dist/js/plugins/foundation.responsiveMenu.js',
    // FOUNDATION + '/dist/js/plugins/foundation.responsiveToggle.js',
    FOUNDATION + '/dist/js/plugins/foundation.reveal.js',
    // FOUNDATION + '/dist/js/plugins/foundation.slider.js',
    FOUNDATION + '/dist/js/plugins/foundation.smoothScroll.js',
    FOUNDATION + '/dist/js/plugins/foundation.sticky.js',
    FOUNDATION + '/dist/js/plugins/foundation.toggler.js',
    // FOUNDATION + '/dist/js/plugins/foundation.tooltip.js',

    // Please place all custom JS scripts within 'dt-assets/js/footer-scrips.js

    'dt-assets/js/footer-scripts.js',

    'node_modules/masonry-layout/dist/masonry.pkgd.js'
  ],

  // Scss files will be concantonated, minified if ran with --production
  styles: 'dt-assets/scss/**/*.scss',

  otherjs: [
    'dt-assets/**/*.js',
    '!dt-assets/js/footer-scripts.js',
  ],

  php: '**/*.php'
};

// Build output locations
const BUILD_DIRS = {
  styles: 'dt-assets/build/css/',
  scripts: 'dt-assets/build/js/',
};

const patterns = [
  {
    // normalize line endings
    pattern: /\\r\\n/g,
    replacement: '\\n'
  }
];

// GULP FUNCTIONS
// concat, and minify JavaScript
gulp.task('scripts', function () {

  return gulp.src(SOURCE.scripts)
    .pipe(plugin.plumber(function (error) {
      log.error(error.message);
      this.emit('end');
    }))
    .pipe(plugin.sourcemaps.init())
    .pipe(plugin.babel({
      presets: ['env'],
      compact: true,
      ignore: ['what-input.js']
    }))
    .pipe(plugin.concat('scripts.js'))
    .pipe(plugin.uglify())
    .pipe(rename({ suffix: '.min' }))
    // .pipe(plugin.sourcemaps.write('.')) // Creates sourcemap for minified JS
    .pipe(frep(patterns))
    .pipe(gulp.dest(BUILD_DIRS.scripts));
});

// Compile Sass, Autoprefix and minify
gulp.task('styles', function () {
  return gulp.src(SOURCE.styles)
    .pipe(plugin.plumber(function (error) {
      log.error(error.message);
      this.emit('end');
    }))
    .pipe(plugin.sourcemaps.init())
    .pipe(plugin.sass())
    .pipe(plugin.autoprefixer({
      cascade: false
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(postcss([cssnano()]))
    // .pipe(plugin.sourcemaps.write('.'))
    .pipe(frep(patterns))
    .pipe(gulp.dest(BUILD_DIRS.styles))
    .pipe(touch());
});

// Run styles, scripts and foundation-js
gulp.task('default', gulp.parallel('styles', 'scripts'));


/**
 * MANAGE WATCH AND RELOADING OPTIONS BELOW
 * NOTE! - Please set your local URL host here if you plan on using Browser-sync
 * example:
 * const LOCAL_URL = process.env.BROWSERSYNC_PROXIED_SITE || 'http://local.discipletools/';
 */

 const LOCAL_URL = process.env.BROWSERSYNC_PROXIED_SITE || ' ';

// Initialize Browser-sync
var server = browserSync.create();

// Initializing proxy for Browser-sync
function serve(done) {
  server.init({
    proxy: LOCAL_URL,
    notify: false,
    reloadDebounce: 2000,
    //reloadDelay: 250,
    //injectChanges: true,
    //reloadOnRestart: false,
  });
  done();
}

// A helpder function to reload with Browser-sync
function reload(done) {
  server.reload();
  done();
}

// Watch for file changes without Browser-Sync | run "gulp watch" or "npm run watch"
gulp.task('watch', function () {
  // Watch .scss files
  gulp.watch(SOURCE.styles, gulp.series('styles'));
  // Watch scripts files
  gulp.watch(SOURCE.scripts, gulp.series('scripts'));
});

// Watch for file changes with Browser-Sync | run "gulp browsersync" or "npm run browsersync"
gulp.task('watchWithBrowserSync', function () {
  // Watch .scss files
  gulp.watch(SOURCE.styles, gulp.series('styles', reload));
  // Watch scripts files
  gulp.watch(SOURCE.scripts, gulp.series('scripts', reload));
  //Watch php files
  gulp.watch(SOURCE.php, gulp.series(reload));
  //Watch other JavaScript files
  gulp.watch(SOURCE.otherjs, gulp.series(reload));
});

// Launch the development environemnt with Browser-Sync
gulp.task('browsersync', gulp.series(gulp.parallel('styles', 'scripts'), serve, 'watchWithBrowserSync'));

/**
 * OPTIONAL - USE THE FOLLOWING TASK TO RUN BROWSER-SYNC WITH A PROXY ARGUMENT FROM THE COMMAND LINE.
 * We're taking advantage of node's [process.argv] to reference arguments from the command line.
 * The beauty of this approach is that we don't have in to include any extra dependencies.
 * https://stackoverflow.com/a/32937333/957186
 * https://www.browsersync.io/docs/gulp
 * https://stackoverflow.com/a/38241262/957186
 * example:
 * gulp browsersync-p --option http://local.discipletools/
 *
 */

gulp.task('browsersync-p', function (done) {

  //get the --option argument value
  var option,
    i = process.argv.indexOf("--option");
  if (i > -1) {
    option = process.argv[i + 1];
  }

  // Initializing Browser-sync
  var serverp = browserSync.create();

  // Initialize proxy for Browser-sync
   serverp.init({
    proxy: process.env.BROWSERSYNC_PROXIED_SITE || option,
    notify: false,
    reloadDebounce: 2000,
    //reloadDelay: 250,
    //injectChanges: true,
    //reloadOnRestart: false,
  });

  // Helper function for Browser-sync
  function reload(done) {
    serverp.reload();
    done();
  }

   // Watch .scss files
   gulp.watch(SOURCE.styles, gulp.series('styles', reload));
   // Watch scripts files
   gulp.watch(SOURCE.scripts, gulp.series('scripts', reload));
   // Watch php files
   gulp.watch(SOURCE.php, gulp.series(reload));
   // Watch other JavaScript files
   gulp.watch(SOURCE.otherjs, gulp.series(reload));

  done();
});
