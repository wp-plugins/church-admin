<?php

/*

Plugin Name: church_admin
Plugin URI: http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin
Description: A church admin system with address book, small groups, rotas, bulk email  and sms
Version: 0.5900
Author: Andy Moyle
Text Domain: church-admin


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
//Version Number
define('OLD_CHURCH_ADMIN_VERSION',get_option('church_admin_version'));
$church_admin_version = '0.5900';
church_admin_constants();//setup constants first
if(OLD_CHURCH_ADMIN_VERSION!= $church_admin_version)
{
	church_admin_backup();
	require_once(CHURCH_ADMIN_INCLUDE_PATH.'install.php');
	church_admin_install();
}
require_once(CHURCH_ADMIN_INCLUDE_PATH.'admin.php');
require_once(CHURCH_ADMIN_INCLUDE_PATH.'functions.php');
add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

add_action('load-church-admin', 'church_admin_add_screen_meta_boxes');

// add localisation

function ca_myplugin_init() {
  load_plugin_textdomain( 'church-admin', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' ); 
}
add_action('init', 'ca_myplugin_init');

//end add localisation

//update_option('church_admin_roles',array(2=>'Elder',1=>'Small group Leader'));
$oldroles=get_option('church_admin_roles');
if(!empty($oldroles))
{
    update_option('church_admin_departments',$oldroles);
    delete_option('church_admin_roles');
}


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
define ('CA_SERM_TBL',$wpdb->prefix.'church_admin_sermon_series');

define ('CA_FIL_TBL',$wpdb->prefix.'church_admin_sermon_files');
define ('CA_BIB_TBL',$wpdb->prefix.'church_admin_bible_books');
//define DB
//define paths
define('CHURCH_ADMIN_DISPLAY_PATH', WP_PLUGIN_DIR . '/church-admin/display/');
define('CHURCH_ADMIN_URL',WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
define('CHURCH_ADMIN_INCLUDE_PATH', WP_PLUGIN_DIR . '/church-admin/includes/');
define('CHURCH_ADMIN_INCLUDE_URL', CHURCH_ADMIN_URL.'includes/');
define('CHURCH_ADMIN_IMAGES_PATH', WP_PLUGIN_DIR . '/church-admin/images/');
define('CHURCH_ADMIN_IMAGES_URL', WP_PLUGIN_URL . '/church-admin/images/');
define('CHURCH_ADMIN_TEMP_PATH',WP_PLUGIN_DIR.'/church-admin/temp/');
define('OLD_CHURCH_ADMIN_EMAIL_CACHE',WP_PLUGIN_DIR.'/church-admin-cache/');
define('OLD_CHURCH_ADMIN_EMAIL_CACHE_URL',WP_PLUGIN_URL.'/church-admin-cache/');
define('CHURCH_ADMIN_EMAIL_CACHE',WP_CONTENT_DIR.'/uploads/church-admin-cache/');
define('CHURCH_ADMIN_EMAIL_CACHE_URL',WP_CONTENT_URL.'/uploads/church-admin-cache/');
define('CA_POD_URL',WP_CONTENT_URL.'/uploads/sermons/');
define('CA_POD_PTH',WP_CONTENT_DIR.'/uploads/sermons/');
if(!is_dir(CA_POD_PTH))
    {
        $old = umask(0);
        mkdir(CA_POD_PTH);
        chmod(CA_POD_PTH, 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen(CA_POD_PTH.'index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
    }
if(!is_dir(CHURCH_ADMIN_EMAIL_CACHE))
{
        $old = umask(0);
        mkdir(CHURCH_ADMIN_EMAIL_CACHE);
        chmod(CHURCH_ADMIN_EMAIL_CACHE, 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen(CHURCH_ADMIN_EMAIL_CACHE.'index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
}
if(is_dir(OLD_CHURCH_ADMIN_EMAIL_CACHE))
{
    
    //grab files
    $files=scandir(OLD_CHURCH_ADMIN_EMAIL_CACHE);
    if(!empty($files))
    {
	foreach($files AS $file)
	{
	    if ($file!= "." && $file!= "..")
	    {
	        //work through files, but don't delete as old emails have link to old uris
	        $success=copy(OLD_CHURCH_ADMIN_EMAIL_CACHE.$file,CHURCH_ADMIN_EMAIL_CACHE.$file);
	        if($success)
	        {
	        	
	        	unlink(OLD_CHURCH_ADMIN_EMAIL_CACHE.$file);
	        }
	    }
	}
	//create htaccess redirect for cached emails
    
	$htaccess="\r\n RedirectPermanent /wp-content/plugins/church-admin-cache/ /wp-content/uploads/church-admin-cache/\r\n";
	// Let's make sure the file exists and is writable first.
	$htaccess_done=get_option('church_admin_htaccess');
	if (is_writable(ABSPATH.'.htaccess')&&empty($htaccess_done))
	{
    
	    if (!$handle = fopen(ABSPATH.'.htaccess', 'a')) {echo "Cannot open file (ABSPATH.'.htaccess')";}
	    elseif(fwrite($handle, $htaccess) === FALSE) {echo "Cannot write to file (".ABSPATH.".htaccess)";}
	    else{fclose($handle);}
	    update_option('church_admin_htaccess','1');
	} 
    }
    
}
    
}//end constants
   
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
    return $rota_order;
    }
    
}
$rota_order=ca_rota_order();
    $people_type=get_option('church_admin_people_type');
    $member_type=church_admin_member_type_array();
    $departments=get_option('church_admin_departments');
    $level=get_option('church_admin_levels');
    if(empty($level['Directory']))$level['Directory']='administrator';
    if(empty($level['Small Groups']))$level['Small Groups']='administrator';
    if(empty($level['Rota']))$level['Rota']='administrator';
    if(empty($level['Funnel'])) $level['Funnel']='administrator';
    if(empty($level['Bulk Email']))$level['Bulk Email']='administrator';
    if(empty($level['Sermons']))$level['Sermons']='administrator';
	if(empty($level['Bulk SMS']))$level['Bulk SMS']='administrator';
    if(empty($level['Calendar']))$level['Calendar']='administrator';
    if(empty($level['Attendance']))$level['Attendance']='administrator';
    if(empty($level['Member Type']))$level['Member Type']='administrator';
    if(empty($level['Service']))$level['Service']='administrator';
    update_option('church_admin_levels',$level);
    
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
		if(stripos($post->post_content,'type=podcast')!== false ||stripos($post->post_content,'type="podcast"')!== false ||stripos($post->post_content,"type='podcast'")!== false )$shortcode_found='podcast';
		if (stripos($post->post_content, '[church_admin_map') !== false )$shortcode_found='map';
		if (stripos($post->post_content, 'type=small-groups-list') !== false ||stripos($post->post_content, 'type="small-groups-list"') !== false )$shortcode_found='map';
                if(stripos($post->post_content, '[church_admin_register') !== false ) $shortcode_found = 'register';
	}
 
	if ($shortcode_found) {
		// enqueue here
		if($shortcode_found=='podcast')
		{
			$ajax_nonce = wp_create_nonce("church_admin_mp3_play");			
			
		    wp_enqueue_script('ca_podcast_audio',CHURCH_ADMIN_INCLUDE_URL.'audio.min.js');
		    wp_enqueue_script('ca_podcast_audio_use',CHURCH_ADMIN_INCLUDE_URL.'audio.use.js');
			wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}
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
wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');

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
    if(isset($_GET['action']) && ($_GET['action']=='church_admin_send_email'||$_GET['action']=='church_admin_send_sms'))
    {
        wp_enqueue_script('jquery');
        wp_register_script('ca_email', CHURCH_ADMIN_INCLUDE_URL.'email.js', false, '1.0');
        wp_enqueue_script('ca_email');
    }
    if(!empty($_GET['action']) && ($_GET['action']=='church_admin_edit_household'||$_GET['action']=='church_admin_edit_service'||$_GET['action']=='church_admin_edit_small_group'))
    {
        wp_enqueue_script('google_map','http://maps.google.com/maps/api/js?sensor=false');
        wp_enqueue_script('js_map',CHURCH_ADMIN_INCLUDE_URL.'maps.js');
        
    }
    if(isset($_GET['action'])&& ($_GET['action']=='church_admin_edit_people'||$_GET['action']=='church_admin_add_calendar'||$_GET['action']=='church_admin_series_event_edit'||$_GET['action']=='church_admin_single_event_edit'||$_GET['action']=='church_admin_edit_attendance'))
    {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery.ui.theme',WP_PLUGIN_URL . '/church-admin/css/jquery-ui-1.8.21.custom.css' );
    }
    if(isset($_GET['page']) &&$_GET['page']=='church_admin_add_attendance')
    {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery.ui.theme', WP_PLUGIN_URL . '/church-admin/css/jquery-ui-1.8.21.custom.css' );
    }
    if(isset($_GET['action']) &&$_GET['action']=='church_admin_add_category')
    {
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_style('farbtastic');	
    }
    if(isset($_GET['action'])&&($_GET['action']=='church_admin_member_type'||$_GET['action']=='church_admin_rota_settings_list'||$_GET['action']=='church_admin_edit_rota_settings'))
    {
        wp_enqueue_script( 'jquery-ui-sortable' );
    }
    if(isset($_GET['action'])&& ($_GET['action']=='permissions'||$_GET['action']=='edit_file'||$_GET['action']=='file_add'||$_GET['action']=='church_admin_edit_rota'))
    {//autocomplete scripts
        wp_enqueue_script( 'jquery-ui-datepicker' ); 
        wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_style( 'jquery.ui.theme', WP_PLUGIN_URL . '/church-admin/css/jquery-ui-1.8.21.custom.css' );
    }
    if(isset($_GET['page'])&& $_GET['page']=='church_admin_permissions')
    {//autocomplete scripts
        wp_enqueue_script( 'jquery-ui-datepicker' ); 
        wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_style( 'jquery.ui.theme', WP_PLUGIN_URL . '/church-admin/css/jquery-ui-1.8.21.custom.css' );
    }
	if(isset($_GET['action'])&&$_GET['action']=='church_admin_view_department')
	{
		wp_enqueue_script('jquery-ui-autocomplete');
	}
    if(isset($_GET['action'])&&$_GET['action']=='church_admin_update_order')
    {
         
        church_admin_update_order($_GET['which']);
        exit();
    }
    if(isset($_GET['action'])&& $_GET['action']=='get_people')
    {
	church_admin_ajax_people();
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
        add_image_size('ca-people-thumb',75,75);
	add_image_size( 'ca-email-thumb', 300, 200 ); //300 pixels wide (and unlimited height)
	add_image_size('ca-120-thumb',120,90);
	add_image_size('ca-240-thumb',240,180);
    }
    
}
/* Thumbnails */





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
if(isset($_GET['page'])&&$_GET['page']=='church_admin_permissions')require(CHURCH_ADMIN_INCLUDE_PATH.'permissions.php');
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
    $user_permissions=get_option('church_admin_user_permissions');
    //let plugin decide level of showing admin menu
    if(!empty($user_permissions)){$level='read';}else{$level='manage_options';}
    add_menu_page('church_admin:Administration', __('Church Admin','church-admin'),  $level, 'church_admin/index.php', 'church_admin_main');
    add_submenu_page('church_admin/index.php', __('Permissions','church-admin'), 'Permissions', 'manage_options', 'church_admin_permissions', 'church_admin_permissions');
    add_submenu_page('church_admin/index.php', __('Settings','church-admin'), 'Settings', 'manage_options', 'church_admin_settings', 'church_admin_settings');

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
	$wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'church_admin_permissions', 'title' => __('Permissions','church-admin'), 'href' => admin_url().'admin.php?page=church_admin_permissions' ));
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
    $id=!empty($_GET['id'])?$_GET['id']:NULL;
    $date_id=!empty($_GET['date_id'])?$_GET['date_id']:NULL;
    $event_id=!empty($_GET['event_id'])?$_GET['event_id']:NULL;
    $people_id=!empty($_GET['people_id'])?$_GET['people_id']:NULL;
    $household_id=!empty($_GET['household_id'])?$_GET['household_id']:NULL;
    $service_id=!empty($_REQUEST['service_id'])?$_REQUEST['service_id']:1;
    $attendance_id=!empty($_GET['attendance_id'])?$_GET['attendance_id']:NULL;
    $department_id=!empty($_GET['department_id'])?$_GET['department_id']:NULL;
    $funnel_id=!empty($_GET['funnel_id'])?$_GET['funnel_id']:NULL;
    $people_type_id=!empty($_GET['people_type_id'])?$_GET['people_type_id']:NULL;
    $member_type_id=!empty($_REQUEST['member_type_id'])?$_REQUEST['member_type_id']:NULL;
    
    $file=!empty($_GET['file'])?$_GET['file']:NULL;
    if(!empty($_REQUEST['church_admin_search'])){if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_search($_REQUEST['church_admin_search']);}}
	elseif(isset($_GET['action']))
    {
	switch($_GET['action'])
	{
		//mailchimp
		case'mailchimp_sync':if(church_admin_level_check('Directory')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'mailchimp.php');church_admin_mailchimp_sync();}break;
		//premissions
		case'permissions':require_once(CHURCH_ADMIN_INCLUDE_PATH.'permissions.php');church_admin_permissions();break;
	//backups
	    case'refresh_backup':check_admin_referer('refresh_backup');church_admin_backup();church_admin_front_admin();break;
	    case'delete_backup':check_admin_referer('delete_backup');church_admin_delete_backup();church_admin_front_admin();break;
	//sermon podcasts
	    case'list_speakers':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_list_speakers();break;
            case'edit_speaker':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_edit_speaker($id);break;
            case'delete_speaker':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_delete_speaker($id);break;
            case'list_sermon_series':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_list_series();break;
            case'edit_series':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_edit_series($id);break;
            case'delete_series':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_delete_series($id);break;
            case'list_files':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_list_files();break;
            case'edit_file':check_admin_referer('edit_podcast_file');require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_edit_file($id);break;
            case'delete_file':check_admin_referer('delete_podcast_file');require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_delete_file($id);break;
            case'file_delete':check_admin_referer('file_delete');require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_file_delete($file);break;
            case'file_add':check_admin_referer('file_add');require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_file_add($file);break;
            case'check_files':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');ca_podcast_check_files();break;
            case'podcast':require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');if(ca_podcast_xml()){echo'<p>Podcast <a href="'.CA_POD_URL.'podcast.xml">feed</a> updated</p>';}break;
            case'podcast_settings':check_admin_referer('podcast_settings');require_once(CHURCH_ADMIN_INCLUDE_PATH.'podcast-settings.php');ca_podcast_settings();break;
            
	    case 'church_admin_send_sms':if(church_admin_level_check('Bulk SMS')){require(CHURCH_ADMIN_INCLUDE_PATH.'sms.php');church_admin_send_sms();}break;
	    
	    case 'church_admin_send_email':if(church_admin_level_check('Bulk Email')){require(CHURCH_ADMIN_INCLUDE_PATH.'email.php');church_admin_send_email();}break;
	    case'church_admin_people_activity':if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php'); echo church_admin_recent_people_activity();}break;
	    //attendance
	    case 'church_admin_attendance_metrics':require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_attendance_metrics($service_id);break;   
	    case 'church_admin_attendance_list':require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_attendance_list($service_id);break;    
	    case 'church_admin_edit_attendance':check_admin_referer('edit_attendance');require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_edit_attendance($attendance);break;         
	    case 'church_admin_delete_attendance':check_admin_referer('delete_attendance');require(CHURCH_ADMIN_INCLUDE_PATH.'attendance.php');church_admin_delete_attendance($attendance_id);break;         
	   
	    //departments
	    case 'church_admin_edit_department':check_admin_referer('edit_department');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_edit_department($department_id);break;         
	    case 'church_admin_delete_department':check_admin_referer('delete_department');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_delete_department($department_id);break;         
	    case 'church_admin_department_list':check_admin_referer('department_list');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_department_list();break;         
       case 'church_admin_view_department':check_admin_referer('view_department');require(CHURCH_ADMIN_INCLUDE_PATH.'departments.php');church_admin_view_department($department_id);break;
	    //funnel
	    case 'church_admin_funnel_list':require(CHURCH_ADMIN_INCLUDE_PATH.'funnel.php');church_admin_funnel_list();break;         
	    case 'church_admin_edit_funnel':check_admin_referer('edit_funnel');require(CHURCH_ADMIN_INCLUDE_PATH.'funnel.php');church_admin_edit_funnel($funnel_id,$people_type_id);break;
	    case 'church_admin_assign_funnel':require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');church_admin_assign_funnel();break;
	    case 'church_admin_email_follow_up_activity':check_admin_referer('email_funnels');require(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');church_admin_email_follow_up_activity();break;
	    //member_type
	         case 'church_admin_member_type':require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_member_type();break;         
	    case 'church_admin_edit_member_type':check_admin_referer('edit_member_type');require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_edit_member_type($member_type_id);break;         
	    case 'church_admin_delete_member_type':check_admin_referer('delete_member_type');require(CHURCH_ADMIN_INCLUDE_PATH.'member_type.php');church_admin_delete_member_type($member_type_id);break;         
	   
	    //celendar
	    case 'church_admin_calendar_list':if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_calendar();}break;         
	    
	    case 'church_admin_add_category':check_admin_referer('add_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_category();}break;         
	    case 'church_admin_edit_category':check_admin_referer('edit_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_edit_category($id);}break;
	    case 'church_admin_delete_category':check_admin_referer('delete_category');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_delete_category($id);}break;
	    case 'church_admin_single_event_delete':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_delete($date_id,$event_id); }break;
	    case 'church_admin_series_event_delete':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event_delete($date_id,$event_id);}break;     
	    case 'church_admin_category_list':if(church_admin_level_check('Calendar'));{require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_category_list();}break;    
	    case 'church_admin_series_event_edit':check_admin_referer('series_event_edit');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_series_event_edit($date_id,$event_id);}break;
	    case 'church_admin_single_event_edit':check_admin_referer('single_event_edit');if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_single_event_edit($date_id,$event_id);}break;
	    case 'church_admin_add_calendar':if(church_admin_level_check('Calendar')){require(CHURCH_ADMIN_INCLUDE_PATH.'calendar.php');church_admin_add_calendar();}break;
	    //address
	    case 'church_admin_move_person':if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_move_person($people_id);}break;
	    case 'church_admin_address_list': if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_address_list($member_type_id);}else{echo"<p>You don't have permission to do that";}break;
	    case 'church_admin_create_user':check_admin_referer('create_user');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_create_user($people_id,$household_id);}break;      
	    case 'church_admin_migrate_users':check_admin_referer('migrate_users');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_migrate_users();}break;
	    case 'church_admin_display_household':check_admin_referer('display_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_display_household($household_id);}break;
	    case 'church_admin_edit_household':check_admin_referer('edit_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_edit_household($household_id);}break;
	    case 'church_admin_delete_household':check_admin_referer('delete_household');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_delete_household($household_id);}break;
	    case 'church_admin_edit_people':check_admin_referer('edit_people');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_edit_people($people_id,$household_id);}break;
	    case 'church_admin_delete_people':check_admin_referer('delete_people');if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_delete_people($people_id,$household_id);}break;
	    case 'church_admin_search':if(church_admin_level_check('Directory')){require(CHURCH_ADMIN_INCLUDE_PATH.'directory.php');church_admin_search($_POST['ca_search']);}break;
	   
	    //rota
	    case 'church_admin_email_rota':if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_email_rota($service_id);}break;
	    case 'church_admin_rota_list':if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_rota_list($service_id);}break;
	    case 'church_admin_rota_settings_list':if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_rota_settings_list();}break;
	    case 'church_admin_edit_rota_settings':check_admin_referer('edit_rota_settings');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_edit_rota_settings($id);}break;
	    case 'church_admin_delete_rota_settings':check_admin_referer('delete_rota_settings');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota_settings.php');church_admin_delete_rota_settings($id);}break;
	    case 'church_admin_edit_rota':check_admin_referer('edit_rota');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_edit_rota($id,$service_id); }break;
	    case 'church_admin_delete_rota':check_admin_referer('delete_rota');if(church_admin_level_check('Rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'rota.php');church_admin_delete_rota($id);}break;
	    //visitor
	    case 'church_admin_add_visitor':check_admin_referer('add_visitor');if(church_admin_level_check('Visitor')){require(CHURCH_ADMIN_INCLUDE_PATH.'visitor.php'); church_admin_add_visitor();} break;
	    case 'church_admin_edit_visitor':check_admin_referer('edit_visitor');if(church_admin_level_check('Visitor')){church_admin_edit_visitor($id);}break;
	    case 'church_admin_delete_visitor':check_admin_referer('delete_visitor');if(church_admin_level_check('Visitor')){church_admin_delete_visitor($id);} break;
	    case 'church_admin_move_visitor':check_admin_referer('move_visitor');if(church_admin_level_check('Visitor')){church_admin_move_visitor($id);}break;
	    //small groups
	    case  'church_admin_edit_small_group':check_admin_referer('edit_small_group');if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php');  church_admin_edit_small_group($id);}break;
	    case  'church_admin_delete_small_group':check_admin_referer('delete_small_group');if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php'); church_admin_delete_small_group($id);}break;
	    case 'church_admin_small_groups':if(church_admin_level_check('Small Groups')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'small_groups.php'); church_admin_small_groups();}break;
	    //services
	    case  'church_admin_edit_service':check_admin_referer('edit_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php');  church_admin_edit_service($id);}break;
	    case  'church_admin_delete_service':check_admin_referer('delete_service');if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php'); church_admin_delete_service($id);}break;
	    case 'church_admin_service_list':if(church_admin_level_check('Service')){require_once(CHURCH_ADMIN_INCLUDE_PATH.'services.php'); church_admin_service_list();}break;
	    
	    //setings
	    case 'church_admin_settings':if(current_user_can('manage_options')){require(CHURCH_ADMIN_INCLUDE_PATH.'communication_settings.php');church_admin_settings();}break;    
	    //default
	    default:church_admin_front_admin();break;
	   
	}
    }
    else {church_admin_front_admin();}
}

function church_admin_shortcode($atts, $content = null) 
{
	
    extract(shortcode_atts(array("type" => 'address-list','days'=>30,'year'=>date('Y'),'service_id'=>1,'photo'=>NULL,'category'=>NULL,'weeks'=>4,'member_type_id'=>1,'map'=>NULL,'series_id'=>NULL,'speaker_id'=>NULL,'file_id'=>NULL), $atts));
    church_admin_posts_logout();
    $out='';
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
	$link = get_bloginfo('site_url').'?church_admin_logout=posts_logout';
	$out.= '<p><a href="' . wp_nonce_url($link, 'posts logout') .'">' . $text . '</a></p>';
	//output logoutlink
    }
    //end of password protected page
   
    //grab content
    switch($type)
    {
	case 'podcast':
	    require_once(CHURCH_ADMIN_DISPLAY_PATH.'sermon-podcast.php');
	    $out.=ca_podcast_display($series_id,$speaker_id,$file_id);
		
		
	break;    
        case 'calendar':
	    
	    $out.='<table><tr><td>'.__('Year Planner pdfs','church-admin').' </td><td>  <form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.__('Choose a pdf','church-admin').' --</option>';
	    for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		
		$out.='<option value="'.home_url().'/?download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.__('Year Planner','church-admin').'</option>';
	    }
	    $out.='</select></form></td></tr></table>';
	    
            

            require_once(CHURCH_ADMIN_DISPLAY_PATH.'calendar.php');
            
        break;
        case 'calendar-list':
            require_once(CHURCH_ADMIN_DISPLAY_PATH.'calendar-list.php');
        break;
        case 'address-list':
	   
            $out.='<p><a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('addresslist' ).'&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            require_once(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
            $out.=church_admin_frontend_directory($member_type_id,$map,$photo);
        break;
        
		case 'small-groups-list':
            require_once(CHURCH_ADMIN_DISPLAY_PATH."small-group-list.php");
            $out.= church_admin_small_group_list($map);
        break;
		case 'small-groups':
            require_once(CHURCH_ADMIN_DISPLAY_PATH."small-groups.php");
            $out.=church_admin_frontend_small_groups($member_type_id);
            
        break;
		case 'rota':
            require_once(CHURCH_ADMIN_DISPLAY_PATH."rota.php");
            $out.=church_admin_front_end_rota();
        break;
        case 'rolling-average-attendance':
		
			if(!file_exists(CHURCH_ADMIN_EMAIL_CACHE.'rolling-average-attendance.png'))
			{
				require(CHURCH_ADMIN_INCLUDE_PATH.'graph.php');
				church_admin_rolling_attendance_graph($service_id);
			}
			$out.='<img src="'.CHURCH_ADMIN_EMAIL_CACHE_URL.'rolling-average-attendance.png" alt="'.__('Rolling Average Attendance Graph','church-admin').'" width="700" height="500"/>';
        break;

        case 'monthly-attendance':
		
			if(!file_exists(CHURCH_ADMIN_EMAIL_CACHE.'monthly-attendance'.$year.'.png'))
			{
				require(CHURCH_ADMIN_INCLUDE_PATH.'graph.php');
				church_admin_monthly_attendance_graph($year,$service_id);
			}
			$out.='<img src="'.CHURCH_ADMIN_EMAIL_CACHE_URL.'monthly-attendance'.$year.'.png" alt="'.__('Monthly Attendance Graph','church-admin').'" width="700" height="500"/>';
        break;
		case 'weekly-attendance':
		
			if(!file_exists(CHURCH_ADMIN_EMAIL_CACHE.'weekly-attendance'.$year.'.png'))
			{
				require(CHURCH_ADMIN_INCLUDE_PATH.'graph.php');
				church_admin_weekly_attendance_graph($year,$service_id);
			}
			$out.='<img src="'.CHURCH_ADMIN_EMAIL_CACHE_URL.'weekly-attendance'.$year.'.png" alt="'.__('Weekly Attendance Graph','church-admin').'" width="700" height="500"/>';
        break;
		case 'rolling-average':
			if(file_exists(CHURCH_ADMIN_CACHE_PATH.'rolling_average_attendance.png'))$out.='<img src="'.CHURCH_ADMIN_CACHE_URL.'rolling_average_attendance.png" alt="'.__('Average Attendance Graph','church-admin').'"/>';
        break;
		case 'birthdays':require_once(CHURCH_ADMIN_INCLUDE_PATH.'birthdays.php');$out.=church_admin_frontend_birthdays($member_type_id, $days);break;
	default:
            require_once(CHURCH_ADMIN_DISPLAY_PATH."address-list.php");
            $out.=church_admin_frontend_directory($member_type_id,$map,$photo);
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
    extract(shortcode_atts(array('zoom'=>13,'member_type_id'=>1,'small_group'=>1), $atts));
    global $wpdb;
    $service=$wpdb->get_row('SELECT lat,lng FROM '.CA_SER_TBL.' LIMIT 1');
    $out.='<script type="text/javascript">var xml_url="'.site_url().'/?download=address-xml&amp;address-xml='.wp_create_nonce('address-xml').'&amp;&member_type_id='.$member_type_id.'&small_group='.$small_group.'";';
    $out.=' var lat='.$service->lat.';';
    $out.=' var lng='.$service->lng.';';
    
    $out.='jQuery(document).ready(function(){
    load(lat,lng,xml_url);});</script><div id="map"></div>';
    if(empty($small_group)){$out.='<div id="groups" style="display:none"></div>';}else{$out.='<div id="groups" ></div>';}
    
    
    return $out;
    
}
add_shortcode("church_admin_register","church_admin_register");
function church_admin_register($atts, $content = null)
{
    extract(shortcode_atts(array('email_verify'=>TRUE,'admin_email'=>TRUE,'member_type_id'=>1), $atts));
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'front_end_register.php');
    $out=church_admin_front_end_register();
    return $out;
}

function church_admin_posts_logout() 
{
    if ( isset( $_GET['church_admin_logout'] ) && ( 'posts_logout' == $_GET['church_admin_logout'] ) &&check_admin_referer( 'posts logout' )) 
    {
	setcookie( 'wp-postpass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH );
	wp_redirect( wp_get_referer() );
	die();
    }
}


add_action( 'init', 'church_admin_posts_logout' );

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

function church_admin_birthday_widget($args)
{
    global $wpdb;
    $wpdb->show_errors();
    extract($args);
	$options=get_option('church_admin_birthday_widget');
	
    $title=$options['title'];
	if(empty($options['member_type_id']))$options['member_type_id']=1;
	if(empty($options['days']))$options['days']=14;
	$out=church_admin_frontend_birthdays($options['member_type_id'], $options['days']);
   if(!empty($out))
   {
		echo $before_widget;
		if (!empty( $options['title']) )echo $before_title . $options['title'] . $after_title;
		require_once(CHURCH_ADMIN_INCLUDE_PATH.'birthdays.php');
		echo $out;
		echo $after_widget;
	}
}
function church_admin_birthday_widget_init()
{
    wp_register_sidebar_widget('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget');
    require(CHURCH_ADMIN_INCLUDE_PATH.'birthdays.php');
    wp_register_widget_control('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget_control');
}
add_action('init','church_admin_birthday_widget_init');

function church_admin_download($file)
{
    switch($file)
    {
		case 'people-csv':if(wp_verify_nonce($_GET['people-csv'],'people-csv')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_people_csv($_GET['member_type_id'],$_GET['people_type_id'],$_GET['sex'],$_GET['address'],$_GET['small_group']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case 'small-group-xml':if(wp_verify_nonce($_GET['small-group-xml'],'small-group-xml')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_small_group_xml();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case 'address-xml':if(wp_verify_nonce($_GET['address-xml'],'address-xml')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_xml($_GET['member_type_id'],$_GET['small_group']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
        case'cron-instructions':if(wp_verify_nonce($_GET['cron-instructions'],'cron-instructions')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_cron_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'rota':if(wp_verify_nonce($_GET['rota'],'rota')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_rota_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
        case'yearplanner':if(wp_verify_nonce($_GET['yearplanner'],'yearplanner')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_year_planner_pdf($_GET['year']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'smallgroup':if(wp_verify_nonce($_GET['smallgroup'],'smallgroup')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_smallgroup_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'addresslist':if(wp_verify_nonce($_GET['addresslist'],'addresslist')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_address_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'vcf':if(wp_verify_nonce($_GET['vcf'],'vcf')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');ca_vcard($_GET['id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'mailinglabel':if(wp_verify_nonce($_GET['mailinglabel'],'mailinglabel')){require(CHURCH_ADMIN_INCLUDE_PATH.'pdf_creator.php');church_admin_label_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
        case 'rotacsv':if(wp_verify_nonce($_GET['rotacsv'],'rotacsv')){require(CHURCH_ADMIN_INCLUDE_PATH."rota.php");church_admin_rota_csv($_GET['service_id']); }else{echo'<p>You can only download if coming from a valid link</p>';}break;
    }
}
function church_admin_delete_backup(){if(file_exists(CHURCH_ADMIN_EMAIL_CACHE.'Church_Admin_Backup.sql.gz'))unlink(CHURCH_ADMIN_EMAIL_CACHE.'Church_Admin_Backup.sql.gz');}
function church_admin_backup()
{
    global $church_admin_version,$wpdb;
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ATT_TBL.'"') == CA_ATT_TBL)$content=church_admin_datadump (CA_ATT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_BIB_TBL.'"') == CA_BIB_TBL)$content.=church_admin_datadump (CA_BIB_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_CAT_TBL.'"') == CA_CAT_TBL)$content.=church_admin_datadump (CA_CAT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FIL_TBL.'"') == CA_FIL_TBL)$content.=church_admin_datadump (CA_FIL_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FP_TBL.'"') == CA_FP_TBL)$content.=church_admin_datadump (CA_FP_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FUN_TBL.'"') == CA_FUN_TBL)$content.=church_admin_datadump (CA_FUN_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOU_TBL.'"') == CA_HOU_TBL)$content.=church_admin_datadump (CA_HOU_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MET_TBL.'"') == CA_MET_TBL)$content.=church_admin_datadump (CA_MET_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MTY_TBL.'"') == CA_MTY_TBL)$content.=church_admin_datadump (CA_MTY_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_PEO_TBL.'"') == CA_PEO_TBL)$content.=church_admin_datadump (CA_PEO_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ROT_TBL.'"') == CA_ROT_TBL)$content.=church_admin_datadump (CA_ROT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_RST_TBL.'"') == CA_RST_TBL)$content.=church_admin_datadump (CA_RST_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SERM_TBL.'"') == CA_SERM_TBL)$content.=church_admin_datadump (CA_SERM_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SER_TBL.'"') == CA_SER_TBL)$content.=church_admin_datadump (CA_SER_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SMG_TBL.'"') == CA_SMG_TBL)$content.=church_admin_datadump (CA_SMG_TBL);
    $content.='UPDATE '.$wpdb->prefix.'options SET option_value="'.OLD_CHURCH_ADMIN_VERSION.'" WHERE option_mame="church_admin_version";'."\r\n";

    if(!empty($content))
    {
		$gzdata = gzencode($content);
		$file_name = 'Church_Admin_Backup.sql.gz';
		$fp = fopen(CHURCH_ADMIN_EMAIL_CACHE.$file_name, 'w');
		fwrite($fp, $gzdata);
		fclose($fp);
	}
}
function church_admin_datadump ($table) {

	global $wpdb;
	$wpdb->show_errors();
	$sql="select * from `$table`";
	$tablequery = $wpdb->get_results($sql,ARRAY_N);
	$num_fields=$wpdb->num_rows +1;
	
	if(!empty($tablequery))
	{
	    
	    $result = "# Dump of $table \r\n";
	    $result .= "# Dump DATE : " . date("d-M-Y") ."\r\n";
	    
	    $increment = $num_fields+1;
	    //build table structure
	    $sql = "SHOW COLUMNS FROM `$table`";
	    $query=$wpdb->get_results($sql);
	    if(!empty($query))
	    {
		$result.="DROP TABLE IF EXISTS `$table`;\r\n CREATE TABLE IF NOT EXISTS `$table` (";
		foreach($query AS $row)
		{
		    $result.="`{$row->Field}` {$row->Type} ";
		    if(isset($row->NULL)){$result.=" NULL ";}else {$result.=" NOT NULL ";}
		    if($row->Key=='PRI'){$key=$row->Field;}
		    if(!empty($row->Default))
		    {
			if($row->Default=='CURRENT_TIMESTAMP'){$result.='default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP';}
			else {$result.=" default '".$row->Default."'";}
		    }
		    if(!empty($row->Extra)) $result.=' '.$row->Extra;
		    $result.=',';
		}
	    }
	    $result.="PRIMARY KEY (`{$key}`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=".$increment." ;\r\n";
	    $result.="-- \r\n -- Dumping data for table `$table`\r\n--\r\n";
	    //build insert for table
	    $result.="-- \r\n -- Dumping data for table `$table`\r\n--\r\n";
	
	    foreach($tablequery AS $row)
	    {
 
		$result .= "INSERT INTO `".$table."` VALUES(";
		for($j=0; $j<count($row); $j++) 
		{
		    $row[$j] = addslashes($row[$j]);
		    $row[$j] = str_replace("\n","\\n",$row[$j]);
		    if (isset($row[$j])) $result .= "'{$row[$j]}'" ; else $result .= "''";
		    if ($j<(count($row)-1)) $result .= ",";
		}   
		$result .= ");\r\n";
	    }
	    	return $result;
	}
}
 



add_action('wp_ajax_ca_mp3_action', 'church_admin_action_callback');
add_action('wp_ajax_nopriv_ca_mp3_action', 'church_admin_action_callback');

function church_admin_action_callback() {
	$nonce = $_POST['data']['security'];
 	if ( ! wp_verify_nonce( $nonce, 'church_admin_mp3_play' ) )die('busted');

	global $wpdb;
	$file_id = esc_sql($_POST['data']['file_id']);
	$sql='UPDATE '.CA_FIL_TBL.' SET plays = plays+1 WHERE file_id = "'.$file_id.'"';
	$wpdb->query($sql);
	$plays=$wpdb->get_var('SELECT plays FROM '.CA_FIL_TBL.' WHERE file_id = "'.$file_id.'"');
	
	echo $plays;
	die();
} 
  
?>