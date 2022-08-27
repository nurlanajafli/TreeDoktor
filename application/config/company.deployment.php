<?php

/**********************************Company Settings******************************************/
$config['company_dir'] = '{{service.id}}';
$config['bucket_sub_folder'] = defined('S3_BUCKET_FOLDER') && S3_BUCKET_FOLDER !== '' ? S3_BUCKET_FOLDER : null;
$config['company_name_short'] = '{{consumer.name.short}}';
$config['company_name_long'] = '{{consumer.name.long}}';
$config['company_header_logo'] = '/assets/' . $config['company_dir'] . '/img/logo_header.png';
$config['company_header_logo_styles'] = 'max-height: 40px;';
$config['company_header_logo_string'] = '{{consumer.name.short}}';
$config['company_header_pdf_logo_styles'] = '{{consumer.pdf.logo.style}}';
$config['company_header_pdf_small_logo_styles'] = '{{consumer.pdf.smlogo.style}}';
$config['company_pdf_terms_conditions'] = 'includes/terms_conditions';
$config['company_pdf_payment_terms'] = 'includes/payment_terms';
$config['payment_logo_styles'] = 'float:right; width:400px; margin:0 auto;';
$config['gps_enabled'] = {{crm.gps}};
$config['processing'] = {{crm.processing}};
$config['tax_name'] = '{{crm.tax.name}}';
$config['tax_rate'] = {{crm.tax.rate}};
$config['tax_perc'] = {{crm.tax.percent}};
$config['tax_string'] = '{{crm.tax.description}}';
$config['map_lat'] = '{{crm.geo.map.lat}}';
$config['map_lon'] = '{{crm.geo.map.lon}}';
$config['map_center'] = $config['map_lat'] . ', ' . $config['map_lon'];
$config['office_lat'] = '{{crm.geo.office.lat}}';
$config['office_lon'] = '{{crm.geo.office.lon}}';
$config['office_location'] = $config['office_lat'] . ', ' . $config['office_lon'];
$config['leads_circles'] = [{{crm.geo.leads_circles}}];
$config['autocomplete_restriction'] = '{{crm.autorestrict}}';
$config['payment_methods'][1] = 'Cash';
if($config['processing']) {
    $config['payment_methods'][2] = 'Credit Card';
    $config['default_cc'] = 2;
}
$config['payment_methods'][3] = 'Cheque';
$config['payment_methods'][4] = 'Debit Card';
$config['payment_methods'][5] = 'E-Transfer';
$config['default_cc'] = 2;
$config['qa_types'][1] = 'Suggestion';
$config['qa_types'][2] = 'Complaint';
$config['qa_types'][3] = 'Compliment';
$config['display_tax_in_estimate'] = {{crm.tax.display.estimate}};
$config['payment_default'] = '{{crm.payment.default}}';
$config['payment_currency'] = '{{crm.payment.currency}}';
/********************************Company Settings End***************************************/



/***********************************Contact Info********************************************/
$config['default_email_from'] = '{{consumer.email.admin.from}}';
$config['default_email_from_second'] = '{{consumer.email.admin.from}}';

$config['my_email'] = '{{consumer.email.admin.address}}';
$config['promotion_links'][0]['link'] = '{{consumer.resources.promo.url}}';
$config['promotion_links'][0]['name'] = '{{consumer.resources.promo.name}}';
$config['social_links']['Instagram'] = '{{consumer.resources.social.instagram}}';
$config['social_links']['Facebook'] = '{{consumer.resources.social.facebook}}';

$config['seo_email'] = '{{consumer.email.admin.address}}';
$config['thank_you_page_sign'] = '{{crm.customers.thnankyoupage.sign}}';
/*********************************Contact Info End*****************************************/


/*************************************Links************************************************/
$config['app_domain'] = '{{service.domain}}';
$config['confirm_link'] = 'http://'.$config['app_domain'].'/';
$config['like_link'] = 'http://'.$config['app_domain'].'/';
$config['mailgun_event'] = '{{crm.mailgun.event}}';
$config['mailgun_msg'] = '{{crm.mailgun.msg}}';
$config['payment_link'] = 'https://'.$config['app_domain'].'/';
$config['payment_brands'] = [{{crm.payment.brands}}];
$config['unsub_link'] = 'http://'.$config['app_domain'].'/';
$config['unsubscribe_link'] = 'http://'.$config['app_domain'].'/';
$config['signature_link'] = 'https://'.$config['app_domain'].'/';

/*************************************Links End********************************************/


/*************************************Twilio Settings*************************************/
$config['messenger'] = {{crm.messenger}};
$config['phone'] = {{crm.phone}};

$config['externalWsPort'] = $config['app_domain'] === 'localhost' ? '8895' : '443';
$config['wsClient'] = 'https://' . $config['app_domain'] . ':' . $config['externalWsPort'];

$config['hard_from_number'] = '{{crm.overrides.hard_from_number}}';
$config['onlineActivitySid'] = '{{crm.keys.onlineactivitysid}}';
$config['busyActivitySid'] = '{{crm.keys.busyactivitysid}}';
$config['reservedActivitySid'] = '{{crm.keys.reservedactivitysid}}';
$config['wrapUpActivitySid'] = '{{crm.keys.wrapupactivitysid}}';
$config['offlineActivitySid'] = '{{crm.keys.offlineactivitysid}}';

$config['api_key'] = '{{crm.keys.api_key}}';

$config['accountSid'] = '{{crm.keys.accountsid}}';
$config['authToken'] = '{{crm.keys.authtoken}}';
$config['workspaceSid'] = '{{crm.keys.workspacesid}}';
$config['taskQueueSid'] = '{{crm.keys.taskqueuesid}}';
$config['appSid'] = '{{crm.keys.appsid}}';
$config['workflowSid'] = '{{crm.keys.workflowsid}}';
$config['messagingServiceSid'] = '{{crm.keys.messagingservicesid}}';

$config['myNumber'] = '{{consumer.office.phone.number}}';
$config['twilioNumber'] = '{{consumer.office.phone.number}}';
/*********************************End Twilio Settings*************************************/

$config['payment_methods'][3] = '{{crm.payment.check_label}}';
?>
