<?php
function church_admin_frontend_small_groups()
{
	global $wpdb;
	$wpdb->show_errors();
	//show small groups 
	$leader=array();
	$out='';
	$sql='SELECT * FROM '.CA_SMG_TBL;

	$results = $wpdb->get_results($sql);    
	foreach ($results as $row) 
	{
		$leaders=maybe_unserialize($row->leader);
		$ldr_names=array();
		if(is_array($leaders))foreach($leaders AS $key=>$value)$ldr_names[]=$wpdb->get_var('SELECT CONCAT_WS(" ", first_name,last_name) FROM '.CA_PEO_TBL.' WHERE people_id ="'.esc_sql($value).'"');
		$people_results=$wpdb->get_results('SELECT CONCAT_WS(" ", first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE smallgroup_id ="'.esc_sql($row->id).'"');
		$out.='<h3>'.$row->group_name;
		if(is_array($leaders))$out.=' led by '.implode(", ",$ldr_names);
		$out.='</h3><p>';
		if($people_results) foreach($people_results AS $people){$out.=$people->name.'<br/>';}
		$out.='</p>';
	}
	return $out;
}


?>	