# Matter System Stabilisation Report

## Root cause

`matters.php` required itself, so PHP treated the second inclusion as already loaded and rendered no controller or page. `matter_view.php` had the same self-reference. Matter creation, lookup, workspace rendering, and legacy view links therefore had no executable implementation despite the Phase 2 tables and dashboard integrations being present.

## Stabilisation delivered

- Replaced the self-referential matter entry point with authenticated matter list, create, update, lookup, and workspace handling.
- Matter creation is transactional and persists the matter plus a `matter.created` activity event.
- References are allocated as `AEP-MAT-YYYY-NNNN`, unique, and protected by SQLite insert/update validation triggers.
- Restored legacy `matter_view.php` as a safe redirect to canonical workspace URLs.
- Added ID and canonical-reference lookup, 400/404 professional error pages, and server-side validation for client, title, status, dates, deadlines, and timeline entries.
- Added workspace overview, details, timeline, activity, documents, deadlines, tasks, and research tabs.
- Confirmed the existing dashboard's **Recent matters** card and **Open matters** metric now link to the functional canonical workspace.

## Files changed

- `phase2.php` — schema indexes/triggers, foreign-key mode, matter service and validation helpers, error page.
- `matters.php` — matter controller and workspace UI.
- `matter_view.php` — backwards-compatible redirect.
- `assets/phase2.css` — details and professional error-page presentation.
- `tests/matter_system_test.php` — isolated sample persistence/validation test.

## Database behaviour

The migration is additive. It creates/retains `p2_matters`, `p2_activity`, and `p3_timeline`, enables foreign keys for the workspace connection, adds a reference index, and adds triggers which reject non-canonical new or changed references. It does not modify existing matter rows.

## Exact isolated test

Run from the repository root:

```powershell
php tests\matter_system_test.php
```

The test uses `sqlite::memory:` only. It creates a sample client and matter, verifies canonical reference allocation, persistence by ID/reference, creation activity, and database rejection of an invalid reference. It does **not** open or write `data\aep.sqlite`.

## Outstanding

No production-data migration was performed: pre-existing matters with blank or legacy references remain unchanged to avoid altering live records. They should be reviewed and assigned canonical references in a separately approved data-cleanup operation.
