<?php

use application\modules\equipment\models\EquipmentCounter;
use application\modules\equipment\requests\EquipmentCounterCreateUpdateRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class EquipmentCounters extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
    }

    public function ajax_get_counters()
    {
        $page = request('page', 1);
        $sort = request('sort', ['counter_created_at', 'desc']);
        /** @var Builder $partQuery */
        $partQuery = EquipmentCounter::with([
            'user',
            'repair',
            'service_report.service' => function (BelongsTo $query) {
                $query->withoutGlobalScope('fields');
            }
        ]);
//        if (request('filter', false)) {
//            $partQuery->where('part_name', 'like', '%' . request('filter') . '%');
//        }
        if (request('where', false)) {
            $partQuery->where(request('where'));
        }
        /** @var LengthAwarePaginator $counters */
        $counters = $partQuery->orderBy(...$sort)
            ->orderBy('counter_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($counters->toArray());
    }

    public function ajax_get_counter()
    {
        $id = request('counter_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$counter = EquipmentCounter::find($id)) {
            return $this->errorResponse('Counter not found');
        }
        return $this->successResponse($counter->toArray());
    }

    public function ajax_create_counter()
    {
        try {
            /** @var EquipmentCounterCreateUpdateRequest $request */
            $request = app(EquipmentCounterCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $counter = EquipmentCounter::create($request->validated());
        return $this->successResponse($counter->toArray());
    }

    public function ajax_update_counter()
    {
        try {
            /** @var EquipmentCounterCreateUpdateRequest $request */
            $request = app(EquipmentCounterCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $counter = EquipmentCounter::find($request->get('counter_id'));

        $counter->fill($request->validated());
        $counter->save();
        return $this->successResponse($counter->toArray());
    }

    public function ajax_delete_counter()
    {
        if (!request('counter_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentCounter::destroy(request('counter_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}