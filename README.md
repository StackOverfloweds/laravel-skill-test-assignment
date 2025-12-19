# Laravel Skill Test 1

This repository contains my solution for Skill Test 1 using Laravel.
The project is fully containerized using Docker to ensure a consistent environment.

---

## Tech Stack
- Laravel
- PHP 8.3
- Nginx
- SQLite
- Docker & Docker Compose

---

## Project Structure
```
.
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── laravel-skill-test/
│   ├── app/
│   ├── database/
│   │   └── database.sqlite
│   ├── routes/
│   └── ...
├── docker-compose.yml
└── README.md
```

---

## Setup

```bash
docker compose up -d --build
docker compose exec app bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

---

## Author
Leonardo Bryan