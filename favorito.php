<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$usuario_id =
$_SESSION['user_id'];

$receta_id =
(int)$_POST['receta_id'];

$check =
$conn->prepare("
SELECT *
FROM favoritos
WHERE usuario_id=?
AND receta_id=?
");

$check->bind_param(
"ii",
$usuario_id,
$receta_id
);

$check->execute();

$res =
$check->get_result();

if($res->num_rows > 0){

    // ❌ QUITAR FAVORITO

    $delete =
    $conn->prepare("
    DELETE FROM favoritos
    WHERE usuario_id=?
    AND receta_id=?
    ");

    $delete->bind_param(
    "ii",
    $usuario_id,
    $receta_id
    );

    $delete->execute();

}else{

    // ❤️ AGREGAR FAVORITO

    $insert =
    $conn->prepare("
    INSERT INTO favoritos
    (usuario_id,receta_id)
    VALUES
    (?,?)
    ");

    $insert->bind_param(
    "ii",
    $usuario_id,
    $receta_id
    );

    $insert->execute();

}

header("Location: mis_favoritos.php");
exit();
?>