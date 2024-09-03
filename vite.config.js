// vite.config.js
const path = require('path')
const { defineConfig } = require('vite')
import vue from '@vitejs/plugin-vue'
import { viteExternalsPlugin } from 'vite-plugin-externals'

module.exports = defineConfig({
  build: {
    lib: {
      entry: path.resolve(__dirname, 'Resources/scripts/module.js'),
      name: 'PaymentsLib',
      fileName: (format) => `payments.${format}.js`,
    },
    outDir: './dist',
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, '../../resources'),
      '~': path.resolve(__dirname, 'Resources'),
    },
  },
  plugins: [
    vue(),
    viteExternalsPlugin({
      vue: 'Vue',
    }),
  ],
})
