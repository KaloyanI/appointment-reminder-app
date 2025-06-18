# Admin Panel API Documentation

## Overview
The Admin Panel API provides endpoints for administrators to view and analyze reminders and appointment statistics across all users in the system. These endpoints are protected by the admin middleware and are only accessible to users with administrative privileges.

## Authentication
All endpoints require authentication and admin privileges. Use the `admin` middleware.

## API Endpoints

### 1. List All Reminders
Retrieve a paginated list of all reminders across the system.

**Endpoint:** `GET /api/admin/reminders`

**Parameters:**
- `status` (optional) - Filter by reminder status ('pending', 'sent', 'failed')
- `user_id` (optional) - Filter by specific user
- `start_date` (optional) - Filter by date range start (Y-m-d)
- `end_date` (optional) - Filter by date range end (Y-m-d)
- `per_page` (optional) - Number of items per page (default: 15)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "appointment_id": 1,
            "status": "pending",
            "scheduled_at": "2024-03-20 10:00:00",
            "sent_at": null,
            "error_message": null,
            "appointment": {
                "id": 1,
                "user_id": 1,
                "client": {
                    "id": 1,
                    "name": "John Doe"
                }
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 50,
        "per_page": 15
    }
}
```

### 2. Reminder Statistics
Get statistical information about reminders across the system.

**Endpoint:** `GET /api/admin/reminders/stats`

**Parameters:**
- `start_date` (optional) - Filter by date range start (Y-m-d)
- `end_date` (optional) - Filter by date range end (Y-m-d)
- `user_id` (optional) - Filter by specific user

**Response:**
```json
{
    "total_reminders": 1000,
    "status_breakdown": {
        "pending": 200,
        "sent": 700,
        "failed": 100
    },
    "method_breakdown": {
        "email": 500,
        "sms": 300,
        "both": 200
    },
    "daily_stats": [
        {
            "date": "2024-03-20",
            "total": 50,
            "sent": 40,
            "failed": 10
        }
    ]
}
```

### 3. User Reminder Settings
View reminder settings across all users.

**Endpoint:** `GET /api/admin/reminder-settings`

**Parameters:**
- `user_id` (optional) - Filter by specific user
- `per_page` (optional) - Number of items per page (default: 15)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "appointment_id": 1,
            "minutes_before": 60,
            "notification_method": "email",
            "is_enabled": true,
            "appointment": {
                "id": 1,
                "client": {
                    "id": 1,
                    "name": "John Doe"
                }
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 30,
        "per_page": 15
    }
}
```

## Error Responses

### Unauthorized Access
```json
{
    "message": "Unauthorized access. Admin privileges required."
}
```
Status Code: 403

### Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "start_date": ["The start date must be a valid date."]
    }
}
```
Status Code: 422

## Notes
- All endpoints are protected by the `admin` middleware
- Dates should be provided in Y-m-d format
- Pagination is available for list endpoints
- Statistics can be filtered by date range and specific users 