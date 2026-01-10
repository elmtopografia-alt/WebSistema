# Copilot instructions for SistemaWeb (SGT)

This file contains concise, actionable guidance for AI coding agents working on this repository.

## Big picture
- Monolithic PHP application without a framework (plain PHP + HTML templates embedded in .php files).
- Two runtime "ambientes": **producao** and **demo**. The chosen ambiente affects DB connection and some feature restrictions.
- DB access uses a local MySQLi wrapper (`db.php` → `Database::getProd()` / `Database::getDemo()`), but many scripts still rely on a global `$conn` variable for compatibility.
- Session-based authorization: session variables like `$_SESSION['usuario_id']`, `$_SESSION['perfil']`, and `$_SESSION['ambiente']` control flows. See `core.php` for helper functions (`exigeAdmin()`, `exigeCliente()`, `bloqueiaDemoParaFinanceiro()`).

## Key files to read first
- `config.php` — environment flag (`ENVIRONMENT`), timezone, and DB constants (credentials are defined here).
- `db.php` — `Database` class and compatibility layer that sets global `$conn` based on `$_SESSION['ambiente']`.
- `core.php` — session checks, user identity load, and access helpers used across the app.
- `login.php`, `demo.php` — examples of authentication logic (note: **demo** uses plaintext passwords; admin uses `password_hash()`/`password_verify`).
- `enviar_email.php` — practical example of external integration (PHPMailer SMTP config & fallback mailto link).
- `gerar_documento.php` / `composer.json` — docx generation using `phpoffice/phpword` (installed via Composer).

## Conventions & patterns
- No framework conventions: files often handle request, DB, and output in the same file (controller+view in one). Keep changes minimal and localized.
- DB queries use prepared statements with `$stmt->bind_param(...)` and `$stmt->execute()`. Prefer the existing style for consistency.
- Global compatibility: many pages expect a global `$conn` variable; maintain that when refactoring.
- Environment switching: `$_SESSION['ambiente'] === 'demo'` → uses `Database::getDemo()`; otherwise production DB is used.
- Passwords: production admin accounts use hashed passwords and `password_verify()`. Demo/test accounts compare plaintext values in `demo.php`.

## Developer workflows / commands
- Install composer deps: run `composer install` (installs `phpoffice/phpword` per `composer.json`).
- PHPMailer may be required for email features: if missing, run `composer require phpmailer/phpmailer`.
- Run a quick local server for manual testing: `php -S localhost:8000 -t .` from the project root and visit `http://localhost:8000/demo.php` or `login.php`.
- Linting: use `php -l <file>` for quick syntax checks; no test suite is present in the repo.

## Integration points / external services
- MySQL / MariaDB — primary data store. DB credentials are in `config.php` (production and demo constants). Connection is established with `mysqli`.
- SMTP — `enviar_email.php` uses PHPMailer and requires SMTP credentials configured inside the file (or supply via secure env process when refactoring).
- DOCX generation — uses `phpoffice/phpword` (see `gerar_documento.php` and `composer.json`).

## Security & maintenance notes (discoverable patterns)
- DB credentials are hard-coded in `config.php`. Many scripts expect to run under a writable file layout (e.g., `propostas_emitidas/` for generated docs).
- Demo accounts may store plaintext passwords (legacy behavior in `demo.php`) — treat demo data as insecure by design.
- Error reporting toggled by `ENVIRONMENT` constant in `config.php` (set to `development` to show errors).

## Helpful quick examples for edits
- To get a DB connection respecting session demo flag in a new script:
  <?php
  require_once 'db.php';
  $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

- Use access helpers from `core.php` after loading user session:
  exigeAdmin(); // die(403) if not admin

- SMTP troubleshooting: `enviar_email.php` shows the SMTP host/port/username/password placeholders and a `SMTP::DEBUG_SERVER` option for verbose output.

## What to watch for when editing
- Keep HTML/PHP layout style consistent (avoid introducing a framework or templating change without a clear migration plan).
- Maintain compatibility with the global `$conn` variable unless you migrate systematically.
- When touching auth or password logic, verify both demo and production flows (`demo.php` vs `login.php`).

---

If anything here is unclear or you want additional examples (e.g., how to refactor DB access into an injectable service or a migration checklist), tell me which area to expand and I will iterate. ✅
