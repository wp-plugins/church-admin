<?php
/*
2011-02-04 added calendar single and series delete; fixed slashes problem
2011-03-14 fixed errors not sowing as red since 0.32.4
 
 
 
*/
require(CHURCH_ADMIN_INCLUDE_PATH.'cache_yearplanner.php');
function church_admin_category_list()
{
    global $wpdb;
    //build category tableheader
        $thead='<tr><th>Edit</th><th>Delete</th><th width="100">Category</th><th>Shortcode</th></tr>';
    $table= '<table class="widefat" ><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
        //grab categories
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    foreach($results AS $row)
    {
        $edit_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_category&amp;id='.$row->cat_id,'edit_category').'">Edit</a>';;
        $delete_url='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_category&amp;id='.$row->cat_id,'delete_category').'">Delete</a>';
        $shortcode='[church_admin type=calendar-list category='.$row->cat_id.' weeks=4]';
        $table.='<tr><td>'.$edit_url.'</td><td>'.$delete_url.'</td><td style="background:'.$row->bgcolor.'">'.esc_html($row->category).'</td><td>'.$shortcode.'</td></tr>';
    }
    $table.='</tbody></table>';
    echo '<div class="wrap"><h2>Calendar Categories</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_add_category','add_category').'">Add a category</a></p>'.$table.'</div>';
}

function church_admin_add_category()
{
     global $wpdb;
    if(!empty($_POST))
    {
        
        $wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_calendar_category (category,bgcolor)VALUES('".esc_sql(stripslashes($_POST['category']))."','".esc_sql($_POST['color'])."')");
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Category Added.</strong></p>';
        echo '</div>';
        church_admin_category_list();
    }
    else
    {
        echo'<div class="wrap church_admin"><h2>Add Category</h2><form action="" method="post">';
        church_admin_category_form('');
        echo'<p><label>&nbsp;</label><input type="submit" name="add_category" value="Add Category"/></p></form></div>';  
    }
    
}

function church_admin_delete_category($id)
{
    global $wpdb;
    $wpdb->show_errors();
    //count how many events have that category
    $count=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_event.cat_id='".esc_sql($id)."' AND ".$wpdb->prefix."church_admin_calendar_event.event_id=".$wpdb->prefix."church_admin_calendar_date.event_id");
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_calendar_category WHERE cat_id='".esc_sql($id)."'");
    //adjust events with deleted cat_id to 0
    $wpdb->query("UPDATE ".$wpdb->prefix."church_admin_calendar_event SET cat_id='1' WHERE cat_id='".esc_sql($id)."'");
    echo '<div id="message" class="updated fade">';
        echo '<p><strong>Category Deleted.<br/>';
        if($count==1) echo 'Note that '.$count.' event used that category and will need editing.';
        if($count>1) echo 'Note that '.$count.' events used that category and will need editing.';
        echo'</strong></p>';
        echo '</div>';
        church_admin_category_list();
    
    
}
function church_admin_edit_category($id)
{
    global $wpdb;
    if(!empty($_POST))
    {
        $wpdb->query("UPDATE ".$wpdb->prefix."church_admin_calendar_category SET category='".esc_sql(stripslashes($_POST['category']))."',bgcolor='".esc_sql($_POST['color'])."' WHERE cat_id='".esc_sql($id)."'");
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Category Edited.</strong></p>';
        echo '</div>';
        church_admin_category_list();
    }
    else
    {
    echo'<div class="wrap church_admin"><h2>Edit Category</h2><form action="" method="post">';
    //grab current data
    $row=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category WHERE cat_id='".esc_sql($id)."'");
    church_admin_category_form($row);
    echo'<p><label>&nbsp;</label><input type="submit" name="edit_category" value="Edit Category"/></p></form>';
    church_admin_category_list();
    echo'</div>';
    }
}
function church_admin_category_form($data)
{
    if(empty($data))$data->bgcolor='#e4afb1';
echo '<script src="'.CHURCH_ADMIN_INCLUDE_URL.'farbtastic.js" type="text/javascript"></script><script type="text/javascript" charset="utf-8"> 
  $(document).ready(function() {
    
    $(\'#picker\').farbtastic(\'#color\');
    
    
  });
 </script>  
 <p><label >Category Name</label><input type="text" name="category" value="'.$data->category.'"/></p>
  <p><label >Background Colour</label><input type="text" id="color" name="color" value="'.$data->bgcolor.'" /></p><div id="picker"></div> 
 ';
}

function church_admin_calendar()
{
    global $wpdb;
    echo'<div class="wrap church_admin"><h2>Calendar</h2><p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_category_list">Category List</a></p>';
    church_admin_calendar_list();
    echo'</div>';
}

function church_admin_series_event($date_id,$event_id)
{

    global $wpdb,$sqlsafe;
    $wpdb->show_errors();
    if(!empty($_POST['edit_event']))
    {//form posted
        //check for errors
        $error=church_admin_calendar_error_check($_POST);
        if(empty($error))
        {
            foreach($_POST AS $key=>$value)$_POST[$key]=stripslashes($value);
        //delete current event_id
        $sql="DELETE FROM ".$wpdb->prefix."church_admin_calendar_event WHERE event_id='".esc_sql($event_id)."'";
        $wpdb->query($sql);
        $sql="DELETE FROM ".$wpdb->prefix."church_admin_calendar_date WHERE event_id='".esc_sql($event_id)."'";
        $wpdb->query($sql);
        //recreate event
        //check not already done
        $check=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_event WHERE title='{$sqlsafe['title']}' AND description='{$sqlsafe['description']}' AND location='{$sqlsafe['location']}' AND cat_id='{$sqlsafe['category']}' AND year_planner='{$sqlsafe['year_planner']}'");
        if(!$check)
        {
            
        //put event details into church_admin_calender_event table
        $sql="INSERT INTO ".$wpdb->prefix."church_admin_calendar_event (title,description,location,cat_id,year_planner,recurring)VALUES('{$sqlsafe['title']}','{$sqlsafe['description']}','{$sqlsafe['location']}','{$sqlsafe['category']}','{$sqlsafe['year_planner']}','{$sqlsafe['recurring']}')";
        $wpdb->query($sql);
        $event_id=$wpdb->insert_id;
        //handle event types
        if($sqlsafe['recurring']=='s')
        {
            $values[]="('{$sqlsafe['start_date']}','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        }
        elseif($sqlsafe['recurring']=='n')
        {
            //handle nth
            for($x=1;$x<$sqlsafe['how_many'];$x++)
            {
               $start_date=nthday($sqlsafe['nth'],$sqlsafe['day'],date('Y-m-d',strtotime($sqlsafe['start_date']." +$x month")));
               
                $values[]="('$start_date','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','{$event_id}')";
                
            }
        }
        else
        {
        if( $sqlsafe['recurring']=='1') $int='day';
        if($sqlsafe['recurring']=='14') $int='fortnight';
        if( $sqlsafe['recurring']=='7') $int='week';
        if( $sqlsafe['recurring']=='m') $int='month';
        if( $sqlsafe['recurring']=='a') $int='year';    
        $values[]="('{$sqlsafe['start_date']}','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        for($x=1;$x<$sqlsafe['how_many'];$x++)
        {
        $start=date('Y-m-d',strtotime("{$sqlsafe['start_date']}+$x $int"));
    
        $values[]="('$start','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        }
        }
        $sql="INSERT INTO ".$wpdb->prefix."church_admin_calendar_date (start_date,start_time,end_time,event_id) VALUES".implode(',',$values);
        $wpdb->query($sql);
      
      //end of process
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Calendar Event Series Edited.</strong></p>';
        echo '</div>';
        church_admin_cache_year_planner();
        church_admin_calendar_list();
        }//end of event not already in db
        }
        else
        {//there was an error
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>There was an error in your form.</strong></p>';
        echo '</div>';
        
        }//end of there was an error
        
    }//end form posted
    else
    {//form 
    $sql="SELECT ".$wpdb->prefix."church_admin_calendar_event.*,".$wpdb->prefix."church_admin_calendar_date.*,".$wpdb->prefix."church_admin_calendar_category.* FROM ".$wpdb->prefix."church_admin_calendar_event,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_category WHERE ".$wpdb->prefix."church_admin_calendar_event.event_id=".$wpdb->prefix."church_admin_calendar_date.event_id AND ".$wpdb->prefix."church_admin_calendar_date.date_id='".esc_sql($date_id)."' AND ".$wpdb->prefix."church_admin_calendar_date.event_id='".esc_sql($event_id)."'";
   
    $data=$wpdb->get_row($sql);
    $data->start_date=mysql2date('d/m/Y',$data->start_date);
     $data->how_many=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_date WHERE event_id='".esc_sql($event_id)."'");
    echo'<div class="wrap church_admin"><h2>Edit a Series Calendar Item</h2><form action="" id="calendar" method="post">';
    echo church_admin_calendar_form($data,$error,1);
    echo '<p><label>&nbsp;</label><input type="submit" name="edit_event" value="Edit Event"/></form></div>';    
    }//end form
}
function church_admin_single_event_delete($date_id,$event_id)
{
    global $wpdb;
    $count=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_date WHERE event_id='".esc_sql($event_id)."'");
    if($count==0){$wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_calendar_event WHERE event_id='".esc_sql($event_id)."'");}
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_calendar_date WHERE date_id='".esc_sql($date_id)."'");
    echo '<div id="message" class="updated fade">';
    echo '<p><strong>Calendar Event deleted.</strong></p>';
    echo '</div>';
    church_admin_cache_year_planner();
    church_admin_calendar_list();
}
function church_admin_series_event_delete($date_id,$event_id)
{
    global $wpdb;
    $count=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_date WHERE event_id='".esc_sql($event_id)."'");
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_calendar_event WHERE event_id='".esc_sql($event_id)."'");
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_calendar_date WHERE date_id='".esc_sql($date_id)."'");
    echo '<div id="message" class="updated fade">';
    echo '<p><strong>Calendar Events deleted.</strong></p>';
    echo '</div>';
    church_admin_cache_year_planner();
    church_admin_calendar_list();
}
function church_admin_single_event_edit($date_id,$event_id)
{
    //This function is to edit a single event (in a single or recurring sequence)
    global $wpdb,$sqlsafe;
    
    if(!empty($_POST['edit_event']))
    {//process
        foreach($_POST AS $key=>$value)$_POST[$key]=stripslashes($value);
        $how_many=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_date WHERE ".$wpdb->prefix."church_admin_calendar_date.event_id='".esc_sql($event_id)."'");
         $error=church_admin_calendar_error_check($_POST);
    
      if(empty($error))
      {//no errors
        if($how_many=='1')
        {
            //overwrite event
            $wpdb->query("UPDATE ".$wpdb->prefix."church_admin_calendar_event SET title='{$sqlsafe['title']}',description='{$sqlsafe['description']}',cat_id='{$sqlsafe['category']}',location='{$sqlsafe['location']}',recurring='{$sqlsafe['recurring']}' WHERE event_id='".esc_sql($event_id)."'");
           
        }else
        {
        
            //insert new event
        
        $sql="INSERT INTO ".$wpdb->prefix."church_admin_calendar_event (title,description,location,cat_id,year_planner,recurring)VALUES('{$sqlsafe['title']}','{$sqlsafe['description']}','{$sqlsafe['location']}','{$sqlsafe['category']}','{$sqlsafe['year_planner']}','{$sqlsafe['recurring']}')";
        $wpdb->query($sql);
        $event_id=$wpdb->insert_id;
            //new $event_id
        }
        //update $date_id entry
        $sql="UPDATE ".$wpdb->prefix."church_admin_calendar_date SET start_date='{$sqlsafe['start_date']}',start_time='{$sqlsafe['start_time']}',end_time='{$sqlsafe['end_time']}',event_id='".esc_sql($event_id)."' WHERE date_id='".esc_sql($date_id)."'";
        
        $wpdb->query($sql);
          echo '<div id="message" class="updated fade">';
        echo '<p><strong>Calendar Event edited.</strong></p>';
        echo '</div>';
        church_admin_cache_year_planner();
        church_admin_calendar_list();
      }//end no errors
      else
      {//errors
        echo "oops";
       
      }//end errors
    }//end process 
    else
    {// form not submitted
    $sql="SELECT ".$wpdb->prefix."church_admin_calendar_event.*,".$wpdb->prefix."church_admin_calendar_date.*,".$wpdb->prefix."church_admin_calendar_category.* FROM ".$wpdb->prefix."church_admin_calendar_event,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_category WHERE ".$wpdb->prefix."church_admin_calendar_event.event_id=".$wpdb->prefix."church_admin_calendar_date.event_id AND ".$wpdb->prefix."church_admin_calendar_date.date_id='".esc_sql($date_id)."' AND ".$wpdb->prefix."church_admin_calendar_date.event_id='".esc_sql($event_id)."' AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id";
    
    $data=$wpdb->get_row($sql);
    $data->start_date=mysql2date('d/m/Y',$data->start_date);
    
    echo'<div class="wrap church_admin"><h2>Edit a Single Calendar Item</h2><form action="" id="calendar" method="post">';
    echo church_admin_calendar_form($data,$error,0);
    echo '<p><label>&nbsp;</label><input type="submit" name="edit_event" value="Edit Event"/></form></div>';
    }//end form not submitted
}


function church_admin_add_calendar()
{
    global $wpdb,$error,$sqlsafe;
    foreach($_POST AS $key=>$value){$_POST[$key]=stripslashes($value);}
    $wpdb->show_errors();
    
    if(!empty($_POST['add_event']))
    {
    foreach($_POST AS $key=>$value){$_POST[$key]=stripslashes($value);}
      $error=array(); //initialise error array
      $sqlsafe=array();//initialise mysqlsafe array
      $error=church_admin_calendar_error_check($_POST);
      if(empty($error))
      {
        //print_r($sqlsafe);
        //process
        //check not already done
        $check=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_event WHERE title='{$sqlsafe['title']}' AND description='{$sqlsafe['description']}' AND location='{$sqlsafe['location']}' AND cat_id='{$sqlsafe['category']}' AND year_planner='{$sqlsafe['year_planner']}'");
        if(!$check)
        {
        //put event details into church_admin_calender_event table
        $sql="INSERT INTO ".$wpdb->prefix."church_admin_calendar_event (title,description,location,cat_id,year_planner,recurring)VALUES('{$sqlsafe['title']}','{$sqlsafe['description']}','{$sqlsafe['location']}','{$sqlsafe['category']}','{$sqlsafe['year_planner']}','{$sqlsafe['recurring']}')";
        
        $wpdb->query($sql);
        $event_id=$wpdb->insert_id;
        //handle event types
        if($sqlsafe['recurring']=='s')
        {
            $values[]="('{$sqlsafe['start_date']}','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        }
        elseif($sqlsafe['recurring']=='n')
        {
            
            //handle nth
            for($x=0;$x<$sqlsafe['how_many'];$x++)
            {
               $start_date=nthday($sqlsafe['nth'],$sqlsafe['day'],date('Y-m-d',strtotime($sqlsafe['start_date']." +$x month")));
               
                $values[]="('$start_date','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','{$event_id}')";
            }
        }
        else
        {
        if( $sqlsafe['recurring']=='1') $int='day';
        if( $sqlsafe['recurring']=='7') $int='week';
        if($sqlsafe['recurring']=='14') $int='fortnight';
        if( $sqlsafe['recurring']=='m') $int='month';
        if( $sqlsafe['recurring']=='a') $int='year';    
        $values[]="('{$sqlsafe['start_date']}','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        for($x=1;$x<$sqlsafe['how_many'];$x++)
        {
        $start=date('Y-m-d',strtotime("{$sqlsafe['start_date']}+$x $int"));
        
        $values[]="('$start','{$sqlsafe['start_time']}','{$sqlsafe['end_time']}','$event_id')";
        }
        }
        $sql="INSERT INTO ".$wpdb->prefix."church_admin_calendar_date (start_date,start_time,end_time,event_id) VALUES".implode(',',$values);
        
        $wpdb->query($sql);
      
      //end of process
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Calendar Event added.</strong></p>';
        echo '</div>';
        church_admin_cache_year_planner();
        church_admin_calendar_list();
        }//end of event not already in db
      }//end of no errors
      else
      {
        //errors in form
        //convert $_POST to $data object
          foreach($error AS $key=>$value)
        {
            if($value==1){$error[$key]=' class="red"';}else{$error[$key]=NULL;}
        }
        
        $data=array_to_object($_POST);
      echo'<div class="wrap church_admin"><h2>Add a Calendar Item</h2><p><em>There were some errors, marked in red</em></p><form action="" id="calendar" method="post">';
        echo church_admin_calendar_form($data,$error,1);
        echo '<p><label>&nbsp;</label><input type="hidden" name="add_event"  value="y"/><input type="submit" value="Add Event"/></form>';
        
      }//end of error handling
      
    }//end of form submitted
    else
    {
      
        echo'<div class="wrap church_admin"><h2>Add a Calendar Item</h2><form action="" id="calendar" method="post">';
        echo church_admin_calendar_form($data,$error,1);
        echo '<p><label>&nbsp;</label><input name="add_event" type="hidden" value="y"/> <input type="submit"  value="Add Event"/></p></form></div>';
        
    }
    
}
function church_admin_calendar_error_check($data)
{
    global $error,$sqlsafe;
     //check startdate
      $start_date=church_admin_dateCheck($data['start_date'], $yearepsilon=50);
      
      $end_date=church_admin_dateCheck($data['end_date'], $yearepsilon=50);
      
      if($start_date){$sqlsafe['start_date']=mysql_real_escape_string($start_date);}else{$error['start_date']==1;}
      
      //check start time
   if (preg_match ("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['start_time'])){$sqlsafe['start_time']=$data['start_time'];}else{$error['start_time']='1';}
        //check end time
  if (preg_match("/([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1})/", $data['end_time'])){$sqlsafe['end_time']=$data['end_time'];}else{$error['end_time']='1';}
 
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
            $error['how_many']=1;
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
                $error['nth']=$error['day']=1;
            }
        }
       if(!empty($data['title'])){ $sqlsafe['title']= esc_sql($data['title']);}else{$error['title']=1;}
       if(!empty($data['description'])){ $sqlsafe['description']= esc_sql(nl2br($data['description']));}else{$error['description']=1;}
       $sqlsafe['description']=strip_tags($sqlsafe['description']);
      $sqlsafe['location']=esc_sql($data['location']);
      if(!empty($_POST['category'])&&ctype_digit($data['category'])){$sqlsafe['category']=$data['category'];}else{$error['category']=1;}
      if($data['year_planner']=='1'){$sqlsafe['year_planner']=1;}else{$sqlsafe['year_planner']=0;}
      
    return $error;  
}
function church_admin_calendar_form($data,$error,$recurring=1)
{
    
    global $wpdb;
  
    $wpdb->show_errors();
    $out='  <script type="text/javascript" src="'.CHURCH_ADMIN_INCLUDE_URL.'javascript.js"></script>
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
</script>
<p><label>Event Title</label><input type="text" name="title" value="'.stripslashes($data->title).'" '.$error['title'].' /></p>
<p><label>Event Description</label><textarea rows="5" cols="50" name="description" '.$error['description'].'>'.stripslashes($data->description).'</textarea></p>
<p><label>Event Location</label><textarea rows="5" cols="50" name="location" '.$error['location'].'>'.stripslashes($data->location).'</textarea></p>
<p><label> Category</label><select name="category" '.$error['category'].' >';
$first='<option value="">Please select...</option>';
$sql="SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category";
$result3=$wpdb->get_results($sql);
foreach($result3 AS $row)
{
    if($data->cat_id==$row->cat_id)
    {
        
        $first='<option value="'.$data->cat_id.'" style="background:'.$data->bgcolor.'">'.$data->category.'</option>';
    }
    else
    {
        $select.='<option value="'.$row->cat_id.'" style="background:'.$row->bgcolor.'">'.$row->category.'</option>';
    }
}

$out.=$first.$select;//have original value first!
$out.='</select></p>
<p><label >Start Date</label><input name="start_date" type="text" '.$error['start_date'].' value="'.$data->start_date.'" size="25" /><a href="#" onclick="cal_begin.select(document.forms[\'calendar\'].start_date,\'date_anchor1\',\'dd/MM/yyyy\'); return false;" name="date_anchor1" id="date_anchor1"><img src="'.CHURCH_ADMIN_IMAGES_URL.'cal.gif" width="16" height="16" border="0" alt="Pick a date"/></a></p><div id="pop_up_cal" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div>';
if($recurring==1){
    $out.='
<p><label>Recurring</label>
<select name="recurring" '.$error['recurring'].' id="recurring" onchange="OnChange(\'recurring\')">
';
if(!empty($data->recurring))
{
    $option=array(s=>'Once',1=>'Daily',7=>'Weekly',n=>'nth day eg.1st Friday',m=>'Monthly',a=>'Annually');
    $out.= '<option value="'.$data->recurring.'">'.$option[$data->recurring].'</option>';
}
$out.='
<option value="s">Once</option>
<option value="1">Daily</option>
<option value="7">Weekly</option>
<option value="14">Fortnightly</option>
<option value="n">nth day (eg 1st Friday)</option>
<option value="m">Monthly on same date</option>
<option value="a">Annually</option>
</select></p>
<div id="nth" ';
if($data->recurring=='n'){$out.='style="display:block"';}else{$out.='style="display:none"';}
$out.='><p><label>Recurring on </label><select '.$error['nth'].' name="nth">';
if(!empty($data->nth)) $out.='<option value="'.$data->nth.'">'.$data->nth.'</option>';
$out.='<option value="1">1st</option><option value="2">2nd</option><option value="3">3rd</option><option value="4">4th</option></select>&nbsp;<select name="day"><option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option></select></p></div>
<div id="howmany" ';
if(!empty($data->recurring) && $data->recurring!='s'){$out.='style="display:block"';}else{$out.='style="display:none"';}
$out.='><p><label>How many times in all?</label><input type="text" '.$error['how_many'].' name="how_many" value="'.$data->how_many.'"/></p></div>';
}//end recurring
else
{
    $out.='<input type="hidden" name="recurring" value="s"/><input type="hidden" name="how_many" value="1"/>';
}
$data->start_time=substr($data->start_time,0,5);//remove seconds
$data->end_time=substr($data->end_time,0,5);//remove seconds
$out.='<p><label>Start Time of form HH:MM</label><input type="text" name="start_time" '.$error['start_time'].' value="'.$data->start_time.'"/></p>
<p><label>End Time of form HH:MM</label><input type="text" name="end_time" '.$error['end_time'].' value="'.$data->end_time.'" /></p>
<p><label>Appear on Year Planner?</label><input type="checkbox" name="year_planner" value="1"';
if($data->year_planner) $out.=' checked="checked"';
$out.='/></p>
';
return $out;
}

function church_admin_calendar_list()
{
    global $wpdb;
    if(empty($_REQUEST['date']))$_REQUEST['date']=time();
   echo'<div class="wrap church_admin"><p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_calendar&amp;date='.$_GET['date'].'">Add calendar Event</a></p>';
$events=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_calendar_event"); 
 if(!empty($events))
{
     //which month to view
    $current=(isset($_REQUEST['date'])) ? intval($_REQUEST['date']) : time(); //get user date or use today
    $next = strtotime("+1 month",$current);
    $previous = strtotime("-1 month",$current);
    $now=date("M Y",$current);
    $sqlnow=date("Y-m%", $current);
    $sqlnext=date("Y-m-d",$next);
    
    echo '<table><tr><td><a href="admin.php?page=church_admin_calendar&amp;date='.$previous.'">Prev</a> '.$now.' <a href="admin.php?page=church_admin_calendar&amp;date='.$next.'">Next</a></td><td>';
    echo'<form action="" method="post"><select name="date">';
    if(isset($_REQUEST['date']))echo '<option value="'.$_REQUEST['date'].'">'.date('M Y',$_REQUEST['date']).'</option>';
//generate a form to access calendar
for($x=0;$x<12;$x++)
{
    $date=strtotime("+ $x month",time());
    echo '<option value="'.$date.'">'.date('M Y',$date).'</option>';
}
echo '</select><input type="submit" value="Go to date"/></form></td></tr></table>';
    //initialise table
    $table='<table class="widefat"><thead><tr><th>Single Edit</th><th>Series Edit</th><th>Single Delete</th><th>Series Delete</th><th>Start date</th><th>Start Time</th><th>End Time</th><th>Event Name</th><th>Category</th><th>Year Planner?</th></tr></thead><tfoot><tr><th>Single Edit</th><th>Series Edit</th><th>Single Delete</th><th>Series Delete</th><th>Start date</th><th>Start Time</th><th>End Time</th><th>Event Name</th><th>Category</th><th>Year Planner?</th></tr></tfoot><tbody>';
    $sql="SELECT ".$wpdb->prefix."church_admin_calendar_date.*,".$wpdb->prefix."church_admin_calendar_event.*,".$wpdb->prefix."church_admin_calendar_category.* FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_date,".$wpdb->prefix."church_admin_calendar_event WHERE ".$wpdb->prefix."church_admin_calendar_date.event_id=".$wpdb->prefix."church_admin_calendar_event.event_id AND ".$wpdb->prefix."church_admin_calendar_date.start_date LIKE '$sqlnow' AND ".$wpdb->prefix."church_admin_calendar_category.cat_id=".$wpdb->prefix."church_admin_calendar_event.cat_id ORDER BY ".$wpdb->prefix."church_admin_calendar_date.start_date";
  
   $result=$wpdb->get_results($sql);
    foreach($result AS $row)
    {
    //create links
    $single_edit_url='<a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_single_event_edit&amp;event_id={$row->event_id}&amp;date_id={$row->date_id}&amp;date={$_GET['date']}",'church admin single event edit').'">Edit</a>';
    if($row->recurring=='s'){$series_edit_url='&nbsp;';}else{$series_edit_url='<a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_series_event_edit&amp;event_id={$row->event_id}&amp;date_id={$row->date_id}&amp;date={$_GET['date']}",'church admin series event edit').'">Edit Series</a>';}
    $single_delete_url='<a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_single_event_delete&amp;event_id={$row->event_id}&amp;date_id={$row->date_id}&amp;date={$_GET['date']}",'single_event_delete').'">Delete this one</a>';

    if($row->recurring=='s'){$series_delete_url='&nbsp;';}else{$series_delete_url='<a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_series_event_delete&amp;event_id={$row->event_id}&amp;date_id={$row->date_id}&amp;date={$_GET['date']}",'series_event_delete').'">Delete Series</a>';}
    
    //sort out category
    
     $table.='<tr><td>'.$single_edit_url.'</td><td>'.$series_edit_url.'</td><td>'.$single_delete_url.'</td><td>'.$series_delete_url.'</td><td>'.mysql2date('j F Y',$row->start_date).'</td><td>'.$row->start_time.'</td><td>'.$row->end_time.'</td><td>'.htmlentities($row->title).'</td><td style="background:'.$row->bgcolor.'">'.htmlentities($row->category).'</td><td>';
     if($row->year_planner){$table.='Yes';}else{$table.='&nbsp;';}
     $table.='</td></tr>';
    }
    $table.='</tbody></table>';
    echo $table.'</div>';
}//end of non empty calendar table

}


function church_admin_dateCheck($date, $yearepsilon=5000) { // inputs format is "DD/MM/YYYY" ONLY !
if (count($datebits=explode('/',$date))!=3) return false;
$year = intval($datebits[2]);
$month = intval($datebits[1]);
$day = intval($datebits[0]);
if ((abs($year-date('Y'))>$yearepsilon) || // year outside given range
($month<1) || ($month>12) || ($day<1) ||
(($month==2) && ($day>28+(!($year%4))-(!($year%100))+(!($year%400)))) ||
($day>30+(($month>7)^($month&1)))) return false; // date out of range
if( checkdate($month,$day,$year)) {return ($year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day));}else{return FALSE;}
}


if(!function_exists(array_to_object)) {
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
    $days=array(0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday');
    $month=date('M',strtotime($date));
    $year=date('Y',strtotime($date));
    return date('Y-m-d',strtotime("+$nth {$days[$day]} $month $year"));
}
?>
