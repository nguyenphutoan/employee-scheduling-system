# EmpScheduleLaravel

Laravel-based employee scheduling application for manager and staff workflow.

## Overview

This project provides a schedule management system including:
- Role-based login for managers and staff.
- Weekly shift scheduling with morning/evening assignments.
- Employee availability submission and status tracking.
- Manager dashboard, employee management, and payroll overview.
- In-app chat between authenticated users.
- Excel schedule export using `maatwebsite/excel`.

## Key Features

- Manager functions:
  - View manager dashboard and weekly schedule.
  - Create new weeks with morning/evening shifts.
  - Assign staff to shifts and approve assignments.
  - Track availability and employee registration status.
  - Export schedule to `.xlsx`.

- Staff functions:
  - View individual dashboard and payroll summary.
  - Register availability for upcoming weeks.
  - Confirm assignment notifications.
  - Update personal profile.

- Shared features:
  - Authentication and session handling.
  - Real-time-style messaging via chat routes.
  - Structured data models for weeks, shifts, positions, assignments, and availability.

## Tech Stack

- PHP 8.2
- Laravel 12
- Tailwind CSS + Vite
- Maatwebsite Excel for exports
- SQLite or database driver configured in `.env`

## Installation

1. Clone the repository.
2. Copy the environment file:
   ```bash
   copy .env.example .env
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run database migrations:
   ```bash
   php artisan migrate
   ```
6. Install frontend dependencies:
   ```bash
   npm install
   ```
7. Build assets or start development server:
   ```bash
   npm run dev
   ```

## Quick Setup

A single setup command is available:

```bash
composer run setup
```

This will install dependencies, create `.env`, generate the app key, run migrations, install npm packages, and build assets.

## Running the Application

Start the Laravel development server:

```bash
php artisan serve
```

Then open the local URL shown in the terminal.

## Testing

Run the PHPUnit test suite with:

```bash
composer test
```

## Project Structure

- `app/Http/Controllers` – Controller logic for auth, manager, staff, and chat.
- `app/Exports` – Excel export implementation.
- `app/Models` – Eloquent models for users, weeks, shifts, availability, positions, assignments, and messages.
- `database/migrations` – Database schema for users, weeks, shifts, availability, positions, assignments, and messages.
- `resources/views` – Blade templates for login, manager, staff, chat, and exports.
- `routes/web.php` – Web routes for authentication, manager, staff, and chat flows.

## Notes

- The app includes a manager and staff role flow.
- Use `.env` to configure database connection, mail, and other environment settings.

## License

This project is open source and uses the MIT license.
