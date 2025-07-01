require('dotenv').config();

/**
 * GULP PACKAGES
 */

// Most packages are lazy loaded
var gulp = require('gulp'),
  log = require('fancy-log'),
  browserSync = require('browser-sync'),
  plugin = require('gulp-load-plugins')(),
  sass = require('gulp-sass')(require('sass')),
  touch = require('gulp-touch-cmd'),
  rename = require('gulp-rename'),
  frep = require('gulp-frep'),
  postcss = require('gulp-postcss'),
  gulpif = require('gulp-if'),
  del = require('del'),
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
  styles: [
    'dt-assets/scss/**/*.scss',
    '!dt-assets/scss/mobile/**/*.scss', // Exclude mobile styles from main build
    'node_modules/@disciple.tools/web-components/src/styles/*',
  ],

  // Mobile-specific styles (Tailwind CSS included)
  mobileStyles: [
    'dt-assets/scss/mobile/tailwind.css',
    'dt-assets/scss/mobile/mobile.scss',
  ],

  // Phase 3 JavaScript files
  phase3Scripts: [
    'dt-assets/js/service-worker.js',
    'dt-assets/js/pwa-manager.js',
    'dt-assets/js/mobile-bulk-actions.js',
    'dt-assets/js/mobile-gesture-manager.js',
  ],

  otherjs: [
    'dt-assets/**/*.js',
    '!dt-assets/js/footer-scripts.js',
  ],

  components: 'node_modules/@disciple.tools/web-components/dist/index.*js',

  php: '**/*.php'
};

// Build output locations
const BUILD_DIRS = {
  styles: 'dt-assets/build/css/',
  scripts: 'dt-assets/build/js/',
  components: 'dt-assets/build/components/',
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
      // presets: ['@babel/preset-env'],
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
    .pipe(sass().on('error', sass.logError))
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

// Compile Mobile Sass with Tailwind CSS, Autoprefix and minify
gulp.task('styles:mobile', function () {
  const tailwindcss = require('tailwindcss');
  const autoprefixer = require('autoprefixer');
  
  return gulp.src(SOURCE.mobileStyles)
    .pipe(plugin.plumber(function (error) {
      log.error(error.message);
      this.emit('end');
    }))
    .pipe(plugin.sourcemaps.init())
    // Process SCSS files through Sass
    .pipe(gulpif('*.scss', sass().on('error', sass.logError)))
    // Process all files through PostCSS (including Tailwind)
    .pipe(postcss([
      tailwindcss('./tailwind.config.js'),
      autoprefixer({
        cascade: false
      }),
      cssnano()
    ]))
    .pipe(plugin.concat('mobile-styles.min.css'))
    // .pipe(plugin.sourcemaps.write('.'))
    .pipe(frep(patterns))
    .pipe(gulp.dest(BUILD_DIRS.styles))
    .pipe(touch());
});

// Clean out components directory before copying new files
gulp.task('components:clean', function () {
  return del([BUILD_DIRS.components]);
});
// Copy components to build directory
gulp.task('components:copy', function () {
  return gulp.src(SOURCE.components)
    .pipe(gulp.dest(BUILD_DIRS.components));
});
// clean & copy web components assets folder
gulp.task('components', gulp.series('components:clean', 'components:copy'));

// Run styles, mobile styles, scripts and components
gulp.task('default', gulp.parallel('styles', 'styles:mobile', 'scripts', 'components'));


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
  // Watch mobile scss files
  gulp.watch(SOURCE.mobileStyles, gulp.series('styles:mobile'));
  // Watch scripts files
  gulp.watch(SOURCE.scripts, gulp.series('scripts'));
});

// Watch mobile styles only | run "gulp watch:mobile" or "npm run watch:mobile"
gulp.task('watch:mobile', function () {
  gulp.watch(SOURCE.mobileStyles, gulp.series('styles:mobile'));
  gulp.watch('dt-assets/scss/mobile/**/*.scss', gulp.series('styles:mobile'));
});

// Watch for file changes with Browser-Sync | run "gulp browsersync" or "npm run browsersync"
gulp.task('watchWithBrowserSync', function () {
  // Watch .scss files
  gulp.watch(SOURCE.styles, gulp.series('styles', reload));
  // Watch mobile scss files
  gulp.watch(SOURCE.mobileStyles, gulp.series('styles:mobile', reload));
  gulp.watch('dt-assets/scss/mobile/**/*.scss', gulp.series('styles:mobile', reload));
  // Watch scripts files
  gulp.watch(SOURCE.scripts, gulp.series('scripts', reload));
  //Watch php files
  gulp.watch(SOURCE.php, gulp.series(reload));
  //Watch other JavaScript files
  gulp.watch(SOURCE.otherjs, gulp.series(reload));
});

// Launch the development environment with Browser-Sync
gulp.task('browsersync', gulp.series(gulp.parallel('styles', 'styles:mobile', 'scripts'), serve, 'watchWithBrowserSync'));

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
