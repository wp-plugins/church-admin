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
  $wpdb->show_errors();
if( !empty($_POST['add_date'])  && !empty($_POST['adults'])&& !empty($_POST['children']) &&is_numeric($_POST['adults']) && is_numeric($_POST['children'])&&check_admin_referer( 'church_admin_add_attendance'))
{ 
    
    $sql='INSERT INTO '.CA_ATT_TBL.' (date,adults,children) VALUES("'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'","'.esc_sql($_POST['adults']).'","'.esc_sql($_POST['children']).'")';

$wpdb->query($sql)  ;
//work out rolling average from values!

     $avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.CA_ATT_TBL.' WHERE `date` >= DATE_SUB("'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'",INTERVAL 52 WEEK) AND `date`<= "'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'"';

    $averow=$wpdb->get_row($avesql);

//update table with rolling average
    $up='UPDATE '.CA_ATT_TBL.' SET rolling_adults="'.$averow->rolling_adults.'", rolling_children="'.$averow->rolling_children.'" WHERE `date`="'.esc_sql(date('Y-m-d',strtotime($_POST['add_date']))).'"';

    $wpdb->query($up);


echo '<div id="message" class="updated fade">';
echo '<p><strong>Attendance added.</strong></p>';
echo '</div>';
church_admin_attendance_metrics();

}
else
{
echo'<div class="wrap church_admin"><h2>Attendance</h2>';
echo '<form action="" method="post" name="add_attendance" id="add_attendance">';



if ( function_exists('wp_nonce_field') ) wp_nonce_field('church_admin_add_attendance');
//datepicker js
echo'<p><label >Date :</label><input type="text" id="add_date" name="add_date" ';
 echo ' value="'.date("d M yy").'" ';
	echo'/></p>';
	echo'<script type="text/javascript">
      jQuery(document).ready(function(){
         jQuery(\'#add_date\').datepicker({
            dateFormat : "'." d MM yy".'", changeYear: true ,yearRange: "2011:'.date('Y',time()+60*60*24*365*10).'"
         });
      });
   </script>';
echo'   <p><label >Adults</label><input type="text" name="adults" value=""/></li>

<p><label >Children</label><input type="text" name="children" value=""/></p>
<p class="submit"><input type="submit" value="Add attendance for that date &raquo;" /></p></form></div>
';
$attendance=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_ATT_TBL);
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
     $first_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' ORDER BY `date` ASC LIMIT 1');
     $last_year=$wpdb->get_var('SELECT YEAR(`date`) FROM '.CA_ATT_TBL.' ORDER BY `date` DESC LIMIT 1');
    
     for($year=$first_year;$year<=$last_year;$year++){$thead.="<th>$year</th>";}
    
     $aggtable=$totaltable=$adulttable=$childtable='<table class="widefat"><thead><tr><th>Month</th>'.$thead.'</tr></thead><tfoot><tr><th>Month</th>'.$thead.'<tr></tfoot><tbody>';
    
	  $results=$wpdb->get_results('SELECT ROUND( AVG( adults ) ) AS adults, ROUND( AVG( children ) ) AS children, YEAR( `date` ) AS year, MONTH( `date` ) AS month FROM '.CA_ATT_TBL.' GROUP BY YEAR( `date` ) , MONTH( `date` )');
	  
	 
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