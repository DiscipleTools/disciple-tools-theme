import { defineConfig } from 'vite';
import { resolve } from 'path';
import liveReload from 'vite-plugin-live-reload';
import legacy from '@vitejs/plugin-legacy';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
  root: '.',
  build: {
    minify: true,
    outDir: 'dt-assets/build-vite',
    rollupOptions: {
      input: {
        style: resolve(__dirname, 'dt-assets/scss/style.scss'),
        login: resolve(__dirname, 'dt-assets/scss/login.scss'),
        light: resolve(
          __dirname,
          'node_modules/@disciple.tools/web-components/src/styles/light.css',
        ),
        dim: resolve(
          __dirname,
          'node_modules/@disciple.tools/web-components/src/styles/dim.css',
        ),
        dark: resolve(
          __dirname,
          'node_modules/@disciple.tools/web-components/src/styles/dark.css',
        ),
        scripts: resolve(__dirname, 'dt-assets/js/main.js'),
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
    // Ensure we don't empty the dir if we are building to separate subdirs,
    // but here we use a single outDir with subdirs in naming.
    emptyOutDir: true,
  },
  plugins: [
    legacy({
      targets: ['defaults', 'not IE 11'],
    }),
    liveReload(['**/*.php']),
    viteStaticCopy({
      targets: [
        {
          src: 'node_modules/@disciple.tools/web-components/dist/index.*js',
          dest: 'components',
        },
      ],
    }),
  ],
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        includePaths: ['node_modules'],
      },
    },
    devSourcemap: true,
  },
  server: {
    proxy: {
      // Proxying is mentioned in specs but we don't have the local URL yet.
      // Defaulting to a placeholder or common local dev URL.
      // '/': 'http://local.discipletools/'
    },
  },
});
