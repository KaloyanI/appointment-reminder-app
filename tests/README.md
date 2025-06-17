# Testing Guide for Appointment Reminder API

This guide explains how to run and manage tests for the Appointment Reminder API.

## Test Structure

The test suite is organized into three main categories:

1. **Feature Tests** (`tests/Feature/`)
   - `Auth/AuthenticationTest.php`: Tests for authentication endpoints
   - Tests user registration, login, logout, and profile retrieval

2. **Unit Tests** (`tests/Unit/`)
   - `UserTest.php`: Tests for User model functionality
   - Tests model attributes, relationships, and methods

3. **Postman Collection** (`tests/Postman/`)
   - `appointment-reminder.postman_collection.json`: Collection for manual API testing
   - Contains all API endpoints with example requests

## Running Tests

### Using Laravel Sail

```bash
# Run all tests
./vendor/bin/sail test

# Run with coverage report
./vendor/bin/sail test --coverage

# Run specific test file
./vendor/bin/sail test tests/Feature/Auth/AuthenticationTest.php

# Run specific test method
./vendor/bin/sail test --filter test_users_can_register
```

### Using PHPUnit Directly

```bash
# Run all tests
php artisan test

# Run with coverage report
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run specific test method
php artisan test --filter test_users_can_register
```

## Manual API Testing

### Using Postman

1. Import the collection from `tests/Postman/appointment-reminder.postman_collection.json`
2. Set up environment variables:
   - `base_url`: `http://localhost:8080`
   - `token`: Your authentication token (obtained after login)

### Using cURL

Test the API endpoints using these cURL commands:

```bash
# Register a new user
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# Get user profile (replace YOUR_TOKEN)
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Logout (replace YOUR_TOKEN)
curl -X POST http://localhost:8080/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## Test Coverage

The test suite covers:

1. Authentication
   - User registration
   - User login
   - User logout
   - Profile retrieval
   - Invalid credentials handling
   - Duplicate email prevention

2. User Model
   - API token functionality
   - Password hashing
   - Fillable attributes
   - Hidden attributes
   - Date casting

## Adding New Tests

When adding new tests:

1. Follow the existing naming conventions
2. Use appropriate assertions
3. Keep tests focused and isolated
4. Add test cases for both success and failure scenarios
5. Update this README when adding new test categories 