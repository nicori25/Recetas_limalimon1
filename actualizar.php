<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$nombre = trim($_POST['nombre'] ?? "");
$email = trim($_POST['email'] ?? "");
$password = $_POST['password'] ?? "";

$preferencia =
trim($_POST['preferencia_tipo'] ?? "");

if(
$preferencia=="otro"
){

$preferencia =
trim($_POST['preferencia_personalizada'] ?? "");

}

if(!empty($password)){

$pass =
password_hash(
$password,
PASSWORD_DEFAULT
);

$stmt =
$conn->prepare("
UPDATE usuarios
SET
nombre=?,
email=?,
password=?,
preferencia_tipo=?
WHERE id=?
");

$stmt->bind_param(
"ssssi",
$nombre,
$email,
$pass,
$preferencia,
$id
);

}else{

$stmt =
$conn->prepare("
UPDATE usuarios
SET
nombre=?,
email=?,
preferencia_tipo=?
WHERE id=?
");

$stmt->bind_param(
"sssi",
$nombre,
$email,
$preferencia,
$id
);

}

$stmt->execute();

$_SESSION['user_nombre']=$nombre;

$_SESSION['preferencia_tipo']=$preferencia;

header("Location: perfil.php");

exit();
?>
