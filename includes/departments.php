<?php
function church_admin_department_list()
{
    global $departments;
    echo'<h2>Departments</h2><p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_department','edit_department').'">'.__('Add a department','church_admin').'</a></p>';
    if(!empty($departments))
    {
        echo'<table class="widefat"><thead><tr><th>'.__('Edit','church_admin').'</th><th>'.__('Delete','church_admin').'</th><th>'.__('Department Name','church_admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church_admin').'</th><th>'.__('Delete','church_admin').'</th><th>'.__('Department Name','church_admin').'</th></tr></tr></tfoot><tbody>';
        foreach($departments AS $id=>$department)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_department&amp;department_id='.$id,'edit_department').'">'.__('Edit','church-admin').'</a>';
            if($id!=1){$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_department&amp;department_id='.$id,'delete_department').'">'.__('Delete','church-admin').'</a>';}else{$delete=__("Can't be deleted",'church-admin');}
            echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$department.'</td></tr>';
        }
        echo'</tbody></table>';
    }

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
        $dep_name=stripslashes($_POST['department_name']);
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
        echo'<p><label>'.__('Ministry Name','church-admin').'</label><input type="text" name="department_name" ';
        if($id) echo ' value="'.$departments[$id].'" ';
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_department" value="yes"/><input type="submit" value="'.__('Save Ministry','church-admin').'&raquo;" /></p></form></div>';
        
    }//end form
}
?>