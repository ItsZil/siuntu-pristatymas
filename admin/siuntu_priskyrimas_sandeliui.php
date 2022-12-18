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

$warehouses = array();
$query = "SELECT * FROM warehouses";
$resultwarehouses=mysqli_query($dbc,$query);

$warehousecount = mysqli_num_rows($resultwarehouses);
while($row = mysqli_fetch_array($resultwarehouses)) {
    array_push($warehouses,$row);
}

$statusarray = array();
array_push($statusarray, "<option value='Siuntos duomenys išsaugoti'>Siuntos duomenys išsaugoti</option>");
array_push($statusarray, "<option value='Siunta atvyko į sandėlį'>Siunta atvyko į sandėlį</option>");
array_push($statusarray, "<option value='Siunta išvežta pristatymui'>Siunta išvežta pristatymui</option>");
array_push($statusarray, "<option value='Siunta pristatyta'>Siunta pristatyta</option>");

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

/* Check if button name "Submit" is active, do this */
if(isset($_POST['submit']))
{
	$count=count($_POST['id']);
    $warehouseid=$_POST['warehouse'];
    $packagestatus=$_POST['package_status'];
	
for($i=0;$i<$count;$i++){
    $sql1="UPDATE packages SET warehouse_id='".$warehouseid[$i]."', status='".$packagestatus[$i]."' WHERE id='" . $_POST['id'][$i] . "'";
    $result1=mysqli_query($dbc, $sql1);
    header('location: siuntu_priskyrimas_sandeliui.php');
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
                        <th>Sandėlis</th>
                        <th>Siuntos ID</th>
                        <th>Išsiuntimo data</th>
                        <th>Pristatymo data</th>
                        <th>Pristatymo adresas</th>
                        <th>Statusas</th>
                        <th>Dydis</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($rows=mysqli_fetch_array($result)){
                            #echo $rows['id'];
                            #die();
                        ?>
                        <tr>
                        <td>
                                <?php 
                                echo "<select name='warehouse[]' id='warehouse' class='form-control' value='Pasirinkite sandėlį'>";
                                if($rows['warehouse_id'] > 0){
                                for($j=0;$j<$warehousecount;$j++){
                                    if($rows['warehouse_id'] == $warehouses[$j]['id']){
                                    echo '<option value="'.$rows['warehouse_id'].'">'.$warehouses[$j]['name'].'</option>';
                                    $selected=$warehouses[$j]['name'];
                                    }
                                    }
                                }
                                else
                                    echo '<option value="">Pasirinkite sandėlį</option>';

                                for($i=0;$i<$warehousecount;$i++){
                                    if($warehouses[$i]['name'] != $selected)
                                    echo '<option value="'.$warehouses[$i]['id'].'">'.$warehouses[$i]['name'].'</option>';
                                }
                                echo "</select>";
                                unset($selected);
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

                        <td>
                        <?php echo $rows['address'] ?>
                        </td>

                        <td>
                        <?php 
                        echo "<select name='package_status[]' id='status' class='form-control' value='Pasirinkite statusą'>";
                        echo '<option value="'.$rows['status'].'">'.$rows['status'].'</option>';
                        $currentstatus=$rows['status'];

                        for($i=0;$i<4;$i++)
                        {
                            if(!str_contains($statusarray[$i], $currentstatus))
                            {
                                echo $statusarray[$i];
                            }
                        }
                        echo "</select>";
                        ?>
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
                <input id='button' type='submit' name='submit' class='btn btn-primary float-end' value="Išsaugoti"">
            </div>
        </form>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>