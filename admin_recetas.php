<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['rol']!="admin"){
    die("Acceso denegado.");
}

$stmt = $conn->prepare("
SELECT
recetas.*,
usuarios.nombre
FROM recetas
JOIN usuarios
ON recetas.usuario_id = usuarios.id
ORDER BY recetas.id DESC
");

$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Administrar recetas</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<h2>🍽️ Administrar recetas</h2>

<table class="tabla-admin">

<thead>
<tr>

<th>Foto</th>
<th>Título</th>
<th>Autor</th>
<th>Tipo</th>
<th>Temporada</th>
<th>Acciones</th>

</tr>
</thead>
<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td data-label="Foto">

<?php if(!empty($row['imagen'])){ ?>

<img
src="uploads/<?php echo htmlspecialchars($row['imagen']); ?>"
class="mini-receta"
>

<?php }else{ ?>

Sin imagen

<?php } ?>

</td>

<td data-label="Título">

<?php echo htmlspecialchars($row['titulo']); ?>

</td>

<td data-label="Autor">

<?php echo htmlspecialchars($row['nombre']); ?>

</td>

<td data-label="Tipo">

<?php echo htmlspecialchars($row['tipo']); ?>

</td>

<td data-label="Temporada">

<?php echo htmlspecialchars($row['estacion']); ?>

</td>

<td data-label="Acciones">

<a
href="editar_receta.php?id=<?php echo $row['id']; ?>"
class="btn-editar"
>

✏️ Editar

</a>

<form action="eliminar_receta.php" method="POST">

    <input
        type="hidden"
        name="id"
        value="<?php echo $row['id']; ?>"
    >

    <button
        class="btn-eliminar"
        onclick="return confirm('¿Eliminar esta receta?')"
    >
        🗑️ Eliminar
    </button>

</form>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>