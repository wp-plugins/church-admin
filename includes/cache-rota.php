<?php
$percent=array();
$headers=array();


$totalcharas=12;//allow for date in output
//grab character count from largest results
$now=date('Y-m-d');
$threemonths=date('Y-m-d',strtotime('+3 months'));
$sql="SELECT ".$wpdb->prefix."church_admin_rota.rota_option_id, MAX( LENGTH( ".$wpdb->prefix."church_admin_rota.who ) )AS whocount,LENGTH(".$wpdb->prefix."church_admin_rota_settings.rota_task) AS title,".$wpdb->prefix."church_admin_rota.rota_option_id FROM  ".$wpdb->prefix."church_admin_rota,".$wpdb->prefix."church_admin_rota_settings WHERE ".$wpdb->prefix."church_admin_rota.rota_date>='$now' AND ".$wpdb->prefix."church_admin_rota.rota_date<'$threemonths' AND ".$wpdb->prefix."church_admin_rota.rota_option_id=".$wpdb->prefix."church_admin_rota_settings.rota_id  GROUP BY ".$wpdb->prefix."church_admin_rota.rota_option_id ";
//echo $sql;
$results=$wpdb->get_results($sql);
if(!empty($results))
{
    
   
    foreach($results AS $row)
    {
        //grab count of character from the larger of  longest entry or its title
        if($row->title >= $row->whocount )
        {
        $totalcharas+=$row->title;
        $percent[$row->rota_option_id]=$row->title;  
        }else
        {
        $totalcharas+=$row->whocount;
        $percent[$row->rota_option_id]=$row->whocount;
        }
    }
//print_r($percent);

require_once(CHURCH_ADMIN_INCLUDE_PATH.'fpdf.php');
$pdf=new FPDF();
$pdf->AddPage('L',get_option('church_admin_pdf_size'));
$pdf->SetFont('Arial','B',16);
$text='Sunday Rota '.date("d-m-Y");
$pdf->Cell(0,10,$text,0,2,C);
$pdf->SetFont('Arial','B',8);


//add column headers
$colres=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings ORDER BY rota_id");
//Date as first header

$h=12;
$w=280*(12/$totalcharas);

$pdf->Cell($w,$h,"Date",1,0,C,0);
foreach($colres AS $colrow)
{
    if($percent[$colrow->rota_id]>0)
    {
        
            $w=round(280*($percent[$colrow->rota_id]/$totalcharas));
       
        
        $pdf->Cell($w,$h,"{$colrow->rota_task}",1,0,C,0);
    } 
    
}

//end of add column headers
$a=1;
$h=6;
$option_id=array_keys($percent);

//get dates from now till three months time
$dateresult=$wpdb->get_results("SELECT DISTINCT rota_date FROM ".$wpdb->prefix."church_admin_rota WHERE rota_date>='$now' AND rota_date<'$threemonths' ORDER BY ".$wpdb->prefix."church_admin_rota.rota_date");
foreach($dateresult AS $date)
{
      //date has changed
        $pdf->Ln();//add new line
        $date1=mysql2date('d/m/Y',$date->rota_date);
        $pdf->Cell(280*(12/$totalcharas),$h,"{$date1}",1,0,C,0);//print new date
        $a++;
    //pull rota results for that date    
    foreach($option_id AS $id)    
    {
    $item=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_rota  WHERE rota_date='".$date->rota_date."' AND rota_option_id='$id'");
    
        $w=round(280*($percent[$id]/$totalcharas));
        if(empty($item->who)){$text=' ';}else{$text=$item->who;}
        $pdf->Cell($w,$h,"$text",1,0,C,0);
    }
}

$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'rota.pdf',F);


}
?>