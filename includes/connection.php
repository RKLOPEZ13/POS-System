<?php
$host = "localhost";     // usually localhost
$user = "root";          // default for XAMPP
$pass = "";              // leave empty unless you set one
$dbname = "pos_system";  // your database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
