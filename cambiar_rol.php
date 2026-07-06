<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location:login.php");
    exit();
}

if($_SESSION['rol']!="admin"){
    die("Acceso denegado.");
}

$id = (int)$_GET['id'];
$rol = $_GET['rol'];

/* Solo permitimos dos roles */
if($rol!="admin" && $rol!="usuario"){
    die("Rol inválido.");
}

/* Evita cambiarte el rol a vos mismo */
if($id == $_SESSION['user_id']){
    die("No puedes cambiar tu propio rol.");
}

$stmt = $conn->prepare("
UPDATE usuarios
SET rol=?
WHERE id=?
");

$stmt->bind_param(
"si",
$rol,
$id
);

$stmt->execute();

header("Location:admin_usuarios.php");
exit();
?>