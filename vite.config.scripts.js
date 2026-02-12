import { defineConfig } from 'vite';

// Although WP has wp_enqueue_script_module to be able to load an ES scripts.min.js directly as a js module,
// WP doesn't let module scripts depend on classic scripts (scripts.min.js depending on jquery) or
// vice versa (modular-list.js depending on scripts.min.js). So at least for now, we need to depend on
// building a legacy IIFE script file that many wp scripts depend on via the 'site-js' handle.
// This doesn't seem possible alongside the processing of SCSS, so we're using this separate Vite build
// to handle just the bundling of the scripts.
export default defineConfig(({ mode }) => {
  return {
    root: '.',
    build: {
      outDir: 'dt-assets/build/js',
      emptyOutDir: true,
      minify: true,
      sourcemap: true,

      rollupOptions: {
        input: {
          scripts: 'dt-assets/js/main.js',
        },
        // Externalize jQuery since WordPress provides it
        external: ['jquery'],
        output: {
          format: 'iife',
          globals: {
            jquery: 'jQuery',
          },
          // Remove the hashes from the filenames for WordPress predictability
          entryFileNames: '[name].min.js',
          chunkFileNames: '[name]-[hash].js',
          assetFileNames: '../assets/[name].[ext]',
        },
      },
    },
  };
});
