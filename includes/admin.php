<?php
function church_admin_front_admin()
{
    global $people_type,$member_type,$wpdb;
    echo'<div class="wrap church_admin"><h2>Church Admin plugin</h2>';
    //top message box
    echo '<!-- end top message box--><div class="updated fade"><p><a id="showhidetrigger" href="#" style="float:right;">show/hide plugin information</a>This is version '.get_option("church_admin_version").' of the <strong>Church Admin</strong> plugin by Andy Moyle.</p>';
    echo'<div id="showhidetarget">';
    echo'<a href="http://twitter.com/#!/WP_Church_Adm" style="float:right"><img src="'.CHURCH_ADMIN_IMAGES_URL.'FollowOnTwitter.png" width="90" height="35"   alt="Twitter"/></a>';
    echo'<p><a href="http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin/plugin-support">Get Support</a><br/><strong>Latest News</strong></p>';
    require(CHURCH_ADMIN_INCLUDE_PATH.'news-feed.php');
    echo church_admin_news_feed();
    echo ' If you like the plugin, please buy me a cup of coffee!...<form class="right" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif"  name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form>';
    echo'</div></div><!-- end top message box-->';
    echo'<script type="text/javascript">
        jQuery(document).ready(function($) {
                $(\'#showhidetarget\').hide();
                $(\'a#showhidetrigger\').click(function () {
                $(\'#showhidetarget\').toggle(400);
            });
        });
    </script>';
    
    //end top message box
        //show activation errors
    $act_error=get_option('activation_error');
   if(!empty($act_error)) echo '<div class="updated fade"><h2> You had an activation error, oh beta tester</h2><p> please post it to the forum on <a href="http://www.themoyles.co.uk">www.themoyles.co.uk</a>.</p>'.$act_error.'</div>';
   //end show activation errors
   echo'<p><a id="showhideactivity" href="#" ">Show/Hide recent people activity</a></p>';
    echo'<div id="recentpeopleactivity">';
    require_once(CHURCH_ADMIN_INCLUDE_PATH.'people_activity.php');
    church_admin_recent_people_activity();
    echo'</div>';
    echo'<script type="text/javascript">
        jQuery(document).ready(function($) {
                $(\'#recentpeopleactivity\').hide();
                $(\'a#showhideactivity\').click(function () {
                $(\'#recentpeopleactivity\').toggle(400);
            });
        });
    </script>';
    //People Functions
    echo'<div class="church_admin_main_menu"><h2>People Functions</h2>';
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">Add Household</a></p>';
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people','edit_people').'">Add a new person (not connected to a current household)</a></p>';
    echo'<p><label>Select Address List</label><form name="address" action="admin.php?page=church_admin/index.php&action=church_admin_address_list" method="POST"><select onchange="this.form.submit();" name="member_type_id" >';
    echo '<option value="">Choose Member Type...</option>';
    foreach($member_type AS $key=>$value)
    {
	echo '<option value="'.$key.'" >'.$value.'</option>';
    }
    echo'</select></form></p>';
    echo'<p><label>Search</label><form name="ca_search" action="admin.php?page=church_admin/index.php&amp;action=church_admin_search" method="POST"><input name="ca_search" style="width:100px;" type="text"/><input type="submit" value="Go"/></form></p>';
    echo '<p><label>Download an address list PDF</label></p><p>';
    
    foreach($member_type AS $key=>$value)
    {
	echo'<a href="'.home_url().'/?download=mailinglabel&member_type_id='.$key.'">'.$value.' - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</a><br/>';
    }
    foreach($member_type AS $key=>$value)
    {
	echo'<a href="'.home_url().'/?download=addresslist&member_type_id='.$key.'">'.$value.' Address List PDF</a><br/>';
    }
    echo'</p>';
    echo'</div>';
    //end people
    //member types
    echo'<div class="church_admin_main_menu"><h2>Member Types</h2>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&action=church_admin_edit_member_type",'edit_member_type').'">Add a member Type</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_member_type">Member Type List</a></p>';
    echo'</div>';
    //communications
    echo'<div class="church_admin_main_menu"><h2>Communications</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_send_sms">Send Bulk SMS</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_send_email">Send Bulk Email</a></p>';
    echo'</div>';
    //follow up
    echo'<div class="church_admin_main_menu"><h2>Follow Up Funnels</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel','edit_funnel').'">Add a follow up funnel</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_funnel_list">Follow Up Funnel List</a></p>';
      
    echo'</div>';
    echo'<div class="clear"></div>';
     
    //follow up
    //Rota
    echo'<div class="church_admin_main_menu"><h2>Rota</h2>';
    echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_rota_settings_list","rota_settings_list").'">View/Edit Rota Jobs</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >Add more rota jobs</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota",'edit_rota').'">Add to rota</a></p>';
    echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
    echo'<p><label>Select a service rota</label><select name="service_id" onchange="this.form.submit();">';
    echo'<option value="">Choose a service...</option>';
     $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    foreach($services AS $service)
    {
	echo'<option value="'.$service->service_id.'">'.$service->service_name.' on '.$days[$service->service_day].' at '.$service->service_time.' '.$service->venue.'</option>';
    }
    echo'</select></p>';
    echo'</form></div>';
   
    //Rota
    

    //Calendar
    echo'<div class="church_admin_main_menu"><h2>Calendar</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_calendar">Add calendar Event</a></p>';
    echo'<p><a href="admin.php?page=church_admin_calendar">View Calendar</a></p>';
   
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_add_category','add_category').'">Add a category</a></p>';
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_category_list','category_list').'">Category List</a></p>';
    
    
    echo '<p><label>Download a year planner PDF</label><form name="calendar_form" action="" method="get"><select name="calendar_form_links" onchange="window.location=document.calendar_form.calendar_form_links.options[document.calendar_form.calendar_form_links.selectedIndex].value">';
    echo'<option selected="selected" value="'.home_url().'/?download=yearplanner&amp;year='.date('Y').'">-- Choose a pdf --</option>';
    for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		echo '<option value="'.home_url().'/?download=yearplanner&amp;year='.$y.'">'.$y.' Year Planner</option>';
	    }
    echo'</select></form></p>';
    echo'</div>';
    
    //end calendar


    echo'<div class="clear"></div>';
    //small Group
    echo'<div class="church_admin_main_menu"><h2>Small groups</h2>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_small_group",'edit_small_group').'">Add a small group</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_small_groups">Small Group List</a></p>';
    echo '<p><label>Download an small group PDF</label><form name="address_list_form" action="'.home_url().'" method="get"><input type="hidden" name="download" value="smallgroup"/><select name="member_type_id" onchange="this.form.submit()">';
    echo'<option selected="selected" value="1">-- Choose a pdf --</option>';

    foreach($member_type AS $key=>$value)
    {
	echo'<option value="'.$key.'">'.$value.' Small group PDF</option>';
    }
    echo '<option value="'.home_url().'/?download=smallgroups&member_type_id='.implode(",",array_keys($member_type)).'">All member types Small group PDF</option>';
    echo'</select></form></p>';
    echo'</div>';
    //Services
    echo'<div class="church_admin_main_menu"><h2>Services</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_service_list">Service List</a></p>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service','edit_service').'">Add a service</a></p>';
    echo'</div>';
    //End Services
    
    echo'<div class="clear"></div>';

    
}
?>