# Development Record

## Request
- Fix cPanel 500 error on page load

---

## Task Checklist
- [x] Moved `url()` helper to `utils/Helpers.php` (tracked by git)
- [x] Updated root `index.php` and `public/index.php` bootstrap
- [x] Fixed `array_key_first()` PHP 7.2 compatibility in `config.php`
- [x] Enhanced diagnostic error output in entry points
- [x] Updated `diagnostic.php` to include `Helpers.php`
- [x] Verified PHP syntax for all modified files

---

## Impacted Files

### NEW
- utils/Helpers.php

### UPDATED
- index.php
- public/index.php
- diagnostic.php
- docs/changelog.md
- .dev-records/dev.md
- views/admin/config.php (fixed array_key_first in previous turn)

---

## Summary
Resolved "Call to undefined function url()" on cPanel by moving the function to a tracked file. Since `.config.php` is ignored by git, updates to it weren't reaching the cPanel server during `git pull`. This fix ensures all core helper functions are tracked. Also added full error reporting to the production catch blocks to speed up troubleshooting.

---

## Security Impact
- **Low** - No changes to auth or data logic. Error reporting is detailed but necessary for current setup phase; should be disabled once stable.

## Database Impact
- **None**

## API Impact
- **None**