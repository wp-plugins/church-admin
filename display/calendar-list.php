<?php
if(isset($_POST['ca_month']) && isset($_POST['ca_year'])){ $current=mktime(12,0,0,$_POST['ca_month'],14,$_POST['ca_year']);}else{$current=time();}
if(isset($category)&&ctype_digit($category)) $catsql=' AND '.$wpdb->prefix."church_admin_calendar_event.cat_id=".$category;
$thismonth = (int)date("m",$current);
$thisyear = date( "Y",$current );
$actualyear=date("Y");
$next = strtotime("+1 month",$current);
$previous = strtotime("-1 month",$current);
$now=date("M Y",$current);
$sqlnow=$thisyear.'-'.$thismonth.'-01';
if(empty($weeks))$weeks=4;
$sqlnext=date("Y-m-d",strtotime($sqlnow." + ".$weeks." weeks"));
   // find out the number of days in the month
$numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
// create a calendar object
$jd = cal_to_jd( CAL_GREGORIAN, $thismonth,date( 1 ), $thisyear );

$sql="SELECT ".$wpdb->prefix."church_admin_calendar_category.fgcolor AS fgcolor,".$wpdb->prefix."church_admin_calendar_category.bgcolor AS bgcolor,".$wpdb->prefix."church_admin_calendar_category.category AS category, ".$wpdb->prefix."church_admin_calendar_category.cat_id,".$wpdb->prefix."church_admin_calendar_event.cat_id,".$wpdb->prefix."church_admin_calendar_date.start_time,".$wpdb->prefix."church_admin_calendar_date.end_time, ".$wpdb->prefix."church_admin_calendar_date.start_date,".$wpdb->prefix."church_admin_calendar_date.event_id, ".$wpdb->prefix."church_admin_calendar_event.event_id,".$wpdb->prefix."church_admin_calendar_event.title AS title, ".$wpdb->prefix."church_admin_calendar_event.description, ".$wpdb->prefix."church_admin_calendar_event.location  FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_date.start_date>'$sqlnow' AND ".$wpdb->prefix."church_admin_calendar_date.start_date<'$sqlnext'  AND ".$wpdb->prefix."church_admin_calendar_date.event_id=".$wpdb->prefix."church_admin_calendar_event.event_id AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id ".$catsql." ORDER BY ".$wpdb->prefix."church_admin_calendar_date.start_date, ".$wpdb->prefix."church_admin_calendar_date.start_time";

    
$result=$wpdb->get_results($sql);

$out.='<table><tr><td>';
if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime("$now -1 month")).'"/><input class="calendar-date-switcher" type="submit" value="Previous" /></form>';}
$out.='</td>
                    <td >'.$now.'</td>
                    <td ><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="Next"/></form></td>
                
                
</tr></table><table>';
$out.='<tr><td width="150">Date</td><td width="150">'.__('Time','church-admin').'</td><td width="400" >'.__('Event','church-admin').'</td></tr>';
foreach($result AS $row)
{
    
    $out.="<tr><td>".mysql2date(get_option('date_format'),$row->start_date)."</td><td>".mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time)."</td><td><strong>".esc_html(stripslashes($row->title))."</strong><br> ".esc_html(stripslashes($row->description))."</td></tr>";
}
$out.="</table>";
	
?>