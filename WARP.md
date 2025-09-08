# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

Project overview
- PHP web app for a Library Management System using MySQL and plain PHP (no framework).
- Entry points live under public/. Shared templates and auth live under src/. Database config and connection helper in config/database.php. Database schema and seed data in database/schema.sql.
- Sessions are used for auth; role-based access via src/auth.php.

Essential commands (Windows PowerShell, PHP 7.4+)
- Start local dev server (serve public/ as document root):
```powershell path=null start=null
php -S localhost:8000 -t public
```
- Import database schema and seed data (creates DB if needed):
```powershell path=null start=null
mysql -u {{MYSQL_USERNAME}} -p {{MYSQL_DATABASE}} < database/schema.sql
```
Notes:
  - The schema file issues CREATE DATABASE IF NOT EXISTS library_management and USE library_management. If you pass an explicit database via {{MYSQL_DATABASE}}, ensure it matches or remove the USE line.
  - Configure DB credentials in config/database.php.
- Quick PHP syntax check (lint) a file or all public entrypoints:
```powershell path=null start=null
# Single file
php -l public\index.php

# All entrypoints in public/
Get-ChildItem public -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```
- Run a specific page for iterative development:
```powershell path=null start=null
# Start server as above, then open the page in your browser
Start-Process http://localhost:8000/login.php
```
- No project test suite is configured (e.g., PHPUnit). If tests are added later, document how to run a single test here.

Credentials for local development
- Defaults seeded by database/schema.sql (from README):
  - Admin: username admin / password admin123
  - Librarian: username librarian / password librarian123

Configuration
- Database connection: config/database.php exposes a global PDO via getDB(). Update host, dbname, username, password as needed.
- App URL when self-hosted locally: http://localhost:8000/ (with php -S) or http://localhost/library-management-system/public/ if using Apache/XAMPP and placing the project under the web root.

High-level architecture and flow
- HTTP request flow:
  1) Requests hit public/*.php entrypoints (e.g., index.php, login.php, books.php, loans.php).
  2) public/*.php includes config/database.php (PDO setup) and src/auth.php (session + auth helpers).
  3) For authenticated pages, isLoggedIn()/requireLogin() guard access and redirect to login.php if needed.
  4) Views are composed with src/header.php and src/footer.php templates.
  5) Data access via the shared PDO from config/database.php using prepared statements.
- Authentication/session:
  - src/auth.php provides login($username, $password), logout(), isLoggedIn(), requireLogin(), getCurrentUser(), hasRole($role).
  - Passwords are compared using MD5 to match seeded values in database/schema.sql. Do not change hashing without updating seeds and login logic together.
- Templates and layout:
  - src/header.php renders the nav and greets the current user via getCurrentUser().
  - src/footer.php closes layout (included by pages).
- Database schema:
  - Core tables: staff (users and roles), members, categories, books, loans.
  - loans references members, books, and staff; includes due/return dates and fines.
  - schema.sql seeds default categories, users, members, and books, and creates useful indexes.
- Frontend behavior:
  - public/index.php fetches dashboard stats from an API endpoint at ../src/api/dashboard_stats.php (ensure this endpoint exists/returns JSON). Values are inserted into the DOM after load.

Common tasks
- Add a new authenticated page:
```powershell path=null start=null
# 1) Create a new file under public/, e.g., public\members.php
# 2) Start with session_start(), include config/database.php and src/auth.php
# 3) Call requireLogin() to guard the page
# 4) Set $page_title and include ../src/header.php; render content; include ../src/footer.php
```
- Run a one-off DB query for debugging:
```powershell path=null start=null
php -r "$pdo=require 'config/database.php';$db=getDB();foreach($db->query('SELECT COUNT(*) AS c FROM books') as $r){echo $r['c'],PHP_EOL;}"
```

Source-of-truth docs to reference
- README.md contains installation steps, default logins, and a project structure overview. Keep this file in sync with any changes to DB setup or entrypoints.

Warp-specific notes
- This repo has no existing WARP.md; this file serves as the project-scoped rules and quickstart for Warp Agents.
- Prefer php -S for local iteration unless the environment requires Apache/XAMPP.
- When modifying auth or hashing, reflect changes in both schema.sql seeds and src/auth.php to avoid lockouts.

