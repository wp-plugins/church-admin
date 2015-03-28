<?php
function church_admin_json()
{
	
	$action=stripslashes($_GET['ca_app']);
	if(!empty($action))
	{
		switch($action)
		{
			case 'login':echo 'Login please';break;
			case 'calendar':echo'Calendar';break;
			case 'media':echo'Media';break;
			case 'address-list':if(church_admin_check_auth()){echo church_admin_json_address();}else{echo'Not logged in';}break;
			case 'services':echo church_admin_json_services();break;
			case 'rota':echo church_admin_json_rota();break;
			case 'small-groups':echo'Small groups';break;
		}//end switch
	}
}	

function church_admin_check_auth()
{
	if(empty($_GET['username'])||empty($_GET['password']))return FALSE;
	$user = get_user_by( 'login', $_GET['username'] );
	if ( $user && wp_check_password( $_GET['password'], $user->data->user_pass, $user->ID) ){return TRUE;}else {return FALSE;}
}

function church_admin_json_address()
{
	global $wpdb;
	$output=array();
	$results=$wpdb->get_results('SELECT a.people_id,CONCAT_WS(" ", a.first_name,a.last_name) AS name, a.email AS email, b.phone AS phone, a.mobile AS mobile, b.address AS address FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND (a.member_type_id=1 OR a.member_type_id=3 OR a.member_type_id=7)');
	
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$output[contacts][]=array('id'=>$row->people_id,'name'=>$row->name,'email'=>$row->email,'phone'=>array('mobile'=>$row->mobile,'home'=>$row->phone),'address'=>$row->address);
		}
		return json_encode($output);
		//return $output;
	}
	
	
return json_encode($output);
}


function church_admin_json_services()
{
	global $wpdb, $days;
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	$output=array();
	if(!empty($services))
	{
		foreach($services AS $service)
		{
			$output['service']=array('service_name'=>$service->service_name,'service_day'=>$days[$service->service_day],'service_time'=>$service->service_time,'service_venue'=>$service->service_venue,'service_address'=>$service->service_address);
		}
		return json_encode($output);
	
	}
}

function church_admin_json_rota()
{
	global $wpdb;
	$output=$rota=array();
	//service details
	$output['service']=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="1"','ARRAY_A');
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
	$output['rota']=$rota;
	
	return json_encode($output);

}
?>