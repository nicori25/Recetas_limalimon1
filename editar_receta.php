<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("
SELECT *
FROM recetas
WHERE id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

$receta = $stmt->get_result()->fetch_assoc();

if(!$receta){
    die("Receta no encontrada");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar receta</title>

<link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<div class="form-box">

<h2>Editar receta</h2>

<form
action="actualizar_receta.php"
method="POST"
enctype="multipart/form-data"
>

<input
type="hidden"
name="id"
value="<?php echo $receta['id']; ?>"
>

<?php if(!empty($receta['imagen'])){ ?>

<p><strong>Imagen actual</strong></p>

<img
src="uploads/<?php echo htmlspecialchars($receta['imagen']); ?>"
style="
width:100%;
max-width:350px;
border-radius:10px;
margin-bottom:15px;
"
>

<?php } ?>

<label>Cambiar imagen</label>

<input
type="file"
name="imagen"
accept="image/*"
>

<input
type="text"
name="titulo"
value="<?php echo htmlspecialchars($receta['titulo']); ?>"
required
>

<textarea
name="descripcion"
placeholder="Descripción"
><?php echo htmlspecialchars($receta['descripcion']); ?></textarea>

<textarea
name="ingredientes"
placeholder="Ingredientes"
><?php echo htmlspecialchars($receta['ingredientes']); ?></textarea>

<textarea
name="pasos"
placeholder="Paso a paso"
><?php echo htmlspecialchars($receta['pasos']); ?></textarea>

<input
type="text"
name="tipo"
value="<?php echo htmlspecialchars($receta['tipo']); ?>"
required
>

<button type="submit">
💾 Guardar cambios
</button>

</form>

</div>

</div>

</body>
</html>