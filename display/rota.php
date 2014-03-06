<?php
function church_admin_front_end_rota()
{
    global $wpdb,$rota_order;
    
    $service_id=$_REQUEST['service_id'];
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    if($wpdb->num_rows==1)
    {//only one service
	$service_id=1;
    }//only one service
    elseif(!($_REQUEST['service_id']))
    {//choose service
	
	$out='<form action="" method="POST">';
	$out.='<p><label>'.__('Which Service?','church-admin').'</label><select name="service_id">';
	foreach($services AS $service)
	{
	    $out.='<option value="'.$service->service_id.'">'.esc_html($service->service_name.__('on','church-admin').' '.$days[$service->service_day].' '.__('at','church-admin').' '.$service->service_time.' '.$service->venue).'</option>';
	}
	$out.='</select></p>';
	$out.='<p class="submit"><input type="submit" name="choose_service" value="'.__('Choose service','church-admin').' &raquo;" /></p></form></div>';
    }//choose service
    if($service_id)
    {
	$sql='SELECT * FROM '.CA_ROT_TBL.'  WHERE rota_date>"'.date('Y-m-d').'" AND service_id="'.esc_sql($service_id).'" ORDER BY rota_date ASC LIMIT 1';
	
	$row=$wpdb->get_row($sql);
	$out='<p><a href="'.home_url().'/?download=rota&amp;rota='.wp_create_nonce('rota').'&amp;service_id='.$service_id.'">'.__('PDF Version of the rota for next 3 months','church-admin').'</a></p>';
	$out.='<p><a href="'.home_url().'/?download=rotacsv&amp;rotacsv='.wp_create_nonce('rotacsv').'&amp;service_id='.$service_id.'">'.__('Spreadsheet Version of the rota for next 3 months','church-admin').'</a></p>';
	
	$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	$out.='<h2>'.__('Who is doing what at the next ','church-admin').esc_html($service->service_name).' '.__('on','church-admin').' '.esc_html($days[$service->service_day]).' '.__('at','church-admin').' '.esc_html($service->service_time).' '.esc_html($service->venue).'</h2>';
	$rota_jobs =unserialize($row->rota_jobs);
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	if(!empty($rota_jobs))
	{
		$table='<table>';
	    foreach($rota_tasks AS $task_row)
	    {
			$people=church_admin_get_people($rota_jobs[$task_row->rota_id]);
	        if(!empty($people)) $table.='<tr><td><strong>'.esc_html($task_row->rota_task).'</strong></td><td>'.esc_html($people).'</td></tr>';
	    }
	    $table.='</table>';
	    $out.=$table;
	}
	else
	{
	    $out.='<p>'.__('No one is doing anything yet','church-admin').'</p>';    
	}
	
	
    }
    return $out;
}


?>
