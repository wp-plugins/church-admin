<?php



function church_admin_permissions()

{



	global $wpdb,$church_admin_version;

	

	

	echo' <div class="wrap" id="church-admin">

	<div id="icon-index" class="icon32"><br/></div><h2>Church Admin Plugin v'.$church_admin_version.'</h2>

	<div id="poststuff">';

	$check=$wpdb->get_var('SELECT COUNT(user_id) FROM '.CA_PEO_TBL);

	if(empty($check))

	{

		echo'<div class="updated fade"><p><strong>'.__('Please create or connect Wordpress User accounts for people in the directory first.','church-admin').'</strong></p></div>';

	}
	if(!empty($_POST['save']))

		{//form saved

			unset($_POST['save']);
			if(!empty($_POST['delete_all'])){delete_option('church_admin_user_permissions');echo'<div class="updated fade"><p>'.__('No individual user permissions are stored','church-admin').'</p></div>';}
			else
			{
				if(!empty($_POST['Directory']))
				{
					$directory=church_admin_get_user_id($_POST['Directory']);
					if(!empty($directory))$user_permissions['Directory']=$directory;
				}

				if(!empty($_POST['Calendar']))
				{	
					$calendar=church_admin_get_user_id($_POST['Calendar']);
					if(!empty($calendar))$user_permissions['Calendar']=$calendar;
				}

				if(!empty($_POST['Rota']))
				{
					$rota=church_admin_get_user_id($_POST['Rota']);
					if(!empty($rota))$user_permissions['Rota']=$rota;
				}
				if(!empty($_POST['Sermons']))
				{
					$sermons=church_admin_get_user_id($_POST['Sermons']);
					if(!empty($sermons))$user_permissions['Sermons']=$sermons;
				}

				if(!empty($_POST['Funnel']))
				{
					$funnel=church_admin_get_user_id($_POST['Funnel']);
					if(!empty($funnel))$user_permissions['Funnel']=$funnel;
				}

				if(!empty($_POST['Bulk_SMS']))
				{
					$sms=church_admin_get_user_id($_POST['Funnel']);
					if(!empty($sms))$user_permissions['Bulk SMS']=$sms;
				}

				if(!empty($_POST['Bulk_Email']))
				{
					$email=church_admin_get_user_id($_POST['Funnel']);
					if(!empty($email))$user_permissions['Bulk Email']=$email;
				}
				if(!empty($_POST['Attendance']))
				{
					$att=church_admin_get_user_id($_POST['Attendance']);
					if(!empty($att))$user_permissions['Attendance']=$att;
				}
				if(!empty($_POST['Member_type']))
				{
					$mt=church_admin_get_user_id($_POST['Member_type']);				
					if(!empty($mt))$user_permissions['Member Type']=$mt;
				}
				if(!empty($_POST['small_groups']))
				{
					$sg=church_admin_get_user_id($_POST['small_groups']);
					if(!empty($sg))$user_permissions['Small Groups']=$sg;
				}
				if(!empty($_POST['Service']))
				{
					$service=church_admin_get_user_id($_POST['Service']);
					if(!empty($service))$user_permissions['Service']=$service;
				}
				if(!empty($_POST['Prayer_Chain']))
				{
					$Prayer_Chain=church_admin_get_user_id($_POST['Prayer_Chain']);
					if(!empty($Prayer_Chain))$user_permissions['Prayer Chain']=$Prayer_Chain;
				}
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
			if(empty($user_permissions['Prayer Chain'])) $user_permissions['Prayer Chain'] = '';
		

			echo'<form action="" method="post">';

			echo'<h2>'.__('Who is allowed to do what?','church-admin').'</h2>';
			echo'<p><label>Delete All user permissions</label><input type="checkbox" class="delete_all_permissions" value="yes" name="delete_all"/></p>';

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

			echo church_admin_autocomplete('Service','service','ser',$user_permissions['Service'],TRUE); 

			echo '</p>';
			echo'<p><label>'.__('Prayer Chain','church-admin').'</label>';

			echo church_admin_autocomplete('Prayer_Chain','prayer-chain','pc',$user_permissions['Prayer Chain'],TRUE); 

			echo '</p>';
			echo'<p class="submit"><input type="hidden" name="save" value="yes"/><input type="submit" value="'.__('Save','church-admin').'" class="primary-button"/></p>';

			echo'</form></div></div>';
			echo'<script type="text/javascript">jQuery(document).ready(function($) {
			$(".delete_all_permissions").click(function(){ 
				$(".to").val("");
				
			});
});</script>';


	



}//end function

?>