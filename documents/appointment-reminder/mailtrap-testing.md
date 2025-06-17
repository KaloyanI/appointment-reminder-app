# Testing Appointment Reminders with Mailtrap

## 1. Mailtrap Configuration

Update your `.env` file with your Mailtrap credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="appointments@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 2. Testing Process with Postman

### Step 1: Authentication

1. Login to get your token:
```http
POST /api/login
Content-Type: application/json

{
    "email": "your-email@example.com",
    "password": "your-password"
}
```

Save the returned token for subsequent requests.

### Step 2: Create a Client

```http
POST /api/clients
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "preferred_notification_method": "email"
}
```

Save the returned `client_id`.

### Step 3: Create an Appointment

```http
POST /api/appointments
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "client_id": "CLIENT_ID_FROM_STEP_2",
    "title": "Dental Checkup",
    "description": "Regular dental checkup and cleaning",
    "start_time": "2024-03-25T14:00:00",
    "end_time": "2024-03-25T15:00:00",
    "timezone": "America/New_York",
    "reminder_before_minutes": 30,
    "location": "123 Medical St, Suite 100"
}
```

Save the returned `appointment_id`.

### Step 4: Trigger Reminder Manually

```http
POST /api/appointments/{appointment_id}/trigger-reminder
Authorization: Bearer {your-token}
```

## 3. Verifying Emails in Mailtrap

1. Log in to your Mailtrap account (https://mailtrap.io)
2. Go to your inbox
3. You should see the reminder email with:
   - Subject: "Reminder: Dental Checkup"
   - Recipient: john@example.com
   - Content including appointment details

## 4. Testing Different Scenarios

### Test 1: Immediate Reminder

```http
POST /api/appointments
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "client_id": "CLIENT_ID",
    "title": "Urgent Consultation",
    "description": "Immediate consultation needed",
    "start_time": "2024-03-20T16:00:00",
    "end_time": "2024-03-20T16:30:00",
    "timezone": "UTC",
    "reminder_before_minutes": 5,
    "location": "Video Call"
}
```

### Test 2: Future Reminder

```http
POST /api/appointments
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "client_id": "CLIENT_ID",
    "title": "Annual Review",
    "description": "Yearly health checkup",
    "start_time": "2024-04-15T10:00:00",
    "end_time": "2024-04-15T11:00:00",
    "timezone": "Europe/London",
    "reminder_before_minutes": 1440,  // 24 hours before
    "location": "Main Clinic"
}
```

### Test 3: Multiple Reminders

Create multiple appointments with different reminder times to test email queuing:

```http
POST /api/appointments
Authorization: Bearer {your-token}
Content-Type: application/json

{
    "client_id": "CLIENT_ID",
    "title": "Quick Checkup",
    "start_time": "2024-03-21T09:00:00",
    "end_time": "2024-03-21T09:30:00",
    "timezone": "Asia/Tokyo",
    "reminder_before_minutes": 60
}
```

## 5. Troubleshooting

### Common Issues and Solutions

1. **Emails Not Showing in Mailtrap**
   - Verify Mailtrap credentials in `.env`
   - Check Laravel logs: `storage/logs/laravel.log`
   - Ensure queue worker is running: `php artisan queue:work`

2. **Wrong Email Content**
   - Check timezone settings
   - Verify client email address
   - Check appointment details

3. **Failed Reminders**
   - View failed jobs: `php artisan queue:failed`
   - Retry failed jobs: `php artisan queue:retry all`

### Debugging Tips

1. Enable queue logging:
```env
QUEUE_DRIVER=sync  # For immediate processing
```

2. Monitor queue status:
```bash
# View failed jobs
php artisan queue:failed

# Clear failed jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

3. Check email content in Mailtrap:
- HTML/Text versions
- Headers and metadata
- Spam score
- Email validation

## 6. Testing Checklist

✓ Mailtrap configuration  
✓ Client creation  
✓ Appointment creation  
✓ Manual reminder trigger  
✓ Email delivery verification  
✓ Content formatting  
✓ Timezone handling  
✓ Queue processing  
✓ Error handling 