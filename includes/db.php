<?php
global $wpdb;
 
//create the name of the table including the wordpress prefix (wp_ etc)
$wppm_table = $wpdb->prefix . "project_coworkers";
 
//check if there are any tables of that name already
if($wpdb->get_var("show tables like '$search_table'") !== $wppm_table)  {
	//create your sql
	$sql =  "CREATE TABLE ". $wppm_table . " (
					  ID mediumint(12) NOT NULL AUTO_INCREMENT, 
					  project_id mediumint(9),
					  user_id mediumint(9),
					  UNIQUE KEY ID (ID));";
}
 
//include the wordpress db functions
require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
dbDelta($sql);