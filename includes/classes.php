<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function  church_admin_classes()
{
    global $wpdb;
	echo'<hr/><h2><a id="classes">'.__('Classes','church-admin').'</a></h2>';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_class&tab=people','edit_class').'">'.__('Add a class','church-admin').'</a></p>';
	
	$classes=$wpdb->get_results('SELECT * FROM '.CA_CLA_TBL.' ORDER BY class_order');
	if(!empty($classes))
	{
		echo'<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Class Name (Click to view)','church-admin').'</th><th>'.__('Next Start Date','church-admin').'</th><th>'.__('Repeat','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Class Name (Click to view)','church-admin').'</th><th>'.__('Next Start Date','church-admin').'</th><th>'.__('Repeat','church-admin').'</th></tr></tfoot><tbody>';
		foreach($classes AS $row)
		{
			
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&amp;action=edit_class&amp;id='.intval($row->class_id),'edit_class').'">'.__('Edit','church-admin').'</a>';
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&amp;action=delete_class&amp;id='.intval($row->class_id),'delete_class').'">'.__('Delete','church-admin').'</a>';
            echo'<tr>';
			echo'<td>'.$edit.'</td><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;tab=people&amp;action=view_class&amp;id='.intval($row->class_id),'view_class').'">'.esc_html($row->name).'</a></td>';
			echo'<td>'.mysql2date(get_option('date_format'),$row->next_start_date).'</td>';
			switch($row->recurring)
			{
				case's':$recurring=__('Once','church-admin');break;
				case'1':$recurring=__('Daily','church-admin');break;
				case'7':$recurring=__('Weekly','church-admin');break;
				case'n':$recurring=__('Nth Day','church-admin');break;
				case'm':$recurring=__('Monthly','church-admin');break;
				case'a':$recurring=__('Annually','church-admin');break;
				
			}
			echo'<td>'.esc_html($recurring).'</td>';
			echo'</tr>';
        }
		
		echo'</tbody></table>';
	}
	
}
function church_admin_delete_class($class_id=NULL)
{
	global $wpdb;
	$event_id=$wpdb->get_var('SELECT event_id FROM '.CA_CLA_TBL.' WHERE class_id="'.esc_sql($class_id).'"');
	if(!empty($event_id))$wpdb->query('DELETE FROM '.CA_DATE_TBL.' WHERE event_id="'.esc_sql($event_id).'"');
	$wpdb->query('DELETE FROM '.CA_CLA_TBL.' WHERE class_id="'.esc_sql($class_id).'"');
	echo'<div class="updated fade"><p>'.__('Class Deleted','church-admin').'</p></div>';
	church_admin_classes();
}

function church_admin_edit_class($class_id=NULL)
{
	global $wpdb;
	echo'<h2>'.__('Edit Class','church-admin').'</h2>';
	if(!empty($class_id))
	{
		$data=$wpdb->get_row('SELECT * FROM '.CA_CLA_TBL.' WHERE class_id="'.esc_sql($class_id).'"');
		
	}
	if(!empty($_POST['save']))
	{
		
		$sql=array();
		foreach($_POST AS $key=>$value) $sql[$key]=esc_sql(stripslashes($value));
		if(empty($class_id))$class_id=$wpdb->get_var('SELECT class_id FROM '.CA_CLA_TBL.' WHERE name="'.$sql['name'].'" AND description="'.$sql['description'].'" AND next_start_date="'.$sql['next_start_date'].'" AND recurring="'.$sql['recurring'].'" AND howmany="'.$sql['how_many'].'" AND start_time="'.$sql['start_time'].'" AND end_time="'.$sql['end_time'].'"');
		if(empty($class_id))
		{
			$query='INSERT INTO '.CA_CLA_TBL.' (name,description,next_start_date,recurring,howmany,start_time,end_time)VALUES("'.$sql['name'].'","'.$sql['description'].'","'.$sql['next_start_date'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['start_time'].'","'.$sql['end_time'].'")';
			echo $query;
			$wpdb->query($query);
			$class_id=$wpdb->insert_id;
		}
		else{
			$query='UPDATE '.CA_CLA_TBL.' SET name="'.$sql['name'].'" , description="'.$sql['description'].'" , next_start_date="'.$sql['next_start_date'].'" , recurring="'.$sql['recurring'].'" , howmany="'.$sql['how_many'].'" AND start_time="'.$sql['start_time'].'" AND end_time="'.$sql['end_time'].'" WHERE class_id="'.esc_sql($class_id).'"';
			$wpdb->query($query);
		}
		if(!empty($sql['calendar']))
			{
					$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.CA_DATE_TBL)+1;
			if(!empty($data->event_id))$wpdb->query('DELETE FROM '.CA_DATE_TBL.' WHERE event_id="'.esc_sql($data->event_id).'"');
			$event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.CA_DATE_TBL)+1;
			switch($_POST['recurring'])
			{
				case's':
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,cat_id,event_id,how_many,start_date,start_time,end_time,facilities_id,general_calendar)VALUES("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['cat_id'].'","'.$event_id.'","'.$sql['how_many'].'","'.$sql['start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","1")';
				break;
				case'n':
					//handle nth
					require_once(plugin_dir_path(__FILE__).'includes/calendar.php');
					$values=array();
					for($x=0;$x<$sql['how_many'];$x++)
					{
						$sql['next_start_date']=nthday($sql['nth'],$sql['day'],date('Y-m-d',strtotime($sql['start_date']." +$x month")));
               			$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$sql['next_start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case '14':
					$values=array();
					for($x=0;$x<$sql['how_many'];$x++)
					{
						$sql['next_start_date']=date('Y-m-d',strtotime("{$sql['start_date']}+$x fortnight"));
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$sql['next_start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case '7':
					$values=array();
					for($x=0;$x<$sql['how_many'];$x++)
					{
						$date=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x week"));
						echo $date.'<br/>';
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$date.'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case 'm':
					$values=array();
					for($x=0;$x<$sql['how_many'];$x++)
					{
						$sql['next_start_date']=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x month"));
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$sql['next_start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
				case 'a':
					$values=array();
					for($x=1;$x<$sql['how_many'];$x++)
					{
						$sql['next_start_date']=date('Y-m-d',strtotime("{$sql['next_start_date']}+$x year"));
						$values[]='("'.$sql['name'].'","'.$sql['description'].'","'.$sql['recurring'].'","'.$sql['how_many'].'","'.$sql['cat_id'].'","'.$sql['next_start_date'].'","'.$sql['start_time'].'","'.$sql['end_time'].'","1","'.$event_id.'")';
					}
					$sql='INSERT INTO '.CA_DATE_TBL.' (title,description,recurring,how_many,cat_id,start_date,start_time,end_time,general_calendar,event_id)VALUES'.implode(",",$values);
				break;
		
			}
				$wpdb->query($sql);
			$cal_id=$wpdb->insert_id;
			$wpdb->query('UPDATE '.CA_CLA_TBL.' SET event_id="'.esc_sql($event_id).'" WHERE class_id="'.esc_sql($class_id).'"');
		}
		echo'<div class="updated fade"><p>Class updated</p></div>';
		church_admin_classes();
	}
	else
	{
		echo'<form action="" method="POST"><table class="form-table">';
		echo'<tr><th scope="row">'.__('Class Name','church-admin').'</th><td><input type="text" name="name" ';
		if(!empty($data->name)) echo 'value="'.esc_html($data->name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('Class Description','church-admin').'</th><td><textarea name="description">';
		if(!empty($data->name)) echo esc_html($data->description);
		echo'</textarea></td></tr>';
		echo'<tr><th scope="row">'.__('Next Start Date','church-admin').' (yyyy-mm-dd)</th><td><input type="text" class="next_start_date" name="next_start_date" ';
		if(!empty($data->next_start_date)) echo 'value="'.esc_html($data->next_start_date).'"';
		echo'/><script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.next_start_date\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script></td></tr>';
		echo '<tr><th scope="row">'.__('Recurring','church-admin').'</th><td><select name="recurring" ';
		echo ' id="recurring" onchange="OnChange(\'recurring\')">';
		if(!empty($data->recurring))
		{
			$option=array('s'=>__('Once','church-admin'),'1'=>__('Daily','church-admin'),'7'=>__('Weekly','church-admin'),'n'=>__('nth day eg.1st Friday','church-admin'),'m'=>__('Monthly','church-admin'),'a'=>__('Annually','church-admin'));
			echo'<option value="'.$data->recurring.'">'.$option[$data->recurring].'</option>';
		}
		echo'<option value="s">'.__('Once','church-admin').'</option><option value="1">'.__('Daily','church-admin').'</option><option value="7">'.__('Weekly','church-admin').'</option><option value="14">'.__('Fortnightly','church-admin').'</option><option value="n">'.__('nth day (eg 1st Friday)','church-admin').'</option><option value="m">'.__('Monthly on same date','church-admin').'</option><option value="a">'.__('Annually','church-admin').'</option></select></td></tr>';
		echo'<tr id="nth" ';
		if(empty($data->recurring) || $data->recurring !='n')echo 'style="display:none"';
		echo'><th scope="row">'.__('Recurring on','church-admin').'</th><td><select name="nth">';
		if(!empty($data->recurring)) echo'<option value="'.esc_html($data->recurring).'">'.esc_html($data->recurring).'</option>';
			echo'<option value="1">'.__('1st','church-admin').'</option><option value="2">'.__('2nd','church-admin').'</option><option value="3">'.__('3rd','church-admin').'</option><option value="4">'.__('4th','church-admin').'</option></select>&nbsp;<select name="day"><option value="0">'.__('Sunday','church-admin').'</option><option value="1">'.__('Monday','church-admin').'</option><option value="2">'.__('Tuesday','church-admin').'</option><option value="3">'.__('Wednesday','church-admin').'</option><option value="4">'.__('Thursday','church-admin').'</option><option value="5">'.__('Friday','church-admin').'</option><option value="6">'.__('Saturday','church-admin').'</option></select></td></tr><script type="text/javascript">

function OnChange(){
if(document.getElementById(\'recurring\').value==\'s\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'none\';
		}
if(document.getElementById(\'recurring\').value==\'1\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'7\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';;
		}
if(document.getElementById(\'recurring\').value==\'14\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';;
		}                
if(document.getElementById(\'recurring\').value==\'n\'){
		document.getElementById(\'nth\').style.display = \'table-row\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'m\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
if(document.getElementById(\'recurring\').value==\'a\'){
		document.getElementById(\'nth\').style.display = \'none\';
		document.getElementById(\'howmany\').style.display = \'table-row\';
		}
}
</script>';
			echo'<tr id="howmany" ';
			if(empty($data->howmany))echo ' style="display:none"';
			echo '><th scope="row">'.__('How many times in all?','church-admin').'</th><td><input type="text" name="how_many" ';
		if(!empty($data->howmany))echo' value="'.intval($data->howmany).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('Show on calendar?','church-admin').'</th><td><input type="checkbox" name="calendar" value="yes" ';
		if(!empty($data->calendar)){echo' checked="checked" ';}
		echo'/></td></tr>';
		echo'<tr><th scope="row"> '.__('Category','church-admin').'</th><td><select name="cat_id" ';
		echo' >';
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
		echo $first.$select;//have original value first!
		echo'</select></td></tr>';
		if(!empty($data->start_time))$data->start_time=substr($data->start_time,0,5);//remove seconds
		if(!empty($data->end_time))$data->end_time=substr($data->end_time,0,5);//remove seconds
		echo '<tr><th scope="row">'.__('Start Time of form HH:MM','church-admin').'</th><td><input type="text" name="start_time" ';
		if(!empty($data->start_time))echo' value="'.$data->start_time.'"';
		echo'/></td></tr>';
		echo '<tr><th scope="row">'.__('End Time of form HH:MM','church-admin').'</th><td><input type="text" name="end_time" ';
		if(!empty($data->end_time))echo' value="'.$data->end_time.'"';
		echo'/></td></tr>';
		echo'<tr><td colspan="2"><input type="hidden" name="save" value="yes"/><input type="submit" class="button-primary" value="'.__('Save','church-admin').'"/></form>';
		echo'</table>';
		
	}
	
}

function church_admin_view_class($id)
{
	global $wpdb;
	$data=$wpdb->get_row('SELECT * FROM '.CA_CLA_TBL.' WHERE class_id="'.esc_sql($id).'"');
	echo'<h2>'.esc_html($data->name).'</h2>';
	
	if(!empty($_POST['completed']))
	{
	if(!empty($_POST['date'])&&checkDateFormat($_POST['date'])){$date=esc_sql($_POST['date']);}else{$date=date('Y-m-d');}
		$completed=church_admin_get_people_id($_POST['completed']);
		if(!empty($completed))$complete=maybe_unserialize($completed);
		if(!empty($complete))
		{
			$values=array();
			foreach($complete AS $key=>$people_id)
			{
				$values[]='("class","'.esc_sql($id).'","'.esc_sql($people_id).'","'.$date.'")';
			}
			$wpdb->query('INSERT INTO '.CA_MET_TBL.' (meta_type,department_id,people_id,meta_date)VALUES '.implode(',',$values));
		}
		
	}
	if(!empty($_POST['delegate']))
	{
	
		$delegates=church_admin_get_people_id($_POST['delegate']);
		print_r($delegates);
		if(!empty($delegates))$delegate=maybe_unserialize($delegates);
		if(!empty($delegate))
		{
			$values=array();
			foreach($delegate AS $key=>$people_id)
			{
				$values[]='("class","'.esc_sql($id).'","'.esc_sql($people_id).'","0000-00-00")';
			}
			$sql='INSERT INTO '.CA_MET_TBL.' (meta_type,department_id,people_id,meta_date)VALUES '.implode(',',$values);
		
			$wpdb->query($sql);
		}
		
	}
	echo'<form action="" method="post"><table class="form-table">';
	echo'<tr><th scope="row">'.__('Add people on the class','church-admin').'</th><td>'.church_admin_autocomplete('delegate').'</td></tr>';
	echo'<tr><th scope="row">'.__('Add people who have completed it','church-admin').'</th><td>'.church_admin_autocomplete('completed').'</td></tr>';
	echo'<tr><th scope="row">Date:</th><td><input type="text" id="date" name="date" /></td></tr>';
	echo'<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'#date\').datepicker({dateFormat : "yy-mm-dd", changeYear: true });});</script>';
	echo'<tr><td colspan=2><input type="submit" value="'.__('Save','church-admin').'"/></td></tr>';
	echo'</table></form>';
	$people=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name,b.meta_date FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.meta_type="class" AND b.department_id="'.esc_sql($id).'"');
	if(!empty($people))
	{
		echo'<table class="widefat striped"><thead><tr><th>Delete</th><th>'.__('Name','church-admin').'</th><th>'.__('Date','church-admin').'</th></tr></thead><tfoot><tr><th>Delete</th><th>'.__('Name','church-admin').'</th><th>'.__('Date','church-admin').'</th></tr></tfoot><tbody>';
		foreach($people AS $person)
		{
			echo'<tr><td>'.__('Delete','church-admin').'</td><td>'.esc_html($person->name).'</td><td>';
			if($person->meta_date!='0000-00-00') {echo __('Completed','church-admin').': ';}
			echo mysql2date(get_option('date_format'),$person->meta_date);
			echo'</td></tr>';
		}
		echo'</tbody></table>';
	}else{echo '<p>'.__('No one has done that class yet','church-admin').'</p>';}
}
?>