<?php
function church_admin_install()
{
    /**
 *
 * Installs WP tables and options
 * 
 * @author  Andy MoyleF
 * @param    null
 * @return   
 * @version  0.11
 *
 * 0.11 added attachement_id to People table 2013-02-24
 * 
 */ 
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
        $sql = 'CREATE TABLE '.CA_PEO_TBL.' (first_name VARCHAR(100),last_name VARCHAR(100), date_of_birth DATE, member_type_id INT(11),attachment_id INT(11), roles TEXT, sex INT(1),mobile VARCHAR(15), email TEXT,people_type_id INT(11),smallgroup_id INT(11),household_id INT(11),member_data TEXT, user_id INT(11),people_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (people_id));';
        $wpdb->query($sql);
    }
	
    //add attachement_id to people table for photo storage
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "attachment_id"')!='attachment_id')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD attachment_id INT(11)';
    $wpdb->query($sql);
    
     }
     //add attachement_id to people table for prayer chain
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "prayer_chain"')!='prayer_chain')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD prayer_chain INT(1) NOT NULL DEFAULT "0" AFTER `attachment_id`';
    $wpdb->query($sql);
    
     }
    
    
    //people_meta table    
   
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MET_TBL.'"') != CA_MET_TBL)
    {
        $sql = 'CREATE TABLE '.CA_MET_TBL.' ( meta_type VARCHAR(255) DEFAULT "ministry", people_id INT(11),department_id INT(11), meta_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (meta_id));';
        $wpdb->query($sql);
    }
    
  //sort out people types  
    
   
    $church_admin_people_settings=get_option('church_admin_people_settings');
    if(empty($church_admin_people_settings['member_type']))$church_admin_people_settings['member_type']=array('0'=>'Mailing List','1'=>'Visitor','2'=>'Member');
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
		    $check=$wpdb->get_var('SELECT member_type_id FROM '. CA_MTY_TBL. ' WHERE member_type_id="'.esc_sql($id).'"');
		    if(!$check)$wpdb->query('INSERT INTO '.CA_MTY_TBL .' (member_type_order,member_type,member_type_id) VALUES("'.$order.'","'.esc_sql($type).'","'.esc_sql($id).'")');
		    $order++;
		}
	    }
    }//end member type already in people_settings option
    $people_type=get_option('church_admin_people_type');
    if(empty($people_type))$people_type=array('1'=>__('Adult','church-admin'),'2'=>__('Child','church-admin'));
	if(empty($people_type[3]))$people_type[3]=__('Teenager','church-admin');
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
	    $address=esc_sql(implode(", ",array('address_line1'=>stripslashes($row->address_line1),'address_line2'=>stripslashes($row->address_line2),'town'=>stripslashes($row->city),'county'=>stripslashes($row->state),'postcode'=>stripslashes($row->zipcode))));
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
			    //church_admin_update_role('1',$people_id);
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
    
    //make sure addresses are stored not as an array from v0.554
    $result=$wpdb->get_results('SELECT * FROM '. CA_HOU_TBL);
    if(!empty($result))
    {
		foreach($result AS $row)
		{
			$address=maybe_unserialize($row->address);
			if(!empty($address) && is_array($address))$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.esc_sql(implode(", ",$address)).'" WHERE household_id="'.esc_sql($row->household_id).'"');
		}
    }
//end migrate old tables
//make smallgrpup_id a serialized area in people table
if(OLD_CHURCH_ADMIN_VERSION<0.5973)
{
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' CHANGE `smallgroup_id` `smallgroup_id` TEXT NOT NULL');
	$people=$wpdb->get_results('SELECT smallgroup_id, people_id FROM '.CA_PEO_TBL);
	if(!empty($people))
	{
		foreach($people AS $person)
		{
			$sg=maybe_unserialize($person->smallgroup_id);
			if(!is_array($sg))
			{
				$s=array($sg);
				$sql='UPDATE '.CA_PEO_TBL.' SET smallgroup_id="'.esc_sql(serialize($s)).'" WHERE people_id="'.esc_sql($person->people_id).'"';
				
				$wpdb->query($sql);
			}
		}
	}
}

    
    //install small group table
    $table_name = $wpdb->prefix."church_admin_smallgroup";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ." (leader int(11) NOT NULL,group_name varchar(255) NOT NULL,whenwhere TEXT NOT NULL,address TEXT, lat VARCHAR(30),lng VARCHAR(30), id int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (id));";
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
		$peeps=array();
		foreach($results AS $row)
		{
		    if(!empty($row->who)){$peeps=explode(", ",$row->who);}
		    $jobs[$row->rota_date][$row->rota_task]=$peeps;
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
	 //install attendance table
    $table_name = CA_IND_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,people_id INT(11) NOT NULL,meeting_type TEXT, meeting_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query ($sql);
    }
    
    //install email table
    $table_name = $wpdb->prefix."church_admin_email";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ." (recipient varchar(500) NOT NULL,  from_name text NOT NULL,  from_email text NOT NULL,  copy text NOT NULL, subject varchar(500) NOT NULL, message text NOT NULL,attachment text NOT NULL,sent datetime NOT NULL,email_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (email_id));";
        $wpdb->query ($sql);
    }
    //install kids work table
   
    if($wpdb->get_var('show tables like "'.CA_KID_TBL.'"') != CA_KID_TBL) 
    {
        $sql="CREATE TABLE IF NOT EXISTS ". CA_KID_TBL." (group_name TEXT NOT NULL,  youngest DATE NOT NULL,  oldest DATE NOT NULL,department_id INT(11), id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id));";
        $wpdb->query ($sql);
    }

    
    //install calendar table1
    $table_name = CA_DATE_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    { $sql='CREATE TABLE IF NOT EXISTS '.CA_DATE_TBL.' (`title` text NOT NULL,`description` text NOT NULL,`location` text NOT NULL,`year_planner` int(1) NOT NULL,`event_image` int(11) DEFAULT NULL,`end_date` date NOT NULL DEFAULT "0000-00-00",`start_date` date NOT NULL DEFAULT "0000-00-00",`start_time` time NOT NULL DEFAULT "00:00:00",`end_time` time NOT NULL DEFAULT "00:00:00", `event_id` int(11) NOT NULL DEFAULT "0",`facilities_id` int(11) DEFAULT NULL,
  `general_calendar` int(1) NOT NULL DEFAULT "1",`how_many` int(11) NOT NULL,`date_id` int(11) NOT NULL AUTO_INCREMENT, `cat_id` int(11) NOT NULL,`recurring` text NOT NULL,PRIMARY KEY (`date_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
        $wpdb->query ($sql);
    }
    //upgrade CA_DATE_TBL if needed
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "facilities_id"')!='facilities_id')
	{
			$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD facilities_id INT(11) AFTER event_id';
			$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "general_calendar"')!='general_calendar')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD general_calendar INT(1) NOT NULL DEFAULT "1" AFTER `facilities_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "description"')!='description')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `description` TEXT NOT NULL AFTER `facilities_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "location"')!='location')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `location` TEXT NOT NULL AFTER `description`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "year_planner"')!='year_planner')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `year_planner` INT(1) NOT NULL AFTER `location`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "cat_id"')!='cat_id')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `cat_id` INT(11) NOT NULL AFTER `year_planner`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "how_many"')!='how_many')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `how_many` INT(11) AFTER `event_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "event_image"')!='event_image')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `event_image` INT (11) AFTER `year_planner`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "recurring"')!='recurring')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `recurring` TEXT NOT NULL AFTER `year_planner`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "title"')!='title')
	{
		$sql='ALTER TABLE '.CA_DATE_TBL.' ADD `title` TEXT NOT NULL FIRST;';
		$wpdb->query($sql);
		$events=$wpdb->get_results('SELECT * FROM '.CA_EVE_TBL);
		if(!empty($events))
		{
			foreach($events AS $event)
			{
			$sql='UPDATE '. CA_DATE_TBL.' SET cat_id="'.esc_sql($event->cat_id).'",event_id="'.esc_sql($event->event_id).'",recurring="'.esc_sql($event->recurring).'",title="'.esc_sql($event->title).'", description="'.$event->description.'", location="'.esc_sql($event->location).'", year_planner="'.esc_sql($event->year_planner).'" WHERE event_id="'.esc_sql($event->event_id).'"';
		
			$wpdb->query($sql);
			}
		}
    
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
    
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SER_TBL.'"') != CA_SER_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SER_TBL.' ( service_name TEXT, service_day INT(1),service_time TIME, venue VARCHAR(100),address TEXT,lat VARCHAR(50),lng VARCHAR(50),first_meeting DATE,service_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (service_id));';
        $wpdb->query($sql);
	$wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,venue,address,lat,lng,first_meeting) VALUES ("'.__('Sunday Service','church-admin').'","1","10:00","'.__('Main Venue','church-admin').'","'.esc_sql(serialize(array('address_line1'=>"",'address_line2'=>"",'town'=>"",'county'=>"",'postcode'=>""))).'","52.0","0.0","'.date('Y-m-d').'")');
    }
    
 if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "department_id"')!='department_id')
{
    //add department_id to allow choosing people easily default NULL no department, 0 = whole list, int = department_id
    $sql='ALTER TABLE  '.CA_RST_TBL.' ADD department_id INT(11) DEFAULT 1';
    $wpdb->query($sql);
 }
  if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "smallgroup_order"')!='smallgroup_order')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD smallgroup_order INT(11)';
    $wpdb->query($sql);
    
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

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "lat"')!='lat')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD lat VARCHAR(30)';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "lng"')!='lng')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD lng VARCHAR(30)';
    $wpdb->query($sql);
}    
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "address"')!='address')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD address TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "last_updated"')!='last_updated')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD last_updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "kidswork_override"')!='kidswork_override')
{
    $sql='ALTER TABLE '.CA_PEO_TBL.' ADD `kidswork_override` INT(11) NOT NULL AFTER `last_updated`;';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "prefix"')!='prefix')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD prefix TEXT ';
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
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "file_subtitle"')!='file_subtitle')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD file_subtitle TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "external_file"')!='external_file')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD external_file TEXT';
    $wpdb->query($sql);
}	
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "transcript"')!='transcript')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD transcript TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "people_order"')!='people_order')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD people_order INT(11) ';
    $wpdb->query($sql);
}

$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' CHANGE `people_order` `people_order` INT(11) NULL DEFAULT "1"');

//v0.5946 add smallgroup attendance indicator
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "smallgroup_attendance"')!='smallgroup_attendance')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD smallgroup_attendance INT(1) DEFAULT 1';
    $wpdb->query($sql);
	
}
//v0.5958 added hope team
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "other_hope_team"')!='other_hope_team')
{
    $sql='ALTER TABLE '.CA_PEO_TBL.' ADD `other_hope_team` TEXT NOT NULL;';
    $wpdb->query($sql);
	
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "meta_type"')!='meta_type')
{
    $sql='ALTER TABLE '.CA_MET_TBL.' ADD `meta_type` VARCHAR(255) NOT NULL DEFAULT "ministry" FIRST;';
    $wpdb->query($sql);
	
}
if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOP_TBL.'"') != CA_HOP_TBL)
{

	 $sql = 'CREATE TABLE IF NOT EXISTS '.CA_HOP_TBL.' (  `job` text NOT NULL,  `ts` datetime NOT NULL,  `hope_team_id` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`hope_team_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
	 $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "plays"')!='plays')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD plays INT(11)';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "initials"')!='initials')
{
    $sql='ALTER TABLE  '.CA_RST_TBL.' ADD initials INT(1)';
    $wpdb->query($sql);
}

if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FAC_TBL.'"')!=CA_FAC_TBL)
{
	$sql="CREATE TABLE IF NOT EXISTS ". CA_FAC_TBL ."  (facility_name TEXT,facilities_order INT(11),  facilities_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`facilities_id`))" ;
        $wpdb->query ($sql);
}

//make sure tables are UTF8  
    $sql='ALTER TABLE '. CA_ATT_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
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
   $timestamp=time();
    wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
}

//roles
$departments=get_option('church_admin_departments');
if(empty($departments))
{
    $departments=array('1'=>__('Small Group Leader','church-admin'),'2'=>__('Elder','church-admin'));
    update_option('church_admin_roles',$departments);
}
//sermon podcast table install

    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SERM_TBL.'"') != CA_SERM_TBL)
    {
        $sql='CREATE TABLE  '.CA_SERM_TBL.' (`series_name` TEXT NOT NULL ,`series_image` TEXT NOT NULL,`series_description` TEXT NOT NULL ,`series_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);
    }
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FIL_TBL.'"') != CA_FIL_TBL)
    {
        $sql='CREATE TABLE  '.CA_FIL_TBL.' (`file_name` TEXT NOT NULL ,`file_title` TEXT NOT NULL ,`file_description` TEXT NOT NULL ,`service_id` INT(11),`bible_passages` TEXT NOT NULL,`private` INT(1) NOT NULL DEFAULT "0",`length` TEXT NOT NULL, `pub_date` DATETIME, last_modified DATETIME, `series_id` INT( 11 ) NOT NULL ,`transcript` TEXT,`video_url` TEXT, `speaker` TEXT NOT NULL,`file_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);
    }
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_BIB_TBL.'"') != CA_BIB_TBL)
    {
	$sql='CREATE TABLE IF NOT EXISTS '.CA_BIB_TBL.' (`bible_id` int(10) NOT NULL AUTO_INCREMENT,`name` varchar(30) NOT NULL, PRIMARY KEY (`bible_id`)) ENGINE=MyISAM ;';
	$wpdb->query($sql);
	$sql="INSERT INTO ".CA_BIB_TBL." (`bible_id`, `name`) VALUES(1, 'Genesis'),(2, 'Exodus'),(3, 'Leviticus'),(4, 'Numbers'),(5, 'Deuteronomy'),(6, 'Joshua'),(7, 'Judges'),(8, 'Ruth'),(9, '1 Samuel'),(10, '2 Samuel'),(11, '1 Kings'),(12, '2 Kings'),(13, '1 Chronicles'),(14, '2 Chronicles'),(15, 'Ezra'),(16, 'Nehemiah'),(17, 'Esther'),(18, 'Job'),(19, 'Psalm'),(20, 'Proverbs'),(21, 'Ecclesiastes'),(22, 'Song of Solomon'),(23, 'Isaiah'),(24, 'Jeremiah'),(25, 'Lamentations'),(26, 'Ezekiel'),(27, 'Daniel'),(28, 'Hosea'),(29, 'Joel'),(30, 'Amos'),(31, 'Obadiah'),(32, 'Jonah'),(33, 'Micah'),(34, 'Nahum'),(35, 'Habakkuk'),(36, 'Zephaniah'),(37, 'Haggai'),(38, 'Zechariah'),(39, 'Malachi'),(40, 'Matthew'),(41, 'Mark'),(42, 'Luke'),(43, 'John'),(44, 'Acts'),(45, 'Romans'),(46, '1 Corinthians'),(47, '2 Corinthians'),(48, 'Galatians'),(49, 'Ephesians'),(50, 'Philippians'),(51, 'Colossians'),(52, '1 Thessalonians'),(53, '2 Thessalonians'),(54, '1 Timothy'),(55, '2 Timothy'),(56, 'Titus'),(57, 'Philemon'),(58, 'Hebrews'),(59, 'James'),(60, '1 Peter'),(61, '2 Peter'),(62, '1 John'),(63, '2 John'),(64, '3 John'),(65, 'Jude'),(66, 'Revelation')";
	$wpdb->query($sql);
    }
    
    $file_template=get_option('ca_podcast_file_template');
    if(empty($file_template))
    {
        $file_template='<div class="ca_podcast_file"><h3><a href="[FILE_URI]">[FILE_TITLE] </a> </h3><p>By [SPEAKER_NAME] on [FILE_DATE] as part of the [SERIES_NAME] series<br/>[FILE_DESCRIPTION] </p><p><audio class="sermonmp3" id="[FILE_ID]" src="[FILE_NAME]" preload="none"></audio></p></div>';
        
    }
	else
	{
		if(!strpos($file_template,'class="sermonmp3"'))$file_template=str_replace('<audio ','<audio class="sermonmp3" id="[FILE_ID]" ',$file_template);
		if(!strpos($file_template,'[FILE_PLAYS'))$file_template=str_replace('[SPEAKER_NAME]','[SPEAKER_NAME] ([FILE_PLAYS]) ',$file_template);
	}
	update_option('ca_podcast_file_template',$file_template);
    $series_template=get_option('ca_podcast_series_template');
    if(empty($series_template))
    {
        $series_template='<h2>[SERIES_NAME]</h2>[SERIES_DESCRIPTION]';
        update_option('ca_podcast_series_template',$series_template);
    }
    $speaker_template=get_option('ca_podcast_speaker_template');
    if(empty($speaker_template))
    {
        $speaker_template='<h2>[SPEAKER_NAME]</h2>[SPEAKER_DESCRIPTION]';
        update_option('ca_podcast_speaker_template',$speaker_template);
    }
    
    if(empty($ca_podcast_settings))
    {
        $ca_podcast_settings=array(
            
            'title'=>'',  
            'copyright'=>'',
            'link'=>CA_POD_URL.'podcast.xml',
            'subtitle'=>'',
            'author'=>'',
            'summary'=>'',
            'description'=>'',
            'owner_name'=>'',
            'owner_email'=>'',
            'image'=>'',
            'category'=>'',
        );
        
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "video_url"')!='video_url')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD video_url TEXT AFTER `transcript`';
    $wpdb->query($sql);
}
//change way speakers are stored for v0.5963
$sermons=$wpdb->get_results('SELECT * FROM '.CA_FIL_TBL);
if(!empty($sermons) && OLD_CHURCH_ADMIN_VERSION <0.5963)
{

	foreach ($sermons AS $sermon)
	{
		$speaker=church_admin_get_people($sermon->speaker);
		$sql='UPDATE '.CA_FIL_TBL.' SET speaker="'.esc_sql($speaker).'" WHERE file_id="'.esc_sql($sermon->file_id).'"';
		
		$wpdb->query($sql);
	}

}
//sort service addresses for ver 0.5911 onwards
$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
if(!empty($services))
foreach($services AS $service)
{
	$address=maybe_unserialize($service->address);
	if(is_array($address))
	{
		$address=implode(', ',array_filter($address));
		$wpdb->query('UPDATE '.CA_SER_TBL.' SET address="'.esc_sql($address).'" WHERE service_id="'.esc_sql($service->service_id).'"');
	}
}

//sermonpodcast
//update version
update_option('church_admin_version',$church_admin_version);
}

 
?>