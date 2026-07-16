# MySQL CMS deployment

The runtime requires MySQL/MariaDB. It never reads or writes `api/data/*.json`;
those files are one-time migration seeds only.

## XAMPP development

1. Import `database/schema.sql` through phpMyAdmin or the MySQL client.
2. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in your local shell.
3. Run `C:\xampp\php\php.exe scripts\migrate_json_to_mysql.php` once.
4. Run `C:\xampp\php\php.exe scripts\test_mysql_cms.php` to test safe CRUD. The test rolls back all test data.
5. Start the website from the project root with the development router:

   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8000 router.php
   ```

   Both `http://127.0.0.1:8000/tk-paud.php` and the clean URL
   `http://127.0.0.1:8000/tk-paud` will then resolve to the correct page.

   > **PENTING:** Jangan gunakan `api/index.php` sebagai router file.
   > Perintah `php -S localhost:8000 api/index.php` akan menyebabkan semua
   > halaman menampilkan konten index/beranda. Selalu gunakan `router.php`.

## Static build preview

After running `php scripts/build-static.php`, you can preview the generated
static HTML files with a separate server:

```powershell
php -S 127.0.0.1:8000 serve-static.php
```

This serves files from `public/` and automatically rewrites `.php` URLs to
their `.html` equivalents so existing links and bookmarks continue to work.

## DomaiNesia production

Create the database and its dedicated user in cPanel, then import
`database/schema.sql` (omit the first `CREATE DATABASE`/`USE` statements if
cPanel already selected the database). Configure the same `DB_*` values in a
server-side configuration file outside `public_html` or through the host's
environment-variable facility.

Upload the contents of `api/` to the web document root (`public_html`) and
keep `api/.htaccess` there. It blocks direct access to `includes/`, `data/`,
and configuration/seed files. Do not upload `scripts/`, `database/`, `.env`,
or `api/config.local.php` to the document root.

Also set `ADMIN_PASSWORD` (prefer a bcrypt/Argon2 hash) and a long random
`SESSION_SECRET`. Do not commit these values. The supplied `.env.example`
contains names only, not secrets.

The database user should have access only to this application's database. Do
not grant `FILE`, `GRANT OPTION`, or global privileges, and disable remote
database access unless it is strictly required.

## Production acceptance checklist

1. Enable SSL for `yayasancendekiacirebon.sch.id`, force HTTPS in cPanel, and
   test both the website and `/admin.php` over HTTPS.
2. Set a unique bcrypt/Argon2 `ADMIN_PASSWORD` and a random `SESSION_SECRET`
   of at least 32 characters in server-side configuration. Never use the local
   development password in production.
3. Enable the hosting WAF/ModSecurity and put the domain behind a CDN/WAF such
   as Cloudflare. Configure rate limiting for `/admin.php` and bot protection
   for public registration pages. Application code alone cannot absorb DDoS.
4. Confirm PHP error display is disabled, error logs are private, automatic
   MySQL backups are scheduled, and a restore has been tested.
5. Run an authenticated smoke test: admin login, article/FAQ create-edit-
   delete, image upload, logout, public article search, and a real registration
   submission with non-sensitive test data. Registration currently posts
   directly to Google Forms, so its delivery must be checked with the school
   form owner after deployment.
