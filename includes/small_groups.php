<?php





function church_admin_small_groups()
{
//function to output small group list	
global $wpdb;
$out='<div class="wrap church_admin"><h2>Small Groups</h2><p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_small_group",'add small group').'">Add a small group</a></p>
<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Group Name</th><th>Leaders</th><th>When</th></tr></tfoot><tbody>';
//grab small group information
$sg_sql = "SELECT * FROM ".$wpdb->prefix."church_admin_smallgroup ";
$sg_results = $wpdb->get_results($sg_sql);
foreach ($sg_results as $sg_row) 
    {
	$leader_sql="SELECT CONCAT_WS(' ',first_name,last_name) AS leader FROM `".$wpdb->prefix."church_admin_directory` WHERE id='".$wpdb->escape($sg_row->leader)."'";
	$leader_row = $wpdb->get_row($leader_sql);
	$edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_small_group&amp;id='.$sg_row->id;
	$delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_small_group&amp;id='.$sg_row->id;
        $out.= '<tr>';
        if($sg_row->id!=1)
	{
	    $out.='<td><a href="'.wp_nonce_url($edit_url, 'edit small group').'">[Edit]</a></td><td><a href="'.wp_nonce_url($delete_url, 'delete small group').'">[Delete]</a></td>';
	}
        else
	{
	   $out.='<td>&nbsp;</td><td>&nbsp;</td>';
	}
        $out.='<td>'.esc_html(stripslashes($sg_row->group_name)).'</td><td>'.esc_html(stripslashes($leader_row->leader)).'</td><td>'.esc_html(stripslashes($sg_row->whenwhere)).'</td></tr>';
        } 
$out.="</tbody></table></div>";
echo $out;	
}
//end of small group information function

function church_admin_delete_small_group($id)
{
    global $wpdb;
    if(check_admin_referer('delete small group') && ctype_digit($id))
    {
	$sql="DELETE FROM ".$wpdb->prefix."church_admin_smallgroup WHERE id='".esc_sql($_GET['id'])."'";
	$wpdb->query($sql);
	 church_admin_small_groups();
    }
    
}

//function to add small group


function church_admin_add_small_group()
{	

global $wpdb;
$wpdb->show_errors();
if(isset($_POST['add_small_group'])&& check_admin_referer('add small group'))
{
  $wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_smallgroup (leader,group_name,whenwhere)VALUES('".esc_sql(stripslashes($_POST['leader']))."','".esc_sql(stripslashes($_POST['group_name']))."','".esc_sql(stripslashes($_POST['whenwhere']))."')") ;
  church_admin_small_groups();
    
}
else
{
    //check there are some members first (else no-one to lead it!)
    $people=$wpdb->get_row("SELECT COUNT(first_name) AS counted FROM ".$wpdb->prefix."church_admin_directory");
    if($people->counted>'0')
    {
        echo'<div class="wrap church_admin"><h2>Add New Small Group</h2><form action="" method="post">';
        if ( function_exists('wp_nonce_field') )wp_nonce_field('add small group');
        echo church_admin_get_smallgroup_form();
        echo'<p class="submit"><input type="submit" name="add_small_group" value="Add Small Group &raquo;" /></p></form></div>';
    }
    else
    {
        echo '<div id="message" class="updated fade"><p><strong>Your need to add some people first - there is no-one in the admin to lead a small group yet.</strong><a href="admin.php?page=church_admin_add_address">Continue &raquo;<a/></p>       </div>';
    }
}
}
//end function to add small group

function church_admin_edit_small_group($id)
{
    global $wpdb;
    $wpdb->show_errors();
    if(isset($_POST['edit_small_group'])&&check_admin_referer('edit small group'))
    {
	
	$wpdb->query("UPDATE ".$wpdb->prefix."church_admin_smallgroup SET leader='".esc_sql(stripslashes($_POST['leader']))."',group_name='".esc_sql(stripslashes($_POST['group_name']))."',whenwhere='".esc_sql(stripslashes($_POST['whenwhere']))."' WHERE id='".esc_sql($id)."'");
    echo'<div class="wrap church_admin"><div id="message" class="updated fade"><p><strong>Small Group Edited</strong></p></div>';
    church_admin_small_groups();
    }
    else
    {
	echo'<div class="wrap church_admin"><h2>Edit Small Group</h2><form action="" method="post">';
        if ( function_exists('wp_nonce_field') )wp_nonce_field('edit small group');
        echo church_admin_get_smallgroup_form();
        echo'<p class="submit"><input type="submit" name="edit_small_group" value="Edit Small Group &raquo;" /></p></form></div>';
    }
}


function church_admin_get_smallgroup_form()
{
global $wpdb;
$wpdb->show_errors();
$out='<ul><li><label >Leader:</label>';
//get people's names
$out.='	<select name="leader">';
//grab current leaders details, if applicable...
if(isset($_GET['id']))
{
    //get leaders id from small group table
    $ld_sql="SELECT * FROM ".$wpdb->prefix."church_admin_smallgroup WHERE id='".$wpdb->escape($_GET['id'])."'";
    $ld_row=$wpdb->get_row($ld_sql);
    //Get leaders name from admin table
    $leader_sql="SELECT CONCAT_WS(' ',first_name,last_name)AS name,id FROM ".$wpdb->prefix."church_admin_directory WHERE id='".$wpdb->escape($ld_row->leader)."'";
    $leader_row=$wpdb->get_row($leader_sql);
 
    //first option is current leader
    $out.='<option value="'.absint($leader_row->id).'">'.esc_html(stripslashes($leader_row->name)).'</option>';
}
//end grab current leaders details 
$peoplesql="SELECT CONCAT_WS(' ',first_name,last_name)AS name,id FROM ".$wpdb->prefix."church_admin_directory ORDER BY last_name";
$peopleresults = $wpdb->get_results($peoplesql);
foreach ($peopleresults as $peoplerow) 
{
    $out.='<option value="'.absint($peoplerow->id).'">'.esc_html(stripslashes($peoplerow->name)).'</option>';
}				
$out.='	</select></li>';
//end get people's names
$out.='<li><label >Group name:</label><input type="text" name="group_name" value="'.esc_html(stripslashes($ld_row->group_name)).'" /></li><li><label >When &amp; Where:</label><input type="text" name="whenwhere" value="'.esc_html(stripslashes($ld_row->whenwhere)).'" />
</li></ul>';
return $out;
}
//end small group form







?>
