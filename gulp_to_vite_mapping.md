### Gulp to Vite Feature Mapping

This document maps the features of the legacy Gulp build system to the new Vite-based implementation for the `disciple-tools-theme` project.

| Feature | Gulp Task / Implementation | Vite Implementation |
| :--- | :--- | :--- |
| **Sass Compilation** | `gulp-sass` (Dart Sass) | Native Sass support (via `sass` compiler) |
| **PostCSS / Autoprefixer** | `gulp-postcss` with `autoprefixer` | Configured via `postcss.config.js` |
| **Minification (CSS)** | `cssnano` | Built-in via `esbuild` |
| **Minification (JS)** | `gulp-uglify` | Built-in via `esbuild` |
| **JS Transpilation** | `gulp-babel` | `@vitejs/plugin-legacy` (Babel-based) |
| **JS Concatenation** | `gulp-concat` | ES Module `import` in `dt-assets/js/main.js` |
| **Asset Copying** | `gulp.src().pipe(gulp.dest())` | `vite-plugin-static-copy` |
| **Watch / Live Reload** | `gulp.watch` + `browser-sync` | Native Vite HMR + `vite-plugin-live-reload` (PHP) |
| **Output Directory** | `dt-assets/build/` | `dt-assets/build-vite/` |
| **Output Filenames** | `scripts.min.js`, `style.min.css` | Same filenames configured in `vite.config.js` (including sourcemaps) |
| **Wildcards (Globs)** | Used in `SOURCE` for scss and components | Explicitly defined in `vite.config.js` |
| **Line Endings** | `gulp-frep` (normalization to `\n`) | Standardized by modern build tools / Git |
| **Script Generation** | Generates only `scripts.min.js` | Custom plugin in `vite.config.js` filters out redundant JS files from CSS entry points |

### Output Comparison (Minified)

| Asset | Gulp Size | Vite Size | Notes |
| :--- | :--- | :--- | :--- |
| `style.min.css` | 274.28 KB | 274.23 KB | Virtually identical. Minor differences due to different minifier implementations.                                                                            |
| `login.min.css` | 35 B | 36 B | Matches (whitespace difference).                                                                                                                             |
| `light.min.css` | 3.46 KB | 3.45 KB | Matches.                                                                                                                                                     |
| `dim.min.css` | 4.49 KB | 4.49 KB | Matches.                                                                                                                                                     |
| `dark.min.css` | 4.44 KB | 4.44 KB | Matches.                                                                                                                                                     |
| `scripts.min.js` | 245.42 KB | 336.69 KB | Vite bundle is larger because it includes ESM overhead, polyfills (via legacy plugin), and properly resolves dependencies instead of simple concatenation.   |

### Unminified Comparison (Script Logic Verification)

| Asset | Gulp Size (Unminified) | Vite Size (Unminified) | Notes |
| :--- | :--- | :--- | :--- |
| `scripts.min.js` | 623.34 KB | 1071.63 KB | Both contain identical custom theme logic and Foundation dependencies. Vite's increased size is due to ESM bundling and `plugin-legacy` polyfills. |

### Key Changes
1.  **Entry Point**: Instead of defining a list of files in `gulpfile.js`, we now use `dt-assets/js/main.js` as the central entry point which imports both JavaScript dependencies and the main SCSS file.
2.  **Modern JS**: Vite treats JavaScript as ES Modules, which is more efficient for development and modern browsers.
3.  **Legacy Support**: The `@vitejs/plugin-legacy` ensures that older browsers still receive compatible bundles.
4.  **Static Assets**: Web components are now copied using `vite-plugin-static-copy` during the build process, equivalent to the `components` task in Gulp.
