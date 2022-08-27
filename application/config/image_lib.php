<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = [
	'image_library' => 'gd2',
	'source_image' => PICTURE_PATH.'tmp_user_file.jpg',
	'new_image' => PICTURE_PATH.'tmp_user_file_thumb.jpg',
	'create_thumb' => FALSE,
	'maintain_ratio' => TRUE,
	'width' => 230,
	'height' => 230,
];