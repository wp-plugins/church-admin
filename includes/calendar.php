<?php
/*
2011-02-04 added calendar single and series delete; fixed slashes problem
2011-03-14 fixed errors not sowing as red since 0.32.4
2012-07-20 Update Internationalisation 
2014-09-22 Simplify db and add image 
2014-10-06 Added facilities bookings
 
*/
function church_admin_new_calendar($current=NULL,$facilities_id=NULL)
{

	global $wpdb;
	if(isset($_POST['ca_month']) && isset($_POST['ca_year'])){ $current=mktime(12,0,0,$_POST['ca_month'],14,$_POST['ca_year']);}
	if(empty($current)){$current=time();}
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
	$out='<div class="wrap church_admin">';
	if(!empty($facilities_id))
	{
		$facility=$wpdb->get_var('SELECT facility_name FROM '.CA_FAC_TBL.' WHERE facilities_id="'.esc_sql($facilities_id).'"');
		$out.='<h2>Booking Calendar for '.$facility.'</h2>';
	}else
	{
		$out.='<h2>Calendar</h2>';
	}
	
	$facs=$wpdb->get_results('SELECT * FROM '.CA_FAC_TBL.' ORDER BY facilities_order');
	if(!empty($facs))
	{
		$out.='<p><label>Choose facility</label><form action="'.admin_url().'?page=church_admin/index.php&action=church_admin_new_calendar" method="POST"><select name="facilities_id">';
		if(!empty($facilities_id)) {$out.='<option value="'.$facilities_id.'">'.$facility.'</option>';}
		$out.='<option value="">'.__('N/A','church-admin').'</option>';
		foreach($facs AS $fac){$out.='<option value="'.$fac->facilities_id.'">'.$fac->facility_name.'</option>';}
		$out.='</select><input type="submit" name="'.__('Choose facility','church-admin').'"/></form></p>';
	}
	$out.='<p>Double click on an event to edit, or a day to add an event</p>';
	$out.='<p><a href="'.admin_url().'?page=church_admin/index.php&action=church_admin_calendar_list">Old Style Calendar List</a></p>';
	$out.='<table class="church_admin_calendar"><tr><td colspan="7" class="calendar-date-switcher"><form method="post" action="'.admin_url().'?page=church_admin/index.php&action=church_admin_new_calendar">'.__('Month','church-admin').'<select name="ca_month">';
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
	$out.='</select><input  type="submit" value="'.__('Submit','church-admin').'"/></form></td></tr>';
	$out.='<tr><td colspan="3" class="calendar-date-switcher">';
	if($now==date('M Y')){$out.='&nbsp;';}else{$out.='<form action="'.get_permalink().'" name="previous" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime("$now -1 month")).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime("$now -1 month")).'"/><input type="submit" value="Previous" class="calendar-date-switcher"/></form>';}
	$out.='</td><td class="calendar-date-switcher">'.$now.'</td><td class="calendar-date-switcher" colspan="3"><form action="'.get_permalink().'" method="post"><input type="hidden" name="ca_month" value="'.date('m',strtotime($now.' +1 month')).'"/><input type="hidden" name="ca_year" value="'.date('Y',strtotime($now.' +1 month')).'"/><input type="submit" class="calendar-date-switcher" value="Next"/></form></td></tr>
	<tr><td  ><strong>'.__('Sunday','church-admin').'</strong></td>
    <td ><strong>'.__('Monday','church-admin').'</strong></td>
    <td ><strong>'.__('Tuesday','church-admin').'</strong></td>
    <td ><strong>'.__('Wednesday','church-admin').'</strong></td>
    <td ><strong>'.__('Thursday','church-admin').'</strong></td>
    <td ><strong>'.__('Friday','church-admin').'</strong></td>
    <td ><strong>'.__('Saturday','church-admin').'</strong></td>
    </tr><tr class="cal">';
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
		$sqlnow="$thisyear-$thismonth-".sprintf('%02d', $counter);
        $rowcounter ++;
		$out.="\t\t".'<td id="'.$sqlnow.'"><strong>'.$counter.'</strong><br/>';
    //put events for day in here
   
    if(empty($facilities_id)){$sql='SELECT a.*, b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.general_calendar=1 AND a.cat_id=b.cat_id  AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';}
	else{$sql='SELECT a.*, b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.facilities_id="'.esc_sql($facilities_id).'" AND a.cat_id=b.cat_id  AND a.start_date="'.$sqlnow.'" ORDER BY a.start_time';}
	
    $result=$wpdb->get_results($sql);
    if($wpdb->num_rows=='0')
    {
        $out.='&nbsp;<br/>&nbsp;<br/>';
    }
    else
    {
        foreach($result AS $row)
        {
			
            $out.= '<div id="item'.$row->date_id.'"style="background-color:'.$row->bgcolor.'" >'.mysql2date(get_option('time_format'),$row->start_time).' '.htmlentities($row->title).'... </div></p>';
        }
    }    
    $out.="</td>\n";
        
        if( $rowcounter % $numinrow == 0 )
        {   
            $out.="\t</tr>\n";
            if( $counter < $numdaysinmonth )
            {
                $out.="\t".'<tr class="cal">'."\n";
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
$out.='</tr></table>';

$out.='<script type="text/javascript">	jQuery(document).ready(function($) {$(".cal").bind("dblclick", function(event) {window.location.href = "'.admin_url().'?page=church_admin/index.php&action=church_admin_new_edit_calendar&id="+event.target.id';
if(!empty($facilities_id))$out.='+ "&facilities_id='.$facilities_id.'"';
$out.='});});</script></div><!--wrap church-admin-->';
echo $out;
}
function church_admin_category_list()
{
    global $wpdb;
    //build category tableheader
        $thead='<tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th width="100">'.__('Category','church-admin').'</th><th>'.__('Shortcode','church-admin').'</th></tr>';
    $table= '<table class="widefat" ><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
        //grab categories
    $results=$wpdb->get_results('SELECT * FROM '.CA_CAT_TBL);
    foreach($results AS $row)
    {
        $edit_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_category&amp;id='.$row->cat_id,'edit_category').'">'.__('Edit','church-admin').'</a>';;
        $delete_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_category&amp;id='.$row->cat_id,'delete_category').'">'.__('Delete','church-admin').'</a>';
        $shortcode='[church_admin type=calendar-list category='.$row->cat_id.' weeks=4]';
        $table.='<tr><td>'.$edit_url.'</td><td>'.$delete_url.'</td><td style="background:'.$row->bgcolor.'">'.esc_html($row->category).'</td><td>'.$shortcode.'</td></tr>';
    }
    $table.='</tbody></table>';
    echo '<div class="wrap"><h2>'.__('Calendar Categories','church-admin').'</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_add_category','add_category').'">'.__('Add a category','church-admin').'</a></p>'.$table.'</div>';
}

function church_admin_add_category()
{
     global $wpdb;
	 $wpdb->show_errors;
    if(!empty($_POST))
    {
        $sql='INSERT INTO '.CA_CAT_TBL.' (category,bgcolor)VALUES("'.esc_sql(stripslashes($_POST['category'])).'","'.esc_sql($_POST['color']).'")';
		echo $sql;
        $wpdb->query($sql);
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.__('Category Added','church-admin').'</strong></p>';
        echo '</div>';
        church_admin_category_list();
    }
    else
    {
        echo'<div class="wrap church_admin"><h2>'.__('Add Category','church-admin').'</h2><form action="" method="post">';
        church_admin_category_form('');
        echo'<p><label>&nbsp;</label><input type="submit" name="add_category" value="'.__('Add Category','church-admin').'"/></p></form></div>';  
    }
    
}

function church_admin_delete_category($id)
{
    global $wpdb;
    $wpdb->show_errors();
    //count how many events have that category
    $count=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_DATE_TBL.' WHERE cat_id="'.esc_sql($id).'"');
    $wpdb->query('DELETE FROM '.CA_CAT_TBL.' WHERE cat_id="'.esc_sql($id).'"');
    //adjust events with deleted cat_id to 0
    $wpdb->query('UPDATE '.CA_DATE_TBL.' SET cat_id="1" WHERE cat_id="'.esc_sql($id).'"');
    echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.__('Category Deleted','church-admin').'.<br/>';
        if($count==1) printf(__('Please note that %1$s event used that category and will need editing','church-admin'),$count).'.';
        if($count>1) printf(__('Please note that %1$s events used that category and will need editing','church-admin'),$count).'.';
        echo'</strong></p>';
        echo '</div>';
        church_admin_category_list();
    
    
}
function church_admin_edit_category($id)
{
    global $wpdb;
    if(!empty($_POST))
    {
        $wpdb->query('UPDATE '.CA_CAT_TBL.' SET category="'.esc_sql(stripslashes($_POST['category'])).'",bgcolor="'.esc_sql($_POST['color']).'" WHERE cat_id="'.esc_sql($id).'"');
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.__('Category Edited','church-admin').'</strong></p>';
        echo '</div>';
        church_admin_category_list();
    }
    else
    {
    echo'<div class="wrap church_admin"><h2>'.__('Edit Category','church-admin').'</h2><form action="" method="post">';
    //grab current data
    $row=$wpdb->get_row('SELECT * FROM '.CA_CAT_TBL.' WHERE cat_id="'.esc_sql($id).'"');
    church_admin_category_form($row);
    echo'<p><label>&nbsp;</label><input type="submit" name="edit_category" value="'.__('Edit Category','church-admin').'"/></p></form>';
   
    echo'</div>';
    }
}
function church_admin_category_form($data)
{
if(empty($data))$data=new stdClass();
 
    if(empty($data->bgcolor))$data->bgcolor='#e4afb1';
	echo '<script type="text/javascript" > 
  jQuery(document).ready(function($) {
    
    $(\'#picker\').farbtastic(\'#color\');
    
    
  });
 </script>  
 <p><label >'.__('Category Name','church-admin').'</label><input type="text" name="category" ';
 if(!empty($data->category)) echo 'value="'.$data->category.'"';
	echo'/></p>
  <p><label >'.__('Background Colour','church-admin').'</label><input type="text" ';
  if(!empty($data->bgcolor)) echo' style="background:'.$data->bgcolor.'" ';
  echo' id="color" name="color" ';
  if(!empty($data->bgcolor))echo' value="'.$data->bgcolor.'" ';
  echo'/></p><div id="picker"></div>';
}

function church_admin_calendar()
{
    global $wpdb;
    echo'<div class="wrap church_admin"><h2>'.__('Calendar','church-admin').'</h2><p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_category_list">'.__('Category List','church-admin').'</a></p>';
    church_admin_calendar_list();
    echo'</div>';
}



function church_admin_event_edit($date_id,$event_id,$edit_type,$date,$facilities_id)
{

	global $wpdb;
	$wpdb->show_errors();
	$edit='Add';
	if(!empty($date_id)){$data=$wpdb->get_row('SELECT a.*,b.* FROM '.CA_DATE_TBL.' a,'.CA_CAT_TBL.' b WHERE a.cat_id=b.cat_id AND a.date_id="'.esc_sql($date_id).'"');$edit='Edit';}
	if(empty($event_id)&&!empty($data->event_id)){$event_id=$data->event_id;$edit='Edit';}
	
	if(!empty($_POST['save_date']))
	{//process
		
		switch($edit_type)
		{
			case'single':if(!empty($date_id))$wpdb->query('DELETE FROM '.CA_DATE_TBL .' WHERE date_id="'.esc_sql($date_id).'"');break;
			case'series':if(!empty($event_id))$wpdb->query('DELETE FROM '.CA_DATE_TBL .' WHERE event_id="'.esc_sql($event_id).'"');break;
		
		}
		
		//get next highest event_id
		$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.CA_DATE_TBL)+1;
		$form=array();
		foreach($_POST AS $key=>$value)$form[$key]=stripslashes($value);
		//adjust data
		$form['start_time'].=':00';
		$form['end_time'].=':00';
		if(empty($form['cat_id'])){$form['cat_id']=1;}
		if(empty($form['year_planner'])){$form['year_planner']=0;}else{$form['year_planner']=1;}
		if(empty($form['general_calendar'])){$form['general_calendar']=0;}else{$form['general_calendar']=1;}
		if(empty($form['end_date'])){$form['end_date']=$form['start_date'];}
		//only allow one submit!
		$checksql='SELECT date_id FROM '.CA_DATE_TBL.' WHERE title="'.esc_sql($form['title']).'" AND description="'.esc_sql($form['description']).'" AND location="'.esc_sql($form['location']).'"  AND cat_id="'.esc_sql($form['cat_id']).'" AND start_date="'.esc_sql($form['start_date']).'" AND start_time="'.esc_sql($form['start_time']).'" AND end_time="'.esc_sql($form['end_time']).'" LIMIT 1';
		
		$check=$wpdb->get_var($checksql);
		if(empty($check)||!empty($date_id))
		{
			//handle upload
			if(empty($data->event_image)){$event_image=NULL;}else{$event_image=$data->event_image;}
	
			if(!empty($_FILES) && $_FILES['uploadfiles']['error'] == 0)
			{
				$filetmp = $_FILES['uploadfiles']['tmp_name'];
				//clean filename and extract extension
				$filename = $_FILES['uploadfiles']['name'];
			
				// get file info
				$filetype = wp_check_filetype( basename( $filename ), null );
				$filetitle = preg_replace('/\.[^.]+$/', '', basename( $filename ) );
				$filename = $filetitle . '.' . $filetype['ext'];
				$upload_dir = wp_upload_dir();
				/**
				* Check if the filename already exist in the directory and rename the
				* file if necessary
				*/
				$i = 0;
				while ( file_exists( $upload_dir['path'] .'/' . $filename ) )
				{
					$filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
					$i++;
				}
	    
				$filedest = $upload_dir['path'] . '/' . $filename;
	    
				move_uploaded_file($filetmp, $filedest);
				$attachment = array('post_mime_type' => $filetype['type'],'post_title' => $filetitle,'post_content' => '','post_status' => 'inherit');
				$event_image = wp_insert_attachment( $attachment, $filedest );
	    
				require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $event_image, $filedest );
	    
				wp_update_attachment_metadata( $event_image,  $attach_data );
				
			}// end handle upload
		
		
			switch($_POST['recurring'])
			{
				case's':
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,cat_id,event_id,how_many,start_date,start_time,end_time,facilities_id,general_calendar)VALUES("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.esc_sql($form['cat_id']).'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['start_date']).'","'.esc_sql($form['start_time']).'","'.esc_sql($form['end_time']).'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
				break;
				case'n':
					//handle nth
					$values=array();
					for($x=0;$x<$form['how_many'];$x++)
					{
						$start_date=nthday($form['nth'],$form['day'],date('Y-m-d',strtotime($form['start_date']." +$x month")));
               			$values[]='("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['cat_id']).'","'.$start_date.'","'.$form['start_time'].'","'.$form['end_time'].'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,event_id,how_many,cat_id,start_date,start_time,end_time,facilities_id,general_calendar)VALUES'.implode(",",$values);
				break;
				case '14':
					$values=array();
					for($x=0;$x<$form['how_many'];$x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x fortnight"));
						$values[]='("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['cat_id']).'","'.$start_date.'","'.$form['start_time'].'","'.$form['end_time'].'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,event_id,how_many,cat_id,start_date,start_time,end_time,facilities_id,general_calendar)VALUES'.implode(",",$values);
				break;
				case '7':
					$values=array();
					for($x=0;$x<$form['how_many'];$x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x week"));
						$values[]='("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['cat_id']).'","'.$start_date.'","'.$form['start_time'].'","'.$form['end_time'].'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,event_id,how_many,cat_id,start_date,start_time,end_time,facilities_id,general_calendar)VALUES'.implode(",",$values);
				break;
				case 'm':
					$values=array();
					for($x=0;$x<$form['how_many'];$x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x month"));
						$values[]='("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['cat_id']).'","'.$start_date.'","'.$form['start_time'].'","'.$form['end_time'].'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,event_id,how_many,cat_id,start_date,start_time,end_time,facilities_id,general_calendar)VALUES'.implode(",",$values);
				break;
				case 'a':
					$values=array();
					for($x=1;$x<$form['how_many'];$x++)
					{
						$start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x year"));
						$values[]='("'.esc_sql($form['title']).'","'.esc_sql($form['description']).'","'.esc_sql($form['location']).'","'.esc_sql($form['recurring']).'","'.esc_sql($form['year_planner']).'","'.$event_image.'","'.$event_id.'","'.esc_sql($form['how_many']).'","'.esc_sql($form['cat_id']).'","'.$start_date.'","'.$form['start_time'].'","'.$form['end_time'].'","'.esc_sql($form['facilities_id']).'","'.esc_sql($form['general_calendar']).'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,location,recurring,year_planner, event_image,event_id,how_many,cat_id,start_date,start_time,end_time,facilities_id,general_calendar)VALUES'.implode(",",$values);
				break;
		
			}
			
			$wpdb->query($sql);
			echo'<div class="updated fade"><p><strong>'.__('Date(s) saved','church-admin').'</strong></p></div>';
		}
		else{echo'<div class="updated fade"><p><strong>'.__('Date(s) already saved','church-admin').'</strong></p></div>';}
		church_admin_new_calendar(strtotime($form['start_date']));
	}//end process
	else
	{
	
		echo'<div class="wrap church_admin">';
		
		if(empty($facilities_id)){echo'<h2>'.$edit.' Calendar Item</h2>';}else{echo '<h2>'.$edit.' Facility Booking</h2>';}
		echo'<form action="" enctype="multipart/form-data" id="calendar" method="post">';
		if(empty($error))$error =new stdClass();
		if(empty($data)) $data = new stdClass();
		if(!empty($data->event_id))
		{
			$multi=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_DATE_TBL.' WHERE event_id="'.esc_sql($data->event_id).'"');
			if($multi>1)
			{
				echo'<p><label>Single or Series Edit?</label><input type="radio" name="edit_type" value="single" checked="checked"/> Single or <input type="radio" name="edit_type" value="series"/> Series</p>';
				if(!empty($data->event_id))echo '<p><label>&nbsp;</label><a class="button" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_series_event_delete&amp;event_id='.$data->event_id.'&amp;date_id='.$data->date_id,'series_event_delete').'">'.__('Delete this series event','church-admin').'</a></p>';
			}
		}
		if(!empty($data->date_id))echo '<p><label>&nbsp;</label><a  class="button" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_single_event_delete&amp;event_id='.$data->event_id.'&amp;date_id='.$data->date_id,'single_event_delete').'">'.__('Delete this single event','church-admin').'</a></p>';
		echo church_admin_calendar_form($data,$error,1,$date,$facilities_id);
		echo '<p><label>&nbsp;</label><input type="submit" name="edit_event" value="'.__('Save Event','church-admin').'"/></form></div>'; 
		}

}

function church_admin_calendar_form($data,$error,$recurring=1,$date,$facilities_id)
{
    
    global $wpdb;
	if(empty($data)) $data=new stdClass();
    
	$wpdb->show_errors();
    $out='  <script type="text/javascript" src="'.plugins_url('includes/javascript.js',dirname(__FILE__) ) . '"></script>
<script type="text/javascript">document.write(getCalendarStyles());</script>
<script type="text/javascript">
var cal_begin = new CalendarPopup(\'pop_up_cal\');
function OnChange(dropdown){
if(document.getElementById(\'recurring\').value==\'s\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'none\';
		}
if(document.getElementById(\'recurring\').value==\'1\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'block\';
		}
if(document.getElementById(\'recurring\').value==\'7\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'block\';;
		}
if(document.getElementById(\'recurring\').value==\'14\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'block\';;
		}                
if(document.getElementById(\'recurring\').value==\'n\'){
		document.getElementById(\'nth\').style.display = \'block\';
		document.getElementById(\'howmany\').style.display = \'block\';
		}
if(document.getElementById(\'recurring\').value==\'m\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'block\';
		}
if(document.getElementById(\'recurring\').value==\'a\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'block\';
		}
}
</script>';
$out.='<p><label>'.__('Event Title','church-admin').'</label><input type="text" name="title" ';
if(!empty($data->title))$out.=' value="'.stripslashes($data->title).'" ';
if(!empty($error->title))$out.=$error->title;
$out.=' /></p>';
$out.='<p><label for="photo">'.__('Photo','church-admin').'</label><input type="file" id="photo" name="uploadfiles" size="35" class="uploadfiles" /></p>';
if(!empty($data->event_image))
		{//photo available
			$out.= '<p><label>Current Photo</label>';
			$out.= wp_get_attachment_image( $data->event_image,'ca-people-thumb' );
			$out.='</p>';
		}//photo available
		else
		{
			$out.= '<p><label>&nbsp;</label>';
			$out.= '<img src="'.plugins_url('images/default-avatar.jpg',dirname(__FILE__) ) .'" width="75" height="75"/>';
			$out.= '</p>';
		}
$out.='<p><label>'.__('Event Description','church-admin').'</label><textarea rows="5" cols="50" name="description" ';
if(!empty($error->description))$out.=$error->description;
$out.='>';
if(!empty($data->description))$out.=stripslashes($data->description);
$out.='</textarea></p>';
$out.='<p><label>'.__('Event Location','church-admin').'</label><textarea rows="5" cols="50" name="location" ';
if(!empty($error->location))$out.=$error->location;
$out.='>';

if(!empty($data->location))$out.=stripslashes($data->location);
$out.='</textarea></p>';
$out.='<p><label>'.__('Facility/Room','church-admin').'</label><select name="facilities_id"> ';
if(!empty($facilities_id))
{
	$facility_name=$wpdb->get_var('SELECT facility_name FROM '.CA_FAC_TBL.' WHERE facilities_id="'.esc_sql($facilities_id).'"');
	if(!empty($facilitiy_name))$out.='<option value="'.$facilities_id.'" selected="selected" >'.$facility_name.'</option>';
}
else{$out.='<option value="">'.__('N/A','church-admin').'</option>';}
$facs=$wpdb->get_results('SELECT * FROM '.CA_FAC_TBL.' ORDER BY facilities_order');
	if(!empty($facs))
	{
		foreach($facs AS $fac){$out.='<option value="'.$fac->facilities_id.'">'.$fac->facility_name.'</option>';}
		
	}
$out.='</select></p>';

$out.='<p><label> '.__('Category','church-admin').'</label><select name="cat_id" ';
if(!empty($error->category)) $out.=$error->category;
$out.=' >';
$select='';
$first='<option value="">'.__('Please select','church-admin').'...</option>';
$sql="SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category";
$result3=$wpdb->get_results($sql);
foreach($result3 AS $row)
{
    if(!empty($data->cat_id)&&$data->cat_id==$row->cat_id)
    {
        
        $first='<option value="'.$data->cat_id.'" style="background:'.$data->bgcolor.'" selected="selected">'.$data->category.'</option>';
    }
    else
    {
        $select.='<option value="'.$row->cat_id.'" style="background:'.$row->bgcolor.'">'.$row->category.'</option>';
    }
}

$out.=$first.$select;//have original value first!
$out.='</select></p>
<p><label >'.__('Start Date','church-admin').'</label><input name="start_date" id="start_date" type="text"';
if(!empty($error->start_date))$out.=$error->start_date;
if(!empty($date))$out.=' value="'.$date.'"';
if(!empty($data->start_date))$out.=' value="'.mysql2date('Y-m-d',$data->start_date).'"';
$out.=' size="25" />';
$out.='<script type="text/javascript">
      jQuery(document).ready(function(){
         jQuery(\'#start_date\').datepicker({
            dateFormat : "'."yy-mm-dd".'", changeYear: true ,yearRange: "2011:'.date('Y',time()+60*60*24*365*10).'"
         });
      });
   </script>';
if($recurring==1){
    $out.='
<p><label>'.__('Recurring','church-admin').'</label>
<select name="recurring" ';
if(!empty($error->recurring))$out.=$error->recurring;
$out.=' id="recurring" onchange="OnChange(\'recurring\')">';
if(!empty($data->recurring))
{
    $option=array('s'=>__('Once','church-admin'),'1'=>__('Daily','church-admin'),'7'=>__('Weekly','church-admin'),'n'=>__('nth day eg.1st Friday','church-admin'),'m'=>__('Monthly','church-admin'),'a'=>__('Annually','church-admin'));
    $out.= '<option value="'.$data->recurring.'">'.$option[$data->recurring].'</option>';
}
$out.='
<option value="s">'.__('Once','church-admin').'</option>
<option value="1">'.__('Daily','church-admin').'</option>
<option value="7">'.__('Weekly','church-admin').'</option>
<option value="14">'.__('Fortnightly','church-admin').'</option>
<option value="n">'.__('nth day (eg 1st Friday)','church-admin').'</option>
<option value="m">'.__('Monthly on same date','church-admin').'</option>
<option value="a">'.__('Annually','church-admin').'</option>
</select></p>
<div id="nth" ';
if(!empty($data->recurring)&&$data->recurring=='n'){$out.='style="display:block"';}else{$out.='style="display:none"';}
$out.='><p><label>'.__('Recurring on','church-admin').' </label><select ';
if(!empty($error->nth))$out.=$error->nth;$out.=' name="nth">';
if(!empty($data->nth)) $out.='<option value="'.$data->nth.'">'.$data->nth.'</option>';
$out.='<option value="1">'.__('1st','church-admin').'</option><option value="2">'.__('2nd','church-admin').'</option><option value="3">'.__('3rd','church-admin').'</option><option value="4">'.__('4th','church-admin').'</option></select>&nbsp;<select name="day"><option value="0">'.__('Sunday','church-admin').'</option><option value="1">'.__('Monday','church-admin').'</option><option value="2">'.__('Tuesday','church-admin').'</option><option value="3">'.__('Wednesday','church-admin').'</option><option value="4">'.__('Thursday','church-admin').'</option><option value="5">'.__('Friday','church-admin').'</option><option value="6">'.__('Saturday','church-admin').'</option></select></p></div>
<div id="howmany" ';
if(!empty($data->recurring) && $data->recurring!='s'){$out.='style="display:block"';}else{$out.='style="display:none"';}
$out.='><p><label>'.__('How many times in all?','church-admin').'</label><input type="text" ';
if(!empty($error->how_many)) $out.=$error->how_many;
$out.=' name="how_many" ';
if(!empty($data->how_many))$out.=' value="'.$data->how_many.'"';
$out.='/></p></div>';
}//end recurring
else
{
    $out.='<input type="hidden" name="recurring" value="s"/><input type="hidden" name="how_many" value="1"/>';
}
if(!empty($data->start_time))$data->start_time=substr($data->start_time,0,5);//remove seconds
if(!empty($data->end_time))$data->end_time=substr($data->end_time,0,5);//remove seconds
$out.='<p><label>'.__('Start Time of form HH:MM','church-admin').'</label><input type="text" name="start_time" ';
if(!empty($error->start_time))$out.=$error->start_time;
if(!empty($data->start_time))$out.=' value="'.$data->start_time.'"';
$out.='/></p>';
$out.='<p><label>'.__('End Time of form HH:MM','church-admin').'</label><input type="text" name="end_time" ';
if(!empty($error->end_time)) $out.=$error->end_time;
if(!empty($data->end_time))$out.=' value="'.$data->end_time.'" ';
$out.='/></p>';
$out.='<p><label>'.__('Appear on Year Planner?','church-admin').'</label><input type="checkbox" name="year_planner" value="1"';
if(!empty($data->year_planner)) $out.=' checked="checked"';
$out.='/>';
$out.='<p><label>'.__('Appear on General Calendar?','church-admin').'</label><input type="checkbox" name="general_calendar" value="1"';
if(!empty($data->general_calendar)) $out.=' checked="checked"';
$out.='/>';
$out.='<input type="hidden" name="save_date" value="yes"/></p>';

return $out;
}




function church_admin_single_event_delete($date_id)
{
    global $wpdb;
    $date=$wpdb->get_var('SELECT start_date FROM '.CA_DATE_TBL.' WHERE date_id="'.esc_sql($date_id).'"');
    $wpdb->query('DELETE FROM '.CA_DATE_TBL.' WHERE date_id="'.esc_sql($date_id).'"');
    echo '<div id="message" class="updated fade">';
    echo '<p><strong>'.__('Calendar Events deleted','church-admin').'.</strong></p>';
    echo '</div>';
    
	
    church_admin_new_calendar(strtotime($date));
}

function church_admin_series_event_delete($event_id)
{
    global $wpdb;
    $date=$wpdb->get_var('SELECT MIN(start_date) FROM '.CA_DATE_TBL.' WHERE event_id="'.esc_sql($event_id).'"');
    $wpdb->query('DELETE FROM '.CA_DATE_TBL.' WHERE event_id="'.esc_sql($event_id).'"');
    echo '<div id="message" class="updated fade">';
    echo '<p><strong>'.__('Calendar Events deleted','church-admin').'.</strong></p>';
    echo '</div>';
    church_admin_new_calendar(strtotime($date));
}


function church_admin_calendar_error_check($data)
{
    global $error,$sqlsafe;
     //check startdate
      $start_date=church_admin_dateCheck($data['start_date']);
      
      $end_date=church_admin_dateCheck($data['end_date'], $yearepsilon=50);
      
      if($start_date){$sqlsafe['start_date']=esc_sql($start_date);}else{$error->start_date==1;}
      
      //check start time
   if (preg_match ("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['start_time'])){$sqlsafe['start_time']=$data['start_time'];}else{$error['start_time']='1';}
        //check end time
  if (preg_match("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['end_time'])){$sqlsafe['end_time']=$data['end_time'];}else{$error->end_time='1';}
 
      //check recurring
      if($data['recurring']=='s'||$data['recurring']=='1'||$data['recurring']=='7'||$data['recurring']=='14'||$data['recurring']=='n'||$data['recurring']=='m'||$data['recurring']=='a'){$sqlsafe['recurring']=$data['recurring'];}else{$error['recurring']=1;}
      //check how many
      if($data['recurring']!='s')
      {
        if(ctype_digit($data['how_many']))
        {
            $sqlsafe['how_many']=$data['how_many'];
        }
        else
        {
            $error->how_many=1;
        }
      }
      //check nth if necessary
      if($data['recurring']=='n')
        {
            if(!empty($data['nth']) && $data['nth']<='4')
            {
                $sqlsafe['nth']=$data['nth'];$sqlsafe['day']=$data['day'];
            }
            else
            {
                $error->nth=$error['day']=1;
            }
        }
       if(!empty($data['title'])){ $sqlsafe['title']= esc_sql($data['title']);}else{$error->title=1;}
       if(!empty($data['description'])){ $sqlsafe['description']= esc_sql(nl2br($data['description']));}else{$error->description=1;}
       $sqlsafe['description']=strip_tags($sqlsafe['description']);
      $sqlsafe['location']=esc_sql($data['location']);
      if(!empty($_POST['category'])&&ctype_digit($data['category'])){$sqlsafe['category']=$data['category'];}else{$error['category']=1;}
      if($data['year_planner']=='1'){$sqlsafe['year_planner']=1;}else{$sqlsafe['year_planner']=0;}
     
    return $error;  
}


function church_admin_calendar_list()
{
    global $wpdb;
    if(empty($_REQUEST['date'])){$entereddate=time();}else{$entereddate=$_REQUEST['date'];}
   echo'<div class="wrap church_admin"><p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_calendar&amp;date='.$entereddate.'">'.__('Add calendar Event','church-admin').'</a></p>';
$events=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_DATE_TBL); 
 if(!empty($events))
{
     //which month to view
    $current=(isset($_REQUEST['date'])) ? intval($_REQUEST['date']) : time(); //get user date or use today
    $next = strtotime("+1 month",$current);
    $previous = strtotime("-1 month",$current);
    $now=date("M Y",$current);
    $sqlnow=date("Y-m%", $current);
    $sqlnext=date("Y-m-d",$next);
    
    echo '<table><tr><td><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_calendar_list&amp;date='.$previous.'">'.__('Prev','church-admin').'</a> '.$now.' <a href="admin.php?page=church_admin/index.php&amp;action=church_admin_calendar_list&amp;date='.$next.'">'.__('Next','church-admin').'</a></td><td>';
    echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_calendar_list" method="post"><select name="date">';
	echo '<option value="'.$entereddate.'">'.date('M Y',$entereddate).'</option>';
//generate a form to access calendar
for($x=0;$x<12;$x++)
{
    $date=strtotime("+ $x month",time());
    echo '<option value="'.$date.'">'.date('M Y',$date).'</option>';
}
echo '</select><input type="submit" value="'.__('Go to date','church-admin').'"/></form></td></tr></table>';
    //initialise table
    $table='<table class="widefat"><thead><tr><th>'.__('Single Edit','church-admin').'</th><th>'.__('Series Edit','church-admin').'</th><th>'.__('Single Delete','church-admin').'</th><th>'.__('Series Delete','church-admin').'</th><th>'.__('Start date','church-admin').'</th><th>'.__('Start Time','church-admin').'</th><th>'.__('End Time','church-admin').'</th><th>'.__('Event Name','church-admin').'</th><th>'.__('Category','church-admin').'</th><th>'.__('Year Planner','church-admin').'?</th></tr></thead><tfoot><tr><th>'.__('Single Edit','church-admin').'</th><th>'.__('Series Edit','church-admin').'</th><th>'.__('Single Delete','church-admin').'</th><th>'.__('Series Delete','church-admin').'</th><th>'.__('Start date','church-admin').'</th><th>'.__('Start Time','church-admin').'</th><th>'.__('End Time','church-admin').'</th><th>'.__('Event Name','church-admin').'</th><th>'.__('Category','church-admin').'</th><th>'.__('Year Planner','church-admin').'?</th></tr></tfoot><tbody>';
    
	
	$sql='SELECT a.*,b.category FROM '.CA_DATE_TBL.' a, '.CA_CAT_TBL.' b WHERE a.cat_id=b.cat_id AND a.start_date LIKE "'.$sqlnow.'" ORDER BY a.start_date';
	
   $result=$wpdb->get_results($sql);
    foreach($result AS $row)
    {
    //create links
    $single_edit_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_single_event_edit&amp;event_id='.$row->event_id.'&amp;date_id='.$row->date_id.'&amp;'.$entereddate,'single_event_edit').'">'.__('Edit','church-admin').'</a>';
    if($row->recurring=='s'){$series_edit_url='&nbsp;';}else{$series_edit_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_series_event_edit&amp;event_id='.$row->event_id.'&amp;date_id='.$row->date_id.'&amp;date='.$entereddate,'series_event_edit').'">'.__('Edit Series','church-admin').'</a>';}
    $single_delete_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_single_event_delete&amp;event_id='.$row->event_id.'&amp;date_id='.$row->date_id.'&amp;date='.$entereddate,'single_event_delete').'">'.__('Delete this one','church-admin').'</a>';

    if($row->recurring=='s'){$series_delete_url='&nbsp;';}else{$series_delete_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_series_event_delete&amp;event_id='.$row->event_id.'&amp;date_id='.$row->date_id.'&amp;date='.$entereddate,'series_event_delete').'">'.__('Delete Series','church-admin').'</a>';}
    
    //sort out category
    if(empty($row->bgcolor))$row->bgcolor='#FFF';
     $table.='<tr><td>'.$single_edit_url.'</td><td>'.$series_edit_url.'</td><td>'.$single_delete_url.'</td><td>'.$series_delete_url.'</td><td>'.mysql2date('j F Y',$row->start_date).'</td><td>'.$row->start_time.'</td><td>'.$row->end_time.'</td><td>'.htmlentities($row->title).'</td><td style="background:'.$row->bgcolor.'">'.htmlentities($row->category).'</td><td>';
     if($row->year_planner){$table.=__('Yes','church-admin');}else{$table.='&nbsp;';}
     $table.='</td></tr>';
    }
    $table.='</tbody></table>';
    echo $table.'</div>';
}//end of non empty calendar table

}


function church_admin_dateCheck($date, $yearepsilon=5000) { // inputs format is "yyyy-mm-dd" ONLY !
if (count($datebits=explode('-',$date))!=3) return false;
$year = intval($datebits[0]);
$month = intval($datebits[1]);
$day = intval($datebits[2]);
if ((abs($year-date('Y'))>$yearepsilon) || // year outside given range
($month<1) || ($month>12) || ($day<1) ||
(($month==2) && ($day>28+(!($year%4))-(!($year%100))+(!($year%400)))) ||
($day>30+(($month>7)^($month&1)))) return false; // date out of range
if( checkdate($month,$day,$year)) {return ($year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day));}else{return FALSE;}
}


if(!function_exists('array_to_object')) {
  function array_to_object($array = array()) {
    if (!empty($array)) {
        $data = false;
        foreach ($array as $akey => $aval) {
            $data -> {$akey} = $aval;
        }
        return $data;
    }
    return false;
}
  
}

function nthday($nth,$day,$date)
{
    $days=array(0=>__('Sunday','church-admin'),1=>__('Monday','church-admin'),2=>__('Tuesday','church-admin'),3=>__('Wednesday','church-admin'),4=>__('Thursday','church-admin'),5=>__('Friday','church-admin'),6=>__('Saturday','church-admin'));
    $month=date('M',strtotime($date));
    $year=date('Y',strtotime($date));
    return date('Y-m-d',strtotime("+$nth {$days[$day]} $month $year"));
}
?>
