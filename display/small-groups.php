<?php
function church_admin_frontend_small_groups($member_type_id=1)
{
	global $wpdb;
	$wpdb->show_errors();
	$out='';
	
	$memb=explode(',',$member_type_id);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql=' WHERE '.implode(' || ',$membsql).'';}else{$memb_sql='';}
	//show small groups 
	$leader=array();
	
	$sql='SELECT * FROM '.CA_SMG_TBL .' ORDER BY smallgroup_order';
	$small_group=$sg=array();
	$results = $wpdb->get_results($sql);    
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$small_group[$row->id]='';
			$sg[$row->id]=$row->group_name;
		}
	
	
		//grab people
		$sql='SELECT CONCAT_WS(" ", first_name,last_name) AS name,smallgroup_id FROM '.CA_PEO_TBL.' '.$memb_sql;
		$peopleresults=$wpdb->get_results($sql);
		if(!empty($peopleresults))
		{
			foreach($peopleresults AS $people)
			{
				$s=maybe_unserialize($people->smallgroup_id);
				
				foreach($s AS $key=>$value){$small_group[$value][]=$people->name;}
				
			}
			
			foreach($small_group AS $id=>$people)
			{
				if(!empty($people))
				{
					if(!empty($sg[$id]))
					{
						$out.='<h3>'.esc_html($sg[$id]).'</h3><p>';
						$out.=esc_html(implode('<br/>',$people)).'</p>';
					}
				}
			}
		}
		
	}
	
	return $out;
}

?>