<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

function generateAdditionalInfo($email)
{
    if (empty($email)) {
        return '';
    }

    $info = $infoStartLine = '<ul class="emails-stat-details-list">';

    if ($email['email_status'] === 'error') {
        $info .= '<li>' . _getInfoDateTime($email['email_updated_at']) . ' &mdash; ' . $email['email_error'] . '</li>';
    }
    else {
        if (!empty($email['email_logs'])) {
            foreach ($email['email_logs'] as $log) {
                $info .= '<li>' .  _getInfoDateTime($log['email_log_created_at']) . ' &mdash; ';
                if ($log['email_log_tracking_status'] === 'accepted') {
                    $info .= 'Sent';
                }
                elseif ($log['email_log_tracking_status'] === 'delivered') {
                    $info .= 'Delivered';
                }
                elseif ($log['email_log_tracking_status'] === 'opened') {
                    $info .= 'Opened';
                }
                elseif ($log['email_log_tracking_status'] === 'clicked') {
                    if (!empty($log['email_log_tracking_details'])) {
                        $details = json_decode($log['email_log_tracking_details']);

                        if (stripos($details->link, '/payments/estimate_signature/') !== false) {
                            $info .= 'Signature link opened';
                        }
                        elseif (stripos($details->link, '/payments/') !== false) {
                            $info .= 'Payment link opened';
                        }
                        elseif (!isset($details->link) || empty($details->link)) {
                            $info .= 'Unknown link opened';
                        }
                        else {
                            $info .= 'Link ' . $details->link . ' opened';
                        }
                    } else {
                        $info .= 'Unknown link opened';
                    }
                }
                elseif ($log['email_log_tracking_status'] === 'bounce') {
                    $toInfo = 'The email account that you tried to reach does not exist.';

                    if (!empty($log['email_log_tracking_details'])) {
                        $details = json_decode($log['email_log_tracking_details']);
                        if (isset($details->bouncedRecipients[0]->diagnosticCode)) {
                            $toInfo = $details->bouncedRecipients[0]->diagnosticCode;
                        }
                    }

                    $info .= $toInfo;
                }
                elseif ($log['email_log_tracking_status'] === 'rejected') {
                    $info .= 'Email was rejected';
                }
                elseif ($log['email_log_tracking_status'] === 'complained') {
                    $info .= 'Complained';
                }
                elseif ($log['email_log_tracking_status'] === 'unsubscribed') {
                    $info .= 'Email was unsubscribed';
                }

                $info .= '</li>';
            }
        }
    }

    if ($info === $infoStartLine) {
        $info .= '<li>No details found</li>';
    }

    $info .= '</ul>';

    return $info;
}

function _getInfoDateTime($date) {
    return '<span class="email-stat-row-date">' . getDateTimeWithDate($date, 'Y-m-d H:i:s', true) . '</span>';
}
