<?php
  $server = "localhost";
  $db = "siuntu_tarnyba";
  $user = "siuntu_tarnyba";
  $password = "siuntu_tarnyba";
  $lentele = "users";
  $dbc=mysqli_connect($server,$user,$password,$db);

  session_start();
  if (session_status() != PHP_SESSION_NONE && isset($_SESSION["access_level"]))
  {
    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();
  }

  if (!$dbc)
  { 
    die ("Nepavyko prisijungti prie duomenų bazės:" .mysqli_error($dbc));
  }

  if ($_POST != null)
  {
    $username = $_POST['username'];
    $password =$_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "SELECT * FROM $lentele WHERE username = '$username'";
    $result = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row == null) 
    {
      $_SESSION["username"] = $username;
      $_SESSION["incorrect_login"] = "Neteisingas prisijungimo vardas arba slaptažodis";
      header('Location: login.php');
    }
    else
    {
      if (password_verify($password, $row['password'])) 
      {
        $id = $row['id'];
        $access_level = $row['access_level'];

        $_SESSION["username"] = $username;
        $_SESSION["id"] = $id;
        $_SESSION["access_level"] = $access_level;
        header('Location: pagrindinis.php');
      }
      else 
      {
        $_SESSION["username"] = $username;
        $_SESSION["incorrect_login"] = "Neteisingas prisijungimo vardas arba slaptažodis";
        header('Location: login.php');
      }
    }
    mysqli_close($dbc);
    exit();
  }
?>

<!doctype html>
<html lang="en">
  <head>
      <?php
        require_once "includes/header.php";
        echo getHeader("Prisijungimas");
      ?>
  </head>
  <body>
    <div class="text-center">
      <h2>Siuntų pristatymo tarnyba</h2>
      <p><i>Prašome prisijungti prie sistemos</i></p>
    </div>

    <form class="form-control" method="post">
      <div class="form-group container">
        <!-- Slapyvardis -->
        <div class="form-floating mb-3">
          <?php
            if (isset($_SESSION["username"]))
            {
              $username = $_SESSION["username"];
              echo "<input type='username' class='form-control' name='username' placeholder='Slapyvardis' maxlength='25' value='$username' required>";
              unset($_SESSION["username"]);
            }
            else
            {
              echo "<input type='username' class='form-control' name='username' placeholder='Slapyvardis' maxlength='25' required>";
            }
          ?>
          <label for="username">Slapyvardis</label>
        </div>
        
        <!-- Slaptažodis -->
        <div class="form-floating mb-3">
          <input type="password" class="form-control" name="password" placeholder="Slaptažodis" required>
          <label for="password">Slaptažodis</label>
        </div>

        <!-- Prisijungimo ar registracijos pranešimai -->
        <div class="form-floating mb-3">
          <?php 
            if (isset($_SESSION["incorrect_login"])) 
            {
              $incorrect_login = $_SESSION["incorrect_login"];
              echo "<p class='text-danger'>$incorrect_login</p>";
              unset($_SESSION['incorrect_login']);
            }
            else if (isset($_SESSION["registration_success"])) 
            {
              $registration_success = $_SESSION['registration_success'];
              echo "<p class='text-success'>$registration_success</p>";
              unset($_SESSION['registration_success']);
            }
          ?>
        </div>
        <!-- Login mygtukas -->
        <input type="submit" name='ok' formv class="btn btn-primary btn-block mb-2" value="Prisijungti">

        <!-- Registracijos mygtukas -->
        <div>
          <p>Neturite paskyros? <a href="register.php">Registruotis</a></p>
      </div>
    </form>
</body>
</html>