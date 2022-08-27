<?php

namespace application\notifications;

use application\notifications\traits\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use NotificationChannels\Fcm\Resources\NotificationPriority;
use NotificationChannels\Fcm\Resources\Visibility;

class UserPush extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function via($notifiable = null)
    {
        return [
            FcmChannel::class,
        ];
    }

    /**
     * Additional notification channels
     *
     * @return string[]
     */
    public function viaAdditional()
    {
        return [
            'database'
        ];
    }

    public function toFcm($notifiable)
    {
        $body = $this->data['body'] ? html_entity_decode(html_entity_decode($this->data['body'], ENT_QUOTES), ENT_QUOTES) : '';

        return FcmMessage::create()
            ->setData([
                'arbostar_data' => json_encode([
                    'action' => $this->data['action'] ?? '',
                    'params' => $this->data['params'] ?? [],
                    'message' => [
                        'title' => $this->data['title'] ?? 'Notification',
                        'body' => $body
                    ]
                ])
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($this->data['title'] ?? 'Notification')
                    ->setBody($body)
    //                ->setImage(config_item('notification_img_url') ?? 'https://dev.arbostar.com/assets/brands/main.png'))
                    ->setImage($this->data['image_url'] ?? null)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()
//                        ->setColor($this->data['color'] ?? '#0A0A0A')
                        ->setSound('default')
                        ->setTag($this->data['tag'] ?? null)
                        ->setNotificationPriority(NotificationPriority::PRIORITY_HIGH())
                        ->setDefaultSound(true)
                        ->setVisibility(Visibility::PUBLIC())
                    )
            )
            ->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()
                        ->setAnalyticsLabel('analytics_ios')
                        ->setImage($this->data['image_url'] ?? null)
                    )
                    ->setPayload([
                        'aps' => [
                            'sound' => 'default',
                            'mutable-content' => isset($this->data['image_url']) ? 1 : 0
                        ]
                    ])
            );
    }

    // optional method when using kreait/laravel-firebase:^3.0, this method can be omitted, defaults to the default project
    public function fcmProject($notifiable, $message)
    {
        // $message is what is returned by `toFcm`
        return 'app'; // name of the firebase project to use
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
