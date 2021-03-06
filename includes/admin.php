<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function church_admin_front_admin()
{
	
	global $church_admin_version,$wpdb, $current_user;
	$user_id = $current_user->ID;
	//check if address list populated
	$check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_HOU_TBL);
	if(empty($check)&&empty($_GET['action']))
	{//first run situation...
		echo'<h2>Church Admin Plugin v'.$church_admin_version.'</h2>';
		echo '<p>'.__('Welcome to the church admin plugin. The first job is to get some people into the directory...','church-admin').'</p>';
		echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=csv-import&tab=people','csv_import').'">'.__('Import Address List CSV','church-admin').'</a></p>';
		echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=address&action=church_admin_new_household','new_household').'">'.__('Add a Household','church-admin').'</a> </p>';
	}//end of first run situation
	else
	{//normal
	//backup
	$filename=get_option('church_admin_backup_filename');
	
	
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'];
	if(!empty($filename))$loc=$path.'/church-admin-cache/'.$filename;
	
	?>
	<!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">
     <table class="form_table"><tbody><tr><th scope="row"><h1><span class="dashicons dashicons-admin-home" ></span>Church Admin Plugin v<?php echo $church_admin_version;?></h1></th><td><a style="text-decoration:none" href="http://www.churchadminplugin.com/feed"><span class="dashicons dashicons-rss"></span></a></td><td><form  action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" class="alignright" name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form></td><td><a class="button-secondary" href="http://www.churchadminplugin.com">Support</a></td><td><a class="button-secondary" href="<?php echo wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=refresh_backup','refresh_backup');?>">Refresh DB Backup </a></td>
	 
	
	 <?php
	 if(file_exists($loc))
    {
		
		echo'<td><a class="button-secondary"  target="_blank" href="'.$upload_dir['baseurl'].'/church-admin-cache/'.$filename.'">Download DB Backup</a></td>';
		echo'<td><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=people&action=delete_backup','delete_backup').'">Delete DB Backup</a></td>';
		
    }
	 ?>
	 
	 </tr></tbody></table>
	
        <h2 class="nav-tab-wrapper">
			<a href="admin.php?page=church_admin/index.php&amp;action=people&tab=people" class="nav-tab <?php if(isset( $_GET['tab'])&& $_GET['tab'] == 'people' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-admin-users"></span><?php echo __('People','church-admin');?></a>
			<a href="admin.php?page=church_admin/index.php&amp;action=children&tab=children" class="nav-tab <?php if(isset( $_GET['tab'])&& $_GET['tab'] == 'children' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-admin-users"></span><?php echo __('Children','church-admin');?></a>
			<a href="admin.php?page=church_admin/index.php&amp;action=classes&tab=classes" class="nav-tab <?php if(isset( $_GET['tab'])&& $_GET['tab'] == 'classes' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-admin-users"></span>Classes</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=small_groups&tab=small_groups" class="nav-tab <?php if(isset( $_GET['tab'])&& $_GET['tab'] == 'small_groups' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-nametag"></span>Groups</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=communication&tab=communication" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'communications' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-megaphone"></span>Comms</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=rota&tab=rota" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'rota' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-calendar"></span><?php echo __('Rota','church-admin');?></a>
			<a href="admin.php?page=church_admin/index.php&amp;action=calendar&tab=calendar" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'calendar' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-calendar-alt"></span>Calendar</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=facilities&tab=facilities" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'facilities' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-calendar"></span>Facilities</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=ministries&tab=ministries" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'ministries' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-clipboard"></span>Ministries</a>
			
			<a href="admin.php?page=church_admin/index.php&amp;action=podcast&tab=podcast" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'podcast' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-playlist-audio"></span>Media</a>
			<a href="admin.php?page=church_admin/index.php&amp;action=settings&tab=settings" class="nav-tab <?php if(isset($_GET['tab'])&& $_GET['tab'] == 'settings' )echo 'nav-tab-active' ; ?>"><span class="dashicons dashicons-admin-generic"></span>Settings</a>
	</h2> 
        
         
    </div><!-- /.wrap -->
<?php	}//end normal

} // end sandbox_theme_display

function church_admin_tracking()
{
	global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	
	
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/church-admin-cache/';
		echo'<hr/><h2><a id="tracking">'.__('Attendance Tracking','church-admin').'</h2>';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance&tab=tracking','edit_attendance').'">'.__('Add service attendance','church-admin').'</a></p>';
	echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=individual_attendance">'.__('Individual Attendance','church-admin').'</a></p>';
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	foreach($services AS $service)  echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list&amp;service_id='.$service->service_id.'">'.sprintf( __('Attendance list for %1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</a></p>';
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if(!empty($services))
	{
		require_once(plugin_dir_path(__FILE__).'/graph.php');
		foreach($services AS $service)
		{
			church_admin_weekly_attendance_graph(date('Y'),$service->service_id);
			if(file_exists($path.'weekly-attendance-'.$service->service_id.'-'.date('Y').'.png'))echo'<h3>'.esc_html($service->service_name).'</h3><p><img src="'.content_url('/uploads/church-admin-cache/weekly-attendance-'.$service->service_id.'-'.date('Y').'.png').'"/></p>';
			
		}
	}
	
}

function church_admin_settings_menu()
{
	global $wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	//errors
	$error=get_option('church_admin_plugin_error');
	if(!empty($error))
	{
		echo'<h2>Installation errors</h2>';
		echo'<p>This is what was saved as an error during activation "'.$error.'"</p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=church_admin_activation_log_clear','clear_error').'">'.__('Clear activation errors log','church_admin').'</a></p><hr/>';
	}
    //services
	echo'<h2>'.__('Services','church-admin').'</h2>';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=church_admin_edit_service','edit_service').'">'.__('Add a service','church-admin').'</a></p>';
	require_once(plugin_dir_path(__FILE__).'/services.php');	
	church_admin_service_list();
	

	//backup
	$filename=get_option('church_admin_backup_filename');
	echo'<hr/><h2>'.__('Backup','church-admin').'</h2>';
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=refresh_backup','refresh_backup').'">Refresh Church Admin DB Backup </a></p>';
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'];
	if(!empty($filename))$loc=$path.'/church-admin-cache/'.$filename;
	if(file_exists($loc))
    {
		
		echo'<p><a href="'.$upload_dir['baseurl'].'/church-admin-cache/'.$filename.'">Download Church Admin DB Backup - For recent Updates, it will be for old version</a></p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=settings&action=delete_backup','delete_backup').'">Delete Church Admin DB Backup - Sensible after download!</a></p>';
		
    }
	
	//permissions
	echo'<hr/>';
	require_once(plugin_dir_path(__FILE__).'communication_settings.php');	
	church_admin_settings();
	//shortcodes
	//directory
	echo'<hr/><h2>Shortcodes</h2>';
    echo'<h3>Directory</h3>';
    echo'<p>The directory shortcode is <strong>[church_admin type=address-list member_type_id=# photo=1 map=1 api_key=#]</strong></p>';
    echo'<p>photo=1 will display a thumbnail if one has been uploaded</p>';
	echo'<p>map=1 shows a map for households that have had a map set - Google Static Maps require an API Key for more than 25,000 views per day, so we require an api key to show the static map image from v0.5943. Please sign up for an api key using your Google account at <a href="https://code.google.com/apis">https://code.google.com/apis</a></p>';
    echo'<p>Member type can include more than one member type separated with commas e.g.<strong>[church_admin type=address-list member_type_id=1,2 map=1 photo=1]</strong></p>';
    echo'<p>kids=0 will stop children being shown.</p>';
    $results=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL.' ORDER BY member_type_id');
    if($results)
    {
        echo '<p>These are your current member types</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.esc_html($row->member_type).': </label>member_type_id='.intval($row->member_type_id).'</p>';
        }
    }
    //recent
	echo'<h3>Recent Visitors</h3>';
	echo'<p><strong>[church_admin type=recent member_type_id=#] </strong>Lists your recent visitors - just specify member_types_ids</p>';
    //small groups
    echo'<h3>Small groups</h3>';
    echo'<p><strong>[church_admin type=small-groups-list map=1]</strong> lists all your small groups\' details in map form (map=1)or as a list (map=0)</p>';
    echo'<p><strong>[church_admin type=small-groups member_type_id=# ]</strong> lists all your small groups and their members for a specific member type</p>';
    
    //rotas
    echo'<h3>Rotas</h3>';
    echo'<p><strong>[church_admin type=rota service_id=1]</strong> lists the upcoming rota for a particular service</p>';
    $results=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL.' ORDER BY service_id');
    if($results)
    {
        echo '<p>These are your current services</p>';
        foreach($results AS $row)
        {
            echo'<p><label>'.esc_html($row->service_name).' on '.esc_html($days[$row->service_day]).' at '.esc_html($row->service_time).' </label>service_id='.intval($row->service_id).'</p>';
        }
    }
    
    //calendar
    echo'<h3>Calendar</h3>';
    $results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
    if($results)
    {
        foreach($results AS $row)
        {
             $shortcode='<strong>[church_admin type=calendar-list category='.esc_html($row->cat_id).' weeks=4]</strong>';
            echo'<p><label>Calendar List by Category '.esc_html($row->category).'</label>'.$shortcode.'</p>';
        }
    }
    //user registration
    echo'<h3>User Registration</h3>';
    echo'<p><strong>[church_admin_register]</strong></p>';
    
    //recent activity
    echo'<h3>Recent Directory Activity</h3>';
    echo'<p><strong>[church_admin_recent]</strong></p>';
    
    //member map
    echo'<h3>Member Map</h3>';
    echo'<p><strong>[church_admin_map member_type_id=# zoom=13 small_group=1]</strong> - zoom is Google map zoom level, small_group=1 for different colours for small groups, 0 for all in red</p>';

	//Attendance
	 echo'<h3>Attendance</h3>';
    echo'<p><strong>[church_admin type="weekly-attendance" year=# service_id=# ]</strong> - Displays graph image 700x500px; year is a single year currently eg 2013, service_id which service</p>';
    echo'<p><strong>[church_admin type="monthly-attendance" year=# service_id=# ]</strong> -Displays graph image 700x500px, year is a single year currently eg 2013, service_id which service</p>';
    //Birthdays
	echo'<h3>'.__('Birthdays','church-admin').'</h3>';
	echo'<p><strong>[church_admin type="birthdays" member_type_id=# days=#]</strong> - Displays upcoming birthdays for the next # days for member_types_ids #</p>';
	
}
function church_admin_podcast()
{
	require_once(plugin_dir_path(__FILE__).'/sermon-podcast.php');
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	echo'<h2>Podcast</h2>';
	echo '<table ><tr><td>The maximum file you can upload from the browser is '.church_admin_max_file_upload_in_bytes().'MB.<br/> Upload bigger files easily using the Church Admin Media Windows Upload Utility</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="7FYG7STCC5W5J"><input type="submit" value="Buy on Paypal for &pound;5GBP" class="button-primary"></form></td></tr></table>';
	echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_file&tab=podcast','edit_podcast_file').'">Upload or add external mp3 File</a></p>';
    echo '<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=check_files&tab=podcast','check_podcast_file').'">Add Already Uploaded Files</a></p>';
	echo'<p><a class="button-secondary"  href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=list_sermon_series&tab=podcast",'list_sermon_series').'">'.__('List Sermon Series','church-admin').'</a></p>';
    echo'<p><a class="button-secondary"  href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=podcast_settings&tab=podcast",'podcast_settings').'">'.__('iTunes Compatible RSS Settings','church-admin').'</a></p>';
	ca_podcast_list_files();
	
}
function church_admin_children()
{
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	
	//kidswork
	echo'<h2>'.__('Kids Work','church-admin').'</h2>';
	require_once(plugin_dir_path(__FILE__).'/kidswork.php');
	church_admin_kidswork();
	
}
function church_admin_ministries()
{

	//ministries
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	echo'<h2>Ministries</h2>';
	require_once(plugin_dir_path(__FILE__).'/departments.php');
	church_admin_department_list();
	
	//hope team
	echo'<hr/><h2>'.__('Hope Team','church-admin').'</h2>';
	echo'<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_hope_team_job','hope_team_jobs').'">Add a hope team job</a> <a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=ministries&action=edit_hope_team','edit_hope_team').'">Edit who is in Hope Team</a></p>';
	require_once(plugin_dir_path(__FILE__).'/hope-team.php');
	church_admin_hope_team_jobs();
	
	echo'<p><a href="'.home_url().'/?download=hope_team_pdf">Hope Team PDF</a></p>';

	
}


function church_admin_rota_main($service_id=NULL)
{
	global $days,$wpdb;
	$days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	
		$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if($wpdb->num_rows>1)
			    {
				echo'<form action="admin.php?page=church_admin/index.php&tab=rota&amp;action=church_admin_rota_list" method="POST">';
				echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Select a service rota','church-admin').'</th><td><select name="service_id" >';
				echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
			        foreach($services AS $service)
				{
				    echo'<option value="'.intval($service->service_id).'">'.esc_html(sprintf( __('%1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time)).'</option>';
				}
				echo'</select> <input type="submit" value="'.__('Select','church-admin').'"/></tr></tbody></table>';
				echo'</form>';
			    }
			    else
			    {
				echo'<p><a href="admin.php?page=church_admin/index.php&tab=rota&amp;action=church_admin_rota_list&service_id=1">'.__('View service rota','church-admin').'</a></p>';
			    }
echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=rota&amp;action=church_admin_rota_settings_list&tab=rota","rota_settings_list").'">'.__('View/Edit Rota Jobs','church-admin').'</a></p>';
	echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=rota&amp;action=church_admin_edit_rota_settings",'edit_rota_settings').'" >'.__('Add more rota jobs','church-admin').'</a></p>';
	echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&tab=rota&amp;action=church_admin_edit_rota&tab=rota&service_id=".$service_id,'edit_rota').'">'.__('Add dates to rota','church-admin').'</a></p>';
	echo'<hr/><h2>'.__('Rota','church-admin').'</h2>';
	$email_day=get_option('church_admin_email_rota_day');
	if(!empty($email_day)) echo'<p><strong>'.sprintf(__('This weeks rotas are automatically emailed on %1$s, when your website is first accessed that day.','church-admin'),$days[$email_day+1]).'</strong></p>';
	echo'<form action="" method="POST">';
	echo'<p><label>'. __("Automatically email current week's rota",'church-admin').'</label>';
	echo'<select name="email_rota_day">';
	echo'<option value="8"'.selected( $email_day, NULL ).'>'.__('No Auto Send','church-admin').'</option>';
		echo'<option value="1"'.selected( $email_day, 1 ).'>'.__('Monday','church-admin').'</option>';
	echo'<option value="2"'.selected( $email_day, 2 ).'>'.__('Tuesday','church-admin').'</option>';
	echo'<option value="3"'.selected( $email_day, 3 ).'>'.__('Wednesday','church-admin').'</option>';
	echo'<option value="4"'.selected( $email_day, 4 ).'>'.__('Thursday','church-admin').'</option>';
	echo'<option value="5"'.selected( $email_day, 5 ).'>'.__('Friday','church-admin').'</option>';
	echo'<option value="6"'.selected( $email_day, 6 ).'>'.__('Saturday','church-admin').'</option>';
	echo'<option value="7"'.selected( $email_day, 7 ).'>'.__('Sunday','church-admin').'</option>';
	echo'</select><input type="submit" value="Save"/></p></form>';
	
    echo'<form action="'.admin_url().'" method="GET"><input type="hidden" name="page" value="church_admin/index.php"/><input type="hidden" name="action" value="church_admin_email_rota"/><input type="hidden" name="tab" value="rota">';
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	
    echo'<p><label>'.__('Email out service rota','church-admin').'</label><select name="service_id">';
    echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
    foreach($services AS $service)
    {
       echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s ', 'church-admin'), $service->service_name,$days[$service->service_day],$service->service_time).'</option>';
    }
    echo'</select><input type="submit" name="submit" value="'.__('Send service rota','church-admin').'"></p>';
    echo'</form>';
    echo'<hr/><h2>'.__('Horizontal Rota PDF','church-admin').' </h2><form action="'.home_url().'" method="GET"><table class="form-table"><tr><th scope="row">'.__('Select Service','church-admin').'<input type="hidden" name="download" value="horizontal_rota_pdf"/></th><td><select name="service_id">';
				
				$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
			    foreach($services AS $service)
			    {	
					echo'<option value="'.$service->service_id.'">'.sprintf( __('%1$s on %2$s at %3$s', 'church-admin'),$service->service_name,$days[$service->service_day],$service->service_time).'</option>';
				}
				echo'</select></td></tr>';
				$rota_jobs=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
				foreach($rota_jobs AS $rota_job)
				{
					echo'<tr><th scope="row">'.$rota_job->rota_task.'</th><td><input type="checkbox" name="rota_id[]" value="'.$rota_job->rota_id.'"/> '.__('Initials?','church-admin').'<input type="checkbox" name="initials[]" value="'.$rota_job->rota_id.'"/></td></tr>';
		
				}
				
				echo'<tr><td colspan="2"><input type="submit" value="'.__('Create PDF','church-admin').'"/></td></tr></table></form>';
   echo'<hr/>';
	require_once(plugin_dir_path(__FILE__).'/rota.php');
	church_admin_rota_list($service_id);
				
}

function church_admin_smallgroups_main()
{
    $member_type=church_admin_member_type_array();
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
	require_once(plugin_dir_path(__FILE__).'/small_groups.php');
	church_admin_small_groups();
    echo '<p><strong>'.__('Download a small group PDF','church-admin').'</strong></p><form name="address_list_form" action="'.home_url().'" method="get"><input type="hidden" name="download" value="smallgroup"/>';
	foreach($member_type AS $key=>$value)
	{
		echo'<p><label>'.esc_html($value).'</label><input type="checkbox" value="'.esc_html($key).'" name="member_type_id[]"/></p>';
	}
	echo wp_nonce_field('smallgroup','smallgroup');
	echo'<input type="submit" value="'.__('Download','church-admin').'"/></form></p>';

}
function church_admin_communication()
{
	echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_sms&tab=communication">'.__('Send Bulk SMS','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_send_email&tab=communication">'.__('Send Bulk Email','church-admin').'</a></p>';
	echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=mailchimp_sync&tab=communication">'.__('Sync Mailchimp Account','church-admin').'</a></p>';
	echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=prayer_chain_message&tab=communication','prayer_chain_message').'">Send Prayer Chain Message</a></p>';
	require_once(plugin_dir_path(__FILE__).'/email.php');
	church_admin_email_list();
}

function church_admin_people_main()
{
    global $people_type;
	$member_type=church_admin_member_type_array();
    		global $wpdb;
	$check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_HOU_TBL);
	if(!empty($check))
	{
		echo'<h2>'.__('People','church-admin').'</h2>';
		echo '<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&tab=address&action=church_admin_new_household','new_household').'">'.__('Add a Household','church-admin').'</a> </p>';
		echo'<form name="ca_search" action="admin.php?page=church_admin/index.php&tab=address" method="POST"><table class="form-table"><tbody><tr><th scope="row">'.__('Search','church-admin').'</th><td><input name="church_admin_search" style="width:100px;" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></td></tr></table></form>';
		echo'<p><a  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=csv-import&tab=people','csv_import').'">Import CSV</a></p>';
		echo'<p><a href="#tracking">'.__('Attendance Tracking','church-admin').'</a></p>';
		echo'<p><a href="#classes">'.__('Classes','church-admin').'</a></p>';
		echo'<p><a href="#followup">'.__('Follow Up','church-admin').'</a></p>';
		echo'<p><a href="#recent_people">'.__('Recent People Activity','church-admin').'</a></p>';
		echo'<p><a href="#member_types">'.__('Member Types','church-admin').'</a></p>';
	
			
				//select member type address list to view.
			    echo'<table class="form-table"><tbody><tr><th scope="row">'.__('Select a directory to view','chuch-admin').'</th><td><form name="address" action="admin.php?page=church_admin/index.php&amp;action=church_admin_address_list" method="POST"><select name="member_type_id" >';
			    echo '<option value="">'.__('Choose Member Type...','church-admin').'</option>';
			    foreach($member_type AS $key=>$value)
			    {
					$count=$wpdb->get_var('SELECT COUNT(people_id) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($key).'"');
					echo '<option value="'.esc_html($key).'" >'.esc_html($value).' ('.$count.' people)</option>';
			    }
			    echo'</select><input type="submit" value="'.__('Go','church-admin').'"/></form></td></tr></tbody></table>';
			    echo '<table class="form-table"><tbody><tr><th scope="row">'.__('Download an address list PDF','church-admin').'</th><td>';
			    if(!empty($member_type))
			    {
				foreach($member_type AS $key=>$value)
				{
				    echo'<a href="'.home_url().'/?download=mailinglabel&amp;mailinglabel='.wp_create_nonce('mailinglabel'.$key).'&amp;member_type_id='.intval($key).'">'.esc_html($value).' - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</a><br/>';
				}
				foreach($member_type AS $key=>$value)
				{
				    echo'<a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('member'.$key).'&amp;member_type_id='.intval($key).'">'.esc_html($value).' '.__('Address List PDF','church-admin').'</a><br/>';
				}
			    }
			    echo'</td></tr></tbody></table>';
				
			    echo'<hr/><table class="form-table"><tbody><th scope="row">'.__('Download an csv of people','church-admin').'</th><td>';
			    echo'<form action="'.home_url().'" method="get">';
				echo wp_nonce_field('people-csv','people-csv');
			    foreach($member_type AS $key=>$value)
				{
						echo '<input type="checkbox" name="member_type_id[]" value="'.esc_html($key).'"/>'.esc_html($value).'<br/>';
				}
				foreach($people_type AS $key=>$value)
				{
						echo '<input type="checkbox" name="people_type_id[]" value="'.esc_html($key).'"/> '.esc_html($value).'<br/>';
				}
				$gender=get_option('church_admin_gender');
				foreach($gender AS $key=>$value)
				{
					echo'<input type="checkbox" name="sex[]" value="'.esc_html($key).'" />'.esc_html($value).'<br/>';
				}
				
				echo'<input type="checkbox" name="address" value="1" /> Include Address<br/>';
				echo'<input type="checkbox" name="small_group" value="1" /> Include Small Group<br/>';
				echo'<input type="hidden" name="download" value="people-csv"/><input type="submit" value="Download"/></p>';
			    echo'</form></td></tr></tbody></table>';
			    echo'<hr/>';
			    require_once(plugin_dir_path( dirname(__FILE__) ).'includes/people_activity.php');
				church_admin_recent_people_activity();
    //people in directory
	//member types
	 echo'<hr/><h2><a id="member_types">'.__('Member Types','church-admin').'</a></h2>';
	 echo'<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type",'edit_member_type').'">'.__('Add a member Type','church-admin').'</a></p>';
	require_once(plugin_dir_path(__FILE__).'/member_type.php');
	church_admin_member_type();
	church_admin_tracking();
	echo'<hr/><h2><a id="followup">'.__('Follow Up','church-admin').'</h2>';
	require_once(plugin_dir_path(__FILE__).'funnel.php');
	church_admin_funnel_list();
	require_once(plugin_dir_path(__FILE__).'classes.php');
	church_admin_classes();
	}
}

?>