<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);

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

$name=$name_err="";
$city=$city_err="";
$address=$address_err="";
$phone=$phone_err="";
$email=$email_err="";
$area_err=$shelves_err="";
$area=0;
$shelves=0;
if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(empty($_POST["name"])){
        $name_err="Įveskite pavadinimą";
    } else{
        $name=trim($_POST['name']);
    }
    if(empty($_POST["city"])){
        $city_err="Įveskite miestą";
    } else{
        $city=trim($_POST['city']);
    }
    if(empty($_POST["address"])){
        $address_err="Įveskite adresą";
    } else{
        $address=trim($_POST['address']);
    }
    if(empty($_POST["phone"])){
        $phone_err="Įveskite telefono numerį";
    } else{
        $phone=trim($_POST['phone']);
    }
    if(empty($_POST["email"])){
        $email_err="Įveskite el. paštą";
    } else{
        $email=trim($_POST['email']);
    }
    if(empty($_POST["area"])){
        $area_err="Įveskite sandėlio plotą";
    } else{
        $area=trim($_POST['area']);
    }
    if(empty($_POST["shelves"])){
        $shelves_err="Įveskite lentynų skaičių";
    } else{
        $shelves=trim($_POST['shelves']);
    }
    if(empty($name_err) && empty($city_err) && empty($address_err) && empty($phone_err) && empty($email_err) && empty($area_err) && empty($shelves_err)){
        $sql="INSERT INTO warehouses (name, city, address, phone, email, area, shelves) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($stmt=mysqli_prepare($dbc, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssii", $param_name, $param_city, $param_address, $param_phone, $param_email, $param_area, $param_shelves);
            $param_name=$name;
            $param_city=$city;
            $param_address=$address;
            $param_phone=$phone;
            $param_email=$email;
            $param_area=$area;
            $param_shelves=$shelves;
            if(mysqli_stmt_execute($stmt)){
                header("location:sandeliai.php");
            }
            else{
                echo "Įvyko klaida, bandykite dar kartą";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Sandėliai");
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
                          <a class='nav-link' href='../kurjeris/kurjeris.php'>Kurjeris</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='../kurjeris/skundas.php'>Skundo registravimas</a>
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
    <div class="container">
        <h3>Sandėlio pridėjimas</h3>
        <form method="post">
                <!-- Pavadinimas -->
                <div class="form-group col-sm-12">
                    <label for="name">Sandėlio pavadinimas</label>
                    <input type="text" class="form-control" name="name" placeholder="Pavadinimas" maxlength="50" required>
                </div>

            <!-- Miestas -->
            <div class="form-group col-sm-12">
                <label for="city">Miestas</label>
                <input type="text" class="form-control" name="city" placeholder="Miestas" required>
            </div>

                <!-- Adresas -->
                <div class="form-group col-sm-12">
                    <label for="address">Adresas</label>
                    <input type="text" class="form-control" name="address" placeholder="Adresas" required>
                </div>

                <!-- Telefono numeris -->
                <div class="form-group col-sm-12">
                    <label for="phone">Telefono numeris</label>
                    <input type="tel" class="form-control" name="phone" placeholder="Telefono numeris" maxlength="12" pattern="+[0-9]{11}" required>
                </div>

                <!-- Elektroninis paštas -->
                <div class="form-group col-sm-12">
                    <label for="email">El. paštas</label>
                    <input type="email" class="form-control" name="email" placeholder="El. paštas" maxlength="75" required>

                </div>

                <!-- Plotas -->
                <div class="form-group col-sm-12">
                    <label for="area">Plotas</label>
                    <input type="number" class="form-control" name="area" placeholder="Plotas" required>
                </div>

                <!-- Lentynų skaičius -->
                <div class="form-group col-sm-12">
                    <label for="shelves">Lentynų skaičius</label>
                    <input type="number" class="form-control" name="shelves" placeholder="Lentynos" required>
                </div>

                <!-- Registracijos mygtukas -->
                <input type="submit" name='ok' class="btn btn-primary btn-block mb-2" value="Įrašyti">
            </div>
        </form>
    </div>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>