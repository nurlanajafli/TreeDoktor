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
                <a href="<?php echo isset($base_url)?$base_url:'#'; ?>"><?php echo isset($company_site_name)?$company_site_name:''; ?></a>
                 
            </td>
        </tr>
        
<tr style="background: #f1f1f3; padding: 10;"><td style="padding: 10px;">
<h3 style="color: #5083e2dd;">Host: <?php echo isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:''; ?></h3>
<h3 style="color: #5083e2dd;">Current URL: <?php echo isset($_SERVER['HTTP_HOST'])?"https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']:''; ?></h3>
<h3 style="color: #5083e2dd;">IP Address: <?php echo isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:''; ?></h3>
<h2 style="border: 1px solid #ff0000; padding: 10px;"><?php echo isset($type_text)?$type_text:''; ?>&nbsp;&nbsp;(<?php echo isset($message['type'])?$message['type']:''; ?>)</h2>
<h3 style="border: 1px solid #ff0000; padding: 10px;">
    <?php if(isset($message['file'])): ?>
    File: <?php echo $message['file']; ?><br> 
    <?php endif; ?>
    <?php if(isset($message['line'])): ?>
    Line: <?php echo $message['line']; ?><br>
    <?php endif; ?>
    <?php if(isset($message['message'])): ?>
    <i><?php echo $message['message']; ?></i><br>
    <?php endif; ?>
</h3>
<h3 style="border: 1px solid #ff0000; padding: 10px; font-weight: 100; font-size: 10px;">
    <strong>POST:</strong>
    <pre style="white-space: pre-wrap;">
        <?php var_dump($_POST); ?>
    </pre>
    <strong>JSON:</strong>
    <pre>
        <?php echo json_encode($_POST, JSON_PRETTY_PRINT); ?>
    </pre>
</h3>
<?php /*
<h3 style="color: #ff0000;">
	Trace:
</h3>
*/ ?>
</td></tr>

<tr class="foot">
    <td style="font-size:11px;color:#626161;padding-left:20px;padding-right:20px;padding-top:40px;padding-bottom:40px">
        <p>
            Best Regards <br />
            Arbostar Administration
        </p>
    </td>
</tr>
</tbody></table>
</div>

</body>
</html>
