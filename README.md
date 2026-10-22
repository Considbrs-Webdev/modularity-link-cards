# Modularity LinkCards

A Modularity module that shows a grid of clickable link cards with icons.

## Requirements

- PHP 8.0 or newer. The source uses PHP 8.0 syntax. `composer.json` does not declare a PHP version.
- Composer
- Node.js `^20.19.0 || >=22.12.0` (required by Vite 7). `package.json` does not declare this.
- npm
- A WordPress site that provides Modularity, Advanced Custom Fields, and AcfExportManager. This package does not install them.

## Getting Started

1. Clone this repository
2. Run `composer install` to install PHP dependencies
3. Run `npm install` to install Node dependencies
4. Run `npm run build` to build assets

## Development

- `npm run dev` - Start the Vite development server
- `npm run watch` - Watch for changes and rebuild
- `npm run build` - Build for production

There is no test, lint, or format command.

## Release

Releases are semantic versions, tagged without a `v` prefix and published as GitHub releases. Development happens on `dev` and is merged into `main` with a fast-forward. Details are in `AGENTS.md`.

## Structure

```
├── modularity-link-cards.php  # Main plugin file
├── source/
│   ├── php/
│   │   ├── App.php             # Application bootstrap
│   │   ├── AcfFields/          # ACF field definitions
│   │   ├── Helper/             # Asset filename lookup
│   │   └── Module/             # Module classes
│   │       ├── LinkCards.php   # Main module class
│   │       ├── assets/         # Module assets (icons)
│   │       └── views/          # Blade templates
│   ├── sass/                   # SCSS files
│   └── ts/                     # TypeScript for the color theme field
├── assets/                     # Compiled assets (generated, gitignored)
└── languages/                  # Translation files
```

## AI instructions

Instructions for AI tools are in `AGENTS.md`.

## License

MIT
