import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { nodePolyfills } from 'vite-plugin-node-polyfills';
import { env } from 'node:process';
import * as dotenv from 'dotenv';
import dotenvExpand from 'dotenv-expand';
import mkcert from 'vite-plugin-mkcert';
// import dns from 'node:dns';
// dns.setDefaultResultOrder('verbatim')
// import RubyPlugin from 'vite-plugin-ruby';
// import inject from "@rollup/plugin-inject";

let myenv = dotenv.config();
dotenvExpand.expand(myenv);

export default defineConfig({
  optimizeDeps: {
    exclude: ['js-big-decimal'] // agar mitt bisa dipakai. See app.js csdb3
  },
  server: {
    // port: process.env.VITE_PUSHER_PORT,
    port: 446,
    host: process.env.VITE_PUSHER_HOST,
    // host: "192.168.11.224",
    https: true
  },
  plugins: [
    mkcert(),
    nodePolyfills(), // supaya tidak ada warning yang muncul di console, eg. Module "stream" has been externalized for browser compatibility. Cannot access "stream.Readable" in client code.
    // inject({   // => that should be first under plugins array
    //   $: 'jquery',
    //   jQuery: 'jquery',
    // }),
    // RubyPlugin(),
    laravel({
      input: [
        // 'resources/js/csdb/detail.js',
        'resources/css/app.css',
        // 'resources/css/loadingbar.css',
        // 'resources/css/dmodule.css',
        // 'resources/js/csdb/CsdbReader.js',
        // 'resources/js/ietm/app.js',
        // 'resources/views/**/*.vue',
        // 'resources/views/**/*.js',
        'resources/js/csdb/app.js',
        // 'resources/js/csdb4/worker.js',
        // 'resources/js/csdb4/tes.js',
        
        // 'resources/js/bootstrap.js',
        // 'resources/scss/style.scss',
        // 'resources/css/app2.css',
        'resources/js/alert.js',
        // 'resources/css/csdb.css',
      ],
      buildDirectory: process.env.VITE_BUILD_DIR,
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
            base: null,
            includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
      'vue': 'vue/dist/vue.esm-bundler.js',
    }
  },
});