# Changelog

All notable changes to the UK Yield Rates Live plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.2] - 2026-07-30

### Fixed

- Dark theme CSS class not applied to shortcode output when `theme="dark"` is used

---

## [1.3.1] - 2026-07-30

### Fixed

- Added missing `wp_ajax_nopriv_uk_yield_refresh` handler so auto-refresh works for anonymous visitors

---

## [1.3.0] - 2026-07-30

### Added

- Automatic BoE ZIP download and Excel parsing via `UK_Yield_BoE_Provider`
- File upload import for ZIP, XLSX, and CSV files via `UK_Yield_Import_Handler`
- Unified `UK_Yield_Provider_Interface` for all data sources
- Admin UI for file upload and import management

### Changed

- Cloudflare Worker marked as experimental (BoE CSV URL returns 404)
- Marked BoE Custom Endpoint as experimental

### Fixed

- Version numbers synchronized across all files

---

## [1.2.0] - 2026-07-30

### Fixed

- `wp_localize_script` handle mismatch on frontend (broke auto-refresh entirely)
- `wp_localize_script` handle mismatch in admin (broke force-refresh, manual validation, bug report modal)
- Version constant mismatch (`UK_YIELD_RATES_VERSION` was stale at `1.0.0`)
- Build script packaging (wrong directory, broken `exclude.txt` reference)

---

## [1.1.0] - 2026-07-29

### 🎉 Major Features

#### Text Style Inheritance
- Shortcodes now automatically inherit text styles from parent elements
- Works with headers (`<h1>`, `<h2>`, etc.), bold text, italic text, and custom classes
- CSS uses `inherit` values for font-weight, font-size, color, and other properties
- Only semantic colors (change indicators) are overridden
- Example: Place in `<h2>` and yields display in h2 style automatically

#### GitHub Integration
- **Bug Reporting**: Report bugs directly from admin interface
- **Feature Requests**: Suggest improvements via GitHub
- Pre-filled system information (WP version, PHP version, theme)
- Structured templates for consistent reports
- Direct link to GitHub issue creation

#### Block Editor Enhancements
- **Preview Modes**: Live data, sample data, custom shortcode
- **Theme Preview**: See light/dark themes in real-time
- Copy shortcode button
- Generated shortcode display
- Better inspector panel organization

### ✨ Improvements

#### Admin Interface
- **Real-time validation** for manual yield entries
- Numeric validation (0-100 range)
- Inline error messages
- Confirmation dialogs for critical actions
- Loading spinners during AJAX operations
- Success/error feedback notifications
- Support section with documentation links

#### Frontend Experience
- **Animations**: Yield value pulse and flash effects
- **Tooltips**: Hover tooltips showing yield details
- **Loading States**: Overlay during auto-refresh
- **Hover Effects**: Subtle background changes on interaction
- Smooth transitions for theme changes

#### CSS & Styling
- Improved text inheritance with `inherit` values
- Better responsive breakpoints
- Enhanced dark theme contrast
- Consistent animation timing
- Touch-friendly mobile interactions

### 🐛 Bug Fixes

#### Critical Fixes
- **Fixed auto-refresh maturity parameter**: AJAX now correctly sends maturity and format to server
- **Fixed shortcode data attributes**: Added `data-maturity` and `data-format` attributes for auto-refresh
- **Added XSS sanitization**: Block editor preview now sanitizes HTML to prevent attacks

#### Bug Fixes
- **Fixed duplicated code**: Extracted shared shortcode builder to eliminate duplication
- **Fixed weekend cache override**: Cache duration now respects admin setting (removed hardcoded 24h)
- **Fixed admin default mismatch**: Aligned settings page default with API default ('manual')

#### Removed Issues
- Removed dead code that referenced undefined properties (`boe_series_codes`, `boe_base_url`)
- Removed old BoE API implementation (replaced by custom endpoint approach)
- Removed SSL verification bypass from old code

### 🔧 Technical Changes

#### New Files
- `blocks/yield-rates/shortcode-builder.js` - Shared shortcode building utility
- `CHANGELOG.md` - This file
- `CONTRIBUTING.md` - Contribution guidelines
- `SECURITY.md` - Security policy

#### Modified Files
- `includes/class-uk-yield-shortcode.php` - Added data attributes for auto-refresh
- `includes/class-uk-yield-cache.php` - Removed weekend cache override
- `includes/class-uk-yield-admin.php` - Enhanced script localization
- `public/js/yield-rates.js` - Added animations, tooltips, loading states
- `public/css/yield-rates.css` - Text inheritance, animations, responsive improvements
- `admin/js/admin.js` - Validation, confirmation dialogs, bug reporting
- `admin/css/admin.css` - Modal styles, support section, improved UX
- `admin/views/settings-page.php` - Support section, bug report modal
- `blocks/yield-rates/edit.js` - Preview modes, theme preview, copy shortcode
- `blocks/yield-rates/editor.css` - Theme preview styles, improved layout

#### Code Quality
- Eliminated code duplication across files
- Improved CSS inheritance patterns
- Enhanced security with HTML sanitization
- Better error handling in AJAX calls
- Proper WordPress coding standards compliance

### 📚 Documentation

- Updated README.md with comprehensive feature documentation
- Updated readme.txt for WordPress.org with new features
- Added text style inheritance examples
- Added GitHub integration documentation
- Improved shortcode attribute documentation
- Added troubleshooting section

---

## [1.0.0] - 2026-07-28

### 🎉 Initial Release

#### Core Features
- Manual yield rate entry (recommended - FREE)
- BoE Custom Endpoint for automatic updates
- FRED API integration (free tier)
- Multiple display formats (inline, sidebar, table, compact)
- Gutenberg block with live preview
- Admin settings page
- Responsive design
- Light/Dark themes
- Auto-refresh functionality

#### Data Sources
- **Manual Entry**: Most reliable, free, no API required
- **BoE Custom Endpoint**: Free, requires Cloudflare/Vercel setup
- **FRED API**: Free tier, requires API key
- **Auto Mode**: Tries all sources with fallback

#### Display Formats
- **Inline**: Perfect for paragraphs, inherits text styles
- **Sidebar Widget**: Standalone widget format
- **Table**: Full comparison table with change indicators
- **Compact**: Single-line display

#### Admin Interface
- Settings page with tabbed navigation
- Data source configuration
- Display settings (format, theme, decimals)
- Advanced settings (cache, auto-refresh)
- Quick start guide
- Shortcode reference

#### Block Editor
- Gutenberg block with visual preview
- Inspector controls for configuration
- Real-time preview updates
- Theme selection

#### Frontend
- Responsive design for all devices
- Light and Dark themes
- Change indicators (↑↓→)
- Last updated timestamp
- Auto-refresh with AJAX

#### Technical
- WordPress transient caching
- Efficient database queries
- Secure AJAX handling
- Input sanitization
- Output escaping
- Nonce verification

---

## [Unreleased]

### Planned Features

#### Version 1.4.0 (Planned)
- [ ] Export/Import settings
- [ ] Multiple currency support
- [ ] Historical data charts
- [ ] Widget areas integration
- [ ] Email notifications for rate changes
- [ ] REST API endpoints
- [ ] WP-CLI commands

---

## Upgrade Guide

### Upgrading to 1.1.0

1. **Backup your database** (recommended)
2. Update plugin files
3. Clear any caching plugins
4. Visit Settings > UK Yield Rates to review new features
5. Test shortcodes on a staging site first
6. Enjoy text inheritance and GitHub integration!

### Breaking Changes

None - this is a backward-compatible update.

### Deprecated Features

None - all 1.0.0 features remain fully supported.

---

## Support

- **Bug Reports**: [GitHub Issues](https://github.com/OrrnobMahmud/uk-yield-rates/issues)
- **Feature Requests**: Open an issue with "enhancement" label on GitHub
- **WordPress.org**: Coming soon

---

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.
