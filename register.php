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

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<div class="form-box">
    <h2>Registro</h2>

    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        <input type="checkbox" onclick="mostrarPassword()" placeholder="Mostrar Contraseña">
        <button type="submit">Registrarse</button>
    </form>
</div>
<script>
function mostrarPassword() {

    let pass =
    document.getElementById("password");

    if(pass.type === "password"){

        pass.type = "text";

    }else{

        pass.type = "password";

    }
}
</script>