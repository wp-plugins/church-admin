<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_edit_facility($facilities_id=NULL)
{
    global $wpdb;
    
    if(isset($_POST['edit_facility']))
    {
		if(empty($facilities_id))$facilities_id=$wpdb->get_var('SELECT facilities_id FROM '.CA_FAC_TBL.' WHERE facility_name="'.esc_sql(stripslashes($_POST['facility'])).'"');
	   if(!empty($facilities_id))
        {
            $wpdb->query('UPDATE '.CA_FAC_TBL.' SET facility_name="'.esc_sql(stripslashes($_POST['facility'])).'" WHERE facilities_id="'.esc_sql($facilities_id).'"');
        }
        else
        {
            $nextorder=1+$wpdb->get_var('SELECT facilities_order FROM '.CA_FAC_TBL.' ORDER BY facilities_order LIMIT 1');
            $wpdb->query('INSERT INTO '.CA_FAC_TBL.'(facilities_order,facility_name)VALUES("'.esc_sql($nextorder).'","'.esc_sql(stripslashes($_POST['facility'])).'")');
        }
        
        echo'<div class="updated fade"><p>'.__('Facility Updated','church-admin').'</p></div>';
        church_admin_facilities();
    }
    else
    {
	
        
        echo'<div class="wrap church_admin"><h2>';
        if($facilities_id){echo' '.__('Edit','church-admin').' ';}else{echo __('Add','church-admin').' ';}
        echo __('Facility','church-admin').'</h2><form action="" method="POST">';
        echo'<p><label>'.__('Facility','church-admin').'</label><input type="text" name="facility" ';
        if(!empty($facilities_id))
	{
	    $type=$wpdb->get_var('SELECT facility_name FROM '.CA_FAC_TBL.' WHERE facilities_id="'.esc_sql($facilities_id).'"');
	    echo'value="'.$type.'" ';
	}
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_facility" value="yes"/><input type="submit" value="'.__('Save Facility','church-admin').' &raquo;" /></p></form></div>';
        
    }
}
function church_admin_delete_facility($facilities_id=NULL)
{
    global $wpdb;
    $wpdb->show_errors();
    if($facilities_id)
    {
        $wpdb->query('DELETE FROM '.CA_FAC_TBL.' WHERE facilities_id="'.esc_sql($facilities_id).'"');
        echo'<div class="updated fade"><p><strong>'.__('Facility Deleted','church-admin').'</strong></p></div>';
    }
    church_admin_facilities();
}
?>
