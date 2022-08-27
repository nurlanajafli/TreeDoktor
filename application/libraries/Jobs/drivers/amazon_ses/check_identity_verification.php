<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class check_identity_verification extends CI_Driver implements JobsInterface
{
    private $CI;
    public function getPayload($data = NULL) {
        return $data;
    }

    public function execute($job = NULL)
    {
        $this->CI =& get_instance();
        $this->CI->load->library('email');
        $this->CI->load->library('MailDriver/amazon');
        $this->CI->load->model('mdl_amazon_identities_orm', 'amazon_identities');
        $payload = json_decode($job->job_payload);

        // get all database identity items for checking
        $identitiesDB = $this->CI->amazon_identities->get_all();

        if (empty($identitiesDB)) return true;

        $identities = array_column($identitiesDB, 'identity');

        //get db identities info from aws
        $verificationAttributes = $this->CI->amazon->getIdentityVerificationAttributes($identities)['VerificationAttributes'];
        $dkimAttributes = $this->CI->amazon->getIdentityDkimAttributes($identities)['DkimAttributes'];

        foreach ($identitiesDB as $identityDB) {
            // check db identity state in aws\
            $identity = $identityDB->identity;
            if (isset($verificationAttributes[$identityDB->identity])) {
                $this->CI->amazon_identities->update($identityDB->identity_id, [
                    'verificationAttributes' => json_encode($verificationAttributes[$identityDB->identity])
                ]);
            } else {
                $this->CI->amazon_identities->delete($identityDB->identity_id);
            }

            if (isset($dkimAttributes[$identityDB->identity])) {
                $this->CI->amazon_identities->update($identityDB->identity_id, [
                    'dkimAttributes' => json_encode($dkimAttributes[$identityDB->identity])
                ]);
            } else {
                $this->CI->amazon_identities->delete($identityDB->identity_id);
            }


        }
        if(!isset($this->CI->wsClient)) {
            $params = new Version1X(config_item('wsClient') . ($payload->user_id ? ('?chat=1&user_id=' . $payload->user_id) : ''));
            $this->CI->wsClient = new WSClient($params);
            $this->CI->wsClient->initialize();
        }
        if($this->CI->wsClient) {
            if ($payload->user_id) {
                $this->CI->wsClient->emit('room', ['chat-' . $payload->user_id]);
            }
            $this->CI->wsClient->emit('message', ['method' => 'checkAwsIdentitiesStatus', 'params' => []]);
        }
        return TRUE;
    }
}
