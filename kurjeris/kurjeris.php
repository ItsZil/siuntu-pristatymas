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
if (isset($_POST["select_packages"]))
{
    $_SESSION["show_map"] = 1;
}
if (!$dbc)
{
    die ("Nepavyko prisijungti prie duomenų bazės:" .mysqli_error($dbc));
}

if(isset($_POST["mileage"]))
{
    $mileage = mysqli_real_escape_string($dbc, $_POST["mileage"]);
    $fuel = mysqli_real_escape_string($dbc, $_POST["fuel"]);
    $id = mysqli_real_escape_string($dbc, $_SESSION["id"]);
    $query = "INSERT INTO daily_mileage (mileage, fuel_used, courier_id) 
    VALUES('$mileage', '$fuel', '$id')";
    mysqli_query($dbc, $query);
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
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Kurjeris");
    ?>
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
        <a class='btn btn-primary' href='pristatymo_patvirtinimas.php'>Siuntos pristatymo patvirtinimas</a>
        <a class='btn btn-primary' href='skundas.php'>Skundo registravimas</a>
    </div>
    <br>

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
                    <input type='submit' name='mileage' class='btn btn-primary float-end' value="Pateikti">
                </form>
            </div>
        </div>
    </div>
    <?php
    if(isset($_SESSION['show_map']))
    {
        echo "<div class='container' id='map'>
        <iframe id='iframe' src='https://storage.googleapis.com/maps-solutions-pg19vlu2ft/locator-plus/aoi4/locator-plus.html' width='100%' height='450' style='border:0;' loading='lazy'>
        </iframe>
    </div>";
    }

    ?>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>