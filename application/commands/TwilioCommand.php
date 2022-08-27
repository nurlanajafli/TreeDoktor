<?php


namespace application\commands;

use application\core\Console\Command;
use application\modules\client_calls\models\ClientsCall;
use Twilio\Rest\Client;

class TwilioCommand extends Command
{
    /**
     * The name and signature of the console command.
     * php index.php mixture twilio:update_recording_urls 2021-11-01
     * @var string
     */
    protected $signature = 'twilio:update_recording_urls {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twilio param date format 2021-10-31';

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
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function handle()
    {
        $CI = &get_instance();
        $CI->load->model('mdl_calls');
        $client = new Client($CI->config->item('accountSid'), $CI->config->item('authToken'));

        $date = $this->argument('date');
        $recordings = $client->recordings->read([
            "dateCreatedAfter" => new \DateTime($date),
        ]);

        foreach ($recordings as $recording) {
            $callRows = ClientsCall::where('call_twilio_sid','=', $recording->callSid)->first();

            $call_voice = 'https://api.twilio.com/' . str_replace('.json', '', $recording->uri);
            if($callRows && !empty($callRows)) {
                $callRows->call_voice = $call_voice;
                $callRows->save();
            }
        }
        $this->output->text('End');
    }
}