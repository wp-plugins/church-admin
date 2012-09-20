<?php
function church_admin_service_list()
{
    global $wpdb,$days;
    echo'<div class="wrap church_admin"><h2>Services List</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service','edit_service').'">Add a service</a></p>';
    echo'<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Service Name</th><th>Day</th><th>Time</th><th>Venue</th><th>Address</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Service Name</th><th>Day</th><th>Time</th><th>Venue</th><th>Address</th></tr></tfoot><tbody>';
    
    $sql='SELECT * FROM '.CA_SER_TBL;
    $results=$wpdb->get_results($sql);
    if($results)
    {
        foreach($results AS $row)
        {
           $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service&amp;id='.$row->service_id,'edit_service').'">Edit</a>';
           $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_service&amp;id='.$row->service_id,'delete_service').'">Delete</a>';
           echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$row->service_name.'</td><td>'.$days[$row->service_day].'</td><td>'.$row->service_time.'</td><td>'.$row->venue.'</td><td>'.implode(", ",array_filter(unserialize($row->address))).'</td></tr>';
        }
        echo'</tbody></table>';
    }
    
}

function church_admin_edit_service($id)
{
    global $wpdb,$days;
    if($id)$data=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($id).'"');
    if(isset($_POST['service']))
    {
        
        $form=array();
        foreach($_POST AS $key=>$value)$form[$key]=stripslashes($value);
        
        $address=serialize(array('address_line1'=>$form['address_line1'],'address_line2'=>$form['address_line1'],'town'=>$form['town'],'county'=>$form['county'],'postcode'=>$form['postcode']));
        if(!$id)$id=$wpdb->get_var('SELECT service_id FROM '.CA_SER_TBL.' WHERE service_name="'.esc_sql($form['service_name']).'" AND service_day="'.esc_sql($form['service_day']).'" AND service_time="'.esc_sql($form['service_time']).'" AND venue="'.esc_sql($form['venue']).'" AND address="'.esc_sql($address).'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'"');
        if($id)
        {//update
            $sql='UPDATE '.CA_SER_TBL.' SET service_name="'.esc_sql($form['service_name']).'" , service_day="'.esc_sql($form['service_day']).'" , service_time="'.esc_sql($form['service_time']).'" , venue="'.esc_sql($form['venue']).'" , address="'.esc_sql($address).'" , lat="'.esc_sql($form['lat']).'" , lng="'.esc_sql($form['lng']).'" WHERE service_id="'.esc_Sql($id).'"';
            
            $wpdb->query($sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,venue,address,lat,lng,first_meeting) VALUES ("'.esc_sql($form['service_name']).'","'.esc_sql($form['service_day']).'","'.esc_sql($form['service_time']).'","'.esc_sql($form['venue']).'","'.esc_sql($address).'","'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'","'.date('Y-m-d').'")'); 
        }//insert
        echo'<div class="wrap church_admin"><div class="updated fade"><p>Service saved</p></div>';
        church_admin_service_list();
        echo'</div>';
    }
    else
    {
       echo'<div class="wrap church_admin"><h2>Service</h2>';
       echo'<form action="" method="post">';
       echo'<p><label>Service Name</label><input type="text" name="service_name" ';
       if(!empty($data->service_name))echo' value="'.$data->service_name.'" ';
       echo'/></p>';
       echo'<p><label>Service Day</label><select name="service_day"> ';
       foreach($days AS $key=>$value)
       {
         echo'<option value="'.$key.'"';
         selected($key,$data->service_day);
         echo '>'.$value.'</option>';
       }
       echo'</select></p>';
       echo'<p><label>Service Time</label><input type="text" name="service_time" ';
       if(!empty($data->service_time))echo' value="'.$data->service_time.'" ';
       echo'/></p>';
       echo'<p><label>Service Venue</label><input type="text" name="venue" ';
       if(!empty($data->venue))echo' value="'.$data->venue.'" ';
       echo'/></p>';
       require_once(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');
       echo church_admin_address_form($data,$error=NULL);
       echo'<p class="submit"><input type="hidden" name="service" value="yes"/><input type="submit" value="Save Service&raquo;" /></p></form></div>';
    }
}
?>