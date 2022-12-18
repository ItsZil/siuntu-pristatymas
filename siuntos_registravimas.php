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

if (isset($_POST["calculate_price"]))
{
    $package_weight = $_POST["package_weight"];
    $package_size = $_POST["package_size"];
    $package_price = 0;

    // Size
    if ($package_size == "XS")
    {
        $package_price += 0.99;
    }
    else if ($package_size == "S")
    {
        $package_price += 1.99;
    }
    else if ($package_size == "M")
    {
        $package_price += 3.99;
    }
    else if ($package_size == "L")
    {
        $package_price += 4.99;
    }
    else
    {
        $package_price += 5.99;
    }

    $package_price += $package_price * $package_weight; // Weight

    // Distance
    $api_key = getenv('GOOGLE_MAPS_API_KEY');
    $from = "";
    $to = "";

    if (isset($_POST["delivery_method"]) && $_POST["delivery_method"] == "pick_up_point_method")
    {
        // Delivery to a post machine
        $from_id = $_POST["pick_up_point"];
        $to_id = $_POST["delivery_point"];

        $query = "SELECT * FROM post_machines WHERE id ='$from_id'";
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);
        $from = $row["address"];


        $query = "SELECT * FROM post_machines WHERE id ='$to_id'";
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);
        $to = $row["address"];
    }
    else if (isset($_POST["recipient_post_code"]))
    {
        // Delivery to address
        $sender_city = $_POST["sender_city"];
        $sender_post_code = $_POST["sender_post_code"];

        $recipient_city = $_POST["recipient_city"];
        $recipient_post_code = $_POST["recipient_post_code"];

        $from = $sender_city . " " . $sender_post_code;
        $to = $recipient_city . " " . $recipient_post_code;
    }

    $from = str_replace(" ", "+", $from);
    $to = str_replace(" ", "+", $to);

    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=$from&destinations=$to&key=$api_key";
    $json = file_get_contents($url);
    $data = json_decode($json, TRUE);
    $distance = $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Distance in km

    $delivery_days = 1;
    if ($distance < 20)
    {
        $package_price += 1;
    }
    else if ($distance < 100)
    {
        $package_price += 2;
    }
    else if ($distance < 500)
    {
        $package_price += 3;
        $delivery_days += 1;
    }
    else
    {
        $package_price += 9;
        $delivery_days += 2;
    }

    $_SESSION["package_price"] = $package_price;
    $_SESSION["package_weight"] = $package_weight;
    $_SESSION["package_size"] = $package_size;
    $_SESSION["delivery_method"] = $_POST["delivery_method"];

    $_SESSION["sender_name"] = $_POST["sender_name"];
    $_SESSION["sender_surname"] = $_POST["sender_surname"];
    $_SESSION["sender_email"] = $_POST["sender_email"];
    $_SESSION["sender_phone"] = $_POST["sender_phone"];

    $_SESSION["recipient_name"] = $_POST["recipient_name"];
    $_SESSION["recipient_surname"] = $_POST["recipient_surname"];
    $_SESSION["recipient_phone"] = $_POST["recipient_phone"];

    if (isset($_POST["delivery_method"]) && $_POST["delivery_method"] == "address_method")
    {
        $_SESSION["sender_address"] = $_POST["sender_address"];
        $_SESSION["sender_city"] = $_POST["sender_city"];
        $_SESSION["sender_post_code"] = $_POST["sender_post_code"];

        $_SESSION["recipient_address"] = $_POST["recipient_address"];
        $_SESSION["recipient_city"] = $_POST["recipient_city"];
        $_SESSION["recipient_post_code"] = $_POST["recipient_post_code"];
    }
    else
    {
        $_SESSION["pick_up_point"] = $_POST["pick_up_point"];
        $_SESSION["delivery_point"] = $_POST["delivery_point"];
    }
    $_SESSION["delivery_days"] = $delivery_days;

    unset($_POST["calculate_price"]);
    header('Location: siuntos_registravimas.php');
}

if (isset($_POST["register_package"]))
{
    $package_price = $_POST["package_price"];
    $package_weight = $_POST["package_weight"];
    $package_size = $_POST["package_size"];
    $delivery_method = $_POST["delivery_method"];

    $sender_name = $_POST["sender_name"];
    $sender_surname = $_POST["sender_surname"];
    $sender_email = $_POST["sender_email"];
    $sender_phone = $_POST["sender_phone"];

    $sender_city = null;
    $sender_post_code = null;

    $recipient_name = $_POST["recipient_name"];
    $recipient_surname = $_POST["recipient_surname"];
    $recipient_phone = $_POST["recipient_phone"];
    $recipient_city = null;
    $recipient_post_code = null;

    $from_address = null;
    $to_address = null;
    if ($delivery_method == 'address_method')
    {
        $delivery_method = 2; // Delivery to an address

        $from_address = $_POST["sender_address"];
        $sender_city = $_POST["sender_city"];
        $sender_post_code = $_POST["sender_post_code"];

        $to_address = $_POST["recipient_address"];
        $recipient_city = $_POST["recipient_city"];
        $recipient_post_code = $_POST["recipient_post_code"];
    }
    else
    {
        $delivery_method = 1; // Delivery to a post machine

        $pick_up_point = $_POST["pick_up_point"];
        $delivery_point = $_POST["delivery_point"];

        $pick_up_point_query = "SELECT address, city FROM post_machines WHERE id = $pick_up_point";
        $pick_up_point_result = mysqli_query($dbc, $pick_up_point_query);
        $pick_up_point_row = mysqli_fetch_assoc($pick_up_point_result);

        $from_address = $pick_up_point_row["address"];
        $sender_city = $pick_up_point_row["city"];

        $delivery_point_query = "SELECT address, city FROM post_machines WHERE id = $delivery_point";
        $delivery_point_result = mysqli_query($dbc, $delivery_point_query);
        $delivery_point_row = mysqli_fetch_assoc($delivery_point_result);

        $to_address = $delivery_point_row["address"];
        $recipient_city = $delivery_point_row["city"];
    }

    $sender_query = "SELECT id FROM clients WHERE name = '$sender_name' AND surname = '$sender_surname' AND address = '$from_address' AND city = '$sender_city' AND post_code = '$sender_post_code' AND phone = '$sender_phone' AND email = '$sender_email' AND type = 1";
    $sender_result = mysqli_query($dbc, $sender_query);
    if (mysqli_num_rows($sender_result) == 0)
    {
        $sender_query = "INSERT INTO clients (name, surname, address, city, post_code, phone, email, type) VALUES ('$sender_name', '$sender_surname', '$from_address', '$sender_city', '$sender_post_code', '$sender_phone', '$sender_email', 1)";
        mysqli_query($dbc, $sender_query);
    }

    // Get the id of the sender
    $sender_id = mysqli_insert_id($dbc);

    $recipient_query = "SELECT id FROM clients WHERE name = '$recipient_name' AND surname = '$recipient_surname' AND address = '$to_address' AND city = '$recipient_city' AND post_code = '$recipient_post_code' AND phone = '$recipient_phone' AND type = 2";
    $recipient_result = mysqli_query($dbc, $recipient_query);
    if (mysqli_num_rows($recipient_result) == 0)
    {
        $recipient_query = "INSERT INTO clients (name, surname, address, city, post_code, phone, type) VALUES ('$recipient_name', '$recipient_surname', '$to_address', '$recipient_city', '$recipient_post_code', '$recipient_phone', 2)";
        mysqli_query($dbc, $recipient_query);
    }

    // Get the id of the recipient
    $recipient_id = mysqli_insert_id($dbc);

    // Insert the package into the database, for delivery_date use DATETIME
    $delivery_date = date("Y-m-d H:i:s");
    if (isset($_SESSION["delivery_days"]))
    {
        $delivery_days = $_SESSION["delivery_days"];
        $delivery_date = date('Y-m-d', strtotime("+$delivery_days days"));
    }
    $delivery_date = $delivery_date . " 12:00:00";

    $package_query = "INSERT INTO packages (sender_id, recipient_id, from_address, to_address, delivery_method, planned_delivery_date, price, weight, size) VALUES ('$sender_id', '$recipient_id', '$from_address', '$to_address', '$delivery_method', '$delivery_date', '$package_price', '$package_weight', '$package_size')";
    mysqli_query($dbc, $package_query);

    if (mysqli_affected_rows($dbc) == 1)
    {
        $_SESSION["notification_message"] = "Nauja siunta sėkmingai užregistruota. Jūsų siuntos kodas: " . mysqli_insert_id($dbc);
        $_SESSION["notification_status"] = 1;
    }
    else
    {
        $_SESSION["notification_message"] = "Siuntos registracija nepavyko. Bandykite dar kartą arba susisiekite su mumis.";
        $_SESSION["notification_status"] = 0;
    }

    unset($_POST["register_package"]);

    unset($_SESSION["delivery_days"]);
    unset($_SESSION["package_price"]);
    unset($_SESSION["package_weight"]);
    unset($_SESSION["package_size"]);
    unset($_SESSION["sender_name"]);
    unset($_SESSION["sender_surname"]);
    unset($_SESSION["sender_email"]);
    unset($_SESSION["sender_phone"]);
    unset($_SESSION["sender_address"]);
    unset($_SESSION["sender_city"]);
    unset($_SESSION["sender_post_code"]);
    unset($_SESSION["recipient_name"]);
    unset($_SESSION["recipient_surname"]);
    unset($_SESSION["recipient_phone"]);
    unset($_SESSION["recipient_address"]);
    unset($_SESSION["recipient_city"]);
    unset($_SESSION["recipient_post_code"]);
    unset($_SESSION["pick_up_point"]);
    unset($_SESSION["delivery_point"]);
    unset($_SESSION["delivery_method"]);
    unset($_SESSION["package_price"]);
    unset($_SESSION["package_weight"]);
    unset($_SESSION["package_size"]);
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "includes/header.php";
        echo getHeader("Siuntos registravimas");
    ?>
</head>
<script>
    $(()=> {
        $("#delivery_method").on("change", () => {
            if ($("#delivery_method").val() == "pick_up_point_method")
            {
                $("#address_method").attr("hidden", true);
                $("#pick_up_method").attr("hidden", false);

                $("#sender_address").attr("required", false);
                $("#sender_city").attr("required", false);
                $("#sender_post_code").attr("required", false);

                $("#recipient_address").attr("required", false);
                $("#recipient_city").attr("required", false);
                $("#recipient_post_code").attr("required", false);
            }
            else
            {
                $("#address_method").attr("hidden", false);
                $("#pick_up_method").attr("hidden", true);

                $("#sender_address").attr("required", true);
                $("#sender_city").attr("required", true);
                $("#sender_post_code").attr("required", true);

                $("#recipient_address").attr("required", true);
                $("#recipient_city").attr("required", true);
                $("#recipient_post_code").attr("required", true);
            }
        });
    });
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
                  <a class="nav-link" href="index.php">Pagrindinis</a>
                  </li>

                  <li class="nav-item">
                      <a class="nav-link" aria-current="page" href="sekimas.php">Sekimas</a>
                  </li>

                  <li class='nav-item'>
                      <a class='nav-link' href='klausti.php'>Klausti</a>
                  </li>

                  <li class='nav-item'>
                      <a class='nav-link active' aria-current="page" href='siuntos_registravimas.php'>Siuntos registravimas</a>
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
                    <a class='nav-link' href='admin/administratorius.php'>Administratorius</a>
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
                <h1>Naujos siuntos registracija</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-auto">
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
                        <label for="sender_name" class="form-label">Jūsų vardas</label>
                        <input type="name" class="form-control" id="sender_name" name="sender_name" value="<?php if (isset($_SESSION["sender_name"])) echo $_SESSION["sender_name"]; else echo null ?>" maxlength="50" required>
                        <br>
                        <label for="sender_surname" class="form-label">Jūsų pavardė</label>
                        <input type="surname" class="form-control" id="sender_surname" name="sender_surname" value="<?php if (isset($_SESSION["sender_surname"])) echo $_SESSION["sender_surname"]; else echo null ?>" maxlength="50" required>
                        <br>
                        <label for="sender_email" class="form-label">El. paštas</label>
                        <input type="email" class="form-control" id="sender_email" name="sender_email" value="<?php if (isset($_SESSION["sender_email"])) echo $_SESSION["sender_email"]; else echo null ?>" maxlength="75" required>
                        <br>
                        <label for="sender_phone" class="form-label">Telefono numeris</label>
                        <input type="text" class="form-control" id="sender_phone" name="sender_phone" value="<?php if (isset($_SESSION["sender_phone"])) echo $_SESSION["sender_phone"]; else echo null ?>" maxlength="12" required>
                        <br>

                        <label for="sender_name" class="form-label">Gavėjo vardas</label>
                        <input type="name" class="form-control" id="recipient_name" name="recipient_name" value="<?php if (isset($_SESSION["recipient_name"])) echo $_SESSION["recipient_name"]; else echo null ?>" maxlength="50" required>
                        <br>
                        <label for="sender_surname" class="form-label">Gavėjo pavardė</label>
                        <input type="surname" class="form-control" id="recipient_surname" name="recipient_surname" value="<?php if (isset($_SESSION["recipient_surname"])) echo $_SESSION["recipient_surname"]; else echo null ?>" maxlength="50" required>
                        <br>
                        <label for="recipient_phone" class="form-label">Gavėjo telefono numeris</label>
                        <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" value="<?php if (isset($_SESSION["recipient_phone"])) echo $_SESSION["recipient_phone"]; else echo null ?>" maxlength="12" required>
                        <br><br>

                        <label for="delivery_method" class="form-label">Pristatymo būdas</label>
                        <select class="form-select" id="delivery_method" name="delivery_method" required>
                            <option value="pick_up_point_method" <?php if (isset($_SESSION["delivery_method"]) && $_SESSION['delivery_method'] == 'pick_up_point_method') { echo 'selected'; } ?>>Paštomatas</option>
                            <option value="address_method" <?php if (isset($_SESSION["delivery_method"]) && $_SESSION['delivery_method'] == 'address_method') { echo 'selected'; } ?>>Adresas</option>
                        </select>
                        <br>

                        <div id="address_method" <?php if (isset($_SESSION["delivery_method"]) && $_SESSION['delivery_method'] == 'address_method') echo 'visible'; else echo 'hidden' ?>>
                            <label for="sender_address" class="form-label">Siuntėjo adresas</label>
                            <input type="text" class="form-control" id="sender_address" name="sender_address" value="<?php if (isset($_SESSION["sender_address"])) echo $_SESSION["sender_address"]; else echo null ?>" maxlength="100">
                            <br>
                            <label for="sender_city" class="form-label">Siuntėjo miestas</label>
                            <input type="text" class="form-control" id="sender_city" name="sender_city" value="<?php if (isset($_SESSION["sender_city"])) echo $_SESSION["sender_city"]; else echo null ?>" maxlength="50">
                            <br>
                            <label for="sender_post_code" class="form-label">Siuntėjo pašto kodas</label>
                            <input type="text" class="form-control" id="sender_post_code" name="sender_post_code" value="<?php if (isset($_SESSION["sender_post_code"])) echo $_SESSION["sender_post_code"]; else echo null ?>" maxlength="10">
                            <br>

                            <br>

                            <label for="recipient_address" class="form-label">Gavėjo adresas</label>
                            <input type="text" class="form-control" id="recipient_address" name="recipient_address" value="<?php if (isset($_SESSION["recipient_address"])) echo $_SESSION["recipient_address"]; else echo null ?>" maxlength="100">
                            <br>
                            <label for="recipient_city" class="form-label">Gavėjo miestas</label>
                            <input type="text" class="form-control" id="recipient_city" name="recipient_city" value="<?php if (isset($_SESSION["recipient_city"])) echo $_SESSION["recipient_city"]; else echo null ?>" maxlength="50">
                            <br>
                            <label for="recipient_post_code" class="form-label">Gavėjo pašto kodas</label>
                            <input type="text" class="form-control" id="recipient_post_code" name="recipient_post_code" value="<?php if (isset($_SESSION["recipient_post_code"])) echo $_SESSION["recipient_post_code"]; else echo null ?>" maxlength="10">
                            <br>
                        </div>

                        <div id="pick_up_method" <?php if (isset($_SESSION["delivery_method"]) && $_SESSION['delivery_method'] == 'pick_up_point_method') echo 'visible';
                            else if (isset($_SESSION["delivery_method"]) && $_SESSION['delivery_method'] == 'address_method') echo 'hidden' ?>>

                            <label for="pick_up_point" class="form-label>">Siuntos paėmimo paštomatas</label>
                            <select class="form-select" id="pick_up_point" name="pick_up_point" required>
                                <?php
                                    $sql = "SELECT * FROM post_machines";
                                    $result = mysqli_query($dbc, $sql);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $id = $row["id"];
                                        $name = $row["name"];
                                        $city = $row["city"];
                                        $address = $row["address"];
                                        if (isset($_SESSION['pick_up_point']) && $_SESSION['pick_up_point'] == $id)
                                        {
                                            echo "<option value='$id' selected>$name, $city</option>";
                                        }
                                        else
                                        {
                                            echo "<option value='$id'>$name, $city</option>";
                                        }
                                    }
                                ?>
                            </select>
                            <br>
                            <label for="delivery_point" class="form-label">Siuntos pristatymo paštomatas</label>
                            <select class="form-select" id="delivery_point" name="delivery_point" required>
                                <?php
                                    $sql = "SELECT * FROM post_machines";
                                    $result = mysqli_query($dbc, $sql);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $id = $row["id"];
                                        $name = $row["name"];
                                        $city = $row["city"];
                                        $address = $row["address"];
                                        if (isset($_SESSION['delivery_point']) && $_SESSION['delivery_point'] == $id)
                                        {
                                            echo "<option value='$id' selected>$name, $city</option>";
                                        }
                                        else
                                        {
                                            echo "<option value='$id'>$name, $city</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <br>
                        <label for="package_weight" class="form-label">Siuntos svoris (kg)</label>
                        <input type="number" class="form-control" name="package_weight"  min="0" step="0.1" placeholder="Svoris (kg)" value="<?php if (isset($_SESSION["package_weight"])) echo $_SESSION["package_weight"]; else echo null ?>" required>
                        <br>
                        <label for="package_size">Siuntos dydis:</label>
                        <select class="form-control" name="package_size">
                            <option <?php if (isset($_SESSION["package_size"]) && $_SESSION["package_size"] == 'XS') echo 'selected'?>>XS</option>
                            <option <?php if (isset($_SESSION["package_size"]) && $_SESSION["package_size"] == 'S') echo 'selected'?>>S</option>
                            <option <?php if (isset($_SESSION["package_size"]) && $_SESSION["package_size"] == 'M') echo 'selected'?>>M</option>
                            <option <?php if (isset($_SESSION["package_size"]) && $_SESSION["package_size"] == 'L') echo 'selected'?>>L</option>
                            <option <?php if (isset($_SESSION["package_size"]) && $_SESSION["package_size"] == 'XL') echo 'selected'?>>XL</option>
                        </select>
                        <br>
                        <label for="price" class="form-label">Siuntos kaina:</label>
                        <div class="input-group mb3">
                            <input type="text" class="form-control" id="package_price" name="package_price" value="<?php if (isset($_SESSION["package_price"])) echo $_SESSION["package_price"]; else echo null ?>" readonly>
                            <input type='submit' name='calculate_price' class='btn btn-primary float-end' value="Skaičiuoti kainą">
                        </div>
                    </div>
                    <input type='submit' name='register_package' class='btn btn-primary' value="Registruoti siuntą">
                </form>
            </div>
        </div>
    </div>
    <?php
        include_once "includes/footer.html";
    ?>
</body>
</html>