jQuery(document).ready(function($) {
console.log(ChurchAdminAjax.ajaxurl);
$("audio").bind("play", function(){
		console.log('Fired');
		var data = {action: "play_count",file_id: $(this).attr("id")};
		//$.post(window.ChurchAdminAjax.ajaxurl, data);
  	});
  
});