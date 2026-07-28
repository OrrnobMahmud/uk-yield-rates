# UK Yield Rates Live

[![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://github.com/OrrnobMahmud/uk-yield-rates/releases)
[![License](https://img.shields.io/badge/license-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP Version](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://php.net)

A WordPress plugin to display UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks. Perfect for estate agents, mortgage brokers, and financial advisors who need to show current yield rates in their website content.

**Author:** [Orrnob Mahmud](https://orrnobmahmud.com)  
**Website:** [https://orrnobmahmud.com](https://orrnobmahmud.com)  
**GitHub:** [https://github.com/OrrnobMahmud/uk-yield-rates](https://github.com/OrrnobMahmud/uk-yield-rates)

---

## 🎯 Key Features

### Text Style Inheritance ✨
The shortcode **automatically inherits text styles** from its surrounding context! Place it in:
- **Headers** (`<h1>`, `<h2>`, etc.) → yields display in header style
- **Bold text** (`<strong>`) → yields display bold
- **Italic text** (`<em>`) → yields display italic
- **Any styled element** → inherits all text properties

### Multiple Data Sources 📊
- **Manual Entry** (Recommended) - Most reliable, FREE
- **BoE Custom Endpoint** - Free with Cloudflare/Vercel
- **FRED API** - Free tier available
- **Auto Mode** - Tries all sources with fallback

### Display Formats 🎨
- **Inline** - Perfect for paragraphs
- **Sidebar Widget** - Standalone widget format
- **Table** - Full comparison table
- **Compact** - Single-line display

### Block Editor Integration 🧩
- Gutenberg block with live preview
- **Preview modes:** Live data, Sample data, Custom shortcode
- **Theme preview:** See light/dark themes in real-time
- Copy shortcode button
- Visual configuration panel

### Admin Interface 💼
- Beautiful, intuitive settings page
- **Real-time validation** for manual entries
- **Confirmation dialogs** for critical actions
- **Loading spinners** for AJAX operations
- **Bug reporting** directly to GitHub
- **Feature requests** integration

---

## 📥 Installation

> **⚠️ Note:** This plugin is NOT yet available on WordPress.org. Install from GitHub.

### Option 1: Download Pre-built Release (Easiest)

1. Go to [GitHub Releases](https://github.com/OrrnobMahmud/uk-yield-rates/releases)
2. Download the latest release ZIP file
3. In WordPress, go to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the ZIP file and click **Install Now**
6. Click **Activate**

### Option 2: Build from Source

```bash
# Clone the repository
git clone https://github.com/OrrnobMahmud/uk-yield-rates.git
cd uk-yield-rates

# Install dependencies
npm install

# Build the Gutenberg block
npm run build

# Create a ZIP file of the folder and upload to WordPress
```

### Post-Installation Setup

1. Go to **Settings > UK Yield Rates**
2. Choose your data source (Manual recommended)
3. Enter yield rates if using Manual mode
4. Use shortcodes or Gutenberg block to display yields

**Detailed instructions:** See [INSTALL.md](INSTALL.md)

---

## 📝 Shortcodes

### Basic Usage

```php
// Default inline display
[uk_yield_rates]

// Single maturity inline
[uk_yield_rates inline="yes" maturity="10"]

// Multiple maturities inline
[uk_yield_rates inline="yes" maturity="2,5,10"]

// Sidebar widget
[uk_yield_rates format="sidebar"]

// Full table
[uk_yield_rates format="table"]

// Compact single line
[uk_yield_rates format="compact"]
```

### Real-World Examples

**Estate Agent:**
```html
<p>Our fixed-rate mortgages start from 4.25%, influenced by the 
<span class="yield">[uk_yield_rates inline="yes" maturity="5"]</span> 
5-year gilt yield. Contact us today!</p>
```

**In a Header (Text Inheritance):**
```html
<h2>Mortgage rates follow the 
[uk_yield_rates inline="yes" maturity="10"] 10-year gilt yield</h2>
```
The yield will inherit the header's font weight, size, and color!

---

## ⚙️ Shortcode Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `maturity` | all, 2, 5, 10, 20, 30 | all | Which yield maturities to display |
| `format` | inline, sidebar, table, compact | inline | Display format |
| `inline` | yes, no | no | Force inline display |
| `show_change` | yes, no | yes | Show change indicator (↑↓→) |
| `decimal` | 2, 3, 4 | 2 | Decimal places |
| `theme` | light, dark | light | Color theme |

---

## 🎨 Text Style Inheritance

The plugin inherits text styles from parent elements:

```html
<!-- Yields display as h1 -->
<h1>[uk_yield_rates inline="yes" maturity="10"]</h1>

<!-- Yields display bold -->
<strong>[uk_yield_rates inline="yes" maturity="10"]</h1>

<!-- Yields inherit custom styles -->
<span style="color: red; font-size: 24px;">
  [uk_yield_rates inline="yes" maturity="10"]
</span>
```

**Only exception:** Change indicators (↑↓→) have semantic colors (green/red/gray).

---

## 🖥️ Block Editor Features

1. Add "UK Yield Rates" block in Gutenberg
2. Configure in Inspector Controls (right sidebar)
3. See live preview in editor
4. Toggle between preview modes (Live, Sample, Custom)
5. Switch themes in real-time
6. Copy shortcode with one click

---

## 💼 Admin Interface

- **Data Source tab** - Configure how to get yields
- **Display Settings tab** - Default format, theme, decimals
- **Advanced tab** - Cache duration, auto-refresh
- **Support section** - Bug reporting, feature requests

Features:
- Real-time validation for manual entries
- Confirmation dialogs for critical actions
- Loading spinners during AJAX operations
- Direct GitHub integration for issues

---

## 🐛 Bug Reporting & Support

### Report a Bug
1. Go to **Settings > UK Yield Rates**
2. Click **"Report Bug on GitHub"**
3. Fill in the form (system info auto-populated)
4. Click **"Open GitHub Issue"**

### Request a Feature
1. Go to **Settings > UK Yield Rates**
2. Click **"Request Feature on GitHub"**
3. Describe your idea
4. Submit via GitHub

### Get Help
- **GitHub Issues**: https://github.com/OrrnobMahmud/uk-yield-rates/issues
- **Documentation**: See README.md and INSTALL.md

---

## 🔄 Changelog

### 1.1.0 (2026-07-29)
#### ✨ New Features
- **Text Style Inheritance** - Shortcodes inherit parent element styles
- **GitHub Bug Reporting** - Report bugs directly from admin
- **Feature Request System** - Request features via GitHub
- **Block Editor Preview Modes** - Live, sample, and custom shortcode preview
- **Theme Preview** - See light/dark themes in editor

#### 🎨 Improvements
- **Admin UI** - Validation, loading states, confirmation dialogs
- **Frontend** - Animations, tooltips, hover effects, loading states
- **Block Editor** - Better preview, copy shortcode button

#### 🐛 Bug Fixes
- Fixed auto-refresh not sending maturity parameter
- Fixed shortcodes not outputting data attributes
- Added XSS sanitization for block editor preview
- Eliminated duplicated shortcode builder code
- Fixed weekend cache ignoring admin settings

### 1.0.0 (2026-07-28)
- Initial release

---

## 📄 License

GPL v2 or later - see [LICENSE](LICENSE) for details

---

## 👤 Author

**Orrnob Mahmud**
- Website: [https://orrnobmahmud.com](https://orrnobmahmud.com)
- GitHub: [https://github.com/OrrnobMahmud](https://github.com/OrrnobMahmud)

---

## 🙏 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## 🔒 Security

To report security vulnerabilities, please see [SECURITY.md](SECURITY.md).

---

## ⭐ Support

If you find this plugin useful, please consider:
- ⭐ [Starring the GitHub repository](https://github.com/OrrnobMahmud/uk-yield-rates)
