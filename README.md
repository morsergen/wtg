# WTG Accommodation Offers API

REST API for asynchronous accommodation offer imports, searching for the cheapest available offers, and safely creating reservations.

## Requirements

- PHP 8.3 or newer
- Composer
- MySQL 8 or newer
- A process capable of running the Laravel database queue worker

## Installation

Clone the repository and install PHP dependencies:

```bash
git clone https://github.com/morsergen/wtg
cd wtg
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database and configure the connection in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wtg
DB_USERNAME=root
DB_PASSWORD=
```

The application uses the database queue driver:

```dotenv
QUEUE_CONNECTION=database
```

## Database setup

Run the database migrations:

```bash
php artisan migrate
```

Seed the required suppliers (`supplier-a` and `supplier-b`):

```bash
php artisan db:seed
```

Both operations can also be executed with one command:

```bash
php artisan migrate --seed
```

## Running the application

If you are not using Docker or a configured web server, start Laravel's local development server:

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

In a separate terminal or application container, start the queue worker:

```bash
php artisan queue:work --tries=3
```

The queue worker processes offer imports asynchronously and must remain running while import requests are being accepted.

## Tests and code quality

The test suite uses a separate MySQL database named `wtg_testing`. Create this empty database before running the tests. Connection credentials are inherited from `.env`, while the database name is configured in `phpunit.xml`.

Run the test suite:

```bash
php artisan test --compact
```

Check code formatting and run static analysis without modifying files:

```bash
composer project:check
```

Automatically fix formatting and then run static analysis:

```bash
composer project:fix
```

## Import idempotency

Imports are identified by the unique combination of `supplier_id` and `external_import_id`. This uniqueness is enforced by a database constraint.

The application uses `createOrFirst()` when accepting an import. A processing job is dispatched only when the import record was newly created. Repeating the same request returns the existing import and does not enqueue it again.

Offers are identified by the unique combination of `supplier_id` and `external_id`. During processing, `updateOrCreate()` updates an existing offer when the same supplier sends it in a later import instead of creating a duplicate.

The job also stops immediately when the import already has the `completed` status.

## Reservation concurrency protection

Reservation creation is executed inside a database transaction. The selected offer is read again using `SELECT ... FOR UPDATE` through Laravel's `lockForUpdate()` method.

The row lock allows only one transaction at a time to check and modify a particular offer. If two requests attempt to reserve the last available unit concurrently, the second transaction waits until the first one commits. It then reads the updated `available_units` value and receives a `409 Conflict` response instead of creating another reservation.

Decreasing `available_units` and creating the reservation happen in the same transaction. If either operation fails, the transaction is rolled back and the row lock is released automatically.

## API documentation

Generate the OpenAPI documentation:

```bash
php artisan l5-swagger:generate
```

When the application is running, Swagger UI is available at:

```text
http://127.0.0.1:8000/api/documentation
```

If the application is served through Docker or another web server, use that server's base URL with the `/api/documentation` path.
