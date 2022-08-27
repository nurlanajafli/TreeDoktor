<?php
/**
 * Handles dynamic search
 *
 * @package tinymce
 */
define('MODX_API_MODE', true);
include_once("../../../../../../index.php");
$modx->db->connect();
if (empty ($modx->config)) {
    $modx->getSettings();
}
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || !isset($_SESSION['mgrValidated'])){
        die();
}
$output = '';
$searchMode = $_GET['search-mode'];
if ($searchMode != 'alias' && $searchMode != 'pagetitle') die();
$query = $modx->db->escape($_GET['q']);

$where = $searchMode." LIKE '%".$query."%' AND deleted=0";

$result = $modx->db->select("id,pagetitle,alias", $modx->getFullTableName('site_content'), $where, '', 10); 

while( $row = $modx->db->getRow( $result ) ) { 
	$output .= $row['pagetitle'] . ' (' . $row['id'] . ')|'. $row['id'] . "\n"; 
 }
die($output);
