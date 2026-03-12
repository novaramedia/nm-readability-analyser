# Changelog

## [2.0.0] - 2026-03-12

### Added
- Read time estimation (saved as `nm_read_time` post meta)
- REST API exposure for both `nm_readability_age` and `nm_read_time`

### Changed
- Complete codebase rewrite — single PHP file, single JS file
- Replaced webpack (30 devDependencies) with esbuild (1 devDependency)
- Removed lodash dependency (native debounce)
- Removed composer / phpdotenv (unused)
- Removed PHP boilerplate classes (singleton, hooker, assets, i18n, activator, deactivator)
- Metabox now uses WordPress-native table styling
- Script only loads on post editor screens (was loading on all admin pages)

### Removed
- SCSS build pipeline (CSS output was empty)
- Setup scripts
- i18n scaffolding (was unused)

## [1.0.0] - 2021-03-05

### Added

- Feature: Analysis in block and classic editor with display in metabox
  