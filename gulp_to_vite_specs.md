### Specification Document: Transitioning from Gulp to Vite

This document outlines the requirements and specifications for replacing the current Gulp-based build system with Vite in the `disciple-tools-theme` project. It provides an evaluation of the existing Gulp functionality and a roadmap for the Vite implementation.

---

### 1. Current Gulp Functionality Evaluation

The current `gulpfile.js` manages the compilation, bundling, and optimization of assets. Below is a detailed breakdown of its features:

#### A. JavaScript Processing (`scripts` task)
*   **Source Files**: Concatenates a specific sequence of files:
    *   `what-input.js`
    *   Foundation Sites core and selected plugins (Accordion, Tabs, Reveal, etc.).
    *   `dt-assets/js/footer-scripts.js` (Custom theme logic).
    *   `masonry-layout`.
*   **Transformations**:
    *   **Babel**: Transpiles ES6+ code to compatible JavaScript (ignoring `what-input.js`).
    *   **Concatenation**: Merges all sources into a single `scripts.js` file.
    *   **Uglify/Minification**: Compresses the code for production.
    *   **Source Maps**: Generates maps for debugging (currently commented out but supported).
    *   **Line Ending Normalization**: Uses `gulp-frep` to ensure `\n` line endings.
    *   **Renaming**: Outputs to `dt-assets/build/js/scripts.min.js`.

#### B. Style Processing (`styles` task)
*   **Source Files**:
    *   `dt-assets/scss/**/*.scss` (Main theme styles).
    *   `node_modules/@disciple.tools/web-components/src/styles/*` (Component-specific styles).
*   **Transformations**:
    *   **Sass Compilation**: Compiles SCSS to CSS using Dart Sass.
    *   **PostCSS & Autoprefixer**: Adds vendor prefixes for cross-browser compatibility.
    *   **CSSNano**: Minifies the resulting CSS.
    *   **Line Ending Normalization**: Ensures consistent `\n` line endings.
    *   **Touch**: Updates file timestamps to ensure cache busting/OS recognition.
    *   **Renaming**: Outputs to `dt-assets/build/css/style.min.css`.

#### C. Web Components Management (`components` task)
*   **Clean**: Deletes the `dt-assets/build/components/` directory before updates.
*   **Copy**: Syncs pre-built distribution files from `@disciple.tools/web-components` to the theme's build directory.

#### D. Development Workflow
*   **Watch Tasks**:
    *   Monitors SCSS and JS files for changes to trigger recompilation.
    *   Monitors PHP files to trigger browser reloads.
*   **BrowserSync**:
    *   Provides a proxy server for local development (e.g., `http://local.discipletools/`).
    *   Synchronizes scrolls, clicks, and form inputs across multiple devices.
    *   Supports a command-line argument `--option` to dynamically set the proxy URL.

---

### 2. Vite Implementation Specifications

The transition to Vite will modernize the stack, significantly reducing build times and improving the developer experience.

#### Technical Requirements
*   **Vite Version**: 6.x
*   **Plugins Needed**:
    *   `@vitejs/plugin-legacy` (for older browser support, replacing Gulp-Babel).
    *   `vite-plugin-live-reload` (to handle PHP file changes).
    *   `sass` (Vite handles this natively, but the compiler is required).
    *   `postcss` with `autoprefixer` and `cssnano` (configured via `postcss.config.js`).

#### Build Configuration (`vite.config.js`)
*   **Input Mapping**: Define entry points for `style.scss` and a main `main.js` (which will import the Foundation dependencies and `footer-scripts.js`).
*   **Output Directory**: Configure `build.outDir` to use sibling directories of the existing structure for result comparison:
    *   CSS → `dt-assets/build-vite/css/`
    *   JS → `dt-assets/build-vite/js/`
*   **Asset Handling**:
    *   Implement a "Copy" plugin or use Vite's `public` directory logic to handle the `components` task.
    *   Ensure the output filename matches `scripts.min.js` and `style.min.css` to maintain compatibility with WordPress `wp_enqueue_script/style` calls.

#### Development Server
*   **Proxy Mode**: Configure `server.proxy` to point to the local WordPress installation.
*   **HMR (Hot Module Replacement)**: Enable for CSS and JS to allow instant updates without full page refreshes.

---

### 3. Key Benefits of the Switch
1.  **Speed**: Vite uses `esbuild` for dependency pre-bundling, which is 10-100x faster than traditional bundlers.
2.  **Modern Standards**: Shifts the project toward native ES Modules.
3.  **Simplified Config**: Replaces ~270 lines of Gulp logic with a more declarative Vite configuration.
4.  **Better Development Experience**: Faster "cold starts" and nearly instantaneous HMR.

---

### 4. Migration Steps
1.  **Initialize Vite**: Install Vite and necessary plugins as `devDependencies`.
2.  **Create Entry Points**: Create a standard JS entry point that `imports` the SCSS and the required Foundation modules.
3.  **Port PostCSS Config**: Move `autoprefixer` and `cssnano` settings to a `postcss.config.js` file.
4.  **Update NPM Scripts**: Replace `gulp` commands in `package.json` with `vite` (dev) and `vite build` (prod).
5.  **Verify WordPress Integration**: Ensure the paths generated by Vite match where the WordPress theme expects them.
