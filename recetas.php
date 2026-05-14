<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM recetas WHERE usuario_id = $user_id";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

    <h2>Mis recetas</h2>

    <div class="form-box">

        <form action="guardar_receta.php" method="POST">

            <input type="text" name="titulo" placeholder="Título" required>

            <textarea name="descripcion" placeholder="Descripción"></textarea>

            <button type="submit">
                Guardar receta
            </button>

        </form>

    </div>

    <div class="recetas-grid">

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="card">

                <h3><?php echo $row['titulo']; ?></h3>

                <p><?php echo $row['descripcion']; ?></p>

                <form action="eliminar_receta.php" method="POST">

                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                    <button type="submit">
                        Eliminar
                    </button>

                </form>

            </div>

        <?php } ?>

    </div>

</div>