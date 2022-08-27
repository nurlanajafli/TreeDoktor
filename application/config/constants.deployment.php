<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', true);

// Local Variables are set to work on the live server. Do not modify!!!
defined('BASE_PATH') OR define("BASE_PATH", "/home2/arbostar/public_html/treedoctors-crm/");
defined('UPLOAD_EMPLOYEE_PIC') OR define("UPLOAD_EMPLOYEE_PIC", BASE_PATH . "uploads/employees_tracking/");
defined('_EMPLOYEE_PIC') OR define("_EMPLOYEE_PIC", "/uploads/employees_tracking/");

// live variables
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
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE',
    'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',
    'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

defined('SITE_NAME') OR define('SITE_NAME', '{{consumer.name.short}}');
defined('PICTURE_PATH') OR define('PICTURE_PATH', 'uploads/avatars/');
defined('PAYMENT_UPLOAD_PATH') OR define("PAYMENT_UPLOAD_PATH", BASE_PATH . "uploads/payment_files/");
defined('PAYMENT_FILES_PATH') OR define('PAYMENT_FILES_PATH', 'uploads/payment_files/');
defined('MAX_RAND_NUM') OR define('MAX_RAND_NUM', '9999999999');

defined('_SECURE_LINK_TO_CLIENT') OR define('_SECURE_LINK_TO_CLIENT', "Payment Link");
defined('_ADMIN_EMAIL') OR define('_ADMIN_EMAIL', "accounting@treedoctors.ca");
defined('_ADMIN_NAME') OR define('_ADMIN_NAME', "Tree Doctors Team");

defined('_PAYMENT_DONE_SUBJECT') OR define('_PAYMENT_DONE_SUBJECT', "Payment received");

//Toronto Arborist merchant ID, used after 21 May, 2014.
//define("_FIRST_DATA_MERCHANT_ID","256780739");
//Tree Doctors merchant ID, used before 21 May, 2014.
//define("_FIRST_DATA_MERCHANT_ID","225790848");
//test merchant id
//define("_FIRST_DATA_MERCHANT_ID", "256780739");
defined('_FIRST_DATA_MERCHANT_ID') OR define("_FIRST_DATA_MERCHANT_ID", "225792679");
defined('_FIRST_DATA_API_KEY') OR define("_FIRST_DATA_API_KEY", "ff900aa889f440Aa9453944a300140F9");
defined('_CC_MAX_PAYMENT') OR define("_CC_MAX_PAYMENT", {{crm.payment.limit}});
defined('_CC_MAX_CLIENT_PAY_COUNT') OR define("_CC_MAX_CLIENT_PAY_COUNT", 20);
//define("_FIRST_DATA_MERCHANT_ID", "225791040");

defined('_HASH_KEY_VALID_DAYS') OR define("_HASH_KEY_VALID_DAYS", 999);

// disable timer button
defined('_DISABLE_TIMER_BUTTON') OR define("_DISABLE_TIMER_BUTTON", 3000);

//options for term and interest
defined('INVOICE_TERM') OR define("INVOICE_TERM", {{crm.invoice.term.general}});
defined('INVOICE_CORP_TERM') OR define("INVOICE_CORP_TERM", {{crm.invoice.term.corporate}});
defined('INVOICE_MUNI_TERM') OR define("INVOICE_MUNI_TERM", {{crm.invoice.term.municipal}});
defined('INVOICE_INTEREST') OR define("INVOICE_INTEREST", {{crm.invoice.interest}});

defined('DB_BACKUP_PATH') OR define("DB_BACKUP_PATH", "cron_dbbackup/");
defined('GOOD_MAN_HOURS_RETURN') OR define("GOOD_MAN_HOURS_RETURN", 67);
defined('GREAT_MAN_HOURS_RETURN') OR define("GREAT_MAN_HOURS_RETURN", 75);
defined('VERY_GREAT_MAN_HOURS_RETURN') OR define("VERY_GREAT_MAN_HOURS_RETURN", 100);
defined('ACTIVITY_TIMEOUT') OR define("ACTIVITY_TIMEOUT", 120);//sec

defined('AWS_KEY') OR define('AWS_KEY', '{{aws.access.key}}');
defined('AWS_SECRET_KEY') OR define('AWS_SECRET_KEY', '{{aws.access.secret}}');
// NB! bucket name to be immutable, and set same way as credentials
defined('S3_BUCKET_NAME') OR define('S3_BUCKET_NAME', '{{aws.s3.name}}');
defined('S3_BUCKET_FOLDER') OR define('S3_BUCKET_FOLDER', '{{aws.s3.folder_name}}');
defined('AWS_REGION') OR define('AWS_REGION', '{{service.region}}');
defined('AWS_VERSION') OR define('AWS_VERSION', 'latest');

defined('DISTANCE_MEASUREMENT') OR define('DISTANCE_MEASUREMENT', '{{crm.distance.unit}}');
/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
/* End of file constants.php */
/* Location: ./application/config/constants.php */
