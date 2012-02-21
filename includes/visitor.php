<?php
/*
 This file deals with visitors
church_admin_add_visitor() - adds the visitor
church_admin_visitor_form($visitordata) is the form
church_admin_move_visitor($id) adds a visitor to the main directory
*/
function church_admin_add_visitor()
{
    global$wpdb;
if(check_admin_referer( 'add_visitor') &&!empty($_POST['first_name'])&&!empty($_POST['last_name']))
{
    //form submitted
    if(empty($_POST['small_group'])||$_POST['smallgroup']=='not yet')$_POST['small_group']='0';
    if(!checkDateFormat($_POST['contacted'])) $_POST['contacted']='0000-00-00';
    if(!checkDateFormat($_POST['returned']))$_POST['returned']='0000-00-00';
    if(!checkDateFormat($_POST['first_sunday'])) $_POST['first_sunday']='0000-00-00';
    if(isset($_POST['regular'])&&$_POST['regular']=='1') {$sqlsafe['regular']=1;}else{$sqlsafe['regular']=0;}
    $sql = "INSERT INTO ".$wpdb->prefix."church_admin_visitors SET first_sunday='".$wpdb->escape($_POST['first_sunday'])."',contacted='".$wpdb->escape($_POST['contacted'])."',contacted_by='".$wpdb->escape($_POST['contacted_by'])."', returned='".$wpdb->escape($_POST['returned'])."', first_name = '".$wpdb->escape($_POST['first_name'])."',last_name     = '".$wpdb->escape($_POST['last_name'])."', email= '".$wpdb->escape($_POST['email'])."', small_group= '".$wpdb->escape($_POST['small_group'])."', address_line1 = '".$wpdb->escape($_POST['address_line1'])."',          address_line2 = '".$wpdb->escape($_POST['address_line2'])."', city = '".$wpdb->escape($_POST['city'])."',state= '".$wpdb->escape($_POST['state'])."',zipcode       = '".$wpdb->escape($_POST['zipcode'])."', homephone     = '".$wpdb->escape($_POST['homephone'])."',cellphone     = '".$wpdb->escape($_POST['cellphone'])."',children = '".$wpdb->escape($_POST['children'])."',regular='".$wpdb->escape($_POST['regular'])."'";
    $wpdb->query($sql)  ;

    church_admin_visitor_list();
}
else
{
    echo'<div class="wrap church_admin"><h2>Add Visitor</h2><form action="" name="visitor" method="post">';
        if ( function_exists('wp_nonce_field') )wp_nonce_field('add_visitor');
        echo church_admin_visitor_form(NULL);
        echo'<p class="submit"><input type="submit" name="add_visitor" value="Add Visitor &raquo;" /></p></form></div>';
}
}

function church_admin_delete_visitor($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_visitors WHERE id='".esc_sql($id)."'");
       echo '<div id="message" class="updated fade"><p>Visitor deleted</p></div>';
church_admin_visitor_list();
}
function church_admin_edit_visitor($id)
{
global $wpdb;
$wpdb->show_errors();
if(check_admin_referer('edit_visitor')&&current_user_can('manage_options')&&isset($_POST['edit_visitor']) )
{
$sqlsafe=array();    
foreach($_POST AS $key=>$value)
{
 $sqlsafe[$key]=esc_sql($value);   
}
if(isset($_POST['regular'])&&$_POST['regular']=='1') {$sqlsafe['regular']=1;}else{$sqlsafe['regular']=0;}
$sql="UPDATE ".$wpdb->prefix."church_admin_visitors SET first_name='{$sqlsafe['first_name']}',last_name='{$sqlsafe['last_name']}',address_line1='{$sqlsafe['address_line1']}',address_line2='{$sqlsafe['address_line2']}',city='{$sqlsafe['city']}',state='{$sqlsafe['state']}',zipcode='{$sqlsafe['zipcode']}',email='{$sqlsafe['email']}',homephone='{$sqlsafe['homephone']}',cellphone='{$sqlsafe['cellphone']}',children='{$sqlsafe['children']}',first_sunday='{$sqlsafe['first_sunday']}',contacted='{$sqlsafe['contacted']}',contacted_by='{$sqlsafe['contacted_by']}',returned='{$sqlsafe['returned']}',small_group='{$sqlsafe['small_group']}', why='{$sqlsafe['why']}',regular='{$sqlsafe['regular']}' WHERE id='".esc_sql($id)."'";

    $wpdb->query($sql);
    echo '<div id="message" class="updated fade"><p>Visitor edited</p></div>';
church_admin_visitor_list();
}
else
{
$visitordata=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_visitors WHERE id=$id");
echo '<div class="wrap church_admin"><h2>Edit Visitor</h2><form action="" id="add_visitor" name="visitor" method="post">';
if ( function_exists('wp_nonce_field') ) wp_nonce_field('edit_visitor');
echo church_admin_visitor_form($visitordata);
echo '<p class="submit"><input type="submit" name="edit_visitor" value="Edit Visitor &raquo;" /></p></form></div>';
}
}




function church_admin_visitor_form($visitordata)
{
    global $wpdb;
echo'<script type="text/javascript" src="'.CHURCH_ADMIN_INCLUDE_URL.'javascript.js"></script>
<script type="text/javascript">document.write(getCalendarStyles());</script>
<script type="text/javascript">
var cal_begin = new CalendarPopup(\'pop_up_cal1\');
var cal_begin = new CalendarPopup(\'pop_up_cal2\');
var cal_begin = new CalendarPopup(\'pop_up_cal3\');
</script>
<ul>
<li><label >Address name:</label><input type="text" name="first_name" value="'.esc_html(stripslashes($visitordata->first_name)).'" /></li>
<li><label >Last name:</label><input type="text" name="last_name" value="'.esc_html(stripslashes($visitordata->last_name)).'" /></li>
<li><label>Email Address:</label><input type="text" name="email" value="'.esc_html(stripslashes($visitordata->email)).'" /></li>
<li><label>Children:</label><input type="text" name="children" value="'.esc_html(stripslashes($visitordata->children)).'"/></li>
<li><label>Address Line 1:</label><input type="text" name="address_line1" value="'.esc_html(stripslashes($visitordata->address_line1)).'" /></li>
<li><label>Address Line 2:</label><input type="text" name="address_line2" value="'.esc_html(stripslashes($visitordata->address_line2)).'" /></li>
<li><label>Town:</label><input type="text" name="city" value="'.esc_html(stripslashes($visitordata->city)).'" /></li>
<li><label>County/State:</label><input type="text" name="state" value="'.esc_html(stripslashes($visitordata->state)).'" /></li>
<li><label>Postcode:</label><input type="text" name="zipcode" value="'.esc_html(stripslashes($visitordata->zipcode)).'" /></li>
<li><label>Phone:</label><input type="text" name="homephone" value="'.esc_html(stripslashes($visitordata->homephone)).'" /></li>
<li><label>Mobile:</label><input type="text" name="cellphone" value="'.esc_html(stripslashes($visitordata->cellphone)).'" /></li>
</ul>  
<h3>Visit Details</h3>
<ul>
<li><label>Reason for visit</label><select name="why">
';
if(isset($visitordata->why))
{
    echo '<option value="'.$visitordata->why.'">';
    switch($visitordata->why)
   {
        case 1: echo 'Just Visiting';break;
        case 2: echo 'Non Christian';break;
        case 3: echo 'Moved to Area';break;
        case 4: echo 'Moved Church';break;
        case 5: echo 'Lost To Church';break;
        case 6: echo 'Pioneer';break;
    }
echo '</option>';
}        
echo '<option value="1">Just Visiting</option><option value="2">Non Christian</option><option value="3">Moved to Area</option><option value="4">Moving Church</option><option value="5">Lost to the Church</option><option value="6">Pioneer</option></select></li>    

<li><label>Date of visit (yyyy-mm-dd):</label><input type="text" name="first_sunday" class="input" size="12" value="';
if(empty($visitordata->first_sunday))
{
    echo date('Y-m-d',strtotime("last Sunday"));
}
else
{
    echo $visitordata->first_sunday;
}
echo'" />
<a href="#" onclick="cal_begin.select(document.forms[\'visitor\'].first_sunday,\'date_anchor1\',\'yyyy-MM-dd\'); return false;" name="date_anchor1" id="date_anchor1">Select date</a><div id="pop_up_cal1" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div>
</li>
<li><label>Contacted (yyyy-mm-dd):</label><input type="text" name="contacted" class="input" size="12" value="'.esc_html($visitordata->contacted).'" /><a href="#" onclick="cal_begin.select(document.forms[\'visitor\'].contacted,\'date_anchor2\',\'yyyy-MM-dd\'); return false;" name="date_anchor2" id="date_anchor2">Select date</a><div id="pop_up_cal2" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div>
</li>
<li><label>Contacted by:</label><input type="text" name="contacted_by" value="'.esc_html(stripslashes($visitordata->contacted_by)).'" /></li>
<li><label>Returned (yyyy-mm-dd):</label><input type="text" name="returned" class="input" size="12" value="'.esc_html(stripslashes($visitordata->returned)).'" /><a href="#" onclick="cal_begin.select(document.forms[\'visitor\'].returned,\'date_anchor3\',\'yyyy-MM-dd\'); return false;" name="date_anchor3" id="date_anchor3">Select date</a><div id="pop_up_cal3" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;z-index:1;"></div></li>
<li><label>Small Group:</label><select name="small_group"><option value="0">Not yet</option>';
$lgsql="SELECT * FROM ".$wpdb->prefix."church_admin_smallgroup";
$lgresults = $wpdb->get_results($lgsql);
foreach ($lgresults as $row) 
{
    echo '<option value="'.absint($row->id).'">'.esc_html(stripslashes($row->group_name)).'</option>';
}				
echo'</select></li>
<li><label>Regular attender?</label><input type="checkbox" name="regular" value="1"/></li>
</ul>
';    
}

function church_admin_move_visitor($id)
{
global $wpdb;
$wpdb->show_errors();
$row=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_visitors WHERE id='$id'");
$check=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_directory WHERE first_name='".$wpdb->prepare($row->first_name)."' AND last_name='".$wpdb->prepare($row->last_name)."' AND email='".$wpdb->prepare($row->email)."' ");
if(empty($check))
{
    $result=$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_directory (first_name,last_name,children,address_line1,address_line2,city,state,zipcode,homephone,cellphone,email)
VALUES('".$wpdb->prepare($row->first_name)."','".$wpdb->prepare($row->last_name)."','".$wpdb->prepare($row->children)."','".$wpdb->prepare($row->address_line1)."','".$wpdb->prepare($row->address_line2)."','".$wpdb->prepare($row->city)."', '".$wpdb->prepare($row->state)."','".$wpdb->prepare($row->zipcode)."','".$wpdb->prepare($row->homephone)."','".$wpdb->prepare($row->cellphone)."','".$wpdb->prepare($row->email)."')");
 $result2=$wpdb->query("UPDATE ".$wpdb->prefix."church_admin_visitors SET regular='1' WHERE id=$id");
    echo '<div id="message" class="updated fade"><p>Visitor added to main address list</p></div>';
    
}
else{echo '<div id="message" class="updated fade"><p>Visitor already in main address list</p></div>';}
church_admin_visitor_list();
}

function church_admin_visitor_list()
{
    global$wpdb;
    $reason=array('1'=>'Just Visiting','2'=>'Non Christian','3'=>'Moved to Area','4'=>'Moved Church','5'=>'Lost To Church');
    $wpdb->show_errors();
echo '<div class="wrap church_admin"><h2>Visitor List</h2><p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_visitor",'add_visitor').'">Add a visitor</a></p>';
//only output pie chart if there are visitors stored
$visitorcount=$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."church_admin_visitors");
if(!empty($visitorcount)&&$visitorcount>0)
{

if(!file_exists(CHURCH_ADMIN_CACHE_PATH.'visitorpiechart.png')){church_admin_visitor_pie_chart();}
echo'<p><img src="'.CHURCH_ADMIN_CACHE_URL.'visitorpiechart.png" alt="Visitor Pie Chart" width="420" height="200" /></p>';
echo'<table class="widefat" ><thead><tr><th>Edit</th><th>Delete</th><th>Move</th><th>Visited</th><th>Name</th><th>Reason</th><th>Regular?</th><th>Home phone</th><th>Cell phone</th><th>Contacted</th><th>Contacted by</th><th>Returned</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Move</th><th>Visited</th><th>Name</th><th>Home phone</th><th>Cell phone</th><th>Contacted</th><th>Contacted by</th><th>Returned</th></tr></tfoot><tbody>';
$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_visitors ORDER BY first_sunday DESC");
$counter=1;
foreach ($results as $row)
{
    if($row->regular==1) {$class='class="church_admin_regular" ';$regular='Yes';}else{$class=$reg='';}
    if($row->contacted=='0000-00-00') $row->contacted='';
    if($row->returned=='0000-00-00') $row->returned='';
    /*
     $_SESSION['address'.$counter]=array();
    $_SESSION['address'.$counter]['name']=htmlentities($row->first_name)." ".$row->last_name;
    $_SESSION['address'.$counter]['address']=stripslashes($row->address_line1).",\r\n" ;
    if(!empty($row->address_line2))$_SESSION['address'.$counter]['address'].=stripslashes($row->address_line2).",\r\n" ;
    if(!empty($row->city))$_SESSION['address'.$counter]['address'].=stripslashes($row->city).",\r\n" ;
    if(!empty($row->state))$_SESSION['address'.$counter]['address'].=stripslashes($row->state).",\r\n" ;
    if(!empty($row->zipcode))$_SESSION['address'.$counter]['address'].=stripslashes($row->zipcode).'.';
    */
    echo '<tr '.$class.'><td><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&action=church_admin_edit_visitor&id=".$row->id,'edit_visitor').'">[Edit]</a></td><td><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_delete_visitor&id=".$row->id,'delete_visitor').'">[Delete]</a></td><td><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&action=church_admin_move_visitor&id=".$row->id,'move_visitor').'" title="Move to main address list" >[Add]</a></td><td>'.mysql2date('d/m/Y',$row->first_sunday).'</td><td>';
    if(!empty ($row->email))echo "<a href=\"mailto:{$row->email}\">";
    if(!empty($row->last_name)) echo $row->last_name.", ";
    echo htmlentities($row->first_name);
    if(!empty ($row->email))echo "</a></td>";
    echo'<td>'.$reason[$row->why].'</td><td>'.$regular.'</td>';
    echo "<td>".$row->homephone."</td><td>".$row->cellphone."</td><td>".$row->contacted."</td><td>".$row->contacted_by."</td><td>".$row->returned."</td></tr>";
    $counter++;
    $class='';
}
echo'</tbody></table></div>';

}
}//end of result

function church_admin_visitor_pie_chart()
{
	global $wpdb;
    //count visitors
$sql="SELECT first_name,children,why FROM ".$wpdb->prefix."church_admin_visitors";
$adults=0;
$children=0;
$results=$wpdb->get_results($sql);
foreach($results AS $row)
{
$adults+=count(explode("&",$row->first_name)   );
$children+=count(explode(",",$row->children));
$why[$row->why]+=count(explode("&",$row->first_name))+count(explode(",",$row->children));
}
echo "<p>$adults adults and $children children have visited</p>";
 //build pchart
 // Standard inclusions     
 include("graph/pChart/pData.class");  
 include("graph/pChart/pChart.class");  
  
 // Dataset definition   
 $DataSet = new pData;  
 $DataSet->AddPoint($why,"Serie1");  
 
$label=array(1=>'Just Visiting',2=>'Non-Christian',3=>'Moved to the area',4=>'Moved Church',5=>'Lost to the Church',6=>'Pioneer');
$DataSet->AddPoint($label,"Serie2");  
 $DataSet->AddAllSeries();  
 $DataSet->SetAbsciseLabelSerie("Serie2"); 
 // Initialise the graph  
 $Test = new pChart(420,200);  
 $Test->drawFilledRoundedRectangle(7,7,443,193,5,240,240,240);  
 $Test->drawRoundedRectangle(5,5,445,195,5,230,230,230);  
   $Test->setColorPalette(0,234,158,46); 
 $Test->setColorPalette(1,118,224,46); 
 // Draw the pie chart  
 $Test->setFontProperties(CHURCH_ADMIN_INCLUDE_PATH."graph/Fonts/tahoma.ttf",8);  
 $Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),150,90,110,PIE_PERCENTAGE,TRUE,50,20,5);  
 $Test->drawPieLegend(280,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
 $Test->Render(CHURCH_ADMIN_CACHE_PATH.'visitorpiechart.png');
}

?>