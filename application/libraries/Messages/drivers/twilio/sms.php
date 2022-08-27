<?php

use application\modules\messaging\models\Messages as MessagesModel;
use Twilio\Rest\Client;

//define("AUTHORIZENET_LOG_FILE", "phplog");

/**
 * Class sms
 * @mixin Messages
 */
class sms extends CI_Driver implements MessagesInterface
{
    /** @var $client \Twilio\Rest\Client */
    protected $client;

    public function init()
    {
        try {
            $this->client = new Client($this->CI->config->item('sms_twilio_account_sid'), $this->CI->config->item('sms_twilio_auth_token_sid'));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }


    public function callback($post)
    {
        // TODO: Implement callback() method.
    }

    public function incoming($post)
    {
        // See: https://www.twilio.com/docs/messaging/guides/webhook-request
        // 'SmsStatus' - Same as 'MessageStatus' value. Deprecated and included for backward compatibility.
        if (!empty($post['From']) && (!empty($post['MessageStatus']) || !empty($post['SmsStatus'])) && !empty($post['SmsSid'])) {
            $this->CI->load->helper('message');

            $from = $post['From'];
            $body = $post['Body'] ?? null;
            $sms_sid = $post['SmsSid'];
            $messageStatus = $post['MessageStatus'] ?? $post['SmsStatus'];

            if ($message = MessagesModel::where('sms_sid', $sms_sid)->first()) {
                $message->sms_status = $messageStatus;
                $message->save();

                // add to paid limit number of sms with error or failed statuses if paid limit used
                if ($messageStatus === 'error' || $messageStatus === 'failed') {
                    check_update_sms_limits($message->sms_segment, false, true);
                }

                $socketData = [
                    'method' => 'updateSmsStatus',
                    'params' => [
                        'sms_id' => $message->sms_id,
                        'sms_status' => $message->sms_status,
                        'sms_error' => $message->sms_error
                    ]
                ];
            } else {
                $phoneCleanLength = (int)config_item('phone_clean_length');
                $phoneCountryCode = config_item('phone_country_code');

                $cleanNumber = str_replace('+', '', $from);

                if (strlen($from) - strlen($phoneCountryCode) == $phoneCleanLength) {
                    $cleanNumber = ltrim($from, $phoneCountryCode);

                    // if the number corresponds to the system number of digits, but is not
                    if (strpos($cleanNumber, '+') !== false) {
                        $cleanNumber = str_replace('+', '', $cleanNumber);
                    }
                }

                $segments = count_sms_segments($body);

                $message = MessagesModel::create([
                    'sms_sid' => $sms_sid,
                    'sms_number' => $cleanNumber,
                    'sms_body' => $body,
                    'sms_date' => date('Y-m-d H:i:s'),
                    'sms_support' => 0,
                    'sms_readed' => 0,
                    'sms_user_id' => 0,
                    'sms_incoming' => 1,
                    'sms_status' => $messageStatus,
                    'sms_error' => null,
                    'sms_segment' => $segments,
                    'sms_provider' => 'twilio/sms'
                ]);

                check_update_sms_limits($segments);

                $user_sms_limit = get_user_sms_limit();
                $count_unreaded = get_count_unreaded_sms();
                $socketData = [
                    'method' => 'refreshChatboxes',
                    'params' => [
                        'incoming' => true,
                        'user_sms_limit' => $user_sms_limit,
                        'count_unreaded' => $count_unreaded,
                        'number' => $cleanNumber
                    ]
                ];
            }

            pushJob('common/socket_send', [
                'room' => ['sms'],
                'message' => $socketData
            ]);
        }
    }

    /**
     * Send SMS
     *
     * @param $number
     * @param $message
     * @param null $from
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance
     * @throws MessagesException
     */
    public function send($number, $message, $from = null)
    {
        try {
            $result = $this->client->messages->create($number, array(
                'from' => '+' . config_item('twilioNumber'),
                'body' => $message . ($from ? ("\n" . $from) : ""),
                'messagingServiceSid' => config_item('messagingServiceSid'),
                'statusCallback' => base_url('messaging/callback/twilio_sms/incoming')
            ));
        } catch (Exception $e) {
            throw new MessagesException($e->getMessage());
        }

        return $result;
    }
}

