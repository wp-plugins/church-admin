<?php
function church_admin_widget_control()
{
    //get saved options
    $options=get_option('church_admin_widget');
    //handle user input
    if($_POST['widget_submit'])
    {
        $options['title']=strip_tags(stripslashes($_POST['title']));
        if($_POST['postit']=='1') {$options['postit']='1';}else{$options['postit']='0';}
        if(ctype_digit($_POST['events'])){$options['events']=$_POST['events'];}else{$options['events']='5';}
        update_option('church_admin_widget',$options);
    }
    church_admin_widget_control_form();
}

function church_admin_widget_control_form()
{
    $option=get_option('church_admin_widget');
    echo '<p><label for="title">Title:</label><input type="text" name="title" value="'.$option['title'].'" /></p>';
    echo '<p><label for="postit">Postit Note style?:</label><input type="checkbox" name="postit" value="1"';
    if($option['postit']==1) echo ' checked="checked" ';
    echo '"/></p>';
    echo '<p><label for="howmany">How many events to show?</label><select name="events">';
    if(isset($option['events'])) echo '<option value="'.$option['events'].'">'.$option['events'].'</option>';
    for($x=1;$x<=10;$x++){echo '<option value="'.$x.'">'.$x.'</option>';}
    echo'</select><input type="hidden" name="widget_submit" value="1"/>';
}

function church_admin_calendar_widget_output($limit,$postit,$title)
{
global $wpdb;
$wpdb->show_errors;
$current=time(); //get user date or use today
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

$sql="SELECT ".$wpdb->prefix."church_admin_calendar_category.category AS category, ".$wpdb->prefix."church_admin_calendar_category.cat_id,".$wpdb->prefix."church_admin_calendar_event.cat_id,TIME_FORMAT(".$wpdb->prefix."church_admin_calendar_date.start_time,'%h:%i%p')AS start_time,".$wpdb->prefix."church_admin_calendar_date.end_time, ".$wpdb->prefix."church_admin_calendar_date.start_date,".$wpdb->prefix."church_admin_calendar_date.event_id, ".$wpdb->prefix."church_admin_calendar_event.event_id,".$wpdb->prefix."church_admin_calendar_event.title AS title, ".$wpdb->prefix."church_admin_calendar_event.description, ".$wpdb->prefix."church_admin_calendar_event.location  FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_date.start_date>'$sqlnow' AND ".$wpdb->prefix."church_admin_calendar_date.start_date<'$sqlnext'  AND ".$wpdb->prefix."church_admin_calendar_date.event_id=".$wpdb->prefix."church_admin_calendar_event.event_id AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id ORDER BY ".$wpdb->prefix."church_admin_calendar_date.start_date LIMIT 1,".$limit;

    
$result=$wpdb->get_results($sql);
if($postit)$out='<div class="Postit">';
$out.='<h1>'.$title.'</h1><ul>';
foreach($result AS $row)
{
    $out.="<li>".mysql2date('D j M Y',$row->start_date).'<br/>'.strtolower($row->start_time)." <strong>".$row->title."</strong><br/></li>";
}
$out.="</ul>";
if($postit)$out.='</div>';
return $out;

}
?>