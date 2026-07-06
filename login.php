<?php
session_start();
include("config.php");

if ($_POST) {

    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("
    SELECT *
    FROM usuarios
    WHERE email=?
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            header("Location: index.php");
            exit();

        } else {

            $error = "La contraseña es incorrecta.";

        }

    } else {

        $error = "No existe una cuenta registrada con ese correo.";

    }

}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="form-box">

<h2>Login</h2>

<?php if(isset($error)){ ?>

<div class="error-box">

<h3>⚠ Error</h3>

<p><?php echo htmlspecialchars($error); ?></p>

<a href="register.php" class="btn-volver">
Crear una cuenta
</a>

</div>

<?php } ?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Correo electrónico"
required
>

<input
type="password"
name="password"
id="password"
placeholder="Contraseña"
required
>

<div class="mostrar-password">

<input
type="checkbox"
id="verPass"
onclick="mostrarPassword()"
>

<label for="verPass">
Mostrar contraseña
</label>

</div>

<button type="submit">

Entrar

</button>

</form>

</div>

<script>

function mostrarPassword(){

let pass =
document.getElementById("password");

pass.type =
pass.type==="password"
?
"text"
:
"password";

}

</script>

</body>
</html>