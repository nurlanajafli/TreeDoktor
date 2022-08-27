<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// Local Variables are set to work on the live server. Do not modify!!!
define("BASE_PATH", "/home2/arbostar/public_html/treedoctors-crm/");
define("UPLOAD_EMPLOYEE_PIC", BASE_PATH . "uploads/employees_tracking/");
define("_EMPLOYEE_PIC", "/uploads/employees_tracking/");

// live variables
//define("BASE_PATH","C:\\wamp\\www\\treedoctor_old\\");
//define("BASE_PATH","C:\\wamp\\www\\treedoctor_old\\");

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');


define('SITE_NAME', 'Tree Doctors');
define('PICTURE_PATH', 'assets/treedoctors/pictures/');
define("PAYMENT_UPLOAD_PATH", BASE_PATH . "uploads/payment_files/");
define('PAYMENT_FILES_PATH', 'uploads/payment_files/');
define('MAX_RAND_NUM', '9999999999');

define("INVOICES_UPLOAD_PATH", BASE_PATH . "uploads/invoices/");

define('_SECURE_LINK_TO_CLIENT', "Payment Link");
define('_ADMIN_EMAIL', "accounting@treedoctors.ca");
define('_ADMIN_NAME', "Tree Doctors Team");

define('_PAYMENT_DONE_SUBJECT', "Payment received");

//Toronto Arborist merchant ID, used after 21 May, 2014.
//define("_FIRST_DATA_MERCHANT_ID","256780739");
//Tree Doctors merchant ID, used before 21 May, 2014.
//define("_FIRST_DATA_MERCHANT_ID","225790848");
//test merchant id
//define("_FIRST_DATA_MERCHANT_ID", "256780739");
define("_FIRST_DATA_MERCHANT_ID", "225792679");
define("_FIRST_DATA_API_KEY", "ff900aa889f440Aa9453944a300140F9");
define("_CC_MAX_PAYMENT", 2000);
//define("_FIRST_DATA_MERCHANT_ID", "225791040");

define("_HASH_KEY_VALID_DAYS", 999);

// disable timer button
define("_DISABLE_TIMER_BUTTON", 3000);

//options for term and interest
define("INVOICE_TERM", 30);
define("INVOICE_INTEREST", 2);

define("DB_BACKUP_PATH", "cron_dbbackup/");
define("GOOD_MAN_HOURS_RETURN", 67);
define("GREAT_MAN_HOURS_RETURN", 75);
define("VERY_GREAT_MAN_HOURS_RETURN", 100);
define("ACTIVITY_TIMEOUT", 120);//sec
define('AWS_KEY', 'AKIAV7WCIUMYP7C2R546');
define('AWS_SECRET_KEY', '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');
// NB! bucket name to be immutable, and set same way as credentials
define('S3_BUCKET_NAME', 'treedoctors');
/* End of file constants.php */
/* Location: ./application/config/constants.php */
