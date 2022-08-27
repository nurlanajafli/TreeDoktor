<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\EstimatesServicesStatus;

class Appestimatesservices extends APP_Controller
{

    /**
     * Appworkorders constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Update status
     */
    public function update_status()
    {
        $estimates_services_id = $this->input->post('estimates_services_id')??null;
        $estimates_services_status_id = $this->input->post('estimates_services_status_id');
        $estimatesService = EstimatesService::find($estimates_services_id);

        if ($estimates_services_id == null || is_null($estimatesService)) {
            return $this->response(['status' => false, 'message' => 'Wrong ID provided'], 400);
        }

        $estimatesServicesStatus = EstimatesServicesStatus::find($estimates_services_status_id);
        if ($estimates_services_status_id == null || is_null($estimatesServicesStatus)) {
            return $this->response(['status' => false, 'message' => 'Wrong Status ID provided'], 400);
        }
        $estimatesService->update(['service_status' => $estimates_services_status_id]);

        return $this->response([
            'status' => 'ok',
            'message' => 'success',
            'exists_unfinished' => EstimatesService::doesnthave('bundle_service')
                ->where('estimate_id', $estimatesService->estimate_id)
                ->whereNotIn('service_status', EstimatesService::FINISHED_SERVICE_STATUSES)
                ->count() ? true : false
        ], 200);
    }
}
