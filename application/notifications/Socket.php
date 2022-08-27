<?php

namespace application\notifications;

use application\core\Channels\SocketChannel;
use application\notifications\traits\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class Socket extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    /**
     * @param array $data = [
     *    'rooms' => [(array)],     // default ['chat-<userId>']
     *    'method' => (string),
     *    'params' => (array),
     *    'sender_id' => [(int)],
     *    'options' => [(array)]    // socket options
     * ]
     */
    public function __construct(array $data) {
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
        return [
            SocketChannel::class,
        ];
    }

    /**
     * Additional notification channels
     *
     * @return array
     */
    public function viaAdditional()
    {
        return [
            'database'
        ];
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function toSocket($notifiable)
    {
        return $this->data;
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
