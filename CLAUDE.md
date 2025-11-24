# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **WordPress-based landing page** for TurtleSchool (거북스쿨), using:
- **WordPress Core**: Standard WordPress installation in `www/` directory
- **Theme**: Astra (v4.11.12) - A highly customizable, lightweight WordPress theme
- **Page Builder**: Elementor - Visual page builder with drag-and-drop interface
- **Language**: Korean (ko_KR) with extensive translation files
- **Database Tool**: phpMyAdmin (accessible via `www/dbeditor/`)

## Directory Structure

```
www/                          # WordPress root (ABSPATH)
├── wp-config.php             # WordPress configuration (DATABASE: ingapt)
├── wp-content/               # WordPress content directory
│   ├── themes/
│   │   └── astra/            # Active theme (436 PHP files)
│   │       ├── inc/          # Theme includes and functionality
│   │       │   ├── builder/  # Astra header/footer builder
│   │       │   ├── compatibility/ # Plugin integrations
│   │       │   ├── customizer/    # Theme customization
│   │       │   └── modules/       # Theme modules
│   │       └── template-parts/    # Template partials
│   ├── plugins/
│   │   ├── elementor/        # Page builder
│   │   ├── elementskit/      # Elementor addons
│   │   ├── astra-sites/      # Demo import tool
│   │   ├── ultimate-member/  # User management
│   │   ├── ninjafirewall/    # Security plugin
│   │   └── ssl-zen/          # SSL management
│   ├── uploads/              # Media files organized by year
│   └── languages/            # Korean translation files
└── dbeditor/                 # phpMyAdmin installation
```

## Development Commands

### WordPress Debugging

Enable debugging by modifying `www/wp-config.php:91`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Debug logs appear in `www/wp-content/debug.log`

### Database Access

- **Database Name**: `ingapt`
- **Database User**: `ingapt`
- **Table Prefix**: `wp_`
- **phpMyAdmin**: Access via `www/dbeditor/` directory

### Theme Development

**Theme Location**: `www/wp-content/themes/astra/`

**Key Theme Files**:
- `functions.php` - Theme initialization and hooks (ASTRA_THEME_VERSION: 4.11.12)
- `inc/core/` - Core functionality classes
- `inc/customizer/` - WordPress Customizer integration
- `inc/builder/` - Header/footer builder system
- `inc/compatibility/` - Plugin compatibility layers

**Theme Customization**:
- Use child theme for modifications (create `astra-child/` directory)
- Hook into Astra actions/filters defined in `inc/core/theme-hooks.php`
- Customizer settings stored in `astra-settings` option

### Plugin Development

**Active Plugins**:
- Elementor (page builder)
- ElementsKit Lite (Elementor addons)
- Ultimate Member (user profiles and registration)
- Astra Sites (demo import)
- SSL Zen / WP Let's Encrypt SSL (HTTPS management)
- NinjaFirewall (security)
- Insert Headers and Footers
- Ultimate Addons for Gutenberg

## Architecture Notes

### WordPress Configuration

**Security Settings** (wp-config.php:107-111):
- File editing disabled: `DISALLOW_FILE_EDIT = true`
- Post revisions limited: `WP_POST_REVISIONS = 7`
- Image editing overwrites: `IMAGE_EDIT_OVERWRITE = true`
- WP-Cron disabled: `DISABLE_WP_CRON = true` (use system cron instead)
- Trash emptied after 7 days: `EMPTY_TRASH_DAYS = 7`

**Really Simple SSL Integration**:
- Session cookies secured with httponly and secure flags
- Security key: `RSSSL_KEY` defined in wp-config.php

### Theme Architecture (Astra)

**Class-Based Structure**:
- `Astra_Theme_Options` - Theme option management
- `Astra_Enqueue_Scripts` - Asset loading
- `Astra_Dynamic_CSS` - Dynamic CSS generation
- `Astra_Builder_Loader` - Header/footer builder initialization
- `Astra_Customizer` - Customizer integration

**Compatibility Layer**:
Astra includes dedicated compatibility modules for:
- Elementor / Elementor Pro
- WooCommerce / SureCart (eCommerce)
- Contact Form 7 / Gravity Forms
- Gutenberg / Beaver Builder
- LearnDash / LifterLMS (LMS)
- Yoast SEO

**Hook System**:
- Uses WordPress action/filter hooks extensively
- Custom hooks defined in `inc/core/theme-hooks.php`
- Modular design allows selective feature loading

### Elementor Integration

Elementor works as:
1. Visual page builder replacing WordPress editor
2. Custom post meta stores page builder data
3. Frontend rendering via Elementor templates
4. Theme provides default styling that Elementor can override

### Localization

**Language**: Korean (ko_KR)
- Translation files in `www/wp-content/languages/`
- Plugin translations in `www/wp-content/languages/plugins/`
- Theme translations in `www/wp-content/languages/themes/`
- JSON files for JavaScript translations

## Important Considerations

### File Permissions
- Uploads directory must be writable: `www/wp-content/uploads/`
- Plugin directory should not be directly editable (DISALLOW_FILE_EDIT enabled)

### Security Notes
- Database credentials are in plain text in `www/wp-config.php`
- File editing disabled in WordPress admin
- Multiple security plugins active (NinjaFirewall, SSL Zen)
- Custom security configurations via Really Simple SSL

### Performance
- WP-Cron disabled (requires server-level cron job setup)
- Image editing set to overwrite (saves disk space)
- Astra theme optimized for performance (minimal CSS/JS)

### Deployment
- This appears to be a development/staging environment (git repository)
- Production deployment would require:
  - New database credentials
  - Updated WP_HOME and WP_SITEURL constants
  - SSL certificate configuration
  - Server cron job for WP-Cron replacement

## Common Tasks

### Adding Custom Functionality
1. Create child theme: `www/wp-content/themes/astra-child/`
2. Add custom functions to child theme's `functions.php`
3. Use Astra hooks from `inc/core/theme-hooks.php`

### Modifying Page Content
1. Pages built with Elementor - edit via WordPress admin
2. Access: WP Admin → Pages → Edit with Elementor
3. Changes stored in post meta, not theme files

### Updating Translations
1. Add/modify .po/.mo files in `www/wp-content/languages/`
2. Use Poedit or similar translation tool
3. JSON files handle JavaScript translations

### Database Operations
1. Use phpMyAdmin at `www/dbeditor/`
2. Export via phpMyAdmin or WP-CLI: `wp db export`
3. Import via phpMyAdmin or WP-CLI: `wp db import`
4. Search/replace URLs: Use WP-CLI `wp search-replace` or Better Search Replace plugin
