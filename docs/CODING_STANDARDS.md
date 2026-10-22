# Coding standards

Applies to new and changed code. Deviations in older code are not by themselves a reason to
rewrite it. Each rule is marked as enforced by tooling or as an observed pattern the team has
chosen to keep.

These rules are a proposal. Nothing in this repository enforces them in CI. The team decides
what stays.

## Language and tooling

- PHP source uses PHP 8.0 syntax (`mixed`, `str_starts_with`). `composer.json` does not declare a PHP version. Observed pattern.
- Node dependencies are installed with npm. Vite 7 requires Node `^20.19.0 || >=22.12.0`. `package.json` does not declare an `engines` field. Observed pattern.
- Front-end assets are built with Vite (`npm run build`) into `assets/dist/`. Entry points are in `vite.config.mjs`. Enforced by the build, not by CI.
- TypeScript `strict` is set in `tsconfig.json`. No npm script or CI job runs the type checker. Observed pattern.
- There is no ESLint, Prettier, PHPCS, PHPUnit, EditorConfig, or CI workflow. VS Code settings name Biome and `vendor/bin/mago format`. Neither Biome nor Mago is installed. Do not treat those settings as active rules.
- Text domain is `modularity-link-cards`. User-facing strings go through WordPress translation functions. Observed pattern.

## Naming and structure

- PHP namespace is `ModularityLinkCards\`, mapped to `source/php/` in `composer.json`. Class names are PascalCase, methods camelCase. Observed pattern.
- The module slug is `link-cards`. ACF field names are snake_case. Observed pattern.
- SCSS uses BEM under `.mod-link-cards`, with 2-space indentation. Observed pattern.
- TypeScript uses 2-space indentation. Hand-written PHP uses 4-space indentation. The ACF PHP export in `source/php/AcfFields/php/` does not follow that indentation. Observed pattern.
- `declare(strict_types=1);` is present on `LinkCards` and `ColorThemeField`, and absent on the plugin bootstrap, `App`, and `CacheBust`. Observed pattern.
- Blade views use Municipio component directives such as `@typography` and `@icon`. Observed pattern.

## Error handling

- Unexpected ACF values are normalised to safe defaults (empty cards, a string column count, default colours). The module does not throw for a missing or oddly typed repeater. Observed pattern.
- Missing Vite manifest entries skip the stylesheet enqueue instead of printing a broken URL. Observed pattern.

## Testing

No automated tests exist, and there is no test command. A change is not covered by a test run in this repository.

## Security

- `modularity-link-cards.php` stops direct access when `WPINC` is not defined. Observed pattern.
- Blade `{{ }}` escapes text, including values written into the card `style` attribute. Links that open a new tab set `rel="noopener noreferrer"`. Observed pattern.
- Colour values are checked for a string and a leading `#`. They are not checked as hex before they are printed in CSS. Observed pattern.
- Do not read, print, or commit secrets. Do not put customer content, personal data, or production content into prompts, commits, or docs.

## Dependencies

- PHP runtime libraries (Modularity, Advanced Custom Fields, AcfExportManager) are provided by the host site. They are not Composer requirements of this package. Observed pattern.
- npm packages are dev dependencies. Update `package.json` through npm so `package-lock.json` stays in sync. Do not hand-edit the lock file. Observed pattern.
- `composer.lock` is committed. It currently lists no packages.

## Git and reviews

- Integration branch is `dev`. Release branch is `main`. Feature branches use names such as `feat/...` and `fix/...`, and land through pull requests. Observed pattern.
- Recent commit messages are sentence-case descriptions. The release commit message is `Release X.Y.Z`. Observed pattern for the release commit; the sentence style is an observed pattern.
- There is no CI. Review is the check.

## Proposed improvements

These are not rules. Older code does not need to be rewritten for them.

- Apply `declare(strict_types=1);` on the PHP files that do not have it, if the team wants that to be the standard.
- `LinkCards::LEGACY_COLOR_THEMES` is never read. The comment says it maps old stored colour strings. Either use it for that fallback or remove it.
- Decide whether Biome, Mago, and a test runner should actually be installed. The editor settings already name the first two.
- Validate colour strings as hex before they are placed in a `style` attribute.
- Declare the PHP version in `composer.json` and the Node version in `package.json` once the team confirms the minimums.
- `composer.json` suggests Municipio `^6.0.0`. Confirm whether that suggestion is still right.
