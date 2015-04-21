<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function  church_admin_facilities()
{
    global $wpdb;
   
    echo'<h2>'.__('Facilities','church-admin').'</h2>';
	echo'<p>'.__('This section if for editing any rooms and halls you have to use with room bookings, or equipment like projectors.','church-admin').'</p>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_facilities','edit_facilities').'">'.__('Add Facility','church-admin').'</a></p>';
    
    $facilities=$wpdb->get_results('SELECT * FROM '.CA_FAC_TBL.' ORDER BY facilities_order');
    if(!empty($facilities))
	{
		echo'<p>'.__('Facilities can be sorted by drag and drop, for use in other parts of the plugin','church-admin').'</p>';
		echo'<table id="sortable" class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Facility','church-admin').'</th><th>'.__('Facility Shortcode','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Facility','church-admin').'</th><th>'.__('Facility Shortcode','church-admin').'</th></tr></tfoot><tbody class="content">';
		foreach($facilities AS $facility)
		{
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_facility&amp;facilities_id='.$facility->facilities_id,'edit_facility').'">'.__('Edit','church-admin').'</a>';
        
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_facility&facilities_id='.$facility->facilities_id,'delete_facility').'">'.__('Delete','church-admin').'</a>';
			echo'<tr class="sortable-row" id="'.$facility->facilities_id.'"><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($facility->facility_name).'</td><td>[church_admin type="calendar" facilities_id="'.$facility->facilities_id.'"]</td></tr>';
       
		}
		echo'</tbody></table>';
		echo ' <script type="text/javascript">jQuery(document).ready(function($) {var fixHelper = function(e,ui){ui.children().each(function() {            $(this).width($(this).width());});  return ui; }; var sortable = $("#sortable tbody.content").sortable({ helper: fixHelper, stop: function(event, ui) {
        //create an array with the new order
        var Order = "order="+$(this).sortable(\'toArray\').toString();
   
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=facilities",
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
		</script>';
	}
}

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
