<?php


function church_admin_funnel_list()
{
    global $wpdb,$member_type,$people_type;
    echo'<h2>'.__('Follow Up Funnel','church-admin').'</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel','edit_funnel').'">'.__('Add a follow up funnel','church-admin').'</a></p>';
    
    $departments=get_option('church_admin_departments');
    $result=$wpdb->get_results('SELECT * FROM '.CA_FUN_TBL .'  ORDER BY funnel_order');
    if($result)
    {
        
        echo'<table class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Funnel','church-admin').'</th><th>'.__('Applies to','church-admin').'...</th><th>'.__('Ministry Responsible','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Funnel','church-admin').'</th><th>'.__('Applies to','church-admin').'...</th><th>'.__('Ministry Responsible','church-admin').'</th></tr></tfoot><tbody>';
        foreach($result AS $row)
        {
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel&amp;funnel_id='.$row->funnel_id,'edit_funnel').'">'.__('Edit','church-admin').'</a>';
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_funnel&amp;funnel_id='.$row->funnel_id,'delete_funnel').'">'.__('Delete','church-admin').'</a>';
            echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$row->action.'</td><td>'.$member_type[$row->member_type_id].'</td><td>'.$departments[$row->department_id].'</td></tr>';
        }
    }
}

function church_admin_edit_funnel($funnel_id=NULL,$people_type_id=1)
{
    global $wpdb,$member_type,$people_type,$departments;
    
    $wpdb->show_errors();
    echo'<div class="church_admin wrap">';
    echo'<h2>';
        if($funnel_id){echo __('Edit','church-admin');$data=$wpdb->get_row('SELECT * FROM '.CA_FUN_TBL.' WHERE funnel_id="'.esc_sql($funnel_id).'"');}else{echo __('Add','church-admin');}
        echo' '.__('Follow Up Funnel','church-admin').'</h2>';
        
        if(isset($_POST['edit_funnel']))
        {//process form
            //deal with new department
            if(!empty($_POST['new_department'])&&$_POST['new_department']!=__('Or add a new department','church-admin'))
            {
                if(!in_array(stripslashes($_POST['new_department']),$departments))
                {
                    $departments[]=stripslashes($_POST['new_department']);
                    $_POST['department']=key($departments);
                    update_option('church_admin_departments',$departments);
                    church_admin_update_department(key($departments),$people_id);
                }
            }
            if(!$funnel_id)$funnel_id=$wpdb->get_var('SELECT funnel_id FROM '.CA_FUN_TBL.' WHERE action="'.esc_sql(stripslashes($_POST['action'])).'" AND member_type_id="'.esc_sql((int)($_POST['member_type_id'])).'"');
            if($funnel_id)
            {//update
                $success=$wpdb->query('UPDATE '.CA_FUN_TBL.' SET people_type_id="'.esc_sql($people_type_id).'", action="'.esc_sql(stripslashes($_POST['action'])).'",member_type_id="'.esc_sql((int)($_POST['member_type_id'])).'",department_id="'.esc_sql((int)($_POST['department_id'])).'" WHERE funnel_id="'.esc_sql($funnel_id).'"');
            }//end update
            else
            {//insert
                $success=$wpdb->query('INSERT INTO '.CA_FUN_TBL.' (action,member_type_id,department_id,people_type_id)VALUES("'.esc_sql(stripslashes($_POST['action'])).'" ,"'.esc_sql((int)($_POST['member_type_id'])).'","'.esc_sql((int)($_POST['department_id'])).'","'.esc_sql($people_type_id).'")');
            }//insert
            echo '<div class="updated fade"><p>'.__('Funnel Updated','church-admin').'</p></div>';
            church_admin_funnel_list($people_type_id);
        }//end process form
        else
        {//form
           echo'<form action="" method="POST">';
           
           //funnel action
           echo'<p><label>'.__('Funnel Action','church-admin').'</label><input type="text" name="action" ';
           if(!empty($data->action))echo ' value="'.$data->action.'" ';
           echo'/></p>';
           //member type
           echo'<p><label>'.__('Link to Member Type','church-admin').'</label><select name="member_type_id">';
           $first='<option value="">'.__('Please select member type','church-admin').'</option>';
           $option='';
           foreach($member_type AS $id=>$type)
           {
             if($id==$data->member_type_id){$first='<option value="'.$id.'" selected="selected">'.$type.'</option>'; }else{$option.='<option value="'.$id.'" >'.$type.'</option>';}
           }
           echo $first.$option.'</option></select></p>';
           //responsible department
           echo'<p><label>'.__('Ministry responsible for action','church-admin').'</label><select name="department_id">';
           $first=$option='';
           foreach($departments AS $id=>$type)
           {
             if($id==$data->member_type_id){$first='<option value="'.$id.'" selected="selected">'.$type.'</option>'; }else{$option.='<option value="'.$id.'" >'.$type.'</option>';}
           }
           echo $first.$option.'</option></select>';
           echo '<input type="text" name="new_department" onfocus="javascript:this.value=\'\';" value="'.__('Or add a new department','church-admin').'"/></p>';
           echo'</p>';
           echo'<p class="submit"><input type="hidden" name="edit_funnel" value="yes"/><input type="submit" value="'.__('Save Follow Up Funnel','church-admin').' &raquo;" /></p></form></div>';
        }//form
      echo'</div>';
}
?>