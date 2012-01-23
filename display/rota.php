<?php
global$wpdb;
$wpdb->show_errors();
$nonce=$_POST['_wpnonce'];


if(isset($_POST['date'])&&wp_verify_nonce($nonce,'rota list') && checkDateFormat($_POST['date'])) {$date=$_POST['date'];}else{$date=date('Y-m-d',strtotime("this Sunday"));}
$htmldate=mysql2date('d-m-Y', $date, $translate = true);

$results=$wpdb->get_results("SELECT ".$wpdb->prefix."church_admin_rota.*,".$wpdb->prefix."church_admin_rota_settings.rota_task FROM ".$wpdb->prefix."church_admin_rota,".$wpdb->prefix."church_admin_rota_settings WHERE ".$wpdb->prefix."church_admin_rota.rota_date='".esc_sql($date)."' AND ".$wpdb->prefix."church_admin_rota.rota_option_id=".$wpdb->prefix."church_admin_rota_settings.rota_id");

$out.='<p><a href="'.home_url().'/?download=rota">PDF Version of the rota</a></p>';

	$out.= '<div id="wrap"><table><tr><th>Who\'s doing what on</th><th> ';
	
	//grab dates in db
$dateresults=$wpdb->get_results("SELECT DISTINCT rota_date FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date>'".date('Y-m-d')."'");
$out.='<form action="" method="post">';
if ( function_exists('wp_nonce_field') )$out.=wp_nonce_field('rota list');
$out.='<select name="date">';
if(isset($date)) $out.=	'<option value="'.$date.'">'.mysql2date('l, F j, Y', $date, $translate = true).'</option>';	
foreach($dateresults AS $daterow)
	{
	if($daterow->rota_date!=$date) $out.='<option value="'.$daterow->rota_date.'">'.mysql2date('l, F j, Y', $daterow->rota_date, $translate = true).'</option>';
	}
$out.='</select><input type="submit" value="Select date &raquo;" /></form></th></tr>';
	

if(!empty($results)) 
{
foreach($results AS $row)
	{
	$out.= '<tr><td><strong>'.esc_html($row->rota_task).'</strong></td><td> '.esc_html($row->who).'</td></tr>';
	}

}else
{
$out.='<tr><td colspan="2">No one is doing anything on '.mysql2date('d-m-Y', $date, $translate = true).'</td></tr>';    
}
$out.='</table></div>';


?>