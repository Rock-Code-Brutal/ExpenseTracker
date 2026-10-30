# Monthly category budgets: handoff and QA

## Setup

Use PHP 8.3+ with the extensions required by Composer, and Node 20.19+ (or a compatible newer release). Run `composer install`, `npm ci`, `php artisan migrate`, and `npm run build`. Do not reset an existing database. Start the local application as usual.

## Behavior

The Budgets tab uses the header currency selector (USD/CAD). Months default to the current New York calendar month. The API accepts `month` as YYYY-MM and stores its first day. Budgets are positive amounts on expense categories, unique per category/month/currency. Spending excludes income, other categories, other currencies, and dates outside the month. Negative remaining values indicate overspending. Totals recalculate on a fresh request; they are not stored.

## Automated checks

Run `php artisan test` and `npm run build`. Tests cover API validation, uniqueness, relationships, currency/month filtering, spending arithmetic, year boundaries, leap days, transaction changes, and deletion preserving transactions.

## Manual browser checklist (use disposable test data)

- [ ] Create a budget and refresh; verify persistence.
- [ ] Edit its amount, save, then cancel a second edit; verify only the saved change persists.
- [ ] Attempt a duplicate; verify a readable validation message.
- [ ] Attempt zero, negative, and excess decimal amounts; verify rejection.
- [ ] Switch month and currency; verify budgets do not leak between selections.
- [ ] Add matching expenses, including an overspend; verify actual and remaining amounts.
- [ ] Edit/delete an expense and return to Budgets; verify recalculation.
- [ ] Cancel removal, then remove the test budget; verify category and transactions remain.
- [ ] Use Tab/Enter and Escape; verify visible focus and labeled controls.
- [ ] Check narrow mobile layout and dark mode.
- [ ] Simulate a failed request; verify error and retry behavior.

## Scope and limitations

Local single-user simulation; no authentication, conversion, recurring budgets, or deployment. Budget updates require the complete category/month/currency/amount payload. Lists are not paginated. UI totals use the server's decimal strings formatted for display.
