<?php

use application\modules\clients\models\Client;
use application\modules\estimates\models\Estimate as EstimateModel;
use application\modules\invoices\models\Invoice;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\workorders\models\Workorder;

class PaymentPortal extends Portal_Controller
{

    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * @param $method
     * @param $extra
     * @param $user_id
     * @return void
     */
    public function addPayment($method = false, $extra = false, $user_id = false)
    {
        $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
        /******************VALIDATION******************/
        if (!$method) {
            if (!$method = $this->input->post('method')) {
                return $this->response([
                    'status' => 'error',
                    'errors' => ['payment_method' => 'Incorrect payment method']
                ], 422);
            }
        }

        if (!$this->input->post('amount')) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Amount Is Required']], 422);
        }

        $amount = getAmount($this->input->post('amount'));
        if (!$amount) {
            return $this->response(['status' => 'error', 'errors' => ['payment_amount' => 'Incorrect Payment Amount']],
                422);
        }

        $tips = 0;

        if ($method == config_item('default_cc')) {
            if (_CC_MAX_PAYMENT != 0 && $amount > _CC_MAX_PAYMENT) {
                return $this->response([
                    'status' => 'error',
                    'errors' => ['payment_amount' => 'Maximum Payment Amount ' . money(_CC_MAX_PAYMENT), 422]
                ]);
            }

            if (!$this->input->post('token')) {
                return $this->response([
                    'status' => 'error',
                    'error' => 'Card processing error',
                    'errors' => ['cc_select' => 'Payment card is not selected']
                ], 422);
            }


            if ($this->input->post('token') && !$this->input->post('crd_name')) {
                return $this->response([
                    'status' => 'error',
                    'error' => 'Card processing error',
                    'errors' => ['crd_name' => 'Card Holder Name Is Required']
                ], 422);
            }

            if ($this->input->post('tips')) {
                $tips = (float)getAmount($this->input->post('tips'));
            }
        }

        if (!$this->input->post('estimate_id') && !$this->input->post('invoice_id') && !$this->input->post('workorder_id')) {
            return $this->response(['status' => 'error', 'error' => 'Incorrect Request'], 400);
        }

        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            if ($_FILES['payment_file']['tmp_name'] && !is_image($_FILES['payment_file']['tmp_name']) && !is_pdf($_FILES['payment_file']['tmp_name'])) {
                return $this->response(['status' => 'error', 'error' => 'File must be image or PDF'], 422);
            }
        }

        /******************VALIDATION******************/

        $estimate_id = $this->input->post('estimate_id');
        $workorder_id = $this->input->post('workorder_id');
        $workorder_data = false;
        if ($workorder_id) {
            $workorder_data = Workorder::where('id', '=', $workorder_id)->first();
        }

        $invoice_data = $estimate_id ? Invoice::where('invoices.estimate_id', '=', $estimate_id)->first() : false;
        if (!$estimate_id && !$invoice_data && !$workorder_data) {
            $invoice_id = $this->input->post('invoice_id');
            if (!$invoice_id) {
                return $this->response(['status' => 'error', 'msg' => 'Incorrect Request'], 400);
            }
            $invoice_data = Invoice::where('id', '=', $invoice_id)->first();
            if (!$invoice_data) {
                return $this->response(['status' => 'error', 'msg' => 'Incorrect Invoice'], 400);
            }
            $estimate_id = $invoice_data->estimate_id;
        } elseif ($workorder_data) {
            $estimate_id = $workorder_data->estimate_id;
        } elseif ($invoice_data) {
            $estimate_id = $invoice_data->estimate_id;
        }

        $estimate_data = EstimateModel::where('estimate_id', '=', $estimate_id)->first();
        if (!$estimate_data) {
            return $this->response(['status' => 'error', 'error' => 'Incorrect Estimate'], 422);
        }

        $client_data = Client::where('client_id', '=', $estimate_data->client_id)->with('primary_contact')->first();
        $client_contact = $client_data->primary_contact;

        $fee_percent = 0;
        $fee = 0;

        $paymentData = [];

        if ($method == config_item('default_cc')) {
            if (isset($extra['is_client']) && $extra['is_client']) {
                if ($invoice_data && $invoice_data->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT) {
                    return $this->response(['status' => 'error', 'error' => 'Credit Card payment limit is full!'], 422);
                } elseif (!$invoice_data && $estimate_data->paid_by_cc >= _CC_MAX_CLIENT_PAY_COUNT) {
                    return $this->response(['status' => 'error', 'error' => 'Credit Card payment limit is full!'], 422);
                }
            }

            if ($this->input->post('token') && $this->input->post('crd_name')) {
                $paymentData = [
                    'token' => $this->input->post('token'),
                    'name' => $this->input->post('crd_name'),
                    'additional' => $this->input->post('additional')
                ];
            } else {
                return $this->response(['status' => 'error', 'error' => 'Incorrect Credit Card data'], 422);
            }

            $fee_percent = round((float)config_item('cc_extra_fee'), 2);
            if ($fee_percent > 0) {
                $fee = round($amount * ($fee_percent / 100), 2);
                $amount += $fee;
            }
        }

        $file = false;
        if (isset($_FILES) && isset($_FILES['payment_file']) && isset($_FILES['payment_file']['error']) && $_FILES['payment_file']['error'] === 0) {
            $file = $this->arboStarProcessing->uploadFile([
                'client_id' => $client_data->client_id,
                'estimate_id' => $estimate_data->estimate_id,
                'estimate_no' => $estimate_data->estimate_no,
                'invoice_no' => !empty($invoice_data) ? $invoice_data->invoice_no : null,
                'lead_id' => $estimate_data->lead_id
            ]);
            if (!$file && !empty($this->arboStarProcessing->uploadFileError)) {
                return $this->response(['status' => 'error', 'error' => $this->arboStarProcessing->uploadFileError],
                    422);
            }
        }
        if (!$user_id) {
            $user_id = isset($this->session->userdata['user_id']) ? $this->session->userdata['user_id'] : 0;
        }

        $iData = [
            'client' => $client_data,
            'contact' => $client_contact,
            'estimate' => $estimate_data,
            'workorder' => $workorder_data,
            'invoice' => $invoice_data,
            'type' => $this->input->post('type') ?: 'deposit',
            'payment_driver' => $client_data->client_payment_driver,
            'amount' => $amount,
            'fee' => $fee,
            'fee_percent' => $fee_percent,
            'tips' => $tips,
            'file' => $file,
            'user_id' => $user_id
        ];

        if ($extra) {
            $iData['extra'] = $extra;
        }

        try {
            $result = $this->arboStarProcessing->pay($method, $iData, $paymentData);
        } catch (Exception $e) {
            return $this->response([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 422);
        }
        return $this->response([
            'status' => 'ok',
            'amnt' => $result['payment_amount'],
            'file' => $result['payment_file'],
            'total' => $result['total'],
            'thanks' => isset($result['thanks']) ? $result['thanks'] : '',
            'message' => 'Payment has been added successfully'
        ], 200);
    }

    /**
     * @param $hash
     * @return void
     */
    public function getCCPaymentForm($hash)
    {
        $estimateModel = EstimateModel::select(EstimateModel::BASE_FIELDS)
            ->where(DB::raw('MD5(CONCAT(estimates.estimate_no, estimates.client_id))'), '=', $hash)
            ->with([
                'invoice' => function ($query) {
                    return $query->with('status');
                },
                'client' => function ($query) {
                    return $query->with('primary_contact');
                },
                'workorder',
                'lead'
            ]);

        if (!$hash || is_null($estimateModel->first())) {
            return $this->response([
                'message' => 'Not found'
            ], 404);
        }

        $estiamteResult = $estimateModel->first();

        if (isset($estiamteResult->workorder) && $estiamteResult->workorder) {
            $estimateModel->withTotals(null,
                ['estimates.estimate_id' => $estiamteResult->estimate_id]);
            $estiamteResult = $estimateModel->first();
            $totalEstimateBalance = $estiamteResult->total_due;
        } else {
            $totalEstimateBalance = (new EstimateModel())->totalEstimateBalance($estiamteResult->estimate_id)->first()->total_due ?? null;
        }

        $type = 'invoice';
        if (empty($estimate['invoice'])) {
            $type = 'deposit';
        }
        $client = Client::getWithContact($estiamteResult->client->client_id);

        $data['payment_type'] = $type;
        $data['client_data'] = $client->toArray();
        $data['lead_data'] = $estiamteResult->lead;
        $data['client_contact'] = $estiamteResult->client->primary_contact;
        $data['invoice'] = $estiamteResult->invoice;
        $data['estimate'] = $estiamteResult;
        $data['estimateConfirmed'] = $estiamteResult->estimate_status->est_status_confirmed === '1' ? true : false;
        $data['workorder'] = empty($estiamteResult->workorder) ? false : $estiamteResult->workorder;
        $data['estimate_balance'] = is_null($totalEstimateBalance) ? 0 : $totalEstimateBalance;
        $data['invoice_paid_status'] = InvoiceStatus::where('completed', '=', 1)->first();

        $data['billingData'] = [
            'customer_id' => $client->client_id,
            'name' => $client->client_name,
            'address' => $client->client_address,
            'city' => $client->client_city,
            'state' => $client->client_state,
            'zip' => $client->client_zip,
            'country' => $client->client_country,
            'phone' => $client->primary_contact->cc_phone_clean ?? null,
            'email' => $client->primary_contact->cc_email ?? null
        ];

        $card_form = '';
        if (config_item('processing')) {
            $this->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
            if ($data['client_data']['client_payment_driver']) {
                $this->arboStarProcessing->setAdapter($data['client_data']['client_payment_driver']);
            }
            $card_form = $this->arboStarProcessing->getPortalClientCardForm(
                $data,
                $data['client_data']['client_payment_driver'],
                'portal_form'
            );
            header('Content-type: text/html');
        }
        echo $card_form;
    }
}
