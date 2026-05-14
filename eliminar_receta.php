<?php
session_start();
include("config.php");

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM recetas
WHERE id=? AND usuario_id=?");

$stmt->bind_param("ii", $id, $user_id);

$stmt->execute();

header("Location: recetas.php");
?>