<?php

use application\modules\clients\models\ClientNote;

/**
 * @param $client_id
 * @param $message
 * @param string $type
 * @param null $lead_id
 * @param null $emailInstance
 * @return bool|int
 */
function make_notes($client_id, $message, $type = 'system', $lead_id = NULL, $emailInstance = null)
{
	$CI = & get_instance();

    if($CI->session->userdata('system_user')) {
        return TRUE;
    }

    $CI->load->model('mdl_clients');
    $note_data['client_note'] = $message;
    $note_data['author'] = request()->user()->id ?? 0;

    if (isset($CI->token) && $CI->token) {
        $note_data['author'] = $CI->user->id;
    }

    $note_data['client_id'] = $client_id;
    $note_data['client_note_date'] = date('Y-m-d H:i:s');
    $note_data['client_note_type'] = $type;
    $note_data['lead_id'] = $lead_id;

    $id = ClientNote::createNote($note_data);

    if ($id) {
        if ($type === 'email' && $emailInstance && method_exists($emailInstance, 'setEmailEntities')) {
            $emailInstance->setEmailEntities(['entity' => 'clientNote', 'id' => $id]);
        }

        return $id;
    }

	return FALSE;
}
