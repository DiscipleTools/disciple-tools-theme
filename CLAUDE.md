# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Disciple.Tools is a WordPress theme that functions as a CRM for Christian ministries managing discipleship and church growth. It's not a typical theme—it's a full application built on WordPress.

## Common Commands

### Build & Development
```bash
npm install          # Install dependencies and run build
npm run dev          # Start Vite dev server with HMR (styles only)
npm run build        # Build CSS/JS for production (Vite)
```

### Linting
```bash
npm run lint                    # Run ESLint
npm run prettier                # Format JS files with Prettier
composer install                # Install PHP dependencies (required for PHPCS)
./tests/test_phpcs.sh           # Run PHP CodeSniffer
./tests/test_phpcs.sh file.php  # Run PHPCS on specific files
./vendor/bin/phpcbf             # Auto-fix PHPCS errors
./tests/test_eslint.sh          # Run ESLint + Prettier check
```

## Architecture

### Core Module Structure
The theme is organized into `dt-*` directories, each representing a major feature module:

- **dt-posts/** - Generic post type CRUD system. `DT_Posts` class provides `create_post()`, `update_post()`, `get_post()`, `list_posts()` for any registered post type
- **dt-contacts/** - Contact post type and contact-specific logic
- **dt-groups/** - Groups post type
- **dt-users/** - User management, permissions, and user-related endpoints
- **dt-notifications/** - Notification system with email queue
- **dt-metrics/** - Dashboard metrics and charts
- **dt-mapping/** - Location/mapping functionality
- **dt-people-groups/** - People groups post type
- **dt-login/** - Authentication, including SSO methods
- **dt-workflows/** - Automation workflows
- **dt-reports/** - Magic links system for public-facing forms
- **dt-core/** - Shared utilities, configuration, libraries, and admin functionality
- **dt-assets/** - Frontend assets (JS, SCSS, images)

### Key Patterns

**Post Type Registration**: Custom post types extend `DT_Module_Base` and register via `dt_registered_post_types` filter.

**Field Definitions**: Post type fields are defined via `dt_custom_fields_settings` filter. Field types include: text, textarea, number, date, key_select, multi_select, tags, connection, location, user_select, communication_channel.

**REST API**: Endpoints follow pattern `/dt-posts/v2/{post_type}/` and `/dt/v1/` namespaces. All REST endpoints require authentication by default.

**Frontend Build** (see `vite.config.js`):
- SCSS from `dt-assets/scss/` and `@disciple.tools/web-components/src/styles/` compile to `dt-assets/build/css/`
- JS from what-input, Foundation core + plugins, `dt-assets/js/footer-scripts.js`, and masonry bundle to `dt-assets/build/js/scripts.min.js`
- Web components JS from `@disciple.tools/web-components/dist/` copy to `dt-assets/build/components/`
- Vite HMR is supported for development.

### Important Files

- `functions.php` - Main entry point, loads all modules
- `dt-core/global-functions.php` - Globally available utility functions
- `dt-posts/dt-posts.php` - Core CRUD operations for all post types
- `dt-core/configuration/config-site-defaults.php` - Default site configuration
- `dt-core/configuration/class-roles.php` - User roles and capabilities

### Documentation

See `docs/` for detailed API documentation:
- `dt-posts-api-reference.md` - DT_Posts API reference
- `dt-posts-field-formats.md` - Field value formats for create/update
- `dt-posts-field-settings.md` - How to define field settings
- `dt-posts-list-query.md` - Query parameters for list_posts()
- `dt-magic-links.md` - Magic links system
- `dt-database-schema.md` - Database schema

## Code Style

- **PHP**: WordPress coding standards with 4-space indentation (not tabs). Run PHPCS to check.
- **JS**: ESLint + Prettier. Don't use `_.` for lodash (conflicts with underscore).
- **Translations**: All user-facing strings must be translatable using `esc_html_e()`, `__()`, etc. with text domain `disciple_tools`.

## Pre-commit Hooks

Husky runs lint-staged on commit:
- PHP files: `./phpcbf.sh` (auto-fix)
- JS files: ESLint + Prettier

## CI Requirements

Pull requests must pass:
1. PHP syntax check
2. PHPCS on changed PHP files
3. ESLint + Prettier
4. Vite build (compiled assets must match expected output)
5. PHPUnit tests (multisite mode)
