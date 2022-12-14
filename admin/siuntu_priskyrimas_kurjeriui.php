<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);

$sql="SELECT * FROM packages";
$result=mysqli_query($dbc,$sql);

/* Count table rows */
$count=mysqli_num_rows($result);

$couriers = array();
$query = "SELECT * FROM users WHERE access_level='2'";
$resultcouriers=mysqli_query($dbc,$query);

$couriercount = mysqli_num_rows($resultcouriers);
while($row = mysqli_fetch_array($resultcouriers)) {
    array_push($couriers,$row);
}
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

if(isset($_POST['submit_courier']))
{
	$number=count($_POST['id']);
	
for($i=0;$i<$number;$i++){
$sql1="UPDATE packages SET courier_id='" . $_POST['courier'][$i] . "' WHERE id='" . $_POST['id'][$i] . "'";
$result1=mysqli_query($dbc, $sql1);
header('location: siuntu_priskyrimas_kurjeriui.php');
}
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Siuntų priskyrimas");
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
                      <a class="nav-link" href="../index.php">Pagrindinis</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link"  href="../sekimas.php">Sekimas</a>
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
                          <a class='nav-link'  href='kurjeris/kurjeris.php'>Kurjeris</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='kurjeris/skundas.php'>Skundo registravimas</a>
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
                            <a class='btn btn-outline-success' href='prisijungimas.php'>Prisijungti</a>
                        </form>";
                  }
        ?>
        </div>
    </nav>
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
                        <th>Kurjeris</th>
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
                        <td>
                                <?php 
                                echo "<select name='courier[]' id='courier' class='form-control' value='Pasirinkite kurjerį'>";
                                if($rows['courier_id'] > 0){
                                for($j=0;$j<$couriercount;$j++){
                                    if($rows['courier_id'] == $couriers[$j]['id']){
                                    echo '<option value="'.$rows['courier_id'].'">'.$couriers[$j]['username'].'</option>';
                                    $selected=$couriers[$j]['username'];
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
                        </td>

                        <td>
                        <?php echo $rows['id']; 
                        echo "<input type='hidden' name='id[]' value=".$rows['id']." />";
                        ?>
                        </td>

                        <td>
                        <?php echo $rows['planned_delivery_date']; ?>
                        </td>

                        <td>
                        <?php echo $rows['delivery_date']; ?>
                        </td>

                        <td><?php echo $rows['from_address']; ?></td>

                        <td><?php echo $rows['to_address']; ?></td>

                        <td>
                        <?php echo $rows['status']; ?>
                        </td>

                        <td>
                        <?php echo $rows['weight']; ?>
                        </td>

                        </tr>

                        <?php
                        }
                        ?>

                    </tbody>
                </table>
                <input id='button' type='submit' name='submit_courier' class='btn btn-primary float-end' value="Išsaugoti"">
            </div>
        </form>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>