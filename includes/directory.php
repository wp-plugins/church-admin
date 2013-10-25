<?php
//Address Directory Functions
function church_admin_address_list($member_type_id=1)
{
    global $wpdb,$member_type;
    $wpdb->show_errors();

   
    
    //show header
    /* Add screen option: user can choose between 1 or 2 columns (default 2) */
    add_screen_option('layout_columns', array('max' => 2, 'default' => 1) );
    echo'<div class="wrap" id="church-admin">
	<div id="icon-index" class="icon32"><br/></div><h2>Address List for '.$member_type[$member_type_id].'</h2>
	<div id="poststuff"><div id="post-body" class="metabox-holder columns-1">';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
    echo'<form  method="get" action="">';
	wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); 
	wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
	echo'</form>';
	add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
	do_meta_boxes('church-admin','advanced',null);

    //end show header
    
    
    //grab address list in order
    $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'" ';
   
    $result = $wpdb->get_var($sql);
    $items=$wpdb->num_rows;
    
    
    // number of total rows in the database
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'pagination.class.php');
    if($items > 0)
    {
	
	$p = new pagination;
	$p->items($items);
	$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
	$p->target("admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&amp;member_type_id=".$member_type_id);
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
     
    
    //prepare WHERE clause using given Member_type_id
    $sql='SELECT * FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"  GROUP BY household_id ORDER BY last_name ASC '.$limit;
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

	echo '<table class="widefat"><thead><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Delete','church-admin').'</th><th>'.__('Last name','church-admin').'</th><th>'.__('First Name(s)','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Last Update','church-admin').'</th></tr></tfoot><tbody>';
	foreach($results AS $row)
	{
	    
	    //grab address
	    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	     //grab people
	    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=$children=array();
	    $prefix='';
	    foreach($people_results AS $people)
	    {
		
		if(empty($people->last_name))$people->last_name=__('Add Surname','church-admin');
		if(empty($people->first_name))$people->first_name=__('Add Firstname','church-admin');
		if($people->people_type_id=='1'){$last_name=$people->last_name; $adults[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>';}else{$children[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$row->household_id.'&amp;people_id='.$people->people_id,'edit_people').'">'.esc_html($people->first_name).'</a>' ;}
		if(!empty($people->prefix)){$prefix=$people->prefix.' ';}
	    }
	    
	    if(!empty($adults)){$adult=implode(" & ",$adults);}else{ $adult=__("Add Name",'church-admin');}
	    if(!empty($children)){$kids=' ('.implode(", ",$children).')';}else{$kids='';}
	    
	    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_household&amp;household_id='.$row->household_id,'delete_household').'">'.__('Delete','church-admin').'</a>';
	    if(empty($add_row->address))$add_row->address=__('Add Address','church-admin');
	    
	    echo '<tr><td>'.$delete.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$row->household_id,'display_household').'">'.$prefix.esc_html($last_name).'</a></td><td>'.$adult.' '.$kids.'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$row->household_id,'display_household').'">'.esc_html($add_row->address).'</a></td><td>'.mysql2date('d/M/Y',$add_row->ts).'</td></tr>';
	    
	    
	}
	echo '</tbody></table>';
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo $p->show();  
    
    }//end of items>0
    }	

    
	echo '</div><!--post-body--></div><!--poststuff--></div><!--wrap-->';
    
}

function church_admin_edit_household($household_id=NULL)
{
    global $wpdb,$member_type,$church_admin_version;
    $wpdb->show_errors();
    ?>
    <div class="wrap" id="church-admin">
	<div id="icon-index" class="icon32"><br/></div><h2>Church Admin Plugin v<?php echo $church_admin_version;?> - Edit Household Address</h2>
	<div id="poststuff">
    <?php
    $member_type_id=$wpdb->get_var('SELECT member_type_id FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"  ORDER BY people_type_id ASC LIMIT 1');
    if(!empty($household_id)){$data=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');}else{$data=NULL;}
    if(!empty($_POST['edit_household']))
    {//process form
	
	$form=array();
	foreach ($_POST AS $key=>$value)$sql[$key]=esc_sql(stripslashes($value));
	if(!$household_id)$household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.$sql['address'].'" AND lat="'.$sql['lat'].'" AND lng="'.$sql['lng'].'" AND phone="'.$sql['phone'].'"');
	if(!$household_id)
	{//insert
	    $success=$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone) VALUES("'.$sql['address'].'", "'.$sql['lat'].'","'.$sql['lng'].'","'.$sql['phone'].'" )');
	    $household_id=$wpdb->insert_id;
	}//end insert
	else
	{//update
	   $success=$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.$sql['address'].'" , lat="'.$sql['lat'].'" , lng="'.$sql['lng'].'" , phone="'.$sql['phone'].'" WHERE household_id="'.esc_sql($household_id).'"');
	}//update
	if($success)
	{
	    echo '<div class="updated fade"><p><strong>'.__('Address saved','church-admin').' <br/><a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&amp;member_type_id='.$member_type_id.'">'.__('Back to Directory','church-admin').'</a></strong></p></div>';
	}
	    echo'<div id="post-body" class="metabox-holder columns-2"><!-- meta box containers here -->';
		echo'<form  method="get" action="">'. wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
		//church_admin_collapseBoxForUser($current_user->ID,"church-admin-people-functions");
			add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
			do_meta_boxes('church-admin','advanced',null);
		echo'</form></div> <script type="text/javascript">
		jQuery(document).ready(function($){$(".if-js-closed").removeClass("if-js-closed").addClass("closed");
			       
				postboxes.add_postbox_toggles( "church-admin");
				});
		</script><!-- End Meta Box Section-->';
		
		echo'<div class="updated fade"><p><strong>'.__('Person Edited','church-admin').' <br/><a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&amp;member_type_id='.$data->member_type_id.'">'.__('Back to Directory','church-admin').'</a></strong></p></div>';
	
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
    
    echo '<p><label>'.__('Phone','church-admin').'</label><input type="text" name="phone" ';
	if(!empty($data->phone)) echo ' value="'.$data->phone.'"';
    if(!empty($errors['phone']))echo' class="red" ';
    echo '/></p>';
	echo'<p class="submit"><input type="hidden" name="edit_household" value="yes"/><input type="submit" value="'.__('Save Address','church-admin').'&raquo;" /></p></form>';
    }//end household form

	?></div></div><?php
}
function church_admin_delete_household($household_id=NULL)
{
    //deletes household with specified household_id
    global $wpdb;
    $wpdb->show_errors();
   
    //delete people meta data
    $people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    foreach($people AS $person){$member_type_id=$person->member_type_id;$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($person->people_id).'"');}
    //delete from household and people tables
    $wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    echo'<div class="updated fade"><p><strong>'.__('Household Deleted','church-admin').'</strong></p></div>';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
	    add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
	    do_meta_boxes('church-admin','advanced',null);
    church_admin_address_list($member_type_id);
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
    /* Add screen option: user can choose between 1 or 2 columns (default 2) */
    add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
    
    
    ?>
    <div class="wrap" id="church-admin">
	<div id="icon-index" class="icon32"><br/></div><h2>Church Admin Plugin v<?php echo $church_admin_version;?> - Edit Person</h2>
	<div id="poststuff">
    <?php
    if($people_id)$data=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
  
    if(!empty($data->household_id))$household_id=$data->household_id;
    if(!empty($_POST['edit_people']))
    {//process
		if(empty($_POST['smallgroup_id']))$_POST['smallgroup_id']=0;
		if(empty($household_id))
		{
			$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (lat,lng) VALUES("52.000","0.000")');
			$household_id=$wpdb->insert_id;
		}
		$sql=array();
		foreach($_POST AS $key=>$value)$sql[$key]=esc_sql(stripslashes_deep($value));
	
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
	
		if(!empty($_POST['ID'])&&ctype_digit($_POST['ID'])){$sql['user_id']=$_POST['ID'];}else{$sql['user_id']='';}
		if($people_id)
		{//update
			$query='UPDATE '.CA_PEO_TBL.' SET user_id="'.$sql['user_id'].'",first_name="'.$sql['first_name'].'" ,prefix="'.$sql['prefix'].'", last_name="'.$sql['last_name'].'" , email="'.$sql['email'].'" , mobile="'.$sql['mobile'].'" , sex="'.$sql['sex'].'" ,people_type_id="'.$sql['people_type_id'].'", member_type_id="'.$sql['member_type_id'].'" , date_of_birth="'.$dob.'",member_data="'.esc_sql($member_data).'",smallgroup_id="'.$sql['smallgroup_id'].'", attachment_id="'.$attachment_id.'",user_id="'.$sql['user_id'].'" WHERE household_id="'.esc_sql($household_id).'" AND people_id="'.esc_sql($people_id).'"';
		    $wpdb->query($query);
			
			
		}//end update
		else
		{
			$wpdb->query('INSERT INTO '.CA_PEO_TBL.' ( first_name,prefix,last_name,email,mobile,sex,people_type_id,member_type_id,date_of_birth,household_id,member_data,smallgroup_id,attachment_id,user_id) VALUES("'.$sql['first_name'].'","'.$sql['prefix'].'","'.$sql['last_name'].'" , "'.$sql['email'].'" , "'.$sql['mobile'].'" , "'.$sql['sex'].'" ,"'.$sql['people_type_id'].'", "'.$sql['member_type_id'].'" , "'.$dob.'" , "'.esc_sql($household_id).'","'.esc_sql($member_data).'" ,"'.$sql['smallgroup_id'].'","'.$attachment_id.'","'.$sql['user_id'].'")');
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
	
		//update meta
		$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
		//if new small group then add small group leader to person's meta
		if(!empty($_POST['group_name'])){church_admin_update_department('1',$people_id);}
		if(!empty($_POST['department']))
		{ 
			foreach($_POST['department'] AS $a=>$key)
			{
				if(array_key_exists($key,$departments)){church_admin_update_department($key,$people_id);}
			}
		}
		if(!empty($_POST['new_department'])&&$_POST['new_department']!='Add a new ministry')
		{
	    
			if(!in_array(stripslashes($_POST['new_department']),$departments))
			{
				$departments[]=stripslashes($_POST['new_department']);
				update_option('church_admin_departments',$departments);
				church_admin_update_department(key($departments),$people_id);
			}
		}
		//end of process into db, now output...		
		
		echo'<div id="post-body" class="metabox-holder columns-2"><!-- meta box containers here -->';
		echo'<form  method="get" action="">'. wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		echo'</form>';
		
		require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
		//church_admin_collapseBoxForUser($current_user->ID,"church-admin-people-functions");
			add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
			do_meta_boxes('church-admin','advanced',null);
		echo'</form></div> <script type="text/javascript">
		jQuery(document).ready(function($){$(".if-js-closed").removeClass("if-js-closed").addClass("closed");
			       
				postboxes.add_postbox_toggles( "church-admin");
				});
		</script><!-- End Meta Box Section-->';
		
		echo'<div class="updated fade"><p><strong>'.__('Person Edited','church-admin').' <br/><a href="./admin.php?page=church_admin/index.php&amp;action=church_admin_address_list&amp;member_type_id='.$data->member_type_id.'">'.__('Back to Directory','church-admin').'</a></strong></p></div>';
	
		church_admin_display_household($household_id);
    }//end process
    else
    {//form
	
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		//first name
		echo'<p><label>'.__('First Name','church-admin').'</label><input type="text" name="first_name" ';
		if(!empty($data->first_name)) echo ' value="'.esc_html($data->first_name).'" ';
		echo'/></p>';
		//prefix
		echo'<p><label>'.__('Prefix (e.g.van der)','church-admin').'</label><input type="text" name="prefix" ';
		if(!empty($data->prefix)) echo ' value="'.esc_html($data->prefix).'" ';
		echo'/></p>';
		//last name
		echo'<p><label>'.__('Last Name','church-admin').'</label><input type="text" name="last_name" ';
		if(!empty($data->last_name)) echo ' value="'.esc_html($data->last_name).'" ';
		echo'/></p>';
		//photo
		echo'<p><label for="photo">'.__('Photo','church-admin').'</label><input type="file" id="photo" name="uploadfiles" size="35" class="uploadfiles" /></p><p><label>&nbsp;</label>';
		if(!empty($data->attachment_id))
		{//photo available
			echo wp_get_attachment_image( $data->attachment_id,'ca-people-thumb' );
		}//photo available
		else
		{
			echo '<img src="'.CHURCH_ADMIN_IMAGES_URL.'default-avatar.jpg" width="75" height="75"/>';
		}
		echo '</p>';
		//email
		echo'<p><label>'.__('Email Address','church-admin').'</label><input type="text" name="email" ';
		if(!empty($data->email)) echo ' value="'.esc_html($data->email).'" ';
		echo'/></p>';
		//mobile
		echo'<p><label>'.__('Mobile','church-admin').'</label><input type="text" name="mobile" ';
		if(!empty($data->mobile)) echo ' value="'.esc_html($data->mobile).'" ';
		echo'/></p>';
		//sex
		echo'<p><label>Sex</label>'.__('Male','church-admin').' <input type="radio" name="sex" value="1"';
		if(!empty($data->sex) && $data->sex==1) echo' checked="checked" ';
		echo ' /> '.__('Female','church-admin').' <input type="radio" name="sex" value="0"';
		if($data->sex==0)  echo' checked="checked" ';
		echo'/></p>';
		//people_type
		echo'<p><label>'.__('People Type','church-admin').'</label><select name="people_type_id">';
		foreach($people_type AS $key=>$value)
		{
			echo'<option value="'.$key.'" ';
			selected($key,$data->people_type_id);
			echo'>'.$value.'</option>';
		}
		echo'</select></p>';
		//date of birth
		echo'<p><label>'.__('Date of Birth','church-admin').'</label><input type=="text" name="date_of_birth" class="date_of_birth" ';
		if(!empty($data->date_of_birth)&&$data->date_of_birth!='0000-00-00') echo ' value="'.$data->date_of_birth.'" ';
		echo'/></p>';
		echo'<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(\'.date_of_birth\').datepicker({
            dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"
         });
		});
		</script>';
	
		echo'<p><label>'.__('Current Member Type','church-admin').'</label><span style="display:inline-block">';
		$first=$option='';
		foreach($member_type AS $key=>$value)
		{
			echo'<input type="radio" name="member_type_id" value="'.$key.'"';
			if($data->member_type_id==$key)echo' checked="checked" ';
			echo ' />'.$value.'<br/>';
	   
		}
	
		echo'</span></p>';
	
	
		$prev_member_types=unserialize($data->member_data);
	
	    echo'<p><label>'.__('Dates of Member Levels','church-admin').'</label><span style="display:inline-block">	';
	    foreach($member_type AS $key=>$value)
	    {
			//if no value for member type date make sure no value is given
			if(empty($prev_member_types[$value])||$prev_member_types[$value]=='0000-00-00'|| $prev_member_types[$value]=='1970-01-01'){$date='';}else{$date=$prev_member_types[$value];}
			echo '<span style="float:left;width:150px">'.$value.'</span> <input type="text" id="'.sanitize_title($value).'" name="'.$value.'" value="'.$date.'"/><br style="clear:left"/>';
			//javascript to bring up date picker
			echo'<script type="text/javascript">jQuery(document).ready(function(){jQuery(\'#'.sanitize_title($value).'\').datepicker({dateFormat : "yy-mm-dd", changeYear: true ,yearRange: "1910:'.date('Y').'"});});</script>';
			//javascript to bring up date picker
			}
			echo'</span></p>';
	
		echo'<p><label>'.__('Ministries','church-admin').'</label><span style="display:inline-block">';
		if(!empty($departments))
		{
			foreach($departments AS $key=>$value)
			{
				echo'<span style="float:left;width:150px">'.$value.'</span><input type="checkbox" name="department[]" value="'.$key.'" ';
				if(!empty($data->people_id))
				{
					$check=$wpdb->get_var('SELECT meta_id FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($data->people_id).'" AND department_id="'.esc_sql($key).'"');
					if($check)echo ' checked="checked" ';
				}
				echo '/><br style="clear:left"/>';
			}
		}
		echo '<input type="text" name="new_department" value="'.__('Add a new ministry','church-admin').'" onfocus="javascript:this.value=\'\';"/></p>';
		//small group
		echo'<p><label>'.__('Small Group','church-admin').'</label><span style="display:inline-block">';
		$smallgroups=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL);
		$first=$option='';
		foreach($smallgroups AS $smallgroup)
		{
			
			echo'<input type="radio" name="smallgroup_id" value="'.$smallgroup->id.'"';
			if($smallgroup->id==$data->smallgroup_id) echo' checked="checked" ';
		echo'/>'.$smallgroup->group_name.'<br/>';
		}
		echo '</span></p>';
		echo'<p><label>Or create new Small Group</label><span style="display:inline-block"><span style="float:left;width:150px">Group Name</span><input type="text" name="group_name"/><br style="clear:left"/><span style="float:left;width:150px">Leader?</span><input type="checkbox" name="leading"/><br style="clear:left;"/><span style="float:left;width:150px">Where &amp; When</span><input type="text" name="whenwhere"/></span></p>';
		if($data->user_id )
		{
			echo'<p><label>'.__('Wordpress User','church-admin').'</label>';
			$user_info=get_userdata($data->user_id);
			if(!empty($user_info)){echo $user_info->roles['0'].'</p>';}
		}	
		elseif(!empty($people_id))
		{
			//check any user_ids stored
			$us=$wpdb->get_results('SELECT user_id FROM '.CA_PEO_TBL.' WHERE user_id!="0"');
			if(!empty($us))	{$where='WHERE `ID` NOT IN (SELECT user_id FROM '.CA_PEO_TBL.')';}else{$where='';}
			$users=$wpdb->get_results('SELECT user_login,ID FROM '.$wpdb->prefix.'users '.$where);
				
			if(!empty($users))
			{
					echo'<p><label>Choose a Wordpress account to associate</label><select name="ID"><option value="">Select a user...</option>';
					foreach($users AS $user) echo'<option value="'.$user->ID.'">'.$user->user_login.'</option>';
					echo'</select></p>';
			}
			echo'<p><label>Create a new Wordpress User</label><input type="checkbox" name="create_user" value="yes"/></p>';
		}
		echo'<p class="submit"><input type="hidden" name="edit_people" value="yes"/><input type="submit" value="'.__('Save Details','church-admin').'&raquo;" /></p></form>';
    }
    ?></div></div>
    <?php
}
function church_admin_delete_people($people_id=NULL,$household_id)
{
    //deletes person with specified people_id
    global $wpdb;
    $wpdb->show_errors();
    $wpdb->query('DELETE FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    $wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
    echo'<div class="updated fade"><p><strong>'.__('Person Deleted','church-admin').'</strong></p></div>';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
	    add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
	    do_meta_boxes('church-admin','advanced',null);
    church_admin_display_household($household_id);
}

function church_admin_address_form($data,$error)
{
    //echos form contents where $data is object of address data and $error is array of errors if applicable
    if(empty($data))$data=(object)'';
    $out='';
    if(!empty($errors))$out.='<p>'.__('There were some errors marked in red','church-admin').'</p>';
    $out.='<script type="text/javascript"> var beginLat =';
    if(!empty($data->lat)) {$out.= $data->lat;}else {$out.='51.50351129583287';}
$out.= '; var beginLng =';
    if(!empty($data->lng)) {$out.=$data->lng;}else {$out.='-0.148193359375';}
    $out.=';</script>';
    
   
    $out.= '<p><label>'.__('Address','church-admin').'</label><input type="text" id="address" name="address" ';
    if(!empty($data->address)) $out.=' value="'.esc_html($data->address).'" ';
    if(!empty($error['address'])) $out.= ' class="red" ';
    $out.= '/></p>';
    if(!isset($data->lng))$data->lng='51.50351129583287';
    if(!isset($data->lat))$data->lat='-0.148193359375';
    $out.= '<p><a href="#" id="geocode_address">'.__('Please click here to update map location','church-admin').'...</a><br/><span id="finalise" >Once you have updated your address, this map will show roughly where your address is.</span><input type="hidden" name="lat" id="lat" value="'.$data->lat.'"/><input type="hidden" name="lng" id="lng" value="'.$data->lng.'"/></p><div id="map" style="width:500px;height:300px"></div>';
    $out.='<div style="clear:left"></div>';
    return $out;
    
}

function church_admin_display_household($household_id)
{
    global $wpdb,$people_type,$member_type;
    
    $departments=get_option('church_admin_departments');
    $add_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($household_id).'"');
    
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
	if(!empty($add_row->lng))$map='<img src="http://maps.google.com/maps/api/staticmap?center='.$add_row->lat.','.$add_row->lng.'&zoom=15&markers='.$add_row->lat.','.$add_row->lng.'&size=500x300&sensor=false" alt="'.$address.'"/>';
	echo'<h2>Household Details</h2>';
	
	//grab people
	$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household_id).'" ORDER BY people_type_id ASC,date_of_birth ASC,sex DESC');
	if($people)
	{//are people
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></p>';
	    echo'<table class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Picture','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Sex','church-admin').'</th><th>'.__('Person type','church-admin').'</th><th>'.__('Member Level','church-admin').'</th><th>'.__('Ministries','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Move to different household','church-admin').'</th><th>'.__('WP user','church-admin').'</th></tr></tfoot><tbody>';
	    foreach ($people AS $person)
	    {
		switch($person->sex)
		{
		    case 0:$sex=__('Female','church-admin');break;
		    case 1:$sex=__('Male','church-admin');break;
		}
		$result=$wpdb->get_results('SELECT * FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'"');
		$department=array();
		foreach($result AS $row){$department[]=$departments[$row->department_id];}
		asort($department);
		if($person->user_id)
		{
		    $user_info=get_userdata($person->user_id);
		    $person_user= $user_info->user_login.'<br/>('.church_admin_get_capabilities($person->user_id).')';
		}
		else
		{
		    $person_user='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_create_user&amp;people_id='.$person->people_id.'&amp;household_id='.$person->household_id,'create_user').'">'.__('Create WP User','church-admin').'</a></p>';
		}
		if(!empty($person->attachment_id))
		{//photo available
		    $photo= wp_get_attachment_image( $person->attachment_id,'ca-people-thumb' );
		}//photo available
		else
		{
		    $photo= '<img src="'.CHURCH_ADMIN_IMAGES_URL.'default-avatar.jpg" width="75" height="75"/>';
		}
		if(!empty($person->prefix)){$prefix=$person->prefix.' ';}else{$prefix='';}
		echo'<tr><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'edit_people').'">'.__('Edit','church-admin').'</a></td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_people&amp;household_id='.$household_id.'&amp;people_id='.$person->people_id.'&amp;household_id='.$household_id,'delete_people').'">'.__('Delete','church-admin').'</a></td><td>'.$photo.'</td><td>'.esc_html($person->first_name).' '.$prefix.esc_html($person->last_name).'</a></td><td>'.$sex.'</td><td>'.$people_type[$person->people_type_id].'</td><td>'.$member_type[$person->member_type_id].'</td><td>'.implode(', ',$department).'</td><td>';
		if(is_email($person->email)){echo '<a href="mailto:'.$person->email.'">'.$person->email.'</a>';}else{echo esc_html($person->email);}
		echo '</td><td>'.esc_html($person->mobile).'</td><td><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_move_person&amp;people_id='.$person->people_id,'move_person').'">Move</a></td><td>'.$person_user.'</td></tr>';
	    }
	    echo'</tbody></table>';
	}//end are people
	else
	{//no people
	    echo'<p>'.__('There are no people stored in that household yet','church-admin').'</p>';
	    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people&amp;household_id='.$household_id,'edit_people').'">'.__('Add someone','church-admin').'</a></p>';
	}//no people
	//end grab people
	if(!empty($add_row->phone))echo'<p><label>Homephone</label>'.$add_row->phone.'</p>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household&amp;household_id='.$household_id,'edit_household').'">'.$address.'</a></p>';
	echo'<p>'.$map.'</p>';
    }//end address stored
    else
    {
	echo'<div class="updated fade"><p><strong>'.__('No Household found','church-admin').'</strong></p></div>';
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
	
	echo'<div class="updated fade"><p><strong>'.__('Wordpress Users migrated','church-admin').'</strong></p></div>';
    }
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
	    add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");
	    do_meta_boxes('church-admin','advanced',null);
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
			echo'<div class="updated fade"><p><strong>'.$data->first_name.' '.$data->last_name.' has been moved to a new household with the same address details!</strong></p></div>';
			
		}
		else
		{
			//remove household entry if only one person was in it.
			$no=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
			if($no==1)$wpdb->query('DELETE FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($data->household_id).'"');
			//move the person to the new household
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET household_id="'.esc_sql($_POST['household_id']).'" WHERE people_id="'.esc_sql($id).'"');
			echo'<div class="updated fade"><p><strong>'.$data->first_name.' '.$data->last_name.' has been moved!</strong></p></div>';
			$household_id=(int)$_POST['household_id'];
		}
	    church_admin_display_household($household_id);
	     require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
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
		echo'<p><label>Create a new household with same address</label><input type="checkbox" name="create" value="yes"/></p>';
		echo'<p><label>Move to household</label><select name="household_id"><option value="">Select a new household...</option>';
		foreach($results AS $row)
		{
		    echo'<option value="'.$row->household_id.'">'.esc_html($row->last_name).' ('.$row->member_type.')</option>';
		}
		echo'</select></p>';
		echo'<p><input type="hidden" name="move_person" value="yes"/><input type="submit" class="primary-button" value="Move person"/></p>';
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
	echo"<p>'.__('Nobody was specified','church-admin').'</p>";
    }
    else
    {//people_id
	
	$user=$wpdb->get_row('SELECT * FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
	if(empty($user))
	{
	    echo'<div class="updated fade">'.__("That people record doesn't exist",'church-admin').'</p></div>';
	}
	else
	{//user exits in plugin db
	    $user_id=email_exists($user->email);
	    if(!empty($user_id) && $user->user_id==$user_id)
	    {//wp user exists and is in plugin db
		echo'<div class="updated fade">'.__('User already created','church-admin').'</p></div>';
		church_admin_display_household($household_id);
	    }
	    elseif(!empty($user_id) && $user->user_id!=$user_id)
	    {//wp user exists, update plugin
			$wpdb->query('UPDATE '.CA_PEO_TBL.' SET user_id="'.esc_sql($user_id).'" WHERE people_id="'.esc_sql($people_id).'"');
			echo'<div class="updated fade">'.__('User updated','church-admin').'</p></div>';
		
	    }
	    else
	    {//wp user needs creating!
		//create unique username
		$username=strtolower(str_replace(' ','',$user->first_name).str_replace(' ','',$user->last_name));
		$x='';
		while(username_exists( $user_name.$x ))
		{
		    $x+=1;
		}
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $username.$x, $random_password, $user->email );
		
		$message='<p>'.__('The web team at','church-admin').' <a href="'.site_url().'">'.site_url().'</a>'.__('have just created a user login for you','church-admin').'</p>';
		$message.='<p>'.__('Your username is','church-admin').' <strong>'.$username.$x.'</strong></p>';
		$message.='<p>'.__('Your password is','church-admin').' <strong>'.$random_password.'</strong></p>';
		echo '<div class="updated fade">'.__('User created with username','church-admin').' <strong>'.$username.'</strong>,'.__('password','church-admin').': <strong>'.$random_password.'</strong> '.__('and this message was queued to them','church-admin').'<br/>'.$message;
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
    $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")';
    
    $results=$wpdb->get_results($sql);
    if(!$results)
    {
	$sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%")';
    
	$results=$wpdb->get_results($sql);
    }
    if($results)
    {
	    echo'<div class="wrap church_admin"><h2>'.__('Address List','church-admin').'</h2><div class="updated fade"><p><strong>'.__('Your search for','church-admin').' '.esc_html($s).' '.__('yielded these','church-admin').' '.$wpdb->num_rows.' '.__('results','church-admin').'</strong></p></div>';
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
	echo'<div class="updated fade"><p>'.__('Search','church-admin').' '.$s.' '.__('not found','church-admin').'</p></div>';
	church_admin_address_list('1');
    }
}




?>
