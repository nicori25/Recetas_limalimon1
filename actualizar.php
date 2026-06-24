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

if(
!preg_match(
'/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{6,18}$/',
$password
)
){

die("
<h3>La contraseña no cumple los requisitos</h3>

Debe tener:

<ul>
<li>Entre 6 y 18 caracteres</li>
<li>Al menos una letra mayúscula</li>
<li>Al menos un número</li>
<li>Al menos un carácter especial</li>
</ul>

<a href='perfil.php'>Volver</a>
");

}

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
