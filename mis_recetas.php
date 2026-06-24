<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT *
FROM recetas
WHERE usuario_id=?
ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Mis recetas</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<h2>Mis recetas</h2>

<div class="recetas-grid">

<?php while($row=$result->fetch_assoc()){ ?>

<div class="card">

<?php if(!empty($row['imagen'])){ ?>

<img
src="uploads/<?php echo htmlspecialchars($row['imagen']); ?>"
class="imagen-receta"
alt="Receta"
>

<?php } ?>

<h3>
<?php echo htmlspecialchars($row['titulo']); ?>
</h3>

<p>
<?php echo nl2br(htmlspecialchars($row['descripcion'])); ?>
</p>

<p>
<strong>Ingredientes:</strong>
<br>
<?php echo nl2br(htmlspecialchars($row['ingredientes'])); ?>
</p>

<p>
<strong>Preparación:</strong>
<br>
<?php echo nl2br(htmlspecialchars($row['pasos'])); ?>
</p>

<p>
<strong>Tipo:</strong>
<?php echo htmlspecialchars($row['tipo']); ?>
</p>

<div style="display:flex; gap:10px; flex-wrap:wrap;">

<a
href="editar_receta.php?id=<?php echo (int)$row['id']; ?>"
class="btn-editar"
>
✏️ Editar
</a>

<form action="eliminar_receta.php" method="POST">

<input
type="hidden"
name="id"
value="<?php echo (int)$row['id']; ?>"
>

<button type="submit">
🗑️ Eliminar
</button>

</form>

</div>

</div>

<?php } ?>

</div>

</div>

</body>
</html>