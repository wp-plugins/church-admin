<?php
//2011-03-08 fixed calendar CSS for 20:20
//2011-03-22 fixed toggle on calendar pages
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
    global $church_admin_version;
    echo'<link rel="stylesheet" type="text/css" media="all" href="'.CHURCH_ADMIN_INCLUDE_URL.'public.css"  />
<!-- church_admin v'.$church_admin_version.'-->
    <style>table.church_admin_calendar{width:';
    if(get_option('church_admin_calendar_width')){echo get_option('church_admin_calendar_width');}else {echo'700';}
    echo 'px !important;}</style>
    <script type="text/javascript">
    function toggle(obj)
    {
    var div1 = document.getElementById(obj)
    if (div1.style.display == \'none\')
    {
    div1.style.display = \'block\'
    }
    else
    {
    div1.style.display = \'none\'
    }
    };
    </script>';
}
?>