<?php
function church_admin_front_end_rota()
{
    global $wpdb;
    $service_id=$_REQUEST['service_id'];
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    if($wpdb->num_rows==1)
    {//only one service
	$service_id=1;
    }//only one service
    elseif(!($_REQUEST['service_id']))
    {//choose service
	
	$out='<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
	$out.='<p><label>Which Service?</label><select name="service_id">';
	foreach($services AS $service)
	{
	    $out.='<option value="'.$service->service_id.'">'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
	}
	$out.='</select></p>';
	$out.='<p class="submit"><input type="submit" name="choose_service" value="Choose service &raquo;" /></p></form></div>';
    }//choose service
    if($service_id)
    {
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_rotas  WHERE rota_date>"'.date('Y-m-d').'" AND service_id="'.esc_sql($service_id).'" LIMIT 1';
	$row=$wpdb->get_row($sql);
	$out='<p><a href="'.home_url().'/?download=rota&amp;service_id='.$service_id.'">PDF Version of the rota</a></p>';
	$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	$out.='<h2>Who is doing what at'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</h2><table>';
	$rota_jobs =unserialize($row->rota_jobs);
	if(!empty($rota_jobs))
	{
	    foreach($rota_jobs AS $job=>$who)
	    {
                if(!empty($who))$out.='<tr><td>'.$job.'</td><td>'.$who.'</td></tr>';
	    }
	}
	else
	{
	    $out.='<tr><td colspan="2">No one is doing anything on '.mysql2date('d-m-Y', $date, $translate = true).'</td></tr>';    
	}
	$out.='</table>';
	
    }
    return $out;
}

/*
global$wpdb;
$wpdb->show_errors();
$nonce=$_POST['_wpnonce'];


if(isset($_POST['date'])&&wp_verify_nonce($nonce,'rota list') && checkDateFormat($_POST['date'])) {$date=$_POST['date'];}else{$date=date('Y-m-d',strtotime("this Sunday"));}
$htmldate=mysql2date('d-m-Y', $date, $translate = true);
$sql="SELECT * FROM ".$wpdb->prefix."church_admin_rotas  WHERE rota_date='".esc_sql($date)."'";

$row=$wpdb->get_row($sql);

$out.='<p><a href="'.home_url().'/?download=rota">PDF Version of the rota</a></p>';

	$out.= '<div id="wrap"><table><tr><th>Who\'s doing what on</th><th> ';
	
	//grab dates in db
$dateresults=$wpdb->get_results("SELECT DISTINCT rota_date FROM ".$wpdb->prefix."church_admin_rotas WHERE rota_date>'".date('Y-m-d')."'");
$out.='<form action="" method="post">';
if ( function_exists('wp_nonce_field') )$out.=wp_nonce_field('rota list');
$out.='<select name="date">';
if(isset($date)) $out.=	'<option value="'.$date.'">'.mysql2date('l, F j, Y', $date, $translate = true).'</option>';	
foreach($dateresults AS $daterow)
	{
	if($daterow->rota_date!=$date) $out.='<option value="'.$daterow->rota_date.'">'.mysql2date('l, F j, Y', $daterow->rota_date, $translate = true).'</option>';
	}
$out.='</select><input type="submit" value="Select date &raquo;" /></form></th></tr>';
	

if(!empty($row)) 
{
    $jobs=unserialize($row->rota_jobs);
    foreach($jobs AS $job=>$who)
    {
        
        if(!empty($who))$out.='<tr><td>'.$job.'</td><td>'.$who.'</td></tr>';
    }
    
}else
{
$out.='<tr><td colspan="2">No one is doing anything on '.mysql2date('d-m-Y', $date, $translate = true).'</td></tr>';    
}
$out.='</table></div>';
 */

?>