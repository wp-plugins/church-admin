<?php
function  church_admin_member_type()
{
    global $wpdb;
    $member_type=church_admin_member_type_array();
    
    if(isset($_POST['current']))
    {
        $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_type_id="'.esc_sql((int)$_POST['reassign']).'" WHERE member_type_id="'.esc_sql((int)$_POST['current']).'"');
        $wpdb->query('UPDATE '.CA_HOU_TBL.' SET member_type_id="'.esc_sql((int)$_POST['reassign']).'" WHERE member_type_id="'.esc_sql((int)$_POST['current']).'"');
        echo'<div class="updated fade"><p>'.__('People reassigned','church-admin').'</p></div>';
    }
    echo'<h2>'.__('Member Types','church-admin').'</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type','edit_member_type').'">'.__('Add Member Type','church-admin').'</a></p>';
    echo'<p>'.__('Member Types can be sorted by drag and drop, for use in other parts of the plugin','church-admin').'</p>';
    echo'<table id="sortable" class="widefat"><thead><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Type','church-admin').'</th><th>'.__('Reassign','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Edit','church-admin').'</th><th>'.__('Delete','church-admin').'</th><th>'.__('Type','church-admin').'</th><th>'.__('Reassign','church-admin').'</th></tr></tfoot><tbody class="content">';
    
    foreach($member_type AS $id=>$membertype)
    {
        $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type&amp;member_type_id='.$id,'edit_member_type').'">'.__('Edit','church_admin').'</a>';
        $check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($id).'"');    
        if(!$check)
        {
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_member_type&member_type_id='.$id,'delete_member_type').'">'.__('Delete','church_admin').'</a>';
            $reassign='';
        }
        else
        {
            $delete=$check .' '.__('people who are','church_admin').' '.$membertype;
            $reassign='<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_member_type" method="post">'.__('Reassign people to','church_admin').' ';
            $reassign.='<select name="reassign">';
            foreach($member_type AS $mtid=>$value)if($mtid!=$id && $mtid!=$_POST['current']) $reassign.='<option value="'.$mtid.'">'.$value.'</option>';
            $reassign.='</select><input type="hidden" name="current" value="'.$id.'"/><input type="submit" value="Reassign"/></form>';
        }
            
        
        echo'<tr class="sortable-row" id="'.$id.'"><td>'.$edit.'</td><td>'.$delete.'</td><td>'.$membertype.'</td><td>'.$reassign.'</td></tr>';
       
    }
    echo'</tbody></table>';
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
            url: "admin.php?page=church_admin/index.php&action=church_admin_update_order&which=member_type",
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

function church_admin_edit_member_type($member_type_id=NULL)
{
    global $wpdb;
    
    if(isset($_POST['edit_member_type']))
    {
        if($member_type_id)
        {
            $wpdb->query('UPDATE '.CA_MTY_TBL.' SET member_type="'.esc_sql(stripslashes($_POST['member_type'])).'" WHERE member_type_id="'.esc_sql($member_type_id).'"');
        }
        else
        {
            $nextorder=1+$wpdb->get_var('SELECT member_type_order FROM '.CA_MTY_TBL.' ORDER BY member_type_order LIMIT 1');
            $wpdb->query('INSERT INTO '.CA_MTY_TBL.'(member_type_order,member_type)VALUES("'.esc_sql($nextorder).'","'.esc_sql(stripslashes($_POST['member_type'])).'")');
        }
        
        echo'<div class="updated fade"><p>'.__('Member Type Updated','church_admin').'</p></div>';
        church_admin_member_type();
    }
    else
    {
	
        
        echo'<div class="wrap church_admin"><h2>';
        if($member_type_id){echo' '.__('Edit','church_admin').' ';}else{echo __('Add','church_admin').' ';}
        echo _e('Member Type','church_admin').'</h2><form action="" method="POST">';
        echo'<p><label>'.__('Member Type','church_admin').'</label><input type="text" name="member_type" ';
        if(!empty($member_type_id))
	{
	    $type=$wpdb->get_var('SELECT member_type FROM '.CA_MTY_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"');
	    echo'value="'.$type.'" ';
	}
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_member_type" value="yes"/><input type="submit" value="'.__('Save Member Type','church_admin').' &raquo;" /></p></form></div>';
        
    }
}
function church_admin_delete_member_type($member_type_id=NULL)
{
    global $wpdb;
    $wpdb->show_errors();
    if($member_type_id)
    {
        $wpdb->query('DELETE FROM '.CA_MTY_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"');
        echo'<div class="updated fade"><p><strong>'.__('Member Type Deleted','church_admin').'</strong></p></div>';
    }
    church_admin_member_type();
}
?>