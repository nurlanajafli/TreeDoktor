<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$active_group = 'default';
//$active_record = true;
$query_builder = true;
$db['default'] = [
    'dsn'    => 'mysql:host={{aws.rds.host}};port=3306;dbname={{aws.rds.schema}}',
    'hostname' => '{{aws.rds.host}}',
    'username' => '{{aws.rds.user}}',
    'password' => '{{aws.rds.password}}',
    'database' => '{{aws.rds.schema}}',
    'dbdriver' => 'pdo',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '' ,
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
];

/* End of file database.php */
/* Location: ./application/config/database.php */
