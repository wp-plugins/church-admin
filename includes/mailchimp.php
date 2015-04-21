<?php
//2014-02-24 fixed encoding error
function church_admin_mailchimp_sync()
{
	global $wpdb;
	$mailchimp_data=get_option('church_admin_mailchimp');
	echo'<h2>'.__('Mailchimp Integration','church-admin').'</h2>';
	if(!empty($_POST['api_key']))
	{
		echo'<div class="updated fade">Processing</div>';
		$ministries=get_option('church_admin_departments');
		$result=$wpdb->get_results('SELECT * FROM '.CA_MTY_TBL);
		$member_levels=array();
		foreach($result AS $row)$member_levels[$row->member_type_id]=$row->member_type;
		$result=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
		$hope_teams=array();
		foreach($result AS $row)$hope_teams[$row->hope_team_id]=$row->job;
		$api_key=sanitize_text_field(stripslashes($_POST['api_key']));
		$listID=sanitize_text_field(stripslashes($_POST['listID']));
		$MailChimp = new MailChimp($api_key);
		$mailchimp_data['api_key']=$api_key;
		//check for groupings
		$groupings=$MailChimp->call('lists/interest-groupings', array('id'=>intval($listID)));
		if(!empty($groupings))
		{
		
			foreach($groupings AS $grouping)
			{
				
				//check for member levels
				if(!empty($grouping['name'])&& $grouping['name']==translate('Member Levels','church-admin'))
				{
					$member_level_id=$grouping['id'];
					
				}
				//check for ministries
				if(!empty($grouping['name'])&&$grouping['name']==translate('Ministries','church-admin'))
				{
					$ministry_id=$grouping['id'];
					
				}
				//Check for hope team
				if(!empty($grouping['name'])&&$grouping['name']==translate('Hope Teams','church-admin'))
				{
					$hope_team_id=$grouping['id'];
					
				}
			}
		}
		if(empty($hope_team_id)) 
		{
			$mem_id=$MailChimp->call('lists/interest-grouping-add',array('id'=>$listID,'type'=>'checkboxes','groups'=>$hope_teams,'name'=>translate('Hope Teams','church-admin')));
			$hope_team_id=$mem_id['id'];
			echo'<p>Added "'.translate('Hope Teams','church-admin').'" grouping on Mailchimp</p>';
		}
		if(empty($member_level_id)) 
		{
			$mem_id=$MailChimp->call('lists/interest-grouping-add',array('id'=>$listID,'type'=>'checkboxes','groups'=>$member_levels,'name'=>translate('Member Levels','church-admin')));
			$member_level_id=$mem_id['id'];
			echo'<p>Added "'.esc_html(translate('Member Levels','church-admin')).'" grouping on Mailchimp</p>';
		}
		if(empty($ministry_id)) 
		{
			$min_id=$MailChimp->call('lists/interest-grouping-add',array('id'=>$listID,'type'=>'checkboxes','groups'=>$ministries,'name'=>translate('Ministries','church-admin')));
			$ministry_id=$min_id['id'];
			echo'<p>Added "'.translate('Ministries','church-admin').'" grouping on Mailchimp</p>';
		}
		
		update_option('church_admin_mailchimp',array('api_key'=>$api_key,'listID'=>intval($listID),'member_level_id'=>intval($member_level_id),'ministry_id'=>intval($ministry_id)));
		//check for groups within groupings! Grouping = member level or ministry, group = the various levels, ministries
		$groupings=$MailChimp->call('lists/interest-groupings', array('id'=>intval($listID)));
		if(!empty($groupings))
		{
			foreach($groupings AS $grouping)
			{
			
				$groups=$grouping['groups'];
				if(!empty($groups))
				{//check all are present
					$gp=array();
					//get all group names in array
					foreach($groups AS $group)
					{
						$gp[]=$group['name'];
					}
				}
				//member levels
				foreach($member_levels AS $id=>$type)
				{
					if(!in_array($type,$gp)&&$grouping['id']==$id)
					{
						$MailChimp->call('lists/interest-group-add', array('id'=>$listID,'grouping_id'=>$id,'group_name'=>$type));		
						echo'<p>Added "'.esc_html($type).'" group to Member Level grouping on Mailchimp</p>';
					}
				
				}	
			
				//ministries
			
				foreach($ministries AS $key=>$ministry)
				{
					if(!in_array($ministry,$gp)&&$grouping['id']==$ministry_id)
					{
						$MailChimp->call('lists/interest-group-add', array('id'=>$listID,'grouping_id'=>$ministry_id,'group_name'=>$ministry));
						echo'<p>Added "'.esc_html($ministry).'" group to Ministries grouping on Mailchimp</p>';
					}
				
				}

			}
		}
		else
		{//create groupings
			foreach($member_levels AS $id=>$type)
				{
					$MailChimp->call('lists/interest-group-add', array('id'=>$listID,'grouping_id'=>$id,'group_name'=>$type));		
					echo'<p>Added "'.esc_html($type).'" group to Member Level grouping on Mailchimp</p>';
					
				
				}
			foreach($ministries AS $key=>$ministry)
				{
					$MailChimp->call('lists/interest-group-add', array('id'=>$listID,'grouping_id'=>$ministry_id,'group_name'=>$ministry));
					echo'<p>Added "'.esc_html($ministry).'" group to Ministries grouping on Mailchimp</p>';
					
				
				}
		}
		echo'<div class="updated fade">Mailchimp groups created/updated</div>';
		
		$people=$wpdb->get_results('SELECT first_name,last_name, people_id,member_type_id, email FROM '.CA_PEO_TBL.' WHERE email!=""');
		if(!empty($people))
		{
			$peeps=array();
			foreach($people AS $person)
			{
				$memb_level=array('id'=>$member_level_id,'groups'=>array($member_levels[$person->member_type_id]));
				//grab ministries
				$result=$wpdb->get_results('SELECT department_id FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="ministry"');
				$mins=$min=array();
				if(!empty($result))
				{
						foreach($result AS $row)
						{
							if($row->department_id!=0)$min[]=$ministries[$row->department_id];
							
						}
				}
				if(!empty($min))$mins=array('id'=>$ministry_id,'groups'=>$min);
				$result=$wpdb->get_results('SELECT department_id FROM '.CA_MET_TBL.' WHERE people_id="'.$person->people_id.'" AND meta_type="hope_team"');
				$htjobs=$ht_jobs=array();
				if(!empty($result))
				{
						foreach($result AS $row)
						{
							if($row->department_id!=0)$ht_jobs[]=$hope_teams[$row->department_id];
							
						}
				}
				if(!empty($ht_jobs))$htjobs=array('id'=>$hope_team_id,'groups'=>$ht_jobs);
				if(is_email($person->email))
				{
					$groupings=array_filter(array($memb_level,$mins,$htjobs));
					
					$peeps[]=array('email'=>array('email'=>$person->email),'email_type'=>'html','merge_vars'=>array('FNAME'=>$person->first_name,'LNAME'=>$person->last_name,'GROUPINGS'=>$groupings));
				}
				
			}
			
			$subs=$MailChimp->call('lists/batch-subscribe',array('id'=>$listID,'batch'=>$peeps, 'double_optin'=>FALSE,'update_existing'=>TRUE,'replace_interests'=>TRUE));
			if(!empty($subs['errors']))
			{	
				echo'<h2>'.__('Here are the errors for that mailchimp sync','church-admin').'</h2>';
				foreach($subs['errors'] AS $errors)
				{
					echo'<p>'.esc_html($errors['error']).'</p>';
				}
			}
		}
		
		echo'<div class="updated fade">Mailchimp subscribers created/updated</div>';
		
	}
	else
	{
		
		echo '<p>'.__('Sync all your directory contacts, their ministries and member level to a given list in your Mailchimp account. You will need to enable an api key and get your list id','church-admin').'</p>';
		echo'<form action="" method="post">';
		echo'<p><label>'.__('Mailchimp API Key','church-admin').'</label><input type="text" name="api_key" ';
		if(!empty($mailchimp_data['api_key'])){echo ' value="'.esc_html($mailchimp_data['api_key']).'" ';}
		echo'/></p>';
		echo'<p><label>'.__('List ID','church-admin').'</label><input type="text" name="listID" ';
		if(!empty($mailchimp_data['listID'])){echo ' value="'.esc_html($mailchimp_data['listID']).'" ';}
		echo'/></p>';
		echo'<p><input type="submit" value="Save"/></p>';
	}

}

function church_admin_mailchimp_update($what,$data)
{
	$mailchimp_data=get_option('church_admin_mailchimp');
	$MailChimp = new MailChimp($mailchimp_data['api_key']);
	switch($what)
		{
			//$data=array('member_level_name');
			case 'member_level_delete':$result=$MailChimp->call('lists/interest-group-del',array('grouping_id'=>$mail_chimp_data['member_level_id'], 'id'=>$mail_chimp_data['listID'],'group_name'=>$data['member_level_name']));break;
			//$data=array('member_level_name');
			case 'member_level_add': $result=$MailChimp->call('lists/interest-group-add',array('grouping_id'=>$mail_chimp_data['member_level_id'], 'id'=>$mail_chimp_data['listID'],'group_name'=>$data['member_level_name']));break;
			//$data=array('member_level_old_name','member_level_new_name');
			case 'member_level_update': $result=$MailChimp->call('lists/interest-group-update',array('grouping_id'=>$mail_chimp_data['member_level_id'], 'id'=>$mail_chimp_data['listID'],'old_name'=>$data['member_level_old_name'],'new_name'=>$data['member_level_new_name']));break;
			//$data=array('ministry_name');
			case 'ministry_delete':$result=$MailChimp->call('lists/interest-group-del',array('grouping_id'=>$mail_chimp_data['ministry_id'], 'id'=>$mail_chimp_data['listID'],'group_name'=>$data['ministry_name']));break; 
			//$data=array('ministry_name');
			case 'ministry_add': $result=$MailChimp->call('lists/interest-group-add',array('grouping_id'=>$mail_chimp_data['ministry_id'], 'id'=>$mail_chimp_data['listID'],'group_name'=>$data['member_level_name']));break;
			//$data=array('ministry_old_name','ministry_new_name');
			case 'ministry_update': $result=$MailChimp->call('lists/interest-group-update',array('grouping_id'=>$mail_chimp_data['ministry_id'], 'id'=>$mail_chimp_data['listID'],'old_name'=>$data['ministry_old_name'],'new_name'=>$data['ministry_new_name']));break;
			case 'update_subscribers':break;
			case 'delete_subscriber':break;
			
		}
		print_r($result);
}


/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 * 
 * Uses curl if available, falls back to file_get_contents and HTTP stream.
 * This probably has more comments than code.
 *
 * Contributors:
 * Michael Minor <me@pixelbacon.com>
 * Lorna Jane Mitchell, github.com/lornajane
 * 
 * @author Drew McLellan <drew.mclellan@gmail.com> 
 * @version 1.1
 */
class MailChimp
{
	private $api_key;
	private $api_endpoint = 'https://<dc>.api.mailchimp.com/2.0';
	private $verify_ssl   = false;

	/**
	 * Create a new instance
	 * @param string $api_key Your MailChimp API key
	 */
	function __construct($api_key)
	{
		$this->api_key = $api_key;
		list(, $datacentre) = explode('-', $this->api_key);
		$this->api_endpoint = str_replace('<dc>', $datacentre, $this->api_endpoint);
	}


	/**
	 * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
	 * @param  string $method The API method to call, e.g. 'lists/list'
	 * @param  array  $args   An array of arguments to pass to the method. Will be json-encoded for you.
	 * @return array          Associative array of json decoded API response.
	 */
	public function call($method, $args=array())
	{
		return $this->_raw_request($method, $args);
	}


	/**
	 * Performs the underlying HTTP request. Not very exciting
	 * @param  string $method The API method to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 * @return array          Assoc array of decoded result
	 */
	private function _raw_request($method, $args=array())
	{      
		$args['apikey'] = $this->api_key;

		$url = $this->api_endpoint.'/'.$method.'.json';

		if (function_exists('curl_init') && function_exists('curl_setopt')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');		
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
			$result = curl_exec($ch);
			curl_close($ch);
		}else{
			$json_data = json_encode($args);
			$result = file_get_contents($url, null, stream_context_create(array(
			    'http' => array(
			        'protocol_version' => 1.1,
			        'user_agent'       => 'PHP-MCAPI/2.0',
			        'method'           => 'POST',
			        'header'           => "Content-type: application/json\r\n".
			                              "Connection: close\r\n" .
			                              "Content-length: " . strlen($json_data) . "\r\n",
			        'content'          => $json_data,
			    ),
			)));
		}

		return $result ? json_decode($result, true) : false;
	}

}
?>