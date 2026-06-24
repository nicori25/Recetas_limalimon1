<?php
include("config.php");

if ($_POST) {

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $passwordTexto = $_POST['password'];

if(
!preg_match(
'/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{6,18}$/',
$passwordTexto
)
){
    die("
    La contraseña debe tener:
    <br>- Entre 6 y 18 caracteres
    <br>- Una mayúscula
    <br>- Un número
    <br>- Un carácter especial
    ");
}
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
        <div id="requisitos">

❌ Entre 6 y 18 caracteres<br>
❌ Una mayúscula<br>
❌ Un número<br>
❌ Un carácter especial

</div>
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
const password =
document.getElementById("password");

password.addEventListener("keyup", function(){

let valor = password.value;

let texto = "";

texto +=
(valor.length >= 6 && valor.length <= 18)
? "✅ Entre 6 y 18 caracteres<br>"
: "❌ Entre 6 y 18 caracteres<br>";

texto +=
(/[A-Z]/.test(valor))
? "✅ Una mayúscula<br>"
: "❌ Una mayúscula<br>";

texto +=
(/[0-9]/.test(valor))
? "✅ Un número<br>"
: "❌ Un número<br>";

texto +=
(/[\W]/.test(valor))
? "✅ Un carácter especial"
: "❌ Un carácter especial";

document.getElementById("requisitos").innerHTML =
texto;

});

</script>