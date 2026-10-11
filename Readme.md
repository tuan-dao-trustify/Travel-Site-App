# Travel Site App

A fresh Laravel application (Inertia + React frontend).

## Build

One-step setup (installs dependencies, creates `.env`, generates the app key, runs migrations and builds assets):

```bash
composer run setup
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

## Run the server

Start the development server (Laravel plus the Vite dev server):

```bash
composer run dev
```
