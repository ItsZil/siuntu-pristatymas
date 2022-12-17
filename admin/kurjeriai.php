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
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Kurjeriai");
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
        <div class="container">
            <div class="col-12">
                <h1>Kurjerių sąrašas</h1>
                <hr>
            </div>
        </div>

        <div class="container">
                <form>
                    <a class='btn btn-primary me-1' href='kurjerio_pridejimas.php'>Pridėti kurjerį</a>
                </form>
            </div>

        <table class='table table-striped'>
            <thead>
            <tr>
                <th>Vardas</th>
                <th>Pavardė</th>
                <th>Telefono numeris</th>
                <th>Veiksmai</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Kurjeris1</td>
                <td>Kurjeris1</td>
                <td>+37068724675</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>
            </tr
            <tr>
                <td>Kurjeris2</td>
                <td>Kurjeris2</td>
                <td>+37068724675</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>

            </tr>
            <tr>
                <td>Kurjeris3</td>
                <td>Kurjeris3</td>
                <td>+37068724675</td>
                <td><button class="btn btn-primary me-1">Redaguoti</button><button class="btn btn-primary ms-3">Ištrinti</button></td>

            </tr>
            </tbody>
        </table>
    </div>



    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>