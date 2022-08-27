<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
interface_exists('JobsInterface', FALSE) OR require_once(APPPATH . '/libraries/Jobs/JobsInterface.php');

class Jobs extends MY_Driver_Library
{
    protected $maxAttemptsNumber = 10;
    protected $driversPath = /*FCPATH . */APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'Jobs' . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR;
    public $CI;
    private $job;
    public function __construct() {
        $this->CI =& get_instance();
        $this->valid_drivers = array_map(
            function($str) {
                return str_replace(['.php', $this->driversPath], '', $str);
            }, get_filenames(APPPATH . 'libraries/Jobs/drivers/', TRUE)
        );
    }
    public function popJob($lock = TRUE, $pid = NULL) {

        $sql = 'START TRANSACTION';
        $this->CI->db->query($sql);

        $sql = 'SELECT * FROM jobs WHERE job_available_at <= ' . time() . ' AND ' .
            'job_reserved_at = 0 AND job_is_completed = 0 AND job_attempts < ' . $this->maxAttemptsNumber .  ' ' .
            'ORDER BY job_available_at ASC LIMIT 1 FOR UPDATE';
        $this->job = $this->CI->db->query($sql)->row();
        unset($sql);

        if($lock) {
            if($this->job) {
                $sql = 'UPDATE jobs SET job_reserved_at = ' . time() . ', job_worker_pid = ' . $pid . ', ' .
                    'job_attempts = job_attempts + 1 WHERE job_id = ' . $this->job->job_id;
                $this->CI->db->query($sql);
                unset($sql);
            }
        }
        $sql = 'COMMIT';
        $this->CI->db->query($sql);
        unset($sql);
        return $this->job;
    }

    function clear() {
        $this->job = null;
    }

    public function processJob($job = FALSE, $pid = NULL) {
        if(!$job)
            return FALSE;
        if (array_search(str_replace('/', DIRECTORY_SEPARATOR, $job->job_driver), $this->valid_drivers) === FALSE)
            return FALSE;
        $result = $this->{$job->job_driver}->execute($job);
        $jobData = [
            'job_is_completed' => $result ? TRUE : FALSE,
            'job_worker_pid' => NULL,
        ];

        if(!$result)
            $jobData['job_reserved_at'] = 0;
        $this->CI->db->where('job_id', $job->job_id);
        $this->CI->db->update('jobs', $jobData);

        $models = $this->CI->load->get_loaded_models();
        foreach ($models as $key => $val) {
            $this->CI->load->unload_model($val);
            if(isset($this->CI->$val)) {
                unset($this->CI->$val);
            }
        }
        if(isset($this->{$job->job_driver})) {
            unset($this->{$job->job_driver});
        }
    }

    public function exists() {
        $this->CI->db->where('job_available_at <=', time());
        $this->CI->db->where('job_reserved_at', 0);
        $this->CI->db->where('job_is_completed', 0);
        $this->CI->db->where('job_attempts <', $this->maxAttemptsNumber);
        return $this->CI->db->count_all_results('jobs');
    }

    public function pushJob($className, $arg = NULL, $availableFrom = null, $delay = 0, $storeJobIdToEntity = null)
    {
        $classPath = $this->_getDriverPath($className);
        if (array_search($classPath, $this->valid_drivers) === FALSE)
            return FALSE;
        $payload = $this->$className->getPayload($arg);

        if(!$payload)
            return FALSE;

        unset($this->$className);
        if(is_array($payload) || is_object($payload))
            $payload = json_encode($payload);
        $job_available = $availableFrom ? intval($availableFrom) : time();
        $job_available = $delay ? $job_available + intval($delay) : $job_available;
        $job = [
            'job_driver' => $className,
            'job_payload' => $payload,
            'job_attempts' => 0,
            'job_is_completed' => 0,
            'job_available_at' => $job_available,
            'job_reserved_at' => 0,
            'job_created_at' => date('Y-m-d H:i:s'),
        ];

        $this->CI->db->insert('jobs', $job);
        $job = $job_available = $payload = $classPath = $arg = null;
        $jobId = $this->CI->db->insert_id();

        // add job_id to related entity if it exists
        if (is_object($storeJobIdToEntity)) {
            try {
                $storeJobIdToEntity->job_id = $jobId;
                $storeJobIdToEntity->save();
            }
            catch (\Doctrine\DBAL\Query\QueryException $e) {

            }
        }

        return $jobId;
    }
    public function setMessage($pid, $msg) {
        $msg = trim($msg);
        if(!$msg)
            return FALSE;
        $this->CI->db->where('job_worker_pid', $pid);
        $this->CI->db->update('jobs', ['job_output' => $msg]);
        $msg = null;
        return TRUE;
    }
    private function _getDriverPath($className) {
        $segments = explode('/', $className);
        $str = str_replace('/', DIRECTORY_SEPARATOR, $className);
        if($segments && count($segments) > 1) {//countOk
            $str = NULL;
            foreach ($segments as $key => $val)
                $str .= $key + 1 == count($segments) ? $val . DIRECTORY_SEPARATOR : $val . DIRECTORY_SEPARATOR;//countOk
            $str = rtrim($str, DIRECTORY_SEPARATOR);
        }
        return $str;
    }
}
