<?php
function church_admin_small_group_list()
{
	global $wpdb;
	$wpdb->show_errors();
	//show small groups 
	$out.='<p>';
	$sql='SELECT * FROM '.CA_SMG_TBL;
	
	$results = $wpdb->get_results($sql);    
	foreach ($results as $row) 
	{
		$leaders=maybe_unserialize($row->leader);
		$ldr_names=array();
		if(is_array($leaders))foreach($leaders AS $key=>$value)$ldr_names[]=$wpdb->get_var('SELECT CONCAT_WS(" ", first_name,last_name) FROM '.CA_PEO_TBL.' WHERE people_id ="'.esc_sql($value).'"');
		$out .= esc_html(stripslashes($row->group_name)).' ';
		if(!empty($leaders)) $out.=" ".__('group led by ','church-admin')." ".esc_html(stripslashes(implode(", ",$ldr_names)));
		if(!empty($row->whenwhere))$out.=' '.__('on','church-admin').' '.esc_html(stripslashes($row->whenwhere)).'<br/>';
	}
	$out.='</p>';

	return $out;
}
?>	