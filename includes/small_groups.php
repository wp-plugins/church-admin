<?php

function church_admin_small_groups()
{
//function to output small group list	
global $wpdb;
$out='<div class="wrap church_admin"><h2>Small Groups</h2><p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_small_group",'edit_small_group').'">Add a small group</a></p>
<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></tfoot><tbody>';
//grab small group information
$sg_sql = 'SELECT * FROM '.CA_SMG_TBL.' ORDER BY id';
$sg_results = $wpdb->get_results($sg_sql);
foreach ($sg_results as $sg_row) 
    {
	//build leader array
	$leaders=unserialize($sg_row->leader);
	$ldr=array();
	if(!empty($leaders))
	{
	    foreach($leaders AS $key=>$value)
	    {
	        $leader_sql='SELECT CONCAT_WS(" ",first_name,last_name)  FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($value).'"';
	        $ldr[] = $wpdb->get_var($leader_sql);
	    }
	}
	if(empty($ldr))$ldr=array(1=>'No leaders assigned yet');
	$edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_small_group&amp;id='.$sg_row->id;
	$delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_small_group&amp;id='.$sg_row->id;
        
        if($sg_row->id!=1)
	{
	    $out.='<tr><td><a href="'.wp_nonce_url($edit_url, 'edit_small_group').'">[Edit]</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete_small_group').'">[Delete]</a></td><td>'.esc_html(stripslashes($sg_row->group_name)).'</td><td>'.implode(", ",$ldr).'</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
       
	}
        else
	{
	   $out.='<tr><td>&nbsp;</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->group_name)).'</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
       
	}
    } 
$out.="</tbody></table></div>";
echo $out;	
}
//end of small group information function

function church_admin_delete_small_group($id)
{
    global $wpdb;
    
	$sql='DELETE FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($_GET['id']).'"';
	$wpdb->query($sql);
	 church_admin_small_groups();
       
}




function church_admin_edit_small_group($id)
{
    global $wpdb;
    $wpdb->show_errors();
    if(isset($_POST['edit_small_group'])&&ctype_digit($_POST['leader1'])&&ctype_digit($_POST['leader2']))
    {
	$form=array();
	foreach($_POST AS $key=>$value)$form[$key]=stripslashes($value);
	if(!empty($_POST['leader1'])){$leaders=esc_sql(serialize(array(1=>$_POST['leader1'],2=>$_POST['leader2'])));}else{$leaders=esc_sql(serialize(array(1=>'',2=>'')));}
	if(!$id)$id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leader="'.$leaders.'" AND whenwhere="'.esc_sql($form['whenwhere']).'" AND group_name="'.esc_sql($form['group_name']).'"');
	if($id)
	{//update
	    $wpdb->query('UPDATE '.CA_SMG_TBL.' SET leader="'.$leaders.'",group_name="'.esc_sql($form['group_name']).'",whenwhere="'.esc_sql($form['whenwhere']).'" WHERE id="'.esc_sql($id).'"');
   
	}//end update
	else
	{//insert
	    $wpdb->query('INSERT INTO  '.CA_SMG_TBL.' (group_name,leader,whenwhere) VALUES("'.esc_sql($form['group_name']).'","'.$leaders.'","'.esc_sql($form['whenwhere']).'")');
	}//insert
	
	echo'<div class="wrap church_admin"><div id="message" class="updated fade"><p><strong>Small Group Edited</strong></p></div>';
	church_admin_small_groups();
    }
    else
    {
	$data=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($id).'"');
	$leaders=$wpdb->get_results('SELECT a.people_id, CONCAT_WS(" ", b.first_name,b.last_name) AS leader  FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.department_id=1 AND a.people_id=b.people_id');
	
	    echo'<div class="wrap church_admin"><h2>Add/Edit Small Group</h2><form action="" method="post">';
	    echo'<p><label>Small group name</label><input type="text" name="group_name"';
	    if(!empty($data->group_name)) echo ' value="'.$data->group_name.'" ';
	    echo'/></p>';
	    echo'<p><label>Where &amp; When</label><input type="text" name="whenwhere"';
	    if(!empty($data->whenwhere)) echo ' value="'.$data->whenwhere.'" ';
	    echo'/></p>';
	    if($leaders)
	    {//leaders available
		$curr_leaders=unserialize($data->leader);
		echo'<p><label>Leader</label>';
		echo'<select name="leader1">';
		foreach($leaders AS $leader)
		{
		    echo'<option value="'.$leader->people_id.'" ';
		    selected($curr_leaders[1],$leader->people_id);
		    echo' >'.$leader->leader.'</option>';
		}
		echo'</select></p>';
		echo'<p><label>Leader</label>';
		echo'<select name="leader2">';
		foreach($leaders AS $leader)
		{
		    echo'<option value="'.$leader->people_id.'" ';
		    selected($curr_leaders[2],$leader->people_id);
		    echo' >'.$leader->leader.'</option>';
		}
		echo'</select></p>';
	    }//leaders available
	    echo'<p class="submit"><input type="submit" name="edit_small_group" value="Edit Small Group &raquo;" /></p></form></div>';
	
	
    }
}









?>
