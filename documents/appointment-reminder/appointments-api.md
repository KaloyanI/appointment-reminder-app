# Appointments API Documentation

## Authentication

All appointment endpoints require authentication using Laravel Sanctum. You need to include the authentication token in your request headers:

```
Authorization: Bearer <your-token>
```

To get the authentication token, you need to login first using the auth endpoints.

## Base URL

All API endpoints are prefixed with `/api`.

## Available Endpoints

### 1. List Appointments
```http
GET /api/appointments
```

Retrieves a paginated list of appointments that can be filtered based on various criteria.

#### Query Parameters

| Parameter  | Type    | Required | Description                                    |
|------------|---------|----------|------------------------------------------------|
| filter     | string  | No       | Filter appointments by time: 'upcoming', 'past', or 'all' |
| client_id  | integer | No       | Filter appointments by specific client         |
| per_page   | integer | No       | Number of items per page (1-100, default: 15) |

#### Example Requests

1. Get all appointments:
```http
GET /api/appointments
```

2. Get upcoming appointments:
```http
GET /api/appointments?filter=upcoming
```

3. Get past appointments:
```http
GET /api/appointments?filter=past
```

4. Get appointments for a specific client with custom pagination:
```http
GET /api/appointments?client_id=1&per_page=20
```

#### Response Format

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "client_id": 1,
            "title": "Dental Checkup",
            "description": "Regular dental checkup and cleaning",
            "start_time": "2024-03-20T14:00:00Z",
            "end_time": "2024-03-20T15:00:00Z",
            "timezone": "America/New_York",
            "status": "scheduled",
            "is_recurring": false,
            "recurrence_rule": null,
            "reminder_before_minutes": 30,
            "location": "123 Medical St",
            "notes": "Patient requested early appointment",
            "created_at": "2024-03-15T10:00:00Z",
            "updated_at": "2024-03-15T10:00:00Z",
            "client": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                // ... other client details
            },
            "reminder_dispatches": [
                {
                    "id": 1,
                    "status": "pending",
                    "scheduled_at": "2024-03-20T13:30:00Z",
                    // ... other reminder details
                }
            ]
        }
        // ... more appointments
    ],
    "first_page_url": "http://your-domain.com/api/appointments?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://your-domain.com/api/appointments?page=5",
    "links": [
        // ... pagination links
    ],
    "next_page_url": "http://your-domain.com/api/appointments?page=2",
    "path": "http://your-domain.com/api/appointments",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
}
```

### Error Responses

#### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

#### 403 Forbidden
```json
{
    "message": "Not authorized."
}
```

#### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "filter": ["The selected filter is invalid."],
        "per_page": ["The per page must be between 1 and 100."]
    }
}
```

## Implementation Examples

### Using cURL

```bash
# Get upcoming appointments
curl -X GET 'http://your-domain.com/api/appointments?filter=upcoming' \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Accept: application/json'

# Get past appointments for specific client
curl -X GET 'http://your-domain.com/api/appointments?filter=past&client_id=1' \
  -H 'Authorization: Bearer your-token-here' \
  -H 'Accept: application/json'
```

### Using JavaScript/Axios

```javascript
// Get upcoming appointments
const getUpcomingAppointments = async () => {
  try {
    const response = await axios.get('/api/appointments', {
      params: {
        filter: 'upcoming'
      },
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching appointments:', error);
    throw error;
  }
};

// Get past appointments
const getPastAppointments = async () => {
  try {
    const response = await axios.get('/api/appointments', {
      params: {
        filter: 'past'
      },
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching appointments:', error);
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

// Get upcoming appointments
try {
    $response = $client->get('/api/appointments', [
        'query' => [
            'filter' => 'upcoming'
        ]
    ]);
    $appointments = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    // Handle error
}

// Get past appointments
try {
    $response = $client->get('/api/appointments', [
        'query' => [
            'filter' => 'past'
        ]
    ]);
    $appointments = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    // Handle error
}
```

## Notes

1. All timestamps are returned in UTC format. Convert them to the desired timezone on the client side using the `timezone` field provided in the appointment data.

2. The response is paginated by default with 15 items per page. Use the pagination links provided in the response to navigate through the results.

3. The appointments are always ordered by `start_time` in ascending order.

4. The response includes related client information and reminder dispatch details through Laravel's eager loading.

5. Make sure to handle timezone conversions appropriately when displaying dates and times to users. 