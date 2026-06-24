<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = (int)$_POST['id'];

$imagenNueva = null;

if(
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] == 0
){

    $ext = strtolower(
        pathinfo(
            $_FILES['imagen']['name'],
            PATHINFO_EXTENSION
        )
    );

    $permitidas = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp"
    ];

    if(in_array($ext, $permitidas)){

        $nombreImagen =
        time() . "_" .
        uniqid() . "." .
        $ext;

        move_uploaded_file(
            $_FILES['imagen']['tmp_name'],
            "uploads/" . $nombreImagen
        );

        $imagenNueva = $nombreImagen;
    }
}

if($imagenNueva != null){

    $stmt = $conn->prepare("
    UPDATE recetas
    SET
    titulo=?,
    descripcion=?,
    ingredientes=?,
    pasos=?,
    tipo=?,
    imagen=?
    WHERE id=?
    ");

    $stmt->bind_param(
    "ssssssi",
    $_POST['titulo'],
    $_POST['descripcion'],
    $_POST['ingredientes'],
    $_POST['pasos'],
    $_POST['tipo'],
    $imagenNueva,
    $id
    );

}else{

    $stmt = $conn->prepare("
    UPDATE recetas
    SET
    titulo=?,
    descripcion=?,
    ingredientes=?,
    pasos=?,
    tipo=?
    WHERE id=?
    ");

    $stmt->bind_param(
    "sssssi",
    $_POST['titulo'],
    $_POST['descripcion'],
    $_POST['ingredientes'],
    $_POST['pasos'],
    $_POST['tipo'],
    $id
    );
}

$stmt->execute();

header("Location: mis_recetas.php");
exit();
?>