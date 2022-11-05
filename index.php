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
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
          <div class="container-fluid">
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                  <a class="nav-link active" aria-current="page" href="#">Pagrindinis</a>
                </li>

                <li class='nav-item'>
                  <a class='nav-link' href='terminals.php'>Paštomatai</a>
                </li>

                <li class='nav-item'>
                  <a class='nav-link' href='question.php'>Klauskite</a>
                </li>
                  <li class='nav-item'>
                      <a class='nav-link' href='newparcel.php'>Siuntos registravimas</a>
                  </li>

                <?php
                  # MENU
                  if ($_SESSION["access_level"] == 2) # Kurjeris
                  {
                    echo
                      "<li class='nav-item'>
                          <a class='nav-link' href='courier.php'>Kurjeris</a>
                      </li>";
                  }
                  if ($_SESSION["access_level"] == 3) # Administratorius
                  {
                    echo
                    "<li class='nav-item'>
                    <a class='nav-link' href=''>[ADMIN] 1</a>
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
                            <a class='btn btn-outline-success' href='login.php'>Prisijungti</a>
                        </form>";
                  }
        ?>
        </div>
    </nav>
    <br>

    <!-- Image carousel -->
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

    <!-- Package tracking and 1 other -->
    <div class="container w-50">
        <div class="row">
            <div class="col-sm-6 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-header">Siuntos sekimas</div>
                    <div class="card-body">
                        <div class="container-sm">
                            <!-- Package tracking form -->
                            <form action="sekimas.php" method="post">
                                <div class="mb-3">
                                    <label for="package_id" class="form-label">Įveskite siuntos numerį, kurią norite sekti ar valdyti:</label>
                                    <input type="text" class="form-control" id="package_id" placeholder="Siuntos numeris" required>
                                </div>
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
                            <form action="sekimas.php" method="post">
                                <div class="mb-3">
                                    <div class="input-group mb3">
                                        <input type="number" class="form-control" id="package_weight"  min="0" step="0.1" placeholder="Svoris (kg)" required>
                                        <input type="number" class="form-control" id="package_length" min="10" placeholder="Ilgis (cm)" required>
                                    </div>
                                    <br>
                                    <div class="input-group mb3">
                                        <input type="number" class="form-control" id="package_width" min="10" placeholder="Plotis (cm)" required>
                                        <input type="number" class="form-control" id="package_height" min="10" placeholder="Aukštis (cm)" required>
                                    </div>
                                </div>
                                <input type='submit' name='register_package' class='btn btn-primary float-end' value='Registruoti'>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php
        include_once "includes/footer.html";
    ?>
</body>
</html>