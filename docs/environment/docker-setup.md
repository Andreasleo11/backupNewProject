# Docker Environment Setup

This document describes the Docker-based development and production environments for the Daijo Industrial Support System.

## Overview

The project uses a **separated Docker strategy**:

- **Development**: Uses [Laravel Sail](https://laravel.com/docs/sail) for a consistent local environment.
- **Production**: Uses a custom optimized multi-stage Dockerfile.

This separation follows best practices for maintainability and performance.

## Folder Structure

```
docker/
├── dev/                          # Development environment (Laravel Sail)
│   ├── 8.2/
│   │   ├── Dockerfile
│   │   ├── php.ini
│   │   ├── start-container
│   │   └── supervisord.conf
│   └── mysql/
│       └── create-testing-database.sh
│
└── prod/                         # Production environment
    ├── Dockerfile                # Multi-stage production build
    ├── entrypoint.sh
    └── nginx/
        └── default.conf

docker-compose.yml                # Development compose file
docker-compose.prod.yml           # Production compose file
.dockerignore
```

> **Note**: The `docker/dev/` structure was reorganized from the original Sail-published files for better separation of concerns.

## Development Environment

### Technology Stack
- PHP 8.2 (via Laravel Sail runtime)
- Node.js 20+ (for Vite)
- MySQL 8.4
- Redis (Alpine)
- Mailpit (email testing)

### How to Start Development

```bash
# Start all services
docker compose up -d

# Or using the Sail wrapper (recommended)
./sail up -d
```

### Useful Development Commands

```bash
# Artisan
./sail artisan migrate
./sail artisan migrate:fresh --seed
./sail artisan tinker

# Frontend
./sail npm run dev          # Vite development server (hot reload)
./sail npm run build

# Composer
./sail composer install
./sail composer update

# Shell access
./sail bash

# View logs
docker compose logs -f laravel.test
```

### Access Points (Development)

| Service       | URL                          | Notes |
|---------------|------------------------------|-------|
| Application   | http://localhost             | Laravel |
| Vite (HMR)    | http://localhost:5173        | Frontend development |
| Mailpit       | http://localhost:8025        | Email catcher |
| MySQL         | `mysql:3306` (inside network)| User: `sail`, Password: `password` |

## Production Environment

### Technology Stack
- PHP 8.2 FPM + Nginx
- Multi-stage build (Node + Composer)
- Optimized for production (no dev dependencies)
- wkhtmltopdf support included

### Building and Running Production

```bash
# Build the production image
docker compose -f docker-compose.prod.yml build

# Run the production stack
docker compose -f docker-compose.prod.yml up -d
```

### Production Compose File

The file `docker-compose.prod.yml` defines:
- `app` service using `docker/prod/Dockerfile`
- `mysql` and `redis` services
- Persistent volumes for database data
- Proper restart policies

### Production Dockerfile Features

- Multi-stage build (reduces final image size)
- Separate Node stage for Vite asset compilation
- Composer with `--no-dev --optimize-autoloader`
- Proper file permissions (`www-data`)
- Laravel production optimizations (`config:cache`, `route:cache`, `view:cache`)
- wkhtmltopdf binaries included for PDF generation

## Environment Variables

Both environments respect the project's `.env` file, with the following overrides in Docker:

### Development Overrides (`docker-compose.yml`)
- `DB_HOST=mysql`
- `MAIL_HOST=mailpit`
- `REDIS_HOST=redis`

### Production Overrides (`docker-compose.prod.yml`)
- `APP_ENV=production`
- `APP_DEBUG=false`
- Same service hostnames as development

## Important Notes

### wkhtmltopdf / PDF Generation

The project uses `barryvdh/laravel-snappy`. Both environments include the required system libraries and binaries:

- **Development**: Uses `h4cc/wkhtmltopdf-amd64` from vendor
- **Production**: Same binary is copied during the build

The paths are set via environment variables:
```env
SNAPPY_PDF_BINARY=/var/www/html/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64
SNAPPY_IMAGE_BINARY=/var/www/html/vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64
```

### Database

- Development and Production use **separate** MySQL instances.
- Never run `migrate:fresh --seed` against the production database.

### Switching Between Environments

- Use `docker compose` (or `./sail`) for development.
- Use `docker compose -f docker-compose.prod.yml` for production/staging.

## Recommended Workflow

1. Daily development → `docker compose up -d` + `./sail`
2. Before deploying → Build production image using `docker-compose.prod.yml`
3. CI/CD should use `docker/prod/Dockerfile` for building production images

## Related Documentation

- [Laravel Sail Documentation](https://laravel.com/docs/sail)
- `docker/prod/Dockerfile` – Production image definition
- `docker-compose.prod.yml` – Production orchestration

---

**Last Updated**: 2026-05-22
**Maintained by**: Development Team
