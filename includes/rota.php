<?php

function church_admin_rota_list()
{
global$wpdb;
//check rota settings!
$rota_jobs=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_jobs FROM ".$wpdb->prefix."church_admin_rota_settings");

$rota_list=$wpdb->get_var("SELECT COUNT(rota_id) AS rota_list FROM ".$wpdb->prefix."church_admin_rota");

if($rota_jobs>0&&$rota_list>0)
{
    echo '<h2>Rota List</h2>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_rota_settings_list","rota_settings_list").'">View/Edit Rota Jobs</a></p>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >Add more rota jobs</a></p>
    <p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_to_rota",'add_to_rota').'">Add to rota</a></p>';
//grab rota tasks
$taskresult=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings  ORDER by rota_id");
if(!empty($taskresult))
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
    	
    //grab already set dates from db after today
    $results=$wpdb->get_results("SELECT DISTINCT rota_date FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date>='".date('Y-m-d')."' ORDER BY rota_date LIMIT 0,24 ");
    //grab results for each date
    foreach($results AS $daterows)
    {
        $edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota&date='.$daterows->rota_date;
        $delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota&date='.$daterows->rota_date;
        //start building row
        echo '<tr><td><a href="'.wp_nonce_url($edit_url, 'edit_rota').'">[Edit]</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete_rota').'">[Delete]</a></td><td>'.mysql2date('jS M Y',$daterows->rota_date).'</td>';
        //get rota task people for that date
        $dateresults=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date='".$daterows->rota_date."' ORDER by rota_option_id");
        foreach ($dateresults AS $daterow)
        {
		echo'<td>'.esc_html($daterow->who).'</td>';
        }
        echo'</tr>';//finish building row	
        }
	echo'</tbody>';
        echo'</table>';
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
    echo 'You need to add some rota dates.</strong><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_to_rota",'add_to_rota').'">Continue &raquo;<a/></p>
       </div>';			
}

}//end of rota list function
    
    
}
function church_admin_add_to_rota()
{
global $wpdb;
//check rota settings!
$check=$wpdb->get_var("SELECT COUNT(rota_id) AS numb FROM ".$wpdb->prefix."church_admin_rota_settings");
if(isset($_POST['add_to_rota'])&&check_admin_referer('add_to_rota'))
{
    //form processed
    //check to see if that date is already set
    $set=$wpdb->get_row("SELECT COUNT(rota_id) AS no FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date='".esc_sql($_POST['rota_date'])."'");
    if($set->no>0){$sqltype='updated';}else{$sqltype='added';}
    //grab hold of rota task ids
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings");
    //process post array on basis of results expected
    foreach($results as $row)
    {
	switch($sqltype)
	{
	case updated:
	//update if entry exist or insert empty data if not!
            if(isset($_POST[$row->rota_id]))	
	    {
                $sql="UPDATE ". $wpdb->prefix."church_admin_rota SET who='".esc_sql($_POST[$row->rota_id])."' WHERE rota_date='".esc_sql($_POST['rota_date'])."' AND rota_option_id='".esc_sql($row->rota_id)."'";
            }
	    else
            {
		$sql= "INSERT INTO ". $wpdb->prefix."church_admin_rota SET rota_date='".esc_sql($_POST['rota_date'])."',rota_option_id='".esc_sql($row->rota_id)."', who=' '";
            }
	break;
	default:				
            $sql= "INSERT INTO ". $wpdb->prefix."church_admin_rota SET rota_date='".esc_sql($_POST['rota_date'])."',rota_option_id='".esc_sql($row->rota_id)."', who='".esc_sql($_POST[$row->rota_id])."'";
	break;
	}
	$wpdb->query($sql);
    }
    church_admin_rota_list();

    
}
else
//only proceed if there are some rota tasks		
if(!empty($check))
{
        echo '<div class="wrap church_admin"><script type="text/javascript" src="'.CHURCH_ADMIN_INCLUDE_URL.'javascript.js"></script><script type="text/javascript">document.write(getCalendarStyles());</script>';
echo'<h2>Rota add</h2><p>Select a date to add to the rota...</p><form name="event_add" id="event_add" action="" method="post">';
if ( function_exists('wp_nonce_field') ) wp_nonce_field('add_to_rota');
    church_admin_rota_task_form();
    echo'<p class="submit"><input type="submit" name="add_to_rota" value="Add Rota for that date &raquo;" /></p></form></div>';
}
//end of check for rota settings
else
{
    echo'<div id="message" class="updated fade"><p><strong>Your need to add some rota jobs first .</strong><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php?action=church_admin_add_rota_settings",'add_rota_settings').'">Continue &raquo;<a/></p></div>';
}

}

//end of add to rota function

function church_admin_rota_task_form($data='null')
{
    global $wpdb;
        //js for date picker

//datepicker js
echo'<script type="text/javascript">var cal_begin = new CalendarPopup(\'pop_up_cal\');function unifydates() {document.forms[\'event_add\'].event_end.value = document.forms[\'quoteform\'].rota_date.value;}</script>
<ul><li><label>Rota Date (yyyy-mm-dd):</label><input type="text" name="rota_date" class="input" size="12" value="'.date('Y-m-d',strtotime("next Sunday")).'" /><a href="#" onClick="cal_begin.select(document.forms[\'event_add\'].rota_date,\'rota_date_anchor\',\'yyyy-MM-dd\'); return false;" name="rota_date_anchor" id="rota_date_anchor">Select date</a><div id="pop_up_cal" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></li>';
//grab different jobs
$task_result=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings");
foreach($task_result as $task_row)
{
    echo '<li><label>'.$task_row->rota_task.':</label><input type="text" name='.$task_row->rota_id.' value=""/></li>';
}
}

function church_admin_delete_rota($date)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date='".esc_sql($date)."'");
    church_admin_rota_list();
    
}
function church_admin_edit_rota($date)
{
    
global $wpdb;    
$date=esc_sql($date);
$htmldate=mysql2date('d-m-Y', $date, $translate = true);
if(isset($_POST['rota_edited'])&&check_admin_referer('edit_rota'))
{
//grab hold of rota task ids
$results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings");
//process post array on basis of results expected
foreach($results as $row)
{
    $sql="UPDATE ". $wpdb->prefix."church_admin_rota SET who='".esc_sql($_POST[$row->rota_id])."' WHERE rota_date='".$date."' AND rota_option_id='".esc_sql($row->rota_id)."'";
$wpdb->query($sql);
}
church_admin_rota_list();
}//end of already edited
else
{//print editing form
echo'<div class="wrap church_admin"> <h2>Edit rota for '.$htmldate.'</h2><form name="rota_edit" id="rota_edit" action="" method="post">';
//grab different jobs
    $task_result=$wpdb->get_results("SELECT ".$wpdb->prefix."church_admin_rota.*,".$wpdb->prefix."church_admin_rota_settings.rota_task FROM ".$wpdb->prefix."church_admin_rota,".$wpdb->prefix."church_admin_rota_settings WHERE rota_date='".$date."' AND ".$wpdb->prefix."church_admin_rota.rota_option_id=".$wpdb->prefix."church_admin_rota_settings.rota_id");
foreach($task_result as $task_row)
{
echo '<ul><li><label>'.$task_row->rota_task.':</label><input type="text" name="'.$task_row->rota_option_id.'" value="'.esc_html($task_row->who).'"/></li></ul>';
}
echo'<p class="submit"><input type="hidden" value="y" name="rota_edited"><input type="submit" value="Update Rota for  '.$htmldate.' &raquo;" /></p></form></div>';
}//end print editing form
    
    
}
?>