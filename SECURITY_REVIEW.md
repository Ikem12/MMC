# Security Review

## Added safeguards
- Session-based authenticated-page guard in `includes/auth.php`.
- Secure cookie flags: `HttpOnly`, `SameSite=Lax`, and `Secure` automatically when HTTPS is active.
- CSRF tokens required for all new create/edit forms.
- Prepared statements for all new data writes and record retrieval by identifier.
- Server-side input trimming, length limits, status allow-lists, date validation, and email validation.
- Central HTML escaping using `aep_h()`.
- Foreign-key enforcement enabled for the shared SQLite connection.
- Activity logging for create and update actions in new modules.

## Existing-module compatibility
Legacy modules were retained unchanged to avoid disrupting current workflows. They should be incrementally migrated to shared CSRF and layout helpers in a follow-up hardening release.

## Deployment guidance
- Use HTTPS for remote access.
- Keep the database directory outside a public web root in production.
- Set `secure` cookies through HTTPS.
- Change default or weak user passwords.
- Do not expose the PHP development server directly to the public internet.
