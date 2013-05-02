<?php

function church_admin_autocomplete($name='people',$first_id='friends',$second_id='to',$current_data=array(),$department_id=NULL)
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
	        if(ctype_digit($value))$value=church_admin_get_person($value);
	        $current.=$value.', ';
	    }
	}else$current=$current_data;
    }
    $out= '<input id="'.$first_id.'" class="to" type="text" name="'.$name.'" value="'.$current.'"/> ';
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
    if($name){return $name;}else{return FALSE;}
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
            case'member_type':$tb=CA_MTY_TBL;$field='member_type_order';$id='member_type_id';break;
            case'rota_settings':$tb=CA_RST_TBL;$field='rota_order';$id='rota_id';break;
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
function church_admin_update_department($department_id,$people_id)
{
  global $wpdb;
  $wpdb->show_errors;
  $id=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND department_id="'.esc_sql($department_id).'"');
  if(!$id){$wpdb->query('INSERT INTO '.CA_MET_TBL.'(people_id,department_id) VALUES("'.esc_sql($people_id).'","'.esc_sql($department_id).'")');}
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
    $sqlsafe['to']=mysql_real_escape_string($to);
    $sqlsafe['from_name']=mysql_real_escape_string($from_name);
    $sqlsafe['from_email']=mysql_real_escape_string($from_email);
    $sqlsafe['subject']=mysql_real_escape_string($subject);    
    $sqlsafe['message']=mysql_real_escape_string($message);
    $sqlsafe['attachment']=mysql_real_escape_string($attachment);

    $sqlsafe['copy']=mysql_real_escape_string($copy);
    $result=$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_email (recipient,from_name,from_email,copy,subject,message,sent,attachment)VALUES('{$sqlsafe['to']}','{$sqlsafe['from_name']}','{$sqlsafe['from_email']}','{$sqlsafe['copy']}','{$sqlsafe['subject']}','{$sqlsafe['message']}',NOW(),'{$sqlsafe['attachment']}')");

    if($result) {return TRUE;}else{return FALSE;}
}





?>