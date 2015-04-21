<?php
if(isset($_POST['ca_month']) && isset($_POST['ca_year'])){ $current=mktime(12,0,0,$_POST['ca_month'],14,$_POST['ca_year']);}else{$current=time();}
if(isset($category)&&ctype_digit($category)) {$catsql=' AND a.cat_id='.esc_sql($category);}else{$catsql='';}
$thismonth = (int)date("m",$current);
$thisyear = date( "Y",$current );
$actualyear=date("Y");
$next = strtotime("+1 month",$current);
$previous = strtotime("-1 month",$current);
$now=date("M Y",$current);
$sqlfirst=date('Y-m-01',$current);
$sqllast=date('Y-m-t',$current);

if(empty($weeks))$weeks=4;
$sqlnext=date("Y-m-d",strtotime($sqlnow." + ".$weeks." weeks"));
   // find out the number of days in the month
$numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
// create a calendar object
$jd = cal_to_jd( CAL_GREGORIAN, $thismonth,date( 1 ), $thisyear );
$sqlnow="$thisyear-$thismonth-".sprintf('%02d', $counter);
    $sql='SELECT a.*, b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.cat_id=b.cat_id  AND a.start_date BETWEEN CAST("'.$sqlnow.'" AS DATE) AND CAST("'.$sqlnext.'" AS DATE) '.$catsql.' ORDER BY a.start_time';
	

    
$result=$wpdb->get_results($sql);

$out.='<table><tr><td>';
if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime("$now -1 month")).'"/><input class="calendar-date-switcher" type="submit" value="Previous" /></form>';}
$out.='</td>
                    <td ><h2>'.esc_html($now).'</h2></td>
                    <td ><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="Next"/></form></td>
                
                
</tr></table><table>';
$out.='<tr><td width="150">Date</td><td width="150">'.__('Time','church-admin').'</td><td width="400" >'.__('Event','church-admin').'</td></tr>';
foreach($result AS $row)
{
    
    $out.="<tr><td>".mysql2date(get_option('date_format'),$row->start_date)."</td><td>".mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time)."</td><td><strong>".esc_html(stripslashes($row->title))."</strong><br> ".esc_html(stripslashes($row->description))."</td></tr>";
}
$out.="</table>";
	
?>