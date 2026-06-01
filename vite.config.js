import { defineConfig } from 'vite';
/** Builds the optional CMS Vue island without turning public pages into an SPA. */
export default defineConfig({build:{lib:{entry:'resources/js/website-menu-manager.js',formats:['es'],fileName:()=> 'website-menu-manager.js'},outDir:'public/assets/js',emptyOutDir:false}});
