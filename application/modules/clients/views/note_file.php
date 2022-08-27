<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>


<strong>From: </strong><?php echo $from; ?><br>
<?php if(isset($cc) && $cc != '') : ?>
    <strong>Cc: </strong><?php echo $cc; ?><br>
<?php endif; ?>
<?php if(isset($bcc) && $bcc != '') : ?>
	<strong>Bcc: </strong><?php echo $bcc; ?><br>
<?php endif; ?>
<strong>To: </strong><?php echo $to; ?><br>

<strong>Subject: </strong><?php echo $subject; ?><br>

<strong>Date: </strong><?php echo date('Y-m-d H:i:s'); ?><br>


<div><?php echo $text; ?></div>
</body>
</html>
