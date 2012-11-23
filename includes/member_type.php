<?php
function  church_admin_member_type()
{
    global $wpdb;
    $member_type=church_admin_member_type_array();
    
    if(isset($_POST['current']))
    {
        $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_type_id="'.esc_sql((int)$_POST['reassign']).'" WHERE member_type_id="'.esc_sql((int)$_POST['current']).'"');
        $wpdb->query('UPDATE '.CA_HOU_TBL.' SET member_type_id="'.esc_sql((int)$_POST['reassign']).'" WHERE member_type_id="'.esc_sql((int)$_POST['current']).'"');
        echo'<div class="updated fade"><p>People reassigned</p></div>';
    }
    echo'<h2>Member Types</h2>';
    echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type','edit_member_type').'">Add Member Type</a></p>';
    echo'<p>Member Types can be sorted by drag and drop, for use in other parts of the plugin.</p>';
    echo'<table id="sortable" class="widefat"><thead><tr><th>Edit</th><th>Delete</th><th>Type</th><th>Reassign</th></tr></thead><tfoot><tr><th>Edit</th><th>Delete</th><th>Type</th><th>Reassign</th></tr></tfoot><tbody class="content">';
    
    foreach($member_type AS $id=>$membertype)
    {
        $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_edit_member_type&amp;member_type_id='.$id,'edit_member_type').'">Edit</a>';
        $check=$wpdb->get_var('SELECT COUNT(*) FROM '.CA_PEO_TBL.' WHERE member_type_id="'.esc_sql($id).'"');    
        if(!$check)
        {
            $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=church_admin_delete_member_type&member_type_id='.$id,'delete_member_type').'">Delete</a>';
            $reassign='';
        }
        else
        {
            $delete=$check .' people who are '.$membertype;
            $reassign='<form action="admin.php?page=church_admin/index.php&amp;action=church_admin_member_type" method="post">Reassign people to ';
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
    $mtype=get_option('church_admin_member_type');
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
        
        echo'<div class="updated fade"><p>Member Type Updated</p></div>';
        church_admin_member_type();
    }
    else
    {
        if($member_type_id)
        {
            foreach($mtype AS $order => $member_type)if($member_type['id']==$member_type_id)$type=$member_type['type'];
        }
        echo'<div class="wrap church_admin"><h2>';
        if($member_type_id){echo' Edit ';}else{echo 'Add ';}
        echo'Member Type</h2><form action="" method="POST">';
        echo'<p><label>Member Type</label><input type="text" name="member_type" ';
        if(!empty($type)) echo'value="'.$type.'" ';
        echo'/></p>';
        echo'<p class="submit"><input type="hidden" name="edit_member_type" value="yes"/><input type="submit" value="Save Member Type&raquo;" /></p></form></div>';
        
    }
}
function church_admin_delete_member_type($member_type_id=NULL)
{
    global $wpdb;
    $wpdb->show_errors();
    if($member_type_id)
    {
        $wpdb->query('DELETE FROM '.CA_MTY_TBL.' WHERE member_type_id="'.esc_sql($member_type_id).'"');
        echo'<div class="updated fade"><p><strong>Member Type Deleted</strong></p></div>';
    }
    church_admin_member_type();
}
?>