<?php
$server = "localhost";
$db = "siuntu_tarnyba";
$user = "root";
$password = "";
// prisijungimas prie DB
$dbc=mysqli_connect($server,$user,$password,$db);

session_start();

$id=$_GET['id'];
$sql="DELETE FROM warehouses WHERE id=$id";
if (!mysqli_query($dbc, $sql)) {
    echo " DB klaida šalinant sandėlį: " . $sql . "<br>" . mysqli_error($dbc);
    exit;
} else {
    header("location:sandeliai.php"); exit;
}
?>
