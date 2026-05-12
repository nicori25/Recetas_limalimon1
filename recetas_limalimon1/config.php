<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "limalimon1";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>