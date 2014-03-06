<?php

function church_admin_frontend_directory($member_type_id=1,$map=NULL,$photo=NULL)
{
  global $wpdb;
  $out='';
  $out.='<p><label style="width:75px;float:left;">'.__('Search','church-admin').'</label><form name="ca_search" action="" method="POST"><input name="ca_search" type="text"/><input type="submit" value="'.__('Go','church-admin').'"/></form></p>';
    if(empty($_POST['ca_search']))
    {
		$limit='';
		$membsql=array();
      $memb=explode(',',$member_type_id);
      foreach($memb AS $key=>$value){if(ctype_digit($value))  $membsql[]='member_type_id='.$value;}
      if(!empty($membsql)) {$memb_sql=' WHERE ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
      $sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC ';
      $results=$wpdb->get_results($sql);
      $items=$wpdb->num_rows;
      // number of total rows in the database
      require_once(CHURCH_ADMIN_INCLUDE_PATH.'pagination.class.php');
      if($items > 0)
      {
	  $p = new pagination;
	  $p->items($items);
	  $p->limit(10); // Limit entries per page
	  
	  $p->target($_SERVER['REQUEST_URI']);
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
      $sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC '.$limit;
      $results=$wpdb->get_results($sql);
    }
    else
    {
      $s=esc_sql(stripslashes($_POST['ca_search']));
      $sql='SELECT DISTINCT household_id FROM '.CA_PEO_TBL.' WHERE first_name LIKE("%'.$s.'%")||last_name LIKE("%'.$s.'%")||email LIKE("%'.$s.'%")';
    
      $results=$wpdb->get_results($sql);
      if(!$results)
      {
        $sql='SELECT DISTINCT household_id FROM '.CA_HOU_TBL.' WHERE address LIKE("%'.$s.'%")||phone LIKE("%'.$s.'%")';
        
	$results=$wpdb->get_results($sql);
      }
    }
  
  foreach($results AS $ordered_row)
  {
      $address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'"');
      $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
      $adults=$children=$emails=$mobiles=$photos=array();
      foreach($people_results AS $people)
	{
	  if($people->people_type_id=='1')
	  {
		  if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
	    $last_name=$prefix.$people->last_name;
	    $adults[]=$people->first_name;
	    if($people->email!=end($emails)) $emails[]=$people->email;
	    if($people->mobile!=end($mobiles))$mobiles[]=$people->mobile;
	    if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
	  }
	  else
	  {
	    $children[]=$people->first_name;
	    if(!empty($people->attachment_id))$photos[$people->first_name]=$people->attachment_id;
	  }
	  
	}
  //create output
  
    $out .= '<div class="church_admin_address"><div style="width:49%; float:left"><div style="clear:both;"></div><div style="margin-bottom: 10px;"><span style="font-size:larger;font-variant: small-caps"><strong>'.esc_html(implode(" &amp; ",$adults)).' '.esc_html($last_name).'</strong></span><br />';
    if(!empty($children))$out.=esc_html(implode(", ",$children)).'<br/>';
    $out.='</div>';
    if(!empty($address->address)){$out.=str_replace(', ',',<br/> ',$address->address);//implode(",<br/> ",array_filter(unserialize($address->address)));
	if(!empty($photos))
    {
		$images='';
		foreach($photos AS $key=>$value)
		{
				$attr=array('alt'=>$key,'title'=>$key);
				$images.='<a href="'.get_attachment_link($value).'">'.wp_get_attachment_image( $value, 'ca-people-thumb',0,$attr ).'</a>&nbsp;';
		}
		$out.='<p >'.$images.'</p>';
	}
	$out.='</div>';
	$out.=	'<div align="right">';}
    if (!empty($emails))
    foreach($emails AS $email)
    {
      $out.='<a class="email" href="'.esc_url('mailto:'.$email).'">'.esc_html($email)."</a><br/>\n";
    }
    if ($address->phone)$out.=esc_html($address->phone)."<br />\n";
    if (!empty($mobiles))
    foreach($mobiles AS $mobile)
    {
      $out.=esc_html($mobile)."<br/>\n";
    }
    if(!empty($map)&&!empty($address->lng)){$out.='<a href="http://maps.google.com/maps?q='.$address->lat.','.$address->lng.'&t=m&z=16"><img src="http://maps.google.com/maps/api/staticmap?center='.$address->lat.','.$address->lng.'&zoom=15&markers='.$address->lat.','.$address->lng.'&size=200x200&sensor=false" height="200px" width="200px"/></a>';}
    $out.='</div>';
    	
    $out.='<div style="clear:both"></div>';
    $out.='<div class="cn-meta" align="left" style="margin-top: 6px"><span><a href="'.home_url().'/?download=vcf&amp;vcf='.wp_create_nonce('vcf').'&amp;id='.$ordered_row->household_id.'">V-Card</a></span>'.        '  <span style="font-size:x-small; font-variant: small-caps; position: absolute; right: 26px; bottom: 8px;">Updated '.human_time_diff( strtotime( $address->ts ) ).' ago</span></div></div>';
  }
  return $out;
}
?>
