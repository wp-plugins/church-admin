<?php

function church_admin_cron_pdf()
{
    //setup pdf
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
    $pdf=new FPDF();
    $pdf->AddPage('P','A4');
    $pdf->SetFont('Arial','B',24);
    $text=__('How to set up Bulk Email Queuing','church-admin');
    $pdf->Cell(0,10,$text,0,2,'L');
    if (PHP_OS=='Linux')
    {
    $phppath='/usr/local/bin/php -f ';
    $cronpath=plugin_dir_path(dirname(__FILE__)).'includes/cronemail.php';
    $command=$phppath.$cronpath;
    
    
    $pdf->SetFont('Arial','',10);
    $text="Instructions for Linux servers and cpanel.\r\nLog into Cpanel which should be ".get_bloginfo('url')."/cpanel using your username and password. \r\nOne of the options will be Cron Jobs which is usually in 'Advanced Tools' at the bottom of the screen. Click on 'Standard' Experience level. that will bring up something like this... ";
    
    $pdf->MultiCell(0, 10, $text );
 
    $pdf->Image(plugin_dir_path( dirname(__FILE__) ).'images/cron-job1.jpg','10','65','','','jpg','');
    $pdf->SetXY(10,180);
    $text="In the common settings option - select 'Once an Hour'. \r\nIn 'Command to run' put this:\r\n".$command."\r\n and then click Add Cron Job. Job Done. Don't forget to test it by sending an email to yourself at a few minutes before the hour! ";
    $pdf->MultiCell(0, 10, $text );
    }
    else
    {
         $pdf->SetFont('Arial','',10);
        $text=__("Unfortunately setting up queuing for email using cron is not possible in Windows servers. Please go back to Communication settings and enable the wp-cron option for scheduling sending of queued emails",'church-admin');
        $pdf->MultiCell(0, 10, $text );
    }
    $pdf->Output();
    

}

function church_admin_smallgroup_pdf($member_type_id)
{
    global $wpdb,$member_type;
require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
//cache small group pdf
$wpdb->show_errors();
$smallgroups=$groupnames=array();
$count=0;
$leader=array();
//get groups
$results=$wpdb->get_results('SELECT group_name,id FROM '.CA_SMG_TBL);
if(!empty($results))
{
	foreach($results AS $row){$smallgroups[$row->id]=array('regular'=>array(),'irregular'=>array(),'connected'=>array());$groupnames[$row->id]=iconv('UTF-8', 'ISO-8859-1',$row->group_name);}
	
	//grab people
	if(!empty($member_type_id)&&!is_array($member_type_id)){$memb=explode(',',esc_sql($member_type_id));}else{$memb=$member_type_id;}
	foreach($memb AS $key=>$value){if(ctype_digit($value))$membsql[]='member_type_id='.$value;}
	if(!empty($membsql)) {$memb_sql='WHERE   ('.implode(' || ',$membsql).')';}else{$memb_sql='';}
	$sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,smallgroup_attendance,smallgroup_id FROM '.CA_PEO_TBL.'  '.$memb_sql.'  ORDER BY smallgroup_attendance,last_name ';
	$peopleresults = $wpdb->get_results($sql);
	
	
	$gp=0;
	
	$person=1;
	foreach ($peopleresults as $people) 
    {
		
        $people->name=stripslashes($people->name);
		$sg=maybe_unserialize($people->smallgroup_id);
		if(!is_array($sg))$sg=array('0'=>$sg);
		$person_gp_count=count($sg);
		if($person_gp_count>1){$count++;}
		foreach($sg AS $key=>$sg_id)
		{
			if($person_gp_count==1 && !empty($sg_id))$count++;//only increment count if person is one existing group
				switch ($people->smallgroup_attendance)
				{
					case'1':$smallgroups[$sg_id]['regular'][]=iconv('UTF-8', 'ISO-8859-1',$people->name);break;
					case'2':$smallgroups[$sg_id]['irregular'][]=iconv('UTF-8', 'ISO-8859-1',$people->name);break;
					case'3':$smallgroups[$sg_id]['connected'][]=iconv('UTF-8', 'ISO-8859-1',$people->name);break;
					default:$smallgroups[$sg_id]['regular'][]=iconv('UTF-8', 'ISO-8859-1',$people->name);break;
				}
			
		}
       
    }
	
	$noofgroups=$wpdb->get_row('SELECT COUNT(id) AS no FROM '.CA_SMG_TBL);
	$counter=$noofgroups->no;

	$pdf=new FPDF();
	$pageno=0;
	$x=10;
	$y=20;
	$w=1;
	$width=55;
	$pdf->AddPage('L',get_option('church_admin_pdf_size'));
	$pdf->SetFont('Arial','B',16);
	
	$whichtype=array();
	foreach($memb AS $key=>$value)$whichtype[]=$member_type[$value];
	$text=implode(", ",$whichtype).' '.__('Small Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
	$pdf->Cell(0,10,$text,0,2,'C');
	$pageno+=1;



	foreach($groupnames AS $z=>$groupname)
	{
		$text='';
		if($w==6)
		{
			$pdf->SetFont('Arial','B',16);
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			
			$whichtype=array();
			foreach($memb AS $key=>$value)$whichtype[]=$member_type[$value];
			$text=implode(", ",$whichtype).' '.__('Small Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
			$pdf->Cell(0,10,$text,0,2,'C');
			$x=10;
			$y=20;
			$w=1;
		}
		$newx=$x+(($w-1)*$width);
		if($pageno>1) {$newx=$x+(($z-($pageno*5))*$width);}
		$pdf->SetXY($newx,$y);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell($width,8,iconv('UTF-8', 'ISO-8859-1',$groupname),1,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($newx,$y+8);
		if(!empty($smallgroups[$z]['regular']))
		{
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell($width,5,__('Regular','church-admin')."\n",'LR',2,'L');
			$pdf->SetFont('Arial','',10);
			$text='';
			for($a=0;$a<count($smallgroups[$z]['regular']);$a++)
			{
				$b=$a+1;
				$text.=$b.') '.$smallgroups[$z]['regular'][$a]."\n";
			}
			$pdf->MultiCell($width,5,$text."\n",'LR');
			
			$pdf->SetX($newx);
		}
		if(!empty($smallgroups[$z]['irregular']))
		{
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell($width,5,__('Irregular','church-admin')."\n",'LR',2,'L');
			$pdf->SetFont('Arial','',10);
			$text='';
			for($a=0;$a<count($smallgroups[$z]['irregular']);$a++)
			{
				$b=$a+1;
				$text.=$b.') '.$smallgroups[$z]['irregular'][$a]."\n";
			}
			$pdf->MultiCell($width,5,$text."\n",'LR');
			
		
			$pdf->SetX($newx);
		}
		if(!empty($smallgroups[$z]['connected']))
		{
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell($width,5,__('Connected','church-admin')."\n",'LR',2,'L');
			$pdf->SetFont('Arial','',10);
			$text='';
			for($a=0;$a<count($smallgroups[$z]['connected']);$a++)
			{
				$b=$a+1;
				$text.=$b.') '.$smallgroups[$z]['connected'][$a]."\n";
			}
			$pdf->MultiCell($width,5,$text."\n",'LR');
			
			
			$pdf->SetX($newx);
		}
		$pdf->Cell($width,0,"",'LB',2,'L');
		$w++;
	}
	$pdf->Output();
}
}


function church_admin_address_pdf($member_type_id=1)
{

//update 2014-03-19 to allow for multiple surnames
	//initilaise pdf
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	//Title
	$pdf=new FPDF();
	$pdf->SetAutoPageBreak(1,10);
	$pdf->AddPage('P',get_option('church_admin_pdf_size'));
	$pdf->SetXY(10,10);
	$pdf->SetFont('Arial','B',18);
	$title=get_option('blogname').' '.__('Address List','church-admin').' '.date(get_option('date_format'));
	$pdf->Cell(0,8,$title,0,1,'C');
	$pdf->Ln(5);

  global $wpdb;
//address book cache

$memb=explode(',',esc_sql($member_type_id));
foreach($memb AS $key=>$value){if(ctype_digit($value)) $membsql[]='member_type_id='.$value;}
if(!empty($membsql)) {$memb_sql=' WHERE '.implode(' || ',$membsql);}else{$memb_sql='';}
//grab addresses
$sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC ';
  $results=$wpdb->get_results($sql);

  $counter=1;
    $addresses=array();
	foreach($results AS $ordered_row)
	{
		$y=$pdf->GetY();
		$h=$pdf->h;
		
		if($h-35<=$y) 
		{
			$pdf->AddPage('P',get_option('church_admin_pdf_size'));
			$pdf->SetXY(10,10);
			$pdf->SetFont('Arial','B',18);
			$title=get_option('blogname').' '.__('Address List','church-admin').' '.date(get_option('date_format'));
			$pdf->Cell(0,8,$title,0,1,'C');
			$pdf->Ln(5);
		}
		$address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'"');
		$people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($ordered_row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
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
		foreach($adults as $lastname=>$firstnames){$adultline[]=implode(" & ",$firstnames).' '.$lastname;}
		//address name of adults in household
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(" & ",$adultline)),0,1,'L');
		$pdf->SetFont('Arial','',10);
		//children
		if(!empty($children))$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(", ",$children)),0,1,'L');
		//address if stored
		if(!empty($address->address)){$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',$address->address),0,1,'L');}
		//emails
		if (!empty($emails))
		{	
			array_unique($emails);
			if(count($emails)<2 && $x<=1)
			{
				$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',esc_html(end($emails))),0,1,'L',FALSE,'mailto:'.end($emails));
			}
			else
			{//more than one email in household
				$text=array();
				foreach($emails AS $name=>$email)
				{
					$content=$name.': '.$email;
					if($email!=end($emails))$content.=', ';
					$width=$pdf->GetStringWidth($content);
					$pdf->Cell($width,5,iconv('UTF-8', 'ISO-8859-1',$content),0,0,'L',FALSE,'mailto:'.$email);
					
				}
				$pdf->Ln();
				//$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',implode(', ',$text)),0,1,'L',FALSE,'mailto:'.end($emails));
			}
		}
		if ($address->phone)$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',$address->phone),0,1,'L',FALSE,'tel:'.$address->phone);
		if (!empty($mobiles))
		{	
			array_unique($mobiles);
			if(count($mobiles)<2 && $x<=1)
			{
				$pdf->Cell(0,5,iconv('UTF-8', 'ISO-8859-1',esc_html(end($mobiles))),0,0,'L',FALSE,'tel:'.end($mobiles));
			}
			else
			{//more than one mobile in household
				$text=array();
				foreach($mobiles AS $name=>$mobile)
				{
					$content=$name.': '.$mobile;
					if($mobile!=end($mobiles))$content.=', ';
					$width=$pdf->GetStringWidth($content);
					$pdf->Cell($width,5,iconv('UTF-8', 'ISO-8859-1',$content),0,0,'L',FALSE,'tel:'.$mobile);
				}
				
			}
			$pdf->Ln(5);
		}
	$pdf->Ln(5);
    }
   
 
$pdf->Output();


//end of cache address list
}
function church_admin_people_csv($member_type_id=1,$people_type_id=1,$sex=1,$add=0,$sg=1)
{
	
	global $wpdb;
	$wpdb->show_errors();
	if(!empty($add))$address=' LEFT JOIN '.CA_HOU_TBL.' ON '.CA_PEO_TBL.'.household_id='.CA_HOU_TBL.'.household_id ';
	if(!empty($sg))$sg=' LEFT JOIN '.CA_SMG_TBL.' ON '.CA_PEO_TBL.'.smallgroup_id='.CA_SMG_TBL.'.id ';
	if(!empty($sex))foreach($sex AS $key=>$value)$gender[]=CA_PEO_TBL.'.sex="'.$value.'"';
	if(!empty($gender)){$genders=' WHERE ('.implode(' || ',$gender).') ';}else{$genders=' WHERE  ('.CA_PEO_TBL.'.sex=1 || '.CA_PEO_TBL.'.sex =0) ';}

	
	if(!empty($people_type_id))foreach($people_type_id AS $key=>$value){if(ctype_digit($value))$peoplesql[]=CA_PEO_TBL.'.people_type_id='.$value;}
	if(!empty($peoplesql)) {$people_sql=' AND ('.implode(' || ',$peoplesql).') ';}else{$people_sql='';}
	
	if(!empty($member_type_id))foreach($member_type_id AS $key=>$value){if(ctype_digit($value))$membsql[]=CA_PEO_TBL.'.member_type_id='.$value;}
	if(!empty($membsql)) {$memb_sql=' AND ('.implode(' || ',$membsql).') ';}else{$memb_sql='';}
	$sql='SELECT '.CA_PEO_TBL.'.*';
	if(!empty($sg)) $sql.=','.CA_SMG_TBL.'.group_name';
	if(!empty($add))$sql.=','.CA_HOU_TBL.'.address ';
	$sql.=' FROM '.CA_PEO_TBL.$address.$sg.$genders.$people_sql.$memb_sql.'  ORDER BY last_name';
	
	$results = $wpdb->get_results($sql);
	if($results)
	{
		$csv="First Name, Last Name, Email, Mobile";
		if(!empty($add))$csv.=',Address';
		if(!empty($sg))$csv.=',Small Group';
		$csv.="\r\n";
		foreach($results AS $row)
		{
			
			$csv.='"'.iconv('UTF-8', 'ISO-8859-1',$row->first_name).'","';
			if(!empty($row->prefix))$csv.=iconv('UTF-8', 'ISO-8859-1',$row->prefix).' ';
			$csv.=iconv('UTF-8', 'ISO-8859-1',$row->last_name).'","'.iconv('UTF-8', 'ISO-8859-1',$row->email).'","'.$row->mobile.'"';
			if(!empty($add))$csv.=',"'.iconv('UTF-8', 'ISO-8859-1',$row->address).'"';
			if(!empty($sg))$csv.=',"'.iconv('UTF-8', 'ISO-8859-1',$row->group_name).'"';
			$csv.="\r\n";
		}
		
		    header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-Disposition: attachment; filename=\"people.csv\"");
			echo $csv;
			exit();
	}		
}

function church_admin_label_pdf($member_type_id=1)
{
global $wpdb;
$wpdb->show_errors();
//grab addresses
//get alphabetic order
$memb=explode(',',esc_sql($member_type_id));
foreach($memb AS $key=>$value){if(ctype_digit($value))$membsql[]='member_type_id='.$value;}
if(!empty($membsql)) {$memb_sql=' WHERE '.implode(' || ',$membsql).' ';}else{$memb_sql='';}
$sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.' GROUP BY last_name ORDER BY last_name';
$results = $wpdb->get_results($sql);
if($results)
{
     require_once('PDF_Label.php');
    $pdflabel = new PDF_Label(get_option('church_admin_label'), 'mm', 1, 2);
    $pdflabel->Open();
    $pdflabel->AddPage();
    $counter=1;
    $addresses=array();
    foreach ($results as $row) 
    {
	
	$add='';
	$address_row=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
	$address=iconv('UTF-8', 'ISO-8859-1',$address_row->address);
	if(!empty($address))
	{
	    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($row->household_id).'" ORDER BY people_type_id ASC,sex DESC');
	    $adults=array();
	    foreach($people_results AS $people)
	    {
	      if($people->people_type_id=='1')
	      {
	        $last_name=iconv('UTF-8', 'ISO-8859-1',$people->last_name);
	        $adults[]=iconv('UTF-8', 'ISO-8859-1',$people->first_name);
	    }
	    }	
	    
	    $add=html_entity_decode(implode(" & ",$adults))." ".$last_name."\n".str_replace(",",",\n",$address);
	    
	    $pdflabel->Add_Label($add);
	}
    }
    //start of cache mailing labels!
   
   
$pdflabel->Output();

//end of mailing labels
}
}


function ca_vcard($id)
{
  global $wpdb;
 $wpdb->show_errors();
    $query='SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($id).'"';
	
	$add_row = $wpdb->get_row($query);
    $address=$add_row->address;
    $phone=$add_row->phone;
    $people_results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($id).'" ORDER BY people_type_id ASC,sex DESC');
    $adults=$children=$emails=$mobiles=array();
      foreach($people_results AS $people)
	{
	  if($people->people_type_id=='1')
	  {
	    $last_name=$people->last_name;
	    $adults[]=$people->first_name;
	    if($people->email!=end($emails)) $emails[]=$people->email;
	    if($people->mobile!=end($mobiles))$mobiles[]=$people->mobile;
		
	  }
	  else
	  {
	    $children[]=$people->first_name;
	  }
	  if(!empty($people->attachment_id))
		{
			$photo=wp_get_attachment_image_src( $people->attachment_id, 'ca-people-thumb',0,$attr );
			
		}
	}
  //prepare vcard
require_once(plugin_dir_path(dirname(__FILE__)).'includes/vcf.php');
$v = new vCard();
if(!empty($add_row->phone))$v->setPhoneNumber($add_row->phone, "PREF;HOME;VOICE");
if(!empty($mobiles))$v->setPhoneNumber("{$mobiles['0']}", "CELL;VOICE");
$v->setName("{$last_name}", implode(" & ",$adults), "", "");

$v->setAddress('',$add_row->address,'','','','','','HOME;POSTAL' );
$v->setEmail("{$emails['0']}");

if(!empty($children)){$v->setNote("Children: ".implode(", ",$children));}
if(!empty($photo))
{
	
	$t=exif_imagetype($photo['0']); 		
	switch($t)
		{
			case 1:$type='GIF';break;
			case 2:$type='JPG';break;
			
		}
	if(!empty($type))$v->setPhoto($type,$photo[0]);
}

$output = $v->getVCard();
$filename=$last_name.'.vcf';


    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: text/x-vcard");
    header("Content-Transfer-Encoding: binary");

   echo $output;

}
function church_admin_year_planner_pdf($initial_year)
{
    if(empty($initial_year))$initial_year==date('Y');
    global $wpdb;
	$wpdb->show_errors;
//check cache admin exists
$upload_dir = wp_upload_dir();
$dir=$upload_dir['basedir'].'/church-admin-cache/';


//initialise pdf
require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
$pdf=new FPDF();
$pdf->AddPage('L','A4');

$pageno=0;
$x=10;
$y=5;
//Title
$pdf->SetXY($x,$y);
$pdf->SetFont('Arial','B',18);
$title=get_option('blogname');
$pdf->Cell(0,8,$title,0,0,'C');
$pdf->SetFont('Arial','B',10);

//Get initial Values
$initial_month='01';
if(empty($initial_year))$initial_year=date('Y');
$month=0;
$days=array('Sun','Mon','Tues','Weds','Thurs','Fri','Sat');
$row=0;
$current=time();
$this_month = (int)date("m",$current);
$this_year = date( "Y",$current );

for($quarter=0;$quarter<=3;$quarter++)
{
for($column=0;$column<=2;$column++)
{//print one of the three columns of months
    $x=10+($column*80);//position column
    $y=15+(44*$quarter);
    $pdf->SetXY($x,$y);
    $this_month=date('m',strtotime($initial_year.'-'.$initial_month.'-01 + '.$month.' month'));
    $this_year=date('Y',strtotime($initial_year.'-'.$initial_month.'-01 + '.$month.' month'));
    // find out the number of days in the month
    $numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $this_month, $this_year );
    // create a calendar object
    $jd = cal_to_jd( CAL_GREGORIAN, $this_month,date( 1 ), $this_year );
    // get the start day as an int (0 = Sunday, 1 = Monday, etc)
    $startday = jddayofweek( $jd , 0 );
    // get the month as a name
    $monthname = jdmonthname( $jd, 1 );
    $month++;//increment month for next iteration
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(70,7,$monthname.' '.$this_year,0,0,'C');
    //position to top left corner of calendar month 
    $y+=7;
    $pdf->SetXY($x,$y);
    $pdf->SetFont('Arial','',8);
    //print daylegend
    for($legend=0;$legend<=6;$legend++)
    {
        $pdf->Cell(10,5,$days[$legend],1,0,'C');
    }
    $y+=5;
    $pdf->SetXY($x,$y);
    for($monthrow=0;$monthrow<=5;$monthrow++)
    {//print 6 weeks
        
        for($day=0;$day<=6;$day++)
        {
            if($monthrow==0 && $day==$startday)$counter=1;//month has started
            if($monthrow==0 && $day<$startday)
            {
                //empty cells before start of month, so fill with grey colour
                $pdf->SetFillColor('192','192','192');
                $pdf->Cell(10,5,'',1,0,'L',TRUE);
            }
            else
            {
                //during month so category background
                $sql='SELECT a.bgcolor FROM '.CA_CAT_TBL.' a, '.CA_DATE_TBL.' b WHERE b.year_planner="1" AND a.cat_id=b.cat_id AND b.start_date="'.$this_year.'-'.$this_month.'-'.sprintf('%02d',$counter).'" LIMIT 1';
				
				$bgcolor=$wpdb->get_var($sql);
                if(!empty($bgcolor))
                {
                    $colour=html2rgb($bgcolor);
                    $pdf->SetFillColor($colour[0],$colour[1],$colour[2]);
                }
                else
                {
                    $pdf->SetFillColor(255,255,255);
                }
                
                 if($counter <= $numdaysinmonth)
                {
                    //duringmonth so print a date
                    $pdf->Cell(10,5,$counter,1,0,'L',TRUE);
                    $counter++;
                }
                else
                {
                //end of month, so back to grey background
                $pdf->SetFillColor('192','192','192');
                $pdf->Cell(10,5,'',1,0,'C',TRUE);
                }
            }
            
           
            
        }
        $y+=5;
        
        $pdf->SetXY($x,$y);
    }
    
}//end of column
}//end row

//Build key
$x=250;
$y=23;
 $pdf->SetFont('Arial','B',10);
$result=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category");
foreach ($result AS $row)
{
    
    $pdf->SetXY($x,$y);
    $colour=html2rgb($row->bgcolor);
    $pdf->SetFillColor($colour[0],$colour[1],$colour[2]);
    $pdf->Cell(15,5,' ',0,0,'L',1);
    $pdf->SetFillColor(255,255,255);
    $pdf->Cell(15,5,$row->category,0,0,'L');
    $pdf->SetXY($x,$y);
    $pdf->Cell(45,5,'',1);
    $y+=6;
}
$pdf->Output();

}


function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function church_admin_horiz_pdf($service_id)
{
	global $wpdb;
	if(!empty($_GET['rota_id']))
	{
		
		require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
		$pdf=new FPDF();
		$rota_jobs=$initials=array();
		foreach($_GET['rota_id'] AS $key=>$value){$rota_jobs[]=' rota_id="'.$value.'" ';}
		if(!empty($_GET['initials']))foreach($_GET['initials'] AS $key=>$value){$initials[$value]=TRUE;}
		$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' WHERE ('.implode(" OR ",$rota_jobs).') ORDER BY rota_order');
		$wanted_jobs=array();
		foreach($rota_tasks AS $rota_task){$wanted_jobs[$rota_task->rota_id]=$rota_task->rota_task;}
		$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE rota_date>CURDATE() ORDER BY rota_date ASC';
		
			$rota_results=$wpdb->get_results($sql);
			
			if(!empty($rota_results))
			{
				$pdf->AddPage('L',get_option('church_admin_pdf_size'));
				
				$pdf->SetFont('Arial','',16);
				
				$text='Rota';
				;
				$pdf->Cell(0,10,$text,0,2,'C');
				$pdf->SetFont('Arial','B',10);
				//job titles
				$pdf->Cell(30,7,__('Date','church-admin'),1,0,'C');
				foreach($wanted_jobs AS $key=>$value)
				{
					if($value=='Sermon Title'){$w=90;}else{$w=45;}

					$pdf->Cell($w,7,$value,1,0,'C');
				}	
				$pdf->Ln();
				$pdf->SetFont('Arial','',10);
				foreach($rota_results AS $rota_row)
				{
					//date
					$pdf->Cell(30,7,mysql2date(get_option('date_format'),$rota_row->rota_date),1,0,'C');
					$jobs_for_day=maybe_unserialize($rota_row->rota_jobs);
					foreach($wanted_jobs AS $key=>$value)
					{
						$people=church_admin_get_people($jobs_for_day[$key]);
						if(!empty($initials[$key]))
						{
							$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_initials($jobs_for_day[$key]));
						}
						else
						{
							$ppl=iconv('UTF-8', 'ISO-8859-1',$people);
						}
						if($value=='Sermon Title'){$w=90;}else{$w=45;}
						$pdf->Cell($w,7,$ppl,1,0,'L');
					}
					$pdf->Ln();
				}
			
			}
		
		
	$pdf->Output();
	}
	

}
function church_admin_rota_pdf($service_id=1)
{
    
    global $wpdb,$days;
    $wpdb->show_errors();
	$percent=array();
	$headers=array();
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	
	$pdf=new FPDF();
	
	
		//Grab Service details
	$service=$wpdb->get_row('SELECT * FROM '.CA_SER_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	//Main rota
	$jobs=array();
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
	for($months=0;$months<=2;$months++)
	{
		
		$sql='SELECT * FROM '.CA_ROT_TBL.' WHERE YEAR(rota_date) = YEAR(CURRENT_DATE + INTERVAL '.$months.' MONTH) AND MONTH(rota_date) = MONTH(CURRENT_DATE + INTERVAL '.$months.' MONTH) ORDER BY rota_date ASC';
		
		$rota_results=$wpdb->get_results($sql);
		if(!empty($rota_results))
		{
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			$pdf->AddFont('Verdana','','verdana.php');
			$pdf->SetFont('Verdana','',16);
			//php has a plus 1 month bug where current day is larger than next months last date eg 31st May will not get June for next month
			//use Mysql to get next month which doesn't have the bug.
			$month_shown=$wpdb->get_var('SELECT MONTHNAME(CURRENT_DATE + INTERVAL '.$months.' MONTH)');
			$text=sprintf( __('Who is doing what in %1$s at %2$s on %3$s at %4$s ', 'church-admin'), $month_shown,$service->service_name,$days[$service->service_day],$service->service_time);
			//$text=__('Sunday Rota produced','church-admin').date("d-m-Y");
			$pdf->Cell(0,10,$text,0,2,'C');
			$pdf->SetFont('Arial','B',12);
			//left hand column shows
			//grab this months services
			//Top left cell empty!
			$pdf->Cell(45,7,'',1,0,'C');
			$jobs=array();
			foreach($rota_results AS $rota_row)
			{
			
				//Output date
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(45,7,mysql2date(get_option('date_format'),$rota_row->rota_date),1,0,'C',0);
				//put that service's jobs in an array with date and job_id for key
				$jobs_for_day=maybe_unserialize($rota_row->rota_jobs);
				
				foreach($jobs_for_day AS $job_key=>$job_who) 
				{
					
					$jobs[$job_key][$rota_row->rota_date]=maybe_unserialize($job_who);
					
				}
					
			}
		
	
			//grab rota order
			$order=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order');
			$x=0;
	
			foreach($order AS $rota_job)
			{
				//1st Column
				$pdf->Ln(7);//line break
				$pdf->SetFont('Arial','B',6);
				$pdf->Cell(45,7,$rota_job->rota_task,1,0,'C',0);
				//that job for each date
		
				$pdf->SetFont('Arial','',6);
				
				foreach((array)$jobs[$rota_job->rota_id] AS $date=>$people)
				{
			
					if($x %2 == 0){$pdf->SetFillColor(200,200,200);$fill=1;}else{$fill=0;}
			
					if(!empty($rota_job->initials)){$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_initials($people));}else{$ppl=iconv('UTF-8', 'ISO-8859-1',church_admin_get_people($people));}
					$pdf->Cell(45,7,$ppl,1,0,'C',$fill);
					$x++;
				}
				$x=0;
			}
		}
	}	
	$pdf->Output();
	
}


function church_admin_small_group_xml()
{
	global $wpdb;
	$results=$wpdb->get_results('SELECT * FROM '.CA_SMG_TBL.' WHERE lat!="" AND lng!=""');
	if(!empty($results))
	{
		$color_def = array
	('1'=>"FF0000",'2'=>"00FF00",'3'=>"0000FF",'4'=>"FFF000",'5'=>"00FFFF",'6'=>"FF00FF",'7'=>"CCCCCC",	8  => "FF7F00",	9  => "7F7F7F",	10 => "BFBFBF",	11 => "007F00",
		12 => "7FFF00",	13 => "00007F",	14 => "7F0000",	15 => "7F4000",
		16 => "FF9933",	17 => "007F7F",	18 => "7F007F",	19 => "007F7F",
		20 => "7F00FF",	21 => "3399CC",	22 => "CCFFCC",	23 => "006633",
		24 => "FF0033",	25 => "B21919",	26 => "993300",	27 => "CC9933",
		28 => "999933",	29 => "FFFFBF",	30 => "FFFF7F",31  => "000000"
	);
		
		header("Content-type: text/xml;charset=utf-8");
		echo '<markers>';
		foreach($results AS $row)
		{

			// Iterate through the rows, printing XML nodes for each

			// ADD TO XML DOCUMENT NODE
				echo '<marker ';
				echo 'pinColor="'.$color_def[$row->id].'" ';
				echo 'lat="' . $row->lat . '" ';
				echo 'lng="' . $row->lng . '" ';
				echo 'smallgroup_name="'.htmlentities($row->group_name).'" ';
				echo 'address="'.htmlentities($row->address).'" ';
				echo 'when="'.htmlentities($row->whenwhere).'" ';
				echo 'smallgroup_id="'.$row->id.'" ';
				echo '/>';
		}
		// End XML file
		echo '</markers>';
				
	}
}
function church_admin_address_xml($member_type_id=1,$small_group=1)
{
    global $wpdb;
	$wpdb->show_errors();
	$markers='<markers>';
    $color_def = array(	'1'=>"FF0000",'2'=>"00FF00",'3'=>"0000FF",'4'=>"FFF000",'5'=>"00FFFF",'6'=>"FF00FF",'7'=>"CCCCCC",'8'  => "FF7F00",	
	9  => "7F7F7F",	10 => "BFBFBF",	11 => "007F00",
		12 => "7FFF00",	13 => "00007F",	14 => "7F0000",	15 => "7F4000",
		16 => "999933",	17 => "007F7F",	18 => "7F007F",	19 => "007F7F",
		20 => "7F00FF",	21 => "3399CC",	22 => "CCFFCC",	23 => "006633",
		24 => "FF0033",	25 => "B21919",	26 => "993300",	27 => "CC9933",
		28 => "FF9933",	29 => "FFFFBF",	30 => "FFFF7F",31  => "000000"
	);
	//grab relevant households
	$memb=explode(',',esc_sql($member_type_id));
	foreach($memb AS $key=>$value){if(ctype_digit($value)) $membsql[]='member_type_id='.$value;}
	if(!empty($membsql)) {$memb_sql=' WHERE '.implode(' || ',$membsql);}else{$memb_sql='';}
	$sql='SELECT household_id FROM '.CA_PEO_TBL.$memb_sql.'  GROUP BY household_id ORDER BY last_name ASC ';
	$results=$wpdb->get_results($sql);
    if(!empty($results))
	{
		foreach($results AS $row)
		{
			$address=$wpdb->get_row('SELECT * FROM '.CA_HOU_TBL.' WHERE household_id="'.esc_sql($row->household_id).'"');
			$sql='SELECT a.* FROM '.CA_PEO_TBL.' a  WHERE a.household_id="'.esc_sql($row->household_id).'" ORDER BY a.people_order, a.people_type_id ASC,sex DESC';
			$people_results=$wpdb->get_results($sql);
			
			$adults=$children=$emails=$mobiles=$photos=array();
			$last_name='';
			$x=0;
			$markers.= '<marker ';
			foreach($people_results AS $people)
			{
				
				if($people->people_type_id=='1')
				{	
					if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
					$last_name=$prefix.$people->last_name;
					$adults[$last_name][]=$people->first_name;
					$smallgroup=$wpdb->get_row('SELECT * FROM '.CA_SMG_TBL.' WHERE id="'.$people->smallgroup_id.'"');
							//small group data for marker
							
							if(!empty($smallgroup))
							{
								$sg=array();
								$sg[]=  'pinColor="'.$color_def[$smallgroup->id].'" ';
								$sg[]= 'smallgroup_id="'.$smallgroup->id.'" ';
								$sg[]= 'smallgroup_name="'.htmlentities($smallgroup->group_name).'" ';
								$sg[]=  'small_group_address="'.htmlentities($smallgroup->address).'" ';
								$sg[]=  'when="'.htmlentities($smallgroup->whenwhere).'" ';
							}
							else
							{$sg=array();
								$sg[]= 'pinColor="FF0000" ';
							}		
					$x++;
				}
				else
				{
					if(!empty($people->prefix)){$prefix=$people->prefix.' ';}else{$prefix='';}
					$last_name=$prefix.$people->last_name;
					$children[$last_name][]=$people->first_name;
				
				}
				
			}
			$markers.=implode(" ",$sg);
			//address data for marker
			$markers.= 'lat="' . $address->lat . '" ';
			$markers.= 'lng="' . $address->lng . '" ';
			$markers.= 'address="'. $address->address.'" ';
			
			//people data
			array_filter($adults);
			$adultline=array();
			//the join statement makes sure the array is imploded like this ",,,&"  
			//http://stackoverflow.com/questions/8586141/implode-array-with-and-add-and-before-last-item 
			foreach($adults as $lastname=>$firstnames){$adultline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice($firstnames, 0, -1))), array_slice($firstnames, -1)))).' '.$lastname;}
			$markers.='adults_names="'.implode(" &amp; ",$adultline). '" ';
			array_filter($children);
			$childrenline=array();
			foreach($children as $lastname=>$firstnames){$childrenline[]=join(' &amp; ', array_filter(array_merge(array(join(', ', array_slice($firstnames, 0, -1))), array_slice($firstnames, -1)))).' '.$lastname;}
			$markers.='childrens_names="'.implode(" &amp; ",$childrenline). '" ';
			$markers.= '/>';
		}
		$markers.='</markers>';
		header("Content-type: text/xml;charset=utf-8");
		echo $markers;
	}
   
    exit();    
}



function church_admin_ministry_pdf()
{
	global $wpdb;
	$ministries=array();
	$ministry_names=get_option('church_admin_departments');
	
	foreach($ministry_names AS $key=>$ministry_name)
	{
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE b.meta_type="ministry" AND a.people_id=b.people_id AND b.department_id="'.esc_sql($key).'" ORDER BY a.last_name';
			
			$people=$wpdb->get_results($sql);
			if(!empty($people))
			{
				foreach($people AS $person) {$ministries[$ministry_name][]=$person->name;}
			}
	
	}
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	$pdf=new FPDF();
	$pdf->AddPage('L',get_option('church_admin_pdf_size'));
	
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(0,10,__('Ministries','church-admin'),0,0,C);
	$pdf->SetFont('Arial','',10);
	$i=1;
	$x=15;
	$y=25;
	ksort($ministries);
	foreach($ministries AS $min_name=>$people)
	{	
		if($i>6)
		{
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));$x=15;$x=25;$i=1;
			
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(0,6,__('Ministries','church-admin'),0,0,'C');
			
		}
		$pdf->SetXY($x,25);
		//ministry name
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,6,$min_name,1,0,'C');
		$pdf->SetXY($x,31);
		//ministry people
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(40,6,iconv('UTF-8', 'ISO-8859-1',implode("\n",$people)),1,'L');
		
		$i++;
		$x+=40;
		$y=30;
		$pdf->SetXY($x,$y);
	}
	$pdf->Output();
}
function church_admin_hope_team_pdf()
{
	global $wpdb;
	$hope_teams=$wpdb->get_results('SELECT * FROM '.CA_HOP_TBL);
	$hope_team_jobs=array();
	
	foreach($hope_teams AS $hope_team)
	{
			$sql='SELECT CONCAT_WS(" ",a.first_name,a.prefix,a.last_name) AS name , mobile, email FROM '.CA_PEO_TBL.' a, '.CA_MET_TBL.' b WHERE b.meta_type="hope_team" AND a.people_id=b.people_id AND b.department_id="'.esc_sql($hope_team->hope_team_id).'" ORDER BY a.last_name';
			
			$people=$wpdb->get_results($sql);
			if(!empty($people))
			{
				foreach($people AS $person) {$hope_team_jobs[$hope_team->job][]=$person->name.' '.$person->mobile.' '.$person->email;}
			}
	
	}
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	$pdf=new FPDF();
	$pdf->AddPage('P',get_option('church_admin_pdf_size'));
	
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(0,10,__('Hope Team','church-admin'),0,1,'C');
	$pdf->SetFont('Arial','',10);
	$i=1;
	
	ksort($hope_team_jobs);
	foreach($hope_team_jobs AS $min_name=>$people)
	{	
		
		//ministry name
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(0,6,$min_name,1,1,'C');
		
		//ministry people
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(0,6,iconv('UTF-8', 'ISO-8859-1',implode("\n",$people)),1,'L');
		$pdf->Ln(5);
		
		
		
	}
	$pdf->Output();
}

function church_admin_kidswork_pdf()
{
	global $wpdb;
	$kidsworkGroups=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL.' ORDER BY youngest DESC');
	
    global $wpdb,$member_type;
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/fpdf.php');
	//cache small group pdf
	$wpdb->show_errors();
	$kidsworkgroups=$groupnames=array();
	$count=0;
	$leader=array();
	
	$count=$noofgroups=0;
	//get groups
	$results=$wpdb->get_results('SELECT * FROM '.CA_KID_TBL);
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$noofgroups++;
			$groupname[$row->id]=iconv('UTF-8', 'ISO-8859-1',$row->group_name);//title first
			$sql='SELECT CONCAT_WS(" ",first_name,last_name) AS name,kidswork_override FROM '.CA_PEO_TBL.' WHERE date_of_birth<"'.$row->youngest.'" AND date_of_birth>"'.$row->oldest.'" AND date_of_birth!="0000-00-00" ORDER BY date_of_birth,last_name ';
			$peopleresults = $wpdb->get_results($sql);
			if(!empty($peopleresults))
			{
				foreach($peopleresults AS $people) 
				{
					if(empty($people->kidswork_override)){$kidsworkgroups[$row->id][]=$people->name;$count++;}else{$kidsworkgroups[$people->kidswork_override][]=$people->name;$count++;}
				}
			}
		}
	}
	
	
	
	$counter=$noofgroups;

	$pdf=new FPDF();
	$pageno=0;
	$x=10;
	$y=20;
	$w=1;
	$width=55;
	$pdf->AddPage('L',get_option('church_admin_pdf_size'));
	$pdf->SetFont('Arial','B',16);
	
	$whichtype=array();
	
	$text=implode(", ",$whichtype).' '.__('Kidswork Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
	$pdf->Cell(0,10,$text,0,2,'C');
	$pageno+=1;



	foreach($groupname AS $id=>$groupname)
	{
		$text='';
		if($w==6)
		{
			$pdf->SetFont('Arial','B',16);
			$pdf->AddPage('L',get_option('church_admin_pdf_size'));
			
			$whichtype=array();
			foreach($memb AS $key=>$value)$whichtype[]=$member_type[$value];
			$text=implode(", ",$whichtype).' '.__('Kidswork Group List','church-admin').' '.date(get_option('date_format')).'  '.$count.' '.__('people','church-admin');
			$pdf->Cell(0,10,$text,0,2,'C');
			$x=10;
			$y=20;
			$w=1;
		}
		$newx=$x+(($w-1)*$width);
		if($pageno>1) {$newx=$x+(($z-($pageno*5))*$width);}
		$pdf->SetXY($newx,$y);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell($width,8,iconv('UTF-8', 'ISO-8859-1',$groupname),1,1,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY($newx,$y+8);
		
			
			$pdf->SetFont('Arial','',10);
			$text='';
			if(!empty($kidsworkgroups[$id]))$text=implode("\n",$kidsworkgroups[$id]);
			$pdf->MultiCell($width,5,$text."\n",'LR');
			
			$pdf->SetX($newx);
	
		
		$pdf->Cell($width,0,"",'LB',2,'L');
		$w++;
	}
	$pdf->Output();
}



?>