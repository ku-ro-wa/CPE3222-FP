# Contact Tracing Application

Simple PHP and MySQL-based contact tracing system for the USC Department of Computer Engineering. It lets users register, sign in, and sign out, while administrators can review visit logs and manage records.

## Features

- Public sign-in and sign-out page for USC users and guests
- User registration with contact details
- Admin login and dashboard
- Search and filter visit logs
- Delete users and related visit records

## Requirements

- PHP 8+
- MySQL / MariaDB
- A local web server such as XAMPP, WAMP, or Laragon

## Setup

1. Create a MySQL database named `contact_tracing_db`.
2. Import `contact_tracing_db.sql` into that database.
3. Update `config.php` if your database credentials are different.
4. Run the project through your web server and open `index.php`.

## Admin Login

- Username: `admin`
- Password: `admin123`

> Change the default admin password after deployment.

## Project Structure

- `index.php` - public user interface
- `admin/` - admin login, dashboard, and account actions
- `api/` - lookup and visit-saving endpoints
- `assets/` - CSS and JavaScript files
- `contact_tracing_db.sql` - database schema

## Notes

- The app uses `config.php` for database access and session handling.
- Visit times are stored in the `check_ins` table.
