<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH . "third_party/MX/Loader.php";

class MY_Loader extends MX_Loader
{
    /**
     * Class constructor
     *
     * Sets component load paths, gets the initial output buffering level.
     *
     * @return    void
     */
    public function __construct()
    {
        parent::__construct();

    }

}
