# Laravel Notes App

A small notes application built to practice and learn Laravel. The app provides a simple and friendly experience for creating and organizing personal notes.

## Features

- User registration and login
- Edit user profile information
- Upload and update profile avatars with FilePond
- Create notes
- View and search notes
- Edit existing notes
- Delete notes
- Demo account with sample notes
- Dark and light display modes, saved locally in the browser

## Technologies

This project was built with:

- Laravel
- FilePond.js
- Bootstrap
- Vite
- SQLite (default development database)

It is intended as a small learning project for understanding Laravel basics, including authentication, controllers, models, migrations, seeders, Blade views, validation, file uploads, and database relationships.

## Installation

### Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm

### Setup

1. Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd example-app
```

2. Install the PHP dependencies:

```bash
composer install
```

3. Create the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

4. Generate the Laravel application key:

```bash
php artisan key:generate
```

5. Configure the database. The project uses SQLite by default, as defined in `.env.example`.

Create the SQLite database file if it does not exist:

```bash
touch database/database.sqlite
```

On Windows PowerShell, use:

```powershell
New-Item database/database.sqlite
```

For a different database, update the `DB_*` values in `.env`, create the database, and then continue with the migration step.

6. Create the private storage directories used for temporary avatar uploads and saved avatars:

```bash
mkdir -p storage/app/private/tmp storage/app/private/avatars
```

On Windows PowerShell, use:

```powershell
New-Item -ItemType Directory -Force storage/app/private/tmp, storage/app/private/avatars
```

The directories are used by the configured Laravel filesystem disks:

- `storage/app/private/tmp` — temporary FilePond uploads
- `storage/app/private/avatars` — final user avatar files

7. Run the database migrations:

```bash
php artisan migrate
```

8. Seed the demo account and sample notes:

```bash
php artisan db:seed --class=DemoUserWithNotesSeeder
```

You can also reset and migrate the database before creating the demo data:

```bash
php artisan migrate:fresh
php artisan db:seed --class=DemoUserWithNotesSeeder
```

9. Install the Node.js dependencies and build the frontend assets:

```bash
npm install
npm run build
```

For frontend development with automatic asset rebuilding, run this in a separate terminal:

```bash
npm run dev
```

10. Start the Laravel development server:

```bash
php artisan serve
```

Open `http://localhost:8000` in your browser.

## Demo Account

Use the following credentials to test the application:

- **Email:** `demo.notes@example.com`
- **Password:** `NotesDemo123!`