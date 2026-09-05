# Phase 3 Architecture Report

The upgrade continues the additive workspace model: the shared `phase2.php` service owns PDO,
schema migration, CSRF, escaping, layout, and reusable deadline/workspace presentation helpers.
This preserves all existing drafting tables and routes.

Phase 3 adds a single focused relation, `p3_counsel_actions`, linked to existing matters and
Counsel Engine reviews. The dashboard, workspaces, search, and reports consume this relation
without duplicating case data. Tab selection remains GET-only; all state changes remain POST plus
CSRF protection.

The next architectural step should be versioned migration files and a service/repository layer,
followed by automated request-level tests.
