
audiojs.events.ready(function() {
        var as = audiojs.createAll();
		
		jQuery(as).ready(function($) {
			$('.ca_podcast_file').each(function(){

				/* cache the container instance in a variable*/
				var container=$(this);
				/* searching within the container instance for each 
				component insulates multiple instances on page*/
			    var audio= container.find('audio');
          
				/*now events will only be bound to this instance*/ 
				audio.on('playing',function(){
					/* within event callbacks keep searches within the main container element*/                     
					var fileID=audio.attr('id');
					
					var data = {file_id: fileID	,security:security};
					
					jQuery.post(ChurchAdminAjax.ajaxurl, { 'action': 'ca_mp3_action','data':   data }, 
						function(response){
						
							$('.plays'+fileID).html(response);
							
						}
					);

				});
				var url=container.find('a');
				
				
				/*now events will only be bound to this instance*/ 
				url.on('contextmenu',function(){
					/* within event callbacks keep searches within the main container element*/                     
					var fileID=audio.attr('id');
					var data = {file_id: fileID	,security:ChurchAdminAjax.security};
					
					jQuery.post(ChurchAdminAjax.ajaxurl, { 'action': 'ca_mp3_action','data':   data }, 
						function(response){
						
							$('.plays'+fileID).html(response);
						}
					);

				});
				
				
			});
		
		});
	});


