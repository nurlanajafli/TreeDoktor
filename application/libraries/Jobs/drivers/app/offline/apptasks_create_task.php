<?php
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class apptasks_create_task extends CI_Driver implements JobsInterface
{
    public $payload;
    public $CI;
    public $body;
    public $wsClient;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_user');
        $this->CI->load->model('mdl_client_tasks');
        $this->CI->token = TRUE;
        $this->CI->user = new stdClass();
    }

    public function getPayload($data = NULL)
    {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->payload = json_decode($job->job_payload, TRUE);
        $this->body = $this->payload['body'] ?? [];
        $post = $_POST = $this->body;
        $this->CI->user->id = $this->payload['user_id'];
        $anyTimeNotExist = $this->notRequiredIfAnytimeExists();

        $data['task_client_id'] = strip_tags($post['client_id']);
        $data['task_lead_id'] = isset($post['lead_id']) ? strip_tags($post['lead_id']) : null;
        $data['task_desc'] = isset($post['description']) ? nl2br($post['description']) : '';
        $data['task_author_id'] = $this->payload['user_id'];
        $data['task_category'] = isset($post['category']) ? strip_tags($post['category']) : null;
        $data['task_status'] = "new";
        $data['task_date_created'] = date('Y-m-d');

        $data['task_date'] = $anyTimeNotExist ? date("Y-m-d", strtotime($post['start_date'])) : date('Y-m-d');
        $data['task_start'] = $anyTimeNotExist ? date('H:i', (300 * round(strtotime($post['start_time']) / 300))) : null;
        $data['task_end'] = $anyTimeNotExist ? date('H:i', (300 * round(strtotime($post['end_time']) / 300))) : null;

        $data['task_address'] = isset($post['address']) ? strip_tags($post['address']) : null;
        $data['task_city'] = isset($post['city']) ? strip_tags($post['city']) : null;
        $data['task_state'] = isset($post['state']) ? strip_tags($post['state']) : null;
        $data['task_zip'] = isset($post['zip']) ? strip_tags($post['zip']) : null;
        $data['task_country'] = isset($post['country']) ? strip_tags($post['country']) : null;
        $data['task_latitude'] = isset($post['lat']) ? strip_tags($post['lat']) : null;
        $data['task_longitude'] = isset($post['lng']) ? strip_tags($post['lng']) : null;

        if (!$data['task_latitude'] || !$data['task_longitude']) {
            $coords = get_lat_lon($data['task_address'], $data['task_city'], $data['task_state'], $data['task_zip'], $data['task_country']);
            $data['task_latitude'] = $coords['lat'];
            $data['task_longitude'] = $coords['lon'];
        }

        $data['task_assigned_user'] = isset($post['assigned_id']) ? $post['assigned_id'] : null;

        $task_id = $this->CI->mdl_client_tasks->insert($data);

        if ($task_id) {
            make_notes($data['task_client_id'], 'I just created a new task "' . $task_id . '" for the client.', 'system', 0);
            $this->_socketWrite('syncJobSuccess', [
                'request_id' => $this->payload['id'],
                'client_id' => $data['task_client_id']
            ]);
        } else {
            $this->_socketWrite('syncJobFailed', [
                'request_id' => $this->payload['id'],
                'message' => 'Task not added',
            ]);
        }
        return true;
    }

    private function notRequiredIfAnytimeExists()
    {
        if (isset(request()->anytime) && (bool)request()->anytime) {
            return false;
        }
        return true;
    }

    private function _socketWrite($msg, $response = []) {
        if(config_item('wsClient')) {
            if(!$this->wsClient) {
                $this->wsClient = new WSClient(new Version1X(config_item('wsClient') . '?chat=1&user_id=' . $this->payload['user_id']));
                $this->wsClient->initialize();
            }
            if($this->wsClient) {
                $this->wsClient->emit('room', ['chat-' . $this->payload['user_id']]);
                $this->wsClient->emit('message', ['method' => $msg, 'params' => $response]);
            }
        }
    }

}