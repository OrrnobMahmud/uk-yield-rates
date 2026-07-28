# Installation Guide

## ⚠️ Important Note

**This plugin is NOT yet available on WordPress.org.** You must install it manually from GitHub.

## Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Node.js 16+ (for building the Gutenberg block)
- npm (comes with Node.js)

## Installation Methods

### Method 1: From GitHub (Recommended)

#### Step 1: Download from GitHub

1. Go to: https://github.com/OrrnobMahmud/uk-yield-rates
2. Click the green "Code" button
3. Select "Download ZIP"
4. Save the ZIP file to your computer

#### Step 2: Extract and Build

1. Extract the ZIP file
2. Open terminal/command prompt
3. Navigate to the extracted folder:
   ```bash
   cd uk-yield-rates
   ```
4. Install dependencies:
   ```bash
   npm install
   ```
5. Build the Gutenberg block:
   ```bash
   npm run build
   ```

#### Step 3: Upload to WordPress

1. Create a ZIP file of the entire `uk-yield-rates` folder
2. In WordPress admin, go to **Plugins > Add New**
3. Click **Upload Plugin**
4. Choose the ZIP file you created
5. Click **Install Now**
6. Click **Activate**

### Method 2: Manual Installation (For Developers)

#### Step 1: Clone the Repository

```bash
git clone https://github.com/OrrnobMahmud/uk-yield-rates.git
```

#### Step 2: Install Dependencies and Build

```bash
cd uk-yield-rates
npm install
npm run build
```

#### Step 3: Link to WordPress

**Option A: Copy to plugins folder**
```bash
# Copy the folder to your WordPress installation
cp -r uk-yield-rates /path/to/wordpress/wp-content/plugins/
```

**Option B: Create symbolic link (Linux/Mac)**
```bash
ln -s /path/to/uk-yield-rates /path/to/wordpress/wp-content/plugins/uk-yield-rates
```

**Option C: Create symbolic link (Windows)**
```cmd
mklink /D "C:\path\to\wordpress\wp-content\plugins\uk-yield-rates" "C:\path\to\uk-yield-rates"
```

#### Step 4: Activate Plugin

1. In WordPress admin, go to **Plugins**
2. Find "UK Yield Rates Live"
3. Click **Activate**

## Post-Installation Setup

1. Go to **Settings > UK Yield Rates**
2. Configure your preferred data source:
   - **Manual Entry** (Recommended) - Enter yields manually
   - **BoE Custom Endpoint** - Automatic updates
   - **FRED API** - Free API option
3. Set your display preferences
4. Use shortcodes or Gutenberg block to display yields

## Shortcode Usage

```php
// Basic inline display
[uk_yield_rates]

// Single maturity
[uk_yield_rates inline="yes" maturity="10"]

// Table format
[uk_yield_rates format="table"]

// Sidebar widget
[uk_yield_rates format="sidebar"]
```

## Troubleshooting

### Plugin doesn't appear after installation

1. Make sure you extracted the ZIP correctly
2. Check that `uk-yield-rates.php` is in the root of the plugin folder
3. Try deactivating and reactivating

### Gutenberg block not working

1. Make sure you ran `npm run build`
2. Check that `blocks/yield-rates/dist/` folder exists
3. Clear your browser cache

### Yields not displaying

1. Go to **Settings > UK Yield Rates**
2. Make sure you've entered yield data (if using Manual mode)
3. Check that your shortcode is correct

## Need Help?

- **GitHub Issues**: https://github.com/OrrnobMahmud/uk-yield-rates/issues
- **Documentation**: See README.md in the plugin folder
