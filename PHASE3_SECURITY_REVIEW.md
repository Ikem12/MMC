# Phase 3 Security Review

Phase 3 retains the authenticated session gate, CSRF validation for every added mutation, PDO
prepared statements for request-derived values, and contextual HTML escaping.

Counsel actions are constrained to their supplied matter identifier on completion. Review creation
uses a transaction so the review and generated action plan do not partially persist. Practice-area
and knowledge-category values are allow-listed where they affect behaviour; global-search legacy
table names remain a static allow-list.

Before production, enable HTTPS-only session cookies, add role- and matter-level authorization,
implement audit retention/access controls, and establish backup and recovery procedures for SQLite.
