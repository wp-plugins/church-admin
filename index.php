<?php

/*

Plugin Name: church_admin
Plugin URI: http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin
Description: A church admin system with address book, small groups, rotas, bulk email  and sms
Version: 0.4.74
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


*/
add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
add_action('activated_plugin','save_error');
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}

//add localisation
$my_translator_domain   = 'church-admin';
$my_translator_is_setup = 0;
function ca_loc_setup(){
  global $my_translator_domain, $my_translator_is_setup;
  if($my_translator_is_setup) {
    return;
  }
  load_plugin_textdomain( 'church-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  $my_translator_is_setup = 1;
}
add_action('plugins_loaded', 'ca_loc_setup');
//end add localisation
//Version Number
define('OLD_CHURCH_ADMIN_VERSION',get_option('church_admin_version'));
$church_admin_version = '0.4.73';
//update_option('church_admin_roles',array(2=>'Elder',1=>'Small group Leader'));
$oldroles=get_option('church_admin_roles');
if(!empty($oldroles))
{
    update_option('church_admin_departments',$oldroles);
    delete_option('church_admin_roles');
}

church_admin_constants();
function church_admin_constants()
{
        /**
 *
 * Sets up constants for plugin
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    global $wpdb;
//define DB
define('CA_HOU_TBL',$wpdb->prefix.'church_admin_household');
define('CA_PEO_TBL',$wpdb->prefix.'church_admin_people');
define('CA_SMG_TBL',$wpdb->prefix.'church_admin_smallgroup');
define('CA_MET_TBL',$wpdb->prefix.'church_admin_people_meta');
define('CA_ATT_TBL',$wpdb->prefix.'church_admin_attendance');
define('CA_ROT_TBL',$wpdb->prefix.'church_admin_rotas');
define('CA_RST_TBL',$wpdb->prefix.'church_admin_rota_settings');
define('CA_SER_TBL',$wpdb->prefix.'church_admin_services');
define('CA_FUN_TBL',$wpdb->prefix.'church_admin_funnels');
define('CA_FP_TBL',$wpdb->prefix.'church_admin_follow_up');
define('CA_MTY_TBL',$wpdb->prefix.'church_admin_member_types');
define ('CA_CAT_TBL',$wpdb->prefix.'church_admin_calendar_category');
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
require_once(CHURCH_ADMIN_INCLUDE_PATH.'functions.php');
}
//check install is uptodate 
if (get_option("church_admin_version") != $church_admin_version ) 
{
    require(CHURCH_ADMIN_INCLUDE_PATH."install.php");
    church_admin_install();
}    
function ca_rota_order()
{
 /**
 *
 * Retrieves rota items in order
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   Array, key is order
 * @version  0.1
 * 
 */ 
    global $wpdb;
    //rota_order
    $results=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order ASC');
    if($results)
    {
        $rota_order=array();
        foreach($results AS $row)
        {
            $rota_order[]=$row->rota_id;
        }
    }
    return $rota_order;
}
$rota_order=ca_rota_order();
    $people_type=get_option('church_admin_people_type');
    $member_type=church_admin_member_type_array();
    $departments=get_option('church_admin_departments');
    $level=get_option('church_admin_levels');
    if(empty($level)||count($level)<10){$level=array(__('Directory','church-admin')=>'administrator',__('Small Groups','church-admin')=>'administrator',__('Rota','church-admin')=>'administrator',__('Funnel','church-admin')=>'administrator',__('Bulk Email','church-admin')=>'administrator',__('Bulk SMS','church-admin')=>'administrator','Vistor'=>'administrator','Calendar'=>'administrator','Attendance'=>'administrator','Service'=>'administrator');update_option('church_admin_levels',$level);}
    
    $days=array(1=>__('Sunday','church-admin'),2=>__('Monday','church-admin'),3=>__('Tuesday','church-admin'),4=>__('Wednesday','church-admin'),5=>__('Thursday','church-admin'),6=>__('Friday','church-admin'),7=>__('Saturday','church-admin'));
    
    
add_filter('the_posts', 'church_admin_conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
function church_admin_conditionally_add_scripts_and_styles($posts){
    /**
 *
 * Add scripts and styles depending on shortcode in post/page, called using filter
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
	if (empty($posts)) return $posts;
 
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '[church_admin_map') !== false )$shortcode_found='map';
                if(stripos($post->post_content, '[church_admin_register') !== false ) $shortcode_found = 'register';
	}
 
	if ($shortcode_found) {
		// enqueue here
		if($shortcode_found=='register')
                {
                    //form field clone script and css                
                    wp_enqueue_script('form-clone',CHURCH_ADMIN_INCLUDE_URL.'jquery-formfields.js');
                    wp_enqueue_style('church_admin',CHURCH_ADMIN_INCLUDE_URL.'admin.css');
                    if(!isset($_POST['save']))
                    {//ad mapping scripts if still form page!
                        wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false');
                        wp_enqueue_script('ca_google_map_script', CHURCH_ADMIN_INCLUDE_URL.'maps.js');
                    }
                }
                else
                {
                    wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false');
                        wp_enqueue_script('ca_google_map_script', CHURCH_ADMIN_INCLUDE_URL.'google_maps.js');
                }
	}
 
	return $posts;
}

function church_admin_init()
{
        /**
 *
 * Initialises js scripts and css
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    //This function add scripts as needed
    ca_thumbnails();
    wp_enqueue_style('church_admin',CHURCH_ADMIN_INCLUDE_URL.'admin.css');
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
    if(isset($_GET['action']) && $_GET['action']=='church_admin_send_email')
    {
        wp_enqueue_script('jquery');
        wp_register_script('ca_email', CHURCH_ADMIN_INCLUDE_URL.'email.js', false, '1.0');
        wp_enqueue_script('ca_email');
    }
    if(isset($_GET['action']) && ($_GET['action']=='church_admin_edit_household'||$_GET['action']=='church_admin_edit_service'))
    {
        wp_enqueue_script('google_map','http://maps.google.com/maps/api/js?sensor=false');
        wp_enqueue_script('js_map',CHURCH_ADMIN_INCLUDE_URL.'maps.js');
        
    }
    if(isset($_GET['action'])&& ($_GET['action']=='church_admin_edit_people'||$_GET['action']=='church_admin_edit_rota'||$_GET['action']=='church_admin_add_calendar'||$_GET['action']=='church_admin_edit_attendance'))
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
    if(isset($_GET['action'])&&($_GET['action']=='church_admin_member_type'||$_GET['action']=='church_admin_rota_settings_list'||$_GET['action']=='church_admin_add_rota_settings'))
    {
        wp_enqueue_script( 'jquery-ui-sortable' );
    }
    if(isset($_GET['action'])&&$_GET['action']=='church_admin_update_order')
    {
         
        church_admin_update_order($_GET['which']);
        exit();
    }
}

add_action('init', 'church_admin_init');
/* Thumbnails */
function ca_thumbnails()
{
        /**
 *
 * Add thumbnails for plugin use
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
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
    global $current_user;
    $level=get_option('church_admin_levels');
    // print_r($level);
    if($level[$what]=="administrator"){return current_user_can('manage_options');}
    elseif($level[$what]=="editor"){return current_user_can('delete_others_pages');}
    elseif($level[$what]=="author"){return current_user_can('publish_posts');}
    elseif($level[$what]=="contributor"){return current_user_can('edit_posts');}
    elseif($level[$what]=="subscriber"){return current_user_can('read');}
    else{ return false;}
}




//grab includes
require(CHURCH_ADMIN_INCLUDE_PATH.'header.inc.php');

if(isset($_GET['page'])&&$_GET['page']=='church_admin_main')require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_address_list'){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');}
if(isset($_GET['page'])&&$_GET['page']=='church_admin_small_groups')require(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_visitor_list')require(CHURCH_ADMIN_INCLUDE_PATH.'visitor.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_settings')require(CHURCH_ADMIN_INCLUDE_PATH.'communication_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_email')require(CHURCH_ADMIN_INCLUDE_PATH.'email.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_sms')require(CHURCH_ADMIN_INCLUDE_PATH.'sms.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_attendance_metrics') require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_calendar')require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_service_list')require(CHURCH_ADMIN_INCLUDE_PATH.'services.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_funnel_list'){require(CHURCH_ADMIN_INCLUDE_PATH.'funnel.php');}
if(isset($_GET['page'])&&$_GET['page']=='church_admin_member_type')require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_department_list')require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');
//Build Admin Menus
add_action('admin_menu', 'church_admin_menus');
function church_admin_menus() 

{
/**
 *
 * Admin menu
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    global $level;
    add_menu_page('church_admin:Administration', __('Church Admin','church-admin'),  'administrator', 'church_admin/index.php', 'church_admin_main');
/*    add_submenu_page('church_admin/index.php', 'Directory List', 'Directory List', $level['Directory'], 'church_admin_address_list', 'church_admin_address_list');
    add_submenu_page('church_admin/index.php', 'Follow Up Funnels', 'Follow Up Funnels', $level['Funnel'], 'church_admin_funnel_list', 'church_admin_funnel_list');
    add_submenu_page('church_admin/index.php', 'Church Departments', 'Church Departments', 'administrator', 'church_admin_department_list', 'church_admin_department_list');
    add_submenu_page('church_admin/index.php', 'Small Group List', 'Small Group List', $level['Small Groups'], 'church_admin_small_groups', 'church_admin_small_groups');
    add_submenu_page('church_admin/index.php', 'Rota List', 'Rota List', $level['Rota'], 'church_admin_rota_list', 'church_admin_rota_list');
    add_submenu_page('church_admin/index.php', 'Calendar', 'Calendar', $level['Calendar'], 'church_admin_calendar', 'church_admin_calendar');
    add_submenu_page('church_admin/index.php', 'Attendance', 'Attendance', $level['Attendance'], 'church_admin_attendance_metrics', 'church_admin_attendance_metrics');
    add_submenu_page('church_admin/index.php', 'Services', 'Services', $level['Service'], 'church_admin_service_list', 'church_admin_service_list');
    if(get_option('church_admin_sms_username'))add_submenu_page('church_admin/index.php', 'Send Bulk SMS', 'Send Bulk SMS', $level['Bulk SMS'], 'church_admin_send_sms', 'church_admin_send_sms');
    if( get_option('church_admin_cron')=='wp-cron'||file_exists(CHURCH_ADMIN_INCLUDE_PATH.'cronemail.php')) add_submenu_page('church_admin/index.php', 'Bulk Email', 'Send Bulk Email', $level['Bulk Email'], 'church_admin_send_email', 'church_admin_send_email');    
    add_submenu_page('church_admin/index.php', 'Member Types', 'Member Types', 'administrator', 'church_admin_member_type', 'church_admin_member_type');
*/
    add_submenu_page('church_admin/index.php', __('Settings','church-admin'), 'Settings', 'administrator', 'church_admin_settings', 'church_admin_settings');

}

// Admin Bar Customisation
function mytheme_admin_bar_render() {
/**
 *
 * Admin Bar Menu
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
 global $wp_admin_bar;
 // Add a new top level menu link
 // Here we add a customer support URL link
 $wp_admin_bar->add_menu( array('parent' => false, 'id' => 'church_admin', 'title' => __('Church Admin','church-admin'), 'href' => admin_url().'admin.php?page=church_admin/index.php' ));
 $wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'church_admin_settings', 'title' => __('Settings','church-admin'), 'href' => admin_url().'admin.php?page=church_admin/index.php&action=church_admin_settings' ));
 $wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'plugin_support', 'title' => __('Plugin Support','church-admin'), 'href' => 'http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin/plugin-support' ));
}

// Finally we add our hook function
add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );



 function church_admin_cron()
    {
/**
 *
 * Calls wp cron
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */
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
        case 'church_admin_send_sms':if(church_admin_level_check('Bulk SMS')){require(CHURCH_ADMIN_INCLUDE_PATH.'sms.php');church_admin_send_sms();}break;
        
        case 'church_admin_send_email':if(church_admin_level_check('Bulk Email')){require(CHURCH_ADMIN_INCLUDE_PATH.'email.php');church_admin_send_email();}break;
        case'church_admin_people_activity':if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php'); echo church_admin_recent_people_activity();}break;
        //attendance
        case 'church_admin_attendance_metrics':require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_attendance_metrics($_GET['service_id']);break;   
        case 'church_admin_attendance_list':require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_attendance_list($_GET['service_id']);break;    
        case 'church_admin_edit_attendance':check_admin_referer('edit_attendance');require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_edit_attendance($_GET['attendance_id']);break;         
        case 'church_admin_delete_attendance':check_admin_referer('delete_attendance');require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_delete_attendance($_GET['attendance_id']);break;         
       
        //departments
        case 'church_admin_edit_department':check_admin_referer('edit_department');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_edit_department($_GET['department_id']);break;         
        case 'church_admin_delete_department':check_admin_referer('delete_department');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_delete_department($_GET['department_id']);break;         
        case 'church_admin_department_list':check_admin_referer('department_list');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_department_list();break;         
       
        //funnel
        case 'church_admin_funnel_list':require(CHURCH_ADMIN_INCLUDE_PATH.'funnel.php');church_admin_funnel_list();break;         
        case 'church_admin_edit_funnel':check_admin_referer('edit_funnel');require(CHURCH_ADMIN_INCLUDE_PATH.'funnel.php');church_admin_edit_funnel($_GET['funnel_id'],$_GET['people_type_id']);break;
        case 'church_admin_assign_funnel':require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');church_admin_assign_funnel();
        case 'church_admin_email_follow_up_activity':check_admin_referer('email_funnels');require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');church_admin_email_follow_up_activity();break;
        //member_type
         case 'church_admin_member_type':require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_member_type();break;         
        case 'church_admin_edit_member_type':check_admin_referer('edit_member_type');require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_edit_member_type($_GET['member_type_id']);break;         
        case 'church_admin_delete_member_type':check_admin_referer('delete_member_type');require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_delete_member_type($_GET['member_type_id']);break;         
       
        //celendar
        case 'church_admin_calendar_list':if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_calendar();}break;         
        
        case 'church_admin_add_category':check_admin_referer('add_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_category();}break;         
        case 'church_admin_edit_category':check_admin_referer('edit_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_edit_category($_GET['id']);}break;
        case 'church_admin_delete_category':check_admin_referer('delete_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_delete_category($_GET['id']);}break;
        case 'church_admin_single_event_delete':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_delete($_GET['date_id'],$_GET['event_id']); }break;
        case 'church_admin_series_event_delete':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event_delete($_GET['date_id'],$_GET['event_id']);}break;     
        case 'church_admin_category_list':if(church_admin_level_check('Calendar'));{require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_category_list();}break;    
        case 'church_admin_series_event_edit':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event($_GET['date_id'],$_GET['event_id']);}break;
        case 'church_admin_single_event_edit':check_admin_referer('single_event_edit');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_edit($_GET['date_id'],$_GET['event_id']);}break;
        case 'church_admin_add_calendar':if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_calendar();}break;
        //address
        case 'church_admin_address_list': if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_address_list($_REQUEST['member_type_id']);}else{echo"<p>You don't have permission to do that";}break;
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
        case  'church_admin_delete_small_group':check_admin_referer('delete_small_group');if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php'); church_admin_delete_small_group($_GET['id']);}break;
        case 'church_admin_small_groups':if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php'); church_admin_small_groups();}break;
        //services
        case  'church_admin_edit_service':check_admin_referer('edit_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php');  church_admin_edit_service($_GET['id']);}break;
        case  'church_admin_delete_service':check_admin_referer('delete_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php'); church_admin_delete_service($_GET['id']);}break;
        case 'church_admin_service_list':if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php'); church_admin_service_list();}break;
        
        //setings
        case 'church_admin_settings':if(current_user_can('manage_options')){require(CHURCH_ADMIN_INCLUDE_PATH.'communication_settings.php');church_admin_settings();}break;    
        //default
        default:require(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');church_admin_front_admin();break;
       
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
	$text = __('Log out of password protected posts','church-admin');
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
	    
	    $out.='<table><tr><td>'.__('Year Planner pdfs','church-admin').' </td><td>  <form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.__('Choose a pdf','church-admin').' --</option>';
	    for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		$out.='<option value="'.home_url().'/?download=yearplanner&amp;year='.$y.'">'.$y.__('Year Planner','church-admin').'</option>';
	    }
	    $out.='</select></form></td></tr></table>';
	    
            

            include(CHURCH_ADMIN_DISPLAY_PATH.'calendar.php');
            
        break;
        case 'calendar-list':
            include(CHURCH_ADMIN_DISPLAY_PATH.'calendar-list.php');
        break;
        case 'address-list':
	   
            $out.='<p><a href="'.home_url().'/?download=addresslist&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            require(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
            $out.=church_admin_frontend_directory($member_type_id,$map);
        break;
	case 'small-groups-list':
            require(CHURCH_ADMIN_DISPLAY_PATH."small-group-list.php");
            $out.= church_admin_small_group_list();
        break;
	case 'small-groups':
            $out.='<p><a href="'.home_url().'/?download=smallgroup&amp;member_type='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            require(CHURCH_ADMIN_DISPLAY_PATH."small-groups.php");
            $out.=church_admin_frontend_small_groups($member_type_id);
        break;
	case 'rota':
            include(CHURCH_ADMIN_DISPLAY_PATH."rota.php");
            $out.=church_admin_front_end_rota();
        break;
        
	case 'monthly-average':
	    if(file_exists(CHURCH_ADMIN_CACHE_PATH.'attendance-graph.png'))$out.='<img src="'.CHURCH_ADMIN_CACHE_URL.'attendance-graph.png" alt="'.__('Average Attendance Graph','church-admin').'"/>';
        break;
	case 'rolling-average':
	    if(file_exists(CHURCH_ADMIN_CACHE_PATH.'rolling_average_attendance.png'))$out.='<img src="'.CHURCH_ADMIN_CACHE_URL.'rolling_average_attendance.png" alt="'.__('Average Attendance Graph','church-admin').'"/>';
        break;
	default:
            require(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
        break;
    }
//output content instead of shortcode!
return $out; 
}
add_shortcode('church_admin_recent','church_admin_recent');
function church_admin_recent()
{
    extract(shortcode_atts(array('month'=>1), $atts));
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'recent.php');church_admin_recent_display($month);
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
    load(lat,lng);});</script><div id="map"></div><div id="groups"></div>';
    
    
    return $out;
    
}
add_shortcode("church_admin_register","church_admin_register");
function church_admin_register()
{
    extract(shortcode_atts(array('email_verify'=>TRUE,'admin_email'=>TRUE), $atts));
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'front_end_register.php');
    $out=church_admin_front_end_register();
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
//add_action('init','church_admin_widget_init');
function church_admin_download($file)
{
    switch($file)
    {   case 'address-xml':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_xml($_GET['member_type_id']);break;
        case'cron-instructions':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_cron_pdf();break;
	case'rota':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_rota_pdf();break;
        case'yearplanner':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_year_planner_pdf($_GET['year']);break;
	case'smallgroup':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_smallgroup_pdf($_GET['member_type_id']);break;
	case'addresslist':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_pdf($_GET['member_type_id']);break;
	case'vcf':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');ca_vcard($_GET['id']);break;
	case'mailinglabel':require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_label_pdf($_GET['member_type_id']);break;
        case 'rotacsv':require(CHURCH_ADMIN_INCLUDE_PATH."rota.php");church_admin_rota_csv($_GET['service_id']); break;
    }
}
?>