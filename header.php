<header class="header">
    
    <img src="img/logo.png" alt="logo">

    <nav>
        <a href="index.php">Inicio</a>

        <?php if(isset($_SESSION['user_id'])) { ?>

            <a href="recetas.php">Recetas</a>
            <a href="ruleta.php">Ruleta</a>
            <a href="mis_recetas.php">Mis recetas</a>
            <a href="mis_favoritos.php">Mis Favoritos</a>
            <a href="perfil.php">Perfil</a>
            <?php if(isset($_SESSION['rol']) && $_SESSION['rol']=="admin"){ ?>

            <a href="admin.php">
            Administración
            </a>

            <?php } ?>
            <a href="logout.php">Cerrar sesión</a>

        <?php } else { ?>

            <a href="login.php">Login</a>
            <a href="register.php">Registro</a>

        <?php } ?>
    </nav>

</header>