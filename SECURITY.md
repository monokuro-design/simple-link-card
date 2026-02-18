# Security Policy

## Reporting Vulnerabilities

If you discover a security vulnerability within the Simple Link Embed plugin, please report it responsibly. Your efforts to keep the WordPress ecosystem safe are greatly appreciated.

### How to Report

1. **Do not create public issues** for security vulnerabilities
2. Send an email to: security@monopedia.jp
3. Include as much detail as possible:
   - Steps to reproduce the vulnerability
   - Potential impact of the vulnerability
   - Proof of concept (if applicable)
   - Your WordPress version and environment details

### Response Timeline

- **Initial response**: Within 48 hours
- **Investigation**: Within 7 days
- **Resolution**: Depending on severity, within 30 days
- **Public disclosure**: After a fix has been released

## Supported Versions

Security updates are provided for the following versions:

| Version | Supported          |
|---------|--------------------|
| 1.x.x   | :white_check_mark: Yes |

## Security Best Practices Implemented

This plugin follows WordPress security best practices:

### Input Validation
- All URLs are validated using `filter_var()` with `FILTER_VALIDATE_URL`
- Only HTTP/HTTPS schemes are allowed
- Whitelist validation for block attributes

### Output Escaping
- All output is escaped using WordPress escaping functions:
  - `esc_url()` for URLs
  - `esc_html()` for text content
  - `esc_attr()` for HTML attributes

### SSRF Protection
- Blocks access to localhost and loopback addresses (127.0.0.1, ::1)
- Blocks private IP ranges (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16)
- Blocks cloud metadata endpoints (169.254.169.254)
- Only HTTP/HTTPS protocols allowed

### Rate Limiting
- REST API endpoint rate limited to 30 requests per minute per user
- Uses Transient API for rate limit tracking

### Access Control
- OGP fetching requires `publish_posts` capability (Author role and above)
- Cache management requires `manage_options` capability (Administrator)
- All REST API endpoints include permission callbacks

### Secure HTTP Requests
- SSL verification enabled for external requests
- Custom User-Agent for identification
- Response size limited to 500KB

### Sanitization
- All user inputs are sanitized before processing
- URL sanitization using `esc_url_raw()`
- Boolean attributes properly cast to boolean type

### Cache Security
- Cache keys use MD5 hashing to prevent key collisions
- Transient API used for secure data storage
- No sensitive data stored in cache

## Security Update Policy

### Critical Vulnerabilities (CVSS 9.0+)
- Patch released within 48 hours of disclosure
- Security advisory published immediately
- Automatic update recommended

### High Vulnerabilities (CVSS 7.0-8.9)
- Patch released within 7 days
- Security advisory published with patch

### Medium Vulnerabilities (CVSS 4.0-6.9)
- Patch released within 30 days
- Included in next release notes

### Low Vulnerabilities (CVSS 0.1-3.9)
- Addressed in next scheduled release
- Documented in release notes

## Security-Related Configuration

### Minimum WordPress Version
- WordPress 5.8 or higher (for block editor support)
- PHP 7.4 or higher

### Dependencies
- Uses WordPress core functions only
- No external PHP dependencies

### Data Privacy
- No personal data is collected or stored
- No data is transmitted to external services except:
  - Fetching OGP data from user-provided URLs
  - Google favicon service (for favicon display)

## Security Audits

This plugin follows the WordPress Plugin Security Best Practices:
- https://developer.wordpress.org/apis/security/
- https://make.wordpress.org/core/handbook/testing/automated-testing/security-queries/

## Security Contact

For security-related questions not involving vulnerability disclosure:
- Email: security@monopedia.jp
- Subject: [Simple Link Embed] Security Inquiry

## Acknowledgments

We thank all security researchers who responsibly disclose vulnerabilities to help keep this plugin secure.
