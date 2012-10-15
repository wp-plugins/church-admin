

jQuery(document).ready(function($){
  var map, marker;
  var markers = [];
  var inputAddress = $('#address_line1').val() + ', ' + $('#address_line2').val()+ ', ' +  $('#town').val()  + ', '+  $('#county').val()  + ', ' + $('#postcode').val() ;
  console.log(inputAddress);
  var inputLat = '#lat';
  var inputLng = '#lng';
  var finalise ='#finalise';
  // these are the links to activate the map plotting
  var domAddressConverter = '#geocode_address';

  
  
 
  
  var contMap = '#map';
  var geocoder = new google.maps.Geocoder();
  
  function convertDivToMap()
  {
    // clear previous markers
    var latlng = new google.maps.LatLng(beginLat, beginLng);
    
    var mapOptions = {
      zoom : 17,
      center : latlng,
      mapTypeId : google.maps.MapTypeId.ROADMAP
    };
    
    map = new google.maps.Map(jQuery(contMap).get(0), mapOptions);
    
    // call a method to place the marker in the current map
    placeMarker(latlng);
  }
  
  // this function accepts an object of the google.maps.LatLng class
  function placeMarker(location)
  {
    // clear previous markers
    if(markers)
    {
      for(i in markers)
      {
        markers[i].setMap(null);
      }
    }
    // create a new marker
    var marker = new google.maps.Marker({
      position : location,
      map : map,
      draggable : true
    });
    
    // add created marker to a global array to be tracked and removed later
    markers.push(marker);
    
    map.setCenter(location);
    
    // extract lat and lng from LatLng location and put values in form
    jQuery(inputLat).val(location.lat());
    jQuery(inputLng).val(location.lng());
    
    /* 
     * when marker is dragged, extract coordinates, 
     * change form values and proceed with geocoding
     */
    google.maps.event.addListener(marker, 'dragend', function(){
      var coords = marker.getPosition();
      jQuery(inputLat).val(coords.lat());
      jQuery(inputLng).val(coords.lng());
      
      geocodeCoords(coords);
      map.setCenter(coords);
    });
  }
  
  function geocodeLocation(address)
  {
    geocoder.geocode({'address' : address}, function(result, status){
      
      if(status!='ZERO_RESULTS')
      {// this returns a latlng
      console.log(status);
      var location = result[0].geometry.location;
      map.setCenter(location);
      }
      // replace markers
      placeMarker(location);      
    });
  }
  
  function geocodeCoords(coords)
  {
    geocoder.geocode({'latLng':coords}, function(result, status){
      switch(status)
      {
        case 'ZERO_RESULTS':
          $(finalise).html('Sorry Google doesn\'t know your address, please drag the pin to where you are for showing on this website');
          break;
        
        case 'ERROR':
          alert('There was a problem in processing. Please try again later.');
          break;
         
        case 'OK':
          $(inputAddress).val(result[1].formatted_address);
          break;
         
      }
    });
  }
  
  function validateAndPlot()
  {
    // handle geocoding of given address
    jQuery(domAddressConverter).click(function(e){
      e.preventDefault();
      
      if(inputAddress== '')
      {
        alert('No address specified!');
      }
      else
      {
        geocodeLocation(inputAddress);
        $(finalise).html('Please drag the pin to finalise address');
      }
    });
    

  }
  
  // begin execution on page load
  jQuery(function(){
    convertDivToMap();
    validateAndPlot();
    
  });
  
});