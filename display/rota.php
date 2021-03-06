<?php
function church_admin_front_end_rota()
{
    global $wpdb,$rota_order,$days;
    $out='';
    if(!empty($_REQUEST['service_id'])){$service_id=$_REQUEST['service_id'];}else{$service_id=1;}
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    if($wpdb->num_rows==1)
    {//only one service
	$service_id=1;
    }//only one service
    elseif(!($_REQUEST['service_id']))
    {//choose service
	
		$out.='<form action="" method="POST">';
		$out.='<p><label>'.__('Which Service?','church-admin').'</label><select name="service_id">';
		foreach($services AS $service)
		{
			$out.='<option value="'.intval($service->service_id).'">'.esc_html($service->service_name).__('on','church-admin').' '.esc_html($days[$service->service_day]).' '.__('at','church-admin').' '.esc_html($service->service_time).' '.esc_html($service->venue).'</option>';
		}
		$out.='</select></p>';
		$out.='<p class="submit"><input type="submit" name="choose_service" value="'.__('Choose service','church-admin').' &raquo;" /></p></form></div>';
    }//choose service
    if($service_id)
    {
	$sql='SELECT * FROM '.CA_ROT_TBL.'  WHERE rota_date>"'.date('Y-m-d').'" AND service_id="'.esc_sql($service_id).'" ORDER BY rota_date ASC LIMIT 1';
	
	$row=$wpdb->get_row($sql);
	
	
	$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	
	$out.='<h3>'.sprintf(__('Who is doing what at the next %1$s on %2$s at the %3$s','church-admin'),esc_html($service->service_name),esc_html($days[$service->service_day]),esc_html($service->service_time.' '.$service->venue)).'</h3>';
	$out.='<p><a href="'.home_url().'/?download=rota&amp;rota='.wp_create_nonce('rota').'&amp;service_id='.intval($service_id).'">'.__('PDF Version of the rota for next 3 months','church-admin').'</a></p>';
	$out.='<p><a href="'.home_url().'/?download=rotacsv&amp;rotacsv='.wp_create_nonce('rotacsv').'&amp;service_id='.intval($service_id).'">'.__('Spreadsheet Version of the rota for next 3 months','church-admin').'</a></p>';
	$rota_jobs =unserialize($row->rota_jobs);
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	if(!empty($rota_jobs))
	{
		$table='<table>';
	    foreach($rota_tasks AS $task_row)
	    {
			$people=church_admin_get_people($rota_jobs[$task_row->rota_id]);
			$services=maybe_unserialize($task_row->service_id);
			if(is_array($services) && in_array($service_id,$services))
			{
				if(!empty($people)&&$people!=" ") $table.='<tr><td><strong>'.esc_html($task_row->rota_task).'</strong></td><td>'.esc_html($people).'</td></tr>';
			}
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