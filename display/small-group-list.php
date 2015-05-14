<?php
function church_admin_small_group_list($map=1)
{
	global $wpdb;
	
	//show small groups 
	
	$out='';
	
		$row=$wpdb->get_row('SELECT AVG(lat) AS lat,AVG(lng) AS lng FROM '.CA_SER_TBL);
		if(!empty($row)&& $map==1)
		{
			
			$out.='<script type="text/javascript">var xml_url="'.site_url().'/?download=small-group-xml&small-group-xml='.wp_create_nonce('small-group-xml').'";';
			$out.=' var lat='.esc_html($row->lat).';';
			$out.=' var lng='.esc_html($row->lng).';';
			$out.='jQuery(document).ready(function(){sgload(lat,lng,xml_url);});</script><div id="map"></div><div id="groups" ></div><div class="clear"></div>';
		}
		else
		{//old way for non geolocated
			$leader=array();
			$sql='SELECT * FROM '.CA_SMG_TBL;
			$results = $wpdb->get_results($sql);    
			if(!empty($results))foreach ($results as $row) {$out.='<p><strong>'.esc_html($row->group_name).'</strong>: '.esc_html($row->whenwhere).' '.esc_html($row->address).'</p>';}
		}//end old way for non geolocated
	return $out;
}
?>