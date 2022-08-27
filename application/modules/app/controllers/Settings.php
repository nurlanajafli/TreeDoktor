<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
use application\modules\brands\models\Brand;
use application\modules\brands\models\BrandContact;
use application\modules\brands\models\BrandImage;
use application\modules\classes\models\QBClass;
use application\modules\references\models\Reference;
use application\modules\crew\models\Crew;

class Settings extends APP_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    private function _settings() {
        $brand_id = default_brand();

        $this->load->config('upload');
        return [
            'base_url' => base_url(),
            'port' => config_item('externalWsPort') ?? '8895',
            'company_name' => brand_name($brand_id, true),
            'timezone' => date('e'),
            'max_file_size' => config_item('max_size'),
            'office_lat' => brand_office_lat($brand_id),
            'office_lon' => brand_office_lon($brand_id),
            'office_address' => brand_office_address($brand_id),
            'office_region' => brand_office_region($brand_id),
            'office_city' => brand_office_city($brand_id),
            'office_state' => brand_office_state($brand_id),
            'office_zip' => brand_office_zip($brand_id),
            'office_country' => brand_office_country($brand_id),
            'autocomplete_restriction' => config_item('autocomplete_restriction'),
            'tax_rate' => config_item('tax_rate'),
            'tax_name' => config_item('tax_name'),
            'taxes' => all_taxes(),
            'default_tax' => getDefaultTax(),
            'overhead_rate' => config_item('service_overhead_rate'),
            'gmaps_key' => config_item('gmaps_app_key') ?: config_item('gmaps_key'),
            'logo' => base_url(get_brand_logo($brand_id, 'main_logo_file', '/assets/' . $this->config->item('company_dir') . '/img/logo.png')),
            'payment_methods' => config_item('payment_methods'),
            'processing' => config_item('processing'),
            'default_cc' => config_item('default_cc'),
            'date_format' => getJSDateFormatForApp(),
            'time_format' => getIntTimeFormat(),
            'currency_symbol' => (config_item('currency_symbol'))?config_item('currency_symbol'):'$',
            'currency_template' => (config_item('currency_symbol_position')) ? str_replace(" ", "", config_item('currency_symbol_position')) : '{currency}{amount}',
            'currency_digit_separator' => ',',
            'display_tax_in_estimate' => config_item('display_tax_in_estimate'),
            'keys' => [
                'web' => config_item('app_ios_key'),
                'ios' => config_item('app_ios_key'),
                'android' => config_item('app_android_key')
            ],
            'request_id_form_endpoint' => 'https://arbostar.arbostar.com/api/formRequest',
            'out_of_date' => false,
            'update_available' => $this->_checkUpdate(),
            'phone_mask' => config_item('phone_inputmask'),
            'phone_prefix' => config_item('phone_preview_prefix') ? config_item('phone_country_code') : null,
            'phone_clean_length' => config_item('phone_clean_length') ?? 10,
            'appointment_task_length' => config_item('AppointmentTaskLength'),
            'auto_tax' => config_item('auto_tax'),
            'messenger' => config_item('messenger'),
        ];
    }

    function index() {
        return $this->response($this->_settings());
    }

    function configurations() {
        $this->load->model('mdl_est_status');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_leads_reason');
        $this->load->model('mdl_settings_orm');
        $this->load->model('mdl_services');

        $result = $this->_settings();

        $workorderStatusesCollection = \application\modules\workorders\models\WorkorderStatus::orderBy('wo_status_priority', 'asc')
            ->get();
        $result['workorder_statuses'] = array_values($workorderStatusesCollection->where('wo_status_active', 1)->toArray());
        $result['workorder_disabled_statuses'] = array_values($workorderStatusesCollection->where('wo_status_active', 0)->toArray());

        $invoiceStatusesCollection = \application\modules\invoices\models\InvoiceStatus::orderBy('priority', 'asc')
            ->get();
        $result['invoice_statuses'] = array_values($invoiceStatusesCollection->where('invoice_status_active', '1')->toArray());
        $result['invoice_disabled_statuses'] = array_values($invoiceStatusesCollection->where('invoice_status_active', '0')->toArray());

        $estimateStatusesCollection = \application\modules\estimates\models\EstimateStatus::orderBy('est_status_id', 'asc')
            ->get();
        $result['estimate_statuses'] = array_values($estimateStatusesCollection->where('est_status_active', 1)->toArray());
        array_unshift($result['estimate_statuses'], [
            'est_status_id' => 0,
            'est_status_name' => 'All Estimates',
            'est_status_active' => '0',
            'est_status_declined' => '0',
            'est_status_default' => '0',
            'est_status_confirmed' => '0',
            'est_status_sent' => '0',
            'est_status_priority' => '0'
        ]);
        $result['estimate_disabled_statuses'] = array_values($estimateStatusesCollection->where('est_status_active', 0)->toArray());

        $estimateDeclineReasons = \application\modules\estimates\models\EstimateReasonStatus::get();
        $result['estimate_declined_reasons'] = array_values($estimateDeclineReasons->toArray());

        $result['lead_statuses'] = $this->mdl_leads_status->get_many_by([
            'lead_status_estimated' => 0,
            'lead_status_for_approval' => 0,
            'lead_status_active' => 1,
        ]);
        $result['lead_declined_reasons'] = $this->mdl_leads_reason->get_many_by([
            'reason_active' => 1
        ]);

        $result['task_categories'] = $this->mdl_client_tasks->get_task_categories();
        $result['brands'] = Brand::withTrashed()->orderBy('deleted_at')->get();


        // QB classes
        $result['classes'] = [];
        $classes = QBClass::where([['class_parent_id', null], 'class_active' => 1])->with('classesWithoutInactive')->get();
        if(!empty($classes)) {
            $result['classes'] = getClasses($classes->toArray());
            deleteEmptyChildrenFromArray($result['classes']);
        }
        // check QB connection
        $result['qb_connection'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value ? 1 : 0;

        // favourite icons
        $itemsWithIcons = DB::table('services')->where([['service_is_favourite', '!=', 0],['service_favourite_icon', '!=', null], ['service_status', 1]])->orderBy('is_bundle')->orderBy('is_product')->get()->toArray();
        foreach ($itemsWithIcons as $item){
            if($item->is_bundle) {
                $bundle_records = $this->mdl_services->get_records_included_in_bundle($item->service_id);
                if ($bundle_records) {
                    foreach ($bundle_records as $record) {
                        $record->service_attachments = $this->getServiceAttachment($record->service_attachments);
                        $record->non_taxable = 0;
                        unset($record->service_qb_id);
                    }
                }
                $item->bundle_records = json_encode($bundle_records, true);
            } else
                $item->service_attachments = $this->getServiceAttachment($item->service_attachments);
            $className = '';
            if(!empty($item->service_class_id)){
                $class = QBClass::find($item->service_class_id);
                if(!empty($class))
                    $className = $class->class_name;
            }
            $item->service_class_name = $className;
        }
        $result['favourites'] = setFavouriteShortcut($itemsWithIcons);
        $result['client_references'] = array_values(Reference::getAllActive(['id', 'name'])->toArray());

        $result['crew_schedule_start'] = config_item('crew_schedule_start');
        $result['crew_schedule_end'] = config_item('crew_schedule_end');
        $result['crews'] = Crew::apiFields()->active()->noDayOff()->get();

        $result['actual_version'] = config_item('actual_version') ?: '1.18/3/15.12';
        $result['update_url'] = config_item('update_url') ?: 'http://167.86.112.191/app/';

        /****DEPRECATED AFTER 01.07.2022*******/
        $result['rewrite'] = ['JobsFetch' => true];
        /****DEPRECATED AFTER 01.07.2022*******/

        return $this->response($result);
    }

    function online() {

    }

    private function _checkUpdate() {
        if(!isset($this->version) || !$this->version) {
            return true;
        }

        if(!preg_match('/^([0-9]+\.[0-9]+\.[0-9]+).*?$/is', $this->version, $parseVersion)) {
            return true;
        }

        $versionSegments = explode('.', $parseVersion[1]);
        $appConfig = config_item('app');

        foreach ($versionSegments as $key => $segment) {
            if($appConfig['latest'][$key] > (int)$segment) {
                return true;
            } elseif ($appConfig['latest'][$key] < (int)$segment) {
                return false;
            }
        }

        return false;
    }

    private function getServiceAttachment($attachments){
        $new_att = [];
        if(isset($attachments)) {
            $i = 0;
            foreach (json_decode($attachments) as $attachment) {

                $transport = new stdClass();
                $tool = [];

                if (isset($attachment->vehicle_id)) {
                    $transport->vehicles['tool_id'] = $attachment->vehicle_id;
                }
                if (isset($attachment->vehicle_option)) {
                    $transport->vehicles['option'] = $attachment->vehicle_option;
                }

                if (isset($attachment->trailer_id)) {
                    $transport->trailers['tool_id'] = $attachment->trailer_id;
                }
                if (isset($attachment->trailer_option)) {
                    $transport->trailers['option'] = $attachment->trailer_option;
                }
                if (isset($attachment->tool_id) && isset($attachment->tools_option)) {
                    foreach ($attachment->tool_id as $key => $tool_id) {
                        $tool[$tool_id] = $attachment->tools_option[$key];
                    }
                }
                $new_att[$i] = ['transport' => $transport, 'tool' => $tool];
                $i++;
            }
        }
        return $new_att;
    }

}

