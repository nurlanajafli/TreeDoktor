<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/


/*
$hook['pre_system'][] = array(
    'class'    => 'DotEnvHook',
    'function' => 'init',
    'filename' => 'DotEnvHook.php',
    'filepath' => 'hooks',
    'params'   => array()
);
*/
$config['error_notification_emails'] = 'anton.rozonenko@gmail.com, gleba.ruslan@gmail.com';
$config['error_notification_from'] = 'system@arbostar.com';
//$config['error_notification_subject'] = 'Important! PHP ERROR ON '.$_SERVER['HTTP_HOST'];
$hook['pre_system'][] = array(
  'class'    => 'PHPFatalError',
  'function' => 'init',
  'filename' => 'PHPFatalError.php',
  'filepath' => 'hooks',
  'params'   => array('error_emails'=>$config['error_notification_emails']) //  ,gleba.ruslan@gmail.com
);


if (ENVIRONMENT && ENVIRONMENT=='production'){
  
  $hook['pre_controller'][] = array(
    'class'    => 'PHPAfterError',
    'function' => 'init',
    'filename' => 'PHPAfterError.php', // file name
    'filepath' => 'hooks',
    'params'   => array('error_emails'=>$config['error_notification_emails']) //  ,gleba.ruslan@gmail.com
  );  
}

if (ENVIRONMENT && ENVIRONMENT=='development'){
  
  $hook['post_controller'][] = array(
    'class'    => 'PHPAfterError',
    'function' => 'init',
    'filename' => 'PHPAfterError.php', // file name
    'filepath' => 'hooks',
    'params'   => array('error_emails'=>$config['error_notification_emails']) //  ,gleba.ruslan@gmail.com
  );  
}

$hook['pre_controller'][] = array(
    'class' => 'InitDependencies',
    'function' => 'init',
    'filename' => 'InitDependencies.php', // file name
    'filepath' => 'hooks',
    'params' => []
);

/*$hook['pre_controller'][] = array(
    'class' => 'DotEnvHook',
    'function' => 'init',
    'filename' => 'DotEnvHook.php', // file name
    'filepath' => 'hooks',
    'params' => []
);*/

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
