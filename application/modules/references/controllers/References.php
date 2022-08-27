<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\references\models\Reference;

class References extends MX_Controller
{

    /**
     * References constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (!isUserLoggedIn()) {
            redirect('login');
        }
        if ($this->session->userdata('CL') === '0') {
            redirect('dashboard');
        }
        $this->_title = SITE_NAME;
    }

    /**
     * list action
     */
    public function list()
    {
        $data['references'] = Reference::withoutAlwaysHidden()->orderBy(Reference::ATTR_DELETED_AT)->orderBy(Reference::ATTR_WEIGHT)->get();
        return $this->response(['html'=>$this->load->view('list', $data, true)], 200);
    }


    /**
     * @param integer $id
     */
    public function get_edit_data(int $id)
    {
        if ($this->input->is_ajax_request()) {
            $referenceModel = Reference::find($id);
            if (is_null($referenceModel)) {
                die(json_encode(['status' => 'error', 'message' => 'Reference not found']));
            }
            if ($this->input->server('REQUEST_METHOD') === 'GET') {
                die(json_encode([
                    'render_form' => 1,
                    'reference' => $referenceModel->toArray()
                ]));
            }
        }
        redirect('dashboard');
    }

    /**
     * save action
     */
    public function save()
    {
        if (!$this->input->is_ajax_request())
            return show_404();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $form_id = strip_tags($this->input->post('id'));
            $form_name = strip_tags($this->input->post('name'));

            $referenceModel = !empty($form_id) ? Reference::find($form_id) : new Reference();
            if($form_name){
                $referenceModel->setAttribute(Reference::ATTR_NAME, $form_name);
                $referenceModel->setAttribute(Reference::ATTR_SLUG, str_replace(' ', '_', strtolower($form_name)));
            }

            $referenceModel->setAttribute(Reference::ATTR_WEIGHT, Reference::max(Reference::ATTR_WEIGHT));

            $result = $referenceModel->save();
            die(json_encode([
                'success' => 1,
                'data' => $result,
                'message' => 'Saved'
            ]));
        }

    }

    /**
     * save positions
     */
    public function save_positions()
    {
        if (!$this->input->is_ajax_request())
            return show_404();

        $request = request();
        $references_list = $request->input('list');
        if(!$references_list || !count($references_list))
            return $this->response([], 400);

        foreach ($references_list as $key => $value){
            Reference::where('id', $value)->update([Reference::ATTR_WEIGHT=>$key]);
        }

        return $this->response([
            'success' => 1,
            'data' => [],
            'message' => 'Saved'
        ], 200);
    }

    /**
     * @param int $id
     */
    public function restore(int $id)
    {
        if ($this->input->is_ajax_request()) {
            $referenceModel = Reference::withTrashed()->find($id);
            if (is_null($referenceModel)) {
                die(json_encode(['status' => 'error', 'message' => 'Reference not found']));
            }
            if (in_array($referenceModel->slug, array_keys(Reference::HIDE_REFERENCE_ARRAY))) {
                $result = $referenceModel->update(['is_' . $referenceModel->slug . '_active' => 1]);
            } else {
                $result = $referenceModel->restore();
            }
            die(json_encode(['data' => $result, 'message' => 'Restored']));
        }
        redirect('dashboard');
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        if ($this->input->is_ajax_request()) {
            $referenceModel = Reference::find($id);
            if (is_null($referenceModel)) {
                die(json_encode(['status' => 'error', 'message' => 'Reference not found']));
            }

            if (in_array($referenceModel->slug, array_keys(Reference::HIDE_REFERENCE_ARRAY))) {
                $result = $referenceModel->update(['is_' . $referenceModel->slug . '_active' => 0]);
            } else {
                $result = $referenceModel->delete();
            }
            die(json_encode(['data' => $result, 'message' => 'Removed']));
        }
        redirect('dashboard');
    }
}
