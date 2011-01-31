<?php
include("../../includes/config.inc.php");
//initialise arrays
$bookings=array();
$adults=array();
$children=array();

//grab bookings
$sql="SELECT *,DATE_FORMAT(booking_date,'%D %b') AS booking_date FROM booking WHERE payment_type>'0' AND cancelled='0' ORDER BY booking_date ";
$result=mysql_query($sql);

while($row=mysql_fetch_assoc($result))
{
//increment booking for each date
$bookings[$row['booking_date']]+=1;

//increment adults
$adult_sql="SELECT COUNT(ticket_id) AS adult FROM ticket WHERE booking_id={$row['booking_id']} AND date_of_birth='0000-00-00'";
$adult_result=mysql_query($adult_sql);
while($adult_row=mysql_fetch_assoc($adult_result))
	{
	$adults[$row['booking_date']]+=$adult_row['adult'];
	
	}
//increment children	
$child_sql="SELECT COUNT(ticket_id) AS child FROM ticket WHERE booking_id={$row['booking_id']} AND date_of_birth>'0000-00-00'";
$child_result=mysql_query($child_sql);
while($child_row=mysql_fetch_assoc($child_result))
	{
	$children[$row['booking_date']]+=$child_row['child'];
	}	
}
$dates=array_keys($bookings);
$acc_adults=array();
$acc_children=array();
$acc_total=array();
$acc_adults[$dates[0]]=$adults[$dates[0]];
$acc_children[$dates[0]]=$children[$dates[0]];
$acc_total[$dates[0]]=$adults[$dates[0]]+$acc_children[$dates[0]];
for($x=1;$x<=count($adults);$x++)
{
$y=$x-1;
$acc_adults[$dates[$x]]=$adults[$dates[$x]]+$acc_adults[$dates[$y]];
$acc_children[$dates[$x]]=$children[$dates[$x]]+$acc_children[$dates[$y]];
$acc_total[$dates[$x]]=$acc_adults[$dates[$x]]+$acc_children[$dates[$y]];
//echo " {$dates[$x]}  {$dates[$y]} Today {$adults[$dates[$x]]} Yesterday {$acc_adults[$dates[$y]]} Cumulative Adults {$acc_adults[$dates[$x]]}<br/>";
}
 // Standard inclusions   
 include("pChart/pData.class");
 include("pChart/pChart.class");

 // Dataset definition 
 $DataSet = new pData;
 $DataSet->AddPoint($acc_adults,"Serie1");
 $DataSet->AddPoint($acc_children,"Serie2");
 $DataSet->AddPoint($acc_total,"Serie3");
 $DataSet->AddPoint($dates,"Serie4");
 $DataSet->AddAllSeries();
 $DataSet->SetAbsciseLabelSerie("Serie4");
 $DataSet->SetSerieName("Adults","Serie1");
 $DataSet->SetSerieName("Children","Serie2");
 $DataSet->SetSerieName("Total","Serie3");
 $DataSet->SetYAxisName("Bookings");
 
 // Initialise the graph   
 $Test = new pChart(700,500);
 $Test->setFontProperties("Fonts/tahoma.ttf",8);   
 $Test->setGraphArea(70,30,680,430);   
 $Test->drawFilledRoundedRectangle(7,7,693,493,5,240,240,240);   
 $Test->drawRoundedRectangle(5,5,695,495,5,230,230,230);   
 $Test->drawGraphArea(255,255,255,TRUE);
 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,90,2);   
 $Test->drawGrid(4,TRUE,230,230,230,50);
  
 // Draw the 0 line   
 $Test->setFontProperties("Fonts/tahoma.ttf",6);   
 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);   
  
 // Draw the line graph
 $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
 $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);   
  
 // Finish the graph   
 $Test->setFontProperties("Fonts/tahoma.ttf",8);   
 $Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);   
 $Test->setFontProperties("Fonts/tahoma.ttf",10);   
 $Test->drawTitle(60,22,"Together at East of England Bookings ",50,50,50,585);
   header ("Content-type: image/png"); 
 $Test->Stroke();
?>