# Appointment Reminder System

A Laravel-based appointment reminder system built with Laravel Sail for easy Docker development.

## Features

- Appointment scheduling and management
- Automated reminder notifications
- Built with Laravel Framework 12.19.0
- Docker development environment with Laravel Sail
- MySQL database
- Redis caching

## Requirements

- **Docker Desktop** (for macOS/Windows) or **Docker Engine** (for Linux)
- **PHP 8.2+** (for running Composer commands locally)
- **Composer** (PHP package manager)

## Quick Start

### 1. Clone and Setup

```bash
git clone <repository-url>
cd appointment-reminder
cp .env.example .env
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Start the Development Environment

```bash
# Start all services (MySQL, Redis, and the application)
./vendor/bin/sail up

# Or run in the background
./vendor/bin/sail up -d
```

### 4. Run Database Migrations

```bash
# Run migrations to create database tables
./vendor/bin/sail artisan migrate

# (Optional) Seed the database with sample data
./vendor/bin/sail artisan db:seed
```

### 5. Access the Application

- **Application**: http://localhost:8080
- **Database**: Connect to `localhost:3307` (MySQL)
- **Redis**: Connect to `localhost:6380`

## Development Commands

### Starting and Stopping Services

```bash
# Start services
./vendor/bin/sail up

# Start services in background
./vendor/bin/sail up -d

# Stop services
./vendor/bin/sail down

# Stop services and remove volumes (clears database)
./vendor/bin/sail down -v
```

### Laravel Commands

```bash
# Run Artisan commands
./vendor/bin/sail artisan make:model Appointment
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed

# Generate application key
./vendor/bin/sail artisan key:generate

# Clear caches
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
```

### Testing

```bash
# Run tests
./vendor/bin/sail test

# Run tests with coverage
./vendor/bin/sail test --coverage
```

### Frontend Development

```bash
# Install Node.js dependencies
./vendor/bin/sail npm install

# Build assets for development
./vendor/bin/sail npm run dev

# Build assets for production
./vendor/bin/sail npm run build

# Watch for changes during development
./vendor/bin/sail npm run dev -- --watch
```

## Database Access

### Using MySQL Client

```bash
# Connect to MySQL container
./vendor/bin/sail mysql

# Or connect from external client
# Host: localhost
# Port: 3307
# Username: sail
# Password: password
# Database: appointment_reminder
```

### Using Redis Client

```bash
# Connect to Redis container
./vendor/bin/sail redis
```

## Troubleshooting

### Port Conflicts

If you encounter port conflicts, the following ports are used:

- **8080**: Application (Laravel)
- **3307**: MySQL Database
- **6380**: Redis
- **5173**: Vite (frontend development server)

To change ports, update the `.env` file:

```env
APP_PORT=8080
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
VITE_PORT=5173
```

### Docker Issues

```bash
# Rebuild containers
./vendor/bin/sail build --no-cache

# Reset everything (removes all data)
./vendor/bin/sail down -v
docker system prune -a
./vendor/bin/sail up
```

### Permission Issues

If you encounter permission issues:

```bash
# Fix storage permissions
./vendor/bin/sail artisan storage:link
sudo chown -R $USER:$USER storage bootstrap/cache
```

## Project Structure

```
appointment-reminder/
├── app/                    # Application code
├── config/                 # Configuration files
├── database/              # Migrations and seeders
├── resources/             # Views, assets, and language files
├── routes/                # Route definitions
├── storage/               # Logs, cache, and file uploads
├── tests/                 # Test files
├── docker-compose.yml     # Docker services configuration
├── .env                   # Environment variables
└── README.md             # This file
```

## API Documentation

The project includes comprehensive API endpoints for:
- Authentication and user management
- Appointment scheduling and management
- Reminder configuration and dispatch
- Admin analytics and controls

Detailed API documentation can be found in the `/documents` directory:
- `documents/appointment-reminder/appointments-api.md`: Appointment management endpoints
- `documents/appointment-reminder/reminder-apis.md`: Reminder system endpoints
- `documents/auth/README.md`: Authentication endpoints
- `documents/bonus/analytics.md`: Admin analytics endpoints

## Reminder System Configuration

Configure the reminder system in `config/reminders.php`:
```env
# Reminder timing configuration
REMINDER_DEFAULT_OFFSET=24 # Hours before appointment
REMINDER_RETRY_ATTEMPTS=3
REMINDER_RETRY_DELAY=15 # Minutes

# Notification channels
NOTIFICATION_CHANNELS=["mail", "sms"]
```

## Notification Channels

The system supports multiple notification channels for appointment reminders:
- Email notifications (default)
- SMS notifications (requires configuration)
- Additional channels can be added by extending the `AppointmentReminder` notification class

## Admin Features

The system includes an admin panel with:
- Analytics dashboard for reminder performance
- User management
- System-wide reminder configuration
- Access restricted via `CheckAdmin` middleware

## Testing

### Automated Tests

The project includes comprehensive test suites:
- Feature tests for API endpoints
- Unit tests for core functionality
- Authentication flow tests

### API Testing with Postman

A complete Postman collection is available at `/tests/Postman/appointment-reminder.postman_collection.json`

To use:
1. Import the collection into Postman
2. Set up environment variables:
   - `base_url`: Your local development URL
   - `token`: Authentication token after login

## Environment Configuration

Key environment variables in `.env`:

```env
APP_NAME="Appointment Reminder"
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=appointment_reminder
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379
```

## About Laravel Framework

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
