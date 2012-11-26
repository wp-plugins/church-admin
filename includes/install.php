<?php
function church_admin_install()
{
    global $wpdb,$church_admin_version;
    $wpdb->show_errors();
    //household table    
    
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOU_TBL.'"') != CA_HOU_TBL)
    {
        $sql = 'CREATE TABLE '.CA_HOU_TBL.' ( address TEXT, lat VARCHAR(50),lng VARCHAR (50), phone VARCHAR(15),member_type_id INT(11),ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,household_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (household_id));';
        $wpdb->query($sql);
    }
    //people table    
    ;
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_PEO_TBL.'"') != CA_PEO_TBL)
    {
        $sql = 'CREATE TABLE '.CA_PEO_TBL.' (first_name VARCHAR(100),last_name VARCHAR(100), date_of_birth DATE, member_type_id INT(11), roles TEXT, sex INT(1),mobile VARCHAR(15), email TEXT,people_type_id INT(11),smallgroup_id INT(11),household_id INT(11),member_data TEXT, user_id INT(11),people_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (people_id));';
        $wpdb->query($sql);
    }
    //people_meta table    
   
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MET_TBL.'"') != CA_MET_TBL)
    {
        $sql = 'CREATE TABLE '.CA_MET_TBL.' ( people_id INT(11),department_id INT(11), meta_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (meta_id));';
        $wpdb->query($sql);
    }
    
  //sort out people types  
    
   
    $church_admin_people_settings=get_option('church_admin_people_settings');
    if(empty($church_admin_people_settings['member_type']))$church_admin_people_settings['member_type']=array(0=>'Mailing List',1=>'Visitor',2=>'Member');
    if(!empty($church_admin_people_settings['member_type']))
    {
	//install member type table
	    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MTY_TBL.'"') != CA_MTY_TBL)
	    {
		$sql='CREATE TABLE '.CA_MTY_TBL.' (`member_type_order` INT( 11 ) NOT NULL ,`member_type` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,`member_type_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY)  CHARACTER SET utf8 COLLATE utf8_general_ci;';
		$wpdb->query($sql);
		$order=1;
		foreach($church_admin_people_settings['member_type'] AS $id=>$type)
		{
		    $wpdb->query('INSERT INTO '.CA_MTY_TBL .' (member_type_order,member_type,member_type_id) VALUES("'.$order.'","'.esc_sql($type).'","'.esc_sql($id).'")');
		    $order++;
		}
	    }
    }//end member type already in people_settings option
    $people_type=get_option('church_admin_people_type');
    if(empty($people_type))$people_type=array('1'=>'Adult','2'=>'Child');
    update_option('church_admin_people_type',$people_type);
   
    
    
    delete_option('church_admin_people_settings');

//migrate old tables
    $table_name = $wpdb->prefix."church_admin_directory";
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name && $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."church_admin_directory_old'") != $wpdb->prefix.'church_admin_directory_old' )
    {
	
	$results=$wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY last_name');
	foreach($results AS $row)
	{
	    
	    //split off household
	    $address=esc_sql(serialize(array('address_line1'=>stripslashes($row->address_line1),'address_line2'=>stripslashes($row->address_line2),'town'=>stripslashes($row->city),'county'=>stripslashes($row->state),'postcode'=>stripslashes($row->zipcode))));
	    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,lat,lng,phone,member_type_id)VALUES("'.$address.'","52.0","0","'.esc_sql($row->homephone).'","1")');
	    $household_id=$wpdb->insert_id;
	    $member_data=esc_sql(serialize(array('member'=>mysql2date('Y-m-d',$row->ts))));
	    //deal with adults assume & is the separator
	    $adults=explode(" & ",$row->first_name);
	    //update smallgroup bits
	    $sg_leader=array();
	    $sg_id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leader="'.$row->id.'"');
		foreach($adults AS $key=>$adult)
		{
		    if(!empty($adult))
		    {
		        $sql='INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($adult)).'","'.esc_sql($row->last_name).'","1","1","1","'.esc_sql($row->email).'","'.$row->cellphone.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$member_data.'")';
		   
		        $wpdb->query($sql);
			//small group leader array  while at it!
			$people_id=$wpdb->insert_id;
			if($sg_id)
			{
			    $sg_leader[]=$people_id;
			    //give person small group leader role!
			    church_admin_update_role('1',$people_id);
			}
		    }
		}
	    if(!empty($sg_leader)&& !empty($sg_id))$wpdb->query('UPDATE '.CA_SMG_TBL.' SET leader="'.esc_sql(serialize($sg_leader)).'" WHERE id="'.esc_sql($sg_id).'"');
	    $children=explode(", ",$row->children);
	    
	    foreach($children AS $key=>$child)
	    {
		if(!empty($child))
		{
		    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($child)).'","'.esc_sql($row->last_name).'","1","2","1","'.esc_sql($row->email).'","'.$row->mobile.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$member_data.'")';
		    
		    $wpdb->query($sql);
		}
	    }
	
	}
	
	$wpdb->query('RENAME TABLE '.$wpdb->prefix.'church_admin_directory TO '.$wpdb->prefix.'church_admin_directory_old');
    }
    //handle visitors
    
    $table_name = $wpdb->prefix."church_admin_visitors";
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name && $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."church_admin_visitors_old'") != $wpdb->prefix.'church_admin_visitors_old')
    {
	
	$results=$wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY last_name');
	foreach($results AS $row)
	{
	    $visitor_data=esc_sql(serialize(array('visitor'=>$row->first_sunday)));
	    //split off household
	    $address=serialize(array('address_line1'=>stripslashes($row->address_line1),'address_line2'=>stripslashes($row->address_line2),'town'=>stripslashes($row->city),'county'=>stripslashes($row->state),'postcode'=>stripslashes($row->zipcode)));
	    //check if entered
	    $household_id=NULL;
	    $household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.esc_sql($address).'" ');
	    if($address=='a:5:{s:13:"address_line1";s:0:"";s:13:"address_line2";s:0:"";s:4:"town";s:0:"";s:6:"county";s:0:"";s:8:"postcode";s:0:"";}'||!$household_id)
	    {
		$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone,member_type_id)VALUES("'.esc_sql($address).'","52.0","0","'.esc_sql($row->homephone).'","0")');
		$household_id=$wpdb->insert_id;
	    }
	    //deal with adults assume & is the separator
	    $adults=explode(" & ",$row->first_name);
	    
		foreach($adults AS $key=>$adult)
		{
		    if(!empty($adult))
		    {
			$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.esc_sql(trim($adult)).'" AND last_name="'.esc_sql($row->last_name).'" AND household_id="'.esc_sql($household_id).'"');
		        if(!$people_id)
			{
			    $sql='INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($adult)).'","'.esc_sql($row->last_name).'","0","1","1","'.esc_sql($row->email).'","'.$row->cellphone.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$visitor_data.'")';
			    $wpdb->query($sql);
			}
			else
			{//update member data
			    $member_data=maybe_unserialize($wpdb->get_var('SELECT member_data FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"'));
			    if(!$member_data)$member_data=array();
			    $member_data['visitor']=$row->first_sunday;
			    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_data="'.esc_sql(serialize($memeber_data)).'" WHERE people_id="'.$people_id.'"');
			}//update memberdata
		    }
		}
	      $children=explode(", ",$row->children);
	    
	    foreach($children AS $key=>$child)
	    {
		if(!empty($child))
		{
		    $people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.esc_sql(trim($adult)).'" AND last_name="'.esc_sql($row->last_name).'" AND household_id="'.esc_sql($household_id).'"');
		        if(!$people_id)
			{
			    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($child)).'","'.esc_sql($row->last_name).'","1","2","1","'.esc_sql($row->email).'","'.$row->mobile.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$visitor_data.'")';
			    $wpdb->query($sql);
			}
			else
			{//update member data
			    $member_data=maybe_unserialize($wpdb->get_var('SELECT member_data FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"'));
			    if(!$member_data)$member_data=array();
			    $member_data['visitor']=$row->first_sunday;
			    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_data="'.esc_sql(serialize($memeber_data)).'" WHERE people_id="'.$people_id.'"');
			}//update memberdata
		}
	    }
	
	}
	
	$wpdb->query('RENAME TABLE '.$wpdb->prefix.'church_admin_visitors TO '.$wpdb->prefix.'church_admin_visitors_old');
    }
//end migrate old tables
    
    //install small group table
    $table_name = $wpdb->prefix."church_admin_smallgroup";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ." (leader int(11) NOT NULL,group_name varchar(255) NOT NULL,whenwhere tinytext NOT NULL,id int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (id));";
        $wpdb->query ($sql);
	$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_smallgroup (leader,group_name,whenwhere,id)VALUES ('0', 'Unattached', '', '1');");
    }
    else
    {
	$wpdb->query('ALTER TABLE '.$table_name.' CHANGE `leader` `leader` TEXT NOT NULL ');
    }

//install emails sent table
    $table_name = $wpdb->prefix."church_admin_email_build";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_email_build (  recipients text NOT NULL,  subject text NOT NULL,  message text NOT NULL,  send_date date NOT NULL,  filename text NOT NULL,  from_name varchar(500) NOT NULL,  from_email varchar(500) NOT NULL,  email_id int(11) NOT NULL auto_increment,  PRIMARY KEY  (email_id)) ;';
$wpdb->query ($sql);
}


    //install rota settings table
    $table_name = CA_RST_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ."  (rota_task TEXT NOT NULL ,task_order INT(11),rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ));";
	$wpdb->query ($sql);
    }
    
    //install rotas table
    $table_name = CA_ROT_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ."  (  rota_date DATE NOT NULL,  rota_jobs TEXT NOT NULL, service_id INT(11) NOT NULL, rota_id INT(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (rota_id));";
	//echo $sql;
	$wpdb->query ($sql);
    }
	if($wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_rota"') == $wpdb->prefix.'church_admin_rota')
	{
	    //grab current jobs
	    $jobs=array();
	    $results=$wpdb->get_results('SELECT a.*,b.rota_task FROM '.$wpdb->prefix.'church_admin_rota a,'.$wpdb->prefix.'church_admin_rota_settings b WHERE a.rota_option_id=b.rota_id');
	    if($results)
	    {
		foreach($results AS $row)
		{
		    $jobs[$row->rota_date][$row->rota_task]=$row->who;
		}
		foreach($jobs AS $date=>$people)
		{
		    $day_jobs=esc_sql(serialize($people));
		    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_rotas (rota_date,rota_jobs,service_id)VALUES("'.esc_sql($date).'","'.$day_jobs.'","1")';
		    $wpdb->query($sql);
		}
	    $wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_rota');
	    }
	}
    
    
  
    
    
    //install attendance table
    $table_name = CA_ATT_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,adults INT(11) NOT NULL,children INT(11)NOT NULL,rolling_adults INT(11) NOT NULL,rolling_children INT(11)NOT NULL,service_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query ($sql);
    }
    
    //install email table
    $table_name = $wpdb->prefix."church_admin_email";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ." (recipient varchar(500) NOT NULL,  from_name text NOT NULL,  from_email text NOT NULL,  copy text NOT NULL, subject varchar(500) NOT NULL, message text NOT NULL,attachment text NOT NULL,sent datetime NOT NULL,email_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (email_id));";
        $wpdb->query ($sql);
    }

    //install calendar table1
    $table_name = $wpdb->prefix."church_admin_calendar_event";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
        $sql="CREATE TABLE  IF NOT EXISTS ". $table_name ."  (recurring VARCHAR(3),title text NOT NULL, description text  NOT NULL, location text NOT NULL, year_planner INT(1),cat_id INT(11) NOT NULL, event_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (event_id)) ;";
        $wpdb->query ($sql);
    }
    
    //install calendar table2
    $table_name = $wpdb->prefix."church_admin_calendar_date";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE  IF NOT EXISTS ". $table_name ."  (start_date date NOT NULL DEFAULT '0000-00-00', start_time time NOT NULL DEFAULT '00:00:00', end_time time NOT NULL DEFAULT '00:00:00', event_id int(11) NOT NULL DEFAULT '0',date_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (date_id)) " ;
        $wpdb->query ($sql);
    }
    
    //install calendar table2
    $table_name = $wpdb->prefix."church_admin_calendar_category";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ."  (category varchar(255)  NOT NULL DEFAULT '',  fgcolor varchar(7)  NOT NULL DEFAULT '', bgcolor varchar(7)  NOT NULL DEFAULT '', cat_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`cat_id`))" ;
        $wpdb->query ($sql);
        $wpdb->query("INSERT INTO $table_name (category,bgcolor,cat_id) VALUES('Unused','#FFFFFF','0')");
    }
    //follow up funnels
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FUN_TBL.'"')!=CA_FUN_TBL)
    {
	
	if(!defined( 'DB_CHARSET'))define( 'DB_COLLATE','utf8');
	$sql='CREATE TABLE '.CA_FUN_TBL.' (action TEXT CHARACTER SET '.DB_CHARSET.' ,
member_type_id INT( 11 )  ,department_id INT( 11 )  , funnel_order INT(11), people_type_id INT(11), funnel_id INT( 11 ) AUTO_INCREMENT PRIMARY KEY
) ENGINE = MYISAM CHARACTER SET '.DB_CHARSET.';';
	$wpdb->query($sql);
    }
        //follow up people's funnels 
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FP_TBL.'"')!=CA_FP_TBL)
    {
	
	if(!defined( 'DB_CHARSET'))define( 'DB_COLLATE','utf8');
	$sql='CREATE TABLE '.CA_FP_TBL.' (funnel_id INT(11) ,member_type_id INT(11),people_id INT( 11 )  ,assign_id INT( 11 )  , assigned_date DATE,email DATE NOT NULL DEFAULT "0000-00-00", completion_date DATE, id INT( 11 ) AUTO_INCREMENT PRIMARY KEY
) ENGINE = MYISAM CHARACTER SET '.DB_CHARSET.';';
	$wpdb->query($sql);
    }
 //services
  //household table    
    
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SER_TBL.'"') != CA_SER_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SER_TBL.' ( service_name TEXT, service_day INT(1),service_time TIME, venue VARCHAR(100),address TEXT,lat VARCHAR(50),lng VARCHAR(50),first_meeting DATE,service_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (service_id));';
        $wpdb->query($sql);
	$wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,venue,address,lat,lng,first_meeting) VALUES ("Sunday Service","1","10:00","Main Venue","'.esc_sql(serialize(array('address_line1'=>"",'address_line2'=>"",'town'=>"",'county'=>"",'postcode'=>""))).'","52.0","0.0","'.date('Y-m-d').'")');
    }
    
    
 if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "rota_order"')!='rota_order')
{
    $sql='ALTER TABLE  '.CA_RST_TBL.' ADD rota_order INT(11)';
    $wpdb->query($sql);
    //order current rota jobs as
    $result=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_id');
    $x=1;
    $order=array();
    if($result)
    {
	foreach($result AS $row)
	{
	    $order[$x]=$row->rota_task;
	    $wpdb->query('UPDATE '.CA_RST_TBL.' SET rota_order ="'.$x.'" WHERE rota_id="'.$row->rota_id.'"');
	    $x++;
	}
    }
    //adjust rota table so it is normalised
   
    $results=$wpdb->get_results('SELECT * FROM '.CA_ROT_TBL);
    if($results)
    {
	 
	foreach($results AS $row)
	{
	    $tasks=maybe_unserialize($row->rota_jobs);
	    if($tasks)
	    {
		$new_rota=array();
		foreach($tasks AS $task_name=>$person)
		{
		    $id=array_search($task_name,$order);
		    if($id) $new_rota[$id]=$person;
		}
		$sql='UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.esc_sql(maybe_serialize($new_rota)).'" WHERE rota_id="'.esc_sql($row->rota_id).'"';
		
		$wpdb->query($sql);
	    }
	}
    }
    
} 
    
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "last_updated"')!='last_updated')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD last_updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP';
    $wpdb->query($sql);
}

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "funnels"')!='funnels')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD funnels TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "role_id"')=='role_id')
{
    $sql='ALTER TABLE  '.CA_MET_TBL.' CHANGE role_id department_id INT(11)';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_ATT_TBL.' LIKE "service_id"')!='service_id')
{
    $sql='ALTER TABLE  '.CA_ATT_TBL.' ADD service_id INT(11) DEFAULT "1"';
    $wpdb->query($sql);
}

//make sure tables are UTF8  
    $sql='ALTER TABLE '. CA_ATT_TBL.'CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
   $sql='ALTER TABLE '.CA_PEO_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
     $sql='ALTER TABLE '.CA_HOU_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
     $sql='ALTER TABLE '.CA_MTY_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
   $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_event CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_category CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_email CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_email_build CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_rota_settings CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    
//update pdf cache
if(!get_option('church_admin_calendar_width'))update_option('church_admin_calendar_width','630');
if(!get_option('church_admin_pdf_size'))update_option('church_admin_pdf_size','A4');
if(!get_option('church_admin_label'))update_option('church_admin_label','L7163');
if(!get_option('church_admin_page_limit'))update_option('church_admin_page_limit',30);
//sort out wp-cron
if(get_option('church_admin_cron')=='wp-cron')
{
    add_action('church_admin_bulk_email','church_admin_cron');
   $timestamp=mktime();
    wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
}

//roles
$departments=get_option('church_admin_departments');
if(empty($departments))
{
    $departments=array('1'=>'Small Group Leader','2'=>'Elder');
    update_option('church_admin_roles',$departments);
}

//update version
update_option('church_admin_version',$church_admin_version);
}

 
?>