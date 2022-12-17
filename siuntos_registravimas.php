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

    if ($package_size == "small")
    {
        $package_price = 1.5;
    }
    else if ($package_size == "medium")
    {
        $package_price = 2.5;
    }
    else if ($package_size == "large")
    {
        $package_price = 3.5;
    }

    $package_price = $package_price * $package_weight;

    $_SESSION["package_price"] = $package_price;
    $_SESSION["package_weight"] = $package_weight;
    $_SESSION["package_size"] = $package_size;

    header('Location: siuntos_registravimas.php');
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
            }
            else
            {
                $("#address_method").attr("hidden", false);
                $("#pick_up_method").attr("hidden", true);
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
                <h1>Naujos siuntos registracija</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-auto">
                <form method="post">
                    <div class="mb-3">
                        <label for="sender_name" class="form-label">Jūsų vardas</label>
                        <input type="name" class="form-control" id="sender_name" name="sender_name" maxlength="50" required>
                        <br>
                        <label for="sender_surname" class="form-label">Jūsų pavardė</label>
                        <input type="surname" class="form-control" id="sender_surname" name="sender_surname" maxlength="50" required>
                        <br>
                        <label for="sender_email" class="form-label">El. paštas</label>
                        <input type="email" class="form-control" id="sender_email" name="sender_email" maxlength="75" required>
                        <br>
                        <label for="sender_phone" class="form-label">Telefono numeris</label>
                        <input type="text" class="form-control" id="sender_phone" name="sender_phone" maxlength="12" required>
                        <br><br>

                        <label for="delivery_method" class="form-label">Pristatymo būdas</label>
                        <select class="form-select" id="delivery_method" name="delivery_method" required>
                            <option value="pick_up_point_method">Paštomatas</option>
                            <option value="address">Adresas</option>
                        </select>
                        <br>

                        <div id="address_method" hidden>
                            <label for="sender_address" class="form-label">Siuntėjo adresas</label>
                            <input type="text" class="form-control" id="sender_address" name="sender_address" maxlength="100" required>
                            <br>
                            <label for="sender_city" class="form-label">Siuntėjo miestas</label>
                            <input type="text" class="form-control" id="sender_city" name="sender_city" maxlength="50" required>
                            <br>
                            <label for="sender_post_code" class="form-label">Siuntėjo pašto kodas</label>
                            <input type="text" class="form-control" id="sender_post_code" name="sender_post_code" maxlength="10" required>
                            <br>
                            <br>
                            <label for="address" class="form-label">Gavėjo adresas</label>
                            <input type="text" class="form-control" id="recipient_address" name="recipient_address" maxlength="100" required>
                            <br>
                            <label for="city" class="form-label">Gavėjo miestas</label>
                            <input type="text" class="form-control" id="recipient_city" name="recipient_city" maxlength="50" required>
                            <br>
                            <label for="zip" class="form-label">Gavėjo pašto kodas</label>
                            <input type="text" class="form-control" id="recipient_post_code" name="recipient_post_code" maxlength="10" required>
                            <label for="phone" class="form-label">Gavėjo telefono numeris</label>
                            <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" maxlength="12" required>
                            <br>
                        </div>

                        <div id="pick_up_method">
                            <label for="pick_up_point" class="form-label>">Siuntos paiemimo paštomatas</label>
                            <select class="form-select" id="pick_up_point" name="pick_up_point" required>
                                <?php
                                    $sql = "SELECT * FROM post_machines";
                                    $result = mysqli_query($dbc, $sql);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $id = $row["id"];
                                        $name = $row["name"];
                                        $city = $row["city"];
                                        echo "<option value='$id'>$name, $city</option>";
                                    }
                                ?>
                            </select>
                            <br>
                            <label for="delivery_point" class="form-label">Siuntos pristatymo paštomatas</label>
                            <select class="form-select" id="delivery_point" name="delivery_point" required>
                                <?php
                                    $result = mysqli_query($dbc, $sql);
                                    while ($row = mysqli_fetch_assoc($result))
                                    {
                                        $id = $row["id"];
                                        $name = $row["name"];
                                        $city = $row["city"];
                                        echo "<option value='$id'>$name, $city</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <br>
                        <label for="package_weight" class="form-label">Siuntos svoris (kg)</label>
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
                        <br>
                        <label for="price" class="form-label">Siuntos kaina:</label>
                        <div class="input-group mb3">
                            <input type="text" class="form-control" id="price" name="price" readonly>
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