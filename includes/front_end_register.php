<?php


function church_admin_front_end_register($email_verify=TRUE,$admin_email=TRUE)
{
    global $wpdb,$people_type;
    $out='';
    if(!empty($_POST['save']) && wp_verify_nonce($_POST['church_admin_register'], 'church_admin_register')   )//add verify nonce
    {//process
        
        $out.=print_r($_POST);
        $address=esc_sql(serialize(array('address_line1'=>stripslashes($_POST['address_line1']),'address_line2'=>stripslashes($_POST['address_line2']),'town'=>stripslashes($_POST['town']),'county'=>stripslashes($_POST['county']),'postcode'=>stripslashes($_POST['postcode']))));
        $lat=esc_sql($_POST['lat']);
        $lng=esc_sql($_POST['lng']);
        $phone=esc_sql($_POST['phone']);
        $household_id=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng)VALUES("'.$address.'","'.$lat.'","'.$lng.'","'.$phone.'")');
        $sql=array();
        for($x=0;$x<=count($_POST['first_name']);$x++)
        {
            if($_POST['sex'][$x]=='male'){$sex=1;}else{$sex=0;}
            $sql[]='("'.esc_sql(stripslashes($_POST['first_name'][$x])).'","'.esc_sql(stripslashes($_POST['last_name'][$x])).'","'.esc_sql(stripslashes($_POST['mobile'][$x])).'","'.esc_sql(stripslashes($_POST['email'][$x])).'","'.$sex.'","'.esc_sql($household_id).'","'.esc_sql((int)$_POST['people_type_id']).'","0")';
        }
        $wpdb->query('INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,mobile,email,sex,household_id,people_type_id,member_type_id) VALUES '.implode(",",$sql));
        
        if($admin_email)
        {
            $message='<p>'.__('A new household has registered on','church-admin').' '.site_url().'</p><p>'.__('Please','church-admin').'  <a href="'.site_url().'wp-admin/admin.php?page=church_admin/index.php&action=church_admin_recent_activity">'.__('check them out','church-admin').'.</a></p>';
            add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
            wp_mail(get_option('admin_email'),"'.__('New site registration','church-admin').'",$message);
        }
        $out.='<p>'.__('Thank you for registering on the site','church-admin').'</p>';
    }//end process
    else
    {//form
        $out.='<div class="church_admin"><h2>'.__('Registration','church-admin').'</h2>';
        $out.='<form action="" method="post"><input type="hidden" name="save" value="yes"/>';
        $out.=wp_nonce_field('church_admin_register','church_admin_register');
        $out.='<div class="clonedInput" id="input1">';
        $out.='<p><label>'.__('First Name','church-admin').'</label><input type="text" class="first_name" id="first_name1" name="first_name[]"/></p>';
        $out.='<p><label>'.__('Last Name','church-admin').'</label><input type="text" class="last_name" id="last_name1" name="last_name[]"/></p>';
        $out.='<p><label>'.__('Mobile','church-admin').'</label><input type="text" class="mobile" id="mobile1" name="mobile[]"/></p>';
        $out.='<p><label>'.__('Person type','church-admin').'</label><select name="people_type_id" id="people_type1" class="people_type_id">';
        foreach($people_type AS $id=>$type){$out.='<option value="'.$id.'">'.$type.'</option>';}
        $out.='</select></p>';
        $out.='<p><label>'.__('Email','church-admin').'</label><input type="text" class="email" id="email1" name="email[]"/></p>';
        $out.='<p><label>'.__('Sex','church-admin').'</label><input type="radio" name="sex" class="male" id="male1" value="male"/>'.__('Male','church-admin').' <input type="radio" name="sex" class="male" id="male1" value="female"/>'.__('Female','church-admin').'</p>';
        $out.='</div>';
        
        $out.='<p id="jquerybuttons"><input type="button" id="btnAdd" value="'.__('Add another person','church-admin').'" /><input type="button" id="btnDel" value="'.__('Remove person','church-admin').'" /></p>';;
        
        require_once(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');
        $out.= church_admin_address_form(NULL,NULL);
        $out.='<p><label>'.__('Phone','church-admin').'</label><input name="phone" type="text"/></p>';
        $out.= '<p><input type="submit" value="'.__('Register','church-admin').'"/></form></div>';
        
    }//form
    return $out;
}
?>