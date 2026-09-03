# AEP Legal Intelligence Platform Upgrade

## Added capabilities
- Unified workspace dashboard with upcoming deadlines, recent activity, quick actions, practice areas, and Counsel Engine access.
- Client management: create, list, view, and edit client records.
- Matter management: link clients to matters, legal practice areas, tasks, deadlines, and Counsel Engine.
- Task management: create, list, view, and edit tasks.
- Deadline creation and dashboard tracking.
- Knowledge Centre that brings together the library, templates, phrase bank, Latin Maxims, domain map, and Counsel Engine.
- Click-through subdomain routing from the domain map, templates, and workbench to focused analysis/template paths.

## Shared infrastructure
- `includes/database.php`: SQLite connection, schema migration, foreign keys, and indexes.
- `includes/functions.php`: output escaping, CSRF tokens, validation helpers, references, and activity logging.
- `includes/auth.php`: shared authenticated-page guard.
- `includes/header.php` and `includes/footer.php`: reusable responsive layout.

## Existing platform preservation
Existing legal-domain modules, templates, workbenches, SQLite data, and URLs remain in place. New modules add a management layer rather than migrating or deleting existing records.
