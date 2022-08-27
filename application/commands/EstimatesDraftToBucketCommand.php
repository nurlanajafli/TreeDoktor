<?php

namespace application\commands;

use Illuminate\Console\Command;

/**
 * @property \CI_Controller CI
 */
class EstimatesDraftToBucketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estimate:draft_to_bucket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move estimates draft from Redis to S3 bucket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->library('Common/EstimateActions');
//        $this->CI->load->helper('redis');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->CI->estimateactions->cronRedisToBucket();

        return 1;
    }
}
