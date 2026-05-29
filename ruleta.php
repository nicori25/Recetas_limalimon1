<?php
session_start();
include("config.php");

$mes = date("n");

if($mes==12 || $mes<=2){

$estacion="verano";

}elseif($mes<=5){

$estacion="otoño";

}elseif($mes<=8){

$estacion="invierno";

}else{

$estacion="primavera";

}

$stmt =
$conn->prepare("
SELECT id,titulo
FROM recetas
WHERE estacion=?
");

$stmt->bind_param(
"s",
$estacion
);

$stmt->execute();

$result =
$stmt->get_result();

$recetas=[];

while(
$row=
$result->fetch_assoc()
){

$recetas[]=$row;

}
?>

<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<h2>

🎡 Ruleta de recetas

</h2>

<p>

Estación actual:

<strong>

<?php echo $estacion; ?>

</strong>

</p>

<div class="form-box">

<button onclick="girar()">

Girar

</button>

</div>

<h2 id="resultado">

</h2>

</div>

<script>

let recetas=
<?php
echo json_encode($recetas);
?>;

function girar(){

if(recetas.length===0){

document
.getElementById(
"resultado"
)
.innerHTML=
"No hay recetas";

return;

}

let random=
Math.floor(
Math.random()
*
recetas.length
);

let receta=
recetas[random];

document
.getElementById(
"resultado"
)
.innerHTML=
`
🍽️ Te tocó:

<br><br>

${receta.titulo}
`;

}

</script>