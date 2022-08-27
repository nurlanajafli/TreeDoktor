<?php 
class Errors extends MX_Controller {

    function __construct()
    {
        parent::__construct();
    }
    
    function error_404()
    {
        die("ok");
        // 404 Header!
        $this->output->set_status_header('404');
        
        // debug
        //$this->output->enable_profiler(TRUE);

        // data
        // some data ...

        // View
        $this->load->view('error_404', $data);
    }
}