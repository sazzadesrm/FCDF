# FCDF Pro — Foreign Currency Deposit Form System

Internal web application for **Walton Hi-Tech Industries PLC (WHIPLC)** that
digitizes the Foreign Currency Deposit Transfer process. Built with plain
PHP 8 + MySQL, designed to run on shared hosting (cPanel / StackCP) with
**no Node.js build step and no mandatory Composer dependencies**.

Developed by: **Sazzad Kabir**, Executive Assistant (EA) to AMD
Email: sazzadmbstu@gmail.com &nbsp;|&nbsp; Mobile: +88-01810-076761
Support: +88-01678-028928

---

## 1. Requirements

- PHP 8.0+ (8.5 recommended) with `pdo_mysql`, `mbstring`, `fileinfo` (standard on cPanel)
- MySQL 8.x or MariaDB 10.x
- Apache with `mod_rewrite` (standard on shared hosting)

No Composer packages are strictly required — the app ships with its own
lightweight PSR-4 autoloader, a pure-PHP TOTP (2FA) implementation, and uses
PHP's built-in `mail()` for email. PDF/JPG/PNG export is done **client-side**
in the browser (html2canvas + jsPDF, loaded from CDN), so no server-side PDF
library is needed either.

## 2. Project Structure — Flat Layout

This project uses a **flat deployment layout**: there is no separate
`public/` subfolder. `index.php` and all public assets sit directly at the
project root, because that root **is** the Document Root on this hosting
account (`public_html/fcdf.sazzad.co.business`).

```
fcdf-pro/
├── index.php                # Front controller (Document Root entry point)
├── install.php               # ONE-TIME admin seed script — delete after running
├── .htaccess                  # Pretty URLs + blocks direct access to src/, storage/, .env, etc.
├── database.sql                # Full schema + seed data
├── migrate_add_manual_depositor.sql   # Only needed if upgrading an existing DB (see §7)
├── .env                          # Pre-filled with your DB credentials
├── .env.example                   # Reference template
├── composer.json                   # Optional — see §4
├── assets/                          # CSS / JS (Bootstrap/Chart.js/Select2 via CDN)
├── uploads/avatars/                  # Profile photo uploads (writable)
├── src/
│   ├── Config/                        # Bootstrap, .env loader, PDO connection
│   ├── Core/                           # Router, Controller, Model, Auth, Validator, Mailer, AuditLogger
│   ├── Helpers/                         # NumberToWords, InvoiceNumberGenerator, TOTP, functions
│   ├── Models/                           # One class per table
│   ├── Controllers/                       # One per module
│   ├── Views/                              # Server-rendered PHP views
│   └── routes.php                           # All route definitions
└── storage/logs/                              # PHP error log (writable)
```

**Security note:** because `src/`, `storage/`, `.env`, and `database.sql`
now live in the same folder the web server serves from, the included
`.htaccess` explicitly blocks direct browser access to all of them (by
extension and by folder name). Do not remove those rules.

## 3. Installation (Starthost / cPanel / StackCP)

> **Note:** `fcdf.sazzad.co.business` looks like a new subdomain, so its
> Document Root path in StackCP may not automatically be
> `public_html/fcdf.sazzad.co.business` — check StackCP → Domains and use
> whatever path is actually listed there for this subdomain before
> uploading (the same way you confirmed it for `sazzad.co.network`
> earlier). The steps below assume that path once confirmed.

1. **Upload** the entire contents of this zip directly into that confirmed
   folder (i.e. `index.php` should end up directly inside it, not nested
   one level deeper).

2. **Confirm your Document Root** points exactly to that folder (no
   `/public` suffix) — StackCP → Domains.

3. **Create the MySQL database** (or reuse the one already provisioned —
   see §6 for credentials). Note the DB host, name, username, and password.

4. **Import the schema**: cPanel → phpMyAdmin → select your database →
   Import → choose `database.sql` → Go. This creates all tables and seeds
   currencies, default Terms & Conditions, and app settings.

   > If you are upgrading an **existing** database from a previous version
   > of this project rather than starting fresh, also run
   > `migrate_add_manual_depositor.sql` afterward — it adds the 3 new
   > columns that support manually-entered Depositor details. Safe to run
   > multiple times.

5. **`.env` is already included and pre-filled** — no editing required
   unless you want to configure outbound email (`MAIL_*` values).

6. **Run the installer once**: open `https://fcdf.sazzad.co.business/install.php`,
   then follow the confirmation link it shows (`?confirm=yes`) to create
   the Administrator account:
   - Email: `sazzadmbstu@gmail.com`
   - Temporary password: `Walton@7130#BD`

   **Then delete `install.php` from the server** and sign in to change the
   password.

7. **Set folder permissions**: ensure `storage/logs/` and
   `uploads/avatars/` are writable by the web server (usually `755`).

8. **Sign in** at `https://fcdf.sazzad.co.business/login`, change the admin
   password, and start adding Master Data (Departments, Legal Entities,
   Operating Units, Products, Countries, Signatories — Currencies and
   default Terms & Conditions are pre-seeded).

## 4. Optional: PHPMailer via Composer

If your host allows CLI Composer and you'd like authenticated SMTP instead
of PHP's built-in `mail()`:

```bash
composer require phpmailer/phpmailer
```

Then update `src/Core/Mailer.php` to use PHPMailer internally — the public
method signatures are designed to stay the same so no controller code needs
to change.

## 5. Key Features

- **Authentication**: login, registration with email verification, forgot/
  reset password, bcrypt password hashing, brute-force lockout, optional
  TOTP 2FA (Google Authenticator compatible, QR code enrollment).
- **Dashboard**: monthly submitted-form count, monthly BDT total, total
  operating units, operating-unit/legal-entity/product/country/currency-wise
  monthly totals (Chart.js), country-wise currency mix table, recent
  deposits table.
- **Deposit Form**: dynamic repeatable currency lines with live BDT
  calculation (whole numbers, no decimals) and live amount-in-words
  preview; **Depositor** can be selected from Master Data *or* entered
  manually (Name/ID/Phone) via a toggle switch; auto invoice numbering
  (`FCDF-MMM-YYYY-0000`, monthly-reset, generated inside a locking DB
  transaction to prevent duplicates).
- **Invoice output**: single-page A4, mobile-responsive, includes the
  "Advance Against Travelling & Tour (Oracle New- 6996 | Oracle Old- 18591)"
  sub-heading, Submitted On / Last Modified timestamps, Print / Export
  PDF / JPG / PNG (client-side, always fits one A4 page regardless of
  device).
- **Master Data**: 8 maintained lists, each with searchable list,
  **Add and Edit** (click "Edit" on any row to load it into the form),
  active/inactive toggle, and delete.
- **Reports**: 6 period-based reports with the same Export/Print options.
- **Settings, Profile (avatar/password/2FA), Audit Trail**: as before.
- **Typography**: Roboto throughout the app and the exported/printed
  invoice.
- **Security**: PDO prepared statements everywhere, CSRF tokens, output
  escaping, HttpOnly + Secure session cookies, server-side RBAC, bcrypt
  hashing, login rate limiting, `.env`/`src`/`storage` blocked from direct
  web access via `.htaccess`.

## 6. Database Credentials (pre-filled in `.env`)

```
DB_HOST=sdb-r.hosting.stackcp.net
DB_NAME=fcdf_pro-323036c233
DB_USER=fcdf_pro
DB_PASS=Walton@7130#BD
APP_URL=https://fcdf.sazzad.co.business
```

## 7. Upgrading an Existing Install

If you already have a working FCDF Pro database from a previous version and
don't want to lose your data:

1. Re-upload all application files (this restructures the deployment to the
   flat layout — see §2).
2. Run `migrate_add_manual_depositor.sql` once via phpMyAdmin.
3. Nothing else changes in the database — invoice numbers for **new**
   submissions will use the shorter `FCDF-MMM-YYYY-0000` format; existing
   invoice numbers already saved are untouched.

If you'd rather start completely fresh, drop all tables and re-import
`database.sql` from scratch instead.

## 8. Support

For any implementation questions, contact the developer:
**Sazzad Kabir** — sazzadmbstu@gmail.com — +88-01810-076761
General accounting support: +88-01678-049106 (printed on every voucher)
Application support: +88-01678-028928
# FCDF
