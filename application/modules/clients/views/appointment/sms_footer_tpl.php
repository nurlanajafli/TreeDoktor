<?php 

$footer = "\n";
$footer .= "Date:".$task_date."\n";
$footer .= "Start:".date("H:i", strtotime($task_start))."\n";
$footer .= "Finish:".date("H:i", strtotime($task_end))."\n";
$footer .= "Address:".$formatted_address;

echo $footer;
?>