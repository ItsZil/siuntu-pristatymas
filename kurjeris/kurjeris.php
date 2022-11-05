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
if (isset($_POST["select_packages"]))
{
    $_SESSION["show_map"] = 1;
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
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='skundas.php'>Skundo registravimas</a>
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
                <th>Pristatymo adresas</th>
                <th>Statusas</th>
                <th>Dydis</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pasirinkta"></td>
                <td>31657</td>
                <td>2022-11-05</td>
                <td>2022-11-08</td>
                <td>Vilniaus g. 17, Utena, 34678</td>
                <td>Nepristatyta</td>
                <td>M</td>
            </tr>
            <tr>
                <td><div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pasirinkta"></td>
                <td>31658</td>
                <td>2022-11-04</td>
                <td>2022-11-06</td>
                <td>Šilo g. 25, Anykščiai, 67541</td>
                <td>Nepristatyta</td>
                <td>L</td></tr>
            <tr>
                <td><div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pasirinkta"></td>
                <td>31659</td>
                <td>2022-11-03</td>
                <td>2022-11-10</td>
                <td>Mokyklos g. 5, Molėtai, 49735</td>
                <td>Užsakyta</td>
                <td>M</td></tr>
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
                        <label for="milage" class="form-label">Rida</label>
                        <input type="text" class="form-control" id="milage" name="milage" required>
                        <br>
                        <label for="fuel" class="form-label">Kuro sanaudos</label>
                        <input type="text" class="form-control" id="fuel" name="fuel" required>
                    </div>
                    <input type='submit' name='register_milage' class='btn btn-primary float-end' value="Pateikti">
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