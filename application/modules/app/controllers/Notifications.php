<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends APP_Controller
{

	function __construct() {
		parent::__construct();
		$this->load->model('mdl_notifications');
	}

	function index() {
        $result = $this->mdl_notifications->order_by('notification_created_at', 'DESC')
            ->limit(200)
            ->get_many_by([
                'notification_user_id' => $this->user->id,
                'notification_deleted_at IS NULL' => NULL,
            ]);

        $data = [];
        if($result)
            $data = array_map(function (&$row) {
                $row->notification_params = json_decode($row->notification_params);
                return $row;
            }, $result);

        return $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
	}

	function delete() {
        $id = $this->input->post('id');
        $notification = $this->mdl_notifications->get_by([
            'notification_id' => $id,
            'notification_user_id' => $this->user->id,
            'notification_deleted_at IS NULL' => NULL,
        ]);

        if(!$notification)
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect ID'
            ],400);

        $this->mdl_notifications->update($id, [
            'notification_deleted_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response([
            'status' => TRUE,
            'data' => []
        ],200);
    }

    function read() {
        $id = $this->input->post('id');

        $where = [
            'notification_user_id' => $this->user->id,
            'notification_deleted_at IS NULL' => NULL,
            'notification_read' => 0,
        ];

        $ids = [];

        if(is_array($id))
            array_map(function ($val) use (&$ids) {
                if(intval($val))
                    $ids[] = $val;
            }, $id);
        elseif (intval($id) && intval($id) == $id)
            $ids[] = intval($id);

        if((!$ids || empty($ids)) && !$this->input->post('all'))
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Input'
            ],400);

        if($ids && !empty($ids))
            $where['notification_id'] = $ids;

        $notifications = $this->mdl_notifications->get_many_by($where);

        if(!$notifications)
            return $this->response([
                'status' => FALSE,
                'message' => 'No Records Found'
            ],400);

        $updateIds = array_map(function ($row) {
            return $row->notification_id;
        }, $notifications);

        $this->mdl_notifications->update_many($updateIds, [
            'notification_read' => 1
        ]);

        return $this->response([
            'status' => TRUE,
            'data' => $updateIds
        ],200);
    }
}
