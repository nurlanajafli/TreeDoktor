<?php

class Queue extends Queue_Controller
{
    protected $currentJob;
    protected $processMessages = [];

    function __construct(){
        parent::__construct();
        $this->db->save_queries = FALSE;
    }

    // Initializer
    protected function init() {
        $this->load->driver('jobs');
    }

    // Worker
    protected function handleWork($static, $pid) {
      if ( $this->terminating )
          return false;

        $job = $this->jobs->popJob(TRUE, $pid);

        if (!$job)
            return false;

        $this->jobs->processJob($job);
        $job = null;
        $this->jobs->clear();
        return true;
    }

    // Listener
    protected function handleListen() {
        if ( $this->terminating )
            return false;
        $this->currentJob = $this->jobs->popJob(FALSE);
        $result = (bool)$this->currentJob;
        $this->currentJob = null;
        $this->jobs->clear();
        return $result;
    }

    protected function handleMessage($pid, $msg = NULL) {
        $this->jobs->setMessage($pid, $msg);
        return TRUE;
    }
}
