<?php

$wpdb->show_errors();
$sql="SELECT ROUND(AVG(adults))as adults,ROUND(AVG(children)) as children,date,date_format(date, '%b %Y')AS shortdate FROM ".$wpdb->prefix."church_admin_attendance GROUP BY shortdate ORDER BY date ASC";

$result=$wpdb->get_results($sql);
//print_r($result);
foreach($result AS $row)
{
    $adults[]=$row->adults;
    $children[]=$row->children;
    $total[]=$row->adults+$row->children;
    $dates[]=$row->shortdate;
}
// Standard inclusions   
 require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/pChart/pData.class");
 require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/pChart/pChart.class");

 // Dataset definition 
 $DataSet = new pData;
 $DataSet->AddPoint($adults,"Serie1");
 $DataSet->AddPoint($children,"Serie2");
 $DataSet->AddPoint($total,"Serie3");
 $DataSet->AddPoint($dates,"Serie4");
 $DataSet->AddSerie("Serie1");
 $DataSet->AddSerie("Serie2");
 $DataSet->AddSerie("Serie3");
 $DataSet->SetAbsciseLabelSerie("Serie4");
 $DataSet->SetSerieName("Adults","Serie1");
 $DataSet->SetSerieName("Children","Serie2");
 $DataSet->SetSerieName("Total","Serie3");
 $DataSet->SetYAxisName("Monthly Average Attendance");
  
 // Initialise the graph   
 $Test = new pChart(1000,500);
 $Test->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",8);   
 $Test->setGraphArea(70,30,980,430);   
 $Test->drawFilledRoundedRectangle(7,7,993,793,5,240,240,240);   
 $Test->drawRoundedRectangle(5,5,995,795,5,230,230,230);   
 $Test->drawGraphArea(255,255,255,TRUE);
 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,90,2);   
 $Test->drawGrid(4,TRUE,230,230,230,50);
  
 // Draw the 0 line   
 $Test->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",6);   
 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);   
  
 // Draw the line graph
 $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
 $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);   
  
 // Finish the graph   
 $Test->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",8);   
 $Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);   
 $Test->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",10);   
 $Test->drawTitle(60,22,"Attendance ",50,50,50,585);
$Test->Render(CHURCH_ADMIN_CACHE_PATH.'attendance-graph.png');
 
?>