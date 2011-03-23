<?php
function church_admin_cache_year_planner()
{
    global $wpdb;
//check cache admin exists
$dir=CHURCH_ADMIN_CACHE_PATH;
if(!(is_dir($dir)))
{
  if(!mkdir(CHURCH_ADMIN_CACHE_PATH,0755))die("There's no cache admin on the server and one couldn't be created. Please create a \"cache\" directory under the church_admin admin");
   
}

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
$pdf->Cell(0,8,$title,0,0,C);
$pdf->SetFont('Arial','B',10);

//Get initial Values
$initial_month='01';
$initial_year='2011';
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
    $pdf->Cell(70,7,$monthname.' '.$this_year,0,0,C);
    //position to top left corner of calendar month 
    $y+=7;
    $pdf->SetXY($x,$y);
    $pdf->SetFont('Arial','',8);
    //print daylegend
    for($legend=0;$legend<=6;$legend++)
    {
        $pdf->Cell(10,5,$days[$legend],1,0,C);
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
                $pdf->Cell(10,5,'',1,0,L,TRUE);
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
                    $pdf->Cell(10,5,$counter,1,0,L,TRUE);
                    $counter++;
                }
                else
                {
                //end of month, so back to grey background
                $pdf->SetFillColor('192','192','192');
                $pdf->Cell(10,5,'',1,0,C,TRUE);
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
    $pdf->Cell(15,5,' ',0,0,L,1);
    $pdf->SetFillColor(255,255,255);
    $pdf->Cell(15,5,$row->category,0,0,L);
    $pdf->SetXY($x,$y);
    $pdf->Cell(45,5,'',1);
    $y+=6;
}
$pdf->Output(CHURCH_ADMIN_CACHE_PATH.'year_planner.pdf',F);

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