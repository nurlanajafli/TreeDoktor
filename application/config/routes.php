<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "login";
$route['404_override'] = '';

//to redirect the admin/user to the user module->admin controller
$route['admin/([a-zA-Z0-9_-]+)/(.+)'] = '$1/admin/$2';
$route['admin/([a-zA-Z0-9_-]+)'] = '$1/admin/index';

//dashboard tasks
$route['dashboard/todo/(.+)'] = 'dashboard/todo/$1';

//Client pagination
$route['clients/page'] = 'clients/index';
$route['clients/page/(:any)'] = 'clients/index/$1';
$route['clients/(:any)/page'] = 'clients/index/1/$1';
$route['clients/(:any)/page/(:any)'] = 'clients/index/$2/$1';
$route['clients/(:any)/(:any)/page/(:any)'] = 'clients/index/$3/$2/$1';
$route['client/(:num)'] = 'clients/details/$1';

$route['(:num)-L'] = 'leads/edit/$1';
//Estimate pagination
$route['(:num)-E'] = 'estimates/no/$1';
$route['(:num)-e'] = 'estimates/no/$1';
$route['(:num)-E/pdf'] = 'estimates/pdf/$1';
$route['(:num)-e/pdf'] = 'estimates/pdf/$1';
$route['estimates/page'] = 'estimates/index';
$route['estimates/page/(:any)'] = 'estimates/index/$1';

//Confirm schedule event
$route['confirm/(.+)'] = 'confirm/index/$1';

//Workorder pagination
$route['(:num)-W'] = 'workorders/no/$1';
$route['(:num)-w'] = 'workorders/no/$1';
$route['(:num)-W/pdf'] = 'workorders/pdf/$1';
$route['(:num)-w/pdf'] = 'workorders/pdf/$1';
$route['(:num)-W/pdf/(:any)'] = 'workorders/pdf/$1/$2';
$route['(:num)-w/pdf/(:any)'] = 'workorders/pdf/$1/$2';
$route['(:num)-W/(:any)'] = 'workorders/no/$1/$2';
$route['(:num)-w/(:any)'] = 'workorders/no/$1/$2';
$route['workorders/page'] = 'workorders/index';
$route['workorders/page/(:any)'] = 'workorders/index/$1';

//Invoice pagination
$route['(:num)-I'] = 'invoices/no/$1';
$route['(:num)-i'] = 'invoices/no/$1';
$route['(:num)-I/pdf'] = 'invoices/pdf/$1';
$route['(:num)-i/pdf'] = 'invoices/pdf/$1';
$route['(:num)-I(:any)'] = 'invoices/no/$1';
$route['(:num)-i(:any)'] = 'invoices/no/$1';
$route['(:num)-I(:any)/pdf'] = 'invoices/pdf/$1';
$route['(:num)-i(:any)/pdf'] = 'invoices/pdf/$1';
$route['invoices/page'] = 'invoices/index';
$route['invoices/page/(:any)'] = 'invoices/index/$1';

$route['invoices/(:num)'] = 'invoices/index/$1';
$route['invoices/(:num)/(:num)'] = 'invoices/index/$1/$2';
$route['invoices/(:num)/(:num)/(:num)'] = 'invoices/index/$1/$2/$3';
$route['invoices/(:num)/(:num)/(:num)/(:num)'] = 'invoices/index/$1/$2/$3/$4';
$route['invoices/(:num)/(:num)/(:num)/(:num)/(:num)'] = 'invoices/index/$1/$2/$3/$4/$5';

$route['invoices/overpaid'] = 'invoices/index/overpaid';
$route['invoices/overpaid/(:num)'] = 'invoices/index/overpaid/$1';
$route['invoices/overpaid/(:num)/(:num)/(:num)'] = 'invoices/index/overpaid/$1/$2/$4';

$route['callback/(.+)'] = 'webhook/Webhook/callback';

/** Portal */
$route['portal/api/settings'] = 'portal/SettingsPortal/get_settings';
$route['portal/api/estimate/(:any)'] = 'portal/Estimate/get/$1';
$route['portal/api/estimate/pdf/(:any)'] = 'portal/Estimate/getEstimatePdf/$1';
$route['portal/api/invoice/pdf/(:any)'] = 'portal/Estimate/getInvoicePdf/$1';
$route['portal/api/estimate/sign/(:any)'] = 'portal/Estimate/sign/$1';
$route['portal/api/estimate/payment_form/(:any)'] = 'portal/PaymentPortal/getCCPaymentForm/$1';
$route['portal/api/estimate/add_payment/(:any)/(:any)/(:any)'] = 'portal/PaymentPortal/addPayment/$1/$2/$3';
$route['portal/api/estimate/set_new_services_status/(:any)'] = 'portal/Estimate/setNewServicesStatus/$1';
$route['portal/(.+)'] = 'portal/Portal/index';
/** //Portal */

/*
$route['invoices/issued/(:any)'] = 'invoices/index/issued/$1';
$route['invoices/issued'] = 'invoices/index/issued/1';
$route['invoices/hold_backs/(:any)'] = 'invoices/index/hold_backs/$1';
$route['invoices/hold_backs'] = 'invoices/index/hold_backs/1';
$route['invoices/sent/(:any)'] = 'invoices/index/sent/$1';
$route['invoices/sent'] = 'invoices/index/sent/1';
$route['invoices/paid/(:any)'] = 'invoices/index/paid/$1';
$route['invoices/paid'] = 'invoices/index/paid/1';
$route['invoices/overdue/(:any)'] = 'invoices/index/overdue/$1';
$route['invoices/overdue'] = 'invoices/index/overdue/1';
*/
//Search Reasult
$route['globalSearch'] = 'dashboard/globalSearch';
$route['globalSearch/(:any)/page'] = 'dashboard/globalSearch/$1';
$route['globalSearch/(:any)/page/(:any)'] = 'dashboard/globalSearch/$1/$2';

//Workorder pagination
$route['payments'] = 'payments/index';
$route['payments/client_payment'] = 'payments/client_payment';
$route['payments/ajax_payment'] = 'payments/ajax_payment';
$route['payments/ajax_edit_payment'] = 'payments/ajax_edit_payment';
$route['payments/ajax_get_card_form'] = 'payments/ajax_get_card_form';
$route['payments/ajax_get_transaction_details'] = 'payments/ajax_get_transaction_details';
$route['payments/ajax_get_payment'] = 'payments/ajax_get_payment';
$route['payments/ajax_delete_payment'] = 'payments/ajax_delete_payment';
$route['payments/ajax_refund_payment'] = 'payments/ajax_refund_payment';
$route['payments/invoice/(:any)'] = 'payments/invoice/$1';
$route['payments/estimate/(:any)'] = 'payments/estimate/$1';
$route['payments/sign_estimate'] = 'payments/sign_estimate';
$route['payments/(:any)'] = 'payments/index/$1';


//Equipments pagination
$route['equipment'] = 'equipment/equipment';
$route['equipment/page/(:num)'] = 'equipment/equipment';

$route['equipment/map'] = 'equipment/map';
$route['equipment/map/(:any)'] = 'equipment/map/$1';
$route['equipment/map/(:any)/(:any)'] = 'equipment/map/$1/$2';

$route['equipment/(:num)'] = 'equipment/profile/$1';
$route['equipment/(:num)/(.+)'] = 'equipment/profile/$1';

$route['equipment/group/(:num)'] = 'equipment/index/$1';
$route['equipment/group/(:num)/page/(:num)'] = 'equipment/index/$1';

$route['equipment/groups'] = 'equipment/equipmentGroups/index';
$route['equipment/groups/page/(:num)'] = 'equipment/equipmentGroups/index/$1';
$route['equipment/groups/(:any)'] = 'equipment/equipmentGroups/$1';


$route['equipment/services'] = 'equipment/equipmentServices/index';
$route['equipment/services/page/(:num)'] = 'equipment/equipmentServices/index/$1';
$route['equipment/services/(:any)'] = 'equipment/equipmentServices/$1';

$route['equipment/service-reports'] = 'equipment/equipmentServiceReports/index';
$route['equipment/service-reports/page/(:num)'] = 'equipment/equipmentServiceReports/index/$1';
$route['equipment/service-reports/pdf/(:num)'] = 'equipment/equipmentServiceReports/pdf/$1';
$route['equipment/service-reports/(:any)'] = 'equipment/equipmentServiceReports/$1';

$route['equipment/repairs'] = 'equipment/equipmentRepairs/index';
$route['equipment/repairs/page/(:num)'] = 'equipment/equipmentRepairs/index/$1';
$route['equipment/repairs/pdf/(:num)'] = 'equipment/equipmentRepairs/pdf/$1';
$route['equipment/repairs/my'] = 'equipment/equipmentRepairs/my';
$route['equipment/repairs/my/page/(:num)'] = 'equipment/equipmentRepairs/my/$1';
$route['equipment/repairs/(:any)'] = 'equipment/equipmentRepairs/$1';

$route['equipment/parts/(:any)'] = 'equipment/equipmentParts/$1';
$route['equipment/notes/(:any)'] = 'equipment/equipmentNotes/$1';
$route['equipment/counters/(:any)'] = 'equipment/equipmentCounters/$1';
$route['equipment/files/(:any)'] = 'equipment/equipmentFiles/$1';

$route['equipment/settings'] = 'equipment/equipmentSettings/index';
$route['equipment/settings/(ajax_:any)'] = 'equipment/equipmentSettings/$1';
$route['equipment/settings/(:any)/page/(:num)'] = 'equipment/equipmentSettings/index/$1';
$route['equipment/settings/(:any)'] = 'equipment/equipmentSettings/index';

$route['equipment/sold'] = 'equipment/equipment/sold';
$route['equipment/sold/page/(:num)'] = 'equipment/sold';

//Soft twilio calls
$route['settings/integrations/twilio/workspace/(:any)/task_queue_save/(:any)'] = 'settings/integrations/twilio/TaskQueue/task_queue_save/$1/$2';
$route['settings/integrations/twilio/workspace/(:any)/task_queue_delete/(:any)'] = 'settings/integrations/twilio/TaskQueue/task_queue_delete/$1/$2';

$route['settings/integrations/twilio/application'] = 'settings/integrations/twilio/Application/index';
$route['settings/integrations/twilio/application/create'] = 'settings/integrations/twilio/Application/create';
$route['settings/integrations/twilio/application/update/(:any)'] = 'settings/integrations/twilio/Application/update/$1';
$route['settings/integrations/twilio/application/delete/(:any)'] = 'settings/integrations/twilio/Application/delete/$1';

$route['settings/integrations/twilio/active-numbers'] = 'settings/integrations/twilio/ActiveNumbers/index';
$route['settings/integrations/twilio/active-numbers/update/(:any)'] = 'settings/integrations/twilio/ActiveNumbers/update/$1';

$route['settings/integrations/twilio/workspace'] = 'settings/integrations/twilio/Workspace/index';
$route['settings/integrations/twilio/workspace/create'] = 'settings/integrations/twilio/Workspace/create';
$route['settings/integrations/twilio/workspace/update/(:any)'] = 'settings/integrations/twilio/Workspace/update/$1';
$route['settings/integrations/twilio/workspace/delete/(:any)'] = 'settings/integrations/twilio/Workspace/delete/$1';
$route['settings/integrations/twilio/workspace/overview/(:any)'] = 'settings/integrations/twilio/Workspace/overview/$1';
$route['settings/integrations/twilio/workspace/get_data_by_sid/(:any)'] = 'settings/integrations/twilio/Workspace/get_data_by_sid/$1';

$route['settings/integrations/twilio/workspace/(:any)/activity'] = 'settings/integrations/twilio/Activity/index/$1';
$route['settings/integrations/twilio/workspace/(:any)/activity/create'] = 'settings/integrations/twilio/Activity/create/$1';
$route['settings/integrations/twilio/workspace/(:any)/activity/update/(:any)'] = 'settings/integrations/twilio/Activity/update/$1/$2';
$route['settings/integrations/twilio/workspace/(:any)/activity/delete/(:any)'] = 'settings/integrations/twilio/Activity/delete/$1/$2';

$route['settings/integrations/twilio/workspace/(:any)/worker'] = 'settings/integrations/twilio/Worker/index/$1';
$route['settings/integrations/twilio/workspace/(:any)/worker/create'] = 'settings/integrations/twilio/Worker/create/$1';
$route['settings/integrations/twilio/workspace/(:any)/worker/update/(:any)'] = 'settings/integrations/twilio/Worker/update/$1/$2';
$route['settings/integrations/twilio/workspace/(:any)/worker/delete/(:any)'] = 'settings/integrations/twilio/Worker/delete/$1/$2';

$route['settings/integrations/twilio/workspace/(:any)/workflow'] = 'settings/integrations/twilio/Workflow/index/$1';
$route['settings/integrations/twilio/workspace/(:any)/workflow/create'] = 'settings/integrations/twilio/Workflow/create/$1';
$route['settings/integrations/twilio/workspace/(:any)/workflow/update/(:any)'] = 'settings/integrations/twilio/Workflow/update/$1/$2';
$route['settings/integrations/twilio/workspace/(:any)/workflow/delete/(:any)'] = 'settings/integrations/twilio/Workflow/delete/$1/$2';

$route['settings/integrations/twilio/workspace/(:any)/task-queue'] = 'settings/integrations/twilio/TaskQueue/index/$1';
$route['settings/integrations/twilio/workspace/(:any)/task-queue/create'] = 'settings/integrations/twilio/TaskQueue/create/$1';
$route['settings/integrations/twilio/workspace/(:any)/task-queue/update/(:any)'] = 'settings/integrations/twilio/TaskQueue/update/$1/$2';
$route['settings/integrations/twilio/workspace/(:any)/task-queue/delete/(:any)'] = 'settings/integrations/twilio/TaskQueue/delete/$1/$2';

$route['settings/integrations/twilio/edit'] = 'settings/integrations/twilio/Soft_twilio_calls/edit';
$route['settings/integrations/twilio/delete'] = 'settings/integrations/twilio/Soft_twilio_calls/delete';
$route['settings/integrations/twilio/flows'] = 'settings/integrations/twilio/Soft_twilio_calls/flows';
$route['settings/integrations/twilio/flow'] = 'settings/integrations/twilio/Soft_twilio_calls/flow';

$route['settings/integrations/twilio/messaging-services'] = 'settings/integrations/twilio/MessagingServices/index';
$route['settings/integrations/twilio/messaging-services/create'] = 'settings/integrations/twilio/MessagingServices/create';
$route['settings/integrations/twilio/messaging-services/update/(:any)'] = 'settings/integrations/twilio/MessagingServices/update/$1';
$route['settings/integrations/twilio/messaging-services/delete/(:any)'] = 'settings/integrations/twilio/MessagingServices/delete/$1';

$route['settings/integrations/twilio'] = 'settings/integrations/twilio/Soft_twilio_calls/index';
$route['settings/integrations/twilio/install'] = 'settings/integrations/twilio/Install';
$route['settings/integrations/twilio/install/([a-zA-Z].+)'] = 'settings/integrations/twilio/Install/$1';
$route['settings/integrations/twilio/sms/uninstall'] = 'settings/integrations/twilio/Install/sms_uninstall';
$route['settings/integrations/twilio/([a-zA-Z].+)'] = 'settings/integrations/twilio/Soft_twilio_calls/$1';
$route['settings/integrations/twilio/([a-zA-Z].+)/(:num)'] = 'settings/integrations/twilio/Soft_twilio_calls/$1/$2';
$route['settings/integrations/twilio/([a-zA-Z].+)/(:num)/([a-zA-Z].+)'] = 'settings/integrations/twilio/Soft_twilio_calls/$1/$2/$3';

$route['client_twilio_calls/dial'] = 'settings/integrations/twilio/Client_twilio_calls/dial';
$route['client_twilio_calls/start/voice/(:any)'] = "settings/integrations/twilio/Client_twilio_calls/start_voice/$1";//hide
$route['client_twilio_calls/start/sms/(:any)'] = "settings/integrations/twilio/Client_twilio_calls/start_sms/$1";
$route['client_twilio_calls/applet/voice/(:any)/(:any)'] = "settings/integrations/twilio/Client_twilio_calls/voice/$1/$2";//hide
$route['client_twilio_calls/applet/sms/(:any)/(:any)'] = "settings/integrations/twilio/Client_twilio_calls/sms/$1/$2";
$route['client_twilio_calls/whisper'] = "settings/integrations/twilio/Client_twilio_calls/whisper";
$route['client_twilio_calls/transcribe'] = "settings/integrations/twilio/Client_twilio_calls/transcribe";
$route['client_twilio_calls/play'] = 'settings/integrations/twilio/Client_twilio_calls/play';
$route['client_twilio_calls/redirectToVoice'] = 'settings/integrations/twilio/Client_twilio_calls/redirectToVoice/$1';
$route['client_twilio_calls/recording'] = 'settings/integrations/twilio/Client_twilio_calls/recording';
$route['client_twilio_calls/assignment'] = 'settings/integrations/twilio/Client_twilio_calls/assignment';//hide
$route['client_twilio_calls/taskCallbacks'] = 'settings/integrations/twilio/Client_twilio_calls/taskCallbacks';//hide
$route['client_twilio_calls/viewVoicemail'] = 'settings/integrations/twilio/Client_twilio_calls/viewVoicemail';
$route['client_twilio_calls/dial_status'] = 'settings/integrations/twilio/Client_twilio_calls/dial_status';

// End Soft twilio calls

//$route['leads/work_types'] = 'leads/work_types';

// employee login
$route['elogin'] = 'employees/login';

// reports general
//$route['reports/general'] = 'reports/index';

$route['payment/online/([a-zA-Z0-9_-]+)'] = 'payments/index/$1';

$route['user/inactive'] = 'user/index/inactive';
$route['user/active'] = 'user/index/active';
$route['user/dismissed'] = 'user/index/dismissed';
$route['user/inactive/system'] = 'user/index/inactive/system';
$route['user/active/system'] = 'user/index/active/system';
$route['user/dismissed/system'] = 'user/index/dismissed/system';

$route['employees/current'] = 'employees/index/current';
$route['employees/temporary'] = 'employees/index/temporary';
$route['employees/past'] = 'employees/index/past';
$route['employees/on_leave'] = 'employees/index/on_leave';

$route['employees/payroll/(:num)/range/([0-9-]+)/([0-9-]+)'] = 'payroll/range_report/$1/$2/$3';

$route['unsubscribe/(:any)/(:any)'] = 'unsubscribe/index/$1/$2';
$route['team_overview/(:num)'] = 'schedule/workorder_overview/$1';

$route['business_intelligence/sales'] = 'reports/sales';
$route['business_intelligence/ajax_get_sales_data'] = 'reports/ajax_get_sales_data';
/********APP*********/
$route['app/online'] = 'app/settings/online';
$route['app/configurations'] = 'app/settings/configurations';
$route['app/error/create'] = 'app/log/slack_report';
$route['app/jobs/(:num)'] = 'app/appjobs/show/$1';
$route['app/jobs/upload'] = 'app/appjobs/upload';
$route['app/jobs/stop/(:num)'] = 'app/appjobs/stop/$1';
$route['app/jobs/start/(:num)'] = 'app/appjobs/start/$1';
$route['app/jobs/ride/(:num)'] = 'app/appjobs/ride/$1';
$route['app/jobs/safety_form'] = 'app/appjobs/safety_form';
$route['app/jobs/delete_file'] = 'app/appjobs/delete_file';
$route['app/jobs/agenda'] = 'app/appjobs/agenda';
$route['app/jobs/agenda/(:any)'] = 'app/appjobs/agenda/$1';
$route['app/jobs/agenda/(:any)/(:any)'] = 'app/appjobs/agenda/$1/$2';
$route['app/jobs/fetch/(:num)'] = 'app/appjobs/fetch/$1';
$route['app/jobs/fetch/(:num)/(:any)'] = 'app/appjobs/fetch/$1/$2';
$route['app/jobs/get_pdf_sign'] = 'app/appjobs/getSafetyPdfSign';
$route['app/jobs/set_pdf_sign'] = 'app/appjobs/setSafetyPdfSign';
$route['app/jobs/dashboard/(:any)'] = 'app/appjobs/dashboard/$1';
$route['app/jobs/save_expenses'] = 'app/appjobs/save_expenses/';
$route['app/jobs/save_expenses/(:any)'] = 'app/appjobs/save_expenses/$1';
$route['app/jobs/showInvoicePdf/(:num)'] = 'app/appjobs/showInvoicePdf/$1';
$route['app/jobs/(:any)'] = 'app/appjobs/index/$1';
$route['app/tracker/timer'] = 'app/tracker/timer';
$route['app/tracker/timer/(:num)'] = 'app/tracker/timer/$1';
$route['app/tracker/(:any)'] = 'app/tracker/index/$1';
$route['app/leads/(:num)'] = 'app/appleads/show/$1';
$route['app/leads/(:num)/(:any)'] = 'app/appleads/show/$1/$2';
$route['app/leads/create_lead'] = 'app/appleads/create_lead';
$route['app/leads/stat'] = 'app/appleads/stat';
$route['app/leads/agenda'] = 'app/appleads/agenda';
$route['app/leads/agenda/(:any)'] = 'app/appleads/agenda/$1';
$route['app/leads/agenda/(:any)/(:any)'] = 'app/appleads/agenda/$1/$2';
$route['app/leads/fetch/(:num)'] = 'app/appleads/fetch/$1';
$route['app/leads/fetch/(:num)/(:any)'] = 'app/appleads/fetch/$1/$2';
$route['app/leads/assign_lead'] = 'app/appleads/assign_lead';
$route['app/leads/assign_task'] = 'app/appleads/assign_task';
$route['app/leads/update_lead'] = 'app/appleads/update_lead';
$route['app/leads/update_task'] = 'app/appleads/update_task';
$route['app/leads/get'] = 'app/appleads/get';
$route['app/leads/send'] = 'app/appleads/send';
$route['app/leads/statuses'] = 'app/appleads/statuses';
$route['app/leads/update_status'] = 'app/appleads/update_status';
$route['app/leads/upload'] = 'app/appleads/upload';
$route['app/leads/delete_file'] = 'app/appleads/delete_file';
$route['app/estimates/save'] = 'app/appestimates/save';//required for APP offline sync
$route['app/estimates/confirm'] = 'app/appestimates/confirm';//required for APP offline sync
$route['app/estimates/show/(:num)'] = 'app/appestimates/show/$1';
$route['app/estimates/get/(:num)'] = 'app/appestimates/get/$1';
$route['app/estimates/get/(:num)/(:num)'] = 'app/appestimates/get/$1/$2';
$route['app/estimates/get/(:num)/(:num)/(:num)'] = 'app/appestimates/get/$1/$2/$3';
$route['app/estimates/get_with_filters'] = 'app/appestimates/get_with_filters';
$route['app/estimates/get_with_filters/(:num)'] = 'app/appestimates/get_with_filters/$1';
$route['app/estimates/get_with_filters/(:num)/(:num)'] = 'app/appestimates/get_with_filters/$1/$2';
$route['app/estimates/fetch/(:num)'] = 'app/appestimates/fetch/$1';
$route['app/estimates/(:any)'] = 'app/appestimates/$1';
$route['app/clients/get'] = 'app/appclients/get';
$route['app/clients/get/(:num)'] = 'app/appclients/get/$1';
$route['app/clients/get/(:num)/(:num)'] = 'app/appclients/get/$1/$2';

// TODO: remove later as deprecated
$route['app/clients/fetch/(:any)'] = 'app/appclients/fetch/$1';

$route['app/clients/details/(:any)'] = 'app/appclients/details/$1';
$route['app/clients/search'] = 'app/appclients/search';
$route['app/clients/create'] = 'app/appclients/create';
$route['app/clients/update/(:any)'] = 'app/appclients/update/$1';
$route['app/clients/update_address/(:any)'] = 'app/appclients/update_address/$1';
$route['app/clients/delete/(:any)'] = 'app/appclients/delete/$1';
$route['app/clients/add_note'] = 'app/appclients/add_note';
$route['app/clients/delete_note/(:any)'] = 'app/appclients/delete_note/$1';
$route['app/clients/get_client_leads'] = 'app/appclients/get_client_leads';
$route['app/clients/add/tag'] = 'app/appclients/add_tag';
$route['app/clients/delete_tag'] = 'app/appclients/delete_tag';
$route['app/clients/tags/search'] = 'app/appclients/tags_search';
$route['app/clients/tags/all'] = 'app/appclients/all_tags';
$route['app/clients/get_us_autotax'] = 'app/appclients/get_us_autotax';
$route['app/tasks/get_categorized_users'] = 'app/apptasks/get_categorized_users';
$route['app/tasks/create_task'] = 'app/apptasks/create_task';
$route['app/tasks/get_task_categories'] = 'app/apptasks/get_task_categories';
$route['app/tasks/fetch/(:num)'] = 'app/apptasks/fetch/$1';
$route['app/creditcards/get_cards'] = 'app/appcreditcards/get_cards';
$route['app/creditcards/get_card_form/(:num)'] = 'app/appcreditcards/get_card_form/$1';
$route['app/creditcards/add_card'] = 'app/appcreditcards/add_card';
$route['app/creditcards/delete_card'] = 'app/appcreditcards/delete_card';
$route['app/creditcards/success/(:num)'] = 'app/appcreditcards/success/$1';
$route['app/creditcards/error'] = 'app/appcreditcards/error';

$route['app/workorders/get'] = 'app/appworkorders/get';
$route['app/workorders/get/(:any)'] = 'app/appworkorders/get/$1';
$route['app/workorders/get/(:any)/(:any)'] = 'app/appworkorders/get/$1/$2';
$route['app/workorders/get/(:any)/(:any)/(:any)'] = 'app/appworkorders/get/$1/$2/$3';
$route['app/workorders/fetch/(:num)'] = 'app/appworkorders/fetch/$1';
$route['app/workorders/upload'] = 'app/appworkorders/upload';
$route['app/workorders/showpdf/(:num)'] = 'app/appworkorders/showpdf/$1';
$route['app/workorders/update_status'] = 'app/appworkorders/update_status';
$route['app/workorders/send_pdf_to_email'] = 'app/appworkorders/send_pdf_to_email';
$route['app/workorders/update_notes'] = 'app/appworkorders/update_notes';
$route['app/workorders/get_by_status'] = 'app/appworkorders/workordersByStatuses';

$route['app/schedule/teams/get/(:any)'] = 'app/appschedule/scheduleCrews/$1';
$route['app/schedule/teams/get/(:any)/(:any)'] = 'app/appschedule/scheduleCrews/$1/$2';
$route['app/schedule/team/create'] = 'app/appschedule/teamCreateUpdate';
$route['app/schedule/team/update'] = 'app/appschedule/teamCreateUpdate';
$route['app/schedule/team/delete/(:num)'] = 'app/appschedule/deleteTeam/$1';
$route['app/schedule/free_members_for_team/(:num)'] = 'app/appschedule/freeMembers/$1';
//$route['app/schedule/team/change_order'] = 'app/appschedule/teamChangeOrder';
//$route['app/schedule/team/save_note'] = 'app/appschedule/team_save_note';

$route['app/schedule/event/create'] = 'app/appschedule/eventCreate';
$route['app/schedule/event/update'] = 'app/appschedule/eventUpdate';
$route['app/schedule/event/delete'] = 'app/appschedule/eventDelete';

//$route['app/schedule/event/change_damage'] = 'app/appschedule/event_change_damage';
//$route['app/schedule/event/change_complain'] = 'app/appschedule/event_change_complain';

$route['app/invoices/get'] = 'app/appinvoices/get';
$route['app/invoices/get/(:any)'] = 'app/appinvoices/get/$1';
$route['app/invoices/get/(:any)/(:any)'] = 'app/appinvoices/get/$1/$2';
$route['app/invoices/get/(:any)/(:any)/(:any)'] = 'app/appinvoices/get/$1/$2/$3';
$route['app/invoices/fetch/(:num)'] = 'app/appinvoices/fetch/$1';
$route['app/invoices/showpdf/(:num)'] = 'app/appinvoices/showpdf/$1';
$route['app/invoices/update_status'] = 'app/appinvoices/update_status';
$route['app/invoices/send_pdf_to_email'] = 'app/appinvoices/send_pdf_to_email';

$route['app/estimatesservices/update_status'] = 'app/appestimatesservices/update_status';

$route['app/payroll/getTeamMemberTime'] = 'app/apppayroll/getTeamMemberTime';
$route['app/payroll/setTeamMemberTime'] = 'app/apppayroll/setTeamMemberTime';
$route['app/payroll/delTeamMemberTime'] = 'app/apppayroll/delTeamMemberTime';

$route['app/equipment/ajax_get_types'] = 'app/appequipmentrepairs/ajax_get_types';
$route['app/equipment/ajax_get_equipment'] = 'app/appequipmentrepairs/ajax_get_equipment';
$route['app/equipment/repairs/ajax_create_repair'] = 'app/appequipmentrepairs/ajax_create_repair';
$route['app/equipment/repairs/ajax_file_upload'] = 'app/appequipmentrepairs/ajax_file_upload';

$route['clearToday'] = 'cron/clearToday';

$route['app/search'] = 'app/appsearch/index';

$route['app/project/details/(:any)'] = 'app/appleads/getProjectDetails/$1';

$route['app/clientsNotes/getNotes'] = 'app/appClientsNotes/getNotes';
$route['app/clientsNotes/getSmsNotes'] = 'app/appClientsNotes/getSmsNotes';
$route['app/clientsNotes/getCallNotes'] = 'app/appClientsNotes/getCallNotes';
/********APP*********/


$route['(:num)'] = 'clients/details/$1';
/********************FOR SIP IFRAME************/
$route['iframe/(:any)'] = '$1';
$route['iframe'] = 'dashboard';

$route['iframe/employees/current'] = 'employees/index/current';
$route['iframe/employees/temporary'] = 'employees/index/temporary';
$route['iframe/employees/past'] = 'employees/index/past';
$route['iframe/employees/on_leave'] = 'employees/index/on_leave';

$route['iframe/payment/online/([a-zA-Z0-9_-]+)'] = 'payments/index/$1';
$route['iframe/user/inactive'] = 'user/index/inactive';

$route['iframe/globalSearch'] = 'dashboard/globalSearch';
$route['iframe/globalSearch/(:any)/page'] = 'dashboard/globalSearch/$1';
$route['iframe/globalSearch/(:any)/page/(:any)'] = 'dashboard/globalSearch/$1/$2';
//$route['iframe/reports/general'] = 'reports/index';

$route['mixture/(.*)'] = 'console/index';

$route['_debugbar/(:any)'] = 'debugbar/$1';
$route['_debugbar/(:any)/(:any)'] = 'debugbar/$1/$2';
$route['opcache-api/(:any)'] = 'opcache/$1';

/* chatattachment */
$route['chat/attachment'] = 'chat/chat/sendAttachment';
$route['app/chat/attachment'] = 'app/users/sendAttachment';

/* Job management for administrator */
$route['jobs/manage/?(:any)?'] = 'administration/JobsManager/index/$1';
$route['job/delete'] = 'administration/JobsManager/ajax_delete';
$route['job/execute'] = 'administration/JobsManager/ajax_execute';
$route['job/edit'] = 'administration/JobsManager/ajax_edit';


/* Brands  */
$route['brands'] = 'brands/index';
$route['brands/(:num)'] = 'brands/index/$1';

/*--- ClientLetters ---*/
$route['clients/letters'] = 'clients/ClientLetters/index';

$route['messaging/callback/(.*)'] = 'messaging/callback/$1';
$route['messaging/ajax_open'] = 'messaging/ajax_open';
$route['messaging/ajax_get_count_unread'] = 'messaging/ajax_get_count_unread';
$route['messaging/ajax_get_users'] = 'messaging/ajax_get_users';
$route['messaging/ajax_get_contacts'] = 'messaging/ajax_get_contacts';
$route['messaging/ajax_get_history'] = 'messaging/ajax_get_history';
$route['messaging/ajax_get_message'] = 'messaging/ajax_get_message';
$route['messaging/ajax_set_read'] = 'messaging/ajax_set_read';
$route['messaging/ajax_send'] = 'messaging/ajax_send';

/* Email tracking */
$route['email/webhook'] = 'webhook/EmailLogs/index';

/** Billing management */
$route['billing'] = 'billing/index';
$route['billing/overview'] = 'billing/index';
$route['billing/sms_subscriptions'] = 'billing/sms_subscriptions';
$route['billing/transactions/(.*)'] = 'billing/transactions/$1';

/** Internal payments */
$route['internal_payments/payments_info'] = 'internalPayments/payments_info';
$route['internal_payments/ajax_get_card_form'] = 'internalPayments/ajax_get_card_form';
$route['internal_payments/ajax_save_billing'] = 'internalPayments/ajax_save_billing';
$route['internal_payments/ajax_get_transaction_details'] = 'internalPayments/ajax_get_transaction_details';
$route['internal_payments/ajax_refund_payment'] = 'internalPayments/ajax_refund_payment';

/* End of file routes.php */
/* Location: ./application/config/routes.php */

