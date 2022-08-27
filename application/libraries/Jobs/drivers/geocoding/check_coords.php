<?php

use application\modules\clients\models\Client;
use application\modules\estimates\models\EstimateStatus;
use application\modules\invoices\models\InvoiceStatus;
use application\modules\leads\models\Lead;
use application\modules\leads\models\LeadStatus;
use application\modules\workorders\models\WorkorderStatus;

class check_coords extends CI_Driver implements JobsInterface
{
    private $payload;
    private $body;
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('Googlemaps');
        $this->CI->googlemaps->geocodeCaching = true;
    }

    public function getPayload($data = NULL)
    {
        return $data;
    }

    public function execute($job = NULL)
    {
        pushJob('geocoding/check_coords', true, strtotime('tomorrow'));
        $this->updateClientsCoords();
        $this->updateLeadsCoords();
        
        $leadStatuses = $this->getLeadStatuses();
        $estimateStatuses = $this->getEstimateStatuses();
        $workOrderStatuses = $this->getWorkOrderStatuses();
        $invoiceStatuses = $this->getInvoiceStatuses();

        $result = $this->getClientsLeadsWithoutCoords($leadStatuses, $estimateStatuses, $workOrderStatuses, $invoiceStatuses);

        if (!empty($result) && is_array($result)) {
            foreach ($result as $key => $val) {
                $geocode = [];
                if ((empty($val->client_lng) || empty($val->client_lat)) && !empty($val->client_address) && !empty($val->client_id)) {
                    $address = $this->getAddress($val->client_address, $val->client_city, $val->client_state, $val->client_zip, $val->client_country);
                    $geocode = $this->CI->googlemaps->get_lat_long_from_address($address);

                    if (!empty($geocode) && is_array($geocode)) {
                        $client = Client::find($val->client_id);
                        if (!empty($client)) {
                            $client->client_lat = $geocode[0] ?: 0.1;
                            $client->client_lng = $geocode[1] ?: 0.1;
                            $client->save();
                        }
                    }

                }
                if ((empty($val->longitude) || empty($val->latitude)) && !empty($val->lead_address) && !empty($val->lead_id)) {
                    if ($val->lead_address != $val->client_address) {
                        $address = $this->getAddress($val->lead_address, $val->lead_city, $val->lead_state, $val->lead_zip, $val->lead_country);
                        $geocode = $this->CI->googlemaps->get_lat_long_from_address($address);
                    }
                    if (!empty($geocode) && is_array($geocode)) {
                        $lead = Lead::find($val->lead_id);
                        if (!empty($lead)) {
                            $lead->latitude = $geocode[0] ?: 0.1;
                            $lead->longitude = $geocode[1] ?: 0.1;
                            $lead->save();
                        }
                    }
                }
            }
        }
        return true;
    }

    private function getAddress($address, $city = '', $state = '', $zip = '', $country = '')
    {
        if (!empty($city))
            $address .= ', ' . $city;
        if (!empty($state))
            $address .= ', ' . $state;
        if (!empty($zip))
            $address .= ', ' . $zip;
        if (!empty($country))
            $address .= ', ' . $country;
        return $address;
    }

    private function updateClientsCoords()
    {
        DB::table('clients')
            ->join('leads', 'clients.client_id', '=', 'leads.client_id')
            ->where(function ($query) {
                $query->where('leads.latitude', '!=', '0')
                    ->orWhere('leads.longitude', '!=', '0')
                    ->orWhere('leads.latitude', '!=', '')
                    ->orWhere('leads.longitude', '!=', '')
                    ->orWhere('leads.latitude', '!=', null)
                    ->orWhere('leads.longitude', '!=', null);
            })
            ->where(function ($query) {
                $query->where('clients.client_lat', '0')
                    ->orWhere('clients.client_lng', '0')
                    ->orWhere('clients.client_lat', '')
                    ->orWhere('clients.client_lng', '')
                    ->orWhere('clients.client_lat', null)
                    ->orWhere('clients.client_lng', null);
            })
            ->where('clients.client_address', DB::raw('leads.lead_address'))
            ->groupBy('clients.client_id')
            ->update([
                'clients.client_lng' => DB::raw('leads.longitude'),
                'clients.client_lat' => DB::raw('leads.latitude')
            ]);
    }

    private function updateLeadsCoords()
    {
        DB::table('leads')
            ->join('clients', 'clients.client_id', '=', 'leads.client_id')
            ->where(function ($query) {
                $query->where('leads.latitude', '0')
                    ->orWhere('leads.longitude', '0')
                    ->orWhere('leads.latitude', '')
                    ->orWhere('leads.longitude', '')
                    ->orWhere('leads.latitude', null)
                    ->orWhere('leads.longitude', null);
            })
            ->where(function ($query) {
                $query->where('clients.client_lat', '!=', '0')
                    ->orWhere('clients.client_lng', '!=', '0')
                    ->orWhere('clients.client_lat', '!=', '')
                    ->orWhere('clients.client_lng', '!=', '')
                    ->orWhere('clients.client_lat', '!=', null)
                    ->orWhere('clients.client_lng', '!=', null);
            })
            ->where('clients.client_address', DB::raw('leads.lead_address'))
            ->update([
                'leads.longitude' => DB::raw('clients.client_lng'),
                'leads.latitude' => DB::raw('clients.client_lat')
            ]);
    }

    private function getClientsLeadsWithoutCoords($leadStatuses, $estimateStatuses, $workOrderStatuses, $invoiceStatuses)
    {
        return DB::table('clients')
            ->join('leads', 'clients.client_id', '=', 'leads.client_id')
            ->leftJoin('estimates', 'leads.lead_id', '=', 'estimates.lead_id')
            ->leftJoin('workorders', 'estimates.estimate_id', '=', 'workorders.estimate_id')
            ->leftJoin('invoices', 'estimates.estimate_id', '=', 'invoices.estimate_id')
            ->where(function ($query) use ($leadStatuses, $estimateStatuses, $workOrderStatuses, $invoiceStatuses) {
                $query->where(function ($query) use ($leadStatuses) {
                    $query->whereNotIn('leads.lead_status_id', $leadStatuses)
                        ->where('estimates.estimate_id', null);
                })
                    ->orWhere(function ($query) use ($estimateStatuses) {
                        $query->whereNotIn('estimates.status', $estimateStatuses)
                            ->where('workorders.estimate_id', null);
                    })
                    ->orWhere(function ($query) use ($workOrderStatuses) {
                        $query->whereNotIn('workorders.wo_status', $workOrderStatuses)
                            ->where('invoices.estimate_id', null);
                    })
                    ->orWhereNotIn('invoices.in_status', $invoiceStatuses);
            })
            ->where(function ($query) {
                $query->where('clients.client_lat', '0')
                    ->orWhere('clients.client_lng', '0')
                    ->orWhere('clients.client_lat', '')
                    ->orWhere('clients.client_lng', '')
                    ->orWhere('clients.client_lat', null)
                    ->orWhere('clients.client_lng', null)
                    ->orWhere('leads.latitude', '0')
                    ->orWhere('leads.longitude', '0')
                    ->orWhere('leads.latitude', '')
                    ->orWhere('leads.longitude', '')
                    ->orWhere('leads.latitude', null)
                    ->orWhere('leads.longitude', null);
            })
            ->get([
                'clients.client_id',
                'clients.client_lat',
                'clients.client_lng',
                'clients.client_address',
                'clients.client_city',
                'clients.client_state',
                'clients.client_zip',
                'clients.client_country',
                'leads.lead_id',
                'leads.longitude',
                'leads.latitude',
                'leads.lead_address',
                'leads.lead_city',
                'leads.lead_state',
                'leads.lead_zip',
                'leads.lead_country'
            ])
            ->toArray();
    }

    private function getLeadStatuses()
    {
        return LeadStatus::where('lead_status_declined', 1)
            ->orWhere('lead_status_estimated', 1)
            ->orWhere('lead_status_id', 5)
            ->pluck('lead_status_id')
            ->toArray();
    }

    private function getEstimateStatuses()
    {
        return EstimateStatus::where('est_status_declined', 1)
            ->orWhere('est_status_confirmed', 1)
            ->pluck('est_status_id')
            ->toArray();
    }

    private function getWorkOrderStatuses()
    {
        return WorkorderStatus::where('is_finished', 1)
            ->pluck('wo_status_id')
            ->toArray();
    }

    private function getInvoiceStatuses()
    {
        return InvoiceStatus::where('completed', 1)
            ->pluck('invoice_status_id')
            ->toArray();
    }
}