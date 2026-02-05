# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within Watchtower, please send an email to <security@example.com> (replace with your actual security contact).

**Please do not report security vulnerabilities through public GitHub issues.**

### What to Include

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Response Timeline

- Initial response: within 48 hours
- Status update: within 7 days
- Fix timeline: depends on severity

### Disclosure Policy

We follow responsible disclosure practices:

1. Reporter notifies us privately
2. We confirm and assess the issue
3. We develop and test a fix
4. We release the fix
5. We publicly disclose with credit to reporter (if desired)

## Security Best Practices

When using Watchtower:

1. **Authorization Gate**: Always configure the `viewWatchtower` gate in production
2. **Middleware**: Use appropriate authentication middleware
3. **Network Access**: Restrict dashboard access to internal networks if possible
4. **Redis Security**: Secure your Redis instance with authentication
