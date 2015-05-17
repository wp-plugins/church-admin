<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_service_list()
{
    global $wpdb,$days;
    echo'<table class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Service Name','church-admin').'</th><th>'.__('Day','church-admin').'</th><th>'.__('Time','church-admin').'</th><th>'.__('Venue','church-admin').'</th><th>'.__('Address','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Service Name','church-admin').'</th><th>'.__('Day','church-admin').'</th><th>'.__('Time','church-admin').'</th><th>'.__('Venue','church-admin').'</th><th>'.__('Address','church-admin').'</th></tr></tfoot><tbody>';
    
    $sql='SELECT * FROM '.CA_SER_TBL;
    $results=$wpdb->get_results($sql);
    if($results)
    {
        foreach($results AS $row)
        {
           $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service&amp;id='.intval($row->service_id),'edit_service').'">Edit</a>';
           $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_service&amp;id='.intval($row->service_id),'delete_service').'">Delete</a>';
           echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html($row->service_name).'</td><td>'.esc_html($days[$row->service_day]).'</td><td>'.esc_html($row->service_time).'</td><td>'.esc_html($row->venue).'</td><td>'.esc_html($row->address).'</td></tr>';
        }
        echo'</tbody></table>';
    }
    
}
function church_admin_delete_service($id)
{
	global $wpdb;
	if(!empty($_POST['confirm_delete']))
	{
		$wpdb->query('DELETE FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql(intval($id)).'"');
		$wpdb->query('DELETE FROM '.CA_ROT_TBL.' WHERE service_id="'.esc_sql(intval($id)).'"');
		echo'<div class="wrap church_admin"><div class="updated fade"><p>'.__('Service deleted','church-admin').'</p></div>';
        church_admin_service_list();
        echo'</div>';
	}
	else
	{
		echo'<form action="" method="POST"><p><label>'.__('Are you sure?','church-admin').'</label><input type="hidden" name="confirm_delete" value="yes"/><input type="submit" value="'.__('Yes','church-admin').'"/></p></form>';
	}

}
function church_admin_edit_service($id)
{
    global $wpdb,$days;
    if($id)$data=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql(intval($id)).'"');
    if(isset($_POST['service']))
    {
        
        $form=array();
        foreach($_POST AS $key=>$value)$form[$key]=sanitize_text_field(stripslashes($value));
        
       
        if(!$id)$id=$wpdb->get_var('SELECT service_id FROM '.CA_SER_TBL.' WHERE service_name="'.esc_sql($form['service_name']).'" AND service_day="'.esc_sql($form['service_day']).'" AND service_time="'.esc_sql($form['service_time']).'" AND venue="'.esc_sql($form['venue']).'" AND address="'.esc_sql($form['address']).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'"');
        if($id)
        {//update
            $sql='UPDATE '.CA_SER_TBL.' SET service_name="'.esc_sql($form['service_name']).'" , service_day="'.esc_sql($form['service_day']).'" , service_time="'.esc_sql($form['service_time']).'" , venue="'.esc_sql($form['venue']).'" , address="'.esc_sql($form['address']).'" , lat="'.esc_sql($form['lat']).'" , lng="'.esc_sql($form['lng']).'" WHERE service_id="'.esc_sql(intval($id)).'"';
            
            $wpdb->query($sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,venue,address,lat,lng,first_meeting) VALUES ("'.esc_sql($form['service_name']).'","'.esc_sql($form['service_day']).'","'.esc_sql($form['service_time']).'","'.esc_sql($form['venue']).'","'.esc_sql($form['address']).'","'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'","'.date('Y-m-d').'")'); 
        }//insert
        echo'<div class="wrap church_admin"><div class="updated fade"><p>'.__('Service saved','church-admin').'</p></div>';
        church_admin_service_list();
        echo'</div>';
    }
    else
    {
       echo'<div class="wrap church_admin"><h2>Service</h2>';
       echo'<form action="" method="post">';
       echo'<p><label>'.__('Service Name','church-admin').'</label><input type="text" name="service_name" ';
       if(!empty($data->service_name))echo' value="'.esc_html($data->service_name).'" ';
       echo'/></p>';
       echo'<p><label>Service Day</label><select name="service_day"> ';
       foreach($days AS $key=>$value)
       {
         echo'<option value="'.intval($key).'"';
         if(!empty($data->service_day))selected($key,$data->service_day);
         echo '>'.esc_html($value).'</option>';
       }
       echo'</select></p>';
       echo'<p><label>'.__('Service Time','church-admin').'</label><input type="text" name="service_time" ';
       if(!empty($data->service_time))echo' value="'.esc_html($data->service_time).'" ';
       echo'/></p>';
       echo'<p><label>'.__('Service Venue','church-admin').'</label><input type="text" name="venue" ';
       if(!empty($data->venue))echo' value="'.esc_html($data->venue).'" ';
       echo'/></p>';
       require_once(plugin_dir_path(dirname(__FILE__)).'includes/directory.php');
       if(empty($data))$data=new stdClass();
	   
	   echo church_admin_address_form($data,NULL);
       echo'<p class="submit"><input type="hidden" name="service" value="yes"/><input type="submit" value="'.__('Save Service','church-admin').'&raquo;" /></p></form></div>';
    }
}
?>