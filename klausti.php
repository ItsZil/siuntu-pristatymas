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

$name=$name_err="";
$email=$email_err="";
$phone=$phone_err="";
$topic=$topic_err="";
$request=$request_err="";

$message="";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(empty($_POST['name'])){
        $name_err="Įveskite vardą";
    } else{
        $name=trim($_POST['name']);
    }
    if(empty($_POST['email'])){
        $email_err="Įveskite el. paštą";
    } else{
        $email=trim($_POST['email']);
    }
    if(empty($_POST['phone'])){
        $phone_err="Įveskite telefono numerį";
    } else{
        $phone=trim($_POST['phone']);
    }
    if(empty($_POST['topic'])){
        $topic_err="Įveskite temą";
    } else{
        $topic=trim($_POST['topic']);
    }
    if(empty($_POST['request'])){
        $request_err="Įveskite savo klausimą";
    } else{
        $request=trim($_POST['request']);
    }
    if(empty($name_err) && empty($email_err) && empty($phone_err) && empty($topic_err) && empty($request_err)){
        $sql="INSERT INTO requests (name, email, phone, topic, request) VALUES (?, ?, ?, ?, ?)";
        if($stmt=mysqli_prepare($dbc, $sql)){
            mysqli_stmt_bind_param($stmt, "sssss", $param_name, $param_email, $param_phone, $param_topic, $param_request);
            $param_name=$name;
            $param_email=$email;
            $param_phone=$phone;
            $param_topic=$topic;
            $param_request=$request;
            if(mysqli_stmt_execute($stmt)){
                $message="Klausimas užregistruotas";
            }
            else{
                $message="Įvyko klaida, bandykite dar kartą";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "includes/header.php";
        echo getHeader("Klausti");
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
                  <a class='nav-link' href="index.php">Pagrindinis</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="sekimas.php">Sekimas</a>
                </li>

                <li class='nav-item'>
                  <a class="nav-link active" aria-current="page" href="klausti.php">Klausti</a>
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
                <h1>Turite klausimų?</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-12">
                <form method="post">
                    <div class="mb-3">
                        <label for="vardas" class="form-label">Jūsų vardas</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="50" required>
                        <br>
                        <label for="email" class="form-label">El. paštas</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="75" required>
                        <br>
                        <label for="phone" class="form-label">Telefono numeris</label>
                        <input type="text" class="form-control" id="phone" name="phone" maxlength="12" required>
                        <br>
                        <label for="topic" class="form-label">Tema</label>
                        <input type="text" class="form-control" id="topic" name="topic" maxlength="50" required>
                        <br>
                        <label for="request" class="form-label">Įveskite savo klausimą žemiau:</label>
                        <textarea class="form-control" id="request" name="request" rows="3" required></textarea>
                    </div>
                    <input type='submit' name='send_question' class='btn btn-primary float-end' value="Siųsti užklausą">
                </form>
                <?php if(!empty($message)) echo "<p>$message</p>"?>
            </div>
        </div>
    </div>

    <?php
        include_once "includes/footer.html";
    ?>
</body>
</html>