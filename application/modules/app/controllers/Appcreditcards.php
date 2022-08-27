<?php use application\modules\clients\models\Client;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Appcreditcards extends APP_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
    }

    function get_cards()
    {
        $client_id = $this->input->post("client_id");
        $cards = [];

        if (!empty($client = Client::find($client_id))) {
            if ($client->client_payment_profile_id) {
                try {
                    $cards = $this->arboStarProcessing->profileCards($client->client_payment_profile_id, $client->client_payment_driver);
                }
                catch (PaymentException $e) {
                    return $this->response([
                        'status' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        }

        return $this->response(array(
            'status' => true,
            'data' => $cards
        ));
    }

    public function get_card_form($client_id)
    {
        if (!empty($client = Client::getWithContact($client_id))) {
            $body = 'Payments disabled';

            if (config_item('processing')) {
                $billingData = [
                    'customer_id' => $client->client_id,
                    'name' => $client->client_name,
                    'address' => $client->client_address,
                    'city' => $client->client_city,
                    'state' => $client->client_state,
                    'zip' => $client->client_zip,
                    'country' => $client->client_country,
                    'phone' => $client->primary_contact->cc_phone_clean ?? null,
                    'email' => $client->primary_contact->cc_email ?? null,
                    'profile_id' => $client->client_payment_profile_id,
                    'authorization' => $this->token
                ];

                $body = $this->arboStarProcessing->getMobileCardForm($billingData, $client->client_payment_driver);
            }

            header('Content-type: text/html');
            $this->load->view('app/clear', ['body' => $body]);

            return;
        }

        return $this->response([
            'status' => false,
            'message' => "Not found client"
        ]);
    }

    function add_card()
    {
        $client_id = $this->input->post('client_id');

        if (!empty($client = Client::getWithContact($client_id))) {
            $billingData = [
                'customer_id' => $client->client_id,
                'name' => $client->client_name,
                'address' => $client->client_address,
                'city' => $client->client_city,
                'state' => $client->client_state,
                'zip' => $client->client_zip,
                'country' => $client->client_country,
                'phone' => $client->primary_contact->cc_phone_clean ?? null,
                'email' => $client->primary_contact->cc_email ?? null,
                'profile_id' => $client->client_payment_profile_id
            ];

            if ($client->client_payment_profile_id) {
                try {
                    $this->arboStarProcessing->profileAddCard(
                        $client->client_payment_profile_id,
                        $billingData,
                        $this->input->post('token'),
                        $this->input->post('crd_name'),
                        $this->input->post('additional'),
                        $client->client_payment_driver
                    );
                } catch (PaymentException $e) {
                    return $this->response([
                        'status' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                try {
                    $profile_id = $this->arboStarProcessing->createProfile(
                        $billingData,
                        $this->input->post('token'),
                        $this->input->post('crd_name'),
                        $this->input->post('additional'),
                        $client->client_payment_driver
                    );
                } catch (PaymentException $e) {
                    return $this->response([
                        'status' => false,
                        'error' => $e->getMessage()
                    ]);
                }

                $client->client_payment_profile_id = $profile_id;
                $client->client_payment_driver = $this->arboStarProcessing->getAdapter();
                $client->save();
            }

            try {
                $cards = $this->arboStarProcessing->profileCards($client->client_payment_profile_id, $client->client_payment_driver);
            }
            catch (PaymentException $e) {
                return $this->response([
                    'status' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $this->response(array(
                'status' => true,
                'data' => $cards
            ));
        }
    }

    function delete_card()
    {
        $client_id = $this->input->post('client_id');
        $card_id = $this->input->post('card_id');

        if (empty($card_id)) {
            return $this->response([
                'status' => false,
                'message' => 'No card'
            ]);
        }

        if (empty($client = Client::find($client_id))) {
            return $this->response([
                'status' => false,
                'message' => 'Client not found'
            ]);
        }

        if (!$client->client_payment_profile_id) {
            return $this->response([
                'status' => false,
                'error' => 'Not found client payment profile'
            ]);
        }

        try {
            $this->arboStarProcessing->profileDeleteCard($client->client_payment_profile_id, $card_id, $client->client_payment_driver);
        } catch (PaymentException $e) {
            return $this->response([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        return $this->response([
            'status' => true
        ]);
    }

    public function success($card_id) {
        header('Content-type: text/html');
        $this->load->view('app/success',['card_id' => $card_id]);
        return;
    }

    public function error() {
        header('Content-type: text/html');
        $this->load->view('app/error',[]);
        return;
    }
}
