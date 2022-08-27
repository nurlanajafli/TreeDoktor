<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use application\modules\leads\models\Lead;
use \Illuminate\Support\Facades\DB;

class ClientsFiles extends MX_Controller {

    private $request;

    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->request = request();
    }

    public function getClientFiles()
    {
        $response = [];
        $client_id = (int) $this->request->input('client_id');
        $status = $this->request->input('status');
        $address = $this->request->input('address');

        if ($client_id) {
            $response['data'] = Lead::getLeadClientFiles($client_id, $status, $address);
            $response['status'] = $status;

            if (!empty($address)) {
                $clientFilesStatuses = Lead::CLIENT_FILES_STATUSES;

                foreach ($clientFilesStatuses as $key => $statusData) {
                    $response['count_statuses'][$key]['count'] = Lead::countLeadClientFiles($client_id, $statusData['name'], $address);
                }
            }
        }

        $this->response($response);
    }

    public function getClientLocations()
    {
        $response = [];
        $client_id = (int) $this->request->input('client_id');

        if ($client_id) {
            $response['data'] = Lead::select([
                DB::raw("CONCAT(lead_address, ', ', lead_city) as location"),
                'lead_address',
                'lead_city',
                'lead_state',
                'lead_zip',
                'lead_country',
                'lead_add_info',
                'latitude',
                'longitude'
            ])
                ->groupBy('lead_address')
                ->orderBy('lead_address')
                ->whereNotNull('lead_address')
                ->where('lead_address', '<>', '')
                ->whereClientId($client_id)
                ->get();
            $response['data']->each->setAppends([]);
        }

        $this->response($response);
    }
}
