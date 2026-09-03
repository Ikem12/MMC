# Refactor Report

## Findings
The original application repeats:
- session startup and login checks;
- direct SQLite PDO construction;
- inline styles, navigation bars, and page shells;
- one-off form parsing and output escaping;
- list/create/view/edit page patterns.

## Completed refactor
The new operational modules use a common database connection, authenticated-page guard, CSRF helper, escaping helper, shared header/footer, and activity logger.

## Deliberately retained legacy code
The legal-domain modules use varied field names and table shapes. Replacing all legacy code in one migration risks breaking existing records and working routes. They remain operational and can be safely moved to the shared includes in staged follow-up work.

## Navigation redesign
The workspace navigation reduces primary links to Workspace, Clients, Matters, Tasks, Knowledge Centre, and Counsel Engine. Existing legal modules remain available through Practice Areas and the Knowledge Centre.
