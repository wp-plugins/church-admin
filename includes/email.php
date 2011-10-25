<?php

function church_admin_send_email()
{
   //this is the main function for sending emails out
   //step1 - build email fochurch_admin
   //step2 - display email - church_admin_email_build() and show recipient choosing fochurch_admin church_admin_choose_recipients()
   //step3 - queue personalised emails to the correct recipients.

   if(!empty($_POST['send_email']))
   {
    echo '<p>Processing</p>';
        church_admin_send_message($_POST['email_id']);
   }
   elseif(!empty($_POST['build_message']))
    {
        echo'<div class="wrap church_admin"><h2>Step 2 check message and choose recipients</h2>';
        $email_id=church_admin_email_build();
        church_admin_choose_recipients($email_id);
    }
    elseif(!empty($_POST['choose_recipients']))
    {
        church_admin_send_message($_POST['message_id']);
    }
    elseif(empty($_POST))
    {
        church_admin_email_form();
    }
}

function church_admin_email_form()
{
    global $wpdb;
    //This function displays a form to build the email message
    echo'<div class="wrap church_admin"><h2>Bulk Email</h2>';
    echo'<h2>Step 1 Build Email Message</h2>';
    //set up form
    echo'<form action="" enctype="multipart/form-data" method="post" >';
    echo'<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
    echo'<input type="hidden" name="build_message" value="1"/>';
    echo'<p><label for="subject"><strong>Subject:</strong></label><input type="text" name="subject" id="subject" maxlength="100" size="100"/></p>
    <p><label for="reply_name"><strong>Reply to name:</strong></label><input id="reply_name" type="text" name="from_name"/></p>
    <p><label for="reply_email"><strong>Email address for replies:</strong></label><input id="reply_email" type="text" name="from_email"/></p> 
    <p><label>Attachment 1 (max 500KB):</label>
			<input type="file" name="userfile1"/>
	</p>
	<p><label>Attachment 2 (max 500KB):</label>
			<input type="file" name="userfile2"/>
	</p>
	<p><label>Attachment 3 (max 500KB):</label>
			<input type="file" name="userfile3"/>
	</p>	';
        if ( function_exists('wp_nonce_field') ) wp_nonce_field('church_admin_events_send_email');
    
    echo'<p> All emails are individually addressed.</p>';
    echo'<div id="poststuff">';
    the_editor('','message',"", true);
    echo'</div>';
    //latest news
    $last_month=date('Y-m-d H:i:s',strtotime('-8 weeks'));
    $sql='SELECT post_title, post_date, post_author,ID FROM '.$wpdb->posts.' WHERE post_date >= "'.$last_month.'" AND post_status IN ("publish") AND post_type IN ("post") ORDER BY post_date DESC';
    $posts=$wpdb->get_results($sql);
    if($posts)
    {
        echo '<p><strong> Choose Latest News posts to add</strong></p>';
        foreach($posts AS $post)
        {
            echo '<p><input type="checkbox" name="post[]" value="'.$post->ID.'"/>'.$post->post_title.' published '.mysql2date('d/m/Y',$post->post_date).'</p>';
        }
    }
   
    echo'<p><input type="submit" class="secondary-button" value="Save Email"/>';
    echo'</form></div>';

}
function church_admin_email_build()
{
    global $wpdb;
    $wpdb->show_errors();
    //this function builds a cached version of the email using the template
    //it returns the email_id from db
    $sqlsafe=array(
                    'subject'=>esc_sql(stripslashes($_POST['subject'])),
                    'news'=>esc_sql(maybe_serialize($_POST['news'])),
                    'events'=>esc_sql(maybe_serialize($_POST['events'])),
                    'from_email'=>esc_sql(stripslashes($_POST['from_email'])),
                    'from_name'=>esc_sql(stripslashes($_POST['from_name']))
                    );
 
    //Build Email
    //handle attachments
if  ($_FILES['userfile1']['size']>0)
{
    $attachments['1'] = CHURCH_ADMIN_CACHE_PATH.$_FILES['userfile1']['name'];
  
    $tmpName  = $_FILES['userfile1']['tmp_name'];
    move_uploaded_file($tmpName,$attachments['1']);
}
if  ($_FILES['userfile2']['size']>0)
{
    $attachments[2] = CHURCH_ADMIN_CACHE_PATH.$_FILES['userfile2']['name'];
     
    $tmpName  = $_FILES['userfile2']['tmp_name'];
    move_uploaded_file($tmpName, $attachments[2]);
}
if  ($_FILES['userfile3']['size']>0)
{
    $attachments[] = CHURCH_ADMIN_CACHE_PATH.$_FILES['userfile3']['name'];
    
    $tmpName  = $_FILES['userfile3']['tmp_name'];
    move_uploaded_file($tmpName, $attachments[3]);
}

    
    //handle latest news
    if(count($_POST['post'])>0)
    {//latest news sections
        $post_section='<table>';
        //handle posts
        $post=$event=array();
        foreach($_POST['post'] AS $post_id)
        {
            $sql1='SELECT post_title,post_content,post_author,post_date,ID FROM '.$wpdb->posts.' WHERE ID="'.esc_sql($post_id).'"';
            $row=$wpdb->get_row($sql1);
            $excerpt = strip_only(strip_shortcodes($row->post_content),'<img>');
            $words = explode(' ', $excerpt, 51);
            if(count($words)==51)$words[50]='<a href="'.get_permalink($row->ID).'">&laquo; Read More &raquo;</a>';
            
            
            $post_excerpt = implode(' ', $words);
            $post_section.='<tr><td>';
            if (function_exists(get_the_post_thumbnail)&& get_the_post_thumbnail( $row->ID, 'email-thumb')!='')
            {
                $post_section.= get_the_post_thumbnail( $row->ID, 'email-thumb');
            }
            else
            {
                $post_section.='<img src="http://dummyimage.com/300x200/000/fff.jpg&text='.str_replace(' ', '+', $row->post_title).'" class="apc_thumb" title="'.$row->post_title.'" alt="'. $row->post_title.'" width="300" height="200">';
            }
            $post_section.='</td><td style="vertical-align:top;"><h2 style="margin-top:25px;"><a href="'.get_permalink($row->ID).'">'.$row->post_title.'</a></h2><p>'.strip_only(trim($post_excerpt),'<img>').'</p><p><a href="'.get_permalink($row->ID).'">Read the whole article here</a></p></td></tr>';
        }
        $post_section.='</table>';
    }//latest news section
    
   
    //grab template
    $message=file_get_contents(CHURCH_ADMIN_INCLUDE_PATH.'email_template.html');
    //add initial paragraph entered by user
    $entered_message=stripslashes(mb_convert_encoding(nl2br($_POST['message']), 'HTML-ENTITIES', 'UTF-8'));
    
    $message=str_replace('[intro]',$entered_message,$message);
     //sort image floating
    $message=str_replace('class="alignleft','style="float:left;margin:5px;" class="',$message);
    $message=str_replace('class="alignright','style="float:right;margin:5px;" class="',$message);
    $message=str_replace('class="aligncenter','style="  display: block;  margin-left: auto;  margin-right: auto;" class="',$message);
    
    //add subject
    $message=str_replace('[subject]',$_POST['subject'],$message);
    //add posts
    $message=str_replace('[posts]',$post_section,$message);
    //RSS URL
    $RSS='<a href="'.get_bloginfo('rss2_url' ).'" style="text_decoration:none" title="RSS"><img src="'.CHURCH_ADMIN_IMAGES_URL.'/rss.png" width="128" height="128"  style="border:none" alt="RSS Feed"/></a>';
     $message=str_replace('[RSS]',$RSS,$message);
    //twitter url
    if(get_option('church_admin_twitter')){$twitter='<a href="http://twitter.com/#!/'.get_option('church_admin_twitter').'" style="text_decoration:none" title="Follow us on Twitter"><img src="'.CHURCH_ADMIN_IMAGES_URL.'twitter.png" width="128" height="128"  style="border:none" alt="Contact"/></a> ';}else{$twitter='';}
    $message=str_replace('[TWITTER]',$twitter,$message);
    //facebook url
     if(get_option('church_admin_facebook')){$facebook='<a href="'.get_option('church_admin_facebook').'" style="text_decoration:none" title="Follow us on Facebook"><img src="'.CHURCH_ADMIN_IMAGES_URL.'facebook.png" width="128" height="128"  style="border:none" alt="Contact"/></a>';}else{$facebook='';}
    $message=str_replace('[FACEBOOK]',$facebook,$message);
    $message=str_replace('[BLOGINFO]','<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>',$message);
    $message=str_replace('[HEADER_IMAGE]','<img src="'.get_option('church_admin_email_image').'" alt="" >',$message);
    
    //copyright year
    $message=str_replace('[year]',date('Y'),$message);
    $filename='Email-'.date('Y-m-d-H-i-s').'.html';
    $message=str_replace('[cache]','<p style="font-size:smaller;text-align:center;margin:0 auto;">Having trouble reading this? - <a href="'.CHURCH_ADMIN_EMAIL_CACHE_URL.$filename.'">view in your web browser</a></p>',$message);
    //james contact details
    
    
    $handle=fopen(CHURCH_ADMIN_EMAIL_CACHE.$filename,"w")OR DIE("Couldn't open");
    fwrite($handle, $message);  
    fclose($handle);
    //write to database
    //add cache message

    $sqlsafe['message']=esc_sql($message);
    $email_id=$wpdb->get_var('SELECT email_id FROM wp_church_admin_email_build WHERE subject="'.$sqlsafe['subject'].'" AND message="'.$sqlsafe['message'].'" AND from_email="'.$sqlsafe['from_email'].'" AND from_name="'.$sqlsafe['from_name'].'" AND filename="'.esc_sql(maybe_serialize($attachments)).'"');
    if($email_id)
    {//update
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_email_build SET subject="'.$sqlsafe['subject'].'",message="'.$sqlsafe['message'].'", from_email="'.$sqlsafe['from_email'].'" ,from_name="'.$sqlsafe['from_name'].'",filename="'.esc_sql(maybe_serialize($attachments)).'" WHERE email_id="'.esc_sql($email_id).'"');
    }//end update
    else
    {//insert
        $sql='INSERT INTO '.$wpdb->prefix.'church_admin_email_build (subject,message,from_email,from_name,send_date,filename) VALUES("'.$sqlsafe['subject'].'","'.$sqlsafe['message'].'","'.$sqlsafe['from_email'].'","'.$sqlsafe['from_name'].'","'.date('Y-m-d').'","'.esc_sql(maybe_serialize($attachments)).'")';
        
        $wpdb->query($sql);
        $email_id=$wpdb->insert_id;
        echo "Email id is $email_id";
    }//insert    
    
    
    //output message to screen
    echo'<h3>This is how the message will look...</h3>';
    echo'<p>The email will have Dear First Name, when it is sent out!</p>';
    echo '<iframe width="700" border="1" height="450" src="'.CHURCH_ADMIN_EMAIL_CACHE_URL.$filename.'">Please upgrade your browser to display the email!</iframe>';
    echo'<h3>Use these social media buttons to post the email url...</h3><div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#appId=118790464849935&amp;xfbml=1"></script><fb:like href="'.CHURCH_ADMIN_EMAIL_CACHE_URL.$filename.'" send="false" width="450" show_faces="false" font="lucida grande"></fb:like></p>';
echo '<a href="https://twitter.com/share" class="twitter-share-button" data-count="none" data-text="'.$_POST['subject'].'" data-url="'.CHURCH_ADMIN_EMAIL_CACHE_URL.$filename.'">Tweet</a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script> '  ;
    
    if($email_id){return $email_id;}else{return FALSE;}
}

function church_admin_choose_recipients($email_id)
{
    global $wpdb;
    $wpdb->show_errors;
    //this function displays a form to select recipients
    echo'<h2>Now choose recipients...</h2><form action="" method="post"><input type="hidden" value="'.$email_id.'" name="email_id"/><input type="hidden" name="send_email" value="1"/>';
 
echo'<p><label><strong>Everyone</strong></label><input type="radio" name="type" value="everyone"/></p>';
echo'<p><label><strong>A Small group</strong></label><input type="radio" name="type" value="smallgroup"/></p>';
 echo'<fieldset id="smallgroup">';
echo'<p><label>Which group</label><select name="group_id">';
$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup');
foreach($results AS $row)
{
    echo'<option value="'.$row->id.'">'.$row->group_name.'</option>';
}
echo'</select></p></fieldset>';
echo'<p><label><strong>Choose individuals</strong></label><input type="radio" name="type" value="individuals"  /></p>';
    //choose individuals
    echo'<fieldset id="individuals">';
    echo '<div class="clonedInput" id="input1">';
    echo'<p><label>Select Person</label><select name="person[]" id="person1" class="person">';
    $results=$wpdb->get_results("SELECT CONCAT_WS(', ',last_name,first_name) AS name,id FROM ".$wpdb->prefix."church_admin_directory ORDER BY last_name");
    foreach($results AS $row)
    {
        echo '<option value="'.$row->id.'">'.$row->name.'</option>';
    }
    echo'</select></p></div>';
    echo'<p><input type="button" id="btnAdd" value="Add another person" /><input type="button" id="btnDel" value="Remove person" /></p></fieldset>';
        echo'<p><input type="submit" class="secondary-button" value="Send Email"/>';
    echo'</form></div>';
    //end choose individuals
//end of choose recipients
}

function church_admin_send_message($email_id)
{
    global $wpdb;
    $wpdb->show_errors();
    //this function sends the message cached in $_POST['filename'] out to the right recipients
    switch($_POST['type'])
    {
        //buidl $sql
        case 'everyone': $sql="SELECT first_name, email ,email2 FROM ".$wpdb->prefix."church_admin_directory";
        break;
        case 'smallgroup': $sql='SELECT first_name,email,email2 FROM '.$wpdb->prefix.'church_admin_directory WHERE small_group="'.esc_sql($_POST['group_id']).'"';
        break;
        
    case 'individuals':
            $names=array();
            foreach ($_POST['person']AS $value){$names[]='id = "'.esc_sql($value).'"';}
            $sql="SELECT first_name, email,email2 FROM ".$wpdb->prefix."church_admin_directory WHERE ".implode(' OR ',$names); break;
        break;
    
    }
        
    $results=$wpdb->get_results($sql);
    if($results)
    {
        $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_email_build WHERE email_id="'.esc_sql($email_id).'"';
  
        $email_data=$wpdb->get_row($sql);
     
        if(!empty($email_data))
        {
            $addresses=array();
            foreach($results AS $row)
            {
                if(get_option('church_admin_cron')!='immediate')
                {//queue the emails
                    if(!empty($row->email))
                    {
                        if(QueueEmail($row->email,$email_data->subject,str_replace('<!--salutation-->','Dear '.$row->first_name.',',$email_data->message),'',$email_data->from_name,$email_data->from_email,$email_data->filename)) echo'<p>'.$row->email.' queued</p>';
                    }
                    if(!empty($row->email2))
                    {
                        if(QueueEmail($row->email2,$email_data->subject,str_replace('<!--salutation-->','Dear '.$row->first_name.',',$email_data->message),'',$email_data->from_name,$email_data->from_email,$email_data->filename)) echo'<p>'.$row->email2.' queued</p>';
                    }
                }
                else{//send immediately using wp_email()
		  
                        add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

                        $headers="From: ".$email_data->from_name." <".$email_data->from_email.">\n";
                        if(!empty($row->email))
                        {
                            if(wp_mail($row->email,$email_data->subject,str_replace('<!--salutation-->]','Dear '.$row->first_name.',',$email_data->message),$headers,unserialize($email_data->filename))) echo'<p>'.$row->email.' sent immediately</p>';
                        }
                        if(!empty($row->email2))
                        {
                            if(wp_mail($row->email2,$email_data->subject,str_replace('<!--salutation-->','Dear '.$row->first_name.',',$email_data->message),$headers,unserialize($email_data->filename))) echo'<p>'.$row->email2.' sent immediately</p>';
                        }
                    }
            }
            $wpdb->query('UPDATE wp_church_admin_email_build SET recipients="'.esc_sql(maybe_serialize($addresses)).'" WHERE email_id="'.esc_sql($email_id).'"');
        }
        
    }
}

function getTweetUrl($url, $text)
{
$maxTitleLength = 120 ;
if (strlen($text) > $maxTitleLength) {
$text = substr($text, 0, ($maxTitleLength-3)).'...';
}
$text=str_replace('"','',$text);
$outputurl='http://twitter.com/share?wrap_links=true&amp;url='.urlencode($url).'&amp;text='.urlencode($text);
$output='<a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$outputurl.'" data-text="'.$text.'" data-count="horizontal">Tweet</a>';
return $output;
}
?>