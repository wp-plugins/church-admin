<?php
function church_admin_prayer_chain()
{
/**
 *
 * Send prayer chain message by sms or email
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 

	global $wpdb;
	
	$username=get_option('church_admin_sms_username');
	$password=get_option('church_admin_sms_password');
	$sender=get_option('church_admin_sms_reply'); 
	$port = 80;  
	echo '<h1>Prayer Chain </h1>';
	
	if(!empty($_POST['add_to_prayer_chain']))
	{
		$id=church_admin_get_people_id($_POST['name']);
		if(!empty($id))
		{
			$ids=maybe_unserialize($id);
			foreach($ids as $key=>$value)
			{
				$wpdb->query('UPDATE '.CA_PEO_TBL.' set prayer_chain=1 WHERE people_id="'.esc_sql($value).'"');
				echo'<div class="updated fade"><p><strong>'.church_admin_get_person($value).' added to prayer chain</strong></p></div>';
			}
			
		}
	}
	if(!empty($_POST['delete_from_prayer_chain']))
	{
		$id=church_admin_get_people_id($_POST['name']);
		if(!empty($id))
		{
			$ids=maybe_unserialize($id);
			foreach($ids as $key=>$value)
			{
				$wpdb->query('UPDATE '.CA_PEO_TBL.' set prayer_chain=0 WHERE people_id="'.esc_sql($value).'"');
				echo'<div class="updated fade"><p>'.$_POST['name'].' taken off prayer chain<strong></p></div>';
			}
			
		}
	}
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ", first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE prayer_chain=1 ORDER BY last_name,first_name');
	if(!empty($results))
	{
		echo'<h2> The prayer chain is made up of...</h2><p>';
		foreach($results AS $row) echo $row->name.', ';
		echo'</p>';
	}
	$count=$wpdb->get_var('SELECT COUNT(prayer_chain) FROM '.CA_PEO_TBL.' WHERE prayer_chain=1');
	if(!empty($_POST['send_prayer_message']))
	{
		$sql='SELECT  DISTINCT mobile,email,CONCAT_WS(" ",first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE  prayer_chain=1';
		$results=$wpdb->get_results($sql);
			if(!empty($_POST['counttxt'])&&!empty($username))
			{//send sms
				
				if(!empty($results))
				{
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
					$msisdn = implode(',',$mobiles);
					$message = stripslashes($_POST['counttxt']);
					$sms_type=get_option('church_admin_bulksms');
					if(empty($sms_type)){$url = 'http://community.bulksms.co.uk:5567/eapi/submission/send_sms/2/2.0';}
					else{$url = $sms_type.':5567/eapi/submission/send_sms/2/2.0';}
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
					echo"</p>";
					
				}
				else
				{
					echo '<p>Nobody is on the prayer chain yet</p>';
				}
				
			
			}//end sms
			if(!empty($_POST['message']))
			{
				$message=stripslashes($_POST['message']);
				if(!empty($results))
				{
					foreach($results AS $row)
					{
						if(QueueEmail($row->email,__('Prayer Chain Request','church-admin'),$message,'','',get_option('admin_email'),''))echo'<p>Prayer request sent to '.$row->name.'</p>';
					}
				}
			}
	
	}
	else
	{
			
			echo '<form action="" method="POST"><p><label>Add to Chain</label><input type="text" name="name" placeholder="Name"/><input type="hidden" name="add_to_prayer_chain"
			value="yes"/><input type="submit" value="Add"/></form></p>';
			echo'';
			echo '<form action="" method="POST"><p><label>Take off Chain</label><input type="text" name="name" placeholder="Name"/><input type="hidden" name="delete_from_prayer_chain" value="yes"/><input type="submit" value="Take Off"/></form></p>';
			echo '<h2>Send a message to   ' .$count.' people in the chain</h2><form action="" method="POST">';
			if(!empty($username)) echo '<h3>'.__('Text Message','church-admin').' <span id="countBody">&nbsp;&nbsp;0</span>/140 characters</h3><p><textarea class="sms" id="counttxt" rows="4" cols="50" name="counttxt"  onkeyup="counterUpdate(\'counttxt\', \'countBody\',\'140\');"></textarea></p>';
			echo'<h3>'.__('And/Or Email version','church-admin').'</h3>';
			wp_editor('','message');
			if(!empty($username)) echo '<script type="text/javascript">
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
</script>';
echo'<p><input type="hidden" name="send_prayer_message" value="yes"/><input type="submit" name="Submit" value="'.__('Send message','church-admin').'"/></p></form>';


	
	}
	
}