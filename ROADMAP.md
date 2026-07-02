# Roadmap

This document outlines the milestones, planned features, and future goals for the Whostyles WordPress plugin.

---

## Milestone 1: Initial Release & Core Functionality (Current Stage)
- [x] Establish standard WordPress plugin project structures.
- [x] Configure Composer development environment and PHPStan static analysis.
- [x] Set up standard documents (`LICENSE`, `STYLEGUIDE`, `ARCHITECTURE`, `README`).
- [ ] Add basic hooks integration:
  - Comment parsing via `comment_text`.
  - Simple post metadata association (`_whostyle_hash`).
  - Output post headers (`X-Whostyle` HTTP header & `<meta name="whostyle">`).
  - Enqueue combined stylesheet for styling attributes.

---

## Milestone 2: Enhancements & Administration
- [ ] Admin Settings Panel:
  - Toggle Whostyle parsing for comments and posts independently.
  - Set a site-wide fallback Whostyle hash (author style).
- [ ] Custom Post Type and Page support.
- [ ] Post Metadata Editor UI:
  - Simple settings box in the classic editor and block editor sidebars.
  - Live validation of the Whostyle hash input using JavaScript.

---

## Milestone 3: Integrations & Performance
- [ ] Block Editor (Gutenberg) integration:
  - Block-level styling or Whostyle-aware custom container blocks.
- [ ] Pre-compiled CSS optimization:
  - Cache dynamic rendering of styles if needed.
  - Optimize the texture stylesheet size.
- [ ] Integration with IndieWeb plugins:
  - Support automatic parsing of Webmention authors' Whostyles from incoming mentions.
- [ ] Compatibility testing with popular page builders (Elementor, Divi) and themes.
