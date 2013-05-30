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
	$leaders=maybe_unserialize($sg_row->leader);
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
    if(isset($_POST['edit_small_group']))
    {
	$form=array();
	foreach($_POST AS $key=>$value)$form[$key]=stripslashes($value);
	$ldr=array();
	if(!empty($_POST['leader1'])&&ctype_digit($_POST['leader1'])){$ldr['1']=$_POST['leader1'];}else{$ldr['1']='';}
	if(!empty($_POST['leader2'])&&ctype_digit($_POST['leader2'])){$ldr['2']=$_POST['leader2'];}else{$ldr['2']='';}
	$leaders=esc_sql(maybe_serialize($ldr));
	if(!$id)$id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leader="'.$leaders.'" AND whenwhere="'.esc_sql($form['whenwhere']).'" AND group_name="'.esc_sql($form['group_name']).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'" AND address="'.esc_sql($form['address']).'"');
	if($id)
	{//update
	    $wpdb->query('UPDATE '.CA_SMG_TBL.' SET lat="'.esc_sql($form['lat']).'",lng="'.esc_sql($form['lng']).'",address="'.esc_sql($form['address']).'", leader="'.$leaders.'",group_name="'.esc_sql($form['group_name']).'",whenwhere="'.esc_sql($form['whenwhere']).'" WHERE id="'.esc_sql($id).'"');
   
	}//end update
	else
	{//insert
	    $wpdb->query('INSERT INTO  '.CA_SMG_TBL.' (group_name,leader,whenwhere,address,lat,lng) VALUES("'.esc_sql($form['group_name']).'","'.$leaders.'","'.esc_sql($form['whenwhere']).'","'.esc_sql($form['address']).'","'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'")');
	}//insert
	
	echo'<div class="wrap church_admin"><div id="message" class="updated fade"><p><strong>'.__('Small Group Edited','church-admin').'</strong></p></div>';
	church_admin_small_groups();
    }
    else
    {
	$data=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($id).'"');
	$leaders=$wpdb->get_results('SELECT a.people_id, CONCAT_WS(" ", b.first_name,b.last_name) AS leader  FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.department_id=1 AND a.people_id=b.people_id');
	
	    echo'<div class="wrap church_admin"><h2>'.__('Add/Edit Small Group','church-admin').'</h2><form action="" method="post">';
	    echo'<p><label>'.__('Small group name','church-admin').'</label><input type="text" name="group_name"';
	    if(!empty($data->group_name)) echo ' value="'.$data->group_name.'" ';
	    echo'/></p>';
	    echo'<p><label>'.__('When','church-admin').'</label><input type="text" name="whenwhere"';
	    if(!empty($data->whenwhere)) echo ' value="'.$data->whenwhere.'" ';
	    echo'/></p>';
	    echo'<script type="text/javascript"> var beginLat =';
		if(empty($data->lat))  {$data->lat='51.50351129583287';}
		echo $data->lat;
		echo '; var beginLng =';
		if(empty($data->lng)) {$data->lng='-0.148193359375';}
		echo $data->lng;
		echo';</script>';
	
	    echo'<p><label>'.__('Address','church-admin').'</label><input type="text" id="address" name="address"';
	    if(!empty($data->address)) echo ' value="'.$data->address.'" ';
	    echo'/></p>';
		echo '<p><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a><br/><span id="finalise" >Once you have updated your address, this map will show roughly where your address is.</span><input type="hidden" name="lat" id="lat" value="'.$data->lat.'"/><input type="hidden" name="lng" id="lng" value="'.$data->lng.'"/></p><div id="map" style="width:500px;height:300px"></div>';
		
  
	    if($leaders)
	    {//leaders available
		$curr_leaders=unserialize($data->leader);
		echo'<p><label>'.__('Leader','church-admin').'</label>';
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
		echo'<option value="">'.__('Second Leader','church-admin').'</option>';
		foreach($leaders AS $leader)
		{
		    echo'<option value="'.$leader->people_id.'" ';
		    selected($curr_leaders[2],$leader->people_id);
		    echo' >'.$leader->leader.'</option>';
		}
		echo'</select></p>';
	    }//leaders available
	    echo'<p class="submit"><input type="submit" name="edit_small_group" value="'.__('Save Small Group','church-admin').' &raquo;" /></p></form></div>';
	
	
    }
}









?>
