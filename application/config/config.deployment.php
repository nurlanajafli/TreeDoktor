<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['composer_autoload'] = FCPATH . 'vendor/autoload.php';
$config['migration_table'] = 'new_migrations';

$segments = array();
$config['base_url'] = "";
if (isset($_SERVER['REQUEST_URI'])) {
    $segments = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
}

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $protocol = 'https';
}

if (isset($_SERVER['HTTP_HOST'])) {
    $config['base_url'] = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/";
} else {
    $config['base_url'] = "https://{{service.domain}}/";
}

$config['index_page'] = '';

$config['uri_protocol'] = 'AUTO';

$config['url_suffix'] = '';

$config['language'] = 'english';

$config['charset'] = 'UTF-8';

$config['enable_hooks'] = true;

$config['subclass_prefix'] = 'MY_';

$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-?=(),+';

$config['allow_get_array'] = true;
$config['enable_query_strings'] = false;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd'; // experimental not currently in use

$config['log_threshold'] = 4;

$config['log_path'] = '';

$config['log_date_format'] = 'Y-m-d H:i:s';

$config['cache_path'] = '';

$config['encryption_key'] = 'gister';

$config['sess_driver'] = 'database';
$config['sess_regenerate_destroy'] = true;
$config['sess_cookie_name'] = '{{service.id}}_session';
$config['sess_expiration'] = 18000;
//$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_table_name'] = 'ci_sessions';
$config['sess_match_ip'] = false;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = false;

$config['cookie_prefix'] = "";
$config['cookie_domain'] = "";
$config['cookie_path'] = "/";
$config['cookie_secure'] = false;

$config['global_xss_filtering'] = true;

$config['csrf_protection'] = false;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;

$config['compress_output'] = false;

$config['time_reference'] = 'gmt';

$config['rewrite_short_tags'] = false;

$config['proxy_ips'] = '';

$config['global_xss_filtering_disabled'] = array('administration/ajax_save_template', 'clients/ajax_send_email');

$config['service_email_address'] = '729138097020-kd8mmg4ahah5u3bnqnefrcjo2v3hro6j@developer.gserviceaccount.com';
$config['service_cert_path'] = './uploads/treedoctors-c6f443f9efc0.p12';
$config['account_email_address'] = '{{consumer.email.billing.address}}';
$config['office_address'] = '{{consumer.office.street}}';
$config['office_region'] = '{{consumer.office.region}}';
$config['office_city'] = '{{consumer.office.city}}';
$config['office_state'] = '{{consumer.office.state}}';
$config['office_zip'] = '{{consumer.office.zip}}';
$config['office_address_map'] = '{{consumer.office.map}}';
$config['office_description'] = '{{consumer.office.description}}';

$config['office_country'] = '{{consumer.office.country}}';
$config['office_phone'] = '{{consumer.office.phone.number}}';
$config['office_phone_mask'] = '{{consumer.office.phone.mask}}';
$config['office_fax'] = '{{consumer.office.fax.number}}';
$config['company_site'] = 'https://{{consumer.site.address}}/';
$config['company_site_http'] = 'http://{{consumer.site.address}}/';
$config['company_site_name'] = '{{consumer.site.shortname}}';
$config['company_site_name_upper'] = '{{consumer.site.formatted}}';

$config['footer_pdf_address'] = {{consumer.pdf.footer}};
$config['per_page_notes'] = 200;

$config['smtp_domains'] = [];
// $config['smtp_domains'] = ['hotmail.com', 'live.com', 'live.ca', 'msn.com', 'live.ru', 'outlook.com'];
// $config['smtp_mail']['protocol'] = 'smtp';
// $config['smtp_mail']['mailtype'] = 'html';
// $config['smtp_mail']['smtp_host'] = 'ssl://smtp.gmail.com';
// $config['smtp_mail']['smtp_port'] = '465';
$config['smtp_mail']['smtp_user'] = '{{crm.smtp.user}}';
// $config['smtp_mail']['smtp_pass'] = '53bhUCG92';
$config['gmaps_geocoding_key'] = '{{crm.geo.key}}';

$config['gmaps_key'] = '{{crm.gmaps.key}}';

$config['refferenced_by'] = [{{crm.leads.referenced_by}}];

$config['followup_modules'] = [
    'leads' => [
        'name' => 'Leads',
        'number' => 1,
        'id_field_name' => 'lead_id',
        'no_field_name' => 'lead_no',
        'action_name' => 'edit',
        'status_field_name' => 'lead_status_id',
        'reasons' => [],
        'statuses' => [
            '1' => 'New',
            '2' => 'For Approval',
            '5' => 'Already Done',
            '3' => 'No Go',
            '4' => 'Estimated',
        ],
    ],
    'estimates' => [
        'name' => 'Estimates',
        'number' => 2,
        'id_field_name' => 'estimate_id',
        'no_field_name' => 'estimate_no',
        'action_name' => 'profile',
        'status_field_name' => 'status',
        'reasons' => [],
        'statuses' => [
            '1' => 'New',
            '2' => 'Sent for approval',
            '3' => 'Pending approval',
            '4' => 'Declined',
            '6' => 'Confirmed',
            '7' => 'Contact the client',
            '8' => 'Thinking- No Follow Up Needed',
        ],
    ],
    'invoices' => [
        'name' => 'Invoices',
        'number' => 3,
        'id_field_name' => 'id',
        'no_field_name' => 'invoice_no',
        'action_name' => 'profile',
        'status_field_name' => 'in_status',
        'reasons' => [],
        'statuses' => [
            '1' => 'Issued',
            '3' => 'Sent',
            '2' => 'Overdue',
            '4' => 'Paid',
            '5' => 'Hold Backs',
        ],
    ],
    'schedule' => [
        'name' => 'Schedule Event',
        'number' => 4,
        'id_field_name' => 'id',
        'no_field_name' => 'workorder_no',
        'action_name' => 'profile',
        'status_field_name' => 'wo_status',
        'reasons' => [],
        'statuses' => [
            '7' => 'Scheduled - Confirmed',
            '48' => 'Scheduled - Confirmed today only'
        ],
    ],
    'client_tasks' => [
        'name' => 'Client Tasks',
        'number' => 5,
        'id_field_name' => 'task_id',
        'no_field_name' => 'task',
        'action_name' => '',
        'status_field_name' => 'task_status',
        'reasons' => [],
        'statuses' => [
            '2' => 'Construction Arborist Report',
            '3' => 'Regular Arborist Report',
            '4' => 'Exemption',
            '5' => 'Payment Follow Up Visit',
            '6' => 'Repair Assessment',
            '7' => 'Meeting with a client',
            '8' => 'Secondary visit.',
            '9' => 'Follow up call',
            '10' => 'Office Related Task',
            '11' => 'Arborist Supervision'
        ],
    ],
    'employees' => [
        'name' => 'Employees',
        'number' => 5,
        'id_field_name' => '',
        'no_field_name' => '',
        'action_name' => '',
        'status_field_name' => '',
        'reasons' => [],
        'statuses' => [],
    ],
    'users' => [
        'name' => 'Users',
        'number' => 6,
        'id_field_name' => '',
        'no_field_name' => '',
        'action_name' => '',
        'status_field_name' => '',
        'reasons' => [],
        'statuses' => [],
    ],
];
// Auto tax for US company
$config['auto_tax_url'] = '{{crm.autotax.url}}';//https://ws.serviceobjects.com/ft/web.svc/JSON/GetTaxInfoByCityCountyState?
$config['auto_tax_key'] = '{{crm.autotax.key}}';//WS19-MLX1-HGW1
$config['auto_tax_type'] = 'sales';

// QB sandbox
$config['qb_mode'] = '{{crm.qb.mode}}';
$config['qb_base_url'] = '{{crm.qb.url}}';

$config['default_signature'] = '';
/* End of file config.php */
/* Location: ./application/config/config.php */
