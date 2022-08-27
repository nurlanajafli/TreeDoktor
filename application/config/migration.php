<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Enable/Disable Migrations
|--------------------------------------------------------------------------
|
| Migrations are disabled by default but should be enabled 
| whenever you intend to do a schema migration.
|
*/

$config['migration_type'] = 'sequential';
//'timestamp';

$config['migration_enabled'] = TRUE;

$config['install_migrations_need'] = TRUE;
/*
|--------------------------------------------------------------------------
| Migrations version
|--------------------------------------------------------------------------
|
| This is used to set migration version that the file system should be on.
| If you run $this->migration->latest() this is the version that schema will
| be upgraded / downgraded to.
|
*/
$config['migration_version'] = 0;


/*
|--------------------------------------------------------------------------
| Migrations Path
|--------------------------------------------------------------------------
|
| Path to your migrations folder.
| Typically, it will be within your application path.
| Also, writing permission is required within the migrations path.
|
*/
$config['migration_path'] = APPPATH . 'migrations/';


$config['migration_install_path'] = APPPATH . 'migrations/core/onlineoffice.sql';
$config['migration_uninstall_path'] = APPPATH . 'migrations/core/clear_database.sql';

$config['migration_data_path'] = APPPATH . 'migrations/data/';


$config['users'] = [
	[
		'user_type'=>'user',
		'emailid'=>'system',
		'password'=>'c62d929e7b7e7b6165923a5dfc60cb56',
		'firstname'=>'system',
		'lastname'=>'system',
		'added_on'=>date('Y-m-d H:i:s'),
		'active_status'=>'yes',
		'rate'=>102,
		'color'=>'#ffffff',
		'picture'=>'',
		'system_user'=>1
	],
	[
		'user_type'=>'admin',
		'emailid'=>'root',
		'password'=>md5('ArboPa$$2019'),
		'firstname'=>'root',
		'lastname'=>'root',
		'added_on'=>date('Y-m-d H:i:s'),
		'active_status'=>'yes',
		'rate'=>102,
		'color'=>'#ffffff',
		'picture'=>'',
        'system_user'=>1
	],
];
/* End of file migration.php */
/* Location: ./application/config/migration.php */
