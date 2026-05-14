<?php
session_start();
include("config.php");

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$usuario_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO recetas(titulo,descripcion,usuario_id)
VALUES(?,?,?)");

$stmt->bind_param("ssi", $titulo, $descripcion, $usuario_id);

$stmt->execute();

header("Location: recetas.php");
?>