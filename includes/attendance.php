<?php
/*
 Adds attendance figures
church_admin_show_rolling_average()
church_admin_show_graph()
church_admin_add_attendance()

*/
function church_admin_attendance_list($service_id=1)
{
     global $wpdb,$days;
     $wpdb->show_errors();
    //grab address list in order
    $items = $wpdb->get_var('SELECT COUNT(*) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
    
    
    // number of total rows in the database
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'pagination.class.php');
    if($items > 0)
    {
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	$p->target("admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list");
	if(!isset($p->paging))$p->paging=1; 
	if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset($_GET['paging']))
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = $_GET['paging'];
	}
        //Query for limit paging
	$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
    } 
    
    //prepare WHERE clause using given service_id
    $sql='SELECT * FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" ORDER BY `date` DESC '.$limit;
    $results=$wpdb->get_results($sql);
    if($results)
     {
	   $sql='SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"';
	  
	  $service=$wpdb->get_row($sql);
	  $service_details=$service->service_name.' '.__('on','church-admin').' '.$days[$service->service_day].' '.$service->service_time;
	  echo'<div class="wrap church_admin"><h2>'.__('Attendance List for','church-admin').' '.$service_details.'</h2>';
	  echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance','edit_attendance').'">'.__('Add attendance','church-admin').'</a>';
	  // Pagination
	  echo '<div class="tablenav"><div class="tablenav-pages">';
	  echo $p->show();  
	  echo '</div></div>';
	  //Pagination
	  echo '<table class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Adults','church-admin').'</th><th>'.__('Children','church-admin').'</th><th>'.__('Total','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Date','church-admin').'</th><th>'.__('Adults','church-admin').'</th><th>'.__('Children','church-admin').'</th><th>'.__('Total','church-admin').'</th></tr></tfoot><tbody>';
	  foreach($results AS $row)
	  {
	       $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance&amp;attendance_id='.$row->attendance_id,'edit_attendance').'">'.__('Edit','church-admin').'</a>';
	       $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_attendance&amp;attendance_id='.$row->attendance_id,'delete_attendance').'">'.__('Delete','church-admin').'</a>';
	       $total=$row->adults+$row->children;
	       echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.mysql2date(get_option('date_format'),$row->date).'</td><td>'.$row->adults.'</td><td>'.$row->children.'</td><td>'.$total.'</td></tr>';
	  }
	  echo'</tbody></table></div>';
     }
}
function church_admin_show_rolling_average()
{   global $wpdb;
     include(CHURCH_ADMIN_INCLUDE_PATH."rolling-average-graph.php");
    echo '<p><img src="'.CHURCH_ADMIN_CACHE_URL.'rolling_average_attendance.png"  width="1000" height="500" alt="Rolling Average Graph"/></p>';

}

function church_admin_show_graph()
{   global $wpdb;
    include(CHURCH_ADMIN_INCLUDE_PATH."attendance-graph.php");
    echo '<p><img src="'.CHURCH_ADMIN_CACHE_URL.'attendance-graph.png" alt="attendance graph" width="1000" height="500" /></p>';

}

function church_admin_edit_attendance($attendance_id){
  global $wpdb,$days;
  
  $wpdb->show_errors();
  $data=$wpdb->get_row('SELECT * FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');
  //print_r($data);
if(isset($_POST['edit_att']))
{
  $sql=array();
     if(ctype_digit($_POST['adults'])){$sqlsafe['adults']=esc_sql($_POST['adults']);}else{$sqlsafe['adults']=0;}
     if(ctype_digit($_POST['children'])){$sqlsafe['children']=esc_sql($_POST['children']);}else{$sqlsafe['children']=0;}
     if(ctype_digit($_POST['service_id'])){$sqlsafe['service_id']=esc_sql($_POST['service_id']);}else{$sqlsafe['service_id']=1;}
     $sqlsafe['date']=date('Y-m-d',strtotime($_POST['add_date']));
     //print_r($sql);
     if(!$attendance_id){$attendance_id=$wpdb->get_var('SELECT attendance_id FROM '.CA_ATT_TBL.' WHERE service_id="'.$sqlsafe['service_id'].'" AND `date`="'.$sqlsafe['date'].'" AND adults="'.$sqlsafe['adults'].'" AND children="'.$sqlsafe['children'].'"');  }
     if($attendance_id)
     {//update
	 $sql='UPDATE '.CA_ATT_TBL.' SET  `date`="'.$sqlsafe['date'].'" , adults="'.$sqlsafe['adults'].'" , children="'.$sqlsafe['children'].'",service_id="'.$sqlsafe['service_id'].'" WHERE attendance_id="'.esc_sql($attendance_id).'"';
     }//update
     else
     {//insert
	  $sql='INSERT INTO '.CA_ATT_TBL.' (date,adults,children,service_id) VALUES("'.$sqlsafe['date'].'","'.$sqlsafe['adults'].'","'.$sqlsafe['children'].'","'.$sqlsafe['service_id'].'")';
     }//insert
     
     $wpdb->query($sql)  ;
     $attendance_id=$wpdb->insert_id;
     //work out rolling average from values!

     $avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.CA_ATT_TBL.' WHERE `date` >= DATE_SUB("'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'"';
    $averow=$wpdb->get_row($avesql);

     //update table with rolling average
         $up='UPDATE '.CA_ATT_TBL.' SET rolling_adults="'.$averow->rolling_adults.'", rolling_children="'.$averow->rolling_children.'" WHERE attendance_id="'.esc_sql($attendance_id).'"';
	 $wpdb->query($up);


     echo '<div id="message" class="updated fade">';
     echo '<p><strong>'.__('Attendance added','church-admin').'.</strong></p>';
     echo '</div>';
     //print_r($sqlsafe);
     church_admin_attendance_list($sqlsafe['service_id']);

}
else
{
echo'<div class="wrap church_admin"><h2>'.__('Attendance','church-admin').'</h2>';
echo '<form action="" method="post" name="add_attendance" id="add_attendance">';

//service
echo'<p><label>'.__('Service','church-admin').'</label><select name="service_id">';

$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
$option='';
foreach($services AS $service)
{
     
    if(!empty($data->service_id)&& $data->service_id==$service->service_id)
     {
	  
	  $first='<option value="'.$service->service_id.'" selected="selected">'.$service->service_name.' '.$service->service_time.'</option>';
     
     }
     else
     {
	  $option.='<option value="'.$service->service_id.'" >'.$service->service_name.' on '.$days[$service->service_day].' '.$service->service_time.'</option>';
     }
}
     echo $first.$option.'</select></p>';
//datepicker js
echo'<p><label >'.__('Date','church-admin').' :</label><input type="text" id="add_date" name="add_date" ';
 if(empty($data->date)){echo ' value="'.date("d M Y").'" ';}else{echo ' value="'.mysql2date("d M Y",$data->date).'" ';}
	echo'/></p>';
	echo'<script type="text/javascript">
      jQuery(document).ready(function(){
         jQuery(\'#add_date\').datepicker({
            dateFormat : "'."d M yy".'", changeYear: true ,yearRange: "2011:'.date('Y',time()+60*60*24*365*10).'"
         });
      });
   </script>';
echo'   <p><label >'.__('Adults','church-admin').'</label><input type="text" name="adults"  ';
if(!empty($data->adults)) echo' value="'.$data->adults.'" ';
echo'/></p>

<p><label >'.__('Children','church-admin').'</label><input type="text" name="children" ';
if(!empty($data->children)) echo' value="'.$data->children.'" ';
echo'/><input type="hidden" name="edit_att" value="y"/></p>
<p class="submit"><input type="submit" value="'.__('Add attendance for that date','church-admin').' &raquo;" /></p></form></div>
';

}//end of attendance form
}//end funtion

function church_admin_delete_attendance($attendance_id)
{
     global $wpdb;
     //find service_id
     $service_id=$wpdb->get_var('SELECT service_id FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');
     $wpdb->query('DELETE FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($attendance_id).'"');
     echo'<div class="updated fade"><p>'.__('Attendance record deleted','church-admin').'</p></div>';
     church_admin_attendance_list($service_id);
}

function church_admin_attendance_metrics($service_id=1)
{
     global $wpdb,$days;
     $wpdb->show_errors;
     
     if(empty($service_id))$service_id=1;
     $service=$wpdb->get_var('SELECT CONCAT_WS(" ",service_name,service_time) AS service FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
     $first_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" ORDER BY `date` ASC LIMIT 1');
     $last_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" ORDER BY `date` DESC LIMIT 1');
    
     for($year=$first_year;$year<=$last_year;$year++){$thead.="<th>$year</th>";}
    
     $aggtable=$totaltable=$adulttable=$childtable='<table class="widefat"><thead><tr><th>'.__('Month','church-admin').'</th>'.$thead.'</tr></thead><tfoot><tr><th>Month</th>'.$thead.'<tr></tfoot><tbody>';
    
	  $results=$wpdb->get_results('SELECT ROUND( AVG( adults ) ) AS adults, ROUND( AVG( children ) ) AS children, YEAR( `date` ) AS year, MONTH( `date` ) AS month FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" GROUP BY YEAR( `date` ) , MONTH( `date` )');
	  
if($results) 
{	  foreach($results AS $row)
	  {
	       
	       $adults[$row->month][$row->year]=$row->adults;
	       $children[$row->month][$row->year]=$row->children;
	  }
	  
     for($month=1;$month<=12;$month++)
     {
	  $aggtable.='<tr><td>'.$month.'</td>';
	  $totaltable.='<tr><td>'.$month.'</td>';
	  $adulttable.='<tr><td>'.$month.'</td>';
	  $childtable.='<tr><td>'.$month.'</td>';
	  for($year=$first_year;$year<=$last_year;$year++)
	  {
	       if(empty($adults[$month][$year])){$adulttable.='<td>&nbsp;</td>';}else{$adulttable.='<td>'.$adults[$month][$year].'</td>';}
	       if(empty($children[$month][$year])){$childtable.='<td>&nbsp;</td>';}else{$childtable.='<td>'.$children[$month][$year].'</td>';}
	       $total=$adults[$month][$year]+$children[$month][$year];
	       if($adults[$month][$year]+$children[$month][$year]>0){$totaltable.='<td>'.$total.'</td>';}else{$totaltable.='<td>&nbsp;</td>';}
	       if($adults[$month][$year]+$children[$month][$year]>0){$aggtable.='<td><span class="adults">'.$adults[$month][$year].'</span>, <span class="children">'.$children[$month][$year].'</span> (<span class="total">'.$total.')</span></td>';}else{$aggtable.='<td>&nbsp;</td>';}
	       
	  }
	  $aggtable.='</tr>';
	  $totaltable.='</tr>';
	  $adulttable.='</tr>';
	  $childtable.='</tr>';
     }
     $aggtable.='</tbody></table>';
	  $totaltable.='</tbody></table>';
	  $adulttable.='</tbody></table>';
	  $childtable.='</tbody></table>';
}
else
{
     $totaltable=$aggtable=$childtable=$adulttable='<p>'.__('No attendance recorded yet','church-admin').'</p>';
}

     echo'<div class="church_admin wrap"><h2>'.__('Attendance Figures','church-admin').'</h2>';
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance','edit_attendance').'">'.__('Add attendance','church-admin').'</a>';
     $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
     
     echo'<table>';
     foreach($services AS $service)
     {
	  $sql='SELECT * FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service->service_id).'"';
	  
	  $check=$wpdb->get_row($sql);
	  if($service->service_id==$service_id)$service_details=$service->service_name.' '.__('on','church-admin').' '.$days[$service->service_day].' '.$service->service_time;
	  if($check) echo'<tr><td><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_metrics&amp;service_id='.$service->service_id.'">View attendance table for '.$service->service_name.' '.$service->service_time.'</a></td><td><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list&amp;service_id='.$service->service_id.'">'.__('Edit week by week attendance for','church-admin').' '.$service->service_name.' '.$service->service_time.'</a></td></tr>';
     }
     echo'</table>';
     echo '<h2>'.__('Attendance Adults,Children (Total)','church-admin').' '.$service_details.'</h2>'.$aggtable;
     echo '<h2>'.__('Total Attendance for','church-admin').' '.$service_details.'</h2>'.$totaltable;
     echo '<h2>'.__('Adults Attendance for','church-admin').' '.$service_details.'</h2>'.$adulttable;
     echo '<h2>'.__('Children Attendance for','church-admin').' '.$service_details.'</h2>'.$childtable;
     echo'</div>';
}
?>