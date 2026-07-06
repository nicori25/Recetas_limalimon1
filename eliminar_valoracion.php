<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != "admin") {
    header("Location: login.php");
    exit();
}

$id = (int)$_POST['id'];

/* Obtener la valoración */

$stmt = $conn->prepare("
SELECT
usuario_id,
receta_id
FROM valoraciones
WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$valoracion = $stmt->get_result()->fetch_assoc();

if(!$valoracion){
    die("La valoración no existe.");
}

/* Eliminar el comentario relacionado */

$stmt = $conn->prepare("
DELETE
FROM opiniones
WHERE usuario_id=?
AND receta_id=?
");

$stmt->bind_param(
"ii",
$valoracion['usuario_id'],
$valoracion['receta_id']
);

$stmt->execute();

/* Eliminar la valoración */

$stmt = $conn->prepare("
DELETE
FROM valoraciones
WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_valoraciones.php");
exit();
?>