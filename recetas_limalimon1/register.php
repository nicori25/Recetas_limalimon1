<?php
include("config.php");

if ($_POST) {

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios(nombre,email,password) VALUES(?,?,?)");
    $stmt->bind_param("sss", $nombre, $email, $password);
    $stmt->execute();

    header("Location: login.php");
}
?>

<link rel="stylesheet" href="css/style.css">

<div class="form-box">
    <h2>Registro</h2>

    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <button type="submit">Registrarse</button>
    </form>
</div>