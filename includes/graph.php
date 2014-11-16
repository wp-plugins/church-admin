<?php

function church_admin_weekly_attendance_graph($year,$service_id)
{
	global $wpdb;
	/* pChart library inclusions */
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pData.class.php');
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pDraw.class.php');
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pImage.class.php');
	//build data

	$result=$wpdb->get_results('SELECT *, DATE_FORMAT(`date`,"%D %b") AS week FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND YEAR(`date`)="'.esc_sql($year).'" ORDER BY `date` ASC');

	if(!empty($result))
	{
		$total=$adults=$children=$date=array();
		foreach($result AS $row)
		{
			$total[]=$row->adults+$row->children;
			$adults[]=$row->adults;
			$children[]=$row->children;
			$date[]=$row->week;
		}
		$MyData = new pData(); 
		$MyData->addPoints($total,'Total');
		$MyData->setSerieWeight("Total",2);		
		$MyData->addPoints($adults,'Adults');
		$MyData->setSerieWeight("Adults",2);
		$MyData->addPoints($children,'Children');
		$MyData->setSerieWeight("Children",2);
		$MyData->setAxisName(0,"Attendance");
		$MyData->addPoints($date,"Labels");
		$MyData->setSerieDescription("Labels","Dates");
		$MyData->setAbscissa("Labels");

		/* Create the pChart object */ 

		$myPicture = new pImage(900,500,$MyData); 



		/* Draw the background */ 
		$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
		$myPicture->drawFilledRectangle(0,0,900,500,$Settings);
		 
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,899,499,array("R"=>0,"G"=>0,"B"=>0)); 
		/* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/Forgotte.ttf',"FontSize"=>11)); 
		$myPicture->drawText(250,30,"Weekly Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 
		/* Draw the scale and the 1st chart */ 
		$myPicture->setGraphArea(40,40,860,460); 
		$myPicture->drawFilledRectangle(40,40,860,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE)); 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/pf_arma_five.ttf',"FontSize"=>6)); 
		$myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_MANUAL,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0)); 
		$myPicture->setShadow(FALSE); 
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,475,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/church-admin-cache/';
		$myPicture->render($path.'weekly-attendance'.$year.'.png'); 
	}//end result
}

function church_admin_monthly_attendance_graph($year,$service_id)
{

global $wpdb;

	/* pChart library inclusions */

	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pData.class.php');

	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pDraw.class.php');

	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pImage.class.php');

	

	

	//build data

	$result=$wpdb->get_results('SELECT ROUND(AVG(adults)) as adults,ROUND(AVG(children)) AS children,MONTHNAME(`date`) AS month FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND YEAR(`date`)="'.esc_sql($year).'" GROUP BY MONTH(`date`) ASC');

	if(!empty($result))

	{

		$adults=$children=$date=$total=array();

		foreach($result AS $row)

		{
			$total[]=$row->adults + $row->children;
			$adults[]=$row->adults;

			$children[]=$row->children;

			$date[]=$row->month;

		}

		$MyData = new pData();   
		$MyData->addPoints($total,'Total');
		$MyData->setSerieWeight("Total",2);
		$MyData->addPoints($adults,'Adults');
		$MyData->setSerieWeight("Adults",2);
		$MyData->addPoints($children,'Children');
		$MyData->setSerieWeight("Children",2);
		$MyData->setAxisName(0,"Attendance");

		$MyData->addPoints($date,"Labels");

		$MyData->setSerieDescription("Labels","Dates");

		$MyData->setAbscissa("Labels");



		/* Create the pChart object */ 

		$myPicture = new pImage(700,500,$MyData); 



		/* Draw the background */ 
		$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
		$myPicture->drawFilledRectangle(0,0,700,500,$Settings);
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,699,499,array("R"=>0,"G"=>0,"B"=>0)); 

		/* Write the chart title */  

		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/Forgotte.ttf',"FontSize"=>11)); 

		$myPicture->drawText(250,20,"Monthly Average Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 



		/* Draw the scale and the 1st chart */ 

		$myPicture->setGraphArea(40,40,660,460); 

		$myPicture->drawFilledRectangle(40,40,660,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 

		$myPicture->drawScale(array("DrawSubTicks"=>TRUE)); 

		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 

		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/pf_arma_five.ttf',"FontSize"=>10));	$myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_MANUAL,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0)); 
		$myPicture->setShadow(FALSE); 
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,30,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/church-admin-cache/';

		$myPicture->render($path.'monthly-attendance'.$year.'.png'); 

	}//end result
}



function church_admin_rolling_attendance_graph($service_id)
{
	global $wpdb;
	/* pChart library inclusions */
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pData.class.php');
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pDraw.class.php');
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/graph/class/pImage.class.php');
	//update rolling average
	$result=$wpdb->get_results('SELECT attendance_id FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'"');
	foreach($result AS $row)
	{
		//update rolling average-attendance
		//work out rolling average from values!

		$avesql='SELECT FORMAT(AVG(adults),0) AS rolling_adults,FORMAT(AVG(children),0) AS rolling_children FROM '.CA_ATT_TBL.' WHERE attendance_id="'.esc_sql($row->attendance_id).'" AND service_id="'.esc_sql($service_id).'"';
		$averow=$wpdb->get_row($avesql);
		//update table with rolling average
         $up='UPDATE '.CA_ATT_TBL.' SET rolling_adults="'.$averow->rolling_adults.'", rolling_children="'.$averow->rolling_children.'" WHERE attendance_id="'.esc_sql($attendance_id).'"';
		$wpdb->query($up);
	 }
	//build data
	$sql='SELECT rolling_adults as adults,rolling_children AS children,date_format(`date`,"%b %Y") AS date FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" GROUP BY YEAR(`date`), MONTH(`date`) ';
	$result=$wpdb->get_results($sql);

	if(!empty($result))

	{

		$total=$adults=$children=$date=array();

		foreach($result AS $row)

		{
			$total[]=$row->adults + $row->children;
			$adults[]=$row->adults;

			$children[]=$row->children;

			$date[]=$row->date;

		}

		$MyData = new pData();   
		$MyData->addPoints($total,'Total');
		$MyData->setSerieWeight("Total",2);
		$MyData->addPoints($adults,'Adults');
		$MyData->setSerieWeight("Adults",2);
		$MyData->addPoints($children,'Children');
		$MyData->setSerieWeight("Children",2);
		$MyData->setAxisName(0,"Attendance");
		$MyData->addPoints($date,"Labels");
		$MyData->setSerieDescription("Labels","Dates");
		$MyData->setAbscissa("Labels");

	

		/* Create the pChart object */ 
		$myPicture = new pImage(900,550,$MyData); 
		$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
		$myPicture->drawFilledRectangle(0,0,900,550,$Settings);
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,899,549,array("R"=>0,"G"=>0,"B"=>0,"Ticks"=>TRUE)); 
		/* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/Forgotte.ttf',"FontSize"=>11)); 
		$myPicture->drawText(250,30,"Rolling Average Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 
		/* Draw the scale and the 1st chart */ 
		$myPicture->setGraphArea(40,40,860,460); 
		$myPicture->drawFilledRectangle(40,40,860,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE,"DrawArrows"=>TRUE,"ArrowSize"=>6,'LabelSkip'=>5));
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		$myPicture->setFontProperties(array("FontName"=>plugin_dir_path(dirname(__FILE__)).'includes/graph/Fonts/pf_arma_five.ttf',"FontSize"=>6)); 
		$myPicture->drawLineChart(array("DisplayValues"=>FALSE)); 
		$myPicture->setShadow(FALSE); 
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,30,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 
		$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/church-admin-cache/';
		$myPicture->render($path.'rolling-average-attendance.png'); 
	}//end result



}
?>