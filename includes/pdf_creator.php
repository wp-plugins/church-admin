<?php



function church_admin_smallgroup_pdf()
{
    global $wpdb;
require_once(CHURCH_ADMIN_INCLUDE_PATH."fpdf.php");
//cache small group pdf
$wpdb->show_errors();
$smallgroups=array();
$leader=array();

//grab people

$sql="SELECT CONCAT_WS(' ',".$wpdb->prefix."church_admin_directory.first_name,".$wpdb->prefix."church_admin_directory.last_name) AS name, ".$wpdb->prefix."church_admin_smallgroup.group_name FROM ".$wpdb->prefix."church_admin_directory,".$wpdb->prefix."church_admin_smallgroup WHERE ".$wpdb->prefix."church_admin_directory.small_group=".$wpdb->prefix."church_admin_smallgroup.id ORDER BY ".$wpdb->prefix."church_admin_directory.small_group";
$results = $wpdb->get_results($sql);
$gp=0;
foreach ($results as $row) 
    {
        $row->name=stripslashes($row->name);
        $smallgroups["{$row->group_name}"].=$row->name."\n";

    }
$groupname=array_keys($smallgroups);
$noofgroups=$wpdb->get_row("SELECT COUNT(id) AS no FROM ".$wpdb->prefix."church_admin_smallgroup");
$counter=$noofgroups->no;

$pdf=new FPDF();
$pageno=0;
$x=10;
$y=30;
$w=1;
$width=55;
$pdf->AddPage('L',get_option('church_admin_pdf_size'));
$pdf->SetFont('Arial','B',16);
$next_sunday=strtotime("this sunday");
$text='Small Group List '.date("d-m-Y",$next_sunday);
$pdf->Cell(0,10,$text,0,2,C);
$pageno+=1;



for($z=0;$z<=$counter-1;$z++)
	{
	if($w==6)
	{
	  $pdf->AddPage('L','A4');
	  $pdf->SetFont('Arial','B',16);
	  $next_sunday=strtotime("this sunday");
	  $text='Small Group List '.date("d-m-Y",$next_sunday);
	  $pdf->Cell(0,10,$text,0,2,C);
	  $x=10;
	  $y=30;
	  $w=1;
	}
	$newx=$x+(($w-1)*$width);
	if($pageno>1) {$newx=$x+(($z-($pageno*5))*$width);}
	$pdf->SetXY($newx,$y);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell($width,10,$groupname[$z],1,0,C);
	$pdf->SetFont('Arial','',10);
	$pdf->SetXY($newx,$y+10);
	$pdf->MultiCell($width,7,$smallgroups[$groupname[$z]],1,L);
	$w++;
	}
$pdf->Output();
}


function church_admin_address_pdf()
{
  global $wpdb;
//address book cache
require_once(CHURCH_ADMIN_INCLUDE_PATH."fpdf.php");

//grab addresses
$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_directory ORDER BY last_name, first_name");    
$counter=1;
$addresses=array();
foreach ($results as $row) 
    {
$addresses['address'.$counter]=array();
$addresses['address'.$counter]['name']=html_entity_decode($row->first_name)." ".$row->last_name;
$addresses['address'.$counter]['kids']=$row->children;
$addresses['address'.$counter]['address']=stripslashes($row->address_line1).', ';
$addresses['label'.$counter]=$row->address_line1;
if(!empty($row->address_line2)){$addresses['address'.$counter]['address'].=stripslashes($row->address_line2).', ';$addresses['label'.$counter].=",\n".stripslashes($row->address_line2);}
if(!empty($row->city)){$addresses['address'.$counter]['address'].=stripslashes($row->city).', ';$addresses['label'.$counter].=",\n".stripslashes($row->city);}
if(!empty($row->state)){$addresses['address'.$counter]['address'].=stripslashes($row->state).', ';$addresses['label'.$counter].=",\n".stripslashes($row->state);}
if(!empty($row->zipcode)){$addresses['address'.$counter]['address'].=stripslashes($row->zipcode).'.';$addresses['label'.$counter].=",\n".stripslashes($row->zipcode);}
if(!empty($row->email)){$addresses['address'.$counter]['email1']=$row->email;}else{$addresses['address'.$counter]['email1']='';}
if(!empty($row->email2)){$addresses['address'.$counter]['email2']=$row->email2;}else{$addresses['address'.$counter]['email2']='';}
if(!empty($row->homephone)){$addresses['address'.$counter]['phone']=$row->homephone;}else{$addresses['address'.$counter]['homephone']='';}
if(!empty($row->cellphone)){$addresses['address'.$counter]['mobile']=$row->cellphone;}else{$addresses['address'.$counter]['mobile']='';}
$counter++;





    }
    

    
//start of cache address-list.pdf    
$pdf=new FPDF();
$pageno=0;
$x=10;
$y=30;
$width=55;
global $pageno;
if(!function_exists('newpage'))
{function newpage($pdf)
{
$pdf->AddPage('P',get_option('church_admin_pdf_size'));
$pdf->SetFont('Arial','B',24);
$text='Address List '.date("d-m-Y");
$pdf->Cell(0,20,$text,0,2,C);
$pdf->SetFont('Arial','',12);
$pageno+=1;
}
}
newpage($pdf);
for($z=0;$z<=$counter-1;$z++)
    {
        if($z/12>0&&$z%12==0) newpage($pdf);//every 13 addresses new page is called
    if(!empty($addresses['address'.$z][name]))
    {
        $pdf->SetFont('Arial','B',10);
           if(!empty($addresses['address'.$z][kids])){$pdf->Cell(100,5,$addresses['address'.$z][name]." ({$addresses['address'.$z][kids]})",0,0,L);}
        else{$pdf->Cell(100,5,$addresses['address'.$z][name],0,0,L);}
        $pdf->SetFont('Arial','',10);
        if(!empty($addresses['address'.$z][phone])){$pdf->Cell(80,5,$addresses['address'.$z][phone],0,1,R);}else{$pdf->Cell(80,5,$addresses['address'.$z][mobile],0,1,R);}
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(100,5,$addresses['address'.$z][address],0,0,L);
        if(!empty($addresses['address'.$z][phone])){$pdf->Cell(80,5,$addresses['address'.$z][mobile],0,1,R);}else{$pdf->Ln();}
        
        $pdf->Cell(0,5,$addresses['address'.$z][email1].' '.$addresses['address'.$z][email2],0,1,L);
        $pdf->Ln();
    }
    }

$pdf->Output();


//end of cache address list
}

function church_admin_label_pdf($type)
{
global $wpdb;
//grab addresses
switch($type)
{
    case'address':$tbl='church_admin_directory';break;
    case'visitor':$tbl='church_admin_visitors';break;
}
	$sql="SELECT * FROM ".$wpdb->prefix.$tbl." ORDER BY last_name, first_name";
    
$results = $wpdb->get_results($sql);    
$counter=1;
$addresses=array();
if(!$results)exit('DB query failure');
foreach ($results as $row) 
    {
$addresses['address'.$counter]=array();
$addresses['address'.$counter]['name']=html_entity_decode($row->first_name)." ".$row->last_name;
$addresses['address'.$counter]['kids']=$row->children;
$addresses['address'.$counter]['address']=stripslashes($row->address_line1).', ';
$addresses['label'.$counter]=$row->address_line1;
if(!empty($row->address_line2)){$addresses['address'.$counter]['address'].=stripslashes($row->address_line2).', ';$addresses['label'.$counter].=",\n".stripslashes($row->address_line2);}
if(!empty($row->city)){$addresses['address'.$counter]['address'].=stripslashes($row->city).', ';$addresses['label'.$counter].=",\n".stripslashes($row->city);}
if(!empty($row->state)){$addresses['address'.$counter]['address'].=stripslashes($row->state).', ';$addresses['label'.$counter].=",\n".stripslashes($row->state);}
if(!empty($row->zipcode)){$addresses['address'.$counter]['address'].=stripslashes($row->zipcode).'.';$addresses['label'.$counter].=",\n".stripslashes($row->zipcode);}
if(!empty($row->email)){$addresses['address'.$counter]['email1']=$row->email;}else{$addresses['address'.$counter]['email1']='';}
if(!empty($row->email2)){$addresses['address'.$counter]['email2']=$row->email2;}else{$addresses['address'.$counter]['email2']='';}
if(!empty($row->homephone)){$addresses['address'.$counter]['phone']=$row->homephone;}else{$addresses['address'.$counter]['homephone']='';}
if(!empty($row->cellphone)){$addresses['address'.$counter]['mobile']=$row->cellphone;}else{$addresses['address'.$counter]['mobile']='';}
$counter++;
}
//start of cache mailing labels!
require_once('PDF_Label.php');
$pdflabel = new PDF_Label(get_option('church_admin_label'), 'mm', 1, 2);
$pdflabel->Open();
$pdflabel->AddPage();

for($z=0;$z<=$counter-1;$z++)
{
    $add=$addresses['address'.$z][name]."\n".$addresses['label'.$z];
    $pdflabel->Add_Label($add);
}
$pdflabel->Output();

//end of mailing labels
}



function ca_vcard($id)
{
  global $wpdb;
 
  $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_directory WHERE id="'.esc_sql($id).'"';

  $row = $wpdb->get_row($sql);

  //prepare vcard
require_once(CHURCH_ADMIN_INCLUDE_PATH.'vcf.php');
$v = new vCard();
if(!empty($row->homephone))$v->setPhoneNumber("{$row->homephone}", "PREF;HOME;VOICE");
if(!empty($row->cellphone))$v->setPhoneNumber("{$row->cellphone}", "CELL;VOICE");
$v->setName("{$row->last_name}", "{$row->first_name}", "", "");
$v->setAddress("", stripslashes($row->address_line1), stripslashes($row->address_line2), stripslashes($row->city), stripslashes($row->state),stripslashes($row->zipcode) ,"");
$v->setEmail("{$row->email}");

if(!empty($row->children)){$v->setNote("Children: ".stripslashes($row->children));}
$output = $v->getVCard();
$filename=$row->last_name.'.vcf';


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
//check cache admin exists
$dir=CHURCH_ADMIN_CACHE_PATH;


//initialise pdf
require_once(CHURCH_ADMIN_INCLUDE_PATH."fpdf.php");
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
                $bgcolor=$wpdb->get_var("SELECT ".$wpdb->prefix."church_admin_calendar_category.bgcolor FROM ".$wpdb->prefix."church_admin_calendar_category,".$wpdb->prefix."church_admin_calendar_event,".$wpdb->prefix."church_admin_calendar_date WHERE ".$wpdb->prefix."church_admin_calendar_event.year_planner='1' AND ".$wpdb->prefix."church_admin_calendar_event.cat_id=".$wpdb->prefix."church_admin_calendar_category.cat_id AND ".$wpdb->prefix."church_admin_calendar_event.event_id=".$wpdb->prefix."church_admin_calendar_date.event_id AND ".$wpdb->prefix."church_admin_calendar_date.start_date='".$this_year."-".$this_month."-".$counter."' LIMIT 1");
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

function church_admin_rota_pdf()
{
    
    global $wpdb;
    $wpdb->show_errors();
$percent=array();
$headers=array();


$totalcharas=12;//allow for date in output
//grab character count from largest results
$now=date('Y-m-d');
$threemonths=date('Y-m-d',strtotime('+6 months'));

require_once(CHURCH_ADMIN_INCLUDE_PATH.'fpdf.php');
$pdf=new FPDF();
$pdf->AddPage('L',get_option('church_admin_pdf_size'));
$pdf->SetFont('Arial','B',16);
$text='Sunday Rota '.date("d-m-Y");
$pdf->Cell(0,10,$text,0,2,C);
$pdf->SetFont('Arial','B',8);

//column headers query
$colres=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings ORDER BY rota_id");
//set up size array, minimum length is the number of characters in the job title (helps if no one is assigned role!)
$size=array();
foreach($colres AS $colrow)$size[$colrow->rota_task]=strlen($colrow->rota_task);
//grab dates
$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_rotas WHERE rota_date>"'.$now.'" AND rota_date<="'.$threemonths.'"';
$results=$wpdb->get_results($sql);


//find longest rota entries
foreach($results AS $row)
{
    $jobs=unserialize($row->rota_jobs);
    foreach($jobs AS $job=>$value)
    {
	//replace $size value if bigger
	//ignore if not enough jobs in that row
	if(count($jobs)==count($size) && (empty($size[$job])||strlen($value)>$size[$job]))$size[$job]=strlen($value);
    }
}
$totalcharas=array_sum($size)+12;

$widths=array();//array with proportions for each key
foreach($size AS $key=>$value)$widths[$key]=$size[$key]/$totalcharas;



//Date as first header

$h=12;
$w=280*(12/$totalcharas);

$pdf->Cell($w,$h,"Date",1,0,C,0);
foreach($colres AS $colrow)
{
    if($widths[$colrow->rota_task]>0)
    {
        
            $w=round(280*$widths[$colrow->rota_task]);
       
        
        $pdf->Cell($w,$h,"{$colrow->rota_task}",1,0,'C',0);
    } 
    
}

//end of add column headers
$a=1;
$h=6;

foreach($results AS $row)
{
      //date has changed
        $pdf->Ln();//add new line
        $date1=mysql2date('d/m/Y',$row->rota_date);
        $pdf->Cell(280*(12/$totalcharas),$h,"{$date1}",1,0,C,0);//print new date
        $a++;
	$jobs=unserialize($row->rota_jobs);
    //pull rota results for that date    
    foreach($jobs AS $key=>$value)    
    {
	
        $w=round(280*$widths[$key]);
        if(empty($value)){$text=' ';}else{$text=$value;}
        $pdf->Cell($w,$h,"$text",1,0,'C',0);
    }
}

$pdf->Output();


}


?>