<?php

if(isset($_POST['ca_month']) && isset($_POST['ca_year'])){ $current=mktime(12,0,0,$_POST['ca_month'],14,$_POST['ca_year']);}else{$current=time();}
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

$out.='<table class="church_admin_calendar">
<tr>
        <td colspan="7" class="calendar-date-switcher">
            <form method="post" action="">
Month<select name="ca_month">
';
$first=$option='';
for($q=0;$q<=12;$q++)
{
    $mon=date('m',($current+$q*(28*24*60*60)));
    $MON=date('M',($current+$q*(28*24*60*60)));
      if(isset($_POST['ca_month'])&&$_POST['ca_month']==$mon) {$first="<option value=\"$mon\" selected=\"selected\">$MON</option>";}else{$out.= "<option value=\"$mon\">$MON</option>";}
}
$out.=$first.$option;
$out.='</select>'.__('Year','church-admin').'<select name="ca_year">';
$first=$option='';
for ($x=$actualyear;$x<=$actualyear+15;$x++)
{
    if(isset($_POST['ca_year'])&&$_POST['ca_year']==$x)
    {
	$first="<option value=\"$x\" >$x</option>";
    }
    else
    {
	$option.= "<option value=\"$x\">$x</option>";
    }
}
$out.=$first.$option;
$out.='</select><input  type="submit" value="Submit"/></form></td></tr>            
            
';
$out.=
'<tr>
               
                    
    <td colspan="3" class="calendar-date-switcher">';
if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime("$now -1 month")).'"/><input type="submit" value="Previous" class="calendar-date-switcher"/></form>';}
$out.='</td>
                    <td class="calendar-date-switcher">'.$now.'</td>
                    <td class="calendar-date-switcher" colspan="3"><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="Next"/></form></td>
                
                
</tr>
		
    <tr><td  ><strong>'.__('Sunday','church-admin').'</strong></td>
    <td ><strong>'.__('Monday','church-admin').'</strong></td>
    <td ><strong>'.__('Tuesday','church-admin').'</strong></td>
    <td ><strong>'.__('Wednesday','church-admin').'</strong></td>
    <td ><strong>'.__('Thursday','church-admin').'</strong></td>
    <td ><strong>'.__('Friday','church-admin').'</strong></td>
    <td ><strong>'.__('Saturday','church-admin').'</strong></td>
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
    $out.="\t\t<td align=\"left\"><strong>$counter</strong><br/>";
    //put events for day in here
    $sqlnow="$thisyear-$thismonth-".sprintf('%02d', $counter);
    $sql='SELECT a.*, b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.cat_id=b.cat_id  AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';
    $result=$wpdb->get_results($sql);
    if($wpdb->num_rows=='0')
    {
        $out.='&nbsp;<br/>&nbsp;<br/>';
    }
    else
    {
        foreach($result AS $row)
        {
			if(!empty($row->event_image)){$image=wp_get_attachment_image( $row->event_image,'ca-people-thumb' ,'',array('class'=>"alignleft"));}else{$image='';}
            $popup=stripslashes("<p><strong>".esc_html(strtoupper($row->title))."</strong><br/>$image".esc_html($row->description)."<br/>".$row->location."<br/>".mysql2date(get_option('time_format'),$row->start_time)." - ".mysql2date(get_option('time_format'),$row->end_time)."<br/>".$row->category." Event");
            $out.= '<div class="church_admin_cal_item" id="ca'.$row->date_id.'"style="background-color:'.$row->bgcolor.'" >'.mysql2date(get_option('time_format'),$row->start_time).' '.htmlentities($row->title).'... </div></p><div id="div'.$row->date_id.'" class="church_admin_tooltip"  >'.$popup.'</div><br/>';
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
$out.="
<script type=\"text/javascript\">

jQuery(document).ready(function($){
       $('.church_admin_cal_item').live('mouseover', function() {
       $('.church_admin_tooltip').hide();//get rid of other ones
  // Live handler called.
	var hideNo=this.id.substr(2);

	$('#div'+hideNo).toggle('25');

});
    
});</script>
";