<?php
function church_admin_json()
{
	
	$action=stripslashes($_GET['ca_app']);
	if(!empty($action))
	{
		switch($action)
		{
			case 'calendar':echo church_admin_json_calendar();break;
			case 'media':echo church_admin_json_media();break;
			case 'search':if(church_admin_check_auth()){echo church_admin_json_search();}else{echo'Not logged in';}break;
			case 'address-list':if(church_admin_check_auth()){echo church_admin_json_address();}else{echo'Not logged in';}break;
			case 'services':echo church_admin_json_services();break;
			case 'rota':echo church_admin_json_rota();break;
			case 'small-groups':echo church_admin_json_groups();break;
		}//end switch
	}
}	
function church_admin_json_groups()
{
	global $wpdb;
	$sql='SELECT * FROM '.CA_SMG_TBL;
	$results = $wpdb->get_results($sql);  
	if(!empty($results))
	{
		foreach ($results as $row) 
		{$output[]=array('name'=>esc_html($row->group_name),'whenwhere'=>esc_html($row->whenwhere),'address'=>esc_html($row->address),'lat'=>$row->lat,'lng'=>$row->lng);}
		return json_encode($output);
	}else 
	{
		$output=array('error'=>__('No small groups yet','church-admin'));
		return json_encode($output);
	}
}
function church_admin_json_calendar()
{
		global $wpdb;
		$output=array();
		if(!empty($_GET['date'])){$date=$_GET['date'];}else{$date=date('Y-m-d H:i:s');}
		$count=$wpdb->get_var('SELECT COUNT(event_id) FROM '.CA_DATE_TBL.' WHERE YEARWEEK(`start_date`, 1) = YEARWEEK(CURDATE(), 1)');
		$sql='SELECT * FROM '.CA_DATE_TBL.' WHERE  YEARWEEK(`start_date`, 1) = YEARWEEK("'.esc_sql($date).'", 1) ORDER BY start_date ASC';
		
		$results=$wpdb->get_results($sql);
		foreach($results AS $row)$output[]=(array)$row;
		return json_encode($output);
		
	
}
function church_admin_json_media()
{
		global $wpdb;
		$count=$wpdb->get_var('SELECT COUNT(file_id) FROM '.CA_FIL_TBL);
		$url=content_url().'/uploads/sermons/';
		$output=array('count'=>$count,'pages'=>ROUND($count/10));
		if(!empty($_GET['page'])){$page=(int)($_GET['page']-1)*10;}else{$page=1;}
		$sql='SELECT a.*,b.* FROM '.CA_FIL_TBL.' a, '.CA_SERM_TBL.' b WHERE a.series_id=b.series_id ORDER BY a.pub_date DESC LIMIT '.$page.',10';
		
		$results=$wpdb->get_results($sql);
		
		if(!empty($results))
		{
			foreach($results AS $row)$output['media'][]=array('title'=>esc_html($row->file_title),'id'=>intval($row->file_id),'description'=>esc_html($row->file_description),'file_url'=>esc_url($url.$row->file_name));
		}
		
		return json_encode($output);
}

function church_admin_check_auth()
{
	if(empty($_GET['username'])||empty($_GET['password']))return FALSE;
	$user = get_user_by( 'login', $_GET['username'] );
	if ( $user && wp_check_password( $_GET['password'], $user->data->user_pass, $user->ID) ){return TRUE;}else {return FALSE;}
}
function church_admin_json_search()
{
	global $wpdb;
	$output=array();
	 $s=esc_sql(stripslashes($_GET['search']));
	$sql='SELECT a.*,b.address AS address FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND (a.first_name LIKE("%'.$s.'%")||a.last_name LIKE("%'.$s.'%")||a.email LIKE("%'.$s.'%")||a.mobile LIKE("%'.$s.'%")||b.address LIKE("%'.$s.'%")||b.phone LIKE("%'.$s.'%"))';
    $results=$wpdb->get_results($sql);
	
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			if(empty($row->phone))$row->phone='';
			if(empty($row->mobile))$row->mobile='';
			$output[]=array('id'=>intval($row->people_id),'name'=>esc_html($row->first_name).' '.esc_html($row->last_name),'email'=>esc_html($row->email),'phone'=>array('mobile'=>esc_html($row->mobile),'home'=>esc_html($row->phone)),'address'=>esc_html($row->address));
		}
	}
return json_encode($output);
}

function church_admin_json_address()
{
	global $wpdb;
	$output=array();
	if(!empty($_GET['member_type_id']))
	{
		$memb=explode(',',$_GET['member_type_id']);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      
	}
	if(!empty($membsql)) {$memb_sql=' ('.implode(' || ',$membsql).')';}else{$memb_sql=' a.member_type_id=1 ';}
	$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND '.$membsql);
	$output=array('count'=>$count,'pages'=>ROUND($count/10));
	if(!empty($_GET['page'])){$page=(int)($_GET['page']-1)*10;}else{$page=1;}
	$results=$wpdb->get_results('SELECT a.people_id,CONCAT_WS(" ", a.first_name,a.last_name) AS name, a.email AS email, b.phone AS phone, a.mobile AS mobile, b.address AS address FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND (a.member_type_id=1 OR a.member_type_id=3 OR a.member_type_id=7) LIMIT '.$page.',10');
	
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$output['data'][]=array('id'=>intval($row->people_id),'name'=>esc_html($row->name),'email'=>esc_html($row->email),'phone'=>array('mobile'=>esc_html($row->mobile),'home'=>esc_html($row->phone)),'address'=>esc_html($row->address));
		}
		return json_encode($output);
		//return $output;
	}
return json_encode($output);
}


function church_admin_json_services()
{
	global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	$output=array();
	
	if(!empty($services))
	{
		foreach($services AS $service)
		{
			$output[]=array('name'=>esc_html($service->service_name),'day'=>esc_html($days[$service->service_day]),'time'=>esc_html($service->service_time),'service_venue'=>esc_html($service->venue),'address'=>esc_html($service->address));
		}
		return json_encode($output);
	
	}else 
	{
		$output=array('error'=>__('No one is doing anything yet','church-admin'));
		return json_encode($output);
	}
}

function church_admin_json_rota()
{
	global $wpdb;
	$output=$rota=array();
	//service details
	
	//rota details for upcoming sunday
	$sql='SELECT * FROM '.CA_ROT_TBL.'  WHERE rota_date>"'.date('Y-m-d').'" AND service_id="1" ORDER BY rota_date ASC LIMIT 1';
	$row=$wpdb->get_row($sql);
	
	if(!empty($row))
	{
		$rota_jobs=unserialize($row->rota_jobs);
		$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
		if(!empty($rota_jobs))
		{
			foreach($rota_tasks AS $task_row)
			{
				$people=church_admin_get_people($rota_jobs[$task_row->rota_id]);
				//match rota jobs to people doing them
				if(!empty($people)&&$people!=" ") $rota[$task_row->rota_task]=esc_html($people);
			}
	    }
	}
	else
	{
	    $rota['error']=__('No one is doing anything yet','church-admin');
	}
	$rota['date']=$row->rota_date;
	$output=$rota;
	
	return json_encode($output);

}
?>