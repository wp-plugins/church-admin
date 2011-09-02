<?php
/*
 Adds attendance figures
church_admin_show_rolling_average()
church_admin_show_graph()
church_admin_add_attendance()

*/
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

function church_admin_add_attendance(){
  global $wpdb;
if( !empty($_POST['add_date']) &&checkDateFormat($_POST['add_date']) && !empty($_POST['adults'])&& !empty($_POST['children']) &&is_numeric($_POST['adults']) && is_numeric($_POST['children'])&&check_admin_referer( 'church_admin_add_attendance'))
{ 
    
    $sql="INSERT INTO ".$wpdb->prefix."church_admin_attendance (date,adults,children) VALUES('".esc_sql($_POST['add_date'])."','".esc_sql($_POST['adults'])."','".esc_sql($_POST['children'])."')";
$wpdb->query($sql)  ;
//work out rolling average from values!

$avesql="SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM ".$wpdb->prefix."church_admin_attendance WHERE `date` >= DATE_SUB('{$_POST['add_date']}',INTERVAL 52 WEEK) AND `date`<= '{$_POST['add_date']}' ";
  
    $averow=$wpdb->get_row($avesql);

//update table with rolling average
    $up="UPDATE ".$wpdb->prefix."church_admin_attendance SET rolling_adults='{$averow->rolling_adults}', rolling_children='{$averow->rolling_children}' WHERE `date`='{$_POST['add_date']}'";

    $wpdb->query($up);


echo '<div id="message" class="updated fade">';
echo '<p><strong>Attendance added.</strong></p>';
echo '</div>';
church_admin_show_rolling_average();
church_admin_show_graph();

}
else
{
echo'<div class="wrap church_admin"><h2>Attendance</h2>';
echo '<form action="" method="post" name="add_attendance" id="add_attendance">';

echo '<script type="text/javascript" src="'.CHURCH_ADMIN_INCLUDE_URL.'javascript.js"></script>
<script type="text/javascript">document.write(getCalendarStyles());</script>';

if ( function_exists('wp_nonce_field') ) wp_nonce_field('church_admin_add_attendance');
//datepicker js
	
echo'<script type="text/javascript">
var cal_begin = new CalendarPopup(\'pop_up_cal\');
function unifydates() {
document.forms[\'add_attendance\'].add_date.value = document.forms[\'add_attendance\'].add_date.value;
}
					</script>
<ul><li><label >Date (yyyy-mm-dd):</label><input type="text" name="add_date" class="input" size="12" value="'.date('Y-m-d',strtotime("last Sunday")).'" /><a href="#" onclick="cal_begin.select(document.forms[\'add_attendance\'].add_date,\'attendance\',\'yyyy-MM-dd\'); return false;" name="attendance" id="attendance">Select date</a><div id="pop_up_cal" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div></li>
<li><label >Adults</label><input type="text" name="adults" value=""/></li>

<li><label >Children</label><input type="text" name="children" value=""/></li>
</ul>
<p class="submit"><input type="submit" value="Add attendance for that date &raquo;" /></p></form></div>
';
$attendance=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_attendance");
if($attendance>0)
{
    echo'<h2>Attendance by Month</h2>';
    church_admin_attendance_metrics();
  //church_admin_show_rolling_average();
  //church_admin_show_graph();
}//end check for values before trying to produce graphs
}//end of attendance form
}//end funtion


function church_admin_attendance_metrics()
{
     global $wpdb;
     $wpdb->show_errors;
     $first_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.$wpdb->prefix.'church_admin_attendance ORDER BY `date` ASC LIMIT 1');
     $last_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.$wpdb->prefix.'church_admin_attendance ORDER BY `date` DESC LIMIT 1');
    
     for($year=$first_year;$year<=$last_year;$year++){$thead.="<th>$year</th>";}
    
     $aggtable=$totaltable=$adulttable=$childtable='<table class="widefat"><thead><tr><th>Month</th>'.$thead.'</tr></thead><tfoot><tr><th>Month</th>'.$thead.'<tr></tfoot><tbody>';
    
	  $results=$wpdb->get_results('SELECT ROUND( AVG( adults ) ) AS adults, ROUND( AVG( children ) ) AS children, YEAR( `date` ) AS year, MONTH( `date` ) AS month FROM '.$wpdb->prefix.'church_admin_attendance GROUP BY YEAR( `date` ) , MONTH( `date` )');
	  
	 
	  foreach($results AS $row)
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
	  
     echo '<h2>Attendance Adults,Children (Total)</h2>'.$aggtable.'<tbody></table>';
     echo '<h2>Total Attendance</h2>'.$totaltable.'<tbody></table>';
     echo '<h2>Adults Attendance</h2>'.$adulttable.'<tbody></table>';
     echo '<h2>Children Attendance</h2>'.$childtable.'<tbody></table>';
}
?>