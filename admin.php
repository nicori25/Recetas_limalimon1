<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['rol'] != "admin") {
    die("No tienes permisos para acceder a esta página.");
}

include("config.php");

// Contar usuarios
$result = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
$usuarios = $result->fetch_assoc()['total'];

// Contar recetas
$result = $conn->query("SELECT COUNT(*) AS total FROM recetas");
$recetas = $result->fetch_assoc()['total'];

// Contar valoraciones
$result = $conn->query("SELECT COUNT(*) AS total FROM valoraciones");
$valoraciones = $result->fetch_assoc()['total'];

// Contar comentarios
$result = $conn->query("SELECT COUNT(*) AS total FROM opiniones");
$comentarios = $result->fetch_assoc()['total'];

// Últimas recetas
$ultimas = $conn->query("
SELECT
recetas.titulo,
usuarios.nombre
FROM recetas
INNER JOIN usuarios
ON recetas.usuario_id = usuarios.id
ORDER BY recetas.id DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Panel Administrador</title>


<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

    <h2>Panel de Administración</h2>

    <div class="form-box">

<h3>

Bienvenido,
<?php echo htmlspecialchars($_SESSION['user_nombre']); ?>

</h3>

<p>

Desde este panel puedes administrar usuarios, recetas, comentarios y supervisar toda la actividad del sitio.

</p>

</div>

    <div class="recetas-grid">

        <div class="card">
        <h3>👥 Usuarios</h3>
        <div class="numero-admin">

        <?php echo $usuarios; ?>

        </div>

        <p>Usuarios registrados</p>
        <a href="admin_usuarios.php" class="btn-editar">
        Administrar
        </a>
        </div>

        <div class="card">
        <h3>🍽️ Recetas</h3>
        <div class="numero-admin">

        <?php echo $recetas; ?>

        </div>

        <p>Recetas publicadas</p>
        <a href="admin_recetas.php" class="btn-editar">
        Administrar
        </a>
        </div>

        

        <div class="card">
    <h3>⭐ Valoraciones y comentarios</h3>
   <div class="numero-admin">

        <?php echo $valoraciones; ?>

        </div>
        <p>Valoraciones registradas</p>
    <a href="admin_valoraciones.php" class="btn-editar">
        Administrar
    </a>
</div>

    </div>
    <h2>🍽️ Últimas recetas publicadas</h2>

<div class="form-box">

<?php

$ultimas =
$conn->query("

SELECT
recetas.titulo,
usuarios.nombre

FROM recetas

INNER JOIN usuarios

ON recetas.usuario_id=usuarios.id

ORDER BY recetas.id DESC

LIMIT 5

");

while($row=$ultimas->fetch_assoc()){

?>

<div class="actividad-admin">

<strong>

🍽️
<?php echo htmlspecialchars($row['titulo']); ?>

</strong>

<br>

<small>

Publicado por

<?php echo htmlspecialchars($row['nombre']); ?>

</small>

</div>

<hr>

<?php } ?>

</div>

</div>

</body>
</html>