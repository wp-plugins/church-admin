<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_send_sms()
{
    global $wpdb,$member_type;
    $wpdb->show_errors();
    //check to see if directory is populated!
    $check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL);
    if(empty($check)||$check<1)
    {
	echo'<div class="updated fade">';
	echo'<p><strong>You need some people in the directory before you can use this Bulk SMS service</strong></p>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
	echo'</div>';
    }
    else
    {//people stored in directory
    
	if(isset($_POST['counttxt'])&& check_admin_referer('church admin send sms'))
	{
	    echo'<div id="message" class="updated fade">';
	    $username=get_option('church_admin_sms_username');
	    $password=get_option('church_admin_sms_password');
	    $sender=get_option('church_admin_sms_reply');    
	    $port = 80;    

    
	    //grab recipients
		if(!empty($_POST['member_type']))
		{
			$w=array();
			$where='(';
			foreach($_POST['member_type'] AS $key=>$value)if(array_key_exists($value,$member_type))$w[]=' member_type_id='.$value.' ';
			$where.=implode("||",$w).')';
			$sql='SELECT mobile FROM '.CA_PEO_TBL.' WHERE '.$where;
		}
		elseif(!empty($_POST['type']) && $_POST['type']=='smallgroup') $sql='SELECT mobile FROM '.CA_PEO_TBL.' WHERE smallgroup_id="'.esc_sql($_POST['group_id']).'"';
		elseif(!empty($_POST['type']) && $_POST['type']=='hope_team') $sql='SELECT a.mobile FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE b.meta_type="hope_team" AND a.people_id=b.people_id AND b.department_id="'.esc_sql($hope_team->hope_team_id).'"';
		elseif(!empty($_POST['type']) && $_POST['type']=='individuals')
		{
			$names=array();
            foreach ($_POST['person'] AS $value){$names[]='people_id = "'.esc_sql($value).'"';}
            $sql='SELECT  mobile FROM '.CA_PEO_TBL.' WHERE '.implode(' OR ',$names);
		}
		elseif(!empty($_POST['type']) && $_POST['type']=='roles')
		{
			foreach($_POST['role_id'] AS $key=>$value)$r[]='b.department_id='.$value;
			$sql='SELECT  a.mobile FROM '.CA_PEO_TBL.' a,'.CA_MET_TBL.' b WHERE b.meta_type="ministry" AND b.people_id=a.people_id AND a.mobile!="" AND ('.implode( " || ",$r).')' ;
      	}
		$results=$wpdb->get_results($sql);
		
	    $mobiles=array();

	    
            
            foreach ($results AS $row)
                {
                    $mobile=str_replace(' ','',$row->mobile);
                    //if starts with 0 replace with 44
					if(!empty($mobile)&&$mobile{0}=='0')
					{
                        $row->mobile=get_option('church_admin_sms_iso').substr($mobile, 1); 
                    }
                    if(!empty($mobile))$mobiles[]=$row->mobile;
		}    
		$mobiles=array_unique($mobiles);
		$needed=count($mobiles);
	    echo"$needed credits required<br/>";   

	    $msisdn = implode(',',$mobiles);     
	    $message = stripslashes($_POST['counttxt']);
	    $sms_type=get_option('church_admin_bulksms');
		$url = $sms_type.'/submission/send_sms/2/2.0';
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt ($ch, CURLOPT_PORT, $port);
	    curl_setopt ($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $post_body = '';
	    $post_fields = array('username' => $username,'password' => $password,'message' => $message,'msisdn' => $msisdn,'sender' => $sender);
	    foreach($post_fields as $key=>$value)
	    {
		$post_body .= urlencode($key).'='.urlencode($value).'&';
	    }
	    $post_body = rtrim($post_body,'&');

	    # Do not supply $post_fields directly as an argument to CURLOPT_POSTFIELDS,
	    # despite what the PHP documentation suggests: cUrl will turn it into in a
	    # multipart formpost, which is not supported:
	    curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_body);
	    $response_string = curl_exec($ch);
	    $curl_info = curl_getinfo($ch);
	    if ($response_string == FALSE)
	    {
	        print "cURL error: ".curl_error($ch)."\n";
	    }
	    elseif ($curl_info['http_code'] != 200)
	    {
	            print "Error: non-200 HTTP status code: ".$curl_info['http_code']."\n";
	    }
	    else
	    {
	        print "Response from server:";
	        $result = split('\|', $response_string);
	        if (count($result) != 3)
	        {
	            print "Error: could not parse valid return data from server.\n".count($result);
	        }
	        else
	        {
	    	if ($result[0] == '0')
                {
		    print "Message sent\n";
		}
		else
                {
                    print "Error sending: status code [$result[0]] description [$result[1]]\n";
		}
	    }
	    }
	    curl_close($ch);
	    echo"</p></div>";
  
	}//end send sms 
	else
	{
	church_admin_send_sms_form();  
	}
    }//people stored in directory
    
}

function church_admin_send_sms_form()
{
    global $member_type,$wpdb;
echo'
<script type="text/javascript">
/* <![CDATA[ */

function counterUpdate(opt_countedTextBox, opt_countBody, opt_maxSize) {
        var countedTextBox = opt_countedTextBox ? opt_countedTextBox : "counttxt";
        var countBody = opt_countBody ? opt_countBody : "countBody";
        var maxSize = opt_maxSize ? opt_maxSize : 1024;

        var field = document.getElementById(countedTextBox);

        if (field && field.value.length >= maxSize) {
                field.value = field.value.substring(0, maxSize);
        }
        var txtField = document.getElementById(countBody);
                if (txtField) {  
                txtField.innerHTML = field.value.length;
        }
}
/* ]]> */

</script><div class="wrap church_admin">
<h1>Send a text message</h1>
<form action="" method="post" name="SMS" id="SMS">
<p><label>Message <span id="countBody">&nbsp;&nbsp;0</span>/160 characters</label><textarea class="sms" id="counttxt" rows="4" cols="50" name="counttxt"  onkeyup="counterUpdate(\'counttxt\', \'countBody\',\'160\');"></textarea></p>
 
';
if ( function_exists('wp_nonce_field') )wp_nonce_field('church admin send sms');
	echo'<h2>Choose recipients...</h2>';
	echo'<p><label><strong>'.__('Choose a member type','church-admin').'</strong></label><input type="radio" name="type" value="member_type"  /></p>';
	echo'<fieldset id="member_type">';
	foreach($member_type AS $key=>$value)
	{
		echo'<p><label><strong>'.__('All','church-admin').' '.$value.'</strong></label><input type="checkbox" name="member_type[]" value="'.$key.'"/></p>';
	}
	echo'</fieldset>';
	echo'<p><label><strong>'.__('A Small group','church-admin').'</strong></label><input type="radio" name="type" value="smallgroup"/></p>';
	echo'<fieldset id="smallgroup">';
	echo'<p><label>'.__('Which group','church-admin').'</label><select name="group_id">';
	$results=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
	foreach($results AS $row)
	{
		echo'<option value="'.intval($row->id).'">'.esc_html($row->group_name).'</option>';
	}
	echo'</select></p></fieldset>';
	echo'<p><label><strong>'.__('Choose individuals','church-admin').'</strong></label><input type="radio" name="type" value="individuals"  /></p>';
    //choose individuals
    echo'<fieldset id="individuals">';
    echo '<div class="clonedInput" id="input1">';
    echo'<p><label>'.__('Select Person','church-admin').'</label><select name="person[]" id="person1" class="person">';
    $results=$wpdb->get_results('SELECT CONCAT_WS(", ",last_name,first_name) AS name,people_id FROM '.CA_PEO_TBL.' WHERE email!="" AND last_name!="" AND first_name!="" ORDER BY last_name');
    foreach($results AS $row)
    {
        echo '<option value="'.intval($row->people_id).'">'.esc_html($row->name).'</option>';
    }
    echo'</select></p></div>';
    echo'<p><input type="button" id="btnAdd" value="'.__('Add another person','church-admin').'" /><input type="button" id="btnDel" value="'.__('Remove person','church-admin').'" /></p></fieldset>';
  
    //end choose individuals
	echo'<p><label>'.__('Everyone in this ministry','church-admin').'...</label><input type="radio" name="type" value="roles"  /></p>';

    $roles=get_option('church_admin_departments');

     echo'<fieldset id="roles">';

    echo '<div class="roleclonedInput" id="roleinput1">';

    echo'<p><label>'.__('Select Ministry','church-admin').'</label><select name="role_id[]" id="roleid1" class="role_id">';

    foreach($roles AS $key=>$value)

    {

      echo'<option value="'.esc_html($key).'">'.esc_html($value).'</option>';

    }

    echo'</select></p></div>';

     echo'<p><input type="button" id="roleadd" value="'.__('Add another ministry','church-admin').'" /><input type="button" id="roledel" value="Remove ministry" /></p></fieldset>';

  
       
   echo'<p><label><strong>'.__('Hope Team','church-admin').'</strong></label><input type="radio" name="type" value="hope_team"/></p>';
   echo'<fieldset id="hope_team">';
    echo'<p><label>'.__('Select Hope Team Job','church-admin').'</label><select name="hope_team" ><option value="">Select job</option>';
    $results=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
    foreach($results AS $row)
    {
        echo '<option value="'.intval($row->hope_team_id).'">'.esc_html($row->job).'</option>';
    }
    echo'</select></p></fieldset>';
    
    //end choose individuals
   
    
//end of choose recipients


	echo'<p><input type="submit" name="submitted" value="Send Message"/></p></div></form>';  
}

?>