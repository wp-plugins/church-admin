<?php
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