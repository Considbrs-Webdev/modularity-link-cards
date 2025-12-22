import { createViteConfig } from "vite-config-factory";

const entries = {
    'css/modularity-link-cards': './source/sass/modularity-link-cards.scss',
};

export default createViteConfig(entries, {
    outDir: "assets/dist",
    manifestFile: "manifest.json",
});
