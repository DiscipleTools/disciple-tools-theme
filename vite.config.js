import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import { v4wp } from '@kucrut/vite-for-wp';
import basicSsl from '@vitejs/plugin-basic-ssl';

export default defineConfig(({ mode }) => {
  return {
    root: '.',
    build: {
      manifest: true,
      minify: true,
      outDir: 'dt-assets/build',
      sourcemap: true,
      rollupOptions: {
        input: {
          style: 'dt-assets/scss/style.scss',
          login: 'dt-assets/scss/login.scss',
          light:
            'node_modules/@disciple.tools/web-components/src/styles/light.css',
          dim: 'node_modules/@disciple.tools/web-components/src/styles/dim.css',
          dark: 'node_modules/@disciple.tools/web-components/src/styles/dark.css',
        },
        output: {
          entryFileNames: 'js/[name].min.js',
          chunkFileNames: 'js/[name]-[hash].js',
          assetFileNames: (assetInfo) => {
            if (assetInfo.name && assetInfo.name.endsWith('.css')) {
              const name = assetInfo.name.replace(/\.css$/, '');
              return `css/${name}.min.css`;
            }
            return 'assets/[name]-[hash][extname]';
          },
        },
      },
      emptyOutDir: false,
    },
    plugins: [
      basicSsl(),
      {
        name: 'cleanup-scripts',
        generateBundle(_, bundle) {
          for (const fileName in bundle) {
            if (
              fileName.startsWith('js/') &&
              (fileName.endsWith('.js') || fileName.endsWith('.js.map'))
            ) {
              const isMap = fileName.endsWith('.map');
              const baseName = isMap
                ? fileName.slice(3, -4)
                : fileName.slice(3);
              // We want to keep anything that is part of the 'scripts' entry
              // entryFileNames: 'js/[name].min.js' -> scripts.min.js
              // polyfills-legacy: 'js/polyfills-legacy.min.js'
              // scripts-legacy: 'js/scripts-legacy.min.js'
              if (
                !baseName.startsWith('scripts.') &&
                !baseName.startsWith('scripts-legacy.') &&
                !baseName.startsWith('polyfills-legacy.')
              ) {
                delete bundle[fileName];
              }
            }
          }
        },
      },
      viteStaticCopy({
        targets: [
          {
            src: 'node_modules/@disciple.tools/web-components/dist/index.*js',
            dest: 'components',
          },
        ],
      }),
      v4wp({
        input: {
          // siteJs: 'dt-assets/js/main.js',
          siteCss: 'dt-assets/scss/style.scss',
          // login: 'dt-assets/scss/login.scss',
        },
        outDir: 'dt-assets/build',
      }),
    ],
    css: {
      preprocessorOptions: {
        scss: {
          api: 'modern-compiler',
          includePaths: ['node_modules'],
          quietDeps: true, // This and below silences deprecation warnings from node_modules, particularly foundation-sites
          silenceDeprecations: [
            'import',
            'global-builtin',
            'if-function',
            'color-functions',
            'abs-percent',
          ],
        },
      },
      devSourcemap: true,
    },
    server: {
      https: true,
      cors: true,
      strictPort: true,
      port: 5173,
      host: 'localhost',
      hmr: {
        host: 'localhost',
      },
    },
  };
});
