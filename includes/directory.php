<?php
//Address Directory Functions
function church_admin_address_list()
{
    $act_error=get_option('activation_error');
   if(!empty($act_error)) echo '<div class="updated fade"><h2> You had an activation error, oh beta tester</h2><p> please post it to the forum on <a href="http://www.themoyles.co.uk">www.themoyles.co.uk</a>.</p>'.$act_error.'</div>';
    if(isset($_REQUEST['member_type_id']) && is_numeric($_REQUEST['member_type_id'])) {$member_type_id=$_REQUEST['member_type_id'];}else{$member_type_id=1;}
    global $wpdb,$member_type;
    $all=implode(',',$member_type);
    $all_id=implode(',',array_keys($member_type));
   
    $wpdb->show_errors();
    //grab address list in order
    $res = $wpdb->query('SELECT * FROM '.CA_HOU_TBL.' WHERE member_type_id="'.$member_type_id.'"');
    $items=$wpdb->num_rows;
    
    // number of total rows in the database
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'pagination.class.php');
    if($items > 0)
    {
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	$p->target("admin.php?page=church_admin/index.php&amp;member_type_id=".$member_type_id);
	if(!isset($p->paging))$p->paging=1; 
	if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset($_GET['paging']))
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = $_GET['paging'];
	}
        //Query for limit paging
	$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
    } 
    
    //prepare WHERE clause using given Member_type_id
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE member_type_id="'.esc_sql($member_type_id).'"  GROUP BY household_id ORDER BY last_name ASC '.$limit;
    $results=$wpdb->get_results($sql);
    if($results)
{    
    //prepare table
    
    echo'<div class="wrap church_admin">';
    echo'<div id="donatebox"><p>This is version '.get_option("church_admin_version").' of the <strong>Church Admin</strong> plugin by Andy Moyle.';
  echo'<a href="http://twitter.com/#!/WP_Church_Adm" class="right"><img src="'.CHURCH_ADMIN_IMAGES_URL.'FollowOnTwitter.png" width="90" height="35"   alt="Twitter"/></a>';
  echo'<p><a href="http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin/plugin-support">Get Support</a><br/><strong>Latest News</strong></p>';
  require(CHURCH_ADMIN_INCLUDE_PATH.'news-feed.php');
  echo church_admin_news_feed();
echo ' If you like the plugin, please buy me a cup of coffee!...<form class="right" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif"  name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form></div>';
echo'<h2>'.ucwords($member_type[$member_type_id]).' Address List</h2>';
    echo'<p><label>Select Address List Type</label><form name="address" action="admin.php?page=church_admin/index.php" method="POST"><select name="member_type_id" >';

    foreach($member_type AS $key=>$value)
    {
	echo '<option value="'.$key.'" ';
	echo selected($key,$_GET['member_type_id'],FALSE);
	echo '>'.$value.'</option>';
    }
    echo '</select><input type="submit" value="Go"/></form></p>';
    echo'<p><label>Search</label><form name="ca_search" action="admin.php?page=church_admin/index.php&amp;action=church_admin_search" method="POST"><input name="ca_search" style="width:100px;" type="text"/><input type="submit" value="Go"/></form></p>';
    echo '<p><label>Download a PDF</label><form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value">';
    echo'<option selected="selected" value="'.home_url().'/?download=addresslist&member_type_id=1">-- Choose a pdf --</option>';

    foreach($member_type AS $key=>$value)
    {
	echo'<option value="'.home_url().'/?download=mailinglabel&member_type_id='.$key.'">'.$value.' - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</option>';
    }
    foreach($member_type AS $key=>$value)
    {
	echo'<option value="'.home_url().'/?download=addresslist&member_type_id='.$key.'">'.$value.' Address List PDF</option>';
    }
    echo'<option value="'.home_url().'/?download=addresslist&member_type_id='.$all_id.'">'.$all.' Address List PDF</option>';
    foreach($member_type AS $key=>$value)
    {
	 echo '<option value="'.home_url().'/?download=smallgroup&member_type_id='.$key.'">'.$value.' Small Group List PDF</option>';
    }
    echo '<option value="'.home_url().'/?download=smallgroup&member_type_id='.$all_id.'">'.$all.' Small Group List PDF</option>';
for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		echo '<option value="'.home_url().'/?download=yearplanner&amp;year='.$y.'">'.$y.' Year Planner</option>';
	    }
    
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    foreach($services AS $service)
    {
	echo'<option value="'.home_url().'/?download=rota&service_id='.$service->service_id.'">Rota - '.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
    }
    echo'</select></form></p>';
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">Add Household</a></p>';
        echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_migrate_users','migrate_users').'">Migrate Wordpress Users into Directory</a></p>';
	
	// Pagination
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo $p->show();  
    echo '</div></div>';
    //Pagination
    //grab address details and associate people and put in table
    if(!empty($results))
    {
	echo '<table class="widefat"><thead><tr><th>Delete</th><th>Last name</th><th>First Name(s)</th><th>Address</th><th>Last Update</th></tr></thead><tfoot><tr><th>Delete</th><th>Last name</th><th>First Name(s)</th><th>Address</th><th>Last Update</th></tr></tfoot><tbody>';
	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT first_name,last_name,people_type_id,people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    foreach($people_results AS $people)
	    {
		if(empty($people->last_name))$people->last_name='Add Surname';
		if(empty($people->first_name))$people->first_name='Add Firstname';
		if($people->people_type_id=='1'){$last_name=$people->last_name; $adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.$people->first_name.'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.$people->first_name.'</a>' ;}
		
	    }
	    
	    if(!empty($adults)){$adult=implode(" & ",$adults);}else{ $adult="Add Name";}
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    $add=implode(', ',array_filter(maybe_unserialize($add_row->address)));
	    
	    if(!empty($add)&& $add!=', , , , '){$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">'.$add.'</a>';}else{$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">Add Address</a>';}
	    
	    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_household&amp;household_id='.$row->household_id,'delete_household').'">Delete</a>';
	    echo '<tr><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$row->household_id,'display_household').'">'.$last_name.'</a></td><td>'.$adult.' '.$kids.'</td><td>'.$address.'</td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	echo '</tbody></table>';
    }	
    }//end of process results
    else
    {
	echo  '<div class="updated fade"><p>No address records yet</p></div>';
	    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">Add Household</a></p>';
        echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_migrate_users','migrate_users').'">Migrate Wordpress Users into Directory</a></p>';
    }
   
    echo '</div>';
}

function church_admin_edit_household($household_id=NULL)
{
    global $wpdb,$member_type;
    $wpdb->show_errors();
    if($household_id){$data=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');}else{$data=NULL;}
    if(!empty($_POST['edit_household']))
    {//process form
	
	$form=array();
	foreach ($_POST AS $key=>$value)$form[$key]=stripslashes($value);
	$address=esc_sql(serialize(array('address_line1'=>$form['address_line1'],'address_line2'=>$form['address_line2'],'town'=>$form['town'],'county'=>$form['county'],'postcode'=>$form['postcode'])));
	if(!$household_id)$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.$address.'" AND lat="'.esc_sql($form['lat']).'" AND lng="'.esc_sql($form['lng']).'" AND phone="'.esc_sql($form['phone']).'"');
	if(!$household_id)
	{//insert
	    $success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.$address.'", "'.esc_sql($form['lat']).'","'.esc_sql($form['lng']).'","'.esc_sql($form['phone']).'" )');
	    $household_id=$wpdb->insert_id;
	}//end insert
	else
	{//update
	   $success=$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.$address.'" , lat="'.esc_sql($form['lat']).'" , lng="'.esc_sql($form['lng']).'" , phone="'.esc_sql($form['phone']).'" WHERE household_id="'.esc_sql($household_id).'"');
	}//update
	if($success)
	{
	    echo '<div class="updated fade"><p><strong>Address saved</strong></p></div>';
	}
	
	    church_admin_display_household($household_id);
	
    }//end process form
    else
    {//household form
	if(!empty($household_id)){$text='Edit ';}else{$text='Add ';}
	echo ' <div class="wrap church_admin" ><h2>'.$text.' Address</h2><form action="" method="post">';
	church_admin_address_form($data,$error=NULL);
	//Phone
    if(!isset($data->phone))$data->phone='';
    echo '<p><label>Phone</label><input type="text" name="phone" value="'.$data->phone.'"';
    if(!empty($errors['phone']))echo' class="red" ';
    echo '/></p>';
	echo'<p class="submit"><input type="hidden" name="edit_household" value="yes"/><input type="submit" value="Save Address&raquo;" /></p></form></div>';
    }//end household form
}
function church_admin_delete_household($household_id=NULL)
{
    //deletes household with specified household_id
    global $wpdb;
    $wpdb->show_errors();
    //delete people meta data
    $people=$wpdb->get_results('SELECT people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    foreach($people AS $person){$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($person->people_id).'"');}
    //delete from household and people tables
    $wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    echo'<div class="updated fade"><p><strong>Household Deleted</strong></p></div>';
    
    church_admin_address_list();
}
function church_admin_edit_people($people_id=NULL,$household_id)
{
    global $wpdb,$people_type,$member_type;
    $roles=get_option('church_admin_roles');
    
    $wpdb->show_errors();
    
    if($people_id)$data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    if(!empty($_POST['edit_people']))
    {//process
	
	$sql=array();
	foreach($_POST AS $key=>$value)$sql[$key]=esc_sql(stripslashes($value));
	if(!empty($_POST['date_of_birth']))$dob=esc_sql(date('Y-m-d',strtotime($_POST['date_of_birth'])));
	if(!$people_id)$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.$sql['first_name'].'" AND last_name="'.$sql['last_name'].'" AND email="'.$sql['email'].'" AND mobile="'.$sql['mobile'].'" AND sex="'.$sql['sex'].'" AND people_type_id="'.$sql['people_type_id'].'" AND  member_type_id="'.$sql['member_type_id'].'" AND date_of_birth="'.$date.'" AND household_id="'.esc_sql($household_id).'"');
	$prev_member_types=array();
	foreach($member_type AS $no=>$type)
	{
	    if($no==$_POST['member_type_id'] && $_POST['member_type_id']!=$data->member_type_id){$prev_member_types[$type]=date('Y-m-d');}
	    $prev_member_types[$type]=date('Y-m-d',strtotime($_POST[$type]));
	}
	
	$prev_member_types=serialize($prev_member_types);
	if($people_id)
	{//update
	    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET first_name="'.$sql['first_name'].'" , last_name="'.$sql['last_name'].'" , email="'.$sql['email'].'" , mobile="'.$sql['mobile'].'" , sex="'.$sql['sex'].'" ,people_type_id="'.$sql['people_type_id'].'", member_type_id="'.$sql['member_type_id'].'" , date_of_birth="'.$date.'",member_data="'.esc_sql($prev_member_types).'",smallgroup_id="'.$sql['smallgroup_id'].'" WHERE household_id="'.esc_sql($household_id).'" AND people_id="'.esc_sql($people_id).'"');
	}//end update
	else
	{
	    $wpdb->query('INSERT INTO '.CA_PEO_TBL.' ( first_name,last_name,email,mobile,sex,people_type_id,member_type_id,date_of_birth,household_id,member_data,smallgroup_id) VALUES("'.$sql['first_name'].'","'.$sql['last_name'].'" , "'.$sql['email'].'" , "'.$sql['mobile'].'" , "'.$sql['sex'].'" ,"'.$sql['people_type_id'].'", "'.$sql['member_type_id'].'" , "'.$date.'" , "'.esc_sql($household_id).'","'.esc_sql($prev_member_types).'" ,"'.$sql['smallgroup_id'].'")');
	    $people_id=$wpdb->insert_id;
	}
	//update meta
	foreach($roles AS $key=>$value)
	{
	    if(!empty($_POST['role'.$key]))church_admin_update_role($key,$people_id);
	}
	if(!empty($_POST['new_role'])&&$_POST['new_role']!='Add a new role')
	{
	    
	    if(!in_array(stripslashes($_POST['new_role']),$roles))
	    {
		$roles[]=stripslashes($_POST['new_role']);
		update_option('church_admin_roles',$roles);
		church_admin_update_role(key($roles),$people_id);
	    }
	}
	echo'<div class="updated fade"><p><strong>Person Edited</strong></p></div>';
	church_admin_display_household($household_id);
    }//end process
    else
    {
	echo'<div class="wrap church_admin"><h2>People Form</h2>';
	echo'<form action="" method="POST">';
	//first name
	echo'<p><label>First Name</label><input type="text" name="first_name" ';
	if(!empty($data->first_name)) echo ' value="'.$data->first_name.'" ';
	echo'</p>';
	//last name
	echo'<p><label>Last Name</label><input type="text" name="last_name" ';
	if(!empty($data->last_name)) echo ' value="'.$data->last_name.'" ';
	echo'</p>';
	//email
	echo'<p><label>Email Address</label><input type="text" name="email" ';
	if(!empty($data->email)) echo ' value="'.$data->email.'" ';
	echo'</p>';
	//mobile
	echo'<p><label>Mobile</label><input type="text" name="mobile" ';
	if(!empty($data->mobile)) echo ' value="'.$data->mobile.'" ';
	echo'</p>';
	//sex
	echo'<p><label>Sex</label>Male <input type="radio" name="sex" value="1"';
	if(!empty($data->sex)) checked($data->sex,1);
	echo ' /> Female <input type="radio" name="sex" value="0"';
	if(!empty($data->sex))  checked($data->sex,0);
	echo'/></p>';
	//people_type
	echo'<p><label>People Type</label><select name="people_type_id">';
	foreach($people_type AS $key=>$value)
	{
	    echo'<option value="'.$key.'" ';
	    selected($key,$data->people_type_id);
	    echo'>'.$value.'</option>';
	}
	echo'</select></p>';
	//date of birth
	echo'<p><label>Date of Birth</label><input type=="text" name="date_of_birth" class="date_of_birth" ';
	if(!empty($data->date_of_birth)&&$data->date_of_birth!='0000-00-00') echo ' value="'.mysql2date(get_option('date_format'),$data->date_of_birth).'" ';
	echo'/></p>';
	echo'<script type="text/javascript">
      jQuery(document).ready(function(){
         jQuery(\'.date_of_birth\').datepicker({
            dateFormat : "d MM yy", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
      });
   </script>';
	
	echo'<p><label>Current Member Type</label><select name="member_type_id">';
		foreach($member_type AS $key=>$value)
	{
	    echo'<option value="'.$key.'" ';
	    selected($key,$data->people_type_id);
	    echo'>'.$value.'</option>';
	}
	echo'</select></p>';
	echo'</p>';
	
	$prev_member_types=unserialize($data->member_data);
	
	    echo'<p><label>Dates of Member Levels</label><span style="display:inline-block">';
	    foreach($member_type AS $key=>$value)
	    {
		 echo $value.': <input type="text" id="'.$value.'" name="'.$value.'" value="'.mysql2date(get_option('date_format'),$prev_member_types[$value]).'"/><br/>';
		echo'<script type="text/javascript">
      jQuery(document).ready(function(){
         jQuery(\'#'.$value.'\').datepicker({
            dateFormat : "d MM yy", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
      });
   </script>';
	    }
	    echo'</span></p>';
	
	echo'<p><label>Roles</label><span class="roles">';
	
	foreach($roles AS $key=>$value)
	{
	    echo'<input type="checkbox" name="role'.$key.'" value="1" ';
	    if(!empty($data->people_id))
	    {
		$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND role_id="'.esc_sql($key).'"');
		if($check)echo ' checked="checked" ';
	    }
	    echo '/>'.$value.'<br/>';
	}
	echo '<input type="text" name="new_role" value="Add a new role"/></p>';
	//small group
	echo'<p><label>Small Group</label><select name="smallgroup_id">';
	$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
	foreach($smallgroups AS $smallgroup)
	{
	    echo'<option value="'.$smallgroup->id.'" ';
	    if(!empty($data->smallgroup_id))selected($smallgroup->id,$data->smallgroup_id);
	    echo'>'.$smallgroup->group_name.'</option>';
	}
	echo'</select></p>';
	echo'<p><label>Wordpress User</label>';
	if($data->user_id)
	{
	   $user_info=get_userdata($data->user_id);
	   echo $user_info->wp_capabilities['0'].'</p>';
	}
	else
	{
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_create_user&amp;people_id='.$people_id.'&amp;household_id='.$household_id,'create_user').'">Create WP User</a></p>';
	}
	echo'<p class="submit"><input type="hidden" name="edit_people" value="yes"/><input type="submit" value="Save Address&raquo;" /></p></form></div>';
    }
}
function church_admin_delete_people($people_id=NULL,$household_id)
{
    //deletes person with specified people_id
    global $wpdb;
    $wpdb->show_errors();
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    echo'<div class="updated fade"><p><strong>Person Deleted</strong></p></div>';
    church_admin_display_household($household_id);
}

function church_admin_address_form($data,$error)
{
    //echos form contents where $data is object of address data and $error is array of errors if applicable
    if(!empty($errors))echo'<p>There were some errors marked in red</p>';
    echo'<script type="text/javascript"> var beginLat =';
    if(!empty($data->lat)) {echo $data->lat;}else {echo'51.50351129583287';}
echo '; var beginLng =';
    if(!empty($data->lng)) {echo $data->lng;}else {echo'-0.148193359375';}
    echo';</script>';
    //end initialise coordinates
    $address=maybe_unserialize($data->address);
    echo '<p><label>Address Line 1</label><input type="text" id="address_line1" name="address_line1" ';
    if(!empty($address['address_line1'])) echo' value="'.$address['address_line1'].'" ';
    if(!empty($error['address_line1'])) echo ' class="red" ';
    echo '/></p>';
    echo '<p><label>Address Line 2</label><input type="text" id="address_line2" name="address_line2" ';
    if(!empty($address['address_line2'])) echo' value="'.$address['address_line2'].'" ';
    if(!empty($error['address_line2'])) echo ' class="red" ';
    echo '/></p>';
    echo '<p><label>City</label><input type="text" id="town" name="town" ';
    if(!empty($address['town'])) echo' value="'.$address['town'].'" ';
    if(!empty($error['town'])) echo ' class="red" ';
    echo '/></p>';
    echo '<p><label>County</label><input type="text" id="county" name="county" ';
    if(!empty($address['county'])) echo' value="'.$address['county'].'" ';
    if(!empty($error['county'])) echo ' class="red" ';
    echo '/></p>';
    echo '<p><label>Postcode</label><input type="text" id="postcode" name="postcode" ';
    if(!empty($address['postcode'])) echo' value="'.$address['postcode'].'" ';
    if(!empty($error['postcode'])) echo ' class="red" ';
    echo '/></p>';
    if(!isset($data->lng))$data->lng='';
    if(!isset($data->lat))$data->lat='';
    echo '<p><label><a href="#" id="geocode_address">Please click here to update map location...</a><br/><span id="finalise" >Once you have updated your address, this map will show roughly where your church is on the website</span></label><input type="hidden" name="lat" id="lat" value="'.$data->lat.'"/><input type="hidden" name="lng" id="lng" value="'.$data->lng.'"/><div id="map" style="width:500px;height:300px"></div></p>';
    
    
}

function church_admin_display_household($household_id)
{
    global $wpdb,$people_type,$member_type;
    $roles=get_option('church_admin_roles');
    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    if($add_row)
    {//address stored
	$address=implode(', ',array_filter(unserialize($add_row->address)));
	if(empty($address))$address='Add Address';
	if(!empty($add_row->lng))$map='<img src="http://maps.google.com/maps/api/staticmap?center='.$add_row->lat.','.$add_row->lng.'&zoom=15&markers='.$add_row->lat.','.$add_row->lng.'&size=500x300&sensor=false" alt="'.$address.'"/>';
	echo'<h2>Household Details</h2>';
	
	//grab people
	$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" ORDER BY people_type_id ASC,date_of_birth ASC,sex DESC');
	if($people)
	{//are people
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">Add someone</a></p>';
	    echo'<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Name</th><th>Sex</th><th>Person type</th><th>Member Level</th><th>Roles</th><th>Email</th><th>Mobile</th><th>WP user</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Name</th><th>Sex</th><th>People type</th><th>Member Level</th><th>Roles</th><th>Email</th><th>Mobile</th><th>WP user</th></tr></tfoot><tbody>';
	    foreach ($people AS $person)
	    {
		switch($person->sex)
		{
		    case 0:$sex='Female';break;
		    case 1:$sex='Male';break;
		}
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'"');
		$role=array();
		foreach($result AS $row){$role[]=$roles[$row->role_id];}
		asort($role);
		if($person->user_id)
		{
		    $user_info=get_userdata($person->user_id);
		    $person_user= church_admin_get_capabilities($person->user_id);
		}
		else
		{
		    $person_user='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_create_user&amp;people_id='.$person->people_id.'&amp;household_id='.$person->household_id,'create_user').'">Create WP User</a></p>';
		}
		echo'<tr><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'edit_people').'">Edit</a></td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_people&amp;household_id='.$household_id.'&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'delete_people').'">Delete</a></td><td>'.$person->first_name.' '.$person->last_name.'</a></td><td>'.$sex.'</td><td>'.$people_type[$person->people_type_id].'</td><td>'.$member_type[$person->member_type_id].'</td><td>'.implode(', ',$role).'</td><td>'.$person->email.'</td><td>'.$person->mobile.'</td><td>'.$person_user.'</td></tr>';
	    }
	    echo'</tbody></table>';
	}//end are people
	else
	{//no people
	    echo'<p>There are no people store in that household yet</p>';
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">Add someone</a></p>';
	}//no people
	//end grab people
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$household_id,'edit_household').'">'.$address.'</a></p>';
	echo'<p>'.$map.'</p>';
    }//end address stored
    else
    {
	echo'<div class="updated fade"><p><strong>No Household found</strong></p></div>';
	church_admin_address_list(); 
    }
}

function church_admin_migrate_users()
{
    global $wpdb;
    $results=$wpdb->get_results('SELECT ID FROM '.$wpdb->users);
    if($results)
    {
	foreach($results AS $row)
	{
	    $check=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($row->ID).'"');
	    if(!$check)
	    {
		$user_info=get_userdata($row->ID);
		$address=$address=esc_sql(serialize(array('address_line1'=>"",'address_line2'=>"",'town'=>"",'county'=>"",'postcode'=>"")));
		$wpdb->query('INSERT INTO '.CA_HOU_TBL.'(member_type_id,address)VALUES("1","'.$address.'")');
		$household_id=$wpdb->insert_id;
		$wpdb->query('INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,email,household_id,user_id,member_type_id,people_type_id,smallgroup_id,sex) VALUES("'.$user_info->first_name.'","'.$user_info->last_name.'","'.$user_info->user_email.'","'.$household_id.'","'.$row->ID.'","1","1","0","1")');
	    }
	}
	
	echo'<div class="updated fade"><p><strong>Wordpress Users migrated</strong></p></div>';
    }
    
    church_admin_address_list();
}

function church_admin_create_user($people_id,$household_id)
{
    global $wpdb;
    if(!$people_id)exit("Nobody was specified!");
    $user=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    if(empty($user->user_id)&&!email_exists($user->email))
    {
	$username=strtolower($user->first_name.$user->last_name);
	$x='';
	while(username_exists( $user_name.$x ))
	{
	    $x+=1;
	}

	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
	$user_id = wp_create_user( $username, $random_password, $user->email );
	$message='<p>The web team at <a href="'.site_url().'">'.site_url().'</a> have just created a user login for you.</p>';
	$message.='<p>Your username is <strong>'.$username.$x.'</strong></p>';
	$message.='<p>Your password is <strong>'.$random_password.'</strong></p>';
	QueueEmail($user->email,'Login for '.site_url(),$message,'',"Web team at ".site_url(),get_option('admin_email'),NULL);
	$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
	church_admin_display_household($household_id);
    }
}
function church_admin_get_capabilities($id)
{
    $user_info=get_userdata($id);
    $cap=$user_info->wp_capabilities;
	if (array_key_exists('subscriber',$cap))return 'Subscriber';
	if (array_key_exists('author',$cap))'Author';break;
	if (array_key_exists('editor',$cap)) 'Editor';break;
	if (array_key_exists('administrator',$cap)) 'Administrator';break;
	return FALSE;
}

function church_admin_search($search)
{
    global $wpdb;
    $s=esc_sql(stripslashes($search));
    $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")';
    
    $results=$wpdb->get_results($sql);
    if(!$results)
    {
	$sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%")';
    
	$results=$wpdb->get_results($sql);
    }
    if($results)
    {
	    echo'<div class="wrap church_admin"><h2>Address List</h2><div class="updated fade"><p><strong>Your search for '.$s.' yielded these '.$wpdb->num_rows.' results.</strong></p></div>';
	    echo '<table class="widefat"><thead><tr><th>Delete</th><th>Last name</th><th>First Name(s)</th><th>Address</th><th>Last Update</th></tr></thead><tfoot><tr><th>Delete</th><th>Last name</th><th>First Name(s)</th><th>Address</th><th>Last Update</th></tr></tfoot><tbody>';
	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT first_name,last_name,people_type_id,people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    foreach($people_results AS $people)
	    {
		if($people->people_type_id=='1'){$last_name=$people->last_name; $adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.$people->first_name.'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php&page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.$people->first_name.'</a>' ;}
		
	    }
	    $adult=implode(" & ",$adults);
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    $add=implode(', ',maybe_unserialize($add_row->address));
	    
	    if(!empty($add)&& $add!=', , , , '){$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">'.$add.'</a>';}else{$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">Add Address</a>';}
	    
	    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_household&amp;household_id='.$row->household_id,'delete_household').'">Delete</a>';
	    echo '<tr><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$row->household_id,'display_household').'">'.$last_name.'</a></td><td>'.$adult.' '.$kids.'</td><td>'.$address.'</td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	echo '</tbody></table>';
	
	
    }
    else
    {
	echo'<div class="updated fade"><p>Search '.$s.' not found</p></div>';
	church_admin_address_list();
    }
}




?>