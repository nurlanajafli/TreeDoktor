<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class Todo extends MX_Controller
{

    /*******************************************************************************************************************
     * //*************
     * //*************                                            Dashboard Controller;
     * //*************
     *******************************************************************************************************************/

    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }

        $this->load->model('mdl_dashboard_tasks', 'mdl_tasks');
        $this->load->model('mdl_user', 'mdl_user');

        //helper library
        $this->load->library('form_validation');
    }

    function ajax_complete()
    {
        $id = $this->input->post('taskId');

        if ($this->mdl_tasks->setComplete($id)) {
            $data['todo'] = $this->mdl_tasks->get($id);
            $data['users'] = $this->mdl_user->getActiveUsersWithTaskManager();
            return $this->response([
                'status' => 'ok',
                'id' => $id,
                'time' => str2ts($data['todo']->task_date_created),
                'html' => $this->load->view('partials/todo_completed_item', $data,'true')
            ]);
        } else {
            return $this->response([
                'status' => 'error',
                'error' => "No data found. You access wrong to do list."
            ]);
        }

    }

    function ajax_revert()
    {
        $id = $this->input->post('taskId');
        $updateData = array('task_status' => '1');
        $wdata = array('task_id' => $id);
        if($this->mdl_tasks->updateTask($updateData, $wdata)){
            $data['todo'] = $this->mdl_tasks->get($id);
            $data['users'] = $this->mdl_user->getActiveUsersWithTaskManager();
            return $this->response([
                'status' => 'ok',
                'id' => $id,
                'time' => str2ts($data['todo']->task_date_created),
                'html' => $this->load->view('partials/todo_item', $data,'true')
            ]);
        } else {
            return $this->response([
                'status' => 'error',
                'error' => "No data found. You access wrong to do list."
            ]);
        }
    }

    function ajax_delete()
    {
        $id = $this->input->post('taskId');
        if ($this->mdl_tasks->delete($id)) {
            return $this->response([
                'status' => 'ok',
                'id' => $id,
            ]);
        } else {
            return $this->response([
                'status' => 'error',
                'error' => "No data found. You deleted wrong to do list."
            ]);
        }
    }

    function ajax_add()
    {

        // Validation:
        $this->form_validation->set_rules('task_urgency', 'Task Urgency', '');
        $this->form_validation->set_rules('task_description', 'Task Description', 'required');


        if ($this->form_validation->run()) {
            $todo = array(
                'task_urgency' => $this->input->post('task_urgency'),
                'task_description' => $this->input->post('task_description'),
                'user_id' => $this->session->userdata('user_id'),
                'task_date_created' => date('Y-m-d H:i:s'),
                'task_created_by' => $this->session->userdata('user_id'));
            if($id = $this->mdl_tasks->add($todo)) {
                $data['todo'] = $this->mdl_tasks->get($id);
                $data['users'] = $this->mdl_user->getActiveUsersWithTaskManager();
                return $this->response([
                    'status' => 'ok',
                    'id' => $id,
                    'time' => str2ts($data['todo']->task_date_created),
                    'html' => $this->load->view('partials/todo_item', $data, 'true')
                ]);
            } else {
                return $this->response([
                    'status' => 'error',
                    'error' => "Ooops.. Error when try add new Task!"
                ]);
            }
        } else {
            return $this->response([
                'status' => 'error',
                'error' => "Ooops.. Error when try add new Task!"
            ]);
        }
    }

    function ajax_edit()
    {
        // Validation:
        $this->form_validation->set_rules('task_urgency', 'Task Urgency', '');
        $this->form_validation->set_rules('task_description', 'Task Description', 'required');
        $this->form_validation->set_rules('task_id', 'Task Id', 'required');

        if ($this->form_validation->run()) {
            $updateData = array(
                'task_urgency' => $this->input->post('task_urgency'),
                'task_description' => $this->input->post('task_description'),
            );
            $wdata['task_id'] = $this->input->post('task_id');
            if($this->mdl_tasks->updateTask($updateData, $wdata)){
                $data['todo'] = $this->mdl_tasks->get($wdata['task_id']);
                $data['users'] = $this->mdl_user->getActiveUsersWithTaskManager();
                return $this->response([
                    'status' => 'ok',
                    'id' => $wdata['task_id'],
                    'time' => str2ts($data['todo']->task_date_created),
                    'html' => $this->load->view('partials/todo_item', $data,'true')
                ]);
            } else {
                return $this->response([
                    'status' => 'error',
                    'error' => "Ooops.. Error when try edit Task!"
                ]);
            }
        } else {
            return $this->response([
                'status' => 'error',
                'error' => "Ooops.. Error when try add new Task!"
            ]);
        }
    }

    function ajax_assign()
    {

        $task_id = $this->input->post('task_id');
        $user_id = $this->input->post('user_id');

        if ($task_id != '' && $user_id != '') {

            $updateData = array(
                'user_id' => $user_id,
                'task_created_by' => $this->session->userdata('user_id'),
                'task_date_created' => date('Y-m-d H:i:s'),
            );
            $wdata['task_id'] = $this->input->post('task_id');
            if($this->mdl_tasks->updateTask($updateData, $wdata)){
                $data['todo'] = $this->mdl_tasks->get($wdata['task_id']);
                $data['users'] = $this->mdl_user->getActiveUsersWithTaskManager();
                return $this->response([
                    'status' => 'ok',
                    'id' => $wdata['task_id'],
                    'time' => str2ts($data['todo']->task_date_created),
                    'html' => $this->load->view('partials/todo_item', $data,'true')
                ]);
            } else {
                return $this->response([
                    'status' => 'error',
                    'error' => "Sorry! there is some problem while assigning task."
                ]);
            }

        } else {
            return $this->response([
                'status' => 'error',
                'error' => "Oops, uou have not selected a user to assign task."
            ]);
        }
    }


}

//end of file todo.php
