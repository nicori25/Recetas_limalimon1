<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt =
$conn->prepare("
SELECT recetas.*, usuarios.nombre
FROM favoritos
JOIN recetas
ON favoritos.receta_id = recetas.id
JOIN usuarios
ON recetas.usuario_id = usuarios.id
WHERE favoritos.usuario_id=?
ORDER BY favoritos.id DESC
");

$stmt->bind_param("i",$id);

$stmt->execute();

$result =
$stmt->get_result();
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<h2>❤️ Mis favoritos</h2>

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

<?php
echo htmlspecialchars(
$row['titulo']
);
?>

</h3>

<p>

<?php
echo nl2br(
htmlspecialchars(
$row['descripcion']
)
);
?>

</p>

<p>

<strong>Ingredientes:</strong>

<br>

<?php
echo nl2br(
htmlspecialchars(
$row['ingredientes']
)
);
?>

</p>

<p>

<strong>Preparación:</strong>

<br>

<?php
echo nl2br(
htmlspecialchars(
$row['pasos']
)
);
?>

</p>

<p>

<strong>Tipo:</strong>

<?php
echo htmlspecialchars(
$row['tipo']
);
?>

</p>

<p>

<strong>Temporada:</strong>

<?php
echo htmlspecialchars(
$row['estacion']
);
?>

</p>

<small>

Publicado por:

<?php
echo htmlspecialchars(
$row['nombre']
);
?>

</small>

<br><br>

<!-- ⭐ PUNTUACIÓN -->

<?php

$prom =
$conn->prepare("
SELECT
AVG(puntuacion) AS promedio,
COUNT(*) AS total
FROM valoraciones
WHERE receta_id=?
");

$prom->bind_param(
"i",
$row['id']
);

$prom->execute();

$data =
$prom->get_result()->fetch_assoc();

$promedio =
round($data['promedio'],1);

$total =
$data['total'];

$porcentaje =
($promedio / 5) * 100;

?>

<div class="rating-box">

<p>

<?php

if($promedio >= 1) echo "⭐";
if($promedio >= 2) echo "⭐";
if($promedio >= 3) echo "⭐";
if($promedio >= 4) echo "⭐";
if($promedio >= 5) echo "⭐";

?>

</p>

<div class="barra">

<div
class="relleno"
style="width:
<?php echo $porcentaje; ?>%;
"
></div>

</div>

<p>

<?php
echo round($porcentaje);
?>%
de aprobación

</p>

<p>

<?php
echo $total;
?>
votos

</p>

</div>

<!-- ❌ QUITAR FAVORITO -->

<form
action="favorito.php"
method="POST"
>

<input
type="hidden"
name="receta_id"
value="<?php echo (int)$row['id']; ?>"
>

<button>

❌ Quitar favorito

</button>

</form>

<!-- 💬 OPINIONES -->

<?php

$stmt2 = $conn->prepare("
SELECT opiniones.*, usuarios.nombre
FROM opiniones
JOIN usuarios
ON opiniones.usuario_id = usuarios.id
WHERE receta_id=?
ORDER BY opiniones.id DESC
");

$stmt2->bind_param(
"i",
$row['id']
);

$stmt2->execute();

$opiniones =
$stmt2->get_result();

while(
$op =
$opiniones->fetch_assoc()
){

?>

<p>

<strong>

<?php
echo htmlspecialchars(
$op['nombre']
);
?>:

</strong>

<?php
echo nl2br(
htmlspecialchars(
$op['comentario']
)
);
?>

</p>

<?php } ?>

</div>

<?php } ?>

</div>

</div>
