<?php

namespace application\notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class Email extends Notification/* implements ShouldQueue*/
{
//    use Queueable;

    private $data;

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
//        $letter = \application\modules\clients\models\ClientLetter::find(9);
//        $letter = \application\modules\clients\models\ClientLetter::compileLetter($letter, 1, []);
//
//        $template = new HtmlString($letter->email_template_text);
        $template = new HtmlString($this->data['template_html']);


        return (new MailMessage)
//            ->greeting('Hello!')
//            ->line('Test message from SES!')
//            ->action('View', '/')
//            ->line('Thank you for using our application!')
            ->subject('Test notification email')
            ->line($template);
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
