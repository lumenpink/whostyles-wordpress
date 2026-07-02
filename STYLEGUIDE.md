# Style Guide

This document outlines the coding standards, rules, and style conventions for the Whostyles WordPress plugin project.

---

## 1. Primary Rule: English Only
To ensure consistency and ease of contribution, everything in the codebase must be written in **English**:
* **File names** and directory structures.
* **Variable names**, constants, properties, and array keys.
* **Function and method names**.
* **Classes, interfaces, and namespaces**.
* **Comments** (inline, block, and Docblocks).
* **Git commit messages** (when requested or manual).
* **Documentation** and logging.

---

## 2. Formatting & SonarQube Rules
We aim to follow SonarQube quality standards and WordPress PHP Coding Standards (WPCS):

### A. Line Length Limit
* **Strict Limit**: All source lines MUST NOT exceed **120 characters**.
* Lines exceeding 120 characters should be wrapped or refactored.
* Ideally, keep normal code within **80-100 characters** for maximum readability.

### B. Indentation and Spacing
* Use **spaces** for indentation. A single indentation level is **4 spaces** (PSR-12 standard is followed for general classes, while complying with WPCS hook formats).
* Avoid trailing whitespace at the end of lines.
* Ensure all files end with a single empty newline character.

### C. PHP Version & Strict Typing
* **Compatibility**: Target PHP 7.4+.
* **Strict Types**: Every PHP file MUST start with:
  ```php
  <?php
  declare(strict_types=1);
  ```

---

## 3. Naming Conventions

### A. Classes & Namespaces
* **Namespaces**: Use PSR-4 autoloader. The root namespace is `Whostyles\WordPress`.
* **Class Names**: Use `PascalCase`. Example: `CommentHandler`, `PostHandler`.

### B. Methods & Functions
* **Methods**: Use `camelCase`. Example: `registerHooks()`, `parseCommentText()`.
* **Global Functions**: Avoid global functions. If necessary, prefix them with `whostyles_` to prevent collisions.

### C. Variables & Constants
* **Variables**: Use `camelCase` or `snake_case` consistently.
* **Class Constants**: Use `UPPER_SNAKE_CASE`. Example: `META_KEY_HASH`.

---

## 4. Documentation & Comments
* Code must be documented using clean PHPDoc blocks for all classes, methods, and properties.
* Document hook parameters when registering filters/actions.
