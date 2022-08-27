<?php

    $config['protocol']    = 'sendmail';
    $config['subject']	=	'Estimate PDF';



    $config['charset']    = 'utf-8';
    $config['newline']    = "\r\n";
    /*$config['validation'] = TRUE;*/

    $config['default_mail_driver'] = 'mailgun'; // test for  aws identities
    $config['mailerDrivers'] = [
        'smtp' => [
            'transport' => 'smtp',
            'protocol' => 'smtp',
            'smtp_host' => 'tls://smtp.meta.ua', 'smtp_user' => 'notify.arbo@meta.ua',
            'fromName' => 'Arbos+Ugen',
            //'smtp_pass' => 'jdwbijypqdhqxcwj',
            'smtp_pass' => 'BGIuv/DJrMvdRI9qADho3',
            'smtp_port' => 465,
            'smtp_timeout' => 30
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail',
//            'smtp_host' => 'localhost',
//            'smtp_port' => 465,
            'from' => 'info@arbostar.com',
            'fromName' => 'Arbostar',
            'html' => true,
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            'protocol' => 'smtp',
            'mailtype' => 'html',
            'host' => 'ssl://smtp.mailgun.org',
            'port' => '465',
            'encryption' => 'tls',
            'username' => 'Arbostar',
            'password' => 'dc3fc61c74cb51cbd69d3f3b7d80c766-9c988ee3-4a9e8f33',
            'timeout' => null,
            'auth_mode' => null,
        ],
        //need to fill
        'amazon' => [
            'transport' => 'amazon',
            'mailtype' => 'html',
            'protocol' => 'smtp',
            'from' => 'ujdjhbnm',
            'fromName' => 'Arbostar',
            'host' => 'email-smtp.us-west-2.amazonaws.com',
            'port' => 587,
            'username' => 'AKIAV7WCIUMYG7YTSFXJ',
            'password' => 'BGIuv/DJrMvdRI9qADho3NxVIpzJnvxYC6JUE3ypTk1l',
            'auth' => true,
            'secure' => 'tls',
            'html' => true,
        ],
    ];
?>
