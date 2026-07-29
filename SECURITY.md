# Security Policy

## Reporting Security Vulnerabilities

**If you discover a security vulnerability in UK Yield Rates Live, please report it responsibly.**

### How to Report

**DO NOT** open a public GitHub issue for security vulnerabilities.

Instead, please report security issues via GitHub's private vulnerability reporting:

1. **GitHub Security Advisory**: Use [GitHub's private vulnerability reporting](https://github.com/OrrnobMahmud/uk-yield-rates/security/advisories/new)

### What to Include

Please provide:

- **Description** of the vulnerability
- **Steps to reproduce** the issue
- **Potential impact** (what could an attacker do?)
- **Suggested fix** (if you have one)
- **Your contact information** (for follow-up)

### Response Timeline

- **Acknowledgment**: Within 48 hours
- **Initial assessment**: Within 5 business days
- **Fix timeline**: Depends on severity (critical = immediate, moderate = next release)
- **Disclosure**: After fix is available

---

## Security Measures

### Input Sanitization

All user input is sanitized to prevent XSS and injection attacks:

```php
// PHP - Sanitization
$sanitized = sanitize_text_field($raw_input);
$escaped = esc_html($user_content);
$attr_escaped = esc_attr($attribute);
```

```javascript
// JavaScript - DOMParser sanitization
function sanitizeHtml(html) {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    return doc.body.textContent || '';
}
```

### Output Escaping

All output is escaped to prevent XSS:

```php
// Always escape output
echo esc_html($yield_value);
echo esc_attr($attribute);
echo esc_url($url);
echo esc_js($javascript_string);
```

### Nonce Verification

AJAX requests are protected with WordPress nonces:

```php
// Verify nonce on every AJAX call
check_ajax_referer('uk_yield_rates_nonce', 'nonce');
```

### Capability Checks

Admin actions require proper WordPress capabilities:

```php
// Only administrators can access settings
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

### Database Security

- No direct SQL queries (uses WordPress API)
- Prepared statements for all queries
- Input validation before storage
- Output escaping on retrieval

### API Security

- **HTTPS only** for all API requests
- **API keys** stored in WordPress options (not in code)
- **No sensitive data** in URLs or logs
- **Error handling** that doesn't expose internals

### File Security

- **No direct file access** (`ABSPATH` check on all PHP files)
- **No file uploads** from users
- **No file inclusion** from user input
- **No eval()** or dynamic code execution

---

## Vulnerability Classes

We actively protect against:

### Cross-Site Scripting (XSS)

- **Stored XSS**: Malicious code stored in database
- **Reflected XSS**: Code in URLs or form submissions
- **DOM-based XSS**: Client-side manipulation

**Mitigations:**
- All output escaped
- Input sanitized
- Content Security Policy headers
- HTTPOnly cookies

### SQL Injection

**Mitigations:**
- No direct SQL queries
- WordPress `$wpdb->prepare()` for all queries
- Input validation

### Cross-Site Request Forgery (CSRF)

**Mitigations:**
- WordPress nonces on all forms
- AJAX nonce verification
- Referer checking

### Remote Code Execution

**Mitigations:**
- No `eval()` usage
- No dynamic code execution
- No file inclusion from user input
- No `unserialize()` on user data

### Privilege Escalation

**Mitigations:**
- Capability checks on all admin actions
- No direct option manipulation
- Proper permission levels

---

## Data Handling

### What We Store

- **Yield rates**: Numeric values only
- **Settings**: Configuration options
- **No user data**: Plugin doesn't collect personal information
- **No tracking**: No analytics or user tracking

### What We Don't Store

- ❌ Personal information
- ❌ Credit card details
- ❌ Authentication credentials
- ❌ API keys in logs

### Data Transmission

- **HTTPS only** for external API calls
- **No data sent** to third parties
- **No telemetry** or usage tracking

---

## Third-Party Dependencies

### External APIs

| API | Purpose | Data Sent | Data Received |
|-----|---------|-----------|---------------|
| Bank of England | Yield data | None | Public yield rates |
| FRED | Yield data | API key | Public yield rates |

### No Third-Party Scripts

- ❌ No Google Analytics
- ❌ No Facebook Pixel
- ❌ No tracking scripts
- ❌ No advertising code
- ❌ No cryptocurrency miners

---

## Configuration Security

### Recommended WordPress Settings

```php
// wp-config.php - Security hardening
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_AUTO_UPDATE_CORE', 'minor');
```

### Apache/.htaccess

```apache
# Protect wp-config.php
<Files wp-config.php>
    Order Allow,Deny
    Deny from all
</Files>

# Protect .htaccess
<Files .htaccess>
    Order Allow,Deny
    Deny from all
</Files>
```

### Nginx

```nginx
# Block access to sensitive files
location ~ /\.ht {
    deny all;
}

location ~* wp-config\.php {
    deny all;
}
```

---

## API Key Security

### FRED API Key

- Stored in `wp_options` table
- Never logged or displayed in HTML
- Transmitted via HTTPS only
- Can be removed anytime

### Best Practices

1. **Don't commit API keys** to Git
2. **Use environment variables** when possible
3. **Rotate keys** periodically
4. **Monitor usage** for anomalies

---

## Content Security Policy (CSP)

If your site implements CSP headers, you may need to allow:

```apache
# Apache - Add to .htaccess
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';"
```

```nginx
# Nginx - Add to server block
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';";
```

### Required CSP Directives

- `script-src 'unsafe-inline'`: Required for inline scripts
- `style-src 'unsafe-inline'`: Required for inline styles
- `connect-src 'self'`: Required for AJAX requests

---

## Audit Logging

### What We Log

- Settings changes (WordPress revision history)
- Error messages (WP_DEBUG_LOG)
- No sensitive data in logs

### Log Files

- `wp-content/debug.log`: WordPress debug log
- No custom log files

---

## Compliance

### GDPR

- **No personal data** collected
- **No cookies** set by plugin
- **No tracking** scripts
- **No third-party** data sharing

### CCPA

- **No data sale** to third parties
- **No personal information** collected

### Accessibility

- **WCAG 2.1 AA** compliant
- **Semantic HTML** throughout
- **Screen reader** compatible
- **Keyboard accessible**

---

## Update Policy

### Security Updates

- **Critical**: Within 24-48 hours
- **High**: Within 1 week
- **Medium**: Next release
- **Low**: As available

### Update Channels

- **WordPress.org**: Official releases
- **GitHub**: Development releases

### Auto-Updates

- **Minor releases**: Auto-update enabled by default
- **Major releases**: Manual update required

---

## Testing Security

### Manual Testing

1. **Input validation**: Try malicious inputs
2. **XSS testing**: Attempt script injection
3. **Privilege escalation**: Test unauthorized access
4. **API testing**: Verify authentication

### Automated Testing

```bash
# PHP CodeSniffer for WordPress
composer install
./vendor/bin/phpcs --standard=WordPress-Extra

# PHPStan for static analysis
./vendor/bin/phpstan analyse

# npm audit for JavaScript dependencies
npm audit
```

### Security Tools

- **WPScan**: WordPress vulnerability scanner
- **PHP CodeSniffer**: Coding standards
- **PHPStan**: Static analysis
- **npm audit**: Dependency vulnerabilities

---

## Secure Development

### Code Review

- All code reviewed before merge
- Security-focused review for sensitive changes
- Automated testing required

### Git Hooks

```bash
# Pre-commit hook
#!/bin/bash
# Run PHP lint
find . -name "*.php" -exec php -l {} \;

# Run PHPCS
./vendor/bin/phpcs --standard=WordPress-Extra
```

### Branch Protection

- **main branch**: Protected
- **Pull requests**: Required
- **Code review**: Required
- **CI checks**: Required

---

## Incident Response

### If Vulnerability Found

1. **Immediate**: Assess severity
2. **1-24 hours**: Develop fix
3. **24-48 hours**: Release patch
4. **48-72 hours**: Public disclosure
5. **Post-mortem**: Document and improve

### Communication

- **Email**: Direct notification to reporter
- **GitHub**: Security advisory
- **WordPress.org**: Plugin update notice
- **README.md**: Security changelog

---

## Contact Information

### Security Team

- **GitHub**: [@OrrnobMahmud](https://github.com/OrrnobMahmud)

### Response Hours

- **Monday-Friday**: 9am-6pm BST
- **Weekend**: Emergency only
- **Holidays**: Limited

---

## Acknowledgments

We thank the following for responsibly reporting vulnerabilities:

*(List will be updated as reports are received)*

---

## Resources

- [WordPress Security Codex](https://codex.wordpress.org/Security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Plugin Security Best Practices](https://developer.wordpress.org/plugins/wordpress-org/how-to-handle-security-issues/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)

---

## License

This security policy is licensed under the GPL v2 or later.

---

**Last updated**: 2026-07-30
**Version**: 1.3.1

Thank you for helping keep UK Yield Rates Live secure! 🔒
