<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT *
FROM recetas
WHERE usuario_id=?
ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">

<?php include("header.php"); ?>

<div class="container">

<h2>Mis recetas</h2>

<div class="recetas-grid">

<?php while($row=$result->fetch_assoc()){ ?>

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

<form action="eliminar_receta.php" method="POST">

<input
type="hidden"
name="id"
value="<?php echo (int)$row['id']; ?>"
>

<button>
Eliminar
</button>

</form>

</div>

<?php } ?>

</div>

</div>
