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
    $package_tracking_id = $_POST['package_tracking_id'];
    $_SESSION['package_tracking_id'] = $package_tracking_id;

    // Check if the package exists
    $sql = "SELECT * FROM packages WHERE id = '$package_tracking_id'";
    $result = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);

    if ($count == 1)
    {
        $from_address = $row['from_address'];
        $to_address = $row['to_address'];
        $planned_delivery_date = $row['planned_delivery_date'];
        $size = $row['size'];
        $long_status = $row['status'];

        $short_status = "Užregistruota";
        if ($long_status == "Siunta atvyko į sandelį")
        {
            $short_status = "Rūšiuojama";
        }
        else if ($long_status == "Siunta išvežta pristatymui")
        {
            $short_status = "Išvežta pristatymui";
        }
        else if ($long_status == "Siunta pristatyta")
        {
            $short_status = "Pristatyta";
        }
        else
        {
            $short_status == $long_status;
        }

        $_SESSION['from_address'] = $from_address;
        $_SESSION['to_address'] = $to_address;
        $_SESSION['planned_delivery_date'] = $planned_delivery_date;
        $_SESSION['size'] = $size;
        $_SESSION['long_status'] = $long_status;
        $_SESSION['short_status'] = $short_status;
    }
    else
    {
        $_SESSION["notification_message"] = "Siunta $package_tracking_id nerasta. Įsitikinkite, kad įvedate teisingą siuntos numerį.";
        $_SESSION["notification_status"] = 0;

        unset($_SESSION['package_tracking_id']);
    }
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
            <?php
                // Show the notification message from session
                if (isset($_SESSION["notification_message"]))
                {
                    //die($_SESSION["notification_status"]);
                    $message = $_SESSION["notification_message"];
                    if ($_SESSION["notification_status"] == 1)
                    {
                        echo "<div class='alert alert-success' role='alert'>$message</div>";
                    }
                    else
                    {
                        echo "<div class='alert alert-danger' role='alert'>$message</div>";
                    }
                    unset($_SESSION["notification_message"]);
                    unset($_SESSION["notification_status"]);
                }
            ?>

            <form method="post">
                <div class="mb-3">
                    <label for="package_tracking_id" class="form-label">Įveskite siuntos numerį, kurią norite sekti ar valdyti:</label>
                    <input type='number' class='form-control' name='package_tracking_id'  placeholder='Siuntos numeris'  value="<?php if (isset($_SESSION["package_tracking_id"])) echo $_SESSION["package_tracking_id"]; else echo null ?>" required>
                </div>
                <input type='submit' name='track_package' class='btn btn-primary float-end' value="Sekti">
            </form>
        </div>
    </div>
</div>

<?php
    if (isset($_SESSION["short_status"]))
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
                                    <th scope='col'>Numatyta pristatymo data</th>
                                    <th scope='col'>Siuntos būsenos aprašymas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>".$_SESSION["short_status"]."</td>
                                    <td>".$_SESSION["size"]."</td>
                                    <td>".$_SESSION["planned_delivery_date"]."</td>
                                    <td>".$_SESSION["long_status"]."</td>
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