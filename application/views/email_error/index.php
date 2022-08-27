<?php 
//echo "<pre>"; 
//var_dump($error)
?>

<tr style="background: #f1f1f3;"><td style="padding: 10px; color: ">
<h3 style="color: #5083e2dd;">Host: <?php echo isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:''; ?></h3>
<h3 style="color: #5083e2dd;">Current URL: <?php echo isset($_SERVER['HTTP_HOST'])?"https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']:''; ?></h3>
<h3 style="color: #5083e2dd;">IP Address: <?php echo isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:''; ?></h3>
<h3 style="border: 1px solid #ff0000; padding: 10px;">
    <?php echo $error->getMessage(); ?>
</h3>
<h5>
    Error code: <?php echo $error->getCode(); ?>
</h5>
<h3 style="color: #ff0000;">
	Trace:
</h3>
<p>
    <?php $Trace = $error->getTrace(); ?>
    <?php if(!empty($Trace)): ?>
    	<?php foreach($Trace as $item): ?>
    		<?php if(!empty($item)): ?>
    			<?php foreach($item as $key => $value): ?>
    				<?php if(is_string($value) || is_numeric($value)): ?>
    					<p style="margin:3px;<?php if(strpos($value, 'application/modules')!==FALSE): ?>color:#ff0000;<?php endif;?>"><strong><?php echo $key; ?>:</strong>&nbsp;<i><?php echo $value; ?></i></p>
    				<?php endif; ?>
    				<?php if(is_array($value) && !empty($value)): ?>
    					<h4 style="padding-left:20px;color: #ff0000;"><?php echo $key; ?></h4>
    					<?php foreach($value as $k => $v): ?>
    						
    						<?php if(is_string($v) || is_numeric($v)): ?>
    							<p style="margin:0; margin-left:20px;"><strong><?php echo $k; ?>:</strong>&nbsp;<strong><?php echo $v; ?></strong></p>
    						<?php endif; ?>

    					<?php endforeach; ?>		

    				<?php endif; ?>
    			<?php endforeach; ?>
    		<?php endif; ?>

    		<hr>
    		<?php /*
    		<p <?php if(strpos((isset($item['file']))?$item['file']:'', 'application')!==FALSE): ?>style="color: #ff0000; font-weight: 700"<?php endif; ?>>

    			<?php if(isset($item['file'])): ?>
    			<p style="margin: 3px;">File:<?php echo $item['file']; ?></p>
    			<?php endif; ?>
    			<?php if(isset($item['line'])): ?>
    			<p>Line:<?php echo $item['line']; ?></p>
    			<?php endif; ?>
    			<p>Function:<?php echo $item['function']; ?></p>
    			<?php if(isset($item['class'])): ?>
    			<p>Class:<?php echo $item['class']; ?></p>
    			<?php endif; ?>
    			<?php if(isset($item['type'])): ?>
    			<p>Type:<?php echo $item['type']; ?></p>
    			<?php endif; ?>
    			<?php if(isset($item['args'])): ?>
    			<p>args:<?php echo json_encode($item['args']); ?></p>
    			<?php endif; ?>
    		</p>
    		*/?>
    	<?php endforeach; ?>
    <?php endif; ?>
</p>

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

</td></tr>