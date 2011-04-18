<?php
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
if(mysql_num_rows($result)==0)exit("no result");
$row=mysql_fetch_assoc($result);
$max_email=$row['option_value'];
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
         $path=$row['attachment'];
         
            $mail->AddAttachment($path, $name = "", $encoding = "base64",$type = "application/octet-stream");
            $attachment[]=$path;
        }
        $mail->Subject = $row['subject'] ;
        $mail->Body=$row['message'];
        if($mail->Send())
            {
                //successful send, so delete from DB
                $sql="DELETE FROM ".$table_prefix."church_admin_email WHERE email_id='".mysql_real_escape_string($row['email_id'])."'";

                mysql_query($sql)or die(mysql_error());
            }
        echo     $mail->ErrorInfo;
        $mail->ClearAllRecipients();//clears all recipients
        $mail->ClearCustomHeaders();//clears headers for next message
    }
}


?>