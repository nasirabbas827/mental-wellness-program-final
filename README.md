# Mental Wellness Hub

mental-wellness-program-final is a mental wellness and counselling application.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `counselling_sessions`, `mood_checkins`, `session_requests`, `users`, `wellness_resources`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_navbar.php`](admin/admin_navbar.php), [`admin/view_programs.php`](admin/view_programs.php), [`admin/view_sessions.php`](admin/view_sessions.php) |
| Counsellor | [`counsellor/add_session.php`](counsellor/add_session.php), [`counsellor/dashboard.php`](counsellor/dashboard.php), [`counsellor/edit_session.php`](counsellor/edit_session.php), [`counsellor/manage_resources.php`](counsellor/manage_resources.php), [`counsellor/profile.php`](counsellor/profile.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php), [`view_counsellors.php`](view_counsellors.php), [`view_programs.php`](view_programs.php), [`view_sessions.php`](view_sessions.php) |
| Participant | [`participant/dashboard.php`](participant/dashboard.php), [`participant/mood_checkins.php`](participant/mood_checkins.php), [`participant/profile.php`](participant/profile.php), [`participant/schedule_session.php`](participant/schedule_session.php), [`participant/view_counsellors.php`](participant/view_counsellors.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/mental-wellness-program-final.git
cd mental-wellness-program-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/mental_db.sql`](Database/mental_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`counsellor/config.php`](counsellor/config.php), [`participant/config.php`](participant/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |
| [`uploads`](uploads) | Upload storage |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
