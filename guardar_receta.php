<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$titulo = trim($_POST['titulo'] ?? "");
$descripcion = trim($_POST['descripcion'] ?? "");
$ingredientes = trim($_POST['ingredientes'] ?? "");
$pasos = trim($_POST['pasos'] ?? "");
$tipo = trim($_POST['tipo'] ?? "");
$estacion = trim($_POST['estacion'] ?? "");
$usuario_id = $_SESSION['user_id'];

if(
    $titulo === "" ||
    $descripcion === "" ||
    $ingredientes === "" ||
    $pasos === "" ||
    $tipo === "" ||
    $estacion === ""
) {
    die("Faltan datos obligatorios para guardar la receta.");
}

if(
$tipo==="otro"
&&
!empty(trim($_POST['tipo_personalizado'] ?? ""))
){

$tipo=
trim($_POST['tipo_personalizado']);

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
estacion,
usuario_id
)
VALUES
(
?,?,?,?,?,?,?
)
");

if(!$stmt) {
    die("Error preparando la receta: " . $conn->error);
}

$stmt->bind_param(
"ssssssi",
$titulo,
$descripcion,
$ingredientes,
$pasos,
$tipo,
$estacion,
$usuario_id
);

if(!$stmt->execute()) {
    die("Error guardando la receta: " . $stmt->error);
}

header(
"Location: recetas.php"
);

exit();
?>
