<?php
session_start();
include("config.php");
include("preferencias.php");

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
$preferencias = obtener_preferencias($conn);
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<h2>Recetas</h2>

<div class="form-box">

<form
action="guardar_receta.php"
method="POST"
enctype="multipart/form-data"
>

<input
type="text"
name="titulo"
placeholder="Título"
required
>

<textarea
name="descripcion"
required
placeholder="Descripción"
></textarea>

<textarea
name="ingredientes"
required
placeholder="Ingredientes"
></textarea>

<textarea
name="pasos"
required
placeholder="Paso a paso"
></textarea>

<label>Tipo de receta</label>

<select
name="tipo"
id="tipo"
onchange="mostrarOtro()"
>

<?php foreach($preferencias as $valor => $texto) { ?>

<option value="<?php echo htmlspecialchars($valor); ?>">
<?php echo htmlspecialchars($texto); ?>
</option>

<?php } ?>

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

<br><br>

<label>Temporada recomendada</label>

<select name="estacion">

<option value="verano">
Verano
</option>

<option value="otoño">
Otoño
</option>

<option value="invierno">
Invierno
</option>

<option value="primavera">
Primavera
</option>

</select>

<br><br>

<label>Foto de la receta</label>

<input
type="file"
name="imagen"
accept="image/*"
>

<button type="submit">
Guardar receta
</button>

</form>

</div>



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
