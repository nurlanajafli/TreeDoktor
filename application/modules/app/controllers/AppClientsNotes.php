<?php

if (!defined('BASEPATH')) exit('No direct script access allowed.');

use application\modules\client_calls\models\ClientsCall;
use application\modules\clients\models\ClientNote;
use application\modules\clients\models\ClientsContact;
use application\modules\messaging\models\Messages;

class AppClientsNotes extends APP_Controller
{
    private $request;

    function __construct() {
        parent::__construct();

        $this->request = request();
    }

    /**
     * Get client notes
     *
     * @uses $_POST['client_id']
     * @uses $_POST['lead_id']
     * @uses $_POST['client_note_type'] - 0 (all) or 'info', 'attachment', 'system', 'email'
     * @uses $_POST['client_only']
     * @uses [$_POST['page']]
     */
    public function getNotes() {
        $response = [];

        $client_id = (int) $this->request->input('client_id');

        if ($client_id) {
            $lead_id = (int) $this->request->input('lead_id');
            $response['client_note_type'] = $client_note_type = $this->request->input('client_note_type') ?: 0;
            $response['client_only'] = $client_only = filter_var($this->request->input('client_only'), FILTER_VALIDATE_BOOLEAN);

            $response['client'] = [
                'client_id' => $client_id
            ];

            $response['client']['notes'] = ClientNote::getClientNotes(
                $client_id,
                $lead_id,
                $client_only,
                $client_note_type,
                $this->request->input('page')
            );

            if ($lead_id && !$client_only) {
                $response['lead'] = [
                    'lead_id' => $lead_id
                ];
            }

            $response['notes_files'] = get_client_notes_files('uploads/notes_files/' . $client_id . '/');

            // handle email log info
            if (sizeof($response['client']['notes']['data'])) {
                $this->load->helper('email_statistic');

                foreach ($response['client']['notes']['data'] as $key => $data) {
                    if (isset($data['emails'][0]) && sizeof($data['emails'][0])) {
                        $response['client']['notes']['data'][$key]['email'] = true;
                        $response['client']['notes']['data'][$key]['email_logs'] = generateAdditionalInfo($data['emails'][0]);
                        unset($response['client']['notes']['data'][$key]['emails']);
                    }
                }
            }
        }

        return $this->response([
            'status' => true,
            'notes' => $response
        ]);
    }

    /**
     * Get client SMS notes
     *
     * @uses $_POST['client_id']
     */
    public function getSmsNotes() {
        $response = [];

        if (config_item('messenger')) {
            $client_id = (int) $this->request->input('client_id');

            if ($client_id) {
                $response = $this->request->all();
                $response['limit'] = $this->config->item('per_page_notes');
                $response['client'] = [
                    'client_id' => $client_id
                ];

                $numbers = ClientsContact::getClientContactsCleanPhones($client_id);

                $response['notes'] = [];
                if ($numbers && sizeof($numbers)) {
                    $response['notes'] = Messages::getClientNotesSms($numbers);
                }
            }
        }

        return $this->response([
            'status' => true,
            'notes' => $response
        ]);
    }

    /**
     * Get client calls notes
     *
     * @uses $_POST['client_id']
     */
    public function getCallNotes() {
        $response = [];

        if (config_item('phone')) {
            $client_id = (int) $this->request->input('client_id');

            if ($client_id) {
                $response = $this->request->all();
                $response['limit'] = $this->config->item('per_page_notes');
                $response['client'] = [
                    'client_id' => $client_id
                ];

                $numbers = ClientsContact::getClientContactsCleanPhones($client_id);

                $response['notes'] = [];
                if ($numbers && count($numbers)) {
                    $response['notes'] = ClientsCall::getClientNotesCalls($numbers);
                }
            }
        }

        return $this->response([
            'status' => true,
            'notes' => $response
        ]);
    }
}