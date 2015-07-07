<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_get_id_by_shortcode($shortcode) {
	global $wpdb;

	$id = NULL;

	$sql = 'SELECT ID
		FROM ' . $wpdb->posts . '
		WHERE
			post_type = "page"
			AND post_status="publish"
			AND post_content LIKE "%' . $shortcode . '%"';

	$id = $wpdb->get_var($sql);
	return $id;
}
function church_admin_initials($people)
{
	$people=maybe_unserialize($people);
	if(!empty($people))
	{
		
		foreach($people as $id=>$peep)
		{
			if(ctype_digit($peep)){$person=church_admin_get_person($peep);}else{$person=$peep;}
			$strlen=strlen($person);
			$initials[$id]='';
			for($i=0;$i<=$strlen;$i++)
			{
				$char=substr($person,$i,1);
				if (ctype_upper($char)){$initials[$id].=$char;}
			}
		}
		
		return implode(', ',$initials);
	
	}else return '';
}
function church_admin_checkdate($date)
{
		$d=explode('-',$date);
		if(is_array($d) && count($d)==3 && checkdate($d[1],$d[2],$d[0])){return TRUE;}else{return FALSE;}
}
function church_admin_level_check($what)
{
    global $current_user;
    get_currentuserinfo();
    $user_permissions=maybe_unserialize(get_option('church_admin_user_permissions'));
	
    $level=get_option('church_admin_levels');
	
    if(!empty($user_permissions[$what]))
    {//user permissions have been set for $what
		
		if( in_array($current_user->ID,maybe_unserialize($user_permissions[$what]))){return TRUE;}else{return FALSE;}
	}//end user permissions have been set
    elseif(!empty($level[$what]) && $level[$what]=="administrator"){return current_user_can('manage_options');}
    elseif(!empty($level[$what]) && $level[$what]=="editor"){return current_user_can('delete_others_pages');}
    elseif(!empty($level[$what]) &&$level[$what]=="author"){return current_user_can('publish_posts');}
    elseif(!empty($level[$what]) &&$level[$what]=="contributor"){return current_user_can('edit_posts');}
    elseif(!empty($level[$what]) &&$level[$what]=="subscriber"){return current_user_can('read');}
    else{ return false;}
}

function church_admin_user($ID)
{
		global $wpdb;
		$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($ID).'"');
		if(!empty($people_id)) {return $people_id;}else{return FALSE;}
}
function church_admin_collapseBoxForUser($userId, $boxId) {
    $optionName = "closedpostboxes_church-admin";
    $close = get_user_option($optionName, $userId);
    $closeIds = explode(',', $close);
    $closeIds[] = $boxId;
    $closeIds = array_unique($clodeIds); // remove duplicate Ids
    $close = implode(',', $closeIds);
    update_user_option($userId, $optionName, $close);
}



function church_admin_autocomplete($name='people',$first_id='friends',$second_id='to',$current_data=array(),$user_id=FALSE)
{
            /**
 *
 * Creates autocomplete field 
 * 
 * @author  Andy Moyle
 * @param    $name,$first_id,$second_id
 * @return   html string
 * @version  0.1
 *
 * 
 */
    $current='';        
    if(!empty($current_data))
    {
        $curr_data=maybe_unserialize($current_data);
        
        if(is_array($curr_data))
		{
			foreach($curr_data AS $key=>$value)
			{
				
				if(ctype_digit($value))
				{
						if(!$user_id)
						{//people_id
							$peoplename=church_admin_get_person($value);
						}
						else
						{//user_id
							$peoplename=church_admin_get_name_from_user($value);
						}	
				}else $peoplename=$value;
				$current.=$peoplename.', ';
			}
		}else$current=$current_data;
    }
    $out= '<input id="'.$first_id.'" class="to" type="text" name="'.esc_html($name).'" value="'.esc_html($current).'"/> ';
    $out.='<script type="text/javascript">

	jQuery(document).ready(function ($){
	$("#'.$first_id.'").blur(function(){
    // Using disable and close after destroy is redundant; just use destroy
    $(this).autocomplete("destroy");
});

	$("#'.$first_id.'").autocomplete({
		source: function(req, add){
			$.getJSON("'.site_url().'/wp-admin/admin.php?page=church_admin/index.php&action=get_people&callback=?", req,  function(data) {  
                              
                    //create array for response objects  
                    var suggestions = [];  
                              
                    //process response  
                    $.each(data, function(i, val){                                
                    suggestions.push(val.name);  
                });  
                              
                //pass array to callback  
                add(suggestions);  
            });  

		},
		select: function (event, ui) {
                var terms = $("#'.$first_id.'").val().split(", ");
		// remove the current input
                terms.pop();
                console.log(terms);
		// add the selected item
                terms.push(ui.item.value);
		console.log(terms);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                $("#'.$first_id.'").val(this.value);
                return false;
            },
		minLength: 3,
		
	});
});


</script>';
    return $out;
}


function church_admin_get_person($id)
{
             /**
 *
 * Returns person's names from $id
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
 global $wpdb;
    $name=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($id).'"');
    if($name){return esc_html($name);}else{return FALSE;}
}
function church_admin_get_name_from_user($id)
{
             /**
 *
 * Returns person's names from user_id
 * 
 * @author  Andy Moyle
 * @param    $id
 * @return   string
 * @version  0.1
 *
 *
*/
 global $wpdb;
 $wpdb->show_errors;
    $name=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($id).'"');
    if($name){return esc_html($name);}else{return FALSE;}
}
function church_admin_get_people($idArray)
{
         /**
 *
 * Returns peoples names from serialized array
 * 
 * @author  Andy Moyle
 * @param    $idArray
 * @return   string
 * @version  0.1
 * 
 */
    global $wpdb;
    $ids=maybe_unserialize($idArray);
    if(!is_array($ids))return $ids;
    if(!empty($ids))
    {
        $names=array();
        foreach($ids AS $key=>$id)
        {
            if(ctype_digit($id))
            {//is int
                $names[]=$wpdb->get_var('SELECT CONCAT_WS(" ",first_name,last_name) FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($id).'"');
            }//end is int
            else
            {//is text
                $names[]=$id;
            }//end is text
        }
        return implode(", ", array_filter($names));
    }
    else
    return " ";
}

function church_admin_get_people_id($name)
{
        /**
 *
 * Returns serialized array of people_id if $name is in DB
 * 
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 * 
 */
    global $wpdb;    
    $names=explode(',',$name);
    
    $people_ids=array();
    if(!empty($names))
    {
        foreach($names AS $key=>$value)
        {
			$value=trim($value);
            if(!empty($value))
            {//only look if a name stored!
                $sql='SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql($value).'" LIMIT 1';
                $result=$wpdb->get_var($sql);
                if($result){$people_ids[]=$result;}else{$people_ids[]=$value;}
            }
        }
    }
    
    return maybe_serialize(array_filter($people_ids));
}
function church_admin_get_user_id($name)
{
        /**
 *
 * Returns serialized array of user_id if $name is in DB
 * 
 * @author  Andy Moyle
 * @param    $name
 * @return   serialized array
 * @version  0.1
 * 
 */
    global $wpdb;    
    $names=explode(',',$name);
    
    $user_ids=array();
    if(!empty($names))
    {
        foreach($names AS $key=>$value)
        {
			$value=trim($value);
            if(!empty($value))
            {//only look if a name stored!
                $sql='SELECT user_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql($value).'" LIMIT 1';
                $result=$wpdb->get_var($sql);
                if($result){$user_ids[]=$result;}else
				{
					echo '<p>'.esc_html($value).' is not stored by Church Admin as  Wordpress User. ';
					$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql($value).'" LIMIT 1');
					if(!empty($people_id))echo'Please <a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.$people_id,'edit_people').'">edit</a> entry to connect/create site user account.';
					echo'</p>';
				}
            }
        }
    }
    if(!empty($user_ids)){ return maybe_serialize(array_filter($user_ids));}else{return NULL;}
}
function church_admin_get_one_id($name)
{
	global $wpdb;
	$sql='SELECT people_id FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.esc_sql($name).'" LIMIT 1';
    $result=$wpdb->get_var($sql);
	if(!empty($result)){return $result;}else{return $name;}
}
function church_admin_ajax_people()
{
            /**
 *
 * Ajax - returns json array with people's names
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   json array
 * @version  0.1
 * 
 */
    global $wpdb;
    $names=explode(", ", $_GET['term']);//put passed var into array
    $name=esc_sql(stripslashes(end($names)));//grabs final value for search

    $sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name FROM '.CA_PEO_TBL.' WHERE CONCAT_WS(" ",first_name,last_name) REGEXP "^'.$name.'"';
   
    $result=$wpdb->get_results($sql);
    if($result)
    {
        $people=array();
        foreach($result AS $row)
        {
            $people[]=array('name'=>$row->name);
        }
        
        //echo JSON to page  
    $response = $_GET["callback"] . "(" . json_encode($people) . ")";  
    echo $response; 
    }
    exit();
}

function church_admin_update_order($which='member_type')
{
    global $wpdb;
    if(isset($_POST['order']))
    {
        switch($which)
        {
			case'facilities':$tb=CA_FAC_TBL;$field='facilities_order';$id='facility_id';break;
            case'member_type':$tb=CA_MTY_TBL;$field='member_type_order';$id='member_type_id';break;
            case'rota_settings':$tb=CA_RST_TBL;$field='rota_order';$id='rota_id';break;
            case'small_groups':$tb=CA_SMG_TBL;$field='smallgroup_order';$id='id';break;
			case'people':$tb=CA_PEO_TBL;$field='people_order';$id='people_id';break;
        }
        $order=explode(",",$_POST['order']);
        foreach($order AS $order=>$row_id)
        {
            $member_type_order++;
            $sql='UPDATE '.$tb.' SET '.$field.'="'.esc_sql($order).'" WHERE '.$id.'="'.esc_sql($row_id).'"';
            $wpdb->query($sql);
        }
    }
}
function church_admin_member_type_array()
{
    global $wpdb;
    $member_type=array();
    $results=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL.' ORDER BY member_type_order ASC');
    foreach($results AS $row)
    {
        $member_type[$row->member_type_id]=$row->member_type;
    }
    return($member_type);
}
function church_admin_update_department($department_id,$people_id,$meta_type='ministry')
{
  global $wpdb;
  $wpdb->show_errors;
  $id=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="'.esc_sql($meta_type).'" AND department_id="'.esc_sql($department_id).'"');
  if(!$id){$wpdb->query('INSERT INTO '.CA_MET_TBL.'(people_id,department_id,meta_type) VALUES("'.esc_sql($people_id).'","'.esc_sql($department_id).'","'.esc_sql($meta_type).'")');}
}
function strip_only($str, $tags) {
    //this functions strips some tages, but not all
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    return $str;
}

function checkDateFormat($date)
{
  //match the format of the date
  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
  {
    //check weather the date is valid of not
        if(checkdate($parts[2],$parts[3],$parts[1]))
          return true;
        else
         return false;
  }
  else
    return false;
}


function QueueEmail($to,$subject,$message,$copy,$from_name,$from_email,$attachment)
{
    global $wpdb;
    $sqlsafe=array();
    $sqlsafe['to']=esc_sql($to);
    $sqlsafe['from_name']=esc_sql($from_name);
    $sqlsafe['from_email']=esc_sql($from_email);
    $sqlsafe['subject']=esc_sql($subject);    
    $sqlsafe['message']=esc_sql($message);
    $sqlsafe['attachment']=esc_sql($attachment);

    $sqlsafe['copy']=esc_sql($copy);
    $result=$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_email (recipient,from_name,from_email,copy,subject,message,sent,attachment)VALUES('{$sqlsafe['to']}','{$sqlsafe['from_name']}','{$sqlsafe['from_email']}','{$sqlsafe['copy']}','{$sqlsafe['subject']}','{$sqlsafe['message']}',NOW(),'{$sqlsafe['attachment']}')");

    if($result) {return $wpdb->insert_id;}else{return FALSE;}
}

if(!function_exists('set_html_content_type')){function set_html_content_type() {return 'text/html';}}

function church_admin_plays($file_id)
{
	global $wpdb;
	$plays=$wpdb->get_var('SELECT plays FROM '.CA_FIL_TBL.' WHERE file_id="'.esc_sql($file_id).'"');
	return $plays;
}
/**
 * Send mail, similar to PHP's mail
 *
 * A true return value does not automatically mean that the user received the
 * email successfully. It just only means that the method used was able to
 * process the request without any errors.
 *
 * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
 * creating a from address like 'Name <email@address.com>' when both are set. If
 * just 'wp_mail_from' is set, then just the email address will be used with no
 * name.
 *
 * The default content type is 'text/plain' which does not allow using HTML.
 * However, you can set the content type of the email by using the
 * 'wp_mail_content_type' filter.
 *
 * If $message is an array, the key of each is used to add as an attachment
 * with the value used as the body. The 'text/plain' element is used as the
 * text version of the body, with the 'text/html' element used as the HTML
 * version of the body. All other types are added as attachments.
 *
 * The default charset is based on the charset used on the blog. The charset can
 * be set using the 'wp_mail_charset' filter.
 *
 * @since 1.2.1
 *
 * @uses PHPMailer
 *
 * @param string|array $to Array or comma-separated list of email addresses to send message.
 * @param string $subject Email subject
 * @param string|array $message Message contents
 * @param string|array $headers Optional. Additional headers.
 * @param string|array $attachments Optional. Files to attach.
 * @return bool Whether the email contents were sent successfully.
 */
if( ! function_exists('wp_mail') ) {function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
    // Compact the input, apply the filters, and extract them back out

    /**
     * Filter the wp_mail() arguments.
     *
     * @since 2.2.0
     *
     * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
     *                    subject, message, headers, and attachments values.
     */
	 /*
    echo 'Appl filters stage';
	$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
	echo'after filters';
    if ( isset( $atts['to'] ) ) {
        $to = $atts['to'];
    }

    if ( isset( $atts['subject'] ) ) {
        $subject = $atts['subject'];
    }

    if ( isset( $atts['message'] ) ) {
        $message = $atts['message'];
    }

    if ( isset( $atts['headers'] ) ) {
        $headers = $atts['headers'];
    }

    if ( isset( $atts['attachments'] ) ) {
        $attachments = $atts['attachments'];
    }

    if ( ! is_array( $attachments ) ) {
        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
    }*/
    global $phpmailer;

    // (Re)create it, if it's gone missing
    if ( ! ( $phpmailer instanceof PHPMailer ) ) {
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $phpmailer = new PHPMailer( true );
    }

    // Headers
    if ( empty( $headers ) ) {
        $headers = array();
    } else {
        if ( !is_array( $headers ) ) {
            // Explode the headers out, so this function can take both
            // string headers and an array of headers.
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();
        $cc = array();
        $bcc = array();

        // If it's actually got contents
        if ( !empty( $tempheaders ) ) {
            // Iterate through the raw headers
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos($header, ':') === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts = preg_split('/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }
                // Explode them out
                list( $name, $content ) = explode( ':', trim( $header ), 2 );

                // Cleanup crew
                $name    = trim( $name    );
                $content = trim( $content );

                switch ( strtolower( $name ) ) {
                    // Mainly for legacy -- process a From: header if it's there
                    case 'from':
                        $bracket_pos = strpos( $content, '<' );
                        if ( $bracket_pos !== false ) {
                            // Text before the bracketed email is the "From" name.
                            if ( $bracket_pos > 0 ) {
                                $from_name = substr( $content, 0, $bracket_pos - 1 );
                                $from_name = str_replace( '"', '', $from_name );
                                $from_name = trim( $from_name );
                            }

                            $from_email = substr( $content, $bracket_pos + 1 );
                            $from_email = str_replace( '>', '', $from_email );
                            $from_email = trim( $from_email );

                        // Avoid setting an empty $from_email.
                        } elseif ( '' !== trim( $content ) ) {
                            $from_email = trim( $content );
                        }
                        break;
                    case 'content-type':
                        if ( is_array($message) ) {
                            // Multipart email, ignore the content-type header
                            break;
                        }
                        if ( strpos( $content, ';' ) !== false ) {
                            list( $type, $charset_content ) = explode( ';', $content );
                            $content_type = trim( $type );
                            if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                            } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                $charset = '';
                            }

                        // Avoid setting an empty $content_type.
                        } elseif ( '' !== trim( $content ) ) {
                            $content_type = trim( $content );
                        }
                        break;
                    case 'cc':
                        $cc = array_merge( (array) $cc, explode( ',', $content ) );
                        break;
                    case 'bcc':
                        $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                        break;
                    default:
                        // Add it to our grand headers array
                        $headers[trim( $name )] = trim( $content );
                        break;
                }
            }
        }
    }

    // Empty out the values that may be set
    $phpmailer->ClearAllRecipients();
    $phpmailer->ClearAttachments();
    $phpmailer->ClearCustomHeaders();
    $phpmailer->ClearReplyTos();

    $phpmailer->Body= '';
    $phpmailer->AltBody= '';

    // From email and name
    // If we don't have a name from the input headers
    if ( !isset( $from_name ) )
        $from_name = 'WordPress';

    /* If we don't have an email from the input headers default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist but
     * there's no easy alternative. Defaulting to admin_email might appear to be another
     * option but some hosts may refuse to relay mail from an unknown domain. See
     * https://core.trac.wordpress.org/ticket/5007.
     */

    if ( !isset( $from_email ) ) {
        // Get the site domain and get rid of www.
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        $from_email = 'wordpress@' . $sitename;
    }

    /**
     * Filter the email address to send from.
     *
     * @since 2.2.0
     *
     * @param string $from_email Email address to send from.
     */
    $phpmailer->From = apply_filters( 'wp_mail_from', $from_email );

    /**
     * Filter the name to associate with the "from" email address.
     *
     * @since 2.3.0
     *
     * @param string $from_name Name associated with the "from" email address.
     */
    $phpmailer->FromName = apply_filters( 'wp_mail_from_name', $from_name );

    // Set destination addresses
    if ( !is_array( $to ) )
        $to = explode( ',', $to );

    foreach ( (array) $to as $recipient ) {
        try {
            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
            $recipient_name = '';
            if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                if ( count( $matches ) == 3 ) {
                    $recipient_name = $matches[1];
                    $recipient = $matches[2];
                }
            }
            $phpmailer->AddAddress( $recipient, $recipient_name);
        } catch ( phpmailerException $e ) {
            continue;
        }
    }

    // If we don't have a charset from the input headers
    if ( !isset( $charset ) )
        $charset = get_bloginfo( 'charset' );

    // Set the content-type and charset

    /**
     * Filter the default wp_mail() charset.
     *
     * @since 2.3.0
     *
     * @param string $charset Default email charset.
     */
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

    // Set mail's subject and body
    $phpmailer->Subject = $subject;

    if ( is_string($message) ) {
        $phpmailer->Body = $message;

        // Set Content-Type and charset
        // If we don't have a content-type from the input headers
        if ( !isset( $content_type ) )
            $content_type = 'text/plain';

        /**
         * Filter the wp_mail() content type.
         *
         * @since 2.3.0
         *
         * @param string $content_type Default wp_mail() content type.
         */
        $content_type = apply_filters( 'wp_mail_content_type', $content_type );

        $phpmailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type
        if ( 'text/html' == $content_type )
            $phpmailer->IsHTML( true );

        // For backwards compatibility, new multipart emails should use
        // the array style $message. This never really worked well anyway
        if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
            $phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
    }
    elseif ( is_array($message) ) {
        foreach ($message as $type => $bodies) {
            foreach ((array) $bodies as $body) {
                if ($type === 'text/html') {
                    $phpmailer->Body = $body;
                }
                elseif ($type === 'text/plain') {
                    $phpmailer->AltBody = $body;
                }
                else {
                    $phpmailer->AddAttachment($body, '', 'base64', $type);
                }
            }
        }
    }

    // Add any CC and BCC recipients
    if ( !empty( $cc ) ) {
        foreach ( (array) $cc as $recipient ) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $phpmailer->AddCc( $recipient, $recipient_name );
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    if ( !empty( $bcc ) ) {
        foreach ( (array) $bcc as $recipient) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $phpmailer->AddBcc( $recipient, $recipient_name );
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    // Set to use PHP's mail()
    $phpmailer->IsMail();

    // Set custom headers
    if ( !empty( $headers ) ) {
        foreach( (array) $headers as $name => $content ) {
            $phpmailer->AddCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
        }
    }

    if ( !empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            try {
                $phpmailer->AddAttachment($attachment);
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    /**
     * Fires after PHPMailer is initialized.
     *
     * @since 2.2.0
     *
     * @param PHPMailer &$phpmailer The PHPMailer instance, passed by reference.
     */
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

    // Send!
    try {
        return $phpmailer->Send();
    } catch ( phpmailerException $e ) {
        return false;
    }
}}
?>