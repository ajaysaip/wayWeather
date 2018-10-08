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
	require 'db.php';
	$x=$_POST["source"];     //input from html//
	$y=$_POST["destination"];//input from html//
	
	//retrieving from database using select query//
	$sql = "SELECT latitude, longitude, temperature, rain FROM mywaypoints.weatherdetails WHERE start_location='$x' AND end_location='$y' ";
$result =mysqli_query($conn, $sql);
var_dump($result);

if (mysqli_num_rows($result) > 0) {//if rows are retrieved then the information from the database will be used//
	echo "Your route is from ".$x." to  ".$y."<br>";
	echo "The route is present in database";
	$g=0;
    while($row = mysqli_fetch_assoc($result)) {
        //echo "lt: " . $row["latitude"]. "  ln: " . $row["longitude"]. " temp:" . $row["temperature"]. " rain:".$row["rain"];
		$lt[$g]=$row["latitude"];
		$ln[$g]=$row["longitude"];
		$tm[$g]=$row["temperature"];
		$rn[$g]=$row["rain"];
		$g=$g+1;
    }
	$p=1;
?>
<script>
var temp = <?php echo json_encode($tm); ?>;
var latitude=<?php echo json_encode($lt); ?>;
var longitude=<?php echo json_encode($ln); ?>;
var prec = <?php echo json_encode($rn); ?>;
/* console.log(temp);
console.log(latitude);
console.log(longitude);
console.log(prec); */
</script>


<?php
}
//if not present in database then the api's are called asusual//
else {
	echo "Your route is from ".$x." to  ".$y."<br>"; 
	$x=urlencode($x);
	$y=urlencode($y);
    //retrieving route and longitudes and latitude along the route//
    $url="https://maps.googleapis.com/maps/api/directions/xml?origin={$x}&destination={$y}&key=AIzaSyCDPIuDZqhvCl1euaOuGn1IQsjtInGCmos"; 
	//xml data//
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
    //print_r($latitude_array);
    //print_r($longitude_array);
    //finding the temperature at given latitudes and longitudes i.e. the waypoints along the route//
    for($j=0;$j<$length;$j++){
		$la=$latitude_array[$j];
		$lo=$longitude_array[$j]; 
		//getting weather at each latitude longitude pair;//
		$urlw="http://api.openweathermap.org/data/2.5/weather?lat={$la[0]}&lon={$lo[0]}&mode=xml&units=metric&APPID=02d247ec922dfa314dd7402cf71d7dd7";
		$xmldataw=file_get_contents($urlw);
		$dataw = simplexml_load_string($xmldataw);
		$temperature[$j]=json_decode( json_encode($dataw->temperature["min"]) , 1);
		$precipitation[$j]=json_decode( json_encode($dataw->precipitation["mode"]) , 1);
	}
	$q=0; 
	//debug//
	$len=sizeof($temperature);
	/*for($a=0;$a<$len;$a++){
		echo $temperature[$a][0];
		echo "<br>";  */
	//}//
	//$myj=json_encode($temperature);
	//storing in database//
	for($q=0;$q<$length;$q++){
		$l=$latitude_array[$q];
		$ll=$longitude_array[$q];
		$t=$temperature[$q][0];
		$r=$precipitation[$q][0];
		$sql = "INSERT INTO mywaypoints.weatherdetails (latitude, longitude, start_location, end_location, temperature, rain)
		VALUES ('$l[0]', '$ll[0]', '$x', '$y', '$t','$r')";

		if (mysqli_query($conn, $sql)) {
			echo "New record created successfully";
		} 
		else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	
	}
?>
<script>
var temperature = <?php echo json_encode($temperature); ?>;
var latitudes=<?php echo json_encode($latitude_array); ?>;
var longitudes=<?php echo json_encode($longitude_array); ?>;
var precipitation = <?php echo json_encode($precipitation); ?>;
length=temperature.length;
//console.log(length);
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

</script>

<?php

}

?>

<script>
//script for diplaying map on the browser//
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


   //     for(int i=0;i<allsteps;i++){}
 //  var test=parseFloat(document.getElementById('test'));
   //     marker = new google.maps.Marker({position:,label:'weather', map: map});

    //    marker = new google.maps.Marker({position: {lng: -78.88,
//lat: 42.88},title:'${allsteps[0]}', map: map});
   // var markers = [];
   
    var length=temp.length;
	console.log(length);
   var polyline=[];
for (var i = 0; i < length ; i++)
{
// window.alert(steplat[i]);
var lat=parseFloat(latitude[i]);
var lng=parseFloat(longitude[i]);
    var pos = new google.maps.LatLng(lat, lng);
 //   window.alert(${steplat.get(i)}+"value of i"+i);
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




