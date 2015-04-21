<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_whosin($id)
{
	global $wpdb;
	$attendance=array('1'=>'Regular','2'=>'Irregular','3'=>'Connected');
	
	$out='';
	$group=$wpdb->get_row('SELECT * FROM  '.CA_SMG_TBL.' WHERE id="'.esc_sql(intval($id)).'"');
	if(!empty($group))
	{
		//group details
		$out.=sprintf( '<h2>%1$s %2$s %3$s</h2>', __( 'Who is in', 'church_admin' ),esc_html($group->group_name),__('group','church-admin') );
		$out.='';
		$out.='<table class="form-table"><tbody>';
		$out.='<tr><th scope="row">'.__('Leader(s)','church-admin').':</th><td>'.esc_html(church_admin_get_people($group->leader)).'</td><td rowspan=3><img class="alignleft" src="http://maps.google.com/maps/api/staticmap?center='.esc_html($group->lat).','.esc_html($group->lng).'&zoom=13&markers='.esc_html($group->lat).','.esc_html($group->lng).'&size=200x200&sensor=FALSE"/></td></tr>';
		$out.='<tr><th scope="row">'.__('Meeting','church-admin').':</th><td>'.esc_html($group->whenwhere).'</td></tr>';
		$out.='<tr><th scope="row">'.__('Venue','church-admin').':</th><td>'.esc_html($group->address).'</td></tr>';
		$out.='</tbody></table>';
		//grab group members
		$sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,email,mobile,smallgroup_attendance,smallgroup_id FROM '.CA_PEO_TBL.' ORDER BY smallgroup_attendance,last_name ';
		$peopleresults = $wpdb->get_results($sql);
		if(!empty($peopleresults))
		{
				$out.='<table class="widefat">';
				$out.='<thead><tr><th>'.__('Name','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Name','church-admin').'</th><th>'.__('Attendance','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></tfoot><tbody>';
				foreach($peopleresults AS $row)
				{
					//smallgroup id now an serialized array, so need to cycle through every address result for it...
					$sg=maybe_unserialize($row->smallgroup_id);
					
					if(is_array($sg)&&in_array($id,$sg))
					{	
						$out.='<tr><td>'.esc_html($row->name).'</td><td>'.$attendance[$row->smallgroup_attendance].'</td><td><a href="mailto:'.esc_html($row->email).'">'.esc_html($row->email).'</a></td><td><a href="call:'.$row->email.'">'.esc_html($row->mobile).'</td></tr>';
					}
				}
				$out.='</tbody></table>';
		}
	}
	echo $out;
}
function church_admin_small_groups()
{
	//function to output small group list	
	global $wpdb;
	$out='<h2>'.__('Small Groups','church-admin').'</h2>';
	if(!empty($_GET['message']))$out.='<div class="updated"><p>'.esc_html(urldecode($_GET['message'])).'</p></div>';
	$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=small_groups&amp;action=edit_small_group",'edit_small_group').'">'.__('Add a small group','church-admin').'</a></p>';
	//map of small groups
	$row=$wpdb->get_row('SELECT AVG(lat) AS lat,AVG(lng) AS lng FROM '.CA_SER_TBL);
	if(!empty($row))
	{
		$out.='<script type="text/javascript">var xml_url="'.site_url().'/?download=small-group-xml&small-group-xml='.wp_create_nonce('small-group-xml').'";';
		$out.=' var lat='.esc_html($row->lat).';';
		$out.=' var lng='.esc_html($row->lng).';';
		$out.='jQuery(document).ready(function(){sgload(lat,lng,xml_url);});</script><div id="admin_map"></div><div id="groups" ></div><div class="clear"></div>';
	}
	//list
	
	//table of groups
		$out.='<table  id="sortable" class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></tfoot><tbody class="content">';
		//grab small group information
		$sg_sql = 'SELECT * FROM '.CA_SMG_TBL.' ORDER BY smallgroup_order';
		$sg_results = $wpdb->get_results($sg_sql);
		foreach ($sg_results as $sg_row) 
		{
			//build leader array
			$leaders=maybe_unserialize($sg_row->leader);
			$ldr=array();
			if(!empty($leaders) &&is_array($leaders))
			{
				foreach($leaders AS $key=>$value)
				{
					$leader_sql='SELECT CONCAT_WS(" ",first_name,last_name)  FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($value).'"';
					$ldr[] = $wpdb->get_var($leader_sql);
				}
			}
			if(empty($ldr[0])&&empty($ldr[2]))$ldr=array(1=>'No leaders assigned yet');
			$edit_url='admin.php?page=church_admin/index.php&action=edit_small_group&tab=small_groups&amp;id='.$sg_row->id;
			$delete_url='admin.php?page=church_admin/index.php&action=delete_small_group&tab=small_groups&amp;id='.$sg_row->id;
        
			if($sg_row->id!=1)
			{
				$out.='<tr class="sortable-row" id="'.$sg_row->id.'"><td><a href="'.wp_nonce_url($edit_url, 'edit_small_group').'">[Edit]</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete_small_group').'">[Delete]</a></td><td><a title="'.__('Who is in this group?','church-admin').'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=whosin&amp;id='.intval($sg_row->id),'whosin').'">'.esc_html(stripslashes($sg_row->group_name)).'</a></td><td>'.esc_html(implode(", ",$ldr)).'</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
			}
			else
			{
				$out.='<tr class="sortable-row" id="'.intval($sg_row->id).'"><td>&nbsp;</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->group_name)).'</td><td>&nbsp;</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
       
			}
		} 
		$out.="</tbody></table>";
	$out.= '
    <script type="text/javascript">
  
 jQuery(document).ready(function($) {
 
    var fixHelper = function(e,ui){
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };
    var sortable = $("#sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order
        
       
				var Order = "order="+$(this).sortable(\'toArray\').toString();

        console.log(Order);
        
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=small_groups",
            type: "post",
            data:  Order,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {
                console.log("Saved.");
            }
        });}
});
$("#sortable tbody.content").disableSelection();
});

   
   
    </script>
';
	echo $out;	
}
//end of small group information function

function church_admin_delete_small_group($id)
{
    global $wpdb;
    
	$sql='DELETE FROM '.CA_SMG_TBL.' WHERE id="'.esc_sql($_GET['id']).'"';
	$wpdb->query($sql);
	echo'<div class="wrap church_admin"><div id="message" class="updated fade"><p><strong>'.__('Small Group Deleted','church-admin').'</strong></p></div>';
	echo church_admin_small_groups();
       
}




function church_admin_edit_small_group($id)
{
    global $wpdb;
    $wpdb->show_errors();
    if(isset($_POST['edit_small_group']))
    {
	$form=array();
	foreach($_POST AS $key=>$value)$form[$key]=santitize_text_field(stripslashes($value));
	$ldr=array();
	if(!empty($_POST['leader1'])&&ctype_digit($_POST['leader1'])){$ldr['1']=$_POST['leader1'];}else{$ldr['1']='';}
	if(!empty($_POST['leader2'])&&ctype_digit($_POST['leader2'])){$ldr['2']=$_POST['leader2'];}else{$ldr['2']='';}
	$leaders=esc_sql(maybe_serialize($ldr));
	if(!$id)$id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leader="'.$leaders.'" AND whenwhere="'.esc_sql($form['whenwhere']).'" AND group_name="'.esc_sql($form['group_name']).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'" AND address="'.esc_sql($form['address']).'"');
	if($id)
	{//update
	    $wpdb->query('UPDATE '.CA_SMG_TBL.' SET lat="'.esc_sql($form['lat']).'",lng="'.esc_sql($form['lng']).'",address="'.esc_sql($form['address']).'", leader="'.$leaders.'",group_name="'.esc_sql($form['group_name']).'",whenwhere="'.esc_sql($form['whenwhere']).'" WHERE id="'.esc_sql(intval($id)).'"');
   
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
	if(empty($data))$data=new stdClass();
	$leaders=$wpdb->get_results('SELECT a.people_id, CONCAT_WS(" ", b.first_name,b.last_name) AS leader  FROM '.CA_MET_TBL.' a, '.CA_PEO_TBL.' b WHERE a.department_id=1 AND a.people_id=b.people_id AND a.meta_type="ministry"');
	
	    echo'<h2>'.__('Add/Edit Small Group','church-admin').'</h2><form action="" method="post">';
	    echo'<p><label>'.__('Small group name','church-admin').'</label><input type="text" name="group_name"';
	    if(!empty($data->group_name)) echo ' value="'.esc_html($data->group_name).'" ';
	    echo'/></p>';
	    echo'<p><label>'.__('When','church-admin').'</label><input type="text" name="whenwhere"';
	    if(!empty($data->whenwhere)) echo ' value="'.esc_html($data->whenwhere).'" ';
	    echo'/></p>';
	    
		echo'<script type="text/javascript"> var beginLat =';
		if(!empty($data->lat)) {echo esc_html($data->lat);}else {$data->lat='51.50351129583287';echo '51.50351129583287';}
		echo '; var beginLng =';
		if(!empty($data->lng)) {echo esc_html($data->lng);}else {$data->lng='-0.148193359375';echo'-0.148193359375';}
		echo';</script>';
	
	    echo'<p><label>'.__('Address','church-admin').'</label><input type="text" id="address" name="address"';
	    if(!empty($data->address)) echo ' value="'.esc_html($data->address).'" ';
	    echo'/></p>';
		echo '<p><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a><br/><span id="finalise" >Once you have updated your address, this map will show roughly where your address is.</span><input type="hidden" name="lat" id="lat" value="'.esc_html($data->lat).'"/><input type="hidden" name="lng" id="lng" value="'.esc_html($data->lng).'"/></p><div id="map" style="width:500px;height:300px"></div><br/ style="clear:left">';
		
  
	    if($leaders)
	    {//leaders available
		if(!empty($data->leader))$curr_leaders=unserialize($data->leader);
		echo'<p><label>'.__('Leader','church-admin').'</label>';
		echo'<select name="leader1">';
		foreach($leaders AS $leader)
		{
		    echo'<option value="'.intval($leader->people_id).'" ';
		    if(!empty($curr_leaders)) selected($curr_leaders[1],$leader->people_id);
		    echo' >'.($leader->leader).'</option>';
		}
		echo'</select></p>';
		echo'<p><label>Leader</label>';
		echo'<select name="leader2">';
		echo'<option value="">'.__('Second Leader','church-admin').'</option>';
		foreach($leaders AS $leader)
		{
		    echo'<option value="'.intval($leader->people_id).'" ';
		    if(!empty($curr_leaders))selected($curr_leaders[2],$leader->people_id);
		    echo' >'.esc_html($leader->leader).'</option>';
		}
		echo'</select></p>';
	    }//leaders available
	    echo'<p class="submit"><input type="submit" name="edit_small_group" value="'.__('Save Small Group','church-admin').' &raquo;" /></p></form>';
	
	
    }
}


?>