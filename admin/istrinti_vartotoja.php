<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);

session_start();

$id=$_GET['id'];
$sql="DELETE FROM users WHERE id=$id";
if (!mysqli_query($dbc, $sql)) {
    echo " DB klaida šalinant vartotoją: " . $sql . "<br>" . mysqli_error($dbc);
    exit;
} else {
    header("location:sandeliai.php"); exit;
}
?>
