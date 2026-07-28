# UK Yield Rates Live

[![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://github.com/OrrnobMahmud/uk-yield-rates)
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

**Example:**
```html
<h2>Mortgage rates are influenced by the <span class="yield">4.25%</span> 10-year gilt yield</h2>
```
The yield value will automatically display in the same font weight, size, and color as the header!

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

### Frontend Experience 🚀
- **Smooth animations** for yield changes
- **Hover tooltips** showing details
- **Loading states** during auto-refresh
- **Responsive design** for all devices

---

## 📦 Data Source Options

### Option A: Manual Entry (Recommended - FREE)
✅ **100% reliable** - no API dependencies  
✅ **No cost** - completely free  
✅ **Easy maintenance** - update once weekly  
✅ **Works immediately** - no setup required  

### Option B: BoE Custom Endpoint (FREE - Best Free Auto Option)
✅ **Official Bank of England data**  
✅ **Free** - no API keys or costs  
✅ **Automatic daily updates**  
✅ **You control the data source**  
⚠️ Requires 30 minutes setup  
⚠️ Need to deploy a small script  

### Option C: FRED API (FREE tier)
✅ Free API key from Federal Reserve  
✅ UK gilt yield data available  
⚠️ Limited free requests per day  
⚠️ Requires API key setup  

---

## 🚀 Quick Start

### Option A: Manual Updates (Recommended)

1. Go to **Settings > UK Yield Rates** in WordPress admin
2. Select "Manual Entry" as data source
3. Visit [Bank of England Yield Curves](https://www.bankofengland.co.uk/statistics/yield-curves)
4. Enter the current yields for 2Y, 5Y, 10Y, 20Y, and 30Y maturities
5. Click "Save Changes"
6. Update when rates change (set a weekly reminder)

### Option B: BoE Custom Endpoint (Best Free Auto Option)

1. Deploy a small script to Cloudflare Workers, Vercel, or Netlify (all free tiers)
2. The script fetches BoE CSV daily and parses yield data
3. Exposes a JSON endpoint like: `https://your-api.workers.dev/yields.json`
4. Go to **Settings > UK Yield Rates** in WordPress admin
5. Select "BoE Custom Endpoint" as data source
6. Enter your endpoint URL and click "Save Changes"

See the admin settings page for sample Cloudflare Worker code and expected JSON format.

### Option C: Automatic Updates with FRED API

1. Go to [FRED API Key Registration](https://fred.stlouisfed.org/docs/api/api_key.html)
2. Sign up for a free API key
3. Go to **Settings > UK Yield Rates** in WordPress admin
4. Select "FRED API" as data source
5. Enter your API key and click "Save Changes"

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
5-year gilt yield. Contact us today for a personalized quote!</p>
```

**Mortgage Broker:**
```html
<h3>Current Market Rates</h3>
[uk_yield_rates format="table"]
<p>The above rates are based on current UK gilt yields. Contact us for personalized mortgage advice.</p>
```

**Financial Advisor:**
```html
<p>Current UK government bond yields: 
[uk_yield_rates inline="yes" maturity="2,5,10"]</p>
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
| `maturity` | all, 2, 5, 10, 20, 30 | all | Which yield maturities to display. Comma-separated for multiple. |
| `format` | inline, sidebar, table, compact | inline | Display format |
| `inline` | yes, no | no | Force inline display in paragraphs |
| `show_change` | yes, no | yes | Show change indicator (↑↓→) |
| `decimal` | 2, 3, 4 | 2 | Decimal places for yield values |
| `theme` | light, dark | light | Color theme |

---

## 🎨 Display Formats

### Inline Format
Best for embedding in paragraphs and text content.

```html
<span class="uk-yield-inline">
  <span class="uk-yield-item">
    <span class="uk-yield-label">10-Year: </span>
    <span class="uk-yield-value">4.05%</span>
    <span class="uk-yield-change positive">↑0.02</span>
  </span>
</span>
```

### Sidebar Widget Format
Perfect for sidebars and widget areas.

```html
<div class="uk-yield-sidebar">
  <div class="uk-yield-sidebar-header">
    <h3 class="uk-yield-sidebar-title">UK Gilt Yields</h3>
  </div>
  <div class="uk-yield-sidebar-content">
    <!-- yield rows -->
  </div>
  <div class="uk-yield-sidebar-footer">Updated: 28 Jul 2026</div>
</div>
```

### Table Format
Great for detailed comparisons.

```html
<div class="uk-yield-table-wrapper">
  <table class="uk-yield-table">
    <thead>
      <tr>
        <th>Maturity</th>
        <th>Yield</th>
        <th>Change</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <!-- yield rows -->
    </tbody>
  </table>
</div>
```

### Compact Format
Single-line display for minimal space.

```html
<span class="uk-yield-compact">
  2Y: 4.25% (+0.02) | 10Y: 4.05% (→ 0.00) | Updated: 14:30
</span>
```

---

## 🎯 Text Style Inheritance

The plugin's CSS is designed to **inherit text styles** from parent elements. This means:

```css
/* All yield elements inherit from parent */
.uk-yield-inline,
.uk-yield-compact {
  font: inherit;
  color: inherit;
  font-weight: inherit;
  font-size: inherit;
  font-style: inherit;
  /* ... and more */
}
```

**What this means:**
- Place in `<h1>` → yields display as h1
- Place in `<strong>` → yields display bold
- Place in styled `<span>` → yields inherit all styles
- Place in custom class → yields inherit those styles

**Only exception:** Change indicators (↑↓→) have semantic colors:
- **Green (#16a34a)** for positive changes
- **Red (#dc2626)** for negative changes
- **Gray (#6b7280)** for neutral/stable

---

## 🖥️ Block Editor Features

### Gutenberg Block
1. Add "UK Yield Rates" block
2. Configure in Inspector Controls (right sidebar)
3. See live preview in editor

### Preview Modes
- **Live Data** - Fetches real yield data from your configured source
- **Sample Data** - Shows example yields (great for testing)
- **Custom Shortcode** - Preview any shortcode configuration

### Theme Preview
- Toggle between Light and Dark themes
- See exactly how yields will appear on frontend
- Visual feedback for theme selection

### Quick Actions
- **Copy Shortcode** - One-click copy to clipboard
- **Generated Shortcode** - See the shortcode for current settings
- **Export/Import** - (Coming soon)

---

## 💼 Admin Interface

### Settings Tabs

1. **Data Source** - Configure how to get yield data
2. **Display Settings** - Default format, theme, decimals
3. **Advanced** - Cache duration, auto-refresh settings

### Features

#### Real-Time Validation
- Instant feedback on yield inputs
- Validates numeric values (0-100 range)
- Shows inline error messages
- Prevents invalid data submission

#### Confirmation Dialogs
- Confirmation before cache refresh
- Warning for destructive actions
- Clear feedback on operations

#### Loading States
- Spinning indicators during AJAX
- Disabled buttons while processing
- Success/error notifications

#### Bug Reporting
- **Report Bug** button opens GitHub
- Pre-filled system information
- Structured bug template
- Direct GitHub integration

#### Feature Requests
- **Request Feature** button
- Structured feature template
- Links to GitHub enhancement label

---

## 📱 Responsive Design

### Mobile Optimizations
- Touch-friendly tap targets
- Readable font sizes
- Responsive tables (horizontal scroll)
- Stacked layouts on small screens

### Breakpoints
- **Mobile:** < 768px
- **Tablet:** 768px - 1024px
- **Desktop:** > 1024px

---

## 🎨 Theming & Customization

### Light Theme (Default)
- White background
- Dark text
- Blue headers (#1e40af)
- Green/Red change indicators

### Dark Theme
- Dark background (#1f2937)
- Light text
- Darker blue headers (#374151)
- Enhanced contrast for readability

### Custom Styling
Override default styles with CSS:

```css
/* Change yield value color */
.uk-yield-value {
  color: #your-brand-color !important;
}

/* Change header background */
.uk-yield-sidebar-header {
  background: #your-brand-color !important;
}

/* Custom fonts */
.uk-yield-table {
  font-family: 'Your Font', sans-serif !important;
}
```

---

## 🚀 Auto-Refresh

### Configuration
1. Enable in **Settings > Advanced > Auto-Refresh**
2. Set refresh interval (5, 15, 30, or 60 minutes)
3. Yields update automatically on frontend

### Features
- AJAX-based (no page reload)
- Smooth animations on update
- Loading indicator during refresh
- Respects current maturity/format settings

---

## 🔧 Technical Details

### Requirements
- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher (recommended)

### Performance
- Efficient transient caching
- No database queries on each page load
- Async JavaScript for auto-refresh
- Optimized CSS delivery

### Security
- Input sanitization on all user data
- Output escaping for HTML
- Nonce verification on AJAX
- Capability checks for admin actions
- XSS prevention on block editor preview

### Hooks & Filters

#### Actions
```php
// Fired after yield data is fetched
do_action('uk_yield_rates_data_fetched', $data);

// Fired after cache is refreshed
do_action('uk_yield_rates_cache_refreshed');
```

#### Filters
```php
// Modify yield data before display
$data = apply_filters('uk_yield_rates_display_data', $data);

// Modify shortcode output
$output = apply_filters('uk_yield_rates_shortcode_output', $output, $atts);
```

---

## 🐛 Bug Reporting & Support

### Report a Bug
1. Go to **Settings > UK Yield Rates**
2. Click **"Report Bug on GitHub"**
3. Fill in the structured form
4. System info auto-populated
5. Click **"Open GitHub Issue"**
6. Submit on GitHub

### Request a Feature
1. Go to **Settings > UK Yield Rates**
2. Click **"Request Feature on GitHub"**
3. Describe your idea
4. Click **"Open GitHub Issue"**
5. Submit on GitHub

### Documentation
- This README
- WordPress.org plugin page (coming soon)

---

## 📚 Installation

### Manual Installation
1. Download the plugin zip file
2. Upload to `/wp-content/plugins/uk-yield-rates/`
3. Activate through **Plugins** menu
4. Configure at **Settings > UK Yield Rates**

### WordPress Admin
1. Go to **Plugins > Add New**
2. Search for "UK Yield Rates"
3. Click **Install Now** then **Activate**
4. Configure at **Settings > UK Yield Rates**

### WP-CLI
```bash
wp plugin install uk-yield-rates --activate
```

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

#### 🔧 Technical
- Shared shortcode builder utility
- Improved CSS inheritance
- Better responsive design
- Enhanced security measures

### 1.0.0 (2026-07-28)
- Initial release
- Manual yield rate entry
- BoE Custom Endpoint integration
- FRED API integration
- Multiple display formats
- Gutenberg block
- Admin settings page
- Responsive design
- Light/Dark themes
- Auto-refresh functionality

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
