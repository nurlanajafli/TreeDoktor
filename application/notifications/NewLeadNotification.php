<?php

namespace application\notifications;

use application\notifications\traits\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var
     */
    private $data;

    /**
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New lead created!')
            ->greeting('New lead form was submited')
            ->line("Lead # " . $this->data['lead_no'] . " has been created")
            ->line('Name: ' . $this->data['name'])
            ->line('Address: ' . $this->data['lead_city'] . ', ' . $this->data['lead_address'] . ', ' . $this->data['lead_zip'])
            ->line('Phone: ' . $this->data['phone'])
            ->line('Email: ' . $this->data['email'])
            ->line('Message: ' . $this->data['message']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'notify_id' => $notifiable->id,
            'data' => $this->data
        ];
    }
}
