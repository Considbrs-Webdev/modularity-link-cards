# AGENTS.md

Instructions for AI tools working in this repository. Read this first.

## Project

Modularity LinkCards is a WordPress plugin that registers a Modularity module: a grid of clickable link cards with icons. It is PHP 8, Blade, SCSS, and TypeScript, built with Vite. The GitHub repository is `Considbrs-Webdev/modularity-link-cards`. Composer and npm package names stay lowercase (`considbrs-webdev`), which those registries require.

The plugin does not boot WordPress. Modularity, Advanced Custom Fields, and AcfExportManager come from the site that loads the plugin. `composer.json` does not require them. It suggests `helsingborg-stad/municipio` `^6.0.0`.

## Setup and commands

| Task | Command |
| --- | --- |
| Install PHP dependencies | `composer install` |
| Install Node dependencies | `npm install` |
| Build assets | `npm run build` |
| Rebuild while editing | `npm run watch` |
| Vite dev server | `npm run dev` |

There is no test, lint, or format command. Do not invent one, and do not report tests as run.

`composer.json` does not declare a PHP version. The source uses PHP 8.0 syntax (`mixed`, `str_starts_with`). `package.json` does not declare a Node version. Vite 7 requires Node `^20.19.0 || >=22.12.0`.

## Structure

- `modularity-link-cards.php` — plugin bootstrap, constants, text domain, ACF import, Modularity view path.
- `source/php/App.php` — registers the module and enqueues the stylesheet.
- `source/php/Module/LinkCards.php` — module class. Normalises ACF data for the view.
- `source/php/Module/views/` — Blade templates. `link-cards.blade.php` and `partials/card.blade.php`.
- `source/php/AcfFields/` — ACF export (`json/`, `php/`) and the custom color theme field.
- `source/php/Helper/CacheBust.php` — hashed filenames from the Vite manifest.
- `source/sass/` — SCSS. `source/ts/` — the color theme field script.
- `languages/` — `modularity-link-cards.pot` and Swedish `sv_SE` translations. `.mo` files are compiled from the `.po` and are committed.
- `assets/dist/` — Vite output, including `manifest.json`. Gitignored. Do not hand-edit it.
- `node_modules/` and `vendor/` — installed dependencies. Do not hand-edit them.

## Conventions

- Follow `docs/CODING_STANDARDS.md` for new and changed code. Those rules are proposals until the team confirms them.
- Do not refactor unrelated code. Deviations in older code are not a reason to rewrite it.
- Comments explain why, briefly. Never refer to documents from code comments; restate the relevant information in the comment instead.
- There is no automated test suite. Do not claim that tests passed.

## Release

Releases follow the shared release skill: semantic version, changelog, annotated tag, and a GitHub release.

- Development branch: `dev`. Release branch: `main`. Push `dev`, then merge it into `main` with `--ff-only`.
- Tag style: `1.0.0` (no `v` prefix). Annotated tags.
- The release commit message is `Release X.Y.Z`. Other commit messages are sentence-case descriptions.
- Version is declared in `modularity-link-cards.php` (`Version:`) and `package.json` (`version`). Update both together, and `package-lock.json` when npm does. `composer.json` has no version field.
- There is no test or lint command. Verify with `composer install` and `npm run build`.
- `build.php` is a packaging script (`composer dump-autoload`, `npm ci`, `npm run build`, and with `--cleanup` it deletes dev files). It is not the versioned release. `--release` keeps `.git` during that cleanup.

## Security and data

- Secrets come from environment variables or the project's secret management. Never read, print or commit their values, and never put them in instructions, code or logs. This repository has no `.env` file.
- Personal, customer and other sensitive data may only be used when approved by the customer and project owner. Use synthetic or anonymized data where possible.
- Project-specific restrictions: do not put customer content, personal data, or production content into prompts, commits, or docs.

## Working with AI

- The developer is responsible for understanding, reviewing and verifying everything AI produces.
- If a guard or permission blocks an action, do not work around it. Report it and let the developer decide.
- If these instructions cause unnecessary steps or unwanted behavior, tell the developer so this file can be improved.
