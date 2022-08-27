<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\webhook\models\EmailLogs as EmailLogsModel;
use application\modules\emails\models\Email as EmailModel;

/**
 * Emails Controller
 * Email tracking
 */
class EmailLogs extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    function index()
    {
        $payload = file_get_contents('php://input');

        if (empty($payload)) {
            exit('No payload.');
        }

        $result = $this->email->parseCallbackMessage($payload);

        if (isset($result['messageId'])) {
            $email = EmailModel::getEmailByMessageId($result['messageId']);

            if ($email) {
                $error = null;

                $updated = EmailModel::updateEmailStatus($email, $result['status']);

                if (isset($updated['error'])) {
                    $error = json_encode([
                        'state' => 'Email status not updated',
                        'error' => $updated['error']
                    ]);
                }

                $emailLogData = [
                    'email_log_email_id' => $email->email_id,
                    'email_log_message_id' => $result['messageId'],
                    'email_log_tracking_id' => $result['trackingId'],
                    'email_log_tracking_status' => $result['status'],
                    'email_log_tracking_details' => $result['details'],
                    'email_log_error' => $error,
                    'email_log_provider' => $result['driver'],
                    'email_log_created_at' => getNowDateTime()
                ];

                $emailLog = EmailLogsModel::createEmailLog($emailLogData);

                if (isset($emailLog['error'])) {
                    exit(json_encode(['error' => $emailLog['error']]));
                }
            } else {
                exit('No such email.');
            }
        } else {
            exit('No messageId.');
        }

        exit();
    }
}
    