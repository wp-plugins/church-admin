<?php

function church_admin_rota_list()
{
global$wpdb;
//check rota settings!
$rota_jobs=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_jobs FROM ".$wpdb->prefix."church_admin_rota_settings");

$rota_list=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_list FROM ".$wpdb->prefix."church_admin_rotas");

if($rota_jobs>0&&$rota_list>0)
{
    echo '<h2>Rota List</h2>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_rota_settings_list","rota_settings_list").'">View/Edit Rota Jobs</a></p>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >Add more rota jobs</a></p>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota",'edit_rota').'">Add to rota</a></p>';
//grab rota tasks
$taskresult=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings  ORDER by rota_id");
if(!empty($taskresult))
{
    
    //grab already set dates from db after today
    $sql="SELECT * FROM ".$wpdb->prefix."church_admin_rotas WHERE rota_date>='".date('Y-m-d')."' ORDER BY rota_date LIMIT 0,52 ";
   
    $results=$wpdb->get_results($sql);
    if($results)
    {
	//build rota tableheader
	echo '<table class="widefat">';
	$thead='<tr><th>Edit</th><th>Delete</th><th width="100">Date</th>';
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
	    $edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota&id='.$daterows->rota_id;
	    $delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota&id='.$daterows->rota_id;
	    //start building row
	    echo '<tr><td><a href="'.wp_nonce_url($edit_url, 'edit_rota').'">[Edit]</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete_rota').'">[Delete]</a></td><td>'.mysql2date('jS M Y',$daterows->rota_date).'</td>';
	    //get rota task people for that date
	    
	    $jobs=unserialize($daterows->rota_jobs);
	    foreach($jobs AS $title=>$who)echo'<td>'.esc_html($who).'</td>';
	    
	    echo'</tr>';//finish building row	
	    }
	echo'</tbody>';
        echo'</table>';
    }
}//end of non empty rota tasks.			
}
//end of check for rota settings
else
{			
echo'<div id="message" class="updated fade"><p><strong>';
			
if ($rota_jobs==0) {
    echo 'You need to add some rota jobs first and then <a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >Continue &raquo;<a/></p></div>';
}
if ($rota_jobs>0 && $rota_list==0) {
    echo 'You need to add some rota dates.</strong><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota",'edit_rota').'">Continue &raquo;<a/></p>
       </div>';			
}

}//end of rota list function
    
    
}


function church_admin_edit_rota($id=NULL)
{
    global $wpdb;
    
    $task_result=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings");
    if(!empty($_POST['edit_rota']))
    {
	
	if(!empty($_POST['rota_date']))
	{
	$d=explode("/",$_POST['rota_date']);
	
	$date=$d[2].'-'.$d[1].'-'.$d[0];
	}
	
	$jobs=array();
	foreach($task_result AS $task){$jobs[$task->rota_task]=stripslashes($_POST[urlencode($task->rota_task)]);}
	if(!$id)
	{
	    $sql='SELECT rota_id FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_date="'.esc_sql($date).'"';
	    $id=$wpdb->get_var($sql);
	}
	if(!empty($id))
	{//update
	    $sql='UPDATE '.$wpdb->prefix.'church_admin_rotas SET rota_jobs="'.esc_sql(serialize($jobs)).'" WHERE rota_id="'.esc_sql($id).'"';
	    
	}//end rota update
	else
	{//insert
	    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_rotas (rota_jobs,rota_date,service_id)VALUES("'.esc_sql(serialize($jobs)).'","'.esc_sql($date).'","1")';
	    
	}//end insert
	
	$wpdb->query($sql);
	echo'<div class="wrap"><div class="updated fade"><p><strong>Rota updated </strong></p></div>';
	church_admin_rota_list();
    }
    else
    {//form
	$jobs=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_id="'.esc_sql($id).'"');
	echo'<div class="wrap church_admin"><form id="rota" name="rota" action="" method="post">';
	if(empty($jobs->rota_date))
	{
	    
	    $next_date=$wpdb->get_var('SELECT DATE_ADD(MAX(rota_date), INTERVAL 7 DAY) FROM '.$wpdb->prefix.'church_admin_rotas LIMIT 1');
	    if(empty($next_date))$next_date=date('d/m/Y',strtotime("next Sunday"));
	     echo'<h2>Add to rota </h2><p><label>Rota Date (dd/mm/YYYY):</label><input type="text" name="rota_date" value="'.mysql2date('d/m/Y',$next_date).'" /></p>';
	    
	}else
	{
	    echo'<h2>Edit rota for '.mysql2date('d/m/Y',$date).'</h2>';
	}
	//grab different jobs
	
	foreach($task_result as $task_row)
	{
	    $job=unserialize($jobs->rota_jobs);
	    echo '<p><label>'.$task_row->rota_task.':</label><input type="text" name="'.urlencode($task_row->rota_task).'" value="'.$job[$task_row->rota_task].'"/></p>';
	}
	echo'<p class="submit"><input type="submit" name="edit_rota" value="Save &raquo;" /></p></form></div>';
    }//end form
    
    


}




function church_admin_delete_rota($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rotas WHERE rota_id='".esc_sql($id)."'");
    echo'<div class="updated fade"><p>Rota Deleted</p></div>';
    church_admin_rota_list();
    
}

?>