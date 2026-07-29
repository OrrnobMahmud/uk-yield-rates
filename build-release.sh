#!/bin/bash

# Build script for UK Yield Rates plugin release
# Creates a distributable ZIP file

set -e

PLUGIN_NAME="uk-yield-rates"
VERSION=$(grep "Version:" uk-yield-rates.php | head -1 | awk '{print $2}' | tr -d '\r')
RELEASE_DIR="releases"
BUILD_DIR="${RELEASE_DIR}/${PLUGIN_NAME}-${VERSION}"

echo "🚀 Building UK Yield Rates v${VERSION}..."

# Clean up any previous builds
rm -rf "${RELEASE_DIR}"
mkdir -p "${BUILD_DIR}"

# Copy plugin files (excluding development files)
echo "📦 Copying plugin files..."
rsync -av --progress \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='build-release.sh' \
  --exclude='releases' \
  --exclude='*.log' \
  --exclude='.DS_Store' \
  --exclude='Thumbs.db' \
  . "${BUILD_DIR}/"

# Install production dependencies
echo "📥 Installing production dependencies..."
cd "${BUILD_DIR}"
npm install --production --legacy-peer-deps 2>/dev/null || true

# Build the Gutenberg block
echo "🔨 Building Gutenberg block..."
npx wp-scripts build blocks/yield-rates/index.js --output-path=blocks/yield-rates/dist 2>/dev/null || {
  echo "⚠️  Warning: Could not build block. Block may need manual build."
}

# Remove development files from build
echo "🧹 Cleaning up build..."
rm -rf node_modules
rm -f package.json package-lock.json

# Create ZIP file
echo "📁 Creating ZIP archive..."
cd "${RELEASE_DIR}"
zip -r "${PLUGIN_NAME}-${VERSION}.zip" "${PLUGIN_NAME}-${VERSION}/"

# Clean up build directory
rm -rf "${BUILD_DIR}"

echo ""
echo "✅ Release build complete!"
echo ""
echo "📁 Output: ${RELEASE_DIR}/${PLUGIN_NAME}-${VERSION}.zip"
echo ""
echo "To install:"
echo "1. Go to WordPress Admin > Plugins > Add New"
echo "2. Click 'Upload Plugin'"
echo "3. Choose the ZIP file"
echo "4. Click 'Install Now' then 'Activate'"
echo ""
echo "To create a GitHub release:"
echo "1. Go to https://github.com/OrrnobMahmud/uk-yield-rates/releases"
echo "2. Click 'Create a new release'"
echo "3. Tag: v${VERSION}"
echo "4. Upload: ${RELEASE_DIR}/${PLUGIN_NAME}-${VERSION}.zip"
echo ""
