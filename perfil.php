<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

    <div class="form-box">

        <h2>Mi Perfil</h2>

        <form action="actualizar_perfil.php" method="POST">

            <input 
                type="text" 
                name="nombre"
                value="<?php echo $user['nombre']; ?>"
                required
            >

            <input 
                type="email" 
                name="email"
                value="<?php echo $user['email']; ?>"
                required
            >

            <input 
                type="password" 
                name="password"
                placeholder="Nueva contraseña (opcional)"
            >

            <button type="submit">
                Actualizar perfil
            </button>

        </form>

    </div>

</div>