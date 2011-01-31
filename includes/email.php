<?php

function church_admin_send_email()
{
global $wpdb;
$wpdb->show_errors();
 
if(!empty($_POST['content'])&&!empty($_POST['subject'])&& check_admin_referer('send email')&&current_user_can('manage_options'))
{
    echo"<h2>Message is being queued</h2>";
    
    //email address from
    $from_name=!empty($_POST['from_name'])?$_POST['from_name']:get_option('blogname');
    $from_email=!empty($_POST['from_email'])?$_POST['from_email']:get_option('admin_email');    
    //handle attachment
   
    if  ($_FILES['userfile']['error']==0)
    {
        $attachment = CHURCH_ADMIN_TEMP_PATH.$_FILES['userfile']['name'];

        $tmpName  = $_FILES['userfile']['tmp_name'];
        move_uploaded_file($tmpName, $attachment) or die("Couldn't move file");
    }
    if($_FILES['userfile']['error']==2)exit("The uploaded file was too big");
    $subject=stripslashes($_POST['subject']);
    $message=stripslashes(mb_convert_encoding(nl2br($_POST['content']), 'HTML-ENTITIES', 'UTF-8'));//sorts out MS WORD pasted gobbledegook
    //grab addresses
    $addresses=array();
    $result=$wpdb->get_results("SELECT DISTINCT email FROM ".$wpdb->prefix."church_admin_directory WHERE email!=''");
    foreach($result AS $add)
    {
        $addresses[]=$add->email;
    }
     $result=$wpdb->get_results("SELECT DISTINCT email2 FROM ".$wpdb->prefix."church_admin_directory WHERE email2!=''");
    foreach($result AS $add)
    {
        $addresses[]=$add->email2;
    }
    echo "<p>Subject: $subject<br/>Attachment: $attachment<br/>Message:$message</p>";
    if(!empty($addresses))
    {
        //we have addresses to send to!
        foreach($addresses AS $recipient)
        {
            if(QueueEmail($recipient,$subject,$message,$copy,$from_name,$from_email,$attachment)) {echo "<p>$recipient added</p>";}
        }
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Message queued </p>';
        echo '</div>';
    }
    else
    {
        echo "No email addresses found!";
    }
//no cron job set up    
if((get_option('church_admin_bulk_email'))){echo '<div id="message" class="updated fade"><p>Message will be sent from the queue in batches of '.get_option('church_admin_bulk_email').' soon.</p></div>';}else{require(CHURCH_ADMIN_INCLUDE_PATH.'cronemail.php');echo '<div id="message" class="updated fade"><p>Message Sent</p></div>';}
}
else
{
    //form not yet submitted
    echo'<div class="wrap"><h2>Send email</h2><form action="" enctype="multipart/form-data" method="post" ><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
    if ( function_exists('wp_nonce_field') ) wp_nonce_field('send email');
    echo'<ul><li><label for="subject">Subject:</label><input type="text" name="subject" id="subject" maxlength="45" size="100"/></li><li><label for="reply_name">Reply to name:</label><input id="reply_name" type="text" name="from_name"/></li><li><label for="reply_email">Email address for replies:</label><input id="reply_email" type="text" name="from_email"/></li><li><label for="attachment">Attachment (max 1MB):</label><input id="attachment" type="file" name="userfile"/></li></ul>';
    echo'<h2>The Message</h2>';
    echo'<div id="poststuff">';
    the_editor("", "content", "", true);
    echo'</div>';
    echo'<p><input type="submit" class="secondary-button" value="Send Email"/></div>';    
    }
    
}
?>