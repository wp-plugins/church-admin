<?php

function church_admin_front_admin()
{
    
    global $people_type,$member_type,$wpdb,$days,$screen_layout_columns,$church_admin_version;

 
    /* Add screen option: user can choose between 1 or 2 columns (default 2) */
    add_screen_option('layout_columns', array('max' => 1, 'default' => 1) );
    
    $user_permissions=get_option('church_admin_user_permissions');
    
    ?>
    <div class="wrap" id="church-admin">
	<div id="icon-index" class="icon32"><br/></div><h2>Church Admin Plugin v<?php echo $church_admin_version;?></h2>
	<div id="poststuff">
	
	<div class="church_admin_left" ><p>
	<?php    echo '<p><label>'.__('If you find the plugin helpful, please contribute!','church-admin').'</label>';?>
	<form  action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif"  name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form></p>
		
		<?php require_once(CHURCH_ADMIN_INCLUDE_PATH.'news-feed.php');echo church_admin_news_feed();?>
		</div>
	<div class="church_admin_left" ><p>
		<a href="http://www.worshipbanners.co.uk" class="alignleft" ><img src="<?php echo CHURCH_ADMIN_IMAGES_URL;?>roll-up-banner-examples.PNG" width="300" height="182" alt="Worship Banners"/></a>
		<br/><h3><a href="http://www.worshipbanners.co.uk">Worshipbanners.co.uk</a></h3>
		The plugin author has recently started a business selling rollup banners to use in Churches.<br/> <a href="http://www.worshipbanners.co.uk">Why not have a look?</a>
	</p>
	</div><div class="clear"></div>
		<?php if(file_exists(CHURCH_ADMIN_EMAIL_CACHE.'Church_Admin_Backup.sql.gz')){	echo '<h3>A plugin database backup is available - <a href="#church-admin-backup">please download and delete</a></h3>';}?>	  <!-- #post-body .metabox-holder goes here -->
		<div id="post-body" class="metabox-holder columns-1">
		    <!-- meta box containers here -->
		    <form  method="get" action="">
		        <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		    wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			echo'</form>';
			
			if(church_admin_level_check('Directory')){	add_meta_box("church-admin-backup", __('Church Admin Backup', 'church-admin'), "church_admin_backup_meta_box", "church-admin");}
			if(church_admin_level_check('Directory')){  add_meta_box("church-admin-shortcodes", __('Shortcodes', 'church-admin'), "church_admin_shortcodes_meta_box", "church-admin");
			if(church_admin_level_check('Bulk Email')){	add_meta_box("church-admin-communications", __('Communications', 'church-admin'), "church_admin_communications_meta_box", "church-admin");}
			if(church_admin_level_check('Directory')){	add_meta_box("church-admin-recent-people-activity", __('Recent People Activity', 'church-admin'), "church_admin_recent_people_activity_meta_box", "church-admin");}
			if(church_admin_level_check('Directory')){	add_meta_box("church-admin-people-functions", __('People Functions', 'church-admin'), "church_admin_people_functions_meta_box", "church-admin");}
			if(church_admin_level_check('Sermons'))	add_meta_box("church-admin-sermons", __('Sermon mp3 podcasting', 'church-admin'), "church_admin_sermons_meta_box", "church-admin");}
			if(church_admin_level_check('Directory')){ 	add_meta_box("church-admin-departments", __('Ministries', 'church-admin'), "church_admin_departments_meta_box", "church-admin");}
			if(church_admin_level_check('Member Type')){	add_meta_box("church-admin-member-types", __('Member Types', 'church-admin'), "church_admin_member_types_meta_box", "church-admin");}
			if(church_admin_level_check('Funnel')){ 	add_meta_box("churchadmin-follow-up", __('Follow Up', 'church-admin'), "church_admin_followup_meta_box", "church-admin");}
			if(church_admin_level_check('Rota')){ 		add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");}
			if(church_admin_level_check('Calendar')){ 	add_meta_box("church-admin-calendar", __('Calendar', 'church-admin'), "church_admin_calendar_meta_box", "church-admin");}
			if(church_admin_level_check('Small Groups')){ 	add_meta_box("church-admin-small_groups", __('Small Groups', 'church-admin'), "church_admin_smallgroups_meta_box", "church-admin");}
			if(church_admin_level_check('Attendance')){	add_meta_box("church-admin-attendance", __('Attendance', 'church-admin'), "church_admin_attendance_meta_box", "church-admin");}
			if(church_admin_level_check('Service')){  add_meta_box("church-admin-services", __('Services', 'church-admin'), "church_admin_services_meta_box", "church-admin");}
			do_meta_boxes('church-admin','advanced',null);?>
		    </form>
		</div>
	</div>
    </div>
    <script type="text/javascript">
	jQuery(document).ready(function($){$(".if-js-closed").removeClass("if-js-closed").addClass("closed");
			       
				postboxes.add_postbox_toggles( 'church-admin');
				});
    </script>
    <?php


}

function church_admin_backup_meta_box()
{
//show backup
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=refresh_backup','refresh_backup').'">Refresh Church Admin DB Backup </a></p>';
		
    if(file_exists(CHURCH_ADMIN_EMAIL_CACHE.'Church_Admin_Backup.sql.gz'))
    {
		echo'<p><a href="'.CHURCH_ADMIN_EMAIL_CACHE_URL.'Church_Admin_Backup.sql.gz">Download Church Admin DB Backup - For recent Updates, it will be for old version</a></p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_backup','delete_backup').'">Delete Church Admin DB Backup - Sensible after download!</a></p>';
		
    }
}
function church_admin_recent_people_activity_meta_box()
{
		global $wpdb;
		//check to see if directory is populated!
    $check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL);
    if(empty($check)||$check<1)
    {
	
	echo'<p><strong>You need some people in the directory before you can See Recent People Activity</strong></p>';
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
	
    }
    else
    {//people stored in directory
     require_once(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');
    church_admin_recent_people_activity();
    }
}
function church_admin_people_functions_meta_box()
{
    global $member_type,$people_type;
    		global $wpdb;
		//check to see if directory is populated!
    $check=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL);
    if(empty($check)||$check<1)
    {
	
	echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people','edit_people').'">'.__('Add a new person (not connected to a current household)','church-admin').'</a></p>';
			    
	
    }
    else
    {//people stored in directory
				echo'<form name="ca_search" action="admin.php?page=church_admin/index.php" method="POST"><p><label>'.__('Search','church-admin').'</label><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="Go"/></p></form>';
			    
				echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_migrate_users','migrate_users').'">'.__('Import Wordpress Users (only new ones added)','church-admin').'</a></p>';
			    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
			    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people','edit_people').'">'.__('Add a new person (not connected to a current household)','church-admin').'</a></p>';
			    echo'<p><label>Select Address List</label><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list" method="POST"><select onchange="this.form.submit();" name="member_type_id" >';
			    echo '<option value="">'.__('Choose Member Type...','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
				echo '<option value="'.$key.'" >'.$value.'</option>';
			    }
			    echo'</select></form></p>';
			    echo '<p>'.__('Download an address list PDF','church-admin').'</p><p>';
			    if(!empty($member_type))
			    {
				foreach($member_type AS $key=>$value)
				{
				    echo'<a href="'.home_url().'/?download=mailinglabel&amp;mailinglabel='.wp_create_nonce($key).'&amp;member_type_id='.$key.'">'.$value.' - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</a><br/>';
				}
				foreach($member_type AS $key=>$value)
				{
				    echo'<a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce($key).'&amp;member_type_id='.$key.'">'.$value.' '.__('Address List PDF','church-admin').'</a><br/>';
				}
			    }
			    echo'</p>';
			    echo'<h2>'.__('Download an csv of people','church-admin').'</h2>';
			    echo'<form action="'.home_url().'" method="get">';
				echo wp_nonce_field('people-csv','people-csv');
			    foreach($member_type AS $key=>$value)
				{
						echo '<p><label>'.$value.'</label><input type="checkbox" name="member_type_id[]" value="'.$key.'"/></p>';
				}
				foreach($people_type AS $key=>$value)
				{
						echo '<p><label>'.$value.'</label><input type="checkbox" name="people_type_id[]" value="'.$key.'"/></p>';
				}
				echo'<p><label>Male</label><input type="checkbox" name="sex[]" value="1" /><br/>';
				echo'<p><label>Female</label><input type="checkbox" name="sex[]" value="0" /><br/>';
				echo'<p><label>Include Address</label><input type="checkbox" name="address" value="1" /><br/>';
				echo'<p><label>Include Small Group</label><input type="checkbox" name="small_group" value="1" /><br/>';
				echo'<input type="hidden" name="download" value="people-csv"/><input type="submit" value="Download"/></p>';
			    echo'</form>';
			    
			    
    }//people in directory
}

function church_admin_sermons_meta_box()
{
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=list_sermon_series",'list_sermon_series').'">'.__('List Sermon Series','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=list_files",'list_files').'">'.__('List Sermon Files','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=edit_file",'edit_podcast_file').'">'.__('Upload Sermon File','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=check_files",'check_files').'">'.__('Attach Uploaded Files','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=podcast_settings",'podcast_settings').'">'.__('iTunes Compatible RSS Settings','church-admin').'</a></p>';
    
}

function church_admin_departments_meta_box()
{
    echo'<p>'.__('In this section you can set up the ministry a person is involved in or a role that they have e.g. Elder or Small Group Leader or P.A. operator','church-admin').'</p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_department_list",'department_list').'">'.__('Ministry List','church-admin').'</a></p>';
	 echo'<p><a href="'.wp_nonce_url(site_url().'/?download=ministries_pdf','ministries_pdf').'">'.__('Ministries PDF','church-admin').'</a></p>';

}
function church_admin_member_types_meta_box()
{
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type",'edit_member_type').'">'.__('Add a member Type','church-admin').'</a></p>';
			    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_member_type">'.__('Member Type List','church-admin').'</a></p>';

}
function church_admin_communications_meta_box()
{
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_sms">'.__('Send Bulk SMS','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_email">'.__('Send Bulk Email','church-admin').'</a></p>';
	echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=mailchimp_sync">'.__('Sync Mailchimp Account','church-admin').'</a></p>';
}
function church_admin_followup_meta_box()
{
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel','edit_funnel').'">'.__('Add a follow up funnel','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_funnel_list">'.__('Follow Up Funnel List','church-admin').'</a></p>';  

}
function church_admin_rota_meta_box()
{
    global $wpdb,$days;
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    
    echo'<form action="'.admin_url().'" method="GET"><input type="hidden" name="page" value="church_admin/index.php"/><input type="hidden" name="action" value="church_admin_email_rota"/>';
    echo'<p><label>'.__('Email out service rota','church-admin').'</label><select name="service_id">';
    echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
    foreach($services AS $service)
    {
       echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s ', 'church-admin'), $service->service_name,$days[$service->service_day],$service->service_time).'</option>';
    }
    echo'</select><input type="submit" name="submit" value="Send service rota"></p>';
    echo'</form>';
    
    echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_rota_settings_list","rota_settings_list").'">'.__('View/Edit Rota Jobs','church-admin').'</a></p>';
			    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota_settings",'edit_rota_settings').'" >'.__('Add more rota jobs','church-admin').'</a></p>';
			    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota",'edit_rota').'">'.__('Add to rota','church-admin').'</a></p>';
			    
			    
			    if($wpdb->num_rows>1)
			    {
				echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
				echo'<p><label>Select a service rota</label><select name="service_id" onchange="this.form.submit();">';
				echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
			        foreach($services AS $service)
				{
				    echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</option>';
				}
				echo'</select></p>';
				echo'</form>';
			    }
			    else
			    {
				echo'<a href="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list&service_id=1">View service rota</a>';
			    }
			    echo'<p><strong>'.__('Download a service rota CSV','church-admin').'</strong><br/>';
			    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			    foreach($services AS $service)
			    {
				echo'<a href="'.home_url().'/?download=rotacsv&amp;rotacsv='.wp_create_nonce('rotacsv').'&amp;service_id='.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</a><br/>';}
			    echo'</p>';
}
function church_admin_calendar_meta_box()
{
    
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_calendar">'.__('Add calendar Event','church-admin').'</a></p>';
			    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_calendar_list">'.__('View Calendar','church-admin').'</a></p>';
    			    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_add_category','add_category').'">'.__('Add a category','church-admin').'</a></p>';
			    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_category_list','category_list').'">'.__('Category List','church-admin').'</a></p>';
			    echo '<p><label>'.__('Download a year planner PDF','church-admin').'</label><form name="calendar_form" action="" method="get"><select name="calendar_form_links" onchange="window.location=document.calendar_form.calendar_form_links.options[document.calendar_form.calendar_form_links.selectedIndex].value">';
			    echo'<option selected="selected" value="'.home_url().'/?download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.date('Y').'">-- '.__('Choose a pdf','church-admin').' --</option>';
			    for($x=0;$x<5;$x++)
			    {
				$y=date('Y')+$x;
				echo '<option value="'.home_url().'/?download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.' '.__('Year Planner','church-admin').'</option>';
			    }
			    echo'</select></form></p>';
}
function church_admin_smallgroups_meta_box()
{
    global $member_type;
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_small_group",'edit_small_group').'">'.__('Add a small group','church-admin').'</a></p>';
			    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_small_groups">'.__('Small Group List','church-admin').'</a></p>';
			    echo '<p><label>'.__('Download an small group PDF','church-admin').'</label><form name="address_list_form" action="'.home_url().'" method="get"><input type="hidden" name="download" value="smallgroup"/><select name="member_type_id" onchange="this.form.submit()">';
			    
				echo'<option selected="selected" value="1">-- '.__('Choose a pdf','church-admin').' --</option>';
			    foreach($member_type AS $key=>$value)
			    {
				echo'<option value="'.$key.'">'.$value.' '.__('Small group PDF','church-admin').'</option>';
			    }
			    echo '<option value="'.home_url().'/?download=smallgroup&amp;smallgroup='.wp_create_nonce('smallgroup').'&amp;member_type_id='.implode(",",array_keys($member_type)).'">'.__('All member types Small group PDF','church-admin').'</option>';
			    echo wp_nonce_field('smallgroup','smallgroup');
				echo'</select></form></p>';

}
function church_admin_services_meta_box()
{
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_service_list">'.__('Service List','church-admin').'</a></p>';
				echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service','edit_service').'">'.__('Add a service','church-admin').'</a></p>';

}
function church_admin_attendance_meta_box()
{
    global $wpdb,$days;
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_metrics">'.__('Church Attendance Data','church-admin').'</a></p>';
			    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			    foreach($services AS $service)  echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list&amp;service_id='.$service->service_id.'">'.sprintf( __('Attendance list for %1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</a></p>';
			   

}


function church_admin_shortcodes_meta_box()
{
    global $wpdb,$days;
    
    //directory
    echo'<h2>Directory</h2>';
    echo'<p>The directory shortcode is <strong>[church_admin type=address-list member_type_id=# photo=1 map=1 api_key=#]</strong></p>';
    echo'<p>photo=1 will display a thumbnail if one has been uploaded</p>';
	echo'<p>map=1 shows a map for households that have had a map set - Google Static Maps require an API Key for more than 25,000 views per day, so we require an api key to show the static map image from v0.5943. Please sign up for an api key using your Google account at <a href="https://code.google.com/apis">https://code.google.com/apis</a></p>';
    echo'<p>Member type can include more than one member type separated with commas e.g.<strong>[church_admin type=address-list member_type_id=1,2 map=1 photo=1]</strong></p>';
    
    $results=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL.' ORDER BY member_type_id');
    if($results)
    {
        echo '<p>These are your current member types</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.$row->member_type.': </label>member_type_id='.esc_html($row->member_type_id).'</p>';
        }
    }
    
    //small groups
    echo'<h2>Small groups</h2>';
    echo'<p><strong>[church_admin type=small-groups-list map=1]</strong> lists all your small groups\' details in map form (map=1)or as a list (map=0)</p>';
    echo'<p><strong>[church_admin type=small-groups member_type_id=# ]</strong> lists all your small groups and their members for a specific member type</p>';
    
    //rotas
    echo'<h2>Rotas</h2>';
    echo'<p><strong>[church_admin type=rota service_id=1]</strong> lists the upcoming rota for a particular service</p>';
    $results=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL.' ORDER BY service_id');
    if($results)
    {
        echo '<p>These are your current services</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.$row->service_name.' on '.$days[$row->service_day].' at '.$row->service_time.' </label>service_id='.esc_html($row->service_id).'</p>';
        }
    }
    
    //calendar
    echo'<h2>Calendar</h2>';
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    if($results)
    {
        foreach($results AS $row)
        {
             $shortcode='<strong>[church_admin type=calendar-list category='.$row->cat_id.' weeks=4]</strong>';
            echo'<p><label>Calendar List by Category '.esc_html($row->category).'</label>'.$shortcode.'</p>';
        }
    }
    //user registration
    echo'<h2>User Registration</h2>';
    echo'<p><strong>[church_admin_register]</strong></p>';
    
    //recent activity
    echo'<h2>Recent Directory Activity</h2>';
    echo'<p><strong>[church_admin_recent]</strong></p>';
    
    //member map
    echo'<h2>Member Map</h2>';
    echo'<p><strong>[church_admin_map member_type_id=# zoom=13 small_group=1]</strong> - zoom is Google map zoom level, small_group=1 for different colours for small groups, 0 for all in red</p>';

	//Attendance
	 echo'<h2>Attendance</h2>';
    echo'<p><strong>[church_admin type="weekly-attendance" year=# service_id=# ]</strong> - Displays graph image 700x500px; year is a single year currently eg 2013, service_id which service</p>';
    echo'<p><strong>[church_admin type="monthly-attendance" year=# service_id=# ]</strong> -Displays graph image 700x500px, year is a single year currently eg 2013, service_id which service</p>';
    //Birthdays
	echo'<h2>'.__('Birthdays','church-admin').'</h2>';
	echo'<p><strong>[church_admin type="birthdays" member_type_id=# days=#]</strong> - Displays upcoming birthdays for the next # days for member_types_ids #</p>';
	

}

?>