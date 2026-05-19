<?php
session_start();
include("config.php");

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

// 🗑️ Primero borrar opiniones
$stmt1 = $conn->prepare("
DELETE FROM opiniones
WHERE receta_id=?
");

$stmt1->bind_param("i", $id);
$stmt1->execute();

// 🗑️ Después borrar receta
$stmt2 = $conn->prepare("
DELETE FROM recetas
WHERE id=? AND usuario_id=?
");

$stmt2->bind_param("ii", $id, $user_id);
$stmt2->execute();

header("Location: recetas.php");
?>