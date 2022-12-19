<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);

$sql="SELECT * FROM packages WHERE courier_id='0'";
$result=mysqli_query($dbc,$sql);

$count=mysqli_num_rows($result);



session_start();

# Atsijungimas
if (isset($_POST["logout"]))
{
    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();
    header('Location: ../index.php');
}

if (!isset($_SESSION["username"]))
{
    $_SESSION["logged_in"] = 0;
    $_SESSION["access_level"] = 1;
}

else
{
    $_SESSION["logged_in"] = 1;
    $user_id = $_SESSION["id"];
    $access_level = $_SESSION["access_level"];
}
if (!$dbc)
{
    die ("Nepavyko prisijungti prie duomenų bazės:" .mysqli_error($dbc));
}

if(isset($_POST["submit_mileage"]))
{
    $mileage = mysqli_real_escape_string($dbc, $_POST["mileage"]);
    $fuel = mysqli_real_escape_string($dbc, $_POST["fuel"]);
    $id = mysqli_real_escape_string($dbc, $_SESSION["id"]);
    $query2="SELECT * FROM cars WHERE courier_id='$id'";
    $res=mysqli_query($dbc, $query2);
    $rows=mysqli_fetch_array($res);
    $query = "INSERT INTO daily_mileage (mileage, fuel_used, courier_id, car_id) 
    VALUES('$mileage', '$fuel', '$id', '".$rows['id']."')";
    $result1=mysqli_query($dbc, $query);
    header('Location: kurjeris.php');
}

if(isset($_POST['select_packages']))
{
	$count=count($_POST['selected_packages']);
    $packageids=$_POST['selected_packages'];
for($i=0;$i<$count;$i++){
    $sql1="UPDATE packages SET courier_id='".$_SESSION['id']."', status='Siunta išvežta pristatymui' WHERE id='" . $packageids[$i] . "'";
    $result1=mysqli_query($dbc, $sql1);
    header('location: kurjeris.php');
}
}


    //var_dump($rows['to_address']);
    //die();


?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Kurjeris");
    ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBmQqsBFDyL32XEANrzHiAg76DbARbjmtU"></script>
    <style>#container {
  height: 100%;
  display: flex;
}

#sidebar {
  flex-basis: 15rem;
  flex-grow: 1;
  padding: 1rem;
  max-width: 30rem;
  height: 100%;
  box-sizing: border-box;
  overflow: auto;
}

#map {
  flex-basis: 0;
  flex-grow: 4;
  height: 100%;
}

#floating-panel {
  position: absolute;
  top: 10px;
  left: 25%;
  z-index: 5;
  background-color: #fff;
  padding: 5px;
  border: 1px solid #999;
  text-align: center;
  font-family: "Roboto", "sans-serif";
  line-height: 30px;
  padding-left: 10px;
}

#floating-panel {
  background-color: #fff;
  border: 0;
  border-radius: 2px;
  box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
  margin: 10px;
  padding: 0 0.5em;
  font: 400 18px Roboto, Arial, sans-serif;
  overflow: hidden;
  padding: 5px;
  font-size: 14px;
  text-align: center;
  line-height: 30px;
  height: auto;
}

#map {
  flex: auto;
}

#sidebar {
  flex: 0 1 auto;
  padding: 0;
}
#sidebar > div {
  padding: 0.5rem;
}</style>
</head>
<script>


</script>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
          <div class="container-fluid">
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                      <a class="nav-link" href="../index.php">Pagrindinis</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" aria-current="page" href="../sekimas.php">Sekimas</a>
                  </li>
                  <li class='nav-item'>
                      <a class='nav-link' href='../klausti.php'>Klausti</a>
                  </li>

                  <li class='nav-item'>
                      <a class='nav-link' href='../siuntos_registravimas.php'>Siuntos registravimas</a>
                  </li>

                  <?php
                  # MENU
                  if ($_SESSION["access_level"] == 2) # Kurjeris
                  {
                      echo
                      "<li class='nav-item'>
                          <a class='nav-link active' aria-current='page' href='kurjeris.php'>Kurjeris</a>
                      </li>";
                  }
                  if ($_SESSION["access_level"] == 3) # Administratorius
                  {
                      echo
                      "<li class='nav-item'>
                    <a class='nav-link' href='admin/uzklausos.php'>Užklausos</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='admin/skundai.php'>Skundai</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='admin/kurjeriai.php'>Kurjeriai</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='admin/sandeliai.php'>Sandėliai</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='admin/auto_priskyrimas.php'>Auto priskyrimas</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='admin/siuntu_priskyrimas.php'>Siuntų priskyrimas</a>
                    </li>";

                  }

                  if (isset($_SESSION["username"]))
                  {
                      $username = $_SESSION["username"];
                      echo
                      "</ul>
                      <span class='navbar-text'>Esate prisijungę kaip $username</span>
                      <form class='d-flex' method='post'>
                        <input type='submit' name='logout' class='btn btn-outline-success' value='Atsijungti'>
                      </form>";
                  }
                  else
                  {
                        echo
                        "</ul>
                        <form class='d-flex'>
                            <a class='btn btn-outline-success' href='../prisijungimas.php'>Prisijungti</a>
                        </form>";
                  }
        ?>
        </div>
    </nav>
    <br>
    <div class="container">
    <div class="container-fluid">
    <a class='nav-link' href='pristatymo_patvirtinimas.php'>Siuntos pristatymo patvirtinimas</a>
    <a class='nav-link' href='skundas.php'>Skundo registravimas</a>
        </div>

    <form method="post">
    <div class="container">
        <div class="container">
            <div class="col-12">
                <h1>Siuntų sąrašas</h1>
                <hr>
            </div>
        </div>

        <table class='table table-striped'>
            <thead>
            <tr>
                <th>Pasirinkti</th>
                <th>Siuntos kodas</th>
                <th>Išsiuntimo data</th>
                <th>Pristatymo data</th>
                <th>Siuntėjo adresas</th>
                <th>Gavėjo adresas</th>
                <th>Statusas</th>
                <th>Dydis</th>
            </tr>
            </thead>
            <tbody>

                <?php
                    while($rows=mysqli_fetch_array($result)){
                ?>

            <tr>

                <td><div class="custom-control custom-checkbox">
                        <input type="checkbox" name="selected_packages[]" class="custom-control-input" id="pasirinkta" value="<?php echo $rows['id']; ?>"></td>
                <td><?php echo $rows['id']; ?></td>
                <td><?php echo $rows['planned_delivery_date']; ?></td>
                <td><?php echo $rows['delivery_date']; ?></td>
                <td><?php echo $rows['from_address']; ?></td>
                <td><?php echo $rows['to_address']; ?></td>
                <td><?php echo $rows['status']; ?></td>
                <td><?php echo $rows['weight']; ?></td>
                <td><?php echo $rows['size']; ?></td>
            </tr>
            <?php
                        }
                        ?>
            </tbody>
        </table>
    </div>
        <input id='button' type='submit' name='select_packages' class='btn btn-primary float-end' value="Pasirinkti siuntas"">
    </form>

    </div>
    <div class="container">
        <div class="container">
            <div class="col-12">
                <h1>Sąnaudų registravimas</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-12">
                <form method="post">
                    <div class="mb-3">
                        <label for="mileage" class="form-label">Rida</label>
                        <input type="text" class="form-control" id="mileage" name="mileage" required>
                        <br>
                        <label for="fuel" class="form-label">Kuro sanaudos</label>
                        <input type="text" class="form-control" id="fuel" name="fuel" required>
                    </div>
                    <input type='submit' name='submit_mileage' class='btn btn-primary float-end' value="Pateikti">
                </form>
            </div>
        </div>
    </div>
    <div class="container">
   <div id="floating-panel">
        <strong>Start:</strong>
        <select id="start">
          <option value="kaunas">Kaunas</option>
          <option value="ignalina">Ignalina</option>
        </select>
        <br />
        <strong>End:</strong>
        <select id="end">
          <option value="vilnius">Vilnius</option>
          <option value="utena">Utena</option>
        </select>
      </div>
    </div>
    <?php
$apiKey = 'AIzaSyBmQqsBFDyL32XEANrzHiAg76DbARbjmtU';
$addresses = ['Vilnius', 'Studentų g. 53, Kaunas, 50299', 'Dariaus ir Girėno g. 6, Alytus, 62137', 'Aušros g. 15, Vidiškės'];
$origin = urlencode(reset($addresses)); // starting point
$destination = urlencode(end($addresses)); // final destination
$waypoints = ""; // intermediate stops

// build the waypoints string
array_pop($addresses); // remove the final destination from the array
array_shift($addresses); // remove the starting point from the array
foreach ($addresses as $address) {
  $waypoints .= urlencode($address) . "|";
}
$dirs = array();
$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origin . "&destination=" . $destination . "&waypoints=" . $waypoints . "&key=" . $apiKey;

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

$responseArray = json_decode($response, true);
$polylines = $responseArray['routes'][0]['overview_polyline']['points'];
$routes=$responseArray['routes'];
foreach ($routes as $route) {
  $legs = $route['legs'];
  $polyline = $route['overview_polyline']['points'];

  foreach ($legs as $leg) {
    $steps = $leg['steps'];

    foreach ($steps as $step) {
      array_push($dirs, $step['html_instructions']. "\n");
    }
  }
}

    ?>


<html>
  <head>
    <style type="text/css">
      #map { height: 400px; width: 100%; }
    </style>
  </head>
  <body>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>"></script>
    <script type="text/javascript">
      function initMap() {
  const directionsRenderer = new google.maps.DirectionsRenderer();
  const directionsService = new google.maps.DirectionsService();
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 7,
    center: { lat: 55.16, lng: 23.88 },
    disableDefaultUI: true,
  });

  directionsRenderer.setMap(map);
  directionsRenderer.setPanel(document.getElementById("sidebar"));

  const control = document.getElementById("floating-panel");

  map.controls[google.maps.ControlPosition.TOP_CENTER].push(control);

  const onChangeHandler = function () {
    calculateAndDisplayRoute(directionsService, directionsRenderer);
  };

  document.getElementById("start").addEventListener("change", onChangeHandler);
  document.getElementById("end").addEventListener("change", onChangeHandler);
}

function calculateAndDisplayRoute(directionsService, directionsRenderer) {
  const start = document.getElementById("start").value;
  const end = document.getElementById("end").value;

  directionsService
    .route({
      origin: 'Vilnius',
      destination: 'Vilnius',
      waypoints: [
        {location: 'Studentų g. 53, Kaunas, 50299',stopover: true},
        {location: 'Dariaus ir Girėno g. 6, Alytus, 62137',stopover: true},
        {location: 'Aušros g. 15, Vidiškės, 30233',stopover: true},
        
    ],
      travelMode: google.maps.TravelMode.DRIVING,
    })
    .then((response) => {
      directionsRenderer.setDirections(response);
    })
    .catch((e) => window.alert("Directions request failed due to " + status));
}

window.initMap = initMap;
    </script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBmQqsBFDyL32XEANrzHiAg76DbARbjmtU&callback=initMap&v=weekly"defer></script>
<div id="container">
      <div id="map" style="height: 650px; width: 800px;"></div>
      <div id="sidebar"></div>
    </div>
</div>

        <?php
include_once "../includes/footer.html";
unset($_SESSION['addresses']);
    ?>
</body>
</html>