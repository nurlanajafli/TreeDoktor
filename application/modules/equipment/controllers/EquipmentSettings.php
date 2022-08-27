<?php

use application\modules\equipment\models\EquipmentRepairStatus;
use application\modules\equipment\models\EquipmentRepairType;
use application\modules\equipment\models\EquipmentServiceType;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class EquipmentSettings extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
    }

    public function index()
    {
        $data['title'] = $this->_title . " - Equipment Settings";
        //load view.
        $this->load->view('equipment/settings', $data);
    }// End Index

    /** SERVICE TYPES */

    public function ajax_get_service_types()
    {
        $page = request('page', 1);
        $sort = request('sort', ['service_type_created_at', 'desc']);
        /** @var Builder $query */
        $query = EquipmentServiceType::query();
        if (request('where', false)) {
            $query->where(request('where'));
        }
        if (request('filter', false)) {
            $query->where(request('filter'));
        }
        if (request('query', false)) {
            $query->where('service_type_name', 'like', '%' . request('query') . '%');
        }
        /** @var LengthAwarePaginator $types */
        $types = $query->orderBy(...$sort)
            ->orderBy('service_type_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($types->toArray());
    }

    public function ajax_get_service_type()
    {
        $id = request('service_type_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$type = EquipmentServiceType::find($id)) {
            return $this->errorResponse('Service Type not found');
        }
        return $this->successResponse($type->toArray());
    }

    public function ajax_create_service_type()
    {
        if (!request('service_type_name', false) || trim(request('service_type_name')) === "") {
            return $this->errorResponse(null, ['service_type_name' => 'Name is required']);
        }
        $serviceType = EquipmentServiceType::create(request()->all());
        return $this->successResponse($serviceType->toArray());
    }

    public function ajax_update_service_type()
    {
        if (!request('service_type_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!$serviceType = EquipmentServiceType::find(request('service_type_id'))) {
            return $this->errorResponse('Service Type not found');
        }

        $serviceType->fill(request()->all());
        $serviceType->save();
        return $this->successResponse($serviceType->toArray());
    }

    public function ajax_delete_service_type()
    {
        if (!request('service_type_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentServiceType::destroy(request('service_type_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    /** REPAIR STATUSES */

    public function ajax_get_repair_statuses()
    {
        $page = request('page', 1);
        $sort = request('sort', ['repair_status_id', 'desc']);
        /** @var Builder $query */
        $query = EquipmentRepairStatus::query();
        if (request('where', false)) {
            $query->where(request('where'));
        }
        if (request('filter', false)) {
            $query->where(request('filter'));
        }
        if (request('query', false)) {
            $query->where('repair_status_name', 'like', '%' . request('query') . '%');
        }
        /** @var LengthAwarePaginator $types */
        $statuses = $query->orderBy(...$sort)
            ->orderBy('repair_status_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($statuses->toArray());
    }

    public function ajax_get_repair_status()
    {
        $id = request('repair_status_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$status = EquipmentRepairStatus::find($id)) {
            return $this->errorResponse('Repair Status not found');
        }
        return $this->successResponse($status->toArray());
    }

    public function ajax_create_repair_status()
    {
        if (!request('repair_status_name', false) || trim(request('repair_status_name')) === "") {
            return $this->errorResponse(null, ['repair_status_name' => 'Name is required']);
        }
        $status = EquipmentRepairStatus::create(request()->all());
        return $this->successResponse($status->toArray());
    }

    public function ajax_update_repair_status()
    {
        if (!request('repair_status_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!request('repair_status_name', false)) {
            return $this->errorResponse(null, ['repair_status_name' => 'Name is required']);
        }
        if (!$status = EquipmentRepairStatus::find(request('repair_status_id'))) {
            return $this->errorResponse('Repair Status not found');
        }

        $status->fill(request()->all());
        $status->save();
        return $this->successResponse($status->toArray());
    }

    public function ajax_delete_repair_status()
    {
        if (!request('repair_status_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentRepairStatus::destroy(request('repair_status_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    /** REPAIR TYPES */

    public function ajax_get_repair_types()
    {
        $page = request('page', 1);
        $sort = request('sort', ['repair_type_id', 'desc']);
        /** @var Builder $query */
        $query = EquipmentRepairType::query();
        if (request('where', false)) {
            $query->where(request('where'));
        }
        if (request('filter', false)) {
            $query->where(request('filter'));
        }
        if (request('query', false)) {
            $query->where('repair_type_name', 'like', '%' . request('query') . '%');
        }
        /** @var LengthAwarePaginator $types */
        $types = $query->orderBy(...$sort)
            ->orderBy('repair_type_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($types->toArray());
    }

    public function ajax_get_repair_type()
    {
        $id = request('repair_type_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$type = EquipmentRepairType::find($id)) {
            return $this->errorResponse('Repair Type not found');
        }
        return $this->successResponse($type->toArray());
    }

    public function ajax_create_repair_type()
    {
        if (!request('repair_type_name', false) || trim(request('repair_type_name')) === "") {
            return $this->errorResponse(null, ['repair_type_name' => 'Name is required']);
        }
        $type = EquipmentRepairType::create(request()->all());
        return $this->successResponse($type->toArray());
    }

    public function ajax_update_repair_type()
    {
        if (!request('repair_type_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!request('repair_type_name', false)) {
            return $this->errorResponse(null, ['repair_type_name' => 'Name is required']);
        }
        if (!$type = EquipmentRepairType::find(request('repair_type_id'))) {
            return $this->errorResponse('Repair Type not found');
        }

        $type->fill(request()->all());
        $type->save();
        return $this->successResponse($type->toArray());
    }

    public function ajax_delete_repair_type()
    {
        if (!request('repair_type_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentRepairType::destroy(request('repair_type_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}