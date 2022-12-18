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

if (isset($_POST["confirm_package"]))
{
    $package_id = $_POST["package_id"];

    $_SESSION["package_id"] = $package_id;

    unset($_SESSION["confirm_package"]);
}

if (isset($_POST["confirm_signature"]))
{
    // Save the signature to the database
    $signature_data = $_POST["signature_data"];
    $package_id = $_SESSION["package_id"];

    $sql = "UPDATE packages SET status = 'Siunta pristatyta', planned_delivery_date = NOW(), delivery_date = NOW(), signature = '$signature_data' WHERE id = $package_id";
    $result = mysqli_query($dbc, $sql);

    if ($result)
    {
        $_SESSION["notification_message"] = "Siuntos $package_id pristatymas patvirtintas sėkmingai.";
        $_SESSION["notification_status"] = 1;
    }
    else
    {
        $_SESSION["notification_message"] = "Siuntos numeriu $package_id pristatymo patvirtinimas nepavyko. Bandykite vėl.";
        $_SESSION["notification_status"] = 0;
    }
    unset($_SESSION["package_id"]);
    unset($_SESSION["confirm_signature"]);
    unset($_SESSION["signature_data"]);
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
        echo getHeader("Pristatymo patvirtinimas");
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
                        <a class='nav-link active' aria-current='page' href='kurjeris.php'>Kurjeris</a>
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
        <div class="container">
            <div class="col-12">
                <h1>Siuntos pristatymo patvirtinimas</h1>
                <hr>
            </div>
        </div>

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

            if (isset($_SESSION["package_id"]))
            {
                $package_id = $_SESSION["package_id"];
                echo "<div class='container text-center'>
                <form method='post'>
                     <input type='hidden' id='signature_data' name='signature_data'>
                    <canvas id='signature-canvas' width='400' height='200' style='border: 1px solid black'></canvas>
                    
                    <script>
                        // get reference to the canvas element
                        var canvas = document.getElementById('signature-canvas');
                      
                        // get reference to the canvas context, which we will use to draw on the canvas
                        var ctx = canvas.getContext('2d');
                        
                        // set stroke style and line width for the signature
                        ctx.strokeStyle = 'black';
                        ctx.lineWidth = 2;
                        
                        // create a flag to track whether the user is currently drawing or not
                        var isDrawing = false;
                        
                        // handle mouse down event
                        canvas.addEventListener('mousedown', function(event) {
                        isDrawing = true;
                        ctx.moveTo(event.offsetX, event.offsetY);
                        });
                        
                        var signatureData = canvas.toDataURL();
                        // handle mouse move event
                        canvas.addEventListener('mousemove', function(event) {
                        if (isDrawing) {
                          ctx.lineTo(event.offsetX, event.offsetY);
                          ctx.stroke();                        
                          signatureData = canvas.toDataURL();
                        }
                        });
                        
                        // handle mouse up event
                        canvas.addEventListener('mouseup', function(event) {
                        isDrawing = false;
                        document.getElementById('signature_data').value = signatureData;
                        });
                    </script>
                  <br><br>
                  <p>Siuntos numeris: $package_id</p>
                 <input type='submit' name='confirm_signature' class='btn btn-success w-25' value='Patvirtinti'>
                 <br><br>
                </form><hr>
               </div>";
        }
        $user_id = $_SESSION["id"];
        $query = "SELECT * FROM packages WHERE courier_id = '$user_id' AND status != 'Siunta pristatyta' ORDER BY id DESC";
        $result = mysqli_query($dbc, $query);

        echo "<div class='container'>
                    <div class='col-12'>
                        <form method='post'><table class='table table-striped'>
                            <thead>
                                <tr>    
                                    <th scope='col'>Siuntos numeris</th>
                                    <th scope='col'>Dydis</th>
                                    <th scope='col'>Pristatymo adresas</th> 
                                    <th scope='col'>Numatoma pristatymo data</th>
                                    <th scope='col'>Patvirtinimas</th>
                                </tr>
                            </thead>
                            <tbody>";
                            while ($row = mysqli_fetch_array($result))
                            {
                                $package_id = $row["id"];
                                $size = $row["size"];
                                $delivery_address = $row["to_address"];
                                $delivery_date = $row["planned_delivery_date"];
                                echo "<tr>
                                        <td>$package_id</td>
                                        <td>$size</td>
                                        <td>$delivery_address</td>
                                        <td>$delivery_date</td>
                                        <td>
                                            <input type='hidden' name='package_id' value='$package_id'>
                                            <input type='submit' name='confirm_package' class='btn btn-success' value='Pristatyti'>
                                        </td>
                                    </tr>";
                            }
                            echo "</tbody>
                        </table></form>
                    </div>
                </div>
            </div>";
        ?>
    </div>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>