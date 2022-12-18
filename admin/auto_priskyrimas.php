<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);
$sql="SELECT * FROM cars";
$result=mysqli_query($dbc,$sql);
unset($_SESSION['selected_id']);
/* Count table rows */
$count=mysqli_num_rows($result);

$couriers = array();
$query = "SELECT * FROM users WHERE access_level='2'";

$resultcouriers=mysqli_query($dbc,$query);

$couriercount = mysqli_num_rows($resultcouriers);
while($row = mysqli_fetch_array($resultcouriers)) {
    array_push($couriers,$row);
}
$rowid= array();
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

if(isset($_POST['delete_city']))
{
	

$sql1="DELETE FROM cars WHERE id='" . $_POST['selected_id'] . "'";
$result1=mysqli_query($dbc, $sql1);
header('location: auto_priskyrimas.php');

}

if(isset($_POST['assign_car']))
{
	$count=count(mysqli_real_escape_string($dbc, $_POST['id']));
    $courierid=mysqli_real_escape_string($dbc, $_POST['courier']);
	
for($i=0;$i<$count;$i++){
    $sql1="UPDATE cars SET courier_id='".$courierid[$i]."'WHERE id='" . $_POST['id'][$i] . "'";
    $result1=mysqli_query($dbc, $sql1);
    header('location: auto_priskyrimas.php');
    }
}
if(isset($_POST['edit_car']))
{
    header('location: edit_car.php');
}


?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Auto priskyrimas");
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
                          <a class='nav-link active' aria-current='page' href='kurjeris/kurjeris.php'>Kurjeris</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='kurjeris/skundas.php'>Skundo registravimas</a>
                      </li>
                      ";
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
    <div class="container">
        <div class="container">
            <div class="col-12">
                <h1>Automobiliai</h1>
                <hr>
            </div>
        </div>

        <table class='table table-striped'>
            <thead>
            <tr>
                <th>Modelis</th>
                <th>Rida</th>
                <th>Būsena</th>
                <th>Bagažo dydis</th>
                <th>Kurjeris</th>
                <th>Veiksmai</th>
            </tr>
            </thead>
            <tbody>
            <?php
                while($rows=mysqli_fetch_array($result)){
                    $id = $rows["id"];
            ?>
            <tr>
                <td><?php echo implode(' ',array($rows['mark'], $rows['model'])); 
                echo "<input type='hidden' name='id[]' value=".$rows['id']." />";
                ?></td>
                <td><?php echo $rows['mileage']; ?></td>
                <td><?php echo $rows['car_status']; ?></td>
                <td><?php echo $rows['baggage_type']; ?></td>
                <td><?php
                //need to fix it so it would show courier name instead of courier id in the table
                if($rows['courier_id'] > 0){
                    for($j=0;$j<$couriercount;$j++){
                        if($rows['courier_id'] == $couriers[$j]['id']){
                        echo $couriers[$j]['name'];
                        }
                        }
                    }?></td>
                
                <?php echo '<td><form method="post" action="edit_car.php">
                  <input type="submit" name="edit_car" class="btn btn-primary float-end" value="Redaguoti"/>';
                  echo "<input type='hidden' name='selected_id' value='$id' /></form></td>";
                echo '
                <td><form method="post">
                  <input type="submit" name="delete_city" class="btn btn-primary" value="Pašalinti"/>';
                  echo "<input type='hidden' name='selected_id' value='$id' />";
                echo '</form></td>';?>
            


            

            <?php
                }
            ?>
            </tbody>
        </table>
    </div>


    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>