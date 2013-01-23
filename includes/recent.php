<?php
function church_admin_recent_display($month)
{
    global $wpdb,$member_type;
    foreach($member_type AS $type_id=>$type)
    {
        $sql='SELECT a.*,b.* FROM '.CA_PEO_TBL.' a, '.CA_HOU_TBL.' b WHERE a.household_id=b.household_id AND a.last_update>DATE_SUB(NOW(), INTERVAL '.esc_sql($month).' MONTH) AND a.member_type_id ="'.esc_sql($type_id).'"';
        $results=$wpdb->get_results($sql);
        if($results)
        {
            echo'<h2>'.$type.' '.__('activity for last','church-admin').' '.$month.' '.__('month(s)','church-admin').'</h2>';
            echo'<table><thead><tr><th>'.__('Date','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Phone','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Next Action','church-admin').'</th><th>'.__('Assign to','church-admin').'</th></tr></thead><tfoot><tr><th>'.__('Date','church-admin').'</th><th>'.__('Name','church-admin').'</th><th>'.__('Address','church-admin').'</th><th>'.__('Mobile','church-admin').'</th><th>'.__('Phone','church-admin').'</th><th>'.__('Email','church-admin').'</th><th>'.__('Next Action','church-admin').'</th><th>'.__('Assign to','church-admin').'</th></tr></tfoot><tbody>';
            foreach($results AS $row)
            {
                $assign=$next_action='coming soon';
                $address=implode(', ',array_filter(unserialize($row->address)));
                echo'<tr><td>'.mysql2date(get_option('date_format'),$row->last_update).'</td><td>'.$row->first_name.', '.$row->last_name.'</td><td>'.$address.'</td><td>'.$row->mobile.'</td><td>'.$row->phone.'</td><td>'.$row->email.'</td><td>'.$next_action.'</td><td>'.$assign.'</td></tr>';
            }
            echo'</tbody></table>';
        }
    }
    
    
}
?>