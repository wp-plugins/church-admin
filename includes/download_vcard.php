<?php
$id=(int)$_GET['id'];
$file='../cache/'.$id.'.vcf';
$output =file_get_contents($file);
$filename=$id.'.vcf';
header('Content-Type: text/x-vcard; charset=utf-8');
 header("Content-Disposition: attachment; filename=$filename");
Header("Content-Length: ".strlen($output));
Header("Connection: close");
echo $output;
?>