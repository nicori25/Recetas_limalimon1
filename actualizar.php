<?php
session_start();
include("config.php");

$id = $_SESSION['user_id'];

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = $_POST['password'];

$preferencia =
$_POST['preferencia_tipo'];

if(
$preferencia=="otro"
&& !empty($_POST['preferencia_personalizada'])
){

$preferencia =
$_POST['preferencia_personalizada'];

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