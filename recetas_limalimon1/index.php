<?php
session_start();
include("config.php");

$sql = "SELECT recetas.*, usuarios.nombre
        FROM recetas
        JOIN usuarios ON recetas.usuario_id = usuarios.id
        ORDER BY recetas.id DESC";

$result = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

    <h2>Recetas</h2>

    <div class="recetas-grid">

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="card">

                <h3><?php echo $row['titulo']; ?></h3>

                <p><?php echo $row['descripcion']; ?></p>

                <small>
                    Publicado por:
                    <?php echo $row['nombre']; ?>
                </small>

            </div>

        <?php } ?>

    </div>

</div>