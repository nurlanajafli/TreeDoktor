<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Schedule extends MX_Controller
{
    function __construct() {
        parent::__construct();
	}

	public function index() {
        $data['title'] = 'Online Calendar';
        $this->load->view('account/schedule/index', $data);
    }

    public function data() {
        $this->load->model('mdl_schedule');
        $this->load->model('mdl_clients');
        $phone = numberFrom($this->input->get('phone'));
        $estimateNo = $this->input->get('no');
        if(!$phone || !$estimateNo) {
            return $this->response([
                'status' => false,
                'message' => 'All fields is required'
            ], 400);
        }

        $client = $this->mdl_clients->get_client_by_fields([
            'cc_phone_clean' => substr($phone, 0, config_item('phone_clean_length')),
            'estimate_no' => $estimateNo . '-E'
        ]);

        if(!$client) {
            return $this->response([
                'status' => false,
                'message' => 'Not found'
            ], 400);
        }

        $events = $this->mdl_schedule->get_events_dashboard([
            'clients.client_id' => $client->client_id
        ]);

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'id' => $event['id'],
                'start_date' => date('Y-m-d H:i:s', $event['event_start']),
                'end_date' => date('Y-m-d H:i:s', $event['event_end']),
                'color' => '#a8f3aa',
                'text' => $event['lead_address'],
            ];
        }

        return $this->response([
            'status' => true,
            'count' => !empty($events) && is_array($events) ? count($events) : 0,
            'data' => $data
        ]);
    }
}
