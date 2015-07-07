<?php
function address_xml($member_type_id)
{
    global $wpdb;
    // Start XML file, create parent node
    $doc = domxml_new_doc("1.0");
    $node = $doc->create_element("markers");
    $parnode = $doc->append_child($node);


// Select all the rows in the markers table
$query = 'SELECT '.CA_HOU_TBL.' FROM markers WHERE member_type_id="'.esc_sql($member_type_id).'"';
$result = $wpdb->get_results($sql);

header("Content-type: text/xml");

//Iterate through the rows, adding XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
  // ADD TO XML DOCUMENT NODE
  $node = $doc->create_element("marker");
  $newnode = $parnode->append_child($node);

  $newnode->set_attribute("name", ent2ncr($row['name']));
  $newnode->set_attribute("address", ent2ncr($row['address']));
  $newnode->set_attribute("lat", ent2ncr($row['lat']));
  $newnode->set_attribute("lng", ent2ncr($row['lng']));
  $newnode->set_attribute("type", ent2ncr($row['type']));
}

$xmlfile = $doc->dump_mem();
echo $xmlfile;

}
?>