<?php
  $server = "localhost";
  $db = "siuntu_tarnyba";
  $user = "siuntu_tarnyba";
  $password = "siuntu_tarnyba";
  $lentele = "users";
  $dbc=mysqli_connect($server,$user,$password,$db);

  session_start();

  if (!$dbc)
  { 
    die ("Negaliu prisijungti prie MySQL:" .mysqli_error($dbc)); 
  }

  if ($_POST != null)
  {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
  $email = $_POST['email'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO $lentele (username, password, name, surname, phone, email)
      VALUES ('$username', '$hashed_password', '$name', '$surname', '$phone', '$email')";

    if (!mysqli_query($dbc, $sql)) 
    {
      die ("Klaida įrašant:" .mysqli_error($dbc));
    }
    else
    {
        $_SESSION["registration_success"] = "Registracija sėkminga. Prašome prisijungti";
        header('Location: login.php');
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
        echo getHeader("Registracija");
      ?>
  </head>
  <body>
    <div class="text-center">
      <h2>Siuntų pristatymo tarnyba</h2>
      <p><i>Registracija</i></p>
    </div>

    <form class="form-floating" method="post">
      <div class="form-group container">
        <!-- Slapyvardis -->
        <div class="form-floating mb-3">
          <input type="username" class="form-control" name="username" placeholder="username" maxlength="20" required>
          <label for="username">Slapyvardis</label>
        </div>
        
        <!-- Slaptažodis -->
        <div class="form-floating mb-3">
          <input type="password" class="form-control" name="password" placeholder="Slaptažodis" required>
          <label for="password">Slaptažodis</label>
        </div>

        <!-- Vardas -->
        <div class="form-floating mb-3">
          <input type="text" class="form-control" name="name" placeholder="Vardas" maxlength="50" required>
          <label for="name">Vardas</label>
        </div>

        <!-- Pavardė -->
        <div class="form-floating mb-3">
          <input type="text" class="form-control" name="surname" placeholder="Pavardė" maxlength="50" required>
          <label for="surname">Pavardė</label>
        </div>

        <!-- Telefono numeris -->
        <div class="form-floating mb-3">
          <input type="tel" class="form-control" name="phone" placeholder="Telefono numeris" maxlength="12" required>
          <label for="phone">Telefono numeris</label>
        </div>

      <!-- Elektroninis paštas -->
      <div class="form-floating mb-3">
          <input type="email" class="form-control" name="email" placeholder="El. paštas" maxlength="75" required>
          <label for="email">El. paštas</label>
      </div>

        <!-- Registracijos mygtukas -->
        <input type="submit" name='ok' formv class="btn btn-primary btn-block mb-2" value="Registruotis">
      </div>
    </form>
  </body>
</html>