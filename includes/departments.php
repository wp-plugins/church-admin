<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_department_list()
{
    $departments=get_option('church_admin_departments');
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_department&tab=ministries','edit_department').'">'.__('Add a ministry','church-admin').'</a> <a class="button-secondary" href="'.wp_nonce_url(site_url().'/?download=ministries_pdf','ministries_pdf').'">'.__('Ministries PDF','church-admin').'</a></p>';
    if(!empty($departments))
    {
        echo'<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Ministry (Click to view people)','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Ministry','church-admin').'</th></tr></tr></tfoot><tbody>';
        foreach($departments AS $id=>$department)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_department&amp;department_id='.$id,'edit_department').'">'.__('Edit','church-admin').'</a>';
            if($id!=1){$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_department&amp;department_id='.$id,'delete_department').'">'.__('Delete','church-admin').'</a>';}else{$delete=__("Can't be deleted",'church-admin');}
            $view='<a title="'.__('View people doing that ministry','church-admin').'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_view_department&amp;department_id='.$id,'view_department').'">'.esc_html($department).'</a>';
            
			echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$view.'</td></tr>';
        }
        echo'</tbody></table>';
    }

}

function church_admin_view_department($id)
{
		echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_department_list",'department_list').'">'.__('Ministry List','church-admin').'</a></p>';
		global $wpdb;$departments;
		$departments=get_option('church_admin_departments');
		$sql='SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.department_id="'.esc_sql($id).'" AND b.meta_type="ministry" ORDER BY a.last_name ASC';
		
		$results=$wpdb->get_results($sql);
		if(!empty($_POST))
		{
			$peoples_id=maybe_unserialize(church_admin_get_people_id($_POST['people']));
			if(!empty($peoples_id)) 
				{
					foreach($peoples_id AS $key=>$people_id)
					{
						$check=$wpdb->get_var('SELECT people_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND department_id="'.esc_sql($id).'" AND meta_type="ministry"');
						$sql='INSERT INTO '.CA_MET_TBL.' (people_id,department_id,meta_type)VALUES("'.esc_sql($people_id).'","'.esc_sql($id).'","ministry")';
						if(empty($check))$wpdb->query($sql);
					}
				}
		}
		if(!empty($results))
		{
			if(!empty($_POST['view_departments']))
			{
				
				foreach($results AS $row)
				{
					if(!empty($_POST[$row->people_id]))
					{
						$sql='DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($row->people_id).'" AND department_id="'.esc_sql($id).'" AND meta_type="ministry"';
						
						$wpdb->query($sql);
					}
					
				}
				
				
			
			}		
		}	
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ",a.first_name,a.last_name) AS name, a.people_id FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE a.people_id=b.people_id AND b.department_id="'.esc_sql($id).'" AND b.meta_type="ministry" ORDER BY a.last_name,a.first_name ASC');
		
			echo '<h2>'.sprintf(__('Viewing who is in "%1s" ministry','church-admin'),esc_html($departments[$id])).'</h2><form action="" method="POST">';
			if(!empty($results))
			{//department contains people
				echo'<table class="widefat striped" ><thead><tr><th>'.__('Remove','church-admin').'</th><th>'.__('Person','church-admin').'</th></tr></thead><tbody>';
				foreach($results AS $row)
				{
					$delete='<input type="checkbox" name="'.esc_html($row->people_id).'" value="x"/>';
					echo'<tr><td>'.$delete.'</td><td>'.esc_html($row->name).'</td></tr>';
				}
				echo'</table>';
			}//department contains people
			echo'<p>Add people:'.church_admin_autocomplete('people','friends','to',NULL).'</p>';
			echo'<p><input type="hidden" name="view_departments" value="yes"/><input type="submit" value="'.__('Update','church-admin').'"/></p></form>';
		
		

}
function church_admin_delete_department($id)
{
    global $departments,$wpdb;
    $wpdb->show_errors();
    //delete department from db
    unset($departments[$id]);
    
    update_option('church_admin_departments',$departments);
    //delete department from people
    $result=$wpdb->get_results('SELECT departments,people_id FROM '.CA_PEO_TBL.' WHERE departments!="NULL"');
    foreach($result AS $row)
    {
        $dep=unserialize($row->departments);
        $id=array_search($id,$dep);
        if($id)unset($dep[$id]);
        $sql='UPDATE '.CA_PEO_TBL.' SET departments="'.esc_sql(serialize($dep)).'" WHERE people_id="'.esc_sql($row->people_id).'"';
        //echo $sql.'<br/>';
        $wpdb->query($sql);
    }
    echo'<div class="updated fade"><p>'.__('Ministries Deleted','church-admin').'</p></div>';
    church_admin_department_list();
}
function church_admin_edit_department($id)
{
    global $departments;
    if(isset($_POST['edit_department']))
    {//process
        $dep_name=sanitize_text_field(stripslashes($_POST['department_name']));
        if($id)
        {//update current department name
            $departments[$id]=$dep_name;
            update_option('church_admin_departments',$departments);
            echo '<div class="updated fade"><p>'.__('Ministries Updated','church-admin').'</p></div>';
        }        
        elseif(!in_array($dep_name,$departments))
        {//add new one if unique
            $departments[]=$dep_name;
            
            update_option('church_admin_departments',$departments);
            echo '<div class="updated fade"><p>'.__('Ministries Updated','church-admin').'</p></div>';
        }
        else
        {//not unique or update, so ignore!
           echo '<div class="updated fade"><p>'.__('Ministries Unchanged','church-admin').'</p></div>'; 
        }
        church_admin_department_list();
        
    }//end process
    else
    {//form
        echo'<h2>';
        if($id){echo __('Update','church-admin').' ';}else {echo __('Add','church-admin').' ';}
        echo __('Ministry','church-admin').'</h2>';
        echo'<form action="" method="post">';
        echo'<p><label>'.__('Ministry Name','church-admin').'</label><input type="text" name="department_name" ';
        if($id) echo ' value="'.esc_html($departments[$id]).'" ';
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_department" value="yes"/><input type="submit" value="'.__('Save Ministry','church-admin').'&raquo;" /></p></form></div>';
        
    }//end form
}
?>