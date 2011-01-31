<?php
$wpdb->show_errors();
//show small groups 
$out.='<p>';
$sql = "SELECT ".$wpdb->prefix."church_admin_smallgroup.*, CONCAT_WS(' ',".$wpdb->prefix."church_admin_directory.first_name,".$wpdb->prefix."church_admin_directory.last_name) AS leader_name FROM ".$wpdb->prefix."church_admin_smallgroup,".$wpdb->prefix."church_admin_directory WHERE ".$wpdb->prefix."church_admin_smallgroup.leader=".$wpdb->prefix."church_admin_directory.id";
$results = $wpdb->get_results($sql);    
foreach ($results as $row) 
	{
		$out .= ''.esc_html(stripslashes($row->group_name))." group led by ".esc_html(stripslashes($row->leader_name)).' on '.esc_html(stripslashes($row->whenwhere)).'<br/>';
	}
	$out.='</p>';
?>	