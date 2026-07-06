<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['rol'] != "admin"){
    die("Acceso denegado.");
}

$id = (int)$_GET['id'];

/* No permitir eliminar al administrador logueado */
if($id == $_SESSION['user_id']){
    die("No puedes eliminar tu propia cuenta.");
}

/* Eliminar favoritos */
$stmt = $conn->prepare("
DELETE FROM favoritos
WHERE usuario_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* Eliminar opiniones */
$stmt = $conn->prepare("
DELETE FROM opiniones
WHERE usuario_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* Eliminar valoraciones */
$stmt = $conn->prepare("
DELETE FROM valoraciones
WHERE usuario_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* Eliminar recetas */
$stmt = $conn->prepare("
DELETE FROM recetas
WHERE usuario_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* Eliminar usuario */
$stmt = $conn->prepare("
DELETE FROM usuarios
WHERE id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: admin_usuarios.php");
exit();
?>