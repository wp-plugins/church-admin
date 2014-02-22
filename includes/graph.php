<?php

function church_admin_weekly_attendance_graph($year,$service_id)
{

	global $wpdb;
	
	
	/* pChart library inclusions */
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pData.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pDraw.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pImage.class.php");
	
	
	//build data
	$result=$wpdb->get_results('SELECT *,WEEK(`date`) AS week FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND YEAR(`date`)="'.esc_sql($year).'" ORDER BY `date` ASC');
	if(!empty($result))
	{
		$adults=$children=$date=array();
		foreach($result AS $row)
		{
			$adults[]=$row->adults;
			$children[]=$row->children;
			$date[]=$row->week;
		}
		$MyData = new pData();   
		$MyData->addPoints($adults,'Adults');
		$MyData->addPoints($children,'Children');
		$MyData->setAxisName(0,"Attendance");
		$MyData->addPoints($date,"Labels");
		$MyData->setSerieDescription("Labels","Dates");
		$MyData->setAbscissa("Labels");
	
		/* Create the pChart object */ 
		$myPicture = new pImage(700,500,$MyData); 

		/* Draw the background */ 
		$Settings = array("R"=>255, "G"=>255, "B"=>255); 
		$myPicture->drawFilledRectangle(0,0,700,500,$Settings); 

		
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,699,499,array("R"=>0,"G"=>0,"B"=>0)); 
		/* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/Forgotte.ttf","FontSize"=>11)); 
		$myPicture->drawText(250,20,"Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

		/* Draw the scale and the 1st chart */ 
		$myPicture->setGraphArea(40,40,660,460); 
		$myPicture->drawFilledRectangle(40,40,660,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE)); 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/pf_arma_five.ttf","FontSize"=>6)); 
		$myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO)); 
		$myPicture->setShadow(FALSE); 

		
		
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,475,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 
		
		
		$myPicture->render(CHURCH_ADMIN_EMAIL_CACHE.'weekly-attendance'.$year.'.png'); 
	}//end result

}

function church_admin_monthly_attendance_graph($year,$service_id)
{

	global $wpdb;
	
	
	/* pChart library inclusions */
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pData.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pDraw.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pImage.class.php");
	
	
	//build data
	$result=$wpdb->get_results('SELECT ROUND(AVG(adults)) as adults,ROUND(AVG(children)),MONTH(`date`) AS month FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'" AND YEAR(`date`)="'.esc_sql($year).'" GROUP BY MONTH(`date`) ASC');
	if(!empty($result))
	{
		$adults=$children=$date=array();
		foreach($result AS $row)
		{
			$adults[]=$row->adults;
			$children[]=$row->children;
			$date[]=$row->week;
		}
		$MyData = new pData();   
		$MyData->addPoints($adults,'Adults');
		$MyData->addPoints($children,'Children');
		$MyData->setAxisName(0,"Attendance");
		$MyData->addPoints($date,"Labels");
		$MyData->setSerieDescription("Labels","Dates");
		$MyData->setAbscissa("Labels");
	
		/* Create the pChart object */ 
		$myPicture = new pImage(700,500,$MyData); 

		/* Draw the background */ 
		$Settings = array("R"=>255, "G"=>255, "B"=>255); 
		$myPicture->drawFilledRectangle(0,0,700,500,$Settings); 

		
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,699,499,array("R"=>0,"G"=>0,"B"=>0)); 
		/* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/Forgotte.ttf","FontSize"=>11)); 
		$myPicture->drawText(250,20,"Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

		/* Draw the scale and the 1st chart */ 
		$myPicture->setGraphArea(40,40,660,460); 
		$myPicture->drawFilledRectangle(40,40,660,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE)); 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/pf_arma_five.ttf","FontSize"=>6)); 
		$myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO)); 
		$myPicture->setShadow(FALSE); 

		
		
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,475,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 
		
		
		$myPicture->render(CHURCH_ADMIN_EMAIL_CACHE.'monthly-attendance'.$year.'.png'); 
	}//end result

}

function church_admin_rolling_attendance_graph($service_id)
{

	global $wpdb;
	
	
	/* pChart library inclusions */
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pData.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pDraw.class.php");
	require_once(CHURCH_ADMIN_INCLUDE_PATH."graph/class/pImage.class.php");
	
	
	//build data
	$result=$wpdb->get_results('SELECT ROUND(AVG(rolling_adults)) as adults,ROUND(AVG(rolling_children)),`date` FROM '.CA_ATT_TBL.' WHERE service_id="'.esc_sql($service_id).'"  ORDER BY `date` ASC ');
	if(!empty($result))
	{
		$adults=$children=$date=array();
		foreach($result AS $row)
		{
			$adults[]=$row->adults;
			$children[]=$row->children;
			$date[]=mysql2date('M/Y',$row->date);
		}
		$MyData = new pData();   
		$MyData->addPoints($adults,'Adults');
		$MyData->addPoints($children,'Children');
		$MyData->setAxisName(0,"Attendance");
		$MyData->addPoints($date,"Labels");
		$MyData->setSerieDescription("Labels","Dates");
		$MyData->setAbscissa("Labels");
	
		/* Create the pChart object */ 
		$myPicture = new pImage(700,500,$MyData); 

		/* Draw the background */ 
		$Settings = array("R"=>255, "G"=>255, "B"=>255); 
		$myPicture->drawFilledRectangle(0,0,700,500,$Settings); 

		
		/* Add a border to the picture */ 
		$myPicture->drawRectangle(0,0,699,499,array("R"=>0,"G"=>0,"B"=>0)); 
		/* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/Forgotte.ttf","FontSize"=>11)); 
		$myPicture->drawText(250,20,"Attendance ".$year,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

		/* Draw the scale and the 1st chart */ 
		$myPicture->setGraphArea(40,40,660,460); 
		$myPicture->drawFilledRectangle(40,40,660,460,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10)); 
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE)); 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		$myPicture->setFontProperties(array("FontName"=>CHURCH_ADMIN_INCLUDE_PATH."graph/fonts/pf_arma_five.ttf","FontSize"=>6)); 
		$myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO)); 
		$myPicture->setShadow(FALSE); 

		
		
		/* Write the chart legend */ 
		$myPicture->drawLegend(510,475,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 
		
		
		$myPicture->render(CHURCH_ADMIN_EMAIL_CACHE.'rolling-average-attendance'.$year.'.png'); 
	}//end result

}


?>
