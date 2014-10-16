<?php
function ca_podcast_display($series_id=NULL,$file_id=NULL,$speaker_name=NULL)
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
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=content_url().'/uploads/sermons/';
	$header='';
	if(!empty($series_id)||!empty($speaker_name))$header.='<p><a href="'.get_permalink().'"><strong>'.__('Show all sermons','church-admin').'</strong></a></p>';
	//Add filter by preacher name
		$speakers=$wpdb->get_results('SELECT DISTINCT speaker AS speakers FROM '.CA_FIL_TBL);
		$preachers=array();
		foreach($speakers AS $speaker)
		{
			$pr=explode(", ",church_admin_get_people($speaker->speakers));//gets list of names from each value
			if(!empty($pr)&& is_array($pr))$preachers=array_merge($preachers,$pr);
		}
		$preachers=array_unique($preachers,SORT_REGULAR);
		if(!empty($preachers))
		{
			$header.='<p><strong>'.__('Filter by preacher','church-admin').':</strong> ';
			$preacher_header=array();
			foreach($preachers AS $key=>$value)
			{
				$preacher_header[].='<a href="'.get_permalink().'?speaker_name='.urlencode($value).'">'.$value.'</a>';
			}
			$header.=implode(', ',$preacher_header).'</p>';
		}
		//End Add filter by preacher name
		
	//Add filter by series
		
	 $series=$wpdb->get_results('SELECT * FROM '.CA_SERM_TBL);
	 if(!empty($series))
	 {
		$header.='<p><strong>'.__('Filter by series','church-admin').':</strong> ';
		$series_header=array();
			foreach($series AS $serie)
			{
				$series_header[].='<a href="'.get_permalink().'?series_id='.urlencode($serie->series_id).'">'.$serie->series_name.'</a>';
			}
			$header.=implode(', ',$series_header).'</p>';
	 }
	
	require_once(plugin_dir_path(dirname(__FILE__)).'includes/pagination.class.php');
	$out='';
    if($file_id){return ca_display_file($file_id);}
	elseif($speaker_name)
    {//speaker_name
	$sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE speaker LIKE "%'.esc_sql($speaker_name).'%" ORDER BY pub_date DESC ';
	$header.='<h2>Sermons by '.esc_html($speaker_name).'</h2>';
	} //end speaker_id specified
    elseif($series_id)
    {//series_id specified
        $series=$wpdb->get_row('SELECT * FROM '.CA_SERM_TBL.' WHERE series_id="'.esc_sql($series_id).'"');
        $ser_header.=get_option('ca_podcast_event_template');
        $ser_header.=str_replace('[SERIES_NAME]',$series->series_name,$ser_header);
        $ser_header.=str_replace('[SERIES_DESCRIPTION]',$series->series_description,$ser_header);
		$header.=$ser_header;
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' WHERE series_id="'.esc_sql($series_id).'"';
    }//end series_id specified
    elseif(empty($speaker_name)&&empty($series_id))
    {//not specified
        
        $sql='SELECT file_id FROM '.CA_FIL_TBL.' ORDER BY pub_date DESC';
    }//not specified
	
		
		
	$results=$wpdb->get_results($sql);
	$items=$wpdb->num_rows;
    if($results)
    {
	
		//pagination
		$p = new pagination;
		$p->items($items);
		$p->limit(get_option('church_admin_page_limit')); // Limit entries per page
		$p->target(get_permalink());
		if(!isset($p->paging))$p->paging=1; 
		if(!isset($_GET[$p->paging]))$_GET[$p->paging]=1;
		$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page
		if(!isset($_GET['paging']))
		{
			$p->page = 1;
		}
		else
		{
			$p->page = $_GET['paging'];
		}
        //Query for limit paging
		$limit = " LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		$pageresults=$wpdb->get_results($sql.$limit);
		 
        $out.=$header;
		// Pagination
		$out.= '<div class="tablenav"><div class="tablenav-pages">';
		$out.=	$p->show();  
		$out.= '</div></div>';
    //Pagination
        foreach($pageresults AS $row){$out.=ca_display_file($row->file_id);}
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
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=content_url().'/uploads/sermons/';
    if(!$file_id)return("<p>There is no file to display</p>");
    $template=get_option('ca_podcast_file_template');
    $sql='SELECT a.*,b.* FROM '.CA_FIL_TBL.' a, '.CA_SERM_TBL.' b WHERE a.series_id=b.series_id AND a.file_id="'.esc_sql($file_id).'"';
    
    $data=$wpdb->get_row($sql);
    $data->speaker_name=$data->speaker;
    if($data)
    {
        $template=str_replace('[VIDEO_URL]',"\r\n".$data->video_url."\r\n",$template);
		$template=str_replace('[FILE_TITLE]',$data->file_title,$template);
		$template=str_replace('[FILE_ID]',$data->file_id,$template);
		$template=str_replace('[FILE_DATE]',mysql2date(get_option('date_format'),$data->pub_date),$template);
        $template=str_replace('[FILE_NAME]',$url.$data->file_name,$template);
		$template=str_replace('[FILE_PLAYS]','Played: <span class="plays'.$data->file_id.'">'.church_admin_plays($data->file_id).'</span> times',$template);
        $template=str_replace('[FILE_URI]',$url.$data->file_name,$template);
        $template=str_replace('[FILE_DOWNLOAD]','<a href="'.$url.$data->file_name.'" title="'.esc_html($data->file_title).'">'.strtoupper(esc_html($data->file_title)).'</a>',$template);
        if(file_exists($path.$data->transcript))
        {
			$template=str_replace('[TRANSCRIPT]','<a href="'.$url.$data->transcript.'" title="'.esc_html($data->transcript).'">'.esc_html($data->transcript).'</a>',$template);
        
		}
		else
		{
			$template=str_replace('[TRANSCRIPT]','',$template);
        
		}	
        $template=str_replace('[FILE_DESCRIPTION]',$data->file_description,$template);
        $template=str_replace('[SERIES_NAME]','<a href="'.get_permalink().'?series_id='.$data->series_id.'">'.$data->series_name.'</a>',$template);
        $template=str_replace('[SPEAKER_NAME]','<a href="'.get_permalink().'?speaker_name='.urlencode($data->speaker_name).'">'.$data->speaker_name.'</a>',$template);
        //$template=str_replace('[SPEAKER_DESCRIPTION]',$data->speaker_description,$template);
      
		return $template;
    }
    else
    {
        return "File not found";
    }
    
}

?>
