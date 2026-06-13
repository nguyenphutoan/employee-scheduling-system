# Employee Scheduling System

A comprehensive employee scheduling application built with Laravel, featuring dedicated workflows and permissions for Managers and Staff.

## 🌟 Overview

This project provides a complete shift management system that includes:
- Role-based authentication and specialized access for Managers and Staff.
- Weekly shift scheduling with clear morning and evening shift assignments.
- Staff availability registration and status tracking.
- Interactive dashboards, employee profile management, and payroll tracking.
- Built-in real-time messaging (Chat) system between authenticated users.
- Schedule export to Excel files using `maatwebsite/excel`.

## 🚀 Key Features

### 👨‍💼 For Managers
- **Dashboard & Scheduling**: View system overview and track weekly schedules.
- **Create & Approve Schedules**: Create new work weeks, assign staff to morning/evening shifts, and finalize schedules.
- **Employee Management**: Add, update, and remove employees, as well as track their active status (active/resigned).
- **Availability Tracking**: Monitor staff's submitted availability to make informed scheduling decisions.
- **Payroll Management**: Overview of timesheets and payroll for all employees.
- **Export Reports**: Export the weekly schedule to an `.xlsx` format.

### 👩‍💻 For Staff
- **Personal Dashboard**: View assigned shifts and a personal payroll summary.
- **Register Availability**: Proactively submit availability for upcoming weeks.
- **Shift Confirmation**: Receive notifications and confirm assigned shifts.
- **Profile Management**: Update personal details and change passwords.

### 🔄 Shared Features
- **Authentication & Security**: Secure Auth system that prevents access for inactive/resigned employees.
- **Direct Messaging (Chat)**: Communicate directly within the application.
- **Optimized Data Structure**: Robust Eloquent Models for Users, Weeks, Shifts, Positions, Assignments, Availability, and Messages.

## 🛠 Tech Stack

- **PHP 8.2**
- **Laravel 12**
- **Tailwind CSS + Vite** (Frontend Styling & Build Tool)
- **Maatwebsite Excel** (Excel Data Export)
- **SQLite** (Default) or any other DBMS configured in `.env`

## ⚙️ Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   ```
2. **Copy the environment configuration file:**
   ```bash
   copy .env.example .env
   ```
3. **Install PHP dependencies via Composer:**
   ```bash
   composer install
   ```
4. **Generate the application key:**
   ```bash
   php artisan key:generate
   ```
5. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```
6. **Install Frontend dependencies via NPM:**
   ```bash
   npm install
   ```
7. **Compile Frontend Assets:**
   ```bash
   npm run dev
   ```

### ⚡ Quick Setup

A convenient custom script is available to automate the entire installation process:
```bash
composer run setup
```
*(This command automatically installs composer dependencies, creates the .env file, generates the app key, migrates the database, installs npm packages, and builds frontend assets).*

## 🚀 Running the App

Start the Laravel Development Server:
```bash
php artisan serve
```
Then, open your web browser and navigate to the URL displayed in your terminal (typically `http://localhost:8000`).

## 🧪 Testing

To run the PHPUnit test suite, execute:
```bash
composer test
```

## 📁 Project Structure

- `app/Http/Controllers/` – Application logic and flow (Auth, Manager, Staff, Chat).
- `app/Exports/` – Logic for exporting data to Excel.
- `app/Models/` – Eloquent models interacting with the database.
- `database/migrations/` – Database schema definitions.
- `resources/views/` – Blade template files for the UI.
- `routes/web.php` – Web routing configurations (Public, Manager, Staff).

## 📝 Notes

- The application has distinct routing and access control flows for Managers and Staff.
- Modify the `.env` file to customize your Database connection (e.g., MySQL/PostgreSQL), Mail configuration, and other environment variables.

## 📄 License

This open-source project is licensed under the [MIT license](https://opensource.org/licenses/MIT).
