import { createViteConfig } from "vite-config-factory";

const entries = {
    'css/modularity-link-cards': './source/sass/modularity-link-cards.scss',
    'css/color-theme-field':     './source/sass/color-theme-field.scss',
    'js/color-theme-field':      './source/ts/color-theme-field.ts',
};

export default createViteConfig(entries, {
    outDir: "assets/dist",
    manifestFile: "manifest.json",
});
