<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$max_upload = return_kilobytes(ini_get('upload_max_filesize'));
$max_post = return_kilobytes(ini_get('post_max_size'));
$memory_limit = return_kilobytes(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

$config = [
	'allowed_types' => 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG',
	'overwrite' 	=> FALSE,
	'max_size' 		=> $upload_mb,
	'upload_path' 	=> PICTURE_PATH,
	'file_name'		=> 'tmp_user_file.jpg'
];