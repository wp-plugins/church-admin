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
    <p><label for="reply_name"><strong>Reply to name:</strong></label><input id="reply_name" type="text" name="from_name" value="'.get_option('blogname').'"/></p>
    <p><label for="reply_email"><strong>Email address for replies:</strong></label><input id="reply_email" type="text" name="from_email" value="'.get_option('admin_email').'"/></p> 
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
            if (function_exists(get_the_post_thumbnail)&& get_the_post_thumbnail( $row->ID, 'ca-email-thumb')!='')
            {
	       
                $post_section.= get_the_post_thumbnail($row->ID,'ca-email-thumb').get_the_post_thumbnail($row->ID,'ca-120-thumb',array('style'=>"display:none;"));
            }
            else
            {
                $post_section.='<img src="http://dummyimage.com/300x200/000/fff.jpg&text='.str_replace(' ', '+', $row->post_title).'" class="attachment-ca-email-thumb" title="'.$row->post_title.'" alt="'. $row->post_title.'"  ><img src="http://dummyimage.com/120x90/000/fff.jpg&text='.str_replace(' ', '+', $row->post_title).'" class="attachment-ca-120-thumb" style="display:none" title="'.$row->post_title.'" alt="'. $row->post_title.'"  >';
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
    $RSS='<a href="'.get_bloginfo('rss2_url' ).'" style="text_decoration:none" title="RSS"><img src="'.CHURCH_ADMIN_IMAGES_URL.'/rss.png" width="32" height="32"  style="border:none" alt="RSS Feed"/></a>';
     $message=str_replace('[RSS]',$RSS,$message);
    //twitter url
    if(get_option('church_admin_twitter')){$twitter='<a href="http://twitter.com/#!/'.get_option('church_admin_twitter').'" style="text_decoration:none" title="Follow us on Twitter"><img src="'.CHURCH_ADMIN_IMAGES_URL.'twitter.png" width="32" height="32"  style="border:none" alt="Twitter"/></a> ';}else{$twitter='';}
    $message=str_replace('[TWITTER]',$twitter,$message);
    //facebook url
     if(get_option('church_admin_facebook')){$facebook='<a href="'.get_option('church_admin_facebook').'" style="text_decoration:none" title="Follow us on Facebook"><img src="'.CHURCH_ADMIN_IMAGES_URL.'facebook.png" width="32" height="32"  style="border:none" alt="Facebook"/></a>';}else{$facebook='';}
    $message=str_replace('[FACEBOOK]',$facebook,$message);
    $message=str_replace('[BLOGINFO]','<a href="'.get_bloginfo('url').'">'.get_bloginfo('url').'</a>',$message);
    $message=str_replace('[HEADER_IMAGE]','<img class="header_image" src="'.get_option('church_admin_email_image').'" alt="" >',$message);
    
    //copyright year
    $message=str_replace('[year]',date('Y'),$message);
    $filename='Email-'.date('Y-m-d-H-i-s').'.html';
    $message=str_replace('[cache]','<p style="font-size:smaller;text-align:center;margin:0 auto;">Having trouble reading this? - <a href="'.CHURCH_ADMIN_EMAIL_CACHE_URL.$filename.'">view in your web browser</a></p>',$message);

    
    
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
    global $wpdb,$member_type;
    $wpdb->show_errors;
    //this function displays a form to select recipients
    echo'<h2>Now choose recipients...</h2><form action="" method="post"><input type="hidden" value="'.$email_id.'" name="email_id"/><input type="hidden" name="send_email" value="1"/>';
 foreach($member_type AS $key=>$value)
 {
   echo'<p><label><strong>'.$value.'</strong></label><input type="checkbox" name="member_type[]" value="'.$key.'"/></p>';
 }

echo'<p><label><strong>A Small group</strong></label><input type="radio" name="type" value="smallgroup"/></p>';
 echo'<fieldset id="smallgroup">';
echo'<p><label>Which group</label><select name="group_id">';
$results=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
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
    $results=$wpdb->get_results('SELECT CONCAT_WS(", ",last_name,first_name) AS name,people_id FROM '.CA_PEO_TBL.' WHERE email!="" AND last_name!="" AND first_name!="" ORDER BY last_name');
    foreach($results AS $row)
    {
        echo '<option value="'.$row->id.'">'.$row->name.'</option>';
    }
    echo'</select></p></div>';
    echo'<p><input type="button" id="btnAdd" value="Add another person" /><input type="button" id="btnDel" value="Remove person" /></p></fieldset>';
  
    //end choose individuals
    echo'<p><label>Choose Role</label><input type="radio" name="type" value="roles"  /></p>';
    $roles=get_option('church_admin_roles');
     echo'<fieldset id="roles">';
    echo '<div class="roleclonedInput" id="roleinput1">';
    echo'<p><label>Select Role</label><select name="role_id[]" id="roleid1" class="role_id">';
    foreach($roles AS $key=>$value)
    {
      echo'<option value="'.$key.'">'.$value.'</option>';
    }
    echo'</select></p></div>';
     echo'<p><input type="button" id="roleadd" value="Add another role" /><input type="button" id="roledel" value="Remove role" /></p></fieldset>';
  
    echo'<p><input type="submit" class="secondary-button" value="Send Email"/>';
    echo'</form></div>';
//end of choose recipients
}

function church_admin_send_message($email_id)
{
    global $wpdb,$member_type;
    $wpdb->show_errors();
    print_r($_POST);
    //this function sends the message cached in $_POST['filename'] out to the right recipients
    if(!empty($_POST['member_type']))
    {
      $w=array();
      $where='(';
      foreach($_POST['member_type'] AS $key=>$value)if(array_key_exists($value,$member_type))$w[]=' member_type_id='.$value.' ';
      $where.=implode("||",$w).')';
      $sql='SELECT DISTINCT email, first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND '.$where;
   
    }
    elseif(!empty($_POST['type']) && $_POST['type']=='smallgroup') $sql='SELECT DISTINCT email,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND small_group_id="'.esc_sql($_POST['group_id']).'"';
    elseif(!empty($_POST['type']) && $_POST['type']=='individuals')
    {
	    $names=array();
            foreach ($_POST['person']AS $value){$names[]='id = "'.esc_sql($value).'"';}
            $sql='SELECT DISTINCT email,first_name FROM '.CA_PEO_TBL.' WHERE email!="" AND "'.implode(' OR ',$names).'"';
    }
    elseif(!empty($_POST['type']) && $_POST['type']=='roles')
    {
      foreach($_POST['role_id'] AS $key=>$value)$r[]='b.role_id='.$value;
      $sql='SELECT DISTINCT a.email,a.first_name FROM '.CA_PEO_TBL.' a,'.CA_MET_TBL.' b WHERE b.people_id=a.people_id AND a.email!="" AND ('.implode( " || ",$r).')';
      
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
                    if(!empty($row->email)&& !in_array($row->email,$addresses))
                    {
			$addresses[]=$row->email;
                        if(QueueEmail($row->email,$email_data->subject,str_replace("<!--salutation-->",'Dear '.$row->first_name.',',$email_data->message),'',$email_data->from_name,$email_data->from_email,$email_data->filename)) echo'<p>'.$row->email.' queued</p>';
                    }
                    
                }
                elseif(!in_array($row->email,$addresses)){//send immediately using wp_email()
		  
                        add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

                        $headers="From: ".$email_data->from_name." <".$email_data->from_email.">\n";
                        if(!empty($row->email))
                        {
			   $addresses[]=$row->email;
			      if(wp_mail($row->email,$email_data->subject,str_replace('<!--salutation-->','Dear '.$row->first_name.',',$email_data->message),$headers,unserialize($email_data->filename)))
			      echo'<p>'.$row->email.' sent immediately</p>';
			    
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