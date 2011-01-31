<?php
//check cache admin exists
$dir=CHURCH_ADMIN_CACHE_PATH;
if(!(is_dir($dir)))
{
  if(!mkdir(CHURCH_ADMIN_CACHE_PATH,0755))die("There's no cache admin on the server and one couldn't be created. Please create a \"cache\" dircetory under the church_admin admin");
    
}


require("fpdf.php");
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
        $smallgroups[$row->group_name].=$row->name."\n";

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
$pdf->AddPage('L','mm','A4');
$pdf->SetFont('Arial','B',16);
$next_sunday=strtotime("this sunday");
$text='Small Group List '.date("d-m-Y",$next_sunday);
$pdf->Cell(0,10,$text,0,2,C);
$pageno+=1;



for($z=0;$z<=$counter-1;$z++)
	{
	if($w==6)
	{
	  $pdf->AddPage('L','mm','A4');
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
$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'sg.pdf',F);






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
//prepare vcard
require_once(CHURCH_ADMIN_INCLUDE_PATH.'vcf.php');
$v = new vCard();
$v->setPhoneNumber("{$row->homephone}", "PREF;HOME;VOICE");
$v->setPhoneNumber("{$row->cellphone}", "CELL;VOICE");
$v->setName("{$row->last_name}", "{$row->first_name}", "", "");
$v->setAddress("", stripslashes($row->address_line1), stripslashes($row->address_line2), stripslashes($row->city), stripslashes($row->state),stripslashes($row->zipcode) ,"");
$v->setEmail("{$row->email}");
if(!empty($row->children)){
$v->setNote("Children: ".stripslashes($row->children));
}
$output = $v->getVCard();
$filename = CHURCH_ADMIN_CACHE_PATH.$row->id.'.vcf';


$handle = fopen($filename, 'w');
fwrite($handle, $output);
fclose($handle);



//end prepare vcard
    }
    

    
//start of cache address-list.pdf    
$pdf=new FPDF();
$pageno=0;
$x=10;
$y=30;
$width=55;
global $pageno;
function newpage($pdf)
{
$pdf->AddPage('P','mm','A4');
$pdf->SetFont('Arial','B',24);
$text='Address List '.date("d-m-Y");
$pdf->Cell(0,20,$text,0,2,C);
$pdf->SetFont('Arial','',12);
$pageno+=1;
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

$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'addresslist.pdf',F);


//end of cache address list

//start of cache mailing labels!



require_once('PDF_Label.php');
$pdf = new PDF_Label('L7163', 'mm', 1, 2);
$pdf->Open();
$pdf->AddPage();

for($z=0;$z<=$counter-1;$z++)
{
    $add=$addresses['address'.$z][name]."\n".$addresses['label'.$z];
    $pdf->Add_Label($add);
}
$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'mailinglabel.pdf',F);
include(CHURCH_ADMIN_INCLUDE_PATH.'cache-rota.php');

//create visitor mailing labels
$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_visitors WHERE address_line1!='' AND regular!='1' ORDER BY last_name, first_name");    
$counter=1;
$addresses=array();
foreach ($results as $row) 
    {
$addresses['address'.$counter]=array();
$addresses['address'.$counter]['name']=html_entity_decode($row->first_name)." ".$row->last_name;
$addresses['address'.$counter]['address']=stripslashes($row->address_line1).', ';
$addresses['label'.$counter]=$row->address_line1;
if(!empty($row->address_line2)){$addresses['address'.$counter]['address'].=stripslashes($row->address_line2).', ';$addresses['label'.$counter].=",\n".stripslashes($row->address_line2);}
if(!empty($row->city)){$addresses['address'.$counter]['address'].=stripslashes($row->city).', ';$addresses['label'.$counter].=",\n".stripslashes($row->city);}
if(!empty($row->state)){$addresses['address'.$counter]['address'].=stripslashes($row->state).', ';$addresses['label'.$counter].=",\n".stripslashes($row->state);}
if(!empty($row->zipcode)){$addresses['address'.$counter]['address'].=stripslashes($row->zipcode).'.';$addresses['label'.$counter].=",\n".stripslashes($row->zipcode);}
  $counter+=1;
    }
$pdf = new PDF_Label('L7163', 'mm', 1, 2);
$pdf->Open();
$pdf->AddPage();

for($z=0;$z<=$counter-1;$z++)
{
    $add=$addresses['address'.$z][name]."\n".$addresses['label'.$z];
    $pdf->Add_Label($add);
}
$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'visitor_mailing_label.pdf',F);

?>