<?php
//Set up header for admin
add_action('admin_head', 'church_admin_header');
function church_admin_header()
{
echo'<link rel="stylesheet" href="'.CHURCH_ADMIN_INCLUDE_URL.'admin.css" type="text/css" media="all" />
<script type="text/javascript">
function toggle(obj){
	var div1 = document.getElementById(obj)
	if (div1.style.display == \'none\') {
		div1.style.display = \'block\'
	} else {
		div1.style.display = \'none\'
	}
};
</script>
';    
}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{
    echo'<link rel="stylesheet" href="'.CHURCH_ADMIN_INCLUDE_URL.'public.css" type="text/css" media="all" />';    
}
?>