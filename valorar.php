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

$puntuacion =
(int)$_POST['puntuacion'];

$check =
$conn->prepare("
SELECT *
FROM valoraciones
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

$update =
$conn->prepare("
UPDATE valoraciones
SET puntuacion=?
WHERE usuario_id=?
AND receta_id=?
");

$update->bind_param(
"iii",
$puntuacion,
$usuario_id,
$receta_id
);

$update->execute();

}else{

$insert =
$conn->prepare("
INSERT INTO valoraciones
(usuario_id,receta_id,puntuacion)
VALUES
(?,?,?)
");

$insert->bind_param(
"iii",
$usuario_id,
$receta_id,
$puntuacion
);

$insert->execute();

}

header("Location: index.php");
exit();
?>