<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * JobsManager Controller
 */
class JobsManager extends MX_Controller
{
	function __construct()
	{
		parent::__construct();

		if (!isAdmin()) {
			redirect('dashboard');
		}

		$this->_title = SITE_NAME;

		$this->load->model('mdl_administration', 'mdl_administration');
		$this->load->model('mdl_jobs');
	}

    /**
     * Open job management view
     *
     * @param  string  $type
     */
	public function index($type = 'all')
	{
	    if ($type != 'failed' && $type != 'completed' && $type != 'free') {
	        $type = 'all';
        }

        $data = [
            'pagination_url' => base_url() . 'jobs/manage' . ($this->uri->segment(3) ? '/' . $this->uri->segment(3) : ''),
            'title' => 'Manage jobs',
            'menu_administration' => 'active',
            'type' => $type
        ];

        if ($this->input->get()) {

            $custom_filter_date = $this->input->get('custom_filter_date') !== false ? $this->input->get('custom_filter_date') : '';
            $data['start'] = $this->input->get('start');
            $data['search'] = $this->input->get('search')['value'];
            $data['limit'] = $this->input->get('length'); // per_page
            $data['order'] = $this->input->get('order')[0]['dir'];
            $data['columns'] = $this->input->get('columns');
            $columnIndex = $this->input->get('order')[0]['column'];
            $column = $data['columns'][$columnIndex]['data'];

            $data['current_jobs'] = $this->mdl_jobs
                ->where($type === 'all' ? true : $this->mdl_jobs->{'get_'.$type}(), ($type === 'all' ? true : null), ($type === 'all' ? false : true))
                ->where('(job_driver like "%' . $data['search'] . '%" OR job_worker_pid like "%' . $data['search'] . '%" OR
                job_output like "%' . $data['search'] . '%" OR job_payload like "%' . $data['search'] . '%")', null)
                ->where('job_created_at like "%' . $custom_filter_date . '%"', null)
                ->get_all();

            $data['current_jobs_limited'] = $this->mdl_jobs
                ->where($type === 'all' ? true : $this->mdl_jobs->{'get_'.$type}(), ($type === 'all' ? true : null), ($type === 'all' ? false : true))
                ->where('(job_driver like "%' . $data['search'] . '%" OR job_worker_pid like "%' . $data['search'] . '%" OR
                job_output like "%' . $data['search'] . '%" OR job_payload like "%' . $data['search'] . '%")', null)
                ->where('job_created_at like "%' . $custom_filter_date . '%"', null)
                ->order_by($column, $data['order'])
                ->limit($data['limit'], $data['start'])
                ->get_all();

            foreach ($data['current_jobs_limited'] as $key => &$job) {
                $job->index = $data['start'] + $key;
                $job->original = json_encode($job);
                $job_is_completed = $job->job_is_completed;
                $job_reserved_at = $job->job_reserved_at;
                $job->job_payload = "<input class='_job_id' type='hidden' value='".$job->job_id."' id='job_".$job->job_id."'><textarea class='job-payload-hidden hidden'>" . $job->job_payload . "</textarea>" . substr ($job->job_payload, 0, 50) . "...";
                $job->job_attempts = "<input class='job-attempts-hidden hidden' type='text' value='" . $job->job_attempts . "'>" . $job->job_attempts;
                $job->job_is_completed = "<input class='job-is-completed-hidden hidden' type='text' value='" . $job->job_is_completed . "'>" . $job->job_is_completed;
                $job->job_available_at = date('Y-m-d H:i:s', $job->job_available_at);
                $job->job_reserved_at = "<input class='job-reserved-at-hidden hidden' type='text' value='" . $job->job_reserved_at . "'>" . date('Y-m-d H:i:s', $job->job_reserved_at);
                $job->job_output = ($job->job_output ? '<span class="badge badge-output" data-toggle="tooltip" data-placement="left" title="" data-html="true" data-original-title="' . strip_tags($job->job_output) . '">
                    <i class="fa fa-info"></i>
                </span>' : '-');
                $job->action = '<a class="job-actions action-edit" title="Edit job">
                    <i class="fa fa-edit"></i>
                </a>
                <a class="job-actions action-remove" title="Remove job">
                    <i class="fa fa-times"></i>
                </a>' .((!$job_is_completed && !$job_reserved_at) ? '<a class="job-actions action-execute" title="Run job">
                        <i class="fa fa-play"></i>
                    </a>' : '');
            }

            $response = array(
                "draw" => intval($this->input->get('draw')),
                "iTotalRecords" => count( $data['current_jobs'] ),
                "iTotalDisplayRecords" => count( $data['current_jobs'] ),
                "aaData" => $data['current_jobs_limited']
            );
            echo json_encode($response);
            return;
        } else {
            $this->load->view("jobs/index", $data);
        }
	}

	public function ajax_execute()
	{
        $this->checkIfAjax();

        $job_id = $this->input->post('job_id');
        $job = $this->mdl_jobs->get($job_id);

        if (!$job)
            die(json_encode(['status' => false]));

        $pid = getmypid();
        $sql = 'UPDATE jobs SET job_reserved_at = ' . time() . ', job_worker_pid = ' . $pid . ', ' .
            'job_attempts = job_attempts + 1 WHERE job_id = ' . $job->job_id;
        $this->db->query($sql);

        $this->load->driver('jobs');
        $this->jobs->processJob($job);

        $result = $this->mdl_jobs->get($job_id);
        die(json_encode(['status' => ($result->job_reserved_at && $result->job_is_completed) ? true : false, 'result' => $result]));
	}

	public function ajax_delete()
    {
        $this->checkIfAjax();

		$job_id = $this->input->post('job_id');
		$result = $this->mdl_jobs->delete($job_id);

		die(json_encode(['status' => $result]));
	}

	public function ajax_edit() {
	    $this->checkIfAjax();

		$postData = $this->input->post();
        $deleteRow = false;

        if(!isset($postData['job_id']) || empty($job = $this->mdl_jobs->get($postData['job_id']))) {
            die(json_encode(['status' => false]));
        }

        $job_payload = json_decode($job->job_payload);

        foreach ($job_payload as $payload_key => &$payload_item) {
            if (isset($postData['job_payload_'.$payload_key])) {
                $payload_item = $postData['job_payload_'.$payload_key];
            }
        }

        $job->job_payload = json_encode($job_payload);

        // make free job, if job is already completed
        // reset job to the initial state, if job is failed
        if (isset($postData['reset_failed']) || isset($postData['make_free'])) {
            $job->job_attempts = 0;
            $job->job_is_completed = 0;
            $job->job_reserved_at = 0;
            $deleteRow = true;
        }

        $result = $this->mdl_jobs->update($job->job_id, $job);

		die(json_encode(['status' => $result, 'result' => $job, 'deleteRow' => $deleteRow]));
	}

	/*
	 * Check if request comes from ajax
	 */
	public function checkIfAjax() {
        if(!$this->input->is_ajax_request()) {
            redirect('dashboard');
        }
    }
} 
