![CI](https://github.com/SilverNate/leads-management-api/actions/workflows/ci.yml/badge.svg)


# Lead Management API

A Laravel-based API for managing and forwarding lead data, with centralized error logging, Redis caching, and PostgreSQL storage.

---

## 🚀 Features

- **Lead Management API**:
  - `POST /api/leads`: Store lead data in `leads_db` (PostgreSQL) and forward to a third-party dummy
  - `GET /api/leads`: Retrieve all leads with Redis caching.
  - `GET /api/leads/{id}`: Retrieve specific lead details.

- **Error Logging**:
  - Logs errors to a separate PostgreSQL database (`error_logs`).

- **Security**:
  - All API endpoints protected with Bearer Token authentication.

- **Databases**:
  - PostgreSQL for both leads and error logs.
  - Includes SQL scripts for schema and sample data.

- **Containerization**:
  - Full Docker Compose setup (Nginx, PHP-FPM, PostgreSQL x2, Redis).

- **Testing**:
  - API tests with PHPUnit.

- **Caching**:
  - Redis caching for lead listing.

- **CI/CD**:
  - GitHub Actions workflow for automated testing (with deploy stubs).

---

## 📦 Prerequisites

- [Git](https://git-scm.com/)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- A code editor VS Code

---

## 🛠 Getting Started

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd lead-management-api
cp .env.example .env
```

### 2. Build and start services

```bash
docker compose build
docker compose up -d
```

This will start:
- Nginx
- PHP-FPM
- PostgreSQL (Leads)
- PostgreSQL (Error Logs)
- Redis

Verify Service 
```bash
docker compose ps
```

### 3.Stop services
- Windows : ctrl + c

```bash
docker compose down
```

### 4. Wordpress installation
1. Open your web browser and go to http://localhost:8080. Follow the on-screen WordPress installation
2. Log in to your WordPress Dashboard after the installation is complete.
3. Check active theme on wordpress admin
4. Create new page
5. Edit page on right-side bar on template, choose <b>Lead Submission Form</b> from drop-down.
6. Publish then open the page
7. Fill the form
8. Add utm source on the url example: http://localhost:8080/?page_id=28<b>&utm_source=facebook</b>
9. Submit

## 📡 API Endpoints

### Authentication
- Authorization: Bearer your_super_secret_api_token_12345

- POST /api/leads
```
Store a new lead 

URL: http://localhost/api/leads

Method: POST

Headers: Content-Type: application/json

Authorization: Bearer <YOUR_API_BEARER_TOKEN>

Example :
{
  "name": "Jane Doe",
  "email": "jane.doe@example.com",
  "phone": "555-123-4567",
  "source": "website_form",
  "message": "Interested in premium features."
}


Responses
201 Created: Lead stored & forwarded

422 Unprocessable Entity: Validation errors

401 Unauthorized: Missing/invalid token

500 Internal Server Error: DB or third-party issue

```

- GET /api/leads
```
Retrieve all leads (cached using Redis).

URL: http://localhost/api/leads

Method: GET

Headers: Authorization: Bearer <YOUR_API_BEARER_TOKEN>

Responses
200 OK: List of leads

401 Unauthorized: Missing/invalid token

500 Internal Server Error: Retrieval failure
```

- GET /api/leads/{id}
Retrieve a single lead by ID.

```
URL: http://localhost/api/leads/1

Method: GET

Headers: Authorization: Bearer <YOUR_API_BEARER_TOKEN>

Responses
200 OK: Lead found

404 Not Found: Lead doesn't exist

401 Unauthorized: Missing/invalid token

500 Internal Server Error: Retrieval failure
```

## Unit Testing
```
php artisan migrate --env=testing
/vendor/bin/phpunit
```
- if error happen delete error_log in database error_log_test cause refreshtable not include other than main DB.


## Troubleshooting
Issue with api not running properly :
```bash
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan route:clear
```

