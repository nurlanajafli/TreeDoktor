<?php

namespace application\core\Listeners;

use application\modules\emails\models\Email;
use Illuminate\Mail\Events\MessageSent;

class LogSentMessage
{
    /**
     * Handle the event.
     *
     * @param  MessageSent $event
     * @return void
     */
    public function handle($event)
    {
        $message = $event->message;
        $messageId = $message
            ->getHeaders()
            ->get('x-ses-message-id')
            ->getValue();

        $from = is_array($message->getFrom()) && sizeof($message->getFrom()) ? array_keys($message->getFrom())[0] : $message->getFrom() ?? '';
        $to = is_array($message->getTo()) && sizeof($message->getTo()) ? array_keys($message->getTo())[0] : $message->getTo() ?? '';
        $cc = is_array($message->getCc()) && sizeof($message->getCc()) ? array_keys($message->getCc())[0] : $message->getCc() ?? null;
        $bcc = is_array($message->getBcc()) && sizeof($message->getBcc()) ? array_keys($message->getBcc())[0] : $message->getBcc() ?? null;

        $email = [
            'email_message_id' => $messageId,
            'email_from' => $from ?? '',
            'email_to' => $to ?? '',
            'email_cc' => $cc ?? null,
            'email_bcc' => $bcc ?? null,
            'email_subject' => $event->message->getSubject() ?? '',
            'email_status' => 'accepted',
            'email_user_id' => request()->user()->id ?? null,
            'email_template_id' => null,
            'email_provider' => 'amazon',
            'email_error' => null,
            'email_created_at' => getNowDateTime(),
            'email_updated_at' => getNowDateTime()
        ];

        Email::createEmail($email);
    }
}