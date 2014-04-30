<?php

function ca_podcast_settings()
{
/**
 *
 * Podcast Settings
 * 
 * @author  Andy Moyle
 * @param    null
 * @return   
 * @version  0.1
 * 
 */ 
    global $wpdb,$ca_podcast_settings;
    $settings=get_option('ca_podcast_settings');
$language_codes = array(
		'en-GB' => 'English UK' ,
                'en_US' => 'English US' ,
		'aa' => 'Afar' , 
		'ab' => 'Abkhazian' , 
		'af' => 'Afrikaans' , 
		'am' => 'Amharic' , 
		'ar' => 'Arabic' , 
		'as' => 'Assamese' , 
		'ay' => 'Aymara' , 
		'az' => 'Azerbaijani' , 
		'ba' => 'Bashkir' , 
		'be' => 'Byelorussian' , 
		'bg' => 'Bulgarian' , 
		'bh' => 'Bihari' , 
		'bi' => 'Bislama' , 
		'bn' => 'Bengali/Bangla' , 
		'bo' => 'Tibetan' , 
		'br' => 'Breton' , 
		'ca' => 'Catalan' , 
		'co' => 'Corsican' , 
		'cs' => 'Czech' , 
		'cy' => 'Welsh' , 
		'da' => 'Danish' , 
		'de' => 'German' , 
		'dz' => 'Bhutani' , 
		'el' => 'Greek' , 
		'eo' => 'Esperanto' , 
		'es' => 'Spanish' , 
		'et' => 'Estonian' , 
		'eu' => 'Basque' , 
		'fa' => 'Persian' , 
		'fi' => 'Finnish' , 
		'fj' => 'Fiji' , 
		'fo' => 'Faeroese' , 
		'fr' => 'French' , 
		'fy' => 'Frisian' , 
		'ga' => 'Irish' , 
		'gd' => 'Scots/Gaelic' , 
		'gl' => 'Galician' , 
		'gn' => 'Guarani' , 
		'gu' => 'Gujarati' , 
		'ha' => 'Hausa' , 
		'hi' => 'Hindi' , 
		'hr' => 'Croatian' , 
		'hu' => 'Hungarian' , 
		'hy' => 'Armenian' , 
		'ia' => 'Interlingua' , 
		'ie' => 'Interlingue' , 
		'ik' => 'Inupiak' , 
		'in' => 'Indonesian' , 
		'is' => 'Icelandic' , 
		'it' => 'Italian' , 
		'iw' => 'Hebrew' , 
		'ja' => 'Japanese' , 
		'ji' => 'Yiddish' , 
		'jw' => 'Javanese' , 
		'ka' => 'Georgian' , 
		'kk' => 'Kazakh' , 
		'kl' => 'Greenlandic' , 
		'km' => 'Cambodian' , 
		'kn' => 'Kannada' , 
		'ko' => 'Korean' , 
		'ks' => 'Kashmiri' , 
		'ku' => 'Kurdish' , 
		'ky' => 'Kirghiz' , 
		'la' => 'Latin' , 
		'ln' => 'Lingala' , 
		'lo' => 'Laothian' , 
		'lt' => 'Lithuanian' , 
		'lv' => 'Latvian/Lettish' , 
		'mg' => 'Malagasy' , 
		'mi' => 'Maori' , 
		'mk' => 'Macedonian' , 
		'ml' => 'Malayalam' , 
		'mn' => 'Mongolian' , 
		'mo' => 'Moldavian' , 
		'mr' => 'Marathi' , 
		'ms' => 'Malay' , 
		'mt' => 'Maltese' , 
		'my' => 'Burmese' , 
		'na' => 'Nauru' , 
		'ne' => 'Nepali' , 
		'nl' => 'Dutch' , 
		'no' => 'Norwegian' , 
		'oc' => 'Occitan' , 
		'om' => '(Afan)/Oromoor/Oriya' , 
		'pa' => 'Punjabi' , 
		'pl' => 'Polish' , 
		'ps' => 'Pashto/Pushto' , 
		'pt' => 'Portuguese' , 
		'qu' => 'Quechua' , 
		'rm' => 'Rhaeto-Romance' , 
		'rn' => 'Kirundi' , 
		'ro' => 'Romanian' , 
		'ru' => 'Russian' , 
		'rw' => 'Kinyarwanda' , 
		'sa' => 'Sanskrit' , 
		'sd' => 'Sindhi' , 
		'sg' => 'Sangro' , 
		'sh' => 'Serbo-Croatian' , 
		'si' => 'Singhalese' , 
		'sk' => 'Slovak' , 
		'sl' => 'Slovenian' , 
		'sm' => 'Samoan' , 
		'sn' => 'Shona' , 
		'so' => 'Somali' , 
		'sq' => 'Albanian' , 
		'sr' => 'Serbian' , 
		'ss' => 'Siswati' , 
		'st' => 'Sesotho' , 
		'su' => 'Sundanese' , 
		'sv' => 'Swedish' , 
		'sw' => 'Swahili' , 
		'ta' => 'Tamil' , 
		'te' => 'Tegulu' , 
		'tg' => 'Tajik' , 
		'th' => 'Thai' , 
		'ti' => 'Tigrinya' , 
		'tk' => 'Turkmen' , 
		'tl' => 'Tagalog' , 
		'tn' => 'Setswana' , 
		'to' => 'Tonga' , 
		'tr' => 'Turkish' , 
		'ts' => 'Tsonga' , 
		'tt' => 'Tatar' , 
		'tw' => 'Twi' , 
		'uk' => 'Ukrainian' , 
		'ur' => 'Urdu' , 
		'uz' => 'Uzbek' , 
		'vi' => 'Vietnamese' , 
		'vo' => 'Volapuk' , 
		'wo' => 'Wolof' , 
		'xh' => 'Xhosa' , 
		'yo' => 'Yoruba' , 
		'zh' => 'Chinese' , 
		'zu' => 'Zulu' , 
		);
    $cats = array( 'Religion & Spirituality -Christianity',
                  'Arts - Design',
            'Arts - Fashion & Beauty',
            'Arts - Food',
            'Arts - Literature',
            'Arts - Performing Arts',
            'Arts - Visual Arts',  
            'Business - Business News',
            'Business - Careers',
            'Business - Investing',
            'Business - Management & Marketing',
            'Business - Shopping',
            'Comedy',
            'Education - Education Technology',
            'Education - Higher Education',
            'Education - K-12',
            'Education - Language Courses',
            'Education - Training',
            'Games & Hobbies - Automotive',
            'Games & Hobbies - Aviation',
            'Games & Hobbies - Hobbies',
            'Games & Hobbies - Other Games',
            'Games & Hobbies - Video Games',
            'Government & Organizations - Local',
            'Government & Organizations - National',
            'Government & Organizations - Non-Profit',
            'Government & Organizations - Regional',
            'Health - Alternative Health',
            'Health - Fitness & Nutrition',
            'Health - Self-Help',
            'Health - Sexuality',
            'Kids & Family',
            'Music',
            'News & Politics',
            'Religion & Spirituality -Buddhism',
            'Religion & Spirituality -Christianity',
            'Religion & Spirituality -Hinduism',
	    'Religion & Spirituality -Islam',
            'Religion & Spirituality -Judaism',
            'Religion & Spirituality -Other',
            'Religion & Spirituality -Spirituality',
            'Science & Medicine - Medicine',
            'Science & Medicine -Natural Sciences',
            'Science & Medicine -Social Sciences',
            'Society & Culture - History',
            'Society & Culture - Personal Journals',
            'Society & Culture - Philosophy',
            'Society & Culture - Places & Travel',
            'Sports & Recreation - Amateur',
            'Sports & Recreation - College & High School',
            'Sports & Recreation - Outdoor',
            'Sports & Recreation - Professional',
            'Technology - Gadgets',
            'Technology - Tech News',
            'Technology - Podcasting',
            'Technology - Software How-To',
            'TV & Film');
            

    if(current_user_can('manage_options'))
    {//current user can
        if(!empty($_POST['save_settings']))
        {//process
            //handle image
            if(!empty($_FILES['image']['tmp_name']))
            {
                
                if(getimagesize($_FILES['image']['tmp_name']))
                {
                    $tmp_name = $_FILES["image"]["tmp_name"];
                    $name = basename($_FILES["image"]["name"]);
                    if(move_uploaded_file($tmp_name, CA_POD_PTH.$name))$image=CA_POD_URL.$name;
                }
                else
                {//not image, so no change
                    $image=$settings['image'];//no change    
                }//not image, so no change
            }
            else
            {//no upload, so no change
                $image=$settings['image'];//no change    
            }//no upload, so no change
            //end handle image
            
            
            $xml=array();
            foreach($_POST AS $key=>$value)$xml[$key]=xmlentities(stripslashes($value));
            switch($xml['explicit'])
            {
                case 'clean':$xml['explicit']='clean';break;
                case 'no':$xml['explicit']='no';break;
                case 'yes':$xml['explicit']='yes';break;
                default:$xml['explicit']='no';
            }
            //only allow valid category
            if(in_array($_POST['category'],$cats)){$xml['category']=xmlentities(stripslashes($_POST['category']));}else{$xml['category']='Religion & Spirituality -Christianity';}
            if(!array_key_exists($xml['language'],$language_codes))$xml['language']='en';
            $new_settings=array('itunes_link'=>$xml['link'],
                'title'=>$xml['title'],  
            'copyright'=>$xml['copyright'],
            'link'=>CA_POD_URL.'podcast.xml',
            'subtitle'=>$xml['subtitle'],
            'author'=>$xml['author'],
            'summary'=>$xml['summary'],
            'description'=>$xml['description'],
            'owner_name'=>$xml['owner_name'],
            'owner_email'=>$xml['owner_email'],
            'image'=>$image,
            'category'=>$xml['category'],
            'language'=>$xml['language'],
            'explicit'=>$xml['explicit']
            );
            
            update_option('ca_podcast_settings',$new_settings);
            update_option('ca_podcast_file_template',stripslashes($_POST['file_template']));
            update_option('ca_podcast_speaker_template',stripslashes($_POST['speaker_template']));
            update_option('ca_podcast_series_template',stripslashes($_POST['series_template']));
            echo'<div class="updated fade"><p><strong>Podcast Settings Updated<br/><a href="'.CA_POD_URL.'podcast.xml">Check xml feed</a></p></div>';
            require_once(CHURCH_ADMIN_INCLUDE_PATH.'sermon-podcast.php');
            ca_podcast_xml();
            
        }//end process
        else
        {//form
				$settings=get_option('ca_podcast_settings');
            echo'<h2>Podcast Settings for RSS file</h2>';
            echo'<form action="" enctype="multipart/form-data" method="post">';
			echo'<p><label>Itunes Link</label><input id="title" type="text" name="itunes_link" value="'.esc_html($settings['itunes_link']).'"/></p>';
			
            echo'<h2>File Template</h2>';
            echo'<textarea rows="20" cols="200" name="file_template">'.get_option('ca_podcast_file_template').'</textarea>';
            echo'<h2>Speaker Template</h2>';
            echo'<textarea rows="20" cols="200" name="speaker_template">'.get_option('ca_podcast_speaker_template').'</textarea>';
            echo'<h2>Series Template</h2>';
            echo'<textarea rows="20" cols="200" name="series_template">'.get_option('ca_podcast_series_template').'</textarea>';
            echo'<h2>Podcast Settings for RSS file</h2>';
            echo'<p><label for="title">Podcast title (255 charas)</label><input id="title" type="text" name="title" value="'.esc_html($settings['title']).'"/></p>';
            echo'<p><label for="copyright">Copyright Message: &copy;</label><input id="copyright" type="text" name="copyright" value="'.esc_html($settings['copyright']).'"/></p>';
            echo'<p><label for="subtitle">Subtitle</label><textarea id="subtitle" name="subtitle" >'.esc_html($settings['subtitle']).'</textarea></p>';
            echo'<p><label for="author">Author</label><input id="author" type="text" name="author" value="'.esc_html($settings['author']).'"/></p>';
            echo'<p><label for="summary">Summary</label><textarea id="summary"  name="summary">'.esc_html($settings['summary']).'</textarea></p>';
            echo'<p><label for="description">Description</label><textarea  id="description"  name="description">'.esc_html($settings['title']).'</textarea></p>';
            echo'<p><label for="explicit">Explicit content</label><select name="explicit">';
            if(!empty($settings['explicit']))echo'<option value="'.$settings['excplicit'].'" selected="selected">'.$settings['explicit'].'</option>';
            echo'<option value="clean">clean</option><option value="no">no</option><option value="yes">yes</option></select></p>';
            
            echo'<p><label for="owner_name">Owner Name</label><input id="owner_name" type="text" name="owner_name" value="'.esc_html($settings['owner_name']).'"/></p>';
            echo'<p><label for="owner_email">Owner Email</label><input id="" type="text" name="owner_email" value="'.esc_html($settings['owner_email']).'"/></p>';
            echo'<p><label for="language">Language</label><select id="language" name="language">';
            $first=$option='';
            foreach($language_codes AS $key=>$value)
            {
                if($key==$settings['language']){$first='<option value="'.$key.'" selected="selected" >'.$value.'</option>';}else{ $option.='<option value="'.$key.'">'.$value.'</option>';}
            }
            echo $first.$option.'</select></p>';
            echo'<p><label for="category">Itunes Category</label><select id="category" name="category">';
            $first=$option='';
            foreach($cats AS $key=>$value)
            {
                if($value==$settings['category']){$first='<option value="'.$value.'" selected="selected" >'.$value.'</option>';}else{ $option.='<option value="'.$value.'">'.$value.'</option>';}
            }
            echo $first.$option.'</select></p>';
            echo'<p><label for="image">Image</label><input type="file" name="image"/>';
            echo'<br/><img src="'.$settings['image'].'">';
            echo'</p>';
            echo '<p><input type="hidden" name="save_settings" value="yes"/><input type="submit" class="primary-button" value="Save Podcast XML settings"/></p></form>';

            
            
            
        }//form        
        
        
        
    }//end current user can
    
    
}

  function xmlentities( $string ) {
        $not_in_list = "A-Z0-9a-z\s_-";
        return preg_replace_callback( "/[^{$not_in_list}]/" , 'get_xml_entity_at_index_0' , $string );
    }
    function get_xml_entity_at_index_0( $CHAR ) {
        if( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
            die( "function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
        }
        switch( $CHAR[0] ) {
            case "'":    case '"':    case '&':    case '<':    case '>':
                return htmlspecialchars( $CHAR[0], ENT_QUOTES );    break;
            default:
                return numeric_entity_4_char($CHAR[0]);                break;
        }       
    }
    function numeric_entity_4_char( $char ) {
        return "&#".str_pad(ord($char), 3, '0', STR_PAD_LEFT).";";
    }
    
?>  
