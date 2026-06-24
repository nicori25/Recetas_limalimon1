<?php
session_start();
include("config.php");

$mes = date("n");

if($mes == 12 || $mes <= 2){

    $estacion = "verano";

}elseif($mes <= 5){

    $estacion = "otoño";

}elseif($mes <= 8){

    $estacion = "invierno";

}else{

    $estacion = "primavera";

}

$stmt = $conn->prepare("
SELECT
id,
titulo,
descripcion,
ingredientes,
pasos,
tipo,
imagen
FROM recetas
WHERE estacion=?
");

$stmt->bind_param(
"s",
$estacion
);

$stmt->execute();

$result = $stmt->get_result();

$recetas = [];

while($row = $result->fetch_assoc()){

    $recetas[] = $row;

}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Ruleta de recetas</title>

<link rel="stylesheet" href="css/style.css">

<style>

.ruleta-box{

text-align:center;

}

.ruleta-btn{

font-size:20px;
padding:15px 30px;

}

.girando{

font-size:28px;
font-weight:bold;
animation:girarTexto 0.7s infinite;

}

@keyframes girarTexto{

0%{
transform:rotate(-5deg);
}

50%{
transform:rotate(5deg);
}

100%{
transform:rotate(-5deg);
}

}

.resultado-card{

margin-top:30px;

}

</style>

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<h2>
🎡 Ruleta de recetas
</h2>

<p>

Estación actual:

<strong>
<?php echo ucfirst($estacion); ?>
</strong>

</p>

<div class="form-box ruleta-box">

<button
class="ruleta-btn"
onclick="girar()"
>

🎲 Girar ruleta

</button>

</div>

<div id="resultado"></div>

</div>

<script>

let recetas =
<?php echo json_encode($recetas); ?>;

function girar(){

let resultado =
document.getElementById("resultado");

if(recetas.length === 0){

resultado.innerHTML = `
<div class="card">
<h3>No hay recetas para esta temporada.</h3>
</div>
`;

return;

}

resultado.innerHTML = `
<div class="girando">
🎡 Girando...
</div>
`;

setTimeout(()=>{

let random =
Math.floor(
Math.random() *
recetas.length
);

let receta =
recetas[random];

resultado.innerHTML = `

<div class="card resultado-card">

${receta.imagen ?
`
<img
src="uploads/${receta.imagen}"
class="imagen-receta"
alt="${receta.titulo}"
>
`
:
""
}

<h3>
🍽️ ${receta.titulo}
</h3>

<p>

<strong>Descripción:</strong>

<br>

${receta.descripcion}

</p>

<p>

<strong>Ingredientes:</strong>

<br>

${receta.ingredientes.replace(/\n/g,"<br>")}

</p>

<p>

<strong>Preparación:</strong>

<br>

${receta.pasos.replace(/\n/g,"<br>")}

</p>

<p>

<strong>Tipo:</strong>

${receta.tipo}

</p>

</div>

`;

},2000);

}

</script>

</body>
</html>