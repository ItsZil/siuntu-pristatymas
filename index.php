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

# Siuntos sekimas
if (isset($_POST["track_package"]))
{
    $_SESSION['package_tracking_number'] = $_POST["package_tracking_number"];

    header('Location: sekimas.php');
}

# Siuntos registravimas
if (isset($_POST["register_package"]))
{
    $package_weight = $_POST["package_weight"];
    $package_size = $_POST["package_size"];

    header('Location: siuntos_registravimas.php');
}

if (!$dbc)
{
    die ("Nepavyko prisijungti prie duomenų bazės:" .mysqli_error($dbc));
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "includes/header.php";
        echo getHeader("Pagrindinis");
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
                  <a class="nav-link active" aria-current="page" href="index.php">Pagrindinis</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="sekimas.php">Sekimas</a>
                </li>

                <li class='nav-item'>
                  <a class='nav-link' href='klausti.php'>Klausti</a>
                </li>

                <li class='nav-item'>
                    <a class='nav-link' href='siuntos_registravimas.php'>Siuntos registravimas</a>
                </li>

                <?php
                  # MENU
                if ($_SESSION["access_level"] == 2) # Kurjeris
                {
                    echo
                    "<li class='nav-item'>
                          <a class='nav-link' href='kurjeris/kurjeris.php'>Kurjeris</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='kurjeris/skundas.php'>Skundo registravimas</a>
                      </li>";
                }
                if ($_SESSION["access_level"] == 3) # Administratorius
                {
                    echo "<li class='nav-item'>
                    <a class='nav-link active' href='admin/administratorius.php'>Administratorius</a>
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

    <div class="container-sm w-50">
        <div id="indicators" class="carousel slide" data-bs-ride="true">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#indicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#indicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#indicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/img/carousel1.png" class="d-block w-100">
                </div>
                <div class="carousel-item">
                    <img src="assets/img/carousel2.png" class="d-block w-100">
                </div>
                <div class="carousel-item">
                    <img src="assets/img/carousel3.png" class="d-block w-100">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#indicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#indicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <br>

    <!-- Package tracking and initial registration forms -->
    <div class="container w-50">
        <div class="row">
            <div class="col-sm-6 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-header">Siuntos sekimas</div>
                    <div class="card-body">
                        <div class="container-sm">
                            <!-- Package tracking form -->
                            <form method="post">
                                <div class="mb-3">
                                    <label for="package_tracking_number" class="form-label">Įveskite siuntos numerį, kurią norite sekti ar valdyti:</label>
                                    <input type="number" class="form-control" name="package_tracking_number" placeholder="Siuntos numeris" required>
                                </div>
                                <br><br>
                                <input type='submit' name='track_package' class='btn btn-primary float-end' value='Sekti'>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-header">Siuntos registracija</div>
                    <div class="card-body">
                        <div class="container-sm">
                            <!-- Package registration form -->
                            <form method="post">
                                <div class="form-group">
                                    <input type="number" class="form-control" name="package_weight"  min="0" step="0.1" placeholder="Svoris (kg)" required>
                                    <br>
                                    <label for="package_size">Siuntos dydis:</label>
                                    <select class="form-control" name="siuntos_dydis">
                                        <option>XS</option>
                                        <option>S</option>
                                        <option>M</option>
                                        <option>L</option>
                                        <option>XL</option>
                                    </select>
                                </div>
                                <br>
                                <input type='submit' name='register_package' class='btn btn-primary float-end' value='Registruoti'>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Terminal map -->
        <div class="card w-100">
            <div class="card-header">Paštomatų žemėlapis</div>
            <div class="card-body">
                <div class="container">
                    <iframe src="https://storage.googleapis.com/maps-solutions-pg19vlu2ft/locator-plus/aoi4/locator-plus.html"
                            width="100%" height="450"
                            style="border:0;"
                            loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <?php
        include_once "includes/footer.html";
    ?>
</body>
</html>