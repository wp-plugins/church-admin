<?php
/*
 2011-02-03 Fixed widget display so multiple events per day
*/
if(!function_exists('church_admin_widget_control'))
{function church_admin_widget_control()
{
    //get saved options
    $options=get_option('church_admin_widget');
    //handle user input
    if($_POST['widget_submit'])
    {
        $options['title']=strip_tags(stripslashes($_POST['title']));
        if($_POST['postit']=='1') {$options['postit']='1';}else{$options['postit']='0';}
        if(ctype_digit($_POST['events'])){$options['events']=$_POST['events'];}else{$options['events']='5';}
        if(ctype_digit($_POST['cat_id'])){$options['cat_id']=$_POST['cat_id'];}else{$options['cat_id']='0';}
        update_option('church_admin_widget',$options);
    }
    church_admin_widget_control_form();
}
}
function church_admin_widget_control_form()
{
    global $wpdb;
    $wpdb->show_errors;
    
    $option=get_option('church_admin_widget');
    echo '<p><label for="title">'.__('Title','church-admin').':</label><input type="text" name="title" value="'.$option['title'].'" /></p>';
    echo '<p><label for="postit">'.__('Postit Note style','church-admin').'?:</label><input type="checkbox" name="postit" value="1"';
    if($option['postit']==1) echo ' checked="checked" ';
    echo '/></p>';
    echo'<p><label for="category">'.__('Select a Category','church-admin').'</label>';
    $sql='SELECT * FROM '.CA_CAT_TBL;
    
    $results=$wpdb->get_results($sql );
    echo'<select name="cat_id">';
    if($option['cat_id'])
    {
        $opt=$wpdb->get_var('SELECT category FROM '.CA_CAT_TBL. 'WHERE cat_id="'.esc_sql($option['cat_id']).'"');
        '<option value="'.$option['cat_id'].'" selected="selected">'.$opt.'</option>';
    }
    echo'<option value="0">'.__('All events','church-admin').'</option>';
    foreach($results AS $row)echo'<option value="'.$row->cat_id.'">'.$row->category.'</option>';
    echo'</select></p>';
    echo '<p><label for="howmany">'.__('How many events to show','church-admin').'?</label><select name="events">';
    if(isset($option['events'])) echo '<option value="'.$option['events'].'">'.$option['events'].'</option>';
    for($x=1;$x<=10;$x++){echo '<option value="'.$x.'">'.$x.'</option>';}
    echo'</select><input type="hidden" name="widget_submit" value="1"/>';
}

function church_admin_calendar_widget_output($limit=5,$postit,$title)
{
global $wpdb;
$limit=3;
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
$options=get_option('church_admin_widget');
if(isset($options['cat_id']) && $options['cat_id']!=0){$cat=CA_CAT_TBL.'.cat_id="'.$options['cat_id'].'" AND ';} else {$cat='';}
//prepare output
$out='';
if($postit)$out.='<div class="Postit">';
$out.='<ul>';

//grab next $limit days events
for($x=0;$x<=$limit;$x++)
{
    //date
    $sqlnow=date('Y-m-d',$current+($x*60*60*24));
 
    //query
$sql='SELECT TIME_FORMAT('.CA_DATE_TBL.'.start_time,"%h:%i%p")AS start_time, '.CA_DATE_TBL.'.start_date AS start_date,'.CA_DATE_TBL.'.end_time, '.CA_EVE_TBL.'.title,'.CA_EVE_TBL.'.location, '.CA_EVE_TBL.'.description,'.CA_CAT_TBL.'.category,'.CA_EVE_TBL.'.event_image AS event_image 
FROM '.CA_DATE_TBL.', '.CA_EVE_TBL.','.CA_CAT_TBL.'
WHERE '.CA_DATE_TBL.'.start_date="'.$sqlnow.'" AND '.CA_DATE_TBL.'.event_id = '.CA_EVE_TBL.'.event_id AND '.CA_EVE_TBL.'.cat_id='.CA_CAT_TBL.'.cat_id ORDER BY '.CA_DATE_TBL.'.start_time LIMIT 0 ,'.$limit;

$result=$wpdb->get_results($sql);
if(!empty($result))
{
  foreach($result AS $row)
    {
    $date=mysql2date('D jS M',$row->start_date);
	$class='';
    $out.='<div itemscope itemtype="http://data-vocabulary.org/Event" class="church-admin-calendar-widget-item">';
	if(!empty($row->event_image))
	{
		$out.=wp_get_attachment_image( $row->event_image, 'ca-people-thumb','',array('class'=>'alignleft'));
		$class=' class="ca_event_detail" ';//adds class to stop text flowing under thumbnail
	}
	$out.='<p '.$class.'><span itemprop="summary">'.strtoupper($row->title).'</span><br/>';
	$out.='<time itemprop="startDate" datetime="'.date('c',strtotime($row->start_date.' '.$row->start_time)) .'">'.$date.' '.strtolower($row->start_time).' </time><br/>';
	$out.='	<span itemprop="location" itemscope itemtype="http://data-vocabulary.org/​Organization"><span itemprop="name">'.$row->location.'</span></span></div></p>';
    }
  
    unset($date,$thisday,$class);
    
}//end of non empty result
else{
    $limit++;//nothing for that day, so increase no of days
    }
}//end of for loop



$out.="</ul>";
if($postit)$out.='</div>';
return $out;

}
?>
