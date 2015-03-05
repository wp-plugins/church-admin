<?php

function church_admin_delete_rota_settings($id)
{
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql($id)."'");
     $result=$wpdb->get_results('SELECT * FROM '.CA_ROT_TBL );
            if($result)
            {
                foreach($result AS $row)
                {
                    $jobs=unserialize($row->rota_jobs);
                    unset($jobs[$id]);
                    $rota_jobs=esc_sql(serialize($jobs));
                    $wpdb->query('UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.$rota_jobs.'" WHERE rota_id="'.$row->rota_id.'"');
                }
            
            }
    require_once(plugin_dir_path(dirname(__FILE__)).'includes/admin.php');
    add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
    do_meta_boxes('church-admin','advanced',null);
    church_admin_rota_settings_list();
}

function church_admin_edit_rota_settings($id=NULL)
{
global $wpdb,$departments;
$wpdb->show_errors();
if(isset($_POST['rota_task'])&&check_admin_referer('edit_rota_settings'))
{
    
    $rota_task=esc_sql(stripslashes($_POST['rota_task']));
	$services=array();
	
	if(!empty($_POST['service_id'])){foreach($_POST['service_id'] AS $key=>$value){$services[]=(int)$value;}}else{$services=array();}
    if(!empty($_POST['department_id'])){(int)$department_id=esc_sql(stripslashes($_POST['department_id']));}else{$department_id="1";}
	if(!empty($_POST['initials'])){$initials=1;}else{$initials=0;}
    if(!$id)
    {//insert
        $id=$wpdb->get_var('SELECT rota_id FROM '.CA_RST_TBL.' WHERE rota_task="'.$rota_task.'"' );
        if(!$id)
        {
			$rota_order=$wpdb->get_var('SELECT MAX(rota_order) FROM '.CA_RST_TBL)+1;
			
            $sql='INSERT INTO '.CA_RST_TBL.' (rota_task,department_id,initials,rota_order,service_id) VALUES("'.$rota_task.'","'.$department_id.'","'.$initials.'","'.esc_sql($rota_order).'","'.esc_sql(serialize($services)).'")';
			
            $wpdb->query($sql);
            $job_id=$wpdb->insert_id;
    
            //add new job to current rota dates
            $result=$wpdb->get_results('SELECT * FROM '.CA_ROT_TBL );
            if($result)
            {
                foreach($result AS $row)
                {
                    $jobs=unserialize($row->rota_jobs);
					
                    $jobs[$job_id]=serialize(array());
                    $rota_jobs=esc_sql(serialize($jobs));
                    $wpdb->query('UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.$rota_jobs.'" WHERE rota_id="'.$row->rota_id.'"');
                }
            
            }
            echo'<div id="message" class="updated fade"><p><strong> Rota Job Added</strong></p></div>';
            require_once(plugin_dir_path(dirname(__FILE__)).'includes/admin.php');
            add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
            do_meta_boxes('church-admin','advanced',null);
            church_admin_rota_settings_list();  
        }else
        {
            $sql='UPDATE '.CA_RST_TBL.' SET rota_task="'.esc_sql(stripslashes($_POST['rota_task'])).'",service_id="'.esc_sql(serialize($services)).'",department_id="'.$department_id.'",initials="'.$initials.'" WHERE rota_id="'.esc_sql($id).'"';
            
            $wpdb->query($sql);
            echo'<div id="message" class="updated fade"><p><strong> Rota Job Updated</strong></p></div>';
            require_once(plugin_dir_path(dirname(__FILE__)).'includes/admin.php');
            add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
            do_meta_boxes('church-admin','advanced',null);
            church_admin_rota_settings_list();  
        }
    }//insert
    else
    {//update
        $sql='UPDATE '.CA_RST_TBL.' SET rota_task="'.esc_sql(stripslashes($_POST['rota_task'])).'",service_id="'.esc_sql(serialize($services)).'",department_id="'.$department_id.'",initials="'.$initials.'" WHERE rota_id="'.esc_sql($id).'"';
        
        $wpdb->query($sql);
        echo'<div id="message" class="updated fade"><p><strong> Rota Job Updated</strong></p></div>';
         require_once(plugin_dir_path(dirname(__FILE__)).'includes/admin.php');
        add_meta_box("church-admin-rota", __('Rota', 'church-admin'), "church_admin_rota_meta_box", "church-admin");
        do_meta_boxes('church-admin','advanced',null);
        church_admin_rota_settings_list();
    }//update
}
else
{
echo'<h1>'.__('Set up Rotas','church-admin').'</h1><h2>'.__('Edit a Rota Job','church-admin').'</h2><div class="wrap church_admin"><form action="" method="post">';
if ( function_exists('wp_nonce_field') ) wp_nonce_field('edit_rota_settings');
$rota_task=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."church_admin_rota_settings WHERE rota_id='".esc_sql($id)."'");
echo'<p><label>'.__('Rota Job','church-admin').':</label><input type="text" name="rota_task" ';
if(!empty($rota_task->rota_task)) echo'value="'.esc_sql($rota_task->rota_task).'"';
echo'/></p>';
echo'<p><label>'.__('Use Autocomplete','church-admin').'</label><input type="checkbox" name="department_id" value="1"';
if(!empty($rota_task->department_id)&&$rota_task->department_id>0) echo' checked="checked" ';
echo'/></p>';
echo'<p><label>'.__('Use Initials','church-admin').'</label><input type="checkbox" name="initials" value="1"';
if(!empty($rota_task->initials)&&$rota_task->initials>0) echo' checked="checked" ';
echo'/></p>';
echo'<p><label>'.__('Which Services need this task?','church-admin').'</label>';
$current_services=unserialize($rota_task->service_id);
$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
		if(!empty($services))
		{
			$ser=array();
			foreach($services AS $service)
			{
				echo'<input type="checkbox" name="service_id[]" value="'.$service->service_id.'" ';
				if(is_array($current_services) && (in_array($service->service_id,$current_services))) echo' checked="checked" ';
				echo'/>'.$service->service_name.'<br/>';
			}
		}
echo'<p class="submit"><input type="submit" name="edit_rota_setting" value="'.__('Save Rota Job','church-admin').' &raquo;" /></p></form>
</div>';
}
}

function church_admin_rota_settings_list()
{
    //outputs the list of rota jobs
global$wpdb,$departments;
echo '<div class="wrap church_admin"><h2>Rota Jobs</h2>';
echo '<p><a href="'.wp_nonce_url("admin.php?page=church_admin/index.php&amp;action=church_admin_edit_rota_settings",'edit_rota_settings').'">Add a rota job</a></p>';
echo'<p>Rota tasks can be sorted by drag and drop, for use in other parts of the plugin.</p>';
$rota_results=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_order ASC');
if(!empty($rota_results))
{
       echo '<table class="widefat" id="sortable"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Rota Task','church-admin').'</th><th>'.__('How chosen','church-admin').'</th><th>'.__('Which Services?','church-admin').'</th><th>'.__('Initials?','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Rota Task','church-admin').'</th><th>'.__('How chosen','church-admin').'</th><th>'.__('Which Services?','church-admin').'</th><th>'.__('Initials?','church-admin').'</th></tr></tfoot><tbody  class="content">';
    foreach($rota_results AS $rota_row)
    {
        $rota_edit_url='admin.php?page=church_admin/index.php&action=church_admin_edit_rota_settings&id='.$rota_row->rota_id;
        $rota_delete_url='admin.php?page=church_admin/index.php&action=church_admin_delete_rota_settings&id='.$rota_row->rota_id;
        if(!empty($rota_row->department_id)&&$rota_row->department_id>0){$how='Autocomplete';}else{$how='Entered Manually';}
		if(!empty($rota_row->initials)){$initials=__('Yes','church-admin');}else{$initials=__('No','church-admin');}
		$ser=array();
		$services=maybe_unserialize($rota_row->service_id);
		foreach($services AS $key=>$value){$ser[]=$wpdb->get_var('SELECT service_name FROM '.CA_SER_TBL .' WHERE service_id="'.esc_sql($value).'"');}
        echo '<tr class="sortable-row" id="'.$rota_row->rota_id.'"><td><a href="'.wp_nonce_url($rota_edit_url, 'edit_rota_settings').'">[Edit]</a></td><td><a href="'.wp_nonce_url(        $rota_delete_url, 'delete_rota_settings').'">[Delete]</a></td><td>'.esc_html(stripslashes($rota_row->rota_task)).'</td><td>'.esc_html($how).'</td><td>'.implode('<br/>',$ser).'</td><td>'.esc_html($initials).'</td></tr>';
    }
    echo'</tbody></table></div>';
        echo '
    <script type="text/javascript">
  
 jQuery(document).ready(function($) {
 
    var fixHelper = function(e,ui){
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };
    var sortable = $("#sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order
        
       
				var Order = "order="+$(this).sortable(\'toArray\').toString();

        console.log(Order);
        
        $.ajax({
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=rota_settings",
            type: "post",
            data:  Order,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {
                console.log("Saved.");
            }
        });}
});
$("#sortable tbody.content").disableSelection();
});

   
   
    </script>
';
}
}

?>
