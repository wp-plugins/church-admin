<?php
//Address Directory Functions

function church_admin_add_address()
{
//this function adds an address to the address directory
global $wpdb;
if(!empty($_POST['first_name'])&&!empty($_POST['last_name'])&&check_admin_referer('church_admin_add_address'))
{
if(empty($_POST['small_group']))$_POST['small_group']='0';
$sql = "INSERT INTO ".$wpdb->prefix."church_admin_directory SET first_name    = '".$wpdb->escape($_POST['first_name'])."',last_name     = '".$wpdb->escape($_POST['last_name'])."',email= '".$wpdb->escape($_POST['email'])."',email2        = '".$wpdb->escape($_POST['email2'])."',website       = '".$wpdb->escape($_POST['website'])."',small_group       = '".$wpdb->escape($_POST['small_group'])."',address_line1 = '".$wpdb->escape($_POST['address_line1'])."', address_line2 = '".$wpdb->escape($_POST['address_line2'])."',city          = '".$wpdb->escape($_POST['city'])."', state         = '".$wpdb->escape($_POST['state'])."', zipcode       = '".$wpdb->escape($_POST['zipcode'])."', homephone     = '".$wpdb->escape($_POST['homephone'])."', cellphone     = '".$wpdb->escape($_POST['cellphone'])."', children         = '".$wpdb->escape($_POST['children'])."'";
$wpdb->query($sql)  ;
require(CHURCH_ADMIN_INCLUDE_PATH.'cache_addresslist.php');
church_admin_directory();
}
else
{
    echo '<div class="wrap church_admin">';
    echo'<h2>Add Address</h2>';
    echo '<form action="" method="post">';
    if ( function_exists('wp_nonce_field') ) wp_nonce_field('church_admin_add_address');
    echo church_admin_directory_form(); 
    echo '<p class="submit"><input type="submit" name="new" value="Add Address &raquo;" /></p></form></div>';
}
}
function church_admin_edit_address($id)
{
    global $wpdb;
    $wpdb->show_errors();
$sql = "SELECT * FROM ".$wpdb->prefix."church_admin_directory WHERE id='".$wpdb->escape($id)."'";
$data = $wpdb->get_row($sql);

if ($_POST['save']&&check_admin_referer('edit_address'))
{
    $wpdb->query("UPDATE ".$wpdb->prefix."church_admin_directory SET first_name    = '".$wpdb->escape($_POST['first_name'])."', last_name     = '".$wpdb->escape($_POST['last_name'])."', children='".$wpdb->escape($_POST['children'])."',email = '".$wpdb->escape($_POST['email'])."', email2= '".$wpdb->escape($_POST['email2'])."',homephone     = '".$wpdb->escape($_POST['homephone'])."',cellphone     = '".$wpdb->escape($_POST['cellphone'])."',address_line1 = '".$wpdb->escape($_POST['address_line1'])."',address_line2 = '".$wpdb->escape($_POST['address_line2'])."',city = '".$wpdb->escape($_POST['city'])."',state         = '".$wpdb->escape($_POST['state'])."',zipcode       = '".$wpdb->escape($_POST['zipcode'])."',children         = '".$wpdb->escape($_POST['children'])."',website       = '".$wpdb->escape($_POST['website'])."',small_group       = '".$wpdb->escape($_POST['small_group'])."'WHERE id ='".$wpdb->escape($id)."'");
require(CHURCH_ADMIN_INCLUDE_PATH.'cache_addresslist.php');
church_admin_directory();
}
else
{ 
    echo'<div class="wrap church_admin"><h2>Edit Address</h2><form action="" method="post">';
   if ( function_exists('wp_nonce_field') ) wp_nonce_field('edit_address');
    echo church_admin_directory_form($data); 
    echo '<p class="submit"><input type="submit" name="save" value="Save &raquo;" /></p></form></div>';
}
}

function church_admin_delete_address($id)
{
    global $wpdb;

    $sql="DELETE FROM ".$wpdb->prefix."church_admin_directory WHERE id='".esc_sql($id)."'";
    $wpdb->query($sql);
    church_admin_directory();
}

function church_admin_directory_form($data='null')
{
 global $wpdb;
if (!$data) {$website = 'http://'; }else{ $website = $data->website;}
$out = '
<ul>
<li><label>Address name:</label><input type="text" name="first_name" value="'.esc_html(stripslashes($data->first_name)).'" /></li>
<li><label >Last name:</label><input type="text" name="last_name" value="'.esc_html(stripslashes($data->last_name)).'" /></li>
<li><label >Children:</label><input type="text" name="children" value="'.esc_html(stripslashes($data->children)).'" /></li>
<li><label >Email Address:</label><input type="text" name="email" value="'.esc_html(stripslashes($data->email)).'" /></li>
<li><label>Email Address 2:</label><input type="text" name="email2" value="'.esc_html(stripslashes($data->email2)).'" /></li>
<li><label >Home phone:</label><input type="text" name="homephone" value="'.esc_html(stripslashes($data->homephone)).'" /></li>
<li><label >Cell phone:</label><input type="text" name="cellphone" value="'.esc_html(stripslashes($data->cellphone)).'" /></li>
';
//get life groups
$out.='<li><label >Small Group:</label><select name="small_group">';$lgsql="SELECT * FROM ".$wpdb->prefix."church_admin_smallgroup";
$lgresults = $wpdb->get_results($lgsql);
$option='';
foreach ($lgresults as $row) 
{
    if($data->small_group==$row->id)
    {
	$first='<option value="'.absint($row->id).'" selected="selected">'.esc_html(stripslashes($row->group_name)).'</option>';
    }
    else $option.='<option value="'.absint($row->id).'">'.esc_html(stripslashes($row->group_name)).'</option>';
}
$out.=$first.$option;
$out.='	</select></li>';
			
$out.='<li><label >Address Line 1:</label><input type="text" name="address_line1" value="'.esc_html(stripslashes($data->address_line1)).'" /></li><li><label >Address Line 2:</label><input type="text" name="address_line2" value="'.esc_html(stripslashes($data->address_line2)).'" /></li><li><label>Town:</label><input type="text" name="city" value="'.esc_html(stripslashes($data->city)).'" /></li><li><label >County/State:</label><input type="text" name="state" value="'.esc_html(stripslashes($data->state)).'" /></li><li><label >Postcode:</label><input type="text" name="zipcode" value="'.esc_html(stripslashes($data->zipcode)).'" /></li><li><label>Website:</label><input type="text" name="website" value="'.esc_html(stripslashes($website)).'" /></li></ul>
';

return $out;
}


function church_admin_directory()
{
    global $wpdb,$church_admin_version;
//header
    $directory='<div class="wrap church_admin"><div id="donatebox"><p>This is version '.get_option("church_admin_version").' of the <strong>Church Admin</strong> plugin by Andy Moyle.<br/><strong>Latest News</strong></p>';
  require(CHURCH_ADMIN_INCLUDE_PATH.'news-feed.php');
  $directory.=church_admin_news_feed();
$directory.='<p><a href="http://www.themoyles.co.uk/web-development/church-admin-wordpress-plugin/plugin-support">Get Support</a><br/>If you like the plugin, please buy me a cup of coffee!...<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="R7YWSEHFXEU52">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form></p></div>
<h2>Church Admin - Main Address List</h2>';
if(OLD_CHURCH_ADMIN_VERSION!=$church_admin_version) echo '<div class="updated fade"><p><strong>'.CHURCH_ADMIN_LATEST_MESSAGE.'</strong></p></div>';
//link to add an address
$directory.='<p><a href="admin.php?page=church_admin/index.php&amp;action=church_admin_add_address">Add Address</a></p>';

//grab directory
$sql = "SELECT * FROM ".$wpdb->prefix."church_admin_directory ORDER BY last_name, first_name";
$results = $wpdb->get_results($sql);
if(!empty($results))
{
    $directory.='<p><a href="admin.php?page=church_admin/index.php&amp;action=refreshcache">Refresh PDF cache</a></p>';     

//only output pdf links if already created
if(file_exists(CHURCH_ADMIN_CACHE_PATH.'addresslist.pdf'))
{
$directory.='<form name="guideform" action="" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="'.CHURCH_ADMIN_URL.'cache/addresslist.pdf">-- Choose a pdf --</option>
<option value="'.CHURCH_ADMIN_URL.'cache/mailinglabel.pdf">Church - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</option><option value="'.CHURCH_ADMIN_URL.'cache/visitor_mailing_label.pdf">Visitors - Avery &reg; '.get_option('church_admin_label').' Mailing Labels</option>
<option value="'.CHURCH_ADMIN_URL.'cache/addresslist.pdf">Address List PDF</option><option value="'.CHURCH_ADMIN_URL.'cache/sg.pdf">Small Group List PDF</option>
<option value="'.CHURCH_ADMIN_URL.'cache/rota.pdf">Sunday Rota List PDF</option><option value="'.CHURCH_ADMIN_URL.'cache/year_planner.pdf">A4 Year Planner PDF</option></select></form>';
}

//table header
$directory.='<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Name</th><th>Email address</th><th>Home phone</th><th>Cell phone</th><th>Last update</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Name</th><th>Email address</th><th>Home phone</th><th>Cell phone</th><th>Last update</th></tr></tfoot><tbody>';
$counter=1;
foreach ($results as $row)
{
    $edit_url="admin.php?page=church_admin/index.php&action=church_admin_edit_address&id=".$row->id;
    $delete_url="admin.php?page=church_admin/index.php&action=church_admin_delete_address&id=".$row->id;
    //put entry into session array for vcards
    $_SESSION['address'.$counter]=array();
    $_SESSION['address'.$counter]['name']=htmlentities($row->first_name)." ".$row->last_name;
    $_SESSION['address'.$counter]['address']=stripslashes($row->address_line1).",\r\n" ;
    if(!empty($row->address_line2))$_SESSION['address'.$counter]['address'].=stripslashes($row->address_line2).",\r\n" ;
    if(!empty($row->city))$_SESSION['address'.$counter]['address'].=stripslashes($row->city).",\r\n" ;
    if(!empty($row->state))$_SESSION['address'.$counter]['address'].=stripslashes($row->state).",\r\n" ;
    if(!empty($row->zipcode))$_SESSION['address'.$counter]['address'].=stripslashes($row->zipcode).'.';
    //table row for directory
    $directory.="<tr><td><a href=\"".wp_nonce_url($edit_url,'edit_address')."\">[Edit]</a></td><td><a href=\"".wp_nonce_url($delete_url,'delete_address')."\">[Delete]</a></td><td>".htmlentities($row->last_name).", ".htmlentities($row->first_name)."</td>
<td>".$row->email."</td><td>".$row->homephone."</td><td>".$row->cellphone."</td><td>".$row->ts."</td></tr>";
    $counter++;
}
$directory.='</tbody></table> ';


}//if results
echo $directory;
}
?>