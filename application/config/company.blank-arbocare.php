<?php

/**********************************Company Settings******************************************/
$config['company_dir'] = 'arborcare';
$config['company_name_short'] = 'Arborcare';
$config['company_name_long'] = 'Arborcare Tree Service Ltd';
$config['company_header_logo'] = '/assets/' . $config['company_dir'] . '/img/logo_header.png';
$config['company_header_logo_styles'] = 'max-height: 40px;';
$config['company_header_logo_string'] = '';
$config['company_header_pdf_logo_styles'] = 'max-width:250px; float:right; max-height: 70px; margin-bottom:20px;';
$config['company_header_pdf_small_logo_styles'] = 'max-width:250px; float:right; max-height: 375px; ';
$config['company_pdf_terms_conditions'] = 'includes/'.$config['company_dir'].'/terms_conditions';
$config['company_pdf_payment_terms'] = 'includes/'.$config['company_dir'].'/payment_terms';
$config['payment_logo_styles'] = 'width:200px; margin:0 auto;';
/********************************Company Settings End***************************************/



/***********************************Contact Info********************************************/
$config['default_email_from'] = 'Team Arborcare';
$config['default_email_from_second'] = 'Arborcare Team';

$config['my_email'] = 'trees@arborecare.arbostar.com';
$config['promotion_links'][0]['link'] = 'https://ca.linkedin.com/company/arborcare-tree-service-ltd-';
$config['promotion_links'][0]['name'] = 'linkedin';
$config['social_links']['LinkedIn'] = 'https://ca.linkedin.com/company/arborcare-tree-service-ltd-';
$config['social_links']['Twitter'] = 'https://twitter.com/arborcarecanada';
$config['social_links']['Facebook'] = 'https://www.facebook.com/ArborCare-Tree-Service-261634060535944/';

$config['seo_email'] = 'seo@arborcare.com';

$config['autocomplete_restriction'] = 'ca';
$config['default_bcc'] = '';
$config['default_cc'] = '';
/*********************************Contact Info End*****************************************/


/*************************************Links************************************************/
$config['confirm_link'] = 'http://confirm.arborcare.arbostar.com/';
$config['like_link'] = 'http://like.arborcare.arbostar.com/';
$config['mailgun_event'] = 'arborcare.arbostar.com/events';
$config['mailgun_msg'] = 'arborcare.arbostar.com/messages';
$config['payment_link'] = 'http://payments.arborcare.arbostar.com/';
$config['unsub_link'] = 'http://unsubscribe.arborcare.arbostar.com/';
$config['unsubscribe_link'] = 'http://unsubscribe.arborcare.arbostar.com/unsubscribe/unsubscribeAll/';

/*************************************Links End********************************************/


/*************************************Twilio Settings*************************************/
$config['enable_twilio'] = false; // set True if need enable Twilio 
$config['hard_from_number'] = '';
$config['onlineActivitySid'] = 'WAd69379e3074947734f771826b916b066';
$config['busyActivitySid'] = 'WA968a3bdf04d6b68531aa8c7038b24193';
$config['reservedActivitySid'] = 'WA3ce5ad5da71384e3f452ccc8b02eefc7';
$config['wrapUpActivitySid'] = 'WA3ed6cf01595d83adc617d0e1977c5242';
$config['offlineActivitySid'] = 'WAca12bfdad956c08315e3f5d1ea7f692f';

$config['accountSid'] = 'ACba9a5eeb8b45a12e3973bd16e6ae83f2';
$config['authToken'] = '59cd6e4b9e26182c4fc30956b950bc5f';
$config['workspaceSid'] = 'WS319bdc8f490f429cf635df5df9a5bd74';
$config['taskQueueSid'] = 'WQ3ca4ca3a7989ada419c13583f0976d66';
$config['appSid'] = 'APe44e5b40bfddca90743d491896ac8425';
$config['workflowSid'] = 'WW22aa417939b1fe29c3ecb31d190b0722';
$config['myNumber'] = '18449890844';
$config['twilioNumber'] = '18449890844';
$config['wsClient'] = 'https://arborcare.arbostar.com:8896';
/*********************************End Twilio Settings*************************************/
