<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_appointment()
    {
        $appointment = Appointment::factory()->create();
        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id
        ]);

        $this->assertInstanceOf(Appointment::class, $reminder->appointment);
        $this->assertEquals($appointment->id, $reminder->appointment->id);
    }

    #[Test]
    public function it_casts_attributes_correctly()
    {
        $reminder = AppointmentReminder::factory()->create([
            'minutes_before' => 60,
            'is_enabled' => true
        ]);

        $this->assertIsInt($reminder->minutes_before);
        $this->assertIsBool($reminder->is_enabled);
    }

    #[Test]
    public function it_can_be_disabled()
    {
        $reminder = AppointmentReminder::factory()->disabled()->create();

        $this->assertFalse($reminder->is_enabled);
    }

    #[Test]
    public function it_can_set_notification_methods()
    {
        $emailReminder = AppointmentReminder::factory()->email()->create();
        $smsReminder = AppointmentReminder::factory()->sms()->create();
        $bothReminder = AppointmentReminder::factory()->bothMethods()->create();

        $this->assertEquals('email', $emailReminder->notification_method);
        $this->assertEquals('sms', $smsReminder->notification_method);
        $this->assertEquals('both', $bothReminder->notification_method);
    }
} 