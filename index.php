<?php

/*

Plugin Name: church_admin
Plugin URI: http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin
Description: A  admin system with address book, small groups, rotas, bulk email  and sms
Version: 0.722
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
$church_admin_version = '0.722';
church_admin_constants();//setup constants first
require_once(plugin_dir_path(__FILE__).'includes/admin.php');
require_once(plugin_dir_path(__FILE__) .'includes/functions.php');
if(OLD_CHURCH_ADMIN_VERSION!= $church_admin_version)
{
	church_admin_backup();
	require_once(plugin_dir_path( __FILE__) .'/includes/install.php');
	church_admin_install();
}

add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
add_action('activated_plugin','church_admin_save_error');
function church_admin_save_error(){
    update_option('church_admin_plugin_error',  ob_get_contents());
}
add_action('load-church-admin', 'church_admin_add_screen_meta_boxes');

// add localisation

function ca_myplugin_init() {
  load_plugin_textdomain( 'church-admin', false, plugin_dir_path( __FILE__) . 'languages/' ); 
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
define('CA_HOP_TBL',$wpdb->prefix.'church_admin_hope_team');
define('CA_PEO_TBL',$wpdb->prefix.'church_admin_people');
define('CA_SMG_TBL',$wpdb->prefix.'church_admin_smallgroup');
define('CA_MET_TBL',$wpdb->prefix.'church_admin_people_meta');
define('CA_ATT_TBL',$wpdb->prefix.'church_admin_attendance');
define('CA_IND_TBL',$wpdb->prefix.'church_admin_individual_attendance');
define('CA_ROT_TBL',$wpdb->prefix.'church_admin_rotas');
define('CA_RST_TBL',$wpdb->prefix.'church_admin_rota_settings');
define('CA_SER_TBL',$wpdb->prefix.'church_admin_services');
define('CA_FUN_TBL',$wpdb->prefix.'church_admin_funnels');
define('CA_FP_TBL',$wpdb->prefix.'church_admin_follow_up');
define('CA_MTY_TBL',$wpdb->prefix.'church_admin_member_types');
define ('CA_CAT_TBL',$wpdb->prefix.'church_admin_calendar_category');
define ('CA_SERM_TBL',$wpdb->prefix.'church_admin_sermon_series');
define('CA_EVE_TBL',$wpdb->prefix.'church_admin_calendar_event');
define('CA_DATE_TBL',$wpdb->prefix.'church_admin_calendar_date');
define ('CA_FIL_TBL',$wpdb->prefix.'church_admin_sermon_files');
define ('CA_BIB_TBL',$wpdb->prefix.'church_admin_bible_books');
define ('CA_FAC_TBL',$wpdb->prefix.'church_admin_facilities');
define('CA_KID_TBL',$wpdb->prefix.'church_admin_kidswork');
//define DB



define('OLD_CHURCH_ADMIN_EMAIL_CACHE',WP_PLUGIN_DIR.'/church-admin-cache/');
define('OLD_CHURCH_ADMIN_EMAIL_CACHE_URL',WP_PLUGIN_URL.'/church-admin-cache/');


define('CA_POD_URL',content_url().'/uploads/sermons/');
$upload_dir = wp_upload_dir();
if(!is_dir( $upload_dir['basedir'].'/sermons/'))
    {
        $old = umask(0);
        mkdir( $upload_dir['basedir'].'/sermons/');
        chmod($upload_dir['basedir'].'/sermons/', 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen($upload_dir['basedir'].'/sermons/'.'index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
    }
if(!is_dir($upload_dir['basedir'].'/church-admin-cache/'))
{
        $old = umask(0);
		 mkdir($upload_dir['basedir'].'/church-admin-cache/');
        chmod($upload_dir['basedir'].'/church-admin-cache/', 0755);
        umask($old); 
        $index="<?php\r\n//nothing is good;\r\n?>";
        $fp = fopen($upload_dir['basedir'].'/church-admin-cache/'.'index.php', 'w');
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
	        $success=copy(OLD_CHURCH_ADMIN_EMAIL_CACHE.$file,plugin_dir_path( dirname(__FILE__)).'church-admin-cache/'.$file);
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
	if(empty($level['Prayer Chain']))$level['Prayer Chain']='administrator';
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
		if (stripos($post->post_content, 'type=small-groups-list') !== false ||stripos($post->post_content, 'type="small-groups-list"') !== false )$shortcode_found='sgmap';
                if(stripos($post->post_content, '[church_admin_register') !== false ) $shortcode_found = 'register';
	}
 
	if ($shortcode_found) {
		// enqueue here
		if($shortcode_found=='podcast')
		{
			$ajax_nonce = wp_create_nonce("church_admin_mp3_play");			
			
		    wp_enqueue_script('ca_podcast_audio',plugins_url('church-admin/includes/audio.min.js',dirname(__FILE__) ) ,'',NULL);
		    wp_enqueue_script('ca_podcast_audio_use',plugins_url('church-admin/includes/audio.use.js',dirname(__FILE__) ),'',NULL,TRUE);
			wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			
		}
		if($shortcode_found=='register')
                {
                    //form field clone script and css                
                    wp_enqueue_script('form-clone',plugins_url('church-admin/includes/jquery-formfields.js',dirname(__FILE__) ),'',NULL);
                    
                    if(!isset($_POST['save']))
                    {//ad mapping scripts if still form page!
                        wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false','',NULL);
                        wp_enqueue_script('ca_google_map_script', plugins_url('church-admin/includes/maps.js',dirname(__FILE__) ),'',NULL);
                    }
                }
                elseif ($shortcode_found=='map')
                {
                    wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false','',NULL);
                    wp_enqueue_script('ca_google_map_script', plugins_url('church-admin/includes/google_maps.js',dirname(__FILE__) ),'',NULL);
                }
				elseif($shortcode_found=='sgmap')
				{
					wp_enqueue_script('google_map_script', 'http://maps.googleapis.com/maps/api/js?sensor=false','',NULL);
                    wp_enqueue_script('ca_google_map_script', plugins_url('church-admin/includes/smallgroup_maps.js',dirname(__FILE__) ) ,'',NULL);
				}
	}
 
	return $posts;
}

add_action('wp_head','church_admin_ajaxurl');
function church_admin_ajaxurl() {
$ajax_nonce = wp_create_nonce("church_admin_mp3_play");	
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var security= '<?php echo $ajax_nonce; ?>';
</script>
<?php
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
		wp_enqueue_script('common','','',NULL);
		wp_enqueue_script('wp-lists','','',NULL);
		wp_enqueue_script('postbox','','',NULL);

    ca_thumbnails();

	if(isset($_GET['ca_app'])){require_once(plugin_dir_path(__FILE__).'includes/json.php');church_admin_json($_GET['ca_app']);exit();}
    if(isset($_GET['download'])){church_admin_download($_GET['download']);exit();}
    if (isset($_GET['action'])&&($_GET['action']=='church_admin_send_email'||$_GET['action']=='church_admin_edit_category'||$_GET['action']=='church_admin_add_category'||$_GET['action']=='church_admin_new_calendar')||!is_admin())
    {
       //Only fire up jquery on the add and edit category pages within admin.php to avoid conflicts
        wp_enqueue_script('jquery','','',NULL);
    }
    //if (!session_id())session_start();
    if(isset($_GET['page']) && $_GET['page']=='church_admin_send_email')
    {
        wp_enqueue_script('jquery');
        wp_register_script('ca_email', plugins_url('church-admin/includes/email.js',dirname(__FILE__) ) , false, '1.0');
        wp_enqueue_script('ca_email','','',NULL);
    }
    if(isset($_GET['action']) && ($_GET['action']=='church_admin_send_email'||$_GET['action']=='church_admin_send_sms'))
    {
        wp_enqueue_script('jquery','','',NULL);
        wp_register_script('ca_email',  plugins_url('church-admin/includes/email.js',dirname(__FILE__) ), false, NULL);
        wp_enqueue_script('ca_email','','',NULL);
    }
	if(isset($_GET['action']) && ($_GET['action']=='church_admin_rota_list'||$_GET['action']=='church_admin_edit_rota'))
    {
        
        wp_register_script('ca_editable',  plugins_url('church-admin/includes/jquery.jeditable.mini.js',dirname(__FILE__) ), array('jquery'), NULL,TRUE);
        wp_enqueue_script('ca_editable');
    }
    if(!empty($_GET['action']) && ($_GET['action']=='church_admin_edit_household'||$_GET['action']=='church_admin_edit_service'||$_GET['action']=='church_admin_edit_small_group'))
    {
        wp_enqueue_script('google_map','http://maps.google.com/maps/api/js?sensor=false','',NULL);
        wp_enqueue_script('js_map', plugins_url('church-admin/includes/maps.js',dirname(__FILE__) ),'',NULL);
        
    }
    if(isset($_GET['action'])&& ($_GET['action']=='church_admin_edit_people'||$_GET['action']=='church_admin_add_calendar'||$_GET['action']=='church_admin_series_event_edit'||$_GET['action']=='church_admin_single_event_edit'||$_GET['action']=='church_admin_edit_attendance'||$_GET['action']=='church_admin_new_edit_calendar'||$_GET['action']=='edit_kidswork'))
    {
        wp_enqueue_script( 'jquery-ui-datepicker','','',NULL );
        wp_enqueue_style( 'jquery.ui.theme',plugins_url('css/jquery-ui-1.8.21.custom.css',__FILE__) ,'',NULL );
    }
    if(isset($_GET['page']) &&$_GET['page']=='church_admin_add_attendance')
    {
        wp_enqueue_script( 'jquery-ui-datepicker' ,'','',NULL);
        wp_enqueue_style( 'jquery.ui.theme', plugins_url('css/jquery-ui-1.8.21.custom.css',__FILE__ ),'',NULL );
    }
    if(isset($_GET['action']) && ($_GET['action']=='church_admin_add_category'||$_GET['action']=='church_admin_edit_category'))
    {
        wp_enqueue_script( 'farbtastic' ,'','',NULL);
        wp_enqueue_style('farbtastic','','',NULL);	
    }
    if(isset($_GET['action'])&&($_GET['action']=='church_admin_member_type'||$_GET['action']=='church_admin_rota_settings_list'||$_GET['action']=='church_admin_edit_rota_settings'))
    {
        wp_enqueue_script( 'jquery-ui-sortable' ,'','',NULL);
    }
    if(isset($_GET['action'])&& ($_GET['action']=='edit_hope_team'||$_GET['action']=='permissions'||$_GET['action']=='edit_file'||$_GET['action']=='file_add'||$_GET['action']=='church_admin_edit_rota'))
    {//autocomplete scripts
        wp_enqueue_script( 'jquery-ui-datepicker','','',NULL ); 
        wp_enqueue_script('jquery-ui-autocomplete','','',NULL);
		wp_enqueue_style( 'jquery.ui.theme', plugins_url('css/jquery-ui-1.8.21.custom.css',__FILE__ ),'',NULL );
    }
    if(isset($_GET['page'])&& $_GET['page']=='church_admin_permissions')
    {//autocomplete scripts
        wp_enqueue_script( 'jquery-ui-datepicker','','',NULL ); 
        wp_enqueue_script('jquery-ui-autocomplete','','',NULL);
		wp_enqueue_style( 'jquery.ui.theme', plugins_url('css/jquery-ui-1.8.21.custom.css',__FILE__ ) ,'',NULL);
    }
	if(isset($_GET['action'])&&$_GET['action']=='church_admin_view_department')
	{
		wp_enqueue_script('jquery-ui-autocomplete','','',NULL);
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
add_action( 'admin_enqueue_scripts','church_admin_public_css');
add_action('wp_enqueue_scripts','church_admin_public_css');
function church_admin_public_css(){wp_enqueue_style('Church-Admin',plugins_url('church-admin/includes/style.css',dirname(__FILE__) ),'',NULL);}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{
    global $church_admin_version;
     
    echo'<!-- church_admin v'.$church_admin_version.'-->
    <style>table.church_admin_calendar{width:';
    if(get_option('church_admin_calendar_width')){echo get_option('church_admin_calendar_width').'px}</style>';}else {echo'700px}</style>';}
    
}


//grab includes

if(isset($_GET['page'])&&$_GET['page']=='church_admin_prayer_chain')require_once(plugin_dir_path(__FILE__).'includes/prayer_chain.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_main')require_once(plugin_dir_path(__FILE__).'includes/directory.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_address_list'){require_once(plugin_dir_path(__FILE__).'includes/directory.php');}
if(isset($_GET['page'])&&$_GET['page']=='church_admin_small_groups')require_once(plugin_dir_path(__FILE__).'includes/small_groups.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_rota_list')require_once(plugin_dir_path(__FILE__).'includes/rota.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_visitor_list')require_once(plugin_dir_path(__FILE__).'includes/visitor.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_settings')require_once(plugin_dir_path(__FILE__).'includes/communication_settings.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_email')require_once(plugin_dir_path(__FILE__).'includes/email.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_send_sms')require_once(plugin_dir_path(__FILE__).'includes/sms.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_attendance_metrics') require_once(plugin_dir_path(__FILE__).'includes/attendance.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_calendar')require_once(plugin_dir_path(__FILE__).'includes/calendar.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_service_list')require_once(plugin_dir_path(__FILE__).'includes/services.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_funnel_list'){require_once(plugin_dir_path(__FILE__).'includes/funnel.php');}
if(isset($_GET['page'])&&$_GET['page']=='church_admin_member_type')require_once(plugin_dir_path(__FILE__).'includes/member_type.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_department_list')require_once(plugin_dir_path(__FILE__).'includes/departments.php');
if(isset($_GET['page'])&&$_GET['page']=='church_admin_permissions')require_once(plugin_dir_path(__FILE__).'includes/permissions.php');
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
	//deprecated next three lines to allow for users to edit own page;
    //$user_permissions=get_option('church_admin_user_permissions');
    //let plugin decide level of showing admin menu
    //if(!empty($user_permissions)){$level='read';}else{$level='manage_options';}
    add_menu_page('church_admin:Administration', __('Church Admin','church-admin'),  'read', 'church_admin/index.php', 'church_admin_main');
    add_submenu_page('church_admin/index.php', __('Permissions','church-admin'), 'Permissions', 'manage_options', 'church_admin_permissions', 'church_admin_permissions');
    add_submenu_page('church_admin/index.php', __('Settings','church-admin'), 'Settings', 'manage_options', 'church_admin_settings', 'church_admin_settings');

}

// Admin Bar Customisation
function church_admin_admin_bar_render() {
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
add_action( 'wp_before_admin_bar_render', 'church_admin_admin_bar_render' );



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
        require_once(plugin_dir_path(__FILE__).'includes/cronemail.php');
    }
//end of cron stuff


//main admin page function


function church_admin_main() 
{
    global $wpdb,$church_admin_version;
	//allow people to edit their own entry

	$self_edit=FALSE;
	$user_id=get_current_user_id();
	if(!empty($_GET['household_id']))$check=$wpdb->get_var('SELECT user_id FROM '.CA_PEO_TBL.' WHERE user_id="'.esc_sql($user_id).'" AND household_id="'.esc_sql($_GET['household_id']).'"');
	if(!empty($check) && $check==$user_id)$self_edit=TRUE;
	
	$id=!empty($_GET['id'])?$_GET['id']:NULL;
	$rota_id=!empty($_GET['rota_id'])?$_GET['rota_id']:NULL;
	$copy_id=!empty($_GET['copy_id'])?$_GET['copy_id']:NULL;
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
	$facilities_id=!empty($_REQUEST['facilities_id'])?$_REQUEST['facilities_id']:NULL;
    $edit_type=!empty($_REQUEST['edit_type'])?$_REQUEST['edit_type']:'single';
    $file=!empty($_GET['file'])?$_GET['file']:NULL;
	
    if(!empty($_REQUEST['church_admin_search'])){if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_search($_REQUEST['church_admin_search']);}}
	elseif(isset($_GET['action']))
    {
	switch($_GET['action'])
	{
	
		//kids work
		case 'edit_kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_edit_kidswork($id);break;
		case 'delete_kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_delete_kidswork($id);break;
		case 'kidswork':require_once(plugin_dir_path(__FILE__).'includes/kidswork.php');church_admin_kidswork();break;
		case 'individual_attendance':require_once(plugin_dir_path(__FILE__).'includes/individual_attendance.php');church_admin_individual_attendance();break;
		//prayer chain 
		
		case'prayer_chain_message':if(church_admin_level_check('Prayer Chain')){require_once(plugin_dir_path(__FILE__).'includes/prayer_chain.php');church_admin_prayer_chain();}else{echo"You don't have permission to send a prayer chain message"; }break;
		//hope team
		case'hope_team_jobs':check_admin_referer('hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_hope_team_jobs($id);break;
		case'edit_hope_team_job':check_admin_referer('hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_edit_hope_team_job($id);break;
		case'delete_hope_team_job':check_admin_referer('delete_hope_team_jobs');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_delete_hope_team_job($id);break;
		case'edit_hope_team':check_admin_referer('edit_hope_team');require_once(plugin_dir_path(__FILE__).'includes/hope-team.php');church_admin_edit_hope_team($id);break;
		//errors
		case 'church_admin_activation_log_clear':check_admin_referer('clear_error');church_admin_activation_log_clear();break;
		//mailchimp
		case'mailchimp_sync':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/mailchimp.php');church_admin_mailchimp_sync();}break;
		//premissions
		case'permissions':require_once(plugin_dir_path(__FILE__).'includes/permissions.php');church_admin_permissions();break;
	//backups
	    case'refresh_backup':check_admin_referer('refresh_backup');church_admin_backup();church_admin_front_admin();break;
	    case'delete_backup':check_admin_referer('delete_backup');church_admin_delete_backup();church_admin_front_admin();break;
	//sermon podcasts
	    case'list_speakers':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_speakers();break;
            case'edit_speaker':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_speaker($id);break;
            case'delete_speaker':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_speaker($id);break;
            case'list_sermon_series':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_series();break;
            case'edit_sermon_series':check_admin_referer('edit_sermon_series');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_series($id);break;
            case'delete_sermon_series':check_admin_referer('delete_sermon_series');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_series($id);break;
            case'list_files':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_list_files();break;
            case'edit_file':check_admin_referer('edit_podcast_file');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_edit_file($id);break;
            case'delete_file':check_admin_referer('delete_podcast_file');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_delete_file($id);break;
            case'file_delete':check_admin_referer('file_delete');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_file_delete($file);break;
            case'file_add':check_admin_referer('file_add');require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_file_add($file);break;
            case'check_files':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');ca_podcast_check_files();break;
            case'podcast':require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');if(ca_podcast_xml()){echo'<p>Podcast <a href="'.CA_POD_URL.'podcast.xml">feed</a> updated</p>';}break;
            case'podcast_settings':check_admin_referer('podcast_settings');require_once(plugin_dir_path(__FILE__).'includes/podcast-settings.php');ca_podcast_settings();break;
            
	    case 'church_admin_send_sms':if(church_admin_level_check('Bulk SMS')){require_once(plugin_dir_path(__FILE__ ).'includes/sms.php');church_admin_send_sms();}break;
	    
	    case 'church_admin_send_email':if(church_admin_level_check('Bulk Email')){require_once(plugin_dir_path(__FILE__).'includes/email.php');church_admin_send_email();}break;
	    case'church_admin_people_activity':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/people_activity.php'); echo church_admin_recent_people_activity();}break;
	    //attendance
	    case 'church_admin_attendance_metrics':require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_attendance_metrics($service_id);break;   
		
	    case 'church_admin_attendance_list':require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_attendance_list($service_id);break;    
	    case 'church_admin_edit_attendance':check_admin_referer('edit_attendance');require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_edit_attendance($attendance_id);break;         
	    case 'church_admin_delete_attendance':check_admin_referer('delete_attendance');require_once(plugin_dir_path(__FILE__).'includes/attendance.php');church_admin_delete_attendance($attendance_id);break;         
	   
	    //departments
	    case 'church_admin_edit_department':check_admin_referer('edit_department');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_edit_department($department_id);break;         
	    case 'church_admin_delete_department':check_admin_referer('delete_department');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_delete_department($department_id);break;         
	    case 'church_admin_department_list':check_admin_referer('department_list');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_department_list();break;         
       case 'church_admin_view_department':check_admin_referer('view_department');require_once(plugin_dir_path(__FILE__).'includes/departments.php');church_admin_view_department($department_id);break;
	    //funnel
	    case 'church_admin_funnel_list':require_once(plugin_dir_path(__FILE__).'includes/funnel.php');church_admin_funnel_list();break;         
	    case 'church_admin_edit_funnel':check_admin_referer('edit_funnel');require_once(plugin_dir_path(__FILE__).'includes/funnel.php');church_admin_edit_funnel($funnel_id,$people_type_id);break;
	    case 'church_admin_assign_funnel':require_once(plugin_dir_path(__FILE__).'includes/people_activity.php');church_admin_assign_funnel();break;
	    case 'church_admin_email_follow_up_activity':check_admin_referer('email_funnels');require_once(plugin_dir_path(__FILE__).'includes/people_activity.php');church_admin_email_follow_up_activity();break;
	    //member_type
	         case 'church_admin_member_type':require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_member_type();break;         
	    case 'church_admin_edit_member_type':check_admin_referer('edit_member_type');require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_edit_member_type($member_type_id);break;         
	    case 'church_admin_delete_member_type':check_admin_referer('delete_member_type');require_once(plugin_dir_path(__FILE__).'includes/member_type.php');church_admin_delete_member_type($member_type_id);break;         
	   
		//facilities
	         case 'church_admin_facilities':require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_facilities();break;         
	    case 'church_admin_edit_facility':check_admin_referer('edit_facility');require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_edit_facility($facilities_id);break;         
	    case 'church_admin_delete_facility':check_admin_referer('delete_facility');require_once(plugin_dir_path(__FILE__).'includes/facilities.php');church_admin_delete_facility($facilities_id);break;   
	   
	    //calendar
	    case 'church_admin_new_calendar':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_new_calendar(time(),$facilities_id);}break;
		case 'church_admin_new_edit_calendar':if(church_admin_level_check('Calendar'))
		{
			require_once(plugin_dir_path(__FILE__).'includes/calendar.php');
			
			if(substr($id,0,4)=='item'){church_admin_event_edit(substr($id,4),NULL,$edit_type,NULL,$facilities_id);}
			else{church_admin_event_edit(NULL,NULL,NULL,$id,$facilities_id);}
		}
		break;
		case 'church_admin_calendar_list':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_calendar();}break;         
	    
	    case 'church_admin_add_category':check_admin_referer('add_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_add_category();}break;         
	    
		case 'church_admin_edit_category':check_admin_referer('edit_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_edit_category($id,NULL);}break;
	    
		case 'church_admin_delete_category':check_admin_referer('delete_category');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_delete_category($id);}break;
	    
		case 'church_admin_single_event_delete':check_admin_referer('single_event_delete');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_single_event_delete($date_id,$event_id); }break;
	    
		case 'church_admin_series_event_delete':check_admin_referer('series_event_delete');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_series_event_delete($event_id);}break;     
	    
		case 'church_admin_category_list':if(church_admin_level_check('Calendar'));{require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_category_list();}break;    
	    
		case 'church_admin_series_event_edit':check_admin_referer('series_event_edit');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit($date_id,$event_id,'series',NULL,NULL);}break;
	    
		case 'church_admin_single_event_edit':check_admin_referer('single_event_edit');if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit($date_id,$event_id,'single',NULL,NULL);}break;
	    
		case 'church_admin_add_calendar':if(church_admin_level_check('Calendar')){require_once(plugin_dir_path(__FILE__).'includes/calendar.php');church_admin_event_edit(NULL,NULL,NULL,NULL,NULL);}break;
		
	    //address
	    case 'church_admin_move_person':if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_move_person($people_id);}break;
	    case 'church_admin_address_list': if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_address_list($member_type_id);}else{echo"<p>You don't have permission to do that";}break;
	    case 'church_admin_create_user':check_admin_referer('create_user');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_create_user($people_id,$household_id);}break;      
	    case 'church_admin_migrate_users':check_admin_referer('migrate_users');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_migrate_users();}break;
	    case 'church_admin_display_household':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_display_household($household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_edit_household':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_edit_household($household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_delete_household':check_admin_referer('delete_household');if(church_admin_level_check('Directory')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_delete_household($household_id);}break;
	    case 'church_admin_edit_people':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_edit_people($people_id,$household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_delete_people':if(church_admin_level_check('Directory')||$self_edit){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_delete_people($people_id,$household_id);}else{echo'<p>'.__('You do not have permission to do that','church-admin').'</p>';}break;
	    case 'church_admin_search':if(wp_verify_nonce('ca_search_nonce','ca_search_nonce')){require_once(plugin_dir_path(__FILE__).'includes/directory.php');church_admin_search($_POST['ca_search']);}break;
		case'church_admin_recent_visitors': require_once(plugin_dir_path(__FILE__).'includes/recent.php');echo church_admin_recent_visitors($member_type_id);break;
	    //rota
		case 'copy_rota_data':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_copy_rota($copy_id,$rota_id);}break;
	    case 'church_admin_email_rota':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_email_rota($service_id);}break;
	    case 'church_admin_rota_list':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_rota_list($service_id);}break;
	    case 'church_admin_rota_settings_list':if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_rota_settings_list();}break;
	    case 'church_admin_edit_rota_settings':check_admin_referer('edit_rota_settings');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_edit_rota_settings($id);}break;
	    case 'church_admin_delete_rota_settings':check_admin_referer('delete_rota_settings');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota_settings.php');church_admin_delete_rota_settings($id);}break;
	    case 'church_admin_edit_rota':check_admin_referer('edit_rota');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_edit_rota($id,$service_id); }break;
	    case 'church_admin_delete_rota':check_admin_referer('delete_rota');if(church_admin_level_check('Rota')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_delete_rota($id);}break;
	    //visitor
	    case 'church_admin_add_visitor':check_admin_referer('add_visitor');if(church_admin_level_check('Visitor')){require_once(plugin_dir_path(__FILE__).'includes/visitor.php'); church_admin_add_visitor();} break;
	    case 'church_admin_edit_visitor':check_admin_referer('edit_visitor');if(church_admin_level_check('Visitor')){church_admin_edit_visitor($id);}break;
	    case 'church_admin_delete_visitor':check_admin_referer('delete_visitor');if(church_admin_level_check('Visitor')){church_admin_delete_visitor($id);} break;
	    case 'church_admin_move_visitor':check_admin_referer('move_visitor');if(church_admin_level_check('Visitor')){church_admin_move_visitor($id);}break;
	    //small groups
	    case  'church_admin_edit_small_group':check_admin_referer('edit_small_group');if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php');  church_admin_edit_small_group($id);}break;
	    case  'church_admin_delete_small_group':check_admin_referer('delete_small_group');if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); church_admin_delete_small_group($id);}break;
	    case 'church_admin_small_groups':if(church_admin_level_check('Small Groups')){require_once(plugin_dir_path(__FILE__).'includes/small_groups.php'); church_admin_small_groups();}break;
	    //services
	    case  'church_admin_edit_service':check_admin_referer('edit_service');if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php');  church_admin_edit_service($id);}break;
	    case  'church_admin_delete_service':check_admin_referer('delete_service');if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php'); church_admin_delete_service($id);}break;
	    case 'church_admin_service_list':if(church_admin_level_check('Service')){require_once(plugin_dir_path(__FILE__).'includes/services.php'); church_admin_service_list();}break;
	    
	    //setings
	    case 'church_admin_settings':if(current_user_can('manage_options')){require_once(plugin_dir_path(__FILE__).'includes/communication_settings.php');church_admin_settings();}break;    
	    //default
	    default:church_admin_front_admin();break;
	   
	}
    }
    else {church_admin_front_admin();}
}

function church_admin_shortcode($atts, $content = null) 
{
	
    extract(shortcode_atts(array("type" => 'address-list','days'=>30,'year'=>date('Y'),'service_id'=>1,'photo'=>NULL,'category'=>NULL,'weeks'=>4,'member_type_id'=>1,'map'=>NULL,'series_id'=>NULL,'speaker_id'=>NULL,'file_id'=>NULL,'api_key'=>NULL,'facilities_id'=>NULL), $atts));
    church_admin_posts_logout();
    $out='';
    global $wpdb;
    $wpdb->show_errors();
    global $wp_query;
    $upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/church-admin-cache/';
    //look to see if church directory is o/p on a password protected page	
    $pageinfo=get_page($wp_query->post->ID);	
    //grab page info
    //check to see if on a password protected page
    if(($pageinfo->post_password!='')&&isset( $_COOKIE['wp-postpass_' . COOKIEHASH] )) 
    {
	$text = __('Log out of password protected posts','church-admin');
	//text for link
	$link = site_url().'?church_admin_logout=posts_logout';
	$out.= '<p><a href="' . wp_nonce_url($link, 'posts logout') .'">' . $text . '</a></p>';
	//output logoutlink
    }
    //end of password protected page
   
    //grab content
    switch($type)
    {
	
		case 'recent':
			require_once(plugin_dir_path(__FILE__).'includes/recent.php');
			$out.=church_admin_recent_visitors($member_type_id=1);
		break;
		case 'podcast':
	    require_once(plugin_dir_path(__FILE__).'display/sermon-podcast.php');
		if(!empty($_GET['speaker_name'])){$speaker_name=urldecode($_GET['speaker_name']);}else{$speaker_name=NULL;}
		if(!empty($_GET['series_id'])){$series_id=urldecode($_GET['series_id']);}
	    $out.=ca_podcast_display($series_id,$file_id,$speaker_name);
		$out = apply_filters ( 'the_content', $out );
		break;    
        case 'calendar':
			if(empty($facilities_id))
			{
				$out.='<table><tr><td>'.__('Year Planner pdfs','church-admin').' </td><td>  <form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.__('Choose a pdf','church-admin').' --</option>';
				for($x=0;$x<5;$x++)
				{
					$y=date('Y')+$x;
		
					$out.='<option value="'.home_url().'/?download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.$y.'">'.$y.__('Year Planner','church-admin').'</option>';
				}
				$out.='</select></form></td></tr></table>';
			}
            

            require_once(plugin_dir_path(__FILE__).'display/calendar.php');
            
        break;
        case 'calendar-list':
            require_once(plugin_dir_path(__FILE__).'/display/calendar-list.php');
        break;
        case 'address-list':
	   
            $out.='<p><a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('member'.$member_type_id ).'&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            require_once(plugin_dir_path(__FILE__).'display/address-list.php');
            $out.=church_admin_frontend_directory($member_type_id,$map,$photo,$api_key);
        break;
        
		case 'small-groups-list':
            require_once(plugin_dir_path(__FILE__).'/display/small-group-list.php');
            $out.= church_admin_small_group_list($map);
        break;
		case 'small-groups':
            require_once(plugin_dir_path(__FILE__).'/display/small-groups.php');
            $out.=church_admin_frontend_small_groups($member_type_id);
            
        break;
		case 'rota':
            require_once(plugin_dir_path(__FILE__).'/display/rota.php');
            $out.=church_admin_front_end_rota();
        break;
        case 'rolling-average-attendance':
		
			if(!file_exists($path.'/rolling-average-attendance.png'))
			{
				require_once(plugin_dir_path(__FILE__).'/includes/graph.php');
				church_admin_rolling_attendance_graph($service_id);
			}
			$out.='<img src="'.content_url('/uploads/church-admin-cache/rolling-average-attendance.png').'" alt="'.__('Rolling Average Attendance Graph','church-admin').'" width="900" height="550"/>';
        break;

        case 'monthly-attendance':
		
			if(!file_exists($path.'/monthly-attendance'.$year.'.png'))
			{
				require_once(plugin_dir_path(__FILE__).'includes/graph.php');
				church_admin_monthly_attendance_graph($year,$service_id);
			}
			$out.='<img src="'.content_url('/uploads/church-admin-cache/monthly-attendance'.$year.'.png').'" alt="'.__('Monthly Attendance Graph','church-admin').'" width="700" height="500"/>';
        break;
		case 'weekly-attendance':
		
			if(!file_exists($path.'/weekly-attendance'.$year.'.png'))
			{
				require_once(plugin_dir_path(__FILE__).'includes/graph.php');
				church_admin_weekly_attendance_graph($year,$service_id);
			}
			
			$out.='<img src="'.content_url('/uploads/church-admin-cache/weekly-attendance'.$year.'.png').'" alt="'.__('Weekly Attendance Graph','church-admin').'" width="900" height="500"/>';
        break;
		case 'rolling-average':
		if(!file_exists($path.'rolling-average-attendance.png'))
			{
				require_once(plugin_dir_path(__FILE__).'includes/graph.php');
				church_admin_rolling_attendance_graph($service_id);
			}
			$upload_dir = wp_upload_dir();
			if(file_exists($path.'rolling_average_attendance.png'))$out.='<img src="'.content_url('/uploads/church-admin-cache/rolling-average.png').'" alt="'.__('Rolling Average Graph','church-admin').'"/>';
        break;
		case 'birthdays':require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');$out.=church_admin_frontend_birthdays($member_type_id, $days);break;
	default:
			$out.='<p><a href="'.home_url().'/?download=addresslist&amp;addresslist='.wp_create_nonce('member'.$member_type_id ).'&amp;member_type_id='.$member_type_id.'">'.__('PDF version','church-admin').'</a></p>';
            require_once(plugin_dir_path(__FILE__).'display/address-list.php');
            $out.=church_admin_frontend_directory($member_type_id,$map,$photo,$api_key);
        break;
    }
//output content instead of shortcode!
return $out; 
}
add_shortcode('church_admin_recent','church_admin_recent');
function church_admin_recent()
{
    extract(shortcode_atts(array('month'=>1), $atts));
    require_once(plugin_dir_path(__FILE__).'includes/recent.php');church_admin_recent_display($month);
}
add_shortcode("church_admin", "church_admin_shortcode");
add_shortcode("church_admin_map","church_admin_map");
function church_admin_map($atts, $content = null) 
{
	$out='';
    extract(shortcode_atts(array('zoom'=>13,'member_type_id'=>1,'small_group'=>1,'unattached'=>0), $atts));
    global $wpdb;
    $service=$wpdb->get_row('SELECT lat,lng FROM '.CA_SER_TBL.' LIMIT 1');
    $out.='<div class="church-map"><script type="text/javascript">var xml_url="'.site_url().'/?download=address-xml&member_type_id='.$member_type_id.'&small_group='.$small_group.'&unattached='.$unattached.'&address-xml='.wp_create_nonce('address-xml').'";';
    $out.=' var lat='.$service->lat.';';
    $out.=' var lng='.$service->lng.';';
    
    $out.='jQuery(document).ready(function(){
    load(lat,lng,xml_url);});</script><div id="map"></div>';
    if(empty($small_group)){$out.='<div id="groups" style="display:none"></div>';}else{$out.='<div id="groups" ></div>';}
    $out.='</div>';
    
    return $out;
    
}
add_shortcode("church_admin_register","church_admin_register");
function church_admin_register($atts, $content = null)
{
    extract(shortcode_atts(array('email_verify'=>TRUE,'admin_email'=>TRUE,'member_type_id'=>1), $atts));
    require_once(plugin_dir_path(__FILE__).'includes/front_end_register.php');
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
    wp_register_sidebar_widget('Church-Admin-Calendar','Church Admin Calendar','church_admin_calendar_widget');
    require_once(plugin_dir_path(__FILE__).'includes/calendar_widget.php');
    wp_register_widget_control('Church-Admin-Calendar','Church Admin Calendar','church_admin_widget_control');
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
		require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');
		echo $out;
		echo $after_widget;
	}
}
function church_admin_birthday_widget_init()
{
    wp_register_sidebar_widget('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget');
    require_once(plugin_dir_path(__FILE__).'includes/birthdays.php');
    wp_register_widget_control('Church Admin Birthdays','Church Admin Birthdays','church_admin_birthday_widget_control');
}
add_action('init','church_admin_birthday_widget_init');
function church_admin_sermons_widget($args)
{
    global $wpdb;
	church_admin_latest_sermons_scripts();
    $wpdb->show_errors();
    extract($args);
    $options=get_option('church_admin_latest_sermons_widget');
    $title=$options['title'];
	$limit=$options['sermons'];
    echo $before_widget;
    if ( $title )echo $before_title . $title . $after_title;
	require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');
    echo church_admin_latest_sermons_widget_output($limit,$title);
    echo $after_widget;
}
function church_admin_sermons_widget_init()
{
    wp_register_sidebar_widget('Church-Admin-Latest-Sermons','Church Admin Latest Sermons','church_admin_sermons_widget');
    require_once(plugin_dir_path(__FILE__).'includes/sermon-podcast.php');
    wp_register_widget_control('Church-Admin-Latest-Sermons','Church Admin Latest Sermons','church_admin_latest_sermons_widget_control');

	
}
function church_admin_latest_sermons_scripts()
{
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");		
	wp_enqueue_script('ca_podcast_audio',plugins_url('church-admin/includes/audio.min.js',dirname(__FILE__)),'',NULL);
	wp_enqueue_script('ca_podcast_audio_use',plugins_url('church-admin/includes/audio.use.js',dirname(__FILE__)),'',NULL);
	wp_localize_script( 'ca_podcast_audio_use', 'ChurchAdminAjax', array('security'=>$ajax_nonce, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}
add_action('init','church_admin_sermons_widget_init');
function church_admin_download($file)
{
    switch($file)
    {
		case'kidswork_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_kidswork_pdf();break;
		case'horizontal_rota_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_horiz_pdf($_GET['service_id']);break;
		case 'hope_team_pdf':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_hope_team_pdf();break;
		case'ministries_pdf':if(wp_verify_nonce($_GET['_wpnonce'],'ministries_pdf')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_ministry_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case 'people-csv':if(wp_verify_nonce($_GET['people-csv'],'people-csv')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_people_csv($_GET['member_type_id'],$_GET['people_type_id'],$_GET['sex'],$_GET['address'],$_GET['small_group']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case 'small-group-xml':if(wp_verify_nonce($_GET['small-group-xml'],'small-group-xml')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_small_group_xml();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case 'address-xml':require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_address_xml($_GET['member_type_id'],$_GET['small_group']);break;
        case'cron-instructions':if(wp_verify_nonce($_GET['cron-instructions'],'cron-instructions')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_cron_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'rota':if(wp_verify_nonce($_GET['rota'],'rota')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_rota_pdf();}else{echo'<p>You can only download if coming from a valid link</p>';}break;
        case'yearplanner':if(wp_verify_nonce($_GET['yearplanner'],'yearplanner')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_year_planner_pdf($_GET['year']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'smallgroup':if(wp_verify_nonce($_GET['smallgroup'],'smallgroup')){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_smallgroup_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'addresslist':if(wp_verify_nonce($_GET['addresslist'],'member'.$_GET['member_type_id'])){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_address_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'vcf':if(wp_verify_nonce($_GET['vcf'],$_GET['id'])){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');ca_vcard($_GET['id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
		case'mailinglabel':if(wp_verify_nonce($_GET['mailinglabel'],'mailinglabel'.$_GET['member_tye_id'])){require_once(plugin_dir_path(__FILE__).'includes/pdf_creator.php');church_admin_label_pdf($_GET['member_type_id']);}else{echo'<p>You can only download if coming from a valid link</p>';}break;
        case 'rotacsv':if(wp_verify_nonce($_GET['rotacsv'],'rotacsv')){require_once(plugin_dir_path(__FILE__).'includes/rota.php');church_admin_rota_csv($_GET['service_id']); }else{echo'<p>You can only download if coming from a valid link</p>';}break;
    }
}
function church_admin_delete_backup()
{
	$filename=get_option('church_admin_backup_filename');
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir']; 
	if($filename&& file_exists($path.'/church-admin-cache/'.$filename))unlink($path.'/church-admin-cache/'.$filename);
}
function church_admin_backup()
{
    global $church_admin_version,$wpdb;
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_ATT_TBL.'"') == CA_ATT_TBL)$content=church_admin_datadump (CA_ATT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_BIB_TBL.'"') == CA_BIB_TBL)$content.=church_admin_datadump (CA_BIB_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_CAT_TBL.'"') == CA_CAT_TBL)$content.=church_admin_datadump (CA_CAT_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FIL_TBL.'"') == CA_FIL_TBL)$content.=church_admin_datadump (CA_FIL_TBL);
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FAC_TBL.'"') == CA_FAC_TBL)$content.=church_admin_datadump (CA_FAC_TBL);
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_DATE_TBL.'"') == CA_DATE_TBL)$content.=church_admin_datadump (CA_DATE_TBL);
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
	$length = 10;
	$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	$filename=md5($randomString).'.sql.gz';
	update_option('church_admin_backup_filename',$filename);
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir']; 
    if(!empty($content))
    {
		$gzdata = gzencode($content);
		$loc=$path.'/church-admin-cache/'.$filename;
		$fp = fopen($loc, 'w');
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
 


add_action('wp_ajax_ajax_rota_edit', 'church_admin_action_rota_edit');
add_action('wp_ajax_nopriv_ajax_rota_edit', 'church_admin_action_rota_edit');
function church_admin_action_rota_edit()
{
	
	check_ajax_referer('ajax_rota_edit','security',TRUE);
	global $wpdb;	
	$id=$_POST['id'];
	$details=explode('~',$id);
	$job_id=$wpdb->get_var('SELECT rota_id FROM '.CA_RST_TBL.' WHERE rota_task="'.esc_sql($details[0]).'"');
	$row=$wpdb->get_row('SELECT * FROM '.CA_ROT_TBL.' WHERE rota_id="'.esc_sql($details[1]).'"');
	$jobs=maybe_unserialize($row->rota_jobs);
	$jobs[$job_id]= church_admin_get_people_id($_POST['value']);
	$sql='UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.esc_sql(maybe_serialize($jobs)).'" WHERE  rota_id="'.esc_sql($details[1]).'"';
	echo $_POST['value'];
	$wpdb->query($sql);
	die();
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
 function church_admin_activation_log_clear(){delete_option('church_admin_plugin_error');church_admin_front_admin();} 
if ( ! function_exists( 'unregister_post_type' ) ) :
function unregister_post_type( $post_type ) {
    global $wp_post_types;
    if ( isset( $wp_post_types[ $post_type ] ) ) {
        unset( $wp_post_types[ $post_type ] );
        return true;
    }
    return false;
}
endif;
//auto rota email section
if(!empty($_POST['email_rota_day']))
{
	$email_day=(int)$_POST['email_rota_day'];
	if($email_day==8){delete_option('church_admin_email_rota_day');wp_clear_scheduled_hook('church_admin_cron_email_rota');}
	if($email_day>=0&& $email_day<=6)update_option('church_admin_email_rota_day',$email_day);
}
add_action('church_admin_cron_email_rota', 'church_admin_auto_email_rota');
function rota_email_activation() 
{
    if ( !wp_next_scheduled( 'church_admin_cron_email_rota' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'daily', 'church_admin_cron_email_rota');
    }
}
add_action('wp', 'rota_email_activation');

function church_admin_auto_email_rota()
{
    global $wpdb;
	$email_day=get_option('church_admin_email_rota_day');
	
	// Get the current date time
    $dateTime = new DateTime();
	
    // Check that the day is Monday
    if(!empty($email_day)&&$dateTime->format('N') == $email_day)
    {//do once now!
		church_admin_debug('Running Auto Email run at '.date('y-m-d H:i:s'));
        $services=$wpdb->get_results('SELECT service_id FROM '.CA_SER_TBL);
		if(!empty($services))
		{
			
			require_once(plugin_dir_path(__FILE__).'includes/rota.php');
			foreach($services AS $service)
			{
				
				
				$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
				$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE service_id="'.esc_sql($service->service_id).'" AND rota_date>CURDATE() LIMIT 1';
				church_admin_debug($sql);
				$results=$wpdb->get_row($sql);
				
				if(!empty($results))
				{
					
					//build rota with jobs
					$message='<p>Rota for  '.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</p>';
					$message.='<table><thead><tr><th>'.__('Job','church-admin').'</th><th>'.__('Who','church-admin').'</th></tr></thead><tbody>';
					if(!empty($rota_jobs))
					{
						foreach($rota_tasks AS $task_row)
						{
							if(!empty($rota_jobs[$task_row->rota_id])) $message.='<tr><td><strong>'.esc_html($task_row->rota_task).': </strong></td><td>'.esc_html(church_admin_get_people($rota_jobs[$task_row->rota_id])).'</td></tr>';
						}
						$message.='</tbody></table>';
					}
					//grab unique people_ids
					$people_ids=array();
					foreach( $rota_jobs AS $key=>$value)
					{
						if(!empty($value))
						{
							$jobs=maybe_unserialize($value);
							foreach($jobs AS $k=>$id)
							{
								if(!in_array($id,$people_ids))$people_ids[]=$id;//only add unique ids
							}
						}
					}
	
					//start emailing the message
				
					if(!empty($people_ids))
					{
						
						foreach($people_ids AS $key=>$people_id)
						{
							$row=$wpdb->get_row('SELECT CONCAT_WS(" ", first_name,last_name) AS name, email FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"');
							if(!empty($row->email))
							{
								if(!empty($row->name))$email_content='<p>'.__('Dear','church-admin').' '.$row->name.',</p>'.$message;
								add_filter( 'wp_mail_content_type', 'set_html_content_type' );
								$headers = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n";
								wp_mail($row->email,"This week's service rota",$email_content,$headers);
								remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
							}
						}	
					}
				}
			}
		}
	}
}

function church_admin_debug($message)
{
	$upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/';
	$fp = fopen($debug_path.'debug.log', 'a');
    fwrite($fp, $message);
    fclose($fp);
} 
?>