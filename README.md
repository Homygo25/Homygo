# Homygo

This repository contains the Homygo Laravel application. To run the project you need to create an `.env` file with the required configuration values.

## Setup

1. Install PHP dependencies with `composer install` and Node dependencies with `npm install`.
2. Copy `.env.example` (or `.env.production.example` for a production base) to `.env`.
3. Generate an application key using `php artisan key:generate`.
4. Run `php artisan migrate` to set up the database.
5. Start the application with `php artisan serve` or your preferred web server.

## Environment configuration

The application relies on the following environment variables. Copy either `.env.example` or `.env.production.example` to `.env` and adjust the values as needed:

- **APP_NAME** – Application name used in notifications.
- **APP_ENV** – Environment name (`production`, `local`, etc.).
- **APP_KEY** – Base64-encoded key generated with `php artisan key:generate`.
- **APP_DEBUG** – Enables debug mode. Should be `false` in production.
- **APP_URL** – Full domain used to access the application.
- **DB_CONNECTION** – Database driver (e.g. `mysql`).
- **DB_HOST**, **DB_PORT**, **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD** – Database connection details.
- **MAIL_MAILER**, **MAIL_HOST**, **MAIL_PORT**, **MAIL_USERNAME**, **MAIL_PASSWORD** – SMTP credentials for outgoing mail.
- **MAIL_FROM_ADDRESS**, **MAIL_FROM_NAME** – Default "from" address and sender name.
- **AWS_ACCESS_KEY_ID**, **AWS_SECRET_ACCESS_KEY**, **AWS_BUCKET** – Amazon S3 credentials when used.
- **QUEUE_CONNECTION**, **CACHE_STORE**, **FILESYSTEM_DISK**, and other settings can be configured as needed.

See `.env.example` for a development template and `.env.production.example` for a production oriented starting point.
