<?php

use application\modules\equipment\models\EquipmentGroup;

class EquipmentGroups extends MX_Controller
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
        $data['title'] = $this->_title . " - Equipment Groups";
        //load view.
        $this->load->view('equipment/groups', $data);
    }// End Index

    public function ajax_get_groups()
    {
        $page = request('page', 1);
        $sort = request('sort', ['group_created_at', 'desc']);
        $groupsQuery = EquipmentGroup::query();
        if (!empty(request('filter', ""))) {
            $groupsQuery->where('group_name', 'like', '%' . request('filter') . '%');
        }
        $groups = $groupsQuery->orderBy(...$sort)
            ->orderBy('group_id', 'desc')
            //->orderBy(['group_id','desc'])
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($groups->toArray());
    }

    public function ajax_get_group()
    {
        $id = request('group_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$group = EquipmentGroup::find($id)) {
            return $this->errorResponse('Group not found');
        }
        return $this->successResponse($group->toArray());
    }

    public function ajax_create_group()
    {
        if (!request('group_name', false)) {
            return $this->errorResponse(null, ['group_name' => 'Group Name is required']);
        }
        if (EquipmentGroup::whereGroupName(request('group_name'))->exists()) {
            return $this->errorResponse(null, ['group_name' => 'Group Name must be unique']);
        }
        if (!request('group_prefix',
                false) && EquipmentGroup::whereGroupPrefix(request('group_prefix'))->where('group_prefix', '!=',
                '')->exists()) {
            return $this->errorResponse(null, ['group_prefix' => 'Group Prefix must be unique']);
        }
        $group = EquipmentGroup::create(request()->all());
        return $this->successResponse($group->toArray());
    }

    public function ajax_update_group()
    {
        if (!request('group_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!$group = EquipmentGroup::find(request('group_id'))) {
            return $this->errorResponse('Group not found');
        }
        if (EquipmentGroup::whereKeyNot(request('group_id'))->whereGroupName(request('group_name'))->exists()) {
            return $this->errorResponse(null, ['group_name' => 'Group Name must be unique']);
        }
        if (!request('group_prefix', false)
            && EquipmentGroup::whereKeyNot(request('group_id'))->whereGroupPrefix(request('group_prefix'))->where('group_prefix',
                '!=', '')->exists()
        ) {
            return $this->errorResponse(null, ['group_prefix' => 'Group Prefix must be unique']);
        }
        $group->fill(request()->all());
        //$dirty = $group->getDirty();
        $group->save();
        return $this->successResponse($group->toArray());
    }

    public function ajax_delete_group()
    {
        if (!request('group_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (!$group = EquipmentGroup::withCount(['equipment'])->find(request('group_id'))) {
            return $this->errorResponse('Delete error');
        }
        if ($group->equipment_count != 0) {
            return $this->errorResponse('Group is not empty!');
        }
        if (!$group->delete()) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}