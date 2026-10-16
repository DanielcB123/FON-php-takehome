# NOTES

## What I found

### Part 1 bugs
- Pagination skipped records due to incorrect offset math (`$page * PER_PAGE`) and incorrect total-page math (`intdiv` dropped remainder pages).
- MRR changed by page because it was summed from only the currently displayed rows.
- Edit flow reported success but did not persist changes because the form posted `customer_id` while backend read `id`.
- Edit form plan select used the wrong key (`plan` instead of `subscription_plan`), which could change selected plan unexpectedly.
- Security issues:
  - SQL injection risk in customer list filtering due to interpolated SQL.
  - Stored XSS risk in customer list because `company_name` was rendered unescaped.

### Part 2 requests
- `status` field (`active`, `trialing`, `cancelled`) was not implemented in schema/UI/validation.
- Company-name search was not implemented and did not integrate with existing plan filter + pagination.

## What I changed and why

### Part 1 fixes
- Reworked list query building in `public/index.php` to use prepared statements and bound parameters for filter values.
- Fixed pagination:
  - page count uses `ceil($total / PER_PAGE)`,
  - offset uses `($page - 1) * PER_PAGE`.
- Recomputed MRR from the full dataset query (not paginated rows).
- Fixed edit persistence in `public/edit.php` by aligning hidden form field with backend (`name="id"`).
- Corrected edit plan selected-state binding to `subscription_plan`.
- Escaped company name output with `e()` to prevent stored XSS.

### Part 2 implementation
- Added `sql/002_add_customer_status.sql` to introduce `status` with default `active` and backfill existing rows.
- Wired migration into Docker init in `docker-compose.yml`.
- Added `STATUSES` allow-list constant in `public/db.php`.
- Added status validation and form controls in `public/add.php` and `public/edit.php`.
- Added status column display in `public/index.php`.
- Updated MRR query to exclude cancelled customers.
- Added company-name partial search (`q`) using `LIKE` with prepared params.
- Ensured search composes with plan filter and is preserved in pagination links.
- Updated filter control styling so Company and Plan controls have matching presentation.

## What I would do differently with more time
- Add session-backed flash/message handling instead of query-string messages.
  - Query-string flash messages are user-controlled and can be replayed/bookmarked, so they’re less   trustworthy as one-time UI state.
  - Session-backed flash is server-controlled and ephemeral (set once, read once), which is the standard Post/Redirect/Get pattern.
- Add CSRF token helpers (generate, embed hidden input, verify on POST) for add/edit forms.
- Add a dedicated style treatment for status pills (`active`, `trialing`, `cancelled`) for clearer visual scanning.
- Refactor duplicated validation logic between add/edit pages into shared helpers.

## Things I noticed but did not fix
- There is still no auth/authorization layer (out of scope for this take-home).
- Flash messaging is query-string based; a session-backed flash system would be more robust.
