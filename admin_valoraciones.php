<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != "admin") {
    header("Location: login.php");
    exit();
}

$sql = "
SELECT
    valoraciones.id,
    valoraciones.puntuacion,
    opiniones.comentario,
    usuarios.nombre,
    recetas.titulo
FROM valoraciones
INNER JOIN usuarios
ON valoraciones.usuario_id = usuarios.id
INNER JOIN recetas
ON valoraciones.receta_id = recetas.id
LEFT JOIN opiniones
ON valoraciones.usuario_id = opiniones.usuario_id
AND valoraciones.receta_id = opiniones.receta_id
ORDER BY valoraciones.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Administrar valoraciones</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<h2>⭐ Administrar Valoraciones</h2>

<div class="recetas-grid">

<?php while($row = $result->fetch_assoc()){ ?>

<div class="card">

<h3>
<?php echo htmlspecialchars($row["titulo"]); ?>
</h3>

<p>

<strong>Usuario:</strong>

<?php echo htmlspecialchars($row["nombre"]); ?>

</p>

<p>

<strong>Puntuación:</strong>

<?php echo $row["puntuacion"]; ?>/5 ⭐

</p>

<p>

<strong>Comentario:</strong><br>

<?php
echo $row["comentario"]
? nl2br(htmlspecialchars($row["comentario"]))
: "<i>Sin comentario</i>";
?>

</p>

<form action="eliminar_valoracion.php" method="POST">

<input
type="hidden"
name="id"
value="<?php echo $row["id"]; ?>"
>

<button
type="submit"
onclick="return confirm('¿Seguro que deseas eliminar esta valoración y su comentario?');"
>
🗑 Eliminar
</button>

</form>

</div>

<?php } ?>

</div>

</div>

</body>

</html>