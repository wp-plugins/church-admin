<?php

function church_admin_news_feed()
{
require_once( ABSPATH . WPINC . '/feed.php' ); 
$output='<ul>';
$max_items = 0; 
if ( function_exists( 'fetch_feed' ) )
{ 
		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( 'http://www.churchadminplugin.com/feed' );
		if ( !is_wp_error( $rss ) ) { // Checks that the object is created correctly 
		    // Figure out how many total items there are, but limit it to 5. 
		    $max_items = $rss->get_item_quantity(4);
		    $rss_items = $rss->get_items( 0, $max_items ); 
		}
	
	    if ( $max_items == 0 )
	    {
	    	$output.='<li class="ajax-error">'.__('No feed items found to display','church-admin').'.</li>';
		}
		else
		{
		    // Loop through each feed item and display each item as a hyperlink.
		    foreach ( $rss_items as $item ) { 
		    $output.='<li><a target="_blank"  href="'. esc_url($item->get_permalink()) .'" title="Posted '.$item->get_date('j F Y | g:i a').'">'.esc_html($item->get_title()).'</a></li>';
		} 
		}
    } else { 
    	$output.=' <li class="ajax-error">'.__('No feed items found to display','church-admin').'.</li>';
    }
    $output.='</ul>';
return $output;
}
?>