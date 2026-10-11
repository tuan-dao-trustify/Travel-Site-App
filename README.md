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

## Seed the database

Create the test user (the only seed data, hotels come from the fake supplier):

```bash
php artisan db:seed
```

Log in with `test@example.com` / `password`.

## Run the server

Start the development server (Laravel plus the Vite dev server):

```bash
composer run dev
```

## Calling logged-in routes with curl

Routes behind `auth` use the session cookie. Log in once with curl, keep the cookies in a file (`-c` saves, `-b` sends), then reuse them. The `-H` flags add the headers.

```bash
curl -s -c c.txt -o /dev/null localhost:8000/login   # 1. get the cookies (GET first, a POST without the token gets 419)
curl -s -b c.txt -c c.txt localhost:8000/login -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $(awk '/XSRF-TOKEN/{print $7}' c.txt | sed 's/%3D/=/g')" \
  -d '{"email":"test@example.com","password":"password"}'   # 2. log in
curl -s -b c.txt -H "Accept: application/json" localhost:8000/me   # 3. call a logged-in route: returns your user as JSON (401 without -b c.txt)
```

Expected result of step 3: `{"id":1,"name":"Test User","email":"test@example.com"}`. Use the same cookie file for any other logged-in route.

The supplier endpoints and the confirmation simulator are described in `FAKE_SUPPLIER.md`.
