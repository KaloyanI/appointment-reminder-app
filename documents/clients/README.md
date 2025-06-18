# Client API Documentation

## Overview
This document outlines the available API endpoints for managing clients in the Appointment Reminder system. All endpoints require authentication using a Bearer token.

## Authentication
All endpoints require a valid Bearer token obtained through the authentication process.

**Headers Required:**
```
Authorization: Bearer {your_token}
Accept: application/json
Content-Type: application/json
```

## API Endpoints

### 1. List Clients
Retrieves a paginated list of clients with optional filtering and sorting.

**Endpoint:** `GET /api/clients`

**Query Parameters:**
- `search` (optional): Search by name, email, or phone
- `per_page` (optional): Number of items per page (default: 15, max: 100)
- `sort_by` (optional): Field to sort by (options: name, email, created_at)
- `sort_direction` (optional): Sort direction (options: asc, desc)

**Example Request:**
```http
GET /api/clients?search=john&per_page=10&sort_by=name&sort_direction=asc
```

**Success Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "timezone": "America/New_York",
            "preferred_notification_method": "email",
            "notes": "VIP client",
            "created_at": "2024-03-20T12:00:00Z",
            "updated_at": "2024-03-20T12:00:00Z"
        }
        // ... more clients
    ],
    "links": {
        "first": "http://localhost/api/clients?page=1",
        "last": "http://localhost/api/clients?page=5",
        "prev": null,
        "next": "http://localhost/api/clients?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "http://localhost/api/clients",
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

### 2. Create Client
Creates a new client.

**Endpoint:** `POST /api/clients`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "timezone": "America/New_York",
    "preferred_notification_method": "email",
    "notes": "VIP client"
}
```

**Required Fields:**
- `name`: String, max 255 characters
- `email`: Valid email address, unique per user
- `timezone`: Valid timezone string
- `preferred_notification_method`: One of: email, sms, both

**Optional Fields:**
- `phone`: String, max 20 characters
- `notes`: String, max 1000 characters

**Success Response (201):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "timezone": "America/New_York",
    "preferred_notification_method": "email",
    "notes": "VIP client",
    "created_at": "2024-03-20T12:00:00Z",
    "updated_at": "2024-03-20T12:00:00Z"
}
```

### 3. Show Client
Retrieves a specific client by ID.

**Endpoint:** `GET /api/clients/{id}`

**Success Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "timezone": "America/New_York",
    "preferred_notification_method": "email",
    "notes": "VIP client",
    "created_at": "2024-03-20T12:00:00Z",
    "updated_at": "2024-03-20T12:00:00Z",
    "appointments": [
        // Array of associated appointments
    ]
}
```

### 4. Update Client
Updates an existing client.

**Endpoint:** `PUT /api/clients/{id}`

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "+1234567890",
    "timezone": "America/New_York",
    "preferred_notification_method": "both",
    "notes": "VIP client - updated"
}
```

**Note:** All fields are optional in updates. Only include fields that need to be updated.

**Success Response (200):**
```json
{
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    // ... other fields
}
```

### 5. Delete Client
Deletes a client.

**Endpoint:** `DELETE /api/clients/{id}`

**Success Response (204):**
Empty response with status code 204

### 6. Update Notification Preferences
Updates a client's notification preferences.

**Endpoint:** `PUT /api/clients/{id}/notification-preferences`

**Request Body:**
```json
{
    "preferred_notification_method": "sms"
}
```

**Required Fields:**
- `preferred_notification_method`: One of: email, sms, both

**Success Response (200):**
```json
{
    "message": "Notification preferences updated successfully",
    "data": {
        "id": 1,
        "preferred_notification_method": "sms",
        // ... other client fields
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email has already been taken."
        ]
    }
}
```

### Not Found (404)
```json
{
    "message": "Client not found."
}
```

### Unauthorized (401)
```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
    "message": "This client does not belong to you."
}
```

## Testing with Postman

1. Import the Postman collection from `tests/Postman/appointment-reminder.postman_collection.json`
2. Set up environment variables:
   - `base_url`: Your API base URL (e.g., `http://localhost:8000`)
   - `token`: Your authentication token
3. Use the "Clients" folder in the collection to test all endpoints
4. Make sure to authenticate first using the login endpoint

## Notes
- All timestamps are returned in UTC
- The API uses Laravel's built-in pagination
- Email addresses must be unique per user (you can have the same email for different users' clients)
- All endpoints require authentication
- Clients are scoped to the authenticated user 