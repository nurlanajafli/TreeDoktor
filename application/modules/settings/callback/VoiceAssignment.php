<?php
namespace application\modules\settings\callback;

use application\core\Interfaces\CallbackInterface;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;


class VoiceAssignment implements CallbackInterface
{

    public function handle(array $params)
    {
        $CI = & get_instance();
        $CI->load->model('mdl_calls');
        $TaskAttributes = json_decode($CI->input->post('TaskAttributes'));
        if ($TaskAttributes == false) {
            var_dump($CI->input->post());
            exit;
        }
        $WorkerAttributes = json_decode($CI->input->post('WorkerAttributes'));

        $call_note = [
            'call_type' => 'taskrouter',
            'call_from' => $TaskAttributes->from,
            'call_to' => $TaskAttributes->to,
            'call_client_id' => $TaskAttributes->clientId,
            'call_user_id' => null,
            'call_route' => 1,
            'call_date' => date('Y-m-d H:i:s'),
            'call_twilio_sid' => $TaskAttributes->call_sid,
            'call_complete' => '0',
            'call_disabled' => 1,
            'call_workspace_sid' => $CI->input->post('WorkspaceSid'),
        ];

        $CI->mdl_calls->insert($call_note);
        $idleActivitySid = SoftTwilioActivityModel::where('friendlyName', 'Idle')->first()->sid;
        $assignment_instruction = [
            'instruction' => 'dequeue',
            'to' => 'client:' . $WorkerAttributes->contact_uri,
            'from' => $TaskAttributes->from,
            'record' => 'record-from-answer',
            'status_callback_url' => base_url('/callback/settings/recording'),
            'post_work_activity_sid' => $idleActivitySid
        ];

        return $assignment_instruction;
    }
}
