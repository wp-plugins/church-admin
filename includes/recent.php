<?php
function church_admin_recent_display($month)
{
    global $wpdb;
	$member_type=church_admin_member_type_array();
    foreach($member_type AS $type_id=>$type)
    {
        $sql='SELECT a.*,b.* FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND a.last_update>DATE_SUB(NOW(), INTERVAL '.esc_sql($month).' MONTH) AND a.member_type_id ="'.esc_sql($type_id).'"';
        $results=$wpdb->get_results($sql);
        if($results)
        {
            echo'<h2>'.$type.' '.__('activity for last','church-admin').' '.$month.' '.__('month(s)','church-admin').'</h2>';
            echo'<table><thead><tr><th>'.__('Date','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Phone','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Next Action','church-admin').'</th><th>'.__('Assign to','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Date','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Phone','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Next Action','church-admin').'</th><th>'.__('Assign to','church-admin').'</th></tr></tfoot><tbody>';
            foreach($results AS $row)
            {
                $assign=$next_action='coming soon';
                $address=implode(', ',array_filter(unserialize($row->address)));
                echo'<tr><td>'.mysql2date(get_option('date_format'),$row->last_update).'</td><td>'.esc_html($row->first_name.', '.$row->last_name).'</td><td>'.esc_html($address).'</td><td>'.esc_html($row->mobile).'</td><td>'.esc_html($row->phone).'</td><td>'.esc_html($row->email).'</td><td>'.$next_action.'</td><td>'.$assign.'</td></tr>';
            }
            echo'</tbody></table>';
        }
    }
    
    
}

function church_admin_recent_visitors($member_type_id=1)
{
	global $wpdb;
	$out='';
	$memb=explode(',',$member_type_id);
    foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
    if(!empty($membsql)) {$memb_sql='('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	$member_type=$wpdb->get_var('SELECT member_type FROM '.CA_MTY_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"');
	$results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE '.$memb_sql.' ORDER BY last_updated DESC');
	
	if(!empty($results))
	{
		$out.='<h1>Recent "'.$member_type.'"</h1>';
		$recent_dates=array();
		foreach($results AS $row)
		{
			$member_data=maybe_unserialize($row->member_data);
			
			$date=$member_data[$member_type];
			
			if(!empty($date) &&$date!='0000-00-00')$recent_dates[$date][]=array('people_id'=>$row->people_id,'first_name'=>$row->first_name,'last_name'=>$row->last_name,'household_id'=>$row->household_id);
			
		}
		//arsort(array_filter($recent_dates));
		
		foreach($recent_dates AS $service_date=>$people)
		{
			
			$out.='<h2>'.mysql2date(get_option('date_format'),$service_date).'</h2>';
			foreach($people AS $person)
			{
			
				$out.='<p><a title="Edit person" href="'.wp_nonce_url('admin.php?page=church-admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.intval($person['people_id']),'edit_people').'">'.esc_html($person['first_name']).'</a> <a href="'.wp_nonce_url('admin.php?page=church-admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.intval($person['household_id']),'edit_household').'" title="edit household">'.esc_html($person['last_name']).'</a></p>';
				
			}
			$out.='<p>&nbsp;</p>';
		
		
		}
	}
	return $out;
}
?>