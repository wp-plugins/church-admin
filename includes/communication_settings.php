<?php
/*
 This file contains the communications settings
 2011-03-05 Removed create cronemail.php - now permanently there
*/
function church_admin_communication_settings()
{
    //only output other setting if opposite form not submitted!
    echo '<div class="wrap church_admin">';
    if(!isset($_POST['sms_username'])) church_admin_email_settings();
    if(!isset($_POST['username'])) church_admin_sms_settings();    
    echo '</div>';
}

function church_admin_sms_settings()
{
    if(!empty($_POST['sms_username']))
    {
        $username = $_POST['sms_username'];
	$password = $_POST['sms_password'];
	$msisdn = $_POST['sms_reply'];
        $message = 'Testing bulk sms';
	$url = 'http://www.bulksms.co.uk/eapi/submission/send_sms/2/2.0';
	$port = 80;   
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
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_body);
	$response_string = curl_exec($ch);
	$curl_info = curl_getinfo($ch);

	if ($response_string == FALSE) {
		print "Error communicating with bulksms.co.uk: ".curl_error($ch)."\n";
	} elseif ($curl_info['http_code'] != 200) {
		print "Error: non-200 HTTP status code: ".$curl_info['http_code']."\n";
	}
	else {
		$result = split('\|', $response_string);
		if (count($result) != 3) {
			print "Error: could not parse valid return data from server.\n".count($result);
		} else {
			if ($result[0] == '0')
                        {
                            update_option('church_admin_sms_username',$_POST['sms_username']);
                            update_option('church_admin_sms_password',$_POST['sms_password']);
                            update_option('church_admin_sms_reply',$_POST['sms_reply']);
                            update_option('church_admin_sms_iso',$_POST['sms_iso']);
                            unset($_POST);
                                echo '<div id="message" class="updated fade"><p><strong>Your bulk sms settings have been updated and a test sms was sent to '.esc_html(get_option('sms_reply')).'.</strong></p><p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_communication_settings">Back to communication Settings &raquo;<a/></p></div>';

			}
			else {
				print "Error sending: status code [$result[0]] description [$result[1]]\n";
			}
		}
	}
	curl_close($ch);
        
    }
    else
    {
         echo'<form action="" method="post">';
        
        church_admin_sms_settings_form();
        echo'<p class="submit"><input type="submit" name="communication_settings" value="Edit SMS Settings &raquo;" /></p></form>';
    }
}
function church_admin_email_settings()
{
    global $wpdb;
    if(!empty($_POST['username']))
    {
        if(!empty($_POST['quantity'])){update_option('church_admin_bulk_email',$_POST['quantity']);}else{delete_option('church_admin_bulk_email');}
        update_option('c',$_POST['host']);
        update_option('mailserver_login',$_POST['username']);
        update_option('mailserver_password',$_POST['password']);
        update_option('mailserver_port',$_POST['port']);
        if(!empty($_POST['queue'])) {update_option('church_admin_cron',$_POST['cron']);}else{update_option('church_admin_cron','');delete_option('church_admin_bulk_email');}
       
      
//sort out wp-cron
if(get_option('church_admin_cron')=='wp-cron')
{
    add_action('church_admin_bulk_email','church_admin_cron');
   $timestamp=mktime();
    wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
}
if(get_option('church_admin_cron')=='cron')wp_clear_scheduled_hook('church_admin_bulk_email');
if(empty($_POST['queue'])) wp_clear_scheduled_hook('church_admin_bulk_email');
        //add test email
        
        //add test sms
        
        
        echo '<div id="message" class="updated fade"><p><strong>Email settings updated</strong></p>';
        if (get_option('church_admin_cron')=='cron')
        {//if queue explain how to set up cron!
        echo '<p>'.church_admin_cron_job_instructions().' for setting up email queuing on your Linux or Unix webserver</p></strong><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_communication_settings">Back to communication Settings &raquo;<a/></p></div>';
        }
    }
    else
    {
        echo'<h2>Email Settings</h2><p>Many hosts limit how many email you are allowed to send an hour. If you want emails to be sent in batches, check "Queue Email" below.</p>';
        echo'<form action="" method="post">';
        
        church_admin_email_settings_form();
        echo'<p class="submit"><input type="submit" name="communication_settings" value="Edit Settings &raquo;" /></p></form>';

        
    }
}

function church_admin_email_settings_form()
{
    echo '<ul><li><label >Do you want to queue email?</label>';
   echo'<input type="checkbox" name="queue" ';
    if(get_option('church_admin_bulk_email')) {echo 'checked="checked" ';}//if already opted display checked
    echo'onclick="javascript:toggle(\'quantity\')" /> </li></ul><div id="quantity"';//allow toggle
    if(!get_option('church_admin_bulk_email'))echo ' style="display:none" ';//don't display if not queued already
    echo '><ul><li><label>Max emails per hour?</label><input type="text" name="quantity" value="'.get_option('church_admin_bulk_email').'"/></li><li> There are two ways to set up sending emails in batches</li><li><strong>Using cron</strong> the best method if you are on a Linux based host. There server checks and sends any emails queued every hour in batches set by you.</li><li><strong>Using wp_cron</strong> - wp-cron works by scheduling every hour, but relies on people visiting your site regularly to do it in the background. (only option on windows hosts!)</li><li><label>I want to use cron:</label><input type="radio" name="cron" value="cron" ';
        if (get_option('church_admin_cron')=='cron') echo 'checked="checked"';
        echo'/></li><li><label>I want to use wp-cron:</label><input type="radio" name="cron" value="wp-cron"';
        if (get_option('church_admin_cron')=='wp-cron') echo 'checked="checked"';
        echo'/></li></ul></div>';

    echo'<ul><li><label >Your mail host:</label><input type="text" name="host" value="'.get_option('mailserver_url').'"/></li><li><label >Your mail username:</label><input type="text" name="username" value="'.get_option('mailserver_login').'"/></li><li><label>Your mail password:</label><input type="text" name="password" value="'.get_option('mailserver_password').'"/></li><li><label>Your mail host port:</label><input type="text" name="port" value="'.get_option('mailserver_port').'"/></li></ul>';
}   
function church_admin_sms_settings_form(){    
    echo'<ul>   
    <li><h2>SMS Settings</h2></li><li><label>Enable Bulk sms?</label><input type="checkbox" name="sms"/></li><li>Set up an account with <a href="www.bulksms.co.uk">www.bulksms.co.uk</a> - prices start at 3.9 per sms</li><li>Once you have registered fill out the form below</li><li><label >SMS username</label><input type="text" name="sms_username" value="'.get_option('church_admin_sms_username').'" /></li><li><label>SMS password</label><input type="text" name="sms_password" value="'.get_option('church_admin_sms_password').'" /></li><li><label >SMS reply eg:447777123456</label><input type="text" name="sms_reply" value="'.get_option('church_admin_sms_reply').'" /></li><li><label >Country code eg 44</label><input type="text" name="sms_iso" value="'.get_option('church_admin_sms_iso').'" /></li></ul>';
}

function church_admin_cron_job_instructions()
{
    //setup pdf
    require("fpdf.php");
    $pdf=new FPDF();
    $pdf->AddPage('P','mm','A4');
    $pdf->SetFont('Arial','B',24);
    $text='How to set up Bulk Email Queuing';
    $pdf->Cell(0,10,$text,0,2,L);
    if (PHP_OS=='Linux')
    {
    $phppath='/usr/local/bin/php -f';
    $cronpath=CHURCH_ADMIN_INCLUDE_PATH.'cronemail.php';
    $command=$phppath.$cronpath;
    
    
    $pdf->SetFont('Arial','',10);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
    $pdf->MultiCell(0, 10, $text );
 
    $pdf->Image(CHURCH_ADMIN_IMAGES_PATH.'cron-job1.jpg','10','65','','','jpg','');
    $pdf->SetXY(10,180);
    $text="In the common settings option - select 'Once an Hour'. \r\nIn 'Command to run' put this:\r\n".$command."\r\n and then click Add Cron Job. Job Done. Don't forget to test it by sending an email to yourself at a few minutes before the hour! ";
    $pdf->MultiCell(0, 10, $text );
    }
    else
    {
         $pdf->SetFont('Arial','',10);
        $text="Unfortunately setting up queuing for email using cron is not possible in Windows servers. Please go back to Communication settings and enable the wp-cron option for scheduling sending of queued emails";
        $pdf->MultiCell(0, 10, $text );
    }
    $pdf->Output(CHURCH_ADMIN_CACHE_PATH.'bulk_email_queuing.pdf',F);
    echo '<a href="'.CHURCH_ADMIN_CACHE_URL.'bulk_email_queuing.pdf">PDF instructions</a>';
}
?>