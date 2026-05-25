<?php
$servername = "localhost";
$username = "root";
$password = "karthikmj6252";
$database = "project";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
