<?php
function church_admin_frontend_small_groups($member_type_id=1)
{
	global $wpdb;
	$wpdb->show_errors();
	$out='';
	
	$memb=explode(',',$member_type_id);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	//show small groups 
	$leader=array();
	
	$sql='SELECT * FROM '.CA_SMG_TBL;

	$results = $wpdb->get_results($sql);    
	foreach ($results as $row) 
	{
		$leaders=maybe_unserialize($row->leader);
		$ldr_names=array();
		if(is_array($leaders))foreach($leaders AS $key=>$value)$ldr_names[]=$wpdb->get_var('SELECT CONCAT_WS(" ", first_name,last_name) FROM '.CA_PEO_TBL.' WHERE people_id ="'.esc_sql($value).'"');
		$sql='SELECT CONCAT_WS(" ", first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE smallgroup_id ="'.esc_sql($row->id).'"'.$memb_sql;
		
		$people_results=$wpdb->get_results($sql);
		$out.='<h3>'.esc_html($row->group_name);
		if(!empty($leaders)&&is_array($leaders))$out.=' '.__('led by','church-admin').' '.esc_html(implode(", ",$ldr_names));
		$out.='</h3><p>';
		if($people_results) foreach($people_results AS $people){$out.=esc_html($people->name).'<br/>';}
		$out.='</p>';
	}
	return $out;
}

?>	
