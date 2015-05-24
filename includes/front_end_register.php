<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_front_end_register($email_verify=TRUE,$admin_email=TRUE,$member_type_id=1)
{
	        /**
 *
 * Front End Registration
 * 
 * @author  Andy Moyle
 * @param    $email_verify,$admin_email
 * @return   
 * @version  0.3
 *
 * 0.2 fixed address save
 * 0.3 added recaptcha service
 * 
 */
    global $wpdb,$people_type;
    if(!ctype_digit($member_type_id))$member_type_id=1;
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/recaptchalib.php');
    $out='';
    $privatekey = "6LclNecSAAAAAG2iyW5voI-9oaVwfgjix59dTeJN";
	if(!empty($_POST))$resp = church_admin_recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);

    if(!empty($_POST['save']) &&($resp->is_valid) && wp_verify_nonce($_POST['church_admin_register'], 'church_admin_register')   )//add verify nonce
    {//process
        
	
	$form=$sql=array();
	foreach ($_POST AS $key=>$value)$form[$key]=stripslashes_deep($value);
	
	$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.esc_sql(sanitize_text_field($form['address'])).'" AND lat="'.esc_sql(sanitize_text_field($form['lat'])).'" AND lng="'.esc_sql(sanitize_text_field($form['lng'])).'" AND phone="'.esc_sql(sanitize_text_field($form['phone'])).'"');
	if(empty($household_id))
	{//insert
	    $success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.esc_sql(sanitize_text_field($form['address'])).'", "'.esc_sql(sanitize_text_field($form['lat'])).'","'.esc_sql(sanitize_text_field($form['lng'])).'","'.esc_sql(sanitize_text_field($form['phone'])).'" )');
	    $household_id=$wpdb->insert_id;
	}//end insert
	else
	{//update
	   $success=$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.esc_sql(sanitize_text_field($form['address'])).'" , lat="'.esc_sql(sanitize_text_field($form['lat'])).'" , lng="'.esc_sql(sanitize_text_field($form['lng'])).'" , phone="'.esc_sql(sanitize_text_field($form['phone'])).'" WHERE household_id="'.esc_sql($household_id).'"');
	}//update
	$sql=array();
        for($x=0;$x<count($_POST['first_name']);$x++)
        {
			$y=$x+1;
            if($_POST['sex'.$y]=='male'){$sex=1;}else{$sex=0;}
            if(!empty($_POST['first_name'][$x])){$first_name=sanitize_text_field($form['first_name'][$x]);}else{$first_name='';}
			if(!empty($_POST['prefix'][$x])){$prefix=sanitize_text_field($form['prefix'][$x]);}else{$prefix='';}
            if(!empty($_POST['last_name'][$x])){$last_name=sanitize_text_field($form['last_name'][$x]);}else{$last_name='';}
            if(!empty($_POST['mobile'][$x])){$mobile=sanitize_text_field($form['mobile'][$x]);}else{$mobile='';}
            if(!empty($_POST['email'][$x])){$email=sanitize_text_field($form['email'][$x]);}else{$email='';}
            if(!empty($_POST['people_type_id'][$x])){$people_type_id=sanitize_text_field($form['people_type_id'][$x]);}else{$people_type_id='';}
            $sql[]='("'.esc_sql($first_name).'","'.esc_sql($prefix).'","'.esc_sql($last_name).'","'.esc_sql($mobile).'","'.esc_sql($email).'","'.$sex.'","'.esc_sql($household_id).'","'.esc_sql((int)$people_type_id).'","'.$member_type_id.'")';
        
        }
        $query='INSERT INTO '.CA_PEO_TBL.' (first_name,prefix,last_name,mobile,email,sex,household_id,people_type_id,member_type_id) VALUES '.implode(",",$sql);
        
        $wpdb->query($query);
        
        if($admin_email)
        {
            $message='<p>'.__('A new household has registered on','church-admin').' '.site_url().'</p><p>'.__('Please','church-admin').'  <a href="'.site_url().'wp-admin/admin.php?page=church_admin/index.php&action=church_admin_recent_activity&tab=people">'.__('check them out','church-admin').'.</a></p>';
            add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
            wp_mail(get_option('admin_email'),__('New site registration','church-admin'),$message);
        }
        $out.='<p>'.__('Thank you for registering on the site','church-admin').'</p>';
    }//end process
    else
    {//form
        $out.='<div class="church_admin"><h2>'.__('Registration','church-admin').'</h2>';
        $out.='<form action="" method="post"><input type="hidden" name="save" value="yes"/>';
        $out.=wp_nonce_field('church_admin_register','church_admin_register',TRUE,FALSE);
        $out.='<div class="clonedInput" id="input1">';
        $out.='<p><label>'.__('First Name','church-admin').'</label><input type="text" class="first_name" id="first_name1" name="first_name[]"/></p>';
        $out.='<p><label>'.__('Prefix (e.g.van der)','church-admin').'</label><input type="text" class="prefix" id="prefix1" name="prefix[]" /></p>';
        $out.='<p><label>'.__('Last Name','church-admin').'</label><input type="text" class="last_name" id="last_name1" name="last_name[]"/></p>';
        $out.='<p><label>'.__('Mobile','church-admin').'</label><input type="text" class="mobile" id="mobile1" name="mobile[]"/></p>';
        $out.='<p><label>'.__('Person type','church-admin').'</label><select name="people_type_id[]" id="people_type1" class="people_type_id">';
        foreach($people_type AS $id=>$type){$out.='<option value="'.$id.'">'.$type.'</option>';}
        $out.='</select></p>';
        $out.='<p><label>'.__('Email','church-admin').'</label><input type="text" class="email" id="email1" name="email[]"/></p>';
        $out.='<p><label>'.__('Sex','church-admin').'</label><input type="radio" name="sex1" class="male" id="male1" value="male"/>'.__('Male','church-admin').' <input type="radio" name="sex1" class="female" id="female1" value="female"/>'.__('Female','church-admin').'</p>';
        $out.='</div>';
        
        $out.='<p id="jquerybuttons"><input type="button" id="btnAdd" value="'.__('Add another person','church-admin').'" /><input type="button" id="btnDel" value="'.__('Remove person','church-admin').'" /></p>';;
        $out.='<p><label>'.__('Phone','church-admin').'</label><input name="phone" type="text"/></p>';
        require_once(plugin_dir_path(dirname(__FILE__)).'includes/directory.php');
        $out.= church_admin_address_form(NULL,NULL);
        //recaptcha service
        
		$out.='<div class="clear"></div>';
		$out.= '<p><label>'.__('To prevent automated registration','church-admin').'</label>'.church_admin_recaptcha_get_html('6LclNecSAAAAACStrXZYLozPCWO1BP7h8X27R54h').'</p>';
        $out.= '<p><input type="submit" value="'.__('Register','church-admin').'"/></form></div>';
        
    }//form
    return $out;
}
?>