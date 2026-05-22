<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])) {
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

<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<h2>Mis recetas</h2>

<div class="form-box">

<form action="guardar_receta.php" method="POST">

<input
type="text"
name="titulo"
placeholder="Título"
required
>

<textarea
name="descripcion"
placeholder="Descripción"
></textarea>

<textarea
name="ingredientes"
placeholder="Ingredientes"
></textarea>

<textarea
name="pasos"
placeholder="Paso a paso"
></textarea>

<label>Tipo de receta</label>

<select
name="tipo"
id="tipo"
onchange="mostrarOtro()"
>

<option value="vegano">
Vegano
</option>

<option value="vegetariano">
Vegetariano
</option>

<option value="sin gluten">
Sin gluten
</option>

<option value="sin lactosa">
Sin lactosa
</option>

<option value="otro">
Otra alergia/intolerancia
</option>

</select>

<input
type="text"
name="tipo_personalizado"
id="otro"
placeholder="Escribir tipo..."
style="display:none;"
>

<button type="submit">
Guardar receta
</button>

</form>

</div>

<div class="recetas-grid">

<?php while($row = $result->fetch_assoc()) { ?>

<div class="card">

<h3>
<?php echo $row['titulo']; ?>
</h3>

<p>
<?php echo $row['descripcion']; ?>
</p>

<p>

<strong>Ingredientes:</strong>

<br>

<?php echo $row['ingredientes']; ?>

</p>

<p>

<strong>Preparación:</strong>

<br>

<?php echo $row['pasos']; ?>

</p>

<p>

<strong>Tipo:</strong>

<?php echo $row['tipo']; ?>

</p>

<form
action="eliminar_receta.php"
method="POST"
>

<input
type="hidden"
name="id"
value="<?php echo $row['id']; ?>"
>

<button>

Eliminar

</button>

</form>

</div>

<?php } ?>

</div>

</div>

<script>

function mostrarOtro(){

let valor =
document.getElementById("tipo").value;

let input =
document.getElementById("otro");

if(valor==="otro"){

input.style.display="block";

}else{

input.style.display="none";

input.value="";

}

}

</script>