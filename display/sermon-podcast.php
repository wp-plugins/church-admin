<?php
function ca_podcast_display($series_id=NULL,$speaker_id=NULL,$file_id=NULL)
{
/**
 *  Podcast Display
 * 
 * @author  Andy Moyle
 * @param    $event_id,$speaker_id,$file_id
 * @return   
 * @version  0.1
 * 
 */
    global $wpdb,$ca_podcast_settings;
    if($file_id){return ca_display_file($file_id);}
    elseif($speaker_id)
    {//speaker_id specified
        $speaker=$wpdb->get_row('SELECT * FROM '.CA_SPK_TBL.' WHERE speaker_id="'.esc_sql($speaker_id).'"');
        $header=get_option('ca_podcast_speaker_template');
        $header=str_replace('[SPEAKER_NAME]',$speaker->speaker_name,$header);
        $header=str_replace('[SPEAKER_DESCRIPTION]',$speaker->speaker_description,$header);
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE speaker_id="'.esc_sql($speaker_id).'"';
    }//end speaker_id specified
    elseif($series_id)
    {//series_id specified
        $series=$wpdb->get_row('SELECT * FROM '.CA_SERM_TBL.' WHERE series_id="'.esc_sql($series_id).'"');
        $header=get_option('ca_podcast_event_template');
        $header=str_replace('[SERIES_NAME]',$series->series_name,$header);
        $header=str_replace('[SERIES_DESCRIPTION]',$series->series_description,$header);
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE series_id="'.esc_sql($series_id).'"';
    }//end series_id specified
    else
    {//not specified
        $header='';
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' ORDER BY pub_date DESC';
    }//not specified
    $results=$wpdb->get_results($sql);
    if($results)
    {
        $out=$header;
        foreach($results AS $row){$out.=ca_display_file($row->file_id);}
        return $out;
    }
    else
    {
        return("<p>There are no media files uploaded yet</p>");
    }
    
}

function ca_display_file($file_id=NULL)
{
    /**
 *  Display file from template
 * 
 * @author  Andy Moyle
 * @param    $file_id
 * @return   
 * @version  0.1
 * 
 */
    global $wpdb,$ca_podcast_settings;
    if(!$file_id)return("<p>There is no file to display</p>");
    $template=get_option('ca_podcast_file_template');
    $sql='SELECT a.*,b.* FROM '.CA_FIL_TBL.' a, '.CA_SERM_TBL.' b WHERE a.series_id=b.series_id AND a.file_id="'.esc_sql($file_id).'"';
    
    $data=$wpdb->get_row($sql);
    $data->speaker_name=church_admin_get_people($data->speaker);
    if(empty($data->speaker_description))$data->speaker_description='';
    if($data)
    {
        $template=str_replace('[FILE_TITLE]',$data->file_title,$template);
        $template=str_replace('[FILE_NAME]',CA_POD_URL.$data->file_name,$template);
        $template=str_replace('[FILE_DOWNLOAD]','<a href="'.CA_POD_URL.$data->file_name.'" title="'.esc_html($data->file_title).'">'.esc_html($data->file_title).'</a>',$template);
        if(file_exists(CA_POD_PTH.$data->transcript))
        {
			$template=str_replace('[TRANSCRIPTION]','<a href="'.CA_POD_URL.$data->transcript.'" title="'.esc_html($data->transcript).'">'.esc_html($data->transcript).'</a>',$template);
        
		}
		else
		{
			$template=str_replace('[TRANSCRIPTION]','',$template);
        
		}	
        $template=str_replace('[FILE_DESCRIPTION]',$data->file_description,$template);
        $template=str_replace('[SERIES_NAME]',$data->series_name,$template);
        $template=str_replace('[SPEAKER_NAME]',$data->speaker_name,$template);
        $template=str_replace('[SPEAKER_DESCRIPTION]',$data->speaker_description,$template);
        return $template;
    }
    else
    {
        return "File not found";
    }
    
}

?>
