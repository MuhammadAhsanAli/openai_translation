# Laravel 12 Translation API (Dockerized)

## Overview
This project implements a **Laravel 12 API** that accepts user input, stores it in a database, and dispatches a queued job to translate the content using **OpenAI** via the [Saloon package](https://docs.saloon.dev/).

The translated text is stored back in the database, and the API allows fetching the translation status and results.

The solution was built with a focus on:
- **Simplicity & clarity** (avoiding unnecessary layers)  
- **Best practices** (Services, Jobs, API Resources, Redis queues)  
- **Scalability awareness** (not over-engineered, but easy to extend)  

---

## Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-repo/laravel-translation-api.git
cd openai_translation
````

### 2. Environment File Setup

```bash
cp .env.example .env
```
Set value of the following in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=username
DB_PASSWORD=secret

OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=openai_model
OPENAI_TOKENS=openai_tokens
OPENAI_TEMPERATURE=openai_temperature 

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 3. Build and Start Docker Containers

```bash
docker-compose up -d --build
```

> Wait a few seconds after `docker-compose up -d` to ensure all services (PHP, MySQL, Redis, Nginx) are fully started.

### 4. Enter the App Container

```bash
docker exec -it translation_app bash
```

### 5. Generate Application Key

Inside the container:

```bash
php artisan key:generate
```

### 6. Run Migrations

> Migration will run automatically **only if `APP_ENV` is not `production`** to avoid destructive changes in production.

```bash
php artisan migrate
```

### 7. Queue Worker

The queue worker starts automatically in the container using **Redis** as the driver:

```bash
php artisan queue:work
```

---

## Base URL

All API requests are served at:

```
http://localhost:8088/
```

---

## Testing

### 1. Run Test

Inside the container:

```bash
php artisan test
```

---

## Postman Collection

* The Postman collection is included in the `postman` folder at the repository root: `API v1 – Translations.postman_collection.json`. To test the API using Postman:

  1. Open Postman.
  2. Click **Import** → **File**.
  3. Select `API v1 – Translations.postman_collection.json` from the postman folder in project.
  4. Run the requests against `http://localhost:8088/`.

---

## Project Structure & Approach

### Service + Job Pattern

* `TranslationService` handles business logic (`createAndDispatch` method).
* `TranslateTextJob` runs translations asynchronously, preventing API blocking.
* Controllers remain **thin** and clean.

### API Resources

* `TranslationResource` formats API responses consistently.
* Example response:

```json
{
  "success": true,
  "data": {
    "id": 17,
    "name": "test",
    "title": "test",
    "description": "test",
    "translated": {
      "description": "prueba"
    },
    "target_language": "es",
    "status": "completed"
  }
}
```

### Redis Usage

* Redis is used as the **queue driver** per task requirements.
* Ensures background jobs are processed efficiently.

### Status Handling

* Status (`pending`, `processing`, `completed`, `failed`) stored as **enum**.
* Prevents later issues when scaling (type-safe and readable).

### Error Handling

* Failures (API issues, translation errors) are logged using Laravel logging.
* Original content is preserved in case of failure for safe retry.

---

## API Endpoints

### Create a Translation

```http
POST /api/translations
```

**Request Body:**

```json
{
  "name": "test",
  "title": "test",
  "description": "test",
  "target_language": "es"
}
```

**Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "id": 17,
    "name": "test",
    "title": "test",
    "description": "test",
    "translated": null,
    "target_language": "es",
    "status": "pending"
  }
}
```

### Get a Translation

```http
GET /api/translations/{id}
```

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "id": 17,
    "name": "test",
    "title": "test",
    "description": "test",
    "translated": {
      "description": "prueba"
    },
    "target_language": "es",
    "status": "completed"
  }
}
```

---

## Validation

* Input fields are validated via **FormRequest** (`name`, `title`, `description`, `target_language`).
* ISO 639-1 validation is applied to ensure correct language codes (`es`, `fr`, `de`).
* Route model binding handles **record not found** automatically.
* Controllers remain thin with no manual try/catch or type checks.

---

## Possible Improvements

1. Introduce **DTOs** for request/response handling.
2. Centralized **API response helper** for success/error responses.
3. Optional Redis caching for repeated translations.
4. Add **interface binding** for services for better testability.

---

## Reviewer Notes

* **Status as Enum**: Strings chosen for clarity; integer ID table could improve storage efficiency in large-scale apps.
* **Responses**: `TranslationResource` keeps API responses consistent; centralized helpers could be added in future.
* **Redis Usage**: Used as queue driver; no extra caching added for simplicity.
* **Queue Worker**: Auto-starts in container; relies on Redis for background processing.


