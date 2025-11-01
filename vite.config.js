import { defineConfig } from 'vite';

export default defineConfig(({ mode }) => {
  const isDev = mode === 'development';

  return {
    publicDir: false,
    build: {
      outDir: 'public',
      emptyOutDir: false,
      manifest: false,
      minify: !isDev,
      sourcemap: isDev,
      rollupOptions: {
        input: {
          app: 'resources/js/app.js',
          styles: 'resources/scss/main.scss',
        },
        output: {
          entryFileNames: isDev ? 'admin/js/[name].js' : 'admin/js/[name].min.js',
          assetFileNames: isDev ? 'admin/css/[name].css' : 'admin/css/[name].min.css',
        },
      },
    },
  };
});
