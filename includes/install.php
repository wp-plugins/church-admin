<?php
function church_admin_install()
{
    global $wpdb,$church_admin_version;
    $wpdb->show_errors();
    //migrate church_directory plugin
    //database tables
    //addresslist
    $old_table_name=$wpdb->prefix."church_directory";
    $new_table_name=$wpdb->prefix."church_admin_directory";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}
    
    //small groups
    $old_table_name=$wpdb->prefix."church_directory_smallgroup";
    $new_table_name=$wpdb->prefix."church_admin_smallgroup";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}
    
    //attendance
    $old_table_name=$wpdb->prefix."church_directory_attendance";
    $new_table_name=$wpdb->prefix."church_admin_attendance";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}
    
    //visitors
    $old_table_name=$wpdb->prefix."church_directory_visitors";
    $new_table_name=$wpdb->prefix."church_admin_visitors";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}
    
    //rota
    $old_table_name=$wpdb->prefix."church_directory_rota";
    $new_table_name=$wpdb->prefix."church_admin_rota";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}

    //rota settings
    $old_table_name=$wpdb->prefix."church_directory_rota_settings";
    $new_table_name=$wpdb->prefix."church_admin_rota_settings";
    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name && $wpdb->get_var("SHOW TABLES LIKE '$new_table_name'") != $new_table_name){$wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");}
    
    if(get_option('church_directory_version'))
    {
        update_option('mailserver_url',get_option('church_directory_mail_host'));
        update_option('mailserver_login',get_option('church_directory_mail_user_name'));
        update_option('mailserver_password',get_option('church_directory_mail_password'));
        update_option('mailserver_port','110');
	if(get_option('church_directory_sms_username'))update_option('church_admin_sms_username',get_option('church_directory_sms_username'));
        if(get_option('church_directory_sms_password'))update_option('church_admin_sms_password',get_option('church_directory_sms_password'));
        if(get_option('church_directory_sms_reply'))update_option('church_admin_sms_reply',get_option('church_directory_sms_reply'));
        if(get_option('church_directory_sms_iso'))update_option('church_admin_sms_iso',$_POST['sms_iso']);
	delete_option('church_directory_version');
	delete_option('church_directory_mail_host');
        delete_option('church_directory_mail_user_name');
	delete_option('church_directory_password');
    	delete_option('church_directory_sms_username');
	delete_option('church_directory_sms_password');
	delete_option('church_directory_sms_reply');
    }
    
    //end of migrate over church_directory plugin
    //address book table    
    $table_name = $wpdb->prefix."church_admin_directory";
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name)
    {
        $sql = "CREATE TABLE ". $table_name ." (id int(11) NOT NULL AUTO_INCREMENT, ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, address_line1 varchar(255) NOT NULL,  address_line2 varchar(255) DEFAULT NULL,  city varchar(255) NOT NULL  ,  state varchar(255) NOT NULL ,  zipcode varchar(8) NOT NULL,  homephone varchar(15) NOT NULL,  cellphone varchar(12) NOT NULL,
first_name varchar(255) NOT NULL, last_name varchar(255) CHARACTER SET utf8 NOT NULL, children text NOT NULL, email varchar(255) NOT NULL, email2 varchar(255) NOT NULL, website tinytext NOT NULL, small_group int(11) NOT NULL , PRIMARY KEY (id));";
        $wpdb->query($sql);
    }
    
    //install small group table
    $table_name = $wpdb->prefix."church_admin_smallgroup";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ." (leader int(11) NOT NULL,group_name varchar(255) NOT NULL,whenwhere tinytext NOT NULL,id int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (id));";
        $wpdb->query ($sql);
	$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_smallgroup (leader,group_name,whenwhere,id)VALUES ('0', 'Unattached', '', '1');");
    }

//install emails sent table
    $table_name = $wpdb->prefix."church_admin_email_build";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
$sql='CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'church_admin_email_build (  recipients text NOT NULL,  subject text NOT NULL,  message text NOT NULL,  send_date date NOT NULL,  filename text NOT NULL,  from_name varchar(500) NOT NULL,  from_email varchar(500) NOT NULL,  email_id int(11) NOT NULL auto_increment,  PRIMARY KEY  (email_id)) ;';
$wpdb->query ($sql);
}


    //install rota settings table
    $table_name = $wpdb->prefix."church_admin_rota_settings";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ."  (rota_task TEXT NOT NULL ,rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ));";
	$wpdb->query ($sql);
    }
    
    //install rota table
    $table_name = $wpdb->prefix."church_admin_rota";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ."  (  rota_date date NOT NULL,  rota_option_id int(11) NOT NULL,  who text NOT NULL,  rota_id int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (rota_id));";
	$wpdb->query ($sql);
    }
    
    //install visitors table
    $table_name = $wpdb->prefix."church_admin_visitors";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
    	$sql="CREATE TABLE ". $table_name ."  (first_name TEXT NOT NULL ,last_name TEXT NOT NULL ,address_line1 TEXT NOT NULL ,address_line2 TEXT NOT NULL ,city TEXT NOT NULL ,state TEXT NOT NULL ,zipcode VARCHAR( 20 ) NOT NULL ,email TEXT NOT NULL ,homephone VARCHAR( 20 ) NOT NULL ,cellphone VARCHAR( 20 ) NOT NULL ,first_sunday DATE NOT NULL ,contacted DATE NOT NULL ,contacted_by VARCHAR( 255 ) NOT NULL ,returned DATE NOT NULL ,
regular INT(1) NOT NULL,why INT(1) NOT NULL,small_group INT NOT NULL ,notes TEXT NOT NULL ,children TEXT NOT NULL ,id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
    $wpdb->query($sql);
    }
    
    
    //install attendance table
    $table_name = $wpdb->prefix."church_admin_attendance";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,adults INT(11) NOT NULL,children INT(11)NOT NULL,rolling_adults INT(11) NOT NULL,rolling_children INT(11)NOT NULL,attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
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
  //make sure tables are UTF8  
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_attendance CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_directory CONVERT TO CHARACTER SET '.DB_CHARSET;
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
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_rota CONVERT TO CHARACTER SET '.DB_CHARSET;
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
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_visitors CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
//update pdf cache
update_option('church_admin_calendar_width','630');
update_option('church_admin_pdf_size','A4');
update_option('church_admin_label','L7163');

//sort out wp-cron
if(get_option('church_admin_cron')=='wp-cron')
{
    add_action('church_admin_bulk_email','church_admin_cron');
   $timestamp=mktime();
    wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
}


//update version
update_option('church_admin_version',$church_admin_version);
}

 
?>