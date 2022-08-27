<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\brands\models\Brand;
use application\modules\estimates\models\Estimate as EstimateModel;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\workorders\models\Workorder;
use application\modules\invoices\models\Invoice;
use application\modules\clients\models\Client;
use Illuminate\Validation\ValidationException;

class Estimate extends Portal_Controller
{

    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * @param string $hash
     * @return EstimateModel|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getEstimateByHash(string $hash)
    {
        return EstimateModel::where(
            DB::raw('MD5(CONCAT(estimates.estimate_no, estimates.client_id))'), '=', $hash)
            ->first();
    }

    /**
     * @param $hash
     * @return void
     */
    public function getEstimatePdf($hash)
    {
        $estimateModel = $this->getEstimateByHash($hash);
        if (is_null($estimateModel)) {
            return $this->response([
                'status' => false,
                'message' => 'Invalid estimate'
            ], 400);
        }
        $this->load->library('Common/EstimateActions', $estimateModel->estimate_id);
        $this->estimateactions->setEstimateId($estimateModel->estimate_id);
        $this->estimateactions->showPDF();
    }

    /**
     * @param $hash
     * @return void
     */
    public function getInvoicePdf($hash)
    {
        $estimateModel = EstimateModel::where(
            DB::raw('MD5(CONCAT(estimates.estimate_no, estimates.client_id))'), '=', $hash)
            ->with(['invoice'])->first();

        if (is_null($estimateModel) || is_null($estimateModel->invoice)) {
            return $this->response(['status' => false, 'message' => 'Invalid data'], 400);
        }

        $this->load->library('Common/InvoiceActions', ['invoice_id' => $estimateModel->invoice->id]);
        $this->invoiceactions->invoice_pdf();
    }

    /**
     * @param $hash
     * @return void
     */
    public function sign($hash)
    {
        $estimateModel = EstimateModel::where(
            DB::raw('MD5(CONCAT(estimates.estimate_no, estimates.client_id))'), '=', $hash)
            ->with([
                'invoice' => function ($query) {
                    return $query->with('status');
                },
                'client',
            ])->first();

        if (!$hash || is_null($estimateModel)) {
            return $this->response([
                'message' => 'Not found'
            ], 404);
        }
        $this->load->library('Common/EstimateActions', $estimateModel->estimate_id);
        $this->estimateactions->setEstimateId($estimateModel->estimate_id);
        $this->estimateactions->setIsConfirmedWeb(false);

        $signature = $this->input->post('signature');

        $isSigned = $this->estimateactions->sign($signature, false);
        if ($isSigned) {

            $this->estimateactions->confirm('Signature');
            $this->estimateactions->sendConfirmedCompanyNotificationWithPdf();
            $this->estimateactions->sendConfirmed();

            $note = $this->input->post('note');
            if (isset($note) && !empty($note)) {
                $workorder = $estimateModel->workorder;
                $workorder->wo_office_notes = "Client's notes:" . htmlentities($note, ENT_QUOTES, "UTF-8") . " ; " . $workorder->wo_office_notes;
                $workorder->save();
            }

            return $this->response([
                'status' => true,
                'message' => 'Confirmation success!'
            ], 200);
        }

        return $this->response([
            'status' => false,
            'message' => 'Confirmation Error'
        ], 200);
    }

    /**
     * @param $hash
     * @return void
     */
    public function get($hash = null)
    {
        $estimate = $this->getEstimateByHash($hash);
        if (!$hash || is_null($estimate)) {
            return $this->response([
                'message' => 'Not found'
            ], 404);
        }

        $estimateModel = EstimateModel::portalFields()->where(
            'estimates.estimate_id', '=', $estimate->estimate_id)
            ->withTotals(null, ['estimates.estimate_id' => $estimate->estimate_id])
            ->with([
                'user' => function ($query) {
                    return $query->portalFields();
                },
                'estimates_service' => function ($query) {
                    return $query->baseFields()->with([
                        'service',
                        'equipments',
                        'crew',
                        'bundle.estimate_service.service'
                    ])->withoutBundleServices();
                },
                'estimate_status',
                'invoice' => function ($query) {
                    return $query->portalFields()->with(['status']);
                },
                'client' => function ($query) {
                    return $query->portalFields();
                }
            ]);
        $estiamteResult = $estimateModel->first();
        $estiamteResult->setAppends(['files']);
        $signedDate = '';
        if (is_bucket_file('uploads/clients_files/' . $estiamteResult->client_id . '/estimates/' . $estiamteResult->estimate_no . '/signature.png')) {
            if (isset($estiamteResult->workorder)) {
                $signedDate = getDateTimeWithDate($estiamteResult->workorder->date_created, 'Y-m-d');
            }
        }

        $estimate = $estiamteResult->toArray();

        $estimate['signature_date'] = $signedDate;
        $estimate['sum'] = $estimate['sum_taxable'] + $estimate['sum_non_taxable'];
        unset($estimate['sum_taxable']);
        unset($estimate['sum_non_taxable']);

        $brand_id = get_brand_id($estimate, $estimate['client']);
        $brand = Brand::find($brand_id);

        $brandPhone = '';
        $brandEmail = '';
        if (isset($brand->contact)) {
            $brandPhone = numberTo($brand->contact->bc_phone) ?? '';
            $brandEmail = $brand->contact->bc_email ?? '';
        }
        $estimate['brand'] = [
            'b_estimate_terms' => get_estimate_terms($brand_id) ?? '',
            'b_payment_terms' => $brand->b_payment_terms ?? '',
            'main_logo' => $brand->mainLogo ?? '',
            'payment_logo' => isset(config_item('brands')[$brand_id]->images['payment_logo_file']['url']) ? base_url(config_item('brands')[$brand_id]->images['payment_logo_file']['url']) : $brand->mainLogo,
            'name' => $brand->b_name ?? '',
            'address' => $brand->b_company_address ?? '',
            'city' => $brand->b_company_city ?? '',
            'state' => $brand->b_company_state ?? '',
            'email' => $brandEmail,
            'phone' => $brandPhone
        ];

        return $this->response($estimate, 200);
    }

    /**
     * @param $hash
     * @return array|void
     */
    public function setNewServicesStatus($hash)
    {
        $estimateModel = $this->getEstimateByHash($hash);
        try {
            $fields = request()->validate([
                'confirm' => "required|boolean",
                'service_id' => "required|integer",
            ]);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $estimatesServices = EstimatesService::where('estimate_id', $estimateModel->estimate_id)->get();

        if (!is_null($estimatesServices)) {
            foreach ($estimatesServices as $estimatesService) {
                if ($estimatesService->id == $fields['service_id']) {
                    $estimatesService->setAttribute(
                        'service_status',
                        $fields['confirm'] == 1 ? Service::SERVICE_STATUS_NEW : Service::SERVICE_STATUS_DECLINED
                    );
                    $estimatesService->save();
                }
            }
        }

        $estimate = EstimateModel::where(
            DB::raw('MD5(CONCAT(estimates.estimate_no, estimates.client_id))'), '=', $hash)
            ->withTotals(null, ['estimates.estimate_id' => $estimateModel->estimate_id])->first();

        return $this->successResponse([
            'data' => [
                'discount_total' => $estimate->discount_total,
                'sum_actual_without_tax' => $estimate->sum_actual_without_tax,
                'total_tax' => $estimate->total_tax,
                'total_with_tax' => $estimate->total_with_tax,
                'tax_value' => $estimate->tax_value,
                'payments_total' => $estimate->payments_total,
                'sum_for_services' => $estimate->sum_for_services,
                "sum" => $estimate->sum_taxable + $estimate->sum_non_taxable,
                "total_due" => $estimate->total_due,
            ]
        ], 'Changed successfully', 200);
    }
}
