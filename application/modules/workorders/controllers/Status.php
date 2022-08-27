<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use application\modules\workorders\models\WorkorderStatus;
use application\modules\workorders\requests\status\SaveStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class Status extends MX_Controller
{
    function __construct()
    {

        parent::__construct();
        //Checking if user is logged in;
        if (!isUserLoggedIn() && isWorkorderAccessible()) {
            redirect('login');
        }

        if (is_cl_permission_none() && isWorkorderAccessible()) {
            redirect(base_url());
        }

        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('WO_STS') != 1) {
            show_404();
        }

        $this->_title = SITE_NAME;
        $this->load->model('mdl_workorders', 'mdl_workorders');
        $this->load->model('mdl_administration');
    }

    public function index()
    {
        $this->load->view('index_status', [
            'title' => "Status",
            'statuses' => WorkorderStatus::activeDescending()->priorityAscending()->get()->toArray()
        ]);
    }

    function ajax_save_status()
    {
        try {
            $request = app(SaveStatusRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $id = $this->input->post('wo_status_id');

        $WorkorderStatus = new WorkorderStatus();
        if($request->input('wo_status_id')!==null)
            $WorkorderStatus = WorkorderStatus::find($request->input('wo_status_id'));

        $WorkorderStatus->fill($request->all())->save();

        return $this->response(['status'=>'ok']);
    }

    function ajax_delete_status()
    {
        $id = $this->input->post('status_id');
        $status = $this->input->post('status');
        if ($id != '')
            $this->mdl_administration->update_status($id, array('wo_status_active' => $status));
        die(json_encode(array('status' => 'ok')));
    }

    function ajax_priority_statuses()
    {
        $data = $this->input->post('data');
        if (empty($data))
            die(json_encode(array('status' => 'error')));
        foreach ($data as $key => $val) {
            if ($val)
                $updateBatch[] = array('wo_status_id' => $val['id'], 'wo_status_priority' => $val['priority']);
        }
        if (empty($updateBatch))
            die(json_encode(array('status' => 'error')));
        if ($this->mdl_workorders->update_priority($updateBatch))
            die(json_encode(array('status' => 'ok')));
        die(json_encode(array('status' => 'error')));
    }
}