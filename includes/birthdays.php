<?php
function church_admin_frontend_birthdays($member_type_id=1, $deltadays=30)
{
	global $wpdb;
	$wpdb->show_errors();
	$out='';
	
	$memb=explode(',',$member_type_id);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	
	$sql='SELECT first_name, last_name, prefix, date_of_birth, 
		FLOOR((UNIX_TIMESTAMP(CONCAT(((RIGHT(date_of_birth, 5) < RIGHT(CURRENT_DATE, 5)) 
		+ YEAR(CURRENT_DATE)), RIGHT(date_of_birth, 6))) - UNIX_TIMESTAMP(CURRENT_DATE)) / 86400) 
		AS upcoming_days FROM '.CA_PEO_TBL.$memb_sql.
		' HAVING (upcoming_days >=0 AND upcoming_days <= '.$deltadays.') order by upcoming_days';
		
		$people_results=$wpdb->get_results($sql);
	
	if(!empty($people_results))
	{
		$out .= '<p><strong>Birthdays within the next '.$deltadays.' days:</strong></p>';
		$out .= '<table cellspacing="0" cellpadding=0 width="100%">';
		$out.='<thead><tr><td><strong>'.__('Name','church-admin').'</strong></td><td><strong>'.__('Date','church-admin').'</strong></td></tr></thead><tbody>';
		foreach($people_results AS $people) 
		{
			if(!empty($people->prefix)){ $prefix=$people->prefix.' '; } else {	$prefix='';	}
			$name=$people->first_name.' '.$prefix.$people->last_name;
			$birthday = mysql2date("d M",$people->date_of_birth);
			$out.='<tr><td>'.esc_html($name).'</td><td>'.esc_html($birthday).'</td></tr>';
		}
		$out.='</tbody></table>';
	}
	
	return $out;
}
function church_admin_birthday_widget_control()
{
    //get saved options
    $options=get_option('church_admin_birthday_widget');
    //handle user input
    if(!empty($_POST['widget_submit']))
    {
	
        
		$options['title']=strip_tags(stripslashes($_POST['title']));
        
        if(ctype_digit($_POST['days'])){$options['days']=$_POST['days'];}else{$options['days']='14';}
        $memb=array();
		foreach($_POST['member_type_id'] AS $key=>$value)$memb[]=$value;
		$options['member_type_id']=implode(',',$memb);
        update_option('church_admin_birthday_widget',$options);
    }
    church_admin_birthday_widget_control_form();
}

function church_admin_birthday_widget_control_form()
{
    global $wpdb,$member_type;
    $wpdb->show_errors;
    
    $option=get_option('church_admin_birthday_widget');
    echo '<p><label for="title">'.__('Title','church-admin').':</label><input type="text" name="title" value="'.$option['title'].'" /></p>';
    echo '<p><label for="member_type_id">'.__('Which Member Types?','church-admin').':</label></p>';
	if(!empty($option['member_type_id']))$stored=explode(',',$option['member_type_id']);
	foreach($member_type AS $key=>$value )
	{
		echo'<p>'.$value.' <input type="checkbox" name="member_type_id[]" value="'.$key.'" ';
		if(!empty($stored)&& in_array($key,$stored)) echo' checked="checked" ';
		echo'/></p>';
	}
    echo'</p>';
    echo '<p><label for="days">'.__('How many days to show','church-admin').'?</label><select name="days">';
    if(isset($option['days'])) echo '<option value="'.$option['days'].'">'.$option['days'].'</option>';
    for($x=1;$x<=365;$x++){echo '<option value="'.$x.'">'.$x.'</option>';}
    echo'</select><input type="hidden" name="widget_submit" value="1"/>';
}

?>