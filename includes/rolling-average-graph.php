<?php

$wpdb->show_errors();
$sql="SELECT ROUND(AVG(rolling_adults))as rolling_adults,ROUND(AVG(rolling_children)) as rolling_children,date,date_format(date, '%b %Y')AS shortdate FROM ".$wpdb->prefix."church_admin_attendance GROUP BY shortdate ORDER BY date ASC";

$result=$wpdb->get_results($sql);
//print_r($result);
foreach($result AS $row)
{
    $rolling_adults[]=$row->rolling_adults;
    $rolling_children[]=$row->rolling_children;
    $rolling_total[]=$row->rolling_adults+$row->rolling_children;
    $rolling_dates[]=$row->shortdate;
}
// Standard inclusions   
require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/pChart/pData.class");
 require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/pChart/pChart.class");

 // Dataset definition 
 $DataSet1 = new pData;
 $DataSet1->AddPoint($rolling_adults,"Serie1");
 $DataSet1->AddPoint($rolling_children,"Serie2");
 $DataSet1->AddPoint($rolling_total,"Serie3");
 $DataSet1->AddPoint($rolling_dates,"Serie4");
 $DataSet1->AddSerie("Serie1");
 $DataSet1->AddSerie("Serie2");
 $DataSet1->AddSerie("Serie3");
 $DataSet1->SetAbsciseLabelSerie("Serie4");
 $DataSet1->SetSerieName("Adults","Serie1");
 $DataSet1->SetSerieName("Children","Serie2");
 $DataSet1->SetSerieName("Total","Serie3");
 $DataSet1->SetYAxisName("Attendance");
  
 // Initialise the graph   
 $Test1 = new pChart(1000,500);
 $Test1->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",8);   
 $Test1->setGraphArea(70,30,980,430);   
 $Test1->drawFilledRoundedRectangle(7,7,993,793,5,240,240,240);   
 $Test1->drawRoundedRectangle(5,5,995,795,5,230,230,230);   
 $Test1->drawGraphArea(255,255,255,TRUE);
 $Test1->drawScale($DataSet1->GetData(),$DataSet1->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,90,2);   
 $Test1->drawGrid(4,TRUE,230,230,230,50);
  
 // Draw the 0 line   
 $Test1->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",6);   
 $Test1->drawTreshold(0,143,55,72,TRUE,TRUE);   
  
 // Draw the line graph
 $Test1->drawLineGraph($DataSet1->GetData(),$DataSet1->GetDataDescription());   
 $Test1->drawPlotGraph($DataSet1->GetData(),$DataSet1->GetDataDescription(),3,2,255,255,255);   
  
 // Finish the graph   
 $Test1->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",8);   
 $Test1->drawLegend(75,35,$DataSet1->GetDataDescription(),255,255,255);   
 $Test1->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",10);   
 $Test1->drawTitle(60,22,"Rolling Average Attendance ",50,50,50,585);
$Test1->Render(CHURCH_ADMIN_CACHE_PATH.'rolling_average_attendance.png');
 
?>