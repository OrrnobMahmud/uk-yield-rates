# Contributing to UK Yield Rates Live

Thank you for your interest in contributing to UK Yield Rates Live! This document provides guidelines and instructions for contributing.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Style Guide](#style-guide)
- [Testing](#testing)
- [License](#license)

---

## Code of Conduct

### Our Pledge

We are committed to making participation in this project a harassment-free experience for everyone, regardless of level of experience, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, or religion.

### Our Standards

Examples of behavior that contributes to creating a positive environment include:

- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

Examples of unacceptable behavior include:

- Trolling, insulting/derogatory comments, and personal or political attacks
- Public or private harassment
- Publishing others' private information without explicit permission
- Other conduct which could reasonably be considered inappropriate in a professional setting

---

## How to Contribute

### 🐛 Reporting Bugs

1. **Check existing issues** - Search [GitHub Issues](https://github.com/OrrnobMahmud/uk-yield-rates/issues) to avoid duplicates
2. **Use the bug report form** - Go to Settings > UK Yield Rates > Report Bug
3. **Include system information** - The form auto-populates this for you
4. **Provide reproduction steps** - Be specific and detailed

**Bug Report Template:**
```markdown
## Bug Description
[Clear, concise description]

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

## Expected Behavior
[What you expected to happen]

## Actual Behavior
[What actually happened]

## System Information
- Plugin Version: [e.g., 1.1.0]
- WordPress Version: [e.g., 6.6]
- PHP Version: [e.g., 8.2]
- Active Theme: [e.g., Twenty Twenty-Four]
- Other Plugins: [List any relevant plugins]

## Screenshots
[If applicable, add screenshots]

## Additional Context
[Any other information]
```

### 💡 Suggesting Features

1. **Check existing discussions** - Search [GitHub Discussions](https://github.com/OrrnobMahmud/uk-yield-rates/discussions)
2. **Use the feature request form** - Go to Settings > UK Yield Rates > Request Feature
3. **Explain the use case** - Why is this feature needed?
4. **Provide examples** - How would it work?

**Feature Request Template:**
```markdown
## Feature Description
[Clear description of the feature]

## Use Case
[Why this feature would be useful]

## Proposed Solution
[How you think it could work]

## Alternatives Considered
[Other approaches you've thought about]

## Additional Context
[Any other information, mockups, or examples]
```

### 🔧 Submitting Code

1. **Fork the repository** on GitHub
2. **Create a feature branch** from `main`
3. **Make your changes** following our coding standards
4. **Test thoroughly** (see [Testing](#testing))
5. **Commit with clear messages** (see [Commit Messages](#commit-messages))
6. **Push to your fork**
7. **Create a Pull Request** with detailed description

---

## Development Setup

### Prerequisites

- [PHP](https://php.net/) 7.4 or higher
- [MySQL](https://mysql.com/) 5.6 or higher
- [WordPress](https://wordpress.org/) 5.0 or higher
- [Node.js](https://nodejs.org/) 16+ (for block editor)
- [Git](https://git-scm.com/)

### Local Development

1. **Clone the repository:**
   ```bash
   git clone https://github.com/OrrnobMahmud/uk-yield-rates.git
   cd uk-yield-rates
   ```

2. **Set up WordPress:**
   - Use [Local by Flywheel](https://localwp.com/), [XAMPP](https://www.apachefriends.org/), or [Docker](https://www.docker.com/)
   - Install WordPress and activate the plugin

3. **Install dependencies (for block editor):**
   ```bash
   npm install
   ```

4. **Start development:**
   ```bash
   npm run start
   ```

5. **Build for production:**
   ```bash
   npm run build
   ```

### File Structure

```
uk-yield-rates/
├── admin/                    # Admin interface
│   ├── css/
│   │   └── admin.css        # Admin styles
│   ├── js/
│   │   └── admin.js         # Admin JavaScript
│   └── views/
│       └── settings-page.php # Settings page template
├── blocks/                   # Gutenberg block
│   └── yield-rates/
│       ├── edit.js           # Block editor component
│       ├── save.js           # Block save function
│       ├── shortcode-builder.js # Shared utility
│       ├── editor.css        # Editor styles
│       └── index.js          # Block registration
├── includes/                  # PHP classes
│   ├── class-uk-yield-api.php      # API handler
│   ├── class-uk-yield-cache.php    # Cache handler
│   ├── class-uk-yield-shortcode.php # Shortcode handler
│   ├── class-uk-yield-admin.php    # Admin handler
│   └── class-uk-yield-block.php    # Block handler
├── languages/                 # Translation files
├── public/                    # Frontend assets
│   ├── css/
│   │   └── yield-rates.css   # Frontend styles
│   └── js/
│       └── yield-rates.js    # Frontend JavaScript
├── sample-endpoint/           # Example Cloudflare Worker
├── templates/                 # Template files
├── CHANGELOG.md              # Changelog
├── CONTRIBUTING.md           # This file
├── LICENSE                   # GPL v2 license
├── README.md                 # Main documentation
├── readme.txt                # WordPress.org readme
├── SECURITY.md               # Security policy
├── package.json              # Node.js dependencies
└── uk-yield-rates.php        # Main plugin file
```

---

## Coding Standards

### PHP Standards

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

```php
<?php
/**
 * Plugin Name: UK Yield Rates Live
 *
 * @package UK_Yield_Rates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class UK_Yield_Rates
 *
 * Main plugin class.
 *
 * @since 1.0.0
 */
class UK_Yield_Rates {

    /**
     * Single instance of the class.
     *
     * @var UK_Yield_Rates|null
     */
    private static $instance = null;

    /**
     * Get single instance.
     *
     * @return UK_Yield_Rates
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     *
     * @return void
     */
    private function init_hooks() {
        add_action('init', [$this, 'init']);
    }
}
```

### JavaScript Standards

Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/):

```javascript
/**
 * UK Yield Rates - Frontend JavaScript
 *
 * @package UK_Yield_Rates
 */

(function() {
    'use strict';

    /**
     * Initialize yield rates functionality.
     *
     * @return void
     */
    function initYieldRates() {
        // Your code here
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYieldRates);
    } else {
        initYieldRates();
    }
})();
```

### CSS Standards

Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/):

```css
/**
 * UK Yield Rates - Frontend Styles
 *
 * @package UK_Yield_Rates
 */

/* Base styles */
.uk-yield-inline {
    display: inline;
    white-space: nowrap;
    /* Inherit text styles from parent */
    font: inherit;
    color: inherit;
    font-weight: inherit;
    font-size: inherit;
}

/* Component styles */
.uk-yield-sidebar {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Responsive styles */
@media (max-width: 768px) {
    .uk-yield-sidebar {
        max-width: 100%;
    }
}
```

### Naming Conventions

**PHP:**
- Classes: `UK_Yield_Rates` (PascalCase)
- Methods: `get_instance()` (snake_case)
- Variables: `$yield_data` (snake_case)
- Constants: `UK_YIELD_RATES_VERSION` (UPPER_SNAKE_CASE)
- Hooks: `uk_yield_rates_data_fetched` (snake_case, prefixed)

**JavaScript:**
- Functions: `initYieldRates()` (camelCase)
- Variables: `yieldData` (camelCase)
- Constants: `MAX_RETRIES` (UPPER_SNAKE_CASE)
- Classes: `YieldRates` (PascalCase)

**CSS:**
- Classes: `.uk-yield-sidebar` (kebab-case, prefixed)
- Variables: `--uk-yield-primary-color` (kebab-case, prefixed)

### Naming Prefix

All plugin assets must use the `uk-yield-` prefix to avoid conflicts:

- **PHP**: `UK_Yield_*` classes
- **JavaScript**: `ukYield*` functions/variables
- **CSS**: `.uk-yield-*` classes
- **HTML**: `data-uk-yield-*` attributes
- **Options**: `uk_yield_rates_*` database options

---

## Pull Request Process

### 1. Create a Branch

```bash
# For features
git checkout -b feature/amazing-feature

# For bug fixes
git checkout -b fix/bug-description

# For documentation
git checkout -b docs/update-readme
```

### 2. Make Changes

- Follow coding standards
- Add comments for complex logic
- Update documentation if needed
- Add tests if applicable

### 3. Test Your Changes

```bash
# PHP syntax check
php -l includes/class-uk-yield-api.php

# Run tests (if available)
npm test

# Manual testing in WordPress
```

### 4. Commit Changes

Write clear, concise commit messages:

```bash
# Good
git commit -m "Fix auto-refresh maturity parameter

- Send maturity and format in AJAX request
- Update JavaScript to include POST parameters
- Resolves #123"

# Bad
git commit -m "fix stuff"
```

### 5. Push and Create PR

```bash
git push origin feature/amazing-feature
```

Then create a Pull Request on GitHub with:

- **Title**: Clear, concise description
- **Description**: Detailed explanation of changes
- **Screenshots**: If UI changes
- **Testing Steps**: How to test the changes
- **Related Issues**: Link to any related issues

### 6. Code Review

- Respond to feedback promptly
- Make requested changes
- Keep PR focused and small
- Squash commits if requested

### 7. Merge

Once approved, your PR will be merged into `main`.

---

## Commit Messages

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting, semicolons, etc.)
- **refactor**: Code refactoring
- **perf**: Performance improvements
- **test**: Adding tests
- **chore**: Maintenance tasks
- **revert**: Reverting changes

### Examples

```bash
# Feature
git commit -m "feat(shortcode): add maturity attribute support"

# Bug fix
git commit -m "fix(cache): resolve weekend cache duration override"

# Documentation
git commit -m "docs(readme): update installation instructions"

# Breaking change
git commit -m "feat(api)!: change endpoint response format

BREAKING CHANGE: API response now uses camelCase instead of snake_case"
```

### Rules

- Use imperative mood ("add feature" not "added feature")
- Keep subject line under 72 characters
- Reference issues and PRs
- Explain **what** and **why**, not **how**

---

## Testing

### Manual Testing Checklist

**Before submitting a PR, verify:**

- [ ] Plugin activates without errors
- [ ] Settings page loads correctly
- [ ] All data sources work (Manual, BoE, FRED)
- [ ] All display formats render properly
- [ ] Gutenberg block works in editor
- [ ] Auto-refresh functions correctly
- [ ] No PHP errors or warnings
- [ ] No JavaScript console errors
- [ ] Mobile responsive layout works
- [ ] Dark theme displays correctly
- [ ] Text inheritance works in different contexts

### Test Cases

**Shortcode Testing:**
```php
// Test all formats
[uk_yield_rates]
[uk_yield_rates format="inline"]
[uk_yield_rates format="sidebar"]
[uk_yield_rates format="table"]
[uk_yield_rates format="compact"]

// Test all maturities
[uk_yield_rates maturity="all"]
[uk_yield_rates maturity="10"]
[uk_yield_rates maturity="2,5,10"]

// Test in different contexts
<h1>[uk_yield_rates inline="yes" maturity="10"]</h1>
<h2>[uk_yield_rates inline="yes" maturity="10"]</h2>
<p>[uk_yield_rates inline="yes" maturity="10"]</p>
<strong>[uk_yield_rates inline="yes" maturity="10"]</strong>
```

**Auto-Refresh Testing:**
1. Enable auto-refresh in settings
2. Set interval to 1 minute
3. Load frontend page
4. Wait for refresh
5. Verify yield values update
6. Check loading indicator appears

**Block Editor Testing:**
1. Create new post
2. Add UK Yield Rates block
3. Test all preview modes
4. Test theme switching
5. Test all format options
6. Verify copy shortcode works

### Browser Testing

Test in:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile Safari (iOS)
- Chrome for Android

---

## Style Guide

### PHP

- Use strict types: `declare(strict_types=1);`
- Use type hints: `function get_yield(string $maturity): float`
- Use PHPDoc blocks for all functions
- Keep functions focused and small
- Avoid deep nesting (max 3 levels)
- Use early returns

### JavaScript

- Use strict mode: `'use strict';`
- Use ES6+ features when supported
- Avoid jQuery dependencies (use vanilla JS when possible)
- Use semantic variable names
- Keep functions pure when possible
- Handle errors gracefully

### CSS

- Use BEM-like naming: `.uk-yield-sidebar__header`
- Keep specificity low
- Use CSS variables for colors
- Mobile-first responsive design
- Avoid `!important` (except for user overrides)
- Use meaningful class names

### Documentation

- Write clear, concise comments
- Explain complex algorithms
- Document all public APIs
- Include code examples
- Keep documentation up-to-date
- Use markdown for formatting

---

## License

By contributing to UK Yield Rates Live, you agree that your contributions will be licensed under the [GPL v2 or later](LICENSE).

---

## Questions?

If you have questions about contributing:

1. **Check the documentation** - [README.md](README.md)
2. **Search existing issues** - [GitHub Issues](https://github.com/OrrnobMahmud/uk-yield-rates/issues)
3. **Open a discussion** - [GitHub Discussions](https://github.com/OrrnobMahmud/uk-yield-rates/discussions)
4. **Contact the author** - [orrnobmahmud.com](https://orrnobmahmud.com)

Thank you for contributing! 🎉
