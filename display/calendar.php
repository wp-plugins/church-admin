<?php

if(isset($_POST['month']) && isset($_POST['year'])){ $current=mktime(12,0,0,$_POST['month'],14,$_POST['year']);}else{$current=time();}
	$thismonth = (int)date("m",$current);
	$thisyear = date( "Y",$current );
	$actualyear=date("Y");
	$next = strtotime("+1 month",$current);
	$previous = strtotime("-1 month",$current);
	$now=date("M Y",$current);
	$sqlnow=date("Y-m-d", $current);
    // find out the number of days in the month
    $numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
    // create a calendar object
    $jd = cal_to_jd( CAL_GREGORIAN, $thismonth,date( 1 ), $thisyear );

    // get the start day as an int (0 = Sunday, 1 = Monday, etc)
    $startday = jddayofweek( $jd , 0 );
    
    // get the month as a name
    $monthname = jdmonthname( $jd, 1 );

$out.='<table  class="church_admin_calendar">
<tr>
        <td colspan="7" class="calendar-date-switcher">
            <form method="post" action="'.get_permalink().'">
Month<select name="month">
';
for($q=0;$q<=12;$q++)
{
    $mon=date('m',($current+$q*(28*24*60*60)));
    $MON=date('M',($current+$q*(28*24*60*60)));
    $out.= "<option value=\"$mon\">$MON</option>";
}
$out.='</select>Year<select  name="year">';
for ($x=$actualyear;$x<=$actualyear+15;$x++)
{
    $out.= "<option value=\"$x\">$x</option>";
}
$out.='</select><input  type="submit" value="Submit"/></form></td></tr>            
            
';
$out.=
'<tr>
                <td colspan="7" class="calendar-date-switcher" >
                    <table border="0" class="calendar-date-switcher" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                    <td class="calendar-prev">';
if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="year" value="'.date('Y',strtotime("$now -1 month")).'"/><input type="submit" value="Previous" class="calendar-date-switcher"/></form>';}
$out.='</td>
                    <td class="calendar-month">'.$now.'</td>
                    <td class="calendar-next"><form action="'.get_permalink().'" method="post"><input type="hidden" name="month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="Next"/></form></td>
                    </tr>
                    </table>
                </td>
</tr>
		
    <tr><td width="100" align="center"><strong>Sunday</strong></td><td width="100" align="center"><strong>Monday</strong></td>
        <td width="100" align="center"><strong>Tuesday</strong></td>
        <td width="100" align="center"><strong>Wednesday</strong></td>
        <td width="100" align="center"><strong>Thursday</strong></td>
        <td width="100" align="center"><strong>Friday</strong></td>
        <td width="100" align="center"><strong>Saturday</strong></td>
    </tr>
    <tr>';
// put render empty cells
$emptycells = 0;
for( $counter = 0; $counter <  $startday; $counter ++ )
{
    $out.="\t\t<td>-</td>\n";
    $emptycells ++;
}
// renders the days
$rowcounter = $emptycells;
$numinrow = 7;
for( $counter = 1; $counter <= $numdaysinmonth; $counter ++ )
{
        $rowcounter ++;
    $out.="\t\t<td align=\"left\">$counter<br/>";
    //put events for day in here
    $sqlnow="$thisyear-$thismonth-$counter";
    $sql="SELECT ".$wpdb->prefix."church_admin_calendar_category.fgcolor AS fgcolor,".$wpdb->prefix."church_admin_calendar_category.bgcolor AS bgcolor,".$wpdb->prefix."church_admin_calendar_category.category AS category, ".$wpdb->prefix."church_admin_calendar_category.cat_id,".$wpdb->prefix."church_admin_calendar_event.cat_id,".$wpdb->prefix."church_admin_calendar_date.date_id,".$wpdb->prefix."church_admin_calendar_date.start_time,".$wpdb->prefix."church_admin_calendar_date.end_time,".$wpdb->prefix."church_admin_calendar_date.start_date,".$wpdb->prefix."church_admin_calendar_date.event_id, ".$wpdb->prefix."church_admin_calendar_event.event_id,".$wpdb->prefix."church_admin_calendar_event.title AS title, ".$wpdb->prefix."church_admin_calendar_event.description, ".$wpdb->prefix."church_admin_calendar_event.location  FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_date.start_date='$sqlnow' AND ".$wpdb->prefix."church_admin_calendar_date.event_id=".$wpdb->prefix."church_admin_calendar_event.event_id AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id ORDER BY ".$wpdb->prefix."church_admin_calendar_date.start_time ";
    
    $result=$wpdb->get_results($sql);
    if($wpdb->num_rows=='0')
    {
        $out.='&nbsp;<br/>&nbsp;<br/>';
    }
    else
    {
        foreach($result AS $row)
        {
            $popup="<p><strong>".$row->title."</strong><br/>".$row->description."<br/>".$row->location."<br/>{$row->start_time} - {$row->end_time}<br/>".$row->category." Event</p>";
            $out.= '<div onmouseover="toggle(\'div'.$row->date_id.'\');" onmouseout="toggle(\'div'.$row->date_id.'\');" style="background-color:'.$row->bgcolor.'" >'.htmlentities($row->title).'</div><div id="div'.$row->date_id.'" class="church_admin_tooltip" style="display:none;" >'.$popup.'</div><br/>';
        }
    }    
    $out.="</td>\n";
        
        if( $rowcounter % $numinrow == 0 )
        {   
            $out.="\t</tr>\n";
            if( $counter < $numdaysinmonth )
            {
                $out.="\t<tr>\n";
            }    
            $rowcounter = 0;
        }
}
// clean up
$numcellsleft = $numinrow - $rowcounter;
if( $numcellsleft != $numinrow )
{
    for( $counter = 0; $counter < $numcellsleft; $counter ++ )
    {
        $out.= "\t\t<td>-</td>\n";
        $emptycells ++;
    }
}

$out.='</tr>
</table>';