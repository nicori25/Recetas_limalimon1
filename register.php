<?php
include("config.php");

$error = "";

$nombre = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre']);
    $email = trim(strtolower($_POST['email']));
    $passwordTexto = $_POST['password'];

    /* ==========================
       VALIDAR FORMATO DEL EMAIL
    =========================== */

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "El correo electrónico no es válido.";

    }

    /* ==========================
       VALIDAR DOMINIO
    =========================== */

    if ($error == "") {

        $dominiosPermitidos = [

            "gmail.com",
            "hotmail.com",
            "outlook.com",
            "live.com",
            "icloud.com",
            "yahoo.com",
            "yahoo.com.ar",
            "proton.me",
            "protonmail.com"

        ];

        $dominio = substr(strrchr($email, "@"), 1);

        if (!in_array($dominio, $dominiosPermitidos)) {

            $error = "Solo se permiten correos de Gmail, Hotmail, Outlook, Yahoo, iCloud o Proton.";

        }

    }

    /* ==========================
       VALIDAR CONTRASEÑA
    =========================== */

    if ($error == "") {

        if (
            !preg_match(
                '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W]).{6,18}$/',
                $passwordTexto
            )
        ) {

            $error =
"La contraseña debe tener:

• Entre 6 y 18 caracteres.

• Una letra mayúscula.

• Una letra minúscula.

• Un número.

• Un carácter especial.";

        }

    }

    /* ==========================
       VERIFICAR EMAIL REPETIDO
    =========================== */

    if ($error == "") {

        $stmt = $conn->prepare("
        SELECT id
        FROM usuarios
        WHERE email=?
        ");

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $error = "Ese correo electrónico ya está registrado.";

        }

    }

    /* ==========================
       INSERTAR USUARIO
    =========================== */

    if ($error == "") {

        $password = password_hash(
            $passwordTexto,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare("
        INSERT INTO usuarios
        (
            nombre,
            email,
            password
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
        ");

        $stmt->bind_param(
            "sss",
            $nombre,
            $email,
            $password
        );

        if ($stmt->execute()) {

            header("Location: login.php");
            exit();

        } else {

            $error = "Ocurrió un error al registrar el usuario.";

        }

    }

}
?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Registro</title>

<link rel="stylesheet"
href="css/style.css">

</head>

<body>

<?php include("header.php"); ?>

<div class="container">

<div class="form-box">

<h2>Registro</h2>

<?php if($error != ""){ ?>

<div class="error-box">

<h3>⚠ Error</h3>

<p style="white-space:pre-line;">
<?php echo htmlspecialchars($error); ?>
</p>

</div>

<?php } ?>

<form method="POST">
    <input
type="text"
name="nombre"
placeholder="Nombre"
required
value="<?php echo htmlspecialchars($nombre); ?>"
>

<input
type="email"
name="email"
placeholder="Correo electrónico"
required
value="<?php echo htmlspecialchars($email); ?>"
>

<input
type="password"
name="password"
id="password"
placeholder="Contraseña"
required
>

<div class="password-options">

<label>

<input
type="checkbox"
onclick="mostrarPassword()"
>

Mostrar contraseña

</label>

</div>

<div id="requisitos">

❌ Entre 6 y 18 caracteres<br>
❌ Una letra mayúscula<br>
❌ Una letra minúscula<br>
❌ Un número<br>
❌ Un carácter especial

</div>

<button type="submit">

Registrarse

</button>

</form>

</div>

</div>

<script>

function mostrarPassword(){

let pass =
document.getElementById("password");

if(pass.type=="password"){

pass.type="text";

}else{

pass.type="password";

}

}

const password =
document.getElementById("password");

password.addEventListener("keyup",function(){

let valor=password.value;

let texto="";

texto +=
(valor.length>=6 && valor.length<=18)
?
"✅ Entre 6 y 18 caracteres<br>"
:
"❌ Entre 6 y 18 caracteres<br>";

texto +=
(/[A-Z]/.test(valor))
?
"✅ Una letra mayúscula<br>"
:
"❌ Una letra mayúscula<br>";

texto +=
(/[a-z]/.test(valor))
?
"✅ Una letra minúscula<br>"
:
"❌ Una letra minúscula<br>";

texto +=
(/[0-9]/.test(valor))
?
"✅ Un número<br>"
:
"❌ Un número<br>";

texto +=
(/[\W_]/.test(valor))
?
"✅ Un carácter especial"
:
"❌ Un carácter especial";

document.getElementById("requisitos").innerHTML =
texto;

});

</script>

</body>

</html>