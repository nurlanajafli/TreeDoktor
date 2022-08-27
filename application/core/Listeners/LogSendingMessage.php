<?php

namespace application\core\Listeners;

use Illuminate\Mail\Events\MessageSending;

class LogSendingMessage
{
    /**
     * Handle the event.
     *
     * @param  MessageSending $event
     * @return void
     */
    public function handle($event)
    {
//        $event->message->getSubject();
//        $event->message->getBody();
//        $event->message->getHeaders();

//        file_put_contents('tmp/logs/mail_test_sending_'. time() . '.txt', json_encode($event));
    }
}