<?php
/**********************************Company Settings******************************************/
$config['processing'] = FALSE;
$config['tax_name'] = 'HST';

/* Added into db config */
$config['tax_rate'] = 1.13;
/* Added into db config */

$config['company_dir'] = 'arbostar';
$config['company_name_short'] = 'Arbostar';
$config['company_name_long'] = 'Arbostar';
$config['company_header_logo'] = '/assets/' . $config['company_dir'] . '/img/logo_header.png';
$config['company_header_logo_styles'] = 'max-height: 40px;';
$config['company_header_logo_string'] = '';
$config['company_header_pdf_logo_styles'] = 'max-width:250px; float:right; max-height: 70px; margin-bottom:20px;';
$config['company_header_pdf_small_logo_styles'] = 'max-width:250px; float:right; max-height: 375px;  ';
$config['company_pdf_terms_conditions'] = '';
$config['company_pdf_payment_terms'] = '';
$config['payment_logo_styles'] = 'float:right; width:400px; margin:0 auto;';
/********************************Company Settings End***************************************/
/***********************************Contact Info********************************************/
$config['default_email_from'] = 'Team Arbostar';
$config['default_email_from_second'] = 'Arbostar Team';
$config['my_email'] = 'info@arbostar.com';
$config['promotion_links'][0]['link'] = 'https://www.facebook.com/ArboStarBMP';
$config['promotion_links'][0]['name'] = 'facebook';
$config['social_links']['Instagram'] = 'https://www.instagram.com/arbo_star/'; 
$config['social_links']['Facebook'] = 'https://www.facebook.com/ArboStarBMP';
$config['seo_email'] = 'seo@arborcare.com';

$config['autocomplete_restriction'] = 'ca';
$config['default_bcc'] = '';
$config['default_cc'] = '';
/*********************************Contact Info End*****************************************/
/*************************************Links************************************************/
$config['confirm_link'] = 'http://arbostar.arbostar.com/confirm';
$config['like_link'] = 'http://arbostar.arbostar.com/like';
$config['mailgun_event'] = 'sandbox.arbostar.com/events';
$config['mailgun_msg'] = 'sandbox.arbostar.com/messages';
$config['payment_link'] = 'https://sandbox.arbostar.com/payments';
$config['unsub_link'] = 'http://sandbox.arbostar.com/unsubscribe';
$config['unsubscribe_link'] = 'http://sandbox.arbostar.com/unsubscribe/unsubscribeAll/';
/*************************************Links End********************************************/
/*************************************Twilio Settings*************************************/
$config['messenger'] = FALSE;
$config['phone'] = FALSE;
$config['hard_from_number'] = '';
$config['onlineActivitySid'] = 'WA8ac2cad0e059057cc79239906a5cd92d';
$config['busyActivitySid'] = 'WA07792161a2daa982c6b583b6b0d1a6c7';
$config['reservedActivitySid'] = 'WA05c6247821cce51c06cd520ba5fb8419';
$config['wrapUpActivitySid'] = 'WA982fbd957b1aa7176f6d42ab05c485eb';
$config['offlineActivitySid'] = 'WA7a59ae313e658cf61d28fd0d73bad820';
$config['accountSid'] = 'ACba9a5eeb8b45a12e3973bd16e6ae83f2';
$config['authToken'] = '59cd6e4b9e26182c4fc30956b950bc5f';
$config['workspaceSid'] = 'WS2b1716bae438b349c9b45452a3446370';
$config['taskQueueSid'] = 'WQf7a1d1c62978f337f89a417831085953';
$config['appSid'] = 'APe44e5b40bfddca90743d491896ac8425';
$config['workflowSid'] = 'WW3c2ac180512a4b35ad937a6c60f253be';
$config['myNumber'] = '18885891764';
$config['twilioNumber'] = '18885891764';
$config['wsClient'] = 'https://arbostar.arbostar.com:8897';
/*********************************End Twilio Settings*************************************/
?>
