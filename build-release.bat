@echo off
setlocal enabledelayedexpansion

REM Build script for UK Yield Rates plugin release (Windows)
REM Creates a distributable ZIP file

set PLUGIN_NAME=uk-yield-rates
for /f "tokens=2 delims=: " %%a in ('findstr "Version:" uk-yield-rates.php') do set VERSION=%%a
set VERSION=%VERSION: =%
set RELEASE_DIR=releases
set BUILD_DIR=%RELEASE_DIR%\%PLUGIN_NAME%-%VERSION%

echo 🚀 Building UK Yield Rates v%VERSION%...

REM Clean up any previous builds
if exist "%RELEASE_DIR%" rmdir /s /q "%RELEASE_DIR%"
mkdir "%RELEASE_DIR%"
mkdir "%BUILD_DIR%"

REM Copy plugin files (excluding development files)
echo 📦 Copying plugin files...
xcopy /E /I /Y /Q ".\*" "%BUILD_DIR%\"

REM Manually exclude specific folders
if exist "%BUILD_DIR%\node_modules" rmdir /s /q "%BUILD_DIR%\node_modules"
if exist "%BUILD_DIR%\.git" rmdir /s /q "%BUILD_DIR%\.git"
if exist "%BUILD_DIR%\releases" rmdir /s /q "%BUILD_DIR%\releases"
if exist "%BUILD_DIR%\.gitignore" del /f /q "%BUILD_DIR%\.gitignore"
if exist "%BUILD_DIR%\package.json" del /f /q "%BUILD_DIR%\package.json"
if exist "%BUILD_DIR%\package-lock.json" del /f /q "%BUILD_DIR%\package-lock.json"
if exist "%BUILD_DIR%\build-release.sh" del /f /q "%BUILD_DIR%\build-release.sh"
if exist "%BUILD_DIR%\build-release.bat" del /f /q "%BUILD_DIR%\build-release.bat"

REM Install production dependencies
echo 📥 Installing production dependencies...
cd "%BUILD_DIR%"
call npm install --production --legacy-peer-deps 2>nul

REM Build the Gutenberg block
echo 🔨 Building Gutenberg block...
call npx wp-scripts build blocks/yield-rates/index.js --output-path=blocks/yield-rates/dist 2>nul
if errorlevel 1 (
    echo ⚠️  Warning: Could not build block. Block may need manual build.
)

REM Remove development files from build
echo 🧹 Cleaning up build...
if exist "%BUILD_DIR%\node_modules" rmdir /s /q "%BUILD_DIR%\node_modules"
if exist "%BUILD_DIR%\package.json" del /f /q "%BUILD_DIR%\package.json"
if exist "%BUILD_DIR%\package-lock.json" del /f /q "%BUILD_DIR%\package-lock.json"

REM Create ZIP file
echo 📁 Creating ZIP archive...
cd "%RELEASE_DIR%"
powershell -command "Compress-Archive -Path '%PLUGIN_NAME%-%VERSION%' -DestinationPath '%PLUGIN_NAME%-%VERSION%.zip' -Force"

REM Clean up build directory
rmdir /s /q "%BUILD_DIR%"

echo.
echo ✅ Release build complete!
echo.
echo 📁 Output: %RELEASE_DIR%\%PLUGIN_NAME%-%VERSION%.zip
echo.
echo To install:
echo 1. Go to WordPress Admin ^> Plugins ^> Add New
echo 2. Click 'Upload Plugin'
echo 3. Choose the ZIP file
echo 4. Click 'Install Now' then 'Activate'
echo.
echo To create a GitHub release:
echo 1. Go to https://github.com/OrrnobMahmud/uk-yield-rates/releases
echo 2. Click 'Create a new release'
echo 3. Tag: v%VERSION%
echo 4. Upload: %RELEASE_DIR%\%PLUGIN_NAME%-%VERSION%.zip
echo.

pause
