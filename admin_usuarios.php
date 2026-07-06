<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['rol'] != "admin"){
    die("Acceso denegado.");
}

$result = $conn->query("
SELECT id,nombre,email,rol
FROM usuarios
ORDER BY id ASC
");
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Administrar usuarios</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<h2>👥 Administrar Usuarios</h2>

<table class="tabla-admin">

<thead>

<tr>

<th>ID</th>

<th>Nombre</th>

<th>Email</th>

<th>Rol</th>

<th>Acciones</th>

</tr>

</thead>
<tbody>

<?php while($user = $result->fetch_assoc()){ ?>

<tr>

<td data-label="ID"><?php echo $user['id']; ?></td>

<td data-label="Nombre"><?php echo htmlspecialchars($user['nombre']); ?></td>

<td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>

<td data-label="Rol">

<?php

if($user['rol']=="admin"){

echo "👑 Administrador";

}else{

echo "👤 Usuario";

}

?>

</td>

<td data-label="Acciones">

<?php if($user['id'] != $_SESSION['user_id']){ ?>

    <?php if($user['rol']=="usuario"){ ?>

        <a
        href="cambiar_rol.php?id=<?php echo $user['id']; ?>&rol=admin"
        class="btn-editar">
        👑 Hacer Admin
        </a>

    <?php }else{ ?>

        <a
        href="cambiar_rol.php?id=<?php echo $user['id']; ?>&rol=usuario"
        class="btn-editar">
        👤 Quitar Admin
        </a>

    <?php } ?>

<?php }else{ ?>

    <strong>Tu cuenta</strong>

<?php } ?>
<a
href="eliminar_usuario.php?id=<?php echo $user['id']; ?>"
class="btn-eliminar"
onclick="return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.')"
>
🗑️ Eliminar
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>