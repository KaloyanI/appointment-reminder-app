# Reminder System APIs

## Authentication

All reminder endpoints require authentication using Laravel Sanctum. Include the authentication token in your request headers:

```
Authorization: Bearer <your-token>
```

## Available Endpoints

### 1. List Reminders
```http
GET /api/reminders
```

Retrieves a paginated list of reminders for the authenticated user's appointments.

#### Query Parameters

| Parameter     | Type    | Required | Description                                    |
|--------------|---------|----------|------------------------------------------------|
| appointment_id| integer | No       | Filter reminders by specific appointment       |
| status       | string  | No       | Filter by status: 'pending', 'sent', 'failed'  |
| per_page     | integer | No       | Number of items per page (1-100, default: 15)  |

#### Example Requests

1. Get all reminders:
```http
GET /api/reminders
```

2. Get pending reminders for a specific appointment:
```http
GET /api/reminders?appointment_id=1&status=pending
```

#### Response Format

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "appointment_id": 1,
            "scheduled_at": "2024-03-20T13:30:00Z",
            "sent_at": null,
            "status": "pending",
            "notification_method": "email",
            "error_message": null,
            "retry_count": 0,
            "created_at": "2024-03-15T10:00:00Z",
            "updated_at": "2024-03-15T10:00:00Z",
            "appointment": {
                "id": 1,
                "title": "Dental Checkup",
                "start_time": "2024-03-20T14:00:00Z",
                "client": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            }
        }
    ],
    "first_page_url": "http://your-domain.com/api/reminders?page=1",
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
    // ... other pagination metadata
}
```

### 2. Manually Trigger Reminder
```http
POST /api/appointments/{appointment}/trigger-reminder
```

Manually triggers a reminder for a specific appointment, bypassing the scheduled time.

#### URL Parameters

| Parameter   | Type    | Required | Description           |
|------------|---------|----------|-----------------------|
| appointment | integer | Yes      | The appointment ID    |

#### Response Format

```json
{
    "id": 1,
    "appointment_id": 1,
    "scheduled_at": "2024-03-15T10:00:00Z",
    "status": "pending",
    "notification_method": "email",
    "created_at": "2024-03-15T10:00:00Z",
    "updated_at": "2024-03-15T10:00:00Z"
}
```

### 3. Retry Failed Reminder
```http
POST /api/reminders/{reminder}/retry
```

Retries a failed reminder dispatch.

#### URL Parameters

| Parameter | Type    | Required | Description        |
|-----------|---------|----------|--------------------|
| reminder  | integer | Yes      | The reminder ID    |

#### Response Format

```json
{
    "id": 1,
    "appointment_id": 1,
    "scheduled_at": "2024-03-15T10:00:00Z",
    "status": "pending",
    "notification_method": "email",
    "retry_count": 1,
    "created_at": "2024-03-15T10:00:00Z",
    "updated_at": "2024-03-15T10:00:00Z"
}
```

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "message": "Not authorized."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "status": ["The selected status is invalid."]
    }
}
```

## Implementation Examples

### Using cURL

```bash
# List all reminders
curl -X GET 'http://your-domain.com/api/reminders' \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Accept: application/json'

# Trigger reminder for appointment
curl -X POST 'http://your-domain.com/api/appointments/1/trigger-reminder' \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Accept: application/json'

# Retry failed reminder
curl -X POST 'http://your-domain.com/api/reminders/1/retry' \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Accept: application/json'
```

### Using JavaScript/Axios

```javascript
// List reminders
const getReminders = async (filters = {}) => {
  try {
    const response = await axios.get('/api/reminders', {
      params: filters,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching reminders:', error);
    throw error;
  }
};

// Trigger reminder
const triggerReminder = async (appointmentId) => {
  try {
    const response = await axios.post(`/api/appointments/${appointmentId}/trigger-reminder`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error triggering reminder:', error);
    throw error;
  }
};

// Retry failed reminder
const retryReminder = async (reminderId) => {
  try {
    const response = await axios.post(`/api/reminders/${reminderId}/retry`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error retrying reminder:', error);
    throw error;
  }
};
```

### Using PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://your-domain.com',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

// List reminders
try {
    $response = $client->get('/api/reminders', [
        'query' => [
            'status' => 'pending',
            'per_page' => 20
        ]
    ]);
    $reminders = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    // Handle error
}

// Trigger reminder
try {
    $response = $client->post('/api/appointments/1/trigger-reminder');
    $reminder = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    // Handle error
}

// Retry failed reminder
try {
    $response = $client->post('/api/reminders/1/retry');
    $reminder = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    // Handle error
}
``` 