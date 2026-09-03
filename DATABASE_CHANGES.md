# Database Changes

All changes use `CREATE TABLE IF NOT EXISTS`; existing SQLite data is preserved.

## New tables
| Table | Purpose |
| --- | --- |
| `clients` | Core client details and reference |
| `matters` | Client-linked legal matters and practice area |
| `tasks` | Matter-linked work items |
| `deadlines` | Matter-linked due dates and priority |
| `activity_log` | User activity audit entries |

## Relationships
`clients (1) -> matters (many) -> tasks/deadlines (many)`.

## Indexes
Indexes support client/matter lookup, task matter lookup, deadlines by date, and activity chronology.

## Migration behavior
The schema is automatically created when a new shared-platform page is opened. It is also included in `init_db.php` for clean installations.
