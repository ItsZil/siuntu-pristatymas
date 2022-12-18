<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);
$couriers = array();
$query = "SELECT * FROM users WHERE access_level='2'";

$resultcouriers=mysqli_query($dbc,$query);

$couriercount = mysqli_num_rows($resultcouriers);
while($row = mysqli_fetch_array($resultcouriers)) {
    array_push($couriers,$row);
}
$fuelarray = array();
array_push($fuelarray, "<option value='Dyzelinas'>Dyzelinas</option>");
array_push($fuelarray, "<option value='Benzinas'>Benzinas</option>");
array_push($fuelarray, "<option value='Elektra'>Elektra</option>");
array_push($fuelarray, "<option value='Dujos'>Dujos</option>");

$transmissionarray = array();
array_push($transmissionarray, "<option value='Mechanine'>Mechaninė</option>");
array_push($transmissionarray, "<option value='Automatine'>Automatinė</option>");

$baggagearray = array();
array_push($baggagearray, "<option value='L1 H1'>L1 H1</option>");
array_push($baggagearray, "<option value='L1 H2'>L1 H2</option>");
array_push($baggagearray, "<option value='L2 H1'>L2 H1</option>");
array_push($baggagearray, "<option value='L2 H2'>L2 H2</option>");
array_push($baggagearray, "<option value='L3 H2'>L3 H2</option>");
array_push($baggagearray, "<option value='L3 H3'>L3 H3</option>");
array_push($baggagearray, "<option value='L4 H2'>L4 H2</option>");
array_push($baggagearray, "<option value='L4 H3'>L4 H3</option>");

$carstatusarray = array();
array_push($carstatusarray, "<option value='Uzimtas'>Užimtas</option>");
array_push($carstatusarray, "<option value='Laisvas'>Laisvas</option>");
array_push($carstatusarray, "<option value='Apziurimas'>Apžiūrimas</option>");
array_push($carstatusarray, "<option value='Taisomas'>Taisomas</option>");

$radioarray = array();
array_push($radioarray, "Yra");
array_push($radioarray, "Nėra");

session_start();

# Atsijungimas
if (isset($_POST["logout"]))
{
    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();
    header('Location: index.php');
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
if(isset($_POST['update_car']))
{
    $date = date(DATE_ATOM, mktime(0, 0, 0, 7, 1, 2000));

    $id = $_POST['selected_id'];
    $date = $_POST['registration_date'];
    $mark = $_POST['mark'];
    $model = $_POST['model'];
    $reg_number = $_POST['reg_number'];
    $year = $_POST['year'];
    $mileage = $_POST['mileage'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $baggage_type = $_POST['baggage_type'];
    $radio = $_POST['radio'];
    $car_status = $_POST['car_status'];
    $value = $_POST['value'];
    $courier = $_POST['courier'];

    $query = "UPDATE cars SET mark='$mark', model='$model', reg_number='$reg_number', 
    year='$year', mileage='$mileage',transmission='$transmission', fuel_type='$fuel_type', 
    radio='$radio', car_status='$car_status', baggage_type='$baggage_type',
     registration_date='$date', value='$value', courier_id='".$courier[0][0]."' WHERE id='$id'";

    $result1=mysqli_query($dbc, $query);
    header('location: auto_priskyrimas.php');

}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Klausti");
    ?>
</head>
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
                  <a class='nav-link' href="../index.php">Pagrindinis</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="../sekimas.php">Sekimas</a>
                </li>

                <li class='nav-item'>
                  <a class="nav-link active" aria-current="page" href="../klausti.php">Klausti</a>
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
                        <a class='nav-link' href='../kurjeris/kurjeris.php'>Kurjeris</a>
                    </li>";
                  }
                  if ($_SESSION["access_level"] == 3) # Administratorius
                  {
                    echo "<li class='nav-item'>
                    <a class='nav-link active' href='administratorius.php'>Administratorius</a>
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
        <?php 
            $mysqli = new mysqli($server, $user, $password, $db); 
            $id = mysqli_real_escape_string($dbc, $_POST['selected_id']);
            $query = "SELECT * FROM cars WHERE id=$id";
            $result = $mysqli->query($query);
            $row = $result->fetch_assoc();
            $query2 = "SELECT DATE(registration_date) FROM cars WHERE id=$id";
            $result2 = $mysqli->query($query);
            $row2 = $result2->fetch_assoc();
            $date = trim($row2['registration_date']);
        ?>
    <div class="container">
        <div class="container">
            <div class="col-12">
                <h1>Automobilio redagavimas</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-12">
                <form method="post">
                    <div class="mb-3">
                        <label for="mark" class="form-label">Markė</label>
                        <input type="text" class="form-control" id="mark" name="mark" value="<?php echo $row['mark']; ?>" maxlength="25" required>
                        <br>
                        <label for="model" class="form-label">Modelis</label>
                        <input type="text" class="form-control" id="model" name="model" value="<?php echo $row['model']; ?>" maxlength="25" required>
                        <br>
                        <label for="reg_number" class="form-label">Valstybinis nr.</label>
                        <input type="text" class="form-control" id="reg_number" name="reg_number" value="<?php echo $row['reg_number']; ?>" maxlength="25" required>
                        <br>
                        <label for="year" class="form-label">Metai</label>
                        <input type="text" class="form-control" id="year" name="year" value="<?php echo $row['year']; ?>" maxlength="10" required>
                        <br>
                        <label for="mileage" class="form-label">Rida</label>
                        <input type="text" class="form-control" id="mileage" name="mileage" value="<?php echo $row['mileage']; ?>" maxlength="50" required>
                        <br>
                        <label for="transmission" class="form-label">Pavarų dėžės tipas</label>
                        <?php 
                        echo "<select name='transmission' id='transmission' class='form-control' value='Pasirinkite pavarų dežės tipą'>";
                        echo '<option value="'.$row['transmission'].'">'.$row['transmission'].'</option>';
                        $currenttransmission=$row['transmission'];

                        for($i=0;$i<count($transmissionarray);$i++)
                        {
                            if(!str_contains($transmissionarray[$i], $currenttransmission))
                            {
                                echo $transmissionarray[$i];
                            }
                        }
                        echo "</select>";
                        ?>
                        <br>
                        <label for="fuel_type" class="form-label">Kuro tipas</label>
                        <?php 
                        echo "<select name='fuel_type' id='fuel_type' class='form-control' value='Pasirinkite kuro tipą'>";
                        echo '<option value="'.$row['fuel_type'].'">'.$row['fuel_type'].'</option>';
                        $currentfuel=$row['fuel_type'];

                        for($i=0;$i<count($fuelarray);$i++)
                        {
                            if(!str_contains($fuelarray[$i], $currentfuel))
                            {
                                echo $fuelarray[$i];
                            }
                        }
                        echo "</select>";
                        ?>
                        <br>
                        <label for="radio" class="form-label">Radijas</label>
                        <?php
                        echo "<select name='radio' id='radio' class='form-control' value='Radijas'>";
                        echo '<option value="'.$row['radio'].'">'.$row['radio'].'</option>';
                        $currentradio=$row['radio'];

                        for($i=0;$i<count($radioarray);$i++)
                        {
                            if(!str_contains($radioarray[$i], $currentradio))
                            {
                                echo $radioarray[$i];
                            }
                        }
                        echo "</select>";    
                        ?>                    
                        <br>
                        <label for="car_status" class="form-label">Būklė</label>
                        <?php
                        echo "<select name='car_status' id='car_status' class='form-control' value='Radijas'>";
                        echo '<option value="'.$row['car_status'].'">'.$row['car_status'].'</option>';
                        $currentstatus=$row['car_status'];

                        for($i=0;$i<count($carstatusarray);$i++)
                        {
                            if(!str_contains($carstatusarray[$i], $currentstatus))
                            {
                                echo $carstatusarray[$i];
                            }
                        }
                        echo "</select>";    
                        ?>                          
                        <br>
                        <label for="baggage_type" class="form-label">Bagažo tipas</label>
                        <?php
                        echo "<select name='baggage_type' id='baggage_type' class='form-control' value='Radijas'>";
                        echo '<option value="'.$row['baggage_type'].'">'.$row['baggage_type'].'</option>';
                        $currentbaggage=$row['baggage_type'];

                        for($i=0;$i<count($baggagearray);$i++)
                        {
                            if(!str_contains($baggagearray[$i], $currentbaggage))
                            {
                                echo $baggagearray[$i];
                            }
                        }
                        echo "</select>";    
                        ?> 
                        <br>
                        <label for="registration_date" class="form-label">Registracijos data</label>
                        <input type='date' class='form-control' id='registration_date' name='registration_date' value='<?php echo $date; ?>' maxlength='50' required>
                        
                        <br>
                        <label for="value" class="form-label">Vertė</label>
                        <input type="text" class="form-control" id="value" name="value" value="<?php echo $row['value']; ?>" maxlength="10" required>
                        <br>
                        <label for="courier[]" class="form-label">Kurjeris</label>
                        <?php 
                                echo "<select name='courier[]' id='courier' class='form-control' value='Pasirinkite kurjerį' required>";
                                if($rows['courier_id'] > 0){
                                for($j=0;$j<$couriercount;$j++){
                                    if($rows['courier_id'] == $couriers[$j]['id']){
                                    echo '<option value="'.$rows['courier_id'].'">'.$couriers[$j]['username'].'</option>';
                                    $selected=$couriers[$j]['username'];
                                    echo "<input type='hidden' name='courier_id' value=".$selected." />";
                                    }
                                    }
                                }
                                else
                                    echo '<option value="">Pasirinkite kurjerį</option>';

                                for($i=0;$i<$couriercount;$i++){
                                    if($couriers[$i]['username'] != $selected)
                                    echo '<option value="'.$couriers[$i]['id'].'">'.$couriers[$i]['username'].'</option>';
                                }
                                echo "</select>";
                                unset($selected)
                                ?>
                    </div>
                    <input type="hidden" name="selected_id" value="<?php echo $id; ?>" />
                    <input type='submit' name='update_car' class='btn btn-primary float-end' value="Išsaugoti">
                </form>
            </div>
        </div>
    </div>

    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>