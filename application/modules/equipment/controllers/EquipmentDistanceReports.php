<?php

class EquipmentDistanceReports extends MX_Controller
{
//    function __construct()
//    {
//
//        parent::__construct();
//
//        if (!isUserLoggedIn()) {
//            redirect('login');
//        }
//        $this->_title = SITE_NAME;
//    }
//
//    public function index()
//    {
//        $data['title'] = $this->_title . " - Equipment Groups";
//        //load view.
//        $this->load->view('equipments/groups', $data);
//    }// End Index
//
//    public function ajax_get_parts()
//    {
//        $page = request('page', 1);
//        $sort = request('sort', ['eq_created_at', 'desc']);
//        /** @var \Illuminate\Database\Query\Builder $partQuery */
//        $partQuery = EquipmentPart::with(['user']);
//        if (request('filter', false)) {
//            $partQuery->where('part_name', 'like', '%' . request('filter') . '%');
//        }
//        /** @var \Illuminate\Pagination\LengthAwarePaginator $parts */
//        $parts = $partQuery->orderBy(...$sort)
//            ->orderBy('part_id', 'desc')
//            ->paginate(10, ['*'], 'page', $page);
//        return $this->successResponse($parts->toArray());
//    }
//
//    public function ajax_get_part()
//    {
//        $id = request('id', false);
//        if (!$id) {
//            return $this->errorResponse('Wrong Input parameters');
//        }
//        if (!$part = EquipmentPart::find($id)) {
//            return $this->errorResponse('Part not found');
//        }
//        return $this->successResponse($part->toArray());
//    }
//
//    public function ajax_create_part()
//    {
//        if (!request('part_name', false)) {
//            return $this->errorResponse(null, ['part_name' => 'Part Name is required']);
//        }
//        $part = EquipmentPart::create(request()->all());
//        return $this->successResponse($part->toArray());
//    }
//
//    public function ajax_update_part()
//    {
//        if (!request('part_id', false)) {
//            return $this->errorResponse('Update error');
//        }
//        if (!$part = EquipmentPart::find(request('part_id'))) {
//            return $this->errorResponse('Part not found');
//        }
//
//        $part->fill(request()->all());
//        //$dirty = $group->getDirty();
//        $part->save();
//        return $this->successResponse($part->toArray());
//    }
//
//    public function ajax_delete_part()
//    {
//        if (!request('part_id', false)) {
//            return $this->errorResponse('Delete error');
//        }
//        if (EquipmentPart::destroy(request('part_id')) === 0) {
//            return $this->errorResponse('Delete error');
//        }
//        return $this->successResponse();
//    }
}