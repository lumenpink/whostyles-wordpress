# Whostyles WordPress Plugin

A WordPress plugin adding native support for the Whostyles V1 (Hash Protocol) specification.

This plugin allows authors to visual syndicate personal typographic and cosmetic styles across WordPress posts
and comment systems safely, performantly, and without compromising the site's structural layout.

For details on the protocol itself, see the core project repository at [github.com/whostyles/whostyles](https://github.com/whostyles/whostyles).

---

## Features
- **Comments Styling**: Automatically parses incoming comments for Whostyle hashes, wrapping them in safe CSS container styling.
- **Author Branding**: Define Whostyles on a per-post or site-wide basis, outputting correct HTTP headers and `<meta>` tags.
- **Zero Raw CSS Execution**: Resolves styling exclusively using constrained CSS Custom Properties (variables) and predefined, host-enforced typography and background textures.

---

## Development Setup

### Requirements
- PHP 7.4 or higher
- Composer

### Installation
1. Clone this repository inside your WordPress `wp-content/plugins/` directory:
   ```bash
   cd wp-content/plugins/
   git clone <repo-url> whostyles
   ```
2. Navigate into the plugin folder and install development dependencies:
   ```bash
   composer install
   ```
   *(Note: Until the `whostyles/whostyles` core package is available on Packagist, the core class is temporarily copied under `src/Lib/Whostyles.php` and loaded using local Composer autoloading configuration.)*

---

## Static Analysis
Run PHPStan to verify code quality and compliance:
```bash
composer run-script phpstan
```
Or directly:
```bash
vendor/bin/phpstan analyse
```

---

## License
This project is licensed under the GNU General Public License v2.0 (GPL-2.0). See [LICENSE](LICENSE) for details.
This plugin is compliant with the requirements for publishing on the WordPress.org plugin directory.
