<html>
<head>
<style>
#map {
        height: 100%;
      }

</style>

</head>

<body>
<?php
	//***please change the api keys***//
	
	$x=$_POST["source"];     //input from html//
	$y=$_POST["destination"];//input from html//
	
	
 echo "Your route is from ".$x." to  ".$y."<br>"; 
	$x=urlencode($x);
	$y=urlencode($y);
//retrieving route and longitudes and latitude along the route//
$url="https://maps.googleapis.com/maps/api/directions/xml?origin={$x}&destination={$y}&key=AIzaSyCDPIuDZqhvCl1euaOuGn1IQsjtInGCmos"; 

$xmldata=file_get_contents($url);
$data = simplexml_load_string($xmldata);
 //echo "<pre>"; print_r($data);
$length=sizeof($data->route->leg->step);
//print_r($data->route->leg->step[0]->start_location);
// seperation of latlng to seperate arrays//
 for($i=0;$i<$length;$i++){
	$latitude_array[$i] = json_decode( json_encode($data->route->leg->step[$i]->start_location->lat) , 1); 
    $longitude_array[$i] = json_decode( json_encode($data->route->leg->step[$i]->start_location->lng) , 1);
}



//finding the temperature at given latitudes and longitudes i.e. the waypoints along the route//
 for($j=0;$j<$length;$j++){
	$la=$latitude_array[$j];
	$lo=$longitude_array[$j]; 
	//print_r($lo[0]);
	$urlw="http://api.openweathermap.org/data/2.5/weather?lat={$la[0]}&lon={$lo[0]}&mode=xml&units=metric&APPID=02d247ec922dfa314dd7402cf71d7dd7";
	$xmldataw=file_get_contents($urlw);
	$dataw = simplexml_load_string($xmldataw);
	$temperature[$j]=json_decode( json_encode($dataw->temperature["value"]) , 1);
	$precipitation[$j]=json_decode( json_encode($dataw->precipitation["mode"]) , 1);
} 

$len=sizeof($temperature);

?>

<script>
var temperature = <?php echo json_encode($temperature); ?>;
var latitudes=<?php echo json_encode($latitude_array); ?>;
var longitudes=<?php echo json_encode($longitude_array); ?>;
var precipitation = <?php echo json_encode($precipitation); ?>;
length=temperature.length;
console.log(length);
var latitude=[];
var longitude=[];
var temp=[];
var prec=[];
for (var i = 0; i < length ; i++){
	latitude[i]=latitudes[i][0];
	longitude[i]=longitudes[i][0];
	temp[i]=temperature[i][0];
	prec[i]=precipitation[i][0];
	
}

console.log(latitude);
console.log(longitude);
console.log(temp);

</script>
<script>
		var lt=parseFloat(latitude[0]);
		var ln=parseFloat(longitude[0]);
		//var latlng = new google.maps.LatLng(39.305, -76.617);
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'),
{
          zoom: 7,
          center: {lat: lt, lng: ln}
		  
        });

   
    var length=temp.length;
	console.log(length);
   var polyline=[];
for (var i = 0; i < length ; i++)
{

var lat=parseFloat(latitude[i]);
var lng=parseFloat(longitude[i]);
    var pos = new google.maps.LatLng(lat, lng);
 
    polyline.push(pos);
    var marker = new google.maps.Marker({
        position: pos,
        map: map,
        title: 'Temperature: '+temp[i]+'C'+'   '+'Rain:'+prec[i]
    });
}

  var pathpolyline = new google.maps.Polyline({
    path: polyline,
    geodesic: true,
    strokeColor: 'blue',
    strokeOpacity: 2.0,
    strokeWeight: 3
  });

  pathpolyline.setMap(map);

directionsDisplay.setMap(map);
     }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCDPIuDZqhvCl1euaOuGn1IQsjtInGCmos&callback=initMap">
    </script>

<div id="map"></div>



</body>
</html>



