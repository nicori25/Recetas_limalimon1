<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT *
FROM usuarios
WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<div class="form-box">

<h2>Mi Perfil</h2>

<form action="actualizar_perfil.php" method="POST">

<input
type="text"
name="nombre"
value="<?php echo $user['nombre']; ?>"
required
>

<input
type="email"
name="email"
value="<?php echo $user['email']; ?>"
required
>

<input
type="password"
name="password"
placeholder="Nueva contraseña (opcional)"
>

<label>Preferencia alimentaria</label>

<select
name="preferencia_tipo"
id="preferencia"
onchange="mostrarOtro()"
>

<option value="">
Sin preferencia
</option>

<option
value="vegano"
<?php if(($user['preferencia_tipo'] ?? "")=="vegano") echo "selected"; ?>
>
Vegano
</option>

<option
value="vegetariano"
<?php if(($user['preferencia_tipo'] ?? "")=="vegetariano") echo "selected"; ?>
>
Vegetariano
</option>

<option
value="sin gluten"
<?php if(($user['preferencia_tipo'] ?? "")=="sin gluten") echo "selected"; ?>
>
Sin gluten
</option>

<option
value="sin lactosa"
<?php if(($user['preferencia_tipo'] ?? "")=="sin lactosa") echo "selected"; ?>
>
Sin lactosa
</option>

<option value="otro">
Otra alergia/intolerancia
</option>

</select>

<input
type="text"
name="preferencia_personalizada"
id="otra"
placeholder="Escribir preferencia..."
style="display:none;"
>

<button type="submit">
Actualizar perfil
</button>

</form>

</div>

</div>

<script>

function mostrarOtro(){

let valor=
document.getElementById("preferencia").value;

let campo=
document.getElementById("otra");

if(valor==="otro"){

campo.style.display="block";

}else{

campo.style.display="none";

campo.value="";

}

}

</script>
