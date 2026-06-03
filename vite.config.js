import { defineConfig } from 'vite';
/** Builds optional Vue islands without turning public pages into an SPA. */
export default defineConfig({build:{rollupOptions:{input:{'website-menu-manager':'resources/js/website-menu-manager.js','applicant-draft':'resources/js/applicant-draft.js'},output:{entryFileNames:'[name].js'}},outDir:'public/assets/js',emptyOutDir:false}});
