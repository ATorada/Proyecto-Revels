<header>
    <?php
        $registrado = true;
        if ($registrado) {
    ?>
    <div class="barra_lateral">
        <h3>Amigos</h3>
        <a href="#">Amigo 1</a>
        <a href="#">Amigo 2</a>
        <a href="#">Amigo 3</a>
    </div>
    <div class="cabecera">
        <a href="index.php"><img class="logo" src="img/logo.png" alt="Logo_Revels"></a>
        <div class="barra_navegacion">
            <a href="new.php">Nuevo Revel</a>
            <a href="#">Cuenta</a>
            <a href="#">Cerrar sesión</a>
            <div class="busqueda">
            <form action="#">
                    <input type="text">
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
            <a class="enlace_resaltado" href="#">Iniciar sesión</a>
        </div>
    </div>
    <?php
        }
    ?>
</header>