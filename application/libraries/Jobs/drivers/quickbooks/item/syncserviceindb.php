<?php

class syncserviceindb extends CI_Driver implements JobsInterface
{
    private $settings;
    private $dataService;
    private $CI;
    private $action;
    private $route;
    private $itemId;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('qb_helper');
        $this->CI->load->model('mdl_services');
        $this->CI->load->model('mdl_bundles_services');

        $this->settings = getQbSettings();
        if (!empty($this->settings) && $this->settings && $this->settings['clientID'] && $this->settings['clientSecret'] && $this->settings['accessTokenKey'] && $this->settings['refreshTokenKey'] && $this->settings['QBORealmID'] && $this->settings['baseUrl'])
            $this->dataService = dataServiceConfigureFromArguments($this->settings['clientID'], $this->settings['clientSecret'], $this->settings['accessTokenKey'],
                $this->settings['refreshTokenKey'], $this->settings['QBORealmID'], $this->settings['baseUrl']);
        $this->route = 'pull';
    }

    public function getPayload($data = NULL)
    {
        if (!$data || empty($this->settings['accessToken']))
            return FALSE;
        return $data;
    }

    public function execute($job = NULL)
    {
        if(!$this->settings['stateFromQB'])
            die;
        if ($job) {
            $payload = unserialize($job->job_payload);
            $items = findByIdInQB($payload['module'], $payload['qbId'], $this->dataService);
            if (!$items) {
                $message = 'Error retrieving data from QuickBooks (qbId = ' . $payload['qbId'] . ')';
                createQBLog('item', 'get', 'pull', -1, $message);
                return FALSE;
            }
            $itemsArray[] = $items;
            $itemsToDB = getAllItemsToDB($itemsArray, $this->dataService);
            if(!$itemsToDB) {
                $message = 'Database validation error (qbId = ' . $payload['qbId'] . ')';
                createQBLog('item', 'get', 'pull', 0, $message);
                return FALSE;
            }
            $item = $itemsToDB[0];

            $checkInDB = $this->CI->mdl_services->find_all(['service_qb_id' => $item['service_qb_id']]);
            if (!$checkInDB) {
                $this->action = 'create';
                if(isset($item['bundle_records'])){
                    $bundleRecords = $item['bundle_records'];
                    unset($item['bundle_records']);
                }
                $this->itemId = $this->CI->mdl_services->insert($item);
            }
            else {
                $this->action = 'update';
                if(isset($item['bundle_records'])){
                    $bundleRecords = $item['bundle_records'];
                    unset($item['bundle_records']);
                }
                $this->itemId = $checkInDB[0]->service_id;
                $this->CI->mdl_services->update_by(['service_qb_id' => $item['service_qb_id']], $item);
            }

            if($items->Type == 'Group'){
                $this->action = 'update';
                if(empty($bundleRecords))
                    return FALSE;
                $this->CI->mdl_bundles_services->delete_by(['bundle_id' => $this->itemId]);
                $price = 0;
                foreach ($bundleRecords as $record){
                    $record['bundle_id'] = $this->itemId;
                    $price += $record['cost'];
                    unset($record['cost']);
                    $this->CI->mdl_bundles_services->insert($record);
                }
                $this->CI->mdl_services->update_by(['service_qb_id' => $item['service_qb_id']], ['cost' => $price]);
            }
            createQBLog('item', $this->action, $this->route, $this->itemId);
            deleteLogsInTmp();
            return TRUE;
        }
        return FALSE;
    }
}
