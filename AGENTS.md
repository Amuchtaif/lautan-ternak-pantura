# AGENTS.md — Lautan Ternak Pantura

## Stack
- **PHP** (native, no Composer), **MySQL** (PDO), **TailwindCSS + FontAwesome** (CDN)
- Custom MVC with front controller (`index.php`)

## Routing
- `.htaccess` base: `/lautan-ternak-pantura/` — hardcoded everywhere
- `index.php?url={path}` → `controllers/{Name}Controller.php` → method `index()` by default
- API endpoints (`api/auth/*`, `api/admin/*`, `api/savings/*`) bypass the front controller and are accessed directly via `.htaccess` extension-removal rewrite
- URL paths: `/marketplace`, `/tabungan`, `/livestock/detail/{id}`, `/views/{role}/dashboard`, `/customer/profile`
- `register.php` now auto-logs in and accepts `redirect` POST param; login page forms also preserve `?redirect=` query param

## Database
- Config: `config/database.php` — custom `loadEnv()` reads `.env` (not vlucas/phpdotenv)
- Schema: `database/schema.sql` (tables: users, livestock, savings_plans, savings_transactions, orders, payments, sohibul_qurban), seed data: `database/seeder.sql`
- Default password for all seed users: `password123`
- `$conn` PDO instance is a **global variable** — check `isset($conn)` before use; won't exist if DB is unreachable
- If hash mismatch: run `fix_passwords.php` from document root to rehash all passwords

## Auth
- Session-based with `password_verify()`. Roles: `admin`, `customer`, `breeder`
- Login POSTs to `api/auth/login.php` (not through front controller)
- Admin views gate on `$_SESSION['role'] === 'admin'` — every dashboard page does its own security check

## Architecture quirks
- No autoloading — all `require_once` paths are manual and relative to document root
- Controllers `require_once 'config/database.php'` and model files inline
- Admin API endpoints accept both `FormData` and JSON (`php://input` decode)
- Image uploads stored at `assets/uploads/livestock/` (created on demand)
- Admin views under `views/admin/` have their own `includes/sidebar.php`

## Commands / tooling
- No build tools, no Composer, no npm, no test runner, no linter/typechecker/formatter
- To set up: import `database/schema.sql` into MySQL, then `database/seeder.sql`
- Quick password fix: `http://localhost/lautan-ternak-pantura/fix_passwords.php`

## Conventions
- View files `require_once 'includes/header.php'` at top and footer is pulled in by `includes/footer.php` (which closes `<main>` and `</html>`)
- Controller class names: `{Name}Controller`, file name: `controllers/{Name}Controller.php`
- Model classes: plain PHP with PDO in `models/`
- All internal URLs use `/lautan-ternak-pantura/` prefix
- `SYSTEM_MAP.md` contains a detailed project overview if needed

## Shared Hosting Compatibility Rules
To ensure the app remains fully compatible on cPanel / shared hosting environments:
- **Clean URLs only**: Never write the `.php` extension in internal anchors or links (e.g., use `/marketplace` instead of `marketplace.php`).
- **Path Stripping base**: Always prefix all internal page routes and asset URLs with `/lautan-ternak-pantura/`. This is stripped transparently by `.htaccess` in the hosting environment.
- **Universal Favicon tag**: Always use `<link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">` for cross-browser favicon support. Avoid `type="image/ico"`.
- **Absolute Asset Paths**: Aset assets (logos, uploads, icons) must use absolute paths prefixed with `/lautan-ternak-pantura/assets/` to prevent broken paths inside nested MVC routes.
- **Encoding Mismatches**: Use standard ASCII characters for placeholders (e.g. `********` for password dots) to prevent server/browser encoding mismatches like corrupt UTF-8 bullet characters.
