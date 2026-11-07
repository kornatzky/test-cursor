# Ledger

A Laravel ledger for balances held in different currencies, with one total in US dollars.

Create an account with your email and password. Enter the amount you hold in each currency. The ledger converts every balance with European Central Bank reference rates and shows the total in USD. Wise is a transfer service, not a currency, so it cannot be entered.

## Setup

Requirements: PHP 8.3+ with bcmath, curl, intl, mbstring, and sqlite; Composer; Node.js 20+.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

## Run

```sh
php artisan serve
```

Open http://127.0.0.1:8000, create an account, and add a balance.

## Tests

```sh
php artisan test
```
