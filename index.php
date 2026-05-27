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

                <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>

                <p><?php echo nl2br(htmlspecialchars($row['descripcion'])); ?></p>

                <p>
                    <strong>Ingredientes:</strong>
                    <br>
                    <?php echo nl2br(htmlspecialchars($row['ingredientes'])); ?>
                </p>

                <p>
                    <strong>Preparacion:</strong>
                    <br>
                    <?php echo nl2br(htmlspecialchars($row['pasos'])); ?>
                </p>

                <p>
                    <strong>Tipo:</strong>
                    <?php echo htmlspecialchars($row['tipo']); ?>
                </p>

                <small>
                    Publicado por:
                    <?php echo htmlspecialchars($row['nombre']); ?>
                </small>
                <?php if(isset($_SESSION['user_id'])) { ?>

<form action="guardar_opinion.php" method="POST">

    <input 
        type="hidden"
        name="receta_id"
        value="<?php echo (int)$row['id']; ?>"
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
            <?php echo htmlspecialchars($op['nombre']); ?>:
        </strong>

        <?php echo nl2br(htmlspecialchars($op['comentario'])); ?>
    </p>

<?php } ?>

            </div>

        <?php } ?>

    </div>

</div>
