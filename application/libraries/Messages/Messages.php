<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\messaging\models\Messages as MessagesModel;
use application\modules\messaging\models\SmsCounter;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

//require_once APPPATH . "/libraries/MY_Driver_library.php";

interface_exists('MessagesInterface', false) or require_once(APPPATH . 'libraries/Messages/MessagesInterface.php');
class_exists('MessagesException', false) or require_once(APPPATH . 'libraries/Messages/MessagesException.php');

/**
 * Payment Class
 * @mixin bambora
 * @mixin sms
 */
class Messages extends MY_Driver_Library
{

    protected $_adapter = 'twilio/sms';

    public $CI;

    protected $sms_unlimited;

    /**
     * Class constructor
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->CI =& get_instance();
        $this->valid_drivers = $config['messages_valid_drivers'];

        $smsProvider = config_item('smsProvider');
        if ($smsProvider && $this->validateAdapter($smsProvider)) {
            $this->setAdapter($smsProvider);
        }

        $this->CI->load->helper('message');

        $this->sms_unlimited = !!config_item('sms_unlimited');
    }

    /**
     * Send message
     *
     * @param $numbers
     * @param $message
     * @param null $from
     * @return array|string[]
     * @throws \libphonenumber\NumberParseException
     */
    public function send($numbers, $message, $from = null): array
    {
        $result = [];
        $noMessageError = false;

        if (!$message) {
            $noMessageError = true;
            $result[] = [
                'error' => 'No message'
            ];
        }

        $phoneCleanLength = (int)config_item('phone_clean_length');
        $phoneCountryCode = config_item('phone_country_code');

        $numbers = is_array($numbers) ? $numbers : explode(',', $numbers);

        foreach ($numbers as $idx => $number) {
            $idx = $noMessageError ? ($idx + 1) : $idx;

            $number = preg_replace('/[+\(\)\s-]+/', '', $number);

            $cleanNumber = null;

            // strlen($number) > 16 - according to E.164 standard max 15 digits, plus (+) symbol
            if (strlen($number) < $phoneCleanLength || strlen($number) > 16) {
                $result[$idx]['error'] = 'Invalid phone number: ' . $number;
                continue;
            } elseif (strlen($number) == $phoneCleanLength) {
                $cleanNumber = $number;
                $number = config_item('phone_country_code') . $number;
            } elseif (!strpos($number, '+')) {
                $number = '+' . $number;
            }

            $phoneUtil = PhoneNumberUtil::getInstance();

            try {
                $numberProto = $phoneUtil->parse($number);
                $isValidPhone = $phoneUtil->isValidNumber($numberProto);
            }
            catch (Exception $e) {
                $result[$idx]['error'] = 'Number parsing error: ' . $number;
                $result[$idx]['debug'] = $e->getMessage();
                continue;
            }

            if (!$isValidPhone) {
                $result[$idx]['error'] = 'Invalid phone number: ' . $number;
                continue;
            }

            if ($noMessageError) {
                continue;
            }

            $messageSegments = count_sms_segments($message);

            if (!$this->sms_unlimited) {
                $smsCounts = SmsCounter::getCurrentCountRemain();

                if (!$smsCounts || $smsCounts->remain < $messageSegments) {
                    if ($idx === 0) {
                        $result[$idx]['error'] = 'Increase limit of sms';

                        return $result;
                    }

                    if (sizeof($result) > 1) {
                        $errors = 0;
                        // check for previous errors
                        foreach ($result as $res) {
                            if (isset($res['error'])) {
                                $errors++;
                            }
                        }

                        // if some messages was sent do refreshChatboxes
                        if (sizeof($result) !== $errors) {
                            $user_sms_limit = get_user_sms_limit();
                            $this->socketNotification([
                                'method' => 'refreshChatboxes',
                                'params' => [
                                    'user_sms_limit' => $user_sms_limit
                                ]
                            ]);
                        }
                    }

                    $result[$idx]['error'] = 'Increase limit of sms';

                    return $result;
                }
            }

            $number = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);

            $error = false;
            try {
                $sendResult = $this->{$this->_adapter}->send($number, $message);
            } catch (MessagesException $e) {
                $error = true;
                $result[$idx]['error'] = 'Error send sms';
                $result[$idx]['debug'] = $e->getMessage();
                $cleanNumber = null;
            }

            if (!$cleanNumber) {
                if (strlen($number) - strlen($phoneCountryCode) == $phoneCleanLength) {
                    $cleanNumber = ltrim($number, $phoneCountryCode);

                    // if the number corresponds to the system number of digits, but is not
                    if (strpos($cleanNumber, '+') !== false) {
                        $cleanNumber = str_replace('+', '', $cleanNumber);
                    }
                } else {
                    $cleanNumber = str_replace('+', '', $number);
                }
            }

            $numSegments = $sendResult->numSegments ?? 0 ?: $messageSegments;

            try {
                $storedMessage = MessagesModel::create([
                    'sms_sid' => $error ? null : $sendResult->sid ?? null,
                    'sms_number' => $cleanNumber,
                    'sms_body' => $message,
                    'sms_date' => now()->toDateTimeString(),
                    'sms_support' => 0,
                    'sms_readed' => 1,
                    'sms_client_id' => null,
                    'sms_user_id' => $this->CI->session->user_id ?? 0,
                    'sms_incoming' => 0,
                    'sms_status' => $error ? 'error' : 'queued',
                    'sms_error' => $error ? $result[$idx]['error'] : null,
                    'sms_provider' => $this->_adapter,
                    'sms_segment' => $error ? 0 : $numSegments,
                    'sms_debug' => $error && !empty($result[$idx]['debug']) ? $result[$idx]['debug'] : null
                ]);

                $result[$idx]['message'] = $storedMessage->toArray();
            }
            catch (Exception $e) {
                $result[$idx]['message'] = null;
                $result[$idx]['error'] = 'Error store sms';
                $result[$idx]['debug'] = $e->getMessage();
            }

            check_update_sms_limits($numSegments, $error);

            $result[$idx]['number'] = $cleanNumber;
        }

        if (sizeof($result)) {
            $errors = 0;
            $sentToNumbers = [];

            foreach ($result as $res) {
                if (array_key_exists('error', $res)) {
                    $errors++;
                } else {
                    $sentToNumbers[] = $res['number'];
                }
            }

            // if some messages was sent do refreshChatboxes
            if (sizeof($numbers) > $errors) {
                $user_sms_limit = get_user_sms_limit();
                $this->socketNotification([
                    'method' => 'refreshChatboxes',
                    'params' => [
                        'user_sms_limit' => $user_sms_limit,
                        'number' => $sentToNumbers
                    ]
                ]);
            }
        }

        return $result;
    }

    public function socketNotification($socketData = [], $rooms = ['sms']) {
        $wsClient = new WSClient(new Version1X(config_item('wsClient')));

        if($wsClient) {
            $wsClient->initialize();
            $wsClient->emit('room', $rooms);
            $wsClient->emit('message', $socketData);
            $wsClient->close();

            return true;
        }

        return false;
    }
}
