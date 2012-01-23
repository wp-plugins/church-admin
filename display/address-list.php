<?php
nocache_headers();
$wpdb->show_errors();
//standard full listing
    $sql = "SELECT * FROM ".$wpdb->prefix."church_admin_directory ORDER BY last_name, first_name";
$counter=1; //the identifier for each record for the vcard and pdf
    $results = $wpdb->get_results($sql);    
	
	foreach ($results as $row) {
	
    	  $age = (int) abs( time() - strtotime( $row->ts ) );

    	  if ( $age < 657000 )	// less than one week: red

    	  		$ageStyle = "color:red";

    	  elseif ( $age < 1314000 )	// one-two weeks: maroon

    	  		$ageStyle = "color:maroon";

    	  elseif ( $age < 2628000 )	// two weeks to one month: green

    	  		$ageStyle = "color:green";

    	  elseif ( $age < 7884000 )	// one - three months: blue

    	  		$ageStyle = "color:blue";

    	  elseif ( $age < 15768000 )	// three to six months: navy

    	  		$ageStyle = "color:navy";

    	  elseif ( $age < 31536000 )	// six months to a year: black

    	  		$ageStyle = "color:black";

    	  else								// more than one year: don't show the update age

    	  		$ageStyle = "display:none";

    	  if (strlen($row->cellphone) > 0 )

    	  		$cell = "Cell: ";

    	  else

    	  		$cell = "";



        $out .= '<div  class="church_admin_address">
	<div style="width:49%; float:left">
		<div style="clear:both;"></div>
		<div style="margin-bottom: 10px;">
			<span style="font-size:larger;font-variant: small-caps"><strong>'.esc_html($row->first_name)." ".esc_html($row->last_name).'</strong></span><br />';
if($row->children)$out.=esc_html($row->children).'<br/>';
$out.='</div>'.
esc_html(stripslashes($row->address_line1))."<br />\n";
if ($row->address_line2) $out .= esc_html(stripslashes($row->address_line2))."<br />\n";
$out .= esc_html(stripslashes($row->city)).",\n".esc_html(stripslashes($row->state))."<br/>\n".esc_html(stripslashes($row->zipcode))."\n".
'</div><div align="right">';
if ($row->email) $out.='Email:<a class="email" href="'.clean_url('mailto:'.$row->email).'">'.esc_html($row->email)."</a><br/>\n";
if ($row->email2) $out.='Email:<a class="email" href="'.clean_url('mailto:'.$row->email2).'">'.esc_html($row->email2)."</a><br />\n";
if ($row->website)  $out.="Website:<a target='_blank' href='".clean_url('http://'.$row->website)."'>".esc_html($row->website)."</a><br />\n";
if ($row->homephone)$out.="Phone:".esc_html($row->homephone)."<br />\n";
if ($row->cellphone)$out.="Mobile:".esc_html($row->cellphone)."<br />\n";

$out.='</div>	
		<div style="clear:both"></div>
	<div class="cn-meta" align="left" style="margin-top: 6px">
		<span><a href="'.home_url().'/?download=vcf&amp;id='.$row->id.'">V-Card</a></span>'.
        '  <span style="'.$ageStyle.' font-size:x-small; font-variant: small-caps; position: absolute; right: 26px; bottom: 8px;">Updated '.human_time_diff( strtotime( $row->ts ) ).' ago</span><br />'.

       '<br />
	</div>
	
</div>';
$counter++;
    }
$_SESSION[address]=$counter;
$out .= "\n";
?>