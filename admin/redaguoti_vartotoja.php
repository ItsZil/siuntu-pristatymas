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

$id=$_GET['id'];
$sql="SELECT * FROM users WHERE id=$id";
$result=mysqli_query($dbc, $sql);
$row=mysqli_fetch_assoc($result);
$username=$row['username'];
$name=$row['name'];
$surname=$row['surname'];
$phone=$row['phone'];
$email=$row['email'];
$access_level=$row['access_level'];
$username_err=$name_err=$phone_err=$email_err=$surname_err=$accesslevel_err="";
if($_SERVER["REQUEST_METHOD"]=="POST") {
    $access_level = trim($_POST['access_level']);
    if (empty($_POST["username"])) {
        $username_err = "Įveskite slapyvardį";
    } else {
        $username = trim($_POST['username']);
    }
    if (empty($_POST["name"])) {
        $name_err = "Įveskite vardą";
    } else {
        $name = trim($_POST['name']);
    }
    if (empty($_POST["surname"])) {
        $surname_err = "Įveskite pavardę";
    } else {
        $surname = trim($_POST['surname']);
    }
    if (empty($_POST["phone"])) {
        $phone_err = "Įveskite telefono numerį";
    } else {
        $phone = trim($_POST['phone']);
    }
    if (empty($_POST["email"])) {
        $email_err = "Įveskite El. paštą";
    } else {
        $email = trim($_POST['email']);
    }
   
    if (empty($username_err) && empty($name_err) && empty($phone_err) && empty($email_err) && empty($surname_err) && empty($accesslevel_err)) {
        echo "test1";
        $sql = "UPDATE users SET username=?, name=?, phone=?, email=?, surname=?, title=?, access_level=? WHERE id=$id";
        if ($stmt = mysqli_prepare($dbc, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $param_username, $param_name, $param_phone, $param_email, $param_surname, $param_title, $param_accesslevel);
            $param_username = $username;
            $param_name = $name;
            $param_phone = $phone;
            $param_email = $email;
            $param_surname = $surname;
            if($access_level == 1){
                $param_title = "Klientas";
            } elseif($access_level == 2){
                $param_title = "Kurjeris";
            } else {
                $param_title = "Administratorius";
            }
            $param_accesslevel= $access_level;
            if (mysqli_stmt_execute($stmt)) {
                header("location:vartotojai.php");
            } else {
                echo "Įvyko klaida, bandykite dar kartą";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php
        include_once "../includes/header.php";
        echo getHeader("Vartotojai");
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
        <h3>Vartotojo redagavimas</h3>
        <?php

        $result
        ?>
        <form method="post">
            <!-- Slapyvardis -->
            <div class="form-group col-sm-12">
                <label for="username">Slapyvardis</label>
                <input type="text" class="form-control" name="username" value="<?php echo $username ?>" maxlength="50" required>
            </div>

            <!-- Vardas -->
            <div class="form-group col-sm-12">
                <label for="name">Vardas</label>
                <input type="text" class="form-control" name="name" value="<?php echo $name ?>" required>
            </div>

            <!-- Pavardė -->
            <div class="form-group col-sm-12">
                <label for="surname">Pavardė</label>
                <input type="text" class="form-control" name="surname" value="<?php echo $surname ?>" required>
            </div>

            <!-- Telefono numeris -->
            <div class="form-group col-sm-12">
                <label for="phone">Telefono numeris</label>
                <input type="tel" class="form-control" name="phone" value="<?php echo $phone ?>" maxlength="12" pattern="+[0-9]{11}" required>
            </div>

            <!-- Elektroninis paštas -->
            <div class="form-group col-sm-12">
                <label for="email">El. paštas</label>
                <input type="email" class="form-control" name="email" value="<?php echo $email ?>" maxlength="75" required>

            </div>

            <!-- Lygis -->
            <div class="form-group col-sm-12">
                <label for="access_level">Lygis</label>
                <select class="form-control" name="access_level">
                    <option value="1" <?php if($access_level == 1) echo 'selected'?> >Klientas - 1</option> 
                    <option value="2" <?php if($access_level == 2) echo 'selected'?> >Kurjeris - 2</option>    
                    <option value="3" <?php if($access_level == 3) echo 'selected'?> >Administratorius - 3</option>       
                </select>
            </div>

            <!-- Registracijos mygtukas -->
            <input type="submit" name='ok' class="btn btn-primary btn-block mb-2" value="Įrašyti">
    </div>
    </form>
    </div>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>