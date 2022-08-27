<?php
use application\modules\classes\models\QBClass;

class ServicesActions
{
    protected $CI;
    protected $service;
    protected $serviceId;

    function __construct($serviceId = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_services');
        /*
        $this->CI->load->model('mdl_clients');
        $this->CI->load->model('mdl_leads');
        $this->CI->load->model('mdl_est_status');
        $this->CI->load->model('mdl_estimates');
        $this->CI->load->model('mdl_estimates_orm');
        */
        if ($serviceId) {
            $this->serviceId = $serviceId;
            $this->service = $this->getServiceById($serviceId);
        }
    }

    function setServiceId($serviceId)
    {
        if (!$serviceId)
            return FALSE;
        $this->service = $this->getServiceById($serviceId);
        if (!$this->service)
            return FALSE;
        $this->serviceId = $serviceId;
        return TRUE;
    }

    function setQBid($serviceQBid)
    {
        if (!$serviceQBid || !$this->service)
            return FALSE;
        $this->service->service_qb_id = $serviceQBid;
    }

    function getService()
    {
        if ($this->service)
            return $this->service;
        return FALSE;
    }

    function create()
    {

    }

    function update()
    {
        if (!$this->service)
            return FALSE;
        $result = $this->CI->mdl_services->update($this->getId(), (array)$this->service);
        if (!$result)
            return FALSE;
        return TRUE;
    }

    function setServiceByName($name)
    {
        $service = $this->CI->mdl_services->find_all(['service_name' => $name]);
        if ($service) {
            $this->service = $service[0];
            return TRUE;
        }
        return FALSE;
    }

    function getQBid()
    {
        if (!$this->service)
            return FALSE;
        return $this->service->service_qb_id;
    }

    function getId()
    {
        if (!$this->service)
            return FALSE;
        return $this->service->service_id;
    }

    public function getServiceAttachment($attachments){
        $new_att = [];
        if(isset($attachments)) {
            $i = 0;
            foreach (json_decode($attachments) as $attachment) {

                $transport = new stdClass();
                $tool = [];

                if (isset($attachment->vehicle_id)) {
                    $transport->vehicles['tool_id'] = $attachment->vehicle_id;
                }
                if (isset($attachment->vehicle_option)) {
                    $transport->vehicles['option'] = $attachment->vehicle_option;
                }

                if (isset($attachment->trailer_id)) {
                    $transport->trailers['tool_id'] = $attachment->trailer_id;
                }
                if (isset($attachment->trailer_option)) {
                    $transport->trailers['option'] = $attachment->trailer_option;
                }
                if (isset($attachment->tool_id) && isset($attachment->tools_option)) {
                    foreach ($attachment->tool_id as $key => $tool_id) {
                        $tool[$tool_id] = $attachment->tools_option[$key];
                    }
                }
                $new_att[$i] = ['transport' => $transport, 'tool' => $tool];
                $i++;
            }
        }
        return $new_att;
    }

    public function addServiceAttachmentToServices(array $services): array{
        if(!empty($services)){
            foreach ($services as $service){
                $service->service_attachments = $this->getServiceAttachment($service->service_attachments);
            }
        }
        return $services;
    }

    public function addRecordsInBundle(object $bundle): object{
        $result = $this->CI->mdl_services->get_records_included_in_bundle($bundle->service_id);
        if($result){
            foreach ($result as $record) {
                $record->service_attachments = $this->getServiceAttachment($record->service_attachments);
                $record->non_taxable = 0;
                unset($record->service_qb_id);
                if(!empty($record->service_class_id)){
                    $class = QBClass::where('class_id', $record->service_class_id)->first();
                    if(!empty($class))
                        $record->service_class_name = $class->class_name;
                }
            }
        }
        $bundle->bundle_records = json_encode($result, true);
        return $bundle;
    }

    private function getServiceById($serviceId)
    {
        $service = $this->CI->mdl_services->get($serviceId);
        if ($service) {
            return $service;
        }
        return FALSE;
    }
}
