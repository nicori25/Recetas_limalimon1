<?php
session_start();
include("config.php");
include("preferencias.php");

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
$preferencias = obtener_preferencias($conn);
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<div class="form-box">

<h2>Mi Perfil</h2>

<form action="actualizar.php" method="POST">

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
id="newpassword"
placeholder="Nueva contraseña (opcional)"
>
<input type="checkbox" onclick="mostrarNewPassword()">
<label>Preferencia alimentaria</label>

<select
name="preferencia_tipo"
id="preferencia"
onchange="mostrarOtro()"
>

<option value="">
Sin preferencia
</option>

<?php foreach($preferencias as $valor => $texto) { ?>

<option
value="<?php echo htmlspecialchars($valor); ?>"
<?php if(($user['preferencia_tipo'] ?? "") == $valor) echo "selected"; ?>
>
<?php echo htmlspecialchars($texto); ?>
</option>

<?php } ?>

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
function mostrarNewPassword() {

    let pass =
    document.getElementById("newpassword");

    if(pass.type === "password"){

        pass.type = "text";

    }else{

        pass.type = "password";

    }
}

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
