<?php



function church_admin_send_sms()
{
    global $wpdb;
    if(isset($_POST['counttxt'])&& check_admin_referer('church admin send sms'))
    {
       echo'<div id="message" class="updated fade">';
$username=get_option(church_admin_sms_username);
$password=get_option(church_admin_sms_password);
$sender=get_option(church_admin_sms_reply);    
$port = 80;    

    
//grab recipients
$mobiles=array();

        $results=$wpdb->get_results('SELECT DISTINCT mobile FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($_POST['who']).'"');
        $needed=count($results);
        foreach ($results AS $row)
            {
                $row->mobile=str_replace(' ','',$row->mobile);
                //if starts with 0 replace with 44
		if($row->mobile{0}=='0')
		{
                    $row->mobile=get_option(church_admin_sms_iso).substr($row->mobile, 1); 
                }
                $mobiles[]=$row->mobile;
            }    
    
 echo"$needed credits required<br/>";   

    $msisdn = implode(',',$mobiles);     
    $message = stripslashes($_POST['counttxt']);
    $url = 'http://community.bulksms.co.uk:5567/eapi/submission/send_sms/2/2.0';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_PORT, $port);
    curl_setopt ($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $post_body = '';
    $post_fields = array(
		username => $username,
		password => $password,
		message => $message,
		msisdn => $msisdn,
		sender => $sender
	);
    foreach($post_fields as $key=>$value) {
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
    
} 
    else
    {
      church_admin_send_sms_form();  
    }
    
}

function church_admin_send_sms_form()
{
    global $member_type;
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
<div id="church_admin_phone">
';
if ( function_exists('wp_nonce_field') )wp_nonce_field('church admin send sms');
echo'<div id="church_admin_whoto">Who to?<br/><select name="who">';
 foreach($member_type AS $key=>$value)
 {
   echo'<option  value="'.$key.'"/>'.$value.'</option>';
 }


echo'</select></div>
 <div id="church_admin_message"><span id="countBody">&nbsp;&nbsp;0</span>/160 characters<br/><textarea class="sms" id="counttxt" rows="4" cols="50" name="counttxt"  onkeyup="counterUpdate(\'counttxt\', \'countBody\',\'160\');"></textarea></div>
 
 
  <div id="church_admin_submit"><input type="submit" name="submitted" value="Send Message"/></div>
</div>
</div>
 </form>
  
';  
}

?>