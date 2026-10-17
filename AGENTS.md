# AGENTS.md

## Project overview

This repository is a Laravel 12 web application for an e-Barangay system. It has two primary user flows:

- Admin portal for approvals, document generation, notifications, and user management
- Resident portal for authentication, registration, verification, and service requests

The application uses:

- Laravel PHP backend with Eloquent models and Blade views
- Vite + Tailwind for frontend asset compilation
- Role-based access with admin and resident middleware
- Email/phone verification and Google login flows

## Key repository structure

- `routes/web.php` — main route definitions and permission boundaries
- `app/Http/Controllers/` — controller logic for admin and resident flows
- `app/Models/` — Eloquent models, especially `User` and domain records
- `resources/views/` — Blade templates and dashboards
- `database/migrations/` and `database/seeders/` — schema and seed data
- `public/` — built assets and static files
- `tests/` — PHPUnit tests

## Working conventions

- Prefer the existing Laravel pattern: route → controller → model → Blade view.
- Follow the existing naming conventions in `routes/web.php`, especially `admin.*` and `resident.*` route names.
- Respect the existing middleware boundaries: `auth`, `guest`, and `AdminMiddleware` are part of the app’s access control model.
- For user-facing changes, inspect the relevant controller and Blade view together before editing to match the current UI and workflow.
- Keep changes consistent with the project’s custom barangay workflows rather than introducing generic Laravel patterns that do not match the app.
- When fixing auth, notification, or verification behavior, check the existing registration and email verification flow before modifying it.

## Common commands

Use these commands as the default development workflow:

- Install dependencies: `composer install` and `npm install`
- Initial app setup: `composer run setup`
- Local development: `composer run dev`
- Production asset build: `npm run build`
- Run the test suite: `composer run test` or `php artisan test`

## Environment and setup notes

- The app expects a Laravel environment file, typically `.env` based on `.env.example`.
- If the app is newly bootstrapped, generate an app key and run migrations before testing features.
- This project includes both PHP and frontend assets; keep both sides aligned when adding routes, views, or form fields.

## Quality bar for edits

- Keep changes minimal and scoped to the requested feature or bug fix.
- Prefer reusing existing controllers, models, and view structure over introducing new frameworks or architectural patterns.
- For bug fixes or behavioral change, add or update tests when practical.
- Do not remove or bypass existing verification or approval flows without understanding the related controller and user status logic.

## Helpful references

- `README.md` — default Laravel project overview
- `routes/web.php` — primary application flow and route names
- `app/Models/User.php` — user model, role fields, verification behavior, and access-related attributes
- `composer.json` — PHP dependencies and scripts
- `package.json` — frontend build scripts

This file is intentionally concise so agents can quickly understand the project shape and follow the established Laravel conventions used here.
