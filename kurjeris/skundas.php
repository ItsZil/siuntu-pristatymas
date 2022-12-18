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

$topic=$topic_err="";
$complaint=$complaint_err="";
$courier_id=$_SESSION["id"];

$sql="SELECT id FROM packages WHERE courier_id=$courier_id";
$result=mysqli_query($dbc, $sql);
if(mysqli_num_rows($result)<1){
    header("location:kurjeris.php");
}

if($_SERVER["REQUEST_METHOD"]=="POST") {
    $package_id=$_POST['selection'];
    if (empty($_POST['topic'])) {
        $topic_err = "Įveskite temą";
    } else {
        $topic = trim($_POST['topic']);
    }
    if (empty($_POST['complaint'])) {
        $complaint_err = "Įveskite nusiskundimą";
    } else {
        $complaint = trim($_POST['complaint']);
    }
    if (empty($topic_err) && empty($complaint_err)) {
        $sql = "INSERT INTO complaints (topic, complaint, package_id, courier_id) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($dbc, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $param_topic, $param_complaint, $param_package, $param_courier);
            $param_topic = $topic;
            $param_complaint = $complaint;
            $param_package = $package_id;
            $param_courier = $courier_id;
            if (mysqli_stmt_execute($stmt)) {
                $message = "Skundas užregistruotas";
            } else {
                $message = "Įvyko klaida, bandykite dar kartą";
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
        echo getHeader("Skundo registravimas");
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
                <h1>Registruoti skundą</h1>
                <hr>
            </div>
        </div>
        <div class="container">
            <div class="col-12">
                <form action="skundas.php" method="post">
                    <div class="mb-3">
                        <label for="topic" class="form-label">Tema</label>
                        <input type="text" class="form-control" id="topic" name="topic" maxlength="50" required>
                        <br>
                        <label for="complaint" class="form-label">Skundas:</label>
                        <textarea class="form-control" id="complaint" name="complaint" rows="3" required></textarea>
                        <label for="selection">Siunta:</label>
                        <?php
                        echo "<select name='selection'>";
                        while ($row=mysqli_fetch_assoc($result)){
                            $package=$row['id'];
                            echo "<option value='$package'>$package</option>";
                        }
                        echo "</select>";?>
                    </div>
                    <button type="submit" class="btn btn-primary float-end">Registruoti skundą</button>
                </form>
            </div>
            <?php if(!empty($message)) echo "<p>$message</p>"?>
        </div>
    </div>
    <?php
        include_once "../includes/footer.html";
    ?>
</body>
</html>