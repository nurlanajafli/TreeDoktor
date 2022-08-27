<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Safety extends APP_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_events_orm');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_incidents');
        $this->load->config('safety_meeting_form');
	}

	function index() {
        $response = array(
            'status' => TRUE,
            'data' => [
                'hazards' => config_item('hazards'),
                'controls' => config_item('controls'),
            ],
        );
        return $this->response($response);
	}

	function incident() {
	    $payload = $this->input->post() ? $this->input->post() : [];
	    if(isset($payload['job_id']))
	        unset($payload['job_id']);
        if(isset($payload['sign']))
            unset($payload['sign']);
        if(isset($payload['uploads']))
            unset($payload['uploads']);

	    $data = [
	        'inc_user_id' => $this->user->id,
	        'inc_job_id' => $this->input->post('job_id', NULL),
	        'inc_created_at' => date('Y-m-d H:i:s'),
	        'inc_payload' => json_encode($payload),
        ];
	    $inc_id = $this->mdl_incidents->insert($data);
	    $data['inc_id'] = $inc_id;
	    $data['inc_payload'] = $payload;

	    /****1.6.2(1.6.4) Support files send with the form-submit****/
        $this->load->library('upload');
        $photos = $signature = [];
        if (isset($_FILES['photos']) && is_array($_FILES['photos'])) {
            foreach ($_FILES['photos']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['photos']['name'][$key];
                $_FILES['file']['type'] = $_FILES['photos']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['photos']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['photos']['error'][$key];
                $_FILES['file']['size'] = $_FILES['photos']['size'][$key];

                $path = 'uploads/incidents/' . $inc_id . '/photos/';
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        /****1.6.2(1.6.4) Support files send with the form-submit****/

        if ($this->input->post('sign')) {
            $signature = str_replace('[removed]', '', $this->input->post('sign'));
            if($signature == $this->input->post('sign'))
                $signature = explode(',', $this->input->post('sign'))[1];
            
            $path = 'uploads/incidents/' . $inc_id . '/sign/signature.png';
            $tmpPath = sys_get_temp_dir() . '/incident_signature_' . $inc_id . '.png';

            $im = imagecreatefromstring(base64_decode($signature));
            imagealphablending($im, false);
            imagesavealpha($im, true);

            imagepng($im, $tmpPath);
            imagedestroy($im);

            if(!getimagesize($tmpPath)) {
                return $this->response([
                    'status' => FALSE,
                    'message' => 'Incorrect Signature Data'
                ], 400);
            }

            bucket_move($tmpPath, $path, ['ContentType' => 'image/png']);
            @unlink($tmpPath);

            $signature = [
                'filepath' => $path,
                'filename' => 'signature.png'
            ];
        }

        if($this->input->post('uploads') && is_array($this->input->post('uploads'))) {
            foreach ($this->input->post('uploads') as $file) {
                bucket_copy($file, 'uploads/incidents/' . $inc_id . '/photos/' . basename($file));
                bucket_unlink($file);
            }
        }

        $data['photos'] = $photos;
        $data['signature'] = $signature;

        $response = [
            'status' => TRUE,
            'data' => $data,
        ];
        return $this->response($response);
    }

    function upload() {
        $this->load->library('upload');
        $photos = [];
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $val) {

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];

                $path = 'uploads/incidents/tmp/';
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
            }
        }
        return $this->response([
            'status' => TRUE,
            'data' => $photos,
        ]);
    }

    function incident_jobs($date = NULL) {
        if(!$date)
            $date = date('Y-m-d');

        $list = $this->mdl_schedule->getJobsIncidentForm($date, $this->user->id);

        $response = [
            'status' => TRUE,
            'data' => $list,
        ];
        return $this->response($response);
    }

	function save() {
	    $event = $this->mdl_schedule->find_by_id($this->input->post('ev_event_id'));

	    if(!$event) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Event'
            ]);
        }

	    $workOrder = $this->mdl_workorders->find_by_id($event->event_wo_id);

        $this->mdl_events_orm->save($this->input->post() + [
                'ev_team_id' => $event->event_team_id,
                'ev_estimate_id' => $workOrder->estimate_id
            ]);
        save_signature($this->input->post());
        return $this->response([
            'status' => TRUE,
            'data' => [
                'pdf_url' => base_url('events/tailgate_safety_pdf/' . $event->id)
            ]
        ]);
    }

}
