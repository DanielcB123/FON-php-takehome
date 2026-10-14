# PHP Take-Home: Subscription Admin

Thanks for taking the time to do this. Please **time-box yourself to about 60 minutes**. We're not looking for perfection; we want to see how you read unfamiliar code, how you fix things, and how you communicate what you did.

## The scenario

This is a small internal tool our support team uses to look up customers and their subscription plans. It was written in a hurry, and a few things are broken. Support has also asked for two small features.

Stack: plain PHP 8 (PDO), MySQL 8, HTML/CSS. No frameworks, no build step. Keep it that way.

## Getting it running

**Option A — Docker (recommended, ~2 minutes):**

```bash
docker compose up
```

Then open http://localhost:8080. The database is created and seeded automatically on first start.

**Option B — Local PHP + MySQL (XAMPP, WAMP, Laragon, etc.):**

1. Import `sql/schema.sql` into MySQL (it creates the `saas_admin` database and seed data).
2. Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` as environment variables, or edit the defaults in `public/db.php`.
3. Serve the `public/` folder, e.g. `php -S localhost:8080 -t public`.

## Part 1 — Bug reports from the support team (fix these)

1. **"The customer list is missing people."** Page 1 of the customer list doesn't start at the beginning, and some customers never show up on any page at all.
2. **"The MRR number is wrong."** The Monthly Recurring Revenue figure at the top of the list changes when you page through results. It should reflect the whole customer base, not whatever's on screen.
3. **"Editing a customer doesn't save."** You open a customer, change something, click Save, see "Customer updated" — and nothing changed. Sometimes the plan even changes to something you didn't pick.
4. **Security.** We've been told this app has at least one serious security issue. Find it and fix it. If you find more than one, fix those too and note them in your write-up.

## Part 2 — Feature requests (build these)

1. **Customer status.** Add a `status` field to customers with the values `active`, `trialing`, and `cancelled`. Existing customers should become `active`. Show it in the list, and allow it to be set on the add and edit forms. **Cancelled customers must not count toward MRR.**
2. **Search by company name.** Add a text search on the customer list that filters by company name (partial match is fine). It should work together with the existing plan filter and with pagination.

## What to hand back

- A link to a Git repository (or a zip of the project including the `.git` folder). Please commit as you go — we read the history.
- A short `NOTES.md` covering: what you found, what you changed and why, anything you'd do differently with more time, and anything you noticed but chose not to fix.
- Any schema changes should go in a new file, `sql/002_*.sql`, so we can apply them on top of the seed.

## What we're evaluating

- Can you find the root cause of a bug rather than patching the symptom?
- Do you write safe database code without being asked to?
- Is your new code consistent with the better parts of the existing code?
- Do you communicate clearly about what you did?

If you run out of time, stop. A clear note saying "I didn't get to X, here's how I'd approach it" is worth more to us than a rushed implementation.
