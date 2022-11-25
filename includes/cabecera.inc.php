<header>
    <?php
    /*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene la cabecera de la página tanto para el usuario registrado como para el no registrado
    - Contiene la barra lateral de amigos
    */
    if (isset($_SESSION["usuario"])) {
    ?>
        <div class="barra_lateral">
            <h3>Seguidos</h3>
            <?php
            //Se obtienen los seguidos del usuario
            $conexion = conectar();
            try {
                $consulta = $conexion->prepare("SELECT * FROM follows WHERE userid = ?");
                $consulta->execute([$_SESSION['usuario_id']]);
                if ($consulta->rowCount() > 0) {
                    while ($seguido = $consulta->fetch(PDO::FETCH_ASSOC)) {
                        $usuario = $conexion->query("SELECT usuario,id FROM users WHERE id = " . $seguido['userfollowed']);
                        $usuario = $usuario->fetch(PDO::FETCH_ASSOC);
                        echo "<form action='results.php' method='post'>";
                        echo '<p>' . $usuario['usuario'] . '</p>';
                        echo '<input type="hidden" name="amigo_id_borrar" value="' . $usuario['id'] . '">';
                        echo '<input type="hidden" name="location" value="' . $_SERVER['REQUEST_URI'] . '">';
                        echo '<input class="boton borrar_cuenta" type="submit" value="Dejar de seguir">';
                        echo "</form>";
                    }
                } else {
                    echo '<p>No sigues a nadie</p>';
                }
            } catch (\Throwable $th) {
                echo '<p class="error">Algo ha salido mal accediendo a la base de datos</p>';
            }
            ?>
        </div>
        <div class="cabecera">
            <a href="index.php"><img class="logo" src="img/logo.png" alt="Logo_Revels"></a>
            <div class="barra_navegacion">
                <a href="new.php">Nuevo Revel</a>
                <a href="account.php?id=<?= $_SESSION['usuario_id'] ?>">Cuenta</a>
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