<?php

function church_admin_frontend_directory($member_type_id=1,$map=NULL,$photo=NULL,$api_key=NULL)
{
	//updte 2014-04-16 to validate and contain microdata
	//update 2014-03-19 to allow for multiple surnames
  global $wpdb;
  $out='';
  $out.='<form name="ca_search" action="" method="POST"><p><label style="width:75px;float:left;">'.__('Search','church-admin').'</label><input name="ca_search" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/>';
  $out.='<input type="hidden" name="ca_search_nonce" value="'.wp_create_nonce('ca_search_nonce').'"/>';
  $out.='</p></form>';
  $memb=explode(',',$member_type_id);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql='('.implode(' || ',$membsql).')';}else{$memb_sql='';}
    if(empty($_POST['ca_search']))
    {
		$limit='';
		$membsql=array();
      
      $sql='SELECT household_id FROM '.CA_PEO_TBL.' WHERE '.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC ';
      $results=$wpdb->get_results($sql);
      $items=$wpdb->num_rows;
      // number of total rows in the database
      require_once(CHURCH_ADMIN_INCLUDE_PATH.'pagination.class.php');
      if($items > 0)
      {
	  $p = new pagination;
	  $p->items($items);
	  $p->limit(10); // Limit entries per page
	  
	  $p->target(get_permalink());
	  if(!isset($p->paging))$p->paging=1; 
	  if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
	  $p->currentPage($_GET[$p->paging]); // Gets and validates the current page
	  $p->calculate(); // Calculates what to show
	  $p->parameterName('paging');
	  $p->adjacents(1); //No. of page away from the current page
	  if(!isset($_GET['paging']))
	  {
	      $p->page = 1;
	  }
	  else
	  {
	      $p->page = $_GET['paging'];
	  }
	  //Query for limit paging
	  $limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
	  
	  
	  // Pagination
	$out.= '<div class="tablenav"><div class="tablenav-pages">';
        $out.= $p->getOutput();  
        $out.= '</div></div>';
      //Pagination
      }
      //grab household_id in last name order
      $sql='SELECT household_id FROM '.CA_PEO_TBL.' WHERE '.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC '.$limit;
      $results=$wpdb->get_results($sql);
    }
    else
    {//search form
      $s=esc_sql(stripslashes($_POST['ca_search']));
      $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE (first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%"))AND '.$memb_sql;
    
      $results=$wpdb->get_results($sql);
      if(!$results)
      {
        $sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%") AND '. $memb_sql;
        
	$results=$wpdb->get_results($sql);
      }
    }
  
  foreach($results AS $ordered_row)
  {
      $address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'"');
      $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'" ORDER BY people_order ASC, people_type_id ASC,sex DESC');
      $adults=$children=$emails=$mobiles=$photos=array();
	  $last_name='';
	  $x=0;
      foreach($people_results AS $people)
	{
		if($people->people_type_id=='1')
		{
			if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
			$last_name=$prefix.$people->last_name;
			$adults[$last_name][]=$people->first_name;
			if(!empty($people->email)&&$people->email!=end($emails)) $emails[$people->first_name]=$people->email;
			if(!empty($people->mobile)&&$people->mobile!=end($mobiles))$mobiles[$people->first_name]=$people->mobile;
			if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
			$x++;
		}
		else
		{
			$children[]=$people->first_name;
			if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
		}
	  
	}
	
  //create output
	array_filter($adults);$adultline=array();
	foreach($adults as $lastname=>$firstnames){$adultline[]=implode(" &amp; ",$firstnames).' '.$lastname;}
    $out .="\r\n". '<div class="church_admin_address" itemscope itemtype="http://schema.org/Person">'."\r\n\t".'<div class="church_admin_name_address" >'."\r\n\t\t".'<p><span itemprop="name"><strong>'.esc_html(implode(" &amp; ",$adultline)).'</strong></span>';
    if(!empty($children))$out.='<br />'.esc_html(implode(", ",$children)).'<br/>';
    $out.='</p>'."\r\n\t\t";
    if(!empty($address->address))
	{
		$out.='<p><span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.str_replace(', ',',<br/> ',$address->address).'</span></p>';
	}
	if(!empty($photos))
		{
			$images='';
			foreach($photos AS $key=>$value)
			{
				$attr=array('alt'=>$key,'title'=>$key);
				$images.='<a href="'.get_attachment_link($value).'">'.wp_get_attachment_image( $value, 'ca-people-thumb',0,$attr ).'</a>&nbsp;';
			}
			$out.='<p class="church_admin_photos">'.$images.'</p>';
		}
		$out.='</div><!--church_admin_name_address-->'."\r\n\t";
	
	$out.=	'<div class="church_admin_phone_email">'."\r\n\t\t".'<p>';
	if ($address->phone)$out.=' <a class="email" href="'.esc_url('tel:'.str_replace(' ','',$address->phone)).'">'.esc_html($address->phone)."</a><br/>\n\r\t\t";

    
    if (!empty($mobiles))
	{	
		array_unique($mobiles);
		if(count($mobiles)<2 && $x<=1)
		{
			$out.="\r\n\t\t".'<a class="email"  href="tel:'.str_replace(' ','',end($mobiles)).'"><span itemprop="telephone">'.esc_html(end($mobiles))."</span></a><br/>\n";
		}
		else
		{//more than one mobile in household
			foreach($mobiles AS $name=>$mobile)
			{
				if(!empty($mobile))$out.=$name.': <a class="email" href="tel:'.str_replace(' ','',$mobile).'"><span itemprop="telephone">'.esc_html($mobile)."</span></a><br/>\n";
			}
		}
	}
	    if (!empty($emails))
	{	
		array_unique($emails);
		if(count($emails)<2 && $x<=1)
		{
			$out.='<a class="email" itemprop="email" href="'.esc_url('mailto:'.end($emails)).'">'.esc_html(end($emails))."</a><br/>\n\r\t\t";
		}
		else
		{//more than one email in household
			foreach($emails AS $name=>$email)
			{
				if(!empty($email))$out.=$name.': <a class="email" itemprop="email" href="'.esc_url('mailto:'.$email).'">'.esc_html($email)."</a><br/>\n\r\t\t";
			}
		}
	}
	$out.='</p>'."\r\n\t".'</div><!--church_admin_phone_email-->';
	
    if(!empty($map)&&!empty($address->lng))
	{
		if(!empty($api_key))
		{
			$map_url='http://maps.google.com/maps/api/staticmap?center='.$address->lat.','.$address->lng.'&amp;zoom=15&amp;markers='.$address->lat.','.$address->lng.'&amp;size=200x200&amp;sensor=false&key='.$api_key;
		}
		else
		{
			$map_url='http://dummyimage.com/200x200/de21de/101017&amp;text=Please+add+a+Google+api+key+to+shortcode';
		}
	$out.="\r\n\t".'<div class="church_admin_address_map">'."\r\n\t\t".'<p><a href="http://maps.google.com/maps?q='.$address->lat.','.$address->lng.'&amp;t=m&amp;z=16"><img src="'.$map_url.'" height="200" width="200" alt="Map"/></a></p>'."\r\n\t";
    $out.='</div><!--church_admin_address_map-->'."\r\n\t";
    }
   
    $out.='<div class="church_admin_vcard" >'."\r\n\t\t".'<p>&nbsp;<a title="'.__('Edit Entry','church-admin').'" href="'.admin_url().'admin.php?page=church_admin/index.php&amp;action=church_admin_display_household&amp;household_id='.$ordered_row->household_id.'"><img src="'.CHURCH_ADMIN_IMAGES_URL.'user-edit-icon.png" width="32" height="32" alt="'.__('Edit Entry','church-admin').'"/></a><span><a title="'.__('Download Vcard','church-admin').'" href="'.home_url().'/?download=vcf&amp;vcf='.wp_create_nonce($ordered_row->household_id).'&amp;id='.$ordered_row->household_id.'"><img src="'.CHURCH_ADMIN_IMAGES_URL.'vcard-icon.png" width="32" height="32" alt="'.__('Download Vcard','church-admin').'"/></a></span>  <span style="float:right;">Updated '.human_time_diff( strtotime( $address->ts ) ).' ago</span></p>'."\r\n\t".'</div><!--church_admin_vcard-->'."\r\n".'</div><!--church_admin_address-->'."\r\n";
  }
  return $out;
}
?>