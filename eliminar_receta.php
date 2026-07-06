<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = (int)$_POST['id'];

/*==========================
OBTENER LA RECETA
==========================*/

if($_SESSION['rol']=="admin"){

    $stmt = $conn->prepare("
    SELECT imagen
    FROM recetas
    WHERE id=?
    ");

    $stmt->bind_param("i",$id);

}else{

    $stmt = $conn->prepare("
    SELECT imagen
    FROM recetas
    WHERE id=?
    AND usuario_id=?
    ");

    $stmt->bind_param(
        "ii",
        $id,
        $_SESSION['user_id']
    );

}

$stmt->execute();

$receta = $stmt->get_result()->fetch_assoc();

if(!$receta){

    die("No tienes permisos para eliminar esta receta.");

}

/*==========================
ELIMINAR IMAGEN
==========================*/

if(
    !empty($receta["imagen"]) &&
    file_exists("uploads/".$receta["imagen"])
){

    unlink("uploads/".$receta["imagen"]);

}

/*==========================
ELIMINAR FAVORITOS
==========================*/

$stmt = $conn->prepare("
DELETE
FROM favoritos
WHERE receta_id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

/*==========================
ELIMINAR VALORACIONES
==========================*/

$stmt = $conn->prepare("
DELETE
FROM valoraciones
WHERE receta_id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

/*==========================
ELIMINAR OPINIONES
==========================*/

$stmt = $conn->prepare("
DELETE
FROM opiniones
WHERE receta_id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

/*==========================
ELIMINAR RECETA
==========================*/

if($_SESSION['rol']=="admin"){

    $stmt = $conn->prepare("
    DELETE
    FROM recetas
    WHERE id=?
    ");

    $stmt->bind_param("i",$id);

}else{

    $stmt = $conn->prepare("
    DELETE
    FROM recetas
    WHERE id=?
    AND usuario_id=?
    ");

    $stmt->bind_param(
        "ii",
        $id,
        $_SESSION['user_id']
    );

}

$stmt->execute();

/*==========================
REDIRECCIONAR
==========================*/

if($_SESSION['rol']=="admin"){

    header("Location: admin_recetas.php");

}else{

    header("Location: mis_recetas.php");

}

exit();
?>