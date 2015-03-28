<?php
function church_admin_individual_attendance()
{
/**
 *
 * Individual attendance tracking form
 * 
 * @author  	Andy Moyle
 * @param    	null
 * @return   	html
 * @version  	0.1
 * @date 		2015-02-01
 */

	global $wpdb,$days;
	echo'<div class="wrap church_admin"><h2>'.__('Individual attendance','church-admin').'</h2>';
	if (!empty($_POST['save_attendance']))
	{//process form
	
		$meeting=explode('-',$_POST['meeting']);
		$sqlsafe=array();
		$adult=$child=0;
		foreach($_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(stripslashes_deep($value));
		foreach($sqlsafe['people_id'] AS $key=>$person)
		{
			$id=$wpdb->get_var('SELECT attendance_id FROM '.CA_IND_TBL.' WHERE `date`="'.$sqlsafe['date'].'" AND people_id="'.$person.'" AND meeting_type="'.esc_sql($meeting[0]).'" AND meeting_id="'.esc_sql($meeting[1]).'"');
			if(!$id)
			{
				$wpdb->query('INSERT INTO '.CA_IND_TBL.' (`date`,people_id,meeting_type,meeting_id)VALUES("'.$sqlsafe['date'].'","'.$person.'","'.esc_sql($meeting[0]).'","'.esc_sql($meeting[1]).'")');
				$sql='SELECT people_type_id FROM '.CA_PEO_TBL.' WHERE people_id="'.$person.'"';
				$person_type=$wpdb->get_var($sql);
				
				switch($person_type)
				{
					case 1:$adult++;break;
					case 2:$child++;break;
					case 3:$child++;break;
				}
			}
			
		}
		if(!empty($_POST['visitors']))
		{
			$names=explode(', ',$_POST['visitors']);
			foreach($names AS $name)
			{
				$people_id=church_admin_get_user_id($name);
				if(empty($people_id))
				{//create household
					$wpdb->query('INSERT INTO '.CA_HOU_TBL.'(address)("")');
					$household_id=$wpdb->insert_id();
				
				}
			}
		
		}
		$sql='INSERT INTO '.CA_ATT_TBL .' (`date`,adults,children,service_id)VALUES("'.$sqlsafe['date'].'","'.$adult.'","'.$child.'","'.$meeting[1].'")';
		
		if($meeting[0]=='service')$wpdb->query($sql);
		echo'<div class="updated fade"><p>'.__('Attendance Updated','church-admin').'</p></div>';
	}//end process form
	//form
		
		$option='';
		
		$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
		if(!empty($services))
		{
		
			foreach($services AS $service)
			{

				$option.='<option value="service-'.$service->service_id.'">'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.'</option>';
			}
		}
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		if(!empty($smallgroups))
		{
			foreach($smallgroups AS $smallgroup)$option.='<option class="smallgroup" value="smallgroup-'.$smallgroup->id.'">Small Group - '.$smallgroup->group_name.'</option>';
		}
		if(!empty($option))
		{
			echo'<form action="" method="POST">';
			echo'<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'#date\').datepicker({dateFormat : "yy-mm-dd", changeYear: true });});</script>';
	
		 echo'<p><label>'.__('Date','church-admin').':</label><input type="text" id="date" name="date" /</p>';
			echo'<p><label>'.__('Which Meeting','church-admin').'</label><select name="meeting">';
			echo $option.'</select></p>';
		}
		$people=$wpdb->get_results('SELECT CONCAT_WS(" ", first_name,prefix,last_name) AS name, people_id FROM '.CA_PEO_TBL.' ORDER BY last_name,first_name');
		if(!empty($people))
		{
			foreach($people AS $person)
			{
				echo'<p><label>'.$person->name.'</label><input type="checkbox" name="people_id[]" value="'.$person->people_id.'"/></p>';
			}
		}
		echo'<p><label>'.__('Comma Separated Visitors','church-admin').'</label><input type="text" name="visitors"/></p>';
		echo'<p><input type="hidden" name="save_attendance" value="yes"/><input type="submit" value="'.__('Save','church-admin').'"/></p></form>';
	

	echo'</div>';
}
?>