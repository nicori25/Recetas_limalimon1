<?php
session_start();
include("config.php");

$titulo =
$_POST['titulo'];

$descripcion =
$_POST['descripcion'];

$ingredientes =
$_POST['ingredientes'];

$pasos =
$_POST['pasos'];

$tipo =
$_POST['tipo'];

$usuario_id =
$_SESSION['user_id'];

if(
$tipo==="otro"
&&
!empty(
$_POST['tipo_personalizado']
)
){

$tipo =
$_POST['tipo_personalizado'];

}

$stmt =
$conn->prepare("
INSERT INTO recetas
(
titulo,
descripcion,
ingredientes,
pasos,
tipo,
usuario_id
)
VALUES
(
?,?,?,?,?,?
)
");

$stmt->bind_param(
"sssssi",
$titulo,
$descripcion,
$ingredientes,
$pasos,
$tipo,
$usuario_id
);

$stmt->execute();

header(
"Location: recetas.php"
);

exit();
?>