<?php

use application\modules\equipment\models\Equipment as EquipmentModel;
use application\modules\equipment\models\EquipmentGroup;
use application\modules\equipment\models\EquipmentRepairStatus;
use application\modules\equipment\requests\EquipmentCreateUpdateRequest;
use application\modules\equipment\requests\EquipmentIdRequest;
use application\modules\equipment\requests\EquipmentPhotoRequest;
use application\modules\equipment\requests\EquipmentRequest;
use application\modules\equipment\requests\EquipmentSaleRequest;
use application\modules\equipment\requests\EquipmentUnsoldRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Equipment extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************
//*************											Equipments Controller
//*************
//*************
//*******************************************************************************************************************	

    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
    }

    public function index($group_id = false)
    {
        $data['title'] = $this->_title . " - Equipment";
        $data['group'] = false;
        $data['groups'] = EquipmentGroup::all();
        if ($group_id) {
            /** @var EquipmentGroup $group */
            $data['group'] = $data['groups']
                ->where('group_id', '=', $group_id)
                ->first();
            if (!$data['group']) {
                redirect('/equipment');
            }
            $data['title'] .= " in " . $data['group']->group_name . 'Group';
        }

        $this->load->view('equipment/equipment', $data);
    }

    public function map($date = null, $code = null)
    {
        $data['title'] = $this->_title . ' - Equipment Map';
        $data['items'] = EquipmentModel::query()
            ->whereNotNull('eq_gps_id')
            ->where('eq_gps_id', '!=', '')
            ->get();

        if ($date && $code) {
            $this->load->model('mdl_tracker');
            set_time_limit(0);
            $data['truck'] = $data['items']->where('eq_code', $code)->first();
            $trackerDB = $this->mdl_tracker->get_by(array('eq_td_code' => $code, 'eq_td_date' => $date));
            if (!$data['truck']) {
                show_404();
            }

            $this->load->driver('gps');
            if (!$this->gps->enabled()) {
                $parkingsResponse = json_encode([]);
                $routeResponse = json_encode([]);
                $distanceResponse = json_encode([]);
            }
            if (!empty($trackerDB)) {
                $info = json_decode($trackerDB->eq_td_data);
                if (!empty($info->parkData)) {
                    $parkingsResponse = json_encode($info->parkData);
                } elseif ($this->gps->enabled()) {
                    $parkingsResponse = $this->gps->parkings($data['truck']->eq_gps_id, $date);
                }
                if (!empty($info->routeData)) {
                    $routeResponse = json_encode($info->routeData);
                } elseif ($this->gps->enabled()) {
                    $routeResponse = $this->gps->route($data['truck']->eq_gps_id, $date);
                }

                if (!empty($info->distanceData)) {
                    $distanceResponse = json_encode($info->distanceData);
                } elseif ($this->gps->enabled()) {
                    $distanceResponse = $this->gps->distance($data['truck']->eq_gps_id, $date);
                }
            } elseif ($this->gps->enabled()) {
                $routeResponse = $this->gps->route($data['truck']->eq_gps_id, $date);
                $parkingsResponse = $this->gps->parkings($data['truck']->eq_gps_id, $date);
                $distanceResponse = $this->gps->distance($data['truck']->eq_gps_id, $date);

                $insert['parkData'] = json_decode($parkingsResponse);
                $insert['routeData'] = json_decode($routeResponse);
                $insert['distanceData'] = json_decode($distanceResponse);
                if (isset($data['truck']->eq_code) && $data['truck']->eq_code && $date == date('Y-m-d')) {
                    $this->mdl_tracker->delete_by(array(
                        'eq_td_code' => $data['truck']->eq_code,
                        'eq_td_date' => $date
                    ));
                }
                if (!empty($insert['parkData']->data) || !empty($insert['routeData']) || !empty($insert['distanceData']->data)) {
                    $this->mdl_tracker->insert(array(
                        'eq_td_code' => $code,
                        'eq_td_date' => $date,
                        'eq_td_data' => json_encode($insert)
                    ));
                }
            }
            $data['date'] = $date;
            $data['code'] = $code;
            $data['route'] = $routeResponse == '.' ? "[]" : $routeResponse;
            $data['parkings'] = $parkingsResponse;
            $data['distance'] = !empty($distanceResponse) ? $distanceResponse : [];
            $this->load->view('check_equipment', $data);
        } else {
            $this->load->library('Googlemaps');

            $config['center'] = config_item('map_center');
            $config['zoom'] = '9';
            $this->googlemaps->initialize($config);
            $data['map'] = $this->googlemaps->create_map();

            //$trackingItems = $this->mdl_equipments->get_items(array('item_tracker_name <>' => 'NULL', 'item_tracker_name <>' => ''));
            $data['tracks'] = [];
            foreach ($data['items'] as $item) {
                $data['tracks'][] = array(
                    'item_code' => $item->eq_code,
                    'item_name' => $item->eq_name,
                    'item_tracker_name' => $item->eq_gps_id
                );
            }
            $this->load->view('equipment/index_map', $data);
        }
    }

    public function sold()
    {
        $data['title'] = $this->_title . " - Sold Equipment";
        $data['group'] = false;
        $data['groups'] = EquipmentGroup::all();

        $this->load->view('equipment/sold', $data);
    }

    public function profile($eq_id = false)
    {
        $data['title'] = $this->_title . " - Equipment Details";
        $data['eq'] = EquipmentModel::with(['group', 'seller'])->withoutGlobalScope('sold')->withCount([
            'services' => function (Builder $query) {
                $query->withoutGlobalScope('fields');
            },
            'repairs'
        ])->find($eq_id);
        if (!$data['eq']) {
            show_404();
        }
        $data['repairStatuses'] = EquipmentRepairStatus::all();

        $this->load->view('equipment/profile', $data);
    }

    public function ajax_get_equipment()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $page = $request->get('page', 1);
        $sort = $request->get('sort', ['eq_created_at', 'desc']);
        /** @var \Illuminate\Database\Query\Builder $eqQuery */
        $eqQuery = EquipmentModel::query()->with(['group']);
        if ($request->has('where')) {
            $eqQuery->where($request->get('where'));
        }
        if ($request->has('filter')) {
            $eqQuery->where($request->get('filter'));
        }
        if ($request->has('query')) {
            $eqQuery->where(function ($query) use ($request) {
                $query->where('eq_code', 'like', '%' . $request->get('query') . '%');
                $query->orWhere('eq_name', 'like', '%' . $request->get('query') . '%');
            });
        }
        /** @var LengthAwarePaginator $equipment */
        $eqQuery->orderBy(...$sort)
            ->orderBy('eq_id', 'desc');
        $equipment = $eqQuery->paginate(30, ['*'], 'page', $page);
        if ($equipment->currentPage() > $equipment->lastPage()) {
            $equipment = $eqQuery->paginate(30, ['*'], 'page', $equipment->lastPage());
        }
        return $this->successResponse($equipment->toArray());
    }

    public function ajax_get_sold_equipment()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $page = $request->get('page', 1);
        $sort = $request->get('sort', ['eq_created_at', 'desc']);
        /** @var \Illuminate\Database\Query\Builder $eqQuery */
        $eqQuery = EquipmentModel::query()
            ->withoutGlobalScope('sold')
            ->with(['group', 'seller'])
            ->whereNotNull('eq_sold_at');
        if ($request->has('where')) {
            $eqQuery->where($request->get('where'));
        }
        if ($request->has('filter')) {
            $eqQuery->where($request->get('filter'));
        }
        if ($request->has('query')) {
            $eqQuery->where(function ($query) use ($request) {
                $query->where('eq_code', 'like', '%' . $request->get('query') . '%');
                $query->orWhere('eq_name', 'like', '%' . $request->get('query') . '%');
            });
        }
        /** @var LengthAwarePaginator $equipment */
        $eqQuery->orderBy(...$sort)
            ->orderBy('eq_id', 'desc');
        $equipment = $eqQuery->paginate(30, ['*'], 'page', $page);
        if ($equipment->currentPage() > $equipment->lastPage()) {
            $equipment = $eqQuery->paginate(30, ['*'], 'page', $equipment->lastPage());
        }
        return $this->successResponse($equipment->toArray());
    }

    public function ajax_get_item()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentIdRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        if (!$eq = EquipmentModel::with(['group', 'seller'])->withoutGlobalScope('sold')->withCount([
            'services' => function (Builder $query) {
                $query->withoutGlobalScope('fields');
            },
            'repairs'
        ])->find($request->get('eq_id'))) {
            return $this->errorResponse('Equipment not found');
        }
        return $this->successResponse($eq->toArray());
    }

    public function ajax_create_item()
    {
        try {
            /** @var EquipmentCreateUpdateRequest $request */
            $request = app(EquipmentCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $eq = EquipmentModel::create($request->validated());

        if ($request->hasFile('file')) {
            $eq->loadPhoto($request->file('file'));
            $eq->save();
        }
        $eq->refresh();
        return $this->successResponse($eq->toArray());
    }

    public function ajax_update_item()
    {
        try {
            /** @var EquipmentCreateUpdateRequest $request */
            $request = app(EquipmentCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        try {
            $eq = EquipmentModel::findOrFail($request->get('eq_id'));
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Equipment not found');
        }

        $eq->fill($request->validated());
        if ($request->hasFile('file')) {
            $eq->loadPhoto($request->file('file'));
        }
        $eq->save();
        return $this->successResponse($eq->toArray());
    }


    public function ajax_delete_item()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentIdRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        if (EquipmentModel::destroy($request->get('eq_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    public function ajax_set_schedule()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentIdRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::find($request->get('eq_id'));

        $eq->eq_schedule = $eq->eq_schedule ? false : true;
        if (!$eq->eq_schedule) {
            $eq->eq_schedule_tool = false;
        }
        $eq->save();
        return $this->successResponse(['checked' => $eq->eq_schedule]);
    }

    public function ajax_set_schedule_tool()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentIdRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::find($request->get('eq_id'));


        if ($eq->eq_schedule) {
            $eq->eq_schedule_tool = $eq->eq_schedule_tool ? false : true;
            $eq->save();
        }
        return $this->successResponse(['checked' => $eq->eq_schedule_tool]);
    }

    public function ajax_set_repair()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentIdRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::find($request->get('eq_id'));

        $eq->eq_repair = $eq->eq_repair ? false : true;
        $eq->save();
        return $this->successResponse(['checked' => $eq->eq_repair]);
    }

    public function ajax_update_thumb()
    {
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentPhotoRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::find($request->get('eq_id'));
        $eq->loadPhoto($request->file('thumb'));

        $eq->save();
        return $this->successResponse(['thumb' => $eq->eq_photo_url]);
    }

    public function ajax_sale_item()
    {
        try {
            /** @var EquipmentSaleRequest $request */
            $request = app(EquipmentSaleRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::withoutGlobalScope('sold')->find($request->get('eq_id'));

        $eq->fill([
            'eq_code' => $request->get('eq_code'),
            'eq_sold_at' => Carbon::now(),
            'eq_sold_cost' => $request->get('eq_sold_cost'),
            'eq_sold_code' => $eq->eq_code,
            'seller_id' => $request->user()->id,
        ]);
        $eq->save();
        $eq->services()->delete();
        return $this->successResponse($eq->toArray());
    }

    public function ajax_unsold_item()
    {
        try {
            /** @var EquipmentUnsoldRequest $request */
            $request = app(EquipmentUnsoldRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $eq = EquipmentModel::withoutGlobalScope('sold')->find($request->get('eq_id'));

        $eq->fill([
            'group_id' => $request->get('group_id'),
            'eq_code' => $request->get('eq_code'),
            'eq_sold_at' => null,
            'eq_sold_cost' => null,
            'eq_sold_code' => null,
            'seller_id' => null
        ]);
        $eq->save();
        return $this->successResponse($eq->toArray());
    }
}

?>
