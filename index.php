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
                <?php if(isset($_SESSION['user_id'])) { ?>

<form action="guardar_opinion.php" method="POST">

    <input 
        type="hidden"
        name="receta_id"
        value="<?php echo $row['id']; ?>"
    >

    <textarea 
        name="comentario"
        placeholder="Escribí una opinión..."
        required
    ></textarea>

    <button type="submit">
        Comentar
    </button>

</form>

<?php } ?>
<?php

$stmt2 = $conn->prepare("
SELECT opiniones.*, usuarios.nombre
FROM opiniones
JOIN usuarios
ON opiniones.usuario_id = usuarios.id
WHERE receta_id=?
ORDER BY opiniones.id DESC
");

$stmt2->bind_param("i", $row['id']);
$stmt2->execute();

$opiniones = $stmt2->get_result();

while($op = $opiniones->fetch_assoc()){

?>

    <p>
        <strong>
            <?php echo $op['nombre']; ?>:
        </strong>

        <?php echo $op['comentario']; ?>
    </p>

<?php } ?>

            </div>

        <?php } ?>

    </div>

</div>