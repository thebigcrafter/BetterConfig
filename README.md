# BetterConfig

BetterConfig is a PHP library packaged as a PocketMine-MP Virion for reading and writing YAML configuration data. The current manifest supports Virion API 5 consumers with PHP 8.1 or newer; it does not provide a standalone server command or PHAR.

> BetterConfig is a maintained compatibility library, not a standalone plugin. Validate the exact consuming plugin and back up live configuration data before deployment.

## Current API

The public class is `KygekTeam\BetterConfig\Config`. It stores an array cache and exposes helpers for:

- Reading top-level keys with `get()` and nested keys with `getNested()`.
- Reading the complete cache with `getAll()` or selected keys with `getContents()`.
- Writing values with `set()`, `setNested()`, `setContents()` and `setAll()`.
- Removing values with `remove()`, `removeNested()`, `removeContents()` and `removeAll()`.
- Checking keys with `exists()`, `existsNested()`, `existsContents()` and `isEmpty()`.
- Saving/reloading YAML through `save()`, `reload()` and `update()`.
- Controlling the nested-key separator and whether reads/writes refresh the file through `setNestedSeparator()` and `setAlwaysUpdated()`.

The constructor accepts a file path, an optional initial cache, a nested-key separator (default `.`), and an `alwaysUpdated` flag. The implementation uses its bundled Composer autoloader and namespaced YAML parser under `src/KygekTeam/BetterConfig/Internal/Yaml/`.

## Compatibility and integration

BetterConfig is intended to be embedded into a PocketMine-MP plugin through the Virion build process. The compatibility contract is the one declared in `composer.json` and `virion.yml`:

- PHP 8.1 or newer within the declared package constraints.
- PocketMine-MP Virion API 5.
- The YAML parser is bundled under the library namespace; the current package has no runtime Composer dependency beyond PHP and `ext-ctype`.

The repository does not include a full consumer integration suite. The local smoke contract covers file creation, nested reads/writes, malformed YAML handling and cache refresh behavior. The source uses no PocketMine API symbols, but each API 5 consumer must still verify data preservation for its own server lifecycle.

## Development checks

Install the locked Composer dependencies and run the repository's static-analysis configuration:

```text
composer install
composer analyse
composer smoke
```

The smoke command uses a temporary file and exercises persistence, nested access, cache refresh, selected-key operations and malformed-YAML recovery.

The `Run tests and static analysis` GitHub Actions workflow runs the locked Composer install, smoke contract and maximum-level PHPStan analysis on pushes, pull requests and manual dispatches. The repository has no project-specific release automation. It also has no user-facing DevTools instructions; packaging belongs to the consuming plugin's build process. This documentation therefore does not copy a builder version that could become stale.

## Project status and provenance

The package metadata and source retain the KygekTeam/BetterConfig namespace and attribution. The README describes the current implementation rather than presenting the historical task list as completed work. This repository is released as a Virion source package; it is not a standalone plugin release.

## License

BetterConfig is licensed under the [GNU General Public License v3.0](LICENSE).
