<?php
if(!defined(DB_NAME)){$output=1;}else{$output=0;}

//cron needs the current working directory setting
chdir(dirname(__FILE__));
//work back to the root directory in anormal wp installation
$file='../../../../wp-config.php';
require($file);
$attachment=array();
// connect to database
$db=mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
mysql_select_db(DB_NAME);

//grab emails per hour
$sql="SELECT option_value FROM ".$table_prefix."options WHERE option_name='church_admin_bulk_email'";

$result=mysql_query($sql);
$row=mysql_fetch_assoc($result);
if(!empty($row['option_value'])){$max_email=$row['option_value'];}else{$max_email=100;}
if($output==1)echo '<p>Attempting to send '.$max_email.' emails on this run</p>';
//initialise phpmailer script
require("class.phpmailer.php");
$mail = new PHPMailer();
//Grab messages
$sql="SELECT * FROM ".$table_prefix."church_admin_email ORDER BY email_id LIMIT 0,".$max_email;

$result=mysql_query($sql);
if(mysql_num_rows($result)>0)
{//only proceed if emails queued in db 
    while($row=mysql_fetch_assoc($result))
    {
        $mail->From     = $row['from_email'];
        $mail->FromName = "{$row['from_name']}";
        
        $mail->IsHTML(true); 
        $mail->AddAddress($row['recipient']);
        if(!empty($row['copy']))$mail->AddAddress($row['copy']);
        if(!empty($row['attachment']))
        {
            $attachments=unserialize($row['attachment']);
            foreach($attachments AS $key=>$path)
                {
                    $mail->AddAttachment($path, $name = "", $encoding = "base64",$type = "application/octet-stream");
                }
           
        }
        $mail->Subject = $row['subject'] ;
        $mail->Body=$row['message'];
        if($mail->Send())
            {
                //successful send, so delete from DB
                $sql="DELETE FROM ".$table_prefix."church_admin_email WHERE email_id='".mysql_real_escape_string($row['email_id'])."'";

                mysql_query($sql)or die(mysql_error());
                if($output==1)echo'<p>Email sent to '.$row['recipient'].'</p>';
            }else{if($output==1)echo'<p>Email NOT sent to '.$row['recipient'].'</p>';}
        if($output==1)echo     $mail->ErrorInfo;
        $mail->ClearAllRecipients();//clears all recipients
        $mail->ClearCustomHeaders();//clears headers for next message
    }
}else{if($output==1)echo'<p>No emails in queue</p>';}


?>