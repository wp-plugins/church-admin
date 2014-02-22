<?php
/*
 This file contains the communications settings
 2011-03-05 Removed create cronemail.php - now permanently there
*/

function church_admin_settings()
{
    global $wpdb;
    $levels=get_option('church_admin_levels');
   $available_levels=get_option($wpdb->prefix.'user_roles');
   $email_settings=get_option('church_admin_smtp');
   echo'<h2>'.__('Church Admin Plugin Settings','church-admin').'</h2>';
      echo'<div class="wrap church_admin">';
   if(!empty($_POST['save_setting']))
   {
      switch($_POST['sms_type'])
	  {
		case'community':update_option('church_admin_bulksms','http://community.bulksms.co.uk');break;
		case'normal':update_option('church_admin_bulksms','http://bulksms.co.uk');break;
	  }
      if(isset($_POST['church_admin_page_limit']))update_option('church_admin_page_limit',$_POST['church_admin_page_limit']);
      if(isset($_POST['church_admin_facebook']))update_option('church_admin_facebook',$_POST['church_admin_facebook']);
      if(isset($_POST['church_admin_twitter']))update_option('church_admin_twitter',$_POST['church_admin_twitter']);
	  if(isset($_POST['church_admin_feedburner']))update_option('church_admin_feedburner',$_POST['church_admin_feedburner']);
      if(isset($_POST['church_admin_calendar_width']) && ctype_digit($_POST['church_admin_calendar_width']))update_option('church_admin_calendar_width',$_POST['church_admin_calendar_width']);	
      if(isset($_POST['church_admin_pdf_size']) && ($_POST['church_admin_pdf_size']=='Legal'||$_POST['church_admin_pdf_size']=='Letter'||$_POST['church_admin_pdf_size']=='A4'))
	{
	 update_option('church_admin_pdf_size',$_POST['church_admin_pdf_size']);

	}
	if(isset($_POST['church_admin_email_image']))
	{
	    update_option('church_admin_email_image',$_POST['church_admin_email_image']);   
	  
	}
	if(isset($_POST['church_admin_label']))
	{
	 switch($_POST['church_admin_label'])
	 {
	    case 'L7163': $option='L7163';break;
	    case '5160': $option='5160';break;
	    case '5161': $option='5161';break;
	    case '5162': $option='5162';break;
	    case '5163': $option='5163';break;
	    case '5164': $option='5164';break;
	    case '8600': $option='8600';break;
	    case '3422': $option='3422';break;
	    default :$option='L7163';break;
	 }
	 update_option('church_admin_label',$option);
	
	}
	 update_option('church_admin_sms_username',$_POST['sms_username']);
         update_option('church_admin_sms_password',$_POST['sms_password']);
         update_option('church_admin_sms_reply',$_POST['sms_reply']);
         update_option('church_admin_sms_iso',$_POST['sms_iso']);
	 //email
	 $smtp_host=stripslashes($_POST['smtp_host']);
	 $smtp_username=stripslashes($_POST['smtp_username']);
	 $smtp_password=stripslashes($_POST['smtp_password']);
	 $smtp_port=stripslashes($_POST['smtp_port']);
	 if(!empty($_POST['smtp_ssl'])){$smtp_ssl=TRUE;}else{$smtp_ssl=FALSE;}
	 $email_settings=array('host'=>$smtp_host,'username'=>$smtp_username,'password'=>$smtp_password,'ssl'=>$smtp_ssl,'port'=>$smtp_port);
	 if(empty($smtp_username)){delete_option('church_admin_smtp',$email_settings);}else{update_option('church_admin_smtp',$email_settings);}
	 update_option('church_admin_bulk_email',$_POST['quantity']);
	 update_option('church_admin_cron',$_POST['cron']);
	 switch($_POST['cron'])
	 {
	    case'wp-cron':
		add_action('church_admin_bulk_email','church_admin_cron');
		$timestamp=mktime();
		wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
		
	    break;
	    case 'cron':
		wp_clear_scheduled_hook('church_admin_bulk_email');
		echo'<p><a href="'.site_url().'/?download=cron-instructions">'.__('PDF Instructions for email cron setup','church-admin').'</a></p>';
		break;
	    default:
	       wp_clear_scheduled_hook('church_admin_bulk_email');
	       update_option('church_admin_cron','immediate');
	      
	    break;
	 }
	 //admin levels
	
	 foreach($levels AS $key=>$value)
	 {
	    if(array_key_exists($_POST['level'.$key],$available_levels))$levels[$key]=$_POST['level'.$key];
	    
	 }
	 update_option('church_admin_levels',$levels);
	 
	 
	 
	 unset($_POST);
	 echo'<div class="updated fade"><p>'.__('Settings Updated','church-admin').'</p></div>';
	 echo'<p><a href="'.site_url().'/?download=cron-instructions">'.__('PDF Instructions for email cron setup','church-admin').'</a></p>';
	 church_admin_settings();
	 echo'</div>';
   }
   else
   {
      echo'<form action="" method="POST">';
      echo'<h3>'.__('General Settings','church-admin').'</h3>';
      echo'<p><label>'.__('Directory Records per page','church-admin').'</label><input type="text" name="church_admin_page_limit" value="'.get_option('church_admin_page_limit').'"/></p>';
      echo '<p><label>'.__('Calendar width in pixels','church-admin').'</label><input type="text" name="church_admin_calendar_width" value="'.get_option('church_admin_calendar_width').'"/></p>';
	echo '<p><label>'.__('PDF Page Size','church-admin').'</label><select name="church_admin_pdf_size">';
	if(get_option('church_admin_pdf_size')=='Letter')
	{echo '<option value="">Letter</option><option value="A4">A4</option><option value="Legal">Legal</option>';}
	elseif(get_option('church_admin_pdf_size')=='Legal')
	{echo '<option value="Legal">Legal</option><option value="A4">A4</option><option value="Letter">Letter</option>';}
	else
	{echo '<option value="A4">A4</option><option value="Legal">Legal</option><option value="Letter">Letter</option>';}
	echo'</select></p>';
	//email template top image
	echo'<p><label>'.__('Email template header image url','church-admin').'</label><input type="text" name="church_admin_email_image" value="'.get_option('church_admin_email_image').'"/></p>';
	//end email template top image
	echo'<p><label>'.__('Facebook page URL','church-admin').'</label><input type="text" name="church_admin_facebook" value="'.get_option('church_admin_facebook').'"/></p>';
	echo'<p><label>'.__('Twitter Username','church-admin').'</label><input type="text" name="church_admin_twitter" value="'.get_option('church_admin_twitter').'"/></p>';
	echo'<p><label>'.__('Feedburner uri','church-admin').'</label><input type="text" name="church_admin_feedburner" value="'.get_option('church_admin_feedburner').'"/></p>';
	//mailing label size
	echo '<p><label>Avery &#174; Label</label><select name="church_admin_label">';

	$l=get_option('church_admin_label');
	echo'<option value="L7163"';
	if($l=='L7163') echo' selected="selected" ';
	echo'>L7163</option>';
	echo'<option value="5160"';
	if($l=='5160') echo' selected="selected" ';
	echo'>5160</option>';
	echo'<option value="5161';
	if($l=='5161') echo' selected="selected" ';
	echo'>5161</option>';
	echo'<option value="5162"';
	if($l=='5162') echo' selected="selected" ';
	echo'>5162</option>';
	echo'<option value="5163"';
	if($l=='5163') echo' selected="selected" ';
	echo'>5163</option>';
	echo'<option value="5164"';
	if($l=='5164') echo' selected="selected" ';
	echo'>5164</option>';
	echo'<option value="8600"';
	if($l=='8600') echo' selected="selected" ';
	echo'>8600</option>';
	echo'<option value="3422"';
	if($l=='3422') echo' selected="selected" ';
	echo'>3422</option></select></p>';
	
	echo '<h2>'.__('Set Levels for plugin functions','church-admin').'</h2>';
	foreach($levels AS $key=>$value)
	{
	    echo'<p><label>'.$key.'</label><select name="level'.$key.'">';
	    echo'<option value="'.$value.'" selected="selected">'.$value.'</option>';
	    foreach($available_levels AS $avail_key=>$avail_value)echo'<option value="'.$avail_key.'">'.$avail_key.'</option>';
	    echo'</select></p>';
	 }
	echo'<h3>'.__('Email Settings','church-admin').'</h3>';
	
	echo'<p>'.__('Emails can be sent immediately or in batches. If you are on a shared host, many hosts limit how many email you are allowed to send an hour. Typically that can be as little as 100. Please choose the appropriate option for your setup.','church-admin').'</p>';
        echo'<form action="" method="post">';
	echo'<p><label>'.__('Send Emails Immediately','church-admin').'</label><input type="radio" name="cron" value="immediate" ';
	if (get_option('church_admin_cron')=='immediate') echo 'checked="checked"';
	echo'/></p><p> '.__('Or if on a share host, please setup queueing below','church-admin').'...';
	echo '<p><label>'.__('Max emails per hour? (required)','church-admin').'</label><input type="text" name="quantity" value="'.get_option('church_admin_bulk_email').'"/></p><p> There are two ways to set up sending emails in batches</p><p><strong>Using cron</strong> the best method if you are on a Linux based host. The server checks and sends any emails queued every hour in batches set by you.</p><p><strong>Using wp_cron</strong> - wp-cron works by scheduling every hour, but relies on people visiting your site regularly to do it in the background. (only option on windows hosts!)</p><p><label>I want to use cron:</label><input type="radio" name="cron" value="cron" ';
        if (get_option('church_admin_cron')=='cron') echo 'checked="checked"';
        echo'/></p><p><label>'.__('I want to use wp-cron:','church-admin').'</label><input type="radio" name="cron" value="wp-cron"';
        if (get_option('church_admin_cron')=='wp-cron') echo 'checked="checked"';
        echo'/></p>';
	echo'<p>'.__('Use wordpress email function (leave settings below blank) or set up an smtp server','church-admin').'...</p>';
	
	echo '<p><label>'.__('SMTP host','church-admin').'</label><input type="text" name="smtp_host" value="'.$email_settings['host'].'"/></p>';
        echo '<p><label>'.__('SMTP username','church-admin').'</label><input type="text" name="smtp_username" value="'.$email_settings['username'].'"/></p>';
        echo '<p><label>'.__('SMTP password','church-admin').'</label><input type="text" name="smtp_password" value="'.$email_settings['password'].'"/></p>';
	echo '<p><label>'.__('SMTP port','church-admin').'</label><input type="text" name="smtp_port" value="'.$email_settings['port'].'"/></p>';
        echo '<p><label>'.__('SMTP ssl?','church-admin').'</label>Yes<input type="checkbox" name="smtp_ssl" value="yes" /></p>';
	
        
        
	echo'<h3>'.__('Bulk SMS Settings','church-admin').'</h3>';
	echo'<p>'.__('Set up an account with','church-admin').' <a href="http://community.bulksms.co.uk">http://community.bulksms.co.uk</a>'.__('Prices start at 3.9 per sms','church-admin').'</p>';
	echo'<p>'.__('Once you have registered fill out the form below','church-admin').'</p>';
	$sms_type=get_option('church_admin_bulksms');
	
	echo'<p><label>Which bulksms.co.uk account type are you using?</label><input type="radio" name="sms_type" value="community"';
	if(empty($sms_type)||$sms_type=='http://community.bulksms.co.uk') echo' checked="checked" ';
	echo'/>Community or <input type="radio" name="sms_type" value="normal"';
	if($sms_type=='http://bulksms.co.uk') echo' checked="checked" ';
	echo' />Normal';
	echo'<p><label >'.__('SMS username','church-admin').'</label><input type="text" name="sms_username" value="'.get_option('church_admin_sms_username').'" /></p>';
	echo'<p><label>'.__('SMS password','church-admin').'</label><input type="text" name="sms_password" value="'.get_option('church_admin_sms_password').'" /></p>';
	echo'<p><label >'.__('SMS reply eg:447777123456','church-admin').'</label><input type="text" name="sms_reply" value="'.get_option('church_admin_sms_reply').'" /></p>';
	echo'<p><label >'.__('Country code eg 44','church-admin').'</label><input type="text" name="sms_iso" value="'.get_option('church_admin_sms_iso').'" /></p>';
   echo'<p class="submit"><input type="hidden" name="save setting" value="1"/><input type="submit"  value="'.__('Save Settings','church-admin').' &raquo;" /></p></form></div>';
  
   }
   
   
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
    $phppath='/usr/local/bin/php';
    $cronpath=CHURCH_ADMIN_INCLUDE_PATH.'cronemail.php';
    $command=$phppath.' -f '.$cronpath;
    
    
    $pdf->SetFont('Arial','',10);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel  or ".get_bloginfo('url').":2082 using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
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
