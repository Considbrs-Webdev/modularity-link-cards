import { createViteConfig } from "vite-config-factory";

const entries = {
    'css/modularity-boilerplate': './source/sass/modularity-boilerplate.scss',
};

export default createViteConfig(entries, {
    outDir: "assets/dist",
    manifestFile: "manifest.json",
});
