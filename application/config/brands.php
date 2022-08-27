<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config = [
    'logos'=>[
        [
            'label'         => 'Main Logo',
            'logo_file'     => 'main_logo_file', 
            'logo_filename' => 'main_logo_name',
            'config_key'    => 'company_header_logo',
            'width'         => 160,
            'height'        => 151,
            'default_image' => '/assets/brands/main.png',
            'thumbnail_template'=>'site_template'
        ],
        [
            'label'         =>  'Estimate PDF',
            'logo_file'     =>  'estimate_logo_file', 
            'logo_filename' =>  'estimate_logo_name',
            'width'         =>  120,
            'height'        =>  120,
            'default_image' =>  '/assets/brands/arbostar.png',
            'thumbnail_template'=>'estimate_template'
        ],
        /*
        [
            'label'         =>  'Estimate PDF Logo',
            'logo_file'     =>  'estimate_logo_file', 
            'logo_filename' =>  'estimate_logo_name',
            'width'         =>  720,
            'height'        =>  200,
            'default_image' =>  '/img.png'
        ],
        */
        [
            'label'         =>  'Estimate PDF Left Side',
            'logo_file'     =>  'estimate_left_side_file', 
            'logo_filename' =>  'estimate_left_side_file_name',
            'width'         =>  58,
            'height'        =>  1153,
            'default_image' =>  '/assets/brands/container_table_left_margin.png',
            'thumbnail_template'=>'estimate_template',
        ], 
        [
            'label'         => 'Invoice PDF',
            'logo_file'     => 'invoice_logo_file', 
            'logo_filename' => 'invoice_logo_name',
            'width'         => 120,
            'height'        => 120,
            'default_image' => '/assets/brands/arbostar.png',
            'thumbnail_template'=>'invoice_template'
        ],
        [
            'label'         =>  'Invoice PDF Left Side',
            'logo_file'     =>  'invoice_left_side_file', 
            'logo_filename' =>  'invoice_left_side_file_name',
            'width'         =>  58,
            'height'        =>  1153,
            'default_image' =>  '/assets/brands/container_table_left_margin.png',
            'thumbnail_template'=>'invoice_template'
        ],
        [
            'label'         =>  'Payment PDF',
            'logo_file'     =>  'payment_logo_file', 
            'logo_filename' =>  'payment_logo_name',
            'width'         =>  160,
            'height'        =>  151,
            'default_image' =>  '/assets/brands/arbostar.png',
            'thumbnail_template'=>'payment_template'
        ],  
        [
            'label'         =>  'Whatermark',
            'logo_file'     =>  'watermark_logo_file', 
            'logo_filename' =>  'watermark_logo_name',
            'width'         =>  395,
            'height'        =>  395,
            'default_image' =>  '/assets/brands/watermark.png',
            'thumbnail_template'=>'whatermark_template'
        ]
    ]
];