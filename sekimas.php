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

    // Check if the package exists
    $sql = "SELECT * FROM packages WHERE id = '$package_tracking_id'";
    $result = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);

    if ($count == 1)
    {
        $to_address = $row['to_address'];
        $planned_delivery_date = $row['planned_delivery_date'];
        $size = $row['size'];
        $long_status = $row['status'];
        $recipient_id = $row['recipient_id'];
        $sender_id = $row['sender_id'];
        $delivery_method = $row['delivery_method'];

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

        $_SESSION['package_tracking_id'] = $package_tracking_id;
        $_SESSION['delivery_method'] = $delivery_method;
        $_SESSION['to_address'] = $to_address;
        $_SESSION['planned_delivery_date'] = $planned_delivery_date;
        $_SESSION['size'] = $size;
        $_SESSION['long_status'] = $long_status;
        $_SESSION['short_status'] = $short_status;
        $_SESSION['recipient_id'] = $recipient_id;
        $_SESSION['sender_id'] = $sender_id;
    }
    else
    {
        $_SESSION["notification_message"] = "Siuntos numeriu $package_tracking_id nepavyko rasti. Įsitikinkite, kad įvedate teisingą siuntos numerį.";
        $_SESSION["notification_status"] = 0;
    }
    unset($_POST['track_package']);
}

if (isset($_POST["redirect_package"]))
{
    $package_tracking_id = $_SESSION['package_tracking_id'];
    $delivery_point = $_POST['delivery_point'];
    $confirmation_phone = $_POST['confirmation_phone'];

    $recipient_id = $_SESSION['recipient_id'];
    $sender_id = $_SESSION['sender_id'];

    // Check if the confirmation_phone matches the recipient's or sender's phone number
    $sql = "SELECT phone FROM clients WHERE id = '$recipient_id' OR id = '$sender_id'";
    $result = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);
    if ($count >= 1)
    {
        $phone = $row['phone'];
        if ($phone == $confirmation_phone)
        {
            $sql = "SELECT address FROM post_machines WHERE id = '$delivery_point'";
            $result = mysqli_query($dbc, $sql);
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $new_to_address = $row['address'];

            if ($new_to_address == $_SESSION['to_address'])
            {
                $_SESSION["notification_message"] = "Siuntos peradresuoti į tą patį paštomatą negalite.";
                $_SESSION["notification_status"] = 0;
                die();
            }

            $sql = "UPDATE packages SET to_address = '$new_to_address', planned_delivery_date = DATE_ADD(planned_delivery_date, INTERVAL 1 DAY) WHERE id = '$package_tracking_id'";
            $result = mysqli_query($dbc, $sql);

            $_SESSION["notification_message"] = "Siunta sėkmingai peardresuota į $new_to_address.";
            $_SESSION["notification_status"] = 1;

            $_SESSION["to_address"] = $new_to_address;
            header('Location: sekimas.php');
        }
        else
        {
            $_SESSION["notification_message"] = "Nepavyko patvirtinti tapatybės. Įsitikinkite, kad įvedate tą patį telefono numerį, kuris buvo naudotas registruojant siuntą.";
            $_SESSION["notification_status"] = 0;
        }
    }
    else
    {
        $_SESSION["notification_message"] = "Nepavyko patvirtinti tapatybės. Įsitikinkite, kad įvedate tą patį telefono numerį, kuris buvo naudotas registruojant siuntą.";
        $_SESSION["notification_status"] = 0;
    }
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
    if (isset($_SESSION["short_status"]) && isset($_SESSION["package_tracking_id"]))
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
                                    <th scope='col'>Būsena</th>
                                    <th scope='col'>Dydis</th>
                                    <th scope='col'>Numatyta pristatymo data</th>   
                                    <th scope='col'>Pristatymo adresas</th>
                                    <th scope='col'>Būsenos aprašymas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>".$_SESSION["short_status"]."</td>
                                    <td>".$_SESSION["size"]."</td>
                                    <td>".$_SESSION["planned_delivery_date"]."</td>
                                    <td>".$_SESSION["to_address"]."</td>
                                    <td>".$_SESSION["long_status"]."</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ";
    }
?>

<br>
<?php
    if (isset($_SESSION["recipient_id"]) && isset($_SESSION["package_tracking_id"]) && isset($_SESSION["delivery_method"]) && $_SESSION["delivery_method"] == 1)
    {
        echo "<div class='container'>
                <div class='container'>
                    <div class='col-12'>
                        <h1>Siuntos peradresavimas</h1>
                        <hr>
                    </div>
                </div>
                <form method='post'>
                    <label>Siuntos peradresavimas galimas tik į paštomatą, esantį tame pačiame mieste, į kurį siunta pristatoma.</label>
                    <br><br>
                    <label for='delivery_point' class='form-label'>Pasirinkite, į kurį paštomatą norėtumėte peradresuoti:</label>
                    <select class='form-select' id='delivery_point' name='delivery_point' required>";
                    // Print all post_machines in the same city as the recipient (saved in the clients table)
                    $sql = "SELECT * FROM post_machines WHERE city = (SELECT city FROM clients WHERE id = ".$_SESSION["recipient_id"].")";
                    $result = $dbc->query($sql);
                    while ($row = $result->fetch_assoc())
                    {
                        echo "<option value='".$row["id"]."'>".$row["name"]. ", ". $row["city"]."</option>";
                    }
                    echo "</select>
                    <br>
                    <label for='confirmation_phone' class='form-label'>Įveskite savo telefono numerį, kad patvirtinti tapatybę:</label>
                    <input type='number' class='form-control' name='confirmation_phone'  placeholder='Telefono numeris' required>
                    <br>
                    <input type='submit' name='redirect_package' class='btn btn-primary float-end' value='Peradresuoti siuntą'>
                </form>
            </div>
        ";
    }
    else if (isset($_SESSION["delivery_method"]) && $_SESSION["delivery_method"] == 2)
    {
        echo "<div class='container'>
                <div class='container'>
                    <div class='col-12'>
                        <h1>Siuntos peradresavimas</h1>
                        <hr>
                    </div>
                </div>
                <div class='container'>
                    <div class='col-12'>
                        <label>Siuntos peradresavimas galimas tik į paštomatą, esantį tame pačiame mieste, į kurį siunta pristatoma.</label>
                        <br><br>
                        <label>Siunta pristatomas tiesiogiai į gavėjo namus, todėl peradresuoti siuntą į kitą adresą negalima.</label>
                    </div>
                </div>
            </div>
        ";
    }
?>


<?php
include_once "includes/footer.html";
?>
</body>
</html>