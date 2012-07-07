<?php

/*

Plugin Name: church_admin
Plugin URI: http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin
Description: A church admin system with address book, small groups, rotas, bulk email  and sms
Version: 0.4.2
Author: Andy Moyle


Author URI:http://www.themoyles.co.uk

License:
----------------------------------------

    
Copyright (C) 2010 Andy Moyle



    This program is free software: you can redistribute it and/or modify

    it under the terms of the GNU General Public License as published by

    the Free Software Foundation, either version 3 of the License, or

    (at your option) any later version.



    This program is distributed in the hope that it will be useful,

    but WITHOUT ANY WARRANTY; without even the implied warranty of

    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

    GNU General Public License for more details.



	http://www.gnu.org/licenses/

----------------------------------------

The structure of this plugin is as follows:

===========================================
MAIN FILES
----------
index.php - main plugin file

INCLUDES
---------

------------------------------------------------


Version History
================================================
0.1 2010-11-31 Initial Release
0.2 2011-01-05 Minor bug fixes for small groups
0.3 2011-01-24 Added calendar
0.31.2 2011-01-31 Fixed install roblems from svn
0.31.3 2011-01-31 Order calendar widget by start date and multiple events per day allowed
0.31.4 2011-02-04 Calendar Event deletes added, fixed jquery conflict on admin pages, rota for today on a sunday
0.32.1    2011-02-08  A4 year planner added,rebuilds cronemail.php on upgrade
0.32.2 2011-02-16 Valid XHTML on admin pages
0.32.3 2011-02-18 Minor formatting fixes on admin pages
0.32.4 20110-03-06 Various fixes
0.32.6 2011-03-08 Calendar CSS fixed
0.32.7 2011-03-14 Calendar fix on error in form - not showing in red
0.32.8 2011-03-23 Calendar times and dates use Wordpress format settings, pdf's adjustable for different sizes
0.32.9 2011-03-25 Agenda view date select fixed
0.32.9.1 2011-04-18 Fixed cron issue
0.32.9.2 2011-05-24 Fixed jquery conflict issue in Calendar tooltip display
0.32.9.3 2011-06-22 Added category shortcode to calendar-list & basic email template to bulk email
0.32.9.5 2011-07-20 Error message if calendar event not saved!
0.32.9.6 2011-08-25 Minor CSS tweak for address list display on non white backgrounds
0.33.0 2011-08-26 Fixes for non queued emails, removed redundant email settings and added templating to Email generation and small group fix for directory
0.33.1 2011-09-02 Attendance tables
0.33.2.1 2011-09-04 Added missing files and ability to send email immediately
0.33.2.2 2011-10-26 MOved emailing cahcing out of plugin
0.33.2.3 2011-10-30 Attendance graph Shortcodes
0.33.2.4 2011-11-30 Fixed Salutation missing from 1st email sent instantly
0.33.2.5 2011-12-01 Added 5 years of year planners to cache
0.33.2.6 2011-12-05 Fixed calendar bugs in display and editing
0.33.2.7 2011-12-13 Calendar  display dropdown menu fix
0.33.2.8 2011-12-19 Calendar previous and next bug fix
0.33.2.9 2011-12-20 Calendar list format bug fix
0.33.3.0 2011-12-31 Jquery no conflict wrapper for email
0.33.3.1 2012-01-03 Fixed bug where add calendar event with same details wasn't saved
0.33.3.2 2012-01-06 Calendar Year planner added choices to main directory list & rota add job issue fixed
0.33.3.3 2012-01-06 UTF8 character set for DB tables
0.33.4.0 2012-01-23 PDFs created dynamically
0.33.4.3 2012-02-21 Clear out filesfrom svn repository
0.33.4.4 2012-02-26 Oops your rota would have been duplicated
0.33.4.5 2012-03-27 Rota gremlins fixed
0.4.0 2012-07-03 Rewrite of directory side
0.4.1 2012-07-03   Search front end and add services
0.4.2 2012-07-06 Added google map showing small group members [church_admin_map member_type_id=#]
*/
add_action('activated_plugin','save_error');
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}
//Version Number
define('OLD_CHURCH_ADMIN_VERSION',get_option('church_admin_version'));
$church_admin_version = '0.4.2';


//define DB
define('CA_HOU_TBL',$table_prefix.'church_admin_household');
define('CA_PEO_TBL',$table_prefix.'church_admin_people');
define('CA_SMG_TBL',$table_prefix.'church_admin_smallgroup');
define('CA_MET_TBL',$table_prefix.'church_admin_people_meta');
define('CA_ATT_TBL',$table_prefix.'church_admin_attendance');
define('CA_ROT_TBL',$table_prefix.'church_admin_rotas');
define('CA_SER_TBL',$table_prefix.'church_admin_services');
//define DB
//define paths
define('CHURCH_ADMIN_DISPLAY_PATH', WP_PLUGIN_DIR . '/church-admin/display/');
define('CHURCH_ADMIN_URL',WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
define('CHURCH_ADMIN_INCLUDE_PATH', WP_PLUGIN_DIR . '/church-admin/includes/');
define('CHURCH_ADMIN_INCLUDE_URL', CHURCH_ADMIN_URL.'includes/');
define('CHURCH_ADMIN_IMAGES_PATH', WP_PLUGIN_DIR . '/church-admin/images/');
define('CHURCH_ADMIN_IMAGES_URL', WP_PLUGIN_URL . '/church-admin/images/');
define('CHURCH_ADMIN_TEMP_PATH',WP_PLUGIN_DIR.'/church-admin/temp/');
define('CHURCH_ADMIN_EMAIL_CACHE',WP_PLUGIN_DIR.'/church-admin-cache/');
define('CHURCH_ADMIN_EMAIL_CACHE_URL',WP_PLUGIN_URL.'/church-admin-cache/');
if(!is_dir(CHURCH_ADMIN_EMAIL_CACHE))mkdir(CHURCH_ADMIN_EMAIL_CACHE,'755');
    $church_admin_people_settings=get_option('church_admin_people_settings');
    $people_type=$church_admin_people_settings['people_type'];
    $member_type=$church_admin_people_settings['member_type'];
    $days=array(1=>'Sunday',2=>'Monday',3=>'Tuesday',4=>'Wednesday',5=>'Thursday',6=>'Friday',7=>'Saturday');
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'functions.php');
    
add_filter('the_posts', 'church_admin_conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
function church_admin_conditionally_add_scripts_and_styles($posts){
	if (empty($posts)) return $posts;
 
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '[church_admin_map') !== false) {
			$shortcode_found = true; // bingo!
			break;
		}
	}
 
	if ($shortcode_found) {
		// enqueue here
		
		wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false');
                wp_enqueue_script('ca_google_map_script', CHURCH_ADMIN_INCLUDE_URL.'google_maps.js');
	}
 
	return $posts;
}

function church_admin_init()
{
    ca_thumbnails();
    if(isset($_GET['download'])){church_admin_download($_GET['download']);exit();}
    if ((isset($_GET['action'])&&($_GET['action']=='church_admin_send_email'||$_GET['action']=='church_admin_edit_category'||$_GET['action']=='church_admin_add_category'))||!is_admin())
    {
       //Only fire up jquery on the add and edit category pages within admin.php to avoid conflicts
        wp_enqueue_script('jquery');
    }
    //if (!session_id())session_start();
    if(isset($_GET['page']) && $_GET['page']=='church_admin_send_email')
    {
        wp_enqueue_script('jquery');
        wp_register_script('ca_email', CHURCH_ADMIN_INCLUDE_URL.'email.js', false, '1.0');
        wp_enqueue_script('ca_email');
    }
    if(isset($_GET['action']) && ($_GET['action']=='church_admin_edit_household'||$_GET['action']=='church_admin_edit_service'))
    {
        wp_enqueue_script('google_map','http://maps.google.com/maps/api/js?sensor=false');
        wp_enqueue_script('js_map',CHURCH_ADMIN_INCLUDE_URL.'maps.js');
        wp_enqueue_style('church_admin',CHURCH_ADMIN_INCLUDE_URL.'admin.css');
    }
    if(isset($_GET['action'])&& ($_GET['action']=='church_admin_edit_people'||$_GET['action']=='church_admin_edit_rota'||$_GET['action']=='church_admin_add_calendar'))
    {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery.ui.theme', plugins_url( '/css/jquery-ui-1.8.21.custom.css', __FILE__ ) );
    }
    if(isset($_GET['page']) &&$_GET['page']=='church_admin_add_attendance')
    {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery.ui.theme', plugins_url( '/css/jquery-ui-1.8.21.custom.css', __FILE__ ) );
    }
    if(isset($_GET['action']) &&$_GET['action']=='church_admin_add_category')
    {
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_style('farbtastic');	
    }
    
}

add_action('init', 'church_admin_init');
/* Thumbnails */
function ca_thumbnails()
{
    add_theme_support( 'post-thumbnails' );
    if ( function_exists( 'add_image_size' ) )
    { 
	add_image_size( 'ca-email-thumb', 300, 200 ); //300 pixels wide (and unlimited height)
	add_image_size('ca-120-thumb',120,90);
	add_image_size('ca-240-thumb',240,180);
    }
    
}
/* Thumbnails */
function church_admin_level_check($what)
{
    $level=get_option('church_admin_levels');
    return current_user_can($level[$what]);   
}

//check install is uptodate 
if (get_option("church_admin_version") != $church_admin_version ) 
{
    require(CHURCH_ADMIN_INCLUDE_PATH."install.php");
    church_admin_install();
}


//grab includes
require(CHURCH_ADMIN_INCLUDE_PATH.'header.inc.php');

if(isset($_GET['page'])&&$_GET['page']=='church_admin_main')require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_small_groups')require(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_visitor_list')require(CHURCH_ADMIN_INCLUDE_PATH.'visitor.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_settings')require(CHURCH_ADMIN_INCLUDE_PATH.'communication_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_email')require(CHURCH_ADMIN_INCLUDE_PATH.'email.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_sms')require(CHURCH_ADMIN_INCLUDE_PATH.'sms.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_add_attendance') require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_calendar')require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_service_list')require(CHURCH_ADMIN_INCLUDE_PATH.'services.php');
//Build Admin Menus
add_action('admin_menu', 'church_admin_menus');
function church_admin_menus() 

{
     $level=get_option('church_admin_levels');
    if(empty($level)){$level=array('Directory'=>'administrator','Small Groups'=>'administrator','Rota'=>'administrator','Bulk Email'=>'administrator','Bulk SMS'=>'administrator','Vistor'=>'administrator','Calendar'=>'administrator','Attendance'=>'administrator','Service'=>'administrator');update_option('church_admin_levels',$level);}
    add_menu_page('church_admin:Administration', 'Church Admin',  'administrator', 'church_admin/index.php', 'church_admin_main');
    add_submenu_page('church_admin/index.php', 'Directory List', 'Directory List', $level['Directory'], 'church_admin/index.php', 'church_admin_main');
    add_submenu_page('church_admin/index.php', 'Small Group List', 'Small Group List', $level['Small Groups'], 'church_admin_small_groups', 'church_admin_small_groups');
    add_submenu_page('church_admin/index.php', 'Rota List', 'Rota List', $level['Rota'], 'church_admin_rota_list', 'church_admin_rota_list');
    add_submenu_page('church_admin/index.php', 'Calendar', 'Calendar', $level['Calendar'], 'church_admin_calendar', 'church_admin_calendar');
    add_submenu_page('church_admin/index.php', 'Attendance', 'Attendance', $level['Attendance'], 'church_admin_add_attendance', 'church_admin_add_attendance');
    add_submenu_page('church_admin/index.php', 'Services', 'Services', $level['Service'], 'church_admin_service_list', 'church_admin_service_list');
    if(get_option('church_admin_sms_username'))add_submenu_page('church_admin/index.php', 'Send Bulk SMS', 'Send Bulk SMS', $level['Bulk SMS'], 'church_admin_send_sms', 'church_admin_send_sms');
    if( get_option('church_admin_cron')=='wp-cron'||file_exists(CHURCH_ADMIN_INCLUDE_PATH.'cronemail.php')) add_submenu_page('church_admin/index.php', 'Bulk Email', 'Send Bulk Email', $level['Bulk Email'], 'church_admin_send_email', 'church_admin_send_email');    
    add_submenu_page('church_admin/index.php', 'Settings', 'Settings', 'administrator', 'church_admin_settings', 'church_admin_settings');
}

 function church_admin_cron()
    {
        // Do something regularly.
        require(CHURCH_ADMIN_INCLUDE_PATH."cronemail.php");
    }
//end of cron stuff


//main admin page function


function church_admin_main() 
{
    global $wpdb,$church_admin_version;
    switch($_GET['action'])
    {
        //celendar
        case 'church_admin_add_category':check_admin_referer('add_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_category();}break;         
        case 'church_admin_edit_category':check_admin_referer('edit_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_edit_category($_GET['id']);}break;
        case 'church_admin_delete_category':check_admin_referer('delete_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_delete_category($_GET['id']);}break;
        case 'church_admin_single_event_delete':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_delete($_GET['date_id'],$_GET['event_id']); }break;
        case 'church_admin_series_event_delete':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event_delete($_GET['date_id'],$_GET['event_id']);}break;     
        case 'church_admin_category_list':if(church_admin_level_check('Calendar'));{require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_category_list();}break;    
        case 'church_admin_series_event_edit':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event($_GET['date_id'],$_GET['event_id']);}break;
        case 'church_admin_single_event_edit':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_edit($_GET['date_id'],$_GET['event_id']);}break;
        case 'church_admin_add_calendar':if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_calendar();}break;
        //address
        case 'church_admin_create_user':check_admin_referer('create_user');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_create_user($_GET['people_id'],$_GET['household_id']);}break;      
        case 'church_admin_migrate_users':check_admin_referer('migrate_users');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_migrate_users();}break;
        case 'church_admin_display_household':check_admin_referer('display_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_display_household($_GET['household_id']);}break;
        case 'church_admin_edit_household':check_admin_referer('edit_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_edit_household($_GET['household_id']);}break;
        case 'church_admin_delete_household':check_admin_referer('delete_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_delete_household($_GET['household_id']);}break;
        case 'church_admin_edit_people':check_admin_referer('edit_people');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_edit_people($_GET['people_id'],$_GET['household_id']);}break;
        case 'church_admin_delete_people':check_admin_referer('delete_people');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_delete_people($_GET['people_id'],$_GET['household_id']);}break;
        case 'church_admin_search':if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_search($_POST['ca_search']);}break;
       
        //rota
        case 'church_admin_rota_list':if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_rota_list($_REQUEST['service_id']);}break;
        case 'church_admin_rota_settings_list':if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_rota_settings_list();}break;
        case 'church_admin_add_rota_settings':check_admin_referer('add_rota_settings');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_add_rota_settings();}break;    
        case 'church_admin_edit_rota_settings':check_admin_referer('edit_rota_settings');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_edit_rota_settings($_GET['id']);}break;
        case 'church_admin_delete_rota_settings':check_admin_referer('delete_rota_settings');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_delete_rota_settings($_GET['id']);}break;
        case 'church_admin_edit_rota':check_admin_referer('edit_rota');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_edit_rota($_GET['id'],$_REQUEST['service_id']); }break;
        case 'church_admin_delete_rota':check_admin_referer('delete_rota');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_delete_rota($_GET['id']);}break;
        //visitor
        case 'church_admin_add_visitor':check_admin_referer('add_visitor');if(church_admin_level_check('Visitor')){require(CHURCH_ADMIN_INCLUDE_PATH.'visitor.php'); church_admin_add_visitor();} break;
        case 'church_admin_edit_visitor':check_admin_referer('edit_visitor');if(church_admin_level_check('Visitor')){church_admin_edit_visitor($_GET['id']);}break;
        case 'church_admin_delete_visitor':check_admin_referer('delete_visitor');if(church_admin_level_check('Visitor')){church_admin_delete_visitor($_GET['id']);} break;
        case 'church_admin_move_visitor':check_admin_referer('move_visitor');if(church_admin_level_check('Visitor')){church_admin_move_visitor($_GET['id']);}break;
        //small groups
        case  'church_admin_edit_small_group':check_admin_referer('edit_small_group');if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php');  church_admin_edit_small_group($_GET['id']);}break;
        case  'church_admin_delete_small_group':check_admin_referer('delete small group');if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php'); church_admin_delete_small_group($_GET['id']);}break;
        //services
        case  'church_admin_edit_service':check_admin_referer('edit_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php');  church_admin_edit_service($_GET['id']);}break;
        case  'church_admin_delete_service':check_admin_referer('delete_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php'); church_admin_delete_service($_GET['id']);}break;
    
        //setings
        case 'church_admin_settings':if(current_user_can('manage_options')){require(CHURCH_ADMIN_INCLUDE_PATH.'communication_settings.php');church_admin_settings();}break;    
        //default
        default:if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');if(isset($_POST['member_type_id'])){$m=$_POST['member_type_id'];}else{$m=1;}church_admin_address_list($m);}break;
       
    }
}

function church_admin_shortcode($atts, $content = null) 
	{
	
		extract(shortcode_atts(array("type" => 'address-list','category'=>NULL,'weeks'=>4,'member_type_id'=>1,'map'=>1), $atts));
    cd_posts_logout();
    global $wpdb;
    $wpdb->show_errors();
    global $wp_query;
    
    //look to see if church directory is o/p on a password protected page	
    $pageinfo=get_page($wp_query->post->ID);	
    //grab page info
    //check to see if on a password protected page
    if(($pageinfo->post_password!='')&&isset( $_COOKIE['wp-postpass_' . COOKIEHASH] )) 
    {
	$text = 'Log out of password protected posts';
	//text for link
	$link = get_bloginfo(url).'?cd_logout=posts_logout';
	$out.= '<p><a href="' . wp_nonce_url($link, 'posts logout') .'">' . $text . '</a></p>';
	//output logoutlink
    }
    //end of password protected page
   
    //grab content
    switch($type)
    {
       
        case 'calendar':
	    
	    $out.='<table><tr><td>Year Planner pdfs </td><td>  <form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- Choose a pdf --</option>';
	    for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		$out.='<option value="'.home_url().'/?download=yearplanner&amp;year='.$y.'">'.$y.' Year Planner</option>';
	    }
	    $out.='</select></form></td></tr></table>';
	    
            

            include(CHURCH_ADMIN_DISPLAY_PATH.'calendar.php');
            
        break;
        case 'calendar-list':
            include(CHURCH_ADMIN_DISPLAY_PATH.'calendar-list.php');
        break;
        case 'address-list':
	   
            $out.='<p><a href="'.home_url().'/?download=addresslist">PDF version</a></p>';
            require(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
            $out.=church_admin_frontend_directory($member_type_id,$map);
        break;
	case 'small-groups-list':
            require(CHURCH_ADMIN_DISPLAY_PATH."small-group-list.php");
            $out.= church_admin_small_group_list();
        break;
	case 'small-groups':
            $out.='<p><a href="'.home_url().'/?download=smallgroup">PDF version</a></p>';
            require(CHURCH_ADMIN_DISPLAY_PATH."small-groups.php");
            $out.=church_admin_frontend_small_groups();
        break;
	case 'rota':
            include(CHURCH_ADMIN_DISPLAY_PATH."rota.php");
            $out.=church_admin_front_end_rota();
        break;
	case 'monthly-average':
	    if(file_exists(CHURCH_ADMIN_CACHE_PATH.'attendance-graph.png'))$out.='<img src="'.CHURCH_ADMIN_CACHE_URL.'attendance-graph.png" alt="Average Attendance Graph"/>';
        break;
	case 'rolling-average':
	    if(file_exists(CHURCH_ADMIN_CACHE_PATH.'rolling_average_attendance.png'))$out.='<img src="'.CHURCH_ADMIN_CACHE_URL.'rolling_average_attendance.png" alt="Average Attendance Graph"/>';
        break;
	default:
            require(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
        break;
    }
//output content instead of shortcode!
return $out; 
}
add_shortcode("church_admin", "church_admin_shortcode");
add_shortcode("church_admin_map","church_admin_map");

function church_admin_map($atts, $content = null) 
{
    extract(shortcode_atts(array('zoom'=>13,'member_type_id'=>1), $atts));
    global $wpdb;
    $service=$wpdb->get_row('SELECT lat,lng FROM '.CA_SER_TBL.' LIMIT 1');
    $out.='<script type="text/javascript">var xml_url="'.site_url().'?download=address-xml&member_type_id='.$member_type_id.'";';
    $out.=' var lat='.$service->lat.';';
    $out.=' var lng='.$service->lng.';';
    $out.='jQuery(document).ready(function(){
    load(lat,lng);});</script><div id="map" ></div><div id="groups"></div>';
    
    
    return $out;
    
}

function cd_posts_logout() 
{
    if ( isset( $_GET['cd_logout'] ) && ( 'posts_logout' == $_GET['cd_logout'] ) &&check_admin_referer( 'posts logout' )) 
    {
	setcookie( 'wp-postpass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH );
	wp_redirect( wp_get_referer() );
	die();
    }
}


add_action( 'init', 'cd_posts_logout' );

//end of logout functions

function church_admin_calendar_widget($args)
{
    global $wpdb;
    $wpdb->show_errors();
    extract($args);
    $options=get_option('church_admin_widget');
    $title=$options['title'];
   
    echo $before_widget;
    if ( $title )echo $before_title . $title . $after_title;
   
    echo church_admin_calendar_widget_output($options['events'],$options['postit'],$title);
    echo $after_widget;
}
function church_admin_widget_init()
{
    wp_register_sidebar_widget('Church Admin Calendar','Church Admin Calendar','church_admin_calendar_widget');
    require(CHURCH_ADMIN_INCLUDE_PATH.'calendar_widget.php');
    wp_register_widget_control('Church Admin Calendar','Church Admin Calendar','church_admin_widget_control');
}
add_action('init','church_admin_widget_init');
function church_admin_download($file)
{
    switch($file)
    {   case 'address-xml':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_xml($_GET['member_type_id']);break;
        case'cron-instructions':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_cron_pdf();break;
	case'rota':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_rota_pdf();break;
        case'yearplanner':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_year_planner_pdf($_GET['year']);break;
	case'smallgroup':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_smallgroup_pdf();break;
	case'addresslist':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_pdf($_GET['member_type_id']);break;
	case'vcf':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');ca_vcard($_GET['id']);break;
	case'mailinglabel':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_label_pdf($_GET['member_type_id']);break;
    }
}
?>