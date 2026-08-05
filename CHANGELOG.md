# Changelog

## [2.1.0] - 2026-08-05

### Added
- Manual entry mode for free usage without API
- Data source selector (Manual / API)
- Maturity sorting by numeric value (6M → 40Y)
- Force server refresh button (when API key is set)

### Changed
- Simplified admin settings page
- Updated to v2.1.0

### Fixed
- Maturity labels now show "10Y" instead of "10Y-Year"
- Compact format shows "10Y" instead of "10YY"

### Removed
- BoE direct download (was broken)
- FRED API integration (required paid key)
- Import handler (unnecessary with API)
- Manual entry fields from v1 (replaced with new system)

## [2.0.0] - 2026-08-05

### Added
- New API client class for automatic updates
- API URL and API Key settings
- Health check endpoint support
- Server refresh capability

### Changed
- Complete rewrite of data fetching layer
- Simplified admin interface
- Updated to WordPress coding standards

### Fixed
- API response format parsing
- Maturity label display

### Removed
- BoE direct download (835 lines of broken code)
- FRED API integration
- Manual entry system from v1
- Import handler

## [1.3.2] - Previous Release

### Added
- Multiple data sources (Manual, BoE, FRED)
- Import handler for ZIP/XLSX/CSV
- Admin settings page
- Shortcode system
- Gutenberg block support

### Changed
- Updated to WordPress 5.0+ requirements

### Fixed
- Various bugs and improvements

## [1.3.1] - Previous Release

- Initial stable release
