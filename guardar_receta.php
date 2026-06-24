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

if($tipo === "otro") {
    $tipo = trim($_POST['tipo_personalizado'] ?? "");
}

if(
    $titulo === "" ||
    $descripcion === "" ||
    $ingredientes === "" ||
    $pasos === "" ||
    $tipo === "" ||
    $estacion === ""
){
    die("Faltan datos obligatorios para guardar la receta.");
}

/*
|--------------------------------------------------------------------------
| SUBIR IMAGEN
|--------------------------------------------------------------------------
*/

$imagen = null;

if(
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] === 0
){

    $ext = strtolower(
        pathinfo(
            $_FILES['imagen']['name'],
            PATHINFO_EXTENSION
        )
    );

    $permitidas = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif'
    ];

    if(in_array($ext, $permitidas)){

        $nombreImagen =
        time() . "_" .
        uniqid() . "." .
        $ext;

        $rutaDestino =
        "uploads/" .
        $nombreImagen;

        move_uploaded_file(
            $_FILES['imagen']['tmp_name'],
            $rutaDestino
        );

        $imagen = $nombreImagen;
    }
}

/*
|--------------------------------------------------------------------------
| GUARDAR RECETA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
INSERT INTO recetas
(
titulo,
descripcion,
ingredientes,
pasos,
tipo,
estacion,
imagen,
usuario_id
)
VALUES
(
?,?,?,?,?,?,?,?
)
");

if(!$stmt){
    die(
        "Error preparando la receta: "
        . $conn->error
    );
}

$stmt->bind_param(
"sssssssi",
$titulo,
$descripcion,
$ingredientes,
$pasos,
$tipo,
$estacion,
$imagen,
$usuario_id
);

if(!$stmt->execute()){
    die(
        "Error guardando la receta: "
        . $stmt->error
    );
}

header("Location: recetas.php");
exit();
?>