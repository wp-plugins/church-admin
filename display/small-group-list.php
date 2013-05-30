<?php
function church_admin_small_group_list($map=1)
{
	global $wpdb;
	$wpdb->show_errors();
	//show small groups 
	
	$out='';
	
		$row=$wpdb->get_row('SELECT AVG(lat) AS lat,AVG(lng) AS lng FROM '.CA_SMG_TBL);
		if(!empty($row)&& $map==1)
		{
			
			$out.='<script type="text/javascript">var xml_url="'.site_url().'/?download=small-group-xml";';
			$out.=' var lat='.$row->lat.';';
			$out.=' var lng='.$row->lng.';';
			$out.='jQuery(document).ready(function(){load(lat,lng,xml_url);});</script><div id="map"></div><div id="groups" ></div>';
		}
		else
		{//old way for non geolocated
			$leader=array();
			$sql='SELECT * FROM '.CA_SMG_TBL;
			$results = $wpdb->get_results($sql);    
			if(!empty($results))foreach ($results as $row) {$out.='<p><strong>'.esc_html($row->group_name).'</strong>: '.$row->whenwhere.' '.$row->address.'</p>';}
		}//end old way for non geolocated
	return $out;
}
?>	
