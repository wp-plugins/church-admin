<?php
//2011-03-08 fixed calendar CSS for 20:20
//2011-03-22 fixed toggle on calendar pages
//Set up header for admin
add_action('admin_head', 'church_admin_header');
function church_admin_header()
{
    wp_enqueue_style('Church Admin',CHURCH_ADMIN_INCLUDE_URL.'admin.css');
 
}
add_action('wp_head','church_admin_public_css');
function church_admin_public_css(){wp_enqueue_style('Church Admin',CHURCH_ADMIN_INCLUDE_URL.'public.css');}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{
    global $church_admin_version;
     
    echo'<!-- church_admin v'.$church_admin_version.'-->
    <style>table.church_admin_calendar{width:';
    if(get_option('church_admin_calendar_width')){echo get_option('church_admin_calendar_width').'}</style>';}else {echo'700}</style>';}
    
}
?>