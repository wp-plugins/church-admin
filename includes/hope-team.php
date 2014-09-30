<?php

function church_admin_hope_team_jobs()
{
	global $wpdb;
	$out='<h2>'.__('Hope Team Jobs','church-admin').'</h2>';
	$out.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_hope_team_job','hope_team_jobs').'">Add a job</a></p>';
	$results=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
	if(!empty($results))
	{	
		
		$out.='<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Job Title</th></tr></thead><tbody>';
		foreach($results AS $row)
		{
		$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_hope_team_job&amp;id='.$row->hope_team_id,'hope_team_jobs').'">Edit</a>';
		$delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_hope_team_job&amp;id='.$row->hope_team_id,'delete_hope_team_job').'">Delete</a>';
		$out.='<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$row->job.'</td></tr>';
		}
		$out.='</tbody></table>';
	}
	echo $out;

}
function church_admin_delete_hope_team_job($id=NULL)
{
	global $wpdb;
	$wpdb->query('DELETE FROM '.CA_HOP_TBL.' WHERE hope_team_id="'.esc_sql($id).'"');
	echo'<div class="updated fade">Job Deleted</div>';
		church_admin_hope_team_jobs();
}
function church_admin_edit_hope_team_job($id=NULL)
{
	global $wpdb;
	echo'<h2>Edit Hope Team Job</h2>';
	if($id){$job=$wpdb->get_var('SELECT job FROM '.CA_HOP_TBL.' WHERE hope_team_id="'.esc_sql($id).'"');}else{$job='';}
	if(!empty($_POST['save_job']))
	{
		if(empty($id))$id=$wpdb->get_var('SELECT hope_team_id FROM '.CA_HOP_TBL.' WHERE job="'.esc_sql($_POST['job']).'"');
		if(empty($id))
		{
			$wpdb->query('INSERT INTO '.CA_HOP_TBL.' (job) VALUES("'.esc_sql($_POST['job']).'")');
		}
		else
		{
			$wpdb->query('UPDATE '.CA_HOP_TBL.' SET job="'.esc_sql($_POST['job']).'"  WHERE hope_team_id="'.esc_sql($id).'"');
		}
		echo'<div class="updated fade">Job saved</div>';
		church_admin_hope_team_jobs();
	}
	else
	{
		echo'<form action="" method="POST">';
	    echo'<p><label>'.__('Job Name?','church-admin').'</label><input type="text" name="job" ';
		if(!empty($job)) echo 'value="'.$job.'" ';
		echo'/></p>';
		 echo'<p class="submit"><input type="hidden" name="save_job" value="yes"/><input type="submit" name="save" value="'.__('Save','church-admin').' &raquo;" /></p></form></div>';
	}

}

function church_admin_edit_hope_team($people_id)
{
	global $wpdb;
	echo'<h2>Edit Hope Team Personnel</h2>';
	if(!empty($_POST['save_person']))
	{
		
		$people_ids=maybe_unserialize(church_admin_get_people_id($_POST['who']));
		if(!empty($people_ids))
		{
			foreach($people_ids AS $person_id)
			{
				$wpdb->query('DELETE FROM '.CA_MET_TBL.' WHERE meta_type="hope_team" AND people_id="'.esc_sql($person_id).'"');
				$jobs=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
				foreach($jobs AS $job)
				{
				
					if(!empty($_POST['job-'.$job->hope_team_id])) 
					{
						$sql='INSERT INTO '.CA_MET_TBL.' (meta_type,people_id,department_id)VALUES("hope_team","'.esc_sql($person_id).'","'.esc_sql($job->hope_team_id).'")';
						
						$wpdb->query($sql);
					}
				}
				
					$wpdb->query('UPDATE '.CA_PEO_TBL.' SET other_hope_team="'.esc_sql(stripslashes($_POST['other'])).'" WHERE people_id="'.esc_sql($people_id).'"');
				
			}
			echo'<div class="updated fade"><p><strong>Hope Team edits saved</strong></p></div>';
		}
		
	}
	
		echo'<div id="church_admin"><form action="" method="POST">';
	    echo'<p><label>'.__('Who?','church-admin').'</label>';
		echo church_admin_autocomplete("who",'friends','to',church_admin_get_people($people_id),FALSE);
		$jobs=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
		foreach($jobs AS $job)
		{
			 echo'<p><label>'.$job->job.'</label><input type=checkbox name="job-'.$job->hope_team_id.'"  value="yes"/></p>';
		}
		echo'<p><label>Anything else</label><textarea name="other">';
		echo'</textarea></p>';
		 echo'<p class="submit"><input type="hidden" name="save_person" value="yes"/><input type="submit" name="choose_service" value="'.__('Save','church-admin').' &raquo;" /></p></form></div>';
	
	
	
	
}

?>