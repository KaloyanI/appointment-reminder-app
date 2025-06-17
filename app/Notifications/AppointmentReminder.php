<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Appointment $appointment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $startTime = $this->appointment->getLocalStartTime();
        $endTime = $this->appointment->getLocalEndTime();
        
        return (new MailMessage)
            ->subject("Reminder: {$this->appointment->title}")
            ->greeting("Hello {$this->appointment->client->name},")
            ->line("This is a reminder for your upcoming appointment:")
            ->line("Title: {$this->appointment->title}")
            ->line("Date: " . $startTime->format('l, F j, Y'))
            ->line("Time: " . $startTime->format('g:i A') . " - " . $endTime->format('g:i A'))
            ->line("Location: " . ($this->appointment->location ?? 'Not specified'))
            ->when($this->appointment->description, function ($message) {
                return $message->line("Description: {$this->appointment->description}");
            })
            ->action('View Appointment Details', url('/appointments/' . $this->appointment->id))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'title' => $this->appointment->title,
            'start_time' => $this->appointment->start_time,
            'end_time' => $this->appointment->end_time,
            'location' => $this->appointment->location,
        ];
    }
} 