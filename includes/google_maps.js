function load(lat,lng) {
        var myOptions = {
          center: new google.maps.LatLng(lat, lng),
          zoom: 13,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map"),myOptions);
      

      

      // Change this depending on the name of your PHP file
      downloadUrl(xml_url, function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        var smallgroup=new Array();
        
        for (var i = 0; i < markers.length; i++) {
          
      var point = new google.maps.LatLng(
              parseFloat(markers[i].getAttribute("lat")),
              parseFloat(markers[i].getAttribute("lng"))
            );
      var pinColor =markers[i].getAttribute("pinColor");
      var id =markers[i].getAttribute("smallgroup_id");
      smallgroup[id]='<img src="http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + pinColor +'"/> '+markers[i].getAttribute("smallgroup_name") +'<br/>';
      
      
      var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
            new google.maps.Size(21, 34),
            new google.maps.Point(0,0),
            new google.maps.Point(10, 34));
      var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow",
        new google.maps.Size(40, 37),
        new google.maps.Point(0, 0),
        new google.maps.Point(12, 35));
          
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: pinImage,
            shadow: pinShadow,
          });
         
        }
        var sg='<h2>Smallgroup Key</h2><p>';
        for(var index in smallgroup) {sg= sg + smallgroup[index];console.log(index);}
        var container = document.getElementById("groups");
            container.innerHTML = sg + '</p>';


      });
      



   

    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;

      request.onreadystatechange = function() {
        if (request.readyState == 4) {
          request.onreadystatechange = doNothing;
          callback(request, request.status);
        }
      };

      request.open('GET', url, true);
      request.send(null);
    }

    function doNothing() {}
}