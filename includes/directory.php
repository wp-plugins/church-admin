<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Address Directory Functions
function church_admin_address_list($member_type_id=1)
{
    global $wpdb,$member_type;
    $wpdb->show_errors();
   
    //grab address list in order
	
    $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"';
   
    $result = $wpdb->get_var($sql);
    $items=$wpdb->num_rows;
    
    echo'<hr/><table class="form-table"><tbody><tr><th scope="row">'.__('Select different address list to view','church-admin').'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list" method="POST"><select name="member_type_id" >';
			    echo '<option value="">'.__('Choose Member Type...','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
					$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($key).'"');
					echo '<option value="'.esc_html($key).'" >'.esc_html($value).' ('.$count.' people)</option>';
			    }
			    echo'</select><input type="submit" value="'.__('Go','church-admin').'"/></form></td></tr></tbody></table>';
    // number of total rows in the database
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
    if($items > 0)
    {
	
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	$p->target("admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&amp;member_type_id=".$member_type_id);
	if(!isset($p->paging))$p->paging=1; 
	if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	$p->currentPage((int)$_GET[$p->paging]); // Gets and validates the current page
	$p->calculate(); // Calculates what to show
	$p->parameterName('paging');
	$p->adjacents(1); //No. of page away from the current page
	if(!isset($_GET['paging']))
	{
	    $p->page = 1;
	}
	else
	{
	    $p->page = intval($_GET['paging']);
	}
        //Query for limit paging
	$limit = esc_sql("LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit);
     
    
    //prepare WHERE clause using given Member_type_id
	$sort='last_name ASC';
	if(!empty($_GET['sort']))
	{
		switch($_GET['sort'])
		{ 
			case'date' :$sort='last_updated DESC';break;
			case'last_name':$sort='last_name ASC';break;
			default:$sort='last_name ASC';break;
		}
	}
    $sql='SELECT * FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"  GROUP BY household_id ORDER BY '.$sort.' '.$limit;
    $results=$wpdb->get_results($sql);
   
    
    if(!empty($results))
    {
	echo '<h2>'.$member_type[$member_type_id].' '.__('address list','church-admin').'</h2>';
	// Pagination
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo $p->show();  
    echo '</div></div>';
    //Pagination
    //grab address details and associate people and put in table
	echo '<table class="widefat"><thead><tr><th>'.__('Delete','church-admin').'</th><th><a href="admin.php?page=church_admin/index.php&action=church_admin_address_list& member_type_id='.intval($member_type_id).'&sort=last_name">'.__('Last name','church-admin').'</a></th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'<a></th><th><a href="admin.php?page=church_admin/index.php&action=church_admin_address_list& member_type_id='.intval($member_type_id).'&sort=date">'.__('Last Update','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></tfoot><tbody>';

	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_order,people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    $prefix='';
	    foreach($people_results AS $people)
	    {
		
		if(empty($people->last_name))$people->last_name=__('Add Surname','church-admin');
		if(empty($people->first_name))$people->first_name=__('Add Firstname','church-admin');
		if($people->people_type_id=='1'){$last_name=$people->last_name; $adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&tab=people&household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>' ;}
		if(!empty($people->prefix)){$prefix=$people->prefix.' ';}
	    }
	    
	    if(!empty($adults)){$adult=implode(" & ",$adults);}else{ $adult=__("Add Name",'church-admin');}
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    
	    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=church_admin_delete_household&household_id='.$row->household_id,'delete_household').'">'.__('Delete','church-admin').'</a>';
	    if(empty($add_row->address))$add_row->address=__('Add Address','church-admin');
	    
	    echo '<tr><td>'.$delete.'</td><td><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&tab=people&household_id='.$row->household_id,'display_household').'">'.esc_html($prefix.$last_name).'</a></td><td>'.$adult.' '.$kids.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&tab=people&household_id='.$row->household_id,'display_household').'">'.esc_html($add_row->address).'</a></td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	echo '</tbody></table>';
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo $p->show();  
    
    }//end of items>0
    }	

    
	
    
}

function church_admin_edit_household($household_id=NULL)
{
    global $wpdb,$member_type,$church_admin_version;
    $wpdb->show_errors();
    ?>
    
    <?php
    $member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"  ORDER BY people_type_id ASC LIMIT 1');
    if(!empty($household_id)){$data=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');}else{$data=NULL;}
    if(!empty($_POST['edit_household']))
    {//process form
	
	$form=array();
	foreach ($_POST AS $key=>$value)$sql[$key]=esc_sql(sanitize_text_field(stripslashes($value)));
	if(!$household_id)$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.$sql['address'].'" AND lat="'.$sql['lat'].'" AND lng="'.$sql['lng'].'" AND phone="'.$sql['phone'].'"');
	if(!$household_id)
	{//insert
	    $success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.$sql['address'].'", "'.$sql['lat'].'","'.$sql['lng'].'","'.$sql['phone'].'" )');
	    $household_id=$wpdb->insert_id;
	}//end insert
	else
	{//update
	   $sql='UPDATE '.CA_HOU_TBL.' SET address="'.$sql['address'].'" , lat="'.$sql['lat'].'" , lng="'.$sql['lng'].'" , phone="'.$sql['phone'].'" WHERE household_id="'.esc_sql($household_id).'"';
	   //echo $sql;
	   $success=$wpdb->query($sql);
	}//update
	if($success)
	{
	    echo '<div class="updated fade"><p><strong>'.__('Address saved','church-admin').' <br/><a href="./admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.__('Back to Directory','church-admin').'</a></strong></td></tr></div>';
	}
	    echo'<div id="post-body" class="metabox-holder columns-2"><!-- meta box containers here -->';
		
		echo'<div class="updated fade"><p><strong>'.__('Household Edited','church-admin').' <br/>';
		if(church_admin_level_check('Directory')) echo'<a href="./admin.php?page=church_admin/index.php&tab=people&action=church_admin_address_list&member_type_id='.$member_type_id.'">'.__('Back to Directory','church-admin').'</a>';
		echo'</strong></td></tr></div>';
	
		church_admin_display_household($household_id);
	
		
    }//end process form
    else
    {//household form
	if(!empty($household_id)){$text='Edit ';}else{$text='Add ';}
	echo '<form action="" method="post">';
	//clean out old style address data
	if(!empty($data)&&is_array(maybe_unserialize($data->address)))
	{
		$data->address=implode(", ",array_filter(maybe_unserialize($data->address)));
	}
	echo church_admin_address_form($data,$error=NULL);
	//Phone
    
    echo '<tr><th scope="row">'.__('Phone','church-admin').'</th><td><input type="text" name="phone" ';
	if(!empty($data->phone)) echo ' value="'.esc_html($data->phone).'"';
    if(!empty($errors['phone']))echo' class="red" ';
    echo '/></td></tr>';
	echo'<p class="submit"><input type="hidden" name="edit_household" value="yes"/><input type="submit" value="'.__('Save Address','church-admin').'&raquo;" /></td></tr></form>';
    }//end household form

	
}
function church_admin_delete_household($household_id=NULL)
{
    //deletes household with specified household_id
    global $wpdb;
    $wpdb->show_errors();
   
    //delete people meta data
    $people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    foreach($people AS $person){$member_type_id=$person->member_type_id;$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE meta_type="ministry" AND people_id="'.esc_sql($person->people_id).'"');}
    //delete from household and people tables
    $wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    echo'<div class="updated fade"><p><strong>'.__('Household Deleted','church-admin').'</strong></td></tr></div>';
    
}

function church_admin_edit_people($people_id=NULL,$household_id=NULL)
{
        /**
 *
 * Edit a person
 * 
 * @author  Andy Moyle
 * @param    $people_id,$household_id
 * @return   
 * @version  0.11
 *
 * 0.11 added photo upload 2012-02-24
 * 
 */    
    
    global $wpdb,$people_type,$member_type,$departments,$current_user,$church_admin_version;
    get_currentuserinfo();
	
    $wpdb->show_errors();
    
	$hopeteamjobs=array();
		$hts=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
		if(!empty($hts))
		{
		
			foreach($hts AS $ht){$hopeteamjobs[$ht->hope_team_id]=$ht->job;}
		}
	
	
    
    if($people_id)$data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(empty($data)) $data = new stdClass();
	
    if(!empty($data->household_id))$household_id=$data->household_id;
    if(!empty($_POST['edit_people']))
    {//process
		if(empty($_POST['smallgroup_id']))$_POST['smallgroup_id']=NULL;
		if(empty($_POST['smallgroup_attendance']))
		{
		
			if(empty($data->smallgroup_attendance)){$_POST['smallgroup_attendance']=1;}else{$_POST['smallgroup_attendance']=$data->smallgroup_attendance;}
		}
		if(empty($household_id))
		{
			$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (lat,lng) VALUES("52.000","0.000")');
			$household_id=$wpdb->insert_id;
		}
		$sql=array();
		foreach($_POST AS $key=>$value)$sql[$key]=esc_sql(sanitize_text_field(stripslashes_deep($value)));
		$sql['sg']=esc_sql(maybe_serialize($_POST['smallgroup_id']));
		//handle date of birth
		if(!empty($_POST['date_of_birth'])&& church_admin_checkdate($_POST['date_of_birth'])){$dob=esc_sql($_POST['date_of_birth']);}else{$dob='0000-00-00';}
	
		if(!$people_id)$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.$sql['first_name'].'" AND prefix="'.$sql['prefix'].'" AND last_name="'.$sql['last_name'].'" AND email="'.$sql['email'].'" AND mobile="'.$sql['mobile'].'" AND sex="'.$sql['sex'].'" AND people_type_id="'.$sql['people_type_id'].'" AND  member_type_id="'.$sql['member_type_id'].'" AND date_of_birth="'.$dob.'" AND household_id="'.esc_sql($household_id).'"');
		$member_data=array();
		foreach($member_type AS $no=>$type)
		{
		
			if(!empty($_POST[$type]) && church_admin_checkdate($_POST[$type])){$member_data[$type]=$_POST[$type];}else{$member_data[$type]="0000-00-00";}
			//if($no==$_POST['member_type_id'] && $_POST['member_type_id']!=$data->member_type_id){$member_data[$type]=date('Y-m-d');}
			//if(!empty($_POST[$type])&&church_admin_checkdate($_POST['type'])){$member_data[$type]=$_POST[$type];}else{$member_data[$type]=NULL;}
		}
	
		$member_data=serialize($member_data);
	
		//handle upload
		if(empty($data->attachment_id)){$attachment_id=NULL;}else{$attachment_id=$data->attachment_id;}
	
		if(!empty($_FILES) && $_FILES['uploadfiles']['error'] == 0)
		{
			$filetmp = $_FILES['uploadfiles']['tmp_name'];
	
			//clean filename and extract extension
			$filename = $_FILES['uploadfiles']['name'];

			// get file info
			$filetype = wp_check_filetype( basename( $filename ), null );
			$filetitle = preg_replace('/\.[^.]+$/', '', basename( $filename ) );
			$filename = $filetitle . '.' . $filetype['ext'];
			$upload_dir = wp_upload_dir();
			/**
			* Check if the filename already exist in the directory and rename the
			* file if necessary
			*/
			$i = 0;
			while ( file_exists( $upload_dir['path'] .'/' . $filename ) )
			{
				$filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
				$i++;
			}
	    
			$filedest = $upload_dir['path'] . '/' . $filename;
	    
			move_uploaded_file($filetmp, $filedest);
			$attachment = array('post_mime_type' => $filetype['type'],'post_title' => $filetitle,'post_content' => '','post_status' => 'inherit');
			$attachment_id = wp_insert_attachment( $attachment, $filedest );
	    
            require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attachment_id, $filedest );
	    
            wp_update_attachment_metadata( $attachment_id,  $attach_data );
		
		}// end handle upload
		if(!church_admin_level_check('Directory'))
		{//keep old values as not able to edit...
			$sql['member_type_id']=$data->member_type_id;
			if(empty($data->member_type_id))
			{
				//no current member level data so give same level as editing user!
				$sql['member_type_id']=$wpdb->get_var('SELECT member_type_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($current_user->ID).'"');
			}
			$member_data=$data->member_data;
			
		}
		if(!empty($_POST['prayer_chain'])){$prayer_chain=1;}else{$prayer_chain=0;}
		if(empty($sql['kidswork_override'])){$sql['kidswork_override']=NULL;}
		if(!empty($_POST['ID'])&&ctype_digit($_POST['ID'])){$sql['user_id']=$_POST['ID'];}else{$sql['user_id']='';}
		if($people_id)
		{//update
			
			$query='UPDATE '.CA_PEO_TBL.' SET prayer_chain="'.$prayer_chain.'",kidswork_override="'.$sql['kidswork_override'].'", user_id="'.$sql['user_id'].'",first_name="'.$sql['first_name'].'" ,prefix="'.$sql['prefix'].'", last_name="'.$sql['last_name'].'" , email="'.$sql['email'].'" , mobile="'.$sql['mobile'].'" , sex="'.$sql['sex'].'" ,people_type_id="'.$sql['people_type_id'].'", member_type_id="'.$sql['member_type_id'].'" , date_of_birth="'.$dob.'",member_data="'.esc_sql($member_data).'",smallgroup_id="'.$sql['sg'].'",smallgroup_attendance="'.$sql['smallgroup_attendance'].'", attachment_id="'.$attachment_id.'",user_id="'.$sql['user_id'].'" WHERE household_id="'.esc_sql($household_id).'" AND people_id="'.esc_sql($people_id).'"';
		    $wpdb->query($query);
			
			
		}//end update
		else
		{
		$sql='INSERT INTO '.CA_PEO_TBL.' ( first_name,prefix,last_name,email,mobile,sex,people_type_id,member_type_id,date_of_birth,household_id,member_data,smallgroup_id,smallgroup_attendance,attachment_id,user_id,prayer_chain,kidswork_override) VALUES("'.$sql['first_name'].'","'.$sql['prefix'].'","'.$sql['last_name'].'" , "'.$sql['email'].'" , "'.$sql['mobile'].'" , "'.$sql['sex'].'" ,"'.$sql['people_type_id'].'", "'.$sql['member_type_id'].'" , "'.$dob.'" , "'.esc_sql($household_id).'","'.esc_sql($member_data).'" ,"'.$sql['sg'].'","'.$sql['smallgroup_attendance'].'","'.$attachment_id.'","'.$sql['user_id'].'","'.$prayer_chain.'","'.$sql['kidswork_override'].'")';
		
			$wpdb->query($sql);
			$people_id=$wpdb->insert_id;
		}
		if(!empty($_POST['create_user']))
		{
			church_admin_create_user($people_id,$household_id);
		}
		//new small group
		if(!empty($_POST['group_name']))
		{
			$check=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE group_name="'.$sql['group_name'].'" AND whenwhere="'.$sql['whenwhere'].'"');
		
			if(!empty($check))
			{//update
				if(!empty($check->leader))$leaders=maybe_unserialize($check->leader);
				if(is_array($leaders)&&!in_array($people_id,$leaders)) {$leaders[]=$people_id;}else{$leaders=array(1=>$people_id);}
				$ldrs=esc_sql(maybe_serialize($leaders));
				$query='UPDATE '.CA_SMG_TBL.' SET leader="'.$ldrs.'",group_name="'.$sql['group_name'].'",whenwhere="'.$sql['whenwhere'].'" WHERE id="'.esc_sql($check->id).'"';
				$wpdb->query($query);
				$sg_id=$check->id;
			}//end update
			else
			{//insert
				$leaders=esc_sql(maybe_serialize(array(1=>$people_id)));
				$query='INSERT INTO  '.CA_SMG_TBL.' (group_name,leader,whenwhere) VALUES("'.$sql['group_name'].'","'.$leaders.'","'.$sql['whenwhere'].'")';
				$wpdb->query($query);
				$sg_id=$wpdb->insert_id;
			}//insert
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET smallgroup_id="'.esc_sql($sg_id).'" WHERE people_id="'.$people_id.'"');	
		}
		
	if(church_admin_level_check('Directory'))
	{//only authorised people
		//update meta
		
		$deleted=$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="ministry"');
	
		//if new small group then add small group leader to person's meta
		if(!empty($_POST['group_name'])){church_admin_update_department('1',$people_id,'ministry');}
		if(!empty($_POST['department']))
		{ 
			foreach($_POST['department'] AS $a=>$key)
			{
				if(array_key_exists($key,$departments)){church_admin_update_department($key,$people_id,'ministry');}
			}
		}
		if(!empty($_POST['hope_team']))
		{ 
			foreach($_POST['hope_team'] AS $a=>$key)
			{
				if(array_key_exists($key,$hopeteamjobs)){church_admin_update_department($key,$people_id,'hope_team');}
			}
		}
		if(!empty($_POST['new_department'])&&$_POST['new_department']!='Add a new ministry')
		{
	    
			if(!in_array(stripslashes($_POST['new_department']),$departments))
			{
				$new=stripslashes($_POST['new_department']);
				$departments[]=$new;
				update_option('church_admin_departments',$departments);
				church_admin_update_department(array_search($new,$departments,'ministry'),$people_id);
			}
		}
	}//only authorised people
		//end of process into db, now output...		
		
		
		
		echo'<div class="updated fade"><p><strong>'.__('Person Edited','church-admin').' <br/>';
		if(church_admin_level_check('Directory') &&!empty($sql['member_type_id'])) echo'<a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&amp;member_type_id='.$sql['member_type_id'].'">'.__('Back to Directory','church-admin').'</a>';
		echo'</strong></td></tr></div>';
	
		church_admin_display_household($household_id);
		
	
		
    }//end process
    else
    {//form
	
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		//first name
		echo'<table class="form-table"><tbody><tr><th scope="row">'.__('First Name','church-admin').'</th><td><input type="text" name="first_name" ';
		if(!empty($data->first_name)) echo ' value="'.esc_html($data->first_name).'" ';
		echo'/></td></tr>';
		//prefix
		echo'<tr><th scope="row">'.__('Prefix (e.g.van der)','church-admin').'</th><td><input type="text" name="prefix" ';
		if(!empty($data->prefix)) echo ' value="'.esc_html($data->prefix).'" ';
		echo'/></td></tr>';
		//last name
		echo'<tr><th scope="row">'.__('Last Name','church-admin').'</th><td><input type="text" name="last_name" ';
		if(!empty($data->last_name)) echo ' value="'.esc_html($data->last_name).'" ';
		echo'/></td></tr>';
		//photo
		echo'<tr><th scope="row">'.__('Photo','church-admin').'</th><td><input type="file" id="photo" name="uploadfiles" size="35" class="uploadfiles" /></td></tr>';
		if(!empty($data->attachment_id))
		{//photo available
			echo '<tr><th scope="row">Current Photo</th><td>';
			echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb' );
			echo'</td></tr>';
		}//photo available
		else
		{
			echo '<tr><th scope="row">&nbsp;</th><td>';
			echo '<img src="'.plugins_url('/images/default-avatar.jpg',dirname(__FILE__) ).'" width="75" height="75"/>';
			echo '</td></tr>';
		}
		//email
		echo'<tr><th scope="row">'.__('Email Address','church-admin').'</th><td><input type="text" name="email" ';
		if(!empty($data->email)) echo ' value="'.esc_html($data->email).'" ';
		echo'/></td></tr>';
		//mobile
		echo'<tr><th scope="row">'.__('Mobile','church-admin').'</th><td><input type="text" name="mobile" ';
		if(!empty($data->mobile)) echo ' value="'.esc_html($data->mobile).'" ';
		echo'/></td></tr>';
		//sex
		echo'<tr><th scope="row">Sex</th><td>'.__('Male','church-admin').' <input type="radio" name="sex" value="1"';
		if(!empty($data->sex) && $data->sex==1) echo' checked="checked" ';
		echo ' /> '.__('Female','church-admin').' <input type="radio" name="sex" value="0"';
		if(empty($data->sex)||$data->sex==0)  echo' checked="checked" ';
		echo'/></td></tr>';
		//people_type
		echo'<tr><th scope="row">'.__('People Type','church-admin').'</th><td><select name="people_type_id">';
		foreach($people_type AS $key=>$value)
		{
			echo'<option value="'.$key.'" ';
			if(!empty($data->people_type_id))selected($key,$data->people_type_id);
			echo'>'.$value.'</option>';
		}
		echo'</select></td></tr>';
		//date of birth
		echo'<tr><th scope="row">'.__('Date of Birth','church-admin').'</th><td><input type=="text" name="date_of_birth" class="date_of_birth" ';
		if(!empty($data->date_of_birth)&&$data->date_of_birth!='0000-00-00') echo ' value="'.esc_html($data->date_of_birth).'" ';
		echo'/></td></tr>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.date_of_birth\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
	if(church_admin_level_check('Directory'))
	{//only available to authorised people
		$kidswork_groups=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL);
		if(!empty($kidswork_groups))
		{//add an override	
			echo'<tr><th scope="row">'.__('Override kids work group','church-admin').'</th><td><select name="kidswork_override">';
			echo'<option value="" '.selected($data->kidswork_override,NULL).'>'.__('Assign by age automatically','church-admin').'</option>';
			foreach($kidswork_groups AS $kwgp)echo'<option value="'.esc_html($kwgp->id).'" '.selected($data->kidswork_override,$kwgp->id).'>'.esc_html($kwgp->group_name).'</option>';
			echo'</select></td></tr>';
		}
	echo'<tr><th scope="row">'.__('Current Member Type','church-admin').'</th><td><span style="display:inline-block">';
	$first=$option='';
	foreach($member_type AS $key=>$value)
	{
		echo'<input type="radio" name="member_type_id" value="'.esc_html($key).'"';
			if(!empty($data->member_type_id)&&$data->member_type_id==$key)echo' checked="checked" ';
			echo ' />'.esc_html($value).'<br/>';
	   
		}
	
		echo'</span></td></tr>';
	
	
		if(!empty($data->member_type_id))$prev_member_types=unserialize($data->member_data);
	
	    echo'<tr><th scope="row">'.__('Dates of Member Levels','church-admin').'</th><td><span style="display:inline-block">	';
	    foreach($member_type AS $key=>$value)
	    {
			//if no value for member type date make sure no value is given
			if(empty($prev_member_types[$value])||$prev_member_types[$value]=='0000-00-00'|| $prev_member_types[$value]=='1970-01-01'){$date='';}else{$date=$prev_member_types[$value];}
			echo '<span style="float:left;width:150px">'.$value.'</span> <input type="text" id="'.sanitize_title($value).'" name="'.esc_html($value).'" value="'.esc_html($date).'"/><br style="clear:left"/>';
			//javascript to bring up date picker
			echo'<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'#'.sanitize_title($value).'\').datepicker({dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"});});</script>';
			//javascript to bring up date picker
			}
			echo'</span></td></tr>';
	
		echo'<tr><th scope="row">'.__('Ministries','church-admin').'</th><td><span style="display:inline-block">';
		if(!empty($departments))
		{
			asort($departments);
			foreach($departments AS $key=>$value)
			{
				echo'<span style="float:left;width:150px">'.$value.'</span><input type="checkbox" name="department[]" value="'.esc_html($key).'" ';
				if(!empty($data->people_id))
				{
					$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND meta_type="ministry" AND department_id="'.esc_sql($key).'"');
					if($check)echo ' checked="checked" ';
				}
				echo '/><br style="clear:left"/>';
			}
		}
		echo '<input type="text" name="new_department" value="'.__('Add a new ministry','church-admin').'" onfocus="javascript:this.value=\'\';"/></td></tr>';
		//hope team
		echo'<tr><th scope="row">'.__('Hope Team','church-admin').'</th><td><span style="display:inline-block">';
		
		foreach($hopeteamjobs AS $key=>$value)
			{
				echo'<span style="float:left;width:150px">'.$value.'</span><input type="checkbox" name="hope_team[]" value="'.esc_html($key).'" ';
				if(!empty($data->people_id))
				{
					$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND meta_type="hope_team" AND department_id="'.esc_sql($key).'"');
					if($check)echo ' checked="checked" ';
				}
				echo '/><br style="clear:left"/>';
			}
	}//only available to authorised people
	//small group
	
		if(!empty($data->smallgroup_id))$sg=maybe_unserialize($data->smallgroup_id);
		if(!empty($sg)&&!is_array($sg))$sg=array(0=>$data->smallgroup_id);
		echo'<tr><th scope="row">'.__('Small Group','church-admin').'</th><td><span style="display:inline-block">';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		$first=$option='';
		foreach($smallgroups AS $smallgroup)
		{
			
			echo'<input type="checkbox" name="smallgroup_id[]" value="'.esc_html($smallgroup->id).'"';
			if(!empty($sg)&&in_array($smallgroup->id,$sg)) echo' checked="checked" ';
		echo'/>'.$smallgroup->group_name.'<br/>';
		}
		echo '</span></td></tr>';
	if(church_admin_level_check('Directory'))
	{//only authorised people to edit wordpress user or create new small groups or adjust attendance indicator
		if(empty($data->smallgroup_attendance))$data->smallgroup_attendance=1;
		echo'<tr><th scope="row">'.__('Small group attendance','church-admin').'</th><td><input type="radio" name="smallgroup_attendance" value="1" '.checked('1',$data->smallgroup_attendance,0).'/>'.__('Regular','church-admin').' &nbsp;<input type="radio" name="smallgroup_attendance" value="2" '.checked('2',$data->smallgroup_attendance,0).'/>'.__('Irregular','church-admin').' &nbsp; <input type="radio" name="smallgroup_attendance" value="3" '.checked('3',$data->smallgroup_attendance,0).'/>'.__('Connected','church-admin').' &nbsp;</td></tr>';
	
		echo'<tr><th scope="row">Or create new Small Group</th><td><span style="display:inline-block"><span style="float:left;width:150px">Group Name</span><input type="text" name="group_name"/><br style="clear:left"/><span style="float:left;width:150px">Leader?</span><input type="checkbox" name="leading"/><br style="clear:left;"/><span style="float:left;width:150px">Where &amp; When</span><input type="text" name="whenwhere"/></span></td></tr>';
		
		if(!empty($data->user_id ))
		{
			echo'<tr><th scope="row">'.__('Wordpress User','church-admin').'</th><td><input type="hidden" name="ID" value="'.esc_html($data->user_id).'"/>';
			$user_info=get_userdata($data->user_id);
			if(!empty($user_info)){echo $user_info->roles['0'].'</td></tr>';}
		}	
		elseif(!empty($people_id))
		{
			//check any user_ids stored
			$us=$wpdb->get_results('SELECT user_id FROM '.CA_PEO_TBL.' WHERE user_id!="0"');
			if(!empty($us))	{$where='WHERE `ID` NOT IN (SELECT user_id FROM '.CA_PEO_TBL.')';}else{$where='';}
			$users=$wpdb->get_results('SELECT user_login,ID FROM '.$wpdb->prefix.'users '.$where);
				
			if(!empty($users))
			{
					echo'<tr><th scope="row">Choose a Wordpress account to associate</th><td><select name="ID"><option value="">Select a user...</option>';
					foreach($users AS $user) echo'<option value="'.esc_html($user->ID).'">'.esc_html($user->user_login).'</option>';
					echo'</select></td></tr>';
			}
			echo'<tr><th scope="row">Create a new Wordpress User</th><td><input type="checkbox" name="create_user" value="yes"/></td></tr>';
		}
		
		echo'<tr><th scope="row">'.__('Prayer Chain','church-admin').'</th><td><input type="checkbox" name="prayer_chain"';
		if(!empty($data->prayer_chain))echo ' checked="checked" ';
		echo'/></td></tr>';
	}//only authorised people to edit wordpress user
		echo'<tr><th scope="row"><input type="hidden" name="edit_people" value="yes"/><input type="submit" value="'.__('Save Details','church-admin').'&raquo;" /></td></tr></tbody></table></form>';
    }
   
}
function church_admin_delete_people($people_id=NULL,$household_id)
{
    //deletes person with specified people_id
    global $wpdb;
    $wpdb->show_errors();
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'" ');
    $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'" AND meta_type="ministry"');
    echo'<div class="updated fade"><p><strong>'.__('Person Deleted','church-admin').'</strong></td></tr></div>';
	church_admin_display_household($household_id);
 
    
}

function church_admin_address_form($data,$error)
{
    //echos form contents where $data is object of address data and $error is array of errors if applicable
    if(empty($data))$data=(object)'';
    $out='';
    if(!empty($errors))$out.='<p>'.__('There were some errors marked in red','church-admin').'</p>';
    $out.='<script type="text/javascript"> var beginLat =';
    if(!empty($data->lat)) {$out.= esc_html($data->lat);}else {$out.='51.50351129583287';}
$out.= '; var beginLng =';
    if(!empty($data->lng)) {$out.=esc_html($data->lng);}else {$out.='-0.148193359375';}
    $out.=';</script>';
    
   
    $out.= '<table class="form-table"><tbody><tr><th scope="row">'.__('Address','church-admin').'</th><td><input type="text" id="address" name="address" ';
    if(!empty($data->address)) $out.=' value="'.esc_html($data->address).'" ';
    if(!empty($error['address'])) $out.= ' class="red" ';
    $out.= '/></td></tr>';
    if(!isset($data->lng))$data->lng='51.50351129583287';
    if(!isset($data->lat))$data->lat='-0.148193359375';
    $out.= '<tr><th scope="row"><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a></th><td><span id="finalise" >Once you have updated your address, this map will show roughly where your address is.</span><input type="hidden" name="lat" id="lat" value="'.$data->lat.'"/><input type="hidden" name="lng" id="lng" value="'.$data->lng.'"/></td></tr><div id="map" style="width:500px;height:300px"></div>';
    $out.='<div style="clear:left"></div></tbody></table>';
    return $out;
    
}

function church_admin_display_household($household_id)
{
    global $wpdb,$people_type,$member_type;
    
    $departments=get_option('church_admin_departments');
    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    if(empty($add_row))$add_row=new stdClass();
    if($add_row)
    {//address stored
	if(!empty($add_row->address))
	{ 
		//old style <v0.554
		if(is_array(maybe_unserialize($add_row->address))) $address=implode(', ',array_filter(maybe_unserialize($add_row->address)));
		//>v0.553
		else{$address=$add_row->address;}
	}else{$address='Add Address';}
	 echo'<script type="text/javascript"> var beginLat =';
    if(!empty($data->lat)) {echo $data->lat;}else {echo '51.50351129583287';}
$out.= '; var beginLng =';
    if(!empty($data->lng)) {echo $data->lng;}else {echo'-0.148193359375';}
    echo';</script>';
	if(empty($add_row->lng)){$add_row->lng='-0.148193359375';}
	if(empty($add_row->lat)){$add_row->lat='51.50351129583287';}
	$map='<img src="http://maps.google.com/maps/api/staticmap?center='.$add_row->lat.','.$add_row->lng.'&zoom=15&markers='.$add_row->lat.','.$add_row->lng.'&size=500x300&sensor=false" alt="'.$address.'"/>';
	echo'<h2>Household Details</h2>';
	
	//grab people
	$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" ORDER BY people_order ASC,people_type_id ASC,date_of_birth ASC,sex DESC');
	if($people)
	{//are people
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></td></tr>';
		echo '<p>'.__('You can drag and drop to sort people display order','church-admin').'</td></tr>';
	if(church_admin_level_check('Directory'))
	{
		echo'<table id="sortable" class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></tfoot><tbody  class="content">';
	}
	else
	{
		echo'<table id="sortable" class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Hope Team','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th></tr></tfoot><tbody  class="content">';
	
	}
	    foreach ($people AS $person)
	    {
		switch($person->sex)
		{
		    case 0:$sex=__('Female','church-admin');break;
		    case 1:$sex=__('Male','church-admin');break;
		}
		//ministries
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="ministry"');
		$department=array();
		foreach($result AS $row)
		{
				if(!empty($departments[$row->department_id]))$department[]=$departments[$row->department_id];
		}
		asort($department);
		//hopeteam
		$hopeteamjobs=array();
		$hts=$wpdb->get_results('SELECT job,hope_team_id FROM '.CA_HOP_TBL);
		if(!empty($hts))
		{
		
			foreach($hts AS $ht){$hopeteamjobs[$ht->hope_team_id]=$ht->job;}
		}
		
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="hope_team"');
		$hopeteam=array();
		foreach($result AS $row)
		{
				if(!empty($hopeteamjobs[$row->department_id]))$hopeteam[]=$hopeteamjobs[$row->department_id];
		}
		asort($hopeteam);
		if($person->user_id)
		{
		    $user_info=get_userdata($person->user_id);
		    if(!empty($user_info))$person_user= $user_info->user_login.'<br/>('.church_admin_get_capabilities($person->user_id).')';
		}
		else
		{
		    $person_user='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_create_user&amp;people_id='.$person->people_id.'&amp;household_id='.$person->household_id,'create_user').'">'.__('Create WP User','church-admin').'</a></td></tr>';
		}
		if(!empty($person->attachment_id))
		{//photo available
		    $photo= wp_get_attachment_image( $person->attachment_id,'ca-people-thumb' );
		}//photo available
		else
		{
		    $photo= '<img src="'.plugins_url('images/default-avatar.jpg',dirname(__FILE__) ) .'" width="75" height="75"/>';
		}
		if(!empty($person->prefix)){$prefix=$person->prefix.' ';}else{$prefix='';}
		echo'<tr class="sortable-row" id="'.esc_html($person->people_id).'"><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'edit_people').'">'.__('Edit','church-admin').'</a></td><td><a onclick="return confirm(\'Are you sure you want to delete '.esc_html($person->first_name).' '.esc_html($prefix).esc_html($person->last_name).'?\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_people&amp;household_id='.$household_id.'&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'delete_people').'">'.__('Delete','church-admin').'</a></td><td>'.$photo.'</td><td>'.esc_html($person->first_name).' '.esc_html($prefix).esc_html($person->last_name).'</a></td><td>'.$sex.'</td><td>'.$people_type[$person->people_type_id].'</td><td>'.esc_html($member_type[$person->member_type_id]).'</td><td>'.implode(',<br/>',$department).'</td><td>'.implode(',<br/>',$hopeteam).'</td><td>';
		if(is_email($person->email)){echo '<a href="mailto:'.esc_url($person->email).'">'.esc_html($person->email).'</a>';}else{echo esc_html($person->email);}
		echo '</td><td>'.esc_html($person->mobile).'</td>';
		if(church_admin_level_check('Directory'))
		{//only Directory level users gets these columns!
			echo '<td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_move_person&amp;people_id='.$person->people_id,'move_person').'">Move</a></td>';
			if(!empty($person_user)){echo'<td>'.$person_user.'</td>';}else{echo'<td>&nbsp;</td>';}
			
		}
		echo'</tr>';
	    }
	    echo'</tbody></table>';
		   echo '
    <script type="text/javascript">
  
 jQuery(document).ready(function($) {
 
    var fixHelper = function(e,ui){
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };
    var sortable = $("#sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order
        
       
				var Order = "order="+$(this).sortable(\'toArray\').toString();

        console.log(Order);
        
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=people",
            type: "post",
            data:  Order,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {
                console.log("Saved.");
            }
        });}
});
$("#sortable tbody.content").disableSelection();
});

   
   
    </script>
';

	}//end are people
	else
	{//no people
	    echo'<p>'.__('There are no people stored in that household yet','church-admin').'</td></tr>';
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></td></tr>';
	}//no people
	//end grab people
	if(!empty($add_row->phone))echo'<tr><th scope="row">'.__('Homephone','church-admin').' </th><td>'.esc_html($add_row->phone).'</td></tr>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$household_id,'edit_household').'">'.__('Edit Address','church-admin').'</a>: '.esc_html($address).'</td></tr>';
	echo'<p>'.$map.'</td></tr>';
    }//end address stored
    else
    {
	echo'<div class="updated fade"><p><strong>'.__('No Household found','church-admin').'</strong></td></tr></div>';
	
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
		$address='';
		$wpdb->query('INSERT INTO '.CA_HOU_TBL.'(member_type_id,address)VALUES("1","'.$address.'")');
		$household_id=$wpdb->insert_id;
		$wpdb->query('INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,email,household_id,user_id,member_type_id,people_type_id,smallgroup_id,sex) VALUES("'.$user_info->first_name.'","'.$user_info->last_name.'","'.$user_info->user_email.'","'.$household_id.'","'.$row->ID.'","1","1","0","1")');
	    }
	}
	
	echo'<div class="updated fade"><p><strong>'.__('Wordpress Users migrated','church-admin').'</strong></td></tr></div>';
    }
   
    church_admin_address_list();
}

function church_admin_move_person($id)
{
    global $wpdb;
	$wpdb->show_errors();
    $data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($id).'"');
    if(!empty($data))
    {
	if(!empty($_POST['move_person']))
	{
	    if(!empty($_POST['create']))
		{
			$sql='INSERT INTO '.CA_HOU_TBL.' ( address,lat,lng,phone ) SELECT address,lat,lng,phone FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($data->household_id).'";';
			
			$wpdb->query($sql);
			$household_id=$wpdb->insert_id;
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET household_id="'.esc_sql($household_id).'" WHERE people_id="'.esc_sql($id).'"');
			echo'<div class="updated fade"><p><strong>'.esc_html($data->first_name).' '.esc_html($data->last_name).' has been moved to a new household with the same address details!</strong></td></tr></div>';
			
		}
		else
		{
			//remove household entry if only one person was in it.
			$no=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
			if($no==1)$wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
			//move the person to the new household
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET household_id="'.esc_sql($_POST['household_id']).'" WHERE people_id="'.esc_sql($id).'"');
			echo'<div class="updated fade"><p><strong>'.esc_html($data->first_name).' '.esc_html($data->last_name).' has been moved!</strong></td></tr></div>';
			$household_id=(int)$_POST['household_id'];
		}
	    church_admin_display_household($household_id);
	     require_once(plugin_dir_path(dirname(__FILE__)).'includes/admin.php');
	    add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
	    do_meta_boxes('church-admin','advanced',null);
	}
	else
	{
	    echo'<div class="wrap"><h2>Move '.esc_html($data->first_name).' '.esc_html($data->last_name).'</h2>';
	    
	    $results=$wpdb->get_results('SELECT a.last_name, a.household_id,b.member_type FROM '.CA_PEO_TBL.' a, '.CA_MTY_TBL.' b WHERE b.member_type_id=a.member_type_id GROUP BY a.household_id,a.last_name ORDER BY a.last_name');
	    if(!empty($results))
	    {
		echo'<form action="" method="post">';
		echo'<tr><th scope="row">Create a new household with same address</th><td><input type="checkbox" name="create" value="yes"/></td></tr>';
		echo'<tr><th scope="row">Move to household</th><td><select name="household_id"><option value="">Select a new household...</option>';
		foreach($results AS $row)
		{
		    echo'<option value="'.esc_html($row->household_id).'">'.esc_html($row->last_name).' ('.$row->member_type.')</option>';
		}
		echo'</select></td></tr>';
		echo'<p><input type="hidden" name="move_person" value="yes"/><input type="submit" class="primary-button" value="Move person"/></td></tr>';
		echo'</form></div>';
	    }
	}
    }
}


function church_admin_create_user($people_id,$household_id)
{
    global $wpdb;
    if(!$people_id)
    {
	echo"<p>'.__('Nobody was specified','church-admin').'</td></tr>";
    }
    else
    {//people_id
	
	$user=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(empty($user))
	{
	    echo'<div class="updated fade">'.__("That people record doesn't exist",'church-admin').'</td></tr></div>';
	}
	else
	{//user exits in plugin db
	    $user_id=email_exists($user->email);
	    if(!empty($user_id) && $user->user_id==$user_id)
	    {//wp user exists and is in plugin db
		echo'<div class="updated fade">'.__('User already created','church-admin').'</td></tr></div>';
		church_admin_display_household($household_id);
	    }
	    elseif(!empty($user_id) && $user->user_id!=$user_id)
	    {//wp user exists, update plugin
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
			echo'<div class="updated fade">'.__('User updated','church-admin').'</td></tr></div>';
		
	    }
	    else
	    {//wp user needs creating!
		//create unique username
		$username=strtolower(str_replace(' ','',$user->first_name).str_replace(' ','',$user->last_name));
		$x='';
		while(username_exists( $username.$x ))
		{
		    $x+=1;
		}
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $username.$x, $random_password, $user->email );
		
		$message='<p>'.__('The web team at','church-admin').' <a href="'.site_url().'">'.site_url().'</a>'.__('have just created a user login for you','church-admin').'</td></tr>';
		$message.='<p>'.__('Your username is','church-admin').' <strong>'.esc_html($username.$x).'</strong></td></tr>';
		$message.='<p>'.__('Your password is','church-admin').' <strong>'.$random_password.'</strong></td></tr>';
		echo '<div class="updated fade">'.__('User created with username','church-admin').' <strong>'.esc_html($username.$x).'</strong>,'.__('password','church-admin').': <strong>'.$random_password.'</strong> '.__('and this message was queued to them','church-admin').'<br/>'.esc_html($message);
		$headers=array();
		$headers[] = 'From: Web team at '.site_url() .'<'.get_option('admin_email').'>';
		$headers[] = 'Cc: Web team at '.site_url() .'<'.get_option('admin_email').'>';
		if(wp_mail($user->email,'Login for '.site_url(),$message,$headers))
		{
		    echo'<strong>'.__('Email sent successfully','church-admin').'</strong></div>';
		}
		else
		{
		    echo'<strong>'.__('Email NOT sent successfully','church-admin').'</strong></div>';
		}
		$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
		church_admin_display_household($household_id);
	    }//wp user needs creating!
    
	   
	    
	}//user exits in plugin db
    
    
    }//people_id
}//function church_admin_create_user
function church_admin_get_capabilities($id)
{
    if(empty($id))return FALSE;
    $user_info=get_userdata($id);
    if(empty($user_info))return FALSE;
    $cap=$user_info->roles;
    
	if (in_array('subscriber',$cap))return 'Subscriber';
	if (in_array('author',$cap))return 'Author';
	if (in_array('editor',$cap))return  'Editor';
	if (in_array('administrator',$cap)) return 'Administrator';
	return FALSE;
}

function church_admin_search($search)
{
    global $wpdb;
    $s=esc_sql(stripslashes($search));
    $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")||mobile LIKE("%'.$s.'%")';
    
    $results=$wpdb->get_results($sql);
    if(!$results)
    {
	$sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%")';
    
	$results=$wpdb->get_results($sql);
    }
    if($results)
    {
	    echo'<div class="wrap church_admin"><h2>'.__('Address List','church-admin').'</h2><div class="updated fade"><p><strong>'.sprintf(__('Your search for %1$s yielded %2$s results','church-admin'),esc_html($s),$wpdb->num_rows).'</strong></td></tr></div>';
	    echo '<table class="widefat"><thead><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></tfoot><tbody>';
	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT first_name,last_name,people_type_id,people_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    foreach($people_results AS $people)
	    {
		if($people->people_type_id=='1'){$last_name=$people->last_name; $adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>' ;}
		
	    }
	    $adult=implode(" & ",$adults);
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    if(!empty($add_row->address))$add=esc_html($add_row->address);
	    
	    if(!empty($add)){$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">'.esc_html($add).'</a>';}else{$address='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$row->household_id,'edit_household').'">Add Address</a>';}
	    
	    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_household&amp;household_id='.$row->household_id,'delete_household').'">Delete</a>';
	    echo '<tr><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$row->household_id,'display_household').'">'.esc_html($last_name).'</a></td><td>'.$adult.' '.$kids.'</td><td>'.$address.'</td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	echo '</tbody></table>';
	
	
    }
    else
    {
	echo'<div class="updated fade"><p>'.__('Search','church-admin').' '.$s.' '.__('not found','church-admin').'</td></tr></div>';
	church_admin_address_list('1');
    }
}




?>