<?php

namespace application\core\Channels;

use ElephantIO\Engine\SocketIO\Version1X;
use Illuminate\Notifications\Notification;
use ElephantIO\Client as WSClient;

class SocketChannel
{
    /**
     * SocketChannel constructor.
     */
    public function __construct() {}

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return bool
     * @throws \Exception
     */
    public function send($notifiable, Notification $notification) {
        // Get the message from the notification class
        $data = $notification->toSocket($notifiable);

        $options = $data['options'] ?? [];
        $rooms = ($data['rooms'] ?? null) ? (is_array($data['rooms']) ? $data['rooms'] : [$data['rooms']]) : ['chat-' . $notifiable->id];
        $method = $data['method'] ?? null;
        $params = $data['params'] ?? [];
        $sender_id = $data['sender_id'] ?? $notifiable->id;

        if (!$rooms || !$method) {
            throw new \Exception('No required data');
        }

        $wsClient = new WSClient(
            new Version1X(config_item('wsClient') . '?chat=1&user_id=' . $sender_id, $options)
        );

        if ($wsClient) {
            $wsClient->initialize();
            $wsClient->emit('room', $rooms);
            $wsClient->emit('message', [
                'method' => $method,
                'params' => $params
            ]);
            $wsClient->close();

            return true;
        }

        throw new \Exception('No WSClient');
    }
}