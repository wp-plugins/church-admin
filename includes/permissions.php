<?php

function church_admin_permissions()
{

	global $wpdb,$church_admin_version;
	
	
	echo' <div class="wrap" id="church-admin">
	<div id="icon-index" class="icon32"><br/></div><h2>Church Admin Plugin v'.$church_admin_version.'</h2>
	<div id="poststuff">';
	$check=$wpdb->get_var('SELECT COUNT(user_id) FROM '.CAP_PEO_TBL);
	if(empty($check))
	{
		echo'<div class="updated fade"><p><strong>'.__('Please create or connect Wordpress User accounts for people in teh directory first.','church-admin').'</strong></p></div>';
	}
	else
	{//proceed
		if(!empty($_POST['save']))
		{//form saved
			unset($_POST['save']);
			if(!empty($_POST['Directory'])){$user_permissions['Directory']=church_admin_get_user_id($_POST['Directory']);}
			if(!empty($_POST['Calendar']))$user_permissions['Calendar']=church_admin_get_user_id($_POST['Calendar']);
			if(!empty($_POST['Rota']))$user_permissions['Rota']=church_admin_get_user_id($_POST['Rota']);
			if(!empty($_POST['Sermons']))$user_permissions['Sermons']=church_admin_get_user_id($_POST['Sermons']);
			if(!empty($_POST['Funnel']))$user_permissions['Funnel']=church_admin_get_user_id($_POST['Funnel']);
			if(!empty($_POST['Bulk_SMS']))$user_permissions['Bulk SMS']=church_admin_get_user_id($_POST['Bulk_SMS']);
			if(!empty($_POST['Bulk_Email']))$user_permissions['Bulk Email']=church_admin_get_user_id($_POST['Bulk_Email']);
			if(!empty($_POST['Attendance']))$user_permissions['Attendance']=church_admin_get_user_id($_POST['Attendance']);
			if(!empty($_POST['Member_type']))$user_permissions['Member Type']=church_admin_get_user_id($_POST['Member_type']);
			if(!empty($_POST['small_groups']))$user_permissions['Small Groups']=church_admin_get_user_id($_POST['small_groups']);
			if(!empty($_POST['Service']))$user_permissions['Service']=church_admin_get_user_id($_POST['Service']);
			if(!empty($user_permissions))
			{//some people have been specified so save them	
			
				echo'<div class="updated fade"><p><strong>'.__('Permissions Saved','church-admin').'</strong></p></div>';
				update_option('church_admin_user_permissions',$user_permissions);
			}
			else
			{//no-one specified, make sure option is deleted
				delete_option('church_admin_user_permissions');
				echo'<div class="updated fade"><p>'.__('No individual user permissions are stored','church-admin').'</p></div>';
			}
		}//form saved
	
			$user_permissions=get_option('church_admin_user_permissions');
			if(empty($user_permissions['Directory']))$user_permissions['Directory']='';
			if(empty($user_permissions['Rota'])) $user_permissions['Rota']='';
			if(empty($user_permissions['Bulk SMS'])) $user_permissions['Bulk SMS']='';
			if(empty($user_permissions['Bulk Email'])) $user_permissions['Bulk Email'] ='';
			if(empty($user_permissions['Sermons'])) $user_permissions['Sermons'] = '';
			if(empty($user_permissions['Calendar'])) $user_permissions['Calendar'] = '';
			if(empty($user_permissions['Attendance'])) $user_permissions['Attendance'] ='';
			if(empty($user_permissions['Funnel'])) $user_permissions['Funnel']='';
			if(empty($user_permissions['Member Type'])) $user_permissions['Member Type'] ='';
			if(empty($user_permissions['Small Groups'])) $user_permissions['Small Groups'] ='';
			if(empty($user_permissions['Service'])) $user_permissions['Service'] = '';
		
			echo'<form action="" method="post">';
			echo'<h2>'.__('Who is allowed to do what?','church-admin').'</h2>';
			echo'<p><label>'.__('Directory','church-admin').'</label>';
			echo church_admin_autocomplete('Directory','Directory','dir',$user_permissions['Directory'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Rota','church-admin').'</label>';
			echo church_admin_autocomplete('Rota','Rota','ro',$user_permissions['Rota'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Bulk SMS','church-admin').'</label>';
			echo church_admin_autocomplete('Bulk SMS','bulk-sms','sms',$user_permissions['Bulk SMS'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Bulk Email','church-admin').'</label>';
			echo church_admin_autocomplete('Bulk Email','bulk-email','email',$user_permissions['Bulk Email'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Sermons','church-admin').'</label>';
			echo church_admin_autocomplete('Sermons','sermons','ser',$user_permissions['Sermons'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Calendar','church-admin').'</label>';
			echo church_admin_autocomplete('Calendar','calendar','cal',$user_permissions['Calendar'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Follow Up Funnels','church-admin').'</label>';
			echo church_admin_autocomplete('Funnel','funnel','funn',$user_permissions['Funnel'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Attendance','church-admin').'</label>';
			echo church_admin_autocomplete('Attendance','attendance','att',$user_permissions['Attendance'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Member Type','church-admin').'</label>';
			echo church_admin_autocomplete('Member_type','member-type','mt',$user_permissions['Member Type'],TRUE); 
			echo '</p>';
			echo'<p><label>'.__('Small groups','church-admin').'</label>';
			echo church_admin_autocomplete('small_groups','small_groups','sg',$user_permissions['Small Groups'],TRUE); 
			echo '</p>';		
			echo'<p><label>'.__('Service','church-admin').'</label>';
			echo church_admin_autocomplete('Service','service','att',$user_permissions['Service'],TRUE); 
			echo '</p>';
			echo'<p class="submit"><input type="hidden" name="save" value="yes"/><input type="submit" value="'.__('Save','church-admin').'" class="primary-button"/></p>';
			echo'</form></div></div>';
		}//end proceed
	

}//end function
?>
