<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
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
		echo'<p><a href="'.site_url().'/?download=cron-instructions&amp;cron-instructions='.wp_create_nonce('cron-instructions').'">'.__('PDF Instructions for email cron setup','church-admin').'</a></td></tr>';
		break;
	    default:
	       wp_clear_scheduled_hook('church_admin_bulk_email');
	       update_option('church_admin_cron','immediate');
	      
	    break;
	 }
	 //admin levels
	
	 foreach($levels AS $key=>$value)
	 {
	    if(!empty($_POST['level'.$key])&&array_key_exists($_POST['level'.$key],$available_levels))$levels[$key]=$_POST['level'.$key];
	    
	 }
	 update_option('church_admin_levels',$levels);
	 
	 
	 
	 unset($_POST);
	 echo'<div class="updated fade"><p>'.__('Settings Updated','church-admin').'</td></tr></div>';
	 echo'<p><a href="'.site_url().'/?download=cron-instructions&amp;cron-instructions='.wp_create_nonce('cron-instructions').'&amp;">'.__('PDF Instructions for email cron setup','church-admin').'</a></td></tr>';
	 church_admin_settings();
	
   }
   else
   {
      echo'<form action="" method="POST">';
      echo'<h2>'.__('General Settings','church-admin').'</h2>';
      echo'<table class="form-table"><tr><th scope="row">'.__('Directory Records per page','church-admin').'</th><td><input type="text" name="church_admin_page_limit" value="'.get_option('church_admin_page_limit').'"/></td></tr>';
      echo '<tr><th scope="row">'.__('Calendar width in pixels','church-admin').'</th><td><input type="text" name="church_admin_calendar_width" value="'.get_option('church_admin_calendar_width').'"/></td></tr>';
	echo '<tr><th scope="row">'.__('PDF Page Size','church-admin').'</th><td><select name="church_admin_pdf_size">';
	if(get_option('church_admin_pdf_size')=='Letter')
	{echo '<option value="">Letter</option><option value="A4">A4</option><option value="Legal">Legal</option>';}
	elseif(get_option('church_admin_pdf_size')=='Legal')
	{echo '<option value="Legal">Legal</option><option value="A4">A4</option><option value="Letter">Letter</option>';}
	else
	{echo '<option value="A4">A4</option><option value="Legal">Legal</option><option value="Letter">Letter</option>';}
	echo'</select></td></tr>';
	//email template top image
	echo'<tr><th scope="row">'.__('Email template header image url','church-admin').'</th><td><input type="text" name="church_admin_email_image" value="'.get_option('church_admin_email_image').'"/></td></tr>';
	//end email template top image
	echo'<tr><th scope="row">'.__('Facebook page URL','church-admin').'</th><td><input type="text" name="church_admin_facebook" value="'.get_option('church_admin_facebook').'"/></td></tr>';
	echo'<tr><th scope="row">'.__('Twitter Username','church-admin').'</th><td><input type="text" name="church_admin_twitter" value="'.get_option('church_admin_twitter').'"/></td></tr>';
	echo'<tr><th scope="row">'.__('Feedburner uri','church-admin').'</th><td><input type="text" name="church_admin_feedburner" value="'.get_option('church_admin_feedburner').'"/></td></tr>';
	//mailing label size
	echo '<tr><th scope="row">Avery &#174; Label</th><td><select name="church_admin_label">';

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
	echo'>3422</option></select></td></tr>';
	echo'<h2>'.__('Permissions','church-admin').'</h2>';
	echo'<p>You can either set individuals  or allow roles like admin/editor/subscriber to have permission for various tasks</td></tr>';
	echo'<p><a href="admin.php?page=church_admin_permissions">Set individual permissions</a></td></tr>';
	echo '<h2>'.__('Set Levels for plugin functions','church-admin').'</h2>';
	foreach($levels AS $key=>$value)
	{
	    echo'<tr><th scope="row">'.$key.'</th><td><select name="level'.$key.'">';
	    echo'<option value="'.$value.'" selected="selected">'.$value.'</option>';
	    foreach($available_levels AS $avail_key=>$avail_value)echo'<option value="'.$avail_key.'">'.$avail_key.'</option>';
	    echo'</select></td></tr>';
	 }
	 echo'</tbody></table>';
	echo'<h2>'.__('Email Settings','church-admin').'</h2>';
	
	echo'<p>'.__('Emails can be sent immediately or in batches. If you are on a shared host, many hosts limit how many email you are allowed to send an hour. Typically that can be as little as 100. Please choose the appropriate option for your setup.','church-admin').'</td></tr>';
        echo'<form action="" method="post">';
	echo'<table class="form-table"><tr><th scope="row">'.__('Send Emails Immediately','church-admin').'</th><td><input type="radio" name="cron" value="immediate" ';
	if (get_option('church_admin_cron')=='immediate') echo 'checked="checked"';
	echo'/></td></tr><p> '.__('Or if on a share host, please setup queueing below','church-admin').'...';
	echo '<tr><th scope="row">'.__('Max emails per hour? (required)','church-admin').'</th><td><input type="text" name="quantity" value="'.get_option('church_admin_bulk_email').'"/></td></tr><p> There are two ways to set up sending emails in batches</td></tr><p><strong>Using cron</strong> the best method if you are on a Linux based host. The server checks and sends any emails queued every hour in batches set by you.</td></tr><p><strong>Using wp_cron</strong> - wp-cron works by scheduling every hour, but relies on people visiting your site regularly to do it in the background. (only option on windows hosts!)</td></tr><tr><th scope="row">I want to use cron:</th><td><input type="radio" name="cron" value="cron" ';
        if (get_option('church_admin_cron')=='cron') echo 'checked="checked"';
        echo'/></td></tr><tr><th scope="row">'.__('I want to use wp-cron:','church-admin').'</th><td><input type="radio" name="cron" value="wp-cron"';
        if (get_option('church_admin_cron')=='wp-cron') echo 'checked="checked"';
        echo'/></td></tr>';
	echo'<p>'.__('Use wordpress email function (leave settings below blank) or set up an smtp server','church-admin').'...</td></tr>';
	
	echo '<tr><th scope="row">'.__('SMTP host','church-admin').'</th><td><input type="text" name="smtp_host" value="'.$email_settings['host'].'"/></td></tr>';
        echo '<tr><th scope="row">'.__('SMTP username','church-admin').'</th><td><input type="text" name="smtp_username" value="'.$email_settings['username'].'"/></td></tr>';
        echo '<tr><th scope="row">'.__('SMTP password','church-admin').'</th><td><input type="text" name="smtp_password" value="'.$email_settings['password'].'"/></td></tr>';
	echo '<tr><th scope="row">'.__('SMTP port','church-admin').'</th><td><input type="text" name="smtp_port" value="'.$email_settings['port'].'"/></td></tr>';
        echo '<tr><th scope="row">'.__('SMTP ssl?','church-admin').'</th><td>Yes<input type="checkbox" name="smtp_ssl" value="yes" /></td></tr>';
	echo'</tbody></table>';
        
        
	echo'<h2>'.__('Bulk SMS Settings','church-admin').'</h2>';
	echo'<p>'.__('Set up an account with','church-admin').' <a href="http://community.bulksms.co.uk">http://community.bulksms.co.uk</a>'.__('Prices start at 3.9 per sms','church-admin').'</td></tr>';
	echo'<p>'.__('Once you have registered fill out the form below','church-admin').'</td></tr>';
	$sms_type=get_option('church_admin_bulksms');
	
	echo'<table class="form-table><tbody><tr><th scope="row">Which bulksms.co.uk account type are you using?</th><td><input type="radio" name="sms_type" value="community"';
	if(empty($sms_type)||$sms_type=='http://community.bulksms.co.uk') echo' checked="checked" ';
	echo'/>Community or <input type="radio" name="sms_type" value="normal"';
	if($sms_type=='http://bulksms.co.uk') echo' checked="checked" ';
	echo' />Normal';
	echo'<tr><th scope="row" >'.__('SMS username','church-admin').'</th><td><input type="text" name="sms_username" value="'.get_option('church_admin_sms_username').'" /></td></tr>';
	echo'<tr><th scope="row">'.__('SMS password','church-admin').'</th><td><input type="text" name="sms_password" value="'.get_option('church_admin_sms_password').'" /></td></tr>';
	echo'<tr><th scope="row" >'.__('SMS reply eg:447777123456','church-admin').'</th><td><input type="text" name="sms_reply" value="'.get_option('church_admin_sms_reply').'" /></td></tr>';
	echo'<tr><th scope="row" >'.__('Country code eg 44','church-admin').'</th><td><input type="text" name="sms_iso" value="'.get_option('church_admin_sms_iso').'" /></td></tr>';
   echo'<tr><th scope="row" >&nbsp;</th><td><input type="hidden" name="save setting" value="1"/><input class="button-settings" type="submit"  value="'.__('Save Settings','church-admin').' &raquo;" /></td></tr></tbody></table></form>';
  
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
    $cronpath=plugin_dir_path(dirname(__FILE__)).'/includes/cronemail.php';
    $command=$phppath.' -f '.$cronpath;
    
    
    $pdf->SetFont('Arial','',10);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel  or ".get_bloginfo('url').":2082 using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
    $pdf->MultiCell(0, 10, $text );
 
    $pdf->Image(plugin_dir_path( dirname(__FILE__) ).'images/cron-job1.jpg','10','65','','','jpg','');
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
	$upload_dir = wp_upload_dir();
    $pdf->Output($upload_dir['basedir'].'church-admin-cache/bulk_email_queuing.pdf',F);
    echo '<a href="'.$upload_dir['baseurl'].'/church-admin-cache/'.'bulk_email_queuing.pdf">PDF instructions</a>';
}
?>
