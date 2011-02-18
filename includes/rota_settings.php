<?php
function church_admin_add_rota_settings()
{
global $wpdb;
$wpdb->show_errors();

if(!empty($_POST['rota_task']) )
{
    //form submitted
    $wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_rota_settings (rota_task) VALUES('".esc_sql(stripslashes($_POST['rota_task']))."')");
    echo'<div id="message" class="updated fade"><p><strong> Rota Job Added</strong></p></div>';
    church_admin_rota_settings_list();
}
else{
echo'<h1>Set up Rotas</h1><h2>Add a Rota Job</h2><div class="wrap church_admin"><form action="" method="post">';
echo'<ul><label>Rota Job:</label><input type="text" name="rota_task" /></li></ul>';
echo'<p class="submit"><input type="submit" name="add_rota_setting" value="Add Rota Job &raquo;" /></p></form>
</div>';
}
}

function church_admin_delete_rota_settings($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql($id)."'");
 
    church_admin_rota_settings_list();
}

function church_admin_edit_rota_settings($id)
{
global $wpdb;
$wpdb->show_errors();
if(isset($_POST['rota_task'])&&check_admin_referer('edit_rota_settings'))
{
    //form submitted
    $wpdb->query("UPDATE ".$wpdb->prefix."church_admin_rota_settings SET rota_task='".esc_sql(stripslashes($_POST['rota_task']))."' WHERE rota_id='".esc_sql($id)."'");
    church_admin_rota_settings_list();
}
else
{
echo'<h1>Set up Rotas</h1><h2>Edit a Rota Job</h2><div class="wrap church_admin"><form action="" method="post">';
if ( function_exists('wp_nonce_field') ) wp_nonce_field('edit_rota_settings');
$rota_task=$wpdb->get_var("SELECT rota_task FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql($id)."'");
echo'<ul><label>Rota Job:</label><input type="text" name="rota_task" value="'.esc_sql($rota_task).'" /></li></ul>';
echo'<p class="submit"><input type="submit" name="edit_rota_setting" value="Edit Rota Job &raquo;" /></p></form>
</div>';
}
}

function church_admin_rota_settings_list()
{
    //outputs the list of rota jobs
global$wpdb;
echo '<div class="wrap church_admin"><h2>Rota Jobs</h2>';
echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_add_rota_settings",'add_rota_settings').'">Add a rota job</a></p>';
$rota_results=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings");
if(!empty($rota_results))
{
       echo '<table class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Rota Task</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Rota Task</th></tr></tfoot><tbody>';
    foreach($rota_results AS $rota_row)
    {
        $rota_edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota_settings&id='.$rota_row->rota_id;
        $rota_delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota_settings&id='.$rota_row->rota_id;
        echo '<tr><td><a href="'.wp_nonce_url($rota_edit_url, 'edit_rota_settings').'">[Edit]</a></td><td><a href="'.wp_nonce_url(        $rota_delete_url, 'delete_rota_settings').'">[Delete]</a></td><td>'.esc_html(stripslashes($rota_row->rota_task)).'</td></tr>';
    }
    echo'</tbody></table></div>';
}
}

?>