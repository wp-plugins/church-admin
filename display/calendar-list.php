<?php
$current=(isset($_GET['m'])) ? intval($_GET['m']) : time(); //get user date or use today
if(isset($_POST['month']) && isset($_POST['year'])) $current=mktime(12,0,0,$_POST['month'],14,$_POST['year']);
$thismonth = (int)date("m",$current);
$thisyear = date( "Y",$current );
$actualyear=date("Y");
$next = strtotime("+1 month",$current);
$previous = strtotime("-1 month",$current);
$now=date("M Y",$current);
$sqlnow=$thisyear.'-'.$thismonth.'-01';

$sqlnext=date("Y-m-d",strtotime($sqlnow." + 1month"));
   // find out the number of days in the month
$numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
// create a calendar object
$jd = cal_to_jd( CAL_GREGORIAN, $thismonth,date( 1 ), $thisyear );

$sql="SELECT ".$wpdb->prefix."church_admin_calendar_category.fgcolor AS fgcolor,".$wpdb->prefix."church_admin_calendar_category.bgcolor AS bgcolor,".$wpdb->prefix."church_admin_calendar_category.category AS category, ".$wpdb->prefix."church_admin_calendar_category.cat_id,".$wpdb->prefix."church_admin_calendar_event.cat_id,".$wpdb->prefix."church_admin_calendar_date.start_time,".$wpdb->prefix."church_admin_calendar_date.end_time, ".$wpdb->prefix."church_admin_calendar_date.start_date,".$wpdb->prefix."church_admin_calendar_date.event_id, ".$wpdb->prefix."church_admin_calendar_event.event_id,".$wpdb->prefix."church_admin_calendar_event.title AS title, ".$wpdb->prefix."church_admin_calendar_event.description, ".$wpdb->prefix."church_admin_calendar_event.location  FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_date.start_date>'$sqlnow' AND ".$wpdb->prefix."church_admin_calendar_date.start_date<'$sqlnext'  AND ".$wpdb->prefix."church_admin_calendar_date.event_id=".$wpdb->prefix."church_admin_calendar_event.event_id AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id";

    
$result=$wpdb->get_results($sql);

$out.='<table width="700">';
$out.='<tr><td colspan="3"><a href="'.get_permalink().'&amp;m='.$previous.'">Previous</a> '.$now.' <a href="'.get_permalink().'&amp;m='.$next.'">Next</a></td></tr>';
$out.='<tr><td width="150">Date</td><td width="150">Time</td><td width="400" >Event</td></tr>';
foreach($result AS $row)
{
    
    $out.="<tr><td>".mysql2date('D j M Y',$row->start_date)."</td><td>".$row->start_time." - ".$row->end_time."</td><td><strong>".htmlentities($row->title)."</strong><br> ".htmlentities($row->description)."</td></tr>";
}
$out.="</table>";
	
?>