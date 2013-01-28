<?php
function church_admin_front_admin()
{
    global $people_type,$member_type,$wpdb,$days;
    echo'<div class="wrap church_admin"><h2>Church Admin plugin</h2>';
    //top message box
    echo '<!-- end top message box--><div class="updated fade"><p><a id="showhidetrigger" href="#" style="float:right;">'.__('Show/Hide plugin information','church-admin').'</a>'.__('This is version','church-admin').' '.get_option("church_admin_version").' '.__('of the','church-admin').'<strong>Church Admin</strong> plugin by Andy Moyle.</p>';
    echo'<div id="showhidetarget">';
    echo'<p><a href="http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin/plugin-support">'.__('Get Support','church-admin').'</a><br/><strong>'.__('Latest News','church-admin').'</strong></p>';
    require(CHURCH_ADMIN_INCLUDE_PATH.'news-feed.php');
    echo church_admin_news_feed();
    echo __('If you like the plugin, please buy me a cup of coffee!','church-admin').'...<form class="right" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52"><input type="image"  src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif"  name="submit" alt="PayPal - The safer, easier way to pay online."><img alt=""  border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form>';
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
   if(!empty($act_error)) echo '<div class="updated fade"><h2>'.__('You had an activation error','church-admin').'</h2><p>'.__('Please post it to the forum on ','church-admin').'<a href="http://www.themoyles.co.uk">www.themoyles.co.uk</a>.</p>'.$act_error.'</div>';
   //end show activation errors
   echo'<p><a id="showhideactivity" href="#" ">'.__('Show/Hide recent people activity','church-admin').'</a></p>';
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
    echo'<h2>'.__('Quick Glossary','church-admin').'</h2>';
    echo'<p><strong>'.__('Member Type','church-admin').'</strong> - '.__('Are the broad categories within church life a person is in eg. First Time Visitor, Regular Attender, Member.','church-admin').'</p>';
    echo'<p><strong>'.__('Ministries','church-admin').'</strong> - '.__('Are the ministries a person is involved with like being an elder, small group leader. Used to be called "Departments" but people got too confused!','church-admin').'</p>';
    echo'<p><strong>'.__('Small Groups','church-admin').'</strong> -'.__("If you have small groups, that's what this section is for. Before creating small groups, you need to make sure your small group leaders have 'Small Group leader' checked in the department.",'church-admin').'</p>';
    //People Functions
    echo'<div class="church_admin_main_menu"><h2>'.__('People Functions','church-admin').'</h2>';
        echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_migrate_users','migrate_users').'">'.__('Import Wordpress Users (only new ones added)','church-admin').'</a></p>';
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_household','edit_household').'">'.__('Add a Household','church-admin').'</a></p>';
    echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_people','edit_people').'">'.__('Add a new person (not connected to a current household)','church-admin').'</a></p>';
    echo'<p><label>Select Address List</label><form name="address" action="admin.php?page=church_admin/index.php&action=church_admin_address_list" method="POST"><select onchange="this.form.submit();" name="member_type_id" >';
    echo '<option value="">'.__('Choose Member Type...','church-admin').'</option>';
    foreach($member_type AS $key=>$value)
    {
	echo '<option value="'.$key.'" >'.$value.'</option>';
    }
    echo'</select></form></p>';
    echo'<p><label>'.__('Search','church-admin').'</label><form name="ca_search" action="admin.php?page=church_admin/index.php&amp;action=church_admin_search" method="POST"><input name="ca_search" style="width:100px;" type="text"/><input type="submit" value="Go"/></form></p>';
    echo '<p>'.__('Download an address list PDF','church-admin').'</p><p>';
    
    foreach($member_type AS $key=>$value)
    {
	echo'<a href="'.home_url().'/?download=mailinglabel&member_type_id='.$key.'">'.$value.' - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</a><br/>';
    }
    foreach($member_type AS $key=>$value)
    {
	echo'<a href="'.home_url().'/?download=addresslist&member_type_id='.$key.'">'.$value.' '.__('Address List PDF','church-admin').'</a><br/>';
    }
    echo'</p>';
    echo'</div>';
    //end people
    
    //departments
    echo'<div class="church_admin_main_menu"><h2>'.__('Ministry','church-admin').'</h2>';
    echo'<p>'.__('In this section you can set up the ministry a person is involved in or a role that they have e.g. Elder or Small Group Leader or P.A. operator','church-admin').'</p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&action=church_admin_department_list",'department_list').'">'.__('Ministry List','church-admin').'</a></p>';
    echo'</div>';
    //departments
    //member types
    echo'<div class="church_admin_main_menu"><h2>'.__('Member Types','church-admin').'</h2>';
    
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&action=church_admin_edit_member_type",'edit_member_type').'">'.__('Add a member Type','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_member_type">'.__('Member Type List','church-admin').'</a></p>';
    echo'</div>';
    //communications
    echo'<div class="church_admin_main_menu"><h2>'.__('Communications','church-admin').'</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_send_sms">'.__('Send Bulk SMS','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_send_email">'.__('Send Bulk Email','church-admin').'</a></p>';
    echo'</div>';
    //follow up
    echo'<div class="church_admin_main_menu"><h2>'.__('Follow Up Funnels','church-admin').'</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_funnel','edit_funnel').'">'.__('Add a follow up funnel','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_funnel_list">'.__('Follow Up Funnel List','church-admin').'</a></p>';
      
    echo'</div>';
    echo'<div class="clear"></div>';
     
    //follow up
    //Rota
    echo'<div class="church_admin_main_menu"><h2>'.__('Rota','church-admin').'</h2>';
    echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_rota_settings_list","rota_settings_list").'">'.__('View/Edit Rota Jobs','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'" >'.__('Add more rota jobs','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota",'edit_rota').'">'.__('Add to rota','church-admin').'</a></p>';
    echo'<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_rota_list" method="POST">';
    echo'<p><label>Select a service rota</label><select name="service_id" onchange="this.form.submit();">';
    echo'<option value="">'.__('Choose a service','church-admin').'...</option>';
     $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    foreach($services AS $service)
    {
	echo'<option value="'.$service->service_id.'">'.$service->service_name.' '.__('on','church-admin').' '.$days[$service->service_day].' '.__('at','church-admin').' '.$service->service_time.' '.$service->venue.'</option>';
    }
    echo'</select></p>';
    echo'</form>';
    echo'<p><strong>Download a service rota CSV</strong><br/>';
    $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
    foreach($services AS $service)
    {
	echo'<a href="'.home_url().'/?download=rotacsv&amp;service_id='.$service->service_id.'">'.$service->service_name.' '.__('on','church-admin').' '.$days[$service->service_day].' '.__('at','church-admin').' '.$service->service_time.' '.$service->venue.'</a><br/>';}
    echo'</p></div>';
   
    //Rota
    

    //Calendar
    echo'<div class="church_admin_main_menu"><h2>'.__('Calendar','church-admin').'</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_calendar">'.__('Add calendar Event','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_calendar_list">'.__('View Calendar','church-admin').'</a></p>';
   
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_add_category','add_category').'">'.__('Add a category','church-admin').'</a></p>';
     echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_category_list','category_list').'">'.__('Category List','church-admin').'</a></p>';
    
    
    echo '<p><label>'.__('Download a year planner PDF','church-admin').'</label><form name="calendar_form" action="" method="get"><select name="calendar_form_links" onchange="window.location=document.calendar_form.calendar_form_links.options[document.calendar_form.calendar_form_links.selectedIndex].value">';
    echo'<option selected="selected" value="'.home_url().'/?download=yearplanner&amp;year='.date('Y').'">-- '.__('Choose a pdf','church-admin').' --</option>';
    for($x=0;$x<5;$x++)
	    {
		$y=date('Y')+$x;
		echo '<option value="'.home_url().'/?download=yearplanner&amp;year='.$y.'">'.$y.' '.__('Year Planner','church-admin').'</option>';
	    }
    echo'</select></form></p>';
    echo'</div>';
    
    //end calendar


    echo'<div class="clear"></div>';
    //small Group
    echo'<div class="church_admin_main_menu"><h2>'.__('Small groups','church-admin').'</h2>';
    echo'<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_small_group",'edit_small_group').'">'.__('Add a small group','church-admin').'</a></p>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_small_groups">'.__('Small Group List','church-admin').'</a></p>';
    echo '<p><label>'.__('Download an small group PDF','church-admin').'</label><form name="address_list_form" action="'.home_url().'" method="get"><input type="hidden" name="download" value="smallgroup"/><select name="member_type_id" onchange="this.form.submit()">';
    echo'<option selected="selected" value="1">-- '.__('Choose a pdf','church-admin').' --</option>';

    foreach($member_type AS $key=>$value)
    {
	echo'<option value="'.$key.'">'.$value.' '.__('Small group PDF','church-admin').'</option>';
    }
    echo '<option value="'.home_url().'/?download=smallgroups&member_type_id='.implode(",",array_keys($member_type)).'">'.__('All member types Small group PDF','church-admin').'</option>';
    echo'</select></form></p>';
    echo'</div>';
    //Services
    echo'<div class="church_admin_main_menu"><h2>'.__('Services','church-admin').'</h2>';
    echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_service_list">'.__('Service List','church-admin').'</a></p>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_service','edit_service').'">'.__('Add a service','church-admin').'</a></p>';
    echo'</div>';
    //End Services
    
    echo'<div class="clear"></div>';
  //attendance
  echo'<div class="church_admin_main_menu"><h2>'.__('Attendance','church-admin').'</h2>';
  echo'<p><a href="admin.php?page=church_admin/index.php&action=church_admin_attendance_metrics">'.__('Church Attendance Data','church-admin').'</a></p>';
  $services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
  foreach($services AS $service)  echo'<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_attendance_list&service_id='.$service->service_id.'">'.__('Attendance List for','church-admin').' '.$service->service_name.' '.__('on','church-admin').' '.$days[$service->service_day].' '.__('at','church-admin').' '.$service->service_time.'</a></p>';
   echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_attendance','edit_attendance').'">'.__('Add Attendance','church-admin').'</a></p>';
   echo'</div>';
   echo'<div class="clear"></div>';
}
?>