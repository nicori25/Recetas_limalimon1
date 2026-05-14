<header class="header">
    
    <img src="img/logo.png" alt="logo">

    <nav>
        <a href="index.php">Inicio</a>

        <?php if(isset($_SESSION['user_id'])) { ?>

            <a href="recetas.php">Recetas</a>
            <a href="logout.php">Cerrar sesión</a>

        <?php } else { ?>

            <a href="login.php">Login</a>
            <a href="register.php">Registro</a>

        <?php } ?>
    </nav>

</header>