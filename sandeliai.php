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
                      <a class="nav-link" href="index.php">Pagrindinis</a>
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
                          <a class='nav-link' href='kurjeris.php'>Kurjeris</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='skundas.php'>Skundo registravimas</a>
                      </li>
                      <li class='nav-item'>
                          <a class='nav-link' href='navigacija.php'>Navigacija</a>
                      </li>";
                  }
                  if ($_SESSION["access_level"] == 3) # Administratorius
                  {
                      echo
                      "<li class='nav-item'>
                    <a class='nav-link' href='uzklausos.php'>Užklausos</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='skundai.php'>Skundai</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link' href='kurjeriai.php'>Kurjeriai</a>
                    </li>
                    <li class='nav-item'>
                    <a class='nav-link active' aria-current='page' href='sandeliai.php'>Sandėliai</a>
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
                <h1>Sandėlių sąrašas</h1>
                <hr>
            </div>
        </div>

        <table class='table table-striped'>
            <thead>
            <tr>
                <th>Pavadinimas</th>
                <th>Adresas</th>
                <th>Telefono numeris</th>
                <th>El. paštas</th>
                <th>Plotas kv. m</th>
                <th>Lentynų skaičius</th>
                <th>Veiksmai</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Sandėlis1</td>
                <td>Liepos g. 15, Kaunas, 50278</td>
                <td>+37069728643</td>
                <td>sandelis1@gmail.com</td>
                <td>50</td>
                <td>150</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>
            </tr
            <tr>
                <td>Sandėlis2</td>
                <td>Aušros g. 17, Kaunas, 50278</td>
                <td>+37062157846</td>
                <td>sandelis2@gmail.com</td>
                <td>35</td>
                <td>120</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>
            </tr>
            <tr>
                <td>Sandėlis3</td>
                <td>Pramonės prospektas 198, Kaunas, 50278</td>
                <td>+37068432617</td>
                <td>sandelis3@gmail.com</td>
                <td>25</td>
                <td>100</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
        include_once "includes/footer.html";
    ?>
</body>
</html>