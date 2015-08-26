<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_funnel_list()
{
    global $wpdb,$people_type;
	$member_type=church_admin_member_type_array();
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel','edit_funnel').'">'.__('Add a follow up funnel','church-admin').'</a></p>';
	$departments=get_option('church_admin_departments');
    $result=$wpdb->get_results('SELECT * FROM '.CA_FUN_TBL .'  ORDER BY funnel_order');
    if($result)
    {
        
        echo'<table class="widefat striped"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Funnel','church-admin').'</th><th>'.__('Applies to','church-admin').'...</th><th>'.__('Ministry Responsible','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Funnel','church-admin').'</th><th>'.__('Applies to','church-admin').'...</th><th>'.__('Ministry Responsible','church-admin').'</th></tr></tfoot><tbody>';
        foreach($result AS $row)
        {
           if(!empty($row->department_id)&&!empty($departments[$row->department_id]))
		   {
			   $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel&amp;funnel_id='.intval($row->funnel_id),'edit_funnel').'">'.__('Edit','church-admin').'</a>';
				$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_funnel&amp;funnel_id='.intval($row->funnel_id),'delete_funnel').'">'.__('Delete','church-admin').'</a>';
				echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($row->action).'</td><td>';
				if(!empty($member_type[$row->member_type_id])){echo esc_html($member_type[$row->member_type_id]);}else{echo'&nbsp;';}
				echo '</td><td>'.$departments[$row->department_id].'</td></tr>';
		   }
        }
		echo'</tbody></table>';
    }
}

function church_admin_edit_funnel($funnel_id=NULL,$people_type_id=1)
{
    global $wpdb,$people_type;
	$departments=get_option('church_admin_departments');
    $member_type=church_admin_member_type_array();
    
    
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
                    $departments[]=sanitize_text_field(stripslashes($_POST['new_department']));
                    $_POST['department']=key($departments);
                    update_option('church_admin_departments',$departments);
                    church_admin_update_department(key($departments),$people_id,'ministry');
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
           echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Funnel Action','church-admin').'</th><td><input type="text" name="action" ';
           if(!empty($data->action))echo ' value="'.esc_html($data->action).'" ';
           echo'/></td></tr>';
           //member type
           echo'<tr><th scope="row">'.__('Link to Member Type','church-admin').'</th><td><select name="member_type_id">';
           $first='<option value="">'.__('Please select member type','church-admin').'</option>';
           $option='';
           foreach($member_type AS $id=>$type)
           {
             if($id==$data->member_type_id){$first='<option value="'.intval($id).'" selected="selected">'.esc_html($type).'</option>'; }else{$option.='<option value="'.intval($id).'" >'.esc_html($type).'</option>';}
           }
           echo $first.$option.'</option></select></td></tr>';
           //responsible department
           echo'<tr><th scope="row">'.__('Ministry responsible for action','church-admin').'</th><td><select name="department_id">';
           $first=$option='';
           foreach($departments AS $id=>$type)
           {
             if($id==$data->member_type_id){$first='<option value="'.intval($id).'" selected="selected">'.esc_html($type).'</option>'; }else{$option.='<option value="'.intval($id).'" >'.esc_html($type).'</option>';}
           }
           echo $first.$option.'</option></select></td></tr>';
           echo '<tr><th scope="row">'.__('Or create a new ministry','church-admin').'</th><td><input type="text" name="new_department" onfocus="javascript:this.value=\'\';" value="'.__('Or add a new ministry','church-admin').'"/></td></tr>';
           
           echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="edit_funnel" value="yes"/><input type="submit" value="'.__('Save Follow Up Funnel','church-admin').' &raquo;" /></td></tr></tbody></table></form>';
        }//form
      echo'</div>';
}
?>