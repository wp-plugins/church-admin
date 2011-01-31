<?php

$wpdb->show_errors();
//show small groups 
$leader=array();
$output='';
//grab groups that have leaders
$sql = "SELECT ".$wpdb->prefix."church_admin_smallgroup.*, CONCAT_WS(' ',".$wpdb->prefix."church_admin_directory.first_name,".$wpdb->prefix."church_admin_directory.last_name) AS leader_name FROM ".$wpdb->prefix."church_admin_smallgroup,".$wpdb->prefix."church_admin_directory WHERE ".$wpdb->prefix."church_admin_smallgroup.leader=".$wpdb->prefix."church_admin_directory.id";
$results = $wpdb->get_results($sql);
$gp=0;
foreach ($results as $row) 
	{
	$people=array();
	$_SESSION[leader][$gp]=stripslashes($row->group_name)." group";
	$output .= '<div  style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; margin:8px 0px; padding:6px; position: relative;">
	
			<span style="font-size:larger;"><strong>'.esc_html(stripslashes($row->group_name))." group led by ".esc_html(stripslashes($row->leader_name)).' on '.esc_html(stripslashes($row->whenwhere)).'</strong></span><br />';
			
	//get group members
	$members_sql=  "SELECT CONCAT_WS(' ',".$wpdb->prefix."church_admin_directory.first_name,".$wpdb->prefix."church_admin_directory.last_name) AS name FROM ".$wpdb->prefix."church_admin_directory WHERE ".$wpdb->prefix."church_admin_directory.small_group=".absint($row->id)." ORDER BY ".$wpdb->prefix."church_admin_directory.last_name";
	
	$member_results=$wpdb->get_results($members_sql);
	foreach($member_results AS $members_row)
	{
		$output.=esc_html($members_row->name).'<br/>';
		$people[]=$members_row->name;
	}
	$output.='</div>';
	$_SESSION['people'][$gp]=$people;
	$gp++;
	}
//add unattached people
$unattached=array();
$_SESSION[leader][$gp]='Unattached';
$output .= '<div  style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; margin:8px 0px; padding:6px; position: relative;"><span style="font-size:larger;"><strong>Unattached</strong></span><br/>';
	$members_sql=  "SELECT CONCAT_WS(' ',".$wpdb->prefix."church_admin_directory.first_name,".$wpdb->prefix."church_admin_directory.last_name) AS name FROM ".$wpdb->prefix."church_admin_directory WHERE ".$wpdb->prefix."church_admin_directory.small_group=1 ORDER BY ".$wpdb->prefix."church_admin_directory.last_name";
	$member_results=$wpdb->get_results($members_sql);
	foreach($member_results AS $members_row)
	{
		$output.=esc_html($members_row->name).'<br/>';
		$unattached[]=$members_row->name;
	}
	$_SESSION['people'][$gp]=$unattached;
	$output.='</div>';



$noofgroups=$wpdb->get_row("SELECT COUNT(id) AS no FROM ".$wpdb->prefix."church_admin_smallgroup");
	$_SESSION['pdf']=$noofgroups->no;
	$out.=$output;
?>	