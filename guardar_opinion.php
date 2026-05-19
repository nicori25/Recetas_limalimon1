<?php
session_start();
include("config.php");

$comentario = $_POST['comentario'];
$receta_id = $_POST['receta_id'];
$usuario_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
INSERT INTO opiniones(comentario,usuario_id,receta_id)
VALUES(?,?,?)
");

$stmt->bind_param("sii", $comentario, $usuario_id, $receta_id);

$stmt->execute();

header("Location: index.php");
?>