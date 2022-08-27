<?php

use application\modules\estimates\models\EstimatesService;

function allow_workorder_status($workorder_status, $services)
{
	if($services) {
		$notFinished = false;
		foreach ($services as $key => $service)
		{
			if(!in_array((int)$service->service_status, EstimatesService::FINISHED_SERVICE_STATUSES)) {
                $notFinished = true;
            }
		}

		if($workorder_status == 0 && $notFinished) {
            return ['status'=>false, 'message'=>'To finish the Workorder you must have all Workorder service statuses only "Completed" or "Declined".'];
        }
	}
	
	return ['status'=>true, 'message'=>false];
}
