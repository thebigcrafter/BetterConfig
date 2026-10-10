# BetterConfig

BetterConfig is a PHP library packaged as a PocketMine-MP Virion for reading and writing YAML configuration data. The current manifest targets PocketMine-MP Virion API 4 and PHP 8.0; it is not an API 5 plugin and does not provide a standalone server command or PHAR.

> BetterConfig is still under development and has not been validated as a production-ready configuration layer. Review the current source and test it in a disposable consumer plugin before using it for live server data.

## Current API

The public class is `KygekTeam\BetterConfig\Config`. It stores an array cache and exposes helpers for:

- Reading top-level keys with `get()` and nested keys with `getNested()`.
- Reading the complete cache with `getAll()` or selected keys with `getContents()`.
- Writing values with `set()`, `setNested()`, `setContents()` and `setAll()`.
- Removing values with `remove()`, `removeNested()`, `removeContents()` and `removeAll()`.
- Checking keys with `exists()`, `existsNested()`, `existsContents()` and `isEmpty()`.
- Saving/reloading YAML through `save()`, `reload()` and `update()`.
- Controlling the nested-key separator and whether reads/writes refresh the file through `setNestedSeparator()` and `setAlwaysUpdated()`.

The constructor accepts a file path, an optional initial cache, a nested-key separator (default `.`), and an `alwaysUpdated` flag. The implementation uses its bundled Composer autoloader and Symfony YAML classes under the configured library path.

## Compatibility and integration

BetterConfig is intended to be embedded into a PocketMine-MP plugin through the Virion build process. The compatibility contract is the one declared in `composer.json` and `virion.yml`:

- PHP 8.0 or newer within the declared package constraints.
- PocketMine-MP Virion API 4.
- `symfony/yaml` and `consolidation/comments` as the current library dependencies.

The repository does not currently include an example plugin or a full consumer integration suite. The local smoke contract covers file creation, nested reads/writes, malformed YAML handling and cache refresh behavior; a consumer must still verify data preservation for its own server lifecycle before adopting the library.

## Development checks

Install the locked Composer dependencies and run the repository's static-analysis configuration:

```text
composer install
vendor/bin/phpstan analyse -c phpstan.neon.dist
composer smoke
```

The smoke command uses a temporary file and exercises persistence, nested access, cache refresh, selected-key operations and malformed-YAML recovery.

The current checkout has no GitHub Actions workflow and no project-specific release automation. It also has no user-facing DevTools instructions; packaging belongs to the consuming plugin's build process. This documentation therefore does not copy a builder version that could become stale.

## Project status and provenance

The package metadata and source retain the KygekTeam/BetterConfig namespace and attribution. The README describes the current implementation rather than presenting the historical task list as completed work. There is no verified stable release or consumer compatibility claim in this repository.

## License

BetterConfig is licensed under the [GNU General Public License v3.0](LICENSE).
