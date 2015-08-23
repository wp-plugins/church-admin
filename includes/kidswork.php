<?php

function church_admin_kidswork()
{
	global $wpdb;
	
	$out='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_kidswork','edit_kidswork').'">Add a kidswork age group</a></p>';
	$out.='<p>'.__('The dates will go up a year on January 1st automatically.','church-admin');
	$out.='<p><a class="button-secondary" target="_blank" href="'.site_url().'?download=kidswork_pdf">'.__('Kidswork PDF','church-admin').'</a></p>';
	//autocorrect
	if(date('z')==0){$wpdb->query('UPDATE '.CA_KID_TBL.' SET youngest = youngest + INTERVAL 1 YEAR, oldest = oldest + INTERVAL 1 YEAR');}
	//get groups
	$results=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL.' ORDER BY youngest DESC');
	if(!empty($results))
	{
		
		$out.='<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Group Name','church-admin').'</th><th>'.__('Youngest','church-admin').'</th><th>'.__('Oldest','church-admin').'</th></tr></thead><tbody>';
		foreach($results AS $row)
		{
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_kidswork&id='.intval($row->id),'edit_kidswork').'">'.__('Edit','church-admin').'</a>';
			$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=delete_kidswork&id='.intval($row->id),'delete_kidswork').'">'.__('Delete','church-admin').'</a>';
			$out.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($row->group_name).'</td><td>'.mysql2date(get_option('date_format'),$row->youngest).'</td><td>'.mysql2date(get_option('date_format'),$row->oldest).'</td></tr>';
		}
		$out.='</table>';
	}
	echo $out;
}
function church_admin_delete_kidswork($id)
{
	global $wpdb;
	$wpdb->query('DELETE FROM '.CA_KID_TBL.' WHERE id="'.esc_sql($id).'"');
	echo'<div class="updated fade"><p><strong>'.__('Kidswork group deleted','church-admin').'</strong></p></div>';
		church_admin_kidswork();
}
function church_admin_edit_kidswork($id=NULL)
{

	global $wpdb;
	
	if(!empty($_POST['save']))
	{
		$sqlsafe=array();
		foreach($_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
		if(empty($id))$id=$wpdb->get_var('SELECT id FROM '.CA_KID_TBL.' WHERE group_name="'.$sqlsafe['group_name'].'" AND youngest="'.$sqlsafe['youngest'].'" AND oldest="'.$sqlsafe['oldest'].'" AND department_id="'.$sqlsafe['department_id'].'"');
		if(!empty($id))
		{//update
			$wpdb->query('UPDATE '.CA_KID_TBL.' SET group_name="'.$sqlsafe['group_name'].'" , youngest="'.$sqlsafe['youngest'].'" , oldest="'.$sqlsafe['oldest'].'" , department_id="'.$sqlsafe['department_id'].'" WHERE id="'.esc_sql($id).'"');
		}
		else
		{//insert
			$wpdb->query('INSERT INTO '.CA_KID_TBL.' (group_name,youngest,oldest,department_id)VALUES("'.$sqlsafe['group_name'].'","'.$sqlsafe['youngest'].'","'.$sqlsafe['oldest'].'","'.$sqlsafe['department_id'].'" )');
		}
		echo'<div class="updated fade"><p><strong>'.__('Kidswork updated','church-admin').'</strong></p></div>';
		church_admin_kidswork();
	
	}
	else
	{
		if(!empty($id))$data=$wpdb->get_row('SELECT * FROM '.CA_KID_TBL.' WHERE id="'.esc_sql($id).'"');
		echo'<h2>'.__('Add a kids work group','church-admin').'<form action="" method="POST">';
		echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Group Name','church-admin').'</th><td><input type="text" name="group_name" id="group_name" ';
		if(!empty($data->group_name)) echo'value="'.esc_html($data->group_name).'"';
		echo'/></td></tr>';
		echo'<tr><th scope="row">'.__('Youngest','church-admin').'</th><td><input type=="text" name="youngest" class="youngest" ';
		if(!empty($data->youngest)&&$data->youngest!='0000-00-00') echo ' value="'.esc_html($data->youngest).'" ';
		echo'/></td></tr>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.youngest\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
		echo'<tr><th scope="row">'.__('Oldest','church-admin').'</th><td><input type=="text" name="oldest" class="oldest" ';
		if(!empty($data->oldest)&&$data->oldest!='0000-00-00') echo ' value="'.esc_html($data->oldest).'" ';
		echo'/></p>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.oldest\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
		echo'<tr><th scope="row">'.__('Led by people from ','church-admin').'</th><td>';
		$departments=get_option('church_admin_departments');
		if(!empty($departments))
		{
			echo'<select name="department_id">';
			$first=$option='';
			foreach($departments AS $id=>$name) 
			{
				if(!empty($data->department_id) && $data->department_id=$id) $first='<option selected="selected" value="'.intval($id).'">'.esc_html($name).'</option>';
				$option.='<option value="'.intval($id).'">'.esc_html($name).'</option>';
			}
			echo $first.$option;
			echo'</select>';
		}
		echo'</td></tr>';
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save" value="yes"/><input type="submit" value="Save"/></td></tr></tbody></table></form>';
		
	}
}
?>