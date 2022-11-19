<header>
    <?php
    if (isset($_SESSION["usuario"])) {
    ?>
        <div class="barra_lateral">
            <h3>Seguidos</h3>
            <?php
            $conexion = conectar();
            $consulta = $conexion->prepare("SELECT * FROM follows WHERE userid = ?");
            $consulta->execute([$_SESSION['usuario_id']]);
            if ($consulta->rowCount() > 0) {
                while ($seguido = $consulta->fetch(PDO::FETCH_ASSOC)) {
                    $usuario = $conexion->query("SELECT usuario FROM users WHERE id = " . $seguido['userfollowed']);
                    $usuario = $usuario->fetch(PDO::FETCH_ASSOC);
                    echo '<p href="perfil.php?id=' . $seguido['userfollowed'] . '">' . $usuario['usuario'] . '</p>';
                }
            } else {
                echo '<p>No sigues a nadie</p>';
            }
            ?>
        </div>
        <div class="cabecera">
            <a href="index.php"><img class="logo" src="img/logo.png" alt="Logo_Revels"></a>
            <div class="barra_navegacion">
                <a href="new.php">Nuevo Revel</a>
                <a href="account.php?id=<?=$_SESSION['usuario_id']?>">Cuenta</a>
                <a href="logout.php">Cerrar sesión</a>
                <div class="busqueda">
                    <form action="results.php">
                        <input type="text" name="busqueda" id="busqueda">
                        <button type="submit">🔍</button>
                    </form>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="cabecera">
            <a href="index.php"><img class="logo" src="img/logo.png" alt="Logo_Revels"></a>
            <div class="barra_navegacion">
                <a class="enlace_resaltado" href="login.php">Iniciar sesión</a>
            </div>
        </div>
    <?php
    }
    ?>
</header>