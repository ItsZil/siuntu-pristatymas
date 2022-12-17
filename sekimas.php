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

if (isset($_POST["track_package"]))
{
    $package_tracking_number = $_POST['package_tracking_number'];
    $package_id = 0;

    $_SESSION['package_tracking_number'] = $package_tracking_number;
    $_SESSION['package_id'] = $package_id;

    unset($_POST['track_package']);
    header('Location: sekimas.php');
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
    echo getHeader("Sekimas");
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
                    <a class="nav-link" aria-current="page" href="index.php">Pagrindinis</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="sekimas.php">Sekimas</a>
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

<div class="container">
    <div class="container">
        <div class="col-12">
            <h1>Siuntos sekimas</h1>
            <hr>
        </div>
    </div>
    <div class="container">
        <div class="col-12">
            <form method="post">
                <div class="mb-3">
                    <label for="package_tracking_number" class="form-label">Įveskite siuntos numerį, kurią norite sekti ar valdyti:</label>
                    <?php
                        if (isset($_SESSION["package_tracking_number"]))
                        {
                            $package_tracking_number = $_SESSION["package_tracking_number"];
                            echo "<input type='number' class='form-control' name='package_tracking_number'  placeholder='Siuntos numeris' value='$package_tracking_number' required>";
                        }
                        else
                        {
                            echo "<input type='number' class='form-control' name='package_tracking_number'  placeholder='Siuntos numeris' required>";
                        }
                    ?>
                </div>
                <input type='submit' name='track_package' class='btn btn-primary float-end' value="Sekti">
            </form>
        </div>
    </div>
</div>

<?php
    if (isset($_SESSION["package_id"]))
    {
        echo "<div class='container'>
                <div class='container'>
                    <div class='col-12'>
                        <h1>Siuntos informacija</h1>
                        <hr>
                    </div>
                </div>
                <div class='container'>
                    <div class='col-12'>
                        <table class='table table-striped'>
                            <thead>
                                <tr>
                                    <th scope='col'>Siuntos būsena</th>
                                    <th scope='col'>Siuntos dydis</th>
                                    <th scope='col'>Siuntos išsiuntimo data</th>
                                    <th scope='col'>Siuntos pristatymo data</th>
                                    <th scope='col'>Siuntos būsenos aprašymas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Rūšiuojama</td>
                                    <td>XS</td>
                                    <td>2022-11-07</td>
                                    <td>2022-11-08</td>
                                    <td>Siunta atvyko į rūšiavimo skyrių.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ";
    }
    else if (isset($_SESSION["track_package"]))
    {
        echo "<p>Siunta pateiktu numeriu nerasta.</p>";
    }
?>

<br>
<?php
    if (isset($_SESSION["package_id"]))
    {
        echo "<div class='container'>
                <div class='container'>
                    <div class='col-12'>
                        <h1>Siuntos peradresavimas</h1>
                        <hr>
                    </div>
                </div>
                <form method='post'>
                    <label for='delivery_point' class='form-label'>Pasirinkite, į kurį paštomatą norėtumėte peradresuoti:</label>
                    <select class='form-select' id='delivery_point' name='delivery_point' required>
                        <option value='1'>KTU Studentų Miestelis, Kaunas</option>
                        <option value='2'>Rimi Varniai, Kaunas</option>
                    </select>
                    <br>
                    <label for='post_code' class='form-label'>Įveskite savo pašto kodą, kad patvirtinti tapatybę:</label>
                    <input type='number' class='form-control' name='post_code'  placeholder='Pašto kodas' required>
                    <br>
                    <input type='submit' name='redirect_package' class='btn btn-primary float-end' value='Peradresuoti siuntą'>
                </form>
            </div>
        ";
    }
?>


<?php
include_once "includes/footer.html";
?>
</body>
</html>