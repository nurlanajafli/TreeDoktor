<?php

namespace application\commands;

use application\modules\estimates\models\Estimate;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use Illuminate\Console\Command;

class SetServicesStatusArbogoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arbogold:setServicesStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $statuses = [
        'Invoiced' => 2,
        'Pending' => 0,
        'Approved' => 0,
        'NotApproved' => 1,
        'Completed' => 2,
        'Scheduled' => 3,
        'ReadyToInvoice' => 2,
//        'SkipNoReSchedule' => 8,
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $url = 'https://shadylane.arborgold.net/AG/JobInformation/ServiceQuery/GetServicesOnJob/';
        $url = 'https://sandborntree.arborgold.net/AG/JobInformation/ServiceQuery/GetServicesOnJob/';
//        $authorization = "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJDb21wYW55TG9naW5JZCI6NDIsIkd1aWQiOiJkYzE2ZTdjZi04ZWI2LTRmYjctYTUyYS0iLCJSZXNvdXJjZSI6MjQxLCJSZXNvdXJjZUVtcGxveWVlIjo4OSwiUGxhdGZvcm0iOiJDbG91ZCIsIlN1YnNjcmlwdGlvblR5cGUiOiJVbHRpbWF0ZSIsIkVtcGxveWVlTG9nZ2VkSW4iOjI0MSwiSXNBcmJvcmdvbGRBZG1pbiI6ZmFsc2UsIklzUGhvbmVNZXNzYWdlIjpmYWxzZSwiSXNDcmV3T25seSI6ZmFsc2UsIkxvZ2dlZEluRW1wTmFtZSI6Ik1heWEsR2hhbmRpYWwiLCJJc0FkbWluIjp0cnVlfQ.SOCEQHqHH5UH4DCQx9tST2ESL9cL0Vi92qtmlaOOWLs";
        $authorization = "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJDb21wYW55TG9naW5JZCI6MTQsIkd1aWQiOiIyOTAwNzJjNi1kMmZhLTRjMjctODQxZC0iLCJSZXNvdXJjZSI6MTU3LCJSZXNvdXJjZUVtcGxveWVlIjo4NywiUGxhdGZvcm0iOiJDbG91ZCIsIlN1YnNjcmlwdGlvblR5cGUiOiJQcmVtaXVtIiwiRW1wbG95ZWVMb2dnZWRJbiI6MTU3LCJJc0FyYm9yZ29sZEFkbWluIjpmYWxzZSwiSXNQaG9uZU1lc3NhZ2UiOmZhbHNlLCJJc0NyZXdPbmx5IjpmYWxzZSwiTG9nZ2VkSW5FbXBOYW1lIjoiTWljaGFlbGEsSGlua3NvbiIsIklzQWRtaW4iOmZhbHNlfQ.EWpmdjG8GZje-Om3YqOxMzMKF-VJbVNRa4I3axhf6MM";
        // create curl resource
        $ch = curl_init();

        // set url

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//        $estimates = Estimate::whereDoesntHave('invoice')->where('status', 9)->get();
//        $estimates = Estimate::whereDoesntHave('invoice')->where('estimate_id', '>', 3542)->get();
        $estimates = Estimate::whereDoesntHave('invoice')->get();

//        $estimates = Estimate::whereDoesntHave('invoice')->whereHas('workorder',
//            function ($q) {
//                $q->where('wo_status', 14);
//            })->get();

        $AllStatuses = [];
        foreach ($estimates as $estimate) {
            curl_setopt($ch, CURLOPT_URL, $url . $estimate->estimate_qb_id);

            //debug2($estimates);

            // $output contains the output string
            $output = curl_exec($ch);
            $result = json_decode($output);
            if (!empty($result) && is_array($result)){
                foreach ($result as $job){
                    if(isset($this->statuses[$job->ServiceStatus])) {
                        $serviceName = $job->ServiceName;
                        EstimatesService::with(["service" => function ($q) use ($serviceName) {
                            $q->where('service.service_name', $serviceName);
                        }])
                            ->where('service_price', $job->Price)
                            ->where('estimate_id', $estimate->estimate_id)
                            ->update(['service_status' => $this->statuses[$job->ServiceStatus]]);
                        $this->output->text($estimate->estimate_no);
                    }
                    $AllStatuses[] = $job->ServiceStatus;
                }
            }
        }

        // close curl resource to free up system resources
        curl_close($ch);

        $this->output->text(array_unique($AllStatuses));
        $this->output->text('end!');
    }
}
