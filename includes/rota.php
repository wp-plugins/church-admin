<?php
function church_admin_email_rota($service_id=1,$date=NULL)
{
        /**
 *
 * Emails out the rota 
 * 
 * @author  Andy Moyle
 * @param    $service_id,$date
 * @return   html string
 * @version  0.1
 * 
 */     
    echo '<h2>'.__('Email service rota','church-admin').'</h2>';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
    add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
    do_meta_boxes('church-admin','advanced',null);
    if(empty($date))$date=date('Y-m-d',strtotime('This Sunday'));
    global $wpdb;
    $rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
    $results=$wpdb->get_row('SELECT * FROM '.CA_ROT_TBL.' WHERE service_id="'.esc_Sql($service_id).'" AND rota_date="'.esc_sql($date).'"');
    if(!empty($results))
    {
	$rota_jobs=maybe_unserialize($results->rota_jobs);
	//build rota with jobs
	$message=__('Here is the rota for ','church-admin');
	if(!empty($rota_jobs))
	{
	    foreach($rota_tasks AS $task_row)
	    {
	        if(!empty($rota_jobs[$task_row->rota_id])) $message.='<p><label style="float:left;width:150px;font-weight:bold">'.esc_html($task_row->rota_task).'</label>'.esc_html(church_admin_get_people($rota_jobs[$task_row->rota_id])).'</p>';
	    }
	    
	}
	//grab unique people_ids
	$people_ids=array();
	
	foreach( $rota_jobs AS $key=>$value)
	{
	    if(!empty($value))
	    {
	    
		$jobs=maybe_unserialize($value);
		foreach($jobs AS $k=>$id)
		{
		    if(!in_array($id,$people_ids))$people_ids[]=$id;//only add unique ids
		}
	    }
	}
	//start emailing the message
	$download_link=site_url().'?download=service_rota&amp;service_id='.$service_id;
	if(!empty($people_ids))
	{
	    echo'<div class="updated fade"<p><strong>Building email list for service</strong></p></div>';
	    foreach($people_ids AS $key=>$people_id)
	    {
		$row=$wpdb->get_row('SELECT CONCAT_WS(" ", first_name,last_name) AS name, email FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
		if(!empty($row->email))
		{
		    
		    if(!empty($row->name))$email_content='<p>'.__('Dear','church-admin').$row->name.',</p>'.$message;
		    
		    
		    if(QueueEmail($row->email,"This week's service rota",$email_content,'',get_option('blogname'),get_option('admin_email'),'',''))echo'<p>Email to '.$row->name.' queued</p>';
		}
	    }
	}
    }
}
function church_admin_rota_list($service_id=NULL)
{
global$wpdb,$rota_order,$days;
//check rota settings!
$rota_jobs=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_jobs FROM ".$wpdb->prefix."church_admin_rota_settings");

$rota_list=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_list FROM ".$wpdb->prefix."church_admin_rotas");

if($rota_jobs>0&&$rota_list>0)
{
    echo '<h2>'.__('Rota List','church-admin').'</h2>';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
    add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
    do_meta_boxes('church-admin','advanced',null);
    //grab rota tasks
$taskresult=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings  ORDER by rota_order");
if(!empty($taskresult))
{
    if(!$service_id)
    {
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if($wpdb->num_rows==1)
	{
	    $service_id=1;
	}
	else
	{
	    echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
	    echo'<p><label>'.__('Which Service?','church-admin').'</label><select name="service_id">';
	    foreach($services AS $service)
	    {
		echo'<option value="'.$service->service_id.'">'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
	    }
	    echo'</select></p>';
	    echo'<p class="submit"><input type="submit" name="choose_service" value="'.__('Choose service','church-admin').' &raquo;" /></p></form></div>';
	}
    }
    if($service_id)
    {//service chosen
	//grab already set dates from db after today
	$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE rota_date>="'.date('Y-m-d').'" AND service_id="'.esc_sql($service_id).'" ORDER BY rota_date LIMIT 0,52 ';
   
	$results=$wpdb->get_results($sql);
	if($results)
	{
		//build rota tableheader
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	         echo'<h2>Rota  for  '.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</h2>';
	    echo '<table class="widefat">';
	    $thead='<tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th width="100">'.__('Date','church-admin').'</th>';
	    foreach($taskresult AS $taskrow)
	    {
	      $thead.='<th>'.esc_html($taskrow->rota_task).'</th>';
	    }
	    $thead.='</tr>';
	
	    echo'<thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
	    //end rota table header
    	
	    //grab results for each date
	    foreach($results AS $daterows)
	    {
		$new_rota=array();
	       $edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota&id='.$daterows->rota_id;
	        $delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota&id='.$daterows->rota_id;
		//start building row
		echo '<tr><td><a href="'.wp_nonce_url($edit_url, 'edit_rota').'">'.__('Edit','church-admin').'</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete_rota').'">'.__('Delete','church-admin').'</a></td><td>'.mysql2date('jS M Y',$daterows->rota_date).'</td>';
		//get rota task people for that date
		$rota_jobs =maybe_unserialize($daterows->rota_jobs);
		if(!empty($rota_jobs))
		{
		    foreach($rota_order AS $order=>$id)
		    {
			if(!empty($rota_jobs[$id]))
			    {
				if(!is_array(maybe_unserialize($rota_jobs[$id])))
				{//rota job is in old format
				    $new_rota[$id]=church_admin_get_people_id($rota_jobs[$id]);
				    echo'<td>'.esc_html(church_admin_get_people($new_rota[$id])).'</td>';
				}
				 echo'<td>'.esc_html(church_admin_get_people($rota_jobs[$id])).'</td>';
				
			    }
			    else
			    {
				echo'<td>&nbsp;</td>';
			    }
		    }
		    if(!empty($new_rota)){$wpdb->query('UPDATE '.CA_ROT_TBL.' SET rota_jobs ="'.esc_sql(seriealise($new_rota)).'" WHERE rota_id="'.esc_sql($date_rows->rota_id).'"');}
		}
		else
		{
		    echo'<td colspan="'.count($rota_order).'">'.__('No one is doing anything yet','church-admin').'</td>';    
		}
			    
		echo'</tr>';//finish building row	
		}
	    echo'</tbody>';
	    echo'</table>';
	}
    }
}//end of non empty rota tasks.			
}
//end of check for rota settings
else
{			

			
if ($rota_jobs==0) {
    echo'<div id="message" class="updated fade"><p><strong>';
    echo '<a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >'.__('You need to add some rota jobs first','church-admin').' &raquo;<a/></p></div>';
}
if ($rota_jobs>0 && $rota_list==0) {
    	church_admin_edit_rota();	
}

}//end of rota list function
    
    
}


function church_admin_edit_rota($id=NULL,$service_id=NULL)
{
    global $wpdb,$days,$rota_order;
   
    echo'';
    if(!$service_id)
    {
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if($wpdb->num_rows==1)
	{
	    $service_id=1;
	}
	else
	{
	    echo'<form action="" method="POST"><p><label>'.__('Which Service?','church_admin').'</label><select name="service_id">';
	    foreach($services AS $service)
	    {
		echo'<option value="'.$service->service_id.'">'.$service->service_name.' '.__('on','church_admin').' '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
	    }
	    echo'</select></p>';
	    echo'<p class="submit"><input type="submit" name="choose_service" value="'.__('Choose service','church_admin').' &raquo;" /></p></form></div>';
	}
    }
    if($service_id)
    {//service chosen
	$task_result=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	if(!empty($_POST['edit_rota']))
    	{
	    	    
	    if(!empty($_POST['rota_date']))
	    {
	        $date=date('Y-m-d',strtotime($_POST['rota_date']));
	    }
	    
	    $jobs=array();
	    foreach($task_result AS $task){$jobs[$task->rota_id]=church_admin_get_people_id($_POST[urlencode($task->rota_id)]);}
	    
	    if(!$id)
	    {
	        $sql='SELECT rota_id FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_date="'.esc_sql($date).'"AND service_id="'.esc_sql($service_id).'"';
	        $id=$wpdb->get_var($sql);
	    }
	    if(!empty($id))
	    {//update
	        $sql='UPDATE '.$wpdb->prefix.'church_admin_rotas SET rota_jobs="'.esc_sql(serialize($jobs)).'" WHERE rota_id="'.esc_sql($id).'" AND service_id="'.esc_sql($service_id).'"';
	    
	    }//end rota update
	    else
	    {//insert
	        $sql='INSERT INTO '.$wpdb->prefix.'church_admin_rotas (rota_jobs,rota_date,service_id)VALUES("'.esc_sql(serialize($jobs)).'","'.esc_sql($date).'","'.esc_sql($service_id).'")';
	    
	    }//end insert
	    
	    $wpdb->query($sql);
	    echo'<div class="wrap"><div class="updated fade"><p><strong>'.__('Rota updated','church_admin').' </strong></p></div>';
	    church_admin_rota_list($service_id);
	}
	else
	{//form
	    $jobs=$wpdb->get_row('SELECT * FROM '.CA_ROT_TBL.' WHERE rota_id="'.esc_sql($id).'"');
	    echo'<form id="churchAdminForm" name="churchAdminForm" action="" method="post">';
	    echo'<input type="hidden" name="service_id" value="'.$service_id.'"/>';
	    if(empty($jobs->rota_date))
	    {
	        
	        $next_date=$wpdb->get_var('SELECT DATE_ADD(MAX(rota_date), INTERVAL 7 DAY) FROM '.$wpdb->prefix.'church_admin_rotas LIMIT 1');
	        if(empty($next_date))$next_date=date("D, d MM yy",strtotime("next Sunday"));
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	         echo'<h2>Add to rota  for  '.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</h2>';
		echo'<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'#rota_date\').datepicker({dateFormat : "yy-mm-dd", changeYear: true });});</script>';
	
		 echo'<p><label>Rota Date:</label><input type="text" id="rota_date" name="rota_date" ';
	        if(!empty($next_date)) echo ' value="'.mysql2date("d M Y",$next_date).'" ';
		echo'/></p>';
	    
	    }else
	    {
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	        echo'<h2>Edit rota for '.mysql2date('d/m/Y',$jobs->rota_date).' and '.$service->service_name.' at '.$service->service_time.' '.$service->venue.'</h2>';
	    }
	    //grab different jobs
	    
	    
	    foreach($task_result as $task_row)
	    {
		$job=array();
	        if(!empty($jobs->rota_jobs))$job=unserialize($jobs->rota_jobs);
	        echo '<p><label>'.$task_row->rota_task.':</label>';
		if(!empty($task_row->department_id))
		{
		    $current=!empty($job[$task_row->rota_id])?$job[$task_row->rota_id]:NULL;
		    echo church_admin_autocomplete($task_row->rota_id,'friends'.$task_row->rota_task,'to'.$task_row->rota_task,$current,$task_row->department_id);
		}
		else
		{
		    if(!empty($job[$task_row->rota_id]))$curr_data=maybe_unserialize($job[$task_row->rota_id]);
		    $people=array();
		    if(!empty($curr_data)&&is_array($curr_data))
		    {
			foreach($curr_data AS $key=>$value)
			{
			    if(ctype_digit($value))
			    {//id
				$people[]=church_admin_get_person($value);
			    }//id
			    else
			    {//text
				$people[]=$value;
			    }//text
			}
		    }
		    echo'<input type="text" name="'.$task_row->rota_id.'"';
		    if(!empty($people))echo 'value="'.esc_html(implode(", ",$people)).'"';
		    echo'/>';
		}
		echo'</p>';
	}
	    echo'<p class="submit"><input type="submit" name="edit_rota" value="'.__('Save','church_admin').' &raquo;" /></p></form>';
	}//end form
    
    }//service chosen
    


}




function church_admin_delete_rota($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rotas WHERE rota_id='".esc_sql($id)."'");
    echo'<div class="updated fade"><p>'.__('Rota Deleted','church_admin').'</p></div>';
    church_admin_rota_list();
    
}

function church_admin_rota_csv($service_id=NULL)
{
global$wpdb,$rota_order;
$csv='';
//check rota settings!
$rota_jobs=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_jobs FROM ".$wpdb->prefix."church_admin_rota_settings");

$rota_list=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_list FROM ".$wpdb->prefix."church_admin_rotas");

if($rota_jobs>0&&$rota_list>0)
{
    //grab rota tasks
$taskresult=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings  ORDER by rota_order");
if(!empty($taskresult))
{
    if(!$service_id)
    {
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if($wpdb->num_rows==1)
	{
	    $service_id=1;
	}
	else
	{
	    echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
	    echo'<p><label>'.__('Which Service?','church-admin').'</label><select name="service_id">';
	    foreach($services AS $service)
	    {
		echo'<option value="'.$service->service_id.'">'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
	    }
	    echo'</select></p>';
	    echo'<p class="submit"><input type="submit" name="choose_service" value="'.__('Choose service','church-admin').' &raquo;" /></p></form></div>';
	}
    }
    $check=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
    if($service_id && $service_id==$check->service_id)
    {//service chosen
	//grab already set dates from db after today
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_date>="'.date('Y-m-d').'" AND service_id="'.esc_sql($service_id).'" ORDER BY rota_date LIMIT 0,52 ';
   
	$results=$wpdb->get_results($sql);
	if($results)
	{
	    $cols=array();
		//build rota tableheader
		$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	        foreach($taskresult AS $taskrow)
	    {
	      $cols[]='"'.esc_html($taskrow->rota_task).'"';
	    }
	    $csv.='"'.__('Date','church-admin').'",'.implode(',',$cols)."\r\n";

	    //end rota table header
    	
	    //grab results for each date
	    foreach($results AS $daterows)
	    {
	       $line=array();
		//start building row
		$line[]= '"'.mysql2date('jS M Y',$daterows->rota_date).'"';
		//get rota task people for that date
		$rota_jobs =maybe_unserialize($daterows->rota_jobs);
		
		
		if(!empty($rota_jobs))
		{
		    foreach($rota_order AS $order=>$id)
		    {
			if(!empty($rota_jobs[$id])) {$line[]='"'.$rota_jobs[$id].'"';}else {$line[]='""';}
		    }
		}
		if(!empty($line)){$csv.=implode(',',$line)."\r\n";}else{$csv.="\r\n";}
		}
	    
	}
	$filename="Rota-for-service-".$check->service_name.".csv";
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Type: text/csv");
	header("Content-Transfer-Encoding: binary");

	echo $csv;
	
    }
    else echo "<p>Not possible to download that rota </p>";
}//end of non empty rota tasks.			
}
  
    
}

?>