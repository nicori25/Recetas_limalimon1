<?php
session_start();
include("config.php");

if ($_POST) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];

            header("Location: index.php");
            exit();
        }
    }

    echo "Datos incorrectos <a href='register.php'>registrate</a>";
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<div class="form-box">
    <h2>Login</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        <input type="checkbox" onclick="mostrarPassword()" placeholder="Mostrar Contraseña">
        <button type="submit">Entrar</button>
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