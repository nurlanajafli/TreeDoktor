<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        .logos{
            display: flex;
            justify-content: space-between;;
            align-items: center;
            background-color: #81ba53;
            height:50px;
            border-radius: 5px 5px 0 0;
            padding: 0 10px
        }
        .logos img{
            width: 30px;
        }
        .logos a{
            text-align: center;
            font-size: 20px;
            line-height: 50px;
            display: inline-block;
            font-weight: bold;
            text-decoration: none;
            color: #fff;
        }
        tr:last-child{
            padding-bottom: 2em;
        }
        td{
            
        }
    </style>
</head>
<body>

<div>

    <table width="980" border="0" cellpadding="0" cellspacing="0" style="font-family:arial;background:#fff" align="center">
        <tbody>
        <tr>
            <td class="logos">
                <?php /*<img src="<?php echo base_url($this->config->item('company_header_logo')); ?>" alt="header">*/ ?>
                <a href="<?php echo base_url("dashboard"); ?>"><?php echo config_item('company_header_logo_string'); ?></a>
                 
            </td>
        </tr>
        